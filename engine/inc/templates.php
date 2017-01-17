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
 Файл: templates.php
-----------------------------------------------------
 Назначение: Управление шаблонами
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['opt_denied'], $lang['opt_denied'] );
}

if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
	
	header( "Location: ?mod=templates&user_hash=" . $dle_login_hash );

}

$_REQUEST['do_template'] = trim( totranslit($_REQUEST['do_template'], false, false) );

$do_template = $_REQUEST['do_template'];
$subaction = $_REQUEST['subaction'];

$templates_list = array ();
if( ! $handle = opendir( ROOT_DIR . "/templates" ) ) {
	die( $lang['opt_errfo'] );
}
while ( false !== ($file = readdir( $handle )) ) {
	if( is_dir( ROOT_DIR . "/templates/$file" ) and ($file != "." and $file != "..") ) {
		$templates_list[] = $file;
	}
}
closedir( $handle );
sort($templates_list);


$language_list = array ();
if( ! $handle = opendir( ROOT_DIR . "/language" ) ) {
	die( $lang['opt_errfo'] );
}
while ( false !== ($file = readdir( $handle )) ) {
	if( is_dir( ROOT_DIR . "/language/$file" ) and ($file != "." and $file != "..") ) {
		$language_list[] = $file;
	}
}
closedir( $handle );

if( $_REQUEST['subaction'] == "language" ) {
	
	$allow_save = false;
	$_REQUEST['do_template'] = trim( totranslit($_REQUEST['do_template'], false, false) );
	$_REQUEST['do_language'] = trim( totranslit($_REQUEST['do_language'], false, false) );

	if( $_REQUEST['do_template'] != "" and $_REQUEST['do_language'] != "" ) {
		$config["lang_" . $_REQUEST['do_template']] = $_REQUEST['do_language'];
		$allow_save = true;
	
	} elseif( $config["lang_" . $_REQUEST['do_template']] and $_REQUEST['do_language'] == "" ) {
		unset( $config["lang_" . $_REQUEST['do_template']] );
		$allow_save = true;
	}
	
	if( $allow_save ) {

		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '66', '{$_REQUEST['do_template']}')" );
		
		if( $auto_detect_config ) $config['http_home_url'] = "";
		
		$handler = fopen( ENGINE_DIR . '/data/config.php', "w" );
		fwrite( $handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n" );
		foreach ( $config as $name => $value ) {
			fwrite( $handler, "'{$name}' => \"{$value}\",\n\n" );
		}
		fwrite( $handler, ");\n\n?>" );
		fclose( $handler );
	
	}

}

if( $subaction == "new" ) {

	$b_form = "<form method=\"post\" ><table width=100%><tr><td height=\"150\"><center>$lang[opt_newtemp_1] <select name=base_template>";

	foreach ( $templates_list as $single_template ) {
		$b_form .= "<option value=\"$single_template\">$single_template</option>";
	}

	$b_form .= '</select> ' . $lang[opt_msgnew] . ' <input class="edit" type=text name=template_name> <br /><br /><input type="submit" value="' . $lang['b_start'] . '" class="btn btn-primary">
        <input type=hidden name=mod value=templates>
        <input type=hidden name=action value=templates>
        <input type=hidden name=subaction value=donew>
        <input type=hidden name=user_hash value="' . $dle_login_hash . '">
        </td></tr></table></form>';

		msg( "info", $lang['create_template'], $b_form );
	exit();
}

