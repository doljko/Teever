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
 Назначение: AJAX для вывода календаря
=====================================================
*/

@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

define( 'DATALIFEENGINE', true );
define( 'ROOT_DIR', substr( dirname(  __FILE__ ), 0, -12 ) );
define( 'ENGINE_DIR', ROOT_DIR . '/engine' );

include ENGINE_DIR . '/data/config.php';

date_default_timezone_set ( $config['date_adjust'] );

if( $config['http_home_url'] == "" ) {
	
	$config['http_home_url'] = explode( "engine/ajax/calendar.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset( $config['http_home_url'] );
	$config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';

$PHP_SELF = $config['http_home_url'] . "index.php";

require_once ENGINE_DIR . '/modules/functions.php';

dle_session();

$_COOKIE['dle_skin'] = trim(totranslit( $_COOKIE['dle_skin'], false, false ));

if( $_COOKIE['dle_skin'] ) {

	if( @is_dir( ROOT_DIR . '/templates/' . $_COOKIE['dle_skin'] ) ) {
		$config['skin'] = $_COOKIE['dle_skin'];
	}
}

if( $config["lang_" . $config['skin']] ) {

	if ( file_exists( ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng' ) ) {	
		@include_once ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng';
	} else die("Language file not found");

} else {
	
	@include_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';

}

$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];


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
	
	if( !$next ) $date_link['next'] = "&nbsp;&nbsp;&nbsp;&nbsp;&raquo;";
	
	$buffer = '<table id="calendar" class="calendar"><tr><th colspan="7" class="monthselect">' . $date_link['prev'] . langdate( 'F', $first_of_month, true ) . ' ' . $cal_year . $date_link['next'] . '</th></tr><tr>';
	
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
	
	return $buffer . '</tr></table>';
}

$buffer = false;
$time = time();
$thisdate = date( "Y-m-d H:i:s", $time );
if( $config['no_date'] AND !$config['news_future'] ) $where_date = " AND date < '" . $thisdate . "'"; else $where_date = "";

$this_month = date( 'm', $time );
$this_year = date( 'Y', $time );

$month = $db->safesql( sprintf("%02d", intval ( $_GET['month'] ) ) );
$year = intval( $_GET['year'] );
$sql = "";

if( $year != '' and $month != '' ) {
	
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
$db->close();

$buffer = cal( $this_month, $this_year, $events );

header( "Content-type: text/html; charset=" . $config['charset'] );
echo $buffer;

?>