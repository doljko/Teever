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
 Файл: stats.php
-----------------------------------------------------
 Назначение: статистика сайта
=====================================================
*/
if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

$tpl->result['content'] = dle_cache( "stats", $config['skin'], true );

if( ! $tpl->result['content'] ) {

	$db->query( "SHOW TABLE STATUS FROM `" . DBNAME . "`" );
	$mysql_size = 0;
	while ( $r = $db->get_row() ) {

		if( strpos( $r['Name'], PREFIX . "_" ) !== false ) $mysql_size += $r['Data_length'] + $r['Index_length'];

	}
	$db->free();
	
	$mysql_size = formatsize( $mysql_size );
	
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post" );
	$stats_news = $row['count'];
	
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE approve ='1'" );
	$stats_approve = $row['count'];
	
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_comments" );
	$count_comments = $row['count'];
	
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . USERPREFIX . "_users" );
	$stats_users = $row['count'];
	
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . USERPREFIX . "_users WHERE banned='yes'" );
	$stats_banned = $row['count'];
	
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE allow_main ='1' AND approve ='1'" );
	$stats_main = $row['count'];
	
	$temp_date = date( 'Y-m-d H:i', $_TIME - (3596 * 24) );
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE date >= '$temp_date'AND date <= '$temp_date' + INTERVAL 24 HOUR AND approve ='1'" );
	$stats_day = $row['count'];
	
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_comments WHERE date >= '$temp_date'AND date <= '$temp_date' + INTERVAL 24 HOUR" );
	$comments_day = $row['count'];
	
	$temp_date = date( 'Y-m-d H:i', $_TIME - (3600 * 24 * 7) );
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE date >= '$temp_date'AND date <= '$temp_date' + INTERVAL 7 DAY AND approve ='1'" );
	$stats_week = $row['count'];
	
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_comments WHERE date >= '$temp_date'AND date <= '$temp_date' + INTERVAL 7 DAY" );
	$comments_week = $row['count'];
	
	$temp_date = date( 'Y-m-d H:i', $_TIME - (3600 * 24 * 31) );
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE date >= '$temp_date'AND date <= '$temp_date' + INTERVAL 31 DAY AND approve ='1'" );
	$stats_month = $row['count'];
	
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_comments WHERE date >= '$temp_date'AND date <= '$temp_date' + INTERVAL 31 DAY" );
	$comments_month = $row['count'];
	
	$temp_date = $_TIME - (3600 * 24);
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . USERPREFIX . "_users WHERE reg_date > '$temp_date'" );
	$user_day = $row['count'];
	
	$temp_date = $_TIME - (3600 * 24 * 7);
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . USERPREFIX . "_users WHERE reg_date > '$temp_date'" );
	$user_week = $row['count'];
	
	$temp_date = $_TIME - (3600 * 24 * 31);
	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . USERPREFIX . "_users WHERE reg_date > '$temp_date'" );
	$user_month = $row['count'];
	
	$tpl->load_template( 'stats.tpl' );
	
	$tpl->set( '{datenbank}', $mysql_size );
	$tpl->set( '{news_num}', $stats_news );
	$tpl->set( '{news_allow}', $stats_approve );
	$tpl->set( '{comm_num}', $count_comments );
	$tpl->set( '{user_num}', $stats_users );
	$tpl->set( '{user_banned}', $stats_banned );
	$tpl->set( '{news_main}', $stats_main );
	$tpl->set( '{news_moder}', $stats_news - $stats_approve );
	
	$tpl->set( '{news_day}', $stats_day );
	$tpl->set( '{news_week}', $stats_week );
	$tpl->set( '{news_month}', $stats_month );
	
	$tpl->set( '{comm_day}', $comments_day );
	$tpl->set( '{comm_week}', $comments_week );
	$tpl->set( '{comm_month}', $comments_month );
	
	$tpl->set( '{user_day}', $user_day );
	$tpl->set( '{user_week}', $user_week );
	$tpl->set( '{user_month}', $user_month );
	
	$db->query( "SELECT user_id, name, user_group, reg_date, lastdate, news_num, comm_num FROM " . USERPREFIX . "_users WHERE news_num > '0' ORDER BY news_num DESC LIMIT 0,10" );
	
	$top_table = "<thead><tr><td>{$lang['top_name']}</td><td align=\"center\">{$lang['top_status']}</td><td align=\"center\">{$lang['top_reg']}</td><td align=\"center\">{$lang['top_last']}</td><td align=\"center\">{$lang['top_nnum']}</td><td align=\"center\">{$lang['top_cnum']}</td><td align=\"center\">{$lang['top_pm']}</td></tr></thead>";
	
	while ( $row = $db->get_row() ) {
		
		$registration = langdate( $config['timestamp_active'], $row['reg_date'] );
		$last = langdate( $config['timestamp_active'], $row['lastdate'] );

		if( $config['allow_alt_url'] ) {
			
			$user_name = $config['http_home_url'] . "user/" . urlencode( $row['name'] ) . "/";
			$user_name = "onclick=\"ShowProfile('" . urlencode( $row['name'] ) . "', '" . htmlspecialchars( $user_name, ENT_QUOTES, $config['charset'] ) . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\"";
			$user_name = "<a {$user_name} class=\"pm_list\" href=\"" . $config['http_home_url'] . "user/" . urlencode( $row['name'] ) . "/\">" . $row['name'] . "</a>";
		
		} else {
			
			$user_name = "$PHP_SELF?subaction=userinfo&user=" . urlencode( $row['name'] );
			$user_name = "onclick=\"ShowProfile('" . urlencode( $row['name'] ) . "', '" . htmlspecialchars( $user_name, ENT_QUOTES, $config['charset'] ) . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\"";
			$user_name = "<a {$user_name} class=\"pm_list\" href=\"$PHP_SELF?subaction=userinfo&amp;user=" . urlencode( $row['name'] ) . "\">" . $row['name'] . "</a>";

		}
				
		$user_pm = "<a href=\"$PHP_SELF?do=pm&amp;doaction=newpm&amp;username=" . urlencode($row['name']) . "\">{$lang['top_pm']}</a>";
		
		$top_table .= "<tr><td>{$user_name}</td><td align=\"center\">{$user_group[$row['user_group']]['group_prefix']}{$user_group[$row['user_group']]['group_name']}{$user_group[$row['user_group']]['group_suffix']}</td><td align=\"center\">{$registration}</td><td align=\"center\">{$last}</td><td align=\"center\">{$row['news_num']}</td><td align=\"center\">{$row['comm_num']}</td><td align=\"center\">[ {$user_pm} ]</td></tr>";
	
	}
	
	$db->free();
	
	$tpl->set( '{topusers}', $top_table );
	
	$tpl->compile( 'content' );
	$tpl->clear();
	
	create_cache( "stats", $tpl->result['content'], $config['skin'], true );
}
?>