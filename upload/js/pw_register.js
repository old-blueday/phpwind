var extracheck	= [];
function Element(evtobj,info,infobox,relObj,required){
    this.o = evtobj;
    this.m = info;
    this.i = infobox;
    if(relObj) this.r = relObj;
    if(required) this.d = required;
}
var ajaxclearhistory=false; //fixed for tt browser
var regGdTimer;
/*
 * initialize the inputs
 */
(function (){
    var e =  document.getElementsByTagName("input");
    for (var i=0;i<e.length;i++) {
    	if (typeof(e[i].onfocus) == 'function') continue;
    	if(e[i].name == 'authmobile' || e[i].name == 'authverify') continue;
		if (e[i].type == 'text' || e[i].type == 'password' || e[i].type == 'email') {
			e[i].noerror = 0;
			e[i].onfocus 	= onFocus;
			e[i].onblur 	= onBlur;
		} else if (e[i].type == 'checkbox' && e[i].id != 'registerclause') {
			e[i].onclick = clickCheckbox;
		} else if (e[i].type == 'radio') {
			e[i].onclick = clickRadio;
		}
	}
    var textareaElement = document.getElementsByTagName("textarea");
    for (var j=0;j<textareaElement.length;j++) {
    	textareaElement[j].noerror = 0;
    	textareaElement[j].onfocus 	= onFocus;
    	textareaElement[j].onblur 	= onBlur;
    }
    onReady(function(){
    	for(var z=0;z<extracheck.length;z++){
    		extracheck[z].onclick = changeArea;
    	}
    });
})();

