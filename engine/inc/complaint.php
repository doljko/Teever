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
 Файл: complaint.php
-----------------------------------------------------
 Назначение: управление жалобами
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
  die("Hacking attempt!");
}

if( !$user_group[$member_id['user_group']]['admin_complaint'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if ($_GET['action'] == "delete") {
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$id = intval($_GET['id']);

	$db->query( "DELETE FROM " . PREFIX . "_complaint WHERE id = '{$id}'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '22', '')" );

	header( "Location: ?mod=complaint" ); die();
}

if ($_POST['action'] == "mass_delete") {
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$selected_complaint = $_POST['selected_complaint'];

	if( ! $selected_complaint ) {
		msg( "error", $lang['mass_error'], $lang['opt_complaint_6'], "?mod=complaint" );
	}

	foreach ( $selected_complaint as $complaint ) {

		$complaint = intval($complaint);

		$db->query( "DELETE FROM " . PREFIX . "_complaint WHERE id = '{$complaint}'" );
	}
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '22', '')" );

	header( "Location: ?mod=complaint" ); die();
}

$found = false;

echoheader("<i class=\"icon-bullhorn\"></i>".$lang['opt_complaint'], $lang['header_compl_1']);

	echo <<<HTML
<script type="text/javascript">
<!-- begin
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
// end -->
</script>
HTML;

$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_complaint WHERE p_id > '0'" );

if($row['count']) {

echo <<<HTML
<form action="?mod=complaint" method="post" name="optionsbar" id="optionsbar">
<input type="hidden" name="mod" value="complaint">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['opt_complaint_1']}</div>
  </div>
  <div class="box-content">

    <table class="table table-normal">
      <thead>
      <tr>
        <td style="width: 180px">{$lang['opt_complaint_3']}</td>
        <td>{$lang['opt_complaint_2']}</td>
		<td style="width: 300px">{$lang['user_action']}</td>
        <td style="width: 40px"><input type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all()"></td>
      </tr>
      </thead>
	  <tbody>
HTML;



$db->query("SELECT `id`, `p_id`, `text`, `from`, `to`, `date`  FROM " . PREFIX . "_complaint WHERE p_id > '0' ORDER BY id DESC");

$entries = "";

while($row = $db->get_row()) {

	$found = true;

	if ( $row['date'] ) $date = date( "d.m.Y H:i", $row['date'] )."<br /><br />"; else $date = "";

	$row['text'] = stripslashes($row['text']);

	$from = "<a class=\"status-info\" onclick=\"javascript:popupedit('".urlencode($row['from'])."'); return(false)\" href=\"#\">{$row['from']}</a><br /><br /><a class=\"btn btn-gold\" href=\"" . $config['http_home_url'] . "index.php?do=pm&doaction=newpm&username=".urlencode($row['from'])."\" target=\"_blank\">{$lang['send_pm']}</a>";
	$to = "<a class=\"status-info\" onclick=\"javascript:popupedit('".urlencode($row['to'])."'); return(false)\" href=\"#\">{$row['to']}</a>, <a class=\"status-info\" href=\"" . $config['http_home_url'] . "index.php?do=pm&doaction=newpm&username=".urlencode($row['to'])."\" target=\"_blank\">{$lang['send_pm']}</a>";

	$entries .= "<tr>
	<td>{$date}<b>{$from}</b></td>
    <td>{$lang['opt_complaint_4']} <strong>{$to}</strong><br /><br />{$row['text']}<br /><br /></td>
    <td align=\"center\" class=\"settingstd\"><a uid=\"{$row['id']}\" href=\"?mod=complaint\" class=\"dellink1 btn btn-red\"><i class=\"icon-trash\"></i> {$lang['opt_complaint_11']}</a></td>
    <td align=\"center\" class=\"settingstd\"><input name=\"selected_complaint[]\" value=\"{$row['id']}\" type=\"checkbox\"></td>
    </tr>";

}


echo <<<HTML
		{$entries}
	  </tbody>
	</table>
	
   </div>
	<div class="box-footer padded text-right">
		<select class="uniform" name="action"><option value="">{$lang['edit_selact']}</option><option value="mass_delete">{$lang['edit_seldel']}</option></select>&nbsp;<input class="btn btn-gray" type="submit" value="{$lang['b_start']}">
	</div>	
</div>
</form>
<script language="javascript" type="text/javascript">  
<!-- 

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
	
	$(function(){

		$("#list1").delegate("tr", "hover", function(){
		  $(this).toggleClass("hoverRow");
		});

		var tag_name = '';

		$('.dellink1').click(function(){

			id_comp = $(this).attr('uid');

		    DLEconfirm( '{$lang['opt_complaint_5']}', '{$lang['p_confirm']}', function () {

				document.location='?mod=complaint&user_hash={$dle_login_hash}&action=delete&id=' + id_comp + '';

			} );

			return false;
		});
	});
	
//-->
</script>
HTML;

}

