/**
 * 后台主题引戳
 * 2009-12-3 lh
 * @use 
 */
var adminoverprint = {
	
	prefixselect : "selecticon_",
	width        : "300",
	height       : "300",
	boxId        : "popbox",
		
	$ : function(id){
		return document.getElementById(id);
	},

	/*id 选择器*/
	IDSelector : function(tagName,find){
		var tags = document.getElementsByTagName(tagName);
		var elements = new Array();
		for(i=0;i<tags.length;i++){
			var id = tags[i].id;
			if(id.indexOf(find) >= 0 ){
				elements.push(tags[i]);
			}
		}
		return elements;
	},
	
	createIcons : function(icons){
		
	},
	
	openBox : function(obj){
		var box = this.$(this.boxId);
		if(box){
			this.setPosition(box,obj);
			box.style.display = "";
			return ;
		}
		var box = document.createElement("div");
		this.setPosition(box,obj);
		box.id = this.boxId;
		box.style.position = "absolute";
		box.style.zIndex = "99999";
		var html = this.$("iconlist");
		box.appendChild(html);
		document.body.appendChild(box);
		html.style.display = "";
	},
	
    getPosition : function(d) {
        var e = [ 0, 0 ];
        var el = d;
        while (el) {
            if (el == document.body)
                break;
            e[0] = e[0] + el.offsetLeft;
            e[1] = e[1] + el.offsetTop;
            el = el.offsetParent;
        }
        return e;
    },
	
	/*设置弹出框位置*/
	setPosition : function(box,obj){
    	var e = this.getPosition(obj);
		//var left = document.body.scrollLeft + (document.body.clientWidth-parseInt(this.width))/2;
		//var top = document.body.scrollTop + (document.body.clientHeight-parseInt(this.height))/2;
		//box.style.top = top+"px";
		//box.style.left = left+"px";
		box.style.top  = e[1]+"px";
		box.style.left = e[0]-100+"px";
		return box;
	},
	
	/*绑定图片选择*/
	bindSelectIcon : function(){
		var elements = this.IDSelector("a", this.prefixselect) || [];
		var _this = this;
		for(i=0;i<elements.length;i++){
			elements[i].onclick = function(e){
				var id = this.getAttribute("id").split("_")[1];
				_this.$("current").value = id;/*设置当前选中的对象*/
				_this.openBox(this);
			}
		}
		
	},
	
	checkInput : function(){
		var inputs = document.getElementsByTagName("input");
		var elements = new Array();
		checked = 0;
		for(i=0;i<inputs.length;i++){
			if(inputs[i].type != "checkbox"){
				continue;
			}
			var name = inputs[i].getAttribute("name");
			if(name.indexOf("list")<0){
				continue;
			}
			if(inputs[i].checked){
				checked++;
			}
			elements.push(inputs[i]);
		}
		if(checked == elements.length ){
			for(i=0;i<elements.length;i++){
				elements[i].checked = "";
			}
		}else{
			for(i=0;i<elements.length;i++){
				elements[i].checked = "checked";
			}
		}
		return false;
	},
	
	bindCloseBox : function(){
		var close = this.$("closebox");
		if(!close) { return false; };
		var _this = this;
		close.onclick = function(){
			_this.closeBox();
			return false;
		}
	},
	
	closeBox : function(){
		var obj = this.$(this.boxId);
		if(!obj){return false;}
		obj.style.display = "none";
	},
	
	bindUserOperate : function(){
		var elements = this.IDSelector("a", "popusericon_");
		var _this = this;
		for(i=0;i<elements.length;i++){
			elements[i].onclick = function(){
				var id = this.getAttribute("id").split("_")[1];
				_this.resetIcon(id);
				return false;
			}
		}
		
	},
	
	bindDelete : function(){
		var elements = this.IDSelector("a", "delete_");
		var _this = this;
		for(i=0;i<elements.length;i++){
			elements[i].onclick = function(){
				var url = this.getAttribute("url");
				if(!url){
					return false;
				}
				if(confirm("你确定要删除这条主题印戳吗？")){
					window.location.href = url;
				}
				return false;
			}
		}
	},
	
	resetIcon : function(id){
		var srcpath = this.$("popiconpath_"+id).getAttribute("path");
		var v = this.$("current").value;
		this.$("iconpath_"+v).src=iconPath+"/"+srcpath;
		this.$("iconinput_"+v).value=srcpath;
		this.closeBox();
	},
	
	init : function(){
		this.bindSelectIcon();
		this.bindCloseBox();
		this.bindUserOperate();
		this.bindDelete();
	}
		
}































