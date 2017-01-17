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
 Файл: links.php
-----------------------------------------------------
 Назначение: управление перекрестными ссылками
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
  die("Hacking attempt!");
}

if( $member_id['user_group'] != 1  ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if (!$config['allow_links']) {

$lang['opt_linkshelp'] .= "<br /><br /><span class=\"note large\"><font color=\"red\">{$lang['module_disabled']}</font></span>";

}

$start_from = intval( $_REQUEST['start_from'] );
$news_per_page = 50;

if( $start_from < 0 ) $start_from = 0;

if ($_REQUEST['searchword']) {
  
  $searchword = htmlspecialchars( strip_tags( stripslashes( trim( urldecode ( $_REQUEST['searchword'] ) ) ) ), ENT_COMPAT, $config['charset'] );
  
} else $searchword = "";

if ($searchword) $urlsearch = "&searchword={$searchword}"; else $urlsearch = "";

if ($_GET['action'] == "delete") {
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$id = intval ( $_GET['id'] );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '95', '')" );
	$db->query( "DELETE FROM " . PREFIX . "_links WHERE id='{$id}'" );

	@unlink( ENGINE_DIR . '/cache/system/links.php' );
	clear_cache();
	header( "Location: ?mod=links&start_from={$start_from}{$urlsearch}" ); die();

}

if ($_POST['action'] == "mass_delete") {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	if( !$_POST['selected_tags'] ) {
		msg( "error", $lang['mass_error'], $lang['mass_links_err'], "?mod=links&start_from={$start_from}" );
	}

	foreach ( $_POST['selected_tags'] as $id ) {
		$id = intval($id);
		$db->query( "DELETE FROM " . PREFIX . "_links WHERE id='{$id}'" );
	}

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '95', '')" );

	@unlink( ENGINE_DIR . '/cache/system/links.php' );
	clear_cache();
	header( "Location: ?mod=links&start_from={$start_from}{$urlsearch}" ); die();

}

if ($_POST['action'] == "mass_r_1" OR $_POST['action'] == "mass_r_2" OR $_POST['action'] == "mass_r_3" OR $_POST['action'] == "mass_r_4") {
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	if( !$_POST['selected_tags'] ) {
		msg( "error", $lang['mass_error'], $lang['mass_links_err'], "?mod=links&start_from={$start_from}" );
	}

	$replacearea = 1;
	if( $_POST['action'] == "mass_r_2" ) $replacearea = 2; elseif( $_POST['action'] == "mass_r_3" ) $replacearea = 3; elseif( $_POST['action'] == "mass_r_4" ) $replacearea = 4;

	foreach ( $_POST['selected_tags'] as $id ) {
		$id = intval($id);
		$db->query( "UPDATE " . PREFIX . "_links SET replacearea='{$replacearea}' WHERE id='{$id}'" );
	}

	@unlink( ENGINE_DIR . '/cache/system/links.php' );
	clear_cache();
	header( "Location: ?mod=links&start_from={$start_from}{$urlsearch}" ); die();
}


if ($_POST['action'] == "mass_r_5" OR $_POST['action'] == "mass_r_6") {
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	if( !$_POST['selected_tags'] ) {
		msg( "error", $lang['mass_error'], $lang['mass_links_err'], "?mod=links&start_from={$start_from}" );
	}

	$onlyone = 0;

	if( $_POST['action'] == "mass_r_5" ) $onlyone = 1;

	foreach ( $_POST['selected_tags'] as $id ) {
		$id = intval($id);
		$db->query( "UPDATE " . PREFIX . "_links SET only_one='{$onlyone}' WHERE id='{$id}'" );
	}

	@unlink( ENGINE_DIR . '/cache/system/links.php' );
	clear_cache();
	header( "Location: ?mod=links&start_from={$start_from}{$urlsearch}" ); die();
}

