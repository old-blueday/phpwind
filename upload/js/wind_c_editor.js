//WYSIWYD JS CODE
var code_htm = new Array();
var method = 4;

function WYSIWYD() {
	this._editMode = 'textmode';
	this.config = null;
	//this.config = new WYSIWYD.Config();
	this._htmlArea = null;
	this._textArea = null;
	this._timerToolbar = null;
	this._doc = null;
	this._iframe = null;
	this._selRange = null;
	this._linknum = 1;
	this.allowHtml = 0;
	this.allowConvert = 1;
	this.mt = null;
	this.ct = 5;
};
WYSIWYD.prototype.init = function(mode, allowHtml) {
	this._textArea = WYSIWYD.getElementById("textarea",'textarea');
	var editor = this;
	var textarea = this._textArea;
	this._textArea.onkeyup = function(){editor.autoSave();};
		
	if (typeof allowHtml != 'undefined') {
		this.allowHtml = allowHtml;
	}
	if (mode == 'wysiwyg') {
		this.setMode(mode);
	}
	if (textarea.form) {
		WYSIWYD._addEvent(textarea, "keydown",function(event) {quickpost(event);});
		WYSIWYD._addEvent(textarea, "mousedown", function() {editor.clearRange();});
		var f = textarea.form;
		var funcref = f.onsubmit;
		/*
		if (typeof f.onsubmit == "function") {
			var funcref = f.onsubmit;
			if (typeof f.__msh_prevOnSubmit == "undefined") {
				f.__msh_prevOnSubmit = [];
			}
			f.__msh_prevOnSubmit.push(funcref);
		}
		*/
		f.onsubmit = function() {
			if (editor._editMode == "textmode") {
				editor._textArea.value = editor.getHTML();
			} else {
				editor._textArea.value = htmltocode(editor.getHTML());
			}
			if (typeof funcref == "function") {
				return funcref();
			}
			return true;
			/*
			var a = this.__msh_prevOnSubmit;
			if (typeof a != "undefined") {
				for (var i in a) {
					return a[i]();
				}
			}
			*/
		};
	}
	this.initButtom();
	this.updateToolbar();
};
WYSIWYD.prototype.autoSave = function() {
	var value = this.getHTML();
	if (!value) {
		return;
	}
	getObj('autosave').style.display = 'none';
	var _=this;
	if (_.mt) {
		clearTimeout(_.mt);
	}
	_.mt = setTimeout(function() {
		getObj('autosave').style.display = '';
		saveData('msg', _._editMode == 'textmode' ? _.getHTML() : htmltocode(_.getHTML()));
		_.mt = null;
	}, _.ct * 1000);
}
WYSIWYD.prototype.setConvert = function(o) {
	this.allowConvert = o.checked ? 1 : 0;
}
WYSIWYD.prototype.setHtmlMode = function(o) {
	this.allowHtml = o.checked ? 1 : 0;
}
WYSIWYD.prototype.initButtom = function() {
	var tb_objects = new Object();
	this._toolbarObjects = tb_objects;

	function setButtonStatus(id, newval) {
		var oldval = this[id];
		var el = this.element;
		if (oldval != newval) {
			switch(id) {
			    case "enabled":
					if (newval) {
						WYSIWYD._removeClass(el, "buttonDisabled");
						el.disabled = false;
					} else {
						WYSIWYD._addClass(el, "buttonDisabled");
						el.disabled = true;
					}
					break;
			    case "active":
					if (newval) {
						WYSIWYD._addClass(el, "buttonPressed");
					} else {
						WYSIWYD._removeClass(el, "buttonPressed");
					}
					break;
			}
			this[id] = newval;
		}
	}

	function setButton(txt,btn) {
		var el = document.getElementById('wy_' + txt);
		if (!el) return;
		var obj = {
			name	: txt,
			element : el,
			enabled : true,
			active	: false,
			text	: btn[0],
			cmd		: btn[1],
			state	: setButtonStatus,
			mover   : btn[2]
		}
		tb_objects[txt] = obj;
		el.unselectable = "on";
		el.href = 'javascript:';

		WYSIWYD._addEvent(el, "click", function(ev) {
			if (obj.enabled) with (WYSIWYD) {
				//_removeClass(el, "buttonActive");
				//_removeClass(el, "buttonHover");
				obj.cmd(obj.name);
				//_stopEvent(is_ie ? window.event : ev);
			}
			return (el.tagName.toLowerCase() == 'a') ? false : true;
		});
	}
	function setSelect(txt) {
		var el = document.getElementById('wy_' + txt);
		if (!el) return;
		var cmd = txt;
		var options = editor.config[txt];
		if (options) {
			var obj = {
				name	: txt,
				element : el,
				enabled : true,
				text	: true,
				cmd		: cmd,
				state	: setButtonStatus,
				mover   : false
			}
			tb_objects[txt] = obj;
			el.unselectable = "on";

			WYSIWYD._addEvent(el, "click", function(ev) {
				ShowSelect(obj.name);
			});
		}
	}
	var buttoms = this.config.btnList;
	for (var txt in buttoms) {
		if (txt != 'event') {
			setButton(txt, buttoms[txt]);
		}
	}
	var selects = this.config.selList;
	for (var i in selects) {
		if (i != 'event') {
			setSelect(selects[i]);
		}
	}
};
WYSIWYD.prototype.initIframe = function() {

	var htmlarea = document.createElement("div");
	htmlarea.id  = 'htmlarea';
	htmlarea.className = "htmlarea";
	htmlarea.style.width = "100%";
	this._htmlArea = htmlarea;
	this._textArea.parentNode.insertBefore(htmlarea, this._textArea);

	var iframe = document.createElement("iframe");
	iframe.name = 'iframe';
	iframe.tabIndex = '199';
	iframe.style.display = "none";
	htmlarea.appendChild(iframe);
	this._iframe = iframe;

	if (!is_ie) {
		iframe.style.borderWidth = "0px";
	}
	var height = this._textArea.offsetHeight || this._textArea.style.height;
	var width  = this._textArea.offsetWidth || this._textArea.style.width;
	height = parseInt(height);
	width = parseInt(width);
	if (!is_ie) {
		height -= 3;
		width -= 3;
	}
	width = width ? width +"px":"100%";
	height = height ? height +"px":"100%";
	iframe.style.width  = width;
	iframe.style.height = height;
	
	this._textArea.style.width = iframe.style.width;
	this._textArea.style.height= iframe.style.height;

	var doc = this._iframe.contentWindow.document;

	this._doc = doc;

	doc.open();
	var html = "<html>\n";
	html += "<head>\n";
	html += "<style> html,body {border:0px;font-family:arial;font-size:14px;margin:0;padding:0;line-height:1.5;}\n";
	html += ".t {border:1px solid #abc8d0;border-collapse : collapse;background:#f8fcfe;}\n";
	html += ".t td {border: 1px solid #D4EFF7;}\n";
	html += "img {border:0;}p {margin:0px;}</style>\n";
	html += "</head>\n";
	html += "<body>\n";
	html += "</body>\n";
	html += "</html>";
	doc.write(html);
	doc.close();

	if (is_ie) {
		doc.body.contentEditable = true;
	}
	WYSIWYD._addEvent(doc, "keydown", function(event) {quickpost(event);});
	WYSIWYD._addEvent(doc, "keyup", function() {editor.autoSave();});
	WYSIWYD._addEvent(doc, "mousedown", function() {editor.clearRange();});
	WYSIWYD._addEvents(doc, ["keydown", "keypress", "mousedown", "mouseup", "drag"],
		function(event) {return editor._editorEvent(is_ie ? editor._iframe.contentWindow.event : event);}
	);
};
WYSIWYD.getOuterHTML = function(ele){
	if(ele.outerHTML){
		return ele.outerHTML;
	}else{
		var attrs=ele.attributes,l = attrs.length, str="<"+ele.tagName.toLowerCase();
		for(var i = 0; i < l; i++)
		{
			if(attrs[i].specified){
                str+=" "+attrs[i].name+'="'+attrs[i].value+'"';
            }
		}
		if('area base basefont col frame hr img br inpu isindex link meta param '.indexOf(' '+ele.tagName.toLowerCase()+' ')>-1){
			return str+" />";
		}
		else
			return str+">"+ele.innerHTML+"</"+ele.tagName.toLowerCase()+">";
	}
}
WYSIWYD.getDfHtml = function(df){
	var str = '',eles = df.childNodes, l=eles.length;
	for(var i=0;i<l;i++){
		if(eles[i].nodeType==3)
			str += eles[i].textContent;
		else if(eles[i].nodeType==1)
			str += WYSIWYD.getOuterHTML(eles[i]);
	}
	return str;
}
WYSIWYD.prototype.getsel = function () {
	if (this._editMode == "wysiwyg") {
		if (is_ie) {
			return this._createRange(this._getSelection()).htmlText;
		} else {
			return WYSIWYD.getDfHtml(this._createRange(this._getSelection()).cloneContents());
		}
	} else if (document.selection) {
		return document.selection.createRange().text;
	} else if (typeof this._textArea.selectionStart != 'undefined') {
		return this._textArea.value.substr(this._textArea.selectionStart,this._textArea.selectionEnd - this._textArea.selectionStart);
	} else if (window.getSelection) {
		return window.getSelection();
	}
};
WYSIWYD.prototype.setMode = function(mode) {
	if (typeof mode == "undefined") {
		mode = ((this._editMode == "textmode") ? "wysiwyg" : "textmode");
		ajax.send('pw_ajax.php?action=changeeditor&editor=' + (mode == 'wysiwyg' ? 1 : 0), '', null);
	}
	switch (mode) {
	    case "textmode":
			this._textArea.value = htmltocode(this.getHTML());
			this._iframe.style.display = "none";
			this._textArea.style.display = "block";
			is_ie?this._textArea.style.width = "100%":0;
			break;
	    case "wysiwyg":
			if (this._htmlArea == null && !IsElement('htmlarea')) {
				this.initIframe();
			}
			if (!is_ie) {
				this._doc.designMode = "off";
			}
			var bodyy = this._doc.getElementsByTagName("body")[0];
			bodyy.innerHTML = codetohtml(this.getHTML()); //Modify

			this._textArea.style.display = "none";
			this._iframe.style.display = "block";
			if (!is_ie) {
				this._doc.designMode = "on";
			}
			break;
	    default:
			alert("Mode <" + mode + "> not defined!");
			return false;
	}
	this._selRange = null;
	this._editMode = mode;
	this.gotoEnd();
};

