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
 Назначение: обратная связь
=====================================================
*/

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

	if( isset( $_POST['send'] ) ) {
		$stop = "";
		
		if( $is_logged ) {

			$name = $member_id['name'];
			$email = $member_id['email'];

		} else {
			
			$name = $lang['feedback_not_reg']." ".strip_tags( stripslashes($_POST['name']) );
		
			$not_allow_symbol = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", "/", "¬", "#", ";", ":", "~", "[", "]", "{", "}", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"', "'" );
			$email = $db->safesql( trim( str_replace( $not_allow_symbol, '', strip_tags( stripslashes( $_POST['email'] ) ) ) ) );
		
		}
		
		$subject = strip_tags( stripslashes( $_POST['subject'] ) );
		$message = stripslashes( $_POST['message'] );
		$recip = intval( $_POST['recip'] );

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

		if( empty( $recipient['fullname'] ) ) $recipient['fullname'] = $recipient['name'];

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
		
		if( empty( $email ) OR dle_strlen($email, $config['charset']) > 50 OR @count(explode("@", $email)) != 2) {
			$stop .= $lang['feed_err_2'];
		} 

		if( empty( $subject ) OR dle_strlen($subject, $config['charset']) > 200 ) {
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
				
				    if ( $resp != null && $resp->success ) {
	
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
			
			msgbox( $lang['all_err_1'], "<ul>{$stop}</ul><a href=\"javascript:history.go(-1)\">$lang[all_prev]</a>" );
		
		} else {
			
			include_once ENGINE_DIR . '/classes/mail.class.php';
			
			$row = $db->super_query( "SELECT * FROM " . PREFIX . "_email WHERE name='feed_mail' LIMIT 0,1" );
			$mail = new dle_mail( $config, $row['use_html']);
			
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
			
			if( $mail->send_error ) msgbox( $lang['all_info'], $mail->smtp_msg );
			else {

				if( $user_group[$member_id['user_group']]['max_mail_day'] ) { 
					if ( !$is_logged ) $check_user = $_IP; else $check_user = $db->safesql($member_id['name']);		
					$db->query( "INSERT INTO " . PREFIX . "_sendlog (user, date, flag) values ('{$check_user}', '{$_TIME}', '2')" );
				}

				msgbox( $lang['feed_ok_1'], "{$lang['feed_ok_2']} <a href=\"{$config['http_home_url']}\">{$lang['feed_ok_4']}</a>" );
			}
		
		}
	
	} else {


		if( ! $user_group[$member_id['user_group']]['allow_feed'] )	{

			$group = 2;
			$user = false;

			if ($_GET['user']) {

				$lang['feed_error'] = str_replace( '{group}', $user_group[$member_id['user_group']]['group_name'], $lang['feed_error'] );
				msgbox( $lang['all_info'], $lang['feed_error'] );

			}

		} else {

			if (isset ($_GET['user'])) $user = intval( $_GET['user'] ); else $user = false;
			$group = 3;

		}
		
		if( ! $user ) $db->query( "SELECT name, user_group, user_id FROM " . USERPREFIX . "_users WHERE user_group < '$group' AND allow_mail = '1' ORDER BY user_group" );
		else $db->query( "SELECT name, user_group, user_id FROM " . USERPREFIX . "_users WHERE user_id = '$user' AND allow_mail = '1'" );
		
		if( $db->num_rows() ) {
			$empf = "<select name=\"recip\">";
			$i = 1;
			while ( $row = $db->get_array() ) {
				$str = $row['name'] . " (" . stripslashes( $user_group[$row['user_group']]['group_name'] ) . ")";
				
				if( $i == 1 ) {
					$empf .= "<option selected=\"selected\" value=\"" . $row["user_id"] . "\">" . $str . "</option>\n";
				} else {
					$empf .= "<option value=\"" . $row["user_id"] . "\">" . $str . "</option>\n";
				}
				$i ++;
			}
			$empf .= "</select>";
			
			$db->free();
			
			$tpl->load_template( 'feedback.tpl' );
			
			$tpl->set( '{recipient}', $empf );

			if( $user_group[$member_id['user_group']]['feedback_question'] ) {
	
				$tpl->set( '[question]', "" );
				$tpl->set( '[/question]', "" );
	
				$question = $db->super_query("SELECT id, question FROM " . PREFIX . "_question ORDER BY RAND() LIMIT 1");
				$tpl->set( '{question}', "<span id=\"dle-question\">".htmlspecialchars( stripslashes( $question['question'] ), ENT_QUOTES, $config['charset'] )."</span>" );
	
				$_SESSION['question'] = $question['id'];
	
			} else {
	
				$tpl->set_block( "'\\[question\\](.*?)\\[/question\\]'si", "" );
				$tpl->set( '{question}', "" );
	
			}

			if( $user_group[$member_id['user_group']]['captcha_feedback'] ) {

				if ( $config['allow_recaptcha'] ) {
			
					$tpl->set( '[recaptcha]', "" );
					$tpl->set( '[/recaptcha]', "" );
			
				$tpl->set( '{recaptcha}', "<div class=\"g-recaptcha\" data-sitekey=\"{$config['recaptcha_public_key']}\" data-theme=\"{$config['recaptcha_theme']}\"></div><script src='https://www.google.com/recaptcha/api.js?hl={$lang['wysiwyg_language']}' async defer></script>" );
	
					$tpl->set_block( "'\\[sec_code\\](.*?)\\[/sec_code\\]'si", "" );
					$tpl->set( '{code}', "" );
			
				} else {
			
					$tpl->set( '[sec_code]', "" );
					$tpl->set( '[/sec_code]', "" );	
					$tpl->set( '{code}', "<a onclick=\"reload(); return false;\" href=\"#\" title=\"{$lang['reload_code']}\"><span id=\"dle-captcha\"><img src=\"engine/modules/antibot/antibot.php\" alt=\"{$lang['reload_code']}\" width=\"160\" height=\"80\" /></span></a>" );
					$tpl->set_block( "'\\[recaptcha\\](.*?)\\[/recaptcha\\]'si", "" );
					$tpl->set( '{recaptcha}', "" );
			
				}
			} else {
				$tpl->set( '{code}', "" );
				$tpl->set( '{recaptcha}', "" );
				$tpl->set_block( "'\\[recaptcha\\](.*?)\\[/recaptcha\\]'si", "" );
				$tpl->set_block( "'\\[sec_code\\](.*?)\\[/sec_code\\]'si", "" );
			}
		
			if( ! $is_logged ) {
				$tpl->set( '[not-logged]', "" );
				$tpl->set( '[/not-logged]', "" );
			} else
				$tpl->set_block( "'\\[not-logged\\](.*?)\\[/not-logged\\]'si", "" );
			
			$tpl->copy_template = "<form  method=\"post\" id=\"sendmail\" name=\"sendmail\" action=\"\">\n" . $tpl->copy_template . "
<input name=\"send\" type=\"hidden\" value=\"send\" />
</form>";
			
			$onload_scripts[] = <<<HTML
$('#sendmail').submit(function() {

	if(document.sendmail.subject.value == '' || document.sendmail.message.value == '') { 

		DLEalert('{$lang['comm_req_f']}', dle_info);
		return false;

	}

	var params = {};
	$.each($('#sendmail').serializeArray(), function(index,value) {
		params[value.name] = value.value;
	});

	params['skin'] = dle_skin;

	ShowLoading('');

	$.post(dle_root + "engine/ajax/feedback.php", params, function(data){
		HideLoading('');
		if (data) {
	
			if (data.status == "ok") {

				scroll( 0, $("#dle-content").offset().top - 70 );
				$('#dle-content').html(data.text);	

			} else {

				if ( document.sendmail.sec_code ) {
		           document.sendmail.sec_code.value = '';
		           reload();
			    }

				if ( typeof grecaptcha != "undefined" ) {
					grecaptcha.reset();
				}

				DLEalert(data.text, dle_info);

			}
	
		}
	}, "json");

  return false;
});
HTML;
			
			$tpl->compile( 'content' );
			$tpl->clear();
		
		} else {
			msgbox( $lang['all_err_1'], $lang['feed_err_7'] );
		}
	}

?>