
function sendurl(obj,type,id,e,urlprefix) {
	var temptype = '0';
	if (type == '16') {
		type = '1';
		temptype = '16';
	}
	if (!(db_ajax & type) || typeof e != 'undefined' && e.ctrlKey) {
		return true;
	}
	if(temptype == '16'){
		type = '16';
	}
	try {
		var url_a = {1 : 'mawhole.php?ajax=1&' , 2 : 'masingle.php?ajax=1&' ,
			4 : 'ajax.php?' , 8 : 'operate.php?ajax=1&', 16 : 'mawholecolony.php?ajax=1&'};
		var url	 = url_a[type];
		var href = obj.href;
		if (href.indexOf(db_dir)!=-1 && href.indexOf(db_ext)!=-1) {
			href = href.substr(href.indexOf(db_dir)+db_dir.length);
			href = href.substring(0,href.lastIndexOf(db_ext));
			var str = href.split('-');
			for (i=0; i<str.length; i++) {
				url += str[i] + '=' + str[++i] + '&';
			}
		} else {
			url += href.substr(href.indexOf('?')+1);
		}
		if (typeof id == 'undefined' || id == '') id = obj.id;
		if (typeof urlprefix != 'undefined') url = urlprefix + url;
		sendmsg(url,'',id);
		return false;
	} catch(e){
		return true;
	}
}
function formclick(obj,action,type) {
	obj.action = action;
	if (db_ajax & type) {
		obj.action += '&ajax=1';
		sendmsg(obj.action,obj,'');
	} else {
		obj.submit();
	}
}
function edited() {
	var str = ajax.request.responseText.split("\t");
	if (str[0] == 'success') {
		var id  = read.obj.id;
		id = id.substr(id.lastIndexOf('_')+1);
		getObj('subject_'+id).innerHTML	= str[1];
		getObj('read_'+id).innerHTML	= str[2];
		closep();
	} else {
		ajax.showError(str[0]);return false;
	}
	return false;
}
function favor(type) {
	closep();
	ajax.send('pw_ajax.php?action=favor&tid='+tid+'&fid='+fid+'&type='+type,'',ajax.get);
}
function addfriend(uid) {
	ajax.send('pw_ajax.php','action=addfriend&touid='+uid,ajax.guide);
}
function delatt(pid, aid, type) {
	if (!confirm('确定要删除此附件？')) return false;
	ajax.send('pw_ajax.php','action=deldownfile&aid=' + aid + (typeof type == 'undefined' ? '' : ('&type=' + type)), function() {
		if (ajax.request.responseText == 'success') {
			var o = getObj('att_'+aid);
			o.parentNode.removeChild(o);
		} else {
			ajax.guide();
		}
	});
}
function playatt(aid) {
	if (typeof player == 'undefined') {
		loadjs('js/player.js','','js_player');
		setTimeout(function(){playatt(aid);},100);return;
	}
	if (IsElement('p_att_' + aid)) {
		getObj('p_att_' + aid).parentNode.removeChild(getObj('p_att_' + aid));
		return;
	}
	ajax.send('pw_ajax.php?action=playatt&aid=' + aid, '', function() {
		var rText = ajax.request.responseText.split('\t');
		if (rText[0] == 'ok') {
			player('att_' + aid, rText[1], rText[2], rText[3], rText[4]);
		} else {
			ajax.guide();
		}
	});
}
function Fjump(value) {
	if(value!='') window.location = 'thread.php?fid='+value;
}
function copyUrl(o) {
	if (is_ie) {
		window.clipboardData.setData("Text",copyurl+o);
		showDialog('success','已成功复制',1);
	} else {
		prompt('按下 Ctrl+C 复制到剪贴板', copyurl+o)
	}
}
function postreply(txt) {
	if (typeof document.FORM != "undefined") {
		document.FORM.atc_title.value = txt;
		if (txt.match(/\((.+?)\)/ig)) {
			document.FORM.replytouser.value = RegExp.$1;
		}
		document.FORM.atc_content.focus();
	} else {
		window.location = 'post.php?action=reply&fid='+fid+'&tid='+tid;
	}
}
function dig() {
	ajax.send('pw_ajax.php?action=dig&tid='+tid,'',function(){
		var str = ajax.request.responseText.split("\t");
		ajax.guide();
		if (typeof str[1] != 'undefined') {
			getObj('r_dig').innerHTML = str[1];
		}
	});
}
function marked() {
	var str = ajax.request.responseText.split("\t");
	if (str == 'success') {
		showDialog('success','评分操作成功！');
		self.location.reload();
	} else {
		ajax.guide();
	}
}
function usetool(id) {
	if (id>0 && confirm('你确定要使用该道具吗?')) {
		closep();
		read.obj = getObj('td_More');
		ajax.send('profile.php?action=toolcenter&job=ajax&tid='+tid+'&toolid='+id,'',ajax.get);
	}
}
function usertool(uid,id) {
	if (confirm('你确定要使用该道具吗?')) {
		closep();
		ajax.send('profile.php?action=toolcenter&job=ajax&uid='+uid+'&toolid='+id,'',ajax.get);
	}
}
function fontsize(text,id){
	getObj("read_"+id).className = text;
}

