<?php
!defined('PW_UPLOAD') && exit('Forbidden');
$maxid = $db->get_value("SELECT MAX(id) FROM pw_advert");
$maxid = $maxid < 100 ? 101 : $maxid+1;

$db->update("ALTER TABLE pw_advert AUTO_INCREMENT=$maxid",false);

//$query = $db->query("");//TODO 转换id小于100的记录
$query = $db->query("SELECT * FROM pw_advert WHERE id<=$maxid AND type=1");
while ($rt = $db->fetch_array($query)) {
	$ads[] = array($rt['type'],$rt['uid'],$rt['ckey'],$rt['stime'],$rt['etime'],$rt['ifshow'],$rt['orderby'],$rt['descrip'],$rt['config']);
}
if ($ads) {
	$db->update("INSERT INTO pw_advert(type,uid,ckey,stime,etime,ifshow,orderby,descrip,config) VALUES ".pwSqlMulti($ads,false));
}
$maxid = $maxid - 1;
$db->update("DELETE FROM pw_advert WHERE id<=$maxid AND type=1");

$arrSQL = array(
	"REPLACE INTO pw_advert VALUES(1, 0, 0, 'Site.Header', 0, 0, 1, 0, '头部横幅~	~显示在页面的头部，一般以图片或flash方式显示，多条广告时系统将随机选取一条显示', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(2, 0, 0, 'Site.Footer', 0, 0, 1, 0, '底部横幅~	~显示在页面的底部，一般以图片或flash方式显示，多条广告时系统将随机选取一条显示', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(3, 0, 0, 'Site.NavBanner1', 0, 0, 1, 0, '导航通栏[1]~	~显示在主导航的下面，一般以图片或flash方式显示，多条广告时系统将随机选取一条显示', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(4, 0, 0, 'Site.NavBanner2', 0, 0, 1, 0, '导航通栏[2]~	~显示在头部通栏广告[1]位置的下面,与通栏广告[1]可一起显示,一般为图片广告', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(5, 0, 0, 'Site.PopupNotice', 0, 0, 1, 0, '弹窗广告[右下]~	~在页面右下角以浮动的层弹出显示，此广告内容需要单独设置相关窗口参数', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(6, 0, 0, 'Site.FloatRand', 0, 0, 1, 0, '漂浮广告[随机]~	~以各种形式在页面内随机漂浮的广告', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(7, 0, 0, 'Site.FloatLeft', 0, 0, 1, 0, '漂浮广告[左]~	~以各种形式在页面左边漂浮的广告，俗称对联广告[左]', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(8, 0, 0, 'Site.FloatRight', 0, 0, 1, 0, '漂浮广告[右]~	~以各种形式在页面右边漂浮的广告，俗称对联广告[右]', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(9, 0, 0, 'Mode.TextIndex', 0, 0, 1, 0, '文字广告[论坛首页]~	~显示在页面的导航下面，一般以文字方式显示，每行四条广告，超过四条将换行显示', 'a:1:{s:7:\"display\";s:3:\"all\";}');",

	"REPLACE INTO pw_advert VALUES(10, 0, 0, 'Mode.Forum.TextRead', 0, 0, 1, 0, '文字广告[帖子页]~	~显示在页面的导航下面，一般以文字方式显示，每行四条广告，超过四条将换行显示', 'a:1:{s:7:\"display\";s:3:\"all\";}');",

	"REPLACE INTO pw_advert VALUES(11, 0, 0, 'Mode.Forum.TextThread', 0, 0, 1, 0, '文字广告[主题页]~	~显示在页面的导航下面，一般以文字方式显示，每行四条广告，超过四条将换行显示', 'a:1:{s:7:\"display\";s:3:\"all\";}');",

	"REPLACE INTO pw_advert VALUES(12, 0, 0, 'Mode.Forum.Layer.TidRight', 0, 0, 1, 0, '楼层广告[帖子右侧]~	~出现在帖子右侧，一般以图片或文字显示，多条帖间广告时系统将随机选取一条显示', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(13, 0, 0, 'Mode.Forum.Layer.TidDown', 0, 0, 1, 0, '楼层广告[帖子下方]~	~出现在帖子下方，一般以图片或文字显示，多条帖间广告时系统将随机选取一条显示', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(14, 0, 0, 'Mode.Forum.Layer.TidUp', 0, 0, 1, 0, '楼层广告[帖子上方]~	~出现在帖子上方，一般以图片或文字显示，多条帖间广告时系统将随机选取一条显示', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(15, 0, 0, 'Mode.Forum.Layer.TidAmong', 0, 0, 1, 0, '楼层广告[楼层中间]~	~出现在帖子楼层之间，一般以图片或文字显示，多条帖间广告时系统将随机选取一条显示', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(16, 0, 0, 'Mode.Layer.Index', 0, 0, 1, 0, '论坛首页分类间~	~出现在首页分类层之间，一般以图片或文字显示，多条帖间广告时系统将随机选取一条显示', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(17, 0, 0, 'Mode.area.IndexMain', 0, 0, 1, 0, '门户首页中间~	~门户首页循环广告下面的中间主要广告位,一般为图片广告', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(18, 0, 0, 'Mode.Layer.area.IndexLoop', 0, 0, 1, 0, '门户首页循环~	~门户首页中间循环模块之间的广告投放，一般为图片广告', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(19, 0, 0, 'Mode.Layer.area.IndexSide', 0, 0, 1, 0, '门户首页侧边~	~门户首页侧边每隔一个模块都有一个广告位显示,位置顺序对应选择的楼层数.一般为小图片广告', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(20, 0, 0, 'Mode.Forum.area.CateMain', 0, 0, 1, 0, '门户频道中间~	~门户频道焦点下面的中间主要广告位,一般为图片广告', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(21, 0, 0, 'Mode.Forum.Layer.area.CateLoop', 0, 0, 1, 0, '门户频道循环~	~门户频道中间循环模块之间的广告投放，一般为图片广告', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(22, 0, 0, 'Mode.Forum.Layer.area.CateSide', 0, 0, 1, 0, '门户频道侧边~	~门户频道侧边每隔一个模块都有一个广告位显示,位置顺序对应选择的楼层数.一般为小图片广告', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(23, 0, 0, 'Mode.Forum.Layer.area.ThreadTop', 0, 0, 1, 0, '门户帖子列表页右上~	~帖子列表页门户模式浏览时，右上方的广告位', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(24, 0, 0, 'Mode.Forum.Layer.area.ThreadBtm', 0, 0, 1, 0, '门户帖子列表页右下~	~帖子列表页门户模式浏览时，右下方的广告位', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(25, 0, 0, 'Mode.Forum.Layer.area.ReadTop', 0, 0, 1, 0, '门户帖子内容页右上~	~帖子内容页门户模式浏览时，右上方的广告位', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",

	"REPLACE INTO pw_advert VALUES(26, 0, 0, 'Mode.Forum.Layer.area.ReadBtm', 0, 0, 1, 0, '门户帖子内容页右下~	~帖子内容页门户模式浏览时，右下方的广告位', 'a:1:{s:7:\"display\";s:4:\"rand\";}');",
);

foreach ($arrSQL as $sql) {
	if (trim($sql)) {
		$db->update($sql);
	}
}

$arrUpdate = array(
	'Site.NavBanner'		=> 'Site.NavBanner1',
	'Mode.Layer.TidRight'	=> 'Mode.Forum.Layer.TidRight',
	'Mode.Layer.TidDown'	=> 'Mode.Forum.Layer.TidDown',
	'Mode.Layer.TidUp'		=> 'Mode.Forum.Layer.TidUp',
	'Mode.Layer.TidAmong'	=> 'Mode.Forum.Layer.TidAmong'
);

foreach ($arrUpdate as $key=>$value) {
	$db->update("UPDATE pw_advert SET ckey=".pwEscape($value,false)."WHERE ckey=".pwEscape($key,false));
}

?>