/*fix lh*/
function onFocus(event){
	var obj;
	if (is_ie){
        obj = window.event.srcElement;
    } else {
        obj = event.target;
    }
	if (obj.id == 'gdcode') showgd('menu_gd');
	if (getInfoBox(obj)) {
		var lastid = eval(obj.id).m.length - 1;
		if(eval(obj.id).m[lastid] == '') {
			getInfoBox(obj).style.display = 'none';
			return;
		}
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
	if (obj.id == 'invcode') {
		setTimeout(function(){analyseData(obj);}, 100);
	} else if(obj.id == 'gdcode') {
		regGdTimer = setTimeout(function(){analyseData(obj);}, 500);
	} else {
		analyseData(obj);
	}
}

function changeArea(obj) {
	if (typeof(obj) != 'undefined') {
		analyseData(obj);
	}
}

function clickCheckbox(event) {
	var obj;
	if (is_ie){
        obj = window.event.srcElement;
    } else {
        obj = event.target;
    }
	var name = obj.name.replace('[]','');
	checkCheckbox(name);
}

function clickRadio(event) {
	var obj;
	if (is_ie){
        obj = window.event.srcElement;
    } else {
        obj = event.target;
    }
	checkRadio(obj.name);
}

var ajaxCheckArray = new Array();
function checkAll(e){
	var e = window.event || e;
	if(e.preventDefault){e.preventDefault();}else{e.returnValue = false;}
    var result = 1;
    //校验input
    var e =  document.getElementsByTagName("input");
    for(var i=0;i<e.length;i++){
    	if(e[i].id == 'authmobile' && !/^1\d{10}$/.test(e[i].value) && getObj('mobileBox').style.display != 'none'){
    		getObj('mobileauth_info').innerHTML = authInfo[1];
    		getObj('mobileauth_info').style.display = '';
    		e[i].onfocus = function(){getObj('mobileauth_info').style.display = 'none';}
    	}else if(e[i].id == 'authverify' && e[i].value =='' && getObj('verifyBox').style.display != 'none'){
    		getObj('authTips').className = 'wrong';
    		getObj('authTips').innerHTML = authInfo[7];
    		getObj('authTips').style.display = '';
    		e[i].onfocus = function(){getObj('authTips').style.display = 'none';}
    	}
		if (e[i].id != 'pwuser' && e[i].id != 'customquest_l' && e[i].id != 'keyword') {
			if(e[i].type == 'text' && e[i].id != 'regpwd' && e[i].id != 'regpwdrepeat' && e[i].id != 'registerclause'){
				try{
					eval(e[i].id);
				}catch(err) {
					continue;
				}
				if(e[i].id && Element.prototype.isPrototypeOf(eval(e[i].id))){
					var tmpresult = analyseData(e[i],true);
					if (typeof (tmpresult) != 'undefined' ) {
						result &= tmpresult;
					}
				}
			}
		}
	}
    
    //校验textarea
    var textareaElement = document.getElementsByTagName("textarea");
    for (var j=0;j<textareaElement.length;j++) {
    	try{
			eval(textareaElement[j].id);
		}catch(err) {
		  continue;
		}
		if(textareaElement[j].id && Element.prototype.isPrototypeOf(eval(textareaElement[j].id))){
			var textarearesult = analyseData(textareaElement[j],true);
			if (typeof (textarearesult) != 'undefined' ) {
				result &= textarearesult;
			}
		}
    }
    
    //校验地区
	for(var i=0;i<extracheck.length;i++){
		var extraresult = analyseData(extracheck[i],true);
		if (typeof (extraresult) != 'undefined' ) {
			result &= extraresult;
		}
	}
	
	ajaxCheck(result);
}

function ajaxCheck(result) {
	var postData = '[';
	for(var i in ajaxCheckArray) {
		if (ajaxCheckArray[i].noerror) continue;
		postData += '["' + i +'","' + ajaxCheckArray[i].type + '","' + ajaxCheckArray[i].value + '"],';
	}
	postData = postData.replace(/,$/,'');
	postData += ']';
	if (postData == '[]') {
		var otherCheck;
		otherCheck = checkWithoutAjax();
		otherCheck &= result;
		if (otherCheck == 1) {
			document.register.submit();
			return true;
		}
	} else {
		var url 	= getRegAjaxCheckUrl();
		var data 	= "action=regcheck&type=all&data=" + postData;
		read.guide();
		ajax.send(url,data,function() {
			var response = ajax.request.responseText;
			if (response == 'success') {
				var otherResult;
				otherResult = checkWithoutAjax();
				otherResult &= result;
				if (otherResult == 1) {
					document.register.submit();
				}
			} else {
				response = JSONParse(response);
				for (var i in response) {
					//analyseData(getObj(i),false);
					showResult(response[i],getObj(i));
				}
				checkWithoutAjax();
			}
			read.close();
		});
	}
}

function checkWithoutAjax() {
	var tempResult = 1;
	
	//校验不需要ajax的非自定义字段
	if (getObj('regpwd')) {
		var pwdResult = checkPwd(getObj('regpwd'));
		showResult(pwdResult, getObj('regpwd'));
		pwdResult = pwdResult > 0 ? 0 : 1;
		tempResult &= pwdResult;
	}
	if (getObj('regpwdrepeat')) {
		var pwdrpResult = checkPwdRepeat(getObj('regpwdrepeat'));
		showResult(pwdrpResult, getObj('regpwdrepeat'));
		pwdrpResult = pwdrpResult > 0 ? 0 : 1;
		tempResult &= pwdrpResult;
	}
	if (getObj('registerclause')) {
		var clauseResult = checkRegisterClause(getObj('registerclause'));
		showResult(clauseResult, getObj('registerclause'));
		clauseResult = clauseResult > 0 ? 0 : 1;
		tempResult &= clauseResult;
	}
	
	//校验checkbox
	for(var j=0;j<checkboxArray.length;j++) {
		var checkResult = checkCheckbox(checkboxArray[j]);
		 if(typeof(checkResult) != 'undefined'){
			 tempResult &= checkResult;
		 }
	}

	//校验radio
	for (var i=0;i<radioArray.length;i++) {
		var radioResult = checkRadio(radioArray[i]);
		 if(typeof(radioResult) != 'undefined'){
			 tempResult &= radioResult;
		 }
	}
	return tempResult;
}

function analyseData(obj,isSubmit,isAsyc,asyc){
    if(isAsyc){
       return showResult(asyc,obj);
    }else{
		if(!isSubmit){
			if (getInfoBox(obj)) {
				getInfoBox(obj).className = inticlass;
			}
			//if(!obj.value) return null;
		}
		if(obj.id){
			var type = obj.id;
			var result = -1;
			switch(type){
				case "regname":
					result = checkRegName(obj,isSubmit);
					break;
				case "regemail":
					result = checkEmail(obj,isSubmit);
					break;
				case "regpwd":
					result = checkPwd(obj);
				    break;
				case "regpwdrepeat":
					result = checkPwdRepeat(obj);
					break;
				case "qanswer":
					result = checkAnswer(obj,isSubmit);
					break;
				case "gdcode":
					result = checkGDCode(obj,isSubmit);
					break;
				case "invcode":
					result = checkInvcode(obj,isSubmit);
					break;
				case "registerclause":
					result = checkRegisterClause(obj);
					break;
				default:
					result = checkField(obj,isSubmit);
					if (isSubmit) return result;
					break;
			}
			return showResult(result,obj);
		}
	}
}

function getRegAjaxCheckUrl() {
	//NEED GLOBAL VAR: regAjaxCheckUrl
	if (typeof(regAjaxCheckUrl) == "undefined" || '' == regAjaxCheckUrl) {
		return location.href;
	}
	return regAjaxCheckUrl;
}

function getInfoBox(obj){
	if (typeof(obj) == 'object') {
		if(obj.id){
			try {
				if (typeof(eval(obj.id)) != 'object') return;
			} catch(err) {
				return;
			}
			if(eval(obj.id).i && getObj(eval(obj.id).i)) {
				return getObj(eval(obj.id).i);
			} else {
				return;
			}
		}
	} else {
		if(eval(obj).i && getObj(eval(obj).i)) {
			return getObj(eval(obj).i);
		} else {
			return;
		}
	}
	return;
}
/*
 * result:0 true ,1 false
 */
function showResult(result,obj){
    if(obj){
    	result = parseInt(result);
        if(result > 0){
            getInfoBox(obj).className = falseclass;
            getInfoBox(obj).style.display = '';
	        getInfoBox(obj).innerHTML =  typeof(obj) == 'object' ? eval(obj.id).m[result] : eval(obj).m[result];
	       	if (obj.id == 'gdcode') changeAllKindsGdCode('sitegdcheck', getObj('menu_gd'));
	        return false;
	    }
	    if(result == 0 ){
			getInfoBox(obj).innerHTML = typeof(obj) == 'object' ? eval(obj.id).m[result] : eval(obj).m[result];
			if (obj.id == 'invcode' && getObj('buy_invitecode')) getObj('buy_invitecode').style.display = 'none';
			getInfoBox(obj).className = rightclass;
			getInfoBox(obj).style.display = '';
			if (obj.id == 'gdcode' && typeof(obj.onblur) == 'function') {
				obj.readOnly = true;
				obj.onblur = obj.onfocus = null;
				removeGdcodeClickEvt(getObj('menu_gd'));	
			}
			return true;
	    }
	}
}

function checkRegName(obj,isSubmit){
	var username = obj.value;
	if (isSubmit == true) {
		ajaxCheckArray[obj.id] = {'noerror':obj.noerror,'type':'regname','value':obj.value};
		return;
	}
	if(username == ""){
		obj.noerror = 0;
		return 5;
	}
	if(strlen(username)<retminname || strlen(username)>regmaxname){
		obj.noerror = 0;
		return 1;
	}
	var url 	= getRegAjaxCheckUrl();
	var data 	= "action=regcheck&type=regname&username="+username;
	getInfoBox(obj).innerHTML = "&nbsp;检测中，请稍等...";
	ajax.send(url,data,function(){
		var response = parseInt(ajax.request.responseText);
		switch (response){
			case 0:
				obj.noerror = 1;
				return showResult(0,obj);
				break;
			case 1:
				obj.noerror = 0;
				return showResult(1,obj);
				break;
			case 2:
				obj.noerror = 0;
				return showResult(2,obj);
				break;
			case 3:
				obj.noerror = 0;
				return showResult(3,obj);
				break;
			case 4:
				obj.noerror = 0;
				return showResult(4,obj);
				break;
			default:
				obj.noerror = 0;
				return showResult(1,obj);
		}
	});
}

function checkEmail(obj,isSubmit){
	var email = obj.value;
	if (isSubmit == true) {
		ajaxCheckArray[obj.id] = {'noerror':obj.noerror,'type':'regemail','value':obj.value};
		return;
	}
	var myReg = /\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/;
	if(!myReg.test(email)){
		obj.noerror = 0;
		return 1;
	} else{
		var url 	= getRegAjaxCheckUrl();
		var data 	= "action=regcheck&type=regemail&email="+email;
		getInfoBox(obj).innerHTML = "&nbsp;检测中，请稍等...";
		ajax.send(url,data,function(){
		var response = parseInt(ajax.request.responseText);
		switch (response){
			case 0:
				obj.noerror = 1;
				return showResult(0,obj);
				break;
			case 1:
				obj.noerror = 0;
				return showResult(1,obj);
				break;
			case 2:
				obj.noerror = 0;
				return showResult(2,obj);
				break;
			case 4:
				obj.noerror = 0;
				return showResult(4,obj);
				break;
			case 5:
				obj.noerror = 0;
				return showResult(5,obj);
				break;
			default:
				obj.noerror = 0;
				return showResult(1,obj);
		}
	});
	}
}


function checkPwd(obj){
	var pwd = obj.value;
	if(pwd.length<regminpwd){
		obj.noerror = 0;
		return 5;
	}else if(regmaxpwd>0 && pwd.length>regmaxpwd) {
		obj.noerror = 0;
		return 6;
	}else if(regnpdifferf>0 && getObj('regname').value==pwd) {
		obj.noerror = 0;
		return 7;
	}
	if(/[\\\/\&\'\"\*\,<>#\?%!。 ]/.test(pwd)){
		obj.noerror = 0;
		return 8;
	}
	var rule = pwdcomplex.split(',');
	var pwdReg;
	for(var i=0;i<rule.length;i++){
		switch(Number(rule[i])){
			case 1:
				pwdReg = /[a-z]/;
				if(!pwdReg.test(pwd)) {
					obj.noerror = 0;
					return 1;
				}
				break;
			case 2:
				pwdReg = /[A-Z]/;
				if(!pwdReg.test(pwd)) {
					obj.noerror = 0;
					return 2;
				}
				break;
			case 3:
				pwdReg = /[0-9]/;
				if(!pwdReg.test(pwd)) {
					obj.noerror = 0;
					return 3;
				}
				break;
			case 4:
				pwdReg = /[^a-zA-Z0-9]/;
				if(!pwdReg.test(pwd)) {
					obj.noerror = 0;
					return 4;
				}
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
	obj.noerror = 1;
	return 0;

}

function checkPwdRepeat(obj){
	if (obj.value==getObj('regpwd').value && obj.value.length > 0) {
		obj.noerror = 1;
		return 0;
	} else {
		obj.noerror = 0;
		return 1;
	}
}

function checkGDCode(obj,isSubmit){
	var gdcode = obj.value;
	if (isSubmit == true) {
		if (regGdTimer) {
			clearTimeout(regGdTimer);
		}
		var tmpValue = encodeURIComponent(obj.value);
		ajaxCheckArray[obj.id] = {'noerror':obj.noerror,'type':'reggdcode','value':tmpValue};
		return;
	}
	if(gdcode==""){
		obj.noerror = 0;
		return 3;
	}else{
		var url 	= getRegAjaxCheckUrl();
		var data 	= "action=regcheck&type=reggdcode&gdcode="+encodeURIComponent(gdcode);
		getInfoBox(obj).innerHTML = "&nbsp;检测中，请稍等...";
		ajax.send(url,data,function(){
			var response = parseInt(ajax.request.responseText);
			switch (response){
				case 0:
					obj.noerror = 1;
					return showResult(0,obj);
					break;
				case 1:
					obj.noerror = 0;
					return showResult(1,obj);
					break;
				default:
					obj.noerror = 0;
					return showResult(1,obj);
			}
		});
	}
}

function removeGdcodeClickEvt(obj) {
	var elements = obj.getElementsByTagName("*");
	for (var i = 0, j = elements.length; i < j; i++) {
		if (typeof elements[i].onclick == 'function') elements[i].onclick = null;
	}
	return true;
}

function checkAnswer(obj,isSubmit){
	var question= parseInt(getObj('regqkey').value);
	var answer 	= obj.value;
	if (isSubmit == true) {
		ajaxCheckArray[obj.id] = {'noerror':obj.noerror,'type':'regcheck','value':question + '|' + answer};
		return;
	}
	if(answer==""){
		return 2;
	}else{
		var url 	= getRegAjaxCheckUrl();
		var data 	= "action=regcheck&type=qanswer&answer="+answer+"&question="+question;
		getInfoBox(obj).innerHTML = "&nbsp;检测中，请稍等...";
		ajax.send(url,data,function(){
			var response = parseInt(ajax.request.responseText);
			if(response > 0){
				obj.noerror = 0;
				try{regInfo[5][2] = regInfo[5][2].replace(/t=\d+/,'t='+new Date().getTime()); }catch(e){alert(e);}
				showResult(response,obj);
			}else{
				obj.noerror = 1;
				return showResult(0,obj);
			}
		});
	}
}

function checkInvcode(obj,isSubmit){
	var invcode = obj.value;
	if (isSubmit == true) {
		ajaxCheckArray[obj.id] = {'noerror':obj.noerror,'type':'invcode','value':invcode};
		return;
	}
	if(invcode){
		var url 	= getRegAjaxCheckUrl();
		var data 	= "action=regcheck&type=invcode&invcode="+invcode;
		getInfoBox(obj).innerHTML = "&nbsp;检测中，请稍等...";
		ajax.send(url,data,function(){
			var response = parseInt(ajax.request.responseText);
			switch (response){
				case 0:
					obj.noerror = 1;
					return showResult(0,obj);
					break;
				case 1:
					obj.noerror = 0;
					return showResult(1,obj);
					break;
				case 2:
					obj.noerror = 0;
					return showResult(2,obj);
					break;
				default:
					obj.noerror = 0;
					return showResult(2,obj);
			}
		});
	}else{
		obj.noerror = 0;
		return 1;
	}
}

function checkRegisterClause(obj){
	if(obj.checked == false) {
		obj.noerror = 0;
		return 1;
	} else {
		obj.noerror = 1;
		return 0;
	}
}

function checkField(obj,isSubmit){
	var field = obj.value;
	if (typeof(obj.id) != 'undefined' && obj.id == 'answered') {
		var safeQuestion = getObj('safequestion').value;
		if (safeQuestion > 0 && field == '') {
			obj.noerror = 0;
			showResult(1,obj);
			return isSubmit ? 0 : 1;
		} else {
			obj.noerror = 1;
			showResult(0,obj);
			return isSubmit ? 1 : 0;
		}
	}
	var isAreaType = obj.tagName == 'SELECT' && /^area_/.test(obj.id);
	if (isSubmit == true) {
		if (typeof(obj.id) == 'undefined') obj.id = obj.name.replace('[]','');
		if ((!isAreaType && field != '') || (isAreaType && field > 0)) {
			//field = field.replace(/&/g,'%26');
			//field = field.replace(/"/g,'%22');
			field = escape(field);
			ajaxCheckArray[obj.id] = {'noerror':obj.noerror,'type':'customerfield','value':obj.name.replace('[]','') + '|' + field};
		} else {
			if (eval(obj.id).d == 1) {
				obj.noerror = 0;
				showResult(1,obj);
				return 0;
			} else {
				obj.noerror = 1;
				showResult(0,obj);
				return 1;
			}
		}
		return;
	}
	var isCompanyType = obj.tagName == 'INPUT' && /^companyname_/.test(obj.id);
	if(field=="" || (isAreaType && field == -1)){
		var elementName;
		if(isAreaType || isCompanyType){
			var el = eval(obj.id);
		} else {
			var el = eval(obj.name);
		}
		if(el.d == 1) {
			obj.noerror = 0;
			showResult(1,obj);
			return 1;
		} else {
			obj.noerror = 1;
			showResult(0,obj);
			return false;
		}
	}else{
		var regx = /^(schoolname|companyname)/i;
		if (regx.test(obj.name) || isAreaType || isCompanyType) {
			obj.noerror = 1;
			showResult(0,obj);
			return 0;
		}
		var url 	= getRegAjaxCheckUrl();
		field = field.replace(/&/g,'%26');
		var data 	= "action=regcheck&type=customerfield&fieldname="+obj.name+"&value="+field;
		getInfoBox(obj).innerHTML = "&nbsp;检测中，请稍等...";
		getInfoBox(obj).style.display = '';
		ajax.send(url,data,function(){
			result = parseInt(ajax.request.responseText);
			if (result == 0) {
				obj.noerror = 1;
			} else {
				obj.noerror = 0;
			}
			showResult(result,obj);
		});
	}
}

function checkCheckbox(name) {
	var obj = eval(name);
	if (obj.d == 1) {
		var allElements = document.getElementsByName(name + '[]');
		var isChecked = 0;
		for (var i=0;i<allElements.length;i++) {
			if (allElements[i].checked == true) isChecked = 1;
		}
		var result = isChecked > 0 ? 0 : 1;
		showResult(result, name);
		return isChecked;
	} else {
		showResult(0, name);
		return 1;
	}
}

function checkRadio(name) {
	var obj = eval(name);
	if (obj.d == 1) {
		var allElements = document.getElementsByName(name);
		var isChecked = 0;
		for (var i=0;i<allElements.length;i++) {
			if (allElements[i].checked == true) isChecked = 1;
		}
		var result = isChecked > 0 ? 0 : 1;
		showResult(result, name);
		return isChecked;
	} else {
		showResult(0, name);
		return 1;
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
