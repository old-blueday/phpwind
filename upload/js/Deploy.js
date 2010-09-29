function IndexDeploy(ID,type) {
	var obj = document.getElementById("cate_"+ID);	
	var img = document.getElementById("img_"+ID);
	if (obj.style.display == "none") {
		obj.style.display = "";
		img_re  = new RegExp("_open\\.gif$");
		img.src = img.src.replace(img_re,'_fold.gif');
		SaveDeploy(ID,type,false);
	} else {
		obj.style.display = "none";
		img_re  = new RegExp("_fold\\.gif$");
		img.src = img.src.replace(img_re,'_open.gif');
		SaveDeploy(ID,type,true);
	}
	return false;
}

function SaveDeploy(ID,type,is) {
	var foo=new Array();
	var deployitem=FetchCookie("deploy");
	var admin_start;
	var admindeploy='';
	var userdeploy='';
	admin_start= deployitem ? deployitem.indexOf("\n") : -1;
	if (admin_start != -1) {
		admindeploy = deployitem.substring(admin_start+1,deployitem.length);
		userdeploy  = deployitem.substring(0,admin_start);
	}
	if (deployitem != null) {
		if (admin_start!=-1) {
			deployitem = type==0 ? userdeploy : admindeploy;
		}
		deployitem=deployitem.split("\t");
		for (i in deployitem) {
			if (deployitem[i]!=ID && deployitem[i]!="") {
				foo[foo.length]=deployitem[i];
			}
		}
	}
	if (is) {
		foo[foo.length]=ID;
	}
	deployitem = type==0 ? "\t"+foo.join("\t")+"\t\n"+admindeploy : userdeploy+"\n\t"+foo.join("\t")+"\t";
	SetCookie("deploy",deployitem)
}

function SetCookie(name,value) {
	expires = new Date();
	expires.setTime(expires.getTime()+(86400*365));
	document.cookie=name+"="+escape(value)+"; expires="+expires.toGMTString()+"; path=/";
}

function FetchCookie(name) {
	var start = document.cookie.indexOf(name);
	var end = document.cookie.indexOf(";",start);
	return start==-1 ? null : unescape(document.cookie.substring(start+name.length+1,(end>start ? end : document.cookie.length)));
}

function Ajump(value) {
	if (value != '') {
		window.location=('profile.php?action=show&username='+value);
	}
}