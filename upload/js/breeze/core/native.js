Breeze.namespace('native', function(B){
var _arProto = {
	lastIndexOf: function(elt /*, from*/)
	{
		var len = this.length;
	
		var from = Number(arguments[1]);
		if (isNaN(from))
		{
		  from = len - 1;
		}
		else
		{
		  from = (from < 0)
			   ? Math.ceil(from)
			   : Math.floor(from);
		  if (from < 0)
			from += len;
		  else if (from >= len)
			from = len - 1;
		}
	
		for (; from > -1; from--)
		{
		  if (from in this &&
			  this[from] === elt)
			return from;
		}
		return -1;
	},
	every: function(fun /*, thisp*/)  
	{  
		var len = this.length >>> 0;  
		if (typeof fun != "function")  
		  throw new TypeError();  
	  
		var thisp = arguments[1];  
		for (var i = 0; i < len; i++)  
		{  
		  if (i in this &&  
			  !fun.call(thisp, this[i], i, this))  
			return false;  
		}  
	  
		return true;  
	},
	forEach: function(fun /*, thisp*/)
	{
		var len = this.length >>> 0;
		if (typeof fun != "function")
		  throw new TypeError();
		
		var thisp = arguments[1];
		for (var i = 0; i < len; i++)
		{
		  if (i in this)
			fun.call(thisp, this[i], i, this);
		}
	},
	filter: function(fun /*, thisp*/)  
	{  
		var len = this.length >>> 0;  
		if (typeof fun != "function")  
		  throw new TypeError();  
	  
		var res = [];  
		var thisp = arguments[1];  
		for (var i = 0; i < len; i++)  
		{  
		  if (i in this)  
		  {  
			var val = this[i]; // in case fun mutates this  
			if (fun.call(thisp, val, i, this))  
			  res.push(val);  
		  }  
		}  
	  
		return res;  
	},
	map: function(fun /*, thisp*/)
	{
		var len = this.length >>> 0;
		if (typeof fun != "function")
		  throw new TypeError();
	
		var res = new Array(len);
		var thisp = arguments[1];
		for (var i = 0; i < len; i++)
		{
		  if (i in this)
			res[i] = fun.call(thisp, this[i], i, this);
		}
	
		return res;
	},
	some: function(fun /*, thisp*/)
	{
		var i = 0,
		len = this.length >>> 0;
		
		if (typeof fun != "function")
			throw new TypeError();
	
		var thisp = arguments[1];
		for (; i < len; i++)
		{
			if (i in this &&
				fun.call(thisp, this[i], i, this))
			return true;
		}
	
		return false;
	}
};
B.mix(Array.prototype, _arProto);
});