function mouseOverShow(ftid){
	var edit = getObj('edit_'+ftid);
	var del =  getObj('del_'+ftid);
	var ftype =  getObj('ftype_'+ftid);
	edit.style.display = '';
	del.style.display = '';
	ftype.className = 'mr5 fl';
}

function mouseOverOut(ftid){
	var edit = getObj('edit_'+ftid);
	var del =  getObj('del_'+ftid);
	var ftype =  getObj('ftype_'+ftid);
	edit.style.display = 'none';
	del.style.display = 'none';
	ftype.className = 'mr5 fl';
}

function showEidt(ftid,u){
	var li = getObj('ft_'+ftid);
	li.onmouseover = '';
	li.onmouseout = '';
	var a = li.getElementsByTagName('a');
	for (var i=0;i<a.length;i++) {
		a[i].style.display = 'none';
		if(a[i].id == 'ftype_'+ftid){
			a[i].className == 'mr5 fl';
		}
	}

	var cite = getObj('ftypenum_'+ftid);
	cite.style.display = 'none';

	var text= elementBind('input','','fl input','width:40px;');
	text.setAttribute('type','text');
	text.value	= a[i-1].innerHTML;
	li.appendChild(text);
	text.focus();

	var ok	= elementBind('span','','btn2','margin:0 0 0 3px;');
	ok.innerHTML = '<span><button type="button">确认</button></span>';
	var cancel	= elementBind('span','','bt2','margin:0 0 0 3px;');
	cancel.innerHTML = '<span><button type="button">取消</button></span>';

	ok.onclick	= function () {
		var name = char_cv(text.value);
		if (name.length<1) {
			showDialog('error','类型名称不能为空');
			return false;
		}
		ajax.send('pw_ajax.php?action=eidtfriendtype','u='+u+'&typename='+name+'&ftid='+ftid,function(){
			var rText = ajax.request.responseText;
			if (rText == 'success') {
				cite.style.display = '';
				cancelEdit(li,name);
			} else {
				ajax.guide();
			}
		});
		mouseOverOut(ftid)
		li.onmouseover = function(){mouseOverShow(ftid)};
		li.onmouseout = function(){mouseOverOut(ftid)};
	}

	cancel.onclick	= function () {
		cite.style.display = '';
		cancelEdit(li);
		li.onmouseover = function(){mouseOverShow(ftid)};
		li.onmouseout = function(){mouseOverOut(ftid)};
	}
	li.appendChild(ok);
	li.appendChild(cancel);
}
function cancelEdit(li,name){
	li = objCheck(li);
	delElement(li.getElementsByTagName('input')[0]);
	delObjOfTag(li,'span');
	var a = li.getElementsByTagName('a');
	for (var i=0;i<a.length;i++) {
		if (name) {
			a[i].innerHTML	= name;
		}
		a[i].style.display	= '';
	}
	var tmp = li.id.split('_');
	getObj('del_'+tmp[1]).style.display = 'none';
	getObj('edit_'+tmp[1]).style.display = 'none';
	getObj('ftype_'+tmp[1]).className == 'mr5 fl';
}
function addType(id,u){
	if (getObj('ft_add')) {
		changeCreatInputDisplay('ft_add');
		return true;
	}
	creatFriendTypeInput(id,u);
}
function creatFriendTypeInput(id,u){
	var parent	= getObj(id);
	var li	= addChild(parent,'li','ft_add');
	var text= elementBind('input','','input','width:40px;');
	text.setAttribute('type','text');
	li.appendChild(text);
	text.focus();

	var ok	= elementBind('span','','btn2','margin:0 0 0 3px');
	ok.innerHTML = '<span><button type="button">确认</button></span>';
	ok.onclick	= function () {
		var name = char_cv(text.value);
		if (name.length<1) {
			showDialog('error','类型名称不能为空');
			return false;
		}
		ajax.send('pw_ajax.php?action=addfriendtype&','typename='+ajax.convert(name),function(){
			var rText = ajax.request.responseText.split('\t');
			if (rText[0] == 'success') {
				var ftid = rText[1] - 0;
				if (isNaN(ftid)==false) {
					creatFriendType('ft_null',ftid,name,u);
					delElement('ft_add');
				} else {
					alert('error');
				}
			} else {
				ajax.guide();
			}
		});
	}
	li.appendChild(ok);
	var cancel	= elementBind('span','','bt2','margin:0 0 0 3px');
	cancel.innerHTML = '<span><button type="button">取消</button></span>';
	cancel.onclick	= function () {
		li.style.display = 'none';
	}
	li.appendChild(cancel);
}

function changeCreatInputDisplay(id){
	var ft_add = getObj(id);
	if (ft_add.style.display =='none') {
		ft_add.style.display = '';
	} else {
		ft_add.style.display = 'none';
	}
}

function creatFriendType(latter,ftid,name,u){
	latter = objCheck(latter);
	var parent = latter.parentNode;
	var li = elementBind('li','ft_'+ftid);

	var str = '<a id="del_'+ftid+'" class="adel cp mr10" style="display:;" onclick="pwConfirm(\'是否确认删除\',this,function(){delFriendType(\''+ftid+'\',\''+u+'\')})">删除</a>\n ';
	str += '<a id="edit_'+ftid+'"  class="aedit mr5 cp" style="display:;" onclick="showEidt(\''+ftid+'\',\''+u+'\');">编辑</a>\n ';
	str += '<a id="ftype_'+ftid+'" href="u.php?a=friend&ftid='+ftid+'"  class="mr5 fl">'+name+'</a>';
	str += '<cite id="ftypenum_'+ftid+'">[0]</cite>\n ';
	li.innerHTML = str;
	li.onmouseover = function(){mouseOverShow(ftid)};
	li.onmouseout = function(){mouseOverOut(ftid)};
	parent.insertBefore(li,latter);

}

function delFriendType(ftid,u){
	ajax.send('pw_ajax.php?action=delfriendtype','u='+u+'&ftid='+ftid,function(){
		var rText = ajax.request.responseText;
		if (rText == 'success') {
			delElement('ft_'+ftid);
		} else {
			ajax.guide();
		}
	});
}

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

