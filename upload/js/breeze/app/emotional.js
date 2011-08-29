/*
* app.emotional 模块
* 表情插入模块
*/
Breeze.namespace('app.emotional', function (B) {
    var win = window, doc = document,
	    defaultConfig = {
	        defaultface: defaultface,
	        face: face,
	        pageNum: 24,
	        triggerType: 'click'
	    },
    /**
    * 表情面板选择器对象
    */
	    emotionalSelector = {
	        id: 'breeze-emotional',
	        load: function () {
	            B.require('dom', 'event', function (B) {
	                var contain = B.createElement('<div class="B_menu B_p10" id="' + emotionalSelector.id + '" style="display:none"></div>'),
	                outer = B.createElement('<div style="width:271px"></div>')
	                nav = B.createElement('<div class="B_menu_nav B_cc"></div>'),
	                closeButton = B.createElement('<a style="margin-top: 7px;" class="B_menu_adel" href="javascript:;">×</a>'),
	                facePages = B.createElement('<div class="B_face_pages"></div>'),
	                tabHead = B.createElement('<ul class="B_cc B_fl" style="width: 220px;"></ul>'),
                    df = document.createDocumentFragment();
	                /*
	                * 产生表情组标签以备tab
	                */
	                for (var i in facedb) {
	                    var navli = B.createElement('<li><a href="javascript:;">' + facedb[i] + '</a></li>');
	                    navli.className = i == defaultConfig.defaultface ? 'B_tab_trigger current' : 'B_tab_trigger';
	                    df.appendChild(navli);
	                    df.innerHTML = '';
	                }
	                tabHead.appendChild(df);
	                nav.appendChild(B.createElement('<a class="B_menu_adel B_close" href="javascript:;">×</a>'));
	                nav.appendChild(B.createElement('<div title="上一页" class="B_menu_pre fl">上一页</div>'));
	                nav.appendChild(B.createElement('<div title="下一页" class="B_menu_next fr">下一页</div>'));
	                nav.appendChild(tabHead);
	                outer.appendChild(nav);

	                /*
	                * 产生表情图片
	                */
	                for (var i in faces) {//无法用forEach,因为老版本的数组原因
	                    if (faces.hasOwnProperty(i)) {
	                        var faceList = B.createElement('<div class="B_tab_panel"></div>'),
	                        faceGroup = B.createElement('<ul class="B_face_list B_cc"><ul>'), faceul = [];
	                        faceList.style.display = i == defaultConfig.defaultface ? 'block' : 'none';
	                        for (var j in faces[i]) {
	                            if (faces[i].hasOwnProperty(j)) {
	                                var li = '<li><img data-code="' + faces[i][j] + '" src="' + face[faces[i][j]][0] + '" title="' + face[faces[i][j]][1] + '" /></li>';
	                                faceul.push(li);
	                            }
	                        }
	                        faceGroup.innerHTML = faceul.slice(0, defaultConfig.pageNum).join(''); //只显示第一页的条数
	                        faceList.appendChild(faceGroup);
	                        outer.appendChild(faceList);

	                        /*
	                        * 产生表情分页标签
	                        */
	                        var pageNum = defaultConfig.pageNum, itemCount = faceul.length;
	                        if (itemCount > pageNum) {//需要分页时才创建分页
	                            var pageCount = itemCount % pageNum == 0 ? itemCount / pageNum : Math.floor(itemCount / pageNum) + 1;
	                            var pageGroup = B.createElement('<ul class="B_face_pages B_cc"></ul>');
	                            for (var i = 0; i < pageCount; i++) {
	                                var pageEl = B.createElement('<a href="javascript:;">' + (i + 1) + '</a>');
	                                pageEl.className = i == 0 ? 'current' : "";
	                                (function (i, pageEl, faceul, faceGroup, pageGroup) {//分页事件处理
	                                    B.addEvent(pageEl, 'click', function (e) {
	                                        e.halt();
	                                        faceGroup.innerHTML = faceul.slice(i * pageNum, i * pageNum + defaultConfig.pageNum).join('');
	                                        B.removeClass(B.$('.current', pageGroup), 'current');
	                                        B.addClass(pageEl, 'current');
	                                    });
	                                })(i, pageEl, faceul, faceGroup, pageGroup);
	                                var li = B.createElement('li');
	                                li.appendChild(pageEl);
	                                pageGroup.appendChild(li);
	                            }
	                        }
	                        faceList.appendChild(pageGroup);
	                    }
	                }
	                contain.appendChild(outer);
	                doc.body.appendChild(contain);
	            });
	        }
	    }
    /**
    * 隐藏面板
    */
    function hideEmotionalSelector() {
        B.$('#' + emotionalSelector.id).style.display = 'none';
    }

    /**
    * emotional类
    */
    B.require('util.dialog', function (B) {//add event for Emotional
        var selector = '#' + emotionalSelector.id;
        if(!B.$(selector)) {
            emotionalSelector.load();
        }
        /**
        * 显示表情选择面板
        */
        B.util.dialog({
            id: emotionalSelector.id,
            reuse: true,
            callback:function(popup) {
                /**
                * 回调触发
                */
                B.live(selector + ' .B_face_list li img','click', function (e) {
                        var code = B.attr(this, 'data-code'),
                            src = this.src;
                        insertTrigger('<img src="'+ src +'" title="'+ this.title +'" emotion="'+ code +'" />');
                        //popup.closep();
                });

                /**
                * 关闭面板事件
                */
                B.$$(selector + ' .B_menu_adel').forEach(function (n) {
                    B.addEvent(n, 'click', function (e) {
                        e.preventDefault();
                        hideEmotionalSelector();
                    });
                });


                /**
                * 点击左右箭头切换tab(上一页)
                */
                B.addEvent(B.$(selector + ' .B_menu_pre'), 'click', function (e) {
                    e.preventDefault();
                    //切换tab

                });

                /**
                * 点击左右箭头切换tab(下一页)
                */
                B.addEvent(B.$(selector + ' .B_menu_next'), 'click', function (e) {
                    e.preventDefault();
                    //切换tab

                });
                /**
                * 切换tab时触发
                */
                B.$$(selector + ' .B_tab_trigger').forEach(function (n) {
                    B.addEvent(n, 'click', function (e) {
                        B.$$(selector + ' .B_tab_trigger').forEach(function (n) {
                            B.removeClass(n, 'current');
                        });
                        B.addClass(this, 'current');
                    });
                });
                
                /**
                * 产生tab效果
                */
                B.require('util.scrollable', function (B) {
                    B.util.tabs("#" + emotionalSelector.id);
                });
                popup.closep();
            }
        });
		/**
		* @description 表情选择器
		* @params {le} le
		* @params {Function} 点击点击具体表情产生的回调函数
		*/
		B.app.emotional = function (elem, callback) {
			insertTrigger = callback;
			B.util.dialog({
				id: emotionalSelector.id,
				pos: ['leftAlign', 'bottom']
			},elem);
		}
    });
});

/*
*PS:此组件兼容老版本的表情选择器,整个组件代码由两大部分组成：DOM结构生成(emotionalSelector类) + 事件处理(Emotional类)
*箭头切tab换暂时没有实现,因为tab组件中不包含,在这里写又显得功能多余了,待下一步实现
*/