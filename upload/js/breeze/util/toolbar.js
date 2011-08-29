/*
* util.toolbar 模块
* 滚动页面toolbar功能
*/
Breeze.namespace('util.toolbar', function(B) {
    B.require('dom','event',function() {
        function ToolBar(elem,exceedElem /*optional*/ ) {
            this.elem = elem;
            this.exceedElem = exceedElem;
            this.init();
        }
    
        ToolBar.prototype = {
            init:function() {
                var self = this,
                    el = self.elem, elT = B.offset(el).top, elW = B.width(el), elH = B.height(el), exceedBottom = 999999999; //默认下拉无极限
                if(self.exceedElem) { 
                    exceedBottom = B.offset(self.exceedElem).top + B.height(self.exceedElem);
                }
                B.addEvent(window,'scroll',function() {
                    elH = B.height(el);
                    if(self.exceedElem) { 
                        exceedBottom = B.offset(self.exceedElem).top + B.height(self.exceedElem);
                    }
                    var winST=(document.compatMode && document.compatMode!="CSS1Compat") ? document.body.scrollTop:document.documentElement.scrollTop || window.pageYOffset;
                    //console.log(winST+","+elT)
                    if(winST > elT && winST < (exceedBottom+elH)) {
                        if(B.UA.ie == 6) {
 							if(!B.$("#B_tmp_blank")){
							   var top = document.documentElement.scrollTop;
								var blank = B.createElement('div');
								blank.id = "B_tmp_blank";
								blank.style.width = B.width(el) + 'px';
								blank.style.height = B.height(el)+'px';
								B.insertBefore(blank, el);
							}
                            B.css(el,{'position':'absolute','top':''+top+'px','z-index':'999','width':''+elW+'px'});
                        }else {
							if(!B.$("#B_tmp_blank")){
								var blank = B.createElement('div');
								blank.id = "B_tmp_blank";
								blank.style.width = B.width(el) + 'px';
								blank.style.height = B.height(el)+'px';
								B.insertBefore(blank, el);
								B.css(el,{'position':'fixed','top':'0px','z-index':'999','width':''+elW+'px'});
							}
                        }
                    }else {
						B.query('#B_tmp_blank').remove();
                        B.css(el,{'position':'static'});
                    }
                });
            }
        }
    
        /**
	     * @description 滚动页面toolbar功能
	     * @params {Element} 要产生toolbar功能的元素
	     * @params {Element} 要比较的元素，比如：固定限度，不能超过某个元素的底部
	     */
        B.util.toolbar = function (elem, exceedElem) {
            elem = typeof elem === 'string' ? B.$(elem) : elem;
            new ToolBar(elem, exceedElem);
        }
    });
});