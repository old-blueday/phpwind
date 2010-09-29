function changSpanDisplay(id){
	id = objCheck(id);
	var spans = id.getElementsByTagName('span');
	for (var i=0;i<spans.length;i++) {
		if (spans[i].style.display == 'none') {
			spans[i].style.display = '';
		} else {
			spans[i].style.display = 'none';
		}
	}
}
function showEidt(ftid,u){
	var li = getObj('ft_'+ftid);
	li.onmouseover = '';
	li.onmouseout = '';
	var spans = li.getElementsByTagName('span');
	for (var i=0;i<spans.length;i++) {
		spans[i].style.display = 'none';
	}
	var a = li.getElementsByTagName('a');
	for (var i=0;i<a.length;i++) {
		a[i].style.display = 'none';
	}

	var text= elementBind('input','','input','width:90px;margin-right:10px;');
	text.setAttribute('type','text');
	text.value	= a[i-1].innerHTML;
	li.appendChild(text);
	text.focus();

	var ok	= elementBind('input','','bt3','');
	ok.setAttribute('type','button');
	ok.value= '确定';

	var cancel	= elementBind('input','','bt','');
	cancel.setAttribute('type','button');
	cancel.value= '取消';

	ok.onclick	= function () {
		var name = char_cv(text.value);
		if (name.length<1) {
			showDialog('error','类型名称不能为空');
			return false;
		}
		ajax.send('apps.php?q=ajax&a=eidtfriendtype','u='+u+'&name='+name+'&ftid='+ftid,function(){
			var rText = ajax.request.responseText;
			if (rText == 'success') {
				cancelEdit(li,name);
			} else {
				ajax.guide();
			}
		});
	}

	cancel.onclick	= function () {
		cancelEdit(li);
	}
	li.appendChild(ok);
	li.appendChild(cancel);
}
function cancelEdit(li,name){
	li = objCheck(li);
	li.onmouseover = function () {
		changSpanDisplay(li);
	}
	li.onmouseout = function () {
		changSpanDisplay(li);
	}
	var input = li.getElementsByTagName('input');
	while (input[0]) {
		delElement(input[0]);
	}
	var a = li.getElementsByTagName('a');
	for (var i=0;i<a.length;i++) {
		if (name) {
			a[i].innerHTML	= name;
		}
		a[i].style.display	= '';
	}
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

	var text= elementBind('input','','input','width:90px;margin-right:10px;');
	text.setAttribute('type','text');
	li.appendChild(text);
	text.focus();

	var ok	= elementBind('input','','bt3','');
	ok.setAttribute('type','button');
	ok.value= '确定';
	ok.onclick	= function () {
		var name = char_cv(text.value);
		if (name.length<1) {
			showDialog('error','类型名称不能为空');
			return false;
		}
		ajax.send('apps.php?q=ajax&a=addfriendtype','u='+u+'&name='+ajax.convert(name),function(){
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
	var cancel	= elementBind('input','','bt','margin:0 0 0 .3em');
	cancel.setAttribute('type','button');
	cancel.value= '取消';
	cancel.onclick	= function () {
		text.value = '取消';
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
	li.onmouseover = function () {
		changSpanDisplay(li);
	}
	li.onmouseout = function () {
		changSpanDisplay(li);
	}
	li.innerHTML = '<span class="fr gray" style="cursor: pointer;display:none" onclick="pwConfirm(\'是否确认删除\',this,function(){delFriendType(\''+ftid+'\',\''+u+'\')})">删除</span><span class="fr gray mr10" style="cursor: pointer;display:none" onclick="showEidt(\''+ftid+'\',\''+u+'\');">编辑</span><a href="u.php?a=friend&ftid='+ftid+'">'+name+'</a>';
	parent.insertBefore(li,latter);
}
function delFriendType(ftid,u){
	ajax.send('apps.php?q=ajax&a=delfriendtype','u='+u+'&ftid='+ftid,function(){
		var rText = ajax.request.responseText;
		if (rText == 'success') {
			delElement('ft_'+ftid);
		} else {
			ajax.guide();
		}
	});
}

function showFriendTypeBox(friendid,name,ftid){
	if (isNaN(friendid)) {
		alert('error');
	}
	ajax.send('apps.php?q=ajax&a=showftlist','u='+winduid,function(){
		var rText = ajax.request.responseText.split('\t');
		if (rText[0]=='success') {
			if (rText[1]!='') {
				var types = JSONParse(rText[1]);
				if (typeof(types)=='object') {
					var friendTypeList = creatFriendTypeList(types,ftid,friendid);
					read.setMenu(friendTypeList);
					read.menupz();
				}
			} else {
				showDialog('error','您还没有设置好友分组');
			}
		}
	});
}

function creatFriendTypeList(obj,ftid,friendid){
	var container	= elementBind('div','','','width:400px;height:100%');
	var title	= elementBind('div','','h b');
	title.innerHTML = '设定好友所属分组';
	container.appendChild(title);
	var inner_div	= addChild(container,'div','','menu-text p10');
	var ul	= addChild(inner_div,'ul','','ul-50pc cc');
	for (var i in obj) {
		if (typeof(obj[i])=='object') {
			creadListLi(obj[i],ul,ftid);
		}
	}
	var footer	= addChild(container,'div','','bdtA bgB','');
	var footer_title = addChild(footer,'div','','cc p10','padding-bottom:0;');
	footer_title.innerHTML = '好友分组只有你自己能够看到';
	var tar	= addChild(footer,'div','','tar pdD');
	var ok	= elementBind('input','','bt3');
	ok.type	= 'button';
	ok.value= '确定';
	ok.onclick	= function () {
		var radios	= document.getElementsByName('friendtype_radio');
		var ftid	= 0;
		for (var i=0;i<radios.length;i++) {
			if (radios[i].checked) {
				ftid= radios[i].value;
				break;
			}
		}
		ajax.send('apps.php?q=ajax&a=setfriendtype','friendid='+friendid+'&ftid='+ftid,function(){
			var rText = ajax.request.responseText;
			if (rText=='success') {
				window.location.reload();
			} else {
				ajax.guide();
			}
		});
	}

	var cansel	= elementBind('input','','bt','margin-left:5px');
	cansel.type	= 'button';
	cansel.value= '取消';
	cansel.onclick	= closep;
	tar.appendChild(ok);
	tar.appendChild(cansel);

	return container;
}

function creadListLi(obj,ul,ftid){
	var li = addChild(ul,'li');
	var add = ftid == obj['ftid'] ? 'checked="checked"' : '';
	li.innerHTML = '<input name="friendtype_radio" type="radio" value="'+obj['ftid']+'" '+add+'> '+ obj['name'];
	ul.appendChild(li);
}