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
 Файл: templates.class.php
-----------------------------------------------------
 Назначение: Парсинг шаблонов
=====================================================
*/

if (! defined ( 'DATALIFEENGINE' )) {
	die ( "Hacking attempt!" );
}

@ini_set('pcre.recursion_limit', 10000000 );
@ini_set('pcre.backtrack_limit', 10000000 );
@ini_set('pcre.jit', false);

require_once ROOT_DIR . '/engine/classes/mobiledetect.class.php';

class dle_template {
	
	var $dir = '';
	var $template = null;
	var $copy_template = null;
	var $desktop = true;
	var $smartphone = false;
	var $tablet = false;
	var $data = array ();
	var $block_data = array ();
	var $result = array ('info' => '', 'vote' => '', 'speedbar' => '', 'content' => '' );
	var $allow_php_include = true;
	var $include_mode = 'tpl';
	
	var $template_parse_time = 0;

    function __construct(){

		$this->dir = ROOT_DIR . '/templates/';

		$mobile_detect = new Mobile_Detect;

		if ( $mobile_detect->isMobile() ) {
			$this->smartphone = true;
			$this->desktop = false;
		}

		if ( $mobile_detect->isTablet() ) {
			$this->smartphone = false;
			$this->desktop = false;
			$this->tablet = true;
		}
	}
	
	function set($name, $var) {
		if( is_array( $var ) && count( $var ) ) {
			foreach ( $var as $key => $key_var ) {
				$this->set( $key, $key_var );
			}
		} else
			$var = str_ireplace( "{include", "&#123;include",  $var );
			$var = str_ireplace( "{custom", "&#123;custom",  $var );
			$this->data[$name] = $var;
	}
	
	function set_block($name, $var) {
		if( is_array( $var ) && count( $var ) ) {
			foreach ( $var as $key => $key_var ) {
				$this->set_block( $key, $key_var );
			}
		} else
			$var = str_ireplace( "{custom", "&#123;custom",  $var );
			$var = str_ireplace( "{include", "&#123;include",  $var );
			$this->block_data[$name] = $var;
	}
	
