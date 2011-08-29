timePopup=5;
var ns = (document.layers);
var ie = (document.all);
var w3 = (document.getElementById && !ie);
adCount = 0;
function initPopup() {
	if (!ns && !ie && !w3) {
		return;
	}
	adDiv = getObj("windlocation").style;
	if (ie||w3) {
		adDiv.visibility="visible";
	} else {
		adDiv.visibility ="show";
	}
	showPopup();
	getlimit();
}   
function showPopup() {
	if (adCount < timePopup * 10) {
		adCount+=1;
		if (ie) {
			documentWidth  = ietruebody().offsetWidth/2+getLeft()-20;
			documentHeight = ietruebody().offsetHeight/2+getTop()-20;
		} else if (ns) {
			documentWidth  = window.innerWidth/2+window.pageXOffset-20;
			documentHeight = window.innerHeight/2+window.pageYOffset-20;
		} else if (w3) {
			documentWidth  = self.innerWidth/2+window.pageXOffset-20;
			documentHeight = self.innerHeight/2+window.pageYOffset-20;
		}
		adDiv.left = documentWidth-250 + 'px';
		adDiv.top  = documentHeight-150 + 'px';
		setTimeout("showPopup()",100);
	} else {
		closePopup();
	}
}
function getlimit(){
	for(var i=5;i>=0;i--){
		setTimeout("setlimit("+i+")",i*1000);
	}
}

function setlimit(i){
	var n = -(i-5);
	var obj = getObj("poplimit");
	if(obj){
		obj.innerHTML = n;
	}
}

function closePopup(){
	if (ie||w3) {
		adDiv.display = "none";
	} else {
		adDiv.visibility = "hide";
	}
}
onload=initPopup;