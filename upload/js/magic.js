var myshow = 'http://rs.phpwind.net/';
var magic_id    = "";
var magic_name  = "";
var showmagicid = "mb_0";
var magicsid    = "";
var op          = "previewmagic";
var mType = new Array('S.swf','J.jpg','T.gif');
if (is_ie) {
	var script_e    = document.createElement('script');
	var head_e      = document.getElementsByTagName("head")[0];
	with (script_e) {
		language = 'javascript1.1';
		htmlFor  = 'magic';
		event    = 'FSCommand(command, args)';
		text     = "magic_DoFSCommand(command, args);";
	}
	head_e.appendChild(script_e);
}
showDefaultMagic();

function showDefaultMagic(){
	var F=getMagic();

	if(F){
		getObj("menu_magicshow").innerHTML = '<img class="lhcl_selectBox" id="'+magic_id+'" src="'+myshow+magic_id+'T.gif" alt="'+I18N['magicuse']+magic_name+'" title="'+I18N['magicuse']+magic_name+'" onclick="selectMagic(\''+magic_id+'\');">';
		getObj("magicsmiliebox").style.display="";
		//getObj("btns").style.display="none";
		//getObj("btnc").style.display="inline";
	} else{
		/*
		var sT = "";
		for(i in mDef){
			try{sT += '<img class="lhcl_slideImage" style="margin:2px" id="'+i+'" src="'+myshow+i+'J.jpg" title="'+mDef[i]+'" onclick="selectMagic(\''+i+'\');" ondblclick="setMagic(this.id)">';}catch(e){}
		}
		getObj("menu_magicshow").innerHTML = sT;
		getObj("btns").style.display       = "inline";
		getObj("btnc").style.display       = "none";
		*/
	}
	op = 'previewmagic';
}
function setMagic(id){
	if(!magic_id) return false;
	if(IsElement('magicbuttons')){
		clearp('magiclist');
		closep();
	}
	var oid   = document.FORM.magicid;
	var oname = document.FORM.magicname;
	if(id!="btnc"){
		oid.value   = magic_id;
		oname.value = magic_name;
	} else{
		oid.value   = "";
		oname.value = "";
	}
	showDefaultMagic();
	return;
}
function getMagic(){
	var oid   = document.FORM.magicid;
	var oname = document.FORM.magicname;
	if (!oid) return false; 
	if (!oname) return false;
	if(!oid.value) return false;
	magic_name = oname.value;
	magic_id   = oid.value;
	return true;
}

