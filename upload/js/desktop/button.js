/**
 *include Compatibility.js core.js
 *按扭的基类，封装了一些最基本的方法和事件
 */
 ~
function()
{
    var ROOT = document.documentElement;
	var componentIndex=0
	var getObj=function(id){return document.getElementById(id);};
	var ce=function(tag){return document.createElement(tag);};
	PW.Button = baseClass.extend();
	var _BP=PW.Button.prototype;
	_BP.template="{text}";
	var skip=function(s,l)
	{
		//l=s.replace(/[^\x00-\xff]/g,'00').length>l?s=s.substr(0,3)+"<font size=1>...</font>":0;
		return s;
	};
	/**
	 *渲染按钮
	 */
	_BP.render=function(obj)
	{
		componentIndex++;
		if(getObj("button_"+(this.id||componentIndex)))
		{
			var btn=getObj("button_"+(this.id||componentIndex));
		}
		else
		{
			var btn=ce("li");
			btn.id="button_"+(this.id||componentIndex);
			//btn.className="btn-big btn-big-out";
			btn.className = "current";
			btn.title=this.text;
			//btn.innerHTML=this.template.replace(/\{text\}/g,document.all?this.text:skip(this.text,8)).replace(/\{id\}/g,this.id);
			btn.innerHTML=this.template.replace(/\{text\}/g,skip(this.text,8)).replace(/\{id\}/g,this.id);
			//btn.style.width=this.width+"px";
			(this.body||obj).appendChild(btn);
		}
		var _this=this;
		btn.onselectstart=function(){return false;};
		btn.onmouseover=function()
		{	
			if(_this.actived)
			{
				return;
			}
			//this.className="btn-big btn-big-over";
		};
		btn.onmouseout=function()
		{
			
			if(_this.actived)
			{
				return;
			}
			//this.className="btn-big btn-big-out";
		};
		this.element=btn;
		this._addClickEvent();
	};
	_BP._addClickEvent=function()
	{
		this.onclick?this.element.onclick=this.onclick:0;
	};
	_BP.onremove=function()
	{
	};
    _BP.remove=function()
	{
		$removeNode(this.element);
		this.onremove();
	};
} ();
	