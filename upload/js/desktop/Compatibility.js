if (typeof HTMLElement != "undefined") {
	if (window.Event) {
		Event.prototype.__defineSetter__("returnValue", function(b) {
			if (!b) this.preventDefault();
			return b
		});
		Event.prototype.__defineSetter__("cancelBubble", function(b) {
			if (b) this.stopPropagation();
			return b
		});
		Event.prototype.__defineGetter__("srcElement", function() {
			var node=this.target;
			while(node.nodeType != 1) node = node.parentNode;
			return node
		})
	}
	
	if (window.Document) {
		
	}
	
	if (window.Node) {
		Node.prototype.replaceNode = function(node) {
			this.parentNode.replaceChild(node, this)
		};
		Node.prototype.removeNode = function(r) {
			if (r) try {
				return this.parentNode.removeChild(this)
			} catch(e) {
				document.body.removeChild(this)
			} else {
				var range = document.createRange();
				range.selectNodeContents(this);
				return this.parentNode.replaceChild(range.extractContents(), this)
			}
		};
		Node.prototype.swapNode = function(node) {
			var nextSibling = this.nextSibling;
			var parentNode = this.parentNode;
			node.parentNode.replaceChild(this, node);
			parentNode.insertBefore(node, nextSibling)
		}
	}
	
	if (window.HTMLElement) {
		function _attachEvent(o,e,h) {
			e=/^onmousewheel$/i.test(e)?"DOMMouseScroll":e.replace(/^on/i,"");
			return o.addEventListener(e, h, false)
		}
		
		HTMLElement.prototype.attachEvent = function(e, h) {
			return _attachEvent(this,e,h)
		};
		window.attachEvent = function(e, h){
			return _attachEvent(window, e, h)
		};
		document.attachEvent = function(e, h){
			return _attachEvent(window, e, h)
		};
		function _detachEvent(o, e, h) {
			e=/^onmousewheel$/i.test(e)?"DOMMouseScroll":e.replace(/^on/i,"");
			return o.removeEventListener(e, h, false)
		}
		HTMLElement.prototype.detachEvent = function(e, h){
			return _detachEvent(this, e, h)
		};
		window.detachEvent = function(e, h){
			return _detachEvent(window, e, h)
		};
		document.detachEvent = function(e, h){
			return _detachEvent(window, e, h)
		};
		HTMLElement.prototype.onpropertychange = function(e, h) {
			return this.onchange
		};
		HTMLElement.prototype.__defineGetter__("all", function() {
			var a = this.getElementsByTagName("*");
			var node = this;
			a.tags = function(sTagName) {
				return node.getElementsByTagName(sTagName)
			};
			return a
		});
		HTMLElement.prototype.__defineGetter__("children", function() {
			var tmp = [];
			var j = 0;
			var n;
			for (var i=0; i<this.childNodes.length; i++) {
				n = this.childNodes[i];
				if (n.nodeType == 1) {
					tmp[j++] = n;
					if (n.name) {
						if (!tmp[n.name])tmp[n.name] = [];
						tmp[n.name][tmp[n.name].length] = n
					}
					if (n.id) tmp[n.id] = n
				}
			}
			return tmp
		});
		HTMLElement.prototype.__defineGetter__("currentStyle", function() {
			return this.ownerDocument.defaultView.getComputedStyle(this, null)
		});
		HTMLElement.prototype.__defineSetter__("outerHTML", function(sHTML) {
			var r = this.ownerDocument.createRange();
			r.setStartBefore(this);
			var df = r.createContextualFragment(sHTML);
			this.parentNode.replaceChild(df, this);
			return sHTML
		});
		HTMLElement.prototype.__defineGetter__("outerHTML", function(){
			var attr;
			var attrs=this.attributes;
			var str="<"+this.tagName;
			for(var i=0;i<attrs.length;i++){
				attr=attrs[i];
				if(attr.specified)str+=" "+attr.name+'="'+attr.value+'"'
			}return str+">"+this.innerHTML+"</"+this.tagName+">"
		});
		HTMLElement.prototype.__defineSetter__("innerText",function (sText){
			var parsedText=document.createTextNode(sText);
			this.innerHTML=parsedText;
			return parsedText
		});
		HTMLElement.prototype.__defineGetter__("innerText",function (){
			var r=this.ownerDocument.createRange();
			r.selectNodeContents(this);
			return r.toString ()
		});
		HTMLElement.prototype.__defineSetter__("outerText",function (sText){
			var parsedText=document.createTextNode(sText);
			this.outerHTML=parsedText;
			return parsedText
		});
		HTMLElement.prototype.__defineGetter__("outerText",function (){
			var r=this.ownerDocument.createRange();
			r.selectNodeContents(this);
			return r.toString ()
		});
		HTMLElement.prototype.__defineGetter__("uniqueID",function (){
			if(!this.id){
				this.id="control_"+parseInt(Math.random()*1000000)
			}
			return this.id
		});
		HTMLElement.prototype.setCapture=function (){
			document.onselectstart=function (){
				return false
			};
			window.captureEvents(Event.MOUSEMOVE|Event.MOUSEUP)
		};
		HTMLElement.prototype.releaseCapture=function (){
			document.onselectstart=null;
			window.releaseEvents(Event.MOUSEMOVE);
			window.releaseEvents(Event.MOUSEUP)
		}
	}
}

if(window.addEventListener){
	FixPrototypeForGecko()
}

function FixPrototypeForGecko(){
	HTMLElement.prototype.__defineGetter__("runtimeStyle",element_prototype_get_runtimeStyle);
	window.constructor .prototype.__defineGetter__("event",window_prototype_get_event);
	Event.prototype.__defineGetter__("keyCode",event_prototype_get_keyCode);
	Event.prototype.__defineGetter__("offsetX",event_prototype_get_ox);
	Event.prototype.__defineGetter__("offsetY",event_prototype_get_oy);
	function event_prototype_get_ox(){
		return event_prototype_get_oxy(this).offsetX
	}
	
	function event_prototype_get_oy(){
		return event_prototype_get_oxy(this).offsetY
	}
	
	function event_prototype_get_oxy(evt){
		var target=evt.target;
		var pageCoord=getPageCoord(target);
		var eventCoord={
			x:window.pageXOffset+evt.clientX,y:window.pageYOffset+evt.clientY
		};
		var offset={
			offsetX:eventCoord.x-pageCoord.x,offsetY:eventCoord.y-pageCoord.y
		};
		return offset
	}
	
	function getPageCoord(element){
		var coord={
			x:0,y:0
		};
		while(element){
			coord.x+=element.offsetLeft;
			coord.y+=element.offsetTop;
			element=element.offsetParent
		}
		return coord
	}
}

function element_prototype_get_runtimeStyle(){
	return this.style
}

function event_prototype_get_offsetX(){
	return this.offsetX
}

function event_prototype_get_keyCode(){
	return event.which
}

function window_prototype_get_event(){
	return SearchEvent()
}

function SearchEvent(){
	if(document.all) return window.event;
	func=SearchEvent.caller;
	var i = 0;//TODO:Firefox 4.x will be a endless loop. --Alacner 2010/09/15
	while(func!=null && i < 100){
		i++;
		var arg0=func.arguments[0];
		if (arg0) {
			if ((arg0.constructor == Event || arg0.constructor == MouseEvent)
				||(typeof(arg0) == "object" && arg0.preventDefault && arg0.stopPropagation))
			{
				return arg0;
			}
		} 
		func=func.caller
	}
	return null
}