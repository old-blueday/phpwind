var portal = {
	channel : "channel",
	invoke : "invokename",
	subinvoke : "subinvoke",
	column : "column",
	invokebox : "invokebox",
	portalbox : "portalbox",
	doing : '',
	/* 对象选择器 */
	$ : function(id){
		return document.getElementById(id);
	},
	/* 获取联动下拉和表单数据接口  */
	getGrade : function(data,action){
		var action = action ? action : "pushto";
		var url = "mode.php?m=area&q=manage&ajax=1&action="+action;
		if (typeof   doing   !=   'undefined') {
			this.doing = doing;
		}
		
		if (this.doing!='') {
			url = url+"&doing="+this.doing;
		}
		var data = data ? data : '';
		var _this = this;
		ajax.send(url,data,function() {
			if("fetch" == action){
				//var portalbox = _this.$(_this.portalbox);
				//portalbox.innerHTML = ajax.runscript(ajax.request.responseText);
				_this.setPortalbox(ajax.request.responseText);
				_this.initFormPushkey();
			}else{
				var haystack = ajax.request.responseText.split("\t");
				_this.pushto(haystack);
			}	
		});
	},
	setPortalbox : function(text){
		var text = text ? text : '';
		var portalbox = this.$(this.portalbox);
		portalbox.innerHTML = text;
		if (getObj('imagetype_ul')) {
			var imageTypeImp = New(imageType,['imagetype_ul']);
		}
	},
	/* 组装推送表单数据 */
	pushto : function(haystack){
		if(haystack && haystack[0] == 4){
			showDialog("error",haystack[1]);
		}else if(haystack && haystack[0] == 1){
			this.$(this.invoke).outerHTML  = haystack[1];
			this.$(this.subinvoke).outerHTML  = (haystack[2]) ? haystack[2] : this._select();
			//this.$(this.column).outerHTML  = haystack[3];
			this.initNormal();
		}else if(haystack && haystack[0] == 2){
			this.$(this.subinvoke).outerHTML  = (haystack[2]) ? haystack[2] : this._select();
			this.initNormal();
		}
	},
	_select : function(){
		return '<select id="subinvoke"><option>选择位置</option></select>';
	},
	/* 初始化 */
	init : function(){
		this.initNormal();
		this.initForm();
		this.initFormPushkey();
	},
	/* 基本初始化 */
	initNormal : function(){
		this.initChannel();
		this.initInvoke();
		this.initSubInvoke();
	},
	/* 初始化表单数据 */
	initForm : function(){
		if(initsubinvoke){
			var data = "&subinvoke="+initsubinvoke;
			this.getGrade(data,"fetch");
		}
	},
	/* 绑定频道onchange事件 */
	initChannel : function(){
		var channel = this.$(this.channel);
		if(channel){
			var _this = this;
			channel.onchange = function(){
				var data = "&channelid="+this.value;
				_this.getGrade(data);
				_this.setPortalbox();
			};
		}
	},
	/* 绑定模块onchange事件 */
	initInvoke : function(){
		var invoke = this.$(this.invoke);
		if(invoke){
			var _this = this;
			invoke.onchange = function(){
				var channelId = _this.$(_this.channel).value;
				var data = "&channelid="+channelId+"&invokename="+this.value;
				_this.getGrade(data);
				_this.setPortalbox();
			};
		}
	},
	/* 绑定子模块onchange事件 */
	initSubInvoke : function(){
		var subinvoke = this.$(this.subinvoke);
		if(subinvoke){
			var _this = this;
			subinvoke.onchange = function(){
				var channelId = _this.$(_this.channel).value;
				var invoke = _this.$(_this.invoke).value;
				var data = "&channelid="+channelId+"&invoke="+invoke+"&ifpush="+ifpush+"&selid="+selid+"&subinvoke="+this.value;
				if (pushdataid) {
					data += "&pushdataid="+pushdataid;
				}
				_this.getGrade(data,"fetch");
			};
		}
	},
	/* 绑定初始化表单提交事件 */
	initFormSubInvoke : function(){
		var subinvoke = this.$("invokepieceid");
		if(subinvoke){
			var form = this.$("subinvokeform");
			subinvoke.onchange = function(){
				setTimeout(function(){
					 form.submit();
				 },0);
			};
		}
	},
	
	initFormPushkey : function(){
		var pushkeybutton = this.$("pushkeybutton");
		var pushkey = this.$("pushkey");
		if(pushkey){
			var _this = this;
			try{
				pushkeybutton.onclick = function(){
					if("" == pushkey.value){
						return ;
					}
					var channelId = _this.$(_this.channel).value;
					var invoke = _this.$(_this.invoke).value;
					var subinvoke = _this.$(_this.subinvoke).value;
					var data = "&channelid="+channelId+"&invoke="+invoke+"&ifpush=4&subinvoke="+subinvoke+"&selid="+pushkey.value;
					_this.getGrade(data,"fetch");
				};
			}catch(e){}
		}
	}
	
}
/*门户管理开放入口*/
var initPortal = function(){
	portal.init();
};
var initFormSubInvoke = function(){
	portal.initFormSubInvoke();
};
