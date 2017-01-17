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
 Файл: find_relates.php
-----------------------------------------------------
 Назначение: Поиск похожих новостей
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
	
	$config['http_home_url'] = explode( "engine/ajax/find_relates.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset( $config['http_home_url'] );
	$config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';

dle_session();

require_once ENGINE_DIR . '/modules/sitelogin.php';
require_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';

if( ! $is_logged ) die( "error" );

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

//####################################################################################################################
//                    Определение категорий и их параметры
//####################################################################################################################
$cat_info = get_vars( "category" );

if( ! is_array( $cat_info ) ) {
	$cat_info = array ();
	
	$db->query( "SELECT * FROM " . PREFIX . "_category ORDER BY posi ASC" );
	while ( $row = $db->get_row() ) {
		
		$cat_info[$row['id']] = array ();
		
		foreach ( $row as $key => $value ) {
			$cat_info[$row['id']][$key] = stripslashes( $value );
		}
	
	}
	set_vars( "category", $cat_info );
	$db->free();
}

$title = $db->safesql( trim( convert_unicode( $_POST['title'], $config['charset'] ) ) );

if( $title == "" ) die();

$buffer = "";

$id = intval( $_POST['id'] );
$mode = intval( $_POST['mode'] );

if ( $mode ) {
	if( !$user_group[$member_id['user_group']]['allow_adds'] ) die( "error" );
} else {
	if( !$user_group[$member_id['user_group']]['allow_admin'] ) die( "error" );
}

if( $id ) $where = " AND id != '" . $id . "'";
else $where = "";

$db->query( "SELECT id, title, date, category, alt_name, MATCH (title, short_story, full_story, xfields) AGAINST ('$title') as score FROM " . PREFIX . "_post WHERE MATCH (title, short_story, full_story, xfields) AGAINST ('$title') AND approve='1'" . $where . " ORDER BY score DESC, date DESC LIMIT 5" );

while ( $related = $db->get_row() ) {
	
	$related['date'] = strtotime( $related['date'] );
	$related['category'] = intval( $related['category'] );
	$news_date = date( 'd-m-Y', $related['date'] );
	
	if( $config['allow_alt_url'] ) {
		
		if( $config['seo_type'] == 1 OR  $config['seo_type'] == 2 ) {
			
			if( $related['category'] and $config['seo_type'] == 2 ) {
				
				$full_link = $config['http_home_url'] . get_url( $related['category'] ) . "/" . $related['id'] . "-" . $related['alt_name'] . ".html";
			
			} else {
				
				$full_link = $config['http_home_url'] . $related['id'] . "-" . $related['alt_name'] . ".html";
			
			}
		
		} else {
			
			$full_link = $config['http_home_url'] . date( 'Y/m/d/', $related['date'] ) . $related['alt_name'] . ".html";
		}
	
	} else {
		
		$full_link = $config['http_home_url'] . "index.php?newsid=" . $related['id'];
	
	}

	if ( dle_strlen($related['title'], $config['charset']) > 65 ) $related['title'] = dle_substr ($related['title'], 0, 65, $config['charset'])." ...";

	if ( $user_group[$member_id['user_group']]['allow_all_edit'] ) {

		$d_link = "<a href=\"?mod=editnews&action=editnews&id={$related['id']}\" target=\"_blank\"><img style=\"vertical-align: middle;border:none;\" alt=\"{$lang['edit_rel']}\" src=\"engine/skins/images/notepad.png\" /></a>&nbsp;&nbsp;<a onclick=\"confirmDelete('?mod=editnews&action=doeditnews&ifdelete=yes&id={$related['id']}&user_hash={$dle_login_hash}', '{$related['id']}'); return false;\" href=\"?mod=editnews&action=doeditnews&ifdelete=yes&id={$related['id']}&user_hash={$dle_login_hash}\" target=\"_blank\"><img style=\"vertical-align: middle;border:none;\" alt=\"{$lang['edit_seldel']}\" src=\"engine/skins/images/delete.png\" /></a>&nbsp;&nbsp;";

	} else $d_link = "";

	if ( $mode ) $d_link = "";
	
	$buffer .= "<div style=\"padding:2px;\">{$d_link}{$news_date} - <a href=\"" . $full_link . "\" target=\"_blank\">" . stripslashes( $related['title'] ) . "</a></div>";

}
$db->close();

@header( "Content-type: text/html; charset=" . $config['charset'] );

if( $buffer ) echo "<div class=\"findrelated\">" . $buffer . "</div>";
else echo "<div class=\"findrelated\">" . $lang['related_not_found'] . "</div>";

?>