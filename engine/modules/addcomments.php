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
 Назначение: Добавление комментариев в базу данных
=====================================================
*/
if( ! defined( 'DATALIFEENGINE' ) OR !$config['allow_comments'] ) {
	die( "Hacking attempt!" );
}

require_once ENGINE_DIR . '/classes/parse.class.php';

if( $config['allow_comments_wysiwyg'] > 0 ) {
	
	$parse = new ParseFilter( Array ('div', 'span', 'p', 'br', 'strong', 'em', 'ul', 'li', 'ol', 'b', 'u', 'i', 's' ), Array (), 0, 1 );

} else {
	
	$parse = new ParseFilter();
	
}

$parse->safe_mode = true;
$parse->allow_url = $user_group[$member_id['user_group']]['allow_url'];
$parse->allow_image = $user_group[$member_id['user_group']]['allow_image'];

$_TIME = time();
$_IP = get_ip();

$name = $db->safesql( htmlspecialchars(strip_tags( trim( $_POST['name'] ) ), ENT_QUOTES, $config['charset'] ) );

$not_allow_symbol = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", "/", "¬", "#", ";", ":", "~", "[", "]", "{", "}", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"', "'", " ", "&" );
$mail = $db->safesql(trim( str_replace( $not_allow_symbol, '', strip_tags( stripslashes( $_POST['mail'] ) ) ) ) );

$post_id = intval( $_POST['post_id'] );
$stop = array ();
$added_comments_id = 0;

if( $is_logged ) {
	$name = $db->safesql($member_id['name']);
	$mail = $db->safesql($member_id['email']);
}

if ( $user_group[$member_id['user_group']]['spamfilter'] ) {

	$row = $db->super_query( "SELECT * FROM " . PREFIX . "_spam_log WHERE ip = '{$_IP}'" );

	if ( !$row['id'] OR !$row['email'] ) {

		include_once ENGINE_DIR . '/classes/stopspam.class.php';
		$sfs = new StopSpam($config['spam_api_key'], $user_group[$member_id['user_group']]['spamfilter'] );
		$args = array('ip' => $_IP, 'email' => $mail);

		if ($sfs->is_spammer( $args )) {

			if ( !$row['id'] ) {
				$db->query( "INSERT INTO " . PREFIX . "_spam_log (ip, is_spammer, email, date) VALUES ('{$_IP}','1', '{$mail}', '{$_TIME}')" );
			} else {
				$db->query( "UPDATE " . PREFIX . "_spam_log SET is_spammer='1', email='{$mail}' WHERE id='{$row['id']}'" );
			}

			$stop[] = $lang['reg_err_29']." ";
			$CN_HALT = TRUE;

		} else {
			if ( !$row['id'] ) {
				$db->query( "INSERT INTO " . PREFIX . "_spam_log (ip, is_spammer, email, date) VALUES ('{$_IP}','0', '{$mail}', '{$_TIME}')" );
			} else {
				$db->query( "UPDATE " . PREFIX . "_spam_log SET email='{$mail}' WHERE id='{$row['id']}'" );
			}
		}
	
	} else {

		if ($row['is_spammer']) {

			$stop[] = $lang['reg_err_29']." ";
			$CN_HALT = TRUE;
		
		}

	}

}

if ($is_logged AND $config['comments_restricted'] AND (($_TIME - $member_id['reg_date']) < ($config['comments_restricted'] * 86400)) ) {
	$stop[] = str_replace( '{days}', intval($config['comments_restricted']), $lang['news_info_8'] );
	$CN_HALT = TRUE;
}

if( $config['simple_reply'] AND $_POST['parent'] ) $config['allow_comments_wysiwyg'] = "-1";

