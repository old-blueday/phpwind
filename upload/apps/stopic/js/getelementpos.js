var getElementPos = Class({},{
	Create	: function (elementId) {
		if (typeof(elementId)=='string') {
			this.element = getObj(elementId);
		} else {
			this.element = elementId;
		}
		this.x = 0;
		this.y  = 0;
		this._init();
	},
	getX	: function () {
		return this.x;
	},
	getY	: function () {
		return this.y;
	},
	_init	: function () {
		var el = this.element;
		var ua = navigator.userAgent.toLowerCase(); 
		var isOpera = (ua.indexOf('opera') != -1); 
		var isIE = (ua.indexOf('msie') != -1 && !isOpera); // not opera spoof 
		if(el.parentNode === null || el.style.display == 'none')  
		{ 
			return false; 
		} 
	 
		var parent = null; 
		var pos = []; 
		var box; 
	 
		if(el.getBoundingClientRect)    //IE 
		{ 
			box = el.getBoundingClientRect(); 
			var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop); 
			var scrollLeft = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft); 
			
			this.x=box.left + scrollLeft,
			this.y=box.top + scrollTop;
			return true;
		} 
		else if(document.getBoxObjectFor)    // gecko 
		{ 
			box = document.getBoxObjectFor(el); 
				
			var borderLeft = (el.style.borderLeftWidth)?parseInt(el.style.borderLeftWidth):0; 
			var borderTop = (el.style.borderTopWidth)?parseInt(el.style.borderTopWidth):0; 
	 
			pos = [box.x - borderLeft, box.y - borderTop]; 
		} 
		else    // safari & opera 
		{ 
			pos = [el.offsetLeft, el.offsetTop]; 
			parent = el.offsetParent; 
			if (parent != el) { 
				while (parent) { 
					pos[0] += parent.offsetLeft; 
					pos[1] += parent.offsetTop; 
					parent = parent.offsetParent; 
				} 
			} 
			if (ua.indexOf('opera') != -1  
				|| ( ua.indexOf('safari') != -1 && el.style.position == 'absolute' ))  
			{ 
					pos[0] -= document.body.offsetLeft; 
					pos[1] -= document.body.offsetTop; 
			}  
		} 
			 
		if (el.parentNode) { parent = el.parentNode; } 
		else { parent = null; } 
	   
		while (parent && parent.tagName != 'BODY' && parent.tagName != 'HTML')  
		{ // account for any scrolled ancestors 
			pos[0] -= parent.scrollLeft; 
			pos[1] -= parent.scrollTop; 
	   
			if (parent.parentNode) { parent = parent.parentNode; }  
			else { parent = null; } 
		} 
		this.x= pos[0]
		this.y= pos[1];
	}
}
);