WYSIWYD.prototype.forceRedraw = function() {
	this._doc.body.style.visibility = "hidden";
	this._doc.body.style.visibility = "visible";
};

WYSIWYD.prototype.focusEditor = function() {
	switch (this._editMode) {
	    case "wysiwyg" : this._iframe.contentWindow.focus(); break;
	    case "textmode": this._textArea.focus(); break;
	    default : alert("ERROR: mode " + this._editMode + " is not defined");
	}
	return this._doc;
};
WYSIWYD.prototype.gotoEnd = function() {
	this.focusEditor();
	switch (this._editMode) {
	    case "wysiwyg" : this._iframe.contentWindow.document.body.innerHTML += ''; break;
	    case "textmode": this._textArea.value += ''; break;
	}
} ;
WYSIWYD.prototype.updateToolbar = function(noStatus) {
	var doc = this._doc;
	var iftext = (this._editMode == "textmode");
	for (var i in this._toolbarObjects) {
		if (i == 'event') {
			continue;
		}
		var btn = this._toolbarObjects[i];
		var cmd = i;

		btn.state("enabled",(!iftext || btn.text));
		if (typeof cmd == "function") {
			continue;
		}
		switch(cmd) {
			case "fontname":
			case "fontsize":
			case "formatblock":
				var options = this.config[cmd];
				if (iftext) {
					if (btn.element.innerHTML != options['default']) btn.element.innerHTML = options['default'];
				} else {
					try {
						var value = ("" + doc.queryCommandValue(cmd)).toLowerCase();
						if (!value || value == 'null') break;
						if (cmd == 'fontsize') value = sizeChange(value);
						//alert(value);
						for (var j in options) {
							if (j != 'default' && j != 'event' && j != btn.element.innerHTML &&
								(j.toLowerCase() == value || options[j].substr(0,value.length).toLowerCase() == value)) {
								btn.element.innerHTML = j;
								break;
							}
						}
					} catch(e) {};
				}
				break;
			//case "htmlmode": btn.state("active", !iftext);break;
			//case "windcode": btn.state("active", iftext);break;
			case 'windcode': getObj('wy_windcode').checked = iftext ? true : false;break;
			default:
				try{
					btn.state("active",(!iftext && btn.mover && doc.queryCommandState(cmd)));
				} catch (e) {}
		}
	}
};
WYSIWYD.prototype.insertNodeAtSelection = function(toBeInserted) {
	if (!is_ie) {
		var sel = this._getSelection();
		var range = this._createRange(sel);
		sel.removeAllRanges();
		range.deleteContents();
		var node = range.startContainer;
		var pos = range.startOffset;
		switch(node.nodeType) {
		    case 3:
			if (toBeInserted.nodeType == 3) {
				node.insertData(pos, toBeInserted.data);
				range = this._createRange();
				range.setEnd(node, pos + toBeInserted.length);
				range.setStart(node, pos + toBeInserted.length);
				sel.addRange(range);
				
			} else {
				node = node.splitText(pos);
				var selnode = toBeInserted;
				if (toBeInserted.nodeType == 11) {
					selnode = selnode.firstChild;
				}
				
				node.parentNode.insertBefore(toBeInserted, node);
				this.selectNodeContents(selnode);
				this.updateToolbar();
			}
			break;
		    case 1:
			var selnode = toBeInserted;
			if (toBeInserted.nodeType == 11) {
				selnode = selnode.firstChild;
			}
			node.insertBefore(toBeInserted, node.childNodes[pos]);
			this.selectNodeContents(selnode);
			this.updateToolbar();
			break;
		}
	} else {
		return null;
	}
};
WYSIWYD.prototype.getParentElement = function() {
	var sel = this._getSelection();
	var range = this._createRange(sel);
	if (is_ie) {
		switch(sel.type) {
		    case "Text":
		    case "None":
			return range.parentElement();
		    case "Control":
			return range.item(0);
		    default:
			return this._doc.body;
		}
	} else try{
		var p = range.commonAncestorContainer;
		if (!range.collapsed && range.startContainer == range.endContainer &&
		    range.startOffset - range.endOffset <= 1 && range.startContainer.hasChildNodes())
			p = range.startContainer.childNodes[range.startOffset];
		while (p.nodeType == 3) {
			p = p.parentNode;
		}
		return p;
	} catch (e) {
		return null;
	}
};
WYSIWYD.prototype.selectNodeContents = function(node, pos) {
	this.focusEditor();
	this.forceRedraw();
	var range;
	var collapsed = (typeof pos != "undefined");
	if (is_ie) {
		range = this._doc.body.createTextRange();
		range.moveToElementText(node);
		(collapsed) && range.collapse(pos);
		range.select();
	} else {
		var sel = this._getSelection();
		range = this._doc.createRange();
		range.selectNodeContents(node);
		
		//(collapsed) && range.collapse(pos);
		range.collapse(false);
		sel.removeAllRanges();
		sel.addRange(range);
	}
};
WYSIWYD.prototype.GetSelectedValue = function(cmdID,value) {
	this.focusEditor();
	if (this._editMode == "textmode") {
		windselect(cmdID,value);
	} else {
		this._comboSelected(cmdID,value);
	}
	this.updateToolbar();
	closep();
	return false;
} ;
WYSIWYD.prototype._comboSelected = function(cmdID,value) {
	switch(cmdID) {
	    case "fontname":
	    case "fontsize": this._doc.execCommand(cmdID, false, value); break;
	    case "formatblock":
			(is_ie) && (value = "<" + value + ">");
			this._doc.execCommand(cmdID, false, value);
			break;
	}
};
WYSIWYD.prototype.execCommand = function(cmdID, UI, param) {
	cmdID = cmdID.toLowerCase();
	switch(cmdID) {
		//case "htmlmode" : break;
		case "windcode" : this.setMode(); break;
		case "undo":
		case "redo":
			this._doc.execCommand(cmdID, UI, param); break;
		case "cut":
		case "copy":
		case "paste":
			try{this._doc.execCommand(cmdID, UI, param);}
			catch(e) {}
			break;
		default : this._doc.execCommand(cmdID, UI, param);
	}
	return true;
};
WYSIWYD.prototype._editorEvent = function(ev) {
	var editor = this;
	var keyEvent = (is_ie && ev.type == "keydown") || (ev.type == "keypress");
	if (editor._timerToolbar) {
		clearTimeout(editor._timerToolbar);
	}
	editor._timerToolbar = setTimeout(function() {
		editor.updateToolbar();
		editor._timerToolbar = null;
	}, 50);
};
WYSIWYD.prototype.getHTML = function() {
	switch (this._editMode) {
	    case "wysiwyg"  : return WYSIWYD.getHTML(this._doc.body, false, this);
	    case "textmode" : return this._textArea.value;
	    default : alert("Mode <" + mode + "> not defined!");
	}
	return false;
};
WYSIWYD.prototype._getSelection = function() {
	if (is_ie) {
		return this._doc.selection;
	} else {
		return this._iframe.contentWindow.getSelection();
	}
};
WYSIWYD.prototype._createRange = function(sel) {
	if (is_ie) {
		return sel.createRange();
	} else {
		this.focusEditor();
		if (typeof sel != "undefined") {
			try{
				return sel.getRangeAt(0);
			} catch(e) {
				return this._doc.createRange();
			}
		} else {
			return this._doc.createRange();
		}
	}
};
WYSIWYD._addEvent = function(el, evname, func) {
	if (el.attachEvent) {
		el.attachEvent("on" + evname, func);
	} else {
		el.addEventListener(evname, func, true);
	}
};
WYSIWYD._addEvents = function(el, evs, func) {
	for (var i = 0; i < evs.length; i++) {
		WYSIWYD._addEvent(el, evs[i], func);
	}
};
WYSIWYD._removeEvent = function(el, evname, func) {
	if (el.detachEvent) {
		el.detachEvent("on" + evname, func);
	} else {
		el.removeEventListener(evname, func, true);
	}
};
WYSIWYD._stopEvent = function(ev) {
	if (is_ie) {
		ev.cancelBubble = true;  //取消气泡事件，使得事件不再向上传递
		ev.returnValue = false;
	} else {
		ev.preventDefault();
		ev.stopPropagation();
	}
};
WYSIWYD._removeClass = function(el, className) {
	if (!(el && el.className)) {
		return;
	}
	var cls = el.className.split(" ");
	var ar = new Array();
	for (var i = cls.length; i > 0;) {
		if (cls[--i] != className) {
			ar[ar.length] = cls[i];
		}
	}
	el.className = ar.join(" ");
};
WYSIWYD._addClass = function(el, className) {
	WYSIWYD._removeClass(el, className);
	el.className += " " + className;
};

