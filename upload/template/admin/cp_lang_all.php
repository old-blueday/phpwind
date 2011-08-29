<?php
!function_exists('readover') && exit('Forbidden');

$lang['all'] = array (

'reg_member'		=> '普通会员',

'operate'			=> '操作:',
'reason'			=> '原因:',

'record_rvrc'		=> '威望',
'record_money'		=> '金钱',
'record_credit'		=> '支持度',

'forum_cate'		=> '◆-',
'forum_cate1'		=> '■-',
'forum_cate2'		=> '●-',
'forum_cate3'		=> '▲-',

'send_welcome'		=> '您好，欢迎光临 $GLOBALS[db_bbsurl].',
'send_feast'		=> '您好，$windid，今天是节日，本论坛全体版主祝您节日愉快！我们特地赠送您财富：$money，威望：$rvrc 作为节日礼物，请笑纳。祝您玩得愉快~',
'whole_notice'		=> '全局公告',
'cms_notice'		=> '文章系统',
'all_notice'		=> '所有公告',
'add_notice'		=> '添加公告',
'read_ischeck'		=> '该帖还未通过验证',
'read_deleted'		=> '该帖已经被删除',

'sqlinfo'			=> '* 数据库相关信息设置\r\n* 具体数值，请联系您的主机商,询问具体的数据库相关信息',
'dbhost'			=> '// 数据库 主机名 或 IP 地址，如数据库端口不是3306，请在 主机名 或 IP 地址后添加“:具体端口”，'
						. '如您的主机是localhost，端口是3307，则更改为“localhost:3307”',
'dbuser'			=> '// 数据库用户名和密码，连接和访问 MySQL 数据库时所需的用户名和密码，不推荐使用空的数据库密码。',
'dbname'			=> '// 数据库名，论坛程序所使用的数据库名。',
'database'			=> '// 数据库类型，有效选项有 mysql 和 mysqli，自pwforums v6.3.2起，'
						. '引入了mysqli的支持，兼容性更好，效率性能更稳定，与mysql连接更稳定\r\n\t'
						. '// 若服务器的配置是 PHP5.1.0或更高版本 和 MySQL4.1.3或更高版本，可以尝试使用 mysqli。',
'PW'				=> '// 表区分符，用于区分每一套程序的符号',
'pconnect'			=> '// 是否持久连接，暂不支持mysqli',
'charset'			=> '* Mysql编码设置(常用编码：gbk、big5、utf8、latin1)\r\n'
						. '* 如果您的论坛出现乱码现象，需要设置此项来修复\r\n'
						. '* 请不要随意更改此项，否则将可能导致论坛出现乱码现象',
'managerinfo'		=> '* 创始人将拥有论坛的所有权限，自pwforums v6.3起支持多重创始人，用户名密码更改方法：\r\n'
						. '* 方法1、将./data/sql_config.php的文件属性设置为777（非NT服务器）或取消只读并将用户权限设置为Full Control（完全控制，NT服务器），\r\n'
						. '* 然后用原始创始人帐号登录后台，在更改论坛创始人处进行相关添加修改操作，\r\n'
						. '* 操作完毕后，再将./data/sql_config.php的文件属性设置为644（非NT服务器）或只读（NT服务器）。（推荐）\r\n'
						. '* 方法2、用记事本打开./data/sql_config.php文件，在“创始人用户名数组”中加入新的用户名，\r\n'
						. '* 如“\$manager = array(\'admin\');”更改为“\$manager = array(\'admin\',\'phpwind\');”，在“创始人密码数组”中加入新的密码，\r\n'
						. '* 如“\$manager_pwd = array(\'21232f297a57a5a743894a0e4a801fc3\');”\r\n'
						. '* 更改为“\$manager_pwd = array(\'21232f297a57a5a743894a0e4a801fc3\',\'21232f297a57a5a743894a0e4a801fc3\');”，\r\n'
						. '* 其中“21232f297a57a5a743894a0e4a801fc3”是密码为admin的md5的加密串，您可以创建一个新的文件在根目录（test.php），\r\n'
						. '* 文件内容为 "<?php echo md5(\'您的密码\');?>" ，在地址栏输入http://你的论坛/test.php获得md5加密后的密码，用完记得删除文件test.php。',
'managername'		=> '// 创始人用户名数组',
'managerpwd'		=> '// 创始人密码数组',
'hostweb'			=> '* 镜像站点设置，默认为1，代表是主站点',
'attach_url'		=> '* 附件url地址，以http:// 开头的绝对地址，为空使用默认',
'slaveConfig'		=> '* 这里填写了代表您已经配置好了主从数据库，程序会进行读写分离处理，前面的配置作主数据库，下面的配置作从数据库。您可以根据您的实际情况配置多台从数据库，配置说明请参考上面的主数据库',				
'week_1'			=> '星期一',
'week_2'			=> '星期二',
'week_3'			=> '星期三',
'week_4'			=> '星期四',
'week_5'			=> '星期五',
'week_6'			=> '星期六',
'week_0'			=> '星期日',

'mode_bbs_mname'	=> '论坛模式',
'mode_bbs_title'	=> '论坛',
'mode_o_mname'		=> '个人中心',
'mode_o_title'		=> '家园',

'nav_index'			=> '首页',
'mode_o_nav_home'	=> '我的首页',
'mode_o_nav_user'	=> '个人空间',
'mode_o_nav_friend'	=> '朋友',
'mode_o_nav_browse'	=> '随便看看',

'mode_bbs_nav'		=> "会员应用,app,,root\n"
						. "最新帖子,lastpost,searcher.php?sch_time=newatc,root\n"
						. "精华区,digest,searcher.php?digest=1,root\n"
						. "社区服务,hack,,root\n"
						. "会员列表,member,member.php,root\n"
						. "统计排行,sort,sort.php,root\n"
						. "基本信息,sort_basic,sort.php,sort\n"
						. "到访IP统计,sort_ipstate,sort.php?action=ipstate,sort\n"
						. "管理团队,sort_team,sort.php?action=team,sort\n"
						. "管理操作,sort_admin,sort.php?action=admin,sort\n"
						. "在线会员,sort_online,sort.php?action=online,sort\n"
						. "会员排行,sort_member,sort.php?action=member,sort\n"
						. "版块排行,sort_forum,sort.php?action=forum,sort\n"
						. "帖子排行,sort_article,sort.php?action=article,sort\n"
						. "标签排行,sort_taglist,link.php?action=taglist,sort\n"

);
?>