function newGdCode(obj) {
	var currentTime = new Date().getTime();
	if (is_ie) {
		obj.movie = obj.movie.replace(/[&,?]{1}nowtime=[0-9]+/, '');
		obj.movie = obj.movie.replace(/ck.php/ig, 'ck.php?nowtime='+currentTime);
		if(gdtype == 3)obj.movie = obj.movie.replace(/autoStart=false/ig, 'autoStart=true');
	} else {
		var html = obj.innerHTML;
		html.replace(/[&,?]{1}nowtime=[0-9]+/, '');
		html.replace(/ck.php/ig, 'ck.php?nowtime='+currentTime);
		if (gdtype == 3)html = html.replace(/autoStart=false/ig, 'autoStart=true');
		obj.innerHTML = html;
	}
	return false;
}
function showGdCode(isreturn) {
	var str;
	var nowTimeString = 'nowtime=' + new Date().getTime();
	if (cloudgdcode) {
		str = '<img id="changeGdCode" src="' + cloudcaptchaurl + '&' + nowTimeString + '&" align="top" class="cp sitegdcheck" onclick="changeCkImage(this)" alt="看不清楚，换一张" title="看不清楚，换一张" /><span onclick="changeCkImage(this.previousSibling);" style="margin-left:3px;" class="s4 cp" id="changeGdCode_a">换一个</span>';
	} else {
		if (gdtype == 1 || !gdtype) {
			str = '<img id="changeGdCode" src="ck.php?' + nowTimeString + '" align="top" class="cp sitegdcheck" onclick="changeCkImage(this)" alt="看不清楚，换一张" title="看不清楚，换一张" /><span onclick="changeCkImage(this.previousSibling);" style="margin-left:3px;" class="s4 cp" id="changeGdCode_a">换一个</span>';
		} else {
			//flash & voice
			str = '<object align="top" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0"';
			str += gdtype == 3 ? ' width="25" height="20">' : ' width="'+ flashWidth + '" height="' + flashHeight + '">';
			str += '<param name="quality" value="high" /><param value="transparent" name="wmode" /><param name="movie" value="';
			if (gdtype == 2) {
				//flash
				str += 'ck.php" />';
				middleString = 'src="ck.php" quality="high" width="' + flashWidth + '" height="' + flashHeight + '" pluginspage="http://www.macromedia.com/go/getflashplayer"  type="application/x-shockwave-flash"></embed></object><a class="s4 sitegdcheck" href="javascript:;"';
				str += '<embed ' + middleString + ' onclick="newGdCode(this.previousSibling);return false;" id="changeGdCode"> 换一个</a>';
			} else if (gdtype ==3) {
				//voice
				str += 'images/ck/audio/audio.swf?file=ck.php&songVolume=100&width=150&autoStart=false&repeatPlay=false&showDownload=false" />';
				middleString = 'src="images/ck/audio/audio.swf?file=ck.php&songVolume=100&width=150&autoStart=false&repeatPlay=false&showDownload=false" width="25" height="20" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed></object><span>(点击播放)</span><a id="changeGdCode" class="s4 sitegdcheck" href="javascript:;"';
				str += '<embed '+ middleString + ' onclick="newGdCode(this.previousSibling.previousSibling);return false;"> 换一个</a>';
			}
		}
	}
	if (isreturn == 1) {
		return str;
	} 
	document.write(str);
}

function showgd(id){
	var codeStr;
	var id = id || 'ckcode';
	codeStr = showGdCode(1);
	try{
		if (getObj(id).style.display != '') {
			getObj(id).innerHTML = codeStr;
		}
		getObj(id).style.display="";
	}catch(e){}
}

function changeCkImage(obj) {
	var tmpurl = obj.src;
	tmpurl = tmpurl.replace(/nowtime=[0-9]+/, '');
	obj.src = tmpurl + 'nowtime='+ new Date().getTime(); 
}

function changeAllKindsGdCode(classname, obj) {
	classname = classname || 'sitegdcheck';
	var allgdcodes = getElementsByClassName(classname, obj);
	for (var i = 0; i < allgdcodes.length; i++) {
		if (typeof allgdcodes[i].onclick == 'function') allgdcodes[i].onclick();
	}
	return true;
}