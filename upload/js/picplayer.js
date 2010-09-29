~function()
{
	var Timer = null;
var CurScreen = MaxScreen = 1;
var pics=document.getElementById('pwSlidePlayer').getElementsByTagName("DIV");
if(!pics){
	pics[0].style.display="";
}
var MaxScreen=pics.length;
window.pwSlidePlayer=function (action,index) {
	clearTimeout(Timer);
	switch (action) {
		case 'pause' :
			clearTimeout(Timer);
			break;
		case 'goon' :
			clearTimeout(Timer);
			Timer = setTimeout('pwSlidePlayer();', 3000);
			break;
		case 'play' :
			CurScreen = index - 1 ;
		default :
			if (CurScreen >= MaxScreen)CurScreen = 0 ;
			for (i=0;i<MaxScreen;i++) {
				pics[i].style.display = "none" ;
			}
			if (pics[CurScreen]){
				pics[CurScreen].style.display = "block" ;
			}
			var NavStr = "" ;
			for (i=1;i<=MaxScreen;i++) {
				if (i == CurScreen+1) {
					NavStr += '<li><a href="javascript:;" target="_self" class="sel">'+i+'</a></li>' ;
				} else {
					NavStr += '<li onclick="pwSlidePlayer(\'play\','+i+');"><a href="javascript:;" target="_self">'+i+'</a></li>' ;
				}
			}
			document.getElementById("SwitchNav").innerHTML = NavStr ;
			if (MaxScreen>1) {
				CurScreen++;
				Timer = setTimeout('pwSlidePlayer();', 3000);
			}
	}
}
}
();