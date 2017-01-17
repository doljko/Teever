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
 Файл: replycomments.php
-----------------------------------------------------
 Назначение: Ответ на комментарий
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
	
	$config['http_home_url'] = explode( "engine/ajax/replycomments.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset( $config['http_home_url'] );
	$config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';


dle_session();

$_REQUEST['skin'] = trim(totranslit($_REQUEST['skin'], false, false));

if( $_REQUEST['skin'] == "" OR !@is_dir( ROOT_DIR . '/templates/' . $_REQUEST['skin'] ) ) {
	die( "Hacking attempt!" );
}

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

if( $config["lang_" . $_REQUEST['skin']] ) {

	if ( file_exists( ROOT_DIR . '/language/' . $config["lang_" . $_REQUEST['skin']] . '/website.lng' ) ) {
		@include_once (ROOT_DIR . '/language/' . $config["lang_" . $_REQUEST['skin']] . '/website.lng');
	} else die("Language file not found");

} else {
	
	@include_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';

}
$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];
$is_logged = false;
$member_id = array ();

if ($config['allow_registration']) {
	require_once ENGINE_DIR . '/modules/sitelogin.php';
}

if( ! $is_logged ) {
	$member_id['user_group'] = 5;
}

@header( "Content-type: text/html; charset=" . $config['charset'] );

if( !$user_group[$member_id['user_group']]['allow_addc'] OR !$config['allow_comments'] OR !$config['tree_comments']) {
	echo $lang['reply_error_1'];
	die();
}

$id = intval($_GET['id']);
$indent = intval($_GET['indent']);

if( $id < 1 ) {
	echo $lang['reply_error_2'];
	die();
}

$row = $db->super_query("SELECT id, post_id, autor FROM " . PREFIX . "_comments WHERE id = '{$id}'");

if (!$row['id']) {
	echo $lang['reply_error_2'];
	die();
}

if ( $is_logged AND $user_group[$member_id['user_group']]['disable_comments_captcha'] AND $member_id['comm_num'] >= $user_group[$member_id['user_group']]['disable_comments_captcha'] ) {
		
		$user_group[$member_id['user_group']]['comments_question'] = false;
		$user_group[$member_id['user_group']]['captcha'] = false;
		
}


echo $lang['reply_descr']." <b>".$row['autor']."</b><br />";

echo "<form  method=\"post\" name=\"dle-comments-form-{$id}\" id=\"dle-comments-form-{$id}\">";

if( $is_logged ) echo "<input type=\"hidden\" name=\"name{$id}\" id=\"name{$id}\" value=\"{$member_id['name']}\" /><input type=\"hidden\" name=\"mail{$id}\" id=\"mail{$id}\" value=\"\" />";
else {

	if ( $config['simple_reply'] ) {
		echo <<<HTML
<div style="padding-bottom:5px;">{$lang['reply_name']}&nbsp;&nbsp;<input type="text" name="name{$id}" id="name{$id}" class="commentsreplyname" /></div>
HTML;

	} else {
		
		echo <<<HTML
<div style="padding-bottom:5px;">{$lang['reply_name']}<br /><input type="text" name="name{$id}" id="name{$id}" class="ui-widget-content ui-corner-all" style="width:350px;padding: .4em;" /></div>
<div style="padding-bottom:5px;">{$lang['reply_mail']}<br /><input type="text" name="mail{$id}" id="mail{$id}" class="ui-widget-content ui-corner-all" style="width:350px;padding: .4em;" /></div>
HTML;

	}
}


	if( $config['allow_comments_wysiwyg'] < 1 OR $config['simple_reply'] ) {
		
		if ( !$config['simple_reply'] ) {
			
			include_once ENGINE_DIR . '/ajax/bbcode.php';
			
			if ( $config['allow_comments_wysiwyg'] == 0 ) $params = "onfocus=\"setNewField(this.name, document.getElementById( 'dle-comments-form-{$id}' ) )\"";
			else $params = "";
		
		} else $params = "";


	} else {
		
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
	show_editor(dle_root);
	$('#dlereplypopup{$id}').dialog( "option", "position", ['0','0'] );
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
		width : "99%",
		height : 180,
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

	$('#dlereplypopup{$id}').dialog( "option", "position", ['0','0'] );
}, 100);

