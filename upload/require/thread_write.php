<?php
!defined('R_P') && exit('Forbidden');

require_once(R_P . 'require/showimg.php');

$pwforum = new PwForum($fid);
if (!$pwforum->isForum(true)) {
	Showmsg('data_error');
}
$foruminfo =& $pwforum->foruminfo;
$groupRight =& $newColony->getRight();
$pwModeImg = "$imgpath/apps";
require_once(R_P . 'u/require/core.php');
//* include_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
pwCache::getData(D_P . 'data/bbscache/o_config.php');

require_once(R_P . 'require/header.php');
list($guidename, $forumtitle) = $pwforum->getTitle();
$msg_guide = $pwforum->headguide($guidename);

$styleid = $colony['styleid'];
$basename = "thread.php?cyid=$cyid&showtype=write";

if (!$colony['ifwriteopen'] && !$ifadmin && (!$colony['ifcyer'] || $colony['ifadmin'] == '-1')) {
	Showmsg('colony_cnmenber');
}
list($faceurl) = showfacedesign($winddb['icon'], 1, 'm');

$writedata = $typeid = array();

if ($count2 = $colony['writenum']) {
	$smileParser = L::loadClass('smileparser', 'smile'); /* @var $smileParser PW_SmileParser */
	require_once(R_P.'require/showimg.php');
	$page = (int)S::getGP('page');
	list($pages,$limit) = pwLimitPages($count2,$page,"{$basename}&showtype=write&cyid=$cyid&");
	$query = $db->query("SELECT w.*,m.username,m.icon,m.groupid FROM pw_cwritedata w LEFT JOIN pw_members m ON w.uid=m.uid WHERE w.cyid=".S::sqlEscape($cyid)." ORDER BY w.replay_time DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		if ($rt['groupid'] == 6 && $db_shield && $groupid != 3) {
			$rt['content'] = appShield('ban_write');
		}
		$rt['content'] = $smileParser->parse($rt['content']);
		list($rt['postdate']) = getLastDate($rt['postdate']);
		list($rt['icon']) = showfacedesign($rt['icon'],1,'m');
		$writedata[$rt['id']] = $rt;
		$typeid[] = $rt['id'];
	}
}

if ($typeid) {
	$sql = "SELECT tt.* FROM (SELECT cm.*,m.icon FROM pw_comment cm LEFT JOIN pw_members m ON cm.uid=m.uid WHERE type='groupwrite' AND typeid in (" . S::sqlImplode($typeid, false) . ") ORDER BY cm.id DESC) tt GROUP BY tt.typeid ";
	$query2 = $db->query($sql);
	while ($rt2 = $db->fetch_array($query2)) {
		$writedata[$rt2['typeid']]['replayuid'] = $rt2['uid'];
		$writedata[$rt2['typeid']]['replayusername'] = $rt2['username'];
		$writedata[$rt2['typeid']]['replaytitle'] = $rt2['title'];
		list($writedata[$rt2['typeid']]['replaypoastdate']) = getLastDate($rt2['postdate']);
		list($writedata[$rt2['typeid']]['replayicon']) = showfacedesign($rt2['icon'], 1, 'm');
	}
}
require_once PrintEot('thread_write');
footer();
?>
