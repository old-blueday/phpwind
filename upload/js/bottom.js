var app_bottom = function (json) {
	this.currentTab = null;

	this.appTab		= null;
	this.friendTab	= null;
	this.messageTab	= null;
	this.postlistTab= null;
	this.forumlistTab=null;

	this.friendTem	= null;
	this.messageTem = null;

	this.init(json);
}

app_bottom.prototype = {
	init 		: function (j) {
		this.appTab		= j.app;
		this.friendTab	= j.friend;
		this.messageTab	=j.message;
		this.postlistTab= j.postlist;
		this.forumlistTab= j.forumlist;

		this.diaryTab= j.diary;
		this.weiboTab= j.weibo;
		this.photoTab= j.photo;

		var _ = this;

		if (IsElement(j.diary))
		{
			getObj(j.diary).onclick=function(e)
			{
				_.halt(e);
				_.showDiary();
			};
		}
		if(IsElement(j.weibo)){
			getObj(j.weibo).onclick=function(e)
			{
				_.halt(e);
				_.showWeibo();
			};
		}
		if(IsElement(j.photo)){
			getObj(j.photo).onclick=function(e)
			{
				_.halt(e);
				_.showPhoto();
			};
		}

		getObj(j.app).onclick = function (e) {
			_.halt(e);
			_.showAPP();
		};
		getObj(j.friend).onclick = function (e) {
			_.halt(e);
			_.showFriend();
		};
		getObj(j.message).onclick = function (e) {
			_.halt(e);
			_.showMessage();
		};
		getObj(j.postlist).onclick = function (e) {
			_.halt(e);
			_.showPostlist(this);
		};
		getObj(j.forumlist).onclick = function (e) {
			_.halt(e);
			_.showForumlist(this);
		};
	},
	/*
	*阻止浏览器默认行为
	*/
	halt:function(evt){
			var e=evt||window.event;
			if(e.preventDefault){
				e.preventDefault();
			}else{
				e.returnValue=false;	
			}
	},
		/**
		 * 显示相册
		 */
	showPhoto:function()
	{
	   this.tabChange(this.photoTab);
	   		this.clickArttribute();
		var thisobj = getObj(this.photoTab);
		var parent	= thisobj.parentNode;
		this.changeFirstNodeDisplay(parent,'span');
		var list	= elementBind('div','','startbar-menuleft');
		var img=getObj('pwd_app_hidden').getElementsByTagName("img")[0];

		list.innerHTML	= "<div class='sbar-title' onclick = 'pw_bottom.hiddenTab();'>\
		<span class='fr closeicon'><img src='"+img.src+"' alt=\"收起\" /></span><span class='b'>相册&nbsp;</span>\
	</div>\
	<DIV style='OVERFLOW: hidden; WIDTH: 550px;height:450px;' class='sbar-box-b'><IFRAME id='pwifm_source' border=0 src='apps.php?q=photos&a=upload&s=1' frameBorder='0' width='100%' scrolling='no' height='100%'></IFRAME></div>";
		parent.appendChild(list);
		list.style.zIndex="-1";
		list.style.left="-1px";
	getObj("pwifm_source").src="apps.php?q=photos&a=upload&s=1";
	   list.style.width="575px";

	},
		/**
		 *显示新鲜事
		 */
	showWeibo:function()
	{
	   	this.tabChange(this.weiboTab);
		this.clickArttribute();
		var thisobj = getObj(this.weiboTab);
		var parent	= thisobj.parentNode;
		this.changeFirstNodeDisplay(parent,'span');
		var list	= elementBind('div','','startbar-menuleft');
		var img=getObj('pwd_app_hidden').getElementsByTagName("img")[0];
		list.innerHTML	='\
<div class="sbar-title" onclick = "pw_bottom.hiddenTab();"> \
		<span class="fr closeicon"><img src="'+img.src+'" alt=\"收起\" /></span><span class="b">新鲜事&nbsp;</span>\
	</div>\
	<div style="overflow:hidden;width:593px;height:210px;overflow:hidden;" class="sbar-box-b"><IFRAME id="pwifm_source" border=0 src="apps.php?q=weibo&a=post&s=1" frameBorder="0" width="100%" scrolling="no" height="100%"></IFRAME></div>';
		parent.appendChild(list);
		list.style.zIndex="-1";
		list.style.left="-1px";
		list.style.width="615px";
	},
		/**
		 *显示写日志
		 */
	showDiary:function()
	{
		this.tabChange(this.diaryTab);
		this.clickArttribute();
		var thisobj = getObj(this.diaryTab);
		var parent	= thisobj.parentNode;
		this.changeFirstNodeDisplay(parent,'span');
		var img=getObj('pwd_app_hidden').getElementsByTagName("img")[0];
		var list	= elementBind('div','','startbar-menuleft');
		var IE6=navigator.userAgent.indexOf("MSIE 7.0")==-1&&navigator.userAgent.indexOf("MSIE 8.0")==-1&&navigator.userAgent.indexOf("MSIE 6.0")>0;
		list.innerHTML	= " <div class='sbar-title' onclick = \"pw_bottom.hiddenTab();\"> \
		<span class=\"fr closeicon\"><img src=\""+img.src+"\" alt=\"收起\" /></span><span class=\"b\">日志&nbsp;</span>\
	</div>\
	<div class='sbar-box-b' style='overflow:hidden;WIDTH:631px;height:340px'><IFRAME id='diary_source' src='apps.php?q=diary&a=write&s=1' frameBorder='0' width='100%' scrolling='no' height='100%'></IFRAME></div>";
		list.style.zIndex="-1";

		parent.appendChild(list);
		setTimeout("getObj('diary_source').src=getObj('diary_source').src",10);
			list.style.left="-1px";
			   list.style.width="652px";

	},

	showAPP		: function () {
		this.tabChange(this.appTab);
		this.clickArttribute();
		var thisobj = getObj(this.appTab);
		var parent	= thisobj.parentNode;
		this.changeFirstNodeDisplay(parent,'span');
		var list	= elementBind('div','','startbar-menuleft');
		list.innerHTML	= getObj('pwd_app_hidden').innerHTML;
		parent.appendChild(list);
	},

	changeFirstNodeDisplay	: function (parent,tag) {
		var firstTag	= this.gerFirstTagNode(parent,tag);
		if (typeof(firstTag)=='object') {
			firstTag.style.display = '';
		}
	},

	showForumlist : function (o) {
		this.tabChange(this.forumlistTab);
		this.clickArttribute();
		var thisobj = getObj(this.forumlistTab);
		var parent	= thisobj.parentNode;
		this.changeFirstNodeDisplay(parent,'span');
		pwForumList(false,false,null,o);
		var list	= elementBind('div','pwd_forumlist_hidden','menu-thread-bottom','');
		list.innerHTML	= getObj('menu_forumlist').innerHTML;
		parent.appendChild(list);
		list.style.zIndex="-1";
		list.style.left="22px";
	},

	showPostlist : function (o) {
		this.tabChange(this.postlistTab);
		this.clickArttribute();
		var thisobj = getObj(this.postlistTab);
		var parent	= thisobj.parentNode;
		this.changeFirstNodeDisplay(parent,'span');
		//pwForumList(false,true,null,o);
		var title_forumlist = getObj('title_forumlist');
		title_forumlist.innerHTML = "快速发帖";
		var list	= elementBind('div','pwd_postlist_hidden','menu-post-bottom','');
		list.innerHTML	= getObj('menu_forumlist').innerHTML;
		parent.appendChild(list);
		list.style.zIndex="-1";
		list.style.left="22px";
	},

	showMessage : function() {
		this.tabChange(this.messageTab);
		this.clickArttribute();
		var thisobj = getObj(this.messageTab);
		var parent	= thisobj.parentNode;
		this.changeFirstNodeDisplay(parent,'span');
		var list;
		if (this.messageTem != null) {
			list = this.messageTem;
			parent.appendChild(list);
		} else {
			this.loadingView(parent);
			var handle = this;
			list = elementBind('div','','startbar-menuright');
			setTimeout(function(){ajax.send('pw_ajax.php?action=pwb_message','u='+winduid,function(){
				var rText = ajax.request.responseText.split('\t');
				if (rText[0] == 'success') {
					var h2 = elementBind('div','','sbar-title');
					h2.innerHTML= '<span class="fr"><img src="images/wind/index/cate_open.gif" alt=\"收起\" /></span><span class="b"><a href="message.php" target="_blank">消息中心</a></span>';
					h2.onclick = function () {
						handle.hiddenTab();
					}
					list.appendChild(h2);

					var div	= elementBind('div','','listAppItem sbar-box-b');
					list.appendChild(div);
					var ul = elementBind('ul','newmessagebox','p10');
					if (rText[1] != '') {
						try{var msgs = JSONParse(rText[1]);
						if (msgs.length > 5) {
							ul.className = 'mes-height';
						}
						if (typeof(msgs)=='object') {
							for (var i in msgs) {
								if (typeof(msgs[i])=='object') {
									handle.creatMess(msgs[i],ul);
								}
							}
						}}catch(e){}
					} else {
						var li = elementBind('li');
						li.innerHTML = '<a href="message.php" target="_blank">您没有未读消息</a>';
						ul.appendChild(li);
					}
					div.appendChild(ul);
					handle.messageTem = list;
					delElement('load_temp');
					parent.appendChild(list);
				} else {
					ajax.guide();
				}
			});},300);
		}
	},

	creatMess	: function (obj,element) {
		if (typeof(obj)!='object') {
			return false;
		}
		var mid = obj['mid'];
		var rid = obj['rid'];
		var typeid = obj['typeid'];
		var title	= char_cv(obj['title']);
		var from	= obj['from'] == 'undefined' || obj['from'] == null ? 'system' : char_cv(obj['from']);
		var fromuid	= char_cv(obj['fromuid']);
		var type = obj['type'];
		var link = type == 'request' ? title : '<a href="message.php?action=info&type='+type+'&smstype='+typeid+'&rid='+rid+'&mid='+mid+'" title="'+title+'" style="cursor:pointer;">'+title+'</a>';
		var li	= elementBind('li','msg_'+mid);
		li.innerHTML = '<span><a href="u.php?uid='+fromuid+'" class="gray">'+from+'</a></span>:&nbsp;'+link;
		element.appendChild(li);
	},

	showFriend	: function () {
		this.tabChange(this.friendTab);
		this.clickArttribute();
		var thisobj = getObj(this.friendTab);
		var parent	= thisobj.parentNode;
		this.changeFirstNodeDisplay(parent,'span');
		this.loadingView(parent);
		var list;
		if (this.friendTemp != null) {
			list = this.friendTemp;
			parent.appendChild(list);
		} else {
			this.loadingView(parent);
			var handle = this;
			list = elementBind('div','','startbar-menuright');
			setTimeout(function(){ajax.send('pw_ajax.php?action=pwb_friend','u='+winduid,function(){
				var rText = ajax.request.responseText.split('\t');
				if (rText[0] == 'success') {try{
					var h2 = elementBind('div','','sbar-title');
					h2.innerHTML= '<span class="fr"><img src="images/wind/index/cate_open.gif" alt=\"收起\" /></span><span class="b">在线好友</span>';
					h2.onclick = function () {
						handle.hiddenTab();
					}
					list.appendChild(h2);
					var div	= elementBind('div','','listAppimg sbar-box-b');
					list.appendChild(div);
					var ul = elementBind('ul','','cc');
					div.appendChild(ul);

					if (rText[1] != '') {
						try{var friends = JSONParse(rText[1]);
						if (typeof(friends)=='object') {
							for (var i in friends) {
								if (typeof(friends[i])=='object') {
									handle.creatFriend(friends[i],ul);
								}
							}
						}}catch(e){}
					} else {
						var li = elementBind('li');
						li.innerHTML = '<div>没有好友在线上 / <a href="u.php?a=friend&type=my">查看所有好友</a></div>';
						ul.appendChild(li);
					}
					handle.friendTemp = list;
					delElement('load_temp');
					parent.appendChild(list);}catch(e){alert(e.message||e)}
				} else {
					ajax.guide();
				}
			});},300);

		}
		delElement('load_temp');
	},

	creatFriend	: function (obj,element) {
		if (typeof(obj)!='object') {
			return false;
		}
		var uid = obj['uid'];
		var face	= obj['face'];
		var username= obj['username'];
		var li = elementBind('li');
		li.innerHTML = '<a class="fr" style="cursor: pointer;" onclick="sendmsg(\'pw_ajax.php?action=msg&touid='+uid+'\')">发消息</a><a href="u.php?uid='+uid+'"><img src="'+char_cv(face)+'" width="16" height="16" alt=\"'+char_cv(username)+'\" /></a>&nbsp;<a href="u.php?uid='+uid+'">'+char_cv(username)+'</a>';
		element.appendChild(li);
	},

	hiddenTab	: function () {
		if (this.currentTab != null) {
			var handle = this;
			thistab = getObj(this.currentTab);
			if (this.currentTab == this.appTab) {
				thistab.onclick = function () {
					handle.showAPP();
				}
			} else if (this.currentTab == this.friendTab) {
				thistab.onclick = function () {
					handle.showFriend();
				}
			} else if (this.currentTab == this.messageTab) {
				thistab.onclick = function () {
					handle.showMessage();
				}
			} else if (this.currentTab == this.postlistTab) {
				thistab.onclick = function () {
					handle.showPostlist(this);
				}
			} else if (this.currentTab == this.forumlistTab) {
				thistab.onclick = function () {
					handle.showForumlist(this);
				}
			}else if(this.currentTab == this.diaryTab)
			{
				thistab.onclick = function () {
					handle.showDiary(this);
				}
			}
			else if(this.currentTab == this.photoTab)
			{
				  thistab.onclick = function () {
					handle.showPhoto(this);
				}
			}else if(this.currentTab == this.weiboTab)
			{
				thistab.onclick = function () {
					handle.showWeibo(this);
				}
			}
			var parent = thistab.parentNode;
			var firstTag	= this.gerFirstTagNode(parent,'span');
			if (typeof(firstTag)=='object') {
				firstTag.style.display = 'none';
			}
			var container = this.getContainer(parent);
			if (typeof(container) == 'object') {
				delElement(container);
			}
			this.currentTab = null;
			this.resetClick();
		}
	},

	getContainer	: function (parent) {
		parent = objCheck(parent);
		var leftContainer = this.getElementByClassName(parent,'startbar-menuleft');
		if (typeof(leftContainer) == 'object') {
			return leftContainer;
		}
		var rightContainer	= this.getElementByClassName(parent,'startbar-menuright');
		if (typeof(rightContainer) == 'object') {
			return rightContainer;
		}

		if (IsElement('pwd_postlist_hidden')) {
			return getObj('pwd_postlist_hidden');
		}
		if (IsElement('pwd_forumlist_hidden')) {
			return getObj('pwd_forumlist_hidden');
		}
		return false;
	},
	getElementByClassName	: function (obj,name) {
		obj	= objCheck(obj);
		var elems = document.getElementsByTagName('div');
		for ( var i = 0; i<elems.length; i++ ) {
			if ( elems[i].className == name ){
				return elems[i];
			}
		}
		return false;
	},
	gerFirstTagNode	: function (obj,tag) {
		obj = objCheck(obj);
		var elems = obj.getElementsByTagName(tag);
		if (elems.length>0) {
			return elems[0];
		}
		return false;
	},
	loadingView	: function (parent) {
		var temp	= elementBind('div','load_temp','startbar-menuright popout');
		temp.innerHTML = '<div style="padding-top:20px;"><div class="sbar-box-b p10"><img src="'+imgpath+'/loading.gif" align="absmiddle" alt="loading" /> 正在加载数据...</div></div>';
		parent.appendChild(temp);
	},

	tabChange	: function (id) {
		this.hiddenTab();
		this.currentTab = id;
		var handle = this;
		getObj(id).onclick = function(){
			handle.hiddenTab();
		};
	},

	clickArttribute	: function () {
		if (this.currentTab == null) {
			return false;
		}
		var handle	= this;
		var thisparent = getObj(this.currentTab).parentNode;

		document.onclick = function (e) {
			var o = is_ie ? window.event.srcElement : e.target;
			while (o) {
				if (o.tagName && o.tagName.toLocaleLowerCase() == 'body') {
					break;
				}
				if (o == thisparent || o == getObj('pwd_postlist_hidden') || o == getObj('pwd_forumlist_hidden')) {
					return true;
				}
				o = o.parentNode;
			}
			handle.hiddenTab();
		}
	},
	resetClick	: function () {
		document.onclick	= '';
	},

	readMessage : function (messagetype,mid) {
		var msgid = 'msg_'+mid;
		var removeObj = getObj(msgid);
		removeObj.parentNode.removeChild(removeObj);
		if(getObj('newmessagebox').childNodes.length == 0){
			getObj('newmessagebox').innerHTML = '<li><a href="message.php" target="_blank">您没有未读消息</a></li>';
			getObj('td_msg').style.color = '';
			getObj('td_msg').innerHTML = '短消息';
			getObj('pwb_message').className = 'app-m fl';
		}
		var msgnum = parseInt(getObj('msgnum').innerHTML.replace(/\(|\)/g,''));
		getObj('msgnum').innerHTML = msgnum - 1;
		getObj('msgnum_show').innerHTML = msgnum - 1;
		sendmsg('pw_ajax.php?action=readmessage&type='+messagetype+'&mid='+mid);
	},

	showToolBar : function(type){
		pw_bottom.hiddenTab('pwb_app');//bugs fix
		if (IsElement('startbar')) {
			thistoolbox = getObj('startbar');
			var date = new Date();
			if (type == 'close') {
				thistoolbox.style.display = 'none';
				getObj('startbar-close').style.display='none';
				getObj('startbar-open').style.display='';
				date.setTime(date.getTime()+86400000);
				document.cookie="toolbarhide=1;expires=" + date.toGMTString() + " path=/";
			} else if(type == 'open') {
				thistoolbox.style.display = '';
				getObj('startbar-close').style.display='';
				getObj('startbar-open').style.display='none';
				document.cookie="toolbarhide=1;expires=" + date.toGMTString() + " path=/";
			}
		}
	},

	delShortCut : function(fid){
		ajax.send('pw_ajax.php?action=shortcut&type=delete&fid='+fid,'',function(){
				var rText = ajax.request.responseText.split('\t');
				if (typeof(rText[1]) != 'undefined' && rText[1] == 'successno'){
					var oShortcut = getObj('shortcut_'+fid);
					oShortcut.parentNode.removeChild(oShortcut);
					var pwd_app = getObj('pwd_app_hidden');
					var lis = document.getElementsByTagName('li');
					for (var i = 0; i < lis.length; i++) {
						if (lis[i].id == 'shortcut_'+fid) {
							lis[i].parentNode.removeChild(lis[i]);
						}
					}
					showDialog('success','删除书签成功！');
				} else {
					showDialog('error','删除书签出错！');
				}
			}
		);
	}
}