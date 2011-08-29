/*
* chain 模块
* 对Breeze提供链式操作功能
*/
Breeze.namespace('util.chain',function(B){
    Function.prototype.method = function(name,fn) {
        this.prototype[name] = fn;
        return this;
    };
    B.require('dom','event','util.animate',function(B) {
        (function() {
            function _$(el) {
                if ( B.isString(el) ) {
                    this.el = B.$$(el);
                } else {
                    if(el && el.nodeType && el.nodeType===1) {
                        this.el = B.makeArray(el);
                    }else {
                        this.el = [];
                    }
                }
                this[0] = this.el[0];
                this.length = this.el.length;
            };
            /*
            * 适合选择器的操作方法
            */
            ['addClass','removeClass','arrt','removeAttr','createElement','remove','css','data','parent','children','prev','next','siblings','addEvent','removeEvent','hide','show','slideDown','slideUp','fadeIn','fadeOut','html'].forEach(function(p) {
                _$.method(p,function() {
                    var arg = B.makeArray(arguments),finalEls = [];
                    //不光是有set操作,还会有get操作,当get元素集合时,需要改变当前的elements
                    for(var i = 0,j = this.el.length; i < j; i++) {
                        var el = this.el[i],
                            result = B[p].apply(el,[el].concat(arg));
                        finalEls = B.makeArray(result || []).concat(finalEls);
                    }
                    if(finalEls.length > 0){
                        this.el = finalEls;
                        this.length = finalEls.length;
                    }
                    return this;
                });
            });
            /*
            * 当前元素不作为第一个参数的
            */
            ['insertBefore', 'insertAfter'].forEach(function(p) {
                _$.method(p,function() {
                    var arg = B.makeArray(arguments);
                    for(var i = 0,j = this.el.length; i < j; i++) {
                        var el = this.el[i];
                        B[p].apply(el,arg.push(el));
                    }
                });
            });
            /*
            * animate函数，因为它有一个util空间隔着,单独写出来
            */
            _$.method('animate',function(style, speed, easing, callback) {
                for(var i = 0,j = this.el.length; i < j; i++) {
                    B.util.animate(this.el[i],style, speed, easing, callback);
                }
                return this;
            });
            
            B['_$'] = function(el){return new _$(el);}
            B['_$'].forEach = B.forEach;
            B['_$'].every = B.every;
            B['_$'].some = B.some;
            B['_$'].map = B.map;
            B['_$'].filter = B.filter;
            B.extend = function(name, fn) {
                _$.method(name, fn);
                return this;
            };
        })();   
       
    });
});

/*
* TODO:
*    目前还没有足够常用的API,只是实现了部分,如表单元素中常用的html(),val()等属性
*/