WYSIWYD.isBlockElement = function(el) {
	var blockTags = " body form textarea fieldset ul ol dl li div " +
		"p h1 h2 h3 h4 h5 h6 quote pre table thead " +
		"tbody tfoot tr td iframe address ";
	return (blockTags.indexOf(" " + el.tagName.toLowerCase() + " ") != -1);
};
WYSIWYD.needsClosingTag = function(el) {
	var closingTags = " head script style div span tr td tbody table em strong font a title ";
	return (closingTags.indexOf(" " + el.tagName.toLowerCase() + " ") != -1);
};
WYSIWYD.htmlEncode = function(str) {
	str = str.replace(/&/ig, "&amp;");
	str = str.replace(/</ig, "&lt;");
	str = str.replace(/>/ig, "&gt;");
	str = str.replace(/\x22/ig, "&quot;");
	return str;
};
WYSIWYD.getHTML = function(root, outputRoot, editor) {
	return root.innerHTML;
};
String.prototype.trim = function() {
	a = this.replace(/^\s+/, '');
	return a.replace(/\s+$/, '');
};
WYSIWYD._makeColor = function(v) {
	if (typeof v != "number") {
		return v;
	}
	var r = v & 0xFF;
	var g = (v >> 8) & 0xFF;
	var b = (v >> 16) & 0xFF;
	return "rgb(" + r + "," + g + "," + b + ")";
};
WYSIWYD._colorToRgb = function(v) {
	if (!v) return '';
	function hex(d) {
		return (d < 16) ? ("0" + d.toString(16)) : d.toString(16);
	}
	if (typeof v == "number") {
		var r = v & 0xFF;
		var g = (v >> 8) & 0xFF;
		var b = (v >> 16) & 0xFF;
		return "#" + hex(r) + hex(g) + hex(b);
	}
	if (v.substr(0, 3) == "rgb") {
		var re = /rgb\s*\(\s*([0-9]+)\s*,\s*([0-9]+)\s*,\s*([0-9]+)\s*\)/;
		if (v.match(re)) {
			var r = parseInt(RegExp.$1);
			var g = parseInt(RegExp.$2);
			var b = parseInt(RegExp.$3);
			return "#" + hex(r) + hex(g) + hex(b);
		}
		return null;
	}
	if (v.substr(0, 1) == "#") {
		return v;
	}
	return null;
};
WYSIWYD.getElementById = function(tag, id) {
	var el, i, objs = document.getElementsByTagName(tag);
	for (i = objs.length; --i >= 0 && (el = objs[i]);)
		if (el.id == id)
			return el;
	return null;
};

WYSIWYD.prototype.insertHTML = function(html) {
	if (is_ie) {
		var rng = this._doc.selection.createRange();
		rng.pasteHTML(html);
	} else {
		var fragment = this._doc.createDocumentFragment();
		var div = this._doc.createElement("div");
		div.innerHTML = html;
		
		while (div.firstChild) {
			fragment.appendChild(div.firstChild);
		}
		var node = this.insertNodeAtSelection(fragment);
	}
};
WYSIWYD.prototype.saveRange = function() {
	if (is_ie) {
		this.focusEditor();
		if (editor._editMode == "wysiwyg") {
			this._selRange = this._createRange(this._getSelection());
		} else {
			this._selRange = document.selection.createRange();
		}
	}
};
WYSIWYD.prototype.restoreRange = function() {
	this.focusEditor();
	if (this._selRange) {
		this._selRange.select();
		this._selRange = null;
	}
};
WYSIWYD.prototype.clearRange = function() {
	this._selRange = null;
}
function editorcode(cmdID) {
	editor.focusEditor();
	if (editor._editMode == "textmode") {
		windcode(cmdID);
	} else {
		editor.execCommand(cmdID,false);
	}
	editor.updateToolbar();
};
function imgIframeAutoHeight()
{
	var iframe = getObj("imgIframe");
	try{
	var bHeight = iframe.contentWindow.document.body.scrollHeight;
	var dHeight = iframe.contentWindow.document.documentElement.scrollHeight;
	var height = Math.max(bHeight, dHeight);
	iframe.height = height;
	}catch(e){}
}
function insertImage(cmdID) {
	editor.saveRange();
	var menu_editor = getObj("menu_editor");
	menu_editor.className = "";
	var iframeW = 452;
	var iframeH = 350;
	menu_editor.innerHTML = '<div id="J_photoInsert"><div class="popout"><table border="0" cellspacing="0" cellpadding="0"><tbody><tr><td class="bgcorner1"></td><td class="pobg1"></td><td class="bgcorner2"></td></tr><tr><td class="pobg4"></td><td style="padding:0;"><div class="popoutContent"><iframe id="imgIframe" scrolling="no" style="zoom:1" frameborder="0" src="job.php?action=pweditor" width="'+iframeW+'" height="'+iframeH+'"></iframe></div></td><td class="pobg2"></td></tr><tr><td class="bgcorner4"></td><td class="pobg3"></td><td class="bgcorner3"></td></tr></tbody></table></div></div>';
/*'<div style="width:340px;"><h4><div class="fr" style="cursor:pointer;" onclick="closep();" title="'+I18N['close']+'"><img src='+imgpath+'/close.gif></div>'+I18N['insertImage']+'</h4><table width="100%"><tbody><tr><td width="25%">'+I18N['mediaurl']+'</td><td><input class="input" type="text" id="mediaurl" size="32" /></td></tr><tr><td> </td><td><input class="btn" type="button" onclick="return insertImageToPage();" value="'+I18N['submit']+'" /></td></tr></tbody></table></div>';*/
	read.open('menu_editor','wy_insertimage','2');
	getObj("imgIframe").src = "job.php?action=pweditor&t=" + new Date().getTime();
	//getObj("mediaurl").focus();
	//setInterval("imgIframeAutoHeight()", 200);
};


function insertImageToPage (txt) {
	editor.restoreRange();
	if (txt) {
		if (editor._editMode == "textmode") {
			sm="[img]"+txt+"[/img]";
			AddText(sm,'');
		} else {
			if(is_ie){editor.insertHTML("<img src=\""+txt+"\" />");}else
			{editor._doc.execCommand("insertimage",false,txt);}
		}
	} else {
		return alert(I18N['mediaurl_empty'])
	}
	closep();
};

function insertMusic(cmdID) {
	editor.saveRange();
	var menu_editor = getObj("menu_editor");
	menu_editor.innerHTML = '<div style="width:520px;"><h4 style="cursor:move;" onmousedown="read.move(event);"><div class="fr" style="cursor:pointer;" onclick="closep();" title="'+I18N['close']+'"><img src='+imgpath+'/close.gif></div>'+I18N['Musicinsert']+'</h4><table width="100%"><tbody><tr><td><input type="text" id="xiami_music" name="keyword" style="width:430px;" onfocus="inputFocus(this);" class="input" maxlength=28/> <input type="button" onclick="getMusic(1);" value="'+I18N['Musicsearch']+'" class="btn" /></td></tr><tr><td><div id="music_list">'+I18N['Musicdesc']+'</div></td></tr></tbody></table></div>';
	read.open('menu_editor','wy_music','2',22);
	getObj("xiami_music").focus();
};

function inputFocus(e){
	e.value = '';
}
function getMusic(page) {
	var keyword = getObj('xiami_music').value;
	if (keyword == '') {
		alert(I18N['Musickeyword']);
		getObj("xiami_music").focus();
		return false;
	}
	ajax.send('apps.php?q=music&ajax=1','page='+page+'&keyword='+keyword,function(){
		var rText = ajax.request.responseText.split('\t');
		
		if (rText[0] == 'success') {
			getObj('music_list').innerHTML = rText[1];
		} else if (rText[0] == 'close') {
			showDialog('error',I18N['Musiceditor']);
		} else {
			showDialog('error','The insert is error');
		}
	});
}

function insert_xiami_music(id){
	var music_info = document.getElementById(id).value;
	if (music_info != ""){
		var info_array		= music_info.split("&");
		var music_id		= info_array[0];
		var music_name		= info_array[1];
		var music_ubb_tag	= "[music=" + music_id + "]" + music_name + "[/music]" ;
		AddCode(music_ubb_tag, "");
		closep();
	}
}

