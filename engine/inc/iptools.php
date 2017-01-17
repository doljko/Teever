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
 Файл: iptools.php
-----------------------------------------------------
 Назначение: Поиск посетителей по IP
=====================================================
*/
if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_iptools'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if( isset( $_REQUEST['ip'] ) ) $ip = $db->safesql( htmlspecialchars( strip_tags( trim( $_REQUEST['ip'] ) ) ) ); else $ip = "";
if( isset( $_REQUEST['name'] ) ) $name = $db->safesql( htmlspecialchars( strip_tags( trim( $_REQUEST['name'] ) ), ENT_QUOTES, $config['charset'] ) ); else $name = "";

if( $_REQUEST['doaction'] == "dodelcomments" AND $_REQUEST['id']) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$id = intval( $_REQUEST['id'] );
	
	$result = $db->query( "SELECT COUNT(*) as count, post_id FROM " . PREFIX . "_comments WHERE user_id='{$id}' AND is_register='1' AND approve='1' GROUP BY post_id" );
	
	while ( $row = $db->get_array( $result ) ) {
		
		$db->query( "UPDATE " . PREFIX . "_post SET comm_num=comm_num-{$row['count']} WHERE id='{$row['post_id']}'" );
	
	}
	$db->free( $result );
	
	$db->query( "UPDATE " . USERPREFIX . "_users set comm_num='0' WHERE user_id ='$id'" );
	$db->query( "DELETE FROM " . PREFIX . "_comments WHERE user_id='{$id}' AND is_register='1'" );
}
	
echoheader( "<i class=\"icon-search\"></i>".$lang['opt_iptools'], $lang['header_ip_1'] );

echo <<<HTML
<form action="?mod=iptools" method="post" class="form-horizontal">
<input type="hidden" name="action" value="find">
<input type="hidden" name="mod" value="iptools">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['opt_iptoolsc']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
	
	{$lang['opt_iptoolsc']}<br /><input style="width:100%;max-width:350px;" type="text" name="ip" value="{$ip}">&nbsp;&nbsp;&nbsp;<input type="submit" value="{$lang['b_find']}" class="btn btn-blue">
	 <div class="note large"><i class="icon-warning-sign"></i> {$lang['opt_ipfe']}</div>
	 {$lang['opt_iptoolsname']}<br /><input style="width:100%;max-width:350px;" type="text" name="name" value="{$name}">&nbsp;&nbsp;&nbsp;<input type="submit" value="{$lang['b_find']}" class="btn btn-blue">
	</div>
	
   </div>
</div>

</form>
HTML;

if( $_REQUEST['action'] == "find" and $ip != "" ) {
	
	echo <<<HTML
<script language="javascript" type="text/javascript">
<!--
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
function cdelete(id){
	    DLEconfirm( '{$lang['comm_alldelconfirm']}', '{$lang['p_confirm']}', function () {
			document.location='?mod=iptools&action=find&ip={$ip}&doaction=dodelcomments&user_hash={$dle_login_hash}&id=' + id + '';
		} );
}
//-->
</script>
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['ip_found_users']}</div>
  </div>
  <div class="box-content">

    <table class="table table-normal table-hover">
      <thead>
      <tr>
        <td>{$lang['user_name']}</td>
		<td>IP</td>
        <td>{$lang['user_reg']}</td>
        <td>{$lang['user_last']}</td>
        <td>{$lang['user_news']}</td>
        <td>{$lang['user_coms']}</td>
		<td>{$lang['user_acc']}</td>

      </tr>
      </thead>
	  <tbody>
