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
 Файл: lostpassword.php
-----------------------------------------------------
 Назначение: Восстановление забытого пароля
=====================================================
*/
if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

function GetRandInt($max){

	if(function_exists('openssl_random_pseudo_bytes') && (version_compare(PHP_VERSION, '5.3.4') >= 0 || strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')) {
	     do{
	         $result = floor($max*(hexdec(bin2hex(openssl_random_pseudo_bytes(4)))/0xffffffff));
	     }while($result == $max);
	} else {

		$result = mt_rand( 0, $max );
	}

    return $result;
}

if( $is_logged ) {
	
	msgbox( $lang['all_info'], $lang['user_logged'] );

} elseif( intval( $_GET['douser'] ) AND $_GET['lostid'] ) {
	
	$douser = intval( $_GET['douser'] );
	$lostid = $_GET['lostid'];
	
	$row = $db->super_query( "SELECT lostid FROM " . USERPREFIX . "_lostdb WHERE lostname='$douser'" );
	
	if( $row['lostid'] != "" AND $lostid != "" AND $row['lostid'] == $lostid ) {

		$row = $db->super_query( "SELECT email, name FROM " . USERPREFIX . "_users WHERE user_id='$douser' LIMIT 0,1" );
			
		$username = $row['name'];
		$lostmail = $row['email'];
		
		if ($_GET['action'] == "ip") {

			$db->query( "UPDATE " . USERPREFIX . "_users SET allowed_ip = '' WHERE user_id='$douser'" );
			$db->query( "DELETE FROM " . USERPREFIX . "_lostdb WHERE lostname='$douser'" );

			$lang['lost_clear_ip_1'] = str_replace("{username}", $username, $lang['lost_clear_ip_1']);
			
			msgbox( $lang['lost_clear_ip'], $lang['lost_clear_ip_1'] );


		} else {

			if(function_exists('openssl_random_pseudo_bytes') && (version_compare(PHP_VERSION, '5.3.4') >= 0 || strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')) {
			
				$stronghash = openssl_random_pseudo_bytes(15);
			
			} else $stronghash = md5(uniqid( mt_rand(), TRUE ));

			$salt = str_shuffle("abchefghjkmnpqrstuvwxyz0123456789".sha1($stronghash. microtime()));

			$new_pass = "";

			for($i = 0; $i < 11; $i ++) {
				$new_pass .= $salt{GetRandInt(72)};
			}
			
			$db->query( "UPDATE " . USERPREFIX . "_users SET password='" . md5( md5( $new_pass ) ) . "', allowed_ip = '' WHERE user_id='{$douser}'" );
			$db->query( "UPDATE " . USERPREFIX . "_social_login SET password='" . md5( $new_pass ) . "' WHERE uid='{$douser}'" );
			$db->query( "DELETE FROM " . USERPREFIX . "_lostdb WHERE lostname='$douser'" );

			include_once ENGINE_DIR . '/classes/mail.class.php';
			$mail = new dle_mail( $config );

			if ($config['auth_metod']) $username = $lostmail;

			$message = $lang['lost_npass']."\n\n{$lang['lost_login']} {$username}\n{$lang['lost_pass']} {$new_pass}\n\n{$lang['lost_info']}\n\n{$lang['lost_mfg']} ".$config['http_home_url'];
			$mail->send( $lostmail, $lang['lost_subj'], $message );
			
			msgbox( $lang['lost_gen'], $lang['lost_send']."<b>{$lostmail}</b>. ".$lang['lost_info'] );
		}	

	} else {

		$db->query( "DELETE FROM " . USERPREFIX . "_lostdb WHERE lostname='$douser'" );
		msgbox( $lang['all_err_1'], $lang['lost_err'] );

	}
	

} elseif( isset( $_POST['submit_lost'] ) ) {

		if ($config['allow_recaptcha']) {

			require_once ENGINE_DIR . '/classes/recaptcha.php';

			if ( $_POST['g-recaptcha-response'] ) {
			
				$reCaptcha = new ReCaptcha($config['recaptcha_private_key']);
		
				$resp = $reCaptcha->verifyResponse(get_ip(), $_POST['g-recaptcha-response'] );
			
			    if ($resp != null && $resp->success) {

					$_POST['sec_code'] = 1;
					$_SESSION['sec_code_session'] = 1;

			    } else $_SESSION['sec_code_session'] = false;
					
			} else $_SESSION['sec_code_session'] = false;

		}

	if( preg_match( "/[\||\'|\<|\>|\[|\]|\"|\!|\?|\$|\/|\\\|\&\~\*\{\+]/", $_POST['lostname'] ) OR !trim($_POST['lostname'])) {

		msgbox( $lang['all_err_1'], "<ul>".$lang['reg_err_4'] . "</ul><br /><a href=\"javascript:history.go(-1)\">$lang[all_prev]</a>" );
	
	} elseif( $_POST['sec_code'] != $_SESSION['sec_code_session'] OR !$_SESSION['sec_code_session'] ) {
		
		msgbox( $lang['all_err_1'], "<ul>".$lang['reg_err_19'] . "</ul><br /><a href=\"javascript:history.go(-1)\">$lang[all_prev]</a>" );
	
	} else {
		
		$_SESSION['sec_code_session'] = false;
		$lostname = $db->safesql( $_POST['lostname'] );
		
		if( @count(explode("@", $lostname)) == 2 ) $search = "email = '" . $lostname . "'";
		else $search = "name = '" . $lostname . "'";
		
		$row = $db->super_query( "SELECT email, password, name, user_id, user_group FROM " . USERPREFIX . "_users WHERE {$search}" );
		
		if( $row['user_id'] AND $user_group[$row['user_group']]['allow_lostpassword']) {
			
			include_once ENGINE_DIR . '/classes/mail.class.php';
			
			$lostmail = $row['email'];
			$userid = $row['user_id'];
			$lostname = $row['name'];
			$lostpass = $row['password'];
			
			$row = $db->super_query( "SELECT * FROM " . PREFIX . "_email where name='lost_mail' LIMIT 0,1" );
			$mail = new dle_mail( $config, $row['use_html'] );
			
			$row['template'] = stripslashes( $row['template'] );

			if(function_exists('openssl_random_pseudo_bytes') && (version_compare(PHP_VERSION, '5.3.4') >= 0 || strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')) {
			
				$stronghash = openssl_random_pseudo_bytes(15);
			
			} else $stronghash = md5(uniqid( mt_rand(), TRUE ));
		
			$salt = str_shuffle("abchefghjkmnpqrstuvwxyz0123456789".sha1($lostpass.$stronghash. microtime()) );
			$rand_lost = '';
			
			for($i = 0; $i < 15; $i ++) {
				$rand_lost .= $salt{GetRandInt(72)};
			}
			
			$lostid = sha1( md5( $lostname . $lostmail ) . microtime() . $rand_lost );

			if ( strlen($lostid) != 40 ) die ("US Secure Hash Algorithm 1 (SHA1) disabled by Hosting");
			
			if (strpos($config['http_home_url'], "//") === 0) $slink = "http:".$config['http_home_url'];
			elseif (strpos($config['http_home_url'], "/") === 0) $slink = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];
			else $slink = $config['http_home_url'];
					
			$lostlink = $slink . "index.php?do=lostpassword&action=password&douser=" . $userid . "&lostid=" . $lostid;
			$iplink = $slink . "index.php?do=lostpassword&action=ip&douser=" . $userid . "&lostid=" . $lostid;

			if( $row['use_html'] ) {
				$link = $lang['lost_password']."<br />".$lostlink."<br /><br />".$lang['lost_ip']."<br />".$iplink;
			} else {
				$link = $lang['lost_password']."\n".$lostlink."\n\n".$lang['lost_ip']."\n".$iplink;
			}
			
			$db->query( "DELETE FROM " . USERPREFIX . "_lostdb WHERE lostname='$userid'" );
			
			$db->query( "INSERT INTO " . USERPREFIX . "_lostdb (lostname, lostid) values ('$userid', '$lostid')" );
			
			$row['template'] = str_replace( "{%username%}", $lostname, $row['template'] );
			$row['template'] = str_replace( "{%lostlink%}", $link, $row['template'] );
			$row['template'] = str_replace( "{%ip%}", get_ip(), $row['template'] );
			
			$mail->send( $lostmail, $lang['lost_subj'], $row['template'] );
			
			if( $mail->send_error ) msgbox( $lang['all_info'], $mail->smtp_msg );
			else msgbox( $lang['lost_ms'], $lang['lost_ms_1'] );
		
		} elseif( !$row['user_id'] ) {

			msgbox( $lang['all_err_1'], $lang['lost_err_1'] );

		} else {

			msgbox( $lang['all_err_1'], $lang['lost_err_2'] );

		}
	}

} else {
	$tpl->load_template( 'lostpassword.tpl' );

	if ( $config['allow_recaptcha'] ) {

		$tpl->set( '[recaptcha]', "" );
		$tpl->set( '[/recaptcha]', "" );

		$tpl->set( '{recaptcha}', "<div class=\"g-recaptcha\" data-sitekey=\"{$config['recaptcha_public_key']}\" data-theme=\"{$config['recaptcha_theme']}\"></div><script src='https://www.google.com/recaptcha/api.js?hl={$lang['wysiwyg_language']}' async defer></script>" );

		$tpl->set_block( "'\\[sec_code\\](.*?)\\[/sec_code\\]'si", "" );
		$tpl->set( '{code}', "" );

	} else {

		$tpl->set( '[sec_code]', "" );
		$tpl->set( '[/sec_code]', "" );	
		$tpl->set( '{code}', "<a onclick=\"reload(); return false;\" href=\"#\" title=\"{$lang['reload_code']}\"><span id=\"dle-captcha\"><img src=\"engine/modules/antibot/antibot.php\" alt=\"{$lang['reload_code']}\" border=\"0\" width=\"160\" height=\"80\" /></span></a>" );
		$tpl->set_block( "'\\[recaptcha\\](.*?)\\[/recaptcha\\]'si", "" );
		$tpl->set( '{recaptcha}', "" );

	}
	
	$tpl->copy_template = "<form  method=\"post\" name=\"registration\" action=\"?do=lostpassword\">\n" . $tpl->copy_template . "
<input name=\"submit_lost\" type=\"hidden\" id=\"submit_lost\" value=\"submit_lost\" />
</form>";
	
	$tpl->compile( 'content' );
	
	$tpl->clear();
}
?>