function showTable(cmdID) {
	if (editor._editMode == "textmode") return false;
	editor.saveRange();
	var menu_editor = getObj("menu_editor");
	menu_editor.innerHTML = '<div style="width:280px;"><h4><div class="fr" style="cursor:pointer;" onclick="closep();" title="'+I18N['close']+'"><img src='+imgpath+'/close.gif></div>'+I18N['inserttable']+'</h4><table width="100%"><tbody><tr><td width="40%">'+I18N['tablerows']+'</td><td><input class="input" type="text" name="rows" id="f_rows" size="5" value="2" /></td></tr><tr><td>'+I18N['tablecols']+'</td><td><input class="input" type="text" name="cols" id="f_cols" size="5" value="4" /></td></tr><tr><td>'+I18N['tablewidth']+'</td><td><input class="input" type="text" name="width" id="f_width" size="5" value="100" /></td></tr><tr><td>'+I18N['tableunit']+'</td><td><input type="radio" name="unit" id="f_unit1" value="%" checked> 百分比 <input type="radio" name="unit" id="f_unit1" value="px"> 像素</td></tr><tr><td> </td><td><input class="btn" type="button" onclick="return insertTable();" value="'+I18N['submit']+'" /></td></tr></tbody></table></div>';
	read.open('menu_editor','wy_inserttable','2');
};
function insertTable() {
	editor.restoreRange();
	var sel = editor._getSelection();
	var range = editor._createRange(sel);
	var fields = ["f_rows", "f_cols", "f_width"];
	var param = new Object();
	for (var i = 0; i < fields.length; i++) {
		var id = fields[i];
		param[id] = getObj(id).value;
	}
	param['f_unit'] = getObj("f_unit1").checked == true ? '%' : 'px';
	var doc = editor._doc;
	var table = doc.createElement("table");
	table.style.width = parseInt(param['f_width']) + param["f_unit"];
	table.className = 't';
	var tbody = doc.createElement("tbody");
	table.appendChild(tbody);
	for (var i = 0; i < param["f_rows"]; ++i) {
		var tr = doc.createElement("tr");
		tbody.appendChild(tr);
		for (var j = 0; j < param["f_cols"]; ++j) {
			var td = doc.createElement("td");
			tr.appendChild(td);
			(is_gecko) && td.appendChild(doc.createElement("br"));
		}
	}
	if (is_ie) {
		range.pasteHTML(table.outerHTML);
	} else {
		editor.insertNodeAtSelection(table);
	}
	closep();
};
function showcolor(cmdID) {
	var menu_editor = getObj("menu_editor");
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
		html += "<div unselectable=\"on\" style=\"background:#" + colors[i] + "\" onClick=\"SetC('" + colors[i] + "','" + cmdID + "')\"></div>";
	}
	html += '</div>';
	menu_editor.innerHTML = html;
	read.open('menu_editor','wy_' + cmdID);
};
function SetC(color,cmdID) {
	editor.focusEditor();
	if (editor._editMode=='textmode') {
		text = editor.getsel();
		var ctype = cmdID == 'forecolor' ? 'color' : 'backcolor';
		AddText("[" + ctype + "=#" + color + "]" + text + "[/" + ctype + "]",text);
	} else {
		if (cmdID == 'hilitecolor' && is_ie) cmdID = "backcolor";
		editor._doc.execCommand(cmdID, false, "#" + color);
	}
	closep();
};
function showJustify(cmdID) {
	var menu_editor = getObj('menu_editor');
	menu_editor.className = 'wy_menu_B';
	menu_editor.innerHTML = "<ul style=\"width:80px;margin:0 0 2px 10px;line-height:22px;\"><li><a unselectable=\"on\" href=\"javascript:\" onclick=\"return setJustify('left');\">左对齐</a></li><li><a unselectable=\"on\" href=\"javascript:\" onclick=\"return setJustify('center');\">居中</a></li><li><a unselectable=\"on\" href=\"javascript:\" onclick=\"return setJustify('right');\">右对齐</a></li><li><a unselectable=\"on\" href=\"javascript:\" onclick=\"return setJustify('full');\">左右平等</a></li></ul>";
	read.open('menu_editor','wy_' + cmdID);
};
function setJustify(cmdID) {
	editorcode('justify' + cmdID);
	closep();
	return false;
};
function showList(cmdID) {
	var menu_editor = getObj('menu_editor');
	menu_editor.className = 'wy_menu_B';
	menu_editor.innerHTML = "<ul style=\"width:80px;margin:0 0 2px 10px;line-height:22px;\"><li><a unselectable=\"on\" href=\"javascript:\" onclick=\"return sendMenuCmd('insertorderedlist');\">有序列表</a></li><li><a unselectable=\"on\" href=\"javascript:\" onclick=\"return sendMenuCmd('insertunorderedlist');\">无序列表</a></li></ul>";
	read.open('menu_editor','wy_' + cmdID);
};
function showDent(cmdID) {
	var menu_editor = getObj('menu_editor');
	menu_editor.className = 'wy_menu_B';
	menu_editor.innerHTML = "<ul style=\"width:80px;margin:0 0 2px 10px;line-height:22px;\"><li><a unselectable=\"on\" href=\"javascript:\" onclick=\"return sendMenuCmd('indent');\">缩进</a></li><li><a unselectable=\"on\" href=\"javascript:\" onclick=\"return sendMenuCmd('outdent');\">取消缩进</a></li></ul>";
	read.open('menu_editor','wy_' + cmdID);
};
function sendMenuCmd(cmdID) {
	editorcode(cmdID);
	closep();
	return false;
};
function ShowSelect(cmdID) {
	var menu_editor = getObj("menu_editor");
	menu_editor.className = 'wy_menu_B';
	var wh = {'fontname' : '110','fontsize' : '60','formatblock' : '80'};
	var html = '<ul style="width:'+wh[cmdID]+'px;margin:0 0 2px 10px;line-height:22px;">';
	var options = editor.config[cmdID];
	for (var i in options) {
		if (i != 'default' && i != 'event') {
			html += "<li"+(cmdID=='fontname' ? ' style="font-family:'+options[i]+';"' : '')+"><a style=\"display:block\" unselectable=\"on\" href=\"javascript:\" style=\"font-size:"+i+"ex ;line-height:100%\" onclick=\"return editor.GetSelectedValue('"+cmdID+"','"+options[i]+"');\">"+i+"</a></li>";
		}
	}
	html += '</ul>';
	menu_editor.innerHTML = html;
	read.open('menu_editor','wy_' + cmdID);
};
function rming(cmdID) {
	editor.saveRange();
	var menu_editor = getObj("menu_editor");
	menu_editor.className = "wy_menu_B";
	menu_editor.innerHTML = '<div style="width:320px;"><h4><div class="fr" style="cursor:pointer;" onclick="closep();" title="'+I18N['close']+'"><img src='+imgpath+'/close.gif></div>'+I18N['insertmedia']+'</h4><table width="100%"><tbody><tr><td width="25%">'+I18N['mediaurl']+'</td><td><input class="input" type="text" id="mediaurl" size="32" /></td></tr><tr><td>'+I18N['mediatype']+'</td><td><input type="radio" name="mediatype" id="mediatype1" value="1"> rm <input type="radio" name="mediatype" id="mediatype2" value="2"> wmv <input type="radio" name="mediatype" id="mediatype3" value="3"> mp3 <input type="radio" name="mediatype" id="mediatype4" value="4" checked> flash</td></tr><tr><td>'+I18N['medialength']+'</td><td><input class="input" type="text" id="medialength" value="314" size="6" />&nbsp;'+I18N['mediawidth']+'&nbsp;&nbsp;<input class="input" type="text" id="mediawidth" value="256" size="6" />&nbsp;'+I18N['mediaheight']+'</td></tr><tr><td>'+I18N['mediaplay']+'</td><td><input type="checkbox" id="midiaauto" />'+I18N['mediaauto']+'</td></tr><tr><td> </td><td><input class="btn" type="button" onclick="return insertmedia();" value="'+I18N['submit']+'" /></td></tr></tbody></table></div>';
	read.open('menu_editor','wy_media','2');
	getObj("mediaurl").focus();
};
function insertmedia() {
	editor.restoreRange();
	var url = getObj("mediaurl").value;
	if (url == '') {
		alert(I18N['mediaurl_empty']);
		return false;
	}
	var mediatype = 2;
	for (var i=1;i<5;i++) {
		if (getObj("mediatype"+i).checked == true) {
			mediatype = i;
			break;
		}
	}
	url=encodeURI(url);
	var code   = '';
	var length = getObj("medialength").value;
	var width  = getObj("mediawidth").value;
	var auto   = getObj("midiaauto").checked == true ? 1 : 0;
	switch (mediatype) {
		case 1: code = '[rm=' + length + ',' + width + ',' + auto + ']' + url + '[/rm]';break;
		case 2: code = '[wmv=' + length + ',' + width + ',' + auto + ']' + url + '[/wmv]';break;
		case 3: code = '[wmv=' + auto + ']'  + url + '[/wmv]';break;
		case 4: code = '[flash=' + length + ',' + width + ',' + auto + ']' + url + '[/flash]';break;
	}
	AddCode(code,'');
	closep();
};
function code(cmdID) {
	editor.focusEditor();
	text = editor.getsel();
	sm = '['+cmdID+']'+text+'[/'+cmdID+']';
	AddCode(sm,text);
};
function quoteme() {
	editor.focusEditor();
	text = editor.getsel();
	if (editor._editMode=='wysiwyg') {
		text = htmltocode(text);
		sm = "[quote]"+text+"[/quote]";
		sm = codetohtml(sm);
	} else {
		sm = "[quote]"+text+"[/quote]";
	}
	AddCode(sm,text);
};

