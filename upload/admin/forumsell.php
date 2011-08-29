<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$admin_file?adminjob=forumsell";

if (empty($action)) {

	require_once(R_P.'require/credit.php');
	//* include_once pwCache::getPath(D_P.'data/bbscache/forumcache.php');
	pwCache::getData(D_P.'data/bbscache/forumcache.php');
	S::gp(array('username'));
	S::gp(array('page','uid','fid'),'GP',2);

	$sql = "WHERE 1";
	if ($username) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userdb = $userService->getByUserName($username);
		if (!$userdb) {
			$errorname = $username;
			adminmsg('user_not_exists');
		}
		$uid = $userdb['uid'];
	}
	if ($uid) {
		$sql .= " AND fs.uid=".S::sqlEscape($uid);
	}
	if ($fid) {
		$sql .= " AND fs.fid=".S::sqlEscape($fid);
	}
	$page < 1 && $page = 1;
	$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
	$rt = $db->get_one("SELECT COUNT(*) AS sum FROM pw_forumsell fs $sql");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&uid=$uid&fid=$fid&");
	$buydb = array();
	$query = $db->query("SELECT fs.*,m.username,m.uid FROM pw_forumsell fs LEFT JOIN pw_members m USING(uid) $sql ORDER BY fs.overdate DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['buydate']	= get_date($rt['buydate']);
		$rt['overtime']	= get_date($rt['overdate']);
		$buydb[] = $rt;
	}

	include PrintEot('forumsell');exit;

} elseif ($_POST['action'] == 'del') {

	S::gp(array('selid'));
	if (!$selid = checkselid($selid)) {
		adminmsg('operate_error');
	}
	$db->update("DELETE FROM pw_forumsell WHERE id IN($selid)");
	adminmsg('operate_success');
}
?>