var imgAdverClass = {
	/*增加图片类 @2009-11-24 lh*/
	num : 2,
	prefix : 'imglist_',
	wrapid : 'imglist',
	$ : function(id){
		return document.getElementById(id);
	},
	add : function(){
		var list = this.$(this.wrapid).getElementsByTagName("tbody");
		var total = list.length -1;/*元素的调整*/
		var next = total+1;
		var current = this.$(this.prefix+total);
		var parent = current.parentNode;
		var tbody = document.createElement("tbody");
		tbody.id = this.prefix+next;
		parent.insertBefore(tbody,current.nextSibling);
		this.template(next);
	},

	remove : function(num){
		var obj = this.$(this.prefix+num);
		var parent = obj.parentNode;
		parent.removeChild(obj);
	},

	template : function(next){
		/*template*/
		var tr1 = this.createTR();
		var td1 = this.createTD('图片地址');
		var td2 = this.createTD('图片链接：<input onclick="isUploadOrLinkImg(this, '+next+')" name="config[imgupload]['+next+']" type="radio" value="0" checked="checked" />&nbsp;&nbsp;&nbsp;&nbsp;图片上传：<input onclick="isUploadOrLinkImg(this, '+next+')" name="config[imgupload]['+next+']" type="radio" value="1" /><br/><span id="url_'+next+'"><input type="text" class="input input_wb" name="config[url]['+next+']" value=""></span><span id="upload_'+next+'" style="display:none;"><input name="uploadurl_'+next+'" accept="image/*" class="input input_wb"  type="file" /></span>&nbsp;&nbsp;<a href="javascript:;">[移除]</a>');
		var td5 = this.createTD('<div class="help_a">&nbsp;</div>');
		td1.className="td1";
		td2.className="td2";
		td5.className="td2";
		tr1.appendChild(td1);
		tr1.appendChild(td2);
		tr1.appendChild(td5);

		var a = tr1.getElementsByTagName("a")[0];/*绑定移除事件*/
		var _this = this;
		a.onclick = function(){
			_this.remove(next);
		}
		var tr2 = this.createTR();
		var td3 = this.createTD('图片链接');
		var td4 = this.createTD('<input type="text" class="input input_wb" name="config[link]['+next+']" value="http://">');
		var td6 = this.createTD('<div class="help_a">&nbsp;</div>');
		td3.className="td1";
		td4.className="td2";
		td6.className="td2";
		tr2.appendChild(td3);
		tr2.appendChild(td4);
		tr2.appendChild(td6);

		var nextId = this.prefix+next;
		this.$(nextId).appendChild(tr1);
		this.$(nextId).appendChild(tr2);
	},

	createTR : function(){
		var tr = document.createElement("tr");
		tr.className="tr1 vt";
		return tr;
	},

	createTD : function(html){
		var td = document.createElement("td");
		td.innerHTML = html;
		return td;
	}
}

var adverDuring = {
	/*广告排期@2009-11-25 lh*/
	prefix : 'wrappop',

	$ : function (id){
		return document.getElementById(id);
	},

	IE : function(){
		return ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));
	},

	bind : function(){
		var list = this.$("list").getElementsByTagName('div');
		this.remove();
		var _this = this;
		for(i=0;i<list.length;i++){
			list[i].onmouseover = function(e){
				var e = e || event;
				_this.tips(e,this);
			}
			list[i].onmouseout = function(){
				//_this.remove(this);
			}
		}
		obj = list[0] || list[1];
		obj.onmouseover();
	},

	tips : function(event,obj){
		//var left = event.offsetX;
		//var top  = event.offsetY;
		var info = obj.getAttribute("info");
		var id   = obj.getAttribute("id");
		var url   = obj.getAttribute("url");
		var pos = this.getpos(obj);
		var top  = pos[1]+20+"px";
		var left = pos[0]+"px";

		var wrap = this.$(this.prefix);
		if(!wrap){
			var wrap = document.createElement("div");
			wrap.className = "admin_pop";
			wrap.id = this.prefix;
			wrap.style.display  = "none";
			wrap.style.zIndex   = 100;
			wrap.style.position = "absolute";
		}
		wrap.style.top      = top;
		wrap.style.left     = left;

		var html = info;/*html elements*/
		url = url ? url : '#';
		html += '<br><a href="'+url+'" >添加广告</a>';
		wrap.innerHTML = html;

		var _this = this;
		wrap.onmouseout = function(){
			//_this.remove();
		}
		wrap.onmouseover = function(){
			//
		}
		document.body.appendChild(wrap);
		wrap.style.display = "";
	},

	getpos : function(d) {
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

	remove : function(){
		var pop = this.$(this.prefix);
		pop ? document.body.removeChild(pop) : 0;
	},

	bindBench : function(){
		var bench = this.$("adverBench");
		bench.onchange = function(){
			document.during.submit();/*submit*/
		}
	},
	bindTime : function(){
		var time = this.$("advertime");
		time.onchange = function(){
			document.during.submit();/*submit*/
		}
	}
}