<?PHP
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
 Файл: functions.inc.php
-----------------------------------------------------
 Назначение: Основные функции
=====================================================
*/
if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

if ( $config['auth_domain'] ) {

	$domain_cookie = explode (".", clean_url( $_SERVER['HTTP_HOST'] ));
	$domain_cookie_count = count($domain_cookie);
	$domain_allow_count = -2;
	
	if ( $domain_cookie_count > 2 ) {
	
		if ( in_array($domain_cookie[$domain_cookie_count-2], array('com', 'net', 'org') )) $domain_allow_count = -3;
		if ( $domain_cookie[$domain_cookie_count-1] == 'ua' ) $domain_allow_count = -3;
		$domain_cookie = array_slice($domain_cookie, $domain_allow_count);
	}
	
	$domain_cookie = "." . implode (".", $domain_cookie);
	
	if( (ip2long($_SERVER['HTTP_HOST']) == -1 OR ip2long($_SERVER['HTTP_HOST']) === FALSE) AND strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' ) define( 'DOMAIN', $domain_cookie );
	else define( 'DOMAIN', null );

} else define( 'DOMAIN', null );

function dle_session( $sid = false ) {

	$params = session_get_cookie_params();

	if ( DOMAIN ) $params['domain'] = DOMAIN;

	if( version_compare(PHP_VERSION, '5.2', '<') ) {
		
		session_set_cookie_params($params['lifetime'], "/", $params['domain']."; HttpOnly", $params['secure']);
	
	} else {
		
		session_set_cookie_params($params['lifetime'], "/", $params['domain'], $params['secure'], true);
	
	}

	if ( $sid ) @session_id( $sid );

	@session_start();

}

function check_login($username, $md5_password, $post = true, $check_log = false) {
	global $member_id, $db, $user_group, $lang, $_IP, $_TIME, $config;

	if( $username == "" OR $md5_password == "" ) return false;
	
	$result = false;
	
	if( $post ) {
		
		$username = $db->safesql( $username );
		$md5_password = md5( $md5_password );

		if ($config['auth_metod']) {

			if ( preg_match( "/[\||\'|\<|\>|\"|\!|\?|\$|\/|\\\|\&\~\*\+]/", $username) ) return false;	
			$where_name = "email='{$username}'";
	
		} else {

			if ( preg_match( "/[\||\'|\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $username) ) return false;
			$where_name = "name='{$username}'";
	
		}

		$member_id = $db->super_query( "SELECT * FROM " . USERPREFIX . "_users WHERE {$where_name} AND password='{$md5_password}'" );
		
		if( $member_id['user_id'] AND $user_group[$member_id['user_group']]['allow_admin'] AND $member_id['banned'] != 'yes' ) $result = TRUE;
		else { 

			$member_id = array ();
	
			$username = $db->safesql(trim( htmlspecialchars( stripslashes($username), ENT_QUOTES, $config['charset'])));
	
			$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$username."', '{$_TIME}', '{$_IP}', '89', '')" );

		}

	} else {
		
		$username = intval( $username );
		$md5_password = md5( $md5_password );
		
		$member_id = $db->super_query( "SELECT * FROM " . USERPREFIX . "_users WHERE user_id='$username'" );
		
		if( $member_id['user_id'] AND $member_id['password'] AND $member_id['password'] == $md5_password AND $user_group[$member_id['user_group']]['allow_admin'] AND $member_id['banned'] != 'yes' ) $result = TRUE;
		else {

			$username = $db->safesql(trim( htmlspecialchars( stripslashes($member_id['name']), ENT_QUOTES, $config['charset'])));

			$member_id = array ();
	
			$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$username."', '{$_TIME}', '{$_IP}', '90', '')" );

		}
	
	}

	if( $result ) {
		
		if( !allowed_ip( $member_id['allowed_ip'] ) OR !allowed_ip( $config['admin_allowed_ip'] ) ) {
			
			$member_id = array ();
			$result = false;
			set_cookie( "dle_user_id", "", 0 );
			set_cookie( "dle_name", "", 0 );
			set_cookie( "dle_password", "", 0 );
			set_cookie( "dle_hash", "", 0 );
			@session_destroy();
			@session_unset();
			set_cookie( session_name(), "", 0 );
			
			msg( "info", $lang['index_msge'], $lang['ip_block'] );
		
		}
	}

	if ( !$result ) { 

		if ($config['login_log']) $db->query( "INSERT INTO " . PREFIX . "_login_log (ip, count, date) VALUES('{$_IP}', '1', '".time()."') ON DUPLICATE KEY UPDATE count=count+1, date='".time()."'" );

	} else {

		if ( $check_log AND !$_SESSION['check_log']) {

			if( $post ) { $a_id = 82; $extr =""; } else { $a_id = 86; if ($_SERVER['HTTP_REFERER']) $extr = $db->safesql(htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES)); else $extr = "Direct DLE Adminpanel"; }

			$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '{$a_id}', '{$extr}')" );
			$_SESSION['check_log'] = 1;
		}

	}

	return $result;
}

