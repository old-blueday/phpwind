function delFriend(u){
	ajax.send('apps.php?q=ajax&a=delfriend&u='+u,'',function(){
		var rText = ajax.request.responseText.split("|");
		if (rText[0] == 'success') {
			var element = document.getElementById('friend_'+u);
			if (element) {
				showDialog('success',M_FRIEND_OPSUCCESS,1);
				element.parentNode.removeChild(element);
			} else {
			}
		} else {
			ajax.guide();
		}
	});
}