if ($_POST['action'] == "mass_r_7" OR $_POST['action'] == "mass_r_8") {
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	if( !$_POST['selected_tags'] ) {
		msg( "error", $lang['mass_error'], $lang['mass_links_err'], "?mod=links&start_from={$start_from}" );
	}

	$targetblank = 0;

	if( $_POST['action'] == "mass_r_7" ) $targetblank = 1;

	foreach ( $_POST['selected_tags'] as $id ) {
		$id = intval($id);
		$db->query( "UPDATE " . PREFIX . "_links SET targetblank='{$targetblank}' WHERE id='{$id}'" );
	}

	@unlink( ENGINE_DIR . '/cache/system/links.php' );
	clear_cache();
	header( "Location: ?mod=links&start_from={$start_from}{$urlsearch}" ); die();
}

if ($_GET['action'] == "add") {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$tag = convert_unicode( urldecode ( $_GET['tag'] ), $config['charset']  );
	$url = convert_unicode( $_GET['url'] , $config['charset']  );
	$onlyone = intval ( $_GET['onlyone'] );
	$targetblank = intval ( $_GET['targetblank'] );
	$replacearea = intval ( $_GET['replacearea'] );

	$rcount = intval ( $_GET['rcount'] );

	if($rcount < 1) $rcount = 0;

	$tag = @$db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $tag ) ) ), ENT_COMPAT, $config['charset'] ) );

	$url = @$db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $url ) ) ), ENT_QUOTES, $config['charset'] ) );
	$url = str_ireplace( "document.cookie", "d&#111;cument.cookie", $url );
	$url = preg_replace( "/javascript:/i", "j&#097;vascript:", $url );
	$url = preg_replace( "/data:/i", "d&#097;ta:", $url );

	if (!$tag) msg( "error", $lang['index_denied'], $lang['links_err'], "?mod=links" );

	if (is_numeric($tag)) msg( "error", $lang['index_denied'], $lang['links_err'], "?mod=links" );

	$row = $db->super_query( "SELECT word FROM " . PREFIX . "_links WHERE word ='{$tag}'" );

	if( $row['word'] ) {
		msg( "error", $lang['addnews_error'], $lang['links_err_1'], "?mod=links" );
	}
	

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '93', '{$tag}')" );
	$db->query( "INSERT INTO " . PREFIX . "_links (word, link, only_one, replacearea, rcount, targetblank) values ('{$tag}', '{$url}', '{$onlyone}', '{$replacearea}', '{$rcount}', '{$targetblank}')" );

	@unlink( ENGINE_DIR . '/cache/system/links.php' );
	clear_cache();
	header( "Location: ?mod=links" ); die();
}

if ($_GET['action'] == "edit") {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$tag = convert_unicode( urldecode ( $_GET['tag'] ), $config['charset']  );
	$url = convert_unicode( $_GET['url'] , $config['charset']  );
	$onlyone = intval ( $_GET['onlyone'] );
	$targetblank = intval ( $_GET['targetblank'] );
	$replacearea = intval ( $_GET['replacearea'] );
	$rcount = intval ( $_GET['rcount'] );

	if($rcount < 1) $rcount = 0;

	$tag = @$db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $tag ) ) ), ENT_COMPAT, $config['charset'] ) );
	$url = @$db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $url ) ) ), ENT_QUOTES, $config['charset'] ) );
	$url = str_ireplace( "document.cookie", "d&#111;cument.cookie", $url );
	$url = preg_replace( "/javascript:/i", "j&#097;vascript:", $url );
	$url = preg_replace( "/data:/i", "d&#097;ta:", $url );
	$id = intval ( $_GET['id'] );

	if (!$tag) msg( "error", $lang['index_denied'], $lang['links_err'], "?mod=links&start_from={$start_from}" );

	if (is_numeric($tag)) msg( "error", $lang['index_denied'], $lang['links_err'], "?mod=links" );

	$row = $db->super_query( "SELECT word FROM " . PREFIX . "_links WHERE word = '{$tag}' AND id != '{$id}'" );

	if( $row['word'] ) {
		msg( "error", $lang['index_denied'], $lang['links_err_1'], "?mod=links" );
	}

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '94', '{$tag}')" );
	$db->query( "UPDATE " . PREFIX . "_links SET word='{$tag}', link='{$url}', only_one='{$onlyone}', replacearea='{$replacearea}', rcount='{$rcount}', targetblank='{$targetblank}' WHERE id='{$id}'" );

	@unlink( ENGINE_DIR . '/cache/system/links.php' );
	clear_cache();
	header( "Location: ?mod=links&start_from={$start_from}{$urlsearch}" ); die();
}

