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
Файл: banners.php
-----------------------------------------------------
Назначение: Вывод баннеров
=====================================================
*/

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

define( 'BANNERS', 1 );

//################# Определение баннеров
$banners = get_vars( "banners" );

if( ! is_array( $banners ) ) {
	$banners = array ();
	
	$db->query( "SELECT * FROM " . PREFIX . "_banners ORDER BY id ASC" );
	
	while ( $row_b = $db->get_row() ) {
		
		$banners[$row_b['id']] = array ();
		
		foreach ( $row_b as $key => $value ) {
			$banners[$row_b['id']][$key] = $value;
		}
	
	}
	set_vars( "banners", $banners );
	$db->free();
}

$ban = array ();
$banner_in_news = array ();

if( count( $banners ) > 0 ) {
	foreach ( $banners as $name => $value ) {
		if( $value['approve'] ) { //если активный
			

			if( $value['category'] ) {
				$value['category'] = explode( ',', $value['category'] );
				
				if( ! in_array( $category_id, $value['category'] ) ) $value['code'] = "";
			}
			
			if( $value['main'] ) {
				if( $_SERVER['QUERY_STRING'] != "" ) $value['code'] = "";
			}

			if( $value['fpage'] AND intval($_GET['cstart']) > 1 ) $value['code'] = "";
			if ($value['start'] AND $_TIME < $value['start'] ) $value['code'] = "";
			if ($value['end'] AND $_TIME > $value['end'] ) $value['code'] = "";
			
			$value['grouplevel'] = explode( ',', $value['grouplevel'] );
			
			if( $value['grouplevel'][0] != "all" and ! in_array( $member_id['user_group'], $value['grouplevel'] ) ) {
				$value['code'] = "";
			}
			
			if( $value['code'] != "" ) //если не порезали по ограничениям
			{
				switch ($value['short_place']) //выбираем расположение баннера
				{
					case 1 : //вверх
						$ban_short['top'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						break;
					
					case 2 : //центр
						$ban_short['cen'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						break;
					
					case 3 : //низ
						$ban_short['down'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						break;
					
					case 4 : //вверх,низ
						$ban_short['top'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						$ban_short['down'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						break;
					
					case 5 : //центр,низ
						$ban_short['cen'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						$ban_short['down'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						break;
					
					case 6 : //Вверх,центр
						$ban_short['cen'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						$ban_short['top'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						break;
					
					case 7 : //вверх,центр,низ
						$ban_short['cen'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						$ban_short['top'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						$ban_short['down'][] = array ("text" => $value['code'], "zakr" => $value['bstick'] );
						break;
				}
			
			}

			if( $value['innews'] ) $banner_in_news[] = $value['banner_tag'];
			
			$ban[$value['banner_tag']][] = $value['code'];
		
		}
	}
}


foreach ( $ban as $key => $value ) {
	
	if( ($r_key = count( $value )) > 1 ) {
		
		for($i = 0; $i < $r_key; $i ++) {
			
			if( $ban[$key][$i] == '' ) unset( $ban[$key][$i] );
		
		}
	}

	sort($ban[$key]);

	if ( isset( $_SESSION['banners'][$key] ) AND count($ban[$key]) > 1 ){
	
		$_SESSION['banners'][$key] = intval( $_SESSION['banners'][$key] );

		if($_SESSION['banners'][$key] < (count($ban[$key])-1) ) $r_key = $_SESSION['banners'][$key]+1;
		else $r_key = 0;

	} else {

		$r_key = array_rand( $ban[$key] );

	}

	$_SESSION['banners'][$key] = $r_key;
	$ban[$key] = $ban[$key][$r_key];

}

$banners = $ban;
$ban = array ();
unset( $ban );

?>