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
 Файл: usergroup.php
-----------------------------------------------------
 Назначение: Настройка групп пользователей
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['addnews_denied'], $lang['db_denied'] );
}

function clear_html( $txt ) {

	if(!$txt) return;

	$find = array ('/data:/i', '/about:/i', '/vbscript:/i', '/onclick/i', '/onload/i', '/onunload/i', '/onabort/i', '/onerror/i', '/onblur/i', '/onchange/i', '/onfocus/i', '/onreset/i', '/onsubmit/i', '/ondblclick/i', '/onkeydown/i', '/onkeypress/i', '/onkeyup/i', '/onmousedown/i', '/onmouseup/i', '/onmouseover/i', '/onmouseout/i', '/onselect/i', '/javascript/i', '/onmouseenter/i', '/onwheel/i', '/onshow/i');
	$replace = array ("d&#097;ta:", "&#097;bout:", "vbscript<b></b>:", "&#111;nclick", "&#111;nload", "&#111;nunload", "&#111;nabort", "&#111;nerror", "&#111;nblur", "&#111;nchange", "&#111;nfocus", "&#111;nreset", "&#111;nsubmit", "&#111;ndblclick", "&#111;nkeydown", "&#111;nkeypress", "&#111;nkeyup", "&#111;nmousedown", "&#111;nmouseup", "&#111;nmouseover", "&#111;nmouseout", "&#111;nselect", "j&#097;vascript", '&#111;nmouseenter', '&#111;nwheel', '&#111;nshow');

	$txt = preg_replace( $find, $replace, $txt );
	$txt = preg_replace( "#<iframe#i", "&lt;iframe", $txt );
	$txt = preg_replace( "#<script#i", "&lt;script", $txt );
	$txt = str_replace( "<?", "&lt;?", $txt );
	$txt = str_replace( "?>", "?&gt;", $txt );

	return $txt;

}

