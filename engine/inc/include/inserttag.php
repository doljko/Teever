<?PHP
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
 Файл: inserttag.php
-----------------------------------------------------
 Назначение: bbcodes
=====================================================
*/

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

$i = 0;
$smiles = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\"><tr>";

$smilies = explode(",", $config['smilies']);
foreach($smilies as $smile) {

	$i++;
	$smile = trim($smile);

	if( file_exists( ROOT_DIR . "/engine/data/emoticons/" . $smile . ".png" ) ) {
		if( file_exists( ROOT_DIR . "/engine/data/emoticons/" . $smile . "@2x.png" ) ) {
			$sm_image = "<img alt=\"{$smile}\" class=\"emoji\" src=\"{$config['http_home_url']}engine/data/emoticons/{$smile}.png\" srcset=\"{$config['http_home_url']}engine/data/emoticons/{$smile}@2x.png 2x\" />";
		} else {
			$sm_image = "<img alt=\"{$smile}\" class=\"emoji\" src=\"{$config['http_home_url']}engine/data/emoticons/{$smile}.png\" />";	
		}
	} elseif ( file_exists( ROOT_DIR . "/engine/data/emoticons/" . $smile . ".gif" ) ) {
		if( file_exists( ROOT_DIR . "/engine/data/emoticons/" . $smile . "@2x.gif" ) ) {
			$sm_image = "<img alt=\"{$smile}\" class=\"emoji\" src=\"{$config['http_home_url']}engine/data/emoticons/{$smile}.gif\" srcset=\"{$config['http_home_url']}engine/data/emoticons/{$smile}@2x.gif 2x\" />";
		} else {
			$sm_image = "<img alt=\"{$smile}\" class=\"emoji\" src=\"{$config['http_home_url']}engine/data/emoticons/{$smile}.gif\" />";	
		}
	}

	$smiles .= "<td style=\"padding:5px;\" align=\"center\"><a href=\"#\" onclick=\"dle_smiley(':$smile:'); return false;\">{$sm_image}</a></td>";

	if ($i%7 == 0) $smiles .= "</tr><tr>";

}

$smiles .= "</tr></table>";

if ($user_group[$member_id['user_group']]['allow_image_upload'] OR $user_group[$member_id['user_group']]['allow_file_upload'] ) {

      $image_upload = "<button type=\"button\" rel=\"tooltip\" class=\"btn btn-default btn-sm btn-small\" title=\"{$lang['bb_t_up']}\" onclick=\"image_upload(); return false;\"><span class=\"editoricon-folder-open\"></span></button>";

} else $image_upload = "";

if ($mod != "editnews") {
	$row['autor'] = $member_id['name'];
}

$p_name = urlencode($row['autor']);

$image_align = array ();
$image_align[$config['image_align']] = "selected";

$bb_js = <<<HTML
<SCRIPT type=text/javascript>
<!--

var uagent    = navigator.userAgent.toLowerCase();
var is_safari = ( (uagent.indexOf('safari') != -1) || (navigator.vendor == "Apple Computer, Inc.") );
var is_opera  = (uagent.indexOf('opera') != -1);
var is_ie     = ( (uagent.indexOf('msie') != -1) && (!is_opera) && (!is_safari) );
var is_ie4    = ( (is_ie) && (uagent.indexOf("msie 4.") != -1) );

var is_win    =  ( (uagent.indexOf("win") != -1) || (uagent.indexOf("16bit") !=- 1) );
var ua_vers   = parseInt(navigator.appVersion);
	
