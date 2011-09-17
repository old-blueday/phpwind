var addTxt = '添加标签，寻找好友';
function addFocus(e){
	if(e.value == addTxt){
		e.value='';
		e.className = 'input';
	}
}
function addBlur(e){
	if(e.value == ''){
		e.value=addTxt;
		e.className = 'input gray';
	}
}
function keyUpTag(e,ele){
		var e=e||window.event;
		var key=e.keyCode||e.which;
		if(key==13){
			var name=ele.value;
			ele.blur();
			addTag(name);	
		}
}
function addTag(name){
	var tagname = name ? name : getObj('tagname').value;
	var regExp = /[<>$^&\\'"]+/;
	if (tagname.length < 1) {
		showDialog('error','标签名称不得少于1字节');
		return false;
	}
	if (tagname.length > 16) {
		showDialog('error','标签名称的长度不得多于16字节');
		return false;
	}
	if (regExp.test(tagname)) {
		showDialog('error','您输入的符号不是很安全哦');
		return false;
	}
	ajax.send('apps.php?q=ajax&a=addtag&ajax=1','&tagname='+ajax.convert(tagname),function(){
		var rText = ajax.runscript(ajax.request.responseText);
		if (rText.indexOf('<') != -1){
			getObj('tagname').className = 'input gray';
			getObj('tagname').value = addTxt;
			var createtags = getObj('createtags');
			if (getObj('dis_notages')) getObj('dis_notages').style.display = 'none';
			if (getObj('dis_'+name)) getObj('dis_'+name).style.display = 'none';
			createtags.innerHTML = rText + createtags.innerHTML ;
		} else {
			ajax.guide();
		}
	});
}

function delTag(tagid){
	ajax.send('apps.php?q=ajax&a=deltag&ajax=1','tagid='+tagid,function(){
		var rText = ajax.request.responseText;
		if (rText=='success') {
			delElement('tag_'+tagid);
		} else {
			ajax.send();
		}
	});
}

function changeOne(){
	ajax.send('apps.php?q=ajax&a=changetag&ajax=1','',function(){
		var rText = ajax.runscript(ajax.request.responseText);
		if (rText.indexOf('<') != -1){
			var changetags = getObj('changetags');
			changetags.innerHTML = rText;
		} else {
			ajax.guide();
		}
	});
}
