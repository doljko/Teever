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
 Файл: rss.php
-----------------------------------------------------
 Назначение: экспорт новостей
=====================================================
*/

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
	
	$config['http_home_url'] = explode( "engine/rss.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset( $config['http_home_url'] );
	$config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

} elseif (strpos($config['http_home_url'], "//") === 0) $config['http_home_url'] = "http:".$config['http_home_url'];
elseif (strpos($config['http_home_url'], "/") === 0) $config['http_home_url'] = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];

require_once ENGINE_DIR . '/classes/mysql.php';
include_once ENGINE_DIR . '/data/dbconfig.php';
include_once ENGINE_DIR . '/modules/functions.php';
require_once ENGINE_DIR . '/classes/templates.class.php';
include_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';

check_xss();
$_TIME = time();

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

$tpl = new dle_template( );
$tpl->dir = ROOT_DIR . '/templates';
define( 'TEMPLATE_DIR', $tpl->dir );

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

$member_id['user_group'] = 5;

if( isset( $_GET['year'] ) ) $year = intval( $_GET['year'] ); else $year = '';
if( isset( $_GET['month'] )) $month = @$db->safesql ( sprintf("%02d", intval ( $_GET['month'] ) ) ); else $month = '';
if( isset( $_GET['day'] )) $day = @$db->safesql ( sprintf("%02d", intval ( $_GET['day'] ) ) ); else $day = '';
if( isset( $_GET['news_name'] ) ) $news_name = @$db->safesql( strip_tags( str_replace( '/', '', $_GET['news_name'] ) ) ); else $news_name = '';
if( isset( $_GET['newsid'] ) ) $newsid = intval( $_GET['newsid'] ); else $newsid = 0;
if( isset( $_GET['news_page'] ) ) $news_page = intval( $_GET['news_page'] ); else $news_page = 0;

if (isset ( $_GET['catalog'] )) {

	$catalog = @strip_tags ( str_replace ( '/', '', urldecode ( $_GET['catalog'] ) ) );

	if ( $config['charset'] == "windows-1251" AND $config['charset'] != detect_encoding($catalog) ) {
		if( function_exists( 'mb_convert_encoding' ) ) {
	
			$catalog = mb_convert_encoding( $catalog, "windows-1251", "UTF-8" );
	
		} elseif( function_exists( 'iconv' ) ) {
		
			$catalog = iconv( "UTF-8", "windows-1251//IGNORE", $catalog );
		
		}
	}

	$catalog = $db->safesql ( dle_substr ( $catalog, 0, 3, $config['charset'] ) ); 

} else $catalog = '';

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

if( isset( $_GET['category'] ) ) {
	if( substr( $_GET['category'], - 1, 1 ) == '/' ) $_GET['category'] = substr( $_GET['category'], 0, - 1 );
	$category = explode( '/', $_GET['category'] );
	$category = end( $category );
	$category = $db->safesql( strip_tags( $category ) );
} else
	$category = '';

if( $category != '' ) $category_id = get_ID( $cat_info, $category );
else $category_id = false;

$view_template = "rss";

$config['allow_cache'] = true;
$config['allow_banner'] = false;
$config['rss_number'] = intval( $config['rss_number'] );
$config['rss_format'] = intval( $config['rss_format'] );
$cstart = 0;

if ( $user ) $config['allow_cache'] = false;

if( $_GET['subaction'] == 'allnews' ) $config['home_title'] = $lang['show_user_news'] . ' ' . htmlspecialchars( $user, ENT_QUOTES, $config['charset'] ) . " - " . $config['home_title'];
elseif( $_GET['do'] == 'cat' ) $config['home_title'] = stripslashes( $cat_info[$category_id]['name'] ) . " - " . $config['home_title'];

$rss_content = <<<XML
<?xml version="1.0" encoding="{$config['charset']}"?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
<channel>
<title>{$config['home_title']}</title>
<link>{$config['http_home_url']}</link>
<language>ru</language>
<description>{$config['home_title']}</description>
<generator>DataLife Engine</generator>
XML;

