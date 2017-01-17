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
 Файл: options.php
-----------------------------------------------------
 Назначение: опции
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( isset( $_REQUEST['subaction'] ) ) $subaction = $_REQUEST['subaction']; else $subaction = "";

if( $action == "options" or $action == '' ) {
	echoheader( "<i class=\"icon-flag\"></i>".$lang['opt_all_rublik'], $lang['opt_all_rublikc'] );
	
	//----------------------------------
	// Список разделов
	//----------------------------------
	

	$options = array ();
	
	$options['config'] = array (
								
								array (
											'name' => $lang['opt_all'], 
											'url' => "?mod=options&action=syscon", 
											'descr' => $lang['opt_allc'], 
											'image' => "tools.png", 
											'access' => "admin" 
								), 
								
								array (
											'name' => $lang['opt_cat'], 
											'url' => "?mod=categories", 
											'descr' => $lang['opt_catc'], 
											'image' => "cats.png", 
											'access' => $user_group[$member_id['user_group']]['admin_categories'] 
								), 
								
								array (
											'name' => $lang['opt_db'], 
											'url' => "?mod=dboption", 
											'descr' => $lang['opt_dbc'], 
											'image' => "dbset.png", 
											'access' => "admin" 
								), 

								array (
											'name' => $lang['opt_vconf'], 
											'url' => "?mod=videoconfig", 
											'descr' => $lang['opt_vconfc'], 
											'image' => "video.png", 
											'access' => "admin" 
								),
								
								array (
											'name' => $lang['opt_xfil'], 
											'url' => "?mod=xfields&xfieldsaction=configure", 
											'descr' => $lang['opt_xfilc'], 
											'image' => "xfset.png", 
											'access' => $user_group[$member_id['user_group']]['admin_xfields'] 
								),

								array (
											'name' => $lang['opt_question'], 
											'url' => "?mod=question", 
											'descr' => $lang['opt_questionc'], 
											'image' => "question.png", 
											'access' => "admin" 
								)
	);
	
	$options['user'] = array (
							
							array (
										'name' => $lang['opt_priv'], 
										'url' => "?mod=options&action=personal", 
										'descr' => $lang['opt_privc'], 
										'image' => "pset.png", 
										'access' => "all" 
							), 
							
							array (
										'name' => $lang['opt_user'], 
										'url' => "?mod=editusers&action=list", 
										'descr' => $lang['opt_userc'], 
										'image' => "uset.png", 
										'access' => $user_group[$member_id['user_group']]['admin_editusers'] 
							), 
							
							array (
										'name' => $lang['opt_xprof'], 
										'url' => "?mod=userfields&xfieldsaction=configure", 
										'descr' => $lang['opt_xprofd'], 
										'image' => "xprof.png", 
										'access' => $user_group[$member_id['user_group']]['admin_userfields'] 
							), 
							
							array (
										'name' => $lang['opt_group'], 
										'url' => "?mod=usergroup", 
										'descr' => $lang['opt_groupc'], 
										'image' => "usersgroup.png", 
										'access' => "admin" 
							),

							array (
										'name' => $lang['opt_social'], 
										'url' => "?mod=social", 
										'descr' => $lang['opt_socialc'], 
										'image' => "social.png", 
										'access' => "admin" 
							)
	);
	
	$options['templates'] = array (
									
									array (
											'name' => $lang['opt_t'], 
											'url' => "?mod=templates&user_hash=" . $dle_login_hash, 
											'descr' => $lang['opt_tc'], 
											'image' => "tmpl.png", 
											'access' => "admin" 
									), 
									
									array (
											'name' => $lang['opt_email'], 
											'url' => "?mod=email", 
											'descr' => $lang['opt_emailc'], 
											'image' => "mset.png", 
											'access' => "admin" 
									) 
	);

	
	
	$options['filter'] = array (
								
								array (
											'name' => $lang['opt_fil'], 
											'url' => "?mod=wordfilter", 
											'descr' => $lang['opt_filc'], 
											'image' => "fset.png", 
											'access' => $user_group[$member_id['user_group']]['admin_wordfilter'] 
								), 
								
								array (
											'name' => $lang['opt_ipban'], 
											'url' => "?mod=blockip", 
											'descr' => $lang['opt_ipbanc'], 
											'image' => "blockip.png", 
											'access' => $user_group[$member_id['user_group']]['admin_blockip'] 
								), 
								
								array (
											'name' => $lang['opt_iptools'], 
											'url' => "?mod=iptools", 
											'descr' => $lang['opt_iptoolsc'], 
											'image' => "iptools.png", 
											'access' => $user_group[$member_id['user_group']]['admin_iptools'] 
								), 
								array (
											'name' => $lang['opt_sfind'], 
											'url' => "?mod=search", 
											'descr' => $lang['opt_sfindc'], 
											'image' => "find_base.png", 
											'access' => "admin" 
								),
								array (
											'name' => $lang['opt_srebuild'], 
											'url' => "?mod=rebuild", 
											'descr' => $lang['opt_srebuildc'], 
											'image' => "refresh.png", 
											'access' => "admin" 
								),
								array (
											'name' => $lang['opt_complaint'], 
											'url' => "?mod=complaint", 
											'descr' => $lang['opt_complaintc'], 
											'image' => "complaint.png", 
											'access' => $user_group[$member_id['user_group']]['admin_complaint'] 
								),
								array (
											'name' => $lang['opt_check'], 
											'url' => "?mod=check", 
											'descr' => $lang['opt_checkc'], 
											'image' => "check.png", 
											'access' => "admin" 
								),
								array (
											'name' => $lang['opt_links'], 
											'url' => "?mod=links", 
											'descr' => $lang['opt_linksc'], 
											'image' => "links.png", 
											'access' => "admin" 
								)
	);

	
	
	$options['others'] = array (
								array (
											'name' => $lang['opt_rules'], 
											'url' => "?mod=static&action=doedit&page=rules", 
											'descr' => $lang['opt_rulesc'], 
											'image' => "rules.png", 
											'access' => $user_group[$member_id['user_group']]['admin_static'] 
								), 
								
								array (
											'name' => $lang['opt_static'], 
											'url' => "?mod=static", 
											'descr' => $lang['opt_staticd'], 
											'image' => "spset.png", 
											'access' => $user_group[$member_id['user_group']]['admin_static'] 
								), 
								
								array (
											'name' => $lang['opt_clean'], 
											'url' => "?mod=clean", 
											'descr' => $lang['opt_cleanc'], 
											'image' => "clean.png", 
											'access' => "admin" 
								), 								
								
								array (
											'name' => $lang['main_newsl'], 
											'url' => "?mod=newsletter", 
											'descr' => $lang['main_newslc'], 
											'image' => "nset.png", 
											'access' => $user_group[$member_id['user_group']]['admin_newsletter'] 
								), 
								array (
											'name' => $lang['opt_vote'], 
											'url' => "?mod=editvote", 
											'descr' => $lang['opt_votec'], 
											'image' => "votes.png", 
											'access' => $user_group[$member_id['user_group']]['admin_editvote'] 
								), 
								
								array (
											'name' => $lang['opt_img'], 
											'url' => "?mod=files", 
											'descr' => $lang['opt_imgc'], 
											'image' => "iset.png", 
											'access' => "admin" 
								), 
								
								array (
											'name' => $lang['opt_banner'], 
											'url' => "?mod=banners&action=list", 
											'descr' => $lang['opt_bannerc'], 
											'image' => "rkl.png", 
											'access' => $user_group[$member_id['user_group']]['admin_banners'] 
								), 
								array (
											'name' => $lang['opt_google'], 
											'url' => "?mod=googlemap", 
											'descr' => $lang['opt_googlec'], 
											'image' => "googlemap.png", 
											'access' => $user_group[$member_id['user_group']]['admin_googlemap'] 
								),
								array (
											'name' => $lang['opt_rss'], 
											'url' => "?mod=rss", 
											'descr' => $lang['opt_rssc'], 
											'image' => "rss_import.png", 
											'access' => $user_group[$member_id['user_group']]['admin_rss'] 
								), 
								array (
											'name' => $lang['opt_rssinform'], 
											'url' => "?mod=rssinform", 
											'descr' => $lang['opt_rssinformc'], 
											'image' => "rss_inform.png", 
											'access' => $user_group[$member_id['user_group']]['admin_rssinform'] 
								),
								array (
											'name' => $lang['opt_tagscloud'], 
											'url' => "?mod=tagscloud", 
											'descr' => $lang['opt_tagscloudc'], 
											'image' => "admin_tagscloud.png", 
											'access' => $user_group[$member_id['user_group']]['admin_tagscloud'] 
								),

								array (
											'name' => $lang['opt_logs'], 
											'url' => "?mod=logs", 
											'descr' => $lang['opt_logsc'], 
											'image' => "admin_logs.png", 
											'access' => "admin" 
								),
	);

	
	foreach ( $options as $sub_options => $value ) {
		$count_options = count( $value );
		
		for($i = 0; $i < $count_options; $i ++) {

			if ($member_id['user_group'] == 1 ) continue;

			if ($member_id['user_group'] != 1 AND  $value[$i]['access'] == "admin") unset( $options[$sub_options][$i] );

			if ( !$value[$i]['access'] ) unset( $options[$sub_options][$i] );
		}
	}
	
	$subs = 0;
	
	foreach ( $options as $sub_options ) {
		
		if( $subs == 1 ) $lang['opt_hopt'] = $lang['opt_s_acc'];
		if( $subs == 2 ) $lang['opt_hopt'] = $lang['opt_s_tem'];
		if( $subs == 3 ) $lang['opt_hopt'] = $lang['opt_s_fil'];
		if( $subs == 4 ) $lang['opt_hopt'] = $lang['opt_s_oth'];
		
		$subs ++;
		
		if( ! count( $sub_options ) ) continue;
		
		echo <<<HTML
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['opt_hopt']}</div>
  </div>
  <div class="box-content">
	<div class="row box-section">
HTML;
		
		$i = 0;
		
		foreach ( $sub_options as $option ) {
			
			if( $i > 1 ) {
				echo "</div><div class=\"row box-section\">";
				$i = 0;
			}
			
			$i ++;

			echo <<<HTML
	  <div class="col-md-6">
		<div class="news with-icons">
		  <div class="avatar"><img src="engine/skins/images/{$option['image']}"></div>
		  <div class="news-content">
			<div class="news-title"><a href="{$option['url']}">{$option['name']}</a></div>
			<div class="news-text">
			  <a href="{$option['url']}">{$option['descr']}</a>
			</div>
		  </div>
		</div>
	  </div>
HTML;
			
		}
		
		echo <<<HTML
	</div>
  </div>
</div>
HTML;
	
	}

	$db->query( "SELECT * FROM " . PREFIX . "_admin_sections" );


	$i = 0;
	$sections = "";
		
	while ( $row = $db->get_array() ) {

		if ($row['allow_groups'] != "all") {

			$groups = explode(",", $row['allow_groups']);

			if ( !in_array($member_id['user_group'], $groups) AND $member_id['user_group'] !=1 ) continue;

		}
			
		if( $i > 1 ) {
			$sections .= "</div><div class=\"row box-section\">";
			$i = 0;
		}
			
		$i ++;

		$row['name'] = totranslit($row['name'], true, false);
		$row['icon'] = totranslit($row['icon'], false, true);

		if ( !$row['icon'] OR !@file_exists( ENGINE_DIR . "/skins/images/{$row['icon']}")) $row['icon'] = "default_icon.png";

		$row['title'] = strip_tags(stripslashes($row['title']));
		$row['descr'] = strip_tags(stripslashes($row['descr']));

		if ($member_id['user_group'] == 1) $del_link = "&nbsp;&nbsp;<a href=\"#\" onclick=\"del_mod('{$row['id']}'); return false;\" class=\"status-error\"><i class=\"icon-trash\"></i></a>"; else $del_link = "";

			$sections .= <<<HTML
	  <div class="col-md-6">
		<div class="news with-icons">
		  <div class="avatar"><img src="engine/skins/images/{$row['icon']}"></div>
		  <div class="news-content">
			<div class="news-title"><a href="?mod={$row['name']}">{$row['title']}</a></div>
			<div class="news-text">
			  <a href="?mod={$row['name']}">{$row['descr']}</a>{$del_link}
			</div>
		  </div>
		</div>
	  </div>
HTML;

	}

	if ( $sections ) {


		echo <<<HTML
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['admin_other_section']}</div>
  </div>
  <div class="box-content">
	<div class="row box-section">
{$sections}
	</div>
  </div>
