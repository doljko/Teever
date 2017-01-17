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
 Файл: dboption.php
-----------------------------------------------------
 Назначение: работа с базой данных
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['addnews_denied'], $lang['db_denied'] );
}

if( isset( $_REQUEST['restore'] ) ) $restore = $_REQUEST['restore']; else $restore = "";

if( $action == "dboption" and count( $_REQUEST['ta'] ) ) {
	$arr = $_REQUEST['ta'];
	reset( $arr );
	
	$tables = "";
	
	while ( list ( $key, $val ) = each( $arr ) ) {
		$tables .= ", `" . $db->safesql( $val ) . "`";
	}
	
	$tables = substr( $tables, 1 );
	if( $_REQUEST['whattodo'] == "optimize" ) {
		$query = "OPTIMIZE TABLE  ";
	} else {
		$query = "REPAIR TABLE ";
	}
	$query .= $tables;

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '23', '')" );

	
	if( $db->query( $query ) ) {
		msg( "info", $lang['db_ok'], $lang['db_ok_1'] . "<br /><br /><a href=\"?mod=dboption\">" . $lang['db_prev'] . "</a>" );
	} else {
		msg( "error", $lang['db_err'], $lang['db_err_1'] . "<br /><br /><a href=\"?mod=dboption\">" . $lang['db_prev'] . "</a>" );
	}

}

echoheader( "<i class=\"icon-hdd\"></i>".$lang['opt_db'], $lang['db_info'] );

$tabellen = "";

$db->query( "SHOW TABLES" );
while ( $row = $db->get_array() ) {
	$titel = $row[0];
	if( substr( $titel, 0, strlen( PREFIX ) ) == PREFIX ) {
		$tabellen .= "<option value=\"{$titel}\" selected>{$titel}</option>\n";
	}
}
$db->free();

echo <<<HTML
<form action="?mod=dboption&action=dboption" method="post">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['db_info']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
	
	  <div class="col-md-3">
		<select style="width:100%;" size="8" name="ta[]" multiple="multiple">{$tabellen}</select>
		<br /><br /><input type="submit" id="rest" class="btn btn-gray" value="{$lang['db_action']}" />
	  </div>
	  
	  <div class="col-md-9">
		<table width="100%">
          <tr>
            <td style="width:70px;"><i class="icon-retweet" style="font-size:500%"></i></td>
            <td width="5%" nowrap="nowrap"><div align="left">
                <input style="border:0px" type="radio" name="whattodo" checked="checked" value="optimize" />
              </div></td>
            <td class="option"><h5>{$lang['db_opt']}</h5>{$lang['db_opt_i']}</td>
          </tr>
          <tr>
            <td><i class="icon-magic" style="font-size:400%"></i></td>
            <td width="5%" nowrap="nowrap"><div align="left">
                <input style="border:0px" type="radio" name="whattodo" value="repair" />
              </div></td>
            <td class="option"><h5>{$lang['db_re']}</h5>{$lang['db_re_i']}</td>
          </tr>
        </table>
		
	  </div>
	  
	</div>
	
   </div>
</div>
</form>
HTML;

if( function_exists( "bzopen" ) ) {
	$comp_methods[2] = 'BZip2';
}
if( function_exists( "gzopen" ) ) {
	$comp_methods[1] = 'GZip';
}
$comp_methods[0] = $lang['opt_notcompress'];

function fn_select($items, $selected) {
	$select = '';
	foreach ( $items as $key => $value ) {
		$select .= $key == $selected ? "<OPTION VALUE='{$key}' SELECTED>{$value}" : "<OPTION VALUE='{$key}'>{$value}";
	}
	return $select;
}
$comp_methods = fn_select( $comp_methods, '' );

