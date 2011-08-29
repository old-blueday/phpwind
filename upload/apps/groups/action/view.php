<?php
!defined('A_P') && exit('Forbidden');

require_once(R_P . 'require/showimg.php');
require_once(R_P . 'apps/groups/lib/colony.class.php');

$isGM = S::inArray($windid, $manager);
!$isGM && $groupid==3 && $isGM = 1;

if (!$isGM && $winduid != $space['uid']) {
	$userdb = $db->get_one("SELECT index_privacy FROM pw_ouserdata WHERE uid=" . S::sqlEscape($uid));
	list($isU, $privacy) = pwUserPrivacy($uid, $userdb);
	if (!$privacy['index']) {
		Showmsg('mode_o_index_right');
	}
}

$group = array();
$count = $db->get_value("SELECT COUNT(DISTINCT c.id) AS count FROM pw_cmembers cm LEFT JOIN pw_colonys c ON cm.colonyid=c.id WHERE cm.uid=" . S::sqlEscape($uid) . " AND cm.ifadmin<>'-1'");

if ($count) {
	$db_perpage = 4;
	$page = (int)S::getGP('page');
	$pageurl = 'apps.php?q=groups&uid=' . $uid . "&";
	list($pages,$limit) = pwLimitPages($count, $page, "$pageurl");
	$query = $db->query("SELECT DISTINCT c.* FROM pw_cmembers cm LEFT JOIN pw_colonys c ON cm.colonyid=c.id WHERE cm.uid=" . S::sqlEscape($uid) . " AND cm.ifadmin <> '-1' ORDER BY cm.colonyid DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		if ($rt['cnimg']) {
			list($rt['cnimg']) = geturl("cn_img/$rt[cnimg]", 'lf');
		} else {
			$rt['cnimg'] = $GLOBALS['imgpath'] . '/g/groupnopic.gif';
		}
		$rt['colonyNums'] = PwColony::calculateCredit($rt);
		//$rt['addtime'] = get_date($rt['addtime'], 'Y-m-d');
		$rt['createtime'] = get_date($rt['createtime'], 'Y-m-d');
		$group[] = $rt;
	}
}

require_once PrintEot('m_space_groups');
pwOutPut();
?>