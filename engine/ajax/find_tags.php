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
 Файл: find_tags.php
-----------------------------------------------------
 Назначение: Автоподсказки для облака тегов
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
	
	$config['http_home_url'] = explode( "engine/ajax/find_tags.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset( $config['http_home_url'] );
	$config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';

dle_session();

$term = convert_unicode( $_GET['term'], $config['charset'] );

if( preg_match( "/[\||\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $term ) ) $term = "";
else $term = $db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $term ) ) ), ENT_QUOTES, $config['charset'] ) );

if( $term == "" ) die("[]");

$buffer = "[]";
$tags = array ();

$db->query("SELECT tag, COUNT(*) AS count FROM " . PREFIX . "_tags WHERE `tag` like '{$term}%' GROUP BY tag ORDER by count DESC LIMIT 15");

while($row = $db->get_row()){

	$tags[] = $row['tag'];

}

if (count($tags)) $buffer = "[\"".implode("\",\"",$tags)."\"]";

@header( "Content-type: text/html; charset=" . $config['charset'] );

echo $buffer;

?>