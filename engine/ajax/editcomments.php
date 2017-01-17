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
 Файл: editcomments.php
-----------------------------------------------------
 Назначение: AJAX для редакторования
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
	
	$config['http_home_url'] = explode( "engine/ajax/editcomments.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset( $config['http_home_url'] );
	$config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';

dle_session();

$_COOKIE['dle_skin'] = trim(totranslit( $_COOKIE['dle_skin'], false, false ));
$_TIME = time ();

if( $_COOKIE['dle_skin'] ) {
	if( @is_dir( ROOT_DIR . '/templates/' . $_COOKIE['dle_skin'] ) ) {
		$config['skin'] = $_COOKIE['dle_skin'];
	}
}

if( $config["lang_" . $config['skin']] ) {
	
	if ( file_exists( ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng' ) ) {
		@include_once (ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng');
	} else die("Language file not found");

} else {
	
	include_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';

}

$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];

require_once ENGINE_DIR . '/classes/parse.class.php';
require_once ENGINE_DIR . '/modules/sitelogin.php';


$area = totranslit($_REQUEST['area'], true, false);

if ( !$area) $area = "news";

$allowed_areas = array(

					'news' => array (
									'comments_table' => 'comments',
									),

					'ajax' => array (
									'comments_table' => 'comments',
									),

					'lastcomments' => array (
									'comments_table' => 'comments',
									),

				);

if (! is_array($allowed_areas[$area]) ) die( "error" );

if( $config['allow_comments_wysiwyg'] > 0) {
	$parse = new ParseFilter( Array ('div','span','p','br','strong','em','ul','li','ol', 'b', 'u', 'i', 's'), Array (), 0, 1 );
} else {
	$parse = new ParseFilter();
}

$parse->safe_mode = true;

if( ! $is_logged ) die( "error" );

$id = intval( $_REQUEST['id'] );

if( ! $id ) die( "error" );

//################# Определение групп пользователей
$user_group = get_vars( "usergroup" );

if( ! $user_group ) {
	$user_group = array ();
	
	$db->query( "SELECT * FROM " . USERPREFIX . "_usergroups ORDER BY id ASC" );
	
	while ( $row = $db->get_row() ) {
		
		$user_group[$row['id']] = array ();
		
		foreach ( $row as $key => $value ) {
			$user_group[$row['id']][$key] = stripslashes($value);
		}
	
	}
	set_vars( "usergroup", $user_group );
	$db->free();
}

$parse->allow_url = $user_group[$member_id['user_group']]['allow_url'];
$parse->allow_image = $user_group[$member_id['user_group']]['allow_image'];

if( $_REQUEST['action'] == "edit" ) {
	$row = $db->super_query( "SELECT id, date, autor, text, is_register FROM " . PREFIX . "_{$allowed_areas[$area]['comments_table']} where id = '$id'" );
	
	if( $id != $row['id'] ) die( "error" );

	$row['date'] = strtotime( $row['date'] );	
	$have_perm = 0;
	
	if( $is_logged and (($member_id['name'] == $row['autor'] and $row['is_register'] and $user_group[$member_id['user_group']]['allow_editc']) or $user_group[$member_id['user_group']]['edit_allc']) ) {
		$have_perm = 1;
	}

	if ( $user_group[$member_id['user_group']]['edit_limit'] AND (($row['date'] + ($user_group[$member_id['user_group']]['edit_limit'] * 60)) < $_TIME) ) {
		$have_perm = 0;
	}
	
	if( ! $have_perm ) die( "error" );
	
	if( $config['allow_comments_wysiwyg'] < 1 ) {
		
		include_once ENGINE_DIR . '/ajax/bbcode.php';
		
		$comm_txt = $parse->decodeBBCodes( $row['text'], false );
		
		if ($config['allow_comments_wysiwyg'] == 0 ) $params = "onfocus=\"setNewField(this.name, document.getElementById( 'dlemasscomments' ) )\"";
		else $params = "";

	} else {
		
		$comm_txt = $parse->decodeBBCodes( $row['text'], true, $config['allow_comments_wysiwyg'] );
		$params = "class=\"ajaxwysiwygeditor\"";

		if ($config['allow_comments_wysiwyg'] == "1") {	

			if( $user_group[$member_id['user_group']]['allow_url'] ) $link_icon = "\"LinkDialog\", \"DLELeech\","; else $link_icon = "";
			if( $user_group[$member_id['user_group']]['allow_image'] ) $link_icon .= "\"ImageDialog\",";
		
		$bb_code = <<<HTML

<script type="text/javascript">
function show_editor( root ) {
	var use_br = false;
	var use_div = true;
	
	oUtil.initializeEditor("ajaxwysiwygeditor",  {
		width: "100%", 
		height: "250", 
		css: root + "engine/editor/scripts/style/default.css",
		useBR: use_br,
		useDIV: use_div,
		groups:[
			["grpEdit1", "", ["Bold", "Italic", "Underline", "Strikethrough", "ForeColor"]],
			["grpEdit2", "", ["JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyFull", "Bullets", "Numbering"]],
			["grpEdit3", "", [{$link_icon}"DLESmiles", "DLEQuote", "DLEHide"]]
	    ],
		arrCustomButtons:[
			["DLESmiles", "modalDialog('"+ root +"engine/editor/emotions.php',350,290)", "{$lang['bb_t_emo']}", "btnEmoticons.gif"],
			["DLEQuote", "DLEcustomTag('[quote]', '[/quote]')", "{$lang['bb_t_quote']}", "dle_quote.gif"],
			["DLEHide", "DLEcustomTag('[hide]', '[/hide]')", "{$lang['bb_t_hide']}", "dle_hide.gif"],
			["DLELeech", "DLEcustomTag('[leech=http://]', '[/leech]')", "{$lang['bb_t_leech']}", "dle_leech.gif"]
		]
		}
	);	
};
setTimeout(function() {
	$( "#innovaeditor0" ).remove();
	show_editor(dle_root);
}, 100);
</script>
HTML;

		} else {

			if( $user_group[$member_id['user_group']]['allow_url'] ) $link_icon = "link,dle_leech,separator,"; else $link_icon = "";
			if( $user_group[$member_id['user_group']]['allow_image'] ) $link_icon .= "image,";

		$bb_code = <<<HTML

<script type="text/javascript">

setTimeout(function() {

	tinymce.remove('textarea.ajaxwysiwygeditor');

	tinymce.init({
		selector: 'textarea.ajaxwysiwygeditor',
		language : "{$lang['wysiwyg_language']}",
		width : "100%",
		height : 220,
		plugins: ["link image paste dlebutton"],
		theme: "modern",
		relative_urls : false,
		convert_urls : false,
		remove_script_host : false,
		extended_valid_elements : "div[align|class|style|id|title]",
		paste_as_text: true,
		toolbar_items_size: 'small',
		statusbar : false,
		menubar: false,
		toolbar1: "bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | {$link_icon}dleemo | bullist numlist | dlequote dlehide",
		dle_root : "{$config['http_home_url']}",
		content_css : "{$config['http_home_url']}engine/editor/css/content.css"

	});

}, 100);

</script>
HTML;


		}
	}
	
	$buffer = <<<HTML
<div class="bb-editor ignore-select">
{$bb_code}
<textarea name="dleeditcomments{$id}" id="dleeditcomments{$id}" rows="10" cols="50" {$params}>{$comm_txt}</textarea><br>
<div align="right" style="width:99%;padding-top:5px;"><input class="bbcodes" title="$lang[bb_t_apply]" type="button" onclick="ajax_save_comm_edit('{$id}', '{$area}'); return false;" value="$lang[bb_b_apply]">
<input class="bbcodes" title="$lang[bb_t_cancel]" type="button" onclick="ajax_cancel_comm_edit('{$id}'); return false;" value="$lang[bb_b_cancel]">
</div></div>
HTML;

} elseif( $_REQUEST['action'] == "save" ) {
	$row = $db->super_query( "SELECT id, post_id, date, autor, text, is_register, approve FROM " . PREFIX . "_{$allowed_areas[$area]['comments_table']} WHERE id = '$id'" );
	
	if( $id != $row['id'] ) die( "error" );
	
	$have_perm = 0;
	$row['date'] = strtotime( $row['date'] );
	
	if( $is_logged AND (($member_id['name'] == $row['autor'] AND $row['is_register'] AND $user_group[$member_id['user_group']]['allow_editc']) OR $user_group[$member_id['user_group']]['edit_allc'] OR $user_group[$member_id['user_group']]['admin_comments']) ) {
		$have_perm = 1;
	}

	if ( $user_group[$member_id['user_group']]['edit_limit'] AND (($row['date'] + ($user_group[$member_id['user_group']]['edit_limit'] * 60)) < $_TIME) ) {
		$have_perm = 0;
	}	

	if( ! $have_perm ) die( "error" );
	
	if( $config['allow_comments_wysiwyg'] > 0) {
		
		$parse->wysiwyg = true;
		$use_html = true;
		
		if( $user_group[$member_id['user_group']]['allow_url'] ) $parse->tagsArray[] = 'a';
		if( $user_group[$member_id['user_group']]['allow_image'] ) $parse->tagsArray[] = 'img';
	
	} else {
		
		if ($config['allow_comments_wysiwyg'] == "-1") $parse->allowbbcodes = false;
		
		$use_html = false;
	}
	
	$comm_txt = trim( $parse->BB_Parse( $parse->process( convert_unicode( $_POST['comm_txt'], $config['charset'] ) ), $use_html ) );
	
	if( $parse->not_allowed_tags ) {
		die( "error" );
	}

	if( $parse->not_allowed_text ) {
		die( "error" );
	}
	
	if( dle_strlen( $comm_txt, $config['charset'] ) > $config['comments_maxlen'] ) {
		
		die( "error" );
	
	}
	
	if( $comm_txt == "" ) {
		
		die( "error" );
	
	}

	if( intval($config['comments_minlen']) AND dle_strlen( $comm_txt, $config['charset'] ) < $config['comments_minlen'] ) {
	
		die( "error" );
	
	}

	//* Автоперенос длинных слов
	if( intval( $config['auto_wrap'] ) ) {
		
		if ( $config['charset'] == "utf-8" ) $utf_pref = "u"; else $utf_pref = "";
		
		$comm_txt = preg_split( '((>)|(<))', $comm_txt, - 1, PREG_SPLIT_DELIM_CAPTURE );
		$n = count( $comm_txt );
		
		for($i = 0; $i < $n; $i ++) {
			if( $comm_txt[$i] == "<" ) {
				$i ++;
				continue;
			}
			
			if( preg_match( "#([^\s\n\r]{" . intval( $config['auto_wrap'] ) . "})#{$utf_pref}i", $comm_txt[$i] ) ) {

				$comm_txt[$i] = preg_replace( "#([^\s\n\r]{" . intval( $config['auto_wrap']-1 ) . "})#{$utf_pref}i", "\\1<br />", $comm_txt[$i] );

			}

		}
		
		$comm_txt = join( "", $comm_txt );
	
	}
	
	$comm_update = $db->safesql( $comm_txt );
	
	$db->query( "UPDATE " . PREFIX . "_{$allowed_areas[$area]['comments_table']} SET text='$comm_update', approve='1' WHERE id = '$id'" );
	
	if( !$row['approve'] ) $db->query( "UPDATE " . PREFIX . "_post SET comm_num=comm_num+1 WHERE id='{$row['post_id']}'" );
	
	$comm_txt = str_replace( "[hide]", "", str_replace( "[/hide]", "", $comm_txt) );
	$buffer = stripslashes( $comm_txt );

	$buffer= str_replace( '{THEME}', $config['http_home_url'] . 'templates/' . $config['skin'], $buffer );

	if( !$row['approve'] ) {
		if ( $config['allow_alt_url'] AND !$config['seo_type'] ) clear_cache( 'full_' ); else clear_cache( 'full_'.$row['post_id'] );
	}

	clear_cache( 'comm_'.$row['post_id'] );

	if ( $config['allow_subscribe'] AND !$row['approve'] ) {
		
		$name = $row['autor'];
		$post_id = $row['post_id'];

		$cat_info = get_vars( "category" );
		
		if( ! is_array( $cat_info ) ) {
			$cat_info = array ();
			
			$db->query( "SELECT * FROM " . PREFIX . "_category ORDER BY posi ASC" );
			while ( $row = $db->get_row() ) {
				
				$cat_info[$row['id']] = array ();
				
				foreach ( $row as $key => $value ) {
					$cat_info[$row['id']][$key] = stripslashes( $value );
				}
			
			}
			set_vars( "category", $cat_info );
			$db->free();
		}
		
		include_once ENGINE_DIR . '/classes/mail.class.php';

		$row = $db->super_query( "SELECT id, short_story, title, date, alt_name, category FROM ".PREFIX."_post WHERE id = '{$post_id}'" );

		$row['date'] = strtotime( $row['date'] );
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
	
		$title = stripslashes($row['title']);
		
		$row = $db->super_query( "SELECT * FROM " . PREFIX . "_email WHERE name='comments' LIMIT 0,1" );
		$mail = new dle_mail( $config, $row['use_html'] );

		if (strpos($full_link, "//") === 0) $full_link = "http:".$full_link;
		elseif (strpos($full_link, "/") === 0) $full_link = "http://".$_SERVER['HTTP_HOST'].$full_link;

		$row['template'] = stripslashes( $row['template'] );
		$row['template'] = str_replace( "{%username%}", $name, $row['template'] );
		$row['template'] = str_replace( "{%date%}", langdate( "j F Y H:i", $_TIME, true ), $row['template'] );
		$row['template'] = str_replace( "{%link%}", $full_link, $row['template'] );
		$row['template'] = str_replace( "{%title%}", $title, $row['template'] );

		$body = str_replace( '\n', "", $comm_update );
		$body = str_replace( '\r', "", $body );
			
		$body = stripslashes( stripslashes( $body ) );
		$body = str_replace( "<br />", "\n", $body );
		$body = strip_tags( $body );
			
		if( $row['use_html'] ) {
			$body = str_replace("\n", "<br />", $body );
		}
					
		$row['template'] = str_replace( "{%text%}", $body, $row['template'] );
		$row['template'] = str_replace( "{%ip%}", "--", $row['template'] );

		$db->query( "SELECT user_id, name, email, hash FROM " . PREFIX . "_subscribe WHERE news_id='{$post_id}'" );

		while($rec = $db->get_row())
		{
			if ($rec['user_id'] != $member_id['user_id'] ) {

				if (strpos($config['http_home_url'], "//") === 0) $slink = "http:".$config['http_home_url'];
				elseif (strpos($config['http_home_url'], "/") === 0) $slink = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];
				else $slink = $config['http_home_url'];
		
				$body = str_replace( "{%username_to%}", $rec['name'], $row['template'] );
				$body = str_replace( "{%unsubscribe%}", $slink . "index.php?do=unsubscribe&post_id=" . $post_id . "&user_id=" . $rec['user_id'] . "&hash=" . $rec['hash'], $body );
				$mail->send( $rec['email'], $lang['mail_comments'], $body );

			}

		}

		$db->free();
	}
	
} else
	die( "error" );

$db->close();

@header( "Content-type: text/html; charset=" . $config['charset'] );
echo $buffer;
?>