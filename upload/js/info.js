function showSystemIcon(page) {
	getObj('iconbox').innerHTML = '<div class="tac" style="width:272px;padding:30px 0 0;"><div><img src="'+imgpath+'/loading.gif" align="absmiddle" />正在加载...</div></div>';
	ajax.send('pw_ajax.php?action=showface&page='+page,'',getIcon);
}

function getIcon() {
	var iconlist = ajax.request.responseText;
	setTimeout(function(){getObj('iconbox').innerHTML = iconlist;},300);
}

function SetCookie(name,value) {
	expires = new Date();
	expires.setTime(expires.getTime()+(86400*365));
	document.cookie=name+"="+escape(value)+"; expires="+expires.toGMTString()+"; path=/";
}

function DelCookie(name) {
	expires = new Date();
	expires.setTime(expires.getTime()-(86400*365));
	document.cookie=name+"=; expires="+expires.toGMTString()+"; path=/";
}

function FetchCookie(name) {
	var start = document.cookie.indexOf(name);
	var end = document.cookie.indexOf(";",start);
	return start==-1 ? null : unescape(document.cookie.substring(start+name.length+1,(end>start ? end : document.cookie.length)));
}

function setFaceLen() {
	var v = 0;
	var facetype = document.getElementsByName('facetype');
	for (i = 0; i < facetype.length; i++) {
		if (facetype[i].checked === true) {
			v = facetype[i].value;break
		}
	}
	if (v == 2) {
		var img = new Image();
		img.src = getObj('httpurl_url').value;
		getObj('httpurl_w').value = img.width;
		getObj('httpurl_h').value = img.height;
	}
	return true;
}
function selectTab(tab) {
	var o = getObj('infolist');
	var t = o.getElementsByTagName('li');
	for (var i=0;i<t.length;i++) {
		if (t[i].id) {
			var oo = getObj(t[i].id);
			if (t[i].id == tab) {
				getObj(t[i].id).className = 'current';
			} else {
				getObj(t[i].id).className = '';
			}
		}
	}
	getObj('userbinding-node').style.display = (tab == 'binding') ? '' : 'none';
}
