function tmpdisplay(){
	this.close = 1 ;
	this.tout  = null;
	this.tmpdiv = null;
	this.tmpdiv = document.createElement('div');
	this.tmpdiv.style.position='absolute';
	this.tmpdiv.style.left='100px';
	this.tmpdiv.style.top='100px';
	this.tmpdiv.style.width="100px";
	this.tmpdiv.style.height="100px";
	this.tmpdiv.style.backgroundColor="transparent";
	this.tmpdiv.className='cc view-current view-bg';
	this.tmpdiv.style.display='none';
	document.body.appendChild(this.tmpdiv);
	
	//显示层
	this.disdiv=function(left,top,width,height,display) {
		this.tmpdiv.style.left=left+'px';
		this.tmpdiv.style.top=top+'px';
		this.tmpdiv.style.width=width+'px';
		this.tmpdiv.style.height=height+'px';
		this.tmpdiv.style.display=display;
	}
	
	//获取绝对路径
	this.getpos=function(o) {
		var rect = o.getBoundingClientRect(); 
		return {"x" : rect.left+ietruebody().scrollLeft, "y" : rect.top+ietruebody().scrollTop}; 

	}
}

var frontAdmin = Class({},
{
	_mode: 'area',
	Create: function (container,tag,mode) {
		tmpdis = new tmpdisplay;
		this.container = container;
		this.editClass = 'open-none';


		this.tag = tag;
		if (mode) {
			this._mode = mode;
		}
		this.current = null;
		this._init();
	},
	_init: function () {
		var list = getElementsByClassName(this.tag,this.container);
		var self = this;
		for (var i = 0; i< list.length; i++ ) {
			this._addEvent(list[i],'mouseover',this._mouseover);
			this._addEvent(list[i],'mouseout',this._mouseout);
		}
		//this._initEditLink();
		var configBox = document.createElement('div');
		configBox.innerHTML = '<div class="open-none" style="width:100%;height:22px;z-index:7;background:#666;filter:alpha(opacity=80);opacity:0.8;padding:0;margin:0"></div><div class="open-none"  style="padding:0;margin:0;overflow:hidden;text-align:left;text-indent:.5em;font:700 12px/22px a;color:#fff;height:22px;z-index:8;top:0;position:absolute"></div><span class="open-none" style="overflow:hidden;height:22px;font:700 12px/22px a;background:#F60;cursor:pointer;position:absolute;z-index:9;color:#FF0;padding:0 15px;margin:0;right:0;top:0;">管理</span>';
		configBox.style.position='absolute';
		configBox.style.display='none';
		configBox.id="configBox";
		configBox.onmouseover=function(){
			if(tmpdis.tout)
				clearTimeout(tmpdis.tout);
		};
		configBox.onmouseout=function(){
			if(tmpdis.tout) {
				clearTimeout(tmpdis.tout);
			}
			thisself = self._getElementsByClassName('view-current')[0];
			thisself.className = 'cc view-current';
			
			tmpdis.close=1;
			tmpdis.tout=setTimeout(function() {
				if(tmpdis.close == 1) {
					thisself.className = 'cc view-hover';
					tmpdis.disdiv(0,0,0,0,'none');
					getObj('configBox').style.display='none';
				}
			},500);
		};
		configBox.childNodes[2].onclick=this._initEditLink;
		document.body.appendChild(configBox);
	},
	_mouseover: function () {
		if(tmpdis.tout)
			clearTimeout(tmpdis.tout);
		if(this.className=='cc view-current')
			return;
		//修改大小位置
		var rect = this.getBoundingClientRect();
		var configBox = getObj('configBox');
		
		configBox.style.left = rect.left+ietruebody().scrollLeft-1+'px';
		configBox.style.top = rect.top+ietruebody().scrollTop-23+'px';
		configBox.style.width = this.clientWidth+2+'px';
		var altname = this.getAttribute('altname');
		var invokename = this.getAttribute('invokename');
		var channelId = this.getAttribute('channelid');
		configBox.childNodes[1].innerHTML=invokename;
		configBox.childNodes[2].id=altname;
		configBox.childNodes[2].channelId=channelId;
		configBox.style.display='';
		//显示出来
		var thispos=tmpdis.getpos(this);
		tmpdis.disdiv(thispos.x-1,thispos.y-1,this.offsetWidth,this.offsetHeight,'block');
		this.className = 'cc view-current';
		this.style.zIndex =2;
		tmpdis.close=0;
	},
	
	_initEditLink:	function () {
		frontAdmin._sendmsg((getObj('headbase') ? getObj('headbase').href : '') + 'mode.php?m=area&q=dialog&invokename='+encodeURI(this.id)+'&channelid='+this.channelId,'',this);//frontadmin
	},
	
	_sendmsg :function(url,data,id) {
		portalBox = new PwMenu('portalBox');
		portalBox.obj = (typeof id == 'undefined' || !id) ? null : getObj(id);
		//portalBox.guide();
		setTimeout(function(){ajax.send(url,data,function(){ajax.get(portalBox)});},100);
	},
	_mouseout: function () {
		if(tmpdis.tout) {
			clearTimeout(tmpdis.tout);
		}
		var thisself=this;
		this.className = 'cc view-current';
		tmpdis.close=1;
		tmpdis.tout=setTimeout(function() {
			if(tmpdis.close == 1) {
				thisself.className = 'cc view-hover';
				tmpdis.disdiv(0,0,0,0,'none');
				getObj('configBox').style.display='none';
			}
		},500);
		
	},
	_addEvent: function (el,evname,func) {
		var self = this;
		el['on'+evname]=function(){self._changView(el,func);}
		if(document.all) {
			el.attachEvent("on" + evname,function(){
				self._changView(el,func);
			});
		} else {
			el.addEventListener(evname,function(){
				self._changView(el,func);
			},true);
		}
	},
	_changView:function(el,func){
		if (this.current) {
			this.current.className = 'cc view-hover';
		}
		this.current = el;
		func.call(el);
	},
	
	_getElementsByClassName : function (className, parentElement) {
		if (typeof(parentElement)=='object') {
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
}
);

function countlen(element,num) {
	if (element.value.length>num){
		alert('超过字数限制');
		element.value=element.value.substring(0,num);
	}
	return true;
}

function addPush(invokepieceid,fid,loopid,channelid) {
	if (typeof(mode) == 'undefined') {
		var temp = frontAdmin._mode;
	} else {
		var temp = mode;


	}
	var url = "mode.php?m="+temp+"&q=frontadmin&action=addpush&invokepieceid="+invokepieceid+"&fid="+fid+"&loopid="+loopid+'&channelid='+channelid;
	var pushkey= 0;
	if (getObj('pushkey')) {
		pushkey = getObj('pushkey').value;
	}
	ajax.send(url,'pushkey='+pushkey,ajax.get);
}

function delPush(element,pushid,channelid) {
	if (!confirm("确定要删除此条目吗？")){
		return false;
	}
	var url = "mode.php?m=area&q=frontadmin&action=deletepush";
	ajax.send(url,'pushid='+pushid+'&channelid='+channelid,function () {
		if (ajax.request.responseText=='success') {
			delTr(element);
		} else {
			alert(ajax.request.responseText);
		}
	});
}

function pickReback(color){
	getObj('color_show').style.backgroundColor = color;
}
function styleOnclick(e,obj){
	var objclass = e.className;
	var temp = objclass.split(' ');
	var newclass = '';
	for (var n=0; n<temp.length; n++){
		if (temp[n]=='one') {
			continue;
		}
		newclass += ' ' + temp[n];
	}
	if (objclass.match(/one/)) {
		e.className = newclass;
		getObj(obj).value = '';
	} else {
		e.className = newclass + ' one';
		getObj(obj).value = 1;
	}
}
function colorCancel(){
	getObj('color_show').style.backgroundColor='#FFFFFF';
	getObj('title1').value='';
}