/**
 * @fileoverview 通用简易富文本编辑器
 * 完成一些基本的功能
 * @author yuyang <yuyangvi@gmail.com>
 * @version 1.0
 */
Breeze.namespace('editor.editor', function(B){
B.require('dom', 'event', function(B){
	var PRE = 'B_', ARR_FONT_SIZE = [10, 12, 16, 19, 24, 32, 48];
	//关闭弹窗的函数
	function closeAll(){
		B.query('.B_menu').css('display', 'none');
	}
	
	//过滤粘贴内容函数
	function filterPasteData(dat){
		 // Remove all SPAN tags
		/*dat = dat.replace(/<\/?SPAN[^>]*>/gi, "" )
		.replace(/<(\w[^>]*) class=([^|>]*)([^>]*)/gi, "<$1$3")
		.replace(/<(\w[^>]*) style="([^"]*)"([^>]*)/gi, "<$1$3")
		.replace(/<(\w[^>]*) lang=([^ |>]*)([^>]*)/gi, "<$1$3")
		.replace(/<\\?\?xml[^>]*>/gi, "")
		.replace(/<\/?\w+:[^>]*>/gi, "")
		.replace(/ /, " " )
		.replace(/<(\/?)pre/gi,"<$1div");*/
		// Transform <P> to <DIV>
		var re = new RegExp("(<P)([^>]*>.*?)(<\/P>)","gi") ; // Different because of a IE 5.0 error
		dat = dat.replace( re, "<div$2</div>" ) ;
		return dat;
	}
	//屏蔽
	function block(e) {
		e.halt();
		return false;
	}
	//Mode
	function DefaultMode(editor){
		this.editor = editor;
		this.init = function() {
			var self = this,
				iframe = B.createElement('<iframe id="note_iframe" width="100%" frameborder="no" style="height:100%;display: block;"></iframe>'),
				textareaHeight = B.height(this.editor.textarea) || 300,
				style = {
					//width: '100%',
					height: textareaHeight+'px',
					backgroundColor: '#ffffff',
					dispaly : 'none'
				},
				div = B.createElement('div', {}, style);
			B.attr(iframe, 'tabindex', 199);
			div.appendChild(iframe);
			B.insertBefore(div, this.editor.textarea);
			this.editor.textarea.style.display = 'none';

			//doc设定
			var win = iframe.contentWindow,
			doc = win.document;
			doc.open();
			var defaultText=B.toolbarCommands.fontSelector[5].defaultText;
			doc.write('<html><head>\
			<style>body{border:0px;font-family:'+defaultText+';font-size:14px;margin:0;padding:0;line-height:1.8;word-wrap:break-word;height:100%;}\
			p{margin:0;}img{border:0;max-width:320px;}\
			table{border-collapse:collapse;font-size:14px;}pre{border:1px dashed #FF33FF;background:#FFddFF}\
			.blockquote{zoom:1;border:1px dashed #CCC;background:#f7f7f7;padding:5px 10px;margin:10px 10px 0;}\
			.B_code{border: 1px solid; border-color: #c0c0c0 #ededed #ededed #c0c0c0;margin:1em;padding:0 0 0 3em;overflow:hidden;background:#ffffff; font:12px/2 Simsun;}\
			.B_code li{border-left:1px solid #ccc;background:#f7f7f7;padding:0 10px;}\
			.B_code li:hover{background:#ffffff;color:#008ef1;}td{padding:0 5px;line-height:1.5;}\
			</style></head><body contentEditable="true">'+(B.UA.ie?'':'<br/>')+'</body></html>');
			doc.close();
			this.doc = doc;
			this.container = div;
			this.editContainer = doc.body;
			this.win = win;
			this.iframe = iframe;
			this.editor.div = div;
			B.addEvent(doc, 'mouseup', this.editor.updateToolbar.bind(this.editor));
			B.addEvent(doc, 'click', function(){
				B.$('#breeze-colorPicker') && (B.$('#breeze-colorPicker').style.display = 'none');
			});
			B.addEvent(doc.body, 'mousedown', function(e){
				self.clearRng();
				e.stopPropagation();
			});
			B.addEvent(doc.body, 'keyup', function(e){
				self.clearRng();
			});
			//解决点击没有焦点BUG
			B.addEvent(doc, 'mousedown', function(){
				setTimeout(function(){
					if(B.UA.ie){
						doc.execCommand('selectAll');
						var rng = doc.selection.createRange();
						rng.collapse(false);
						rng.select();
					}else{
						doc.body.focus();
					}
				},0);
			});
			this.editor.setAutoSave(doc);
			if (this.editor.textarea.value) {
				this.setHTML((this.editor.getHtmlFromUBB ? this.editor.getHtmlFromUBB() : this.editor.textarea.value) + '<br />');
			} else {
				this.setHTML('<div>'+(B.UA.opera?'&nbsp;':(B.UA.ie?'':'<br />'))+'</div>');
			}
			setTimeout(function(){
				//if(B.UA.ie<7){//兼容IE6的後退功能,但是因此不能捕?@??態
					//doc.designMode = 'on';
				//} else {
					self.setEditable();
				//}
			}, 100);

			if (B.UA.ie){
				B.addEvent(doc.body, 'paste', self.pasteCache4IE.bind(self));
			} else {
				B.addEvent(doc.body, 'paste', self.pasteCache.bind(self));
			}
		}
		this.command = function(command){
			
			if (command == 'Inserthorizontalrule'){
				this.pasteHTML('<hr><br>');
			} else if (command == 'PgFormat') {
				B.$$('div', editDoc.body).forEach(function(n){
					B.css(n, 'text-indent', '2em');
				});
			} else {
				if(B.UA.webkit&&command=="RemoveFormat"){
					editDoc.execCommand("hilitecolor", false, "transparent");
				}
				editDoc.execCommand(command, false, null);
			}
		}
		this.queryState = function(command) {
			return editDoc.queryCommandState(command);
		}
		this.valueCommand = function(command,value) {
			editDoc.execCommand(command, false, value); 
			editor.updateToolbar();
		}
		this.queryValue = function(command) {
			return editDoc.queryCommandValue(command);
		}
		this.wrapCommand = function(command){
			if(B.UA.ie||B.UA.webkit){
				editDoc.execCommand('Indent', false, null);
				var pNode = editDoc.selection.createRange().parentElement();
				if(pNode.tagName == 'BLOCKQUOTE'){
					pNode.className = 'B_blockquote';
					pNode.style.marginRight = '';
				}
			}else{
				editDoc.execCommand('FormatBlock', false, 'blockquote');
				var pNode = this.win.getSelection().getRangeAt(0).commonAncestorContainer;
				pNode.tagName=='BLOCKQUOTE';
			}
		}
		this.insertCommand = function(command){
			this.pasteHTML(value);
		}
		this.setEditable = function() {
			this.doc.body.contentEditable = false;
			this.doc.body.contentEditable = true;
		}
		this.init();
		var editDoc = this.doc;
	}
	
	DefaultMode.prototype = {
		focus: function() {
			this.doc.body.focus();
		},
		//储存选区
		saveRng: function(){
			if (this.doc.selection){
				var rng = this.doc.selection.createRange();
				if (rng.parentElement && rng.parentElement().document == this.doc){
					this._rng = rng;
				}
			} else {
				var sel = this.win.getSelection()||this.win.defaultView.getSelection();
				if (sel.rangeCount > 0){
					this._rng = sel.getRangeAt(0);
				}
			}
		},
		clearRng: function() {
			this._rng = null;
		},
		//恢复选区
		restoreRng: function(){
			if(!this._rng) return;
			if(B.UA.ie){
				this._rng.select();
			} else {
				this.focus();
				var sel = this.win.getSelection(),
					newRng = this._rng.cloneRange();
				sel.addRange(newRng);
			}
		},
		//获得选区
		getRng: function(){
			if (this._rng){
				return this._rng;
			}
			this.focus();
			if(this.doc.selection){//IE
				return this.doc.selection.createRange();
			} else{
				return this.win.getSelection().getRangeAt(0);
			}
		},
		getSel: function(){
			return this.doc.selection || this.win.getSelection();
		},
		//获取HTML
		getHTML: function(){
			return this.formatXHTML(this.doc.body.innerHTML);
		},
		setHTML: function(sHtml){
			this.doc.body.innerHTML = sHtml;
		},
		getSelText: function(){
			if(B.UA.ie){
				return this.formatXHTML(this.getRng().htmlText);
			}else{
				var d = B.createElement('div');
				d.appendChild(this.getRng().cloneContents());
				return this.formatXHTML(d.innerHTML);
			}
		},
		isSel: function() {
			return !(B.UA.ie ? !this.getRng().text : this.getRng().collapsed);
		},

		//插入HTML
		pasteHTML:function(sHtml)
		{
			//_this.focus();
			//sHtml=_this.processHTML(sHtml,'write');
			var sel = this.getSel(), rng = this.getRng();

			//为了定位在后面
			sHtml += '<'+(B.UA.ie?'img':'span')+' id="_brz_mark" width="0" height="0" />';
			if(rng.insertNode){
				rng.deleteContents();
				rng.insertNode(rng.createContextualFragment(sHtml));
			}else{
				if(sel.type.toLowerCase()=='control'){sel.clear();rng=_this.getRng();};
				rng.pasteHTML(sHtml);
			}
			var bmark=B.$('#_brz_mark',this.doc);
			if(bmark){
				if(B.UA.ie){
					rng.moveToElementText(bmark);
					rng.select();
				}
				else if(bmark){
					rng.selectNode(bmark); 
					sel.removeAllRanges();
					sel.addRange(rng);
				}
				B.remove(bmark);
			}
			//editor.focus();
		},

		//格式化HTML
		formatXHTML: function(sHtml,bFormat){//By John Resig
			var emptyTags = makeMap("area,base,basefont,br,col,frame,hr,img,input,isindex,link,meta,param,embed");//HTML 4.01
			var blockTags = makeMap("address,applet,blockquote,button,center,dd,dir,div,dl,dt,fieldset,form,frameset,hr,iframe,ins,isindex,li,map,menu,noframes,noscript,object,ol,p,pre,script,table,tbody,td,tfoot,th,thead,tr,ul");//HTML 4.01
			var inlineTags = makeMap("a,abbr,acronym,applet,b,basefont,bdo,big,br,button,cite,code,del,dfn,em,font,i,iframe,img,input,ins,kbd,label,map,object,q,s,samp,script,select,small,span,strike,strong,sub,sup,textarea,tt,u,var");//HTML 4.01
			var closeSelfTags = makeMap("colgroup,dd,dt,li,options,p,td,tfoot,th,thead,tr");
			var fillAttrsTags = makeMap("checked,compact,declare,defer,disabled,ismap,multiple,nohref,noresize,noshade,nowrap,readonly,selected");
			var specialTags = makeMap("script,style");
			var tagReplac={'b':'strong','i':'em','s':'del','strike':'del'};
			var startTag = /^<\??(\w+(?:\:\w+)?)((?:\s+[\w-\:]*(?:\s*=\s*(?:(?:"[^"]*")|(?:'[^']*')|[^>\s]+))?)*)\s*(\/?)>/;
			var endTag = /^<\/(\w+(?:\:\w+)?)[^>]*>/;
			var attr = /([\w-]+(?:\:\w+)?)(?:\s*=\s*(?:(?:"([^"]*)")|(?:'([^']*)')|([^\s\/>]+)))?/g;
			var skip=0,stack=[],last=sHtml,results=Array(),lvl=-1,lastTag='body',lastTagStart;
			stack.last = function(){return this[ this.length - 1 ];};
			while(last.length>0)
			{
				if(!stack.last()||!specialTags[stack.last()])
				{
					skip=0;
					if(last.substring(0, 4)=='<!--')
					{//注释标签
						skip=last.indexOf("-->");
						if(skip!=-1)
						{
							skip+=3;
							addHtmlFrag(last.substring(0,skip));
						}
					}
					else if(last.substring(0, 2)=='</')
					{//结束标签
						match = last.match( endTag );
						if(match)
						{
							parseEndTag(match[1]);
							skip = match[0].length;
						}
					}
					else if(last.charAt(0)=='<')
					{//开始标签
						match = last.match( startTag );
						if(match)
						{
							parseStartTag(match[1],match[2],match[3]);
							skip = match[0].length;
						}
					}
					if(skip==0)//普通文本
					{
						skip=last.indexOf('<');
						if(skip==0)skip=1;
						else if(skip<0)skip=last.length;
						addHtmlFrag(last.substring(0,skip).replace(/[<>]/g,function(c){return {'<':'&lt;','>':'&gt;'}[c];}));
					}
					last=last.substring(skip);
				}
				else
				{//处理style和script
					last=last.replace(/^([\s\S]*?)<\/(style|script)>/i, function(all, script,tagName){
						addHtmlFrag(script);
						return ''
					});
					parseEndTag(stack.last());
				}
			}
			parseEndTag();
			sHtml=results.join('');
			results=null;
			function makeMap(str)
			{
				var obj = {}, items = str.split(",");
				for ( var i = 0; i < items.length; i++ )obj[ items[i] ] = true;
				return obj;
			}
			function processTag(tagName)
			{
				if(tagName)
				{
					tagName=tagName.toLowerCase();
					var tag=tagReplac[tagName];
					if(tag)tagName=tag;
				}
				else tagName='';
				return tagName;
			}
			function parseStartTag(tagName,rest,unary)
			{
				tagName=processTag(tagName);
				if(blockTags[tagName])while(stack.last()&&inlineTags[stack.last()])parseEndTag(stack.last());
				if(closeSelfTags[tagName]&&stack.last()==tagName)parseEndTag(tagName);
				unary = emptyTags[ tagName ] || !!unary;
				if (!unary)stack.push(tagName);
				var all=Array();
				all.push('<' + tagName);
				rest.replace(attr, function(match, name)
				{
					name=name.toLowerCase();
					var value = arguments[2] ? arguments[2] :
							arguments[3] ? arguments[3] :
							arguments[4] ? arguments[4] :
							fillAttrsTags[name] ? name : "";
					all.push(' '+name+'="'+value+'"');
				});
				all.push((unary ? " /" : "") + ">");
				addHtmlFrag(all.join(''),tagName,true);
			}
			function parseEndTag(tagName)
			{
				if(!tagName)var pos=0;//清空栈
				else
				{
					tagName=processTag(tagName);
					for(var pos=stack.length-1;pos>=0;pos--)if(stack[pos]==tagName)break;//向上寻找匹配的开始标签
				}
				if(pos>=0)

				{
					for(var i=stack.length-1;i>=pos;i--)addHtmlFrag("</" + stack[i] + ">",stack[i]);
					stack.length=pos;
				}
			}
			function addHtmlFrag(html,tagName,bStart)
			{
				if(bFormat==true)
				{
					html=html.replace(/(\t*\r?\n\t*)+/g,'');//清理换行符和相邻的制表符
					if(html.match(/^\s*$/))return;//不格式化空内容的标签
					var bBlock=blockTags[tagName],tag=bBlock?tagName:'';
					if(bBlock)
					{
						if(bStart)lvl++;//块开始
						if(lastTag=='')lvl--;//补文本结束
					}
					else if(lastTag)lvl++;//文本开始
					if(tag!=lastTag||bBlock)addIndent();
					results.push(html);
					if(tagName=='br')addIndent();//回车强制换行
					if(bBlock&&(emptyTags[tagName]||!bStart))lvl--;//块结束
					lastTag=bBlock?tagName:'';lastTagStart=bStart;
				}
				else results.push(html);
			}
			function addIndent(){results.push('\r\n');if(lvl>0){var tabs=lvl;while(tabs--)results.push("\t");}}
			//font转style
			function font2style(all,tag,attrs,content)
			{
				var styles='',f,s,c,style;
				f=attrs.match(/ face\s*=\s*"\s*([^"]+)\s*"/i);
				if(f)styles+='font-family:'+f[1]+';';
				s=attrs.match(/ size\s*=\s*"\s*(\d+)\s*"/i);
				if(s)styles+='font-size:'+ARR_FONT_SIZE[(s[1]>7?7:(s[1]<1?1:s[1]))-1]+'px;';
				c=attrs.match(/ color\s*=\s*"\s*([^"]+)\s*"/i);
				if(c)styles+='color:'+c[1]+';';
				style=attrs.match(/ style\s*=\s*"\s*([^"]+)\s*"/i);
				if(style)styles+=style[1];
				if(styles)content='<span style="'+styles+'">'+content+'</span>';
				return content;
			}
			sHtml = sHtml.replace(/<(font)(\s+[^>]*?)?>(((?!<\1(\s+[^>]*?)?>)[\s\S])*?)<\/\1>/ig,font2style);//最里层
			sHtml = sHtml.replace(/<(font)(\s+[^>]*?)?>(((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S])*?<\/\1>)*?)<\/\1>/ig,font2style);//第2层
			sHtml = sHtml.replace(/<(font)(\s+[^>]*?)?>(((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S])*?<\/\1>)*?<\/\1>)*?)<\/\1>/ig,font2style);//第3层
			//sHtml = sHtml.replace(/^(\s*\r?\n)+|(\s*\r?\n)+$/g,'');//清理首尾换行
			sHtml = sHtml.replace(/(\t*\r?\n)+/g,'\r\n');//多行变一行
			return sHtml;
		},
		/**
		 *粘贴管理
		 */
		pasteCache4IE: function(evt){
			this.saveRng();
			var ifmTemp=document.getElementById("ifmTemp");
			if (!ifmTemp){
				ifmTemp=document.createElement("IFRAME");
				ifmTemp.id="ifmTemp";
				ifmTemp.style.width="1px";
				ifmTemp.style.height="1px";
				ifmTemp.style.position="absolute";
				ifmTemp.style.border="none";
				ifmTemp.style.left="-10000px";
				//ifmTemp.src="iframeblankpage.html";
				document.body.appendChild(ifmTemp);
				ifmTemp.contentWindow.document.designMode = "On";
				ifmTemp.contentWindow.document.open();
				ifmTemp.contentWindow.document.write("<body></body>");
				ifmTemp.contentWindow.document.close();
			}else {
				ifmTemp.contentWindow.document.body.innerHTML="";
			}
			ifmTemp.contentWindow.focus();
			ifmTemp.contentWindow.document.execCommand("Paste",false,null);
			this.win.focus();
		
			var newData = ifmTemp.contentWindow.document.body.innerHTML;
			//filter the pasted data
			newData = filterPasteData(newData);
			ifmTemp.contentWindow.document.body.innerHTML = newData;
			
			//paste the data into the editor
			rng = this.getRng();
			rng.pasteHTML(newData);
			evt.halt();
		},
		pasteCache: function(evt){
			var doc = this.doc,
				enableKeyDown=false,
				self = this;
			//create the temporary html editor
			this.saveRng();
			var divTemp = this.doc.createElement("div");
			divTemp.id='htmleditor_tempdiv';
			divTemp.innerHTML='\uFEFF';
			divTemp.style.left="-10000px";    //hide the div
			divTemp.style.height="1px";
			divTemp.style.width="1px";
			divTemp.style.position="absolute";
			divTemp.style.overflow="hidden";
			this.doc.body.appendChild(divTemp);
			//disable keyup,keypress, mousedown and keydown
            B.addEvent(this.doc, 'mousedown', block);
            B.addEvent(this.doc, 'keydown', block);
            enableKeyDown=false;

            //get current selection;
            var sel = this.win.getSelection();

            //move the cursor to into the div
            var docBody=divTemp.firstChild,
            	rng = doc.createRange();
				if(!B.UA.webkit){  
					 rng.setStart(docBody, 0);
					 rng.setEnd(docBody, 1);
					 sel.removeAllRanges();
					 sel.addRange(rng);
				 }

            var originText = doc.body.textContent;
            if (originText==='\uFEFF'){
            	originText="";
            }

            setTimeout(function(){
				var newData = '';
            	//get and filter the data after onpaste is done
				if (divTemp.innerHTML === '\uFEFF'){//webkit返回
					newData="";
					doc.body.removeChild(divTemp);
					return;
				}
				newData = divTemp.innerHTML;
				if (self._rng){
					sel.removeAllRanges();
					sel.addRange(self._rng);
				}
				//过滤Excel
				window.a = newData;
				newData=filterPasteData(newData);
				divTemp.innerHTML=newData;
				//paste the new data to the editor
				doc.execCommand('inserthtml', false, newData );
				doc.body.removeChild(divTemp);
			}, 16);
            //enable keydown,keyup,keypress, mousedown;
            enableKeyDown = true;
            B.removeEvent(doc, 'mousedown', block);
            B.removeEvent(doc, 'keydown', block);
			return true;
		}
	};
	
	/**
	 * UI工厂
	 */
	//普通的
	function iconUI(command, group ,title){
		var el;
		if (B.UA.ie && B.UA.ie < 7 && ['undoIcon','redoIcon'].indexOf(command)>-1){
			el = B.createElement('<a class="'+PRE+'ico B_disabled" title="'+title+'" href="javascript:;"><div class="'+PRE+command+'"></div></a>');
		}else{
			el = B.createElement('<a class="'+PRE+'ico" title="'+title+'" href="javascript:;"><div class="'+PRE+command+'"></div></a>');
		}
		group.appendChild(el);
		return el;
	}
	//大按钮
	function buttonUI(command, group){
		var el = B.createElement('<a class="'+PRE+'ico" title="'+title+'" href="javascript:;">'+command+'</a>');
		group.appendChild(el);
		return el;
	}
	//选择框
	function selectorUI(command, group, title){
		var el = B.createElement('<div class="' + PRE + 'selector" title="'+title+'"></div>'),
		data = B.toolbarCommands[command][5],
		ul = B.createElement('ul', {unselectable:'on'}, {'width':data.width+17+'px'});
		for(var n in data.list){
			var style = {}, li;
			style[data.style] = data.list[n];
			li = B.createElement('li', {unselectable:'on'}, style);
			if(command == 'sizeSelector'){
				li.style.fontSize = ARR_FONT_SIZE[n-1]+'px';
			}
			li.innerHTML = n;
			ul.appendChild(li);
		}
		var ulContainer = B.createElement('<div class="B_fl"></div>');
		ulContainer.appendChild(ul);
		el.appendChild(ulContainer);
		el.innerHTML += '<span style="width:'+data.width+'px" unselectable="on">'+data.defaultText+'</span><div class="B_dropdown" unselectable="on">&nbsp;</div>';
		el.defaultText = data.defaultText;
		if(B.UA.ie < 7){
			function mOver(){
				B.addClass(this, 'hover');
			}
			function mOut(){
				B.removeClass(this, 'hover');
			}
			B.addEvent(el, 'mouseover', mOver);
			B.addEvent(el, 'mouseout', mOut);
		}
		group.appendChild(el);
		return el;
	}

	//颜色框
	function colorUI(command, group, title){		
		var el = B.createElement('<div class="'+PRE+'icoDown"><a class="'+PRE+'ico" href="javascript://"><div title="' + title + '" class="' + PRE+command + '"><span style="background-color:' + B.toolbarCommands[command][5] + '"></span>' + title + '</div></a><em unselectable="on"></em></div>');
		group.appendChild(el);
		return el;
	}
	//大按钮
	function buttonUI(command, group, title){
		command = command.replace('Btn', 'Icon');
		var el = B.createElement('<a class="'+PRE+'icoBig" href="javascript:;" title="插入'+title+'"></a>');
		el.innerHTML = '<div class="'+PRE+command+'"></div><p>'+title+'</p>';
		group.appendChild(el);
		return el;
	}
	//换行
	function brUI(command, group){
		var el = B.createElement('<div class="'+PRE+'clear"></div>');
		el.innerHTML = '&nbsp;';
		group.appendChild(el);
		return el;
	}
		
	/**
		控制器模块
		-----------
		Connects Command-obejcts to DOM nodes which works as UI
	*/
	function CommandController(command, val, elem, editor){
		var self = this;
		elem.unselectable = "on"; // IE, prevent focus
		/*B.addEvent(elem, "mousedown", function(evt) { 
			// we cancel the mousedown default to prevent the button from getting focus
			// (doesn't work in IE)
			if (!B.UA.ie){
				evt.preventDefault();
			}
		})*/
		B.addEvent(elem, "mousedown", function(evt) { 
			editor.saveRng();
			var mode = editor.modes[editor.currentMode];
			mode[command](val);
			//editor.restoreRng();
			evt.preventDefault();
		});
		B.addEvent(elem, 'click', block);

	}
	function ToggleCommandController(command, val, elem, editor) {
		this.updateUI = function() {
			if (editor.currentMode != 'default') return;
			try{
				editor.modes['default'].queryState(val) ? B.addClass(elem, "active"):B.removeClass(elem, "active");
			}catch(e){
				//alert('queryState' + val + '不可用');
			}
		};
		editor.updateListeners.push(this.updateUI);
		
		var self = this;
		elem.unselectable = "on"; // IE, prevent focus
		
		B.addEvent(elem, "mousedown", function(evt) {
			editor.saveRng();
			var mode = editor.modes[editor.currentMode];
			if(editor.currentMode!='default'){
				mode[command](val);
			}else{
				if((val=="JustifyCenter"&&mode.queryState("JustifyCenter")==true)||(val=="JustifyRight"&&mode.queryState("JustifyRight")==true)){
					mode[command]("JustifyNone");
				}else{
					mode[command](val);
				}
			}
			//editor.restoreRng();
			evt.preventDefault();
			editor.updateToolbar();
		});
		B.addEvent(elem, 'click', block);
	}
	function ValueSelectorController(command, val, elem, editor) {
		var self = this, ul = B.$('ul',elem), span =B.$('span', elem);
		this.updateUI = function() {
			if (editor.currentMode != 'default') return;
			var value = editor.modes['default'].queryValue(val);
			if ( /^\d+px$/.test(value) ){
				value = ARR_FONT_SIZE.indexOf(parseInt(value))+1;
			}
			/*var defaultText;
			//实在是没有办法
			if(val == 'FontSize'){
				defaultText = B.toolbarCommands.sizeSelector[5].defaultText;
			} else if(val == 'FontName'){
				defaultText = B.toolbarCommands.fontSelector[5].defaultText;
			}*/
			span.innerHTML = value || '&nbsp;';
		}
		editor.updateListeners.push(this.updateUI);
		
		elem.unselectable = "on"; // IE, prevent focus
		function hide(){
			B.css(ul,  'display', 'none');
			B.removeEvent(document, 'mouseover', hide);
		}
		B.addEvent(ul, 'click', function(evt){
			if(evt.target.tagName == 'LI'){
				//editor.saveRng();
				var li = evt.target,
					mode = editor.modes[editor.currentMode];
				mode[command](val, li.innerHTML);
				hide();
			}
			evt.stopPropagation();
			//editor.doc.body.focus();
			return false;
		});	
		B.addEvent(elem, 'mousedown', function(evt) {
			editor.saveRng();
			var ul = B.$('ul',elem),
				value =B.$('span', elem).innerHTML,
				lis = B.$$('li', ul),
				node = null;
			for(var i=0, l=lis.length; i<l; i++){
				if(lis[i] == value){
					node = lis[i];
					break;
				}
			}
			/*var nodes = B.$$('li', ul).filter(function(n){
					return n.innerHTML == value;
				}),
				node = nodes.length ? nodes[0] : null;*/
			act = B.$('li.active', ul);
			if(node != act) {
				B.addClass(node, 'active');
				act && B.removeClass(act, 'active');
			}
			B.css(ul,  'display', 'block');
			B.addEvent(document, 'mouseover', hide);
			editor.restoreRng();
			evt.halt();
		});
		B.addEvent(elem, 'mouseover', function(evt){
			evt.stopPropagation();
		});
	}
	function ColorSelectorController(command, val, elem,editor){
		var self = this, div = B.$('div',elem), dropdown =B.$('em', elem), span = B.$('span', elem);
		elem.unselectable = "on"; // IE, prevent focus
		B.addEvent(div, 'mousedown', function(evt){
			editor.saveRng();
			var color = B.formatColor(B.getComputedStyle(span).backgroundColor),
			mode = editor.modes[editor.currentMode];
			mode[command](val, color);
			evt.preventDefault();
			//editor.restoreRng();
			//B.UA.ie || setTimeout(function(){editor.focus();}, 100);
		});

		//获得按钮
		function getColor(){
			var originColor = B.getComputedStyle(span).backgroundColor;
			B.util.colorPicker(elem, originColor, function(color){
				span.style.backgroundColor = color;
				var mode = editor.modes[editor.currentMode];
				//mode.restoreRng();
				mode[command](val, B.formatColor(color));
				//B.UA.ie || setTimeout(function(){editor.focus();}, 100);
			});
		}
		B.addEvent(dropdown, 'mousedown', function(evt){
			closeAll();
			editor.saveRng();
			B.require('util.colorPicker', getColor);
			evt.preventDefault();
			//editor.restoreRng();
			//B.UA.ie || setTimeout(function(){editor.focus();}, 100);
		});
		B.addEvent(elem, 'click', block);
	}
	function InsertCommandController(command, val, elem, editor){
		B.addEvent(elem, 'mousedown', function(evt){
			closeAll();
			editor.saveRng();
			var mode = editor.modes[editor.currentMode];
			B.require('editor.'+val, function(){
				var txt = editor.getSelText();
				if(txt == '<p></p>'){
					txt = '';
				}
				B.editor[val](elem, function(str){
					mode.pasteHTML(str);
				}, txt, '');
				editor.restoreRng();
			});
			evt.preventDefault();
		});
		B.addEvent(elem, 'click', block);
	}
	//插件方式
	function PluginCommandController(command, val, elem, editor){
		B.addEvent(elem, 'mousedown', function(evt){
			//console.log(elem.parentNode.parentNode.parentNode.nextSibling.nextSibling);
			//editor.textarea=elem.parentNode.parentNode.parentNode.nextSibling.nextSibling;
			closeAll();
			editor.saveRng();
			var mode = editor.modes[editor.currentMode];
			var callback = function(str){
				/*editor.doc.body.focus();editor.focus();*/
				editor.pasteHTML(str);
			};
			
			B.require('app.'+val, function(){
				editor.restoreRng();
				if (!B.app[val]){
					alert(val+'不存在');
					return;
				}
				B.app[val](elem, callback, editor);
			});
			evt.preventDefault();
		});
		B.addEvent(elem, 'click', block);
		
	}
	/**
	 * 工具栏模块
	 */
	B.toolbarCommands = {
		boldIcon: ['Bold', '粗体', iconUI, ToggleCommandController, 'command'],
		italicIcon: ['Italic', '斜体', iconUI, ToggleCommandController, 'command'],
		underlineIcon: ['Underline', '下划线', iconUI, ToggleCommandController, 'command'],
		strikethroughIcon: ['Strikethrough', '删除线', iconUI, ToggleCommandController, 'command'],
		removeformat: ['RemoveFormat', '清除样式', iconUI, CommandController, 'command'],
		leftIcon: ['JustifyLeft', '左对齐',  iconUI, ToggleCommandController, 'command'],
		rightIcon: ['JustifyRight', '右对齐', iconUI, ToggleCommandController, 'command'],
		centerIcon: ['JustifyCenter', '居中对齐', iconUI, ToggleCommandController, 'command'],
		fullIcon: ['JustifyFull', '两端对齐', iconUI, ToggleCommandController, 'command'],
		imageIcon:  ['Image', '图片', iconUI, InsertCommandController, 'insertCommand'],
		foreColor:  ['Forecolor', '文字颜色', colorUI, ColorSelectorController, 'valueCommand', '#FF0000'],
		backColor: [B.UA.ie ? 'Backcolor' : 'hilitecolor', '背景色',  colorUI, ColorSelectorController, 'valueCommand', '#FFFF00'],
		olIcon: ['InsertOrderedList', '编号',    iconUI, CommandController, 'command'],
		ulIcon: ['InsertUnorderedList', '项目符号',    iconUI, CommandController, 'command'],
		indentIcon: ['Indent', '缩进',    iconUI, CommandController, 'wrapCommand'],
		outdentIcon: ['Outdent', '取消缩进',    iconUI, CommandController, 'command'],
		hrIcon: ['Inserthorizontalrule',      '分隔线',  iconUI, CommandController, 'command'],
		quoteIcon: ['blockquote',    '插入引用',    iconUI, InsertCommandController, 'insertCommand'],
		codeIcon: ['code',           '插入代码',    iconUI, InsertCommandController, 'insertCommand'],
		linkIcon: ['createLink',    '插入链接',  iconUI, InsertCommandController, 'command'],
		unlinkIcon: ['Unlink',        '取消链接', iconUI, CommandController, 'command'],
		tableIcon: ['inserttable',   '插入表格',    iconUI,  InsertCommandController, 'insertCommand'],
		faceBtn: ['emotional', '表情', buttonUI, PluginCommandController, 'insertCommand'],
		photoBtn: ['insertImage', '图片', buttonUI, PluginCommandController, 'insertCommand'],
		fileBtn: ['insertAttach', '附件', buttonUI, PluginCommandController, 'insertCommand'],
		videoBtn: ['insertvideo', '视频', buttonUI, InsertCommandController, 'insertCommand'],
		musicBtn: ['insertmusic', '音乐', buttonUI, InsertCommandController, 'insertCommand'],
		sellIcon: ['sell', '插入出售', iconUI, PluginCommandController, 'insertCommand'],
		postIcon: ['post', '插入隐藏', iconUI, PluginCommandController, 'insertCommand'],
		pwcodeIcon: ['pwcode', '自定义代码', iconUI, PluginCommandController, 'insertCommand'],
		setformIcon: ['setform', '帖子格式', iconUI, PluginCommandController, 'insertCommand'],
		undoIcon: ['undo', '撤销', iconUI, CommandController, 'command'],
		redoIcon: ['redo', '恢复', iconUI, CommandController, 'command'],
		pgformatIcon: ['PgFormat', '段落化', iconUI, CommandController, 'command'],
		fontSelector: ['FontName',      '字体',    selectorUI, ValueSelectorController, 'valueCommand', 
			{
				style: 'fontFamily',
				width: 100,
				list: {
					"宋体":	'宋体',
					"新宋体":	'新宋体',
					"楷体_GB2312":	'楷体_GB2312',
					"黑体":	'黑体',
					"微软雅黑": '微软雅黑',
					"Arial":	   'arial,helvetica,sans-serif',
					"Courier New":	   'courier new,courier,monospace',
					"Georgia":	   'georgia,times new roman,times,serif',
					"Tahoma":	   'tahoma,arial,helvetica,sans-serif',
					"Times New Roman": 'times new roman,times,serif',
					"Verdana":	   'verdana,arial,helvetica,sans-serif',
					"impact":	   'impact'
				},
				defaultText: 'Arial'
		}],
		sizeSelector: ['FontSize', '字号', selectorUI, ValueSelectorController, 'valueCommand',
			{width: 30, list: {
				'1': 1, '2': 2, '3' :3, '4': 4, '5':5, '6':6, '7':7
			},defaultText: 2}
		],
		br: [null, null, brUI],
		musicIcon: ['music', '虾米音乐', iconUI, PluginCommandController, 'insertCommand'],
		magicIcon: ['magic', '动漫传情', iconUI, PluginCommandController, 'insertCommand'],
		pageIcon: ['pagecut', '分页符', iconUI, PluginCommandController, 'insertCommand'],
		remotePid: ['remotepic', '远程图片下载', iconUI, PluginCommandController, '']
	};	
	/**
	 * 编辑器模块
	 */
	function Editor(textarea, toolbar, mini, conf){
		if ( !(this instanceof Editor) ){
			return new Editor(textarea, toolbar, mini, conf);
		}
		this.updateListeners = [];
		this.saveSec = 10;
		this._interval;	//自动保存
		this.currentMode = 'default';
		this.isFullScreen = false;
		this.isSaveMode = false;//保存当前编辑模式
		this.isQuickPost = true;//Ctrl + Enter 快速发布

		this.textarea = textarea;
		this.div = null;

		this.createToolBar = function(toolbar, mini) {//生成工具栏
			var toolbarEl = B.createElement('ul'),
				miniIndex = ' ' + mini + ' ',
				self = this;
			toolbar.forEach(function(group){
				var groupEl = B.createElement('li');
				group.split(' ').forEach(function(t){
					try {
						var binding = B.toolbarCommands[t], 
						uimaker = binding[2];
						el = uimaker(t, groupEl, binding[1]);
						
						//判断是否在mini中
						if (miniIndex.indexOf(' '+t+' ') > -1){
							el.style.display = 'block';
						}
						self.commandBinding(el, binding);
					} catch(e) {
						alert('找不到组件:'+t);
					}
				});
				toolbarEl.appendChild(groupEl);
			});
			return toolbarEl;
		}
		this.initToolBar = function(toolbar) {
			var toolbar = B.$('.' + toolbar,this.textarea.parentNode);//ID为改class，为了考虑多编辑器的存在
			var toolbarEl = toolbar.childNodes;
			for (var i = 0 ,len = toolbarEl.length; i < len; i++) {
				var t = toolbarEl[i];
				if (t.nodeType === 1) {
					var commandName = t.getAttribute('data-type').substr(3);
					this.commandBinding(t, B.toolbarCommands[commandName]);
				}
			}
			this.area = toolbar.parentNode.parentNode.parentNode;
		}
		this.commandBinding = function(el, binding) {
			if (binding.length > 3){
				var ControllerConstructor = binding[3], command = binding[4];
				new ControllerConstructor(command, binding[0], el, this);
			}
		}
		this.ititEditor = function(toolbar, mini) {
			if (!B.isArray(toolbar)) {
				this.initToolBar(toolbar);
				return;
			}
			var toolbarEl = this.createToolBar(toolbar, mini);
			var tar = B.createElement('<div class="B_tar"></div>'),
				p = B.createElement('<p class="B_cc"></p>'),
				scr = B.createElement('<a href="javascript:;" unselectable="on" class="B_fullAll" hidefocus="true"><img src="js/breeze/editor/style/full.png" title="全屏"></a>');
			scr.unselectable = 'on';
			B.addEvent( scr, 'click', self.toggleFullScreen.bind(self) );
			p.appendChild(scr);
		
			scr = B.createElement('<a href="javascript:;" onclick="return false;" class="B_simple">简单</a>');
			scr.unselectable = 'on';
			B.addEvent( scr, 'mousedown', self.toggleToolBar.bind(self) );
			B.addEvent( scr, 'click', block);
			p.appendChild(scr);
			tar.appendChild(p);
		
			var tbContainer = B.createElement('<div class="' + PRE + 'editor_toolbar"></div>');
			tbContainer.appendChild(tar);
			tbContainer.appendChild(toolbarEl);

			this.area = B.createElement('<div class="'+PRE+'editor"></div>');
			this.area.appendChild(tbContainer);
			B.insertBefore(this.area, this.textarea);
			this.area.appendChild(this.textarea);
			B.css(this.textarea,{'height': textareaHeight+'px','width':'100%','border':'none','overflow':'auto','margin':'0','padding':'0'});
		}
		this.createFootToolBar = function() {
			//下面的拖动
			var foot = B.createElement('<div class="B_editor_buttom">\
				<div class="B_fr"><div class="B_flex"></div></div>\
				<div class="B_fr mr5"><a class="B_restoreHandle" href="javascript://">恢复数据</a>\
					<a id="newdraft" href="javascript://">草稿箱</a>\
					<a class="B_checkBtn" href="javascript://">字数检查</a></div>\
				<span class="B_saveText"><i class="B_saved">已保存</i><i class="B_saving">'+this.saveSec+'秒保存一次</i></span>\
			</div>');
			this.area.appendChild(foot);

			//恢复数据
			B.query('.B_restoreHandle', foot).addEvent('click', self.restore.bind(self));
			B.query('#newdraft', foot).addEvent('click', function(){
				self.saveRng();
				return opendraft(this.id);
			});

			//字数检查
			var checkbtn = B.$('.B_checkBtn', this.area);
			B.addEvent(checkbtn, 'click', function(e){
				B.require('util.dialog', function(B){
					var txt = self.getUBB();
					if (B.trim(txt) == '') txt = '';
					B.util.dialog({
						id: 'wordcount',
						reuse: false,
						data: '<div style="background:#FFF;border:1px solid #ccc;padding:5px 10px;">已写'+txt.length+'字</div>',
						pos: ['rightAlign', 'top'],
						callback: function(popup){
							setTimeout(function(){popup.closep()}, 2000);
						}
					}, checkbtn);
					return false;
				});
				e.preventDefault();
			});

			var handle = B.$('.B_flex', foot);
			B.addEvent(handle, 'mouseover', function() {
				var container = self.modes[self.currentMode].container,
					otherMode = self.currentMode == 'default' ? 'UBB' : 'default';
				var hidecontainer = typeof self.modes[otherMode] != 'undefined' ? self.modes[otherMode].container : null;
				B.require('util.resizable', function() {
					B.util.resizable({
						obj:container,
						handle:handle,
						onlyY:true,
						ondrag:function() {
							if (hidecontainer) {
								var height = B.height(container) || parseInt(container.style.height) || parseInt(container.height);
								B.css(hidecontainer,'height',height+'px');//拖动大小时隐藏的textarea大小也要变
							}
						}
					});
				});
			});
		}
		this.setAutoSave = function(elem) {
			var save_bar=B.$('.B_saveText');
			B.addEvent(elem, 'keyup', function(){
				self.currentSec = self.saveSec;
				var classname=save_bar.className;
				if(classname.indexOf("B_saving")<0){
					save_bar.className = 'B_saveText B_saving';
				}
				if (!self._interval){
					self._interval = setInterval(function(){
						self.currentSec--;
						if(self.currentSec == 0){
							clearInterval(self._interval);
							self._interval = null;
							self.save.call(self);
							save_bar.className = 'B_saveText B_saved';
						}
					}, 1000);
				}
			});
		}

		var self = this,
			textareaHeight = B.height(textarea) || 300;
		this.ititEditor(toolbar, mini);
		this.createFootToolBar();
		this.modes = {'default': DefaultMode};
		this.plugins.forEach(function(fn){
			fn.call(self);
		});
		if (typeof conf == 'object') {
			for (var i in conf) this[i] = conf[i];
		}
		this.init();

		//不建议的代码
		if (textarea.form) {
			var f = textarea.form;
			var funcref = f.onsubmit;
			f.onsubmit = function() {
				self.save();
				if (typeof funcref == "function") {
					return funcref();
				}
				return true;
			};
		}
	}
	Editor.prototype = {
		plugins: [],
		init: function(){
			if (typeof this.modes[this.currentMode] == 'function') {
				var mode = this.modes[this.currentMode];
				this.modes[this.currentMode] = new mode(this);
				var self = this;
				if (this.isQuickPost) {
					
					B.addEvent(this.modes[this.currentMode].editContainer, 'keydown', function(e) {
						var keyDownCode = (typeof e.which != 'undefined') ? e.which : e.keyCode;
						if ((e.ctrlKey && keyDownCode == 13) || (e.altKey && keyDownCode == 83)) {
							try{self.textarea.form.Submit.click();}catch(e){}
						}
					});
				}
				if(this.allowAt&&this.currentMode=="default"){
					B.require("app.at",function(){
						var At=new B.app.at(self.modes[self.currentMode].iframe,self.allowAtLen||0);
						At.init();
						
					})
				}
				
			}
		},
		focus: function(){
			return this.modes[this.currentMode].focus();
		},
		//
		saveRng: function(){
			return this.modes[this.currentMode].saveRng();
		},
		clearRng: function(){
			return this.modes[this.currentMode].clearRng();
		},
		restoreRng: function(){
			var self = this;
			setTimeout(function(){
				self.modes[self.currentMode].restoreRng();
			}, 0);
		},
		//侦听
		updateToolbar: function(e){
			this.updateListeners.map(function(updateUI){
				updateUI();
			});
		},
		//获取HTML
		getHTML: function(){
			return this.modes[this.currentMode].getHTML();
			//return this.modes['default'].formatXHTML(this.doc.body.innerHTML);
		},
		setHTML: function(sHtml){
			this.modes[this.currentMode].setHTML(sHtml);
		},
		getRng: function(){
			return this.modes[this.currentMode].getRng();
		},
		getSelText: function(){
			return this.modes[this.currentMode].getSelText();
		},
		isSel: function(){
			return this.modes[this.currentMode].isSel();
		},
		pasteHTML: function(str){
			return this.modes[this.currentMode].pasteHTML(str);
		},
		//全屏切换
		toggleFullScreen: function(){
		    var cbody = document.body, docEL = document.documentElement,
		        viewportWidth = B.UA.ie ? docEL.clientWidth || cbody.clientWidth : window.innerWidth,
		        viewportHeight = B.UA.ie ? docEL.clientHeight || cbody.clientHeight : window.innerHeight,
		        toolbar = B.$('.B_editor_toolbar', this.area),
		        buttom = B.$('.B_editor_buttom', this.area),
		        divHeight = viewportHeight - (B.height(toolbar) + 23),//B.height(buttom)计算出来为0,height不准确,需检查
		        flex = B.$('.B_flex',this.area);
		        B.css(toolbar,{'width':'100%','position':''});//受toolbar功能影响,需要回复原位,要不然可能是fiexd状态
			if(this.isFullScreen){
				B.css(this.area, {
					position: '',
					width: '100%',
					height: '100%'
				});
				B.css(this.div, {
					width: '100%',
					height: '300px'
				});
				cbody.style.overflow = '';
				docEL.style.overflow = "";
				flex.style.display = '';
				this.textarea.style.height = '';
				window.scrollTo(this.scrollLeft, this.scrollTop);//还原大小时还原页面位置
				this.isFullScreen = false;
			}else{
			    this.scrollLeft = docEL.scrollLeft || cbody.scrllLeft,
		        this.scrollTop = docEL.scrollTop || cbody.scrollTop;
		        cbody.style.overflow = 'hidden';
				docEL.style.overflow = "hidden";
				flex.style.display = 'none';//全屏时不允许resize
				B.css(this.area, {
					position:'absolute',
					width:viewportWidth + 'px',
					height:viewportHeight + 'px',
					top:0,
					left:0,
					zIndex:999
				});
				window.scrollTo(0, 0);
				this.textarea.style.height = divHeight + 'px';//textarea同样高
				B.css(this.div, {
					width: '100%',
					height: divHeight + 'px'
				});
				this.isFullScreen = true;
			}
			var fullScreen = B.$('.B_fullAll', this.area);
			if(fullScreen){
				fullScreen.innerHTML = this.isFullScreen ? '<img src="js/breeze/editor/style/unfull.png" title="返回">': '<img src="js/breeze/editor/style/full.png" title="全屏">';
			}
			if (this.modes['default']) this.modes['default'].setEditable();
			window.event && (event.returnValue = false);
		},
		//简单切换
		toggleToolBar: function(){
			var toolbar = B.$('.B_editor_toolbar', this.area),
				btn = B.$('.B_simple', this.area);
			
			if(B.hasClass(toolbar, 'B_editor_minitoolbar')){
				B.removeClass(toolbar, 'B_editor_minitoolbar');
				btn.innerHTML = '简单';
			} else {
				B.addClass(toolbar, 'B_editor_minitoolbar');
				btn.innerHTML = '高级';
			}
		},
		saveMode: function(){
			this.isSaveMode && SetCookie('editmode', this.currentMode == 'default' ? 0 : 1);
		},
		//本地存储
		save: function(){
			if(this.getUBB){
				var textval = this.getUBB();
				if(this.currentMode == 'default') {
					this.textarea.value = this.isUBB ? textval : this.getUBBFromHtml();
				}
				if(textval.replace(/\s+/g,'')!=''){
					B.require('util.localStorage', function(){
						B.util.localStorage.set('msg', textval);
					});
				}
			}
		},
		//本地恢复
		restore: function(e){
			var self = this;
			B.require('util.localStorage', function(){
				self.textarea.value = B.util.localStorage.get('msg')||'';
				if (self.currentMode == 'default' && self.getSavedHTML) {
					self.setHTML(self.getSavedHTML() || '');
				}
			});
			e.preventDefault();
		},
		quickpost: function(){
		},
		reset: function() {
			this.setHTML('');
			if(this._interval){
				clearInterval(this._interval);
				this._interval=null;
				//B.query('.B_saveText').html(' ');
			}
			if (typeof uploader != 'undefined') {
				uploader.reset();
			}
		}
	};
	B.editor = Editor;
});
});