var text_enter_url       = "$lang[bb_url]";
var text_enter_size       = "$lang[bb_flash]";
var text_enter_flash       = "$lang[bb_flash_url]";
var text_enter_page      = "$lang[bb_page]";
var text_enter_url_name  = "$lang[bb_url_name]";
var  text_enter_tooltip  = "$lang[bb_url_tooltip]";
var text_enter_page_name = "$lang[bb_page_name]";
var text_enter_image    = "$lang[bb_image]";
var text_enter_email    = "$lang[bb_email]";
var text_enter_list     = "$lang[bb_list_item]";
var text_code           = "$lang[bb_code]";
var text_quote          = "$lang[bb_quote]";
var text_alt_image      = "$lang[bb_alt_image]";
var error_no_url        = "$lang[bb_no_url]";
var error_no_title      = "$lang[bb_no_title]";
var error_no_email      = "$lang[bb_no_email]";
var prompt_start        = "$lang[bb_prompt_start]";
var img_title   		= "$lang[bb_img_title]";
var img_align  	        = "{$lang['images_align']}";
var img_align_sel  	    = "<select name='dleimagealign' id='dleimagealign' class='ui-widget-content ui-corner-all'><option value='' {$image_align[0]}>{$lang['opt_sys_no']}</option><option value='left' {$image_align['left']}>{$lang['images_left']}</option><option value='right' {$image_align['right']}>{$lang['images_right']}</option><option value='center' {$image_align['center']}>{$lang['images_center']}</option></select>";
var email_title  	    = "$lang[bb_email_title]";
var dle_prompt          = "$lang[p_prompt]";
var bb_t_emo  	        = "{$lang['bb_t_emo']}";
var bb_t_col  	        = "{$lang['bb_t_col']}";

var ie_range_cache = '';
var list_open_tag = '';
var list_close_tag = '';
var listitems = '';

var selField  = "short_story";

var bbtags   = new Array();

var fombj    = document.forms[0];

function setFieldName(which)
{

   if (which != selField)
   {
       selField = which;

   }
}

function emoticon(theSmilie)
{
	doInsert(" " + theSmilie + " ", "", false);
}

function pagebreak()
{
	doInsert("{PAGEBREAK}", "", false);
}

function simpletag(thetag)
{
	doInsert("[" + thetag + "]", "[/" + thetag + "]", true);
}

function pagelink()
{
	var thesel = get_sel(eval('fombj.'+ selField))

    if (!thesel) {
        thesel = '$lang[bb_bb_page]';
    }

	DLEprompt(text_enter_page, "1", dle_prompt, function (r) {

		var enterURL = r;

		DLEprompt(text_enter_page_name, thesel, dle_prompt, function (r) {

			doInsert("[page="+enterURL+"]"+r+"[/page]", "", false);
			ie_range_cache = null;
	
		});

	});
}

function DLEurlPrompt( d, callback ){

	var b = {};
    var urlvalue = '';
    var urltitle = '';

	if( d.indexOf("http://") != -1 || d.indexOf("https://") != -1 || d.indexOf("ftp://") != -1) {
		urlvalue = d;
		urltitle = d;
	} else {
		urlvalue = 'http://';
		urltitle = d;	
	}

	b[dle_act_lang[3]] = function() { 
					$(this).dialog("close");						
			    };

	b[dle_act_lang[2]] = function() { 
					if ( $("#dle-promt-url").val().length < 1) {
						 $("#dle-promt-url").addClass('ui-state-error');
					} else if ($("#dle-promt-title").val().length < 1) {
						 $("#dle-promt-title").addClass('ui-state-error');
					} else {
						var dleurl = $("#dle-promt-url").val();
						var dleurltitle = $("#dle-promt-title").val();
						var dleurltooltip = $("#dle-promt-tooltip").val();
						$(this).dialog("close");
						$("#dlepopup").remove();
						if( callback ) callback( dleurl, dleurltitle, dleurltooltip);	
					}				
				};

	$("#dlepopup").remove();

	$("body").append("<div id='dlepopup' title='" + dle_prompt + "' style='display:none'>"+ text_enter_url +"<br /><input type='text' name='dle-promt-url' id='dle-promt-url' class='ui-widget-content ui-corner-all' style='width:97%;' value='" + urlvalue + "'/><br /><br />"+ text_enter_url_name +"<br /><input type='text' name='dle-promt-title' id='dle-promt-title' class='ui-widget-content ui-corner-all' style='width:97%;' value='" + urltitle + "'/><br /><br />"+ text_enter_tooltip +"<br /><input type='text' name='dle-promt-tooltip' id='dle-promt-tooltip' class='ui-widget-content ui-corner-all' style='width:97%;' value=''/></div>");

	$('#dlepopup').dialog({
		autoOpen: true,
		width: 500,
		resizable: false,
		buttons: b
	});


	$("#dle-promt-url").select().focus();

};

