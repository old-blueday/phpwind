timecredit=3;
var ns = (document.layers);
var ie = (document.all);
var w3 = (document.getElementById && !ie);
adCount = 0;
function initcredit() {
	if (!ns && !ie && !w3) {
		return;
	}
	adDiv = getObj("creditlocation").style;
	showcredit();
	if (ie||w3) {
		adDiv.visibility="visible";
	} else {
		adDiv.visibility ="show";
	}
}
function showcredit() {
	if (adCount < timecredit * 10) {
		adCount+=1;
		setTimeout("showcredit()",100);
	} else {
		closecredit();
	}
}
function closecredit(){
	if (ie||w3) {
		adDiv.display = "none";
	} else {
		adDiv.visibility = "hide";
	}
}
onload=initcredit;