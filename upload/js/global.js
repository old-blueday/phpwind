/*
 *对话框类。
 *使用举例：
 *@example
 new PwMenu('boxID').guide();
 *
 */
/**
 *@param String id 对话框的id，若不传递，则默认为pw_box
 */
PWMENU_ZINDEX=0;

function PwMenu(id){
	this.pid	= null;
	this.obj	= null;
	this.w		= null;
	this.h		= null;
	this.t		= 0;
	this.menu	= null;
	this.mid	= id;
	this.oCall  = null;
	this.init(id);
}

PwMenu.prototype = {

	init : function(id) {
		this.menu = getPWBox(id);
		var _ = this;
		document.body.insertBefore(this.menu,document.body.firstChild);
		_.menu.style.zIndex=PWMENU_ZINDEX+10+"";
		PWMENU_ZINDEX+=10;
	},

	guide : function() {
		this.menu=this.menu||getPWBox(this.mid);
		this.menu.className = '';
		this.menu.innerHTML = '<div class="popout"><table border="0" cellspacing="0" cellpadding="0"><tbody><tr><td class="bgcorner1"></td><td class="pobg1"></td><td class="bgcorner2"></td></tr><tr><td class="pobg4"></td><td><div class="popoutContent" style="padding:20px;"><img src="'+imgpath+'/loading.gif" align="absmiddle" alt="loading" /> 正在加载数据...</div></td><td class="pobg2"></td></tr><tr><td class="bgcorner4"></td><td class="pobg3"></td><td class="bgcorner3"></td></tr></tbody></table></div>';
		this.menupz(this.obj);
	},

	close : function() {
		var _=this;
		read.t = setTimeout(function() {
			_.menu?0:_.menu=read.menu;
			if (_.menu) {
				_.menu.style.display = 'none';
				_.menu.className = '';
				if (_.oCall && _.oCall.close) _.oCall.close();
			}
		}, 100);
	},

	setMenu : function(element,type,border,oCall) {
		if (this.IsShow() && this.oCall && this.oCall.close) {
			this.oCall.close();
		}
		if (type) {
			this.menu=this.menu||getPWBox(this.mid);
			var thisobj = this.menu;
		} else {
			var thisobj = getPWContainer(this.mid,border);
		}
		if (typeof(element) == 'string') {
			thisobj.innerHTML = element;
		} else {
			while (thisobj.hasChildNodes()) {
				thisobj.removeChild(thisobj.firstChild);
			}
			thisobj.appendChild(element);
		}
		this.oCall = null;
		if (typeof oCall == 'object' && oCall.open) {
			this.oCall = oCall;
			oCall.open();
		}
	},

	move : function(e) {
		if (is_ie) {
			document.body.onselectstart = function(){return false;}
		}
		var e  = is_ie ? window.event : e;
		var o  = this.menu||getPWBox(this.mid);
		var x  = e.clientX;
		var y  = e.clientY;
		this.w = e.clientX - parseInt(o.offsetLeft);
		this.h = e.clientY - parseInt(o.offsetTop);
		var _=this;
		document.onmousemove = function(e) {
			_.menu=_.menu||getPWBox(_.mid);
			var e  = is_ie ? window.event : e;
			var x  = e.clientX;
			var y  = e.clientY;
			_.menu.style.left = x - _.w + 'px';
			_.menu.style.top  = y - _.h + 'px';
		};
		document.onmouseup   = function() {
			if (is_ie) {
				document.body.onselectstart = function(){return true;}
			}
			document.onmousemove = '';
			document.onmouseup   = '';
		};
	},


	open : function(idName, object, type, pz, oCall) {
		if (typeof idName == 'string') {
			idName = getObj(idName);
		}
		if (idName == null) return false;
		this.menu=this.menu||getPWBox(this.mid);
		clearTimeout(read.t);
		if (typeof type == "undefined" || !type) type = 1;
		if (typeof pz == "undefined" || !pz) pz = 0;

		this.setMenu(idName.innerHTML, 1, 1, oCall);
		this.menu.className = idName.className;
		this.menupz(object,pz);
		var _=this;
		if (type == 3) {
			document.onmousedown = function (e) {
				var o = is_ie ? window.event.srcElement : e.target;
				if (!issrc(o)) {
					read.close();
					document.onmousedown = '';
				}
			}
		} else if (type != 2) {
			getObj(object).onmouseout = function() {_.close();getObj(object).onmouseout = '';};
			this.menu.onmouseout = function() {_.close();}
			this.menu.onmouseover = function() {clearTimeout(read.t);}
		}
	},

	menupz : function(obj,pz) {
		this.menu=this.menu||getPWBox(this.mid);
		this.menu.onmouseout = '';
		this.menu.style.display = '';
		//this.menu.style.zIndex	= 3000;
		this.menu.style.left	= '-500px';
		this.menu.style.visibility = 'visible';

		if (typeof obj == 'string') {
			obj = getObj(obj);
		}
		if (obj == null) {
			if (is_ie) {
				this.menu.style.top  = (ietruebody().offsetHeight - this.menu.offsetHeight)/3 + getTop() +($('upPanel')?$('upPanel').scrollTop:0)+ 'px';
				this.menu.style.left = (ietruebody().offsetWidth - this.menu.offsetWidth)/2 + 'px';
			} else {
				this.menu.style.top  = (document.documentElement.clientHeight - this.menu.offsetHeight)/3 + getTop() + 'px';
				this.menu.style.left = (document.documentElement.clientWidth - this.menu.offsetWidth)/2 + 'px';
			}
		} else {
			var top  = findPosY(obj);
			var left = findPosX(obj);
			var pz_h = Math.floor(pz/10);
			var pz_w = pz % 10;
			if (is_ie) {
				var offsetheight = ietruebody().offsetHeight;
				var offsethwidth = ietruebody().offsetWidth;
			} else {
				var offsetheight = ietruebody().clientHeight;
				var offsethwidth = ietruebody().clientWidth;
			}
			/*
			if (IsElement('upPanel') && is_ie) {
				var gettop = 0;
			} else {
				var gettop  = ;
			}
			*/
			var show_top = IsElement('upPanel') ? top - getObj('upPanel').scrollTop : top;

			if (pz_h!=1 && (pz_h==2 || show_top < offsetheight/2)) {
				top += getTop() + obj.offsetHeight;
			} else {
				top += getTop() - this.menu.offsetHeight;
			}
			if (pz_w!=1 && (pz_w==2 || left > (offsethwidth)*3/5)) {
				left -= this.menu.offsetWidth - obj.offsetWidth - getLeft();
			}
			this.menu.style.top = top+ 'px';
			if (top < 0) {
				this.menu.style.top  = 0  + 'px';
			}
			this.menu.style.left = left + 'px';
			if (pz_w != 1 && left + this.menu.offsetWidth > document.body.offsetWidth+ietruebody().scrollLeft) {
				this.menu.style.left = document.body.offsetWidth+ietruebody().scrollLeft-this.menu.offsetWidth-30 + 'px';
			}
		}
	},

	InitMenu : function() {
		var _=this;
		function setopen(a,b) {
			if (getObj(a)) {
				var type = null,pz = 0,oc;
				if (typeof window[a] == 'object') {
					oc = window[a];
					oc.type ? type = oc.type : 0;
					oc.pz ? pz = oc.pz : 0;
				}
				getObj(a).onmouseover = function(){_.open(b, a, type, pz, oc);};
				//getObj(a).onmouseover=function(){_.open(b,a);callBack?callBack(b):0};
				//try{getObj(a).parentNode.onfocus = function(){_.open(b,a);callBack?callBack(b):0};}catch(e){}
			}
		}
		for (var i in openmenu) {
			try{setopen(i,openmenu[i]);}catch(e){}
		}
	},

	IsShow : function() {
		this.menu=this.menu||getPWBox(this.mid);
		return (this.menu.hasChildNodes() && this.menu.style.display != 'none') ? true : false;
	}
};
var read = new PwMenu();

