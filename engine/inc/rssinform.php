<?php
/*
=====================================================
DataLife Engine - by SoftNews Media Group
-----------------------------------------------------
http://dle-news.ru/
-----------------------------------------------------
Copyright (c) 2004-2016 SoftNews Media Group
=====================================================
Файл: rssinform.php
-----------------------------------------------------
Назначение: управление RSS информерами
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_rssinform'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if( isset( $_REQUEST['id'] ) ) $id = intval( $_REQUEST['id'] ); else $id = "";

if( $_REQUEST['action'] == "doadd" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$rss_tag = totranslit( strip_tags( trim( $_POST['rss_tag'] ) ) );
	$rss_descr = $db->safesql( strip_tags( trim( $_POST['rss_descr'] ) ) );
	$rss_url = $db->safesql( strip_tags( trim( $_POST['rss_url'] ) ) );
	$rss_template = totranslit( strip_tags( trim( $_POST['rss_template'] ) ) );
	$rss_max = intval( $_POST['rss_max'] );
	$rss_tmax = intval( $_POST['rss_tmax'] );
	$rss_dmax = intval( $_POST['rss_dmax'] );
	$rss_date_format = $db->safesql( strip_tags( trim( $_POST['rss_date_format'] ) ) );

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
	
	if( $rss_tag == "" or $rss_descr == "" or $rss_url == "" or $rss_template == "" ) msg( "error", $lang['addnews_error'], $lang['addnews_erstory'], "javascript:history.go(-1)" );
	
	$db->query( "INSERT INTO " . PREFIX . "_rssinform (tag, descr, category, url, template, news_max, tmax, dmax, rss_date_format) values ('$rss_tag', '$rss_descr', '$category', '$rss_url', '$rss_template', '$rss_max', '$rss_tmax', '$rss_dmax', '$rss_date_format')" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '53', '{$rss_tag}')" );

	@unlink( ENGINE_DIR . '/cache/system/informers.php' );
	clear_cache();
	header( "Location: http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"] . "?mod=rssinform" );

}

if( $_REQUEST['action'] == "doedit" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$rss_tag = totranslit( strip_tags( trim( $_POST['rss_tag'] ) ) );
	$rss_descr = $db->safesql( strip_tags( trim( $_POST['rss_descr'] ) ) );
	$rss_url = $db->safesql( strip_tags( trim( $_POST['rss_url'] ) ) );
	$rss_template = totranslit( strip_tags( trim( $_POST['rss_template'] ) ) );
	$rss_max = intval( $_POST['rss_max'] );
	$rss_tmax = intval( $_POST['rss_tmax'] );
	$rss_dmax = intval( $_POST['rss_dmax'] );
	$rss_date_format = $db->safesql( strip_tags( trim( $_POST['rss_date_format'] ) ) );
	
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
	
	if( $rss_tag == "" or $rss_descr == "" or $rss_url == "" or $rss_template == "" ) msg( "error", $lang['addnews_error'], $lang['addnews_erstory'], "javascript:history.go(-1)" );
	
	$db->query( "UPDATE " . PREFIX . "_rssinform SET tag='$rss_tag', descr='$rss_descr', category='$category', url='$rss_url', template='$rss_template', news_max='$rss_max', tmax='$rss_tmax', dmax='$rss_dmax', rss_date_format='$rss_date_format' WHERE id='$id'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '54', '{$rss_tag}')" );

	@unlink( ENGINE_DIR . '/cache/system/informers.php' );
	clear_cache();
	header( "Location: http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"] . "?mod=rssinform" );
}

if( $_GET['action'] == "off" AND $id) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$db->query( "UPDATE " . PREFIX . "_rssinform set approve='0' WHERE id='$id'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '55', '{$id}')" );

	@unlink( ENGINE_DIR . '/cache/system/informers.php' );
	clear_cache();
}
if( $_GET['action'] == "on" AND $id) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$db->query( "UPDATE " . PREFIX . "_rssinform set approve='1' WHERE id='$id'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '56', '{$id}')" );

	@unlink( ENGINE_DIR . '/cache/system/informers.php' );
	clear_cache();
}

if( $_GET['action'] == "delete" AND $id) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$db->query( "DELETE FROM " . PREFIX . "_rssinform WHERE id='$id'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '57', '{$id}')" );

	@unlink( ENGINE_DIR . '/cache/system/informers.php' );
	clear_cache();
}

if( $_REQUEST['action'] == "add" or $_REQUEST['action'] == "edit" ) {
	
	if( $_REQUEST['action'] == "add" ) {
		$doaction = "doadd";
		$all_cats = "selected";
		$rss_max = "5";
		$rss_tmax = 0;
		$rss_dmax = 200;
		$rss_template = "informer";
		$rss_date_format = "j F Y H:i";
	
	} else {
		
		$row = $db->super_query( "SELECT * FROM " . PREFIX . "_rssinform WHERE id='$id' LIMIT 0,1" );
		$rss_tag = $row['tag'];
		$rss_descr = htmlspecialchars( stripslashes( $row['descr'] ), ENT_QUOTES, $config['charset'] );
		$rss_url = htmlspecialchars( stripslashes( $row['url'] ), ENT_QUOTES, $config['charset'] );
		$rss_template = htmlspecialchars( stripslashes( $row['template'] ), ENT_QUOTES, $config['charset'] );
		$rss_max = $row['news_max'];
		$rss_tmax = $row['tmax'];
		$rss_dmax = $row['dmax'];
		$rss_date_format = $row['rss_date_format'];
		$doaction = "doedit";
	}
	
	$opt_category = CategoryNewsSelection( explode( ',', $row['category'] ), 0, FALSE );
	if( ! $row['category'] ) $all_cats = "selected";
	else $all_cats = "";
	
	echoheader( "<i class=\"icon-rss\"></i>".$lang['opt_rssinform'], $lang['header_rs_2'] );
	
	echo <<<HTML
<form action="" method="post" class="form-horizontal">
<input type="hidden" name="mod" value="rssinform">
<input type="hidden" name="action" value="{$doaction}">
<input type="hidden" name="user_hash" value="$dle_login_hash" />
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['opt_rssinform']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['rssinform_xname']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:350px;" type="text" name="rss_tag" value="{$rss_tag}" />&nbsp;&nbsp;&nbsp;({$lang['xf_lat']})
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['rssinform_xdescr']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:350px;" type="text" name="rss_descr" value="{$rss_descr}" />
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['addnews_cat']}</label>
		  <div class="col-lg-10">
			<select name="category[]" style="width:100%;max-width:350px;height:100px;" multiple>
   <option value="0" {$all_cats}>{$lang['edit_all']}</option>
   {$opt_category}
   </select>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['rssinform_url']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:350px;" type="text" name="rss_url" value="{$rss_url}" />
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['opt_sys_an']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:200px;" type="text" name="rss_date_format" value="{$rss_date_format}" /> <a onclick="javascript:Help('date'); return false;" class="status-info" href="#">{$lang['opt_sys_and']}</a>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['rssinform_template']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:200px;" type="text" name="rss_template" value="{$rss_template}" /> .tpl
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['rssinform_max']}</label>
		  <div class="col-lg-10">
			<input style="width: 100px;" type="text" name="rss_max" value="{$rss_max}" />&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_ri_max']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['rssinform_tmax']}</label>
		  <div class="col-lg-10">
			<input style="width: 100px;" type="text" name="rss_tmax" value="{$rss_tmax}" />&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_ri_tmax']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['rssinform_dmax']}</label>
		  <div class="col-lg-10">
			<input style="width: 100px;" type="text" name="rss_dmax" value="{$rss_dmax}" />&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_ri_dmax']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2"></label>
		  <div class="col-lg-10">
			<input type="submit" class="btn btn-green" value="{$lang['user_save']}">
		  </div>
		 </div>
		 
	</div>
	
   </div>
</div>
</form>
HTML;
	
	echofooter();

} else {
	
	echoheader( "<i class=\"icon-rss\"></i>".$lang['opt_rssinform'], $lang['header_rs_2'] );
	
	$db->query( "SELECT * FROM " . PREFIX . "_rssinform ORDER BY id ASC" );
	
	$entries = "";
	
	if( ! $config['rss_informer'] ) $offline = "<div class=\"well relative\"><span class=\"triangle-button green\"><i class=\"icon-bell\"></i></span>{$lang['modul_offline']}</div>";
	else $offline = "";
	
	while ( $row = $db->get_row() ) {
		
		$row['descr'] = stripslashes( $row['descr'] );
		$row['tag'] = "{inform_" . $row['tag'] . "}";
		
		if( $row['approve'] ) {
			$status = "<span title=\"{$lang['rssinform_on']}\" class=\"status-success tip\"><b><i class=\"icon-ok-sign\"></i></b></span>";
			$lang['led_active'] = $lang['banners_aus'];
			$led_action = "off";
		} else {
			$status = "<span title=\"{$lang['rssinform_off']}\" class=\"status-error tip\"><b><i class=\"icon-exclamation-sign\"></i></b></span>";
			$lang['led_active'] = $lang['rssinform_ein'];
			$led_action = "on";
		}

		$menu_link = <<<HTML
        <div class="btn-group">
          <button class="btn btn-default dropdown-toggle" data-toggle="dropdown"><i class="icon-cog"></i> {$lang['filter_action']} <span class="caret"></span></button>
          <ul class="dropdown-menu text-left">
            <li><a onclick="document.location='?mod=rssinform&user_hash={$dle_login_hash}&action={$led_action}&id={$row['id']}'; return(false)" href="#"><i class="icon-eye-open"></i> {$lang['led_active']}</a></li>
            <li><a onclick="document.location='?mod=rssinform&user_hash={$dle_login_hash}&action=edit&id={$row['id']}'; return(false)" href="#"><i class="icon-pencil"></i> {$lang['group_sel1']}</a></li>
			<li class="divider"></li>
            <li><a onclick="javascript:confirmdelete('{$row['id']}'); return(false);" href="#"><i class="icon-trash"></i> {$lang['cat_del']}</a></li>
          </ul>
        </div>
HTML;

		
		$entries .= "
	   <tr>
		<td>{$row['tag']}</td>
		<td>{$row['descr']}</td>
		<td>{$row['template']}.tpl</td>
		<td align=\"center\">{$status}</td>
		<td>{$menu_link}</td>
		 </tr>";
	}
	$db->free();
	
	echo <<<HTML
<script language="javascript" type="text/javascript">
<!--
function confirmdelete(id){
	    DLEconfirm( '{$lang['rssinform_del']}', '{$lang['p_confirm']}', function () {
			document.location="?mod=rssinform&user_hash={$dle_login_hash}&action=delete&id="+id;
		} );
}
//-->
</script>
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['rssinform_title']}</div>
  </div>
  <div class="box-content">

    <table class="table table-normal table-hover">
      <thead>
      <tr>
        <td style="width: 200px">{$lang['banners_tag']}</td>
        <td>{$lang['static_descr']}</td>
        <td style="width: 200px">{$lang['rssinform_template']}</td>
		<td style="width: 150px">{$lang['banners_opt']}</td>
        <td style="width: 200px">{$lang['vote_action']}</td>
      </tr>
      </thead>
	  <tbody>
		{$entries}
	  </tbody>
	</table>
	
   </div>
	<div class="box-footer padded">
		<input onclick="document.location='?mod=rssinform&action=add'" type="button" class="btn btn-blue" value="{$lang['rssinform_create']}">
	</div>	
</div>
{$offline}
HTML;
	
	echofooter();

}
?>