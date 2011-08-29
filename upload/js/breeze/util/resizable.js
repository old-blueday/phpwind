/*
* util.resizable 模块
* resize支持
*/
Breeze.namespace('util.resizable', function(B) {
    B.require('dom','event',function() {
        var doc = document;
        function Resizable(options) {
            this.obj = options.obj;
            this.handle = options.handle || config.obj;
            this.onlyX = options.onlyX;
            this.onlyY = options.onlyY;
            this.onstart = options.onstart;
            this.onstop = options.onstop;
            this.ondrag = options.ondrag;
            this.init();
        }
        
       Resizable.prototype = {
            init:function() {
                    var obj = this.obj,
                    offset = B.offset(obj),
                    cursor;
               // B.css(obj,{'position':'absolute','left':offset.left+'px','top':offset.top,'display':'block'});
               cursor = this.onlyX ? 'e-resize' :(this.onlyY ? 's-resize' :'se-resize');
               B.css(this.obj,'display','block');
               B.css(this.handle,'cursor',cursor);
               B.addEvent(this.handle, 'mousedown', this.start.bind(this));
            },
            start:function(e) {
                e = this.fixEvent(e);
                e.halt();
                this.handle.lastMouseX=e.pageX;
		        this.handle.lastMouseY=e.pageY;
                doc.onmousemove = this.drag.bind(this);
		        doc.onmouseup = this.end.bind(this);
		        this.onstart && this.onstart.call(e);
            },
            drag:function(e) {
                e = this.fixEvent(e);
                var handle = this.handle,
		            mouseY = e.pageY,mouseX = e.pageX,
					width =  parseInt(this.obj.style.width) || B.width(this.obj) || parseInt(this.obj.width),
					height = parseInt(this.obj.style.height) || B.height(this.obj) || parseInt(this.obj.height),
					currentWidth = width + mouseX - handle.lastMouseX,
					currentHeight = height + mouseY - handle.lastMouseY;
				if(this.onlyX) {
				    if(currentWidth<10) currentWidth = 10 ;
				    this.obj.style.width = currentWidth + "px";
				} else if(this.onlyY) {
				    if(currentHeight<10) currentHeight = 10 ;
				    this.obj.style.height = currentHeight + "px";
				} else {
				    this.obj.style.width = currentWidth + "px";
				    this.obj.style.height = currentHeight + "px";
				}
		        handle.lastMouseX = mouseX;
		        handle.lastMouseY = mouseY;
		        this.ondrag && this.ondrag.call(e);
		        //document.onmouseup = this.;
            },
            end:function(e) {
                document.onmousemove = null;
		        document.onmouseup = null;
		        this.onstop && this.onstop.call(e);
            },
            /*
            格式化事件参数
            */
            fixEvent:function(e) {
		        e = e || window.event;
		        if(e.layerX === undefined)e.layerX=e.offsetX;
		        if(e.layerY === undefined)e.layerY=e.offsetY;
		        if(e.pageX === undefined)e.pageX = e.clientX + doc.body.scrollLeft - doc.body.clientLeft;
		        if(e.pageY === undefined)e.pageY = e.clientY + doc.body.scrollTop - doc.body.clientTop;
		        if(e.preventDefault === undefined)e.preventDefault = function(){
		            e.returnValue = false;
		        }
		        if(e.stopPropagation === undefined)e.stopPropagation = function(){
		            e.cancelBubble = true;
		        }
		        return e;
	        }
        }
        
        /**
	     * @description 元素resize支持
	     * @params {String} 要产生resize的元素
	     * @params {String} 拖动手柄
	     */
        B.util.resizable = function(options) {
			new Resizable(options);
        }
    });   
});
/*
TODO:范围限定,自动创建手柄,textarea支持
*/