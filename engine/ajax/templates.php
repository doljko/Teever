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
 Назначение: AJAX для редактирования шаблонов
=====================================================
*/

@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

define( 'DATALIFEENGINE', true );
define( 'ROOT_DIR', substr( dirname(  __FILE__ ), 0, -12 ) );
define( 'ENGINE_DIR', ROOT_DIR . '/engine' );

include ENGINE_DIR.'/data/config.php';

date_default_timezone_set ( $config['date_adjust'] );

if ($config['http_home_url'] == "") {

	$config['http_home_url'] = explode("engine/ajax/templates.php", $_SERVER['PHP_SELF']);
	$config['http_home_url'] = reset($config['http_home_url']);
	$config['http_home_url'] = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];

}

require_once ENGINE_DIR.'/classes/mysql.php';
require_once ENGINE_DIR.'/data/dbconfig.php';
require_once ENGINE_DIR.'/inc/include/functions.inc.php';
dle_session();
require_once ENGINE_DIR.'/modules/sitelogin.php';

if(($member_id['user_group'] != 1)) {die ("error");}

$_TIME = time ();

$selected_language = $config['langs'];

if (isset( $_COOKIE['selected_language'] )) { 

	$_COOKIE['selected_language'] = trim(totranslit( $_COOKIE['selected_language'], false, false ));

	if ($_COOKIE['selected_language'] != "" AND @is_dir ( ROOT_DIR . '/language/' . $_COOKIE['selected_language'] )) {
		$selected_language = $_COOKIE['selected_language'];
	}

}
if ( file_exists( ROOT_DIR.'/language/'.$selected_language.'/adminpanel.lng' ) ) {
	require_once ROOT_DIR.'/language/'.$selected_language.'/adminpanel.lng';
} else die("Language file not found");

$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];

$allowed_extensions = array ("tpl", "css", "js");


@header("Content-type: text/html; charset=".$config['charset']);

function clear_url_dir($var) {
	if ( is_array($var) ) return "";

	$var = str_replace(chr(0), '', $var);
	$var = str_ireplace( ".php", "", $var );
	$var = str_ireplace( ".php", ".ppp", $var );
	$var = trim( strip_tags( $var ) );
	$var = str_replace( "\\", "/", $var );
	$var = preg_replace( "/[^a-z0-9\/\_\-]+/mi", "", $var );
	return $var;

}