if (typeof totalpage != 'undefined' && totalpage > 1) {
	document.onkeydown = function(e) {
		var e = is_ie ? window.event : e;
		var tagname = is_ie ? e.srcElement.tagName : e.target.tagName;
		if (tagname == 'INPUT' || tagname == 'TEXTAREA') {
			return;
		}
		actualCode = e.keyCode ? e.keyCode : e.charCode;
		if (actualCode == 39 && page<totalpage) {
			window.location = jurl + (page+1);
		} else if (actualCode == 37 && page>1) {
			window.location = jurl + (page-1);
		}
	}
}

function worded() {
	var str = ajax.request.responseText.split("\t");
	var id  = read.obj.id;
	id = id.substr(id.lastIndexOf('_')+1);
	if (str[0] == 'success') {
		if (IsElement('lwd_'+id)) {
			if (str[1] == '') {
				getObj('read_'+id).removeChild(getObj('lwd_'+id));
			} else {
				getObj('lwd_'+id).lastChild.innerHTML = str[1];
			}
		} else {
			if(str[1] == '')
				return;
			var tpc = getObj('read_'+id);
			var s	= document.createElement("div");
			s.id = 'lwd_' + id;
			s.innerHTML = '<div class="louMes"><span class="fr adel" onclick="read.obj=getObj(\'lwd_' + id + '\');ajax.send(\'pw_ajax.php?action=leaveword\',\'step=3&tid=' + tid + '&pid=' + id + '\',worded);">x</span><h4 class="b">楼主留言：</h4><p>'+str[1]+'</p></div>';
			if (IsElement('alert_'+id)) {
				tpc.insertBefore(s,getObj('alert_'+id));
			} else{
				tpc.appendChild(s);
			}
		}
	} else {
		ajax.guide();
	}
}
function reminded() {
	var str = ajax.request.responseText.split("\t");
	var id  = read.obj.id;
	id = id.substr(id.lastIndexOf('_')+1);
	if (str[0] == 'success') {
		if (IsElement('mag_'+id)) {
			getObj('mag_'+id).lastChild.innerHTML = str[1];
		} else {
			var o = getObj('p_'+id);
			var s = document.createElement("div");
			s.id = 'mag_' + id;
			s.className = 'tpc_content';
			s.innerHTML = '<h6 class="quote"><span class="s3 f12 fn">管理提醒： ('+str[2]+')</span></h6><blockquote class="blockquote">'+str[1]+'</blockquote>';
			o.parentNode.insertBefore(s,o.nextSibling);
		}
	} else if (str[0] == 'cancle') {
		if (IsElement('mag_'+id)) {
			getObj('mag_'+id).parentNode.removeChild(getObj('mag_'+id));
		}
	} else {
		ajax.guide();
	}
}

