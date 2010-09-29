function getAbsolutePos(el) {
	var r = { x: el.offsetLeft, y: el.offsetTop };
	if (el.offsetParent) {
		var tmp = getAbsolutePos(el.offsetParent);
		r.x += tmp.x;
		r.y += tmp.y;
	}
	return r;
};
function __dlg_onclose() {
	opener.Dialog._return(null);
};
function __dlg_init(bottom) {
	var body = document.body;
	var body_height = 0;
	if (typeof bottom == "undefined") {
		var div = document.createElement("div");
		body.appendChild(div);
		var pos = getAbsolutePos(div);
		body_height = pos.y;
	} else {
		var pos = getAbsolutePos(bottom);
		body_height = pos.y + bottom.offsetHeight;
	}
	window.dialogArguments = opener.Dialog._arguments;
	if (!document.all) {
		window.sizeToContent();
		window.sizeToContent();
		window.addEventListener("unload", __dlg_onclose, true);
		window.innerWidth = body.offsetWidth;
		window.innerHeight = body_height;
	} else {
		window.resizeTo(body.offsetWidth, body_height);
		var ch = body.clientHeight;
		var cw = body.clientWidth;
		window.resizeBy(body.offsetWidth - cw, body_height - ch);
	}
};
function __dlg_close(val) {
	opener.Dialog._return(val);
	window.close();
};

function Init() {
	__dlg_init();
	document.getElementById("f_rows").focus();
};
function onOK() {
	var fields = ["f_rows", "f_cols", "f_width", "f_unit"];
	var param = new Object();
	for(var i in fields) {
		var id = fields[i];
		var el = document.getElementById(id);
		param[id] = el.value;
	}
	__dlg_close(param);
	return false;
};
function onCancel() {
	__dlg_close(null);
	return false;
};