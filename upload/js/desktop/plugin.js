if (!Array.prototype.indexOf){Array.prototype.indexOf = function(elt /*, from*/){var len = this.length;var from = Number(arguments[1]) || 0;from = (from < 0)? Math.ceil(from): Math.floor(from);if (from < 0)from += len;for (; from < len; from++){if (from in this && this[from] === elt)return from;}return -1;};}
if (!Array.prototype.forEach){Array.prototype.forEach = function(fun /*, thisp*/){var len = this.length;if (typeof fun != "function")throw new TypeError();var thisp = arguments[1];for (var i = 0; i < len; i++){if (i in this)fun.call(thisp, this[i], i, this);}};}
if (!Array.prototype.every){Array.prototype.every = function(fun /*, thisp*/) { var len = this.length; if (typeof fun != "function") throw new TypeError(); var thisp = arguments[1]; for (var i = 0; i < len; i++) {if (i in this && !fun.call(thisp, this[i], i, this)) return false;}return true; };}
Function.prototype.bind = function(context) {
  var fun = this;
  return function(){
    return fun.apply(context, arguments);
  };
};
var PW = {};
/**
 * 切换class
 */
PW.ClassBind = function(eles, className, bindhandle, callback)
{
	var l = eles.length;
	var handle = function(){
		this.className=className;
		callback && callback(this);
	};
	for(var i=0;i<l;i++)
	{
		eles[i]['on'+bindhandle] =  handle;
	}
};
/**
 * 滑动标签
 */
PW.Tab=function(cfg){
	var l = cfg.tabs.length;
	var handle = function(ele){
		for(var i=0;i<l;i++)
		{
			if(ele==cfg.tabs[i])
			{
				(cfg.container[i].style.display=='none')&&(cfg.container[i].style.display='');
			}else if(cfg.tabs[i].className=='current')
			{
				cfg.tabs[i].className='';
				cfg.container[i].style.display='none';
				
			}
		}
		cfg.callback && cfg.callback();
	};
	for(var i=1;i<l;i++)
	{
		cfg.container[i].style.display='none';
	}
	PW.ClassBind(cfg.tabs,'current', 'click', handle);
}
/**
 * 全选
 */
PW.selectAll=function(name,self)
{
	var ischeck = self.checked;
	var otherInputs = document.getElementsByName(name);
	var l = otherInputs.length;
	for(var i=0;i<l;i++)
		otherInputs[i].checked=ischeck;
}
PW.initSelectAll=function(params)
{
	var ar=[];
	for(var p in params)
	{
		ar.push([p,params[p]]);
	}
	ar.forEach(function(n){
		n[1].onclick=function(){PW.selectAll(n[0],n[1])};
		var otherInputs = document.getElementsByName(n[0]);
		var l = otherInputs.length;
		for(var i=0;i<l;i++)
			otherInputs[i].onclick=function(){
				PW.checkSelectAll(n[0],n[1]);
			};
	});
}
PW.checkSelectAll=function(name,handler)
{
	var otherInputs = document.getElementsByName(name);
	var l = otherInputs.length;
	for(var i=0;i<l;i++)
	{
		if(!otherInputs[i].checked)
		{
			handler.checked=false;
			return;
		}
	}
	handler.checked=true;
}
/**
 * 含文字的模块
 */
 function radioWithWords(self)
 {
	 var ele = self.getElementsByTagName('input')[0];
	 var nm  = ele.name;
	 ele.click();
	 //ele.checked=true;
	 //取消其它单选框的选择
	 var otherInputs = document.getElementsByName(nm);
	 var l = otherInputs.length;
	 for(var i=0;i<l;i++)
		if(otherInputs[i].parentNode.className=='current')
			otherInputs[i].parentNode.className='';

	 self.className = 'current';
 }