var agt = navigator.userAgent.toLowerCase();
var is_ie = ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));
var is_gecko= (navigator.product == "Gecko");
var is_webkit=agt.indexOf('webkit')>-1;
var is_safari = (agt.indexOf('chrome')==-1)&&is_webkit;
var is_ie6 = is_ie && /msie (\d)\./.test(agt) && parseInt(RegExp.$1) < 7;
if(is_ie6){
	try{ 
		document.execCommand("BackgroundImageCache", false, true);
	}catch(e){}
}
//comatibility.js
(function () {
if(!window.opera && !is_ie){
	if (window.Event) {
		var ep = Event.prototype;
		ep.__defineSetter__("returnValue", function(b) {
			if (!b) this.preventDefault();
			return b
		});
		ep.__defineSetter__("cancelBubble", function(b) {
			if (b) this.stopPropagation();
			return b
		});
		ep.__defineGetter__("srcElement", function() {
			var node=this.target;
			while(node && node.nodeType != 1) node = node.parentNode;
			return node
		})
	}
	if (window.HTMLElement) {
		var hp = HTMLElement.prototype;
		function _attachEvent(o,e,h) {
			e=/^onmousewheel$/i.test(e)?"DOMMouseScroll":e.replace(/^on/i,"");
			return o.addEventListener(e, h, false)
		}		
		hp.attachEvent = function(e, h) {
			return _attachEvent(this,e,h)
		};
		window.attachEvent = function(e, h){
			return _attachEvent(window, e, h)
		};
		document.attachEvent = function(e, h){
			return _attachEvent(window, e, h)
		};
		function _detachEvent(o, e, h) {
			e=/^onmousewheel$/i.test(e)?"DOMMouseScroll":e.replace(/^on/i,"");
			return o.removeEventListener(e, h, false)
		}
		hp.detachEvent = function(e, h){
			return _detachEvent(this, e, h)
		};
		window.detachEvent = function(e, h){
			return _detachEvent(window, e, h)
		};
		document.detachEvent = function(e, h){
			return _detachEvent(window, e, h)
		};
		hp.__defineGetter__("children", function() {
			var tmp = [];
			var j = 0;
			var n;
			for (var i=0; i<this.childNodes.length; i++) {
				n = this.childNodes[i];
				if (n.nodeType == 1) {
					tmp[j++] = n;
					if (n.name) {
						if (!tmp[n.name])tmp[n.name] = [];
						tmp[n.name][tmp[n.name].length] = n
					}
					if (n.id) tmp[n.id] = n
				}
			}
			return tmp
		});
		hp.__defineGetter__("currentStyle", function() {
			return this.ownerDocument.defaultView.getComputedStyle(this, null)
		});
		hp.__defineSetter__("outerHTML", function(sHTML) {
			var r = this.ownerDocument.createRange();
			r.setStartBefore(this);
			var df = r.createContextualFragment(sHTML);
			this.parentNode.replaceChild(df, this);
			return sHTML
		});
		hp.__defineGetter__("outerHTML", function(){
			var attr;
			var attrs=this.attributes;
			var str="<"+this.tagName;
			for(var i=0;i<attrs.length;i++){
				attr=attrs[i];
				if(attr.specified)str+=" "+attr.name+'="'+attr.value+'"'
			}return str+">"+this.innerHTML+"</"+this.tagName+">"
		});
		hp.__defineSetter__("innerText",function (sText){
			var parsedText=document.createTextNode(sText);
			this.innerHTML=parsedText;
			return parsedText
		});
		hp.__defineGetter__("innerText",function (){
			var r=this.ownerDocument.createRange();
			r.selectNodeContents(this);
			return r.toString ()
		});
	
		hp.__defineGetter__("uniqueID",function (){
			if(!this.id){
				this.id="control_"+parseInt(Math.random()*1000000)
			}
			return this.id
		});
		hp.setCapture=function (){
			document.onselectstart=function (){
				return false
			};
			window.captureEvents(Event.MOUSEMOVE|Event.MOUSEUP)
		};
		hp.releaseCapture=function (){
			document.onselectstart=null;
			window.releaseEvents(Event.MOUSEMOVE);
			window.releaseEvents(Event.MOUSEUP)
		}
	}
}
})();
document.write("<script src='js/lang/zh_cn.js'></sc"+"ript>");
var gIsPost = true;
var getObj = function(id){
	if(!id){
		return false;
	}
	return document.getElementById(id);
};
if (location.href.indexOf('/simple/') != -1) {
	getObj('headbase')?getObj('headbase').href = location.href.substr(0,location.href.indexOf('/simple/')+1):0;
} else if (location.href.indexOf('.html')!=-1 && 0) {
	var base = location.href.replace(/^(http(s)?:\/\/(.*?)\/)[^\/]*\/[0-9]+\/[0-9]{4,6}\/[0-9]+\.html$/i,'$1');
	if (base != location.href) {
		getObj('headbase')?getObj('headbase').href = base:0;
	}
}
//通用domready
(function(win,doc){
	var dom = [];
	dom.isReady  = false;
	win.onReady = function(fn){
		if ( dom.isReady ) {
			fn()
		} else {
			dom.push( fn );
		}
	}
	dom.fireReady = function() {
		if ( !dom.isReady ) {
			if ( !doc.body ) {
				return setTimeout( dom.fireReady, 16 );
			}
			dom.isReady = 1;
			if ( dom.length ) {
				for(var i = 0, fn;fn = dom[i];i++)
					fn()
			}
		}
	}
	if ( doc.readyState === "complete" ) {
		dom.fireReady();
	}else if(-[1,] ){
		doc.addEventListener( "DOMContentLoaded", function() {
	  		doc.removeEventListener( "DOMContentLoaded",  arguments.callee , false );
	  		dom.fireReady();
		}, false );
	}else {
		doc.attachEvent("onreadystatechange", function() {
		  if ( doc.readyState == "complete" ) {
			doc.detachEvent("onreadystatechange", arguments.callee );
			dom.fireReady();
		  }
	});
	(function(){
		if ( dom.isReady ) {
			return;
		}
		var node = new Image();
		try {
			node.doScroll();
			node = null;
		} catch( e ) {
			setTimeout( arguments.callee, 64 );
			return;
		}
	  	dom.fireReady();
	})();
  }
})(window,document);

