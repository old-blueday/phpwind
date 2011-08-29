<?php
!defined('A_P') && exit('Forbidden');

S::gp(array('a', 'do' ,'uid', 'page','ajax'));
$page = intval($page);
$page < 1 && $page = 1;
$db_perpage = 10;

//* include pwCache::getPath(R_P. 'data/bbscache/o_config.php');
pwCache::getData(R_P. 'data/bbscache/o_config.php');
if(!$winduid && in_array($do,array('transmit','comment'))) Showmsg('not_login');
if (!$space =& $newSpace->getInfo()) {
	Showmsg('您访问的空间不存在!');
}
$isGM = S::inArray($windid, $manager);
!$isGM && $groupid==3 && $isGM=1;
$indexRight = $newSpace->viewRight('index');
$indexValue = $newSpace->getPrivacyByKey('index');
$weiboService = L::loadClass('weibo','sns');/* @var $weiboService PW_Weibo */
$USCR = 'space_weibo';
$basename = 'apps.php?q='.$q.'&';

if ($uid) {
	$isSpace = true;
	require_once S::escapePath($appEntryBasePath . 'action/view.php');
} else {
	require_once S::escapePath($appEntryBasePath . 'action/my.php');
}