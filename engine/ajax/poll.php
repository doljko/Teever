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
 Назначение: AJAX для опросов в новостях
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
	
	$config['http_home_url'] = explode( "engine/ajax/poll.php", $_SERVER['PHP_SELF'] );
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

require_once ENGINE_DIR . '/modules/sitelogin.php';

if( ! $is_logged ) {
	$member_id['user_group'] = 5;
}

function votes($all, $ansid) {
	
	$data = array ();
	$alldata = array ();
	
	if( $all != "" ) {
		$all = explode( "|", $all );
		
		foreach ( $all as $vote ) {
			list ( $answerid, $answervalue ) = explode( ":", $vote );
			$data[$answerid] = intval( $answervalue );
		}
	}
	
	foreach ( $ansid as $id ) {
		$data[$id] ++;
	}
	
	foreach ( $data as $key => $value ) {
		$alldata[] = intval( $key ) . ":" . intval( $value );
	}
	
	$alldata = implode( "|", $alldata );
	
	return $alldata;
}


$news_id = intval( $_REQUEST['news_id'] );
$answers = explode( " ", trim( $_REQUEST['answer'] ) );

$buffer = "";

$_REQUEST['vote_skin'] = trim(totranslit($_REQUEST['vote_skin'], false, false));

if( $_REQUEST['vote_skin'] == "" OR !@is_dir( ROOT_DIR . '/templates/' . $_REQUEST['vote_skin'] ) ) {
	die( "Hacking attempt!" );
}

if( $config["lang_" . $_REQUEST['vote_skin']] ) {

	if ( file_exists( ROOT_DIR . '/language/' . $config["lang_" . $_REQUEST['vote_skin']] . '/website.lng' ) ) {
		
		@include_once (ROOT_DIR . '/language/' . $config["lang_" . $_REQUEST['vote_skin']] . '/website.lng');
		
	} else die("Language file not found");

} else {
	
	@include_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';

}

$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];

if( $is_logged ) $log_id = intval( $member_id['user_id'] );
else $log_id = $_IP;

$poll = $db->super_query( "SELECT * FROM " . PREFIX . "_poll WHERE news_id = '{$news_id}'" );
$log = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_poll_log WHERE news_id = '{$news_id}' AND member ='{$log_id}'" );

if( $log['count'] and $_REQUEST['action'] != "list" ) $_REQUEST['action'] = "results";

if($_REQUEST['action'] != "list" AND !$user_group[$member_id['user_group']]['allow_poll']) $_REQUEST['action'] = "results";

$votes = "";

if( $_REQUEST['action'] == "vote" ) {
	
	$votes = votes( $poll['answer'], $answers );
	$db->query( "UPDATE  " . PREFIX . "_poll set answer='$votes', votes=votes+" . count( $answers ) . " WHERE news_id = '{$news_id}'" );
	$db->query( "INSERT INTO " . PREFIX . "_poll_log (news_id, member) VALUES('{$news_id}', '$log_id')" );
	
	$_REQUEST['action'] = "results";
}

if( $_REQUEST['action'] == "results" ) {
	
	if( $votes == "" ) {
		$votes = $poll['answer'];
		$allcount = $poll['votes'];
	} else {
		$allcount = count( $answers ) + $poll['votes'];
	}
	
	$answer = get_votes( $votes );
	$body = explode( "<br />", stripslashes( $poll['body'] ) );
	$pn = 0;
	
	for($i = 0; $i < sizeof( $body ); $i ++) {
		
		$num = $answer[$i];
		
		if( ! $num ) $num = 0;
		
		++ $pn;
		if( $pn > 5 ) $pn = 1;
		
		if( $allcount != 0 ) $proc = (100 * $num) / $allcount;
		else $proc = 0;

		$intproc =intval($proc);		
		$proc = round( $proc, 2 );

		$buffer .= <<<HTML
{$body[$i]} - {$num} ({$proc}%)<br />
<div class="pollprogress"><span class="poll{$pn}" style="width:{$intproc}%;">{$proc}%</span></div>
HTML;
	
	}
	
	$buffer .= <<<HTML
	<div class="pollallvotes">{$lang['poll_count']} {$allcount}</div>
HTML;

} elseif( $_REQUEST['action'] == "list" ) {
	
	$body = explode( "<br />", stripslashes( $poll['body'] ) );
	
	if( ! $poll['multiple'] ) {
		
		for($v = 0; $v < sizeof( $body ); $v ++) {
			if( ! $v ) $sel = "checked";
			else $sel = "";
			
			$buffer .= <<<HTML
<div class="pollanswer"><input id="vote{$news_id}{$v}" name="dle_poll_votes" type="radio" $sel value="{$v}" /><label for="vote{$news_id}{$v}">  {$body[$v]}</label></div>
HTML;
		
		}
	} else {
		
		for($v = 0; $v < sizeof( $body ); $v ++) {
			
			$buffer .= <<<HTML
<div class="pollanswer"><input id="vote{$news_id}{$v}" name="dle_poll_votes[]" type="checkbox" value="{$v}" /><label for="vote{$news_id}{$v}">  {$body[$v]}</label></div>
HTML;
		
		}
	
	}

} else
	die( "error" );

@header( "Content-type: text/html; charset=" . $config['charset'] );
echo $buffer;
?>