/**
 *include Compatibility.js drag.js resize.js
 * 窗口控件
 * 继承自基类baseClass
 */
~
function()
{
    var windowIndex = 1;
	var csn='windoo-sizer windoo-';
    var IE = document.all;
    var getObj = function(s)
    {
        return document.getElementById(s);
    };
	FOCUSWINDOW="";
    var uniqueID = 0;
    
	PW.Window = baseClass.extend();
	var _WP=PW.Window.prototype;
	_WP._defaultSize={width:760,height:500,left:40,top:20};
	PW.Window.all={};
	/**
	 *删除节点
	 */
	PW.Window.remove = function(el)
    {
        var a = document.createElement("DIV");
        a.appendChild(el);
        a.innerHTML = "";
        a = null;
    };
	/**
	 *内部方法，获取窗口的当前尺寸位置
	 */
    _WP._getWinSize = function()
    {
        return	        {
            width: this.self.win.offsetWidth,
            height: this.self.win.offsetHeight,
            left: this.self.win.offsetLeft,
            top: this.self.win.offsetTop
        };
    };
    _WP.findNode = function(jsonArg)
    {
        if (jsonArg.className)
        {
            var a = this.self.win.getElementsByTagName("div");
            for (var i = 0,len = a.length; i < len; i++)
            {
                if (a[i].className == jsonArg.className)
                {
                    return a[i];
                }
            }
        } else
        {
            return this.self.win.getElementById(jsonArg.id);
        }
    };
	/**
	 *设置窗口的大小位置。
	 */
    _WP.setSize = function(jsonArg)
    {
        jsonArg.resizeobj = this.self.win;
        var _this = this;
        jsonArg.onResize = function()
        {
			if(!getObj('shadow_body' + _this.id)) return;
            _this.self.shadow.style.width = _this.self.win.offsetWidth + 26 + 'px';
            _this.self.shadow.style.height = _this.self.win.offsetHeight + 31 + 'px';
            _this.self.shadow.style.left = Number(_this.self.win.style.left.replace("px", "")) - 13;
            _this.self.shadow.style.top = Number(_this.self.win.style.top.replace("px", "")) - 7;
            getObj('shadow_body' + _this.id).style.height = _this.self.win.offsetHeight - 23 + 'px';
            getObj('main_body' + _this.id).style.width = _this.self.shadow.offsetWidth - 63 + 'px';

            getObj('main_body' + _this.id).style.height = _this.self.shadow.offsetHeight - 23 + 'px';
            _this.onresize ? _this.onresize(_this.self.win) : 0;

        };
        if (IE)
        {
            clearTimeout(_this.resizeTimer);
            _this.resizeTimer = setTimeout(function()
            {
                PW.Resize.setSize(jsonArg);
            },
            100);
        } else
        {
            PW.Resize.setSize(jsonArg);
        }
		this.maximized||this.minimized?0:PW.Window._lastModifiedSize=this._getWinSize();
    };
	/**
	 *预定义事件。当窗口聚焦时，触发此事件
	 */
    _WP.onfocus = function()
    {};
    /**
     *当失焦时，出发此事件。
     */
	_WP.onblur = function()
    {};
	/**
	 *内部方法，失焦后的处理
	 */
    _WP._blur = function()
    {
        this._opacity(FOCUSWINDOW.self, 30);
		FOCUSWINDOW.self.shadow.style.zIndex = parseInt(this.self.shadow.style.zIndex)-2;
        FOCUSWINDOW.self.win.style.zIndex = parseInt(this.self.win.style.zIndex)-1;
		this.focused=false;
		FOCUSWINDOW.self.win.className="windoo windoo-aqua-blur windoo-aqua-ie";
        this.onblur.call(this.self);
		
    };
	_WP._fo="win";
	_WP._f=false;
	_WP._opacity = function(json, s)
    {
        this.opacity(json[this._fo],s);
    };
    _WP.opacity = function(htmlElement, s)
    {
		if(!this._f)
		{
			return htmlElement.isBlur=s;
		}
        IE ? (htmlElement.style.filter = s ? "alpha(opacity=" + s + ")": "") : (htmlElement.style.opacity = s ? s / 100 : "");
    };
	/**
	 *聚焦后处理
	 */
    _WP._focus = function(self)
    {
		this.focused=true;
		FOCUSWINDOW.focused=false;
		var index=this.getMaxIndex();
		self.shadow.style.zIndex =index+2;
		self.win.style.zIndex = index+2;
        if (this._isBlur(self))
        {
            this._opacity(self, "");
			this._blur();
            this.onfocus.call(self);
			self.shadow.style.display = "";
			self.win.style.display = "";
            FOCUSWINDOW = this;
        }
		FOCUSWINDOW.self.win.className="windoo windoo-aqua-focus windoo-aqua-ie";
    };
	/**
	 *窗口的切换
	 */
	_WP.toggle=function()
	{
		if(this.focused)
		{
			this.min();
		}
		else
		{
			this.toFront();
		}
	};
	_WP._isBlur=function(self)
	{
		if(!this._f)
		{
			return self.win.isBlur;
		}
		return (IE && self[this._fo].style.filter != "") || (!IE && self[this._fo].style.opacity != "");
	};
	/**
	 *窗口置最前
	 */
    _WP.toFront = function()
    {
		FOCUSWINDOW.focused=false;
		this.self.shadow.style.display = "";
		this.self.win.style.display = "";
		var index=this.getMaxIndex();
            this.self.win.style.zIndex = index;
			this.self.shadow.style.zIndex = Number(index);
        if (this._isBlur(this.self))
        {

            this._blur();
            this._opacity(this.self, '');
            FOCUSWINDOW = this;
        }
		this.onfocus.call(this.self);
		FOCUSWINDOW.self.win.className="windoo windoo-aqua-focus windoo-aqua-ie";
		this.focused=true;
		return this;
    };
    _WP._removeDragEvent = function()
    {
        this.self.win.mouse_down?getObj('drag-' + this.id).detachEvent("onmousedown", this.self.win.mouse_down):0;
        getObj('drag-' + this.id).style.cursor = "default";
    };
    _WP._addDragEvent = function()
    {
        this.self.win.mouse_down?getObj('drag-' + this.id).attachEvent("onmousedown", this.self.win.mouse_down):0;
        getObj('drag-' + this.id).style.cursor = "move";
    };
    _WP._addResizeEvent = function()
    {
        var a = [this.findNode({
            className: csn+'west'
        }), this.findNode({
            className: csn+'east'
        }), this.findNode({
            className: csn+'north'
        }), this.findNode({
            className: csn+'south'
        }), this.findNode({
            className: csn+'nw'
        }), this.findNode({
            className: csn+'se'
        }), this.findNode({
            className: csn+'sw'
        }), this.findNode({
            className: csn+'ne'
        })];
        var cls;
		/**
		 *给四边加上拖拉事件，使支持窗口的大小调整
		 */
        for (var i = 0,len = a.length; i < len; i++)
        {
            a[i].mouse_down?a[i].attachEvent("onmousedown", a[i].mouse_down):0;
            cls = a[i].className.replace("windoo-sizer windoo-", "");
            a[i].style.cursor = (cls.length > 3 ? cls.split("")[0] : cls) + "-resize";
        }
    };
    _WP._removeResizeEvent = function()
    {
        var a = [this.findNode({
            className: csn+'west'
        }), this.findNode({
            className: csn+'east'
        }), this.findNode({
            className: csn+'north'
        }), this.findNode({
            className: csn+'south'
        }), this.findNode({
            className: csn+'nw'
        }), this.findNode({
            className: csn+'se'
        }), this.findNode({
            className: csn+'sw'
        }), this.findNode({
            className: csn+'ne'
        })];
		/**
		 *删除四边的删除事件
		 */
        for (var i = 0,len = a.length; i < len; i++)
        {
            a[i].mouse_down?a[i].detachEvent("onmousedown", a[i].mouse_down):0;
            a[i].style.cursor = "default";
        }
    };
	/**
	 *窗口最大化
	 */
    _WP.max = function(windowResize)
    {
		if(!this._winSize)
		{
			this._winSize = PW.Window._lastModifiedSize||this._defaultSize;
		}

        windowResize?0:this.toFront();
        this.maximized = true;
        this._removeDragEvent();
        this._removeResizeEvent();
        getObj('restore_' + this.id).style.display = '';
        getObj('max_' + this.id).style.display = 'none';
        this.setSize({
            width: this._body.offsetWidth,
            height: this._body.offsetHeight-3,
            left: '0px',
            top: '0px'
        });
		this.minimized=false;
    };
	_WP.onmin=function()
	{
	};
	/**
	 *窗口最小化
	 */
    _WP.min = function()
    {
        this.self.shadow.style.display = "none";
        this.self.win.style.display = "none";
		this.focused=false;
		this.minimized=true;
		this.maximized = false;
		this.onmin();
    };
	/**
	 *窗口恢复
	 */
    _WP.restore = function()
    {

        this._addDragEvent();
        this._addResizeEvent();
		this.self.shadow.style.display = "";
        this.self.win.style.display = "";
        getObj('restore_' + this.id).style.display = 'none';
        getObj('max_' + this.id).style.display = '';
        this.setSize(this._winSize);
		this.maximized = false;
		this.minimized=false;
    };
	_WP.onclose = function()
    {
    };
	_WP.getMaxIndex=function()
	{
		var ind=5;
		var curIndex=0;
		for (var i in PW.Window.all)
		{
			try{if(PW.Window.all[i].self&&PW.Window.all[i].self.win)
			{
				curIndex=Math.floor(PW.Window.all[i].self.win.style.zIndex);
				ind<curIndex?ind=curIndex:0;
			}}catch(e){continue;}
		}
		return ind+1;
	};
	/**
	 *关闭窗口
	 */
    _WP.close = function()
    {
        this._removeDragEvent();
        this._removeResizeEvent();
        PW.Window.remove(this.self.shadow);
        PW.Window.remove(this.self.win);
		this.onclose();
		PW.Window.all[this.uniqueID]=null;
    };
	/**
	 *重载窗口中的iframe的url
	 */
	_WP.loadIframe=function(url)
	{
	   var getIframe=this.self.win.getElementsByTagName("iframe")[0];
	   getIframe.src=url||getIframe.contentWindow.location;
	};
	/**
	 *渲染窗口组件，主入口
	 */
    _WP.render = function(container)
    {

		_WP._defaultSize={width:760,height:500,left:Math.floor(document.body.clientWidth/2-760/2)-145,top:Math.floor(document.body.clientHeight/2-500/2)-55};
        uniqueID++;
        this.uniqueID = this.uniqueID || uniqueID;
        windowIndex++;

        this._body = this.body || document.body;
        if (this.maximized)
        {
            this.width = this._body.offsetWidth-10;
            this.height = this._body.offsetHeight-10;
            this.left = 0;
            this.top = 0;
        }
		else
		{
			this.width=this.width||_WP._defaultSize.width;
			this.height=this.height||_WP._defaultSize.height;
			this.top=this.top||(_WP._defaultSize.top+uniqueID*5);
			this.left=this.left||(_WP._defaultSize.left-uniqueID*5);

		}
        var _container = container || "client";
        if (getObj('win-' + this.uniqueID))
        {
            this.self = {
                shadow: getObj('wins-' + this.uniqueID),
				title:getObj('top-title-'+this.uniqueID),
				drag:getObj('drag-'+this.uniqueID),
                win: getObj('win-' + this.uniqueID)
            };
            return this.toFront();
        }
        var win = document.createElement("DIV");

        win.className = "windoo windoo-aqua-focus windoo-aqua-ie";
        win.id = "win-" + this.uniqueID;

        this.id = "W_" + uniqueID;
		PW.Window.all[this.uniqueID]=this;
        eval(this.id + "=this");
       	var fixedParam={left:13,top:7,width:26,height:31};
        with(win.style)
        {
            cssText = "z-index:" + (this.getMaxIndex()+1) + ";POSITION: absolute; FILTER: ; WIDTH: " + this.width + "px; HEIGHT: " + (this.height - fixedParam.height) + "px; VISIBILITY: visible; OVERFLOW: hidden; TOP: " + this.top + "px; LEFT: " + this.left + "px; opacity: 1"; //300 200
            //left top:152 41
        }

        var div_shadow = document.createElement("DIV");

        this._body.appendChild(div_shadow);
        this._body.appendChild(win);
        div_shadow.className = "windoo-shadow-aqua";
        div_shadow.id = "wins-" + this.uniqueID;
        with(div_shadow.style)
        {
            cssText = "z-index:" + (this.getMaxIndex()) + ";POSITION: absolute; VISIBILITY: visible; TOP: " + (this.top - 7) + "px; LEFT: " + (this.left - 13) + "px; opacity: 1";
        }
        div_shadow.innerHTML = '\
                            <DIV class=top>\
                                <DIV class=l> \
                                </DIV>	\
                                <DIV class=r> \
                                </DIV>	 \
                                <DIV class=m> \
                                </DIV>	 \
                            </DIV>	 \
                            <DIV style="HEIGHT: ' + (this.height - 54) + 'px" id="shadow_body' + this.id + '" resize=3 class=mid> \
                                <DIV class=l>\
                                </DIV>	 \
                                <DIV class=r>	  \
                                </DIV>	   \
                                <DIV class=m>	  \
                                </DIV>	   \
                            </DIV>	\
                            <DIV class=bot>	  \
                                <DIV class=l>  \
                                </DIV>		 \
                                <DIV class=r>  \
                                </DIV>		\
                                <DIV class=m>	\
                                </DIV>	  \
                            </DIV>';
        win.innerHTML = '\
							<DIV class=windoo-frame>\
                                <DIV class="top-left windoo-drag" id="drag-' + this.id + '">\
                                    <DIV class=top-right id="top_right">\
                                        <DIV class=title id="top_title">\
                                            <DIV class=title-text id="top-title-'+this.id+'" >\
                                                <div style="width:88px;overflow:hidden; text-overflow:ellipsis;white-space:nowrap; ">' + this.title + '</div>\
                                            </DIV>\
                                        </DIV>\
                                    </DIV>\
                                </DIV>\
                                <DIV class="bot-left windoo-drag">\
                                    <DIV class=bot-right>\
                                        <DIV style="HEIGHT: ' + (this.height - 64) + 'px"  id="main_body' + this.id + '" resize=2 class=strut>\
                                            &nbsp;\
                                        </DIV>\
                                    </DIV>\
                                </DIV>\
							</DIV>\
                            <DIV style="HEIGHT: ' + (this.height - 63) + 'px" resizeY=1 class=windoo-body id="' + win.id + '-body">\
                                <TABLE border=0 style="POSITION: absolute;height:100%;width:100%; BORDER-COLLAPSE: collapse;TOP: 0px; LEFT: 0px">\
                                    <TBODY>\
                                        <TR>\
                                            <TD style="POSITION: relative;width:100%;word-break:break-all;">\
                                                <DIV style="height:100%;background:#E6F2FC;width:100%;overflow:auto;">\
                                                    ' + this.html + '\
                                                </DIV>\
                                            </TD>\
                                        </TR>\
                                    </TBODY>\
                                </TABLE>\
                            </DIV>\
							<A class="windoo-button windoo-close" style="left:131px;background:url(js/desktop/images/go.gif) 10px 3px no-repeat" hidefocus title=前进 href="javascript:;" onclick="history.go(1);">\
                                前进前进前进前进前进\
                            </A>\
								<A class="windoo-button windoo-close" style="left:102px;background:url(js/desktop/images/back.gif) 10px 3px no-repeat" hidefocus title=后退 href="javascript:;" onclick="history.go(-1);">\
                                后退\
                            </A>\
								<A class="windoo-button windoo-close" style="left:170px;background:url(js/desktop/images/refresh.gif) 5px 3px no-repeat" hidefocus title=刷新 href="javascript:;" onclick="PW.Window.all[\''+this.uniqueID+'\'].loadIframe();">\
							刷新</A>\
                            <A class="windoo-button windoo-close" hidefocus title=关闭 href="javascript:;" onclick="' + this.id + '.close();return false;">\
                                x\
                            </A>\
                            <A id="max_' + this.id + '" class="windoo-button windoo-maximize" hidefocus title=最大化 href="javascript:;" onclick="' + this.id + '.max();return false;">\
                                x\
                            </A>\
                            <A class="windoo-button windoo-minimize" hidefocus title=最小化 href="javascript:;" onclick="' + this.id + '.min();return false;">\
                                x\
                            </A>\
                            <A id="restore_' + this.id + '" class="windoo-button windoo-restore" hidefocus title=向下还原 href="javascript:;" onclick="' + this.id + '.restore();return false;" style="display:none;">\
                                x\
                            </A>\
                            <DIV class="windoo-sizer windoo-east">\
                            </DIV>\
                            <DIV class="windoo-sizer windoo-west">\
                            </DIV>\
                            <DIV class="windoo-sizer windoo-north">\
                            </DIV>\
                            <DIV class="windoo-sizer windoo-south">\
                            </DIV>\
                            <DIV class="windoo-sizer windoo-nw">\
                            </DIV>\
                            <DIV class="windoo-sizer windoo-ne">\
                            </DIV>\
                            <DIV class="windoo-sizer windoo-sw">\
                            </DIV>\
                            <DIV class="windoo-sizer windoo-se">\
                            </DIV>';
		 this.self = {
            shadow: div_shadow,
			title:getObj('top-title-'+this.id),
			drag:getObj('drag-'+this.id),
            win: win
        };
        FOCUSWINDOW ? FOCUSWINDOW._blur() : 0;
        FOCUSWINDOW = this;
		if(!this.maximized)
		{
			this._winSize=this._getWinSize();
		}
		this.focused=true;
        var _this = this;
		setTimeout(function(){_this.onfocus();},100);
		this.self.drag.onselectstart=function(){return false;};
		this.self.drag.onselect=function(){return false;};
		this.self.drag.ondblclick=function(){_this.maximized?_this.restore():_this.max()};
        win.onmousedown = function()
        {
            _this._focus(_this.self)
        };
        var allIframes = getObj(win.id + '-body').getElementsByTagName("iframe");
        var cwin;
		var mousedownFn=function(ev,win)
                {
					if(!_this.focused)
					{
						try{var isScroll=ev.clientX>win.document.body.clientWidth-10;}catch(e){}
						setTimeout(function()
						{
							//if(isScroll)return;
							_this._focus(_this.self);
							_this.toFront();
						},
						10);
					}
					else
					{
						for(var i in PW.Menu.all){PW.Menu.all[i]?PW.Menu.all[i].remove?PW.Menu.all[i].remove():0:0;}
					}
					//!IE?event.cancelBubble=true:0;
                };
        for (var i = 0,len = allIframes.length; i < len; i++)
        {
            cwin = allIframes[i].contentWindow;
            if (cwin.document)
            {
                cwin.document.onmousedown =function(ev){mousedownFn(ev||event,cwin)};
            }
			var onloadFn=function()
			{

				try{setTimeout(function(){cwin.focus();},1000);}catch(e){}
				return function()
				{
					cwin.document.onmousedown =function(ev){mousedownFn(ev||event,cwin)};
				};
			};
			onloadFn=onloadFn.call(cwin);
            allIframes[i].attachEvent("onload",onloadFn);

        }

		/**
		 *增加窗口的拖放事件。使窗口可拖动
		 */
        drag.DD({
            moving: function(obj)
            {
                div_shadow.style.left = Number(obj.style.left.replace("px", "")) - 13 + 'px';
                div_shadow.style.top = Number(obj.style.top.replace("px", "")) - 7 + 'px';

                PW.Window._lastModifiedSize=_this._winSize = _this._getWinSize();
            },
            drag_obj: getObj('drag-' + this.id),
            move_obj: win,
            body_obj: getObj(win.id + '-body'),
            onfocus: function()
            {
				_this._focus(_this.self) ;
				allIframes[0].style.visibility="hidden";


            },
			feedback:function(){allIframes[0].style.visibility="visible";},
            move_range: _container	//传递一个窗口的拖放容器，使之仅可在此容器内拖动。
        });
//		win.autoHidden=this.autoHidden;
		
        var onResize = function(size)
        {
            div_shadow.style.width = win.offsetWidth + fixedParam.width + 'px';
            div_shadow.style.height = win.offsetHeight + fixedParam.height + 'px';
            div_shadow.style.left = Number(win.style.left.replace("px", "")) - 13+"px";
            div_shadow.style.top = Number(win.style.top.replace("px", "")) - 7+"px";
            getObj('shadow_body' + _this.id).style.height = win.offsetHeight - 23 + 'px';
            getObj('main_body' + _this.id).style.width = div_shadow.offsetWidth - 63 + 'px';

            getObj('main_body' + _this.id).style.height = div_shadow.offsetHeight - 23 + 'px';
            _this.onresize ? _this.onresize(win) : 0;
            _this._winSize = _this._getWinSize();
        };
		var direct=['east','west','south','north','nw','ne','se','sw'];
		/**
		 *初始化四边的事件，使之可调整窗口的大小
		 */
		for (var i=0,len=direct.length; i<len; i++)
		{
			PW.Resize({
				direct: direct[i].length>2?direct[i].substr(0,1):direct[i],
				body_obj: getObj(win.id + '-body'),
				onResize: onResize,
				dragObj: this.findNode({
					className: csn+direct[i]
				}),
				resizeObj: win,
				body: this.body,
				min: [200, 100]
			});
		}
		if (this.maximized)
        {
            window.attachEvent("onresize",function()
            {
				_this.maximized?_this.max(true):0;
            });
			this.max();
            return;
        }
		else
		{
			this.restore();
		}
    };

} ();