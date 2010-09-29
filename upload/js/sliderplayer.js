
function pwSliderPlayers(elementId, pauseTime, currentPageClassName) {
	try {
		if (pwSliderPlayer === undefined) pwSliderPlayer = {};
	} catch (e) {
		pwSliderPlayer = {};
	}
	var elementObj = document.getElementById(elementId);
	pwSliderPlayer[elementId] = new PWSliderPlayer(elementObj, pauseTime, currentPageClassName);

	elementObj.onmouseover = function() {
		pwSliderPlayer[elementId].pause();
	}
	elementObj.onmouseout = function() {
		pwSliderPlayer[elementId].play();
	};
	return pwSliderPlayer;
}
function PWSliderPlayer(elementObj, pauseTime, currentPageClassName) {
	this.id = elementObj.id;
	this.timer;
	this.curScreen = 0;
	this.elementObj = elementObj;
	this.pauseTime = (undefined == pauseTime) ? 3000 : pauseTime * 1000;
	this.currentPageClassName = (undefined == currentPageClassName) ? 'sel' : currentPageClassName;
	this.pics = elementObj.getElementsByTagName('div');
	this.maxScreen = this.pics.length-1;
	this.go().play();
}
PWSliderPlayer.prototype.pause = function(){
	clearInterval(this.timer);
	this.timer = null;
};
PWSliderPlayer.prototype.play = function(){
	if (!this.timer) this.timer = setInterval('pwSliderPlayer.'+this.id+'.go()', this.pauseTime);
};
PWSliderPlayer.prototype.go = function(t){
	this.curScreen = t===undefined ? this.curScreen : t;
	this.curScreen %= this.maxScreen;
	var NavStr='';
	for (i=0;i<this.maxScreen;i++) {
		if (i == this.curScreen) {
			NavStr += '<li><a href="javascript:;" target="_self" class="'+this.currentPageClassName+'">'+(i+1)+'</a></li>' ;
			this.pics[i].style.display = "" ;
		} else {
			NavStr += '<li onclick="pwSliderPlayer.'+this.id+'.go('+i+');"><a href="javascript:;" target="_self">'+(i+1)+'</a></li>' ;
			this.pics[i].style.display = "none" ;
		}
	}
	this.elementObj.getElementsByTagName('ul')[0].innerHTML = NavStr ;
	++this.curScreen;
	return this;
}
