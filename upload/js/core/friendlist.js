function showFriendList(ftid) {
	var objs = getObj('friendlist').getElementsByTagName('div');
	for (var i=0; i<objs.length; i++) {
		if (ftid == -1) {
			objs[i].style.display = '';
		} else {
			var typeid = 'ftid_'+ftid;
			if (typeid == objs[i].id) {
				objs[i].style.display = '';
			} else {
				objs[i].style.display = 'none';
			}
		}
	}
}
var ifcheck = true;
function CheckAll(form,match) {
	for (var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if (e.type == 'checkbox' && (typeof match == 'undefined' || e.name.match(match))) {
			e.checked = ifcheck;
		}
	}
	ifcheck = ifcheck == true ? false : true;
}