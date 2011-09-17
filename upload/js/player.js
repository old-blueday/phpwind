function player(id,url,width,height,type) {
	if (!IsElement('p_' + id)) {
		var player = document.createElement('div');
		player.id  = 'p_' + id;
		player.style.cssText = 'display:block;';
		player.innerHTML = eval('player_' + type)(url.replace('"',''),width,height,'swf_' + id);
		setTimeout(function(){getObj(id).appendChild(player)},200);
	} else {
		if (is_ie) {
			try{document['swf_'+id].pause();}catch(e){}
		}
		var p = getObj(id);
		p.removeChild(p.lastChild);
	}
}
/*
*多媒体播放点击切换
*/
function toggleVideo(elem){
	addEvent(elem,"click",function(e){
		var e=e||window.event;
		if(e.preventDefault){
			e.preventDefault();
		}else{
			e.returnValue=false;
		}
		var id=elem.getAttribute("data-pid");
		var url=elem.getAttribute("data-url");
		var w=elem.getAttribute("data-width");
		var h=elem.getAttribute("data-height");
		var type=elem.getAttribute("data-type");
		if(!url){
			return false;
		}
		if(!elem.open){
			player('player_'+id,url,w,h,type);
			elem.open=true;
			elem.className="video_u";
			elem.innerHTML="收起";
		}else{
			elem.open=false;
			elem.className="video";
			elem.innerHTML="点击播放";
			if(getObj("p_player_"+id)){
				getObj("player_"+id).removeChild(getObj("p_player_"+id));
			}
		}
	})
	
}
function player_rm(url,width,height,id){
	if (is_ie) {
		return "<object classid=\"CLSID:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\" id=\""+id+"\" width=\""+width+"\" height=\""+height+"\"><param name=\"src\" value=\""+url+"\" /><param name=\"controls\" value=\"Imagewindow\" /><param name=\"wmode\" value=\"opaque\" /><param name=\"console\" value=\"clip1\" /><param name=\"autostart\" value=\"true\" /></object><br /><object classid=\"CLSID:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\" width=\""+width+"\" height=\"44\"><param name=\"src\" value=\""+url+"\" /><param name=\"controls\" value=\"ControlPanel,StatusBar\" /><param name=\"console\" value=\"clip1\" /><param name=\"autostart\" value=\"true\" /></object>";
	} else if (agt.indexOf('firefox')!=-1) {
		return "<object data=\""+url+"\" type=\"audio/x-pn-realaudio-plugin\" id=\""+id+"\" width=\""+width+"\" height=\""+height+"\" autostart=\"true\" console=\"clip1\" controls=\"Imagewindow\"><embed src=\""+url+"\" type=\"audio/x-pn-realaudio-plugin\" autostart=\"true\" console=\"clip1\" controls=\"Imagewindow\" width=\""+width+"\" height=\""+height+"\"></embed></object><br /><object data=\""+url+"\" type=\"audio/x-pn-realaudio-plugin\" width=\""+width+"\" height=\"44\" autostart=\"true\" console=\"clip1\" controls=\"ControlPanel,StatusBar\"><embed src=\""+url+"\" type=\"audio/x-pn-realaudio-plugin\" autostart=\"true\" console=\"clip1\" controls=\"ControlPanel,StatusBar\" width=\""+width+"\" height=\"44\"></embed></object>";
	} else if (agt.indexOf('safari')!=-1) {
		return "<object type=\"audio/x-pn-realaudio-plugin\" id=\""+id+"\" width=\""+width+"\" height=\""+height+"\"><param name=\"src\" value=\""+url+"\" /><param name=\"controls\" value=\"Imagewindow\" /><param name=\"console\" value=\"clip1\" /><param name=\"autostart\" value=\"true\" /></object><br /><object type=\"audio/x-pn-realaudio-plugin\" width=\""+width+"\" height=\"44\"><param name=\"src\" value=\""+url+"\" /><param name=\"controls\" value=\"ControlPanel,StatusBar\" /><param name=\"console\" value=\"clip1\" /><param name=\"autostart\" value=\"true\" /></object>";
	} else {
		return "<object classid=\"clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\" id=\""+id+"\" width=\""+width+"\" height=\""+height+"\"><param name=\"src\" value=\""+url+"\" /><param name=\"controls\" value=\"Imagewindow\" /><param name=\"console\" value=\"clip1\" /><param name=\"autostart\" value=\"true\" /><param name=\"wmode\" value=\"opaque\" /><embed src=\""+url+"\" type=\"audio/x-pn-realaudio-plugin\" autostart=\"true\" console=\"clip1\" controls=\"Imagewindow\" width=\""+width+"\" height=\""+height+"\"></embed></object><br /><object classid=\"clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\" width=\""+width+"\" height=\"44\"><param name=\"src\" value=\""+url+"\" /><param name=\"controls\" value=\"ControlPanel\" /><param name=\"console\" value=\"clip1\" /><param name=\"autostart\" value=\"true\" /><embed src=\""+url+"\" type=\"audio/x-pn-realaudio-plugin\" autostart=\"true\" console=\"clip1\" controls=\"ControlPanel,StatusBar\" width=\""+width+"\" height=\"44\"></embed></object>";
	}
}
function player_flash(url,width,height,id){
	if (is_ie) {
		return "<object classid='CLSID:D27CDB6E-AE6D-11cf-96B8-444553540000' id='"+id+"' width='"+width+"' height='"+height+"'><param name='src' value='"+url+"' /><param name='autostart' value='true' /><param name='loop' value='true' /><param name='allownetworking' value='internal' /><param name='allowscriptaccess' value='never' /><param name='allowfullscreen' value='true' /><param name='quality' value='high' /><param name='wmode' value='transparent'></object>";
	} else {
		return "<embed id='"+id+"' src='"+url+"' allownetworking='internal' allowscriptaccess='never' allowfullscreen='true' quality='high' wmode='transparent' width='"+width+"' height='"+height+"' type='application/x-shockwave-flash'/>";
	}
}
function player_wmv(url,width,height,id) {
	if (height<64) height = 64;
	if (is_ie) {
		return "<object classid=\"CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95\" id=\""+id+"\" width=\""+width+"\" height=\""+height+"\"><param name=\"src\" value=\""+url+"\" /><param name=\"ShowStatusBar\" value=\"true\" /><param name=\"wmode\" value=\"opaque\" /></object>";
	} else if (agt.indexOf('firefox')!=-1) {
		return "<object data=\""+url+"\" type=\"application/x-mplayer2\" id=\""+id+"\" width=\""+width+"\" height=\""+height+"\" ShowStatusBar=\"true\"><param name=\"wmode\" value=\"transparent\" /><embed type=\"application/x-mplayer2\" src=\""+url+"\" width=\""+width+"\" height=\""+height+"\" ShowStatusBar=\"true\"></embed></object>";
	} else if (agt.indexOf('safari')!=-1) {
		return "<object type=\"application/x-mplayer2\" id=\""+id+"\" width=\""+width+"\" height=\""+height+"\"><param name=\"src\" value=\""+url+"\" /><param name=\"wmode\" value=\"transparent\" /><param name=\"ShowStatusBar\" value=\"true\" /></object>";
	} else {
		return "<object classid=\"CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95\" id=\""+id+"\" width=\""+width+"\" height=\""+height+"\"><param name=\"src\" value=\""+url+"\" /><param name=\"ShowStatusBar\" value=\"true\" /><param name=\"wmode\" value=\"transparent\" /><embed type=\"application/x-mplayer2\" src=\""+url+"\" width=\""+width+"\" height=\""+height+"\" ShowStatusBar=\"true\"></embed></object>";
	}
}