if( $config['allow_comments_wysiwyg'] > 0 ) {
		
	$parse->wysiwyg = true;
	
	if( $user_group[$member_id['user_group']]['allow_url'] ) $parse->tagsArray[] = 'a';
	if( $user_group[$member_id['user_group']]['allow_image'] ) $parse->tagsArray[] = 'img';
	
	$comments = $parse->BB_Parse( $parse->process( trim($_POST['comments']) ) );
	
} else {
		
	if ($config['allow_comments_wysiwyg'] == "-1") $parse->allowbbcodes = false;
		
	$comments = $parse->BB_Parse( $parse->process( trim($_POST['comments'] )), false );
}

if( intval($config['comments_minlen']) AND dle_strlen( str_replace(" ", "", strip_tags(trim($comments))), $config['charset'] ) < $config['comments_minlen'] ) {

	$stop[] = $lang['news_err_40'];
	$CN_HALT = TRUE;

}

if( $user_group[$member_id['user_group']]['max_comment_day'] ) {

	$this_time = $_TIME - 86400;
	$db->query( "DELETE FROM " . PREFIX . "_sendlog WHERE date < '$this_time' AND flag='3'" );

	if ( !$is_logged ) $check_user = $_IP; else $check_user = $db->safesql($member_id['name']);

	$row = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_sendlog WHERE user = '{$check_user}' AND flag='3'");
		
	if( $row['count'] >=  $user_group[$member_id['user_group']]['max_comment_day'] ) {
		
		$stop[] = str_replace('{max}', $user_group[$member_id['user_group']]['max_comment_day'], $lang['news_err_45']);
		$CN_HALT = TRUE;
	}

}

if ( $is_logged AND $user_group[$member_id['user_group']]['disable_comments_captcha'] AND $member_id['comm_num'] >= $user_group[$member_id['user_group']]['disable_comments_captcha'] ) {

	$user_group[$member_id['user_group']]['comments_question'] = false;
	$user_group[$member_id['user_group']]['captcha'] = false;

}

if( $user_group[$member_id['user_group']]['captcha'] ) {

	if ($config['allow_recaptcha']) {

		require_once ENGINE_DIR . '/classes/recaptcha.php';
		$_REQUEST['sec_code'] = 1;
		$_SESSION['sec_code_session'] = false;

		if ($_POST['g_recaptcha_response']) {
			$reCaptcha = new ReCaptcha($config['recaptcha_private_key']);

			$resp = $reCaptcha->verifyResponse($_IP, $_POST['g_recaptcha_response'] );
			
		        if ($resp != null && $resp->success) {

					$_REQUEST['sec_code'] = 1;
					$_SESSION['sec_code_session'] = 1;

		        }
		}

	}

} else {
	$_SESSION['sec_code_session'] = 1;
	$_REQUEST['sec_code'] = 1;
}

if( $user_group[$member_id['user_group']]['comments_question'] ) {

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
	
		if( !$pass_answer ) { $stop[] = $lang['reg_err_24']; $CN_HALT = TRUE; }
	
	} else { $stop[] = $lang['reg_err_24']; $CN_HALT = TRUE;}
		
}

if( $is_logged and ($member_id['restricted'] == 2 or $member_id['restricted'] == 3) ) {
	
	$stop[] = $lang['news_info_3'];
	$CN_HALT = TRUE;

}

if( dle_strlen( $name, $config['charset'] ) > 40 ) {
	$stop[] = $lang['news_err_1'];
	$CN_HALT = TRUE;
}

if( preg_match( "/[\||\'|\<|\>|\[|\]|\"|\!|\?|\$|\@|\#|\/|\\\|\&\~\*\{\+]/", $name ) ) {
	$stop[] = $lang['reg_err_4'];
	$CN_HALT = TRUE;
}

if( strlen( $mail ) > 50 AND !$is_logged ) {
	$stop[] = $lang['news_err_2'];
	$CN_HALT = TRUE;
}
if( ! $post_id ) {
	$stop[] = $lang['news_err_id'];
	$CN_HALT = TRUE;
}
if( dle_strlen( $comments, $config['charset'] ) > $config['comments_maxlen'] ) {
	$stop[] = $lang['news_err_3'];
	$CN_HALT = TRUE;
}
if( $_REQUEST['sec_code'] != $_SESSION['sec_code_session'] OR !$_SESSION['sec_code_session'] ) {
	$stop[] = $lang['news_err_30'];
	$CN_HALT = TRUE;
}

