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
 Файл: feedback.php
-----------------------------------------------------
 Назначение: Отправка E-mail через обратную связь
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

	$config['http_home_url'] = explode("engine/ajax/feedback.php", $_SERVER['PHP_SELF']);
	$config['http_home_url'] = reset($config['http_home_url']);
	$config['http_home_url'] = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];

}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';
require_once ENGINE_DIR . '/classes/templates.class.php';

dle_session();

$_REQUEST['skin'] = trim(totranslit($_REQUEST['skin'], false, false));
$dle_module = "feedback";

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

if( $config["lang_" . $_REQUEST['skin']] ) {

	if ( file_exists( ROOT_DIR . '/language/' . $config["lang_" . $_REQUEST['skin']] . '/website.lng' ) ) {
		@include_once (ROOT_DIR . '/language/' . $config["lang_" . $_REQUEST['skin']] . '/website.lng');
	} else die("Language file not found");

} else {
	
	@include_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';

}
$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];

require_once ENGINE_DIR . '/modules/sitelogin.php';

if (!$is_logged) $member_id['user_group'] = 5;

$tpl = new dle_template( );
$tpl->dir = ROOT_DIR . '/templates/' . $_REQUEST['skin'];
define( 'TEMPLATE_DIR', $tpl->dir );

@header( "Content-type: text/html; charset=" . $config['charset'] );

$stop = "";

if( $is_logged ) {

	$name = $db->safesql($member_id['name']);
	$email = $db->safesql($member_id['email']);

} else {

	$_POST['name']  = convert_unicode( $_POST['name'], $config['charset']  );
	$_POST['email'] = convert_unicode( $_POST['email'], $config['charset'] );
			
	$name = $lang['feedback_not_reg']." ".strip_tags( stripslashes($_POST['name']) );

	$not_allow_symbol = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", "/", "¬", "#", ";", ":", "~", "[", "]", "{", "}", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"', "'" );
	$email = $db->safesql( trim( str_replace( $not_allow_symbol, '', strip_tags( stripslashes( $_POST['email'] ) ) ) ) );


}

$subject = trim(strip_tags( stripslashes( convert_unicode( $_POST['subject'], $config['charset']  ) ) ) );
$message = trim(stripslashes( convert_unicode($_POST['message'], $config['charset'] ) ) );
$recip = intval( $_POST['recip'] );
$_POST['question_answer'] = convert_unicode( $_POST['question_answer'], $config['charset'] );

if( !$user_group[$member_id['user_group']]['allow_feed'] )	{

	$recipient = $db->super_query( "SELECT name, email, fullname, user_group FROM " . USERPREFIX . "_users WHERE user_id='" . $recip . "' AND user_group = '1'" );

} else {

	$recipient = $db->super_query( "SELECT name, email, fullname, user_group FROM " . USERPREFIX . "_users WHERE user_id='" . $recip . "' AND allow_mail = '1'" );

}

if ( $config['sec_addnews'] AND $recipient['user_group'] != 1 ) {

	$row = $db->super_query( "SELECT * FROM " . PREFIX . "_spam_log WHERE ip = '{$_IP}'" );

	if ( !$row['id'] OR !$row['email'] ) {

		include_once ENGINE_DIR . '/classes/stopspam.class.php';
		$sfs = new StopSpam($config['spam_api_key'], $config['sec_addnews']);
		$args = array('ip' => $_IP, 'email' => $email);

		if ($sfs->is_spammer( $args )) {

			if ( !$row['id'] ) {
				$db->query( "INSERT INTO " . PREFIX . "_spam_log (ip, is_spammer, email, date) VALUES ('{$_IP}','1', '{$email}', '{$_TIME}')" );
			} else {
				$db->query( "UPDATE " . PREFIX . "_spam_log SET is_spammer='1', email='{$email}' WHERE id='{$row['id']}'" );
			}

			$stop .= $lang['reg_err_34']." ";

		} else {
			if ( !$row['id'] ) {
				$db->query( "INSERT INTO " . PREFIX . "_spam_log (ip, is_spammer, email, date) VALUES ('{$_IP}','0', '{$email}', '{$_TIME}')" );
			} else {
				$db->query( "UPDATE " . PREFIX . "_spam_log SET email='{$email}' WHERE id='{$row['id']}'" );
			}
		}
	
	} else {

		if ($row['is_spammer']) {

			$stop .= $lang['reg_err_34']." ";
		
		}

	}

}

if( !$recipient['fullname'] ) $recipient['fullname'] = $recipient['name'];

if (!$recipient['name']) $stop .= $lang['feed_err_8'];

if( $user_group[$member_id['user_group']]['max_mail_day'] ) {
		
	$this_time = time() - 86400;
	$db->query( "DELETE FROM " . PREFIX . "_sendlog WHERE date < '$this_time' AND flag='2'" );

	if ( !$is_logged ) $check_user = $_IP; else $check_user = $db->safesql($member_id['name']);
	
	$row = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_sendlog WHERE user = '{$check_user}' AND flag='2'");
		
	if( $row['count'] >=  $user_group[$member_id['user_group']]['max_mail_day'] ) {
		
		$stop .= str_replace('{max}', $user_group[$member_id['user_group']]['max_mail_day'], $lang['feed_err_9']);
	}
}

if( empty( $name ) OR dle_strlen($name, $config['charset']) > 100 ) {
	$stop .= $lang['feed_err_1'];
}
		
