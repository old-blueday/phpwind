//自定义类型配置配置
function getFriendTypeConfig() {
	this.createUrl = "apps.php?q=ajax&a=addfriendtype";
	this.delUrl = "apps.php?q=ajax&a=delfriendtype";
	this.upUrl = "apps.php?q=ajax&a=eidtfriendtype";
}
var typeConfig = new getFriendTypeConfig();


function setFriendType(obj,friendid,ftid) {
	if (isNaN(friendid)) {
		alert('error');
	}
	ajax.send('pw_ajax.php?action=setfriendtype','friendid='+friendid+'&ftid='+ftid,function(){
		var rText = ajax.request.responseText.split('\t');
		if (rText[0]=='success') {
			var element = document.getElementById('ftypename_'+friendid);
			if (element) {
				showDialog('success','分组成功!',1);
				element.innerHTML = rText[1];
			}
		} else {
			ajax.guide();
		}
	});
}

function creadListLi(obj,ul,ftid){
	var li = addChild(ul,'li');
	var add = ftid == obj['ftid'] ? 'checked="checked"' : '';
	li.innerHTML = '<input name="friendtype_radio" type="radio" value="'+obj['ftid']+'" '+add+'> '+ obj['name'];
	ul.appendChild(li);
}

function delFriend(touid){
	ajax.send('pw_ajax.php?action=delfriend','touid='+touid,function(){
		var rText = ajax.request.responseText.split('\t');
		if (rText[0]=='success') {
			window.location.reload();
		} else {
			ajax.guide();
		}
	});
}

function addFriend(touid){
	ajax.send('pw_ajax.php?action=addfriend&touid='+touid+'&reload=1','',ajax.get);
}


/*
 * 朋友快速搜索
 *
 * @param string type 字段类型
 * @param string keyword 关键字
 */
function getSearchResult(type, keyword) {
	var url = 'u.php?a=friend&type=find&step=2&'+type+'=1&keyword='+keyword;
	window.location.href = url;
}


function delObjOfTag(obj,tagName){
	var span = obj.getElementsByTagName(tagName);
	for (var i=0;i<span.length;i++) {
		delElement(span[i]);
		delObjOfTag(obj,tagName);
	}



}

