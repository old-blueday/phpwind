~
function()
{
	Form =
	{};
	var getObj = function(s)
	{
		return  document.getElementById(s);
	};
	/**
	 *@param string frm 表单的name值
	 */
	Form.validate = function(frm)
	{
		var ale = document[frm].elements;
		var submit = document[frm].onsubmit;
		function _e()
		{	
			submit ? submit() : 0;
			
			var error = 0;
			for (var i = 0,len = ale.length; i < len; i++)
			{
				ale[i].onblur ? ale[i].onblur() : 0;
				if (ale[i].getAttribute("hasError") == 1)
				{
					error++;
				}
			}
			if (error > 0)
			{
				document[frm].Submit.disabled = false;
				cnt = 0;
				return !! true;
			}
		}
		/**
		*这里不能用attachEvent来加事件，否则return  false也会提交表单
		*/
		//document[frm].onsubmit = _e;
		/**
		*设置提示信息，内部方法
		*@param nodeElement obj 节点
		*/
		function _setNotic(obj, pass, range)
		{
			var a = document.createElement("span");
			a.style.height = obj.offsetHeight + "px";
			a.id = "tip_" + obj.name;
			var newserror = obj.getAttribute("error");

			if (pass) {
				a.className = "msg pass";
				obj.setAttribute("hasError", 0);
				a.innerHTML = obj.getAttribute("pass") || getSuccessHtml();
			} else {
				a.className = "msg error";
				obj.setAttribute("hasError", 1);

				if (obj.value != '') {
					if (newserror == 'email_error' || newserror == 'email_error2') {
						newserror = 'Email格式错误';
					} else if (newserror == 'number_error' || newserror == 'number_error2') {
						newserror = '必须为整数';
					} else if (newserror == 'numberic_error' || newserror == 'numberic_error2') {
						newserror = '必须为数字类型';
					} else if (newserror == 'rang_error' || newserror == 'rang_error2') {
						newserror = '数字类型范围错误，取值为'+range[0]+'-'+range[1];
					}
				} else {
					newserror = '';
				}
				if (newserror) {
					a.innerHTML = getErrorHtml(newserror);
				} else {
					a.innerHTML = getErrorHtml('此项必填');
				}
			}
			
			if (obj.type == 'checkbox') {
				obj.parentNode.insertBefore(a, null);
			} else {
				obj.parentNode.insertBefore(a, obj.nextSibling);
			}
		}
		for (var i = 0,len = ale.length; i < len; i++)
		{
			var getc = ale[i].getAttribute("check");
			if (getc) {
				ale[i].onblur = function()
				{
					var getc = this.getAttribute("check");
					var a = getObj("tip_" + this.name);
					a ? a.parentNode.removeChild(a) : 0;
					var newerror = this.getAttribute("error");
					/**
					*如果是checkbox，则计算checkbox的个数并根据设置的属性check里的范围进行比较
					*/
					if (this.type == "checkbox")
					{
						var allCheckboxs = document.getElementsByName(this.name);
						var checked = 0;
						for (var j = 0,
						lens = allCheckboxs.length; j < lens; j++)
						{
							if (allCheckboxs[j].checked)
							{
								checked++;
							}
						}
						var chk = this.getAttribute("check");
						if (!chk) return;
					   
						/**
						*以"-"来划分最小值和最大值
						*/
						var mm = chk.split("-");
						var isTrue;
						mm[0] = Math.floor(mm[0]);
						mm[1] ? mm[1] = Math.floor(mm[1]) : 0;
						if (mm[1])
						{
							if (checked <= mm[1] && checked >= mm[0])
							{
								isTrue = true;
							} else
							{
								isTrue = false;
							}
						} else
						{
							if (checked >= mm[0])
							{
								isTrue = true;
							} else
							{
								isTrue = false;
							}
						}
						/**
						*最后一个checkbox后面才有验证提示
						*/
						var lastCheckbox = allCheckboxs[allCheckboxs.length - 1];
						_setNotic(lastCheckbox, isTrue);
						return  false;
					}
					/**
					*如果是以/开头的验证,则识别为正则验证，否则的话，则认为是范围的验证
					*/
					if (getc.indexOf("/") != 0)
					{
						var range = getc.split("-");
						if (newerror == 'rang_error2' && (this.value == '' || this.value == 0)) {
							isTrue = true;
						} else {
							var isTrue=	range[0] <= Math.floor(this.value) && range[1] >= Math.floor(this.value);
						}
					} else  {
					   try {
						var r = eval(getc);
						}
						catch (e) {
							var r = new RegExp();
						}
						if (newerror == 'email_error2' && (this.value == '' || this.value == 0)) {
							isTrue = true;
						} else if (newerror == 'number_error2' && (this.value == '' || this.value == 0)) {
							isTrue = true;
						} else if (newerror == 'numberic_error2' && (this.value == '' || this.value == 0)) {
							isTrue = true;
						} else {
							var testValue1 = this.value.split("\n");
							var testValue2 = this.value.split("\r\n");
							var isTrue1 = r.test(testValue1);
							var isTrue2 = r.test(testValue2);
							if (isTrue1 == true || isTrue2 == true) {
								isTrue = true;
							}
						}
					}
					_setNotic(this, isTrue, range);
				}
			}
		}
	};
} ();