HTML;
	
	$db->query( "SELECT * FROM " . USERPREFIX . "_users WHERE logged_ip LIKE '{$ip}%'" );
	
	$i = 0;
	while ( $row = $db->get_array() ) {
		$i ++;
		
		if( $row[news_num] == 0 ) {
			$news_link = "$row[news_num]";
		} else {
			$news_link = "[<a href=\"{$config['http_home_url']}index.php?subaction=allnews&user=" . urlencode( $row['name'] ) . "\" target=\"_blank\">" . $row[news_num] . "</a>]";
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
				   <li><a onclick="javascript:cdelete('{$row['user_id']}'); return(false)" href=""?mod=iptools&action=find&ip={$ip}&doaction=dodelcomments&user_hash={$dle_login_hash}&id={$row['id']}"><i class="icon-trash"></i> {$lang['comm_del']}</a></li>
				  </ul>
				</div>
HTML;
		}
		
		if( $row['banned'] == 'yes' ) $group = "<font color=\"red\">" . $lang['user_ban'] . "</font>";
		else $group = $user_group[$row['user_group']]['group_name'];
		
		echo "
        <tr>
        <td>
        <a onclick=\"javascript:popupedit('$row[user_id]'); return(false)\" href=#>{$row['name']}</a>
        </td>
        <td>
        " . $row['logged_ip'] . "</td>
        <td align=\"center\">
        " . langdate( "d/m/Y - H:i", $row['reg_date'] ) . "</td>
        <td align=\"center\">
        " . langdate( 'd/m/Y - H:i', $row['lastdate'] ) . "</td>
        <td align=\"center\">
        " . $news_link . "</td>
        <td align=\"center\">
        " . $comms_link . "</td>
        <td>
        " . $group . "</td>
        </tr>";
	}
	
	if( $i == 0 ) {
		echo "<tr>
     <td height=18 colspan=7>
       <p align=center>{$lang['ip_empty']}</p></td>
    </tr>";
	}
	
	echo <<<HTML
	  </tbody>
	</table>
  </div>
</div>


<div class="box">
  <div class="box-header">
    <div class="title">{$lang['ip_found_comments']}</div>
  </div>
  <div class="box-content">

    <table class="table table-normal table-hover">
      <thead>
      <tr>
        <td>{$lang['user_name']}</td>
		<td>IP</td>
        <td>{$lang['user_reg']}</td>
        <td>{$lang['user_last']}</td>
        <td>{$lang['user_news']}</td>
        <td>{$lang['user_coms']}</td>
		<td>{$lang['user_acc']}</td>

      </tr>
      </thead>
	  <tbody>
HTML;
	
	$db->query( "SELECT " . PREFIX . "_comments.user_id, " . PREFIX . "_comments.ip, " . USERPREFIX . "_users.comm_num, banned, user_group, reg_date, lastdate, " . USERPREFIX . "_users.name, " . USERPREFIX . "_users.news_num FROM " . PREFIX . "_comments LEFT JOIN " . USERPREFIX . "_users ON " . PREFIX . "_comments.user_id=" . USERPREFIX . "_users.user_id WHERE " . PREFIX . "_comments.ip LIKE '{$ip}%' AND " . PREFIX . "_comments.is_register = '1' AND " . USERPREFIX . "_users.name != '' GROUP BY " . PREFIX . "_comments.user_id" );
	
	$i = 0;
	while ( $row = $db->get_array() ) {
		$i ++;
		
		if( $row[news_num] == 0 ) {
			$news_link = "$row[news_num]";
		} else {
			$news_link = "[<a href=\"{$config['http_home_url']}index.php?subaction=allnews&user=" . urlencode( $row['name'] ) . "\" target=\"_blank\">" . $row[news_num] . "</a>]";
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
				   <li><a onclick="javascript:cdelete('{$row['user_id']}'); return(false)" href=""?mod=iptools&action=find&ip={$ip}&doaction=dodelcomments&user_hash={$dle_login_hash}&id={$row['id']}"><i class="icon-trash"></i> {$lang['comm_del']}</a></li>
				  </ul>
				</div>
HTML;
		}
		
		if( $row['banned'] == 'yes' ) $group = "<font color=\"red\">" . $lang['user_ban'] . "</font>";
		else $group = $user_group[$row['user_group']]['group_name'];
		
		echo "
        <tr>
        <td>
        <a onclick=\"javascript:popupedit('$row[user_id]'); return(false)\" href=#>{$row['name']}</a>
        </td>
        <td>
        " . $row['ip'] . "</td>
        <td align=\"center\">
        " . langdate( "d/m/Y - H:i", $row['reg_date'] ) . "</td>
        <td align=\"center\">
        " . langdate( 'd/m/Y - H:i', $row['lastdate'] ) . "</td>
        <td align=\"center\">
        " . $news_link . "</td>
        <td align=\"center\">
        " . $comms_link . "</td>
        <td>
        " . $group . "</td>
        </tr>";
	}
	
	if( $i == 0 ) {
		echo "<tr>
     <td height=18 colspan=7>
       <p align=center>{$lang['ip_empty']}</p></td>
    </tr>";
	}
	
	echo <<<HTML
	  </tbody>
	</table>
  </div>
