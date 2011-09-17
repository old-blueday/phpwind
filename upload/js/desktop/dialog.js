/**
 * 对话框类 用于创建多个窗口，方便切换操作。 使用：PW.Dialog(JSONArgu); JSONArgu 为一个object类型数据。如：
 * {id:'',url:'',name:''}
 *
 * @param string
 *            id 窗口id。
 * @param string
 *            url 窗口所对应的url地址
 * @param string
 *            name 窗口的标题.
 */
~function() {
	/**
	 * 获取对象的相对于body的绝对坐标
	 *
	 * @param nodeElement
	 *            d 对象
	 * @return Array:[x,y]
	 */
	var _getPos = function(d) {
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
	};

	/**
	 * 左右选择器
	 */
	PW.lrSelector = function() {
		var winButtons = getObj("taskbar").getElementsByTagName("LI");
		var totalNum = winButtons.length;
		for ( var i = 0; i < totalNum; i++) {
			if (winButtons[i].className == "current") {
				current = i;
			}
		}
		// 左选择
		var leftKey = current - 1;
		var left = leftKey >= 0 ? winButtons[leftKey] : 0;
		var navleft = getObj("navleft");
		if (left) {
			navleft.className = "admin_nav_left";
			navleft.onclick = function() {
				left ? left.self.onclick() : 0;
			}
		} else {
			navleft.className = "admin_nav_left_old";
			navleft.onclick = function() {
			}/* 注销事件 */
		}
		// 右选择
		var rightKey = current + 1;
		var right = rightKey > 0 ? winButtons[rightKey] : 0;
		var navright = getObj("navright");
		if (right) {
			navright.className = "admin_nav_right";
			navright.onclick = function() {
				right ? right.self.onclick() : 0;
			}
		} else {
			navright.className = "admin_nav_right_old";
			navright.onclick = function() {
			}/* 注销事件 */
		}
		if(!totalNum ){/*如果没有窗口菜单*/
			navleft.style.display = "none";
			navright.style.display = "none";
		}else{
			navleft.style.display = "";
			navright.style.display = "";
		}
		return false;
	}

	/* 左右分页器 */
	PW.lrPager = function() {
		// var showNum = 8;/*根椐页面宽度调整*/
		var showNum = Math
				.ceil((document.documentElement.clientWidth - 170) / 116);
		var current;
		var winButtons = getObj("taskbar").getElementsByTagName("LI");
		var totalNum = winButtons.length;
		/* 获取当前菜单的位置 */
		for ( var i = 0; i < totalNum; i++) {
			if (winButtons[i].className == "current") {
				current = i;
			}
		}
		var page = Math.ceil((current + 1) / showNum);
		var start = (page - 1) * showNum;
		var end = start + showNum - 1;
		for ( var j = 0; j < totalNum; j++) {
			if (j >= start && j <= end) {
				winButtons[j].style.display = "";
				continue;
			}
			winButtons[j].style.display = "none";
		}
		return false;
	}

	PW.checkClose = function(){
		var elements =getObj("taskbar").getElementsByTagName("li");
		if(elements.length == 0){
			PW.openHome();
		}
	}

	/*导航功能*/
	PW.menuNav = function(obj){
		getObj("breadCrumb").innerHTML = "";
		var defaulMenu = "后台首页";
		var index = [defaulMenu,"搜索结果","关于phpwind","后台菜单地图"];
		var obj = getObj("button_"+obj.id);
		var name = obj.firstChild.firstChild.innerHTML;/*获取当前的菜单名称*/
		//后台首页单独处理
		if(index.toString().indexOf(name) != "-1"){
			menus = [name];
		}else{
			var menus = adminNavClass.get(name,mainnavs,menunavs);
			if(menus == null){//默认
				menus = [defaulMenu];
			}
			if(menus[0] == "模式管理"){
				menus.splice(0,1);
			}
		}
		//getObj("breadCrumb").innerHTML = "当前位置 &raquo; "+menus.join(" &raquo; ");

		var menubox = document.createElement("div");
		menubox.innerHTML = "当前位置: ";
		var j = 1;
		var length = menus.length;
		for(i=0;i<length;i++){
			var span       = document.createElement("span");
			var divide     = (j == length) ? "" : " &raquo; ";
			//var classname  = (j == length && j >2 ) ? "admenu_down" : "";
			var classname  = ( length == 1 ) ? "" : "admenu_down";
			span.className = classname;
			span.innerHTML = menus[i];
			span.setAttribute("menu",menus[i]);
			span.setAttribute("depth",i);
			/*过滤主菜单*/
			//if( i > 0 ){
				span.onclick = function(evt){
					adminNavClass.remove();
					var name = this.getAttribute("menu");
					var depth = this.getAttribute("depth");
					adminNavClass.node(name,mainnavs,menunavs,this,depth);
					//adminNavClass.stop(evt);
					return false;
				}
				span.onmouseout = function(){
				}
			//}
			var span1 = document.createElement("span");
			span1.innerHTML = divide;
			menubox.appendChild(span);
			menubox.appendChild(span1);
			j++;
		}
		getObj("breadCrumb").appendChild(menubox);
	}
	/*弹出子菜单页面*/
	PW.ChildDialog = function(obj){
		adminNavClass.remove();
		var name = obj.getAttribute("name");
		var id = obj.getAttribute("aid");
		var menu = adminNavClass.level(id, name, mainnavs,menunavs);
		if(menu){
			setTimeout(function(){
				PW.Dialog(menu);
			},0);
		}
		/*关闭弹出菜单*/
		for ( var i in PW.Menu.all) {
			PW.Menu.all[i] ? PW.Menu.all[i].remove ? PW.Menu.all[i].remove() : 0 : 0;
		}
		return false;
	}

	PW.Dialog = function(items) {
		window.MOUSE_OVERED = false;
		if (!getObj("iframe_" + items.id)) {
			var ifr = document.createElement("iframe");
			ifr.scrolling = "auto";
			ifr.width = "100%";
			ifr.height = getObj('desktopContainer').offsetHeight + "px";
			ifr.frameBorder = "no";
			ifr.style.border = "0";
			ifr.src = items.url;
			ifr.id = "iframe_" + items.id;
			getObj('desktopContainer').appendChild(ifr);
			//ifr.contentWindow.onkeydown=enterkeycode;
		} else {
			getObj("iframe_" + items.id).src = items.url;
		}
		var ifr = getObj("iframe_" + items.id);
		var mousedownFn = function(ev, win) {

			for ( var i in PW.Menu.all) {
				PW.Menu.all[i] ? PW.Menu.all[i].remove ? PW.Menu.all[i]
						.remove() : 0 : 0;
			}
			try {
				PW.setCurrent();
				startPanelShow.remove();
			} catch (e) {
			}
			// !IE?event.cancelBubble=true:0;
		};
		var allIframes = [ ifr ];
		for ( var i = 0, len = allIframes.length; i < len; i++) {
			cwin = allIframes[i].contentWindow;
			if (cwin.document) {
				cwin.document.onmousedown = function(ev) {
					mousedownFn(ev || event, cwin)
				};
			}
			var onloadFn = function() {

				try {
					setTimeout(function() {
						cwin.focus();
					}, 1000);
				} catch (e) {
				}
				return function() {
					cwin.document.onmousedown = function(ev) {
						mousedownFn(ev || event, cwin)
					};
				};
			};
			onloadFn = onloadFn.call(cwin);
			removeEvent(allIframes[i],"load",onloadFn);
			addEvent(allIframes[i],"load",onloadFn)

		}
		if (PW.Window.all[items.id]) {

			var b = PW.Window.all[items.id];
			mousedownFn();
		} else {
			var b = new PW.TaskButton();

		}

		PW.Window.all[items.id] = b;
		b.id = items.id;

		/**
		 * 当任务栏的按钮被删除时触发此方法，来删除对应的iframe窗口
		 */

		b.onremove = function() {
			$removeNode(ifr);
			PW.lrSelector();
			PW.checkClose();
		};
		b.text = items.name;
		// b.width = 94;/*需要适当调整*/
		/**
		 * 当点击了任务栏的按钮后，触发此方法。
		 */
		b.onclick = function() {
			ACTIVEDBUTTON ? ACTIVEDBUTTON.blur() : 0;
			ifr.style.display = "";
			b.focus();
		};

		/**
		 * 当聚焦到按钮时，触发该方法
		 */
		b.onfocus = function() {
			ifr.style.display = "";
			PW.lrSelector();/* 左右选择器 */
			PW.lrPager();/*左右分页*/
			PW.setCurrent();/*当前*/
			PW.menuNav(b);/*导航*/
			adminNavClass.initTips();
			getObj('taskbar').scrollTop = IE ? b.element.offsetTop
					: _getPos(b.element)[1] - _getPos(getObj('taskbar'))[1];

		};
		/**
		 * 当按钮失去焦点时，触发此方法。
		 */
		b.onblur = function() {
			ifr.style.display = "none";
		};
		b.render(getObj('taskbar'));
		this.button = b;
		b.element.self = b;
		/**
		 * 在按钮被删除前，触发该方法，做一些切换邻近按钮的工作
		 */
		b.onbeforeremove = function() {
			if (ACTIVEDBUTTON != this) {/* 如果不是当前按钮则不切换 */
				return;
			}
			var e = b.element.previousSibling || b.element.nextSibling;
			e ? e.self.onclick() : 0;
		};

		getObj('taskbar').scrollTop = getObj('taskbar').scrollHeight;
		items.onclick ? items.onclick(items) : 0;
		b.focus();
		/**
		 * 为了兼容老的代码，这里故意封装为对象返回。
		 */
		return {
			loadIframe : function() {
				ifr.src = items.url;
				return {
					ifr : ifr
				}
			},
			ifr : ifr
		};
	};
}();