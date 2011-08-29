/*
* app.insertAttach 模块
* 附件插入模块
*/
Breeze.namespace('app.insertAttach', function (B) {
    var win = window, doc = document,
        defaultConfig = {
            rspHtmlPath: 'demo/php/attach_list.html',
            callback: function () { }
        },
        tattachSelector = {
            id: 'editor-insertTattach',
            load: function (elem) {
                var id = this.id;
                B.require('request', 'dom', 'event', 'util.dialog', function (B) {
                    B.ajax({
                        url: defaultConfig.rspHtmlPath,
                        dataType: 'html',
                        cache: false,
                        success: function (data) {
                            B.util.dialog({
                                pos: ['leftAlign', 'bottom'],
                                id: id,
                                data: data,
                                reuse: true,
                                callback: function (popup) {
                                    InsertAttach();
                                }
                            }, elem);
                        },
                        error: function (data) {
                            alert(data);
                        }
                    });
                });
            }
        }

    //事件处理类
    function InsertAttach() {
        var self = this;
        if (!(self instanceof InsertAttach)) {
            return new InsertAttach();
        }
        var selector = '#' + tattachSelector.id;
        /**
        * 产生tab效果
        */
        B.require('util.scrollable', function (B) {
            B.util.tabs("#" + tattachSelector.id);
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

        B.$$(selector + ' .review_bg').forEach(function (n) {
            B.addEvent(n, 'mouseover', function () {
                B.$('div.B_fl', this).style.display = 'block';
            });
            B.addEvent(n, 'mouseout', function () {
                B.$('div.B_fl', this).style.display = 'none';
            });
        });

        /*
        * 无刷新上传后增加一个附件列表项
        */
        B.require('util.ajaxForm', function (B) {
            B.util.ajaxForm('#attach_upload_form', function (data) {
                var table = B.$(selector + ' .B_file_table table'),
                    row = table.rows[1].cloneNode(true),
                    tbody = table.getElementsByTagName("tbody");
                if (tbody) {
                    tbody[0].appendChild(row);
                    B.$('input', row.cells[0]).value = data;
                } else {
                    table.appendChild(row);
                }
                var div = B.$(selector + ' .tattach_list');
                div.scrollTop = div.scrollHeight - div.offsetHeight;
            });
        });
        
        /*
        * 附件编辑
        */
        B.live(selector + ' .attach_edit', 'click', function () {
            alert(this.innerHTML);
        });
        
        /*
        * 附件添加
        */
        B.live(selector + ' .attach_insert', 'click', function () {
            alert(this.innerHTML);
        });
        
        /*
        * 附件删除
        */
        B.live(selector + ' .attach_del', 'click', function () {
            alert(this.innerHTML);
        });
    }
    /**
    * @description 图片选择器
    * @params {String} 要产生附件选择器的元素
    * @params {Function} 选择附件后产生的回调函数
    */
    B.app.insertAttach = function (elem, callback) {
        insertTrigger = callback;
        tattachSelector.load(elem);
    }
});

/*
此组件涉及到先通过ajax加载HTML,所以事件处理类InsertAttach在tattachSelector.load()中实例化,这与colorpicker有点不同,分开来html和event为了更容易维护和阅读
*/