echoheader( "<i class=\"icon-link\"></i>".$lang['opt_links'], $lang['header_l_1'] );

echo <<<HTML
<form action="?mod=links" method="get" name="navi" id="navi">
<input type="hidden" name="mod" value="links">
<input type="hidden" name="start_from" id="start_from" value="{$start_from}">
<input type="hidden" name="searchword" value="{$searchword}">
</form>
<form action="?mod=links" method="post" name="optionsbar" id="optionsbar">
<input type="hidden" name="mod" value="links">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
<input type="hidden" name="start_from" id="start_from" value="{$start_from}">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['opt_links']}</div>
	<ul class="box-toolbar">
      <li>
        <label class="input-with-submit">
          <input type="text" name="searchword" placeholder="{$lang['search_field']}" style="width: 230px;" onchange="document.optionsbar.start_from.value=0;" value="{$searchword}">
          <button type="submit" class="submit-icon">
            <i class="icon-search"></i>
          </button>
        </label>
      </li>
    </ul>
  </div>
  <div class="box-content">
HTML;

$i = $start_from+$news_per_page;

if ( $searchword ) {
  
  $searchword = @$db->safesql($searchword);
  $where = "WHERE word like '%$searchword%' OR link like '%$searchword%' ";
  $lang['links_not_found'] = $lang['tags_s_not_found'];
  
} else $where = "";

$result_count = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_links {$where}");
$all_count_news = $result_count['count'];


		// pagination

		$npp_nav = "";
		
		if( $all_count_news > $news_per_page ) {

			if( $start_from > 0 ) {
				$previous = $start_from - $news_per_page;
				$npp_nav .= "<li><a onclick=\"javascript:search_submit($previous); return(false);\" href=\"#\" title=\"{$lang['edit_prev']}\">&lt;&lt;</a></li>";
			}
			
			$enpages_count = @ceil( $all_count_news / $news_per_page );
			$enpages_start_from = 0;
			$enpages = "";
			
			if( $enpages_count <= 10 ) {
				
				for($j = 1; $j <= $enpages_count; $j ++) {
					
					if( $enpages_start_from != $start_from ) {
						
						$enpages .= "<li><a onclick=\"javascript:search_submit($enpages_start_from); return(false);\" href=\"#\">$j</a></li>";
					
					} else {
						
						$enpages .= "<li class=\"active\"><span>$j</span></li>";
					}
					
					$enpages_start_from += $news_per_page;
				}
				
				$npp_nav .= $enpages;
			
			} else {
				
				$start = 1;
				$end = 10;
				
				if( $start_from > 0 ) {
					
					if( ($start_from / $news_per_page) > 4 ) {
						
						$start = @ceil( $start_from / $news_per_page ) - 3;
						$end = $start + 9;
						
						if( $end > $enpages_count ) {
							$start = $enpages_count - 10;
							$end = $enpages_count - 1;
						}
						
						$enpages_start_from = ($start - 1) * $news_per_page;
					
					}
				
				}
				
				if( $start > 2 ) {
					
					$enpages .= "<li><a onclick=\"javascript:search_submit(0); return(false);\" href=\"#\">1</a></li> <li><span>...</span></li>";
				
				}
				
				for($j = $start; $j <= $end; $j ++) {
					
					if( $enpages_start_from != $start_from ) {
						
						$enpages .= "<li><a onclick=\"javascript:search_submit($enpages_start_from); return(false);\" href=\"#\">$j</a></li>";
					
					} else {
						
						$enpages .= "<li class=\"active\"><span>$j</span></li>";
					}
					
					$enpages_start_from += $news_per_page;
				}
				
				$enpages_start_from = ($enpages_count - 1) * $news_per_page;
				$enpages .= "<li><span>...</span></li><li><a onclick=\"javascript:search_submit($enpages_start_from); return(false);\" href=\"#\">$enpages_count</a></li>";
				
				$npp_nav .= $enpages;
			
			}
			
			if( $all_count_news > $i ) {
				$how_next = $all_count_news - $i;
				if( $how_next > $news_per_page ) {
					$how_next = $news_per_page;
				}
				$npp_nav .= "<li><a onclick=\"javascript:search_submit($i); return(false);\" href=\"#\" title=\"{$lang['edit_next']}\">&gt;&gt;</a></li>";
			}
			
			$npp_nav = "<ul class=\"pagination pagination-sm\">".$npp_nav."</ul>";
		
		}
		
		// pagination

