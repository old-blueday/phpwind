/*
* app.music 模块
* 虾米音乐插入模块
* 因为沿用旧代码，所有有很多全局变量。
*/
Breeze.namespace('app.pagecut', function (B) {
	B.app.pagecut = function(elem, callback, editor) {
		var code = "\n[###page###]\n";
		editor.pasteHTML(code, "");
	}
});