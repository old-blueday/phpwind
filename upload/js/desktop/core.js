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
$ = function(id) 
{
    return document.getElementById(id);
};

~function()
{
	/*
	 *将readyBound加入到闭包环境，避免使用全局变量
	 */
	var readyBound=false;
	/*
	 *页面代码加载完毕时执行，比onload要早执行。
	 */
	window.onReady = function(fallBackFunction)
	{
		if (window.readyBound) return;
		readyBound = true;
		var ready = 0;
		// Mozilla, Opera and webkit nightlies currently support this event
		if (document.addEventListener)
		{
			// Use the handy event callback
			document.addEventListener("DOMContentLoaded",
			function()
			{
				document.removeEventListener("DOMContentLoaded", arguments.callee, false);
				if (ready) return;
				ready = 1;
				fallBackFunction?fallBackFunction():0;
			},
			false);

			// If IE event model is used
		} else if (document.attachEvent)
		{
			// ensure firing before onload,
			// maybe late but safe also for iframes
			document.attachEvent("onreadystatechange",
			function()
			{
				if (document.readyState === "complete")
				{
					document.detachEvent("onreadystatechange", arguments.callee);
					if (ready) return;
					ready = 1;
					fallBackFunction?fallBackFunction():0;
				}
			});

			// If IE and not an iframe
			// continually check to see if the document is ready
			if (document.documentElement.doScroll && window == window.top)(function()
			{
				if (ready) return;
				try
				{
					// If IE is used, use the trick by Diego Perini
					// http://javascript.nwbox.com/IEContentLoaded/
					document.documentElement.doScroll("left");
				} catch(error)
				{
					setTimeout(arguments.callee, 0);
					return;
				}
				ready = 1;
				fallBackFunction?fallBackFunction():0;

			})();
		}
	};
}();
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