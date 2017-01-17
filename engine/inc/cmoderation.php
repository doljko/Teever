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
 Файл: cmoderation.php
-----------------------------------------------------
 Назначение: Модерация комментариев
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_comments'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'], "?mod=main" );
}

if( $action == "mass_approve" ) {
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	if( ! $_POST['selected_comments'] ) {
		msg( "error", $lang['mass_error'], $lang['mass_acomm'], "?mod=cmoderation" );
	}
	
	include_once ENGINE_DIR . '/classes/mail.class.php';
			
	foreach ( $_POST['selected_comments'] as $c_id ) {
		
		$c_id = intval( $c_id );
		$post_id = intval( $_POST['post_id'][$c_id] );
		
		$db->query( "UPDATE " . PREFIX . "_comments SET approve='1' WHERE id='{$c_id}'" );
		$db->query( "UPDATE " . PREFIX . "_post SET comm_num=comm_num+1 WHERE id='{$post_id}'" );

		if ( $config['allow_subscribe'] ) {
	
			$row = $db->super_query( "SELECT autor, text FROM " . PREFIX . "_comments WHERE id = '{$c_id}'" );
	
			$name = $row['autor'];
			$body = $row['text'];
	
			$row = $db->super_query( "SELECT id, short_story, title, date, alt_name, category FROM ".PREFIX."_post WHERE id = '{$post_id}'" );
	
			$row['date'] = strtotime( $row['date'] );
			$row['category'] = intval( $row['category'] );
	
			if( $config['allow_alt_url'] ) {
					
				if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
				
					if( $row['category'] and $config['seo_type'] == 2 ) {
						
						$full_link = $config['http_home_url'] . get_url( $row['category'] ) . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";
						
					} else {
						
						$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
						
					}
					
				} else {
					
					$full_link = $config['http_home_url'] . date( 'Y/m/d/', $row['date'] ) . $row['alt_name'] . ".html";
				}
				
			} else {
					
				$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];
				
			}
		
			$title = stripslashes($row['title']);
			
			$row = $db->super_query( "SELECT * FROM " . PREFIX . "_email WHERE name='comments' LIMIT 0,1" );
			$mail = new dle_mail( $config, $row['use_html'] );
	
			if (strpos($full_link, "//") === 0) $full_link = "http:".$full_link;
			elseif (strpos($full_link, "/") === 0) $full_link = "http://".$_SERVER['HTTP_HOST'].$full_link;
	
			$row['template'] = stripslashes( $row['template'] );
			$row['template'] = str_replace( "{%username%}", $name, $row['template'] );
			$row['template'] = str_replace( "{%date%}", langdate( "j F Y H:i", $_TIME, true ), $row['template'] );
			$row['template'] = str_replace( "{%link%}", $full_link, $row['template'] );
			$row['template'] = str_replace( "{%title%}", $title, $row['template'] );
	
			$body = str_replace( '\n', "", $body );
			$body = str_replace( '\r', "", $body );
				
			$body = stripslashes( stripslashes( $body ) );
			$body = str_replace( "<br />", "\n", $body );
			$body = strip_tags( $body );
				
			if( $row['use_html'] ) {
				$body = str_replace("\n", "<br />", $body );
			}
						
			$row['template'] = str_replace( "{%text%}", $body, $row['template'] );
			$row['template'] = str_replace( "{%ip%}", "--", $row['template'] );
	
			$db->query( "SELECT user_id, name, email, hash FROM " . PREFIX . "_subscribe WHERE news_id='{$post_id}'" );
	
			while($rec = $db->get_row())
			{
				if ($rec['user_id'] != $member_id['user_id'] ) {
	
					if (strpos($config['http_home_url'], "//") === 0) $slink = "http:".$config['http_home_url'];
					elseif (strpos($config['http_home_url'], "/") === 0) $slink = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];
					else $slink = $config['http_home_url'];
			
					$body = str_replace( "{%username_to%}", $rec['name'], $row['template'] );
					$body = str_replace( "{%unsubscribe%}", $slink . "index.php?do=unsubscribe&post_id=" . $post_id . "&user_id=" . $rec['user_id'] . "&hash=" . $rec['hash'], $body );
					$mail->send( $rec['email'], $lang['mail_comments'], $body );
	
				}
	
			}
	
			$db->free();
		}
	
	}
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '19', '')" );
	
	clear_cache();
	
	msg( "info", $lang['mass_head'], $lang['mass_approve_ok'], "?mod=cmoderation" );

}

