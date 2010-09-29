<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$admin_file?adminjob=viewban";

if (empty($action)) {

	InitGP(array('page','banuser','bantype','adminban','starttime','endtime'));
	(!is_numeric($page) || $page < 1) && $page = 1;
	$limit = pwLimit(($page-1)*$db_perpage,$db_perpage);
	$sql = "WHERE 1";
	$url = $basename;
	$count = 0;
	if ($banuser) {
		$sql .= " AND m.username=".pwEscape($banuser);
		$count = 1;
	}
	if ($bantype) {
		$sql .= " AND b.type=".pwEscape($bantype);
		$url .= "&bantype=$bantype";
	}
	if ($adminban) {
		$sql .= " AND b.admin=".pwEscape($adminban);
		$url .= "&adminban=".rawurlencode($adminban);
	}
	if ($starttime) {
		!is_numeric($starttime) && $starttime = PwStrtoTime($starttime);
		$sql .= " AND b.startdate>".pwEscape($starttime);
		$url .= "&starttime=$starttime";
	}
	if ($endtime) {
		!is_numeric($endtime) && $endtime = PwStrtoTime($endtime);
		$sql .= " AND b.startdate<".pwEscape($endtime);
		$url .= "&endtime=$endtime";
	}
	if ($count < 1) {
		@extract($db->get_one("SELECT COUNT(*) AS count FROM pw_banuser b $sql"));
		$pages = numofpage($count,$page,ceil($count/$db_perpage),"$url&");
	}
	$bandb = $ids = $uids1 = $uids2 = array();
	$query = $db->query("SELECT b.*, m.username FROM pw_banuser b LEFT JOIN pw_members m ON b.uid=m.uid $sql ORDER BY b.uid DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		if ($rt['type'] == 1 && $timestamp - $rt['startdate'] > $rt['days']*86400) {
			$ids[] = $rt['id'];
			if ($rt['fid']) {
				$uids2[] = $rt['uid'];
			} else {
				$uids1[] = $rt['uid'];
			}
		} else {
			$rt['startdate'] && $rt['date'] = get_date($rt['startdate']);
			$bandb[] = $rt;
		}
	}
	if ($ids) {
		$db->update("DELETE FROM pw_banuser WHERE id IN(".pwImplode($ids).")");
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->updates($uids1, array('groupid' => -1));
		$uids2 && $db->update("UPDATE pw_members m LEFT JOIN pw_banuser b ON m.uid=b.uid AND b.fid>0 SET m.userstatus=m.userstatus&(~1) WHERE b.uid is NULL AND m.uid IN(".pwImplode($uids2).")");
	}
	include PrintEot('viewban');exit;

} elseif ($_POST['action'] == 'freeban') {

	InitGP(array('free'),'P');
	!$free && adminmsg('operate_error');
	$ids = pwImplode($free);

	$uids1 = $uids2 = array();
	$_cache = getDatastore();
	$userNames = array();
	$query = $db->query("SELECT b.*,m.username FROM pw_banuser b LEFT JOIN pw_members m ON b.uid=m.uid WHERE b.uid IN ($ids)");
	while ($rt = $db->fetch_array($query)) {
		$userNames[] = $rt['username'];
		$_cache->delete('UID_'.$rt['uid']);
		if ($rt['fid']) {
			$uids2[] = $rt['uid'];
		} else {
			$uids1[] = $rt['uid'];
		}
	}
	$db->update("DELETE FROM pw_banuser WHERE uid IN($ids)");
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$userService->updates($uids1, array('groupid'=>-1));
	$uids2 && $db->update("UPDATE pw_members m LEFT JOIN pw_banuser b ON m.uid=b.uid AND b.fid>0 SET m.userstatus=m.userstatus&(~1) WHERE b.uid is NULL AND m.uid IN(".pwImplode($uids2).")");

	M::sendNotice(
		$userNames,
		array(
			'title' => getLangInfo('writemsg','banuser_free_title'),
			'content' => getLangInfo('writemsg','banuser_free_content',array(
				'manager'	=> $admin_name,
			)),
		)
	);
	adminmsg('operate_success');
}
?>