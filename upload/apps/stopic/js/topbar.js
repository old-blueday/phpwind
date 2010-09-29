Function.prototype.bind = function(){  
  var fn = this, args = Array.prototype.slice.call(arguments), object = args.shift();  
  return function(){  
    return fn.apply(object,  
      args.concat(Array.prototype.slice.call(arguments)));  
  };  
};
var Topbar = function(ele)
{
	//获取实际的位置值。
	this.bar = ele;
	this.origin = parseInt(this.getElementPos(ele).y);
	this.keeping = false;
	this.ie6=!(window.XMLHttpRequest);
	window.onscroll=this.onScroll.bind(this);
};
//置顶
var _WP = Topbar.prototype;
_WP.keepTop=function(){
	this.bar.style.top=0;
	this.bar.style.position=this.ie6?'relative':'fixed';
	return true;
};
_WP.reloadOrigin=function(){
	this.origin = parseInt(this.getElementPos(this.bar).y);
}
//恢复初始状态
_WP.turnOrigin=function(){
	this.bar.style.position='static';
	return;
};
_WP.onScroll=function(){
	if(Math.max(document.documentElement.scrollTop, document.body.scrollTop)>this.origin)
	{
		this.keeping = this.keeping||this.keepTop();
		this.ie6&&this.ie6Scroll();
	}
	else
	{
		this.keeping = this.keeping?this.turnOrigin():this.keeping;
	}
};
//ie6下的滚动方法
_WP.ie6Scroll=function(){
	this.bar.style.top = document.documentElement.scrollTop-this.origin+'px';
};
_WP.getElementPos=function(el){ 
    var ua = navigator.userAgent.toLowerCase(); 
    var isOpera = (ua.indexOf('opera') != -1); 
    var isIE = (ua.indexOf('msie') != -1 && !isOpera); // not opera spoof 
 
    if(el.parentNode === null || el.style.display == 'none')  
    { 
        return false; 
    } 
 
    var parent = null; 
    var pos = []; 
    var box; 
 
    if(el.getBoundingClientRect)    //IE 
    { 
        box = el.getBoundingClientRect(); 
        var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop); 
        var scrollLeft = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft); 
 
        return {x:box.left + scrollLeft, y:box.top + scrollTop}; 
    } 
    else if(document.getBoxObjectFor)    // gecko 
    { 
        box = document.getBoxObjectFor(el); 
            
        var borderLeft = (el.style.borderLeftWidth)?parseInt(el.style.borderLeftWidth):0; 
        var borderTop = (el.style.borderTopWidth)?parseInt(el.style.borderTopWidth):0; 
 
        pos = [box.x - borderLeft, box.y - borderTop]; 
    } 
    else    // safari & opera 
    { 
        pos = [el.offsetLeft, el.offsetTop]; 
        parent = el.offsetParent; 
        if (parent != el) { 
            while (parent) { 
                pos[0] += parent.offsetLeft; 
                pos[1] += parent.offsetTop; 
                parent = parent.offsetParent; 
            } 
        } 
        if (ua.indexOf('opera') != -1  
            || ( ua.indexOf('safari') != -1 && el.style.position == 'absolute' ))  
        { 
                pos[0] -= document.body.offsetLeft; 
                pos[1] -= document.body.offsetTop; 
        }  
    } 
         
    if (el.parentNode) { parent = el.parentNode; } 
    else { parent = null; } 
   
    while (parent && parent.tagName != 'BODY' && parent.tagName != 'HTML')  
    { // account for any scrolled ancestors 
        pos[0] -= parent.scrollLeft; 
        pos[1] -= parent.scrollTop; 
   
        if (parent.parentNode) { parent = parent.parentNode; }  
        else { parent = null; } 
    } 
    return {x:pos[0], y:pos[1]}; 
};
var zt_topbar = new Topbar(getObj('zt_topbar'));
