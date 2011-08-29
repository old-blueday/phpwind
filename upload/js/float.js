/*
右下弹窗广告
*/
var showtime=0;
var marginL=10; //右边距
var it1;

function ShowPop(popParam,popCode){
	var popup=document.createElement("DIV");
	popup.id="popup";
	popup.className='t';
	popup.style.height=popParam.winHeight+"px";
	popup.style.width=popParam.winWidth+"px";
	popup.style.padding="0";
	popup.style.margin="0";
	popup.style.backgroundColor="#FFFFFF";
	popup.style.position="absolute";
	popup.style.top=ietruebody().clientHeight+getTop()-marginL-popParam.winHeight+"px";
	popup.style.right=marginL+"px";
	popup.style.zIndex = 9999;

	popup.innerHTML="<table cellspacing=0 cellpadding=0 width=100%><tr><td class='h'><a id='closeButton' title='close' class='fr' onclick='hidePop();'><img src='images/close.gif' alt=\"close\" /></a>"+popParam.title+"</td></tr><tr class='f_one'><td>" + popCode + "</td></tr></table>";
	var btn = findElement(popup,'a',"closeButton");
	btn.style.cssText='cursor:pointer;margin-top:3px;';
	document.body.appendChild(popup);
	it1 = setInterval(function() {floatPop(popParam)},100);
}
function floatPop(popParam){
	if(popParam.winClose>0){
		showtime++;
		if(showtime > popParam.winClose*10){
			hidePop('auto');
			return;
		}
	}
	document.getElementById("popup").style.top=((document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body).clientHeight+getTop()-marginL-popParam.winHeight+"px";
}
function hidePop(type){
	document.getElementById("popup").style.display="none";
	document.getElementById("popup").innerHTML="";
	clearInterval(it1);
	if(typeof type=='undefined') document.cookie="hidepop=1; path=/";
}
function findElement(root,tag,id){
	var ar=root.getElementsByTagName(tag);
	for(var i=0;i<ar.length;i++){
		if(ar[i].id==id) return ar[i];
	}
	return null;
}

/*
漂浮广告
*/
var it2;
var delay = 10;
var x = 50,y = 60; //初始坐标
var xin = true,yin = true;
var step = 1;
//alert(ietruebody().clientWidth)
function ShowAd(floatCode){
	var popup=document.createElement("DIV");
	popup.id="floatAd";
	popup.style.position = 'absolute';

	popup.innerHTML = floatCode+"<br /><a style='cursor:pointer;' onclick='hideAd();'>关闭</a>";

	document.body.appendChild(popup);

	obj = document.getElementById("floatAd");
	it2= setInterval("floatAd()", delay);
	obj.onmouseover=function(){clearInterval(it2)};
	obj.onmouseout=function(){it2=setInterval("floatAd()", delay)};

}
function floatAd(){
	var L=T=0;
	if(!-[1,]){
		var R = ietruebody().clientWidth-obj.offsetWidth;
		var B = ietruebody().clientHeight-obj.offsetHeight;
	}else{
		var R = document.documentElement.clientWidth-obj.offsetWidth;
		var B = document.documentElement.clientHeight-obj.offsetHeight;
	}
	obj = document.getElementById("floatAd");
	obj.style.left = x + getLeft() + "px";
	obj.style.top = y + getTop() + "px";
	x = x + step*(xin?1:-1);
	if (x < L) { xin = true; x = L};
	if (x > R) { xin = false; x = R};
	y = y + step*(yin?1:-1);
	if (y < T) { yin = true; y = T };
	if (y > B) { yin = false; y = B };
}
function hideAd(){
	document.getElementById("floatAd").style.display="none";
	document.getElementById("floatAd").innerHTML="";
	clearInterval(it2);
}

/*
左右漂浮对联广告
*/
var marginTop = 120; //对顶上边距
var marginX = 15; //横向 边距
var it3;

function ShowFloat(LeftCode, RightCode){
	if(LeftCode!=''){
		var popup=document.createElement("DIV");
		popup.id="adLeftFloat";
		popup.style.position = 'absolute';
		popup.style.left = marginX+"px";
		popup.style.top = marginTop+"px";

		popup.innerHTML = LeftCode+"<br><div style=\"width:100;background-color:#E1E1E1; text-align:left\"><a style=\"cursor:pointer;\" onclick=\"hideFloat();\">关闭</a></div>";

		document.body.appendChild(popup);
	}
	if(RightCode!='') {
		var popup=document.createElement("DIV");
		popup.id="adRightFloat";
		popup.style.position = 'absolute';
		popup.style.right = marginX+"px";
		popup.style.top = marginTop+"px";

		popup.innerHTML = RightCode+"<br><div style=\"width:100;background-color:#E1E1E1; text-align:left\"><a style=\"cursor:pointer;\" onclick=\"hideFloat();\">关闭</a></div>";

		document.body.appendChild(popup);
	}
	moveFloat();
}
function hideFloat(){
	clearTimeout(it3);
	if(IsElement("adLeftFloat")){
		document.getElementById("adLeftFloat").style.display = "none";
		document.getElementById("adLeftFloat").innerHTML = "";}
	if(IsElement("adRightFloat")){
		document.getElementById("adRightFloat").style.display = "none";
		document.getElementById("adRightFloat").innerHTML = "";}
	return false;
}
function moveFloat(){
	if(IsElement("adLeftFloat"))
		document.getElementById("adLeftFloat").style.top = getTop() + marginTop + 'px';
	if(IsElement("adRightFloat"))
		document.getElementById("adRightFloat").style.top = getTop() + marginTop + 'px';
	it3 = setTimeout("moveFloat();",80);
}