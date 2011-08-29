// JavaScript Document
Breeze.namespace('app.sell', function(B) {
	var callbackTrigger;
	B.require('util.dialog', function(B){
		B.util.dialog({
			id: 'B_editor_sell',
			reuse: true,
			pos: ['left','top',-10000, 0],
			data: '<div class="B_menu B_p10">\
	<div style="width:310px;">\
		<div class="B_h B_drag_handle"><a href="#" class="B_menu_adel B_close">×</a>插入出售内容</div>\
		<form name="B_editor_sellForm" class="B_tableA mb10">\
			<div class="B_mb5 B_sellStatus">已设置<span></span>处出售内容，设置为<span></span>，可在纯文本模式第一处出售内容处修改出售信息</div>\
			<div class="B_firstSell">\
			<div class="B_mb5">允许设置最高售价<span style="color:#ff5500;">10</span>，限制最高收入<span style="color:#ff5500;">100</span></div>\
			<div class="B_cc B_mb5">\
				<div class="B_fl">售价：<input name="price" type="text" class="B_input" size="2" style="margin:0;" value="2"></div>\
				<div class="B_icoDown" style="width:9px;background:none;margin:0 10px 0 0;border:1px solid #ccc;height:19px;"><em></em></div>\
				<div class="B_fl" id="B_editor_sellList" style="display:none">\
					<div class="B_menu" style="width:40px;margin-top:21px;margin-left:26px;">\
						<ul class="B_menulist">\
							<li><a href="#">1</a></li>\
							<li><a href="#">3</a></li>\
							<li><a href="#">5</a></li>\
							<li><a href="#">7</a></li>\
							<li><a href="#">9</a></li>\
						</ul>\
					</div>\
				</div>\
				<select name="unt"><option value="ww">威望</option><option value="money">金钱</option></select>\
			</div></div>\
			<div id="B_editor_sellText" class="B_mb10"><textarea name="content" rows="5" style="width:300px;overflow:auto;line-height:1.5;border:1px solid #ccc;"></textarea>\
			</div>\
		</form>\
		<div class="B_tac"><span class="B_btn2 B_submit"><span><button type="button">提 交</button></span></span><span class="B_bt2 B_close"><span><button type="button">取 消</button></span></span></div>\
	</div>\
</div>',
			callback: function(popup){
				//绑定下拉按钮
				var dropdown = B.$('#B_editor_sell .B_icoDown'), droplist = B.$('#B_editor_sellList');
				B.addEvent(dropdown, 'click',  function(B){
					droplist.style.display = '';
				});
				B.addEvent(droplist.getElementsByTagName('ul')[0], 'click', function(e){
					var val = B.UA.ie? e.target.innerText : e.target.textContent;
					document.B_editor_sellForm.price.value = val;
					droplist.style.display = 'none';
				});
				//绑定提交按钮
				var btn = B.$('#B_editor_sell .B_submit');
				B.addEvent(btn, 'click', function(){
					var form = document.B_editor_sellForm,
					price = form.price.value,
					unt = form.unt.value,
					content = form.content.value;
					callbackTrigger('[sell='+price+','+unt+']' + content + '[/sell]');
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
	B.app.sell = function(elem, fn, editor) {
		var ubbcode = editor.getUBB(), form = document.B_editor_sellForm,
		res = ubbcode.match(/\[sell=[\d]+(,[\w]+)?]/ig),
		iscollapsed = editor.getRng().collapsed;
		n = (res==null) ? 0 : res.length;
		if(n){
			if(!iscollapsed){
				fn(res[0] + editor.getSelText() + '[/sell]');
				return;
			}
			var stat = B.$('#B_editor_sell .B_sellStatus'),
			sel = B.$('#B_editor_sell select'),
			res = /\[sell=([\d]+)(,([\w]+))?]/.exec(res[0]);
			var spans = B.$$('span', stat);
			spans[0].innerHTML = n;
			
			//获取select
			var unt;
			for(var i = 0; i < sel.options.length; i++){
				if(sel.options[i].value == res[3]){
					unt = sel.options[i].text;
					break;
				}
			}
			spans[1].innerHTML = res[1] + unt;
			form.price.value = res[1];
			form.unt.value = unt;
			
			B.css(B.$('#B_editor_sell .B_firstSell'), 'display', 'none');
			B.css(stat, 'display', '');
	
		}else{
			B.css(B.$('#B_editor_sell .B_sellStatus'), 'display', 'none');
			B.css(B.$('#B_editor_sell .B_firstSell'), 'display', '');
		}
		B.$('#B_editor_sellText').style.display = iscollapsed ? '' : 'none';
		if(!iscollapsed){
			form.content.value = editor.getSelText();
		}
		B.util.dialog({id:'B_editor_sell',pos:['leftAlign', 'bottom']},elem);
		callbackTrigger = fn;
    }
});