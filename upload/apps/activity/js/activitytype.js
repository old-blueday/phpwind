function delActivity(id){/*删除活动*/
	ajax.send('apps.php?q=activity&a=delactivity&action=delactivity&ajax=1&id='+id,'',function(){
		var rText = ajax.request.responseText.split('\t');
		if (rText[0] == 'success') {
			var element = document.getElementById('activity_'+id);
			if (element) {
				element.parentNode.removeChild(element);
				showDialog('success','删除成功!', 2);
			} else {
				window.location.reload();
			}
		} else if (rText[0] == 'mode_o_delactivity_permit_err') {
			showDialog('error','您没有权限删除别人的活动', 2);
		} else {
			showDialog('error','删除失败', 2);
		}
	});
}