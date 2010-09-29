getObj('upPanel')&&getObj('upPanel').insertBefore(read.menu,getObj('upPanel').firstChild);
function scroll(x,y){getObj('upPanel').scrollTop=y;getObj('upPanel').scrollLeft=x;}
window.onmousewheel = document.onmousewheel = function(){
	var delta = window.event.wheelDelta/120;
	document.body.scrollTop += delta;
};