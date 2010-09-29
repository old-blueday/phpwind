<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=customcredit";

require_once(R_P."require/credit.php");

if (empty($action)) {

	InitGP(array('page'),'GP',2);
	$page<1 && $page = 1;
	$limit = pwLimit(($page-1)*$db_perpage,$db_perpage);
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_membercredit WHERE value!=0");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&");

	$query = $db->query("SELECT m.uid,m.username,mc.cid,mc.value FROM pw_membercredit mc LEFT JOIN pw_members m USING(uid) WHERE value!=0 ORDER BY cid, value DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['name'] = $_CREDITDB[$rt['cid']][0];
		$creditdb[] = $rt;
	}
	include PrintEot('customcredit');exit;

} elseif ($action == 'edit') {

	if (empty($_POST['step'])) {

		InitGP(array('uid','username'));
		
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		if(is_numeric($uid)){
			$rt = $userService->get($uid);
		} else{
			$rt = $userService->getByUserName($username);
		}

		if (!$rt) {
			$errorname = $username;
			adminmsg('user_not_exists');
		}
		$u_credit = $credit->get($rt['uid'],'CUSTOM');
		include PrintEot('customcredit');exit;

	} else {

		InitGP(array('uid','creditdb'),'P');
		!is_numeric($uid) && adminmsg('operate_error');
		foreach ($creditdb as $key => $value) {
			if (is_numeric($key) && is_numeric($value)) {
				$db->pw_update(
					"SELECT uid FROM pw_membercredit WHERE uid=".pwEscape($uid)."AND cid=".pwEscape($key),
					"UPDATE pw_membercredit SET value=".pwEscape($value)."WHERE uid=".pwEscape($uid)."AND cid=".pwEscape($key),
					"INSERT INTO pw_membercredit SET ".pwSqlSingle(array('uid'=>$uid,'cid'=>$key,'value'=>$value))
				);
			}
		}
		adminmsg('operate_success');
	}
}
?>