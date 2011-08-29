function cms_submit(id,column_id){
	var sendUrl = 'index.php?m=cms&q=list&action=del&ids=' + id + '&column_id='+column_id;
	showDialog('confirm','确定要删除该文章?',0,function(){
		ajax.send(sendUrl,'',function(){
			var rText = ajax.request.responseText;
			if (rText=='success') {
				showDialog('success','文章删除成功!',2);
				setTimeout("window.location.reload();",2000);
			} else {
				ajax.guide();
			}
		});
	});
}

function deleteArticle(id,cid){
	var sendUrl = 'index.php?m=cms&q=list&action=del&ids=' + id +'&column_id='+cid;
	showDialog('confirm','确定要删除该文章?',0,function(){
		ajax.send(sendUrl,'',function(){
			var rText = ajax.request.responseText;
			if (rText=='success') {
				showDialog('success','文章删除成功!',2);
				setTimeout("window.location.href='index.php?m=cms&q=list&column="+cid+"';",2000);
			} else {
				ajax.guide();
			}
		});
	});
}

function cms_submit_m(form,column_id){
	var checkBoxObj = form.aids;
	var aids = '';
	if(checkBoxObj == 'undefined' || checkBoxObj == null){
		showDialog('error','请至少选择一条文章',0);
		return false;
	}
	for (var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if (e.name != "" && e.type == 'checkbox' && e.checked) {
			aids += aids == '' ? e.value : ',' + e.value;
		}
	}
	if("" == aids || aids == "undefined" || aids == null ){
		showDialog('error','请至少选择一条文章',0);
		return false;
	}
	var sendUrl = 'index.php?m=cms&q=list&action=del&ids=' + aids + '&column_id' + column_id;
	showDialog('confirm','确定要删除选中文章?',0,function(){
		ajax.send(sendUrl,'',function(){
			var rText = ajax.request.responseText;
			if (rText=='success') {
				showDialog('success','文章删除成功!',2);
				setTimeout(function(){
					for (var i = 0; i < form.elements.length; i++) {
						var e = form.elements[i];
						if (e.name != "" && e.type == 'checkbox' && e.checked) {
							e.checked = false;
						}
					}
					window.location.reload();
				},2000);
			} else {
				showDialog('error','文章删除失败',2);
			}
		});
	});
}


function checkAll(form,obj){
	var checkBox = form.aids;
	if(checkBox.value){
		obj.checked ? checkBox.checked = true : checkBox.checked = false;
	}else{
		for (var i = 0; i < checkBox.length; i++) {
			obj.checked ? checkBox[i].checked = true : checkBox[i].checked = false;
		}
	}
}