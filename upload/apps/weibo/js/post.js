var weibo_post = function(json) {
	this.init(json);
}
weibo_post.prototype = {
	init 		: function (j) {
		this.form		= j.form;
		this.content	= j.content;
		this.smile		= j.smile;
		this.url		= j.url;
		this.photo		= j.photo;
		
		this.wordLimit = 255;
		this.postUrl = 'apps.php?q=weibo&do=post&ajax=1';
		this.nextdo		= j.nextdo;
		this.weibotip		= j.weibotip,
		this.dvalue 	= this.$(this.content).value;//后台设置的默认文字
		var _			 = this;
		var saveRng;

		if(_.IsElement(j.content)) {
			_.$(j.content).onkeyup = function()
			{
				_.checkWordLength();return false;
			};
		}
		
		if (_.IsElement(j.smile)) {
			_.$(j.smile).onclick = function()
			{
				_.showSmile();return false;
			};
		}
		
		if (_.IsElement(j.url)) {
			_.$(j.url).onclick = function()
			{
				
				//colose.log('urlInput');
				_.showUrl();return false;
			};
			
		
			_.$('sumbitUrl').onclick = function()
			{
				
				//colose.log('urlInput');
				_.applyUrl();return false;
			};
			
			var urlContainerObj = _.$('urlContainer');
			urlContainerObj.getElementsByTagName('a')[0].onclick = function() {
				_.displayElement('urlContainer', false);
			};
			
			_.$(j.url).onmousedown = function() {
				var content = _.$(_.content);
				//if(content.value === defaultTopic || content.value === _.dvalue){content.value='';}
				if(document.selection){
					_.saveRng = document.selection.createRange();
					if(_.saveRng.parentElement().tagName != 'TEXTAREA')
					{
						saveRng=null;
					}
				}
			}
		}
		
		if (_.IsElement('uploadPic')) {
			_.$('uploadPic').onmousedown = function() {
				var content = _.$(_.content);
				if(content.value === defaultTopic || content.value === _.dvalue){content.value='';}
				if(document.selection){
					saveRng = document.selection.createRange();
					if(saveRng.parentElement().tagName != 'TEXTAREA')
					{
						saveRng=null;
					}
				}
				_.tabChange('uploadPic');
			}
		}
		
		if (_.IsElement('writePic')) {
			_.$('writePic').onchange = function() {
				_.uploadPic();return false;
			}
		}

		if (_.IsElement('submitSuccess')) {
			_.$('submitSuccess').onclick = function() {
				_.displayElement('submitSuccess', false);return false;
			}
		}
		
		if (_.IsElement('weibo_submit')) {
			_.$('weibo_submit').onclick = function() {
				_.post();return false;
			}
		}
	},
	
	$ : function(objId){
		return document.getElementById(objId);
	},
	
	IsElement : function(id) {
		return this.$(id) != null ? true : false;
	},
	displayElement : function(elementId, isDisplay) {
		if (undefined == isDisplay) {
			this.$(elementId).style.display = this.$(elementId).style.display == 'none' ? '' : 'none';
		} else {
			this.$(elementId).style.display = isDisplay ? '' : 'none';
		}
	},
	
	shockWarning : function() {
		var elementObj = getObj(this.content);
		var step = 4;
		var itv = setInterval(function(){
//			console.log(step);
			elementObj.style.backgroundColor = (step%2) ? '' : '#ffdddd';
			step--;
			if (step == 0) {
				clearInterval(itv);
				elementObj.focus();
			}
		}, 300);
	},
	
	checkWordLength : function() {
		var thisobj = getObj(this.content);
		var value = '';
		var len = strlen(thisobj.value);
		var showobj = this.$(thisobj.id+'limit');
		if(len > this.wordLimit) {
			value = '已超出<em>'+(len - this.wordLimit)+'</em>个字节';
		} else {
			value = '<em>'+len+'/255</em>';
		}
		if(showobj != null) {
			showobj.innerHTML = value;
			if(this.wordLimit - len > 0) {
				showobj.parentNode.getElementsByTagName('em')[0].className = "";
			} else {
				showobj.parentNode.getElementsByTagName('em')[0].className = "s1";	
			}
		}
	},
	
	showSmile : function(e) {
		/*e = e||event;
		stopPropagation(e)*/
		var thisobj = this.$(this.smile);
		var content = this.$(this.content);
		var _ = this;
		_.applySmile(function(codeText) {
			insertContentToTextArea(content, codeText);
			_.checkWordLength();
		});
		var len = defaultTopic.length;
		var pos = content.value.indexOf(defaultTopic);
		if (is_ie && pos >= 0) {
			var range = content.createTextRange();
			range.moveStart('character',-content.value.length);
			range.moveEnd('character',-content.value.length);
			range.collapse(true);
			range.moveStart('character',pos+1);
			range.moveEnd('character',len-2);
			range.select();
		} else {content.focus()};
		var rect = thisobj.getBoundingClientRect();
		this.$('smileContainer').style.left = rect.left+ietruebody().scrollLeft + 'px';
		this.$('smileContainer').style.top = rect.top + ietruebody().scrollTop + 22 +'px';
		this.tabChange(this.smile);
		document.body.onmousedown=hideSmile;
	},
	
	applySmile : function(addSmileCallback) {
		var _ = this;
		smileContainerObj = this.$('smileContainer');
		var smiles = smileContainerObj.getElementsByTagName('a');
		if (smiles.length) {
			smiles[0].onclick = function() {displayElement('smileContainer', false);};
			for (i=1; i<smiles.length; i++) {
				smiles[i].onclick = function() {
				if(_.$(_.content).value==_.dvalue){
					_.$(_.content).value = '';
				}
				var codeText = '[s:' + this.title + ']';
					if (content.value === defaultTopic) setSelection();
					addSmileCallback(codeText);
					hideSmile();
				}
			}
		}
	},
	
	showUrl : function() {
		var thisobj = this.$(this.url);
		var _ = this;
		this.$('urlInput').value = "http://";
		var content = _.$(_.content);
		this.tabChange(this.url);
		var rect = thisobj.getBoundingClientRect();
		this.$('urlContainer').style.left = rect.left+ietruebody().scrollLeft + 'px';
		this.$('urlContainer').style.top = rect.top + ietruebody().scrollTop + 22 +'px';
		if(content.value === _.dvalue){content.value='';}
		if (document.selection) {
			this.$('urlInput').select();
			var sel = document.selection.createRange();
			sel.moveStart('character',sel.text.length);
			sel.select();
			delete sel;
		}else{
			this.$('urlInput').focus();
		}
	},

	applyUrl : function(addUrlCallback) {
		var content = this.$(this.content);	
		var text = this.$('urlInput');
		var codeText = text.value + ' ';
		var preg = /^http\:\/\/.{4,255}$/;
		if (!preg.test(codeText)) {
			showDialog('error','链接地址出错，链接地址必须以:http://开头');
			return false;
		}
		if (content.value === defaultTopic) setSelection();
		this.saveRng && this.saveRng.select();
		insertContentToTextArea(content, codeText);
		this.displayElement('urlContainer', false);
		if(content.value === this.dvalue){content.value='';}
		this.checkWordLength();
	},

	uploadPic : function() {
		var saveRng;
		var thisform = this.$('uploadPicForm');
		var content = this.$(this.content);
		if(content.value === this.dvalue){content.value='';}
		var _ = this;
		content.style.height='18px';			
		this.displayElement('uploadPicDiv', true);
		this.displayElement('uploadPicload',false);
		this.displayElement('uploadPicLoadding',true);
		
		ajax.submit(thisform, function() {
			var gotText = ajax.request.responseText;
			var textSplit = gotText.split('\t');
			_.displayElement('uploadPicLoadding',false);
			_.displayElement('uploadPicload',true);
			if ('success' == textSplit[0]) {
				var div = document.createElement('div');
				div.className = "fl";
				textSplit[1] = _.formatPicName(textSplit[1], '.', 10);
				div.innerHTML = '<span class="fl mr10"><span class="fl mr5">'+textSplit[1]+'</span><input type="hidden" name="uploadPic[]" value="'+textSplit[2]+'" /><a class="adel" style="float:left;" href="javascript:;" onclick="clearPic(this,'+textSplit[2]+')">删除</a></span>';
				div.setAttribute('thumb', textSplit[4]);
				div.onmouseover = previewThumb;
				div.onmouseout  = previewClose;
				_.$('uploadPicDiv').appendChild(div);
				
				var picNum = _.getPicNum();		
				if (picNum >= 4 ) {
					_.displayElement('uploadPicload',false);
					_.displayElement('uploadPicLoadding',true);
					_.$('uploadPicLoadding').innerHTML = '';
					return false;
				}
			} else {
				showDialog("error", gotText);
			}
		});
	},

	tabChange	: function (type) {
		if ('weibo_url' == type) {
			this.displayElement('urlContainer', true);
			this.displayElement('smileContainer', false);
		} else if ('weibo_smile' == type) {
			this.displayElement('smileContainer', true);
			this.displayElement('urlContainer', false);
		} else {
			this.displayElement('smileContainer', false);
			this.displayElement('urlContainer', false);
		}
	},
	
	getPicNum : function() {
		return this.$('uploadPicDiv').getElementsByTagName('div').length;	
	},
	
	formatPicName : function(str,search,length) {
		var extPosition = str.lastIndexOf(search);
		var ext = str.substring(extPosition);
		var strname = str.substring(0,extPosition);
		if (strname.length > length) {
			strname = strname.substring(0,length-3)+'...'+ext;
		} else {
			strname = str
		}
		return strname;
	},
	
	post : function() {
		var _this = this;
		var thisForm = _this.$(_this.form),
			content = _this.$(_this.content),
			weibo_submit = _this.$('weibo_submit');
		
		var nextdo = _this.nextdo;
		var weibotip = _this.weibotip;
		content.value = content.value.replace(/(^\\s*)|(\\s*$)/g, "");
		var picNum = _this.getPicNum();
		if (picNum && (content.value === '' || content.value === _this.dvalue)) {
			content.value = '分享图片';
		}
		
		if (content.value == '' || strlen(content.value) >_this.wordLimit || content.value === _this.dvalue || content.value === defaultTopic) return _this.shockWarning();
		weibo_submit.disabled = false;
		ajax.send(_this.postUrl, thisForm, function() {
			var gotText = ajax.request.responseText;
			if ('发表成功!\treload' != gotText) {
				showDialog("error", gotText);
			} else {
				weibo_submit.removeAttribute('disabled')
				_this.displayElement('submitSuccess', true);
				_this.displayElement('uploadPicload', true);
				_this.displayElement('uploadPicDiv', false);
				_this.displayElement('uploadPicLoadding', false);
				_this.$('uploadPicLoadding').innerHTML = '正在上传中.......';
				_this.$('uploadPicDiv').innerHTML = '';
				_this.$(_this.content).style.height='';
				_this.$(_this.content).value = '';
				_this.$(_this.content).style.color='rgb(136, 136, 136)';
				setTimeout(function(){_this.displayElement('submitSuccess', false);_this.$(_this.content).value = weibotip;}, 2000);
				if (nextdo == 'filterweibo') filterCheckAll();
				getWeiboList(nextdo, 1, 'weiboFeed');
				_this.checkWordLength();
				getObj('writePic').value = '';
			}
		});
	}
}

