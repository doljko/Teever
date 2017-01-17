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
 Файл: blockip.php
-----------------------------------------------------
 Назначение: Блокировка посетителей по IP
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_blockip'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if( isset( $_REQUEST['ip_add'] ) ) $ip_add = $db->safesql( htmlspecialchars( strip_tags( trim( $_REQUEST['ip_add'] ) ), ENT_QUOTES, $config['charset'] ) ); else $ip_add = "";
if( isset( $_REQUEST['ip'] ) ) $ip = htmlspecialchars( strip_tags( trim( $_REQUEST['ip'] ) ), ENT_QUOTES, $config['charset'] ); else $ip = "";
if( isset( $_REQUEST['id'] ) ) $id = intval( $_REQUEST['id'] ); else $id = 0;

if( $action == "add" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	include_once ENGINE_DIR . '/classes/parse.class.php';
	
	$parse = new ParseFilter();
	$parse->safe_mode = true;
	$banned_descr = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['descr'] ), false ) );
	
	if( (trim( $_POST['date'] ) == "") OR (($_POST['date'] = strtotime( $_POST['date'] )) === - 1) OR !$_POST['date']) {
		$this_time = 0;
		$days = 0;
	} else {
		$this_time = $_POST['date'];
		$days = 1;
	}
	
	if( ! $ip_add ) {
		msg( "error", $lang['ip_error'], $lang['ip_error'], "?mod=blockip" );
	}

	$row = $db->super_query( "SELECT id FROM " . PREFIX . "_banned WHERE ip ='$ip_add'" );

	if ( $row['id'] ) {
		msg( "error", $lang['ip_error_1'], $lang['ip_error_1'], "?mod=blockip" );
	}
	
	$db->query( "INSERT INTO " . USERPREFIX . "_banned (descr, date, days, ip) values ('$banned_descr', '$this_time', '$days', '$ip_add')" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '9', '{$ip_add}')" );
	
	@unlink( ENGINE_DIR . '/cache/system/banned.php' );

} elseif( $action == "delete" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	if( ! $id ) {
		msg( "error", $lang['ip_error'], $lang['ip_error'], "?mod=blockip" );
	}
	
	$db->query( "DELETE FROM " . USERPREFIX . "_banned WHERE id = '$id'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '10', '')" );

	@unlink( ENGINE_DIR . '/cache/system/banned.php' );

}

echoheader( "<i class=\"icon-lock\"></i>".$lang['opt_ipban'], $lang['header_filter_1'] );

echo <<<HTML
<form action="" method="post" class="form-horizontal">
<input type="hidden" name="action" value="add">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['ip_add']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
	
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['ip_type']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:350px;" type="text" name="ip_add" value="{$ip}">
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['ban_date']}</label>
		  <div class="col-lg-10">
			<input data-rel="calendar" type="text" name="date" size="20">
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['ban_descr']}</label>
		  <div class="col-lg-10">
			<textarea style="width:100%;max-width:350px;"  rows="5" name="descr"></textarea>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2"></label>
		  <div class="col-lg-10">
			<input type="submit" value="{$lang['user_save']}" class="btn btn-green">
		  </div>
		 </div>
		 
	</div>
	<div class="row box-section">
	<span class="note large">{$lang['ip_example']}</span>
	</div>	
   </div>
</div>

</form>
HTML;

echo <<<HTML
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['ip_list']}</div>
  </div>
  <div class="box-content">
    <table class="table table-normal table-hover">
      <thead>
      <tr>
        <td style="width: 200px"></td>
        <td style="width: 190px">{$lang['ban_date']}</td>
        <td>{$lang['ban_descr']}</td>
        <td style="width: 180px">{$lang[vote_action]}</td>
      </tr>
      </thead>
	  <tbody>
HTML;

$db->query( "SELECT * FROM " . USERPREFIX . "_banned WHERE users_id = '0' ORDER BY id DESC" );

$i = 0;
while ( $row = $db->get_row() ) {
	$i ++;
	
	if( $row['date'] ) $endban = langdate( "j M Y H:i", $row['date'] );
	else $endban = $lang['banned_info'];
	
	echo "
        <tr>
        <td>
        {$row['ip']}
        </td>
        <td>
        {$endban}
        </td>
        <td>
        " . stripslashes( $row['descr'] ) . "
        </td>
        <td>
        <a class=\"btn btn-red\" href=\"?mod=blockip&action=delete&id={$row['id']}&user_hash={$dle_login_hash}\"><i class=\"icon-unlock\"></i> {$lang['ip_unblock']}</a></td>
        </tr>
        ";
}

if( $i == 0 ) {
	echo "<tr>
     <td height=\"18\" colspan=\"4\"><p align=\"center\"><br><b>{$lang['ip_empty']}<br><br></b></td>
    </tr>";
}

echo <<<HTML
	  </tbody>
	</table>
 
  </div>
</div>
HTML;

echofooter();
?>