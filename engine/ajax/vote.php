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
 Файл: vote.php
-----------------------------------------------------
 Назначение: AJAX голосования на сайте
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
	
	$config['http_home_url'] = explode( "engine/ajax/vote.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset( $config['http_home_url'] );
	$config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';

dle_session();

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

$_REQUEST['vote_skin'] = trim(totranslit($_REQUEST['vote_skin'], false, false));

if( $_REQUEST['vote_skin'] ) {
	if( @is_dir( ROOT_DIR . '/templates/' . $_REQUEST['vote_skin'] ) ) {
		$config['skin'] = $_REQUEST['vote_skin'];
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

require_once ENGINE_DIR . '/classes/templates.class.php';
require_once ENGINE_DIR . '/modules/sitelogin.php';

if( !$is_logged ) $member_id['user_group'] = 5;

$rid = intval( $_REQUEST['vote_id'] );
$vote_check = intval( $_REQUEST['vote_check'] );
$nick = $db->safesql($member_id['name']);
$vote_skin = $config['skin'];

$tpl = new dle_template( );
$tpl->dir = ROOT_DIR . '/templates/' . $vote_skin;
define( 'TEMPLATE_DIR', $tpl->dir );

@header( "Content-type: text/html; charset=" . $config['charset'] );

	if( $_REQUEST['vote_action'] == "vote" ) {
		
		if ($user_group[$member_id['user_group']]['allow_vote']) {
	
			if( $is_logged ) $row = $db->super_query( "SELECT count(*) as count FROM " . PREFIX . "_vote_result WHERE vote_id='$rid' AND name='$nick'" );
			else $row = $db->super_query( "SELECT count(*) as count FROM " . PREFIX . "_vote_result WHERE vote_id='$rid' AND ip='$_IP'" );
			
			if( !$row['count'] ) $is_voted = false;
			else $is_voted = true;
	
		} else $is_voted = true;
		
		if( $is_voted == false ) {
			
			if( ! $is_logged ) $nick = "guest";
			
			$db->query( "INSERT INTO " . PREFIX . "_vote_result (ip, name, vote_id, answer) VALUES ('$_IP', '$nick', '$rid', '$vote_check')" );
			
			$db->query( "UPDATE " . PREFIX . "_vote SET vote_num=vote_num+1 WHERE id='$rid'" );
		}
		
	}

	if( $_REQUEST['vote_mode'] == "fast_vote" ) { echo $lang['vote_ok']; die(); }

	$result = $db->super_query( "SELECT * FROM " . PREFIX . "_vote WHERE id='$rid'" );
	$title = stripslashes( $result['title'] );
	$body = stripslashes( $result['body'] );
	$body = explode( "<br />", $body );
	$max = $result['vote_num'];
	
	$db->query( "SELECT answer, count(*) as count FROM " . PREFIX . "_vote_result WHERE vote_id='$rid' GROUP BY answer" );
	$answer = array ();
	
	while ( $row = $db->get_row() ) {
		$answer[$row['answer']]['count'] = $row['count'];
	}
	
	$db->free();
	$pn = 0;
	$entry = "";
	
	for($i = 0; $i < sizeof( $body ); $i ++) {
		
		$num = $answer[$i]['count'];
		
		++ $pn;
		if( $pn > 5 ) $pn = 1;
		
		if( ! $num ) $num = 0;
		
		if( $max != 0 ) $proc = (100 * $num) / $max;
		else $proc = 0;
		
		$proc = round( $proc, 2 );
		
		$entry .= "<div class=\"vote\">$body[$i] - $num ($proc%)</div><div class=\"voteprogress\"><span class=\"vote{$pn}\" style=\"width:".intval($proc)."%;\">{$proc}%</span></div>\n";
	}
	$entry = "<div id=\"dle-vote\">$entry</div>";
	
	$tpl->load_template( 'vote.tpl' );
	
	$tpl->set( '{list}', $entry );
	$tpl->set( '{vote_id}', $rid );
	$tpl->set( '{title}', $title );
	$tpl->set( '{votes}', $max );
	$tpl->set( '[voteresult]', '' );
	$tpl->set( '[/voteresult]', '' );
	$tpl->set_block( "'\\[votelist\\].*?\\[/votelist\\]'si", "" );
	$tpl->compile( 'vote' );

	$db->close();

	$tpl->result['vote'] = str_replace( '{THEME}', $config['http_home_url'] . 'templates/' . $vote_skin, $tpl->result['vote'] );

	echo $tpl->result['vote'];
?>