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
 Файл: calendar.php
-----------------------------------------------------
 Назначение: Вывод календаря и архивов на сайте
=====================================================
*/

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

$is_change = false;

if (!$config['allow_cache']) { $config['allow_cache'] = 1; $is_change = true;}

# Генерируем календарь
function cal($cal_month, $cal_year, $events) {
	global $f, $r, $year, $month, $config, $lang, $langdateshortweekdays, $PHP_SELF;
	
	$next = true;
	
	if( intval( $cal_year . $cal_month ) >= date( 'Ym' ) AND !$config['news_future'] ) $next = false;

	$cur_date=date( 'Ymj', time() );
	$cal_date = $cal_year.$cal_month;

	$cal_month = intval( $cal_month );
	$cal_year = intval( $cal_year );
	
	if( $cal_month < 0 ) $cal_month = 1;
	if( $cal_year < 0 ) $cal_year = 2008;
	
	$first_of_month = mktime( 0, 0, 0, $cal_month, 7, $cal_year );
	$maxdays = date( 't', $first_of_month ) + 1; // 28-31
	$prev_of_month = mktime( 0, 0, 0, ($cal_month - 1), 7, $cal_year );
	$next_of_month = mktime( 0, 0, 0, ($cal_month + 1), 7, $cal_year );
	$cal_day = 1;
	$weekday = date( 'w', $first_of_month ); // 0-6
	

	if( $config['allow_alt_url'] ) {
		
		$date_link['prev'] = '<a class="monthlink" onclick="doCalendar(' . date( "'m','Y'", $prev_of_month ) . ',\'right\'); return false;" href="' . $config['http_home_url'] . date( 'Y/m/', $prev_of_month ) . '" title="' . $lang['prev_moth'] . '">&laquo;</a>&nbsp;&nbsp;&nbsp;&nbsp;';
		$date_link['next'] = '&nbsp;&nbsp;&nbsp;&nbsp;<a class="monthlink" onclick="doCalendar(' . date( "'m','Y'", $next_of_month ) . ',\'left\'); return false;" href="' . $config['http_home_url'] . date( 'Y/m/', $next_of_month ) . '" title="' . $lang['next_moth'] . '">&raquo;</a>';
	
	} else {
		
		$date_link['prev'] = '<a class="monthlink" onclick="doCalendar(' . date( "'m','Y'", $prev_of_month ) . ',\'right\'); return false;" href="' . $PHP_SELF . '?year=' . date( "Y", $prev_of_month ) . '&amp;month=' . date( "m", $prev_of_month ) . '" title="' . $lang['prev_moth'] . '">&laquo;</a>&nbsp;&nbsp;&nbsp;&nbsp;';
		$date_link['next'] = '&nbsp;&nbsp;&nbsp;&nbsp;<a class="monthlink" onclick="doCalendar(' . date( "'m','Y'", $next_of_month ) . ',\'left\'); return false;" href="' . $PHP_SELF . '?year=' . date( "Y", $next_of_month ) . '&amp;month=' . date( "m", $next_of_month ) . '" title="' . $lang['next_moth'] . '">&raquo;</a>';
	
	}
	
	if( ! $next ) $date_link['next'] = "&nbsp;&nbsp;&nbsp;&nbsp;&raquo;";
	
	$buffer = '<div id="calendar-layer"><table id="calendar" class="calendar"><tr><th colspan="7" class="monthselect">' . $date_link['prev'] . langdate( 'F', $first_of_month, true ) . ' ' . $cal_year . $date_link['next'] . '</th></tr><tr>';
	
	$buffer = str_replace( $f, $r, $buffer );
	
	# Дни недели: рабочая неделя
	for($it = 1; $it < 6; $it ++)
		$buffer .= '<th class="workday">' . $langdateshortweekdays[$it] . '</th>';
		
	# Дни недели: субботний и воскресный дни
	$buffer .= '<th class="weekday">' . $langdateshortweekdays[6] . '</th>';
	$buffer .= '<th class="weekday">' . $langdateshortweekdays[0] . '</th>';
	
	$buffer .= '</tr><tr>';
	
	if( $weekday > 0 ) {
		$buffer .= '<td colspan="' . $weekday . '">&nbsp;</td>';
	}
	
	while ( $maxdays > $cal_day ) {

		$cal_pos = $cal_date.$cal_day;

		if( $weekday == 7 ) {
			$buffer .= '</tr><tr>';
			$weekday = 0;
		}
		
		# В данный день есть новость
		if( isset( $events[$cal_day] ) ) {
			$date['title'] = langdate( 'd F Y', $events[$cal_day], true );
			
			# Если суббота и воскресенье.
			if( $weekday == '5' or $weekday == '6' ) {
								
				if( $config['allow_alt_url'] ) $buffer .= '<td '.(($cal_pos==$cur_date)?' class="day-active day-current" ':' class="day-active" ').'><a class="day-active" href="' . $config['http_home_url'] . '' . date( "Y/m/d", $events[$cal_day] ) . '/" title="' . $lang['cal_post'] . ' ' . $date['title'] . '">' . $cal_day . '</a></td>';
				else $buffer .= '<td '.(($cal_pos==$cur_date)?' class="day-active day-current" ':' class="day-active" ').'><a class="day-active" href="' . $PHP_SELF . '?year=' . date( "Y", $events[$cal_day] ) . '&amp;month=' . date( "m", $events[$cal_day] ) . '&day=' . date( "d", $events[$cal_day] ) . '" title="' . $lang['cal_post'] . ' ' . $date['title'] . '">' . $cal_day . '</a></td>';
			
			} 

			# Рабочии дни.
			else {
				
				if( $config['allow_alt_url'] ) $buffer .= '<td '.(($cal_pos==$cur_date)?' class="day-active-v day-current" ':' class="day-active-v" ').'><a class="day-active-v" href="' . $config['http_home_url'] . '' . date( "Y/m/d", $events[$cal_day] ) . '/" title="' . $lang['cal_post'] . ' ' . $date['title'] . '">' . $cal_day . '</a></td>';
				else $buffer .= '<td '.(($cal_pos==$cur_date)?' class="day-active-v day-current" ':' class="day-active-v" ').'><a class="day-active-v" href="' . $PHP_SELF . '?year=' . date( "Y", $events[$cal_day] ) . '&amp;month=' . date( "m", $events[$cal_day] ) . '&day=' . date( "d", $events[$cal_day] ) . '" title="' . $lang['cal_post'] . ' ' . $date['title'] . '">' . $cal_day . '</a></td>';
			
			}
		} 

		# В данный день новостей нет.
		else {
			
			# Если суббота воскресенье
			if( $weekday == "5" or $weekday == "6" ) {
				$buffer .= '<td '.(($cal_pos==$cur_date)?' class="weekday day-current" ':' class="weekday" ').'>' . $cal_day . '</td>';
			} 

			# Дни, когда ничего нет
			else {
				$buffer .= '<td '.(($cal_pos==$cur_date)?' class="day day-current" ':' class="day" ').'>' . $cal_day . '</td>';
			}
		}
		
		$cal_day ++;
		$weekday ++;
	}
	
	if( $weekday != 7 ) {
		$buffer .= '<td colspan="' . (7 - $weekday) . '">&nbsp;</td>';
	}
	
	return $buffer . '</tr></table></div>';
}

