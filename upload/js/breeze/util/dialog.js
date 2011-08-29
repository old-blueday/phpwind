/**
* @fileoverview 基础弹窗
*
* @author yuyang <yuyangvi@gmail.com>
* @version 1.0
*/
Breeze.namespace('util.dialog', function(B){
	var dialogDef = {
		/**
		 * 默认的弹窗ID
		 * @type String
		 */
		id: 'pw_box',
		/**
		 * 是否重复利用已经产生的弹窗
		 * @type Boolean
		 */
		reuse: true,
		/**
		 * 弹窗的内容HTML
		 * @type String
		 */
		data: null,
		/**
		* 定位设置
		* @type Array
		* 四个元素的数组，分别如下
		* <ol><li>第一个元素为'left'(左沿相切),'leftalign'（左沿对齐）,'center'(居中),'right'（右沿相切）,'rightalign'（右沿对齐）中的一个,</li>
		* <li>第二个元素为'top'(顶沿相切),'topalign'(顶沿对齐),'center'(居中),'bottom'(底沿相切),'bottomalign'(底沿对齐)中的一个</li>
		* <li>第三个元素为数字，表示向右的偏移值,不存在则设为0</li>
		* <li>第四个元素为数字，表示向下的偏移值,不存在则设为0</li></ol>
		*/
		pos: ['center','center',0,0],
		/**
		 * 定位设置的参照物
		 * @type HTMLElement
		 */
		posrel: null,
		/**
		 * 鼠标移开自动关闭
		 * @type Boolean
		 */
		autoHide: false, 
		/**
		 * 遮罩
		 * @type Boolean
		 */
		mask: false,
		/**
		 * 弹窗弹出后的执行函数，可以用此绑定事件在里面。
		 * @type Function
		 */
		callback: null
	};
	
	/**
	 * @class 遮罩类
	 */
	function Mask(){
		this.mask;
	}
	Mask.prototype={
		create: function(){
			if(this.mask){
				B.css(this.mask, 'display', '');
			}else{
				this.mask =  B.createElement('div', {}, {
					position: 'absolute',
					top: 0,
					left: 0,
					width: B.width(window),
					height: B.height(window),
					'background-color': '#000000',
					opacity: 0.6
				});
				var self = this;
				B.require('event', function(B){
					B.addEvent( window, 'resize', function(){
						self.resize.call(self);
					});
				}); 
				document.body.appendChild(this.mask);
			}
		},
		closep: function(){
			B.css(this.mask, 'display', 'none');
		},
		distory: function(){
			B.removeElement(this.mask);
		},
		resize: function(){
			B.css(this.mask,{
				width: B.width(window),
				height: B.height(window)
			});
		}
	};
	var mask = new Mask();
	/**
	 * 弹窗类
	 */
	function Dialog(setting, ele)
	{
		if( !(this instanceof Dialog) ){
			return new Dialog(setting, ele);
		}
		
		/**
		 * 最终的设置
		 * @private {Setting}
		 */
		var popwin, self = this;
		B.merge(self, dialogDef, setting);
		ele && (self.posrel = ele);
		
		/*
		* IE6隐藏select
		*/
		if(B.UA.ie === 6) {
		    B.$$('select').forEach(function(n){
		        n.style.visibility = 'hidden';
		    });
		}
		/**
		 * 展开弹窗
		 */
		B.require('dom', function(B){
			var popwin = B.$('#' + self.id);
			//设定遮罩
			if(self.mask){
				mask.create();
			}
			if (!popwin || !self.reuse){//如果弹窗没建立
				//生成弹窗
				popwin = B.$query(B.createElement(self.data))(B.attr, 'id', self.id)(B.css,{position:'absolute',visibility:'hidden'})();
				
				//设定高宽
				self.width && B.css(popwin, 'width', self.width);
				self.height && B.css(popwin, 'height', self.height);
				document.body.appendChild(popwin);
				//绑定关闭事件
				B.require('event', function(B){
					B.$$query('.B_close', popwin)(B.addEvent, 'click', function(e){
						self.closep();
					});
				});
			}else{
				B.css(popwin, {display:'block', visibility:'hidden'});
			}
			//显示
			self.win = B.$query(popwin)(layerOut, self.pos, self.posrel)(B.css, {visibility:'visible', backgroundColor:'#ffffff'})();
			self.callback && self.callback(self);
			//绑定事件
			if(self.autoHide){
				B.require('event',function(B){
					var stopp = function(e){
						e.stopPropagation();
					};
					var closep = function(e){
						self.closep();
						B.removeEvent(document, 'click', closep);
						B.removeEvent(self.posrel, 'click', stopp);
						B.removeEvent(popwin,   'click', stopp);
					}
					B.addEvent(document, 'click', closep);
					B.addEvent(self.posrel, 'click', stopp);
					B.addEvent(popwin,   'click', stopp);
				});
			}
			
			//绑定拖动
			if(B.$('.B_drag_handle', popwin)){
				B.require('util.draggable', function(){
					B.util.draggable('#'+self.id, '.B_drag_handle');
				});
			}
		});
		
		return self;
	}
	
	Dialog.prototype = {
		closep: function(){
			if(this.reuse){
				B.css(this.win, 'display', 'none');
			} else {
				B.remove(this.win);
			}
			this.mask && mask.closep();
			var self = this;
			//ie6 select处理
			if(B.UA.ie === 6) {
		        B.$$('select').forEach(function(n){
		            n.style.visibility = 'visible';
		        });
		    }
			//TODO:回收内存
			setTimeout(function(){delete self;}, 0);
			//delete this;
		}
	};
	
	B.util.dialog = Dialog;

	/**
	 * 定位的位置
	 * @type Array
	 */
	function layerOut(popwin, pos, rel)
	{
		var res = rel ? B.offset(rel) : 
			{
				left: (pos[0].indexOf('left') < 0) ? B.width(document.body) : 0,
				top:  (pos[1].indexOf('top') < 0) ? B.height(document) : 0
			};
		//相对于整个页面的设置中，相切变为对齐
		if (!rel) {
			['left','right'].indexOf(pos[0])>-1 && (pos[0]+='Align');
			['top','bottom'].indexOf(pos[1])>-1 && (pos[1]+='Align');
		}
		
		//配置X轴位置
		if (pos[0].indexOf('right') > -1 || pos[0]=='center') {
			pos[0] == 'center' && rel && (res.left *= 2);
			rel && (res.left += B.width(rel));
		}

		if (['left', 'rightAlign', 'center'].indexOf(pos[0])>-1){
			res.left -= B.width(popwin);
			pos[0] == 'center' && (res.left /= 2);
		}
		res.left += B.scrollLeft();
		
		//配置Y轴位置
		if (pos[1].indexOf('bottom') > -1 || pos[1]=='center') {
			pos[1] == 'center' && rel && (res.top *= 2);
			rel && (res.top += B.height(rel));
		}

		if (['top', 'bottomAlign','center'].indexOf(pos[1])>-1) {
			res.top -= B.height(popwin);
			pos[1] == 'center' && (res.top /= 2);
		}
		res.top += B.scrollTop();
		
		//配置偏移
		pos[2] && (res.left += parseInt(pos[2]));
		pos[3] && (res.top += parseInt(pos[3]));
		
		//防止移动到屏幕外面
		if( res.left < B.scrollLeft() ){
			res.left = B.scrollLeft();
		}else if( res.left > B.scrollLeft()+B.width(window)-B.width(popwin) ){
			res.left = B.scrollLeft()+B.width(window)-B.width(popwin);
		}
		
		if( res.top < B.scrollTop() ){
			res.top = B.scrollTop();
		}else if( res.top > B.scrollTop()+B.height(window)-B.height(popwin) ){
			res.top = B.scrollTop()+B.height(window)-B.height(popwin);
		}
		
		B.css(popwin, res);
	}
	
	/**
	 * 提醒和警告
	 */
	B.util.alert = function(str){
		Dialog({
			pos: ['center', 'center'],
			data:'<div class="B_dialog_alert"><h4>警告</h4><p>'+str+'</p><div><input type="button" class="B_close" value="关闭" /></div></div>',
			reuse: true,
			mask:true
		});
	}
});