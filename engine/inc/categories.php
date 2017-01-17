<?PHP
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
 Файл: categories.php
-----------------------------------------------------
 Назначение: управление категориями
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

$result = "";
$catid = intval( $_REQUEST['catid'] );

if( ! $user_group[$member_id['user_group']]['admin_categories'] ) {
	msg( "error", $lang['index_denied'], $lang['cat_perm'] );
}

function get_sub_cats($id, $subcategory = false) {
	
	global $cat_info;
	$subfound = array ();
	
	if( ! $subcategory ) {
		$subcategory = array ();
		$subcategory[] = $id;
	}
	
	foreach ( $cat_info as $cats ) {
		if( $cats['parentid'] == $id ) {
			$subfound[] = $cats['id'];
		}
	}
	
	foreach ( $subfound as $parentid ) {
		$subcategory[] = $parentid;
		$subcategory = get_sub_cats( $parentid, $subcategory );
	}
	
	return $subcategory;

}

function makeDropDown($options, $name, $selected) {
	$output = "<select class=\"uniform\" name=\"{$name}\" style=\"min-width:100px;\">\r\n";
	foreach ( $options as $value => $description ) {
		$output .= "<option value=\"{$value}\"";
		if( $selected == $value ) {
			$output .= " selected ";
		}
		$output .= ">{$description}</option>\n";
	}
	$output .= "</select>";
	return $output;
}

function SelectSkin($skin) {
	global $lang;
	
	$templates_list = array ();
	
	$handle = opendir( ROOT_DIR . '/templates' );
	
	while ( false !== ($file = readdir( $handle )) ) {
		if( is_dir( ROOT_DIR . "/templates/$file" ) and ($file != "." and $file != ".." and $file != "smartphone") ) {
			$templates_list[] = $file;
		}
	}
	closedir( $handle );
	
	$skin_list = "<select class=\"uniform\" name=\"skin_name\">";
	$skin_list .= "<option value=\"\">" . $lang['cat_skin_sel'] . "</option>";
	
	foreach ( $templates_list as $single_template ) {
		if( $single_template == $skin ) $selected = " selected";
		else $selected = "";
		$skin_list .= "<option value=\"{$single_template}\"" . $selected . ">{$single_template}</option>";
	}
	$skin_list .= '</select>';
	
	return $skin_list;
}

function clear_url_dir( $var ) {
	if ( is_array($var) ) return "";

	$var = str_ireplace( ".php", "", $var );
	$var = str_ireplace( ".php", ".ppp", $var );
	$var = trim( strip_tags( $var ) );
	$var = str_replace( "\\", "/", $var );
	$var = preg_replace( "/[^a-z0-9\/\_\-]+/mi", "", $var );
	return $var;

}