</script>
HTML;


		}
	}

echo <<<HTML
<div class="bb-editor">
{$bb_code}
<textarea name="comments{$id}" id="comments{$id}" rows="10" cols="50" {$params}></textarea>
</div>
HTML;

if ($config['allow_subscribe'] AND $user_group[$member_id['user_group']]['allow_subscribe']) {
echo <<<HTML
<div style="padding-top:5px;">
	<input type="checkbox" name="subscribe{$id}" id="subscribe{$id}" value="1"><label for="subscribe{$id}">&nbsp;&nbsp;{$lang['c_subscribe']}</label>
</div>
HTML;
}

if( $user_group[$member_id['user_group']]['comments_question'] ) {
	$question = $db->super_query("SELECT id, question FROM " . PREFIX . "_question ORDER BY RAND() LIMIT 1");
	$question = htmlspecialchars( stripslashes( $question['question'] ), ENT_QUOTES, $config['charset'] );
	
	echo <<<HTML
<div id="dle-question{$id}" style="padding-top:5px;">{$question}</div>
<div><input type="text" name="question_answer{$id}" id="question_answer{$id}" placeholder="{$lang['question_hint']}" class="ui-widget-content ui-corner-all" style="width:70%;padding: .4em;" /></div>
HTML;

}

if( $user_group[$member_id['user_group']]['captcha'] ) {

	if ( $config['allow_recaptcha'] ) {
		
		echo <<<HTML
<div id="dle_recaptcha{$id}" style="padding-top:5px;"></div><input type="hidden" name="recaptcha{$id}" id="recaptcha{$id}" value="1" />
<script type="text/javascript">
<!--
var recaptcha_widget = grecaptcha.render('dle_recaptcha{$id}', {'sitekey' : '{$config['recaptcha_public_key']}', 'theme':'{$config['recaptcha_theme']}'});
//-->
</script>
HTML;

	} else {

		echo <<<HTML
<div style="padding-top:5px;"><a onclick="reload{$id}(); return false;" title="{$lang['reload_code']}" href="#"><span id="dle-captcha{$id}"><img src="{$config['http_home_url']}engine/modules/antibot/antibot.php" alt="{$lang['reload_code']}" width="160" height="80" /></span></a></div>
<div><input class="ui-widget-content ui-corner-all sec-code" style="width:149px;padding: .4em;" type="text" name="sec_code{$id}" id="sec_code{$id}" placeholder="{$lang['captcha_hint']}"></div>
<script type="text/javascript">
<!--
function reload{$id} () {

	var rndval = new Date().getTime(); 

	document.getElementById('dle-captcha{$id}').innerHTML = '<img src="{$config['http_home_url']}engine/modules/antibot/antibot.php?rndval=' + rndval + '" width="160" height="80" alt="" />';
	document.getElementById('sec_code{$id}').value = '';
};
//-->
</script>
HTML;

	}
}
	
echo "<input type=\"hidden\" name=\"postid{$id}\" id=\"postid{$id}\" value=\"{$row['post_id']}\" /></form>";

if( $config['simple_reply'] ) {

	echo  <<<HTML
<div align="right"><input class="bbcodes" title="{$lang['reply_comments']}" type="button" onclick="ajax_fast_reply('{$id}', '{$indent}'); return false;" value="{$lang['reply_comments_1']}">
<input class="bbcodes" title="$lang[bb_t_cancel]" type="button" onclick="ajax_cancel_reply(); return false;" value="{$lang['bb_b_cancel']}">
</div>
HTML;

	
}

?>