var jobCenterClass = {
	/*前台任务类 @2009-11-30 lh*/
	prefixApply   : "apply_",
	prefixStart   : "start_",
	prefixQuit    : "quit_",
	prefixGain    : "gain_",
	prefixTbody   : "job_",
	prefixApplied : "applied_",
	
	$ : function(id){
		return document.getElementById(id);
	},

	/*申请提交事件*/
	submitApply : function(id){
		var verify = verifyhash || "";
		var url = 'jobcenter.php?action=apply&verify=' + verify;;
		//this.submit(url,id);
		var _this = this;
		ajax.send(url, "&id="+id+"&step=2",function() {
			var response = _this.convert(ajax.request.responseText);
			if(response['flag']){
				showDialog("success",response['message'],2);
				var obj = _this.$(_this.prefixApply+id);
				obj.className = "tasks_apply_old";
				//response['html']
				var tab = _this.$("tasktab_1");/**/
				if( tab && response['html']){
					tab.innerHTML = response['html'];
					_this.init();
					new PW.accordion('jobpop_h','taskA_dl','tasktab_1');
				}
				obj.onclick = function(){}
			}else{
				showDialog("error",response['message']);
			}
		});
		
	},
	
	/*提示信息*/
	convert : function(response){
		return eval(response)[0];
	},
	
	
	/*绑定申请点击事件*/
	bindApplyClick : function(){
		var e = this.IDSelector("a",this.prefixApply) || [];
		var _this = this;
		for(i=0;i<e.length;i++){
			e[i].onclick = function(){
				var id = this.id.split("_")[1];
				//_this.hiddenPOP();
				_this.submitApply(id);
			}
		}
	},
	/*绑定立即开始点击事件*/
	bindStartClick : function(){
		var e = this.IDSelector("a",this.prefixStart) || [];
		var _this = this;
		for(i=0;i<e.length;i++){
			e[i].onclick = function(){
				var id = this.id.split("_")[1];
				var job = this.getAttribute("job");
				var _obj = this;
				_this.setCookie(1,function(){
					_this.submitStart(id, job,_obj);
				});
				return false;
			}
		}
	},
	
	submitStart : function(id,job,start){
		if(!start){
			return false;
		}
		var link = start.getAttribute("link");
		var url = 'jobcenter.php?action=start&id='+id;
		var headbase = this.$("headbase");
		var baseUrl = headbase.getAttribute("href");
		if(!link){
			ajax.send(url, "&ajax=1");
			var c = this.$("job_condition_"+id);
			var message = c ? c.innerHTML : "按指定任务条件完成即可获取奖励";
			showDialog("success",message,2);
			return false;
		}
		this.hiddenPOP();
		var verify = verifyhash || "";
		window.location.href = baseUrl+url;
	},
	
	/*绑定获取奖励点击事件*/
	bindGainClick : function(){
		var e = this.IDSelector("a",this.prefixGain) || [];
		var _this = this;
		for(i=0;i<e.length;i++){
			e[i].onclick = function(){
				var id = this.id.split("_")[1];
				_this.submitGain(id);
				return false;
			}
		}
	},
	
	
	submitGain : function(id){
		var verify = verifyhash || "";
		var url = 'jobcenter.php?action=gain&verify=' + verify;;
		var _this = this;
		ajax.send(url, "&id="+id+"&step=2",function() {
			var response = _this.convert(ajax.request.responseText);
			if(response['flag']){
				showDialog("success",response['message'],2);
				var obj = _this.$(_this.prefixTbody+id);
				if(obj){
					obj.parentNode.removeChild(obj);
				}
				var obj2 = _this.$(_this.prefixApplied+id);
				if(obj2){
					obj2.parentNode.removeChild(obj2);
				}
				//response['html']
				var tab = _this.$("tasktab_1");/**/
				if( tab && response['html']){
					tab.innerHTML = response['html'];
					_this.init();
					new PW.accordion('jobpop_h','taskA_dl','tasktab_1');
				}
			}else{
				showDialog("error",response['message']);
			}
		});
		
	},
	
	/*绑定放弃点击事件*/
	bindQuitClick : function(){
		var e = this.IDSelector("a",this.prefixQuit) || [];
		var _this = this;
		for(i=0;i<e.length;i++){
			e[i].onclick = function(){
				var id = this.id.split("_")[1];
				var info = this.getAttribute("info");
				_this.showConfirm(id, info);
			}
		}
	},
	
	showConfirm : function(id, info){
		var info = info || "是否确认放弃本次任务";
		var _this = this;
		showDialog({type:"confirm",message:info,onOk:function(){
			_this.submitQuit(id);
		}});
	},
	
	submitQuit : function(id){
		var verify = verifyhash || "";
		var url = 'jobcenter.php?action=quit&verify=' + verify;;
		var _this = this;
		ajax.send(url, "&id="+id+"&step=2",function() {
			var response = _this.convert(ajax.request.responseText);
			if(response['flag']){
				showDialog("success",response['message'],2);
				/*移除对象*/
				var obj = _this.$(_this.prefixTbody+id);
				obj.parentNode.removeChild(obj);
			}else{
				showDialog("error",response['message']);
			}
		});
	},
	
	/*表单提交*/
	submit : function(url,id){
		var form = document.createElement("form");
		form.action = url
		form.method = "post";
		var idInput = this.createInput("hidden","id",id);
		var actionInput = this.createInput("hidden","step","2");
		form.appendChild(idInput);
		form.appendChild(actionInput);
		document.body.appendChild(form);
		setTimeout(function(){/*ie6*/
			form.submit();
			document.body.removeChild(form);
		},0);
		return;
	},

	/*id 选择器*/
	IDSelector : function(tagName,find){
		var tags = document.getElementsByTagName(tagName);
		var elements = new Array();
		for(i=0;i<tags.length;i++){
			var id = tags[i].id;
			if(id.indexOf(find) >= 0 ){
				elements.push(tags[i]);
			}
		}
		return elements;
	},
	/*创建表单元素*/
	createInput : function(type,name,value){
		var hidden = document.createElement("input");
		hidden.type = type;
		hidden.name = name;
		hidden.value = value;
		return hidden;
	},
	
	bindNavClick : function(){
		var navs = this.$("jobnav").getElementsByTagName("li") || [];
		var _this = this;
		for(i=0;i<navs.length;i++){
			navs[i].onclick = function(){
				_this.resetNav();
				this.className = "current";
			}
		}
	},
	
	resetNav : function(){
		var navs = this.$("jobnav").getElementsByTagName("li") || [];
		for(i=0;i<navs.length;i++){
			navs[i].className = "";
		}
	},
	
	hiddenPOP : function(){
		var pop = this.$("jobpop") || 0;
		pop ? pop.style.display='none' : 0;
	},
	
	bindCloseClick : function (){
		var close = this.$("close");
		if(!close){
			return false;
		}
		var _this = this;
		close.onclick = function(){
			_this.hiddenPOP();
			_this.setCookie(1,function(){});
			return false;
		}
	},
	
	bindGoToCenter : function(){
		var obj = this.$("gotocenter");
		if(!obj){
			return false;
		}
		var _this = this;
		var headbase = this.$("headbase");
		var baseUrl = headbase.getAttribute("href");
		obj.onclick = function(){
			_this.hiddenPOP();
			_this.setCookie(1,function(){
				window.location.href = baseUrl+"jobcenter.php";
			});
			return false;
		};
	},
	
	setCookie : function(v,func){
		var url = 'pw_ajax.php?action=jobpop';
		ajax.send(url, "&job=cookie&v="+v,function(){
			func ? func() : 0;
		});
	},
	
	init : function(){
		this.bindApplyClick();
		this.bindStartClick();
		this.bindQuitClick();
		this.bindGainClick();
		this.bindCloseClick();
		this.bindGoToCenter();
	}
	
}
/*初始化任务中心*/
function jobCenterInit(){
	jobCenterClass.init();
}
/*运行任务中心js*/
function jobCenterRun(html){
	var obj = document.createElement("div");
	obj.innerHTML = html;
	document.body.appendChild(obj);
	jobCenterInit();
	new PW.accordion('jobpop_h','taskA_dl','tasktab_1');
	new PW.accordion('jobpop_h','taskA_dl','tasktab_2');
	new PW.tab('dopen','tasktab_1');
}