// ********************************************************************************
// Добавление категории
// ********************************************************************************
if( $action == "add" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$quotes = array ("\x27", "\x22", "\x60", "\t", "\n", "\r" );

	if( $_POST['cat_icon'] == $lang['cat_icon'] ) {
		$_POST['cat_icon'] = "";
	}
	
	$cat_name  = $db->safesql(  htmlspecialchars( strip_tags( stripslashes($_POST['cat_name'] ) ), ENT_QUOTES, $config['charset']) );
	$skin_name = trim( totranslit($_POST['skin_name'], false, false) );
	$cat_icon  = $db->safesql(  htmlspecialchars( strip_tags( stripslashes($_POST['cat_icon']) ), ENT_QUOTES, $config['charset']) );
	$show_sub = intval($_POST['show_sub']);
	$allow_rss = intval($_POST['allow_rss']);

	if( ! $cat_name ) {
		msg( "error", $lang['cat_error'], $lang['cat_ername'], "javascript:history.go(-1)" );
	}

	if (trim($_POST['alt_cat_name'])) {

		$alt_cat_name = totranslit( stripslashes( $_POST['alt_cat_name'] ), true, false );

	} else {

		$alt_cat_name = totranslit( stripslashes( $cat_name ), true, false );

	}

	if( ! $alt_cat_name ) {
		msg( "error", $lang['cat_error'], $lang['cat_erurl'], "javascript:history.go(-1)" );
	}
	
	if ( in_array($_POST['news_sort'], array("date", "rating", "news_read", "title")) )	{

		$news_sort = $db->safesql( $_POST['news_sort'] );

	} else $news_sort = "";

	if ( in_array($_POST['news_msort'], array("ASC", "DESC")) )	{

		$news_msort = $db->safesql( $_POST['news_msort'] );

	} else $news_msort = "";

	if ( $_POST['news_number'] > 0)
		$news_number = intval( $_POST['news_number'] );
	else $news_number = 0;

	if ( $_POST['category'] > 0)
		$category = intval( $_POST['category'] );
	else $category = 0;

	$reserved_name = array('tags','xfsearch','user','lastnews','catalog','newposts','favorites');

	if (in_array($alt_cat_name, $reserved_name) AND !$category)	{
	
		msg( "error", $lang['cat_error'], $lang['cat_resname'], "javascript:history.go(-1)" );	
	}

	if ( $_POST['short_tpl'] ) {

		$url = @parse_url ( $_POST['short_tpl'] );
		$file_path = dirname (clear_url_dir($url['path']));
		$tpl_name = pathinfo($url['path']);
		$tpl_name = totranslit($tpl_name['basename']);

		if ($file_path AND $file_path != ".") $tpl_name = $file_path."/".$tpl_name;

		$short_tpl = $tpl_name;

	} else $short_tpl = "";
	
	if ( $_POST['full_tpl'] ) {

		$url = @parse_url ( $_POST['full_tpl'] );
		$file_path = dirname (clear_url_dir($url['path']));
		$tpl_name = pathinfo($url['path']);
		$tpl_name = totranslit($tpl_name['basename']);

		if ($file_path AND $file_path != ".") $tpl_name = $file_path."/".$tpl_name;

		$full_tpl = $tpl_name;

	} else $full_tpl = "";
	
	$meta_title = $db->safesql( htmlspecialchars ( strip_tags( stripslashes( $_POST['meta_title'] ) ), ENT_QUOTES, $config['charset'] ) );
	$description = $db->safesql( dle_substr( strip_tags( stripslashes( $_POST['descr'] ) ), 0, 200, $config['charset'] ) );
	$keywords = $db->safesql( str_replace( $quotes, " ", strip_tags( stripslashes( $_POST['keywords'] ) ) ) );
	
	$row = $db->super_query( "SELECT alt_name FROM " . PREFIX . "_category WHERE alt_name ='{$alt_cat_name}'" );
	
	if( $row['alt_name'] ) {
		msg( "error", $lang['cat_error'], $lang['cat_eradd'], "?mod=categories" );
	}
	
	$db->query( "INSERT INTO " . PREFIX . "_category (parentid, name, alt_name, icon, skin, descr, keywords, news_sort, news_msort, news_number, short_tpl, full_tpl, metatitle, show_sub, allow_rss) values ('$category', '$cat_name', '$alt_cat_name', '$cat_icon', '$skin_name', '$description', '$keywords', '$news_sort', '$news_msort', '$news_number', '$short_tpl', '$full_tpl', '$meta_title', '$show_sub', '$allow_rss')" );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '12', '{$cat_name}')" );

	
	@unlink( ENGINE_DIR . '/cache/system/category.php' );
	clear_cache();
	
	msg( "info", $lang['cat_addok'], $lang['cat_addok_1'], "?mod=categories" );

} 
// ********************************************************************************
// Удаление категории
// ********************************************************************************
elseif( $action == "remove" ) {
	

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	function DeleteSubcategories($parentid) {
		global $db;
		
		$subcategories = $db->query( "SELECT id FROM " . PREFIX . "_category WHERE parentid = '$parentid'" );
		
		while ( $subcategory = $db->get_row( $subcategories ) ) {
			DeleteSubcategories( $subcategory['id'] );
			
			$db->query( "DELETE FROM " . PREFIX . "_category WHERE id = '" . $subcategory['id'] . "'" );
		}
	}
	
	if( ! $catid ) {
		msg( "error", $lang['cat_error'], $lang['cat_noid'], "?mod=categories" );
	}
	
	$row = $db->super_query( "SELECT count(*) as count FROM " . PREFIX . "_post WHERE category regexp '[[:<:]]($catid)[[:>:]]'" );
	
	if( $row['count'] ) {
		
		if( is_array( $_REQUEST['new_category'] ) ) {
			if( ! in_array( $catid, $_REQUEST['new_category'] ) ) {
				
				$category_list = $db->safesql( htmlspecialchars( strip_tags( stripslashes( implode( ',', $_REQUEST['new_category']))), ENT_QUOTES, $config['charset'] ) );
				
				$db->query( "UPDATE " . PREFIX . "_post set category='$category_list' WHERE category regexp '[[:<:]]($catid)[[:>:]]'" );
				
				$db->query( "DELETE FROM " . PREFIX . "_category WHERE id='$catid'" );
				
				DeleteSubcategories( $catid );
				
				@unlink( ENGINE_DIR . '/cache/system/category.php' );
				
				clear_cache();

				$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '13', '{$catid}')" );

				
				msg( "info", $lang['cat_delok'], $lang['cat_delok_1'], "?mod=categories" );
			}
		}
		
		msg( "info", $lang['all_info'], "<form action=\"\" method=\"post\">{$lang['comm_move']} <select name=\"new_category[]\" class=\"categoryselect\" data-placeholder=\"{$lang['addnews_cat_sel']}\" style=\"width:350px;\" multiple>" . CategoryNewsSelection( 0, 0 ) . "</select> <input class=\"btn btn-green\" type=\"submit\" value=\"{$lang['b_start']}\"><script type=\"text/javascript\">
$(function(){
	$('.categoryselect').chosen({allow_single_deselect:true, no_results_text: '{$lang['addnews_cat_fault']}'});
});
</script></form>", "?mod=categories" );
	
	} else {
		
		$db->query( "DELETE FROM " . PREFIX . "_category WHERE id='$catid'" );
		
		DeleteSubcategories( $catid );
		
		@unlink( ENGINE_DIR . '/cache/system/category.php' );

		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '13', '{$catid}')" );
		
		clear_cache();
		
		msg( "info", $lang['cat_delok'], $lang['cat_delok_1'], "?mod=categories" );
	}
} 
// ********************************************************************************
// Редактирование категории
// ********************************************************************************
elseif( $action == "edit" ) {
	
	$catid = intval( $_GET['catid'] );
	
	if( ! $catid ) {
		msg( "error", $lang['cat_error'], $lang['cat_noid'], "?mod=categories" );
	}
	
	$row = $db->super_query( "SELECT * FROM " . PREFIX . "_category WHERE id = '{$catid}'" );
	
	if( ! $row['id'] ) msg( "error", $lang['cat_error'], $lang['cat_noid'], "?mod=categories" );

	echoheader( "<i class=\"icon-folder-open-alt\"></i>".$lang['cat_head'], $lang['opt_catc_1'] );
	
	$categorylist = CategoryNewsSelection( $row['parentid'], 0 );
	$skinlist = SelectSkin( $row['skin'] );
	
	$row['name'] = stripslashes( preg_replace( array ("'\"'", "'\''" ), array ("&quot;", "&#039;" ), $row['name'] ) );
	$row['metatitle'] = stripslashes( preg_replace( array ("'\"'", "'\''" ), array ("&quot;", "&#039;" ), $row['metatitle'] ) );
	$row['descr'] = stripslashes( preg_replace( array ("'\"'", "'\''" ), array ("&quot;", "&#039;" ), $row['descr'] ) );
	$row['keywords'] = stripslashes( preg_replace( array ("'\"'", "'\''" ), array ("&quot;", "&#039;" ), $row['keywords'] ) );
	
	$row['news_sort'] = makeDropDown( array ("" => $lang['sys_global'], "date" => $lang['opt_sys_sdate'], "rating" => $lang['opt_sys_srate'], "news_read" => $lang['opt_sys_sview'], "title" => $lang['opt_sys_salph'] ), "news_sort", $row['news_sort'] );
	$row['news_msort'] = makeDropDown( array ("" => $lang['sys_global'], "DESC" => $lang['opt_sys_mminus'], "ASC" => $lang['opt_sys_mplus'] ), "news_msort", $row['news_msort'] );
	$row['show_sub'] = makeDropDown( array ("0" => $lang['sys_global'], "1" => $lang['opt_sys_yes'], "2" => $lang['opt_sys_no'] ), "show_sub", $row['show_sub'] );
	$row['allow_rss'] = makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "allow_rss", $row['allow_rss'] );
	
	echo <<<HTML