function checkUrl(obj) {
	var url = obj.href;
	var suburl = '';
	var urladd = '';
	var regex = /^((\w+):\/\/)?((\w+):?(\w+)?@)?([^\/\?:]+):?(\d+)?(\/?[^\?#]+)?\??([^#]+)?#?(\w*)/;
	if (db_urlcheck.length > 0) {
		var str = db_urlcheck.split(",");
		var r = regex.exec(url);
		for (var i in str){
			if (r[6].indexOf('.'+str[i]) !== -1 || r[6] == str[i] ){
				return true;
			}
		}
	}

	var regex2 = /^http(s)?:\/\/(\w+\.)*((\w+)\.(com|net|cn|com\.cn|net\.cn|org|org\.cn|biz|cc|name|asia|mobi|me|tel|中国|公司|网络|hk|tv))(\/(.+))?/;
	var r2 = regex2.exec(db_bbsurl);

	if (r2 != null && url.indexOf(r2[3]) != -1) {
		return true;
	} else if (url.indexOf('localhost') == -1 && url.indexOf('127.0') == -1){
		suburl = url.substr(0,30);
		if (suburl != url) {
			urladd = '...';
		}
		getObj("pw_box").innerHTML='';
		setTimeout(
			function(){
				read.open('checkurl',obj.id,1,11);
				getObj('suburl').innerHTML = suburl + urladd;
				getObj('trueurl').href = url;
				//for buttons
				var buttons = document.getElementsByTagName('button');
				for(var i=0;i<buttons.length;i++){
					if(buttons[i].id == 'trueurl'){
						buttons[i].onclick=function(){
							window.open(url);
							closep();
							return false;
						}
					}
				}
				//for buttons
			},300
		);
		return false;
	} else {
		return true;
	}
}

function getFloorUrl(obj) {
	var uid = getObj("hideUid").value;
	var tid = getObj("tid").value;
	var url = "job.php?action=topost&floor=" + obj.value + "&tid=" + tid;
	if (uid > 0) {
		url += "&uid=" + uid;
	}
	window.location = url;
}

function viewIp(o) {
	read.obj = o;
	var str = o.id.split('_');
	ajax.send('masingle.php?action=viewip&ajax=1&fid=' + fid + '&tid=' + tid + '&pid=' + str[1] + '&page=' + page, '', function() {
		ajax.get();read.closeByMove(o.id);
	});
}

function manageSignature(idName, pid, isBanned) {
	url = 'masingle.php?action=bansignature&fid=' + fid + '&tid=' + tid + '&pid=' + pid + '&page=' + page;
	url += (db_ajax & 2) ? '&ajax=1' : '';
	url += (isBanned == 1) ? '&isbanned=1' : '';
	if (!(db_ajax & 2)) {
		getObj(idName).href=url;
		return true;
	}
	sendmsg(url,'','');
}

function showSignature(idName, isShow) {
	if (isShow) {
		getObj(idName).style.cssText = 'display:inline';
	} else {
		getObj(idName).style.cssText = 'display:none';
	}
}

var loadFloor = {
	showHidden : function(tid) {
		var pids = '';
		var objs = document.forms['delatc'].getElementsByTagName('blockquote');
		for (var i = 0; i < objs.length; i++) {
			if (objs[i].id && objs[i].id.substr(0, 7) == 'hidden_') {
				pids += (pids ? ',' : '') + objs[i].id.substr(objs[i].id.lastIndexOf('_') + 1);
			}
		}
		this.get(tid, pids);
	},
	get : function(tid, pids) {
		if (pids == '') return;
		ajax.send('pw_ajax.php?action=readfloor&tid=' + tid + '&pids=' + pids, '', function() {
			var rText = ajax.request.responseText;
			if (rText == 'fail') return;
			var tmpNode = document.createElement('div');
			tmpNode.innerHTML = rText;
			if (tmpNode.childNodes.length > 0) {
				for (var i = 0; i < tmpNode.childNodes.length; i++) {
					var o = tmpNode.childNodes[i];
					if (o.nodeType == 1 && o.id && o.id.substr(0,10) == 'readfloor_') {
						for (var j = 0; j < o.childNodes.length; j++) {
							var mo = o.childNodes[j];
							if (mo.nodeType == 1 && mo.id != '' && IsElement(mo.id)) {
								getObj(mo.id).parentNode.replaceChild(mo.cloneNode(true), getObj(mo.id));
							}
						}
					}
				}
			}
		});
	}
}

function showAttImg(lou, type) {
	var other = type == 'small' ? 'all' : 'small';
	getObj('imgList_' + lou + '_' + type).style.display = '';
	getObj('imgList_' + lou + '_' + other).style.display = 'none';
	getObj('imgAction_' + lou + '_' + type).className = 'current';
	getObj('imgAction_' + lou + '_' + other).className = '';
}

var readImg = {
	queue : {},
	lou : 0,
	init : function(lou) {
		if (!this.queue[lou]) {
			var list = getObj('imgList_' + lou + '_small').getElementsByTagName('img');
			var imgs = [];
			for (var i=0; i<list.length; i++) {
				imgs[i] = list[i].getAttribute('src_data');
			}
			this.queue[lou] = {'value' : imgs, 'counter' : 0};
		}
	},
	show : function(lou, counter) {
		this.init(lou);
		var obj = this.queue[lou];
		obj.counter = counter;
		var photo = obj.value;
		var prephoto = getObj('prephoto');
		var nextphoto = getObj('nextphoto');
		if (obj.counter == 0) {
			if (photo.length > 1) {
				nextphoto.style.display = '';
			}
			prephoto.style.display = 'none';
		}
		if (obj.counter == photo.length-1) {
			if (obj.counter > 0) {
				prephoto.style.display = '';
			}
			nextphoto.style.display = 'none';
		}
		if (obj.counter > 0 && obj.counter < photo.length-1) {
			prephoto.style.display = '';
			nextphoto.style.display = '';
		}
		this.createTmp(photo,counter);
		this.lou = lou;
	},
	viewAll : function() {
		var obj = this.queue[this.lou];
		var photo = obj.value;
		window.open(photo[obj.counter]);
	},
	nextPhoto : function() {
		var obj = this.queue[this.lou];
		var photo = obj.value;
		if (obj.counter > photo.length -2) return;
		obj.counter++;
		getObj('prephoto').style.display = '';
		this.createTmp(photo,obj.counter);

		if (obj.counter == photo.length-1) {
			getObj('nextphoto').style.display = 'none';
		}
	},
	createTmp : function(photo, counter){
		var imgpretmp = getObj('imgpre')
		if(imgpretmp){
			document.body.removeChild(imgpretmp);
			imgpretmp.onload='';
			delete imgpretmp;
		}
		imgpre = new Image();
		imgpre.id = 'imgpre';
		imgpre.src = photo[counter];
		imgpre.style.position = 'absolute';
		imgpre.style.top = '-100000px';
		document.body.insertBefore(imgpre,document.body.childNodes[0]);
		setTimeout(reposPhoto,0);
		imgpre.onload = reposPhoto;
		getObj('photo_pop_page').innerHTML = (parseInt(counter)+1) + ' / ' + photo.length;
	},

	prevPhoto : function() {
		var obj = this.queue[this.lou];
		var photo = obj.value;
		if (obj.counter < 1) return;
		obj.counter--;
		getObj('nextphoto').style.display = '';
		this.createTmp(photo,obj.counter);
		if (obj.counter <= 0) {
			obj.counter = 0;
			getObj('prephoto').style.display = 'none';
		}
	},

	hidePhoto : function(){
		getObj('photo_pop').style.display='none';
		getObj('photo_pop_mask').style.display='none';
		getObj('imgpre').src='#';
	}
}
function reposPhoto() {
	var photo = getObj('imgpre');
	if(photo.src == 'javascript://;'){
		setTimeout(reposPhoto,200);
		return;
	}
	if(!photo.width){
		setTimeout(reposPhoto,200);
		return;
	}
	var path = getObj('photo_path');
	path.src =  photo.src;
	path.style.height = 'auto';
	path.style.width  = 'auto';
	if (photo.width > 900) {
		path.style.height = photo.height/photo.width*900+'px';
		path.style.width = '900px';
	}
	var offsetheight = (is_ie ? ietruebody().offsetHeight : ietruebody().clientHeight) - 120;
	if (photo.height > offsetheight) {
		path.style.width = photo.width/photo.height*offsetheight+'px';
		path.style.height = offsetheight + 'px';
	}
	var popo= getObj('photo_pop');
	popo.style.top = '-10000px';
	popo.style.display='';
	getObj('photo_pop_mask').style.width=document.body.scrollWidth+'px';
	getObj('photo_pop_mask').style.height=document.body.scrollHeight+'px';
	popo.style.left = (document.body.clientWidth-popo.clientWidth)/2+'px';
	popo.style.top = ( (window.innerHeight||document.documentElement.offsetHeight) -popo.clientHeight)/2+ietruebody().scrollTop+'px';
	getObj('photo_pop_mask').style.display='';
	getObj('prephoto').style.height=getObj('nextphoto').style.height= (parseInt(path.style.height)||photo.height)+'px';
}