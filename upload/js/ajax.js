function get_object(idname){
	if (document.getElementById){
		return document.getElementById(idname);
	}else if (document.all){
		return document.all[idname];
	}else if (document.layers){
		return document.layers[idname];
	}else{
		return null;
	}
}
function get_tags(parentobj, tag){
	if (typeof parentobj.getElementsByTagName != 'undefined'){
		return parentobj.getElementsByTagName(tag);
	}else if (parentobj.all && parentobj.all.tags){
		return parentobj.all.tags(tag);
	}else{
		return null;
	}
}
function unhtmlspecialchars(str){
	f = new Array(/&lt;/g, /&gt;/g, /&quot;/g, /&amp;/g);
	r = new Array('<', '>', '"', '&');
	for (var i = 0; i < f.length; i++){
		str = str.replace(f[i], r[i]);
	}
	return str;
}
function htmlspecialchars(str){
	var f = new Array(new RegExp('&', 'g'),new RegExp('<', 'g'),new RegExp('>', 'g'),new RegExp('"', 'g'));
	var r = new Array('&amp;','&lt;','&gt;','&quot;');
	for (var i = 0; i < f.length; i++){
		str = str.replace(f[i], r[i]);
	}
	return str;
}