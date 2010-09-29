//帮助中心菜单导航
	
function fnInitMenu(){
	
	YAHOO.namespace('Menu');
	var sMenuId = "qMenu_1"; //导航的ID名
	var sMenu2ClassName = "qMenu_2"; //二级菜单的className
	var sMenu1ActiveClassName = "activeItem"; //当前激活的一级菜单className
	var oMenu = YAHOO.util.Dom.get(sMenuId);
	
	YAHOO.Menu.hide = function(obj){
		
		var menu2 = YAHOO.util.Dom.getElementsByClassName(sMenu2ClassName,"ul",oMenu);
		for(var i=1;i<menu2.length;i++){
			if(menu2[i].parentNode == obj) continue; 
			menu2[i].style.display="none";
			YAHOO.util.Dom.removeClass(menu2[i].parentNode,sMenu1ActiveClassName);	
		}
		

	}
	
	YAHOO.Menu.fold = function(ev){
		
		var a =YAHOO.util.Event.getTarget(ev);
		var b =YAHOO.util.Dom.getLastChild(a);	
		if(YAHOO.util.Dom.hasClass(a,sMenu1ActiveClassName)){							
			if(YAHOO.util.Dom.hasClass(b,sMenu2ClassName)){
				b.style.display = "none";
			}
			YAHOO.util.Dom.removeClass(a,sMenu1ActiveClassName);			
		}else{
			
		 	if(YAHOO.util.Dom.hasClass(b,sMenu2ClassName)){
				
				YAHOO.Menu.hide();
				b.style.display = "block";
			}
			YAHOO.util.Dom.addClass(a,sMenu1ActiveClassName);
		}
	}
	
	YAHOO.Menu.init = new function(){		
		var c = YAHOO.util.Dom.getChildren(sMenuId);	
		for(var j=0;j<c.length;j++){
			YAHOO.util.Event.on(c[j],"click",YAHOO.Menu.fold);
			if(YAHOO.util.Dom.hasClass(c[j],sMenu1ActiveClassName)){
				var oNotHide =c[j];
			}	
		}
		YAHOO.Menu.hide(oNotHide);
	}	
}

YAHOO.util.Event.onDOMReady(fnInitMenu);