(function(win){
	/**
	 *通用事件处理函数
	 */
	// http://dean.edwards.name/weblog/2005/10/add-event/
	win.addEvent = function(element, type, handler) {
		if (element.addEventListener) {
			element.addEventListener(type, handler, false);
		} else {
			if (!handler.$$guid) handler.$$guid = addEvent.guid++;
			if (!element.events) element.events = {};
			var handlers = element.events[type];
			if (!handlers) {
				handlers = element.events[type] = {};
				if (element["on" + type]) {
					handlers[0] = element["on" + type];
				}
			}
			handlers[handler.$$guid] = handler;
			element["on" + type] = handleEvent;
		}
	};
	addEvent.guid = 1;
	function removeEvent(element, type, handler) {
		if (element.removeEventListener) {
			element.removeEventListener(type, handler, false);
		} else {
			if (element.events && element.events[type]) {
				delete element.events[type][handler.$$guid];
			}
		}
	};
	function handleEvent(event) {
		var returnValue = true;
		event = event || fixEvent(((this.ownerDocument || this.document || this).parentWindow || window).event);
		var handlers = this.events[event.type];
		for (var i in handlers) {
			this.$$handleEvent = handlers[i];
			if (this.$$handleEvent(event) === false) {
				returnValue = false;
			}
		}
		return returnValue;
	};
	function fixEvent(event) {
		event.preventDefault = fixEvent.preventDefault;
		event.stopPropagation = fixEvent.stopPropagation;
		return event;
	};
	fixEvent.preventDefault = function() {
		this.returnValue = false;
	};
	fixEvent.stopPropagation = function() {
		this.cancelBubble = true;
	};
})(window);
/**
 *验证码的，点其他地方消失的事件添加。
 */
function PW_popEvent (obj){
	if (!obj){return false;}
	//判断a元素是否包含b元素
	var contains = document.compareDocumentPosition ? function(a, b){
		return !!(a.compareDocumentPosition(b) & 16);
	} : function(a, b){
		return a !== b && (a.contains ? a.contains(b) : true);
	};
   document.body.onmousedown=function(e) {
		var e = window.event || e,
			elem = e.srcElement || e.target;
		if(!contains(obj,elem) && elem.id!=='showpwd'){
			obj.style.display = 'none';
		}
	};
}
function getElementsByClassName (className, parentElement){
	if (parentElement && typeof(parentElement)=='object') {
		var elems = parentElement.getElementsByTagName("*");
	} else {
		var elems = (document.getElementById(parentElement)||document.body).getElementsByTagName("*");
	}
	var result=[];
	for (i=0; j=elems[i]; i++) {
	   if ((" "+j.className+" ").indexOf(" "+className+" ")!=-1) {
			result.push(j);
	   }
	}
	return result;
}
var contains = document.compareDocumentPosition ? function(a, b){
		return !!(a.compareDocumentPosition(b) & 16);
	} : function(a, b){
		return a !== b && (a.contains ? a.contains(b) : true);
	};

