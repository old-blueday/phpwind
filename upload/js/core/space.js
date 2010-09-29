function postdbopen(menuid,aid){
	read.open(menuid,aid);
	var obj = getObj(menuid).getElementsByTagName('a');
	for (var i=0;i<obj.length;i++) {
		if (obj[i].id && obj[i].id.indexOf('ptable_') != -1) {
			var ptable = '';
			var objarray = obj[i].id.split('_');
			if (parseInt(objarray[1]) > 0) {
				ptable = '&ptable=' + objarray[1];
			}
			getObj(obj[i].id).href = getObj(aid).href + ptable;
			if (aid == 'del_post') {
				getObj(obj[i].id).target = "_blank";
			}
		}
	}
}
ImgLoad(content);

function ImgLoad(obj)
{
	for(var i=0;i<obj.getElementsByTagName("img").length;i++){
		var o=obj.getElementsByTagName("img")[i];
		if (o.width>imgMaxWidth){
			if (o.style.width){
				o.style.width="";
			}
			o.width=imgMaxWidth;
			o.removeAttribute("height");
			o.setAttribute("title",M_USER_CTRL);
			o.style.cursor="hand";
			o.style.display="block";
			o.vspace=5;
			o.resized=1;
			o.onclick=ImgClick;
			o.onmousewheel=bbimg;
		}
	}
}

function ImgClick()
{
	var url = getObj('imgview');
	if (url.parentElement){
		if (url.parentElement.tagName!="A"){
			if (url.src) window.open(url.src);
		}
	}else{
		if (url.src) window.open(url.src);
	}
}

function bbimg()
{
	if (event.ctrlKey){
		var zoom=parseInt(this.style.zoom, 10)||100;
		zoom+=event.wheelDelta/12;
		if (zoom>0) this.style.zoom=zoom+'%';
		return false;
	}else{
		return true;
	}
}
