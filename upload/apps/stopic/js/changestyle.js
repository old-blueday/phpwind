var changeStyle = Class({},{
	Create	: function (container, styleSaveId) {
		this._init(container);
		this.styleInput = objCheck(styleSaveId);
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
		this.styleInput.value = styledir;
		
		e.className="current";
		var self = this;
		var stopic_id = getObj('stopicId').value;
		ajax.send(AJAXURL,'job=changestyle&style='+styledir + '&stopicid=' + stopic_id + '&blockid=block_banner_101',function(){
			var rText = ajax.request.responseText.split('\t');
			if (rText[0] != 'success') {
				alert(ajax.request.responseText);
				return false;
			}
			var layout	= JSONParse(rText[1]);
			self._initLayout(layout);
			checkAdvanceSubmit(); //TODO
		});
	},
	_initLayout	: function (layout) {
		for (var i in layout) {
			if (getObj('layout'+i)) {
				layout[i] = layout[i].replace(/<br \/>/g,"\r\n");
				if (i=='bannerurl' && '' != getObj('layout'+i).value && getObj('layout'+i).value.indexOf('stopic/data/style') == -1) {
					continue;
				}
				getObj('layout'+i).value=layout[i];
			}
			if (getObj('layout'+i+'span')) {
				getObj('layout'+i+'span').style.cssText = "color:"+layout[i];
			}
		}
	},
	_getStyleDir	: function (e) {
		if (e.id && e.id.indexOf('style_') != -1) {
			var start = 6;
			return e.id.substr(start);
		}
		return false;
	}
});