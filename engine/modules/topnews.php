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
 Файл: topnews.php
-----------------------------------------------------
 Назначение: вывод рейтинговых статей
=====================================================
*/

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

$tpl->result['topnews'] = dle_cache( "topnews", $config['skin'], true );

if( $tpl->result['topnews'] === false ) {
	
	$this_month = date( 'Y-m-d H:i:s', $_TIME );

	$tpl->load_template( 'topnews.tpl' );

	if( strpos( $tpl->copy_template, "xfvalue" ) !== false OR strpos( $tpl->copy_template, "[xfgiven_" ) !== false ) { $xfound = true; $xfields = xfieldsload();}
	else $xfound = false;

	$config['top_number'] = intval($config['top_number']);
	if ($config['top_number'] < 1 ) $config['top_number'] = 10;

	
	$db->query( "SELECT p.id, p.date, p.short_story, p.xfields, p.title, p.category, p.alt_name FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE p.approve=1 AND p.date >= '$this_month' - INTERVAL 1 MONTH AND p.date < '$this_month' ORDER BY rating DESC, comm_num DESC, news_read DESC, date DESC LIMIT 0,{$config['top_number']}" );
	
	while ( $row = $db->get_row() ) {
		
		$row['date'] = strtotime( $row['date'] );

		if( ! $row['category'] ) {
			$my_cat = "---";
			$my_cat_link = "---";
		} else {
			
			$my_cat = array ();
			$my_cat_link = array ();
			$cat_list = explode( ',', $row['category'] );

			if ($config['category_separator'] != ',') $config['category_separator'] = ' '.$config['category_separator'];
		 
			if( count( $cat_list ) == 1 ) {
				
				$my_cat[] = $cat_info[$cat_list[0]]['name'];
				
				$my_cat_link = get_categories( $cat_list[0], $config['category_separator'] );
			
			} else {
				
				foreach ( $cat_list as $element ) {
					if( $element ) {
						$my_cat[] = $cat_info[$element]['name'];
						if( $config['allow_alt_url'] ) $my_cat_link[] = "<a href=\"" . $config['http_home_url'] . get_url( $element ) . "/\">{$cat_info[$element]['name']}</a>";
						else $my_cat_link[] = "<a href=\"$PHP_SELF?do=cat&category={$cat_info[$element]['alt_name']}\">{$cat_info[$element]['name']}</a>";
					}
				}
				
				$my_cat_link = implode( "{$config['category_separator']} ", $my_cat_link );
			}
			
			$my_cat = implode( "{$config['category_separator']} ", $my_cat );
		}

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

		if( date( 'Ymd', $row['date'] ) == date( 'Ymd', $_TIME ) ) {
			
			$tpl->set( '{date}', $lang['time_heute'] . langdate( ", H:i", $row['date'] ) );
		
		} elseif( date( 'Ymd', $row['date'] ) == date( 'Ymd', ($_TIME - 86400) ) ) {
			
			$tpl->set( '{date}', $lang['time_gestern'] . langdate( ", H:i", $row['date'] ) );
		
		} else {
			
			$tpl->set( '{date}', langdate( $config['timestamp_active'], $row['date'] ) );
		
		}

		$news_date = $row['date'];
		$tpl->copy_template = preg_replace_callback ( "#\{date=(.+?)\}#i", "formdate", $tpl->copy_template );

		$tpl->set( '{category}', $my_cat );
		$tpl->set( '{link-category}', $my_cat_link );

		$row['title'] = stripslashes( $row['title'] );

		$row['title'] = str_replace( "{", "&#123;", $row['title'] );

		$tpl->set( '{title}', $row['title'] );
		
		if ( preg_match( "#\\{title limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches ) ) {
			$count= intval($matches[1]);

			$row['title'] = strip_tags( $row['title'] );

			if( $count AND dle_strlen( $row['title'], $config['charset'] ) > $count ) {
					
				$row['title'] = dle_substr( $row['title'], 0, $count, $config['charset'] );
					
				if( ($temp_dmax = dle_strrpos( $row['title'], ' ', $config['charset'] )) ) $row['title'] = dle_substr( $row['title'], 0, $temp_dmax, $config['charset'] ). " ...";
				
			}

			$tpl->set( $matches[0], $row['title'] );

		}


		$tpl->set( '{link}', $full_link );

		$row['short_story'] = stripslashes( $row['short_story'] );

		if( $user_group[$member_id['user_group']]['allow_hide'] ) $row['short_story'] = str_ireplace( "[hide]", "", str_ireplace( "[/hide]", "", $row['short_story']) );
		else $row['short_story'] = preg_replace ( "#\[hide\](.+?)\[/hide\]#ims", "<div class=\"quote\">" . $lang['news_regus'] . "</div>", $row['short_story'] );

		if (stripos ( $tpl->copy_template, "{image-" ) !== false) {

			$images = array();
			preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $row['short_story'], $media);
			$data=preg_replace('/(img|src)("|\'|="|=\')(.*)/i',"$3",$media[0]);

			foreach($data as $url) {
				$info = pathinfo($url);
				if (isset($info['extension'])) {
					if ($info['filename'] == "spoiler-plus" OR $info['filename'] == "spoiler-plus" ) continue;
					$info['extension'] = strtolower($info['extension']);
					if (($info['extension'] == 'jpg') || ($info['extension'] == 'jpeg') || ($info['extension'] == 'gif') || ($info['extension'] == 'png')) array_push($images, $url);
				}
			}

			if ( count($images) ) {
				$i=0;
				foreach($images as $url) {
					$i++;
					$tpl->copy_template = str_replace( '{image-'.$i.'}', $url, $tpl->copy_template );
					$tpl->copy_template = str_replace( '[image-'.$i.']', "", $tpl->copy_template );
					$tpl->copy_template = str_replace( '[/image-'.$i.']', "", $tpl->copy_template );
				}

			}

			$tpl->copy_template = preg_replace( "#\[image-(.+?)\](.+?)\[/image-(.+?)\]#is", "", $tpl->copy_template );
			$tpl->copy_template = preg_replace( "#\\{image-(.+?)\\}#i", "{THEME}/dleimages/no_image.jpg", $tpl->copy_template );

		}

		$tpl->set( '{text}', $row['short_story'] );

		if ( preg_match( "#\\{text limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches ) ) {
			$count= intval($matches[1]);

			$row['short_story'] = str_replace( "</p><p>", " ", $row['short_story'] );
			$row['short_story'] = strip_tags( $row['short_story'], "<br>" );
			$row['short_story'] = trim(str_replace( "<br>", " ", str_replace( "<br />", " ", str_replace( "\n", " ", str_replace( "\r", "", $row['short_story'] ) ) ) ));

			if( $count AND dle_strlen( $row['short_story'], $config['charset'] ) > $count ) {
					
				$row['short_story'] = dle_substr( $row['short_story'], 0, $count, $config['charset'] );
					
				if( ($temp_dmax = dle_strrpos( $row['short_story'], ' ', $config['charset'] )) ) $row['short_story'] = dle_substr( $row['short_story'], 0, $temp_dmax, $config['charset'] );
				
			}

			$tpl->set( $matches[0], $row['short_story'] );

		}

		// Обработка дополнительных полей
		if( $xfound ) {
			$xfieldsdata = xfieldsdataload( $row['xfields'] );
			
			foreach ( $xfields as $value ) {
				$preg_safe_name = preg_quote( $value[0], "'" );
				
				$xfieldsdata[$value[0]] = stripslashes( $xfieldsdata[$value[0]] );
				
				if ( $value[3] == "yesorno" ) {
					
				    if( intval($xfieldsdata[$value[0]]) ) {
						$xfgiven = true;
						$xfieldsdata[$value[0]] = $lang['xfield_xyes'];
					} else {
						$xfgiven = false;
						$xfieldsdata[$value[0]] = $lang['xfield_xno'];
					}
					
				} else {
					
					if($xfieldsdata[$value[0]] == "") $xfgiven = false; else $xfgiven = true;
					
				}
				
				if( !$xfgiven ) {
					$tpl->copy_template = preg_replace( "'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template );
					$tpl->copy_template = str_replace( "[xfnotgiven_{$value[0]}]", "", $tpl->copy_template );
					$tpl->copy_template = str_replace( "[/xfnotgiven_{$value[0]}]", "", $tpl->copy_template );
				} else {
					$tpl->copy_template = preg_replace( "'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template );
					$tpl->copy_template = str_replace( "[xfgiven_{$value[0]}]", "", $tpl->copy_template );
					$tpl->copy_template = str_replace( "[/xfgiven_{$value[0]}]", "", $tpl->copy_template );
				}

				if(strpos( $tpl->copy_template, "[ifxfvalue" ) !== false ) {
					$tpl->copy_template = preg_replace_callback ( "#\\[ifxfvalue(.+?)\\](.+?)\\[/ifxfvalue\\]#is", "check_xfvalue", $tpl->copy_template );
				}
				
				if ( $value[6] AND !empty( $xfieldsdata[$value[0]] ) ) {
					$temp_array = explode( ",", $xfieldsdata[$value[0]] );
					$value3 = array();

					foreach ($temp_array as $value2) {

						$value2 = trim($value2);
						$value2 = str_replace("&#039;", "'", $value2);

						if( $config['allow_alt_url'] ) $value3[] = "<a href=\"" . $config['http_home_url'] . "xfsearch/" .$value[0]."/". urlencode( $value2 ) . "/\">" . $value2 . "</a>";
						else $value3[] = "<a href=\"$PHP_SELF?do=xfsearch&amp;xfname=".$value[0]."&amp;xf=" . urlencode( $value2 ) . "\">" . $value2 . "</a>";
					}

					$xfieldsdata[$value[0]] = implode(", ", $value3);

					unset($temp_array);
					unset($value2);
					unset($value3);

				}
				
				if($value[3] == "image" AND $xfieldsdata[$value[0]] ) {
					$path_parts = @pathinfo($xfieldsdata[$value[0]]);
		
					if( $value[12] AND file_exists(ROOT_DIR . "/uploads/posts/" .$path_parts['dirname']."/thumbs/".$path_parts['basename']) ) {
						$thumb_url = $config['http_home_url'] . "uploads/posts/" . $path_parts['dirname']."/thumbs/".$path_parts['basename'];
						$img_url = $config['http_home_url'] . "uploads/posts/" . $path_parts['dirname']."/".$path_parts['basename'];
					} else {
						$img_url = 	$config['http_home_url'] . "uploads/posts/" . $path_parts['dirname']."/".$path_parts['basename'];
						$thumb_url = "";
					}
					
					if($thumb_url) {
						$xfieldsdata[$value[0]] = "<a href=\"$img_url\" rel=\"highslide\" class=\"highslide\" target=\"_blank\"><img class=\"xfieldimage {$value[0]}\" src=\"$thumb_url\" alt=\"\" /></a>";
					} else $xfieldsdata[$value[0]] = "<img class=\"xfieldimage {$value[0]}\" src=\"{$img_url}\" alt=\"\" />";
				}
				
				$tpl->copy_template = str_replace( "[xfvalue_{$value[0]}]", $xfieldsdata[$value[0]], $tpl->copy_template );

				if ( preg_match( "#\\[xfvalue_{$preg_safe_name} limit=['\"](.+?)['\"]\\]#i", $tpl->copy_template, $matches ) ) {
					$count= intval($matches[1]);
		
					$xfieldsdata[$value[0]] = str_replace( "</p><p>", " ", $xfieldsdata[$value[0]] );
					$xfieldsdata[$value[0]] = strip_tags( $xfieldsdata[$value[0]], "<br>" );
					$xfieldsdata[$value[0]] = trim(str_replace( "<br>", " ", str_replace( "<br />", " ", str_replace( "\n", " ", str_replace( "\r", "", $xfieldsdata[$value[0]] ) ) ) ));
		
					if( $count AND dle_strlen( $xfieldsdata[$value[0]], $config['charset'] ) > $count ) {
							
						$xfieldsdata[$value[0]] = dle_substr( $xfieldsdata[$value[0]], 0, $count, $config['charset'] );
							
						if( ($temp_dmax = dle_strrpos( $xfieldsdata[$value[0]], ' ', $config['charset'] )) ) $xfieldsdata[$value[0]] = dle_substr( $xfieldsdata[$value[0]], 0, $temp_dmax, $config['charset'] );
						
					}
		
					$tpl->set( $matches[0], $xfieldsdata[$value[0]] );
		
				}

			}
		}
		// Обработка дополнительных полей


		$tpl->compile( 'topnews' );
	}

	$tpl->clear();	
	$db->free();

	create_cache( "topnews", $tpl->result['topnews'], $config['skin'], true );
}
?>