function tag_url()
{
	var thesel = get_sel(eval('fombj.'+ selField))

    if (!thesel) {
        thesel ='My Webpage';
    }

	DLEurlPrompt(thesel, function (dleurl, dleurltitle, dleurltooltip) {

		if( dleurltooltip.length > 0 ) {
			dleurl = dleurl + '|' + dleurltooltip;
		}
	
		doInsert("[url="+dleurl+"]"+dleurltitle+"[/url]", "", false);

		ie_range_cache = null;

	});
}


function tag_leech()
{
	var thesel = get_sel(eval('fombj.'+ selField))

    if (!thesel) {
        thesel ='My Webpage';
    }

	DLEurlPrompt(thesel, function (dleurl, dleurltitle, dleurltooltip) {
	
		if( dleurltooltip.length > 0 ) {
			dleurl = dleurl + '|' + dleurltooltip;
		}
		
		doInsert("[leech="+dleurl+"]"+dleurltitle+"[/leech]", "", false);

		ie_range_cache = null;

	});
}

function tag_video()
{
	var thesel = get_sel(eval('fombj.'+ selField))

    if (!thesel) {
        thesel ='http://';
    }

	DLEprompt(text_enter_url, thesel, dle_prompt, function (r) {

		doInsert("[video="+r+"]", "", false);
		ie_range_cache = null;
	
	});
}

function tag_audio()
{
	var thesel = get_sel(eval('fombj.'+ selField))

    if (!thesel) {
        thesel ='http://';
    }

	DLEprompt(text_enter_url, thesel, dle_prompt, function (r) {

		doInsert("[audio="+r+"]", "", false);
		ie_range_cache = null;
	
	});
}

function tag_youtube()
{
	var thesel = get_sel(eval('fombj.'+ selField))

    if (!thesel) {
        thesel ='http://';
    }

	DLEprompt(text_enter_url, thesel, dle_prompt, function (r) {

		doInsert("[media="+r+"]", "", false);
		ie_range_cache = null;
	
	});
}

function tag_flash()
{
	var thesel = get_sel(eval('fombj.'+ selField))

    if (!thesel) {
        thesel ='http://';
    }

	DLEprompt(text_enter_flash, thesel, dle_prompt, function (r) {

		var enterURL = r;

		DLEprompt(text_enter_size, "425,264", dle_prompt, function (r) {

			doInsert("[flash="+r+"]"+enterURL+"[/flash]", "", false);
			ie_range_cache = null;
	
		});

	});

}

function tag_list(type)
{

	list_open_tag = type == 'ol' ? '[ol=1]\\n' : '[list]\\n';
	list_close_tag = type == 'ol' ? '[/ol]' : '[/list]';
	listitems = '';

	var thesel = get_sel(eval('fombj.'+ selField))

    if (!thesel) {
        thesel ='';
    }

	insert_list( thesel );

}

function insert_list( thesel )
{
	DLEprompt(text_enter_list, thesel, dle_prompt, function (r) {

		if (r != '') {

			listitems += '[*]' + r + '\\n';
			insert_list('');

		} else {

			if( listitems )
			{
				doInsert(list_open_tag + listitems + list_close_tag, "", false);
				ie_range_cache = null;
			}
		}

	}, true);

}

function tag_image()
{

	var thesel = get_sel(eval('fombj.'+ selField));

    if (!thesel) {
        thesel ='http://';
    }

	DLEimagePrompt(thesel, function (imageurl, imagealt, imagealign) {

		var imgoption = "";

		if (imagealt != "") { 

			imgoption = "|"+imagealt;

		}

		if (imagealign != "" && imagealign != "center") { 

			imgoption = imagealign+imgoption;

		}

		if (imgoption != "" ) {

			imgoption = "="+imgoption;

		}

		if (imagealign == "center") {
			doInsert("[center][img"+imgoption+"]"+imageurl+"[/img][/center]", "", false);
		}
		else {
			doInsert("[img"+imgoption+"]"+imageurl+"[/img]", "", false);
		}

		ie_range_cache = null;

	});
};

