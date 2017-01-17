<?php
/*
=====================================================
 DataLife Engine - by SoftNews Media Group
-----------------------------------------------------
 http://dle-news.ru/
-----------------------------------------------------
 Copyright (c) 2004-2016 SoftNews Media Group
=====================================================
 Данный код защищен авторскими правами
=====================================================
 Файл: rss.class.php
-----------------------------------------------------
 Назначение: XML Парсер
=====================================================
*/

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

class xmlParser {
	
	var $att;
	var $id;
	var $title;
	var $content = array ();
	var $max_news = 0;
	var $rss_charset = '';
	var $lastdate = '';
	var $pre_lastdate = '';
	
	function __construct($file, $max) {
		
		$this->max_news = $max;
		
		if( ! ($data = $this->_get_contents( $file )) ) {
			$this->content[0]['title'] = "Fatal Error";
			$this->content[0]['description'] = "Fatal Error: could not open XML input (" . $file . ")";
			$this->content[0]['link'] = "#";
			$this->content[0]['date'] = time();
		}

		preg_replace_callback( "#encoding=\"(.+?)\"#i", array( &$this, 'get_charset'), $data );

		libxml_use_internal_errors(true);
		$data = str_replace("content:encoded>","content>",$data);
		$data = preg_replace( "#content:encoded(.+?)>#i", "content>", $data );
		$xml = simplexml_load_string($data);

		if ($xml) {

			$i = 0;
			if ( $xml->channel->item ) {
		
				foreach ($xml->channel->item as $item) {

					if ( $item->title ) $this->content[$i]['title'] = (string)$item->title;
					if ( $item->description ) $this->content[$i]['description'] = (string)$item->description;
					if ( $item->link ) $this->content[$i]['link'] = (string)$item->link;
					if ( $item->pubDate ) $this->content[$i]['date'] = (string)$item->pubDate;
					if ( $item->category ) $this->content[$i]['category'] = (string)$item->category;
					if ( $item->content ) $this->content[$i]['content'] = (string)$item->content;


    				$dc = $item->children("http://purl.org/dc/elements/1.1/");
					if ( $dc->creator ) $this->content[$i]['author'] = (string)$dc->creator;


					$i ++;
					if ( $i == $this->max_news ) break;

				} 
			
			} else {

				$atom = $xml->children('http://www.w3.org/2005/Atom');

				foreach ($atom->entry as $item) {

					if ( $item->title ) $this->content[$i]['title'] = (string)$item->title;
					if ( $item->summary ) $this->content[$i]['description'] = (string)$item->summary;
					if ( $item->link ) $this->content[$i]['link'] = (string)$item->link->attributes()->href;
					if ( $item->published ) $this->content[$i]['date'] = (string)$item->published;
					if ( $item->updated ) $this->content[$i]['date'] = (string)$item->updated;
					if ( $item->category ) $this->content[$i]['category'] = (string)$item->category;


					if ( $item->author->name ) $this->content[$i]['author'] = (string)$item->author->name;

					if ( $item->content ) {

						$details = $item->children('http://www.w3.org/2005/Atom');

						$this->content[$i]['content'] = $details->content->asXML();

						$this->content[$i]['content'] = str_replace("</content>", "", $this->content[$i]['content']);
						$this->content[$i]['content'] = preg_replace("#<content[^>]*>#", "", $this->content[$i]['content']);
						$this->content[$i]['content'] = str_replace("&lt;", "<", $this->content[$i]['content']);
						$this->content[$i]['content'] = str_replace("&gt;", ">", $this->content[$i]['content']);
						$this->content[$i]['content'] = str_replace("&amp;", "&", $this->content[$i]['content']);

					}


					$i ++;
					if ( $i == $this->max_news ) break;

				}

			}

			$this->convert();

		} else {

			    $errors = libxml_get_errors();


				$this->content[0]['title'] = "XML error in File: " . $file;
				$this->content[0]['description'] = sprintf( "XML error: %s at line %d", $errors[0]->message, $errors[0]->line );
				$this->content[0]['link'] = "#";
				$this->content[0]['date'] = time();

			    libxml_clear_errors();
		}

		
	}
	
