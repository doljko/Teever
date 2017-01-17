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
 Файл: poll.php
-----------------------------------------------------
 Назначение: Опрос в новостях
=====================================================
*/
if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

$_IP = get_ip();

if( $is_logged ) $log_id = intval( $member_id['user_id'] );
else $log_id = $_IP;

$poll = $db->super_query( "SELECT * FROM " . PREFIX . "_poll WHERE news_id = '{$row['id']}'" );

if($config['allow_cache'] AND $dle_module != "showfull") {

	$log = array('count' => 0 );

} else $log = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_poll_log WHERE news_id = '{$row['id']}' AND member ='{$log_id}'" );

$poll['title'] = stripslashes( $poll['title'] );
$poll['frage'] = stripslashes( $poll['frage'] );
$body = explode( "<br />", stripslashes( $poll['body'] ) );

$tplpoll = new dle_template();
$tplpoll->dir = TEMPLATE_DIR;

$tplpoll->load_template( 'poll.tpl' );

$tplpoll->set( '{title}', $poll['title'] );
$tplpoll->set( '{question}', $poll['frage'] );
$tplpoll->set( '{votes}', $poll['votes'] );
$tplpoll->set( '{news-id}', $row['id'] );

if( $log['count'] ) {
	
	$tplpoll->set_block( "'\\[not-voted\\](.+?)\\[/not-voted\\]'si", "" );
	$tplpoll->set( '[voted]', '' );
	$tplpoll->set( '[/voted]', '' );

} else {
	
	$tplpoll->set_block( "'\\[voted\\](.+?)\\[/voted\\]'si", "" );
	$tplpoll->set( '[not-voted]', '' );
	$tplpoll->set( '[/not-voted]', '' );
}

$list = "<div id=\"dle-poll-list-{$row['id']}\">";

if( ! $log['count'] and $user_group[$member_id['user_group']]['allow_poll'] ) {
	if( ! $poll['multiple'] ) {
		
		for($v = 0; $v < sizeof( $body ); $v ++) {
			if( ! $v ) $sel = "checked=\"checked\"";
			else $sel = "";
			
			$list .= <<<HTML
<div class="pollanswer"><input id="vote{$row['id']}{$v}" name="dle_poll_votes" type="radio" $sel value="{$v}" /><label for="vote{$row['id']}{$v}"> {$body[$v]}</label></div>
HTML;
		
		}
	} else {
		
		for($v = 0; $v < sizeof( $body ); $v ++) {
			
			$list .= <<<HTML
<div class="pollanswer"><input id="vote{$row['id']}{$v}" name="dle_poll_votes[]" type="checkbox" value="{$v}" /><label for="vote{$row['id']}{$v}">  {$body[$v]}</label></div>
HTML;
		
		}
	
	}
	
	$allcount = 0;

} else {
	
	$answer = get_votes( $poll['answer'] );
	$allcount = $poll['votes'];
	$pn = 0;
	
	for($v = 0; $v < sizeof( $body ); $v ++) {
		
		$num = $answer[$v];
		++ $pn;
		if( $pn > 5 ) $pn = 1;
		
		if( ! $num ) $num = 0;
		
		if( $allcount != 0 ) $proc = (100 * $num) / $allcount;
		else $proc = 0;

		$intproc =intval($proc);		
		$proc = round( $proc, 2 );
		
		$list .= <<<HTML
{$body[$v]} - {$num} ({$proc}%)<br />
<div class="pollprogress"><span class="poll{$pn}" style="width:{$intproc}%;">{$proc}%</span></div>
HTML;
	
	}
	
	$allcount = 1;

}

$list .= "</div>";

$tplpoll->set( '{list}', $list );

if($config['allow_cache']) $allcount = 0;

$ajax_script = <<<HTML
<script type="text/javascript">
<!--
dle_poll_voted[{$row['id']}] = {$allcount};
//-->
</script>
HTML;

$tplpoll->copy_template = $ajax_script . "<form method=\"post\" name=\"dlepollform_{$row['id']}\" id=\"dlepollform_{$row['id']}\" action=\"\">" . $tplpoll->copy_template . "<input type=\"hidden\" name=\"news_id\" id=\"news_id\" value=\"" . $row['id'] . "\" /><input type=\"hidden\" name=\"status\" id=\"status\" value=\"0\" /></form>";

$tplpoll->compile( 'poll' );
$tplpoll->clear();

$tpl->result['poll'] = $tplpoll->result['poll'];
unset($tplpoll);

?>