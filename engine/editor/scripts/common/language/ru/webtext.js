function loadTxt() {
  document.getElementById("tab0").innerHTML = "\u0422\u0435\u043a\u0441\u0442";
  document.getElementById("tab1").innerHTML = "\u0422\u0435\u043d\u0438";
  document.getElementById("tab2").innerHTML = "\u041f\u0430\u0440\u0430\u0433\u0440\u0430\u0444";
  document.getElementById("tab3").innerHTML = "\u0421\u043f\u0438\u0441\u043a\u0438";
  document.getElementById("tab4").innerHTML = "\u0420\u0430\u0437\u043c\u0435\u0440";
  document.getElementById("lblColor").innerHTML = "\u0426\u0432\u0435\u0442 \u0442\u0435\u043a\u0441\u0442\u0430:";
  document.getElementById("lblHighlight").innerHTML = "\u0426\u0432\u0435\u0442 \u0444\u043e\u043d\u0430:";
  document.getElementById("lblLineHeight").innerHTML = "\u0412\u044b\u0441\u043e\u0442\u0430 \u0441\u0442\u0440\u043e\u043a:";
  document.getElementById("lblLetterSpacing").innerHTML = "\u041e\u0442\u0441\u0443\u043f\u044b \u0441\u0438\u043c\u0432\u043e\u043b\u043e\u0432:";
  document.getElementById("lblWordSpacing").innerHTML = "\u041e\u0442\u0441\u0443\u043f\u044b \u0441\u043b\u043e\u0432:";
  document.getElementById("lblNote").innerHTML = "\u042d\u0442\u0430 \u0432\u043e\u0437\u043c\u043e\u0436\u043d\u043e\u0441\u0442\u044c \u043d\u0435 \u043f\u043e\u0434\u0434\u0435\u0440\u0436\u0438\u0432\u0430\u0435\u0442\u0441\u044f \u0432 IE."
}
function writeTitle() {
  document.write("<title>" + "\u041f\u0430\u0440\u0430\u043c\u0435\u0442\u0440\u044b \u0442\u0435\u043a\u0441\u0442\u0430" + "</title>")
}
function getTxt(s) {
  switch(s) {
    case "DEFAULT SIZE":
      return"\u0420\u0430\u0437\u043c\u0435\u0440 \u043f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e";
	case "Heading 1": return "Heading 1";
    case "Heading 2": return "Heading 2";
    case "Heading 3": return "Heading 3";
    case "Heading 4": return "Heading 4";
    case "Heading 5": return "Heading 5";
    case "Heading 6": return "Heading 6";
    case "Preformatted": return "Preformatted";
    case "Normal": return "Normal";
  }
}
;