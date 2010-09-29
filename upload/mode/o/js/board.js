function postBoard(){
	var title = getObj('board').value;
	var uid = getObj('board_uid').value;
	if (typeof(uid) == "undefined") {
		showDialog('error','没有要留言的对象');
		return false;
	}
	if (title.length < 3 || title.length > 200) {
		showDialog('error','留言的字数请保证在3～200之内');
		return false;
	}
	if (uid == winduid) {
		showDialog('error','不能自己给自己留言');
		return false;
	}
	
	ajax.send('apps.php?q=ajax&a=postboard','&uid='+uid+'&title='+ajax.convert(title),function(){
		var rText = ajax.request.responseText.split('\t');

		if (rText[0]=='success') {
			creatBoard('board_container',rText[1],rText[2],rText[3]);
			getObj('board').value = '';
		} else {
			ajax.guide();
		}
	});
}
function delBoard(bid){
	if (isNaN(bid)) {
		showDialog('error','错误');
	}
	ajax.send('apps.php?q=ajax&a=delboard','id='+bid,function(){
		var rText = ajax.request.responseText;
		if (rText=='success') {
			delElement('board_'+bid);
		} else {
			ajax.send();
		}
	});
}
function creatBoard(container,bid,face,title){
	if (isNaN(bid)) {
		showDialog('error','错误');
	}
	container = objCheck(container);
	var dl	= elementBind('dl','board_'+bid,'cc');
	var dt	= elementBind('dt');
	var img	= elementBind('img','','img-50');
	img.src	= face;
	var img_a = elementBind('a');
	img_a.href = 'u.php';
	img_a.appendChild(img);
	dt.appendChild(img_a);
	var dd  = elementBind('dd','','dd60');
	var del_a = elementBind('a','','del fr mr10','cursor: pointer;');
	del_a.setAttribute('onclick',"pwConfirm('是否确定删除本条留言',this,function(){delBoard('"+bid+"')})");
	del_a.innerHTML = '删除';
	var username_a = elementBind('a','','b');
	username_a.href = 'u.php';
	username_a.innerHTML = windid;
	var postdate_span = elementBind('span','','gray');
	var date = new Date();
	var thispost = dateFormat(date,'yyyy-mm-dd hh:ii:ss');
	postdate_span.innerHTML = thispost+': ';
	var title_p = elementBind('p','','f14');
	title_p.innerHTML = title;
	
	dd.appendChild(del_a);
	dd.appendChild(username_a);
	dd.appendChild(postdate_span);
	dd.appendChild(title_p);
	dl.appendChild(dt);
	dl.appendChild(dd);
	/*
	var dd2 = elementBind('dd','','dd30 ddbor');
	var username_a = elementBind('a');
	username_a.href = 'mode.php?q=user';
	username_a.innerHTML = windid;
	var postdate_span = elementBind('span','','f10 gray');
	var date = new Date();
	var thispost = dateFormat(date,'yyyy-mm-dd hh:ii:ss');
	postdate_span.innerHTML = thispost+': ';
	var br = elementBind('br');
	var title_span = elementBind('span');
	title_span.innerHTML = title;
	dd2.appendChild(username_a);
	dd2.appendChild(postdate_span);
	dd2.appendChild(br);
	dd2.appendChild(title_span);
	dl.appendChild(dt);
	dl.appendChild(dd);
	dl.appendChild(dd2);
	*/
	var createboardbox = getObj('createboardbox');
	createboardbox.insertBefore(dl,createboardbox.firstChild);
}