function DLEimagePrompt( d, callback ){

	var b = {};

	b[dle_act_lang[3]] = function() { 
					$(this).dialog("close");						
			    };

	b[dle_act_lang[2]] = function() { 
					if ( $("#dle-promt-text").val().length < 1) {
						 $("#dle-promt-text").addClass('ui-state-error');
					} else {
						var imageurl = $("#dle-promt-text").val();
						var imagealt = $("#dle-image-alt").val();
						var imagealign = $("#dleimagealign").val();
						$(this).dialog("close");
						$("#dlepopup").remove();
						if( callback ) callback( imageurl, imagealt, imagealign );	
					}				
				};

	$("#dlepopup").remove();

	$("body").append("<div id='dlepopup' title='" + dle_prompt + "' style='display:none'>"+ text_enter_image +"<br /><input type='text' name='dle-promt-text' id='dle-promt-text' class='ui-widget-content ui-corner-all' style='width:97%;' value='" + d + "'/><br /><br />"+ text_alt_image +"<br /><input type='text' name='dle-image-alt' id='dle-image-alt' class='ui-widget-content ui-corner-all' style='width:97%;' value=''/><br /><br />"+img_align+"&nbsp;"+img_align_sel+"</div>");

	$('#dlepopup').dialog({
		autoOpen: true,
		width: 500,
		resizable: false,
		buttons: b
	});

	if (d.length > 0) {
		$("#dle-promt-text").select().focus();
	} else {
		$("#dle-promt-text").focus();
	}
};

function tag_email()
{
	var thesel = get_sel(eval('fombj.'+ selField))
		
	if (!thesel) {
		   thesel ='';
	}

	DLEprompt(text_enter_email, thesel, dle_prompt, function (r) {

		doInsert("[email="+r+"]"+r+"[/email]", "", false);
		ie_range_cache = null;

	});
}

function doInsert(ibTag, ibClsTag, isSingle)
{
	var isClose = false;
	var obj_ta = eval('fombj.'+ selField);

	if ( (ua_vers >= 4) && is_ie && is_win)
	{
		if (obj_ta.isTextEdit)
		{
			obj_ta.focus();
			var sel = document.selection;
			var rng = ie_range_cache ? ie_range_cache : sel.createRange();
			rng.colapse;
			if((sel.type == "Text" || sel.type == "None") && rng != null)
			{
				if(ibClsTag != "" && rng.text.length > 0)
					ibTag += rng.text + ibClsTag;
				else if(isSingle)
					ibTag += rng.text + ibClsTag;
	
				rng.text = ibTag;
			}
		}
		else
		{
				obj_ta.value += ibTag + ibClsTag;
			
		}
		rng.select();
		ie_range_cache = null;

	}
	else if ( obj_ta.selectionEnd != null)
	{ 
		var ss = obj_ta.selectionStart;
		var st = obj_ta.scrollTop;
		var es = obj_ta.selectionEnd;
		
		var start  = (obj_ta.value).substring(0, ss);
		var middle = (obj_ta.value).substring(ss, es);
		var end    = (obj_ta.value).substring(es, obj_ta.textLength);
		
		if(!isSingle) middle = "";
		
		if (obj_ta.selectionEnd - obj_ta.selectionStart > 0)
		{
			middle = ibTag + middle + ibClsTag;
		}
		else
		{
			middle = ibTag + middle + ibClsTag;
		}
		
		obj_ta.value = start + middle + end;
		
		var cpos = ss + (middle.length);
		
		obj_ta.selectionStart = cpos;
		obj_ta.selectionEnd   = cpos;
		obj_ta.scrollTop      = st;


	}
	else
	{
		obj_ta.value += ibTag + ibClsTag;
	}

	obj_ta.focus();
	return isClose;
}

function setColor(color)
{
	doInsert("[color=" +color+ "]", "[/color]", true );
}

