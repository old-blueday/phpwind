<?php
!defined('A_P') && exit('Forbidden');

!$db_dopen && Showmsg('dairy_close');
$USCR = 'user_diary';
//* include pwCache::getPath(R_P. 'data/bbscache/o_config.php');
pwCache::getData(R_P. 'data/bbscache/o_config.php');
//* require_once pwCache::getPath(D_P."data/bbscache/forum_cache.php");
pwCache::getData(D_P."data/bbscache/forum_cache.php");
require_once(R_P.'require/showimg.php');
if (!$space =& $newSpace->getInfo()) {
	Showmsg('您访问的空间不存在!');
}
$isGM = S::inArray($windid, $manager);
!$isGM && $groupid==3 && $isGM=1;
$indexRight = $newSpace->viewRight('index');
$indexValue = $newSpace->getPrivacyByKey('index');
if ($db_question && $o_diary_qcheck) {
	$qkey = array_rand($db_question);

}
S::gp(array('a', 'uid','username', 'page','ajax'));

if ($username && !$uid) {
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$uid = $userService->getUserIdByUserName($username);
}

$uid = intval($uid);
$page = intval($page);
$page < 1 && $page = 1;
$db_perpage = 10;

require_once(R_P . 'u/lib/space.class.php');
$newSpace = new PwSpace($uid ? $uid : $winduid);
$space =& $newSpace->getInfo();
empty($space) && Showmsg('user_not_exists');
list(,$showq) = explode("\t", $db_qcheck);
if ($ajax == '1') {
	require_once S::escapePath($appEntryBasePath . 'action/ajax.php');
} elseif ($uid) {
	$isSpace = true;
	$USCR = 'space_diary';
	require_once S::escapePath($appEntryBasePath . 'action/view.php');
} else {
	require_once S::escapePath($appEntryBasePath . 'action/my.php');
} 
exit;
//TODO ajax route

