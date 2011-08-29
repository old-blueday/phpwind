
function PwDraft(){

}

PwDraft.prototype = {
	insert : function(o){
		var obj = o.parentNode.firstChild;
		if(typeof editor != 'undefined'){
			editor.pasteHTML(B.editor.ubb2html(obj.value));
		} else{
			document.FORM.atc_content.value += obj.value;
		}
	},

	update : function(o,id){
		var msg = ajax.convert(o.parentNode.firstChild.value);
		ajax.send('pw_ajax.php', 'action=draft&step=3&atc_content='+msg+'&did='+id, ajax.guide);
	},

	del : function(id,page){
		ajax.send('pw_ajax.php', 'action=draft&step=4&did='+id, function(){
			ajax.guide(function() {
				draft.pages(page);
			});
		});
	},

	prev : function(page){
		draft.pages(parseInt(page)-1);
	},

	next : function(page){
		draft.pages(parseInt(page)+1);
	},

	pages : function(page){
		ajax.send('pw_ajax.php','action=draft&page='+page,function(){
			if (ajax.request.responseText != null && ajax.request.responseText.indexOf('<') != -1) {
				read.setMenu(this.runscript(ajax.request.responseText));
				read.menupz(read.obj);
			} else {
				closep();
				ajax.guide();
			}
		});
	}
}

var draft = new PwDraft();