
function PwDrag(){
	this.drag  = false;
	this.move  = null;
	this.lastX = null;
	this.lastY = null;
	this.obj   = null;
	this.id    = null;
};

PwDrag.prototype = {

	_add : function(e){
		if(Drag.drag) return;
		if(is_ie){
			document.body.onselectstart = function(){
				return false;
			}
		}
		var e = is_ie ? window.event: e;
		var o = e.srcElement || e.target;
		Drag.id   = o.id;
		Drag.obj  = Drag._create();
		Drag.move = Drag._createMove();
		Drag._init(o);
		Drag.drag = true;
		document.body.appendChild(Drag.move);
		document.onmousemove = Drag._onDrag;
		document.onmouseup   = Drag._dragEnd;
		Drag.lastX = e.clientX - parseInt(Drag.move.style.left);
		Drag.lastY = e.clientY - parseInt(Drag.move.style.top);
	},

	_create : function(){
		if(IsElement('t_'+Drag.id)){
			getObj('t_'+Drag.id).parentNode.removeChild(getObj('t_'+Drag.id));
		}
		var o = document.createElement("div");
		o.id  = 't_'+Drag.id;
		o.className = 'moduleA';
		o.innerHTML = "<h3 onmousedown=\"Drag._move(event);return false;\"><a style=\"cursor:pointer\" onclick=\"send('action=edit&type="+Drag.id+"');\" class=\"editButton\">设置</a><a style=\"cursor:pointer\" class=\"editButton\" onclick=\"delMode('"+Drag.id+"')\">删除</a><a style=\"cursor:pointer\" class=\"editButton\" onclick=\"updateMode('"+Drag.id+"')\">更新</a>" + getObj(Drag.id).innerHTML + "</h3><div class=\"editBox\"><div class=\"editArea\"></div></div>";
		return o;
	},

	_createMove : function(){
		var o = document.createElement("div");
		o.id  = 'move';
		o.className = 'move moduleA';
		o.innerHTML = '<h3 onmousedown="Drag._move(event);return false;"><a href="" class="editButton">设置</a><a href="" class="editButton">删除</a><a href="" class="editButton">更新</a>' + getObj(Drag.id).innerHTML + '</h3><div class="editBox"><div class="editArea"></div></div>';
		o.style.zIndex = 2000;
		return o;
	},

	_init : function(o){
		if(Drag.move == null)
			return;
		var p = new Drag._fetchpos(o);
		Drag.move.style.left  = p.left + getLeft() - 1 + 'px';
		Drag.move.style.top   = p.top + getTop() - 1 + 'px';
		Drag.move.style.width = '258px';
	},

	_move : function(e){
		if(Drag.drag) return;
		var e = is_ie ? window.event: e;
		var o = e.srcElement || e.target;
		Drag.obj = o.parentNode;
		if(Drag.obj.tagName.toLowerCase() != 'div')
			return;

		if(is_ie){
			document.body.onselectstart = function(){
				return false;
			}
		}
		Drag.id  = Drag.obj.id.substr(2);
		
		Drag.move = Drag._createMove();
		Drag._init(Drag.obj);
		if(e.clientX - parseInt(Drag.move.style.left) > 250){
			Drag.move.style.left = e.clientX - 100 + 'px';
		}
	
		Drag.drag = true;
		document.body.appendChild(Drag.move);
		document.onmousemove = Drag._onDrag;
		document.onmouseup   = Drag._dragEnd;
		Drag.lastX = e.clientX - parseInt(Drag.move.style.left);
		Drag.lastY = e.clientY - parseInt(Drag.move.style.top);
	},

	_onDrag : function(e){
		if((!Drag.drag) || Drag.move == null){
			return;
		}
		var e = is_ie ? window.event: e;
		var x = e.clientX;
		var y = e.clientY;

		Drag.move.style.left = x - Drag.lastX + 'px';
		Drag.move.style.top  = y - Drag.lastY + 'px';
		Drag._dragMove(x,y);
	},

	_dragMove : function(x,y){//节点移动
		var o = getObj('no');
		var p = new Drag._fetchpos(o);

		if(x<p.left || x>p.left+p.width || y<p.top || y>p.top+p.height){
			return false;
		}
		var tds = o.getElementsByTagName('td');
		for(var i = 0;i < tds.length;i++){
			var pw = new Drag._fetchpos(tds[i]);

			if(x<pw.left || x>pw.left+pw.width || y<pw.top || y>pw.top+pw.height){
				continue;
			}
			var ds = tds[i].getElementsByTagName('div');

			if(ds.length>0){
				for(var j=0;j<ds.length;j++){
					var ph = new Drag._fetchpos(ds[j]);
					if(y>ph.top && y<ph.top+ph.height){
						ds[j].parentNode.insertBefore(Drag.obj,ds[j]);
						//Drag.move.style.width = Drag.obj.offsetWidth + 'px';
						return;
					}
				}
			}
			tds[i].appendChild(Drag.obj);
			//Drag.move.style.width = Drag.obj.offsetWidth + 'px';
			return;
		}
	},

	_dragEnd : function(){
		if(!Drag.drag){
			return;
		}
		Drag.drag = false;
		if(is_ie){
			document.body.onselectstart = function(){
				return true;
			}
		}
		document.body.removeChild(Drag.move);
		document.onmousemove = '';
		document.onmouseup = '';
		Drag.obj = null;
	},

	_fetchpos : function(obj){
		this.left = 0;
		this.top  = 0;
		this.width = obj.offsetWidth;
		this.height = obj.offsetHeight;
		if(obj.offsetParent){
			while(obj.offsetParent){
				this.left += obj.offsetLeft;
				this.top  += obj.offsetTop - obj.scrollTop;
				obj = obj.offsetParent;
			}
		} else if(obj.x){
			this.left += obj.x;
			this.top  += obj.y;
		}
		this.left -= getLeft();
		this.top  -= getTop();
	}
}

var Drag  = new PwDrag();