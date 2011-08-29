// JavaScript Document
Breeze.namespace('app.post', function(B) {
	var callbackTrigger;
	function code2HTML(str){
		str = str.replace(/</g, '&lt;');
		str = str.replace(/>/g, '&gt;');
		return str.replace(/\r?\n/g, '<br />');
	}
	B.require('util.dialog', function(B){
		B.util.dialog({
			id: 'B_editor_post',
			reuse: true,
			outWin: true,
			pos: ['left','top',-10000, 0],
			data: '<div class="B_menu B_p10B">\
	<div style="width:310px;">\
		<div class="B_h B_drag_handle"><a href="#" class="B_menu_adel B_close">×</a>插入隐藏内容</div>\
		<form name="B_editor_postForm" class="B_tableA mb10">\
			<div class="B_mb5">设置内容隐藏后，其他用户需要回复后才能浏览。</div>\
			<div id="B_editor_postText" class="B_mb10"><textarea name="content" rows="5" style="width:300px;overflow:auto;line-height:1.5;border:1px solid #ccc;"></textarea>\
			</div>\
		</form>\
		<div class="B_tac"><span class="B_btn2 B_submit"><span><button type="button">提 交</button></span></span><span class="B_bt2 B_close"><span><button type="button">取 消</button></span></span></div>\
	</div>\
</div>',
			callback: function(popup){
				//绑定提交按钮
				var btn = B.$('#B_editor_post .B_submit');
				B.addEvent(btn, 'click', function(){
					var form = document.B_editor_postForm,
					content = code2HTML(form.content.value);

					callbackTrigger('[post]' + content + '[/post]');
					form.reset();
					popup.closep();
				});
			}
		});
		
		//绑定事件
	});
	/**
	 * @description 图片选择器
	 * @params {String} 要产生图片选择器的元素
	 * @params {Function} 点击图片后产生的回调函数
	 */
	B.app.post = function(elem, fn, editor) {
		iscollapsed = !editor.isSel();
		if(iscollapsed){
			B.util.dialog({id:'B_editor_post',pos:['leftAlign', 'bottom']},elem);
			callbackTrigger = fn;
		}else{
			fn('[post]' + editor.getSelHtml() + '[/post]');
			//editor.pasteHTML('[post]' + editor.getSelText() + '[/post]');
		}
    }
});