# Календарь
if( $config['allow_calendar'] ) {
	
	$events = array ();
	
	$thisdate = date( "Y-m-d H:i:s", $_TIME );
	if( $config['no_date'] AND !$config['news_future'] ) $where_date = " AND date < '" . $thisdate . "'";
	else $where_date = "";
	
	$this_month = date( 'm', $_TIME );
	$this_year = date( 'Y', $_TIME );
	$sql = "";
	
	if( $year != '' and $month != '' ) $cache_id = $config['skin'] . $month . $year;
	else $cache_id = $config['skin'] . $this_month . $this_year;
	
	$tpl->result['calendar'] = dle_cache( "calendar", $cache_id );
	
	if( ! $tpl->result['calendar'] ) {
		
		if( $year != '' and $month != '' ) {

			$month = totranslit($month, true, false);

			if( ($year == $this_year and $month < $this_month) or ($year < $this_year) ) {
				$where_date = "";
				$approve = "";
			} else {
				$approve = " AND approve=1";
			}
			
			$sql = "SELECT DISTINCT DAYOFMONTH(date) as day FROM " . PREFIX . "_post WHERE date >= '{$year}-{$month}-01' AND date < '{$year}-{$month}-01' + INTERVAL 1 MONTH" . $approve . $where_date;
			
			$this_month = $month;
			$this_year = $year;
		
		} else {
			
			$sql = "SELECT DISTINCT DAYOFMONTH(date) as day FROM " . PREFIX . "_post WHERE date >= '{$this_year}-{$this_month}-01' AND date < '{$this_year}-{$this_month}-01' + INTERVAL 1 MONTH AND approve=1" . $where_date;
		
		}
		
			
		$db->query( $sql );
			
		while ( $row = $db->get_row() ) {
			$events[$row['day']] = strtotime( $this_year . "-" . $this_month . "-" . $row['day'] );
		}
			
		$db->free();

		
		$tpl->result['calendar'] = cal( $this_month, $this_year, $events );
		create_cache( "calendar", $tpl->result['calendar'], $cache_id );
	}

}