function showError(obj, errorKey) {
	var message;
	if (errorKey) {
		message = getErrorHtmlByKey(errorKey);
	} else {
		message = getSuccessHtml();
	}
	showLineMessage(obj, message);
}
/*
 * 显示字段每行的提示信息
 * @param object obj 字段，如<input>
 * @param string message 提示的HTML
 */
function showLineMessage(obj, message) {
	var parentTrNode = getParentTrNode(obj, 3);
	if (parentTrNode.nodeName == 'TR') {
		var Td = parentTrNode.getElementsByTagName('TD');
		if (Td.length)
		{
			var lastTdNumber = Td.length-1;
			var lastTd = Td[lastTdNumber];
			var Span = lastTd.getElementsByTagName('span');
			if (Span.length) {
				var lastSpanNumber = Span.length-1;
				var lastSpan = Span[lastSpanNumber];
				if (lastSpan.className == 'notice') {
					lastSpan.innerHTML = message;
				} else {
					var a = document.createElement('span');
					a.className = 'notice';
					a.innerHTML = message;
					lastTd.insertBefore(a, lastTd.nextSibling);
				}
			} else {
				var a = document.createElement('span');
				a.className = 'notice';
				a.innerHTML = message;
				lastTd.insertBefore(a, lastTd.nextSibling);
			}
		}
	}
}
/*
 * 获取oject的顶上一个类型为TR的node
 * @param object obj 字段，如<input>
 * @param int depth 搜索层数
 * @return object TR的object
 */
function getParentTrNode(obj, depth) {
	if (depth == 0) {
		return obj;
	}
	if (obj.parentNode.nodeName == 'TR') {
		return obj.parentNode;
	} else {
		return getParentTrNode(obj.parentNode, depth - 1);
	}
}
/*
 * 获取正确的提示HTML
 * @return string HTML
 */
function getSuccessHtml() {
	return '<em class="aa_ok">正确</em>';
}
/*
 * 根据错误key获取正确的提示HTML
 * @param string key 错误的key
 * @param string value 错误相关的值
 * @return string HTML
 */
function getErrorHtmlByKey(key) {
	var message = getErrorMessageByKey(key);
	return getErrorHtml(message);
}
/*
 * 获取错误的提示HTML
 * @param string message 提示文字
 * @return string HTML
 */
function getErrorHtml(message) {
	return '<em class="aa_err">'+message+'</em>';
}

function _DateTotime (str) {
	var new_str = str.replace(/:/g,'-');
	new_str = new_str.replace(/ /g,'-');
	var arr = new_str.split("-");
	if (arr[3] || arr[4]) {
		var datum = new Date(Date.UTC(arr[0],arr[1]-1,arr[2],arr[3]-8,arr[4]));
	} else {
		var datum = new Date(Date.UTC(arr[0],arr[1]-1,arr[2]));
	}
	return datum.getTime()/1000;
}
function getCalendarError(obj, field) {
	var startTimestamp;
	var endTimestamp;
	if (field == 'starttime' || field == 'endtime') {
		startTimestamp = _DateTotime(getObj('calendar_starttime').value);
		endTimestamp = _DateTotime(getObj('calendar_endtime').value);
	} else if (field == 'signupstarttime' || field == 'signupendtime') {
		startTimestamp = _DateTotime(getObj('calendar_signupstarttime').value);
		endTimestamp = _DateTotime(getObj('calendar_signupendtime').value);
	} else {
		startTimestamp = endTimestamp = _DateTotime(obj.value);
	}
	if (!startTimestamp || !endTimestamp) {
		setErrorAttribute(obj, 1);
		return 'invalid_calendar';
	} else if (startTimestamp > endTimestamp) {
		setErrorAttribute(obj, 1);
		return 'start_time_later_than_end_time';
	} else if (field == 'signupendtime') {
		var activityStartTimestamp = _DateTotime(getObj('calendar_starttime').value);
		if (!activityStartTimestamp || endTimestamp > activityStartTimestamp) {
			setErrorAttribute(obj, 1);
			return 'signup_end_time_later_than_activity_start_time';
		}
	}
	setErrorAttribute(obj, 0);
	return false;
}

