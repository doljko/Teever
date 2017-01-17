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
 Файл: show.full.php
-----------------------------------------------------
 Назначение: Просмотр полной новости и комментариев
=====================================================
*/
if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}


	$allow_list = explode( ',', $user_group[$member_id['user_group']]['allow_cats'] );
	$not_allow_cats = explode ( ',', $user_group[$member_id['user_group']]['not_allow_cats'] );
	
	$perm = 1;
	$i = 0;
	$news_found = false;
	$allow_full_cache = false;

	if ( $config['allow_alt_url'] AND !$config['seo_type'] ) $cprefix = "full"; else $cprefix = "full_".$newsid;

	$row = dle_cache ( $cprefix, $sql_news );

	if( $row ) {

		$row = unserialize($row);

		if ( !is_array($row) ) die( "Cache data not correct" );

		$full_cache = true;

	} else {
		$row = $db->super_query( $sql_news );
		$full_cache = false;
	}

	$options = news_permission( $row['access'] );
	if( $options[$member_id['user_group']] AND $options[$member_id['user_group']] != 3 ) $perm = 1;
	if( $options[$member_id['user_group']] == 3 ) $perm = 0;
			
	if( $options[$member_id['user_group']] == 1 ) $user_group[$member_id['user_group']]['allow_addc'] = 0;
	if( $options[$member_id['user_group']] == 2 ) $user_group[$member_id['user_group']]['allow_addc'] = 1;
			
	if( $row['id'] AND !$row['approve'] AND $member_id['name'] != $row['autor'] AND !$user_group[$member_id['user_group']]['allow_all_edit'] ) $perm = 0;
	if( !$row['approve'] ) $allow_comments = false;

	if ($row['id'] AND $config['no_date'] AND !$config['news_future'] AND !$user_group[$member_id['user_group']]['allow_all_edit']) {

		if( strtotime($row['date']) > $_TIME ) {
			$perm = 0;		
		}

	}

	if ($config['category_separator'] != ',') $config['category_separator'] = ' '.$config['category_separator'];

	if( ! $row['category'] ) {
		$my_cat = "---";
		$my_cat_link = "---";
	} else {
			
		$my_cat = array ();
		$my_cat_link = array ();
		$cat_list = explode( ',', $row['category'] );
		
		if( count( $cat_list ) == 1 ) {
				
			if( $allow_list[0] != "all" AND !in_array( $cat_list[0], $allow_list ) ) $perm = 0;

			if( $not_allow_cats[0] != "" AND in_array( $cat_list[0], $not_allow_cats ) ) $perm = 0;
				
			$my_cat[] = $cat_info[$cat_list[0]]['name'];
				
			$my_cat_link = get_categories( $cat_list[0], $config['category_separator'] );
			
		} else {
				
			foreach ( $cat_list as $element ) {
					
				if( $allow_list[0] != "all" AND !in_array( $element, $allow_list ) ) $perm = 0;
				
				if( $not_allow_cats[0] != "" AND in_array( $element, $not_allow_cats ) ) $perm = 0;
					
				if( $element ) {
					$my_cat[] = $cat_info[$element]['name'];
					if( $config['allow_alt_url'] ) $my_cat_link[] = "<a href=\"" . $config['http_home_url'] . get_url( $element ) . "/\">{$cat_info[$element]['name']}</a>";
					else $my_cat_link[] = "<a href=\"$PHP_SELF?do=cat&amp;category={$cat_info[$element]['alt_name']}\">{$cat_info[$element]['name']}</a>";
				}
			}
				
			$my_cat_link = implode( "{$config['category_separator']} ", $my_cat_link );
		}
			
		$my_cat = implode( "{$config['category_separator']} ", $my_cat );
	}

	if ( $row['id'] AND  $perm ) {

		if( strtotime($row['date']) >= ($_TIME - 2592000) ) {
				
			$allow_full_cache = true;
			
		}

		define( 'NEWS_ID', $row['id'] );

		$disable_index = $row['disable_index'];
		$news_author = $row['user_id'];
		
		if ($allow_full_cache AND !$full_cache) create_cache ( $cprefix, serialize($row), $sql_news );

		$xfields = xfieldsload();
		
		if( $row['votes'] AND $view_template != "print" ) include_once ENGINE_DIR . '/modules/poll.php';
		
		$category_id = intval( $row['category'] );
		
		if( $view_template == "print" ) $tpl->load_template( 'print.tpl' );
		elseif( $category_id and $cat_info[$category_id]['full_tpl'] != '' ) $tpl->load_template( $cat_info[$category_id]['full_tpl'] . '.tpl' );
		else $tpl->load_template( 'fullstory.tpl' );

		if( $config['allow_read_count'] AND !$news_page AND !$cstart) {
			if ( $config['allow_read_count'] == 2 ) {

				$readcount = $db->super_query( "SELECT count(*) as count FROM " . PREFIX . "_read_log WHERE news_id='{$row['id']}' AND ip='{$_IP}'" );

				if( !$readcount['count'] ) {

					if( $config['cache_count'] ) $db->query( "INSERT INTO " . PREFIX . "_views (news_id) VALUES ('{$row['id']}')" );
					else $db->query( "UPDATE " . PREFIX . "_post_extras SET news_read=news_read+1 WHERE news_id='{$row['id']}'" );

					$db->query( "INSERT INTO " . PREFIX . "_read_log (news_id, ip) VALUES ('{$row['id']}', '{$_IP}')" );
				}

			} else {

				if( $config['cache_count'] ) $db->query( "INSERT INTO " . PREFIX . "_views (news_id) VALUES ('{$row['id']}')" );
				else $db->query( "UPDATE " . PREFIX . "_post_extras SET news_read=news_read+1 WHERE news_id='{$row['id']}'" );
			}
		}
		
		$news_found = TRUE;
		$row['date'] = strtotime( $row['date'] );
		
		if( (strlen( $row['full_story'] ) < 13) and (strpos( $tpl->copy_template, "{short-story}" ) === false) ) {
			$row['full_story'] = $row['short_story'];
		}

		if( ! $news_page ) {
			$news_page = 1;
		}

		if( $config['allow_alt_url'] ) {
			
			if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
				
				if( $category_id AND $config['seo_type'] == 2 ) {

					$c_url = get_url( $category_id );				
					$full_link = $config['http_home_url'] . $c_url . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";

					if ($config['seo_control'] AND ( isset($_GET['seourl']) OR strpos ( $_SERVER['REQUEST_URI'], "?" ) !== false ) ) {

						if ($_GET['seourl'] != $row['alt_name'] OR $_GET['seocat'] != $c_url OR strpos ( $_SERVER['REQUEST_URI'], "?" ) !== false OR ($_GET['news_page'] == 1 AND $cstart < 2 AND $view_template != "print") OR ($view_template == "print" AND $news_page > 1) ) {

							if ($view_template == "print") {

								$re_url = explode ( "engine/print.php", strtolower ( $_SERVER['PHP_SELF'] ) );
								$re_url = reset ( $re_url );

							} else {

								$re_url = explode ( "index.php", strtolower ( $_SERVER['PHP_SELF'] ) );
								$re_url = reset ( $re_url );

							}

							header("HTTP/1.0 301 Moved Permanently");
							header("Location: {$re_url}{$c_url}/{$row['id']}-{$row['alt_name']}.html");
							die("Redirect");

						}

					}

					$print_link = $config['http_home_url'] . $c_url . "/print:page,1," . $row['id'] . "-" . $row['alt_name'] . ".html";
					$short_link = $config['http_home_url'] . $c_url . "/";
					$row['alt_name'] = $row['id'] . "-" . $row['alt_name'];
					$link_page = $config['http_home_url'] . $c_url . "/" . 'page,' . $news_page . ',';
					$news_name = $row['alt_name'];
				
				} else {
				
					$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";

					if ($config['seo_control'] AND ( isset($_GET['seourl']) OR strpos ( $_SERVER['REQUEST_URI'], "?" ) !== false ) ) {

						if ($_GET['seourl'] != $row['alt_name'] OR $_GET['seocat'] OR $_GET['news_name'] OR strpos ( $_SERVER['REQUEST_URI'], "?" ) !== false OR ($_GET['news_page'] == 1 AND $cstart < 2 AND $view_template != "print") OR ($view_template == "print" AND $news_page > 1) ) {

							if ($view_template == "print") {

								$re_url = explode ( "engine/print.php", strtolower ( $_SERVER['PHP_SELF'] ) );
								$re_url = reset ( $re_url );

							} else {

								$re_url = explode ( "index.php", strtolower ( $_SERVER['PHP_SELF'] ) );
								$re_url = reset ( $re_url );

							}

							header("HTTP/1.0 301 Moved Permanently");
							header("Location: {$re_url}{$row['id']}-{$row['alt_name']}.html");
							die("Redirect");

						}

					}

					$print_link = $config['http_home_url'] . "print:page,1," . $row['id'] . "-" . $row['alt_name'] . ".html";
					$short_link = $config['http_home_url'];
					$row['alt_name'] = $row['id'] . "-" . $row['alt_name'];
					$link_page = $config['http_home_url'] . 'page,' . $news_page . ',';
					$news_name = $row['alt_name'];
				
				}
			
			} else {
				
				$full_link = $config['http_home_url'] . date( 'Y/m/d/', $row['date'] ) . $row['alt_name'] . ".html";

				if ( $config['seo_control'] ) {

					if ($_GET['newsid'] OR strpos ( $_SERVER['REQUEST_URI'], "?" ) !== false OR ($_GET['news_page'] == 1 AND $cstart < 2 AND $view_template != "print") OR ($view_template == "print" AND $news_page > 1) ) {

						if ($view_template == "print") {

							$re_url = explode ( "engine/print.php", strtolower ( $_SERVER['PHP_SELF'] ) );
							$re_url = reset ( $re_url );

						} else {

							$re_url = explode ( "index.php", strtolower ( $_SERVER['PHP_SELF'] ) );
							$re_url = reset ( $re_url );

						}

						header("HTTP/1.0 301 Moved Permanently");
						header("Location: {$re_url}".date( 'Y/m/d/', $row['date'] ).$row['alt_name'].".html");
						die("Redirect");

					}

				}

				$print_link = $config['http_home_url'] . date( 'Y/m/d/', $row['date'] ) . "print:page,1," . $row['alt_name'] . ".html";
				$short_link = $config['http_home_url'] . date( 'Y/m/d/', $row['date'] );
				$link_page = $config['http_home_url'] . date( 'Y/m/d/', $row['date'] ) . 'page,' . $news_page . ',';
				$news_name = $row['alt_name'];
			
			}
		
		} else {
			
			$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];
			$print_link = $config['http_home_url'] . "engine/print.php?newsid=" . $row['id'];
			$short_link = "";
			$link_page = "";
			$news_name = "";
		
		}
		
		$i ++;		
		//
		// обработка страниц
		//

		$news_seiten = explode( "{PAGEBREAK}", $row['full_story'] );
		$anzahl_seiten = count( $news_seiten );
		
		if( $news_page <= 0 OR $news_page > $anzahl_seiten OR (isset($_GET['news_page']) AND $_GET['news_page'] === "0") ) {
			
			$news_page = 1;

			if ( $config['seo_control'] ) {
				$re_url = str_replace( $config['http_home_url'], "/", $full_link );
				header("HTTP/1.0 301 Moved Permanently");
				header("Location: {$re_url}");
				die("Redirect");
			}
		}

		if( $view_template == "print" ) {
			
			$row['full_story'] = str_replace( "{PAGEBREAK}", "", $row['full_story'] );
			$row['full_story'] = preg_replace( "'\[page=(.*?)\](.*?)\[/page\]'si", "\\2", $row['full_story'] );
			$tpl->set_block( "'\\[pages\\](.*?)\\[/pages\\]'si", "" );
			$tpl->set( '{pages}', "" );
		
		} else {
			
			$row['full_story'] = $news_seiten[$news_page - 1];
			
			$row['full_story'] = preg_replace( '#(\A[\s]*<br[^>]*>[\s]*|<br[^>]*>[\s]*\Z)#is', '', $row['full_story'] ); // remove <br/> at end of string
			$news_seiten = "";
			unset( $news_seiten );
			
			if( $anzahl_seiten > 1 ) {

				$tpl2 = new dle_template();
				$tpl2->dir = TEMPLATE_DIR;
				$tpl2->load_template( 'splitnewsnavigation.tpl' );
				
				if( $news_page < $anzahl_seiten ) {
					$pages = $news_page + 1;
					
					if( $config['allow_alt_url'] ) {
						$nextpage = "<a href=\"" . $short_link . "page," . $pages . "," . $row['alt_name'] . ".html\">";
					} else {
						$nextpage = "<a href=\"$PHP_SELF?newsid=" . $row['id'] . "&amp;news_page=" . $pages . "\">";
					}

					$tpl2->set( '[next-link]', $nextpage );
					$tpl2->set( '[/next-link]', "</a>" );

				} else {

					$tpl2->set_block( "'\\[next-link\\](.*?)\\[/next-link\\]'si", "<span>\\1</span>" );

				}
				
				if( $news_page > 1 ) {
					$pages = $news_page - 1;
					
					if( $config['allow_alt_url'] ) {
						if ( $pages == 1 ) $prevpage = "<a href=\"" . $full_link . "\">";
						else $prevpage = "<a href=\"" . $short_link . "page," . $pages . "," . $row['alt_name'] . ".html\">";
					} else {
						if ( $pages == 1 ) $prevpage = "<a href=\"" . $full_link. "\">";
						else $prevpage = "<a href=\"$PHP_SELF?newsid=" . $row['id'] . "&amp;news_page=" . $pages . "\">";
					}

					$tpl2->set( '[prev-link]', $prevpage );
					$tpl2->set( '[/prev-link]', "</a>" );

				} else {

					$tpl2->set_block( "'\\[prev-link\\](.*?)\\[/prev-link\\]'si", "<span>\\1</span>" );

				}

				$listpages ="";

				if( $anzahl_seiten <= 10 ) {
					
					for($j = 1; $j <= $anzahl_seiten; $j ++) {
						
						if( $j != $news_page ) {
							
							if( $config['allow_alt_url'] ) {

								if ($j == 1)
									$listpages .= "<a href=\"" . $full_link . "\">$j</a> ";
								else
									$listpages .= "<a href=\"" . $short_link . "page," . $j . "," . $row['alt_name'] . ".html\">$j</a> ";

							} else {

								if ($j == 1)
									$listpages .= "<a href=\"{$full_link}\">$j</a> ";
								else
									$listpages .= "<a href=\"$PHP_SELF?newsid=" . $row['id'] . "&amp;news_page=" . $j . "\">$j</a> ";

							}
						
						} else {
							
							$listpages .= "<span>$j</span> ";
						}
					
					}

				} else {
					$start = 1;
					$end = 10;
					$nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
					
					if( $news_page > 1 ) {
						
						if( $news_page > 6 ) {
							
							$start = $news_page - 4;
							$end = $start + 8;
							
							if( $end >= $anzahl_seiten ) {
								$start = $anzahl_seiten - 9;
								$end = $anzahl_seiten - 1;
								$nav_prefix = "";
							} else
								$nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
						
						}
					
					}
					
					if( $start >= 2 ) {
						
						$listpages .= "<a href=\"" . $full_link . "\">1</a> <span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
					
					}
					
					for($j = $start; $j <= $end; $j ++) {
						
						if( $j != $news_page ) {

							if( $config['allow_alt_url'] ) {

								if ($j == 1)
									$listpages .= "<a href=\"" . $full_link . "\">$j</a> ";
								else
									$listpages .= "<a href=\"" . $short_link . "page," . $j . "," . $row['alt_name'] . ".html\">$j</a> ";

							} else {

								if ($j == 1)
									$listpages .= "<a href=\"{$full_link}\">$j</a> ";
								else
									$listpages .= "<a href=\"$PHP_SELF?newsid=" . $row['id'] . "&amp;news_page=" . $j . "\">$j</a> ";

							}
						
						} else {
							
							$listpages .= "<span>$j</span> ";
						}
					
					}
					
					if( $news_page != $anzahl_seiten ) {
						
						if( $config['allow_alt_url'] ) $listpages .= $nav_prefix . "<a href=\"" . $short_link . "page," . $anzahl_seiten . "," . $row['alt_name'] . ".html\">{$anzahl_seiten}</a>";
						else $listpages .= $nav_prefix . "<a href=\"$PHP_SELF?newsid=" . $row['id'] . "&amp;news_page=" . $anzahl_seiten . "\">{$anzahl_seiten}</a>";
					
					} else
						$listpages .= "<span>{$anzahl_seiten}</span> ";

				}

				$tpl2->set( '{pages}', $listpages );
				$tpl2->compile( 'content' );
				
				$tpl->set( '{pages}', $tpl2->result['content'] );
				unset($tpl2);
				
				if( $config['allow_alt_url'] ) {
					
					$replacepage = "<a href=\"" . $short_link . "page," . "\\1" . "," . $row['alt_name'] . ".html\">\\2</a>";
				
				} else {
					
					$replacepage = "<a href=\"$PHP_SELF?newsid=" . $row['id'] . "&amp;news_page=\\1\">\\2</a>";
				}
				
				$row['full_story'] = preg_replace( "'\[page=(.*?)\](.*?)\[/page\]'si", $replacepage, $row['full_story'] );
				$tpl->set( '[pages]', "" );
				$tpl->set( '[/pages]', "" );

			
			} else {
				
				$tpl->set( '{pages}', '' );
				$row['full_story'] = preg_replace( "'\[page=(.*?)\](.*?)\[/page\]'si", "", $row['full_story'] );
				$tpl->set_block( "'\\[pages\\](.*?)\\[/pages\\]'si", "" );
			}
		}

		$row['title'] = stripslashes( $row['title'] );		
		$metatags['title'] = $row['title'];

		if( $row['keywords'] == '' AND $row['descr'] == '' AND $config['create_metatags'] ) {
			create_keywords( $row['full_story'] );
		} else {
			$metatags['keywords'] = $row['keywords'];
			if( $row['descr'] ) $metatags['description'] = $row['descr']; else $metatags['description'] = $row['title'];
		}

		if ($row['metatitle']) $metatags['header_title'] = $row['metatitle'];

		$social_tags['site_name'] = $config['home_title'];
		$social_tags['type'] = 'article';
		$social_tags['title'] = htmlspecialchars( $row['title'], ENT_QUOTES, $config['charset'] );
		$social_tags['url'] = $full_link;

		$comments_num = $row['comm_num'];
		
		$news_find = array ('{comments-num}' => number_format($row['comm_num'], 0, ',', ' '), '{views}' => number_format($row['news_read'], 0, ',', ' '), '{category}' => $my_cat, '{link-category}' => $my_cat_link, '{news-id}' => $row['id'] );
		
		if( date( 'Ymd', $row['date'] ) == date( 'Ymd', $_TIME ) ) {
			
			$tpl->set( '{date}', $lang['time_heute'] . langdate( ", H:i", $row['date'] ) );
		
		} elseif( date( 'Ymd', $row['date'] ) == date( 'Ymd', ($_TIME - 86400) ) ) {
			
			$tpl->set( '{date}', $lang['time_gestern'] . langdate( ", H:i", $row['date'] ) );
		
		} else {
			
			$tpl->set( '{date}', langdate( $config['timestamp_active'], $row['date'] ) );
		
		}
		$news_date = $row['date'];
		$tpl->copy_template = preg_replace_callback ( "#\{date=(.+?)\}#i", "formdate", $tpl->copy_template );

		if ( $row['fixed'] ) {

			$tpl->set( '[fixed]', "" );
			$tpl->set( '[/fixed]', "" );
			$tpl->set_block( "'\\[not-fixed\\](.*?)\\[/not-fixed\\]'si", "" );

		} else {

			$tpl->set( '[not-fixed]', "" );
			$tpl->set( '[/not-fixed]', "" );
			$tpl->set_block( "'\\[fixed\\](.*?)\\[/fixed\\]'si", "" );
		}

		if ( $comments_num ) {

			$tpl->set( '[comments]', "" );
			$tpl->set( '[/comments]', "" );
			$tpl->set_block( "'\\[not-comments\\](.*?)\\[/not-comments\\]'si", "" );

		} else {

			$tpl->set( '[not-comments]', "" );
			$tpl->set( '[/not-comments]', "" );
			$tpl->set_block( "'\\[comments\\](.*?)\\[/comments\\]'si", "" );
		}

		if ( $row['votes'] ) {

			$tpl->set( '[poll]', "" );
			$tpl->set( '[/poll]', "" );
			$tpl->set_block( "'\\[not-poll\\](.*?)\\[/not-poll\\]'si", "" );

		} else {

			$tpl->set( '[not-poll]', "" );
			$tpl->set( '[/not-poll]', "" );
			$tpl->set_block( "'\\[poll\\](.*?)\\[/poll\\]'si", "" );
		}	

		if( $vk_url ) {
			$tpl->set( '[vk]', "" );
			$tpl->set( '[/vk]', "" );
			$tpl->set( '{vk_url}', $vk_url );	
		} else {
			$tpl->set_block( "'\\[vk\\](.*?)\\[/vk\\]'si", "" );
			$tpl->set( '{vk_url}', '' );	
		}
		if( $odnoklassniki_url ) {
			$tpl->set( '[odnoklassniki]', "" );
			$tpl->set( '[/odnoklassniki]', "" );
			$tpl->set( '{odnoklassniki_url}', $odnoklassniki_url );
		} else {
			$tpl->set_block( "'\\[odnoklassniki\\](.*?)\\[/odnoklassniki\\]'si", "" );
			$tpl->set( '{odnoklassniki_url}', '' );	
		}
		if( $facebook_url ) {
			$tpl->set( '[facebook]', "" );
			$tpl->set( '[/facebook]', "" );
			$tpl->set( '{facebook_url}', $facebook_url );	
		} else {
			$tpl->set_block( "'\\[facebook\\](.*?)\\[/facebook\\]'si", "" );
			$tpl->set( '{facebook_url}', '' );	
		}
		if( $google_url ) {
			$tpl->set( '[google]', "" );
			$tpl->set( '[/google]', "" );
			$tpl->set( '{google_url}', $google_url );
		} else {
			$tpl->set_block( "'\\[google\\](.*?)\\[/google\\]'si", "" );
			$tpl->set( '{google_url}', '' );	
		}
		if( $mailru_url ) {
			$tpl->set( '[mailru]', "" );
			$tpl->set( '[/mailru]', "" );
			$tpl->set( '{mailru_url}', $mailru_url );	
		} else {
			$tpl->set_block( "'\\[mailru\\](.*?)\\[/mailru\\]'si", "" );
			$tpl->set( '{mailru_url}', '' );	
		}
		if( $yandex_url ) {
			$tpl->set( '[yandex]', "" );
			$tpl->set( '[/yandex]', "" );
			$tpl->set( '{yandex_url}', $yandex_url );
		} else {
			$tpl->set_block( "'\\[yandex\\](.*?)\\[/yandex\\]'si", "" );
			$tpl->set( '{yandex_url}', '' );
		}
		
		if( $row['editdate'] ) $_DOCUMENT_DATE = $row['editdate'];
		
		else $_DOCUMENT_DATE = $row['date'];
		
		if( $row['view_edit'] and $row['editdate'] ) {
			
			if( date( Ymd, $row['editdate'] ) == date( Ymd, $_TIME ) ) {
				
				$tpl->set( '{edit-date}', $lang['time_heute'] . langdate( ", H:i", $row['editdate'] ) );
			
			} elseif( date( Ymd, $row['editdate'] ) == date( Ymd, ($_TIME - 86400) ) ) {
				
				$tpl->set( '{edit-date}', $lang['time_gestern'] . langdate( ", H:i", $row['editdate'] ) );
			
			} else {
				
				$tpl->set( '{edit-date}', langdate( $config['timestamp_active'], $row['editdate'] ) );
			
			}
			
			$tpl->set( '{editor}', $row['editor'] );
			$tpl->set( '{edit-reason}', $row['reason'] );
			
			if( $row['reason'] ) {
				
				$tpl->set( '[edit-reason]', "" );
				$tpl->set( '[/edit-reason]', "" );
			
			} else
				$tpl->set_block( "'\\[edit-reason\\](.*?)\\[/edit-reason\\]'si", "" );
			
			$tpl->set( '[edit-date]', "" );
			$tpl->set( '[/edit-date]', "" );
		
		} else {
			
			$tpl->set( '{edit-date}', "" );
			$tpl->set( '{editor}', "" );
			$tpl->set( '{edit-reason}', "" );
			$tpl->set_block( "'\\[edit-date\\](.*?)\\[/edit-date\\]'si", "" );
			$tpl->set_block( "'\\[edit-reason\\](.*?)\\[/edit-reason\\]'si", "" );
		}
		
		if( $config['allow_tags'] and $row['tags'] ) {
			
			$tpl->set( '[tags]', "" );
			$tpl->set( '[/tags]', "" );
			
			$social_tags['news_keywords'] = $row['tags'];
		
			$tags = array ();
			
			$row['tags'] = explode( ",", $row['tags'] );
			
			foreach ( $row['tags'] as $value ) {
				
				$value = trim( $value );
				
				if( $config['allow_alt_url'] ) $tags[] = "<span><a href=\"" . $config['http_home_url'] . "tags/" . urlencode( $value ) . "/\">" . $value . "</a></span>";
				else $tags[] = "<span><a href=\"$PHP_SELF?do=tags&amp;tag=" . urlencode( $value ) . "\">" . $value . "</a></span>";
			
			}
			
			$tpl->set( '{tags}', implode( " ", $tags ) );
		
		} else {
			
			$tpl->set_block( "'\\[tags\\](.*?)\\[/tags\\]'si", "" );
			$tpl->set( '{tags}', "" );
		
		}
		
		$tpl->set( '', $news_find );

		$url_cat = $category_id;
		$category_id = $row['category'];

		if( strpos( $tpl->copy_template, "[catlist=" ) !== false ) {
			$tpl->copy_template = preg_replace_callback ( "#\\[(catlist)=(.+?)\\](.*?)\\[/catlist\\]#is", "check_category", $tpl->copy_template );
		}
								
		if( strpos( $tpl->copy_template, "[not-catlist=" ) !== false ) {
			$tpl->copy_template = preg_replace_callback ( "#\\[(not-catlist)=(.+?)\\](.*?)\\[/not-catlist\\]#is", "check_category", $tpl->copy_template );
		}

		$category_id = $url_cat;
	
		if( $category_id AND $cat_info[$category_id]['icon'] ) {
			
			$tpl->set( '{category-icon}', $cat_info[$category_id]['icon'] );
		
		} else {
			
			$tpl->set( '{category-icon}', "{THEME}/dleimages/no_icon.gif" );
		
		}

		if ( $category_id )
			$tpl->set( '{category-url}', $config['http_home_url'] . get_url( $category_id ) . "/" );
		else
			$tpl->set( '{category-url}', "#" );
		
		if ($config['allow_search_print']) {

			$tpl->set( '[print-link]', "<a href=\"" . $print_link . "\">" );
			$tpl->set( '[/print-link]', "</a>" );

		} else {

			$tpl->set( '[print-link]', "<a href=\"" . $print_link . "\" rel=\"nofollow\">" );
			$tpl->set( '[/print-link]', "</a>" );

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

		if( $row['allow_rate'] ) { 

			$tpl->set( '{rating}', ShowRating( $row['id'], $row['rating'], $row['vote_num'], $user_group[$member_id['user_group']]['allow_rating'] ) );
			$tpl->set( '{vote-num}', "<span id=\"vote-num-id-".$row['id']."\">".$row['vote_num']."</span>" );
			$tpl->set( '[rating]', "" );
			$tpl->set( '[/rating]', "" );
			
			if( $user_group[$member_id['user_group']]['allow_rating'] ) {

				if ( $config['rating_type'] ) {
						
					$tpl->set( '[rating-plus]', "<a href=\"#\" onclick=\"doRate('plus', '{$row['id']}'); return false;\" >" );
					$tpl->set( '[/rating-plus]', '</a>' );
					
					if ( $config['rating_type'] == "2" ) {
						
						$tpl->set( '[rating-minus]', "<a href=\"#\" onclick=\"doRate('minus', '{$row['id']}'); return false;\" >" );
						$tpl->set( '[/rating-minus]', '</a>' );
						
					} else {
						$tpl->set_block( "'\\[rating-minus\\](.*?)\\[/rating-minus\\]'si", "" );
					}
					
				} else {
					$tpl->set_block( "'\\[rating-plus\\](.*?)\\[/rating-plus\\]'si", "" );
					$tpl->set_block( "'\\[rating-minus\\](.*?)\\[/rating-minus\\]'si", "" );
				}
				
			} else {
				$tpl->set_block( "'\\[rating-plus\\](.*?)\\[/rating-plus\\]'si", "" );
				$tpl->set_block( "'\\[rating-minus\\](.*?)\\[/rating-minus\\]'si", "" );				
			}

		} else { 

			$tpl->set( '{rating}', "" );
			$tpl->set( '{vote-num}', "" );
			$tpl->set_block( "'\\[rating\\](.*?)\\[/rating\\]'si", "" );
			$tpl->set_block( "'\\[rating-plus\\](.*?)\\[/rating-plus\\]'si", "" );
			$tpl->set_block( "'\\[rating-minus\\](.*?)\\[/rating-minus\\]'si", "" );
		}
		
		if( $config['allow_alt_url'] ) {
			
			$go_page = $config['http_home_url'] . "user/" . urlencode( $row['autor'] ) . "/";
			$tpl->set( '[day-news]', "<a href=\"".$config['http_home_url'] . date( 'Y/m/d/', $row['date'])."\" >" );
		
		} else {
			
			$go_page = "$PHP_SELF?subaction=userinfo&amp;user=" . urlencode( $row['autor'] );
			$tpl->set( '[day-news]', "<a href=\"$PHP_SELF?year=".date( 'Y', $row['date'])."&amp;month=".date( 'm', $row['date'])."&amp;day=".date( 'd', $row['date'])."\" >" );
		
		}
		
		$tpl->set( '[/day-news]', "</a>" );
		$tpl->set( '[profile]', "<a href=\"" . $go_page . "\">" );
		$tpl->set( '[/profile]', "</a>" );

		$tpl->set( '{login}', $row['autor'] );

		$tpl->set( '{author}', "<a onclick=\"ShowProfile('" . urlencode( $row['autor'] ) . "', '" . $go_page . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\" href=\"" . $go_page . "\">" . $row['autor'] . "</a>" );
		
		$_SESSION['referrer'] = $_SERVER['REQUEST_URI'];
		
		$tpl->set( '[full-link]', "<a href=\"" . $full_link . "\">" );
		$tpl->set( '[/full-link]', "</a>" );
		
		$tpl->set( '{full-link}', $full_link );
		
		if( $row['allow_comm'] ) {
			
			$tpl->set( '[com-link]', "<a id=\"dle-comm-link\" href=\"" . $full_link . "#comment\">" );
			$tpl->set( '[/com-link]', "</a>" );
		
		} else
			$tpl->set_block( "'\\[com-link\\](.*?)\\[/com-link\\]'si", "" );
		
		if( ! $row['approve'] and ($member_id['name'] == $row['autor'] and ! $user_group[$member_id['user_group']]['allow_all_edit']) ) {
			$tpl->set( '[edit]', "<a href=\"" . $config['http_home_url'] . "index.php?do=addnews&amp;id=" . $row['id'] . "\" >" );
			$tpl->set( '[/edit]', "</a>" );
			if( $config['allow_quick_wysiwyg'] ) $allow_comments_ajax = true;
		} elseif( $is_logged and (($member_id['name'] == $row['autor'] and $user_group[$member_id['user_group']]['allow_edit']) or $user_group[$member_id['user_group']]['allow_all_edit']) ) {
			$tpl->set( '[edit]', "<a onclick=\"return dropdownmenu(this, event, MenuNewsBuild('" . $row['id'] . "', 'full'), '170px')\" href=\"#\">" );
			$tpl->set( '[/edit]', "</a>" );
			if( $config['allow_quick_wysiwyg'] ) $allow_comments_ajax = true;
		} else
			$tpl->set_block( "'\\[edit\\](.*?)\\[/edit\\]'si", "" );
		
		if( $config['related_news'] AND $view_template != "print") {
			
			if ( $allow_full_cache ) $related_buffer = dle_cache( "related", $row['id'].$config['skin'], true ); else $related_buffer = false;
		
			if( $related_buffer === false ) {

				if ( $row['related_ids'] ) {
					
					$id_list = array();
					$id_temp = explode(",", $row['related_ids']);
					
					foreach ( $id_temp as $value ) {
						
						$value = intval($value);
						
						if ( $value > 0 ) $id_list[] = $value;
						
					}
					
					$row['related_ids'] = implode( ',', $id_list );
					
					unset($id_list);
					unset($id_temp);
			
					$db->query( "SELECT id, date, short_story, xfields, title, category, alt_name FROM " . PREFIX . "_post WHERE id IN({$row['related_ids']}) ORDER BY id DESC");
					$first_show = false;

				} else {
					$first_show = true;
					$related_ids = array();
			
					if( strlen( $row['full_story'] ) < strlen( $row['short_story'] ) ) $body = $row['short_story'];
					else $body = $row['full_story'];
					
					$body = strip_tags( stripslashes( $metatags['title'] . " " . $body ) );

					if( dle_strlen( $body, $config['charset'] ) > 1000 ) {
						$body = dle_substr( $body, 0, 1000, $config['charset'] );
					}
					
					$body = $db->safesql( $body );
					
					$config['related_number'] = intval( $config['related_number'] );
					if( $config['related_number'] < 1 ) $config['related_number'] = 5;
	
					$allowed_cats = array();
	
					foreach ($user_group as $value) {
						if ($value['allow_cats'] != "all" AND !$value['allow_short'] ) $allowed_cats[] = $db->safesql($value['allow_cats']);
					}
	
					if (count($allowed_cats)) {

						$allowed_cats = implode(",", $allowed_cats);
						$allowed_cats = explode(",", $allowed_cats);
						$allowed_cats = array_unique($allowed_cats);
						sort($allowed_cats);
	
						if ($config['allow_multi_category']) {
							
							$allowed_cats = "category regexp '[[:<:]](" . implode ( '|', $allowed_cats ) . ")[[:>:]]' AND ";
						
						} else {
							
							$allowed_cats = "category IN ('" . implode ( "','", $allowed_cats ) . "') AND ";
						
						}

					} else $allowed_cats="";
					
					$not_allowed_cats = array();
	
					foreach ($user_group as $value) {
						if ($value['not_allow_cats'] != "" AND !$value['allow_short'] ) $not_allowed_cats[] = $db->safesql($value['not_allow_cats']);
					}
	
					if (count($not_allowed_cats)) {

						$not_allowed_cats = implode(",", $not_allowed_cats);
						$not_allowed_cats = explode(",", $not_allowed_cats);
						$not_allowed_cats = array_unique($not_allowed_cats);
						sort($not_allowed_cats);
	
						if ($config['allow_multi_category']) {
							
							$not_allowed_cats = "category NOT REGEXP '[[:<:]](" . implode ( '|', $not_allowed_cats ) . ")[[:>:]]' AND ";
						
						} else {
							
							$not_allowed_cats = "category NOT IN ('" . implode ( "','", $not_allowed_cats ) . "') AND ";
						
						}

					} else $not_allowed_cats="";

					if ($config['related_only_cats'] AND $row['category'] ) {

						$allowed_cats="";
						$not_allowed_cats = "";
						$allow_sub_cats = true;
						$all_cats = explode(",", $row['category']);
						$get_cats = array();

						foreach ($all_cats as $value) {

							if ( $cat_info[$value]['show_sub'] ) {
				
								if ( $cat_info[$value]['show_sub'] == 1 ) $get_cats[] = get_sub_cats ( $value );
								else { $get_cats[] = $value; }
				
							} else {
				
								if ( $config['show_sub_cats'] ) $get_cats[] = get_sub_cats ( $value );
								else { $get_cats[] = $value; }
				
							}

						}
						
						$get_cats = implode("|", $get_cats);
						$get_cats = explode("|", $get_cats);
						
						if ( count($get_cats) < 2 ) $allow_sub_cats = false;

						$get_cats = implode("|", $get_cats);
						
						if ($config['allow_multi_category']) {
							
							$where_category = "category regexp '[[:<:]](" . $get_cats . ")[[:>:]]' AND ";
						
						} else {
							
							if ( $allow_sub_cats ) {
								
								$get_cats = str_replace ( "|", "','", $get_cats );
								$where_category = "category IN ('" . $get_cats . "') AND ";
							
							} else {
								
								$where_category = "category = '{$get_cats}' AND ";
							
							}
						
						}

					} else $where_category = "";

					$db->query( "SELECT id, date, short_story, xfields, title, category, alt_name FROM " . PREFIX . "_post WHERE {$where_category}{$allowed_cats}{$not_allowed_cats}MATCH (title, short_story, full_story, xfields) AGAINST ('$body') AND id != " . $row['id'] . " AND approve=1" . $where_date . " LIMIT " . $config['related_number'] );
				}

				$tpl2 = new dle_template();
				$tpl2->dir = TEMPLATE_DIR;
				$tpl2->load_template( 'relatednews.tpl' );

				if( strpos( $tpl2->copy_template, "xfvalue" ) !== false OR strpos( $tpl2->copy_template, "[xfgiven_" ) !== false ) { $xfound = true; }
				else $xfound = false;
								
				while ( $related = $db->get_row() ) {
					
					if ( $first_show ) $related_ids[] =	$related['id'];

					$related['date'] = strtotime( $related['date'] );

					if( ! $related['category'] ) {
						$my_cat = "---";
						$my_cat_link = "---";
					} else {
						
						$my_cat = array ();
						$my_cat_link = array ();
						$rel_cat_list = explode( ',', $related['category'] );
					 
						if( count( $rel_cat_list ) == 1 ) {
							
							$my_cat[] = $cat_info[$rel_cat_list[0]]['name'];
							
							$my_cat_link = get_categories( $rel_cat_list[0], $config['category_separator'] );
						
						} else {
							
							foreach ( $rel_cat_list as $element ) {
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

					$related['category'] = intval( $related['category'] );
					
					if( $config['allow_alt_url'] ) {
						
						if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
							
							if( $related['category'] and $config['seo_type'] == 2 ) {
								
								$rel_full_link = $config['http_home_url'] . get_url( $related['category'] ) . "/" . $related['id'] . "-" . $related['alt_name'] . ".html";
							
							} else {
								
								$rel_full_link = $config['http_home_url'] . $related['id'] . "-" . $related['alt_name'] . ".html";
							
							}
						
						} else {
							
							$rel_full_link = $config['http_home_url'] . date( 'Y/m/d/', $related['date'] ) . $related['alt_name'] . ".html";
						}
					
					} else {
						
						$rel_full_link = $config['http_home_url'] . "index.php?newsid=" . $related['id'];
					
					}

					$related['title'] = strip_tags( stripslashes( $related['title'] ) );

					$tpl2->set( '{title}', $related['title'] );
					$tpl2->set( '{link}', $rel_full_link );
					$tpl2->set( '{category}', $my_cat );
					$tpl2->set( '{link-category}', $my_cat_link );

					if( date( 'Ymd', $related['date'] ) == date( 'Ymd', $_TIME ) ) {
						
						$tpl2->set( '{date}', $lang['time_heute'] . langdate( ", H:i", $related['date'] ) );
					
					} elseif( date( 'Ymd', $related['date'] ) == date( 'Ymd', ($_TIME - 86400) ) ) {
						
						$tpl2->set( '{date}', $lang['time_gestern'] . langdate( ", H:i", $related['date'] ) );
					
					} else {
						
						$tpl2->set( '{date}', langdate( $config['timestamp_active'], $related['date'] ) );
					
					}
					$news_date = $related['date'];
					$tpl2->copy_template = preg_replace_callback ( "#\{date=(.+?)\}#i", "formdate", $tpl2->copy_template );

					$related['short_story'] = stripslashes( $related['short_story'] );

					if( $user_group[$member_id['user_group']]['allow_hide'] ) $related['short_story'] = str_ireplace( "[hide]", "", str_ireplace( "[/hide]", "", $related['short_story']) );
					else $related['short_story'] = preg_replace ( "#\[hide\](.+?)\[/hide\]#ims", "<div class=\"quote\">" . $lang['news_regus'] . "</div>", $related['short_story'] );

					if (stripos ( $tpl2->copy_template, "{image-" ) !== false) {
			
						$images = array();
						preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $related['short_story'], $media);
						$data=preg_replace('/(img|src)("|\'|="|=\')(.*)/i',"$3",$media[0]);
			
						foreach($data as $url) {
							$info = pathinfo($url);
							if (isset($info['extension'])) {
								if ($info['filename'] == "spoiler-plus" OR $info['filename'] == "spoiler-minus" ) continue;
								$info['extension'] = strtolower($info['extension']);
								if (($info['extension'] == 'jpg') || ($info['extension'] == 'jpeg') || ($info['extension'] == 'gif') || ($info['extension'] == 'png')) array_push($images, $url);
							}
						}
			
						if ( count($images) ) {
							$i=0;
							foreach($images as $url) {
								$i++;
								$tpl2->copy_template = str_replace( '{image-'.$i.'}', $url, $tpl2->copy_template );
								$tpl2->copy_template = str_replace( '[image-'.$i.']', "", $tpl2->copy_template );
								$tpl2->copy_template = str_replace( '[/image-'.$i.']', "", $tpl2->copy_template );
							}
			
						}

						$tpl2->copy_template = preg_replace( "#\[image-(.+?)\](.+?)\[/image-(.+?)\]#is", "", $tpl2->copy_template );			
						$tpl2->copy_template = preg_replace( "#\\{image-(.+?)\\}#i", "{THEME}/dleimages/no_image.jpg", $tpl2->copy_template );
			
					}

					if ( preg_match( "#\\{text limit=['\"](.+?)['\"]\\}#i", $tpl2->copy_template, $matches ) ) {
						$count= intval($matches[1]);

						$related['short_story'] = preg_replace( "#<!--TBegin(.+?)<!--TEnd-->#is", "", $related['short_story'] );
						$related['short_story'] = preg_replace( "#<!--MBegin(.+?)<!--MEnd-->#is", "", $related['short_story'] );
						$related['short_story'] = preg_replace( "'\[attachment=(.*?)\]'si", "", $related['short_story'] );
						$related['short_story'] = preg_replace ( "#\[hide\](.+?)\[/hide\]#ims", "", $related['short_story'] );

						$related['short_story'] = str_replace( "</p><p>", " ", $related['short_story'] );
			
						$related['short_story'] = strip_tags( $related['short_story'], "<br>" );
						$related['short_story'] = trim(str_replace( "<br>", " ", str_replace( "<br />", " ", str_replace( "\n", " ", str_replace( "\r", "", $related['short_story'] ) ) ) ));
						if( $count AND dle_strlen( $related['short_story'], $config['charset'] ) > $count ) {
								
							$related['short_story'] = dle_substr( $related['short_story'], 0, $count, $config['charset'] );
								
							if( ($temp_dmax = dle_strrpos( $related['short_story'], ' ', $config['charset'] )) ) $related['short_story'] = dle_substr( $related['short_story'], 0, $temp_dmax, $config['charset'] );
							
						}
			
						$tpl2->set( $matches[0], $related['short_story'] );
			
					} else $tpl2->set( '{text}', $related['short_story'] );

					if ( preg_match( "#\\{title limit=['\"](.+?)['\"]\\}#i", $tpl2->copy_template, $matches ) ) {
						$count= intval($matches[1]);
		
						if( $count AND dle_strlen( $related['title'], $config['charset'] ) > $count ) {
					
							$related['title'] = dle_substr( $related['title'], 0, $count, $config['charset'] );
								
							if( ($temp_dmax = dle_strrpos( $related['title'], ' ', $config['charset'] )) ) $related['title'] = dle_substr( $related['title'], 0, $temp_dmax, $config['charset'] );
							
						}
						$tpl2->set( $matches[0], $related['title'] );
					
					}

					if( $xfound ) {
						$xfieldsdata = xfieldsdataload( $related['xfields'] );
						
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
								$tpl2->copy_template = preg_replace( "'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "", $tpl2->copy_template );
								$tpl2->copy_template = str_replace( "[xfnotgiven_{$value[0]}]", "", $tpl2->copy_template );
								$tpl2->copy_template = str_replace( "[/xfnotgiven_{$value[0]}]", "", $tpl2->copy_template );
							} else {
								$tpl2->copy_template = preg_replace( "'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is", "", $tpl2->copy_template );
								$tpl2->copy_template = str_replace( "[xfgiven_{$value[0]}]", "", $tpl2->copy_template );
								$tpl2->copy_template = str_replace( "[/xfgiven_{$value[0]}]", "", $tpl2->copy_template );
							}
							
							if(strpos( $tpl2->copy_template, "[ifxfvalue" ) !== false ) {
								$tpl2->copy_template = preg_replace_callback ( "#\\[ifxfvalue(.+?)\\](.+?)\\[/ifxfvalue\\]#is", "check_xfvalue", $tpl2->copy_template );
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
				
							$tpl2->copy_template = str_replace( "[xfvalue_{$value[0]}]", $xfieldsdata[$value[0]], $tpl2->copy_template );
			
							if ( preg_match( "#\\[xfvalue_{$preg_safe_name} limit=['\"](.+?)['\"]\\]#i", $tpl2->copy_template, $matches ) ) {
								$count= intval($matches[1]);
					
								$xfieldsdata[$value[0]] = str_replace( "</p><p>", " ", $xfieldsdata[$value[0]] );
								$xfieldsdata[$value[0]] = strip_tags( $xfieldsdata[$value[0]], "<br>" );
								$xfieldsdata[$value[0]] = trim(str_replace( "<br>", " ", str_replace( "<br />", " ", str_replace( "\n", " ", str_replace( "\r", "", $xfieldsdata[$value[0]] ) ) ) ));
					
								if( $count AND dle_strlen( $xfieldsdata[$value[0]], $config['charset'] ) > $count ) {
										
									$xfieldsdata[$value[0]] = dle_substr( $xfieldsdata[$value[0]], 0, $count, $config['charset'] );
										
									if( ($temp_dmax = dle_strrpos( $xfieldsdata[$value[0]], ' ', $config['charset'] )) ) $xfieldsdata[$value[0]] = dle_substr( $xfieldsdata[$value[0]], 0, $temp_dmax, $config['charset'] );
									
								}
					
								$tpl2->set( $matches[0], $xfieldsdata[$value[0]] );
					
							}

						}
					}

					$tpl2->compile( 'content' );
				
				}

				$related_buffer = $tpl2->result['content'];
				unset($tpl2);
				$db->free();

				if ( $first_show ) {
					if ( count($related_ids) ) {
						$related_ids = implode(",",$related_ids);
						$db->query( "UPDATE " . PREFIX . "_post_extras SET related_ids='{$related_ids}' WHERE news_id='{$row['id']}'" );
					}
				}

				if ( $allow_full_cache ) create_cache( "related", $related_buffer, $row['id'].$config['skin'], true );
			}
			
			if ( $related_buffer ) {

				$tpl->set( '[related-news]', "" );
				$tpl->set( '[/related-news]', "" );

			} else $tpl->set_block( "'\\[related-news\\](.*?)\\[/related-news\\]'si", "" );

			$tpl->set( '{related-news}', $related_buffer );
		
		}
		
		if( $is_logged ) {
			
			$fav_arr = explode( ',', $member_id['favorites'] );
			
			if( ! in_array( $row['id'], $fav_arr ) ) {

				$tpl->set( '{favorites}', "<a id=\"fav-id-" . $row['id'] . "\" href=\"$PHP_SELF?do=favorites&amp;doaction=add&amp;id=" . $row['id'] . "\"><img src=\"" . $config['http_home_url'] . "templates/{$config['skin']}/dleimages/plus_fav.gif\" onclick=\"doFavorites('" . $row['id'] . "', 'plus', 0); return false;\" title=\"" . $lang['news_addfav'] . "\" style=\"vertical-align: middle;border: none;\" alt=\"\" /></a>" );
				$tpl->set( '[add-favorites]', "<a id=\"fav-id-" . $row['id'] . "\" onclick=\"doFavorites('" . $row['id'] . "', 'plus', 1); return false;\" href=\"$PHP_SELF?do=favorites&amp;doaction=add&amp;id=" . $row['id'] . "\">" );
				$tpl->set( '[/add-favorites]', "</a>" );
				$tpl->set_block( "'\\[del-favorites\\](.*?)\\[/del-favorites\\]'si", "" );
			} else { 

				$tpl->set( '{favorites}', "<a id=\"fav-id-" . $row['id'] . "\" href=\"$PHP_SELF?do=favorites&amp;doaction=del&amp;id=" . $row['id'] . "\"><img src=\"" . $config['http_home_url'] . "templates/{$config['skin']}/dleimages/minus_fav.gif\" onclick=\"doFavorites('" . $row['id'] . "', 'minus', 0); return false;\" title=\"" . $lang['news_minfav'] . "\" style=\"vertical-align: middle;border: none;\" alt=\"\" /></a>" );
				$tpl->set( '[del-favorites]', "<a id=\"fav-id-" . $row['id'] . "\" onclick=\"doFavorites('" . $row['id'] . "', 'minus', 1); return false;\" href=\"$PHP_SELF?do=favorites&amp;doaction=del&amp;id=" . $row['id'] . "\">" );
				$tpl->set( '[/del-favorites]', "</a>" );
				$tpl->set_block( "'\\[add-favorites\\](.*?)\\[/add-favorites\\]'si", "" );
			}

			$tpl->set( '[complaint]', "<a href=\"javascript:AddComplaint('" . $row['id'] . "', 'news')\">" );
			$tpl->set( '[/complaint]', "</a>" );

		
		} else {
			$tpl->set( '{favorites}', "" );
			$tpl->set_block( "'\\[complaint\\](.*?)\\[/complaint\\]'si", "" );
			$tpl->set_block( "'\\[add-favorites\\](.*?)\\[/add-favorites\\]'si", "" );
			$tpl->set_block( "'\\[del-favorites\\](.*?)\\[/del-favorites\\]'si", "" );
		}
		
		if( $row['votes'] ) $tpl->set( '{poll}', $tpl->result['poll'] );
		else $tpl->set( '{poll}', '' );
		
		if( $config['allow_banner'] ) include_once ENGINE_DIR . '/modules/banners.php';
		
		if( $config['allow_banner'] AND count( $banners ) ) {
			
			foreach ( $banners as $name => $value ) {
				$tpl->copy_template = str_replace( "{banner_" . $name . "}", $value, $tpl->copy_template );

				if ( $value ) {
					$tpl->copy_template = str_replace ( "[banner_" . $name . "]", "", $tpl->copy_template );
					$tpl->copy_template = str_replace ( "[/banner_" . $name . "]", "", $tpl->copy_template );
				}
			}
		}
		
		$tpl->set_block( "'{banner_(.*?)}'si", "" );
		$tpl->set_block ( "'\\[banner_(.*?)\\](.*?)\\[/banner_(.*?)\\]'si", "" );

		$tpl->set( '{title}', $row['title'] );
		
		if ( preg_match( "#\\{title limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches ) ) {
			$count= intval($matches[1]);
			$row['title'] = strip_tags( $row['title'] );

			if( $count AND dle_strlen( $row['title'], $config['charset'] ) > $count ) {
		
				$row['title'] = dle_substr( $row['title'], 0, $count, $config['charset'] );
					
				if( ($temp_dmax = dle_strrpos( $row['title'], ' ', $config['charset'] )) ) $row['title'] = dle_substr( $row['title'], 0, $temp_dmax, $config['charset'] );
				
			}
			$tpl->set( $matches[0], $row['title'] );
		
		}

		$row['short_story'] = stripslashes($row['short_story']);
		$row['full_story'] = stripslashes($row['full_story']);

		if ($config['allow_links'] AND function_exists('replace_links') AND isset($replace_links['news']) ) {
			$row['short_story'] = replace_links ( $row['short_story'], $replace_links['news'] );
			$row['full_story'] = replace_links ( $row['full_story'], $replace_links['news'] );
		}

		if (stripos ( $tpl->copy_template, "{image-" ) !== false) {

			$images = array();
			preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $row['short_story'], $media);
			$data=preg_replace('/(img|src)("|\'|="|=\')(.*)/i',"$3",$media[0]);
	
			foreach($data as $url) {
				$info = pathinfo($url);
				if (isset($info['extension'])) {
					if ($info['filename'] == "spoiler-plus" OR $info['filename'] == "spoiler-minus" ) continue;
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


		if (stripos ( $tpl->copy_template, "{fullimage-" ) !== false) {

			$images = array();
			preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $row['full_story'], $media);
			$data=preg_replace('/(img|src)("|\'|="|=\')(.*)/i',"$3",$media[0]);
	
			foreach($data as $url) {
				$info = pathinfo($url);
				if (isset($info['extension'])) {
					if ($info['filename'] == "spoiler-plus" OR $info['filename'] == "spoiler-minus" ) continue;
					$info['extension'] = strtolower($info['extension']);
					if (($info['extension'] == 'jpg') || ($info['extension'] == 'jpeg') || ($info['extension'] == 'gif') || ($info['extension'] == 'png')) array_push($images, $url);
				}
			}
	
			if ( count($images) ) {
				$i=0;
				foreach($images as $url) {
					$i++;
					$tpl->copy_template = str_replace( '{fullimage-'.$i.'}', $url, $tpl->copy_template );
					$tpl->copy_template = str_replace( '[fullimage-'.$i.']', "", $tpl->copy_template );
					$tpl->copy_template = str_replace( '[/fullimage-'.$i.']', "", $tpl->copy_template );
				}
	
			}
	
			$tpl->copy_template = preg_replace( "#\[fullimage-(.+?)\](.+?)\[/fullimage-(.+?)\]#is", "", $tpl->copy_template );
			$tpl->copy_template = preg_replace( "#\\{fullimage-(.+?)\\}#i", "{THEME}/dleimages/no_image.jpg", $tpl->copy_template );
	
		}

		$images = array();
		$allcontent = $row['full_story'].$row['short_story'].stripslashes($row['xfields']);
		preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $allcontent, $media);
		$data=preg_replace('/(img|src)("|\'|="|=\')(.*)/i',"$3",$media[0]);
	
		foreach($data as $url) {
			$info = pathinfo($url);
			if (isset($info['extension'])) {
				if ($info['filename'] == "spoiler-plus" OR $info['filename'] == "spoiler-minus" ) continue;
				$info['extension'] = strtolower($info['extension']);
				if (($info['extension'] == 'jpg' || $info['extension'] == 'jpeg' || $info['extension'] == 'gif' || $info['extension'] == 'png') AND !in_array($url, $images) ) array_push($images, $url);
			}
		}

		if ( count($images) ) $social_tags['image'] = $images[0];

		if ( preg_match("#<!--dle_video_begin:(.+?)-->#is", $allcontent, $media) ){
			$media[1] = explode( ",", trim( $media[1] ) );

			if (count($media[1]) > 1 )  $media[1] = $media[1][1]; else $media[1] = $media[1][0];

			$media[1] = explode( "|", $media[1] );
			$social_tags['video'] = $media[1][0];

		}

		if ( preg_match("#<!--dle_audio_begin:(.+?)-->#is", $allcontent, $media) ){
			$media[1] = explode( ",", trim( $media[1] ) );

			if (count($media[1]) > 1 )  $media[1] = $media[1][1]; else $media[1] = $media[1][0];

			$social_tags['audio'] = $media[1];

		}

		unset($allcontent);

		if ($smartphone_detected) {

			if (!$config['allow_smart_format']) {

					$row['short_story'] = strip_tags( $row['short_story'], '<p><br><a>' );
					$row['full_story'] = strip_tags( $row['full_story'], '<p><br><a>' );

			} else {

				if ( !$config['allow_smart_images'] ) {
	
					$row['short_story'] = preg_replace( "#<!--TBegin-->(.+?)<!--TEnd-->#is", "", $row['short_story'] );
					$row['short_story'] = preg_replace( "#<img(.+?)>#is", "", $row['short_story'] );
					$row['full_story'] = preg_replace( "#<!--TBegin-->(.+?)<!--TEnd-->#is", "", $row['full_story'] );
					$row['full_story'] = preg_replace( "#<img(.+?)>#is", "", $row['full_story'] );
	
				}
	
				if ( !$config['allow_smart_video'] ) {
	
					$row['short_story'] = preg_replace( "#<!--dle_video_begin(.+?)<!--dle_video_end-->#is", "", $row['short_story'] );
					$row['short_story'] = preg_replace( "#<!--dle_audio_begin(.+?)<!--dle_audio_end-->#is", "", $row['short_story'] );
					$row['short_story'] = preg_replace( "#<!--dle_media_begin(.+?)<!--dle_media_end-->#is", "", $row['short_story'] );
					$row['full_story'] = preg_replace( "#<!--dle_video_begin(.+?)<!--dle_video_end-->#is", "", $row['full_story'] );
					$row['full_story'] = preg_replace( "#<!--dle_audio_begin(.+?)<!--dle_audio_end-->#is", "", $row['full_story'] );
					$row['full_story'] = preg_replace( "#<!--dle_media_begin(.+?)<!--dle_media_end-->#is", "", $row['full_story'] );
	
				}

			}

		}
		$tpl->set( '{comments}', "<!--dlecomments-->" );
		$tpl->set( '{addcomments}', "<!--dleaddcomments-->" );
		$tpl->set( '{navigation}', "<!--dlenavigationcomments-->" );

		$tpl->set( '{short-story}', $row['short_story'] );

		$tpl->set( '{full-story}', $row['full_story'] );

		if ( preg_match( "#\\{full-story limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches ) ) {
			$count= intval($matches[1]);
			
			$row['full_story'] = preg_replace( "#<!--TBegin(.+?)<!--TEnd-->#is", "", $row['full_story'] );
			$row['full_story'] = preg_replace( "#<!--MBegin(.+?)<!--MEnd-->#is", "", $row['full_story'] );
			$row['full_story'] = preg_replace( "'\[attachment=(.*?)\]'si", "", $row['full_story'] );
			$row['full_story'] = preg_replace ( "#\[hide\](.+?)\[/hide\]#ims", "", $row['full_story'] );
				
			$row['full_story'] = str_replace( "</p><p>", " ", $row['full_story'] );
			$row['full_story'] = strip_tags( $row['full_story'], "<br>" );
			$row['full_story'] = trim(str_replace( "<br>", " ", str_replace( "<br />", " ", str_replace( "\n", " ", str_replace( "\r", "", $row['full_story'] ) ) ) ));

			if( $count AND dle_strlen( $row['full_story'], $config['charset'] ) > $count ) {
					
				$row['full_story'] = dle_substr( $row['full_story'], 0, $count, $config['charset'] );
					
				if( ($temp_dmax = dle_strrpos( $row['full_story'], ' ', $config['charset'] )) ) $row['full_story'] = dle_substr( $row['full_story'], 0, $temp_dmax, $config['charset'] );
				
			}

			$tpl->set( $matches[0], $row['full_story'] );

		}

		if( strpos( $tpl->copy_template, "xfvalue" ) !== false OR strpos( $tpl->copy_template, "[xfgiven_" ) !== false ) {
			
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
				
				if ($config['allow_links'] AND $value[3] == "textarea" AND function_exists('replace_links')) $xfieldsdata[$value[0]] = replace_links ( $xfieldsdata[$value[0]], $replace_links['news'] );

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
		
		$xfieldsdata = $row['xfields'];
		$category_id = $row['category'];
		
		$tpl->compile( 'content' );

		if( $user_group[$member_id['user_group']]['allow_hide'] ) $tpl->result['content'] = str_ireplace( "[hide]", "", str_ireplace( "[/hide]", "", $tpl->result['content']) );
		else $tpl->result['content'] = preg_replace ( "#\[hide\](.+?)\[/hide\]#ims", "<div class=\"quote\">" . $lang['news_regus'] . "</div>", $tpl->result['content'] );

		if ( $config['allow_banner'] AND count($banner_in_news) AND !$view_template ){
	
			foreach ( $banner_in_news as $name) {
				$tpl->result['content'] = str_replace( "{banner_" . $name . "}", $banners[$name], $tpl->result['content'] );
	
				if( $banners[$name] ) {
					$tpl->result['content'] = str_replace ( "[banner_" . $name . "]", "", $tpl->result['content'] );
					$tpl->result['content'] = str_replace ( "[/banner_" . $name . "]", "", $tpl->result['content'] );
				}
			}
	
			$tpl->result['content'] = preg_replace( "'\\[banner_(.*?)\\](.*?)\\[/banner_(.*?)\\]'si", '', $tpl->result['content'] );
		
		}
		
		$news_id = $row['id'];
		$allow_comments = $row['allow_comm'];

		$allow_add = true;

		if ( $config['max_comments_days'] ) {

			if ($row['date'] < ($_TIME - ($config['max_comments_days'] * 3600 * 24)) )	$allow_add = false;

		}
		
		if( $view_template ) $allow_comments = false;
	
	}

	$tpl->clear();
	unset( $row );
	
	if( $config['files_allow'] ) if( strpos( $tpl->result['content'], "[attachment=" ) !== false ) {
		$tpl->result['content'] = show_attach( $tpl->result['content'], $news_id );
	}
	
	if( !$news_found AND !$perm ) msgbox( $lang['all_err_1'], "<b>{$user_group[$member_id['user_group']]['group_name']}</b> " . $lang['news_err_28'] );
	elseif( !$news_found ) {
		@header( "HTTP/1.0 404 Not Found" );
		msgbox( $lang['all_err_1'], $lang['news_err_12'] );
	}

//####################################################################################################################
//		 Просмотр комментариев
//####################################################################################################################
if( $allow_comments AND $news_found) {
	
	if( $comments_num > 0 ) {

		include_once ENGINE_DIR . '/classes/comments.class.php';
		$comments = new DLE_Comments( $db, $comments_num, intval($config['comm_nummers']) );

		if( $config['comm_msort'] == "" OR $config['comm_msort'] == "ASC" ) $comm_msort = "ASC"; else $comm_msort = "DESC";

		if( $config['tree_comments'] ) $comm_msort = "ASC";
		
		if( $config['allow_cmod'] ) $where_approve = " AND " . PREFIX . "_comments.approve=1";
		else $where_approve = "";

		$comments->query = "SELECT " . PREFIX . "_comments.id, post_id, " . PREFIX . "_comments.user_id, date, autor as gast_name, " . PREFIX . "_comments.email as gast_email, text, ip, is_register, " . PREFIX . "_comments.rating, " . PREFIX . "_comments.vote_num, " . PREFIX . "_comments.parent, name, " . USERPREFIX . "_users.email, news_num, comm_num, user_group, lastdate, reg_date, signature, foto, fullname, land, xfields FROM " . PREFIX . "_comments LEFT JOIN " . USERPREFIX . "_users ON " . PREFIX . "_comments.user_id=" . USERPREFIX . "_users.user_id WHERE " . PREFIX . "_comments.post_id = '$news_id'" . $where_approve . " ORDER BY " . PREFIX . "_comments.id " . $comm_msort;

		if ( $allow_full_cache AND $config['allow_comments_cache'] ) $allow_full_cache = $news_id; else $allow_full_cache = false;

		$comments->build_comments('comments.tpl', 'news', $allow_full_cache, $full_link );

		unset ($tpl->result['comments']);

		if( isset($_GET['news_page']) AND $_GET['news_page'] ) $user_query = "newsid=" . $newsid . "&amp;news_page=" . intval( $_GET['news_page'] ); else $user_query = "newsid=" . $newsid;

		$comments->build_navigation('navigation.tpl', $link_page . "{page}," . $news_name . ".html#comment", $user_query, $full_link);		

		unset ($comments);
		unset ($tpl->result['commentsnavigation']);
	
	}

	if ($is_logged AND $config['comments_restricted'] AND (($_TIME - $member_id['reg_date']) < ($config['comments_restricted'] * 86400)) ) {

		$lang['news_info_6'] = str_replace( '{days}', intval($config['comments_restricted']), $lang['news_info_8'] );
		$allow_add = false;

	}

	if (!isset($member_id['restricted'])) $member_id['restricted'] = false;
	
	if( $member_id['restricted'] AND $member_id['restricted_days'] AND $member_id['restricted_date'] < $_TIME ) {
		
		$member_id['restricted'] = 0;
		$db->query( "UPDATE LOW_PRIORITY " . USERPREFIX . "_users SET restricted='0', restricted_days='0', restricted_date='' WHERE user_id='{$member_id['user_id']}'" );
	
	}
	
	if( $user_group[$member_id['user_group']]['allow_addc'] AND $config['allow_comments'] AND $allow_add AND ($member_id['restricted'] != 2 AND $member_id['restricted'] != 3) ) {

		if( !$comments_num ) {		
			if( strpos ( $tpl->result['content'], "<!--dlecomments-->" ) !== false ) {
	
				$tpl->result['content'] = str_replace ( "<!--dlecomments-->", "\n<div id=\"dle-ajax-comments\"></div>\n", $tpl->result['content'] );
	
			} else $tpl->result['content'] .= "\n<div id=\"dle-ajax-comments\"></div>\n";
		}
		
		$tpl->load_template( 'addcomments.tpl' );

		if ($config['allow_subscribe'] AND $user_group[$member_id['user_group']]['allow_subscribe']) $allow_subscribe = true; else $allow_subscribe = false;
		
		if( $config['allow_comments_wysiwyg'] > 0 ) {
			$text='';
			include_once ENGINE_DIR . '/editor/comments.php';
			$bb_code = "";
			$allow_comments_ajax = true;
		} else
			include_once ENGINE_DIR . '/modules/bbcode.php';

		if ( $is_logged AND $user_group[$member_id['user_group']]['disable_comments_captcha'] AND $member_id['comm_num'] >= $user_group[$member_id['user_group']]['disable_comments_captcha'] ) {
		
			$user_group[$member_id['user_group']]['comments_question'] = false;
			$user_group[$member_id['user_group']]['captcha'] = false;
		
		}

		if( $user_group[$member_id['user_group']]['comments_question'] ) {

			$tpl->set( '[question]', "" );
			$tpl->set( '[/question]', "" );

			$question = $db->super_query("SELECT id, question FROM " . PREFIX . "_question ORDER BY RAND() LIMIT 1");
			$tpl->set( '{question}', "<span id=\"dle-question\">".htmlspecialchars( stripslashes( $question['question'] ), ENT_QUOTES, $config['charset'] )."</span>" );

			$_SESSION['question'] = $question['id'];

		} else {

			$tpl->set_block( "'\\[question\\](.*?)\\[/question\\]'si", "" );
			$tpl->set( '{question}', "" );

		}
		
		if( $user_group[$member_id['user_group']]['captcha'] ) {

			if ( $config['allow_recaptcha'] ) {

				$tpl->set( '[recaptcha]', "" );
				$tpl->set( '[/recaptcha]', "" );

				$tpl->set( '{recaptcha}', "<div class=\"g-recaptcha\" data-sitekey=\"{$config['recaptcha_public_key']}\" data-theme=\"{$config['recaptcha_theme']}\"></div>" );

				$tpl->set_block( "'\\[sec_code\\](.*?)\\[/sec_code\\]'si", "" );
				$tpl->set( '{reg_code}', "" );

			} else {

				$tpl->set( '[sec_code]', "" );
				$tpl->set( '[/sec_code]', "" );
				$path = parse_url( $config['http_home_url'] );
				$tpl->set( '{sec_code}', "<a onclick=\"reload(); return false;\" title=\"{$lang['reload_code']}\" href=\"#\"><span id=\"dle-captcha\"><img src=\"" . $path['path'] . "engine/modules/antibot/antibot.php\" alt=\"{$lang['reload_code']}\" width=\"160\" height=\"80\" /></span></a>" );
				$tpl->set_block( "'\\[recaptcha\\](.*?)\\[/recaptcha\\]'si", "" );
				$tpl->set( '{recaptcha}', "" );
			}

		} else {
			$tpl->set( '{sec_code}', "" );
			$tpl->set( '{recaptcha}', "" );
			$tpl->set_block( "'\\[recaptcha\\](.*?)\\[/recaptcha\\]'si", "" );
			$tpl->set_block( "'\\[sec_code\\](.*?)\\[/sec_code\\]'si", "" );
		}

		if( $config['allow_comments_wysiwyg'] > 0 ) {

			$tpl->set( '{editor}', $wysiwyg );

		} else {
			$tpl->set( '{editor}', $bb_code );

		}
		
		$tpl->set( '{text}', '' );
		$tpl->set( '{title}', $lang['news_addcom'] );

		if( $vk_url ) {
			$tpl->set( '[vk]', "" );
			$tpl->set( '[/vk]', "" );
			$tpl->set( '{vk_url}', $vk_url );	
		} else {
			$tpl->set_block( "'\\[vk\\](.*?)\\[/vk\\]'si", "" );
			$tpl->set( '{vk_url}', '' );	
		}
		if( $odnoklassniki_url ) {
			$tpl->set( '[odnoklassniki]', "" );
			$tpl->set( '[/odnoklassniki]', "" );
			$tpl->set( '{odnoklassniki_url}', $odnoklassniki_url );
		} else {
			$tpl->set_block( "'\\[odnoklassniki\\](.*?)\\[/odnoklassniki\\]'si", "" );
			$tpl->set( '{odnoklassniki_url}', '' );	
		}
		if( $facebook_url ) {
			$tpl->set( '[facebook]', "" );
			$tpl->set( '[/facebook]', "" );
			$tpl->set( '{facebook_url}', $facebook_url );	
		} else {
			$tpl->set_block( "'\\[facebook\\](.*?)\\[/facebook\\]'si", "" );
			$tpl->set( '{facebook_url}', '' );	
		}
		if( $google_url ) {
			$tpl->set( '[google]', "" );
			$tpl->set( '[/google]', "" );
			$tpl->set( '{google_url}', $google_url );
		} else {
			$tpl->set_block( "'\\[google\\](.*?)\\[/google\\]'si", "" );
			$tpl->set( '{google_url}', '' );	
		}
		if( $mailru_url ) {
			$tpl->set( '[mailru]', "" );
			$tpl->set( '[/mailru]', "" );
			$tpl->set( '{mailru_url}', $mailru_url );	
		} else {
			$tpl->set_block( "'\\[mailru\\](.*?)\\[/mailru\\]'si", "" );
			$tpl->set( '{mailru_url}', '' );	
		}
		if( $yandex_url ) {
			$tpl->set( '[yandex]', "" );
			$tpl->set( '[/yandex]', "" );
			$tpl->set( '{yandex_url}', $yandex_url );
		} else {
			$tpl->set_block( "'\\[yandex\\](.*?)\\[/yandex\\]'si", "" );
			$tpl->set( '{yandex_url}', '' );
		}
		
		if( ! $is_logged ) {
			$tpl->set( '[not-logged]', '' );
			$tpl->set( '[/not-logged]', '' );
		} else
			$tpl->set_block( "'\\[not-logged\\](.*?)\\[/not-logged\\]'si", "" );
		
		if( $is_logged ) $hidden = "<input type=\"hidden\" name=\"name\" id=\"name\" value=\"{$member_id['name']}\" /><input type=\"hidden\" name=\"mail\" id=\"mail\" value=\"\" />";
		else $hidden = "";
		
		$tpl->copy_template = "<form  method=\"post\" name=\"dle-comments-form\" id=\"dle-comments-form\" >" . $tpl->copy_template . "
		<input type=\"hidden\" name=\"subaction\" value=\"addcomment\" />{$hidden}
		<input type=\"hidden\" name=\"post_id\" id=\"post_id\" value=\"$news_id\" /></form>";

		$onload_scripts[] = <<<HTML
$('#dle-comments-form').submit(function() {
	doAddComments();
	return false;
});
HTML;


		if ( $user_group[$member_id['user_group']]['captcha'] AND $config['allow_recaptcha'] ) {

		$tpl->copy_template .= <<<HTML
<script src='https://www.google.com/recaptcha/api.js?hl={$lang['wysiwyg_language']}' async defer></script>
HTML;
		
		}
		
		$tpl->compile( 'addcomments' );
		$tpl->clear();

		if ( strpos ( $tpl->result['content'], "<!--dleaddcomments-->" ) !== false ) {

			$tpl->result['content'] = str_replace ( "<!--dleaddcomments-->", $tpl->result['addcomments'], $tpl->result['content'] );

		} else {

			$tpl->result['content'] .= $tpl->result['addcomments'];

		}

		unset ($tpl->result['addcomments']);

	} elseif( $member_id['restricted'] ) {
		
		$tpl->load_template( 'info.tpl' );
		
		if( $member_id['restricted_days'] ) {
			
			$tpl->set( '{error}', $lang['news_info_2'] );
			$tpl->set( '{date}', langdate( "j F Y H:i", $member_id['restricted_date'] ) );
		
		} else
			$tpl->set( '{error}', $lang['news_info_3'] );
		
		$tpl->set( '{title}', $lang['all_info'] );
		$tpl->compile( 'content' );
		$tpl->clear();

	} elseif( !$allow_add ) {

		$tpl->load_template( 'info.tpl' );
		$tpl->set( '{error}', $lang['news_info_6'] );
		$tpl->set( '{days}', $config['max_comments_days'] );
		$tpl->set( '{title}', $lang['all_info'] );
		$tpl->compile( 'content' );
		$tpl->clear();
	
	} elseif( $config['allow_comments'] ) {
		
		$tpl->load_template( 'info.tpl' );
		$tpl->set( '{error}', $lang['news_info_1'] );
		$tpl->set( '{group}', $user_group[$member_id['user_group']]['group_name'] );
		$tpl->set( '{title}', $lang['all_info'] );
		$tpl->compile( 'content' );
		$tpl->clear();
	
	}
}
?>