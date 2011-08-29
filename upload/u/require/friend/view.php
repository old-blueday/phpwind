<?php
!defined('R_P') && exit('Forbidden');
empty($space) && Showmsg('您访问的空间不存在!');

if (!$newSpace->viewRight('index')) {
	Showmsg('该空间设置隐私，您没有权限查看!');
}
$basename = "u.php?a=$a&uid=$uid&";

$count  = 0;
$friendsService = L::loadClass('Friend', 'friend'); /* @var $friendsService PW_Friend */

/* 找出登录者的好友array(0=>uid1,1=>uid2,.......n=>uidn)*/
$uids = array();

$count = (int)$friendsService->countUserFriends($uid);
$page > ceil($count/$db_perpage) && $page = ceil($count/$db_perpage);
$friends = $count ? $friendsService->findUserFriendsInPage($uid, $page, $db_perpage) : array();
foreach ($friends as $key => $friend) {
	$uids[] = $friend['uid'];
}

$attentionSerivce = L::loadClass('attention', 'friend'); /* @var $attentionSerivce PW_Attention */
$myAttentionUids = $attentionSerivce->getUidsInFollowListByFriendids($winduid, $uids);

foreach ($friends as $key => $friend) {
	if (!S::inArray($friend['uid'], $myAttentionUids)) continue;
	$friends[$key]['attention'] = true;
}

$pages = numofpage($count,$page,ceil($count/$db_perpage),"{$basename}");
require_once (uTemplate::printEot('space_friend'));
pwOutPut();
?>