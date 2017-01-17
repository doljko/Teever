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
 Файл: static.php
-----------------------------------------------------
 Назначение: редактирование статистических страниц
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_static'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

include_once ENGINE_DIR . '/classes/parse.class.php';

$parse = new ParseFilter( Array (), Array (), 1, 1 );

function SelectSkin($skin) {
	global $lang;
	
	$templates_list = array ();
	
	$handle = opendir( './templates' );
	
	while ( false !== ($file = readdir( $handle )) ) {
		if( is_dir( "./templates/$file" ) and ($file != "." and $file != "..") ) {
			$templates_list[] = $file;
		}
	}
	closedir( $handle );
	
	$skin_list = "<select class=\"uniform\" name=\"skin_name\">";
	$skin_list .= "<option value=\"\">" . $lang['cat_skin_sel'] . "</option>";
	
	foreach ( $templates_list as $single_template ) {
		if( $single_template == $skin ) $selected = " selected";
		else $selected = "";
		$skin_list .= "<option value=\"$single_template\"" . $selected . ">$single_template</option>";
	}
	$skin_list .= '</select>';
	
	return $skin_list;
}

if( !$action ) $action = "list";

if( $action == "list" ) {
	$_SESSION['admin_referrer'] = $_SERVER['REQUEST_URI'];

	echoheader( "<i class=\"icon-file\"></i>".$lang['opt_sm_static'], $lang['header_st_1'] );
	
	$search_field = $db->safesql( trim( htmlspecialchars( stripslashes( @urldecode( $_GET['search_field'] ) ), ENT_QUOTES, $config['charset'] ) ) );
	if ($_GET['fromnewsdate']) $fromnewsdate = strtotime( $_GET['fromnewsdate'] ); else $fromnewsdate = "";
	if ($_GET['tonewsdate']) $tonewsdate = strtotime( $_GET['tonewsdate'] ); else $tonewsdate = "";


	if ($fromnewsdate === -1 OR !$fromnewsdate) $fromnewsdate = "";
	if ($tonewsdate === -1 OR !$tonewsdate)   $tonewsdate = "";
	
	$start_from = intval( $_GET['start_from'] );
	$news_per_page = intval( $_GET['news_per_page'] );
	$gopage = intval( $_REQUEST['gopage'] );

	if( ! $news_per_page or $news_per_page < 1 ) {
		$news_per_page = 50;
	}
	if( $gopage ) $start_from = ($gopage - 1) * $news_per_page;
	
	if( $start_from < 0 ) $start_from = 0;

	$where = array ();
	$where[] = "name != 'dle-rules-page'";
	
	if( $search_field != "" ) {
		
		$where[] = "(template like '%$search_field%' OR descr like '%$search_field%')";
	
	}
	
	if( $fromnewsdate != "" ) {
		
		$where[] = "date >= '$fromnewsdate'";
	
	}
	
	if( $tonewsdate != "" ) {
		
		$where[] = "date <= '$tonewsdate'";
	
	}
	
	if( count( $where ) ) {
		
		$where = implode( " AND ", $where );
		$where = " WHERE " . $where;
	
	} else {
		$where = "";
	}
	
	$order_by = array ();
	
	if( $_REQUEST['search_order_t'] == "asc" or $_REQUEST['search_order_t'] == "desc" ) $search_order_t = $_REQUEST['search_order_t'];
	else $search_order_t = "";
	if( $_REQUEST['search_order_d'] == "asc" or $_REQUEST['search_order_d'] == "desc" ) $search_order_d = $_REQUEST['search_order_d'];
	else $search_order_d = "";
	
	if( ! empty( $search_order_t ) ) {
		$order_by[] = "name $search_order_t";
	}
	if( ! empty( $search_order_d ) ) {
		$order_by[] = "date $search_order_d";
	}
	
	$order_by = implode( ", ", $order_by );
	if( ! $order_by ) $order_by = "date desc";
	
	$search_order_date = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( isset( $_REQUEST['search_order_d'] ) ) {
		$search_order_date[$search_order_d] = 'selected';
	} else {
		$search_order_date['desc'] = 'selected';
	}
	$search_order_title = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( ! empty( $search_order_t ) ) {
		$search_order_title[$search_order_t] = 'selected';
	} else {
		$search_order_title['----'] = 'selected';
	}

	$db->query( "SELECT id, name, descr, template, views, date FROM " . PREFIX . "_static" . $where . " ORDER BY " . $order_by . " LIMIT $start_from,$news_per_page" );

	// Prelist Entries

	if( $start_from == "0" ) {
		$start_from = "";
	}
	$i = $start_from;
	$entries_showed = 0;
	
	$entries = "";
	
	while ( $row = $db->get_array() ) {

		$i ++;
		
		$itemdate = @date( "d.m.Y H:i", $row['date'] );
		
		$title = htmlspecialchars( stripslashes( $row['name'] ), ENT_QUOTES, $config['charset'] );
		$descr = stripslashes($row['descr']);
		if( $config['allow_alt_url'] ) $vlink = $config['http_home_url'] . $row['name'] . ".html";
		else $vlink = $config['http_home_url'] . "index.php?do=static&page=" . $row['name'];

		$entries .= "<tr>

        <td>
        $itemdate - <a title=\"{$lang[static_view]}\" class=\"tip status-info\" href=\"{$vlink}\" target=\"_blank\">$title</a></td>
        <td><a title=\"{$lang[edit_static_act]}\" class=\"tip status-info\" href=\"?mod=static&action=doedit&id={$row['id']}\">$descr</a></td>
        <td align=center>{$row['views']}</td>
        <td align=center><input name=\"selected_news[]\" value=\"{$row['id']}\" type='checkbox' /></td>
        </tr>";

		$entries_showed ++;
		
		if( $i >= $news_per_page + $start_from ) {
			break;
		}
	}
	
	// End prelisting
	$result_count = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_static" . $where );
	
	$all_count_news = $result_count['count'];
	if ( $fromnewsdate ) $fromnewsdate = date("Y-m-d", $fromnewsdate );
	if ( $tonewsdate ) $tonewsdate = date("Y-m-d", $tonewsdate );

	
	///////////////////////////////////////////
	// Options Bar
	echo <<<HTML
<script language="javascript">
    function search_submit(prm){
      document.optionsbar.start_from.value=prm;
      document.optionsbar.submit();
      return false;
    }
    function gopage_submit(prm){
      document.optionsbar.start_from.value= (prm - 1) * {$news_per_page};
      document.optionsbar.submit();
      return false;
    }
    </script>
<div style="padding-top:5px;padding-bottom:2px;display:none" name="advancedsearch" id="advancedsearch">
<form action="?mod=static&amp;action=list" method="GET" name="optionsbar" id="optionsbar" class="form-horizontal">
<input type="hidden" name="mod" value="static">
<input type="hidden" name="action" value="list">
<input type="hidden" name="start_from" id="start_from" value="{$start_from}">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['edit_stat']}&nbsp;<b>{$entries_showed}</b>&nbsp;&nbsp;&nbsp;{$lang['edit_stat_1']}&nbsp;<b>{$all_count_news}</b></div>
  </div>
  <div class="box-content">

	<div class="row box-section">

	  <div class="col-md-6">
	  
		<div class="form-group">
		  <label class="control-label col-lg-3">{$lang['edit_search_static']}</label>
		  <div class="col-lg-9">
			<input name="search_field" value="{$search_field}" type="text" style="width:100%">
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-3">{$lang['static_per_page']}</label>
		  <div class="col-lg-9">
			<input style="text-align: center" name="news_per_page" value="{$news_per_page}" type="text" size="10">
		  </div>
		 </div>
		 
	  </div>
	  
	  <div class="col-md-6">

	  	<div class="form-group">
		  <label class="control-label col-lg-3">{$lang['search_by_date']}</label>
		  <div class="col-lg-9">
			{$lang['edit_fdate']} <input data-rel="calendardate" type="text" name="fromnewsdate" id="fromnewsdate" size="13" maxlength="16" value="{$fromnewsdate}">
			{$lang['edit_tdate']} <input data-rel="calendardate" type="text" name="tonewsdate" id="tonewsdate" size="13" maxlength="16" value="{$tonewsdate}">
		  </div>
		 </div>

	  </div>
	  
	</div>
	
	<div class="row box-section">
	{$lang['static_order']}
	</div>	

	<div class="row box-section">
	{$lang['edit_et']} <select class="uniform" name="search_order_t" id="search_order_t">
           <option {$search_order_title['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_title['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_title['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
	{$lang['search_by_date']} <select class="uniform" name="search_order_d" id="search_order_d">
           <option {$search_order_date['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_date['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_date['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
	</div>
	<div class="row box-section">
	<input onclick="javascript:search_submit(0); return(false);" class="btn btn-gray" type="submit" value="{$lang['edit_act_1']}">
	</div>	
	
   </div>
</div>
</form>
</div>
HTML;
	// End Options Bar
	

	echo <<<JSCRIPT
<script language='JavaScript' type="text/javascript">
<!--
function ckeck_uncheck_all() {
    var frm = document.static;
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
-->
</script>
JSCRIPT;
	
	if( $entries_showed == 0 ) {
		
		echo <<<HTML
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['static_head']}</div>
	<ul class="box-toolbar">
      <li class="toolbar-link">
          <a href="javascript:ShowOrHide('advancedsearch');"><i class="icon-search"></i> {$lang['static_advanced_search']}</a>
      </li>
    </ul>
  </div>
  <div class="box-content">
	<div class="row box-section">
		<br /><br /><center>{$lang['edit_nostatic']}</center><br /><br />
	</div>
	<div class="row box-section">
		<input type="button" value="{$lang['static_new']}" class="btn btn-blue" onclick="document.location='?mod=static&action=addnew'">
	</div>
  </div>
</div>
HTML;
	
	} else {

		// pagination
		$npp_nav = "";
			
		if( $all_count_news > $news_per_page ) {
			
			if( $start_from > 0 ) {
				$previous = $start_from - $news_per_page;
				$npp_nav .= "<li><a onclick=\"javascript:search_submit($previous); return(false);\" href=\"#\" title=\"{$lang['edit_prev']}\"><i class=\"icon-backward\"></i></a></li>";
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
				$npp_nav .= "<li><a onclick=\"javascript:search_submit($i); return(false);\" href=\"#\" title=\"{$lang['edit_next']}\"><i class=\"icon-forward\"></i></a></li>";
			}
			
			$npp_nav = "<ul class=\"pagination pagination-sm\">".$npp_nav."</ul>";
		
		}
		
		// pagination
	
		echo <<<HTML
<form action="" method="post" name="static">
<input type="hidden" name="mod" value="mass_static_actions">
<input type="hidden" name="user_hash" value="$dle_login_hash" />
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['static_head']}</div>
	<ul class="box-toolbar">
      <li class="toolbar-link">
          <a href="javascript:ShowOrHide('advancedsearch');"><i class="icon-search"></i> {$lang['static_advanced_search']}</a>
      </li>
    </ul>
  </div>
  <div class="box-content">
  
    <table class="table table-normal table-hover">
      <thead>
      <tr>
        <td style="width: 400px">{$lang['static_title']}</td>
        <td>{$lang['static_descr']}</td>
        <td style="width: 140px">{$lang['st_views']}</td>
        <td style="width: 40px"><input type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all()"></td>
      </tr>
      </thead>
	  <tbody>
		{$entries}
	  </tbody>
	</table>
		  
  </div>
  
<div class="box-footer padded">
          <div class="pull-left">{$npp_nav}</div>
		  <div class="pull-right"><input type="button" value="{$lang['static_new']}" class="btn btn-blue" onclick="document.location='?mod=static&action=addnew'"> <select name="action" class="uniform">
<option value="">{$lang['edit_selact']}</option>
<option value="mass_date">{$lang['mass_edit_date']}</option>
<option value="mass_clear_count">{$lang['mass_clear_count']}</option>
<option value="mass_delete">{$lang['edit_seldel']}</option>
</select>
<input class="btn btn-gold" type="submit" value="{$lang['b_start']}"></div>
</div>  
  
</div>
</form>
HTML;
	
	}
	
	echofooter();

} elseif( $action == "addnew" ) {

	echoheader( "<i class=\"icon-file\"></i>".$lang['opt_sm_static'], $lang['header_st_1'] );
	
	echo "
    <SCRIPT LANGUAGE=\"JavaScript\">
    function preview(){";
	
	if( $config['allow_static_wysiwyg'] == 1 ) {
		echo "submit_all_data();";
	}

	if( $config['allow_static_wysiwyg'] == 2 ) {
		echo "tinyMCE.triggerSave();";
	}
	
	echo "if(document.static.template.value == '' || document.static.description.value == '' || document.static.name.value == ''){ Growl.info({ title: '{$lang[p_info]}', text: '{$lang['static_err_1']}'}); }
    else{
        dd=window.open('','prv','height=400,width=750,resizable=1,scrollbars=1')
        document.static.mod.value='preview';document.static.target='prv'
        document.static.submit(); dd.focus()
        setTimeout(\"document.static.mod.value='static';document.static.target='_self'\",500)
    }
    }
    onload=focus;function focus(){document.forms[0].name.focus();}

	function auto_keywords ( key )
	{

		var wysiwyg = '{$config['allow_static_wysiwyg']}';

		if (wysiwyg == \"1\") {
			submit_all_data();
		}

		if (wysiwyg == \"2\") {
			tinyMCE.triggerSave();
		}

		var short_txt = document.getElementById('template').value;

		ShowLoading('');

		$.post(\"engine/ajax/keywords.php\", { short_txt: short_txt, key: key }, function(data){
	
			HideLoading('');
	
			if (key == 1) { $('#autodescr').val(data); }
			else { $('#keywords').tokenfield('setTokens', data); }
	
		});

		return false;
	}
    </SCRIPT>";

	if( !$config['allow_static_wysiwyg'] ) $fix_br = "<input class=\"icheck\" type=\"radio\" name=\"allow_br\" id=\"allow_br\" value=\"1\" checked=\"checked\" /><label for=\"allow_br\">{$lang['static_br_html']}</label><br /><input class=\"icheck\" type=\"radio\" name=\"allow_br\" id=\"allow_br1\" value=\"0\" /><label for=\"allow_br1\">{$lang['static_br_html_1']}</label>";
	else $fix_br = "<input class=\"icheck\" type=\"radio\" name=\"allow_br\" name=\"allow_br\" value=\"0\" /><label for=\"allow_br\">{$lang['static_br_html_1']}</label>";

	if ($member_id['user_group'] == 1 ) $fix_br .= "<br /><input class=\"icheck\" type=\"radio\" name=\"allow_br\" id=\"allow_br2\" value=\"2\" /><label for=\"allow_br2\">{$lang['static_br_html_2']}</label>";

	$groups = get_groups();
	$skinlist = SelectSkin('');
	
	if( $config['allow_static_wysiwyg'] == "2" ) echo "<form class=\"form-horizontal\" method=post name=\"static\" id=\"static\" onsubmit=\"if(document.static.name.value == '' || document.static.description.value == ''){Growl.info({ title: '{$lang[p_info]}', text: '{$lang['static_err_1']}'}); return false}\" action=\"\">";
	else echo "<form class=\"form-horizontal\" method=post name=\"static\" id=\"static\" onsubmit=\"if(document.static.name.value == '' || document.static.description.value == ''){Growl.info({ title: '{$lang[p_info]}', text: '{$lang['static_err_1']}'}); return false}\" action=\"\">";	

	echo <<<HTML
<input type="hidden" name="action" value="dosavenew">
<input type="hidden" name="mod" value="static">
<input type="hidden" name="preview_mode" value="static" >
<input type="hidden" name="user_hash" value="$dle_login_hash" />
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['static_a']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
	
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['static_title']}</label>
		  <div class="col-md-10">
			<input type="text" name="name" size="55">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_stitle']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['static_descr']}</label>
		  <div class="col-md-10">
			<input type="text" name="description" size="55">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_sdesc']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['edit_edate']}</label>
		  <div class="col-md-10">
			<input data-rel="calendar" type="text" name="newdate" size="20" value="">&nbsp;<input type="checkbox" name="allow_now" id="allow_now" value="yes" checked>&nbsp;{$lang['edit_jdate']}
		  </div>
		 </div>
		<div class="form-group editor-group">
		  <label class="control-label col-lg-2">{$lang['static_templ']}</label>
		  <div class="col-lg-10">
HTML;
	
	if( $config['allow_static_wysiwyg'] ) {
		
		include (ENGINE_DIR . '/editor/static.php');
	
	} else {
		
		include (ENGINE_DIR . '/inc/include/inserttag.php');
		
		echo <<<HTML
		{$bb_code}<textarea style="width:100%;max-width: 950px;height:350px;" name="template" id="template" onfocus="setFieldName(this.name)"></textarea><script type=text/javascript>var selField  = "template";</script>
HTML;
	
	}
	
	
	echo <<<HTML
		  </div>
		 </div>
		 
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['static_type']}</label>
		  <div class="col-md-10">
			{$fix_br}
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-md-2"></label>
		  <div class="col-md-10">
			{$lang['add_metatags']}&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_metas']}" >?</span>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['meta_title']}</label>
		  <div class="col-md-10">
			<input type="text" name="meta_title" style="width:100%;max-width:437px;">
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['meta_descr']}</label>
		  <div class="col-md-10">
			<input type="text" name="descr" id="autodescr" style="width:100%;max-width:437px;"> <i class="icon-warning-sign"></i> {$lang['meta_descr_max']}
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['meta_keys']}</label>
		  <div class="col-md-10">
			<textarea class="tags" name="keywords" id='keywords' style="width:400px;height:70px;"></textarea><br /><br />
			<button onclick="auto_keywords(1); return false;" class="btn btn-blue"><i class="icon-exchange"></i> {$lang['btn_descr']}</button>&nbsp;
			<button onclick="auto_keywords(2); return false;" class="btn btn-blue"><i class="icon-exchange"></i> {$lang['btn_keyword']}</button>
		  </div>
		 </div>		 
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['static_tpl']}</label>
		  <div class="col-md-10">
			<input type="text" name="static_tpl" size="20">.tpl&nbsp;<span class="help-button" data-rel="popover" data-html="true" data-trigger="hover" data-placement="right" data-content="{$lang['hint_stpl']}" >?</span>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['static_skin']}</label>
		  <div class="col-md-10">
			{$skinlist}&nbsp;<span class="help-button" data-rel="popover" data-html="true" data-trigger="hover" data-placement="right" data-content="{$lang['hint_static_skin']}" >?</span>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['stat_allow']}</label>
		  <div class="col-md-10">
			<select name="grouplevel[]" style="width:150px;height:93px;" multiple><option value="all" selected>{$lang['edit_all']}</option>{$groups}</select>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-md-2"></label>
		  <div class="col-md-10">
			<input class="icheck" type="checkbox" name="allow_template" id="allow_template" value="1" checked><label for="allow_template">{$lang['st_al_templ']}</label>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-md-2"></label>
		  <div class="col-md-10">
			<input class="icheck" type="checkbox" name="allow_count" id="allow_count" value="1" checked><label for="allow_count">{$lang['allow_count']}</label><br />
			<input class="icheck" type="checkbox" name="allow_sitemap" id="allow_sitemap" value="1" checked><label for="allow_sitemap">{$lang['allow_sitemap']}</label><br />
			<input class="icheck" type="checkbox" name="disable_index" id="disable_index" value="1"><label for="disable_index">{$lang['add_disable_index']}</label>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-md-2"></label>
		  <div class="col-md-10">
			<input type="submit" value="{$lang['user_save']}" class="btn btn-green">&nbsp;&nbsp;&nbsp;<input onclick="preview()" type="button" class="btn btn-blue" value="{$lang['btn_preview']}">
		  </div>
		 </div>			 
		 
	</div>
	
   </div>
