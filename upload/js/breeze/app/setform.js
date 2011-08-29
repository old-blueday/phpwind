// JavaScript Document
Breeze.namespace('app.setform', function(B) {
	var callbackTrigger, sel;
	/**
	 * @description 图片选择器
	 * @params {String} 要产生图片选择器的元素
	 * @params {Function} 点击图片后产生的回调函数
	 */
	function closeExtend(e){
		closep();
		document.body.onmousedown = null;
		getObj('pw_box').onmousedown = null;
		read.obj = null;
	}
	window.showform = function(id) {
		ajax.send('pw_ajax.php','action=extend&type=setform&id='+id,ajax.get);
	}
	window.insertform = function(id) {
		var code = '<table width="60%" border="1" bordercolor="#cccccc" style="background-color:#ffffff">';
		code += '<tr><td colspan=2><b>'+id+'</b></td></tr>'
		var ds   = getObj('formstyle').getElementsByTagName('tr');
		for (var i=0;i<ds.length;i++) {
			code += '<tr><td>'+ds[i].firstChild.innerHTML+'</td><td>'+ds[i].lastChild.firstChild.value+'</td></tr>';
		}
		code += '</table>';
		callbackTrigger(code);
		closep();
	};

	B.app.setform = function(elem, fn, editor) {
		if (typeof read == 'object' && read.obj != null && read.obj.id == 'wy_setform') {
			closep();
			read.obj=null;
		} else {
			ajax.send('pw_ajax.php','action=extend&type=setform', function(){
				read.obj = elem;
				ajax.get();
			});
		}
		getObj('pw_box').onmousedown=function(e){
			e = e||event;
			if (e.stopPropagation){
				e.stopPropagation();
			}else{
				e.cancelBubble = true;
			}
		};
		document.body.onmousedown = closeExtend;
		callbackTrigger = fn;
    }
});