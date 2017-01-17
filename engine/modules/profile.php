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
 Файл: profile.php
-----------------------------------------------------
 Назначение: Профиль пользователя
=====================================================
*/

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

include_once ENGINE_DIR . '/classes/parse.class.php';
$parse = new ParseFilter( );
$parse->safe_mode = true;

//####################################################################################################################
//         Обновление информации о пользователе
//####################################################################################################################
if( $allow_userinfo and $doaction == "adduserinfo" ) {
	
	$stop = false;
	$id = intval($_POST['id']);

	if( !$is_logged OR $_POST['dle_allow_hash'] == "" OR $_POST['dle_allow_hash'] != $dle_login_hash OR !$id) {
		
		die( "Hacking attempt! User ID not valid" );
	
	}

	if ( $member_id['user_id'] != $id AND $member_id['user_group'] != 1 ) {
		die( "Hacking attempt!" );
	}

	$row = $db->super_query( "SELECT * FROM " . USERPREFIX . "_users WHERE user_id = '{$id}'" );
	
	if( !$is_logged or !($member_id['user_id'] == $row['user_id'] or $member_id['user_group'] == 1) ) {

		$stop = $lang['news_err_13'];

	} else {

		$parse->allow_url = $user_group[$member_id['user_group']]['allow_url'];
		$parse->allow_image = $user_group[$member_id['user_group']]['allow_image'];
		
		$password1 = $_POST['password1'];
		$password2 = $_POST['password2'];

		if( $_POST['allow_mail'] ) $allow_mail = 0; else $allow_mail = 1;

		$altpass = md5( $_POST['altpass'] );
		$info = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['info'] ), false ) );

		$not_allow_symbol = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", "/", "¬", "#", ";", ":", "~", "[", "]", "{", "}", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"', "'", " ", "&" );
		$email = $db->safesql(trim( str_replace( $not_allow_symbol, '', strip_tags( stripslashes( $_POST['email'] ) ) ) ) );

		$timezones = array('Pacific/Midway','US/Samoa','US/Hawaii','US/Alaska','US/Pacific','America/Tijuana','US/Arizona','US/Mountain','America/Chihuahua','America/Mazatlan','America/Mexico_City','America/Monterrey','US/Central','US/Eastern','US/East-Indiana','America/Lima','America/Caracas','Canada/Atlantic','America/La_Paz','America/Santiago','Canada/Newfoundland','America/Buenos_Aires','Greenland','Atlantic/Stanley','Atlantic/Azores','Africa/Casablanca','Europe/Dublin','Europe/Lisbon','Europe/London','Europe/Amsterdam','Europe/Belgrade','Europe/Berlin','Europe/Bratislava','Europe/Brussels','Europe/Budapest','Europe/Copenhagen','Europe/Madrid','Europe/Paris','Europe/Prague','Europe/Rome','Europe/Sarajevo','Europe/Stockholm','Europe/Vienna','Europe/Warsaw','Europe/Zagreb','Europe/Athens','Europe/Bucharest','Europe/Helsinki','Europe/Istanbul','Asia/Jerusalem','Europe/Kiev','Europe/Minsk','Europe/Riga','Europe/Sofia','Europe/Tallinn','Europe/Vilnius','Asia/Baghdad','Asia/Kuwait','Africa/Nairobi','Asia/Tehran','Europe/Kaliningrad','Europe/Moscow','Europe/Volgograd','Europe/Samara','Asia/Baku','Asia/Muscat','Asia/Tbilisi','Asia/Yerevan','Asia/Kabul','Asia/Yekaterinburg','Asia/Tashkent','Asia/Kolkata','Asia/Kathmandu','Asia/Almaty','Asia/Novosibirsk','Asia/Jakarta','Asia/Krasnoyarsk','Asia/Hong_Kong','Asia/Kuala_Lumpur','Asia/Singapore','Asia/Taipei','Asia/Ulaanbaatar','Asia/Urumqi','Asia/Irkutsk','Asia/Seoul','Asia/Tokyo','Australia/Adelaide','Australia/Darwin','Asia/Yakutsk','Australia/Brisbane','Pacific/Port_Moresby','Australia/Sydney','Asia/Vladivostok','Asia/Sakhalin','Asia/Magadan','Pacific/Auckland','Pacific/Fiji');
		$timezone = $db->safesql( (string)$_POST['timezone'] );		
		
		if (!in_array($timezone, $timezones)) $timezone = '';

		$fullname = $db->safesql( $parse->process( $_POST['fullname'] ) );
		$land = $db->safesql( $parse->process( $_POST['land'] ) );
		
		if ($_POST['allowed_ip']) {

			$_POST['allowed_ip'] = str_replace( "\r", "", trim( $_POST['allowed_ip'] ) );
			$allowed_ip = str_replace( "\n", "|", $_POST['allowed_ip'] );
	
			$temp_array = explode ("|", $allowed_ip);
			$allowed_ip	= array();
	
			if (count($temp_array)) {
	
				foreach ( $temp_array as $value ) {
					$value1 = str_replace( "*", "0", trim($value) );
					$value1 = explode ('/', $value1);
					
					$value1 = ip2long($value1[0]);
	
					if( $value1 != -1 AND $value1 !== FALSE ) $allowed_ip[] = trim( $value );
				}
		
			}
	
			if ( count($allowed_ip) ) $allowed_ip = $db->safesql( $parse->process( implode("|", $allowed_ip) ) ); else $allowed_ip = "";

		} else $allowed_ip = "";

		$xfieldsid = stripslashes( $row['xfields'] );
		
		if( $user_group[$row['user_group']]['allow_signature'] ) {
			
			$signature = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['signature'] ), false ) );
		
		} else
			$signature = "";

		if ( $_POST['gravatar'] ) {
			$gravatar = $db->safesql(trim( str_replace( $not_allow_symbol, '', strip_tags( stripslashes( $_POST['gravatar'] ) ) ) ) );
			if ( count(explode("@", $gravatar)) == 2 AND strlen( $gravatar ) < 50) {
				$db->query( "UPDATE " . USERPREFIX . "_users SET foto='{$gravatar}' WHERE user_id = '{$id}'" );
			} else $db->query( "UPDATE " . USERPREFIX . "_users set foto='' WHERE user_id = '{$id}'" );
		} else {
			if (count(explode("@", $row['foto'])) == 2) $db->query( "UPDATE " . USERPREFIX . "_users SET foto='' WHERE user_id = '{$id}'" );
		}

		$image = $_FILES['image']['tmp_name'];
		$image_name = $_FILES['image']['name'];
		$image_size = $_FILES['image']['size'];
		$img_name_arr = explode( ".", $image_name );
		$type = totranslit( end( $img_name_arr ) );
		
		if( $image_name != "" ) $image_name = totranslit( stripslashes( $img_name_arr[0] ) ) . "." . $type;

		if( strpos ( $image_name, "php" ) !== false ) die("Hacking attempt!");
	
		if( is_uploaded_file( $image ) and ! $stop ) {
			
			if( intval( $user_group[$member_id['user_group']]['max_foto'] ) > 0 ) {
				
				if( !$config['avatar_size'] OR $image_size < ($config['avatar_size'] * 1024) ) {
					
					$allowed_extensions = array ("jpg", "png", "gif" );
					
					if( in_array( $type, $allowed_extensions ) AND $image_name ) {
						
						include_once ENGINE_DIR . '/classes/thumb.class.php';
						
						$res = @move_uploaded_file( $image, ROOT_DIR . "/uploads/fotos/" . $row['user_id'] . "." . $type );
						
						if( $res ) {
							
							@chmod( ROOT_DIR . "/uploads/fotos/" . $row['user_id'] . "." . $type, 0666 );
							$thumb = new thumbnail( ROOT_DIR . "/uploads/fotos/" . $row['user_id'] . "." . $type );
							
							$thumb->size_auto( $user_group[$member_id['user_group']]['max_foto'] );
							$thumb->jpeg_quality( $config['jpeg_quality'] );
							$thumb->save( ROOT_DIR . "/uploads/fotos/foto_" . $row['user_id'] . "." . $type );
							
							@chmod( ROOT_DIR . "/uploads/fotos/foto_" . $row['user_id'] . "." . $type, 0666 );
							
							if (strpos($config['http_home_url'], "//") === 0) $avatar_url = $config['http_home_url'];
							elseif (strpos($config['http_home_url'], "/") === 0) $avatar_url = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];
							else $avatar_url = $config['http_home_url'];
							
							$foto_name = $db->safesql( $avatar_url . "uploads/fotos/" ."foto_" . $row['user_id'] . "." . $type );
							
							$db->query( "UPDATE " . USERPREFIX . "_users SET foto='{$foto_name}' WHERE user_id = '{$id}'" );
						
						} else
							$stop .= $lang['news_err_14'];
					} else
						$stop .= $lang['news_err_15'];
				} else
					$stop .= str_replace("{size}", $config['avatar_size'], $lang['news_err_16']);
			} else
				$stop .= $lang['news_err_32'];
			
			@unlink( ROOT_DIR . "/uploads/fotos/" . $row['user_id'] . "." . $type );
		}
		
		if( $_POST['del_foto'] == "yes" AND !$stop) {
			$url = @parse_url ( $row['foto'] );
			$row['foto'] = basename($url['path']);
			
			@unlink( ROOT_DIR . "/uploads/fotos/" . totranslit($row['foto']) );
			$db->query( "UPDATE " . USERPREFIX . "_users set foto='' WHERE user_id = '{$id}'" );
		
		}
		
		if( strlen( $password1 ) > 0 ) {
			
			$altpass = md5( $altpass );
			
			if( $altpass != $member_id['password'] ) {
				$stop .= $lang['news_err_17'];
			}
			
			if( $password1 != $password2 ) {
				$stop .= $lang['news_err_18'];
			}
			
			if( strlen( $password1 ) < 6 ) {
				$stop .= $lang['news_err_19'];
			}

			if ($member_id['user_id'] == $row['user_id'] AND $user_group[$member_id['user_group']]['admin_editusers']) {
				$stop .= $lang['news_err_42'];
			}
		}
		
		if( empty( $email ) OR strlen( $email ) > 50 OR @count(explode("@", $email)) != 2) {
			
			$stop .= $lang['news_err_21'];
		}

		if ($member_id['user_id'] == $row['user_id'] AND $email != $member_id['email'] AND $user_group[$member_id['user_group']]['admin_editusers']) {
			$stop .= $lang['news_err_42'];
		}

		if ( $email != $member_id['email'] ) {

			if( count( $banned_info['email'] ) ) foreach ( $banned_info['email'] as $banned ) {
				
				$banned['email'] = str_replace( '\*', '.*', preg_quote( $banned['email'], "#" ) );
				
				if( $banned['email'] and preg_match( "#^{$banned['email']}$#i", $email ) ) {
					
					if( $banned['descr'] ) {
						$lang['reg_err_23'] = str_replace( "{descr}", $lang['reg_err_22'], $lang['reg_err_23'] );
						$lang['reg_err_23'] = str_replace( "{descr}", $banned['descr'], $lang['reg_err_23'] );
					} else
						$lang['reg_err_23'] = str_replace( "{descr}", "", $lang['reg_err_23'] );
					
					$stop .= $lang['reg_err_23'];
				}
			}

		}

		if ($config['registration_type'] AND $email != $row['email'] AND !$user_group[$member_id['user_group']]['admin_editusers'] ) $send_mail_log = true; else $send_mail_log = false;

		if( intval( $user_group[$member_id['user_group']]['max_info'] ) > 0 and dle_strlen( $info, $config['charset'] ) > $user_group[$member_id['user_group']]['max_info'] ) {
			
			$stop .= $lang['news_err_22'];
		}
		if( intval( $user_group[$member_id['user_group']]['max_signature'] ) > 0 and dle_strlen( $signature, $config['charset'] ) > $user_group[$member_id['user_group']]['max_signature'] ) {
			
			$stop .= $lang['not_allowed_sig'];
		}
		if( dle_strlen( $fullname, $config['charset'] ) > 100 ) {
			
			$stop .= $lang['news_err_23'];
		}
		if ( preg_match( "/[\||\'|\<|\>|\"|\!|\]|\?|\$|\@|\/|\\\|\&\~\*\+]/", $fullname ) ) {
	
			$stop .= $lang['news_err_35'];
		}
		if( dle_strlen( $land, $config['charset'] ) > 100 ) {
			
			$stop .= $lang['news_err_24'];
		}
		if ( preg_match( "/[\||\'|\<|\>|\"|\!|\]|\?|\$|\@|\/|\\\|\&\~\*\+]/", $land ) ) {
	
			$stop .= $lang['news_err_36'];
		}
		
		if( $parse->not_allowed_tags ) {
			
			$stop .= $lang['news_err_34'];
		}
	
		if( $parse->not_allowed_text ) {
			
			$stop .= $lang['news_err_38'];
		}
		
		$db->query( "SELECT name FROM " . USERPREFIX . "_users WHERE email = '$email' AND user_id != '{$id}'" );
		
		if( $db->num_rows() ) {
			$stop .= $lang['reg_err_8'];
		}
		
		$db->free();

	}
	
	if( $stop ) {

		msgbox( $lang['all_err_1'], "<ul>".$stop."</ul>" );

	} else {
		
		$xfieldsaction = "init";
		$xfieldsadd = false;
		include (ENGINE_DIR . '/inc/userfields.php');
		$filecontents = array ();
		
		if( !empty( $postedxfields ) ) {
			foreach ( $postedxfields as $xfielddataname => $xfielddatavalue ) {
				if( ! $xfielddatavalue ) {
					continue;
				}
				
				$xfielddatavalue = $db->safesql( $parse->BB_Parse( $parse->process( $xfielddatavalue ), false ) );
		
				$xfielddataname = $db->safesql( str_replace( $not_allow_symbol, '', $xfielddataname) );
				
				$xfielddataname = str_replace( "|", "&#124;", $xfielddataname );
				$xfielddatavalue = str_replace( "|", "&#124;", $xfielddatavalue );
				$filecontents[] = "$xfielddataname|$xfielddatavalue";
			}
			
			$filecontents = implode( "||", $filecontents );
		} else
			$filecontents = '';

		if ( !$send_mail_log AND $email != $row['email']) {

			$mailchange = " email='{$email}',";

			$db->query( "UPDATE " . PREFIX . "_subscribe SET email='{$email}' WHERE user_id = '{$id}'" );

		} else $mailchange = "";
		
		if( strlen( $password1 ) > 0 ) {

			$db->query( "UPDATE " . USERPREFIX . "_social_login SET password='" . md5( $password1 ) . "' WHERE uid='{$id}'" );
			$password1 = md5( md5( $password1 ) );
			$sql_user = "UPDATE " . USERPREFIX . "_users SET fullname='$fullname', land='$land',{$mailchange} info='$info', signature='$signature', password='$password1', allow_mail='$allow_mail', xfields='$filecontents', allowed_ip='$allowed_ip', timezone='$timezone' WHERE user_id = '{$id}'";
		
		} else {
			
			$sql_user = "UPDATE " . USERPREFIX . "_users SET fullname='$fullname', land='$land',{$mailchange} info='$info', signature='$signature', allow_mail='$allow_mail', xfields='$filecontents', allowed_ip='$allowed_ip', timezone='$timezone' WHERE user_id = '{$id}'";
		
		}
		
		$db->query( $sql_user );

		if ($user_group[$member_id['user_group']]['admin_editusers']) $db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '64', '{$row['name']}')" );

		if ( $_POST['subscribe'] ) $db->query( "DELETE FROM " . PREFIX . "_subscribe WHERE user_id = '{$row['user_id']}'" );

		if ( $send_mail_log ) {

			if(function_exists('openssl_random_pseudo_bytes')) {
				
				$stronghash = md5(openssl_random_pseudo_bytes(15));
				
			} else $stronghash = md5(uniqid( mt_rand(), TRUE ));

			$salt = str_shuffle("abchefghjkmnpqrstuvwxyz0123456789".$stronghash);
			$rand_lost = "";
			
			for($i = 0; $i < 15; $i ++) {
				$rand_lost .= $salt{mt_rand( 0, 33 )};
			}
			
			$hashid = sha1( md5( $row['user_id'] . $row['email'] ) . time() . $rand_lost );

			$db->query( "DELETE FROM " . USERPREFIX . "_mail_log WHERE user_id='{$row['user_id']}'" );
			
			$db->query( "INSERT INTO " . USERPREFIX . "_mail_log (user_id, mail, hash) values ('{$row['user_id']}', '{$email}', '{$hashid}')" );

			include_once ENGINE_DIR . '/classes/mail.class.php';
			$mail = new dle_mail( $config );
			
			if (strpos($config['http_home_url'], "//") === 0) $slink = "http:".$config['http_home_url'];
			elseif (strpos($config['http_home_url'], "/") === 0) $slink = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];
			else $slink = $config['http_home_url'];
					
			$link = $slink . "index.php?do=changemail&id=".$hashid;

			$lang['change_mail_1'] = str_replace("{name}", $member_id['name'], $lang['change_mail_1']);

			$message = $lang['change_mail_1']." {$email} {$lang['change_mail_2']}\n\n{$lang['change_mail_3']} {$link}\n\n{$lang['lost_mfg']} ".$config['http_home_url'];
			$mail->send( $email, $lang['change_mail_subj'], $message );

			msgbox( $lang['all_info'], "<ul>".$lang['change_mail']."</ul>" );
		}

	}

}

