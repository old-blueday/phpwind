<?php
!defined('A_P') && exit('Forbidden');

!$winduid && Showmsg('not_login');
empty($space) && Showmsg('space_not_exist');

$pageUrl = $basename;
$a && $pageUrl .= 'a='.$a.'&';
$see && $pageUrl .= 'see='.$see.'&';

L::loadClass('ActivityForO', 'activity', false);
$postActForO = new PW_ActivityForO();
$seeTitle = $postActForO->getSeeTitleBySee($see);

if (empty($see)) {
	S::gp(array('actmid'),GP,2);
	S::gp(array('timerange'),GP);
	
	// * @include_once pwCache::getPath(D_P."data/bbscache/activity_config.php");
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
				$startTimeBeforeTimestamp = $timestamp + $timerange;
				$where .= " AND (dv.starttime <= ".S::sqlEscape($startTimeBeforeTimestamp)."AND dv.starttime >".S::sqlEscape($timestamp)."OR (dv.endtime <=".S::sqlEscape($startTimeBeforeTimestamp)."And dv.endtime >".S::sqlEscape($timestamp)." ))";
				$pageUrl .= 'timerange=%2B'.$timerange.'&';
			}
		}
	}
	$timeSelectHtml = $postActForO->getTimeSelectHtml($timerange, 1, '');
	$actmidSelectHtml = $postActForO->getActmidSelectHtml($actmid, 1, '');
	if (empty($a)) { //我的活动
		$authoridTidDb = $myTidDb = $allActivityIdsIHaveParticipated = array();
		$fids = trim(getSpecialFid() . ",'0'",',');
		$query = $db->query("SELECT tid FROM pw_threads WHERE authorid=".S::sqlEscape($winduid) . " AND special=8 AND fid not IN($fids)");
		while ($rt = $db->fetch_array($query)) {
			$authoridTidDb[] = $rt['tid'];//我发布的
		}
		$allActivityIdsIHaveParticipated = $postActForO->getAllParticipatedActivityIdsByUid($winduid);//我参与的
		$myTidDb = array_merge($authoridTidDb,$allActivityIdsIHaveParticipated);
		empty($myTidDb) && $myTidDb=array(-1);
		is_array($myTidDb) && $myTidDb && $where .= " AND dv.tid IN (".S::sqlImplode($myTidDb).")";
	} elseif ($a == 'recommended') { //推荐的活动
		$where .= " AND dv.recommend = 1";
	}
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
			if (!$isGM && $rt['anonymous'] && $winduid != $rt['authorid']) {
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
				$where .= " AND (a.begintime <= ".S::sqlEscape($startTimeBeforeTimestamp)."AND a.begintime >".S::sqlEscape($timestamp)."OR (a.endtime <=".S::sqlEscape($startTimeBeforeTimestamp)."And a.endtime >".S::sqlEscape($timestamp)." ))";
				$pageUrl .= 'timerange=%2B'.$timerange.'&';
			}
		}
	}
	
	if (empty($a)) { //我的活动
		$authoridTidDb = $myTidDb = $allActivityIdsIHaveParticipated = array();
		$query = $db->query("SELECT actid FROM pw_actmembers WHERE uid=" . S::sqlEscape($winduid));
		while ($rt = $db->fetch_array($query)) {			
			$allActivityIdsIHaveParticipated[] = $rt['actid'];//我参与的
		}
		$query = $db->query("SELECT id FROM pw_active WHERE uid=" . S::sqlEscape($winduid));
		while ($rt = $db->fetch_array($query)) {			
			$authoridTidDb[] = $rt['id'];//我发布的
		}
		$myTidDb = array_merge($authoridTidDb,$allActivityIdsIHaveParticipated);
		empty($myTidDb) && $myTidDb=array(-1);
		is_array($myTidDb) && $myTidDb && $where .= " AND a.id IN (".S::sqlImplode($myTidDb).")";
	} else { //所有活动
		$where .= " ";
	}
		
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

} elseif ('feeslog' == $see) { //费用流通日志

	
	$db_perpage = 30;
	
	S::gp(array('feeslogstatus', 'activityname', 'otherpartyname'),GP);
	$feesLogStatusSelectHtml = getSelectHtml($postActForO->getFeesLogStatus(), $feeslogstatus, 'feeslogstatus');

	$uidDb = $authoridDb = $fromuidDb = $actpidDB = array();
	$query = $db->query("SELECT actpid FROM pw_activitypaylog WHERE uid = ".S::sqlEscape($winduid));
	while ($rt = $db->fetch_array($query)) {
		$uidDb[] = $rt['actpid'];
	}
	$query = $db->query("SELECT actpid FROM pw_activitypaylog WHERE authorid = ".S::sqlEscape($winduid));
	while ($rt = $db->fetch_array($query)) {
		$authoridDb[] = $rt['actpid'];
	}
	$query = $db->query("SELECT actpid FROM pw_activitypaylog WHERE fromuid = ".S::sqlEscape($winduid));
	while ($rt = $db->fetch_array($query)) {
		$fromuidDb[] = $rt['actpid'];
	}

	$where = 'WHERE 1';
	$actpidDB = array_merge($uidDb,$authoridDb,$fromuidDb);
	empty($actpidDB) && $actpidDB =array(-1);
	is_array($actpidDB) && $actpidDB && $where .= " AND log.actpid IN (".S::sqlImplode($actpidDB).")";
	
	if ($feeslogstatus) {
		$where .= " AND status = ".S::sqlEscape($feeslogstatus);
		$pageUrl .= 'feeslogstatus='.$feeslogstatus.'&';
	}
	if ($activityname) {
		$where .= " AND subject LIKE ".S::sqlEscape('%'.$activityname.'%');
		$pageUrl .= 'activityname='.$activityname.'&';
	}
	if ($otherpartyname) {
		$where .= " AND (log.username LIKE ".S::sqlEscape('%'.$otherpartyname.'%').
					" OR author LIKE ".S::sqlEscape('%'.$otherpartyname.'%').
					" OR log.fromusername LIKE " . S::sqlEscape('%' . $otherpartyname . '%') . 
					") AND '" . $otherpartyname . "'!='" . $windid . "'";
		$pageUrl .= 'otherpartyname='.$otherpartyname.'&';
	}

	$count = $db->get_value("SELECT count(*) AS count FROM pw_activitypaylog log $where");
	if ($count) {
		list($pages,$limit) = pwLimitPages($count,$page,$pageUrl);
		$query = $db->query("SELECT log.*, m.ifpay AS ifpay, log.fromuid AS fromuid, 
							log.fromusername AS fromusername, log.username AS username, log.uid AS uid,m.issubstitute AS issubstitute
							FROM pw_activitypaylog log
							LEFT JOIN pw_activitymembers m ON log.actuid=m.actuid 
							$where ORDER BY tid DESC, actpid DESC $limit");
		$feesLog = array();
		while ($rt = $db->fetch_array($query)) {
			$rt['subject'] = substrs($rt['subject'],20);
			$rt['createtime'] = $postActForO->getTimeFromTimestamp($rt['createtime'], 'minute');
			$rt['statusText'] = $postActForO->getFeesLogStatus($rt['status']);
			//获得支付说明，收入或支出，对方
			$postActForO->getPayLogDescription($rt);
			$rt['costtype'] = $postActForO->getFeesLogCostType($rt['costtype']);
			$actpid = $rt['actpid'];
			$tid = $rt['tid'];
			$feesLog[$tid][$actpid] = $rt;
		}
	}
}
require_once PrintEot('m_activity');
pwOutPut();