$i = 0;

if ( $all_count_news ) {

	$entries = "";

	$db->query("SELECT * FROM " . PREFIX . "_links {$where}ORDER BY id DESC LIMIT {$start_from},{$news_per_page}");

	while($row = $db->get_row()) {
	
		$menu_link = <<<HTML
        <div class="btn-group">
          <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">{$lang['filter_action']} <span class="caret"></span></button>
          <ul class="dropdown-menu text-left">
            <li><a uid="{$row['id']}" href="?mod=links" class="editlink"><i class="icon-pencil"></i> {$lang['word_ledit']}</a></li>
			<li class="divider"></li>
            <li><a uid="{$row['id']}" class="dellink" href="?mod=links"><i class="icon-trash"></i> {$lang['word_ldel']}</a></li>
          </ul>
        </div>
HTML;

		$entries .= "<tr>
        <td><div id=\"content_{$row['id']}\">{$row['word']}</div></td>
        <td><div id=\"url_{$row['id']}\">{$row['link']}</div><input type=\"hidden\" name=\"rcount_{$row['id']}\" id=\"rcount_{$row['id']}\" value=\"{$row['rcount']}\" /><input type=\"hidden\" name=\"only_one_{$row['id']}\" id=\"only_one_{$row['id']}\" value=\"{$row['only_one']}\" /><input type=\"hidden\" name=\"targetblank_{$row['id']}\" id=\"targetblank_{$row['id']}\" value=\"{$row['targetblank']}\" /><input type=\"hidden\" name=\"replacearea_{$row['id']}\" id=\"replacearea_{$row['id']}\" value=\"{$row['replacearea']}\" /></td>
        <td align=\"center\">{$menu_link}</td>
        <td align=\"center\"><input name=\"selected_tags[]\" value=\"{$row['id']}\" type=\"checkbox\"></td>
        </tr>";


	}

	$db->free();

echo <<<HTML

    <table class="table table-normal table-hover">
      <thead>
      <tr>
        <td>{$lang['links_tag']}</td>
        <td>{$lang['links_url']}</td>
        <td style="width: 200px">{$lang['user_action']}</td>
        <td style="width: 40px"><input type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all()"></td>
      </tr>
      </thead>
	  <tbody>
		{$entries}
	  </tbody>
	</table>
<div class="box-footer padded">
    <div class="pull-left">{$npp_nav}</div>
	<div class="pull-right">
	<input class="btn btn-green" type="button" onclick="addLink()" value="{$lang['add_links']}">&nbsp;
	<select class="uniform" name="action">
	<option value="">{$lang['edit_selact']}</option>
	<option value="mass_r_1">{$lang['links_m_act']} {$lang['links_area_2']}</option>
	<option value="mass_r_2">{$lang['links_m_act']} {$lang['links_area_3']}</option>
	<option value="mass_r_3">{$lang['links_m_act']} {$lang['links_area_4']}</option>
	<option value="mass_r_4">{$lang['links_m_act']} {$lang['links_area_5']}</option>
	<option value="mass_r_7">{$lang['links_m_act']} {$lang['links_area_6']}</option>
	<option value="mass_r_8">{$lang['links_m_act']} {$lang['links_area_7']}</option>
	<option value="mass_r_5">{$lang['links_m_act_1']} {$lang['links_m_act_2']}</option>
	<option value="mass_r_6">{$lang['links_m_act_1']} {$lang['links_m_act_3']}</option>
	<option value="mass_delete">{$lang['edit_seldel']}</option>
	</select>&nbsp;<input class="btn btn-gold" type="submit" value="{$lang['b_start']}">
	</div>
</div>
HTML;


}  else {

echo <<<HTML
<div class="row box-section">
<table width="100%">
    <tr>
        <td style="height:50px;"><div align="center"><br /><br />{$lang['links_not_found']}<br /><br></a></div><input class="btn btn-green" type="button" onclick="addLink()" value="{$lang['add_links']}"></td>
    </tr>
</table>
</div>
HTML;

}

