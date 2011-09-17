
var newAtt = {
	aid : 0,

	getElements : function (s) {
		var o = new Array();
		var p = s.getElementsByTagName('select');
		for (var i = 0; i < p.length; i++) {
			o.push(p[i]);
		}
		var p = s.getElementsByTagName('input');
		for (var i = 0; i < p.length; i++) {
			o.push(p[i]);
		}
		return o;
	},

	create : function(isSimple) {
		newAtt.isSimple = isSimple;
		if (!IsElement('attach'))
			return;
		//newAtt.aid++;
		newAtt.aid = getObj('attach').childNodes.length + 1;
		attachnum--;
		var s = getObj('att_mode').getElementsByTagName("tr")[0].cloneNode(true);
		var id = newAtt.aid;
		s.id = 'att_div' + id;

		var tags = newAtt.getElements(s);
		for (var i = 0; i < tags.length; i++) {
			tags[i].name += id;
			tags[i].id = tags[i].name;
			if (tags[i].name == 'attachment_' + id) {
				tags[i].onchange = function(){newAtt.up(id);};
			}
		}
		getObj('attach').appendChild(s);
	},

	up : function(id) {
		var div  = getObj('att_div' + id), atm = getObj('attachment_' + id), path= atm.value,filesize=-1;
		var attach_ext = path.substr(path.lastIndexOf('.') + 1, path.length).toLowerCase();
		/*不同附件类型的文件尺寸限制  zph*/
		var maxUploadSize = 2048000;
		if(typeof(allow_size) != 'undefined' && allow_size.indexOf(attach_ext) !== false){
			var attach_ext_arr = allow_size.split(';');
			for (var i in attach_ext_arr) {
				if(attach_ext_arr[i].split(':')[0].replace(/^\s+|\s+$/g, '') == attach_ext){
					maxUploadSize = parseInt(attach_ext_arr[i].split(':')[1]) * 1024;
				}
			}
		}
		maxUploadSize = maxUploadSize>0?maxUploadSize:2048000;
		/* end */
		if(atm.files)
		{
			filesize =atm.files[0].fileSize;
		}
		else if (window.navigator.userAgent.indexOf("MSIE 6") > 0 && !window.XMLHttpRequest) {
			var img = new Image();
			img.dynsrc =path;
			filesize =img.fileSize;
		}
		if(filesize && (filesize>maxUploadSize)){
			showDialog('warning','<font color=\'red\'>' + attach_ext + '</font>附件大小不能超过 <font color=\'red\'>'+ maxUploadSize/1024 +'K </font>');
			atm.value=null;
			if(is_ie){
				atm.select();
				document.execCommand('delete');
			}else
				atm.value=null;
				return false;
		}

		if (allow_ext != '  ' && (attach_ext == '' || allow_ext.indexOf(' ' + attach_ext + ' ') == -1)) {
			if (IsElement('att_span' + id)) {
				getObj('att_span'+id).parentNode.removeChild(getObj('att_span'+id));
			}
			if (path != '') {
				if (typeof showDialog == 'function') {
					showDialog('warning','附件类型不匹配');
				} else {
					alert('附件类型不匹配!');
				}
			}
			return false;
		}
		getObj('attachment_' + id).onmouseover = function(){newAtt.viewimg(id)};
		if (!IsElement('att_span' + id)) {
			var li = document.createElement("span");
			if(!this.isSimple)
			{
				var s = document.createElement("a");
				s.className    = 'bta';
				s.unselectable = 'on';
				s.onclick      = function(){newAtt.addupload(id)};
				s.innerHTML    = '插入';
				li.appendChild(s);
			}
			var s    = document.createElement("a");
			s.className    = 'bta';
			s.unselectable = 'on';
			s.onclick      = function(){newAtt.delupload(id)};
			s.innerHTML    = '删除';
			li.appendChild(s);
			li.id = 'att_span' + id;
			div.lastChild.appendChild(li);
		}
		if (attachnum > 0 && getObj('attach').lastChild.id == 'att_div' + id) {
			newAtt.create(this.isSimple);
		}
	},

	sel : function(o) {
		var p = o.parentNode.parentNode;
		var s = p.getElementsByTagName('select')[1];
		switch (o.value) {
			case '1':
				if (!IsElement('atc_requireenhide') || getObj('atc_requireenhide').disabled == true) {
					if (typeof showDialog == 'function') {
						showDialog('error','您没有权限加密附件');
					} else {
						alert('您没有权限加密附件!');
					}
					o.selectedIndex = 0;return;
				}
				break;
			case '2':
				if (!IsElement('atc_requiresell') || getObj('atc_requiresell').disabled == true) {
					if (typeof showDialog == 'function') {
						showDialog('error','您没有权限出售附件');
					} else {
						alert('您没有权限出售附件!');
					}
					o.selectedIndex = 0;return;
				}
				break;
			default:return;
		}
		var d = getObj('attmode_' + o.value).options;
		s.length = 0;
		for (var i = 0; i < d.length; i++) {
			s.options[i] = new Option(d[i].text,d[i].value);
		}
	},

	viewimg : function(id) {
		var path = getObj('attachment_' + id).value;
		if (!is_ie || !path.match(/\.(jpg|gif|png|bmp|jpeg)$/ig))
			return;
		newAtt.getimage(path, 320, 'attachment_' + id);
	},

	getimage : function(path,maxwh,id) {
		var img = new Image();
		img.src = path+"?ra="+Math.random();
		img.onload = function(){
			getObj('viewimg').innerHTML =  '<div style="padding:5px;"><img src="' + img.src + '"' + ((this.width>maxwh || this.height>maxwh) ? (this.width > this.height ? ' width' : ' height') + '="' + maxwh + '"' : '') + ' /></div>';
			read.open('viewimg', id, 3);
		};
	},

	addupload : function(attid) {
		if (typeof WYSIWYD == 'function') {
			editor.focusEditor();
			AddCode(' [upload=' + attid + '] ','');
		} else {
			var atc = document.FORM.atc_content;
			var text = ' [upload=' + attid + '] ';
			if (document.selection) {
				var sel = document.selection.createRange();
				if (sel.parentElement().name == 'atc_content') {
					sel.text = text;
					sel.select();
				} else {
					atc.value += text;
				}
			} else if (typeof atc.selectionStart != 'undefined') {
				var prepos = atc.selectionStart;
				atc.value = atc.value.substr(0,atc.selectionStart) + text + atc.value.substr(atc.selectionEnd);
				atc.selectionStart = prepos + text.length;
				atc.selectionEnd = prepos + text.length;
			} else {
				atc.value += ' [upload=' + attid + '] ';
			}
		}
	},

	delupload : function(id) {
		getObj('att_div' + id).parentNode.removeChild(getObj('att_div' + id));
		attachnum++;
		if (getObj('attach').hasChildNodes() == false) {
			newAtt.create();
		}
		var delstring = new RegExp('\\[upload=' + id + '\\]', 'g');
		if (typeof WYSIWYD == 'function') {
			editor._editMode == 'textmode' ? editor._textArea.value = editor._textArea.value.replace(delstring, '') : editor._doc.body.innerHTML = editor._doc.body.innerHTML.replace(delstring, '');
		} else {
			document.FORM.atc_content.value = document.FORM.atc_content.value.replace(delstring, '');
		}
	},

	add : function(o) {
		var id = o.parentNode.parentNode.parentNode.id;
		id = id.substr(id.lastIndexOf('_') + 1);
		addattach(id);
	}
}

