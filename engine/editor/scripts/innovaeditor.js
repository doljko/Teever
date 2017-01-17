/*** Editor Script Wrapper ***/
var oScripts=document.getElementsByTagName("script"); 
var sEditorPath;
for(var i=0;i<oScripts.length;i++)
  {
  var sSrc=oScripts[i].src.toLowerCase();
  if(sSrc.indexOf("scripts/innovaeditor.js")!=-1) sEditorPath=oScripts[i].src.replace(/innovaeditor.js/,"");
}

document.write("<scr" + "ipt src='" + sEditorPath + "common/nlslightbox/nlslightbox.js' type='text/javascript'></scr" + "ipt>");
document.write("<scr" + "ipt src='" + sEditorPath + "common/nlslightbox/nlsanimation.js' type='text/javascript'></scr" + "ipt>");
document.write("<link href='" + sEditorPath + "common/nlslightbox/nlslightbox.css' rel='stylesheet' type='text/css' />");
document.write("<scr" + "ipt src='" + sEditorPath + "common/nlslightbox/dialog.js' type='text/javascript'></scr" + "ipt>");

document.write("<li"+"nk rel='stylesheet' href='"+sEditorPath+"style/istoolbar.css' type='text/css' />");
document.write("<scr"+"ipt src='"+sEditorPath+"istoolbar.js'></scr"+"ipt>");

var UA = navigator.userAgent.toLowerCase();
var LiveEditor_isIE = (UA.indexOf('msie') >= 0) ? true : false;

if(LiveEditor_isIE) {
  document.write("<scr"+"ipt src='"+sEditorPath+"editor.js'></scr"+"ipt>");
} else if(UA.indexOf('safari')!=-1 && UA.indexOf('edge') == -1) {
  document.write("<scr"+"ipt src='"+sEditorPath+"saf/editor.js'></scr"+"ipt>");
} else { //ie11 use moz script now.
  document.write("<scr"+"ipt src='"+sEditorPath+"moz/editor.js'></scr"+"ipt>");
}

function DLEcustomTag(StartTag, EndTag) {
  var obj = oUtil.obj;
  var oEditor = oUtil.oEditor;
  var oSel;
  
   obj.saveForUndo();
	
  if(navigator.appName.indexOf("Microsoft") != -1) {
    if(!oEditor) {
      return
    }
    oEditor.focus();
    obj.setFocus();
    oSel = oEditor.document.selection.createRange();

	var sHTML = StartTag + oSel.htmlText + EndTag;
	sHTML = sHTML.replace(/[\n\t\r]/gi, "");
	
    if(oSel.parentElement) {
     oSel.pasteHTML(sHTML)
	 
    }else {
      oSel.item(0).outerHTML = sHTML
    }

  }else {
    oSel = oEditor.getSelection();
    var range = oSel.getRangeAt(0);
	
	var d = oEditor.document.createElement('div'); 
    d.appendChild(range.cloneContents());
	var selhtml = d.innerHTML
	var sHTML = StartTag + selhtml + EndTag;
    var docFrag = range.createContextualFragment(sHTML);
    var lastNode = docFrag.childNodes[docFrag.childNodes.length - 1];
	range.deleteContents();
    range.insertNode(docFrag);
    try {
      oEditor.document.designMode = "on";
    }catch(e) {
    }
    range = oEditor.document.createRange();
    range.setStart(lastNode, lastNode.nodeValue.length);
    range.setEnd(lastNode, lastNode.nodeValue.length);
    oSel = oEditor.getSelection();
    oSel.removeAllRanges();
    oSel.addRange(range);
  }
};