echo <<<HTML
   </div>
</div>
</form>


<div class="well relative"><span class="triangle-button green"><i class="icon-bell"></i></span>{$lang['opt_linkshelp']}</div>
<script language="javascript" type="text/javascript">  
<!-- 
    function search_submit(prm){
      document.navi.start_from.value=prm;
      document.navi.submit();
      return false;
    }

	function ckeck_uncheck_all() {
	    var frm = document.optionsbar;
	    for (var i=0;i<frm.elements.length;i++) {
	        var elmnt = frm.elements[i];
	        if (elmnt.type=='checkbox') {
	            if(frm.master_box.checked == true){ elmnt.checked=false; }
	            else{ elmnt.checked=true; }
	        }
	    }
	    if(frm.master_box.checked == true){ frm.master_box.checked = false; }
	    else{ frm.master_box.checked = true; }
	}
	function addLink() {
		var b = {};
	
		b[dle_act_lang[3]] = function() { 
						$(this).dialog("close");						
				    };
	
		b[dle_act_lang[2]] = function() { 
						if ( $("#dle-promt-tag").val().length < 1) {
							 $("#dle-promt-tag").addClass('ui-state-error');
						} else if ( $("#dle-promt-url").val().length < 1 ) {
							 $("#dle-promt-tag").removeClass('ui-state-error');
							 $("#dle-promt-url").addClass('ui-state-error');
						} else {
							var tag = $("#dle-promt-tag").val();
							var url = $("#dle-promt-url").val();
							var rcount = $("#dle-rcount").val();

							if ( $("#only-one").prop( "checked" ) ) { var onlyone = "1"; } else { var onlyone = "0"; }
							if ( $("#targetblank").prop( "checked" ) ) { var targetblank = "1"; } else { var targetblank = "0"; }

							var replacearea = $("#replacearea").val();

							$(this).dialog("close");
							$("#dlepopup").remove();

							document.location='?mod=links&user_hash={$dle_login_hash}&action=add&tag=' + encodeURIComponent(tag) + '&url=' + encodeURIComponent(url)+ '&onlyone=' + onlyone + '&targetblank=' + targetblank + '&rcount=' + rcount +'&replacearea='+replacearea;

						}				
					};

		$("#dlepopup").remove();

		$("body").append("<div id='dlepopup' title='{$lang['add_links_new']}' style='display:none'><br />{$lang['add_links_tag']}<br /><input type='text' name='dle-promt-tag' id='dle-promt-tag' class='ui-widget-content ui-corner-all' style='width:97%; padding: .4em;' value=''/><br /><br />{$lang['add_links_url']}<br /><input type='text' name='dle-promt-url' id='dle-promt-url' class='ui-widget-content ui-corner-all' style='width:97%; padding: .4em;' value='http://'/><br /><br />{$lang['links_rcount']} <input type='text' name='dle-rcount' id='dle-rcount' class='ui-widget-content ui-corner-all' style='width:50px; padding: .4em;' value='0'/> {$lang['links_rcount_1']}<br /><br />{$lang['links_area_1']} <select name='replacearea' id='replacearea' class='ui-widget-content ui-corner-all'><option value='1'>{$lang['links_area_2']}</option><option value='2'>{$lang['links_area_3']}</option><option value='3'>{$lang['links_area_4']}</option><option value='4'>{$lang['links_area_5']}</option></select><br /><br /><input type='checkbox' name='only-one' id='only-one' value=''><label for='only-one'>&nbsp;{$lang['add_links_one']}</label>&nbsp;&nbsp;&nbsp;<input type='checkbox' name='targetblank' id='targetblank' value=''><label for='targetblank'>&nbsp;{$lang['links_target']}</label></div>");
	
		$('#dlepopup').dialog({
			autoOpen: true,
			width: 500,
			resizable: false,
			buttons: b
		});

	}

