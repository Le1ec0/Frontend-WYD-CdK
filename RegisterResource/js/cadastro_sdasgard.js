//DEFINE CSS
var fileref=document.createElement("link");
fileref.setAttribute("rel", "stylesheet");
fileref.setAttribute("type", "text/css");
var bg = getQueryVariable("bg");
if (bg === "friend") {
  fileref.setAttribute("href", "./RegisterResource/css/friend.css");
} else {
  fileref.setAttribute("href", "./RegisterResource/css/default.css");
}
document.getElementsByTagName("head")[0].appendChild(fileref);

//Fix Download Link by code

function getQueryVariable(variable) {
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
    if (pair[0] == variable) {
      return pair[1];
    }
  }
  return undefined;
}

function logAnalytics() { }

//////////////////////////////////////////////////////////
/*function CenterForm() {
	var popup = document.getElementsByClassName("main")[0];
	var head = document.getElementsByClassName("head")[0];
	var popupHeight = popup.offsetHeight;
	var body = document.body,
	html = document.documentElement;

	var height = Math.max( body.scrollHeight, body.offsetHeight,
	html.clientHeight, html.scrollHeight, html.offsetHeight );
	head.style.height = ((height-popupHeight)/2)+"px";
}

document.onresize = function() {
  CenterForm();
};
*/
document.onreadystatechange = function () {
  var state = document.readyState
  if (state == 'complete') {
  	document.body.style.opacity = 1;
  	//CenterForm();
  }

  document.getElementById("baixeOJogo").innerHTML = "<td>Ja tem uma conta? <a href=''>Baixe o jogo</a></td>";

};
