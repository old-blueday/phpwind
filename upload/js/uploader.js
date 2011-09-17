var imgpath = 'images';
/***************************
 * 新版上传文件
 * @author yuyang
 * $Id$
 */
var uploader = {
	startId:1,
	mode:0,
	/**
	 * 初始化
	 */
	init:function(){
		ajax.send('pw_ajax.php?action=mutiatt','',function() {
			var rText = ajax.request.responseText.split('\t');
			if (rText[0] == 'ok') {
				eval(rText[1]);
				var tbody = document.getElementById('attach').getElementsByTagName('tbody')[0];
				var tfoot = document.getElementById('attach').getElementsByTagName('tfoot')[0]; 
				for(var i in att)
				{
					var row = tfoot.rows[0].cloneNode(true);
					var tds = row.getElementsByTagName('td');
					tds[0].innerHTML = att[i][0] + '&nbsp;(' + att[i][1] + 'K)<span id="atturl_'+i+'" style="display:none">'+att[i][2]+'</span>';
					tds[0].title = att[i][0] + '\n上传日期：' + att[i][3];
					tds[0].id='uploadfile_'+i;
					try{
						row.getElementsByTagName('select')[0].name = 'flashatt[' + i + '][special]';
						row.getElementsByTagName('select')[1].name = 'flashatt[' + i + '][ctype]';
						row.getElementsByTagName('input')[1].name = 'flashatt[' + i + '][needrvrc]';
					}catch(e){}
						row.getElementsByTagName('input')[0].name = 'flashatt[' + i + '][desc]';
						if(uploader.insertable)
							row.getElementsByTagName('td')[2].innerHTML='<span><a href="javascript:;" onclick="addattach('+i+');" class="bta">插入</a><a href="javascript:;" onclick="uploader.del(this,'+i+');" class="bta">删除</a></span>';
						else
							row.getElementsByTagName('td')[2].innerHTML = '<span><a href="javascript:;" onclick="uploader.del(this,'+i+');" class="bta">删除</a></span>';
					if (att[i][2].match(/\.(jpg|gif|png|bmp|jpeg)$/ig))
					{
						tds[0].onmouseover = function() {
							uploader.view(this);
						};
					}
					tbody.appendChild(row);
					document.getElementById('flashAtt_use').innerHTML='<a style="padding-right: 0pt;" href="javascript:;" hidefocus="true">已有附件('+tbody.rows.length+')</a>'
				}
			}
			if(uploader.mode==0)
			{
				uploader.singleTab();
			}
		});
	},
	/**
	 * 切换到批量上传
	 */
	mutiTab:function(){
		document.getElementById('singleUploadMod').style.display = 'none';
		
		document.getElementById('batUpload').style.display = '';
		document.getElementById('formUploadBtn').className='';
		document.getElementById('batUploadBtn').className='current';
		document.getElementById('attach').style.display = document.getElementById('attach').getElementsByTagName('tbody')[0].rows.length?'':'none';
		document.getElementById('uploadFileInfo').style.display = document.getElementById('qlist').rows.length?'':'none';
		//载入flash
		if(!uploader.flash)
		{
			var flashVar = {url:document.getElementById('headbase').href+'job.php?action=mutiupload&random='+Math.floor(Math.random()*100)};
			var params   = {
				menu: "false",  
				scale: "noScale",
				allowScriptAccess: "always",
				value:'always',
				wmode:'transparent' 
			};
			var attr = {id:'mutiupload',name:'mutiupload'};
			loadjs('js/swfobject.js','','',function(){
				swfobject.embedSWF(imgpath + '/uploader.swf', "B_uploader_container", "250", "46", "10.0.0", "js/expressInstall.swf",flashVar,params,attr,function(e){
						uploader.flash = e.ref;
				});
			});
		}
		this.countFile();
		uploader.mode=1;
	},
	/**
	 * 切换到普通上传
	 **/
	singleTab:function(){
		document.getElementById('uploadFileInfo').style.display = 'none';
		document.getElementById('batUpload').style.display = 'none';
		document.getElementById('singleUploadMod').style.display = '';
		document.getElementById('formUploadBtn').className='current';
		document.getElementById('batUploadBtn').className='';
		document.getElementById('attach').style.display = '';
		var tbody = document.getElementById('attach').getElementsByTagName('tbody')[0];
		var tfoot = document.getElementById('attach').getElementsByTagName('tfoot')[0];
		if(tbody.getElementsByTagName('tr').length>=uploader.maxLength)
			tfoot.style.display='none';
		uploader.mode=0;
	},
	/**
	 * 新增普通上传
	 */
	insert:function(ele)
	{
		var tbody = document.getElementById('attach').getElementsByTagName('tbody')[0];
		var tfoot = document.getElementById('attach').getElementsByTagName('tfoot')[0];
		if(ele.parentNode.parentNode.parentNode.tagName=='TBODY' && !ele.value)
		{
			tbody.removeChild(ele.parentNode.parentNode);
			if(tbody.rows.length<uploader.maxLength && uploader.mode==0)
				tfoot.style.display='';
			uploader.countFile();
			return;
		}
		var tr1 = tfoot.getElementsByTagName('tr')[0];
		var v = tr1.getElementsByTagName('input')[0].value;
		if(v)
		{
			var ext = v.substr(v.lastIndexOf('.')+1).toLowerCase();
			if(allow_ext.indexOf(' '+ext+' ')<0){
				showDialog('warning','附件类型不匹配',2);
				var fileinput = tr1.getElementsByTagName('input')[0];
				fileinput.value='';
				fileinput.select();
				document.execCommand('Delete');
				return;
			}
			var tr2 = tr1.cloneNode(true);
			var inputs = tr1.getElementsByTagName('input');
			inputs[0].name += '_'+uploader.startId;
			inputs[1].name += '_'+uploader.startId;
			
			tr2.getElementsByTagName('input')[0].value='';
			tr2.getElementsByTagName('input')[1].value='';
			
			setTimeout(function(){
			//插入删除
				if(uploader.insertable)
					tr1.getElementsByTagName('td')[2].innerHTML='<span><a href="javascript:;" onclick="uploader.insert2editor(this);" class="bta">插入</a><a href="javascript:;" onclick="uploader.del(this);" class="bta">删除</a></span>';
				else
					tr1.getElementsByTagName('td')[2].innerHTML = '<span><a href="javascript:;" onclick="uploader.del(this);" class="bta">删除</a></span>';
				tbody.appendChild(tr1);
				tfoot.appendChild(tr2);
				if(tbody.getElementsByTagName('tr').length>=uploader.maxLength)
					tfoot.style.display='none';
			},0);
			uploader.startId++;
		}
	},
	/**
	 * 删除附件列表中的文件
	 */
	del:function(e,id)
	{
		var tr = e.parentNode.parentNode.parentNode;
		var tbody = tr.parentNode;
		var tfoot = document.getElementById('attach').getElementsByTagName('tfoot')[0];
		if(id)
		{
			ajax.send('pw_ajax.php?action=delmutiattone','aid='+id,function() {
				if (ajax.request.responseText == 'ok') {
					tbody.removeChild(tr);
					if(tbody.getElementsByTagName('tr').length<uploader.maxLength&&uploader.mode==0)
						tfoot.style.display='';
					uploader.countFile();
				} else {
					showDlg("error","删除失败")
				}
			});
		}else{
			tbody.removeChild(tr);
			if(tbody.getElementsByTagName('tr').length<uploader.maxLength&&uploader.mode==0)
				tfoot.style.display='';
			uploader.countFile();
		}
		if(uploader.mode==1)
			document.getElementById('attach').style.display = document.getElementById('attach').getElementsByTagName('tbody')[0].rows.length?'':'none';
	},
	/**
	 * 列出待上传的文件
	 */
	list:function(queue)
	{
		var qlist = document.getElementById('qlist');
		while(j = qlist.rows.length)
		{
			qlist.deleteRow(0);
		}
		for(var i=queue.length-1;i>=0;--i)
		{
			var tr = qlist.insertRow(0);
			var cel1 = tr.insertCell(0);
			cel1.className='wname';
			cel1.innerHTML = queue[i].name;

			
			var cel2 = tr.insertCell(1);
			cel2.innerHTML = uploader.getSize(queue[i].size);

			
			var cel3 = tr.insertCell(2);
			if (queue[i].error == '') {
				cel3.innerHTML = '0%';
			} else {
				switch (queue[i].error) {
					case 'exterror':
						cel3.innerHTML='类型不匹配';
						tr.error=1;
						break;
					case 'toobig':
						cel3.innerHTML='大小超过限制';
						tr.error=1;
						break;
				}
			}
			var cel4 = tr.insertCell(3);
			cel4.innerHTML = '<span onclick="uploader.mutidel(this)" class="updel" style="cursor:pointer;">x</span>';
		}
		this.countFile();
		document.getElementById('uploadFileInfo').style.display = document.getElementById('qlist').rows.length?'':'none';
	},
	/**
	 * 删除flash中的文件
	 */
	mutidel:function(ele){
		uploader.flash.remove(ele.parentNode.parentNode.sectionRowIndex);
		document.getElementById('uploadFileInfo').style.display = document.getElementById('qlist').rows.length?'':'none';
	},
	/**
	 * 计算大小
	 */
	getSize:function(m)
	{
		var pStr = 'BKMGTPEZY',
			i = Math.floor(Math.log(m/Math.LN2 / 10) );
		var n = m/Math.pow(1024, i),
			t = 3-Math.ceil(Math.log(n)/Math.LN10);
		return Math.round(n*Math.pow(10,t))/Math.pow(10,t)+pStr.charAt(i);  
	},
	/**
	 * 进度控制
	 */
	progress:function(i,percent)
	{
		document.getElementById('qlist').rows[i].getElementsByTagName('td')[2].innerHTML = percent + '%';
	},
	//单个文件上传成功
	complete:function(i)
	{
		var qlist = document.getElementById('qlist');
		var tbody = document.getElementById('attach').getElementsByTagName('tbody')[0];
		var tfoot = document.getElementById('attach').getElementsByTagName('tfoot')[0]; 
		var row = document.getElementById('qlist').rows[i];
		var tr1 = tfoot.rows[0].cloneNode(true);
		
		tr1.getElementsByTagName('td')[0].innerHTML = row.cells[0].innerHTML;
		if(uploader.insertable)
			tr1.getElementsByTagName('td')[2].innerHTML = '<span><a href="javascript:;" onclick="uploader.del(this);" class="bta">插入</a></span><span><a href="javascript:;" onclick="uploader.del(this);" class="bta">删除</a></span>';
		else
			tr1.getElementsByTagName('td')[2].innerHTML = '<span><a href="javascript:;" onclick="uploader.del(this);" class="bta">删除</a></span>';
		tbody.appendChild(tr1);
		qlist.deleteRow(i);
		document.getElementById('attach').style.display = document.getElementById('attach').getElementsByTagName('tbody')[0].rows.length?'':'none';
	},
	//批量上传完毕
	finish:function(b){
		if(!b)//没有可以上传的图片
		{
			var tbody = document.getElementById('attach').getElementsByTagName('tbody')[0];
			var qlist = document.getElementById('qlist');
			var str =(tbody.getElementsByTagName('tr').length<uploader.maxLength||(!qlist.rows.length))?'请选择文件上传！':'附件已经超过数量限制！';
			showDialog('warning',str,2);
			return;
		}
		document.getElementById('uploadFileInfo').style.display = document.getElementById('qlist').rows.length?'':'none';
		ajax.send('pw_ajax.php?action=mutiatt','',function() {
			var rText = ajax.request.responseText.split('\t');
			if (rText[0] == 'ok') {
				eval(rText[1]);
				var tbody = document.getElementById('attach').getElementsByTagName('tbody')[0];
				for (var i=0;i<tbody.rows.length;i++)
				{
					var row = tbody.rows[i];
					for(var j in att)
					{
						var fname = row.cells[0];
						if(fname.innerHTML == att[j][0])//补充
						{
							fname.innerHTML = att[j][0] + '&nbsp;(' + att[j][1] + 'K)<span id="atturl_'+j+'" style="display:none">'+att[j][2]+'</span>';
							fname.title = att[j][0] + '\n上传日期：' + att[j][3];
							fname.id='uploadfile_'+j;
							try{
								row.getElementsByTagName('select')[0].name = 'flashatt[' + j + '][special]';
								row.getElementsByTagName('select')[1].name = 'flashatt[' + j + '][ctype]';
								row.getElementsByTagName('input')[1].name = 'flashatt[' + j + '][needrvrc]';
							}catch(e){}
							row.getElementsByTagName('input')[0].name = 'flashatt[' + j + '][desc]';
							if(uploader.insertable)
								row.cells[2].innerHTML='<span><a href="javascript:;" onclick="addattach('+j+');" class="bta">插入</a><a href="javascript:;" onclick="uploader.del(this,'+j+');" class="bta">删除</a></span>';
							else
								row.cells[2].innerHTML='<span><a href="javascript:;" onclick="uploader.del(this,'+j+');" class="bta">删除</a></span>';
							if (att[j][2].match(/\.(jpg|gif|png|bmp|jpeg)$/ig))
							{
								fname.onmouseover = function() {uploader.view(this);};
							}
							delete att[j];
						}
						
					}
				}
			}
		});
	},
	view:function(o){
		var span = o.getElementsByTagName('span')[0]
		var path = span.innerHTML;
		var id = span.id.substr(span.id.lastIndexOf('_')+1);
		var img = new Image();
		img.src = path+"?ra="+Math.random();
		img.onload = function(){
			getObj('viewimg').innerHTML =  '<div style="padding:6px;"><img src="' + img.src + '" /></div>';
			read.open('viewimg', 'uploadfile_'+id, 3);
		};
	},
	clear : function() {
		ajax.send('pw_ajax.php?action=delmutiatt','',function() {
			if (ajax.request.responseText == 'ok') {
				var tbody = document.getElementById('attach').getElementsByTagName('tbody')[0];
				while(tbody.rows.length){
					tbody.deleteRow(0);
				}
				document.getElementById('uploadFileInfo').style.display = document.getElementById('qlist').rows.length?'':'none';
			} else {
				showDlg("error","删除失败")
			}
		});
	},
	//批量上传限制最大数目
	countFile:function()
	{
		var restLength = this.getRestCount();
		var qlist = document.getElementById('qlist');
		var item,i=0;
		while(qlist.rows[i])
		{
			if(!qlist.rows[i].error)
			{
				if(restLength)
				{
					qlist.rows[i].cells[2].innerHTML ='0%';
					restLength--;
				}else{
					qlist.rows[i].cells[2].innerHTML ='附件个数超过限制';
				}
			}
			i++;
		}
	},
	getRestCount:function(){
		return uploader.maxLength-document.getElementById('attach').getElementsByTagName('tbody')[0].rows.length;
	},
	error:function(str){
		alert(str);
	},
	insert2editor:function(e){
		var upname = e.parentNode.parentNode.parentNode.getElementsByTagName('input')[0].name;
		var attid = upname.substr(upname.indexOf('_')+1);
		editor.focusEditor();
		AddCode(' [upload=' + attid + '] ','');
	}
};