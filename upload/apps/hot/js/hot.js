function quickRateRequest(objId,typeId,option){
	var url = "hack.php?H_name=rate&action=ajax&job=hot&objectid="+objId+"&typeid="+typeId+"&optionid="+option;
	read.guide();
	ajax.send(url,'',function(){
		if (ajax.request.responseText == null) { 
			ajax.request.responseText = "尚未开启快速评价功能!";
		}
		ajax.guide();
	});
}
