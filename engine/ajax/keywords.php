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
 Файл: keywords.php
-----------------------------------------------------
 Назначение: Генерация ключевых слов
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

	$config['http_home_url'] = explode("engine/ajax/keywords.php", $_SERVER['PHP_SELF']);
	$config['http_home_url'] = reset($config['http_home_url']);
	$config['http_home_url'] = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];

}

require_once ENGINE_DIR.'/classes/mysql.php';
require_once ENGINE_DIR.'/data/dbconfig.php';
require_once ROOT_DIR.'/language/'.$config['langs'].'/adminpanel.lng';
require_once ENGINE_DIR.'/inc/include/functions.inc.php';
require_once ENGINE_DIR.'/classes/parse.class.php';

dle_session();

$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];

@header("Content-type: text/html; charset=".$config['charset']);

$parse = new ParseFilter();
$full_story = $parse->BB_Parse($parse->process(convert_unicode($_REQUEST['full_txt'], $config['charset'])), false);
$short_story = $parse->BB_Parse($parse->process(convert_unicode($_REQUEST['short_txt'], $config['charset'])), false);

$metatags = create_metatags ($short_story." ".$full_story, true);

$metatags['description'] = trim($metatags['description']);
$metatags['keywords'] = trim($metatags['keywords']);

if ($_REQUEST['key'] == 1) echo stripslashes($metatags['description']);
else echo stripslashes($metatags['keywords']);

?>