<?php
!defined('R_P') && exit('Forbidden');
$USCR = 'user_friend';
require_once(R_P.'require/showimg.php');
include_once(D_P . 'data/bbscache/dbreg.php');
$isGM = CkInArray($windid, $manager);
!$isGM && $groupid==3 && $isGM=1;

InitGP(array('uid', 'page'));
$uid = intval($uid);
$page = intval($page);
$page < 1 && $page = 1;
$db_perpage = 10;

require_once(R_P . 'u/lib/space.class.php');
$newSpace = new PwSpace($uid ? $uid : $winduid);
$space =& $newSpace->getInfo();

if ($uid) {
	$isSpace = true;
	$USCR = 'space_friend';
	require_once Pcv('u/require/friend/view.php');
} else {
	!$winduid && Showmsg('not_login');
	require_once Pcv('u/require/friend/my.php');
}
exit;
?>