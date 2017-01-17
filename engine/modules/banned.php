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
 Файл: banned.php
-----------------------------------------------------
 Назначение: Забаненные пользователи
=====================================================
*/

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

$this_time = time();
$sel_banned = $db->query( "SELECT users_id FROM " . USERPREFIX . "_banned WHERE days != '0' AND date < '$this_time'" );
$del = false;
$blocked = false;

while ( $row = $db->get_row( $sel_banned ) ) {
	
	$del = true;
	
	if( $row['users_id'] ) $db->query( "UPDATE " . USERPREFIX . "_users SET banned='' WHERE user_id = '{$row['users_id']}'" );

}

$db->free( $sel_banned );

if( $del ) {
	
	$db->query( "DELETE FROM " . USERPREFIX . "_banned WHERE days != '0' AND date < '$this_time'" );
	@unlink( ENGINE_DIR . '/cache/system/banned.php' );

}

if( $blockip ) {
	
	$blocked = true;
	
	if( $banned_info['ip'][$blockip]['date'] ) {
		
		if( $banned_info['ip'][$blockip]['date'] > $this_time ) $endban = langdate( "j M Y H:i", $banned_info['ip'][$blockip]['date'], true );
		else $blocked = false;
	
	} else
		$endban = $lang['banned_info'];
	
	$descr = $lang['ip_block'] . "<br /><br />" . $banned_info['ip'][$blockip]['descr'];

} elseif( $banned_info['users_id'][$member_id['user_id']]['users_id'] ) {
	
	$blocked = true;
	
	if( $banned_info['users_id'][$member_id['user_id']]['date'] ) {
		
		if( $banned_info['users_id'][$member_id['user_id']]['date'] > $this_time ) $endban = langdate( "j M Y H:i", $banned_info['users_id'][$member_id['user_id']]['date'], true );
		else $blocked = false;
	
	} else
		$endban = $lang['banned_info'];
	
	$descr = $banned_info['users_id'][$member_id['user_id']]['descr'];

}

if( $blocked ) {
	
	$tpl->dir = ROOT_DIR . '/templates';
	
	$tpl->load_template( 'banned.tpl' );
	$tpl->set( '{description}', $descr );
	$tpl->set( '{end}', $endban );
	$tpl->compile( 'content' );
	echo $tpl->result['content'];
	die();

}
?>