function formatsize($file_size) {
	
	if( !$file_size OR $file_size < 1) return '0 b';
	
    $prefix = array("b", "Kb", "Mb", "Gb", "Tb");
    $exp = floor(log($file_size, 1024)) | 0;
	
    return round($file_size / (pow(1024, $exp)), 2).' '.$prefix[$exp];

}

function CheckCanGzip() {
	
	if( headers_sent() || connection_aborted() || ! function_exists( 'ob_gzhandler' ) || ini_get( 'zlib.output_compression' ) ) return 0;
	
	if( strpos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip' ) !== false ) return "x-gzip";
	if( strpos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) !== false ) return "gzip";
	
	return 0;
}

function GzipOut() {
	
	$ENCODING = CheckCanGzip();
	
	if( $ENCODING ) {
		$Contents = ob_get_contents();
		ob_end_clean();
		
		header( "Content-Encoding: $ENCODING" );
		
		$Contents = gzencode( $Contents, 1, FORCE_GZIP );
		echo $Contents;
		
		exit();
	} else {
		//      ob_end_flush(); 
		exit();
	}
}

function allowed_ip($ip_array) {
	
	$ip_array = trim( $ip_array );

	$_IP = get_ip();

	if( $ip_array == "" ) {
		return true;
	}
	
	$ip_array = explode( "|", $ip_array );
	
	$db_ip_split = explode( ".", $_IP );
	
	foreach ( $ip_array as $ip ) {
		
		$ip = trim( $ip );
		
		if( $ip == $_IP ) {
			return true;
		}
		
		if( count(explode ('/', $ip)) == 2 ) {
				
			if( maskmatch($_IP, $ip) ) return true;
				
		} else {
			
			$ip_check_matches = 0;
			$this_ip_split = explode( ".", $ip );
	
			
			for($i_i = 0; $i_i < 4; $i_i ++) {
				if( $this_ip_split[$i_i] == $db_ip_split[$i_i] or $this_ip_split[$i_i] == '*' ) {
					$ip_check_matches += 1;
				}
			
			}
			
			if( $ip_check_matches == 4 ) return true;
		}
	
	}
	
	return false;
}


function  maskmatch($IP, $CIDR) { 
    list ($net, $mask) = explode ('/', $CIDR); 
    return ( ip2long($IP) & ~((1 << (32 - $mask)) - 1) ) == ip2long ($net); 
}

function msg($type, $title, $text, $back = FALSE) {
	global $lang;
	
	if( $back ) {
		$back = "<div class=\"row box-section\"><div class=\"col-md-12 text-center\"><a class=\"btn btn btn-red\" href=\"$back\">$lang[func_msg]</a></div></div>";
	}
	
	if ($title == "error") $title = $lang['addnews_error'];
	
	echoheader( "<i class=\"icon-comment-alt\"></i>".$lang['header_box_title'], $title );
	
	echo <<<HTML
<div class="box">
  <div class="box-header">
    <div class="title">{$title}</div>
  </div>
  <div class="box-content">
	<div class="row box-section">
		<table width="100%">
		    <tr>
		        <td height="100" class="text-center settingstd">{$text}</td>
		    </tr>
		</table>
	</div>
	{$back}
  </div>
</div>
HTML;
	
	echofooter();
	die();
}