echo <<<HTML
<script type="text/javascript">
    function save(){

		var rndval = new Date().getTime(); 

		$('body').append('<div id="modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #666666; opacity: .40;filter:Alpha(Opacity=40); z-index: 999; display:none;"></div>');
		$('#modal-overlay').css({'filter' : 'alpha(opacity=40)'}).fadeIn('slow');
	
		$("#dlepopup").remove();
		$("body").append("<div id='dlepopup' title='{$lang['db_back']}' style='display:none'></div>");
	
		$('#dlepopup').dialog({
			autoOpen: true,
			width: 540,
			height: 345,
			resizable: false,
			dialogClass: "modalfixed",
			buttons: {
				"Ok": function() { 
					$(this).dialog("close");
					$("#dlepopup").remove();							
				} 
			},
			open: function(event, ui) { 
				$("#dlepopup").html("<iframe width='99%' height='220' src='?mod=dumper&action=backup&comp_method=" + $("#comp_method").val() + "&rndval=" + rndval + "' frameborder='0' marginwidth='0' marginheight='0' scrolling='no'></iframe>");
			},
			beforeClose: function(event, ui) { 
				$("#dlepopup").html("");
			},
			close: function(event, ui) {
					$('#modal-overlay').fadeOut('slow', function() {
			        $('#modal-overlay').remove();
			    });
			 }

		});

		if ($(window).width() > 830 && $(window).height() > 530 ) {
			$('.modalfixed.ui-dialog').css({position:"fixed"});
			$( '#dlepopup').dialog( "option", "position", ['0','0'] );
		}

		return false;

    }
</script>
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['db_back']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
	
		{$lang['b_method']} <select class="uniform" name="comp_method" id="comp_method">{$comp_methods}</select>&nbsp;&nbsp;<input type="button" class="btn btn-green" onclick="save(); return false;" value="{$lang['b_save']}" />
	  
	</div>
	
   </div>
</div>
HTML;

define( 'PATH', 'backup/' );

function file_select() {
	$files = array ('' );
	if( is_dir( PATH ) && $handle = opendir( PATH ) ) {
		while ( false !== ($file = readdir( $handle )) ) {
			if( preg_match( "/^.+?\.sql(\.(gz|bz2))?$/", $file ) ) {
				$files[$file] = $file;
			}
		}
		closedir( $handle );
	}
	return $files;
}

$files = fn_select( file_select(), '' );

echo <<<HTML
<script type="text/javascript">
    function dbload(){

		var rndval = new Date().getTime(); 

		$('body').append('<div id="modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #666666; opacity: .40;filter:Alpha(Opacity=40); z-index: 999; display:none;"></div>');
		$('#modal-overlay').css({'filter' : 'alpha(opacity=40)'}).fadeIn('slow');
	
		$("#dlepopup").remove();
		$("body").append("<div id='dlepopup' title='{$lang['db_load']}' style='display:none'></div>");
	
		$('#dlepopup').dialog({
			autoOpen: true,
			width: 540,
			height: 345,
			resizable: false,
			dialogClass: "modalfixed",
			buttons: {
				"Ok": function() { 
					$(this).dialog("close");
					$("#dlepopup").remove();							
				} 
			},
			open: function(event, ui) { 
				$("#dlepopup").html("<iframe width='99%' height='220' src='?mod=dumper&action=restore&file=" + $("#file").val() + "&rndval=" + rndval + "' frameborder='0' marginwidth='0' marginheight='0' scrolling='no'></iframe>");
			},
			beforeClose: function(event, ui) { 
				$("#dlepopup").html("");
			},
			close: function(event, ui) {
					$('#modal-overlay').fadeOut('slow', function() {
			        $('#modal-overlay').remove();
			    });
			 }
		});

		if ($(window).width() > 830 && $(window).height() > 530 ) {
			$('.modalfixed.ui-dialog').css({position:"fixed"});
			$( '#dlepopup' ).dialog( "option", "position", ['0','0'] );
		}

		return false;

    }
</script>
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['db_load']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
	
		{$lang['b_restore']} <select class="uniform" name="file" id="file">{$files}</select>&nbsp;&nbsp;<input type="button" class="btn btn-red" onclick="dbload(); return false;" value="{$lang['b_load']}" />
	  
	</div>
	
   </div>
</div>
HTML;

echofooter();
?>