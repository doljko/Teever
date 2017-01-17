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
 Файл: check.php
-----------------------------------------------------
 Назначение: Анализ производительности скрипта
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}
if( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['addnews_denied'], $lang['db_denied'] );
}


if ( file_exists( ROOT_DIR . '/language/' . $selected_language . '/admincheck.lng' ) ) {
	require_once (ROOT_DIR . '/language/' . $selected_language . '/admincheck.lng');
}

$result = array();

foreach($user_group as $value) {

	if ( $value['allow_cats'] != "all" OR $value['not_allow_cats'] != "" ) {

		$result[] = "<div class=\"alert alert-error\" style=\"padding:10px; margin-bottom:10px;\">".str_replace("{name}", $value['group_name'],$lang['admin_check_32'])."</div>";

	}

}

if ( $config['allow_cache'] AND $config['allow_change_sort'] ) {

	$result[] = "<div class=\"alert alert-error\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_28']}</div>";

}

if ( $config['allow_tags'] ) {

	$result[] = "<div class=\"alert alert-error\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_27']}</div>";

}

if ( $config['allow_archives'] ) {

	$result[] = "<div class=\"alert alert-error\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_25']}</div>";

}

if ( $config['allow_calendar'] ) {

	$result[] = "<div class=\"alert alert-error\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_24']}</div>";

}

if ( $config['allow_read_count'] AND !$config['cache_count'] ) {

	$result[] = "<div class=\"alert alert-error\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_23']}</div>";

}

if ( $config['allow_cmod'] ) {

	$result[] = "<div class=\"alert alert-error\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_19']}</div>";

}

if ( $config['no_date'] ) {

	$result[] = "<div class=\"alert alert-error\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_15']}</div>";

}

if ( $config['related_news'] ) {

	$result[] = "<div class=\"alert alert-error\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_14']}</div>";

}

if ( $config['allow_multi_category'] ) {

	$result[] = "<div class=\"alert alert-error\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_13']}</div>";

}

if ( !$config['allow_cache'] ) {

	$result[] = "<div class=\"alert alert-error\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_12']}</div>";

}

if ( $config['fast_search'] ) {

	$result[] = "<div class=\"alert alert-error\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_10']}</div>";

}

if ( $config['full_search'] ) {

	$result[] = "<div class=\"alert alert-error\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_9']}</div>";

}

if ( $config['online_status'] ) {

	$result[] = "<div class=\"alert alert-error\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_34']}</div>";

}

if ( $config['allow_subscribe'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_33']}</div>";

}

if ( $config['allow_skin_change'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_30']}</div>";

}

if ( $config['files_allow'] AND $config['files_count'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_29']}</div>";

}

if ( $config['rss_informer'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_26']}</div>";

}

if ( $config['allow_read_count'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_22']}</div>";

}

if ( $config['allow_topnews'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_21']}</div>";

}

if ( $config['allow_banner'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_18']}</div>";

}

if ( $config['allow_fixed'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_16']}</div>";

}

if ( $config['js_min'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_8']}</div>";

}

if ( $config['allow_registration'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_11']}</div>";

}

if ( $config['allow_gzip'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_7']}</div>";

}

if ( $config['allow_comments'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_6']}</div>";

}

if ( $config['show_sub_cats'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_4']}</div>";

}

if ( $config['mail_pm'] ) {

	$result[] = "<div class=\"alert alert-success\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_31']}</div>";

}

if ( $config['allow_votes'] ) {

	$result[] = "<div class=\"alert alert-success\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_20']}</div>";

}

if ( $config['speedbar'] ) {

	$result[] = "<div class=\"alert alert-success\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_17']}</div>";

}

if ( $config['allow_admin_wysiwyg'] OR $config['allow_site_wysiwyg'] OR $config['allow_quick_wysiwyg'] OR $config['allow_comments_wysiwyg'] > 0 ) {

	$result[] = "<div class=\"alert alert-success\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_3']}</div>";

}

if ( $config['short_rating'] ) {

	$result[] = "<div class=\"alert alert-success\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_5']}</div>";

}

if ( count($result) ) {
	$result = implode("", $result);
} else 	$result = "<div class=\"alert alert-success\" style=\"padding:10px;\">{$lang['admin_check_2']}</div>";

echoheader( "<i class=\"icon-leaf\"></i>".$lang['opt_check'], $lang['opt_check'] );
	
	echo <<<HTML
<div class="box">
  <div class="box-header">
    <div class="title">{$lang['opt_check']}</div>
  </div>
  <div class="box-content">
	<div class="row box-section">{$lang['admin_check_1']}
	
	</div>
	<div class="row box-section">
	{$result}
	
	</div>
	
   </div>
</div>	
HTML;

echofooter();
?>