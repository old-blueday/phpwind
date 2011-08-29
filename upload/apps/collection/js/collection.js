var collection = {	
	appUrl : 'apps.php?q=collection',
	postdata : verifyhash ? 'verify='+verifyhash :  '',
	$ : function(id){
		return document.getElementById(id);
	},
	post : function(form) {
		var link = form.link.value;
		var preg = /^http\:\/\/.{4,255}$/;
		if (!preg.test(link)) {
			this._showMessage('error','链接地址出错，必须是正确的网址，且以“http://”开头');
			return false;
		}
		form.submit();
	},
	_showMessage : function(status,message) {
		showDialog(status, message);
	},
	
	del : function(id) {
		var _this = this;
		ajax.send('apps.php?q=collection',this.postdata + '&a=del&ajax=1&id='+id,function(){
			var rText = ajax.request.responseText.split('\t');
			if (rText[0] == 'success') {
				var element = _this.$('collection_'+id);
				if (element) {
					window.location.reload();
				} else {
					alert(ajax.request.responseText);
				}
			} else {
				ajax.guide();
			}
		});
	}
}

function showCollection(type,hash,obj,id){
	if (!document.getElementById('share_flash_' + id)) {
		var videoAddr;
		switch (type) {
			case 'youku.com':
				videoAddr = 'http://player.youku.com/player.php/sid/'+hash+'=/v.swf';
				break;
			case 'youtube.com':
				videoAddr = 'http://www.youtube.com/v/'+hash;
				break;
			case 'sina.com.cn':
				videoAddr = 'http://vhead.blog.sina.com.cn/player/outer_player.swf?vid='+hash;
				break;
			case 'sohu.com':
				videoAddr = 'http://v.blog.sohu.com/fo/v4/'+hash;
				break;
			case 'music':
				if (hash.match(/^http\:\/\/.{1,251}\.mp3\??.*$/i)) {
					var hashUrl = encodeURI(hash);
					var vObject = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase=" http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="290" height="40" id="FlashVars" align="middle"><param name="movie" value="u/images/mp3player.swf" /><param name="flashvars" value="soundFile='+hashUrl+'&loop=no&autostart=yes" /><embed height="40" width="290" wmode="transparent" type="application/x-shockwave-flash" src="u/images/mp3player.swf" flashvars ="soundFile='+hashUrl+'&loop=no&autostart=yes" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash"/></object>';
					//var vObject = '<embed height="40" width="290" wmode="transparent" type="application/x-shockwave-flash" src="u/images/mp3player.swf?soundFile='+hash+'&loop=no&autostart=yes"/>';
				} else if (hash.match(/^http\:\/\/.{1,251}\.wma\??.*$/i)) {
					var isIE6=navigator.userAgent.indexOf("MSIE 7.0")==-1&&navigator.userAgent.indexOf("MSIE 8.0")==-1&&navigator.userAgent.indexOf("MSIE 6.0")>0;
					if (isIE6) {
						var vObject = '<object height="64" width="290" data="" classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6"><param value="'+hash+'" name="url"/><param value="'+hash+'" name="src"/><param value="true" name="showcontrols"/><param value="0" name="autostart"/></object>';
					} else {
						var vObject = '<object height="64" width="290" data="" classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6"><param value="'+hash+'" name="url"/><param value="'+hash+'" name="src"/><param value="true" name="showcontrols"/><param value="0" name="autostart"/><object height="64" width="290" data="'+hash+'" type="audio/x-ms-wma"><param value="'+hash+'" name="src"/><param value="1" name="autostart"/><param value="true" name="controller"/></object></object>';
					}
				} else {
					alert('type_error');
					return false;
				}
				break;
			case 'flash':
				if (hash.match(/\.swf\??.*$/i)) {
					var vObject = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase=" http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0" width="480" height="400"><param name="movie" value="'+hash+'" /><embed width="480" height="400" menu="false" quality="high" src="'+hash+'" type="application/x-shockwave-flash" /></object>';
					//var vObject = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="480" height="400"><param name="movie" value="'+hash+'" /><param name="quality" value="high" /><param name="bgcolor" value="#FFFFFF" /><embed width="480" height="400" menu="false" quality="high" src="'+hash+'" type="application/x-shockwave-flash" /></object>';
				} else {
					alert('type_error');
					return false;
				}
				break;
			default:
				showDialog('error','数据已失效，请点击链接地址查看该分享');
				return false;
		}

		if (!vObject) {
			var vObject = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="480" height="400"><param value="transparent" name="wmode"/><param value="'+videoAddr+'" name="movie" /><embed src="'+videoAddr+'" wmode="transparent" allowfullscreen="true" type="application/x-shockwave-flash" width="480" height="400"></embed></object>';
		}
		
		var pObject = obj.parentNode;
		var flash = document.createElement('div');
		flash.id = 'share_flash_' + id;
		flash.innerHTML = vObject;
		pObject.appendChild(flash);

		var close = document.createElement('div');
		close.id = 'share_close_' + id;
		close.className = 'video-close';
		var a = document.createElement('a');
		a.className = 'video-close-link';
		a.href = 'javascript:void(0);';
		a.onclick = function(){
			document.getElementById('share_flash_' + id).style.display = 'none';
			document.getElementById('share_close_' + id).style.display = 'none';
			obj.style.display = '';
		};
		a.innerHTML = '收起';
		close.appendChild(a);
		
		pObject.appendChild(flash);
		pObject.appendChild(close);
	} else {
		document.getElementById('share_flash_' + id).style.display = '';
		document.getElementById('share_close_' + id).style.display = '';
	}
	obj.style.display = 'none';
}

//自定义类型配置配置
function getCollectionTypeConfig() {
	this.createUrl = "apps.php?q=ajax&a=addcollecttype";
	this.delUrl = "apps.php?q=ajax&a=delcollecttype";
	this.upUrl = "apps.php?q=ajax&a=editcollecttype";
}
var typeConfig = new getCollectionTypeConfig();