# Выводим архивы
if( $config['allow_archives'] ) {
	
	$tpl->result['archive'] = dle_cache( "archives", $config['skin'] );
	
	if( ! $tpl->result['archive'] ) {
		
		$f2 = array ('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12' );
		$f3 = array ('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' );
		
		if( $config['no_date'] AND !$config['news_future'] ) {
			$thisdate = date( "Y-m-d H:i:s", $_TIME );
			$where_date = " AND date < '" . $thisdate . "'";
		} else
			$where_date = "";
		
		$db->query( "SELECT DATE_FORMAT(date,'%b %Y') AS m_date, MAX(date) AS max, COUNT(id) AS cnt FROM " . PREFIX . "_post WHERE approve=1" . $where_date . " GROUP BY m_date ORDER BY max desc" );
		
		$news_archive = array ();
		
		while ( $row = $db->get_row() ) {
			
			$arch_title['ru'] = str_replace( $f3, $r, $row['m_date'] );
			$arch_title['en'] = str_replace( $f3, $f2, $row['m_date'] );
			$arch_url = explode( " ", $arch_title['en'] );
			$arch_title['en'] = $arch_url[1] . "/" . $arch_url[0];
						
			if( $config['allow_alt_url'] ) $news_archive[] = '<a class="archives" href="' . $config['http_home_url'] . $arch_title['en'] . '/"><b>' . $arch_title['ru'] . ' (' . $row['cnt'] . ')</b></a>';
			else $news_archive[] = "<a class=\"archives\" href=\"$PHP_SELF?year=$arch_url[1]&amp;month=$arch_url[0]\"><b>" . $arch_title['ru'] . " (" . $row['cnt'] . ")</b></a>";
		
		}
		
		$db->free();
		
		$i = count( $news_archive );
		
		if( $i > 6 ) {
			$news_archive[6] = "<div id=\"dle_news_archive\" style=\"display:none;\">" . $news_archive[6];
			$news_archive[] = "</div><div id=\"dle_news_archive_link\" ><br /><a class=\"archives\" onclick=\"$('#dle_news_archive').toggle('blind',{},700); return false;\" href=\"#\">" . $lang['show_archive'] . "</a></div>";
		}
		
		if( $i ) $tpl->result['archive'] = implode( "<br />", $news_archive );
		else $tpl->result['archive'] = "";
		
		create_cache( "archives", $tpl->result['archive'], $config['skin'] );
	}

}

if ($is_change) $config['allow_cache'] = false;

?>