</div>
HTML;


		if ($member_id['user_group'] ==1) {

		echo <<<HTML
<script type="text/javascript">
<!--
function del_mod ( id ){

    DLEconfirm( '{$lang['confirm_del_mod']}', '{$lang['p_confirm']}', function () {

		$.get("engine/ajax/adminfunction.php?action=deletemodules", { id: id }, function( data ){
			if (data == 'ok') { document.location.reload(false); }
		});
	} );

	return false;
}
//-->
</script>
HTML;

		}

	}

	echofooter();
} 

// ********************************************************************************
// Редактирование персональной информации
// ********************************************************************************
elseif( $action == "personal" ) {
	echoheader( "<i class=\"icon-user\"></i>".$lang['opt_priv'], $lang['opt_priv'] );
	
	$registrationdate = langdate( "l, j F Y - H:i", $member_id['reg_date'] ); //registration date
	if( $member_id['allow_mail'] == 0 ) $ifchecked = "checked";
	else $ifchecked = ""; //if user wants to hide his e-mail
	

	foreach ( $member_id as $key => $value ) {
		$member_id[$key] = stripslashes( preg_replace( array (
																"'\"'", 
																"'\''" 
		), array (
					"&quot;", 
					"&#039;" 
		), $member_id[$key] ) );
	}

	include_once ENGINE_DIR . '/classes/parse.class.php';
	
	$parse = new ParseFilter( );
	$parse->safe_mode = true;


	$xfieldsaction = "list";
	$xfieldsadd = false;
	$adminmode = true;
	$is_logged = true;
	$xfieldsid = $member_id['xfields'];
	include (ENGINE_DIR . '/inc/userfields.php');

	$timezoneselect = "<select class=\"uniform\" name=\"timezone\"><option value=\"\">{$lang['system_default']}</option>\r\n";

	foreach ( $langtimezones as $value => $description ) {
		$timezoneselect .= "<option value=\"$value\"";
		if( $member_id['timezone'] == $value ) {
			$timezoneselect .= " selected ";
		}
		$timezoneselect .= ">$description</option>\n";
	}

	$timezoneselect .= "</select>";
	
	echo <<<HTML
<form method="post" action="" name="personal" class="form-horizontal">
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['opt_hprv']}</div>
  </div>
  <div class="box-content">

	<div class="row box-section">
		<div class="form-group">
		  <label class="col-lg-2">{$lang['user_name']}</label>
		  <div class="col-lg-10">
			{$member_id['name']}
		  </div>
		 </div>
		<div class="form-group">
		  <label class="col-lg-2">{$lang['user_acc']}</label>
		  <div class="col-lg-10">
			{$user_group[$member_id['user_group']]['group_name']}
		  </div>
		 </div>
		<div class="form-group">
		  <label class="col-lg-2">{$lang['user_news']}</label>
		  <div class="col-lg-10">
			{$member_id['news_num']}
		  </div>
		 </div>
		<div class="form-group">
		  <label class="col-lg-2">{$lang['user_reg']}</label>
		  <div class="col-lg-10">
			{$registrationdate}
		  </div>
		 </div>

		<div class="form-group">
		  <label class="col-lg-2">{$lang['user_timezone']}</label>
		  <div class="col-lg-10">
			{$timezoneselect}
		  </div>
		 </div>

		<div class="form-group">
		  <label class="col-lg-2">{$lang['user_mail']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:350px;" type="text" name="editmail" value="{$member_id['email']}">&nbsp;&nbsp;&nbsp;<input type="checkbox" name="edithidemail" {$ifchecked} id="edithidemail" value="1" />&nbsp;<label for="edithidemail">{$lang['opt_hmail']}</label>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="col-lg-2">{$lang['opt_fullname']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:350px;" type="text" name="editfullname" value="{$member_id['fullname']}" >
		  </div>
		 </div>
		<div class="form-group">
		  <label class="col-lg-2">{$lang['opt_land']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:350px;" type="text" name="editland" value="{$member_id['land']}">
		  </div>
		 </div>
		<div class="form-group">
		  <label class="col-lg-2">{$lang['opt_altpassw']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:350px;" name="altpass" type="password">&nbsp;<span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{$lang['hint_pass']}" >?</span>
		  </div>
		 </div>
		<div class="form-group">
		  <label class="col-lg-2">{$lang['user_newpass']}</label>
		  <div class="col-lg-10">
			<input style="width:100%;max-width:350px;" type="text" name="editpassword">
		  </div>
		 </div>
		{$output}
	</div>
	<div class="row box-section">
	<input type="submit" class="btn btn-green" value="{$lang['user_save']}">
	</div>	
   </div>
</div>
<input type="hidden" name="mod" value="options">
<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
<input type="hidden" name="action" value="dosavepersonal">
</form>
HTML;
	
	echofooter();
} 
// ********************************************************************************
// Запись персональной информации
// ********************************************************************************
elseif( $action == "dosavepersonal" ) {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$not_allow_symbol = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", "/", "¬", "#", ";", ":", "~", "[", "]", "{", "}", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"', "'" );

	include_once ENGINE_DIR . '/classes/parse.class.php';
	
	$parse = new ParseFilter( );
	$parse->safe_mode = true;
	$parse->allow_url = $user_group[$member_id['user_group']]['allow_url'];
	$parse->allow_image = $user_group[$member_id['user_group']]['allow_image'];

	$xfieldsaction = "init";
	$xfieldsadd = false;
	$xfieldsid = $member_id['xfields'];
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

	if( $parse->not_allowed_tags ) {

			msg( "error", $lang['user_err'], $lang['user_err_5'], "?mod=options&action=personal" );			

	}
	
	$editpassword = $_POST['editpassword'];

	$editmail = $db->safesql(trim( str_replace( $not_allow_symbol, '', strip_tags( stripslashes( $_POST['editmail'] ) ) ) ) );


	if( empty( $editmail ) OR strlen( $editmail ) > 50 OR @count(explode("@", $editmail)) != 2 ) {
		
		msg( "error", $lang['user_err'], "E-mail not correct", "?mod=options&action=personal" );
	}

	$editfullname = $db->safesql( $_POST['editfullname'] );

	if ( preg_match( "/[\||\'|\<|\>|\"|\!|\]|\?|\$|\@|\/|\\\|\&\~\*\+]/", $editfullname ) ) {

		$editfullname = "";
	}

	$editland = $db->safesql( $_POST['editland'] );

	if ( preg_match( "/[\||\'|\<|\>|\"|\!|\]|\?|\$|\@|\/|\\\|\&\~\*\+]/", $editland ) ) {

		$editland = "";
	}

	$timezones = array('Pacific/Midway','US/Samoa','US/Hawaii','US/Alaska','US/Pacific','America/Tijuana','US/Arizona','US/Mountain','America/Chihuahua','America/Mazatlan','America/Mexico_City','America/Monterrey','US/Central','US/Eastern','US/East-Indiana','America/Lima','America/Caracas','Canada/Atlantic','America/La_Paz','America/Santiago','Canada/Newfoundland','America/Buenos_Aires','Greenland','Atlantic/Stanley','Atlantic/Azores','Africa/Casablanca','Europe/Dublin','Europe/Lisbon','Europe/London','Europe/Amsterdam','Europe/Belgrade','Europe/Berlin','Europe/Bratislava','Europe/Brussels','Europe/Budapest','Europe/Copenhagen','Europe/Madrid','Europe/Paris','Europe/Prague','Europe/Rome','Europe/Sarajevo','Europe/Stockholm','Europe/Vienna','Europe/Warsaw','Europe/Zagreb','Europe/Athens','Europe/Bucharest','Europe/Helsinki','Europe/Istanbul','Asia/Jerusalem','Europe/Kiev','Europe/Minsk','Europe/Riga','Europe/Sofia','Europe/Tallinn','Europe/Vilnius','Asia/Baghdad','Asia/Kuwait','Africa/Nairobi','Asia/Tehran','Europe/Kaliningrad','Europe/Moscow','Europe/Volgograd','Europe/Samara','Asia/Baku','Asia/Muscat','Asia/Tbilisi','Asia/Yerevan','Asia/Kabul','Asia/Yekaterinburg','Asia/Tashkent','Asia/Kolkata','Asia/Kathmandu','Asia/Almaty','Asia/Novosibirsk','Asia/Jakarta','Asia/Krasnoyarsk','Asia/Hong_Kong','Asia/Kuala_Lumpur','Asia/Singapore','Asia/Taipei','Asia/Ulaanbaatar','Asia/Urumqi','Asia/Irkutsk','Asia/Seoul','Asia/Tokyo','Australia/Adelaide','Australia/Darwin','Asia/Yakutsk','Australia/Brisbane','Pacific/Port_Moresby','Australia/Sydney','Asia/Vladivostok','Asia/Sakhalin','Asia/Magadan','Pacific/Auckland','Pacific/Fiji');
	$timezone = $db->safesql( (string)$_POST['timezone'] );		

	if (!in_array($timezone, $timezones)) $timezone = '';


	$db->query( "SELECT name FROM " . USERPREFIX . "_users WHERE email = '{$editmail}' AND user_id != '{$member_id['user_id']}'" );

	if( $db->num_rows() ) {
			msg( "error", $lang['user_err'], $lang['user_err_4'], "?mod=options&action=personal" );
	}


	$altpass = md5( $_POST['altpass'] );
	
	if( $_POST['edithidemail'] ) {
		$edithidemail = 0;
	} else {
		$edithidemail = 1;
	}
	
	if( $editpassword != "" ) {
		
		if( $altpass == $cmd5_password ) {
			$editpassword = md5( md5( $editpassword ) );
			$sql_update = "UPDATE " . USERPREFIX . "_users SET email='$editmail', fullname='$editfullname', land='$editland', password='$editpassword', allow_mail='$edithidemail', xfields='$filecontents', timezone='$timezone' WHERE user_id='{$member_id['user_id']}'";
		
		} else
			msg( "error", $lang['user_err'], $lang['opt_errpass'], "?mod=options&action=personal" );
	
	} else {
		
		$sql_update = "UPDATE " . USERPREFIX . "_users set email='$editmail', fullname='$editfullname', land='$editland', allow_mail='$edithidemail', xfields='$filecontents', timezone='$timezone' WHERE user_id='{$member_id['user_id']}'";
	}
	
	$db->query( $sql_update );
	
	msg( "info", $lang['user_editok'], $lang['opt_peok'], "?mod=options&action=personal" );
} 
// ********************************************************************************
// Настройки скрипта
// ********************************************************************************
elseif( $action == "syscon" ) {

	if( $member_id['user_group'] != 1 ) {
		msg( "error", $lang['opt_denied'], $lang['opt_denied'] );
	}
	
	include_once ENGINE_DIR . '/classes/parse.class.php';
	$parse = new ParseFilter( Array (), Array (), 1, 1 );
	
	$config['offline_reason'] = str_replace( '&quot;', '"', $config['offline_reason'] );
	
	$config['offline_reason'] = $parse->decodeBBCodes( $config['offline_reason'], false );
	if( $auto_detect_config ) $config['http_home_url'] = "";

	$config['admin_allowed_ip'] = str_replace( "|", "\n", $config['admin_allowed_ip'] );

	$config['speedbar_separator'] = htmlspecialchars( $config['speedbar_separator'], ENT_QUOTES, $config['charset'] );
	$config['category_separator'] = htmlspecialchars( $config['category_separator'], ENT_QUOTES, $config['charset'] );

	echoheader( "<i class=\"icon-cogs\"></i>".$lang['opt_all'], $lang['opt_general_sys'] );
	
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
	
	if( ! $handle = opendir( "./templates" ) ) {
		die( "Невозможно открыть директорию ./templates" );
	}
	while ( false !== ($file = readdir( $handle )) ) {
		if( is_dir( ROOT_DIR . "/templates/$file" ) and ($file != "." and $file != "..") ) {
			$sys_con_skins_arr[$file] = $file;
		}
	}
	closedir( $handle );
	
	if( ! $handle = opendir( "./language" ) ) {
		die( "Невозможно открыть директорию ./data/language/" );
	}
	while ( false !== ($file = readdir( $handle )) ) {
		if( is_dir( ROOT_DIR . "/language/$file" ) and ($file != "." and $file != "..") ) {
			$sys_con_langs_arr[$file] = $file;
		}
	}
	closedir( $handle );
	
	foreach ( $user_group as $group )
		$sys_group_arr[$group['id']] = $group['group_name'];
	
	echo <<<HTML
<script type="text/javascript">
<!--
        function ChangeOption(selectedOption) {

                document.getElementById('general').style.display = "none";
                document.getElementById('security').style.display = "none";
                document.getElementById('news').style.display = "none";
                document.getElementById('comments').style.display = "none";
                document.getElementById('optimisation').style.display = "none";
                document.getElementById('files').style.display = "none";
                document.getElementById('mail').style.display = "none";
                document.getElementById('users').style.display = "none";
                document.getElementById('imagesconf').style.display = "none";
                document.getElementById('rss').style.display = "none";
                document.getElementById('smartphone').style.display = "none";
                document.getElementById(selectedOption).style.display = "";

				$('#'+selectedOption).find(".iButton-icons-tab").iButton({
					labelOn: "<i class='icon-ok'></i>",
					labelOff: "<i class='icon-remove'></i>",
					handleWidth: 30
				});

       }
//-->
</script>
<div class="box">
  <div class="box-content">
	<div class="row box-section">
		<ul class="settingsb">
		 <li style="min-width:90px;"><a href="javascript:ChangeOption('general');" class="tip" title="{$lang['opt_allsys']}"><i class="icon-cog"></i><span>{$lang['opt_b_1']}</span></a></li>
		 <li style="min-width:90px;"><a href="javascript:ChangeOption('security');" class="tip" title="{$lang['opt_secrsys']}"><i class="icon-umbrella"></i><span>{$lang['opt_b_2']}</span></a></li>
		 <li style="min-width:90px;"><a href="javascript:ChangeOption('news');" class="tip" title="{$lang['opt_newssys']}"><i class="icon-file-alt"></i><span>{$lang['opt_b_3']}</span></a></li>
		 <li style="min-width:90px;"><a href="javascript:ChangeOption('comments');" class="tip" title="{$lang['opt_commsys']}"><i class="icon-pencil"></i><span>{$lang['opt_b_4']}</span></a></li>
		 <li style="min-width:90px;"><a href="javascript:ChangeOption('optimisation');" class="tip" title="{$lang['opt_dbsys']}"><i class="icon-bar-chart"></i><span>{$lang['opt_b_5']}</span></a></li>
		 <li style="min-width:90px;"><a href="javascript:ChangeOption('files');" class="tip" title="{$lang['opt_filesys']}"><i class="icon-upload-alt"></i><span>{$lang['opt_b_6']}</span></a></li>
		 <li style="min-width:90px;"><a href="javascript:ChangeOption('mail');" class="tip" title="{$lang['opt_sys_mail']}"><i class="icon-envelope"></i><span>{$lang['opt_b_7']}</span></a></li>
		 <li style="min-width:90px;"><a href="javascript:ChangeOption('users');" class="tip" title="{$lang['opt_usersys']}"><i class="icon-user"></i><span>{$lang['opt_b_8']}</span></a></li>
		 <li style="min-width:90px;"><a href="javascript:ChangeOption('imagesconf');" class="tip" title="{$lang['opt_imagesys']}"><i class="icon-picture"></i><span>{$lang['opt_b_9']}</span></a></li>
		 <li style="min-width:90px;"><a href="javascript:ChangeOption('smartphone');" class="tip" title="{$lang['opt_smartphone']}"><i class="icon-mobile-phone"></i><span>{$lang['opt_b_10']}</span></a></li>
		 <li style="min-width:90px;"><a href="javascript:ChangeOption('rss');" class="tip" title="{$lang['opt_rsssys']}"><i class="icon-rss"></i><span>RSS</span></a></li>
		</ul>
     </div>
   </div>
</div>
HTML;
	
	echo <<<HTML
<form action="" method="post">
HTML;
	
	echo <<<HTML
<div id="general" class="box">
  <div class="box-header">
    <div class="title">{$lang['opt_sys_all']}</div>
  </div>
  <div class="box-content">
  <table class="table table-normal">
HTML;
	
	showRow( $lang['opt_sys_ht'], $lang['opt_sys_htd'], "<input type=text style=\"width:100%;\" name=\"save_con[home_title]\" value=\"{$config['home_title']}\">", "white-line" );
	showRow( $lang['opt_sys_hu'], $lang['opt_sys_hud'], "<input type=text style=\"width:100%;\" name=\"save_con[http_home_url]\" value=\"{$config['http_home_url']}\">" );
	showRow( $lang['opt_sys_chars'], $lang['opt_sys_charsd'], "<input type=\"text\" style=\"width:100%;\" name=\"save_con[charset]\" value=\"{$config['charset']}\">" );
	showRow( $lang['opt_sys_descr'], $lang['opt_sys_descrd'], "<input type=\"text\" style=\"width:100%;\" name=\"save_con[description]\" value=\"{$config['description']}\">" );
	showRow( $lang['opt_sys_key'], $lang['opt_sys_keyd'], "<textarea style=\"width:100%;height:100px;\" name=\"save_con[keywords]\">{$config['keywords']}</textarea>" );
	showRow( $lang['opt_sys_short_name'], $lang['opt_sys_short_named'], "<input type=\"text\" style=\"width:100%;\" name=\"save_con[short_title]\" value=\"{$config['short_title']}\">" );
	showRow( $lang['opt_sys_sts'], $lang['opt_sys_stsd'], makeDropDown( array ("1" => $lang['opt_sys_sts1'], "2" => $lang['opt_sys_sts2'] , "3" => $lang['opt_sys_sts3']), "save_con[start_site]", "{$config['start_site']}" ) );
	showRow( $lang['opt_sys_at'], $lang['opt_sys_atd']." ".date ( "d.m.Y, H:i", time () ), makeDropDown( $langtimezones, "save_con[date_adjust]", "{$config['date_adjust']}" ) );
	showRow( $lang['opt_sys_dc'], $lang['opt_sys_dcd'], makeCheckBox( "save_con[allow_alt_url]", "{$config['allow_alt_url']}" ) );
	showRow( $lang['opt_sys_seotype'], $lang['opt_sys_seotyped'], makeDropDown( array ("1" => $lang['opt_sys_seo_1'], "2" => $lang['opt_sys_seo_2'], "0" => $lang['opt_sys_seo_3'] ), "save_con[seo_type]", "{$config['seo_type']}" ) );
	showRow( $lang['opt_sys_seoc'], $lang['opt_sys_seocd'], makeCheckBox( "save_con[seo_control]", "{$config['seo_control']}" ) );
	showRow( $lang['opt_sys_al'], $lang['opt_sys_ald'], makeDropDown( $sys_con_langs_arr, "save_con[langs]", "{$config['langs']}" ) );
	showRow( $lang['opt_sys_as'], $lang['opt_sys_asd'], makeDropDown( $sys_con_skins_arr, "save_con[skin]", "{$config['skin']}" ) );
	showRow( $lang['opt_sys_wda'], $lang['opt_sys_wdad'], makeDropDown( array ("0" => $lang['editor_def'], "1" => "LiveEditor (WYSIWYG)", "2" => "TinyMCE (WYSIWYG)"), "save_con[allow_admin_wysiwyg]", "{$config['allow_admin_wysiwyg']}" ) );
	showRow( $lang['opt_sys_wdst'], $lang['opt_sys_wdasd'], makeDropDown( array ("0" => $lang['editor_def'], "1" => "LiveEditor (WYSIWYG)", "2" => "TinyMCE (WYSIWYG)" ), "save_con[allow_static_wysiwyg]", "{$config['allow_static_wysiwyg']}" ) );
	showRow( $lang['opt_sys_smc'], $lang['opt_sys_smcd'], makeCheckBox( "save_con[allow_complaint_mail]", "{$config['allow_complaint_mail']}" ) );
	showRow( $lang['opt_sys_offline'], $lang['opt_sys_offlined'], makeCheckBox( "save_con[site_offline]", "{$config['site_offline']}" ) );
	showRow( $lang['opt_sys_reason'], $lang['opt_sys_reasond'], "<textarea style=\"width:100%;height:150px;\" name=\"save_con[offline_reason]\">{$config['offline_reason']}</textarea>" );
	
	echo "</table></div></div>";
	
	echo <<<HTML
<div id="security" class="box" style='display:none'>
  <div class="box-header">
    <div class="title">{$lang['opt_secrsys']}</div>
  </div>
  <div class="box-content">
  <table class="table table-normal">
HTML;
	
	showRow( $lang['opt_sys_path'], $lang['opt_sys_pathd'], "<input type=\"text\" name=\"save_con[admin_path]\" value=\"{$config['admin_path']}\" size=50>", "white-line" );
	showRow( $lang['opt_sys_logextra'], $lang['opt_sys_logextrad'], makeDropDown( array ("0" => $lang['opt_sys_stdm'], "1" => $lang['opt_sys_extram'] ), "save_con[extra_login]", "{$config['extra_login']}" ) );
	showRow( $lang['opt_sys_iprest'], $lang['opt_sys_iprestd'], "<textarea style=\"width:100%;height:100px;\" name=\"save_con[admin_allowed_ip]\">{$config['admin_allowed_ip']}</textarea>" );
	showRow( $lang['opt_sys_llog'], $lang['opt_sys_llogd'], "<input type=text style=\"text-align: center;\" name=\"save_con[login_log]\" value=\"{$config['login_log']}\" size=20>" );
	showRow( $lang['opt_sys_tban'], $lang['opt_sys_tband'], "<input type=text style=\"text-align: center;\" name=\"save_con[login_ban_timeout]\" value=\"{$config['login_ban_timeout']}\" size=20>" );
	showRow( $lang['opt_sys_ip'], $lang['opt_sys_ipd'], makeDropDown( array ("0" => $lang['opt_sys_ipn'], "1" => $lang['opt_sys_ipm'], "2" => $lang['opt_sys_iph'] ), "save_con[ip_control]", "{$config['ip_control']}" ) );
	showRow( $lang['opt_sys_loghash'], $lang['opt_sys_loghashd'], makeCheckBox( "save_con[log_hash]", "{$config['log_hash']}" ) );
	showRow( $lang['opt_sys_recapt'], $lang['opt_sys_recaptd'], makeDropDown( array ("0" => $lang['opt_sys_gd2'], "1" => $lang['opt_sys_recaptcha'] ), "save_con[allow_recaptcha]", "{$config['allow_recaptcha']}" ) );
	showRow( $lang['opt_sys_recaptpub'], $lang['opt_sys_recaptpubd'], "<input  type=text name=\"save_con[recaptcha_public_key]\" value=\"{$config['recaptcha_public_key']}\" size=50>" );
	showRow( $lang['opt_sys_recaptpriv'], $lang['opt_sys_recaptpubd'], "<input  type=text name=\"save_con[recaptcha_private_key]\" value=\"{$config['recaptcha_private_key']}\" size=50>" );
	showRow( $lang['opt_sys_recapttheme'], $lang['opt_sys_recaptthemed'], makeDropDown( array ("light" => "Light", "dark" => "Dark" ), "save_con[recaptcha_theme]", "{$config['recaptcha_theme']}" ) );
	showRow( $lang['opt_sys_mdl'], $lang['opt_sys_mdld'], "<input type=text style=\"text-align: center;\" name=\"save_con[adminlog_maxdays]\" value=\"{$config['adminlog_maxdays']}\" size=20>" );
	
	echo "</table></div></div>";
	
	echo <<<HTML
<div id="news" class="box" style='display:none'>
  <div class="box-header">
    <div class="title">{$lang['opt_newssys']}</div>
  </div>
  <div class="box-content">
  <table class="table table-normal">
HTML;
	
	showRow( $lang['opt_sys_newc'], $lang['opt_sys_newd'], "<input type=text style=\"text-align: center;\"  name=\"save_con[news_number]\" value=\"{$config['news_number']}\" size=20>", "white-line" );
	showRow( $lang['opt_sys_snumc'], $lang['opt_sys_snumd'], "<input type=text style=\"text-align: center;\"  name=\"save_con[search_number]\" value=\"{$config['search_number']}\" size=20>" );
	showRow( $lang['opt_sys_findr'], $lang['opt_sys_findrd'], "<input type=text style=\"text-align: center;\"  name=\"save_con[search_pages]\" value=\"{$config['search_pages']}\" size=20>" );

	showRow( $lang['opt_sys_related_num'], $lang['opt_sys_related_numd'], "<input type=text style=\"text-align: center;\"  name=\"save_con[related_number]\" value=\"{$config['related_number']}\" size=20>" );
	showRow( $lang['opt_sys_top_num'], $lang['opt_sys_top_numd'], "<input type=text style=\"text-align: center;\"  name=\"save_con[top_number]\" value=\"{$config['top_number']}\" size=20>" );
	showRow( $lang['opt_sys_cloud_num'], $lang['opt_sys_cloud_numd'], "<input  type=text style=\"text-align: center;\"  name=\"save_con[tags_number]\" value=\"{$config['tags_number']}\" size=20>" );
	showRow( $lang['opt_sys_max_mod'], $lang['opt_sys_max_modd'], "<input  type=text style=\"text-align: center;\"  name=\"save_con[max_moderation]\" value=\"{$config['max_moderation']}\" size=20>" );
	showRow( $lang['group_n_restr'], $lang['group_n_restrd'], "<input  type=text style=\"text-align: center;\"  name=\"save_con[news_restricted]\" value=\"{$config['news_restricted']}\" size=20>" );
	showRow( $lang['opt_sys_cls'], $lang['opt_sys_clsd'], "<input  type=text style=\"text-align: center;\"  name=\"save_con[category_separator]\" value=\"{$config['category_separator']}\" size=20>" );
	showRow( $lang['opt_sys_spbs'], $lang['opt_sys_spbsd'], "<input  type=text style=\"text-align: center;\"  name=\"save_con[speedbar_separator]\" value=\"{$config['speedbar_separator']}\" size=20>" );
	showRow( $lang['opt_sys_am'], $lang['opt_sys_amd'], "<input  type=text style=\"width:100%;\"  name=\"save_con[smilies]\" value=\"{$config['smilies']}\" >" );
	showRow( $lang['opt_sys_an'], "<a onclick=\"javascript:Help('date'); return false;\" class=main href=\"#\">$lang[opt_sys_and]</a>", "<input  type=text name=\"save_con[timestamp_active]\" value=\"{$config['timestamp_active']}\" size=40>" );
	showRow( $lang['opt_sys_navi'], $lang['opt_sys_navid'], makeDropDown( array ("0" => $lang['opt_sys_navi_1'], "1" => $lang['opt_sys_navi_2'], "2" => $lang['opt_sys_navi_3'], "3" => $lang['opt_sys_navi_4'] ), "save_con[news_navigation]", "{$config['news_navigation']}" ) );
	showRow( $lang['opt_sys_sort'], $lang['opt_sys_sortd'], makeDropDown( array ("date" => $lang['opt_sys_sdate'], "rating" => $lang['opt_sys_srate'], "news_read" => $lang['opt_sys_sview'], "title" => $lang['opt_sys_salph'] ), "save_con[news_sort]", "{$config['news_sort']}" ) );
	showRow( $lang['opt_sys_msort'], $lang['opt_sys_msortd'], makeDropDown( array ("DESC" => $lang['opt_sys_mminus'], "ASC" => $lang['opt_sys_mplus'] ), "save_con[news_msort]", "{$config['news_msort']}" ) );
	showRow( $lang['opt_sys_catsort'], $lang['opt_sys_catsortd'], makeDropDown( array ("date" => $lang['opt_sys_sdate'], "rating" => $lang['opt_sys_srate'], "news_read" => $lang['opt_sys_sview'], "title" => $lang['opt_sys_salph'] ), "save_con[catalog_sort]", "{$config['catalog_sort']}" ) );
	showRow( $lang['opt_sys_catmsort'], $lang['opt_sys_catmsortd'], makeDropDown( array ("DESC" => $lang['opt_sys_mminus'], "ASC" => $lang['opt_sys_mplus'] ), "save_con[catalog_msort]", "{$config['catalog_msort']}" ) );
	showRow( $lang['opt_sys_align'], $lang['opt_sys_alignd'], makeDropDown( array ("" => $lang['opt_sys_none'], "left" => $lang['opt_sys_left'], "center" => $lang['opt_sys_center'], "right" => $lang['opt_sys_right'] ), "save_con[image_align]", "{$config['image_align']}" ) );
	showRow( $lang['opt_sys_nfut'], $lang['opt_sys_nfutd'], makeCheckBox( "save_con[news_future]", "{$config['news_future']}" ) );
	showRow( $lang['opt_sys_amet'], $lang['opt_sys_ametd'], makeCheckBox( "save_con[create_metatags]", "{$config['create_metatags']}" ) );
	showRow( $lang['opt_sys_acat'], $lang['opt_sys_acatd'], makeCheckBox( "save_con[create_catalog]", "{$config['create_catalog']}" ) );
	showRow( $lang['opt_sys_plink'], $lang['opt_sys_plinkd'], makeCheckBox( "save_con[parse_links]", "{$config['parse_links']}" ) );
	showRow( $lang['opt_sys_nmail'], $lang['opt_sys_nmaild'], makeCheckBox( "save_con[mail_news]", "{$config['mail_news']}" ) );
	showRow( $lang['opt_sys_sub'], $lang['opt_sys_subd'], makeCheckBox( "save_con[show_sub_cats]", "{$config['show_sub_cats']}" ) );
	showRow( $lang['opt_sys_ad'], $lang['opt_sys_add'], makeCheckBox( "save_con[hide_full_link]", "{$config['hide_full_link']}" ) );
	showRow( $lang['opt_sys_asp'], $lang['opt_sys_aspd'], makeCheckBox( "save_con[allow_search_print]", "{$config['allow_search_print']}" ) );
	showRow( $lang['opt_sys_adt'], $lang['opt_sys_adtd'], makeCheckBox( "save_con[allow_add_tags]", "{$config['allow_add_tags']}" ) );
	showRow( $lang['opt_sys_mhs'], $lang['opt_sys_mhsd'], makeCheckBox( "save_con[allow_share]", "{$config['allow_share']}" ) );
	showRow( $lang['opt_sys_rfc'], $lang['opt_sys_rfcd'], makeCheckBox( "save_con[related_only_cats]", "{$config['related_only_cats']}" ) );
	showRow( $lang['opt_sys_asrate'], $lang['opt_sys_asrated'], makeCheckBox( "save_con[short_rating]", "{$config['short_rating']}" ) );
	showRow( $lang['opt_sys_rtp'], $lang['opt_sys_rtpd'], makeDropDown( array ("0" => $lang['opt_sys_rtp_1'], "1" => $lang['opt_sys_rtp_2'], "2" => $lang['opt_sys_rtp_3']), "save_con[rating_type]", "{$config['rating_type']}" ) );
	showRow( $lang['opt_sys_wds'], $lang['opt_sys_wdsd'], makeDropDown( array ("0" => $lang['editor_def'], "1" => "LiveEditor (WYSIWYG)", "2" => "TinyMCE (WYSIWYG)"), "save_con[allow_site_wysiwyg]", "{$config['allow_site_wysiwyg']}" ) );
	showRow( $lang['opt_sys_wdq'], $lang['opt_sys_wdsd1'], makeDropDown( array ("0" => $lang['editor_def'], "1" => "LiveEditor (WYSIWYG)", "2" => "TinyMCE (WYSIWYG)"), "save_con[allow_quick_wysiwyg]", "{$config['allow_quick_wysiwyg']}" ) );
	
	echo "</table></div></div>";
	
	echo <<<HTML
<div id="comments" class="box" style='display:none'>
  <div class="box-header">
    <div class="title">{$lang['opt_sys_cch']}</div>
  </div>
  <div class="box-content">
  <table class="table table-normal">
HTML;
	showRow( $lang['opt_sys_alc'], $lang['opt_sys_alcd'], makeCheckBox( "save_con[allow_comments]", "{$config['allow_comments']}" ), "white-line" );
	showRow( $lang['opt_sys_trc'], $lang['opt_sys_trcd'], makeCheckBox( "save_con[tree_comments]", "{$config['tree_comments']}" ) );
	showRow( $lang['opt_sys_trcl'], $lang['opt_sys_trcld'], "<input  type=text style=\"text-align: center;\"  name=\"save_con[tree_comments_level]\" value=\"{$config['tree_comments_level']}\" size=20>" );
	showRow( $lang['opt_sys_trcf'], $lang['opt_sys_trcfd'], makeCheckBox( "save_con[simple_reply]", "{$config['simple_reply']}" ) );
	showRow( $lang['group_c_restr'], $lang['group_c_restrd'], "<input  type=text style=\"text-align: center;\"  name=\"save_con[comments_restricted]\" value=\"{$config['comments_restricted']}\" size=20>" );
	showRow( $lang['opt_sys_subs'], $lang['opt_sys_subsd'], makeCheckBox( "save_con[allow_subscribe]", "{$config['allow_subscribe']}" ) );
	showRow( $lang['opt_sys_comb'], $lang['opt_sys_combd'], makeCheckBox( "save_con[allow_combine]", "{$config['allow_combine']}" ) );
	showRow( $lang['opt_sys_mcommd'], $lang['opt_sys_mcommdd'], "<input  type=text style=\"text-align: center;\"  name='save_con[max_comments_days]' value=\"{$config['max_comments_days']}\" size=20>" );
	showRow( $lang['opt_sys_minc'], $lang['opt_sys_mincd'], "<input  type=text style=\"text-align: center;\"  name='save_con[comments_minlen]' value=\"{$config['comments_minlen']}\" size=20>" );
	showRow( $lang['opt_sys_maxc'], $lang['opt_sys_maxcd'], "<input  type=text style=\"text-align: center;\"  name='save_con[comments_maxlen]' value=\"{$config['comments_maxlen']}\" size=20>" );
	showRow( $lang['opt_sys_cpm'], $lang['opt_sys_cpmd'], "<input  type=text style=\"text-align: center;\"  name='save_con[comm_nummers]' value=\"{$config['comm_nummers']}\" size=20>" );
	showRow( $lang['opt_sys_clazy'], $lang['opt_sys_clazyd'], makeCheckBox( "save_con[comments_lazyload]", "{$config['comments_lazyload']}" ) );
	showRow( $lang['opt_sys_csort'], $lang['opt_sys_csortd'], makeDropDown( array ("DESC" => $lang['opt_sys_mminus'], "ASC" => $lang['opt_sys_mplus'] ), "save_con[comm_msort]", "{$config['comm_msort']}" ) );
	showRow( $lang['opt_sys_af'], $lang['opt_sys_afd'], "<input  type=text style=\"text-align: center;\"  name='save_con[flood_time]' value=\"{$config['flood_time']}\" size=20>" );
	showRow( $lang['opt_sys_aw'], $lang['opt_sys_awd'], "<input  type=text style=\"text-align: center;\"  name='save_con[auto_wrap]' value=\"{$config['auto_wrap']}\" size=20>" );
	showRow( $lang['opt_sys_ct'], "<a onClick=\"javascript:Help('date')\" class=main href=\"#\">$lang[opt_sys_and]</a>", "<input  type=text name='save_con[timestamp_comment]' value=\"{$config['timestamp_comment']}\" size=40>" );
	showRow( $lang['opt_sys_asc'], $lang['opt_sys_ascd'], makeCheckBox( "save_con[allow_search_link]", "{$config['allow_search_link']}" ) );
	showRow( $lang['opt_sys_cmail'], $lang['opt_sys_cmaild'], makeCheckBox( "save_con[mail_comments]", "{$config['mail_comments']}" ) );	
	showRow( $lang['opt_sys_acrate'], $lang['opt_sys_acrated'], makeCheckBox( "save_con[allow_comments_rating]", "{$config['allow_comments_rating']}" ) );
	showRow( $lang['opt_sys_rtc'], $lang['opt_sys_rtcd'], makeDropDown( array ("0" => $lang['opt_sys_rtp_1'], "1" => $lang['opt_sys_rtp_2'], "2" => $lang['opt_sys_rtp_3']), "save_con[comments_rating_type]", "{$config['comments_rating_type']}" ) );
	showRow( $lang['opt_sys_wdcom'], $lang['opt_sys_wdscomd'], makeDropDown( array ("-1" => $lang['editor_none'], "0" => $lang['editor_def'], "1" => "LiveEditor (WYSIWYG)", "2" => "TinyMCE (WYSIWYG)" ), "save_con[allow_comments_wysiwyg]", "{$config['allow_comments_wysiwyg']}" ) );
	
	echo "</table></div></div>";
	
	echo <<<HTML
<div id="optimisation" class="box" style='display:none'>
  <div class="box-header">
    <div class="title">{$lang['opt_sys_dch']}</div>
  </div>
  <div class="box-content">
  <table class="table table-normal">
HTML;
	showRow( $lang['opt_sys_cac'], $lang['opt_sys_cad'], makeCheckBox( "save_con[allow_cache]", "{$config['allow_cache']}" ), "white-line" );
	showRow( $lang['opt_sys_cc'], $lang['opt_sys_ccd'], "<input  type=text style=\"text-align: center;\" name=\"save_con[clear_cache]\" value=\"{$config['clear_cache']}\" size=20>" );
	showRow( $lang['opt_sys_ctype'], $lang['opt_sys_ctyped'], makeDropDown( array ("0" => $lang['opt_sys_filec'], "1" => "Memcache" ), "save_con[cache_type]", "{$config['cache_type']}" ) );
	showRow( $lang['opt_sys_memserv'], $lang['opt_sys_memservd'], "<input  type=text name=\"save_con[memcache_server]\" value=\"{$config['memcache_server']}\" size=40>" );
	showRow( $lang['opt_sys_ccache'], $lang['opt_sys_ccached'], makeCheckBox( "save_con[allow_comments_cache]", "{$config['allow_comments_cache']}" ) );
	showRow( $lang['opt_sys_ag'], $lang['opt_sys_agd'], makeCheckBox( "save_con[allow_gzip]", "{$config['allow_gzip']}" ) );
	showRow( $lang['opt_sys_ajsm'], $lang['opt_sys_ajsmd'], makeCheckBox( "save_con[js_min]", "{$config['js_min']}" ) );
	showRow( $lang['opt_sys_search'], $lang['opt_sys_searchd'], makeDropDown( array ("1" => $lang['opt_sys_advance'], "0" => $lang['opt_sys_simple'] ), "save_con[full_search]", "{$config['full_search']}" ) );
	showRow( $lang['opt_sys_fastsearch'], $lang['opt_sys_fastsearchd'], makeCheckBox( "save_con[fast_search]", "{$config['fast_search']}" ) );
	showRow( $lang['opt_sys_ur'], $lang['opt_sys_urd'], makeCheckBox( "save_con[allow_registration]", "{$config['allow_registration']}" ) );
	showRow( $lang['opt_sys_multiple'], $lang['opt_sys_multipled'], makeCheckBox( "save_con[allow_multi_category]", "{$config['allow_multi_category']}" ) );
	showRow( $lang['opt_sys_related'], $lang['opt_sys_relatedd'], makeCheckBox( "save_con[related_news]", "{$config['related_news']}" ) );
	showRow( $lang['opt_sys_nodate'], $lang['opt_sys_nodated'], makeCheckBox( "save_con[no_date]", "{$config['no_date']}" ) );
	showRow( $lang['opt_sys_afix'], $lang['opt_sys_afixd'], makeCheckBox( "save_con[allow_fixed]", "{$config['allow_fixed']}" ) );	
	showRow( $lang['opt_sys_sbar'], $lang['opt_sys_sbard'], makeCheckBox( "save_con[speedbar]", "{$config['speedbar']}" ) );
	showRow( $lang['opt_sys_ban'], $lang['opt_sys_band'], makeCheckBox( "save_con[allow_banner]", "{$config['allow_banner']}" ) );
	showRow( $lang['opt_sys_cmod'], $lang['opt_sys_cmodd'], makeCheckBox( "save_con[allow_cmod]", "{$config['allow_cmod']}" ) );
	showRow( $lang['opt_sys_voc'], $lang['opt_sys_vocd'], makeCheckBox( "save_con[allow_votes]", "{$config['allow_votes']}" ) );
	showRow( $lang['opt_sys_toc'], $lang['opt_sys_tocd'], makeCheckBox( "save_con[allow_topnews]", "{$config['allow_topnews']}" ) );
	showRow( $lang['opt_sys_rn'], $lang['opt_sys_rnd'], makeDropDown( array ("0" => $lang['opt_sys_r1'], "1" => $lang['opt_sys_r2'], "2" => $lang['opt_sys_r3'] ), "save_con[allow_read_count]", "{$config['allow_read_count']}" ) );
	showRow( $lang['cache_c'], $lang['cache_cd'], makeCheckBox( "save_con[cache_count]", "{$config['cache_count']}" ) );
	showRow( $lang['opt_sys_dk'], $lang['opt_sys_dkd'], makeCheckBox( "save_con[allow_calendar]", "{$config['allow_calendar']}" ) );
	showRow( $lang['opt_sys_da'], $lang['opt_sys_dad'], makeCheckBox( "save_con[allow_archives]", "{$config['allow_archives']}" ) );
	showRow( $lang['opt_sys_inform'], $lang['opt_sys_informd'], makeCheckBox( "save_con[rss_informer]", "{$config['rss_informer']}" ) );
	showRow( $lang['opt_sys_tags'], $lang['opt_sys_tagsd'], makeCheckBox( "save_con[allow_tags]", "{$config['allow_tags']}" ) );
	showRow( $lang['opt_sys_change_s'], $lang['opt_sys_change_sd'], makeCheckBox( "save_con[allow_change_sort]", "{$config['allow_change_sort']}" ) );
	showRow( $lang['opt_sys_cajax'], $lang['opt_sys_cajaxd'], makeCheckBox( "save_con[comments_ajax]", "{$config['comments_ajax']}" ) );
	showRow( $lang['opt_sys_online'], $lang['opt_sys_onlined'], makeCheckBox( "save_con[online_status]", "{$config['online_status']}" ) );
	showRow( $lang['opt_sys_links'], $lang['opt_sys_linksd'], makeCheckBox( "save_con[allow_links]", "{$config['allow_links']}" ) );
	
	echo "</table></div></div>";
	
	echo <<<HTML
<div id="files" class="box" style='display:none'>
  <div class="box-header">
    <div class="title">{$lang['opt_filesys']}</div>
  </div>
  <div class="box-content">
  <table class="table table-normal">
HTML;
	
	showRow( $lang['opt_sys_file'], $lang['opt_sys_filed'], makeCheckBox( "save_con[files_allow]", "{$config['files_allow']}" ), "white-line" );
	showRow( $lang['opt_sys_maxfilec'], $lang['opt_sys_maxfilecd'], "<input  type=text style=\"text-align: center;\"  name='save_con[max_file_count]' value=\"{$config['max_file_count']}\" size=20>" );
	showRow( $lang['opt_sys_file4'], $lang['opt_sys_file4d'], makeCheckBox( "save_con[files_force]", "{$config['files_force']}" ) );
	showRow( $lang['opt_sys_file3'], $lang['opt_sys_file3d'], makeCheckBox( "save_con[files_antileech]", "{$config['files_antileech']}" ) );
	showRow( $lang['opt_sys_file2'], $lang['opt_sys_file2d'], makeCheckBox("save_con[files_count]", "{$config['files_count']}" ) );
	
	echo "</table></div></div>";
	
	echo <<<HTML
<div id="mail" class="box" style='display:none'>
  <div class="box-header">
    <div class="title">{$lang['opt_sys_mail']}</div>
  </div>
  <div class="box-content">
  <table class="table table-normal">
HTML;

	showRow( $lang['opt_sys_amail'], $lang['opt_sys_amaild'], "<input  type=text name='save_con[admin_mail]' value='{$config['admin_mail']}' size=40>", "white-line" );
	showRow( $lang['opt_sys_mt'], $lang['opt_sys_mtd'], "<input  type=\"text\" name='save_con[mail_title]' value=\"{$config['mail_title']}\" size=40>" );

	showRow( $lang['opt_sys_mm'], $lang['opt_sys_mmd'], makeDropDown( array ("php" => "PHP Mail()", "smtp" => "SMTP" ), "save_con[mail_metod]", "{$config['mail_metod']}" ) );
	showRow( $lang['opt_sys_smtph'], $lang['opt_sys_smtphd'], "<input  type=text name='save_con[smtp_host]' value=\"{$config['smtp_host']}\" size=40>" );
	showRow( $lang['opt_sys_smtpp'], $lang['opt_sys_smtppd'], "<input  type=text style=\"text-align: center;\" name='save_con[smtp_port]' value=\"{$config['smtp_port']}\" size=20>" );
	showRow( $lang['opt_sys_smtup'], $lang['opt_sys_smtpud'], "<input  type=text name='save_con[smtp_user]' value=\"{$config['smtp_user']}\" size=40>" );
	showRow( $lang['opt_sys_smtupp'], $lang['opt_sys_smtpupd'], "<input  type=text name='save_con[smtp_pass]' value=\"{$config['smtp_pass']}\" size=40>" );
	showRow( $lang['opt_sys_msec'], $lang['opt_sys_msecd'], makeDropDown( array ("" => $lang['opt_sys_no'], "ssl" => "SSL", "tls" => "TLS" ), "save_con[smtp_secure]", "{$config['smtp_secure']}" ) );
	showRow( $lang['opt_sys_smtpm'], $lang['opt_sys_smtpmd'], "<input  type=text name='save_con[smtp_mail]' value=\"{$config['smtp_mail']}\" size=40>" );
	showRow( $lang['opt_sys_mbcc'], $lang['opt_sys_mbccd'], makeCheckBox( "save_con[mail_bcc]", "{$config['mail_bcc']}" ) );
	
	echo "</table></div></div>";
	
	echo <<<HTML
<div id="users" class="box" style='display:none'>
  <div class="box-header">
    <div class="title">{$lang['opt_sys_uch']}</div>
  </div>
  <div class="box-content">
  <table class="table table-normal">
HTML;

	showRow( $lang['opt_sys_mauth'], $lang['opt_sys_mauthd'], makeDropDown( array ("0" => $lang['opt_sys_login'], "1" => $lang['opt_sys_email'] ), "save_con[auth_metod]", $config['auth_metod'] ), "white-line" );
	showRow( $lang['opt_sys_reggroup'], $lang['opt_sys_reggroupd'], makeDropDown( $sys_group_arr, "save_con[reg_group]", $config['reg_group'] ) );
	showRow( $lang['opt_sys_ut'], $lang['opt_sys_utd'], makeDropDown( array ("0" => $lang['opt_sys_reg'], "1" => $lang['opt_sys_reg_1'] ), "save_con[registration_type]", "{$config['registration_type']}" ) );
	showRow( $lang['opt_sys_addsec'], $lang['opt_sys_addsecd'], makeDropDown( array ( "0" => $lang['opt_sys_r1'], "3" => $lang['opt_sys_r6'], "2" => $lang['opt_sys_r4'], "1" => $lang['opt_sys_r5'] ), "save_con[sec_addnews]", "{$config['sec_addnews']}" ) );
	showRow( $lang['opt_sys_sapi'], $lang['opt_sys_sapid'], "<input type=text name=\"save_con[spam_api_key]\" value=\"{$config['spam_api_key']}\" size=50>" );
	showRow( $lang['opt_sys_pn'], $lang['opt_sys_pnd'], makeCheckBox( "save_con[profile_news]", "{$config['profile_news']}" ) );
	showRow( $lang['opt_sys_soc'], $lang['opt_sys_socd'], makeCheckBox( "save_con[allow_social]", "{$config['allow_social']}" ) );
	showRow( $lang['opt_sys_rsc'], $lang['opt_sys_rscd'], makeCheckBox( "save_con[auth_only_social]", "{$config['auth_only_social']}" ) );

	showRow( $lang['opt_sys_rmip'], $lang['opt_sys_rmipd'], makeCheckBox( "save_con[reg_multi_ip]", "{$config['reg_multi_ip']}" ) );
	showRow( $lang['opt_sys_adr'], $lang['opt_sys_adrd'], makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "save_con[auth_domain]", "{$config['auth_domain']}" ) );
	showRow( $lang['opt_sys_rules'], $lang['opt_sys_rulesd'], makeCheckBox( "save_con[registration_rules]", "{$config['registration_rules']}" ) );
	showRow( $lang['opt_sys_code'], $lang['opt_sys_coded'], makeCheckBox( "save_con[allow_sec_code]", "{$config['allow_sec_code']}" ) );
	showRow( $lang['opt_sys_question'], $lang['opt_sys_questiond'], makeCheckBox( "save_con[reg_question]", "{$config['reg_question']}" ) );
	showRow( $lang['opt_sys_sc'], $lang['opt_sys_scd'], makeCheckBox( "save_con[allow_skin_change]", "{$config['allow_skin_change']}" ) );
	showRow( $lang['opt_sys_pmail'], $lang['opt_sys_pmaild'], makeCheckBox( "save_con[mail_pm]", "{$config['mail_pm']}" ) );
	showRow( $lang['opt_sys_um'], $lang['opt_sys_umd'], "<input  type=text style=\"text-align: center;\"  name='save_con[max_users]' value=\"{$config['max_users']}\" size=20>" );
	showRow( $lang['opt_sys_ud'], $lang['opt_sys_udd'], "<input  type=text style=\"text-align: center;\"  name='save_con[max_users_day]' value=\"{$config['max_users_day']}\" size=20>" );
	
	echo "</table></div></div>";
	
	echo <<<HTML
<div id="imagesconf" class="box" style='display:none'>
  <div class="box-header">
    <div class="title">{$lang['opt_sys_ich']}</div>
  </div>
  <div class="box-content">
  <table class="table table-normal">
HTML;
	
	showRow( $lang['opt_sys_maxside'], $lang['opt_sys_maxsided'], "<input  type=text style=\"text-align: center;\"  name='save_con[max_up_side]' value=\"{$config['max_up_side']}\" size=20>", "white-line" );
	showRow( $lang['opt_sys_sdefm'], $lang['opt_sys_sdefmd'], makeDropDown( array ("0" => $lang['upload_t_seite_1'], "1" => $lang['upload_t_seite_2'], "2" => $lang['upload_t_seite_3'] ), "save_con[o_seite]", "{$config['o_seite']}" ) );
	showRow( $lang['opt_sys_maxsize'], $lang['opt_sys_maxsized'], "<input  type=text style=\"text-align: center;\"  name='save_con[max_up_size]' value=\"{$config['max_up_size']}\" size=20>" );
	showRow( $lang['opt_sys_dim'], $lang['opt_sys_dimd'], "<input  type=text style=\"text-align: center;\"  name='save_con[max_image_days]' value=\"{$config['max_image_days']}\" size=20>" );
	showRow( $lang['opt_sys_iw'], $lang['opt_sys_iwd'], makeCheckBox( "save_con[allow_watermark]", "{$config['allow_watermark']}" ) );
	showRow( $lang['opt_sys_im'], $lang['opt_sys_imd'], "<input  type=text style=\"text-align: center;\"  name='save_con[max_watermark]' value=\"{$config['max_watermark']}\" size=20>" );
	showRow( $lang['opt_sys_wms'], $lang['opt_sys_wmsd'], makeDropDown( array ("1" => $lang['opt_sys_wms_1'], "2" => $lang['opt_sys_wms_2'], "3" => $lang['opt_sys_wms_3'], "4" => $lang['opt_sys_wms_4'] ), "save_con[watermark_seite]", "{$config['watermark_seite']}" ) );
	showRow( $lang['opt_sys_ia'], $lang['opt_sys_iad'], "<input  type=text style=\"text-align: center;\"  name='save_con[max_image]' value=\"{$config['max_image']}\" size=20>" );
	showRow( $lang['opt_sys_mi'], $lang['opt_sys_mid'], "<input  type=text style=\"text-align: center;\"  name='save_con[medium_image]' value=\"{$config['medium_image']}\" size=20>" );
	showRow( $lang['opt_sys_sdef'], $lang['opt_sys_sdefd'], makeDropDown( array ("0" => $lang['upload_t_seite_1'], "1" => $lang['upload_t_seite_2'], "2" => $lang['upload_t_seite_3'] ), "save_con[t_seite]", "{$config['t_seite']}" ) );
	showRow( $lang['opt_sys_ij'], $lang['opt_sys_ijd'], "<input  type=text style=\"text-align: center;\"  name='save_con[jpeg_quality]' value=\"{$config['jpeg_quality']}\" size=20>" );
	showRow( $lang['opt_sys_av'], $lang['opt_sys_avd'], "<input  type=text style=\"text-align: center;\"  name='save_con[avatar_size]' value=\"{$config['avatar_size']}\" size=20>" );

	showRow( $lang['opt_sys_imw'], $lang['opt_sys_imwd'], "<input  type=text style=\"text-align: center;\"  name='save_con[tag_img_width]' value=\"{$config['tag_img_width']}\" size=20>" );
	showRow( $lang['opt_sys_dimm'], $lang['opt_sys_dimmd'], makeCheckBox( "save_con[thumb_dimming]", "{$config['thumb_dimming']}" ) );
	showRow( $lang['opt_sys_gall'], $lang['opt_sys_galld'], makeCheckBox( "save_con[thumb_gallery]", "{$config['thumb_gallery']}" ) );
	showRow( $lang['opt_sys_sim'], $lang['opt_sys_simd'], makeDropDown( array ("0" => $lang['outline_1'], "1" => $lang['outline_2'], "2" => $lang['outline_3'], "3" => $lang['outline_4'] ), "save_con[outlinetype]", "{$config['outlinetype']}" ) );
	
	echo "</table></div></div>";


	echo <<<HTML
<div id="smartphone" class="box" style='display:none'>
  <div class="box-header">
    <div class="title">{$lang['opt_smartphone']}</div>
  </div>
  <div class="box-content">
  <table class="table table-normal">
HTML;
	
	showRow( $lang['opt_sys_smart'], $lang['opt_sys_smartd'], makeCheckBox( "save_con[allow_smartphone]", "{$config['allow_smartphone']}" ), "white-line" );
	showRow( $lang['opt_sys_sm_im'], $lang['opt_sys_sm_imd'], makeCheckBox( "save_con[allow_smart_images]", "{$config['allow_smart_images']}" ) );
	showRow( $lang['opt_sys_sm_iv'], $lang['opt_sys_sm_ivd'], makeCheckBox( "save_con[allow_smart_video]", "{$config['allow_smart_video']}" ) );
	showRow( $lang['opt_sys_sm_fm'], $lang['opt_sys_sm_fmd'], makeCheckBox( "save_con[allow_smart_format]", "{$config['allow_smart_format']}" ) );
	showRow( $lang['opt_sys_sm_n'], $lang['opt_sys_sm_nd'], "<input  type=text style=\"text-align: center;\"  name='save_con[mobile_news]' value=\"{$config['mobile_news']}\" size=20>" );
	
	echo "</table></div></div>";

	
	echo <<<HTML
<div id="rss" class="box" style='display:none'>
  <div class="box-header">
    <div class="title">{$lang['opt_rsssys']}</div>
  </div>
  <div class="box-content">
  <table class="table table-normal">
HTML;
	
	showRow( $lang['opt_sys_arss'], $lang['opt_sys_arssd'], makeCheckBox( "save_con[allow_rss]", "{$config['allow_rss']}" ), "white-line" );
	showRow( $lang['opt_sys_trss'], $lang['opt_sys_trssd'], makeDropDown( array ("0" => $lang['opt_sys_rss_type_0'], "1" => $lang['opt_sys_rss_type_1'] ), "save_con[rss_mtype]", "{$config['rss_mtype']}" ) );
	showRow( $lang['opt_sys_nrss'], $lang['opt_sys_nrssd'], "<input  type=text style=\"text-align: center;\"  name='save_con[rss_number]' value=\"{$config['rss_number']}\" size=20>" );
	showRow( $lang['opt_sys_frss'], $lang['opt_sys_frssd'], makeDropDown( array ("0" => $lang['opt_sys_rss_type_2'], "1" => $lang['opt_sys_rss_type_3'], "2" => $lang['opt_sys_rss_type_4'] ), "save_con[rss_format]", "{$config['rss_format']}" ) );
	
	echo "</table></div></div>";

	if(!is_writable(ENGINE_DIR . '/data/config.php')) {

		echo "<div class=\"alert alert-error\">".str_replace("{file}", "engine/data/config.php", $lang['stat_system'])."</div>";

	}
	
	echo <<<HTML
<div style="margin-bottom:30px;">
<input type="hidden" name="mod" value="options">
<input type="hidden" name="action" value="dosavesyscon">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
<input type="submit" class="btn btn-lg btn-green" value="{$lang['user_save']}">
</div>
</form>
HTML;
	
	echofooter();
} // ********************************************************************************
// Запись настроек
// ********************************************************************************
elseif( $action == "dosavesyscon" ) {
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	if( $member_id['user_group'] != 1 ) {
		msg( "error", $lang['opt_denied'], $lang['opt_denied'] );
	}

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '48', '')" );
	
	$save_con = $_POST['save_con'];
	$save_con['seo_control'] = intval($save_con['seo_control']);
	$save_con['allow_complaint_mail'] = intval($save_con['allow_complaint_mail']);
	$save_con['site_offline'] = intval($save_con['site_offline']);
	$save_con['allow_alt_url'] = intval($save_con['allow_alt_url']);
	$save_con['log_hash'] = intval($save_con['log_hash']);
	$save_con['news_future'] = intval($save_con['news_future']);
	$save_con['create_metatags'] = intval($save_con['create_metatags']);
	$save_con['create_catalog'] = intval($save_con['create_catalog']);
	$save_con['parse_links'] = intval($save_con['parse_links']);
	$save_con['mail_news'] = intval($save_con['mail_news']);
	$save_con['show_sub_cats'] = intval($save_con['show_sub_cats']);
	$save_con['short_rating'] = intval($save_con['short_rating']);
	$save_con['allow_search_print'] = intval($save_con['allow_search_print']);
	$save_con['allow_add_tags'] = intval($save_con['allow_add_tags']);
	$save_con['allow_share'] = intval($save_con['allow_share']);
	$save_con['related_only_cats'] = intval($save_con['related_only_cats']);
	$save_con['hide_full_link'] = intval($save_con['hide_full_link']);
	$save_con['allow_subscribe'] = intval($save_con['allow_subscribe']);
	$save_con['allow_combine'] = intval($save_con['allow_combine']);
	$save_con['allow_search_link'] = intval($save_con['allow_search_link']);
	$save_con['mail_comments'] = intval($save_con['mail_comments']);
	$save_con['allow_comments'] = intval($save_con['allow_comments']);
	$save_con['allow_comments_cache'] = intval($save_con['allow_comments_cache']);
	$save_con['js_min'] = intval($save_con['js_min']);
	$save_con['fast_search'] = intval($save_con['fast_search']);
	$save_con['allow_multi_category'] = intval($save_con['allow_multi_category']);
	$save_con['related_news'] = intval($save_con['related_news']);
	$save_con['no_date'] = intval($save_con['no_date']);
	$save_con['allow_fixed'] = intval($save_con['allow_fixed']);
	$save_con['speedbar'] = intval($save_con['speedbar']);
	$save_con['allow_banner'] = intval($save_con['allow_banner']);
	$save_con['allow_cmod'] = intval($save_con['allow_cmod']);
	$save_con['cache_count'] = intval($save_con['cache_count']);
	$save_con['rss_informer'] = intval($save_con['rss_informer']);
	$save_con['allow_tags'] = intval($save_con['allow_tags']);
	$save_con['allow_change_sort'] = intval($save_con['allow_change_sort']);
	$save_con['comments_ajax'] = intval($save_con['comments_ajax']);
	$save_con['online_status'] = intval($save_con['online_status']);
	$save_con['allow_links'] = intval($save_con['allow_links']);
	$save_con['allow_cache'] = intval($save_con['allow_cache']);
	$save_con['allow_gzip'] = intval($save_con['allow_gzip']);
	$save_con['allow_registration'] = intval($save_con['allow_registration']);
	$save_con['allow_votes'] = intval($save_con['allow_votes']);
	$save_con['allow_topnews'] = intval($save_con['allow_topnews']);
	$save_con['allow_calendar'] = intval($save_con['allow_calendar']);
	$save_con['allow_archives'] = intval($save_con['allow_archives']);
	$save_con['files_allow'] = intval($save_con['files_allow']);
	$save_con['files_count'] = intval($save_con['files_count']);
	$save_con['allow_sec_code'] = intval($save_con['allow_sec_code']);
	$save_con['allow_skin_change'] = intval($save_con['allow_skin_change']);
	$save_con['allow_watermark'] = intval($save_con['allow_watermark']);
	$save_con['files_force'] = intval($save_con['files_force']);
	$save_con['files_antileech'] = intval($save_con['files_antileech']);
	$save_con['use_admin_mail'] = intval($save_con['use_admin_mail']);
	$save_con['mail_bcc'] = intval($save_con['mail_bcc']);
	$save_con['reg_multi_ip'] = intval($save_con['reg_multi_ip']);
	$save_con['registration_rules'] = intval($save_con['registration_rules']);
	$save_con['reg_question'] = intval($save_con['reg_question']);
	$save_con['mail_pm'] = intval($save_con['mail_pm']);
	$save_con['thumb_dimming'] = intval($save_con['thumb_dimming']);
	$save_con['thumb_gallery'] = intval($save_con['thumb_gallery']);
	$save_con['allow_smartphone'] = intval($save_con['allow_smartphone']);
	$save_con['allow_smart_images'] = intval($save_con['allow_smart_images']);
	$save_con['allow_smart_video'] = intval($save_con['allow_smart_video']);
	$save_con['allow_smart_format'] = intval($save_con['allow_smart_format']);
	$save_con['allow_rss'] = intval($save_con['allow_rss']);
	$save_con['comments_lazyload'] = intval($save_con['comments_lazyload']);
	$save_con['adminlog_maxdays'] = intval($save_con['adminlog_maxdays']);
	$save_con['allow_social'] = intval($save_con['allow_social']);
	$save_con['auth_only_social'] = intval($save_con['auth_only_social']);
	$save_con['allow_comments_rating'] = intval($save_con['allow_comments_rating']);
	$save_con['tree_comments'] = intval($save_con['tree_comments']);
	$save_con['tree_comments_level'] = intval($save_con['tree_comments_level']);
	$save_con['simple_reply'] = intval($save_con['simple_reply']);
	$save_con['profile_news'] = intval($save_con['profile_news']);

	if( $save_con['adminlog_maxdays'] < 30 ) $save_con['adminlog_maxdays'] = 30;

	if (substr ( $save_con['http_home_url'], - 1, 1 ) != '/') $save_con['http_home_url'] = $save_con['http_home_url']."/";
	
	include_once ENGINE_DIR . '/classes/parse.class.php';
	$parse = new ParseFilter();
	$parse->safe_mode = true;;
	
	$save_con['offline_reason'] = $parse->process( stripslashes( trim( $save_con['offline_reason'] ) ) );
	$save_con['offline_reason'] = str_replace( '"', '&quot;', $parse->BB_Parse( $save_con['offline_reason'], false ) );

	$save_con['admin_allowed_ip'] = str_replace( "\r", "", trim( $save_con['admin_allowed_ip'] ) );
	$save_con['admin_allowed_ip'] = str_replace( "\n", "|", $save_con['admin_allowed_ip'] );


	$temp_array = explode ("|", $save_con['admin_allowed_ip']);
	$allowed_ip	= array();
	
	if (count($temp_array)) {
	
		foreach ( $temp_array as $value ) {
			$value1 = str_replace( "*", "0", trim($value) );
			$value1 = explode ('/', $value1);
					
			$value1 = ip2long($value1[0]);
	
			if( $value1 != -1 AND $value1 !== FALSE ) $allowed_ip[] = trim( $value );
		}
		
	}
	
	if ( count($allowed_ip) ) $save_con['admin_allowed_ip'] = implode("|", $allowed_ip); else $save_con['admin_allowed_ip'] = "";

	if($save_con['related_number'] != $config['related_number']) {
		$db->query( "UPDATE " . PREFIX . "_post_extras SET related_ids=''" );
	}

	$find = array();
	$replace = array();
	
	$find[] = "'\r'";
	$replace[] = "";
	$find[] = "'\n'";
	$replace[] = "";

	if( $auto_detect_config ) $config['http_home_url'] = "";
	
	$save_con = $save_con + $config;

	$handler = fopen( ENGINE_DIR . '/data/config.php', "w" );
	
	fwrite( $handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n" );
	foreach ( $save_con as $name => $value ) {
		
		if( $name != "offline_reason" ) {
			
			$value = trim( strip_tags(stripslashes( $value )) );
			$value = htmlspecialchars( $value, ENT_QUOTES, $config['charset']);
			$value = preg_replace( $find, $replace, $value );
			
			$name = trim( strip_tags(stripslashes( $name )) );
			$name = htmlspecialchars( $name, ENT_QUOTES, $config['charset'] );
			$name = preg_replace( $find, $replace, $name );
		}

		if( $name == "speedbar_separator" OR $name == "category_separator") {
			$value = str_replace( '&amp;', '&', $value );
		}
		
		$value = str_replace( "$", "&#036;", $value );
		$value = str_replace( "{", "&#123;", $value );
		$value = str_replace( "}", "&#125;", $value );
		$value = str_replace( chr(0), "", $value );
		$value = str_replace( chr(92), "", $value );
		$value = str_ireplace( "base64_decode", "base64_dec&#111;de", $value );
		
		$name = str_replace( "$", "&#036;", $name );
		$name = str_replace( "{", "&#123;", $name );
		$name = str_replace( "}", "&#125;", $name );
		$name = str_replace( chr(0), "", $name );
		$name = str_replace( chr(92), "", $name );
		$name = str_replace( '(', "", $name );
		$name = str_replace( ')', "", $name );
		$name = str_ireplace( "base64_decode", "base64_dec&#111;de", $name );
		
		fwrite( $handler, "'{$name}' => '{$value}',\n\n" );
	
	}
	fwrite( $handler, ");\n\n?>" );
	fclose( $handler );
	
	clear_cache();
	msg( "info", $lang['opt_sysok'], $lang['opt_sysok_1'], "?mod=options&action=syscon" );
}

?>