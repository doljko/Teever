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
Файл: rssinform.php
-----------------------------------------------------
Назначение: Вывод RSS информеров
=====================================================
*/

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

//################# Определение информеров
$informers = get_vars( "informers" );

if( ! is_array( $informers ) ) {
	$informers = array ();
	
	$db->query( "SELECT * FROM " . PREFIX . "_rssinform ORDER BY id ASC" );
	
	while ( $row_b = $db->get_row() ) {
		
		$informers[$row_b['id']] = array ();
		
		foreach ( $row_b as $key => $value ) {
			$informers[$row_b['id']][$key] = stripslashes( $value );
		}
	
	}
	set_vars( "informers", $informers );
	$db->free();
}

$allow_cache = $config['allow_cache'];
$config['allow_cache'] = 1;
$temp = array ();
$i = 0;

if( count( $informers ) ) {
	foreach ( $informers as $name => $value ) {
		if( $value['approve'] ) { //если активный
			

			if( $value['category'] ) {
				$value['category'] = explode( ',', $value['category'] );
				
				if( ! in_array( $category_id, $value['category'] ) ) $value['url'] = "";
			}
			
			$temp[$value['tag']][$i]['id'] = $value['id'];
			$temp[$value['tag']][$i]['tag'] = $value['tag'];
			$temp[$value['tag']][$i]['url'] = $value['url'];
			$temp[$value['tag']][$i]['template'] = $value['template'];
			$temp[$value['tag']][$i]['news_max'] = $value['news_max'];
			$temp[$value['tag']][$i]['tmax'] = $value['tmax'];
			$temp[$value['tag']][$i]['dmax'] = $value['dmax'];
			$temp[$value['tag']][$i]['rss_date_format'] = $value['rss_date_format'];
		
		}
		$i ++;
	}
	
	foreach ( $temp as $key => $value ) {
		
		$r_key = array_rand( $temp[$key] );
		$temp[$key] = $temp[$key][$r_key];
	
	}
	
	$informers = array ();
	
	foreach ( $temp as $key => $value ) {
		
		if( $value['url'] == "" ) {
			
			$informers[$value['tag']] = "";
			continue;
		
		}
		
		$buffer = dle_cache( "informer_" . $value['id'], $config['skin'] );

		if ( $buffer !== false) {

			$file_date = @filemtime( ENGINE_DIR.'/cache/informer_'.$value['id'].'_'.md5($config['skin']).'.tmp' );

			if ( $file_date ) {

				if (date ( "d-H", $file_date ) != date ( "d-H" )) {

					$buffer = false;
					@unlink( ENGINE_DIR.'/cache/informer_'.$value['id'].'_'.md5($config['skin']).'.tmp' );

				}

			}

		}
		
		if( $buffer === false ) {

			include_once ENGINE_DIR . '/classes/rss.class.php';
			
			$xml = new xmlParser( stripslashes( $value['url'] ), $value['news_max'] );
						
			$xml->pre_parse( 0 );

			$tpl->load_template( $value['template'] . '.tpl' );
			
			foreach ( $xml->content as $content ) {
				$content['title'] = trim(strip_tags( $content['title'] ));
				$content['category'] = trim( strip_tags( $content['category'] ) );
				$content['author'] = trim( strip_tags( $content['author'] ) );
				
				if( $value['tmax'] and dle_strlen( $content['title'], $config['charset'] ) > $value['tmax'] ) $content['title'] = dle_substr( $content['title'], 0, $value['tmax'], $config['charset'] ) . " ...";
				
				if (stripos ( $tpl->copy_template, "{image-" ) !== false) {
		
					$images = array();
					preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $content['description'], $media);
					$data=preg_replace('/(img|src)("|\'|="|=\')(.*)/i',"$3",$media[0]);
		
					foreach($data as $url) {
						$info = pathinfo($url);
						if (isset($info['extension'])) {
							if ($info['filename'] == "spoiler-plus" OR $info['filename'] == "spoiler-minus" ) continue;
							$info['extension'] = strtolower($info['extension']);
							if (($info['extension'] == 'jpg') || ($info['extension'] == 'jpeg') || ($info['extension'] == 'gif') || ($info['extension'] == 'png')) array_push($images, $url);
						}
					}
		
					if ( count($images) ) {
						$i=0;
						foreach($images as $url) {
							$i++;
							$tpl->copy_template = str_replace( '{image-'.$i.'}', $url, $tpl->copy_template );
							$tpl->copy_template = str_replace( '[image-'.$i.']', "", $tpl->copy_template );
							$tpl->copy_template = str_replace( '[/image-'.$i.']', "", $tpl->copy_template );
						}
		
					}
		
					$tpl->copy_template = preg_replace( "#\[image-(.+?)\](.+?)\[/image-(.+?)\]#is", "", $tpl->copy_template );
					$tpl->copy_template = preg_replace( "#\\{image-(.+?)\\}#i", "{THEME}/dleimages/no_image.jpg", $tpl->copy_template );
		
				}

				$content['description'] = strip_tags( $content['description'], "<br>" );
				$content['description'] = str_replace( "<br>", " ", str_replace( "<br />", " ", $content['description'] ) );
				$content['description'] = trim( $content['description'] );
				
				if( $value['dmax'] and dle_strlen( $content['description'], $config['charset'] ) > $value['dmax'] ) {
					
					$content['description'] = dle_substr( $content['description'], 0, $value['dmax'], $config['charset'] );
					
					if( ($temp_dmax = dle_strrpos( $content['description'], ' ', $config['charset'] )) ) $content['description'] = dle_substr( $content['description'], 0, $temp_dmax, $config['charset'] );
					
					$content['description'] .= " ...";
				
				}

				$content['link'] = htmlspecialchars ($content['link'], ENT_QUOTES, $config['charset']);

				$tpl->set( '{title}', $content['title'] );
				$tpl->set( '{news}', $content['description'] );
				$tpl->set( '[link]', "<a href=\"{$content['link']}\" target=\"_blank\">" );
				$tpl->set( '[/link]', "</a>" );
				$tpl->set( '{link}', $content['link'] );
				$tpl->set( '{category}', $content['category'] );
				$tpl->set( '{author}', $content['author'] );
				$tpl->set( '{date}', langdate( $value['rss_date_format'], $content['date'] ) );

				$news_date = $content['date'];
				$tpl->copy_template = preg_replace_callback ( "#\{date=(.+?)\}#i", "formdate", $tpl->copy_template );
				
				$tpl->compile( 'rss_info' );
				
			}
			
			
			$buffer = $tpl->result['rss_info'];
			$tpl->result['rss_info'] = "";
			$tpl->clear();

			create_cache( "informer_" . $value['id'], $buffer, $config['skin'] );
		
		}
		
		$informers[$value['tag']] = $buffer;
	
	}

}

$temp = array ();
unset( $temp );
$config['allow_cache'] = $allow_cache;

?>