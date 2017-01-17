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
 Файл: download.php
-----------------------------------------------------
 Назначение: Скачивание файлов
=====================================================
*/
define ( 'DATALIFEENGINE', true );
define ( 'FILE_DIR', '../uploads/files/' );
define ( 'ROOT_DIR', '..' );
define ( 'ENGINE_DIR', ROOT_DIR . '/engine' );

@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

require ENGINE_DIR . '/data/config.php';

date_default_timezone_set ( $config['date_adjust'] );

if ($config['http_home_url'] == "") {
	
	$config['http_home_url'] = explode ( "engine/download.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset ( $config['http_home_url'] );
	$config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';

dle_session();

require_once ENGINE_DIR . '/modules/sitelogin.php';
require_once ENGINE_DIR . '/classes/download.class.php';

function reset_url($url) {
	$value = str_replace ( "http://", "", $url );
	$value = str_replace ( "https://", "", $value );
	$value = str_replace ( "www.", "", $value );
	$value = explode ( "/", $value );
	$value = reset ( $value );
	return $value;
}

function clear_url_dir($var) {
	if ( is_array($var) ) return "";
	
	$var = str_ireplace( ".php", "", $var );
	$var = str_ireplace( ".php", ".ppp", $var );
	$var = trim( strip_tags( $var ) );
	$var = str_replace( "\\", "/", $var );
	$var = preg_replace( "/[^a-z0-9\/\_\-]+/mi", "", $var );
	return $var;
	
}

//################# Определение групп пользователей
$user_group = get_vars ( "usergroup" );

if (! $user_group) {
	
	$user_group = array ();
	
	$db->query ( "SELECT * FROM " . USERPREFIX . "_usergroups ORDER BY id ASC" );
	
	while ( $row = $db->get_row () ) {
		
		$user_group[$row['id']] = array ();
		
		foreach ( $row as $key => $value ) {
			$user_group[$row['id']][$key] = $value;
		}
	
	}
	
	set_vars ( "usergroup", $user_group );
	$db->free ();

}

if (! $is_logged) {
	$member_id['user_group'] = 5;
}

if (! $user_group[$member_id['user_group']]['allow_files'])
	die ( "Access denied" );

if ($config['files_antileech']) {
	
	$_SERVER['HTTP_REFERER'] = reset_url ( $_SERVER['HTTP_REFERER'] );
	$_SERVER['HTTP_HOST'] = reset_url ( $_SERVER['HTTP_HOST'] );

	if ($_SERVER['HTTP_HOST'] != $_SERVER['HTTP_REFERER']) {
		@header ( 'Location: ' . $config['http_home_url'] );
		die ( "Access denied!!!<br /><br />Please visit <a href=\"{$config['http_home_url']}\">{$config['http_home_url']}</a>" );
	}

}

$id = intval ( $_REQUEST['id'] );

if ($_REQUEST['area'] == "static")
	$row = $db->super_query ( "SELECT name, onserver FROM " . PREFIX . "_static_files WHERE id ='{$id}'" );
else
	$row = $db->super_query ( "SELECT name, onserver FROM " . PREFIX . "_files WHERE id ='{$id}'" );

if (! $row)
	die ( "Access denied" );

$url = @parse_url ( $row['onserver'] );

$file_path = dirname (clear_url_dir($url['path']));
$file_name = pathinfo($url['path']);
$file_name = totranslit($file_name['basename'], false);

if ($file_path AND $file_path != ".") $file_name = $file_path."/".$file_name;

$file = new download ( FILE_DIR . $file_name, $row['name'], $config['files_force'], intval($user_group[$member_id['user_group']]['files_max_speed']) );

if ($_REQUEST['area'] == "static") {
	
	if ($config['files_count'] and ! $file->range)
		$db->query ( "UPDATE " . PREFIX . "_static_files SET dcount=dcount+1 WHERE id ='$id'" );

} else {
	
	if ($config['files_count'] and ! $file->range)
		$db->query ( "UPDATE " . PREFIX . "_files SET dcount=dcount+1 WHERE id ='$id'" );

}

$db->close ();
session_write_close();

$file->download_file();
?>