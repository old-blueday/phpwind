/**
 * 微博页面交互
 * @author suqian
 * @date 2010-7-5
 */
var space_uid = space_uid,timer;

weiboPhotos.queue = new Array();//相册队列
weiboPhotos.qLength = 0;//队列个数

function weiboPhotos() {
	this.space = 1;
	this.key = 1;
	this.ulprex = 'photolist_';
	this.push = function(key,value){
		if(this.exists(key)){
			weiboPhotos.queue[key] = {'value':value,'counter':0};
			weiboPhotos.qLength++;
		}
		
	};
	this.next = function(key){
		var ulObj = getObj(this.ulprex+key);
		ulObj.scrollLeft+=116;
	};
	this.prev = function(key){
		var ulObj = getObj(this.ulprex+key);
		ulObj.scrollLeft-=116;
	};
	this.showPhoto = function(pid,path,key,counter){
		if(this.legalImg(path)){
			return false;
		}
		var obj = weiboPhotos.queue[key];
		obj.counter = counter;
		var photo = obj.value;
		var prephoto = getObj('prephoto');
		var nextphoto = getObj('nextphoto');
		if(obj.counter == 0){
			if(photo.length > 1){
				nextphoto.style.display = '';
			}
			prephoto.style.display = 'none';
		}
		if(obj.counter == photo.length-1){
			if(obj.counter > 0){
				prephoto.style.display = '';
			}
			nextphoto.style.display = 'none';
		}
		if(obj.counter > 0 && obj.counter < photo.length-1){
			prephoto.style.display = '';
			nextphoto.style.display = '';
		}
		this.createTmp(path);
		this.key = key;
		
	};	
	this.nextPhoto = function(){
		var obj = weiboPhotos.queue[this.key];
		var photo = obj.value;
		obj.counter++;
		getObj('prephoto').style.display = '';
		this.createTmp(photo[obj.counter].s_path);

		if(obj.counter == photo.length-1){
			getObj('nextphoto').style.display = 'none';
		}
	};
	this.createTmp = function(src){
		var imgpretmp = getObj('imgpre'),self = this;
		if(imgpretmp){
			document.body.removeChild(imgpretmp);
			imgpretmp.onload='';
			delete imgpretmp;
		}
		this.imgpre = new Image();
		this.imgpre.src = src;
		if (this.imgpre.complete){
			this.reposPhoto();
			return;
		}
		this.imgpre.onload = function(){
			self.reposPhoto();
		}
	};
	this.reposPhoto = function(){
		var photo = this.imgpre;
		var path = getObj('photo_path');
		path.src =  photo.src;
		var popo= getObj('photo_pop');
		var mask = getObj('photo_pop_mask');
		mask.style.width=document.body.scrollWidth+'px';
		mask.style.height=document.body.scrollHeight+'px';
		mask.style.display='';
		
		var reportH = document.documentElement.clientHeight || document.body.clientHeight || window.innerHeight,
			reportW = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth,
			i = 0, finalW = Math.min(reportW-25,photo.width),finalH = Math.min(reportH-25,photo.height);

		path.style.display ='block';
		popo.style.display='block';
		path.style.height = finalH +'px';
		path.style.width = 'auto';
		getObj('prephoto').style.height=getObj('nextphoto').style.height= finalH+'px';
		popo.style.left = (document.body.clientWidth - popo.clientWidth) / 2+'px';
		popo.style.top = ( (window.innerHeight||document.documentElement.offsetHeight) - popo.clientHeight)/2+ietruebody().scrollTop+'px';

	};
	this.prevPhoto = function(){
		var obj = weiboPhotos.queue[this.key];
		var photo = obj.value;
		obj.counter--;
		getObj('nextphoto').style.display = '';
		this.createTmp(photo[obj.counter].s_path);
		if(obj.counter<=0){
			obj.counter = 0;
			getObj('prephoto').style.display = 'none';
		}
	}
	
	this.legalImg = function (path){
		return /imgdel\.jpg$/.test(path);
	}
	this.exists = function(key){
		if(weiboPhotos.queue[key]){
			return false;
		}else{
			return true;
		}
	};
	this.hidePhoto = function(){
		clearInterval(timer);
		timer = null;
		getObj('photo_pop').style.display='none';
		getObj('photo_pop_mask').style.display='none';
		if (getObj('comment_to_weibo')) getObj('comment_to_weibo').style.display='none';
	}
}
var weibo = new weiboPhotos();

