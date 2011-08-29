Function.prototype.bind = function(){  
  var fn = this, args = Array.prototype.slice.call(arguments), object = args.shift();  
  return function(){  
    return fn.apply(object,  
      args.concat(Array.prototype.slice.call(arguments)));  
  };  
};
if(typeof(HTMLElement)!="undefined")//给firefox定义contains()方法，ie下不起作用
{   
      HTMLElement.prototype.contains=function(obj)   
      {   
          while(obj!=null&&typeof(obj.tagName)!="undefind"){//通过循环对比来判断是不是obj的父元素
   　　　　if(obj==this) return true;   
   　　　　obj=obj.parentNode;
   　　}   
          return false;   
      };   
};
var sSelect=function(sel){
	if(sel)
	{
		this.sel = sel;
		this.init();
		return this;
	}else 
		return false;
};
sSelect.getElementsByClass = function(searchClass,node,tag) {
	var classElements = new Array();
	if ( node == null )
			node = document;
	if ( tag == null )
			tag = '*';
	var els = node.getElementsByTagName(tag);
	var elsLen = els.length;
	var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)");
	for (i = 0, j = 0; i < elsLen; i++) {
			if ( pattern.test(els[i].className) ) {
					classElements[j] = els[i];
					j++;
			}
	}
	return classElements;
};
var _MP=sSelect.prototype;
_MP.init=function()
{
	this.vir = document.createElement('span');
	this.vir.className="dropselectbox";
	this.vir.innerHTML='<div class="fl"><ul><li></li></ul></div><button type="button" onfocus="blur();"></button>';
	this.vir.getElementsByTagName('button')[0].innerHTML = this.getSelectedText();
	var opts='';
	this.sel.parentNode.insertBefore(this.vir,this.sel);
	this.vir.getElementsByTagName('button')[0].style.width = this.vir.style.width = this.sel.clientWidth+20+'px';
	this.sel.style.display='none';
	this.getSelectedText();
	this.vir.getElementsByTagName('button')[0].onclick=this.showOptions.bind(this);
	this.vir.style.display='';
};
_MP.getSelectedText=function(){
	var index = this.sel.selectedIndex;
	return this.sel.options[index].text;
};
_MP.showOptions=function(){
	var opts= '';
	var ul = this.vir.getElementsByTagName('ul')[0];
	ul.style.width = this.vir.clientWidth*((this.sel.length>>4)+1)+'px';
	var w = Math.floor(parseInt(ul.style.width)/((this.sel.length>>4)+1))+'px';
	for(var i=0;i<this.sel.length;i++)
	{
		opts += '<li style="width:'+w+';float:left;display:block;">'+this.sel.options[i].text+'</li>';
	}
	
	ul.innerHTML=opts;
	ul.getElementsByTagName('li')[this.sel.selectedIndex].className='over';
	ul.style.display='block';
	ul.onclick=_MP.select.bind(this);
	ul.onmouseover=_MP.mouseOver.bind(this);
	document.body.onmouseover= _MP.cancel.bind(this);
};
_MP.findValue=function(txt){
	for(var i=0;i<this.sel.length;i++)
	{
		if(txt==this.sel.options[i].text)
		{
			return this.sel.options[i].value;
		}
	}
	return null;
}
_MP.select=function(evt)
{
	evt = evt || window.event;
	var target = evt.target||evt.srcElement;
	if(target.tagName!='LI')
		return false;
	this.sel.value = this.findValue(target.innerHTML);
	this.sel.onchange && this.sel.onchange();
	this.vir.getElementsByTagName('button')[0].innerHTML=target.innerHTML;
	this.cancel(evt);
}
_MP.mouseOver=function(evt)
{
	evt = evt || window.event;
	var target = evt.target||evt.srcElement;
	_MP.stop(evt);
	
	if(target.tagName!='LI')
		return false;
	var selli=sSelect.getElementsByClass('over',this.vir,'li');
	if(selli.length>0)
		selli[0].className='';
	target.className='over';
}
_MP.cancel=function(evt)
{
	var ul = this.vir.getElementsByTagName('ul')[0];
	document.body.onmousemove=null;
	ul.style.display='none';
	ul.innerHTML='';
	
}
_MP.stop=function(e)
{
	e = e||window.event;  
	if(e.stopPropagation){  
		e.stopPropagation();  
	}else{  
		e.cancelBubble = true;
	}  
}