// JavaScript Document
B.namespace('editor.code', function(B){
	//create PopUp
	var insertTrigger;
	function shiftHTML(str){
		if (editor.currentMode == 'default') {
			str = str.replace(/<\/(p|div)>/g, '').replace(/^(\s*)<(p|div)>/g, '$1');
			str = str.replace(/<(p|div|br(\s\/)?)>/g, '</li><li>');
		} else {
			str = str.replace(/\r?\n/g, '</li><li>');
		}
		return '<ol class="B_code"><li>' + str + '</li></ol><br/>';
	}
	function code2HTML(str){
		str = str.replace(/</g, '&lt;');
		str = str.replace(/>/g, '&gt;');
		return '<ol class="B_code"><li>' + str.replace(/\n/g, '</li><li>') + '</li></ol><br/>';
	}
	function showWin(elem){
		B.require('util.dialog', function(B){
			B.util.dialog({
				id: 'B_editor_code',
				reuse: true,
				data: '<div class="B_menu B_p10B">\
		<div style="width:310px;">\
			<div class="B_h B_drag_handle"><a href="#" class="B_menu_adel B_close">×</a>插入代码内容</div>\
			<form name="B_editor_codeForm" class="B_tableA mb10">\
				<div id="B_editor_codeText" class="B_mb10"><textarea name="content" rows="5" style="width:300px;overflow:auto;line-height:1.5;border:1px solid #ccc;"></textarea>\
				</div>\
			</form>\
			<div class="B_tac"><span class="B_btn2 B_submit"><span><button class="B_submit" type="button">提 交</button></span></span><span class="B_bt2 B_close"><span><button type="button">取 消</button></span></span></div>\
		</div>\
	</div>',
				callback: function(popup){
					var btn = B.$('#B_editor_code .B_submit');
					btn.onclick = function(){
						var form = document.B_editor_codeForm,
							content = code2HTML(form.content.value);
						insertTrigger(content)
						form.reset();
						popup.closep();
					};
				},
				pos: ['leftAlign', 'bottom']
			}, elem);
			
			//绑定事件
		});
	}	
	B.editor.code = function(elem, fn, txt) {
		txt == "\r\n<p>&nbsp;</p>" && (txt = '');
		if(!txt){
			showWin(elem);
			insertTrigger = fn;
		}else{
			fn( shiftHTML(editor.getSelHtml()) );
		}
    }
});