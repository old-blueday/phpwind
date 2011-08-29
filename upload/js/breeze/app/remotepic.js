/*
* app.remotepic 模块
* 远程图片下载模块
*/
Breeze.namespace('app.remotepic', function (B) {
	function closeAll(){
		B.query('.B_menu').css('display', 'none');
	}
	var $ = B.query, index = 1;
	var win = window, doc = document,
		defaultConfig = {
			rspHtmlPath: attachConfig.url,
			callback: function () { }
		},
		tattachSelector = {
			id: 'editor-remotepic',
			hideInter:null,
			pics:{key:[],val:[]},
			load: function (elem, myeditor) {
				var self=this;
				var id = this.id;
				B.require('util.dialog', function (B) {
					B.util.dialog({
						pos: ['leftAlign', 'bottom'],
						id: id,
						data: '<div class="B_menu B_p10B">\
								<div style="width:200px;">\
									<div class="B_h B_cc B_drag_handle">\
										<a href="javascript://" class="B_menu_adel B_close" style="margin-top:2px;">×</a>远程图片下载\
									</div>\
									<!--附件列表开始-->\
									<div style="padding-top:5px">\
										<div class="B_remote">\
											<div class="cc" style="padding-top:30px;" id="B_remote_cont">\
												<img src="images/loading_r.gif"/><br/><br/>下载中...\
											</div>\
											<span class="B_remote_tip" id="B_remote_tip"></span>\
										</div>\
									</div>\
									<!--结束-->\
								</div>\
							</div>',
						reuse: true,
						callback: function (popup) {
							myeditor.area.appendChild(popup.win);
							var ubb=myeditor.getUBB();
								self.parseHTML(ubb);
						}
					}, elem);
				});
			},
			parseHTML:function(str){
				//初始化底部提示信息
				getObj("B_remote_tip").innerHTML="";
				var self=this;
				var pics=this.pics.key;
				var urls=[];
				var tipsStr;
				var s=str.replace(/\[img\]\s*(?=http)(((?!")[\s\S])+?)(?:"[\s\S]*?)?\s*\[\/img\]/ig,function(all,url){
					//处理远程附件
					if(attachConfig&&attachConfig.remoteAttUrl){
						var remoteUrl=attachConfig.remoteAttUrl;
						if(url.indexOf(remoteUrl)>-1){
							return false;
						}
					}
					//end
					pics.push(all);
					urls.push(url);
				});
				if(urls.length<1){
					getObj("B_remote_cont").innerHTML="无远程图片";
					tattachSelector.hideInter=setTimeout(function(){
						getObj(self.id).style.display="none";
					},3000)
					return false;
				}
				self.loadImg(str,urls);
			},
			getPostPics:function(pics){
				var data = '';
				for (var i=0,len=pics.length;i<len;i++){
					if (data) data += '&';
					data += 'urls['+ i +']=' + pics[i];
				}
				if(attachConfig&&attachConfig.type){
					data+="&type="+attachConfig.type;
				}
				if(attachConfig&&attachConfig.postData){
					var postdata=attachConfig.postData;
					for(var i in postdata){
						data+="&"+i+"="+postdata[i];
					}
				}
				return data;
			},
			loadImg:function(str,pics){
					var self=this;
					var mode=myeditor.currentMode;
					getObj("B_remote_cont").innerHTML='<img src="images/loading_r.gif"/><br/><br/>远程图片下载中,请稍候...';
					B.require('global.uploader',function(){
						var availLen=uploader.getRestCount();
						if(availLen<1){
							getObj("B_remote_tip").innerHTML="";
							getObj("B_remote_cont").innerHTML='<span style="color:#f00">你已经达到下载上限</span>';
							tattachSelector.hideInter=setTimeout(function(){
									getObj(self.id).style.display="none";
							},3000)
							return false;
						}
						if(pics.length>availLen){
							tipsStr="数量超出上传限制,仅能下载"+availLen+"张";
							//tipsStr="共有"+pics.length+"张远程图片，可下载"+availLen+"张";
							getObj("B_remote_tip").innerHTML=tipsStr;
							pics=pics.slice(0,availLen);
						}
						var data = self.getPostPics(pics);
						
						ajax.send("job.php?action=remotedownload",data,function(){
								var index=0;
								var failNum=0;
								var successNum=0;
								try{
									
									/*var end=ajax.request.responseText.lastIndexOf(",");
									ajax.request.responseText=ajax.request.responseText.substr(0,end);*/
									var json="{"+ajax.request.responseText+"}";
									var json=eval("("+json+")");
									//格式  var json={'80' : ['Chrysanthemum.jpg', '859', 'attachment/Mon_1107/5_1_c32ab805d9c8963.jpg', 'img', '0', '0', '', ''],'81' : ['Desert.jpg', '827', 'attachment/Mon_1107/5_1_39725fd5f4ae645.jpg', 'img', '0', '0', '', ''],'82' : ['Hydrangeas.jpg', '582', 'attachment/Mon_1107/5_1_cfca2e0916365b4.jpg', 'img', '0', '0', '', ''],'83' : ['Jellyfish.jpg', '758', 'attachment/Mon_1107/5_1_742edcbdf53cb76.jpg', 'img', '0', '0', '', ''],'84' : ['Koala.jpg', '763', 'attachment/Mon_1107/5_1_813237795e63c97.jpg', 'img', '0', '0', '', ''],'85' : ['Lighthouse.jpg', '549', 'attachment/Mon_1107/5_1_65124265c1a957a.jpg', 'img', '0', '0', '', ''],'86' : ['Penguins.jpg', '760', 'attachment/Mon_1107/5_1_ff09342db99f3bb.jpg', 'img', '0', '0', '', ''],'87' : ['Tulips.jpg', '607', 'attachment/Mon_1107/5_1_acdf5acb0afe2f2.jpg', 'img', '0', '0', '', '']};
									var editor_remote_holder=document.createElement("div");
									for(var i in json){
										index++;
										//超过限制
										if(index>availLen){
											return false;
										}
										//如果图片下载失败
										if(json[i].length<1){
											self.pics.val.push("");
											failNum++;
											continue;
										}
										//将数据和uploader同步
										uploader.data[i]=json[i];
										self.pics.val.push(json[i][0]);
										str=str.replace(self.pics.key[successNum],"[attachment="+i+"]");
										
										var input=document.createElement("input");
										input.setAttribute("type","hidden");
										input.name="flashatt["+i+"][desc]";
										input.id="tmpRemoteHidden"+i;
										editor_remote_holder.appendChild(input);
										successNum++;
									}
									uploader.amount+=successNum;
									myeditor.area.appendChild(editor_remote_holder);
									
									if(mode=="default"){
										myeditor.setHTML(myeditor.ubb2html(str));
									}else if(mode=="UBB"){
										myeditor.setHTML(str);
									}
									var failStr=failNum>0?"下载失败"+failNum+"个<br/>失败可能原因：图片尺寸过大或数量超出限制":"";
									getObj("B_remote_cont").innerHTML='<img src="images/success_bg.gif"/><br/><br/>远程图片下载完成.'+failStr;
									//getObj("B_remote_cont").innerHTML='<img src="images/success_bg.gif"/><br/><br/>远程图片下载完成.共'+index+'个'+failStr;
									self.pics={key:[],val:[]};
									tattachSelector.hideInter=setTimeout(function(){
										getObj(self.id).style.display="none";
									},3000)
								}catch(e){
									getObj("B_remote_cont").innerHTML="远程图片下载失败.<br/>失败可能原因：图片尺寸过大或数量超出限制";
									tattachSelector.hideInter=setTimeout(function(){
										getObj(self.id).style.display="none";
									},3000)
								}	
						});
					});	
			}
		};
    /**
    * @description 图片选择器
    * @params {String} 要产生附件选择器的元素
    * @params {Function} 选择附件后产生的回调函数
    */
	B.app.remotepic = function (elem, callback, editor) {
		insertTrigger = callback;
		myeditor = editor;
		if(tattachSelector.hideInter){
			clearTimeout(tattachSelector.hideInter);
		}
		if (B.$('#'+tattachSelector.id)){
			B.util.dialog({
				id: tattachSelector.id,
				reuse: true,
				callback:function(){
							var ubb=myeditor.getUBB();
								tattachSelector.parseHTML(ubb);
				},
				pos: ['leftAlign', 'bottom']
			}, elem);
		} else {
			tattachSelector.load(elem, myeditor);
		}
    }
});

/*
此组件涉及到先通过ajax加载HTML,所以事件处理类InsertAttach在tattachSelector.load()中实例化,这与colorpicker有点不同,分开来html和event为了更容易维护和阅读
*/