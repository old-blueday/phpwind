function quote(eleid,step,intval){
	this.ele=document.getElementById(eleid);
	this.parent = this.ele.parentNode;
	this.stopScroll=false;
	this.currentTop=0;
	this.stoptime=0;
	this.step=step;
	this.intval=intval;
	this.clientHeight=this.ele.clientHeight;
	var copyNode = this.ele.cloneNode(true);
	copyNode.id='';
	this.parent.appendChild(copyNode);

	var _this=this;
	this.parent.onmouseover=function(){
		_this.stopScroll=true;
	};
	this.parent.onmouseout=function(){
		_this.stopScroll=false;
	};
	setInterval(function(){_this.scrollUp()}, 15);
	
}
quote.prototype.scrollUp=function(){
	if(this.stopScroll)
		return;
	if(this.currentTop > this.step) {
		if(this.currentTop > this.intval) {
			this.currentTop = 0;
		}
	} else {
		var preTop = this.parent.scrollTop;
		this.parent.scrollTop = preTop>this.clientHeight?(preTop+1-this.clientHeight):(preTop+1);
	}
	this.currentTop += 1;
}
