
function MgMode() {
	this.t = null;
	this.mgmenu = null;
	this.init();
}

MgMode.prototype = {

	init : function() {
		var its = this;
		ajax.send('pw_ajax.php?action=mgmode','',function() {
			if (ajax.request.responseText != 'false') {
				its.mgmenu = ajax.request.responseText;
				its.showMenu();
			}
		});
	},

	showMenu : function(ev) {
		var e=ev||window.KSHBJ||null;
		if (this.mgmenu) {
			read.menu.className = 'popout';
			read.menu.innerHTML = this.mgmenu;
			read.menupz(e,0);
			var its = this;
			this.t = setInterval(function(){its.move(e)},100);
		}
	},

	move : function(ev) {
		if (read.IsShow() && IsElement('mgmode')) {
			read.menupz(ev,0);
		} else {
			clearInterval(this.t);
		}
	},

	forder : function() {
		this.x = null;
		this.y = null;
		this.obj = null;
		this.src = null;
		this.cate = null;
		this.across = false;

		this.init = function() {
			var tbodys = getObj('content').getElementsByTagName('tbody');
			var its = this;
			for (var i=0; i<tbodys.length; i++) {
				if (tbodys[i].id.match(/^cate_\d+$/ig)) {
					if (tbodys[i].className != 'across') {
						var trs = tbodys[i].getElementsByTagName('tr');
						for (var j = 0; j < trs.length; j++) {
							if (trs[j].getElementsByTagName('td').length > 1) {
								trs[j].style.cursor = 'move';
								trs[j].onmousedown = function(event){its.moveStart(event,this);}
							}
						}
					} else {
						var trs = tbodys[i].getElementsByTagName('th');
						for (var j = 0; j < trs.length; j++) {
							if (trs[j].id.match(/^fid_\d+/ig)) {
								trs[j].style.cursor = 'move';
								trs[j].onmousedown = function(event){its.moveStart(event,this);}
							}
						}
					}
				}
			}
			var ts = getObj('content').getElementsByTagName('div');
			for (var i = 0; i < ts.length; i++)	{
				this.setpz(ts[i]);
			}
			read.menu.innerHTML = '<div id="mgmode"><table border="0" cellspacing="0" cellpadding="0"><tbody><tr><td class="bgcorner1"></td><td class="pobg1"></td><td class="bgcorner2"></td></tr><tr><td class="pobg4"></td><td><div class="popoutContent"><div style="width:280px;"><div class="popTop">版块顺序设置</div><div class="p10"><p class="mb5">分类顺序可通过分类右边的↑↓箭头进行调整</p>版块可以直接拖拽进行操作</div><div class="popBottom"><span class="btn2"><span><button onclick="mf.submit();" type="button">保存</button></span></span><span class="bt2"><span><button onclick="mf.cancle(true);" type="button">返回</button></span></span></div></div></div></td><td class="pobg2"></td></tr><tr><td class="bgcorner4"></td><td class="pobg3"></td><td class="bgcorner3"></td></tr></tbody></table></div>';
		}
		this.preo = function(o) {
			o = o.previousSibling;
			while (o != null && o.nodeType != 1) {
				o = o.previousSibling;
			}
			return o;
		}
		this.nexto = function(o) {
			o = o.nextSibling;
			while (o != null && o.nodeType != 1) {
				o = o.nextSibling;
			}
			return o;
		}
		this.setpz = function(o) {
			if (o.id.match(/^t_\d+$/ig)) {
				var th = o.getElementsByTagName('h3')[0];
				if (this.preo(o) && this.preo(o).id.match(/^t_\d+$/ig)) {
					this.addicon(th,'up');
				}
				if (this.nexto(o) && this.nexto(o).id.match(/^t_\d+$/ig)) {
					this.addicon(th,'down');
				}
			}
		}
		this.addicon = function(o,type) {
			var a = document.createElement('a');
			var its = this;
			a.className = 'fn f12 extra';
			a.href="javascript:;";
			a.style.cursor = 'pointer';
			a.innerHTML = type == 'up' ? '<span class="cate_fold">↑</span>' : '<span class="cate_fold">↓</span>';
			a.onclick = function(){its.movepz(this,type);}
			o.appendChild(a);
			//o.insertBefore(a,o.firstChild);
		}
		this.removeicon = function(o) {
			if (o.id.match(/^t_\d+$/ig)) {
				var s = o.getElementsByTagName('h3')[0].childNodes;
				for (var i = s.length - 1; i >= 0 ; i--) {
					if (s[i].nodeType == 1 && s[i].tagName.toLowerCase() == 'a' && s[i].className == 'fn f12 extra') {
						s[i].parentNode.removeChild(s[i]);
					}
				}
			}
		}
		this.movepz = function(o,type) {
			while (o && (o.tagName.toLowerCase() != 'div' || !o.id.match(/^t_\d+$/ig))) {
				o = o.parentNode;
			}
			if (o == null) return;
			switch (type) {
				case 'up':
					if (!this.preo(o) || !this.preo(o).id.match(/^t_\d+$/ig)) {
						return;
					}
					this.removeicon(this.preo(o));
					this.removeicon(o);
					o.parentNode.insertBefore(o,this.preo(o));
					this.setpz(o);
					this.setpz(this.nexto(o));
					break;
				case 'down':
					if (!this.nexto(o) || !this.nexto(o).id.match(/^t_\d+$/ig)) {
						return;
					}
					this.removeicon(this.nexto(o));
					this.removeicon(o);
					o.parentNode.insertBefore(this.nexto(o),o);
					this.setpz(o);
					this.setpz(this.preo(o));
					break;
			}
		}
		this.moveStart = function(e,o) {
			var e = is_ie ? event : e;
			if (is_ie) {
				document.body.onselectstart = function() {
					return false;
				}
			}
			this.src = o;
			this.cate = o.parentNode;
			while (this.cate.tagName.toLowerCase() != 'tbody') {
				this.cate = this.cate.parentNode;
			}
			this.obj  = document.createElement('div');
			this.obj.className = 't';
			this.obj.style.position = 'absolute';
			var table = document.createElement('table');
			var tbody = document.createElement('tbody');
			if (o.tagName.toLowerCase() == 'tr') {
				var tr  = o.cloneNode(true);
				var tds = tr.childNodes;
				for (var i = 0; i < tds.length; i++) {
					if (tds[i].nodeType == 1) {
						tds[i].width = o.childNodes[i].offsetWidth;
					}
				}
				this.across = false;
			} else {
				var tr = document.createElement('tr');
				tr.className = 'tr1';
				tr.appendChild(o.cloneNode(true));
				this.across = true;
			}
			tbody.appendChild(tr);
			table.appendChild(tbody);
			this.obj.appendChild(table);
			document.body.appendChild(this.obj);

			this.obj.style.width = parseInt(o.offsetWidth) + 'px';
			this.obj.style.left  = findPosX(o) + getLeft() + 'px';
			this.obj.style.top   = findPosY(o) + getTop() + 'px';
			this.x = e.clientX - parseInt(this.obj.style.left);
			this.y = e.clientY - parseInt(this.obj.style.top);

			var its = this;
			document.onmousemove = function(event){its.moving(event);};
			document.onmouseup = function(event){its.moveEnd(event);};
		}
		this.moving = function(e) {
			var e = is_ie ? event : e;
			var x = e.clientX;
			var y = e.clientY;
			this.obj.style.left = x - this.x + 'px';
			this.obj.style.top  = y - this.y + 'px';
		}
		this.moveEnd = function(e) {
			if (is_ie) {
				document.body.onselectstart = function(){return true;}
			}
			document.body.removeChild(this.obj);
			document.onmousemove = '';
			document.onmouseup = '';

			var e = is_ie ? event : e;
			var x = e.clientX;
			var y = e.clientY;
			var o = this.cate;
			var l = findPosX(o);
			var t = findPosY(o);
			var src = null;

			if (x<l || x>l+o.offsetWidth || y<t || y>t+o.offsetHeight) return;
			var gtag = this.across ? 'th' : 'tr';
			var trs = o.getElementsByTagName(gtag);
			for (var i = 0; i < trs.length; i++) {
				//if (!this.across || this.across && trs[i].className == 'td1 f_one') {
					l = findPosX(trs[i]);
					t = findPosY(trs[i]);
					if (x>l && x<l+trs[i].offsetWidth && y>t && y<t+trs[i].offsetHeight) {
						src = trs[i];break;
					}
				//}
			}
			if (src == null) return;
			var s = this.src.nextSibling;
			var p = this.src.parentNode;
			if (s == src) {
				p.insertBefore(src,this.src);
			} else {
				src.parentNode.insertBefore(this.src,src);
				if (this.across && src.parentNode != p) {
					if (s == null) {
						p.appendChild(src);
					} else {
						p.insertBefore(src,s);
					}
				}
			}
		}
		this.submit = function() {
			var tbodys = getObj('content').getElementsByTagName('tbody');
			var data = 'action=mforder';
			for (var i = 0; i < tbodys.length; i++) {
				if (tbodys[i].id.match(/^cate_\d+$/ig)) {
					var fid = '';
					var gtag = tbodys[i].className == 'across' ? 'th' : 'tr';
					var trs = tbodys[i].getElementsByTagName(gtag);
					for (var j = 0; j < trs.length; j++) {
						if (trs[j].id.match(/^fid_\d+/ig)) {
							fid += (fid ? ',' : '') + trs[j].id.substr(4);
						}
					}
					data += '&cate[' + tbodys[i].id.substr(5) + ']=' + fid;
				}
			}
			this.cancle(false);
			ajax.send('pw_ajax.php',data,ajax.guide);
		}
		this.cancle = function(p) {
			var tbodys = getObj('content').getElementsByTagName('tbody');
			for (var i = 0; i < tbodys.length; i++) {
				if (tbodys[i].id.match(/^cate_\d+$/ig)) {
					if (tbodys[i].className != 'across') {
						var trs = tbodys[i].getElementsByTagName('tr');
						for (var j = 0; j < trs.length; j++) {
							if (trs[j].getElementsByTagName('td').length > 1) {
								trs[j].style.cursor = '';
								trs[j].onmousedown = '';
							}
						}
					} else {
						var trs = tbodys[i].getElementsByTagName('td');
						for (var j = 0; j < trs.length; j++) {
							if (trs[j].id.match(/^fid_\d+/ig)) {
								trs[j].style.cursor = '';
								trs[j].onmousedown = '';
							}
						}
					}
				}
			}
			var ts = getObj('content').getElementsByTagName('div');
			for (var i = 0; i < ts.length; i++)	{
				this.removeicon(ts[i]);
			}
			if (p) {
				mgm.showMenu();
			} else {
				closep();
			}
		}

		this.init();
	},

	fsetname : function() {
		this.fids = new Array();
		this.desc = new Array();
		this.init = function() {
			var its = this;
			document.onmousedown = function(e){its.select(e);};
			read.menu.innerHTML = '<div id="mgmode"><table border="0" cellspacing="0" cellpadding="0"><tbody><tr><td class="bgcorner1"></td><td class="pobg1"></td><td class="bgcorner2"></td></tr><tr><td class="pobg4"></td><td><div class="popoutContent"><div style="width:280px;"><div class="popTop">版块名称修改</div><div class="p10"><p class="mb5">点击版块标题即可修改</div><div class="popBottom"><span class="btn2"><span><button onclick="mf.submit();" type="button">保存</button></span></span><span class="bt2"><span><button onclick="mf.cancle(true);" type="button">返回</button></span></span></div></div></div></td><td class="pobg2"></td></tr><tr><td class="bgcorner4"></td><td class="pobg3"></td><td class="bgcorner3"></td></tr></tbody></table></div>';
		}
		this.select = function(e) {
			var e = is_ie ? window.event : e;
			var o = e.srcElement || e.target;
			var o = this.srcobj(o);
			switch (o.tagName.toLowerCase()) {
				case 'a':
					this.edf(o);break;
				case 'span':
					this.edd(o);break;
			}
			return false;
		}
		this.srcobj = function(o) {
			while (!this.issrc(o) && o.parentNode) {
				o = o.parentNode;
				if (o.tagName.toLowerCase() == 'body') break;
			}
			return o;
		}
		this.issrc = function(o) {
			var tn = o.tagName.toLowerCase();
			if (tn == 'a' && o.id.match(/^fn_\d+$/)) {
				return true;
			}
			if (tn == 'span' && o.id.match(/^desc_\d+$/)) {
				return true;
			}
			return false;
		}
		this.edf = function(o) {
			var its = this;
			var fid = o.id.substr(3);
			var input = document.createElement('input');
			input.id = 'input_' + fid;
			input.size = 22;
			input.className = 'input';
			input.value = this.innerhtml(o);
			input.onblur = function() {
				if (this.value != its.innerhtml(o)) {
					its.fids[fid] = this.value;
					o.innerHTML = this.value;
				}
				o.style.display = '';
				o.onclick = '';
				this.parentNode.removeChild(this);
			}
			o.parentNode.insertBefore(input,o);
			o.style.display = 'none';
			o.onclick = function() {return false;}
		}
		this.edd = function(o) {
			var its = this;
			var fid = o.id.substr(5);
			var textarea = document.createElement('textarea');
			textarea.id = 'tta_' + fid;
			textarea.style.cssText = 'width:300px;height:40px';
			textarea.value = this.innerhtml(o);
			textarea.onblur = function() {
				if (this.value != its.innerhtml(o)) {
					its.desc[fid] = this.value;
					o.innerHTML = this.value;
				}
				o.style.display = '';
				this.parentNode.removeChild(this);
			}
			o.parentNode.insertBefore(textarea,o);
			o.style.display = 'none';
		}
		this.innerhtml = function(o) {
			var html = '';
			if (o.hasChildNodes()) {
				for (var i = o.firstChild; i; i = i.nextSibling) {
					html += this.outerhtml(i);
				}
			}
			return html;
		}
		this.outerhtml = function(root) {
			var html = '';
			switch (root.nodeType)	{
				case 1:
					var closed = (!(root.hasChildNodes() || this.needsClosingTag(root)));
					html = "<" + root.tagName.toLowerCase();
					var attrs = root.attributes;
					for (var i = 0; i < attrs.length; ++i) {
						var a = attrs.item(i);
						if (!a.specified) {
							continue;
						}
						var name = a.nodeName.toLowerCase();
						if (/_moz|contenteditable|_msh/.test(name)) {
							continue;
						}
						var value;
						if (name != "style") {
							if (typeof root[a.nodeName] != "undefined" && typeof root[a.nodeName] != "function" && name != "href" && name != "src") {
								value = root[a.nodeName];
							} else {
								value = a.nodeValue;
							}
						} else {
							value = root.style.cssText.toLowerCase();
						}
						if (/(_moz|^$)/.test(value)) {
							continue;
						}
						html += " " + name + '="' + value + '"';
					}
					html += closed ? " />" : ">";
					html += this.innerhtml(root);
					if (!closed) {
						html += "</" + root.tagName.toLowerCase() + ">";
					}
					break;
				case 3:
					if (!root.previousSibling && !root.nextSibling && root.data.match(/^\s*$/i) ) {
						html = '&nbsp;';
					} else {
						html = root.data;
					}
					break;
			}
			return html;
		}
		this.needsClosingTag = function(el) {
			var closingTags = " head script style div span tr td tbody table em strong font a title ";
			return (closingTags.indexOf(" " + el.tagName.toLowerCase() + " ") != -1);
		}
		this.submit = function() {
			var data = 'action=mfsetname';
			for (var i in this.fids) {
				data += '&fids[' + i + ']=' + this.fids[i];
			}
			for (var i in this.desc) {
				data += '&desc[' + i + ']=' + this.desc[i];
			}
			this.cancle(false);
			ajax.send('pw_ajax.php',data,ajax.guide);
		}
		this.cancle = function(p) {
			document.onmousedown = '';
			var inputs = getObj('content').getElementsByTagName('input');
			for (var i = inputs.length - 1; i >= 0; i--) {
				if (inputs[i].id.match(/^input_\d+$/)) {
					inputs[i].nextSibling.style.display = '';
					inputs[i].parentNode.removeChild(inputs[i]);
				}
			}
			var ttas = getObj('content').getElementsByTagName('textarea');
			for (var i = ttas.length - 1; i >= 0; i--) {
				if (ttas[i].id.match(/^tta_\d+$/)) {
					ttas[i].nextSibling.style.display = '';
					ttas[i].parentNode.removeChild(ttas[i]);
				}
			}
			if (p) {
				mgm.showMenu();
			} else {
				closep();
			}
		}
		this.init();
	},

	style : function() {

		this.ccsRules = null;
		this.cssEd = {
			'tablewidth' : {'#main' : 'width'},
			'mtablewidth' : {'#header' : 'width','#footer' : 'width'},
			'bgcolor' : {'body' : 'backgroundColor'},
			'linkcolor' : {'a' : 'color','.bta' : 'color','.menu a:hover' : 'background'},
			'tablecolor' : {'.bta' : 'borderColor','.input' : 'borderColor','textarea' : 'borderColor','#notice' : 'borderTopColor','.t' : 'borderColor','.t2' : 'borderTopColor','.h span.activetab' : 'borderColor', '.menu' : 'borderColor','.pages a' : 'borderColor'},
			'tdcolor' : {'select' : 'borderColor','.toptool' : 'borderBottomColor','#infobox' : 'borderColor', '#profile-menubar' : 'borderColor','.guide' : 'borderColor','td.guide' : 'borderBottomColor','.tr1 td.td1' : 'borderColor', '.tr3 td' : 'borderBottomColor','.tr3 th' : 'borderBottomColor','.tips' : 'borderColor','.tiptop' : 'borderBottomColor','.tipad' : 'borderTopColor','.blockquote' : 'borderColor'},
			'headcolor' : {'.h' : 'backgroundColor','.tr4' : 'backgroundColor'},
			'headborder' : {'.h' : 'borderBottomColor','.tab' : 'backgroundColor','#footer' : 'borderTopColor'},
			'headfontone' : {'.btn' : 'color','.h' : 'color','.h a' : 'color','.tr4' : 'color','.quote' : 'color', '.pages a:hover' : 'color'},
			'headfonttwo' : {'.h span a' : 'color','.h span' : 'color'},
			'cbgcolor' : {'.toptool' : 'backgroundColor','.guide' : 'backgroundColor','.tr2' : 'backgroundColor', '.pages a:hover' : 'backgroundColor','#footer' : 'borderBottomColor'},
			'cbgborder' : {'.tr2 td' : 'borderBottomColor','.tr2 th' : 'borderBottomColor'},
			'cbgfont' : {'.tr2' : 'color','.tr2 a' : 'color','.pages a:hover' : 'borderColor','.pages input' : 'borderColor','.pages input' : 'color'},
			'forumcolorone' : {'.f_one' : 'backgroundColor','.t_one' : 'backgroundColor','.r_one' : 'backgroundColor','.t table' : 'borderColor','.tab' : 'borderColor'},
			'forumcolortwo' : {'.f_two' : 'backgroundColor','.t_two' : 'backgroundColor','.r_two' : 'backgroundColor','.z .tr3:hover' : 'backgroundColor','.tips' : 'backgroundColor'}
		}
		this.setcss = function(o) {
			var cskey = o.name.replace(/^set\[(\w+)\]$/ig,'$1');
			if (!cskey || typeof this.cssEd[cskey] == 'undefined') {
				return;
			}
			var ed = this.cssEd[cskey];
			for (var i in ed) {
				this.setrule(i,ed[i],o.value);
			}
		}
		this.setrule = function(selector,style,v) {
			if (this.ccsRules == null) {
				this.ccsRules = new Array();
				var ss = document.styleSheets[0];
				var rules = ss.cssRules ? ss.cssRules : ss.rules;
				for (var i = 0; i < rules.length; i++) {
					var rule = rules[i];
					var selt = rule.selectorText.toLowerCase().split(',');
					for (var j = 0; j < selt.length; j++) {
						this.ccsRules[selt[j]] = rule;
					}
				}
			}
			if (typeof this.ccsRules[selector] != 'undefined') {
				var rule = this.ccsRules[selector];
				eval('rule.style.' + style + '="' + v + '";');
			}
		}
		this.submit = function() {
			closep();
			ajax.guide();
		}
		read.obj = null;
		ajax.send('pw_ajax.php?action=setstyle','',ajax.get);
	}
}

var mgm = new MgMode();
var mf;