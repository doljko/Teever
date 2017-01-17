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
 Файл: adminfunction.php
-----------------------------------------------------
 Назначение: Выполнение различных функций админпанели
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

	$config['http_home_url'] = explode("engine/ajax/adminfunction.php", $_SERVER['PHP_SELF']);
	$config['http_home_url'] = reset($config['http_home_url']);
	$config['http_home_url'] = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];

}

require_once ENGINE_DIR.'/classes/mysql.php';
require_once ENGINE_DIR.'/data/dbconfig.php';
require_once ENGINE_DIR.'/inc/include/functions.inc.php';

dle_session();
$_TIME = time ();

//################# Определение групп пользователей
$user_group = get_vars( "usergroup" );

if( ! $user_group ) {
	$user_group = array ();
	
	$db->query( "SELECT * FROM " . USERPREFIX . "_usergroups ORDER BY id ASC" );
	
	while ( $row = $db->get_row() ) {
		
		$user_group[$row['id']] = array ();
		
		foreach ( $row as $key => $value ) {
			$user_group[$row['id']][$key] = stripslashes($value);
		}
	
	}
	set_vars( "usergroup", $user_group );
	$db->free();
}

$cat_info = get_vars( "category" );
		
if( ! is_array( $cat_info ) ) {
	$cat_info = array ();
			
	$db->query( "SELECT * FROM " . PREFIX . "_category ORDER BY posi ASC" );
	while ( $row = $db->get_row() ) {
		
		$cat_info[$row['id']] = array ();
		
		foreach ( $row as $key => $value ) {
			$cat_info[$row['id']][$key] = stripslashes( $value );
		}
			
	}
	set_vars( "category", $cat_info );
	$db->free();
}

require_once ENGINE_DIR.'/modules/sitelogin.php';

if( !$is_logged OR !$user_group[$member_id['user_group']]['allow_admin'] ) { die ("error"); }

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
$buffer = "";

function parseJsonArray($jsonArray, $parentID = 0)
{
  $return = array();
  foreach ($jsonArray as $subArray) {
     $returnSubSubArray = array();
     if (isset($subArray['children'])) {
       $returnSubSubArray = parseJsonArray($subArray['children'], $subArray['id']);
     }
     $return[] = array('id' => $subArray['id'], 'parentid' => $parentID);
     $return = array_merge($return, $returnSubSubArray);
  }

  return $return;
}

@header("Content-type: text/html; charset=".$config['charset']);

