function player(id,url,width,height,type) {
	if (!IsElement('p_' + id)) {
		var player = document.createElement('div');
		player.id  = 'p_' + id;
		player.style.cssText = 'display:block;margin:5px 0 0 2px';
		player.innerHTML = eval('player_' + type)(url.replace('"',''),width,height);
		setTimeout(function(){getObj(id).appendChild(player)},200);
	} else {
		var p = getObj(id);
		p.removeChild(p.lastChild);
	}
}
function player_rm(url,width,height){
	if (is_ie) {
		return "<object classid=\"CLSID:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\" width=\""+width+"\" height=\""+height+"\"><param name=\"src\" value=\""+url+"\" /><param name=\"controls\" value=\"Imagewindow\" /><param name=\"console\" value=\"clip1\" /><param name=\"autostart\" value=\"true\" /></object><br /><object classid=\"CLSID:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\" width=\""+width+"\" height=\"44\"><param name=\"src\" value=\""+url+"\" /><param name=\"controls\" value=\"ControlPanel,StatusBar\" /><param name=\"console\" value=\"clip1\" /><param name=\"autostart\" value=\"true\" /></object>";
	} else if (agt.indexOf('firefox')!=-1) {
		return "<object data=\""+url+"\" type=\"audio/x-pn-realaudio-plugin\" width=\""+width+"\" height=\""+height+"\" autostart=\"true\" console=\"clip1\" controls=\"Imagewindow\"><embed src=\""+url+"\" type=\"audio/x-pn-realaudio-plugin\" autostart=\"true\" console=\"clip1\" controls=\"Imagewindow\" width=\""+width+"\" height=\""+height+"\"></embed></object><br /><object data=\""+url+"\" type=\"audio/x-pn-realaudio-plugin\" width=\""+width+"\" height=\"44\" autostart=\"true\" console=\"clip1\" controls=\"ControlPanel,StatusBar\"><embed src=\""+url+"\" type=\"audio/x-pn-realaudio-plugin\" autostart=\"true\" console=\"clip1\" controls=\"ControlPanel,StatusBar\" width=\""+width+"\" height=\"44\"></embed></object>";
	} else if (agt.indexOf('safari')!=-1) {
		return "<object type=\"audio/x-pn-realaudio-plugin\" width=\""+width+"\" height=\""+height+"\"><param name=\"src\" value=\""+url+"\" /><param name=\"controls\" value=\"Imagewindow\" /><param name=\"console\" value=\"clip1\" /><param name=\"autostart\" value=\"true\" /></object><br /><object type=\"audio/x-pn-realaudio-plugin\" width=\""+width+"\" height=\"44\"><param name=\"src\" value=\""+url+"\" /><param name=\"controls\" value=\"ControlPanel,StatusBar\" /><param name=\"console\" value=\"clip1\" /><param name=\"autostart\" value=\"true\" /></object>";
	} else {
		return "<object classid=\"clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\" width=\""+width+"\" height=\""+height+"\"><param name=\"src\" value=\""+url+"\" /><param name=\"controls\" value=\"Imagewindow\" /><param name=\"console\" value=\"clip1\" /><param name=\"autostart\" value=\"true\" /><embed src=\""+url+"\" type=\"audio/x-pn-realaudio-plugin\" autostart=\"true\" console=\"clip1\" controls=\"Imagewindow\" width=\""+width+"\" height=\""+height+"\"></embed></object><br /><object classid=\"clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\" width=\""+width+"\" height=\"44\"><param name=\"src\" value=\""+url+"\" /><param name=\"controls\" value=\"ControlPanel\" /><param name=\"console\" value=\"clip1\" /><param name=\"autostart\" value=\"true\" /><embed src=\""+url+"\" type=\"audio/x-pn-realaudio-plugin\" autostart=\"true\" console=\"clip1\" controls=\"ControlPanel,StatusBar\" width=\""+width+"\" height=\"44\"></embed></object>";
	}
}
function player_flash(url,width,height){
	if (is_ie) {
		return "<object classid=\"CLSID:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\""+width+"\" height=\""+height+"\"><param name=\"src\" value=\""+url+"\" /><param name=\"autostart\" value=\"true\" /><param name=\"loop\" value=\"true\" /><param name=\"quality\" value=\"high\" /></object>";
	} else {
		return "<object data=\""+url+"\" type=\"application/x-shockwave-flash\" width=\""+width+"\" height=\""+height+"\"><param name=\"autostart\" value=\"true\" /><param name=\"loop\" value=\"true\" /><param name=\"quality\" value=\"high\" /><EMBED src=\""+url+"\" quality=\"high\" width=\""+width+"\" height=\""+height+"\" TYPE=\"application/x-shockwave-flash\" PLUGINSPAGE=\"http://www.macromedia.com/go/getflashplayer\"></EMBED></object>";
	}
}
function player_wmv(url,width,height) {
	if (height<64) height = 64;
	if (is_ie) {
		return "<object classid=\"CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95\" width=\""+width+"\" height=\""+height+"\"><param name=\"src\" value=\""+url+"\" /><param name=\"ShowStatusBar\" value=\"true\" /></object>";
	} else if (agt.indexOf('firefox')!=-1) {
		return "<object data=\""+url+"\" type=\"application/x-mplayer2\" width=\""+width+"\" height=\""+height+"\" ShowStatusBar=\"true\"><embed type=\"application/x-mplayer2\" src=\""+url+"\" width=\""+width+"\" height=\""+height+"\" ShowStatusBar=\"true\"></embed></object>";
	} else if (agt.indexOf('safari')!=-1) {
		return "<object type=\"application/x-mplayer2\" width=\""+width+"\" height=\""+height+"\"><param name=\"src\" value=\""+url+"\" /><param name=\"ShowStatusBar\" value=\"true\" /></object>";
	} else {
		return "<object classid=\"CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95\" width=\""+width+"\" height=\""+height+"\"><param name=\"src\" value=\""+url+"\" /><param name=\"ShowStatusBar\" value=\"true\" /><embed type=\"application/x-mplayer2\" src=\""+url+"\" width=\""+width+"\" height=\""+height+"\" ShowStatusBar=\"true\"></embed></object>";
	}
}