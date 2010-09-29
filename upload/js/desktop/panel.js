/**
 *include core.js
 *
 */
~
function()
{
	var IE6=navigator.userAgent.indexOf("MSIE 7.0")==-1&&navigator.userAgent.indexOf("MSIE 8.0")==-1&&navigator.userAgent.indexOf("MSIE 6.0")>0;
    PW.Panel = baseClass.extend();
    PW.Panel.prototype = {
		cssText:'',
        getPos: function(d)
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
        },
		onafterhide:function()
		{
		},
        hide: function()
        {
            this.element.style.display = "none";
			this.onafterhide();
        },
        show: function()
        {
            this.element.style.display = "";
        },
        toggle: function()
        {
            if (!this.element) return;
            if (this.element.style.display == "")
            {
                this.element.style.display = "none";
            } else
            {
                this.element.style.display = "";
            }
        },
        appendChild: function(obj)
        {
            this.element.appendChild(obj.element);
            return this;
        },
		insertBefore: function(newobj,referobj)
        {
            this.element.insertBefore(newobj.element,referobj.element);
            return this;
        },
		insertAfter: function(newobj,referobj)
        {
            this.element.insertBefore(newobj.element,referobj.element.nextSibling);
            return this;
        },
		onclick:function()
		{
		},
        init: function()
        {},
        render: function(obj)
        {
            this.ROOT = this.ROOT || document.body;
            this.body = this.body || document.body;
            var div = document.createElement("DIV");
            div.id = "panel_" + this.id;
            with(div.style)
            {
				cssText=this.cssText;
                this.width?width = this.width + "px":0;
                this.height?height = this.height + "px":0;
                position = "absolute";
            }

            this.ROOT.appendChild(div);
            this.element = div;
            this.init();
            var xy = this.getPos(obj);
            var x = xy[0];
            var y = xy[1] - div.offsetHeight;
            if (xy[0] + div.offsetWidth > this.ROOT.clientWidth)
            {
                x = this.ROOT.clientWidth - div.offsetWidth+16;
            }
            this.direct = this.direct || "up";
            if (this.direct == "up")
            {
                if (y < 0)
                {
                    y = 0;
                }
            }
            if (this.direct == "down")
            {
                var y = xy[1] + obj.offsetHeight;
                if (y > this.ROOT.offsetHeight)
                {
                    y = xy[1] - div.offsetHeight;
                }
            }
            div.style.left = x + this.ROOT.offsetLeft-23 + "px";
			if(this.direct=="up")
			{
				div.style.top = -1000 + "px";
				setTimeout(function()
				{
					div.style.top = y - div.offsetHeight+(document.all?20:2)+ "px";
				}
				,100);
			}
			else
			{
				//var fixtop = IE6 ? 60 : 0;/*兼容性*/
				//fixtop = IE ? 60 : 0;/*兼容性*/
				fixtop = IE ? 86 : 0;/*兼容性*/
				div.style.top = y + this.ROOT.offsetTop - fixtop + "px";
			}
            return this;
        }
    };
} ();