$(function(){

		var tag_name = '';

		$('.dellink').click(function(){

			tag_name = $('#content_'+$(this).attr('uid')).text();
			var urlid = $(this).attr('uid');

		    DLEconfirm( '{$lang['tagscloud_del']} <b>&laquo;'+tag_name+'&raquo;</b> {$lang['tagscloud_del_2']}', '{$lang['p_confirm']}', function () {

				document.location="?mod=links&start_from={$start_from}&user_hash={$dle_login_hash}{$urlsearch}&action=delete&id=" + urlid;

			} );

			return false;
		});


		$('.editlink').click(function(){

			var tag = $('#content_'+$(this).attr('uid')).text();
			var url = $('#url_'+$(this).attr('uid')).text();
			var onlyone = $('#only_one_'+$(this).attr('uid')).val();
			var targetblank = $('#targetblank_'+$(this).attr('uid')).val();

			var rcount = $('#rcount_'+$(this).attr('uid')).val();
			var replacearea = $('#replacearea_'+$(this).attr('uid')).val();
			var urlid = $(this).attr('uid');

			var b = {};
		
			b[dle_act_lang[3]] = function() { 
							$(this).dialog("close");						
					    };
		
			b[dle_act_lang[2]] = function() { 
							if ( $("#dle-promt-tag").val().length < 1) {
								 $("#dle-promt-tag").addClass('ui-state-error');
							} else if ( $("#dle-promt-url").val().length < 1 ) {
								 $("#dle-promt-tag").removeClass('ui-state-error');
								 $("#dle-promt-url").addClass('ui-state-error');
							} else {
								var tag = $("#dle-promt-tag").val();
								var url = $("#dle-promt-url").val();
								var replacearea = $("#replacearea").val();
								var rcount = $("#dle-rcount").val();
	
								if ( $("#only-one").prop( "checked" ) ) { var onlyone = "1"; } else { var onlyone = "0"; }
								if ( $("#targetblank").prop( "checked" ) ) { var targetblank = "1"; } else { var targetblank = "0"; }
							
								$(this).dialog("close");
								$("#dlepopup").remove();
	
								document.location="?mod=links&start_from={$start_from}&user_hash={$dle_login_hash}{$urlsearch}&action=edit&tag=" + encodeURIComponent(tag) + '&url=' + encodeURIComponent(url)+ '&onlyone=' + onlyone + '&targetblank=' + targetblank + '&rcount=' + rcount + '&replacearea='+replacearea+'&id=' + urlid;
	
							}				
						};
	
			$("#dlepopup").remove();

			$("body").append("<div id='dlepopup' title='{$lang['add_links_new']}' style='display:none'><br />{$lang['add_links_tag']}<br /><input type='text' name='dle-promt-tag' id='dle-promt-tag' class='ui-widget-content ui-corner-all' style='width:97%; padding: .4em;' value=\""+tag+"\"/><br /><br />{$lang['add_links_url']}<br /><input type='text' name='dle-promt-url' id='dle-promt-url' class='ui-widget-content ui-corner-all' style='width:97%; padding: .4em;' value='"+url+"'/><br /><br />{$lang['links_rcount']} <input type='text' name='dle-rcount' id='dle-rcount' class='ui-widget-content ui-corner-all' style='width:50px; padding: .4em;' value='"+rcount+"'/> {$lang['links_rcount_1']}<br /><br />{$lang['links_area_1']} <select name='replacearea' id='replacearea' class='ui-widget-content ui-corner-all'><option value='1'>{$lang['links_area_2']}</option><option value='2'>{$lang['links_area_3']}</option><option value='3'>{$lang['links_area_4']}</option><option value='4'>{$lang['links_area_5']}</option></select><br /><br /><input type='checkbox' name='only-one' id='only-one' value=''><label for='only-one'>&nbsp;{$lang['add_links_one']}</label>&nbsp;&nbsp;&nbsp;<input type='checkbox' name='targetblank' id='targetblank' value=''><label for='targetblank'>&nbsp;{$lang['links_target']}</label><input type='hidden' name='url-id' id='url-id' value='"+urlid+"'></div>");
		
			$('#dlepopup').dialog({
				autoOpen: true,
				width: 500,
				resizable: false,
				buttons: b
			});

			if ( onlyone == 1 ) {  $("#only-one").prop( "checked", "checked" ); }
			if ( targetblank == 1 ) {  $("#targetblank").prop( "checked", "checked" ); }

			$('#replacearea').val(replacearea);

			return false;
		});

});
//-->
</script>
HTML;


echofooter();
?>