</div>
</form>
HTML;
	
	echofooter();
} elseif( $action == "dosavenew" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$allow_br = intval( $_POST['allow_br'] );
	if ($member_id['user_group'] != 1 AND $allow_br > 1 ) $allow_br = 1;

	if ($allow_br == 2) {

		if( function_exists( "get_magic_quotes_gpc" ) && get_magic_quotes_gpc() ) $_POST['template'] = stripslashes( $_POST['template'] );  

		$template = trim( addslashes( $_POST['template'] ) );

	} else {

		if ( $config['allow_static_wysiwyg'] ) $parse->allow_code = false;

		$template = $parse->process( $_POST['template'] );
	
		if( $config['allow_static_wysiwyg'] or $allow_br != '1' ) {
			$template = $parse->BB_Parse( $template );
		} else {
			$template = $parse->BB_Parse( $template, false );
		}

	}

	$disable_index = isset( $_POST['disable_index'] ) ? intval( $_POST['disable_index'] ) : 0;
	$metatags = create_metatags( $template );
	$name = trim( totranslit( $_POST['name'], true, false ) );
	$descr = trim( $db->safesql( htmlspecialchars( $_POST['description'], ENT_QUOTES, $config['charset'] ) ) );
	$template = $db->safesql( $template );

	$tpl = $db->safesql(cleanpath( $_POST['static_tpl'] ));

	$skin_name =  trim( totranslit( $_POST['skin_name'], false, false ) );
	$newdate = $_POST['newdate'];
    if( isset( $_POST['allow_now'] ) ) $allow_now = $_POST['allow_now']; else $allow_now = "";
	
	if( ! count( $_POST['grouplevel'] ) ) $_POST['grouplevel'] = array ("all" );
	$grouplevel = $db->safesql( implode( ',', $_POST['grouplevel'] ) );
	
	$allow_template = intval( $_POST['allow_template'] );
	$allow_count = intval( $_POST['allow_count'] );
	$allow_sitemap = intval( $_POST['allow_sitemap'] );

  // Обработка даты и времени
	$added_time = time();
	$newsdate = strtotime( $newdate );

	if( ($allow_now == "yes") OR ($newsdate === - 1) OR !$newsdate) {
		$thistime = $added_time;
	} else {
		$thistime = $newsdate;
		if( ! intval( $config['no_date'] ) and $newsdate > $added_time ) $thistime = $added_time;
	}
					
	if( $name == "" or $descr == "" or $template == "" ) msg( "error", $lang['static_err'], $lang['static_err_1'], "javascript:history.go(-1)" );

	$static_count = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_static WHERE name='$name'" );

	if ($static_count['count']) msg( "error", $lang['static_err'], $lang['static_err_2'], "javascript:history.go(-1)" );
	
	$db->query( "INSERT INTO " . PREFIX . "_static (name, descr, template, allow_br, allow_template, grouplevel, tpl, metadescr, metakeys, template_folder, date, metatitle, allow_count, sitemap, disable_index) values ('$name', '$descr', '$template', '$allow_br', '$allow_template', '$grouplevel', '$tpl', '{$metatags['description']}', '{$metatags['keywords']}', '{$skin_name}', '{$thistime}', '{$metatags['title']}', '$allow_count', '$allow_sitemap', '$disable_index')" );
	$row = $db->insert_id();
	$db->query( "UPDATE " . PREFIX . "_static_files SET static_id='{$row}' WHERE author = '{$member_id['name']}' AND static_id = '0'" );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '59', '{$name}')" );
	
	msg( "info", $lang['static_addok'], $lang['static_addok_1'], "?mod=static" );

} elseif( $action == "doedit" ) {
	
	$id = intval( $_GET['id'] );
	
	if( $_GET['page'] == "rules" ) {
		
		$row = $db->super_query( "SELECT * FROM " . PREFIX . "_static where name='dle-rules-page'" );
		$lang['static_edit'] = $lang['rules_edit'];
		if( ! $row['id'] ) {
			$id = "";
			$row['allow_template'] = "1";
		} else
			$id = $row['id'];
		
		if( ! $config['registration_rules'] ) $lang['rules_descr'] = $lang['rules_descr'] . " <font color=\"red\">" . $lang['rules_check'] . "</font>";
	
	} else {
		
		$row = $db->super_query( "SELECT * FROM " . PREFIX . "_static where id='$id'" );
	}

	if ($row['allow_br'] == 2) {

		if ($member_id['user_group'] != 1) msg( "error", $lang['index_denied'], $lang['static_not_allowed'] );

		$row['template'] = htmlspecialchars( stripslashes( $row['template'] ), ENT_QUOTES, $config['charset'] );


	} else {
	
		if( $row['allow_br'] != '1' or $config['allow_static_wysiwyg'] ) {
			
			$row['template'] = $parse->decodeBBCodes( $row['template'], true, $config['allow_static_wysiwyg'] );
		
		} else {
			
			$row['template'] = $parse->decodeBBCodes( $row['template'], false );
		
		}
	}
	
	$skinlist = SelectSkin( $row['template_folder'] );
	$row['descr'] = stripslashes($row['descr']);
	$row['metatitle'] = stripslashes( $row['metatitle'] );
	$itemdate = @date( "Y-m-d H:i:s", $row['date'] );
	
	echoheader( "<i class=\"icon-file\"></i>".$lang['opt_sm_static'], $lang['header_st_1'] );
	
	echo <<<HTML
<script language="javascript">

function CheckStatus(Form){
	if(Form.allow_date.checked) {
		Form.allow_now.disabled = true;
		Form.allow_now.checked = false;
	} else {
		Form.allow_now.disabled = false;
	}
}

function confirmdelete(id) {
	    DLEconfirm( '{$lang['static_confirm']}', '{$lang['p_confirm']}', function () {
			document.location="?mod=static&action=dodelete&user_hash={$dle_login_hash}&id="+id;
		} );
}
</script>
HTML;

	echo "
    <SCRIPT LANGUAGE=\"JavaScript\">
    function preview(){";
	
	if( $config['allow_static_wysiwyg'] == 1 ) {
		echo "submit_all_data();";
	}

	if( $config['allow_static_wysiwyg'] == 2 ) {
		echo "tinyMCE.triggerSave();";
	}
	
	echo "if(document.static.template.value == ''){ Growl.info({ title: '{$lang[p_info]}', text: '{$lang['static_err_1']}'}); }
    else{
        dd=window.open('','prv','height=400,width=750,resizable=1,scrollbars=1')
        document.static.mod.value='preview';document.static.target='prv'
        document.static.submit(); dd.focus()
        setTimeout(\"document.static.mod.value='static';document.static.target='_self'\",500)
    }
    }

	function auto_keywords ( key )
	{

		var wysiwyg = '{$config['allow_static_wysiwyg']}';

		if (wysiwyg == \"1\") {
			submit_all_data();
		}

		if (wysiwyg == \"2\") {
			tinyMCE.triggerSave();
		}

		var short_txt = document.getElementById('template').value;

		ShowLoading('');

		$.post(\"engine/ajax/keywords.php\", { short_txt: short_txt, key: key }, function(data){
	
			HideLoading('');
	
			if (key == 1) { $('#autodescr').val(data); }
			else { $('#keywords').tokenfield('setTokens', data); }
	
		});

		return false;
	}
    </SCRIPT>";
	$check = array();

	$check[$row['allow_br']] = "checked=\"checked\"";

	if( !$config['allow_static_wysiwyg'] ) $fix_br = "<input class=\"icheck\" type=\"radio\" name=\"allow_br\" id=\"allow_br\" value=\"1\" {$check[1]} /><label for=\"allow_br\">{$lang['static_br_html']}</label><br /><input class=\"icheck\" type=\"radio\" name=\"allow_br\" id=\"allow_br1\" value=\"0\" {$check[0]} /><label for=\"allow_br1\">{$lang['static_br_html_1']}</label>";
	else $fix_br = "<input class=\"icheck\" type=\"radio\" name=\"allow_br\" id=\"allow_br\" value=\"0\" {$check[0]} /><label for=\"allow_br\">{$lang['static_br_html_1']}</label>";

	if ($member_id['user_group'] == 1 ) $fix_br .= "<br /><input class=\"icheck\" type=\"radio\" name=\"allow_br\" id=\"allow_br2\" value=\"2\" {$check[2]} /><label for=\"allow_br2\">{$lang['static_br_html_2']}</label>";

	if( $row['allow_template'] ) $check_t = "checked";
	else $check_t = "";

	if( $row['allow_count'] ) $check_c = "checked";
	else $check_c = "";

	if( $_GET['page'] != "rules" ) {

		if( $row['sitemap'] ) $allow_sitemap = "<br /><input class=\"icheck\" type=\"checkbox\" name=\"allow_sitemap\" id=\"allow_sitemap\" value=\"1\" checked><label for=\"allow_sitemap\">{$lang['allow_sitemap']}</label>";
		else $allow_sitemap = "<br /><input class=\"icheck\" type=\"checkbox\" name=\"allow_sitemap\" id=\"allow_sitemap\" value=\"1\"><label for=\"allow_sitemap\">{$lang['allow_sitemap']}</label>";

		if( $row['disable_index'] ) $disable_index = "<br /><input class=\"icheck\" type=\"checkbox\" name=\"disable_index\" id=\"disable_index\" value=\"1\" checked><label for=\"disable_index\">{$lang['add_disable_index']}</label>";
		else $disable_index = "<br /><input class=\"icheck\" type=\"checkbox\" name=\"disable_index\" id=\"disable_index\" value=\"1\"><label for=\"disable_index\">{$lang['add_disable_index']}</label>";
	
	} else $allow_sitemap = "";


	$groups = get_groups( explode( ',', $row['grouplevel'] ) );
	if( $row['grouplevel'] == "all" ) $check_all = "selected";
	else $check_all = "";
	
	if( $_GET['page'] == "rules" ) {
		
		echo "<form class=\"form-horizontal\" method=post name=\"static\" id=\"static\" action=\"\">";
	
	} else {
		
		if( $config['allow_static_wysiwyg'] == 2 ) echo "<form class=\"form-horizontal\" method=post name=\"static\" id=\"static\" onsubmit=\"if(document.static.name.value == '' || document.static.description.value == '' ){Growl.info({ title: '{$lang[p_info]}', text: '{$lang['static_err_1']}'}); return false}\" action=\"\">";
		else echo "<form class=\"form-horizontal\" method=post name=\"static\" id=\"static\" onsubmit=\"if(document.static.name.value == '' || document.static.description.value == ''){Growl.info({ title: '{$lang[p_info]}', text: '{$lang['static_err_1']}'}); return false}\" action=\"\">";
	
	}
	
	echo <<<HTML
<input type="hidden" name="action" value="dosaveedit">
<input type="hidden" name="mod" value="static">
<input type="hidden" name="preview_mode" value="static" >
<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
<input type="hidden" name="static_date" value="{$row['date']}" />
<input type="hidden" name="id" value="{$id}">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['static_edit']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
HTML;
	
	if( $_GET['page'] == "rules" ) {
		
		echo <<<HTML
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['static_descr']}</label>
		  <div class="col-md-10">
			<input type="text" name="description" size="55" value="{$row['descr']}">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_sdesc']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-md-2"></label>
		  <div class="col-md-10">
			{$lang['rules_descr']}
		  </div>
		 </div>
HTML;
	
	} else {
		
		echo <<<HTML
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['static_title']}</label>
		  <div class="col-md-10">
			<input type="text" name="name" size="55" value="{$row['name']}">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_stitle']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['static_descr']}</label>
		  <div class="col-md-10">
			<input type="text" name="description" size="55" value="{$row['descr']}">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_sdesc']}" >?</span>
		  </div>
		 </div>
