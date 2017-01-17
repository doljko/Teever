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
 Файл: comments.php
-----------------------------------------------------
 Назначение: Управления комментариями
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_comments'] ) {
	msg( "error", $lang['addnews_denied'], $lang['addnews_denied'], "?mod=editnews&amp;action=list" );
}

$id = intval( $_REQUEST['id'] );

function deletecomments( $id ) {
	global $config, $db;
	
	$id = intval($id);

	$row = $db->super_query( "SELECT id, post_id, user_id, is_register, approve FROM " . PREFIX . "_comments WHERE id = '{$id}'" );
	
	$db->query( "DELETE FROM " . PREFIX . "_comments WHERE id = '{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_comment_rating_log WHERE c_id = '{$id}'" );	

	if( $row['is_register'] ) {
		$db->query( "UPDATE " . USERPREFIX . "_users SET comm_num=comm_num-1 WHERE user_id ='{$row['user_id']}'" );
	}
	
	if($row['approve']) $db->query( "UPDATE " . PREFIX . "_post SET comm_num=comm_num-1 WHERE id='{$row['post_id']}'" );

	if ( $config['tree_comments'] ) {

		$sql_result = $db->query( "SELECT id FROM " . PREFIX . "_comments WHERE parent = '{$id}'" );
	
		while ( $row = $db->get_row( $sql_result ) ) {
			deletecomments( $row['id'] );
		}

	}

}

