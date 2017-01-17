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
 Файл: message.php
-----------------------------------------------------
 Назначение: уведомление о удалении новости
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
	
	$config['http_home_url'] = explode( "engine/ajax/message.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset( $config['http_home_url'] );
	$config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';

dle_session();

require_once ENGINE_DIR . '/classes/parse.class.php';
require_once ENGINE_DIR . '/modules/sitelogin.php';
require_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';

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

if( !$is_logged ) die( "error" );
if ( !$user_group[$member_id['user_group']]['allow_all_edit'] ) die( "error" );

$parse = new ParseFilter( );
$parse->safe_mode = true;
$parse->allow_url = $user_group[$member_id['user_group']]['allow_url'];
$parse->allow_image = $user_group[$member_id['user_group']]['allow_image'];

$id = intval( $_POST['id'] );
$text = convert_unicode( $_POST['text'], $config['charset'] );

if( !$id OR !$text) die( "error" );

$row = $db->super_query( "SELECT id, title, autor FROM " . PREFIX . "_post WHERE id='{$id}'" );

if ( !$row['id'] ) die( "error" );

$title = stripslashes($row['title']);
$row['autor'] = $db->safesql($row['autor']);

$row = $db->super_query( "SELECT email, name, user_id FROM " . USERPREFIX . "_users WHERE name = '{$row['autor']}'" );
			
if( ! $row['user_id'] ) die( "User not found" );

if ($_POST['allowdelete'] == "no" ) {

	$lang['message_pm'] = $lang['message_pm_4'];

	$message = <<<HTML
[b]{$row['name']}[/b],

{$lang['message_pm_1']} "{$title}" {$lang['message_pm_5']} [b]{$member_id['name']}[/b]. 

{$lang['message_pm_6']}

[quote]{$text}[/quote]
HTML;


} else {

$message = <<<HTML
[b]{$row['name']}[/b],

{$lang['message_pm_1']} "{$title}" {$lang['message_pm_2']} [b]{$member_id['name']}[/b]. 

{$lang['message_pm_3']}

[quote]{$text}[/quote]
HTML;

}

$message = $db->safesql( $parse->BB_Parse( $parse->process( trim( $message ) ), false ) );
$time = time();
$member_id['name'] = $db->safesql($member_id['name']);

$db->query( "INSERT INTO " . USERPREFIX . "_pm (subj, text, user, user_from, date, pm_read, folder) values ('{$lang['message_pm']}', '$message', '{$row['user_id']}', '{$member_id['name']}', '$time', 'no', 'inbox')" );
$db->query( "UPDATE " . USERPREFIX . "_users SET pm_all=pm_all+1, pm_unread=pm_unread+1  WHERE user_id='{$row['user_id']}'" );


if( $config['mail_pm'] ) {
			
		include_once ENGINE_DIR . '/classes/mail.class.php';
		
		$mail_template = $db->super_query( "SELECT * FROM " . PREFIX . "_email WHERE name='pm' LIMIT 0,1" );
		$mail = new dle_mail( $config , $mail_template['use_html'] );
		
		$mail_template['template'] = stripslashes( $mail_template['template'] );
		$mail_template['template'] = str_replace( "{%username%}", $row['name'], $mail_template['template'] );
		$mail_template['template'] = str_replace( "{%date%}", langdate( "j F Y H:i", $time ), $mail_template['template'] );
		$mail_template['template'] = str_replace( "{%fromusername%}", $member_id['name'], $mail_template['template'] );
		$mail_template['template'] = str_replace( "{%title%}", $lang['message_pm'], $mail_template['template'] );
		$message = stripslashes( stripslashes( $message) );

		if( !$mail_template['use_html'] ) {
			$message = str_replace( "<br />", "\n", $message );
			$message = str_replace( '&quot;', '"', $message );
			$message = strip_tags( $message );
		}
		
		$mail_template['template'] = str_replace( "{%text%}", $message, $mail_template['template'] );
		
		$mail->send( $row['email'], $lang['mail_pm'], $mail_template['template'] );
		
}

@header( "Content-type: text/html; charset=" . $config['charset'] );
echo "ok";
?>