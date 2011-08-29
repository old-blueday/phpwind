/*
* ajaxForm 模块
* 使form提交变成无刷新式的
*/
Breeze.namespace('util.ajaxForm', function (B) {

    function AjaxForm(form, callback) {
        this.form = form;
        this.callback = callback;
        if (!this.form || this.form.tagName !== 'FORM') {//必须为form元素
            return false;
        }
        this._initialize();
    }

    AjaxForm.prototype = {
        _load: function (frame) {
            if (frame.contentDocument) {
                var d = frame.contentDocument;
            } else if (frame.contentWindow) {
                var d = frame.contentWindow.document;
            }
			var data = (typeof d.documentElement != 'undefined') ?
					d.documentElement.textContent :
					d.body.innerHTML;
            this.callback(data);
        },
        _initialize: function () {
            var self = this, form = self.form, callback = self.callback,
                    n = new Date().getTime(),
					f = B.createElement('<iframe style="display:none" src="javascript:void(0);" id="' + n + '" name="' + n + '"></iframe>');

			if (f.attachEvent){
				f.onreadystatechange = function () {
                    if ( f.readyState == "complete" &&  f.src != 'javascript:void(0);' ) {
						self._load(f);
                    }
                }
			} else {
				f.onload = function (){
                    self._load(f);
                }
			}
			document.body.appendChild(f);
/*		            d = document.createElement('div');
            d.innerHTML = 
            document.body.appendChild(d);
            var frame = document.getElementById(n);
			if (frame.attachEvent) {
                frame.onreadystatechange = function () {
                    if (frame.readyState == "complete") {
						if(frame.src != 'javascript:void(0);' )
							self._load(frame);
                    }
                }
            } else {
                frame.onload = function () {
                    self._load(frame);
                }
            }
*/
            form.setAttribute('target', n);
            form.method = 'post';
        }
    }

    /**
    * @description 无刷新表单
    * @params {String} 要产生无刷新表单的form
    * @params {Function} 提交成功后的回调函数,回调函数的参数为服务器端输出的html
    */
    B.util.ajaxForm = function (form, callback) {
        form = typeof form === 'string' ? B.$(form) : form;
        new AjaxForm(form, callback);
    };
});

/*
按自己的思路写的一个简洁的AJAX FORM提交,因request模块中已经包含ajax数据提交,故这里不处理,这里只单纯的对form做提交
*/