$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_complaint WHERE c_id > '0'" );

if($row['count']) {

echo <<<HTML
<form action="?mod=complaint" method="post" name="optionsbar2" id="optionsbar2">
<input type="hidden" name="mod" value="complaint">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['opt_complaint_15']}</div>
  </div>
  <div class="box-content">

    <table class="table table-normal">
      <thead>
      <tr>
        <td style="width: 180px">{$lang['opt_complaint_3']}</td>
        <td>{$lang['opt_complaint_2']}</td>
		<td style="width: 300px">{$lang['user_action']}</td>
        <td style="width: 40px"><input type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all2()"></td>
      </tr>
      </thead>
	  <tbody>
HTML;



$db->query("SELECT " . PREFIX . "_complaint.id, `c_id`, " . PREFIX . "_complaint.text, `from`, `to`, " . PREFIX . "_complaint.date, " . PREFIX . "_comments.autor, is_register, post_id, " . PREFIX . "_comments.text as c_text, " . PREFIX . "_post.title, " . PREFIX . "_post.date as newsdate, " . PREFIX . "_post.alt_name, " . PREFIX . "_post.category FROM " . PREFIX . "_complaint LEFT JOIN " . PREFIX . "_comments ON " . PREFIX . "_complaint.c_id=" . PREFIX . "_comments.id LEFT JOIN " . PREFIX . "_post ON " . PREFIX . "_comments.post_id=" . PREFIX . "_post.id WHERE c_id > '0' ORDER BY id DESC");

$entries = "";

while($row = $db->get_row()) {

	$found = true;

	$row['text'] = stripslashes($row['text']);
	if ( $row['date'] ) $date = date( "d.m.Y H:i", $row['date'] )."<br /><br />"; else $date = "";

	if ($row['c_text']) {

		$row['c_text'] = "<div class=\"quote\">" . stripslashes( $row['c_text'] ) . "</div>";
		$edit_link = "<br /><br /><a class=\"btn btn-default\" href=\"" . $config['http_home_url'] . "index.php?do=comments&amp;action=comm_edit&amp;id=" . $row['c_id'] ."\" target=\"_blank\"><i class=\"icon-pencil\"></i> {$lang['opt_complaint_12']}</a>";
		$del_c_link = "<br /><br /><a class=\"btn btn-red\" href=\"javascript:DeleteComments('{$row['c_id']}')\"><i class=\"icon-trash\"></i> {$lang['opt_complaint_13']}</a>";

	} else {

		$row['c_text'] = "<div class=\"quote\">" .$lang['opt_complaint_10']. "</div>";
		$edit_link = "";
		$del_c_link = "";
	}

	$from = "<a class=\"status-info\" onclick=\"javascript:popupedit('".urlencode($row['from'])."'); return(false)\" href=\"#\">{$row['from']}</a><br /><br /><a class=\"btn btn-gold\" href=\"" . $config['http_home_url'] . "index.php?do=pm&doaction=newpm&username=".urlencode($row['from'])."\" target=\"_blank\">{$lang['send_pm']}</a>";

	if($row['is_register'])
		$to = "<a class=\"status-info\" onclick=\"javascript:popupedit('".urlencode($row['autor'])."'); return(false)\" href=\"#\">{$row['autor']}</a>, <a class=\"status-info\" href=\"" . $config['http_home_url'] . "index.php?do=pm&doaction=newpm&username=".urlencode($row['autor'])."\" target=\"_blank\">{$lang['send_pm']}</a>";
	else $to = $row['autor'];

	$row['category'] = intval( $row['category'] );

	if( $config['allow_alt_url'] ) {
					
		if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
						
			if( $row['category'] and $config['seo_type'] == 2 ) {
							
				$full_link = $config['http_home_url'] . get_url( $row['category'] ) . "/" . $row['post_id'] . "-" . $row['alt_name'] . ".html";
						
			} else {
							
				$full_link = $config['http_home_url'] . $row['post_id'] . "-" . $row['alt_name'] . ".html";
						
			}
					
		} else {
						
			$full_link = $config['http_home_url'] . date( 'Y/m/d/', strtotime ($row['newsdate']) ) . $row['alt_name'] . ".html";
		}
				
	} else {
					
		$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['post_id'];
	
	}

	$full_link = "<a class=\"status-info\" href=\"" . $full_link . "\" target=\"_blank\">" . stripslashes( $row['title'] ) . "</a>";

	$entries .= "<tr>
	<td>{$date}<b>{$from}</b></td>
    <td>{$lang['opt_complaint_7']} {$full_link}<br /><br />{$lang['opt_complaint_8']} <b>{$to}</b><br /><br /><b>{$lang['opt_complaint_9']}</b><br />{$row['c_text']}<b>{$lang['opt_complaint_2']}</b><br />{$row['text']}<br /><br /></td>
    <td align=\"center\" class=\"settingstd\"><a uid=\"{$row['id']}\" class=\"btn btn-red dellink2\" href=\"?mod=complaint\"><i class=\"icon-trash\"></i> {$lang['opt_complaint_11']}</a>{$edit_link}{$del_c_link}</td>
    <td align=\"center\" class=\"settingstd\"><input name=\"selected_complaint[]\" value=\"{$row['id']}\" type=\"checkbox\"></td>
    </tr>";

}


