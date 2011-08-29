/**
* @fileoverview 核心文件，包含按需载入管理器和基础函数的封装
* @author yuyang <yuyangvi@gmail.com>
* @version 1.0
*/
(function () {
    if (!window.Breeze) {
        /**
        * Function 扩展
        */
        Function.prototype.bind = function () {
            var fn = this, args = Array.prototype.slice.call(arguments, 0), object = args.shift();
            return function () {
                return fn.apply(object,
			  args.concat(Array.prototype.slice.call(arguments, 0)));
            };
        };
        /**
        * 使IE兼容数组的indexOf方法.
        * @memberOf Array
        * @returns 数字，表示元素在数组中的索引地址，如果不在数组中就返回-1
        * @type int
        */
        if (!Array.prototype.indexOf) {
            Array.prototype.indexOf = function (elt /*, from*/) {
                var len = this.length >>> 0;

                var from = Number(arguments[1]) || 0;
                from = (from < 0)
				 ? Math.ceil(from)
				 : Math.floor(from);
                if (from < 0)
                    from += len;

                for (; from < len; from++) {
                    if (from in this &&
				  this[from] === elt)
                        return from;
                }
                return -1;
            };
        }
        window.isQueue = false;
        var loadQueue = [],
		modQueue = [],

		isLoading = false,
		loadingIndex = 0,
		runVerson = '1.0',

		startQueue = function () {
		    if (loadQueue.length === 0) {
		        isQueue = false;
		        return;
		    }
		    isQueue = true;
		    var mod = loadQueue.shift();
		    (loadingIndex > 0) && (loadingIndex--);
		    switch (typeof mod) {
		        case 'string': //模组
		            if (modQueue.indexOf(mod) == -1) {
		                var script = document.createElement('script');
		                script.id = mod.replace('.', '-');

		                var i = mod.indexOf('.');
		                if (i < 0) {
		                    mod = 'core.' + mod;
		                } else {
		                    //补充上层命名空间
		                    var basemod = mod.slice(0, i);
		                    Breeze[basemod] || (Breeze[basemod] = {});
		                }
		                script.src = Breeze.path + mod.replace('.', '/') + '.js?v='+runVerson;
		                isQueue = false;
		                isLoading = true;
						//document.body.appendChild(script);
		                document.getElementsByTagName('head')[0].appendChild(script);
		            } else {
		                startQueue();
		            }
		            break;
		        case 'function': //回执
		            mod(Breeze);
		            startQueue();
		        default:
		    }
		},
		word,
        win = window,
        doc = win.document,
        // Is the DOM ready to be used? Set to true once it occurs.
	    isReady = false,

        // The functions to execute on DOM ready.
	    readyList = [],

        // Has the ready events already been bound?
	    readyBound = false;
        /** 
        * @construct
        */
        Breeze = {
            version: '1.0.0',
            path: BREEZE_BASE_PATH,/*function () {
                return document.getElementById('B_script_base').src.replace('core/base.js', '');
            } (),*/
            /**
            * @description 将参数中的js文件和函数加入管理队列<br />
            * 参数可以是字符串或者函数本身
            * @exports require as Breeze.require
            * @params {String|Function} fun
            */
            require: function () {
                var args = Array.prototype.slice.call(arguments),
				prequeue = loadQueue.slice(0, loadingIndex),
				afterqueue = loadQueue.slice(loadingIndex);
                loadQueue = prequeue.concat(args, afterqueue);
                loadingIndex += args.length;
                isQueue || isLoading || startQueue();
            },
            /**
            * @description 命名空间, 用在各模块文件下面,以注明自己所代表的模块。
            * @param {String} modName 对应的名称
            *
            */
            namespace: function (modName, fn) {
                modQueue.push(modName);
                loadingIndex = 0;
                fn(Breeze);
                isLoading = false;
                isQueue || startQueue();
            },

            /**
            * @description 检测对象是否是字面量对象
            * @param {Object} o 对象
            * @returns {Boolean}
            */
            isPlainObject: function (o) {
                return o && o.toString() === '[object Object]' && !o['nodeType'] && !o['setInterval'];
            },
            /**
            * @description 当dom加载完成后执行
            * @params {Function} DOM加载完成后要执行的函数
            * @example B.ready(function(){
                                do some thing...
                                });
            */
            ready: function (fn) {
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
            },
            /**
            * Binds ready events.
            */
            _bindReady: function () {
                var self = this,
					doScroll = doc.documentElement.doScroll,
					eventType = doScroll ? 'onreadystatechange' : 'DOMContentLoaded',
					COMPLETE = 'complete',
					fire = function () {
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
                            } catch (ex) {
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
            _fireReady: function () {
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
            /**
            * @description 浏览器判断<br/>
            * 可以判断浏览器的核心和javascript的版本号
            * 有ie,webkit,opera和gecko四种
            * 值为数字表示的版本号，如果不是该核心，值为0
            * @example Breeze.UA.ie
            */
            UA: function () {
                var o = {
                    ie: 0,
                    gecko: 0,
                    webkit: 0,
                    opera: 0
                },
				ua = navigator.userAgent,
				m,
				numberify = function (s) {
				    var c = 0;
				    return parseFloat(s.replace(/\./g, function () {
				        return (c++ == 1) ? '' : '.';
				    }));
				};
                if (ua) {
                    m = ua.match(/AppleWebKit\/([^\s]*)/);
                    if (m && m[1]) {
                        o.webkit = numberify(m[1]);
                    }

                };

                if (!o.webkit) { // not webkit
                    // @todo check Opera/8.01 (J2ME/MIDP; Opera Mini/2.0.4509/1316; fi; U; ssr)
                    m = ua.match(/Opera[\s\/]([^\s]*)/);
                    if (m && m[1]) {
                        o.opera = numberify(m[1]);
                    } else { // not opera or webkit
                        m = ua.match(/MSIE\s([^;]*)/);
                        if (m && m[1]) {
                            o.ie = numberify(m[1]);
                        } else { // not opera, webkit, or ie
                            m = ua.match(/Gecko\/([^\s]*)/);
                            if (m) {
                                o.gecko = 1; // Gecko detected, look for revision
                                m = ua.match(/rv:([^\s\)]*)/);
                                if (m && m[1]) {
                                    o.gecko = numberify(m[1]);
                                }
                            }
                        }
                    }
                }
                return o;
            } (),
            /***
            * @description 用于属性合并
            * @param a 被加工的对象
            * @param b 将其参数的属性赋给第一个对象,
            * @param overWrite 确定是否对已经有的属性直接覆盖
            * @returns 加工后的对象
            */
            mix: function (/**Object*/a, /**Object*/b, /**Boolean*/overWrite) {
                for (var p in b) {
                    if (overWrite || (typeof a[p] == 'undefined')) {
                        a[p] = b[p];
                    }
                }
                return a;
            },
            /***
            * @description 多对象覆盖
            * @param {Object} object 需要覆盖的对象
            * @return 加工后的对象
            */
            merge: function (o) {
                var a = arguments, i, l = a.length;
                for (i = 1; i < l; i = i + 1) {
                    Breeze.mix(o, a[i], true);
                }
                return o;
            },
            /**
            * @description 将类数组转换为数组
            * @param {ArrayLike} array 类数组
            * @returns Array 加工后的数组
            */
            makeArray: function (o/*=====, results=====*/) {
                if (B.isArray(o)) return o;
                if (typeof o.length !== 'number' || B.isString(o) || B.isFunction(o)) {
                    return [o];
                }
                return Array.prototype.slice.call(o, 0);
                /*=====
                if ( results ) {
                results.push.apply( results, array);
                return results;
                }
				
                return array;
                =====*/
            },
            /**
            * @description 将字符串左右两侧的空白去掉
            * @param {String} str 字符串
            * @return 加工后的字符串
            */
            trim: function (str) {
                if (typeof str === 'string') {
                    return str.trim ? str.trim() : str.replace(/\s+$/, '');
                }
                return '';
            },
            /**
            * @description 检查对象是否是函数
            * @param {Object} obj 任意对象
            * @returns Boolean
            */
            isFunction: function (obj) {
                return Object.prototype.toString.call(obj) === "[object Function]";
            },
            /**
            * @description 检查对象是否为数组
            * @param {Object} obj任意对象
            * @returns Boolean
            */
            isArray: function (obj) {
                return Object.prototype.toString.call(obj) === '[object Array]';
            },
            /**
            * @description 检查对象是否为字符串
            * @param {Object} obj任意对象
            * @returns Boolean
            */
            isString: function (o) {
                return Object.prototype.toString.call(o) === '[object String]';
            },
            /**
            * @description 设定缩写
            */
            shortcut: function (s) {
                word && (window[word] = undefined);
                window[word = s] = Breeze;
            },
            /**
            * @description 接受CSS
            * @param {String} url 地址
            */
            loadCSS: function (url, id) {
                var css = document.createElement('link');
                css.type = 'text/css';
				css.rel = 'stylesheet';
				css.href = url;
				if (id){
					css.id = id;
				}
                document.body.appendChild(css);
				return css;
            }
        };

        /**
        * Set Shortcut
        */
        Breeze.shortcut('B');


        var bindNative = function () {
            ['every', 'forEach', 'filter', 'map', 'some'].forEach(function (n) {
                B[n] = function () {
                    var arg = arguments, l = arg.length;
                    if (l == 0) {
                        return null;
                    }
                    var newarg = Breeze.makeArray(arg);
                    return Array.prototype[n].apply(newarg.shift(), newarg);
                }
            });
        };
        if (Array.prototype.some) {
            bindNative();
        } else {
           B.require('native', bindNative);
        }
        /**
        * @name Breeze.$
        * @function
        */
        var bindDom = function (B) {
            if (typeof Sizzle !== 'undefined') {//载入,整合sizzle;
                /**
                * @lends Breeze
                * @description XX函数
                */
                B.$$ = Sizzle;
                /**
                * @lends Breeze
                * @description XX函数
                */
                B.$ = function (selector, parentNode) {
                    var results = Sizzle(selector, parentNode);
                    return results.length ? results[0] : null;
                }
            } else {//不载入,整合querySelector
                B.$ = function (selector, parentNode) {
                    parentNode = parentNode || document;
                    return parentNode.querySelector(selector);
                };
                B.$$ = function (selector, parentNode) {
                    parentNode = parentNode || document;
                    var ar = parentNode.querySelectorAll(selector), l = ar.length, res = [];
                    for (var i = 0; i < l; i++) {
                        res.push(ar[i]);
                    }
                    return res;
                };
            }
        };
        if (document.querySelectorAll) {
            bindDom(B);
        } else {
            B.require('sizzle', bindDom);
        }




        /*
        * 链式核心代码
        */
        Function.prototype.method = function (name, fn) {
            this.prototype[name] = fn;
            return this;
        };
        function _$(el) {
            if (B.isString(el)) {
                this.nodes = B.$$(el);
            } else {
                if (el && el.nodeType && el.nodeType === 1) {
                    this.nodes = B.makeArray(el);
                } else {
                    this.nodes = [];
                }
            }
            this[0] = this.nodes[0];
            this.length = this.nodes.length;
            //return this.nodes;
        };
        B.query = function (el) { return new _$(el); }
        B.extend = function (name, fn) {
            _$.method(name, fn);
            return this;
        };
    }
})();