HTML;
	
	}
	
	echo <<<HTML
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['edit_edate']}</label>
		  <div class="col-md-10">
			<input data-rel="calendar" type="text" name="newdate" value="{$itemdate}">&nbsp;<input type="checkbox" name="allow_date" id="allow_date" value="yes" onclick="CheckStatus(static)" checked>&nbsp;{$lang['edit_ndate']}&nbsp;<input type="checkbox" name="allow_now" id="allow_now" value="yes" disabled>&nbsp;{$lang['edit_jdate']}
		  </div>
		 </div>
		<div class="form-group editor-group">
		  <label class="control-label col-lg-2">{$lang['static_templ']}</label>
		  <div class="col-lg-10">
HTML;
	
	if( $config['allow_static_wysiwyg'] ) {
		
		include (ENGINE_DIR . '/editor/static.php');
	
	} else {
		
		include (ENGINE_DIR . '/inc/include/inserttag.php');
		
		echo <<<HTML
		{$bb_code}<textarea style="width:100%;max-width: 950px;height:350px;" name="template" id="template" onfocus="setFieldName(this.name)">{$row['template']}</textarea><script type=text/javascript>var selField  = "template";</script>
HTML;
	
	}
	
	
	echo <<<HTML
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['static_type']}</label>
		  <div class="col-md-10">
			{$fix_br}
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-md-2"></label>
		  <div class="col-md-10">
			{$lang['add_metatags']}&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_metas']}" >?</span>
		  </div>
		 </div>			 
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['meta_title']}</label>
		  <div class="col-md-10">
			<input type="text" name="meta_title" style="width:100%;max-width:437px;" value="{$row['metatitle']}">
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['meta_descr']}</label>
		  <div class="col-md-10">
			<input type="text" name="descr" id="autodescr" style="width:100%;max-width:437px;" value="{$row['metadescr']}"> <i class="icon-warning-sign"></i> {$lang['meta_descr_max']}
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['meta_keys']}</label>
		  <div class="col-md-10">
			<textarea class="tags" name="keywords" id='keywords' style="width:400px;height:70px;">{$row['metakeys']}</textarea><br /><br />
			<button onclick="auto_keywords(1); return false;" class="btn btn-blue"><i class="icon-exchange"></i> {$lang['btn_descr']}</button>&nbsp;
			<button onclick="auto_keywords(2); return false;" class="btn btn-blue"><i class="icon-exchange"></i> {$lang['btn_keyword']}</button>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['static_tpl']}</label>
		  <div class="col-md-10">
			<input type="text" name="static_tpl" size="20" value="{$row['tpl']}">.tpl&nbsp;<span class="help-button" data-rel="popover" data-html="true" data-trigger="hover" data-placement="right" data-content="{$lang['hint_stpl']}" >?</span>
		  </div>
		 </div>
