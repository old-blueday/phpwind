/**
 *include Compatibility.js core.js
 *菜单类，用于创建无极限的菜单。
 继承自baseClass基类。
 使用方法：new PW.Menu(config).render();
 */
 ~
function()
{
    var ROOT = document.documentElement;
	var getObj=function(id){return document.getElementById(id);};
	var ce=function(tag){return document.createElement(tag);};
	PW.Menu = baseClass.extend();
	PW.Menu.all={};
	var _MP=PW.Menu.prototype;
	_MP._createClass=function()
	{
		return new PW.Menu();
	};
    _MP.addItem = function(json,last)
    {
        if (json == "-")
        {
            var b = ce("DIV");
			var a = ce("DIV");
			b.appendChild(a);
			b.style.paddingLeft="16px";
            a.className = "menu-sep";
            this.self.appendChild(b);
            return;
        }
        var a = ce("A");
        a.href = "javascript:;";
        a.id = "menuItem_" + json.id;
		//IE?a.setAttribute("hideFocus","true"):a.style.outline='none';
        a.innerHTML = json.name;
		a.disabled=json.disabled||false;
		var li=ce("li");
		li.appendChild(a);
		a.style.cursor="default";
		last||this.noArrow?li.style.border="0":0;/*是否有下级子菜单*/
        this.self.appendChild(li);
        var _this = this;
		li.onmouseover = function(){/*鼠标效果*/
			var elements = getObj("menu_"+_this.id).getElementsByTagName("li");
			for(var i=0;i<elements.length;i++){
				elements[i].className = "";
			}
			li.className = "current";
		}
		if(!json.items)
		{
			a.onclick = function(evt)
			{
				var e=evt||window.event;
				if(e.stopPropagation){
					e.stopPropagation();
				}else{
					e.cancelBubble = true;
				}
				if(this.disabled) return;
				_this.remove();
				_this.handler(json);/*点击事件*/
				return false;
			};
			a.className="menu_s1 fr";
		}
        else
        {
			//var arrow=ce("span");/*下一级*/
			a.className="menu_s2 fr";
			/*子菜单模式*/
			a.setAttribute("aid",json.id);
			a.setAttribute("name",json.name);
			a.onclick = function(){
				PW.ChildDialog(this);
			}
			//a.insertBefore(arrow,a.firstChild);
        }
		//var showedMenu={};
        var showItems= function()
        {
            if (json.items/*&&showedMenu.id!=_this.id+"_"+a.id*/)
            {
                var b = _this._createClass();
                b.body = _this.body;
                var c = _this.getPos(this);
                b.id = _this.id+"_"+a.id;/*级联定位*/
                b.width = _this.width;
                b.left = c[0] + this.offsetWidth - b.body.offsetLeft-150; /*新菜单左+宽 170+8 */
                b.items = json.items;
				var fixtop=50;
				b.top = c[1] - b.body.offsetTop-72;
                b.render();
				if(b.element.offsetTop>_this.ROOT.offsetHeight-b.element.offsetHeight-fixtop)
				{

					b.element.style.top=_this.ROOT.offsetHeight-b.element.offsetHeight-fixtop+"px";
				}
				//showedMenu=b.id;/*error*/
				_this.self.actived&&_this.self.actived.id!=b.id?_this.self.actived.remove():0;
				_this.self.actived=b;

            }
			else
			{
				if (typeof(_this.self.actived) != 'undefined'){
					_this.self.actived.remove();
				}
			}
       };
		a.onmouseover =showItems;
        return a;
    };
    _MP.removeItem = function(id)
    {
        this._remove(getObj('menuItem_' + id));
    };
    _MP.getPos = function(d)
    {
        var e = [0, 0];
        var el = d;
        while (el)
        {
            if (el == this.ROOT) break;

            e[0] = e[0] + el.offsetLeft;
            e[1] = e[1] + el.offsetTop;
            el = el.offsetParent;
        }
        return e;
    };
    _MP.show = function()/*主容器样式调整*/
    {
		var sty=this.menu.style;
		//with(this.menu.style)

        //{

            sty.width = this.width + "px";

            sty.left = (this.left+this.width>this.ROOT.offsetWidth?this.ROOT.offsetWidth-this.width:this.left) + "px";

			/*指示样式*/
			//if(this.top+this.menu.offsetHeight>this.ROOT.offsetHeight-60)
			//{
				//var tt=this.top-(this.ROOT.offsetHeight-this.menu.offsetHeight-fixtop)+60;
				//this.arrow?this.arrow.style.top=tt+"px":0;
			//}
			
            //sty.top = (this.top+this.menu.offsetHeight>this.ROOT.offsetHeight-37?this.ROOT.offsetHeight-this.menu.offsetHeight-fixtop:this.top) + "px";
			sty.top = (this.top-26+this.menu.offsetHeight>this.ROOT.offsetHeight?this.ROOT.offsetHeight-this.menu.offsetHeight:this.top-26) + "px";
			//sty.top = (this.top+this.menu.offsetHeight>this.ROOT.offsetHeight-60?this.ROOT.offsetHeight-this.menu.offsetHeight-fixtop:this.top) + "px";
			sty.position = "absolute";
            //sty.display = "";
        //}

    };
    _MP._remove = function(el)
    {
		if(!el)return;
        var a = ce("DIV");
        a.appendChild(el);
        a.innerHTML = "";
        a = null;
    };
    _MP.onremoved = function()
    {};
    _MP.remove = function()
    {
        this._remove(this.menu);
		window.lastObj?this.menu.id.indexOf(lastObj.id)>0?lastObj=0:0:0;
		this.onremoved();
    };
	_MP.handler=function(items)
	{
		items.onclick ? items.onclick.call(items) : 0;
		this.onclick?this.onclick():0;
	};
    _MP.render = function()
    {
        ROOT = document.body;
		var arrow;
        var _this = this;
        this.items = this.items || [];/*数据对象*/
        this.ROOT = ROOT;
        this.body = this.body || document.body;/*显示的区域*/
        if (getObj("menu_" + this.id))
        {
            var a = getObj("menu_" + this.id);
			this.self=a.lastChild;/*最后个子元素*/
        }
		else
        {

            var a = ce("div");/*主菜单容器*/
			//a.style.position="relative";
			//a.className="menuIndex";
			if(!getObj("arrow_div")&&!this.noArrow) /*指示*/
			{
			arrow=ce("DIV");
			arrow.className="menu_s";
			arrow.style.left="0px";
			arrow.id="arrow_div";

			//a.appendChild(arrow);
			}
            a.id = "menu_" + this.id;
            //a.className = "gMenu";

            var b = ce("div");/*菜单区*/
            b.className = "menu_bg";
            b.innerHTML="<ul class=\"menu_li\"></ul>";

            //b.innerHTML="<div class=\"menu_bg\"><ul class=\"menu_li\"></ul></div>";
            //b.className = "gM_Warp gMenuOpt";
            this.self =b.getElementsByTagName("UL")[0];
            /*隐藏*/
            a.style.left='-10000px';//a.style.display = "none";

            a.appendChild(b);
            this.body.appendChild(a);
			if(IE)
			{
				a.attachEvent("onmouseover",function()
				{
					 window.MOUSE_OVERED=1;
					 clearTimeout(window.MenuTimer);
				});
				a.attachEvent("onmouseout",function()
				{
					 window.MenuTimer=setTimeout(function(){
					 window.MOUSE_OVERED=0;
					 },30);
				});
			}
            var item, items;

            /*菜单条内容组装*/
            for (var i = 0,len = this.items.length; i < len; i++)
            {
                item = this.addItem(this.items[i],i==this.items.length-1);
				if(!item)continue;
                if (!this.items[i].items)
                {
                    items = _this.items[i];

                }
				else
                {
                    item.onmousedown = function(evt)
                    {
                        var e=evt||window.event;
						if(e.stopPropagation){
							e.stopPropagation();
						}else{
							e.cancelBubble = true;
						}
                    };
                }
            }

            /*指定条的高度*/
            //var top=parseInt(a.offsetHeight/2-18/2-20);
			//arrow?(arrow.style.top=47+"px"):0;
			//this.arrow=arrow;


            /*窗口点击关闭事件 firefox*/
            ROOT[a.id] ? removeEvent(ROOT,"mousedown", ROOT[a.id]) : 0;
            addEvent(ROOT,"mousedown",ROOT[a.id]=function(evt)
            {
				var e=evt||window.event;
				var evt=e.target||e.srcElement;
				var el=evt.outerHTML||evt.parentNode.outerHTML;
				navigator.userAgent.toLowerCase().indexOf("firefox")>0?el=evt.innerHTML||evt.parentNode.innerHTML:0;
                if (b.innerHTML.toLowerCase().indexOf(el.toLowerCase())==-1)
                {
                    _this.remove();
                }
            });

        }
		PW.Menu.all[this.id]=this;
		this.menu = a;
		this.element=a;
		this.show();
		a.style.zIndex="10000001";

    };

} ();
