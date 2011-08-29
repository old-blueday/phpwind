// JavaScript Document
B.namespace('editor.inserttable', function(B){
	//create PopUp
	B.require('util.dialog', function(B){
		B.util.dialog({
			id: 'B_editor_table',
			reuse: true,
			pos: ['left','top',-10000, 0],
			outWin: true,
			data: '<div class="B_menu B_p10B">\
	<div style="width: 250px;">\
		<div class="B_h B_drag_handle"><a class="B_menu_adel B_close" href="#">×</a>插入表格</div>\
		<form name="B_editor_tableForm" class="B_tableA">\
			<table width="100%" class="B_mb10"><tbody>\
				<tr><td width="75">行数</td><td><input type="text" size="5" class="B_input B_mr20" name="tRows" value="2"></td></tr>\
				<tr><td>列数</td><td><input type="text" size="5" class="B_input" name="tCols" value="2"></td></tr>\
				<tr><td>表格宽度</td><td><input type="text" size="5" class="B_input B_mr5" name="tWidth" value="100"><select name="tUnit"><option value="%">百分比</option><option value="">像素</option></select></td></tr>\
				<tr><td>边框颜色</td><td><input type="text" size="5" value="#dddddd" class="B_input B_mr5 B_fl" name="tBorderColor"><a class="B_ico"><div class="B_backColor" title="边框色"><span style="background:#dddddd"></span>边框色</div></a></td></tr>\
				<tr><td>背景颜色</td><td><input type="text" size="5" value="#ffffff" class="B_input B_mr5 B_fl" name="tBackColor"><a class="B_ico"><div class="B_backColor" title="背景色"><span style="background:#ffffff"></span>背景色</div></a></td></tr>\
				<tr><td>边框大小</td><td><input name="tBorderWidth" type="text" class="B_input B_mr5" value="1" size="5"></td></tr>\
				</tbody></table></form>\
		<div class="B_tac B_p10"><span class="B_btn2"><span><button class="B_submit" type="button">提 交</button></span></span><span class="B_bt2"><span><button type="button" class="B_close">取 消</button></span></span></div>\
	</div></div>',
			callback: function(popup){
				var btn = B.$('#B_editor_table .B_submit');
				B.removeEvent(btn, 'click');
				B.addEvent(btn, 'click', function(){
					var form = document.B_editor_tableForm,
					cols = form.tCols.value,
					rows = form.tRows.value,
					str = 'cellspacing="0" border="' + form.tBorderWidth.value + '" bordercolor="'+ form.tBorderColor.value +'" width="'+form.tWidth.value + form.tUnit.value + '" style="background-color: '+ form.tBackColor.value+'"';
					str = '<table '+str+'>';
					var row = ''
					for(var i = 0; i < cols; i++){
						row += '<td>&nbsp;</td>';
					}
					for(var i=0; i < rows; i++){
						str+='<tr>'+row+'</tr>';
					}
					str += '</table><br/>';
					insertTrigger(str);
					form.reset();
					popup.closep();
				});
				B.$$('#B_editor_table .B_ico').forEach(function(n){
					B.addEvent(n, 'click', function(e){
						B.require('util.colorPicker', function(){
							var span = B.$('span', n), color = B.formatColor(span.style.backgroundColor);
							B.util.colorPicker(n, color, function(newColor){
								B.css(span, 'backgroundColor', newColor);
								B.prev(n).value = newColor;
							});
						});
						e.halt();
					});
				});
			}
		});
		//绑定事件
	});
	B.editor.inserttable = function(elem,  fn){
		insertTrigger = fn;
		B.util.dialog({
			id: 'B_editor_table',
			pos: ['leftAlign','bottom']
		}, elem);
	}
});