if( $action == "dodelete" AND $id) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$result = $db->query( "SELECT COUNT(*) as count, user_id FROM " . PREFIX . "_comments WHERE post_id='$id' AND is_register='1' GROUP BY user_id" );
	
	while ( $row = $db->get_array( $result ) ) {
		
		$db->query( "UPDATE " . USERPREFIX . "_users SET comm_num=comm_num-{$row['count']} WHERE user_id='{$row['user_id']}'" );
	
	}
	
	$db->query( "DELETE FROM " . PREFIX . "_comments WHERE post_id='$id'" );
	$db->query( "UPDATE " . PREFIX . "_post SET comm_num='0' WHERE id ='$id'" );
	
	clear_cache();
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '20', '$id')" );
	
	msg( "info", $lang['mass_head'], $lang['mass_delokc'], "$PHP_SELF?mod=editnews&amp;action=list" );

} elseif( $action == "mass_delete" ) {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	if( ! $_POST['selected_comments'] ) {
		msg( "error", $lang['mass_error'], $lang['mass_dcomm'], "?mod=comments&action=edit&id={$id}" );
	}
	
	foreach ( $_POST['selected_comments'] as $c_id ) {

		$c_id = intval( $c_id );
		
		deletecomments( $c_id );

	}
	
	clear_cache( array('news_', 'full_', 'comm_', 'rss') );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '21', '')" );
	
	msg( "info", $lang['mass_head'], $lang['mass_delokc'], "?mod=comments&action=edit&id={$id}" );

} elseif( $action == "edit" ) {

	if ( $id ) $where = "post_id = '{$id}' AND "; else $where = "";

	$start_from = intval( $_GET['start_from'] );
	if( $start_from < 0 ) $start_from = 0;
	$news_per_page = 50;
	$i = $start_from;

	$gopage = intval( $_GET['gopage'] );
	if( $gopage > 0 ) $start_from = ($gopage - 1) * $news_per_page;

	
	echoheader( "<i class=\"icon-file-alt\"></i>".$lang['header_c_1'], $lang['header_c_3'] );
	
	$entries = "";
	
	$result_count = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_comments WHERE {$where}approve='1'" );

	$db->query( "SELECT " . PREFIX . "_comments.id, post_id, " . PREFIX . "_comments.date, " . PREFIX . "_comments.autor, text, ip, " . PREFIX . "_post.title, " . PREFIX . "_post.date as newsdate, " . PREFIX . "_post.alt_name, " . PREFIX . "_post.category FROM " . PREFIX . "_comments LEFT JOIN " . PREFIX . "_post ON " . PREFIX . "_comments.post_id=" . PREFIX . "_post.id WHERE {$where}" . PREFIX . "_comments.approve = '1' ORDER BY " . PREFIX . "_comments.date DESC LIMIT $start_from,$news_per_page" );
	
	while ( $row = $db->get_array() ) {
		$i ++;

		$row['text'] = "<div id='comm-id-" . $row['id'] . "'>" . stripslashes( $row['text'] ) . "</div>";
		$row['newsdate'] = strtotime( $row['newsdate'] );
		$row['date'] = strtotime( $row['date'] );
		$date = date( "d-m-Y, H:i", $row['date'] );
		
		if( $config['allow_alt_url'] ) {
			
			if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
				
				if( intval( $row['category'] ) and $config['seo_type'] == 2 ) {
					
					$full_link = $config['http_home_url'] . get_url( intval( $row['category'] ) ) . "/" . $row['post_id'] . "-" . $row['alt_name'] . ".html";
				
				} else {
					
					$full_link = $config['http_home_url'] . $row['post_id'] . "-" . $row['alt_name'] . ".html";
				
				}
			
			} else {
				
				$full_link = $config['http_home_url'] . date( 'Y/m/d/', $row['newsdate'] ) . $row['alt_name'] . ".html";
			}
		
		} else {
			
			$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['post_id'];
		
		}
		
		$news_title = "<a class=\"status-info\" href=\"" . $full_link . "\"  target=\"_blank\">" . stripslashes( $row['title'] ) . "</a>";
		$row['autor'] = "<a class=\"status-info\" onclick=\"javascript:popupedit('".urlencode($row['autor'])."'); return(false)\" href=\"#\">{$row['autor']}</a>";
		$row['ip'] = "<a class=\"status-info\" href=\"?mod=blockip&ip=".urlencode($row['ip'])."\" target=\"_blank\">{$row['ip']}</a>";

	
	$entries .= <<<HTML
 <li id='table-comm-{$row['id']}' class="arrow-box-left gray">
 <div class="avatar"><input name="selected_comments[]" value="{$row['id']}" type="checkbox"></div>
    <div class="info">
      <span class="name">
        <span class="label label-green">{$lang['edit_autor']}</span> <strong class="indent">{$row['autor']}</strong> IP: {$row['ip']} {$lang['cmod_n_title']} <strong>{$news_title}</strong>
      </span>
      <span class="time"><i class="icon-time"></i>{$date}</span>
    </div>
    <div class="content">
      <blockquote>
        {$row['text']}<input type="hidden" name="post_id[{$row['id']}]" value="{$row['post_id']}">
      </blockquote>
      <div>
		<a onclick="ajax_comm_edit('{$row['id']}'); return false;" href="#" class="btn btn-xs btn-blue"><i class="icon-pencil"></i> <b>{$lang['group_sel1']}</b></a>
		<a onclick="MarkSpam('{$row['id']}'); return false;" href="#" class="btn btn-xs btn-gold"><i class="icon-minus-sign"></i> <b>{$lang['btn_spam']}</b></a>
		<a onclick="DeleteComments('{$row['id']}'); return false;" href="#" class="btn btn-xs btn-red"><i class="icon-trash"></i> <b>{$lang['edit_dnews']}</b></a>
      </div>
    </div>
  </li>
HTML;
	
	}
	
	$db->free();

		// pagination

		$npp_nav = "";
		
		if( $start_from > 0 ) {
			$previous = $start_from - $news_per_page;
			$npp_nav .= "<li><a href=\"?mod=comments&action=edit&id={$id}&start_from={$previous}\" title=\"{$lang['edit_prev']}\">&lt;&lt;</a></li>";
		}
		
		if( $result_count['count'] > $news_per_page ) {
			
			$enpages_count = @ceil( $result_count['count'] / $news_per_page );
			$enpages_start_from = 0;
			$enpages = "";
			
			if( $enpages_count <= 10 ) {
				
				for($j = 1; $j <= $enpages_count; $j ++) {
					
					if( $enpages_start_from != $start_from ) {
						
						$enpages .= "<li><a href=\"?mod=comments&action=edit&id={$id}&start_from={$enpages_start_from}\">$j</a></li>";
					
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
					
					$enpages .= "<li><a href=\"?mod=comments&action=edit&id={$id}&start_from=0\">1</a></li> <li><span>...</span></li>";
				
				}
				
				for($j = $start; $j <= $end; $j ++) {
					
					if( $enpages_start_from != $start_from ) {
						
						$enpages .= "<li><a href=\"?mod=comments&action=edit&id={$id}&start_from={$enpages_start_from}\">$j</a></li>";
					
					} else {
						
						$enpages .= "<li class=\"active\"><span>$j</span></li>";
					}
					
					$enpages_start_from += $news_per_page;
				}
				
				$enpages_start_from = ($enpages_count - 1) * $news_per_page;
				$enpages .= "<li><span>...</span></li><li><a href=\"?mod=comments&action=edit&id={$id}&start_from={$enpages_start_from}\">$enpages_count</a></li>";
				
				$npp_nav .= $enpages;
			
			}
		
			if( $result_count['count'] > $i ) {
				$how_next = $result_count['count'] - $i;
				if( $how_next > $news_per_page ) {
					$how_next = $news_per_page;
				}
				$npp_nav .= "<li><a href=\"?mod=comments&action=edit&id={$id}&start_from={$i}\" title=\"{$lang['edit_next']}\">&gt;&gt;</a></li>";
			}
			
			$npp_nav = "<div class=\"row box-section text-center\"><ul class=\"pagination pagination-sm\">".$npp_nav."</ul></div>";
		}		
		// pagination

	
	echo <<<HTML
<script language='JavaScript' type="text/javascript">
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

};

