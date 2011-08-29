/*
 * 搜索引擎javascript服务类
 * @author liuhui 2010-4-22
 * @version phpwind 8.0
 */
var searcher = {
	_advanced : ['thread','diary'],
	start : 0,
	$ : function(id){
		return document.getElementById(id);
	},
	/* 获取搜索类型 */
	_getSearchType : function(){
		var obj = this.$("searchType");
		if(!obj){return ;}
		return obj.getElementsByTagName("LI");
	},
	/* 绑定搜索类型点击事件 */
	_bindTypeClick : function(){
		var elements = this._getSearchType();
		if(!elements){return ;}
		var _this = this;
		for(i=0;i<elements.length;i++){
			elements[i].onclick=function(){
				_this._typeClick(this);
				var mainbox = _this.$("mainbox");
				if(mainbox){
					mainbox.style.display = "none";
				}
				_this._resetAdvanced("none");
			}
		}
	},
	/* 绑定高级链接点击事件 */
	_bindAdvanced : function(){
		var advanced = this.$("searchAdvanced");
		var close = this.$("close");
		var close2 = this.$("close2");
		if(!advanced || !close || !close2){return ;}
		var _this = this;
		advanced.onclick=function(){
			if (getObj('advancedThread').style.display != 'none' || getObj('advancedDiary').style.display != 'none'){
				_this._resetAdvanced("none");
				if (getObj('Calendar')) {
					HiddenCalendar();
				}
				advanced.innerHTML = '高级搜索';
				return;
			}
			_this._hiddenAdvanced();
			advanced.innerHTML = '普通搜索';
		}
		close.onclick = function(){
			_this._resetAdvanced("none");
			if (getObj('Calendar')) {
				HiddenCalendar();
			}
			advanced.innerHTML = '高级搜索';
		}
		close2.onclick = function(){
			_this._resetAdvanced("none");
			if (getObj('Calendar')) {
				HiddenCalendar();
			}
			advanced.innerHTML = '高级搜索';
		}
	},
	/* 绑定日历点击事件 */
	_bindCalendar : function(){
		this.$("starttime").onclick=function(){
			ShowCalendar("starttime");
		}
		this.$("endtime").onclick=function(){
			ShowCalendar("endtime");
		}
		this.$("ts").onclick=function(){
			ShowCalendar("starttime");
		}
		this.$("te").onclick=function(){
			ShowCalendar("endtime");
		}
		this.$("diarystarttime").onclick=function(){
			ShowCalendar("diarystarttime");
		}
		this.$("diaryendtime").onclick=function(){
			ShowCalendar("diaryendtime");
		}
		this.$("ts2").onclick=function(){
			ShowCalendar("diarystarttime");
		}
		this.$("te2").onclick=function(){
			ShowCalendar("diaryendtime");
		}
		
	},
	/* 初始化搜索类型点击事件 */
	_initTypeClick : function(type){
		if(!type){return ;}
		var elements = this._getSearchType();
		if(!elements){return ;}
		var _this = this;
		for(i=0;i<elements.length;i++){
			var t = elements[i].id;
			if(t == type){
				_this._typeClick(elements[i]);
			}
		}
	},
	/* 搜索类型点击事件 */
	_typeClick : function(obj){
//		this._resetClass();
//		obj.className = "current";
		var type = obj.id;
		var help = this.$("searchAdvanced");//help
		var advanced = this._advanced.toString();
		if(advanced.indexOf(type) >= 0 ){
			help.style.display = "";
		}else{
			help.style.display = "none";
		}
		this.$("hiddenType").value = type || 'thread';
		this._hiddenCalendar();
		var _this = this;
		setTimeout(function(){
			_this.searchSubmit();
		},0);
	},
	
	searchSubmit : function (){
		//if(this.start > 0){
			this.$("searchform").submit();
		//}
		this.start++;
	},
	
	/* 重置搜索类型class值 */
	_resetClass : function (){
		var elements = this._getSearchType();
		if(!elements){return ;}
		for(i=0;i<elements.length;i++){
			//elements[i].className = "";
		}
	},
	/* 隐藏高级表单函数 */
	_hiddenAdvanced : function(){
		var current = this.$("hiddenType").value;
		if(current == "thread"){
			this.$("advancedThread").style.display = "";
			this.$("advancedDiary").style.display = "none";
		}else if(current == "diary"){
			this.$("advancedThread").style.display = "none";
			this.$("advancedDiary").style.display = "";
		}else{
			this.$("advancedThread").style.display = "none";
			this.$("advancedDiary").style.display = "none";
		}
		return false;
	},
	/* 重置高级表单 */
	_resetAdvanced : function(v){
		var r =  this.$("hiddenType").value;
		if(!r){return ;}
		this.$("advancedThread").style.display=v;
		this.$("advancedDiary").style.display=v;
	},
	/* 隐藏日历函数 */
	_hiddenCalendar : function(){
		var c = this.$('Calendar');
		if(c){
			c.style.visibility='hidden';
		}
	},
	
	_showTable : function(id){
		var o = this.$(id);
		if(!o) return false;
		o.style.display = "";
		var other = (id == 'ttable') ? 'ptable' : 'ttable';
		var obj = this.$(other);
		if(!obj) return false;
		obj.style.display = "none";
	},
	
	/* 初始化搜索事件 */
	init : function(type){
		this._initTypeClick(type);
		this._bindTypeClick();
		this._bindAdvanced();
		this._bindCalendar();
	}
}
var searcherInit = function(type){
	searcher.init(type);
}
var searchertable = function(id){
	searcher._showTable(id);
}
//搜索框添加焦点样式@by chenchaoqun
/*var keyword1 = document.getElementById('searchform').keyword;
keyword1.onfocus = function() {
	this.className = this.className + ' inputFocus';
}
keyword1.onblur = function(){
	this.className = this.className.replace(' inputFocus','');
}
if(document.getElementById('searchformbottom')){
	keyword2 = document.getElementById('searchformbottom').keyword
	keyword2.onfocus = function(){keyword1.onfocus.call(this);}
	keyword2.onblur = function(){keyword1.onblur.call(this);}
}*/