if( $action == "del" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$id = intval( $_REQUEST['id'] );
	$grouplevel = intval( $_REQUEST['grouplevel'] );
	
	if( $id < 6 ) msg( "error", $lang['addnews_error'], $lang['group_notdel'], "?mod=usergroup" );
	
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '75', '{$id}')" );

	$row = $db->super_query( "SELECT count(*) as count FROM " . USERPREFIX . "_users WHERE user_group='{$id}'" );
	
	if( ! $row['count'] ) {
		$db->query( "DELETE FROM " . USERPREFIX . "_usergroups WHERE id = '{$id}'" );
		@unlink( ENGINE_DIR . '/cache/system/usergroup.php' );
		clear_cache();
		msg( "info", $lang['all_info'], $lang['group_del'], "?mod=usergroup" );
	} else {
		if( $grouplevel and $grouplevel != $id ) {
			$db->query( "UPDATE " . USERPREFIX . "_users set user_group='{$grouplevel}' WHERE user_group='{$id}'" );
			$db->query( "DELETE FROM " . USERPREFIX . "_usergroups WHERE id = '{$id}'" );
			@unlink( ENGINE_DIR . '/cache/system/usergroup.php' );
			clear_cache();
			msg( "info", $lang['all_info'], $lang['group_del'], "?mod=usergroup" );
		} else
			msg( "info", $lang['all_info'], "<form action=\"\" method=\"post\">{$lang['group_move']} <select class=\"uniform\" name=\"grouplevel\">" . get_groups( 4 ) . "</select> <input class=\"btn btn-blue\" type=\"submit\" value=\"{$lang['b_start']}\"></form>", "?mod=usergroup" );
	}

} elseif( $action == "selectgroup" ) {

	msg( "info", $lang['all_info'], "<form action=\"\" method=\"get\"><input type=\"hidden\" name=\"mod\" value=\"usergroup\"><input type=\"hidden\" name=\"action\" value=\"add\">{$lang['group_select']} <select class=\"uniform\" name=\"id\">" . get_groups( 4 ) . "</select> <input class=\"btn btn-blue\" type=\"submit\" value=\"{$lang['b_start']}\"></form>", "?mod=usergroup" );

} elseif( $action == "doadd" OR $action == "doedit" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	if( ! count( $_REQUEST['allow_cats'] ) ) $_REQUEST['allow_cats'][] = "all";
	if( ! count( $_REQUEST['not_allow_cats'] ) ) $_REQUEST['not_allow_cats'][] = "";
	if( ! count( $_REQUEST['cat_add'] ) ) $_REQUEST['cat_add'][] = "all";
	if( ! count( $_REQUEST['cat_allow_addnews'] ) ) $_REQUEST['cat_allow_addnews'][] = "all";

	$group_name = $db->safesql( strip_tags( clear_html($_REQUEST['group_name']) ) );
	$group_icon = $db->safesql( strip_tags( clear_html($_REQUEST['group_icon']) ) );
	$files_type = $db->safesql( strip_tags( clear_html($_REQUEST['files_type']) ) );
	$files_type = str_replace(' ','', $files_type);

	$group_prefix = $db->safesql( trim( clear_html($_REQUEST['group_prefix']) ) );
	$group_suffix = $db->safesql( trim( clear_html($_REQUEST['group_suffix']) ) );

	$list = array();
	
	foreach ( $_REQUEST['not_allow_cats'] as $value ) {
		if( intval($value) ) $list[] = intval($value);
	}
	
	$not_allow_cats = $db->safesql( implode( ',', $list) );
	
	$allow_cats = $db->safesql( clear_html(implode( ',', $_REQUEST['allow_cats']) ) );
	$cat_add = $db->safesql( clear_html(implode( ',', $_REQUEST['cat_add']) ) );
	$cat_allow_addnews = $db->safesql( clear_html(implode( ',', $_REQUEST['cat_allow_addnews']) ) );
	
	$allow_admin = intval( $_REQUEST['allow_admin'] );
	$allow_offline = intval( $_REQUEST['allow_offline'] );
	$allow_main = intval( $_REQUEST['allow_main'] );
	$allow_adds = intval( $_REQUEST['allow_adds'] );
	$moderation = intval( $_REQUEST['moderation'] );
	$allow_edit = intval( $_REQUEST['allow_edit'] );
	$allow_all_edit = intval( $_REQUEST['allow_all_edit'] );
	$allow_addc = intval( $_REQUEST['allow_addc'] );
	$allow_editc = intval( $_REQUEST['allow_editc'] );
	$allow_delc = intval( $_REQUEST['allow_delc'] );
	$edit_allc = intval( $_REQUEST['edit_allc'] );
	$del_allc = intval( $_REQUEST['del_allc'] );
	$allow_hide = intval( $_REQUEST['allow_hide'] );
	$allow_pm = intval( $_REQUEST['allow_pm'] );
	$allow_vote = intval( $_REQUEST['allow_vote'] );
	$allow_files = intval( $_REQUEST['allow_files'] );
	$allow_feed = intval( $_REQUEST['allow_feed'] );
	$allow_search = intval( $_REQUEST['allow_search'] );
	$allow_rating = intval( $_REQUEST['allow_rating'] );
	$allow_comments_rating = intval( $_REQUEST['allow_comments_rating'] );	
	$max_pm = intval( $_REQUEST['max_pm'] );
	$max_foto = $db->safesql( $_REQUEST['max_foto'] );
	$allow_short = intval( $_REQUEST['allow_short'] );
	$time_limit = intval( $_REQUEST['time_limit'] );
	$rid = intval( $_REQUEST['rid'] );
	$allow_fixed = intval( $_REQUEST['allow_fixed'] );
	$allow_poll = intval( $_REQUEST['allow_poll'] );
	$captcha = intval( $_REQUEST['captcha'] );
	$allow_modc = intval( $_REQUEST['allow_modc'] );
	$max_signature = intval( $_REQUEST['max_signature'] );
	$max_info = intval( $_REQUEST['max_info'] );
	$admin_addnews = intval( $_REQUEST['admin_addnews'] );
	$admin_editnews = intval( $_REQUEST['admin_editnews'] );
	$admin_comments = intval( $_REQUEST['admin_comments'] );
	$admin_categories = intval( $_REQUEST['admin_categories'] );
	$admin_editusers = intval( $_REQUEST['admin_editusers'] );
	$admin_wordfilter = intval( $_REQUEST['admin_wordfilter'] );
	$admin_xfields = intval( $_REQUEST['admin_xfields'] );
	$admin_userfields = intval( $_REQUEST['admin_userfields'] );
	$admin_static = intval( $_REQUEST['admin_static'] );
	$admin_editvote = intval( $_REQUEST['admin_editvote'] );
	$admin_newsletter = intval( $_REQUEST['admin_newsletter'] );
	$admin_blockip = intval( $_REQUEST['admin_blockip'] );
	$admin_banners = intval( $_REQUEST['admin_banners'] );
	$admin_rss = intval( $_REQUEST['admin_rss'] );
	$admin_iptools = intval( $_REQUEST['admin_iptools'] );
	$admin_rssinform = intval( $_REQUEST['admin_rssinform'] );
	$admin_googlemap = intval( $_REQUEST['admin_googlemap'] );
	$admin_tagscloud = intval( $_REQUEST['admin_tagscloud'] );
	$admin_complaint = intval( $_REQUEST['admin_complaint'] );
	$allow_html = intval( $_REQUEST['allow_html'] );
	$allow_image_size = intval( $_REQUEST['allow_image_size'] );
	$allow_image_upload = intval( $_REQUEST['allow_image_upload'] );
	$allow_file_upload = intval( $_REQUEST['allow_file_upload'] );
	$allow_signature = intval( $_REQUEST['allow_signature'] );
	$allow_url = intval( $_REQUEST['allow_url'] );
	$allow_image = intval( $_REQUEST['allow_image'] );
	$news_sec_code = intval( $_REQUEST['news_sec_code'] );
	$allow_subscribe = intval( $_REQUEST['allow_subscribe'] );
	$flood_news = intval( $_REQUEST['flood_news'] );
	$max_day_news = intval( $_REQUEST['max_day_news'] );
	$force_leech = intval( $_REQUEST['force_leech'] );
	$edit_limit = intval( $_REQUEST['edit_limit'] );
	$captcha_pm = intval( $_REQUEST['captcha_pm'] );
	$max_pm_day = intval( $_REQUEST['max_pm_day'] );
	$max_comment_day = intval( $_REQUEST['max_comment_day'] );
	$max_mail_day = intval( $_REQUEST['max_mail_day'] );
	$comments_question = intval( $_REQUEST['comments_question'] );
	$news_question = intval( $_REQUEST['news_question'] );
	$max_images = intval( $_REQUEST['max_images'] );
	$max_files = intval( $_REQUEST['max_files'] );
	$disable_news_captcha = intval( $_REQUEST['disable_news_captcha'] );
	$disable_comments_captcha = intval( $_REQUEST['disable_comments_captcha'] );
	$pm_question = intval( $_REQUEST['pm_question'] );
	$captcha_feedback = intval( $_REQUEST['captcha_feedback'] );
	$feedback_question = intval( $_REQUEST['feedback_question'] );
	$max_file_size = intval( $_REQUEST['max_file_size'] );
	$files_max_speed = intval( $_REQUEST['files_max_speed'] );
	$allow_lostpassword = intval( $_REQUEST['allow_lostpassword'] );
	$spamfilter = intval( $_REQUEST['spamfilter'] );
	$spampmfilter = intval( $_REQUEST['spampmfilter'] );
	$max_edit_days = intval( $_REQUEST['max_edit_days'] );
	$force_reg = intval( $_REQUEST['force_reg'] );
	$force_reg_days = intval( $_REQUEST['force_reg_days'] );
	$force_reg_group = intval( $_REQUEST['force_reg_group'] );
	$force_news = intval( $_REQUEST['force_news'] );
	$force_news_count = intval( $_REQUEST['force_news_count'] );
	$force_news_group = intval( $_REQUEST['force_news_group'] );
	$force_comments = intval( $_REQUEST['force_comments'] );
	$force_comments_count = intval( $_REQUEST['force_comments_count'] );
	$force_comments_group = intval( $_REQUEST['force_comments_group'] );
	$force_rating = intval( $_REQUEST['force_rating'] );
	$force_rating_count = intval( $_REQUEST['force_rating_count'] );
	$force_rating_group = intval( $_REQUEST['force_rating_group'] );
	

	if( $group_name == "" ) msg( "error", $lang['addnews_error'], $lang['group_err1'], "?mod=usergroup&action=add" );
	
	@unlink( ENGINE_DIR . '/cache/system/usergroup.php' );
	
	if( $action == "doadd" ) {
		$db->query( "INSERT INTO " . USERPREFIX . "_usergroups (group_name, allow_cats, allow_adds, cat_add, allow_admin, allow_addc, allow_editc, allow_delc, edit_allc, del_allc, moderation, allow_all_edit, allow_edit, allow_pm, max_pm, max_foto, allow_files, allow_hide, allow_short, time_limit, rid, allow_fixed, allow_feed, allow_search, allow_poll, allow_main, captcha, icon, allow_modc, allow_rating, allow_offline, allow_image_upload, allow_file_upload, allow_signature, allow_url, news_sec_code, allow_image, max_signature, max_info, admin_addnews, admin_editnews, admin_comments, admin_categories, admin_editusers, admin_wordfilter, admin_xfields, admin_userfields, admin_static, admin_editvote, admin_newsletter, admin_blockip, admin_banners, admin_rss, admin_iptools, admin_rssinform, admin_googlemap, allow_html, group_prefix, group_suffix, allow_subscribe, allow_image_size, cat_allow_addnews, flood_news, max_day_news, force_leech, edit_limit, captcha_pm, max_pm_day, max_mail_day, admin_tagscloud, allow_vote, admin_complaint, news_question, comments_question, max_comment_day, max_images, max_files, disable_news_captcha, disable_comments_captcha, pm_question, captcha_feedback, feedback_question, files_type, max_file_size, files_max_speed, allow_lostpassword, spamfilter, allow_comments_rating, max_edit_days, spampmfilter, force_reg, force_reg_days, force_reg_group, force_news, force_news_count, force_news_group, force_comments, force_comments_count, force_comments_group, force_rating, force_rating_count, force_rating_group, not_allow_cats) values ('$group_name', '$allow_cats', '$allow_adds', '$cat_add', '$allow_admin', '$allow_addc', '$allow_editc', '$allow_delc', '$edit_allc', '$del_allc', '$moderation', '$allow_all_edit', '$allow_edit', '$allow_pm', '$max_pm', '$max_foto', '$allow_files', '$allow_hide', '$allow_short', '$time_limit', '$rid', '$allow_fixed', '$allow_feed', '$allow_search', '$allow_poll', '$allow_main', '$captcha', '$group_icon', '$allow_modc', '$allow_rating', '$allow_offline', '$allow_image_upload', '$allow_file_upload', '$allow_signature', '$allow_url', '$news_sec_code', '$allow_image', '$max_signature', '$max_info', '$admin_addnews', '$admin_editnews', '$admin_comments', '$admin_categories', '$admin_editusers', '$admin_wordfilter', '$admin_xfields', '$admin_userfields', '$admin_static', '$admin_editvote', '$admin_newsletter', '$admin_blockip', '$admin_banners', '$admin_rss', '$admin_iptools', '$admin_rssinform', '$admin_googlemap', '$allow_html', '$group_prefix', '$group_suffix', '$allow_subscribe', '$allow_image_size', '$cat_allow_addnews', '$flood_news', '$max_day_news', '$force_leech', '$edit_limit', '$captcha_pm', '$max_pm_day', '$max_mail_day', '$admin_tagscloud', '$allow_vote', '$admin_complaint', '$news_question', '$comments_question', '$max_comment_day', '$max_images', '$max_files', '$disable_news_captcha', '$disable_comments_captcha', '$pm_question', '$captcha_feedback', '$feedback_question', '$files_type', '$max_file_size', '$files_max_speed', '$allow_lostpassword', '$spamfilter', '$allow_comments_rating', '$max_edit_days', '$spampmfilter', '$force_reg','$force_reg_days','$force_reg_group','$force_news','$force_news_count','$force_news_group','$force_comments','$force_comments_count','$force_comments_group','$force_rating','$force_rating_count','$force_rating_group', '$not_allow_cats')" );
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '76', '{$group_name}')" );
		msg( "info", $lang['all_info'], $lang['group_ok1'], "?mod=usergroup" );
	} else {
		$id = intval( $_REQUEST['id'] );
		if( $id == 1 ) $allow_admin = 1;
		if( $id == 4 OR  $id == 5) $allow_admin = 0;
		$db->query( "UPDATE " . USERPREFIX . "_usergroups SET group_name='$group_name', allow_cats='$allow_cats', allow_adds='$allow_adds', cat_add='$cat_add', allow_admin='$allow_admin', allow_addc='$allow_addc', allow_editc='$allow_editc', allow_delc='$allow_delc', edit_allc='$edit_allc', del_allc='$del_allc', moderation='$moderation', allow_all_edit='$allow_all_edit', allow_edit='$allow_edit', allow_pm='$allow_pm', max_pm='$max_pm', max_foto='$max_foto', allow_files='$allow_files', allow_hide='$allow_hide', allow_short='$allow_short', time_limit='$time_limit', rid='$rid', allow_fixed='$allow_fixed', allow_feed='$allow_feed', allow_search='$allow_search', allow_poll='$allow_poll', allow_main='$allow_main', captcha='$captcha', icon='$group_icon', allow_modc='$allow_modc', allow_rating='$allow_rating', allow_offline='$allow_offline', allow_image_upload='$allow_image_upload', allow_file_upload='$allow_file_upload', allow_signature='$allow_signature', allow_url='$allow_url', news_sec_code='$news_sec_code', allow_image='$allow_image', max_signature='$max_signature', max_info='$max_info', admin_addnews='$admin_addnews', admin_editnews='$admin_editnews', admin_comments='$admin_comments', admin_categories='$admin_categories', admin_editusers='$admin_editusers', admin_wordfilter='$admin_wordfilter', admin_xfields='$admin_xfields', admin_userfields='$admin_userfields', admin_static='$admin_static', admin_editvote='$admin_editvote', admin_newsletter='$admin_newsletter', admin_blockip='$admin_blockip', admin_banners='$admin_banners', admin_rss='$admin_rss', admin_iptools='$admin_iptools', admin_rssinform='$admin_rssinform', admin_googlemap='$admin_googlemap', allow_html='$allow_html', group_prefix='$group_prefix', group_suffix='$group_suffix', allow_subscribe='$allow_subscribe', allow_image_size='$allow_image_size', cat_allow_addnews='$cat_allow_addnews', flood_news='$flood_news', max_day_news='$max_day_news', force_leech='$force_leech', edit_limit='$edit_limit', captcha_pm='$captcha_pm', max_pm_day='$max_pm_day', max_mail_day='$max_mail_day', admin_tagscloud='$admin_tagscloud', allow_vote='$allow_vote', admin_complaint='$admin_complaint', news_question='$news_question', comments_question='$comments_question', max_comment_day='$max_comment_day', max_images='$max_images', max_files='$max_files', disable_news_captcha='$disable_news_captcha', disable_comments_captcha='$disable_comments_captcha', pm_question='$pm_question', captcha_feedback='$captcha_feedback', feedback_question='$feedback_question', files_type='$files_type', max_file_size='$max_file_size', files_max_speed='$files_max_speed', allow_lostpassword='$allow_lostpassword', spamfilter='$spamfilter', allow_comments_rating='$allow_comments_rating', max_edit_days='$max_edit_days', spampmfilter='$spampmfilter', force_reg='$force_reg', force_reg_days='$force_reg_days', force_reg_group='$force_reg_group', force_news='$force_news', force_news_count='$force_news_count', force_news_group='$force_news_group', force_comments='$force_comments', force_comments_count='$force_comments_count', force_comments_group='$force_comments_group', force_rating='$force_rating', force_rating_count='$force_rating_count', force_rating_group='$force_rating_group', not_allow_cats='$not_allow_cats' WHERE id='{$id}'" );
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '77', '{$group_name}')" );
		msg( "info", $lang['all_info'], $lang['group_ok2'], "?mod=usergroup" );
	}
	clear_cache();
} elseif( $action == "add" or $action == "edit" ) {

	$id = intval( $_REQUEST['id'] );

	if (!$user_group[$id]['group_name']) {
		msg( "error", $lang['addnews_error'], $lang['group_err2'], "javascript:history.go(-1)" );
	}

	echoheader( "<i class=\"icon-user\"></i>".$lang['header_groups'], $lang['header_groups_1'] );
	
	if( ! $config['allow_cmod'] ) $warning = "<br /><font color=\"red\">" . $lang['modul_offline'] . "</font>";
	else $warning = "";

	if( ! $config['allow_subscribe'] ) $warning_1 = "<br /><font color=\"red\">" . $lang['modul_offline_1'] . "</font>";
	else $warning_1 = "";

	$group_prefix_value = htmlspecialchars( stripslashes( $user_group[$id]['group_prefix'] ), ENT_QUOTES, $config['charset'] );
	$group_suffix_value = htmlspecialchars( stripslashes( $user_group[$id]['group_suffix'] ), ENT_QUOTES, $config['charset'] );
	$files_type_value = htmlspecialchars( stripslashes( $user_group[$id]['files_type'] ), ENT_QUOTES, $config['charset'] );
		
	if( $user_group[$id]['allow_offline'] ) $allow_offline = "checked";
	if( $user_group[$id]['allow_admin'] ) $allow_admin = "checked";
	if( $user_group[$id]['allow_adds'] ) $allow_adds = "checked";
	if( $user_group[$id]['moderation'] ) $moderation = "checked";
	if( $user_group[$id]['allow_edit'] ) $allow_edit = "checked";
	if( $user_group[$id]['allow_all_edit'] ) $allow_all_edit = "checked";
	if( $user_group[$id]['allow_addc'] ) $allow_addc = "checked";
	if( $user_group[$id]['allow_editc'] ) $allow_editc = "checked";
	if( $user_group[$id]['allow_delc'] ) $allow_delc = "checked";
	if( $user_group[$id]['edit_allc'] ) $edit_allc = "checked";
	if( $user_group[$id]['del_allc'] ) $del_allc = "checked";
	if( $user_group[$id]['allow_hide'] ) $allow_hide = "checked";
	if( $user_group[$id]['allow_pm'] ) $allow_pm = "checked";
	if( $user_group[$id]['allow_vote'] ) $allow_vote = "checked";
	if( $user_group[$id]['allow_files'] ) $allow_files = "checked";
	if( $user_group[$id]['allow_feed'] ) $allow_feed = "checked";
	if( $user_group[$id]['allow_search'] ) $allow_search = "checked";
	if( $user_group[$id]['allow_rating'] ) $allow_rating = "checked";
	if( $user_group[$id]['allow_comments_rating'] ) $allow_comments_rating = "checked";
	if( $user_group[$id]['allow_short'] ) $allow_short = "checked";
	if( $user_group[$id]['time_limit'] ) $time_limit = "checked";
	if( $user_group[$id]['allow_fixed'] ) $allow_fixed = "checked";
	if( $user_group[$id]['allow_poll'] ) $allow_poll = "checked";
	if( $user_group[$id]['allow_main'] ) $allow_main = "checked";
	if( $user_group[$id]['captcha'] ) $allow_captcha = "checked";
	if( $user_group[$id]['captcha_pm'] ) $allow_captcha_pm = "checked";
	if( $user_group[$id]['allow_modc'] ) $allow_modc = "checked";
	if( $user_group[$id]['allow_image_upload'] ) $allow_image_upload = "checked";
	if( $user_group[$id]['allow_file_upload'] ) $allow_file_upload = "checked";
	if( $user_group[$id]['allow_signature'] ) $allow_signature = "checked";
	if( $user_group[$id]['allow_url'] ) $allow_url = "checked";
	if( $user_group[$id]['allow_image'] ) $allow_image = "checked";
	if( $user_group[$id]['news_sec_code'] ) $news_sec_code = "checked";
	if( $user_group[$id]['admin_addnews'] ) $admin_addnews = "checked";
	if( $user_group[$id]['admin_editnews'] ) $admin_editnews = "checked";
	if( $user_group[$id]['admin_comments'] ) $admin_comments = "checked";
	if( $user_group[$id]['admin_categories'] ) $admin_categories = "checked";
	if( $user_group[$id]['admin_editusers'] ) $admin_editusers = "checked";
	if( $user_group[$id]['admin_wordfilter'] ) $admin_wordfilter = "checked";
	if( $user_group[$id]['admin_xfields'] ) $admin_xfields = "checked";
	if( $user_group[$id]['admin_userfields'] ) $admin_userfields = "checked";
	if( $user_group[$id]['admin_static'] ) $admin_static = "checked";
	if( $user_group[$id]['admin_editvote'] ) $admin_editvote = "checked";
	if( $user_group[$id]['admin_newsletter'] ) $admin_newsletter = "checked";
	if( $user_group[$id]['admin_blockip'] ) $admin_blockip = "checked";
	if( $user_group[$id]['admin_banners'] ) $admin_banners = "checked";
	if( $user_group[$id]['admin_rss'] ) $admin_rss = "checked";
	if( $user_group[$id]['admin_iptools'] ) $admin_iptools = "checked";
	if( $user_group[$id]['admin_rssinform'] ) $admin_rssinform = "checked";
	if( $user_group[$id]['admin_googlemap'] ) $admin_googlemap = "checked";
	if( $user_group[$id]['allow_html'] ) $allow_html = "checked";
	if( $user_group[$id]['allow_subscribe'] ) $allow_subscribe = "checked";
	if( $user_group[$id]['allow_image_size'] ) $allow_image_size = "checked";
	if( $user_group[$id]['force_leech'] ) $force_leech = "checked";
	if( $user_group[$id]['admin_tagscloud'] ) $admin_tagscloud = "checked";
	if( $user_group[$id]['admin_complaint'] ) $admin_complaint = "checked";
	if( $user_group[$id]['comments_question'] ) $comments_question = "checked";
	if( $user_group[$id]['news_question'] ) $news_question = "checked";
	if( $user_group[$id]['pm_question'] ) $pm_question = "checked";
	if( $user_group[$id]['captcha_feedback'] ) $captcha_feedback = "checked";
	if( $user_group[$id]['feedback_question'] ) $feedback_question = "checked";
	if( $user_group[$id]['allow_lostpassword'] ) $allow_lostpassword = "checked";
	if( $user_group[$id]['force_reg'] ) $force_reg = "checked";
	if( $user_group[$id]['force_news'] ) $force_news = "checked";
	if( $user_group[$id]['force_comments'] ) $force_comments = "checked";
	if( $user_group[$id]['force_rating'] ) $force_rating = "checked";
	
	if( $id == 1 ) $admingroup = "disabled";
	if( $id == 5 ) $gastgroup = "disabled";
	
	$group_list = get_groups( $user_group[$id]['rid'] );
	$force_reg_group = get_groups( $user_group[$id]['force_reg_group'] );
	$force_news_group = get_groups( $user_group[$id]['force_news_group'] );
	$force_comments_group = get_groups( $user_group[$id]['force_comments_group'] );
	$force_rating_group = get_groups( $user_group[$id]['force_rating_group'] );

	$spamfilter_sel = array ('0' => '', '1' => '', '2' => '', '3' => '' );
	$spamfilter_sel[$user_group[$id]['spamfilter']] = 'selected="selected"';

	$spampmfilter_sel = array ('0' => '', '1' => '', '2' => '', '3' => '' );
	$spampmfilter_sel[$user_group[$id]['spampmfilter']] = 'selected="selected"';
		
	if( $user_group[$id]['allow_cats'] == "all" ) $allow_cats_value = "selected";
	$categories_list = CategoryNewsSelection( explode( ',', $user_group[$id]['allow_cats'] ), 0, false );

	if( $user_group[$id]['not_allow_cats'] == "" ) $not_allow_cats_value = "selected";
	$not_allow_cats_list = CategoryNewsSelection( explode( ',', $user_group[$id]['not_allow_cats'] ), 0, false );
		
	if( $user_group[$id]['cat_add'] == "all" ) $cat_add_value = "selected";
	if( $user_group[$id]['cat_allow_addnews'] == "all" ) $cat_allow_addnews_value = "selected";

	$cat_add_list = CategoryNewsSelection( explode( ',', $user_group[$id]['cat_add'] ), 0, false );
	$cat_allow_addnews_list = CategoryNewsSelection( explode( ',', $user_group[$id]['cat_allow_addnews'] ), 0, false );
	
	$max_pm_value = $user_group[$id]['max_pm'];
	$max_foto_value = $user_group[$id]['max_foto'];
	$max_signature_value = $user_group[$id]['max_signature'];
	$max_info_value = $user_group[$id]['max_info'];
	$max_pm_day_value = $user_group[$id]['max_pm_day'];
	$max_comment_day_value = $user_group[$id]['max_comment_day'];
	$max_mail_day_value = $user_group[$id]['max_mail_day'];
	$flood_news_value = $user_group[$id]['flood_news'];
	$max_images_value = $user_group[$id]['max_images'];
	$max_files_value = $user_group[$id]['max_files'];
	$max_day_news_value = $user_group[$id]['max_day_news'];
	$edit_limit_value = $user_group[$id]['edit_limit'];
	$disable_comments_captcha_value = $user_group[$id]['disable_comments_captcha'];
	$disable_news_captcha_value = $user_group[$id]['disable_news_captcha'];
	$max_file_size_value = $user_group[$id]['max_file_size'];
	$files_max_speed_value = $user_group[$id]['files_max_speed'];
	$max_edit_days_value = $user_group[$id]['max_edit_days'];
	$force_reg_days = $user_group[$id]['force_reg_days'];
	$force_news_count = $user_group[$id]['force_news_count'];
	$force_comments_count = $user_group[$id]['force_comments_count'];
	$force_rating_count = $user_group[$id]['force_rating_count'];
	
	if( $action == "add" ) {
		$submit_value = $lang['group_new'];
		$form_title = $lang['group_new1'];
		$form_action = "?mod=usergroup&amp;action=doadd";
		
		$group_name_value = "";
		$group_icon_value = "";
	
	} else {
	
		$group_name_value = htmlspecialchars( stripslashes( $user_group[$id]['group_name'] ), ENT_QUOTES, $config['charset'] );
		$group_icon_value = htmlspecialchars( stripslashes( $user_group[$id]['icon'] ), ENT_QUOTES, $config['charset'] );
		
		$form_title = $lang['group_edit1'] . $group_name_value;
		$form_action = "?mod=usergroup&amp;action=doedit&amp;id=" . $id;
		$submit_value = $lang['group_edit'];
	
	}

	echo <<<HTML
<script type="text/javascript">
	$(function(){
		$('[data-toggle="tab"]').on('shown.bs.tab', function(e) {
		  var id;
		  id = $(e.target).attr("href");
		  $(id).find(".cat_select").chosen({allow_single_deselect:true, no_results_text: '{$lang['addnews_cat_fault']}'});
		});
	});
</script>
<form action="{$form_action}" method="post">
<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
<div class="box">
		
		    <div class="box-header">
				<ul class="nav nav-tabs nav-tabs-left">
					<li class="active"><a href="#tabhome" data-toggle="tab"><i class="icon-home"></i> {$lang['tabs_gr_all']}</a></li>
					<li><a href="#tabnews" data-toggle="tab"><i class="icon-file-alt"></i> {$lang['tabs_gr_news']}</a></li>
					<li><a href="#tabcomments" data-toggle="tab"><i class="icon-pencil"></i> {$lang['tabs_gr_comments']}</a></li>
					<li><a href="#tabcaptcha" data-toggle="tab"><i class="icon-lock"></i> {$lang['tabs_gr_cap']}</a></li>
					<li><a href="#tabadmin" data-toggle="tab"><i class="icon-dashboard"></i> {$lang['tabs_gr_admin']}</a></li>
				</ul>
				<ul class="box-toolbar">
				  <li class="toolbar-link">
					  <a href="#"><i class="icon-info-sign"></i> {$form_title}</a>
				  </li>
				</ul>
			</div>
			
            <div class="box-content">
                 <div class="tab-content">			
                     <div class="tab-pane active" id="tabhome">
						
<table class="table table-normal table-hover settingsgr">
    <tr>
        <td class="white-line" style="width:58%"><h6>{$lang['group_name']}</h6><span class="note large">{$lang[hint_gtitle]}</span></td>
        <td class="white-line" style="width:42%"><input type="text" style="width:100%;" name="group_name" value="{$group_name_value}"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_icon']}</h6><span class="note large">{$lang['hint_gicon']}</span></td>
        <td><input type="text" style="width:100%;" name="group_icon" value="{$group_icon_value}"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_pref']}</h6><span class="note large">{$lang['hint_gpref']}</span></td>
        <td><input type="text" style="width:100%;" name="group_prefix" value="{$group_prefix_value}"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_suf']}</h6><span class="note large">{$lang['hint_gsuf']}</span></td>
        <td><input type="text" style="width:100%;" name="group_suffix" value="{$group_suffix_value}"></td>
    </tr>
	

    <tr>
        <td><h6>{$lang['group_lostpass']}</h6><span class="note large">{$lang['hint_glostpass']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_lostpassword" {$allow_lostpassword} value="1" ></td>
    </tr>
	

    <tr>
        <td><h6>{$lang['group_offline']}</h6><span class="note large">{$lang['hint_goffline']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_offline" {$allow_offline} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_hic']}</h6><span class="note large">{$lang['hint_gvhide']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_hide" {$allow_hide} value="1"></td>
    </tr>
 	

    <tr>
        <td><h6>{$lang['group_force_leech']}</h6><span class="note large">{$lang['hint_force_leech']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="force_leech" {$force_leech} value="1" ></td>
    </tr>
 	
   <tr>
        <td><h6>{$lang['group_svote']}</h6><span class="note large">{$lang['group_svoted']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_vote" {$allow_vote} value="1"></td>
    </tr>
	

   <tr>
        <td><h6>{$lang['group_apm']}</h6><span class="note large">{$lang['hint_gapm']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_pm" {$allow_pm} value="1" {$gastgroup}></td>
    </tr>

    <tr>
        <td><h6>{$lang['group_fpmspam']}</h6><span class="note large">{$lang['hint_fpmspam']}</span></td>
        <td><select name="spampmfilter" class="uniform">
			<option {$spampmfilter_sel['0']} value="0">{$lang['opt_sys_r1']}</option>
			<option {$spampmfilter_sel['3']} value="3">{$lang['opt_sys_r6']}</option>
            <option {$spampmfilter_sel['2']} value="2">{$lang['opt_sys_r4']}</option>
			<option {$spampmfilter_sel['1']} value="1">{$lang['opt_sys_r5']}</option>
            </select></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_afil']}</h6><span class="note large">{$lang['hint_gafile']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_files" {$allow_files} value="1"></td>
    </tr>
 	
    <tr>
        <td><h6>{$lang['a_feed']}</h6><span class="note large">{$lang['hint_gafeed']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_feed" {$allow_feed} value="1"></td>
    </tr>
 	
    <tr>
        <td><h6>{$lang['a_search']}</h6><span class="note large">{$lang['hint_gasearch']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_search" {$allow_search} value="1"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_tlim']}</h6><span class="note large">{$lang['hint_glimit']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="time_limit" {$time_limit} value="1" {$admingroup}{$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_rlim']}</h6><span class="note large">{$lang['hint_grid']}</span></td>
        <td><select name="rid" class="uniform">
           {$group_list}
            </select></td>
    </tr>

    <tr>
        <td><h6>{$lang['group_reglim']}</h6><span class="note large">{$lang['group_reglimd']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="force_reg" {$force_reg} value="1" {$admingroup}{$gastgroup}>
			{$lang['force_group_days']}<br />
			<input type="text" size="20" name="force_reg_days" value="{$force_reg_days}">
			<br />{$lang['force_group']}<br />
			<select name="force_reg_group" class="uniform">
           {$force_reg_group}
            </select>
		</td>
    </tr>
    <tr>
        <td><h6>{$lang['group_nlim']}</h6><span class="note large">{$lang['group_nlimd']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="force_news" {$force_news} value="1" {$admingroup}{$gastgroup}>
			{$lang['force_group_news']}<br />
			<input type="text" size="20" name="force_news_count" value="{$force_news_count}">
			<br />{$lang['force_group']}<br />
			<select name="force_news_group" class="uniform">
           {$force_news_group}
            </select>
		</td>
    </tr>
    <tr>
        <td><h6>{$lang['group_clim']}</h6><span class="note large">{$lang['group_climd']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="force_comments" {$force_comments} value="1" {$admingroup}{$gastgroup}>
			{$lang['force_group_comm']}<br />
			<input type="text" size="20" name="force_comments_count" value="{$force_comments_count}">
			<br />{$lang['force_group']}<br />
			<select name="force_comments_group" class="uniform">
           {$force_comments_group}
            </select>
		</td>
    </tr>
    <tr>
        <td><h6>{$lang['group_ratelim']}</h6><span class="note large">{$lang['group_ratelimd']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="force_rating" {$force_rating} value="1" {$admingroup}{$gastgroup}>
			{$lang['force_group_rating']}<br />
			<input type="text" size="20" name="force_rating_count" value="{$force_rating_count}">
			<br />{$lang['force_group']}<br />
			<select name="force_rating_group" class="uniform">
           {$force_rating_group}
            </select>
		</td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_mpmd']}</h6><span class="note large">{$lang['hint_gmpmd']}</span></td>
        <td><input type="text" size="20" name="max_pm_day" value="{$max_pm_day_value}"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_mpm']}</h6><span class="note large">{$lang['hint_gmpm']}</span></td>
        <td><input type="text" size="20" name="max_pm" value="{$max_pm_value}"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_memd']}</h6><span class="note large">{$lang['hint_memd']}</span></td>
        <td><input type="text" size="20" name="max_mail_day" value="{$max_mail_day_value}"></td>
    </tr>
	

    <tr>
        <td><h6>{$lang['group_mfot']}</h6><span class="note large">{$lang['hint_gmphoto']}</span></td>
        <td><input type="text" size="20" name="max_foto" value="{$max_foto_value}"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_max_info']}</h6><span class="note large">{$lang['hint_max_info']}</span></td>
        <td><input type="text" size="20" name="max_info" value="{$max_info_value}"></td>
    </tr>
</table>
							  

					</div>
                    <div class="tab-pane" id="tabnews" >
<table class="table table-normal table-hover settingsgr">
    <tr>
        <td class="white-line" style="width:58%"><h6>{$lang['group_ct']}</h6><span class="note large">{$lang['hint_gacats']}</span></td>
        <td class="white-line" style="width:42%"><select data-placeholder="{$lang['addnews_cat_sel']}" name="allow_cats[]" style="width:350px;" class="cat_select" multiple >
<option value="all" {$allow_cats_value}>{$lang['edit_all']}</option>
{$categories_list}
</select></td>
    </tr>

    <tr>
        <td class="white-line" style="width:58%"><h6>{$lang['group_nct']}</h6><span class="note large">{$lang['group_nctd']}</span></td>
        <td class="white-line" style="width:42%"><select data-placeholder="{$lang['addnews_cat_sel']}" name="not_allow_cats[]" style="width:350px;" class="cat_select" multiple >
<option value="" {$not_allow_cats_value}>--</option>
{$not_allow_cats_list}
</select></td>
    </tr>

    <tr>
        <td><h6>{$lang['group_aladdnews']}</h6><span class="note large">{$lang['hint_galaddnews']}</span></td>
        <td><select data-placeholder="{$lang['addnews_cat_sel']}" name="cat_allow_addnews[]" style="width:350px;" class="cat_select" multiple >
<option value="all" {$cat_allow_addnews_value}>{$lang['edit_all']}</option>
{$cat_allow_addnews_list}
</select></td>
    </tr>

	
    <tr>
        <td><h6>{$lang['group_alct']}</h6><span class="note large">{$lang['hint_gadc']}</span></td>
        <td><select data-placeholder="{$lang['addnews_cat_sel']}" name="cat_add[]" style="width:350px;" class="cat_select" multiple >
<option value="all" {$cat_add_value}>{$lang['edit_all']}</option>
{$cat_add_list}
</select></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_shid']}</h6><span class="note large">{$lang['hint_gasr']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_short" {$allow_short} value="1"></td>
    </tr>
	
     <tr>
        <td><h6>{$lang['group_poll']}</h6><span class="note large">{$lang['group_poll_hint']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_poll" {$allow_poll} value="1"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['a_rating']}</h6><span class="note large">{$lang['hint_garating']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_rating" {$allow_rating} value="1"></td>
    </tr>
	
     <tr>
        <td><h6>{$lang['group_adds']}</h6><span class="note large">{$lang['hint_gaad']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_adds" {$allow_adds} value="1" {$gastgroup}></td>
    </tr>
	
     <tr>
        <td><h6>{$lang['group_adds_html']}</h6><span class="note large">{$lang['hint_gaadhtml']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_html" {$allow_html} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_moder']}</h6><span class="note large">{$lang['hint_gmod']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="moderation" {$moderation} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_main']}</h6><span class="note large">{$lang['group_main_hint']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_main" {$allow_main} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_fixed']}</h6><span class="note large">{$lang['hint_gfixed']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_fixed" {$allow_fixed} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['opt_sys_aiu']}</h6><span class="note large">{$lang['opt_sys_aiud']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_image_upload" {$allow_image_upload} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_image_size']}</h6><span class="note large">{$lang['hint_image_size']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_image_size" {$allow_image_size} value="1" {$gastgroup}></td>
    </tr>

	
    <tr>
        <td><h6>{$lang['opt_sys_file']}</h6><span class="note large">{$lang['opt_sys_filed']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_file_upload" {$allow_file_upload} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_max_images']}</h6><span class="note large">{$lang['hint_max_images']}</span></td>
        <td><input type="text" size="20" name="max_images" value="{$max_images_value}"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_max_files']}</h6><span class="note large">{$lang['hint_max_files']}</span></td>
        <td><input type="text" size="20" name="max_files" value="{$max_files_value}"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['opt_sys_file1']}</h6><span class="note large">{$lang['opt_sys_file1d']}</span></td>
        <td><input type="text" size="60" name="files_type" value="{$files_type_value}"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['opt_sys_maxfile']}</h6><span class="note large">{$lang['opt_sys_maxfiled']}</span></td>
        <td><input type="text" size="20" name="max_file_size" value="{$max_file_size_value}"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['opt_sys_file5']}</h6><span class="note large">{$lang['opt_sys_file5d']}</span></td>
        <td><input type="text" size="20" name="files_max_speed" value="{$files_max_speed_value}"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_flood_news']}</h6><span class="note large">{$lang['hint_flood_news']}</span></td>
        <td><input type="text" size="20" name="flood_news" value="{$flood_news_value}"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_day_news']}</h6><span class="note large">{$lang['hint_day_news']}</span></td>
        <td><input type="text" size="20" name="max_day_news" value="{$max_day_news_value}"></td>
    </tr>
    <tr>
        <td><h6>{$lang['group_edit_days']}</h6><span class="note large">{$lang['hint_edit_days']}</span></td>
        <td><input type="text" size="20" name="max_edit_days" value="{$max_edit_days_value}"></td>
    </tr>
</table>

                     </div>
                    <div class="tab-pane" id="tabcomments" >
<table class="table table-normal table-hover settingsgr">
    <tr>
        <td class="white-line" style="width:58%"><h6>{$lang['group_addc']}</h6><span class="note large">{$lang['hint_gac']}</span></td>
        <td class="white-line" style="width:42%"><input class="iButton-icons-tab" type="checkbox" name="allow_addc" {$allow_addc} value="1"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_fspam']}</h6><span class="note large">{$lang['hint_fspam']}</span></td>
        <td><select name="spamfilter" class="uniform">
			<option {$spamfilter_sel['0']} value="0">{$lang['opt_sys_r1']}</option>
			<option {$spamfilter_sel['3']} value="3">{$lang['opt_sys_r6']}</option>
            <option {$spamfilter_sel['2']} value="2">{$lang['opt_sys_r4']}</option>
			<option {$spamfilter_sel['1']} value="1">{$lang['opt_sys_r5']}</option>
            </select></td>
    </tr>

    <tr>
        <td><h6>{$lang['c_rating']}</h6><span class="note large">{$lang['hint_gcrating']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_comments_rating" {$allow_comments_rating} value="1"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['opt_sys_subs']}</h6><span class="note large">{$lang['opt_sys_subsd']}</span>{$warning_1}</td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_subscribe" {$allow_subscribe} value="1" {$gastgroup}></td>
    </tr>
	

    <tr>
        <td><h6>{$lang['group_signature']}</h6><span class="note large">{$lang['hint_signature']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_signature" {$allow_signature} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_max_signature']}</h6><span class="note large">{$lang['hint_max_signature']}</span></td>
        <td><input type="text" size="20" name="max_signature" value="{$max_signature_value}"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_url']}</h6><span class="note large">{$lang['hint_group_url']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_url" {$allow_url} value="1"></td>
    </tr>
	

    <tr>
        <td><h6>{$lang['group_image']}</h6><span class="note large">{$lang['hint_group_image']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_image" {$allow_image} value="1"></td>
    </tr>
	

    <tr>
        <td><h6>{$lang['group_modc']}</h6><span class="note large">{$lang['hint_modc']}</span>{$warning}</td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_modc" {$allow_modc} value="1"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_editc']}</h6><span class="note large">{$lang['hint_gec']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_editc" {$allow_editc} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_delc']}</h6><span class="note large">{$lang['hint_gdc']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_delc" {$allow_delc} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_edit_limit']}</h6><span class="note large">{$lang['hint_edit_limit']}</span></td>
        <td><input type="text" size="20" name="edit_limit" value="{$edit_limit_value}"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_mcmd']}</h6><span class="note large">{$lang['hint_gmcmd']}</span></td>
        <td><input type="text" size="20" name="max_comment_day" value="{$max_comment_day_value}"></td>
    </tr>

	
    <tr>
        <td><h6>{$lang['group_allc']}</h6><span class="note large">{$lang['hint_gaec']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="edit_allc" {$edit_allc} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_dllc']}</h6><span class="note large">{$lang['hint_gadcom']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="del_allc" {$del_allc} value="1" {$gastgroup}></td>
    </tr>
</table>						

                     </div>
                    <div class="tab-pane" id="tabcaptcha" >
<table class="table table-normal table-hover settingsgr">
     <tr>
        <td class="white-line" style="width:58%"><h6>{$lang['opt_sys_news_c']}</h6><span class="note large">{$lang['hint_scode']}</span></td>
        <td class="white-line" style="width:42%"><input class="iButton-icons-tab" type="checkbox" name="news_sec_code" {$news_sec_code} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['opt_sys_qsn']}</h6><span class="note large">{$lang['hint_qcode']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="news_question" {$news_question} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_d_nc']}</h6><span class="note large">{$lang['hint_d_nc']}</span></td>
        <td><input type="text" size="20" name="disable_news_captcha" value="{$disable_news_captcha_value}"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['opt_sys_code_com']}</h6><span class="note large">{$lang['opt_sys_codecd']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="captcha" {$allow_captcha} value="1"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['opt_sys_qsc']}</h6><span class="note large">{$lang['hint_qcode_1']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="comments_question" {$comments_question} value="1"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_d_cc']}</h6><span class="note large">{$lang['hint_d_cc']}</span></td>
        <td><input type="text" size="20" name="disable_comments_captcha" value="{$disable_comments_captcha_value}"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['opt_sys_code_pm']}</h6><span class="note large">{$lang['opt_sys_code_pmd']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="captcha_pm" {$allow_captcha_pm} value="1"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['opt_sys_qspm']}</h6><span class="note large">{$lang['hint_qcode_2']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="pm_question" {$pm_question} value="1"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['opt_sys_code_feed']}</h6><span class="note large">{$lang['opt_sys_code_feedd']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="captcha_feedback" {$captcha_feedback} value="1"></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['opt_sys_qsfeed']}</h6><span class="note large">{$lang['hint_qcode_3']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="feedback_question" {$feedback_question} value="1"></td>
    </tr>
</table>						
                     </div>
                    <div class="tab-pane" id="tabadmin" >

<table class="table table-normal table-hover settingsgr">
    <tr>
        <td class="white-line" style="width:58%"><h6>{$lang['group_aadm']}</h6><span class="note large">{$lang['hint_gadmin']}</span></td>
        <td class="white-line" style="width:42%"><input class="iButton-icons-tab" type="checkbox" name="allow_admin" {$allow_admin} value="1" {$gastgroup}{$admingroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_a_addnews']}</h6><span class="note large">{$lang['group_h_addnews']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="admin_addnews" {$admin_addnews} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_a_editnews']}</h6><span class="note large">{$lang['group_h_editnews']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="admin_editnews" {$admin_editnews} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_edit2']}</h6><span class="note large">{$lang['hint_gned']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_edit" {$allow_edit} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_edit3']}</h6><span class="note large">{$lang['hint_gnaed']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="allow_all_edit" {$allow_all_edit} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_a_comments']}</h6><span class="note large">{$lang['group_h_comments']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="admin_comments" {$admin_comments} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_a_categories']}</h6><span class="note large">{$lang['group_h_categories']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="admin_categories" {$admin_categories} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_a_editusers']}</h6><span class="note large">{$lang['group_h_editusers']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="admin_editusers" {$admin_editusers} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_a_wordfilter']}</h6><span class="note large">{$lang['group_h_wordfilter']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="admin_wordfilter" {$admin_wordfilter} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_a_xfields']}</h6><span class="note large">{$lang['group_h_xfields']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="admin_xfields" {$admin_xfields} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_a_userfields']}</h6><span class="note large">{$lang['group_h_userfields']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="admin_userfields" {$admin_userfields} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_a_static']}</h6><span class="note large">{$lang['group_h_static']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="admin_static" {$admin_static} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_a_editvote']}</h6><span class="note large">{$lang['group_h_editvote']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="admin_editvote" {$admin_editvote} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_a_newsletter']}</h6><span class="note large">{$lang['group_h_newsletter']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="admin_newsletter" {$admin_newsletter} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_a_blockip']}</h6><span class="note large">{$lang['group_h_blockip']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="admin_blockip" {$admin_blockip} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_a_banners']}</h6><span class="note large">{$lang['group_h_banners']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="admin_banners" {$admin_banners} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_a_rss']}</h6><span class="note large">{$lang['group_h_rss']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="admin_rss" {$admin_rss} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_a_iptools']}</h6><span class="note large">{$lang['group_h_iptools']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="admin_iptools" {$admin_iptools} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_a_rssinform']}</h6><span class="note large">{$lang['group_h_rssinform']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="admin_rssinform" {$admin_rssinform} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_a_googlemap']}</h6><span class="note large">{$lang['group_h_googlemap']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="admin_googlemap" {$admin_googlemap} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_a_tagscloud']}</h6><span class="note large">{$lang['group_h_tagscloud']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="admin_tagscloud" {$admin_tagscloud} value="1" {$gastgroup}></td>
    </tr>
	
    <tr>
        <td><h6>{$lang['group_a_complaint']}</h6><span class="note large">{$lang['group_h_complaint']}</span></td>
        <td><input class="iButton-icons-tab" type="checkbox" name="admin_complaint" {$admin_complaint} value="1" {$gastgroup}></td>
    </tr>
</table>						

                     </div>
				</div>
				<div class="padded">
					<input class="btn btn-green" type="submit" value="{$submit_value}">
				</div>
			</div>
</div>


</form>
HTML;
	
	echofooter();
} else {
	
	echoheader( "<i class=\"icon-user\"></i>".$lang['header_groups'], $lang['header_groups_1'] );
	
	$db->query( "SELECT user_group, count(*) as count FROM " . USERPREFIX . "_users GROUP BY user_group" );
	$entries = "";

	while ( $row = $db->get_row() )
		$count_list[$row['user_group']] = $row['count'];
	$db->free();
	
	foreach ( $user_group as $group ) {
		$count = intval( $count_list[$group['id']] );

		if ( $group['id'] > 5 ) {
			$dlink="<li><a href=\"?mod=usergroup&action=del&user_hash={$dle_login_hash}&id={$group['id']}\"><i class=\"icon-trash\"></i> {$lang['group_sel2']}</a></li>";
		} else {
			$dlink="<li><a href=\"#\"><i class=\"icon-trash\"></i> {$lang['group_sel3']}</a></li>";		
		}
		
		if( $group['allow_admin'] ) $group['group_name'] .= " (<span class=\"status-error\">{$lang['have_adm']}</span>)";
		
		$menu_link = <<<HTML
        <div class="btn-group">
          <button class="btn btn-default dropdown-toggle" data-toggle="dropdown"><i class="icon-pencil"></i> {$lang['filter_action']} <span class="caret"></span></button>
          <ul class="dropdown-menu text-left pull-right">
            <li><a href="?mod=usergroup&action=edit&id={$group['id']}"><i class="icon-pencil"></i> {$lang['group_sel1']}</a></li>
			<li class="divider"></li>
            {$dlink}
          </ul>
        </div>
HTML;

		$entries .= "
    <tr>
    <td height=22 align=\"center\">{$group['id']}</td>
    <td>{$group['group_name']}</td>
    <td align=\"center\">{$count}</td>
    <td>{$menu_link}</td>
     </tr>";
	}
	
	echo <<<HTML
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['group_list']}</div>
  </div>
  <div class="box-content">

    <table class="table table-normal table-hover">
      <thead>
      <tr>
        <td style="width: 60px">ID</td>
        <td>{$lang['group_name']}</td>
        <td>{$lang['group_sel4']}</td>
        <td style="width: 150px"></td>
      </tr>
      </thead>
	  <tbody>
		{$entries}
	  </tbody>
	</table>
	<div class="box-footer padded">
		<input onclick="document.location='?mod=usergroup&action=selectgroup'" type="button" class="btn btn-blue" value="{$lang['group_sel5']}">
	</div>	
   </div>
</div>	
HTML;
	
	echofooter();
}
?>