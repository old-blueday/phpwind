/***************************
 * 新版上传文件
 * @author yuyang
 * $Id$
 */
var uploader = {
	startId:0,
	mode:0,
	/**
	 * 初始化
	 */
	init:function(){
		this.flash = swfobject.getObjectById('mutiupload');
	},
	/**
	 * 列出待上传的文件
	 */
	list:function(queue)
	{
		var restLength = this.getRestCount();
		if(restLength<1){
			showDialog('warning','此相册照片数量达到上限，无法上传!',2);
			return false;
		}
		var qlist = document.getElementById('qlist');
		while(j = qlist.rows.length)
		{
			qlist.deleteRow(0);
		}
		for(var i=queue.length-1;i>=0;--i)
		{
			var tr = qlist.insertRow(0);
			var cel1 = tr.insertCell(0);
			cel1.innerHTML = queue[i].name;
			
			var cel2 = tr.insertCell(1);
			cel2.className='wname';
			var desc = queue[i].desc===undefined?queue[i].name:queue[i].desc;
			cel2.innerHTML = '<input type="text" value="'+desc+'" onchange="uploader.storage(this)" />';			
			var cel3 = tr.insertCell(2);
			cel3.innerHTML = uploader.getSize(queue[i].size);
			
			var cel4 = tr.insertCell(3);
			if (queue[i].error == '') {
				cel4.innerHTML = '0%';
			} else {
				switch (queue[i].error) {
					case 'exterror':
						cel4.innerHTML='<span class="s1">类型不匹配</span>';
						tr.error=1;
						break;
					case 'toobig':
						cel4.innerHTML='<span class="s1">大小超过限制</span>';
						tr.error=1;
						break;
				}
			}
			var cel5 = tr.insertCell(4);
			cel5.innerHTML = '<span onclick="uploader.mutidel(this)" class="updel" style="cursor:pointer;">x</span>';
		}
		this.countFile();
	},
	/**
	 * 删除flash中的文件
	 */
	mutidel:function(ele){
		var y = ele.parentNode.parentNode.sectionRowIndex-uploader.startId;
		if(y>=0)
			uploader.flash.remove(ele.parentNode.parentNode.sectionRowIndex-uploader.startId);
	},
	/**
	 * 计算大小
	 */
	getSize:function(n)
	{
		var pStr = 'BKMGTPEZY';
		var i = 0;
		while(n>1024)
		{
			n=n/1024;
			i++;
		}
		var t = 3-Math.ceil(Math.log(n)/Math.LN10);
		return Math.round(n*Math.pow(10,t))/Math.pow(10,t)+pStr.charAt(i);  
	},
	/**
	 * 进度控制
	 */
	progress:function(i,percent)
	{
		document.getElementById('qlist').rows[this.startId+i].getElementsByTagName('td')[3].innerHTML = percent + '%';
	},
	//单个文件上传成功
	complete:function(i)
	{
		this.startId++;
	},
	//批量上传完毕
	finish:function(b){
		if(!b){//没有可以上传的图片
			closep();
			if (this.isAlbumFull == true) {
				showDialog('warning','上传失败，请重新选择相册!',2);
			} else {
				showDialog('warning','上传失败，请重新选择照片!',2);
			}
		}else{
			read.setMenu(uploader.jumpphoto(uploader.albumId));
			read.menupz();//showDialog('success','上传成功！',2);
		}
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
				if(restLength > 0)
				{
					qlist.rows[i].cells[3].innerHTML ='0%';
					restLength--;
				}else{
					qlist.rows[i].cells[3].innerHTML ='<span class="s1">附件个数超过限制</span>';
				}
			}
			i++;
		}
	},
	getRestCount:function(){
		return uploader.maxLength;
	},
	storage:function(e){
		uploader.flash.setDesc(e.parentNode.parentNode.sectionRowIndex-uploader.startId,e.value);
	},
	setLimits:function(i){
		uploader.maxLength = i.toString();
	},
	setAlbumId:function(i){
		uploader.albumId = i;
		uploader.flash.setAlbumId(parseInt(i));
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
		ok.innerHTML = '<span><button type="button">继续</button></span>';	;

		ok.onclick	= function () {
			window.location.href = uploader.baseurl + 'a=upload&job=flash&aid=' + toaid;
		}

		var toview	= elementBind('span','','bt2','');
		toview.innerHTML = '<span><button type="button">浏览</button></span>';
		toview.onclick	= function () {
			window.location.href = uploader.baseurl + 'a=album&aid=' + toaid;
		}

		tar.appendChild(ok);
		tar.appendChild(toview);

		return maindiv;
	},
	error:function(s)
	{
		alert(s);
	}
};