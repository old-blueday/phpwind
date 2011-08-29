/*
* util.colorPicker 模块
* 颜色选择器
*/
Breeze.namespace('util.colorPicker', function (B) {
    var win = window, doc = document,
	    defaultConfig = {
	        defaultColor: '#000000',
	        callback: function () { }
	    },
    /**
    * 选择器对象
    */
	    colorSelector = {
	        id: 'breeze-colorPicker',
	        load: function () {
	            var _mainColor = ['ffffff;', '000000', 'eeece1', '1f497d', '4f81bd', 'c0504d', '9bbb59', '8064a2', '4bacc6', 'f79646'], //主色
	                _colorList = [['f2f2f2', 'd8d8d8', 'bfbfbf', 'a5a5a5', '7f7f7f'], ['7f7f7f', '595959', '3f3f3f', '262626', '0c0c0c'], //副色列表
	                             ['ddd9c3', 'c4bd97', '938953', '494429', '1d1b10'], ['c6d9f0', '8db3e2', '548dd4', '17365d', '0f243e'],
	                             ['dbe5f1', 'b8cce4', '95b3d7', '366092', '244061'], ['f2dcdb', 'e5b9b7', 'd99694', '953734', '632423'],
                                 ['ebf1dd', 'd7e3bc', 'c3d69b', '76923c', '4f6128'], ['e5e0ec', 'ccc1d9', 'b2a2c7', '5f497a', '3f3151'],
                                 ['dbeef3', 'b7dde8', '92cddc', '31859b', '205867'], ['fdeada', 'fbd5b5', 'fac08f', 'e36c09', '974806']];
	            _standardColor = ['c00000', 'ff0000', 'ffc000', 'ffff00', '92d050', '00b050', '00b0f0', '0070c0', '002060', '7030a0']; //标准色

	            /**
	            * 创建color面板所需DOM结构
	            */
	            var contain = doc.createElement('div'),
                    mainUl = doc.createElement('ul'),
                    listUl = doc.createElement('ul'),
                    standardUl = doc.createElement('ul'),
                    hr = doc.createElement('div'),
                    mainContain = doc.createElement('div'),
                    main = [], list = [], standard = [];
	            mainContain.style.width = '201px';
				mainContain.style.paddingTop = '10px';
	            _mainColor.forEach(function (n) {
	                var li = '<li><span style="background-color:#' + n + '" unselectable="on"></span></li>';
	                main.push(li);
	            });
	            mainUl.innerHTML = main.join('');
	            _colorList.forEach(function (n) {
	                var li = [];
	                n.forEach(function (j) {
	                    var span = '<span style="background-color:#' + j + '" unselectable="on"></span>';
	                    li.push(span);
	                });
	                list.push('<li>' + li.join('') + '</li>');
	            });
	            listUl.innerHTML = list.join('');
	            _standardColor.forEach(function (n) {
	                var li = '<li><span title="#' + n + '" style="background-color:#' + n + '" unselectable="on"></span></li>';
	                standard.push(li);
	            });
	            standardUl.innerHTML = standard.join('');
	            mainContain.appendChild(mainUl); //添加colorPicker主色列表
	            mainContain.appendChild(listUl); //添加颜色列表
	            mainContain.appendChild(hr);
	            mainContain.appendChild(standardUl); //添加标准色列表
	            listUl.className = mainUl.className = 'B_listBg B_cc B_mb5';
	            standardUl.className = 'B_listBg B_cc';
	            hr.className = 'B_hrA B_mb5';
	            contain.id = this.id;
	            contain.style.zIndex = 99999;
	            contain.style.display = 'none';
	            contain.className = 'B_menu B_p10B';
	            contain.appendChild(mainContain);
	            doc.body.appendChild(contain);
	        }
	    };
    /*
    *隐藏颜色选择面板
    *暂时不要,因为在弹窗方法中使用了autoHide
    */
    function hideColorSelector() {
        B.$('#' + colorSelector.id).style.display = 'none';
    }

    /**
    * ColorPiker类
    */
    function ColorPicker(setting, ele) {
        var self = this;
        B.merge(self, defaultConfig, setting);
        if (!(self instanceof ColorPicker)) {
            return new ColorPicker(setting, ele);
        }
        if (!B.$('#' + colorSelector.id)) {
            colorSelector.load();
        }
        B.require('dom', 'event', 'util.dialog', function (B) {
            B.util.dialog({
                pos: ['leftAlign', 'bottom'],
                id: colorSelector.id,
                reuse: true,
                autoHide: true,
                data: B.$('#' + colorSelector.id).innerHTML,
                callback: function () {
                    B.$$('#' + colorSelector.id + ' span').forEach(function (n) {
                        n.className = '';
						n.onmousedown=function(e){
							B.$$("#" + colorSelector.id + ' .current').forEach(function (n) {
                                n.className = '';
                            });
                            this.className = 'current';
                            var color = this.style.backgroundColor;
                            self.callback(B.formatColor(color));
                            hideColorSelector();
							return false;
						}
                        if (B.formatColor(n.style.backgroundColor) == B.formatColor(self.defaultColor)) {
                            n.className = 'current';
                        }
                    });
                }
            }, ele);
        });
    }

    /**
    * @description 颜色选择器
    * @params {String} 要产生颜色选择器的元素
    * @params {String} 默认颜色(如:#ffffff)
    * @params {Function} 点击色块后产生的回调函数
    */
    B.util.colorPicker = function (elem, defaultColor, callback) {
        elem = typeof elem === 'string' ? B.$(elem) : elem;
        if (typeof defaultColor == 'function') {
            callback = defaultColor; defaultColor = '';
        }
        ColorPicker({
            defaultColor: defaultColor,
            callback: callback
        }, elem);
    }
});
/*
整个组件代码由两大部分组成：DOM结构生成(colorSelector类) + 事件处理(ColorPicker类)
*/