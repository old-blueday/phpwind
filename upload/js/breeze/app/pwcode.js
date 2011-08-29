// JavaScript Document
Breeze.namespace('app.pwcode', function(B) {
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
	window.upcode = function (id,param) {
		var d = getObj(id).lastChild.innerHTML.split('|');
		var t = id.substr(id.indexOf('_')+1);
		var c = new Array();
		for (var i=0;i<param;i++) {
			do{
				c[i] = prompt(d[i],'');
				if (c[i] == null)
					return;
			}while (c[i]=='');
		}
		switch(param) {
			case '2' : code = '[' + t + '=' + c[0] + ']' + c[1] + '[/' + t + ']';break;
			case '3' : code = '[' + t + '=' + c[0] + ',' + c[1] + ']' + c[2] + '[/' + t + ']';break;
			default: code = '[' + t + ']' + c[0] + '[/' + t + ']';break;
		}
		callbackTrigger(code,'');
		closep();
	};
	B.app.pwcode = function(elem, fn, editor) {
		if (typeof read == 'object' && read.obj != null && read.obj.id == 'wy_pwcode') {
			closep();
			read.obj=null;
		} else {
			ajax.send('pw_ajax.php','action=extend&type=pwcode', function(){
				read.obj = elem;//有个找不到在哪里的地方把这个删掉了。所以挪到后面操作。
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