HTML;
	
	if( $_GET['page'] != "rules" ) echo <<<HTML
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['static_skin']}</label>
		  <div class="col-md-10">
			{$skinlist}&nbsp;<span class="help-button" data-rel="popover" data-html="true" data-trigger="hover" data-placement="right" data-content="{$lang['hint_static_skin']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-md-2">{$lang['stat_allow']}</label>
		  <div class="col-md-10">
			<select name="grouplevel[]" style="width:150px;height:93px;" multiple><option value="all" {$check_all}>{$lang['edit_all']}</option>{$groups}</select>
		  </div>
		 </div>
HTML;
	
	echo <<<HTML
		<div class="form-group">
		  <label class="control-label col-md-2"></label>
		  <div class="col-md-10">
			<input class="icheck" type="checkbox" name="allow_template" id="allow_template" value="1" {$check_t}><label for="allow_template">{$lang['st_al_templ']}</label>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-md-2"></label>
		  <div class="col-md-10">
			<input class="icheck" type="checkbox" name="allow_count" id="allow_count" value="1" {$check_c}><label for="allow_count">{$lang['allow_count']}</label>
			{$allow_sitemap}
			{$disable_index}
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-md-2"></label>
		  <div class="col-md-10">
			<input type="submit" value="{$lang['user_save']}" class="btn btn-green">&nbsp;
			<button onclick="preview(); return false;" class="btn btn-gray"><i class="icon-desktop"></i> {$lang['btn_preview']}</button>&nbsp;
			<button onclick="confirmdelete('{$row['id']}'); return false;" class="btn btn-red"><i class="icon-trash"></i> {$lang['edit_dnews']}</button>
		  </div>
		 </div>

	 </div>
	
   </div>
