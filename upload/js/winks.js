var myshow_vars = {
	resource: 'http://rs.phpwind.net/',
	flash: 'S',
	wink_id: 'myshow_wink',
	logo_id: 'myshow_pic',
	wink_width: 350,
	wink_height: 350,
	pw_tid: tid,
	pw_page: page
}
var target_e = getObj(myshow_wink['target']);
if (!target_e)
	target_e = $S(myshow_wink['target'])[0];
if (myshow_vars['pw_tid'] && myshow_vars['pw_page'] == 1) {
	wink_build();
	wink_show(false);
}

// wink function
function wink_build(){
	var wink_e = $C('div', myshow_vars['wink_id']);
	target_e.appendChild(wink_e);
	set_style(wink_e,{visibility:'hidden',position:'absolute',width:myshow_vars['wink_width'] + 'px',height:myshow_vars['wink_height'] + 'px'});
	if (is_ie) {
		var script_e = $C('script');
		var head_e = document.getElementsByTagName("head")[0];
		script_e.type = 'text/javascript';
		script_e.language = 'javascript';
		script_e.htmlFor = 'winkFlash';
		script_e.event = 'FSCommand(command,args)';
		script_e.text = "if (command == \"quit\")wink_close();";
		head_e.appendChild(script_e);
	}
	if (window.onresize)
		var myshow_resize_callback = window.onresize;

	window.onresize = function (event){
		wink_position();
		if (myshow_resize_callback)
			myshow_resize_callback(event);
	}
	return wink_e;
}

function winkFlash_DoFSCommand(command,args){
	if (command == "quit")wink_close();
}
function wink_show(force){
	var wink_e = getObj(myshow_vars['wink_id']);
	if (wink_e.style['display'] != 'none' && wink_e.style['visibility'] != 'hidden')return;
	wink_position();
	var cookie = readCookie('myshow_wink');
	if (!cookie)cookie = '';
	if (cookie.indexOf(myshow_vars['pw_tid'] + '#') < 0 || force ){
		t=setInterval("wink_position()", 100);
		wink_e.innerHTML = flash_build('winkFlash',myshow_vars['resource'] + myshow_wink['code'] + myshow_vars['flash'] + '.swf?refer=' + myshow_wink['refer'],myshow_vars['wink_width'], myshow_vars['wink_height']);
		set_style(wink_e,{display:'block',visibility:'visible'});
		if(!force){
			cookie = cookie.split('#');
			cookie[cookie.length - 1] = myshow_vars['pw_tid'];
			cookie =cookie.join('#') + '#';
			createCookie('myshow_wink',cookie);
		}
	}
}

function wink_close(){
	var wink_e = getObj(myshow_vars['wink_id']);
	var flash_e = getObj('winkFlash');
	clearInterval(t);
	if(flash_e){
		if (is_ie)
			wink_e.innerHTML = '';
		else
			flash_e.src = '';
	}
	set_style(wink_e,{display:'none'});
}

function wink_position(){
	var wink_e = getObj(myshow_vars['wink_id']);
	var top  = getTop()+((document.documentElement.clientHeight||document.body.clientHeight)-parseInt(myshow_vars['wink_height']))/2;
	var left = getLeft()+(ietruebody().clientWidth-parseInt(myshow_vars['wink_width']))/2;
	set_style(wink_e,{top:top+"px",left:left+"px"});
}
function logo_onclick(event){
	var event = event || window.event;
	var target_e = event.target || event.srcElement;
	if (target_e.id == 'my_close')
		logo_close();
	else
		wink_show(true);
}
function logo_close(){
	wink_close();
	set_style(getObj(myshow_vars['logo_id']), {display:'none'});
}

function flash_build(id, url, width, height){
	return "<div id=\"player\"><object id=\""+id+"\" CLASSID=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\""+width+"\" height=\""+height+"\"><param name=\"movie\" value=\""+url+"\"><param name=\"play\" value=\"true\"><param name=\"wmode\" value=\"transparent\"><param name=\"allowScriptAccess\" value=\"always\"><param name=\"swliveconnect\" value=\"true\"><param name=\"quality\" value=\"high\"><embed name=\""+id+"\" src=\""+url+"\" width=\""+width+"\" height=\""+height+"\" play=\"true\" loop=\"true\" wmode=\"transparent\" allowScriptAccess=\"always\" swliveconnect=\"true\" quality=\"high\"></embed></object></div>";
}

function $C(tag,id){
	var element = document.createElement(tag);
	if (id)element.id = id;
	return element;
}

function set_style(element, styles){
	if (!element || !styles || !element.style)return false;

	for (var name in styles) {
		try{element.style[name] = styles[name];}catch(e){}
	}
	return true;
}

function $S(className,parentElement){
	var result = [];
	var children = (getObj(parentElement) || document.body).getElementsByTagName('*');
	for (var index = 0; index < children.length; index ++) {
		if (children[index].className.match(new RegExp("(^|\\s)" + className + "(\\s|$)")))
			result.push(children[index]);
	}
	return result;
}

function readCookie(name){
	var start=document.cookie.indexOf(name);
	var end=document.cookie.indexOf(";",start);
	return start==-1 ? null : unescape(document.cookie.substring(start+name.length+1,(end>start ? end : document.cookie.length)));
}

function createCookie(name,value){
	expires=new Date();
	expires.setTime(expires.getTime()+(86400*365));
	document.cookie=name+"="+escape(value)+"; expires="+expires.toGMTString()+"; path=/";
}