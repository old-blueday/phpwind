/*<![CDATA[*/

/**
 * <p>
 * Title: 数据列表生成
 * </p>
 * <p>
 * @{#} list.js Created on 2009-21-04 18:01:53
 * </p>
 * <p>
 * Description:  根据指定的模块和数据列生成页面数据列表,如用户列表.
 * </p>
 * <p>
 * </p>
 * 	更新功能，已支持直接写函数，函数体写在区域<f></f>之间
 * 2009-1-8 增强函数体内的{var}的转义\{检测，\{将不列入变量解析范围。$0-9不做解析，因为函数体可能用到正则表达式
 * 匿名函数可缩写为f(){}，return可缩写为r。系统自动检测r的书写位置(两边非数字)是否可以被替换为return;
 * @version 1.0
 */
window.desk?0:desk={};
window.G?0:G={};
String.prototype.r=function(f,r)
{
	var c=this+"";
	if(f instanceof Array)
	{
		for (var i=0; i<f.length; i++)
		{
			c=c.replace(f[i],r[i]);
		}
	}
	else
	{
		c=c.replace(f,r);
	}
	return c;
};
desk.list={

	/**
	 * 根据指定的数据列生成页面数据列表,如用户列表.
	 * @param {String}	 data 		列表数据来源,数组列表
	 * @param {String}	 module 	列表生成规则模板字符串,模板字段以{数据字段名}传递,然后进行匹配替换.
	 *								{this}表示数据对象本体，要传递参数，在函数后用|分割开，如：cDate|__object__注意：函数体内不能出现|符号，应当转义处理
	 * @param {String}	 str 		如无数据来源或数据来源为空时的显示默认值
	 * @return {String}  			用于显示数据列表的HTML代码,直接innerHTML即可. 
	 */
simple: function(j, m, n) {
    var s = "",q_=[];
	var backup_j=j;
	var feedBack;
	if(j.json)
	{
		m=j.module;
		n=j.nullText;
		feedBack=j.each;
		j=j.json;

	}
    var q = m = m.r(/\s{2,}/g,' ').r(/\n/g,'');
    var r = a = d = "";
    for (var i = 0,l = j.length; i < l; i++) {
        try {
            j[i].list_id = i
        } catch(e) {
            j[i] = [j[i]]
        }
        //分割2010-2-26 by lh
        if(i==7){
        	s += "<li id='admin_hr'></li>";
    		q_.push(returnString);
        }
        m.r(/(^|[^\\])((?:\$\d+?)|(?:\{[\w|\|\'|\"]+\}))(?:<f>([\s|\S]+?)\<\/f\>)?/g,
        function(o, l, z, c) {
            z = z.r("$", "").r("{", "").r("}", "");
            a = [(c || "")];
            d = j[i];
            var m = z.split("||");
            var f = '';
            for (var p = 0,le = m.length; p < le; p++) {

                m[p]?f = f || (/[\'|\"]/.test(m[p])?eval(m[p]):d[m[p]]):0;
            }
			
            d[z] = f;
            var g = "";
            if (a[0] == "") {
                g = ""
            } else {
				
                G.TO = j[i];
                var b = a[0].match(/[^\\]\{[\w|\|]+\}/g) || [];
                for (var k = 0, le = b.length; k < le; k++) {
                    b[k] = b[k].r(/^[^\{]/, '');
                    if (b[k] == "{this}") {
                        a[0] = a[0].r(/\{this\}/g, "G.TO")
                    } else {
                        a[0] = a[0].r(eval("/" + b[k].r(/\|/g, "\\|").r("{", "\{").r("}", "\}") + "/g"), d[b[k].r("{", "").r("}", "")])
                    }
                } 
                var h;
                if (a[1] == "__object__" || a[1] == "this") {
                    h = "G.TO"
                } else {
                    h = "'" + d[a[1]] + "'"
                } 
                try {
                    if (a[0].indexOf(".") == 0) {
                        g = eval("('" + (d[z] || "").toString().r(/\'/g, "\'") + "')" + a[0])
                    } else {
							
                        g = eval("(" + a[0].r(/^f\(/, 'function(').r(/([\W]|[\{])r\s+/, '$1return ') + ")('" + (d[z] || "").toString().r(/\'/g, "\'") + "'" + (a[1] ? "," + h + "": "") + ")")
                    }
                } catch(e) {
                    g = ""
                }
            }
            q = q.r(o, l + (c ? g: (d[z] || "").toString()))
        });
		var returnString= q.r([/@LK;/g, /@RK;/g, /@LT;/g,/@GT;/g],["{","}","<",">"]);
        s += returnString;
		q_.push(returnString);

        q = m;

    }
	var fd=function(){if(backup_j.json.length==0){return feedBack(backup_j.nullText)}feedBack(q_.splice(0,backup_j.once||3).join(""));q_.length?setTimeout(fd,backup_j.timer||50):(backup_j.onload?backup_j.onload():0);};
		feedBack?fd():0;
    return j.length > 0 ? s: n
}
};

/*]]>*/