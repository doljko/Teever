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
 Файл: deletecomments.php
-----------------------------------------------------
 Назначение: удаление комментария
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

dle_session();

require_once ENGINE_DIR . '/modules/sitelogin.php';

$_TIME = time ();

if( !$is_logged ) die( "error" );

$id = intval( $_REQUEST['id'] );

if( ! $id ) die( "error" );

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

function deletecomments( $id ) {
	global $config, $db;
	
	$id = intval($id);

	$row = $db->super_query( "SELECT id, post_id, user_id, is_register, approve FROM " . PREFIX . "_comments WHERE id = '{$id}'" );
	
	$db->query( "DELETE FROM " . PREFIX . "_comments WHERE id = '{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_comment_rating_log WHERE c_id = '{$id}'" );	

	if( $row['is_register'] ) {
		$db->query( "UPDATE " . USERPREFIX . "_users SET comm_num=comm_num-1 WHERE user_id ='{$row['user_id']}'" );
	}
	
	if($row['approve']) $db->query( "UPDATE " . PREFIX . "_post SET comm_num=comm_num-1 WHERE id='{$row['post_id']}'" );

	if ( $config['tree_comments'] ) {

		$sql_result = $db->query( "SELECT id FROM " . PREFIX . "_comments WHERE parent = '{$id}'" );
	
		while ( $row = $db->get_row( $sql_result ) ) {
			deletecomments( $row['id'] );
		}

	}

}

$row = $db->super_query( "SELECT id, post_id, user_id, date, is_register FROM " . PREFIX . "_comments WHERE id = '{$id}'" );

if ($row['id'])	{

	$have_perm = false;
	$row['date'] = strtotime( $row['date'] );

	if( $_GET['dle_allow_hash'] != "" AND $_GET['dle_allow_hash'] == $dle_login_hash AND (($member_id['user_id'] == $row['user_id'] AND $row['is_register'] AND $user_group[$member_id['user_group']]['allow_delc']) OR $member_id['user_group'] == '1' OR $user_group[$member_id['user_group']]['del_allc']) ) $have_perm = true;

	if ( $user_group[$member_id['user_group']]['edit_limit'] AND (($row['date'] + ($user_group[$member_id['user_group']]['edit_limit'] * 60)) < $_TIME) ) {
		$have_perm = false;
	}

	if( $have_perm ) {
		deletecomments( $row['id'] );

		if ( $config['allow_alt_url'] AND !$config['seo_type'] ) $cprefix = "full_"; else $cprefix = "full_".$row['post_id'];

		clear_cache( array( 'news_', 'rss', 'comm_'.$row['post_id'], $cprefix ) );
		
		@header( "Content-type: text/html; charset=" . $config['charset'] );
		echo $row['id'];
	
	} else die( "error" );

} else die( "error" );
?>