function getFeesMoneyError(obj, isMust) {
	var money = obj.value;
	var feesconditiondb = document.getElementsByName('act[fees][condition][]');
	var feesmoneydb = document.getElementsByName('act[fees][money][]');
	for (i = 0; i < feesmoneydb.length - 1; i++) {
		if (feesmoneydb[i].value > 0 && money == feesmoneydb[i].value && !feesconditiondb[i].value) {
			setErrorAttribute(obj, 1);
			return 'invalid_feescondition';
		} else {
			setErrorAttribute(obj, 0);
		}
	}
	money = parseFloat(money);
	if ((obj.value && !obj.value.match(/^(([1-9]\d*)|0)(\.\d{0,2})?$/)) || (isMust && !money) || (obj.value && money < 0)) {
		setErrorAttribute(obj, 1);
		return 'invalid_money';
	} else {
		setErrorAttribute(obj, 0);
		return false;
	}
}
function getMoneyError(obj, isMust) {
	var money = obj.value;
	money = parseFloat(money);
	if ((obj.value && !obj.value.match(/^(([1-9]\d*)|0)(\.\d{0,2})?$/)) || (isMust && money < 0) || (obj.value && (money < 0))) {
		setErrorAttribute(obj, 1);
		return 'invalid_money';
	} else {
		setErrorAttribute(obj, 0);
		return false;
	}
}
function getParticipantError(obj , peopleAlreadySignup) {
	
	var payMethod = parseInt(getPayMethod ());
	var maxParticipant = getMaxParticipant ();
	var minParticipant = getMinParticipant ();

	if ((minParticipant && (!isInt(minParticipant) || minParticipant < 0)) || (maxParticipant && (!isInt(maxParticipant) || maxParticipant < 0))) {
		setErrorAttribute(obj, 1);
		return 'invalid_participant_number';
	} else if (parseInt(maxParticipant) > 0 && parseInt(maxParticipant) < parseInt(minParticipant)) {
		setErrorAttribute(obj, 1);
		return 'minimum_larger_than_maximum';
	} else if (parseInt(maxParticipant) > 0 && parseInt(maxParticipant) < parseInt(peopleAlreadySignup)) {
		setErrorAttribute(obj, 1);
		return 'max_less_than_people_already_signup';
	} else {
		setErrorAttribute(obj, 0);
		return false;
	}
}
function getPayMethod () {
	var paymethods = document.forms['FORM'].elements['act[paymethod]'];
	if (paymethods) {
		var paymethod = 0;
		for (i=0; i<paymethods.length; i++) {
			if (paymethods[i].checked == true) {
				paymethod = paymethods[i].value;
			}
		}
		return paymethod;
	} else if (getObj('AlreadyPaid').value) {
		return getObj('AlreadyPaid').value;
	} else {
		return false;
	}
}
function getMaxParticipant () {
	return getObj('maxparticipant').value;
}
function getMinParticipant () {
	return getObj('minparticipant').value;
}
function getTelephoneError (obj) {
	var telephones = obj.value;
	if (!telephones.match(/^(([0-9p\(\)\-\+])[,，]?)+$/)) {
		setErrorAttribute(obj, 1);
		return 'invalid_telephone_format';
	} else {
		setErrorAttribute(obj, 0);
		return false;
	}
}
function getContactError (obj) {
	if (obj.value == '') {
		setErrorAttribute(obj, 1);
		return 'invalid_contact_format';
	} else {
		setErrorAttribute(obj, 0);
		return false;
	}
}
function isInt(x) {
	var y=parseInt(x);
	if (isNaN(y)) {
		return false;
	}
	return x==y && x.toString()==y.toString();
}

function setErrorAttribute(obj, value) {
	obj.setAttribute("hasError", value);
}