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
 Файл: main.php
-----------------------------------------------------
 Назначение: Общее формирование страницы сайта
=====================================================
*/
if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

$home_url = clean_url($config['http_home_url']);

if ($home_url AND clean_url( $_SERVER['HTTP_HOST'] ) != $home_url ) {

	$replace_url = array ();
	$replace_url[0] = $home_url;
	$replace_url[1] = clean_url ( $_SERVER['HTTP_HOST'] );

} else $replace_url = false;

$tpl->load_template ( 'main.tpl' );

$tpl->set ( '{calendar}', $tpl->result['calendar'] );
$tpl->set ( '{archives}', $tpl->result['archive'] );
$tpl->set ( '{tags}', $tpl->result['tags_cloud'] );
$tpl->set ( '{vote}', $tpl->result['vote'] );
$tpl->set ( '{topnews}', $tpl->result['topnews'] );
$tpl->set ( '{login}', $tpl->result['login_panel'] );
$tpl->set ( '{speedbar}', $tpl->result['speedbar'] );

if ( $dle_module == "showfull" AND $news_found ) {
	
	if( strpos( $tpl->copy_template, "related-news" ) !== false ) {
		$tpl->set( '[related-news]', "" );
		$tpl->set( '[/related-news]', "" );
		$tpl->set( '{related-news}', $related_buffer );
	}
	
	if( strpos( $tpl->copy_template, "xfvalue" ) !== false OR strpos( $tpl->copy_template, "[xfgiven_" ) !== false ) {

		$xfieldsdata = xfieldsdataload( $xfieldsdata );
		
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
		
				$tpl->copy_template = str_replace( $matches[0], $xfieldsdata[$value[0]], $tpl->copy_template );
		
			}
			
			if( $user_group[$member_id['user_group']]['allow_hide'] ) $tpl->copy_template = str_ireplace( "[hide]", "", str_ireplace( "[/hide]", "", $tpl->copy_template) );
			else $tpl->copy_template = preg_replace ( "#\[hide\](.+?)\[/hide\]#ims", "<div class=\"quote\">" . $lang['news_regus'] . "</div>", $tpl->copy_template );

			if( $config['files_allow'] ) if( strpos( $tpl->copy_template, "[attachment=" ) !== false ) {
				$tpl->copy_template = show_attach( $tpl->copy_template, NEWS_ID );
			}
	
		}
	}
		
} else {
	
	if( strpos( $tpl->copy_template, "related-news" ) !== false ) {
		$tpl->set( '{related-news}', "" );
		$tpl->set_block( "'\\[related-news\\](.*?)\\[/related-news\\]'si", "" );
	}
	
	if( strpos( $tpl->copy_template, "[xfvalue_" ) !== false OR strpos( $tpl->copy_template, "[xfgiven_" ) !== false ) {
		$tpl->copy_template = preg_replace( "'\\[xfnotgiven_(.*?)\\](.*?)\\[/xfnotgiven_(.*?)\\]'is", "", $tpl->copy_template );
		$tpl->copy_template = preg_replace( "'\\[xfgiven_(.*?)\\](.*?)\\[/xfgiven_(.*?)\\]'is", "", $tpl->copy_template );
		$tpl->copy_template = preg_replace( "'\\[xfvalue_(.*?)\\]'i", "", $tpl->copy_template );
	}
	if( strpos( $tpl->copy_template, "[ifxfvalue" ) !== false ) {
		$tpl->copy_template = preg_replace( "#\\[ifxfvalue(.+?)\\](.+?)\\[/ifxfvalue\\]#is", "", $tpl->copy_template );
	}

}

if ($config['allow_skin_change']) $tpl->set ( '{changeskin}', ChangeSkin ( ROOT_DIR . '/templates', $config['skin'] ) );

if (count ( $banners ) and $config['allow_banner']) {

	foreach ( $banners as $name => $value ) {
		$tpl->copy_template = str_replace ( "{banner_" . $name . "}", $value, $tpl->copy_template );
		if ( $value ) {
			$tpl->copy_template = str_replace ( "[banner_" . $name . "]", "", $tpl->copy_template );
			$tpl->copy_template = str_replace ( "[/banner_" . $name . "]", "", $tpl->copy_template );
		}
	}

}

