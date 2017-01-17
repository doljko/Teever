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
 Файл: rss.php
-----------------------------------------------------
 Назначение: Управление RSS каналами
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_rss'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if( isset( $_REQUEST['id'] ) ) $id = intval( $_REQUEST['id'] ); else $id = "";


if( $_GET['subaction'] == "clear" ) {

	$lastdate = intval( $_GET['lastdate'] );
	if( $id and $lastdate ) $db->query( "UPDATE " . PREFIX . "_rss SET lastdate='$lastdate' WHERE id='$id'" );

}

if( $_REQUEST['action'] == "addnews" ) {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	include_once ENGINE_DIR . '/classes/parse.class.php';
	
	$parse = new ParseFilter( Array (), Array (), 1, 1 );
	
	$allow_comm = intval( $_POST['allow_comm'] );
	$allow_main = intval( $_POST['allow_main'] );
	$allow_rating = intval( $_POST['allow_rating'] );
	$news_fixed = 0;
	$allow_br = intval( $_POST['text_type'] );
	$lastdate = intval( $_POST['lastdate'] );
	
	if( count( $_POST['content'] ) ) {
		
		foreach ( $_POST['content'] as $content ) {
			$approve = intval( $content['approve'] );
			
			if( ! count( $content['category'] ) ) {
				$content['category'] = array ();
				$content['category'][] = '0';
			}

			$category_list = array();
		
			foreach ( $content['category'] as $value ) {
				$category_list[] = intval($value);
			}
		
			$category_list = $db->safesql( implode( ',', $category_list ) );
			
			$full_story = $parse->process( $content['full'] );
			$short_story = $parse->process( $content['short'] );
			$title = $parse->process(  trim( strip_tags ($content['title']) ) );
			$_POST['title'] = $title;
			$alt_name = totranslit( stripslashes( $title ) );
			$title = $db->safesql( $title );
			
			if( ! $allow_br ) {
				$full_story = $db->safesql( $parse->BB_Parse( $full_story ) );
				$short_story = $db->safesql( $parse->BB_Parse( $short_story ) );
			} else {
				$full_story = $db->safesql( $parse->BB_Parse( $full_story, false ) );
				$short_story = $db->safesql( $parse->BB_Parse( $short_story, false ) );
			}
			
			$metatags = create_metatags( $short_story . $full_story );
			$thistime = date( "Y-m-d H:i:s", strtotime( $content['date'] ) );
			
			if( trim( $title ) == "" ) {
				msg( "error", $lang['addnews_error'], $lang['addnews_ertitle'], "javascript:history.go(-1)" );
			}
			if( trim( $short_story ) == "" ) {
				msg( "error", $lang['addnews_error'], $lang['addnews_erstory'], "javascript:history.go(-1)" );
			}
			
			$db->query( "INSERT INTO " . PREFIX . "_post (date, autor, short_story, full_story, xfields, title, descr, keywords, category, alt_name, allow_comm, approve, allow_main, allow_br) values ('$thistime', '{$member_id['name']}', '$short_story', '$full_story', '', '$title', '{$metatags['description']}', '{$metatags['keywords']}', '$category_list', '$alt_name', '$allow_comm', '$approve', '$allow_main', '$allow_br')" );

			$row = $db->insert_id();
			$db->query( "INSERT INTO " . PREFIX . "_post_extras (news_id, allow_rate, votes, user_id) VALUES('{$row}', '$allow_rating', '0', '{$member_id['user_id']}')" );


			$db->query( "UPDATE " . USERPREFIX . "_users set news_num=news_num+1 where user_id='{$member_id['user_id']}'" );
			$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '1', '{$title}')" );
		
		}
		
		if( $id and $lastdate ) $db->query( "UPDATE " . PREFIX . "_rss SET lastdate='$lastdate' WHERE id='$id'" );
		
		clear_cache();
		msg( "info", $lang['addnews_ok'], $lang['rss_added'], "?mod=rss" );
	
	}
	
	msg( "error", $lang['addnews_error'], $lang['rss_notadded'], "?mod=rss" );

} elseif( $_REQUEST['action'] == "news" and $id ) {
	
	include_once ENGINE_DIR . '/classes/rss.class.php';
	include_once ENGINE_DIR . '/classes/parse.class.php';
	
	$parse = new ParseFilter( Array (), Array (), 1, 1 );
	$parse->leech_mode = true;
	
	$rss = $db->super_query( "SELECT * FROM " . PREFIX . "_rss WHERE id='$id'" );
	
	$xml = new xmlParser( stripslashes( $rss['url'] ), $rss['max_news'] );
	
	$xml->pre_lastdate = $rss['lastdate'];
	
	$xml->pre_parse( $rss['date'] );
	
	$i = 0;

	foreach ( $xml->content as $content ) {
		if( $rss['text_type'] ) {
			$xml->content[$i]['title'] = $parse->decodeBBCodes( $xml->content[$i]['title'], false );
			$xml->content[$i]['description'] = $parse->decodeBBCodes( $xml->content[$i]['description'], false );
			$xml->content[$i]['date'] = date( "Y-m-d H:i:s", $xml->content[$i]['date'] );
		
		} else {
			$xml->content[$i]['title'] = $parse->decodeBBCodes( $xml->content[$i]['title'], false );
			$xml->content[$i]['description'] = $parse->decodeBBCodes( $xml->content[$i]['description'], true, "yes" );
			$xml->content[$i]['date'] = date( "Y-m-d H:i:s", $xml->content[$i]['date'] );
		}
		$i ++;
	}
	
	echoheader( "<i class=\"icon-rss\"></i>".$lang['opt_rss'], $lang['header_rs_1'] );
	
	echo <<<HTML
<script type="text/javascript">

	function doFull( link, news_id, rss_id )
	{

		ShowLoading('');

		$.post('engine/ajax/rss.php', { link: link, news_id: news_id, rss_id: rss_id, rss_charset: "{$xml->rss_charset}" }, function(data){
	
			HideLoading('');
	
			$('#cfull'+ news_id).html(data);
	
		});

	return false;
	}

	function RemoveTable( nummer ) {
	    DLEconfirm( '{$lang['edit_cdel']}', '{$lang['p_confirm']}', function () {
			document.getElementById('ContentTable' + nummer).innerHTML = '';
		} );
	}

	function preview( id )
	{
        dd=window.open('','prv','height=400,width=750,resizable=1,scrollbars=1');
        document.addnews.target='prv';
		document.addnews.title.value = document.getElementById('title_' + id).value;
		document.addnews.short_story.value = document.getElementById('short_' + id).value;
		if (document.getElementById('full_' + id)) {
		document.addnews.full_story.value = document.getElementById('full_' + id).value;
		} else {
		document.addnews.full_story.value = "";
		}
        document.addnews.submit();
    }
</script>
<form method=post name="addnewsrss" action="?mod=rss&action=addnews">
<div class="box">
  <div class="box-header">
    <div class="title">{$rss['url']}</div>
  </div>
  <div class="box-content">
HTML;
	
	$i = 0;
	$categories_list = CategoryNewsSelection( $rss['category'], 0 );
	
	if( count( $xml->content ) ) {
		foreach ( $xml->content as $content ) {
			
			echo '<span id="ContentTable' . $i . '"><table class="table table-normal"><tr><td colspan="2">
    <b><a onclick="RemoveTable(' . $i . '); return false;" href="#" ><i class="icon-trash bigger-130 status-error"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:ShowOrHide(\'cp' . $i . '\',\'cc' . $i . '\')" >' . $content['title'] . '</a></td>
    </tr>
    <tr id=\'cp' . $i . '\' style=\'display:none\'>
    <td width=200 valign="top" style="padding: 5px"><input type="text" size="55" id="title_' . $i . '" name="content[' . $i . '][title]" value="' . $content['title'] . '"><br />
	<br /><input data-rel="calendar" type="text" name="content[' . $i . '][date]" size="20" value="' . $content['date'] . '">
	</td>
    <td valign="top" style="padding: 5px"><select name="content[' . $i . '][category][]" id="category" style="width:100%;max-width:350px;height:100px;" multiple>
    ' . $categories_list . '</select></td>
    </tr>
    <tr id=\'cc' . $i . '\' style=\'display:none\'>
    <td colspan="2">
    <textarea style="width:100%;max-width:950px;height:200px;" id="short_' . $i . '" name="content[' . $i . '][short]">' . $content['description'] . '</textarea>
	<div id="cfull' . $i . '">' . htmlspecialchars( $content['link'], ENT_QUOTES, $config['charset'] ) . '</div>
	<input class="icheck" type="checkbox" name="content[' . $i . '][approve]" id="content[' . $i . '][approve]" value="1" checked><label for="content[' . $i . '][approve]">' . $lang['addnews_mod'] . '</label><br />
	<br /><input onclick="doFull(\'' . urlencode( rtrim( $content['link'] ) ) . '\', \'' . $i . '\', \'' . $rss['id'] . '\')" type="button" class="btn btn-green" value="&nbsp;&nbsp;' . $lang['rss_dofull'] . '&nbsp;&nbsp;">&nbsp;&nbsp;<input onclick="preview(' . $i . ')" type="button" class="btn btn-blue" value="&nbsp;&nbsp;' . $lang['btn_preview'] . '&nbsp;&nbsp;">&nbsp;&nbsp;<input onclick="RemoveTable(' . $i . '); return false;" type="button" class="btn btn-red" value="&nbsp;&nbsp;' . $lang['edit_dnews'] . '&nbsp;&nbsp;"><br /><br />
  </tr></table></span>';
			
			$i ++;
		}
		
		echo <<<HTML
    <div class="box-footer padded"><input type="submit" value=" {$lang['rss_addnews']} " class="btn btn-blue">
&nbsp;&nbsp;<input onclick="document.location='?mod=rss&action=news&subaction=clear&id={$id}&lastdate={$xml->lastdate}'" type="button" value=" {$lang['rss_clear']} " class="btn btn-red">
	<input type="hidden" name="allow_main" value="{$rss['allow_main']}">
	<input type="hidden" name="allow_rating" value="{$rss['allow_rating']}">
	<input type="hidden" name="allow_comm" value="{$rss['allow_comm']}">
	<input type="hidden" name="lastdate" value="{$xml->lastdate}">
	<input type="hidden" name="id" value="{$id}">
	<input type="hidden" name="user_hash" value="$dle_login_hash" />
	<input type="hidden" name="text_type" value="{$rss['text_type']}">
	</div>	
HTML;
	
	} else {
		
		echo "<div style=\"padding:10px;\" align=\"center\">" . $lang['rss_no_rss'] . "<br /><br><a class=\"btn btn-red\" href=\"?mod=rss\">{$lang['func_msg']}</a></div>";
	
	}
	
	echo <<<HTML
   </div>
</div></form>

<form method="post" name="addnews" id="addnews">
<input type="hidden" name="mod" value="preview">
<input type="hidden" name="title" value="">
<input type="hidden" name="short_story" value="">
<input type="hidden" name="full_story" value="">
<input type="hidden" name="allow_br" value="{$rss['text_type']}">
</form>
HTML;
	
	echofooter();

} elseif( $_REQUEST['action'] == "doadd" or $_REQUEST['action'] == "doedit" ) {
	
	$url = $db->safesql( trim( $_REQUEST['rss_url'] ) );
	$description = $db->safesql( trim( $_REQUEST['rss_descr'] ) );
	
	$max_news = intval( $_REQUEST['rss_maxnews'] );
	$allow_main = intval( $_REQUEST['allow_main'] );
	$allow_rating = intval( $_REQUEST['allow_rating'] );
	$allow_comm = intval( $_REQUEST['allow_comm'] );
	$text_type = intval( $_REQUEST['text_type'] );
	$date = intval( $_REQUEST['rss_date'] );
	$category = intval( $_REQUEST['category'] );
	
	$search = $db->safesql( trim( $_REQUEST['rss_search'] ) );
	$cookies = $db->safesql( trim( $_REQUEST['rss_cookie'] ) );
	
	if( $url == "" ) msg( "error", $lang['addnews_error'], $lang['rss_err1'], "javascript:history.go(-1)" );
	
	if( $_REQUEST['action'] == "doadd" ) {
		$db->query( "INSERT INTO " . PREFIX . "_rss (url, description, allow_main, allow_rating, allow_comm, text_type, date, search, max_news, cookie, category) values ('$url', '$description', '$allow_main', '$allow_rating', '$allow_comm', '$text_type', '$date', '$search', '$max_news', '$cookies', '$category')" );
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '51', '{$url}')" );
		msg( "info", $lang['all_info'], $lang['rss_ok1'], "?mod=rss" );
	} else {
		$db->query( "UPDATE " . PREFIX . "_rss set url='$url', description='$description', allow_main='$allow_main', allow_rating='$allow_rating', allow_comm='$allow_comm', text_type='$text_type', date='$date', search='$search', max_news='$max_news', cookie='$cookies', category='$category', lastdate='0' WHERE id='{$id}'" );
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '52', '{$url}')" );
		msg( "info", $lang['all_info'], $lang['rss_ok2'], "?mod=rss" );
	}

} elseif( $_REQUEST['action'] == "add" or $_REQUEST['action'] == "edit" ) {
	
	function makeDropDown($options, $name, $selected) {
		$output = "<select class=\"uniform\" style=\"min-width:100px;\" name=\"$name\">\r\n";
		foreach ( $options as $value => $description ) {
			$output .= "<option value=\"$value\"";
			if( $selected == $value ) {
				$output .= " selected ";
			}
			$output .= ">$description</option>\n";
		}
		$output .= "</select>";
		return $output;
	}
	
	echoheader( "<i class=\"icon-rss\"></i>".$lang['opt_rss'], $lang['header_rs_1'] );;
	
	if( $action == "add" ) {
		
		$rss_date = makeDropDown( array ("1" => $lang['rss_date_1'], "0" => $lang['rss_date_2'] ), "rss_date", "1" );
		$text_type = makeDropDown( array ("1" => "BBCODES", "0" => "HTML" ), "text_type", "1" );
		
		$allow_main = makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "allow_main", "1" );
		$allow_rating = makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "allow_rating", "1" );
		$allow_comm = makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "allow_comm", "1" );
		
		$rss_search_value = "<html>{get}</html>";
		$rss_maxnews_value = 5;
		
		$categories_list = CategoryNewsSelection( 0, 0 );
		$rss_info = $lang['rss_new'];
		$submit_value = $lang['rss_new'];
		$form_action = "?mod=rss&amp;action=doadd";
	
	} else {
		
		$row = $db->super_query( "SELECT * FROM " . PREFIX . "_rss WHERE id='$id'" );
		
		$rss_date = makeDropDown( array ("1" => $lang['rss_date_1'], "0" => $lang['rss_date_2'] ), "rss_date", $row['date'] );
		$text_type = makeDropDown( array ("1" => "BBCODES", "0" => "HTML" ), "text_type", $row['text_type'] );
		
		$allow_main = makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "allow_main", $row['allow_main'] );
		$allow_rating = makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "allow_rating", $row['allow_rating'] );
		$allow_comm = makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "allow_comm", $row['allow_comm'] );
		
		$rss_search_value = htmlspecialchars( stripslashes( $row['search'] ), ENT_QUOTES, $config['charset'] );
		$rss_maxnews_value = $row['max_news'];
		
		$categories_list = CategoryNewsSelection( $row['category'], 0 );
		$rss_info = $row['url'];
		$submit_value = $lang['user_save'];
		$rss_url_value = htmlspecialchars( stripslashes( $row['url'] ), ENT_QUOTES, $config['charset'] );
		$rss_descr_value = htmlspecialchars( stripslashes( $row['description'] ), ENT_QUOTES, $config['charset'] );
		$rss_cookie_value = htmlspecialchars( stripslashes( $row['cookie'] ), ENT_QUOTES, $config['charset'] );
		
		$form_action = "?mod=rss&amp;action=doedit&amp;id=" . $id;
	}
	
	echo <<<HTML
