/*
* app.insertImage 模块
* 图片插入模块
*/
Breeze.namespace('app.insertImage', function(B) {
	var win = window,doc = document,
	    defaultConfig = {
	        rspHtmlPath:imageConfig.url,
			tabs:(typeof imageConfig.tabs == 'undefined') ? ['network'] : imageConfig.tabs,
			tabname:{'local' : '本地图片', 'album':'相册图片','network':'网络图片'},
	        callback:function(){}
	    },
		albumList = null,
		isLoading = false,
		menuPop = {
			create : function() {
				var html = '<div class="B_menu B_p10B">\
	<div style="width: 480px;">\
		<div class="B_menu_nav B_cc B_drag_handle">\
			<a style="margin-top: 2px;" class="B_menu_adel B_close" href="#">×</a>\
			<ul class="B_cc">';
				defaultConfig.tabs.forEach(function(n){
					html += '<li id="tab_'+n+'" class="B_tab_trigger"><a href="javascript:;">'+defaultConfig.tabname[n]+'</a></li>';
				});
				html += '</ul>\
		</div>',
					i = 0;
				defaultConfig.tabs.forEach(function(n){
					html += menuPop[n](i++ == 0 ? '' : 'none');
				});
				html += '</div>\
	<input type="hidden" id="verifyhash" value="" />\
</div>';
				return html;
			},
			local : function(show) {
				return '<div class="B_photo_con B_tab_panel" style="display:'+show+'">\
			<div class="B_mb10 B_cc" style="height:25px;">\
				<span class="B_fr" id="B_picuploader_savetoalbum">\
					<label class="B_mr5"><input type="checkbox" name="savetoalbum" value="1">同时保存到</label>\
					<select name="albumid" disabled><option>选择相册</option></select>\
				</span>\
				<span id="B_picuploader_container"><span id="B_picuploader_flash"><span id="uploaderTmpSpan" style="display:none;"><embed style="display:none;" src="images/blank.swf" type="application/x-shockwave-flash" wmode="transparent"/><em class="s2" style="position:relative;top:3px;">该浏览器尚未安装flash插件，<a href="http://www.adobe.com/go/getflashplayer" target="_blank">点击安装</a></em></span></span></span>\
			</div>\
			<div class="B_mb5">\
				<div class="B_file_imgTip B_cc">\
					图片列表&nbsp;<span>(您还可以上传<span class="restCount s4"></span>张图片)</span>\
				</div>\
				<div class="B_file_img">\
					<ul id="B_image_tile" class="B_cc"><li style="width:0;overflow:hidden;height:0px;padding:0;margin:0;"></li></ul>\
				</div>\
			</div>\
			<div class="B_file_tips">\
				<a class="B_helpA" style="float:right;padding:0 0 0 18px;width:auto;" onclick="event&&(event.returnValue=false)">可上传类型<i id="image_allow_filetype"></i></a>\
				<div class="B_helpB">点击已上传图片插入到帖子</div>\
			</div>\
		</div>';
			},
			album : function(show) {
				return '<div class="B_photo_con B_tab_panel" style="display:'+show+';">\
		<div class="B_album_list B_mb5" style="display:none">\
			<select id="album_list" name="aid"></select>\
		</div>\
		<div class="c"></div>\
		<div class="popupList" id="noPhotoList" style="display:none">\
			<div class="p10">相册下还没有照片，请先 <a href="apps.php?q=photos&a=upload&job=flash" class="s4" target="_blank">上传</a></div>\
		</div>\
		<div class="B_popupList B_mb10" id="photoList">\
				<ul id="album" class="B_popUlone B_cc">\
				</ul>\
			</div>\
			<div class="B_helpB">点击图片插入到帖子</div >\
		</div>';
			},
			network : function(show) {
				return '<div class="B_photo_con B_tab_panel" style="display:'+show+'">\
			<div style="padding-bottom:30px;">\
				<div class="B_tac B_p15 B_cc">图片地址：<input id="networkImg" size="50" type="text" class="input" value="http://" /></div>\
				<div class="B_tac"><span class="B_btn2"><span><button type="button" id="btn_insertImg">插入图片</button></span></span></div>\
			</div>\
		</div>';
			}
		},
		imageSelector = {
			id :'editor-insertImage',
			load : function(elem) {
				var id = this.id;
				B.require('util.dialog', function(B) {
					B.util.dialog({
						pos: ['leftAlign','bottom'],
						id: id,
						data: menuPop.create(),
						reuse: true,
						callback: function(popup) {
							InsertImage(myeditor);//事件处理类
							initList();
							myeditor.area.appendChild(popup.win);//转移弹窗位置
						}
					}, elem);
				});
			}
		};
	    
	/**
     * 隐藏面板
     */   
	function hideImageSelector() {
        B.$('#' + imageSelector.id).style.display = 'none';
    }
    //给相册图片添加选中事件
    function addPhotoClick() {
        B.require('event',function() {
            B.$$('#' + imageSelector.id +' #photoList li').forEach(function(n) {
                n['onmousedown'] = function(e){
					insertTrigger('<img src="' + B.$('input', this).value + '" />');
					//hideImageSelector();
					//this.className = this.className == 'current' ? '' :'current'
				}
            });
        });
    }
    /**
     * 获取相册中的相片
     */ 
    function getPhotosByAlbumId(albumId) {
        B.require('request',function(B) {
            B.ajax({
                type:'post',
                url:defaultConfig.rspHtmlPath + '&verify='+window.verifyhash,
                data: {job:'listphotos',aid:albumId},
                success :function(data) {
                    var rText = data.split('\t');
					if (rText[0] == 'success') {
						var photos = B.parseJSON(rText[1]);
						showPhotos(photos);
					}else{
						showPhotos();
					}
                }
            });
        });
    };

	function activateAlbum() {
		var selector = '#'+ imageSelector.id;
		if (this.checked === true && B.$(selector + ' #B_picuploader_savetoalbum select').disabled === true) {
			B.$(selector + ' #B_picuploader_savetoalbum select').disabled = false;
			if (albumList == null) {
				B.require('request', function(B){
					B.ajax({
						type:'GET',
						url:defaultConfig.rspHtmlPath + '&job=listalbum&verify='+window.verifyhash,
						cache:false,
						success :function(data) {
							var rText = data.split('\t');
							if (rText[0] == 'success') {
								albumList = B.parseJSON(rText[1]);
							} else {
								albumList = [];
							}
							showSaveAlbumList();
						}
					});
				});
			} else {
				showSaveAlbumList();
			}
		}
	}

	function showSaveAlbumList() {
		var o = B.$('#'+ imageSelector.id + ' #B_picuploader_savetoalbum select');
		if (albumList.length > 0) {
			o.options.length = 0;
			var i = 0;
			albumList.forEach(function(n){
				o.options[i++] = new Option(n[1], n[0]);
			});
		}
	}

	function showAlbumList() {
		B.query('.B_album_list').css('display', '');
		var i = 0;
		albumList.forEach(function(n){
			B.$('#album_list').options[i++] = new Option(n[1], n[0]);
		});
	}

	function showPhotos(photos) {
		if (photos && B.isArray(photos) && photos.length > 0) {
			var albumHtml = '';
			photos.forEach(function(n){
				albumHtml += '<li><span></span><label><img src="'+ n.thumbpath +'" width="56" height="56" /><input type="checkbox" value="' + n.path + '" /></label></li>';
			});
			B.$('#album').innerHTML = albumHtml;
			B.$('#photoList').style.display='';
			B.$('#noPhotoList').style.display='none';
			addPhotoClick(B.$('#photoList'));
		} else {
			B.$('#photoList').style.display='none';
			B.$('#noPhotoList').style.display='';
		}
	}

	function loadImageList() {
		if (isLoading) return;
		B.require('request', function(B){
			B.ajax({
				type:'GET',
				url:defaultConfig.rspHtmlPath + '&verify='+window.verifyhash,
				cache:false,
				success :function(data) {
					isLoading = true;
					var rText = data.split('\t');
					if (rText[0] == 'success') {
						albumList = B.parseJSON(rText[1]);
						showAlbumList();
						var photos = B.parseJSON(rText[2]);
						showPhotos(photos);
					} else {
						albumList = [];
						showPhotos();
					}
				}
			});
		});
	}
	/**
	 * 初始化列表
	 */
	function initList(){
	}
    /**
     * ImageInsert类
     */   
    function InsertImage() {
        var _self = this;
        if( !(_self instanceof InsertImage) ) {
		    return new InsertImage(myeditor);
	    }
        B.require('event',function(B) {//add event for ImageInsert
            var selector = '#'+ imageSelector.id,
				cfgs = B.$$(selector + ' .B_tab_trigger');
			B.addClass(cfgs[0], 'current');

			if (B.$(selector + ' #tab_local')) {
				B.addEvent(B.$(selector + ' #B_picuploader_savetoalbum input'), 'click', activateAlbum);
				if (B.hasClass(B.$(selector + ' #tab_local'), 'current')) {
					B.require('global.uploader', function(){
						uploader.init('picuploader',myeditor);
					});
				}
			}
			if (B.$(selector + ' #tab_network')) {
				//最终点击插入图片按钮事件
				B.$('#btn_insertImg').onclick = function() {
					insertTrigger('<img src="' + B.$('#networkImg').value + '" />');
				};
			}
			if (B.$(selector + ' #tab_album') && B.hasClass(B.$(selector + ' #tab_album'), 'current')) {
				loadImageList();
			}
			if (B.$('#album_list')){
				B.$('#album_list').onchange = function(){
					getPhotosByAlbumId(this.value);
				};
				/**
				 * 产生相册中图片延迟加载效果
				 */
				B.require('util.lazyload',function(B) {
					B.util.lazyload('img',{
						container: B.$('#photoList')
					});
				});
			}
            /**
             * 产生tab效果
             */
			B.require('util.scrollable',function(B) {
				B.util.tabs("#"+imageSelector.id);
			});
            
            /**
             * 切换tab时触发
             */
			cfgs.forEach(function(n) {
				B.addEvent(n, 'click', function(e) {
					cfgs.forEach(function(n) {
						B.removeClass(n,'current');
					});
					B.addClass(this, 'current');
					if(this.id === 'tab_local'){
						B.require('global.uploader', function(){uploader.init('picuploader',myeditor);});
					} else if (this.id === 'tab_album') {
						loadImageList();
					}
					e.preventDefault();
                });
            });
            //window.verifyhash = B.$('#verifyhash').value;//服务器输出的verifyhash,ajax需要(为兼容PW)
            /**
             * 关闭面板事件
             */
            B.$$(selector + ' .B_menu_adel').forEach(function(n) {
                B.addEvent(n,'click',function(e) {
                    e.preventDefault();
                    hideImageSelector();
                });
            });
        });
	}
	/**
	 * @description 图片选择器
	 * @params {String} 要产生图片选择器的元素
	 * @params {Function} 点击图片后产生的回调函数
	 */
	B.app.insertImage = function(elem, callback, editor) {
	    insertTrigger = callback;
		myeditor = editor;
		if (B.$('#'+imageSelector.id)){//如果HTML已经生成，那么直接弹出框，无需初始化组件
			B.util.dialog({
				id:imageSelector.id,
				reuse: true,
				callback:function(){
					if (B.$('#' + imageSelector.id + ' #tab_local') && B.hasClass(B.$('#tab_local'), 'current')) {
						uploader.init('picuploader',myeditor);
					}
				},
				pos: ['leftAlign', 'bottom']
			}, elem);
		} else {
			imageSelector.load(elem, myeditor);
		}
    }
});