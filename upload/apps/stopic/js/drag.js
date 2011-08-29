var drag = Class({},{
	Create	: function (container,item,head,edit,close,content,pack,closeAble) {
		this.containerClass = container;
		this.itemClass	= item;
		this.headClass	= head;
		this.editClass	= edit;
		this.closeClass	= close;
		this.contentClass	= content;
		this.closeAbleClass	= closeAble;
		this.packId	= pack;
		if (is_ie) {//注释以下代码,在IE中无法选中文本框中的文字 by chenchaoqun
			//document.body.onselectstart = function(){
				//return false;
			//}
		}
		this.ie6 = !window.xmlHttp;
		this.webkit = navigator.userAgent.toLowerCase().indexOf('webkit')>-1;
		this.gecko = navigator.userAgent.toLowerCase().indexOf('gecko')>-1;
		this._initContainers();
		this._initItems();
		this._initItemHeads();
		this._initCloses();
	},
	getItems	: function () {
		return this.items;
	},

	in_Items	: function (id) {
		for (var i=0;i<this.items.length;i++) {
			if (id==this.items[i]) {
				return true;
			}
		}
		return false;
	},
	
	setItemTitle	: function (item,title) {
		var head	= getElementsByClassName(this.headClass,item);
	},
	
	setItemContent	: function (item,html) {
		var content	= getElementsByClassName(this.contentClass,item);
		for (var i=0;i<content.length;i++) {
			content[i].innerHTML = html;
		}
	},
	setItemTitle	: function (item,title) {
		var header	= getElementsByClassName(this.headClass,item);
		var titlespan	= header[0].getElementsByTagName('span');
		titlespan[0].innerHTML	= title;
	},
	
	getStopicConfig	: function () {
		var config	= '';
		for (var i=0;i<this.containers.length;i++) {
			config += this._getContainerItems(this.containers[i]);
		}
		return config;
	},

	_getContainerItems	: function (container) {
		var temp = '';
		var items	= getElementsByClassName(this.itemClass,container);
		for (var i=0;i<items.length;i++) {
			temp += '&block_config['+container.id+']['+i+']='+items[i].id;
		}
		return temp;
	},
	
	_initContainers	: function () {
		var containers	= getElementsByClassName(this.containerClass);
		if (typeof(containers)=='undefined') {
			alert('no such container');
			return false;
		}
		this.containers = containers;
	},
	_initItems	: function () {
		this.items	= Array();
		var items	= getElementsByClassName(this.itemClass);
		for (var i=0;i<items.length;i++) {
			this.items.push(items[i].id);
		}
	},
	_initItemHeads	: function () {
		var heads	= getElementsByClassName(this.headClass);
		var self	= this;
		for (var i = 0; i<heads.length; i++) {
			heads[i].onmousedown = function (e) {
				self._mousedown(e);
			}
		}
	},
	_initCloses	: function () {
		var closes	= getElementsByClassName(this.closeClass);
		var self	= this;
		for (var i = 0; i<closes.length; i++) {
			closes[i].onclick = function (e) {
				self._removeItem(e);
			}
		}
	},

	_initEdits	: function () {
		var edits	= getElementsByClassName(this.editClass);
		var self	= this;
		for (var i = 0; i<closes.length; i++) {
			closes[i].onclick = function (e) {
				self._editContent(e);
			}
		}
	},

	_editContent	: function (o) {
		var itemid = this._getItemParentId(o);
		var stopic_id = STOPIC_ID ? STOPIC_ID : getObj('stopicId').value;
		ajax.send(AJAXURL,'ajax=1&job=editblock&block_id='+itemid+'&stopic_id='+stopic_id,ajax.get);
	},

	_removeItem	: function (o) {
		if (!confirm('确定要删除该内容块么？')) return false;
		
		var itemid = this._getItemParentId(o);
		if (itemid==false) {
			return false;
		}
		delElement(itemid);
		for (var i=0;i<this.items.length;i++) {
			if (this.items[i]==itemid) {
				this.items.splice(i,1);
				break;
			}
		}
	},
	_getItemParentId	: function (o) {
		while (o) {
			var classNames = o.className.split(' ');
			for (i=0; i<classNames.length; i++) {
				for (j=0; j<this.closeAbleClass.length; j++) {
					if (classNames[i]==this.closeAbleClass[j]) {
						return o.id;
					}
				}
			}
			o = o.parentNode;
		}
		return false;
	},

	_mousedown	: function(e){
		var self = this;
		var e = is_ie ? window.event: e;
		var o = e.srcElement || e.target;

		if (o.className==this.closeClass) {
			this._removeItem(o);
		} else if (o.className==this.editClass) {
			setTimeout(function() {self._editContent(o);}, 200);
		} else {
			if (o.tagName.toLowerCase()=='span') o = o.parentNode;
			this._mousedownInit(e,o);
			document.onmousemove = function(event){
				self._onDrag(event);
			};
			document.onmouseup   = function(event){
				self._dragEnd(event);
			};
		}
	},

	_onDrag	: function (e) {
		var e = is_ie ? window.event: e;
		this.currentItem.style.left = e.clientX - this.lastX + 'px';
		var isFixed = document.getElementById('zt_topbar').style.position=='fixed';
		this.currentItem.style.top  = e.clientY - this.lastY + 'px';
		this._moveHelp(e);
	},

	_dragEnd	: function (e) {
		var e = is_ie ? window.event: e;
		if (this.help) {
			this.currentItem.style.position = 'static';
			this.currentItem.style.width	= 'auto';
			this.currentItem.style.height	= 'auto';
			this.currentItem.style.zIndex	= 0;
			this._addItems(this.currentItem.id);
			this._replaceNode(this.help,this.currentItem);
			this.help = '';
		} else {
			delElement(this.currentItem);
		}
		this.currentItem = '';
		document.onmousemove = '';
		document.onmouseup = '';
	},
	_addItems	: function (id) {
		if (this.in_Items(id)==false) {
			this.items.push(id);
		}
	},
	_replaceNode	: function (oldnode,newnode) {
		if (is_ie) {
			oldnode.replaceNode(newnode);
		} else {
			oldnode.parentNode.replaceChild(newnode,oldnode);
		}
	},

	_moveHelp	: function (e) {
		eventContainer = this._getEventContainer(e);
		if (eventContainer == false) return;
		currentIndex	= this._getEventIndex(eventContainer,e);
		if (!this.help){
			this.help = this._createHelpDiv();
		}
		if (currentIndex == false) {
			eventContainer.id.appendChild(this.help);
		} else {
			eventContainer.id.insertBefore(this.help,currentIndex);
		}
	},

	_getEventContainer	: function (e) {
		var self = this;
		var scrolltop = document.body.scrollTop||ietruebody().scrollTop;
		
		for (var i=0;i<this.containers.length;i++) {
			var pos = this._getContainerArea(this.containers[i]);
			pos[1] -= scrolltop;
			if (e.clientX>pos[0] && e.clientX<pos[0]+this.containers[i].offsetWidth && e.clientY>pos[1] &&e.clientY<pos[1]+this.containers[i].offsetHeight) {
				return {
					id	: self.containers[i],
					x	: pos[0],
					y	: pos[1]
				};
			}
		}
		return false;
	},

	_getContainerArea	: function (container) {
		var temp = New(getElementPos,[container]);
		return [temp.getX(),temp.getY()];
	},

	_getEventIndex	: function (container,e) {
		var items	= getElementsByClassName(this.itemClass,container.id.id);
		var tempHeight = e.clientY-container.y;
		var temp	= 0;
		for (var i=0;i<items.length;i++) {
			if (tempHeight<(temp + items[i].offsetHeight/2)) {
				return items[i];
			}
			temp	= temp + items[i].offsetHeight;
		}
		return false;
	},


	_mousedownInit	: function (e,o) {
		this.currentItem	= o.parentNode;
		this.currentContainer	= this.currentItem.parentNode;
		this.currentIndex	= this._getCurrentIndex(this.currentContainer.id,this.currentItem.id);

		this._mousedownItemPosInit(e,this.currentItem.id);

		this.currentItem.style.width	= this.currentItem.offsetWidth+'px';
		this.currentItem.style.height	= this.currentItem.offsetHeight+'px';
		this.currentItem.style.zIndex	= 2000;
		this.help	= this._createHelpDiv();
		this.currentContainer.insertBefore(this.help,this.currentItem);
		this.currentItem.style.position = 'absolute';
		
		//document.body.appendChild(this.currentItem);
	},
	_mousedownItemPosInit	: function (e,id) {
		var index = id.substr(id.lastIndexOf('_')+1);
		var r = /^\d{1,3}$/.test(index);
		var currentpos = New(getElementPos,[id]);
		this.lastX	= e.clientX - parseInt(currentpos.getX());
		var isFixed = document.getElementById('zt_topbar').style.position=='fixed';
		
		this.lastY	= e.clientY-parseInt(currentpos.getY());
		this.currentItem.style.left	= currentpos.getX()+'px';
		this.currentItem.style.top	=currentpos.getY()+'px';
	},

	_createHelpDiv	: function () {
		var o = document.createElement("div");
		o.id  = 'help';
		o.className = 'sortHelper';
		o.style.height	= this.currentItem.offsetHeight+'px';
		o.innerHTML = '';
		return o;
	},

	_getCurrentIndex	: function (container,item) {
		var items	= getElementsByClassName(this.itemClass,container);
		for (var i=0;i<items.length;i++){
			if (items[i].id==item) {
				return i;
			}
		}
		return false;
	}
}
);