if( empty($email) OR dle_strlen($email, $config['charset']) > 50 OR @count(explode("@", $email)) != 2) {
	$stop .= $lang['feed_err_2'];
}

if( empty($subject) OR dle_strlen($subject, $config['charset']) > 200 ) {
	$stop .= $lang['feed_err_4'];
}

if( empty( $message ) OR dle_strlen($message, $config['charset']) > 20000 ) {
	$stop .= $lang['feed_err_5'];
}

if( $user_group[$member_id['user_group']]['captcha_feedback'] ) {
	
	if ($config['allow_recaptcha']) {
	
		if ( $_POST['g-recaptcha-response'] ) {
	
			require_once ENGINE_DIR . '/classes/recaptcha.php';			
			$reCaptcha = new ReCaptcha($config['recaptcha_private_key']);

			$resp = $reCaptcha->verifyResponse(get_ip(), $_POST['g-recaptcha-response'] );
				
			if ($resp != null && $resp->success) {
	
				$_POST['sec_code'] = 1;
				$_SESSION['sec_code_session'] = 1;
	
			} else $_SESSION['sec_code_session'] = false;
			
		} else $_SESSION['sec_code_session'] = false;
	
	}
			
	if( $_POST['sec_code'] != $_SESSION['sec_code_session'] OR !$_SESSION['sec_code_session'] ) {
		$stop .= $lang['reg_err_19'];
	}
	
	$_SESSION['sec_code_session'] = false;
}

if( $user_group[$member_id['user_group']]['feedback_question'] ) {
	
	if ( intval($_SESSION['question']) ) {
	
		$answer = $db->super_query("SELECT id, answer FROM " . PREFIX . "_question WHERE id='".intval($_SESSION['question'])."'");

		$answers = explode( "\n", $answer['answer'] );
	
		$pass_answer = false;
	
		if( function_exists('mb_strtolower') ) {
			$question_answer = trim(mb_strtolower($_POST['question_answer'], $config['charset']));
		} else {
			$question_answer = trim(strtolower($_POST['question_answer']));
		}
	
		if( count($answers) AND $question_answer ) {
			foreach( $answers as $answer ){

				if( function_exists('mb_strtolower') ) {
					$answer = trim(mb_strtolower($answer, $config['charset']));
				} else {
					$answer = trim(strtolower($answer));
				}

				if( $answer AND $answer == $question_answer ) {
					$pass_answer	= true;
					break;
				}
			}
		}
	
		if( !$pass_answer ) $stop .= "<li>".$lang['reg_err_24']."</li>";
	
	} else $stop .= "<li>".$lang['reg_err_24']."</li>";
	
}

if( $stop ) {

	$stop = "<ul>{$stop}</ul>";

	$stop = str_replace ('"', '\"', $stop);
			
	echo "{\"status\": \"error\",\"text\": \"{$stop}\"}";

	die();
		
} else {

	include_once ENGINE_DIR . '/classes/mail.class.php';
				
	$row = $db->super_query( "SELECT * FROM " . PREFIX . "_email WHERE name='feed_mail' LIMIT 0,1" );
	$mail = new dle_mail( $config,  $row['use_html']);

	if( $row['use_html'] ) {
		$message = htmlspecialchars($message, ENT_QUOTES, $config['charset']);
		$message = preg_replace( array ("'\r'", "'\n'"), array ("", "<br />"), $message );
	}
	
	$row['template'] = stripslashes( $row['template'] );
	$row['template'] = str_replace( "{%username_to%}", $recipient['fullname'], $row['template'] );
	$row['template'] = str_replace( "{%username_from%}", $name, $row['template'] );
	$row['template'] = str_replace( "{%text%}", $message, $row['template'] );
	$row['template'] = str_replace( "{%ip%}", get_ip(), $row['template'] );
	$row['template'] = str_replace( "{%email%}", $email, $row['template'] );
	$row['template'] = str_replace( "{%group%}", $user_group[$member_id['user_group']]['group_name'], $row['template'] );

	$mail->from = $email;
				
	$mail->send( $recipient['email'], $subject, $row['template'] );

	if( $mail->send_error ) {

		echo "{\"status\": \"error\",\"text\": \"{$mail->smtp_msg}\"}";

	} else {

		if( $user_group[$member_id['user_group']]['max_mail_day'] ) {
			$_TIME = time (); 
			if ( !$is_logged ) $check_user = $_IP; else $check_user = $db->safesql($member_id['name']);		
			$db->query( "INSERT INTO " . PREFIX . "_sendlog (user, date, flag) values ('{$check_user}', '{$_TIME}', '2')" );
		}

		msgbox( $lang['feed_ok_1'], "{$lang['feed_ok_2']} <a href=\"{$config['http_home_url']}\">{$lang['feed_ok_4']}</a>" );

		$tpl->result['info'] = str_replace( '{THEME}', $config['http_home_url'] . 'templates/' . $_REQUEST['skin'], $tpl->result['info'] );
		$tpl->result['info'] = str_replace ('"', '\"', $tpl->result['info']);
		$tpl->result['info'] = str_replace( "{", '', $tpl->result['info'] );
		$tpl->result['info'] = str_replace( "}", '', $tpl->result['info'] );
		$tpl->result['info'] = str_replace( "\r", '', $tpl->result['info'] );
		$tpl->result['info'] = str_replace( "\n", '', $tpl->result['info'] );
		$tpl->result['info'] = str_replace( "\t", '', $tpl->result['info'] );

		echo "{\"status\": \"ok\",\"text\": \"{$tpl->result['info']}\"}";

	}

}

?>