if($_POST['action'] == "create") {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die ("error");
	
	}

	$template = trim( totranslit($_POST['template'], false, false) );
	$file = trim( totranslit($_POST['file'], false, false) );
	$root = ROOT_DIR . '/templates/';

	if (!$file OR !$template) die ("error");

	if(!file_exists($root.$template."/") ) die ("error");

	if(!is_writable($root.$template."/")) {
	
		$lang['stat_template'] = str_replace ("{template}", '/templates/'.$template.'/', $lang['stat_template']);
	
		echo $lang['stat_template']; die();
	
	}

	if(file_exists($root.$template."/".$file.".tpl") ) { echo $lang['template_create_err']; die();}

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '69', '{$template}/{$file}.tpl')" );

	$handle = fopen( $root.$template."/".$file.".tpl", "w" );
	fwrite( $handle, "" );
	fclose( $handle );

	@chmod( $root.$template."/".$file.".tpl", 0666 );

	echo "ok"; die();

} elseif($_POST['action'] == "save") {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die ("error");
	
	}

	$_POST['file'] = trim(str_replace( "..", "", urldecode($_POST['file']) ));
	
	if(!$_POST['file']) { die ("error"); }
	
	$url = @parse_url ( $_POST['file'] );

	$root = ROOT_DIR . '/templates/';
	$file_path = dirname (clear_url_dir($url['path']));
	$file_name = pathinfo($url['path']);
	$file_name = totranslit($file_name['basename'], false, true);

	$type = explode( ".", $file_name );
	$type = totranslit( end( $type ) );
	
	if(!in_array( $type, $allowed_extensions ) ) die ("error");

	if(!file_exists($root.$file_path."/".$file_name) ) die ("error");

	if(!is_writable($root.$file_path."/".$file_name)) { echo " <font color=\"red\">".$lang['template_edit_fail']."</font>"; die (); }

	$_POST['content'] = convert_unicode( $_POST['content'], $config['charset']  );

	if( function_exists( "get_magic_quotes_gpc" ) && get_magic_quotes_gpc() ) $_POST['content'] = stripslashes( $_POST['content'] );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '70', '{$file_path}/{$file_name}')" );

	$handle = fopen( $root.$file_path."/".$file_name, "w" );
	fwrite( $handle, $_POST['content'] );
	fclose( $handle );

	if ($type == "css" OR $type == "js") {

		$fdir = opendir( ENGINE_DIR . '/cache/system/' );
		while ( $file = readdir( $fdir ) ) {
			if( $file != '.' and $file != '..' and $file != '.htaccess' and $file != 'cron.php' ) {
				@unlink( ENGINE_DIR . '/cache/system/' . $file );
			
			}
		}

	}

	clear_cache();
	echo "ok"; die();


} elseif($_POST['action'] == "load") {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die ("error");
	
	}

	$_POST['file'] = trim(str_replace( "..", "", urldecode($_POST['file']) ));
	
	if(!$_POST['file']) { die ("error"); }
	
	$url = @parse_url ( $_POST['file'] );

	$root = ROOT_DIR . '/templates/';
	$file_path = dirname (clear_url_dir($url['path']));
	$file_name = pathinfo($url['path']);
	$file_name = totranslit($file_name['basename'], false, true);

	$type = explode( ".", $file_name );
	$type = totranslit( end( $type ) );
	
	if ( !in_array( $type, $allowed_extensions ) ) die ("error");

	if( !file_exists($root.$file_path."/".$file_name) ) die ("error");

	$content = @htmlspecialchars( file_get_contents( $root.$file_path."/".$file_name ), ENT_QUOTES, $config['charset'] );

	echo $lang['template_edit']." ".$file_path."/".$file_name;

	if(!is_writable($root.$file_path."/".$file_name)) echo " <font color=\"red\">".$lang['template_edit_fail']."</font>";

	echo "<br />".$lang['hot_keys'];

	$script= "";

	if ($type == "tpl") {
		$script= <<<HTML
<script language="JavaScript" type="text/javascript">
  var editor = CodeMirror.fromTextArea(document.getElementById('file_text'), {
    mode: "htmlmixed",
	lineNumbers: true,
	dragDrop: false,
    indentUnit: 4,
    indentWithTabs: false
  });
</script>
HTML;

	}

	if ($type == "css") {
		$script= <<<HTML
<script language="JavaScript" type="text/javascript">
  var editor = CodeMirror.fromTextArea(document.getElementById('file_text'), {
    indentUnit: 4,
	lineNumbers: true,
	dragDrop: false,
    mode: "css"
  });
</script>
HTML;

	}

	if ($type == "js") {
		$script= <<<HTML
<script language="JavaScript" type="text/javascript">
  var editor = CodeMirror.fromTextArea(document.getElementById('file_text'), {
    lineNumbers: true,
    matchBrackets: true,
	indentUnit: 4,
	dragDrop: false,
    mode: "javascript"
  });
</script>
HTML;

	}

	echo <<<HTML
<br /><br /><div style="border: solid 1px #BBB;width:99%;height:463px;"><textarea style="width:100%;height:440px;" name="file_text" id="file_text" wrap="off">{$content}</textarea></div>
<div style="padding:5px;"><input onClick="savefile('{$file_path}/{$file_name}')" type="button" class="btn btn-green" value="{$lang['user_save']}" style="width:100px;"></div>
{$script}
HTML;

} else {


	$root = ROOT_DIR . '/templates/';
	$_POST['dir'] = clear_url_dir(urldecode($_POST['dir']));
	
	if( file_exists($root . $_POST['dir']) ) {
		$files = scandir($root . $_POST['dir']);
		natcasesort($files);
		if( count($files) > 2 ) {
			echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
			// All dirs
			foreach( $files as $file ) {
				if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && is_dir($root . $_POST['dir'] . $file) ) {
					echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "/\">" . htmlentities($file) . "</a></li>";
				}
			}
			// All files
			foreach( $files as $file ) {
				if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && !is_dir($root . $_POST['dir'] . $file) ) {
					$serverfile_arr = explode( ".", $file );
					$ext = totranslit( end( $serverfile_arr ) );
	
					if ( in_array( $ext, $allowed_extensions ) )
						echo "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "\">" . htmlentities($file) . "</a></li>";
				}
			}
			echo "</ul>";	
		}
	}
}

?>