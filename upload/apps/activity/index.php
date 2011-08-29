<?php
!defined('A_P') && exit('Forbidden');

!$winduid && Showmsg('not_login');
//* require_once pwCache::getPath(D_P."data/bbscache/forum_cache.php");
pwCache::getData(D_P."data/bbscache/forum_cache.php");
require_once(R_P.'require/showimg.php');

$USCR = 'user_activity';

$isGM = S::inArray($windid, $manager);

S::gp(array('a', 'see' , 'username'));
S::gp(array('uid', 'page','ajax'),GP,2);

$basename = 'apps.php?q='.$q.'&';

$db_perpage = 10;
$page < 1 && $page = 1;

if ($username && !$uid) {
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$uid = $userService->getUserIdByUserName($username);
}

//* include pwCache::getPath(D_P. 'data/bbscache/o_config.php');
pwCache::getData(D_P. 'data/bbscache/o_config.php');
require_once(R_P . 'u/lib/space.class.php');
$newSpace = new PwSpace($uid ? $uid : $winduid);
$space =& $newSpace->getInfo();

if ($ajax == '1') {
	require_once S::escapePath($appEntryBasePath . 'action/ajax.php');
} elseif ($uid) {
	$isSpace = true;
	$USCR = 'space_activity';
	require_once S::escapePath($appEntryBasePath . 'action/view.php');
} else {
	require_once S::escapePath($appEntryBasePath . 'action/my.php');
} 
exit;
//TODO ajax route