<form method="post" action="" class="form-horizontal">
  <input type="hidden" name="action" value="doedit">
  <input type="hidden" name="user_hash" value="{$dle_login_hash}" />
  <input type="hidden" name="catid" value="{$row['id']}">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['cat_edit']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['cat_name']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:350px;" value="{$row['name']}" type="text" name="cat_name">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_catname']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['cat_url']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:350px;" value="{$row['alt_name']}" type="text" name="alt_cat_name">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_cataltname']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['cat_addicon']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:350px;" value="{$row['icon']}" type="text" name="cat_icon">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_caticon']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['meta_title']}</label>
		  <div class="col-lg-10">
			<input type="text" name="meta_title" style="width:100%;max-width:350px;" value="{$row['metatitle']}"> ({$lang['meta_descr_max']})
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['meta_descr_cat']}</label>
		  <div class="col-lg-10">
			<input type="text" name="descr" style="width:100%;max-width:350px;" value="{$row['descr']}"> ({$lang['meta_descr_max']})
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['meta_keys']}</label>
		  <div class="col-lg-10">
			<textarea name="keywords" style="width:100%;max-width:350px;" rows="5">{$row['keywords']}</textarea>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['cat_parent']}</label>
		  <div class="col-lg-10">
			<select class="uniform" name="parentid" >{$categorylist}</select>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['cat_skin']}</label>
		  <div class="col-lg-10">
			{$skinlist}&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_cattempl']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['opt_sys_sort']}</label>
		  <div class="col-lg-10">
			{$row['news_sort']}
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['opt_sys_msort']}</label>
		  <div class="col-lg-10">
			{$row['news_msort']}
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['opt_sys_sub']}</label>
		  <div class="col-lg-10">
			{$row['show_sub']}
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['cat_allow_rss']}</label>
		  <div class="col-lg-10">
			{$row['allow_rss']}
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['opt_sys_newc']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:145px;" type="text" name="news_number" value="{$row['news_number']}">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_news_number']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['cat_s_tpl']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:200px;" type="text" name="short_tpl" value="{$row['short_tpl']}">.tpl&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['cat_s_tpl_hit']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['cat_f_tpl']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:200px;" type="text" name="full_tpl" value="{$row['full_tpl']}">.tpl&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['cat_f_tpl_hit']}" >?</span>
		  </div>
		 </div>		
		<div class="form-group">
		  <label class="control-label col-lg-2"></label>
		  <div class="col-lg-10">
			<input type="submit" class="btn btn-green" value="{$lang['vote_edit']}">
		  </div>
		 </div>

	 </div>
   </div>
