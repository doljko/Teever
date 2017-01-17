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
 Файл: preview.php
-----------------------------------------------------
 Назначение: Предосмотр новостей на сайте
=====================================================
*/


@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

if ( !count($_POST) ) die("Hacking attempt!");

define( 'DATALIFEENGINE', true );
define( 'ROOT_DIR', substr( dirname(  __FILE__ ), 0, -7 ) );
define( 'ENGINE_DIR', ROOT_DIR . '/engine' );

require ENGINE_DIR . '/data/config.php';

date_default_timezone_set ( $config['date_adjust'] );

if( $config['http_home_url'] == "" ) {
	
	$config['http_home_url'] = explode( "engine/preview.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset( $config['http_home_url'] );
	$config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';
require_once ENGINE_DIR . '/classes/templates.class.php';

check_xss();

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

if( $_COOKIE['dle_skin'] ) {

	$_COOKIE['dle_skin'] = trim( totranslit($_COOKIE['dle_skin'], false, false) );

	if( $_COOKIE['dle_skin'] != '' AND @is_dir( ROOT_DIR . '/templates/' . $_COOKIE['dle_skin'] ) ) {
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

if ($config['allow_registration']) {
	
	include_once ENGINE_DIR . '/modules/sitelogin.php';
}

if (!$is_logged) $member_id['user_group'] = 5;

if ( !$user_group[$member_id['user_group']]['allow_html'] ) {

	$config['allow_site_wysiwyg'] = 0;
	$_POST['short_story'] = strip_tags ($_POST['short_story']);
	$_POST['full_story'] = strip_tags ($_POST['full_story']);

}

$tpl = new dle_template( );
$tpl->allow_php_include = false;
$tpl->dir = ROOT_DIR . '/templates/' . $config['skin'];

@header( "Cache-Control: no-cache, must-revalidate, max-age=0" );
@header( "Expires: 0" );
@header( "Content-type: text/html; charset=" . $config['charset'] );

$css = file_get_contents( $tpl->dir."/".'preview.css' );

if( $config['allow_site_wysiwyg'] == 1 ) {

$editor_files = <<<HTML
<link media="screen" href="{$config['http_home_url']}engine/editor/css/default.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js"></script>
<script type="text/javascript" src="{$config['http_home_url']}engine/editor/scripts/webfont.js"></script>
HTML;


} else $editor_files = "";

echo <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta content="text/html; charset={$config['charset']}" http-equiv=Content-Type>
<link media="screen" href="{$config['http_home_url']}engine/classes/html5player/mediaelementplayer.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="{$config['http_home_url']}engine/classes/js/jquery.js"></script>
<script type="text/javascript" src="{$config['http_home_url']}engine/classes/js/jqueryui.js"></script>
<script type="text/javascript" src="{$config['http_home_url']}engine/classes/js/dle_js.js"></script>
<script type="text/javascript" src="{$config['http_home_url']}engine/classes/html5player/mediaelement-and-player.min.js"></script>
<style type="text/css">
{$css}
</style>
{$editor_files}
</head> 
<body>
<script type="text/javascript" src="{$config['http_home_url']}engine/classes/highslide/highslide.js"></script>
<script type="text/javascript" src="{$config['http_home_url']}engine/classes/highlight/highlight.code.js"></script>
<script type="text/javascript">
	var dle_root = '{$config['http_home_url']}';
	
	hljs.initHighlightingOnLoad();

    hs.graphicsDir = '{$config['http_home_url']}engine/classes/highslide/graphics/';
    hs.outlineType = 'rounded-white';
    hs.numberOfImagesToPreload = 0;
    hs.showCredits = false;
    hs.loadingText = '{$lang['ajax_info']}';
    hs.fullExpandTitle = '{$lang['thumb_expandtitle']}';
    hs.restoreTitle = '{$lang['thumb_restore']}';
    hs.focusTitle = '{$lang['thumb_focustitle']}';
    hs.loadingTitle = '{$lang['thumb_cancel']}';
</script>
HTML;

$tpl->clear();

//####################################################################################################################
//                    Определение категорий и их параметры
//####################################################################################################################
$result_cat = $db->query( "SELECT * FROM " . PREFIX . "_category ORDER BY posi ASC" );

while ( $row = $db->get_row( $result_cat ) ) {
	$cat[$row['id']] = $row['name'];
	$cat_icon[$row['id']] = $row['icon'];
	$cat_alt_name[$row['id']] = $row['alt_name'];
	$cat_parentid[$row['id']] = $row['parentid'];

}
$db->free( $result_cat );

include_once ENGINE_DIR . '/classes/parse.class.php';
$parse = new ParseFilter( Array (), Array (), 1, 1 );

if( $config['allow_site_wysiwyg'] ) $allow_br = 0; else $allow_br = 1;

if( $config['allow_site_wysiwyg'] ) {
	$title = stripslashes( $parse->process( $_POST['title'] ) );
	$parse->allow_code = false;
	$full_story = $parse->process( $_POST['full_story'] );
	$short_story = $parse->process( $_POST['short_story'] );
	
	$full_story = $parse->BB_Parse( $full_story );
	$short_story = $parse->BB_Parse( $short_story );

} else {
	$full_story = $parse->process( $_POST['full_story'] );
	$short_story = $parse->process( $_POST['short_story'] );
	$title = stripslashes( $parse->process( $_POST['title'] ) );
	$full_story = $parse->BB_Parse( $full_story, false );
	$short_story = $parse->BB_Parse( $short_story, false );
}

if( is_array( $_REQUEST['catlist'] ) ) $catlist = $_REQUEST['catlist'];
else {$catlist = array (); $_REQUEST['catlist'] = array (); }

if( ! count( $catlist ) ) {
	$my_cat = "---";
	$my_cat_link = "---";
} else {
	
	$my_cat = array ();
	$my_cat_link = array ();
	
	foreach ( $catlist as $element ) {
		if( $element ) {
			$my_cat[] = $cat[$element];
			$my_cat_link[] = "<a href=\"#\">{$cat[$element]}</a>";
		}
	}
	$my_cat = stripslashes( implode( ', ', $my_cat ) );
	$my_cat_link = stripslashes( implode( ', ', $my_cat_link ) );
}

$dle_module = "main";

if ( @is_file($tpl->dir."/preview.tpl") ) $tpl->load_template('preview.tpl');
else $tpl->load_template('shortstory.tpl');
 
if ( $parse->not_allowed_text ) $tpl->copy_template = $lang['news_err_39'];

$tpl->copy_template = preg_replace( "#\\{custom(.+?)\\}#i", "", $tpl->copy_template);
$tpl->template = preg_replace( "#\\{custom(.+?)\\}#i", "", $tpl->template);
	
$tpl->set('[short-preview]', "");
$tpl->set('[/short-preview]', "");
$tpl->set_block("'\\[full-preview\\](.*?)\\[/full-preview\\]'si","");
$tpl->set_block("'\\[static-preview\\](.*?)\\[/static-preview\\]'si","");

$tpl->set( '{title}', $title );

	if ( preg_match( "#\\{title limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches ) ) {
		$count= intval($matches[1]);
		$title = strip_tags( $title );

		if( $count AND dle_strlen( $title, $config['charset'] ) > $count ) {
						
			$title = dle_substr( $title, 0, $count, $config['charset'] );
						
			if( ($temp_dmax = dle_strrpos( $title, ' ', $config['charset'] )) ) $title = dle_substr( $title, 0, $temp_dmax, $config['charset'] );
					
		}

		$tpl->set( $matches[0], $title );

		
	}

	if( !count( $_REQUEST['catlist'] ) ) {
		$_REQUEST['catlist'] = array ();
		$_REQUEST['catlist'][] = '0';
	}

	$c_list = array();

	foreach ( $_REQUEST['catlist'] as $value ) {
		$c_list[] = intval($value);
	}
	$category_id = implode (',', $c_list);

	if( strpos( $tpl->copy_template, "[catlist=" ) !== false ) {
		$tpl->copy_template = preg_replace_callback ( "#\\[(catlist)=(.+?)\\](.*?)\\[/catlist\\]#is", "check_category", $tpl->copy_template );
	}
		
	if( strpos( $tpl->copy_template, "[not-catlist=" ) !== false ) {
		$tpl->copy_template = preg_replace_callback ( "#\\[(not-catlist)=(.+?)\\](.*?)\\[/not-catlist\\]#is", "check_category", $tpl->copy_template );
	}

$tpl->set( '{views}', 0 );
$date = time ();
$tpl->set( '{date}', langdate( $config['timestamp_active'], $date ) );
$news_date = $date;
$tpl->copy_template = preg_replace_callback ( "#\{date=(.+?)\}#i", "formdate", $tpl->copy_template );
$tpl->set( '[link]', "<a href=#>" );
$tpl->set( '[/link]', "</a>" );
$tpl->set( '{comments-num}', 0 );
$tpl->set( '[full-link]', "<a href=#>" );
$tpl->set( '[/full-link]', "</a>" );
$tpl->set( '[com-link]', "<a href=#>" );
$tpl->set( '[/com-link]', "</a>" );
$tpl->set( '[day-news]', "<a href=#>");
$tpl->set( '[/day-news]', "</a>");
$tpl->set( '{rating}', "" );
$tpl->set( '[rating]', "" );
$tpl->set( '[/rating]', "" );
$tpl->set( '{author}', "--" );
$tpl->set( '{approve}', "" );
$tpl->set( '{category}', $my_cat );
$tpl->set( '{favorites}', '' );
$tpl->set( '{link-category}', $my_cat_link );
$tpl->set( '{edit-date}', "" );
$tpl->set( '{editor}', "" );
$tpl->set( '{edit-reason}', "" );
$tpl->set_block( "'\\[edit-date\\](.*?)\\[/edit-date\\]'si", "" );
$tpl->set_block( "'\\[edit-reason\\](.*?)\\[/edit-reason\\]'si", "" );
$tpl->set_block( "'\\[complaint\\](.*?)\\[/complaint\\]'si", "" );
$tpl->set_block( "'\\[add-favorites\\](.*?)\\[/add-favorites\\]'si", "" );
$tpl->set_block( "'\\[del-favorites\\](.*?)\\[/del-favorites\\]'si", "" );
$tpl->set_block( "'\\[rating-plus\\](.*?)\\[/rating-plus\\]'si", "" );
$tpl->set_block( "'\\[rating-minus\\](.*?)\\[/rating-minus\\]'si", "" );
$tpl->set_block( "'\\[rating-type-1\\](.*?)\\[/rating-type-1\\]'si", "" );
$tpl->set_block( "'\\[rating-type-3\\](.*?)\\[/rating-type-3\\]'si", "" );
$tpl->set_block( "'\\[rating-type-2\\](.*?)\\[/rating-type-2\\]'si", "" );	
if( $cat_icon[$category[0]] != "" ) {
	$tpl->set( '{category-icon}', $cat_icon[$category[0]] );
} else {
	$tpl->set( '{category-icon}', "{THEME}/dleimages/no_icon.gif" );
}

$tpl->set_block( "'\\[tags\\](.*?)\\[/tags\\]'si", "" );
$tpl->set( '{tags}', "" );

if ( $_POST['news_fixed'] ) {

	$tpl->set( '[fixed]', "" );
	$tpl->set( '[/fixed]', "" );
	$tpl->set_block( "'\\[not-fixed\\](.*?)\\[/not-fixed\\]'si", "" );

} else {

	$tpl->set( '[not-fixed]', "" );
	$tpl->set( '[/not-fixed]', "" );
	$tpl->set_block( "'\\[fixed\\](.*?)\\[/fixed\\]'si", "" );
}

$tpl->set( '[mail]', "" );
$tpl->set( '[/mail]', "" );
$tpl->set( '{news-id}', "ID Unknown" );

$tpl->copy_template = preg_replace( "#\\[category=(.+?)\\](.*?)\\[/category\\]#is", "\\2", $tpl->copy_template );

$tpl->set_block( "'\\[edit\\].*?\\[/edit\\]'si", "" );
$tpl->set_block( "'{banner_(.*?)}'si", "" );

$xfieldsaction = "templatereplacepreview";
$xfieldsinput = $tpl->copy_template;
include (ENGINE_DIR . '/inc/xfields.php');
$tpl->copy_template = $xfieldsoutput;

$tpl->set( '{short-story}', stripslashes( $short_story ) );
$tpl->set( '{full-story}', stripslashes( $full_story ) );

$tpl->copy_template = "<fieldset style=\"border-style:solid; border-width:1; border-color:black;\"><legend> <span style=\"font-size: 10px; font-family: Verdana\">{$lang['preview_short']}</span> </legend>" . $tpl->copy_template . "</fieldset>";
$tpl->compile( 'shortstory' );
$tpl->result['shortstory'] = str_replace( "[hide]", "", str_replace( "[/hide]", "", $tpl->result['shortstory']) );
$tpl->result['shortstory'] = str_replace ( '{THEME}', $config['http_home_url'] . 'templates/' . $config['skin'], $tpl->result['shortstory'] );

echo $tpl->result['shortstory'];

$dle_module = "showfull";

if ( @is_file($tpl->dir."/preview.tpl") ) $tpl->load_template('preview.tpl');
else $tpl->load_template('fullstory.tpl');

if ( $parse->not_allowed_text ) $tpl->copy_template = $lang['news_err_39'];

$tpl->copy_template = preg_replace( "#\\{custom(.+?)\\}#i", "", $tpl->copy_template);
$tpl->template = preg_replace( "#\\{custom(.+?)\\}#i", "", $tpl->template);
	
$tpl->copy_template = str_replace('[full-preview]', "", $tpl->copy_template);
$tpl->copy_template = str_replace('[/full-preview]', "", $tpl->copy_template);
$tpl->copy_template = preg_replace("'\\[short-preview\\](.*?)\\[/short-preview\\]'si","", $tpl->copy_template);
$tpl->copy_template = preg_replace("'\\[static-preview\\](.*?)\\[/static-preview\\]'si","", $tpl->copy_template);

if( strlen( $full_story ) < 10 AND strpos( $tpl->copy_template, "{short-story}" ) === false ) {
	$full_story = $short_story;
}

$tpl->set( '{title}', $title );

	if ( preg_match( "#\\{title limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches ) ) {
		$count= intval($matches[1]);
		$title = strip_tags( $title );

		if( $count AND dle_strlen( $title, $config['charset'] ) > $count ) {
						
			$title = dle_substr( $title, 0, $count, $config['charset'] );
						
			if( ($temp_dmax = dle_strrpos( $title, ' ', $config['charset'] )) ) $title = dle_substr( $title, 0, $temp_dmax, $config['charset'] );
					
		}

		$tpl->set( $matches[0], $title );

		
	}

	if( !count( $_REQUEST['catlist'] ) ) {
		$_REQUEST['catlist'] = array ();
		$_REQUEST['catlist'][] = '0';
	}

	$c_list = array();

	foreach ( $_REQUEST['catlist'] as $value ) {
		$c_list[] = intval($value);
	}
	$category_id = implode (',', $c_list);

	if( strpos( $tpl->copy_template, "[catlist=" ) !== false ) {
		$tpl->copy_template = preg_replace_callback ( "#\\[(catlist)=(.+?)\\](.*?)\\[/catlist\\]#is", "check_category", $tpl->copy_template );
	}
		
	if( strpos( $tpl->copy_template, "[not-catlist=" ) !== false ) {
		$tpl->copy_template = preg_replace_callback ( "#\\[(not-catlist)=(.+?)\\](.*?)\\[/not-catlist\\]#is", "check_category", $tpl->copy_template );
	}

$tpl->set( '{views}', 0 );
$tpl->set( '{poll}', '' );
$tpl->set( '{date}', langdate( $config['timestamp_active'], $date ) );
$news_date = $date;
$tpl->copy_template = preg_replace_callback ( "#\{date=(.+?)\}#i", "formdate", $tpl->copy_template );
$tpl->set( '[link]', "<a href=#>" );
$tpl->set( '[/link]', "</a>" );
$tpl->set( '{comments-num}', 0 );
$tpl->set( '[full-link]', "<a href=#>" );
$tpl->set( '[/full-link]', "</a>" );
$tpl->set( '[com-link]', "<a href=#>" );
$tpl->set( '[/com-link]', "</a>" );
$tpl->set( '[day-news]', "<a href=#>");
$tpl->set( '[/day-news]', "</a>");
$tpl->set( '{rating}', "" );
$tpl->set( '[rating]', "" );
$tpl->set( '[/rating]', "" );
$tpl->set( '{author}', "--" );
$tpl->set( '{category}', $my_cat );
$tpl->set( '{link-category}', $my_cat_link );
$tpl->set( '{related-news}', "" );
$tpl->set('{vote-num}', "0");
if( $cat_icon[$category[0]] != "" ) {
	$tpl->set( '{category-icon}', $cat_icon[$category[0]] );
} else {
	$tpl->set( '{category-icon}', "{THEME}/dleimages/no_icon.gif" );
}
$tpl->set( '{edit-date}', "" );
$tpl->set( '{editor}', "" );
$tpl->set( '{edit-reason}', "" );
$tpl->set_block( "'\\[edit-date\\](.*?)\\[/edit-date\\]'si", "" );
$tpl->set_block( "'\\[edit-reason\\](.*?)\\[/edit-reason\\]'si", "" );
$tpl->set( '{pages}', '' );
$tpl->set( '{favorites}', '' );
$tpl->set( '[mail]', "" );
$tpl->set( '[/mail]', "" );
$tpl->set( '{news-id}', "ID Unknown" );
$tpl->set_block( "'\\[tags\\](.*?)\\[/tags\\]'si", "" );
$tpl->set( '{tags}', "" );
$tpl->set_block( "'\\[complaint\\](.*?)\\[/complaint\\]'si", "" );
$tpl->set_block( "'\\[add-favorites\\](.*?)\\[/add-favorites\\]'si", "" );
$tpl->set_block( "'\\[del-favorites\\](.*?)\\[/del-favorites\\]'si", "" );
$tpl->set_block( "'\\[rating-plus\\](.*?)\\[/rating-plus\\]'si", "" );
$tpl->set_block( "'\\[rating-minus\\](.*?)\\[/rating-minus\\]'si", "" );
$tpl->set_block( "'\\[rating-type-1\\](.*?)\\[/rating-type-1\\]'si", "" );
$tpl->set_block( "'\\[rating-type-3\\](.*?)\\[/rating-type-3\\]'si", "" );
$tpl->set_block( "'\\[rating-type-2\\](.*?)\\[/rating-type-2\\]'si", "" );
$tpl->set_block( "'\\[pages\\](.*?)\\[/pages\\]'si", "" );
$tpl->set_block( "'\\[related-news\\](.*?)\\[/related-news\\]'si", "" );
$tpl->set('{addcomments}', "");
$tpl->set('{comments}', "");
$tpl->set('{navigation}', "");
	
if ( $_POST['news_fixed'] ) {

	$tpl->set( '[fixed]', "" );
	$tpl->set( '[/fixed]', "" );
	$tpl->set_block( "'\\[not-fixed\\](.*?)\\[/not-fixed\\]'si", "" );

} else {

	$tpl->set( '[not-fixed]', "" );
	$tpl->set( '[/not-fixed]', "" );
	$tpl->set_block( "'\\[fixed\\](.*?)\\[/fixed\\]'si", "" );
}

$tpl->copy_template = preg_replace( "#\\[category=(.+?)\\](.*?)\\[/category\\]#is", "\\2", $tpl->copy_template );
$tpl->set_block( "'\\[edit\\].*?\\[/edit\\]'si", "" );

$tpl->set( '[print-link]', "<a href=#>" );
$tpl->set( '[/print-link]', "</a>" );
$tpl->set_block( "'{banner_(.*?)}'si", "" );

$xfieldsaction = "templatereplacepreview";
$xfieldsinput = $tpl->copy_template;
include (ENGINE_DIR . '/inc/xfields.php');
$tpl->copy_template = $xfieldsoutput;

$tpl->set( '{short-story}', stripslashes( $short_story ) );
$tpl->set( '{full-story}', stripslashes( $full_story ) );

$tpl->copy_template = "<fieldset style=\"border-style:solid; border-width:1; border-color:black;\"><legend> <span style=\"font-size: 10px; font-family: Verdana\">{$lang['preview_full']}</span> </legend>" . $tpl->copy_template . "</fieldset>";
$tpl->compile( 'fullstory' );
$tpl->result['fullstory'] = str_replace( "[hide]", "", str_replace( "[/hide]", "", $tpl->result['fullstory']) );
$tpl->result['fullstory'] = str_replace ( '{THEME}', $config['http_home_url'] . 'templates/' . $config['skin'], $tpl->result['fullstory'] );

echo $tpl->result['fullstory'];

echo <<<HTML
</body></html>
HTML;

?>