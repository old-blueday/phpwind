// JavaScript Document
Breeze.namespace('editor.ubb', function(B){

var UBBCommands = {
	Bold: 'b',
	Italic: 'i',
	Underline: 'u',
	Strikethrough: 's',
	JustifyLeft: 'align=left',
	JustifyRight: 'align=right',
	JustifyCenter: 'align=center',
	Image: 'img',
	Forecolor: 'color',
	Backcolor: 'back',
	hilitecolor: 'back',
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
	sell: 'sell'
};

//TODO:假设类传过来了。
var Editor = B.editor;

function UBBMode(editor){
	//
	var textarea = this.textarea = editor.textarea;
	this.command = function(command){
		if(['Undo','Redo'].indexOf(command)>-1){
			document.execCommand(command, false, null);
			return;
		}
		
		command = UBBCommands[command];
		var commands = command.split(' '),
			pretag = '[' + command.replace(/\s+/ig, '][') + ']',
			endtag = '';
		commands.forEach(function(n){
			endtag = '[/' + n.replace(/=.*$/ig, '') + ']' + endtag;
		});
		if (document.selection) {
			var rng = document.selection.createRange(),
				l = rng.text.length;
			if (rng.parentElement() != textarea){
				
				textarea.select();
				rng = document.selection.createRange();
				rng.collapse(false);
			}
			rng.text = pretag + rng.text + endtag;
			rng.moveStart('character', -endtag.length-l);
			rng.moveEnd('character', -endtag.length);
			if(rng.text){
				this._rng = rng;//rng.select();
			}
		} else if (typeof textarea.selectionStart != 'undefined') {
			var prepos = textarea.selectionStart, endpos = textarea.selectionEnd;
			var val = textarea.value;
			textarea.value = val.substr(0,prepos) + pretag + val.substr(prepos, endpos-prepos)+ endtag + val.substr(endpos);
			/*setTimeout(function(){
				textarea.setSelectionRange(prepos + 2 + command.length, endpos + 2 +command.length);
			}, 10);*/
			this._rng = [prepos + 2 + command.length, endpos + 2 +command.length];
		} else {
			textarea.value += pretag + endtag;
			var i = prepos + 2 + command.length;
			this._rng = [i, i];
			//textarea.setSelectionRange(i, i);
		}
	};

	this.valueCommand = function(command,value) {
		command = UBBCommands[command];
		var pretag = '[' + command + '=' + value + ']',
			endtag = '[/'+command+']';
		if (document.selection) {
			var rng = document.selection.createRange(),
				l = rng.text.length;
			if (rng.parentElement() != textarea){
				textarea.select();
				rng = document.selection.createRange();
				rng.collapse(false);
			}
			rng.text = pretag + rng.text + endtag;
			rng.moveStart('character', -endtag.length-l);
			rng.moveEnd('character', -endtag.length);
			if(rng.text){
				rng.select();
			}
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
	};
	this.wrapCommand = function(command){
		this.command(command);
	};
	
	this.insertCommand = function(command){
		this.valueCommand(command, value);
	};
}

UBBMode.prototype = {
	//储存选区
	saveRng: function(sel){
		var textarea = this.textarea;
		if (document.selection) {
			var rng = document.selection.createRange();
			if (rng.parentElement() != this.textarea){
				textarea.select();
				rng = document.selection.createRange();
				rng.collapse(false);
			}
			this._rng = rng;
		}else{
			this._rng = [this.textarea.selectionStart, this.textarea.selectionEnd];
		}
	},
	//恢复选区
	restoreRng: function(){
		if(B.UA.ie){
			this._rng.select();
		}else{
			var textarea = this.textarea, _rng = this._rng;
			setTimeout(function(){
				textarea.focus();
				textarea.setSelectionRange(_rng[0], _rng[1]);
			},140);
		}
	},
	//获得选区
	getRng: function(){
		return this._rng;
	},
	getSel: function(){
		//return text.selection || window.getSelection();
	},
	//获取HTML
	getHTML: function(){
		return this.formatXHTML(this.doc.body.innerHTML);
	},
	getSelText: function(){
		var textarea = this.textarea;
		if(B.UA.ie){
			return this.getRng().htmlText;
		}else{
			return textarea.value.substr(textarea.selectionStart, textarea.selectionEnd);
		}
	},

	pasteHTML: function(str){
		var textarea = this.textarea;
		str = html2ubb(str);
		if (document.selection) {
			var rng = this._rng;//document.selection.createRange(),
				l = rng.text.length;
			/*if (rng.parentElement() != textarea){
				textarea.select();
				rng = document.selection.createRange();
				rng.collapse(false);
			}*/
			rng.text = str;
			rng.moveStart('character', -str.length);
			rng.moveEnd('character', 0);
			if(rng.text){
				rng.select();
			}
		} else if (typeof textarea.selectionStart != 'undefined') {
			var prepos = textarea.selectionStart, endpos = textarea.selectionEnd,val = textarea.value;
			textarea.value = val.substr(0,prepos) + str + val.substr(endpos);
			//textarea.setSelectionRange(prepos, prepos+str.length);
		} else {
			var i = textarea.value.length;
			textarea.value += str;
			//var i = prepos + 2 + command.length;
			//textarea.setSelectionRange(i, i);
		}
	}
};

//添加切換按鈕;
Editor.prototype.plugins.push(function(){
	var tar = B.$('.B_tar', this.area);
	var textMode = B.createElement('<label for="B_textMode"><input type="checkbox"> 代码模式</label>');
	tar.appendChild(textMode);
	var checkbox = B.$('input', textMode);
	B.addEvent(checkbox, 'click', this.ubbtoggle.bind(this));
});
Editor.prototype.ubbtoggle = function(){
	if(!this.modes.UBB){
		this.modes.UBB = new UBBMode(this);
	}
	
	if(this.currentMode == 'default'){
		var txt = this.getHTML();
		this.textarea.value = html2ubb(txt);
		this.textarea.style.display = '';
		this.board.style.display= 'none';
		this.currentMode = 'UBB';
	}else{
		var txt = ubb2html(this.textarea.value);
		this.doc.body.innerHTML = txt;//this.modes['default'].formatXHTML(txt);
		this.textarea.style.display = 'none';
		this.board.style.display= '';
		this.currentMode = 'default';
	}
	B.$$query('.active', this.area)(B.removeClass, 'active')();
	this.doc.body.contentEditable = false;
	this.doc.body.contentEditable = true;
};
Editor.prototype.getUBB = function(){
	return this.currentMode == 'default' ? html2ubb(this.getHTML()) : this.textarea.value
}
function ubb2html(sUBB)
{
	var i,sHtml=String(sUBB),arrcode=new Array(),cnum=0;
	/*sHtml=sHtml.replace(/\[code\]([\s\S]*?)\[\/code\]/ig, function(all, context){
		return  context;
	});*/
	sHtml=sHtml.replace(/\[code\s*(?:=\s*([^\]]+?))?\]([\s\S]*?)\[\/code\]/ig,function(all,t,c){//code特殊处理
		cnum++;
		arrcode[cnum]= '<ol class="B_code"><li>' + c.replace(/\r?\n/ig, '</li><li>') + '</li></ol>';
		return "[\tubbcodeplace_"+cnum+"\t]";
	});

	sHtml=sHtml.replace(/&/ig, '&amp;');
	sHtml=sHtml.replace(/[<>]/g,function(c){return {'<':'&lt;','>':'&gt;'}[c];});
	sHtml=sHtml.replace(/\r?\n/g,"<br />");
	
	sHtml=sHtml.replace(/\[(\/?)(b|u|i|s)\]/ig, function(all, pre, tag){
		if (pre){
			return '</span>';
		}
		var str = '<span style="';
		switch(tag){
			case 'b':
				str += 'font-weight: bold;';
				break;
			case 'u':
				str += 'text-decoration: underline;';
				break;
			case 'i':
				str += 'font-style: italic;';
				break;
			case 's':
				str += 'text-decoration: line-through;';
		}
		str += '">';
		return str;
	});
	//sHtml=sHtml.replace(/\[(\/?)(b|u|i|s|sup|sub)\]/ig,'<$1$2>');
	
	sHtml=sHtml.replace(/\[color\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\]/ig,'<font color="$1">');
	sHtml=sHtml.replace(/\[size\s*=\s*(\d+?)\s*\]/ig,'<font size="$1">');
	sHtml=sHtml.replace(/\[font\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\]/ig,'<font face="$1">');
	sHtml=sHtml.replace(/\[\/(color|size|font)\]/ig,'</font>');
	sHtml=sHtml.replace(/\[back\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\]/ig,'<span style="background-color:$1;">');
	sHtml=sHtml.replace(/\[\/back\]/ig,'</span>');
	for(i=0;i<3;i++)sHtml=sHtml.replace(/\[align\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\](((?!\[align(?:\s+[^\]]+)?\])[\s\S])*?)\[\/align\]/ig,'<div align="$1">$2</div>');
	sHtml=sHtml.replace(/\[img\]\s*(((?!")[\s\S])+?)(?:"[\s\S]*?)?\s*\[\/img\]/ig,'<img src="$1" alt="" />');
	sHtml=sHtml.replace(/\[img\s*=([^,\]]*)(?:\s*,\s*(\d*%?)\s*,\s*(\d*%?)\s*)?(?:,?\s*(\w+))?\s*\]\s*(((?!")[\s\S])+?)(?:"[\s\S]*)?\s*\[\/img\]/ig,function(all,alt,p1,p2,p3,src){
		var str='<img src="'+src+'" alt="'+alt+'"',a=p3?p3:(!isNum(p1)?p1:'');
		if(isNum(p1))str+=' width="'+p1+'"';
		if(isNum(p2))str+=' height="'+p2+'"'
		if(a)str+=' align="'+a+'"';
		str+=' />';
		return str;
	});
	sHtml=sHtml.replace(/\[hr]/ig, '<hr>');
	sHtml=sHtml.replace(/\[s:(\d{1,3})]/ig, function(a,b){return '<img emotion="'+b+'" title="'+face[b][1]+'" src="' + face[b][0] + '" />';});
	sHtml=sHtml.replace(/\[url\]\s*(((?!")[\s\S])*?)(?:"[\s\S]*?)?\s*\[\/url\]/ig,'<a href="$1">$1</a>');
	sHtml=sHtml.replace(/\[url\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\]\s*([\s\S]*?)\s*\[\/url\]/ig,'<a href="$1">$2</a>');
	sHtml=sHtml.replace(/\[email\]\s*(((?!")[\s\S])+?)(?:"[\s\S]*?)?\s*\[\/email\]/ig,'<a href="mailto:$1">$1</a>');
	sHtml=sHtml.replace(/\[email\s*=\s*([^\]"]+?)(?:"[^\]]*?)?\s*\]\s*([\s\S]+?)\s*\[\/email\]/ig,'<a href="mailto:$1">$2</a>');
	sHtml=sHtml.replace(/\[quote\]([\s\S]*?)\[\/quote\]/ig,'<blockquote class="B_blockquote">$1</blockquote>');
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
		str+=' width="'+w+'"';
		str+=' bgcolor="'+b+'"';
		str+=' bordercolor="'+c+'"';
		str+=' border="'+s+'"';
		return str+'>';
	});
	sHtml=sHtml.replace(/\[tr\s*(?:=\s*([^\]"]+?)(?:"[^\]]*?)?)?\s*\]/ig,function(all,bg){
		return '<tr'+(bg?' bgcolor="'+bg+'"':'')+'>';
	});
	sHtml=sHtml.replace(/\[td\s*(?:=\s*(\d{1,2})\s*,\s*(\d{1,2})\s*(?:,\s*(\d{1,4}%?))?)?\s*\]/ig,function(all,col,row,w){
		return '<td'+(col>1?' colspan="'+col+'"':'')+(row>1?' rowspan="'+row+'"':'')+(w?' width="'+w+'"':'')+'>';
	});
	sHtml=sHtml.replace(/\[\/(table|tr|td)\]/ig,'</$1>');
	//sHtml=sHtml.replace(/\[li\]((?:(?!\[\/li\]|\[\/list\]|\[list\s*(?:=[^\]]+)?\])[\s\S])+)\[\/li\]/ig,'<li>$1</li>');
	sHtml=sHtml.replace(/\[list\s*(?:=\s*([^\]"]+?)(?:"[^\]]*?)?)?\s*\]?([\s\S]*?)\[\/list\]/ig,function(all,type, context){
		var tag= type ? 'ol' : 'ul';
		var str = '<' + tag + '>'
			+ context.replace(/\[li\]((?:(?!\[\/li\]|\[\/list\]|\[list\s*(?:=[^\]]+)?\])[\s\S])+)\[\/li\]/ig,'<li>$1</li>')
			+ '</'+tag+'>';
		return str;
	});

	//sHtml=sHtml.replace(/\[\/list\]/ig,'</ul>');
	
	for(i=1;i<=cnum;i++)sHtml=sHtml.replace("[\tubbcodeplace_"+i+"\t]", arrcode[i]);

	sHtml=sHtml.replace(/(^|<\/?\w+(?:\s+[^>]*?)?>)([^<$]+)/ig, function(all,tag,text){
		return tag+text.replace(/[\t ]/g,function(c){return {'\t':'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',' ':'&nbsp;'}[c];});
	});
	function isNum(s){if(s!=null&&s!='')return !isNaN(s);else return false;}
	return sHtml;
}

function html2ubb(sHtml)
{
	var formatColor = B.formatColor;
	var mapSize1=[10, 12, 16, 19, 24, 32, 48];
	var mapSize2=['x-small', 'small', 'medium', 'large', 'x-large', 'xx-large', '-webkit-xxx-large'];
	var regSrc=/\s+src\s*=\s*(["']?)\s*(.+?)\s*\1(\s|$)/i,regWidth=/\s+width\s*=\s*(["']?)\s*(\d+(?:\.\d+)?%?)\s*\1(\s|$)/i,regHeight=/\s+height\s*=\s*(["']?)\s*(\d+(?:\.\d+)?%?)\s*\1(\s|$)/i,regBg=/(?:background|background-color|bgcolor)\s*[:=]\s*(["']?)\s*((rgb\s*\(\s*\d{1,3}%?,\s*\d{1,3}%?\s*,\s*\d{1,3}%?\s*\))|(#[0-9a-f]{3,6})|([a-z]{1,20}))\s*\1/i;
	var regBc=/(?:border-color|bordercolor)\s*[:=]\s*(["']?)\s*((rgb\s*\(\s*\d{1,3}%?,\s*\d{1,3}%?\s*,\s*\d{1,3}%?\s*\))|(#[0-9a-f]{3,6})|([a-z]{1,20}))\s*\1/i;
	var regBw=/\s+border\s*=\s*(["']?)\s*(\d+(?:\.\d+)?)\s*\1(\s|$)/i;
	var i,sUBB=String(sHtml),arrcode=new Array(),cnum=0;
	sUBB=sUBB.replace(/\s*\r?\n\s*/g,'');
	sUBB = sUBB.replace(/<(script|style)(\s+[^>]*?)?>[\s\S]*?<\/\1>/ig, '');
	sUBB = sUBB.replace(/<!--[\s\S]*?-->/ig,'');

	sUBB=sUBB.replace(/<br\s*?\/?>/ig,"\r\n");
	
	sUBB=sUBB.replace(/\[code\s*(=\s*([^\]]+?))?\]([\s\S]*?)\[\/code\]/ig,function(all,t,c){//code特殊处理
		cnum++;arrcode[cnum]=all;
		return "[\tubbcodeplace_"+cnum+"\t]";
	});
	
	sUBB=sUBB.replace(/<(\/?)(b|u|i|s)(\s+[^>]*?)?>/ig,'[$1$2]');
	sUBB=sUBB.replace(/<(\/?)strong(\s+[^>]*?)?>/ig,'[$1b]');
	sUBB=sUBB.replace(/<(\/?)em(\s+[^>]*?)?>/ig,'[$1i]');
	sUBB=sUBB.replace(/<(\/?)(strike|del)(\s+[^>]*?)?>/ig,'[$1s]');
	sUBB=sUBB.replace(/<(\/?)(sup|sub)(\s+[^>]*?)?>/ig,'[$1$2]');
	sUBB=sUBB.replace(/<hr[^>]*?\/?>/ig,'[hr]');

	for(i=0;i<9;i++)sUBB=sUBB.replace(/<(span)(?:\s+[^>]*?)?\s+style\s*=\s*"((?:[^"]*?;)*\s*(?:font-weight|text-decoration|font-style|font-family|font-size|color|background|background-color)\s*:[^"]*)"(?: [^>]+)?>(((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S])*?<\/\1>)*?<\/\1>)*?)<\/\1>/ig,function(all,tag,style,content){
		var bold=style.match(/(?:^|;)\s*font-weight\s*:\s*bold/i),
			underline=style.match(/(?:^|;)\s*text-decoration\s*:[^;]*underline/i),
			strike=style.match(/(?:^|;)\s*text-decoration\s*:[^;]*line-through/i)
			italic=style.match(/(?:^|;)\s*font-style\s*:\s*italic/i),
			face=style.match(/(?:^|;)\s*font-family\s*:\s*([^;]+)/i),
			size=style.match(/(?:^|;)\s*font-size\s*:\s*([^;]+)/i),
			color=style.match(/(?:^|;)\s*color\s*:\s*([^;]+)/i),
			back=style.match(/(?:^|;)\s*(?:background|background-color)\s*:\s*([^;]+)/i),
			str=content;
		if(face)str='[font='+face[1]+']'+str+'[/font]';
		if(italic)str='[i]'+str+'[/i]';
		if(strike)str='[s]'+str+'[/s]';
		if(underline)str='[u]'+str+'[/u]';
		if(bold)str='[b]'+str+'[/b]';
		if(color)str='[color='+formatColor(color[1])+']'+str+'[/color]';
		if(back)str='[back='+formatColor(back[1])+']'+str+'[/back]';
		if(size)
		{
			if (size[1].toLowerCase().indexOf('px')>-1){
				size = mapSize1.indexOf( parseInt(size[1]) ) + 1;
			}else if (mapSize2.indexOf(size[1]) > -1){
				size = mapSize2.indexOf(size[1]) + 1;
			}
			if(size)str='[size='+size+']'+str+'[/size]';
		}

		return str;
	});
	for(i=0;i<3;i++)sUBB=sUBB.replace(/<(div|p)(?:\s+[^>]*?)?[\s"';]\s*(?:text-)?align\s*[=:]\s*(["']?)\s*(left|center|right)\s*\2[^>]*>(((?!<\1(\s+[^>]*?)?>)[\s\S])+?)<\/\1>/ig,'[align=$3]$4[/align]');
	for(i=0;i<3;i++)sUBB=sUBB.replace(/<(center)(?:\s+[^>]*?)?>(((?!<\1(\s+[^>]*?)?>)[\s\S])*?)<\/\1>/ig,'[align=center]$2[/align]');
	for(i=0;i<3;i++)sUBB=sUBB.replace(/<(p|div)(?:\s+[^>]*?)?\s+style\s*=\s*"((?:[^"]*?;)*\s*text-align\s*:[^"]*)"(?: [^>]+)?>(((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S])*?<\/\1>)*?<\/\1>)*?)<\/\1>/ig,function(all,tag,style,content){
		
	});
	sUBB=sUBB.replace(/<a(?:\s+[^>]*?)?\s+href=(["'])\s*(.+?)\s*\1[^>]*>\s*([\s\S]*?)\s*<\/a>/ig,function(all,q,url,text){
		if(!(url&&text))return '';
		var tag='url',str;
		if(url.match(/^mailto:/i))
		{
			tag='email';
			url=url.replace(/mailto:(.+?)/i,'$1');
		}
		str='['+tag;
		if(url!=text)str+='='+url;
		return str+']'+text+'[/'+tag+']';
	});
	sUBB=sUBB.replace(/<img(\s+[^>]*?)\/?>/ig,function(all,attr){
		var emot=attr.match(/\s+emotion\s*=\s*(["']?)\s*(.+?)\s*\1(\s|$)/i);
		if(emot)return '[s:'+emot[2]+']';
		var url=attr.match(regSrc),w=attr.match(regWidth),h=attr.match(regHeight),align=attr.match(/\s+align\s*=\s*(["']?)\s*(\w+)\s*\1(\s|$)/i),str='[img',p='';
		if(!url)return '';
		if(w||h)p+=','+(w?w[2]:'')+','+(h?h[2]:'');
		if(align)p+=','+align[2];
		if(p)str+='='+p;
		str+=']'+url[2]+'[/img]';
		return str;
	});
	sUBB=sUBB.replace(/<blockquote\s+[^>]*?class=\"B_blockquote\"[^>]*?>[\r\n]*?([\s\S]+?)[\r\n]*?<\/blockquote>/ig, '[quote]$1[/quote]');
	sUBB=sUBB.replace(/<blockquote(?:\s+[^>]*?)?>[\r\n]*?([\s\S]+?)[\r\n]*?<\/blockquote>/ig,'[blockquote]$1[/blockquote]');
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
		if(attr)
		{
			var w=attr.match(regWidth),b=attr.match(regBg),c=attr.match(regBc),s=attr.match(regBw);
			if(w)
			{
				str+='='+w[2];
				if(b)str+=','+B.formatColor(b[2]);
				if(c)str+=','+B.formatColor(c[2]);
				if(s)str+=','+s[2];
			}else{
				str += '=100%,#ffffff,#cccccc,1'
			}
		}
		return str+']';
	});
	sUBB=sUBB.replace(/<tr(\s+[^>]*?)?>/ig,function(all,attr){
		var str='[tr';
		if(attr)
		{
			var bg=attr.match(regBg)
			if(bg)str+='='+bg[2];
		}
		return str+']';
	});
	sUBB=sUBB.replace(/<(?:th|td)(\s+[^>]*?)?>/ig,function(all,attr){
		var str='[td';
		if(attr)
		{
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
		return '[align='+tag+'][list]' + context.replace(/<li(\s+[^>]*?)?>([\s\S]*?)[\r\n]*?<\/li>/ig, "[li]$2[/li]") + '[/list][/align]';
		}
		if(attr)t=attr.match(/\s+type\s*=\s*(["']?)\s*(.+?)\s*\1(\s|$)/i);
		return '[list'+(t?'='+t[2]:'')+']' + context.replace(/<li(\s+[^>]*?)?>([\s\S]*?)[\r\n]*?<\/li>/ig, "[li]$2[/li]") + '[/list]';
	});
	sUBB=sUBB.replace(/<ol(\s+[^>]*?)?>([\s\S]*?)<\/ol>/ig, function(all, attr, context){
		var tag;
		if( attr && attr.match(/align="?([^\s"]*?)"?/ig) ){
			tag = /align="?([^\s"]*?)"?/ig.exec(attr)[1];
		}else if(  attr && attr.match(/text-align\s*:\s*([^\s;]*?);/ig) ) {
			tag = /text-align\s*:\s*([^\s;]*?);/ig.exec(attr)[1];
		}
		if(tag){
			return '[align='+tag+'][list=1]' + context.replace(/<li(\s+[^>]*?)?>([\s\S]*?)[\r\n]*?<\/li>/ig, '[li]$2[/li]') + '[/list][/align]';
		}else if( attr && attr.match(/class="?B_code"?/) ){
			return '[code]' + B.trim(context.replace(/<li(\s+[^>]*?)?>([\s\S]*?)[\r\n]*?<\/li>/ig, "$2\n")) + '[/code]';
		}else{
			return '[list=1]' + context.replace(/<li(\s+[^>]*?)?>([\s\S]*?)[\r\n]*?<\/li>/ig, '[li]$2[/li]') + '[/list]';
		}
	});
	
	sUBB=sUBB.replace(/<h([1-6])(\s+[^>]*?)?>/ig,function(all,n){return '\r\n\r\n[size='+(7-n)+'][b]'});
	sUBB=sUBB.replace(/<\/h[1-6]>/ig,'[/b][/size]\r\n\r\n');
	sUBB=sUBB.replace(/<address(\s+[^>]*?)?>/ig,'\r\n[i]');
	sUBB=sUBB.replace(/<\/address>/ig,'[i]\r\n');
	for(i=0;i<3;i++)sUBB=sUBB.replace(/<(p)(?:\s+[^>]*?)?>(((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S])*?<\/\1>)*?<\/\1>)*?)<\/\1>/ig,"\r\n\r\n$2\r\n\r\n");
	for(i=0;i<3;i++)sUBB=sUBB.replace(/<(div)(?:\s+[^>]*?)?>(((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S])*?<\/\1>)*?<\/\1>)*?)<\/\1>/ig,"\r\n$2\r\n");
	
	sUBB=sUBB.replace(/((\s|&nbsp;)*\r?\n){3,}/g,"\r\n\r\n");//限制最多2次换行
	sUBB=sUBB.replace(/^((\s|&nbsp;)*\r?\n)+/g,'');//清除开头换行
	sUBB=sUBB.replace(/((\s|&nbsp;)*\r?\n)+$/g,'');//清除结尾换行
	
	for(i=1;i<=cnum;i++)sUBB=sUBB.replace("[\tubbcodeplace_"+i+"\t]", arrcode[i]);

	sUBB=sUBB.replace(/<[^<>]+?>/g,'');//删除所有HTML标签
	sUBB=sUBB.replace(/&lt;/ig, '<');
	sUBB=sUBB.replace(/&gt;/ig, '>');
	sUBB=sUBB.replace(/&nbsp;/ig, ' ');
	sUBB=sUBB.replace(/&amp;/ig, '&');
	
	return sUBB;
}
});