echo <<<HTML
		{$entries}
	  </tbody>
	</table>
	
   </div>
	<div class="box-footer padded text-right">
		<select class="uniform" name="action"><option value="">{$lang['edit_selact']}</option><option value="mass_delete">{$lang['edit_seldel']}</option></select>&nbsp;<input class="btn btn-gray" type="submit" value="{$lang['b_start']}">
	</div>	
</div>
</form>
<script language="javascript" type="text/javascript">  
<!-- 

	function ckeck_uncheck_all2() {
	    var frm = document.optionsbar2;
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

function DeleteComments(id) {

    DLEconfirm( '{$lang['opt_complaint_13']}?', '{$lang['p_confirm']}', function () {

		ShowLoading('');
	
		$.get("engine/ajax/deletecomments.php", { id: id, dle_allow_hash: '{$dle_login_hash}' }, function(r){
	
			HideLoading('');
	
			r = parseInt(r);
		
			if (!isNaN(r)) {
		
				DLEalert('$lang[opt_complaint_14]', '$lang[p_info]');
				
			}
	
		});

	} );

};
$(function(){

		$("#list2").delegate("tr", "hover", function(){
		  $(this).toggleClass("hoverRow");
		});

		var tag_name = '';

		$('.dellink2').click(function(){

			id_comp = $(this).attr('uid');

		    DLEconfirm( '{$lang['opt_complaint_5']}', '{$lang['p_confirm']}', function () {

				document.location='?mod=complaint&user_hash={$dle_login_hash}&action=delete&id=' + id_comp + '';

			} );

			return false;
		});
});
//-->
</script>
HTML;

}

$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_complaint WHERE n_id > '0'" );

if($row['count']) {

echo <<<HTML
<form action="?mod=complaint" method="post" name="optionsbar3" id="optionsbar3">
<input type="hidden" name="mod" value="complaint">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['opt_complaint_16']}</div>
  </div>
  <div class="box-content">

    <table class="table table-normal">
      <thead>
      <tr>
        <td style="width: 180px">{$lang['opt_complaint_3']}</td>
        <td>{$lang['opt_complaint_2']}</td>
		<td style="width: 300px">{$lang['user_action']}</td>
        <td style="width: 40px"><input type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all3()"></td>
      </tr>
      </thead>
	  <tbody>
HTML;



$db->query("SELECT " . PREFIX . "_complaint.id, `n_id`, " . PREFIX . "_complaint.text, `from`, `to`, " . PREFIX . "_complaint.date,  " . PREFIX . "_post.id as post_id, " . PREFIX . "_post.title, " . PREFIX . "_post.date as newsdate, " . PREFIX . "_post.alt_name, " . PREFIX . "_post.category  FROM " . PREFIX . "_complaint LEFT JOIN " . PREFIX . "_post ON " . PREFIX . "_complaint.n_id=" . PREFIX . "_post.id WHERE n_id > '0' ORDER BY id DESC");


