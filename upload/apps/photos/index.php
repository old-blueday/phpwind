<?php
!defined('A_P') && exit('Forbidden');
!$db_phopen && Showmsg('photos_close');
$USCR = 'user_photos';
//* include_once pwCache::getPath(D_P.'data/bbscache/o_config.php');
pwCache::getData(D_P.'data/bbscache/o_config.php');
if ($db_question && $o_photos_qcheck) {
	$qkey = array_rand($db_question);
}
require_once(R_P.'require/showimg.php');
$basename = 'apps.php?q='.$q.'&';
S::gp(array('a','s','uid','username','page','ifriend'));


$uid = intval($uid);
$page = intval($page);
$page < 1 && $page = 1;
$perpage = 20;
if ($username && !$uid) {
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$uid = $userService->getUserIdByUserName($username);
}
$isGM = S::inArray($windid, $manager);
!$isGM && $groupid==3 && $isGM=1;
$indexRight = $newSpace->viewRight('index');
$indexValue = $newSpace->getPrivacyByKey('index');
$isSpace = false;
if ($uid && intval($uid) > 0) {
	$isSpace = true;
}
$uid = $uid ? $uid : $winduid;
if (!$space =& $newSpace->getInfo()) {
	Showmsg('您访问的空间不存在!');
}
list(,$showq) = explode("\t", $db_qcheck);
if (!$uid) {
	S::gp(array('a','q','pid','aid'));
	if (is_numeric($pid) && ('photos' == $q) && ('album' == $a)) {
		$url = $db_bbsurl."/apps.php?q=photos&a=view&pid=".$pid;	
		$result = $db->get_one("SELECT p.aid,c.ownerid FROM pw_cnphoto p LEFT JOIN pw_cnalbum c ON p.aid=c.aid WHERE p.pid=".S::sqlEscape($pid));	
	} elseif(is_numeric($aid) && ('photos' == $q) && ('album' == $a)) {
		$url = $db_bbsurl."/apps.php?q=photos&a=album&aid=".$aid;
		$result = $db->get_one("SELECT p.ownerid,p.aid FROM pw_cnalbum p WHERE p.aid=".S::sqlEscape($aid));
	} else {
		Showmsg("user_not_exists");
	}
	if (!is_array($result)) {
		Showmsg($result);
	}
	$url .= "&uid=".$result['ownerid'];
	ObHeader($url);
};
/*
require_once(R_P . 'u/lib/space.class.php');
$newSpace = new PwSpace($uid ? $uid : $winduid);
$space =& $newSpace->getInfo();
empty($space) && Showmsg('user_not_exists');
*/

L::loadClass('photo', 'colony', false);
$photoService = new PW_Photo($uid,$ifriend,$page,$perpage);
$isGM = $photoService->isPermission();
$isown = $photoService->isSelf();

if ($isSpace) {
	$USCR = 'space_photos';
//	S::gp('uid');
	$newSpace = new PwSpace($uid ? $uid : $winduid);
	$photoRight=$newSpace->viewRight('photos');
	require_once S::escapePath($appEntryBasePath . 'action/view.php');
} else {
	!$winduid && Showmsg('not_login');	
	if ($isown) {
		$a_key = $a == 'friend' ? 'index' : 'own';
		$a_key = $ifriend ? 'index' : $a_key;
		require_once S::escapePath($appEntryBasePath . 'action/my.php');
	} else {
		Showmsg('undefined_action');
	}
}

function createfail($checkpwd,$showinfo='',$type='fail') {
	if ($checkpwd) {
		$showinfo = 'fail' == $type && ''!= $showinfo ? getLangInfo('msg',$showinfo) : $showinfo;
		echo "$type\t$showinfo";
		ajax_footer();
	}
	return false;
}

?>