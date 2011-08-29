Breeze.namespace('global.uploader', function(B){
	window.uploader = {
		startId:1,
		attachStatus: false,
		picStatus: false,
		data: {},
		maxLength: attachConfig.attachnum,
		amount: 0,
		rand: Math.random(),
		flash:null,
		/**
		 * 联动select
		 */
		scurType: function(el){
			var id = el.value;
			if(!id){
				return false;
			}
			var key = id == 2 ? 'sell' : 'enhide';
			if (attachConfig[key]['ifopen'] != '') {
				showDialog('error','您没有权限' + el.options[el.selectedIndex].text + '附件');
				el.selectedIndex = 0;
				return false;
			}
			var ctype = attachConfig[key]['credit'];
			var sel = B.next(el, 'select');
			sel.options.length = 0;
			for (var i in ctype) {
				sel.options.add(new Option(ctype[i], i));
			}
		},
		/**
		 * 列出待上传的文件
		 */
		list: function(queue) {
			//列出列表
			var str = '';
			if(this.picStatus){
				var  plist = B.$('#B_image_tile');
			}
			if (this.attachStatus){
				var qlist = B.$('#B_qlist');
			}
			queue.forEach(function(item){
				if ( B.$('#B_uploaderfile_'+item.fileid) || B.$('#B_picuploaderfile_'+item.fileid) ){
					return false;
				}
				//检测错误
				var ext = item.name.substr(item.name.lastIndexOf('.')+1),
					isPic = (['jpg', 'jpeg', 'gif', 'png', 'bmp'].indexOf(ext.toLowerCase()) >= 0),
					status;
				if (item.error == '') {
					status = uploader.getSize(item.size);
				} else {
					switch (item.error) {
						case 'exterror':
							status = '类型不匹配';
							break;
						case 'toobig':
							status = '大小超过限制';
							break;
					}
				}
				if (uploader.attachStatus){
					str = '<dl id="B_uploaderfile_'+item.fileid+'" class="B_tmp'+(item.error?' B_error':'')+'" title="'+item.name+'"><dt title="附件信息"><div><b class="B_icoFile B_icoFile_'+ext+'">&nbsp;</b>'+item.name+'</div></dt><dd class="B_file_desc">'+status+'</dd><dd class="B_file_dd"><a href="javascript:;" onclick="return uploader.mutidel(this);" title="删除">删除</a></dd></dl>';
					qlist.appendChild(B.createElement(str));
				}
				if (uploader.picStatus && isPic){
					if (!item.error) status = '<div style="width:0%"></div>';
					str = '<li id="B_picuploaderfile_'+item.fileid+'" title="'+item.name+'" class="B_tmp'+(item.error?' B_error':'')+'"><div class="B_file_con"><div class="B_p10">'+item.name+'</div></div><div class="B_file_opera"><a class="B_del" href="javascript:;" onclick="return uploader.mutidel(this);" title="删除">删除</a></div><div class="B_file_pg">'+status+'</div></li>';
					plist.appendChild(B.createElement(str));
				}
				return true;
			});
			uploader.countFile();
		},
		/**
		 * 删除flash中的文件
		 */
		mutidel:function(ele){
			var line = ele.parentNode.parentNode,
				id = line.id,
				index = id.substr(id.lastIndexOf('_')+1);
				if(uploader.flash){
					uploader.flash.remove(index);
				}
			B.remove(line);
			return false;
		},
		/**
		 * 计算大小
		 */
		getSize:function(m){
			var pStr = 'BKMGTPEZY',
				i = Math.floor(Math.log(m)/Math.LN2 / 10 ),
				n = m/Math.pow(1024, i),
				t = 3-Math.ceil(Math.log(n)/Math.LN10);
			return Math.round(n*Math.pow(10,t))/Math.pow(10,t)+pStr.charAt(i);  
		},
		/**
		 * 进度控制
		 */
		progress:function(fileid,percent){
			var pervalue;
			if (uploader.current=='uploader'){
				pervalue = B.$('#B_uploaderfile_'+fileid+' .B_per_value');
				if (!pervalue){
					B.$('#B_uploaderfile_'+fileid+' .B_file_desc').innerHTML = '<div class="B_percent"><div class="B_per_value" style="width:0%;"></div></div>';
					pervalue = B.$('#B_uploaderfile_'+fileid+' .B_per_value');
				}
			}else{
				pervalue = B.$('#B_picuploaderfile_'+fileid+' .B_file_pg div');
			}
			pervalue.style.width = percent + '%';
		},
		//单个文件上传成功
		complete: function(fileid, str, name, size){
			var newline, data;
			try {
				eval('data='+str);
			} catch(e) {
				B.require('global.msglang',function(B){
					var errmsg = typeof msglang[str] == 'undefined' ? '上传失败!' : msglang[str];
					if (uploader.attachStatus) {
						B.$('#B_uploaderfile_'+fileid+' .B_file_desc').innerHTML = errmsg;
						B.$('#B_uploaderfile_'+fileid+' .B_file_desc').title = errmsg;
					}
					if (uploader.picStatus)	{
						B.$('.B_file_pg', line).innerHTML = errmsg;
					}
				});
				return;
			}
			if (uploader.attachStatus){
				B.$('#B_uploaderfile_'+fileid+' .B_file_desc').innerHTML = '<input type="text" name="flashatt['+data.aid+'][desc]" />';
				B.$('#B_uploaderfile_'+fileid+' .B_file_dd').innerHTML = '<a class="B_mr10">插入</a><a>删除</a>';
				newline = B.createElement('<dd class="B_file_set"><select class="B_mr5" name="flashatt['+data.aid+'][special]" onchange="uploader.scurType(this)"><option>无</option><option value="2">出售</option><option value="1">加密</option></select>积分：<select class="B_mr10" name="flashatt['+data.aid+'][ctype]"><option>铜币</option></select>价格：<input type="text" name="flashatt['+data.aid+'][needrvrc]"></dd>');
				B.$('#B_uploaderfile_'+fileid).appendChild(newline);
				
				//修改链接
				var line = B.$('#B_uploaderfile_'+fileid),
					cfgs = B.$$('a', line);
				line.id  = 'B_upload_'+data.aid;
				B.addEvent(cfgs[0], 'mousedown', function(e){
					editor.pasteHTML(B.editor.ubb2attach('[attachment='+data.aid+']'));
					e.preventDefault();
				});
				cfgs[1].onclick='';
				B.addEvent(cfgs[1], 'click', uploader.del);
				B.addEvent(B.$('.B_file_desc input', line), 'mousedown', function(){
					editor.saveRng();
				});
				B.removeClass(line, 'B_tmp');
			}
			if (uploader.picStatus) {
				var line = B.$('#B_picuploaderfile_'+fileid);
				newline = B.createElement('<div class="B_file_ip"><input type="text" name="flashatt['+data.aid+'][desc]"/></div>');
				line.appendChild(newline);
				B.$('.B_file_con', line).innerHTML = '<img src="'+data.path+'" />';
				line.id = 'B_picupload_'+data.aid;
				B.removeClass(line, 'B_tmp');
				B.$('.B_del', line).onclick='';
				B.addEvent(B.$('.B_del', line), 'click', uploader.del);
				B.addEvent(B.$('img', line), 'mousedown', function(e){
					editor.pasteHTML(B.editor.ubb2attach('[attachment='+data.aid+']'));
					e.preventDefault();
				});
				B.addEvent(B.$('input', line), 'mousedown', function(){
					editor.saveRng();
				});
			}
			uploader.data[data.aid] = [name, size, data.path];
			uploader.amount++;
			uploader.showRestCount();
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
			B.get('pw_ajax.php?action=delmutiatt','',function(xmlNode) {
				var str = B.UA.ie ? xmlNode.text : xmlNode.documentElement.textContent;
				if (str == 'ok') {
					B.query('#editor-insertTattach .B_tips').remove();		
				} else {
					ajax_guide();
				}
			});
			window.event && (event.returnValue = false);
		},
		//切换显示售密
		toggleSelect: function(e){
			if(e.target.innerHTML.indexOf( '显示')>=0){
				B.query('#B_qlist dl').addClass('B_file_dl');
				e.target.innerHTML='隐藏“售密设置”';
			}else{
				B.query('#B_qlist dl').removeClass('B_file_dl');
				e.target.innerHTML='显示“售密设置”';
			}
			e.preventDefault();
		},
		//批量上传限制最大数目
		countFile: function(){
			var row = B.$$('#B_qlist dl.B_tmp'),
				restCount = uploader.getRestCount();
				u = restCount, t = restCount;
			row.forEach(function(n){
				if (!B.hasClass(n, 'B_error') && (--u<0) ){
					B.$('dd', n).innerHTML = '数量超出限制';
					return;
				}
			});
			var tiles = B.$$('#B_image_tile li.B_tmp');
			tiles.forEach(function(n){
				var pg = B.$('.B_file_pg', n),
					warningStr = '数量超出限制'
				pg.innerHTML = (--t<0) ?  warningStr : (pg.innerHTML==warningStr) ? '<div width="0%"></div>': pg.innerHTML;
				return;
			});
		},
		//单个删除
		del: function(e){
			var line =  e.target.parentNode.parentNode,
				id = line.id.substr(line.id.lastIndexOf('_')+1),
				param = {'aid' : id, 'type' : attachConfig.type||''};
			B.post('pw_ajax.php?action=delmutiattone', param,function(xmlNode) {
				var str = B.UA.ie? xmlNode.text: xmlNode.documentElement.textContent;
				if (str == 'ok') {
					var line1 = B.$('#B_upload_'+id),
						line2 = B.$('#B_picupload_'+id)
					line1 && B.remove(line1);
					line2 && B.remove(line2);
					delete uploader.data[id];
					uploader.amount--;
					uploader.showRestCount();
				} else {
					ajax_guide();
				}
			});
			e.preventDefault();
		},
		deloldattach: function(e) {
			var line =  e.target.parentNode.parentNode,
				id = line.id.substr(line.id.lastIndexOf('_')+1),
				param = {'aid' : id, 'type' : attachConfig.type||'', 'verify' : verifyhash||''};
			B.post('pw_ajax.php?action=deldownfile', param, function(xmlNode) {
				var str = B.UA.ie? xmlNode.text: xmlNode.documentElement.textContent;
				if (str == 'success') {
					B.remove(B.$('#B_upload_'+id));
					B.remove(B.$('#B_picupload_'+id));
					delete attachConfig.list[id];
				} else {
					B.util.alert('您没有权限删除该附件!', e.target, ['rightAlign', 'bottom']);
				}
			});
			e.preventDefault();
		},
		getRestCount: function(){
			return uploader.maxLength - uploader.amount;
		},
		showRestCount: function(){
			B.$$('.restCount').forEach(function(n){
				n.innerHTML = uploader.getRestCount();
			});
		},
		error: function(str){
			alert(str);
		},
		insert2editor: function(e){
			var upname = e.parentNode.parentNode.parentNode.getElementsByTagName('input')[0].name;
			var attid = upname.substr(upname.indexOf('_')+1);
			editor.focusEditor();
			AddCode(' [attachment=' + attid + '] ','');
		},
		insert: function(elem){
			var tr = B.$('#B_attach tfoot tr').cloneNode(true),
				td = B.$('.review_bg', tr),
				newEl = elem.cloneNode(true);
			
			td.innerHTML = '';
			B.insertBefore(newEl, elem);
			td.appendChild(elem);
			B.$('#B_attach tbody').appendChild(tr);
			elem.name = 'attachment_'+(index++);
		},
		modify: function(el){
			var path = el.value,
				ext = path.substr(path.lastIndexOf('.') + 1, path.length).toLowerCase();
			if (typeof attachConfig.filetype[ext] == 'undefined') {
				alert(ext + '文件格式不符合条件');
				return false;
			}
			var f = B.createElement('<form method="post" action="pweditor.php?action=modifyattach' + (typeof attachConfig.type == 'undefined' ? '' : '&type=' + attachConfig.type) + '" enctype="multipart/form-data"></form>'),
				newel = el.cloneNode(true),
				nextel = el.nextSibling;
			document.body.insertBefore(f, document.body.childNodes[0]);
			f.appendChild(el);
			nextel.parentNode.insertBefore(newel, nextel);
			ajax.submit(f, function() {
				var rText = ajax.request.responseText.split('\t');
				if (rText[0] == 'ok') {
					var fname = rText[1],
						ext = fname.substr(fname.lastIndexOf('.')+1),
						tNode = B.$('#B_upload_name_' + el.name.substr(5));
					tNode.innerHTML = rText[1];
					B.prev(tNode).className = 'B_icoFile B_icoFile_'+ext;
				} else {
					showDialog('error',rText[1]);
				}
				document.body.removeChild(f);
			});
		},
		setAllowFileType : function(elem, filetype){
			var arr = new Array();
			for (var i in filetype) {
				arr.push(i + ':' + filetype[i] + 'KB');
			}
			elem.innerHTML = arr.join('; ');
		},
		reset : function() {
			uploader.data = {};
			uploader.maxLength = attachConfig.attachnum;
			uploader.amount = 0;
			if (uploader.picStatus) {
				B.css(B.$('#editor-insertImage'), 'display', 'none');
				var  plist = B.$('#B_image_tile');
				while (plist.firstChild != null) {
					plist.removeChild(plist.firstChild);
				}
			}
			if (uploader.attachStatus){
				B.css(B.$('#editor-insertTattach'), 'display', 'none');
				var qlist = B.$('#B_qlist');
				while (qlist.firstChild != null) {
					qlist.removeChild(qlist.firstChild);
				}
			}
		},
		showFlash: function(id) {
			var flashVar = {
					url: getBaseUrl()+'job.php' + escape('?action=mutiupload' + (typeof attachConfig.type == 'undefined' ? '' : '&type=' + attachConfig.type) + '&random='+Math.floor(Math.random()*100)),
					jsobject: 'uploader'
				},
				params = {
					menu: "false",
					scale: "noScale",
					allowScriptAccess: "always",
					value:'always',
					wmode:'transparent'
				},
				attr = {id:'muti'+id, name:'muti'+id};

			swfobject.embedSWF(imgpath + '/uploader.swf?'+uploader.rand, 'B_'+id+'_flash', "128", "22", "10.0.0", "js/expressInstall.swf", flashVar, params, attr, function(e){
				uploader.flash = e.ref;
			});
		},
		setPostData: function() {
			if (uploader.postData) {
				uploader.flash.setPostData(uploader.postData);
			} else {
				B.require('request', function(B){
					B.get(getBaseUrl()+'job.php?action=mutiupload&random='+Math.floor(Math.random()*100),function(data){
						if (typeof attachConfig.postData != 'object') attachConfig.postData = {};
						uploader.postData=B.merge({}, attachConfig.postData, B.parseJSON(data));
						uploader.flash.setPostData(uploader.postData);
					});
				});
			}
		},
		initflash:function(){
			if (uploader.flash) {
				uploader.flash.setFileType(uploader.current=='uploader'? attachConfig.filetype :  imageConfig.filetype);
				uploader.setPostData();
			} else {
				setTimeout(uploader.initflash,100);
			}
		},
		init: function(id){
			uploader.current = id;
			if (typeof swfobject != 'undefined') {
				if (uploader.flash) {
					var pa = uploader.flash.parentNode,
						mid = pa.id.split('_')[1];
					uploader.flash.parentNode.replaceChild(B.createElement('<span id="B_'+mid+'_flash"></span>'), uploader.flash);
					uploader.flash=null;
				}
				uploader.showFlash(id);
			} else {
				loadjs('js/swfobject.js','','',function() {uploader.showFlash(id);});
			}
			B.query('.B_tmp').remove();
			if (id == 'uploader' && !uploader.attachStatus) {
				if (typeof attachConfig.ifhide == 'undefined' || attachConfig.ifhide == 'disabled') {
					B.query('.B_file_tips label').css('display', 'none');
				} else if (attachConfig.ifhide == 'checked') {
					B.$('.B_file_tips input').checked = true;
				}
				if (typeof attachConfig.sell == 'undefined') {
					B.query('#B_sm_cfg').css('display', 'none');
				}
				//已经存在的
				for(var i in attachConfig.list){
					var itm = attachConfig.list[i],
						ext = itm[0].substr(itm[0].lastIndexOf('.')+1),
						str = '<dl id="B_upload_'+i+'">\
							<dt title="'+itm[0]+'"><div><b class="B_icoFile B_icoFile_'+ext+'">&nbsp;</b><span id="B_upload_name_'+i+'">'+itm[0]+'</span></div>\
							</dt><dd class="B_file_desc"><input type="text" name="oldatt_desc['+i+']" value="'+itm[7]+'">\
							</dd><dd class="B_file_dd"><a class="B_mr5">插入</a>\
							<span class="B_file_modify"><input name="file_'+i+'" type="file" onchange="uploader.modify(this);"><a>修改</a></span><a>删除</a></dd><dd class="B_file_set">\
							<select class="B_mr5" name="oldatt_special['+i+']" onchange="uploader.scurType(this)"><option>无</option><option value="2">出售</option><option value="1">加密</option></select>积分：<select class="B_mr10" name="oldatt_ctype['+i+']"><option>铜币</option></select>\
							价格：<input type="text" name="oldatt_needrvrc['+i+']" value="'+itm[5]+'"></dd></dl>',
						node = B.createElement(str);

					B.$('#B_qlist').appendChild(node);
					if (itm[5] && itm[4] && itm[5] != '0' && itm[4] != '0') {
						var sel = B.$$('select', node);
						sel[0].value = itm[4];this.scurType(sel[0]);sel[1].value = itm[6];
					}
					var cfgs = B.$$('a', node);
					B.addEvent(cfgs[2], 'click', uploader.deloldattach);
					B.addEvent(cfgs[0], 'click', function(e){
						editor.pasteHTML(B.editor.ubb2attach('[attachment='+this.parentNode.parentNode.id.substr(9)+']'));
						e.preventDefault();
					});
					B.addEvent(B.$('.B_file_desc input', node), 'mousedown', function(){
						editor.saveRng();
					});
					//B.addEvent(B.$('.modify', node), 'click', uploader.modify);
				}
				for(var i in uploader.data){
					var itm = uploader.data[i],
						ext = itm[0].substr(itm[0].lastIndexOf('.')+1),
						str = '<dl id="B_upload_'+i+'">\
							<dt title="'+itm[0]+'"><div><b class="B_icoFile B_icoFile_'+ext+'">&nbsp;</b>'+itm[0]+'</div>\
							</dt><dd class="B_file_desc"><input type="text" name="flashatt['+i+'][desc]">\
							</dd><dd class="B_file_dd"><a class="B_mr10">插入</a>\
							<a>删除</a></dd><dd class="B_file_set">\
							<select class="B_mr5" name="flashatt['+i+'][special]" onchange="uploader.scurType(this)"><option>无</option><option value="2">出售</option><option value="1">加密</option></select>积分：<select class="B_mr10" name="flashatt[494][ctype]"><option>铜币</option></select>\
							价格：<input type="text" name="flashatt['+i+'][needrvrc]"></dd></dl>',
						node = B.createElement(str);
					B.$('#B_qlist').appendChild(node);
					var cfgs = B.$$('a', node);
					B.addEvent(cfgs[0], 'click', function(e){
						editor.pasteHTML(B.editor.ubb2attach('[attachment='+this.parentNode.parentNode.id.substr(9)+']'));
						e.preventDefault();
					});
					B.addEvent(cfgs[1], 'click', uploader.del);
					B.addEvent(B.$('.B_file_desc input', node), 'mousedown', function(){
						editor.saveRng();
					});
				}
				uploader.attachStatus = true;
				uploader.setAllowFileType(B.$('#attach_allow_filetype'), attachConfig.filetype);
			}
			if (id == 'picuploader' && !uploader.picStatus) {
				for(var i in attachConfig.list){
					var itm = attachConfig.list[i],
						ext = itm[0].substr(itm[0].lastIndexOf('.')+1);
					if (['jpg', 'gif', 'png', 'jpeg', 'bmp'].indexOf(ext.toLowerCase()) < 0){
						continue;
					}
					var str = '<li title="'+itm[0]+'" id="B_picupload_' + i + '">\
							<div class="B_file_con">\
								<img src="' + itm[2] + '">\
							</div>\
							<div class="B_file_opera">\
								<a href="javascript:;" class="B_del" title="删除">删除</a>\
							</div>\
							<div class="B_file_pg">\
								<div style="width: 100%;"></div>\
							</div>\
							<div class="B_file_ip">\
								<input type="text" name="oldatt_desc[' + i + ']" value="' + itm[7] + '">\
							</div>\
						</li>',
						node = B.createElement(str);
					B.$('#B_image_tile').appendChild(node);
					B.addEvent(B.$('img', node), 'click', function(){
						editor.pasteHTML(B.editor.ubb2attach('[attachment='+this.parentNode.parentNode.id.substr(12)+']'));
					});
					B.addEvent(B.$('a', node), 'click', uploader.deloldattach);
					B.addEvent(B.$('input', node), 'mousedown', function(){
						editor.saveRng();
					});
				}
				for(var i in uploader.data){
					var itm = uploader.data[i],
						ext = itm[2].substr(itm[2].lastIndexOf('.')+1);
					if (['jpg', 'gif', 'png', 'jpeg', 'bmp'].indexOf(ext.toLowerCase()) < 0){
						continue;
					}
					var str = '<li title="'+itm[0]+'" id="B_picupload_' + i + '">\
							<div class="B_file_con">\
								<img src="' + itm[2] + '">\
							</div>\
							<div class="B_file_opera">\
								<a onclick="return uploader.mutidel(this);" href="javascript:;" title="删除" class="B_del">删除</a>\
							</div>\
							<div class="B_file_pg">\
								<div style="width: 100%;"></div>\
							</div>\
							<div class="B_file_ip">\
								<input type="text" name="flashatt[' + i + '][desc]">\
							</div>\
						</li>',
						node = B.createElement(str);
					B.$('#B_image_tile').appendChild(node);
					B.addEvent(B.$('.B_del', node), 'click', uploader.del);
					B.addEvent(B.$('img', node), 'click', function(){
						editor.pasteHTML('<img src="'+this.src+'"/>');
					});
					B.addEvent(B.$('input', node), 'mousedown', function(){
						editor.saveRng();
					});
				}
				uploader.picStatus = true;
				uploader.setAllowFileType(B.$('#image_allow_filetype'), imageConfig.filetype);
			}
			uploader.showRestCount();
		}
	};
});