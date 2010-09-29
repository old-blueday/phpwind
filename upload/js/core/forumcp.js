function addtype(){
	var s = getObj('tmode').firstChild.cloneNode(true);
	getObj('tbody').appendChild(s);
}

function addSubType(id) {
	var inputs = getObj('t_sub_mode').getElementsByTagName('input');
	inputs[0].name = "new_t_sub_view_db\["+id+"\]\[\]";
	inputs[1].name = "new_t_sub_db\["+id+"\]\[\]";
	var s = getObj('t_sub_mode').firstChild.cloneNode(true);
	getObj('t_sub_body_'+id).appendChild(s);
}

function delTtype(id,type) {
	var url = '$ajaxurl';
	if (type == 'top') {
		if (!confirm("确定要删除此主题分类吗？删除此主题分类将同时删除其二级分类")) return false;
	} else if (type == 'sub') {
		if (!confirm("确定要删除此主题分类吗？")) return false;
	}
	var data = 'action=delttype&type='+type+'&id='+id+'&';
	setTimeout("ajax.send('" + url + "','" + data + "',delTtypeR)",200);
}

function delTtypeR(){
	var ids = ajax.request.responseText.split("\t");
	if (ids[0] == 'success') {
		for (var i = 1;i<ids.length;i++) {
			delElement('ttype_'+ids[i]);
		}
	} else {
		alert("删除错误！");
	}
}