function ietruebody() {
	return (document.compatMode && document.compatMode!="BackCompat" && !is_webkit)? document.documentElement : document.body;
}
function getTop() {
	return window.pageYOffset || ietruebody().scrollTop;
}
function getLeft() {
	return window.pageXOffset || ietruebody().scrollLeft;
}
function IsElement(id) {
	return document.getElementById(id) != null ? true : false;
}
function CopyCode(obj) {
	if (typeof obj != 'object') {
		if (is_ie) {
			if(window.clipboardData.setData("Text",obj)){
				alert('复制成功！');
			}
		} else {
			prompt('按Ctrl+C复制内容', obj);
		}
	} else if (is_ie) {
		var lis = obj.getElementsByTagName('li'), ar = [];
		for(var i=0,l=lis.length; i<l; i++){
			ar.push(lis[i].innerText);
		}
		if(window.clipboardData.setData('Text', ar.join("\r\n") ) ){
			alert('复制成功！');
		}
	} else {
		function openClipWin(){
			var lis = obj.getElementsByTagName('li'), ar = [];
			for(var i=0,l=lis.length; i<l; i++){
				ar.push(lis[i].textContent);
			}
			window.clip = new ZeroClipboard.Client();
			clip.setHandCursor( true );
			
			clip.addEventListener('complete', function (client, text) {
				alert("复制成功!" );
				closep();
			});
			clip.setText(ar.join("\r\n"));
			var clipEle = getObj('clipWin');
			if (!clipEle){
				var clipEle = document.createElement('div');
				clipEle.innerHTML = '<div class="popout"><table border="0" cellspacing="0" cellpadding="0"><tbody><tr><td class="bgcorner1"></td><td class="pobg1"></td><td class="bgcorner2"></td></tr><tr><td class="pobg4"></td><td><div class="popoutContent">\
<div class="p10"><a href="javascript:closep();" class="adel">关闭</a>提示</div><div class="popBottom"><span class="btn2"><span><button type="button">点击这里复制代码</button></span></span></div></div></td><td class="pobg2"></td></tr><tr><td class="bgcorner4"></td><td class="pobg3"></td><td class="bgcorner3"></td></tr></tbody></table></div>';
				//clipEle.innerHTML = '<p id="d_clip_button">提示</p>';
				clipEle.style.display = 'none';
				document.body.appendChild(clipEle);
			}
			read.open(clipEle, null, 2);
			var btn = getObj('pw_box').getElementsByTagName('button')[0];
			clip.glue(btn);
			//clip.glue( 'd_clip_button', 'd_clip_container' );
		}//彈窗
		
		if (!window.clip){
			var script = document.createElement('script');
			script.src = 'js/ZeroClipboard.js';
			script.onload = function(){
				ZeroClipboard.setMoviePath( 'js/ZeroClipboard.swf' );
				openClipWin();
			};
			document.body.appendChild(script);
		}else{
			openClipWin();
		}
	}
	return false;
}
function Addtoie(value,title) {
	try{
		is_ie ? window.external.AddFavorite(value,title) : window.sidebar.addPanel(title,value,"");
	  }catch(err){
	    txt = "1、抱歉，您的IE注册表值被修改，导致不支持收藏，您可按照以下方法修改。\n\n"
	    txt += "2、打开注册表编辑器，右键点击HKEY_CLASSES_ROOT查找'C:/\WINDOWS/\system32/\shdocvw.dll'。 \n\n"
	    txt += "3、点击(默认)，把'C:/\WINDOWS/\system32/\shdocvw.dll'修改为'C:/\WINDOWS/\system32/\ieframe.dll'，重启IE浏览器。\n\n"
		is_ie ? alert(txt) : alert("抱歉，您的浏览器不支持，请使用Ctrl+D进行添加\n\n")
	  }
}

var ifcheck = true;
function CheckAll(form,match) {
	for (var i = 0,j = form.elements.length; i < j; i++) {
		var e = form.elements[i];
		if (e.type == 'checkbox' && (typeof match == 'undefined' || e.name.match(match))) {
			e.checked = ifcheck;
		}
	}
	ifcheck = ifcheck == true ? false : true;
}