function array_indexOf(arr, elt) {
	var len = arr.length >>> 0;

    var from = Number(arguments[2]) || 0;
    from = (from < 0)
         ? Math.ceil(from)
         : Math.floor(from);
    if (from < 0)
      from += len;

    for (; from < len; from++)
    {
      if (from in arr &&
          arr[from] === elt)
        return from;
    }
    return -1;
}

var PW ={};
PW.hSlide = function(e,h)
{
	setTimeout(function(){
		var _nh;
		if (e.style.display == 'none') {
			_nh = 0;
			e.style.height = '0px';
			e.style.display = '';
		}
		else {
			_nh = (e.style.height === '') ? parseInt(e.scrollHeight || e.clientHeight || e.offsetHeight) : parseInt(e.style.height);
		}
		var _ch = h?parseInt(e.scrollHeight||e.clientHeight||e.offsetHeight):0;
		
		_rh = (_nh+_ch)/2;
		if (Math.abs(_rh-_ch)<10)
		{
				e.style.height = '';
				h||(e.style.display = 'none');
		}
		else
		{
			e.style.height = _rh+'px';
			PW.hSlide(e,h);
		}
	},50);
};
PW.accordion = function(t,c,id){
	var tt = getElementsByClassName(t,id);
	var cc = getElementsByClassName(c,id);
	this.index = 0;
	for(var i=tt.length-1;i>-1;--i)
	{
		//cc[i].style.overflow='hidden';
		if(i>0)
			tt[i].className='jobpop_h';
		else
			cc[i].style.display = '';
		var self = this;
		//cc[i].style.overflow = 'hidden';
		tt[i].onclick = function(){
			//var j = tt.indexOf(this);
			var j = array_indexOf(tt, this);
			//_rh = (cc[j].scrollHeight||cc[j].clientHeight||cc[j].offsetHeight);
			if(self.index != j)
			{
				cc[self.index].style.display = 'none';
				cc[j].style.display = '';
				tt[j].className='jobpop_h current';
				tt[self.index].className='jobpop_h';
				//PW.hSlide(cc[this.index],0);
				//PW.hSlide(cc[j],_rh);
				self.index = j;
			}
		}
	}
};

PW.tab = function(t,c,id){
	var tt = getElementsByClassName(t,id);
	var cc = getElementsByClassName(c,id);
	this.index = 0;
	for(var i=tt.length-1;i>-1;--i)
	{
		//cc[i].style.overflow='hidden';
		if(i>0)
		{
			cc[i].style.display = 'none';
			//cc[i].style.height = 0;
		}
		var self = this;
		//cc[i].style.overflow = 'hidden';
		tt[i].onclick = function(){
			//var j = tt.indexOf(this);
			var j = array_indexOf(tt, this);
			//_rh = (cc[j].scrollHeight||cc[j].clientHeight||cc[j].offsetHeight);
			if(self.index != j)
			{
				cc[j].style.display = '';
				 cc[self.index].style.display = 'none';
				 tt[j].className = 'dopen current';
				 tt[self.index].className='dopen';
				//PW.hSlide(cc[this.index],0);
				//PW.hSlide(cc[j],_rh);
				self.index = j;
			}
		}
	}
};