function closep() {
	read.menu.style.display = 'none';
	read.menu.className = '';
}
function cancelping(url) {
	ajax.send(url,'',function(){
	var in_text=ajax.request.responseText;
	TINY.box.show(in_text,1,700,630,1);
	})
}
function findPosX(obj) {
	var curleft = 0;
	if (obj.offsetParent) {
		while (obj.offsetParent) {
			curleft += obj.offsetLeft
			obj = obj.offsetParent;
		}
	} else if (obj.x) {
		curleft += obj.x;
	}
	return curleft - getLeft();
}
function findPosY(obj) {
	var curtop = 0;
	if (obj.offsetParent) {
		while (obj.offsetParent) {
			curtop += obj.offsetTop
			obj = obj.offsetParent;
		}
	} else if (obj.y) {
		curtop += obj.y;
	}
	return curtop - getTop();
}
function in_array(str,a){
	for (var i=0; i<a.length; i++) {
		if(str == a[i])	return true;
	}
	return false;
}
function loadjs(path, code, id, callBack) {
	if (typeof id == 'undefined') id = '';
	if (id != '' && IsElement(id)) {
		try{callBack?callBack():0;}catch(e){}
		return false;
	}
	var header = document.getElementsByTagName("head")[0];
	var s = document.createElement("script");
	if (id) s.id = id;
	if (path) {
		//bug fix
		if(is_webkit && path.indexOf(' ')>-1)
		{
			var reg = /src="(.+?)"/ig;
			var arr = reg.exec(path);
			if(arr){
				path = arr[1];
			}				
		}
		s.src = path;
	} else if (code) {
		s.text = code;
	}
	if (document.all) {
		s.onreadystatechange = function() {
			if (s.readyState == "loaded" || s.readyState == "complete") {
				callBack?callBack():0;
			}
		};
	} else {
		try{s.onload = callBack?callBack:null;}catch(e){callBack?callBack():0;}
	}
	header.appendChild(s);
	return true;
}
function keyCodes(e) {
	if (read.menu.style.display == '' && e.keyCode == 27) {
		read.close();
	}
}