var oldAtt = {

	init : function() {

		var o = getObj('ajaxtable');
		for (var i in attachs) {
			var s = getObj('att_mode').firstChild.cloneNode(true);
			s.id = 'att_' + i;

			s.getElementsByTagName('select')[0].name	= 'oldatt_special[' + i + ']';
			s.getElementsByTagName('select')[0].value	= attachs[i][4];
			if (attachs[i][4] > 0) {
				newAtt.sel(s.getElementsByTagName('select')[0]);
			}
			s.getElementsByTagName('select')[1].name	= 'oldatt_ctype[' + i + ']';
			s.getElementsByTagName('select')[1].value	= attachs[i][6];
			s.getElementsByTagName('input')[2].name		= 'oldatt_needrvrc[' + i + ']';
			s.getElementsByTagName('input')[2].value	= attachs[i][5];
			s.getElementsByTagName('input')[1].name		= 'oldatt_desc[' + i + ']';
			s.getElementsByTagName('input')[1].value	= attachs[i][7];

			var fname = s.getElementsByTagName('td')[0];
			fname.innerHTML = '<div style="width:160px;overflow:hidden;"><span id="attach_' + i + '" onmouseover="oldAtt.view(this);"><span class="s1 b">' + attachs[i][0] + '</span>&nbsp;(' + attachs[i][1] + 'K)</span></div>';

			var li = document.createElement('span');
			li.id = 'atturl_' + i;
			li.innerHTML = attachs[i][2];
			li.style.display = 'none';
			fname.appendChild(li);

			var li = document.createElement("span");
			var a = document.createElement("a");
			a.id = 'md_' + i;
			a.className    = 'bta';
			a.unselectable = 'on';
			a.onclick      = function(){oldAtt.modify(this)};
			a.innerHTML    = '修改';
			li.appendChild(a);
			var a = document.createElement("a");
			a.className    = 'bta';
			a.onclick      = function(){newAtt.add(this)};
			a.innerHTML    = '插入';
			a.unselectable = 'on';
			li.appendChild(a);
			s.lastChild.appendChild(li);

			var li = document.createElement('td');
			li.innerHTML = '<input type="checkbox" name="keep[]" value="' + i + '" checked />';
			s.insertBefore(li, s.firstChild);

			o.appendChild(s);
		}
	},

	modify : function(o) {
		var id = o.parentNode.parentNode.parentNode.id;
		id = id.substr(id.lastIndexOf('_') + 1);
		var s = getObj('attach_' + id);
		var p = oldAtt.create(id);
		s.parentNode.insertBefore(p,s);
		p.select();
		s.style.display = 'none';
		oldAtt.change(id,2);
	},

	create : function(id) {
		var o		= document.createElement('input');
		o.type		= 'file';
		o.className	= 'input';
		o.size		= 20;
		o.maxLength	= 100;
		o.name		= 'replace_' + id;
		o.id		= 'replace_' + id;
		o.onmouseover = function(){oldAtt.view(this)};
		return o;
	},

	cancle : function(id) {
		var o = getObj('replace_' + id);
		var s = getObj('attach_' + id);
		o.parentNode.removeChild(o);
		s.style.display = '';
		oldAtt.change(id,1);
	},

	change : function(id,type) {
		var s = getObj('md_' + id);
		if (type == 2) {
			s.innerHTML = '取消';
			s.onclick = function(){oldAtt.cancle(id)};
		} else {
			s.innerHTML = '修改';
			s.onclick = function(){oldAtt.modify(this)};
		}
	},

	view : function(o) {
		var id = o.parentNode.parentNode.parentNode.id;
		id = id.substr(id.lastIndexOf('_') + 1);
		if (IsElement('replace_' + id)) {
			var path = getObj('replace_' + id).value;
			if (!is_ie || !path.match(/\.(jpg|gif|png|bmp|jpeg)$/ig))
				return;
			newAtt.getimage(path, 320, 'att_' + id);
		} else {
			var path = getObj('atturl_' + id).innerHTML;
			if (path.match(/\.(jpg|gif|png|bmp|jpeg)$/ig)) {
				newAtt.getimage(path, 320, 'att_' + id);
			}
		}
	}
}

