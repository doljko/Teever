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
 Файл: sitemap.php
-----------------------------------------------------
 Назначение: Уведомление поисковых систем, о новой карте
=====================================================
*/

@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

define('DATALIFEENGINE', true);
define( 'ROOT_DIR', substr( dirname(  __FILE__ ), 0, -12 ) );
define( 'ENGINE_DIR', ROOT_DIR . '/engine' );

include ENGINE_DIR.'/data/config.php';

date_default_timezone_set ( $config['date_adjust'] );

if ($config['http_home_url'] == "") {

	$config['http_home_url'] = explode("engine/ajax/sitemap.php", $_SERVER['PHP_SELF']);
	$config['http_home_url'] = reset($config['http_home_url']);
	$config['http_home_url'] = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];

}

require_once ENGINE_DIR.'/classes/mysql.php';
require_once ENGINE_DIR.'/data/dbconfig.php';
require_once ENGINE_DIR.'/inc/include/functions.inc.php';
dle_session();
require_once ENGINE_DIR.'/modules/sitelogin.php';

if( !$is_logged ) die( "error" );

if (strpos($config['http_home_url'], "//") === 0) $config['http_home_url'] = "http:".$config['http_home_url'];
elseif (strpos($config['http_home_url'], "/") === 0) $config['http_home_url'] = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];

if(!@file_exists(ROOT_DIR. "/uploads/sitemap.xml")){ 

	die( "error" );

} else {

	if ($config['allow_alt_url']) {

		$map_link = $config['http_home_url']."sitemap.xml";

	} else {

		$map_link = $config['http_home_url']."uploads/sitemap.xml";

	}
}

if ( strtolower($config['charset']) != "utf-8" ) {

	if( function_exists( 'mb_convert_encoding' ) ) {

		$map_link = mb_convert_encoding( $map_link, "UTF-8", $config['charset'] );

	} elseif( function_exists( 'iconv' ) ) {
	
		$map_link = iconv($config['charset'], "UTF-8//IGNORE", $map_link);
	
	}

}



//################# Определение групп пользователей
$user_group = get_vars( "usergroup" );

if( ! $user_group ) {
	$user_group = array ();
	
	$db->query( "SELECT * FROM " . USERPREFIX . "_usergroups ORDER BY id ASC" );
	
	while ( $row = $db->get_row() ) {
		
		$user_group[$row['id']] = array ();
		
		foreach ( $row as $key => $value ) {
			$user_group[$row['id']][$key] = stripslashes($value);
		}
	
	}
	set_vars( "usergroup", $user_group );
	$db->free();
}

if( !$user_group[$member_id['user_group']]['admin_googlemap'] ) { die ("error"); }

$selected_language = $config['langs'];

if (isset( $_COOKIE['selected_language'] )) { 

	$_COOKIE['selected_language'] = trim(totranslit( $_COOKIE['selected_language'], false, false ));

	if ($_COOKIE['selected_language'] != "" AND @is_dir ( ROOT_DIR . '/language/' . $_COOKIE['selected_language'] )) {
		$selected_language = $_COOKIE['selected_language'];
	}

}

if ( file_exists( ROOT_DIR.'/language/'.$selected_language.'/adminpanel.lng' ) ) {
	require_once ROOT_DIR.'/language/'.$selected_language.'/adminpanel.lng';
} else die("Language file not found");

$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];
$buffer = "";

function send_url($url, $map) {
		
	$data = false;

	$file = $url.urlencode($map);
		
	if( function_exists( 'curl_init' ) ) {
			
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $file );
		curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, false );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 6 );
			
		$data = curl_exec( $ch );
		curl_close( $ch );

		return $data;
		
	} else {

		return @file_get_contents( $file );

	}
	
}

@header("Content-type: text/html; charset=".$config['charset']);

if (strpos ( send_url("http://google.com/webmasters/sitemaps/ping?sitemap=", $map_link), "successfully added" ) !== false) {

	$buffer .= "<br />".$lang['sitemap_send']." Google: ".$lang['nl_finish'];

} else {

	$buffer .= "<br />".$lang['sitemap_send']." Google: ".$lang['nl_error']." URL: <a href=\"http://google.com/webmasters/sitemaps/ping?sitemap=".urlencode($map_link)."\" target=\"_blank\">http://google.com/webmasters/sitemaps/ping?sitemap={$map_link}</a>";

}

if (strpos ( send_url("http://ping.blogs.yandex.ru/ping?sitemap=", $map_link), "OK" ) !== false) {

	$buffer .= "<br />".$lang['sitemap_send']." Yandex: ".$lang['nl_finish'];

} else {

	$buffer .= "<br />".$lang['sitemap_send']." Yandex: ".$lang['nl_error']." URL: <a href=\"http://ping.blogs.yandex.ru/ping?sitemap=".urlencode($map_link)."\" target=\"_blank\">http://ping.blogs.yandex.ru/ping?sitemap={$map_link}</a>";

}

send_url("http://www.bing.com/webmaster/ping.aspx?siteMap=", $map_link);
$buffer .= "<br />".$lang['sitemap_send']." Bing: ".$lang['nl_finish'];


echo $buffer;

?>