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
 Файл: videoconfig.php
-----------------------------------------------------
 Назначение: настройка видеоплееров
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

require_once (ENGINE_DIR . '/data/videoconfig.php');



if( $action == "save" ) {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '78', '')" );
	
	$save_con = $_POST['save_con'];
	$save_con['tube_related'] = intval($save_con['tube_related']);
	$save_con['tube_dle'] = intval($save_con['tube_dle']);
	$save_con['preload'] = intval($save_con['preload']);

	
	$find = array();
	$replace = array();
	
	$find[] = "'\r'";
	$replace[] = "";
	$find[] = "'\n'";
	$replace[] = "";
	
	$save_con = $save_con + $video_config;
	
	$handler = fopen( ENGINE_DIR . '/data/videoconfig.php', "w" );
	
	fwrite( $handler, "<?PHP \n\n//Videoplayers Configurations\n\n\$video_config = array (\n\n" );
	foreach ( $save_con as $name => $value ) {
		
		$value = trim(strip_tags(stripslashes( $value )));
		$value = htmlspecialchars( $value, ENT_QUOTES, $config['charset']);
		$value = preg_replace( $find, $replace, $value );
			
		$name = trim(strip_tags(stripslashes( $name )));
		$name = htmlspecialchars( $name, ENT_QUOTES, $config['charset'] );
		$name = preg_replace( $find, $replace, $name );
		
		$value = str_replace( "$", "&#036;", $value );
		$value = str_replace( "{", "&#123;", $value );
		$value = str_replace( "}", "&#125;", $value );
		if( $name != "flv_watermark_al") $value = str_replace( ".", "", $value );
		$value = str_replace( '/', "", $value );
		$value = str_replace( chr(92), "", $value );
		$value = str_replace( chr(0), "", $value );
		$value = str_replace( '(', "", $value );
		$value = str_replace( ')', "", $value );
		$value = str_ireplace( "base64_decode", "base64_dec&#111;de", $value );
		
		$name = str_replace( "$", "&#036;", $name );
		$name = str_replace( "{", "&#123;", $name );
		$name = str_replace( "}", "&#125;", $name );
		$name = str_replace( ".", "", $name );
		$name = str_replace( '/', "", $name );
		$name = str_replace( chr(92), "", $name );
		$name = str_replace( chr(0), "", $name );
		$name = str_replace( '(', "", $name );
		$name = str_replace( ')', "", $name );
		$name = str_ireplace( "base64_decode", "base64_dec&#111;de", $name );
		
		fwrite( $handler, "'{$name}' => '{$value}',\n\n" );
	
	}
	fwrite( $handler, ");\n\n?>" );
	fclose( $handler );
	
	clear_cache();
	msg( "info", $lang['opt_sysok'], $lang['opt_sysok_1'], "?mod=videoconfig" );
}



	echoheader( "<i class=\"icon-play\"></i>".$lang['header_me_1'], $lang['opt_vconf'] );

function showRow($title = "", $description = "", $field = "", $class = "") {
	echo "<tr>
       <td class=\"col-xs-10 col-sm-6 col-md-7 {$class}\"><h6>{$title}</h6><span class=\"note large\">{$description}</span></td>
       <td class=\"col-xs-2 col-md-5 settingstd {$class}\">{$field}</td>
       </tr>";
}
	
function makeDropDown($options, $name, $selected) {
	$output = "<select class=\"uniform\" style=\"min-width:100px;\" name=\"$name\">\r\n";
	foreach ( $options as $value => $description ) {
		$output .= "<option value=\"$value\"";
		if( $selected == $value ) {
			$output .= " selected ";
		}
		$output .= ">$description</option>\n";
	}
	$output .= "</select>";
	return $output;
}

function makeCheckBox($name, $selected) {
	$selected = $selected ? "checked" : "";
	
	return "<input class=\"iButton-icons-tab\" type=\"checkbox\" name=\"$name\" value=\"1\" {$selected}>";
}


echo <<<HTML
<form action="?mod=videoconfig&action=save" name="conf" id="conf" method="post">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['vconf_title']}</div>
  </div>
  <div class="box-content">
  <table class="table table-normal">
HTML;

	showRow( $lang['vconf_widht'], $lang['vconf_widhtd'], "<input type=text style=\"text-align: center;\" name=\"save_con[width]\" value=\"{$video_config['width']}\" size=20>", "white-line" );
	showRow( $lang['vconf_height'], $lang['vconf_heightd'], "<input type=text style=\"text-align: center;\" name=\"save_con[height]\" value=\"{$video_config['height']}\" size=20>" );
	showRow( $lang['vconf_awidht'], $lang['vconf_awidhtd'], "<input type=text style=\"text-align: center;\" name=\"save_con[audio_width]\" value=\"{$video_config['audio_width']}\" size=20>" );

	showRow( $lang['opt_sys_turel'], $lang['opt_sys_tureld'], makeCheckBox( "save_con[tube_related]", "{$video_config['tube_related']}" ) );
	showRow( $lang['opt_sys_tudle'], $lang['opt_sys_tudled'], makeCheckBox( "save_con[tube_dle]", "{$video_config['tube_dle']}" ) );
	showRow( $lang['opt_sys_preload'], $lang['opt_sys_preloadd'], makeCheckBox( "save_con[preload]", "{$video_config['preload']}" ) );


echo <<<HTML
</table></div></div>
<div style="margin-bottom:30px;">
<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
<input type="submit" class="btn btn-green" value="{$lang['user_save']}">
</div>

</form>
HTML;

	if(!is_writable(ENGINE_DIR . '/data/videoconfig.php')) {

		$lang['stat_system'] = str_replace ("{file}", "engine/data/videoconfig.php", $lang['stat_system']);

		echo "<div class=\"alert alert-error\">{$lang['stat_system']}</div>";

	}

echofooter();
?>