function showcustomquest(qid,id){
	var id = id || 'customquest';
	getObj(id).style.display = qid==-1 ? '' : 'none';
}
function showCK(){
	var ckcode = getObj('ckcode');
	ckcode.style.display="";
	ckcode.style.zIndex="1000000";
	if (ckcode.src.indexOf('ck.php') == -1) {
		ckcode.src = 'ck.php?nowtime=' + new Date().getTime();
	}
}
function setTab(m,n){
	var tli=document.getElementById("menu"+m).getElementsByTagName("li");
	var mli=document.getElementById("main"+m).getElementsByTagName("div");
	for(var i=0,j=tli.length;i<j;i++){
		tli[i].className=i==n?"hover":"";
		mli[i].style.display=i==n?"block":"none";
	}
}
function changeState() {
	var msg = ajax.request.responseText;
	if (msg == 1) {
		getObj('stealth').className = '';
		getObj('iconimg').title = HEADER_HIDE;
		getObj('online_state').innerHTML = '<img src="'+IMG_PATH+'/stealth.png" align="absmiddle" alt="隐身" />隐身';
	} else {
		getObj('stealth').className = 'hide';
		getObj('iconimg').title = HEADER_ONLINE;
		getObj('online_state').innerHTML = '<img src="'+IMG_PATH+'/online.png" align="absmiddle" alt="在线" />在线';
	}
}
function showcustomquest_l(qid){
	getObj('customquest_l').name = 'customquest';
	getObj('customquest_l').style.display = qid==-1 ? '' : 'none';
}

function checkinput(obj,val){
	if (obj.className.indexOf('gray')!=-1) {
		obj.value = '';
		obj.className = obj.className.replace('gray', 'black');
	} else if (val && obj.value=='') {
		obj.value = obj.defaultValue = val;
		if (obj.className.indexOf('black') == -1) {
			obj.className += ' gray';
		} else {
			obj.className = obj.className.replace('black', 'gray');
		}
	}
}
var mt;
function showLoginDiv(){
	mt = setTimeout(function(){
		read.open('user-login','show-login',2,26);
		getObj('pwuser').focus();
	},200);
	//mt = setTimeout('read.open(\'user-login\',\'show-login\',2,26);getObj(\'pwuser\').focus();',200);
	document.onmousedown = function (e) {
		var e = window.event || e;
		var o = e.srcElement || e.target;
		if (!issrc(o)) {
			read.close();
			document.onmousedown = null;
		}
	}
}
function issrc(o) {
	var k = 0;
	while (o) {
		if (o == read.menu) {
			return true;
		}
		if (o.tagName.toLowerCase() == 'body' || ++k>10) {
			break;
		}
		o = o.parentNode;
	}
	return false;
}

function imgResize(o, size) {
	if (o.width > o.height) {
		if (o.width > size) o.width = size;
	} else {
		if (o.height > size) o.height = size;
	}
	try{
		var next = getObj('next');
		var pre = getObj('pre');
		next.coords = '0 0 ' + ',' + o.width/2 + ',' + o.height;
		pre.coords = o.width/2 + ' 0 ' + ',' + o.width + ',' + o.height;
	}catch(e){}
}
function ajaxurl(o, ep) {
	if (typeof o == 'object') {var url = o.href;read.obj = o;} else {var url = o;}
	ajax.send(url + ((typeof ep == 'undefined' || !ep) ? '' : ep), '', ajax.get);
	return false;
}

function sendurl(o,id) {
	read.obj = o;
	sendmsg(o.href,'',id);
	return false;
}
function showAnnouce(){
	var annouce = getObj('annouce_div');
	annouce.style.display = annouce.style.display == 'none' ? '' : 'none';
}

function showCK(){
	var a = getObj('ckcode2') || getObj('ckcode');
	a.style.display="";
	if (a.src.indexOf('ck.php') == -1) {
		a.src = 'ck.php?nowtime=' + new Date().getTime();
	}
}
function showConInfo(uid,cyid){
	ajax.send('apps.php?q=group&a=uintro&cyid='+cyid+'&uid='+uid,'',ajax.get);
}

