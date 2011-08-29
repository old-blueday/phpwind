<?php
!function_exists('readover') && exit('Forbidden');
$lang = array(
	'back'				=> '返 回',

	'chinaz'			=> '添加ChinaZ.com的图片友情链接',
	'chinaz_alt'		=> '中国站长站——为中文网站提供动力',
	'chinaz_logo'		=> 'http://www.chinaz.com/images/chinaz.gif',
	'chinaz_name'		=> '中国站长站',
	'chinaz_url'		=> 'http://www.chinaz.com',
	'ckpwd'				=> '重复密码',

	'dbattachurl'		=> '附件url地址，以http:// 开头的绝对地址，为空使用默认',
	'dbcharset'			=> "Mysql编码设置(常用编码：gbk、big5、utf8、latin1){$crlf}如果您的论坛出现乱码现象，需要设置此项来修复{$crlf}请不要随意更改此项，否则将可能导致论坛出现乱码现象",
	'dbhost'			=> '数据库服务器',
	'dbhostweb'			=> '镜像站点设置',
	'dbifhostweb'		=> '是否为主站点',
	'dbinfo'			=> '以下变量需根据您的数据库服务器说明档修改',
	'dbmanager'			=> "创始人将拥有论坛的所有权限，自pwforums v6.3起支持多重创始人，用户名密码更改方法：{$crlf}方法1、将./data/sql_config.php的文件属性设置为777（非NT服务器）或取消只读并将用户权限设置为Full Control（完全控制，NT服务器），然后用原始创始人帐号登录后台，在更改论坛创始人处进行相关添加修改操作，操作完毕后，再将./data/sql_config.php的文件属性设置为644（非NT服务器）或只读（NT服务器）。（推荐）{$crlf}方法2、用记事本打开./data/sql_config.php文件，在“创始人用户名数组”中加入新的用户名，如“\$manager = array('admin');”更改为“\$manager = array('admin','phpwind');”，在“创始人密码数组”中加入新的密码，如“\$manager_pwd = array('21232f297a57a5a743894a0e4a801fc3');”更改为“\$manager_pwd = array('21232f297a57a5a743894a0e4a801fc3','21232f297a57a5a743894a0e4a801fc3');”，其中“21232f297a57a5a743894a0e4a801fc3”是密码为admin的md5的加密串，您可以创建一个新的文件在根目录（test.php），文件内容为 \"<?php echo md5('您的密码');?>\" ，在地址栏输入http://你的论坛/test.php获得md5加密后的密码，用完记得删除文件test.php。",
	'dbmanagername'		=> "创始人用户名数组",
	'dbmanagerpwd'		=> '创始人密码数组',
	'dbname'			=> '数据库名',
	'database'			=> '数据库类型',
	'dbpconnect'		=> '是否持久连接',
	'dbpre'				=> '表区分符',
	'dbpres'			=> '数据库表前缀',
	'dbpw'				=> '数据库密码',
	'dbuser'			=> '数据库用户名',

	'error_777'			=> '<b>{#filename}</b> 文件或文件夹777属性检测不通过',
	'error_777s'		=> '<b>{#filenames}</b> 等文件或文件夹777属性检测不通过',
	'error_admin'		=> '将admin.php改成{#db_adminfile}后才能继续升级',
	'error_adminemail'	=> '创始人电子邮箱不能为空',
	'error_adminname'	=> '创始人用户名不能为空',
	'error_adminpwd'	=> '创始人密码不能为空',
	'error_ckpwd'		=> '两次输入密码不同',
	'error_delrecycle'	=> '请登录后台删除回收站版块，在前台版主管理或后台回收站管理进行帖子操作',
	'error_nodatabase'	=> '您没有该 <b>{#dbname}</b> 数据库的操作权限或指定的数据库 <b>{#dbname}</b> 不存在,且您无权限建立,请联系服务器管理员!',
	'error_dbhost'		=> '数据库服务器不能为空',
	'error_dbname'		=> '数据库名不能为空',
	'error_dbpw'		=> '你填的数据库密码为空，是否使用空的数据库密码',
	'error_dbuser'		=> '数据库用户名不能为空',
	'error_delinstall'	=> '系统无法删除{#basename}，请登录FTP删除此文件',
	'error_forums'		=> '版块ID错误，非法操作!',
	'error_forums1'		=> '论坛分类一不能为空',
	'error_forums2'		=> '论坛版块一不能为空',
	'error_forums3'		=> '若填写了论坛版块二，论坛分类二不能为空',
	'error_forums4'		=> '若填写了论坛分类二，论坛版块二不能为空',
	'error_mysqli'		=> '注意：您的服务器的配置低于MySQL4.1.3版本，不允许使用 mysqli，程序自动设置为 mysql',
	'error_nothing'		=> '任意项目没有填写',
	'error_table'		=> '{#pw_table}已经存在而非PW论坛数据库，请在数据库管理里改名或删除{#pw_table}表，再进行升级',
	'error_unfind'		=> '<b>{#filename}</b> 文件或文件夹不存在,请新建文件夹或新建对应的文件（空文件即可）',
	'error_unfinds'		=> '<b>{#filenames}</b> 等文件或文件夹不存在，请新建文件夹或新建对应的文件（空文件即可）',

	'forums1'			=> '论坛分类一',
	'forums2'			=> '论坛版块一',
	'forums3'			=> '论坛分类二',
	'forums4'			=> '论坛版块二',
	'forumsmsg'			=> '填写分类和版块名称',

	'hacklist'			=> '插件列表',
	'have_file'			=> '您已经安装过 phpwind，如需重新安装，请删除此文件（{#bbsurl}/data/{#lockfile}）再进行安装',
	'have_upfile'		=> '您已经升级过 phpwind，如需重新升级，请删除此文件（{#bbsurl}/data/{#lockfile}.lock）再进行后续操作',
	'have_install'		=> '数据库<span class="s1">{#dbname}</span>中已经安装过phpwind.“继续安装”将清除原来的数据，使用其他数据库请“返回上一步”重新设置',



	'header_pw'			=> '官方论坛',
	'header_help'		=> '使用手册',

	'judg_1'			=> '正方获胜',
	'judg_2'			=> '反方获胜',
	'judg_3'			=> '双方战平',

	'last'				=> '继 续',
	'link_index'		=> '系统前台地址',
	'link_admin'		=> '系统后台地址',
	'link_phpwind'		=> 'PW官方论坛',
	'log_help'			=> '<li><a href="http://www.phpwind.com/help/" target="_blank" class="black">全面在线帮助手册</a></li><li><a href="http://www.phpwind.net/read-htm-tid-621045.html" target="_blank" class="black">phpwind7推荐环境</a></li><li><a href="http://www.phpwind.net/read-htm-tid-691673.html" target="_blank" class="black">phpwind 7.5 css风格详解</a></li><li><a href="http://www.phpwind.net/read-htm-tid-696195.html" target="_blank" class="black">PW 6.32到PW风格升级教程</a></li><li><a href="http://www.phpwind.net/read-htm-tid-704022.html" target="_blank" class="black">门户模式化说明</a></li><li><a href="http://www.phpwind.net/read-htm-tid-704023.html" target="_blank" class="black">模式化风格说明</a></li><li class="right"><a href="http://www.phpwind.net/thread-htm-fid-2.html" target="_blank" class="black">更多资源与帮助</a></li>',
	'log_install'		=> '<p>1、运行环境需求：PHP+MYSQL</p><p>2、安装步骤</p><p>● Linux 或 Freebsd 服务器下安装方法</p>
		<p>第一步：</p><p>使用ftp工具，用二进制模式将该软件包里的upload目录下的所有文件上传到您的空间，假设上传后目录为 upload。</p>
		<p>第二步：</p><p>先确认以下目录或文件属性为 (777) 可写模式。</p><div class="c"></div>
		<div style="padding:.5em 2em">
			<span class="black" style="width:250px; float:left">attachment</span><span class="black" style="padding-right:2em">data</span><br />
			<span class="black" style="width:250px; float:left">attachment/cn_img</span><span class="black" style="padding-right:2em">data/bbscache</span><br />
			<span class="black" style="width:250px; float:left">attachment/photo</span><span class="black" style="padding-right:2em">data/groupdb</span><br/>
			<span class="black" style="width:250px; float:left">attachment/thumb</span><span class="black" style="padding-right:2em">data/guestcache</span><br/>
			<span class="black" style="width:250px; float:left">attachment/upload</span><span class="black" style="padding-right:2em">data/style</span><br/>
			<span class="black" style="width:250px; float:left">attachment/mini</span><span class="black" style="padding-right:2em">data/tmp</span><br/>
			<span class="black" style="width:250px; float:left">html</span><span class="black" style="padding-right:2em">data/tplcache</span><br/>
			<span class="black" style="width:250px; float:left">html/read</span><span class="black" style="padding-right:2em">data/tplcache</span><br/>
			<span class="black" style="width:250px; float:left">html/channel</span><span class="black" style="padding-right:2em">data/forums</span><br/>
			<span class="black" style="width:250px; float:left">html/portal/bbsindex</span><span class="black" style="padding-right:2em">html/portal/bbsindex/main.htm</span><br/>
			<span class="black" style="width:250px; float:left">html/portal/bbsindex/config.htm</span><span class="black" style="padding-right:2em">html/portal/bbsindex/index.html</span><br/>
			<span class="black" style="width:250px; float:left">html/portal/bbsradio</span><span class="black" style="padding-right:2em">html/portal/bbsradio/main.htm</span><br/>
			<span class="black" style="width:250px; float:left">html/portal/bbsradio/config.htm</span><span class="black" style="padding-right:2em">html/portal/bbsradio/index.html</span><br/>
			<span class="black" style="width:250px; float:left">html/portal/oindex</span><span class="black" style="padding-right:2em">html/portal/oindex/main.htm</span><br/>
			<span class="black" style="width:250px; float:left">html/portal/oindex/config.htm</span><span class="black" style="padding-right:2em">html/portal/oindex/index.html</span><br/>
			<span class="black" style="width:250px; float:left">html/portal/groupgatherleft/main.htm</span><span class="black" style="padding-right:2em">html/portal/groupgatherleft/config.htm</span><br/>
			<span class="black" style="width:250px; float:left">html/portal/groupgatherleft/index.html</span><span class="black" style="padding-right:2em">html/portal/groupgatherright/main.htm</span><br/>
			<span class="black" style="width:250px; float:left">html/portal/groupgatherright/config.htm</span><span class="black" style="padding-right:2em">html/portal/groupgatherright/index.html</span><br/>
			<span class="black" style="width:250px; float:left">html/portal/userlist/main.htm</span><span class="black" style="padding-right:2em">html/portal/userlist/config.htm</span><br/>
			<span class="black" style="width:250px; float:left">html/portal/userlist/index.html</span><span class="black" style="padding-right:2em">html/portal/usermix/main.htm</span><br/>
			<span class="black" style="width:250px; float:left">html/portal/usermix/config.htm</span><span class="black" style="padding-right:2em">html/portal/usermix/index.html</span><br/>
			<span class="black" style="width:250px; float:left">html/stopic</span><span class="black" style="padding-right:2em">js/</span><br/>
		</div>
		<p>第三步：</p><p>运行 <small>http://yourwebsite/upload/{#basename}</small> 安装程序，填入安装相关信息与资料，完成安装！</p>
		<p>● Windows 服务器下安装方法</p><p>第一步：</p><p>使用ftp工具，将该软件包里的upload目录下的所有文件上传到您的空间，假设上传后目录为 upload。</p>
		<p>第二步：</p><p>运行 <small>http://yourwebsite/upload/{#basename}</small> 安装程序，填入安装相关信息与资料，完成安装！</p>',
	'log_install_t'		=> '安装须知',
	'log_marketing'		=> '<div>尊敬的站长：</div><p>感谢您选择phpwind 社区系统。基于彼此的信赖，我们诚邀您加入phpwind一站式营销计划。</p><p>phpwind一站式营销是帮助广大站长获取收益的网络广告营销平台，它的优势与特色在于：</p><div style="padding:1em 2em">充分结合phpwind 社区系统，使广告投放与管理操作更加得心应手；<br />经由专业力量优化的广告代码，更符合社区的属性；<br />长达一年的实战社区营销经验累积与调试；<br />值得信赖的合作伙伴与精心挑选的广告产品——Google Adsense、北京新锐光芒科技有限公司；<br />更具优势的结算方式——广告收益次月以人民币进行结算，最低收益提领额度为100元。</div><p><a href="http://union.phpwind.com/question.php" target="_blank" class="black"><u>关于phpwind一站式营销</u></a>&nbsp; &nbsp;<a href="http://www.phpwind.net/thread.php?fid=82" target="_blank" class="black"><u>讨论区</u></a></p><p>从建站到挣钱， phpwind永远是您最真诚的伙伴！</p>',
	'log_marketing_t'	=> 'phpwind社区营销',
	'log_partner'		=> '<p>跟随国内开源事业不断前进的脚步，phpwind携手更多WEB应用开发伙伴，共创美好明天。在此向您推荐以下软件，PW Forums与以下软件的完美整合将为您提供更多建站选择：</p><div class="c"></div><div style="padding:2em"><a href="http://www.dedecms.com" target="_blank" class="black" style="width:250px; float:left">DedeCms织梦内容管理系统</a><a href="http://www.phpwind.net/thread.php?fid=90" target="_blank" class="black" style="padding-right:2em">讨论区</a><br /><a href="http://www.dedecms.com" target="_blank" class="black" style="width:250px; float:left">CMSWARE思维网站内容管理系统</a><a href="http://www.phpwind.net/thread.php?fid=92" target="_blank" class="black" style="padding-right:2em">讨论区</a><a href="http://www.phpwind.net/read.php?tid=527223" target="_blank" class="black">最新整合教程</a><br /><a href="http://www.php168.com" target="_blank" class="black" style="width:250px; float:left">PHP168整站系统</a><a href="http://www.phpwind.net/thread.php?fid=91" target="_blank" class="black" style="padding-right:2em">讨论区</a><a href="http://down2.php168.com/mv/6.rar" target="_blank" class="black">最新整合教程</a><br/><a href="http://www.shopex.com.cn" target="_blank" class="black" style="width:250px; float:left">ShopEx网上商店商城系统</a><a href="http://www.phpwind.net/thread.php?fid=93" target="_blank" class="black" style="padding-right:2em">讨论区</a><a href="http://www.phpwind.net/read.php?tid=527222" target="_blank" class="black">最新整合教程</a></div><p>中国站长站ChinaZ.com是针对各类站点提供资讯及资源服务的网站——拥有国内最大的源码发布下载中心及聚集站长、网管的技术资讯社区。中国站长站致力于打造草根站长原创资讯圈，倡导分享建站经历和建站经验，让更多人关注到个人站长、个人网站。</p><p>phpwind与中国站长站有着相互认同，结为战略合作伙伴，希望能够在未来携手倡导网络互助与共享精神，为站长提供尽可能多的服务。</p><p>现在访问 <a href="http://www.chinaz.com/zhuanti/phpwind/index.htm" target="_blank" class="black"><u>ChinaZ.com</u></a> 了解phpwind的最新信息与更多技术参考资料！</p>',
	'log_partner_t'		=> '合作伙伴',
	'log_repair'		=> '<p>(一) 补丁版本：phpwind v{#from_version}（{#wind_repair}）</p>
		<p>(二) 适用版本：phpwind v{#from_version}</p>
		<p>(三) 更新步骤:</p>
		<p>第一步：打补丁前请务必备份数据, 以免遇到失败导致数据丢失</p>
		<p>第二步：如果upload目录下存在 images、attachment、data 目录,请将改成你论坛相应的目录名。注: 可以到论坛后台的 “核心-安全与优化-动态目录” 里查看</p>
		<p>第三步：使用ftp工具，将该软件包里的upload目录下的所有文件上传到您的空间，假设上传后目录为 upload。</p>
		<p>第四步：运行 <small>http://yourwebsite/upload/{#basename}</small> 补丁更新程序，按提示进行操作, 直到更新完成！</p>',
	'log_resources'		=> '<p>感谢您选择使用phpwind 社区系统，如果您对我们的产品及服务有任何疑问或建议，随时欢迎您将信息发表到官方论坛（http://www.phpwind.net）或发送至我们的电子邮箱<font color="#f79646"><b>client@phpwind.net</b></font>。</p><br /><p>如果您需要构建与论坛相关联的内容发布管理系统，我们诚意向您推荐我们的合作伙伴——PHP168。<br />　　<a href="http://bbs.php168.com/read-bbs-tid-205437.html" target="_blank" style="color:#00727c">合作主页</a>&nbsp; &nbsp; &nbsp; &nbsp; <a href="http://www.phpwind.net/thread-htm-fid-91.html" target="_blank" style="color:#00727c">讨论区</a></p><p>如果您需要构建与论坛相关联的网店管理系统，我们诚意推荐我们的合作伙伴——ShopEX。<br />　　<a href="http://www.phpwind.com/shopex" target="_blank" style="color:#00727c">合作主页</a>&nbsp; &nbsp; &nbsp; &nbsp; <a href="http://www.phpwind.net/thread-htm-fid-93.html" target="_blank" style="color:#00727c">讨论区</a></p><p>如果您需要构建与论坛相关联的博客系统，我们诚意向您推荐我们的博客产品——LxBlog。<br />　　<a href="http://www.phpwind.com/introduce.php?action=introduce&job=bloginfo" target="_blank" style="color:#00727c">官方主页</a>&nbsp; &nbsp; &nbsp; &nbsp; <a href="http://www.phpwind.net/thread-htm-fid-21.html" target="_blank" style="color:#00727c">讨论区</a>&nbsp; &nbsp; &nbsp; &nbsp; <a href="http://www.phpwind.net/read-htm-tid-620820.html" target="_blank" style="color:#00727c">论坛与博客整合图文教程</a></p><p>如果您需要个性化您的论坛，欢迎您访问phpwind官方论坛获取更多资源。phpwind将联合数十家第三方团队与业务伙伴，与站长一起播种未来！</p><p><a href="http://www.phpwind.net/hack.php?H_name=hackcenter&action=style" target="_blank" style="color:#00727c">获取更多免费论坛风格</a>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a href="http://www.phpwind.net/hack.php?H_name=hackcenter&action=hack" target="_blank" style="color:#00727c">获取更多免费论坛插件</a></p>',
	'log_union'			=> '<p>安装完成后，您的无图版帖子顶楼内容上方将出现阿里妈妈统一投放的广告，所有收益都归站长所有，并由阿里妈妈统一结算。收益详情请见 <a href="http://union.phpwind.com/news.php?action=read&nid=46" target="_blank" style="color:#00727c">http://union.phpwind.com/news.php?action=read&nid=46</a>。</p><p>现在，在版本安装完成之前，您还可开启完整版以下3个阿里妈妈广告位：</p><p><input type="checkbox" name="banners" value="1" CHECKED> 全站顶部 468*60 <input type="checkbox" name="atcbottoms" value="1"> 帖子内容下方 468*60 <input type="checkbox" name="footers" value="1"> 全站底部 760*90 </p><p>如果您尚未注册阿里妈妈帐号，请不要担心。无论是无图版还是完整版的广告，从第一个PV和点击开始，数据将会保存在为您预留的帐户中，直到您注册登录营销平台（<a href="http://union.phpwind.com" target="_blank" style="color:#00727c">http://union.phpwind.com</a>）并绑定阿里妈妈帐号后（可在营销平台一次性完成阿里妈妈帐号的注册与绑定），您可以随时登录阿里妈妈平台（<a href="http://www.alimama.com" target="_blank" style="color:#00727c">http://www.alimama.com</a>）查看您无图版广告数据并提领收益。<a href="http://www.phpwind.net/read-htm-tid-633350.html" target="_blank" style="color:#00727c">查看图文说明</a></p><p>如果您已拥有阿里妈妈帐号，只需登录营销平台进行激活操作即可。</p><p>7月15日-8月15日,“<a href="http://www.phpwind.net/read.php?tid=633351" target="_blank" style="color:#00727c">有流量就有现金，分级奖励</a>”活动正式开始，敬请关注。</p>',
	'log_unionmsgt'		=> '欢迎您使用并测试phpwind V7.5 sp3 社区系统！',
	'log_unionmsgc'		=> '尊敬的站长：\n\n　　欢迎您使用并测试phpwind V7.5 sp3社区系统。官方v6.3 RC演示于3月15日开放至今，版本经过多次调试与完善，已趋于稳定。在RC版公开测试期间，如发现BUG，或对这个版本有任何建议、意见，欢迎您进入此帖进行回复[url]http://www.phpwind.net/read.php?tid=603810[/url]。\n\n　　另外，从4月30日起，通过phpwind社区营销平台指导，站长将可以获得阿里妈妈提供的在线广告服务，由营销后台登记注册至阿里妈妈（[url]www.alimama.com[/url]）平台的站长，将会获得如下[b]独家优势[/b]：\n　　1.高优先级别享有阿里妈妈广告交易平台的众多功能，包括优先三包、优先享有更多广告推广模式等；\n　　2.phpwind社区营销平台累计收入超过100元的站长，将会直接享有网站最低收入保障服务，让您在稳定的收入下，获得更多价值；\n　　3.在未来，phpwind站长将会获得阿里妈妈广告交易平台的phpwind论坛插件，实现更多快捷、并具有独立优势的在线广告服务。\n　　同时，phpwind社区营销平台Google Adsense、下划线、主题帖营销第一期，将暂时停止投放。\n\n　　[b]详情请访问：[url]http://www.phpwind.net/read-htm-tid-602463.html[/url]。[/b]\n\nphpwind官方\n2008-4-29',
	'log_update'		=> '<li><a href="http://www.phpwind.net/read-htm-tid-704022.html" target="_blank" class="black">门户模式化</a></li><li><a href="http://www.phpwind.net/read-htm-tid-681220.html" target="_blank" class="black">PW7.0之好友</a></li><li><a href="http://www.phpwind.net/read-htm-tid-677057.html" target="_blank" class="black">探索PW7.0之用户中心</a></li><li><a href="http://www.phpwind.net/read-htm-tid-679230.html" target="_blank" class="black">PW7.0 之 自定义风格</a></li><li><a href="http://www.phpwind.net/read-htm-tid-674290.html" target="_blank" class="black">PW7.0 之密码安全问题</a></li><li><a href="http://www.phpwind.net/read-htm-tid-680607.html" target="_blank" class="black">盘点7.0细节功能 (一）</a></li><li><a href="http://www.phpwind.net/read-htm-tid-680768.html" target="_blank" class="black">盘点7.0细节功能 (二）</a></li><li><a href="http://www.phpwind.net/read-htm-tid-681088.html" target="_blank" class="black">盘点7.0细节功能 (三）</a></li><li class="right"><a href="http://www.phpwind.net/thread-htm-fid-78.html" target="_blank" class="black">了解更多特性</a></li>',
	'log_upto'			=> '<p>1、运行环境需求：PHP+MYSQL。</p><p>2、适用版本：{#from_version}。</p><p>3、升级步骤：</p><p>● Linux 或 Freebsd 服务器下安装方法。</p><p>第一步：</p><p>升级前请务必备份论坛文件与数据, 以免升级失败导致数据丢失</p><p>第二步：</p><p>请将 upload 目录内的 images 目录改名为论坛的图片目录名。注: 可以到论坛后台的 全局 里查看。</p><p>第三步：</p><p>使用ftp工具中的二进制模式，将该软件包里 upload 里的所有文件覆盖上传到您的论坛目录，假设上传后目录仍旧为 upload。将升级文件(<small>{#basename}</small>)上传到 upload 下</p><p>第四步：</p><p>运行 <small>http://yourwebsite/upload/{#basename}</small> 升级程序，按升级提示进行升级, 直到升级结束！</p><p>● Windows 服务器下安装方法。</p><p>第一步：</p><p>升级前请务必备份论坛文件与数据, 以免升级失败导致数据丢失</p><p>第二步：</p><p>请将 upload 目录内的 images 目录改名为论坛的图片目录名。注: 可以到论坛后台的 核心设置 里查看。</p><p>第三步：</p><p>使用ftp工具，将该软件包里 upload 里的所有文件覆盖上传到您的论坛目录，假设上传后目录仍旧为 upload。将升级文件(<small>{#basename}</small>)上传到 upload 下</p><p>第四步：</p><p>运行 <small>http://yourwebsite/upload/{#basename}</small> 升级程序，按升级提示进行升级, 直到升级结束！</p>',
	'log_upto_t'		=> '升级须知',
	'log_repair_t'		=> '更新须知',

	'managermsg'		=> '创始人信息',

	'name'				=> '用户名',
	'nf_reply'			=> '最新回复',
	'nf_new'			=> '最新帖子',
	'nf_dig'			=> '最新精华',
	'nf_pic'			=> '最新图片',

	'promptmsg'			=> '提示信息',
	'pwd'				=> '密码',

	'redirect'			=> '自动跳转不成功请点击这里',
	'redirect_msg'		=> '数据正在更新升级，这个过程比较漫长，可能需要花费您几分钟的时间，请耐心等待......',

	'redirect_msgs'		=> ' &nbsp; &nbsp; <font color="red">{#start}</font> TO <font color="red">{#end}</font>',

	'setform_1'			=> '出租信息',
	'setfrom_1_inro'	=> '<table cellspacing="1" cellpadding="1" align="left" width="100%" style="background:#D4EFF7;text-align:left"><tr><td width="30%" align="center" style="background:#fff;height:25px;"><b>联  系 人:</b></td><td style="background:#fff;padding-left:5px"><p contentEditable=true></p></td></tr><tr><td width="30%" align="center" style="background:#fff;height:25px;"><b>联系方式:</b></td><td style="background:#fff;padding-left:5px"><p contentEditable=true></p></td></tr><tr><td width="30%" align="center" style="background:#fff;height:25px;"><b>房屋类型:</b></td><td style="background:#fff;padding-left:5px"><p contentEditable=true></p></td></tr><tr><td width="30%" align="center" style="background:#fff;height:25px;"><b>房屋位置:</b></td><td style="background:#fff;padding-left:5px"><p contentEditable=true></p></td></tr><tr><td width="30%" align="center" style="background:#fff;height:25px;"><b><font color="#ff3300">出租</font>价格:</b></td><td style="background:#fff;padding-left:5px"><p contentEditable=true></p></td></tr><tr><td width="30%" align="center" style="background:#fff;height:25px;"><b>房屋层次:</b></td><td style="background:#fff;padding-left:5px"><p contentEditable=true></p></td></tr><tr><td width="30%" align="center" style="background:#fff;height:25px;"><b>房屋面积:</b></td><td style="background:#fff;padding-left:5px"><p contentEditable=true></p></td></tr><tr><td width="30%" align="center" style="background:#fff;height:25px;"><b>建造年份:</b></td><td style="background:#fff;padding-left:5px"><p contentEditable=true></p></td></tr><tr><td width="30%" align="center" style="background:#fff;height:25px;"><b>简单情况:</b></td><td style="background:#fff;padding-left:5px"><p contentEditable=true></p></td></tr></table>',
	'showmsg'			=> '安装提示信息',
	'showmsg_upto'		=> '升级提示信息',
	'sqlconfig_file'	=> '数据库配置文件不可写，将./data/sql_config.php的文件属性设置为777（非NT服务器）或取消只读并将用户权限设置为Full Control（完全控制，NT服务器）',
	'step_null'			=> '',
	'step_next'			=> '下一步',
	'step_prev'			=> '上一步',
	'step_1_upto'		=> '升级须知',
	'step_1_left_upto'	=> '请务必认真阅读软件升级说明',
	'step_1_left_repair'=> '请务必认真阅读软件更新说明',
	'step_1_right_upto'	=> '开始升级',
	'step_1_right_repair'=> '开始更新',
	'step_readme'		=> '阅读安装协议',
	'step_readme_left'	=> '请务必认真阅读软件安装协议',
	'step_readme_right'	=> '同意协议，下一步',
	'step_database'		=> '填写数据库信息与创始人信息',
	'step_database_left'	=> '阅读安装协议',
	'step_database_right'	=> '安装详细信息',
	'step_hack'			=> '选择需要安装的插件',
	'step_view'			=> '安装详细信息',
	'step_view_left'	=> '填写数据库信息',
	'step_view_right'	=> '确认安装完成',
	'step_resources'	=> '资源导航',
	'step_resources_left'=> '建立版块并填充帖子',
	'step_5'			=> '默认模式选择',
	'step_5_left'		=> '安装详细信息',
	'step_5_right'		=> '资源导航',
	'step_6'			=> '建立版块并填充帖子',
	'step_6_left'		=> '取消并退出',
	'step_6_right'		=> ' 提 交 ',
	'step_finish'		=> '完成操作',
	'step_union'		=> '社区营销',
	'step_union_right'	=> '下一步',
	'step_app'		    => 'APP帖子交换成功！',
	'writable_success'	=> '可写',
	'writable_error'	=> '不可写',
	'success'			=> '完 成',
	'success_1'			=> '同意安装!',
	'success_2'			=> '{#filename} 777属性检测通过!',
	'success_3'			=> '系统配置创建成功!',
	'success_3_1'		=> '建立数据表 {#tablename} ... 成功!',
	'success_3_2'		=> '创始人信息创建成功!',
	'success_5'			=> '默认模式选择成功!',
	'success_7'			=> '版块 {#value} 编辑完成!',
	'success_7_1'		=> '版块 {#value} 创建成功!',
	'success_7_2'		=> '数据更新成功!',
	'success_4'			=> '插件添加成功!',
	'success_4_1'		=> '友情链接添加成功!',
	'success_4_2'		=> '系统缓存更新完成!',
	'success_install'	=> '恭喜您，您的 phpwind v{#wind_version}已经安装成功！',
	'success_repair'	=> '恭喜您，您的 phpwind v{#wind_version}已经更新成功！',
	'success_upto'		=> '恭喜您，您的 phpwind v{#wind_version}已经升级成功！',
	'welcome_msg'		=> '欢迎来到 phpwind 安装向导，安装前请仔细阅读安装说明后才开始安装。安装文件夹里同样提供了有关软件安装的说明，请您仔细阅读。　　<div style="margin-top:.5em">安装过程中遇到任何问题 &nbsp;<a href="http://www.phpwind.net/thread-htm-fid-2.html" target="_blank" class="black"><u><b>请到官方讨论区寻求帮助</b></u></a></div>',
	'welcome_msgupto'	=> '欢迎来到 phpwind 升级向导，升级前请仔细阅读 升级说明里的每处细节后才能开始升级。升级文件夹里同样提供了有关软件升级的说明，请您同样仔细阅读，以保证升级进程的顺利进行。',
	'welcome_msgrepair'	=> '欢迎来到 phpwind 更新向导，更新前请仔细阅读 更新说明里的每处细节后才能开始更新。更新文件夹里同样提供了有关软件更新的说明，请您同样仔细阅读，以保证更新进程的顺利进行。',
	'finish_exit'		=>'关  闭',
	'title_install'		=> 'phpwind 安装程序',
	'title_repair'		=> 'phpwind 补丁程序',
	'title_upto'		=> 'phpwind 升级程序',
	'env_os'			=> '类UNIX',
	'unlimited'			=> '不限制',
	'environment_check' => '检测环境',
	'insert_data'		=> '创建数据',
	'install_complete'	=> '完成安装',
	'check_enrironment'	=> '环境检测',
	'current_server'	=> '当前服务器',
	'recommend_env'		=> '推荐配置',
	'lowest_env'		=> '最低要求',
	'os'				=> '操作系统',
	'phpversion'		=> 'PHP版本',
	'attach_upload'		=> '附件上传',
	'disk_space'		=> '磁盘空间',
	'right_check'		=> '目录、文件权限检查',
	'current_status'	=> '当前状态',
	'required_status'	=> '所需状态',
	'recheck'			=> '重新检测',
	'databaseinfo'		=> '数据库信息',
	'databasetiop'		=> '数据库服务器地址，一般为localhost',
	'dbpretip'			=> '建议使用默认，同一数据库安装多个论坛时需修改',
	'manager'			=> '管理员帐号',
	'install_completed'	=> '安装完成，点击进入',
	'complete_tips'		=> '浏览器会自动跳转，无需人工干预',
	'installing'		=> '正在安装...',

	//7.3 Start
	'step_pre'			=> '上一步',
	'step_next'			=> '下一步',
	'accept'			=> '接 受',
	'left_info'			=> '<dt style="margin:0;">更新记录</dt>
        <dd style="padding-top:5px;"><a href="http://www.phpwind.net/read-htm-tid-1251662.html" target="_blank">phpwind 8.5介绍帖</a></dd>
        <dd><a href="http://www.phpwind.net/read-htm-tid-1251648.html" target="_blank">phpwind 8.5 bug修复列表</a></dd>
        <dt>帮助文档</dt>
        <dd style="padding-top:5px;"><a href="http://www.phpwind.net/read-htm-tid-1251651.html" target="_blank">安装教程</a></dd>
		<dd><a href="http://www.phpwind.net/read-htm-tid-1251656.html" target="_blank">升级教程</a></dd>
		<dd><a href="http://www.phpwind.net/read-htm-tid-1251658.html" target="_blank">插件安装教程</a></dd>
        <dd><a href="http://www.phpwind.net/read-htm-tid-1251659.html" target="_blank">风格安装教程</a></dd>
        <dd><a href="http://www.phpwind.net/read-htm-tid-1250119.html" target="_blank">数据库结构手册</a></dd>
        <dd><a href="http://www.phpwind.net/thread-htm-fid-54.html" target="_blank">在线反馈</a></dd>
		<dd><a href="http://faq.phpwind.net/" target="_blank">帮助中心</a></dd>',
	'step_problem'		=> '<p>操作过程中若遇到任何问题，</p> <p><a href="http://www.phpwind.net/thread-htm-fid-2.html" target="_blank"><strong>请到官方论坛寻求帮助</strong></a></p>',
	'step_deldir'		=> '友情提示：请及时删除admin/code.php、template/admin/code.htm和hack/app整个文件夹',

	//update
	'update_1'			=> '阅读升级须知',
	'update_2'			=> '具体升级阶段',
	'update_3'			=> '资源导航',
	'update_finish'		=> '升级成功！',
	'admin_name'		=> '创始人帐号：',
	'admin_pwd'			=> '密码：',
	'admin_login_2'		=> ' 登 录 ',
	'login_error'		=> '用户名或密码错误，登录失败',

	//install
	'install_1'			=> '阅读安装协议',
	'install_2'			=> '填写数据信息',
	'install_3'			=> '选择需要安装的插件',
	'install_4'			=> '安装详细信息',
	'install_5'			=> '默认模式选择',
	'install_6'			=> '资源导航',
	'install_finish'	=> '安装成功！',
	'forum_finish'		=> '版块创建完成',
	'app_limit'			=> '您设置的下载数量总和超过了250',

	'app_1'				=>'添加版块',
	'app_2'				=>'您想为这个版块填充何种类型的帖子',
	'app_3'				=>'每天下载数量',
	'app_4'				=>'提示：<br />1、以上操作将为您生成一个新的APP帐户，帐户信息会随后发送到admin的“短消息信箱”中。<br />
2、目前，每日下载总数为250帖；以上设置，您可以随时登录“APP平台>帖子交换>自动下载”进行修改或关闭操作；版块名称可在“论坛后台>版块管理”中修改。<br />
3、提交后，下载操作将在24小时内生效执行。<br />
4、在本地安装的论坛，以上操作无效。<br />
5、查看详细教程 <a href=http://www.phpwind.net/read.php?tid=753545 target=_blank>http://www.phpwind.net/read.php?tid=753545</a>',
	'app_5'				=>'APP 帖子交换是一项充实站点内容，推广自己站点的应用。<br />
#充实内容<br />
通过phpwind APP帖子交换，为phpwind站长和站点提供一个平台，实现站点与站点之间的内容资源共享交换。上传帖子到平台、从平台下载帖子是这个应用中最主要的两个操作。根据设定可以选择发帖内容分类，并且有手动下载和自动下载两种方式。<br />
#推广站点<br />
每一个帖子下载时，都保证带上原创地址与链接，不允许修改。帖子被收录的越多、收录帖子的站点的PR值权重越高，你站点的外链权重也就越高，从而提升你站点的搜索引擎收录量和PR值。<br />
在您申请开通免费空间一键安装论坛的时候，系统将引导您开启帖子交换并立即下载帖子。您也可以选择跳过不开启，之后可随时到“APP平台>控制面板>帖子交换”中修改设置。<br />
详细介绍 <a href=http://www.phpwind.net/read-htm-tid-700917.html target=_blank>http://www.phpwind.net/read-htm-tid-700917.html</a><br />
论坛咨询 <a href=http://www.phpwind.net/thread-htm-fid-100.html target=_blank>http://www.phpwind.net/thread-htm-fid-100.html</a><br />
查看APP更多应用 <a href=http://www.phpwind.net/read-htm-tid-717216.html target=_blank>http://www.phpwind.net/read-htm-tid-717216.html</a>',
	'app_subject'		=> 'APP平台信息',
	'app_content1'		=> "尊敬的站长：<br />
通过您在APP平台上的临时帐户，您已开启APP平台“帖子交换”中的“自动下载”，您的临时帐户信息如下。<br />
用户名：{#username}<br />
密码：{#pwd}<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;对于临时用户名，您有且仅有一次修改机会，请按照此教程进行操作（<a href=http://www.phpwind.net/read.php?tid=753581 target=_blank>http://www.phpwind.net/read.php?tid=753581</a>）进行操作。为了保证帐户的信息安全，登录APP平台时还需要您填写自己的常用电子邮箱，此邮箱将作为激活、修改此帐户信息的最重要凭证。<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;您已开启的“帖子交换>自动下载”可随时登录APP平台修改设置，也可以尝试手动下载方式，通过精确条件筛选与您站点相符的内容再进行下载。除了下载，您还可以推送您站点中原创帖子到帖子交换中心，当您的帖子被其他站点下载后，帖子中都会保留原创地址。通过这种方式，您可以提高站点外链权重，并进行推广。<br />
帖子交换详细教程请访问：<a href=http://www.phpwind.net/read-htm-tid-746186.html target=_blank>http://www.phpwind.net/read-htm-tid-746186.html</a><br />
相信APP平台上其他应用也能帮到您，详情请访问<a href=http://www.phpwind.net/read-htm-tid-717216.html target=_blank>http://www.phpwind.net/read-htm-tid-717216.html</a>，如有任何问题请咨询app@phpwind.com。",
	'app_content2'		=> "尊敬的站长：<br />APP 帖子交换是一项充实站点内容，推广自己站点的应用。<br />
#充实内容
通过phpwind APP帖子交换，为 phpwind 站长和站点提供一个平台，实现站点与站点之间的内容资源共享交换。上传帖子到平台、从平台下载帖子是这个应用中最主要的两个操作。根据设定可以选择发帖内容分类，并且有手动下载和自动下载两种方式。<br />
#推广站点
每一个帖子下载时，都保证带上原创地址与链接，不允许修改。帖子被收录的越多、收录帖子的站点的PR值权重越高，你站点的外链权重也就越高，从而提升你站点的搜索引擎收录量和PR值。<br />
在您申请开通免费空间一键安装论坛的时候，系统将引导您开启帖子交换并立即下载帖子。您也可以选择跳过不开启，之后可随时到“APP平台>控制面板>帖子交换”中修改设置。<br />
详细介绍 <a href=http://www.phpwind.net/read-htm-tid-700917.html target=_blank>http://www.phpwind.net/read-htm-tid-700917.html</a><br />
论坛咨询 <a href=http://www.phpwind.net/thread-htm-fid-100.html target=_blank>http://www.phpwind.net/thread-htm-fid-100.html</a>l<br />
查看APP更多应用 <a href=http://www.phpwind.net/read-htm-tid-717216.html target=_blank>http://www.phpwind.net/read-htm-tid-717216.html</a>",

	//7.3 End
############################## SQL LANG ##############################
	'act_1'				=> '摆了个ＰＯＳＥ道：你、你、你没我酷..',
	'act_2'				=> '撅了撅嘴说：气死我了呀!呀!呀!',
	'act_3'				=> '仰天狂笑：普天之下,竟然没有我的对手...',
	'act_4'				=> '鼻子一酸,开始叭嗒叭嗒掉眼泪了',
	'act_5'				=> '清清嗓子唱起歌来：东方红,太阳升',
	'action_1'			=> '比酷',
	'action_2'			=> '生气',
	'action_3'			=> '狂笑',
	'action_4'			=> '痛哭',
	'action_5'			=> '唱歌',
	'anonymousname'		=> '匿名',

	'colony'			=> '朋友圈',
	'credit_descrip'	=> '自定义积分',
	'credit_name'		=> '好评度',
	'credit_unit'		=> '点',

	'db_adminreason'	=> '广告帖\n恶意灌水\n非法内容\n与版规不符\n重复发帖\n\n优秀文章\n原创内容',
	'db_admingradereason'=> '优秀文章，支持！\n神马都是浮云',
	'db_charset'		=> 'utf-8',
	'db_currencyname'	=> '银元',
	'db_currencyunit'	=> '个',
	'db_creditname'		=> '贡献值',
	'db_creditunit'		=> '点',
	'db_moneyname'		=> '铜币',
	'db_moneyunit'		=> '枚',
	'db_rvrcname'		=> '威望',
	'db_rvrcunit'		=> '点',
	'db_whybbsclose'	=> '网站升级中，请稍后访问',
	'db_visitmsg'		=> '本站仅限内部开放',
	'db_floorunit'		=> '楼',
	'db_floorname_1'	=> '楼主',
	'db_floorname_2'	=> '沙发',
	'db_floorname_3'	=> '板凳',
	'db_floorname_4'	=> '地板',
	'db_sitemsg_1'		=> '带红色*的都是必填项目，若填写不全将无法注册',
	'db_sitemsg_2'		=> '请添加能正常收发邮件的电子邮箱',
	'db_sitemsg_3'		=> '如果您在网吧或者非个人电脑，建议设置Cookie有效期为 即时，以保证账户安全',
	'db_sitemsg_4'		=> '如果您在写长篇帖子又不马上发表，建议存为草稿',
	'db_sitemsg_5'		=> '如果您提交过一次失败了，可以用”恢复数据”来恢复帖子内容',
	'db_sitemsg_6'		=> '批量上传需要先选择文件，再选择上传',

	'debateclass1'		=> '科学教育',
	'debateclass2'		=> '影视娱乐',
	'debateclass3'		=> '休闲时尚',
	'debateclass4'		=> '体育健身',
	'debateclass5'		=> '星座联盟',
	'debateclass6'		=> '社会百态',
	'debateclass7'		=> '其他类别',
	'default_atc'		=> '默认分类',
	'default_forum'		=> '默认版块',
	'default_recycle'	=> '回收站',

	'help_1'			=> '新手入门',
	'help_2'			=> '注册、登录',
	'help_3'			=> '忘记密码',
	'help_4'			=> '添加和编辑个人资料',
	'help_5'			=> '选择风格',
	'help_6'			=> '选择默认编辑器',
	'help_7'			=> '接收邮件',
	'help_8'			=> '发帖回帖',
	'help_9'			=> '发表主题',
	'help_10'			=> '发表出售帖',
	'help_11'			=> '发表交易帖、悬赏帖和投票帖',
	'help_12'			=> '发表匿名帖',
	'help_13'			=> '发表回复',
	'help_14'			=> '引用功能',
	'help_15'			=> '附件上传',
	'help_16'			=> '购买主题',
	'help_17'			=> '我的主题',
	'help_18'			=> '常用功能',
	'help_19'			=> '收藏功能',
	'help_20'			=> '朋友圈功能',
	'help_21'			=> '短消息功能',
	'help_22'			=> '搜索功能',
	'help_23'			=> '帖子举报功能',
	'help_24'			=> '社区插件',
	'help_25'			=> '道具使用',
	'help_26'			=> 'Rss聚合',
	'help_27'			=>'Wind Code',
	'helpd_2'			=> '注册方法：如果您还没有注册，是以游客状态浏览论坛的，在头部导航栏可以看到“您尚未登录&#160;注册”的字样，点击“注册”，填写相应的信息，就可以完成注册了。\n因站长设置的不同，游客的浏览及使用论坛的权限会受到很多限制，如果您喜欢这个论坛，建议您马上注册。\n登录方法：如果您已经注册了该论坛，可以在网站首页头部的登录模块进行登录，也可以在页面头部导航栏点击“登录”，进入登录页面进行登录，在限制游客访问的页面，也会有登录提示页面出现。',
	'helpd_3'			=> '如果您忘记密码，请在登录页面点击“找回密码”并输入用户名，系统将自动发送密码到您的有效电子邮箱中。',
	'helpd_4'			=> '点击进入“控制面板”下的“编辑个人资料”，就可以对自己的资料信息进行修改了。',
	'helpd_5'			=> '点击进入“控制面板”下的“编辑个人资料”，找到“论坛可控制性数据”一栏，在该栏目下有“选择风格”的选项，在下拉列表里选择喜欢的风格，点击“提交”按钮，就可以了。',
	'helpd_6'			=> '点击进入“控制面板”下的“编辑个人资料”，找到“论坛可控制性数据”一栏，在该栏目下有“选择默认使用的编辑器”选项，选择习惯使用的编辑器，点击“提交”。',
	'helpd_7'			=> '点击进入“控制面板”下的“编辑个人资料”，在“论坛可控制性数据”一栏最下边，找到“是否接受邮件”，选择“接收邮件”，点击“提交”。',
	'helpd_9'			=> '在帖子列表页面和帖子阅读页面，可以看到“新帖”图标，点击即可进入主题帖发布页面，如果没有发帖权限，会有提示“本论坛只有特定用户组才能发表主题,请到其他版块发帖,以提高等级!”出现。\n如果用不到这样完全的发帖功能，也可以在帖子列表页面底部的快速发帖模块进行发帖操作。',
	'helpd_10'			=> '在帖子列表页面和帖子阅读页面，点击“新帖”图标进入主题帖发布页面，发帖时在帖子编辑器下方找到“出售此帖”，在前面的复选框理勾选（如果复选框呈灰色，说明该版块不允许发布交易帖或者您的权限不够），填写好会员读帖需要支付的铜币数量（注意不能超过支付最大值）。\n同样，也可以在帖子列表页面底部的快速发帖模块进行发帖操作时设置读帖需要支付的铜币数量。',
	'helpd_11'			=> '发表交易帖、悬赏帖和投票帖','','在帖子列表页面和帖子阅读页面，当鼠标停在新帖图标上时，如果你在该版块有发表交易帖、悬赏帖或投票帖的权限时，就会出现一个下拉菜单，菜单项里显示：商品、悬赏、投票，点击需要的帖子类型即可进入相应的主题发表页面发布新的主题。',
	'helpd_12'			=> '在帖子列表页面和帖子阅读页面，点击“新帖”图标进入发帖页面，在发帖时勾选内容编辑器下面的匿名帖复选框，或者在快速发帖处勾选（如果复选框呈灰色，说明该版块不允许发布匿名帖或者您的权限不够）。',
	'helpd_13'			=> '在帖子阅读页面点击“回复”按钮进入回复页面回复主题帖，也可以在页面下方的快速发帖处进行回复。',
	'helpd_14'			=> '在需要引用的帖子楼层上点击引用，即可引用当前楼层内容，也可以用Wind Code代码进行引用，把需要引用的内容放入[quote] 您要引用的文字[/quote]中间即可。',
	'helpd_15'			=> '在发帖页面下的附件上传处点击浏览按钮，上传有效后缀类型的附件，同时可以在描述框对附件进行描述，并设置下载附件所需要的威望值。',
	'helpd_16'			=> '读一个出售帖时，首先要在点击“购买”按钮，如果铜币数大于购买帖子所需的数量，就会扣掉相应的铜币数，同时购买成功，可以阅读到帖子内容。',
	'helpd_17'			=> '在“控制面板”的“我的主题”下，可以查看我的主题，我的回复，我的精华，我的投票，我的交易等',
	'helpd_19'			=> '在帖子阅读页面，可以看到“打印 | 加为IE收藏 | 收藏主题 | 上一主题 | 下一主题”，点击“收藏主题”后，可以在“控制面板”下的“收藏夹”里看到收藏的主题，同时可以对收藏的主题进行分类管理。',
	'helpd_20'			=> '朋友圈功能以插件形式存在，可以通过创建或加入已创建朋友圈，实现和同一朋友圈会员间更紧密的互动。',
	'helpd_21'			=> '可以通过会员头像信息栏或者帖子楼层短消息按钮实现会员之间互发短消息。',
	'helpd_22'			=> '点击导航栏的“搜索”链接进入搜索页面，在搜索页面下，可以通过关键词或用户名对帖子主题、回复以及精华按版块进行搜索。因级别不同和各论坛设置不同，搜索权限可能会受到限制。',
	'helpd_23'			=> '协助站长进行帖子监控、举报不良帖子推荐优秀帖子的功能。在帖子楼层操作栏点击“举报”填写理由并提交就能实现了对当前楼层帖子举报的操作。',
	'helpd_24'			=> '是对论坛功能的一个重要补充。插件通过把程序文件上传到hack目录下，在后台“添加插件”处进行安装，并能在“插件管理”中对相应的插件进行编辑管理和卸载。插件安装好后并开启后通过设置下拉显示或直接显示，让插件链接显示在“社区服务”的下拉菜单中或者直接显示在导航栏上。',
	'helpd_25'			=> '对会员使用的道具在会员头像信息栏的头像按钮处选择，对帖子使用的道具在帖子操作栏的“使用道具”处选择。打开道具列表后可以看到自己存有的道具数量，如果数量为零需要购买才能使用。',
	'helpd_26'			=> '程序提供了几处常用的Rss订阅。首页的“Rss订阅本版面最新帖子”、分类页面下的“Rss订阅本版面最新帖子”和版块页面下的“Rss订阅本版面最新帖子”。',
	'helpd_27'			=> '<table><tr class="tr3 tr"><td><font color="#5a6633" face="verdana">[quote]</font>被引用的内容，主要作用是在发帖时引用并回复具体楼层的帖子<font color="#5a6633" face="verdana">[/quote]</font></td><td><table cellpadding="5" cellspacing="1" width="94%" bgcolor="#000000" align="center"><tr><td class="f_one">被引用的帖子和您的回复内容</td></tr></table></td></tr><tr class="tr3 tr"><td><font color="#5a6633" face="verdana">[code]</font><font color="#5a6633"></font><font face="courier" color="#333333"><br />echo "phpwind 欢迎您!"\r</font><font color="#5a6633" face="verdana">[/code]</font></td><th><div class="tpc_content" id="read_553959"><h6 class="quote">Copy code</h6><blockquote id="code1">echo "phpwind 欢迎您!"</blockquote></div></th></tr><tr class="tr3 tr"><td><font face="verdana" color="5a6633">[url]</font><font face="verdana">http://www.phpwind.net</font><font color="5a6633">[/url] </font></td><td><a href="http://www.phpwind.net" target="_blank"><font color="#000066">http://www.phpwind.net</font></a></td></tr><tr class="tr3 tr"><td><font face="verdana" color="5a6633">[url=http://www.phpwind.net]</font><font face="verdana">phpwind</font><font color="5a6633">[/url]</font></td><td><a href="http://www.phpwind.net"><font color="000066">PHPwind</font></a></font></td></tr><tr class="tr3 tr"><td><font face="verdana" color="5a6633">[email]</font><font face="verdana">fengyu@163.com</font><font color="5a6633">[/email]</font></td><td><a href="mailto:fengyu@163.com"><font color="000066">fengyu@163.com</font></a></td></tr><tr class="tr3 tr"><td><font face="verdana" color="5a6633">[email=fengyu@163.com]</font><font face="verdana">email me</font><font color="5a6633">[/email]</font></td><td><a href="mailto:fengyu@163.com"><font color="000066">email me</font></a></td></tr><tr class="tr3 tr"> <td><font face="verdana" color="5a6633">[b]</font><font face="verdana">粗体字</font><font color="5a6633" face="verdana">[/b]</font> </td><td><font face="verdana"><b>粗体字</b></font> </td></tr><tr class="tr3 tr"><td><font face="verdana" color="5a6633">[i]</font><font face="verdana">斜体字<font color="5a6633">[/i]</font> </font></td><td><font face="verdana"><i>斜体字</i></font> </td></tr><tr class="tr3 tr"><td><font face="verdana" color="5a6633">[u]</font><font face="verdana">下划线</font><font color="5a6633">[/u]</font></td><td><font face="verdana"><u>下划线</u></font> </td></tr><tr class="tr3 tr"> <td><font face=verdana color=5a6633>[align=center(可以是向左left，向右right)]</font>位于中间<font color="5a6633">[/align]</font></td><td><font face="verdana"><div align="center">中间对齐</div></font></td></tr><tr class="tr3 tr"> <td><font face="verdana" color="5a6633">[size=4]</font><font face="verdana">改变字体大小<font color="5a6633">[/size] </font> </font></td><td><font face="verdana">改变字体大小 </font></td></tr><tr class="tr3 tr"> <td><font face="verdana" color="5a6633">[font=</font><font color="5a6633">楷体_gb2312<font face="verdana">]</font></font><font face="verdana">改变字体<font color="5a6633">[/font] </font> </font></td><td><font face="verdana"><font face=楷体_gb2312>改变字体</font> </font></td></tr><tr class="tr3 tr"> <td><font face="verdana" color="5a6633">[color=red]</font><font face="verdana">改变颜色<font color="5a6633">[/color] </font> </font></td><td><font face="verdana" color="red">改变颜色</font><font face="verdana"> </font></td></tr><tr class="tr3 tr"> <td><font face="verdana" color="5a6633">[img]</font><font face="verdana">http://www.phpwind.net/logo.gif<font color="5a6633">[/img]</font> </font></td><td><img src="logo.gif" /></font> </td></tr><tr class="tr3 tr"> <td><font face=宋体 color="#333333"><font color="#5a6633">[fly]</font>飞行文字特效<font color="#5a6633">[/fly]</font> </font></td><td><font face=宋体&nbsp; &nbsp; color="#333333"><marquee scrollamount="3" behavior="alternate" width="90%">飞行文字特效</marquee></font></td></tr><tr class="tr3 tr"> <td><font face=宋体 color="#333333"><font color="#5a6633">[move]</font>滚动文字特效<font color="#5a6633">[/move]</font> </font></td><td><font face=宋体 color="#333333"> <marquee scrollamount="3" width="90%">滚动文字特效</marquee></font></td></tr><tr class="tr3 tr"><td><font face=宋体 color="#333333"><font color="#5a6633">[flash=400,300]</font>http://www.phpwind.net/wind.swf<font color="#5a6633">[/flash]</font> </font></td><td><font face=宋体 color="#333333">将显示flash文件</font> </td></tr><tr class="tr3 tr"><td><font face=宋体 color="#333333"><font color="#5a6633">[iframe]</font>http://www.phpwind.net<font color="#5a6633">[/iframe]</font> </font></td><td><font face=宋体 color="#333333">将在帖子中粘贴网页(后台默认关闭)</font> </td></tr><tr class="tr3 tr"><td><font color=#5a6633>[glow=255(宽度),red(颜色),1(边界)]</font>要产生光晕效果的文字<font color="#5a6633">[/glow]</font></td><td align="center"><font face=宋体 color="#333333"><table width="255" style="filter:glow(color=red, direction=1)"><tr><td align="center">要产生彩色发光效果的文字</td></tr></table></font></td></tr></table>',
	'home_1'			=> '社区焦点',
	'home_2'			=> '热门标签',
	'home_3'			=> '推荐帖子',
	'home_4'			=> '热门文章',
	'home_5'			=> '站点信息',
	'home_6'			=> '版块排行',
	'home_7'			=> '最新回复',
	'home_8'			=> '最新帖子',
	'home_9'			=> '会员排行',

	'level_1'			=> '游客',
	'level_3'			=> '管理员',
	'level_4'			=> '总版主',
	'level_5'			=> '论坛版主',
	'level_6'			=> '禁止发言',
	'level_7'			=> '未验证会员',
	'level_8'			=> '新手上路',
	'level_9'			=> '侠客',
	'level_10'			=> '骑士',
	'level_11'			=> '圣骑士',
	'level_12'			=> '精灵王',
	'level_13'			=> '风云使者',
	'level_14'			=> '光明使者',
	'level_15'			=> '天使',
	'level_16'			=> '荣誉会员',

	'medaldesc_1'		=> '谢谢您为社区发展做出的不可磨灭的贡献!',
	'medaldesc_2'		=> '辛劳地为论坛付出劳动，收获快乐，感谢您!',
	'medaldesc_3'		=> '谢谢您为社区积极宣传,特颁发此奖!',
	'medaldesc_4'		=> '您为论坛做出了特殊贡献,谢谢您!',
	'medaldesc_5'		=> '为社区提出建设性的建议被采纳,特颁发此奖!',
	'medaldesc_6'		=> '谢谢您积极发表原创作品,特颁发此奖!',
	'medaldesc_7'		=> '帖图高手,堪称大师!',
	'medaldesc_8'		=> '能够长期提供优质的社区水资源者,可得这个奖章!',
	'medaldesc_9'		=> '新人有很大的进步可以得到这个奖章!',
	'medaldesc_10'		=> '您总是能给别人带来欢乐,谢谢您!',
	'medalname_1'		=> '终身成就奖',
	'medalname_2'		=> '优秀斑竹奖',
	'medalname_3'		=> '宣传大使奖',
	'medalname_4'		=> '特殊贡献奖',
	'medalname_5'		=> '金点子奖',
	'medalname_6'		=> '原创先锋奖',
	'medalname_7'		=> '贴图大师奖',
	'medalname_8'		=> '灌水天才奖',
	'medalname_9'		=> '新人进步奖',
	'medalname_10'		=> '幽默大师奖',
	'money'				=> '铜币',

	'ol_whycolse'		=> '系统没有开启网上支付功能!',

	'plan_1'			=> '定时清理群发消息',
	'plan_2'			=> '自动解除禁言',
	'plan_3'			=> '发送生日短消息',
	'plan_4'			=> '悬赏帖到期通知',
	'plan_5'			=> '管理团队工资发放',
	'plan_6'			=> '勋章自动回收',
	'plan_7'			=> '限期头衔自动回收',

	'rg_banname'		=> '版主,管理员,admin,斑竹',
	'rg_rgpermit'		=> '当您申请用户时，表示您已经同意遵守本规章。 <br /><br />欢迎您加入本站点参加交流和讨论，本站点为公共论坛，为维护网上公共秩序和社会稳定，请您自觉遵守以下条款： <br /><br />一、不得利用本站危害国家安全、泄露国家秘密，不得侵犯国家社会集体的和公民的合法权益，不得利用本站制作、复制和传播下列信息：<br />　 （一）煽动抗拒、破坏宪法和法律、行政法规实施的；<br />　（二）煽动颠覆国家政权，推翻社会主义制度的；<br />　（三）煽动分裂国家、破坏国家统一的；<br />　（四）煽动民族仇恨、民族歧视，破坏民族团结的；<br />　（五）捏造或者歪曲事实，散布谣言，扰乱社会秩序的；<br />　（六）宣扬封建迷信、淫秽、色情、赌博、暴力、凶杀、恐怖、教唆犯罪的；<br />　（七）公然侮辱他人或者捏造事实诽谤他人的，或者进行其他恶意攻击的；<br />　（八）损害国家机关信誉的；<br />　（九）其他违反宪法和法律行政法规的；<br />　（十）进行商业广告行为的。<br /><br />二、互相尊重，对自己的言论和行为负责。<br />三、禁止在申请用户时使用相关本站的词汇，或是带有侮辱、毁谤、造谣类的或是有其含义的各种语言进行注册用户，否则我们会将其删除。<br />四、禁止以任何方式对本站进行各种破坏行为。<br />五、如果您有违反国家相关法律法规的行为，本站概不负责，您的登录论坛信息均被记录无疑，必要时，我们会向相关的国家管理部门提供此类信息。 ',
	'rg_welcomemsg'		=> '感谢您的注册，欢迎您的到来，希望这里能给您带来快乐！多多发言吧！本站全体管理人员向您问好<br />您的注册名为:$rg_name',
	'rg_whyregclose'	=> '管理员关闭了注册!',
	'rvrc'				=> '威望',

	'sharelinks'		=> 'PHPwind官方论坛',
	'smile'				=> '默认表情',

	'tool_1'			=> '威望工具',
	'tool_1_inro'		=> '可将自己负威望清零',
	'tool_2'			=> '清零卡',
	'tool_2_inro'		=> '可将自己所有负分清零,包括铜币,威望,贡献值,积分',
	'tool_3'			=> '醒目卡',
	'tool_3_inro'		=> '可以将自己的帖子标题加亮显示',
	'tool_4'			=> '置顶I',
	'tool_4_inro'		=> '可将自己发表的帖子在版块中置顶，置顶时间为6小时',
	'tool_5'			=> '置顶II',
	'tool_5_inro'		=> '可将自己发表的帖子在分类中置顶，置顶时间为6小时',
	'tool_6'			=> '置顶III',
	'tool_6_inro'		=> '可将自己发表的帖子在整个论坛中置顶，置顶时间为6小时',
	'tool_7'			=> '提前帖子',
	'tool_7_inro'		=> '可以把自己发表的帖子提前到帖子所在版块的第一页',
	'tool_8'			=> '改名卡',
	'tool_8_inro'		=> '可更改自己在论坛的用户名',
	'tool_9'			=> '精华I',
	'tool_9_inro'		=> '可以将自己的帖子加为精华I',
	'tool_10'			=> '精华II',
	'tool_10_inro'		=> '可以将自己的帖子加为精华II',
	'tool_11'			=> '锁定帖子',
	'tool_11_inro'		=> '可以将自己发表的帖子锁定，不让其他会员回复此帖',
	'tool_12'			=> '解除锁定',
	'tool_12_inro'		=> '可以解除自己被帖子锁定，让其他会员可以回复此帖',
	'tool_13'			=> '鲜花',
	'tool_13_inro'		=> '可以给帖子增加推荐数',
	'tool_14'			=> '鸡蛋',
	'tool_14_inro'		=> '可以给帖子减少推荐数',
	'tool_15'			=> '运气卡',
	'tool_15_inro'		=> '使用后随机加减交易币(-100,100)',
	'tool_16'			=> '生日卡',
	'tool_16_inro'		=> '以短消息方式发送给好友，祝好友生日快乐',
	'tool_17'			=> '沉淀卡',
	'tool_17_inro'		=> '帖子中使用，每用一次让帖子丢到12小时前',
	'tool_18'			=> '猪头术',
	'tool_18_inro'		=> '使用后让对方头像变为猪头，此效果持续24小时或到对方使用清洗卡为止',
	'tool_19'			=> '还原卡',
	'tool_19_inro'		=> '清除猪头卡的效果',
	'tool_20'			=> '透视镜',
	'tool_20_inro'		=> '对用户使用 查看用户IP',
	'tool_21'			=> '护身符',
	'tool_21_inro'		=> '使用后，不能对该用户实现猪头术效果，48小时内有效',
	'tool_22'			=> '时空卡',
	'tool_22_inro'		=> '帖子中使用，让帖子发布到12小时后',

	'admin_login'		=> '请先用创始人登录再进行升级操作',

	'block_1'			=> '最新主题',
	'block_2'			=> '最新回复',
	'block_3'			=> '精华主题',
	'block_4'			=> '回复排行',
	'block_5'			=> '人气排行',
	'block_6'			=> '金钱排行',
	'block_7'			=> '威望排行',
	'block_8'			=> '今日发帖',
	'block_9'			=> '主题数排行',
	'block_10'			=> '发帖量排行',
	'block_11'			=> '热门标签',
	'block_12'			=> '最新标签',
	'block_13'			=> '最新图片',
	'block_14'			=> '最新图片(频道)',
	'block_15'			=> '热门活动(整站)',
	'block_16'			=> '最新交易(整站)',
	'block_17'			=> '热门交易(整站)',
	'block_18'			=> '在线时间排行',
	'block_19'			=> '今日发帖排行',
	'block_20'			=> '月发帖排行',
	'block_21'			=> '发帖排行',
	'block_22'			=> '月在线排行',
	'block_23'			=> '今日热帖(整站)',
	'block_24'			=> '热门活动(频道)',
	'block_25'			=> '贡献值排行',
	'block_26'			=> '交易币排行榜',
	'block_27'			=> '首页推送',
	'block_28'			=> '加亮主题(频道)',
	'block_29'			=> '加亮主题(整站)',
	'block_30'			=> '今日热帖(频道)',
	'block_31'			=> '精华主题(频道)',
	'block_32'			=> '精华主题(版块)',
	'block_33'			=> '加亮主题(版块)',
	'block_34'			=> '最新回复(版块)',
	'block_35'			=> '频道页推送',
	'block_36'			=> '点击排行(频道)',
	'block_37'			=> '最新主题(频道)',
	'block_38'			=> '今日活动(整站)',
	'block_39'			=> '今日活动(频道)',
	'block_40'			=> '最新图片(版块)',
	'block_41'			=> '最新活动(整站)',
	'block_42'			=> '最新活动(频道)',
	'block_43'			=> '最新交易(频道)',
	'block_44'			=> '热门交易(频道)',
	'block_45'			=> '今日交易(整站)',
	'block_46'			=> '今日交易(频道)',
	'block_47'			=> '今日人气(整站)',
	'block_48'			=> '今日人气(频道)',
	'stamp_1'			=> '帖子排行',
	'stamp_2'			=> '用户排行',
	'stamp_3'			=> '版块排行',
	'stamp_4'			=> '标签排行',
	'stamp_5'			=> '图片',
	'stamp_6'			=> '活动',
	'stamp_7'			=> '交易',
	'stamp_8'			=> '推送',
	'fourmtype_2'		=> '悬赏',
	'fourmtype_5'		=> '频道页隐藏',
	'mode_area_name'	=> '门户模式',
	'mode_pw_name'		=> '论坛模式',
	'mode_o_name'		=> '个人中心',
	'mode_area_header'	=> '门户',
	'mode_pw_header'	=> '论坛',
	'mode_o_header'		=> '个人中心',
	'mode_pgcon_index'	=> '首页',
	'mode_pgcon_cate'	=> '频道页',
	'mode_pgcon_thread'	=> '列表页',
	'mode_pgcon_m_home'	=> '动态',

	//7.3.2
	'diary_o_uploadsize'=> 'a:5:{s:3:"jpg";i:300;s:4:"jpeg";i:300;s:3:"png";i:400;s:3:"gif";i:400;s:3:"bmp";i:400;}',

	'config_noexists'	=> '数据库配置文件不存在,请重新填写配置信息',
	'install_initdata'	=> "正在初始化数据,请稍候...$GLOBALS[stepstring]",
	'undefined_action'	=> '非法操作,请返回',
	'action_success'	=> '此步操作完成,请进入下一步操作',
	'promptmsg_database'=> '继续安装',

	'Site.Header'		=> '头部横幅~	~显示在页面的头部，一般以图片或flash方式显示，多条广告时系统将随机选取一条显示',
	'Site.Footer'		=> '底部横幅~	~显示在页面的底部，一般以图片或flash方式显示，多条广告时系统将随机选取一条显示',
	'Site.NavBanner1'	=> '导航通栏~	~显示在主导航的下面，一般以图片或flash方式显示，多条广告时系统将随机选取一条显示',
	'Site.PopupNotice'	=> '弹窗广告[右下]~	~在页面右下角以浮动的层弹出显示，此广告内容需要单独设置相关窗口参数',
	'Site.FloatRand'	=> '漂浮广告[随机]~	~以各种形式在页面内随机漂浮的广告',
	'Site.FloatLeft'	=> '漂浮广告[左]~	~以各种形式在页面左边漂浮的广告，俗称对联广告[左]',
	'Site.FloatRight'	=> '漂浮广告[右]~	~以各种形式在页面右边漂浮的广告，俗称对联广告[右]',
	'Mode.TextIndex'	=> '文字广告[首页]~	~显示在页面的导航下面，一般以文字方式显示，每行四条广告，超过四条将换行显示',
	'Mode.Forum.TextRead'	=> '文字广告[帖子页]~	~显示在页面的导航下面，一般以文字方式显示，每行四条广告，超过四条将换行显示',
	'Mode.Forum.TextThread'	=> '文字广告[主题页]~	~显示在页面的导航下面，一般以文字方式显示，每行四条广告，超过四条将换行显示',
	'Mode.Forum.Layer.TidRight'	=> '楼层广告[帖子右侧]~	~出现在帖子右侧，一般以图片或文字显示，多条帖间广告时系统将随机选取一条显示',
	'Mode.Forum.Layer.TidDown'	=> '楼层广告[帖子下方]~	~出现在帖子下方，一般以图片或文字显示，多条帖间广告时系统将随机选取一条显示',
	'Mode.Forum.Layer.TidUp'		=> '楼层广告[帖子上方]~	~出现在帖子上方，一般以图片或文字显示，多条帖间广告时系统将随机选取一条显示',
	'Mode.Forum.Layer.TidAmong'	=> '楼层广告[楼层中间]~	~出现在帖子楼层之间，一般以图片或文字显示，多条帖间广告时系统将随机选取一条显示',
	'Mode.Layer.Index'		=> '首页分类间广告~	~出现在首页分类层之间，一般以图片或文字显示，多条帖间广告时系统将随机选取一条显示',

	'nav_home_main' => '首页',
	'nav_bbs_main' => '论坛',
	'nav_o_main' => '个人中心',
	'nav_sort_bbs' => '统计排行',
	'nav_app_bbs' => 'APP应用',
	'nav_hack_bbs' => '社区服务',
	'nav_member_bbs' => '会员列表',
	'nav_lastpost_bbs' => '最新帖子',
	'nav_digest_bbs' => '精华区',
	'nav_search_bbs' => '搜索',
	'nav_faq_bbs' => '帮助',
	'nav_sort_basic_bbs' => '基本信息',
	'nav_sort_ipstate_bbs' => '到访IP统计',
	'nav_sort_team_bbs' => '管理团队',
	'nav_sort_admin_bbs' => '管理操作',
	'nav_sort_online_bbs' => '在线会员',
	'nav_sort_member_bbs' => '会员排行',
	'nav_sort_forum_bbs' => '版块排行',
	'nav_sort_article_bbs' => '帖子排行',
	'nav_sort_taglist_bbs' => '标签排行',
	'nav_index_o' => '个人中心首页',
	'nav_home_o' => '我的首页',
	'nav_user_o' => '个人空间',
	'nav_friend_o' => '朋友',
	'nav_browse_o' => '随便看看',
);

$appinfo =array(
	'226'=>array(
		'cup' => '167',
		'cid' => '226',
		'name' => '国际军情',
		'ifshow' => '0',
	),

	'210'=>array(
		'cup' => '170',
		'cid' => '210',
		'name' => '游戏机',
		'ifshow' => '0',
	),

	'209'=>array(
		'cup' => '170',
		'cid' => '209',
		'name' => '相机|DV',
		'ifshow' => '0',
	),

	'208'=>array(
		'cup' => '170',
		'cid' => '208',
		'name' => 'PC|笔记本',
		'ifshow' => '0',
	),

	'207'=>array(
		'cup' => '170',
		'cid' => '207',
		'name' => '手机|PDA',
		'ifshow' => '0',
	),

	'206'=>array(
		'cup' => '170',
		'cid' => '206',
		'name' => '智库资源',
		'ifshow' => '0',
	),

	'205'=>array(
		'cup' => '170',
		'cid' => '205',
		'name' => '业界资讯',
		'ifshow' => '0',
	),

	'204'=>array(
		'cup' => '171',
		'cid' => '204',
		'name' => '男人装|女人装',
		'ifshow' => '0',
	),

	'203'=>array(
		'cup' => '171',
		'cid' => '203',
		'name' => '达人指点',
		'ifshow' => '0',
	),

	'202'=>array(
		'cup' => '171',
		'cid' => '202',
		'name' => '城市精锐',
		'ifshow' => '0',
	),

	'201'=>array(
		'cup' => '171',
		'cid' => '201',
		'name' => '护肤|美妆',
		'ifshow' => '0',
	),

	'200'=>array(
		'cup' => '171',
		'cid' => '200',
		'name' => '名品精选',
		'ifshow' => '0',
	),

	'199'=>array(
		'cup' => '172',
		'cid' => '199',
		'name' => '国际旅游',
		'ifshow' => '0',
	),

	'198'=>array(
		'cup' => '172',
		'cid' => '198',
		'name' => '国内旅游',
		'ifshow' => '0',
	),

	'211'=>array(
		'cup' => '170',
		'cid' => '211',
		'name' => '其他家电',
		'ifshow' => '0',
	),

	'212'=>array(
		'cup' => '169',
		'cid' => '212',
		'name' => '热点关注',
		'ifshow' => '0',
	),

	'225'=>array(
		'cup' => '167',
		'cid' => '225',
		'name' => '台海军情',
		'ifshow' => '0',
	),

	'224'=>array(
		'cup' => '167',
		'cid' => '224',
		'name' => '中国军情',
		'ifshow' => '0',
	),

	'223'=>array(
		'cup' => '167',
		'cid' => '223',
		'name' => '尖端武器',
		'ifshow' => '0',
	),

	'222'=>array(
		'cup' => '167',
		'cid' => '222',
		'name' => '军事历史',
		'ifshow' => '0',
	),

	'221'=>array(
		'cup' => '167',
		'cid' => '221',
		'name' => '网上谈兵',
		'ifshow' => '0',
	),

	'220'=>array(
		'cup' => '168',
		'cid' => '220',
		'name' => '博客大观',
		'ifshow' => '0',
	),

	'219'=>array(
		'cup' => '168',
		'cid' => '219',
		'name' => ' 音乐|影视',
		'ifshow' => '0',
	),

	'218'=>array(
		'cup' => '168',
		'cid' => '218',
		'name' => '猎奇|笑谈',
		'ifshow' => '0',
	),

	'217'=>array(
		'cup' => '168',
		'cid' => '217',
		'name' => '星座|占卜',
		'ifshow' => '0',
	),

	'216'=>array(
		'cup' => '168',
		'cid' => '216',
		'name' => '游戏|动漫',
		'ifshow' => '0',
	),

	'215'=>array(
		'cup' => '168',
		'cid' => '215',
		'name' => '明星|传闻|八卦',
		'ifshow' => '0',
	),

	'214'=>array(
		'cup' => '169',
		'cid' => '214',
		'name' => '试驾资讯',
		'ifshow' => '0',
	),

	'213'=>array(
		'cup' => '169',
		'cid' => '213',
		'name' => '口碑大全',
		'ifshow' => '0',
	),

	'197'=>array(
		'cup' => '172',
		'cid' => '197',
		'name' => '旅行宝典',
		'ifshow' => '0',
	),

	'196'=>array(
		'cup' => '172',
		'cid' => '196',
		'name' => '演出|剧场',
		'ifshow' => '0',
	),

	'195'=>array(
		'cup' => '172',
		'cid' => '195',
		'name' => '家居|装饰',
		'ifshow' => '0',
	),

	'184'=>array(
		'cup' => '174',
		'cid' => '184',
		'name' => '做站的那些事',
		'ifshow' => '0',
	),

	'177'=>array(
		'cup' => '176',
		'cid' => '177',
		'name' => '全日制教育',
		'ifshow' => '0',
	),

	'178'=>array(
		'cup' => '176',
		'cid' => '178',
		'name' => '成教|自学考',
		'ifshow' => '0',
	),

	'179'=>array(
		'cup' => '176',
		'cid' => '179',
		'name' => '技能培训',
		'ifshow' => '0',
	),

	'181'=>array(
		'cup' => '176',
		'cid' => '181',
		'name' => '四六级',
		'ifshow' => '0',
	),

	'182'=>array(
		'cup' => '175',
		'cid' => '182',
		'name' => '美文',
		'ifshow' => '0',
	),

	'183'=>array(
		'cup' => '174',
		'cid' => '183',
		'name' => 'IT圈',
		'ifshow' => '0',
	),

	'180'=>array(
		'cup' => '176',
		'cid' => '180',
		'name' => '公务员',
		'ifshow' => '0',
	),

	'101'=>array(
		'cup' => '0',
		'cid' => '101',
		'name' => '社会',
		'ifshow' => '0',
	),

	'166'=>array(
		'cup' => '101',
		'cid' => '166',
		'name' => '国内时政',
		'ifshow' => '0',
	),

	'186'=>array(
		'cup' => '101',
		'cid' => '186',
		'name' => '国际时政',
		'ifshow' => '0',
	),

	'189'=>array(
		'cup' => '101',
		'cid' => '189',
		'name' => '房产|零售',
		'ifshow' => '0',
	),

	'190'=>array(
		'cup' => '173',
		'cid' => '190',
		'name' => '足球',
		'ifshow' => '0',
	),

	'191'=>array(
		'cup' => '173',
		'cid' => '191',
		'name' => '篮球',
		'ifshow' => '0',
	),

	'192'=>array(
		'cup' => '173',
		'cid' => '192',
		'name' => '其他',
		'ifshow' => '0',
	),

	'193'=>array(
		'cup' => '172',
		'cid' => '193',
		'name' => '烹饪|美食',
		'ifshow' => '0',
	),

	'194'=>array(
		'cup' => '172',
		'cid' => '194',
		'name' => '健康|医疗',
		'ifshow' => '0',
	),

	'187'=>array(
		'cup' => '101',
		'cid' => '187',
		'name' => '金融|能源|通讯',
		'ifshow' => '0',
	),

	'188'=>array(
		'cup' => '101',
		'cid' => '188',
		'name' => '航空|旅游',
		'ifshow' => '0',
	),

	'167'=>array(
		'cup' => '0',
		'cid' => '167',
		'name' => '军事',
		'ifshow' => '0',
	),

	'168'=>array(
		'cup' => '0',
		'cid' => '168',
		'name' => '娱乐',
		'ifshow' => '0',
	),

	'169'=>array(
		'cup' => '0',
		'cid' => '169',
		'name' => '汽车',
		'ifshow' => '0',
	),

	'170'=>array(
		'cup' => '0',
		'cid' => '170',
		'name' => '数码',
		'ifshow' => '0',
	),

	'171'=>array(
		'cup' => '0',
		'cid' => '171',
		'name' => '时尚',
		'ifshow' => '0',
	),

	'172'=>array(
		'cup' => '0',
		'cid' => '172',
		'name' => '生活',
		'ifshow' => '0',
	),

	'174'=>array(
		'cup' => '0',
		'cid' => '174',
		'name' => 'IT',
		'ifshow' => '0',
	),

	'176'=>array(
		'cup' => '0',
		'cid' => '176',
		'name' => '教育培训',
		'ifshow' => '0',
	),

	'175'=>array(
		'cup' => '0',
		'cid' => '175',
		'name' => '文学文艺',
		'ifshow' => '0',
	),

	'173'=>array(
		'cup' => '0',
		'cid' => '173',
		'name' => '体育',
		'ifshow' => '0',
	),

);