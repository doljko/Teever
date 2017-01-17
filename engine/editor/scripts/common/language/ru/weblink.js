function loadTxt() {
  document.getElementById("tab0").innerHTML = "\u0421\u0441\u044b\u043b\u043a\u0430";
  document.getElementById("tab1").innerHTML = "\u0421\u0442\u0438\u043b\u0438";
  document.getElementById("lblUrl").innerHTML = "\u0410\u0434\u0440\u0435\u0441:";
  document.getElementById("lblTitle").innerHTML = "\u0417\u0430\u0433\u043e\u043b\u043e\u0432\u043e\u043a:";
  document.getElementById("lblTarget1").innerHTML = "\u041e\u0442\u043a\u0440\u044b\u0442\u044c \u043d\u0430 \u0442\u0435\u043a\u0443\u0449\u0435\u0439 \u0441\u0442\u0440\u0430\u043d\u0438\u0446\u0435";
  document.getElementById("lblTarget2").innerHTML = "\u041e\u0442\u043a\u0440\u044b\u0442\u044c \u043d\u0430 \u043d\u043e\u0432\u043e\u0439 \u0441\u0442\u0440\u0430\u043d\u0438\u0446\u0435";
  document.getElementById("lnkNormalLink").innerHTML = "\u041d\u043e\u0440\u043c\u0430\u043b\u044c\u043d\u0430\u044f \u0441\u0441\u044b\u043b\u043a\u0430 &raquo;";
  document.getElementById("btnCancel").value = "\u041e\u0442\u043c\u0435\u043d\u0430"
}
function writeTitle() {
  document.write("<title>" + "\u0412\u0441\u0442\u0430\u0432\u043a\u0430 \u0441\u0441\u044b\u043b\u043a\u0438" + "</title>")
}
function getTxt(s) {
  switch(s) {
    case "insert":
      return"\u0412\u0441\u0442\u0430\u0432\u0438\u0442\u044c";
    case "change":
      return"\u0418\u0437\u043c\u0435\u043d\u0438\u0442\u044c"
  }
}
;