/*
userCard = {
	t1	 : null,
	t2	 : null,
	menu : null,
	//uids : '',
	data : {},
	init : function() {
		var els = getElementsByClassName('userCard');
		for (i = 0; i < els.length; i++) {
			if (els[i].id) {
				var sx = els[i].id.split('_');
				//userCard.uids += (userCard.uids ? ',' : '') + sx[3];
				els[i].onmouseover = function() {
					var _ = this;
					clearTimeout(userCard.t2);
					userCard.t1 = setTimeout(function(){userCard.show(_.id);}, 800);
				}
				els[i].onmouseout = function() {
					clearTimeout(userCard.t1);
					if (userCard.menu) userCard.t2 = setTimeout(function(){userCard.menu.close();},500);
				}
			}
		}
	},
	show : function(id) {
		var sx = id.split('_');
		if (typeof userCard.data[sx[3]] == 'undefined') {
			try {
				ajax.send(getObj('headbase').href + 'pw_ajax.php?action=showcard&uid=' + sx[3]+ '&rnd='+Math.random(), '', function() {
					userCard.data[sx[3]] = ajax.runscript(ajax.request.responseText);
					userCard.show(id);
				})
			} catch(e){}
			return;
		}
		userCard.menu ? 0 : userCard.menu = new PwMenu('userCard');
		userCard.menu.menu.style.zIndex = 9;
		userCard.menu.obj = getObj(sx[1] + '_' + sx[2]) || getObj(id);
		userCard.menu.setMenu(userCard.data[sx[3]], '', 1);
		userCard.menu.menupz(userCard.menu.obj,21);
	}
}
*/

var Class = function(aBaseClass, aClassDefine) {
	function class_() {
		this.Inherit = aBaseClass;
		for (var member in aClassDefine) {
			try{this[member] = aClassDefine[member];}catch(e){}		//针对opera,safri浏览器的只读属性的过滤
		}
	}
	class_.prototype = aBaseClass;
	return  new class_();
};
var New = function(aClass, aParams) {
	function new_()	{
		this.Inherit = aClass;
		if (aClass.Create) {
			aClass.Create.apply(this, aParams);
		}
	}
	new_.prototype = aClass;
	return  new new_();
};
/* end */

function imgLoopClass(){
	this.timeout   = 2000;
	this.currentId = 0;
	this.tmp       = null;
	this.wrapId    = 'x-pics';
	this.tag       = 'A';
	this.wrapNum   = 0;
	this.total     = 10;
}
imgLoopClass.prototype = {
	/*对象选择器*/
	$ : function(id){
		return document.getElementById(id);
	},
	/*图片显示*/
	display : function(pics,currentId){
		for(i=0,len=pics.length;i<len;i++){
			if(i==currentId){
				var current = pics[i];
			}
			pics[i].style.display = "none";
		}
		current.style.display = "";
	},
	/*获取所有标签对象*/
	gets : function(){
		var wrapId = this.wrapId+this.wrapNum;
		var obj = this.$(wrapId);
		if(!obj){
			return false;
		}
		return this.$(wrapId).getElementsByTagName(this.tag);
	},
	/*轮显*/
	alternate : function(){
		var pictures = this.gets();
		if(!pictures){
			return false;
		}
		var length = pictures.length;
		this.currentId = this.currentId ? this.currentId : 0;
		if(this.currentId+1>length){
			this.currentId = 0;
		}
		this.display(pictures,this.currentId);
		this.currentId = this.currentId+1;
	},
	/*循环器*/
	loop : function(){
		this.alternate();
		var _this = this;
		t = setTimeout(function(){
			_this.loop();
		},this.timeout);
	},

	/*单页面多个图片轮播，最多十个*/
	imginit : function(){
		for(i=0;i<this.total;i++){
			var obj = this.$(this.wrapId+i);
			if(!obj){
				continue;
			}
			imgloop(i);/*调用外部通用接口*/
		}
	},

	init : function(){

	}
}
/*图片轮播调用接口*/
var imgloops = new imgLoopClass();
/*特殊图片轮播调用*/
function imgloop(num){
	var imgloops = new imgLoopClass();
	imgloops.wrapNum = num;
	imgloops.loop();
}
/*任务中心弹出控制*/
showJobPOP = function(){
	var pop = getObj("jobpop") || 0;
	var newjob = getObj("newjob");
	if(newjob){
		var num = newjob.getAttribute("num");
		if(!num){
			window.location.href = "jobcenter.php";
			return false;
		}
	}
	if(pop){
		pop.style.display='';
	}else{
		openjobpop("&job=show");/*必须显示*/
	}
	return false;
}
/*弹出任务中心界面*/
function openjobpop(param){
	var param = param || '';
	ajax.send('pw_ajax.php?action=jobpop',param,function(){
		jobCenterRun(ajax.request.responseText);
	});
}
//所有的删除确定
function checkDel(sub,str){
	if(confirm(str))
		sub.form.submit();
}