/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      Создания нового шаблона
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
if( $subaction == "donew" ) {
	
	function open_dir($dir, $newdir) { //The function that will copy the files
		if( file_exists( $dir ) && file_exists( $newdir ) ) {
			$open_dir = opendir( $dir );
			while ( false !== ($file = readdir( $open_dir )) ) {
				if( $file != "." && $file != ".." ) {
					if( @filetype( $dir . "/" . $file . "/" ) == "dir" ) {
						if( ! file_exists( $newdir . "/" . $file . "/" ) ) {
							mkdir( $newdir . "/" . $file . "/" );
							@chmod( $newdir . "/" . $file, 0777 );
							open_dir( $dir . "/" . $file . "/", $newdir . "/" . $file . "/" );
						}
					} else {
						copy( $dir . "/" . $file, $newdir . "/" . $file );
						@chmod( $newdir . "/" . $file, 0666 );
					}
				}
			}
		}
	}

	$base_template = trim( totranslit($_REQUEST['base_template'], false, false) );
	$template_name = trim( totranslit($_REQUEST['template_name'], false, false) );
	
	if( preg_match( "/[\||\'|\<|\>|\[|\]|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $template_name ) ) {
		msg( "error", $lang['opt_error'], $lang['opt_error_1'], "?mod=templates&subaction=new&user_hash={$dle_login_hash}" );
	}
	
	$result = @mkdir( ROOT_DIR . "/templates/" . $template_name, 0777 );
	@chmod( ROOT_DIR . "/templates/" . $template_name, 0777 );
	
	if( ! $result ) msg( "error", $lang['opt_error'], $lang['opt_cr_err'], "?mod=templates&subaction=new&user_hash={$dle_login_hash}" );
	else open_dir( ROOT_DIR . "/templates/" . $base_template, ROOT_DIR . "/templates/" . $template_name );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '67', '{$template_name}')" );
	
	msg( "info", $lang['opt_info'], $lang['opt_info_1'], "?mod=templates&user_hash={$dle_login_hash}" );
}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      Подготовка к удалению
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
if( $subaction == "delete" ) {
	if( strtolower( $do_template ) == strtolower($config['skin']) OR strtolower( $do_template ) == "smartphone" OR strtolower( $do_template ) == '' ) {
		msg( "Error", $lang['opt_error'], $lang['opt_error_4'], "?mod=templates&user_hash={$dle_login_hash}" );
	}
	$msg = "<form method=\"post\">$lang[opt_info_2] <b>$do_template</b>?<br><br>
        <input class=\"btn btn-success\" type=submit value=\" $lang[opt_yes] \"> &nbsp;<input class=\"btn btn-danger\" onClick=\"document.location='?mod=templates';\" type=button value=\"$lang[opt_no]\">
        <input type=hidden name=mod value=templates>
        <input type=hidden name=subaction value=dodelete>
        <input type=hidden name=do_template value=\"$do_template\">
        <input type=hidden name=user_hash value=\"$dle_login_hash\">
        </form>";
	
	msg( "info", $lang['opt_info_3'], $msg );
}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      Удаление шаблона
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
if( $subaction == "dodelete" ) {
	if( strtolower( $do_template ) == strtolower($config['skin']) OR strtolower( $do_template ) == "smartphone" ) {
		msg( "Error", $lang['opt_error'], $lang['opt_error_4'], "?mod=templates&user_hash={$dle_login_hash}" );
	}
	if(!$do_template OR preg_match( "/[\||\'|\<|\>|\[|\]|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $do_template ) ) {
		msg( "error", $lang['opt_error'], $lang['opt_error_1'], "?mod=templates&user_hash={$dle_login_hash}" );
	}

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '68', '{$do_template}')" );
	
	listdir( ROOT_DIR . "/templates/" . $do_template );
	
	msg( "info", $lang['opt_info_3'], $lang['opt_info_4'], "?mod=templates&user_hash={$dle_login_hash}" );
}


/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      Редактирование шаблона
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
$show_delete_link = '';

$do_template = trim( totranslit($do_template, false, false) );

if( $do_template == '' or ! $do_template ) {
	$do_template = $config['skin'];
} elseif( $do_template != $config['skin'] AND $do_template != "smartphone" ) {
	$show_delete_link = "<a class=\"btn btn-red\" href=\"?mod=templates&subaction=delete&user_hash={$dle_login_hash}&do_template=$do_template\">$lang[opt_dellink]</a>";
}

if (!@is_dir ( ROOT_DIR . '/templates/' . $do_template )) {
	die ( "Template not found!" );
}

if(!is_writable(ROOT_DIR . '/templates/' . $do_template . "/")) {

	$lang['stat_template'] = str_replace ("{template}", '/templates/'.$do_template.'/', $lang['stat_template']);

	$fail = "<div class=\"alert alert-error\">{$lang['stat_template']}</div>";

} else $fail = "";

echoheader( "<i class=\"icon-dashboard\"></i>".$lang['header_tm_1'], $lang['header_tm_2'] );

echo <<<HTML
<link rel="stylesheet" type="text/css" href="engine/skins/codemirror/css/default.css">
<script type="text/javascript" src="engine/skins/codemirror/js/code.js"></script>
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['opt_edit_head']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
	
		 <div class="form-group">
		  <div class="col-lg-2">{$lang['opt_theads']}</div>
		  <div class="col-lg-10">
			<b>{$do_template}</b>
		  </div>
		</div>
	</div>
	<div class="row box-section">	
		 <div class="form-group">
		  <div class="col-lg-2">{$lang['opt_sys_al']}</div>
		  <div class="col-lg-10"><form method="post" action="?mod=templates">
			<select class="uniform" name="do_language">
		<option value="">{$lang['sys_global']}</option>