function dle_smiley ( text ){
	doInsert(' ' + text + ' ', '', false);

	ie_range_cache = null;
};
function image_upload()
{
	if ( is_ie )
	{
		document.getElementById(selField).focus();
		ie_range_cache = document.selection.createRange();
	}

	media_upload ( selField, '{$p_name}', '{$id}', 'no');

}
function insert_font(value, tag)
{
    if (value == 0)
    {
    	return;
	}
	
	if ( is_ie )
	{
		document.getElementById(selField).focus();
		ie_range_cache = document.selection.createRange();
	}
	
	doInsert("[" +tag+ "=" +value+ "]", "[/" +tag+ "]", true );


}

function insert_header(value) {
	
	if ( is_ie )
	{
		document.getElementById(selField).focus();
		ie_range_cache = document.selection.createRange();
	}
	
	doInsert("[h" +value+ "]", "[/h" +value+ "]", true );


};

function tag_typograf()
	{

		ShowLoading('');

		$.post("engine/ajax/typograf.php", { txt: document.getElementById( selField ).value}, function(data){
	
			HideLoading('');
	
			$('#' + selField).val(data); 
	
		});

	}

function get_sel(obj)
{

 if (document.selection) 
 {

   if ( is_ie )
   {
		document.getElementById(selField).focus();
		ie_range_cache = document.selection.createRange();
   }

   var s = document.selection.createRange(); 
   if (s.text)
   {
	 return s.text;
   }
 }
 else if (typeof(obj.selectionStart)=="number")
 {
   if (obj.selectionStart!=obj.selectionEnd)
   {
     var start = obj.selectionStart;
     var end = obj.selectionEnd;
	 return (obj.value.substr(start,end-start));
   }
 }

 return false;

};

$(function(){
	$( ".color-btn" ).click(function() {
	  setColor( $(this).data('value') );
	});
})
-->
</SCRIPT>
HTML;

