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
 Файл: editusers.php
-----------------------------------------------------
 Назначение: настройка пользователей
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_editusers'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

$id = intval( $_REQUEST['id'] );

if( !$action ) $action = "list";

// ********************************************************************************
// Список пользователей
// ********************************************************************************
if( $action == "list" ) {

	echoheader( "<i class=\"icon-user\"></i>".$lang['user_head'], $lang['opt_user'] );

	echo <<<HTML
<script type="text/javascript">
<!-- begin
function popupedit( id ){

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
				"{$lang['edit_dnews']}": function() {
					window.frames.edituserframe.confirmDelete("{$dle_login_hash}");
				},
				"{$lang['user_save']}": function() {

					document.getElementById('edituserframe').contentWindow.document.getElementById('saveuserform').submit();

				}
			},
			open: function(event, ui) {
				$("#dleuserpopup").html("<iframe name='edituserframe' id='edituserframe' width='100%' height='389' src='?mod=editusers&action=edituser&id=" + id + "&rndval=" + rndval + "' frameborder='0' marginwidth='0' marginheight='0' allowtransparency='true'></iframe>");
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

HTML;


	echo '
	function confirmdelete(id, user){
	    DLEconfirm( "' . $lang['user_deluser'] . '", "' . $lang['p_confirm'] . '", function () {
		    document.location="?mod=editusers&user_hash=' . $dle_login_hash . '&action=dodeleteuser&id="+id+"&user="+user;
		} );
    }
    function clearform(frm){
    for (var i=0;i<frm.length;i++) {
      var el=frm.elements[i];
      if (el.type=="checkbox" || el.type=="radio") { el.checked=0; continue; }
      if ((el.type=="text") || (el.type=="textarea") || (el.type == "password")) { el.value=""; continue; }
      if ((el.type=="select-one") || (el.type=="select-multiple")) { el.selectedIndex=0; }
    }
    document.searchform.start_from.value="";
    }
    function list_submit(prm){
      document.searchform.start_from.value=prm;
      document.searchform.submit();
      return false;
    }
    // end -->
    </script>';

	$grouplist = get_groups( 4 );

	$search_name = $db->safesql( trim( htmlspecialchars( strip_tags( $_REQUEST['search_name'] ), ENT_QUOTES, $config['charset'] ) ) );
	$search_mail = $db->safesql( trim( htmlspecialchars( strip_tags( $_REQUEST['search_mail'] ) ) ) );

	$toregdate = $db->safesql( trim( htmlspecialchars( strip_tags( $_REQUEST['toregdate'] ) ) ) );
	$fromregdate = $db->safesql( trim( htmlspecialchars( strip_tags( $_REQUEST['fromregdate'] ) ) ) );
	$fromentdate = $db->safesql( trim( htmlspecialchars( strip_tags( $_REQUEST['fromentdate'] ) ) ) );
	$toentdate = $db->safesql( trim( htmlspecialchars( strip_tags( $_REQUEST['toentdate'] ) ) ) );

	$search_news_f = intval( $_REQUEST['search_news_f'] );
	$search_news_t = intval( $_REQUEST['search_news_t'] );
	$search_coms_f = intval( $_REQUEST['search_coms_f'] );
	$search_coms_t = intval( $_REQUEST['search_coms_t'] );

	if ( !$search_news_f ) $search_news_f = "";
	if ( !$search_news_t ) $search_news_t = "";
	if ( !$search_coms_f ) $search_coms_f = "";
	if ( !$search_coms_t ) $search_coms_t = "";

	if ( intval($_REQUEST['news_per_page']) > 0 ) $news_per_page = intval( $_REQUEST['news_per_page'] ); else $news_per_page = 50;

	echo <<<HTML
<div style="display:none" name="advancedadd" id="advancedadd">
<form method="post" action="" class="form-horizontal">
<input type="hidden" name="action" value="adduser">
<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
<input type="hidden" name="mod" value="editusers">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['user_auser']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">

							<div class="form-group">
							  <label class="control-label col-lg-2">{$lang['user_name']}</label>
							  <div class="col-lg-10">
								<input size="40" type="text" name="regusername">
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-lg-2">{$lang['user_pass']}</label>
							  <div class="col-lg-10">
								<input size="40" type="text" name="regpassword">
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-lg-2">{$lang['user_mail']}</label>
							  <div class="col-lg-10">
								<input size="40" type="text" name="regemail">
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-lg-2">{$lang['user_acc']}</label>
							  <div class="col-lg-10">
								<select class="uniform" name="reglevel">{$grouplist}</select>
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

	if( $_REQUEST['search_reglevel'] ) { $search_reglevel = $_REQUEST['search_reglevel']; $group_list = get_groups( $_REQUEST['search_reglevel'] ); }
	else $group_list = get_groups();

	if( $_REQUEST['search_banned'] == "yes" ) { $search_banned = "yes"; $ifch = "checked"; } else {$search_banned = ""; $ifch = "";}
	
	if( $_REQUEST['search_full_name'] ) { $search_full_name = 1; $ifsfn = "checked"; } else { $search_full_name = ""; $ifsfn = ""; }

	$search_order_user = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( ! empty( $_REQUEST['search_order_u'] ) ) {
		$search_order_user[$_REQUEST['search_order_u']] = 'selected';
		if ($_REQUEST['search_order_u'] == "desc" or $_REQUEST['search_order_u'] == "asc") $search_order_u = $_REQUEST['search_order_u'];
	} else {
		$search_order_user['----'] = 'selected';
	}
	$search_order_reg = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( ! empty( $_REQUEST['search_order_r'] ) ) {
		$search_order_reg[$_REQUEST['search_order_r']] = 'selected';
		if ($_REQUEST['search_order_r'] == "desc" or $_REQUEST['search_order_r'] == "asc") $search_order_r = $_REQUEST['search_order_r'];
	} else {
		$search_order_reg['----'] = 'selected';
	}
	$search_order_last = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( ! empty( $_REQUEST['search_order_l'] ) ) {
		$search_order_last[$_REQUEST['search_order_l']] = 'selected';
		if ($_REQUEST['search_order_l'] == "desc" or $_REQUEST['search_order_l'] == "asc") $search_order_l = $_REQUEST['search_order_l'];
	} else {
		$search_order_last['----'] = 'selected';
	}
	$search_order_news = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( ! empty( $_REQUEST['search_order_n'] ) ) {
		$search_order_news[$_REQUEST['search_order_n']] = 'selected';
		if ($_REQUEST['search_order_n'] == "desc" or $_REQUEST['search_order_n'] == "asc") $search_order_n = $_REQUEST['search_order_n'];
	} else {
		$search_order_news['----'] = 'selected';
	}
	$search_order_coms = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( ! empty( $_REQUEST['search_order_c'] ) ) {
		$search_order_coms[$_REQUEST['search_order_c']] = 'selected';
		if ($_REQUEST['search_order_c'] == "desc" or $_REQUEST['search_order_c'] == "asc") $search_order_c = $_REQUEST['search_order_c'];
	} else {
		$search_order_coms['----'] = 'selected';
	}

	echo <<<HTML
<form name="searchform" id="searchform" method="post" action="?mod=editusers&action=list" class="form-horizontal">
<input type="hidden" name="action" id="action" value="list">
<input type="hidden" name="search" id="search" value="search">
<input type="hidden" name="start_from" id="start_from" value="{$start_from}">
<input type="hidden" name="mod" id="mod" value="editusers">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['user_se']}</div>
	<ul class="box-toolbar">
      <li class="toolbar-link">
          <a href="javascript:ShowOrHide('advancedadd');"><i class="icon-plus"></i> {$lang['user_auser']}</a>
      </li>
    </ul>
  </div>
  <div class="box-content">

	<div class="row box-section">

	  <div class="col-md-5">
							<div class="form-group">
							  <label class="control-label col-lg-2">{$lang['user_name']}</label>
							  <div class="col-lg-10">
								<input size="40" type="text" name="search_name" id="search_name" value="{$search_name}">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_user']}" >?</span>
								<div style="padding-top:5px;"><input class="icheck" type="checkbox" id="search_full_name" name="search_full_name" value="1" {$ifsfn}><label for="search_full_name">{$lang['search_full_name']}</label></div>
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-lg-2">{$lang['user_mail']}</label>
							  <div class="col-lg-10">
								<input size="40" type="text" name="search_mail" id="search_mail" value="{$search_mail}">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_mail']}" >?</span>
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-lg-2">{$lang['user_banned']}</label>
							  <div class="col-lg-10">
								<input class="icheck" type="checkbox" name="search_banned" id="search_banned" value="yes" $ifch>
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-lg-2">{$lang['user_acc']}</label>
							  <div class="col-lg-10">
								<select class="uniform" name="search_reglevel" id="search_reglevel"><option selected value="0">{$lang['edit_all']}</option>{$group_list}</select>
							  </div>
							 </div>
	  </div>

	  <div class="col-md-7">
							<div class="form-group">
							  <label class="control-label col-lg-4">{$lang['edit_regdate']}</label>
							  <div class="col-lg-8">
								{$lang['edit_fdate']}&nbsp;<input data-rel="calendardate" type="text" name="fromregdate" id="fromregdate" size="17" maxlength="16" value="{$fromregdate}">
								{$lang['edit_tdate']}&nbsp;<input data-rel="calendardate" type="text" name="toregdate" id="toregdate" size="17" maxlength="16" value="{$toregdate}">
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-lg-4">{$lang['edit_entedate']}</label>
							  <div class="col-lg-8">
								{$lang['edit_fdate']}&nbsp;<input data-rel="calendardate" type="text" name="fromentdate" id="fromentdate" size="17" maxlength="16" value="{$fromentdate}">
								{$lang['edit_tdate']}&nbsp;<input data-rel="calendardate" type="text" name="toentdate" id="toentdate" size="17" maxlength="16" value="{$toentdate}">
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-lg-4">{$lang['edit_newsnum']}</label>
							  <div class="col-lg-8">
								{$lang['edit_fdate']}&nbsp;<input type="text" name="search_news_f" id="search_news_f" size="8" maxlength="7" value="{$search_news_f}">
								{$lang['edit_tdate']}&nbsp;<input type="text" name="search_news_t" id="search_news_t" size="8" maxlength="7" value="{$search_news_t}">
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-lg-4">{$lang['edit_comsnum']}</label>
							  <div class="col-lg-8">
								{$lang['edit_fdate']}&nbsp;<input type="text" name="search_coms_f" id="search_coms_f" size="8" maxlength="7" value="{$search_coms_f}">
								{$lang['edit_tdate']}&nbsp;<input type="text" name="search_coms_t" id="search_coms_t" size="8" maxlength="7" value="{$search_coms_t}">
							  </div>
							 </div>
							<div class="form-group">
							  <label class="control-label col-lg-4">{$lang['edit_upp']}</label>
							  <div class="col-lg-8">
								<input style="text-align:center;" type="text" name="news_per_page" id="news_per_page" size="24" maxlength="7" value="{$news_per_page}">
							  </div>
							 </div>

	  </div>

	</div>
	<div class="row box-section">
	{$lang['user_order']}
	</div>
	<div class="row box-section">
		<div class="col-md-2 col-xs-6">
		{$lang['user_name']}<br /><select class="uniform" name="search_order_u" id="search_order_u">
           <option {$search_order_user['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_user['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_user['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
		</div>
		<div class="col-md-2 col-xs-6">
		{$lang['user_reg']}<br /><select class="uniform" name="search_order_r" id="search_order_r">
           <option {$search_order_reg['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_reg['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_reg['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
		</div>
		<div class="col-md-2 col-xs-6">
		{$lang['user_last']}<br /><select class="uniform" name="search_order_l" id="search_order_l">
           <option {$search_order_last['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_last['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_last['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
		</div>
		<div class="col-md-2 col-xs-6">
		{$lang['user_news']}<br /><select class="uniform" name="search_order_n" id="search_order_n">
           <option {$search_order_news['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_news['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_news['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
		</div>
		<div class="col-md-4 col-xs-12">
		{$lang['user_coms']}<br /><select class="uniform" name="search_order_c" id="search_order_c">
           <option {$search_order_coms['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_coms['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_coms['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
		</div>
	</div>
	<div class="row box-section">
		<input type="submit" class="btn btn-blue" value="{$lang['b_find']}">&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="button" class="btn btn-red" value="{$lang['user_breset']}" onclick="javascript:clearform(document.searchform); return false;">&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="reset" class="btn btn-gray" value="{$lang['user_brestore']}">
   </div>
   </div>
</div>
</form>
HTML;

	$where = array ();

	if( ! empty( $_REQUEST['search'] ) ) {
		
		if( $search_full_name ) $where[] = "name='{$search_name}'";
		else $where[] = "name LIKE '%{$search_name}%'";
		
	}
	if( ! empty( $search_mail ) ) {
		$where[] = "email LIKE '%$search_mail%'";
	}
	if( ! empty( $search_banned ) ) {
		$search_banned = $db->safesql( $search_banned );
		$where[] = "banned='$search_banned'";
	}
	if( ! empty( $fromregdate ) ) {
		$where[] = "reg_date>='" . strtotime( $fromregdate ) . "'";
	}
	if( ! empty( $toregdate ) ) {
		$where[] = "reg_date<='" . strtotime( $toregdate ) . "'";
	}
	if( ! empty( $fromentdate ) ) {
		$where[] = "lastdate>='" . strtotime( $fromentdate ) . "'";
	}
	if( ! empty( $toentdate ) ) {
		$where[] = "lastdate<='" . strtotime( $toentdate ) . "'";
	}
	if( ! empty( $search_news_f ) ) {
		$search_news_f = intval( $search_news_f );
		$where[] = "news_num>='$search_news_f'";
	}
	if( ! empty( $search_news_t ) ) {
		$search_news_t = intval( $search_news_t );
		$where[] = "news_num<'$search_news_t'";
	}
	if( ! empty( $search_coms_f ) ) {
		$search_coms_f = intval( $search_coms_f );
		$where[] = "comm_num>='$search_coms_f'";
	}
	if( ! empty( $search_coms_t ) ) {
		$search_coms_t = intval( $search_coms_t );
		$where[] = "comm_num<'$search_coms_t'";
	}
	if( $search_reglevel ) {
		$search_reglevel = intval( $search_reglevel );
		$where[] = "user_group='$search_reglevel'";
	}

	$where = implode( " AND ", $where );
	if( ! $where ) {
		$where = "user_group < '4'";
		$hint_search = "<div class=\"well relative\"><span class=\"triangle-button green\"><i class=\"icon-bell\"></i></span>{$lang['hint_user']}</div>";
	} else $hint_search = "";

	$order_by = array ();

	if( ! empty( $search_order_u ) ) {
		$order_by[] = "name $search_order_u";
	}
	if( ! empty( $search_order_r ) ) {
		$order_by[] = "reg_date $search_order_r";
	}
	if( ! empty( $search_order_l ) ) {
		$order_by[] = "lastdate $search_order_l";
	}
	if( ! empty( $search_order_n ) ) {
		$order_by[] = "news_num $search_order_n";
	}
	if( ! empty( $search_order_c ) ) {
		$order_by[] = "comm_num $search_order_c";
	}

	$order_by = implode( ", ", $order_by );
	if( ! $order_by ) {
		$order_by = "reg_date asc";
	}

	// ------ Запрос к базе
	$query_count = "SELECT COUNT(*) as count FROM " . USERPREFIX . "_users WHERE $where";
	$result_count = $db->super_query( $query_count );
	$all_count_news = $result_count['count'];

	echo <<<HTML
<script language="javascript" type="text/javascript">
<!--
function cdelete(id){
	    DLEconfirm( '{$lang['comm_alldelconfirm']}', '{$lang['p_confirm']}', function () {
			document.location='?mod=editusers&action=dodelcomments&user_hash={$dle_login_hash}&id=' + id + '';
		} );
}

function ndelete(id){
	    DLEconfirm( '{$lang['news_alldelconfirm']}', '{$lang['p_confirm']}', function () {
			document.location='?mod=editusers&action=dodelnews&user_hash={$dle_login_hash}&id=' + id + '';
		} );
}

function ckeck_uncheck_all() {
    var frm = document.editusers;
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

//-->
</script>
{$hint_search}
<form action="" method="post" name="editusers">
<input type="hidden" name=mod value="mass_user_actions">
<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['user_list']} ({$all_count_news})</div>
  </div>
  <div class="box-content">

    <table class="table table-normal table-hover">
      <thead>
      <tr>
        <td>{$lang['user_name']}</td>
        <td class="news-list-tab">{$lang['user_reg']}</td>
        <td class="news-list-tab" >{$lang['user_last']}</td>
        <td class="news-list-tab" style="width: 40px"><i class="icon-file-alt  tip" data-original-title="{$lang['rss_maxnews']}"></i></td>
        <td class="news-list-tab" style="width: 40px"><i class="icon-comment-alt tip" data-original-title="{$lang['edit_com']}"></i></td>
		<td style="width: 250px">{$lang['user_acc']}</td>
        <td style="width: 130px">{$lang['user_action']}</td>
        <td style="width: 40px"><input type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all()"></td>
      </tr>
      </thead>
	  <tbody>
HTML;

	$start_from = intval( $_REQUEST['start_from'] );
	$i = $start_from;

	// ------ Запрос к базе
	$db->query( "SELECT * FROM " . USERPREFIX . "_users WHERE {$where} ORDER BY {$order_by} LIMIT {$start_from},{$news_per_page}" );

	while ( $row = $db->get_row() ) {
		$i ++;

		$last_login = langdate( 'd/m/Y - H:i', $row['lastdate'] );
		$user_name = "<a class=\"status-info\" href=\"{$config['http_home_url']}index.php?subaction=userinfo&user=" . urlencode( $row['name'] ) . "\" target=\"_blank\">" . $row[name] . "</a>";
		if( $row[news_num] == 0 ) {
			$news_link = "$row[news_num]";
		} else {
			
			$row['name'] = urlencode( $row['name'] );
			
			if( $config['allow_alt_url'] ) {
				
				$url_user = $config['http_home_url']."user/".$row['name']."/news/";
				
			} else {
				
				$url_user = $config['http_home_url']."index.php?subaction=allnews&user=".$row['name'];
				
			}
			
			$news_link = <<<HTML
				<div class="btn-group">
				<a href="#" target="_blank" data-toggle="dropdown" data-original-title="{$lang['rss_maxnews']}" class="status-info tip"><b>{$row['news_num']}</b></a>
				  <ul class="dropdown-menu text-left">
				   <li><a href="{$url_user}" target="_blank"><i class="icon-eye-open"></i> {$lang['comm_view']}</a></li>
				   <li class="divider"></li>
				   <li><a onclick="javascript:ndelete('{$row['user_id']}'); return(false)" href="?mod=editusers&action=dodelnews&user_hash={$dle_login_hash}&id={$row['user_id']}"><i class="icon-trash"></i> {$lang['comm_del']}</a></li>
				  </ul>
				</div>
HTML;
			
		}

		if( $row[comm_num] == 0 ) {
			$comms_link = $row['comm_num'];
		} else {

			$comms_link = <<<HTML
				<div class="btn-group">
				<a href="#" target="_blank" data-toggle="dropdown" data-original-title="{$lang['edit_com']}" class="status-info tip"><b>{$row['comm_num']}</b></a>
				  <ul class="dropdown-menu text-left">
				   <li><a href="{$config['http_home_url']}index.php?do=lastcomments&userid={$row['user_id']}" target="_blank"><i class="icon-eye-open"></i> {$lang['comm_view']}</a></li>
				   <li class="divider"></li>
				   <li><a onclick="javascript:cdelete('{$row['user_id']}'); return(false)" href="?mod=editusers&action=dodelcomments&user_hash={$dle_login_hash}&id={$row['user_id']}"><i class="icon-trash"></i> {$lang['comm_del']}</a></li>
				  </ul>
				</div>
HTML;
		}

		$user_delete = "<li class=\"divider\"></li><li><a onclick=\"javascript:confirmdelete('" . $row[user_id] . "', '" . $row[name] . "'); return(false)\" href=\"#\"><i class=\"icon-trash\"></i> {$lang['user_del']}</a></li>";

		if( $row['banned'] == 'yes' ) $user_level = "<font color=\"red\">" . $lang['user_ban'] . "</font>";
		else $user_level = $user_group[$row['user_group']]['group_name'];

		if( $row['user_group'] == 1 ) $user_delete = "";
		
		$pmname = urlencode($row['name']);

		$menu_link = <<<HTML
        <div class="btn-group">
          <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">{$lang['filter_action']} <span class="caret"></span></button>
          <ul class="dropdown-menu text-left pull-right">
            <li><a onclick="javascript:popupedit('{$row['user_id']}'); return(false);" href="#"><i class="icon-pencil"></i> {$lang['user_edit']}</a></li>
            <li><a href="{$config['http_home_url']}index.php?do=feedback&user={$row['user_id']}" target="_blank"><i class="icon-envelope"></i> {$lang['bb_b_mail']}</a></li>
			<li><a href="{$config['http_home_url']}index.php?do=pm&doaction=newpm&username={$pmname}" target="_blank"><i class="icon-user"></i> {$lang['nl_pm']}</a></li>
            {$user_delete}
          </ul>
        </div>
HTML;

		if ( count(explode("@", $row['foto'])) == 2 ) {
			$avatar = '//www.gravatar.com/avatar/' . md5(trim($row['foto'])) . '?s=' . intval($user_group[$row['user_group']]['max_foto']);
		} else {
			
			if( $row['foto'] ) {
				
				if (strpos($row['foto'], "//") === 0) $avatar = "http:".$row['foto']; else $avatar = $row['foto'];

				$avatar = @parse_url ( $avatar );

				if( $avatar['host'] ) {
					
					$avatar = $row['foto'];
					
				} else $avatar = $config['http_home_url'] . "uploads/fotos/" . $row['foto'];
			
			} else $avatar = "engine/skins/images/noavatar.png";
		}

		echo "<tr>
        <td><span><img class=\"menu-avatar\" src=\"{$avatar}\" /> <span>{$user_name}</span></span></td>
        <td class=\"news-list-tab\" align=\"center\">";
		echo (langdate( "d/m/Y - H:i", $row['reg_date'] ));
		echo "</td>
        <td class=\"news-list-tab\" align=\"center\">$last_login</td>
        <td class=\"news-list-tab\" align=\"center\">
        {$news_link}</td>
        <td class=\"news-list-tab\" align=\"center\">
        {$comms_link}</td>
        <td>{$user_level}</td>
        <td>{$menu_link}</td>
		<td align=\"center\"><input name=\"selected_users[]\" value=\"{$row['user_id']}\" type=\"checkbox\"></td>
        </tr>";
	}
	$db->free();

	// pagination

	$npp_nav = "";


	if( $all_count_news > $news_per_page ) {

		if( $start_from > 0 ) {
			$previous = $start_from - $news_per_page;
			$npp_nav .= "<li><a onclick=\"javascript:list_submit($previous); return(false)\" href=#> &lt;&lt; </a></li>";
		}

		$enpages_count = @ceil( $all_count_news / $news_per_page );
		$enpages_start_from = 0;
		$enpages = "";

		if( $enpages_count <= 10 ) {

			for($j = 1; $j <= $enpages_count; $j ++) {

				if( $enpages_start_from != $start_from ) {

					$enpages .= "<li><a onclick=\"javascript:list_submit($enpages_start_from); return(false);\" href=\"#\">$j</a></li>";

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

				$enpages .= "<li><a onclick=\"javascript:list_submit(0); return(false);\" href=\"#\">1</a></li> <li><span>...</span></li>";

			}

			for($j = $start; $j <= $end; $j ++) {

				if( $enpages_start_from != $start_from ) {

					$enpages .= "<li><a onclick=\"javascript:list_submit($enpages_start_from); return(false);\" href=\"#\">$j</a></li>";

				} else {

					$enpages .= "<li class=\"active\"><span>$j</span></li>";
				}

				$enpages_start_from += $news_per_page;
			}

			$enpages_start_from = ($enpages_count - 1) * $news_per_page;
			$enpages .= "<li><span>...</span></li><li><a onclick=\"javascript:list_submit($enpages_start_from); return(false);\" href=\"#\">$enpages_count</a></li>";

			$npp_nav .= $enpages;

		}

		if( $all_count_news > $i ) {
			$how_next = $all_count_news - $i;
			if( $how_next > $news_per_page ) {
				$how_next = $news_per_page;
			}
			$npp_nav .= "<li><a onclick=\"javascript:list_submit($i); return(false)\" href=#> &gt;&gt; </a></li>";
		}

		$npp_nav = "<ul class=\"pagination pagination-sm\">".$npp_nav."</ul>";

	}

	// pagination

	echo <<<HTML
	  </tbody>
	</table>
   </div>
	<div class="box-footer padded">
		<div class="pull-left">
		{$npp_nav}
		</div>
		<div class="pull-right">
		<select class="uniform" name="action">
<option value="">{$lang['edit_selact']}</option>
<option value="mass_move_to_group">{$lang['massusers_group']}</option>
<option value="mass_move_to_ban">{$lang['massusers_banned']}</option>
<option value="mass_delete_comments">{$lang['massusers_comments']}</option>
<option value="mass_delete_pm">{$lang['masspm_delete']}</option>
<option value="mass_delete">{$lang['massusers_delete']}</option>
</select>&nbsp;<input class="btn btn-gold" type="submit" value="{$lang['b_start']}">
		</div>
	</div>
</div>
</form>
HTML;

	echofooter();
}
// ********************************************************************************
// Добавление пользователя
// ********************************************************************************
elseif( $action == "adduser" ) {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {

		die( "Hacking attempt! User not found" );

	}

	if( ! $_POST['regusername'] ) {
		msg( "error", $lang['user_err'], $lang['user_err_1'], "javascript:history.go(-1)" );
	}

	if( preg_match( "/[\||\'|\<|\>|\[|\]|\"|\!|\?|\$|\@|\#|\/|\\\|\&\~\*\{\+]/", $_POST['regusername'] ) ) msg( "error", $lang['user_err'], $lang['user_err_6'], "javascript:history.go(-1)" );

	if( ! $_POST['regpassword'] ) {
		msg( "error", $lang['user_err'], $lang['user_err_2'], "javascript:history.go(-1)" );
	}
	if( empty( $_POST['regemail'] ) OR @count(explode("@", $_POST['regemail'])) != 2) {
		msg( "error", $lang['user_err_1'], $lang['user_err_1'], "javascript:history.go(-1)" );
	}

	$regusername = $db->safesql($_POST['regusername']);

	$not_allow_symbol = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", "/", "¬", "#", ";", ":", "~", "[", "]", "{", "}", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"', "'", " " );
	$regemail = $db->safesql(trim( str_replace( $not_allow_symbol, '', strip_tags( stripslashes( $_POST['regemail'] ) ) ) ) );

	$row = $db->super_query( "SELECT name, email FROM " . USERPREFIX . "_users WHERE name = '{$regusername}' OR email = '{$regemail}'" );

	if( $row['email'] == $regemail ) {
		msg( "error", $lang['user_err'], $lang['user_err_4'], "javascript:history.go(-1)" );
	}

	if( $row['name'] ) {
		msg( "error", $lang['user_err'], $lang['user_err_3'], "javascript:history.go(-1)" );
	}

	$add_time = time();
	$regpassword = md5( md5( $_POST['regpassword'] ) );

	$reglevel = intval( $_POST['reglevel'] );

	if ( $member_id['user_group'] != 1 AND $reglevel < 2 ) $reglevel = 4;

	$db->query( "INSERT INTO " . USERPREFIX . "_users (name, password, email, user_group, reg_date, lastdate, info, signature, favorites, xfields) values ('$regusername', '$regpassword', '$regemail', '$reglevel', '$add_time', '$add_time','','','','')" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '63', '{$regusername}')" );
	clear_cache();

	msg( "info", $lang['user_addok'], "$lang[user_ok] <b>$regusername</b> $lang[user_ok_1] <b>{$user_group[$reglevel]['group_name']}</b>", "?mod=editusers&action=list" );
}
// ********************************************************************************
// Редактирование пользователя
// ********************************************************************************
elseif( $action == "edituser" ) {

	if( isset( $_REQUEST['user'] ) ) {

		$user = $db->safesql( strip_tags( urldecode( $_GET['user'] ) ) );

		$skin = trim( totranslit($_REQUEST['skin'], false, false) );

		if ( $skin ) $skin = "&skin=".$skin;

		if( $user != "" ) {

			$row = $db->super_query( "SELECT user_id FROM " . USERPREFIX . "_users WHERE name = '$user'" );

			if( ! $row['user_id'] ) die( "User not found" );

			header( "Location: ?mod=editusers&action=edituser&id=" . $row['user_id'].$skin );
			die( "User not found" );

		}
	}

	$row = $db->super_query( "SELECT " . USERPREFIX . "_users.*, " . USERPREFIX . "_banned.days, " . USERPREFIX . "_banned.descr, " . USERPREFIX . "_banned.date as banned_date FROM " . USERPREFIX . "_users LEFT JOIN " . USERPREFIX . "_banned ON " . USERPREFIX . "_users.user_id=" . USERPREFIX . "_banned.users_id WHERE user_id = '$id'" );

	if( ! $row['user_id'] ) die( "User not found" );

	if ($member_id['user_group'] != 1 AND $row['user_group'] == 1 )
		die( $lang['edit_not_admin'] );

	include_once ENGINE_DIR . '/classes/parse.class.php';

	$parse = new ParseFilter();
	$parse->safe_mode = true;

	$row['fullname'] = $parse->decodeBBCodes( $row['fullname'], false );
	$row['land'] = $parse->decodeBBCodes( $row['land'], false );
	$row['info'] = $parse->decodeBBCodes( $row['info'], false );
	$row['signature'] = $parse->decodeBBCodes( $row['signature'], false );
	$row['descr'] = $parse->decodeBBCodes( $row['descr'], false );

	$skin = trim( totranslit($_REQUEST['skin'], false, false) );

	if ( $skin ) {

		$css_path = $config['http_home_url']."templates/".$skin."/frame.css";

	} else {

		$css_path = "engine/skins/stylesheets/frame.css";

	}

	echo <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
<meta content="text/html; charset={$config['charset']}" http-equiv="content-type" />
<title>{$lang['user_edhead']}</title>
<link rel="stylesheet" type="text/css" href="{$css_path}">
<link rel="stylesheet" type="text/css" media="all" href="engine/classes/calendar/calendar.css" />
<script type="text/javascript" src="engine/classes/js/jquery.js"></script>
<script type="text/javascript" src="engine/classes/calendar/calendar.js"></script>
</head>
<body>
<script language="javascript" type="text/javascript">
<!--

var cal_language   = {en:{months:['{$lang['January']}','{$lang['February']}','{$lang['March']}','{$lang['April']}','{$lang['May']}','{$lang['June']}','{$lang['July']}','{$lang['August']}','{$lang['September']}','{$lang['October']}','{$lang['November']}','{$lang['December']}'],dayOfWeek:["{$langdate['Sun']}", "{$langdate['Mon']}", "{$langdate['Tue']}", "{$langdate['Wed']}", "{$langdate['Thu']}", "{$langdate['Fri']}", "{$langdate['Sat']}"]}};

function confirmDelete(url){

	parent.DLEconfirm( '{$lang['user_deluser']}', '{$lang['p_confirm']}', function () {

		document.location='{$_SERVER['PHP_SELF']}?mod=editusers&action=dodeleteuser&popup=yes&id={$row['user_id']}&user_hash='+url;

	} );

}

//-->
</script>
HTML;

	$last_date = langdate( "j F Y - H:i", $row['lastdate'] );
	$reg_date = langdate( "j F Y - H:i", $row['reg_date'] );
	if( $row['time_limit'] != "" ) $row['time_limit'] = date( "Y-m-d H:i", $row['time_limit'] );

	if ( count(explode("@", $row['foto'])) == 2 ) {

		$avatar = '//www.gravatar.com/avatar/' . md5(trim($row['foto'])) . '?s=' . intval($user_group[$row['user_group']]['max_foto']);
		$gravatar = $row['foto'];
		
	} else {

		if( $row['foto'] ) {
			
			if (strpos($row['foto'], "//") === 0) $avatar = "http:".$row['foto']; else $avatar = $row['foto'];

			$avatar = @parse_url ( $avatar );

			if( $avatar['host'] ) {
				
				$avatar = $row['foto'];
				
			} else $avatar = $config['http_home_url'] . "uploads/fotos/" . $row['foto'];


		} else {

			$avatar = "engine/skins/images/noavatar.png";

		}

		$gravatar = "";
	}

	$xfieldsaction = "admin";
	$xfieldsid = $row['xfields'];
	include (ENGINE_DIR . '/inc/userfields.php');

	echo <<<HTML
<form name="saveuserform" id="saveuserform" action="" method="post" enctype="multipart/form-data">
<table width="98%">
    <tr>
        <td width="150" style="padding:4px;">{$lang['user_name']}</td>
        <td>{$row['name']}</td>
        <td rowspan="6" valign="top" align="right"><img src="{$avatar}" border="0" style="max-width:100px;max-height:100px;" /></td>
    </tr>
    <tr>
        <td style="padding:4px;">IP:</td>
        <td><a href="#" onclick="parent.document.location='?mod=iptools&ip={$row['logged_ip']}'; return false;">{$row['logged_ip']}</a></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_news']}</td>
        <td>{$row['news_num']}</td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_last']}</td>
        <td>{$last_date}</td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_reg']}</td>
        <td>{$reg_date}</td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_mail']}</td>
        <td><input size="20" class="edit bk" name="editmail" value="{$row['email']}" /></td>
    </tr>
    <tr>
        <td colspan="3"><hr></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_newlogin']}</td>
        <td colspan="2"><input size="20" name="editlogin" class="edit bk"></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_newpass']}</td>
        <td colspan="2"><input size="20" name="editpass" class="edit bk"></td>
    </tr>
    <tr>
        <td colspan="3"><hr></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_acc']}</td>
        <td colspan="2"><select name="editlevel">
HTML;

	echo get_groups( $row[user_group] );

	if( $row['banned'] == "yes" ) $ifch = "checked";
	$row['days'] = intval( $row['days'] );

	if( $row['banned'] == "yes" and $row['days'] ) $endban = $lang['ban_edate'] . " " . langdate( "j F Y H:i", $row['banned_date'] );
	else $endban = "";

	$restricted_selected = array (0 => '', 1 => '', 2 => '', 3 => '' );
	$restricted_selected[$row['restricted']] = 'selected';

	if( $row['restricted'] and $row['restricted_days'] ) $end_restricted = $lang['edit_tdate'] . " " . langdate( "j M Y H:i", $row['restricted_date'] );
	else $end_restricted = "";

	if( $row['restricted'] ) $lang['restricted_none'] = $lang['restricted_clear'];

	echo <<<HTML
</select></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_gtlimit']}</td>
        <td colspan="2"><input data-rel="calendardatetime" size="20" name="time_limit" id="time_limit" class="edit bk" value="{$row['time_limit']}"></td>
    </tr>
    <tr>
        <td colspan="3"><hr></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_banned']}</td>
        <td colspan="2"><input type="checkbox" name="banned" value="yes" $ifch></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['ban_date']}</td>
        <td colspan="2"><input size="5" name="banned_date" class="edit bk" value="{$row['days']}"> {$endban}</td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['ban_descr']}</td>
        <td colspan="2"><textarea style="width:100%; height:60px;" name="banned_descr" class="bk">{$row['descr']}</textarea></td>
    </tr>
    <tr>
        <td colspan="3"><hr></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['restricted']}</td>
        <td colspan="2"><select name="restricted"><option value="0" $restricted_selected[0]>{$lang['restricted_none']}</option>
<option value="1" $restricted_selected[1]>{$lang['restricted_news']}</option>
<option value="2" $restricted_selected[2]>{$lang['restricted_comm']}</option>
<option value="3" $restricted_selected[3]>{$lang['restricted_all']}</option>
</select></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['restricted_date']}</td>
        <td colspan="2"><input size="5" name="restricted_days" class="edit bk" value="{$row['restricted_days']}"> {$end_restricted}</td>
    </tr>
    <tr>
        <td colspan="3"><hr></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_del_comments']}</td>
        <td colspan="2"><input type="checkbox" name="del_comments" value="yes" /></td>
    </tr>
    <tr>
        <td colspan="3"><div class="hr_line"></div></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['opt_fullname']}</td>
        <td colspan="2"><input style="width:100%;" name="editfullname" value="{$row['fullname']}" class="edit bk"></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['opt_land']}</td>
        <td colspan="2"><input style="width:100%;" name="editland" value="{$row['land']}" class="edit bk"></td>
    </tr>

    <tr>
        <td colspan="3"><hr></td>
    </tr>
    <tr>
        <td style="padding:4px;">Gravatar:</td>
        <td colspan="2"><input size="20" name="gravatar" value="{$gravatar}" class="edit bk"></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_avatar']}</td>
        <td colspan="2"><input type="file" name="image" style="width:304px;" class="edit" /></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['user_del_avatar']}</td>
        <td colspan="2"><input type="checkbox" name="del_foto" value="yes" /></td>
    </tr>
    <tr>
        <td colspan="3"><hr></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['extra_minfo']}</td>
        <td colspan="2" style="padding-bottom:4px;"><textarea style="width:100%; height:70px;" name="editinfo" class="bk">{$row['info']}</textarea></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['extra_signature']}</td>
        <td colspan="2"><textarea style="width:100%; height:70px;" name="editsignature" class="bk">{$row['signature']}</textarea></td>
    </tr>
	{$output}
    <tr>
        <td colspan="3">&nbsp;
    <input type="hidden" name="id" value="{$id}">
    <input type="hidden" name="mod" value="editusers">
    <input type="hidden" name="user_hash" value="$dle_login_hash">
    <input type="hidden" name="action" value="doedituser">
	<input type="hidden" name="prev_restricted" value="{$row['restricted_days']}"></td>
    </tr>
</table>
</form>
HTML;

	echo <<<HTML
</body>

</html>
HTML;

}
// ********************************************************************************
// Сохранение отредактированной информации
// ********************************************************************************
elseif( $action == "doedituser" ) {

	if( ! $id ) {
		die( $lang['user_nouser'] );
	}

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {

		die( "Hacking attempt! User not found" );

	}

	$row = $db->super_query( "SELECT user_id, name, user_group, email, foto FROM " . USERPREFIX . "_users WHERE user_id = '$id'" );

	if( ! $row['user_id'] ) die( "User not found" );

	if ($member_id['user_group'] != 1 AND $row['user_group'] == 1 )
		die( $lang['edit_not_admin'] );

	$editlevel = intval( $_POST['editlevel'] );

	if ($member_id['user_group'] != 1 AND $editlevel < 2 )
		die( $lang['admin_not_access'] );

    if( $row['user_id'] == $member_id['user_id'] AND $editlevel != $row['user_group'] ) $editlevel = $row['user_group'];

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '64', '{$row['name']}')" );

	include_once ENGINE_DIR . '/classes/parse.class.php';

	$parse = new ParseFilter();
	$parse->safe_mode = true;

	$editlogin = $db->safesql( $parse->process( $_POST['editlogin'] ) );
	$editfullname = $db->safesql( $parse->process( $_POST['editfullname'] ) );

	$editland = $db->safesql( $parse->process( $_POST['editland'] ) );
	$editinfo = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['editinfo'] ), false ) );
	$editsignature = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['editsignature'] ), false ) );
	$time_limit = trim( $_POST['time_limit'] ) ? strtotime( $_POST['time_limit'] ) : "";

	$not_allow_symbol = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", "/", "¬", "#", ";", ":", "~", "[", "]", "{", "}", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"', "'", " " );
	$editmail = $db->safesql(trim( str_replace( $not_allow_symbol, '', strip_tags( stripslashes( $_POST['editmail'] ) ) ) ) );

	if( empty( $editmail ) OR strlen( $editmail ) > 50 OR @count(explode("@", $editmail)) != 2) die( "E-mail not correct" );
	if( preg_match( "/[\||\'|\<|\>|\[|\]|\"|\!|\?|\$|\@|\#|\/|\\\|\&\~\*\{\+]/", $editlogin ) ) die( "New login not correct" );

	if ($editmail != $row['email']) {

		if ( $db->num_rows( $db->query( "SELECT user_id FROM " . USERPREFIX . "_users WHERE email = '$editmail'" ) ) ) {
			header( "Location: {$_SERVER['REQUEST_URI']}" );
			die();
		}

		$db->query( "UPDATE " . PREFIX . "_subscribe SET email='{$editmail}' WHERE user_id = '{$id}'" );

	}

	if ( $_POST['banned'] ) $banned = "yes";

	if( ! $user_group[$editlevel]['time_limit'] ) $time_limit = "";

	if ( $_POST['gravatar'] ) {

		$gravatar = $db->safesql(trim( str_replace( $not_allow_symbol, '', strip_tags( stripslashes( $_POST['gravatar'] ) ) ) ) );

		if ( count(explode("@", $gravatar)) == 2 ) {
			$db->query( "UPDATE " . USERPREFIX . "_users SET foto='{$gravatar}' WHERE user_id = '{$id}'" );
		} else $db->query( "UPDATE " . USERPREFIX . "_users set foto='' WHERE user_id = '{$id}'" );

	} else {

		if (count(explode("@", $row['foto'])) == 2) $db->query( "UPDATE " . USERPREFIX . "_users set foto='' WHERE user_id = '{$id}'" );
	}

	$image = $_FILES['image']['tmp_name'];
	$image_name = $_FILES['image']['name'];
	$image_size = $_FILES['image']['size'];
	$img_name_arr = explode( ".", $image_name );
	$type = totranslit(end( $img_name_arr ));

	if( $image_name != "" ) $image_name = totranslit( stripslashes( $img_name_arr[0] ) ) . "." . $type;

	if( stripos ( $image_name, "php" ) !== false ) die("Hacking attempt!");

	if( is_uploaded_file( $image ) ) {

		if( $image_size < 100000 ) {

			$allowed_extensions = array ("jpg", "png", "jpe", "jpeg", "gif" );

			if( in_array( $type, $allowed_extensions ) AND $image_name ) {
				include_once ENGINE_DIR . '/classes/thumb.class.php';

				$res = @move_uploaded_file( $image, ROOT_DIR . "/uploads/fotos/" . $id . "." . $type );

				if( $res ) {

					@chmod( ROOT_DIR . "/uploads/fotos/" . $id . "." . $type, 0666 );
					$thumb = new thumbnail( ROOT_DIR . "/uploads/fotos/" . $id . "." . $type );

					$thumb->size_auto( $user_group[$member_id['user_group']]['max_foto'] );
					$thumb->jpeg_quality( $config['jpeg_quality'] );
					$thumb->save( ROOT_DIR . "/uploads/fotos/foto_" . $id . "." . $type );

					@chmod( ROOT_DIR . "/uploads/fotos/foto_" . $id . "." . $type, 0666 );

					if (strpos($config['http_home_url'], "//") === 0) $avatar_url = $config['http_home_url'];
					elseif (strpos($config['http_home_url'], "/") === 0) $avatar_url = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];
					else $avatar_url = $config['http_home_url'];
							
					$foto_name = $db->safesql( $avatar_url . "uploads/fotos/" ."foto_" . $id . "." . $type);

					$db->query( "UPDATE " . USERPREFIX . "_users SET foto='{$foto_name}' WHERE user_id='$id'" );

				}
			}

		}

		@unlink( ROOT_DIR . "/uploads/fotos/" . $id . "." . $type );
	}

	if( $_POST['del_foto'] == "yes" ) {
		$row = $db->super_query( "SELECT foto FROM " . USERPREFIX . "_users WHERE user_id='$id'" );
		$db->query( "UPDATE " . USERPREFIX . "_users set foto='' WHERE user_id='$id'" );
		
		$url = @parse_url ( $row['foto'] );
		$row['foto'] = basename($url['path']);
			
		@unlink( ROOT_DIR . "/uploads/fotos/" . totranslit($row['foto']) );
	}

	$xfieldsaction = "init_admin";
	include (ENGINE_DIR . '/inc/userfields.php');
	$filecontents = array ();

	if( ! empty( $postedxfields ) ) {
		foreach ( $postedxfields as $xfielddataname => $xfielddatavalue ) {
			if( ! $xfielddatavalue ) {
				continue;
			}

			$xfielddatavalue = $db->safesql( $parse->BB_Parse( $parse->process( $xfielddatavalue ), false ) );

			$xfielddataname = $db->safesql( $xfielddataname );

			$xfielddataname = str_replace( "|", "&#124;", $xfielddataname );
			$xfielddatavalue = str_replace( "|", "&#124;", $xfielddatavalue );
			$filecontents[] = "$xfielddataname|$xfielddatavalue";
		}

		$filecontents = implode( "||", $filecontents );
	} else
		$filecontents = '';

	$sql_update = "UPDATE " . USERPREFIX . "_users SET user_group='$editlevel', banned='$banned', land='$editland', info='$editinfo', signature='$editsignature', email='$editmail', fullname='$editfullname', time_limit='$time_limit', xfields='$filecontents'";

	if( trim( $editlogin ) != "" ) {

		$row = $db->super_query( "SELECT user_id FROM " . USERPREFIX . "_users WHERE name='$editlogin'" );

		if( ! $row['user_id'] ) {

			$row = $db->super_query( "SELECT name FROM " . USERPREFIX . "_users WHERE user_id='$id'" );
			$db->query( "UPDATE " . PREFIX . "_post SET autor='$editlogin' WHERE autor='{$row['name']}'" );
			$db->query( "UPDATE " . PREFIX . "_comments SET autor='$editlogin' WHERE autor='{$row['name']}' AND is_register='1'" );
			$db->query( "UPDATE " . USERPREFIX . "_pm SET user_from='$editlogin' WHERE user_from='{$row['name']}'" );
			$db->query( "UPDATE " . PREFIX . "_vote_result SET name='$editlogin' WHERE name='{$row['name']}'" );
			$db->query( "UPDATE " . PREFIX . "_images SET author='$editlogin' WHERE author='{$row['name']}'" );
			$db->query( "UPDATE " . PREFIX . "_files SET author='$editlogin' WHERE author='{$row['name']}'" );

			$sql_update .= ", name='$editlogin'";
		} else
			msg( "error", $lang['addnews_error'], $lang['user_edit_found'], "javascript:history.go(-1)" );
	}

	if( $_POST['restricted'] ) {

		$restricted = intval( $_POST['restricted'] );
		$restricted_days = intval( $_POST['restricted_days'] );

		$sql_update .= ", restricted='$restricted'";

		if( $restricted_days != $_POST['prev_restricted'] ) {

			$restricted_date = time();
			$restricted_date = $restricted_days ? $restricted_date + ($restricted_days * 60 * 60 * 24) : '';

			$sql_update .= ", restricted_days='$restricted_days', restricted_date='$restricted_date'";

		}

	} else {

		$sql_update .= ", restricted='0', restricted_days='0', restricted_date=''";

	}

	if( trim( $_POST['editpass'] ) != "" ) {
		$editpass = md5( md5( $_POST['editpass'] ) );
		$sql_update .= ", password='$editpass'";

		$db->query( "UPDATE " . USERPREFIX . "_social_login SET password='" . md5( $_POST['editpass'] ) . "' WHERE uid='{$id}'" );
	}

	$sql_update .= " WHERE user_id='{$id}'";

	$db->query( $sql_update );

	if( $banned ) {
		$banned_descr = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['banned_descr'] ), false ) );
		$this_time = time();
		$banned_date = intval( $_POST['banned_date'] );
		$this_time = $banned_date ? $this_time + ($banned_date * 60 * 60 * 24) : 0;

		$row = $db->super_query( "SELECT users_id, days FROM " . USERPREFIX . "_banned WHERE users_id = '$id'" );

		if( !$row['users_id'] ) {

			$db->query( "INSERT INTO " . USERPREFIX . "_banned (users_id, descr, date, days) values ('$id', '$banned_descr', '$this_time', '$banned_date')" );

		} else {

			if( $row['days'] != $banned_date ) $db->query( "UPDATE " . USERPREFIX . "_banned set descr='$banned_descr', days='$banned_date', date='$this_time' WHERE users_id = '$id'" );
			else $db->query( "UPDATE " . USERPREFIX . "_banned set descr='$banned_descr' WHERE users_id = '$id'" );

		}

		$db->query( "DELETE FROM " . PREFIX . "_subscribe WHERE user_id='{$id}'" );

		@unlink( ENGINE_DIR . '/cache/system/banned.php' );

	} else {

		$db->query( "DELETE FROM " . USERPREFIX . "_banned WHERE users_id = '$id'" );
		@unlink( ENGINE_DIR . '/cache/system/banned.php' );

	}

	if( $_POST['del_comments'] ) {

		$result = $db->query( "SELECT COUNT(*) as count, post_id FROM " . PREFIX . "_comments WHERE user_id='$id' AND is_register='1' AND approve='1' GROUP BY post_id" );

		while ( $row = $db->get_array( $result ) ) {

			$db->query( "UPDATE " . PREFIX . "_post SET comm_num=comm_num-{$row['count']} where id='{$row['post_id']}'" );

		}
		$db->free( $result );

		$db->query( "UPDATE " . USERPREFIX . "_users set comm_num='0' where user_id ='$id'" );
		$db->query( "DELETE FROM " . PREFIX . "_comments WHERE user_id='$id' AND is_register='1'" );

	}
	clear_cache();
	header( "Location: {$_SERVER['REQUEST_URI']}" );

}
// ********************************************************************************
// Удаление пользователя
// ********************************************************************************
elseif( $action == "dodeleteuser" ) {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {

		die( "Hacking attempt! User not found" );

	}

	if( ! $id ) {
		die( $lang['user_nouser'] );
	}

	if( $id == 1 ) {
		die( $lang['user_undel'] );
	}


	$row = $db->super_query( "SELECT user_id, user_group, name, foto FROM " . USERPREFIX . "_users WHERE user_id='$id'" );

	if( ! $row['user_id'] ) die( "User not found" );

	if ($member_id['user_group'] != 1 AND $row['user_group'] == 1 )
		die( $lang['user_undel'] );
	
	$url = @parse_url ( $row['foto'] );
	$row['foto'] = basename($url['path']);

	@unlink( ROOT_DIR . "/uploads/fotos/" . totranslit($row['foto']) );
	
	$db->query( "DELETE FROM " . USERPREFIX . "_pm WHERE user_from = '{$row['name']}' AND folder = 'outbox'" );
	$db->query( "DELETE FROM " . USERPREFIX . "_users WHERE user_id='{$id}'" );
	$db->query( "DELETE FROM " . USERPREFIX . "_social_login WHERE uid='{$id}'" );
	$db->query( "DELETE FROM " . USERPREFIX . "_banned WHERE users_id='{$id}'" );
	$db->query( "DELETE FROM " . USERPREFIX . "_pm WHERE user='{$id}'" );
	$db->query( "DELETE FROM " . USERPREFIX . "_ignore_list WHERE user='{$id}' OR user_from='{$row['name']}'");
	$db->query( "DELETE FROM " . PREFIX . "_subscribe WHERE user_id='{$id}'");
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '65', '{$row['name']}')" );

	clear_cache();

	if ($_GET['popup'] == "yes") {

		die( "<body><script type=\"text/javascript\">window.close();</script>".$lang[user_ok]." ".$lang[user_delok_1]."</body>" );

	} else {

		msg( "info", $lang['user_delok'], "$lang[user_ok] $user $lang[user_delok_1]", "?mod=editusers&action=list" );

	}

} elseif( $action == "dodelcomments" ) {
	
	if( ! $id ) {
		die( $lang['user_nouser'] );
	}

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {

		die( "Hacking attempt! User not found" );

	}

function deletecomments( $id ) {
	global $config, $db;
	
	$id = intval($id);

	$row = $db->super_query( "SELECT id, post_id FROM " . PREFIX . "_comments WHERE id = '{$id}'" );
	
	$db->query( "DELETE FROM " . PREFIX . "_comments WHERE id = '{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_comment_rating_log WHERE c_id = '{$id}'" );	
	
	$db->query( "UPDATE " . PREFIX . "_post SET comm_num=comm_num-1 WHERE id='{$row['post_id']}'" );

	if ( $config['tree_comments'] ) {

		$sql_result = $db->query( "SELECT id FROM " . PREFIX . "_comments WHERE parent = '{$id}'" );
	
		while ( $row = $db->get_row( $sql_result ) ) {
			deletecomments( $row['id'] );
		}

	}

}	
	
	$row = $db->super_query( "SELECT name FROM " . USERPREFIX . "_users WHERE user_id='{$id}'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '97', '".$db->safesql($row['name'])."')" );

	$result = $db->query( "SELECT id FROM " . PREFIX . "_comments WHERE user_id='{$id}' AND is_register='1' AND approve='1'" );

	while ( $row = $db->get_array( $result ) ) {

		deletecomments( $row['id'] );

	}
	$db->free( $result );

	$db->query( "UPDATE " . USERPREFIX . "_users SET comm_num='0' WHERE user_id ='$id'" );

	clear_cache();

	msg( "info", $lang['user_delok'], $lang['comm_alldel'], "?mod=editusers&action=list" );
	
} elseif( $action == "dodelnews" ) { 

	if( ! $id ) {
		die( $lang['user_nouser'] );
	}

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {

		die( "Hacking attempt! User not found" );

	}
	
	$row = $db->super_query( "SELECT name FROM " . USERPREFIX . "_users WHERE user_id='{$id}'" );	
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '98', '".$db->safesql($row['name'])."')" );

	$result = $db->query( "SELECT news_id FROM " . PREFIX . "_post_extras WHERE user_id='{$id}'" );

	while ( $row = $db->get_array( $result ) ) {
		
		$db->query( "DELETE FROM " . PREFIX . "_post WHERE id='{$row['news_id']}'" );
		$db->query( "DELETE FROM " . PREFIX . "_comments WHERE post_id='{$row['news_id']}'" );
		$db->query( "DELETE FROM " . PREFIX . "_poll WHERE news_id = '{$row['news_id']}'" );
		$db->query( "DELETE FROM " . PREFIX . "_poll_log WHERE news_id = '{$row['news_id']}'" );
		$db->query( "DELETE FROM " . PREFIX . "_post_log WHERE news_id = '{$row['news_id']}'" );
		$db->query( "DELETE FROM " . PREFIX . "_logs WHERE news_id = '{$row['news_id']}'" );
		$db->query( "DELETE FROM " . PREFIX . "_tags WHERE news_id = '{$row['news_id']}'" );
		$db->query( "DELETE FROM " . PREFIX . "_xfsearch WHERE news_id = '{$row['news_id']}'" );
		
		$db->query( "SELECT onserver FROM " . PREFIX . "_files WHERE news_id = '{$row['news_id']}'" );
		while ( $row_files = $db->get_row() ) {

			$url = explode( "/", $row_files['onserver'] );

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
		$db->free();
		$db->query( "DELETE FROM " . PREFIX . "_files WHERE news_id = '{$row['news_id']}'" );

		$row_images = $db->super_query( "SELECT images  FROM " . PREFIX . "_images WHERE news_id = '{$row['news_id']}'" );
		
		$listimages = explode( "|||", $row_images['images'] );
		
		if( $row_images['images'] != "" ) foreach ( $listimages as $dataimages ) {
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
		
		$db->query( "DELETE FROM " . PREFIX . "_images WHERE news_id = '{$row['news_id']}'" );

		
	}

	$db->query( "DELETE FROM " . PREFIX . "_post_extras WHERE user_id='{$id}'" );
	$db->query( "UPDATE " . USERPREFIX . "_users SET news_num='0' WHERE user_id ='{$id}'" );
	
	$db->free( $result );
	clear_cache();
	
	msg( "info", $lang['user_delok'], $lang['news_alldel'], "?mod=editusers&action=list" );
}

?>