/*
* app.insertAttach 模块
* 附件插入模块
*/
Breeze.namespace('app.insertAttach', function (B) {
	//flash控件
	var $ = B.query, index = 1;
	var win = window, doc = document,
		defaultConfig = {
			rspHtmlPath: attachConfig.url,
			callback: function () { }
		},
		tattachSelector = {
			id: 'editor-insertTattach',
			load: function (elem, myeditor) {
				var id = this.id;
				B.require('util.dialog', function (B) {
					B.util.dialog({
						pos: ['leftAlign', 'bottom'],
						id: id,
						data: '<div class="B_menu B_p10B">\
	<div style="width:480px;">\
		<div class="B_h B_cc B_drag_handle">\
			<a href="javascript://" class="B_menu_adel B_close" style="margin-top:2px;">×</a>附件上传\
		</div>\
		<!--附件列表开始-->\
		<div style="padding-top:5px">\
			<div class="B_mb10 cc">\
				<a id="B_sm_cfg" href="javascript://" class="B_fr">显示“售密设置”</a>\
				<span id="B_uploader_container"><span id="B_uploader_flash"><span id="uploaderTmpSpan" style="display:none;"><embed style="display:none;" src="images/blank.swf" type="application/x-shockwave-flash" wmode="transparent"/><em class="s2" style="position:relative;top:3px;">该浏览器尚未安装flash插件，<a href="http://www.adobe.com/go/getflashplayer" target="_blank">点击安装</a></em></span></span></span>\
			</div>\
			<div class="B_file">\
				<div class="cc">\
					<dl style="background:#f7f7f7;">\
						<dt>附件名&nbsp;<span>(您还可以上传<span class="restCount s4"></span>个附件)</span></dt>\
						<dd>附件描述</dd>\
						<dd class="B_file_dd">操作</dd>\
					</dl>\
					<div id="B_qlist"></div>\
					<!--选择文件后 显示的列表-->\
				</div>\
			</div>\
			<div class="B_file_tips">\
				<a class="B_helpA" style="float:right;padding:0 0 0 18px;width:auto;" onclick="event&&(event.returnValue=false);">可上传类型<i id="attach_allow_filetype"></i></a>\
				<label><input name="atc_hideatt" type="checkbox" value="1" style="padding:0;margin:-2px 0 0;"> 隐藏附件（回复可见）<input type="hidden" name="isAttachOpen" value="1" /></label>\
			</div>\
		<!--结束-->\
	</div>\
</div>',
						reuse: true,
						callback: function (popup) {
							B.require('global.uploader',function(){
								uploader.init('uploader',myeditor);
								B.addEvent(B.$('#B_sm_cfg'), 'click', uploader.toggleSelect);
							});
							myeditor.area.appendChild(popup.win);
						}
					}, elem);
				});
			}
		};

    /**
    * @description 图片选择器
    * @params {String} 要产生附件选择器的元素
    * @params {Function} 选择附件后产生的回调函数
    */
	B.app.insertAttach = function (elem, callback, editor) {
		insertTrigger = callback;
		myeditor = editor;
		if (B.$('#'+tattachSelector.id)){
			B.util.dialog({
				id: tattachSelector.id,
				reuse: true,
				callback:function(){uploader.init('uploader',myeditor);},
				pos: ['leftAlign', 'bottom']
			}, elem);
		} else {
			tattachSelector.load(elem, myeditor);
		}
    }
});

/*
此组件涉及到先通过ajax加载HTML,所以事件处理类InsertAttach在tattachSelector.load()中实例化,这与colorpicker有点不同,分开来html和event为了更容易维护和阅读
*/