<?php
!defined('W_P') && exit('Forbidden');
InitGP ( array ('uid','username') );
! $winduid && wap_msg ( 'not_login' );
$db_blogsource = array('web'=>'网页','signature'=>'个性签名','m'=>'手机','other'=>'其他');
if(!$uid && !$username){
	$uid = $winduid;
}
if ($uid && $uid != $winduid) {
	$userdb = $db->get_one("SELECT m.uid,m.icon,m.username,m.honor FROM pw_members m WHERE m.uid = ".pwEscape($uid));
} elseif ($username && $username != $windid){
	$userdb = $db->get_one("SELECT m.uid,m.icon,m.username,m.honor FROM pw_members m WHERE m.username = ".pwEscape($username));
	$uid = $userdb['uid'];
}else{
	$userdb = &$winddb;
	$uid = $userdb['uid'];
}
if (empty($userdb)) {
	$errorname = '';
	wap_msg('user_not_exists');
}
require_once(R_P.'require/showimg.php');
list($usericon) = showfacedesign($userdb['icon'], 1, 's');
list($lastDate) = getLastDate($userdb['postdate']);
require_once(W_P.'include/db/myspace.db.php');
$myspace = new MyspaceDB();
$myspace->setPerPage(5);
$myArticles = $myspace->getArticlesByUser($uid);
$myReplaies = $myspace->getReplaysByUser($uid);
if ($winduid == $uid) {
	$collectionService = L::loadClass('Collection', 'collection');
	$mydata = $collectionService->findByUidAndTypeInPage($winduid, 'postfavor', 1, 5, 'all');
	$myFavThreads = array();
	foreach ($mydata as $key=>$value) {
		$temp = array();
		$temp['url'] = "index.php?a=read&tid=".$value['typeid'];
		$temp['subject'] = $value['content']['postfavor']['subject'];
		$myFavThreads[] = $temp;
	}
}
wap_header ();
require_once PrintWAP ( 'myhome' );
wap_footer ();
?>
