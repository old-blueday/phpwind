/**
 * 拖放对象方法库
 *使用方法：drag.DD(JSONArg);
 *JSONArg 为一个Object，如：{drag_obj:getObj('xx'),move_obj:getObj('xx2'),move_range:[],ifreturn:false,hiddenData:true,receivers:'',feedback:function(){},moving:function(){}}
 *			drag_obj 拖动的对象
            move_obj 移动的对象
            _move_range 移动范围
            ifreturn 是否不匹配到自动返回原始位置
            hiddenData 拖动的过程是否隐藏主体数据
            receivers 设置要接收的容器的id的正则式（不推荐使用）
            feedback 鼠标弹起时调用
            moving 拖动过程中调用
 */
~
function()
{
	/**
	 *加载一个兼容各个浏览器的js包。之后就可以用ie的方式来写事件，而不用去考虑兼容性问题。
	 */
    var IE = document.all;
    IE ? document.execCommand('BackgroundImageCache', false, true) : 0;
    var getObj = function(s)
    {
        return document.getElementById(s);
    };
    var ROOT = document.documentElement;
    var MOUSEDOWN = false;
    drag = {
        match_obj: null,
        x: 0,
        y: 0,
        tx: 0,
        ty: 0,
        compare_x: 0,
        compare_obj: null,
        _hiddenLeft: {},
        _hiddenRight: {},
        cssText: {},
        posxy: function(obj, relative)
        {
            var e = [0, 0];
            el = obj;
            while (el)
            {
                if (!el.parentNode || !el.parentNode.style)
                {
                    break;
                }
                if (!relative && el.parentNode.style.position == "relative") break;

                e[0] = e[0] + el.offsetLeft;
                e[1] = e[1] + el.offsetTop;
                el = el.offsetParent;
            }
            return e;
        },
        boo: function(obj)
        {
            return getObj(obj.id + '-body');
        },
        setTop: function(obj)
        {
            var a = document.getElementsByTagName("*");
            var max = 0;
            for (var i = 0,len = a.length; i < len; i++)
            {
                try{max = parseInt(a[i].style.zIndex) > max ? parseInt(a[i].style.zIndex): max;}catch(e){alert(e.message||e)}
            }
            obj.style.zIndex = parseInt(max) - 1 + 2;
        },
        DD: function(jsonArg)
        {
            ROOT = document.body;
            var drag_obj = jsonArg.drag_obj,
            move_obj = jsonArg.move_obj,
            _move_range = move_range = jsonArg.move_range,
            ifreturn = jsonArg.ifreturn,
            hiddenData = jsonArg.hiddenData,
            receivers = jsonArg.receivers,
            feedback = jsonArg.feedback,
            moving = jsonArg.moving;
            if (!getObj('cover_drag_div'))
            {
                var cover = document.createElement("DIV");
                with(cover.style)
                {
                    position = "absolute";
                    display = "none";
                    top = ROOT.scrollTop + "px";
                    left = ROOT.scrollLeft + "px";
                    IE ? filter = "alpha(opacity=0)": 0;
                    IE ? background = "#FFF": 0;
                    width = "100%";
                    height = "100%";
                }
                cover.id = "cover_drag_div";
                ROOT.appendChild(cover);
            }

            var cover = getObj('cover_drag_div');

            var hidenObject = jsonArg.body_obj || 0;
            move_obj.style.position = "absolute";
            var id = move_obj.id.replace("dialog", "");

            drag._hiddenLeft[id] = false;
            drag._hiddenRight[id] = false;
            try
            {
                drag_obj.firstChild.onmousedown = function()
                {
                    return false;
                }
            } catch(e)
            {};
            MOUSEDOWN = false;
            try
            {
                var parent_node = move_obj.parentNode || ROOT;
            } catch(e)
            {}
            var beforeresizewin = [parent_node.offsetWidth, parent_node.offsetHeight];
			var timerValue=10;
            move_obj.mouse_down = function(a)
            {
				 if (!a)
                {
                    a = event;
                }
				if(a.button==2) return;
                
                move_obj.bg = move_obj.style.backgroundColor;
				if (!IE&&move_obj.style.backgroundColor != "transparent")
                    {
                     //   move_obj.style.backgroundColor = "transparent";
                        //move_obj.style.opacity = 0.5;
                    }
                cover.style.zIndex = parseInt(move_obj.style.zIndex) - 1;
				jsonArg.onfocus ? jsonArg.onfocus() : 0;
                if (move_range == "client")
                {
                    var dd = ROOT;
                    move_range = [0, dd.clientWidth - move_obj.offsetWidth - 20, 0, dd.scrollHeight - move_obj.offsetHeight - 10]
                }
                if (_move_range.outerHTML)
                {
                    move_range = [0, _move_range.offsetWidth - move_obj.offsetWidth, 0, _move_range.scrollHeight - move_obj.offsetHeight];
                }
                drag.setTop(move_obj);
                getObj('cover_drag_div').style.display = "";
                beforeresizewin = [parent_node.offsetWidth, parent_node.offsetHeight];
                MOUSEDOWN = true;
                var docm = document;

                drag.compare_obj = drag_obj;
               
                drag.x = (a.layerX ? a.layerX: a.offsetX) + parent_node.offsetLeft;
                drag.y = (a.layerY ? a.layerY: a.offsetY) + parent_node.offsetTop;
                if (drag_obj != move_obj)
                {
                    drag.x = drag.x + a.srcElement.offsetLeft;
                    drag.y = drag.y + drag_obj.offsetTop;
                }
                if (a.srcElement.tagName)
                {
                    var tag = /(input|textarea|select)/i.test(a.srcElement.tagName.toLowerCase());
                    if (tag)
                    {
                        try
                        {
                            a.srcElement.focus();
                        } catch(e)
                        {}
                        return;
                    }
                }
                try
                {
                    drag.x0 = drag.posxy(move_obj)[0];
                    drag.y0 = drag.posxy(move_obj)[1];
                    if (ROOT.setCapture)
                    {
                        ROOT.setCapture();
                    } else if (window.captureEvents)
                    {
                        window.captureEvents(Event.MOUSEMOVE | Event.MOUSEUP);
                    }
                } catch(e)
                {}
                try
                {
                    var relative = move_obj.parentNode.style.position == "relative" ? drag.posxy(move_obj.parentNode) : [0, 0];
                } catch(e)
                {}
                var ps = drag.posxy(move_obj.parentNode);
                var topPixel = 0;

                docm.onmousemove = function(a)
                {
                    if (IE&&move_obj.style.backgroundColor != "transparent")
                    {
                      //  move_obj.style.backgroundColor = "transparent";
                       // IE ? move_obj.style.filter = "alpha(opacity=50)": move_obj.style.opacity = 0.5;
                    }
                    if (!MOUSEDOWN) return;
                    if (hidenObject && hidenObject.style.visibility != "hidden")
                    {
                        //hidenObject.style.visibility = "hidden";
                    }
                    drag._hiddenLeft[id] = false;
                    drag._hiddenRight[id] = false;

                    a = a || event;
                    if (!a.pageX && IE)
                    {
                        a.pageX = a.clientX;
                    }
                    if (!a.pageY && IE)
                    {
                        a.pageY = a.clientY;
                    }
                    if (Math.abs(a.pageX - drag.x) < 5)
                    {
                        return false;
                    }

                    drag.tx = a.pageX - drag.x + ROOT.scrollLeft - (IE ? 9 : 0);
                    drag.ty = a.pageY - drag.y + (IE ? ROOT.scrollTop: 0) - topPixel;
                    if (move_range != null)
                    {
                        move_obj.style.left = (drag.tx < move_range[0] ? move_range[0] : drag.tx > move_range[1] ? move_range[1] : drag.tx) + "px";
                        move_obj.style.top = (drag.ty < move_range[2] ? move_range[2] : drag.ty > move_range[3] ? move_range[3] : drag.ty) + "px";
                    } else
                    {
                        move_obj.style.left = drag.tx + "px";
                        move_obj.style.top = drag.ty + "px";
                    }
                    var arr_m = [a.pageX, a.pageY, a.offsetX || a.layerX, a.offsetY || a.layerY];
                    if (receivers)
                    {
                        try
                        {
                            drag.match_obj = drag.findMatch(receivers, {x:a.clientX,y:a.clientY}, move_obj); //drag.getBestMatch(receivers,move_obj,arr_m);
                            drag.matchedObj = drag.match_obj ? drag.match_obj: null;
                        } catch(e)
                        {}
                        if (Math.abs(a.pageX - drag.lastX) > 5 || Math.abs(a.pageY - drag.lastY) > 5)
                        {

                            moving ? moving(drag.matchedObj, move_obj, a) : "";
                            drag.lastX = a.pageX;
                            drag.lastY = a.pageY;
                        }
                    } else
                    {
                        moving ? moving(move_obj, a) : "";
                    }
                    if (hiddenData != "swap")
                    {
                        try
                        {
                            drag.compare_obj = drag.match_obj;
                        } catch(e)
                        {}
                    }
                };
                docm.onmouseup = function(evt)
                {
                    var evt = evt || event;
                    MOUSEDOWN = false;
					var clearCover=function()
					{
						//IE ? move_obj.style.filter = "": move_obj.style.opacity = "";
						//move_obj.style.backgroundColor = move_obj.bg;
					};
					clearCover();
                    
					
                    if (hidenObject)
                    {
                        hidenObject.style.visibility = "visible";
                    }
                    if (ROOT.releaseCapture)
                    {
                        ROOT.releaseCapture();
                    } else if (window.captureEvents)
                    {
                        window.captureEvents(Event.MOUSEMOVE | Event.MOUSEUP);
                    }
                    docm.onmousedown = null;
                    docm.onmousemove = null;
                    docm.onmouseup = null;
                    getObj('cover_drag_div').style.display = "none";

                    if (ifreturn)
                    {
                        if (!drag.match_obj)
                        {
                            move_obj.style.left = drag.x0 + "px";
                            move_obj.style.top = drag.y0 + "px";
                        } else
                        {
                            if (hiddenData == "swap")
                            {
                                move_obj.style.left = drag.posxy(drag.match_obj)[0] + "px";
                                move_obj.style.top = drag.posxy(drag.match_obj)[1] + "px";
                                drag.match_obj.style.left = drag.x0 + "px";
                                drag.match_obj.style.top = drag.y0 + "px";
                            } else
                            {
                                move_obj.style.left = drag.posxy(drag.match_obj)[0] + "px";
                                move_obj.style.top = drag.posxy(drag.match_obj)[1] + "px";
                            }
                        }
                    }
                    feedback ? feedback(move_obj, drag.match_obj, evt) : "";
                    drag.match_obj = null;
                    if (move_obj.offsetLeft < 0)
                    {
                        if (move_obj.autoHidden)
                        {
                            move_obj.style.left = 0 - move_obj.offsetWidth + 15 + "px";
                        }

                        drag._hiddenLeft[id] = true;
                        move_obj.onmouseover = function()
                        {
                            if (drag._hiddenLeft[id])
                            {
                                clearTimeout(drag._tm);
                                if (move_obj.autoHidden)
                                {
                                    move_obj.style.left = 0 + "px";
                                }
                            }
                        };
                        move_obj.onmouseout = function()
                        {
                            if (drag._hiddenLeft[id])
                            {
                                clearTimeout(drag._tm); ! move_obj.autoHidden ? "": drag._tm = setTimeout(function(){
									move_obj.style.left=0-move_obj.offsetWidth+15+'px';	
								}, 300);
                            }
                        }
                    } else
                    {
                        drag._hiddenLeft[id] = false;
                        clearTimeout(drag._tm);
                    }
                    if (move_obj.offsetLeft + move_obj.offsetWidth > parent_node.clientWidth)
                    { ! move_obj.autoHidden ? "": move_obj.style.left = parent_node.clientWidth + parent_node.scrollLeft - 15 + "px";
                        drag._hiddenRight[id] = true;
                        move_obj.onmouseover = function()
                        {
                            if (drag._hiddenRight[id])
                            {
                                clearTimeout(drag._tm); ! move_obj.autoHidden ? "": move_obj.style.left = parent_node.clientWidth + parent_node.scrollLeft - move_obj.offsetWidth - 15 + "px";
                            }
                        };
                        move_obj.onmouseout = function()
                        {
                            if (drag._hiddenRight[id])
                            { ! move_obj.autoHidden ? "": drag._tm = setTimeout(function(){
									move_obj.style.left=parent_node.clientWidth+parent_node.scrollLeft-15+'px';
								}, 300);
                            }
                        }
                    } else
                    {
                        drag._hiddenRight[id] = false;
                        clearTimeout(drag._tm);
                    }

                };
            };
            drag_obj.attachEvent("onmousedown", move_obj.mouse_down);
        },
        lastX: 0,
        lastY: 0,
        findMatch: function(o, evt, t)
        {
            var found = null;
            if (Math.abs(evt.x - this.lastX) > 10 || Math.abs(evt.y - this.lastY) > 10)
            {

                var max_distance = 100000000;
                for (var j = 0; j < o.length; j++)
                {
                    var ele = o[j].obj;
                    var x_x = evt.x - o[j].pos[0];
                    var y_y = evt.y - o[j].pos[1];
                    var distance = (Math.pow(x_x, 2) + Math.pow(y_y, 2));
                    if (ele == t)
                    {
                        continue;
                    }
                    if (isNaN(distance))
                    {
                        continue;
                    }
                    if (distance < max_distance)
                    {
                        max_distance = distance;
                        found = ele;
                    }
                }
                this.lastX = evt.x;
                this.lastY = evt.y;
            }
            return found;
        },
        getBestMatch: function(reg, srcElement, pos)
        {
            var objj = srcElement;
            var order;
            if (pos[0] > this.compare_x)
            {
                order = "r";
            } else
            {
                order = "l";
            }
            this.compare_x = pos[0];
            var reg = reg;
            var match_obj = null;
            var hx = objj.offsetWidth;
            var vy = objj.offsetHeight;
            var mx = parseInt(pos[0]);
            var my = parseInt(pos[1]);
            var ox = parseInt(pos[2]);
            var oy = parseInt(pos[3]);
            var lx = mx - ox - 2;
            var rx = lx + hx + 2;
            var lyu = my - oy;
            var lyd = lyu + vy;
            var fit = 5;
            switch (order)
            {
            case "l":
                for (var i = lyu; i <= lyd; i = i + fit)
                {
                    if (reg.test(document.elementFromPoint(lx, i).id))
                    {
                        match_obj = document.elementFromPoint(lx, i);
                    }
                }
                if (match_obj == null)
                {
                    for (var j = lyu; j <= lyd; j = j + fit)
                    {
                        if (reg.test(document.elementFromPoint(rx, j).id))
                        {
                            match_obj = document.elementFromPoint(rx, j);
                        }
                    }
                }
                break;
            case "r":
                for (var j = lyu; j <= lyd; j = j + fit)
                {
                    if (reg.test(document.elementFromPoint(rx, j).id))
                    {
                        match_obj = document.elementFromPoint(rx, j);
                    }
                }
                if (match_obj == null)
                {
                    for (var i = lyu; i <= lyd; i = i + fit)
                    {
                        if (reg.test(document.elementFromPoint(lx, i).id))
                        {
                            match_obj = document.elementFromPoint(lx, i);
                        }
                    }
                }
                break;
            default:
            }
            try
            {
                getObj("track").innerHTML = match_obj.id;
            } catch(e)
            {}
            return match_obj;
        }
    };
} ();