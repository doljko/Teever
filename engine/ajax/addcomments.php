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
 Файл: addcomments.php
-----------------------------------------------------
 Назначение: AJAX для добавления комментариев
=====================================================
*/

@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

define( 'DATALIFEENGINE', true );
define( 'ROOT_DIR', substr( dirname(  __FILE__ ), 0, -12 ) );
define( 'ENGINE_DIR', ROOT_DIR . '/engine' );

include ENGINE_DIR . '/data/config.php';

date_default_timezone_set ( $config['date_adjust'] );

if( $config['http_home_url'] == "" ) {
	
	$config['http_home_url'] = explode( "engine/ajax/addcomments.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset( $config['http_home_url'] );
	$config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';
require_once ENGINE_DIR . '/classes/templates.class.php';

dle_session();

$_REQUEST['skin'] = trim(totranslit($_REQUEST['skin'], false, false));

if( $_REQUEST['skin'] == "" OR !@is_dir( ROOT_DIR . '/templates/' . $_REQUEST['skin'] ) ) {
	die( "Hacking attempt!" );
}

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
//####################################################################################################################
//                    Определение категорий и их параметры
//####################################################################################################################
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
//####################################################################################################################
//                    Определение забаненных пользователей и IP
//####################################################################################################################
$banned_info = get_vars ( "banned" );

if (! is_array ( $banned_info )) {
	$banned_info = array ();
	
	$db->query ( "SELECT * FROM " . USERPREFIX . "_banned" );
	while ( $row = $db->get_row () ) {
		
		if ($row['users_id']) {
			
			$banned_info['users_id'][$row['users_id']] = array (
																'users_id' => $row['users_id'], 
																'descr' => stripslashes ( $row['descr'] ), 
																'date' => $row['date'] );
		
		} else {
			
			if (count ( explode ( ".", $row['ip'] ) ) == 4)
				$banned_info['ip'][$row['ip']] = array (
														'ip' => $row['ip'], 
														'descr' => stripslashes ( $row['descr'] ), 
														'date' => $row['date']
														);
			elseif (strpos ( $row['ip'], "@" ) !== false)
				$banned_info['email'][$row['ip']] = array (
															'email' => $row['ip'], 
															'descr' => stripslashes ( $row['descr'] ), 
															'date' => $row['date'] );
			else $banned_info['name'][$row['ip']] = array (
															'name' => $row['ip'], 
															'descr' => stripslashes ( $row['descr'] ), 
															'date' => $row['date'] );
		
		}
	
	}
	set_vars ( "banned", $banned_info );
	$db->free ();
}

if( $config["lang_" . $_REQUEST['skin']] ) {

	if ( file_exists( ROOT_DIR . '/language/' . $config["lang_" . $_REQUEST['skin']] . '/website.lng' ) ) {
		@include_once (ROOT_DIR . '/language/' . $config["lang_" . $_REQUEST['skin']] . '/website.lng');
	} else die("Language file not found");

} else {
	
	@include_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';

}
$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];
$is_logged = false;
$member_id = array ();

if ($config['allow_registration']) {
	require_once ENGINE_DIR . '/modules/sitelogin.php';
}

if( ! $is_logged ) {
	$member_id['user_group'] = 5;
}

if ( check_ip ( $banned_info['ip'] ) ) die("error");
if ($is_logged AND $member_id['banned'] == "yes") die("error");

$tpl = new dle_template( );
$tpl->dir = ROOT_DIR . '/templates/' . $_REQUEST['skin'];
define( 'TEMPLATE_DIR', $tpl->dir );

$ajax_adds = true;

$_POST['name'] = convert_unicode( $_POST['name'], $config['charset']  );
$_POST['mail'] = convert_unicode( $_POST['mail'], $config['charset'] );
$_POST['comments'] = convert_unicode( $_POST['comments'], $config['charset'] );
$_POST['question_answer'] = convert_unicode( $_POST['question_answer'], $config['charset'] );

require_once ENGINE_DIR . '/modules/addcomments.php';

if( $CN_HALT != TRUE ) {

	include_once ENGINE_DIR . '/classes/comments.class.php';
	$comments = new DLE_Comments( $db, 1, 1 );
	$comments->intern_count = -1;
	if($parent) $comments->indent = $indent+1;
	
	$comments->query = "SELECT " . PREFIX . "_comments.id, post_id, " . PREFIX . "_comments.user_id, date, autor as gast_name, " . PREFIX . "_comments.email as gast_email, text, ip, is_register, " . PREFIX . "_comments.rating, " . PREFIX . "_comments.vote_num, name, " . USERPREFIX . "_users.email, news_num, comm_num, user_group, lastdate, reg_date, signature, foto, fullname, land, xfields FROM " . PREFIX . "_comments LEFT JOIN " . USERPREFIX . "_users ON " . PREFIX . "_comments.user_id=" . USERPREFIX . "_users.user_id WHERE " . PREFIX . "_comments.id = '{$added_comments_id}'";
	$comments->build_comments('comments.tpl', 'ajax' );

}

if( $_POST['editor_mode'] == "wysiwyg" ) {

	if( $config['allow_comments_wysiwyg'] == "1") $clear_value = "oUtil.obj.focus();oUtil.obj.loadHTML('');";
	else $clear_value = "tinyMCE.activeEditor.setContent('');";

} else {
	
	$clear_value = "form.comments.value = '';";

}

if( $user_group[$member_id['user_group']]['comments_question'] ) {
	$qs = $db->super_query("SELECT id, question FROM " . PREFIX . "_question ORDER BY RAND() LIMIT 1");
	$qs['question'] = htmlspecialchars( stripslashes( $qs['question'] ), ENT_QUOTES, $config['charset'] );
	$_SESSION['question'] = $qs['id'];
}

if( $CN_HALT ) {
	
	$stop = implode( '<br /><br />', $stop );

	if($parent) {

		$replyclear="";
		
		if($user_group[$member_id['user_group']]['comments_question']) {
			
				$replyclear .= <<<HTML
	
		jQuery('#dle-question{$parent}').text('{$qs['question']}');
		jQuery('#question_answer{$parent}').val('');

HTML;
	
		}
	
		if( $user_group[$member_id['user_group']]['captcha'] AND $config['allow_recaptcha'] ) {

				$replyclear .= <<<HTML

		grecaptcha.reset(recaptcha_widget);
		
HTML;
			
		}

		if( $user_group[$member_id['user_group']]['captcha'] AND !$config['allow_recaptcha'] ) {

				$replyclear .= <<<HTML
	
		reload{$parent} ();
		
HTML;
			
		}
		
	} else  {

		$replyclear = <<<HTML
	
	if ( typeof grecaptcha != "undefined" ) {
	   grecaptcha.reset();
    }

	if ( form.question_answer ) {

	   form.question_answer.value ='';
       jQuery('#dle-question').text('{$qs['question']}');
    }

	if ( document.getElementById('dle-captcha') ) {
		document.getElementById('dle-captcha').innerHTML = '<img src="' + dle_root + 'engine/modules/antibot/antibot.php?rand=' + timeval + '" width="160" height="80" alt="">';
	}
		
HTML;
		
	} 
	
	$tpl->result['content'] = "<script type=\"text/javascript\">\nvar form = document.getElementById('dle-comments-form');\n";
	
	if( ! $where_approve ) {
		$tpl->result['content'] .= "\n{$clear_value}\n";
		
		if($parent) $tpl->result['content'] .= "\n jQuery('#dlereplypopup').remove(); jQuery('#dlefastreplycomments').remove(); \n";
	}
	
	$tpl->result['content'] .= "\n DLEalert('" . $stop . "', '". $lang['add_comm']."');\n var timeval = new Date().getTime();\n

	{$replyclear}\n </script>";

} else {

	if ( $config['tree_comments'] ) {
		
		if (!$parent) $parent = '';
		
		$tpl->result['content'] = "<div id=\"blind-animation{$parent}\" style=\"display:none\"><ol class=\"comments-tree-list\"><li id=\"comments-tree-item-{$added_comments_id}\" class=\"comments-tree-item\" >".$tpl->result['content']."</li></ol><div>";	

	} else {
		$tpl->result['content'] = "<div id=\"blind-animation\" style=\"display:none\">".$tpl->result['content']."<div>";
	}
	
	$tpl->result['content'] .= <<<HTML
<script type="text/javascript">
	var timeval = new Date().getTime();

	var form = document.getElementById('dle-comments-form');

	if ( form.question_answer ) {

	   form.question_answer.value ='';
       jQuery('#dle-question').text('{$qs['question']}');

    }

	{$clear_value}
</script>
HTML;

}

$tpl->result['content'] = str_replace( '{THEME}', $config['http_home_url'] . 'templates/' . $_REQUEST['skin'], $tpl->result['content'] );

@header( "Content-type: text/html; charset=" . $config['charset'] );
echo $tpl->result['content'];
?>