	function _get_contents($file) {
		
		$data = false;

		if (stripos($file, "http://") !== 0 AND stripos($file, "https://") !== 0) {
			return false;
		}

		if( function_exists( 'curl_init' ) ) {
			
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $file );
			curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
			
			$data = curl_exec( $ch );
			curl_close( $ch );

			if( $data ) return $data;
			else return false;
		
		} else {

			$data = @file_get_contents( $file );
			
			if( $data ) return $data;
			else return false;

		}
	
	}
	
	function pre_parse($date) {
		
		global $config;
		
		$i = 0;
		
		foreach ( $this->content as $content ) {
			
			$content_date = strtotime( $content['date'] );
			
			if( $date ) {
				$this->content[$i]['date'] = time();
			} else {
				$this->content[$i]['date'] = $content_date;
			}
			
			if( ! $i ) $this->lastdate = $content_date;
			
			if( $i and $content_date > $this->lastdate ) $this->lastdate = $content_date;
			
			if( $this->pre_lastdate != "" and $this->pre_lastdate >= $content_date ) {
				unset( $this->content[$i] );
				$i ++;
				continue;
			}
			
			$this->content[$i]['description'] = trim( $this->content[$i]['description'] );
			$this->content[$i]['content'] = trim( $this->content[$i]['content'] );
			
			if( $this->content[$i]['content'] != '' ) {
				$this->content[$i]['description'] = $this->content[$i]['content'];
			}
			unset( $this->content[$i]['content'] );
			
			if( preg_match_all( "#<div id=\'news-id-(.+?)\'>#si", $this->content[$i]['description'], $out ) ) {
				
				$this->content[$i]['description'] = preg_replace( "#<div id=\'news-id-(.+?)\'>#si", "", $this->content[$i]['description'] );
				$this->content[$i]['description'] = dle_substr( $this->content[$i]['description'], 0, - 6, $config['charset'] );
			
			}
			
			$i ++;
		}
	
	}
	
	function get_charset($matches=array()) {
	
		if( $this->rss_charset == '' ) $this->rss_charset = strtolower( $matches[1] );
	
	}	
	
	function convert() {

		global $config;

		$to = strtolower($config['charset']);
		
		if( $to == "utf-8" ) return;

		if( function_exists( 'mb_convert_encoding' ) ) {

			$i = 0;
			
			foreach ( $this->content as $content ) {
				
				if( $this->content[$i]['title'] ) $this->content[$i]['title'] = mb_convert_encoding( $this->content[$i]['title'], $to, "UTF-8" );
				
				if( $this->content[$i]['description'] ) $this->content[$i]['description'] = mb_convert_encoding($this->content[$i]['description'], $to, "UTF-8" );
				
				if( $this->content[$i]['content'] ) $this->content[$i]['content'] = mb_convert_encoding($this->content[$i]['content'], $to, "UTF-8" );
				
				if( $this->content[$i]['category'] ) $this->content[$i]['category'] = mb_convert_encoding($this->content[$i]['category'], $to, "UTF-8" );
				
				if( $this->content[$i]['author'] ) $this->content[$i]['author'] = mb_convert_encoding($this->content[$i]['author'], $to, "UTF-8" );
				
				$i ++;
			
			}

		} elseif( function_exists( 'iconv' ) ) {

			$i = 0;
			
			foreach ( $this->content as $content ) {
				
				if( @iconv( "UTF-8", $to . "//IGNORE", $this->content[$i]['title'] ) ) $this->content[$i]['title'] = @iconv( "UTF-8", $to . "//IGNORE", $this->content[$i]['title'] );
				
				if( @iconv( "UTF-8", $to . "//IGNORE", $this->content[$i]['description'] ) ) $this->content[$i]['description'] = @iconv( "UTF-8", $to . "//IGNORE", $this->content[$i]['description'] );
				
				if( $this->content[$i]['content'] and @iconv( "UTF-8", $to . "//IGNORE", $this->content[$i]['content'] ) ) $this->content[$i]['content'] = @iconv( "UTF-8", $to . "//IGNORE", $this->content[$i]['content'] );
				
				if( $this->content[$i]['category'] and @iconv( "UTF-8", $to . "//IGNORE", $this->content[$i]['category'] ) ) $this->content[$i]['category'] = @iconv( "UTF-8", $to . "//IGNORE", $this->content[$i]['category'] );
				
				if( $this->content[$i]['author'] and @iconv( "UTF-8", $to . "//IGNORE", $this->content[$i]['author'] ) ) $this->content[$i]['author'] = @iconv( "UTF-8", $to . "//IGNORE", $this->content[$i]['author'] );
				
				$i ++;
			
			}
		}
	}
}

?>