<form action="{$form_action}" method="post" class="form-horizontal">
<div class="box">
  <div class="box-header">
    <div class="title">{$rss_info}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['rss_url']}</label>
		  <div class="col-lg-10">
			<input type="text" style="width:100%;max-width:350px;" name="rss_url" value="{$rss_url_value}">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['rss_hurl']}" >?</span>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['rss_descr']}</label>
		  <div class="col-lg-10">
			<input type="text" style="width:100%;max-width:350px;" name="rss_descr" value="{$rss_descr_value}">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['rss_hdescr']}" >?</span>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['rss_maxnews']}</label>
		  <div class="col-lg-10">
			<input type="text" size="5" name="rss_maxnews" value="{$rss_maxnews_value}">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['rss_hmaxnews']}" >?</span>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['xfield_xcat']}</label>
		  <div class="col-lg-10">
			<select name="category" class="uniform">{$categories_list}</select>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['rss_date']}</label>
		  <div class="col-lg-10">
			{$rss_date}
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['rss_main']}</label>
		  <div class="col-lg-10">
			{$allow_main}
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['rss_rating']}</label>
		  <div class="col-lg-10">
			{$allow_rating}
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['rss_comm']}</label>
		  <div class="col-lg-10">
			{$allow_comm}
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['rss_text_type']}</label>
		  <div class="col-lg-10">
			{$text_type}
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['rss_search']}</label>
		  <div class="col-lg-10">
			<textarea cols="50" rows="5" name="rss_search">{$rss_search_value}</textarea>&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['rss_hsearch']}" >?</span>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['rss_cookie']}</label>
		  <div class="col-lg-10">
			<textarea cols="50" rows="5" name="rss_cookie">{$rss_cookie_value}</textarea>&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['rss_hcookie']}" >?</span>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-lg-2"></label>
		  <div class="col-lg-10">
			<input class="btn btn-green" type="submit" value="{$submit_value}">
		  </div>
		 </div>			 
		 
	</div>
	
   </div>
