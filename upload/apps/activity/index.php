<?php
!defined('A_P') && exit('Forbidden');

!$winduid && Showmsg('not_login');
require_once(D_P."data/bbscache/forum_cache.php");
require_once(R_P.'require/showimg.php');

$USCR = 'user_activity';

$isGM = CkInArray($windid, $manager);

InitGP(array('a', 'see' , 'username'));
InitGP(array('uid', 'page','ajax'),GP,2);

$basename = 'apps.php?q='.$q.'&';

$db_perpage = 10;
$page < 1 && $page = 1;

if ($username && !$uid) {
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$uid = $userService->getUserIdByUserName($username);
}

include(D_P. 'data/bbscache/o_config.php');
require_once(R_P . 'u/lib/space.class.php');
$newSpace = new PwSpace($uid ? $uid : $winduid);
$space =& $newSpace->getInfo();

if ($ajax == '1') {
	require_once Pcv($appEntryBasePath . 'action/ajax.php');
} elseif ($uid) {
	$isSpace = true;
	$USCR = 'space_activity';
	require_once Pcv($appEntryBasePath . 'action/view.php');
} else {
	require_once Pcv($appEntryBasePath . 'action/my.php');
} 
exit;
//TODO ajax route

