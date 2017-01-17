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
 Файл: quote.php
-----------------------------------------------------
 Назначение: цитирование комментариев
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

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';
require_once ENGINE_DIR . '/classes/parse.class.php';

dle_session();
$_COOKIE['dle_skin'] = trim(totranslit( $_COOKIE['dle_skin'], false, false ));

if( $_COOKIE['dle_skin'] ) {
	if( @is_dir( ROOT_DIR . '/templates/' . $_COOKIE['dle_skin'] ) ) {
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

$is_logged = false;
$member_id = array ();

if ($config['allow_registration'] ) {
	require_once ENGINE_DIR . '/modules/sitelogin.php';
}

if( ! $is_logged ) {
	$member_id['user_group'] = 5;
}

if ($is_logged AND $member_id['banned'] == "yes") die("error");

$id = intval( $_GET['id'] );
$area = $_GET['area'];

if(!$id) die( "error" );

$parse = new ParseFilter();
$parse->safe_mode = true;

$row = $db->super_query( "SELECT autor, text FROM " . PREFIX . "_comments WHERE id = '{$id}'" );

if (!$row['text']) die( "error" );

if( $config['allow_comments_wysiwyg'] < 1 ) {
	
	$text = $parse->decodeBBCodes( $row['text'], false );
	$text = str_replace( "&quot;", '"', $text );
	$text = str_replace( "&#039;", "'", $text );
	$text = str_replace( "&#39;", "'", $text );
	$text = str_replace( "&#34;", '"', $text );
	$text = str_replace( "&#58;", ":", $text );
	$text = str_replace( "&#91;", "[", $text );
	$text = str_replace( "&#93;", "]", $text );
	$text = str_replace( "&lt;", "<", $text );
	$text = str_replace( "&gt;", ">", $text );
	$text = str_replace( "&#123;", "{", $text );


} else {
	$text = $parse->decodeBBCodes( $row['text'], TRUE, $config['allow_comments_wysiwyg'] );
	$text = preg_replace('/<p[^>]*>/', '', $text); 
	$text = str_replace("</p>", "<br />", $text);	
	$text = preg_replace('/<div[^>]*>/', '', $text); 
	$text = str_replace("</div>", "<br />", $text);
	$text = str_replace( "\r", "", $text );
	$text = str_replace( "\n", "", $text );
	$text = trim($text);

}

if( !$user_group[$member_id['user_group']]['allow_hide'] ) $text = preg_replace ( "#\[hide\](.+?)\[/hide\]#ims", "", $text );

@header( "Content-type: text/html; charset=" . $config['charset'] );
if($area == "admin") {
	echo "<textarea id='edit-comm-{$id}' style=\"width:100%;\" rows=\"7\">{$text}</textarea><br /><br /><a onclick=\"ajax_save_comm_edit('{$id}'); return false;\" href=\"#\" class=\"btn btn-xs btn-green\"><i class=\"icon-ok\"></i> <b>{$lang['bb_b_approve']}</b></a><br /><br />";
} else {
	echo "[quote={$row['autor']}]{$text}[/quote]";
}
?>