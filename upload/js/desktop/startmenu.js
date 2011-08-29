/**
 *inherit from WPanel
 *开始菜单。
 *使用方法：new PW.StartMenu().render();
 */

 	var startPanelShow;
~
function()
{
	var IE6=navigator.userAgent.indexOf("MSIE 7.0")==-1&&navigator.userAgent.indexOf("MSIE 8.0")==-1&&navigator.userAgent.indexOf("MSIE 6.0")>0;
	var getObj = function(s)
    {
        return document.getElementById(s);
    };
	PW.StartMenu=baseClass.extend(
	{
	  items:[],
	  //module:"{name}<f>f(){if({this}=='-'){return'<div  class=\"menu-sep-start "+(IE6?"fixedPadding":"")+"\">&nbsp;</div>'}else{return'<li url=\"{url}\" id=\"{id}-shortcut\"  '+({this}.disabled?'  style=color:#ccc; onfocus=\"return false;\" onmousedown=\"this.onclick=null;this.onmousedown=null;this.onmouseup=null;event.cancelBubble=true;return false;\" disabled=true ':'')+'  class=\"start_li "+(IE6?"fixedPadding":"")+"\"><a href=\"#\" '+({this}.disabled?' style=color:#ccc; onmousedown=\"return false;\" disabled':'')+'  class=\"menu_item\">{name}</a></li>'}}</f>"
	  module:"{name}<f>f(){if({this}=='-'){return''}else{return'<li url=\"{url}\" id=\"{id}-shortcut\"  '+({this}.disabled?'  style=color:#ccc; onfocus=\"return false;\" onmousedown=\"this.onclick=null;this.onmousedown=null;this.onmouseup=null;event.cancelBubble=true;return false;\" disabled=true ':'')+'  class=\"start_li "+(IE6?"fixedPadding":"")+"\"><a href=\"#\" '+({this}.disabled?' style=color:#ccc; onmousedown=\"return false;\" disabled':'')+'  class=\"menu_item\">{name}</a></li>'}}</f>"
	  // module:'<li url="{url}" id="{id}-shortcut"><a>{name}</a></li>'
	});

    PW.StartMenu.prototype.render = function()
    {
        var _this = this;
		this.direct=this.direct||"up";
		PW.Menu.all["startMenu"]=this;
		getObj('startMenu').onmousedown=function(){event.cancelBubble=true;};
        getObj('startMenu').onclick = function()
        {
        	if(getObj('startPP')){
        		startPanelShow.remove();
				startPanelShow=0;
				getObj('startMenu').clicked=false;
        		return false;
        	}
        	killMenu();/*删除左侧菜单*/
        	PW.setCurrent();/*设置当前*/
			if(getObj('startPP')&&getObj('startPP').style.display=='')
			{
				startPanelShow.remove();
				startPanelShow=0;
				getObj('startMenu').clicked=false;
				return
			}
            var p = new PW.WPanel({
                width: 173,
				direct:_this.direct
			});

			getObj('startMenu').clicked=true;
			//var topFix=0;
			//var hat="<div class=footStartPanel style='font-size:1px;line-height:16px;"+(IE6?"margin-top:"+topFix+";width:100%":"")+"'>&nbsp;</div>";
			//var upDown={up:[hat,""],down:["",hat]};
			//var html = upDown[_this.direct][0]+"<div class='"+(IE6?"ie6pos":"startPanel")+" menu_item'><ul class='menu_li' >"+desk.list.simple(_this.items, _this.module, '')+"</ul></div>"+(IE6?"<div  style=position:relative;left:0;top:"+topFix+";width:100%;z-index:-1 class=startPanel><img src=js/desktop/css/windoo/s.gif onload=\"this.parentNode.style.height=this.parentNode.previousSibling.offsetHeight;this.parentNode.nextSibling.style.marginTop=this.parentNode.style.top=0\"></div>":"")+upDown[_this.direct][1];
			
			var html = '<div class="startmenu pr"><ul>'+desk.list.simple(_this.items, _this.module, '')+'</ul><h2><a onclick="getObj(\'startPP\').style.display=\'none\';startPanelShow.remove();" class="del_img fr" href="javascript:;">关闭</a>常用功能项</h2></div>';
			
			p.render(getObj('startMenu')).setHTML(html).onclick(function()
            {
				if(this.url=="loginout")
				{
					return window.location.href=db_adminfile+"?adminjob=quit";
				}
				PW.Dialog(this);
				return false;
            },"firstChild"+(_this.direct=="up"?".nextSibling":""),"firstChild");

			//!IE6?p.element.className="":"";
			startPanelShow=p;
			p.element.id="startPP";
			var pchild=p.element.getElementsByTagName("*");
			for (var i=0,len=pchild.length; i<len; i++)
			{
				pchild[i].setAttribute("t",1);
			}
			
			//drag.setTop(p.element.style.zIndex='10');
			
			p.element.style.zIndex='10';
			
			p.element.onmousedown=function(){event.cancelBubble=true};
			
			_this.remove=function(){
				startPanelShow=0;
				getObj('startMenu').clicked=false;
				p.remove();
				getObj('startPanelImg')?getObj('startPanelImg').src='js/desktop/images/start.png':0;};
			_this.element=p.element;
        };

    };
} ();