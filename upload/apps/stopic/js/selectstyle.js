var selectStyle = Class({},{
	Create	: function (container,style) {
		this.styleInput = objCheck(style);
		this._init(container);
	},
	_init	: function (container) {
		container = objCheck(container);
		var elems = container.getElementsByTagName("li");
		var self = this;
		this.items = Array();
		for (var i=0;i<elems.length ;i++ ) {
			this.items.push(elems[i]);
			elems[i].onclick = function () {
				self._onclick(this);
			}
		}
	},
	_onclick	: function (e) {
		var styledir = this._getStyleDir(e);
		for (var i=0;i<this.items.length ;i++ )	{
			this.items[i].className = '';
		}
		e.className="current";
		this._setStyleInputValue(styledir);
	},
	_setStyleInputValue	: function (styleDir) {
		this.styleInput.value	= styleDir;
	},
	_getStyleDir	: function (e) {
		if (e.id && e.id.indexOf('style_') != -1) {
			var start = 6;
			return e.id.substr(start);
		}
		return false;
	}
});