	function load_template($tpl_name) {

		$time_before = $this->get_real_time();
		
		$tpl_name = str_replace(chr(0), '', $tpl_name);

		$url = @parse_url ( $tpl_name );

		$file_path = dirname ($this->clear_url_dir($url['path']));
		$tpl_name = pathinfo($url['path']);

		$tpl_name = totranslit($tpl_name['basename']);
		$type = explode( ".", $tpl_name );
		$type = strtolower( end( $type ) );

		if ($type != "tpl") {
			$this->template = "Not Allowed Template Name: " .str_replace(ROOT_DIR, '', $this->dir)."/".$tpl_name ;
			$this->copy_template = $this->template;
			return "";

		}

		if ($file_path AND $file_path != ".") $tpl_name = $file_path."/".$tpl_name;

		if( stripos ( $tpl_name, ".php" ) !== false ) {
			$this->template = "Not Allowed Template Name: " .str_replace(ROOT_DIR, '', $this->dir)."/".$tpl_name ;
			$this->copy_template = $this->template;
			return "";
		}

		if( $tpl_name == '' || !file_exists( $this->dir . "/" . $tpl_name ) ) {
			$this->template = "Template not found: " .str_replace(ROOT_DIR, '', $this->dir)."/".$tpl_name ;
			$this->copy_template = $this->template;
			return "";
		}

		$this->template = file_get_contents( $this->dir . "/" . $tpl_name );
		
		if (strpos ( $this->template, "{*" ) !== false) {
			$this->template = preg_replace("'\\{\\*(.*?)\\*\\}'si", '', $this->template);
		}

		$this->template = $this->check_module($this->template);
		
		if (strpos ( $this->template, "[group=" ) !== false OR strpos ( $this->template, "[not-group=" ) !== false) {
			$this->template = $this->check_group($this->template);
		}
		
		if (strpos ( $this->template, "[page-count=" ) !== false) {
			$this->template = preg_replace_callback ( "#\\[(page-count)=(.+?)\\](.*?)\\[/page-count\\]#is", array( &$this, 'check_page'), $this->template );
		}

		if (strpos ( $this->template, "[not-page-count=" ) !== false) {
			$this->template = preg_replace_callback ( "#\\[(not-page-count)=(.+?)\\](.*?)\\[/not-page-count\\]#is", array( &$this, 'check_page'), $this->template );
		}

		if (strpos ( $this->template, "[tags=" ) !== false) {
			$this->template = preg_replace_callback ( "#\\[(tags)=(.+?)\\](.*?)\\[/tags\\]#is", array( &$this, 'check_tag'), $this->template );
		}


		if (strpos ( $this->template, "[not-tags=" ) !== false) {
			$this->template = preg_replace_callback ( "#\\[(not-tags)=(.+?)\\](.*?)\\[/not-tags\\]#is", array( &$this, 'check_tag'), $this->template );
		}

		if (strpos ( $this->template, "[news=" ) !== false) {
			$this->template = preg_replace_callback ( "#\\[(news)=(.+?)\\](.*?)\\[/news\\]#is", array( &$this, 'check_tag'), $this->template );
		}

		if (strpos ( $this->template, "[not-news=" ) !== false) {
			$this->template = preg_replace_callback ( "#\\[(not-news)=(.+?)\\](.*?)\\[/not-news\\]#is", array( &$this, 'check_tag'), $this->template );
		}

		if (strpos ( $this->template, "[smartphone]" ) !== false) {
			$this->template = preg_replace_callback ( "#\\[(smartphone)\\](.*?)\\[/smartphone\\]#is", array( &$this, 'check_device'), $this->template );
		}

		if (strpos ( $this->template, "[not-smartphone]" ) !== false) {
			$this->template = preg_replace_callback ( "#\\[(not-smartphone)\\](.*?)\\[/not-smartphone\\]#is", array( &$this, 'check_device'), $this->template );
		}

		if (strpos ( $this->template, "[tablet]" ) !== false) {
			$this->template = preg_replace_callback ( "#\\[(tablet)\\](.*?)\\[/tablet\\]#is", array( &$this, 'check_device'), $this->template );
		}

		if (strpos ( $this->template, "[not-tablet]" ) !== false) {
			$this->template = preg_replace_callback ( "#\\[(not-tablet)\\](.*?)\\[/not-tablet\\]#is", array( &$this, 'check_device'), $this->template );
		}

		if (strpos ( $this->template, "[desktop]" ) !== false) {
			$this->template = preg_replace_callback ( "#\\[(desktop)\\](.*?)\\[/desktop\\]#is", array( &$this, 'check_device'), $this->template );
		}

		if (strpos ( $this->template, "[not-desktop]" ) !== false) {
			$this->template = preg_replace_callback ( "#\\[(not-desktop)\\](.*?)\\[/not-desktop\\]#is", array( &$this, 'check_device'), $this->template );
		}

		if( strpos( $this->template, "{include file=" ) !== false ) {
			$this->include_mode = 'tpl';			
			$this->template = preg_replace_callback( "#\\{include file=['\"](.+?)['\"]\\}#i", array( &$this, 'load_file'), $this->template );
		
		}

		$this->copy_template = $this->template;
		
		$this->template_parse_time += $this->get_real_time() - $time_before;
		return true;
	}

	function load_file( $matches=array() ) {
		global $db, $is_logged, $member_id, $cat_info, $config, $user_group, $category_id, $_TIME, $lang, $smartphone_detected, $dle_module;

		$name = $matches[1];

		$name = str_replace( chr(0), "", $name );
		$name = str_replace( '..', '', $name );

		$url = @parse_url ($name);
		$type = explode( ".", $url['path'] );
		$type = strtolower( end( $type ) );

		if ($type == "tpl") {

			return $this->sub_load_template( $name );

		}

		if ($this->include_mode == "php") {

			if ( !$this->allow_php_include ) return;

			if ($type != "php") return "To connect permitted only files with the extension: .tpl or .php";

			if ($url['path']{0} == "/" )
				$file_path = dirname (ROOT_DIR.$url['path']);
			else
				$file_path = dirname (ROOT_DIR."/".$url['path']);

			$file_name = pathinfo($url['path']);
			$file_name = $file_name['basename'];

			if ( stristr ( php_uname( "s" ) , "windows" ) === false )
				$chmod_value = @decoct(@fileperms($file_path)) % 1000;

			if ( stristr ( dirname ($url['path']) , "uploads" ) !== false )
				return "Include files from directory /uploads/ is denied";

			if ( stristr ( dirname ($url['path']) , "templates" ) !== false )
				return "Include files from directory /templates/ is denied";

			if ( stristr ( dirname ($url['path']) , "engine/data" ) !== false )
				return "Include files from directory /engine/data/ is denied";

			if ( stristr ( dirname ($url['path']) , "engine/cache" ) !== false )
				return "Include files from directory /engine/cache/ is denied";

			if ( stristr ( dirname ($url['path']) , "engine/inc" ) !== false )
				return "Include files from directory /engine/inc/ is denied";

			if ($chmod_value == 777 ) return "File {$url['path']} is in the folder, which is available to write (CHMOD 777). For security purposes the connection files from these folders is impossible. Change the permissions on the folder that it had no rights to the write.";

			if ( !file_exists($file_path."/".$file_name) ) return "File {$url['path']} not found.";

			$url['query'] = str_ireplace(array("file_path","file_name", "dle_login_hash", "_GET","_FILES","_POST","_REQUEST","_SERVER","_COOKIE","_SESSION") ,"Filtered", $url['query'] );

			if( substr_count ($this->template, "{include file=") < substr_count ($this->copy_template, "{include file=")) return "Filtered";

			if ( isset($url['query']) AND $url['query'] ) {

				$module_params = array();

				parse_str( $url['query'], $module_params );

				extract($module_params, EXTR_SKIP);

				unset($module_params);
				

			}

			ob_start();
			$tpl = new dle_template();
			$tpl->dir = TEMPLATE_DIR;
			include $file_path."/".$file_name;
			return ob_get_clean();

		}

		return '{include file="'.$name.'"}';


	}
	
