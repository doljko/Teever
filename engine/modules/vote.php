<?php
/*
=====================================================
 DataLife Engine - by SoftNews Media Group 
-----------------------------------------------------
 http://dle-news.ru/
-----------------------------------------------------
 Copyright (c) 2004-2016 
=====================================================
 Данный код защищен авторскими правами
=====================================================
 Файл: vote.php
-----------------------------------------------------
 Назначение: Вывод опроса на сайте
=====================================================
*/

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

$stop = false;
$is_voted = false;
$entry = "";

$flag = 0;
$data = array ();

if( isset( $_REQUEST['vote_action'] ) ) $vote_action = $_REQUEST['vote_action']; else $vote_action = "";
if( isset( $_REQUEST['vote_id'] ) ) $vote_id = intval( $_REQUEST['vote_id'] ); else $vote_id = 0;
if( isset( $_REQUEST['vote_check'] ) ) $vote_check = intval( $_REQUEST['vote_check'] ); else $vote_check = 0;

$vote_info = get_vars( "vote" );

if( ! is_array( $vote_info ) ) {
	$vote_info = array ();
	
	$db->query( "SELECT id, title, category, body, vote_num, start, end, grouplevel FROM " . PREFIX . "_vote WHERE approve" );
	
	while ( $row = $db->get_row() ) {
		$vote_info[$row['id']] = array ('id' => $row['id'], 'title' => $row['title'], 'category' => $row['category'], 'body' => $row['body'], 'vote_num' => $row['vote_num'], 'start' => $row['start'], 'end' => $row['end'], 'grouplevel' => $row['grouplevel'] );
	}
	set_vars( "vote", $vote_info );
	$db->free();
}

if( !$vote_id OR $vote_info[$vote_id]['id'] == "" ) {

	$find_vote = array ();
	$find_cats = array ();

	foreach ( $vote_info as $votes ) {
		
		if ($votes['start'] AND $_TIME < $votes['start'] ) continue;
		if ($votes['end'] AND $_TIME > $votes['end'] ) continue;

		$votes['grouplevel'] = explode( ',', $votes['grouplevel'] );
			
		if( $votes['grouplevel'][0] != "all" AND !in_array( $member_id['user_group'], $votes['grouplevel'] ) ) {
			continue;
		}
			

		$v_cats = explode( ',', $votes['category'] );
		
		if( $v_cats[0] == 'all' ) $find_vote[] = $votes;

		if( $category_id ) {
			$temp_cats = explode( ',', $category_id );
			foreach ($temp_cats as $temp_cat ) {
				if( in_array( $temp_cat, $v_cats ) ) $find_cats[] = $votes;
			}
		}
	
	}
	
	if( count( $find_cats ) ) $find_vote = $find_cats;
	$rand_keys = array_rand( $find_vote );
	$rid = $find_vote[$rand_keys]['id'];

} else
	$rid = $vote_id;

if( $vote_action == "vote" ) {
	/////////////////////////////////////////////////////////////////////////////
	//  Проверяем проголосовал ли текущий пользователь
	/////////////////////////////////////////////////////////////////////////////
	$_IP = get_ip();
	
	if( isset( $member_id['name'] ) ) $nick = $db->safesql($member_id['name']);
	else $nick = '';

	if ($user_group[$member_id['user_group']]['allow_vote']) {
	
		if( $is_logged ) $row = $db->super_query( "SELECT count(*) as count FROM " . PREFIX . "_vote_result WHERE vote_id='$rid' AND name='$nick'" );
		else $row = $db->super_query( "SELECT count(*) as count FROM " . PREFIX . "_vote_result WHERE vote_id='$rid' AND ip='$_IP'" );
		
		if( !$row['count'] AND count( explode( ".", $_IP ) ) == 4 ) $is_voted = false;
		else $is_voted = true;

	} else $is_voted = true;
	
	$flag = 1;
	
	/////////////////////////////////////////////////////////////////////////////
	//  Учёт голоса
	/////////////////////////////////////////////////////////////////////////////
	if( $is_voted == false ) {
		
		if( ! $is_logged ) $nick = "guest";
		
		$db->query( "INSERT INTO " . PREFIX . "_vote_result (ip, name, vote_id, answer) VALUES ('$_IP', '$nick', '$rid', '$vote_check')" );
		
		$db->query( "UPDATE " . PREFIX . "_vote SET vote_num=vote_num+1 WHERE id='$rid'" );

	}
}

if( $vote_action == "results" OR $flag ) {
	$db->query( "SELECT answer, count(*) as count FROM " . PREFIX . "_vote_result WHERE vote_id='$rid' GROUP BY answer" );
	
	$flag = 1;
	$pn = 0;
	$answer = array ();
	
	while ( $row = $db->get_row() ) {
		$answer[$row['answer']]['count'] = $row['count'];
	}
	
	$db->free();

}

if (!$user_group[$member_id['user_group']]['allow_vote']) $not_allow_vote = "if (event == 'vote') { DLEalert('{$lang['vote_not_allow']}', dle_info); return false;}"; else $not_allow_vote = "";

$ajax_script = <<<HTML
<script type="text/javascript">
<!--
function doVote( event ){

	{$not_allow_vote}
	var vote_check = $('#dle-vote input:radio[name=vote_check]:checked').val();

	ShowLoading('');

	$.get(dle_root + "engine/ajax/vote.php", { vote_id: "{$rid}", vote_action: event, vote_check: vote_check, vote_skin: dle_skin }, function(data){

		HideLoading('');

		$("#vote-layer").fadeOut(500, function() {
			$(this).html(data);
			$(this).fadeIn(500);
		});

	});
}
//-->
</script>
HTML;

switch ($flag) {
	case 0 :

		$title = stripslashes( $vote_info[$rid]['title'] );
		$body = stripslashes( $vote_info[$rid]['body'] );
		$body = explode( "<br />", $body );

		for($i = 0; $i < sizeof( $body ); $i ++) {

			if( $i == 0 ) $sel = "checked=\"checked\""; else $sel = "";

			$entry .= "<div class=\"vote\"><input id=\"vote_check{$i}\" name=\"vote_check\" type=\"radio\" $sel value=\"$i\" /><label for=\"vote_check{$i}\"> $body[$i]</label></div>";
		}
		
		$entry = "<div id=\"dle-vote\">$entry</div>";
		
		$tpl->load_template( 'vote.tpl' );
		
		$tpl->copy_template = $ajax_script . "<div id='vote-layer'>" . $tpl->copy_template . "</div>";
		
		$tpl->set( '{list}', $entry );
		$tpl->set( '{vote_id}', $rid );
		$tpl->set( '{title}', $title );
		$tpl->set( '[votelist]', '' );
		$tpl->set( '[/votelist]', '' );
		$tpl->set_block( "'\\[voteresult\\].*?\\[/voteresult\\]'si", "" );
		$tpl->compile( 'vote' );
		$tpl->clear();
		break;
	
	case 1 :

		$result = $db->super_query( "SELECT * FROM " . PREFIX . "_vote WHERE id='$rid'" );
		$title = stripslashes( $result['title'] );
		$body = stripslashes( $result['body'] );
		$body = explode( "<br />", $body );
		$max = $result['vote_num'];
		
		for($i = 0; $i < sizeof( $body ); $i ++) {
			
			++ $pn;
			if( $pn > 5 ) $pn = 1;
			
			$num = $answer[$i]['count'];
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
		$tpl->clear();
		break;

}

if( !$rid ) $tpl->result['vote'] = "";

?>