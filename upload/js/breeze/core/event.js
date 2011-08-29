/*
 * @fileoverflow event 模块<br/>
 * 对event对象的封装和对事件的添加删除
 * @author chenchaoqu <chaoren1641@gmail.com>
 * @version 1.0 
 */
Breeze.namespace('event', function (B) {
    var win = window, doc = document, body = doc.body,

    // Is the DOM ready to be used? Set to true once it occurs.
	isReady = false,

    // The functions to execute on DOM ready.
	readyList = [],

    // Has the ready events already been bound?
	readyBound = false,
	cache = {},
	guid = 1;

    /*
    * 扩展event对象
    */
    function _extentEvent(event) {
        //无法重写event某些属性,如target,故重新拷贝
        var e = {};
        for (var i in event) { e[i] = event[i]; }
        e.preventDefault = function () {
            if (event.preventDefault) event.preventDefault();
            else event.returnValue = false;
        };
        e.stopPropagation = function () {
            if (event.stopPropagation) event.stopPropagation();
            else event.cancelBubble = true;
        }

        e.halt = function () { e.preventDefault(); e.stopPropagation() };

        e.target = event.target || event.srcElement;

        var type = e.type;

        //check if target is a textnode (safari)
        while (e.target && e.target.nodeType == 3) e.target = e.target.parentNode;

        /*
        * 在IE下：
        *   支持keyCode
        *   不支持which和charCode,二者值为 undefined
        * 在Firefox下：
        *   支持keyCode，除功能键外，其他键值始终为 0
        *   支持which和charCode，二者的值相同
        * 在Opera下：
        *   支持keyCode和which，二者的值相同
        *   不支持charCode，值为 undefined
        */
        e.keyCode = event.which || event.keyCode;

        //from mootools
        if (type.match(/(click|mouse|menu)/i)) {
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
            if (e.type.match(/DOMMouseScroll|mousewheel/)) {
                e.wheelDelta = (event.wheelDelta) ? e.wheelDelta / 120 : -(e.detail || 0) / 3;
            }
            e.rightClick = (e.which == 3) || (e.button == 2);
            if (!event.relatedTarget && event.fromElement) {
                e.relatedTarget = (event.fromElement === event.target) ? event.toElement : event.fromElement;
            }
        };
        return e;
    }


    function _handleEvent(event) {
        var returnValue = true;
        event = _extentEvent(event || ((this.ownerDocument || this.document || this).parentWindow || window).event); //扩展event
        var handlers = this.events[event.type];
        for (var i in handlers) {
            this.$$handler = handlers[i];
            if (this.$$handler(event) === false) returnValue = false;
        }
        return returnValue;
    };


    B.mix(B, /** @lends Breeze */{
    /**
    * @description 添加事件
    * @see http://dean.edwards.name/weblog/2005/10/add-event2/
    * @params {Object} 要添加事件的元素对象
    * @params {String} 事件类型
    * @params {Function} 事件处理函数
    */
    addEvent: function (element, type, handler) {
        if (!element || !type || typeof handler != "function") { return; } //参数不合法
        //textNode and comment
        if (element.nodeType == 3 || element.nodeType == 8)
            return;

        if (B.UA.ie && element.setInterval)
            element = win;
        if (!handler.$$guid) handler.$$guid = guid++;
        if (!element.events) element.events = {};
        var handlers = element.events[type];
        if (!handlers) {
            handlers = element.events[type] = {};
            if (element['on' + type]) handlers[0] = element['on' + type];
            element['on' + type] = _handleEvent;
        }
        handlers[handler.$$guid] = handler;
        cache[handler.$$guid] = element; //添加cache,ie unload时用
    },

    /**
    * @description 移除事件
    * @params {Object} 要移除事件的元素对象 
    * @params {String} 事件类型(可选)
    * @params {Function} 事件处理函数(可选)
    */
    removeEvent: function (element, type /* optional */, handler /* optional */) {
        // delete the event handler from the hash table
        if (!handler) {
            if (element.events && element.events[type])
                delete element.events[type];
        }
        if (!type) {
            for (var i in element.events) {
                delete element.events[i];
            }
        }
        if (element.events && element.events[type] && handler.$$guid) {
            delete element.events[type][handler.$$guid];
        }
    },

    /**
    * @description 点击元素时循环触发不同事件
    * @params {Object} 要触发事件元素
    * @params {Function} 第一次点击触发的函数
    * @params {Function} 第二次点击触发的函数
    * @example B.get("#one")("goggleClick",fn1,fn2);
    */
    toggleClick: function (element, fn, fn2) {
        if (!fn2) { addEvent(element, "click", fn); }
        else {
            element.toggle = true;
            this.addEvent(element, "click", function (e) {
                element.toggle == true ? fn.call(this, e) : fn2.call(this, e);
                element.toggle = !element.toggle;
            });
        }
    },
    /**
    * @description 给存在或将出现的元素绑定事件
    * @params {String} 元素CSS2.1选择器
    * @params {String} 事件类型
    * @params {Function} 事件处理函数
    * @example B.get("#one")("live","click",fn);
    **/
    live: function (selector, type, fn) {
        var d = doc,
			atta = !!d.attachEvent,
			noBubble = /blur|focus/i.test(type);
        if (noBubble) {//if onblur or onfocus
            d = body;
            if (atta) { type += 'in'; } //if ie:focusin
        }
        var self = this;
        B.require('dom', function (B) {
            self.addEvent(d, type, function (e) {
                var elements = B.$$(selector),
					    el = e.target;
                for (var i = 0, j = elements.length; i < j; i++) {
                    if (elements[i] != d && elements[i] == el) {
                        fn.call(el, e);
                    }
                }
				e.preventDefault();
            });
        });
    },

    /**
    * @description 给存在或将出现的元素绑定事件
    * @params {Object} 要触发事件元素
    * @params {String} 事件类型
    * @example B.get("#one")("live","click",fn);
    **/
	trigger: function (el, type){
		return B.UA.ie ? el[type]() : el['on'+type]({
			type: type,
			target: el
		});
	}
});

    // Prevent memory leaks in IE
    if (win.attachEvent && !win.addEventListener) {
        win.attachEvent('onunload', function () {
            for (var i in cache) {
                B.removeEvent(cache[i]);
            }
        });
        //避免<body onload="fn" 被覆盖
        doc.onreadystatechange = function () {
            if (win.onload && win.onload != _handleEvent) {
                B.addEvent(win, 'load', win.onload);
                win.onload = _handleEvent;
            }
        }
    }
    
    /*
    * 链式
    */
	['addEvent','removeEvent','live'].forEach(function(p) {
        B.extend(p,function() {
            var arg = B.makeArray(arguments);
            for(var i = 0,j = this.nodes.length; i < j; i++) {
                var el = this.nodes[i];
                B[p].apply(el,[el].concat(arg));
            }
            return this;
        });
    });
});

/**
 * TODO:
 *   - live已经实现,因为选择器结果要时时刷新,故参数中只能传选择器参数,而不能传HTMLElementList
 */