function echoheader($header_title, $header_subtitle) {
	global $skin_header, $skin_footer, $member_id, $user_group, $js_array, $config, $lang, $is_loged_in, $mod, $action, $langdate, $db;

	include_once (ENGINE_DIR . '/skins/default.skin.php');
	$skin_header = str_replace( "{js_files}", build_js($js_array), $skin_header );
	
	if( $is_loged_in )
		echo $skin_header;
	else
		echo $skin_not_logged_header;
}

function echofooter() {
	global $is_loged_in, $skin_footer;
	
	echo $skin_footer;

}

function listdir($dir) {
	
	$current_dir = opendir( $dir );
	while ( $entryname = readdir( $current_dir ) ) {
		if( is_dir( "$dir/$entryname" ) and ($entryname != "." and $entryname != "..") ) {
			listdir( "${dir}/${entryname}" );
		} elseif( $entryname != "." and $entryname != ".." ) {
			unlink( "${dir}/${entryname}" );
		}
	}
	@closedir( $current_dir );
	rmdir( ${dir} );
}

function totranslit($var, $lower = true, $punkt = true) {
	global $langtranslit;
	
	if ( is_array($var) ) return "";

	$var = str_replace(chr(0), '', $var);

	if (!is_array ( $langtranslit ) OR !count( $langtranslit ) ) {
		$var = trim( strip_tags( $var ) );

		if ( $punkt ) $var = preg_replace( "/[^a-z0-9\_\-.]+/mi", "", $var );
		else $var = preg_replace( "/[^a-z0-9\_\-]+/mi", "", $var );

		$var = preg_replace( '#[.]+#i', '.', $var );
		$var = str_ireplace( ".php", ".ppp", $var );

		if ( $lower ) $var = strtolower( $var );

		return $var;

	}
	
	$var = trim( strip_tags( $var ) );
	$var = preg_replace( "/\s+/ms", "-", $var );
	$var = str_replace( "/", "-", $var );

	$var = strtr($var, $langtranslit);
	
	if ( $punkt ) $var = preg_replace( "/[^a-z0-9\_\-.]+/mi", "", $var );
	else $var = preg_replace( "/[^a-z0-9\_\-]+/mi", "", $var );

	$var = preg_replace( '#[\-]+#i', '-', $var );
	$var = preg_replace( '#[.]+#i', '.', $var );

	if ( $lower ) $var = strtolower( $var );

	$var = str_ireplace( ".php", "", $var );
	$var = str_ireplace( ".php", ".ppp", $var );
	
	if( strlen( $var ) > 200 ) {
		
		$var = substr( $var, 0, 200 );
		
		if( ($temp_max = strrpos( $var, '-' )) ) $var = substr( $var, 0, $temp_max );
	
	}
	
	return $var;
}


function langdate($format, $stamp, $servertime = false ) {
	global $langdate, $member_id;

	$timezones = array('Pacific/Midway','US/Samoa','US/Hawaii','US/Alaska','US/Pacific','America/Tijuana','US/Arizona','US/Mountain','America/Chihuahua','America/Mazatlan','America/Mexico_City','America/Monterrey','US/Central','US/Eastern','US/East-Indiana','America/Lima','America/Caracas','Canada/Atlantic','America/La_Paz','America/Santiago','Canada/Newfoundland','America/Buenos_Aires','Greenland','Atlantic/Stanley','Atlantic/Azores','Africa/Casablanca','Europe/Dublin','Europe/Lisbon','Europe/London','Europe/Amsterdam','Europe/Belgrade','Europe/Berlin','Europe/Bratislava','Europe/Brussels','Europe/Budapest','Europe/Copenhagen','Europe/Madrid','Europe/Paris','Europe/Prague','Europe/Rome','Europe/Sarajevo','Europe/Stockholm','Europe/Vienna','Europe/Warsaw','Europe/Zagreb','Europe/Athens','Europe/Bucharest','Europe/Helsinki','Europe/Istanbul','Asia/Jerusalem','Europe/Kiev','Europe/Minsk','Europe/Riga','Europe/Sofia','Europe/Tallinn','Europe/Vilnius','Asia/Baghdad','Asia/Kuwait','Africa/Nairobi','Asia/Tehran','Europe/Kaliningrad','Europe/Moscow','Europe/Volgograd','Europe/Samara','Asia/Baku','Asia/Muscat','Asia/Tbilisi','Asia/Yerevan','Asia/Kabul','Asia/Yekaterinburg','Asia/Tashkent','Asia/Kolkata','Asia/Kathmandu','Asia/Almaty','Asia/Novosibirsk','Asia/Jakarta','Asia/Krasnoyarsk','Asia/Hong_Kong','Asia/Kuala_Lumpur','Asia/Singapore','Asia/Taipei','Asia/Ulaanbaatar','Asia/Urumqi','Asia/Irkutsk','Asia/Seoul','Asia/Tokyo','Australia/Adelaide','Australia/Darwin','Asia/Yakutsk','Australia/Brisbane','Pacific/Port_Moresby','Australia/Sydney','Asia/Vladivostok','Asia/Sakhalin','Asia/Magadan','Pacific/Auckland','Pacific/Fiji');

	if (!$stamp) { $stamp = time(); }
	
	$local = new DateTime('@'.$stamp);

	if ($member_id['timezone'] AND !$servertime) {
		$localzone = $member_id['timezone'];

	} else {

		$localzone = date_default_timezone_get();
	}

	if (!in_array($localzone, $timezones)) $localzone = 'Europe/Moscow';

	$local->setTimeZone(new DateTimeZone($localzone));

	return strtr( $local->format($format), $langdate );

}