if ($_REQUEST['action'] == "newsspam") {

	if ( !$user_group[$member_id['user_group']]['allow_all_edit']) die ("error");

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die ("error");
	
	}

	$id = intval( $_REQUEST['id'] );
	
	if( $id < 1 ) die( "error" );

	$row = $db->super_query( "SELECT id, autor, approve FROM " . PREFIX . "_post WHERE id = '{$id}'" );

	if ($row['id'])	{

		$author = $db->safesql($row['autor']);

		if( $row['approve'] ) die ("error");

		$row = $db->super_query( "SELECT user_id, user_group FROM " . USERPREFIX . "_users WHERE name = '{$author}'" );

		$user_id = intval($row['user_id']);

		if ($user_group[$row['user_group']]['allow_admin']) die ($lang['mark_spam_error']);

		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '87', '{$author}')" );

		$result = $db->query( "SELECT id FROM " . PREFIX . "_post WHERE autor='{$author}' AND approve='0'" );
			
		while ( $row = $db->get_array( $result ) ) {
			$id = intval( $row['id'] );
			$db->query( "UPDATE " . USERPREFIX . "_users SET news_num=news_num-1 WHERE user_id='{$user_id}'" );

			$db->query( "DELETE FROM " . PREFIX . "_post WHERE id='{$id}'" );
			$db->query( "DELETE FROM " . PREFIX . "_post_extras WHERE news_id='{$id}'" );
			$db->query( "DELETE FROM " . PREFIX . "_comments WHERE post_id='{$id}'" );
			$db->query( "DELETE FROM " . PREFIX . "_poll WHERE news_id = '{$id}'" );
			$db->query( "DELETE FROM " . PREFIX . "_poll_log WHERE news_id = '{$id}'" );
			$db->query( "DELETE FROM " . PREFIX . "_post_log WHERE news_id = '{$id}'" );
			$db->query( "DELETE FROM " . PREFIX . "_logs WHERE news_id = '{$id}'" );
			$db->query( "DELETE FROM " . PREFIX . "_tags WHERE news_id = '{$id}'" );
			$db->query( "DELETE FROM " . PREFIX . "_xfsearch WHERE news_id = '{$id}'" );
			
			$db->query( "SELECT onserver FROM " . PREFIX . "_files WHERE news_id = '{$id}'" );

			while ( $row = $db->get_row() ) {
				$url = explode( "/", $row['onserver'] );

				if( count( $url ) == 2 ) {
						
					$folder_prefix = $url[0] . "/";
					$file = $url[1];
					
				} else {
						
					$folder_prefix = "";
					$file = $url[0];
					
				}
				$file = totranslit( $file, false );
	
				if( trim($file) == ".htaccess") die("Hacking attempt!");

				@unlink( ROOT_DIR . "/uploads/files/" . $folder_prefix . $file );
			}

			$db->query( "DELETE FROM " . PREFIX . "_files WHERE news_id = '{$id}'" );

			$row = $db->super_query( "SELECT images  FROM " . PREFIX . "_images where news_id = '{$id}'" );
			
			$listimages = explode( "|||", $row['images'] );
			
			if( $row['images'] != "" ) foreach ( $listimages as $dataimages ) {
				$url_image = explode( "/", $dataimages );
				
				if( count( $url_image ) == 2 ) {
					
					$folder_prefix = $url_image[0] . "/";
					$dataimages = $url_image[1];
				
				} else {
					
					$folder_prefix = "";
					$dataimages = $url_image[0];
				
				}
				
				@unlink( ROOT_DIR . "/uploads/posts/" . $folder_prefix . $dataimages );
				@unlink( ROOT_DIR . "/uploads/posts/" . $folder_prefix . "thumbs/" . $dataimages );
				@unlink( ROOT_DIR . "/uploads/posts/" . $folder_prefix . "medium/" . $dataimages );
			}
			
			$db->query( "DELETE FROM " . PREFIX . "_images WHERE news_id = '{$id}'" );
			
		}

		$db->free( $result );
		$db->query( "UPDATE " . USERPREFIX . "_users SET restricted='3', restricted_days='0' WHERE user_id ='{$user_id}'" );
		clear_cache();
		$buffer = $lang['mark_spam_ok_2'];

	} else die ("error");

}


if ($_REQUEST['action'] == "clearpoll") {

	if ( !$user_group[$member_id['user_group']]['allow_all_edit']) die ("error");

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die ("error");
	
	}

	$id = intval( $_REQUEST['id'] );
	
	if( $id < 1 ) die( "error" );
	
	$db->query( "UPDATE  " . PREFIX . "_poll SET  votes='0', answer='' WHERE news_id = '{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_poll_log WHERE news_id='{$id}'" );
	
	$buffer = $lang['clear_poll_2'];

}