</div>
</form>
HTML;
	
	echofooter();
	die();

} 
// ********************************************************************************
// Запись отредактированной категории
// ********************************************************************************
elseif( $action == "doedit" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$quotes = array ("\x27", "\x22", "\x60", "\t", "\n", "\r", '"' );

	$cat_name  = $db->safesql(  htmlspecialchars( strip_tags( stripslashes($_POST['cat_name'] ) ), ENT_QUOTES, $config['charset']) );
	$skin_name = trim( totranslit($_POST['skin_name'], false, false) );
	$cat_icon  = $db->safesql(  htmlspecialchars( strip_tags( stripslashes($_POST['cat_icon']) ), ENT_QUOTES, $config['charset']) );

	if (trim($_POST['alt_cat_name'])) {

		$alt_cat_name = totranslit( stripslashes( $_POST['alt_cat_name'] ), true, false );

	} else {

		$alt_cat_name = totranslit( stripslashes( $cat_name ), true, false );

	}

	$show_sub = intval($_POST['show_sub']);
	$allow_rss = intval($_POST['allow_rss']);
		
	$catid = intval( $_POST['catid'] );
	$parentid = intval( $_POST['parentid'] );

	$meta_title = $db->safesql( htmlspecialchars ( strip_tags( stripslashes( $_POST['meta_title'] ) ), ENT_QUOTES, $config['charset'] ) );
	$description = $db->safesql( dle_substr( strip_tags( stripslashes( $_POST['descr'] ) ), 0, 200, $config['charset'] ) );
	$keywords = $db->safesql( str_replace( $quotes, " ", strip_tags( stripslashes( $_POST['keywords'] ) ) ) );

	$reserved_name = array('tags','xfsearch','user','lastnews','catalog','newposts','favorites');

	if (in_array($alt_cat_name, $reserved_name) AND !$parentid)	{
	
		msg( "error", $lang['cat_error'], $lang['cat_resname'], "javascript:history.go(-1)" );	
	}

	if ( $_POST['short_tpl'] ) {

		$url = @parse_url ( $_POST['short_tpl'] );
		$file_path = dirname (clear_url_dir($url['path']));
		$tpl_name = pathinfo($url['path']);
		$tpl_name = totranslit($tpl_name['basename']);

		if ($file_path AND $file_path != ".") $tpl_name = $file_path."/".$tpl_name;

		$short_tpl = $tpl_name;

	} else $short_tpl = "";
	
	if ( $_POST['full_tpl'] ) {

		$url = @parse_url ( $_POST['full_tpl'] );
		$file_path = dirname (clear_url_dir($url['path']));
		$tpl_name = pathinfo($url['path']);
		$tpl_name = totranslit($tpl_name['basename']);

		if ($file_path AND $file_path != ".") $tpl_name = $file_path."/".$tpl_name;

		$full_tpl = $tpl_name;

	} else $full_tpl = "";

	if ( in_array($_POST['news_sort'], array("date", "rating", "news_read", "title")) )	{

		$news_sort = $db->safesql( $_POST['news_sort'] );

	} else $news_sort = "";

	if ( in_array($_POST['news_msort'], array("ASC", "DESC")) )	{

		$news_msort = $db->safesql( $_POST['news_msort'] );

	} else $news_msort = "";

	if ( $_POST['news_number'] > 0)
		$news_number = intval( $_POST['news_number'] );
	else $news_number = 0;
	
	if( ! $catid ) {
		msg( "error", $lang['cat_error'], $lang['cat_noid'], "?mod=categories" );
	}
	if( $cat_name == "" ) {
		msg( "error", $lang['cat_error'], $lang['cat_noname'], "javascript:history.go(-1)" );
	}
	
	$row = $db->super_query( "SELECT id, alt_name FROM " . PREFIX . "_category WHERE alt_name = '$alt_cat_name'" );
	
	if( $row['id'] and $row['id'] != $catid ) {
		msg( "error", $lang['cat_error'], $lang['cat_eradd'], "javascript:history.go(-1)" );
	}
	
	if( in_array( $parentid, get_sub_cats( $catid ) ) ) {
		msg( "error", $lang['cat_error'], $lang['cat_noparentid'], "?mod=categories" );
	}

	$db->query( "UPDATE " . PREFIX . "_category SET parentid='$parentid', name='$cat_name', alt_name='$alt_cat_name', icon='$cat_icon', skin='$skin_name', descr='$description', keywords='$keywords', news_sort='$news_sort', news_msort='$news_msort', news_number='$news_number', short_tpl='$short_tpl', full_tpl='$full_tpl', metatitle='$meta_title', show_sub='$show_sub', allow_rss='$allow_rss' WHERE id='{$catid}'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '14', '{$cat_name}')" );
	
	@unlink( ENGINE_DIR . '/cache/system/category.php' );
	clear_cache();
	
	msg( "info", $lang['cat_editok'], $lang['cat_editok_1'], "?mod=categories" );
}
// ********************************************************************************
// List all Categories
// ********************************************************************************