function DLEclean() {
  var obj = oUtil.obj;
  var oEditor = oUtil.oEditor;
  var oSel;
  
   obj.saveForUndo();
	
  if(navigator.appName.indexOf("Microsoft") != -1) {
    if(!oEditor) {
      return
    }
    oEditor.focus();
    obj.setFocus();
    oSel = oEditor.document.selection.createRange();

	var sHTML = delete_all_format( oSel.htmlText );

	if ( !sHTML || sHTML == "" ) return;

	sHTML = sHTML.replace(/[\n\t\r]/gi, "");

	if ( !sHTML || sHTML == "" ) return;
	
    if(oSel.parentElement) {
     oSel.pasteHTML(sHTML);
	 
    }else {
      oSel.item(0).outerHTML = sHTML
    }

  }else {
    oSel = oEditor.getSelection();
    var range = oSel.getRangeAt(0);
	
	var d = oEditor.document.createElement('div'); 
    d.appendChild(range.cloneContents());
	var selhtml = d.innerHTML
	var sHTML = delete_all_format( selhtml );
	if ( !sHTML || sHTML == "" ) return;
    var docFrag = range.createContextualFragment(sHTML);
    var lastNode = docFrag.childNodes[docFrag.childNodes.length - 1];
	range.deleteContents();
    range.insertNode(docFrag);
    try {
      oEditor.document.designMode = "on";
      range = oEditor.document.createRange();
      range.setStart(lastNode, lastNode.nodeValue.length);
      range.setEnd(lastNode, lastNode.nodeValue.length);
      oSel = oEditor.getSelection();
      oSel.removeAllRanges();
      oSel.addRange(range);
    }catch(e) {
    }
  }
};

function delete_all_format( str ) {

	if ( !str || str == "" ) return false;

	str = String(str).replace(/<!(?:--[\s\S]*?--\s*)?>\s*/gi, "");
	str = String(str).replace(/<\\?\?xml[^>]*>/gi, "");
	str = String(str).replace(/<\/?o:p[^>]*>/gi, "");
	str = String(str).replace(/<\/?u1:p[^>]*>/gi, "");
	str = String(str).replace(/<\/?v:[^>]*>/gi, "");
	str = String(str).replace(/<\/?o:[^>]*>/gi, "");
	str = String(str).replace(/<\/?st1:[^>]*>/gi, "");
	str = String(str).replace(/<\/?w:wrap[^>]*>/gi, "");
	str = String(str).replace(/<\/?w:anchorlock[^>]*>/gi, "");
	str = String(str).replace(/<\!--\[if[^>]*>/gi, "");
	str = String(str).replace(/<\!--\[endif\]--\>/gi, "");
	str = String(str).replace(/<!\[endif\]--\>/gi, "");
	str = String(str).replace(/<\/?meta[^>]*>/gi, "");
	str = String(str).replace(/<\/?link[^>]*>/gi, "");
	str = String(str).replace(/<\/?style[^>]*>/gi, "");
	str = String(str).replace(/<\/?style[^>]*>/gi, "");
	str = String(str).replace(/<p[^>]*>/gi, "<p>");
	str = String(str).replace(/<div[^>]*>/gi, "<div>");
	str = String(str).replace(/<h1[^>]*>/gi, "<h1>");
	str = String(str).replace(/<h2[^>]*>/gi, "<h2>");
	str = String(str).replace(/<h3[^>]*>/gi, "<h3>");
	str = String(str).replace(/<h4[^>]*>/gi, "<h4>");
	str = String(str).replace(/<h5[^>]*>/gi, "<h5>");
	str = String(str).replace(/<h6[^>]*>/gi, "<h6>");
	str = String(str).replace(/<br[^>].+?>/gi, "<br />");
	str = String(str).replace(/<span[^>]*>/gi, "");
	str = String(str).replace(/<\/span>/gi, "");
	str = String(str).replace(/<stong[^>]*>/gi, "");
	str = String(str).replace(/<\/stong>/gi, "");
	str = String(str).replace(/<em[^>]*>/gi, "");
	str = String(str).replace(/<\/em>/gi, "");
	str = String(str).replace(/<b>/gi, "");
	str = String(str).replace(/<\/b>/gi, "");
	str = String(str).replace(/<i>/gi, "");
	str = String(str).replace(/<\/i>/gi, "");
	str = String(str).replace(/<a[^>].+?><\/a>/gi, "");

	return str;
}

function submit_all_data() {
  var sContent;
  for(var i = 0;i < oUtil.arrEditor.length;i++) {
    var oEdit = eval(oUtil.arrEditor[i]);
      sContent = oEdit.getXHTMLBody();
	  if (sContent == "<br />") sContent = '';
	  document.getElementById(oEdit.idTextArea).value = sContent
  }
};