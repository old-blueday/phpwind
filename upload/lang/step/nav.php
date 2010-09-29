<?php
!defined('PW_UPLOAD') && exit('Forbidden');

//INIT
$db->update("DELETE FROM pw_nav WHERE type!='head' AND type!='foot'");
$query = $db->query("SELECT * FROM pw_config WHERE db_name IN ('db_modes','db_mode','db_hackdb','db_modedomain')");
while ($rt = $db->fetch_array($query)) {
	$$rt['db_name'] = unserialize($rt['db_value']);
	if (empty($$rt['db_name'])) {
		$$rt['db_name'] = array();
	}
}

$navConfigService = L::loadClass('navconfig', 'site'); /* @var $navConfigService PW_NavConfig */
$adds = 0;


//MAIN
$view = 20;
$vieworder = array('area' => '1', 'bbs' => '2');
foreach ($db_modes as $key => $value) {
	if (isset($db_modedomain[$key]) && $db_modedomain[$key]) {
		$link = 'http://' . $db_modedomain[$key];
	} elseif ('o' == $key) {
		$link = 'u.php';
	} else {
		$link = 'index.php?m=' . $key;
	}
	$adds += (bool)$navConfigService->add(PW_NAV_TYPE_MAIN, array('nkey' => $key,
		'pos' => '-1',
		'title' => ($value['title'] ? $value['title'] : $value['m_name']),
		'style' => '',
		'link' => $link,
		'alt' => '',
		'target' => 0,
		'view' => $vieworder[$key] ? $vieworder[$key] : $view++,
		'upid' => 0,
		'isshow' => $value['ifopen'],
	));
}


$view = 3;
include D_P.'data/bbscache/area_config.php';
if (!$area_default_alias) {
	$currentAlias = is_array($area_channels) ? current($area_channels) : array();
	$area_default_alias = $currentAlias['alias'];
}
$channelService=L::loadClass('channelService', 'area');
foreach ($channelService->getChannels() as $alias => $channel) {
	$link = "index.php?m=area&alias=".$alias;
	$isShow = in_array($alias, array('bbsindex', 'home')) ? 0 : 1; //$area_default_alias
	$adds += (bool)$navConfigService->add(PW_NAV_TYPE_MAIN, array('nkey' => 'area_'.$alias, 'pos' => '-1', 'title' => $channel['name'], 'link' => $link, 'view' => $view++, 'upid' => 0, 'isshow' => $isShow));
}


//FOOT
$db->update("DELETE FROM pw_nav WHERE type='foot' AND link IN (".pwImplode(array($db_ceoconnect, 'simple/', 'm/index.php')).")");
$defaults = array(
	array('pos' => '-1', 'title' => '联系我们', 'link' => 'sendemail.php?username=admin', 'view'=>1, 'target' => 0, 'isshow' => 1),
	array('pos' => '-1', 'title' => '无图版', 'link' => 'simple/', 'view'=>2, 'target' => 0, 'isshow' => 1),
	array('pos' => '-1', 'title' => '手机浏览', 'link' => 'm/index.php', 'view'=>3, 'target' => 0, 'isshow' => 1),
);
foreach ($defaults as $key => $value) {
	$adds += (bool)$navConfigService->add(PW_NAV_TYPE_FOOT, $value);
}


//LEFT
$defaults = array(
	array('pos' => array('bbs','area'), 'title' => '10分钟建站', 'link' => 'http://www.phpwind.com/easysite/index.php', 'view'=>1, 'target' => 1, 'isshow' => 1),
	array('pos' => array('bbs','area'), 'title' => '轻松转换', 'link' => 'http://www.phpwind.net/convert.php', 'view'=>2, 'target' => 1, 'isshow' => 1),
	array('pos' => array('bbs','area'), 'title' => '站长沙龙', 'link' => 'http://www.phpwind.com/salon/index.html', 'view'=>3, 'target' => 1, 'isshow' => 1),
);
foreach ($defaults as $key => $value) {
	$adds += (bool)$navConfigService->add(PW_NAV_TYPE_HEAD_LEFT, $value);
}


//RIGHT
$db->update("UPDATE pw_nav SET type=".pwEscape(PW_NAV_TYPE_HEAD_RIGHT)." WHERE type='head'");
$db->update("DELETE FROM pw_nav WHERE type=".pwEscape(PW_NAV_TYPE_HEAD_RIGHT)." AND link=".pwEscape('faq.php'));
$defaults = array(
	'hack' => array(
		'data' => array('pos' => array('bbs','area'), 'nkey' => 'hack', 'title' => '社区服务', 'link' => '', 'view'=>1, 'target' => 0, 'isshow' => 1),
		'subs' => array(),
	),
	'sort' => array(
		'data' => array('pos' => array('bbs','area'), 'nkey' => 'sort', 'title' => '统计排行', 'link' => 'sort.php', 'view'=>2, 'target' => 0, 'isshow' => 1),
		'subs' => array(),
	),
	'help' => array(
		'data' => array('pos' => array('bbs','area'), 'title' => '帮助', 'link' => 'faq.php', 'view'=>3, 'target' => 0, 'isshow' => 1),
	),
);

$view = 1;
unset($db_hackdb['app']);
foreach ($db_hackdb as $value) {
	list($title, $key, $valid) = $value;
	$link = 'toolcenter' == $key ? 'profile.php?action=toolcenter' : 'hack.php?H_name=' . $key;
	$defaults['hack']['subs'][$key] = array('nkey' => 'hack_' . $key, 'title' => $title, 'link' => $link, 'view' => $view++, 'isshow' => ($valid ? 1 : 0));
}

$view = 1;
//include R_P . 'lang/wind/cp_lang_all.php';
$bbsNavConfig = "社区应用,app,,root\n"
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
	. "标签排行,sort_taglist,link.php?action=taglist,sort\n";
foreach (explode("\n", $bbsNavConfig) as $value) {
	if (!trim($value)) continue;
	list($title, $key, $link, $upkey) = explode(',', trim($value));
	if ($upkey == 'sort') {
		$defaults['sort']['subs'][$key] = array('nkey' => $key, 'title' => trim($title), 'link' => trim($link), 'view' => $view++, 'isshow' => 1);
	}
}

foreach ($defaults as $key => $value) {
	$navId = $navConfigService->add(PW_NAV_TYPE_HEAD_RIGHT, $value['data']);
	if (isset($value['subs'])) {
		foreach ($value['subs'] as $sub) {
			$sub['upid'] = $navId;
			$navConfigService->add(PW_NAV_TYPE_HEAD_RIGHT, $sub);
		}
	}
}


?>