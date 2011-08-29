// JavaScript Document
B.namespace('editor.blockquote', function(B){
	//create PopUp
	var insertTrigger;
	function code2HTML(str){
		str = str.replace(/</g, '&lt;');
		str = str.replace(/>/g, '&gt;');
		return str.replace(/\r?\n/g, '<br />');
	}
	function showWin(elem){
		B.require('util.dialog', function(B){
			B.util.dialog({
				id: 'B_editor_quote',
				reuse: true,
				data: '<div class="B_menu B_p10B">\
		<div style="width:310px;">\
			<div class="B_h B_drag_handle"><a href="#" class="B_menu_adel B_close">×</a>插入引用内容</div>\
			<form name="B_editor_quoteForm" class="B_tableA mb10">\
				<div id="B_editor_quoteText" class="B_mb10"><textarea name="content" rows="5" style="width:300px;overflow:auto;line-height:1.5;border:1px solid #ccc;"></textarea>\
				</div>\
			</form>\
			<div class="B_tac"><span class="B_btn2 B_submit"><span><button class="B_submit" type="button">提 交</button></span></span><span class="B_bt2 B_close"><span><button type="button">取 消</button></span></span></div>\
		</div>\
	</div>',
				callback: function(popup){
					var btn = B.$('#B_editor_quote .B_submit');
					btn.onclick = function(){
						var form = document.B_editor_quoteForm,
						content = '<blockquote class="blockquote">' + code2HTML(form.content.value) + '</blockquote><br>';
						insertTrigger(content);
						form.reset();
						popup.closep();
					};
				},
				pos: ['leftAlign', 'bottom']
			}, elem);
			
			//绑定事件
		});
	}
	function getExt(url){
		if (typeof url != 'string'){
			return '';
		}
		var extIndex = url.lastIndexOf('.');
		if (extIndex < 0){
			return 'flash';
		}
		ext = url.substr(extIndex+1).toLowerCase();
		var type = ['wmv', 'rm', 'rmvb'].indexOf(ext);
		switch(type){
			case -1:
				return 'flash';
			case 0:
				return ext;
			default:
				return 'rm';
		}
	}
	
	B.editor.blockquote = function(elem, fn, txt) {
		txt == "\r\n<p>&nbsp;</p>" && (txt = '');
		window.txt = txt;
		if(!txt){
			showWin(elem);
			//B.util.dialog({id:'B_editor_quote',pos:['leftAlign', 'bottom']},elem);
			insertTrigger = fn;
		}else{
			fn('<blockquote class="blockquote">' + editor.getSelHtml() + '</blockquote>');
		}
    }
});