echoheader( "<i class=\"icon-folder-open-alt\"></i>".$lang['cat_head'], $lang['opt_catc_1'] );

$categorylist = CategoryNewsSelection( 0, 0 );
$skinlist = SelectSkin( '' );

echo <<<HTML
<div style="display:none" name="newcats" id="newcats">
<form method="post" action="" class="form-horizontal">
<input type="hidden" name="mod" value="categories">
<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
<input type="hidden" name="action" value="add">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['cat_add']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['cat_name']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:350px;" type="text" name="cat_name">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_catname']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['cat_url']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:350px;" type="text" name="alt_cat_name">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_cataltname']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['cat_addicon']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:350px;" type="text" name="cat_icon">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_caticon']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['meta_title']}</label>
		  <div class="col-lg-10">
			<input type="text" name="meta_title" style="width:100%;max-width:350px;"> ({$lang['meta_descr_max']})
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['meta_descr_cat']}</label>
		  <div class="col-lg-10">
			<input type="text" name="descr" style="width:100%;max-width:350px;"> ({$lang['meta_descr_max']})
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['meta_keys']}</label>
		  <div class="col-lg-10">
			<textarea name="keywords" style="width:100%;max-width:350px;" rows="5"></textarea>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['cat_parent']}</label>
		  <div class="col-lg-10">
			<select class="uniform" name="category" >{$categorylist}</select>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['cat_skin']}</label>
		  <div class="col-lg-10">
			{$skinlist}&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_cattempl']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['opt_sys_sort']}</label>
		  <div class="col-lg-10">
			<select class="uniform" name="news_sort">
