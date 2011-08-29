/*
* event 模块
* 对event对象的封装和对事件的添加删除
*/
(function(B){
	var win = window, doc = document, body = doc.body,

	isFunction = function(o) {
            return toString.call(o) === '[object Function]';
    },

	getDom = function(o){
		if(typeof o === 'string') {
			B.require('dom');
			return B.querySelector(target);
		}
		return o;
	},

	// Is the DOM ready to be used? Set to true once it occurs.
	isReady = false,

	// The functions to execute on DOM ready.
	readyList = [],

	// Has the ready events already been bound?
	readyBound = false,
	guid = 1;
	
	/*
	* 扩展event对象
	*/
	function _extentEvent(event)
	{
		var e = {};
		for(var i in event){e[i] = event[i];}
		e.preventDefault = function(){
			if (event.preventDefault) event.preventDefault();
			else event.returnValue = false;
		};
		e.stopPropagation = function(){
			if(event.stopPropagation) event.stopPropagation();
			else event.cancelBubble = true;
		}
		
		e.halt = function(){e.preventDefault();e.stopPropagation()};
		
		e.target = e.target || e.srcElement;
		e.code = e.which || e.keyCode;
		var type = e.type;
		while (e.target && e.target.nodeType == 3) e.target = e.target.parentNode;
		if (type.match(/(click|mouse|menu)/i)){
			var win = window, doc = win.document;
			doc = (!doc.compatMode || doc.compatMode == 'CSS1Compat') ? doc.documentElement : doc.body;
			e.page = {
				x: e.pageX || e.clientX + doc.scrollLeft,
				y: e.pageY || e.clientY + doc.scrollTop
			};
			e.client = {
				x: (e.pageX) ? e.pageX - win.pageXOffset : e.clientX,
				y: (e.pageY) ? e.pageY - win.pageYOffset : e.clientY
			};
			if (e.type.match(/DOMMouseScroll|mousewheel/)){
				e.wheel = (event.wheelDelta) ? e.wheelDelta / 120 : -(e.detail || 0) / 3;
			}
			e.rightClick = (e.which == 3) || (e.button == 2);
			var related = null;
			if (type.match(/over|out/)){
				switch (type){
					case 'mouseover': related = e.relatedTarget || e.fromElement; break;
					case 'mouseout': related = e.relatedTarget || e.toElement;
				}
				if (!(function(){
					while (related && related.nodeType == 3) related = related.parentNode;
					return true;
				}).create({attempt: Browser.Engine.gecko})()) related = false;

				e.related = related;
			}
		};
		return e;
	}

	/*
	** 避免<body onload="fn" 被覆盖
	**
	*/
	if (!win.addEventListener) {
		doc.onreadystatechange = function() {
			if (win.onload && win.onload != handleEvent) {
				addEvent(win, 'load', win.onload);
				win.onload = handleEvent;
			}
		}
	}

	function _handleEvent(event) {
		var returnValue = true;
		event = _extentEvent(event || win.event);//扩展event
		var handlers = this.events[event.type];
		for (var i in handlers)
		{
			this.$$handler = handlers[i];
			if (this.$$handler(event) === false) returnValue = false;
		}
		return returnValue;
	};	
		
					
	B.mix(B,{
			
			/*  添加事件
			 *  主要代码来自:http://dean.edwards.name/weblog/2005/10/add-event2/
			 */
			addEvent: function(element,type,handler) {
				if(!element || !type || typeof handler !="function"){return;}//参数不合法
				//textNode and comment
				if(element.nodeType == 3 || element.nodeType == 8)
					return;
					
				if ( B.UA.ie && element.setInterval )//
					element = win;
					if (!handler.$$guid) handler.$$guid = guid++;
					if (!element.events) element.events = {};
					var handlers = element.events[type];
					if (!handlers)
					{
						handlers = element.events[type] = {};
						if (element['on' + type]) handlers[0] = element['on' + type];
						element['on' + type] = _handleEvent;
					}
					handlers[handler.$$guid] = handler;
			},

			/*
			 *  移除事件
			 */
			removeEvent: function(element,type /* optional */ ,handler /* optional */ ) {
				// delete the event handler from the hash table
				if(!handler){ 
					delete element.events[type];
				}
				if(!type){
					for(var i in element.events){
						delete element.events[i];
					}
					return;
				}
				if(element.events && element.events[type] && handler.$$guid) {
					delete element.events[type][handler.$$guid];
				}
			},
			

			/*
			*点击元素时循环触发不同事件
			*/
			toggleClick: function(element, fn, fn2 ) {
				if(!fn2){addEvent(element,"click",fn);}
				else{
					element.toggle = true;
					addEvent(element,"click",function(e){	
						element.toggle==true?fn(e):fn2(e);
						element.toggle = !element.toggle;
					});
				}
			},

			/*
			*dom加载完成后执行
			*/
			ready: function(fn) {
				// Attach the listeners
				if (!readyBound) this._bindReady();

				// If the DOM is already ready
				if (isReady) {
					// Execute the function immediately
					fn.call(win, this);
				} else {
					// Remember the function for later
					readyList.push(fn);
				}

				return this;
			},

			/**
			 * Binds ready events.
			 */
			_bindReady: function() {
				var self = this,
					doScroll = doc.documentElement.doScroll,
					eventType = doScroll ? 'onreadystatechange' : 'DOMContentLoaded',
					COMPLETE = 'complete',
					fire = function() {
						self._fireReady();
					};

				// Set to true once it runs
				readyBound = true;

				// Catch cases where ready() is called after the
				// browser event has already occurred.
				if (doc.readyState === COMPLETE) {
					return fire();
				}

				// w3c mode
				if (doc.addEventListener) {
					function domReady() {
						doc.removeEventListener(eventType, domReady, false);
						fire();
					}

					doc.addEventListener(eventType, domReady, false);

					// A fallback to window.onload, that will always work
					win.addEventListener('load', fire, false);
				}
				// IE event model is used
				else {
					function stateChange() {
						if (doc.readyState === COMPLETE) {
							doc.detachEvent(eventType, stateChange);
							fire();
						}
					}

					// ensure firing before onload, maybe late but safe also for iframes
					doc.attachEvent(eventType, stateChange);

					// A fallback to window.onload, that will always work.
					win.attachEvent('onload', fire);

					if (win == win.top) { // not an iframe
						function readyScroll() {
							try {
								// Ref: http://javascript.nwbox.com/IEContentLoaded/
								doScroll('left');
								fire();
							} catch(ex) {
								setTimeout(readyScroll, 1);
							}
						}
						readyScroll();
					}
				}
			},

			/**
			 * Executes functions bound to ready event.
			 */
			_fireReady: function() {
				if (isReady) return;

				// Remember that the DOM is ready
				isReady = true;

				// If there are functions bound, to execute
				if (readyList) {
					// Execute all of them
					var fn, i = 0;
					while (fn = readyList[i++]) {
						fn.call(win, this);
					}

					// Reset the list of functions
					readyList = null;
				}
			},

			//当元素生效时执行
			live: function(element,type ,fn) {
				var d = doc,
					atta = !!d.attachEvent,
					noBubble = /blur|focus/i.test(type);
				if(noBubble){//if onblur or onfocus
					d = body;
					if(atta){type += 'in';}//if ie:focusin
				}
				//element = Array.prototype.slice.call(element);
				this.addEvent(d,type,function(e){
					var el = e.target;
					if(element!=d && element == el){
						fn.call(el,e);
					}
				});
			},
			
			die: function(id,fn) {
				//if(timer)
			}
			
	});
})(Breeze);