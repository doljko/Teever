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
 Файл: logs.php
-----------------------------------------------------
 Назначение: Список действий в админпанели
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}
if( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['addnews_denied'], $lang['db_denied'] );
}


if ( file_exists( ROOT_DIR . '/language/' . $selected_language . '/adminlogs.lng' ) ) {
	require_once (ROOT_DIR . '/language/' . $selected_language . '/adminlogs.lng');
}

$start_from = intval( $_REQUEST['start_from'] );
$config['adminlog_maxdays'] = intval($config['adminlog_maxdays']);
$news_per_page = 50;

if( $start_from < 0 ) $start_from = 0;
if($config['adminlog_maxdays'] < 30 ) $config['adminlog_maxdays'] = 30;

$thisdate = $_TIME - ($config['adminlog_maxdays'] * 3600 * 24);

$db->query( "DELETE FROM " . USERPREFIX . "_admin_logs WHERE date < '{$thisdate}'" );

echoheader( "<i class=\"icon-globe\"></i>".$lang['opt_logs'], $lang['header_log_1']  );

if( $action == "auth") $lang['opt_logsc'] = $lang['admin_logs_auth'];

	echo <<<HTML
<script language="javascript" type="text/javascript">
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

function search_submit(prm){
	document.navi.start_from.value=prm;
	document.navi.submit();
	return false;
}

//-->
</script>
<div class="box">
  <div class="box-content">
	<div class="row box-section">


<div class="action-nav-normal action-nav-line">
  <div class="row action-nav-row">
  
    <div class="col-sm-1 action-nav-button" style="min-width:100px;">
      <a href="?mod=logs" class="tip" title="{$lang['admin_logs_all']}">
        <i class="icon-globe"></i>
        <span>{$lang['opt_b_1']}</span>
      </a>
    </div>

    <div class="col-sm-1 action-nav-button" style="min-width:110px;">
      <a href="?mod=logs&action=auth" class="tip" title="{$lang['admin_logs_auth']}">
        <i class="icon-key"></i>
        <span>{$lang['header_log_2']}</span>
      </a>
    </div>
	
  </div>	
</div>


     </div>
   </div>
</div>

<form action="?mod=logs" method="get" name="navi" id="navi">
<input type="hidden" name="mod" value="logs">
<input type="hidden" name="action" value="{$action}">
<input type="hidden" name="start_from" id="start_from" value="{$start_from}">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['opt_logsc']}</div>
  </div>
  <div class="box-content">
    <table class="table table-normal table-hover">
      <thead>
      <tr>
        <td>{$lang['addnews_date']}</td>
        <td>{$lang['user_name']}</td>
        <td>IP:</td>
        <td>{$lang['user_action']}</td>
      </tr>
      </thead>
	  <tbody>
HTML;

	if( $action == "auth") {

		$where = "action ='89' OR action ='90' OR action ='91' OR action ='92'";

	} else {

		$where = "action !='89' AND action !='90' AND action !='91' AND action !='92'";
	}
	
	$db->query( "SELECT SQL_CALC_FOUND_ROWS * FROM " . USERPREFIX . "_admin_logs WHERE {$where} ORDER BY date DESC LIMIT {$start_from},{$news_per_page}" );

	$entries = "";
	
	$i = $start_from;
	while ( $row = $db->get_array() ) {
		$i ++;

		$row['date'] = date( "d.m.Y H:i:s", $row['date'] );
		$status = $lang["admin_logs_action_".$row['action']];

		$entries .= "
        <tr>
        <td>{$row['date']}</td>
        <td><a class=\"status-info\" onclick=\"javascript:popupedit('".urlencode($row[name])."'); return false;\" href=\"#\">{$row['name']}</a></td>
        <td>{$row['ip']}</td>
        <td style=\"word-break: break-all;\">{$status} <b>".stripslashes($row['extras'])."</b></td>
        </tr>";
	}

	if( !$entries ) {
		echo "<tr><td colspan=\"4\" align=\"center\"><br /><br />" . $lang['logs_not_found'] . "<br /><br /><br /></td></tr>";
	} else {
		echo $entries;
	}


	$db->free();

	$result_count = $db->super_query("SELECT FOUND_ROWS() as count");
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
	
	echo <<<HTML
</tbody></table>

	<div class="box-footer padded">
		{$npp_nav}
	</div>

	</div>
</div>
</form>
HTML;

echofooter();
?>