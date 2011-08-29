var adminjobclass = {
	/*后台用户任务系统类 @2009-11-27 lh*/
	
	rewardid     : "reward",
	joblistid    : "joblist",
	prefix       : "job_",
	joballid     : "joball",
	current      : null,
	rewardTables : "rewardTables",
	jobtables    : "jobTables",
	sid          : "starttime",
	eid          : "endtime",
	
	/*ID选择器*/
	$ : function(id){
		return document.getElementById(id);
	},
	/*绑定任务奖励*/
	bindReward : function(id){
		var lists = this.$(this.rewardid).getElementsByTagName("li");
		var _this = this;
		for(i=0;i<lists.length;i++){
			var input = lists[i].getElementsByTagName("input")[0];/*单选表单*/
			input.onclick = function(){
				if(this.checked){
					var current = _this.$(_this.rewardid+"_"+this.value);
					_this.hiddenReward();
					current.style.display = "";
				}
			}
		}
	},
	/*隐藏任务奖励*/
	hiddenReward : function(){
		//var ids = ['reward_none','reward_credit','reward_tools','reward_medal','reward_usergroup','reward_invitecode'];
		var divs = this.$(this.rewardTables).getElementsByTagName("div");
		for(i=0;i<divs.length;i++){
			divs[i].style.display = "none";
		}
		return true;
	},
	/*初始化任务奖励*/
	initReward : function(id){
		var obj = this.$(id);
		if(obj){
			obj.style.display = "";
		}
	},
	/*绑定任务类事件*/
	bindJobList : function(){
		var lists = this.$(this.joblistid).getElementsByTagName("li");
		var _this = this;
		for(i=0;i<lists.length;i++){
			//lists[i].onmouseover = function(){
			lists[i].onclick = function(){
				var id = _this.prefix+this.getAttribute("id");
				var jobs = _this.$(id);
				_this.hiddenJobList();
				jobs.style.display = "";
				this.className = "current";
			}
		}
	},
	/*绑定每个具体任务的设置*/
	bindJob : function(){
		var uls = this.$(this.joballid).getElementsByTagName("ul");
		var _this = this;
		for(i=0;i<uls.length;i++){/*最大层ul列表*/
			var lis = uls[i].getElementsByTagName("li");/*li列表*/
			for(j=0;j<lis.length;j++){
				var input = lis[j].getElementsByTagName("input")[0];/*单选表单*/
				input.onclick = function(){
					if(this.checked){
						var id = _this.prefix+this.value;
						var jobs = _this.$(id);
						_this.hiddenJobTables();
						jobs.style.display = "";
					}
				}
			}
		}
	},
	/*隐藏任务设置*/
	hiddenJobTables : function(){
		var tables = this.$(this.jobtables).getElementsByTagName("table");
		for(i=0;i<tables.length;i++){
			tables[i].style.display = "none";
		}
		return true;
	},
	/*隐藏任务菜单*/
	hiddenJobList : function(){
		var uls = this.$(this.joballid).getElementsByTagName("ul");
		for(i=0;i<uls.length;i++){
			uls[i].style.display = "none";
		}
		var lis = this.$(this.joblistid).getElementsByTagName("li");
		for(i=0;i<lis.length;i++){
			lis[i].className = "";
		}
		return true;
	},
	/*初始化菜单*/
	initJobList : function(id){
		this.$(this.prefix+id).style.display = "";
		this.$(id).className = "current";
	},
	/*初始化任务配置*/
	initJobTable : function(id){
		this.$(id).style.display = "";
	},
	/*绑定时间日历*/
	bindCalendar : function (){
		var _this = this;
		this.$(this.sid).onclick = function(){
			ShowCalendar(_this.sid,1);
		}
		this.$(this.eid).onclick = function(){
			ShowCalendar(_this.eid,1);
		}		
	},
	/*初始化*/
	init : function(reward,list,job,isuserguide){
		if (isuserguide != '1') {
			this.bindJobList();
			this.bindJob();
		}
		this.bindReward();
		this.initReward(reward);/*初始化选择*/
		this.initJobList(list);/*初始化*/
		this.initJobTable(job);/*初始化任务配置*/
		this.bindCalendar();
	},
	
	deleteJob : function(url,id){
		if(confirm("删除任务后，将删除所有与该任务关联的数据。是否确认删除？")){
			var form = document.createElement("form");
			form.action = url;
			form.method = "post";
			var input_id = this.createInput("hidden","id",id);
			var input_action = this.createInput("hidden","action","delete");
			form.appendChild(input_id);
			form.appendChild(input_action);
			document.body.appendChild(form);
			setTimeout(function(){/*ie6*/
				form.submit();
				document.body.removeChild(form);
			},0);
		}
	},
	
	createInput : function(type,name,value){
		var hidden = document.createElement("input");
		hidden.type = type;
		hidden.name = name;
		hidden.value = value;
		return hidden;
	}
	
}

/*公用调用自动化加载事件*/
function adminJobReady(reward,list,job,isuserguide){
	adminjobclass.init(reward,list,job,isuserguide);
}

function deleteJob(url,id){
	adminjobclass.deleteJob(url,id);
	return true;
}
























