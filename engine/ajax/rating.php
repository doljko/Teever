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
 Файл: rating.php
-----------------------------------------------------
 Назначение: AJAX для рейтинга новостей
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
	
	$config['http_home_url'] = explode( "engine/ajax/rating.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset( $config['http_home_url'] );
	$config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';

dle_session();

$_REQUEST['skin'] = totranslit($_REQUEST['skin'], false, false);

if( $_REQUEST['skin'] ) {
	if( @is_dir( ROOT_DIR . '/templates/' . $_REQUEST['skin'] ) ) {
		$config['skin'] = $_REQUEST['skin'];
	} else {
		die( "Hacking attempt!" );
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

require_once ENGINE_DIR . '/modules/sitelogin.php';

@header( "Content-type: text/html; charset=" . $config['charset'] );

if( ! $is_logged ) $member_id['user_group'] = 5;

if( ! $user_group[$member_id['user_group']]['allow_rating'] ) {
		echo "{\"error\":true, \"errorinfo\":\"{$lang['rating_error3']}\"}";
		die();
}

if( $_REQUEST['go_rate'] == "minus" ) $_REQUEST['go_rate'] = -1;
if( $_REQUEST['go_rate'] == "plus" ) $_REQUEST['go_rate'] = 1;

$go_rate = intval( $_REQUEST['go_rate'] );
$news_id = intval( $_REQUEST['news_id'] );

if ( !$config['rating_type'] ) {
	if( $go_rate > 5 or $go_rate < 1 ) $go_rate = false;
}

if ( $config['rating_type'] == "1" ) {
	$go_rate = 1;
}

if ( $config['rating_type'] == "2" ) {
	if( $go_rate != 1 AND $go_rate != -1 ) $go_rate = false;
}

if( !$go_rate or !$news_id ) {
	echo "{\"error\":true, \"errorinfo\":\"{$lang['rating_error3']}\"}";
	die();
}


$member_id['name'] = $db->safesql($member_id['name']);

if( $is_logged ) $where = "member = '{$member_id['name']}'";
else $where = "ip ='{$_IP}'";

$row = $db->super_query( "SELECT news_id, rating FROM " . PREFIX . "_logs WHERE news_id ='{$news_id}' AND {$where}" );

if( !$row['news_id'] ) {

	$allrate = $db->super_query( "SELECT rating, user_id FROM " . PREFIX . "_post_extras WHERE news_id ='{$news_id}'" );
	
	if( $allrate['user_id'] == $member_id['user_id'] ) {
		
		$db->close();
		
		echo "{\"error\":true, \"errorinfo\":\"{$lang['rating_error1']}\"}";
		die();
	}

	if( $config['rating_type'] == "1" AND $allrate['rating'] < 0 ) {
		
		$db->query( "UPDATE " . PREFIX . "_post_extras SET rating='{$go_rate}', vote_num='1' WHERE news_id ='{$news_id}'" );
		
	} elseif ( !$config['rating_type'] AND $allrate['rating'] < 0 ) {
		
		$db->query( "UPDATE " . PREFIX . "_post_extras SET rating='{$go_rate}', vote_num='1' WHERE news_id ='{$news_id}'" );
		
	} else {
		
		$db->query( "UPDATE " . PREFIX . "_post_extras SET rating=rating+'{$go_rate}', vote_num=vote_num+1 WHERE news_id ='{$news_id}'" );
		
	}	

	if ( $db->get_affected_rows() )	{
		if( $is_logged ) $user_name = $member_id['name'];
		else $user_name = "noname";
		
		$db->query( "INSERT INTO " . PREFIX . "_logs (news_id, ip, member, rating) values ('{$news_id}', '{$_IP}', '{$user_name}', '{$go_rate}')" );

		if ( $config['allow_alt_url'] AND !$config['seo_type'] ) $cprefix = "full_"; else $cprefix = "full_".$news_id;	
	
		clear_cache( array( 'news_', 'rss', $cprefix ) );

	}
	
} elseif( $row['rating'] AND $row['rating'] != $go_rate ) {
	
	$allrate = $db->super_query( "SELECT rating, user_id FROM " . PREFIX . "_post_extras WHERE news_id ='{$news_id}'" );
	
	if( $config['rating_type'] == "1" AND $allrate['rating'] < 0 ) {
		
		$db->query( "UPDATE " . PREFIX . "_post_extras SET rating='{$go_rate}', vote_num='1' WHERE news_id ='{$news_id}'" );
		
	} elseif ( !$config['rating_type'] AND $allrate['rating'] < 0 ) {
		
		$db->query( "UPDATE " . PREFIX . "_post_extras SET rating='{$go_rate}', vote_num='1' WHERE news_id ='{$news_id}'" );
		
	} else {
		
		$db->query( "UPDATE " . PREFIX . "_post_extras SET rating=rating-'{$row['rating']}' WHERE news_id ='{$news_id}'" );
		$db->query( "UPDATE " . PREFIX . "_post_extras SET rating=rating+'{$go_rate}' WHERE news_id ='{$news_id}'" );
		
	}
	
	$db->query( "UPDATE " . PREFIX . "_logs SET rating='{$go_rate}' WHERE news_id ='{$news_id}' AND {$where}" );
	
} else {
	
	$db->close();
	
	echo "{\"error\":true, \"errorinfo\":\"{$lang['rating_error2']}\"}";
	die();
}

$row = $db->super_query( "SELECT news_id, rating, vote_num FROM " . PREFIX . "_post_extras WHERE news_id ='$news_id'" );

$buffer = ShowRating( $row['news_id'], $row['rating'], $row['vote_num'], true );

$buffer = addcslashes($buffer, "\t\n\r\"\\/");

$buffer = htmlspecialchars("{\"success\":true, \"rating\":\"{$buffer}\", \"votenum\":\"{$row['vote_num']}\"}", ENT_NOQUOTES, $config['charset']);

$db->close();

echo $buffer;
?>