if( $action == "mass_delete" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	if( ! $_POST['selected_comments'] ) {
		msg( "error", $lang['mass_error'], $lang['mass_dcomm'], "?mod=cmoderation" );
	}
	
	foreach ( $_POST['selected_comments'] as $c_id ) {
		
		$c_id = intval( $c_id );
		
		$row = $db->super_query( "SELECT user_id FROM " . PREFIX . "_comments WHERE id='{$c_id}'" );
		
		if( $row['user_id'] ) $db->query( "UPDATE " . USERPREFIX . "_users SET comm_num=comm_num-1 where user_id='{$row['user_id']}'" );
		
		$db->query( "DELETE FROM " . PREFIX . "_comments WHERE id='{$c_id}'" );
	
	}
	
	clear_cache();
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '19', '')" );
	
	msg( "info", $lang['mass_head'], $lang['mass_delokc'], "?mod=cmoderation" );

}

echoheader( "<i class=\"icon-file-alt\"></i>".$lang['header_c_1'], $lang['header_c_2'] );

$entries = "";

$db->query( "SELECT " . PREFIX . "_comments.id, post_id, " . PREFIX . "_comments.date, " . PREFIX . "_comments.autor, text, ip, " . PREFIX . "_post.title, " . PREFIX . "_post.date as newsdate, " . PREFIX . "_post.alt_name, " . PREFIX . "_post.category FROM " . PREFIX . "_comments LEFT JOIN " . PREFIX . "_post ON " . PREFIX . "_comments.post_id=" . PREFIX . "_post.id WHERE " . PREFIX . "_comments.approve = '0' ORDER BY " . PREFIX . "_comments.date DESC" );

while ( $row = $db->get_array() ) {

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
        <span id="save-button-{$row['id']}"><a onclick="public_comm('{$row['id']}', '{$row['post_id']}'); return false;" href="#" class="btn btn-xs btn-green"><i class="icon-ok"></i> <b>{$lang['bb_b_approve']}</b></a></span>
		<a onclick="ajax_comm_edit('{$row['id']}'); return false;" href="#" class="btn btn-xs btn-blue"><i class="icon-pencil"></i> <b>{$lang['group_sel1']}</b></a>
		<a onclick="MarkSpam('{$row['id']}'); return false;" href="#" class="btn btn-xs btn-gold"><i class="icon-minus-sign"></i> <b>{$lang['btn_spam']}</b></a>
		<a onclick="DeleteComments('{$row['id']}'); return false;" href="#" class="btn btn-xs btn-red"><i class="icon-trash"></i> <b>{$lang['edit_dnews']}</b></a>
      </div>
    </div>
  </li>
HTML;

}

$db->free();

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
			width: 580,
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
	document.getElementById('save-button-'+c_id).innerHTML = '';
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
		ShowOrHide('table-comm-'+c_id);
	
	});

	return false;
}
function public_comm( c_id, post_id )
{

	ShowLoading('');

	$.post('engine/ajax/adminfunction.php', { id: c_id, post_id:post_id, action: "commentspublic", user_hash: '{$dle_login_hash}' }, function(data){
	
		HideLoading('');
		ShowOrHide('table-comm-'+c_id);
	
	});

	return false;
};

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
<input type="hidden" name="mod" value="cmoderation">
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
	<div class="row box-section">
		<select class="uniform" name="action">
		<option value="">{$lang['edit_selact']}</option>
		<option value="mass_approve">{$lang['bb_b_approve']}</option>
		<option value="mass_delete">{$lang['edit_seldel']}</option>
		</select> <input class="btn btn-gray" type="submit" value="{$lang['b_start']}" />
	</div>
   </div>
</div>
</form>
HTML;

echofooter();
?>