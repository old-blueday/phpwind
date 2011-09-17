IE = document.all;
PW={};
/*
 * 删除dom节点，并释放内存
 *@param nodeElement htmlElement 节点对象
 */
$removeNode=function(htmlElement)
{
	var a=document.createElement("DIV");
	a.appendChild(htmlElement);
	a.innerHTML="";
	a=null;
};
/*
 *根据ID获取节点对象
 *@param String id 节点id
 */
var getObj = function(id) 
{
    return document.getElementById(id);
};

//通用domready
(function(win,doc){
	var dom = [];
	dom.isReady  = false;
	win.onReady = function(fn){
		if ( dom.isReady ) {
			fn()
		} else {
			dom.push( fn );
		}
	}
	dom.fireReady = function() {
		if ( !dom.isReady ) {
			if ( !doc.body ) {
				return setTimeout( dom.fireReady, 16 );
			}
			dom.isReady = 1;
			if ( dom.length ) {
				for(var i = 0, fn;fn = dom[i];i++)
					fn()
			}
		}
	}
	if ( doc.readyState === "complete" ) {
		dom.fireReady();
	}else if(-[1,] ){
		doc.addEventListener( "DOMContentLoaded", function() {
	  		doc.removeEventListener( "DOMContentLoaded",  arguments.callee , false );
	  		dom.fireReady();
		}, false );
	}else {
		doc.attachEvent("onreadystatechange", function() {
		  if ( doc.readyState == "complete" ) {
			doc.detachEvent("onreadystatechange", arguments.callee );
			dom.fireReady();
		  }
	});
	(function(){
		if ( dom.isReady ) {
			return;
		}
		var node = new Image();
		try {
			node.doScroll();
			node = null;
		} catch( e ) {
			setTimeout( arguments.callee, 64 );
			return;
		}
	  	dom.fireReady();
	})();
  }
})(window,document);

(function(win){
	/**
	 *通用事件处理函数
	 */
	// http://dean.edwards.name/weblog/2005/10/add-event/
	win.addEvent = function(element, type, handler) {
		if (element.addEventListener) {
			element.addEventListener(type, handler, false);
		} else {
			if (!handler.$$guid) handler.$$guid = addEvent.guid++;
			if (!element.events) element.events = {};
			var handlers = element.events[type];
			if (!handlers) {
				handlers = element.events[type] = {};
				if (element["on" + type]) {
					handlers[0] = element["on" + type];
				}
			}
			handlers[handler.$$guid] = handler;
			element["on" + type] = handleEvent;
		}
	};
	addEvent.guid = 1;
	function removeEvent(element, type, handler) {
		if (element.removeEventListener) {
			element.removeEventListener(type, handler, false);
		} else {
			if (element.events && element.events[type]) {
				delete element.events[type][handler.$$guid];
			}
		}
	};
	win.removeEvent=removeEvent;
	function handleEvent(event) {
		var returnValue = true;
		event = event || fixEvent(((this.ownerDocument || this.document || this).parentWindow || window).event);
		var handlers = this.events[event.type];
		for (var i in handlers) {
			this.$$handleEvent = handlers[i];
			if (this.$$handleEvent(event) === false) {
				returnValue = false;
			}
		}
		return returnValue;
	};
	function fixEvent(event) {
		event.preventDefault = fixEvent.preventDefault;
		event.stopPropagation = fixEvent.stopPropagation;
		return event;
	};
	fixEvent.preventDefault = function() {
		this.returnValue = false;
	};
	fixEvent.stopPropagation = function() {
		this.cancelBubble = true;
	};
})(window);
/**
 *创建函数的代理，并将作用域(this)指向首个参数
 * @param Object b 当前作用域对象，将b替换函数内的this对象
 * @example 使用举例：killError=function(){return true};
 * window.onerror= killError.delegate();
 */
Function.prototype.delegate = function(b)
{
    var _ = this;
    return function()
    {
        try{return _.call(b)}catch(e){}
    }
};
/**
 *类继承
 *@param  JSON overrides 属性和方法的重载
 */
Function.prototype.extend = function(overrides)
{
    var F = function(config)
    {
        if (typeof(config) == "object")
        {
            for (var i in config)
            {
               try{ this[i] = config[i];}catch(e){}
            }
        }
    };
    F.prototype = new this;
    if (overrides)
    {
        for (var i in overrides)
        {
			/**
			 *safri 浏览器在遍历时会报一个异常
			 */
            try{F.prototype[i] = overrides[i];}catch(e){}
        }
    }
    return F;

};

/*
 *组件基类，这个是所有组件类的父类，或者父父类，任何组件类都继承自此类，这样可以有比较统一的配置项
 *@param JSON config 组件配置
 */
var baseClass=function(config)	 
{
	if (typeof(config) == "object")
	{
		for (var i in config)
		{
			try{this[i] = config[i];}catch(e){}
		}
	}
};