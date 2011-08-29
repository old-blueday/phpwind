/*
* util.draggble 模块
* 拖动支持
*/
Breeze.namespace('util.draggable', function(B) {
    B.require('dom','event',function() {
        var doc = document;
        function Draggable(obj,handle) {
            this.obj = obj;
            this.handle = handle || obj;
            this.init();
        }
        
        Draggable.prototype = {
            init:function() {
                var obj = this.obj,
                    offset = B.offset(obj);
                B.css(obj,{'position':'absolute','left':offset.left+'px','top':offset.top,'display':'block'});
                this.handle.onmousedown = this.start.bind(this);
            },
            start:function(e) {
                e = this.fixEvent(e);
                e.preventDefault();
                this.handle.lastMouseX = e.pageX;
		        this.handle.lastMouseY = e.pageY;
                doc.onmousemove = this.drag.bind(this);
		        doc.onmouseup = this.end.bind(this);
		        doc.body.setCapture && this.obj.setCapture();
            },
            drag:function(e) {
                e = this.fixEvent(e);
                var handle = this.handle,
		            mouseY = e.pageY,mouseX = e.pageX,
		            top = parseInt(this.obj.style.top),
		            left = parseInt(this.obj.style.left),
		            currentLeft = left + mouseX - handle.lastMouseX,
		            currentTop = top + (mouseY - handle.lastMouseY);
		        this.obj.style.left = currentLeft + "px";
		        this.obj.style.top = currentTop + "px";
		        handle.lastMouseX = mouseX;
		        handle.lastMouseY = mouseY;
            },
            end:function(e) {
                document.onmousemove = null;
		        document.onmouseup = null;
		        doc.body.releaseCapture && this.obj.releaseCapture();// IE释放鼠标监控
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
	     * @description 元素拖动支持
	     * @params {String} 要产生拖动的元素（选择器）
	     * @params {String} 拖动手柄（选择器，可选）
	     */
        B.util.draggable = function(selector,hand) {
            B.$$(selector).forEach(function(n) {
                if(hand) {
                    var handle = B.$(hand,n);
                    new Draggable(n,handle);
                }else {
                    new Draggable(n);
                }
            });
        }
    });   
});
/*
TODO:onstart,ondrag,onend等事件支持，范围限定，方向限定
*/