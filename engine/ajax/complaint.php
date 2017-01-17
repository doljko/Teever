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
 Файл: complaint.php
-----------------------------------------------------
 Назначение: Отправка жалоб на ПС и комментарии
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
	
	$config['http_home_url'] = explode( "engine/ajax/complaint.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset( $config['http_home_url'] );
	$config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';

dle_session();

$_COOKIE['dle_skin'] = trim(totranslit( $_COOKIE['dle_skin'], false, false ));
$_TIME = time ();

if( $_COOKIE['dle_skin'] ) {
	if( @is_dir( ROOT_DIR . '/templates/' . $_COOKIE['dle_skin'] ) ) {
		$config['skin'] = $_COOKIE['dle_skin'];
	}
}

if( $config["lang_" . $config['skin']] ) {
	
	if ( file_exists( ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng' ) ) {
		@include_once (ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng');
	} else die("Language file not found");

} else {
	
	include_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';

}

$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];

require_once ENGINE_DIR . '/classes/parse.class.php';
require_once ENGINE_DIR . '/modules/sitelogin.php';

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

@header( "Content-type: text/html; charset=" . $config['charset'] );

$parse = new ParseFilter();
$parse->safe_mode = true;
$parse->allow_url = $user_group[$member_id['user_group']]['allow_url'];
$parse->allow_image = $user_group[$member_id['user_group']]['allow_image'];

$id = intval( $_POST['id'] );
$text = convert_unicode( $_POST['text'], $config['charset'] );
$text = $parse->BB_Parse( $parse->process( trim( $text ) ), false );

if (strpos($config['http_home_url'], "//") === 0) $config['http_home_url'] = "http:".$config['http_home_url'];
elseif (strpos($config['http_home_url'], "/") === 0) $config['http_home_url'] = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];

if ( $config['allow_complaint_mail'] ) {

	include_once ENGINE_DIR . '/classes/mail.class.php';
	$mail = new dle_mail( $config );
	$lang['mail_complaint_1'] = str_replace( "{site}", $config['http_home_url'], $lang['mail_complaint_1'] );
}

if ($_POST['action'] == "pm") {

	if( !$is_logged ) die( "error" );

	if( !$id OR !$text) die( "error" );

	$row = $db->super_query( "SELECT id, text, user, user_from FROM " . USERPREFIX . "_pm WHERE id='{$id}'" );

	if( $row['user'] != $member_id['user_id'] OR !$row['id']) die("Operation not Allowed");

	if ($row['user_from'] == $member_id['name']) { echo $lang['error_complaint_2']; die(); }

	$db->query( "SELECT id FROM " . PREFIX . "_complaint WHERE p_id='{$id}'" );

	if ($db->num_rows()) { echo $lang['error_complaint_1']; die(); }

	$row['text'] = "<div class=\"quote\">".stripslashes( $row['text'] )."</div>";

	$text = $db->safesql( $row['text'].$text );
	$member_id['name'] = $db->safesql($member_id['name']);
	$row['user_from'] = $db->safesql($row['user_from']);

	$db->query( "INSERT INTO " . PREFIX . "_complaint (`p_id`, `c_id`, `n_id`, `text`, `from`, `to`, `date`) values ('{$row['id']}', '0', '0', '{$text}', '{$member_id['name']}', '{$row['user_from']}', '{$_TIME}')" );

	if ( $config['allow_complaint_mail'] ) {
		$mail->send( $config['admin_mail'], $lang['mail_complaint'], $lang['mail_complaint_1'] );	
	}

} elseif ($_POST['action'] == "comments") {

	if( !$is_logged ) die( "error" );

	if( !$id OR !$text) die( "error" );

	$row = $db->super_query( "SELECT id, autor FROM " . PREFIX . "_comments WHERE id='{$id}'" );

	if(!$row['id']) die("Operation not Allowed");

	if ($row['autor'] == $member_id['name']) { echo $lang['error_complaint_2']; die(); }

	$member_id['name'] = $db->safesql($member_id['name']);

	$db->query( "SELECT id FROM " . PREFIX . "_complaint WHERE c_id='{$id}' AND `from`='{$member_id['name']}'" );

	if ($db->num_rows()) { echo $lang['error_complaint_1']; die(); }

	$text = $db->safesql( $text );

	$db->query( "INSERT INTO " . PREFIX . "_complaint (`p_id`, `c_id`, `n_id`, `text`, `from`, `to`, `date`) values ('0', '{$row['id']}', '0', '{$text}', '{$member_id['name']}', '', '{$_TIME}')" );

	if ( $config['allow_complaint_mail'] ) {
		$mail->send( $config['admin_mail'], $lang['mail_complaint'], $lang['mail_complaint_1'] );	
	}

} elseif ($_POST['action'] == "news") {

	if( !$is_logged ) die( "error" );

	if( !$id OR !$text) die( "error" );

	$row = $db->super_query( "SELECT id, autor FROM " . PREFIX . "_post WHERE id='{$id}'" );

	if(!$row['id']) die("Operation not Allowed");

	$member_id['name'] = $db->safesql($member_id['name']);

	$db->query( "SELECT id FROM " . PREFIX . "_complaint WHERE n_id='{$id}' AND `from`='{$member_id['name']}'" );

	if ($db->num_rows()) { echo $lang['error_complaint_1']; die(); }

	$text = $db->safesql( $text );

	$db->query( "INSERT INTO " . PREFIX . "_complaint (`p_id`, `c_id`, `n_id`, `text`, `from`, `to`, `date`) values ('0', '0', '{$row['id']}', '{$text}', '{$member_id['name']}', '', '{$_TIME}')" );

	if ( $config['allow_complaint_mail'] ) {
		$mail->send( $config['admin_mail'], $lang['mail_complaint'], $lang['mail_complaint_1'] );	
	}

} elseif ($_POST['action'] == "orfo") {

	if(!$text) die( "error" );

	$seltext = convert_unicode( $_POST['seltext'], $config['charset'] );
	$seltext = htmlspecialchars( $parse->process( trim( $seltext ) ), ENT_QUOTES, $config['charset'] );
	$url = $db->safesql( htmlspecialchars( $parse->clear_url( trim( $_POST['url'] ) ), ENT_QUOTES, $config['charset'] ) );

	if(!$seltext) die( "error" );

	if( !$is_logged ) $author = $_IP; else $author = $db->safesql($member_id['name']);

	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_complaint WHERE p_id='0' AND c_id='0' AND n_id='0' AND `from`='{$author}'" );

	if ($row['count'] > 2 ) { echo $lang['error_complaint_1']; die(); }

	$seltext = "<div class=\"quote\">".stripslashes( $seltext )."</div>";
	$text = $db->safesql( $seltext.$text );

	$db->query( "INSERT INTO " . PREFIX . "_complaint (`p_id`, `c_id`, `n_id`, `text`, `from`, `to`, `date`) values ('0', '0', '0', '{$text}', '{$author}', '{$url}', '{$_TIME}')" );

	if ( $config['allow_complaint_mail'] ) {
		$mail->send( $config['admin_mail'], $lang['mail_complaint'], $lang['mail_complaint_1'] );	
	}

}
echo "ok";
?>