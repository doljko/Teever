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
 Файл: favorites.php
-----------------------------------------------------
 Назначение: AJAX для фаворитов
=====================================================
*/

@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

define( 'DATALIFEENGINE', true );
define( 'ROOT_DIR', substr( dirname(  __FILE__ ), 0, -12 ) );
define( 'ENGINE_DIR', ROOT_DIR . '/engine' );

include ENGINE_DIR . '/data/config.php';

date_default_timezone_set ( $config['date_adjust'] );

if( $config['http_home_url'] == "" ) {
	
	$config['http_home_url'] = explode( "engine/ajax/favorites.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset( $config['http_home_url'] );
	$config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';

dle_session();

$_REQUEST['skin'] = totranslit($_REQUEST['skin'], false, false);

if( $_REQUEST['skin'] == "" OR !@is_dir( ROOT_DIR . '/templates/' . $_REQUEST['skin'] ) ) {

	die( "Hacking attempt!" );

} else {

	$config['skin'] = $_REQUEST['skin'];

}

if( $config["lang_" . $config['skin']] ) {
	if ( file_exists( ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng' ) ) {	
		include_once ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng';
	} else die("Language file not found");
} else {
	
	include_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';

}

$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];

require_once ENGINE_DIR . '/modules/sitelogin.php';

if( !$is_logged ) die( "error" );

$id = intval( $_REQUEST['fav_id'] );

if( !$id OR $id < 1) die( "error" );

if( $_REQUEST['action'] == "plus" ) {
	$error = "";
	
	$list = explode( ",", $member_id['favorites'] );
	
	foreach ( $list as $daten ) {

		if( $daten == $id ) $error = "stop";

	}
	
	if( $error != "stop" ) {

		$list[] = $id;
		$favorites = $db->safesql(implode( ",", $list ));
		
		if( $member_id['favorites'] == "" ) $favorites = $id;
		
		$member_id['favorites'] = $favorites;
		
		$db->query( "UPDATE " . USERPREFIX . "_users SET favorites='{$favorites}' WHERE user_id = '{$member_id['user_id']}'" );
	
	}

	if ( $_REQUEST['alert'] ) $buffer = $lang['fav_plus'];
	else $buffer = "<img src=\"" . $config['http_home_url'] . "templates/{$config['skin']}/dleimages/minus_fav.gif\" onclick=\"doFavorites('" . $id . "', 'minus'); return false;\" title=\"" . $lang['news_minfav'] . "\" style=\"vertical-align: middle;border: none;\" />";

} elseif( $_REQUEST['action'] == "minus" ) {
	
	$list = explode( ",", $member_id['favorites'] );
	$i = 0;
	
	foreach ( $list as $daten ) {

		if( $daten == $id ) unset( $list[$i] );
		$i ++;

	}
	
	if( count( $list ) ) $member_id['favorites'] = $db->safesql(implode( ",", $list ));
	else $member_id['favorites'] = "";
	
	$db->query( "UPDATE " . USERPREFIX . "_users SET favorites='{$member_id['favorites']}' WHERE user_id = '{$member_id['user_id']}'" );

	if ( $_REQUEST['alert'] ) $buffer = $lang['fav_minus'];
	else $buffer = "<img src=\"" . $config['http_home_url'] . "templates/{$config['skin']}/dleimages/plus_fav.gif\" onclick=\"doFavorites('" . $id . "', 'plus'); return false;\" title=\"" . $lang['news_addfav'] . "\" style=\"vertical-align: middle;border: none;\" />";

} else
	die( "error" );

$db->close();

@header( "Content-type: text/html; charset=" . $config['charset'] );
echo $buffer;
?>