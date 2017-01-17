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
 Файл: search.php
-----------------------------------------------------
 Назначение: поиск и замена текста в базе данных
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
  die("Hacking attempt!");
}

if($member_id['user_group'] != 1){ msg("error", $lang['addnews_denied'], $lang['db_denied']); }

if ($_POST['action'] == "replace") {

	if ($_REQUEST['user_hash'] == "" OR $_REQUEST['user_hash'] != $dle_login_hash) {

		  die("Hacking attempt! User not found");

	}

	if( function_exists( "get_magic_quotes_gpc" ) && get_magic_quotes_gpc() ) {

		$_POST['find'] = stripslashes( $_POST['find'] );
		$_POST['replace'] = stripslashes( $_POST['replace'] );

	} 

	$find = $db->safesql(addslashes(trim($_POST['find'])));
	$replace = $db->safesql(addslashes(trim($_POST['replace'])));

	if ($find == "" OR !count($_POST['table'])) msg("error",$lang['addnews_error'],$lang['vote_alert'], "javascript:history.go(-1)");

	if (in_array("news", $_POST['table'])) {
		$db->query("UPDATE `" . PREFIX . "_post` SET `short_story`=REPLACE(`short_story`,'$find','$replace')");
		$db->query("UPDATE `" . PREFIX . "_post` SET `full_story`=REPLACE(`full_story`,'$find','$replace')");
		$db->query("UPDATE `" . PREFIX . "_post` SET `xfields`=REPLACE(`xfields`,'$find','$replace')");

	}

	if (in_array("comments", $_POST['table'])) {
		$db->query("UPDATE `" . PREFIX . "_comments` SET `text`=REPLACE(`text`,'$find','$replace')");
	}

	if (in_array("pm", $_POST['table'])) {
		$db->query("UPDATE `" . USERPREFIX . "_pm` SET `text`=REPLACE(`text`,'$find','$replace')");
	}

	if (in_array("static", $_POST['table'])) {
		$db->query("UPDATE `" . PREFIX . "_static` SET `template`=REPLACE(`template`,'$find','$replace')");

	}

	if (in_array("tags", $_POST['table'])) {
		$db->query("UPDATE `" . PREFIX . "_tags` SET `tag`=REPLACE(`tag`,'$find','$replace')");
		$db->query("UPDATE `" . PREFIX . "_post` SET `tags`=REPLACE(`tags`,'$find','$replace')");
     }

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '58', '".htmlspecialchars("find: ".$find." replace: ".$replace, ENT_QUOTES, $config['charset'])."')" );

	clear_cache ();
	msg("info", $lang['find_done_h'], $lang['find_done'], "?mod=search");

}


echoheader("<i class=\"icon-exchange\"></i>".$lang['opt_sfind'], $lang['find_main']);

echo <<<HTML
<form action="" method="post" class="form-horizontal">
<input type="hidden" name="action" value="replace">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['find_main']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
	
{$lang['find_info']}
	  
	</div>
	<div class="row box-section">
	
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['find_ftable']}</label>
		  <div class="col-lg-10">
			<select name="table[]" style="height:90px;" multiple>
		<option value="news" selected>{$lang['find_rnews']}</option><option value="comments" selected>{$lang['find_rcomms']}</option><option value="pm" selected>{$lang['find_rpm']}</option><option value="static" selected>{$lang['find_rstatic']}</option><option value="tags" selected>{$lang['find_rtags']}</option>
		</select>
		   </div>
	    </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['find_ftext']}</label>
		  <div class="col-lg-10">
			<textarea name="find" style="width:100%;max-width:450px;height:150px;"></textarea>
		   </div>
	    </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['find_rtext']}</label>
		  <div class="col-lg-10">
			<textarea name="replace" style="width:100%;max-width:450px;height:150px;"></textarea>
		   </div>
	    </div>
		<div class="form-group">
		  <label class="control-label col-lg-2"></label>
		  <div class="col-lg-10">
			<input type="submit" class="btn btn-gray" value="{$lang['find_rstart']}">
		   </div>
	    </div>		
	</div>
	
   </div>
</div>

</form>
HTML;


echofooter();
?>