</div>
HTML;

}

if( $name != "" ) {
	
	echo <<<HTML
<script language="javascript" type="text/javascript">
function MenuIPBuild( m_id ){

var menu=new Array()

menu[0]='<a href="https://www.nic.ru/whois/?ip=' + m_id + '" target="_blank">{$lang['ip_info']}</a>';
menu[1]='<a href="?mod=blockip&ip=' + m_id + '" target="_blank">{$lang['ip_ban']}</a>';

return menu;
}
//-->
</script>

<div class="box">
  <div class="box-header">
    <div class="title">{$lang['opt_iptoolsname']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
HTML;
	
	$row = $db->super_query( "SELECT user_id, name, logged_ip FROM " . USERPREFIX . "_users WHERE name='" . $name . "'" );
	
	if( ! $row['user_id'] ) {
		
		echo "<center><b>" . $lang['user_nouser'] . "</b></center>";
	
	} else {
			$ip_link = <<<HTML
				<div class="btn-group">
				<a href="#" target="_blank" data-toggle="dropdown" class="status-info"><b>{$row['logged_ip']}</b></a>
				  <ul class="dropdown-menu text-left">
				   <li><a href="https://www.nic.ru/whois/?ip={$row['logged_ip']}" target="_blank"><i class="icon-eye-open"></i> {$lang['ip_info']}</a></li>
				   <li class="divider"></li>
				   <li><a href="?mod=blockip&ip={$row['logged_ip']}"><i class="icon-trash"></i> {$lang['ip_ban']}</a></li>
				  </ul>
				</div>
HTML;
		
		echo $lang['user_name'] . " <b>" . $row['name'] . "</b><br /><br />" . $lang['opt_iptoollast'] . $ip_link."<br /><br />" . $lang['opt_iptoolcall'] . " <b>";
		
		$db->query( "SELECT ip FROM " . PREFIX . "_comments WHERE user_id = '{$row['user_id']}' GROUP BY ip" );
		
		$ip_list = array ();
		
		while ( $row = $db->get_array() ) {
		
			$ip_list[] = <<<HTML
				<div class="btn-group">
				<a href="#" target="_blank" data-toggle="dropdown" class="status-info"><b>{$row['ip']}</b></a>
				  <ul class="dropdown-menu text-left">
				   <li><a href="https://www.nic.ru/whois/?ip={$row['ip']}" target="_blank"><i class="icon-eye-open"></i> {$lang['ip_info']}</a></li>
				   <li class="divider"></li>
				   <li><a href="?mod=blockip&ip={$row['ip']}"><i class="icon-trash"></i> {$lang['ip_ban']}</a></li>
				  </ul>
				</div>
HTML;
		}
		
		echo implode( ", ", $ip_list );
	}
	
	echo <<<HTML
	</div>
	
   </div>
</div>
HTML;

}

echofooter();
?>