if( $comments == '' ) {
	$stop[] = $lang['news_err_11'];
	$CN_HALT = TRUE;
}

if( $parse->not_allowed_tags ) {
	$stop[] = $lang['news_err_33'];
	$CN_HALT = TRUE;
}

if( $parse->not_allowed_text ) {
	$stop[] = $lang['news_err_37'];
	$CN_HALT = TRUE;
}

if( $member_id['user_group'] > 2 and intval( $config['flood_time'] ) and !$CN_HALT ) {
	if( flooder( $_IP ) == TRUE ) {
		$stop[] = $lang['news_err_4'] . " " . $lang['news_err_5'] . " {$config['flood_time']} " . $lang['news_err_6'];
		$CN_HALT = TRUE;
	}
}

if( $config['tree_comments'] ){
	
	if( $_POST['parent'] AND intval($_POST['parent']) > 0 ) $parent = intval( $_POST['parent'] ); else $parent = 0;
	if( $_POST['indent'] AND intval($_POST['indent']) > 0 ) $indent = intval( $_POST['indent'] ); else $indent = 0;
	
	if ($parent) {
		
		$row = $db->super_query("SELECT id FROM " . PREFIX . "_comments WHERE id = '{$parent}'");
		
		if (!$row['id']) { $stop[] = $lang['reply_error_2']; $CN_HALT = TRUE; }
		
		if ($config['tree_comments_level'] AND $indent >= $config['tree_comments_level'] ) { $stop[] = $lang['reply_error_1']; $CN_HALT = TRUE; }
	}
	
} else {
	
	$parent = 0;
	$indent = 0;
	
}

$row = $db->super_query( "SELECT id, date, allow_comm, approve, access, user_id FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE id='{$post_id}'" );
$options = news_permission( $row['access'] );
$news_author = $row['user_id'];


if( (! $user_group[$member_id['user_group']]['allow_addc'] and $options[$member_id['user_group']] != 2) or $options[$member_id['user_group']] == 1 ) die( "Hacking attempt!" );

if( ! $row['id'] or ! $row['allow_comm'] or ! $row['approve'] ) {
	$stop[] = $lang['news_err_29'];
	$CN_HALT = TRUE;
}

if ( $config['max_comments_days'] ) {
	$row['date'] = strtotime( $row['date'] );

	if ($row['date'] < ($_TIME - ($config['max_comments_days'] * 3600 * 24)) ) {
		$stop[] = $lang['news_err_29'];
		$CN_HALT = TRUE;
	}
}

if( empty( $name ) and $CN_HALT != TRUE ) {
	$stop[] = $lang['news_err_9'];
	$CN_HALT = TRUE;
}

if( $mail != "" ) {
	if( @count(explode("@", $mail)) != 2 ) {
		$stop[] = $lang['news_err_10'];
		$CN_HALT = TRUE;
	}


}

if( !$is_logged and $CN_HALT != TRUE ) {
	$db->query( "SELECT name from " . USERPREFIX . "_users WHERE name = '" . $name . "'" );
	
	if( $db->num_rows() > 0 ) {
		$name = $lang['c_not_reg']." ".$name;
		
		$db->query( "SELECT name from " . USERPREFIX . "_users WHERE name = '" . $name . "'" );
		
		if( $db->num_rows() > 0 ) {
			$stop[] = $lang['news_err_7'];
			$CN_HALT = TRUE;
		}
	}
	$db->free();
}