	function sub_load_template( $tpl_name ) {
		
		$tpl_name = str_replace(chr(0), '', $tpl_name);
		
		$url = @parse_url ( $tpl_name );

		$file_path = dirname ($this->clear_url_dir($url['path']));
		$tpl_name = pathinfo($url['path']);
		$tpl_name = totranslit($tpl_name['basename']);
		$type = explode( ".", $tpl_name );
		$type = strtolower( end( $type ) );

		if ($type != "tpl") {

			return "Not Allowed Template Name: ". $tpl_name;

		}

		if ($file_path AND $file_path != ".") $tpl_name = $file_path."/".$tpl_name;

		if (strpos($tpl_name, '/templates/') === 0) {

			$tpl_name = str_replace('/templates/','',$tpl_name);
			$templatefile = ROOT_DIR . '/templates/'.$tpl_name;

		} else $templatefile = $this->dir . "/" . $tpl_name;

		if( $tpl_name == '' || !file_exists( $templatefile ) ) {

			$templatefile = str_replace(ROOT_DIR,'',$templatefile);
			return "Template not found: " . $templatefile ;
			return false;

		}

		if( stripos ( $templatefile, ".php" ) !== false ) return "Not Allowed Template Name: ". $tpl_name;

		$template = file_get_contents( $templatefile );

		if (strpos ( $template, "{*" ) !== false) {
			$template = preg_replace("'\\{\\*(.*?)\\*\\}'si", '', $template);
		}

		$template = $this->check_module($template);
		
		if (strpos ( $template, "[group=" ) !== false OR strpos ( $template, "[not-group=" ) !== false ) {
			$template = $this->check_group($template);
		}

		if (strpos ( $template, "[page-count=" ) !== false) {
			$template = preg_replace_callback ( "#\\[(page-count)=(.+?)\\](.*?)\\[/page-count\\]#is", array( &$this, 'check_page'), $template );
		}

		if (strpos ( $template, "[not-page-count=" ) !== false) {
			$template = preg_replace_callback ( "#\\[(not-page-count)=(.+?)\\](.*?)\\[/not-page-count\\]#is", array( &$this, 'check_page'), $template );
		}

		if (strpos ( $template, "[tags=" ) !== false) {
			$template = preg_replace_callback ( "#\\[(tags)=(.+?)\\](.*?)\\[/tags\\]#is", array( &$this, 'check_tag'), $template );
		}


		if (strpos ( $template, "[not-tags=" ) !== false) {
			$template = preg_replace_callback ( "#\\[(not-tags)=(.+?)\\](.*?)\\[/not-tags\\]#is", array( &$this, 'check_tag'), $template );
		}

		if (strpos ( $template, "[news=" ) !== false) {
			$template = preg_replace_callback ( "#\\[(news)=(.+?)\\](.*?)\\[/news\\]#is", array( &$this, 'check_tag'), $template );
		}


		if (strpos ( $template, "[not-news=" ) !== false) {
			$template = preg_replace_callback ( "#\\[(not-news)=(.+?)\\](.*?)\\[/not-news\\]#is", array( &$this, 'check_tag'), $template );
		}

		if (strpos ( $template, "[smartphone]" ) !== false) {
			$template = preg_replace_callback ( "#\\[(smartphone)\\](.*?)\\[/smartphone\\]#is", array( &$this, 'check_device'), $template );
		}

		if (strpos ( $template, "[not-smartphone]" ) !== false) {
			$template = preg_replace_callback ( "#\\[(not-smartphone)\\](.*?)\\[/not-smartphone\\]#is", array( &$this, 'check_device'), $template );
		}

		if (strpos ( $template, "[tablet]" ) !== false) {
			$template = preg_replace_callback ( "#\\[(tablet)\\](.*?)\\[/tablet\\]#is", array( &$this, 'check_device'), $template );
		}

		if (strpos ( $template, "[not-tablet]" ) !== false) {
			$template = preg_replace_callback ( "#\\[(not-tablet)\\](.*?)\\[/not-tablet\\]#is", array( &$this, 'check_device'), $template );
		}

		if (strpos ( $template, "[desktop]" ) !== false) {
			$template = preg_replace_callback ( "#\\[(desktop)\\](.*?)\\[/desktop\\]#is", array( &$this, 'check_device'), $template );
		}

		if (strpos ( $template, "[not-desktop]" ) !== false) {
			$template = preg_replace_callback ( "#\\[(not-desktop)\\](.*?)\\[/not-desktop\\]#is", array( &$this, 'check_device'), $template );
		}
		
		return $template;
	}