if( !file_exists( $tpl->dir . "/rss.tpl" ) ) {

	$tpl->template = <<<HTML
[shortrss]<item>
<title>{title}</title>
<guid isPermaLink="true">{rsslink}</guid>
<link>{rsslink}</link>
<description>{short-story}</description>
<category>{category}</category>
<dc:creator>{rssauthor}</dc:creator>
<pubDate>{rssdate}</pubDate>
</item>[/shortrss]
[fullrss]<item>
<title>{title}</title>
<guid isPermaLink="true">{rsslink}</guid>
<link>{rsslink}</link>
<description><![CDATA[{short-story}]]></description>
<category><![CDATA[{category}]]></category>
<dc:creator>{rssauthor}</dc:creator>
<pubDate>{rssdate}</pubDate>
</item>[/fullrss]
[yandexrss]<item>
<title>{title}</title>
<link>{rsslink}</link>
<description>{short-story}</description>
<category>{category}</category>{images}
<pubDate>{rssdate}</pubDate>
<yandex:full-text>{full-story}</yandex:full-text>
</item>[/yandexrss]
HTML;

	$tpl->copy_template = $tpl->template;

} else {
	
	$tpl->load_template( 'rss.tpl' );
	
}


if( $config['site_offline'] OR ! $config['allow_rss'] ) {
	
	$rss_content .= <<<XML
<item>
<title>RSS in offline mode</title>
<guid isPermaLink="true"></guid>
<link></link>
<description>RSS in offline mode</description>
<category>undefined</category>
<dc:creator>DataLife Engine</dc:creator>
<pubDate>DataLife Engine</pubDate>
</item>
XML;

} else {
	
	if( $config['rss_format'] == 1 ) {
		
		$tpl->template = str_replace( '[fullrss]', '', $tpl->template );
		$tpl->template = str_replace( '[/fullrss]', '', $tpl->template );
		$tpl->template = preg_replace( "'\\[yandexrss\\](.*?)\\[/yandexrss\\]'si", "", $tpl->template );
		$tpl->template = preg_replace( "'\\[shortrss\\](.*?)\\[/shortrss\\]'si", "", $tpl->template );
		$tpl->template = trim($tpl->template);
		
	} elseif( $config['rss_format'] == 2 ) {
		
		$rss_content = <<<XML
<?xml version="1.0" encoding="{$config['charset']}"?>
<rss xmlns:yandex="http://news.yandex.ru" xmlns:media="http://search.yahoo.com/mrss/" version="2.0">
<channel>
<title>{$config['home_title']}</title>
<link>{$config['http_home_url']}</link>
<language>ru</language>
<description>{$config['home_title']}</description>
<yandex:logo>{$config['http_home_url']}yandexlogo.png</yandex:logo>
<yandex:logo type="square">{$config['http_home_url']}yandexsquarelogo.png</yandex:logo>
<generator>DataLife Engine</generator>
XML;
		
		$tpl->template = str_replace( '[yandexrss]', '', $tpl->template );
		$tpl->template = str_replace( '[/yandexrss]', '', $tpl->template );
		$tpl->template = preg_replace( "'\\[fullrss\\](.*?)\\[/fullrss\\]'si", "", $tpl->template );
		$tpl->template = preg_replace( "'\\[shortrss\\](.*?)\\[/shortrss\\]'si", "", $tpl->template );
		$tpl->template = trim($tpl->template);		
	} else {
		
		$tpl->template = str_replace( '[shortrss]', '', $tpl->template );
		$tpl->template = str_replace( '[/shortrss]', '', $tpl->template );
		$tpl->template = preg_replace( "'\\[fullrss\\](.*?)\\[/fullrss\\]'si", "", $tpl->template );
		$tpl->template = preg_replace( "'\\[yandexrss\\](.*?)\\[/yandexrss\\]'si", "", $tpl->template );
		$tpl->template = trim($tpl->template);	
	}
	
	$tpl->copy_template = $tpl->template;
	
	include_once ENGINE_DIR . '/engine.php';
	
	$rss_content .= $tpl->result['content'];
}

$rss_content .= '</channel></rss>';

header( "Content-type: application/xml; charset=".$config['charset'] );
echo $rss_content;

?>