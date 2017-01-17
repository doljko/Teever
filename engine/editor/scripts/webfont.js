function LoadFont(fontFamily) {
  if(fontFamily != "") {
    try {
	  fontFamily=fontFamily.split("'").join('');
      WebFont.load({google:{families:[fontFamily + "::latin,cyrillic"]}})
    }catch(e) {
    }
  }
}
function EmbedFont(id) {
  var arrSysFonts = ["impact", "palatino linotype", "tahoma", "century gothic", "lucida sans unicode", "times new roman", "arial narrow", "verdana", "copperplate gothic light", "lucida console", "gill sans mt", "trebuchet ms", "courier new", "arial", "georgia", "garamond", "arial black", "bookman old style", "courier", "helvetica"];
  var sHTML;
  if(!id) {
    sHTML = document.documentElement.innerHTML
  }else {
    sHTML = document.getElementById(id).innerHTML
  }
  var urlRegex = /font-family?:.+?(\;|,|")/g;
  var matches = sHTML.match(urlRegex);
  if(matches) {
    for(var i = 0, len = matches.length;i < len;i++) {
      var sFont = matches[i].replace(/font-family?:/g, "").replace(/;/g, "").replace(/,/g, "").replace(/"/g, "");
      sFont = jQuery.trim(sFont);
      sFont = sFont.replace("'", "").replace("'", "");
      if($.inArray(sFont.toLowerCase(), arrSysFonts) == -1) {
        LoadFont(sFont)
      }
    }
  }
}
jQuery(document).ready(function() {
  EmbedFont();
  if(typeof oUtil != "undefined") {
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
  }
});