if( intval( $config['auto_wrap'] ) ) {

	if ( $config['charset'] == "utf-8" ) $utf_pref = "u"; else $utf_pref = "";
	
	$comments = preg_split( '((>)|(<))', $comments, - 1, PREG_SPLIT_DELIM_CAPTURE );
	$n = count( $comments );
	
	for($i = 0; $i < $n; $i ++) {
		if( $comments[$i] == "<" ) {
			$i ++;
			continue;
		}
		
		if( preg_match( "#([^\s\n\r]{" . intval( $config['auto_wrap'] ) . "})#{$utf_pref}i", $comments[$i] ) ) {

			$comments[$i] = preg_replace( "#([^\s\n\r]{" . intval( $config['auto_wrap']-1 ) . "})#{$utf_pref}i", "\\1<br />", $comments[$i] );

		}

	}
	
	$comments = join( "", $comments );
	
}

$time = date( "Y-m-d H:i:s", $_TIME );
$where_approve = 1;

$_SESSION['sec_code_session'] = 0;
$_SESSION['question'] = false;

if( $CN_HALT ) {
	
	msgbox( $lang['all_err_1'], implode( "<br />", $stop ) . "<br /><br /><a href=\"javascript:history.go(-1)\">" . $lang['all_prev'] . "</a>" );

} else {
	
	$update_comments = false;

	if ( $config['allow_combine'] ) {
	
		$row = $db->super_query( "SELECT id, post_id, user_id, date, text, ip, is_register, approve, parent FROM " . PREFIX . "_comments WHERE post_id = '{$post_id}' ORDER BY id DESC LIMIT 0,1" );
		
		if( $row['id'] ) {
			
			if( $row['user_id'] == $member_id['user_id'] AND $row['is_register'] AND $row['parent'] == $parent ) $update_comments = true;
			elseif( $row['ip'] == $_IP AND ! $row['is_register'] AND ! $is_logged AND $row['parent'] == $parent) $update_comments = true;

			$row['date'] = strtotime( $row['date'] );
			
			if( date( "Y-m-d", $row['date'] ) != date( "Y-m-d", $_TIME ) ) $update_comments = false;

			if ( $user_group[$member_id['user_group']]['edit_limit'] AND (($row['date'] + ($user_group[$member_id['user_group']]['edit_limit'] * 60)) < $_TIME ) ) $update_comments = false;
			
			if( ((dle_strlen( $row['text'], $config['charset'] ) + dle_strlen( $comments, $config['charset'] )) > $config['comments_maxlen']) and $update_comments ) {
				$update_comments = false;
				$stop[] = $lang['news_err_3'];
				$CN_HALT = TRUE;
				msgbox( $lang['all_err_1'], implode( "<br />", $stop ) . "<br /><br /><a href=\"javascript:history.go(-1)\">" . $lang['all_prev'] . "</a>" );
			
			}
		
		}

	}
	
	if( ! $CN_HALT ) {
		
		if( $config['allow_cmod'] and $user_group[$member_id['user_group']]['allow_modc'] ) {
			
			if( $update_comments ) {
				if( $row['approve'] ) $update_comments = false;
			}
			
			$where_approve = 0;
			$stop[] = $lang['news_err_31'];
			$CN_HALT = TRUE;
			msgbox( $lang['all_info'], implode( "<br />", $stop ) . "<br /><br /><a href=\"javascript:history.go(-1)\">" . $lang['all_prev'] . "</a>" );
		
		}
		
		if( $update_comments ) {
			
			$comments = $db->safesql( $row['text'] ) . "<br /><br />" . $db->safesql( $comments );
			$db->query( "UPDATE " . PREFIX . "_comments set date='$time', text='{$comments}', approve='{$where_approve}' WHERE id='{$row['id']}'" );
			$added_comments_id = $row['id'];
		
		} else {

			$comments =	$db->safesql( $comments );		

			if( $is_logged ) $db->query( "INSERT INTO " . PREFIX . "_comments (post_id, user_id, date, autor, email, text, ip, is_register, approve, parent) values ('{$post_id}', '{$member_id['user_id']}', '{$time}', '{$name}', '{$mail}', '{$comments}', '{$_IP}', '1', '{$where_approve}', '{$parent}')" );
			else $db->query( "INSERT INTO " . PREFIX . "_comments (post_id, date, autor, email, text, ip, is_register, approve, parent) values ('{$post_id}', '{$time}', '{$name}', '{$mail}', '{$comments}', '{$_IP}', '0', '{$where_approve}', '{$parent}')" );

			$added_comments_id = $db->insert_id();			

			// обновление количества комментариев в новостях 
			if( $where_approve ) $db->query( "UPDATE " . PREFIX . "_post SET comm_num=comm_num+1 WHERE id='{$post_id}'" );
			
			// обновление количества комментариев у юзера 
			if( $is_logged ) {
				$db->query( "UPDATE " . USERPREFIX . "_users SET comm_num=comm_num+1 WHERE user_id ='{$member_id['user_id']}'" );
			}
		}
		
		// Защита от флуда
		if( $config['flood_time'] ) {
			$db->query( "INSERT INTO " . PREFIX . "_flood (id, ip) values ('$_TIME', '$_IP')" );
		}

		if( $user_group[$member_id['user_group']]['max_comment_day'] ) {		
			$db->query( "INSERT INTO " . PREFIX . "_sendlog (user, date, flag) values ('{$check_user}', '{$_TIME}', '3')" );
		}

		if ( $config['mail_comments'] OR $config['allow_subscribe'] ) {

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

			$body = str_replace( '\n', "", $comments );
			$body = str_replace( '\r', "", $body );
			
			$body = stripslashes( stripslashes( $body ) );
			$body = str_replace( "<br />", "\n", $body );
			$body = strip_tags( $body );
			
			if( $row['use_html'] ) {
				$body = str_replace("\n", "<br />", $body );
			}
					
			$row['template'] = str_replace( "{%text%}", $body, $row['template'] );

		}

		if( $config['mail_comments'] ) {
			
			$body = str_replace( "{%ip%}", $_IP, $row['template'] );
			$body = str_replace( "{%username_to%}", $lang['admin'], $body );
			$body = str_replace( "{%unsubscribe%}", "--", $body );			
			$mail->send( $config['admin_mail'], $lang['mail_comments'], $body );
		
		}


		if ( $config['allow_subscribe'] AND $where_approve ) {

			$row['template'] = str_replace( "{%ip%}", "--", $row['template'] );
			$found_subscribe = false;

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

				} else {

					$found_subscribe = true;

				}

			}

			$db->free();

			if ($_POST['allow_subscribe'] AND $user_group[$member_id['user_group']]['allow_subscribe'] AND !$found_subscribe) {

				if(function_exists('openssl_random_pseudo_bytes')) {
					
					$stronghash = md5(openssl_random_pseudo_bytes(15));
					
				} else $stronghash = md5(uniqid( mt_rand(), TRUE ));

				$salt = str_shuffle($stronghash);
				$s_hash = "";
				
				for($i = 0; $i < 10; $i ++) {
					$s_hash .= $salt{mt_rand( 0, 31 )};
				}

				$s_hash = md5($s_hash);

				$db->query( "INSERT INTO " . PREFIX . "_subscribe (user_id, name, email, news_id, hash) values ('{$member_id['user_id']}', '{$member_id['name']}', '{$member_id['email']}', '{$post_id}', '{$s_hash}')" );

			}

		}

		if ( $config['allow_alt_url'] AND !$config['seo_type'] ) $cprefix = "full_"; else $cprefix = "full_".$post_id;

		clear_cache( array( 'news_', 'rss', 'comm_'.$post_id, $cprefix ) );
		
		if( ! $ajax_adds AND ! $CN_HALT ) {
			header( "Location: {$_SERVER['REQUEST_URI']}" );
			die();
		}
	
	} else msgbox( $lang['all_err_1'], implode( "<br />", $stop ) . "<br /><br /><a href=\"javascript:history.go(-1)\">" . $lang['all_prev'] . "</a>" );

}
?>