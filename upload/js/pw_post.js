/**
 * 批量管理帖子
 * @param  name string
 * @param  value string
 * @author suqian
 * @date 2010-6-13
 * @return void
 */
function pw_post(name,value){

	this.panelpre = 'manager_';
	this.panelallid = 'ajaxall';
	this.currentallid = 'pw_sel_all';
	this.controlid = 'pw_box';
	this.counterid = 'select_count';
	this.checkid = 'tidarray[]';
	

	this.panel = null;
	this.panelall = null;
	this.currentall = null;
	this.control = null;
	this.counter = null;
	this.check = null;
	
	this.url = 'mawhole.php?ajax=1&action=batch&'+name+'='+value;	
	this.init = function(){
	}
	this.$ = function(value,type){
		var obj = null;
		switch(type){
			case 'id'   : obj = document.getElementById(value);break;
			case 'name' : obj = document.getElementsByName(value);break;
			case 'tag'  : obj = document.getElementsByTag(value);break;
			default:obj = document.getElementById(value);break;
		}
		return obj;
	}
	this.show = function(obj,e){
		var checkinfo = this.checknum();
		this.control = this.$(this.controlid, 'id');
		this.counter = this.$(this.counterid, 'id');
		this.panel = this.$(this.panelpre+obj.value,'id');
		if (!this.counter) {
		  if(!checkinfo[2]) return false;
		  sendmsg(this.url, checkinfo[2], 1);
		  var _this = this;
		  setTimeout(function(){
			  var tmp = _this.$(_this.counterid, 'id');
			  if(tmp){
				  _this.$(_this.counterid, 'id').innerHTML = checkinfo[0];
				  _this.position(e,obj.parentNode);
			  }
		  },300);
		}else{
			this.counter.innerHTML = checkinfo[0];
			this.position(e,obj.parentNode);
		}
		if((checkinfo[0] == 0 && this.counter)){
			this.control.style.display = 'none';
			this.panel.style.display = 'none';
		}else if(checkinfo[0] >0 && this.control.style.display == 'none'){
			this.panel.style.display = '';
			this.control.style.display = '';
			_this = this;
			this.panel.onclick = function(){
				_this.panel.style.display = 'none';
				if(!_this.counter){
					_this.show(e,obj);
				}else{
					_this.control.style.display = '';
				}
				_this.position(e,obj.parentNode);
			}
		}
	}
	this.action = function (atag,id){
		read.obj = atag;
		var info = this.checknum();
		var data = info[2];
		sendmsg(atag.href,data,id);
		return false;
	}
	
	this.manager = function(obj,e){
		var checkinfo = this.checknum();
		this.control = this.$(this.controlid, 'id');
		this.counter = this.$(this.counterid, 'id');
		this.panel = this.$(this.panelpre+obj.value,'id');
		if(checkinfo[0]>0 && this.control.style.display == 'none'){
			this.panel.style.display = '';
			_this = this;
			this.panel.onclick = function(){
				_this.panel.style.display = 'none';
				if(!_this.counter){
					_this.show(e,obj);
				}else{
					_this.control.style.display = '';
				}
				_this.position(e,obj.parentNode);
			}
			obj.onmouseout = function(){
			}
			this.displaycheck(obj);	
		}else{
			_this = this;
			_this.panel.style.display = 'none';
		}	
	}
	this.position = function (eArg,obj){
		var rect = obj.getBoundingClientRect();
		read.menu.style.top  = rect.top+ietruebody().scrollTop+'px';
		read.menu.style.left = rect.left+300+ietruebody().scrollLeft+'px';
	}
	
	this.displaycheck = function(obj){
		this.check = this.$(this.checkid, 'name');
		for(var i=0;i<this.check.length;i++){
			if(this.check[i].value != obj.value){
				var id = this.check[i].value;
				this.$(this.panelpre+id,'id').style.display = 'none';
			}
		}
	}
	
	this.checknum = function(){
		var a = b = 0;
		var selected = '';
		this.check = this.$(this.checkid, 'name');
		for (var i=0; i<this.check.length; i++) {
			if (this.check[i].checked) {
				selected += '&tidarray[' + a + ']=' + this.check[i].value;
				a++;
			} else {
				b++;
			}
		}
		return new Array(a,b,selected);
	}
	
	this.showError = function(message,time){
		var control = this.$(this.controlid, 'id'),
			popout = getElementsByClassName('popout',control)[0],
			msgBoxs = getElementsByClassName('wrongTip',popout),
			box = msgBoxs.length ? msgBoxs[0] : null;
			popBottom = getElementsByClassName('popBottom',control);
			if(!box) {	
				
				box = document.createElement('div');
				box.className = 'wrongTip';
				box.innerHTML = message;
				popBottom[0].parentNode.insertBefore(box,popBottom[0]);
			}
			box.style.display = '';
			box.innerHTML = message;
			if(time == undefined) time = 3;	
			clearTimeout(this.showTime);
			this.showTime = setTimeout(function(){box.style.display = 'none';},time * 1000);
			return false;
	}
	
	this.checkall = function(obj){
		this.panelall = this.$(this.panelallid, 'id');
		this.currentall = this.$(this.currentallid,'id');
		this.control = this.$(this.controlid, 'id');
		this.counter = this.$(this.counterid, 'id');
		this.check = this.$(this.checkid, 'name');
		for(var i=0;i<this.check.length;i++){
			obj.checked ? this.check[i].checked = true : this.check[i].checked = false;
		}
		if(this.counter){
			if(obj.checked){
				this.counter.innerHTML = this.check.length;
			}else{
				this.counter.innerHTML = 0;
				this.control.style.display = 'none';
				this.displaycheck(obj);
			}
			obj.id == this.currentallid ? this.panelall.checked = false : this.currentall.checked = false;
		}	
	}
	

	this.ajaxsubmit = function (form,dbreason,type){
		var atc_content = form.atc_content;
		function callback(){
			if (ajax.request.responseText == null) {
				ajax.request.responseText = '您请求的页面出错啦!';
			}
			var rText = ajax.request.responseText.split('\t');
			if(type == 'batch'){
				operateOverPrint(rText);
			}else{
				if(operateOverPrint(rText)){
					var overprint = getObj("read_overprint");
					if(overprint){
						return false;
					}else{
						window.location.reload();
						return false;
					}
				}
			}
			if (rText[1] != 'nextto') {
				if(in_array(rText[0],['操作成功!','操作完成!'])){
					showDialog('success',rText[0],2);
				}else{
					showDialog('error',rText[0],2);
				}
			}
			if (typeof(rText[1]) != 'undefined' && in_array(rText[1],['jump','nextto','reload'])) {
				if (rText[1] == 'jump') {
					setTimeout("window.location.href='"+rText[2]+"';",200);
				} else if (rText[1] == 'nextto') {
					sendmsg(rText[2],rText[3],rText[4]);
				} else if (rText[1] == 'reload') {
					setTimeout("window.location.reload();",3000);
				}
			}
		}
		dbreason == '1' ? atc_content.value ? ajax.submit(form,callback) : this.showError('请输入操作原因'): ajax.submit(form,callback);
	}
}