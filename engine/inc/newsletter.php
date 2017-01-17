<?php
/*
=====================================================
DataLife Engine - by SoftNews Media Group 
-----------------------------------------------------
Copyright (c) 2004-2016
=====================================================
 Данный код защищен авторскими правами
=====================================================
 Файл: newsletter.php
-----------------------------------------------------
 Назначение: Отправка массовых сообщений
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
  die("Hacking attempt!");
}

if( ! $user_group[$member_id['user_group']]['admin_newsletter'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if (isset ($_REQUEST['editor'])) $editor = $_REQUEST['editor']; else $editor = "";
if (isset ($_REQUEST['type'])) $type = $_REQUEST['type']; else $type = "";
if (isset ($_REQUEST['action'])) $action = $_REQUEST['action']; else $action = "";
if (isset ($_REQUEST['a_mail'])) $a_mail = intval($_REQUEST['a_mail']); else $a_mail = "";

if (isset ($_GET['empfanger'])) {

	$empfanger = array ();

	if( !count( $_GET['empfanger'] ) ) {
		$empfanger[] = '0';
	} else {

		foreach ( $_GET['empfanger'] as $value ) {
			$empfanger[] = intval($value);
		}

	}

	if ( $empfanger[0] ) $empfanger = $db->safesql( implode( ',', $empfanger ) ); else $empfanger = "0";

} else $empfanger = "0";

if ($action=="send") {

	include_once ENGINE_DIR.'/classes/parse.class.php';

	$parse = new ParseFilter(Array(), Array(), 1, 1);

	$title = strip_tags(stripslashes($parse->process($_POST['title'])));
	$message = stripslashes($parse->process($_POST['message']));
	$start_from = intval($_GET['start_from']);
	$limit = intval($_GET['limit']);
	$interval = intval($_GET['interval']) * 1000;

	if ($limit < 1) {

		$limit = 20;

	}

	if ($editor == "wysiwyg"){

		$message = $parse->BB_Parse($message);

	} else {

		$message = $parse->BB_Parse($message, false);
	}

	$where = array();

	$where[] = "banned != 'yes'";

	if ($empfanger) {
	
		$user_list = array(); 
	
		$temp = explode(",", $empfanger); 
	
		foreach ( $temp as $value ) {
			$user_list[] = intval($value);
		}
	
		$user_list = implode( "','", $user_list );
	
		$user_list = "user_group IN ('" . $user_list . "')";
	
	} else $user_list = false;

	if ($user_list) $where[] = $user_list;
	if ($a_mail AND $type == "email") $where[] = "allow_mail = '1'";

	if (count($where)) $where = " WHERE ".implode (" AND ", $where);
	else $where = "";

	$row = $db->super_query("SELECT COUNT(*) as count FROM " . USERPREFIX . "_users".$where);

	if ($start_from > $row['count'] OR $start_from < 0) $start_from = 0;

	if ($type == "email")
		$type_send = $lang['bb_b_mail'];
	else
		$type_send = $lang['nl_pm'];

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '47', '{$type_send}')" );


echo <<<HTML
<!doctype html>
<html>
<head>
<meta content="text/html; charset={$config['charset']}" http-equiv="content-type" />
<title>{$lang['nl_seng']}</title>
<link href="engine/skins/stylesheets/application.css" media="screen" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="engine/classes/js/jquery.js"></script>
</head>
<body>
<script language="javascript" type="text/javascript">
var total = {$row['count']};

	$(function() {

		$("#status").ajaxError(function(event, request, settings){
		   $(this).html('{$lang['nl_error']}');
			$('#button').attr("disabled", false);

			var startagain = parseInt($('#sendet_ok').val());
			startagain = startagain + {$limit};

			$('#sendet_ok').val( startagain );

		 });

		$('#button').click(function() {
			$('#status').html('{$lang['nl_sinfo']}');
			$('#button').attr("disabled", "disabled");
			$('#button').val("{$lang['send_forw']}");

			senden( $('#sendet_ok').val() );
			return false;
		});

	});

function senden( startfrom ){

	var title = $('#title').html();
	var message = $('#message').html();

	$.post("engine/ajax/newsletter.php", { startfrom: startfrom, title: title, message: message, type: '{$type}', empfanger: '{$empfanger}', a_mail: '{$a_mail}', limit: '{$limit}'  },
		function(data){

			if (data) {

				if (data.status == "ok") {

					$('#gesendet').html(data.count);
					$('#sendet_ok').val(data.count);

					var proc = Math.round( (100 * data.count) / total );

					if ( proc > 100 ) proc = 100;

					$('.progress-bar').width( proc + '%');

			         if (data.count >= total) 
			         {
			              $('#status').html('{$lang['nl_finish']}');
			         }
			         else 
			         { 
			              setTimeout("senden(" + data.count + ")", {$interval} );
			         }


				}

			}
		}, "json");

	return false;
}
</script>
<div class="padded">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['nl_seng']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
<table width="100%">
    <tr>
        <td width="120">{$lang['nl_empf']}</td>
        <td>{$row['count']}</td>
    </tr>
    <tr>
        <td>{$lang['nl_type']}</td>
        <td>{$type_send}</td>
    </tr>
    <tr>
        <td colspan="2"><br />
		<div class="progress">
          <div class="progress-bar progress-blue" style="width:0%;"><span></span></div>
        </div>
		{$lang['nl_sendet']} <span style="color:red;" id='gesendet'>{$start_from}</span> {$lang['mass_i']} <span style="color:blue;">{$row['count']}</span> {$lang['nl_status']} <span id="status"></span>
		<br /><br /><input id="button" type="button" value="{$lang['nl_start']}" class="btn btn-green"><input type="hidden" id="sendet_ok" name="sendet_ok" value="{$start_from}">
		</td>
    </tr>
</table>	
	  
	</div>
	<div class="row box-section">
	{$lang['nl_info']}
	</div>	
   </div>
</div>
</div>
HTML;

$message = stripslashes($message);

echo <<<HTML
<pre style="display:none;" id="title">{$title}</pre>
<pre style="display:none;" id="message">{$message}</pre>
</body>

</html>
HTML;

}
elseif ($action=="preview")
{
include_once ENGINE_DIR.'/classes/parse.class.php';

$parse = new ParseFilter(Array(), Array(), 1, 1);

$title = strip_tags(stripslashes($parse->process($_POST['title'])));
$message = stripslashes($parse->process($_POST['message']));

if ($editor == "wysiwyg"){
$message = $parse->BB_Parse($message);
} else {
$message = $parse->BB_Parse($message, false);
}

echo <<<HTML
<html><title>{$title}</title>
<meta content="text/html; charset={$config['charset']}" http-equiv=Content-Type>
<style type="text/css">
html,body{
height:100%;
margin:0px;
padding: 0px;
font-size: 11px;
font-family: verdana;
}
p {
margin:0px;
padding: 0px;
}
table{
border:0px;
border-collapse:collapse;
}

table td{
padding:0px;
font-size: 11px;
font-family: verdana;
}

a:active,
a:visited,
a:link {
	color: #4b719e;
	text-decoration:none;
	}

a:hover {
	color: #4b719e;
	text-decoration: underline;
	}
</style>
<body>
HTML;

echo "<fieldset style=\"border-style:solid; border-width:1; border-color:black;\"><legend> <span style=\"font-size: 10px; font-family: Verdana\">{$title}</span> </legend>{$message}</fieldset>";


}
elseif ($action=="message") {


	echoheader( "<i class=\"icon-envelope\"></i>".$lang['main_newsl'], $lang['header_ne_1'] );


    echo "
    <script type=\"text/javascript\">
    function send(){";

	if ($editor == "wysiwyg"){
	echo "tinyMCE.triggerSave();";
	}

	echo "if(document.addnews.message.value == '' || document.addnews.title.value == ''){ DLEalert('$lang[vote_alert]', '$lang[p_info]'); }
    else{
        dd=window.open('','snd','height=350,width=620,resizable=1,scrollbars=1')
        document.addnews.action.value='send';document.addnews.target='snd'
        document.addnews.submit();dd.focus()
    }
    }
    </script>";

    echo "
    <script type=\"text/javascript\">
    function preview(){";

	if ($editor == "wysiwyg"){
	echo "tinyMCE.triggerSave();";
	}

	echo "if(document.addnews.message.value == '' || document.addnews.title.value == ''){ DLEalert('$lang[vote_alert]', '$lang[p_info]'); }
    else{
        dd=window.open('','prv','height=300,width=600,resizable=1,scrollbars=1')
        document.addnews.action.value='preview';document.addnews.target='prv'
        document.addnews.submit();dd.focus()
        setTimeout(\"document.addnews.action.value='send';document.addnews.target='_self'\",500)
    }
    }
    </script>";

	$start_from = intval($_GET['start_from']);

echo <<<HTML
<form method="POST" name="addnews" id="addnews" action="" class="form-horizontal">
<input type="hidden" name="mod" value="newsletter">
<input type="hidden" name="action" value="send">
<input type="hidden" name="type" value="{$type}">
<input type="hidden" name="a_mail" value="{$a_mail}">
<input type="hidden" name="editor" value="{$editor}">
<input type="hidden" name="start_from" value="{$start_from}">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['nl_main']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
	
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['edit_title']}</label>
		  <div class="col-lg-10">
			<input type="text" size="55" name="title">
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['nl_message']}</label>
		  <div class="col-lg-10">
HTML;

	if( $_REQUEST['editor'] == "wysiwyg" ) {
		
		include(ENGINE_DIR.'/editor/newsletter.php');
	
	} else {

		$bb_editor = true;
		include (ENGINE_DIR . '/inc/include/inserttag.php');
		echo "{$bb_code}<textarea style=\"width:100%;max-width: 950px;height:300px;\" onfocus=\"setFieldName(this.name)\" name=\"message\" id=\"message\" ></textarea><script type=\"text/javascript\">var selField  = \"message\";</script>";
	}

echo <<<HTML
			<br><br>{$lang['nl_info_1']} <b>{$lang['nl_info_2']}</b>
		  </div>
		</div>
		<div class="form-group">
		  <label class="control-label col-lg-2"></label>
		  <div class="col-lg-10">
			<input type="button" onClick="send(); return false;" class="btn btn-green" value="{$lang['btn_send']}">&nbsp;
        <input onClick="preview()" type="button" class="btn btn-gray" value="{$lang['btn_preview']}">
		  </div>
		 </div>	
		
	</div>
	
   </div>
</div>		
</form>		
HTML;

  echofooter();
}
else {

	echoheader( "<i class=\"icon-envelope\"></i>".$lang['main_newsl'], $lang['header_ne_1'] );
	$group_list = get_groups ();

echo <<<HTML
<form method="GET" action="" class="form-horizontal">
<input type="hidden" name="mod" value="newsletter">
<input type="hidden" name="action" value="message">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['nl_main']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
	
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['nl_type']}</label>
		  <div class="col-lg-10">
			<select class="uniform" name="type">
           <option value="email">{$lang['bb_b_mail']}</option>
          <option value="pm">{$lang['nl_pm']}</option></select>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['nl_empf']}</label>
		  <div class="col-lg-10">
			<select name="empfanger[]" style="width:200px;height:90px;" multiple>
           <option value="all" selected>{$lang['edit_all']}</option>
           {$group_list}
		   </select>
		  </div>
		 </div>		  
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['nl_editor']}</label>
		  <div class="col-lg-10">
			<select name="editor" class="uniform">
           <option value="bbcodes">BBCODES</option>
          <option value="wysiwyg">WYSIWYG</option></select>
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['nl_startfrom']}</label>
		  <div class="col-lg-10">
			<input type="text" size="10" name="start_from" value="0"> {$lang['nl_user']}
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['nl_n_mail']}</label>
		  <div class="col-lg-10">
			<input type="text" size="10" name="limit" value="20">
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['nl_interval']}</label>
		  <div class="col-lg-10">
			<input type="text" size="10" name="interval" value="3">
		  </div>
		 </div>	
		<div class="form-group">
		  <label class="control-label col-lg-2">{$lang['nl_amail']}</label>
		  <div class="col-lg-10">
			<input type="checkbox" name="a_mail" value="1" class="icheck" >
		  </div>
		 </div>			 
		<div class="form-group">
		  <label class="control-label col-lg-2"></label>
		  <div class="col-lg-10">
			<input type="submit" class="btn btn-blue" value="{$lang['edit_next']}">
		  </div>
		 </div>	
	 </div>
	
   </div>
</div>
</form>
HTML;

  echofooter();
}
?>