/**
 * 后台导航功能
 * 2009-11-8 lh
 * @use
 * var menus = adminNavClass.get('Sphinx全文索引',mains,menus);
 */
var adminNavClass = {
	/*导航数组 全局*/
	navArray : [],
	navMain  : [],
	/*分割标识符*/
	sing : "|",

	/*剥离对象组装数组*/
	strip : function(obj,prefix){
		for(i in obj){
			this.navArray.push(prefix+this.sing+obj[i].id+this.sing+obj[i].name);
			if(obj[i].items){
				this.strip(obj[i].items,prefix+this.sing+obj[i].id);
			}
		}
		return this.navArray;
	},

	/*剥离主菜单与子菜单*/
	stripMain : function(mainobj,menuobj){
		for(i in mainobj){
			var id = mainobj[i].id;
			var name = mainobj[i].name;
			this.navArray.push(id+this.sing+name);
			var obj = menuobj[id] ? menuobj[id]['items'] : 0;/*菜单对象数组访问*/
			obj ? this.strip(obj,id) : 0;
		}
		return this.navArray;
	},

	/*组装主菜单*/
	buildMain : function(mainobj){
		for(i in mainobj){
			var id = mainobj[i].id;
			var name = mainobj[i].name;
			this.navMain[id] = name;
		}
	},

	/*初始化菜单数据*/
	init : function(mainobj,menuobj){
		this.stripMain(mainobj,menuobj);
		this.buildMain(mainobj);
	},

	/*获取菜单导航*/
	get : function(name,mainobj,menuobj){
		if(typeof(mainobj) != "object" || typeof(menuobj) != "object" || name == ""){
			//return alert(this.language('data_error'));
			return null;
		}
		this.init(mainobj,menuobj);/*初始化*/
		if(this.navArray.length <= 0){
			//return alert(this.language('data_error'));
			return null;
		}
		var result = null;/*是否存在多个相同的菜单*/
		for(var i=0;i<this.navArray.length;i++){
			if(this.navArray[i].indexOf(name) != "-1"){
				result = this.navArray[i];
			}
		}
		if(result === null){
			//return alert(this.language('data_not_exist'));
			return null;
		}
		/*分割*/
		var keys = result.split(this.sing);
		var length = keys.length;/*菜单层次length-1级*/
		var menus = [];
		var topmenu = keys[0] ? keys[0] : '';
		if(topmenu){
			this.navMain[topmenu] ? menus.push(this.navMain[topmenu]) : '';/*主菜单部分*/
		}
		for(var i=0;i<length;i++){
			var menu = this.getNav(keys[i],i+2);
			menu ? menus.push(menu) : 0;
		}
		return menus;
	},

	node : function(name,mainobj,menuobj,obj,depth){
		if(typeof(mainobj) != "object" || typeof(menuobj) != "object" || name == ""){
			//return alert(this.language('data_error'));
			return null;
		}
		this.init(mainobj,menuobj);/*初始化*/
		if(this.navArray.length <= 0){
			//return alert(this.language('data_error'));
			return null;
		}
		var result = null;/*是否存在多个相同的菜单*/
		for(var i=0;i<this.navArray.length;i++){
			if(this.navArray[i].indexOf(name) != "-1"){
				result = this.navArray[i];
			}
		}
		if(result === null){
			//return alert(this.language('data_not_exist'));
			return null;
		}
		/*分割*/
		var keys = result.split(this.sing);
		//var depth = (depth > 0 && keys[0] == 'mode') ? 3 : depth;//模式管理支持四级
		if(depth == 0){
			this.menu(MAIN_BLOCK,obj,name);//MAIN_BLOCK 根目录
		}else if(depth == 1){
			this.menu(menuobj[keys[0]]['items'],obj,name);
		}else if(depth == 2){
			var nodes = menuobj[keys[0]]['items'];
			for(var i=0;i<nodes.length;i++){
				if(nodes[i].id == keys[1]){
					this.menu(nodes[i]['items'],obj,name);
				}
			}
		}else if(depth == 3){
			var nodes = menuobj[keys[0]]['items'];
			for(var i=0;i<nodes.length;i++){//获取三级
				if(nodes[i].id == keys[1]){
					nodes = nodes[i]['items'];
					break;
				}
			}
			for(var i=0;i<nodes.length;i++){//获取四级
				if(nodes[i].id == keys[2]){
					this.menu(nodes[i]['items'],obj,name);
				}
			}
		}else{
			return;
		}

	},

	menu : function(nodes,obj,name){
		var div1 = document.createElement("div");
		div1.id = "topmenu3";
		div1.className = "admenu";
		var div2 = document.createElement("div");
		div2.className = "admenu_bg";

		var div3 = document.createElement("h2");
		div3.innerHTML = name;
		div3.className = "treename";

		var ul = document.createElement("ul");
		for(var i=0;i<nodes.length;i++){
			var li = this.create(nodes[i].id,nodes[i].name,nodes[i].url);
			ul.appendChild(li);
		}
		div2.appendChild(div3);
		div2.appendChild(ul);
		div1.appendChild(div2);
		/*定位*/
		var p = this.getpos(obj);
		div1.style.left = p[0]-12+"px";
		div1.style.top  = p[1]+obj.offsetHeight-21+"px";
		div1.style.position = "absolute";
		div1.style.zIndex = "9999";
		div1.style.width = "110px";
		var _this = this;
		div1.onmouseover = function(evt){
			_this.stop(evt);
		}
		div1.onmousemove = function(evt){
			_this.stop(evt);
		}
		document.body.onmouseover = function(){
			_this.remove();
		}
		var divframe = this.buildIframe("topmenu3fr", div1);
		document.body.appendChild(div1);
		divframe.style.height=div1.clientHeight+'px';
	},

	buildIframe : function(id,element){
		var divframe = document.createElement("iframe");
		divframe.id = id;
		divframe.frameborder = "0";
		divframe.style.left = element.style.left;
		divframe.style.top  = element.style.top;
		divframe.style.position = "absolute";
		divframe.style.zIndex = "9999";
		divframe.style.width = "150px";
		divframe.style.border = "0";
		divframe.style.filter = "alpha(opacity=0)";
		divframe.scrolling = "no";
		divframe.src="about:blank";

		document.body.appendChild(divframe);
		return divframe;
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

    stop : function(evt){
    	if(evt){
    		evt.stopPropagation();
    	}else{
    		event.cancelBubble = true;
    	}
    },

	create : function(id,name,url){
		var li = document.createElement("li");
		var a  = document.createElement("a");
		a.innerHTML = name;
		a.href = "javascript:;";
		a.setAttribute("aid",id);//考虑兼容性
		a.setAttribute("name",name);
		var _this = this;
		if(url){
			a.onclick = function(){
				_this.remove();
				setTimeout(function(){
					PW.Dialog({id:id,name:name,url:url});
				},0);
			}
			li.appendChild(a);
		}else{
			a.onclick = function(){
				_this.remove();
				var name = this.getAttribute("name");
				var id = this.getAttribute("aid");
				var menu = _this.level(id, name, mainnavs,menunavs);//注意mainnavs和menunavs全局变量
				if(menu){
					PW.Dialog(menu);
				}
			}
			li.appendChild(a);
		}
		return li;
	},




	level : function(id ,name,mainobj,menuobj){
		if(typeof(mainobj) != "object" || typeof(menuobj) != "object" || name == ""){
			return null;
		}
		return this.find(id,name,mainobj,menuobj);
	},

	/*目前共有三级菜单，子菜单深度为二级 */
	find : function(id ,name,mainobj,menuobj){
		var depth = 0;
		for(k in mainobj){
			var tmp_id = mainobj[k].id;
			var tmp_name = mainobj[k].name;
			if(tmp_id == id && tmp_name == name){
				depth = 1;
			}
			if(depth == 1){
				var menu = menuobj[tmp_id]['items'][0];
				if( menu.url != undefined){
					return menu;
				}
				//查找字菜单
				var nodes = menuobj[tmp_id]['items'];
				for(var i=0;i<nodes.length;i++){
					var menu = nodes[i]['items'][0];
					if(menu.url != undefined){
						return menu;
					}
				}
			}else{
				//二级菜单查找
				var nodes = menuobj[tmp_id]['items'];
				for(var i=0;i<nodes.length;i++){
					if(nodes[i].id == id && nodes[i].name == name ){
						var menu = nodes[i]['items'][0];
						if(menu.url != undefined){
							return menu;
						}
					}
				}
			}
		}

	},

	$ : function(id){
		return document.getElementById(id);
	},

	remove : function(){
		var obj = this.$("topmenu3");
		var ifr = this.$("topmenu3fr");
		if(obj){
			ifr.parentNode.removeChild(ifr);
			obj.parentNode.removeChild(obj);
		}
	},

	/*获取导航*/
	getNav : function(id,depth){
		for(var i=0;i<this.navArray.length;i++){
			if(this.navArray[i].indexOf(this.sing+id+this.sing) != "-1" && this.navArray[i].split(this.sing).length == depth){
				return this.navArray[i].split(this.sing)[depth-1];
			}
		}
	},


	sid  : "link_screen",
	seid : "link_screen_empty",
	/*全屏*/
	fullscreen : function(){
		var screen = this.$(this.sid);
		var empty  = this.$(this.seid);
		var fullscreen = this.$("fullscreen");
		if(screen){
			screen.parentNode.removeChild(screen);
			this.loadCss("images/admin/fullscreenempty.css",this.seid);
			fullscreen.innerHTML = "<i class=\"admin_full\">全屏</i>";
		}else{
			if(empty){empty.parentNode.removeChild(empty);}
			this.loadCss("images/admin/fullscreen.css",this.sid);
			fullscreen.innerHTML = "<i class=\"admin_fullclose\">退出全屏</i>";
		}
	},
	/*加载css文件*/
	loadCss : function(file,id){
		var css = document.createElement("link");
		css.rel = "stylesheet";
		css.type = "text/css";
		css.href = file;
		css.id = id;
		var head = document.getElementsByTagName("head")[0];
		head.appendChild(css);
	},
	/*刷新页面*/
	refresh : function(){
		var iframe = this.getframe();
		iframe.contentWindow.location.reload(true);
	},

	pid      : "pagesetting",
	showDesc : "showtips",
	showTips : "showfunc",
	/*页面设置*/
	page : function(obj){
		var page = this.$(this.pid);
		if(page){
			page.parentNode.removeChild(page);
		}
		var div1 = document.createElement("div");
		div1.id = this.pid;
		div1.className = "admenu";
		var div2 = document.createElement("div");
		div2.className = "admenu_bg";
		var i = document.createElement("i");
		i.innerHTML = "页面设置";
		i.className = "toppage_down";



		var ul = document.createElement("ul");

		var stvs = this.buildPage("t1"," 显示提示信息",this.showTips);

		var sfvs = this.buildPage("t2"," 显示功能描述",this.showDesc);

		ul.appendChild(stvs);
		ul.appendChild(sfvs);
		div2.appendChild(i);
		div2.appendChild(ul);
		div1.appendChild(div2);
		/*定位*/
		var p = this.getpos(obj);
		div1.className = "toppage_menu admenu";
		div1.style.left = p[0]-obj.offsetWidth+6+"px";
		div1.style.top  = p[1]+obj.offsetHeight-2+"px";

		var _this = this;
		div1.onmouseover = function(evt){
			_this.stop(evt);
		}
		div1.onmousemove = function(evt){
			_this.stop(evt);
		}
		document.body.onmouseover = function(){
			_this.pageRemove();
		}
		var divframe = this.buildIframe("pageifr", div1);
		divframe.onmousemove = function(evt){
			_this.stop(evt);
		}
		document.body.appendChild(div1);
		this.setChecked(this.showTips);
		this.setChecked(this.showDesc);

		divframe.style.height=div1.clientHeight+10+'px';
	},
	/*页面设置选中值*/
	setChecked : function(ckey){
		var sfv = (Cookie.get(ckey)) ? 0 : 1;
		if(sfv){
			this.$(ckey+"input").checked = true;
		}else{
			this.$(ckey+"input").checked = false;
		}
	},
	/*组装页面设置内容*/
	buildPage : function(name,descript,ckey){
		var li = document.createElement("li");
		var input = document.createElement("input");
		input.id = ckey+"input";
		input.type="checkbox";
		input.name=name;
		var value = (Cookie.get(ckey)) ? 0 : 1;
		input.value=value;
		var _this = this;

		var span = document.createElement("span");
		span.innerHTML = descript;
		li.appendChild(input);
		li.appendChild(span);
		li.onclick = function(e){
			e = e||window.event;
			var target = e.srcElement||e.target;
			var v = (input.checked) ? 0 : 1;
			if(target.tagName!='INPUT')
			{
				var x = !!v;
				input.checked=x;
				v = v?0:1;
			}
			if(v){
				setCookie(ckey,v);
			}else{
				Cookie.del(ckey);
			}
			_this.initTips();
		}
		return li;
	},
	/*移除页面设置*/
	pageRemove : function(){
		var page = this.$(this.pid);
		var ifr = this.$("pageifr");
		if(page){
			ifr.parentNode.removeChild(ifr);
			page.parentNode.removeChild(page);
		}
	},
	/*初始化信息*/
	initTips : function(){
		var tips = Cookie.get(this.showTips) ? 0 : 1;
		this._showTips(tips);
		var desc = Cookie.get(this.showDesc) ? 0 : 1;
		this._showDesc(desc);
	},
	/*控制提示信息*/
	_showTips : function(isopen){
		var iframe = this._getChild();
		var infos = this.$C("admin_info",iframe);
		var v = (isopen) ? "block" : "none";
		if(infos){
			for(var i=0;i<infos.length;i++){
				infos[i].style.display = v;
			}
		}
	},
	/*控制功能描述*/
	_showDesc : function(isopen){
		var iframe = this._getChild();
		var descs = this.$C("help_a",iframe);
		var v = (isopen) ? "block" : "none";
		if(descs){
			for(var i=0;i<descs.length;i++){
				descs[i].style.display = v;
			}
		}
	},
	/*获取iframe子元素*/
	_getChild : function(){
		var iframe = this.getframe();
		return iframe.contentWindow.document;
	},
	/*获取当前iframe*/
	getframe : function(){
		var frames = this.$("desktopContainer").getElementsByTagName("iframe");
		for(var i=0;i<frames.length;i++){
			if(frames[i].style.display == ""){
				return frames[i];
			}
		}
	},

	$C : function (className, parentElement){
		if (typeof(parentElement)=='object') {
			var elems = parentElement.getElementsByTagName("*");
		} else {
			var elems = (document.getElementById(parentElement)||document.body).getElementsByTagName("*");
		}
		var result=[];
		for (i=0; j=elems[i]; i++) {
		   if ((j.className).indexOf(className)!=-1) {
				result.push(j);
		   }
		}
		return result;
	},

	manage : function(){},

	/*语言*/
	language : function(key){
		var m = [];
		m['data_error'] = "数据格式不正确";
		m['data_not_exist'] = "查找的菜单不存在";
		return m[key];
	}
}
function closeAdminTab(win){
	if(win.frameElement){
		var mid = win.frameElement.id.substr(7);
		parent.getObj('button_'+mid).getElementsByTagName('a')[1].onclick();
	}
}
