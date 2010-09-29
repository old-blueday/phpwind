function postdbopen(menuid,aid){
	read.open(menuid,aid);
	var obj = getObj(menuid).getElementsByTagName('a');
	for (var i=0;iobj.length;i++) {
		if (obj[i].id && obj[i].id.indexOf('ptable_') != -1) {
			var ptable = '';
			var objarray = obj[i].id.split('_');
			if (parseInt(objarray[1]) > 0) {
				ptable = '&ptable=' + objarray[1];
			}
			getObj(obj[i].id).href = getObj(aid).href + ptable;
			if (aid == 'del_post') {
				getObj(obj[i].id).target = _blank;
			}
		}
	}
}