function opencode(menu,td,id) {
	document.body.onclick = null;
	document.body.onmousedown=null;
	var id = id || 'ckcode';
	if (read.IsShow() && read.menu.firstChild.id == id) return;
	read.open(menu,td,2,11);
	getObj(id).src = 'ck.php?nowtime=' + new Date().getTime();

	document.body.onmousedown=function(e) {
		var o = is_ie ? window.event.srcElement : e.target;
        var f = is_ie ? false : true;//firefox  e.type = click by lh

		if( o!=getObj(id) && o!=td )
		{
			closep();
		}
		if (o == td || (f && e.type == "click")) {
			return;
		} else if (o.id == id) {
			getObj(id).src = 'ck.php?nowtime=' + new Date().getTime();
		} else {
			closep();
			document.body.onmousedown = null;
			document.body.onmousedown=null;
		}
	};

}

function getPWBox(type){
	if (getObj(type||'pw_box')) {
		return getObj(type||'pw_box');
	}
	var pw_box	= elementBind('div',type||'pw_box','','position:absolute');

	document.body.appendChild(pw_box);
	return pw_box;
}

function getPWContainer(id,border){
	if (typeof(id)=='undefined') id='';
	if (getObj(id||'pw_box')) {
		var pw_box = getObj(id||'pw_box');
	} else {
		var pw_box = getPWBox(id);
	}
	if (getObj(id+'box_container')) {
		return getObj(id+'box_container');
	}

	if (border == 1) {
		pw_box.innerHTML = '<div class="popout"><div id="'+id+'box_container"></div></div>';
	} else {
		pw_box.innerHTML = '<div class="popout"><table border="0" cellspacing="0" cellpadding="0"><tbody><tr><td class="bgcorner1"></td><td class="pobg1"></td><td class="bgcorner2"></td></tr><tr><td class="pobg4"></td><td><div class="popoutContent" id="'+id+'box_container"></div></td><td class="pobg2"></td></tr><tr><td class="bgcorner4"></td><td class="pobg3"></td><td class="bgcorner3"></td></tr></tbody></table></div>';
	}
	var popoutContent = getObj(id+'box_container');
	return popoutContent;
}
function elementBind(type,id,stylename,csstext){
	var element = document.createElement(type);
	if (id) {
		element.id = id;
	}
	if (typeof(stylename) == 'string') {
		element.className = stylename;
	}
	if (typeof(csstext) == 'string') {
		element.style.cssText = csstext;
	}
	return element;
}

