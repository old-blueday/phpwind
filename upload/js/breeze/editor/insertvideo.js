// JavaScript Document
B.namespace('editor.insertvideo', function(B){
	//create PopUp
	var insertTrigger;
	B.require('util.dialog', function(B){
		B.util.dialog({
			id: 'B_editor_video',
			reuse: true,
			data: '<div class="B_menu B_p10B">\
	<div style="width:310px;">\
		<div class="B_h B_drag_handle"><a href="javascript:;" class="B_menu_adel B_close">×</a>插入视频</div>\
		<form name="B_editor_videoForm" class="B_tableA">\
			<table width="100%" class="B_mb10">\
				<tbody><tr>\
					<td width="60">地址：</td>\
					<td><input name="url" type="text" class="B_input B_fl" size="35"><a class="B_helpA"  onclick="event&&(event.returnValue=false)"><i>支持rm wmn avi flv swf等视频格式链接地址<br />示例：http://server/filename.wmv<br>支持优酷、土豆等视频网站的视频网址</i></a></td>\
				</tr><tr>\
					<td>尺寸：</td>\
					<td><span class="B_mr20"><input name="w" type="text" class="B_input" size="2" value="480"> 宽</span><span><input name="h" type="text" class="B_input" value="400" size="2"> 高</span></td>\
				</tr><tr>\
					<td>设置：</td>\
					<td><label><input name="autoPlay" type="checkbox">自动展开</label></td>\
				</tr>\
			</tbody></table>\
		</form>\
		<div class="B_tac B_p10"><span class="B_btn2"><span><button class="B_sumbit" type="button">提 交</button></span></span><span class="B_bt2"><span><button class="B_close" type="button">取 消</button></span></span></div>\
	</div>\
</div>',
			callback: function(popup){
				var btn = B.$('#B_editor_video .B_sumbit');
				B.addEvent(btn, 'click', function(){
					var form = document.B_editor_videoForm,
					url = form.url.value,
					arg = [form.w.value, form.h.value, form.autoPlay.checked ? 1 : 0].join(','),
					ext = getExt(url);
					var str = '['+ext+'='+arg+']' + url + '[/'+ext+']';
					insertTrigger(str);
					form.reset();
					popup.closep();
				});
			}
		});
		
		//绑定事件
	});
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
	
	B.editor.insertvideo = function(elem, fn){
		insertTrigger = fn;
		B.util.dialog({
			id: 'B_editor_video',
			pos: ['leftAlign', 'bottom']
		}, elem);
	}
});