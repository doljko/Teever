<?php
/*
=====================================================
DataLife Engine - by SoftNews Media Group
-----------------------------------------------------
http://dle-news.ru/
-----------------------------------------------------
Copyright (c) 2004-2016 SoftNews Media Group
=====================================================
Файл: banners.php
-----------------------------------------------------
Назначение: управление баннерами
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( !$user_group[$member_id['user_group']]['admin_banners'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if( isset( $_REQUEST['id'] ) ) $id = intval( $_REQUEST['id'] );
else $id = "";

function makeDropDown($options, $name, $selected) {
	$output = "<select class=\"uniform\" name=\"{$name}\">\r\n";
	foreach ( $options as $value => $description ) {
		$output .= "<option value=\"$value\"";
		if( $selected == $value ) {
			$output .= " selected ";
		}
		$output .= ">{$description}</option>\n";
	}
	$output .= "</select>";
	return $output;
}

if( $_POST['action'] == "doadd" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	if( function_exists( "get_magic_quotes_gpc" ) && get_magic_quotes_gpc() ){
		$_POST['banner_descr'] = stripslashes( $_POST['banner_descr'] );
		$_POST['banner_code'] = stripslashes( $_POST['banner_code'] );
	}
	
	$banner_tag = totranslit( strip_tags( trim( $_POST['banner_tag'] ) ) );
	$banner_descr = $db->safesql( strip_tags( trim( $_POST['banner_descr'] ) ) );
	$banner_code = $db->safesql( trim( $_POST['banner_code'] ) );
	$approve = intval( $_REQUEST['approve'] );
	$short_place = intval( $_REQUEST['short_place'] );
	$bstick = intval( $_REQUEST['bstick'] );
	$main = intval( $_REQUEST['main'] );
	$fpage = intval( $_REQUEST['fpage'] );
	$innews = intval( $_REQUEST['innews'] );

	$category = $_POST['category'];

	if( !count( $category ) ) {
		$category = array ();
		$category[] = '0';
	}

	$category_list = array();

	foreach ( $category as $value ) {
		$category_list[] = intval($value);
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

	if ( trim($_POST['start_date']) ) {

		$start_date = @strtotime( $_POST['start_date'] );

		if ($start_date === - 1 OR !$start_date) $start_date = "";

	} else $start_date = "";

	if ( trim($_POST['end_date']) ) {

		$end_date = @strtotime( $_POST['end_date'] );

		if ($end_date === - 1 OR !$end_date) $end_date = "";

	} else $end_date = "";
	
	if( $banner_tag == "" or $banner_descr == "" ) msg( "error", $lang['addnews_error'], $lang['addnews_erstory'], "javascript:history.go(-1)" );
	
	$db->query( "INSERT INTO " . PREFIX . "_banners (banner_tag, descr, code, approve, short_place, bstick, main, category, grouplevel, start, end, fpage, innews) values ('$banner_tag', '$banner_descr', '$banner_code', '$approve', '$short_place', '$bstick', '$main', '$category', '$grouplevel', '$start_date', '$end_date', '$fpage', '$innews')" );
	@unlink( ENGINE_DIR . '/cache/system/banners.php' );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '4', '{$banner_tag}')" );

	clear_cache();
	header( "Location: " . $_SERVER['PHP_SELF'] . "?mod=banners" );

}

if( $_POST['action'] == "doedit" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	if (!$id) msg( "error", "ID not valid", "ID not valid" );

	if( function_exists( "get_magic_quotes_gpc" ) && get_magic_quotes_gpc() ){
		$_POST['banner_descr'] = stripslashes( $_POST['banner_descr'] );
		$_POST['banner_code'] = stripslashes( $_POST['banner_code'] );
	}
	
	$banner_tag = totranslit( strip_tags( trim( $_POST['banner_tag'] ) ) );
	$banner_descr = $db->safesql( strip_tags( trim( $_POST['banner_descr'] ) ) );
	$banner_code = $db->safesql( trim( $_POST['banner_code'] ) );
	$approve = intval( $_REQUEST['approve'] );
	$short_place = intval( $_REQUEST['short_place'] );
	$bstick = intval( $_REQUEST['bstick'] );
	$main = intval( $_REQUEST['main'] );
	$fpage = intval( $_REQUEST['fpage'] );
	$innews = intval( $_REQUEST['innews'] );

	$category = $_POST['category'];

	if( !count( $category ) ) {
		$category = array ();
		$category[] = '0';
	}

	$category_list = array();

	foreach ( $category as $value ) {
		$category_list[] = intval($value);
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

	if ( trim($_POST['start_date']) ) {

		$start_date = @strtotime( $_POST['start_date'] );

		if ($start_date === - 1 OR !$start_date) $start_date = "";

	} else $start_date = "";

	if ( trim($_POST['end_date']) ) {

		$end_date = @strtotime( $_POST['end_date'] );

		if ($end_date === - 1 OR !$end_date) $end_date = "";

	} else $end_date = "";
	
	if( $banner_tag == "" or $banner_descr == "" ) msg( "error", $lang['addnews_error'], $lang['addnews_erstory'], "javascript:history.go(-1)" );
	
	$db->query( "UPDATE " . PREFIX . "_banners SET banner_tag='$banner_tag', descr='$banner_descr', code='$banner_code', approve='$approve', short_place='$short_place', bstick='$bstick', main='$main', category='$category', grouplevel='$grouplevel', start='$start_date', end='$end_date', fpage='$fpage', innews='$innews' WHERE id='$id'" );
	@unlink( ENGINE_DIR . '/cache/system/banners.php' );
	clear_cache();
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '5', '{$banner_tag}')" );

	header( "Location: " . $_SERVER['PHP_SELF'] . "?mod=banners" );
}

if( $_GET['action'] == "off" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	if (!$id) msg( "error", "ID not valid", "ID not valid" );
	
	$db->query( "UPDATE " . PREFIX . "_banners set approve='0' WHERE id='$id'" );
	@unlink( ENGINE_DIR . '/cache/system/banners.php' );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '6', '{$id}')" );

	clear_cache();
}
if( $_GET['action'] == "on" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	if (!$id) msg( "error", "ID not valid", "ID not valid" );
	
	$db->query( "UPDATE " . PREFIX . "_banners set approve='1' WHERE id='$id'" );
	@unlink( ENGINE_DIR . '/cache/system/banners.php' );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '7', '{$id}')" );

	clear_cache();
}

if( $_GET['action'] == "delete" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	if (!$id) msg( "error", "ID not valid", "ID not valid" );
	
	$db->query( "DELETE FROM " . PREFIX . "_banners WHERE id='$id'" );
	@unlink( ENGINE_DIR . '/cache/system/banners.php' );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '8', '{$id}')" );

	clear_cache();
}

if( $_GET['action'] == "view" ) {
	
	if (!$id) msg( "error", "ID not valid", "ID not valid" );

	$row = $db->super_query( "SELECT * FROM " . PREFIX . "_banners WHERE id='$id'" );

	if (!$row['id']) msg( "error", "ID not valid", "ID not valid" );

echo <<<HTML
<html><title>DataLife Engine - {$lang['skin_title']}</title>
<meta content="text/html; charset={$config['charset']}" http-equiv=Content-Type>
<style type="text/css">
html,body{
	height:100%;
	margin:0px;
	padding: 0px;
	font-size: 11px;
	font-family: verdana;
}
p {
	margin:0px;
	padding: 0px;
}
table{
	border:0px;
	border-collapse:collapse;
}

table td{
	padding:0px;
	font-size: 11px;
	font-family: verdana;
}

a:active,
a:visited,
a:link {
	color: #4b719e;
	text-decoration:none;
}

a:hover {
	color: #4b719e;
	text-decoration: underline;
}
</style>
<body>
HTML;

echo "<fieldset style=\"border-style:solid; border-width:1; border-color:black;\">{$row['code']}</fieldset></body></html>";

	die();
}

if( $_REQUEST['action'] == "add" or $_REQUEST['action'] == "edit" ) {
	
	$start_date = "";
	$stop_date  = "";

	if( $_REQUEST['action'] == "add" ) {
		$checked = "checked";
		$doaction = "doadd";
		$all_cats = "selected";
		$check_all = "selected";
		$groups = get_groups();
		$checked2 = "";
		$checked3 = "";
		$checked4 = "";
		$checked5 = "";
		$checked6 = "";
	
	} else {
		
		$row = $db->super_query( "SELECT * FROM " . PREFIX . "_banners WHERE id='$id' LIMIT 0,1" );
		$banner_tag = $row['banner_tag'];
		$banner_descr = htmlspecialchars( $row['descr'], ENT_QUOTES, $config['charset'] );
		$banner_code = htmlspecialchars(  $row['code'], ENT_QUOTES, $config['charset'] );
		$short_place = $row['short_place'];
		$checked = ($row['approve']) ? "checked" : "";
		$checked2 = ($row['allow_full']) ? "checked" : "";
		$checked3 = ($row['bstick']) ? "checked" : "";
		$checked4 = ($row['main']) ? "checked" : "";
		$checked5 = ($row['fpage']) ? "checked" : "";
		$checked6 = ($row['innews']) ? "checked" : "";
		$doaction = "doedit";
		
		$groups = get_groups( explode( ',', $row['grouplevel'] ) );
		if( $row['grouplevel'] == "all" ) $check_all = "selected";
		else $check_all = "";

		if ( $row['start'] ) $start_date = @date( "Y-m-d H:i", $row['start'] );
		if ( $row['end'] )  $end_date  = @date( "Y-m-d H:i", $row['end'] );
	
	}
	
	$opt_category = CategoryNewsSelection( explode( ',', $row['category'] ), 0, FALSE );
	if( ! $row['category'] ) $all_cats = "selected";
	else $all_cats = "";
	
	echoheader( "<i class=\"icon-shopping-cart\"></i>".$lang['header_banner'], $lang['header_banner_1'] );
	
	echo <<<HTML
<link rel="stylesheet" type="text/css" href="engine/skins/codemirror/css/default.css">
<style type="text/css">
.CodeMirror {
  height: 300px !important;
}
</style>
<script type="text/javascript" src="engine/skins/codemirror/js/code.js"></script>
<form action="" method="post" name="bannersform" class="form-horizontal">
<input type="hidden" name="mod" value="banners">
<input type="hidden" name="action" value="{$doaction}">
<input type="hidden" name="user_hash" value="$dle_login_hash" />
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['banners_title']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">

		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['banners_xname']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:350px;" type="text" name="banner_tag" value="{$banner_tag}" />&nbsp;&nbsp;&nbsp;({$lang['xf_lat']})
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['banners_xdescr']}</label>
		  <div class="col-lg-10">
			<input  style="width:100%;max-width:350px;" type="text" name="banner_descr" value="{$banner_descr}" />
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['addnews_cat']}</label>
		  <div class="col-lg-10">
			<select data-placeholder="{$lang['addnews_cat_sel']}" style=width:350px;" name="category[]" class="cat_select" multiple><option value="0" {$all_cats}>{$lang['edit_all']}</option>{$opt_category}</select>
		  </div>
		 </div>		 
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['vote_startdate']}</label>
		  <div class="col-lg-10">
			<input data-rel="calendardatetime" type="text" name="start_date" size="20" value="{$start_date}" />&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_bstart']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['vote_enddate']}</label>
		  <div class="col-lg-10">
			<input data-rel="calendardatetime" type="text" name="end_date" size="20" value="{$end_date}" />&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_bend']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['banners_code']}</label>
		  <div class="col-lg-10">
			<div style="border: solid 1px #BBB;width:99%;">
				<textarea style="width:100%;" name="banner_code" id="banner_code" rows="16">{$banner_code}</textarea>
			</div>
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
			<input class="icheck" type="checkbox" name="approve" value="1" {$checked} id="editbact"/><label for="editbact">{$lang['banners_approve']}</label><br />
			<input class="icheck" type="checkbox" value="1" name="main" {$checked4} id="main" /><label for="main">{$lang['banners_main']}</label><br />
			<input class="icheck" type="checkbox" value="1" name="fpage" {$checked5} id="fpage" /><label for="fpage">{$lang['banners_fpage']}</label><br />
			<input class="icheck" type="checkbox" value="1" name="innews" {$checked6} id="innews" /><label for="innews">{$lang['banners_innews']}</label>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2"></label>
		  <div class="col-lg-10">
			<b>{$lang['banners_s_opt']}</b>
		  </div>
		 </div>		 
		<div class="form-group">
		  <label class="control-label col-lg-2"></label>
		  <div class="col-lg-10">
HTML;

	echo makeDropDown( array ("0" => $lang['banners_s_0'], "1" => $lang['banners_s_1'], "2" => $lang['banners_s_2'], "3" => $lang['banners_s_3'], "4" => $lang['banners_s_4'], "5" => $lang['banners_s_5'], "6" => $lang['banners_s_6'], "7" => $lang['banners_s_7'] ), "short_place", $short_place );
	
	echo <<<HTML
		  <label for="optional">{$lang['banners_s']}</label><br />
		  <br /><input class="icheck" type="checkbox" value="1" name="bstick" {$checked3} id="bstick" /><label for="bstick">{$lang['banners_bstick']}</label>
		  </div>
		 </div>	
		 
	</div>
   </div>
	<div class="box-footer padded">
	<input type="submit" class="btn btn-green" value="{$lang['user_save']}">
	</div>	
</div>
</form>
<script type="text/javascript">
	$(function(){
		  $(".cat_select").chosen({allow_single_deselect:true, no_results_text: '{$lang['addnews_cat_fault']}'});
		  
			var editor = CodeMirror.fromTextArea(document.getElementById('banner_code'), {
			  mode: "htmlmixed",
			  lineNumbers: true,
			  dragDrop: false,
			  indentUnit: 4,
			  indentWithTabs: false
			});

	});
</script>
HTML;
	
	echofooter();

} else {
	$js_array[] = "engine/classes/highlight/highlight.code.js";
	
	echoheader( "<i class=\"icon-shopping-cart\"></i>".$lang['header_banner'], $lang['header_banner_1'] );
	
	$db->query( "SELECT * FROM " . PREFIX . "_banners ORDER BY id DESC" );
	
	$entries = "";
	
	while ( $row = $db->get_row() ) {
		
		$row['descr'] = $row['descr'];
		$row['code'] = "<pre><code>".htmlspecialchars ($row['code'], ENT_QUOTES, $config['charset'])."</code></pre>";

		if ( $row['start'] ) $start_date = date( "d.m.Y H:i", $row['start'] ); else $start_date = "--";
		if ( $row['end'] ) $end_date = date( "d.m.Y H:i", $row['end'] ); else $end_date = "--";

		
		if( $row['approve'] ) {
			$status = "<span title=\"{$lang['banners_on']}\" class=\"status-success tip\"><b><i class=\"icon-ok-sign\"></i></b></span>";
			$lang['led_active'] = $lang['banners_aus'];
			$led_action = "off";
		} else {
			$status = "<span title=\"{$lang['banners_off']}\" class=\"status-error tip\"><b><i class=\"icon-exclamation-sign\"></i></b></span>";
			$lang['led_active'] = $lang['banners_ein'];
			$led_action = "on";
		}
		if( $row['short_place'] ) {
			$status2 = "<span title=\"{$lang['banners_s_on']}\" class=\"status-success tip\"><b><i class=\"icon-ok-sign\"></i></b></span>";
			$lang['led_short'] = $lang['banners_s_on'];
		} else {
			$status2 = "<span title=\"{$lang['banners_s_off']}\" class=\"status-error tip\"><b><i class=\"icon-exclamation-sign\"></i></b></span>";
			$lang['led_short'] = $lang['banners_s_off'];
		}

		$menu_link = <<<HTML
        <div class="btn-group">
          <button class="btn btn-default dropdown-toggle" data-toggle="dropdown"><i class="icon-pencil"></i> {$lang['filter_action']} <span class="caret"></span></button>
          <ul class="dropdown-menu text-left">
            <li><a onclick="javascript:preview('{$row['id']}'); return false;" href="#"><i class="icon-desktop"></i> {$lang['banner_view']}</a></li>
            <li><a href="?mod=banners&user_hash={$dle_login_hash}&action={$led_action}&id={$row['id']}"><i class="icon-eye-open"></i> {$lang['led_active']}</a></li>
            <li><a href="?mod=banners&user_hash={$dle_login_hash}&action=edit&id={$row['id']}"><i class="icon-magic"></i> {$lang['group_sel1']}</a></li>
			<li class="divider"></li>
            <li><a onclick="javascript:confirmdelete('{$row['id']}'); return false;" href="#"><i class="icon-trash"></i> {$lang['cat_del']}</a></li>
          </ul>
        </div>
HTML;
		
		$entries .= "
   <tr>
    <td>
    {$row['descr']}<br />{$lang['banners_tag']}<br />[banner_{$row['banner_tag']}]<br />{banner_{$row['banner_tag']}}<br />[/banner_{$row['banner_tag']}]<br /><br />{$lang['vote_startinfo']}: {$start_date}<br />{$lang['vote_endinfo']}: {$end_date}</td>
    <td>{$row['code']}</td>
    <td>{$status} {$lang['banners_act']}<br />{$status2} {$lang['banners_s_a']}</td>
    <td>{$menu_link}</td>
  </tr>";
	}
	$db->free();
	
	echo <<<HTML
<script language="javascript" type="text/javascript">
<!--
function confirmdelete(id){
	    DLEconfirm( '{$lang['banners_del']}', '{$lang['p_confirm']}', function () {
			document.location="?mod=banners&action=delete&user_hash={$dle_login_hash}&id="+id;
		} );
}

function preview(id){
	window.open('?mod=banners&action=view&id='+id,'prv','height=300,width=650,resizable=1,scrollbars=1');
}

  hljs.initHighlightingOnLoad();
  
//-->
</script>
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['banners_list']}</div>
  </div>
  <div class="box-content">
    <table class="table table-normal" style="table-layout:fixed;">
      <thead>
      <tr>
        <td style="width: 170px">{$lang['static_descr']}</td>
        <td></td>
        <td style="width: 150px">{$lang['banners_opt']}</td>
        <td style="width: 180px">{$lang[vote_action]}</td>
      </tr>
      </thead>
	  <tbody>
		{$entries}
	  </tbody>
	</table>
	<div class="box-footer padded">
		<input onclick="document.location='?mod=banners&action=add'" type="button" class="btn btn-green" value="{$lang['bb_create']}">
		<a class="pull-right" onclick="javascript:Help('banners'); return false;" href="#">{$lang['banners_help']}</a>
	</div>		  
	</div>
	
   </div>
</div>
HTML;
	
	echofooter();

}
?>