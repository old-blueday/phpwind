// JavaScript Document
Breeze.namespace('editor.ubb', function(B){
var IMGPATH = 'images/post/smile/',
	UBBCommands = {
		Bold: 'b',
		Italic: 'i',
		Underline: 'u',
		Strikethrough: 'strike',
		JustifyLeft: 'align=left',
		JustifyRight: 'align=right',
		JustifyCenter: 'align=center',
		Image: 'img',
		Forecolor: 'color',
		Backcolor: 'backcolor',
		hilitecolor: 'backcolor',
		InsertOrderedList: 'list=1 li',
		InsertUnorderedList: 'list li',
		Indent: 'blockquote',
		Outdent: 'blockquote',
		Inserthorizontalrule: 'hr',
		blockquote: 'quote',
		code: 'code',
		Createlink: 'link',
		Unlink: 'link',
		Inserttable: 'table',
		FontName: 'font',
		FontSize: 'size',
		sell: 'sell',
		PgFormat: 'paragraph',
		removeformat: '',
		undo:'undo',
		redo:'redo'
	};
//TODO:假设类传过来了。
var Editor = B.editor;

function removeFormat(textarea){
	var reg = /\[(\/?)(b|u|i|strike)\]/ig;
	if (document.selection) {
		var rng = document.selection.createRange(),
			txt = rng.text;
		rng.text = txt.replace(reg, '');
		rng.moveEnd('character', rng.text.length-txt.length);
		return rng;
	} else if (typeof textarea.selectionStart != 'undefined') {
		var prepos = textarea.selectionStart, endpos = textarea.selectionEnd,
			val = textarea.value,
			frag = textarea.value.substr(prepos, endpos-prepos),
			newfrag = frag.replace(reg, '');
		textarea.value = val.substr(0,prepos) + newfrag + val.substr(endpos);
		return [prepos, prepos+newfrag.length];
	}

}
function UBBMode(editor){
	var textarea = this.textarea = this.container = this.editContainer = editor.textarea;
	this.editor  = editor;
	
	this.init = function() {
		var editor = this.editor;
		editor.codeModeBtn && (editor.codeModeBtn.className = 'B_onCodeMode');
		B.addEvent(this.textarea, 'mousedown', function(){
			editor.clearRng();
		});
		B.addEvent(this.textarea, 'keyup', function(){
			editor.clearRng();
		});
		editor.setAutoSave(this.textarea);
		
	}
	this.command = function(command){
		if(['undo','redo'].indexOf(command)>-1){
			document.execCommand(command, false, null);
			return;
		}
		if(command == 'PgFormat'){
			var str = textarea.value,
				reg = /^\[paragraph\]/;
			if(reg.test(str)){
				textarea.value =  str.replace(reg, '');
			} else {
				textarea.value = '[paragraph]'+str;
			}
			return;
		}
		if(command == 'RemoveFormat'){
			this._rng = removeFormat(textarea);
			return;
		}
		
		command = UBBCommands[command];
		var commands = command.split(' '),
			pretag = '[' + command.replace(/\s+/ig, '][') + ']',
			endtag = '';
		if (command != 'hr') {
			commands.forEach(function(n){
				endtag = '[/' + n.replace(/=.*$/ig, '') + ']' + endtag;
			});
		}
		if (document.selection) {
			var rng = this.getRng(),
				l = rng.text.length;
			/*if (rng.parentElement() != textarea){
				textarea.select();
				rng = document.selection.createRange();
				rng.collapse(false);
			}*/
			rng.text = pretag + rng.text + endtag;
			rng.moveStart('character', -endtag.length-l);
			rng.moveEnd('character', -endtag.length);
			//if(rng.text){
				this._rng = rng;
			//}
		} else if (typeof textarea.selectionStart != 'undefined') {
			var prepos = textarea.selectionStart, endpos = textarea.selectionEnd;
			var val = textarea.value;
			textarea.value = val.substr(0,prepos) + pretag + val.substr(prepos, endpos-prepos)+ endtag + val.substr(endpos);
			this._rng = [prepos + 2 + command.length, endpos + 2 +command.length];
		} else {
			textarea.value += pretag + endtag;
			var i = prepos + 2 + command.length;
			this._rng = [i, i];
		}
		this.restoreRng();
	}
	this.valueCommand = function(command,value) {
		command = UBBCommands[command];
		var pretag = '[' + command + '=' + value + ']',
			endtag = '[/'+command+']';
		if (document.selection) {
			var rng = this.getRng(),
				l = rng.text.length;
			/*
			if (rng.parentElement() != textarea){
				textarea.select();
				rng = document.selection.createRange();
				rng.collapse(false);
			}
			*/
			rng.text = pretag + rng.text + endtag;
			rng.moveStart('character', -endtag.length-l);
			rng.moveEnd('character', -endtag.length);
			//if(rng.text){
				this._rng = rng;
			//}
		} else if (typeof textarea.selectionStart != 'undefined') {
			var prepos = textarea.selectionStart, endpos = textarea.selectionEnd;
			var val = textarea.value;
			textarea.value = val.substr(0,prepos) + '[' + command + '=' + value + ']' + val.substr(prepos, endpos-prepos)+ '[/' + command + ']' + val.substr(endpos);
			this._rng = [prepos + 3 + command.length + value.length, endpos + 3 +command.length + value.length];// startpos + text.length;
		} else {
			textarea.value += '[' + command + '=' + value + '][/' + command + ']';
			var i = prepos + 2 + command.length;
			this._rng = [i,i];
			//textarea.setSelectionRange(i, i);
		}
		this.restoreRng();
	}
	this.wrapCommand = function(command){
		this.command(command);
	}
	this.insertCommand = function(command){
		this.valueCommand(command, value);
	}
	this.init();
}

UBBMode.prototype = {
	focus: function() {
		this.textarea.focus();
	},
	//储存选区
	saveRng: function(sel){
		var textarea = this.textarea;
		if (document.selection) {
			var rng = document.selection.createRange();
			if (rng.parentElement() != this.textarea){
				textarea.select();
				rng = document.selection.createRange();
				rng.collapse(false);
				rng.select();
			}
			this._rng = document.selection.createRange();
		}else{
			this._rng = [this.textarea.selectionStart, this.textarea.selectionEnd];
		}
	},
	clearRng: function() {
		if(this._rng){
			this._rng = null;
		}
	},
	//恢复选区
	restoreRng: function(){
		if (!this._rng) return;
		if (B.UA.ie) {
			this._rng.select();
		} else if (typeof this.textarea.selectionStart != 'undefined') {
			this.focus();
			this.textarea.setSelectionRange(this._rng[0], this._rng[1]);
		}
	},
	//获得选区
	getRng: function(){
		if (this._rng){
			return this._rng;
		}
		this.focus();
		if (document.selection) {
			return document.selection.createRange();
		} else {
			return [this.textarea.selectionStart, this.textarea.selectionEnd];
		}
		//return this._rng;
	},
	getSel: function(){
		//return text.selection || window.getSelection();
	},
	//获取HTML
	getHTML: function(){
		return this.textarea.value;
	},
	setHTML: function(sHtml){
		this.textarea.value = sHtml;
	},
	getSelText: function(){
		var textarea = this.textarea;
		if(B.UA.ie){
			return this.getRng().text;
		}else{
			return textarea.value.substr(textarea.selectionStart, textarea.selectionEnd - textarea.selectionStart);
		}
	},
	isSel: function() {
		return B.UA.ie ? !!this.getRng().text : ((this.textarea.selectionEnd - this.textarea.selectionStart) > 0);
	},

	pasteHTML: function(str){
		var textarea = this.textarea;
		str = html2ubb(str);
		if (document.selection) {
			var rng = this.getRng();
			rng.text = str;
			rng.moveStart('character', 0);
			rng.moveEnd('character', 0);
			this._rng = rng;
		} else if (typeof textarea.selectionStart != 'undefined') {
			var rng = this.getRng(), prepos = rng[0], endpos = rng[1],val = textarea.value;
			textarea.value = val.substr(0,prepos) + str + val.substr(endpos);
			this._rng = [prepos + str.length, prepos + str.length];
		} else {
			var i = textarea.value.length;
			textarea.value += str;
		}
		this.restoreRng();
	}
};

//添加切换;
Editor.prototype.plugins.push(function(){
	var tar = B.$('.B_tar', this.area);
	if (tar) {
		var textMode = B.createElement('<span class="B_codeMode">代码模式</span>');
		tar.appendChild(textMode);
		B.addEvent(textMode, 'click', this.ubbtoggle.bind(this));
		B.addEvent(textMode, 'selectstart', function(){return false;});
		this.codeModeBtn = textMode;
	}
	this.isUBB = true;
	this.modes.UBB = UBBMode;
});
Editor.prototype.ubbtoggle = function(){
	this.currentMode = this.currentMode == 'default' ? 'UBB' : 'default';
	this.init();
	if (this.currentMode == 'default') {
		this.textarea.style.display = 'none';
		this.div.style.display = '';
		this.codeModeBtn&&(this.codeModeBtn.className = 'B_codeMode');
		this.setHTML(this.getHtmlFromUBB());
		this.modes['default'].setEditable();
	} else {
		this.textarea.style.display = '';
		this.div.style.display = 'none';
		this.codeModeBtn&&(this.codeModeBtn.className = 'B_onCodeMode');
		this.setHTML(this.getUBBFromHtml());
	}
	this.saveMode();
	B.$$query('.active', this.area)(B.removeClass, 'active')();
}
Editor.prototype.getHtmlFromUBB = function(){
	return this.isUBB ? ubb2html(this.textarea.value) : txt2html(this.textarea.value);
}
Editor.prototype.getUBBFromHtml = function(){
	return this.isUBB ? html2ubb(this.modes['default'].getHTML()) : html2txt(this.modes['default'].getHTML());
}
Editor.prototype.getUBB = function(){
	return this.currentMode == 'default' ? html2ubb(this.getHTML()) : this.textarea.value
}
Editor.prototype.getSelHtml = function(checkbox){
	var txt = this.getSelText();
	return p2br(this.currentMode == 'default' ? txt : ubb2html(txt));
}
Editor.prototype.getSavedHTML = function(){
	return ubb2html(this.textarea.value);
}
Editor.prototype.setHtmlMode = function(checkbox){
	this.isUBB = !checkbox.checked;
}
Editor.prototype.ubb2html=ubb2html;
function ubb2html(sUBB) {
	var para = false;
	if (sUBB.indexOf('[paragraph]') > -1){
		sUBB = sUBB.replace('[paragraph]', '');
		para = true;
	}
	var i,sHtml=String(sUBB),arrcode=new Array(),cnum=0;
	sHtml=sHtml.replace(/&/ig, '&amp;');
	sHtml=sHtml.replace(/[<>]/g,function(c){return {'<':'&lt;','>':'&gt;'}[c];});
	sHtml=sHtml.replace(/\[code\s*(?:=\s*([^\]]+?))?\]([\s\S]*?)\[\/code\]/ig,function(all,t,c){//code特殊处理
		cnum++;
		arrcode[cnum]= '<ol class="B_code"><li>' + c.replace(/\r?\n/ig, '</li><li>') + '</li></ol>';
		return "[\tubbcodeplace_"+cnum+"\t]";
	});
	sHtml=sHtml.replace(/\[(b|u|i|strike)\]\s*?\[\/(b|u|i|strike)\]/ig, '');
	if(B.UA.gecko){//firefox  font-weight; 0526新增color
		sHtml=sHtml.replace(/\[(\/?)(b|u|i|strike)\]/ig, function(all, pre, tag){
			if (pre) return '</span>';
			var str = '<span style="';
			switch(tag){
				case 'b':str += 'font-weight: bold;';break;
				case 'u':str += 'text-decoration: underline;';break;
				case 'i':str += 'font-style: italic;';break;
				case 'strike':str += 'text-decoration: line-through;';
			}
			str += '">';
			return str;
		});
		sHtml=sHtml.replace(/\[color\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\]/ig,'<span style="color:$1">');
		sHtml=sHtml.replace(/\[\/color\]/ig,'</span>');
	} else {//other  strong em u del 
		sHtml=sHtml.replace(/\[(\/?)(b|u|i|strike)\]/ig, '<$1$2>');
	}
	sHtml=sHtml.replace(/\[(\/?)(sup|sub)\]/ig,'<$1$2>');
	sHtml=sHtml.replace(/\[color\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\]/ig,'<font color="$1">');
	sHtml=sHtml.replace(/\[size\s*=\s*(\d+?)\s*\]/ig,'<font size="$1">');
	sHtml=sHtml.replace(/\[font\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\]/ig,'<font face="$1">');
	sHtml=sHtml.replace(/\[\/(color|size|font)\]/ig,'</font>');
	sHtml=sHtml.replace(/\[backcolor\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\]/ig,'<span style="background-color:$1;">');
	sHtml=sHtml.replace(/\[\/backcolor\]/ig,'</span>');
	for(i=0;i<3;i++)sHtml=sHtml.replace(/\[align\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\](((?!\[align(?:\s+[^\]]+)?\])[\s\S])*?)\[\/align\]/ig,'<div align="$1">$2</div>');
	sHtml=sHtml.replace(/\[img\]\s*(((?!")[\s\S])+?)(?:"[\s\S]*?)?\s*\[\/img\]/ig,'<img src="$1" alt="" />');
	sHtml=sHtml.replace(/\[s:(\d{1,3})]/ig, function(a,b){if(!face[b]){b=faces[defaultface][0]||'';}return '<img emotion="'+b+'" title="'+face[b][1]+'" src="' + IMGPATH + face[b][0] + '" />';});
	sHtml=sHtml.replace(/\[img\s*=([^,\]]*)(?:\s*,\s*(\d*%?)\s*,\s*(\d*%?)\s*)?(?:,?\s*(\w+))?\s*\]\s*(((?!")[\s\S])+?)(?:"[\s\S]*)?\s*\[\/img\]/ig,function(all,alt,p1,p2,p3,src){
		var str='<img src="'+src+'" alt="'+alt+'"',a=p3?p3:(!isNum(p1)?p1:'');
		if(isNum(p1))str+=' width="'+p1+'"';
		if(isNum(p2))str+=' height="'+p2+'"'
		if(a)str+=' align="'+a+'"';
		str+=' />';
		return str;
	});
	sHtml=sHtml.replace(/\[hr]/ig, " <hr>");
	//sHtml=sHtml.replace(/\[s:(\d{1,3})]/ig, function(a,b){return '<img emotion="'+b+'" title="'+face[b][1]+'" src="' + IMGPATH + face[b][0] + '" />';});
	sHtml=sHtml.replace(/\[url\]\s*(((?!")[\s\S])*?)(?:"[\s\S]*?)?\s*\[\/url\]/ig,'<a href="$1">$1</a>');
	sHtml=sHtml.replace(/\[url\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\]\s*([\s\S]*?)\s*\[\/url\]/ig,'<a href="$1">$2</a>');
	sHtml=sHtml.replace(/\[email\]\s*(((?!")[\s\S])+?)(?:"[\s\S]*?)?\s*\[\/email\]/ig,'<a href="mailto:$1">$1</a>');
	sHtml=sHtml.replace(/\[email\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\]\s*([\s\S]+?)\s*\[\/email\]/ig,'<a href="mailto:$1">$2</a>');
	sHtml=sHtml.replace(/\[quote\]([\s\S]*?)\[\/quote\]/ig,'<blockquote class="blockquote">$1</blockquote>');
	sHtml=sHtml.replace(/\[blockquote\]([\s\S]*?)\[\/blockquote\]/ig,'<blockquote>$1</blockquote>');
	sHtml=sHtml.replace(/\[flash\s*(?:=\s*(\d+)\s*,\s*(\d+)\s*)?\]\s*(((?!")[\s\S])+?)(?:"[\s\S]*?)?\s*\[\/flash\]/ig,function(all,w,h,url){
		if(!w)w=480;if(!h)h=400;
		return '<embed type="application/x-shockwave-flash" src="'+url+'" wmode="opaque" quality="high" bgcolor="#ffffff" menu="false" play="true" loop="true" width="'+w+'" height="'+h+'"/>';
	});
	sHtml=sHtml.replace(/\[media\s*(?:=\s*(\d+)\s*,\s*(\d+)\s*(?:,\s*(\d+)\s*)?)?\]\s*(((?!")[\s\S])+?)(?:"[\s\S]*?)?\s*\[\/media\]/ig,function(all,w,h,play,url){
		if(!w)w=480;if(!h)h=400;
		return '<embed type="application/x-mplayer2" src="'+url+'" enablecontextmenu="false" autostart="'+(play=='1'?'true':'false')+'" width="'+w+'" height="'+h+'"/>';
	});
	sHtml=sHtml.replace(/\[table\s*(?:=\s*(\d{1,4}%?)\s*(?:,\s*([^\]"]+){1,3}(?:"[^\]]*?)?)?)?\s*\]/ig,function(all,w,o){
		var str = '<table', b, c, s;
		if (o){
			o=o.split(',');
			b=o[0],c=o[1],s=o[2];
		}
		str+=' width="'+(w?w:'100%')+'"';
		if(b)str+=' bgcolor="'+b+'"';
		if(c)str+=' bordercolor="'+c+'"';
		str+=' border="'+(s?s:1)+'"';
		return str+'>';
	});
	sHtml=sHtml.replace(/\[tr\s*(?:=\s*([^\]"]+?)(?:"[^\]]*?)?)?\s*\]/ig,function(all,bg){
		return '<tr'+(bg?' bgcolor="'+bg+'"':'')+'>';
	});
	sHtml=sHtml.replace(/\[td\s*(?:=\s*(\d{1,2})\s*,\s*(\d{1,2})\s*(?:,\s*(\d{1,4}%?))?)?\s*\]/ig,function(all,col,row,w){
		return '<td'+(col>1?' colspan="'+col+'"':'')+(row>1?' rowspan="'+row+'"':'')+(w?' width="'+w+'"':'')+'>';
	});
	sHtml=sHtml.replace(/\[\/(table|tr|td)\]/ig,'</$1>');
	sHtml=sHtml.replace(/\[list\s*(?:=\s*([^\]"]+?)(?:"[^\]]*?)?)?\s*\]?([\s\S]*?)\[\/list\]/ig,function(all,type, context){
		var tag= type ? 'ol' : 'ul';
		var str = '<' + tag + '>'
			+ context.replace(/\[li\]\[\/li\]/, '<li></li>').replace(/\[li\]((?:(?!\[\/li\]|\[\/list\]|\[list\s*(?:=[^\]]+)?\])[\s\S])+)\[\/li\]/ig,'<li>$1</li>')
			+ '</'+tag+'>';
		return str;
	});

	sHtml=sHtml.replace(/<(\w+)(\s+[^>]*)?>([\s\S]+?)<\/\1>/ig, function(all,tag,attr,text){
		return '<' + tag + (attr?attr:'') + '>' + text.replace(/\r?\n/g, '<br />') + '</' + tag + '>';
	});
	var style = para ? ' style="text-indent: 2em"' : '';
	sHtml='<div'+style+'>' + sHtml.replace(/\r?\n/g, '</div><div'+style+'>') +'</div>';
	sHtml=sHtml.replace(/<div>\s*<\/div>/ig,'<div'+style+'>&nbsp;</div>');

	for(i=1;i<=cnum;i++)sHtml=sHtml.replace("[\tubbcodeplace_"+i+"\t]", arrcode[i]);

	sHtml=sHtml.replace(/(^|<\/?\w+(?:\s+[^>]*?)?>)([^<$]+)/ig, function(all,tag,text){
		return tag+text.replace(/[\t ]/g,function(c){return {'\t':'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',' ':'&nbsp;'}[c];});
	});
	sHtml = ubb2attach(sHtml);
	function isNum(s){if(s!=null&&s!='')return !isNaN(s);else return false;}
	return sHtml;
}
function txt2html(sHtml) {
	sHtml=sHtml.replace(/\[code\s*(?:=\s*([^\]]+?))?\]([\s\S]*?)\[\/code\]/ig,function(all,t,c){//code特殊处理
		all=all.replace(/&/ig, '&amp;');
		all=all.replace(/[<>]/g,function(s){return {'<':'&lt;','>':'&gt;'}[s];});
		return all;
	});
	sHtml=sHtml.replace(/\[s:(\d{1,3})]/ig, function(a,b){if(!face[b]){b=faces[defaultface][0]||'';}return '<img emotion="'+b+'" title="'+face[b][1]+'" src="' + IMGPATH + face[b][0] + '" />';});
	sHtml = ubb2attach(sHtml);
	return sHtml;
}
function html2txt(sHtml) {
	sHtml=sHtml.replace(/\[code\s*(?:=\s*([^\]]+?))?\]([\s\S]*?)\[\/code\]/ig,function(all,t,c){//code特殊处理
		all=all.replace(/&lt;/ig, '<');
		all=all.replace(/&gt;/ig, '>');
		all=all.replace(/&amp;/ig, '&');
		return all;
	});
	sHtml=sHtml.replace(/<img((\s+\w+\s*=\s*(["'])?.*?\3)*)\s*\/?>/ig,function(all,attr){
		var emot=attr.match(/\s+emotion\s*=\s*(["']?)\s*(.+?)\s*\1(\s|$)/i);
		if(emot)return '[s:'+emot[2]+']';
		var attach=attr.match(/\s+attachment\s*=\s*(["']?)\s*(.+?)\s*\1(\s|$)/i);
		if(attach)return '[attachment='+attach[2]+']';
		return all;
	});
	return sHtml;
}
Editor.prototype.html2ubb=html2ubb;
function html2ubb(sHtml) {
	var formatColor = B.formatColor;
	var mapSize1=[10, 12, 16, 19, 24, 32, 48];
	var mapSize2=['x-small', 'small', 'medium', 'large', 'x-large', 'xx-large', '-webkit-xxx-large'];
	var regSrc=/\s+src\s*=\s*(["']?)\s*(.+?)\s*\1(\s|$)/i,regWidth=/\s+width\s*=\s*(["']?)\s*(\d+(?:\.\d+)?%?)\s*\1(\s|$)/i,regHeight=/\s+height\s*=\s*(["']?)\s*(\d+(?:\.\d+)?%?)\s*\1(\s|$)/i,regBg=/(?:background|background-color|bgcolor)\s*[:=]\s*(["']?)\s*((rgb\s*\(\s*\d{1,3}%?,\s*\d{1,3}%?\s*,\s*\d{1,3}%?\s*\))|(#[0-9a-f]{3,6})|((?!initial)[a-z]{1,20}))\s*\1/i;
	var regBc=/(?:border-color|bordercolor)\s*[:=]\s*(["']?)\s*((rgb\s*\(\s*\d{1,3}%?,\s*\d{1,3}%?\s*,\s*\d{1,3}%?\s*\))|(#[0-9a-f]{3,6})|([a-z]{1,20}))\s*\1/i;
	var regBw=/\s+border\s*=\s*(["']?)\s*(\d+(?:\.\d+)?)\s*\1(\s|$)/i;
	var i,sUBB=String(sHtml),arrcode=new Array(),cnum=0,para=false;
	if (sUBB.match(/text-indent\:[\s]?2em/i)) para = true;

	sUBB=sUBB.replace(/\s*\r?\n\s*/g,'');
	sUBB=sUBB.replace(/<(script|style)(\s+[^>]*?)?>[\s\S]*?<\/\1>/ig, '');
	sUBB=sUBB.replace(/<!--[\s\S]*?-->/ig,'');
	
	sUBB=sUBB.replace(/\[code\s*(=\s*([^\]]+?))?\]([\s\S]*?)\[\/code\]/ig,function(all,t,c){//code特殊处理
		cnum++;arrcode[cnum]=all;
		return "[\tubbcodeplace_"+cnum+"\t]";
	});
	sUBB=sUBB.replace(/<(\/?)(b|u|i|strike)(\s+[^>]*?)?>/ig,'[$1$2]');
	sUBB=sUBB.replace(/<(\/?)strong(\s+[^>]*?)?>/ig,'[$1b]');
	sUBB=sUBB.replace(/<(\/?)em(\s+[^>]*?)?>/ig,'[$1i]');
	sUBB=sUBB.replace(/<(\/?)(s|del)(\s+[^>]*?)?>/ig,'[$1strike]');
	sUBB=sUBB.replace(/<(\/?)(sup|sub)(\s+[^>]*?)?>/ig,'[$1$2]');
	sUBB=sUBB.replace(/<hr[^>]*?\/?>/ig,'[hr]');

	for(i=0;i<11;i++)sUBB=sUBB.replace(/<(span)(?:\s+[^>]*?)?\s+style\s*=\s*"((?:[^"]*?;)*\s*(?:font-weight|text-decoration|font-style|font-family|font-size|color|background|background-color)\s*:[^"]*)"(?: [^>]+)?>(((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S])*?<\/\1>)*?<\/\1>)*?)<\/\1>/ig,function(all,tag,style,content){
		var bold=style.match(/(?:^|;)\s*font-weight\s*:\s*bold/i),
			underline=style.match(/(?:^|;)\s*text-decoration\s*:[^;]*underline/i),
			strike=style.match(/(?:^|;)\s*text-decoration\s*:[^;]*line-through/i)
			italic=style.match(/(?:^|;)\s*font-style\s*:\s*italic/i),
			fontface=style.match(/(?:^|;)\s*font-family\s*:\s*\'?([^;'&]+)\'?/i),
			size=style.match(/(?:^|;)\s*font-size\s*:\s*\'?([^;']+)\'?/i),
			color=style.match(/(?:^|;)\s*color\s*:\s*([^;]+)/i),
			back=style.match(/(?:^|;)\s*(?:background|background-color)\s*:\s*(?!transparent)([^;]+)/i),
			str=content;
		if(fontface)str='[font='+fontface[1]+']'+str+'[/font]';
		if(italic)str='[i]'+str+'[/i]';
		if(strike)str='[strike]'+str+'[/strike]';
		if(underline)str='[u]'+str+'[/u]';
		if(bold)str='[b]'+str+'[/b]';
		if(color)str='[color='+formatColor(color[1])+']'+str+'[/color]';
		if(back)str='[backcolor='+formatColor(back[1])+']'+str+'[/backcolor]';
		if(size){
			if (size[1].toLowerCase().indexOf('px')>-1) {
				size = mapSize1.indexOf( parseInt(size[1]) ) + 1;
			}else if(size[1].toLowerCase().indexOf('pt')>-1){
				size = Math.ceil(parseInt(size[1])/10);
			}else if (mapSize2.indexOf(size[1]) > -1){
				size = mapSize2.indexOf(size[1]) + 1;
			}
			if(size)str='[size='+size+']'+str+'[/size]';
		}
		return str;
	});
	if(B.UA.webkit!=undefined){
		sUBB=sUBB.replace(/<div>\s*<br\s*\/>\s*<\/div>/ig,'\r\n');
	}
	for(i=0;i<3;i++)sUBB=sUBB.replace(/<(div|p)(?:\s+[^>]*?)?[\s"';]\s*(?:text-)?align\s*[=:]\s*(["']?)\s*(left|center|right)\s*\2[^>]*>(((?!<\1(\s+[^>]*?)?>)[\s\S])+?)<\/\1>/ig,'[align=$3]$4[/align]');
	for(i=0;i<3;i++)sUBB=sUBB.replace(/<(center)(?:\s+[^>]*?)?>(((?!<\1(\s+[^>]*?)?>)[\s\S])*?)<\/\1>/ig,'[align=center]$2[/align]');
	for(i=0;i<3;i++)sUBB=sUBB.replace(/<(p|div)(?:\s+[^>]*?)?\s+style\s*=\s*"((?:[^"]*?;)*\s*color\s*:[^"]*)"(?: [^>]+)?>(((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S])*?<\/\1>)*?<\/\1>)*?)<\/\1>/ig,function(all,tag,style,content){
		var color=style.match(/(?:^|;)\s*color\s*:\s*([^;]+)/i),
			str;
		if(color)str='[color='+formatColor(color[1])+']'+content+'[/color]\r\n';
		return str;
	});
	sUBB=sUBB.replace(/<a(?:\s+[^>]*?)?\s+href=(["'])\s*(.+?)\s*\1[^>]*>\s*([\s\S]*?)\s*<\/a>/ig,function(all,q,url,text){
		if(!(url&&text))return '';
		var tag='url',str;
		if(url.match(/^mailto:/i)){
			tag='email';
			url=url.replace(/mailto:(.+?)/i,'$1');
		}
		str='['+tag;
		if(url!=text)str+='='+url;
		return str+']'+text+'[/'+tag+']';
	});
	//隐藏功能
	sUBB=sUBB.replace(/\[url\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\]\[post\]\s*([\s\S]*?)\s*\[\/post\]\[\/url\]/ig,'[post][url=$1]$2[/url][/post]');
	sUBB=sUBB.replace(/<img((\s+\w+\s*=\s*(["'])?.*?\3)*)\s*\/?>/ig,function(all,attr){
		//ff6 fixbug
		var src=attr.match(/\s+src\s*=\s*(["']?)\s*(.+?)\s*\1(\s|$)/i);
		if(src[2]&&src[2].indexOf("chrome://livemargins")>-1){
			return '';
		}
		var emot=attr.match(/\s+emotion\s*=\s*(["']?)\s*(.+?)\s*\1(\s|$)/i);
		if(emot)return '[s:'+emot[2]+']';
		var attach=attr.match(/\s+attachment\s*=\s*(["']?)\s*(.+?)\s*\1(\s|$)/i);
		if(attach)return '[attachment='+attach[2]+']';
		var url=attr.match(regSrc);
		if(!url)return '';
		return '[img]'+url[2]+'[/img]';
	});
	sUBB=sUBB.replace(/<blockquote\s+[^>]*?class=\"blockquote\"[^>]*?>[\n]*?([\s\S]+?)[\n]*?<\/blockquote>/ig, function(all,txt){
		return '[quote]' + p2br(txt) + '[/quote]';
	});
	sUBB=sUBB.replace(/<blockquote(?:\s+[^>]*?)?>[\n]*?([\s\S]+?)[\n]*?<\/blockquote>/ig,'[blockquote]$1[/blockquote]');
	//sUBB=sUBB.replace(/<pre(?:\s+[^>]*?)?>([\s\S]+?)<\/pre>/ig,'[code]$1[/code]');
	sUBB=sUBB.replace(/<embed((?:\s+[^>]*?)?(?:\s+type\s*=\s*"\s*application\/x-shockwave-flash\s*"|\s+classid\s*=\s*"\s*clsid:d27cdb6e-ae6d-11cf-96b8-4445535400000\s*")[^>]*?)\/>/ig,function(all,attr){
		var url=attr.match(regSrc),w=attr.match(regWidth),h=attr.match(regHeight),str='[flash';
		if(!url)return '';
		if(w&&h)str+='='+w[2]+','+h[2];
		str+=']'+url[2];
		return str+'[/flash]';
	});
	sUBB=sUBB.replace(/<embed((?:\s+[^>]*?)?(?:\s+type\s*=\s*"\s*application\/x-mplayer2\s*"|\s+classid\s*=\s*"\s*clsid:6bf52a52-394a-11d3-b153-00c04f79faa6\s*")[^>]*?)\/>/ig,function(all,attr){
		var url=attr.match(regSrc),w=attr.match(regWidth),h=attr.match(regHeight),p=attr.match(/\s+autostart\s*=\s*(["']?)\s*(.+?)\s*\1(\s|$)/i),str='[media',auto='0';
		if(!url)return '';
		if(p)if(p[2]=='true')auto='1';
		if(w&&h)str+='='+w[2]+','+h[2]+','+auto;
		str+=']'+url[2];
		return str+'[/media]';
	});
	sUBB=sUBB.replace(/<table(\s+[^>]*?)?>/ig,function(all,attr){
		var str='[table';
		if (attr) {
			var w=attr.match(regWidth),b=attr.match(regBg),c=attr.match(regBc),s=attr.match(regBw);
			if(w){
				str+='='+w[2];
				if (s && s[2]=='1') s=null;
				if (b||c||s) {
					str+=','+(b?B.formatColor(b[2]):'#ffffff');
					str+=','+(c?(B.formatColor(c[2])=='initial'?'#dddddd':B.formatColor(c[2])):'');
					str+=','+(s?s[2]:1);
				}
			}
		}
		return str+']';
	});
	sUBB=sUBB.replace(/<tr(\s+[^>]*?)?>/ig,function(all,attr){
		var str='[tr';
		return str+']';
	});
	sUBB=sUBB.replace(/<(?:th|td)(\s+[^>]*?)?>/ig,function(all,attr){
		var str='[td';
		if(attr){
			var col=attr.match(/\s+colspan\s*=\s*(["']?)\s*(\d+)\s*\1(\s|$)/i),row=attr.match(/\s+rowspan\s*=\s*(["']?)\s*(\d+)\s*\1(\s|$)/i),w=attr.match(regWidth);
			col=col?col[2]:1;
			row=row?row[2]:1;
			if(col>1||row>1||w)str+='='+col+','+row;
			if(w)str+=','+w[2];
		}
		return str+']';
	});
	sUBB=sUBB.replace(/<\/(table|tr)>/ig,'[/$1]');
	sUBB=sUBB.replace(/<\/(th|td)>/ig,'[/td]');
	sUBB=sUBB.replace(/<ul(\s+[^>]*?)?>([\s\S]*?)<\/ul>/ig,function(all, attr, context){
		var t, tag;
		if( attr && attr.match(/align="?([^\s"]*?)"?/ig) ){
			tag = /align="?([^\s"]*?)"?/ig.exec(attr)[1];
		}else if( attr && attr.match(/text-align\s*:\s*([^\s;]*?);/ig) ) {
			tag = /text-align\s*:\s*([^\s;]*?);/ig.exec(attr)[1];
		}
		if(tag){
			return '[align='+tag+'][list]' + context.replace(/<li(\s+[^>]*?)?>([\s\S]*?)[\n]*?<\/li>/ig, "[li]$2[/li]") + '[/list][/align]';
		}
		if(attr)t=attr.match(/\s+type\s*=\s*(["']?)\s*(.+?)\s*\1(\s|$)/i);
		return '[list'+(t?'='+t[2]:'')+']' + context.replace(/<li(\s+[^>]*?)?>([\s\S]*?)[\n]*?<\/li>/ig, "[li]$2[/li]") + '[/list]';
	});
	sUBB=sUBB.replace(/<ol(\s+[^>]*?)?>([\s\S]*?)<\/ol>/ig, function(all, attr, context){
		var tag;
		if( attr && attr.match(/align="?([^\s"]*?)"?/ig) ){
			tag = /align="?([^\s"]*?)"?/ig.exec(attr)[1];
		}else if(  attr && attr.match(/text-align\s*:\s*([^\s;]*?);/ig) ) {
			tag = /text-align\s*:\s*([^\s;]*?);/ig.exec(attr)[1];
		}
		if(tag){
			return '[align='+tag+'][list=1]' + context.replace(/<li(\s+[^>]*?)?>([\s\S]*?)[\n]*?<\/li>/ig, '[li]$2[/li]') + '[/list][/align]';
		}else if( attr && attr.match(/class="?B_code"?/) ){
			return '[code]' + B.trim(context.replace(/<li(\s+[^>]*?)?>([\s\S]*?)[\n]*?<\/li>/ig, "$2\n")) + '[/code]';
		}else{
			return '[list=1]' + context.replace(/<li(\s+[^>]*?)?>([\s\S]*?)[\n]*?<\/li>/ig, '[li]$2[/li]') + '[/list]';
		}
	});
	
	sUBB=sUBB.replace(/<h([1-6])(\s+[^>]*?)?>/ig,function(all,n){return '\n\n[size='+(7-n)+'][b]'});
	sUBB=sUBB.replace(/<\/h[1-6]>/ig,'[/b][/size]\n\n');
	sUBB=sUBB.replace(/<address(\s+[^>]*?)?>/ig,'\n[i]');
	sUBB=sUBB.replace(/<\/address>/ig,'[i]\n');
	for(i=1;i<=cnum;i++)sUBB=sUBB.replace("[\tubbcodeplace_"+i+"\t]", arrcode[i]);
	for(i=0;i<3;i++)sUBB=sUBB.replace(/([\s\S])<(div|p)(?:\s+[^>]*?)?>(((?!<\2(\s+[^>]*?)?>)[\s\S]|<\2(\s+[^>]*?)?>((?!<\2(\s+[^>]*?)?>)[\s\S]|<\2(\s+[^>]*?)?>((?!<\2(\s+[^>]*?)?>)[\s\S])*?<\/\2>)*?<\/\2>)*?)<\/\2>/ig,"$1\n$3");
	sUBB=sUBB.replace(/<br[^\/>]*?\/?>/ig,"\n");/*if(B.UA.gecko>0)*///FF下使用
	if(para)sUBB = '[paragraph]'+sUBB;
	//sUBB=sUBB.replace(/((\s|&nbsp;)*\r?\n){3,}/g,"\n\n");//限制最多2次换行
	//sUBB=sUBB.replace(/^((\s|&nbsp;)*\r?\n)+/g,'');//清除开头换行
	//sUBB=sUBB.replace(/((\s|&nbsp;)*\r?\n)+$/g,'');//清除结尾换行

	sUBB=sUBB.replace(/<[^<>]+?>/g,'');//删除所有HTML标签
	sUBB=sUBB.replace(/&lt;/ig, '<');
	sUBB=sUBB.replace(/&gt;/ig, '>');
	sUBB=sUBB.replace(/&nbsp;/ig, ' ');
	sUBB=sUBB.replace(/&amp;/ig, '&');
	return sUBB;
}
function ubb2attach(str){
	var mixObj, list = (typeof attachConfig != 'undefined' && typeof attachConfig.list != 'undefined') ? attachConfig.list : {};
	if(typeof uploader != 'undefined' && uploader.data){
		mixObj = B.merge({}, uploader.data, list);
	}else{
		mixObj = list;
	}
	return str.replace(/\[attachment=(\d+)\]/g, function($1, $2){
		if(mixObj[$2]){
			var path = mixObj[$2][2],
				ext = path.substr(path.lastIndexOf('.')+1);
			if (['jpg', 'gif', 'png', 'jpeg', 'bmp'].indexOf(ext.toLowerCase()) >= 0){
				if(B.UA.ie<=6){
					var img=new Image();
					img.src=path;
					if(img.complete){
						if(img.width>320){
							return '<img src="'+path+'" width="320" attachment="'+$2+'">';
						}
					}else{
						var _temp="tmp"+(+(new Date()));
						img.onload=function(){
							if(img.width>320){
								var _html=editor.getHTML().replace('temp="'+_temp+'"','width="320"');
								editor.setHTML(_html);
							}
							img.onload=null;
						}
						return '<img src="'+path+'" temp="'+_temp+'" attachment="'+$2+'">';
					}
				}
				
				return '<img src="'+path+'" attachment="'+$2+'">';
				
			}else{
				return '<img src="images/wind/file/zip.gif" attachment="'+$2+'">';
			}
		}
		return $1;
	});
}
function p2br(txt) {
	txt=txt.replace(/^(\s*)<(p|div)>/ig, '$1');
	txt=txt.replace(/<(p|div)>/ig, "<br />");
	txt=txt.replace(/<\/(p|div)>/ig, '');
	return txt;
}
B.editor.ubb2attach = ubb2attach;
B.editor.ubb2html = ubb2html;
});