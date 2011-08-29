/*
* lazyload 模块
* 
*/
Breeze.namespace('util.lazyload', function(B) {
    var win = window,doc = document,
        defaultConfig = {
            container :win,
            img_data : 'data-src',
            area_cls : 'bz-lazyLoad',
            delay : 100,//resize时和socrll时延迟处理,以免频繁触发,100毫秒基本无视觉问题
            placeholder :''//图片占位符
        }
    
    function Lazyload(selector,settings) {
        var self = this;
        B.merge(self, defaultConfig, settings);
        if( !(self instanceof Lazyload) ) {
			return new Lazyload(selector, settings);
		}
		B.require('dom','event',function(B) {
		   var lazyImgs = [],lazyAreas = [],
		    container = self.container.nodeType===1 ? self.container :win,
		    threshold = function() {
		        if(container===win) {
		            var scrollTop =  win.pageYOffset || container.scrollTop || doc.documentElement.scrollTop || doc.body.scrollTop,
		            eHeight = doc.documentElement.innerHeight || ducument.body.clientHeight || ducument.documentElement.clientHeight;
		            return scrollTop + eHeight;
		        }
		        return B.offset(container).top + container.clientHeight;
		    },
		    eHeight = function() {return container.innerHeight || container.clientHeight;}
		    B.$$(selector).forEach(function (n) {
                if(n.nodeName === 'IMG' && n.getAttribute(self.img_data)) {
                    lazyImgs.push(n);
                    if(self.placeholder!==''){
                        n.src = self.placeholderl;
                    }
                }
                if(n.nodeName === 'TEXTAREA' && B.hasClass(n,self.area_cls)) {
                    lazyAreas.push(n);
                }
            });
            //加载image	
            var _loadImgs = function() {
		        lazyImgs.forEach(function(n) {
		            if(!n.src || n.src==='') {
		                if(B.offset(n).top <= threshold()) {
		                    n.src = n.getAttribute(self.img_data);
		                }
		            }
		        });
	        };
        	
        	//加载textarea	
	        var _loadAreas = function() {
        	    lazyAreas.forEach(function(n) {
        	        var isHide = true,
        	            top = B.offset(isHide?n.parentNode:n).top;
        	        if(B.hasClass(n,self.area_cls)) {//当没有加载的时候才加载,有可能已经加载过
        	            if(top <= threshold()) {
        	                n.style.display = 'none';n.className = '';
        	                var elem = B.createElement('<div>' + n.value + '</div>');
        	                B.insertBefore(elem,n);
        	            }
        	        }
        	    });
	        };
	        
	        var _loadAll = function() {
	            var timer;
                if(timer) {
                    clearTimeout(timer);
                }
                timer = setTimeout(function() {//延迟执行
                    _loadImgs();
                    _loadAreas();
                },self.delay);
	        }
            B.addEvent(container,'scroll',function() {
                _loadAll();
            });
            B.addEvent(container,'resize',function() {
                _loadAll();
            }); 
            _loadAll();//默认加载一次
		});
    }
    
    
    /**
	 * @description 数据延迟加载
	 * @params {String} 要延迟加载的元素
	 * @params {PlainObject} 设置参数 包含{
	 *                                   container://要产生滚动的元素,
	 *                                   delay://延迟加载的时间,
	 *                                   placeholder://图片占位符,
	 *                                   img_data : //图片的自定义属性,
     *                                   area_cls : //textarea的class名
	 *                                   }
	 */
	B.util.lazyload = function(selector,settings) {
	    Lazyload(selector,settings);
    }
});
/*
当前只考虑竖向滚动时的延迟加载,横向基本上很少用到,所以暂时不考虑,在图片延迟加载中,
只能加img-data参数,加了src就达不到延迟效果
*/