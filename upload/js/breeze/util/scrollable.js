/**
 * @fileoverview 切换组件
 * @author yuyang <yuyangvi@gmail.com>
 * @version 1.0
 * @depends base
 */
Breeze.namespace('util.scrollable',function(B){
	var cfg = {
        triggers: [],
        panels: [],

        // 是否有触点
        hasTriggers: true,

        // 触发类型
        triggerType: 'mouse', // or 'click'
        // 触发延迟
        //delay: .1, // 100ms

        activeIndex: 0, // markup 的默认激活项，应该与此 index 一致
        activeTriggerCls: 'b_current',

        // 可见视图内有多少个 panels
        steps: 1
    };
	function Scrollable(opt){
		var self = this;
		self.triggers = opt.triggers;
		self.panels = opt.panels;
		delete opt.triggers;
		delete opt.panels;
		self.opt = B.merge({}, cfg, opt);
		if(this.opt.hasTriggers){
			B.require('event', function(B){self.bindTrigglers()});
		}
		/*B.$$(triggers)(B.addEvent, trggerType, self.trigger);*/
	};
	Scrollable.prototype = {
		/**
		 * 绑定鼠标事件
		 */
		bindTrigglers: function(){
			var self = this;
			self.triggers.forEach(function(n, i){
				B.addEvent(n, 'click', function(){
					self.focusTrigger(i);
				});
			});
		},
		/**
		 * 触发事件
		 */
		 focusTrigger: function(index){
            //var self = this;
            //if (!self._triggerIsValid()) return; // 重复点击

            //this._cancelSwitchTimer(); // 比如：先悬浮，再立刻点击，这时悬浮触发的切换可以取消掉。
            this.switchTo(index);
		 },
		 
		 /**
		  * 切换效果
		  */
		 switchTo: function(index){
			if (this.opt.activeIndex == index){
				return;
			}
			var self = this, cfg = self.opt,
				triggers = self.triggers, panels = self.panels,
				activeIndex = self.opt.activeIndex,
				steps = cfg.steps,
				fromIndex = activeIndex * steps, toIndex = index * steps;
           	// if (!self._triggerIsValid()) return self; // 再次避免重复触发
            
			/*if (self.fire(EVENT_BEFORE_SWITCH, {toIndex: index}) === false) return self;

            // switch active trigger
            if (cfg.hasTriggers) {
                self._switchTrigger(activeIndex > -1 ? triggers[activeIndex] : null, triggers[index]);
            }

            // switch active panels
            if (direction === undefined) {
                direction = index > activeIndex ? FORWARD : BACKWARD;
            }
			*/
            // switch view
            self.switchView(
                panels.slice(fromIndex, fromIndex + steps),
                panels.slice(toIndex, toIndex + steps),
                index);

            // update activeIndex
            self.opt.activeIndex = index;

            return self; // chain*/
		 },
		 //显示和隐藏
		 switchView: function(fromPanels, toPanels, index){
			/*B.require('util.animate', function(B){*/
				fromPanels.forEach(function(n){
					B.hide(n);
					//B.animaten.style.display = 'none';
				});
				toPanels.forEach(function(n){
					B.show(n);
				});
			/*});*/
			//B.css(fromPanels, DISPLAY, NONE);
            //B.css(toPanels, DISPLAY, BLOCK);
            // fire onSwitch events
            //this._fireOnSwitch(index);

		 }
	};
	/**
	 * Tabs
	 */
	B.util.tabs = function(selector){
		B.$$(selector).forEach(function(n){
			var triggers = B.$$('.B_tab_trigger', n);
			var panels = B.$$('.B_tab_panel', n);
			new Scrollable({triggers:triggers,panels:panels});
		});
	}
});