function addChild(parent,type,id,stylename,csstext){
	parent = objCheck(parent);
	var child = elementBind(type,id,stylename,csstext);
	parent.appendChild(child);
	return child;
}

function delElement(id){
	id = objCheck(id);
	id.parentNode.removeChild(id);
}

function pwForumList(isLink,isPost,fid,handle,ifblank) {
	if (isLink == true) {
		if (isPost == true){
			if(ifblank == true) {
				window.open('post.php?fid='+fid);
			} else {
				window.location.href = 'post.php?fid='+fid;
			}
			if (is_ie) {
				window.event.returnValue = false;
			}
		} else {
			return true;
		}
	} else {
		if (gIsPost != isPost || read.menu.style.display=='none' || read.menu.innerHTML == '') {
			read.menu.innerHTML = '';
			if (isPost == true) {
				if (getObj('title_forumlist') == null) {
					showDialog('error','没有找到版块列表信息');
				}
				getObj('title_forumlist').innerHTML = '选择你要发帖的版块';
			} else {
				if (getObj('title_forumlist') == null) {
					showDialog('error','没有找到版块列表信息');
				}
				getObj('title_forumlist').innerHTML = '快速浏览';
			}
			gIsPost = isPost;
			if (handle.id.indexOf('pwb_')==-1) {
				read.open('menu_forumlist',handle,2);
			}

		} else {
			read.close();
		}
	}
	return false;
}
function char_cv(str){
	if (str != ''){
		str = str.replace(/</g,'&lt;');
		str = str.replace(/%3C/g,'&lt;');
		str = str.replace(/>/g,'&gt;');
		str = str.replace(/%3E/g,'&gt;');
		str = str.replace(/'/g,'&#39;');
		str = str.replace(/"/g,'&quot;');
	}
	return str;
}

function showDialog(type,message,autohide,callback) {
	if (!type) type = 'warning';
	var tar = '<div class="popBottom" style="text-align:right;">';
	if (type == 'confirm' && typeof(callback) == 'function') {
		temp = function () {
			closep();
			if (typeof(callback)=='function') {
				callback();
			}
		}
		var button = typeof(callback)=='function' ? '<span class="btn2"><span><button onclick="temp();" type="button">确定</button></span></span>' : '<span class="btn2"><span><button type="button">确定</button></span></span>';

		tar += button+'</span></span>';
	}
	if (autohide) {
		tar += '<div class="fl gray">本窗口'+autohide+'秒后关闭</div>';
	}
	tar += '<span class="bt2"><span><button onclick="closep();" type="button">关闭</button></span></span>';
	var container = '<div style="width:350px;"><div class="popTop">提示</div><div class="popCont"><img src="'+imgpath+'/'+type+'_bg.gif" class="mr10" align="absmiddle" />'+message+'</div>'+tar+'</div>';
	read.setMenu(container);
	read.menupz();
	if (autohide) {
		window.setTimeout("closep()", (autohide * 1000));
	}
}

function checkFileType() {
	var fileName = getObj("uploadpic").value;
	if (fileName != '') {
		var regTest = /\.(jpe?g|gif|png)$/gi;
		var arrMactches = fileName.match(regTest);
		if (arrMactches == null) {
			getObj('fileTypeError').style.display = '';
			return false;
		} else {
			getObj('fileTypeError').style.display = 'none';
		}
	}
	return true;
}
var searchTxt = '搜索其实很简单！ (^_^)';
function searchFocus(e){
	if(e.value == searchTxt)
		e.value='';
}
function searchBlur(e){
	if(e.value == '')
		e.value=searchTxt;
}
function getSearchType(e){
	var n = e.srcElement;
	if(n && n.tagName!='LI') return;
	n.parentNode.nextSibling.innerHTML = n.innerHTML;
	var m = n.parentNode.firstChild;
	while(m){
		if(m.style.display=='none'){
			m.style.display='';
			break;
		}else
			m = m.nextSibling;
	}
	n.style.display='none';
	getObj('search_type').value=n.getAttribute('type');
	n.parentNode.style.display='none';
}
function searchInput() {
	if(getObj('search_input').value==searchTxt)
		getObj('search_input').value='';
	return true;
}