	function clear_url_dir($var) {
		if ( is_array($var) ) return "";
	
		$var = str_ireplace( ".php", "", $var );
		$var = str_ireplace( ".php", ".ppp", $var );
		$var = trim( strip_tags( $var ) );
		$var = str_replace( "\\", "/", $var );
		$var = preg_replace( "/[^a-z0-9\/\_\-]+/mi", "", $var );
		$var = preg_replace( '#[\/]+#i', '/', $var );

		return $var;
	
	}

	function check_module($matches) {
		global $dle_module;
		
		$regex = '/\[(aviable|available|not-aviable|not-available)=(.+?)\]((?>(?R)|.)*?)\[\/\1\]/is';

		if (is_array($matches)) {
			
			$aviable = $matches[2];
			$block = $matches[3];
			
			if ($matches[1] == "aviable" OR $matches[1] == "available") $action = true; else $action = false;
			
			$aviable = explode( '|', $aviable );
			
			if( $action ) {
				
				if( ! (in_array( $dle_module, $aviable )) and ($aviable[0] != "global") ) $matches = '';
				else $matches = $block;
			
			} else {
				
				if( (in_array( $dle_module, $aviable )) ) $matches = '';
				else $matches = $block;
			}		
	
		}
	
		return preg_replace_callback($regex, array( &$this, 'check_module'), $matches);
	}

	function check_group( $matches ) {
		global $member_id;

		$regex = '/\[(group|not-group)=(.+?)\]((?>(?R)|.)*?)\[\/\1\]/is';

		if (is_array($matches)) {

			$groups = $matches[2];
			$block = $matches[3];
	
			if ($matches[1] == "group") $action = true; else $action = false;
			
			$groups = explode( ',', $groups );
			
			if( $action ) {
				
				if( ! in_array( $member_id['user_group'], $groups ) ) $matches = ''; else $matches = $block;
			
			} else {
				
				if( in_array( $member_id['user_group'], $groups ) ) $matches = ''; else $matches = $block;
			
			}
		}
		
		return preg_replace_callback($regex, array( &$this, 'check_group'), $matches);
	
	}

	function check_device( $matches=array() ) {

		$block = $matches[2];
		$device = $this->desktop;

		if ($matches[1] == "smartphone" OR $matches[1] == "tablet" OR $matches[1] == "desktop") $action = true; else $action = false;
		if ($matches[1] == "smartphone" OR $matches[1] == "not-smartphone") $device = $this->smartphone;
		if ($matches[1] == "tablet" OR $matches[1] == "not-tablet") $device = $this->tablet;

		if( $action ) {
			
			if( !$device ) return "";
		
		} else {
			
			if( $device ) return "";
		
		}

		return $block;
	}

	function declination( $matches=array() ) {
		
	    $matches[1] = str_replace(' ', '', $matches[1] );
		$matches[1] = intval($matches[1]);
		$words = explode('|', trim($matches[2]));
		$parts_word = array();

		switch ( count($words) ) {
			case 1:
				$parts_word[0] = $words[0];
				$parts_word[1] = $words[0];
				$parts_word[2] = $words[0];
				break;
			case 2:
				$parts_word[0] = $words[0];
				$parts_word[1] = $words[0].$words[1];
				$parts_word[2] = $words[0].$words[1];
				break;
			case 3: 
				$parts_word[0] = $words[0];
				$parts_word[1] = $words[0].$words[1];
				$parts_word[2] = $words[0].$words[2];
				break;
			case 4: 
				$parts_word[0] = $words[0].$words[1];
				$parts_word[1] = $words[0].$words[2];
				$parts_word[2] = $words[0].$words[3];
				break;
		}
	
		$word = $matches[1]%10==1&&$matches[1]%100!=11?$parts_word[0]:($matches[1]%10>=2&&$matches[1]%10<=4&&($matches[1]%100<10||$matches[1]%100>=20)?$parts_word[1]:$parts_word[2]);
	
		return $word;
	}

