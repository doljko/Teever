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
 Файл: shortsite.php
-----------------------------------------------------
 Назначение: WYSIWYG для новостей с сайта
=====================================================
*/

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

$p_name = urlencode($member_id['name']);

if( $config['allow_site_wysiwyg'] == "1" ) {

if ( $user_group[$member_id['user_group']]['allow_image_upload'] OR $user_group[$member_id['user_group']]['allow_file_upload'] ) $image_upload = "\"DLEUpload\", "; else $image_upload = "";

	$onload_scripts[] = <<<HTML
	create_editor('');

	setTimeout(function() {
		
	    for(var i = 0;i < oUtil.arrEditor.length;i++) {
	      var oEditor = eval("idContent" + oUtil.arrEditor[i]);
	      var sHTML;
	      if(navigator.appName.indexOf("Microsoft") != -1) {
	        sHTML = oEditor.document.documentElement.outerHTML
	      }else {
	        sHTML = getOuterHTML(oEditor.document.documentElement)
	      }
	      sHTML = sHTML.replace(/FONT-FAMILY/g, "font-family");
	      var urlRegex = /font-family?:.+?(\;|,|")/g;
	      var matches = sHTML.match(urlRegex);
	      if(matches) {
	        for(var j = 0, len = matches.length;j < len;j++) {
	          var sFont = matches[j].replace(/font-family?:/g, "").replace(/;/g, "").replace(/,/g, "").replace(/"/g, "");
			  sFont=sFont.split("'").join('');
	          sFont = jQuery.trim(sFont);
	          var sFontLower = sFont.toLowerCase();
	          if(sFontLower != "serif" && sFontLower != "arial" && sFontLower != "arial black" && sFontLower != "bookman old style" && sFontLower != "comic sans ms" && sFontLower != "courier" && sFontLower != "courier new" && sFontLower != "garamond" && sFontLower != "georgia" && sFontLower != "impact" && sFontLower != "lucida console" && sFontLower != "lucida sans unicode" && sFontLower != "ms sans serif" && sFontLower != "ms serif" && sFontLower != "palatino linotype" && sFontLower != "tahoma" && sFontLower != 
	          "times new roman" && sFontLower != "trebuchet ms" && sFontLower != "verdana") {
	            sURL = "//fonts.googleapis.com/css?family=" + sFont + "&subset=latin,cyrillic";
	            var objL = oEditor.document.createElement("LINK");
	            objL.href = sURL;
	            objL.rel = "StyleSheet";
	            oEditor.document.documentElement.childNodes[0].appendChild(objL)
	          }
	        }
	      }
	    }
	}, 100);

function create_editor( root ) {

	var use_br = false;
	var use_div = true;
	
	oUtil.initializeEditor("wysiwygeditor",  {
		width: "100%", 
		height: "400", 
		css: root + "engine/editor/scripts/style/default.css",
		useBR: use_br,
		useDIV: use_div,
		groups:[
			["grpEdit1", "", ["Paragraph", "TextDialog", "FontDialog", "Subscript", "Superscript", "ForeColor", "BackColor", "BRK", "Bold", "Italic", "Underline", "Strikethrough", "DLEPasteText", "Styles", "RemoveFormat"]],
			["grpEdit2", "", ["JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyFull", "BRK", "Bullets", "Numbering", "Indent", "Outdent"]],
			["grpEdit3", "", ["Table", "TableDialog", "DLESmiles", "CharsDialog", "Line", "BRK", "LinkDialog", "DLELeech", "ImageDialog", {$image_upload}"FlashDialog"]],
			["grpEdit4", "", ["DLEQuote", "DLECode", "DLEHide", "DLESpoiler", "CustomTag", "BRK", "DLEVideo", "DLEAudio", "DLEMedia", "DLETypograf", "Paragraph"]],
			["grpEdit5", "", ["SearchDialog", "SourceDialog", "BRK", "Undo", "Redo"]]
	    ],
		arrCustomButtons:[
			["DLEUpload", "media_upload('short_story', '{$p_name}', '{$id}', '1')", "{$lang['bb_t_up']}", "dle_upload.gif"],
			["DLESmiles", "modalDialog('"+ root +"engine/editor/emotions.php',350,290)", "{$lang['bb_t_emo']}", "btnEmoticons.gif"],
			["DLEPasteText", "modalDialog('"+ root +"engine/editor/scripts/common/webpastetext.htm',450,380)", "{$lang['paste_text']}", "btnPaste.gif"],
			["DLETypograf", "tag_typograf()", "{$lang['bb_t_t']}", "dle_tt.gif"],
			["DLEQuote", "DLEcustomTag('[quote]', '[/quote]')", "{$lang['bb_t_quote']}", "dle_quote.gif"],
			["DLECode", "DLEcustomTag('[code]', '[/code]')", "{$lang['bb_t_code']}", "dle_code.gif"],
			["DLEHide", "DLEcustomTag('[hide]', '[/hide]')", "{$lang['bb_t_hide']}", "dle_hide.gif"],
			["DLESpoiler", "DLEcustomTag('[spoiler]', '[/spoiler]')", "{$lang['bb_t_spoiler']}", "dle_spoiler.gif"],
			["DLELeech", "DLEcustomTag('[leech=http://]', '[/leech]')", "{$lang['bb_t_leech']}", "dle_leech.gif"],
			["DLEVideo", "modalDialog('"+ root +"engine/editor/scripts/common/webbbvideo.htm',400,250)", "{$lang['bb_t_video']} (BB Codes)", "dle_video.gif"],
			["DLEAudio", "modalDialog('"+ root +"engine/editor/scripts/common/webbbaudio.htm',400,200)", "{$lang['bb_t_audio']} (BB Codes)", "dle_mp3.gif"],
			["DLEMedia", "modalDialog('"+ root +"engine/editor/scripts/common/webbbmedia.htm',400,250)", "{$lang['bb_t_yvideo']} (BB Codes)", "dle_media.gif"]
		],
		arrCustomTag: [
			["{$lang['bb_t_br']}", "{PAGEBREAK}"],
	        ["{$lang['bb_t_p']}", "[page=1][/page]"]
		]
		}
	);	
};

function tag_typograf() {

	ShowLoading('');

	var oEditor = oUtil.oEditor;
	var obj = oUtil.obj;

	obj.saveForUndo();
    oEditor.focus();
    obj.setFocus();

	var txt = obj.getXHTMLBody();

	$.post("engine/ajax/typograf.php", {txt: txt}, function(data){
	
		HideLoading('');
	
		obj.loadHTML(data); 
	
	});
};
HTML;

$shortarea = <<<HTML
<script type="text/javascript" src="engine/editor/scripts/language/{$lang['wysiwyg_language']}/editor_lang.js"></script>
<script type="text/javascript" src="engine/editor/scripts/innovaeditor.js"></script>
<script type="text/javascript">
	var text_upload = "$lang[bb_t_up]";
</script>
<style type="text/css">
.wseditor table td { 
	padding:0px;
	border:0;
}
</style>
<div class="wseditor"><textarea id="short_story" name="short_story" class="wysiwygeditor" style="width:98%;height:200px;">{short-story}</textarea></div>
HTML;

} else {


	if ( $user_group[$member_id['user_group']]['allow_image_upload'] OR $user_group[$member_id['user_group']]['allow_file_upload'] ) $image_upload = "dleupload "; else $image_upload = "";

	$onload_scripts[] = <<<HTML
	tinymce.init({
		selector: 'textarea.wysiwygeditor',
		language : "{$lang['wysiwyg_language']}",
		width : "100%",
		height : "350",
		theme: "modern",
		plugins: ["advlist autolink lists link image charmap anchor searchreplace visualblocks visualchars fullscreen media nonbreaking table contextmenu emoticons paste textcolor colorpicker codemirror spellchecker dlebutton codesample"],
		relative_urls : false,
		convert_urls : false,
		remove_script_host : false,
		extended_valid_elements : "noindex,div[align|class|style|id|title]",
		custom_elements : 'noindex',
		toolbar_items_size: 'small',
		menubar: false,
		toolbar1: "fontselect fontsizeselect | table | link anchor dleleech unlink | {$image_upload}image dleemo dlemp dletube dlaudio | dlequote dlespoiler codesample dlebreak dlepage code",
		toolbar2: "undo redo | copy paste pastetext | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | subscript superscript | bullist numlist forecolor backcolor spellchecker removeformat",

		spellchecker_language : "ru",
		spellchecker_languages : "Russian=ru,Ukrainian=uk,English=en",
		spellchecker_rpc_url : "//speller.yandex.net/services/tinyspell",
		image_caption: true,

		dle_root : "{$config['http_home_url']}",
		dle_upload_area : "short_story",
		dle_upload_user : "{$p_name}",
		dle_upload_news : "{$id}",

		content_css : "{$config['http_home_url']}engine/editor/css/content.css"

	});
HTML;

$shortarea = <<<HTML
<script type="text/javascript" src="engine/editor/jscripts/tiny_mce/tinymce.min.js"></script>
<script type="text/javascript">
	var text_upload = "$lang[bb_t_up]";
</script>
    <textarea id="short_story" name="short_story" class="wysiwygeditor" style="width:98%;height:200px;">{short-story}</textarea>
HTML;


}

?>