$entries = "";

while($row = $db->get_row()) {

	$found = true;

	$row['text'] = stripslashes($row['text']);
	if ( $row['date'] ) $date = date( "d.m.Y H:i", $row['date'] )."<br /><br />"; else $date = "";

	if ($row['post_id']) {

		$edit_link = "<br /><br /><a class=\"btn btn-default\" href=\"?mod=editnews&amp;action=editnews&amp;id=" . $row['n_id'] ."\" target=\"_blank\"><i class=\"icon-pencil\"></i> {$lang['opt_complaint_18']}</a>";

	} else {

		$edit_link = "";
	}

	$from = "<a class=\"status-info\" onclick=\"javascript:popupedit('".urlencode($row['from'])."'); return(false)\" href=\"#\">{$row['from']}</a><br /><br /><a class=\"btn btn-gold\" href=\"" . $config['http_home_url'] . "index.php?do=pm&doaction=newpm&username=".urlencode($row['from'])."\" target=\"_blank\">{$lang['send_pm']}</a>";


	$row['category'] = intval( $row['category'] );

	if( $config['allow_alt_url'] ) {
					
		if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
						
			if( $row['category'] and $config['seo_type'] == 2 ) {
							
				$full_link = $config['http_home_url'] . get_url( $row['category'] ) . "/" . $row['post_id'] . "-" . $row['alt_name'] . ".html";
						
			} else {
							
				$full_link = $config['http_home_url'] . $row['post_id'] . "-" . $row['alt_name'] . ".html";
						
			}
					
		} else {
						
			$full_link = $config['http_home_url'] . date( 'Y/m/d/', strtotime ($row['newsdate']) ) . $row['alt_name'] . ".html";
		}
				
	} else {
					
		$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['post_id'];
	
	}

	$full_link = "<a class=\"status-info\" href=\"" . $full_link . "\" target=\"_blank\">" . stripslashes( $row['title'] ) . "</a>";

	$entries .= "<tr>
	<td>{$date}<strong>{$from}</strong></td>
    <td>{$lang['opt_complaint_17']} {$full_link}<br /><br /><b>{$lang['opt_complaint_2']}</b><br />{$row['text']}<br /><br /></td>
    <td align=\"center\" class=\"settingstd\"><a uid=\"{$row['id']}\" class=\"btn btn-red dellink3\" href=\"?mod=complaint\"><i class=\"icon-trash\"></i>  {$lang['opt_complaint_11']}</a>{$edit_link}</td>
    <td align=\"center\" class=\"settingstd\"><input name=\"selected_complaint[]\" value=\"{$row['id']}\" type=\"checkbox\"></td>
    </tr>";

}


echo <<<HTML
		{$entries}
	  </tbody>
	</table>
	
   </div>
	<div class="box-footer padded text-right">
		<select class="uniform" name="action"><option value="">{$lang['edit_selact']}</option><option value="mass_delete">{$lang['edit_seldel']}</option></select>&nbsp;<input class="btn btn-gray" type="submit" value="{$lang['b_start']}">
	</div>	
</div>
</form>
<script language="javascript" type="text/javascript">  
<!-- 

	function ckeck_uncheck_all3() {
	    var frm = document.optionsbar3;
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
	
	$(function(){

			$("#list3").delegate("tr", "hover", function(){
			  $(this).toggleClass("hoverRow");
			});

			var tag_name = '';

			$('.dellink3').click(function(){

				id_comp = $(this).attr('uid');

				DLEconfirm( '{$lang['opt_complaint_5']}', '{$lang['p_confirm']}', function () {

					document.location='?mod=complaint&user_hash={$dle_login_hash}&action=delete&id=' + id_comp + '';

				} );

				return false;
			});
	});
//-->
</script>
HTML;

}

$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_complaint WHERE p_id = '0' AND c_id = '0' AND n_id = '0'" );

