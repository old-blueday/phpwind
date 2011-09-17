/**
 *include Compatibility.js core.js panel.js inherit from panel
 *开始菜单中的面板,继承自PW.Panel类
 *使用方法：new PW.WPanel().render()
 */
PW.WPanel = PW.Panel.extend({
    cssText: "",
	direct:"down"
});
PW.WPanel.prototype.remove = function()
{
    var a = document.createElement("DIV");
    a.appendChild(this.element);
    a.innerHTML = "";
    a = null;
};
PW.WPanel.prototype.onafterhide = function()
{
    this.remove();
};
PW.WPanel.prototype.init = function()
{
    var _this = this;

    this.ROOT.attachEvent("onmousedown",
    function(evt)
    {
		var e=evt||window.event;
		var er=e.target||e.srcElement;
		if(er.nodeType!=1)
		{
			 er=er.parentNode;
		}
        if (!er.getAttribute("t"))
        {
            _this.remove();
			try
			{
				if(er!=getObj("startMenu")&&er!=getObj("startPanelImg"))
				{
				STartMenu.remove();
				}

			}
			catch (e)
			{
			}
        }
    });
};
PW.WPanel.prototype.setHTML = function(html)
{
    this.element.innerHTML = html;
    return this;
};
PW.WPanel.prototype.onclick = function(fn)
{
	var nods=arguments;
	var nodes;
	var el=this.element;
	for (var i=1,len=nods.length; i<len; i++)
	{
		nodes=nods[i].split(".");
		for (var j=0,lenj=nodes.length; j<lenj; j++)
		{
			el=el[nodes[j]];
		}
	}
    var a = nods.length>1?el.childNodes:this.element.childNodes;
    var _this = this;
    for (var i = 0,len = a.length; i < len; i++)
    {
        if (a[i].nodeType == 1)
        {
            a[i].onclick = function()
            {
                fn.call({
                    id: this.id.replace('-shortcut', ''),
                    name: this.innerText,
                    url: this.getAttribute("url")
                });
                _this.remove();
            };

        }
    }
};