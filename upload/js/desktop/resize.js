/*<![CDATA[*/

/**
 * 调整对象的尺寸.
 * 
 */
~
function()
{
	var Doc=document;
    var IE = Doc.all;
    var resize_down = false;
	
    var getObj = function(s)
    {
        return Doc.getElementById(s);
    };
    var setSize = function(v)
    {
        if (v < 0)
        {
            return 0;
        }
        return v;
    };
    PW.Resize = function(jsonArg)
    {
        var ROOT = jsonArg.body || Doc.body;
        var dragobj = jsonArg.dragObj,
        resizeobj = jsonArg.resizeObj,
        maxRange = jsonArg.max,
        minRange = jsonArg.min;
        var max = maxRange ? [maxRange[0], maxRange[1]] : [];
        var min = minRange ? [minRange[0], minRange[1]] : [];
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
        var ghost = resizeobj.cloneNode("");
        ROOT.appendChild(ghost);
        ghost.style.display = "none";
        ghost.id = "";
        var _wh, xy;
        var mm = function(evt)
        {
            if (resize_down)
            {
                jsonArg.body_obj ? jsonArg.body_obj.style.visibility = "hidden": 0;
				
				var evt=evt||event;
				if(evt.stopPropagation){
					evt.stopPropagation();
				}else{
					evt.cancelBubble = true;
				}
                if (ROOT.setCapture)
                {
                    ROOT.setCapture();
                } else if (window.captureEvents)
                {
                    window.captureEvents(Event.MOUSEMOVE | Event.MOUSEUP);
                }
                var resizeX = function(ev, abs)
                {
                    ghost.style.width = setSize(_wh.width + (abs || 1) * (ev.clientX - xy.clientX) - (IE ? 1 : 4)) + 'px';
                };
                var resizeY = function(ev, abs)
                {
                    ghost.style.height = setSize(_wh.height + (abs || 1) * (ev.clientY - xy.clientY) - (IE ? 1 : 4)) + 'px';
                };
                var moveTop = function(ev)
                {
                    if (ghost.offsetHeight > min[1])
                    {
                        ghost.style.top = ev.clientY - ROOT.offsetTop - (IE ? 2 : 1) + "px";
                    }
                };
                var moveLeft = function(ev)
                {
                    if (ghost.offsetWidth > min[0])
                    {
                        ghost.style.left = ev.clientX - ROOT.offsetLeft - 2 + "px";
                    }

                };
                jsonArg.direct = jsonArg.direct.replace("east", "e").replace("west", "w").replace("north", "n").replace("south", "s");
                ROOT.style.cursor = jsonArg.direct + "-resize";
                switch (jsonArg.direct)
                {
                case "e":
                    resizeX(evt);
                    break;
                case "w":
                    resizeX(evt, -1);
                    moveLeft(evt);
                    break;
                case "n":
                    resizeY(evt, -1);
                    moveTop(evt);
                    break;
                case "s":
                    resizeY(evt);
                    break;
                case "ne":
                    resizeX(evt);
                    resizeY(evt, -1);
                    moveTop(evt);
                    break;
                case "se":
                    resizeX(evt);
                    resizeY(evt);
                    break;
                case "sw":
                    resizeX(evt, -1);
                    resizeY(evt);
                    moveLeft(evt);
                    break;
                case "nw":
                    resizeX(evt, -1);
                    resizeY(evt, -1);
                    moveLeft(evt);
                    moveTop(evt);
                    break;
                }
                if (max[0])
                {
                    if (ghost.offsetWidth > max[0])
                    {
                        ghost.style.width = max[0] + "px";
                    }
                }
                if (max[1])
                {
                    if (ghost.offsetHeight > max[1])
                    {
                        ghost.style.height = max[1] + "px";
                    }
                }
                if (min[0])
                {
                    if (ghost.offsetWidth < min[0])
                    {
                        ghost.style.width = min[0] + "px";
                    }
                }
                if (min[1])
                {
                    if (ghost.offsetHeight < min[1])
                    {
                        ghost.style.height = min[1] + "px";
                    }
                }
            }
        };
        var mu = function()
        {
            if (resize_down)
            {
                jsonArg.body_obj ? jsonArg.body_obj.style.visibility = "visible": 0;
                PW.Resize.setSize({
                    resizeobj: resizeobj,
                    width: ghost.offsetWidth,
                    height: ghost.offsetHeight,
                    left: ghost.style.left,
                    top: ghost.style.top,
                    onResize: jsonArg.onResize
                });

            }
			getObj('cover_drag_div').style.display = "none";
            ROOT.style.cursor = "default";
            ghost.style.display = "none";
            resize_down = false;
            if (ROOT.releaseCapture)
            {
                ROOT.releaseCapture();
            } else if (window.captureEvents)
            {
                window.captureEvents(Event.MOUSEMOVE | Event.MOUSEUP);
            }
            Doc.detachEvent("onmousemove", mm);
            Doc.detachEvent("onmouseup", mu);
        };
        dragobj.attachEvent("onmousedown", dragobj.mouse_down = function(evt)
        { 
			var evt=evt||event;
			! maxRange ? max = [ROOT.offsetWidth - 20, ROOT.offsetHeight - 20] : 0;
            with(ghost.style)
            {
                var rs = resizeobj.style;
                display = "";
                width = rs.width;
                height = rs.height;
                left = rs.left;
                top = rs.top;
                background = "#DEEDFE";
                border = "1px solid #98ABC1";
                IE ? filter = "alpha(opacity=50)": opacity = 0.5;
            }
			setTimeout(function(){window.drag?drag.setTop(ghost)+(cover.style.zIndex = parseInt(ghost.style.zIndex) - 1):0;},50);
			getObj('cover_drag_div').style.display = "";
            _wh = {
                width: ghost.offsetWidth,
                height: ghost.offsetHeight
            };
            xy = {
                clientX: evt.clientX,
                clientY: evt.clientY
            };
            resize_down = true;
            Doc.detachEvent("onmousemove", mm);
            Doc.attachEvent("onmousemove", mm);

            Doc.detachEvent("onmouseup", mu);
            Doc.attachEvent("onmouseup", mu);
        });

    };
    PW.Resize.setSize = function(jsonArg)
    {
        var resizeobj = jsonArg.resizeobj;
        jsonArg.width = jsonArg.width || resizeobj.offsetWidth;
        jsonArg.height = jsonArg.height || resizeobj.offsetHeight;
        var ae = resizeobj.getElementsByTagName("*");
        var resize_childs = [];
        var resize_Y = [];
        var resize_X = [];
        for (var i = 0; i < ae.length; i++)
        {
            if (ae[i].getAttribute("resize"))
            {
                resize_childs.push([ae[i].getAttribute("resize"), ae[i]]);
            }
            if (ae[i].getAttribute("resizeX"))
            {
                resize_X.push([ae[i].getAttribute("resizeX"), ae[i]]);
            }
            if (ae[i].getAttribute("resizeY"))
            {
                resize_Y.push([ae[i].getAttribute("resizeY"), ae[i]]);
            }
        }
        resize_childs.sort();
        resize_X.sort();
        resize_Y.sort();
        var vX = valueX = jsonArg.width - resizeobj.offsetWidth;
        if (',w,sw,nw,'.indexOf("," + jsonArg.direct + ",") != -1)
        {
            valueX = Math.abs(valueX);
        }
        var vY = valueY = jsonArg.height - resizeobj.offsetHeight;
        if (',n,ne,nw,'.indexOf("," + jsonArg.direct + ",") != -1)
        {
            valueY = Math.abs(valueY);
        }
        resizeobj.style.width = jsonArg.width + 'px';
        resizeobj.style.height = jsonArg.height + 'px';
        jsonArg.left ? resizeobj.style.left = jsonArg.left: 0;
        jsonArg.top ? resizeobj.style.top = jsonArg.top: 0;

        for (var i = 0,len = resize_childs.length; i < len; i++)
        {
            resize_childs[i][1].style.width = setSize(resize_childs[i][1].offsetWidth + valueX) + 'px';
            resize_childs[i][1].style.height = setSize(resize_childs[i][1].offsetHeight + valueY) + 'px';
        }
        for (var i = 0,len = resize_X.length; i < len; i++)
        {
            resize_X[i][1].style.width = setSize(resize_X[i][1].offsetWidth + valueX) + 'px';
        }
        for (var i = 0,len = resize_Y.length; i < len; i++)
        {
            resize_Y[i][1].style.height = setSize(resize_Y[i][1].offsetHeight + valueY) + 'px';
        }
        jsonArg.onResize ? jsonArg.onResize({
            height: vY,
            width: vX
        }) : 0;
    };
} ();