if ($_REQUEST['action'] == "commentspublic") {

	if ( !$user_group[$member_id['user_group']]['admin_comments']) die ("error");

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die ("error");
	
	}
	
	$c_id = intval( $_REQUEST['id'] );
	$post_id = intval( $_REQUEST['post_id'] );
	
	$db->query( "UPDATE " . PREFIX . "_comments SET approve='1' WHERE id='{$c_id}'" );
	$db->query( "UPDATE " . PREFIX . "_post SET comm_num=comm_num+1 WHERE id='{$post_id}'" );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '19', '')" );
	
	clear_cache();

	if ( $config['allow_subscribe'] ) {

		$row = $db->super_query( "SELECT autor, text FROM " . PREFIX . "_comments WHERE id = '{$c_id}'" );

		$name = $row['autor'];
		$body = $row['text'];
		
		include_once ENGINE_DIR . '/classes/mail.class.php';

		$row = $db->super_query( "SELECT id, short_story, title, date, alt_name, category FROM ".PREFIX."_post WHERE id = '{$post_id}'" );

		$row['date'] = strtotime( $row['date'] );
		$row['category'] = intval( $row['category'] );

		if( $config['allow_alt_url'] ) {
				
			if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
			
				if( $row['category'] and $config['seo_type'] == 2 ) {
					
					$full_link = $config['http_home_url'] . get_url( $row['category'] ) . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";
					
				} else {
					
					$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
					
				}
				
			} else {
				
				$full_link = $config['http_home_url'] . date( 'Y/m/d/', $row['date'] ) . $row['alt_name'] . ".html";
			}
			
		} else {
				
			$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];
			
		}
	
		$title = stripslashes($row['title']);
		
		$row = $db->super_query( "SELECT * FROM " . PREFIX . "_email WHERE name='comments' LIMIT 0,1" );
		$mail = new dle_mail( $config, $row['use_html'] );

		if (strpos($full_link, "//") === 0) $full_link = "http:".$full_link;
		elseif (strpos($full_link, "/") === 0) $full_link = "http://".$_SERVER['HTTP_HOST'].$full_link;

		$row['template'] = stripslashes( $row['template'] );
		$row['template'] = str_replace( "{%username%}", $name, $row['template'] );
		$row['template'] = str_replace( "{%date%}", langdate( "j F Y H:i", $_TIME, true ), $row['template'] );
		$row['template'] = str_replace( "{%link%}", $full_link, $row['template'] );
		$row['template'] = str_replace( "{%title%}", $title, $row['template'] );

		$body = str_replace( '\n', "", $body );
		$body = str_replace( '\r', "", $body );
			
		$body = stripslashes( stripslashes( $body ) );
		$body = str_replace( "<br />", "\n", $body );
		$body = strip_tags( $body );
			
		if( $row['use_html'] ) {
			$body = str_replace("\n", "<br />", $body );
		}
					
		$row['template'] = str_replace( "{%text%}", $body, $row['template'] );
		$row['template'] = str_replace( "{%ip%}", "--", $row['template'] );

		$db->query( "SELECT user_id, name, email, hash FROM " . PREFIX . "_subscribe WHERE news_id='{$post_id}'" );

		while($rec = $db->get_row())
		{
			if ($rec['user_id'] != $member_id['user_id'] ) {

				if (strpos($config['http_home_url'], "//") === 0) $slink = "http:".$config['http_home_url'];
				elseif (strpos($config['http_home_url'], "/") === 0) $slink = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];
				else $slink = $config['http_home_url'];
		
				$body = str_replace( "{%username_to%}", $rec['name'], $row['template'] );
				$body = str_replace( "{%unsubscribe%}", $slink . "index.php?do=unsubscribe&post_id=" . $post_id . "&user_id=" . $rec['user_id'] . "&hash=" . $rec['hash'], $body );
				$mail->send( $rec['email'], $lang['mail_comments'], $body );

			}

		}

		$db->free();
	}
	
	$buffer = 'ok';	
}

