function delTr (e) {
	while (e && e.tagName.toLowerCase() != 'tr') {
		e = e.parentNode;
	}
	if (e.tagName.toLowerCase() == 'tr') {
		delElement(e);
	}
}
var addTagRelate = Class({},{
	Create	: function (fildbody,filddata) {
		this._setFildBody(fildbody);
		this._setCloneNode(filddata);
		this.appendToFildBody();
	},
	
	appendToFildBody	: function () {
		this.fildBody.appendChild(this.clonenode);
	},

	_setFildBody	: function (fildbody) {
		this.fildBody	= getObj(fildbody);
	},

	_setCloneNode	: function (filddata) {
		var nodes = getObj(filddata).getElementsByTagName('tr');
		this.clonenode	= nodes[0].cloneNode(true);
	}
});