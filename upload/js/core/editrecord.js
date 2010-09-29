function editrecord(id,record){
	var s = getObj('s_' + id);
	var r = getObj('ping_' + id);
	if (s.checked == true) {
		r.innerHTML = '<input type="text" name="record[' + id + ']" size="30" value="' + record + '">';
	} else {
		r.innerHTML = record;
	}
	
}