if ($_REQUEST['action'] == "commentsspam") {

	if ( !$user_group[$member_id['user_group']]['del_allc']) die ("error");

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die ("error");
	
	}

	$id = intval( $_REQUEST['id'] );
	
	if( $id < 1 ) die( "error" );

	$row = $db->super_query( "SELECT id, user_id, autor, email, ip, is_register FROM " . PREFIX . "_comments WHERE id = '{$id}'" );

	if ($row['id'])	{

		$user_id = intval($row['user_id']);
		$author = $db->safesql($row['autor']);
		$email = $db->safesql($row['email']);
		$is_register = $row['is_register'];
		$ip = $db->safesql($row['ip']);

		if ( $is_register ) {

			$row = $db->super_query( "SELECT user_group FROM " . USERPREFIX . "_users WHERE user_id = '{$user_id}'" );

			if ($user_group[$row['user_group']]['allow_admin']) die ($lang['mark_spam_error']);

			$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '87', '{$author}')" );

			$result = $db->query( "SELECT COUNT(*) as count, post_id FROM " . PREFIX . "_comments WHERE user_id='{$user_id}' AND is_register='1' AND approve='1' GROUP BY post_id" );
			
			while ( $row = $db->get_array( $result ) ) {
				
				$db->query( "UPDATE " . PREFIX . "_post SET comm_num=comm_num-{$row['count']} WHERE id='{$row['post_id']}'" );
			
			}
			$db->free( $result );

			$db->query( "UPDATE " . USERPREFIX . "_users SET comm_num='0', restricted='3', restricted_days='0' WHERE user_id ='{$user_id}'" );
			$db->query( "DELETE FROM " . PREFIX . "_comments WHERE user_id='{$user_id}' AND is_register='1'" );

		} else {

			$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '88', '{$author}')" );

			$result = $db->query( "SELECT COUNT(*) as count, post_id FROM " . PREFIX . "_comments WHERE ip='{$ip}' AND is_register='0' AND approve='1' GROUP BY post_id" );
			
			while ( $row = $db->get_array( $result ) ) {
				
				$db->query( "UPDATE " . PREFIX . "_post SET comm_num=comm_num-{$row['count']} WHERE id='{$row['post_id']}'" );
			
			}
			$db->free( $result );

			$db->query( "DELETE FROM " . PREFIX . "_comments WHERE ip='{$ip}' AND is_register='0'" );
			$db->query( "INSERT INTO " . USERPREFIX . "_banned (descr, date, days, ip) values ('{$lang['mark_spam_ok_1']}', '0', '0', '{$ip}')" );
			@unlink( ENGINE_DIR . '/cache/system/banned.php' );

		}

		clear_cache();

		if ( $email AND strlen($config['spam_api_key']) > 3 ) {
		
			include_once ENGINE_DIR . '/classes/stopspam.class.php';
			$sfs = new StopSpam($config['spam_api_key'], $config['sec_addnews']);
			$args = array('ip_addr' => $ip, 'username' => $author, 'email' => $email );
			$sfs->add( $args );
		
		}

		$buffer = $lang['mark_spam_ok'];		

	} else die ("error");
}

if ($_REQUEST['action'] == "clearcache") {

	if ( $member_id['user_group'] != 1 ) die ("error");

	$fdir = opendir( ENGINE_DIR . '/cache/system/' );
	while ( $file = readdir( $fdir ) ) {
		if( $file != '.' and $file != '..' and $file != '.htaccess' and $file != 'cron.php' ) {
			@unlink( ENGINE_DIR . '/cache/system/' . $file );
		
		}
	}
	
	clear_cache();

	$buffer = $lang['clear_cache'];

}


if ($_REQUEST['action'] == "clearsubscribe") {

	if ( $member_id['user_group'] != 1 ) die ("error");

	$db->query("TRUNCATE TABLE " . PREFIX . "_subscribe");

	$buffer = $lang['clear_subscribe'];

}

if ($_REQUEST['action'] == "sendnotice") {

	$row = $db->super_query( "SELECT id FROM " . PREFIX . "_notice WHERE user_id = '{$member_id['user_id']}'" );
	
	$notice = convert_unicode($_POST['notice'], $config['charset']);
	
	if( function_exists( "get_magic_quotes_gpc" ) && get_magic_quotes_gpc() ) $notice = stripslashes( $notice );
	
	$notice = $db->safesql( $notice );
	
	if( $row['id'] ) {
		
		$db->query( "UPDATE " . PREFIX . "_notice SET notice='{$notice}' WHERE user_id = '{$member_id['user_id']}'" );
	
	} else {
		
		$db->query( "INSERT INTO " . PREFIX . "_notice (user_id, notice) values ('{$member_id['user_id']}', '{$notice}')" );
	
	}

	$buffer = "<font color=\"green\">".$lang['saved']."</font>";

}

if ($_REQUEST['action'] == "deletemodules") {

	if ( $member_id['user_group'] != 1 ) die ("error");

	$id = intval($_REQUEST['id']);

	if ( $id ) {
		$db->query( "DELETE FROM " . PREFIX . "_admin_sections WHERE id = '{$id}'" );
	
		$buffer = 'ok';
	}

}

