var userBinding = {
	baseUrl : getObj('headbase').href,
	menu : null,
	getMenu : function(callback) {
		if (userBinding.menu != null) {
			return true;
		}		
		ajax.send(this.baseUrl + 'pw_ajax.php?action=showuserbinding', '', function() {
			userBinding.menu = document.createElement('div');
			if (ajax.request.responseText != null && ajax.request.responseText.indexOf('<') != -1) {
				userBinding.menu.className = 'pw_menu';
				userBinding.menu.innerHTML = ajax.runscript(ajax.request.responseText);
				callback();
			}
		});
		return false;
	},
	append : function() {
		if (!userBinding.getMenu(userBinding.append)) {
			return;
		}
		var s = read.menu.getElementsByTagName('div');
		for (var i = 0; i < s.length; i++) {
			if (s[i].className == 'userbindingMenu') {
				s[i].innerHTML = '<h5>帐号切换</h5>' + userBinding.menu.innerHTML;break;
			}
		}
	},

	get : function(id) {
		if (!userBinding.getMenu(function() {userBinding.show(id);})) {
			return;
		}
		userBinding.show(id);
	},

	show : function(id) {
		read.open(userBinding.menu, id, 1, 21, userinfomorediv);
	},

	change : function(uid) {
		ajax.send(this.baseUrl + 'pw_ajax.php?action=switchuser&uid=' + uid, '', function() {
			var rText = ajax.request.responseText.split('\t');
			if (typeof dataStorage == 'object') {
				dataStorage.save();
			}
			if (rText[0] == 'ok') {
				if (rText[1]) {
					ajax.runscript(rText[1]);
					setTimeout(function(){userBinding.reload();}, 500);
				} else {
					userBinding.reload();
				}
			} else {
				ajax.guide();
			}
		});
	},
	
	reload : function() {
		is_ie ? history.go(0) : location.reload();
	}
}