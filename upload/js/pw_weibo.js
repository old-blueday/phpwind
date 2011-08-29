/**
 * 微博页面交互
 * @author suqian
 * @date 2010-7-5
 */
var space_uid = space_uid,timer;
var weibo_isTopic = 0;
var weibo_topicName = '';
var defaultString = defaultString ? defaultString : '';

weiboPhotos.queue = new Array();//相册队列
weiboPhotos.qLength = 0;//队列个数

function weiboPhotos() {
	this.space = 1;
	this.key = 1;
	this.ulprex = 'photolist_';
	this.push = function(key,value){
		this.exists(key) && weiboPhotos.qLength++;
		weiboPhotos.queue[key] = {'value':value,'counter':0,'lastcounter':-1};
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
		if (obj.counter == counter && getObj('photocontainer_' + key).style.display != 'none') {
			this.hidePhoto(key);
			return false;
		}
		this.key = key;
		obj.counter = counter;
		this.showTips(key, obj);
		this.createTmp(path, key);
	};	
	this.nextPhoto = function(key){
		var obj = weiboPhotos.queue[key];
		var photo = obj.value;
		obj.counter++;
		this.showTips(key, obj);
		this.createTmp(photo[obj.counter].s_path, key);
	};
	this.createTmp = function(src, key){
		var imgpretmp = getObj('imgpre'),self = this;
		if(imgpretmp){
			document.body.removeChild(imgpretmp);
			imgpretmp.onload='';
			delete imgpretmp;
		}
		this.imgpre = new Image();
		this.imgpre.src = src;
		if (this.imgpre.complete){
			this.reposPhoto(key);
			return;
		}
		this.imgpre.onload = function(){
			self.reposPhoto(key);
		}
	};
	this.reposPhoto = function(key){
		var photo = this.imgpre;
		var _ = this;
		var path = getObj('photo_path_' + key);
		var zoomoutmask = getObj('photo_small_' + key);
		var container = getObj('photocontainer_' + key);
		var mask = getObj('photo_mask_' + key);
		var list = getObj(this.ulprex + key);
		if (list.getAttribute('count') == 1) {
			list.style.display = 'none';
			zoomoutmask.onclick = function(){
				_.hidePhoto(key);
			};
			zoomoutmask.style.cursor = "url('u/images/zoomout.cur'),auto";
		}
		path.src = photo.src;
		path.style.width = 'auto';
		if (photo.width > 440) path.style.width = '440px';
		mask.style.display = container.style.display = '';
		container.appendChild(mask);
		/*var popo= getObj('photo_pop');
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
		*/
	};
	this.prevPhoto = function(key){
		var obj = weiboPhotos.queue[key];
		var photo = obj.value;
		obj.counter--;
		this.showTips(key, obj);
		this.createTmp(photo[obj.counter].s_path, key);
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
	this.hidePhoto = function(key){
		clearInterval(timer);
		timer = null;
		var obj = weiboPhotos.queue[key];
		if (obj.lastcounter != -1) {
			liElements = getObj(this.ulprex + key).getElementsByTagName('li');
			liElements[obj.lastcounter].style.cursor = liElements[obj.lastcounter].firstChild.style.cursor = "url('u/images/zoomin.cur'),auto";
		}
		getObj('photocontainer_' + key).style.display='none';
		getObj('photo_mask_' + key).style.display='none';
		if (getObj(this.ulprex + key).getAttribute('count') == 1) {
			getObj(this.ulprex + key).style.display = '';
		}
		if (getObj('comment_to_weibo')) getObj('comment_to_weibo').style.display='none';
	}
	this.bigPhotoUrl = function(key) {
		var obj = weiboPhotos.queue[key];
		var photo = obj.value;
		return photo[obj.counter].s_path;
	}
	this.showTips = function(key, obj) {
		var prephoto = getObj('prephoto_' + key);
		var nextphoto = getObj('nextphoto_' + key);
		var photo = obj.value;
		if (obj.lastcounter != -1) {
			liElements = getObj(this.ulprex + key).getElementsByTagName('li');
			liElements[obj.lastcounter].style.cursor = liElements[obj.lastcounter].firstChild.style.cursor = "url('u/images/zoomin.cur'),auto";
		}
		if(obj.counter == 0){
			if(photo.length > 1){
				this.display(key, nextphoto, 'next');
			}
			this.hide(key, prephoto, 'prev');
		}
		if(obj.counter == photo.length-1){
			if(obj.counter > 0){
				this.display(key, prephoto, 'prev');
			}
			this.hide(key, nextphoto, 'next');
		}
		if(obj.counter > 0 && obj.counter < photo.length-1){
			this.display(key, prephoto, 'prev');
			this.display(key, nextphoto, 'next');
		}
		if (getObj(this.ulprex + key).getAttribute('count') != 1) {
			liElement = getObj(this.ulprex + key).getElementsByTagName('li');
			liElement[obj.counter].style.cursor = liElement[obj.counter].firstChild.style.cursor = "url('u/images/zoomout.cur'),auto";
			obj.lastcounter = obj.counter;
		}
	}
	this.hide = function(key, obj, pos) {
		if (getObj(this.ulprex + key).getAttribute('count') == 1) {
			obj.style.display = 'none';
		}
		obj.onclick = function(){
			return false;
		};
		obj.title = pos == 'next' ? '已是最后一张' : '已是第一张';
		obj.style.cursor = 'default';
	}
	this.display = function(key, obj, pos) {
		var _ = this;
		if (pos == 'next') {
			obj.title = '下一张';
			obj.onclick = function() {
				_.nextPhoto(key);
			};
			obj.style.cursor = "url('u/images/next.cur'),auto";
			return;
		}
		obj.title = '上一张';
		obj.onclick = function() {
			_.prevPhoto(key);
		};
		obj.style.cursor = "url('u/images/pre.cur'),auto";
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
	if (getObj('weiboFeed0') != null && getObj('weiboFeed1') != null){
		var _id = getObj('weiboFeed0').style.display == 'block' ? 0 : 1;
		commentObj = getObj('comment_'+id+'_'+_id);
	}	

	if (_id == undefined && ajaxObj) {
		commentObj.removeChild(ajaxObj);
	}else if(_id != undefined && commentObj.innerHTML.length>10 ){
		commentObj.removeChild(ajaxObj);
	}else if(_id != undefined && getObj('comment_'+id+'_'+(_id^1)) && getObj('comment_'+id+'_'+(_id^1)).innerHTML.length>10){
		getObj('comment_'+id+'_'+(_id^1)).removeChild(ajaxObj);
		refreshcomment(action,mid,uid,identify);
	}else {
		refreshcomment(action,mid,uid,identify);
	}
}

function refreshcomment(action,mid,uid,identify,page){
	var url = 'apps.php?q=weibo&ajax=1&do='+action;
	var id = buildId(mid,identify);
	var commentObj = getObj('comment_'+id);
	if (getObj('weiboFeed0') != null && getObj('weiboFeed1') != null){
		commentObj = getObj('weiboFeed0').style.display == 'block' ? getObj('comment_'+id+'_0') : getObj('comment_'+id+'_1');
	}
	
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

function deleteweibo(mid,action,page,menuId,isTopic,topicName){
	var url = 'apps.php?q=weibo&ajax=1&do=deleteweibo&mid='+mid;
	weibo_isTopic = isTopic;
	weibo_topicName = topicName;
	if (getObj('weiboFeed0') != null && getObj('weiboFeed1') != null){
		menuId = getObj('weiboFeed0').style.display == 'block' ? 'weiboFeed0' : 'weiboFeed1';
	}	
	showDialog('confirm', '你确定要删除此条新鲜事吗?',0, function(){
		if (getObj(url) == null){
			setTimeout(function() {location.reload();}, 300);
		}	
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
	/*
	if (typeof defaultString != 'undefined' && defaultString && textArea.innerHTML == defaultString)
	{
		textArea.innerHTML = '';
	}
	*/
	miniSmile.show(obj);
	miniSmile.apply(function(code, textArea) {
		if (typeof defaultString != 'undefined' && defaultString && textArea.innerHTML == defaultString)
		{
			textArea.innerHTML = '';
		}
		insertContentToTextArea(textArea, code);
		wordlength(textArea, 255, tip);
	}, textArea);
	textArea.focus();
}

function getFilterWeibo(href) {
	if (href.match(/page=(\d+)/ig)) {
		getWeiboList('filterweibo', RegExp.$1, getCurrentWeiboDiv());
	}
	return false;
}

var weiboList = {
	getPage : function(href) {return href.match(/page=(\d+)/) ? RegExp.$1 : 1;},
	getUid : function(href) {return href.match(/uid=(\d+)/) ? RegExp.$1 : 0;},
	filterWeibo : function(href) {getWeiboList('filterweibo', this.getPage(href), getCurrentWeiboDiv());scroll(0,0);return false;},
	my : function(href) {getWeiboList('my', this.getPage(href), getCurrentWeiboDiv(), this.getUid(href));scroll(0,0);return false;},
	attention : function(href) {getWeiboList('attention', this.getPage(href), getCurrentWeiboDiv());scroll(0,0);return false;},
	lookround : function(href) {getWeiboList('lookround', this.getPage(href), getCurrentWeiboDiv());scroll(0,0);return false;},
	refer : function(href) {getWeiboList('refer', this.getPage(href), getCurrentWeiboDiv());scroll(0,0);return false;},
	receive : function(href) {getWeiboList('receive', this.getPage(href), getCurrentWeiboDiv());scroll(0,0);return false;},
	topics : function(href) {
		if(href.match(/topic=([^&]+)/ig))weibo_topicName = RegExp.$1;
		getWeiboList('topics', this.getPage(href), getCurrentWeiboDiv());scroll(0,0);return false;
	}
}

function ajaxPage(url,objId){
	var obj = getObj(objId);
	ajax.send(url, '', function() {
		obj.innerHTML = ajax.runscript(ajax.request.responseText);
	});
	return false;
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
	} else if (in_array(action, ['my', 'attention', 'lookround', 'refer', 'receive', 'conloy' ,'topics'])) {
		if (weibo_isTopic == 1) action = 'topics';
		var url = 'apps.php?ajax=1&q=weibo&do=' + action + '&page=' + page;
		if (typeof uid != 'undefined' && uid && weibo_isTopic == '0') url += '&uid='+uid;
		if (weibo_topicName != '') url += '&topic='+weibo_topicName;
		ajax.send(url, '', function() {
			obj.innerHTML = ajax.runscript(ajax.request.responseText);
		});
	} else if (action == 'reload') {
		if (getObj('weiboFeed0') != null && getObj('weiboFeed1') != null){
			var _s = window.location.href;
			_s = (_s.indexOf('#') == -1) ? _s : _s.substr(0, _s.indexOf('#'));
			window.location.href =  _s + "#" + getCurrentWeiboDiv(); 
		}
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
		value = '<em>' + len + '/255</em>';
	}
	var showobj = (typeof id == 'undefined') ? $('span_' + obj.id) : $(id);
	if (showobj != null) {
		showobj.innerHTML = value;
		showobj.parentNode.getElementsByTagName('em')[0].className = (maxlimit - len > 0) ? '' : 's1';
	}
}


function transmitWeibo(id,action,isTopic,topicName) {
	var url = "apps.php?q=weibo&do=transmit&ajax=1";
	sendmsg(url, 'mid='+id+'&action='+action + '&istopic='+isTopic+'&topicname='+topicName, '');
}

function transmitWeiboSubmitForm(form, page,uid,isTopic,topicName) {
	//var transmitAction ='';
	if (typeof form == 'undefined' || !form) {
		return false;
	}
	weibo_isTopic = isTopic;
	weibo_topicName = topicName;
	/*
	//说转发内容可以为空了
	if (getObj('atc_content').value == '' || (defaultString && getObj('atc_content').innerHTML == defaultString)){
		showDialog('error','内容不能为空');
		return false;
	}
	*/
	if (typeof defaultString != 'undefined' && defaultString && getObj('atc_content').innerHTML == defaultString)
	{
		getObj('atc_content').innerHTML = '';
	}
	if (transmitAction == "user_home") {
		transmitAction = "filterweibo";
	}
	if (getObj('weiboFeed0') != null && getObj('weiboFeed1') != null){
		transmitAction = 'reload';
	}

	page = page ? page : 1;
	ajax.submit(form,function(){	
		var rText = ajax.request.responseText;
		if (rText =='success') {
			getWeiboList(transmitAction,page,getCurrentWeiboDiv(),uid);
			showDialog('success','转发成功!',2);
			return false;
		} else {
			ajax.guide();
		}
	});
}

function posttopicWeiboSubmitForm(form, page) {
	//var transmitAction ='';
	if (typeof form == 'undefined' || !form) {
		return false;
	}
	page = page ? page : 1;
	ajax.submit(form,function(){	
		var rText = ajax.request.responseText;
		if (rText =='success') {
			getWeiboList('topics',page,getCurrentWeiboDiv());
			showDialog('success','发布成功!',2);
			return false;
		} else {
			ajax.guide();
		}
	});
}

function addAttention(topicName){
	var topicName = topicName ? topicName : getObj('topicName').value;
	if (typeof(topicName) == "undefined") {
		showDialog('error','数据错误');
		return false;
	}
	ajax.send('apps.php?q=ajax&a=addAttention','&topicName='+topicName,function(){
		var rText = ajax.request.responseText.split('\t');
		if (rText[0] == 'success') {
			window.location.reload();
		} else {
			ajax.guide();
		}
	});
}

function delAttention(topicName){
	if (typeof(topicName) == "undefined") {
		showDialog('error','数据错误');
		return false;
	}
	ajax.send('apps.php?q=ajax&a=delAttention','&topicName='+topicName,function(){
		var rText = ajax.request.responseText.split('\t');
		if (rText[0] == 'success') {
			window.location.reload();
		} else {
			ajax.guide();
		}
	});
}

function switchDiv(key){
	getObj('switch_tag' + key).className="s5 b";
	getObj('switch_tag' + (key^1)).className="s6";
	getObj('weiboFeed' + key).style.display='block';
	getObj('weiboFeed' + (key^1)).style.display='none';

	var smile = getObj('smileContainer');
	var p = smile.parentNode; 
	p.removeChild(smile);
	var m = document.getElementById("weiboFeed" + key);
	m.appendChild(smile);
}

function getCurrentWeiboDiv(){ 
	if (getObj('weiboFeed0') == null || getObj('weiboFeed1') == null) return 'weiboFeed';
	return getObj('weiboFeed0').style.display == 'block' ? 'weiboFeed0' : 'weiboFeed1';
}

function darenPhotos(){
	this.photos = getObj("darenPhotos").getElementsByTagName('li');
	this.photoNum = this.photos.length;
	this.position = 0;
	this.numOfRow = 3; // 一行显示几张图片
	this.numOfScroll = 3; // 一次滚动3张
	this.show = function(d) { 
		if (this.photoNum <= this.numOfRow) return;
		this.position = this.positionConvert(this.position + d * this.numOfScroll);
		for(i=0; i<this.photoNum; i++){
			this.photos.item(i).style.display = 'none';
		}
		for(i=this.position; i<this.position + this.numOfRow; i++){
			this.photos.item(this.positionConvert(i)).style.display = 'block';
		}
	}
	this.positionConvert = function(p){
		p = p >= this.photoNum ?  p % this.photoNum : p;
		p = p < 0 ? p + this.photoNum : p; 
		return p;
	}
}
if (getObj("darenPhotos")){
	var daren = new darenPhotos();
	daren.show(0);
}
