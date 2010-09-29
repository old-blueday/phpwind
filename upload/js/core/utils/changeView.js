var changeView = Class({},
{
Create: function (container,tag) {
this.container = container;
this.tag = tag;
this.current = null;
this._init();
},
_init: function () {
var list = document.getElementById(this.container).getElementsByTagName(this.tag);
var self = this;
for (var i = 0; i< list.length; i++ ) {
this._addEvent(list[i],'mouseover',this._mouseover);
}
list[0].className = 'cc current';
this.current = list[0];
},
_mouseover: function () {
this.className = 'cc current';
},
_addEvent: function (el,evname,func) {
var self = this;
if(is_ie) {
el.attachEvent("on" + evname,function(){
self._changeView(el,func);
});
} else {
el.addEventListener(evname,function(){
self._changeView(el,func);
},true);
}
},
_changeView:function(el,func){
if (this.current) {
this.current.className = 'cc';
}
func.call(el);
this.current = el;
}
});