</div>
</form>
HTML;
	
	echofooter();
	
} else {
	
	if( $_REQUEST['action'] == "del" and $id ) {
		
		if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
			
			die( "Hacking attempt! User not found" );
		
		}
		
		$db->query( "DELETE FROM " . PREFIX . "_rss WHERE id = '$id'" );
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '50', '{$id}')" );

	}
	
	echoheader( "<i class=\"icon-rss\"></i>".$lang['opt_rss'], $lang['header_rs_1'] );
	
	$db->query( "SELECT id, url, description FROM " . PREFIX . "_rss ORDER BY id DESC" );
	
	while ( $row = $db->get_row() ) {
		$row['description'] = stripslashes( $row['description'] );

		$menu_link = <<<HTML
        <div class="btn-group">
          <button class="btn btn-default dropdown-toggle" data-toggle="dropdown"><i class="icon-cog"></i> {$lang['filter_action']} <span class="caret"></span></button>
          <ul class="dropdown-menu text-left">
            <li><a href="?mod=rss&action=news&id={$row['id']}"><i class="icon-download"></i> {$lang['rss_news']}</a></li>
            <li><a href="?mod=rss&action=edit&id={$row['id']}"><i class="icon-pencil"></i> {$lang['rss_edit']}</a></li>
			<li class="divider"></li>
            <li><a href="?mod=rss&action=del&user_hash={$dle_login_hash}&id={$row['id']}"><i class="icon-trash"></i> {$lang['rss_del']}</a></li>
          </ul>
        </div>
HTML;
		
		$entries .= "
    <tr>
    <td><b>{$row['id']}</b></td>
    <td style=\"word-break: break-all;\">{$row['url']}</td>
    <td>{$row['description']}</td>
    <td>{$menu_link}</td>
     </tr>";
	}
	$db->free();

	echo <<<HTML
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['rss_list']}</div>
  </div>
  <div class="box-content">

    <table class="table table-normal table-hover">
      <thead>
      <tr>
        <td style="width: 60px">ID</td>
        <td>{$lang['rss_url']}</td>
        <td>{$lang['rss_descr']}</td>
        <td style="width: 200px">{$lang['vote_action']}</td>
      </tr>
      </thead>
	  <tbody>
		{$entries}
	  </tbody>
	</table>
	
   </div>
   	<div class="box-footer padded">
		<input onclick="document.location='?mod=rss&action=add'" type="button" class="btn btn-blue" value=" {$lang['rss_new']} ">
	</div>	
</div>	
HTML;
	
	echofooter();
}
?>