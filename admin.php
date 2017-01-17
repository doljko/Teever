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
 Файл: admin.php
-----------------------------------------------------
 Назначение: админпанель
=====================================================
*/

@ob_start();
@ob_implicit_flush(0);

if( !defined( 'E_DEPRECATED' ) ) {

	@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
	@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

} else {

	@error_reporting ( E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE );
	@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE );

}

@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );

define ( 'DATALIFEENGINE', true );
define ( 'ROOT_DIR', dirname ( __FILE__ ) );
define ( 'ENGINE_DIR', ROOT_DIR . '/engine' );

//#################
$check_referer = true;
//#################

require_once (ENGINE_DIR . '/inc/include/init.php');

if ($is_loged_in == FALSE) {

	$m_auth = $config['auth_metod'] ? $lang['login_box_2'] : $lang['login_box_1'];
	$m_auth2 = $config['auth_metod'] ? "envelope" : "user";
	
	if( ! $handle = opendir( "./language" ) ) {
		die( "Folder /language/ not found" );
	}

	while ( false !== ($file = readdir( $handle )) ) {
		if( is_dir( ROOT_DIR . "/language/$file" ) and ($file != "." and $file != "..") ) {
			$sys_con_langs_arr[$file] = $file;
		}
	}
	closedir( $handle );

	function makeDropDown($options, $name, $selected) {
		$output = "<select class=\"uniform\" style=\"width:100%\" name=\"$name\">\r\n";
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

	$select_language = makeDropDown( $sys_con_langs_arr, "selected_language", $selected_language );

	include_once (ENGINE_DIR . '/skins/default.skin.php');

	$skin_login = str_replace("{mauth}", $m_auth, $skin_login);
	$skin_login = str_replace("{mauth2}", $m_auth2, $skin_login);
	$skin_login = str_replace("{select}", $select_language, $skin_login);
	$skin_login = str_replace("{result}", $result, $skin_login);

	echo $skin_login;

	exit ();

} elseif ($is_loged_in == TRUE) {

	// ********************************************************************************
	// Подключение модулей админпанели
	// ********************************************************************************

	if ( !$mod ) {

		include_once (ENGINE_DIR . '/inc/main.php');

	} elseif ( @file_exists( ENGINE_DIR . '/inc/' . $mod . '.php' ) ) {

		include_once (ENGINE_DIR . '/inc/' . $mod . '.php');

	} else {

		msg ( "error", $lang['index_denied'], $lang['mod_not_found'] );
	}
}

$db->close ();

GzipOut ();
?>