//####################################################################################################################
//         Просмотр профиля пользователя
//####################################################################################################################


$user_found = FALSE;

if( preg_match( "/[\||\'|\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $user ) ) $user="";

$sql_result = $db->query( "SELECT * FROM " . USERPREFIX . "_users WHERE name = '{$user}'" );

$tpl->load_template( 'userinfo.tpl' );

while ( $row = $db->get_row( $sql_result ) ) {
	
	$user_found = TRUE;
	
	if( $row['banned'] == 'yes' ) $user_group[$row['user_group']]['group_name'] = $lang['user_ban'];
	
	if( $row['allow_mail'] ) {

		if ( !$user_group[$member_id['user_group']]['allow_feed'] AND $row['user_group'] != 1 )
			$tpl->set( '{email}', $lang['news_mail'] );
		else
			$tpl->set( '{email}', "<a href=\"$PHP_SELF?do=feedback&amp;user=$row[user_id]\">" . $lang['news_mail'] . "</a>" );


	} else {

		$tpl->set( '{email}', $lang['news_mail'] );

	}

	if ( $user_group[$member_id['user_group']]['allow_pm'] ) {
		
		$tpl->set( '{pm}', "<a href=\"$PHP_SELF?do=pm&amp;doaction=newpm&amp;username=" . urlencode($row['name']) . "\">" . $lang['news_pmnew'] . "</a>" );
		
	} else {
		
		$tpl->set( '{pm}', $lang['news_pmnew'] );
	}

	
	if( ! $row['allow_mail'] ) $mailbox = "checked";
	else $mailbox = "";

	if ( count(explode("@", $row['foto'])) == 2 ) {
		$tpl->set( '{gravatar}', $row['foto'] );	

		$tpl->set( '{foto}', '//www.gravatar.com/avatar/' . md5(trim($row['foto'])) . '?s=' . intval($user_group[$row['user_group']]['max_foto']) );
	
	} else {
	
		if( $row['foto'] ) {
			
			if (strpos($row['foto'], "//") === 0) $avatar = "http:".$row['foto']; else $avatar = $row['foto'];

			$avatar = @parse_url ( $avatar );

			if( $avatar['host'] ) {
				
				$tpl->set( '{foto}', $row['foto'] );
				
			} else $tpl->set( '{foto}', $config['http_home_url'] . "uploads/fotos/" . $row['foto'] );
			
		} else $tpl->set( '{foto}', "{THEME}/dleimages/noavatar.png" );

		$tpl->set( '{gravatar}', '' );
	}

	if (stripos ( $tpl->copy_template, "[profile-user-group=" ) !== false) {
		$tpl->copy_template = preg_replace_callback ( '#\\[profile-user-group=(.+?)\\](.*?)\\[/profile-user-group\\]#is',
			function ($matches) {
				global $row;

				$groups = $matches[1];
				$block = $matches[2];
				
				$groups = explode( ',', $groups );
				
				if( !in_array( $row['user_group'], $groups ) ) return "";
		
				return $block;
			},		
		$tpl->copy_template );
	}

	if (stripos ( $tpl->copy_template, "[not-profile-user-group=" ) !== false) {
		$tpl->copy_template = preg_replace_callback ( '#\\[not-profile-user-group=(.+?)\\](.*?)\\[/not-profile-user-group\\]#is',
			function ($matches) {
				global $row;
				
				$groups = $matches[1];
				$block = $matches[2];
				
				$groups = explode( ',', $groups );
				
				if( in_array( $row['user_group'], $groups ) ) return "";
		
				return $block;
			},		
		$tpl->copy_template );
	}
	
	$tpl->set( '{hidemail}', "<input type=\"checkbox\" name=\"allow_mail\" value=\"1\" " . $mailbox . " /> " . $lang['news_noamail'] );
	$tpl->set( '{usertitle}', stripslashes( $row['name'] ) );

	if( $row['fullname'] ) {
		$tpl->set( '[fullname]', "" );
		$tpl->set( '[/fullname]', "" );
		$tpl->set( '{fullname}', stripslashes( $row['fullname'] ) );
		$tpl->set_block( "'\\[not-fullname\\](.*?)\\[/not-fullname\\]'si", "" );
	
	} else {
		$tpl->set_block( "'\\[fullname\\](.*?)\\[/fullname\\]'si", "" );
		$tpl->set( '{fullname}', "" );
		$tpl->set( '[not-fullname]', "" );
		$tpl->set( '[/not-fullname]', "" );
	}

	if( $row['land'] ) {
		$tpl->set( '[land]', "" );
		$tpl->set( '[/land]', "" );
		$tpl->set( '{land}', stripslashes( $row['land'] ) );
		$tpl->set_block( "'\\[not-land\\](.*?)\\[/not-land\\]'si", "" );
	
	} else {
		$tpl->set_block( "'\\[land\\](.*?)\\[/land\\]'si", "" );
		$tpl->set( '{land}', "" );
		$tpl->set( '[not-land]', "" );
		$tpl->set( '[/not-land]', "" );
	}

	if( $row['info'] ) {
		$tpl->set( '[info]', "" );
		$tpl->set( '[/info]', "" );
		$tpl->set( '{info}', stripslashes( $row['info'] ) );
		$tpl->set_block( "'\\[not-info\\](.*?)\\[/not-info\\]'si", "" );	
	} else {
		$tpl->set_block( "'\\[info\\](.*?)\\[/info\\]'si", "" );
		$tpl->set( '{info}', "" );
		$tpl->set( '[not-info]', "" );
		$tpl->set( '[/not-info]', "" );
	}

	if ( ($row['lastdate'] + 1200) > $_TIME ) {

		$tpl->set( '[online]', "" );
		$tpl->set( '[/online]', "" );
		$tpl->set_block( "'\\[offline\\](.*?)\\[/offline\\]'si", "" );

	} else {
		$tpl->set( '[offline]', "" );
		$tpl->set( '[/offline]', "" );
		$tpl->set_block( "'\\[online\\](.*?)\\[/online\\]'si", "" );
	}

	if ( $config['rating_type'] == "1" ) {
			$tpl->set( '[rating-type-2]', "" );
			$tpl->set( '[/rating-type-2]', "" );
			$tpl->set_block( "'\\[rating-type-1\\](.*?)\\[/rating-type-1\\]'si", "" );
			$tpl->set_block( "'\\[rating-type-3\\](.*?)\\[/rating-type-3\\]'si", "" );
	} elseif ( $config['rating_type'] == "2" ) {
			$tpl->set( '[rating-type-3]', "" );
			$tpl->set( '[/rating-type-3]', "" );
			$tpl->set_block( "'\\[rating-type-1\\](.*?)\\[/rating-type-1\\]'si", "" );
			$tpl->set_block( "'\\[rating-type-2\\](.*?)\\[/rating-type-2\\]'si", "" );
	} else {
			$tpl->set( '[rating-type-1]', "" );
			$tpl->set( '[/rating-type-1]', "" );
			$tpl->set_block( "'\\[rating-type-3\\](.*?)\\[/rating-type-3\\]'si", "" );
			$tpl->set_block( "'\\[rating-type-2\\](.*?)\\[/rating-type-2\\]'si", "" );	
	}

	if ( $config['comments_rating_type'] == "1" ) {
			$tpl->set( '[comments-rating-type-2]', "" );
			$tpl->set( '[/comments-rating-type-2]', "" );
			$tpl->set_block( "'\\[comments-rating-type-1\\](.*?)\\[/comments-rating-type-1\\]'si", "" );
			$tpl->set_block( "'\\[comments-rating-type-3\\](.*?)\\[/comments-rating-type-3\\]'si", "" );
	} elseif ( $config['comments_rating_type'] == "2" ) {
			$tpl->set( '[comments-rating-type-3]', "" );
			$tpl->set( '[/comments-rating-type-3]', "" );
			$tpl->set_block( "'\\[comments-rating-type-1\\](.*?)\\[/comments-rating-type-1\\]'si", "" );
			$tpl->set_block( "'\\[comments-rating-type-2\\](.*?)\\[/comments-rating-type-2\\]'si", "" );
	} else {
			$tpl->set( '[comments-rating-type-1]', "" );
			$tpl->set( '[/comments-rating-type-1]', "" );
			$tpl->set_block( "'\\[comments-rating-type-3\\](.*?)\\[/comments-rating-type-3\\]'si", "" );
			$tpl->set_block( "'\\[comments-rating-type-2\\](.*?)\\[/comments-rating-type-2\\]'si", "" );	
	}
		
	$timezoneselect = "<select class=\"timezoneselect\" name=\"timezone\"><option value=\"\">{$lang['system_default']} {$langtimezones[$config['date_adjust']]}</option>\r\n";

	foreach ( $langtimezones as $value => $description ) {
		$timezoneselect .= "<option value=\"$value\"";
		if( $row['timezone'] == $value ) {
			$timezoneselect .= " selected ";
		}
		$timezoneselect .= ">$description</option>\n";
	}

	$timezoneselect .= "</select>";

	$tpl->set( '{timezones}', $timezoneselect );

	$tpl->set( '{editmail}', stripslashes( $row['email'] ) );
	$tpl->set( '{status}',  $user_group[$row['user_group']]['group_prefix'].$user_group[$row['user_group']]['group_name'].$user_group[$row['user_group']]['group_suffix'] );
	$tpl->set( '{rate}', userrating( $row['user_id'] ) );
	$tpl->set( '{commentsrate}', commentsuserrating( $row['user_id'] ) );
	$tpl->set( '{registration}', langdate( "j F Y H:i", $row['reg_date'] ) );
	$tpl->set( '{lastdate}', langdate( "j F Y H:i", $row['lastdate'] ) );
	
	if( $user_group[$row['user_group']]['icon'] ) $tpl->set( '{group-icon}', "<img src=\"" . $user_group[$row['user_group']]['icon'] . "\" alt=\"\" />" );
	else $tpl->set( '{group-icon}', "" );
	
	if( $is_logged and $user_group[$row['user_group']]['time_limit'] and ($member_id['user_id'] == $row['user_id'] or $member_id['user_group'] < 3) ) {
		
		$tpl->set_block( "'\\[time_limit\\](.*?)\\[/time_limit\\]'si", "\\1" );
		
		if( $row['time_limit'] ) {
			
			$tpl->set( '{time_limit}', langdate( "j F Y H:i", $row['time_limit'] ) );
		
		} else {
			
			$tpl->set( '{time_limit}', $lang['no_limit'] );
		
		}
	
	} else {
		
		$tpl->set_block( "'\\[time_limit\\](.*?)\\[/time_limit\\]'si", "" );
	
	}
	
	$tpl->set( '{ip}', $_IP );
	$tpl->set( '{allowed-ip}', stripslashes( str_replace( "|", "\n", $row['allowed_ip'] ) ) );
	$tpl->set( '{editinfo}', $parse->decodeBBCodes( $row['info'], false ) );
	
	if( $user_group[$row['user_group']]['allow_signature'] ) $tpl->set( '{editsignature}', $parse->decodeBBCodes( $row['signature'], false ) );
	else $tpl->set( '{editsignature}', $lang['sig_not_allowed'] );
	
	if( $row['comm_num'] ) {

		$tpl->set( '[comm-num]', "" );
		$tpl->set( '[/comm-num]', "" );
		$tpl->set( '{comm-num}', number_format($row['comm_num'], 0, ',', ' ') );
		$tpl->set( '{comments}', "<a href=\"$PHP_SELF?do=lastcomments&amp;userid=" . $row['user_id'] . "\">" . $lang['last_comm'] . "</a>" );
		$tpl->set_block( "'\\[not-comm-num\\](.*?)\\[/not-comm-num\\]'si", "" );
	
	} else {
		
		$tpl->set( '{comments}', $lang['last_comm'] );
		$tpl->set( '{comm-num}', 0 );
		$tpl->set_block( "'\\[comm-num\\](.*?)\\[/comm-num\\]'si", "" );
		$tpl->set( '[not-comm-num]', "" );
		$tpl->set( '[/not-comm-num]', "" );
	}
	
	if( $row['news_num'] ) {
		
		if( $config['allow_alt_url'] ) {
			
			$tpl->set( '{news}', "<a href=\"" . $config['http_home_url'] . "user/" . urlencode( $row['name'] ) . "/news/" . "\">" . $lang['all_user_news'] . "</a>" );
			$tpl->set( '[rss]', "<a href=\"" . $config['http_home_url'] . "user/" . urlencode( $row['name'] ) . "/rss.xml" . "\" title=\"" . $lang['rss_user'] . "\">" );
			$tpl->set( '[/rss]', "</a>" );
		
		} else {
			
			$tpl->set( '{news}', "<a href=\"" . $PHP_SELF . "?subaction=allnews&amp;user=" . urlencode( $row['name'] ) . "\">" . $lang['all_user_news'] . "</a>" );
			$tpl->set( '[rss]', "<a href=\"engine/rss.php?subaction=allnews&amp;user=" . urlencode( $row['name'] ) . "\" title=\"" . $lang['rss_user'] . "\">" );
			$tpl->set( '[/rss]', "</a>" );
		}

		$tpl->set( '{news-num}', number_format($row['news_num'], 0, ',', ' ') );
		$tpl->set( '[news-num]', "" );
		$tpl->set( '[/news-num]', "" );
		$tpl->set_block( "'\\[not-news-num\\](.*?)\\[/not-news-num\\]'si", "" );

	} else {
		
		$tpl->set( '{news}', $lang['all_user_news'] );
		$tpl->set_block( "'\\[rss\\](.*?)\\[/rss\\]'si", "" );
		$tpl->set( '{news-num}', 0 );
		$tpl->set_block( "'\\[news-num\\](.*?)\\[/news-num\\]'si", "" );
		$tpl->set( '[not-news-num]', "" );
		$tpl->set( '[/not-news-num]', "" );
	}
	
	if( $row['signature'] and $user_group[$row['user_group']]['allow_signature'] ) {
		
		$tpl->set_block( "'\\[signature\\](.*?)\\[/signature\\]'si", "\\1" );
		$tpl->set( '{signature}', stripslashes( $row['signature'] ) );
	
	} else {
		
		$tpl->set_block( "'\\[signature\\](.*?)\\[/signature\\]'si", "" );
		$tpl->set( '{signature}', "" );	
	}
	
	$xfieldsaction = "list";
	$xfieldsadd = false;
	$xfieldsid = $row['xfields'];
	include (ENGINE_DIR . '/inc/userfields.php');
	$tpl->set( '{xfields}', $output );

	if ( count( $xfieldinput ) ) {
		foreach ( $xfieldinput as $key => $value ) {
			$tpl->copy_template = str_replace( "[xfinput_{$key}]", $value, $tpl->copy_template );
		}		
	}
	
	// Обработка дополнительных полей
	$xfieldsdata = xfieldsdataload( $row['xfields'] );
	
	foreach ( $xfields as $value ) {

		$preg_safe_name = preg_quote( $value[0], "'" );
		
		if( $value[5] != 1 OR ($is_logged AND $member_id['user_group'] == 1) OR ($is_logged AND $member_id['user_id'] == $row['user_id']) ) {

			if( empty( $xfieldsdata[$value[0]] ) ) {

				$tpl->copy_template = preg_replace( "'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template );
				$tpl->copy_template = str_replace( "[xfnotgiven_{$value[0]}]", "", $tpl->copy_template );
				$tpl->copy_template = str_replace( "[/xfnotgiven_{$value[0]}]", "", $tpl->copy_template );

			} else {

				$tpl->copy_template = preg_replace( "'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template );
				$tpl->copy_template = str_replace( "[xfgiven_{$value[0]}]", "", $tpl->copy_template );
				$tpl->copy_template = str_replace( "[/xfgiven_{$value[0]}]", "", $tpl->copy_template );

			}

			$tpl->copy_template = preg_replace( "'\\[xfvalue_{$preg_safe_name}\\]'i", stripslashes( $xfieldsdata[$value[0]] ), $tpl->copy_template );

		} else {

			$tpl->copy_template = preg_replace( "'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template );
			$tpl->copy_template = preg_replace( "'\\[xfvalue_{$preg_safe_name}\\]'i", "", $tpl->copy_template );
			$tpl->copy_template = preg_replace( "'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template );

		}

	}
	// Обработка дополнительных полей
	

	if( $is_logged and ($member_id['user_id'] == $row['user_id'] OR $member_id['user_group'] == 1) ) {
		$tpl->set( '{edituser}', "<a href=\"javascript:ShowOrHide('options')\">" . $lang['news_option'] . "</a>" );
		$tpl->set( '[not-logged]', "" );
		$tpl->set( '[/not-logged]', "" );

		$ignore_list = array();
		$temp_result = $db->query( "SELECT * FROM " . USERPREFIX . "_ignore_list WHERE user='{$row['user_id']}'" );
		while ( $temp_row = $db->get_row( $temp_result ) ) {

			if( $config['allow_alt_url'] ) {
				
				$user_name = $config['http_home_url'] . "user/" . urlencode( $temp_row['user_from'] ) . "/";
				$user_name = "onclick=\"ShowProfile('" . urlencode( $temp_row['user_from'] ) . "', '" . htmlspecialchars( $user_name, ENT_QUOTES, $config['charset'] ) . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\"";
				$user_name = "<a {$user_name} class=\"pm_list\" href=\"" . $config['http_home_url'] . "user/" . urlencode( $temp_row['user_from'] ) . "/\">" . $temp_row['user_from'] . "</a>";
			
			} else {
				
				$user_name = "$PHP_SELF?subaction=userinfo&amp;user=" . urlencode( $temp_row['user_from'] );
				$user_name = "onclick=\"ShowProfile('" . urlencode( $temp_row['user_from'] ) . "', '" . htmlspecialchars( $user_name, ENT_QUOTES, $config['charset'] ) . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\"";
				$user_name = "<a {$user_name} class=\"pm_list\" href=\"$PHP_SELF?subaction=userinfo&amp;user=" . urlencode( $temp_row['user_from'] ) . "\">" . $temp_row['user_from'] . "</a>";
	
			}

			$ignore_list[] = "<span id=\"dle-ignore-list-{$temp_row['id']}\">{$user_name}&nbsp;<a title=\"{$lang['del_from_ignore_1']}\" href=\"javascript:DelIgnorePM('" . $temp_row['id'] . "', '" . $lang['del_from_ignore'] . "')\"><img style=\"vertical-align: middle;border:none;\" alt=\"\" src=\"{THEME}/dleimages/delete.png\" /></a>";
		}
		$db->free( $temp_result );
		if (count($ignore_list)) $tpl->set( '{ignore-list}', implode(",&nbsp;</span>", $ignore_list)."</span>" ); else $tpl->set( '{ignore-list}', "" );

	} else {
		$tpl->set( '{edituser}', "" );
		$tpl->set( '{ignore-list}', "" );
		$tpl->set_block( "'\\[not-logged\\](.*?)\\[/not-logged\\]'si", "<!-- profile -->" );
	}
	
	if( $config['allow_alt_url'] ) $link_profile = $config['http_home_url'] . "user/" . urlencode( $row['name'] ) . "/";
	else $link_profile = $PHP_SELF . "?subaction=userinfo&user=" . urlencode( $row['name'] );
	
	if( $is_logged and ($member_id['user_id'] == $row['user_id'] or $member_id['user_group'] == 1) ) {
		$tpl->copy_template = "<form  method=\"post\" name=\"userinfo\" id=\"userinfo\" enctype=\"multipart/form-data\" action=\"{$link_profile}\">" . $tpl->copy_template . "
		<input type=\"hidden\" name=\"doaction\" value=\"adduserinfo\" />
		<input type=\"hidden\" name=\"id\" value=\"{$row['user_id']}\" />
		<input type=\"hidden\" name=\"dle_allow_hash\" value=\"{$dle_login_hash}\" />
		</form>";
	}
	
	$tpl->compile( 'content' );

}

$tpl->clear();
$db->free( $sql_result );

if( $user_found == FALSE ) {
	$allow_active_news = false;
	@header( "HTTP/1.0 404 Not Found" );
	msgbox( $lang['all_err_1'], $lang['news_err_26'] );
}
?>