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
 Файл: editnews.php
-----------------------------------------------------
 Назначение: редактирование новостей
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_editnews'] ) {
	msg( "error", $lang['addnews_denied'], $lang['edit_denied'] );
}

if( isset( $_REQUEST['author'] ) ) $author = $db->safesql( trim( htmlspecialchars( $_REQUEST['author'], ENT_QUOTES, $config['charset'] ) ) ); else $author = "";
if( isset( $_REQUEST['ifdelete'] ) ) $ifdelete = $_REQUEST['ifdelete']; else $ifdelete = "";
if( isset( $_REQUEST['news_fixed'] ) ) $news_fixed = $_REQUEST['news_fixed']; else $news_fixed = "";
if( isset( $_REQUEST['search_cat'] ) ) $search_cat = intval( $_REQUEST['search_cat'] ); else $search_cat = "";
if ( !$action ) $action = "list";

include_once ENGINE_DIR . '/classes/parse.class.php';

$parse = new ParseFilter( Array (), Array (), 1, 1 );

if( $action == "list" ) {

	$_SESSION['admin_referrer'] = $_SERVER['REQUEST_URI'];

	echoheader( "<i class=\"icon-edit\"></i>".$lang['header_ed_title'], $lang['edit_head'] );

	$search_field = $db->safesql( trim( htmlspecialchars( stripslashes( urldecode( $_REQUEST['search_field'] ) ), ENT_QUOTES, $config['charset'] ) ) );
	$search_author = $db->safesql( trim( htmlspecialchars( stripslashes( urldecode( $_REQUEST['search_author'] ) ), ENT_QUOTES, $config['charset'] ) ) );
	$fromnewsdate = $db->safesql( trim( htmlspecialchars( stripslashes( $_REQUEST['fromnewsdate'] ), ENT_QUOTES, $config['charset'] ) ) );
	$tonewsdate = $db->safesql( trim( htmlspecialchars( stripslashes( $_REQUEST['tonewsdate'] ), ENT_QUOTES, $config['charset'] ) ) );

	$start_from = intval( $_REQUEST['start_from'] );
	$news_per_page = intval( $_REQUEST['news_per_page'] );
	$gopage = intval( $_REQUEST['gopage'] );

	$_REQUEST['news_status'] = intval( $_REQUEST['news_status'] );
	$news_status_sel = array ('0' => '', '1' => '', '2' => '' );
	$news_status_sel[$_REQUEST['news_status']] = 'selected="selected"';

	if( ! $news_per_page or $news_per_page < 1 ) {
		$news_per_page = 50;
	}
	if( $gopage ) $start_from = ($gopage - 1) * $news_per_page;

	if( $start_from < 0 ) $start_from = 0;

	$where = array ();

	if( ! $user_group[$member_id['user_group']]['allow_all_edit'] and $member_id['user_group'] != 1 ) {

		$where[] = "autor = '{$member_id['name']}'";

	}

	if( $search_field != "" ) {

		$where[] = "(short_story like '%$search_field%' OR title like '%$search_field%' OR full_story like '%$search_field%' OR xfields like '%$search_field%')";

	}

	if( $search_author != "" ) {

		$where[] = "autor like '$search_author%'";

	}

	if( $search_cat != "" ) {

		if ($search_cat == -1) $where[] = "category = '' OR category = '0'";
		else $where[] = "category regexp '[[:<:]]($search_cat)[[:>:]]'";

	}

	if( $fromnewsdate != "" ) {

		$where[] = "date >= '$fromnewsdate'";

	}

	if( $tonewsdate != "" ) {

		$where[] = "date <= '$tonewsdate'";

	}

	if( $_REQUEST['news_status'] == 1 ) $where[] = "approve = '1'";
	elseif( $_REQUEST['news_status'] == 2 ) $where[] = "approve = '0'";

	if( count( $where ) ) {

		$where = implode( " AND ", $where );
		$where = " WHERE " . $where;

	} else {
		$where = "";
	}

	$order_by = array ();

	if( $_REQUEST['search_order_f'] == "asc" or $_REQUEST['search_order_f'] == "desc" ) $search_order_f = $_REQUEST['search_order_f'];
	else $search_order_f = "";
	if( $_REQUEST['search_order_m'] == "asc" or $_REQUEST['search_order_m'] == "desc" ) $search_order_m = $_REQUEST['search_order_m'];
	else $search_order_m = "";
	if( $_REQUEST['search_order_d'] == "asc" or $_REQUEST['search_order_d'] == "desc" ) $search_order_d = $_REQUEST['search_order_d'];
	else $search_order_d = "";
	if( $_REQUEST['search_order_t'] == "asc" or $_REQUEST['search_order_t'] == "desc" ) $search_order_t = $_REQUEST['search_order_t'];
	else $search_order_t = "";
	if( $_REQUEST['search_order_c'] == "asc" or $_REQUEST['search_order_c'] == "desc" ) $search_order_c = $_REQUEST['search_order_c'];
	else $search_order_c = "";
	if( $_REQUEST['search_order_v'] == "asc" or $_REQUEST['search_order_v'] == "desc" ) $search_order_v = $_REQUEST['search_order_v'];
	else $search_order_v = "";


	if( ! empty( $search_order_f ) ) {
		$order_by[] = "fixed $search_order_f";
	}
	if( ! empty( $search_order_m ) ) {
		$order_by[] = "approve $search_order_m";
	}
	if( ! empty( $search_order_d ) ) {
		$order_by[] = "date $search_order_d";
	}
	if( ! empty( $search_order_t ) ) {
		$order_by[] = "title $search_order_t";
	}
	if( ! empty( $search_order_c ) ) {
		$order_by[] = "comm_num $search_order_c";
	}
	if( ! empty( $search_order_v ) ) {
		$order_by[] = "news_read $search_order_v";
	}
	$order_by = implode( ", ", $order_by );
	if( ! $order_by ) $order_by = "fixed desc, approve asc, date desc";

	$search_order_fixed = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( isset( $_REQUEST['search_order_f'] ) ) {
		$search_order_fixed[$search_order_f] = 'selected';
	} else {
		$search_order_fixed['desc'] = 'selected';
	}
	$search_order_mod = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( isset( $_REQUEST['search_order_m'] ) ) {
		$search_order_mod[$search_order_m] = 'selected';
	} else {
		$search_order_mod['asc'] = 'selected';
	}
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
	$search_order_comments = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( ! empty( $search_order_c ) ) {
		$search_order_comments[$search_order_c] = 'selected';
	} else {
		$search_order_comments['----'] = 'selected';
	}
	$search_order_view = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( ! empty( $search_order_v ) ) {
		$search_order_view[$search_order_v] = 'selected';
	} else {
		$search_order_view['----'] = 'selected';
	}

	$db->query( "SELECT p.id, p.date, p.title, p.category, p.autor, p.alt_name, p.comm_num, p.approve, p.fixed, e.news_read, e.votes FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) " . $where . " ORDER BY " . $order_by . " LIMIT $start_from,$news_per_page" );

	// Prelist Entries

	if( $start_from == "0" ) {
		$start_from = "";
	}
	$i = $start_from;
	$entries_showed = 0;

	$entries = "";

	while ( $row = $db->get_array() ) {

		$i ++;

		$itemdate = date( "d.m.Y", strtotime( $row['date'] ) );

		$title = $row['title'];

		$title = htmlspecialchars( stripslashes( $title ), ENT_QUOTES, $config['charset'] );
		$title = str_replace("&amp;","&", $title );

		$entries .= "<tr><td><span class=\"news-list-date\">{$itemdate} - </span>";

		if( $row['fixed'] ) $entries .= "<span class=\"badge badge-red\">{$lang['edit_fix']}</span>&nbsp;&nbsp;";

		if( $row['votes'] ) $entries .= "<i class=\"icon-bar-chart\"></i>&nbsp;&nbsp;";

		if( $config['allow_alt_url'] ) {

			if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {

				if( intval( $row['category'] ) and $config['seo_type'] == 2 ) {

					$full_link = $config['http_home_url'] . get_url( intval( $row['category'] ) ) . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";

				} else {

					$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";

				}

			} else {

				$full_link = $config['http_home_url'] . date( 'Y/m/d/', strtotime( $row['date'] ) ) . $row['alt_name'] . ".html";
			}

		} else {

			$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];

		}

		if( $row['comm_num'] > 0 ) {

			$comm_link = <<<HTML
<div class="btn-group">
<a href="{$full_link}" target="_blank" data-toggle="dropdown" data-original-title="{$lang['edit_com']}" class="status-info tip"><b>{$row['comm_num']}</b></a>
  <ul class="dropdown-menu text-left">
   <li><a href="{$full_link}" target="_blank"><i class="icon-eye-open"></i> {$lang['comm_view']}</a></li>
   <li><a href="?mod=comments&action=edit&id={$row['id']}"><i class="icon-pencil"></i> {$lang['vote_edit']}</a></li>
   <li class="divider"></li>
   <li><a onclick="javascript:cdelete('{$row['id']}'); return(false)" href="?mod=comments&user_hash={$dle_login_hash}&action=dodelete&id={$row['id']}"><i class="icon-trash"></i> {$lang['comm_del']}</a></li>
  </ul>
</div>
HTML;

		} else {
			$comm_link = $row['comm_num'];
		}

		$entries .= "<a title='{$lang['edit_act']}' href=\"?mod=editnews&action=editnews&id={$row['id']}\">{$title}</a>
        <td class=\"news-list-tab\" style=\"text-align: center\"><a data-original-title=\"{$lang['st_views']}\" class=\"status-info tip\" href=\"{$full_link}\" target=\"_blank\">{$row['news_read']}</a></td><td class=\"news-list-tab\" align=\"center\">" . $comm_link;

		$entries .= "</td><td style=\"text-align: center\">";

		if( $row['approve'] ) $erlaub = "<span class=\"status-success\"><b><i class=\"icon-ok-sign\"></i></b></span>";
		else $erlaub = "<span class=\"status-error\"><b><i class=\"icon-exclamation-sign\"></i></b></span>";
		$entries .= $erlaub;

		$entries .= "<td class=\"news-list-tab\" style=\"text-align: center\">";

		if( ! $row['category'] ) $my_cat = "---";
		else {

			$my_cat = array ();
			$cat_list = explode( ',', $row['category'] );

			foreach ( $cat_list as $element ) {
				if( $element ) $my_cat[] = $cat[$element];
			}
			$my_cat = implode( ',<br />', $my_cat );
		}

		$entries .= "{$my_cat}<td><a href=\"?mod=editusers&action=list&search=yes&search_name=" . $row['autor'] . "\">" . $row['autor'] . "</a>
               <td style=\"text-align: center\"><input name=\"selected_news[]\" value=\"{$row['id']}\" type=\"checkbox\">
         </tr>";

		$entries_showed ++;

		if( $i >= $news_per_page + $start_from ) {
			break;
		}
	}


	// End prelisting
	$result_count = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post" . $where );

	$all_count_news = $result_count['count'];

	///////////////////////////////////////////
	// Options Bar
	$category_list = CategoryNewsSelection( $search_cat, 0, false );

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
<div style="display:none" name="advancedsearch" id="advancedsearch">
<form class="form-horizontal" action="?mod=editnews&amp;action=list" method="GET" name="optionsbar" id="optionsbar">
<input type="hidden" name="mod" value="editnews">
<input type="hidden" name="action" value="list">
<input type="hidden" name="start_from" id="start_from" value="{$start_from}">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['edit_stat']} {$entries_showed} {$lang['edit_stat_1']} {$all_count_news}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">

	  <div class="col-md-6">

		<div class="form-group">
		  <label class="control-label col-lg-3">{$lang['edit_search_news']}</label>
		  <div class="col-lg-9">
			<input name="search_field" value="{$search_field}" type="text" style="width:100%" >
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-3">{$lang['edit_cat']}</label>
		  <div class="col-lg-9">
			<select class="uniform" name="search_cat" ><option selected value="">{$lang['edit_all']}</option><option value="-1">{$lang['cat_in_none']}</option>{$category_list}</select>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-3">{$lang['search_by_status']}</label>
		  <div class="col-lg-9">
			<select class="uniform" name="news_status" id="news_status">
				<option {$news_status_sel['0']} value="0">{$lang['news_status_all']}</option>
				<option {$news_status_sel['1']} value="1">{$lang['news_status_approve']}</option>
                <option {$news_status_sel['2']} value="2">{$lang['news_status_mod']}</option>
			</select>
		  </div>
		 </div>

	  </div>

	  <div class="col-md-6">

		<div class="form-group">
		  <label class="control-label col-lg-3">{$lang['search_by_author']}</label>
		  <div class="col-lg-9">
			<input name="search_author" value="{$search_author}" type="text" size="36">
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-3">{$lang['search_by_date']}</label>
		  <div class="col-lg-9">
			{$lang['edit_fdate']} <input data-rel="calendar" type="text" name="fromnewsdate" id="fromnewsdate" size="14" maxlength="16" value="{$fromnewsdate}">
 {$lang['edit_tdate']} <input data-rel="calendar" type="text" name="tonewsdate" id="tonewsdate" size="14" maxlength="16" value="{$tonewsdate}">
		  </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-lg-3">{$lang['edit_page']}</label>
		  <div class="col-lg-9">
			<input class="edit bk" style="text-align: center" name="news_per_page" value="{$news_per_page}" type="text" size="36">
		  </div>
		 </div>

	  </div>

	</div>
	<div class="row box-section">
	{$lang['news_order']}
	</div>
	<div class="row box-section">
		<div class="col-md-2 col-xs-6">
		{$lang['news_order_fixed']}<br /><select class="uniform" name="search_order_f" id="search_order_f">
			   <option {$search_order_fixed['----']} value="">{$lang['user_order_no']}</option>
			   <option {$search_order_fixed['asc']} value="asc">{$lang['user_order_plus']}</option>
			   <option {$search_order_fixed['desc']} value="desc">{$lang['user_order_minus']}</option>
				</select>
		</div>
		<div class="col-md-2 col-xs-6">
		{$lang['edit_approve']}<br /><select class="uniform" name="search_order_m" id="search_order_m">
           <option {$search_order_mod['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_mod['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_mod['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
		</div>
		<div class="col-md-2 col-xs-6">
		{$lang['search_by_date']}<br /><select class="uniform" name="search_order_d" id="search_order_d">
           <option {$search_order_date['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_date['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_date['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
		</div>
		<div class="col-md-2 col-xs-6">
		{$lang['edit_et']}<br /><select class="uniform" name="search_order_t" id="search_order_t">
           <option {$search_order_title['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_title['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_title['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
		</div>
		<div class="col-md-2 col-xs-6">
		{$lang['search_by_comment']}<br /><select class="uniform" name="search_order_c" id="search_order_c">
           <option {$search_order_comments['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_comments['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_comments['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
		</div>
		<div class="col-md-2 col-xs-6">
		{$lang['search_by_view']}<br /><select class="uniform" name="search_order_v" id="search_order_v">
           <option {$search_order_view['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_view['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_view['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
		</div>
	</div>
	<div class="row box-section">
	<button onclick="search_submit(0); return(false);" class="btn btn-blue"><i class="icon-search"></i> {$lang['edit_act_1']}</button>
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
    var frm = document.editnews;
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
    <div class="title">{$lang['news_list']}</div>
	<ul class="box-toolbar">
      <li class="toolbar-link">
          <a href="javascript:ShowOrHide('advancedsearch');"><i class="icon-search"></i> {$lang['news_advanced_search']}</a>
      </li>
    </ul>
  </div>
  <div class="box-content">

	<div class="row box-section" style="display: table;min-height:100px;">
	  <div class="col-md-12 text-center" style="display: table-cell;vertical-align:middle;">{$lang['edit_nonews']}</div>
	</div>

   </div>
</div>
HTML;

	} else {

		echo <<<HTML
<script language="javascript" type="text/javascript">
<!--
function cdelete(id){

	    DLEconfirm( '{$lang['db_confirmclear']}', '{$lang['p_confirm']}', function () {
			document.location='?mod=comments&user_hash={$dle_login_hash}&action=dodelete&id=' + id + '';
		} );
}
//-->
</script>
<form action="" method="post" name="editnews">
<input type=hidden name="mod" value="massactions">
<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['news_list']}</div>
	<ul class="box-toolbar">
      <li class="toolbar-link">
          <a href="javascript:ShowOrHide('advancedsearch');"><i class="icon-search"></i> {$lang['news_advanced_search']}</a>
      </li>
    </ul>
  </div>
  <div class="box-content">

    <table class="table table-normal table-hover">
      <thead>
      <tr>
        <td>{$lang['edit_title']}</td>
        <td class="news-list-tab" style="width: 60px"><i class="icon-eye-open tip" data-original-title="{$lang['st_views']}"></i></td>
        <td class="news-list-tab" style="width: 60px"><i class="icon-comment-alt tip" data-original-title="{$lang['edit_com']}"></i></td>
        <td style="width: 60px">{$lang['edit_approve']}</td>
        <td class="news-list-tab">{$lang['edit_cl']}</td>
        <td style="width: 140px">{$lang['edit_autor']}</td>
        <td style="width: 40px"><input type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all();"></td>
      </tr>
      </thead>
	  <tbody>
		{$entries}
	  </tbody>
	</table>

   </div>
<div class="box-footer padded">
          <div class="pull-left">
HTML;

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

			echo "<ul class=\"pagination pagination-sm\">".$npp_nav."</ul>";


		}
// pagination

			echo <<<HTML
          </div>
          <div class="pull-right">
			<select name="action" class="uniform">
				<option value="">{$lang['edit_selact']}</option>
				<option value="mass_move_to_cat">{$lang['edit_selcat']}</option>
				<option value="mass_edit_symbol">{$lang['edit_selsymbol']}</option>
				<option value="mass_edit_author">{$lang['edit_selauthor']}</option>
				<option value="mass_edit_cloud">{$lang['edit_cloud']}</option>
				<option value="mass_date">{$lang['mass_edit_date']}</option>
				<option value="mass_approve">{$lang['mass_edit_app']}</option>
				<option value="mass_not_approve">{$lang['mass_edit_notapp']}</option>
				<option value="mass_fixed">{$lang['mass_edit_fix']}</option>
				<option value="mass_not_fixed">{$lang['mass_edit_notfix']}</option>
				<option value="mass_comments">{$lang['mass_edit_comm']}</option>
				<option value="mass_not_comments">{$lang['mass_edit_notcomm']}</option>
				<option value="mass_rating">{$lang['mass_edit_rate']}</option>
				<option value="mass_not_rating">{$lang['mass_edit_notrate']}</option>
				<option value="mass_main">{$lang['mass_edit_main']}</option>
				<option value="mass_not_main">{$lang['mass_edit_notmain']}</option>
				<option value="mass_clear_count">{$lang['mass_clear_count']}</option>
				<option value="mass_clear_rating">{$lang['mass_clear_rating']}</option>
				<option value="mass_clear_cloud">{$lang['mass_clear_cloud']}</option>
				<option value="mass_delete">{$lang['edit_seldel']}</option>
			</select>&nbsp;<input class="btn btn-gold" type="submit" value="{$lang['b_start']}">
          </div>
</div>
</div>
</form>
HTML;

	}

	echofooter();
}

// ********************************************************************************
// Показ новости и редактирование
// ********************************************************************************
elseif( $action == "editnews" ) {
	$id = intval( $_GET['id'] );
	$row = $db->super_query( "SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE id = '$id'" );

	$found = FALSE;

	if( $id == $row['id'] ) $found = TRUE;
	if( ! $found ) {
		msg( "error", $lang['cat_error'], $lang['edit_nonews'] );
	}

	$cat_list = explode( ',', $row['category'] );

	$have_perm = 0;

	if( $user_group[$member_id['user_group']]['allow_edit'] AND $row['autor'] == $member_id['name'] ) {
		$have_perm = 1;
	}

	if( $user_group[$member_id['user_group']]['allow_all_edit'] ) {
		$have_perm = 1;

		$allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_add'] );

		foreach ( $cat_list as $selected ) {
			if( $allow_list[0] != "all" and !in_array( $selected, $allow_list ) AND $row['approve']) $have_perm = 0;
		}
	}

	if( $user_group[$member_id['user_group']]['max_edit_days'] ) {
		$newstime = strtotime( $row['date'] );
		$maxedittime = $_TIME - ($user_group[$member_id['user_group']]['max_edit_days'] * 3600 * 24);
		if( $maxedittime > $newstime ) $have_perm = 0;
	}
	
	if( ($member_id['user_group'] == 1) ) {
		$have_perm = 1;
	}

	if( ! $have_perm ) {
		msg( "error", $lang['addnews_denied'], $lang['edit_denied'], "?mod=editnews&action=list" );
	}

	$row['title'] = $parse->decodeBBCodes( $row['title'], false );
	$row['title'] = str_replace("&amp;","&", $row['title'] );
	$row['descr'] = $parse->decodeBBCodes( $row['descr'], false );
	$row['keywords'] = $parse->decodeBBCodes( $row['keywords'], false );

	$row['metatitle'] = stripslashes( $row['metatitle'] );

	if( $row['allow_br'] != '1' OR $config['allow_admin_wysiwyg'] ) {
		$row['short_story'] = $parse->decodeBBCodes( $row['short_story'], true, $config['allow_admin_wysiwyg'] );
		$row['full_story'] = $parse->decodeBBCodes( $row['full_story'], true, $config['allow_admin_wysiwyg'] );
	} else {
		$row['short_story'] = $parse->decodeBBCodes( $row['short_story'], false );
		$row['full_story'] = $parse->decodeBBCodes( $row['full_story'], false );
	}

	$access = permload( $row['access'] );

	$poll = array();
	
	if( $row['votes'] ) {
		$poll = $db->super_query( "SELECT * FROM " . PREFIX . "_poll where news_id = '{$row['id']}'" );
		$poll['title'] = $parse->decodeBBCodes( $poll['title'], false );
		$poll['frage'] = $parse->decodeBBCodes( $poll['frage'], false );
		$poll['body'] = $parse->decodeBBCodes( $poll['body'], false );
		$poll['multiple'] = $poll['multiple'] ? "checked" : "";

		if ($user_group[$member_id['user_group']]['allow_all_edit'] AND $poll['votes']) {
			$clear_poll = "<button onclick=\"clearPoll('{$id}'); return false;\" class=\"btn btn-red\"><i class=\"icon-trash\"></i> {$lang['clear_poll']}</button>";
		} else $clear_poll = "";
		
	} else $clear_poll = "";

	$expires = $db->super_query( "SELECT * FROM " . PREFIX . "_post_log where news_id = '{$row['id']}'" );

	if ( $expires['expires'] ) $expires['expires'] = date("Y-m-d", $expires['expires']);

	$js_array[] = "engine/classes/uploads/html5/fileuploader.js";
		
	echoheader( "<i class=\"icon-edit\"></i>".$lang['header_ed_title'], $lang['edit_head'] );

	if ( !$user_group[$member_id['user_group']]['allow_html'] ) $config['allow_admin_wysiwyg'] = 0;

	// Доп. поля
	$xfieldsaction = "categoryfilter";
	include (ENGINE_DIR . '/inc/xfields.php');
	echo $categoryfilter;

	$author = urlencode($row['autor']);

	echo <<<HTML
<script type="text/javascript">
<!-- 
function popupedit( name ){

		var rndval = new Date().getTime();

		$('body').append('<div id="modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #666666; opacity: .40;filter:Alpha(Opacity=40); z-index: 999; display:none;"></div>');
		$('#modal-overlay').css({'filter' : 'alpha(opacity=40)'}).fadeIn('slow');

		$("#dleuserpopup").remove();
		$("body").append("<div id='dleuserpopup' title='{$lang['user_edhead']}' style='display:none'></div>");

		$('#dleuserpopup').dialog({
			autoOpen: true,
			width: 570,
			height: 510,
			resizable: false,
			dialogClass: "modalfixed",
			buttons: {
				"{$lang['user_can']}": function() {
					$(this).dialog("close");
					$("#dleuserpopup").remove();
				},
				"{$lang['user_save']}": function() {
					document.getElementById('edituserframe').contentWindow.document.getElementById('saveuserform').submit();
				}
			},
			open: function(event, ui) {
				$("#dleuserpopup").html("<iframe name='edituserframe' id='edituserframe' width='100%' height='389' src='?mod=editusers&action=edituser&user=" + name + "&rndval=" + rndval + "' frameborder='0' marginwidth='0' marginheight='0' allowtransparency='true'></iframe>");
			},
			beforeClose: function(event, ui) {
				$("#dleuserpopup").html("");
			},
			close: function(event, ui) {
					$('#modal-overlay').fadeOut('slow', function() {
			        $('#modal-overlay').remove();
			    });
			 }
		});

		if ($(window).width() > 830 && $(window).height() > 530 ) {
			$('.modalfixed.ui-dialog').css({position:"fixed"});
			$('#dleuserpopup').dialog( "option", "position", ['0','0'] );
		}
		
		$('#dleuserpopup').css("-webkit-overflow-scrolling","touch");
		
		return false;

}
function clearPoll(id) {

    DLEconfirm( '{$lang['clear_poll_1']}', '{$lang['p_confirm']}', function () {

		ShowLoading('');

		$.get("engine/ajax/adminfunction.php", { id: id, action: 'clearpoll', user_hash: '{$dle_login_hash}' }, function(data){

			HideLoading('');

			DLEalert(data, '{$lang['p_info']}');

		});

	} );

	return false;
	
}
function MarkSpam(id, hash) {

    DLEconfirm( '{$lang['mark_spam']}', '{$lang['p_confirm']}', function () {

		ShowLoading('');

		$.get("engine/ajax/adminfunction.php", { id: id, action: 'newsspam', user_hash: hash }, function(data){

			HideLoading('');

			if (data != "error") {

			    DLEconfirm( data, '$lang[p_info]', function () {
					document.location='{$_SESSION['admin_referrer']}';
				} );

			}

		});

	} );

	return false;
};
// -->
</script>
HTML;

	echo "
    <script type=\"text/javascript\">
    function preview(){";

	if( $config['allow_admin_wysiwyg'] == 1 ) {
		echo "submit_all_data();";
	}

	if( $config['allow_admin_wysiwyg'] == 2 ) {
		echo "document.getElementById('short_story').value = $('#short_story').html();
	document.getElementById('full_story').value = $('#full_story').html();";
	}

	echo "if(document.addnews.title.value == ''){ Growl.info({
				title: '{$lang[p_info]}',
				text: '{$lang['addnews_alert']}'
			}); return false; }
    else{
        dd=window.open('','prv','height=400,width=750,left=0,top=0,resizable=1,scrollbars=1')
        document.addnews.mod.value='preview';document.addnews.target='prv'
        document.addnews.submit();dd.focus()
        setTimeout(\"document.addnews.mod.value='editnews';document.addnews.target='_self'\",500)
    }
    }
    function sendNotice( id ){
		var b = {};

		b[dle_act_lang[3]] = function() {
			$(this).dialog('close');
		};

		b['{$lang['p_send']}'] = function() {
			if ( $('#dle-promt-text').val().length < 1) {
				$('#dle-promt-text').addClass('ui-state-error');
			} else {
				var response = $('#dle-promt-text').val()
				$(this).dialog('close');
				$('#dlepopup').remove();
				$.post('engine/ajax/message.php', { id: id,  text: response, allowdelete: \"no\" },
					function(data){
						if (data == 'ok') { DLEalert('{$lang['p_send_ok']}', '{$lang['p_info']}'); }
					});

			}
		};

		$('#dlepopup').remove();

		$('body').append(\"<div id='dlepopup' class='dle-promt' title='{$lang['p_title']}' style='display:none'>{$lang['p_text']}<br /><br /><textarea name='dle-promt-text' id='dle-promt-text' class='ui-widget-content ui-corner-all' style='width:97%;height:100px; padding: .4em;'></textarea></div>\");

		$('#dlepopup').dialog({
			autoOpen: true,
			width: 500,
			resizable: false,
			buttons: b
		});

	}

    function confirmDelete(url, id){

		var b = {};

		b[dle_act_lang[1]] = function() {
						$(this).dialog(\"close\");
				    };

		b['{$lang['p_message']}'] = function() {
						$(this).dialog(\"close\");

						var bt = {};

						bt[dle_act_lang[3]] = function() {
										$(this).dialog('close');
								    };

						bt['{$lang['p_send']}'] = function() {
										if ( $('#dle-promt-text').val().length < 1) {
											 $('#dle-promt-text').addClass('ui-state-error');
										} else {
											var response = $('#dle-promt-text').val()
											$(this).dialog('close');
											$('#dlepopup').remove();
											$.post('engine/ajax/message.php', { id: id,  text: response },
											  function(data){
											    if (data == 'ok') { document.location=url; } else { DLEalert('{$lang['p_not_send']}', '{$lang['p_info']}'); }
										  });

										}
									};

						$('#dlepopup').remove();

						$('body').append(\"<div id='dlepopup' title='{$lang['p_title']}' class='dle-promt' style='display:none'>{$lang['p_text']}<br /><br /><textarea name='dle-promt-text' id='dle-promt-text' class='ui-widget-content ui-corner-all' style='width:97%;height:100px; padding: .4em;'></textarea></div>\");

						$('#dlepopup').dialog({
							autoOpen: true,
							width: 500,
							resizable: false,
							buttons: bt
						});

				    };

		b[dle_act_lang[0]] = function() {
						$(this).dialog(\"close\");
						document.location=url;
					};

		$(\"#dlepopup\").remove();

		$(\"body\").append(\"<div id='dlepopup' title='{$lang['p_confirm']}' class='dle-promt' style='display:none'><div id='dlepopupmessage'>{$lang['edit_cdel']}</div></div>\");

		$('#dlepopup').dialog({
			autoOpen: true,
			width: 500,
			resizable: false,
			buttons: b
		});


    }

    function CheckStatus(Form){
		if(Form.allow_date.checked) {
		Form.allow_now.disabled = true;
		Form.allow_now.checked = false;
		} else {
		Form.allow_now.disabled = false;
		}
    }

	function auto_keywords ( key )
	{
		var wysiwyg = '{$config['allow_admin_wysiwyg']}';

		if (wysiwyg == \"1\") {
			submit_all_data();
		}

		if (wysiwyg == \"2\") {
			tinyMCE.triggerSave();
		}

		var short_txt = document.getElementById('short_story').value;
		var full_txt = document.getElementById('full_story').value;

		ShowLoading('');

		$.post(\"engine/ajax/keywords.php\", { short_txt: short_txt, full_txt: full_txt, key: key }, function(data){

			HideLoading('');

			if (key == 1) { $('#autodescr').val(data); }
			else { $('#keywords').tokenfield('setTokens', data);}

		});

		return false;
	}

	function find_relates ()
	{
		var title = document.getElementById('title').value;

		ShowLoading('');

		$.post('engine/ajax/find_relates.php', { title: title, id: '{$row['id']}' }, function(data){

			HideLoading('');

			$('#related_news').html(data);

		});

		return false;

	};
	
	function xfimagedelete( xfname, xfvalue )
	{
		
		DLEconfirm( '{$lang['image_delete']}', '{$lang['p_info']}', function () {
		
			ShowLoading('');
			
			$.post('engine/ajax/upload.php', { subaction: 'deluploads', user_hash: '{$dle_login_hash}', news_id: '{$row['id']}', author: '{$author}', 'images[]' : xfvalue }, function(data){
	
				HideLoading('');
				
				$('#uploadedfile_'+xfname).html('');
				$('#xf_'+xfname).val('');
			});
			
		} );

		return false;

	};

	function xffiledelete( xfname, xfvalue )
	{
		
		DLEconfirm( '{$lang['file_delete']}', '{$lang['p_info']}', function () {
		
			ShowLoading('');
			
			$.post('engine/ajax/upload.php', { subaction: 'deluploads', user_hash: '{$dle_login_hash}', news_id: '{$row['id']}', author: '{$author}', 'files[]' : xfvalue }, function(data){
	
				HideLoading('');
				
				$('#uploadedfile_'+xfname).html('');
				$('#xf_'+xfname).val('');
				$('#xf_'+xfname).hide('');
			});
			
		} );

		return false;

	};

	function checkxf ( )
	{
		var wysiwyg = '{$config['allow_admin_wysiwyg']}';
		var status = '';

		if (wysiwyg == \"2\") {
			tinyMCE.triggerSave();
		}

		$('[uid=\"essential\"]:visible').each(function(indx) {

			if($.trim($(this).find('[rel=\"essential\"]').val()).length < 1) {

				Growl.info({
					title: '{$lang[p_info]}',
					text: '{$lang['addnews_xf_alert']}'
				});

				status = 'fail';

			}

		});

		if(document.addnews.title.value == ''){

			Growl.info({
				title: '{$lang[p_info]}',
				text: '{$lang['addnews_alert']}'
			});

			status = 'fail';

		}

		return status;

	};

	$(function(){
		$('#tags').tokenfield({
		  autocomplete: {
		    source: 'engine/ajax/find_tags.php',
			minLength: 3,
		    delay: 500
		  },
		  createTokensOnBlur:true
		});

		$('[data-rel=links]').tokenfield({createTokensOnBlur:true});

		$('.categoryselect').chosen({allow_single_deselect:true, no_results_text: '{$lang['addnews_cat_fault']}'});

	});
    </script>";

	$categories_list = CategoryNewsSelection( $cat_list, 0 );
	if( $config['allow_multi_category'] ) $category_multiple = "class=\"categoryselect\" multiple";
	else $category_multiple = "class=\"categoryselect\"";

	if( $member_id['user_group'] == 1 ) {

		$author_info = "<input type=\"text\" name=\"new_author\" size=\"20\" value=\"{$row['autor']}\"><input type=\"hidden\" name=\"old_author\" value=\"{$row['autor']}\" />";

	} else {

		$author_info = "<b>{$row['autor']}</b>";

	}


	if ( $user_group[$member_id['user_group']]['admin_editusers'] ) {

		$author_info .= "&nbsp;<a class=\"status-info\" onclick=\"javascript:popupedit('".urlencode($row['autor'])."'); return(false)\" href=\"#\"><i class=\"icon-user\"></i></a>";

	}

	if( $row['allow_comm'] ) $ifch = "checked";	else $ifch = "";
	if( $row['allow_main'] ) $ifmain = "checked"; else $ifmain = "";
	if( $row['approve'] ) $ifapp = "checked"; else $ifapp = "";
	if( $row['fixed'] ) $iffix = "checked";	else $iffix = "";
	if( $row['allow_rate'] ) $ifrat = "checked"; else $ifrat = "";
	if( $row['disable_index'] ) $ifdis = "checked"; else $ifdis = "";

	if( $user_group[$member_id['user_group']]['allow_fixed'] and $config['allow_fixed'] ) $fix_input = "<input class=\"icheck\" type=\"checkbox\" id=\"news_fixed\" name=\"news_fixed\" value=\"1\" {$iffix}><label for=\"news_fixed\">{$lang['addnews_fix']}</label>"; else $fix_input = "";
	if( $user_group[$member_id['user_group']]['allow_main'] ) $main_input = "<input class=\"icheck\" type=\"checkbox\" id=\"allow_main\" name=\"allow_main\" value=\"1\" {$ifmain}><label for=\"allow_main\">{$lang['addnews_main']}</label>"; else $main_input = "";
	if($member_id['user_group'] < 3 ) $disable_index = "<input class=\"icheck\" type=\"checkbox\" id=\"disable_index\" name=\"disable_index\" value=\"1\" {$ifdis}><label for=\"disable_index\">{$lang['add_disable_index']}</label>"; else $disable_index = "";

	if( $row['allow_br'] == '1' ) $fix_br_cheked = "checked";
	else $fix_br_cheked = "";

	if( !$config['allow_admin_wysiwyg'] ) $fix_br = "<input class=\"icheck\" type=\"checkbox\" id=\"allow_br\" name=\"allow_br\" value=\"1\" {$fix_br_cheked}><label for=\"allow_br\">{$lang['allow_br']}</label>";
	else $fix_br = "";

	if( $row['editdate'] ) {
		$row['editdate'] = date( "d.m.Y H:i:s", $row['editdate'] );
		$lang['news_edit_date'] = $lang['news_edit_date'] . " " . $row['editor'] . " - " . $row['editdate'];
	} else
		$lang['news_edit_date'] = "";
	if( $row['view_edit'] == '1' ) $view_edit_cheked = "checked";
	else $view_edit_cheked = "";

	$exp_action = array();
	$exp_action[$expires['action']] = "selected=\"selected\"";

	if ($row['autor'] != $member_id['name']) $notice_btn = "<button  onclick=\"sendNotice('{$id}');  return false;\" class=\"btn btn-default\"><i class=\"icon-envelope\"></i> {$lang['btn_notice']}</button>&nbsp;"; else $notice_btn = "";
	if ($row['autor'] != $member_id['name'] AND $user_group[$member_id['user_group']]['allow_all_edit'] AND !$row['approve']) $spam_btn = "<button  onclick=\"MarkSpam('{$id}', '{$dle_login_hash}'); return false;\" class=\"btn btn-gold\"><i class=\"icon-minus-sign\"></i> {$lang['btn_spam']}</button>&nbsp;"; else $spam_btn = "";

	echo <<<HTML
<div class="box">

		    <div class="box-header">
				<ul class="nav nav-tabs nav-tabs-left">
					<li class="active"><a href="#tabhome" data-toggle="tab"><i class="icon-home"></i> {$lang['tabs_news']}</a></li>
					<li><a href="#tabvote" data-toggle="tab"><i class="icon-bar-chart"></i> {$lang['tabs_vote']}</a></li>
					<li><a href="#tabextra" data-toggle="tab"><i class="icon-tasks"></i> {$lang['tabs_extra']}</a></li>
					<li><a href="#tabperm" data-toggle="tab"><i class="icon-lock"></i> {$lang['tabs_perm']}</a></li>
				</ul>
			</div>

            <div class="box-content">
			<form method="post" class="form-horizontal" name="addnews" id="addnews" onsubmit="if(checkxf()=='fail') return false;" action="">
                 <div class="tab-content">
                     <div class="tab-pane active" id="tabhome">
						<div class="row box-section">

							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['edit_info']}</label>
							  <div class="col-md-10">
								ID=<b>{$row['id']}</b>, {$lang['edit_eau']} {$author_info}
							  </div>
							 </div>

							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['edit_et']}</label>
							  <div class="col-md-10">
								<input type="text" style="width:99%;max-width:437px;" name="title" id="title" value="{$row['title']}">&nbsp;<button onclick="find_relates(); return false;" class="btn btn-sm btn-black">{$lang['b_find_related']}</button>&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_title']}" >?</span><span id="related_news"></span>
							  </div>
							 </div>

							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['edit_edate']}</label>
							  <div class="col-md-10">
								<input type="text" name="newdate" data-rel="calendar" size="20" value="{$row['date']}">&nbsp;<input class="checkbox-inline" type="checkbox" name="allow_date" id="allow_date" value="yes" onclick="CheckStatus(addnews)" checked><label for="allow_date">&nbsp;{$lang['edit_ndate']}</label>&nbsp;<input class="checkbox-inline" type="checkbox" name="allow_now" id="allow_now" value="yes" disabled>&nbsp;<label for="allow_now">{$lang['edit_jdate']}</label>
							  </div>
							 </div>

							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['edit_cat']}</label>
							  <div class="col-md-10">
								<select data-placeholder="{$lang['addnews_cat_sel']}" name="category[]" id="category" onchange="onCategoryChange(this)" {$category_multiple} style="width:350px;">{$categories_list}</select>
							  </div>
							 </div>

							 <div class="form-group editor-group">
							  <label class="control-label col-lg-2">{$lang['addnews_short']}</label>
							  <div class="col-lg-10">
HTML;

	if( $config['allow_admin_wysiwyg'] ) {

		include (ENGINE_DIR . '/editor/shortnews.php');

	} else {

		$bb_editor = true;
		include (ENGINE_DIR . '/inc/include/inserttag.php');
		echo "{$bb_code}<textarea style=\"width:100%;max-width: 950px;height:300px;\" onfocus=\"setFieldName(this.name)\" name=\"short_story\" id=\"short_story\" >{$row['short_story']}</textarea>";
	}

echo <<<HTML
							  </div>
							</div>

							 <div class="form-group editor-group">
							  <label class="control-label col-lg-2">{$lang['addnews_full']}</label>
							  <div class="col-lg-10">
HTML;
	if( $config['allow_admin_wysiwyg'] ) {

		include (ENGINE_DIR . '/editor/fullnews.php');

	} else {

		echo "{$bb_panel}<textarea style=\"width:100%;max-width: 950px;height:350px;\" onfocus=\"setFieldName(this.name)\" name=\"full_story\" id=\"full_story\">{$row['full_story']}</textarea>";
	}
	// XFields Call
	$xfieldsaction = "list";
	$xfieldsid = $row['xfields'];
	$xfieldscat = $row['category'];
	$sess_id = session_id();
	$news_id = $id;
	$author = urlencode($row['autor']);
	include (ENGINE_DIR . '/inc/xfields.php');
	// End XFields Call

	if( !$config['allow_admin_wysiwyg'] ) $output = str_replace("<!--panel-->", $bb_panel, $output);
echo <<<HTML
							  </div>
							</div>
{$output}

							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['news_edit_reason']}</label>
							  <div class="col-md-10">
								<input class="icheck" type="checkbox" id="view_edit" name="view_edit" value="1" {$view_edit_cheked}><label for="view_edit">{$lang['allow_view_edit']}</label><br /><input type="text" style="width:100%;max-width:437px;" name="editreason" id="editreason" value="{$row['reason']}"> {$lang['news_edit_date']}
							  </div>
							 </div>

							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['addnews_option']}</label>
							  <div class="col-md-10">
								  <div class="row">
									<div class="col-md-12"><input class="icheck" type="checkbox" id="approve" name="approve" value="1" {$ifapp}><label for="approve">{$lang['addnews_mod']}</label></div>
								  </div>
								  <div class="row">
									<div class="col-md-3" style="max-width:300px;" >{$main_input}</div>
									<div class="col-md-3" style="max-width:250px;"><input class="icheck" type="checkbox" id="allow_comm" name="allow_comm" value="1" {$ifch}><label for="allow_comm">{$lang['addnews_comm']}</label></div>
									<div class="col-md-6">{$disable_index}</div>
								  </div>
								  <div class="row">
									<div class="col-md-3" style="max-width:300px;" ><input class="icheck" type="checkbox" id="allow_rating" name="allow_rating" value="1" {$ifrat}><label for="allow_rating">{$lang['addnews_allow_rate']}</label></div>
									<div class="col-md-3" style="max-width:250px;">{$fix_input}</div>
									<div class="col-md-6"></div>
								  </div>
								  <div class="row">
									<div class="col-md-12">{$fix_br}</div>
								  </div>
							  </div>
							 </div>

						</div>
					</div>
                    <div class="tab-pane" id="tabvote" >
						<div class="row box-section">

							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['v_ftitle']}</label>
							  <div class="col-md-10">
								<input type="text" name="vote_title" style="width:100%;max-width:350px;" value="{$poll['title']}">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang[hint_ftitle]}" >?</span>
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['vote_title']}</label>
							  <div class="col-md-10">
								<input type="text" name="frage" style="width:100%;max-width:350px;" value="{$poll['frage']}">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang[hint_vtitle]}" >?</span>
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['vote_body']}<div class="note large">{$lang['vote_str_1']}</div></label>
							  <div class="col-md-10">
								<textarea rows="7" style="width:100%;max-width:350px;" name="vote_body">{$poll['body']}</textarea>
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-md-2"></label>
							  <div class="col-md-10">
								<input class="icheck" type="checkbox" id="allow_m_vote" name="allow_m_vote" value="1" {$poll['multiple']}><label for="allow_m_vote">{$lang['v_multi']}</label>
								<br />{$clear_poll}
							  </div>
							 </div>

							<div class="row">
								<div class="col-md-12"><span class="note large"> <i class="icon-warning-sign"></i> {$lang['v_info']}</span></div>
							</div>


						</div>
                     </div>
                    <div class="tab-pane" id="tabextra" >
						<div class="row box-section">

							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['catalog_url']}</label>
							  <div class="col-md-10">
								<input type="text" name="catalog_url" size="5" value="{$row['symbol']}">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['catalog_hint_url']}" >?</span>
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['addnews_url']}</label>
							  <div class="col-md-10">
								<input type="text" name="alt_name" style="width:100%;max-width:437px;" value="{$row['alt_name']}">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_url']}" >?</span>
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['addnews_tags']}</label>
							  <div class="col-md-10">
								<input type="text" name="tags" id="tags" style="width:437px;" autocomplete="off" value="{$row['tags']}" />
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['date_expires']}</label>
							  <div class="col-md-10">
								<input type="text" name="expires" data-rel="calendardate" size="20" value="{$expires['expires']}">&nbsp;{$lang['cat_action']}&nbsp;<select class="uniform" name="expires_action"><option value="0">{$lang['mass_noact']}</option><option value="1" {$exp_action[1]}>{$lang['edit_dnews']}</option><option value="2" {$exp_action[2]}>{$lang['mass_edit_notapp']}</option><option value="3" {$exp_action[3]}>{$lang['mass_edit_notmain']}</option><option value="4" {$exp_action[4]}>{$lang['mass_edit_notfix']}</option></select>&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_expires']}" >?</span>
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
								<input type="text" name="descr" id="autodescr" style="width:100%;max-width:437px;" value="{$row['descr']}"> <span class="note large"> <i class="icon-warning-sign"></i> {$lang['meta_descr_max']}</span>
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-md-2">{$lang['meta_keys']}</label>
							  <div class="col-md-10">
								<textarea class="tags" name="keywords" id='keywords' style="width:437px;">{$row['keywords']}</textarea><br /><br />
									<button onclick="auto_keywords(1); return false;" class="btn btn-blue"><i class="icon-exchange"></i> {$lang['btn_descr']}</button>&nbsp;
									<button onclick="auto_keywords(2); return false;" class="btn btn-blue"><i class="icon-exchange"></i> {$lang['btn_keyword']}</button>
							  </div>
							 </div>

						</div>
                     </div>
                    <div class="tab-pane" id="tabperm" >
						<div class="row box-section">
HTML;

	if( $member_id['user_group'] < 3 ) {
		foreach ( $user_group as $group ) {
			if( $group['id'] > 1 ) {
				echo <<<HTML
							<div class="form-group">
							  <label class="control-label col-md-2">{$group['group_name']}</label>
							  <div class="col-md-10">
								<select class="uniform" name="group_extra[{$group['id']}]">
										<option value="0">{$lang['ng_group']}</option>
										<option value="1" {$access[$group['id']][1]}>{$lang['ng_read']}</option>
										<option value="2" {$access[$group['id']][2]}>{$lang['ng_all']}</option>
										<option value="3" {$access[$group['id']][3]}>{$lang['ng_denied']}</option>
								</select>
							   </div>
							 </div>
HTML;
			}
		}
	} else {

		echo <<<HTML
    <tr>
        <td style="padding:4px;"><br />{$lang['tabs_not']}</br /><br /></td>
    </tr>
HTML;

	}

echo <<<HTML
							<div class="row">
								<div class="col-md-12"><span class="note large"> <i class="icon-warning-sign"></i> {$lang['tabs_g_info']}</span></div>
							</div>
						</div>
                     </div>
				</div>
				<div class="padded">
	<input type="submit" class="btn btn-green" value="{$lang['news_save']}">&nbsp;
	<button onclick="preview(); return false;" class="btn btn-gray"><i class="icon-desktop"></i> {$lang['btn_preview']}</button>&nbsp;
	{$notice_btn}
	{$spam_btn}
	<button onclick="confirmDelete('?mod=editnews&action=doeditnews&ifdelete=yes&id=$id&user_hash=$dle_login_hash', '{$id}'); return false;" class="btn btn-red"><i class="icon-trash"></i> {$lang['edit_dnews']}</button>
    <input type="hidden" name="id" value="$id" />
    <input type="hidden" name="expires_alt" value="{$expires['expires']}{$expires['action']}" />
    <input type="hidden" name="user_hash" value="$dle_login_hash" />
    <input type="hidden" name="action" value="doeditnews" />
    <input type="hidden" name="mod" value="editnews" />
				</div>
</form>
			</div>
</div>
HTML;

	echofooter();
}
// ********************************************************************************
// Сохранение или удаление новости
// ********************************************************************************
elseif( $action == "doeditnews" ) {

	$id = intval( $_GET['id'] );

	$allow_comm = isset( $_POST['allow_comm'] ) ? intval( $_POST['allow_comm'] ) : 0;
	$allow_main = isset( $_POST['allow_main'] ) ? intval( $_POST['allow_main'] ) : 0;
	$approve = isset( $_POST['approve'] ) ? intval( $_POST['approve'] ) : 0;
	$allow_rating = isset( $_POST['allow_rating'] ) ? intval( $_POST['allow_rating'] ) : 0;
	$news_fixed = isset( $_POST['news_fixed'] ) ? intval( $_POST['news_fixed'] ) : 0;
	$allow_br = isset( $_POST['allow_br'] ) ? intval( $_POST['allow_br'] ) : 0;
	$view_edit = isset( $_POST['view_edit'] ) ? intval( $_POST['view_edit'] ) : 0;
	$category = $_POST['category'];
	$disable_index = isset( $_POST['disable_index'] ) ? intval( $_POST['disable_index'] ) : 0;

	if($member_id['user_group'] > 2 ) $disable_index = 0;

	if( ! count( $category ) ) {
		$category = array ();
		$category[] = '0';
	}

	$category_list = array();

	foreach ( $category as $value ) {
		$category_list[] = intval($value);
	}

	$category_list = $db->safesql( implode( ',', $category_list ) );

	$allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_add'] );

	foreach ( $category as $selected ) {
		if( $allow_list[0] != "all" and ! in_array( $selected, $allow_list ) and $member_id['user_group'] != 1 ) $approve = 0;
	}

	if( !$user_group[$member_id['user_group']]['moderation'] ) $approve = 0;

	$allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_allow_addnews'] );

	foreach ( $category as $selected ) {
		if( $allow_list[0] != "all" AND ! in_array( $selected, $allow_list ) AND $ifdelete != "yes") msg( "error", $lang['addnews_error'], $lang['news_err_41'], "javascript:history.go(-1)" );
	}

	$title = $parse->process( trim( strip_tags ($_POST['title']) ) );

	if ( !$user_group[$member_id['user_group']]['allow_html'] ) {

		$_POST['short_story'] = strip_tags ($_POST['short_story']);
		$_POST['full_story'] = strip_tags ($_POST['full_story']);

	}

	if ( $config['allow_admin_wysiwyg'] ) $parse->allow_code = false;

	$full_story = $parse->process( $_POST['full_story'] );
	$short_story = $parse->process( $_POST['short_story'] );

	if( $config['allow_admin_wysiwyg'] or $allow_br != '1' ) {

		$full_story = $db->safesql( $parse->BB_Parse( $full_story ) );
		$short_story = $db->safesql( $parse->BB_Parse( $short_story ) );

	} else {

		$full_story = $db->safesql( $parse->BB_Parse( $full_story, false ) );
		$short_story = $db->safesql( $parse->BB_Parse( $short_story, false ) );

	}

	if( $parse->not_allowed_text ) {
		msg( "error", $lang['addnews_error'], $lang['news_err_39'], "javascript:history.go(-1)" );
	}

	if( trim( $title ) == "" and $ifdelete != "yes" ) msg( "error", $lang['cat_error'], $lang['addnews_alert'], "javascript:history.go(-1)" );

	if( dle_strlen( $title, $config['charset'] ) > 255 ) {
		msg( "error", $lang['cat_error'], $lang['addnews_ermax'], "javascript:history.go(-1)" );
	}

	if( trim( $_POST['alt_name'] ) == "" or ! $_POST['alt_name'] ) $alt_name = totranslit( stripslashes( $title ) );
	else $alt_name = totranslit( stripslashes( $_POST['alt_name'] ) );

	$title = $db->safesql( $title );
	$metatags = create_metatags( $short_story." ".$full_story );

	$catalog_url = $db->safesql( dle_substr( htmlspecialchars( strip_tags( stripslashes( trim( $_POST['catalog_url'] ) ) ), ENT_QUOTES, $config['charset'] ), 0, 3, $config['charset'] ) );

	if ($config['create_catalog'] AND !$catalog_url) $catalog_url = $db->safesql( dle_substr( htmlspecialchars( strip_tags( stripslashes( trim( $title ) ) ), ENT_QUOTES, $config['charset'] ), 0, 1, $config['charset'] ) );

	$editreason = $db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $_POST['editreason'] ) ) ), ENT_QUOTES, $config['charset'] ) );

	if( @preg_match( "/[\||\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $_POST['tags'] ) ) $_POST['tags'] = "";
	else $_POST['tags'] = @$db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $_POST['tags'] ) ) ), ENT_COMPAT, $config['charset'] ) );

	if ( $_POST['tags'] ) {

		$temp_array = array();
		$tags_array = array();
		$temp_array = explode (",", $_POST['tags']);

		if (count($temp_array)) {

			foreach ( $temp_array as $value ) {
				if( trim($value) ) $tags_array[] = trim( $value );
			}

		}

		if ( count($tags_array) ) $_POST['tags'] = implode(", ", $tags_array); else $_POST['tags'] = "";

	}

	// обработка опроса
	if( trim( $_POST['vote_title'] != "" ) ) {

		$add_vote = 1;
		$vote_title = trim( $db->safesql( $parse->process( $_POST['vote_title'] ) ) );
		$frage = trim( $db->safesql( $parse->process( $_POST['frage'] ) ) );
		$vote_body = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['vote_body'] ), false ) );
		$allow_m_vote = intval( $_POST['allow_m_vote'] );

	} else
		$add_vote = 0;

	// обработка доступа
	if( $member_id['user_group'] < 3 and $ifdelete != "yes" ) {

		$group_regel = array ();

		foreach ( $_POST['group_extra'] as $key => $value ) {
			if( $value ) $group_regel[] = intval( $key ) . ':' . intval( $value );
		}

		if( count( $group_regel ) ) $group_regel = implode( "||", $group_regel );
		else $group_regel = "";

	} else
		$group_regel = '';

	if ( ($_POST['expires'].$_POST['expires_action']) != $_POST['expires_alt'] ) {
		if( trim( $_POST['expires'] ) != "" ) {
			if( (($expires = strtotime( $_POST['expires'] )) === - 1) OR !$expires) {
				msg( "error", $lang['addnews_error'], $lang['addnews_erdate'], "javascript:history.go(-1)" );
			}
		} else $expires = '';

		$expires_change = true;

	} else $expires_change = false;

	$no_permission = FALSE;
	$okdeleted = FALSE;
	$okchanges = FALSE;

	$db->query( "SELECT id, title, autor, date, category, approve, tags, news_id FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE id = '$id'" );

	while ( $row = $db->get_row() ) {
		$item_db[0] = $row['id'];
		$item_db[1] = $row['autor'];
		$item_db[2] = $row['tags'];
		$item_db[3] = $row['approve'];
		$item_db[4] = $db->safesql( $row['title'] );
		$item_db[5] = explode( ',', $row['category'] );
		$item_db[6] = $row['news_id'];
		$item_db[7] = strtotime( $row['date'] );
	}

	$db->free();

	if( $ifdelete != "yes" ) {

		$xfieldsaction = "init";
		$xfieldsid = $item_db[0];
		include (ENGINE_DIR . '/inc/xfields.php');
	}


	if( $item_db[0] ) {

		$have_perm = 0;

		if( $user_group[$member_id['user_group']]['allow_all_edit'] ) $have_perm = 1;

		if( $user_group[$member_id['user_group']]['allow_edit'] and $item_db[1] == $member_id['name'] ) {
			$have_perm = 1;
		}

		if( $ifdelete == "yes" ) {

			$allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_add'] );

			foreach ( $item_db[5] as $selected ) {
				if( $allow_list[0] != "all" AND !in_array($selected, $allow_list) ) $have_perm = 0;
			}

			if( !$user_group[$member_id['user_group']]['moderation']) {

				$have_perm = 0;

			}
		}
		
		if( $user_group[$member_id['user_group']]['max_edit_days'] ) {
			$maxedittime = $_TIME - ($user_group[$member_id['user_group']]['max_edit_days'] * 3600 * 24);
			if( $maxedittime > $item_db[7] ) $have_perm = 0;
		}
		
		if( ($member_id['user_group'] == 1) ) {
			$have_perm = 1;
		}
		
		if( $have_perm ) {

			if( $ifdelete != "yes" ) {
				$okchanges = TRUE;

				// Обработка даты и времени
				$added_time = time();
				$newdate = $_POST['newdate'];

				if( $_POST['allow_date'] != "yes" ) {

					if( $_POST['allow_now'] == "yes" ) $thistime = date( "Y-m-d H:i:s", $added_time );
					elseif( (($newsdate = strtotime( $newdate )) === - 1) OR !$newsdate ) {
						msg( "error", $lang['cat_error'], $lang['addnews_erdate'], "javascript:history.go(-1)" );
					} else {

						$thistime = date( "Y-m-d H:i:s", $newsdate );

						if( ! intval( $config['no_date'] ) and $newsdate > $added_time ) {
							$thistime = date( "Y-m-d H:i:s", $added_time );
						}

					}

					$db->query( "UPDATE " . PREFIX . "_post SET title='$title', date='$thistime', short_story='$short_story', full_story='$full_story', xfields='$filecontents', descr='{$metatags['description']}', keywords='{$metatags['keywords']}', category='$category_list', alt_name='$alt_name', allow_comm='$allow_comm', approve='$approve', allow_main='$allow_main', fixed='$news_fixed', allow_br='$allow_br', symbol='$catalog_url', tags='{$_POST['tags']}', metatitle='{$metatags['title']}' WHERE id='$item_db[0]'" );

				} else {

					$db->query( "UPDATE " . PREFIX . "_post SET title='$title', short_story='$short_story', full_story='$full_story', xfields='$filecontents', descr='{$metatags['description']}', keywords='{$metatags['keywords']}', category='$category_list', alt_name='$alt_name', allow_comm='$allow_comm', approve='$approve', allow_main='$allow_main', fixed='$news_fixed', allow_br='$allow_br', symbol='$catalog_url', tags='{$_POST['tags']}', metatitle='{$metatags['title']}' WHERE id='$item_db[0]'" );
				}

				if ($item_db[6]) $db->query( "UPDATE " . PREFIX . "_post_extras SET allow_rate='$allow_rating', votes='$add_vote', disable_index='$disable_index', access='$group_regel', editdate='$added_time', editor='{$member_id['name']}', reason='$editreason', view_edit='$view_edit' WHERE news_id='$item_db[0]'" );
				else $db->query( "INSERT INTO " . PREFIX . "_post_extras (news_id, allow_rate, votes, disable_index, access, editdate, editor, reason, view_edit) VALUES('{$item_db[0]}', '{$allow_rating}', '{$add_vote}', '{$disable_index}', '{$group_regel}', '{$added_time}', '{$member_id['name']}', '{$editreason}', '{$view_edit}')" );

				$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '25', '{$title}')" );


				if( $add_vote ) {

					$count = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_poll WHERE news_id = '$item_db[0]'" );

					if( $count['count'] ) $db->query( "UPDATE  " . PREFIX . "_poll set title='$vote_title', frage='$frage', body='$vote_body', multiple='$allow_m_vote' WHERE news_id = '$item_db[0]'" );
					else $db->query( "INSERT INTO " . PREFIX . "_poll (news_id, title, frage, body, votes, multiple, answer) VALUES('$item_db[0]', '$vote_title', '$frage', '$vote_body', 0, '$allow_m_vote', '')" );

				} else {
					$db->query( "DELETE FROM " . PREFIX . "_poll WHERE news_id='$item_db[0]'" );
					$db->query( "DELETE FROM " . PREFIX . "_poll_log WHERE news_id='$item_db[0]'" );
				}

				if ( $expires_change ) {

					$expires_action = intval($_POST['expires_action']);
					$db->query( "DELETE FROM " . PREFIX . "_post_log WHERE news_id='$item_db[0]'" );

					if( $expires AND $expires_action ) {
						$db->query( "INSERT INTO " . PREFIX . "_post_log (news_id, expires, action) VALUES('$item_db[0]', '$expires', '$expires_action')" );
					}

				}

				// Смена автора публикации
				if( $member_id['user_group'] == 1 AND $_POST['new_author'] != $_POST['old_author'] ) {

					$_POST['new_author'] = $db->safesql( $_POST['new_author'] );

					$row = $db->super_query( "SELECT user_id  FROM " . USERPREFIX . "_users WHERE name = '{$_POST['new_author']}'" );

					if( $row['user_id'] ) {

						$db->query( "UPDATE " . PREFIX . "_post SET autor='{$_POST['new_author']}' WHERE id='$item_db[0]'" );
						$db->query( "UPDATE " . PREFIX . "_post_extras SET user_id='{$row['user_id']}' WHERE news_id='$item_db[0]'" );
						$db->query( "UPDATE " . PREFIX . "_images SET author='{$_POST['new_author']}' WHERE news_id='$item_db[0]'" );
						$db->query( "UPDATE " . PREFIX . "_files SET author='{$_POST['new_author']}' WHERE news_id='$item_db[0]'" );

						$db->query( "UPDATE " . USERPREFIX . "_users SET news_num=news_num+1 where user_id='{$row['user_id']}'" );
						$db->query( "UPDATE " . USERPREFIX . "_users SET news_num=news_num-1 where name='$item_db[1]'" );

					} else {

						msg( "error", $lang['addnews_error'], $lang['edit_no_author'], "javascript:history.go(-1)" );

					}

				}

				// Облако тегов
				if( $_POST['tags'] != $item_db[2] or $approve != $item_db[3] ) {
					$db->query( "DELETE FROM " . PREFIX . "_tags WHERE news_id = '$item_db[0]'" );

					if( $_POST['tags'] != "" and $approve ) {

						$tags = array ();

						$_POST['tags'] = explode( ",", $_POST['tags'] );

						foreach ( $_POST['tags'] as $value ) {

							$tags[] = "('" . $item_db[0] . "', '" . trim( $value ) . "')";
						}

						$tags = implode( ", ", $tags );
						$db->query( "INSERT INTO " . PREFIX . "_tags (news_id, tag) VALUES " . $tags );

					}
				}
				
				$db->query( "DELETE FROM " . PREFIX . "_xfsearch WHERE news_id = '{$item_db[0]}'" );

				if ( count($xf_search_words) AND $approve ) {
					
					$temp_array = array();
					
					foreach ( $xf_search_words as $value ) {
						
						$temp_array[] = "('" . $item_db[0] . "', '" . $value[0] . "', '" . $value[1] . "')";
					}
					
					$xf_search_words = implode( ", ", $temp_array );
					$db->query( "INSERT INTO " . PREFIX . "_xfsearch (news_id, tagname, tagvalue) VALUES " . $xf_search_words );
				}

			} else {

				$db->query( "DELETE FROM " . PREFIX . "_post WHERE id='{$item_db[0]}'" );
				$db->query( "DELETE FROM " . PREFIX . "_post_extras WHERE news_id='{$item_db[0]}'" );
				$db->query( "DELETE FROM " . PREFIX . "_comments WHERE post_id='{$item_db[0]}'" );
				$db->query( "DELETE FROM " . PREFIX . "_poll WHERE news_id='{$item_db[0]}'" );
				$db->query( "DELETE FROM " . PREFIX . "_poll_log WHERE news_id='{$item_db[0]}'" );
				$db->query( "DELETE FROM " . PREFIX . "_post_log WHERE news_id='{$item_db[0]}'" );
				$db->query( "DELETE FROM " . PREFIX . "_tags WHERE news_id = '{$item_db[0]}'" );
				$db->query( "DELETE FROM " . PREFIX . "_xfsearch WHERE news_id = '{$item_db[0]}'" );
				$db->query( "DELETE FROM " . PREFIX . "_logs WHERE news_id = '{$item_db[0]}'" );
				$db->query( "DELETE FROM " . PREFIX . "_subscribe WHERE news_id='{$item_db[0]}'");
				$db->query( "UPDATE " . USERPREFIX . "_users SET news_num=news_num-1 WHERE name='{$item_db[1]}'" );

				$okdeleted = TRUE;

				$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '26', '{$item_db[4]}')" );


				$row = $db->super_query( "SELECT images  FROM " . PREFIX . "_images where news_id = '$item_db[0]'" );

				$listimages = explode( "|||", $row['images'] );

				if( $row['images'] != "" ) foreach ( $listimages as $dataimages ) {
					$url_image = explode( "/", $dataimages );

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

				$db->query( "DELETE FROM " . PREFIX . "_images WHERE news_id = '$item_db[0]'" );

				$db->query( "SELECT id, onserver FROM " . PREFIX . "_files WHERE news_id = '$item_db[0]'" );

				while ( $row = $db->get_row() ) {

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

				}

				$db->query( "DELETE FROM " . PREFIX . "_files WHERE news_id = '$item_db[0]'" );

			}
		} else
			$no_permission = TRUE;

	}

	clear_cache( array('news_', 'full_'.$item_db[0], 'comm_'.$item_db[0], 'tagscloud_', 'archives_', 'calendar_', 'rss', 'stats') );

	if( ! $_SESSION['admin_referrer'] ) {

		$_SESSION['admin_referrer'] = "?mod=editnews&amp;action=list";

	}

	if( $no_permission ) {
		msg( "error", $lang['addnews_error'], $lang['edit_denied'], $_SESSION['admin_referrer'] );
	} elseif( $okdeleted ) {
		msg( "info", $lang['edit_delok'], $lang['edit_delok_1'], $_SESSION['admin_referrer'] );
	} elseif( $okchanges ) {
		msg( "info", $lang['edit_alleok'], $lang['edit_alleok_1'], $_SESSION['admin_referrer'] );
	} else {
		msg( "error", $lang['addnews_error'], $lang['edit_allerr'], $_SESSION['admin_referrer'] );
	}
}
?>