function sell(cmdID) {
	editor.saveRange();
	//editor.focusEditor();
	var menu_editor = getObj("menu_editor");
	menu_editor.className = "wy_menu_B";
	editor.sellNum = editor.sellNum||5;
	var n = editor.getHTML().match(/\[sell=[\d]+(,[\w]+)?]/ig);
	n = (n==null)?0:n.length;
	menu_editor.innerHTML = '<div style="width:290px;"><h4><a href="javascript:;" onclick="closep();" class="adel">关闭</a>帖子出售</h4><div style="padding:5px 30px;"><p class="f14 mb10">共有 <span class="s2">'+(n+1)+'</span> 处出售内容 售价不能大于:<font color="blue">' + sellprice + '</font></p><p class="f14"><span class="fl" style="line-height:22px;">帖子售价：</span><div class="fl mr5 f12"><input name="" type="text" class="input fl" style="width:50px;" value="'+editor.sellNum+'" /><a href="javascript:;" class="select_arrow fl" style="margin:2px 0 0 2px;" onclick="sell_dropdown()" >下拉</a><div class="fl"><div id="sell_dropdown" class="pw_menu" style="position:absolute;margin:20px 0 0 -17px;display:none;"><ul class="menuList tal" style="width:40px;" onmousedown="getSellValue(event)"><li><a href="javascript:;">1</a></li><li><a href="javascript:;">3</a></li><li><a href="javascript:;">5</a></li><li><a href="javascript:;">7</a></li><li><a href="javascript:;">9</a></li></ul></div></div></div><span id="sell_credit_span"></span><div class="c"></div></p></div><div class="p10 tac"><span class="btn2"><span><button onclick="insertSell()" type="button">确定</button></span></span><span class="bt2"><span><button type="button" onclick="closep()">取消</button></span></span></div></div>';
	read.open('menu_editor','wy_sell','2');
	if (IsElement('attmode_2')) {
		var s = getObj('attmode_2').cloneNode(true);
		s.id = 'sell_credit';s.name = '';
		s.style.width = '70px';
		getObj('sell_credit_span').appendChild(s);
	}
};
function insertSell() {
	editor.restoreRange();
	var text = editor.getsel();
	var txt = getObj('pw_box').getElementsByTagName('input')[0].value;
	if (txt != null) {
		editor.sellNum = txt;
		var s = getObj('pw_box').getElementsByTagName('select')[0];
		if (s != null && s.value != 'money') {
			txt += ',' + s.value;
			editor.sellCredit = s.value;
		} else if (editor.sellCredit) {
			txt += ',' + editor.sellCredit;
		}
		sm = "[sell="+txt+"]"+text+"[/sell]";
		AddCode(sm,text);
		closep();
	}
}
function sell_dropdown() {
	getObj('sell_dropdown').style.display='';
	document.body.onmousedown=function(){
		getObj('sell_dropdown').style.display='none';
		document.body.onmousedown=null;
	};
}
function br() {
	editor.focusEditor();
	if (editor._editMode == "textmode") {
		return false;
	} else {
		sm="<br />";
		editor.insertHTML(sm);
	}
};
function getSellValue(e){
	var e = e || event;
	var elem=e.target||e.srcElement;
	if(elem.tagName=='A')
	{
		getObj('pw_box').getElementsByTagName('input')[0].value = elem.innerHTML;
	}
};
function showsale(cmdID) {
	editor.saveRange();
	var menu_editor = getObj("menu_editor");
	menu_editor.innerHTML = '<div style="width:300px;"><h4><div class="fr" style="cursor:pointer;" onclick="closep();" title="'+I18N['close']+'"><img src='+imgpath+'/close.gif></div>'+I18N['showsale']+'</h4><table width="100%"><tr><td width="25%">'+I18N['seller']+'</td><td><input class="input" id="seller" size="30" /></td></tr><tr><td>'+I18N['salename']+'</td><td><input class="input" id="subject" size="30" /></td></tr><tr><td> '+I18N['saleprice']+'</td><td><input class="input" id="price" size="7" /></td></tr><tr><td>'+I18N['saledes']+'</td><td><textarea id="saledes" rows="4" cols="33"></textarea></td></tr><tr><td>'+I18N['demo']+'</td><td><input class="input" id="demo" size="30" /></td></tr><tr><td>'+I18N['contact']+'</td><td><input class="input" id="contact" size="30" /></td></tr><tr><td>'+I18N['md']+'</td><td><input type="radio" name="md" value="4" onclick="setmethod(4);" checked />'+I18N['salemoney4']+'<input type="radio" name="md" value="2" onclick="setmethod(2);" />'+I18N['salemoney2']+'&nbsp;<input type="radio" name="md" value="1" onclick="setmethod(1);" />'+I18N['salemoney1']+'</td></tr><tbody id="setmethod" style="display:none"><tr><td>'+I18N['transport']+'</td><td><input type="radio" value="1" name="transport" onclick="setfee(true)" checked /> '+I18N['transport1']+'&nbsp;&nbsp; <input type="radio" value="2" name="transport" onclick="setfee(false)" /> '+I18N['transport2']+'<br /><input type="hidden" value="3" />'+I18N['transport3']+'&nbsp;<input class="input" disabled size="2" id="ordinary_fee" /> &nbsp;&nbsp; '+I18N['transport4']+'&nbsp;<input class="input" disabled size="2" id="express_fee" /> '+I18N['yuan']+'</td></tr><tr><td> </td><td><input class="btn" type="button" onclick="return insertsale();" value="'+I18N['submit']+'" /></td></tr></tbody></table></div>';
	read.open('menu_editor','wy_sale','2');
};
function setfee(type) {
	getObj("ordinary_fee").disabled = type;
	getObj("express_fee").disabled = type;
};
function setmethod(mode) {
	method = mode;
	obj = getObj("setmethod");
	obj.style.display = mode==2 ? "" : "none";
};
function insertsale() {
	editor.restoreRange();
	var required = {
		"seller": I18N['seller_empty'],
		"subject": I18N['subject_empty'],
		"price": I18N['price_empty']
	};
	for (var i in required) {
		var el = getObj(i);
		if (!el.value) {
			alert(required[i]);
			el.focus();
			return false;
		}
	}
	var code  = '[payto]';
	code += '(seller)' + getObj("seller").value + '(/seller)';
	code += '(subject)' + getObj("subject").value + '(/subject)';
	code += '(body)' + getObj("saledes").value + '(/body)';
	code += '(price)' + getObj("price").value + '(/price)';
	code += '(ordinary_fee)' + getObj("ordinary_fee").value + '(/ordinary_fee)';
	code += '(express_fee)' + getObj("express_fee").value + '(/express_fee)';
	code += '(demo)' + getObj("demo").value + '(/demo)';
	code += '(contact)' + getObj("contact").value + '(/contact)';
	code += '(method)' + method + '(/method)';
	code += '[/payto]';
	AddCode(code,'');
	closep();
};
function showcreatelink(cmdID){
	editor.saveRange();
	text = editor.getsel();
	text = text.replace(/</ig,'&lt;');
	text = text.replace(/>/ig,'&gt;');
	text = text.replace(/\"/ig,'&quot;');

	var menu_editor = getObj("menu_editor");
	menu_editor.className = 'wy_menu_B';
	menu_editor.innerHTML = '<div style="width:380px;"><h4><div class="fr" style="cursor:pointer;" onclick="closep();" title="'+I18N['close']+'"><img src='+imgpath+'/close.gif></div>'+I18N['showcreatelink']+'</h4><table width="100%"><tbody id="linkmode" style="display:none;"><tr><td><input class="input" id="linkdiscrip" size="20" style="width:120px" value="" /></td><td><input class="input" id="linkaddress" value="http://" size="35" style="width:190px" /></td></tr></tbody><tr><td width="40%">'+I18N['linkdiscrip']+'</td><td><a style="margin-right:10px;" class="fr" href="javascript:;" hidefocus="true" onclick="editor.addlink();">'+I18N['addlink']+'</a><span>'+I18N['linkaddress']+'</span></td></tr><tr><td><input class="input" id="linkdiscrip1" size="20" style="width:120px" value="'+text+'" /></td><td><input class="input" id="linkaddress1" value="http://" size="35" style="width:190px" /></td></tr><tbody id="linkbody"></tbody><tr><td>'+I18N['linktype']+'</td><td><input type="radio" name="linktype" value="0" checked/>&nbsp;'+I18N['no']+'&nbsp;<input type="radio" name="linktype" value="1" />&nbsp;'+I18N['yes']+'</td></tr><tr><td>&nbsp;</td><td><input class="btn" type="button" onclick="return editor.insertlink();" value="'+I18N['submit']+'" /></td></tr></table></div>';
	read.open('menu_editor','wy_createlink','2');
};
function showEmotion() {
	editor.saveRange();
	showDefault();
	document.body.onmousedown=null;
	document.body.onclick=null;
	setTimeout(function(){
	document.getElementById('pw_box').onclick=function(e)
	{
		if ( e && e.preventDefault )
			e.stopPropagation();
		else
			window.event.cancelBubble = true;
	};
	document.body.onclick=function(){
		closep();
		document.body.onclick=null;
		document.getElementById('pw_box').onclick=null;
	};},100);
	return false;
};
function showMagic() {
	showMagicDefault();
	return false;
}
WYSIWYD.prototype.addlink = function(){
	var s = getObj('linkmode').firstChild.cloneNode(true);
	var linknum = ++this._linknum;
	var tags = s.getElementsByTagName('input');
	for (var i=0;i<tags.length;i++) {
		if (tags[i].id == 'linkdiscrip') {
			tags[i].id = 'linkdiscrip' + linknum;
		}
		if (tags[i].id == 'linkaddress') {
			tags[i].id = 'linkaddress' + linknum;
		}
	}
	getObj('linkbody').appendChild(s);
};

WYSIWYD.prototype.insertlink = function() {
	this.restoreRange();
	var AddTxt = '';
	var downadd = '';
	var linknum = this._linknum;
	if (document.getElementsByName('linktype')[1].checked == true) {
		downadd = ',1'
	}
	for (var i=1;i<=linknum;i++) {
		if (getObj('linkdiscrip'+i).value.length == 0 && getObj("linkaddress"+i).value == 'http://') continue;
		var linkaddr= getObj("linkaddress"+i).value;
		if(linkaddr.indexOf('http://')<0 && linkaddr.indexOf('ftp://')<0)
			linkaddr = 'http://'+linkaddr;
		if (getObj('linkdiscrip'+i).value){
			AddTxt += "[url=" + encodeURI(linkaddr) + downadd +"]" + getObj("linkdiscrip"+i).value + "[/url]";
		} else {
			AddTxt += "[url=" + encodeURI(linkaddr) + downadd +"]" + linkaddr + "[/url]";
		}
	}
	if (this._editMode == 'wysiwyg') {
		AddTxt = codetohtml(htmltocode(AddTxt));
	}
	AddCode(AddTxt,'');
	this._linknum = 1;
	closep();
};

function windcode(code) {
	text = editor.getsel();
	switch(code) {
		case "windcode": editor.setMode(); return true;
		//case "windcode": return false;
		case "bold": AddTxt = "[b]" + text + "[/b]";break;
		case "italic": AddTxt = "[i]" + text + "[/i]";break;
		case "underline": AddTxt = "[u]" + text + "[/u]";break;
		case "strikethrough": AddTxt = "[strike]" + text + "[/strike]";break;
		case "subscript": AddTxt = "[sub]" + text + "[/sub]";break;
		case "superscript": AddTxt = "[sup]" + text + "[/sup]";break;
		case "justifyleft": AddTxt = "[align=left]" + text + "[/align]";break;
		case "justifycenter": AddTxt = "[align=center]" + text + "[/align]";break;
		case "justifyright": AddTxt = "[align=right]" + text + "[/align]";break;
		case "justifyfull": AddTxt = "[align=justify]" + text + "[/align]";break;
		case "inserthorizontalrule": text='';AddTxt="[hr]";break;
		case "indent": AddTxt = "[blockquote]" + text + "[/blockquote]";break;
		case "createlink":
			if (text) {
				if(text.indexOf('http://')==-1)
					text = 'http://'+text;
				AddTxt = "[url=" + text + "]" + text + "[/url]";
			} else {
				txt = prompt('URL:',"http://");
				if (txt) {
					AddTxt = "[url=" + txt + "]" + txt + "[/url]";
				} else {
					AddTxt = "[url][/url]";
				}
			}
			break;
		case "insertorderedlist":
			if (text) {
				AddTxt = "[list=a][li]" + text + "[/li][/list]";
			} else {
				txt=prompt('a,A,1',"a");
				while (txt!="A" && txt!="a" && txt!="1" && txt!=null) {
					txt=prompt('a,A,1',"a");
				}
				if (txt!=null) {
					if (txt=="1") {
						AddTxt="[list=1]";
					} else if (txt=="a") {
						AddTxt="[list=a]";
					} else if (txt=="A") {
						AddTxt="[list=A]";
					}
					ltxt="1";
					while (ltxt!="" && ltxt!=null) {
						ltxt=prompt(I18N['listitem'],"");
						if (ltxt!="") {
							AddTxt+="[li]"+ltxt+"[/li]";
						}
					}
					AddTxt+="[/list]";
				}
			}
			break;
		case "insertunorderedlist":
			if (text) {
				AddTxt = "[list][li]" + text + "[/li][/list]";
			} else {
				AddTxt="[list]";
				txt="1";
				while (txt!="" && txt!=null) {
					txt=prompt(I18N['listitem'],"");
					if (txt!="") {
						AddTxt+="[li]"+txt+"[/li]";
					}
				}
				AddTxt+="[/list]";
			}
			break;
		case 'undo':
		case 'redo':
			try{document.execCommand(code, null, null);}catch(e){}return;
		default : return false;
	}
	AddText(AddTxt,text);
};
function windselect(cmdID,value) {
	text = editor.getsel();
	switch(cmdID) {
	    case "fontname": AddTxt = "[font=" + value + "]" + text + "[/font]";break;
	    case "fontsize": AddTxt = "[size=" + value + "]" + text + "[/size]";break;
		case "formatblock":
			if (value == 'p'){
				AddTxt = "";
			}else{
				value = value.replace(/h(\d)/,'$1');
				value = 7 - value;
				if(text == ''){
					text= ' ';
				}
				AddTxt = value ? "[size=" + value + "][b]" + text + "[/b][/size]" : "";
			}
			break;
		default : AddTxt = "";
	}
	AddText(AddTxt,text);
};
function AddText(code,text) {
	var startpos = text == '' ? code.lastIndexOf(']') + 1 : code.lastIndexOf(text);//modify by superman 2010-08-31 lastIndex
	if (document.selection) {
		var sel = document.selection.createRange();
		sel.text = code.replace(/\r?\n/g, '\r\n');
		sel.moveStart('character',-code.replace(/\r/g,'').length + startpos);
		sel.moveEnd('character', -code.length + text.length + startpos);
		sel.select();
	} else if (typeof editor._textArea.selectionStart != 'undefined') {
		var prepos = editor._textArea.selectionStart;
		editor._textArea.value = editor._textArea.value.substr(0,prepos) + code + editor._textArea.value.substr(editor._textArea.selectionEnd);
		editor._textArea.selectionStart = prepos + startpos;
		editor._textArea.selectionEnd = prepos + startpos + text.length;
	} else {
		document.FORM.atc_content.value += code;
	}
};
function AddCode(code,text) {
	editor.focusEditor();
	if (editor._editMode == 'textmode') {
		AddText(code,text);
	} else {
		editor.insertHTML(code);
		/*if(is_ie){
			var rng = document.selection.createRange();
			sel.moveStart('character',sel.text.length);
		}*/
	}
};
function htmltocode(str) {
	//filter M$ word codes
	str = str.replace(/(\r?\n)/ig, '');
	str = str.replace(/<style>[^<]*<\/style>/ig,'');
	str = str.replace(/<meta[^>]*>/ig,'');
	str = str.replace(/<link[^>]*>/ig,'');
	str = str.replace(/<![--]?[^>]*[--]?>/ig,''); 

	str = str.replace(/<xml>[.\s]*?<\/xml>/mig,'');

	str = str.replace(/<img[^>]*smile=\"(\d+)\"[^>]*>/ig,'[s:$1]');
	str = str.replace(/<img[^>]*type=\"(attachment|upload)\_(\d+)\"[^>]*>/ig,'[$1=$2]');
	if (editor.allowHtml) {
		return str;
	}
	code_htm = new Array();
	
	str = str.replace(/<p[^>\/]*\/>/ig,'\n');
	str = str.replace(/\[code\](.+?)\[\/code\]/ig, function($1, $2) {return phpcode($2);});
	str = str.replace(/\son[\w]{3,16}\s?=\s*([\'\"]).+?\1/ig,'');

	if (editor.allowConvert) {
		str = str.replace(/<hr[^>]*>/ig,'[hr]');
		str = str.replace(/<s>(.*)?<\/s>/ig,'<strike>$1</strike>');
		str = str.replace(/<(sub|sup|u|strike|b|i|pre)(\s[^\/>]*)?>(.*)?<\/(sub|sup|u|strike|b|i|pre)>/ig,function($1,$2,$3,$4){
			var c = '<'+$2+'>';
			if($3)c+='<span'+$3+'>';
			c+=$4;
			if($3)c+='</span>';
			c+='</'+$2+'>';
			return c;
		});
		str = str.replace(/<(sub|sup|u|strike|b|i|pre)(\s[^\/>]*)?>/ig,function(t1,t2){return '['+t2.toLowerCase()+']';});
		str = str.replace(/<\/(sub|sup|u|strike|b|i|pre)>/ig,function(t1,t2){return '[/'+t2.toLowerCase()+']';});
		str = str.replace(/<(\/)?strong>/ig,'[$1b]');
		str = str.replace(/<(\/)?em>/ig,'[$1i]');
		str = str.replace(/<(\/)?blockquote([^>]*)>/ig,'[$1blockquote]');

		str = str.replace(/<img[^>]*src=[\'\"\s]*([^\s\'\"]+)[^>]*>/ig,'[img]'+'$1'+'[/img]');
		str = str.replace(/<a[^>]*href=[\'\"\s]*mailto:([^\s\'\"]*)[^>]*>(.+?)<\/a>/ig,'[email=$1]'+'$2'+'[/email]');
		str = str.replace(/<a[^>]*href=[\'\"\s]*([^\s\'\"]*)[^>]*>(.+?)<\/a>/ig,'[url=$1]'+'$2'+'[/url]');
		str = str.replace(/<h([1-6]+)([^>]*)>(.*?)<\/h\1>/ig,function($1,$2,$3,$4) {return h($3,$4,$2);});

		str = searchtag('table',str,'table',1);
		str = searchtag('font',str,'Font',1);
		str = searchtag('div',str,'ds',1);

		str = searchtag('p',str,'p',1);
		str = searchtag('span',str,'ds',1);
		str = searchtag('ol',str,'list',1);
		str = searchtag('ul',str,'list',1);
	}
	for (var i = 0; i < code_htm.length; i++) {
		str = str.replace("[\twind_phpcode_" + i + "\t]", code_htm[i]);
	}

	str = str.replace(/&nbsp;/ig,' ');
	str = str.replace(/<br[^>]*>/ig,'\n');
	str = str.replace(/<[^>]*?>/ig, '');
	str = str.replace(/&amp;/ig, '&');
	str = str.replace(/&quot;/ig,'"');
	str = str.replace(/&lt;/ig, '<');
	str = str.replace(/&gt;/ig, '>');
	str = str.replace(/\s*?\[(td[^\]]*?|tr[^\]]*?)\]/ig,'[$1]');
	str = str.replace(/\[\/(td|tr)\]\s*/ig,'[/$1]');
	return str;
};
function searchtag(tagname,str,action,type) {
	if (type == 2) {
		var tag = ['[',']'];
	} else {
		var tag = ['<','>'];
	}
	var head = tag[0] + tagname;
	var head_len = head.length;
	var foot = tag[0] + '/' + tagname + tag[1];
	var foot_len = foot.length;
	var strpos = 0;

	do {
		var strlower = str.toLowerCase();
		var strlen = str.length;
		var begin = strlower.lastIndexOf(head, strlen - 1 - strpos);
		if (begin == -1) {
			break;
		}
		for (var i = begin + head_len; i < strlen; i++) {
			if (str.charAt(i) == tag[1]) break;
		}
		if (i>=strlen) break;

		var firsttag = i;
		var style = str.substr(begin + head_len, firsttag - begin - head_len);

		var end = strlower.indexOf(foot, firsttag);
		if (end == -1) break;

		firsttag++;
		var findstr = str.substr(firsttag, end - firsttag);
		var prestr  = str.substr(0,begin);
		if (begin > 0 && (tagname == 'p' || tagname == 'div') && !prestr.match(/<\/(p|div)>/ig)) {
			prestr += '\n';
		}
		str = prestr + eval(action)(style,findstr,tagname) + str.substr(end+foot_len);

		strpos = str.length - begin;

	} while (begin != -1);

	return str;
};
function h(style,code,size) {
	size = 7 - size;
	code = '[size=' + size + '][b]' + code + '[/b][/size]';
	return p(style,code);
};
function p(style,code) {
	if (style.indexOf('align=') != -1) {
		style = findvalue(style,'align=');
		code  = '[align=' + style + ']' + code + '[/align]';
	} else {
		code += "\n";
	}
	return code;
};
function ds(style, code, tagName) {
	var styles = [
		['b' , 0 , 'font-weight:', 'bold', null],
		['i' , 0 , 'font-style:' , 'italic', null],
		['u' , 0 , 'text-decoration:' , 'underline', null],
		['strike' , 0 , 'text-decoration:' , 'line-through', null],
		['backcolor' , 1 , 'background-color:', null, colorChange],
		['color' , 1 , 'color:', null, colorChange],
		['font' , 1 , 'font-family:', null, null],
		['size' , 1 , 'font-size:', null, sizeChange],
		['align' , 1 , 'align=', null, null],
		['align' , 1 , 'text-align:', null, null],
		['blockquote' , 3 , 'margin-left:', 40, null]
	];
	style = style.toLowerCase();

	function colorChange(color) {
		if (color.indexOf('rgb') != -1) {
			color = WYSIWYD._colorToRgb(color);
		}
		return color;
	}
	var newline = false;

	for (var i = 0; i < styles.length; i++) {
		var begin = style.indexOf(styles[i][2]);
		if (begin == -1) {
			continue;
		}
		if (styles[i][0] == 'align') {
			newline = true;
		}
		var value = findvalue(style, styles[i][2]);
		if (styles[i][4] != null) {
			value = styles[i][4](value);
		}
		if (styles[i][1] == 0) {
			if (value.match(styles[i][3])) {
				code = '[' + styles[i][0] + ']' + code + '[/' + styles[i][0] + ']';
			}
		} else if (styles[i][1] == 3) {
			var bqnum = value.match(/([0-9]+)/g) / styles[i][3];
			for (var j = 0; j < bqnum; j++) {
				code = '[' + styles[i][0] + ']' + code + '[/' + styles[i][0] + ']';
			}
		} else {
			code = '[' + styles[i][0] + '=' + value + ']' + code + '[/' + styles[i][0] + ']';
		}
		if (styles[i][0] == 'backcolor') style = style.replace(styles[i][2], '');
	}
	return code + (tagName == 'div' && !newline ? "\n" : '');
};
function sizeChange(size) {
	function rate_arr(unit) {
		switch (unit) {
			case 'px': return [13, 16, 18, 24, 32, 48];
			case 'pt': return [9.5, 12, 14, 18, 24, 36];
			case 'em': return [0.8, 1, 1.2, 1.5, 2, 3];
		}
		return false;
	}
	function size2rate(size, unit) {
		var _a = rate_arr(unit);
		if (_a) {
			for (var i = 0; i < _a.length; i++) {
				if (size < _a[i]) {
					return i + 1;
				}
			}
			return 7;
		}
		return 2;
	}
	var size_arr = {'x-small' : 1, 'small' : 2, 'medium' : 3, 'large' : 4, 'x-large' : 5, 'xx-large' : 6, '-webkit-xxx-large' : 7};

	if (typeof size_arr[size] != 'undefined') {
		size = size_arr[size];
	} else if (size.match(/^(\d+)(px|pt|em)?/)) {
		size = RegExp.$1;
		if (RegExp.$2) {
			size = size2rate(size, RegExp.$2);
		}
	} else {
		size = 2;
	}
	return size;
};
function list(type,code,tagname) {
	code = code.replace(/<(\/)?li>/ig,'[$1li]');
	if (tagname == 'ul') {
		return '[list]'+code+'[/list]';
	}
	if (type && type.indexOf('type=')!='-1') {
		type = findvalue(type,'type=');
		if (type!='a' && type!='A' && type!='1') {
			type='1';
		}
		return '[list=' + type + ']' + code + '[/list]';
	} else {
		return '[list=1]'+code+'[/list]';
	}
};
function Font(style,str) {
	var styles = new Array();

	styles = [
		['size' , 'size='],
		['backcolor' , 'background-color:'],
		['color' , 'color='],
		['color' , 'color:'],
		['font' , 'face='],
		['font' , 'font-family:']
	];
	style = style.toLowerCase();

	for (var i = 0; i < styles.length; i++) {
		var begin = style.indexOf(styles[i][1]);
		if (begin == -1) {
			continue;
		}
		var value = findvalue2(style,styles[i][1]);
		if (in_array(styles[i][0], ['backcolor','color']) && value.indexOf('rgb') != -1) {
			value = WYSIWYD._colorToRgb(value);
		}
		str = '[' + styles[i][0] + '=' + value + ']' + str + '[/' + styles[i][0] + ']';
		if (styles[i][0] == 'backcolor') style = style.replace(styles[i][1], '');
	}
	return str;
};
function table(style,str) {

	str = str.replace(/<tr([^>]*)>/ig,'[tr]');
	str = str.replace(/<\/tr>/ig,'[/tr]');
	str = searchtag('td',str,'td',1);
	str = searchtag('th',str,'td',1);

	var styles = ['width=','width:'];
	style = style.toLowerCase();

	var s = '';
	for (var i = 0; i < styles.length; i++) {
		if (style.indexOf(styles[i]) == -1) {
			continue;
		}
		s = '=' + findvalue(style,styles[i]);
		break;
	}
	var i = s.indexOf(' ');
	if( i > -1 ){
		s = s.slice(0, i);
	}
	return '[table' + s + ']' + str + '[/table]';
};
function td(style,str) {
	if (style == '') {
		return '[td]' + str + '[/td]';
	}

	var colspan = 1;
	var rowspan = 1;
	var width = '';
	var value;

	if (style.indexOf('colspan=') != -1) {
		value = findvalue(style,'colspan=');
		if (value>1) colspan = value;
	}
	if (style.indexOf('rowspan=') != -1) {
		value = findvalue(style,'rowspan=');
		if (value>1) rowspan = value;
	}
	if (style.indexOf('width=') != -1) {
		width = findvalue(style,'width=');
	}
	if (width == '') {
		return (colspan == 1 && rowspan == 1 ? '[td]' : '[td=' + colspan + ',' + rowspan + ']') + str + '[/td]';
	} else {
		return '[td=' + colspan + ',' + rowspan + ',' + width + ']' + str + '[/td]';
	}
};
function findvalue(style,find) {
	var firstpos = style.indexOf(find)+find.length;
	var len = style.length;
	var start = 0;
	for (var i = firstpos; i < len; i++) {
		var t_char = style.charAt(i);
		if (start == 0) {
			if (t_char == '"' || t_char == "'") {
				start = i+1;
			} else if (t_char != ' ') {
				start = i;
			}
			continue;
		}
		if (t_char == '"' || t_char == "'" || t_char == ';') {
			break;
		}
	}
	return style.substr(start,i-start);
}
function findvalue2(style,find) {
	var reg=new RegExp(find+'[\\"\\s]?(rgb\\([^\\(\\)]*?\\)|[^\\"\\s]*)?\\"?\\s*','i');
	//var reg=new RegExp(find+'[\\"\\s]?([^\\"\\s]*|rgb\\([^\\(\\)]*?\\))?\\"?\\s*','i');
	var b = style.match(reg);
	return b[1];
}
function codetohtml(str) {
	code_htm = new Array();
	str = str.replace(/&(?!(#[0-9]+|[a-z]+);)/ig,'&amp;');
	if (!editor.allowHtml) {
		str = str.replace(/</ig,'&lt;');
		str = str.replace(/>/ig,'&gt;');
	} else {
		str = str.replace(/\<([^>]+)\>([^<]+?)\<([^>]+)\>/ig, function($1, $2, $3, $4) {return htmlTagTrim($2, $3, $4);});
	}
	str = str.replace(/\[code\](.+?)\[\/code\]/ig, function($1, $2) {return phpcode($2);});

	if (editor.allowConvert) {
		str = str.replace(/\[hr\]/ig,'<hr />');
		str = str.replace(/\[\/(size|color|font|backcolor)\]/ig,'</font>');
		str = str.replace(/\[(sub|sup|u|i|strike|b|blockquote|li)\]/ig,'<$1>');
		str = str.replace(/\[\/(sub|sup|u|i|strike|b|blockquote|li)\]/ig,'</$1>');
		str = str.replace(/\[\/align\]/ig,'</p>');
		str = str.replace(/\[(\/)?h([1-6])\]/ig,'<$1h$2>');

		str = str.replace(/\[align=(left|center|right|justify)\]/ig,'<p align="$1">');
		str = str.replace(/\[size=(\d+?)\]/ig,'<font size="$1">');
		str = str.replace(/\[color=([^\[\<]+?)\][\s\r\n]*/ig, '<font color="$1">');
		str = str.replace(/\[backcolor=([^\[\<]+?)\]/ig, '<font style="background-color:$1">');
		str = str.replace(/\[font=([^\[\<]+?)\]/ig, '<font face="$1">');
		str = str.replace(/\[list=(a|A|1)\](.+?)\[\/list\]/ig,'<ol type="$1">$2</ol>');
		str = str.replace(/\[(\/)?list\]/ig,'<$1ul>');

		//str = str.replace(/\[(attachment|upload)=(\d+)\]/ig,function($1,$2,$3) {return attpath($3,$2);});
		str = str.replace(/\[s:(\d+)\]/ig,function($1,$2) { return smilepath($2);});
		str = str.replace(/\[img\]([^\[]*)\[\/img\]/ig,'<img src="$1" border="0" />');
		str = str.replace(/\[url=([^\]]+)\]([^\[]+)\[\/url\]/ig, '<a href="$1">'+'$2'+'</a>');
		str = str.replace(/\s*?\[(td|tr)\]/ig,'[$1]');
		str = str.replace(/\[\/(td|tr)\]\s*/ig,'[/$1]');
		str = searchtag('table',str,'tableshow',2);
	}
	for (var i = 0; i < code_htm.length ; i++) {
		str = str.replace("[\twind_phpcode_" + i + "\t]", code_htm[i]);
	}
	
	str = str.replace(/\n/ig,'<br />');
	return str;
};

function htmlTagTrim(t1, t2, t3) {
	var re = /([\/\w]+)/;
	var tag1 = t1;
	if (t1.match(re)) {
		tag1 = RegExp.$1;
	}
	var tag2 = t3;
	if (t3.match(re)) {
		tag2 = RegExp.$1;
	}
	if (tag1 == 'tr' && tag2 == 'td' || tag1 == '/td' && tag2 == 'td' || tag1 == '/td' && tag2 == '/tr') {
		return '<' + t1 + '><' + t3 + '>';
	} else {
		return '<' + t1 + '>' + t2 + '<' + t3 + '>';
	}
};
function phpcode(code) {
	code_htm.push('[code]' + code.replace(/<\/p>/ig,'\n') + '[/code]');
	return "[\twind_phpcode_" + (code_htm.length - 1) + "\t]";
};
function tableshow(style,str) {
	if (style.substr(0,1) == '=') {
		var width = style.substr(1);
	} else {
		var width = '100%';
	}
	str = str.replace(/\[td=(\d{1,2}),(\d{1,2})(,(\d{1,3}%?))?\]/ig,'<td colspan="$1" rowspan="$2" width="$4">');
	str = str.replace(/\[(tr|td)\]/ig,'<$1>');
	str = str.replace(/\[\/(tr|td)\]/ig,'</$1>');

	return '<table width=' + width + ' class="t" cellspacing=0>' + str + '</table>';
};
function smilepath(NewCode) {
	return '<img src="' + imgpath + '/post/smile/' + face[NewCode][0] + '" smile="' + NewCode + '" /> ';
};
function attpath(attid,type) {
	var path = '';
	if (type == 'attachment' && IsElement('atturl_'+attid)) {
		path = getObj('atturl_'+attid).innerHTML;
	} else if (type == 'upload' && is_ie && IsElement('attachment_'+attid)) {
		path = getObj('attachment_'+attid).value;
	}
	if (!path) {
		return '[' + type + '=' + attid + ']';
	} else {
		if (!path.match(/\.(jpg|gif|png|bmp|jpeg)$/ig)) {
			path = imgpath + '/' + stylepath + '/file/zip.gif';
		}
		var img = imgmaxwh(path,320);
		return '<img src="' + path + '" type="' + type + '_' + attid + '" width="'+img.width+'" />';
	}
};
function addattach(attid) {
	editor.focusEditor();
	if (editor._editMode == 'textmode') {
		AddText(' [attachment=' + attid + '] ','');
	} else if (IsElement('atturl_' + attid)) {
		var path = getObj('atturl_'+attid).innerHTML;
		if (!path.match(/\.(jpg|gif|png|bmp|jpeg)$/ig)) {
			path = imgpath + '/' + stylepath + '/file/zip.gif';
		}
		img = imgmaxwh(path,320);

		setTimeout(function(){
			var temp = '<img src="'+img.src+'" type="attachment_'+attid;
			if (img.width>320) {
				img.width = 320;
			}
			if (img.width) {
				temp += '" width="'+img.width+'" />';
			} else {
				temp += '" />';
			}
			editor.insertHTML(temp);
		},1000);
	}
	closep();
};
function imgmaxwh(url,maxwh) {
	var img = new Image();
	img.src = url;
	if (img.width>maxwh || img.width>maxwh) {
		img.width = (img.width/img.height)>1 ? maxwh : maxwh*img.width/img.height;
	}
	return img;
};
function checklength(theform,postmaxchars) {
	if (postmaxchars != 0) {
		message = '\n' + I18N['maxbits'] + postmaxchars;
	} else {
		message = '';
	}
	var msg = editor._editMode == "textmode" ? editor.getHTML() : htmltocode(editor.getHTML());

	alert(I18N['currentbits'] + strlen(msg) + message);
};
function extend(cmdID) {
	if (cmdID == 'setform') {
		editor.saveRange();
	}
	if (typeof read == 'object' && read.obj != null && read.obj.id == 'wy_'+cmdID) {
		closep();read.obj=null;
	} else {
		read.obj = getObj('wy_'+cmdID);
		ajax.send('pw_ajax.php','action=extend&type='+cmdID,ajax.get);
	}
	getObj('pw_box').onmousedown=function(e){
		e = e||event;
		if (e.stopPropagation){
			e.stopPropagation();
		}else{
			e.cancelBubble = true;
		}
	};
	document.body.onmousedown = closeExtend;
};
function closeExtend(e){
	closep();
	document.body.onmousedown = null;
	getObj('pw_box').onmousedown = null;
	read.obj = null;
}
function upcode(id,param) {
	var d = getObj(id).lastChild.innerHTML.split('|');
	var t = id.substr(id.indexOf('_')+1);
	var c = new Array();
	for (var i=0;i<param;i++) {
		do{
			c[i] = prompt(d[i],'');
			if (c[i] == null)
				return;
		}while (c[i]=='');
	}
	switch(param) {
		case '2' : code = '[' + t + '=' + c[0] + ']' + c[1] + '[/' + t + ']';break;
		case '3' : code = '[' + t + '=' + c[0] + ',' + c[1] + ']' + c[2] + '[/' + t + ']';break;
		default: code = '[' + t + ']' + c[0] + '[/' + t + ']';break;
	}
	editor.focusEditor();
	AddCode(code,'');
	closep();
};
function IsChecked(id) {
	return document.getElementById(id) && document.getElementById(id).checked === true ? true : false;
};
function insertform(id) {
	editor.restoreRange();
	var code = '<table class="t" width="60%">';
	code += '<tr class="tr3"><td colspan=2><b>'+id+'</b></td></tr>'
	var ds   = getObj('formstyle').getElementsByTagName('tr');
	for (var i=0;i<ds.length;i++) {
		code += '<tr class="tr3"><td>'+ds[i].firstChild.innerHTML+'</td><td>'+ds[i].lastChild.firstChild.value+'</td></tr>';
	}
	code += '</table>';
	if (editor._editMode=='textmode') {
		AddText(htmltocode(code),'');
	} else {
		editor.insertHTML(code);
	}
	closep();
};
function showform(id) {
	ajax.send('pw_ajax.php','action=extend&type=setform&id='+id,ajax.get);
}
function pageCut(){
	editor.saveRange();
	editor.restoreRange();
	var code = "\n[###page###]\n";
	if (editor._editMode=='textmode') {
		AddText(code,'');
	} else {
		editor.insertHTML(codetohtml(code));
	}
	return false;
}
WYSIWYD.prototype.flex = function(e) {
	if (is_ie) {
		document.body.onselectstart = function(){return false;}
	}
	var _ = this;
	var e = is_ie ? window.event : e;
	var pY = e.clientY;
	var pH = parseInt(this._textArea.style.height);

	document.onmousemove = function(e) {
		var e = is_ie ? window.event : e;
		var y = e.clientY;
		var h = pH + (y - pY);
		_._textArea.style.height = h + 'px';
		if (_._iframe != null) {
			_._iframe.style.height = h + 'px';
		}
	}
	document.onmouseup = function() {
		if (is_ie) {
			document.body.onselectstart = function(){return true;}
		}
		document.onmousemove = '';
		document.onmouseup   = '';
	}
};

WYSIWYD.prototype.addsmile = function(NewCode) {
	this.restoreRange();
	if (this._editMode == 'textmode') {
		sm = '[s:' + NewCode + ']';
		AddText(sm,'');
	} else {
		sm = '<img src="' + imgpath + '/post/smile/' + face[NewCode][0] + '" smile="' + NewCode + '" /> ';
		this.insertHTML(sm);
	};
	if(window.getSelection)
	{
		this._iframe.contentWindow.focus();
		var rng = this._iframe.contentWindow.getSelection().getRangeAt(0);
		var item = rng.startContainer;
		rng.setStartAfter(item);
	}
};