function CategoryNewsSelection($categoryid = 0, $parentid = 0, $nocat = TRUE, $sublevelmarker = '', $returnstring = '') {
	global $cat, $cat_parentid, $member_id, $user_group, $mod;
	
	if ($mod == "addnews" OR $mod == "editnews")
		$allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_allow_addnews'] );
	else
		$allow_list = explode( ',', $user_group[$member_id['user_group']]['allow_cats'] );
		
	if( $mod != "usergroup" ) {
		
		$not_allow_list = explode( ',', $user_group[$member_id['user_group']]['not_allow_cats'] );
		
	} else $not_allow_list = array();

	$spec_list = explode( ',', $user_group[$member_id['user_group']]['cat_add'] );
	
	if( $parentid == 0 ) {
		if( $nocat ) $returnstring .= '<option value="0"></option>';
	} else {
		$sublevelmarker .= '&nbsp;&nbsp;&nbsp;&nbsp;';
	}
	
	if( isset( $cat_parentid ) ) {
		
		$root_category = @array_keys( $cat_parentid, $parentid );
		
		if( is_array( $root_category ) ) {
			
			foreach ( $root_category as $id ) {
				
				$category_name = $cat[$id];
				
				if( ( $allow_list[0] == "all" OR in_array( $id, $allow_list ) ) OR $mod == "usergroup" ) {
					
					if( in_array( $id, $not_allow_list ) ) continue;
					
					if( $spec_list[0] == "all" or in_array( $id, $spec_list ) ) $color = "black";
					else $color = "red";
					
					$returnstring .= "<option style=\"color: {$color}\" value=\"" . $id . '" ';
					
					if( is_array( $categoryid ) ) {
						foreach ( $categoryid as $element ) {
							if( $element == $id ) $returnstring .= 'SELECTED';
						}
					} elseif( $categoryid == $id ) $returnstring .= 'SELECTED';
					
					$returnstring .= '>' . $sublevelmarker . $category_name . '</option>';
				}
				
				$returnstring = CategoryNewsSelection( $categoryid, $id, $nocat, $sublevelmarker, $returnstring );
			}
		}
	}
	
	return $returnstring;
}

$mcache = false;

if ( $config['cache_type'] ) {

	if ( function_exists('memcache_connect') ) {

		$memcache_server = explode(":", $config['memcache_server']);

		if ($memcache_server[0] == 'unix') {
			$memcache_server = array($config['memcache_server'], 0);
		}

		$mcache = @memcache_connect( $memcache_server[0], $memcache_server[1] );

		if( $mcache AND function_exists('memcache_set_compress_threshold') )
		{
			memcache_set_compress_threshold( $mcache, 20000, 0.2 );
		}

	}

}

