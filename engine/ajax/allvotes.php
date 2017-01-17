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
 Файл: allvotes.php
-----------------------------------------------------
 Назначение: Вывод всех голосований на сайте
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
	
	$config['http_home_url'] = explode( "engine/ajax/allvotes.php", $_SERVER['PHP_SELF'] );
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

$_REQUEST['dle_skin'] = totranslit($_REQUEST['dle_skin'], false, false);

if( $_REQUEST['dle_skin'] ) {
	if( @is_dir( ROOT_DIR . '/templates/' . $_REQUEST['dle_skin'] ) ) {
		$config['skin'] = $_REQUEST['dle_skin'];
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
require_once ENGINE_DIR . '/modules/sitelogin.php';

if( !$is_logged ) $member_id['user_group'] = 5;

$vote_skin = $config['skin'];
$_TIME = time ();
$nick = $db->safesql($member_id['name']);

$sql_result = $db->query( "SELECT * FROM " . PREFIX . "_vote" );
$content = "";

while ( $row = $db->get_row( $sql_result ) ) {

	$title = stripslashes( $row['title'] );
	$body = stripslashes( $row['body'] );
	$body = explode( "<br />", $body );
	$max = $row['vote_num'];

	$db->query( "SELECT answer, count(*) as count FROM " . PREFIX . "_vote_result WHERE vote_id='{$row['id']}' GROUP BY answer" );
	$answer = array ();
	
	while ( $row1 = $db->get_row() ) {
		$answer[$row1['answer']]['count'] = $row1['count'];
	}

	$pn = 0;
	$entry = "";

	$allow_vote = true;
	$disable = $lang['vote_disable'];

	if ($row['start'] AND $_TIME < $row['start'] ) $allow_vote = false;
	if ($row['end'] AND $_TIME > $row['end'] ) $allow_vote = false;

	if ( !$row['approve'] ) $allow_vote = false;

	if ($user_group[$member_id['user_group']]['allow_vote']) {

		if( $is_logged ) $row2 = $db->super_query( "SELECT count(*) as count FROM " . PREFIX . "_vote_result WHERE vote_id='{$row['id']}' AND name='$nick'" );
		else $row2 = $db->super_query( "SELECT count(*) as count FROM " . PREFIX . "_vote_result WHERE vote_id='{$row['id']}' AND ip='$_IP'" );
		
		if( $row2['count'] ) { $disable = $lang['vote_disable_1']; $allow_vote = false; }

	} else { $disable = $lang['vote_not_allow']; $allow_vote = false; }

	for($i = 0; $i < sizeof( $body ); $i ++) {
		
		++ $pn;
		if( $pn > 5 ) $pn = 1;
		
		$num = $answer[$i]['count'];
		if( ! $num ) $num = 0;
		if( $max != 0 ) $proc = (100 * $num) / $max;
		else $proc = 0;
		$proc = round( $proc, 2 );

		if( $i == 0 ) $sel = "checked=\"checked\"";
		else $sel = "";
		if ( $allow_vote )
			$radio = "<input name=\"vote_check\" type=\"radio\" $sel value=\"$i\" />";
		else
			$radio = "&nbsp;";
		
		$entry .= "<tr><td width=\"20\" nowrap>{$radio}</td><td><div class=\"vote\">$body[$i] - $num ($proc%)</div><div class=\"voteprogress\"><span class=\"vote{$pn}\" style=\"width:".intval($proc)."%;\">{$proc}%</span></div></td></tr>";
	}

	$entry = "<table width=\"100%\">{$entry}</table>";

	if ( $allow_vote ) $button = "<br /><input type=\"submit\" onclick=\"fast_vote('{$row['id']}'); return false;\" class=\"dlevotebutton\" value=\"{$lang['vote_set']}\" />";
	else $button = "<font color=\"red\">{$disable}</font>";

	$content .= <<<HTML
<form method="post" name="vote_{$row['id']}" id="vote_{$row['id']}" action=''>
<fieldset>
  <legend>{$title}</legend>
  <div id="dle-vote_list-{$row['id']}">{$entry}{$button}<br /><br />{$lang['max_votes']} {$max}</div>
</fieldset>
</form>
HTML;

}


@header( "Content-type: text/html; charset=" . $config['charset'] );
echo "<div id=\"dlevotespopup\" title=\"{$lang['all_votes']}\" style=\"display:none\"><div id=\"dlevotespopupcontent\" style=\"overflow: auto;\">{$content}</div></div>";

?>