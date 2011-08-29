function ddSliderPlayers(elementId,handleId, pauseTime, currentClassName) {
	try {
		if (typeof ddSliderPlayer == 'undefined')
			ddSliderPlayer = {};
	} catch (e) {
		ddSliderPlayer = {};
	}
	var elementObj = document.getElementById(elementId);
	var handler = document.getElementById(handleId);
	ddSliderPlayer[elementId] = new DDSliderPlayer(elementObj, handler, pauseTime, currentClassName);

	elementObj.onmouseover = function() {
		ddSliderPlayer[elementId].pause();
	}
	elementObj.onmouseout = function() {
		ddSliderPlayer[elementId].play();
	};
	return ddSliderPlayer;
}
function DDSliderPlayer(elementObj, handler, pauseTime, currentClassName) {
	this.id = elementObj.id;
	this.timer;
	this.curScreen = 0;
	this.elementObj = elementObj;
	this.pauseTime = (undefined == pauseTime) ? 3000 : pauseTime * 1000;
	this.currentClassName = (currentClassName == undefined) ? 'sel' : currentClassName;
	this.pics = getElementsByClassName('switchItem',elementObj);
	this.handlers = getElementsByClassName('switchNavItem',handler);
	this.maxScreen = this.pics.length > this.handlers.length ? this.handlers.length : this.pics.length;
	for (i=0;i<this.handlers.length;i++) {
		this.handlers[i].setAttribute("index", i);
		var id=this.id;
		this.handlers[i].onmouseover=function(){
			var u = this.getAttribute('index');
			ddSliderPlayer[id].go(u).pause();
		}
		this.handlers[i].onmouseout=function(){
			ddSliderPlayer[id].play();
		}
	}
	this.go();
	if (pauseTime)
		this.play();
}
DDSliderPlayer.prototype.pause = function(){
	this.pauseTime && clearInterval(this.timer);
	this.timer = null;
};
DDSliderPlayer.prototype.play = function(){
	if (!this.timer&&this.pauseTime) this.timer = setInterval('ddSliderPlayer.'+this.id+'.go()', this.pauseTime);
};
DDSliderPlayer.prototype.go = function(t){
	this.curScreen = t===undefined ? this.curScreen : t;
	this.curScreen %= this.maxScreen;
	for (i=0;i<this.maxScreen;i++) {
		if (i == this.curScreen) {
			this.handlers[i].className = 'switchNavItem '+this.currentClassName;
			this.pics[i].style.display = '';
		} else {
			this.handlers[i].className = 'switchNavItem';
			this.pics[i].style.display = "none" ;
		}
	}
	++this.curScreen;
	return this;
}
function ddHSlider(offX,eleid) {
	document.getElementById(eleid).scrollLeft+=offX;
}