if($row['count']) {

echo <<<HTML
<form action="?mod=complaint" method="post" name="optionsbar4" id="optionsbar4">
<input type="hidden" name="mod" value="complaint">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['opt_complaint_21']}</div>
  </div>
  <div class="box-content">

    <table class="table table-normal">
      <thead>
      <tr>
        <td style="width: 180px">{$lang['opt_complaint_3']}</td>
        <td>{$lang['opt_complaint_2']}</td>
		<td style="width: 300px">{$lang['user_action']}</td>
        <td style="width: 40px"><input type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all4()"></td>
      </tr>
      </thead>
	  <tbody>
HTML;



$db->query("SELECT `id`, `text`, `from`, `to`, `date` FROM " . PREFIX . "_complaint WHERE p_id = '0' AND c_id = '0' AND n_id = '0' ORDER BY id DESC");

$entries = "";

while($row = $db->get_row()) {

	$found = true;
	if ( $row['date'] ) $date = date( "d.m.Y H:i", $row['date'] )."<br /><br />"; else $date = "";

	$row['text'] = stripslashes($row['text']);

	if (count(explode(".", $row['from'])) != 4 ) $from = "<a class=\"status-info\" onclick=\"javascript:popupedit('".urlencode($row['from'])."'); return(false)\" href=\"#\">{$row['from']}</a><br /><br /><a class=\"btn btn-gold\" href=\"" . $config['http_home_url'] . "index.php?do=pm&doaction=newpm&username=".urlencode($row['from'])."\" target=\"_blank\">{$lang['send_pm']}</a>";
	else $from = $row['from'];

	if ( $config['charset'] == "windows-1251") {

		if( function_exists( 'mb_convert_encoding' ) ) {
	
			$row['to'] = mb_convert_encoding( $row['to'], "windows-1251", "UTF-8" );
	
		} elseif( function_exists( 'iconv' ) ) {
		
			$row['to'] = iconv( "UTF-8", "windows-1251//IGNORE", $row['to'] );
		
		}

	}

	$to = "<a class=\"status-info\" href=\"{$row['to']}\" target=\"_blank\">{$row['to']}</a>";

	$entries .= "<tr>
	<td>{$date}<b>{$from}</b></td>
    <td>{$lang['opt_complaint_22']} <b>{$to}</b><br /><br />{$row['text']}<br /><br /></td>
    <td align=\"center\" class=\"settingstd\"><a uid=\"{$row['id']}\" class=\"dellink4 btn btn-red\" href=\"?mod=complaint\"><i class=\"icon-trash\"></i> {$lang['opt_complaint_11']}</a></td>
    <td align=\"center\" class=\"settingstd\"><input name=\"selected_complaint[]\" value=\"{$row['id']}\" type=\"checkbox\"></td>
    </tr>";

}


echo <<<HTML
		{$entries}
	  </tbody>
	</table>
	
   </div>
	<div class="box-footer padded text-right">
		<select class="uniform" name="action"><option value="">{$lang['edit_selact']}</option><option value="mass_delete">{$lang['edit_seldel']}</option></select>&nbsp;<input class="btn btn-gray" type="submit" value="{$lang['b_start']}">
	</div>	
</div>
</form>
<script language="javascript" type="text/javascript">  
<!-- 

	function ckeck_uncheck_all4() {
	    var frm = document.optionsbar4;
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
	
	$(function(){

			$("#list4").delegate("tr", "hover", function(){
			  $(this).toggleClass("hoverRow");
			});

			var tag_name = '';

			$('.dellink4').click(function(){

				id_comp = $(this).attr('uid');

				DLEconfirm( '{$lang['opt_complaint_5']}', '{$lang['p_confirm']}', function () {

					document.location='?mod=complaint&user_hash={$dle_login_hash}&action=delete&id=' + id_comp + '';

				} );

				return false;
			});
	});
//-->
</script>
HTML;

}

if (!$found) {


echo <<<HTML
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['opt_complaint']}</div>
  </div>
  <div class="box-content">
	<div class="row box-section">
		<table width="100%">
		    <tr>
		        <td height="100" class="text-center settingstd">{$lang['opt_complaint_19']}</td>
		    </tr>
		</table>
	</div>
	<div class="row box-section"><div class="col-md-12 text-center"><a class="btn btn btn-red" href="javascript:history.go(-1)">{$lang['func_msg']}</a></div></div>
  </div>
</div>
HTML;


}

echofooter();
?>