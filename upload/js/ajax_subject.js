
function PwSubject(){

}

PwSubject.prototype = {

	init : function(){
		var tds = getObj("ajaxtable").getElementsByTagName('td');
		for(var i=0;i<tds.length;i++){
			if(tds[i].hasChildNodes() && tds[i].id && tds[i].id.substr(0, 3) == 'td_'){
				tds[i].ondblclick = function(){subject.doubleclick(this.id);};
			}
		}
	},

	doubleclick : function(id){
		var tid = id.substr(id.lastIndexOf('_')+1);
		if(IsElement('editor_'+tid)) return false;
		var obj = getObj('a_ajax_'+tid);
		ajax.send('ajax.php','action=subject&fid='+fid+'&tid='+tid,function(){
			subject.get(tid);
		});
	},

	get : function(tid){
		var str = ajax.request.responseText.split("\t");
		if(str[0] == 'success'){
			var o = document.createElement('span');
			o.id  = 'editor_'+tid;
			o.innerHTML = '<input class="input" type="text" size="40" maxLength="100" value="'+str[1]+'" /> <a onclick="subject.save(\''+tid+'\')" class="bta">保存</a><a onclick="subject.cancle(\''+tid+'\')" class="bta">取消</a>';
			var obj = getObj('a_ajax_'+tid);
			obj.parentNode.insertBefore(o,obj);
			obj.style.display = 'none';
			o.firstChild.focus();
		} else{
			ajax.guide();
		}
	},

	cancle : function(tid){
		getObj('editor_'+tid).parentNode.removeChild(getObj('editor_'+tid));
		getObj('a_ajax_'+tid).style.display = '';
	},

	save : function(tid){
		var v = getObj('editor_'+tid).firstChild.value;
		var data = 'action=subject&step=2&fid='+fid+'&tid='+tid+'&atc_content='+(this.replace(v));
		ajax.send('ajax.php',data,function(){
			subject.finish(tid);
		});
	},
	
	replace : function (str){
		var regexs = new Array(new RegExp('%', 'g'),new RegExp(',', 'g'),new RegExp('\/', 'g'),new RegExp('\\?', 'g'),new RegExp(':', 'g'),new RegExp('@', 'g'),new RegExp('&', 'g'),new RegExp('=', 'g'),new RegExp('\\+', 'g'),new RegExp('\\$', 'g'),new RegExp('#', 'g'));
		var replaces = new Array('%25','%2C','%2F','%3F','%3A','%40','%26','%3D','%2B','%24','%23');
		for (var i = 0; i < regexs.length; i++){
			str = str.replace(regexs[i], replaces[i]);
		}
		return str;
	},

	finish : function(tid){
		var str = ajax.request.responseText.split("\t");
		if(str[0] == 'success'){
			subject.cancle(tid);
			getObj('a_ajax_'+tid).innerHTML = str[1];
		} else{
			ajax.guide();
		}
	}
}

var subject = new PwSubject();

subject.init();