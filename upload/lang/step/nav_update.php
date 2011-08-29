<?php
!defined('PW_UPLOAD') && exit('Forbidden');
$navConfigService = L::loadClass('navconfig', 'site'); /* @var $navConfigService PW_NavConfig */
$adds = 0;


//4.1.	原主导航

//原主导航升级，还是升级成主导航。门户的导航属性需要修改
$areaNav = $navConfigService->getByKey('area');
$areaNavId = $areaNav && isset($areaNav['nid']) ? $areaNav['nid'] : 0;
$navConfigService->update($areaNavId, array('floattype' => 'cross', 'listtype' => 'space', 'selflisttype' => 'space'));

//主导航中增加：门户频道
$channelService = L::loadClass('channelService', 'area');
foreach ($channelService->getChannels() as $alias => $channel) {
	if (!$navConfigService->getByKey('area_' . $alias)) {
		$link = "index.php?m=area&alias=" . $alias;
		$isShow = in_array($alias, array('bbsindex', 'home')) ? 0 : 1;
		$adds += (bool) $navConfigService->add(PW_NAV_TYPE_MAIN, array('nkey' => 'area_' . $alias, 'pos' => '-1', 'title' => $channel['name'], 'link' => $link, 'view' => $areaNav['view']++, 'upid' => 0, 'isshow' => $isShow));
	}
}
//主导航中增加：群组聚合
$adds += (bool)$navConfigService->add(PW_NAV_TYPE_MAIN, array('nkey' => 'group', 'pos' => '-1', 'title' => '群组', 'style' => '', 'link' => 'group.php', 'alt' => '', 'target' => 0, 'view' => 3, 'upid' => 0, 'isshow' => 1));

//4.2.	原模式导航

//原门户模式导航：升级后成为主导航中“门户”的二级导航。
$db->update("UPDATE pw_nav SET type=".pwEscape(PW_NAV_TYPE_MAIN).", upid=".pwEscape($areaNavId)." WHERE type='area_navinfo'");

//原论坛模式导航：升级后成为顶部右侧导航。
$db->update("UPDATE pw_nav SET type=".pwEscape(PW_NAV_TYPE_HEAD_RIGHT).", pos='bbs,area' WHERE type='bbs_navinfo'");

//原圈子模式导航：删除。
$db->update("DELETE FROM pw_nav WHERE type='o_navinfo'");

//4.3. 	原顶部导航：升级后成为顶部左侧导航。
$db->update("UPDATE pw_nav SET type=".pwEscape(PW_NAV_TYPE_HEAD_LEFT)." WHERE type='head'");

//4.4.	原底部导航：自定义数据保持升级，增加几个默认导航：联系我们、无图版、手机浏览
$db->update("DELETE FROM pw_nav WHERE type=".pwEscape(PW_NAV_TYPE_FOOT)." AND link IN (".pwImplode(array($db_ceoconnect, 'simple/', 'm/index.php')).")");
$defaults = array(
	array('pos' => '-1', 'title' => '联系我们', 'link' => $db_ceoconnect, 'view'=>1, 'target' => 0, 'isshow' => 1),
	array('pos' => '-1', 'title' => '无图版', 'link' => 'simple/', 'view'=>2, 'target' => 0, 'isshow' => 1),
	array('pos' => '-1', 'title' => '手机浏览', 'link' => 'm/', 'view'=>3, 'target' => 0, 'isshow' => 1),
);
foreach ($defaults as $key => $value) {
	$adds += (bool)$navConfigService->add(PW_NAV_TYPE_FOOT, $value);
}
