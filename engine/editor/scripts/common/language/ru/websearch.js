function loadTxt() {
  document.getElementById("lblSearch").innerHTML = "\u041d\u0430\u0439\u0442\u0438:";
  document.getElementById("lblReplace").innerHTML = "\u0417\u0430\u043c\u0435\u043d\u0438\u0442\u044c:";
  document.getElementById("lblMatchCase").innerHTML = "\u0440\u0435\u0433\u0438\u0441\u0442\u0440";
  document.getElementById("lblMatchWhole").innerHTML = "\u0438\u0441\u043a\u0430\u0442\u044c \u0441\u043b\u043e\u0432\u0430";
  document.getElementById("btnSearch").value = "\u0418\u0441\u043a\u0430\u0442\u044c \u0434\u0430\u043b\u0435\u0435";
  document.getElementById("btnReplace").value = "\u0417\u0430\u043c\u0435\u043d\u0438\u0442\u044c";
  document.getElementById("btnReplaceAll").value = "\u0417\u0430\u043c\u0435\u043d\u0438\u0442\u044c \u0432\u0441\u0435"
}
function getTxt(s) {
  switch(s) {
    case "Finished searching":
      return"\u041f\u043e\u0438\u0441\u043a \u043f\u043e \u0434\u043e\u043a\u0443\u043c\u0435\u043d\u0442\u0443 \u0437\u0430\u043a\u043e\u043d\u0447\u0435\u043d.\n\u041d\u0430\u0447\u0430\u0442\u044c \u043f\u043e\u0438\u0441\u043a \u0441\u043d\u0430\u0447\u0430\u043b\u0430?";
    default:
      return""
  }
}
function writeTitle() {
  document.write("<title>\u043f\u043e\u0438\u0441\u043a \u0438 \u0437\u0430\u043c\u0435\u043d\u0430</title>")
}
;