<option value="" selected >{$lang['sys_global']}</option>
<option value="date">{$lang['opt_sys_sdate']}</option>
<option value="rating">{$lang['opt_sys_srate']}</option>
<option value="news_read">{$lang['opt_sys_sview']}</option>
<option value="title">{$lang['opt_sys_salph']}</option>
</select>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['opt_sys_msort']}</label>
		  <div class="col-lg-10">
			<select class="uniform" name="news_msort">
<option value="" selected >{$lang['sys_global']}</option>
<option value="DESC">{$lang['opt_sys_mminus']}</option>
<option value="ASC">{$lang['opt_sys_mplus']}</option>
</select>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['opt_sys_sub']}</label>
		  <div class="col-lg-10">
			<select class="uniform" name="show_sub">
<option value="0" selected >{$lang['sys_global']}</option>
<option value="1">{$lang['opt_sys_yes']}</option>
<option value="2">{$lang['opt_sys_no']}</option>
</select>
		  </div>
		 </div>

		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['cat_allow_rss']}</label>
		  <div class="col-lg-10">
			<select class="uniform" name="allow_rss" style="min-width:100px;">
<option value="1" selected>{$lang['opt_sys_yes']}</option>
<option value="0">{$lang['opt_sys_no']}</option>
</select>
		  </div>
		 </div>	

		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['opt_sys_newc']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:145px;" type="text" name="news_number">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_news_number']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['cat_s_tpl']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:200px;" type="text" name="short_tpl">.tpl&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['cat_s_tpl_hit']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['cat_f_tpl']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:200px;" type="text" name="full_tpl">.tpl&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['cat_f_tpl_hit']}" >?</span>
		  </div>
		 </div>		
		<div class="form-group">
		  <label class="control-label col-lg-2"></label>
		  <div class="col-lg-10">
			<input type="submit" class="btn btn-green" value="{$lang['vote_new']}">
		  </div>
		 </div>
		 
	</div>
  </div>
</div>
</form>
</div>
HTML;


if( ! count( $cat_info ) ) {
	
	echo <<<HTML
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['cat_list']}</div>
	<ul class="box-toolbar">
      <li class="toolbar-link">
          <a href="javascript:ShowOrHide('newcats');"><i class="icon-plus"></i> {$lang['b_cats_1']}</a>
      </li>
    </ul>
  </div>
  <div class="box-content">

	<div class="row box-section">
	{$lang['cat_nocat']}
	</div>
  </div>