$tpl->set_block ( "'{banner_(.*?)}'si", "" );
$tpl->set_block ( "'\\[banner_(.*?)\\](.*?)\\[/banner_(.*?)\\]'si", "" );

if (count ( $informers ) and $config['rss_informer']) {
	foreach ( $informers as $name => $value ) {
		$tpl->copy_template = str_replace ( "{inform_" . $name . "}", $value, $tpl->copy_template );
	}
}

if ($allow_active_news AND $news_found AND $config['allow_change_sort'] AND $dle_module != "userinfo") {

	$tpl->set ( '[sort]', "" );
	$tpl->set ( '{sort}', news_sort ( $do ) );
	$tpl->set ( '[/sort]', "" );

} else {

	$tpl->set_block ( "'\\[sort\\](.*?)\\[/sort\\]'si", "" );

}

if (stripos ( $tpl->copy_template, "[category=" ) !== false) {
	$tpl->copy_template = preg_replace_callback ( "#\\[(category)=(.+?)\\](.*?)\\[/category\\]#is", "check_category", $tpl->copy_template );
}

if (stripos ( $tpl->copy_template, "[not-category=" ) !== false) {
	$tpl->copy_template = preg_replace_callback ( "#\\[(not-category)=(.+?)\\](.*?)\\[/not-category\\]#is", "check_category", $tpl->copy_template );
}


if (stripos ( $tpl->copy_template, "[static=" ) !== false) {
	$tpl->copy_template = preg_replace_callback ( "#\\[(static)=(.+?)\\](.*?)\\[/static\\]#is", "check_static", $tpl->copy_template );
}

if (stripos ( $tpl->copy_template, "[not-static=" ) !== false) {
	$tpl->copy_template = preg_replace_callback ( "#\\[(not-static)=(.+?)\\](.*?)\\[/not-static\\]#is", "check_static", $tpl->copy_template );
}

if (stripos ( $tpl->copy_template, "{customcomments" ) !== false) {
	$tpl->copy_template = preg_replace_callback ( "#\\{customcomments(.+?)\\}#i", "custom_comments", $tpl->copy_template );
}

