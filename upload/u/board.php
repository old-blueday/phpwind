<?php
!defined('R_P') && exit('Forbidden');
$USCR = 'space_board';
$isGM = S::inArray($windid,$manager);
!$isGM && $groupid==3 && $isGM=1;

S::gp(array('uid', 'page'),'',2);

require_once(R_P . 'u/lib/space.class.php');
$newSpace = new PwSpace($uid ? $uid : $winduid);
$space =& $newSpace->getInfo();

empty($space) && Showmsg('您访问的空间不存在!');

if (!$newSpace->viewRight('index')) {
	Showmsg('该空间设置隐私，您没有权限查看!');
}
if (!$newSpace->viewRight('messageboard')) {
	Showmsg('该空间留言板设置隐私，您没有权限查看!');
}
//需要重构
$uid = isset($uid) ? $uid : $winduid;

$basename = 'u.php?a=board&uid='.$uid.'&';
//$spaceurl = $basename."u=$u&";

require_once(R_P.'require/showimg.php');


if ($uid != $winduid) {
	$username	= $userdb['username'];
} else {
	$username	= $windid;
}

$db_perpage = 10;
$count	= $db->get_value("SELECT COUNT(*) AS count FROM pw_oboard WHERE touid=".S::sqlEscape($uid));
list($pages,$limit) = pwLimitPages($count,$page,$basename);

$boards = array();
require_once(R_P.'require/bbscode.php');
$wordsfb = L::loadClass('FilterUtil', 'filter');
$query = $db->query("SELECT o.*,m.icon as face FROM pw_oboard o LEFT JOIN pw_members m ON o.uid=m.uid WHERE o.touid=".S::sqlEscape($uid)." ORDER BY o.id DESC $limit");
while ($rt = $db->fetch_array($query)) {
	$rt['postdate']	= get_date($rt['postdate']);
	list($rt['face'])	=  showfacedesign($rt['face'],1,'m');
	if ($rt['groupid'] == 6 && $db_shield && $groupid != 3) {
		$rt['title'] = appShield('ban_feed');
	} elseif (!$wordsfb->equal($rt['ifwordsfb'])) {
		$rt['title'] = $wordsfb->convert($rt['title'], array(
			'id'	=> $rt['id'],
			'type'	=> 'comments',
			'code'	=> $rt['ifwordsfb']
		));
	}
	if (strpos($rt['title'],'[s:') !== false) {
		$tpc_author = $rt['username'];
		$rt['title'] = showface($rt['title']);
	}
	if (strpos($rt['title'],'[url') !== false) {
		$rt['title'] = convert($rt['title'],$db_windpost);
	}
	$boardids[] = $rt['id'];
	$boards[] = $rt;
}
if (!empty($boardids)) {
	$commentdb = getCommentDb('board',$boardids);
}

$friendsService = L::loadClass('Friend', 'friend'); /* @var $friendsService PW_Friend */
$ismyfriend = 1;
if ($friendsService->isFriend($winduid,$uid) !== true) $is_friend = 0;



$isSpace = true;
require_once uTemplate::PrintEot('space_board');
pwOutPut();
?>