</div>
HTML;

} else {

	function DisplayCategories($parentid = 0, $sublevelmarker = false) {
		global $lang, $cat_info, $config, $dle_login_hash;

		$cat_item = "";
		
		if( count( $cat_info ) ) {
			
			foreach ( $cat_info as $cats ) {
				if( $cats['parentid'] == $parentid ) $root_category[] = $cats['id'];
			}
			
			if( count( $root_category ) ) {
				
				foreach ( $root_category as $id ) {
					
					$category_name = $cat[$id];
					
					if( $config['allow_alt_url'] ) $link = "<a href=\"" . $config['http_home_url'] . get_url( $id ) . "/\" target=\"_blank\">" . stripslashes( $cat_info[$id]['name'] ) . "</a>";
					else $link = "<a href=\"{$config['http_home_url']}index.php?do=cat&category=" . $cat_info[$id]['alt_name'] . "\" target=\"_blank\">" . stripslashes( $cat_info[$id]['name'] ) . "</a>";

					$cat_item .= "<li class=\"dd-item\" data-id=\"{$cat_info[$id]['id']}\"><div class=\"dd-handle\"><b>ID:{$cat_info[$id]['id']}</b> {$link} <div class=\"pull-right\"><a href=\"?mod=categories&action=edit&catid=" . $cat_info[$id]['id'] . "\"><i title=\"{$lang['cat_ed']}\" alt=\"{$lang['cat_ed']}\" class=\"icon-pencil bigger-130\"></i></a>&nbsp;&nbsp;<a onclick=\"javascript:cdelete('{$cat_info[$id]['id']}'); return(false);\" href=\"?mod=categories&user_hash=" . $dle_login_hash . "&action=remove&catid=" . $cat_info[$id]['id'] . "\"><i title=\"{$lang['cat_del']}\" alt=\"{$lang['cat_del']}\" class=\"icon-trash bigger-130 status-error\"></i></a></div></div>";
					
					$cat_item .= DisplayCategories( $id, true );
				}

				if( $sublevelmarker ) return "<ol class=\"dd-list\">".$cat_item."</ol>"; else return $cat_item;

			}
		}
		
	}


	echo <<<HTML
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['cat_list']}</div>
	<ul class="box-toolbar">
      <li class="toolbar-link">
          <a href="javascript:ShowOrHide('newcats');"><i class="icon-plus"></i> {$lang['b_cats_1']}</a>
      </li>
    </ul>
  </div>
  <div class="box-content">
	
		<div class="dd"><ol class="dd-list">
HTML;

	echo DisplayCategories();

	echo <<<HTML
		</ol></div>

	<div class="box-footer padded">
		<button id="catsort" class="btn btn-blue">{$lang['cat_posi']}</button>
	</div>
  </div>
</div>
<script>
	$(document).ready(function(){

		$('.dd').nestable({
			maxDepth: 500
		});

		$('.dd-handle a').on('mousedown', function(e){
			e.stopPropagation();
		});

		$('#catsort').click(function(){
		
			var url = "action=catsort&user_hash={$dle_login_hash}&list="+window.JSON.stringify($('.dd').nestable('serialize'));
			ShowLoading('');
			$.post('engine/ajax/adminfunction.php', url, function(data){
	
				HideLoading('');
	
				if (data == 'ok') {

					DLEalert('{$lang['cat_sort_ok']}', '{$lang['p_info']}');

				} else {

					DLEalert('{$lang['cat_sort_fail']}', '{$lang['p_info']}');

				}
	
			});

		});


	});

	function cdelete(id){
		
	    DLEconfirm( '{$lang['cat_delete']}', '{$lang['p_confirm']}', function () {
			document.location='?mod=categories&user_hash={$dle_login_hash}&action=remove&catid=' + id + '';
		} );
	}
</script>
HTML;
}


echofooter();
?>