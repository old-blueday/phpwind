Breeze.namespace('app.frameload', function (B) {
	var win = window, doc = document, body = doc.body, now = (new Date).getTime();
	//安全的执行script
	function $exec(text){
		if (!text) return text;
		if (win.execScript){
			win.execScript(text);
		} else {
			var script = doc.createElement('script');
			script.setAttribute('type', 'text/javascript');
			try {
				script.appendChild( doc.createTextNode( "window." + now + "=1;" ) );
			} catch(e) {}
			if ( window[now] ) {
				script.appendChild( doc.createTextNode( data ) );
			} else {
				script.text = data;
			}
			doc.head.appendChild(script);
			doc.head.removeChild(script);
		}
		return text;
	};

	function init(){
		B.app.frameload = function(url, callback){
			var n = new Date().getTime();
			var s = B.createElement('<iframe style="display:none" src="about:blank" id="' + n + '" name="' + n + '"></iframe>');
			document.body.insertBefore(s, document.body.childNodes[0]);
			B.addEvent(s, 'load', function(){
				var str = s.contentWindow.document.body.innerHTML ;
				str = $exec(str);
				callback(str);
				B.removeEvent(s, 'load');
				B.remove(s);
			});
			s.src = url;
		}
	}
	B.require('dom', 'event', init);
});
