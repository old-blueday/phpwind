//自定义类型配置配置
function getDiaryTypeConfig() {
	this.createUrl = "apps.php?q=ajax&a=adddiarytype";
	this.delUrl = "apps.php?q=ajax&a=deldiarytype";
	this.upUrl = "apps.php?q=ajax&a=eidtdiarytype";
}
var typeConfig = new getDiaryTypeConfig();



function add_dtid(u,id) {/*写日志页分类添加*/
	if (isNaN(u) && winduid != u) {
		showDialog('error','非法操作');
		read.menu.style.top="450px";
	}
	ajax.send('apps.php?q=ajax&a=adddiarytype','u='+u+'&b=1',function(){
		var rText = ajax.request.responseText.split('\t');
		if (rText[0]=='success') {
			if (rText[1]=='1') {
				read.setMenu(create_dtid(id));
				read.menupz();
				read.menu.style.top="450px";
			} else {
				showDialog('error','非法操作');
				read.menu.style.top="450px";
			}
		}
	});
}

function create_dtid(id) {/*写日志页分类添加*/
	selid = objCheck(id);
	var maindiv	= elementBind('div','','','width:300px;height:100%');
	var title = elementBind('div','','popTop');
	title.innerHTML = '增加日志分类';
	maindiv.appendChild(title);
	var innerdiv = addChild(maindiv,'div','','p10');
	var ul = addChild(innerdiv,'ul','');
	var li = addChild(ul,'li');
	li.innerHTML = '分类名称';

	var text= elementBind('input','','input','margin-left:10px');
	text.setAttribute('type','text');
	li.appendChild(text);
	var innername = document.createTextNode(' 小于20字节');
	li.appendChild(innername);
	text.focus();

	var footer	= addChild(maindiv,'div','','popBottom','');
	var tar	= addChild(footer,'div','','tar');
	var ok	= elementBind('span','','btn2','');
	ok.setAttribute('type','button');
	ok.innerHTML = '<span><button type="button">确认</button></span>';


	ok.onclick	= function () {
		var name = char_cv(text.value);
		if (name.length<1) {
			showDialog('error','<font color="red">分类</font> 名称不能为空');
			read.menu.style.top="450px";
			return false;
		}
		ajax.send('apps.php?q=ajax&a=adddiarytype','u='+winduid+'&name='+ajax.convert(name),function(){
			var rText = ajax.request.responseText.split('\t');
			if (rText[0] == 'success') {
				var dtid = rText[1] - 0;
				if (isNaN(dtid)==false) {
					var option = elementBind('option');
					option.innerHTML = name;
					getObj('dtid_add').parentNode.getElementsByTagName('button')[0].innerHTML= name;
					//var innername = document.createTextNode(name);
					//option.appendChild(innername);
					option.value = dtid;
					option.selected = 'selected';
					selid.insertBefore(option,null);
					showDialog('success','分类添加成功!',2);
					read.menu.style.top="450px";
					closep();
				} else {
					showDialog('error','非法错误');
					read.menu.style.top="450px";
				}
			} else {
				ajax.guide();
			}
		});
	}

	var cansel	= elementBind('span','','bt2','');
	cansel.type	= 'button';
	cansel.innerHTML= '<span><button type="button">取消</button></span>';

	cansel.onclick	= closep;

	tar.appendChild(ok);
	tar.appendChild(cansel);

	return maindiv;
}

function optionsel(id,ifsendweibo) {/*权限选择*/
	copy = objCheck('if_copy');
	if (isNaN(id)) {
		showDialog('error','非法操作');
	}
	if (id == '0') {
		copy.disabled = '';
		copy.checked = 'checked';
	} else if (id == '1') {
		copy.disabled = '';
		copy.checked = 'checked';
	} else if (id == '2') {
		copy.disabled = 'disabled';
		copy.checked = '';
	}
	if(ifsendweibo){
		sendweibo = objCheck('lab_weibo');
		if(id == '0'){
			sendweibo.style.display = '';
			sendweibo.checked = true;
		}else{
			sendweibo.style.display = 'none';
			sendweibo.checked = false;
		}
	}
}

function delDiary(id,u,space){/*删除日志*/
	ajax.send('apps.php?q=ajax&a=deldiary&id='+id,'',function(){
		var rText = ajax.request.responseText.split('\t');
		if (rText[0] == 'success') {
			var element = document.getElementById('diary_'+id);
			if (element) {
				element.parentNode.removeChild(element);
				if (space != 2) {
					window.location.href = basename;
				}
			} else {
					window.location.reload();
			}
		} else {
			ajax.guide();
		}
	});
}

function Copydiary(did,dtid,privacy) {/*日志转载*/
	ajax.send('apps.php?q=ajax&a=copydiary&did='+did+'&dtid='+dtid+'&privacy='+privacy,'',function(){
		var rText = ajax.request.responseText.split('\t');
		if (rText[0]=='success') {
			read.setMenu(create_copy(rText[1]));
			read.menupz();
		} else {
			ajax.guide();
		}
	});
}

function create_copy(did) {/*转载提示*/
	var maindiv	= elementBind('div','','','width:300px;');
	var title = elementBind('div','','popTop');
	title.innerHTML = '转载提示';
	maindiv.appendChild(title);
	var innerdiv = addChild(maindiv,'div','','p15');
	var ul = addChild(innerdiv,'ul','');
	var li = addChild(ul,'li');
	li.innerHTML = '转载成功，日志已存在我的日志中，是否要去浏览？';

	var footer	= addChild(maindiv,'div','','popBottom','');
	var tar	= addChild(footer,'div','','tar');


	var ok	= elementBind('span','','btn2','');
	ok.innerHTML = '<span><button type="button">确认</button></span>';

	ok.onclick	= function () {
		window.location.href = 'apps.php?q=diary&a=detail&did='+did;
	}


	var cansel	= elementBind('span','','bt2','');
	cansel.innerHTML = '<span><button type="button">关闭</button></span>';
	cansel.onclick	= closep;

	tar.appendChild(ok);
	tar.appendChild(cansel);

	return maindiv;
}

function ajaxpage(url,type,u,space) {/*浏览日志*/
	ajax.send(url,'',function() {
		var rText = ajax.request.responseText.split('\t');
		if (rText[0] == 'success') {
			if (rText[1]) {
				var tourl = rText[2];
				window.location.href = tourl + 'did=' + rText[1];
			} else {
				ajax.request.responseText = type == 'next' ? '已经是最后一篇日志' : '已经是第一篇日志';
				ajax.guide();
			}
		} else {
			ajax.guide();
		}
	});
	return false;
}



function deldiaryatt(did,aid) {
	if(!confirm('确定要删除此附件？')) return false;
	ajax.send('apps.php?q=diary&ajax=1','action=delatt&did='+did+'&aid='+aid,function(){
		if (ajax.request.responseText == 'success') {
			var o = getObj('att_'+aid);
			o.parentNode.removeChild(o);
			showDialog('success','删除成功!',2);
		} else {
			ajax.guide();
		}
	});
}

