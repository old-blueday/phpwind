/*
* app.music 模块
* 虾米音乐插入模块
* 因为沿用旧代码，所有有很多全局变量。
*/
function getMusic(page) {
	var keyword = getObj('xiami_music').value;
	if (keyword == '') {
		alert(I18N['Musickeyword']);
		getObj("xiami_music").focus();
		return false;
	}
	ajax.send('apps.php?q=music&ajax=1','page='+page+'&keyword='+keyword,function(){
		var rText = ajax.request.responseText.split('\t');
		if (rText[0] == 'success') {
			getObj('music_list').innerHTML = rText[1];
		} else if (rText[0] == 'close') {
			showDialog('error',I18N['Musiceditor']);
		} else {
			showDialog('error','The insert is error');
		}
	});
	return false;
}
function insert_xiami_music(id){
	var music_info = document.getElementById(id).value;
	if (music_info != ""){
		var info_array		= music_info.split("&");
		var music_id		= info_array[0];
		var music_name		= info_array[1];
		var music_ubb_tag	= "[music=" + music_id + "]" + music_name + "[/music]" ;
		editor.pasteHTML(music_ubb_tag, "");
		closep();
	}
}
Breeze.namespace('app.music', function (B) {
	B.app.music = function(elem, callback, editor) {
		var menu_editor = getObj("menu_editor");
		if (menu_editor == null) {
			menu_editor = B.createElement('<div id="menu_editor" style="display:none"></div>');
			document.body.appendChild(menu_editor);
		}
		menu_editor.innerHTML = '<div style="width:350px;"><div class="B_h" onmousedown="read.move(event);"><a href="javascript:;" onclick="closep();return false;" class="B_menu_adel" title="'+I18N['close']+'">'+I18N['close']+'</a>'+I18N['Musicinsert']+'</div><div class="mb10 cc"><input type="text" id="xiami_music" name="keyword" class="input fl mr5" style="width:290px;" maxlength=28/><span class="fl"><span class="B_btn2" style="margin:0;"><span><button type="button" onclick="getMusic(1);">'+I18N['Musicsearch']+'</button></span></span></span></div><div id="music_list">'+I18N['Musicdesc']+'</div></div>';
		read.open('menu_editor',elem,'2',22);
		getObj('pw_box').className = 'B_menu B_p10B';
		getObj("xiami_music").focus();
	}
});