function clear_cache($cache_areas = false) {
	global $mcache;

	if ( $mcache ) {

		memcache_flush($mcache);

	}

	if ( $cache_areas ) {
		if(!is_array($cache_areas)) {
			$cache_areas = array($cache_areas);
		}
	}
		
	$fdir = opendir( ENGINE_DIR . '/cache' );
		
	while ( $file = readdir( $fdir ) ) {
		if( $file != '.' and $file != '..' and $file != '.htaccess' and $file != 'system' ) {
			
			if( $cache_areas ) {
				
				foreach($cache_areas as $cache_area) if( strpos( $file, $cache_area ) !== false ) @unlink( ENGINE_DIR . '/cache/' . $file );
			
			} else {
				
				@unlink( ENGINE_DIR . '/cache/' . $file );
			
			}
		}
	}
}

function xfieldsdataload($id) {
	
	if( $id == "" ) return;
	
	$xfieldsdata = explode( "||", $id );
	foreach ( $xfieldsdata as $xfielddata ) {
		list ( $xfielddataname, $xfielddatavalue ) = explode( "|", $xfielddata );
		$xfielddataname = str_replace( "&#124;", "|", $xfielddataname );
		$xfielddataname = str_replace( "__NEWL__", "\r\n", $xfielddataname );
		$xfielddatavalue = str_replace( "&#124;", "|", $xfielddatavalue );
		$xfielddatavalue = str_replace( "__NEWL__", "\r\n", $xfielddatavalue );
		$data[$xfielddataname] = $xfielddatavalue;
	}
	return $data;
}

function xfieldsload() {
	global $lang;
	$path = ENGINE_DIR . '/data/xfields.txt';
	$filecontents = file( $path );
	
	if( ! is_array( $filecontents ) ) msg( "error", $lang['xfield_error'], "$lang[xfield_err_3] \"engine/data/xfields.txt\". $lang[xfield_err_4]" );
	
	foreach ( $filecontents as $name => $value ) {
		$filecontents[$name] = explode( "|", trim( $value ) );
		foreach ( $filecontents[$name] as $name2 => $value2 ) {
			$value2 = str_replace( "&#124;", "|", $value2 );
			$value2 = str_replace( "__NEWL__", "\r\n", $value2 );
			$filecontents[$name][$name2] = $value2;
		}
	}
	return $filecontents;
}

function create_metatags($story, $ajax = false) {
	global $config, $db;
	
	$keyword_count = 20;
	$newarr = array ();
	$headers = array ();
	$quotes = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", "\\", ",", ".", "/", "¬", "#", ";", ":", "@", "~", "[", "]", "{", "}", "=", "-", "+", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"');
	$fastquotes = array ("\x22", "\x60", "\t", "\n", "\r", '"', '\r', '\n', "$", "{", "}", "[", "]", "<", ">", "\\");

	$story = preg_replace( "#\[hide\](.+?)\[/hide\]#is", "", $story );
	$story = preg_replace( "'\[attachment=(.*?)\]'si", "", $story );
	$story = preg_replace( "'\[page=(.*?)\](.*?)\[/page\]'si", "", $story );
	$story = str_replace( "{PAGEBREAK}", "", $story );
	$story = str_replace( "&nbsp;", " ", $story );
	
	$story = str_replace( '<br />', ' ', $story );
	$story = strip_tags( $story );
	$story = preg_replace( "#&(.+?);#", "", $story );
	$story = trim(str_replace( " ,", "", $story ));
 
	if( trim( $_REQUEST['meta_title'] ) != "" ) {

		$headers['title'] = trim( htmlspecialchars( strip_tags( stripslashes($_REQUEST['meta_title'] ) ), ENT_COMPAT, $config['charset'] ) );
		$headers['title'] = $db->safesql(str_replace( $fastquotes, '', $headers['title'] ));

	} else $headers['title'] = "";
	
	if( trim( $_REQUEST['descr'] ) != "" ) {

		$headers['description'] = strip_tags( stripslashes( $_REQUEST['descr'] ) );

		if( dle_strlen( $headers['description'], $config['charset'] ) > 200 ) {
			
			$headers['description'] = dle_substr( $headers['description'], 0, 200, $config['charset'] );
			
			if( ($temp_dmax = dle_strrpos( $headers['description'], ' ', $config['charset'] )) ) $headers['description'] = dle_substr( $headers['description'], 0, $temp_dmax, $config['charset'] );

		}
		
		$headers['description'] = $db->safesql( str_replace( $fastquotes, '', $headers['description'] ));
	
	} elseif($config['create_metatags'] OR $ajax) {
		
		$story = str_replace( $fastquotes, '', $story );

		$headers['description'] = stripslashes($story);
		
		if( dle_strlen( $headers['description'], $config['charset'] ) > 200 ) {
			
			$headers['description'] = dle_substr( $headers['description'], 0, 200, $config['charset'] );
			
			if( ($temp_dmax = dle_strrpos( $headers['description'], ' ', $config['charset'] )) ) $headers['description'] = dle_substr( $headers['description'], 0, $temp_dmax, $config['charset'] );

		}
		
		$headers['description'] = $db->safesql( $headers['description'] );

	} else {

		$headers['description'] = '';

	}
	
	if( trim( $_REQUEST['keywords'] ) != "" ) {
		
		$arr = explode( ",", $_REQUEST['keywords'] );
		$newarr = array();

		foreach ( $arr as $word ) {
			$newarr[] = trim($word);
		}

		$_REQUEST['keywords'] = implode( ", ", $newarr );
		
		$headers['keywords'] = $db->safesql( str_replace( $fastquotes, " ", strip_tags( stripslashes( $_REQUEST['keywords'] ) ) ) );

	} elseif( $config['create_metatags'] OR $ajax) {
		
		$story = str_replace( $quotes, ' ', $story );
		
		$arr = explode( " ", $story );
		
		foreach ( $arr as $word ) {
			if( dle_strlen( $word, $config['charset'] ) > 4 ) $newarr[] = $word;
		}
		
		$arr = array_count_values( $newarr );
		arsort( $arr );
		
		$arr = array_keys( $arr );
		
		$total = count( $arr );
		
		$offset = 0;
		
		$arr = array_slice( $arr, $offset, $keyword_count );
		
		$headers['keywords'] = $db->safesql( implode( ", ", $arr ) );
	} else {

		$headers['keywords'] = '';

	}
	
	return $headers;
}

