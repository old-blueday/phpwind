function MiniEditor(obj) {
	this.obj = getObj(obj);
	this.linknum = 1;
	this._selRange = null;

	var its = this;
	this.obj.onmousedown = function() {
		its._selRange = null;
	}
}

MiniEditor.prototype.createLinkBox = function(object) {
	this.saveRange();
	if (IsElement('mini_linkbox')) {
		var div = getObj('mini_linkbox');
	} else {
		var div = document.createElement('div');
		div.id  = 'mini_linkbox';
		div.style.display = 'none';
		div.innerHTML = '<div class="popout"><table border="0" cellspacing="0" cellpadding="0"><tbody><tr><td class="bgcorner1"></td><td class="pobg1"></td><td class="bgcorner2"></td></tr><tr><td class="pobg4"></td><td><div class="popoutContent"><div style="width:400px"><div class="popTop" style="cursor:move" onmousedown="read.move(event);"><a href="javascript:;" class="adel fr" onclick="closep();">删除</a>插入url链接</div><div style="padding:5px 0 10px"><table width="100%" cellspacing="0" cellpadding="0"><tbody id="linkmode" style="display:none;"><tr><td><input class="input" id="linkdiscrip" size="20" value="" /></td><td><input class="input" id="linkaddress" value="http://" size="35" /></td></tr></tbody><tr><td>链接说明</td><td><a href="javascript:;" class="fr s3 mr10" id="mini_addlink">[添加]</a>链接地址</td></tr><tr><td><input class="input" id="linkdiscrip1" size="20" value="" /></td><td><input class="input" id="linkaddress1" value="http://" size="35" /></td></tr><tbody id="linkbody"></tbody></table></div><div class="popBottom"><span class="btn2"><span><button type="button" id="mini_button">提 交</button></span></span></div></div></div></td><td class="pobg2"></td></tr><tr><td class="bgcorner4"></td><td class="pobg3"></td><td class="bgcorner3"></td></tr></tbody></table></div>';
		document.body.appendChild(div);
	}
	read.open('mini_linkbox', object, '2');
	
	var text = this.getsel();
	if (text) {
		getObj('linkdiscrip1').value = text;
	}
	var its = this;
	getObj('mini_addlink').onclick = function() {
		its.addlink();
	}
	getObj('mini_button').onclick = function() {
		its.insertlink();
	}
}

MiniEditor.prototype.insertlink = function() {
	var AddTxt = '';
	var text = '';
	var temp_linknum = this.linknum;
	this.restoreRange();
	for (var i = 1; i <= this.linknum; i++) {
		if (getObj('linkdiscrip'+i).value.length == 0 && getObj("linkaddress"+i).value == 'http://') {
			continue;
		}
		if (getObj('linkdiscrip'+i).value) {
			AddTxt += "[url=" + encodeURI(getObj("linkaddress"+i).value) + "]" + getObj("linkdiscrip"+i).value + "[/url]";
			text = getObj("linkdiscrip"+i).value;
		} else {
			AddTxt += "[url=" + encodeURI(getObj("linkaddress"+i).value) + "]" + getObj("linkaddress"+i).value + "[/url]";
			text = getObj("linkdiscrip"+i).value;
		}
	}
	this.AddText(AddTxt, text);
	this.linknum = 1;
	closep();
}

MiniEditor.prototype.AddText = function(code, text) {
	this.obj.focus();
	var startpos = text == '' ? code.indexOf(']') + 1 : code.indexOf(text);
	if (document.selection) {
		var sel = document.selection.createRange();
		sel.text = code.replace(/\r?\n/g, '\r\n');
		sel.moveStart('character',-code.replace(/\r/g,'').length + startpos);
		sel.moveEnd('character', -code.length + text.length + startpos);
		sel.select();
	} else if (typeof this.obj.selectionStart != 'undefined') {
		var prepos = this.obj.selectionStart;
		this.obj.value = this.obj.value.substr(0,prepos) + code + this.obj.value.substr(this.obj.selectionEnd);
		this.obj.selectionStart = prepos + startpos;
		this.obj.selectionEnd = prepos + startpos + text.length;
	} else {
		this.obj.value += code;
	}
}

MiniEditor.prototype.getsel = function () {
	if (document.selection) {
		return document.selection.createRange().text;
	} else if (typeof this.obj.selectionStart != 'undefined') {
		return this.obj.value.substr(this.obj.selectionStart,this.obj.selectionEnd - this.obj.selectionStart);
	} else if (window.getSelection) {
		return window.getSelection();
	}
}

MiniEditor.prototype.addlink = function() {
	var s = getObj('linkmode').firstChild.cloneNode(true);
	var temp_linknum = ++this.linknum;
	var tags = s.getElementsByTagName('input');
	for (var i = 0; i < tags.length; i++) {
		if (tags[i].id == 'linkdiscrip') {
			tags[i].id = 'linkdiscrip' + this.linknum;
		}
		if (tags[i].id == 'linkaddress') {
			tags[i].id = 'linkaddress' + this.linknum;
		}
	}
	getObj('linkbody').appendChild(s);
}

MiniEditor.prototype.bold = function() {
	var text = this.getsel();
	var AddTxt = "[b]" + text + "[/b]";
	this.AddText(AddTxt, text, this.obj);
}

MiniEditor.prototype.createcolor = function(obj) {
	this.saveRange();
	if (IsElement('mini_createcolor')) {
		var div = getObj('mini_createcolor');
	} else {
		var div = document.createElement('div');
		div.id  = 'mini_createcolor';
		div.style.display = 'none';

		var colors = [
			'000000','660000','663300','666600','669900','66CC00','66FF00',
			'666666','660066','663366','666666','669966','66CC66','66FF66',
			'CCCCCC','6600CC','6633CC','6666CC','6699CC','66CCCC','66FFCC',
			'FF0000','FF3300','FF6600','FF9900','FFCC00','FFFF00','00FFFF',
			'FF0066','FF3366','FF6666','FF9966','FFCC66','FFFF66','00CCFF',
			'FF00CC','FF33CC','FF66CC','FF99CC','FFCCCC','FFFFCC','0000FF'
		];
		var html = '<div id="colorbox">';
		for (var i = 0; i < colors.length; i++) {
			html += "<div unselectable=\"on\" style=\"background:#" + colors[i] + "\" id=\"color_" + colors[i] + "\"></div>";
		}
		html += '</div>';
		div.innerHTML = html;
		document.body.appendChild(div);
	}
	read.open('mini_createcolor', obj, '2');

	var its = this;
	var divs = getObj('colorbox').getElementsByTagName('div');
	for (var i = 0; i < divs.length; i++) {
		divs[i].onclick = function() {
			its.insertcolor(this.id);
		}
	}
}

MiniEditor.prototype.insertcolor = function(str) {
	this.saveRange();
	var color = str.substr(6);
	var text = this.getsel();
	var AddTxt = "[color=#" + color + "]" + text + "[/color]";
	this.AddText(AddTxt, text);
	closep();
}

MiniEditor.prototype.saveRange = function() {
	if (is_ie) {
		this.obj.focus();
		this._selRange = document.selection.createRange();
	}
}

MiniEditor.prototype.restoreRange = function() {
	this.obj.focus();
	if (this._selRange) {
		this._selRange.select();
		this._selRange = null;
	}
}