var flashAtt = {

	flashObj : null,
	url : 'job.php?action=mutiupload&random=' + Math.floor(Math.random()*100),

	init : function(isSimple) {
		flashAtt.isSimple = isSimple;
		if (IsElement('flash')) {
			getObj('flash').parentNode.removeChild(getObj('flash'));
			return;
		}
		var flashVar = {
			url : getObj('headbase').href + escape(flashAtt.url),
			mutiupload : (allowmutinum - mutiupload)
		};
		var params   = {
			menu: "false",
			scale: "noScale",
			allowScriptAccess: "always",
			value:'always',
			wmode:'transparent'
		};
		var attr = {id:'mutiupload',name:'mutiupload'};
		swfobject.embedSWF(imgpath + '/upload.swf?'+Math.random(), "flashUploadPanel", "250", "46", "10.0.0", "js/expressInstall.swf",flashVar,params,attr,function(e){
			flashAtt.flashObj = e.ref;
		});
	},

	show : function() {
		var list = flashAtt.flashObj.getQueue();
		getObj("uploadFileInfo")?getObj("uploadFileInfo").style.display="":0;
		var qlist = getObj('qlist');
		while (qlist.hasChildNodes()) {
			qlist.removeChild(qlist.firstChild);
		}
		for (var i=0;i<list.length;i++)	{
			if (list[i].error == 'null')
				list[i].error = '';
			var tr = document.createElement('tr');
			tr.id  = 'l_' + i;
			var td = document.createElement('td');
			td.innerHTML = '<div style="width:200px;overflow:hidden;">' + list[i].name + '</div>';
			tr.appendChild(td);

			var td = document.createElement('td');
			td.innerHTML = flashAtt.getSize(list[i].size);
			tr.appendChild(td);

			var td = document.createElement('td');

			if (list[i].error == '') {
				td.innerHTML = (list[i].loaded > -1 ? parseInt(100 * list[i].loaded / list[i].size) : '0') + '%';
			} else {
				switch (list[i].error) {
					case 'exterror':
						list[i].error = '附件类型不匹配';break;
					case 'toobig':
						list[i].error = '附件大小超过限制';break;
					case 'numerror':
						list[i].error = '附件个数超过限制';break;
				}
				td.innerHTML = list[i].error;
			}
			tr.appendChild(td);

			var td = document.createElement('td');
			td.innerHTML = '<span onclick="flashAtt.del(this)" style="cursor:pointer;">x</span>';
			tr.appendChild(td);

			qlist.appendChild(tr);
		}
	},

	progress:function(i,percent) {
		document.getElementById('l_'+i).getElementsByTagName('td')[2].innerHTML = percent + '%';
	},

	use : function() {
		ajax.send('pw_ajax.php?action=mutiatt','',function() {
			var rText = ajax.request.responseText.split('\t');
			if (rText[0] == 'ok') {
				eval(rText[1]);
				if (IsElement('flashatt')) {
					var o = getObj('flashatt');
					while (o.hasChildNodes()) {
						o.removeChild(o.firstChild);
					}
				} else {
					var o = document.createElement('tbody');
					o.id  = 'flashatt';
					getObj('attach').parentNode.insertBefore(o,getObj('attach'));
				}
				for (var i in att) {
					var s = getObj('att_mode').firstChild.cloneNode(true);
					s.id = 'flashatt_' + i;
					try{
						s.getElementsByTagName('select')[0].name = 'flashatt[' + i + '][special]';
						s.getElementsByTagName('select')[1].name = 'flashatt[' + i + '][ctype]';
						s.getElementsByTagName('input')[2].name = 'flashatt[' + i + '][needrvrc]';
					}catch(e){}
					s.getElementsByTagName('input')[1].name = 'flashatt[' + i + '][desc]';
					var fname = s.getElementsByTagName('td')[0];
					fname.innerHTML = '<div style="width:200px;overflow:hidden;">' + att[i][0] + '&nbsp;(' + att[i][1] + 'K)' + '</div>';
					fname.title = att[i][0] + '\n上传日期：' + att[i][3];
					fname.onmouseover = function() {flashAtt.view(this);};

					var li = document.createElement('span');
					li.id = 'atturl_' + i;
					li.innerHTML = att[i][2];
					li.style.display = 'none';
					fname.appendChild(li);

					var li = document.createElement("span");
					if (!flashAtt.isSimple) {
						var a = document.createElement("a");
						a.className    = 'bta';
						a.unselectable = 'on';
						a.onclick      = function(){newAtt.add(this)};
						a.innerHTML    = '插入';
						li.appendChild(a);
					}
					var a = document.createElement("a");
					a.saveId = i;
					a.className    = 'bta';
					a.onclick      = function(){flashAtt.remove(this)};
					a.innerHTML    = '删除';
					li.appendChild(a);
					s.lastChild.appendChild(li);

					o.appendChild(s);
				}
			} else {
				ajax.guide();
			}
		});
	},

	clear : function() {
		ajax.send('pw_ajax.php?action=delmutiatt','',function() {
			if (ajax.request.responseText == 'ok') {
				if (IsElement('flashatt')) {
					getObj('flashatt').parentNode.removeChild(getObj('flashatt'));
				}
				getObj('flashAtt_use').style.display = 'none';
				getObj('flashAtt_clear').style.display = 'none';
				//getObj('mutiinfo').parentNode.removeChild(getObj('mutiinfo'));
			} else {
				showDlg('error','删除失败')
			}
		});
	},

	view : function(o) {
		var id = o.parentNode.id;
		id = id.substr(id.lastIndexOf('_') + 1);
		var path = getObj('atturl_' + id).innerHTML;
		if (path.match(/\.(jpg|gif|png|bmp|jpeg)$/ig)) {
			newAtt.getimage(path, 320, 'flashatt_' + id);
		}
	},

	finish : function() {
		flashAtt.show();
		flashAtt.use();
		getObj("uploadFileInfo")?getObj("uploadFileInfo").style.display="none":0;
	},

	del : function(o) {
		var s  = o.parentNode.parentNode;
		var id = s.id.substr(2);
		flashAtt.flashObj.remove(id);
		flashAtt.show();
	},

	remove : function(o) {
		ajax.send('pw_ajax.php?action=delmutiattone','aid='+o.saveId,function() {
			if (ajax.request.responseText == 'ok') {
				var s = o.parentNode.parentNode.parentNode;
				s.parentNode.removeChild(s);
				if (typeof WYSIWYD == 'function') {
					if (editor._editMode == 'textmode') {
						var delstring = new RegExp('\\[attachment=' + o.saveId + '\\]', 'g');
						editor._textArea.value = editor._textArea.value.replace(delstring, '');
					} else {
						var delstring = new RegExp('<img[^>]*type="attachment\\_' + o.saveId + '"[^>]*>', 'ig');
						editor._doc.body.innerHTML = editor._doc.body.innerHTML.replace(delstring, '');
					}
				}
			} else {
				showDlg('error','删除失败')
			}
		});
	},

	getSize:function(n)	{
		var pStr = 'BKMGTPEZY';
		var i = 0;
		while(n>1024)
		{
			n=n/1024;
			i++;
		}
		var t = 3-Math.ceil(Math.log(n)/Math.LN10);
		return Math.round(n*Math.pow(10,t))/Math.pow(10,t)+pStr.charAt(i);  
	}
}

