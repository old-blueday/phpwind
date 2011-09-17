/**
 *include Compatibility.js core.js inherit from PW.Button
 *任务栏按钮类，继承自button基类
 */
 ~
function()
{
	PW.TaskButton = PW.Button.extend();
	var TASKBUTTONCOUNT=0;
	ACTIVEDBUTTON='';
	var _TP=PW.TaskButton.prototype;
	//_TP.template="<span class=skipText>{text}</span><img src=js/desktop/images/close.gif style=cursor:pointer;display:none;position:absolute;left:90px;top:7px>"; 
	_TP.template='<i><a href="javascript:;" hidefocus="true">{text}</a><a href="javascript:;" hidefocus="true" class="del"></a></i>';
	//_TP.onfocus=function(){};
	//_TP.onblur=function(){this.actived=false;this.element.className="btn-big btn-big-out";};
	_TP.focus=function()
	{
		 this.element.lastChild.style.display="";
		 if(ACTIVEDBUTTON&&ACTIVEDBUTTON!=this)
		{
			 ACTIVEDBUTTON.blur();
			 ACTIVEDBUTTON.element.className="";
		}
		 this.actived=true;
		 ACTIVEDBUTTON=this;
		 ACTIVEDBUTTON.element.className="current";
		 this.onfocus();
	};
	_TP.blur=function()
	{
		if(this.element.lastChild)
		{
			 //this.element.lastChild.style.display="none";
			 
			 this.onblur();
		}
	};
	_TP.onbeforeremove=function(){};
	_TP.remove=function()
	{
		this.onbeforeremove();
		$removeNode(this.element);
		this.onremove();
		TASKBUTTONCOUNT--;
	};
	_TP.close=function()
	{
		this.remove();
	};
	_TP.oncontextmenu=function()
	{

	};
	_TP._addClickEvent=function()
	{
		TASKBUTTONCOUNT++;
		this.focused=true;
		var _this=this;
		this.onclick?this.element.onclick=function(){_this.onclick()}:0;
		this.element.oncontextmenu=function()
		{
			_this.oncontextmenu({clientX:event.clientX,clientY:event.clientY});
			return false;
		};
		addEvent(this.element,"mouseover",function()
		{
			//_this.element.lastChild.style.display="";
		});
		addEvent(this.element,"mouseout",function()
		{
			//_this.element.lastChild.style.display="none";
		});
		/* 删除button */
		this.element.lastChild.lastChild.onclick=function(){
			_this.remove();
			event.cancelBubble=true;
		};
	};
} ();
	