/**
* @fileoverview DOM模块
* JavaScript.
*
* @author yuyang <yuyangvi@gmail.com>
* @version 1.0
*/
Breeze.namespace('dom', function(B){
	var CUSTOM_STYLES = {},
		ELEM_DATA = {},
		ELEM_INDEX = 1000,
		cssShow = { position: "absolute", visibility: "hidden", display: "block" },
		dom = 	
	/**
      @lends Breeze
    */
	{
		/**
		 * @function
		 * @param {HTMLElement} a
		 * @param {HTMLElement} b
		 * @returns {Boolean}
		 * @description 判断 a 节点是否包含 b 节点。
		 */
		contains: document.compareDocumentPosition ? function(a, b){
				return !!(a.compareDocumentPosition(b) & 16);
			} : function(a, b){
				return a !== b && (a.contains ? a.contains(b) : true);
			},
			
		/***
		 * @param {HTMLElement} elem
		 * @param {String} value
		 * @description 给元素添加指定 class。
		 */	
		addClass: function(elem, value){
			if (value && typeof value === "string" ) {
				var classNames = (value || "").split( /\s+/ );
	
				if ( elem.nodeType === 1 ){
					if ( !elem.className ){
						elem.className = value;

					} else {
						var className = " " + elem.className + " ", setClass = elem.className;
						for ( var c = 0, cl = classNames.length; c < cl; c++ ){
							if ( className.indexOf( " " + classNames[c] + " " ) < 0 ){
								setClass += " " + classNames[c];
							}
						}
						elem.className = B.trim( setClass );
					}
				}
			}
		},
		/***
		 * @param {HTMLElement} elem
		 * @param {String} value
		 * @description 删除元素指定的 class
		 */	
		removeClass: function(elem, value){
			if ( (value && typeof value === "string") || value === undefined ) {
				var classNames = (value || "").split(/\s+/);
	
				if ( elem.nodeType === 1 && elem.className ) {
					if ( value ) {
						var className = (" " + elem.className + " ").replace(/[\n\t]/g, " ");
						for ( var c = 0, cl = classNames.length; c < cl; c++ ) {
							className = className.replace(" " + classNames[c] + " ", " ");
						}
						elem.className = B.trim( className );
					} else {
						elem.className = "";
					}
				}
			}
		},
		/***
		 * @param {HTMLElement} elem
		 * @param {String} value
		 * @returns {Boolean}
		 * @description 检查元素是否包含指定 class。
		 */	
		hasClass: function(elem, selector){
			var className = " " + selector + " ";
			if ( (" " + elem.className + " ").replace(/[\n\t]/g, " ").indexOf(className) > -1 ) {
				return true;
			} else {
				return false;
			}
		},
		/***
		 * @param {HTMLElement} elem
		 * @param {String} value
		 * @returns {Boolean}
		 * @description 返回指定元素的innerHTML(简单实现,暂不考虑其它安全功能)
		 */	
		html: function(el, content) {
		    if(content) {
			    el.innerHTML = content;
			}else {
			    return el.innerHTML;
			}
		},
		/***
		 * @param {HTMLElement} elem
		 * @param {String} name
		 * @param {String} val
		 * @description 给元素添加属性。
		 */	
		attr: function(el, name, val){
			if ( !isElementNode(el) || (typeof name === 'string') && (B.trim(name) === '') ){ return;}
			if (B.isPlainObject(name)) {
				for (var k in name) {
					B.attr(el, k, name[k]);
				}
				return;
			}

			if (val === undefined) {//getter
				var ret;
				if ( !/href|src|style/.test(name) ) {
                    ret = el[name] || el.getAttribute(name);
                }
				if (B.UA.ie && B.UA.ie < 8) {
					// 不光是 href, src, 还有 rowspan 等非 mapping 属性，也需要用第 2 个参数来获取原始值
					if (/href|src|colspan|rowspan/.test(name)) {
						ret = el.getAttribute(name, 2);
					}
					// 在标准浏览器下，用 getAttribute 获取 style 值
					// IE7- 下，需要用 cssText 来获取
					else if (name === 'style') {
						ret = el.style.cssText;
					}
				}
				return ret === null ? undefined : ret;
			} else {//setter
                if (B.UA.ie && B.UA.ie < 8 && name === 'style') {
                    el.style.cssText = val;
                }
                else {
                    // checked 属性值，需要通过直接设置才能生效
                    if(name === 'checked') {
                        el[name] = !!val;
                    }
                    // convert the value to a string (all browsers do this but IE)
                    el.setAttribute(name, '' + val);
                }
			}
		},
		/***
		 * @param {HTMLElement} el
		 * @param {String} name
		 * @description 删除元素指定属性。
		 */	
        removeAttr: function(el, name) {
			if (isElementNode(el)) {
				B.attr(el, name, ''); // 先置空
				el.removeAttribute(name); // 再移除
			}
        },
		
		/***
		 * @param {HTMLElement} el
		 * @description 获取元素所在页面的位置。
		 */	
		offset:function(el){
			if (!isElementNode(el)){
				return;
			}
				var rect, x = 0, y = 0,
				w = getWin(el.ownerDocument);
				
				rect = el.getBoundingClientRect();
				
				x = rect.left + B.scrollLeft(w);
				y = rect.top + B.scrollTop(w);
				return {left: x, top: y}; 
		},
		/**
		 * @param {String}
		 * @returns {HTMLElement}
		 * @description 新建一个节点
		 */
		createElement:function(str, attr, style){
			var el;
			if(str.indexOf('<') == -1){
				el = document.createElement(str);
			}else{
				var div = document.createElement('div');
				div.innerHTML = B.trim(str);
				el = div.firstChild;
				div.removeChild(el);
				delete div;
			}
			attr && B.attr(el, attr);
			style && B.css(el, style);
			return el;
		},
		/**
		 * @param {HTMLElement}
		 * @returns {HTMLElement}
		 * @description 删除一个节点
		 */
		remove:function(el){
			el && el.parentNode && el.parentNode.removeChild(el);
		},
		/**
		 * @param {HTMLElement} el
		 * @param {name|map} css键名或者对象
		 * @param {val} css键值
		 * @description 获取/设置CSS
		 */
        css: function(el, name, val) {
            // supports hash
            if (B.isPlainObject(name)) {
                for (var k in name) {
                    B.css(el, k, name[k]);
                }
                return;
            }
			name = CUSTOM_STYLES[name] || name;
            if (typeof name == 'string' && (name.indexOf('-') > 0) ) {
                name = name.replace(/-([a-z])/ig, function(all, letter) {
					return letter.toUpperCase();
				});
            }

            // getter
            if (val === undefined) {
                if (el && el.style) {
                    ret = name.get ? name.get(el) : el.style[name];

                    // 有 get 的直接用自定义函数的返回值
                    if (ret === '' && !name.get) {
                        ret = el.currentStyle? el.currentStyle[name] : el.ownerDocument.defaultView.getComputedStyle(el, null)[name];
						//ret = fixComputedStyle(el, name, DOM._getComputedStyle(el, name));
                    }
                }
                return ret === undefined ? '' : ret;
            } else {
                // normalize unsetting
                if (val === null || val === '') {
                    val = '';
                }
                // number values may need a unit
                else if (!isNaN(new Number(val)) && /width|height|top|left|right|bottom|margin|padding/i.test(name)) {
                    val += 'px';
                }

				if (el && el.style) {
					name.set ? name.set(el, val) : (el.style[name] = val);
					if (val === '') {
						if (!el.style.cssText)
							el.removeAttribute('style');
					}
				}
            }
        },
		/**
		 * @param {HTMLElement} el
		 * @param {name|map} css键名或者对象
		 * @param {val} css键值
		 * @description 存取数据,只能存字符串
		 */
		data: function(el, name, val) {
            // supports hash
			//var pre = 'data-';
            if (B.isPlainObject(name)) {
                for (var k in name) {
                    B.data(el, k, name[k]);
                }
                return;
            }
			
			var attId = el.getAttribute('data-breeze');
            // getter
            if (val === undefined) {
                if (el && name) {
					var attId = el.getAttribute('data-breeze')
					if(!attId){
						return null;
					}
					if(!ELEM_DATA[attId]){
						ELEM_DATA[attId] = null;
					}
					return ELEM_DATA[attId][name];
                }
            } else {//setter
				if (el && name) {
					if (val === '' && attId) {
						ELEM_DATA[attId] = null;
					} else {
						if(!attId) {
							attId = 'BREEZE_' + ++ELEM_INDEX;
							el.setAttribute('data-breeze', attId);
						}
						if(!ELEM_DATA[attId]){
							ELEM_DATA[attId] = {};
						}
						ELEM_DATA[attId][name] = val;
					}
				}
            }
        },
		/**
		 * @param {HTMLElement} el
		 * @param {string} selector 选择器
		 * @description 获取父级节点
		 */
		parent: function(el, selector) {
            return nth(el, selector, 'parentNode', function(elem) {
                return elem.nodeType != 11;
            });
        },
		/**
		 * @param {HTMLElement} el
		 * @param {string} selector 选择器
		 * @description 获取下一个节点
		 */
		next: function(el, selector) {
            return nth(el, selector, 'nextSibling');
        },

		/**
		 * @param {HTMLElement} el
		 * @param {string} selector 选择器
		 * @description 获取上一个节点
		 */
        prev: function(el, selector) {
            return nth(el, selector, 'previousSibling');
        },

		/**
		 * @param {HTMLElement} el
		 * @param {string} selector 选择器
		 * @description 获取所有的同级节点
		 */
        siblings: function(el, selector) {
            return getSiblings(el, selector, true);
        },

		/**
		 * @param {HTMLElement} el
		 * @param {string} selector 选择器
		 * @description 获取所有的子节点
		 */
        children: function(el, selector){
            return getSiblings(el, selector);
        },
		
		/**
		 * @param {HTMLElement} newNode
		 * @param {HTMLElement} refNode
		 * @description 插入到节点前面
		 */
		insertBefore: function(newNode, refNode) {
            if (newNode && refNode && refNode.parentNode) {
                refNode.parentNode.insertBefore(newNode, refNode);
            }
            return newNode;
        },
		/**
		 * @param {HTMLElement} newNode
		 * @param {HTMLElement} refNode
		 * @description 插入到节点后面
		 */
		insertAfter: function(newNode, refNode) {
            if (newNode && refNode && refNode.parentNode) {
                if (refNode.nextSibling) {
                    refNode.parentNode.insertBefore(newNode, refNode.nextSibling);
                } else {
                    refNode.parentNode.appendChild(newNode);
                }
            }
            return newNode;
        },
		/**
		  * @param {HTMLElement} newNode
		  * @param {HTMLElement} refNode
		  * @description 插入到内部前面
		  */
		prepend: function(newNode, refNode){
			 if (newNode && refNode) {
				if (refNode.firstChild) {
                    refNode.insertBefore(newNode, refNode.firstChild);
                } else {
                    refNode.appendChild(newNode);
                }
            }
            return newNode;
		},
		/**
		 * @params {String} c
		 * @description 格式化颜色字符串，将rgb转换成16进制
		 * @returns {String}
		 */
		formatColor: function(c)
		{
			var matchs;
			if(matchs=c.match(/\s*rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/i)){c=(matchs[1]*65536+matchs[2]*256+matchs[3]*1).toString(16);while(c.length<6)c='0'+c;c='#'+c;}
			c=c.replace(/^#([0-9a-f])([0-9a-f])([0-9a-f])$/i,'#$1$1$2$2$3$3');
			return c;
		},
		
		getComputedStyle: function(el){
			return window.getComputedStyle ? window.getComputedStyle(el, null) : el.currentStyle;   
		},
		
		show: function(el){
			el.style.display = 'block';
			return el;
		},
		
		hide: function(el){
			el.style.display = 'none';
			return el;
		}
	};
	/**
	 * 判断是否是节点
	 */
	function isElementNode(el){
		return el && el.nodeType === 1;
	}
	function getWin(elem) {
		return (elem && ('scrollTo' in elem) && elem.document) ?
			elem :
			elem && elem.nodeType === 9 ?
				elem.defaultView || elem.parentWindow :
				false;
    }
	
	function filterTest(el,filter){
		return B.$$(filter).indexOf(el) > -1;
	}
	
	function nth(elem, filter, direction, extraFilter){
		var ret = null;
        while((elem = elem[direction])) {
            if (isElementNode(elem) && (!filter || filterTest(elem, filter)) && (!extraFilter || extraFilter(elem))) {
                ret = elem;
                break;
            }
        }
        return ret;
    }

    function getSiblings(elem, filter, parent) {
        var ret = [], j, parentNode = elem, next;
        if (elem && parent) parentNode = elem.parentNode;

        if (parentNode) {
            for (j = 0, next = parentNode.firstChild; next; next = next.nextSibling) {
                if (isElementNode(next) && next !== elem && (!filter || test(next, filter))) {
                    ret[j++] = next;
                }
            }
        }
        return ret;
    }
	function test(elem, filter) {
		var match, tag, cls, ret = [];
	
		// 默认仅支持最简单的 tag.cls 形式
		if ( (match = /^(?:#([\w-]+))?\s*([\w-]+|\*)?\.?([\w-]+)?$/.exec(filter) ) && !match[1]) {
			tag = match[2];
			cls = match[3];
			return !((tag && elem.tagName !== tag.toUpperCase()) || (cls && !B.hasClass(elem, cls)));
		}
		return true;
	}
	

	B.mix(B, dom);
/** @memberOf Breeze */
	['Left', 'Top'].forEach( function(name, i) {
		var method = 'scroll' + name;
		/** @constructor */
		B[method] = function(el){
			var ret = 0,
				w = el === undefined ? window : getWin(el),
				d;
	
			if (w && (d = w.document)){
				ret = w[i ? 'pageYOffset' : 'pageXOffset']
					|| d.documentElement[method]
					|| d.body[method]
			}
			else if ( isElementNode(el) ){
				ret = el[method];
			}
			return ret;
		}
	});
	['Width', 'Height'].forEach( function(name, i) {
		B[name.toLowerCase()] = function(el){
			if(!el){
				return null;
			}
			if ('scrollTo' in el && el.document){//window
				return el.document.compatMode === "CSS1Compat" ? el.document.documentElement[ "client" + name ] : el.document.body[ "client" + name ];
			} else if (el.nodeType === 9){//document
				return Math.max(
					el.documentElement["client" + name],
					el.body["scroll" + name], el.documentElement["scroll" + name],
					el.body["offset" + name], el.documentElement["offset" + name]
				);
			} else if (el['offset'+name] !== 0){
				return el['client'+ name] ? el['client'+name] : el['scroll'+name];
			}else{
				var old = {};
				for (var s in cssShow){
					old[s] = B.css(el, s);
				}
				B.css(el, cssShow);
				var val = el['client'+name];
				B.css(el, old);
				return val;
			}
		}
	});

	/**
	 * @lends Breeze
	 * @description 单链式函数
	 */
	var queryEls;
	B.$query = function(){
		var arg = arguments, l = arg.length;
		if (l == 0){
			return queryEls;
		} else {
			var f = arg[0];
			if (typeof f === 'string'){
				queryEls = B.$(f, arg[1]);
			} else if (isElementNode(f)){
				queryEls = f;
			} else if (typeof f === 'function'){
				var newarg = B.makeArray(arg);
				newarg[0] =queryEls;
				var ele = f.apply(null,newarg);
				if(ele && ele.nodeType)
					queryEls = ele;
			}
			return arg.callee;
		}
	}
	
	B.$$query = function(){
		var arg = arguments, l = arg.length;
		if (l == 0){
			return queryEls;
		} else {
			var f = arg[0];
			if (typeof f === 'string'){
				queryEls = B.$$(f,arg[1]);
			} else if (f.push){
				queryEls = f;
			} else if (typeof f === 'function'){
				var newarg = B.makeArray(arg);
				var result = [];
				queryEls.forEach(function(n){
					newarg[0] = n;
					var ele = f.apply(null, newarg);
					if(ele && ele.nodeType){
						result.push(ele);
					}
				});
				
				if(result.length > 0){
					queryEls = result;
				}
			}
			return arg.callee;
		}
	}
	
	/**
	 * IE 统一
	 */
	try {
        if (document.documentElement.style['opacity'] === undefined && document.documentElement['filters']) {

            CUSTOM_STYLES['opacity'] = {
                get: function(elem) {
                    var val = 100;

                    try { // will error if no DXImageTransform
                        val = elem['filters']['DXImageTransform.Microsoft.Alpha']['opacity'];
                    }
                    catch(e) {
                        try {
                            val = elem['filters']('alpha')['opacity'];
                        } catch(ex) {
                            // 没有设置过 opacity 时会报错，这时返回 1 即可
                        }
                    }

                    return val / 100 + '';
                },

                set: function(elem, val) {
                    var style = elem.style;

                    // IE has trouble with opacity if it does not have layout
                    // Force it by setting the zoom level
                    style.zoom = 1;

                    // Set the alpha filter to set the opacity
                    //elem.style.filters.alpha.opacity = val*188
					style['filter'] = 'alpha(opacity=' + val * 100 + ')';
                }
            };
        }
    } catch (e){
	}
	
	/*
    * 链式扩展
    */
	['addClass','removeClass','arrt','removeAttr','remove','css','data','parent','children','prev','next','siblings','html','height','width'].forEach(function(p) {
        B.extend(p,function() {
            var arg = B.makeArray(arguments),finalEls = [];
            for(var i = 0,j = this.nodes.length; i < j; i++) {
                var el = this.nodes[i],
                    result = B[p].apply(el,[el].concat(arg));
                finalEls = finalEls.concat(B.makeArray(result || []));
            }
            //如果是获取元素,更新当前this.el
            if(finalEls.length > 0){
                this.nodes = finalEls;
                this.length = finalEls.length;
            }
            return this;
        });
        /*
        * 当前元素不作为第一个参数的
        */
        ['insertBefore', 'insertAfter'].forEach(function(p) {
            B.extend(p,function() {
                var arg = B.makeArray(arguments);
                for(var i = 0,j = this.nodes.length; i < j; i++) {
                    arg.push(this.nodes[i]);
                    B[p].apply(this.nodes[i],arg);
                }
                return this;
            });
        });
        /*
        * each扩展
        */
        B.extend('each',function(fun){
            if(!B.isFunction(fun)) {
                return false;
            }
            for(var i = 0,j = this.nodes.length;i < j;i++) {
                var el = this.nodes[i];
                fun.call(el,i);
            }
            return this;
        });
    });
});