if (stripos ( $tpl->copy_template, "{custom" ) !== false) {
	$tpl->copy_template = preg_replace_callback ( "#\\{custom(.+?)\\}#i", "custom_print", $tpl->copy_template );
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

$config['http_home_url'] = explode ( "index.php", strtolower ( $_SERVER['PHP_SELF'] ) );
$config['http_home_url'] = reset ( $config['http_home_url'] );

if ( !$user_group[$member_id['user_group']]['allow_admin'] ) $config['admin_path'] = "";

$ajax .= <<<HTML
{$pm_alert}<script type="text/javascript">
<!--
var dle_root       = '{$config['http_home_url']}';
var dle_admin      = '{$config['admin_path']}';
var dle_login_hash = '{$dle_login_hash}';
var dle_group      = {$member_id['user_group']};
var dle_skin       = '{$config['skin']}';
var dle_wysiwyg    = '{$config['allow_comments_wysiwyg']}';
var quick_wysiwyg  = '{$config['allow_quick_wysiwyg']}';
var dle_act_lang   = ["{$lang['p_yes']}", "{$lang['p_no']}", "{$lang['p_enter']}", "{$lang['p_cancel']}", "{$lang['p_save']}", "{$lang['p_del']}", "{$lang['ajax_info']}"];
var menu_short     = '{$lang['menu_short']}';
var menu_full      = '{$lang['menu_full']}';
var menu_profile   = '{$lang['menu_profile']}';
var menu_send      = '{$lang['menu_send']}';
var menu_uedit     = '{$lang['menu_uedit']}';
var dle_info       = '{$lang['p_info']}';
var dle_confirm    = '{$lang['p_confirm']}';
var dle_prompt     = '{$lang['p_prompt']}';
var dle_req_field  = '{$lang['comm_req_f']}';
var dle_del_agree  = '{$lang['news_delcom']}';
var dle_spam_agree = '{$lang['mark_spam']}';
var dle_complaint  = '{$lang['add_to_complaint']}';
var dle_big_text   = '{$lang['big_text']}';
var dle_orfo_title = '{$lang['orfo_title']}';
var dle_p_send     = '{$lang['p_send']}';
var dle_p_send_ok  = '{$lang['p_send_ok']}';
var dle_save_ok    = '{$lang['n_save_ok']}';
var dle_reply_title= '{$lang['reply_comments']}';
var dle_tree_comm  = '{$dle_tree_comments}';
var dle_del_news   = '{$lang['news_delnews']}';\n
HTML;

if ($user_group[$member_id['user_group']]['allow_all_edit']) {

	$ajax .= <<<HTML
var dle_notice     = '{$lang['btn_notice']}';
var dle_p_text     = '{$lang['p_text']}';
var dle_del_msg    = '{$lang['p_message']}';
var allow_dle_delete_news   = true;\n
HTML;

} else {

	$ajax .= <<<HTML
var allow_dle_delete_news   = false;\n
HTML;

}

if ($config['fast_search'] AND $user_group[$member_id['user_group']]['allow_search']) {

	$ajax .= <<<HTML
var dle_search_delay   = false;
var dle_search_value   = '';
HTML;

	$onload_scripts[] = "FastSearch();";

}

if (strpos ( $tpl->result['content'], "<pre" ) !== false) {

	$js_array[] = "engine/classes/highlight/highlight.code.js";
	$onload_scripts[] = "$('pre code').each(function(i, e) {hljs.highlightBlock(e, null)});";

}


if (strpos ( $tpl->result['content'], "hs.expand" ) !== false OR strpos ( $tpl->copy_template, "hs.expand" ) !== false OR strpos ( $tpl->result['content'], "highslide" ) !== false OR strpos ( $tpl->copy_template, "highslide" ) !== false) {

	$js_array[] = "engine/classes/highslide/highslide.js";

	if ($config['thumb_dimming']) $dimming = "hs.dimmingOpacity = 0.60;"; else $dimming = "";

	if ($config['thumb_gallery'] AND ($dle_module == "showfull" OR $dle_module == "static") ) {

	$gallery = "hs.align = 'center'; hs.transitions = ['expand', 'crossfade']; hs.addSlideshow({interval: 4000, repeat: false, useControls: true, fixedControls: 'fit', overlayOptions: { opacity: .75, position: 'bottom center', hideOnMouseOut: true } });";

	} else $gallery = "";

	switch ( $config['outlinetype'] ) {

		case 1 :
			$type = "hs.wrapperClassName = 'wide-border';";
			break;

		case 2 :
			$type = "hs.wrapperClassName = 'borderless';";
			break;

		case 3 :
			$type = "hs.wrapperClassName = 'less';\nhs.outlineType = null;";
			break;

		default :
			$type = "hs.outlineType = 'rounded-white';";
			break;


	}

	$ajax .= <<<HTML

hs.graphicsDir = '{$config['http_home_url']}engine/classes/highslide/graphics/';
{$type}
hs.numberOfImagesToPreload = 0;
hs.showCredits = false;
{$dimming}
hs.lang = { loadingText : '{$lang['loading']}', playTitle : '{$lang['thumb_playtitle']}', pauseTitle:'{$lang['thumb_pausetitle']}', previousTitle : '{$lang['thumb_previoustitle']}', nextTitle :'{$lang['thumb_nexttitle']}',moveTitle :'{$lang['thumb_movetitle']}', closeTitle :'{$lang['thumb_closetitle']}',fullExpandTitle:'{$lang['thumb_expandtitle']}',restoreTitle:'{$lang['thumb_restore']}',focusTitle:'{$lang['thumb_focustitle']}',loadingTitle:'{$lang['thumb_cancel']}'
};
{$gallery}

HTML;

}

if ( $config['allow_share'] AND ($dle_module == "showfull" OR $dle_module == "static") ) {

	if ( preg_match("/(msie)/i", $_SERVER['HTTP_USER_AGENT']) ) {

		$js_array[] = "engine/classes/masha/ierange.js";
		$js_array[] = "engine/classes/masha/masha.js";

	} else $js_array[] = "engine/classes/masha/masha.js";
}


if (strpos ( $tpl->result['content'], "<video" ) !== false OR strpos ( $tpl->result['content'], "<audio" ) !== false OR strpos ( $tpl->copy_template, "<video" ) !== false OR strpos ( $tpl->copy_template, "<audio" ) !== false) {
	
	$js_array[] = "engine/classes/html5player/mediaelement-and-player.min.js";
	$video_found = true;
	
} else $video_found = false;

$js_array = build_js($js_array, $config);

if ($allow_comments_ajax AND ($config['allow_comments_wysiwyg'] > 0 OR $config['allow_quick_wysiwyg'])) {
	$lang['wysiwyg_language'] = totranslit( $lang['wysiwyg_language'], false, false );

	if ( $config['allow_quick_wysiwyg'] == "2" OR $config['allow_comments_wysiwyg'] == "2" ) {

		$js_array .="\n<script type=\"text/javascript\" src=\"{$config['http_home_url']}engine/editor/jscripts/tiny_mce/tinymce.min.js\"></script>";

	}

	if ( $config['allow_quick_wysiwyg'] == "1" OR $config['allow_comments_wysiwyg'] == "1" ) {
		$js_array .="\n<script type=\"text/javascript\" src=\"{$config['http_home_url']}engine/editor/scripts/language/{$lang['wysiwyg_language']}/editor_lang.js\"></script>";
		$js_array .="\n<script type=\"text/javascript\" src=\"{$config['http_home_url']}engine/editor/scripts/innovaeditor.js\"></script>";
	}
}

if ($config['allow_admin_wysiwyg'] == "1" OR $config['allow_site_wysiwyg'] == "1" OR $config['allow_static_wysiwyg'] == "1") {
	$js_array .="\n<script type=\"text/javascript\" src=\"//ajax.googleapis.com/ajax/libs/webfont/1/webfont.js\"></script>";
	$js_array .="\n<script type=\"text/javascript\" src=\"{$config['http_home_url']}engine/editor/scripts/webfont.js\"></script>";
	$js_array .="\n<link media=\"screen\" href=\"{$config['http_home_url']}engine/editor/css/default.css\" type=\"text/css\" rel=\"stylesheet\" />";
}

if ( $video_found ) {
	$js_array .="\n<link media=\"screen\" href=\"{$config['http_home_url']}engine/classes/html5player/mediaelementplayer.css\" type=\"text/css\" rel=\"stylesheet\" />";
}

if( $_SERVER['QUERY_STRING'] AND !$tpl->result['content'] AND !$tpl->result['info'] AND stripos ( $tpl->copy_template, "{content}" ) !== false ) {

	@header( "HTTP/1.0 404 Not Found" );
	msgbox( $lang['all_err_1'], $lang['news_err_27'] );

}

if ( count($onload_scripts) ) {
	
	$onload_scripts =implode("\n", $onload_scripts);

	$ajax .= <<<HTML

jQuery(function($){
{$onload_scripts}
});
HTML;

} else $onload_scripts="";

$ajax .= <<<HTML

//-->
</script>
HTML;

if (stripos ( $tpl->copy_template, "{jsfiles}" ) !== false) {
	$tpl->set ( '{headers}', $metatags );
	$tpl->set ( '{jsfiles}', $js_array );
} else {
	$tpl->set ( '{headers}', $metatags."\n".$js_array );
}

$tpl->set ( '{AJAX}', $ajax );
$tpl->set ( '{info}',  $tpl->result['info'] );

$tpl->set ( '{content}', "<div id='dle-content'>" . $tpl->result['content'] . "</div>" );

$tpl->compile ( 'main' );

if ($config['allow_links']) $tpl->result['main'] = replace_links ( $tpl->result['main'], $replace_links['all'] );

$tpl->result['main'] = str_ireplace( '{THEME}', $config['http_home_url'] . 'templates/' . $config['skin'], $tpl->result['main'] );

if ($replace_url) $tpl->result['main'] = str_replace ( $replace_url[0]."/", $replace_url[1]."/", $tpl->result['main'] );

$tpl->result['main'] = str_replace ( 'src="http://'.$_SERVER['HTTP_HOST'].'/', 'src="/', $tpl->result['main'] );

echo $tpl->result['main'];

$tpl->global_clear();

$db->close();

echo "\n<!-- Developed by Webman (https://www.facebook.com/ariukaweb/) -->\r\n";

GzipOut();

?>