function set_vars($file, $data) {

	if ( is_array($data) OR is_int($data) ) {
		
		$file = totranslit($file, true, false);	
		$fp = fopen( ENGINE_DIR . '/cache/system/' . $file . '.php', 'wb+' );
		fwrite( $fp, serialize( $data ) );
		fclose( $fp );
		
		@chmod( ENGINE_DIR . '/cache/system/' . $file . '.php', 0666 );

	}

}

function get_vars($file) {
	$file = totranslit($file, true, false);

	$data = @file_get_contents( ENGINE_DIR . '/cache/system/' . $file . '.php' );

	if ( $data !== false ) {

		$data = unserialize( $data );
		if ( is_array($data) OR is_int($data) ) return $data;

	} 

	return false;

}
function get_groups($id = false) {
	global $user_group;
	
	$returnstring = "";
	
	foreach ( $user_group as $group ) {
		$returnstring .= '<option value="' . $group['id'] . '" ';
		
		if( is_array( $id ) ) {
			foreach ( $id as $element ) {
				if( $element == $group['id'] ) $returnstring .= 'SELECTED';
			}
		} elseif( $id and $id == $group['id'] ) $returnstring .= 'SELECTED';
		
		$returnstring .= ">" . $group['group_name'] . "</option>\n";
	}
	
	return $returnstring;

}
function permload($id) {
	
	if( $id == "" ) return;
	
	$data = array ();
	
	$groups = explode( "|", $id );
	foreach ( $groups as $group ) {
		list ( $groupid, $groupvalue ) = explode( ":", $group );
		$data[$groupid][1] = ($groupvalue == 1) ? "selected" : "";
		$data[$groupid][2] = ($groupvalue == 2) ? "selected" : "";
		$data[$groupid][3] = ($groupvalue == 3) ? "selected" : "";
	}
	return $data;
}
function check_xss() {

	if ($_GET['mod'] == "editnews" AND $_GET['action'] == "list") return;
	if ($_GET['mod'] == "tagscloud" ) return;
	if ($_GET['mod'] == "links" ) return;
	
	$url = html_entity_decode( urldecode( $_SERVER['QUERY_STRING'] ), ENT_QUOTES, 'ISO-8859-1' );

	$url = str_replace( "\\", "/", $url );

	
	if( $url ) {
		
		if( (strpos( $url, '<' ) !== false) || (strpos( $url, '>' ) !== false) || (strpos( $url, '"' ) !== false) || (strpos( $url, './' ) !== false) || (strpos( $url, '../' ) !== false) || (strpos( $url, '\'' ) !== false) || (strpos( $url, '.php' ) !== false) ) {
			
			if( $_GET['mod'] != "editnews" OR $_GET['action'] != "list" ) die( "Hacking attempt!" );
		
		}
	
	}
	
	$url = html_entity_decode( urldecode( $_SERVER['REQUEST_URI'] ), ENT_QUOTES, 'ISO-8859-1' );
	$url = str_replace( "\\", "/", $url );
	
	if( $url ) {
		
		if( (strpos( $url, '<' ) !== false) || (strpos( $url, '>' ) !== false) || (strpos( $url, '"' ) !== false) || (strpos( $url, '\'' ) !== false) ) {
			
			die( "Hacking attempt!" );
		
		}
	
	}

}

