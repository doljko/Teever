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
 Файл: go.php
-----------------------------------------------------
 Назначение: Переадресация ссылки
=====================================================
*/

@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

define( 'ENGINE_DIR', dirname( __FILE__ ) );

include (ENGINE_DIR . '/data/config.php');

date_default_timezone_set ( $config['date_adjust'] );

function reset_url($url) {
	$value = str_replace ( "http://", "", $url );
	$value = str_replace ( "https://", "", $value );
	$value = str_replace ( "www.", "", $value );
	$value = explode ( "/", $value );
	$value = reset ( $value );
	return $value;
}

$url = @rawurldecode ( $_GET['url'] );
$url = @base64_decode ( $url );
$url = @str_replace ( "&amp;", "&", $url );

$_SERVER['HTTP_REFERER'] = reset_url ( $_SERVER['HTTP_REFERER'] );
$_SERVER['HTTP_HOST'] = reset_url ( $_SERVER['HTTP_HOST'] );

if (($_SERVER['HTTP_HOST'] != $_SERVER['HTTP_REFERER']) OR $url == "") {
	@header ( 'Location: /index.php' );
	die ( "Access denied!!!<br /><br />Please visit <a href=\"/index.php\">{$_SERVER['HTTP_HOST']}</a>" );
}

if ( $config['charset'] == "windows-1251" ) {

	if( function_exists( 'mb_convert_encoding' ) ) {
	
		$url = mb_convert_encoding( $url, "UTF-8", "windows-1251" );
	
	} elseif( function_exists( 'iconv' ) ) {
	
		$url = iconv( "windows-1251", "UTF-8", $url );
	
	}

}

@header ( 'Location: ' . $url );

die ( "Link Redirect:<br /><br />Please click <a href=\"{$url}\">here.</a>" );
?>