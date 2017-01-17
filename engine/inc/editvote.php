<?php
/*
=====================================================
DataLife Engine - by SoftNews Media Group 
-----------------------------------------------------
 http://dle-news.ru/
-----------------------------------------------------
 Copyright (c) 2004-2016 SoftNews Media Group
=====================================================
 Файл: editvote.php
-----------------------------------------------------
 Назначение: Управление опросами
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_editvote'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if( isset( $_REQUEST['id'] ) ) $id = intval( $_REQUEST['id'] ); else $id = "";

include_once ENGINE_DIR . '/classes/parse.class.php';

$parse = new ParseFilter();
$parse->filter_mode = false;

$stop = false;

if( $_GET['action'] == "delete" ) {

		if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$db->query( "DELETE FROM " . PREFIX . "_vote WHERE id='$id'" );
	$db->query( "DELETE FROM " . PREFIX . "_vote_result WHERE vote_id='$id'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '27', '{$id}')" );

	@unlink( ENGINE_DIR . '/cache/system/vote.php' );
	msg( "info", $lang['vote_str_2'], $lang['vote_str_2'], "?mod=editvote" );

}
if( $_GET['action'] == "clear" ) {

		if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$db->query( "UPDATE " . PREFIX . "_vote set vote_num='0' WHERE id='$id'" );
	$db->query( "DELETE FROM " . PREFIX . "_vote_result WHERE vote_id='$id'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '28', '{$id}')" );

	@unlink( ENGINE_DIR . '/cache/system/vote.php' );
	msg( "info", $lang['vote_clear3'], $lang['vote_clear3'], "?mod=editvote" );

}

if( $_GET['action'] == "off" ) {
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$db->query( "UPDATE " . PREFIX . "_vote set approve='0' WHERE id='$id'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '29', '{$id}')" );

	@unlink( ENGINE_DIR . '/cache/system/vote.php' );
}

if( $_GET['action'] == "on" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$db->query( "UPDATE " . PREFIX . "_vote set approve='1' WHERE id='$id'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '30', '{$id}')" );

	@unlink( ENGINE_DIR . '/cache/system/vote.php' );
}

if( $_GET['action'] == "doadd" ) {

	if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	if ( trim($_POST['start_date']) ) {

		$start_date = @strtotime( $_POST['start_date'] );

		if ($start_date === - 1 OR !$start_date) $start_date = "";

	} else $start_date = "";

	if ( trim($_POST['end_date']) ) {

		$end_date = @strtotime( $_POST['end_date'] );

		if ($end_date === - 1 OR !$end_date) $end_date = "";

	} else $end_date = "";
	
	$category = $_POST['category'];
	
	if( !count( $category ) ) {
		$category = array ();
		$category[] = 'all';
	}

	$category_list = array();

	foreach ( $category as $value ) {
		if ($value == "all") $category_list[] = $value; else $category_list[] = intval($value);
	}

	$category = $db->safesql( implode( ',', $category_list ) );

	$grouplevel = $_POST['grouplevel'];
	
	if( !count( $grouplevel ) ) {
		$grouplevel = array ();
		$grouplevel[] = 'all';
	}

	$g_list = array();

	foreach ( $grouplevel as $value ) {
		if ($value == "all") $g_list[] = $value; else $g_list[] = intval($value);
	}

	$grouplevel = $db->safesql( implode( ',', $g_list ) );
	
	$title = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['title'] ), false ) );
	$body = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['body'] ), false ) );
	
	$db->query( "INSERT INTO " . PREFIX . "_vote (category, vote_num, date, title, body, approve, start, end, grouplevel) VALUES ('$category', 0, CURRENT_DATE(), '$title', '$body', '1', '$start_date', '$end_date', '$grouplevel')" );
	@unlink( ENGINE_DIR . '/cache/system/vote.php' );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '2', '{$title}')" );

	msg( "info", $lang['vote_str_3'], $lang['vote_str_3'], "?mod=editvote" );

} elseif( $_GET['action'] == "update" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	if ( trim($_POST['start_date']) ) {

		$start_date = @strtotime( $_POST['start_date'] );

		if ($start_date === - 1 OR !$start_date) $start_date = "";

	} else $start_date = "";

	if ( trim($_POST['end_date']) ) {

		$end_date = @strtotime( $_POST['end_date'] );

		if ($end_date === - 1 OR !$end_date) $end_date = "";

	} else $end_date = "";
	
	$category = $_POST['category'];
	
	if( ! count( $category ) ) {
		$category = array ();
		$category[] = 'all';
	}

	$category_list = array();

	foreach ( $category as $value ) {
		if ($value == "all") $category_list[] = $value; else $category_list[] = intval($value);
	}

	$category = $db->safesql( implode( ',', $category_list ) );

	$grouplevel = $_POST['grouplevel'];
	
	if( !count( $grouplevel ) ) {
		$grouplevel = array ();
		$grouplevel[] = 'all';
	}

	$g_list = array();

	foreach ( $grouplevel as $value ) {
		if ($value == "all") $g_list[] = $value; else $g_list[] = intval($value);
	}

	$grouplevel = $db->safesql( implode( ',', $g_list ) );
	
	$title = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['title'] ), false ) );
	$body = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['body'] ), false ) );
	$id = intval( $_REQUEST['id'] );
	
	$db->query( "UPDATE " . PREFIX . "_vote SET category='$category', title='$title', body='$body', start='$start_date', end='$end_date', grouplevel='$grouplevel' WHERE id=$id" );
	@unlink( ENGINE_DIR . '/cache/system/vote.php' );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '3', '{$title}')" );

	msg( "info", $lang['vote_str_4'], $lang['vote_str_4'], "?mod=editvote" );

}

if( $_GET['action'] == "views" AND $_GET['id']) {

	$id = intval ($_GET['id']);

	$row = $db->super_query( "SELECT id, title, category, body, vote_num FROM " . PREFIX . "_vote WHERE id='$id'" );
		
	$title = stripslashes( $row['title'] );
	$body = stripslashes( $row['body'] );
	$body = explode( "<br />", $body );
	$max = $row['vote_num'];


	$db->query( "SELECT answer, count(*) as count FROM " . PREFIX . "_vote_result WHERE vote_id='$id' GROUP BY answer" );
	
	$pn = 0;
	$entry = "";
	$answer = array ();
	
	while ( $row = $db->get_row() ) {
		$answer[$row['answer']]['count'] = $row['count'];
	}
	
	$db->free();

	for($i = 0; $i < sizeof( $body ); $i ++) {
			
		++ $pn;
		if( $pn > 5 ) $pn = 1;
			
		$num = $answer[$i]['count'];
		if( ! $num ) $num = 0;
		if( $max != 0 ) $proc = (100 * $num) / $max;
		else $proc = 0;
		$proc = round( $proc, 2 );
			
		$entry .= "<div align=\"left\">$body[$i] - $num ($proc%)</div><div class=\"voteprogress\" align=\"left\"><span class=\"vote{$pn}\" style=\"width:".intval($proc)."%;\">{$proc}%</span></div>\n";

	}

	if ( !$title ) $entry = $lang['vote_notfound'];

	$entry = "<div style=\"width:500px;\">$entry</div>";

	echoheader( "<i class=\"icon-bar-chart\"></i>".$lang['header_votes'], $lang['editvote'] );

echo <<<HTML
<style type="text/css">
.voteprogress {
  overflow: hidden;
  height: 15px;
  margin-bottom: 5px;
  background-color: #f7f7f7;
  background-image: -moz-linear-gradient(top, #f5f5f5, #f9f9f9);
  background-image: -ms-linear-gradient(top, #f5f5f5, #f9f9f9);
  background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#f5f5f5), to(#f9f9f9));
  background-image: -webkit-linear-gradient(top, #f5f5f5, #f9f9f9);
  background-image: -o-linear-gradient(top, #f5f5f5, #f9f9f9);
  background-image: linear-gradient(top, #f5f5f5, #f9f9f9);
  background-repeat: repeat-x;
  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#f5f5f5', endColorstr='#f9f9f9', GradientType=0);
  -webkit-box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
  -moz-box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
  -webkit-border-radius: 4px;
  -moz-border-radius: 4px;
  border-radius: 4px;
}

.voteprogress span {
  color: #ffffff;
  text-align: center;
  text-indent: -2000em;
  height: 15px;
  display: block;
  overflow: hidden;
  text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
  background: #0e90d2;
  background-image: -moz-linear-gradient(top, #149bdf, #0480be);
  background-image: -ms-linear-gradient(top, #149bdf, #0480be);
  background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#149bdf), to(#0480be));
  background-image: -webkit-linear-gradient(top, #149bdf, #0480be);
  background-image: -o-linear-gradient(top, #149bdf, #0480be);
  background-image: linear-gradient(top, #149bdf, #0480be);
  background-repeat: repeat-x;
  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#149bdf', endColorstr='#0480be', GradientType=0);
}

.voteprogress .vote2 {
  background-color: #dd514c;
  background-image: -moz-linear-gradient(top, #ee5f5b, #c43c35);
  background-image: -ms-linear-gradient(top, #ee5f5b, #c43c35);
  background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#ee5f5b), to(#c43c35));
  background-image: -webkit-linear-gradient(top, #ee5f5b, #c43c35);
  background-image: -o-linear-gradient(top, #ee5f5b, #c43c35);
  background-image: linear-gradient(top, #ee5f5b, #c43c35);
  background-repeat: repeat-x;
  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ee5f5b', endColorstr='#c43c35', GradientType=0);
}

.voteprogress .vote3 {
  background-color: #5eb95e;
  background-image: -moz-linear-gradient(top, #62c462, #57a957);
  background-image: -ms-linear-gradient(top, #62c462, #57a957);
  background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#62c462), to(#57a957));
  background-image: -webkit-linear-gradient(top, #62c462, #57a957);
  background-image: -o-linear-gradient(top, #62c462, #57a957);
  background-image: linear-gradient(top, #62c462, #57a957);
  background-repeat: repeat-x;
  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#62c462', endColorstr='#57a957', GradientType=0);
}

.voteprogress .vote4 {
  background-color: #4bb1cf;
  background-image: -moz-linear-gradient(top, #5bc0de, #339bb9);
  background-image: -ms-linear-gradient(top, #5bc0de, #339bb9);
  background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#5bc0de), to(#339bb9));
  background-image: -webkit-linear-gradient(top, #5bc0de, #339bb9);
  background-image: -o-linear-gradient(top, #5bc0de, #339bb9);
  background-image: linear-gradient(top, #5bc0de, #339bb9);
  background-repeat: repeat-x;
  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#5bc0de', endColorstr='#339bb9', GradientType=0);
}

.voteprogress .vote5 {
  background-color: #faa732;
  background-image: -moz-linear-gradient(top, #fbb450, #f89406);
  background-image: -ms-linear-gradient(top, #fbb450, #f89406);
  background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#fbb450), to(#f89406));
  background-image: -webkit-linear-gradient(top, #fbb450, #f89406);
  background-image: -o-linear-gradient(top, #fbb450, #f89406);
  background-image: linear-gradient(top, #fbb450, #f89406);
  background-repeat: repeat-x;
  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fbb450', endColorstr='#f89406', GradientType=0);
}
</style>
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['vote_result']}&nbsp;{$title}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
	
		<div id="main_box" align="center"><br />{$entry}<br /><br />{$lang['vote_count']}&nbsp;{$max}<br /><br /> 
		<input id = "next_button" onclick="history.go(-1); return false;" class="btn btn-blue" style="width:150px;" type="button" value="{$lang['func_msg']}">
		</div>
	  
	</div>
	
   </div>
</div>
HTML;

	echofooter();

} elseif( $_GET['action'] == "edit" OR $_GET['action'] == "add" ) {

	echoheader( "<i class=\"icon-bar-chart\"></i>".$lang['header_votes'], $lang['editvote'] );
	$canedit = false;
	$start_date = "";
	$stop_date  = "";
	

	if( ($_GET['action'] == "edit") && $id != '' ) {
		$canedit = true;
		$row = $db->super_query( "SELECT * FROM " . PREFIX . "_vote WHERE id='$id' LIMIT 0,1" );
		
		$title = $parse->decodeBBCodes( $row['title'], false );
		$body = $parse->decodeBBCodes( $row['body'], false );
		$icategory = explode( ',', $row['category'] );
		if( $row['category'] == "all" ) $all_cats = "selected";
		else $all_cats = "";

		if ( $row['start'] ) $start_date = @date( "Y-m-d H:i", $row['start'] );
		if ( $row['end'] )  $end_date  = @date( "Y-m-d H:i", $row['end'] );
		$groups = get_groups( explode( ',', $row['grouplevel'] ) );

		if( $row['grouplevel'] == "all" ) $check_all = "selected";
		else $check_all = "";
	
	} else {
		$canedit = false;
		$groups = get_groups();
		$check_all = "selected";
		$icategory = 0;
		$title = "";
		$body = "";
	}
	
	$opt_category = CategoryNewsSelection( $icategory, 0, FALSE );
	
	if( $canedit == false ) {
		echo "<form class=\"form-horizontal\" method=\"post\" action=\"?mod=editvote&action=doadd\" name=\"addvote\" onsubmit=\"if(document.addvote.title.value == '' || document.addvote.body.value == ''){DLEalert('{$lang['vote_alert']}', '{$lang['p_info']}');return false}\">";
		$button = "<input type=\"submit\" class=\"btn btn-green\" value=\"{$lang['vote_new']}\">";
	} else {
		echo "<form class=\"form-horizontal\" method=\"post\" action=\"?mod=editvote&action=update&id={$id}\" name=\"addvote\" onsubmit=\"if(document.addvote.title.value == '' || document.addvote.body.value == ''){DLEalert('{$lang['vote_alert']}', '{$lang['p_info']}');return false}\">";
		$button = "<input type=\"submit\" class=\"btn btn-green\" value=\"{$lang['vote_edit']}\">";
	
	}
	$user_group[$member_id['user_group']]['allow_image_upload'] =false;
	$user_group[$member_id['user_group']]['allow_file_upload'] =false;
	include (ENGINE_DIR . '/inc/include/inserttag.php');
	
	echo <<<HTML
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['opt_votec']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
	
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['vote_title']}</label>
		  <div class="col-lg-10">
			<input type="text" name="title" style="width:100%;max-width:437px;" value="{$title}">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_vtitle']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['addnews_cat']}</label>
		  <div class="col-lg-10">
			<select data-placeholder="{$lang['addnews_cat_sel']}" style=width:437px;" name="category[]" class="cat_select" multiple>
				<option value="all" {$all_cats}>{$lang['edit_all']}</option>
				{$opt_category}
			</select>&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_vcat']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['vote_startdate']}</label>
		  <div class="col-lg-10">
			<input data-rel="calendar" type="text" name="start_date" size="20" value="{$start_date}" />&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_vstart']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['vote_enddate']}</label>
		  <div class="col-lg-10">
			<input data-rel="calendar" type="text" name="end_date" size="20" value="{$end_date}" />&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_vend']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['vote_body']}<br /><span class="note large">{$lang['vote_str_1']}</span></label>
		  <div class="col-lg-10">
			{$bb_code}<textarea style="width:100%;max-width:950px;height:300px;" name="body" id="body" onfocus="setFieldName(this.name)">{$body}</textarea><script type=text/javascript>var selField  = "body";</script>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['stat_allow']}</label>
		  <div class="col-lg-10">
			<select name="grouplevel[]" style="width:250px;height:100px;" multiple><option value="all" {$check_all}>{$lang['edit_all']}</option>{$groups}</select>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2"></label>
		  <div class="col-lg-10">
			{$button}
		  </div>
		 </div>		 
	</div>
	
   </div>
</div>
<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
</form>
<script type="text/javascript">
	$(function(){
		  $(".cat_select").chosen({allow_single_deselect:true, no_results_text: '{$lang['addnews_cat_fault']}'});
	});
</script>
HTML;
	
	echofooter();

} else {

echoheader( "<i class=\"icon-bar-chart\"></i>".$lang['header_votes'], $lang['editvote'] );


echo "
 <script language=\"javascript\">
 <!-- begin
    function confirmdelete(id){
	    DLEconfirm( '{$lang['vote_confirm']}', '{$lang['p_confirm']}', function () {
			document.location=\"?mod=editvote&action=delete&user_hash={$dle_login_hash}&id=\"+id;
		} );
    }
    function confirmclear(id){
	    DLEconfirm( '{$lang['vote_clear']}', '{$lang['p_confirm']}', function () {
			document.location=\"?mod=editvote&action=clear&user_hash={$dle_login_hash}&id=\"+id;
		} );
    }
 // end -->
 </script>";

$db->query( "SELECT * FROM " . PREFIX . "_vote ORDER BY id DESC" );

$entries = "";

while ( $row = $db->get_row() ) {
	
	$item_id = $row['id'];
	$item_date = date( "d.m.Y", strtotime( $row['date'] ) );
	$title = htmlspecialchars( stripslashes( $row['title'] ), ENT_QUOTES, $config['charset'] );

	if ( $row['start'] ) $start_date = date( "d.m.Y H:i", $row['start'] ); else $start_date = "--";
	if ( $row['end'] ) $end_date = date( "d.m.Y H:i", $row['end'] ); else $end_date = "--";
	
	if( dle_strlen( $title, $config['charset'] ) > 74 ) {
		$title = dle_substr( $title, 0, 70, $config['charset'] ) . " ...";
	}
	
	$item_num = $row['vote_num'];
	if( empty( $row['category'] ) ) {
		$my_cat = "<center>--</center>";
	} elseif( $row['category'] == "all" ) {
		$my_cat = $lang['edit_all'];
	} else {
			$my_cat = array ();
			$cat_list = explode( ',', $row['category'] );
			
			foreach ( $cat_list as $element ) {
				if( $element ) $my_cat[] = $cat[$element];
			}
			$my_cat = implode( ',<br />', $my_cat );
	}
	
	if( $row['approve'] ) {
		$status = "<span title=\"{$lang['led_on_title']}\" class=\"status-success tip\"><b><i class=\"icon-ok-sign\"></i></b></span>";
		$led_action = "off";
		$lang['led_title'] = $lang['vote_aus'];		
	} else {
		$status = "<span title=\"{$lang['led_off_title']}\" class=\"status-error tip\"><b><i class=\"icon-exclamation-sign\"></i></b></span>";
		$lang['led_title'] = $lang['vote_ein'];
		$led_action = "on";
	}

		$menu_link = <<<HTML
        <div class="btn-group">
          <button class="btn btn-default dropdown-toggle" data-toggle="dropdown"><i class="icon-pencil"></i> {$lang['filter_action']} <span class="caret"></span></button>
          <ul class="dropdown-menu text-left pull-right">
            <li><a href="?mod=editvote&action=views&id={$item_id}"><i class="icon-eye-open"></i> {$lang['vote_view']}</a></li>
            <li><a href="?mod=editvote&action={$led_action}&user_hash={$dle_login_hash}&id={$item_id}"><i class="icon-magic"></i> {$lang['led_title']}</a></li>
			<li><a onclick="javascript:confirmclear('{$item_id}'); return(false);" href="#"><i class="icon-retweet"></i> {$lang['vote_clear2']}</a></li>
			<li class="divider"></li>
            <li><a onclick="javascript:confirmdelete('{$item_id}'); return(false);" href="#"><i class="icon-trash"></i> {$lang['cat_del']}</a></li>
          </ul>
        </div>
HTML;
	
	$entries .= "
   <tr>
    <td>{$item_date}&nbsp;-&nbsp;<a class=\"tip\" title='{$lang['word_ledit']}' href=\"?mod=editvote&action=edit&id={$item_id}\">{$title}</td>
    <td align=\"center\">{$start_date}</td>
    <td align=\"center\">{$end_date}</td>
    <td align=\"center\">{$status}</td>
    <td align=\"center\">{$row['vote_num']}</td>
    <td align=\"center\">{$my_cat}</td>
    <td>{$menu_link}</td>
     </tr>";
}
$db->free();

if( empty( $entries ) ) {
	$entries = "<tr><td colspan=\"7\" align=\"center\" height=\"40\">" . $lang['vote_nodata'] . "</td></tr>";
}

echo <<<HTML
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['opt_votec']}</div>
  </div>
  <div class="box-content">

    <table class="table table-normal table-hover">
      <thead>
      <tr>
        <td>{$lang['edit_title']}</td>
        <td>{$lang['vote_startinfo']}</td>
        <td>{$lang['vote_endinfo']}</td>
        <td>{$lang['led_status']}</td>
        <td>{$lang['vote_count']}</td>
		<td>{$lang['edit_cl']}</td>
        <td style="width: 200px">{$lang['vote_action']}</td>
      </tr>
      </thead>
	  <tbody>
		{$entries}
	  </tbody>
	</table>
	
   </div>
	<div class="box-footer padded">
		<input onclick="document.location='?mod=editvote&action=add'" type="button" class="btn btn-blue" value="{$lang['poll_new']}">
	</div>	
</div>
HTML;

echofooter();

}
?>