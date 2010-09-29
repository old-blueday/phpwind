var change = Class({},{
	Create	: function (channel,invoke,invokepiece,url,invokes) {
		this.channel = getObj(channel);
		this.invoke	= getObj(invoke);
		this.invokepiece = getObj(invokepiece);
		if (invokes) {
			this.invokes = JSONParse(invokes);
		}
		this.url = url;
		this._init();
	},
	_init	: function () {
		this._initChannel();
		this._initInvoke();
	},
	_initChannel : function () {
		var _this = this;
		this.channel.onchange = function(){
			var data = "action=channelchange&alias="+this.value;
			ajax.send(_this.url,data,function() {
					var text = ajax.request.responseText;
					_this.invokes = JSONParse(text);
					_this.createInovkeSelect();
					_this.createInovkePieceSelect();
				}
			);
		}
	},
	_initInvoke : function () {
		var _this = this;
		this.invoke.onchange = function(){
			_this.createInovkePieceSelect(this.value);
		}
	},

	createInovkeSelect : function () {
		this.invoke.innerHTML = '';
		var s = this.invoke;
		s.options[s.options.length] = new Option("","");
		for (var invoke in this.invokes) {
			var temp = this.invokes[invoke];
			s.options[s.options.length] = new Option(temp['title'],temp['invokename']);
		}
	},
	createInovkePieceSelect : function (invoke) {
		this.invokepiece.innerHTML = '';
		var s = this.invokepiece;
		s.options[s.options.length] = new Option("","");
		
		if (invoke) {
			for (var piece in this.invokes[invoke]['pieces']) {
				var temp = this.invokes[invoke]['pieces'][piece];
				s.options[s.options.length] = new Option(temp,piece);
			}
		}
	}
	
});

var editChange = Class(change,{
	Create	: function (channel,invoke,invokepiece,url,invokes,fetchBox) {
		change.Create.call(this,channel,invoke,invokepiece,url,invokes);	//调用父类的构造函数
		this.fetchBox = getObj(fetchBox);
		this._initPiece();
	},
	_initPiece : function () {
		var _this = this;
		this.invokepiece.onchange = function () {
			var data = "action=fetch&pushdataid="+pushdataid+"&invokepieceid="+this.value;
			ajax.send(_this.url,data,function() {
					var text = ajax.request.responseText;
					_this.fetchBox.innerHTML = text;
				}
			);
		}
	}
	
});