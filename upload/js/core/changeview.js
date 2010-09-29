var changView = Class({},{
	Create: function (container,tag,ifhidden) {
		this.container = container;
		this.tag = tag;
		if (ifhidden) {
			this.ifhidden = 1;
		} else {
			this.ifhidden = 0;
		}
		this.current = null;
		this._init();
	},
	_init: function () {
		if(!document.getElementById(this.container))return;
		var list = document.getElementById(this.container).getElementsByTagName(this.tag);
		var self = this;
		for (var i = 0; i< list.length; i++ ) {
			this._addEvent(list[i],'mouseover',this._mouseover);
			if (this.ifhidden == 1) {
				this._addEvent(list[i],'mouseout',this._mouseout);
			}
		}
		if (this.ifhidden == 0 && list[0]) {
			list[0].className = 'cc current';
			this.current = list[0];
		}
	},
	_mouseover: function () {
		clearTimeout(this.t);
		this.className = 'cc current';
	},
	_mouseout: function () { 
		var _=this;
		this.t=setTimeout(function()
		{
			_.className = 'cc';
		},200);
	},
	_addEvent: function (el,evname,func) {
		var self = this;
		if(is_ie) {
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
		if (this.ifhidden==0) {
			if (this.current) {
				this.current.className = 'cc';
			}
			this.current = el;
		}
		func.call(el);
	}
});