function insertContentToTextArea(textAreaObj, codeText) {
	var startPostionOffset = codeText.length;
	textAreaObj.focus();
	if (document.selection) {
		var selection = document.selection.createRange();
		selection.text = codeText.replace(/\\r?\\n/g, '\\r\\n');
		selection.moveStart('character', - codeText.replace(/\\r/g,'').length + startPostionOffset);
		selection.moveEnd('character', - codeText.length + startPostionOffset);
		selection.select();
	} else if (typeof textAreaObj.selectionStart != 'undefined') {
		var prepos = textAreaObj.selectionStart;
		textAreaObj.value = textAreaObj.value.substr(0,prepos) + codeText + textAreaObj.value.substr(textAreaObj.selectionEnd);
		textAreaObj.selectionStart = prepos + startPostionOffset;
		textAreaObj.selectionEnd = prepos + startPostionOffset;
	}
}

function displayElement(elementId, isDisplay) {
	if (undefined == isDisplay) {
		getObj(elementId).style.display = getObj(elementId).style.display == 'none' ? '' : 'none';
	} else {
		getObj(elementId).style.display = isDisplay ? '' : 'none';
	}
}
function preview_img(id){
	var photype = getObj('p_'+id);
	if(getObj('q_'+id)){
		getObj('q_'+id).value = "";
	}
	var patn = /\.jpg$|\.jpeg$|\.png|\.bmp|\.gif$/i;
	if(patn.test(photype.value)){
		var Preview = getObj('preview_'+id);

		if (is_gecko || is_webkit) {
			Preview.src = photype.files[0].getAsDataURL();
		} else if (is_ie) {
			Preview.src="images/90.png";
			photype.select();
			var val = document.selection.createRange().text;
			Preview.filters.item("DXImageTransform.Microsoft.AlphaImageLoader").src = val;
			Preview.filters.item("DXImageTransform.Microsoft.AlphaImageLoader").sizingMethod = 'scale';
		}
	} else {
		showDialog('error','您选择的似乎不是图像文件。',2);
	}
}

var Attention = {
	add : function(obj, touid, recommend) {
		ajax.send('pw_ajax.php?action=addattention&touid=' + touid + (recommend ? '&recommend=' + recommend : ''), '', function() {
			var rText = ajax.request.responseText.split('\t');
			if (rText[0] == 'success') {
				obj.innerHTML = '关注中';
				obj.className = obj.className.replace('follow', 'following gray');
				obj.onclick = '';
				
				if (obj.name) {
					getObj(obj.name+'_'+touid).innerHTML = parseInt(getObj(obj.name+'_'+touid).innerHTML) + 1;
				}
				
			} else {
				ajax.guide();
			}
		});
		return false;
	},
	del : function(touid) {
		ajax.send('pw_ajax.php?action=delattention&touid=' + touid, '', function() {
			var rText = ajax.request.responseText.split('\t');
			if (rText[0] == 'success') {
				window.location.reload();
			} else {
				ajax.guide();
			}
		});
		return false;
	}
};

function getBaseUrl() {
	var baseURL = document.baseURI || getHeadBase() || document.URL;
	if (baseURL && baseURL.match(/(.*)\/([^\/]?)/)) {
		baseURL = RegExp.$1 + "/";
	}
	return baseURL;
}
function getHeadBase() {
	return getObj('headbase') ? getObj('headbase').href : null;
}
//获取元素精确样式
function getStyle(ele,style){
	if(document.defaultView){
		return document.defaultView.getComputedStyle(ele,null)[style];
	}else{
		return ele.currentStyle[style];
	}
}
//操作元素样式
function hasClass(obj,className){
		var classname = ' ' + className + ' ';
		var cname=obj.className;
		if ((' ' + cname + ' ').indexOf(classname) > -1) return true;
		return false;
}
function addClass(obj,className){
		if(hasClass(obj,className)){
			return false;
		}
		if(obj.className){
		obj.className+=" "+className;
		}else{
			obj.className=className;
		}
}
function removeClass(obj,className){
		if(!hasClass(obj,className)){
			return false;
		}
		var cname=' '+obj.className+' ';
		obj.className=cname.replace(' '+className+' ',"").replace(/^\s+|\s+$/,"");
}
function toggleClass(obj,className){
	  if(hasClass(obj,className)){
			removeClass(obj,className);
	  }else{
			addClass(obj,className);
	  }
}