var externalDrag = Class(drag,{
	externalDrag	: function (external) {
		var externals	= getElementsByClassName(external);
		var self	= this;
		for (var i=0;i<externals.length;i++) {
			externals[i].onmousedown = function (e) {
				self._externalMouseDown(e);
			}
		}
	},
	_externalMouseDown	: function (e) {
		var e = is_ie ? window.event: e;
		var o = e.srcElement || e.target;
		
		this.currentItem = this._creadItem(o.id,o.innerHTML);
		document.body.appendChild(this.currentItem);

		this._mousedownItemPosInit(e,o.id);

		var self	= this;
		document.onmousemove = function(event){
			self._onDrag(event);
		};
		document.onmouseup   = function(event){
			self._dragEnd(event);
		};
	},

	_creadItem	: function (id,title) {
		var newid	= this._getNewId(id);
		var tempitem= elementBind('div',newid,this.itemClass,'width:400px;height:60px;z-index:2000;position:absolute');
		var head	= elementBind('div','',this.headClass);
		var titlespan	= elementBind('span');
		titlespan.innerHTML = title;
		head.appendChild(titlespan);
		var edit	= elementBind('a','',this.editClass);
		edit.href	= 'javascript:void(0);';
		edit.innerHTML	= '编辑';
		head.appendChild(edit);

		var close	= elementBind('a','',this.closeClass);
		close.href	= 'javascript:void(0);';
		close.innerHTML	= '[x]';
		head.appendChild(close);
		var self	= this;
		head.onmousedown = function (e) {
			self._mousedown(e);
		}
		var contentSpecialStyle = (id == 'block_banner' || id == 'block_nvgt') ? 'padding:0;margin:0;border:0;' : ''
		var content	= elementBind('div','',this.contentClass,contentSpecialStyle);
		content.innerHTML	= '&nbsp;';
		tempitem.appendChild(head);
		tempitem.appendChild(content);
		return tempitem;
	},
	_getNewId	: function (id) {
		var temp_id=id+'_'+parseInt(Math.random()*100+1);
		while (document.getElementById(temp_id)!=null) {
			temp_id=id+'_'+parseInt(Math.random()*100+1);
		}
		return temp_id;
	}
});
var externalLayoutDrag = Class(drag,{
	externalLayoutDrag	: function (external) {
		var externals	= getElementsByClassName(external);
		var self	= this;
		for (var i=0;i<externals.length;i++) {
			externals[i].onmousedown = function (e) {
				self._externalMouseDown(e);
			}
		}
	},
	_externalMouseDown	: function (e) {
		var e = is_ie ? window.event: e;
		var o = e.srcElement || e.target;

		this.currentItem = this._creadItem(o.id,getObj(o.id + '_title').innerHTML);
		document.body.appendChild(this.currentItem);

		this._mousedownItemPosInit(e,o.id);

		var self	= this;
		document.onmousemove = function(event){
			self._onDrag(event);
		};
		document.onmouseup   = function(event){
			self._dragEnd(event);
			
			dragItemImpl._initContainers();
		};
	},

	_creadItem	: function (id,title) {
		var newid	= this._getNewId(id);
		var tempitem= elementBind('div',newid,this.itemClass+' cc','width:942px;height:60px;z-index:2000;position:absolute');
		var head	= elementBind('div','',this.headClass);
		var titlespan	= elementBind('span');
		titlespan.innerHTML = title + '&nbsp;';
		head.appendChild(titlespan);

		var close	= elementBind('a','',this.closeClass);
		close.href	= 'javascript:void(0);';
		close.innerHTML	= '[x]';
		head.appendChild(close);
		var self	= this;
		head.onmousedown = function (e) {
			self._mousedown(e);
		}
		
		tempitem.appendChild(head);
		appends = this._getLayoutContents(id, newid);
		for (i=0; i<appends.length; i++) {
			tempitem.appendChild(appends[i]);
		}
		return tempitem;
	},
	_getNewId	: function (id) {
		var temp_id=id+'_'+parseInt(Math.random()*100+1);
		while (document.getElementById(temp_id)!=null) {
			temp_id=id+'_'+parseInt(Math.random()*100+1);
		}
		return temp_id;
	},
	_getLayoutContents : function(layoutType, parentId) {
		if ('' == layoutType || undefined == layoutType) return false;
		var config = {
			type1v0: {num: 1, styles: [''], suffixs: ['main']},
			type1v1: {num: 2, styles: ['width:463px;float:left;','width:463px;float:right;overflow:hidden;'], suffixs: ['left', 'right']},
			type1v1v1: {num: 3, styles: ['width:310px;margin-right:8px;float:left;overflow:hidden;','width:296px;float:left;','width:310px;float:right;overflow:hidden;'], suffixs: ['left', 'mid', 'right']},
			type1v2: {num: 2, styles: ['width:310px;float:left;','width:618px;float:right;overflow:hidden;'], suffixs: ['left', 'right']},
			type2v1: {num: 2, styles: ['width:618px;float:left;','width:310px;float:right;overflow:hidden;'], suffixs: ['left', 'right']}
		};
		layoutConfig = eval('config.'+layoutType);
		appends = [];
		for (i=0; i<layoutConfig.num; i++) {
			var content	= elementBind('div','','itemDroppable',layoutConfig.styles[i]);
			content.id = parentId + '_' + layoutConfig.suffixs[i];
			content.innerHTML	= '&nbsp;';
			appends.push(content);
		}
		return appends;
	}
});