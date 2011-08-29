
function PwTags(){
	this.tagdb	= null;
	this.obj	= null;
	this.input	= null;
	this.hide	= true;
	this.menu	= null;
}

PwTags.prototype = {

	init : function(){
		if(tag.obj == null){
			ajax.send('pw_ajax.php','action=tag',function(){tag.create(1);});
		} else{
			tag.show();
		}
	},
	
	create : function(type){
		tag.tagdb = new Array();
		var s = ajax.request.responseText.split("\t");
		for(var i=0;i<s.length;i++){
			if(s[i]){
				var r = s[i].split(',');
				tag.tagdb[i] = [r[0],r[1]];
			}
		}
		tag.obj	= document.createElement("div");
		tag.obj.onmouseover = function(){tag.hide = false;};
		tag.obj.onmouseout  = function(){tag.hide = true;};
		tag.obj.style.cssText = 'border:1px solid #fcefbb;background:#fffff3;width:317px;display:none;position:absolute;';
		tag.input = getObj('atc_tags');
		tag.input.onkeyup = function(){tag.show();};
		tag.input.onblur  = tag.close;
		tag.input.parentNode.appendChild(tag.obj);
		if(type == 1){
			tag.tagmenu();
		} else{
			tag.get();
		}
	},

	tagmenu : function(){
		tag.menu = getObj('tagmenu');
		var html = '';
		for(var i=0;i<tag.tagdb.length;i++){
			var s = tag.tagdb[i];
			html += '<a href="javascript:;" class="gray" onclick="tag.addtag(this);return false;" style="cursor:pointer;margin:5px;">'+s[0]+'</a>';
			//if(i>98) break;
		}
		tag.menu.lastChild.innerHTML = html;
		tag.menu.style.display = '';
	},

	show : function(){
		if(tag.menu == null){
			tag.tagmenu();
		}
		var str = tag.input.value;
		str = str.replace(/^\s+/g,'');
		var pos = str.lastIndexOf(' ') + 1;
		str = str.substr(pos,str.length);

		if(str == ''){
			tag.obj.style.display = 'none';
			return;
		}
		var html = '';
		var num  = 0;
		for(var i=0;i<tag.tagdb.length;i++){
			var s = tag.tagdb[i];
			if(s[0].indexOf(str)==0){
				html += '<div onmouseover="this.style.background=\'#fcefbb\'" onmouseout="this.style.background=\'\'" onclick="tag.insert(this);" class="pd5"><span class="fr gray f10">(' + s[1] + ')</span><span>' + s[0] + '</span></div>';
				if(++num>9) break;
			}
		}
		if(html==''){
			tag.obj.style.display = 'none';
			return;
		}
		var o = getObj("atc_tags");
		var left  = o.getBoundingClientRect().left-getObj('pw_box').getBoundingClientRect().left+ietruebody().scrollLeft;
		var top   = o.getBoundingClientRect().top-getObj('pw_box').getBoundingClientRect().top+ietruebody().scrollTop + 20;
		
		tag.obj.style.display = '';
		tag.obj.style.top  = top  + 'px';
		tag.obj.style.left = left + 'px';

		tag.obj.innerHTML = html;
	},

	insert : function(o){
		tag.hide = true;
		var str = tag.input.value;
		var pos = str.lastIndexOf(' ') + 1;
		var laststr = str.substr(pos,str.length);
		tag.input.value = str.substr(0,pos) + o.lastChild.innerHTML;
		tag.close();
	},

	addtag : function(o){
		var str = tag.input.value.replace(/^\s+/g,'').replace(/\s+$/g,'');
		str = str.replace(/\s+/g,' ');
		if(str.split(' ').length > 4){
			if (typeof showDialog == 'function') {
				showDialog('warning','最多可以添加  <font color="red">5</font> 个标签');
			} else {
				alert('最多可以添加5个标签');
			}
		} else{
			tag.input.value = str + (str ? ' ' : '') + o.innerHTML;
		}
	},

	get : function(){
		if(tag.tagdb == null){
			ajax.send('pw_ajax.php','action=tag',function(){tag.create(2);});
			return;
		}
		var num     = 0;
		var gettags = '';
		var subject = document.FORM.atc_title.value;
		var content = editor.getHTML();
		var tagName = '';
		for(var i=0;i<tag.tagdb.length;i++){
			var s = tag.tagdb[i];
			tagName = (s[0].indexOf(' ') != -1 && s[0].indexOf('"') != -1) ? s[0].replace(/"/g,''):s[0];
			if(subject.indexOf(tagName) != -1 || content.indexOf(tagName) != -1){
				gettags += gettags ? ' '+s[0] : s[0];
				if(++num>4) break;
			}
		}
		if(gettags){
			this.input.value = gettags;
		} else{
			if (typeof showDialog == 'function') {
				showDialog('warning','没有可用的标签');
			} else {
				alert('没有可用的标签');
			}
		}
	},

	close : function(){
		if(tag.hide){
			tag.obj.style.display = "none";
		}
	}
}

var tag = new PwTags();