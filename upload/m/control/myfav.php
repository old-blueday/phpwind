<?php
!defined('W_P') && exit('Forbidden');
! $winduid && wap_msg ( 'not_login' );
InitGP ( array ('page', 'type', 'fr','from' ) );
if ($fr == 'index') {
	$returnUrl = "index.php";
} else {
	$returnUrl = "index.php?a=mybbs";
}
InitGP(array('uid'));
!$uid && $uid = $winduid;
$userdb = $db->get_one("SELECT m.uid,m.icon,m.username FROM pw_members m WHERE m.uid = ".pwEscape($uid));
require_once(W_P.'include/db/myspace.db.php');
$myspace = new MyspaceDB();
if (!empty($type)) {
	if ($type == 'my') {
		$mydata = $myspace->getArticlesByUser($uid,$page);
	} elseif ($type == 're') {
		$mydata = $myspace->getReplaysByUser($uid,$page);
	} elseif ($type == 'fav' && $winduid == $uid) {
		if (!$page) $page = 1;
		//$mydata = $myspace->getFavsByUser($uid,$page);
		$collectionService = L::loadClass('Collection', 'collection');
		$myFavThreads = $collectionService->findByUidAndTypeInPage($winduid, 'postfavor', $page, $wap_perpage, 'all');
		$mydata = array();
		foreach ($myFavThreads as $key=>$value) {
			$temp = array();
			$temp['url'] = "index.php?a=read&tid=".$value['typeid'];
			$temp['subject'] = $value['content']['postfavor']['subject'];
			$mydata[] = $temp;
		}
	}
	$url = "index.php?a=myfav&" . ($type ? "&amp;type=$type" : "") . "&amp;".($uid? "&amp;uid=$uid" : "") . "&amp;";
	$pages = getPages($page,count($mydata),$url);
}else {
	$myArticles = $myspace->getArticlesByUser($uid);
	$myReplaies =  $myspace->getReplaysByUser($uid);
	if ($winduid == $uid) {
		$collectionService = L::loadClass('Collection', 'collection');
		$results = $collectionService->findByUidAndTypeInPage($winduid, 'postfavor', 1, 5, 'all');
		//$myFavThreads = $myspace->getFavsByUser($uid);
		$myFavThreads = array();
		foreach ($results as $key=>$value) {
			$temp = array();
			$temp['url'] = "index.php?a=read&tid=".$value['typeid'];
			$temp['subject'] = $value['content']['postfavor']['subject'];
			$myFavThreads[] = $temp;
		}
	}
}
wap_header ();
require_once PrintWAP ( 'myfav' );
wap_footer ();
?>