function clean_url($url) {
	
	if( $url == '' ) return;
	
	$url = str_replace( "http://", "", $url );
	$url = str_replace( "https://", "", $url );
	if( strtolower( substr( $url, 0, 4 ) ) == 'www.' ) $url = substr( $url, 4 );
	$url = explode( '/', $url );
	$url = reset( $url );
	$url = explode( ':', $url );
	$url = reset( $url );
	
	return $url;
}

function set_cookie($name, $value, $expires) {
	
	if( $expires ) {
		
		$expires = time() + ($expires * 86400);
	
	} else {
		
		$expires = FALSE;
	
	}
	
	if( PHP_VERSION < 5.2 ) {
		
		if ( DOMAIN ) setcookie( $name, $value, $expires, "/", "; HttpOnly" );
		else setcookie( $name, $value, $expires, "/", DOMAIN . "; HttpOnly" );
	
	} else {
		
		setcookie( $name, $value, $expires, "/", DOMAIN, NULL, TRUE );
	
	}
}

function get_url($id) {
	
	global $cat_info;
	
	if( ! $id ) return;
	
	$parent_id = $cat_info[$id]['parentid'];
	
	$url = $cat_info[$id]['alt_name'];
	
	while ( $parent_id ) {
		
		$url = $cat_info[$parent_id]['alt_name'] . "/" . $url;
		
		$parent_id = $cat_info[$parent_id]['parentid'];
		
		if( $cat_info[$parent_id]['parentid'] == $cat_info[$parent_id]['id'] ) break;
	
	}
	
	return $url;
}

function convert_unicode($t, $to = 'windows-1251') {

	$to = strtolower( $to );

	if( $to == 'utf-8' ) {
		
		return $t;
	
	} else {

		if( function_exists( 'mb_convert_encoding' ) ) {

			$t = mb_convert_encoding( $t, $to, "UTF-8" );

		} elseif( function_exists( 'iconv' ) ) {

			$t = iconv( "UTF-8", $to . "//IGNORE", $t );

		} else $t = "The library iconv AND mbstring is not supported by your server";
	
	}

	return $t;
}

function check_netz($ip1, $ip2) {
	
	$ip1 = explode( ".", $ip1 );
	$ip2 = explode( ".", $ip2 );
	
	if( $ip1[0] != $ip2[0] ) return false;
	if( $ip1[1] != $ip2[1] ) return false;
	
	return true;

}

function compare_filter($a, $b) {
	
	$a = explode( "|", $a );
	$b = explode( "|", $b );
	
	if( $a[1] == $b[1] ) return 0;
	
	return strcasecmp( $a[1], $b[1] );

}

function auth() {
	header( 'WWW-Authenticate: Basic realm="Admin Area"' );
	header( 'HTTP/1.0 401 Unauthorized' );
	echo "<H1>Access Denied</H1>";
	exit();
}

