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
 Файл: print.php
-----------------------------------------------------
 Назначение: Версия для печати
=====================================================
*/

@ob_start ();
@ob_implicit_flush ( 0 );

define( 'DATALIFEENGINE', true );
define( 'ROOT_DIR', '..' );
define( 'ENGINE_DIR', dirname( __FILE__ ) );

@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

include ENGINE_DIR . '/data/config.php';

date_default_timezone_set ( $config['date_adjust'] );

if( $config['http_home_url'] == "" ) {
	
	$config['http_home_url'] = explode( "engine/print.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset( $config['http_home_url'] );
	$config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

require_once ENGINE_DIR . '/classes/mysql.php';
include_once ENGINE_DIR . '/data/dbconfig.php';
include_once ENGINE_DIR . '/modules/functions.php';
require_once ENGINE_DIR . '/classes/templates.class.php';

dle_session();

if( $config['site_offline']) die( "The site in offline mode" );

check_xss();
$_TIME = time();

if (isset ( $_COOKIE['dle_skin'] ) ) {

	$_COOKIE['dle_skin'] = trim( totranslit($_COOKIE['dle_skin'], false, false) );

	if ($_COOKIE['dle_skin'] != '' AND @is_dir ( ROOT_DIR . '/templates/' . $_COOKIE['dle_skin'] )) {
		$config['skin'] = $_COOKIE['dle_skin'];
	}
}

if( $config["lang_" . $config['skin']] ) {
	if ( file_exists( ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng' ) ) {	
		include_once ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng';
	} else die("Language file not found");
} else {
	
	include_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';

}

$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];

@header("Content-type: text/html; charset=".$config['charset']);

if( $config['allow_registration'] ) {
	
	include_once ENGINE_DIR . '/modules/sitelogin.php';

}

//####################################################################################################################
//                    Определение категорий и их параметры
//####################################################################################################################
$cat_info = get_vars( "category" );

if( ! $cat_info ) {
	$cat_info = array ();
	
	$db->query( "SELECT * FROM " . PREFIX . "_category ORDER BY posi ASC" );
	while ( $row = $db->get_row() ) {
		
		$cat_info[$row['id']] = array ();
		
		foreach ( $row as $key => $value ) {
			$cat_info[$row['id']][$key] = $value;
		}
	
	}
	set_vars( "category", $cat_info );
	$db->free();
}

//################# Определение групп пользователей
$user_group = get_vars( "usergroup" );

if( ! $user_group ) {
	$user_group = array ();
	
	$db->query( "SELECT * FROM " . USERPREFIX . "_usergroups ORDER BY id ASC" );
	
	while ( $row = $db->get_row() ) {
		
		$user_group[$row['id']] = array ();
		
		foreach ( $row as $key => $value ) {
			$user_group[$row['id']][$key] = $value;
		}
	
	}
	set_vars( "usergroup", $user_group );
	$db->free();
}

if( ! $is_logged ) {
	$member_id['user_group'] = 5;
}

$PHP_SELF = $config['http_home_url'] . "index.php";

if (isset ( $_GET['year'] )) $year = intval ( $_GET['year'] ); else $year = '';
if (isset ( $_GET['month'] )) $month = @$db->safesql ( sprintf("%02d", intval ( $_GET['month'] ) ) ); else $month = '';
if (isset ( $_GET['day'] )) $day = @$db->safesql ( sprintf("%02d", intval ( $_GET['day'] ) ) ); else $day = '';
if (isset ( $_GET['news_name'] )) $news_name = @$db->safesql ( strip_tags ( str_replace ( '/', '', $_GET['news_name'] ) ) ); else $news_name = '';
if (isset ( $_GET['newsid'] )) $newsid = intval ( $_GET['newsid'] ); else $newsid = 0;
if (isset ( $_GET['cstart'] )) $cstart = intval ( $_GET['cstart'] ); else $cstart = 0;
if (isset ( $_GET['news_page'] )) $news_page = intval ( $_GET['news_page'] ); else $news_page = 0;

if (isset ( $_GET['user'] )) {

	$user = @strip_tags ( str_replace ( '/', '', urldecode ( $_GET['user'] ) ) );

	if ( $config['charset'] == "windows-1251" AND $config['charset'] != detect_encoding($user) ) {

		if( function_exists( 'mb_convert_encoding' ) ) {
	
			$user = mb_convert_encoding( $user, "windows-1251", "UTF-8" );
	
		} elseif( function_exists( 'iconv' ) ) {
		
			$user = iconv( "UTF-8", "windows-1251//IGNORE", $user );
		
		}

	}

	$user = $db->safesql ( $user ); 

	if( preg_match( "/[\||\'|\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $user ) ) $user="";

} else $user = '';

$category = '';

if (isset ($_REQUEST['do']) ) $do = totranslit ( $_REQUEST['do'] ); else $do = "";
if (isset ($_REQUEST['subaction']) ) $subaction = totranslit ($_REQUEST['subaction']); else $subaction = "";
if ( isset ($_REQUEST['doaction']) ) $doaction = totranslit ($_REQUEST['doaction']); else $doaction = "";
if ($do == "tags" AND !$_GET['tag']) $do = "alltags";

$dle_module = $do;
if ($do == "" and ! $subaction and $year) $dle_module = "date";
elseif ($do == "" and $catalog) $dle_module = "catalog";
elseif ($do == "") $dle_module = $subaction;
if ($subaction == '' AND $newsid) $dle_module = "showfull";
$dle_module = $dle_module ? $dle_module : "main";

$tpl = new dle_template();
$tpl->dir = ROOT_DIR . '/templates/' . $config['skin'];
define( 'TEMPLATE_DIR', $tpl->dir );

if ($config['rss_informer']) include_once ENGINE_DIR . '/modules/rssinform.php';

$config['allow_cache'] = false;
$view_template = "print";
include_once ENGINE_DIR . '/engine.php';

$tpl->result['content'] = str_replace ( '{THEME}', $config['http_home_url'] . 'templates/' . $config['skin'], $tpl->result['content'] );
$tpl->result['content'] = str_replace ( '{charset}', $config['charset'], $tpl->result['content'] );

echo $tpl->result['content'];
?>