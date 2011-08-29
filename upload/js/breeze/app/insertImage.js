/*
* app.insertImage 模块
* 图片插入模块
*/
Breeze.namespace('app.insertImage', function(B) {
	var win = window,doc = document,
	    defaultConfig = {
	        rspHtmlPath:'demo/php/m_photos_editor.html',
	        callback:function(){}
	    },
	    
	    imageSelector = {
	        id :'editor-insertImage',
	        load : function(elem) {
	            var id = this.id;
	            B.require('request','util.dialog',function(B) {
	                B.ajax({
	                    url:defaultConfig.rspHtmlPath,
	                    dataType:'html',
	                    cache:false,
	                    success:function(data) {
                            B.util.dialog({
                                pos: ['leftAlign','bottom'],
                                id: id,
                                data:data,
                                reuse: true,
                                callback:function(popup) {
                                    InsertImage();//事件处理类
                                }
                            },elem);
	                    }
	                });
	            });
	        }
	    }
	    
	
	
	/**
     * 隐藏面板
     */   
	function hideImageSelector() {
        B.$('#' + imageSelector.id).style.display = 'none';
    }
    //给相册图片添加选中事件
    function addPhotoClick(contain) {
        B.require('event',function() {
            B.$$('#' + imageSelector.id +' #photoList li').forEach(function(n) {
                n['onmousedown'] = function(e){this.className = this.className == 'current' ? '' :'current'}
            });
        });
    }
    
    function createAlbum() {
        var aname = B.$('#albumName').value,
            gdcode,qanswer,qkey;
        if(B.$('#gdcode')) { gdcode = B.$('#gdcode').value; }
        if(B.$('#qanswer')) { qanswer = B.$('#qanswer').value; }
        if(B.$('#qkey')) { qkey = B.$('#qkey').value; }
        if(aname=='') { alert('请输入相册名!');B.$('#albumName').focus();return false; }
        if(B.$('#gdcode') && B.$('#gdcode').value === '') {
            alert('请输入验证码!');B.$('#gdcode').focus();return false;
        }
        if(B.$('#qanswer') && B.$('#qanswer').value === '') {
            alert('请输入问题答案!');B.$('#qanswer').focus();return false;
        }
        
        B.ajax({
            type:'post',
            url:'apps.php?q=photos&a=create&verify='+window.verifyhash,
            data: {step:2,checkpwd:1,private:0,aname:aname,gdcode:gdcode,qanswer:qanswer,qkey:qkey},
            success:function(data) {
                var rText = data.split('\t');
                if (rText[0] == 'success') {
                    var albumList = B.$('#album_list');
		            albumList.options.add(new Option(aname,rText[1]),0);
		            albumList.value=rText[1];
		            B.$('#albumName').value='';
		            B.$('#create_album_div').style.display='none';
                }else if(rText[0] === 'limit_num'){
                    alert('您创建的相册已经达到'+rText[1]+'个');
                }else {
                    alert(rText[1] != '' ? rText[1] : '未知错误');
                }
            }
        });
    }
    /**
     * 获取相册中的相片
     */ 
    function getPhotosByAlbumId(albumId) {
        B.require('request',function(B) {
            B.ajax({
                type:'post',
                url:'apps.php?q=photos&a=pweditor&verify='+window.verifyhash,
                data: {action:'listphotos',private:0,aid:albumId},
                success :function(data) {
                    var rText = data.split('\t'),
                        albumHtml = '';
                        if (rText[0] == 'success') {
			                try{
			                    var photos = B.JSONParse(rText[1]);
			                    if (typeof(photos)=='object') {
				                    for (var i in photos) {
					                    if (typeof(photos[i])=='object') {
						                    albumHtml += '<li><span></span><label><img data_src="'+ photos[i].thumbpath +'" width="56" height="56" /><input type="checkbox" value="' + photos[i].path + '" /></label></li>';
					                    }
				                    }
				                    B.$('#album').innerHTML = albumHtml;
				                    B.$('#photoList').style.display='';
				                    B.$('#noPhotoList').style.display='none';
			                    }
			                }catch(e){}
			                addPhotoClick(B.$('#photoList'));
		                }else{
			                B.$('#photoList').style.display='none';
			                B.$('#noPhotoList').style.display='';
		                }
                }
            });
        });
    };
    
    
    /**
     * ImageInsert类
     */   
    function InsertImage(elem) {
        var self = this;
        if( !(self instanceof InsertImage) ) {
		    return new InsertImage();
	    }
        B.require('event',function(B) {//add event for ImageInsert
            var selector = '#'+ imageSelector.id;
            addPhotoClick(B.$(selector));
            
            //点击创建相册按钮时显示创建区域
            B.addEvent(B.$(selector + ' #btn_createAlbum'),'click',function() {
                B.$(selector + ' .vt').style.display = 'block';
            });
            //验证码框第一次取得焦点时显示验证码图片
            B.addEvent(B.$('#gdcode'),'focus',function() {
                var s = B.$('#ckcode');
                if(s.style.display === 'none') {
                    s.src = 'http://127.0.0.1/pwdev/ck.php?nowtime=' + new Date().getTime();
                    s.style.display = '';
                }
            });
            
            //点击取消隐藏创建相册元素
            B.addEvent(B.$('#cancel_cerate'),'click',function() {
                B.$('#create_album_table').style.display = 'none';
            });
            
            //点击创建按钮产生事件
            B.addEvent(B.$('#btn_create'),'click',function() {
                createAlbum();
            });
            //创建相册时的验证码更新
            B.addEvent(B.$('#ckcode'),'click',function() {
                this.src = 'http://127.0.0.1/pwdev/ck.php?nowtime=' + new Date().getTime();
            });
            
            //相册切换时载入相片
            B.addEvent(B.$('#album_list'),'change',function() {
                getPhotosByAlbumId(this.value);
            });
            //最终点击插入图片按钮事件
            B.$('#btn_insertImg').onclick = function() {
                var t = B.$(selector + ' li.current').title;
                switch(t)
	            {
		            case 'album':
			            B.$$('#photoList li.current input').forEach(function(n) {
			                insertTrigger('<img src="' + n.value + '" />');
			            });
			            break;
		            case 'local':
			            var form = B.$('#uploadPhoto');
			            form.action = 'php/form.ashx?q=photos&a=pweditor&verify='+ parent.verifyhash + '&action=upload',
			            B.require('util.ajaxForm',function(B) {
			                B.util.ajaxForm(form,function(data) {
			                    insertTrigger('<img src="' + data + '" />');
			                });
			            });
			            break;
		            case 'network':
			            insertTrigger('<img src="' + B.$('#networkImg').value + '" />');
			            break;
		            default:
	            }
            };
            
            
            /**
             * 产生tab效果
             */
            B.require('util.scrollable',function(B) {
                B.util.tabs("#"+imageSelector.id);
            });
            
            /**
             * 产生相册中图片延迟加载效果
             */
            B.require('util.lazyload',function(B) {
                B.util.lazyload('img',{
                    container:B.$('#photoList')
                });
            });
            
            /**
             * 切换tab时触发
             */
            B.$$(selector + ' .B_tab_trigger').forEach(function(n) {
                B.addEvent(n,'click',function(e) {
                    B.$$(selector + ' .B_tab_trigger').forEach(function(n) {
                        B.removeClass(n,'current');
                    });
                    B.addClass(this,'current');
                });
            });
            window.verifyhash = B.$('#verifyhash').value;//服务器输出的verifyhash,ajax需要(为兼容PW)
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
	B.app.insertImage = function(elem,callback) {
	    insertTrigger = callback;
		imageSelector.load(elem);
    }
});