function build_js($js) {
	global $config;

	$js_array = array();

	if ($config['js_min']) {

		$js_array[] = "<script type=\"text/javascript\" src=\"engine/classes/min/index.php?charset={$config['charset']}&amp;g=admin&amp;18\"></script>";

		if ( count($js) ) $js_array[] = "<script type=\"text/javascript\" src=\"engine/classes/min/index.php?charset={$config['charset']}&amp;f=".implode(",", $js)."&amp;18\"></script>";

		return implode("\n", $js_array);

	} else {

		$default_array = array (
			'engine/skins/javascripts/application.js',
		);

		if ( count($js) ) $js = array_merge($default_array, $js); else $js = $default_array;

		foreach ($js as $value) {
		
			$js_array[] = "<script type=\"text/javascript\" src=\"{$value}\"></script>";
		
		}

		return implode("\n", $js_array);
	}

}

function dle_strlen($value, $charset ) {

	if ( strtolower($charset) == "utf-8") {
		if( function_exists( 'mb_strlen' ) ) {
			return mb_strlen( $value, "utf-8" );
	
		} elseif( function_exists( 'iconv_strlen' ) ) {
			return iconv_strlen($value, "utf-8");
		}
	}

	return strlen($value);

}

function dle_substr($str, $start, $length, $charset ) {

	if ( strtolower($charset) == "utf-8") {
		if( function_exists( 'mb_substr' ) ) {
			return mb_substr( $str, $start, $length, "utf-8" );
	
		} elseif( function_exists( 'iconv_substr' ) ) {
			return iconv_substr($str, $start, $length, "utf-8");
		}
	}

	return substr($str, $start, $length);

}

function dle_strrpos($str, $needle, $charset ) {

	if ( strtolower($charset) == "utf-8") {
		if( function_exists( 'mb_strrpos' ) ) {
			return mb_strrpos( $str, $needle, null, "utf-8" );
	
		} elseif( function_exists( 'iconv_strrpos' ) ) {
			return iconv_strrpos($str, $needle, "utf-8");
		}
	}

	return strrpos($str, $needle);

}

function check_allow_login($ip, $max ) {
	global $db, $config;

	$config['login_ban_timeout'] = intval($config['login_ban_timeout']);
	
	$block_date = time()-($config['login_ban_timeout'] * 60);

	$row = $db->super_query( "SELECT * FROM " . PREFIX . "_login_log WHERE ip='{$ip}'" );

	if ( $row['count'] AND $row['date'] < $block_date ) $db->query( "DELETE FROM " . PREFIX . "_login_log WHERE ip = '{$ip}'" );

	if ($row['count'] >= $max AND $row['date'] > $block_date ) return false;
	else return true;

}

function detect_encoding($string) {  
  static $list = array('utf-8', 'windows-1251');
   
  foreach ($list as $item) {

	if( function_exists( 'mb_convert_encoding' ) ) {

		$sample = mb_convert_encoding( $string, $item, $item );

	} elseif( function_exists( 'iconv' ) ) {
	
		$sample = iconv($item, $item, $string);
	
	}

	if (md5($sample) == md5($string)) return $item;
   }

   return null;
}

function get_ip() {

	if ( filter_var( $_SERVER['REMOTE_ADDR'] , FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
		return filter_var( $_SERVER['REMOTE_ADDR'] , FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
	}

	if ( filter_var( $_SERVER['REMOTE_ADDR'] , FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ) {
		return filter_var( $_SERVER['REMOTE_ADDR'] , FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
	}

	return 'localhost';
}

function http_get_contents( $file ) {
		
	$data = false;

	if (stripos($file, "http://") !== 0 AND stripos($file, "https://") !== 0) {
		return false;
	}
		
	if( function_exists( 'curl_init' ) ) {
			
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $file );
		curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
		@curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
		curl_setopt( $ch, CURLOPT_HEADER, 0);
			
		$data = curl_exec( $ch );
		curl_close( $ch );

		if( $data !== false ) return $data;
		
	} 

	if( preg_match('/1|yes|on|true/i', ini_get('allow_url_fopen')) ) {

		$data = @file_get_contents( $file );
			
		if( $data !== false ) return $data;

	}

	return false;	
}

function cleanpath($path) {
	$path = trim(str_replace(chr(0), '', (string)$path));
	$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
	$parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
	$absolutes = array();
	foreach ($parts as $part) {
		if ('.' == $part) continue;
		if ('..' == $part) {
			array_pop($absolutes);
		} else {
			$absolutes[] = totranslit($part, false, false);
		}
	}

	return implode(DIRECTORY_SEPARATOR, $absolutes);
}

?>