var mediaPlayer = {
	
	showVideo : function(videoAddr, id) {
		var obj = getObj('showVideo_' + id);
		if (is_ie) {
			var vObject = '<object classid="CLSID:D27CDB6E-AE6D-11cf-96B8-444553540000" width="480" height="400"><param name="src" value="' + videoAddr + '" /><param name="autostart" value="true" /><param name="wmode" value="opaque" /><param name="loop" value="true" /><param name="quality" value="high" /></object>';
		} else {
			var vObject = '<object data="' + videoAddr + '" type="application/x-shockwave-flash" width="480" height="400"><param name="autostart" value="true" /><param name="wmode" value="opaque" /><param name="loop" value="true" /><param name="quality" value="high" /><EMBED src="' + videoAddr + '" quality="high" width=\""+480+"\" height=\""+400+"\" TYPE=\"application/x-shockwave-flash\" wmode=\"opaque\" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED></object>';
		}
		this.showPlayer(obj, vObject, 'video', id);
	},
	
	showMusic : function(link, id, obj) {
		if (link.match(/^http\:\/\/.{1,251}\.mp3\??.*$/i)) {
			var vObject = '<embed height="40" width="290" wmode="transparent" type="application/x-shockwave-flash" src="u/images/mp3player.swf?soundFile=' + encodeURI(link) + '&loop=no&autostart=yes"/>';
		} else if (link.match(/^http\:\/\/.{1,251}\.wma\??.*$/i)) {
			var isIE6=navigator.userAgent.indexOf("MSIE 7.0")==-1&&navigator.userAgent.indexOf("MSIE 8.0")==-1&&navigator.userAgent.indexOf("MSIE 6.0")>0;
			if (isIE6) {
				var vObject = '<object height="64" width="290" data="" classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6"><param value="' + link + '" name="url"/><param value="' + link + '" name="src"/><param value="true" name="showcontrols"/><param value="1" name="autostart"/></object>';
			} else {
				var vObject = '<object height="64" width="290" data="" classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6"><param value="' + link + '" name="url"/><param value="' + link + '" name="src"/><param value="true" name="showcontrols"/><param value="1" name="autostart"/><object height="64" width="290" data="' + link + '" type="audio/x-ms-wma"><param value="' + link + '" name="src"/><param value="1" name="autostart"/><param value="true" name="controller"/></object></object>';
			}
		} else {
			alert('type_error');
			return false;
		}
		this.showPlayer(obj, vObject, 'music', id);
	},

	showPlayer : function(obj, vObject, type, id) {
		var show_id  = 'show_'  + type + '_' + id;
		var colse_id = 'colse_' + type + '_' + id;
		var flash = document.getElementById(show_id);
		if (!flash) {
			var pObject = obj.parentNode;
			var flash = document.createElement('div');
			flash.id = show_id;
			pObject.appendChild(flash);

			var close = document.createElement('div');
			close.id = colse_id;
			close.className = 'video-close';
			var a = document.createElement('a');
			a.className = 'video-close-link';
			a.href = 'javascript:void(0);';
			a.onclick = function(){
				document.getElementById(show_id).style.display = 'none';
				document.getElementById(colse_id).style.display = 'none';
				obj.style.display = '';
			};
			a.innerHTML = '<span class="f12">收起</span>';
			close.appendChild(a);
			pObject.appendChild(close);
		}
		flash.innerHTML = vObject;
		flash.style.display = '';
		document.getElementById(colse_id).style.display = '';
		obj.style.display = 'none';
	}
}
function layoutEmotion(){
	getObj('pw_box').style.top= getObj('td_face').getBoundingClientRect().top+ietruebody().scrollTop-260+'px';
}
//图像大小确定时定位弹窗
function reposPhoto()
{
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
	var popo= getObj('photo_pop');
	var mask = getObj('photo_pop_mask');
	mask.style.width=document.body.scrollWidth+'px';
	mask.style.height=document.body.scrollHeight+'px';
	mask.style.display='';
	
	//getObj('prephoto').style.height = getObj('nextphoto').style.height = '0px';
	var reportH = document.documentElement.clientHeight || document.body.clientHeight || window.innerHeight,
		reportW = document.documentElement.clientWidth || document.body.clientWidth || window.innerWidth,
		i = 0, finalW = Math.min(reportW,photo.width)-25,finalH = Math.min(reportH,photo.height)-25;
	//clearInterval(timer);
	path.style.display ='block';
	popo.style.display='block';
	path.style.height = finalH +'px';
	path.style.width = 'auto';
	getObj('prephoto').style.height=getObj('nextphoto').style.height= finalH+'px';
	popo.style.left = (document.body.clientWidth - popo.clientWidth) / 2+'px';
	popo.style.top = ( (window.innerHeight||document.documentElement.offsetHeight) - popo.clientHeight)/2+ietruebody().scrollTop+'px';
	/*timer = setInterval(function(){//放大动画效果
		var yHeight = parseInt(path.style.height,10);
		var dist = Math.ceil((finalH - yHeight) / 2);
		yHeight = yHeight + dist;
		if (yHeight >= finalH) {
			yHeight = finalH;
			clearInterval(timer);
			timer = null;
			getObj('prephoto').style.height=getObj('nextphoto').style.height= yHeight+'px';
			//getObj('prephoto').style.width=getObj('nextphoto').style.width= path.clientWidth+'px';
			//path.style.height = 'auto';
			path.style.width = 'auto';
		}
		path.style.height = yHeight +'px';
		getObj('prephoto').style.height=getObj('nextphoto').style.height= yHeight+'px';
		popo.style.left = (document.body.clientWidth - popo.clientWidth) / 2+'px';
		popo.style.top = ( (window.innerHeight||document.documentElement.offsetHeight) - popo.clientHeight)/2+ietruebody().scrollTop+'px';
		//v += a;
	}, 12);*/
}