function selectMagic(id){
	if(op == "previewmagic")hideMagic();
	try{getObj(magic_id).className="lhcl_slideImage";}catch(e){}
	try{getObj(id).className="lhcl_selectBox";}catch(e){}
	magic_id   = id;
	magic_name = getObj(id).title;
	previewMagic(200,210);
}
function previewMagic(w,h){
	if(!magic_id)return false;
	var MagicUrl = myshow+magic_id+"S.swf";
	var o = getObj(op);
	if(!w || !h){
		if(op == "previewmagic"){
			w=350;h=350;
		}else{
			w=200;h=210;
		}
	}
	o.innerHTML  = flash_build('magic',MagicUrl,w,h);
	if (op == "previewmagic") {
		o.style.top  = (getTop()+((ietruebody().clientHeight-300)/2))+"px";
		o.style.left = (getLeft()+((ietruebody().clientWidth-400)/2))+"px";
		o.style.display = "block";
	} else {
		o.style.display = "block";
	}
}
function magic_DoFSCommand (command, args) {
	if(command == "quit") hideMagic();
}
function clearp(id){
	getObj(id).innerHTML = "";
	hideMagic();
}
function hideMagic(){
try{
	var o = getObj('player');
	if(o){
		if(is_ie){
			o.innerHTML = '';
		} else{
			getObj('magic').src = '';
		}
		o.style.display = 'none';
	}
}catch(e){}
}
function showMagicDefault(){
	op = 'magicpreview';
	if (!IsElement('magicbuttons')) {
		loadFaceCss();
		read.obj = getObj("td_magicsmile");
		read.guide();
		ajax.send('pw_ajax.php?action=showsmile&type=magic','',initMagic);
	} else{
		read.open('menu_magicsmile','td_magicsmile','2');
		showMagics(showmagicid,magicsid,1);
	}
}
function showMagics(showid,subjectid,page){
	showmagicid = showid;
	magicsid    =subjectid;
	selectMenu("magicbuttons",showmagicid);
	getObj("magiclist").innerHTML = showLoading();;
	getObj("magicpage").innerHTML = "";
	var url = 'pw_ajax.php?action=showsmile&type=magic&subjectid='+subjectid+'&page='+page;
	ajax.send(url,'',initMagics);
}
function NumOfPage(total,currentPage,showIndex){
	var showIndex = showIndex ? showIndex : 5;
	var currentPage = currentPage ? currentPage : 1;
	var pageIndex=[];

	if(total==1){
		return '';
	} else if(total<currentPage){
		currentPage=total;
	} else if(currentPage<1){
		currentPage=1;
	}
	pageIndex.push("<div style='height:6px;'><span style='margin:0 4px;cursor:pointer' onclick='showMagics(\""+showmagicid+"\","+magicsid+",1)'><img src='"+imgpath+"/wind/page_prev.gif' height='9' width='9'></span>");
	if(total<=showIndex){
		for(var i=1;i<=total;i++){
			if(i==currentPage){
				pageIndex.push("<span style='margin:0 4px;'><b>"+i+"</b></span>");
			} else{
				pageIndex.push("<span style='margin:0 4px;cursor:pointer' onclick='showMagics(\""+showmagicid+"\","+magicsid+","+i+")'>"+i+"</span>");
			}
		}
	} else{
		if((currentPage-parseInt(showIndex/2))<1){
			var startIndex=1;
		} else{
			var startIndex=currentPage-parseInt(showIndex/2);
		}
		if((startIndex+showIndex)>total){
			var endIndex=total;
			startIndex=endIndex-showIndex+1;
		} else{
			var endIndex=startIndex+showIndex-1;
		}
		for(var i=startIndex;i<=endIndex;i++){
			if(i==currentPage){
				pageIndex.push("<span style='margin:0 4px;'><b>"+i+"</b></span>");
			} else{
				pageIndex.push("<span style='margin:0 4px;cursor:pointer' onclick='showMagics(\""+showmagicid+"\","+magicsid+","+i+")'>"+i+"</span>");
			}
		}
	}
	pageIndex[pageIndex.length]="<span style='margin:0 4px;cursor:pointer' onclick='showMagics(\""+showmagicid+"\","+magicsid+","+total+")'><img src='"+imgpath+"/wind/page_next.gif' height='9' width='9'></span></div>";
	return pageIndex.join('&nbsp;');
}
function initMagics(){

	var response = ajax.XmlDocument();
	var magicsid   = new Array();
	var magicsname = new Array();
	var magicstype = new Array();
	var magicscode = new Array();
	var i=response.getElementsByTagName('items')[0];
	var pagecount=i.getAttribute('pagecount');
	var page = i.getAttribute('page');
	var node = i.childNodes;
	var j=0;

	for(var i=0;i<node.length;i++){
		try{
			magicsid[j]   = node[i].getAttribute('id');
			magicsname[j] = node[i].getAttribute('name');
			magicstype[j] = node[i].getAttribute('type');
			magicscode[j] = node[i].getElementsByTagName('code').item(0).firstChild.nodeValue;
			j++;
		}catch(e){}
	}
	var showface = getObj("magiclist");
	showface.innerHTML = "";
	for(i in magicsid){
		try{var pic = document.createElement("img");
		pic.style.margin = "3px";
		pic.style.cursor = "pointer";
		pic.className="lhcl_slideImage";
		pic.id = magicscode[i];
		pic.name= magicsid[i];
		pic.title=magicsname[i];
		pic.src = myshow+magicscode[i]+mType[magicstype[i]];
		pic.onclick = function(){selectMagic(this.id);};
		pic.ondblclick=function(){setMagic(this.id);};
		showface.appendChild(pic);}catch(e){}
	}
	magic_id=magicscode[0];
	selectMagic(magic_id);
	getObj("magicpage").innerHTML=NumOfPage(pagecount,page,5);

}
function initMagic(){
	var response = ajax.XmlDocument();
	var magicid   = new Array();
	var magicname = new Array();
	var node = response.getElementsByTagName('subject')[0].childNodes;
	var j=0;
	for(var i=0;i<node.length;i++){
		try{
			magicid[j] = node[i].getAttribute('id');
			magicname[j] = node[i].getAttribute('name');
			j++;
		}catch(e){}
	}
	var s = '<div style="float:left;margin:5px"><div id="loading" style="width:200px;height:210px;border:1px solid #ccc;display:none;"></div><div id="magicpreview" style="width:200px;height:210px;border:1px solid #ccc;"></div><div style="margin-top:6px;text-align:center"><input class="btn" type="Button" value="'+I18N['preview']+'" onclick="previewMagic(200,210)"/><input class="btn" id="btnsm" type="button" value="'+I18N['use']+'" onclick="setMagic(this.id)" /></div></div><div id="magiclist" style="height:240px;"></div><div id="magicpage"></div>';
	var num = 0;
	var b='<span style="float:right;margin:3px 0 0 5px;width:auto;cursor:pointer" onclick="clearp(\'magiclist\');closep();showDefaultMagic();" title="关闭"><img src='+imgpath+'/close.gif></span><ul>';
	for(f in magicid){
		try{b += '<li id="mb_'+num+'" style="float:left" onclick="showMagics(\'mb_'+num+'\','+magicid[f]+',1);">'+magicname[f]+'</li>';
		num++;}catch(e){}
	}
	b += '</ul>';
	var a = {id:'menu_magicsmile',bid:'magicbuttons',sid:'showMagic',width:'575',height:'260',bhtml:b,shtml:s};
	initMenuTab(a,"9");
	read.open('menu_magicsmile','td_magicsmile','2');
	magicsid = magicid[0];
	showMagics(showmagicid,magicsid,'1');
}
function flash_build(id, url, width, height){
	return "<div id=\"player\"><object id=\""+id+"\" CLASSID=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\""+width+"\" height=\""+height+"\"><param name=\"movie\" value=\""+url+"\"><param name=\"play\" value=\"true\"><param name=\"wmode\" value=\"transparent\"><param name=\"allowScriptAccess\" value=\"always\"><param name=\"swliveconnect\" value=\"true\"><param name=\"quality\" value=\"high\"><embed name=\""+id+"\" src=\""+url+"\" width=\""+width+"\" height=\""+height+"\" play=\"true\" loop=\"true\" wmode=\"transparent\" allowScriptAccess=\"always\" swliveconnect=\"true\" quality=\"high\"></embed></object></div>";
}