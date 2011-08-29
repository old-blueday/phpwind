/*
* animate 模块
* 动画组件,使元素可以产生动画效果
*/
Breeze.namespace('util.animate', function (B) {
    var win = window, doc = document, M = Math,
        div = doc.createElement('div'),
        divStyle = div.style,
        transTag = divStyle.MozTransform === '' ? 'Moz' :
                    (divStyle.WebkitTransform === '' ? 'Webki' :
                    (divStyle.OTransform === '' ? 'O' :
                    false)),
        matrixFilter = !transTag && divStyle.filter === '',

        props = ('backgroundColor borderBottomColor borderBottomWidth borderLeftColor borderLeftWidth ' +
    'borderRightColor borderRightWidth borderSpacing borderTopColor borderTopWidth bottom color fontSize ' +
    'fontWeight height left letterSpacing lineHeight marginBottom marginLeft marginRight marginTop maxHeight ' +
    'maxWidth minHeight minWidth opacity outlineColor outlineOffset outlineWidth paddingBottom paddingLeft ' +
    'paddingRight paddingTop right textIndent top width wordSpacing zIndex').split(' '),


    /*
    * form kissy
    */
    M = Math, PI = M.PI,
    pow = M.pow, sin = M.sin,
    BACK_CONST = 1.70158,
    Easing = {
        /**
        * Uniform speed between points.
        */
        easeNone: function (t) {
            return t;
        },

        /**
        * Begins slowly and accelerates towards end. (quadratic)
        */
        easeIn: function (t) {
            return t * t;
        },

        /**
        * Begins quickly and decelerates towards end.  (quadratic)
        */
        easeOut: function (t) {
            return (2 - t) * t;
        },

        /**
        * Begins slowly and decelerates towards end. (quadratic)
        */
        easeBoth: function (t) {
            return (t *= 2) < 1 ?
                .5 * t * t :
                .5 * (1 - (--t) * (t - 2));
        },

        /**
        * Begins slowly and accelerates towards end. (quartic)
        */
        easeInStrong: function (t) {
            return t * t * t * t;
        },

        /**
        * Begins quickly and decelerates towards end.  (quartic)
        */
        easeOutStrong: function (t) {
            return 1 - (--t) * t * t * t;
        },

        /**
        * Begins slowly and decelerates towards end. (quartic)
        */
        easeBothStrong: function (t) {
            return (t *= 2) < 1 ?
                .5 * t * t * t * t :
                .5 * (2 - (t -= 2) * t * t * t);
        },

        /**
        * Snap in elastic effect.
        */

        elasticIn: function (t) {
            var p = .3, s = p / 4;
            if (t === 0 || t === 1) return t;
            return -(pow(2, 10 * (t -= 1)) * sin((t - s) * (2 * PI) / p));
        },

        /**
        * Snap out elastic effect.
        */
        elasticOut: function (t) {
            var p = .3, s = p / 4;
            if (t === 0 || t === 1) return t;
            return pow(2, -10 * t) * sin((t - s) * (2 * PI) / p) + 1;
        },

        /**
        * Snap both elastic effect.
        */
        elasticBoth: function (t) {
            var p = .45, s = p / 4;
            if (t === 0 || (t *= 2) === 2) return t;

            if (t < 1) {
                return -.5 * (pow(2, 10 * (t -= 1)) *
                    sin((t - s) * (2 * PI) / p));
            }
            return pow(2, -10 * (t -= 1)) *
                sin((t - s) * (2 * PI) / p) * .5 + 1;
        },

        /**
        * Backtracks slightly, then reverses direction and moves to end.
        */
        backIn: function (t) {
            if (t === 1) t -= .001;
            return t * t * ((BACK_CONST + 1) * t - BACK_CONST);
        },

        /**
        * Overshoots end, then reverses and comes back to end.
        */
        backOut: function (t) {
            return (t -= 1) * t * ((BACK_CONST + 1) * t + BACK_CONST) + 1;
        },

        /**
        * Backtracks slightly, then reverses direction, overshoots end,
        * then reverses and comes back to end.
        */
        backBoth: function (t) {
            if ((t *= 2) < 1) {
                return .5 * (t * t * (((BACK_CONST *= (1.525)) + 1) * t - BACK_CONST));
            }
            return .5 * ((t -= 2) * t * (((BACK_CONST *= (1.525)) + 1) * t + BACK_CONST) + 2);
        },

        /**
        * Bounce off of start.
        */
        bounceIn: function (t) {
            return 1 - Easing.bounceOut(1 - t);
        },

        /**
        * Bounces off end.
        */
        bounceOut: function (t) {
            var s = 7.5625, r;

            if (t < (1 / 2.75)) {
                r = s * t * t;
            }
            else if (t < (2 / 2.75)) {
                r = s * (t -= (1.5 / 2.75)) * t + .75;
            }
            else if (t < (2.5 / 2.75)) {
                r = s * (t -= (2.25 / 2.75)) * t + .9375;
            }
            else {
                r = s * (t -= (2.625 / 2.75)) * t + .984375;
            }

            return r;
        },

        /**
        * Bounces off start and end.
        */
        bounceBoth: function (t) {
            if (t < .5) {
                return Easing.bounceIn(t * 2) * .5;
            }
            return Easing.bounceOut(t * 2 - 1) * .5 + .5;
        }
    };

    B.mix(B, {
        //form jquery
        queue: function (elem, type, data) {
            if (!elem) {
                return;
            }
            if (typeof type !== "string") {
                data = type;
                type = "fx";
            }
            //type = (type || "fx") + "queue";
            var q = B.data(elem, type);

            // Speed up dequeue by getting out quickly if this is just a lookup
            if (!data) {
                return q || [];
            }

            if (!q || B.isArray(data)) {
                q = B.data(elem, type, B.makeArray(data));

            } else {
                q.push(data);
            }
            if ( type === "fx" && B.queue(elem)[0] !== "inprogress" ) {
				B.dequeue( elem, type );
			}
            return q;
        },

        dequeue: function (elem, type) {
            type = type || "fx";

            var queue = B.queue(elem, type), fn = queue.shift();

            // If the fx queue is dequeued, always remove the progress sentinel
            if (fn === "inprogress") {
                fn = queue.shift();
            }
            if (fn) {
                // Add a progress sentinel to prevent the fx queue from being
                // automatically dequeued
                if (type === "fx") {
                    queue.unshift("inprogress");
                }
                fn.call(elem, function () { 
                    B.dequeue(elem, type);
                });
            }
        }
    });

    /*
    * from:http://github.com/madrobby/emile/
    */
    function interpolate(source, target, pos) {
        if(isNaN(source)){source = 0;}
        return (source + (target - source) * pos).toFixed(3);
    }
    function s(str, p, c) {
        return str.substr(p, c || 1);
    }
    /*
    * 转换为rgb(255,255,255)格式
    */
    function color(source, target, pos) {
        var i = 2, j, c, tmp, v = [], r = [];
        while (j = 3, c = arguments[i - 1], i--)
            if (s(c, 0) == 'r') {
                c = c.match(/\d+/g); while (j--) v.push(~ ~c[j]);
            } else {
                if (c.length == 4) c = '#' + s(c, 1) + s(c, 1) + s(c, 2) + s(c, 2) + s(c, 3) + s(c, 3);
                while (j--) v.push(parseInt(s(c, 1 + j * 2, 2), 16));
            }
        while (j--) {
            tmp = ~ ~(v[j + 3] + (v[j] - v[j + 3]) * pos);
            r.push(tmp < 0 ? 0 : tmp > 255 ? 255 : tmp);
        }
        return 'rgb(' + r.join(',') + ')';
    }

    function parse(prop) {
        if(!prop){prop = '0';}//IE下取不取没有设定的样式
        var p = parseFloat(prop), q = prop.replace(/^[\-\d\.]+/, '');
        return isNaN(p) ? { v: q, f: color, u: ''} : { v: p, f: interpolate, u: q };
    }
    /*
    * 样式名标准化
    */
    function normalize(style) {
        var css, rules = {}, i = props.length, v;
        div.innerHTML = '<div style="' + style + '"></div>';
        css = div.childNodes[0].style;
        while (i--) if (v = css[props[i]]) { rules[props[i]] = parse(v); };
        return rules;
    }

    /*
    * 动画主函数
    * animate('#test', 'width: 100px', 5, 'bounceOut',function(){});
    */
    var animate = function (el, style, speed, easingfun, callback) {
        el = typeof el == 'string' ? B.$(el) : el;
        B.require('dom', function (B) {
            if (typeof easingfun == 'function') {
                callback = easingfun;
                easingfun = 'easeNone';
            }
            B.queue(el, function () {
                var target = normalize(style), comp = el.currentStyle ? el.currentStyle : getComputedStyle(el, null),
                prop, current = {}, start = +new Date, dur = speed || 200, finish = start + dur, interval,
                easing = typeof easingfun == 'string' && Easing[easingfun] ? Easing[easingfun] : function (pos) { return (-M.cos(pos * M.PI) / 2) + 0.5; };
                for (prop in target) {
                    current[prop] = parse(comp[prop]);
                }
                interval = setInterval(function () {
                    var time = +new Date, pos = time > finish ? 1 : (time - start) / dur;
                    for (prop in target) {
                        B.css(el, prop, target[prop].f(current[prop].v, target[prop].v, easing(pos)) + target[prop].u);
                    }
                    
                    if (time > finish) {
                        clearInterval(interval);
                        interval = null;
                        callback && callback.call(el);
                        B.dequeue(el);
                    }
                }, 10);
                
            });
        });
        return B.util;
    },

    /*
    * 旋转
    */
    rotate = function () {
        //暂时不实现
    },


    //Node animate
    speeds = {
        slow: 600,
        fast: 200,
        // Default speed
        _default: 400
    },
    FX = {
        show: ['overflow', 'opacity', 'height', 'width'],
        fade: ['opacity'],
        slide: ['overflow', 'height']
    },
    effects = {
        show: ['show', 1],
        hide: ['show', 0],
        toggle: ['toggle'],
        fadeIn: ['fade', 1],
        fadeOut: ['fade', 0],
        slideDown: ['slide', 1],
        slideUp: ['slide', 0]
    }


    B.require('dom', function (B) {

        _EF = {}

        for (var ef in effects) {
            (function (ef) {
                _EF[ef] = function (elem, speed, callback) {
                    elem = typeof elem == 'string' ? B.$(elem) : elem;
                    if (!B.data(elem, 'height')) {
                        B.data(elem, { height: B.height(elem), width: B.width(elem), opacity: B.css(elem, 'opacity') });
                    }
                    if (!speed) {
                        speed = speeds._default;
                    } else if (typeof speed == 'string') {
                        speed = speeds[speed];
                    } else if (B.isFunction(speed)) {
                        callback = speed;
                    }
                    runFx(elem, effects[ef][0], speed, effects[ef][1], callback);
                }
            })(ef);
        }

        function runFx(elem, action, speed, display, callback) {
            //if (display || action === 'toggle') { elem.style.display = ''; }

            if (action === 'toggle') {
                display = B.css(elem, 'height') === '0px' ? 1 : 0;
                action = 'show';
            }

            var style = '', oldW = B.data(elem, 'width'), oldH = B.data(elem, 'height'), oldOp = B.data(elem, 'opacity');
            FX[action].forEach(function (p) {
                if (p === 'overflow') {
                    B.css(elem, 'overflow', 'hidden');
                } else if (p === 'opacity') {
                    var s = display ? oldOp + ';' : '0;';
                    style += 'opacity:' + s;
                    //if (display) B.css(elem, 'opcacity', '0');
                } else if (p === 'height') {
                    var s = display ? oldH + 'px;' : '0px;';
                    style += 'height:' + s;
                    //if (display) B.css(elem, 'height', '0px');
                } else if (p === 'width') {
                    var s = display ? oldW + 'px;' : '0px';
                    style += 'width:' + s;
                    //if (display) B.css(elem, 'width', '0px');
                }
            });
            //分析最终样式后进行动画
            animate(elem, style, speed, 'easeIn', function () {
                //if (!display) { elem.style.display = 'none'; }
                callback && callback.call(elem);
            });
        }
        B.mix(B, _EF);
    });

    B.animate = animate;
    //B.util.rotate = rotate;

    /*
    * 链式
    */
    ['hide','show','slideDown','slideUp','fadeIn','fadeOut','animate'].forEach(function(p) {
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

/*
* TO:CSS3支持,rotate旋转支持,目前还没有实现如jquery的队列机制,同时执行好几个动画会有问题
*
*/