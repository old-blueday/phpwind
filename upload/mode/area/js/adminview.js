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
var G_MODE_SWITCH=0;//全局模块管理开关
var portalBox;
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
		if(!G_MODE_SWITCH){
			return false;
		}
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
		if(!G_MODE_SWITCH){
			return false;
		}
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
//管理模块=========
(function(){
	var ChannelManage=function(status,url,ns,mode){//mode 1门户 0其他(不显示刷新和查看)
			//创建开关
			this.handler=document.createElement("div");
			this.handler.innerHTML='<div id="pw_divTop"></div><a href="javascript:;">模块管理</a>';
			this.handler.id="J_channelManage";
			this.handler.className="pw_diy";
			//创建控制面板
			this.console=document.createElement("div");
			this.console.id="consolePanel";
			this.console.style.display="none";
			var html=['<a href="javascript:void(0)" id="console_close" class="fr">关闭</a><span class="mr20">目前处于模块管理模式，你可以<a id="console_showAll" href="javascript:void(0)">显示所有模块</a>便于查找隐藏模块</span>','<span class="bt2" id="console_refresh"><span><button type="button">刷新静态</button></span></span><span class="bt2" id="console_showStatic"><span><button type="button">查看静态</button></span></span>'];
			this.console.innerHTML=mode?html.join(" "):html[0];
			document.body.appendChild(this.handler);
			document.body.appendChild(this.console);
			this.closeBtn=document.getElementById("console_close");
			this.showAllBtn=document.getElementById("console_showAll");
			this.refreshBtn=mode?document.getElementById("console_refresh"):null;
			this.staticBtn=mode?document.getElementById("console_showStatic"):null;
			this.modes=this.getElementsByClassName("view-hover");
			this.defaultStatus=status;//控制台默认状态 0(默认) 1:显示控制台  2:显示控制台和模块
			this.staticUrl=url;
			this.namespace=ns;
			this.mode=mode;
	}
	ChannelManage.prototype={
		"init":function(){
			var that=this;
			if(that.defaultStatus==1){
				this.show();
			}
			if(this.defaultStatus==2){
				this.defaultStatus=1;
				this.show(1);
			}
			this.addEvent(this.handler,"click",function(){
				if(getStyle(that.console,"display")=="none"){
					that.show();
				}else{
					that.hide(1);
				}
			});
			this.addEvent(that.closeBtn,"click",function(){
				that.hide(1);
			})
			this.addEvent(that.showAllBtn,"click",function(){
				that.toggleModes();
			})
			if(this.mode){
				this.addEvent(that.refreshBtn,"click",function(){
					updateCache(that.namespace);//刷新静态
				})
				this.addEvent(that.staticBtn,"click",function(){
					location.href=that.staticUrl;
				})
			}
		},
		"esc":function(){
			var that=this;
			if(this.isEsc){
				return false;
			}else{
				this.isEsc=1;
				this.addEvent(document,"keyup",function(e){
					var e=e||window.event;
					var keyCode=e.which||e.keyCode;
					if(keyCode==27){
						that.hide(1);
					}
				})
			}
		},
		"show":function(all){//all:是否操作所有模块 1/0  下同
			G_MODE_SWITCH=1;
			this.console.style.display="";
			this.esc();
			if(all){
				this.toggleModes();
			}
		},
		"hide":function(all){
			G_MODE_SWITCH=0;
			this.console.style.display="none";
			if(all){
				this.defaultStatus=2;
				this.toggleModes();
			}
		},
		"toggleModes":function(){
			if(this.defaultStatus!=2){
				for(var i=0,len=this.modes.length;i<len;i++){
					this.modes[i].style.cssText="_height:16px;min-height:16px;display:block;background:#ffffdb;border:1px solid #F60;";
				}
			}else{
				for(var i=0,len=this.modes.length;i<len;i++){
					this.modes[i].style.cssText="";
				}
			}
			this.defaultStatus=this.defaultStatus==2?0:2;
			this.showAllBtn.innerHTML=this.defaultStatus==2?"关闭模块显示":"显示所有模块";
				
		},
		"addEvent":function(elem,type,fn){
			if (elem.addEventListener) {
				elem.addEventListener(type, fn, false);
			} else {
				elem['$e' + type + fn] = fn;
				elem[type + fn] = function(){elem['$e' + type + fn](window.event)};
				elem.attachEvent('on' + type, elem[type + fn]);
			};
		},
		"getElementsByClassName":function(classname,obj){
			var arr=[];
			 var eles=(obj?obj:document).getElementsByTagName("*");
			 for(var i=0,len=eles.length;i<len;i++){//遍历符合条件的元素
				  var ele=eles[i];
				  var classnames=ele.className;
				  if(classnames!=""&&classnames.replace(/\s+/g," ")!=" "){//如果该元素有classname并且不为空
					   var names=classnames.match(/[^\s]+/g);//将其classname保存在一个数组
					   if(!-[1,]){//<=ie8
							   for(var j=0,len2=names.length;j<len2;j++){
									  if(names[j]==classname){
										   arr.push(ele);
									  }
							   }
					   }else{
								 if(names.indexOf(classname)!=-1){//ie9 other
									  arr.push(ele);
								 }
					   }
				  }
			 }
			 return arr;
		}
	}
	window.ChannelManage=ChannelManage;
})();
/*
onReady(function(){
	//参数介绍：
	//--默认显示:0-只显示模块管理(默认)  1-显示控制栏  2-显示控制栏和全部模块
	//--查看静态url:URL
	//--刷新静态命名
	//--控制栏呈现模式:1-门户(默认) 0-其他,如论坛(不显示刷新和查看)
	var console=new ChannelManage(0,"http://www.phpwind.net","home85",1);
	console.init();
})
*/