function displayControl(from){
	var style = getObj('comment_to_weibo').style;
	if(style.display == 'none'){
		from.writeContent.value = '';
	}
	style.display='';
}

function displaySuccess(form,ifdisplay){
	if(ifdisplay){
		 form.writeContent.style.display = "none";
		 getObj('comment_success').style.display = "";
		 getObj('comment_to_weibo').style.display ="none";
	}else{
		 form.writeContent.style.display = "";
		 getObj('writeContent').value = "";
		 getObj('comment_success').style.display = "none";
		 getObj('comment_to_weibo').style.display ="";
	}
}

function getcomments(action,mid,uid,identify){
	var id = buildId(mid,identify);
	var ajaxObj = getObj('option_'+id);
	var commentObj = getObj('comment_'+id);
	if (ajaxObj) {
		commentObj.removeChild(ajaxObj);
	} else {
		refreshcomment(action,mid,uid,identify);
	}
}

function refreshcomment(action,mid,uid,identify,page){
	var url = 'apps.php?q=weibo&ajax=1&do='+action;
	var id = buildId(mid,identify);
	var commentObj = getObj('comment_'+id);
	var data = buildData(mid,uid,identify,page);
	ajax.send(url,data,function(){
		var responseText = ajax.request.responseText;
		if(responseText.length < 100) {
			showDialog("warning", responseText,2);
			return false;
		} else {
			commentObj.innerHTML = responseText;
		}
	});
}

function buildId(mid,identify){
	var id = mid;
	if(identify){
		id += '_'+identify;
	}
	return  id;
}

function buildData(mid,uid,identify,page){
	var data = 'mid='+mid;
	if(uid){
		data += '&uids='+uid;
	}
	if(identify){
		data += '&identify='+identify;
	}
	if(page){
		data += '&commentpage='+page;
	}
	return data;
}