</div>
</form>
HTML;
	
	echofooter();
} elseif( $action == "dosaveedit" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$allow_br = intval( $_POST['allow_br'] );
	if ($member_id['user_group'] != 1 AND $allow_br > 1 ) $allow_br = 1;

	if ($allow_br == 2) {

		if( function_exists( "get_magic_quotes_gpc" ) && get_magic_quotes_gpc() ) $_POST['template'] = stripslashes( $_POST['template'] );  

		$template = trim( addslashes( $_POST['template'] ) );

	} else {

		if ( $config['allow_static_wysiwyg'] ) $parse->allow_code = false;

		$template = $parse->process( $_POST['template'] );
	
		if( $config['allow_static_wysiwyg'] or $allow_br != '1' ) {
			$template = $parse->BB_Parse( $template );
		} else {
			$template = $parse->BB_Parse( $template, false );
		}

	}
	
	$metatags = create_metatags( $template );
	
	if( $_GET['page'] == "rules" ) {
		
		$name = "dle-rules-page";
	
	} else {
		
		$name = trim( totranslit( $_POST['name'], true, false ) );
		
		if( ! count( $_POST['grouplevel'] ) ) $_POST['grouplevel'] = array ("all" );
		$grouplevel = $db->safesql( implode( ',', $_POST['grouplevel'] ) );
	
	}

	$descr = trim( $db->safesql( htmlspecialchars( $_POST['description'], ENT_QUOTES, $config['charset'] ) ) );
	$disable_index = isset( $_POST['disable_index'] ) ? intval( $_POST['disable_index'] ) : 0;	
	$template = $db->safesql( $template );
	$allow_template = intval( $_POST['allow_template'] );
	$allow_count = intval( $_POST['allow_count'] );
	$allow_sitemap = intval( $_POST['allow_sitemap'] );
	$tpl = $db->safesql(cleanpath( $_POST['static_tpl'] ));
	$skin_name =  trim( totranslit( $_POST['skin_name'], false, false ) );
	$newdate = $_POST['newdate'];
	if( isset( $_POST['allow_date'] ) ) $allow_date = $_POST['allow_date']; else $allow_date = "";
	if( isset( $_POST['allow_now'] ) )  $allow_now = $_POST['allow_now']; else $allow_now = "";

	// Обработка даты и времени
	$added_time = time();
	$newsdate = strtotime( $newdate );

	if( $allow_date != "yes" ) {

		if( $allow_now == "yes" ) $thistime = $added_time;
		elseif( ($newsdate === - 1) OR !$newsdate ) {
				$thistime = $added_time;
		} else {

			$thistime = $newsdate;

			if( ! intval( $config['no_date'] ) and $newsdate > $added_time ) {
				$thistime = $added_time;
			}

		}
					
	} else {
		$thistime = intval( $_POST['static_date'] );
	}
	
	if( $_GET['page'] == "rules" ) {
		
		if( $_POST['id'] ) {
			
			$db->query( "UPDATE " . PREFIX . "_static SET descr='$descr', template='$template', allow_br='$allow_br', allow_template='$allow_template', grouplevel='all', tpl='$tpl', metadescr='{$metatags['description']}', metakeys='{$metatags['keywords']}', template_folder='{$skin_name}', date='{$thistime}', metatitle='{$metatags['title']}', allow_count='{$allow_count}', sitemap='0', disable_index='0' WHERE name='dle-rules-page'" );

			$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '60', 'dle-rules-page')" );
		
		} else {
			
			$db->query( "INSERT INTO " . PREFIX . "_static (name, descr, template, allow_br, allow_template, grouplevel, tpl, metadescr, metakeys, template_folder, date, metatitle, allow_count, sitemap, disable_index) values ('$name', '$descr', '$template', '$allow_br', '$allow_template', 'all', '$tpl', '{$metatags['description']}', '{$metatags['keywords']}', '{$skin_name}', '{$thistime}', '{$metatags['title']}', '{$allow_count}', '0', '0')" );
			$row = $db->insert_id();
			$db->query( "UPDATE " . PREFIX . "_static_files SET static_id='{$row}' WHERE author = '{$member_id['name']}' AND static_id = '0'" );
		
		}
		
		msg( "info", $lang['rules_ok'], $lang['rules_ok'], "?mod=static&action=doedit&page=rules" );
	
	} else {
		
		$id = intval( $_GET['id'] );

		if( $name == "" or $descr == "" or $template == "" ) msg( "error", $lang['static_err'], $lang['static_err_1'], "javascript:history.go(-1)" );

		$static_count = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_static WHERE name='$name' AND id != '$id'" );
	
		if ($static_count['count']) msg( "error", $lang['static_err'], $lang['static_err_2'], "javascript:history.go(-1)" );

		$db->query( "UPDATE " . PREFIX . "_static SET name='$name', descr='$descr', template='$template', allow_br='$allow_br', allow_template='$allow_template', grouplevel='$grouplevel', tpl='$tpl', metadescr='{$metatags['description']}', metakeys='{$metatags['keywords']}', template_folder='{$skin_name}', date='{$thistime}', metatitle='{$metatags['title']}', allow_count='{$allow_count}', sitemap='{$allow_sitemap}', disable_index='$disable_index' WHERE id='$id'" );

		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '60', '{$name}')" );
		
		msg( "info", $lang['static_addok'], $lang['static_addok_1'], "?mod=static" );
	
	}
	
	msg( "info", $lang['static_addok'], $lang['static_addok_1'], "?mod=static" );

} elseif( $action == "dodelete" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$id = intval( $_GET['id'] );
	
	$db->query( "DELETE FROM " . PREFIX . "_static WHERE id='$id'" );
	
	$db->query( "SELECT name, onserver FROM " . PREFIX . "_static_files WHERE static_id = '$id'" );
	
	while ( $row = $db->get_row() ) {
		
		if( $row['onserver'] ) {
			
			$url = explode( "/", $row['onserver'] );

			if( count( $url ) == 2 ) {
					
				$folder_prefix = $url[0] . "/";
				$file = $url[1];
				
			} else {
					
				$folder_prefix = "";
				$file = $url[0];
				
			}
			$file = totranslit( $file, false );
	
			if( trim($file) == ".htaccess") die("Hacking attempt!");

			@unlink( ROOT_DIR . "/uploads/files/" . $folder_prefix . $file );
		
		} else {
			
			$url_image = explode( "/", $row['name'] );
			
			if( count( $url_image ) == 2 ) {
				
				$folder_prefix = $url_image[0] . "/";
				$dataimages = $url_image[1];
			
			} else {
				
				$folder_prefix = "";
				$dataimages = $url_image[0];
			
			}
			
			@unlink( ROOT_DIR . "/uploads/posts/" . $folder_prefix . $dataimages );
			@unlink( ROOT_DIR . "/uploads/posts/" . $folder_prefix . "thumbs/" . $dataimages );
			@unlink( ROOT_DIR . "/uploads/posts/" . $folder_prefix . "medium/" . $dataimages );
		}
	
	}
	
	$db->query( "DELETE FROM " . PREFIX . "_static_files WHERE static_id = '$id'" );
	
	msg( "info", $lang['static_del'], $lang['static_del_1'], "?mod=static" );

}
?>