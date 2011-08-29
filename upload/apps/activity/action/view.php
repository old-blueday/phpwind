<?php
!defined('A_P') && exit('Forbidden');

!$winduid && Showmsg('not_login');
empty($space) && Showmsg('space_not_exist');
if (!$newSpace->viewRight('index')) {
	Showmsg('space_is_not_viewright!');
}

$basename .= 'uid='.$uid.'&';
$pageUrl = $basename;
$a && $pageUrl .= 'a='.$a.'&';
$see && $pageUrl .= 'see='.$see.'&';

L::loadClass('ActivityForO', 'activity', false);
$postActForO = new PW_ActivityForO();
$seeTitle = $postActForO->getSeeTitleBySee($see);

if (empty($see)) {
	S::gp(array('actmid'),GP,2);
	S::gp(array('timerange'),GP);
	//* @include_once pwCache::getPath(D_P."data/bbscache/activity_config.php");
	pwCache::getData(D_P."data/bbscache/activity_config.php");

	$where = " WHERE dv.ifrecycle=0";

	if ($actmid) {
		$where .= " AND dv.actmid=".S::sqlEscape($actmid);
		$pageUrl .= 'actmid='.$actmid.'&';
	}
	if (!empty($timerange)) {	
		if ('+' == $timerange[0]) {
			$timerange = (int)$timerange;
			if ($timerange > 0) {
				$startTimeBeforeTimestamp = $timerange + $timestamp;
				$where .= " AND dv.starttime <= ".S::sqlEscape($startTimeBeforeTimestamp);
				$pageUrl .= 'timerange=%2B'.$timerange.'&';
			}
		}
	}
	$timeSelectHtml = $postActForO->getTimeSelectHtml($timerange, 1, '');
	$actmidSelectHtml = $postActForO->getActmidSelectHtml($actmid, 1, '');

	$authoridTidDb = $myTidDb = $allActivityIdsIHaveParticipated = array();
	$fids = trim(getSpecialFid() . ",'0'",',');
	$query = $db->query("SELECT tid FROM pw_threads WHERE authorid=".S::sqlEscape($uid) . " AND special=8 AND fid NOT IN($fids)");
	while ($rt = $db->fetch_array($query)) {
		$authoridTidDb[] = $rt['tid'];//TA发布的
	}
	$allActivityIdsIHaveParticipated = $postActForO->getAllParticipatedActivityIdsByUid($uid);//TA参与的
	$myTidDb = array_merge($authoridTidDb,$allActivityIdsIHaveParticipated);
	is_array($myTidDb) && $myTidDb && $where .= " AND dv.tid IN (".S::sqlImplode($myTidDb).")";

	$defaultActivityValueTableName = getActivityValueTableNameByActmid();
	$count = $db->get_value("SELECT COUNT(*) AS count FROM $defaultActivityValueTableName dv LEFT JOIN pw_threads t USING (tid) $where");

	if ($count) {
		list($pages,$limit) = pwLimitPages($count,$page,$pageUrl);
		$activities = $activityIds = array();
		$query = $db->query("SELECT dv.*, t.subject, t.author, t.authorid, t.anonymous FROM $defaultActivityValueTableName dv LEFT JOIN pw_threads t USING (tid) $where ORDER BY dv.tid DESC $limit");

		while ($rt = $db->fetch_array($query)) {
			$rt['actionButton'] = $postActForO->getSignupHtml($rt);
			$rt['subject'] = substrs($rt['subject'],45);
			foreach (array('starttime', 'endtime') AS $timeType) {
				$rt[$timeType] = $postActForO->getTimeFromTimestamp($rt[$timeType], 'minute');
			}
			if (!$isGM && $rt['anonymous'] && $uid != $rt['authorid']) {
				$rt['author'] = $rt['authorid'] = '';
			}
			//预设报名人数为0
			$rt['signup'] = 0;
			$activities[$rt['tid']] = $rt;
			$activityIds[] = $rt['tid'];
		}
		
		//获取活动报名人数
		$query = $db->query("SELECT tid, SUM(signupnum) AS count FROM pw_activitymembers WHERE fupid=0 AND tid IN (".S::sqlImplode($activityIds).") AND ifpay IN('0','1','2','4') GROUP BY tid");
		while ($rt = $db->fetch_array($query)) {
			if (array_key_exists($rt['tid'], $activities)) {
				$activities[$rt['tid']]['signup'] = $rt['count'];
			}
		}
	}
} elseif ('fromgroup' == $see) { //来自群组
	
	S::gp(array('type'),GP,2);
	S::gp(array('timerange'),GP);
	
	$where = " WHERE 1";
	$timeSelectHtml = $postActForO->getTimeSelectHtml($timerange, 1, '');

	if ($type) {
		$where .= " AND a.type=".S::sqlEscape($type);
		$pageUrl .= 'type='.$type.'&';
		${'type_'.$type} = 'selected';
	}
	if (!empty($timerange)) {	
		if ('+' == $timerange[0]) {
			$timerange = (int)$timerange;
			if ($timerange > 0) {
				$startTimeBeforeTimestamp = $timerange + $timestamp;
				$where .= " AND a.begintime <= ".S::sqlEscape($startTimeBeforeTimestamp);
				$pageUrl .= 'timerange=%2B'.$timerange.'&';
			}
		}
	}

	$authoridTidDb = $myTidDb = $allActivityIdsIHaveParticipated = array();
	$query = $db->query("SELECT actid FROM pw_actmembers WHERE uid=" . S::sqlEscape($uid));
	while ($rt = $db->fetch_array($query)) {			
		$allActivityIdsIHaveParticipated[] = $rt['actid'];//TA参与的
	}
	$query = $db->query("SELECT id FROM pw_active WHERE uid=" . S::sqlEscape($uid));
	while ($rt = $db->fetch_array($query)) {			
		$authoridTidDb[] = $rt['id'];//TA发布的
	}
	$myTidDb = array_merge($authoridTidDb,$allActivityIdsIHaveParticipated);
	is_array($myTidDb) && $myTidDb && $where .= " AND a.id IN (".S::sqlImplode($myTidDb).")";
	
	
	$count = $db->get_value("SELECT COUNT(*) AS count FROM pw_active a $where");
	if ($count) {
		list($pages,$limit) = pwLimitPages($count,$page,$pageUrl);
		$activities = array();
		$query = $db->query("SELECT a.*,m.username FROM pw_active a LEFT JOIN pw_members m USING(uid) $where ORDER BY a.id DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['signup'] = $rt['members'];
			$rt['title'] = substrs($rt['title'],45);
			$rt['starttime'] = $rt['begintime'];
			$rt['location'] = $rt['address'];
			$rt['authorid'] = $rt['uid'];
			$rt['author'] = $rt['username'];
			$rt['actionButton'] = $postActForO->getGroupSignupHtml($rt);
			foreach (array('starttime', 'endtime') AS $timeType) {
				$rt[$timeType] = $postActForO->getTimeFromTimestamp($rt[$timeType], 'minute');
			}
			$activities[] = $rt;
		}
	}
}

require_once PrintEot('m_space_activity');
pwOutPut();