var photoflashAtt = {

	baseurl : 'apps.php?q=photos&',
	photoflashObj : null,

	init : function() {
		if (IsElement('flash')) {
			getObj('flash').parentNode.removeChild(getObj('flash'));
			return;
		}
		var div = document.createElement('div');
		div.id  = 'flash';

		var flashvar = 'url=' + getObj('headbase').href + 'job.php';
		if (is_ie) {
			var html = '<object id="mutiupload" name="mutiupload" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="250" height="46"><param name="movie" value="' +  imgpath + '/uploadphoto.swf" /><param name="FlashVars" value="' + flashvar + '"/><param name="allowScriptAccess" value="always" /><param name="wmode" value="transparent" /></object>';
		} else {
			var html = '<embed type="application/x-shockwave-flash" src="' + imgpath + '/uploadphoto.swf" width="250" height="46" id="mutiupload" name="mutiupload" allowScriptAccess="always" wmode="transparent" FlashVars="' + flashvar + '" />';
		}
		html += '<table width="450"><tr><td class="wname">文件名</td><td>描述</td><td width="15%">大小</td><td width="20%">上传进度</td><td width="5%">删</td></tr><tbody id="qlist"></tbody></table>';
		getObj('attsize').parentNode.insertBefore(div,getObj('attsize'));
		div.innerHTML = html;
		photoflashAtt.photoflashObj = document['mutiupload'];
	},

	show : function(s) {

		var list = photoflashAtt.photoflashObj.getQueue();
		var qlist = getObj('qlist');

		var aid = getObj('aidsel_info');
		if (aid.value == '') {
			showDialog('success','请选择相册',2);
			return false;
		}
		photoflashAtt.getaid(aid.value);

		while (qlist.hasChildNodes()) {
			qlist.removeChild(qlist.firstChild);
		}
		for (var i in list)	{
			if (list[i].error == 'null')
				list[i].error = '';
			var tr = document.createElement('tr');
			tr.id  = 'l_' + i;
			var td = document.createElement('td');
			td.className = 'wname';
			td.innerHTML = list[i].name;
			tr.appendChild(td);

			var td = document.createElement('td');
			var pintro_id = 'pintro_' + i;
			var pintro = list[i].name.split('.');
			if (s) {
				td.innerHTML = '<input name="pintro" id="' + pintro_id + '" type="text" class="input" value="' + pintro[0] + '">';
			} else {
				td.innerHTML = '锁定描述';
			}

			tr.appendChild(td);

			var td = document.createElement('td');
			td.innerHTML = Math.round(list[i].size/1024) + 'K';
			tr.appendChild(td);

			var td = document.createElement('td');
			if (list[i].error == '') {
				td.innerHTML = (list[i].loaded > -1 ? parseInt(100 * list[i].loaded / list[i].size) : '0') + '%';
			} else {
				switch (list[i].error) {
					case 'exterror':
						list[i].error = '类型不匹配';break;
					case 'toobig':
						list[i].error = '大小超过限制';break;
					case 'numerror':
						list[i].error = '个数超过限制';break;
				}
				td.innerHTML = list[i].error;
			}
			tr.appendChild(td);

			var td = document.createElement('td');
			td.innerHTML = '<span onclick="photoflashAtt.del(this)" class="updel" style="cursor:pointer;">x</span>';
			tr.appendChild(td);

			qlist.appendChild(tr);
		}
	},

	getaid : function(aid) {
		photoflashAtt.photoflashObj.getalbumid(aid);
		photoflashAtt.getallowmutinum();
	},

	getallowmutinum : function() {
		var aid = getObj('aidsel_info');
		ajax.send(photoflashAtt.baseurl + 'a=getallowflash&aid='+aid.value,'',function() {
		var rText = ajax.request.responseText.split('\t');
			if (rText[0] == 'ok') {
				if (rText[1]) {
					photoflashAtt.photoflashObj.getallowmutinums(rText[1]);
				}
			} else {
				ajax.guide();
			}
		});
	},

	getAllNames : function() {
		var photolist = photoflashAtt.photoflashObj.getQueue();
		var values="";
		for (var i in photolist) {
			if (photolist[i].error == 'null')
				photolist[i].error = '';
			values += getObj('pintro_' + i).value+'|';
		}
		values = encodeURI(values);
		photoflashAtt.photoflashObj.beginUpload("filenames="+values.slice(0,-1));
	},

	view : function(o) {
		var id = o.parentNode.id;
		id = id.substr(id.lastIndexOf('_') + 1);
		var path = getObj('atturl_' + id).innerHTML;
		if (path.match(/\.(jpg|gif|png|bmp|jpeg)$/ig)) {
			newAtt.getimage(path, 320, 'att_' + id);
		}
	},

	finish : function() {
		photoflashAtt.show();
		var toaid = getObj('aidsel_info').value;
		ajax.send('apps.php?q=ajax&a=mutiuploadphoto','&aid=' + toaid,function(){
			var rText = ajax.request.responseText;
			if (rText == 'success') {
				photoflashAtt.photoflashObj.stopupload();
				read.setMenu(photoflashAtt.jumpphoto(toaid));
				read.menupz();
			} else {
				ajax.guide();
				setTimeout(window.location.reload(),2000);
			}
		});
	},

	jumpphoto : function(toaid) {
		var maindiv	= elementBind('div','','','width:300px;height:100%');
		var title = elementBind('div','','popTop');
		title.innerHTML = '上传成功!';
		maindiv.appendChild(title);
		var innerdiv = addChild(maindiv,'div','','p15');
		var ul = addChild(innerdiv,'ul','');
		var li = addChild(ul,'li');
		li.innerHTML = '照片上传成功，是否继续上传？<br />注：附件超过大小或超过相册数将上传不成功！';

		var footer	= addChild(maindiv,'div','','popBottom','');
		var tar	= addChild(footer,'div','','');
		var ok	= elementBind('span','','btn2','');
		ok.innerHTML = '<span><button type="button">继续</button></span>';	

		ok.onclick	= function () {
			window.location.href = photoflashAtt.baseurl + 'a=upload&job=flash&aid=' + toaid;
		}
		var toview	= elementBind('span','','bt2','');
		toview.innerHTML = '<span><button type="button">浏览</button></span>';
		toview.onclick	= function () {
			window.location.href = photoflashAtt.baseurl + 'a=album&aid=' + toaid;
		}

		tar.appendChild(ok);
		tar.appendChild(toview);

		return maindiv;
	},

	del : function(o) {
		var s  = o.parentNode.parentNode;
		var id = s.id.substr(2);
		photoflashAtt.photoflashObj.remove(id);
		photoflashAtt.show(1);
	},

	remove : function(o) {
		var s = o.parentNode.parentNode.parentNode;
		s.parentNode.removeChild(s);
	},
	progress:function(i,percent) {
		document.getElementById('l_'+i).getElementsByTagName('td')[3].innerHTML = percent + '%';
	}
}