HTML;

foreach ( $language_list as $single_language ) {
	if( $single_language == $config["lang_" . $do_template] ) {
		echo "<option selected value=\"$single_language\">$single_language</option>";
	} else {
		echo "<option value=\"$single_language\">$single_language</option>";
	}
}

echo <<<HTML
		</select>&nbsp;&nbsp;<input type="submit" value="{$lang['b_select']}" class="btn btn-gray"><input type=hidden name=user_hash value="$dle_login_hash"><input type="hidden" name="subaction" value="language"><input type="hidden" name="do_template" value="{$do_template}"></form>
		  </div>
		</div>	
	</div>
	<div class="row box-section">	
		 <div class="form-group">
		  <div class="col-lg-2">{$lang['opt_newtepled']}</div>
		  <div class="col-lg-10"><form method="post" action="?mod=templates"><select class="uniform" name="do_template">
HTML;

foreach ( $templates_list as $single_template ) {
	if( $single_template == $do_template ) {
		echo "<option selected value=\"$single_template\">$single_template</option>";
	} else {
		echo "<option value=\"$single_template\">$single_template</option>";
	}
}

echo <<<HTML
</select>&nbsp;&nbsp;<input type="submit" value="{$lang['b_start']}" class="btn btn-gray">&nbsp;&nbsp;<a onclick="javascript:Help('templates')" class="status-info" href="#">{$lang['opt_temphelp']}</a><input type=hidden name=user_hash value="$dle_login_hash"><input type="hidden" name="action" value="templates"></form>
		  </div>
		</div>
	</div>		
		<div class="row box-section">
			 <div class="form-group">
			  <div class="col-lg-2"></div>
			  <div class="col-lg-10">
				<a class="btn btn-green" href="?mod=templates&subaction=new&action=templates&user_hash={$dle_login_hash}">{$lang['opt_enewtepl']}</a>&nbsp;
				{$show_delete_link}
			  </div>
			</div>
		</div>
		
		
	
   </div>
</div>
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['opt_edteil']} <b>{$do_template}</b> {$lang['templates_help']} <a class="main" href="http://dle-news.ru/extras/online/all2.html" target="_blank">http://dle-news.ru/extras/online/all2.html</a></div>
  </div>
  <div class="box-content">

	<div class="row box-section">
	
	  <div class="col-md-2" style="padding: 0px !important;">
		<div id="filetree" class="filetree"></div>
		<br /><input onClick="createfile()" type="button" class="btn btn-blue" value="{$lang['template_create']}">
	  </div>
	  
	  <div class="col-md-10">
			<div id="fileedit" style="border: solid 1px #BBB;height: 560px; padding:5px;"></div>
	  </div>
	  
	</div>
	
   </div>
</div>
<script type="text/javascript">
$(document).ready( function() {

	$('#filetree').fileTree({ root: '{$do_template}/', script: 'engine/ajax/templates.php', folderEvent: 'click', expandSpeed: 750, collapseSpeed: 750, multiFolder: false }, function(file) { 
	
		ShowLoading('');		
		$.post('engine/ajax/templates.php', { action: "load", file: file, user_hash: "{$dle_login_hash}" }, function(data){
			
			HideLoading('');
			$('#fileedit').html(data);
			
		}, 'html');
	});

});
function savefile( file ){
	var content = editor.getValue();

	$.post('engine/ajax/templates.php', { action: "save", file: file, content: content, user_hash: "{$dle_login_hash}" }, function(data){

		if ( data == "ok" ) {
			Growl.info({
				title: '{$lang[p_info]}',
				text: '{$lang['template_saved']}'
			});
		} else {
			DLEalert( data, '{$lang['p_info']}');
		}

	});

};

function createfile( ){

	DLEprompt("{$lang['template_enter']}", '', "{$lang['p_prompt']}", function (file) {

		ShowLoading('');		
		$.post('engine/ajax/templates.php', { action: "create", file: file, template: '{$do_template}', user_hash: "{$dle_login_hash}" }, function(data){
				
			HideLoading('');
				
			if ( data == "ok" ) {
				document.location='?mod=templates&do_template={$do_template}&user_hash={$dle_login_hash}';
			} else {
				DLEalert( data, '{$lang['p_info']}');
			}
	
		});

	});

};
</script>
{$fail}
HTML;

echofooter();
?>