function postcomment(form){
	var content = form.writeContent.value;
	var contentLen = strlen(content);
	if(contentLen <= 0 || contentLen > 255 ){
		shockwarning(form.writeContent);
		showText = (contentLen <= 0 ? '评论内容不能为空' : '评论内容不能多于255字节');
		showDialog("error", showText);
		return false;
	}
	var baseUrl = "apps.php?q=weibo&do=postcomment&ajax=1";
	var rnd = Math.random()*(8)+1;
	var url = baseUrl + '&rnd='+rnd;
	form.action = url;
	ajax.send(form.action,form,function(){
		var responseText = ajax.request.responseText;
		if(responseText ==  'ok'){
			var subdo = form.subdo.value;
			var uid = form.uids.value;
			var mid = form.mid.value;
			var identify = form.identify.value;
			var id = buildId(mid,identify);
			var commentnum = getObj('commentnum_'+id);
			if(commentnum){
				var regExp = /\(([0-9]+)\)/;
				if(regExp.test(commentnum.innerHTML)){
					var num = RegExp.$1;
				}
				if(isNaN(num)){
					str = '(1)';
				}else{
					str = '('+(++num)+')';
				}
				commentnum.innerHTML = str;
			}			
			refreshcomment(subdo,mid,uid,identify);
		}else{
			showDialog("error", responseText);
		}
		
	});
}

function shockwarning (obj) {
	var step = 4;
	var itv = setInterval(function(){
		obj.style.backgroundColor = (step%2) ? '' : '#ffdddd';
		step--;
		if (step == 0) {
			clearInterval(itv);
			obj.focus();
		}
	}, 300);
}


function deletecomment(cid,form){
	var mid = form.mid.value;
	var url = 'apps.php?q=weibo&ajax=1&do=deletecomment&cid='+cid+'&mid='+mid;
	showDialog('confirm', '你确定要删除此条评论吗?',0, function(){
		ajax.send(url,'',function(){
			var responseText = ajax.request.responseText;
			if(responseText ==  'ok'){
				var subdo = form.subdo.value;
				var uid = form.uids.value;
				var mid = form.mid.value;
				var identify = form.identify.value;
				var page = form.commentpage.value;
				var id = buildId(mid,identify);
				var commentnum = getObj('commentnum_'+id);
				if(commentnum){
					var regExp = /\(([0-9]+)\)/;
					if(regExp.test(commentnum.innerHTML)){
						var num = RegExp.$1;
					}
					if(isNaN(num) || num<=0){
						str = '';
					}else{
						str = '('+(--num)+')';
					}
					commentnum.innerHTML = str;
				}
				refreshcomment(subdo,mid,uid,identify,page);
				
			}
		});
	});
}

function deletecommentRefersh(mid,cid,page){
	var url = 'apps.php?q=weibo&ajax=1&do=deletecomment&cid='+cid+'&mid='+mid;
	showDialog('confirm', '你确定要删除此条评论吗?',0, function(){
		ajax.send(url, '', function() {
			var responseText = ajax.request.responseText;
			if (responseText ==  'ok') {
				location.href = 'apps.php?q=weibo&do=receive&page='+page;
			} else {
				ajax.guide();
			}
		});
	});
}

function deleteweibo(mid,action,page,menuId){
	var url = 'apps.php?q=weibo&ajax=1&do=deleteweibo&mid='+mid;
	showDialog('confirm', '你确定要删除此条新鲜事吗?',0, function(){
		ajax.send(url,'',function(){
			var responseText = ajax.request.responseText;
			if (responseText ==  'ok') {
				if(space_uid == 'undefined' || space_uid == null){
					getWeiboList(action,page,menuId);
				}else{
					getWeiboList(action,page,menuId,space_uid);
				}
			} else {
				ajax.guide();
			}
		});
	});
}

function replycomment(username,form){
	form.writeContent.value = '回复@'+username+' : ';
	form.ifreplay.value = '1';
}

function showcommentsmile(obj, textArea, tip) {
	if (typeof textArea != 'object') textArea = getObj(textArea);
	if (defaultString && textArea.innerHTML == defaultString)
	{
		textArea.innerHTML = '';
	}
	miniSmile.show(obj);
	miniSmile.apply(function(code, textArea) {
		insertContentToTextArea(textArea, code);
		wordlength(textArea, 255, tip);
	}, textArea);
	textArea.focus();
}

