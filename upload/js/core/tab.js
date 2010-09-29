function showTabs(id,select,element,css){
	var o = getObj(id);
	var t = o.getElementsByTagName(element);
	for (var i=0;i<t.length;i++) {
		if (t[i].id) {
			var oo = getObj(t[i].id);
			if (t[i].id == select) {
				getObj(t[i].id).className = css;
				getObj('info_'+t[i].id).style.display = '';
			} else {
				getObj(t[i].id).className = '';
				getObj('info_'+t[i].id).style.display = 'none';
			}
		}
	}
	if (getObj('info_type')) {
		getObj('info_type').value=select;
	}
	return false;
}

function pwTab(tabId, tabpre, infopre, cookie, tagname, css) {
	this.css = (typeof css == 'undefined' || css == '') ? 'current' : css;
	this.tagname = (typeof tagname == 'undefined' || tagname == '') ? 'li' : tagname;
	this.cookie = (typeof cookie == 'undefined' || cookie == '') ? '' : cookie;
	this.tab = getObj(tabId);
	this.tabpre = tabpre;
	this.infopre = infopre;
	this.init();
}

pwTab.prototype.init = function() {
	var tags = this.tab.getElementsByTagName(this.tagname);
	var its = this;
	for (var i = 0; i < tags.length; i++) {
		if (this.checkId(tags[i].id)) {
			tags[i].onclick = function() {
				its.show(this.id);
			}
		}
	}
}

pwTab.prototype.checkId = function(id) {
	return (id && id.substr(0, this.tabpre.length) == this.tabpre);
}

pwTab.prototype.show = function(id) {
	if (typeof id == 'undefined' || !id || id == 'null') {
		return;
	}
	var tags = this.tab.getElementsByTagName(this.tagname);
	for (var i = 0; i < tags.length; i++) {
		if (this.checkId(tags[i].id)) {
			tags[i].className = (tags[i].id == id) ? this.css : '';
			var key = tags[i].id.substr(this.tabpre.length);
			if (IsElement(this.infopre + key)) {
				getObj(this.infopre + key).style.display = (tags[i].id == id) ? '' : 'none';
			}
		}
	}
	if (this.cookie) {
		SetCookie(this.cookie, id);
	}
}