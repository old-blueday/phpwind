/**
 * @author wengqianshan
 * @功能
 */
 Breeze.namespace('app.at', function (B) {
 var ie=B.UA.ie;
var at = function(editor,len){
    this.editor = editor;
    this.win = editor.contentWindow;
    this.doc = editor.contentWindow.document;
    
    this.active = 0;//当输入@后 激活此功能，
    this.gspan = null;
    this.gval = null;
    this.gtip = null;
    this.lindex = -1;
    this.list = null;
    this.enter = false;
    this.usableVal = null;
	this.usableUid=null;
    this.maxNum = len||0;//最多允许@的人数
    this.currNum = 0;//当前已经@的人数|不可修改
	this.contentID="note_iframe";
	this.maxTxtLength=15;//允许输入的关键字最大长度
	this.cache=[];//缓存数据
	this.queryDelay=200;//请求服务器延时时间
	this.queryInter=null;
	this.errorInter=null;
	this.ime_mode=false;//输入法状态
	/*
	*this.cache=[{"key":"xiao","items":[{"uid":2,"uname":"xiaoshan"},{"uid":3,"uname":"xiaoshan"}]},{"key":"xiaoh","items":[{"uid":14,"uname":"xiaoshan"},{"uid":5,"uname":"xiaoshan"}]}]
	*/
}
at.prototype = {
    "paste": function(html){
		//粘贴html
        if (!this.doc.selection) {
            var selection = this.win.getSelection();
            var rg = selection.getRangeAt(0);
            var fragment = rg.createContextualFragment(html);
            var oLastNode = fragment.lastChild; //获得DocumentFragment的末尾位置 
            rg.insertNode(fragment);
        }
        else {
            var range = this.doc.selection.createRange();
            range.pasteHTML(html);
        }
    },
    "addEvent": function(elem, type, fn){
		//绑定事件
        if (elem.addEventListener) {
            elem.addEventListener(type, fn, false);
        }
        else {
            elem['$e' + type + fn] = fn;
            elem[type + fn] = function(){
                elem['$e' + type + fn](window.event)
            };
            elem.attachEvent('on' + type, elem[type + fn]);
        };
            },
    "removeEvent": function(elem, type, fn){
			//移除事件
			if (elem.removeEventListener) {
				elem.removeEventListener(type, fn, false);
			}
			else {
				elem.detachEvent('on' + type, elem[type + fn]);
				elem[type + fn] = null;
			};
    },
    "init": function(){	
		//初始化
        var self = this;
		var cancelKeypress=false;
		
		//keydown事件
        this.addEvent(self.doc, "keydown", function(e){
			if(self.queryInter){
				clearTimeout(self.queryInter);
			}
            if (self.win.getSelection) {
                var sel = self.win.getSelection();
                var range = sel.getRangeAt(0);
            }
            else {
                var sel = self.doc.selection;
                var range = sel.createRange();
            }
            var e = e || self.win.event;
            var keyCode = e.which || e.keyCode;
			//是否开启输入法
			self.ime_mode=e.shiftKey&&(keyCode==229||keyCode==197);

			var condition=e.shiftKey &&keyCode==50;
			//如果是英文状态下输入@,即激活@功能
            if (condition) {
				//如果@人数超出限制
                if (self.maxNum&&self.maxNum!=0&&self.currNum >= self.maxNum) {
                    return false;
                }
				cancelKeypress=true;
				
				//创建@
				self.createAt();
                self.preventDefault(e);
            }
				//选中状态时按回车
            else if (self.enter && keyCode == 13) {
                    self.addTo();
                    self.preventDefault(e);
            }
				//空格、回车、tab
            else if (keyCode == 13 || keyCode == 32 || keyCode == 9) {
					
					self.reset();
					self.hide();
			}
				//后退
			else  if (keyCode == 8) {
				//chrome专属
				if(!ie&&range.startContainer.parentElement){
					var node=range.startContainer.parentElement;
					if(node&&node.className=="pw_at_li"){
						//获取当前准备删除的用户名 备用
						var uname=node.innerHTML.substr(1);
						node.parentElement.removeChild(node);
						sel.addRange(range);
						self.preventDefault(e);
						return false;
					}
				}else if(range.parentElement!=undefined){
					var node=range.parentElement().firstChild;
					if(node&&node.nodeName=="A"&&node.className=="pw_at_li"){
						//获取当前准备删除的用户名 备用
						var uname=node.innerHTML.substr(1);
						node.parentElement.removeChild(node);
					}
				}
			}
			//激活状态下方向键事件
			else if (self.active && (keyCode == 37 || keyCode == 38)) {
				self.directionKey(-1);
				self.preventDefault(e);
			}
			else  if (self.active && (keyCode == 39 || keyCode == 40)) {
				self.directionKey(1);
				self.preventDefault(e);
			}						
        })
		
		//针对Opera的兼容处理
		if(!!window.opera){
			self.doc.onkeypress=function(){
				if(cancelKeypress==true){
					cancelKeypress=false;
					return false;
				}
			}
		}
		
		//keyup事件
        this.addEvent(self.doc, "keyup", function(e){
			var e = e || self.win.event;
            var keyCode = e.which || e.keyCode;
			if (self.win.getSelection) {
                var sel = self.win.getSelection();
                var range = sel.getRangeAt(0);
            }
            else {
                var sel = self.doc.selection;
                var range = sel.createRange();
            }
			//如果是输入法模式
			if(self.ime_mode){
				//获取光标父节点的最后一个节点
				if(!ie||ie>=9){
					var node=range.startContainer;
					//如果正常获取节点内容
					if(node&&node.nodeValue!=null){
						var len=node.length;
						var str=node.nodeValue.substr(len-1,1);
						if(str=="@"){
							//如果发现刚刚输入的是@ 就删除这个@然后构造我们自己的@
							range.setStart(node, len-1);
							range.setEnd(node, len);
							range.deleteContents();
							self.createAt();
						}
					}
				}else{
					var node=range.parentElement().lastChild;
					//如果正常获取节点内容
					if(node&&node.nodeValue!=null){
						var len=node.length;
						var str=node.nodeValue.substr(len-1,1);
						if(str=="@"){
							node.nodeValue=node.nodeValue.substr(0,len-1)
							self.createAt();
						}
					}
				}
			}
			//英文状态...
			if(!self.gspan){
				return false;
			}
			if(self.errorInter){
				clearTimeout(self.errorInter);
			}
				//激活状态下span内容存在时
            if (self.active == 1 && self.gspan != null) {
				//屏蔽方向键
                if (keyCode == 37 || keyCode == 38 || keyCode == 39 || keyCode == 40) {
                    return false;
                }
				//获取关键字并做相关处理
				var _gval=self.gspan.innerHTML.substr(1);
				
				if(_gval&&_gval.length>=self.maxTxtLength){
					self.hide();
					self.reset();
					return false;
				}
				//后退键
				if(keyCode==8){
					//如果把@都删除了的话 直接重置
					if(self.gval==""){
						self.hide();
						self.reset();
						return false;
					}
					if (document.getElementById("tmpDiv")) {
						div = document.getElementById("tmpDiv");
						div.style.display = "block";
						div.innerHTML = "<span style='padding:0 5px;'>想用@提到谁？</span>";
					}
				}
				//符合条件,执行查询
				if(self.active==1){
					self.gval = _gval;
					if(self.gval!=""){
						//如果有缓存数据 试着从缓存读取
						if(self.cache.length>=1){
							for(var i=0,len=self.cache.length;i<len;i++){
								if(self.gval==self.cache[i].key){
									self.fillCont(self.cache[i].items);
									return false;
								}
							}
						}
						//延时查询
						self.queryInter=setTimeout(function(){
								self.getData();
							},self.queryDelay);
						
					}
				}
            }
			
			self.preventDefault(e);
        })
		this.addEvent(document,"mousedown",function(){
			self.hide();
		})
    },
	"createAt":function(){
				if (this.win.getSelection) {
					var sel = this.win.getSelection();
					var range = sel.getRangeAt(0);
				}
				else {
					var sel = this.doc.selection;
					var range = sel.createRange();
				}
				this.active = 1;
                var id = "tmp" + (+new Date());
				this.paste("<span id=" + id + ">@</span>");
                
                var span = this.doc.getElementById(id);
				
				this.gspan = span;
				var x = span.getBoundingClientRect().left;
				var y = span.getBoundingClientRect().top;

                if (ie && ie < 9) {
                    var range = this.doc.body.createTextRange();
					range.moveToElementText(span);
					range.moveStart("character");
					range.select();
                }
                else {
                        var ospan = span.firstChild;
                        range.setStart(ospan, 1);
                        range.setEnd(ospan, 1);
                        sel.removeAllRanges();
                        sel.addRange(range);
                    }
                this.show(x, y);
	},
    "show": function(x, y){
		//显示弹出层
		var st=document.documentElement.scrollTop+document.body.scrollTop;
        var content = document.getElementById(this.contentID);
        var x0 = content.getBoundingClientRect().left + 11;
		var y0 = content.getBoundingClientRect().top + 20+st;
        var div;
        if (document.getElementById("tmpDiv")) {
            div = document.getElementById("tmpDiv");
            div.style.display = "block";
        }
        else {
            div = document.createElement("div");
            div.id = "tmpDiv";
            //div.style.cssText = "position:absolute;z-index:1;padding:0px;min-width:100px;min-height:18px;border:1px solid #eee;background:#fff";
            document.body.appendChild(div);
        }
        div.innerHTML = "<span style='padding:0 5px;'>想用@提到谁？</span>";
        this.gtip = div;
        div.style.left = x + x0 + "px";
        div.style.top = y + y0 + "px";
        
    },
	"addTo":function(){
			if(this.lindex==-1){
				return false;
			}
			var self=this;
			 if (self.win.getSelection) {
                var sel = self.win.getSelection();
                var range = sel.getRangeAt(0);
            }
            else {
                var sel = self.doc.selection;
                var range = sel.createRange();
            }
			var uname=self.usableVal;
			var uid=self.usableUid;
			self.sync(uname);
			self.gspan.innerHTML = "<a class='pw_at_li' data-uid='"+uid+"' href='u.php?uid="+uid+"'>@" + self.usableVal + "</a>&nbsp;";
			if (ie && ie < 9) {
				var range = self.doc.body.createTextRange();
				//range.moveToElementText(self.gspan);
				//range.moveStart("character");
				//range.select();
			}
			else {
				var ospan = self.gspan.lastChild;
				range.setStart(ospan, 1);
				range.setEnd(ospan, 1);
				sel.removeAllRanges();
				sel.addRange(range);
			}
			self.currNum++;
			self.hide();
			self.reset();
	},
	"sync":function(uname){
			/*
			*同pw_search.js结合使用
			*/
			if(getObj("get_friend")){
				if(!pwSearch.dst){
					pwSearch.init('message.php?type=ajax','action=friend','resultd');
				}
				pwSearch.add(uname);
			}
	},
	"showLoding":function(str){
		if(this.gtip){
			this.gtip.innerHTML=str;
		}
	},		
	"getData":function(){
		if(this.gval==null){
			return false;
		}
		var self=this;
		var param="key="+encodeURI(this.gval);
		this.showLoding("<span style='padding:0 5px;'>加载中...</span>");
		ajax.send("pw_ajax.php?action=friends",param,function(){
			var json=ajax.request.responseText;
			if(!json){
				self.hide();
				return false;
			}
			var json=eval("("+json+")");
			var status=json.status;
			if(!status||status!=1){
				
				self.showLoding("<span style='padding:0 5px;'>没有您要找的用户</span>");
				self.errorInter=setTimeout(function(){
					self.hide();
				},1500)
				return false;
			}
			var users=json.users;
			self.fillCont(users);
			var cache={};
			cache.key=self.gval;
			cache.items=users;
			self.cache.push(cache);			
			
		})
	},
    "fillCont": function(data){
		//填充数据
		var self=this;
		if(data==undefined||!this.gtip){
			self.showLoding("<span style='padding:0 5px;'>没有您要找的用户</span>");
			self.errorInter=setTimeout(function(){
				self.hide();
				//self.reset();
			},1500)
			//this.hide();
			//this.reset();
			return false;
		}
		var self=this;
        var ul = document.createElement("ul");
        ul.className = "atlist";
        ul.id = "atUl";
        this.list = ul;
        for (var i = 0; i < data.length; i++) {
			var uid=data[i].uid;
			var uname=data[i].uname;
            var li = document.createElement("li");
            var a = document.createElement("a");
            a.setAttribute("href", "javascript:void(0)");
			a.setAttribute("data-uid",uid);
            a.innerHTML = uname;
            li.appendChild(a);
            ul.appendChild(li);
			(function(ele,name,id,index){
				ele.onmousedown=function(){
					self.usableVal=name;
					self.usableUid=id;
					self.lindex=index;
					self.addTo();
					return false;
				}
			})(li,uname,uid,i)
        }
        this.gtip.innerHTML = "";
        this.gtip.appendChild(ul);
		this.hover(0);
    },
    "hide": function(){
		//隐藏弹出层
        var div;
        if (document.getElementById("tmpDiv")) {
            div = document.getElementById("tmpDiv");
            div.style.display = "none";
        }
    },
    "directionKey": function(i){
		if(this.list==null){
			return false;
		}
		var lis = this.list.getElementsByTagName("li");
		var l=lis.length-1;
		//方向键控制
        if (i == -1) {
            this.lindex = this.lindex <= -1 ? l : this.lindex - 1;
        }
        else {
            this.lindex = this.lindex >= l ? -1 : this.lindex + 1;
        }
		for (var j = 0, len = lis.length; j < len; j++) {
			lis[j].className = "";
		}
		if (this.lindex != -1) {
			this.hover(this.lindex);
		}
		else {
			this.usableVal = null;
			this.enter = false;
		}
        //console.log(this.lindex);
    },
	"hover":function(index){
				this.lindex=index;
				this.enter = true;
                var currLi = this.list.getElementsByTagName("li")[index];
                currLi.className = "hover";
                this.usableVal = currLi.getElementsByTagName("a")[0].innerHTML;
				this.usableUid=currLi.getElementsByTagName("a")[0].getAttribute("data-uid");
	},
    "reset": function(){
		//重置
        this.active = 0;
        this.gspan = null;
        this.gval = null;
        this.gtip = null;
        this.lindex = -1;
        this.list = null;
        this.enter = false;
        this.usableVal = null;
		this.usableUid=null;
		this.ime_mode=false;
    },
    "preventDefault": function(e){
        if (e.preventDefault) {
            e.preventDefault();
        }
        else {
            e.returnValue = false;
        }
    }
}
B.app.at=at; 
});