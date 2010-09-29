var drag = {
	init:function(mod,col,handle){
		this.modClass=mod;
		this.mods = this.getElementsByClassName(mod);
		var cols=[];
		if (col && col.length > 0) {
			var i=0;
			while (i < col.length) {
				cols = cols.concat(this.getElementsByClassName(col[i++]) );
			}
		}
		for (var i=0; i<cols.length; i++) {
			cols[i].x = cols[i].getBoundingClientRect().left;
		}
		this.cols = cols;
		for (var i = 0;i<this.mods.length;i++) {
			this.mods[i].getElementsByTagName(handle)[0].onmousedown=this.beginDrag;
			this.mods[i].getElementsByTagName(handle)[0].style.cursor = "move";
		}
		this.blank = document.createElement('div');
		this.blank.className = 'boxA';
		this.templayoutdata = this._getLayoutString();
	},
	getElementsByClassName:function(cn,parentNode){
		parentNode = parentNode||document;
		var rx=new RegExp('\\b'+cn+'\\b');
		var allT=parentNode.getElementsByTagName('*'), allCN=[], i=0, a;
		while(a=allT[i++]){
			rx.test(a.className)?allCN[allCN.length]=a:null;
		}
		return allCN;
	},
	beginDrag:function(e){
		if(drag.isDragging)
			return;
		e = e||event;
		drag.mouseRect = {x:e.clientX+ietruebody().scrollLeft,y:e.clientY+ietruebody().scrollTop};
		var node = e.srcElement||e.currentTarget;
		drag.cMod = node.parentNode;
		if (drag.cMod.className != drag.modClass) {
			return;
		}
		var rect = drag.cMod.getBoundingClientRect();
		drag.modRect ={x:rect.left+ietruebody().scrollLeft,y:rect.top+ietruebody().scrollTop};
		var modWidth=drag.cMod.offsetWidth-2;
		drag.cMod.parentNode.insertBefore(drag.blank,drag.cMod);
		document.body.insertBefore(drag.cMod, document.body.firstChild);
		drag.cMod.style.position = 'absolute';
		drag.cMod.style.width = modWidth-2+'px';
		drag.blank.style.height = drag.cMod.offsetHeight-2+'px';
		drag.cMod.style.zIndex = drag.cMod.style.zIndex + 1;
		drag.cMod.style.top = drag.modRect.y+'px';
		drag.cMod.style.left= drag.modRect.x+'px';
		document.onmousemove = drag.dragging;
		document.onmouseup = drag.drop;
		drag.isDragging = true;
	},
	dragging:function(e){
		e = e||event;
		var rect = {
			x:e.clientX+ietruebody().scrollLeft-drag.mouseRect.x+drag.modRect.x,
			y:e.clientY+ietruebody().scrollTop-drag.mouseRect.y+drag.modRect.y
		}
		drag.cMod.style.left = rect.x+'px';
		drag.cMod.style.top = rect.y+'px';
		drag.calculate(rect);
	},
	drop:function(e){
		document.onmousemove=null;
		document.onmouseup=null;
		drag.isDragging = false;
		var parent = drag.blank.parentNode;
		parent.insertBefore(drag.cMod,drag.blank);
		parent.removeChild(drag.blank);
		drag.cMod.style.position='';
		drag.cMod.style.width='';
		if (drag.chageLayout()) {
			drag.openGuide();
		} else {
			drag.closeGuide();
		}
	},
	openGuide:function() {
		getObj('modelupdate').style.display = '';
		if (getObj('modelguide')) {
			getObj('modelguide').style.display = 'none';
		}
	},
	closeGuide:function() {
		getObj('modelupdate').style.display = 'none';
		if (getObj('modelguide')) {
			getObj('modelguide').style.display = '';
		}
	},
	calculate:function(rect){
		//计算横向
		var i=0,j=0;
		while (i < this.cols.length) {
			if (rect.x < this.cols[i].x) {
				j = ( i>0 && (this.cols[i-1].x+this.cols[i].x)>2*rect.x )?i-1:i;
				break;
			}
			j=i,i++;
		}
		//获取所有的module
		var mods = getElementsByClassName(this.modClass,this.cols[j]);
		//过滤一下
		var col = this.cols[j];
		if (this.blank.parentNode != col) {
			col.appendChild(this.blank);
			this.cMod.style.width=this.blank.clientWidth-2+'px';
			this.blank.style.height=this.cMod.clientHeight-2+'px';
			mods.push(this.blank);
		}

		i=0;j=mods.length;var coltops=[];
		var offset = ietruebody().scrollTop;
		while (i < mods.length) {
			coltops[i] = mods[i].getBoundingClientRect().top+offset;
			if (rect.y < coltops[i] && mods[i].id != 'space_info') {
				j=i;
				break;
			}
			i++;
		}
		if (j < mods.length) {
			col.insertBefore(this.blank,mods[j]);
		} else {
			col.appendChild(this.blank);
		}
	},
	_getLayoutString : function() {
		var data = '';
		for (var i = 0; i < this.cols.length; i++) {
			var mods = getElementsByClassName(this.modClass, this.cols[i]);
			for (var j = 0; j < mods.length; j++) {
				data += 'param[' + i + '][' + j + ']=' + mods[j].id.substr(6) + '&';
			}
		}
		return data;
	},
	save : function() {
		var its = this;
		ajax.send('pw_ajax.php?action=spacelayout&' + this._getLayoutString(), '', function() {
			ajax.guide();
			its.closeGuide();
			its.templayoutdata = its._getLayoutString();
		});
	},
	chageLayout : function() {
		return (this._getLayoutString() != this.templayoutdata);
	},
	reset : function () {
		showDialog("confirm","你确定要取消吗？",0,function(){
			window.location.reload();
		});
	}
}