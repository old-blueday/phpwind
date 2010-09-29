var agt = navigator.userAgent.toLowerCase();
var is_ie = ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));
var is_gecko= (navigator.product == "Gecko");

var gIsPost = true;
if (location.href.indexOf('/simple/') != -1) {
	getObj('headbase').href = location.href.substr(0,location.href.indexOf('/simple/')+1);
} else if (location.href.indexOf('.html')!=-1) {
	var base = location.href.replace(/^(http(s)?:\/\/(.*?)\/)[^\/]*\/[0-9]+\/[0-9]{4,6}\/[0-9]+\.html$/i,'$1');
	if (base != location.href) {
		getObj('headbase').href = base;
	}
}