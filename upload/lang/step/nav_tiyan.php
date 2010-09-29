<?php
!defined('PW_UPLOAD') && exit('Forbidden');
$navConfigService = L::loadClass('navconfig', 'site'); /* @var $navConfigService PW_NavConfig */
$adds = 0;
$view = 10;

$channels = array(
    'baby' => array('name' => '亲子'),
    'delicious' => array('name' => '美食'),
    'decoration' => array('name' => '家装'),
    'finance' => array('name' => '财经'),
	'travel' => array('name' => '旅游'),
);

$upNav = $navConfigService->getByKey('area');
$upId = $upNav && isset($upNav['nid']) ? $upNav['nid'] : 0;
foreach ($channels as $alias => $channel) {
	$link = "index.php?m=area&alias=".$alias;
	$adds += (bool)$navConfigService->add(PW_NAV_TYPE_MAIN, array('nkey' => 'area_'.$alias, 'pos' => '-1', 'title' => $channel['name'], 'link' => $link, 'view' => $view++, 'upid' => $upId, 'isshow' => 1));
}