	function check_page( $matches=array() ) {

		$pages = $matches[2];
		$block = $matches[3];

		if ($matches[1] == "page-count") $action = true; else $action = false;
	
		$pages = explode( ',', $pages );
		$page = intval($_GET['cstart']);

		if ( $page < 1 ) $page = 1;
		
		if( $action ) {
			
			if( !in_array( $page, $pages ) ) return "";
		
		} else {
			
			if( in_array( $page, $pages ) ) return "";
		
		}
		
		return $block;
	
	}

	function check_tag( $matches=array() ) {
		global $config;

		$params = $matches[2];
		$block = $matches[3];

		if ($matches[1] == "tags" OR $matches[1] == "news") $action = true; else $action = false;
		if ($matches[1] == "tags" OR $matches[1] == "not-tags") $tag = "tags";
		if ($matches[1] == "news" OR $matches[1] == "not-news") $tag = "news";
	
		$props = "";
		$params = trim($params);

		if ( $tag == "news" ) {

			if( defined( 'NEWS_ID' ) ) $props = NEWS_ID;
			$params = explode( ',', $params);
		
		} elseif ( $tag == "tags" ) {
		
			if( defined( 'CLOUDSTAG' ) ) {

				if( function_exists('mb_strtolower') ) {

					$params = mb_strtolower($params, $config['charset']);
					$props = trim(mb_strtolower(CLOUDSTAG, $config['charset']));

				} else {

					$params = strtolower($params);
					$props = trim(strtolower(CLOUDSTAG));

				}

			}

			$params = explode( ',', $params);

		
		} else return "";

		
		if( $action ) {
			
			if( !in_array( $props, $params ) ) return "";
		
		} else {
			
			if( in_array( $props, $params ) ) return "";
		
		}
		
		return $block;
	
	}
	
	function _clear() {
		
		$this->data = array ();
		$this->block_data = array ();
		$this->copy_template = $this->template;
	
	}
	
	function clear() {
		
		$this->data = array ();
		$this->block_data = array ();
		$this->copy_template = null;
		$this->template = null;
	
	}
	
	function global_clear() {
		
		$this->data = array ();
		$this->block_data = array ();
		$this->result = array ();
		$this->copy_template = null;
		$this->template = null;
	
	}
	
	function compile($tpl) {
		
		$time_before = $this->get_real_time();
		
		if( count( $this->block_data ) ) {
			foreach ( $this->block_data as $key_find => $key_replace ) {
				$find_preg[] = $key_find;
				$replace_preg[] = $key_replace;
			}
			
			$this->copy_template = preg_replace( $find_preg, $replace_preg, $this->copy_template );
		}

		foreach ( $this->data as $key_find => $key_replace ) {
			$find[] = $key_find;
			$replace[] = $key_replace;
		}
		
		$this->copy_template = str_ireplace( $find, $replace, $this->copy_template );
		
		if (strpos ( $this->copy_template, "[declination=" ) !== false) {
			$this->copy_template = preg_replace_callback ( "#\\[declination=(.+?)\\](.+?)\\[/declination\\]#is", array( &$this, 'declination'), $this->copy_template );
		}
		
		if( strpos( $this->copy_template, "{customcomments" ) !== false ) {		
			$this->copy_template = preg_replace_callback( "#\\{customcomments(.+?)\\}#i", "custom_comments", $this->copy_template );
		
		}
		
		if( strpos( $this->copy_template, "{custom" ) !== false ) {		
			$this->copy_template = preg_replace_callback( "#\\{custom(.+?)\\}#i", "custom_print", $this->copy_template );
		
		}
		
		if( strpos( $this->template, "{include file=" ) !== false ) {
			$this->include_mode = 'php';			
			$this->copy_template = preg_replace_callback( "#\\{include file=['\"](.+?)['\"]\\}#i", array( &$this, 'load_file'), $this->copy_template );
		
		}
		
		if( isset( $this->result[$tpl] ) ) $this->result[$tpl] .= $this->copy_template;
		else $this->result[$tpl] = $this->copy_template;
		
		$this->_clear();
		
		$this->template_parse_time += $this->get_real_time() - $time_before;
	}
	
	function get_real_time() {
		list ( $seconds, $microSeconds ) = explode( ' ', microtime() );
		return (( float ) $seconds + ( float ) $microSeconds);
	}
}
?>