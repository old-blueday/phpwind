function Element(evtobj,info,infobox,relObj){
    this.o = evtobj;
    this.m = info;
    this.i = infobox;
    if(relObj) this.r = relObj;
}
/*
 * initialize the inputs
 */
(function (){
    var e =  document.getElementsByTagName("input");
    for (var i=0;i<e.length;i++) {
    	if (typeof(e[i].onfocus) == 'function') continue;
		if (e[i].type == 'text' || e[i].type == 'password') {
			e[i].onfocus 	= onFocus;
			e[i].onblur 	= onBlur;
		}
	}
})();

/*fix lh*/
function onFocus(event){
	var obj;
	if (is_ie){
        obj = window.event.srcElement;
    } else {
        obj = event.target;
    }
	if (getInfoBox(obj)) {
		var lastid = eval(obj.id).m.length - 1;
	    getInfoBox(obj).innerHTML = eval(obj.id).m[lastid];
	    getInfoBox(obj).className = inticlass;
	 }
}

function onBlur(evnt){
	var obj;
	if (is_ie) {
        obj = event.srcElement;
    } else {
        obj = evnt.target;
    }
	analyseData(obj);
}

function checkAll(){
    var result = true;
    var e =  document.getElementsByTagName("input");
	if (objCheck("question_0") != undefined ) {
		var question_0 = objCheck("question_0").selected;
		var question_99 = objCheck("question_99").selected;
	}
    for(var i=0;i<e.length;i++){
		if (e[i].id != 'pwuser' && e[i].id != 'customquest_l' && e[i].id != 'keyword') {
			if (question_0 == true && (e[i].id == 'customquested' || e[i].id == 'answered')) {
				continue;
			} else if (question_99 == false && question_0 == false && e[i].id == 'customquested') {
				continue;
			}
			if(e[i].type == 'text' || e[i].type == 'password' || e[i].type == 'checkbox'){
				try{
					eval(e[i].id);
				}
				catch(err) {
				  continue;
				}
				if(e[i].id && Element.prototype.isPrototypeOf(eval(e[i].id))){
					var tmpresult = analyseData(e[i],true);
					if(typeof(tmpresult) != 'undefined'){
						result &= tmpresult;
					}
				}
			}
		}
	}
	for(var i=0;i<extracheck.length;i++){
		var tmpresult = analyseData(extracheck[i],true);
	    if(typeof(tmpresult) != 'undefined'){
	        result &= tmpresult;
	    }
	}
    return (result == "0"?false:true);
}

function analyseData(obj,isSubmit,isAsyc,asyc){
    if(isAsyc){
       return showResult(asyc,obj);
    }else{
		if(!isSubmit){
			if (getInfoBox(obj)) {
				getInfoBox(obj).className = inticlass;
			}

			if(!obj.value) return null;
		}
		if(obj.id){
			var type = obj.id;
			var result = -1;
			switch(type){
				case "regname":
					result = checkRegName(obj);
					break;
				case "regemail":
					result = checkEmail(obj);
					break;
				case "regpwd":
					result = checkPwd(obj);
				    break;
				case "regpwdrepeat":
					result = checkPwdRepeat(obj);
					break;
				case "qanswer":
					result = checkAnswer(obj);
					break;
				case "gdcode":
					result = checkGDCode(obj);
					break;
				case "invcode":
					result = checkInvcode(obj);
					break;
				case "registerclause":
					result = checkRegisterClause(obj);
					break;
				default:
					result = checkField(obj);
					break;
			}
			return showResult(result,obj);
		}
	}
}

function getInfoBox(obj){
	if(obj.id){
		try {
			if (typeof(eval(obj.id)) != 'object')
				return;
		} catch(err) {
			return;
		}
		if(eval(obj.id).i && getObj(eval(obj.id).i))
			return getObj(eval(obj.id).i);
		else
		    return;
	}
	return;
}
/*
 * result:0 true ,1 false
 */
function showResult(result,obj){
    if(obj){
        if(result > 0){
            getInfoBox(obj).className = falseclass;
	        getInfoBox(obj).innerHTML = eval(obj.id).m[result];
	        //obj.parentNode.focus();
	        return false;
	    }
	    if(result == 0 ){
			getInfoBox(obj).innerHTML = eval(obj.id).m[result];
			getInfoBox(obj).className = rightclass;
			return true;
	    }
	}
}

function checkRegName(obj){
	var username = obj.value;
	if(username == ""){
		return 5;
	}
	if(strlen(username)<retminname || strlen(username)>regmaxname){
		return 1;
	}
	var url 	= location.href;
	var data 	= "action=regcheck&type=regname&username="+username;
	getInfoBox(obj).innerHTML = "检测中，请稍等...";
	ajax.send(url,data,function(){
		var response = parseInt(ajax.request.responseText);
		switch (response){
			case 0:
				return showResult(0,obj);
				break;
			case 1:
				return showResult(1,obj);
				break;
			case 2:
				return showResult(2,obj);
				break;
			case 3:
				return showResult(3,obj);
				break;
			case 4:
				return showResult(4,obj);
				break;
			default:
				return showResult(1,obj);
		}
	});
}