function getFilterWeibo(href) {
	if (href.match(/page=(\d+)/ig)) {
		getWeiboList('filterweibo', RegExp.$1, 'weiboFeed');
	}
	return false;
}

var weiboList = {
	getPage : function(href) {return href.match(/page=(\d+)/) ? RegExp.$1 : 1;},
	getUid : function(href) {return href.match(/uid=(\d+)/) ? RegExp.$1 : 0;},
	filterWeibo : function(href) {getWeiboList('filterweibo', this.getPage(href), 'weiboFeed');scroll(0,0);return false;},
	my : function(href) {getWeiboList('my', this.getPage(href), 'weiboFeed', this.getUid(href));scroll(0,0);return false;},
	attention : function(href) {getWeiboList('attention', this.getPage(href), 'weiboFeed');scroll(0,0);return false;},
	lookround : function(href) {getWeiboList('lookround', this.getPage(href), 'weiboFeed');scroll(0,0);return false;},
	refer : function(href) {getWeiboList('refer', this.getPage(href), 'weiboFeed');scroll(0,0);return false;},
	receive : function(href) {getWeiboList('receive', this.getPage(href), 'weiboFeed');scroll(0,0);return false;}
}

function getWeiboList(action, page, menuId, uid, type) {
	var obj = getObj(menuId);
	if (obj == null) {
		return false;
	}
	if (action == 'filterweibo') {

		if (type == 'all')
		{
			getObj('imgs').checked = true;
			getObj('strings').checked = true;
		} else if (type == 'strings')
		{
			getObj('imgs').checked = '';
			getObj('strings').checked = true;
		} else if (type == 'imgs')
		{
			getObj('imgs').checked = true;
			getObj('strings').checked = '';
		} else {
		}
		getObj('page').value = page;
		ajax.submit(getObj('filterWeiboForm'), function() {
			obj.innerHTML = ajax.runscript(ajax.request.responseText);
		});
	} else if (in_array(action, ['my', 'attention', 'lookround', 'refer', 'receive', 'conloy'])) {
		var url = 'apps.php?ajax=1&q=weibo&do=' + action + '&page=' + page + ((typeof uid != 'undefined' && uid) ? '&uid='+uid : '');
		ajax.send(url, '', function() {
			obj.innerHTML = ajax.runscript(ajax.request.responseText);
		});
	} else if (action == 'reload') {
		setTimeout(function() {location.reload();}, 50);
	} else if (action == 'reloaddelay') {
		setTimeout(function() {location.reload();}, 2000);
	}
}

function ctrlEnter(e,form,callback){
	var e = window.event ? window.event : e;
	if(e.ctrlKey && e.keyCode == 13){
		callback(form);
	}
}

function wordlength(obj, maxlimit, id) {
	var len = strlen(obj.value);
	var value = '';
	if (len > maxlimit) {
		value = '已超出<em>' + (len - maxlimit) + '</em>个字节';
	} else {
		value = '你还可以输入<em>' + (maxlimit - len) + '</em>个字节';
	}
	var showobj = (typeof id == 'undefined') ? $('span_' + obj.id) : $(id);
	if (showobj != null) {
		showobj.innerHTML = value;
		showobj.parentNode.getElementsByTagName('em')[0].className = (maxlimit - len > 0) ? '' : 's1';
	}
}


function transmitWeibo(id,action) {
	var url = "apps.php?q=weibo&do=transmit&ajax=1";
	sendmsg(url, 'mid='+id+'&action='+action, '');
}

function transmitWeiboSubmitForm(form, page,uid) {
	//var transmitAction ='';
	if (typeof form == 'undefined' || !form) {
		return false;
	}
	if (defaultString && getObj('atc_content').innerHTML == defaultString)
	{
		getObj('atc_content').innerHTML = '';
	}
	if (transmitAction == "user_home") {
		transmitAction = "filterweibo";
	}
	page = page ? page : 1;
	ajax.submit(form,function(){	
		var rText = ajax.request.responseText;
		if (rText =='success') {
			getWeiboList(transmitAction,page,'weiboFeed',uid);
			showDialog('success','转发成功!',2);
			return false;
		} else {
			ajax.guide();
		}
	});
}


