function photoList(list,pid,aid){
	this.items	= new Array();
	this.list	= null;
	this.current= null;
	this.aid	= aid;
	this.init(list,pid);
	this.items.indexOf = function(v){
		for(var i = this.length; i-- && this[i] !== v;);
		return i;
	}
}
photoList.prototype = {
	init	: function(list,pid){
		list	= objCheck(list);
		this.list	= list;
		pid	= pid - 0;
		items	= list.getElementsByTagName('div');
		for (var i=0;i<items.length;i++) {
			var thisid	= items[i].id;
			var temp	= thisid.split('_');
			var thispid	= temp[1];
			thispid = thispid - 0;
			if (isNaN(thispid)) {
				continue;
			}
			if (this.current==null && thispid == pid) {
				this.current	= thisid;
			}
			this.items.push(thisid);
		}
	},

        refleshPre : function(prePid){
            var currentPid = this.current.split("_")[1];
            if(prePid == currentPid){
                showDialog('error','已经是第一张照片了',2);
                return false;
            }
            window.location = basename+"a=view&pid="+prePid;
        },

        refleshNext : function(nextPid){
            var currentPid = this.current.split("_")[1];
            if(nextPid == currentPid){
                showDialog('error','已经是最后一张照片了',2);
                return false;
            }
            window.location = basename+"a=view&pid="+nextPid;
        },

        refleshGroupsPre : function(prePid,cyid){
            var currentPid = this.current.split("_")[1];
            if(prePid == currentPid){
                showDialog('error','已经是第一张照片了',2);
                return false;
            }
            window.location = basename+"cyid="+cyid+"&a=view&pid="+prePid;
        },

        refleshGroupsNext : function(nextPid,cyid){
            var currentPid = this.current.split("_")[1];
            if(nextPid == currentPid){
                showDialog('error','已经是最后一张照片了',2);
                return false;
            }
            window.location = basename+"cyid="+cyid+"&a=view&pid="+nextPid;
        },

	goPre	: function(element){
		var nowindex	= this.items.indexOf(this.current);
		if (this.current == this.items[0]) {
			showDialog('error','已经是第一张照片了',2);
			return false;
		}
		if (nowindex>1) {
			getObj(this.getPreNodeId(this.current,2)).style.display = '';
			getObj(this.getNextNodeId(this.current,1)).style.display = 'none';
			this.current	= this.items[nowindex-1];
		} else {
			var handle	= this;
			var thisid	= this.items[nowindex-1];
			var temp	= thisid.split('_');
			var thispid	= temp[1] - 0;
			if (!isNaN(thispid)) {
				var temp_func = element.onclick;
				element.onclick = '';
				ajax.send(basename+'a=pre&pid='+thispid+'&aid='+handle.aid,'',function() {
					var rText = ajax.request.responseText.split('\t');
					if (rText[0] == 'ok') {
						if (rText[1]) {
							var newitem = JSONParse(rText[1]);
							var div	= handle.creatItem(newitem);
							handle.insertBefore(div);
							getObj(handle.getNextNodeId(handle.current,1)).style.display = 'none';
							handle.current	= handle.items[nowindex-1];
							handle.items.unshift('imglist_'+newitem['pid']);
						}
					} else if (rText[0] == 'begin') {
						handle.creatBegin();
						getObj(handle.getNextNodeId(handle.current,1)).style.display = 'none';
						handle.current	= handle.items[nowindex-1];
					} else {
						ajax.guide();
					}
					element.onclick = temp_func;
				});
			} else {
				showDialog('error','已经是第一张照片了',2);
			}
		}
	},
	goNext	: function(element){
		var nowindex	= this.items.indexOf(this.current);
		var itemslength	= this.items.length;
		if (this.current == this.items[itemslength-1]) {
			showDialog('error','已经是最后一张照片了',2);
			return false;
		}
		if (itemslength-nowindex>2) {
			getObj(this.getNextNodeId(this.current,2)).style.display = '';
			getObj(this.getPreNodeId(this.current,1)).style.display = 'none';
			this.current	= this.items[nowindex+1];
		} else {
			var handle	= this;
			var thisid	= this.items[nowindex+1];
			var temp	= thisid.split('_');
			var thispid	= temp[1] - 0;
			if (!isNaN(thispid)) {
				var temp_func = element.onclick;
				element.onclick = '';
				ajax.send(basename+'a=next&pid='+thispid+'&aid='+handle.aid,'',function() {
					var rText = ajax.request.responseText.split('\t');
					if (rText[0] == 'ok') {
						if (rText[1]) {
							var newitem = JSONParse(rText[1]);
							var div	= handle.creatItem(newitem);
							handle.insertEnd(div);
							getObj(handle.getPreNodeId(handle.current,1)).style.display = 'none';
							handle.current	= handle.items[nowindex+1];
							handle.items.push('imglist_'+newitem['pid']);
						}
					} else if (rText[0] == 'end') {
						handle.creatEnd();
						getObj(handle.getPreNodeId(handle.current,1)).style.display = 'none';
						handle.current	= handle.items[nowindex+1];
					} else {
						ajax.guide();
					}
					element.onclick = temp_func;
				});
			} else {
				showDialog('error','已经是最后一张照片了',2);
			}
		}
	},
	getNextNodeId : function(currentid,step){
		var items	= this.list.getElementsByTagName('div');
		var n	= 0;
		for (var i=0;i<items.length;i++) {
			if (!items[i].id) continue;
			if (n==step) {
				return items[i].id;
			}
			if (items[i].id==currentid || n>0) {
				n++;
			}
		}
		return false;
	},
	getPreNodeId : function(currentid,step){
		var items	= this.list.getElementsByTagName('div');
		var n	= 0;
		for (var i=items.length-1;i>=0;i--) {
			if (!items[i].id) continue;
			if (n==step) {
				return items[i].id;
			}
			if (items[i].id==currentid || n>0) {
				n++;
			}
		}
		return false;
	},
	creatItem	: function(obj){
		if (typeof(obj)!='object') {
			return false;
		}
		var pid = obj['pid'];
		var path	= char_cv(obj['path']);
		var div	= elementBind('div','imglist_'+pid,'img');
		div.innerHTML	= '<a href='+basename+'a=view&pid='+pid+'"><img src="'+path+'" onload="if(this.width>100){this.width = 100;} else if(this.height>100){this.height=100;}" /></a>';
		return div;
	},
	creatBegin	: function(){
		if (getObj('imglist_begin')) {
			getObj('imglist_begin').style.display = '';
			return false;
		} else {
			var div	= elementBind('div','imglist_begin','img');
			div.innerHTML	= '<img src="images/apps/pbegin.jpg" style="cursor:pointer;" />';
			this.insertBefore(div);
		}
	},
	creatEnd	: function(){
		if (getObj('imglist_end')) {
			getObj('imglist_end').style.display = '';
			return false;
		} else {
			var div	= elementBind('div','imglist_end','img');
			div.innerHTML	= '<img src="images/apps/pend.jpg" style="cursor:pointer;" />';
			this.insertEnd(div);
		}
	},
	insertBefore: function(div){
		var oldelement	= getObj(this.items[0]);
		this.list.insertBefore(div,oldelement);
	},
	insertEnd	: function(div){
		if (getObj('imglist_end')) {
			this.list.insertBefore(div,getObj('imglist_end'));
		} else {
			this.list.appendChild(div);
		}
	}
}