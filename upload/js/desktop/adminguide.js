/**
 * 后台引导功能
 * 2009-11-10 lh
 * @use 
 */
var adminguide = {
	id       : "adminguide",
	bid      : "adminguidebig",
	width    : "612px",
	height   : "312px",
	zIndex   : "99999",
	position : "absolute",
	prefix   : "step",
	wid      : "guide",
	cid      : "close",
	fid      : "guideiframe",
	
	/*ID选择器*/
	$ : function(id){
		return document.getElementById(id);
	},
	
	isIe6 : function(){
		return navigator.userAgent.indexOf("MSIE 7.0")==-1&&navigator.userAgent.indexOf("MSIE 8.0")==-1&&navigator.userAgent.indexOf("MSIE 6.0") > 0;
	},
	
	/*第几步*/
	step : function(step){
		var step = step ? step : 1;
		this.create(step);
	},
	
	/*设置引导框位置*/
	setPosition : function(box){
		var left = document.body.scrollLeft + (document.body.clientWidth-parseInt(this.width))/2;
		var top = document.body.scrollTop + (document.body.clientHeight-parseInt(this.height))/2;
		box.style.top = top+"px";
		box.style.left = left+"px";
		return box;
	},
	
	/*创建显示元素*/
	create : function(step){
		var box = this.$(this.id);
		if(!box){
			var box = this.box();
		}
		//this.resetCheckbox(box);
		var current = this.$(this.prefix+step);
		
		this.bindAClick(current);
		this.bindButtonClink(current,step);
		current.style.display = "";
		box.appendChild(current);
		this.resetCheckbox(step);/*IE*/
		if(!this.$(this.bid)){
			this.shade();
		}
		var ie6 = this.isIe6();
		//ie6 ? this.iframeShade() : 0;
		if(!this.$(this.fid)){
			this.iframeShade();
		}
	},
	
	/*获取当前下次是否显示值*/
	getCheckBox : function(){
		return  this.$("guideshow").value;
	},
	
	/*设置当前下次是否显示值*/
	setCheckBox : function(value){
		this.$("guideshow").value = value;
		ajax.send(ajaxurl,'&action=guide&guideshow='+value);
	},
	
	/*重置当前下次是否显示值*/
	resetCheckbox : function(step){
		var stats = this.getCheckBox();
		var checked = (stats == 1) ? "checked" : "";
		var checkboxs = this.$(this.id).getElementsByTagName("input");
		var current = this.$(this.prefix+step);
		var checkboxs = current.getElementsByTagName("input");
		for(var i=0;i<checkboxs.length;i++){
			if(checkboxs[i].type == "checkbox"){
				checkboxs[i].checked = checked;
			}
		}
	},
	
	/*主容器*/
	box : function(){
		var box = document.createElement("div");
		box.id = this.id;
		box.style.width = this.width;
		box.style.height = this.height;
		box.style.zIndex = this.zIndex;
		box.style.position = this.position;
		this.setPosition(box);
		document.body.appendChild(box);
		return box;
	},
	
	/*阴影*/
	shade : function(){
		var big = this.createDiv(this.bid);
		/*ie6*/
		var ie6 = this.isIe6();
		if(ie6){
			var sbig = this.createDiv(this.bid+"second");
			sbig.style.top = "0px";
			sbig.style.height = "86px";/*60->86*/
			document.body.appendChild(sbig);
			big.style.top = "86px";/*高度 60->86*/
		}else{
			big.style.top = "0px";
		}
		document.body.appendChild(big);
	},
	
	createDiv : function(id){
		var big = document.createElement("div");
		big.id = id;
		big.style.width = "100%";
		big.style.height = "100%";
		big.style.backgroundColor  = "#000000";
		big.style.opacity = "0.5";
		big.style.filter  = "alpha(opacity=50)";/*ie*/
		big.style.zIndex = "99998";
		big.style.position = this.position;
		big.style.left = "0px";
		return big;
	},
	
	/*ie6兼容*/
	iframeShade : function(){
		var ifr = document.createElement("iframe");
		ifr.id = this.fid;
		ifr.style.width = "100%";
		ifr.style.height = "100%";
		ifr.style.position = "absolute";
		ifr.style.filter = "alpha(opacity=0)";
		ifr.style.zIndex = "99990";
		ifr.scrolling = "no";
		ifr.src="about:blank";
		ifr.style.top = "0px";
		ifr.style.left = "0px";
		document.body.appendChild(ifr);
	},
	
	/*关闭元素*/
	remove : function(){
		document.body.removeChild(this.$(this.bid));
		this.isIe6() ? document.body.removeChild(this.$(this.bid+"second")) : 0;
		this.copy();
		this.removeBox();
		this.removeIframe();
		this.attention();
	},
	
	/*关闭单个元素*/
	removeBox : function(){
		document.body.removeChild(this.$(this.id));
	},
	
	/*关闭ie6兼容*/
	removeIframe : function(){
		var ifrObj = this.$(this.fid);/*ie6*/
		ifrObj ? document.body.removeChild(ifrObj) : 0;
	},
	
	/*复制备份*/
	copy : function(){
		for(var i= 1;i<=5;i++){
			this.$("step"+i).style.display = "none";
			this.$("guide").appendChild(this.$("step"+i));/*移动*/
		}
	},
	
	/*提示小图标*/
	attention : function(){
		var obj = this.$("attention");
		obj.className = "admin_guide_new fr";
		setTimeout('adminguide.recover()',5000);
	},
	
	/*恢复小图标*/
	recover : function(){
		var obj = this.$("attention");
		obj.className = "admin_guide fr";
	},
	
	/*绑定关闭事件*/
	bindAClick : function(box){
		var links = box.getElementsByTagName("a");
		var _this = this;
		for(var i = 0; i<links.length; i++){
			if(links[i].id != this.cid){
				continue;
			}
			links[i].onclick = function(){
				_this.remove();
			}
			return;
		}
		return false;
	},
	
	/*绑定上下步事件*/
	bindButtonClink : function(box,step){
		var links = box.getElementsByTagName("input");
		var _this = this;
		for(var i = 0; i<links.length; i++){
			if(links[i].getAttribute("go") == "next"){
				links[i].onclick = function(){
					_this.step(step+1);
					box.style.display = "none";
				}
			}
			if(links[i].getAttribute("go") == "up"){
				links[i].onclick = function(){
					_this.step(step-1);
					box.style.display = "none";
				}
			}
			if(links[i].type == "checkbox"){
				links[i].onclick = function(){
					var value = (this.checked) ? 1 : 0;
					_this.setCheckBox(value);
					_this.resetCheckbox(step);
				}
			}
			if(links[i].getAttribute("go") == "finish"){
				links[i].onclick = function(){
					_this.remove();
				}
			}
		}
		return false;
	}
		
}