function ajax_comm_edit( c_id )
{

	ShowLoading('');
	
	$.get("engine/ajax/quote.php", { id: c_id, area:"admin", action: "edit" }, function(data){

		HideLoading('');
		$('#comm-id-'+c_id).html(data);


	}, 'html');
	return false;
};
function ajax_save_comm_edit( c_id )
{
	var comm_txt = document.getElementById('edit-comm-'+c_id).value;

	ShowLoading('');

	$.post('engine/ajax/editcomments.php', { comm_txt: comm_txt, id: c_id, action: "save" }, function(data){
	
		HideLoading('');
		$("#comm-id-"+c_id).html(data);
	
	});

	return false;
}

function DeleteComments(id) {

    DLEconfirm( '{$lang['d_c_confirm']}', '{$lang['p_confirm']}', function () {

		ShowLoading('');
	
		$.get("engine/ajax/deletecomments.php", { id: id, dle_allow_hash: '{$dle_login_hash}' }, function(r){
	
			HideLoading('');
	
			ShowOrHide('table-comm-'+id);
	
		});

	} );

};
function MarkSpam(id) {

    DLEconfirm( '{$lang['mark_spam_c']}', '{$lang['p_confirm']}', function () {

		ShowLoading('');
	
		$.get("engine/ajax/adminfunction.php", { id: id, action: 'commentsspam', user_hash: '{$dle_login_hash}' }, function(data){
	
			HideLoading('');
	
			if (data != "error") {
	
			    DLEconfirm( data, '{$lang['p_confirm']}', function () {
					location.reload(true);
				} );
	
			}
	
		});

	} );

};
//-->
</script>
<form action="" method="post" name="editnews">
<input type=hidden name=mod value="comments">
<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['comm_einfo']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">

<ul class="chat-box timeline">
{$entries}
</ul>
  
	</div>
	{$npp_nav}
	<div class="row box-section">
		<select class="uniform" name="action"><option value="">---</option><option value="mass_delete">{$lang['edit_seldel']}</option></select>
		<input class="btn btn-gray" type="submit" value="{$lang['b_start']}" />
	</div>
   </div>
</div>
</form>
HTML;
	
	echofooter();
} else {
	msg( "error", $lang['addnews_denied'], $lang['addnews_denied'], "?mod=editnews&amp;action=list" );
}
?>