if ($_REQUEST['action'] == "catsort") {

	if( !$user_group[$member_id['user_group']]['admin_categories'] ) die ("error");
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die ("error");
	
	}

	$_POST['list'] = json_decode(stripslashes($_POST['list']), true);

	if ( !is_array($_POST['list']) ) die ("error");
	
	$_POST['list'] = parseJsonArray($_POST['list']);
	
	$i= 0;

	foreach ( $_POST['list'] as $value ) {
		$i++;

		$id = intval($value['id']);
		$parentid = intval($value['parentid']);
		
		if ( $id ) {

			$db->query( "UPDATE " . PREFIX . "_category SET parentid='{$parentid}', posi='{$i}' WHERE id = '{$id}'" );

		}
	}

	@unlink( ENGINE_DIR . '/cache/system/category.php' );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '11', '')" );

	$buffer = 'ok';

}


if ($_REQUEST['action'] == "xfsort") {

	if( !$user_group[$member_id['user_group']]['admin_xfields'] ) die ("error");

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die ("error");
	
	}

	$_POST['list'] = json_decode(stripslashes($_POST['list']), true);

	if ( !is_array($_POST['list']) ) die ("error");
	
	$_POST['list'] = parseJsonArray($_POST['list']);

	function xfieldssave($data) {
	
	    $data = array_values($data);
		$filecontents = "";
		
	    foreach ($data as $index => $value) {
	      $value = array_values($value);
	      foreach ($value as $index2 => $value2) {
	        $value2 = stripslashes($value2);
	        $value2 = str_replace("|", "&#124;", $value2);
	        $value2 = str_replace("\r\n", "__NEWL__", $value2);
	        $filecontents .= $value2 . ($index2 < count($value) - 1 ? "|" : "");
	      }
	      $filecontents .= ($index < count($data) - 1 ? "\r\n" : "");
	    }
		
	    $filehandle = fopen(ENGINE_DIR.'/data/xfields.txt', "w+");
	
	    if (!$filehandle) die ("error");
		
		$find = array ('/data:/i', '/about:/i', '/vbscript:/i', '/onclick/i', '/onload/i', '/onunload/i', '/onabort/i', '/onerror/i', '/onblur/i', '/onchange/i', '/onfocus/i', '/onreset/i', '/onsubmit/i', '/ondblclick/i', '/onkeydown/i', '/onkeypress/i', '/onkeyup/i', '/onmousedown/i', '/onmouseup/i', '/onmouseover/i', '/onmouseout/i', '/onselect/i', '/javascript/i', '/onmouseenter/i', '/onwheel/i', '/onshow/i');
		$replace = array ("d&#097;ta:", "&#097;bout:", "vbscript<b></b>:", "&#111;nclick", "&#111;nload", "&#111;nunload", "&#111;nabort", "&#111;nerror", "&#111;nblur", "&#111;nchange", "&#111;nfocus", "&#111;nreset", "&#111;nsubmit", "&#111;ndblclick", "&#111;nkeydown", "&#111;nkeypress", "&#111;nkeyup", "&#111;nmousedown", "&#111;nmouseup", "&#111;nmouseover", "&#111;nmouseout", "&#111;nselect", "j&#097;vascript", '&#111;nmouseenter', '&#111;nwheel', '&#111;nshow');
			
		$filecontents = preg_replace( $find, $replace, $filecontents );
		$filecontents = preg_replace( "#<iframe#i", "&lt;iframe", $filecontents );
		$filecontents = preg_replace( "#<script#i", "&lt;script", $filecontents );
		$filecontents = str_replace( "<?", "&lt;?", $filecontents );
		$filecontents = str_replace( "?>", "?&gt;", $filecontents );
		$filecontents = str_replace( "$", "&#036;", $filecontents );
		
	    fwrite($filehandle, $filecontents);
	    fclose($filehandle);
		
	
	}


	$xfields = xfieldsload();
	$temp_array = array();

	foreach ( $_POST['list'] as $value ) {

		$id = intval($value['id']);
		$temp_array[] = $xfields[$id];		

	}

	$xfields = $temp_array;

	xfieldssave($xfields);

	$buffer = 'ok';

}

