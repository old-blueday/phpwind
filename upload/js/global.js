/*
 *对话框类。
 *使用举例：
 *@example
 new PwMenu('boxID').guide();
 *
 */
/**
 * @param String
 *            id 对话框的id，若不传递，则默认为pw_box
 */
PWMENU_ZINDEX=1001;

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
			/*while (thisobj.hasChildNodes()) {
				thisobj.removeChild(thisobj.firstChild);
			}*/
			thisobj.innerHTML = '';
			thisobj.appendChild(element);
		}
		this.oCall = null;
		if (oCall && oCall.open) {
			this.oCall = oCall;
			oCall.open();
		}
	},

	move : function(e) {
		if(is_ie){document.body.onselectstart = function(){return false;}}
		if(document.body.style.webkitUserSelect){document.body.style.webkitUserSelect = ''}
		var e  = window.event || e;
		var o  = this.menu||getPWBox(this.mid);
		var x  = e.clientX;
		var y  = e.clientY;
		this.w = e.clientX - parseInt(o.offsetLeft);
		this.h = e.clientY - parseInt(o.offsetTop);
		var _=this;
		_.menu=_.menu||getPWBox(_.mid);
		document.body.setCapture && _.menu.setCapture();
		document.onmousemove = function(e) {
			var e  = window.event || e;
			var x  = e.clientX;
			var y  = e.clientY;
			y=(y - _.h<0)?0:(y - _.h);
			_.menu.style.left = x - _.w + 'px';
			_.menu.style.top  = y+ 'px';
		};
		document.onmouseup   = function() {
			if(is_ie){document.body.onselectstart = function(){return true;}}
			document.body.releaseCapture && _.menu.releaseCapture();// IE释放鼠标监控
			document.onmousemove = null;
			document.onmouseup = null;
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

		if (type == 3) {
			this.closeByClick();
		} else if (type != 2) {
			this.closeByMove(object);
		}
	},
	
	closeByClick : function() {
		document.onmousedown = function (e) {
			var e=e||window.event;
			var o = e.target||e.srcElement;
			var contain=contains(read.menu,o);
			if (!contain) {
				read.close();
				document.onmousedown = null;
			}
		}
	},

	closeByMove : function(id) {
		var _=this;
		getObj(id).onmouseout = function() {_.close();getObj(id).onmouseout = null;};
		_.menu.onmouseout = function() {_.close();}
		_.menu.onmouseover = function() {clearTimeout(read.t);}
	},

	menupz : function(obj,pz) {
		this.menu=this.menu||getPWBox(this.mid);
		this.menu.onmouseout = '';
		this.menu.style.display = '';
		// this.menu.style.zIndex = 3000;
		this.menu.style.position='absolute';
		this.menu.style.left	= '-500px';
		this.menu.style.visibility = 'visible';

		if (typeof obj == 'string') {
			obj = getObj(obj);
		}
		if (!obj||obj == null) {
			if (is_ie) {
				this.menu.style.top  = (ietruebody().offsetHeight - this.menu.offsetHeight)/3 + getTop() +(getObj('upPanel') ? getObj('upPanel').scrollTop:0)+ 'px';
				this.menu.style.left = (ietruebody().offsetWidth - this.menu.offsetWidth)/2 + 'px';
			} else {
				var top = (document.documentElement.clientHeight - this.menu.offsetHeight)/3 + getTop();
				if(top < 0){top = 0;}
				this.menu.style.top  = top + 'px';
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
				var offsetheight = document.documentElement.clientHeight;
				var offsethwidth = document.documentElement.clientWidth;
			}
			/*
			 * if (IsElement('upPanel') && is_ie) { var gettop = 0; } else { var
			 * gettop = ; }
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
			this.menu.style.top = (top < 0 ? 0 : top) + 'px';
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
				if (window[a]) {
					oc = window[a];
					oc.type ? type = oc.type : 0;
					oc.pz ? pz = oc.pz : 0;
				}
				getObj(a).onmouseover = function(){_.open(b, a, type, pz, oc);};
				// getObj(a).onmouseover=function(){_.open(b,a);callBack?callBack(b):0};
				// try{getObj(a).parentNode.onfocus =
				// function(){_.open(b,a);callBack?callBack(b):0};}catch(e){}
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
	for (var i = 0,j = a.length; i < j; i++) {
		if(str == a[i])	return true;
	}
	return false;
}
function loadjs(path, code, id, callBack) {
	id = id || '';
	if (id != '' && IsElement(id)) {
		try{callBack?callBack():0;}catch(e){}
		return false;
	}
	var header = document.getElementsByTagName("head")[0];
	var s = document.createElement("script");
	if (id) s.id = id;
	if (path) {
		// bug fix
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
addEvent(document,'keyup',function(e){
	if(read.menu.style.display != 'none'){
		if (e.keyCode == 27) {
			read.close();
		}
	}
});

function opencode(menu,td,id) {
	document.body.onclick = document.body.onmousedown = null;
	var id = id || 'ckcode';
	if (read.IsShow() && read.menu.firstChild.id == id) return;
	read.open(menu,td,2,11);
	getObj(id).src = 'ck.php?nowtime=' + new Date().getTime();

	document.body.onmousedown=function(e) {
		var o = is_ie ? window.event.srcElement : e.target;
        var f = is_ie ? false : true;// firefox e.type = click by lh

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
		}
	};

}

function getPWBox(type){
	if (getObj(type||'pw_box')) {
		return getObj(type||'pw_box');
	}
	var pw_box	= elementBind('div',type||'pw_box','','position:absolute;left:-10000px');

	document.body.appendChild(pw_box);
	return pw_box;
}

function getPWContainer(id,border){
	id = id || '';
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
				getObj('title_forumlist').innerHTML = '快速跳转';
			}
			gIsPost = isPost;
			if (handle.id.indexOf('pwb_')==-1) {
				read.open('menu_forumlist', handle, 3);
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
var searchTxt = '搜索其实很简单！';
function searchFocus(e){
	if(e.value == searchTxt){
		e.value='';
		e.className = '';
	}
	//e.parentNode.className += ' inputFocus';
}
function searchBlur(e){
	if(e.value == ''){
		e.value=searchTxt;
		e.className = 'gray';
	}
	//e.parentNode.className = 'ip';
}
function getSearchType(evt){
	var e=evt||window.event;
	var n = e.srcElement || e.target;
	if(n && n.tagName!='LI') return;
	n.parentNode.parentNode.getElementsByTagName('h6')[0].innerHTML = n.innerHTML;
	var lis = n.parentNode.getElementsByTagName('li');
	for(var i = 0,j=lis.length;i < j;i++){
		lis[i].style.display = '';
	}
	n.style.display='none';
	getObj('search_type').value=n.getAttribute('type');
	n.parentNode.style.display='none';
	//本版搜索
	if (typeof(getObj('inner_forum')) != 'undefined'){
		var inputs = n.parentNode.parentNode.parentNode.getElementsByTagName('input');
		for(var i = 0,j=inputs.length;i < j;i++){
			if (typeof(n.id) && n.id == 'inner_forum') {
				eval("inputs[i].value = " + inputs[i].id + ';');
			} else {
				if (typeof(inputs[i].id) != 'undefined' && inputs[i].id.indexOf('ins_') == 0)
				{
					inputs[i].value = '';
				}
			}
		}
	}
}
function searchInput() {
	if(getObj('search_input').value==searchTxt)
		getObj('search_input').value='';
	return true;
}
//pw通用弹出框
(function(win,doc) {
    if (win.showDlg) return;
        isIE = !+'\v1', // IE浏览器
	    isCompat = doc.compatMode == 'CSS1Compat',	// 浏览器当前解释模式
	    IE6 = isIE && /MSIE (\d)\./.test(navigator.userAgent) && parseInt(RegExp.$1) < 7, // IE6以下需要用iframe来遮罩
	    useFixed = !isIE || (!IE6 && isCompat), // 滚动时，IE7+（标准模式）及其它浏览器使用Fixed定位
        Typeis = function(o,type) {
		    return Object.prototype.toString.call(o)==='[object ' + type + ']';
	    }, // 判断元素类型
        getObj = function(o) {
            return Typeis(o,'String') ? doc.getElementById(o) : o;
        },
        $height = function(obj) {return parseInt(obj.style.height) || obj.offsetHeight}, // 获取元素高度
        $width = function(obj) {return parseInt(obj.style.width) || obj.offsetWidth}, // 获取元素高度
        getWinSize = function() {
            var rootEl = doc.body;
			return [Math.max(rootEl.scrollWidth, rootEl.clientWidth), Math.max(Math.max(doc.body.scrollHeight,rootEl.scrollHeight), Math.max(rootEl.clientHeight,doc.body.clientHeight || window.clientHeight))]
		},
		/* 获取scrollLeft和scrollTop */
		getScrollPos = function() {
		    var body = doc.body,docEl = doc.documentElement;
			return {
			    left:body.scrollLeft || docEl.scrollLeft, top:body.scrollTop || docEl.scrollTop
			}
		},
        empty = function(){},
        defaultCfg = {   // 默认配置
            id:         'pw_dialog',
            type:       'warning',
            message:    '',// 弹出提示的文字
            showObj:    null,// 要显示的本地元素,在ajax提示是常用
            width:      300,// 弹出框高度
            isMask:     1,
            autoHide:   0,// 是否自动关闭
		    zIndex:		9999, // 层叠值
		    onShow:		empty,// 显示时执行
		    onOk:       empty,
		    onClose:	empty, // 关闭时执行
		    left:       '50%',// 绝对位置
		    top:        '50%',
		    alpha:      0.2,// 遮罩的透明度
		    backgroundColor:'#000',// 遮罩的背景色
		    titleText:  '提示',// 提示标题
		    okText:      '确定',// 确定按钮文字
		    cancelText:  '取消',// 取消文字，确认时用
		    closeText:  '关闭',// 关闭文字
		    button:     null// 默认不显示按钮
        },
		icoPath = 'images/';
        
    var Dialog = function(options) {// 构造函数
        var self = this;
        this.options = options;
        if (!(self instanceof Dialog)) {
            return new Dialog(options);
        }
        this._initialize();
    }
    Dialog.prototype = {
        _initialize:function() {
            for(var i in defaultCfg) {
                if(!(i in options)){
                    options[i] = defaultCfg[i];
                }
            }
            this.show();
        },
        show:function(options) {
            var self = this,
                opt = self.options,
                box = opt.showObj;
            	//closep();
                createButton = function(){// 创建按钮
                    var html = [],btn = opt.button;
                    if(opt.autoHide){ html.push('<div class="fl gray">本窗口<span class="spanTime">'+ opt.autoHide +'</span>秒后关闭</div>');}
                    if(btn){
                        for(var i = 0,j = btn.length;i < j;i++ ) {
                            html.push('<span class="bt2"><span><button class="pw_dialoag_button" type="button">'+ btn[i][0] +'</button></span></span>');
                        }
                    }else {
                        if(opt.type === 'confirm') {
                            html.push('<span class="btn2"><span><button type="button" class="pw_dialoag_ok">'+ opt.okText +'</button></span></span>');
                        }
                        html.push('<span class="bt2"><span><button type="button" class="pw_dialoag_close">'+ opt.closeText +'</button></span></span>');
                    }
                    return html.join('');
                }
                // timeout;
            if(!opt.showObj) {
                var divStyle = 'z-index:'+ (opt.zIndex + 1) +';position:'+ (useFixed ? 'fixed' : 'absolute')+';';
                    maskStyle = (!opt.isMask ? 'display:none':'') + 'width:'+ getWinSize()[0] +'px;height:'+ getWinSize()[1] +'px;z-index:'+ opt.zIndex +';position:absolute;top:0;left:0;text-align:center;filter:alpha(opacity='+ opt.alpha*100 + ');opacity:'+ opt.alpha +';background-color:'+opt.backgroundColor;
                    if(!getObj(opt.id)) {
                        box = document.createElement('div');
                        box.id = opt.id;
                    }else {
                        box = getObj(opt.id);
                    }
                    if (!opt.type) opt.type = defaultCfg.type;
		            box.innerHTML = [
		            /* 遮罩 */
		            '<div style="' + maskStyle + '"></div>', IE6 ? ("<iframe id='maskIframe' src='about:blank' style='" + maskStyle + "'></iframe>") : '',
		            /* 窗体 */
		            // IE6 ? "<iframe src='javascript:false'
					// style='width:100%;height:999px;position:absolute;top:0;left:0;z-index:-1;opacity:1;filter:alpha(opacity=100)'></iframe>":
					// '',
		            '<div style="'+ divStyle +'" class="popout">\
		            <table cellspacing="0" cellpadding="0" border="0">\
		                <tbody>\
		                <tr><td class="bgcorner1"></td><td class="pobg1"></td><td class="bgcorner2"></td></tr><tr><td class="pobg4"></td>\
		                    <td>\
		                        <div id="box_container" class="popoutContent">\
		                            <div style="width:'+ opt.width +'px;">\
		                                <div class="popTop">'+ opt.titleText +'</div>\
		                                <div class="popCont" style="padding-left:20px;padding-right:20px;"><img align="absmiddle" class="mr10" src="'+ icoPath + opt.type +'_bg.gif">'+ opt.message +'</div>\
		                                <div style="text-align: right;" class="popBottom">\
		                                '+ createButton() + '\
		                                </div>\
		                            </div>\
		                        </div>\
		                    </td><td class="pobg2"></td></tr><tr><td class="bgcorner4"></td><td class="pobg3"></td><td class="bgcorner3"></td></tr>\
		                </tbody>\
		            </table>\
		            </div>',
		            /* 阴影 */
		            isIE ? "<div id='ym-shadow' style='position:absolute;z-index:10000;background:#808080;filter:alpha(opacity=80) progid:DXImageTransform.Microsoft.Blur(pixelradius=5);'></div>": ''].join('');
		        doc.body.insertBefore(box, doc.body.childNodes[0]);
		        var popout = getElementsByClassName('popout',box)[0];
                popout.style.left = Typeis(opt.left,'Number') ? opt.left + 'px' : opt.left
                popout.style.top = Typeis(opt.top,'Number') ? opt.top + 'px' : opt.top;
                var h = $height(popout),w = $width(popout);
                if(!Typeis(opt.left,'Number')) {
				    popout.style.marginLeft = useFixed ? - w / 2 + "px" : getScrollPos().left - w / 2 + "px";
				}else {
				    popout.style.left = ''+opt.left + 'px';
				}
				if(!Typeis(opt.top,'Number')) {
				    popout.style.marginTop = useFixed ? - h / 2 + "px" : getScrollPos().top - h / 2 + "px";
				}else {
				    popout.style.top = ''+opt.top + 'px';
				}
				var closeTime = function() {
					if(interval){
						clearInterval(interval);
						interval = null;
					}
                };
				if(opt.button) {
				    var customBtn = getElementsByClassName('pw_dialoag_button',box),buttons = opt.button;
				    if(customBtn.length){
                        for(var i = 0,j = customBtn.length;i < j;i++) {
                            (function(i){
                                customBtn[i].onclick = function() {
                                   buttons[i][1] && buttons[i][1](); 
                                }
                            })(i)
                            
                        }
                    }
				}else{
		            var closeBtn = getElementsByClassName('pw_dialoag_close',box),
                        okBtn = getElementsByClassName('pw_dialoag_ok',box);
                   if(closeBtn.length){
                        closeBtn[0].onclick = function() {
                            self.close();
                        }
                    }
                    if(okBtn.length) {
                        okBtn[0].onclick = function() {
                            self.options.onOk && self.options.onOk();
							//self.options.onClose && self.options.onClose();
                            self.close();
                        }
                    }
                }
                
            }else{
                var obj = getObj(opt.showObj);
                if(obj.nodeType !== 1) {// 如果传进来的不是元素,直接return
                    return;
                }
                obj.style.display = '';
                var msgObj = getElementsByClassName('message',obj),
                    msgClose = getElementsByClassName('close',obj);
                if( !msgObj.length ) { return false; }
                msgObj[0].innerHTML = opt.message;
                if( msgClose.length ) { msgClose[0].onclick = function() {obj.style.display = 'none'; }}
            }
            opt.onShow && opt.onShow();
            if(opt.autoHide) {
                var spanTime = getElementsByClassName('spanTime',popout)[0];
		        interval = setInterval(function() {
		                var time = --opt.autoHide;
		                if(spanTime){ spanTime.innerHTML = time;}
		                if(time === 0){
		                    clearInterval(interval);
		                    self.close();
		                }
		        },1000);
		    }
        },
        close:function() {
            var opt = this.options;
            if(!opt.showObj && getObj(opt.id)) {
                doc.body.removeChild(getObj(opt.id));
            }else if(getObj(opt.showObj)) {
                getObj(opt.showObj).style.display = 'none';
            }
            opt.onClose && opt.onClose();
        }
    }
    win['showDlg'] = function(type,message,autohide,callback){
		var isMask = type === 'confirm' ? 0 : 1,
			onClose = type !== 'confirm' ? callback : null,
			options = arguments.length === 1 ? arguments[0] : { type:type,message:message,autoHide:autohide,onOk:callback,onClose:onClose,isMask:isMask };
        Dialog(options);
    }
	win['showDialog'] = win['showDlg'];

	//tab切换
	win.showTabSimple = function(tabNavs,tabBodys,callback){
		if(tabNavs.length!=tabBodys.length){return false;}
		tabBodys[0].style.display = 'block';//默认第一个显示
		var len = tabNavs.length;
		for (var i = 0; i < len; i++) {
	    		(function(i){
					addEvent(tabNavs[i],'click',function(e){
						e.preventDefault();
						for(var j = 0; j < len; j++){
							tabBodys[j].style.display = 'none';
							tabNavs[j].className = '';
						}
						tabNavs[i].className = 'current';
						tabBodys[i].style.display = 'block';
						callback && callback.call(this,tabNavs[i],tabBodys[i],i);
					});
				})(i);
		}
	};
	//显示隐藏一个元素
	win.toggleDisplay=function(handler,cont,callback){
		if(!handler){
			return false;
		}
		addEvent(handler,"click",function(e){
			e.preventDefault();
			var display = getStyle(cont,"display");
			cont.style.display = (display=="none") ? "block" : "none";
			handler.arrow = (display == "none") ? "up":"down";
			callback && callback.call(handler);
		})
	};
	//fadeIn,fadeOut
	var fade = function(elem, flag){
		elem.style.display = '';
		elem.alpha = flag ? 1:100;
		elem.style.opacity = (elem.alpha / 100);
		elem.style.filter = 'alpha(opacity=' + elem.alpha + ')';
		var value = elem.alpha;
		(function(){
			elem.style.opacity = (value / 100);
			elem.style.filter = 'alpha(opacity=' + value + ')';
			if (flag) {
				value+=4;
				if (value <= 100) {
	            	setTimeout(arguments.callee, 14);//继续调用本身
	        	}
			}
			else {
				value-=4;
				if (value >= 0) {
	            	setTimeout(arguments.callee, 14);//继续调用本身
	        	}
			}
	    })();
	}
	win.fadeIn = function(elem){fade(elem,true);};
	win.fadeOut = function(elem){fade(elem,false);};
	//回到顶部功能
	win.scrollBar=function(){
		var Tween={
			Quad:{
				easeOut: function(t,b,c,d){
					return -c *(t/=d)*(t-2) + b;
				},
				easeInOut: function(t,b,c,d){
					if ((t/=d/2) < 1) return c/2*t*t + b;
					return -c/2 * ((--t)*(t-2) - 1) + b;
				}		
			}
		}
		var that = this;
		if(!getObj("scrollBar")){
			var ele = doc.createElement("div");
			ele.id = "scrollBar";
			ele.innerHTML='<a hideFocus="true" href="javascript:void(0)">回到顶部</a>';
			doc.body.appendChild(ele);
		}else{
			var ele = getObj("scrollBar");
		}
		var barTxt="回到顶部";
		var distance=200;//限定范围
		var dd = doc.documentElement;
		var db = doc.body;
		var scrollTop;//顶部距离
		this.setStyle = function(){
			scrollTop = db.scrollTop || dd.scrollTop;//顶部距离
			var sw = dd.scrollWidth;
			var pos='right:50%;margin-right:-510px;';
			var fullscreen = getObj('fullscreenStyle');//判断屏幕状态
			if((fullscreen && !fullscreen.disabled) || sw<1020){//宽屏或者窗口宽度小于可见值时 1020=960+20*2+10*2
				pos='right:5px;';
			}
			var ctxt=scrollTop >= distance ? '': 'display:none';
			ele.style.cssText='position:fixed;'+pos+'bottom:75px;'+ctxt;
		}
		this.update=function(){//控制滑块显示 并修正IE6定位
				scrollTop = db.scrollTop || dd.scrollTop;
				ele.style.display=(scrollTop>=distance)?"block":"none";
			if(!win.XMLHttpRequest){//如果IE6
				var h = ele.offsetHeight;
				var ch = doc.documentElement.clientHeight;
				ele.style.position="absolute";
				ele.style.top=ch+scrollTop-h-75+"px";
			}	
		}
		that.b=0;//初始值
		that.c=0;//变化量
		var d = 10,t = 0;//持续时间和增量
		this.run=function(){
			if(dd.scrollTop){
				dd.scrollTop=Math.ceil(Tween.Quad.easeOut(t,that.b,that.c,d));
			}else{
				db.scrollTop = Math.ceil(Tween.Quad.easeOut(t,that.b,that.c,d));
			}
			if(t<d){ t++; setTimeout(that.run, 10); }else{t=0;}
		}
		ele.onclick=function(){
			that.b = scrollTop;
			that.c =- scrollTop;
			that.run();
			return false;
		}
		this.init=function(){
			this.setStyle();
			win.onscroll=function(){
				that.update();
			}
			win.onresize=function(){
				that.setStyle();
				that.update();
			}
		}
	}
	
	//消息提示
	win.messageTip=function(st){
			if(!getObj("pw_all_tip")){
				return false;
			}
			this.dd = document.documentElement;
			this.db = document.body;
			this.scrollTop=null;//顶部距离
			this.ele=getObj("pw_all_tip");
			this.closeBtn=getObj("pw_all_tclose");
			this.st=st||90;
			this.height=this.ele.offsetHeight;
	}
	messageTip.prototype={
		init:function(){
			if(!this.ele){
				return false;
			}
			this.setStyle();
			var self=this;
			addEvent(this.closeBtn,"click",function(){
				self.close();
			})
			addEvent(window,"scroll",function(){
				self.update();
			})
			addEvent(window,"resize",function(){
				self.setStyle();
				self.update();
			})
		},
		setStyle : function(){
			this.scrollTop = this.db.scrollTop || this.dd.scrollTop;//顶部距离
			var sw = this.dd.scrollWidth;
			var pos='right:50%;margin-right:-480px;';
			var fullscreen = getObj('fullscreenStyle');//判断屏幕状态
			if((fullscreen && !fullscreen.disabled) || sw<980){//宽屏或者窗口宽度小于可见值时 980=960+10*2
				pos='right:10px;';
			}
				this.ele.style.cssText='position:absolute;'+pos+'top:'+this.st+'px;';
				this.ele.style.display="block";
		},
		update:function(){
				this.scrollTop = this.db.scrollTop + this.dd.scrollTop;
				var top=this.ele.getBoundingClientRect().top;
				if(this.scrollTop>this.st){
					if(!window.XMLHttpRequest){
						this.ele.style.top=this.scrollTop+"px";
					}else{
						this.ele.style.position="fixed";
						this.ele.style.top="0px";
					}
				}else{			
					if(!!window.XMLHttpRequest){
						this.ele.style.position="absolute";
					}
					this.ele.style.top=this.st+"px";
				}
				
		},
		close:function(){
			var self = this,url="";
			if(typeof pw_baseurl!="undefined"){
				url=pw_baseurl+"/";
			}
			var num=this.closeBtn.getAttribute("data-num");
			ajax.send(url+'pw_ajax.php?action=clearmessage&num='+num,'',function(){
				var rText = ajax.request.responseText.split('\t');
					self.ele.style.display="none";
			});
		}
	}
	
	/*小名片 2011-06-30*/
	win.usercard = function(){
		this.id = "userCard";
		this.actClass = "_cardshow";
		this.obj = null;
		this.popContent=null;
		this.medalList=null;
		this.medalNum=7;//每次移动几个
		this.inter = null;
		this.dock = false;
		this.currHandler = null;//用来记录当前滑过的元素
		this.wrapWidth=245;//.card_medal_wrap宽度
		this.liWidth=35;
		this.data=[];
	}
	usercard.prototype = {
		"init": function(){
			//初始化,绑定元素鼠标事件触发名片
			var self = this;
			var items = getElementsByClassName(this.actClass);
			if(items.length<1){
				return false;
			}
			for (var i = 0, len = items.length; i < len; i++) {
				(function(item){
					addEvent(item, "mouseover", function(e){
						var t = this;
						if (self.inter) {
							clearTimeout(self.inter);
						}
						self.inter = setTimeout(function(){
							self.show(t);
						}, 500);
						return false;
					})
					addEvent(item, "mouseout", function(e){
						var t = this;
						if (self.inter) {
							clearTimeout(self.inter);
						}
						self.inter = setTimeout(function(){
							if (!self.dock&&self.obj!=null) {
								self.hide();
							}
						}, 1000);
						return false;
					})
				})(items[i])
			}
			
		},
		"show": function(o){
			//显示名片
			var self = this;
			//如果是刚刚滑过的元素
			if (self.obj && self.currHandler && self.currHandler == o) {
				self.obj.style.visibility="visible";
				return false;
			}
			self.currHandler = o;
			var obj = document.getElementById(this.id);
			var popc=getObj("cardPopContent");
			if (obj&&popc) {
				popc.innerHTML = "";
				this.obj = obj;
			}
			else {
				this.obj = document.createElement("div");
				this.obj.id = this.id;
				this.obj.style.position = "absolute";
				this.obj.innerHTML='<table border="0" cellspacing="0" cellpadding="0"><tbody><tr><td class="bgcorner1"></td><td class="pobg1"></td><td class="bgcorner2"></td></tr><tr><td class="pobg4"></td><td><div class="popoutContent" id="cardPopContent"></div></td><td class="pobg2"></td></tr><tr><td class="bgcorner4"></td><td class="pobg3"></td><td class="bgcorner3"></td></tr></tbody></table>';
				document.body.appendChild(this.obj);
			}
			//初始隐藏
			this.obj.style.visibility="hidden";
			
			this.popContent=getObj("cardPopContent");
			
			addEvent(this.obj, "mouseover", function(e){
				if (!self.dock) {
					self.dock = true;
					self.obj.style.visibility="visible";
				}
			})
			addEvent(this.obj, "mouseout", function(e){
				var ele = e.relatedTarget || e.toElement;
				if (!self.contains(self.obj, ele) && self.dock) {
					self.hide();
					self.dock = false;
				}
			})
			this.create(o);
			
		},
		"medalAni":function(){
			if(!this.prev||!this.next){
				return false;
			}
			var self=this;
			var lis=this.medalList.getElementsByTagName("li");
			var gw=lis.length*self.liWidth;
			this.medalList.style.width=gw+"px";
			var maxPan=(gw%self.wrapWidth!=0)?Math.floor(gw/self.wrapWidth)*self.wrapWidth:gw-self.wrapWidth;//最大允许偏移量
			var timer;
			var moveTo=function(elem,final_x,final_y,interval){
				var xpos=parseInt(getStyle(self.medalList,"left"));
				self.prev.className="card_pre";
				self.next.className="card_next";
				if(xpos==final_x){
					return false;
				}
				if(xpos<final_x){
					var dist=Math.ceil((final_x-xpos)/5);
					xpos=xpos+dist;
				}
				if(xpos>final_x){
					var dist=Math.ceil((xpos-final_x)/5);
					xpos=xpos-dist;
				}
				if(xpos>=0){
					elem.style.left="0px";
					self.prev.className="card_pre_old";
					self.next.className="card_next";
					return false;
				}
				if(xpos<=-maxPan){
					elem.style.left=-maxPan+"px";
					self.prev.className="card_pre";
					self.next.className="card_next_old";
					return false;
				}	
				
				elem.style.left=xpos+"px";
				timer=setTimeout(function(){
					moveTo(elem,final_x,final_y,interval);
				},interval);
			}
			addEvent(this.prev,"click",function(){
				var xpos=Math.abs(parseInt(getStyle(self.medalList,"left")));
				var width=self.liWidth*self.medalNum
				if(xpos%width!=0){
					return false;
				}
				if(timer){
					clearTimeout(timer);
				}
				var currLeft=parseInt(getStyle(self.medalList,"left"));
				var final_x=currLeft+self.liWidth*self.medalNum;
				moveTo(self.medalList,final_x,0,40)
			})
			addEvent(this.next,"click",function(){
				var xpos=Math.abs(parseInt(getStyle(self.medalList,"left")));
				var width=self.liWidth*self.medalNum
				if(xpos%width!=0){
					return false;
				}
				if(timer){
					clearTimeout(timer);
				}
				var currLeft=parseInt(getStyle(self.medalList,"left"));
				var final_x=currLeft-self.liWidth*self.medalNum;
				moveTo(self.medalList,final_x,0,40)
			})
		},
		"contains": function(a, b){
			//元素a是否包含元素B
			if (document.compareDocumentPosition) {
				return !!(a.compareDocumentPosition(b) & 16);
			}
			else {
				return a !== b && (a.contains ? a.contains(b) : true);
			}
		},
		"updatePos": function(ele){
			//更新名片位置
			this.obj.style.visibility="visible";
			var dd = document.documentElement;
			var db = document.body;
			var stop = dd.scrollTop + db.scrollTop
			var sleft = dd.scrollLeft + db.scrollLeft;
			var cw = dd.clientWidth;
			var ch = dd.clientHeight;
			var bound = ele.getBoundingClientRect();
			var left = bound.left;
			var top = bound.top;
			var h = ele.offsetHeight;
			var w = ele.offsetWidth;
			var oh = this.obj.offsetHeight;
			var ow = this.obj.offsetWidth;
			this.obj.style.left = (left + ow) > cw ? (sleft + left + w - ow + "px") : (sleft + left + "px");
			this.obj.style.top = (top + oh + h) > ch ? (stop + top - oh + "px") : (stop + top + h + "px");
		},
		"hide": function(){
			//关闭(隐藏)名片
			this.obj.style.visibility="hidden";
		},
		"create": function(ele){
			//构建名片内容
			var self=this;
			var key=ele.getAttribute("data-card-key");
			//除去后顾之忧
			if(!key){
				key=ele.getAttribute("data-card-url");
			}
			var data=this.data;
			if(data.length>0){
				for(var i=0,len=data.length;i<len;i++){
					if(data[i].k==key){
						this.fillCont(ele,data[i].v);
						return false;
					}
				}
			}
			this.getData(ele,function(json){
				var status=json.status||"";
				if(status!="success"){
					return false;
				}
				self.fillCont(ele,json);
				self.data.push({"k":key,"v":json})
			})
			
		},
		"fillCont":function(ele,json){
				var uid=json.uid||"";
				var username=json.username||"";
				var icon=json.icon||"images/face/none.gif";
				var memtitle=json.memtitle||"";
				var viewTid=json.viewTid||"";
				var viewFid=json.viewFid||"";
				var genderClass=json.genderClass||"";
				var attention=json.attention||"";
				var memberTags=json.memberTags||[];
				var online=json.online == 1 ? "在线" : "离线";
				var mine=json.mine||"";
				var medals=json.medals||[];
				var displayLen=this.wrapWidth/this.liWidth;//可显示的图标个数
				var gender = genderClass == 1 ? 'man' : 'women';
				var isonline = json.online == 1 ? '_ol' : '_unol';
				var genderclass = gender+isonline;
				//判断它正在看
				var readHtml="";
				if(viewTid!=""){
					readHtml='<a href="read.php?tid='+(viewTid||'')+'" title="Ta正在看" class="w s4" target="_blank">(Ta正在看...)</a>';
				}
				else if(viewFid!="") {
					readHtml='<a href="thread.php?fid='+(viewFid||'')+'" title="Ta正在看" class="w s4" target="_blank">(Ta正在看...)</a>';
				}
				//组合标签
				var tagHtml=[];
				var tagLen=memberTags.length;
				for(var i=0,len=tagLen;i<len;i++){
					var _tid=memberTags[i].tagid;
					var _tname=memberTags[i].tagname;
					var _tagclass = memberTags[i].className;
					tagHtml.push('<a href="u.php?a=friend&type=find&decode=1&according=tags&step=2&f_keyword='+encodeURIComponent(_tname)+'" class='+_tagclass+' target="_blank">'+_tname+'</a>');
				}
				if(tagLen>3){
					tagHtml.push('<a href="u.php?uid='+uid+'" title="查看更多"  target="_blank">...</a>');
				}
				tagHtml = (tagHtml == "") ? "还未设置个性标签哦" : tagHtml.join("，");
				//勋章
				var medalsHtml=[];
				var medalsLen=medals.length;
				for(var i=0,len=medalsLen;i<len;i++){
					var _mclass = medals[i].isuser == 1 ? 'open' : '';
					var _smallimage=medals[i].smallimage;
					var _mname=medals[i].name;
					medalsHtml.push('<li class='+_mclass+'><a href="apps.php?q=medal" target="_blank"><img src="'+_smallimage+'" wihdth="30" height="30" title="'+_mname+'"></a></li>');
				}

				var attentionHtml=[];
				attentionHtml = mine ? '<a href="javascript:void(0)" onclick="'+(attention==1?'':'Attention.add(this,'+uid+',1)')+'" class="'+(attention==1?'add_following':'s4 add_follow')+' mr10">'+(attention==1?'已关注':'加关注')+'</a><span class="gray2 mr10">|</span><a href="javascript:void(0)" onclick="sendmsg(\'pw_ajax.php?action=msg&touid='+uid+'\', \'\', this)" class="s4">发消息</a>' : '';
				//勋章html
				var medalHtml='<div class="popBottom">\
						'+((medalsLen>displayLen)?'<div class="card_page"><a href="javascript:void(0)" class="card_pre_old" id="card_pre">上一组</a><a href="javascript:void(0)" class="card_next" id="card_next">下一组</a></div>':'')+'\
								<div class="card_medal_wrap">\
									<ul class="cc" id="cardMedal">\
										'+(medalsHtml.join(""))+'\
									</ul>\
								</div>\
							</div>';			
				var fragment = document.createDocumentFragment();
				var card = document.createElement("div");
				card.innerHTML = '<div class="card_small"><dl class="cc">\
						<dt><a href="u.php?uid='+uid+'" target="_blank"><img src="'+icon+'" width="48" height="48" title="'+username+'"></a></dt>\
						<dd>\
							<span class="fr gray w">'+memtitle+'</span>\
							<span class="'+genderclass+' mr5" title="'+online+'" alt="'+online+'" >在线</span><a href="u.php?uid='+uid+'" class="b mr5" target="_blank">'+username+'</a>'+readHtml+'\
							<p><span class="w">标签：</span>'+tagHtml+'</p>\
							<p class="tar">'+attentionHtml+'</p>\
						</dd>\
					</dl>\
					'+(medalsLen>0?medalHtml:'')+'</div>';
				fragment.appendChild(card);
				//return fragment;
				this.popContent.innerHTML="";//fix ajax multi addEvent  
				this.popContent.appendChild(fragment);
				this.medalList=getObj("cardMedal");
				this.prev=getObj("card_pre");
				this.next=getObj("card_next");
				this.updatePos(ele);
				this.medalAni();
		},
		"getData":function(ele,callback){
			var url=ele.getAttribute("data-card-url");
			ajax.send(url,"",function(){
				if(callback){
					var data=ajax.request.responseText;
					callback(eval("("+data+")"));
				}
			})
		}
	}
	win.onReady(function(){
			var goTop=new scrollBar();
				goTop.init();
				win.goTop=goTop;
			new usercard().init();
	});
	/*新功能气泡
	*new Bubble(a).init();
	*a:页面中的气泡元素ID  如: [{"name":"pw_all_tip_kmd","id":"markoperation"},{"name":"pw_all_tip_medal","id":"applicationcenter"}]
	*/
	win.Bubble=function(ids){
		this.ids=ids;
		this.info={};//程序输出的气泡ID，表示不显示此气泡
		this.uid=(typeof winduid!="undefined")?winduid:null;
	}
	Bubble.prototype={
		init:function(){
			if(this.uid==null){
				return false;
			}
			var self=this;
			if(this.ids.length<1){
				return false;
			}
			if(typeof userBubbleInfo!="undefined"){
				this.info=userBubbleInfo;
			}
			var elems=[];
			for(var i=0,len=this.ids.length;i<len;i++){
				var name=this.ids[i].name;
				var id=this.ids[i].id;
				//判断该层是否可以显示
				if(this.info[name]!=undefined){
					continue;
				}
				if(!getObj(name)){
					continue;
				}
				if(!getObj(id)){
					continue;
				}
				//气泡元素
				var obj=getObj(name);
				//参照ID
				var ele=getObj(id);
				elems.push({k:name,v:obj,ele:ele});
			}
			if(elems.length>0){
				var bubbleBox=elems[0].v;
				var posBox=elems[0].ele;
				var left=posBox.getBoundingClientRect().left;
				var top=posBox.getBoundingClientRect().top;
				bubbleBox.style.display="block";
				bubbleBox.style.position="absolute";
				bubbleBox.style.left=left+"px";
				bubbleBox.style.top=top+"px";
				
				var btns=bubbleBox.getElementsByTagName("a");
				for(var i=0,len=btns.length;i<len;i++){
					var btn=btns[i];
					(function(ele,obj,id){
						addEvent(ele,"click",function(){
							self.hide(obj,id,ele);
							return false;
						})
					})(btn,bubbleBox,elems[0].k)
				}
			}
		},
		close:function(obj){
			obj.style.display="none";
		},
		hide:function(obj,str,ele){
			ajax.send("pw_ajax.php?action=bubble&uid="+this.uid+"&sign="+str,"",function(){
				var href=ele.getAttribute("href");
				if(href&&href.indexOf("javascript")==-1){
					location.href=href;
				}
			});
			this.close(obj);
			return false;
		}
	}
})(window,document);