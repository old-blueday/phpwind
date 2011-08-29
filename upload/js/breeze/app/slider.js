// JavaScript Document
Breeze.namespace('app.slider', function(B) {
	var  step = 5000;
	var  Slider = function(selector){
        var self = this;
        if( !(self instanceof Slider) ) {
		    return new Slider(selector);
	    }
		this.elem = B.$(selector);
		this.slider =  B.$('#B_slider');
		this.slideContainer = this.slider.parentNode;
		this.isAutoPlay = false;
		this.init();
	}
	Slider.prototype = {
		//校正宽度
		adjustWin: function(){
			//获取宽度
			var self = this;
			B.require('dom', function(B){
				var winw = B.width(window);
				//设置宽度
				self.elem.width = winw-400;
			});
		},
		//设置内部宽度
		adjustDiv: function(){
			var slide = B.$('.B_slider', this.elem);
			slide.style.width = 60 * B.$$('span', slide).length-3+'px';
		},
		//初始化
		init: function(){
			var self = this;
			this.adjustDiv();
			this.adjustWin();
			B.require('event', function(B){
				B.addEvent(window, 'resize', self.adjustWin.bind(self));
				B.addEvent(B.$('#nextPage'), 'click', self.nextPage.bind(self));
				B.addEvent(B.$('#prePage'),  'click', self.prePage.bind(self));
				B.addEvent(B.$('#nextPic'),  'click', self.nextPic.bind(self));
				B.addEvent(B.$('#prePic'),   'click', self.prePic.bind(self));
				B.addEvent(B.$('#pause'),    'click', self.autoPlayToggle.bind(self));
				B.$$('a', self.slider).forEach(function(n){
					B.addEvent(n, 'click', self.autoPlayCheck.bind(self));
				});
				//var link = B.$('a', this.slider);
				var link = B.$('.B_slider a');
				B.trigger(link, 'click');
			});
		},
		autoPlayToggle: function(){
			var el = B.$('#pause');
			if(this.isAutoPlay){
				this.isAutoPlay = false;
				el.className = 'play';
				el.title = '播放';
			}else{
				this.isAutoPlay = true;
				el.className = 'stop';
				el.title = '停止';
			}
			this.autoPlayCheck();
		},
		//动画效果
		animate: function(){
			var currentLeft =  this.slider.parentNode.scrollLeft || 0;
			if(Math.abs(currentLeft-this.point)>40){
				this.slider.parentNode.scrollLeft = (currentLeft>this.point ? currentLeft-40 : currentLeft+40);
			}else{
				this.slider.parentNode.scrollLeft = this.point;
				clearInterval(this.interval);
			}
		},
		//显示效果
		nextPage: function(){
			var self = this;
			var currentLeft =  this.slider.parentNode.scrollLeft || 0,
				w = B.width(this.slideContainer);
			this.point = Math.min(currentLeft+w, B.width(B.$('.B_slider', this.elem))-w);
			this.interval = setInterval(self.animate.bind(self), 50);
		},
		prePage: function(){
			var self = this,
				currentLeft =  this.slider.parentNode.scrollLeft || 0,
				w = B.width(this.slideContainer);
			this.point = Math.max(currentLeft-w, 0);
			this.interval = setInterval(self.animate.bind(self), 50);
		},
		nextPic: function(){
			var nextEl = B.next(B.$('.current', this.slider)) || B.$('span', this.slider);
			this._shiftPage(nextEl);
			var link = B.$('a', nextEl);
			B.trigger(link, 'click');
			this.autoPlayCheck();
		},
		prePic: function(){
			var prevEl = B.prev(B.$('.current', this.slider));
			if(!prevEl){
				var _tmps= B.$$('span', this.slider);
				prevEl = _tmps[_tmps.length-1];
			}
			this._shiftPage(prevEl);
			var link = B.$('a', prevEl);
			B.trigger(link, 'click');
			this.autoPlayCheck();
		},
		_shiftPage: function(el){
			var styleLeft = this.slider.parentNode.scrollLeft || 0;
			if( (el.offsetLeft > B.width(this.slideContainer) + styleLeft) || (el.offsetLeft< styleLeft) ){
				var w = B.width(this.slideContainer);
				this.point = Math.min(el.offsetLeft-8,  B.width(B.$('.B_slider', this.elem))-w);
				clearInterval(this.interval);
				var self = this;
				this.interval = setInterval(self.animate.bind(self), 100);
			}
		},
		autoPlayCheck: function(){
			var self = this;
			this.autoInterval && clearTimeout(this.autoInterval);
			if(this.isAutoPlay){
				this.autoInterval = setTimeout(self.nextPic.bind(self), step);
			}
		}
	};
	B.app.slider = Slider;
});