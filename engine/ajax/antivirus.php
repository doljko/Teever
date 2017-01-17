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
 Файл: antivirus.php
-----------------------------------------------------
 Назначение: Проверка на подозрительные файлы
=====================================================
*/

@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

define('DATALIFEENGINE', true);
define( 'ROOT_DIR', substr( dirname(  __FILE__ ), 0, -12 ) );
define( 'ENGINE_DIR', ROOT_DIR . '/engine' );

include ENGINE_DIR.'/data/config.php';

date_default_timezone_set ( $config['date_adjust'] );

if ($config['http_home_url'] == "") {

	$config['http_home_url'] = explode("engine/ajax/antivirus.php", $_SERVER['PHP_SELF']);
	$config['http_home_url'] = reset($config['http_home_url']);
	$config['http_home_url'] = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];

}

require_once ENGINE_DIR.'/classes/mysql.php';
require_once ENGINE_DIR.'/data/dbconfig.php';
require_once ENGINE_DIR.'/inc/include/functions.inc.php';

dle_session();

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

require_once ENGINE_DIR.'/modules/sitelogin.php';

if($member_id['user_group'] != 1) {die ("error");}

require_once ENGINE_DIR.'/classes/antivirus.class.php';

$antivirus = new antivirus();

if ($_REQUEST['folder'] == "lokal"){

	if( $antivirus->snap ) {

		$antivirus->scan_files( ROOT_DIR, false, true );

	} else {

		$antivirus->scan_files( ROOT_DIR."/backup", false );
		$antivirus->scan_files( ROOT_DIR."/engine", false);
		$antivirus->scan_files( ROOT_DIR."/language", false );
		$antivirus->scan_files( ROOT_DIR."/templates", false);
		$antivirus->scan_files( ROOT_DIR."/uploads", false );
		$antivirus->scan_files( ROOT_DIR."/upgrade", false );
		$antivirus->scan_files( ROOT_DIR, false);
	}

} elseif ($_REQUEST['folder'] == "snap") {

	$antivirus->scan_files( ROOT_DIR, true, true );

	$filecontents = "";

    foreach( $antivirus->snap_files as $idx => $data )
    {
		$filecontents .= $data['file_path']."|".$data['file_crc']."\r\n";
    }

    $filehandle = fopen(ENGINE_DIR.'/data/snap.db', "w+");
    fwrite($filehandle, $filecontents);
    fclose($filehandle);
	@chmod(ENGINE_DIR.'/data/snap.db', 0666);

} else {

	$antivirus->snap = false;
	$antivirus->scan_files( ROOT_DIR, false, true );

}

if ($_REQUEST['folder'] != "snap") {
	$con_content = @file_get_contents( ROOT_DIR . "/engine/data/config.php");

	if (strpos ( $con_content, "_SERVER" ) !== false OR strpos ( $con_content, "eval" ) !== false) {
	
		$file_date = date("d.m.Y H:i:s", filectime(ROOT_DIR . "/engine/data/config.php"));
		$file_size = filesize(ROOT_DIR . "/engine/data/config.php");
	
		 $antivirus->bad_files[] = array( 'file_path' => "/engine/data/config.php",
									'file_name' => "config.php",
									'file_date' => $file_date,
									'type' => 2,
									'file_size' => $file_size ); 
	}

	$con_content = @file_get_contents( ROOT_DIR . "/engine/data/dbconfig.php");

	if (strpos ( $con_content, "_SERVER" ) !== false OR strpos ( $con_content, "eval" ) !== false) {
	
		$file_date = date("d.m.Y H:i:s", filectime(ROOT_DIR . "/engine/data/dbconfig.php"));
		$file_size = filesize(ROOT_DIR . "/engine/data/dbconfig.php");
	
		 $antivirus->bad_files[] = array( 'file_path' => "/engine/data/dbconfig.php",
									'file_name' => "dbconfig.php",
									'file_date' => $file_date,
									'type' => 2,
									'file_size' => $file_size ); 
	}

	$con_content = @file_get_contents( ROOT_DIR . "/engine/data/videoconfig.php");

	if (strpos ( $con_content, "_SERVER" ) !== false OR strpos ( $con_content, "eval" ) !== false) {
	
		$file_date = date("d.m.Y H:i:s", filectime(ROOT_DIR . "/engine/data/videoconfig.php"));
		$file_size = filesize(ROOT_DIR . "/engine/data/videoconfig.php");
	
		$antivirus->bad_files[] = array( 'file_path' => "/engine/data/videoconfig.php",
									'file_name' => "videoconfig.php",
									'file_date' => $file_date,
									'type' => 2,
									'file_size' => $file_size ); 
	}

}



@header("Content-type: text/html; charset=".$config['charset']);

if (count($antivirus->bad_files)) {

echo <<<HTML
<div class="row box-section">{$lang['anti_result']}</div>
<table class="table table-normal">
<thead>
    <tr>
        <td>{$lang['anti_file']}</td>
        <td style="width:8%;">{$lang['anti_size']}</td>
        <td style="width:17%;">{$lang['addnews_date']}</td>
        <td style="width:17%;">&nbsp;</td>
    </tr>
</thead>
<tbody>
HTML;

  foreach( $antivirus->bad_files as $idx => $data )
  { 

	if ($data['file_size'] < 50000) $color = "<font color=\"green\">";
	elseif ($data['file_size'] < 100000) $color = "<font color=\"blue\">";
	else $color = "<font color=\"red\">";

	$data['file_size'] = formatsize ($data['file_size']);

	if ($data['type']) $type = $lang['anti_modified']; else $type = $lang['anti_not'];

	if ($data['type'] == 2 ) $type = $lang['anti_modified_1'];

	$data['file_path'] = preg_replace("/([0-9]){10}_/", "*****_", $data['file_path']);

echo <<<HTML
    <tr>
        <td>{$color}{$data['file_path']}</font></td>
        <td>{$color}{$data['file_size']}</font></td>
        <td>{$color}{$data['file_date']}</font></td>
        <td>{$color}{$type}</font></td>
    </tr>
HTML;
  }

echo <<<HTML
</tbody>
</table>
HTML;

}
elseif ($_REQUEST['folder'] == "snap") {

echo <<<HTML
<div class="row box-section">{$lang['anti_creates']}</div>
HTML;

}
else {

echo <<<HTML
<div class="row box-section">{$lang['anti_notfound']}</div>
HTML;

}

echo <<<HTML
<div class="row box-section"><button class="btn btn-gray" onclick="check_files('global'); return false;"><i class="icon-search"></i> {$lang['anti_global']}</button> <button class="btn btn-gold" onclick="check_files('snap'); return false;"><i class="icon-magic"></i> {$lang['anti_snap']}</button></div>
HTML;
?>