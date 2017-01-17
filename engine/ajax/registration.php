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
 Файл: registration.php
-----------------------------------------------------
 Назначение: AJAX проверки имени
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

	$config['http_home_url'] = explode("engine/ajax/registration.php", $_SERVER['PHP_SELF']);
	$config['http_home_url'] = reset($config['http_home_url']);
	$config['http_home_url'] = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];

}

require_once ENGINE_DIR.'/classes/mysql.php';
require_once ENGINE_DIR.'/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';

dle_session();

$_COOKIE['dle_skin'] = trim(totranslit( $_COOKIE['dle_skin'], false, false ));

if ($_COOKIE['dle_skin']) {
	if (@is_dir(ROOT_DIR.'/templates/'.$_COOKIE['dle_skin']))
		{
			$config['skin'] = $_COOKIE['dle_skin'];
		}
}

if ($config["lang_".$config['skin']]) {
	if ( file_exists( ROOT_DIR.'/language/'.$config["lang_".$config['skin']].'/website.lng' ) ) {
		include_once ROOT_DIR.'/language/'.$config["lang_".$config['skin']].'/website.lng';
	} else die("Language file not found");
} else {

     include_once ROOT_DIR.'/language/'.$config['langs'].'/website.lng';

}
$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];

require_once ENGINE_DIR.'/modules/functions.php';
require_once ENGINE_DIR.'/classes/parse.class.php';

$parse = new ParseFilter();


function check_name($name)
{
	global $lang, $db, $banned_info, $relates_word, $config;

	$stop = '';

	if (dle_strlen($name, $config['charset']) > 30 OR dle_strlen(trim($name), $config['charset']) < 3)
	{

            $stop .= $lang['reg_err_3'];
	}


	if (preg_match("/[\||\'|\<|\>|\[|\]|\"|\!|\?|\$|\@|\#|\/|\\\|\&\~\*\{\+]/",$name))
	{

            $stop .= $lang['reg_err_4'];
	}

	if( stripos(urlencode ($name), "%AD") !== false ) {

		$stop .= $lang['reg_err_4'];

	}

	if (strpos( strtolower ($name) , '.php' ) !== false) {
            $stop .= $lang['reg_err_4'];
	}

	if (count($banned_info['name']))
		foreach($banned_info['name'] as $banned){

			$banned['name'] = str_replace( '\*', '.*' ,  preg_quote($banned['name'], "#") );

			if ( $banned['name'] AND preg_match( "#^{$banned['name']}$#i", $name ) ) {

				if ($banned['descr']) {
					$lang['reg_err_21']	= str_replace("{descr}", $lang['reg_err_22'], $lang['reg_err_21']);
					$lang['reg_err_21']	= str_replace("{descr}", $banned['descr'], $lang['reg_err_21']);
				} else $lang['reg_err_21']	= str_replace("{descr}", "", $lang['reg_err_21']);

				$stop .= $lang['reg_err_21'];
			}
	}

	if (!$stop)
	{

		if( function_exists('mb_strtolower') ) {
			$name = trim(mb_strtolower($name, $config['charset']));
		} else {
			$name = trim(strtolower( $name ));
		}
		$search_name=strtr($name, $relates_word);

		$db->query ("SELECT name FROM " . USERPREFIX . "_users WHERE LOWER(name) REGEXP '[[:<:]]{$search_name}[[:>:]]' OR name = '$name'");

        if ($db->num_rows() > 0)
        {
			$stop .= $lang['reg_err_20'];
		}
	}

	if (!$stop) return false; else return $stop;
}

$banned_info = get_vars ("banned");

if (!is_array($banned_info)) {
$banned_info = array ();

$db->query("SELECT * FROM " . USERPREFIX . "_banned");
while($row = $db->get_row()){

	if ($row['users_id']) {

       $banned_info['users_id'][$row['users_id']] = array('users_id' => $row['users_id'], 'descr' => stripslashes($row['descr']), 'date' => $row['date']);

    } else {

		if (count(explode(".", $row['ip'])) == 4)
			$banned_info['ip'][$row['ip']] = array('ip' => $row['ip'], 'descr' => stripslashes($row['descr']), 'date' => $row['date']);
		elseif (strpos( $row['ip'], "@" ) !== false)
			$banned_info['email'][$row['ip']] = array('email' => $row['ip'], 'descr' => stripslashes($row['descr']), 'date' => $row['date']);
		else
			$banned_info['name'][$row['ip']] = array('name' => $row['ip'], 'descr' => stripslashes($row['descr']), 'date' => $row['date']);

   }

}
set_vars ("banned", $banned_info);
$db->free();
}

$name  = $db->safesql(trim(htmlspecialchars($parse->process(convert_unicode($_POST['name'], $config['charset'])), ENT_QUOTES, $config['charset'])));
$name = preg_replace('#\s+#i', ' ', $name);
$allow = check_name($name);

if (!$allow)
	$buffer = "<font color=\"green\">".$lang['reg_ok_ajax']."</font>";
else
	$buffer = "<font color=\"red\">".$allow."</font>";

@header("Content-type: text/html; charset=".$config['charset']);
echo $buffer;
?>