if ($_REQUEST['action'] == "userxfsort") {

	if( !$user_group[$member_id['user_group']]['admin_userfields'] ) die ("error");

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die ("error");
	
	}

	$_POST['list'] = json_decode(stripslashes($_POST['list']), true);

	if ( !is_array($_POST['list']) ) die ("error");
	
	$_POST['list'] = parseJsonArray($_POST['list']);

	function profileload() {

	  $path = ENGINE_DIR.'/data/xprofile.txt';
	  $filecontents = file($path);
	
	    if (!is_array($filecontents)) die ("error");
	  
	    foreach ($filecontents as $name => $value) {
	      $filecontents[$name] = explode("|", trim($value));
	      foreach ($filecontents[$name] as $name2 => $value2) {
	        $value2 = str_replace("&#124;", "|", $value2); 
	        $value2 = str_replace("__NEWL__", "\r\n", $value2);
	        $filecontents[$name][$name2] = $value2;
	      }
	    }
	    return $filecontents;
	}


	function profilesave($data) {
	
	    $data = array_values($data);
		$filecontents = "";
	
	    foreach ($data as $index => $value) {
	      $value = array_values($value);
	      foreach ($value as $index2 => $value2) {
	        $value2 = stripslashes($value2);
	        $value2 = str_replace("|", "&#124;", $value2);
	        $value2 = str_replace("\r\n", "__NEWL__", $value2);
	        $filecontents .= $value2 . ($index2 < count($value) - 1 ? "|" : "");
	      }
	      $filecontents .= ($index < count($data) - 1 ? "\r\n" : "");
	    }
	  
	    $filehandle = fopen(ENGINE_DIR.'/data/xprofile.txt', "w+");
	    if (!$filehandle) die ("error");
	
		$find = array ('/data:/i', '/about:/i', '/vbscript:/i', '/onclick/i', '/onload/i', '/onunload/i', '/onabort/i', '/onerror/i', '/onblur/i', '/onchange/i', '/onfocus/i', '/onreset/i', '/onsubmit/i', '/ondblclick/i', '/onkeydown/i', '/onkeypress/i', '/onkeyup/i', '/onmousedown/i', '/onmouseup/i', '/onmouseover/i', '/onmouseout/i', '/onselect/i', '/javascript/i', '/onmouseenter/i', '/onwheel/i', '/onshow/i');
		$replace = array ("d&#097;ta:", "&#097;bout:", "vbscript<b></b>:", "&#111;nclick", "&#111;nload", "&#111;nunload", "&#111;nabort", "&#111;nerror", "&#111;nblur", "&#111;nchange", "&#111;nfocus", "&#111;nreset", "&#111;nsubmit", "&#111;ndblclick", "&#111;nkeydown", "&#111;nkeypress", "&#111;nkeyup", "&#111;nmousedown", "&#111;nmouseup", "&#111;nmouseover", "&#111;nmouseout", "&#111;nselect", "j&#097;vascript", '&#111;nmouseenter', '&#111;nwheel', '&#111;nshow');
		
		$filecontents = preg_replace( $find, $replace, $filecontents );
		$filecontents = preg_replace( "#<iframe#i", "&lt;iframe", $filecontents );
		$filecontents = preg_replace( "#<script#i", "&lt;script", $filecontents );
		$filecontents = str_replace( "<?", "&lt;?", $filecontents );
		$filecontents = str_replace( "?>", "?&gt;", $filecontents );
		$filecontents = str_replace( "$", "&#036;", $filecontents );
	
	    fwrite($filehandle, $filecontents);
	    fclose($filehandle);
	}

	$xfields = profileload();

	$temp_array = array();

	foreach ( $_POST['list'] as $value ) {

		$id = intval($value['id']);
		$temp_array[] = $xfields[$id];		

	}

	$xfields = $temp_array;
	profilesave($xfields);

	$buffer = 'ok';
}

echo $buffer;

?>