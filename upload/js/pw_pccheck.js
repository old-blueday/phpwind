/**
 *表单验证。
 *使用方法：Form.validate(Form_name)  Form_name:表单的name值
 *
 */
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
                return  !! showDialog('error', '标题、内容不能为空或者有其他错误项',2);
            }
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
        function getCalendarError() {
        	var startTimestamp;
        	var endTimestamp;
        	try {
        		var newserror = getObj('tip_postcate[endtime]');
        		startTimestamp = _DateTotime(getObj('calendar_begintime').value);
        		endTimestamp = _DateTotime(getObj('calendar_endtime').value);
        	}catch(e){}
        	if(startTimestamp > endTimestamp) {
        		newserror.className = "msg error";
        		newserror.innerHTML='开始时间不能早于结束时间';
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
            var a = document.createElement("div");
            //a.style.height = obj.offsetHeight + "px";
            a.id = "tip_" + obj.name;
			var newserror = obj.getAttribute("error");
            if (pass) {
                a.className = "msg pass";
                obj.setAttribute("hasError", 0);
                a.innerHTML = obj.getAttribute("pass") || "&nbsp;";
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

                a.innerHTML = newserror || "此项必填";
            }
			
			if (obj.type == 'checkbox') {
				obj.parentNode.insertBefore(a, null);
			} else {
				obj.parentNode.insertBefore(a, obj.nextSibling);
			}
            
        }
        for (var i = 0,len = ale.length; i < len; i++)
        {
            ale[i].onblur = function()
            {
                var getc = this.getAttribute("check");
                if (getc)
                {
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
							if (range[0] == 'n' || range[1] == 'n') {
								if (range[0] == 'n' && range[1] != 'n') {
									isTrue = range[1] >= Math.floor(this.value) ? true : false;
								} else if (range[0] != 'n' && range[1] == 'n') {
									isTrue = range[0] <= Math.floor(this.value) ? true : false;
								} else {
									regstr = /^\d+$/;
									isTrue = regstr.test(this.value) ? true : false;
								}
							} else {
								var isTrue=	this.value != '' && range[0] <= Math.floor(this.value) && range[1] >= Math.floor(this.value);
							}
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
                    getCalendarError();
                }
            }
        }
    };
} ();