function clearPic(e,pid) {
		ajax.send('apps.php?q=photos&a=delphoto&pid='+pid,'',function() {
			var rText = ajax.request.responseText.split('\t');
			if (rText[0] == 'ok') {
			//var val = e.previousSibling.value;
			/*
			var val = '[upload=' + pid + ']';//modify by chenjm
			var writeC = getObj('writeContent');
			writeC.value = writeC.value.replace(val,'');
			*/
				var node = e.parentNode.parentNode;
				node.parentNode.removeChild(node);			
				var picNum = getObj('uploadPicDiv').getElementsByTagName('div').length;	;
				if (picNum < 1 ) {
					displayElement('uploadPicDiv', false);
					getObj('weibo_content').style.height='';
				} else if(picNum <= 4 && picNum >= 1) {
					displayElement('uploadPicload',true);
					displayElement('uploadPicLoadding',false);
				}			
			//wordlength(writeC,255);
				previewClose();
				getObj('writePic').value = '';
				
		} else {
			ajax.guide();
		}
	});
	return false;
}	

function previewThumb(e) {
	e = e||event;
	var target;
	if(e.currentTarget){
		target = e.currentTarget;
	}else{
		target = e.srcElement;
		while(!target.getAttribute('thumb'))
		target = target.parentNode;
	}
	var url = target.getAttribute('thumb');
	var imgPreview = getObj('imgPreview');
	imgPreview.style.left=target.getBoundingClientRect().left+ietruebody().scrollLeft+'px';
	imgPreview.src = url;
	imgPreview.style.display='';
}

function previewClose() {
	displayElement('imgPreview',false);
}

function filterCheckAll() {
	var objform = getObj('filterWeiboForm');
	if (objform == null) return;
	for (var i = 0; i < objform.elements.length; i++) {
		if(objform.elements[i].name.match('filter')) {
			objform.elements[i].checked = true
		}
	}
}

function hideSmile()
{
	document.body.onmousedown='';
	getObj('smileContainer').style.display='none';
}