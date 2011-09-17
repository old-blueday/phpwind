/**
 * Message Center javascript package
 */
var MC = {
	/* global selector */
	showMessageId : "showmessage",
	baseUrl : 'message.php',
	$ : function(id){
		return document.getElementById(id);
	},
	
	/* ajax post submit */
	post : function(url,data,callback,sCallback,fCallback,isTips){
		var _this = this;
		ajax.send(url,data,function(){
			callback ? callback() : 0;
			isTips ?  0 : _this._showMessage(ajax.request.responseText,sCallback,fCallback);
		});
	},
	
	/* ajax send submit */
	send : function(url,data,callback){
		ajax.send(url,data,callback);
	},
	
	/* private show message include success and fail */
	_showMessage : function(txt,sCallback,fCallback){
		if(!txt || txt.indexOf('{') == '-1' ) return false;
		var r = JSONParse(txt);
		if(r.bool){
			this.showSuccessTips(r.message);
			sCallback ? sCallback() : 0;
		}else{
			this.showFailTips(r.message);
			fCallback ? fCallback() : 0;
		}
	},
	showSuccessTips : function(message,otherid){
		if(!message) return false;
		var mdiv = otherid ?  this.$(otherid) : this.$(this.showMessageId);
		try{
			mdiv.innerHTML = message;
			mdiv.className = "rightTip mb10";
			mdiv.style.display = '';
		} catch(e){}
		return this;
	},
	showFailTips : function(message,otherid){
		if(!message) return false;
		var mdiv = otherid ?  this.$(otherid) : this.$(this.showMessageId);
		try{
			mdiv.innerHTML = message;
			mdiv.className = "wrongTip mb10";
			mdiv.style.display = '';
		} catch(e){}
		return this;
	},
	fadeTips : function(otherid){
		clearTimeout(MC.timer);
		var mdiv = otherid ?  this.$(otherid) : this.$(this.showMessageId);
		if (mdiv) {
			this.timer = setTimeout(function(){mdiv.style.display = 'none';},3000);
		}
	},
	/* ajax get */
	get : function(url,data,id){
		sendmsg(url,data,id);
		
	},
	/* ajax submit */
	submit : function(obj,recall){
		ajax.submit(obj,recall);
	},
	/* show dialog */
	tips : function(type,message,autohide,callback){
		showDialog(type,message,autohide,callback);
	},
	/* get object position*/
	position : function(){
		
	},
	/* check All */
	CheckAll : function(form,obj){
			for(var i=0;i<document.getElementsByName("checkall").length;i++){			
				document.getElementsByName("checkall")[i].checked=obj.checked;
			}
		var checkBox = form.rids;
		if (typeof(checkBox) == 'undefined') return;
		if(checkBox.value){
			obj.checked ? checkBox.checked = true : checkBox.checked = false;
		}else{
			for (var i = 0; i < checkBox.length; i++) {	
				obj.checked ? checkBox[i].checked = true : checkBox[i].checked = false;
			}
		}
	
	},
	/* current tip */
	setCurrent : function(src,dst,css){
		setCurrent(src,dst,css);
	},
	/* send post reply */
	sendPostReply : function(url,form,otherid){
		var e=getEvent();
		if(e.preventDefault){
			e.preventDefault();
		}else{
			e.returnValue=false;
		}
		var replyContent = editor.getHTML();
		var mid = MC.$("parentMid").value;
		if("" == mid){
			MC.showFailTips("非法操作",otherid);
			MC.fadeTips(otherid);
			return false;
		}
		if("" == replyContent || "<br />" == replyContent){
			MC.showFailTips("回复内容不能为空",otherid);
			MC.fadeTips(otherid);
			return false;
		}
		//form.atc_content.value = htmltocode(replyContent);
		MC.post(url,form,'',function(){
			if (typeof editor == 'object') {
				editor.reset();
			} else {
				MC.$("textarea").value = "";
			}
			window.setTimeout(function(){location.reload();},100);
		}, function(){
			try{
				var gdcodes = getObj('changeGdCode');
				if (typeof gdcodes.onclick == 'function') gdcodes.onclick();
			}catch(e){}		
		});
		return false;
	},
	
	/* 单条删除 */
	del : function(rid,type,subtype,page,otherid){
		var dataUrl = MC.baseUrl+'?ajax=1&type='+type+'&action='+subtype+'&page='+page;
		var url = MC.baseUrl+'?&type=ajax&action=del&rids[]='+rid;
		MC.send(url,'',function(){
			MC.nonRefresh(dataUrl,ajax.request.responseText,otherid);	
		})
	},
	group : function(message,data,type,page,otherid){
		var dataUrl = MC.baseUrl+'?type='+type+'&page='+page+'&ajax=1';
		var ajaxUrl = MC.baseUrl+'?type=ajax';
		if(message != ''){
			MC.tips('confirm', message,0, function(){
				MC.send(ajaxUrl, data, function(){
					MC.nonRefresh(dataUrl, ajax.request.responseText,otherid)
				})
			})
		} else {
			MC.send(ajaxUrl, data, function(){
				MC.nonRefresh(dataUrl, ajax.request.responseText,otherid)
			})
		}
		
	},
	/* 批量操作 */
	mSubmit : function(form,action,type,subtype,page,otherid){
		var checkBoxObj = form.rids;
		var rids = '';
		if(checkBoxObj == 'undefined' || checkBoxObj == null){
			MC.showFailTips("请至少选择一条消息",otherid);
			MC.fadeTips(otherid);
			return false;
		}
		for (var i = 0; i < form.elements.length; i++) {
			var e = form.elements[i];
			if (e.name != "" && e.type == 'checkbox' && e.checked) {
				rids += rids == '' ? e.value : ',' + e.value;
			}
		}
		if("" == rids || rids == "undefined" || rids == null ){
			MC.showFailTips("请至少选择一条消息",otherid);
			MC.fadeTips(otherid);
			return false;
		}
		var sendUrl = MC.baseUrl+'?type=ajax&action='+action+'&rids='+rids;
		var dataUrl = MC.baseUrl+'?ajax=1&type='+type+'&action='+subtype+'&page='+page;
		if (action == 'del') { 
			MC.tips('confirm','确定要删除选中的消息?',0,function(){
				MC.send(sendUrl,'',function(){
					MC.nonRefresh(dataUrl,ajax.request.responseText,otherid);	
				});
			});
		} else {
			MC.send(sendUrl,'',function(){	
				MC.nonRefresh(dataUrl,ajax.request.responseText,otherid);
			});
			
		}
		
	},
	nonRefresh : function(dataUrl,message,otherid){
		if(/{.+}/.test(message)){
			var rText = eval('(' + message +')');
			if(null == rText.bool){
				showDialog({type:'confirm',message:rText.message,okText:'重新登录',onOk:function(){
					location.href = "login.php";
				}});
				return false;
			};
		};
		MC.$('hiddenMessage').innerHTML = message;
		var timer = window.setTimeout(function(){
		clearTimeout(timer);
		MC.showSuccessTips(MC.$('hiddenMessage').innerHTML,otherid);
		MC.fadeTips(otherid)},100);
		MC.send(dataUrl,'',function(){
			MC.$('content').innerHTML = ajax.request.responseText;
				//重新初始化select
				new sSelect(getObj('alltype'));
		});	
	},
	/* 搜索功能 */
	submitSearch : function(form){
		var unames = document.getElementsByName('_usernames[]');
		if(!unames || !unames.length){
			MC.showFailTips("好友不能为空");
			MC.fadeTips();
			return false;
		}
		var formele = form;
		var newuname = [];
		for(var i=unames.length-1;i>=0;i--){
			if(unames[i].value == windid ){
				MC.showFailTips("不能搜索自己消息");
				return false;
			}
			var item = unames[i].cloneNode(true);
			formele.appendChild(item);
			newuname.push(item);
		}
		return true;
	},

	/* 多人对话展开 */
	showDetailMsg : function(mid,/* 1:open 2:close */status){
		var closeObj = MC.$("sms_0_"+mid);
		var openObj = MC.$("sms_1_"+mid);
		var openButton = MC.$("openAll");
		if(status == '1'){
			closeObj.style.display = 'none';
			openObj.style.display = '';
		} else if(status == '2'){
			closeObj.style.display = '';
			openObj.style.display = 'none';
		} else if(status == 'all'){
			var smsTable = MC.$("smsListTable");
			var trs = smsTable.rows;
			for(var i = 0; i < trs.length; i++){
				if(trs[i].id.indexOf("sms_0_") != '-1'){
					trs[i].style.display = 'none';
				}else if(trs[i].id.indexOf("sms_1_") != '-1'){
					trs[i].style.display = '';
				}
			}
			openButton.innerHTML = "全部收起";
			openButton.onclick = function(){MC.showDetailMsg('','close');}
		} else if(status == 'close'){
			var smsTable = MC.$("smsListTable");
			var trs = smsTable.rows;
			for(var i = 0; i < trs.length; i++){
				if(trs[i].id.indexOf("sms_0_") != '-1'){
					trs[i].style.display = '';
				}else if(trs[i].id.indexOf("sms_1_") != '-1'){
					trs[i].style.display = 'none';
				}
			}
			openButton.innerHTML = "全部展开";
			openButton.onclick = function(){MC.showDetailMsg('','all');}
		}
	},
	/*操作状态改变*/
	changeStatus : function(url,rid,className,ifall,otherid){
		var httpUrl = url;
		var tipId = 'tip_';
		var optId = 'opt_';
		var skipId = 'skip_';
		var iconId = 'icon_';
		var emId = 'em_';
		var pid = '';
		var rids = '';
		var checkAll = document.getElementsByName(ifall);
		if(ifall){
			var tips = opts = vkValue = rids = '';
			var form = document.FORM;
			var checkBoxObj = form.rids;			
			if(checkBoxObj == 'undefined' || checkBoxObj == null){
				MC.showFailTips("请至少选择一条请求",otherid);
				MC.fadeTips(otherid);
				return false;
			}
			if(checkBoxObj.value == "undefined" || checkBoxObj.value == null){
				for(var i = 0 ; i < checkBoxObj.length ; i++){
					if (checkBoxObj[i].checked) {
						ckValue = checkBoxObj[i].value;
						if(MC.$(skipId+ckValue).value == 'true'){
							continue;
						}
						rids += ',' + ckValue;
						tips = MC.$(tipId+ckValue);
						if(tips){
							pid += pid == ''? ckValue : ','+ckValue;
						}
					}		
				}
			}else{
				if(checkBoxObj.checked){
					rids = checkBoxObj.value;
					var tips = MC.$(tipId+rids);
					var skip = MC.$(skipId+rids);
					if(tips && skip.value != 'true'){	
						pid = rids;
					}else{
						rids = "";
						checkBoxObj.checked = false;
						for(var i=0;i<checkAll.length;i++){
							checkAll[i].checked = false;
						}
					}
				}
			}
			if("" == rids || rids == "undefined" || rids == null ){
				MC.showFailTips("请至少选择一条未被忽略或者未同意过的请求",otherid);
				MC.fadeTips(otherid);
				for(var i=0;i<checkAll.length;i++){
					checkAll[i].checked = false;
				}
				if(checkBoxObj.value == "undefined" || checkBoxObj.value == null){
					for(var i=0;i<checkBoxObj.length;i++){			
							checkBoxObj[i].checked = false;
					}
				}else{
					checkBoxObj.checked = false;
				}
				return false;
			}
			httpUrl = url+'&rids='+rids;
		}
		function callback(){
			if(!ifall){
				var tip = MC.$(tipId+rid);
				var opt = MC.$(optId+rid);
				var skip = MC.$(skipId+rid);
				var icon = MC.$(iconId+rid);
				tip.className=className;
				tip.innerHTML=ajax.request.responseText;
				opt.style.display='none';
				skip.value = 'true';
				if(icon){
					icon.src = "u/images/message/sendread.png";
					MC.$(emId+rid).innerHTML = '';
				}
				location.reload();
			}else{
				var pids = pid.split(',');
				var returnValue = ajax.request.responseText;
				for(var i=0;i<pids.length;i++){
					var tip = MC.$(tipId+pids[i]);
					var opt = MC.$(optId+pids[i]);
					var skip = MC.$(skipId+pids[i]);
					var icon = MC.$(iconId+pids[i]);
					skip.value = 'true';
					tip.innerHTML = returnValue;
					tip.className = className;
					opt.style.display = 'none';
					if(icon){
						icon.src = "u/images/message/sendread.png";
						MC.$(emId+pids[i]).innerHTML = ''
					}
				}
				for(var i=0;i<checkAll.length;i++){
					checkAll[i].checked = false;
				}
				if(checkBoxObj.value == "undefined" || checkBoxObj.value == null){
					for(var i=0;i<checkBoxObj.length;i++){			
							checkBoxObj[i].checked = false;
					}
				}else{
					var skip = MC.$(skipId+pids[0]);
					if(skip){
						skip.value = 'true';
					}
					checkBoxObj.checked = false;
				}
				location.reload();
			}
			
		}
		if(className == 'ignoreTip'){
			MC.tips('confirm','你确定要忽略这些请求吗?',0,function(){
				MC.post(httpUrl,'',callback,'','',true);})
		}else if(className == 'ignoreOneTip'){
			MC.tips('confirm','你确定要忽略请求吗?',0,function(){
				MC.post(httpUrl,'',callback,'','',true);
				})
		}else{
			MC.post(httpUrl,'',callback,'','',true);
		}
	},
	getTypeList:function (type,action){
		var url = 'message.php?type='+type;
		if(action != ''){
			url += '&action='+action;
		}
		location.href = url;
	}
}