$bb_panel = <<<HTML
<div class="bbcodes-editor">
	<div class="btn-group more-size">
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_b']}" onclick="simpletag('b'); return false;"><span class="editoricon-bold"></span></button>
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_i']}" onclick="simpletag('i'); return false;"><span class="editoricon-italic"></span></button>
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_u']}" onclick="simpletag('u'); return false;"><span class="editoricon-underline"></span></button>
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_s']}" onclick="simpletag('s'); return false;"><span class="editoricon-strikethrough"></span></button>
	</div>
	<div class="btn-group more-size">
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_sub']}" onclick="simpletag('sub'); return false;"><span class="editoricon-subscript"></span></button>
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_sup']}" onclick="simpletag('sup'); return false;"><span class="editoricon-superscript"></span></button>
	</div>
	<div class="btn-group more-size">
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_b_img']}" onclick="tag_image(); return false;"><span class="editoricon-image"></span></button>
		{$image_upload}
	</div>
	<div class="btn-group more-size single">
		<button type="button" data-toggle="dropdown" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_emo']}"><span class="editoricon-smile-o"></span></button>
		<ul class="dropdown-menu text-left">
			<li>{$smiles}</li>
		</ul>
	</div>
	<div class="btn-group more-size">
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_url']}" onclick="tag_url(); return false;"><span class="editoricon-chain"></span></button>
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_leech']}" onclick="tag_leech(); return false;"><span class="editoricon-key"></span></button>
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_m']}" onclick="tag_email(); return false;"><span class="editoricon-envelope-o"></span></button>
	</div>
	<div class="btn-group more-size">
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_video']}" onclick="tag_video(); return false;"><span class="editoricon-film"></span></button>
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_audio']}" onclick="tag_audio(); return false;"><span class="editoricon-music"></span></button>
	</div>
	<div class="btn-group more-size">
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_hide']}" onclick="simpletag('hide'); return false;"><span class="editoricon-eye-blocked"></span></button>
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_quote']}" onclick="simpletag('quote'); return false;"><span class="editoricon-quotes-left"></span></button>
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_code']}" onclick="simpletag('code'); return false;"><span class="editoricon-code"></span></button>
	</div>
	<div style="clear:both;"></div>
	<div class="btn-group single more-size">
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" data-toggle="dropdown" title="{$lang['bb_t_header']}"><span class="editoricon-header"></span><span class="caret"></span></button>
			<ul class="dropdown-menu">
				<li><a onclick="javascript:insert_header('1'); return(false);" href="#"><h1>{$lang['bb_header']} 1</h1></a></li>
				<li><a onclick="javascript:insert_header('2'); return(false);" href="#"><h2>{$lang['bb_header']} 2</h2></a></li>
				<li><a onclick="javascript:insert_header('3'); return(false);" href="#"><h3>{$lang['bb_header']} 3</h3></a></li>
				<li><a onclick="javascript:insert_header('4'); return(false);" href="#"><h4>{$lang['bb_header']} 4</h4></a></li>
				<li><a onclick="javascript:insert_header('5'); return(false);" href="#"><h5>{$lang['bb_header']} 5</h5></a></li>
				<li><a onclick="javascript:insert_header('6'); return(false);" href="#"><h6>{$lang['bb_header']} 6</h6></a></li>
			</ul>
	</div>
	<div class="btn-group single more-size">
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" data-toggle="dropdown" title="{$lang['bb_t_font']}"><span class="editoricon-font"></span><span class="caret"></span></button>
			<ul class="dropdown-menu text-left">
				<li><a onclick="javascript:insert_font('Arial', 'font'); return(false);" href="#" style="font-family:Arial">Arial</a></li>
				<li><a onclick="javascript:insert_font('Arial Black', 'font'); return(false);" href="#" style="font-family:Arial Black">Arial Black</a></li>
				<li><a onclick="javascript:insert_font('Century Gothic', 'font'); return(false);" href="#" style="font-family:Century Gothic">Century Gothic</a></li>
				<li><a onclick="javascript:insert_font('Courier New', 'font'); return(false);" href="#" style="font-family:Courier New">Courier New</a></li>
				<li><a onclick="javascript:insert_font('Georgia', 'font'); return(false);" href="#" style="font-family:Georgia">Georgia</a></li>
				<li><a onclick="javascript:insert_font('Impact', 'font'); return(false);" href="#" style="font-family:Impact">Impact</a></li>
				<li><a onclick="javascript:insert_font('System', 'font'); return(false);" href="#" style="font-family:System">System</a></li>
				<li><a onclick="javascript:insert_font('Tahoma', 'font'); return(false);" href="#" style="font-family:Tahoma">Tahoma</a></li>
				<li><a onclick="javascript:insert_font('Times New Roman', 'font'); return(false);" href="#" style="font-family:Times New Roman">Times New Roman</a></li>
				<li><a onclick="javascript:insert_font('Verdana', 'font'); return(false);" href="#" style="font-family:Verdana">Verdana</a></li>
			</ul>
	</div>
	<div class="btn-group single more-size">
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" data-toggle="dropdown" title="{$lang['bb_t_size']}"><span class="editoricon-text-height"></span><span class="caret"></span></button>
			<ul class="dropdown-menu">
				<li><a onclick="javascript:insert_font('1', 'size'); return(false);" href="#" style="font-size:8pt;">1</a></li>
				<li><a onclick="javascript:insert_font('2', 'size'); return(false);" href="#" style="font-size:10pt;">2</a></li>
				<li><a onclick="javascript:insert_font('3', 'size'); return(false);" href="#" style="font-size:12pt;">3</a></li>
				<li><a onclick="javascript:insert_font('4', 'size'); return(false);" href="#" style="font-size:14pt;">4</a></li>
				<li><a onclick="javascript:insert_font('5', 'size'); return(false);" href="#" style="font-size:18pt;">5</a></li>
				<li><a onclick="javascript:insert_font('6', 'size'); return(false);" href="#" style="font-size:24pt;">6</a></li>
				<li><a onclick="javascript:insert_font('7', 'size'); return(false);" href="#" style="font-size:36pt;">7</a></li>
			</ul>
	</div>
	<div class="btn-group more-size">
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_l']}" onclick="simpletag('left'); return false;"><span class="editoricon-align-left"></span></button>
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_c']}" onclick="simpletag('center'); return false;"><span class="editoricon-align-center"></span></button>
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_r']}" onclick="simpletag('right'); return false;"><span class="editoricon-align-right"></span></button>
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_j']}" onclick="simpletag('justify'); return false;"><span class="editoricon-align-justify"></span></button>
	</div>
	<div class="btn-group more-size">
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_list1']}" onclick="tag_list('list'); return false;"><span class="editoricon-list-ul"></span></button>
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_list2']}" onclick="tag_list('ol'); return false;"><span class="editoricon-list-ol"></span></button>
	</div>
	<div class="btn-group single">
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" data-toggle="dropdown" title="{$lang['bb_t_color']}"><span class="editoricon-brush"></span><span class="caret"></span></button>
			<ul class="dropdown-menu" style="min-width: 150px !important;">
				<li>
					<div class="color-palette"><div><button type="button" class="color-btn" style="background-color:#000000;" data-value="#000000"></button><button type="button" class="color-btn" style="background-color:#424242;" data-value="#424242"></button><button type="button" class="color-btn" style="background-color:#636363;" data-value="#636363"></button><button type="button" class="color-btn" style="background-color:#9C9C94;" data-value="#9C9C94"></button><button type="button" class="color-btn" style="background-color:#CEC6CE;" data-value="#CEC6CE"></button><button type="button" class="color-btn" style="background-color:#EFEFEF;" data-value="#EFEFEF"></button><button type="button" class="color-btn" style="background-color:#F7F7F7;" data-value="#F7F7F7"></button><button type="button" class="color-btn" style="background-color:#FFFFFF;" data-value="#FFFFFF"></button></div><div><button type="button" class="color-btn" style="background-color:#FF0000;" data-value="#FF0000"></button><button type="button" class="color-btn" style="background-color:#FF9C00;" data-value="#FF9C00"></button><button type="button" class="color-btn" style="background-color:#FFFF00;"  data-value="#FFFF00"></button><button type="button" class="color-btn" style="background-color:#00FF00;"  data-value="#00FF00"></button><button type="button" class="color-btn" style="background-color:#00FFFF;"  data-value="#00FFFF" ></button><button type="button" class="color-btn" style="background-color:#0000FF;"  data-value="#0000FF" ></button><button type="button" class="color-btn" style="background-color:#9C00FF;"  data-value="#9C00FF" ></button><button type="button" class="color-btn" style="background-color:#FF00FF;"  data-value="#FF00FF" ></button></div><div><button type="button" class="color-btn" style="background-color:#F7C6CE;"  data-value="#F7C6CE" ></button><button type="button" class="color-btn" style="background-color:#FFE7CE;"  data-value="#FFE7CE" ></button><button type="button" class="color-btn" style="background-color:#FFEFC6;"  data-value="#FFEFC6" ></button><button type="button" class="color-btn" style="background-color:#D6EFD6;"  data-value="#D6EFD6" ></button><button type="button" class="color-btn" style="background-color:#CEDEE7;"  data-value="#CEDEE7" ></button><button type="button" class="color-btn" style="background-color:#CEE7F7;"  data-value="#CEE7F7" ></button><button type="button" class="color-btn" style="background-color:#D6D6E7;"  data-value="#D6D6E7" ></button><button type="button" class="color-btn" style="background-color:#E7D6DE;"  data-value="#E7D6DE" ></button></div><div><button type="button" class="color-btn" style="background-color:#E79C9C;"  data-value="#E79C9C" ></button><button type="button" class="color-btn" style="background-color:#FFC69C;"  data-value="#FFC69C" ></button><button type="button" class="color-btn" style="background-color:#FFE79C;"  data-value="#FFE79C" ></button><button type="button" class="color-btn" style="background-color:#B5D6A5;"  data-value="#B5D6A5" ></button><button type="button" class="color-btn" style="background-color:#A5C6CE;"  data-value="#A5C6CE" ></button><button type="button" class="color-btn" style="background-color:#9CC6EF;"  data-value="#9CC6EF" ></button><button type="button" class="color-btn" style="background-color:#B5A5D6;"  data-value="#B5A5D6" ></button><button type="button" class="color-btn" style="background-color:#D6A5BD;"  data-value="#D6A5BD" ></button></div><div><button type="button" class="color-btn" style="background-color:#E76363;"  data-value="#E76363" ></button><button type="button" class="color-btn" style="background-color:#F7AD6B;"  data-value="#F7AD6B" ></button><button type="button" class="color-btn" style="background-color:#FFD663;"  data-value="#FFD663" ></button><button type="button" class="color-btn" style="background-color:#94BD7B;"  data-value="#94BD7B" ></button><button type="button" class="color-btn" style="background-color:#73A5AD;"  data-value="#73A5AD" ></button><button type="button" class="color-btn" style="background-color:#6BADDE;"  data-value="#6BADDE" ></button><button type="button" class="color-btn" style="background-color:#8C7BC6;"  data-value="#8C7BC6" ></button><button type="button" class="color-btn" style="background-color:#C67BA5;"  data-value="#C67BA5" ></button></div><div><button type="button" class="color-btn" style="background-color:#CE0000;"  data-value="#CE0000" ></button><button type="button" class="color-btn" style="background-color:#E79439;"  data-value="#E79439" ></button><button type="button" class="color-btn" style="background-color:#EFC631;"  data-value="#EFC631" ></button><button type="button" class="color-btn" style="background-color:#6BA54A;"  data-value="#6BA54A" ></button><button type="button" class="color-btn" style="background-color:#4A7B8C;"  data-value="#4A7B8C" ></button><button type="button" class="color-btn" style="background-color:#3984C6;"  data-value="#3984C6" ></button><button type="button" class="color-btn" style="background-color:#634AA5;"  data-value="#634AA5" ></button><button type="button" class="color-btn" style="background-color:#A54A7B;"  data-value="#A54A7B" ></button></div><div><button type="button" class="color-btn" style="background-color:#9C0000;"  data-value="#9C0000" ></button><button type="button" class="color-btn" style="background-color:#B56308;"  data-value="#B56308" ></button><button type="button" class="color-btn" style="background-color:#BD9400;"  data-value="#BD9400" ></button><button type="button" class="color-btn" style="background-color:#397B21;"  data-value="#397B21" ></button><button type="button" class="color-btn" style="background-color:#104A5A;"  data-value="#104A5A" ></button><button type="button" class="color-btn" style="background-color:#085294;"  data-value="#085294" ></button><button type="button" class="color-btn" style="background-color:#311873;"  data-value="#311873" ></button><button type="button" class="color-btn" style="background-color:#731842;"  data-value="#731842" ></button></div><div><button type="button" class="color-btn" style="background-color:#630000;"  data-value="#630000" ></button><button type="button" class="color-btn" style="background-color:#7B3900;"  data-value="#7B3900" ></button><button type="button" class="color-btn" style="background-color:#846300;"  data-value="#846300" ></button><button type="button" class="color-btn" style="background-color:#295218;"  data-value="#295218" ></button><button type="button" class="color-btn" style="background-color:#083139;"  data-value="#083139" ></button><button type="button" class="color-btn" style="background-color:#003163;"  data-value="#003163" ></button><button type="button" class="color-btn" style="background-color:#21104A;"  data-value="#21104A" ></button><button type="button" class="color-btn" style="background-color:#4A1031;"  data-value="#4A1031" ></button></div></div>				
				</li>
			</ul>
	</div>
	<div class="btn-group more-size">
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_flash']}" onclick="tag_flash(); return false;"><span class="editoricon-facebook2"></span></button>
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_youtube']}" onclick="tag_youtube(); return false;"><span class="editoricon-youtube-square"></span></button>
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_t']}" onclick="tag_typograf(); return false;"><span class="editoricon-font-size"></span></button>
	</div>
	<div class="btn-group more-size single">
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_spoiler']}" onclick="simpletag('spoiler'); return false;"><span class="editoricon-read-more"></span></button>
	</div>
	<div class="btn-group more-size">
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_br']}" onclick="pagebreak(); return false;"><span class="editoricon-page-break"></span></button>
		<button type="button" class="btn btn-default btn-sm btn-small" rel="tooltip" title="{$lang['bb_t_p']}" onclick="pagelink(); return false;"><span class="editoricon-insert-template"></span></button>
	</div>
</div>
HTML;

$bb_code = $bb_js.$bb_panel;
?>