function checkEmail(obj){
	var email = obj.value;
	var myReg = /\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/;
	if(!myReg.test(email)){
		return 1;
	} else{
		var url 	= location.href;
		var data 	= "action=regcheck&type=regemail&email="+email;
		getInfoBox(obj).innerHTML = "检测中，请稍等...";
		ajax.send(url,data,function(){
		var response = parseInt(ajax.request.responseText);
		switch (response){
			case 0:
				return showResult(0,obj);
				break;
			case 1:
				return showResult(1,obj);
				break;
			case 2:
				return showResult(2,obj);
				break;
			case 4:
				return showResult(4,obj);
				break;
			case 5:
				return showResult(5,obj);
				break;
			default:
				return showResult(1,obj);
		}
	});
	}
}


function checkPwd(obj){
	var pwd = obj.value;
	if(pwd.length<regminpwd){
		return 5;
	}else if(regmaxpwd>0 && pwd.length>regmaxpwd) {
		return 6;
	}else if(regnpdifferf>0 && getObj('regname').value==pwd) {
		return 7;
	}
	if(/[\\\/\&\'\"\*\,<>#\?% ]/.test(pwd)){
		return 8;
	}
	var rule = pwdcomplex.split(',');
	var pwdReg;
	for(var i=0;i<rule.length;i++){
		switch(Number(rule[i])){
			case 1:
				pwdReg = /[a-z]/;
				if(!pwdReg.test(pwd)) return 1;
				break;
			case 2:
				pwdReg = /[A-Z]/;
				if(!pwdReg.test(pwd)) return 2;
				break;
			case 3:
				pwdReg = /[0-9]/;
				if(!pwdReg.test(pwd)) return 3;
				break;
			case 4:
				pwdReg = /[^a-zA-Z0-9]/;
				if(!pwdReg.test(pwd)) return 4;
				break;
			default:
				continue;
				break;
		}
	}
	var pwdRepeat = getObj('regpwdrepeat');
	if(pwdRepeat.value && checkPwdRepeat(pwdRepeat)){
		getInfoBox(pwdRepeat).className = falseclass;
	    getInfoBox(pwdRepeat).innerHTML = eval(pwdRepeat.id).m[1];
	}
	return 0;

}

function checkPwdRepeat(obj){
	if (obj.value==getObj('regpwd').value && obj.value.length > 0) {
		return 0;
	} else {
		return 1;
	}
}

function checkGDCode(obj){
	var gdcode = obj.value;
	if(gdcode==""){
		return 3;
	}else{
		var url 	= location.href;
		var data 	= "action=regcheck&type=reggdcode&gdcode="+gdcode;
		getInfoBox(obj).innerHTML = "检测中，请稍等...";
		ajax.send(url,data,function(){
			var response = parseInt(ajax.request.responseText);
			switch (response){
				case 0:
					return showResult(0,obj);
					break;
				case 1:
					return showResult(1,obj);
					break;
				default:
					return showResult(1,obj);
			}
		});
	}
}

function checkAnswer(obj){
	var question= parseInt(getObj('regqkey').value);
	var answer 	= obj.value;
	if(answer==""){
		return 2;
	}else{
		var url 	= location.href;
		var data 	= "action=regcheck&type=qanswer&answer="+answer+"&question="+question;
		getInfoBox(obj).innerHTML = "检测中，请稍等...";
		ajax.send(url,data,function(){
			var response = parseInt(ajax.request.responseText);
			if(response > 0){
				try{regInfo[5][2] = regInfo[5][2].replace(/t=\d+/,'t='+new Date().getTime()); }catch(e){alert(e);}
				showResult(response,obj);
			}else{
				return showResult(0,obj);
			}
		});
	}
}

function checkInvcode(obj){
	var invcode = obj.value;
	if(invcode){
		var url 	= location.href;
		var data 	= "action=regcheck&type=invcode&invcode="+invcode;
		getInfoBox(obj).innerHTML = "检测中，请稍等...";
		ajax.send(url,data,function(){
			var response = parseInt(ajax.request.responseText);
			switch (response){
				case 0:
					return showResult(0,obj);
					break;
				case 1:
					return showResult(1,obj);
					break;
				case 2:
					return showResult(2,obj);
					break;
				default:
					return showResult(2,obj);
			}
		});
	}else{
		return 1;
	}
}

function checkRegisterClause(obj){
	if(obj.checked == false) {
		return 1;
	} else {
		return 0;
	}
}

function checkField(obj){
	var field = obj.value;
	if(field==""){
		return 1;
	}else{
		return 0;
	}
}

function strlen(str){
	var len = 0;
	var s_len = str.length = (is_ie && str.indexOf('\n')!=-1) ? str.replace(/\r?\n/g, '_').length : str.length;
	var c_len = charset == 'utf-8' ? 3 : 2;
	for(var i=0;i<s_len;i++){
		len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? c_len : 1;
	}
	return len;
}
