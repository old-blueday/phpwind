<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$admin_file?adminjob=banuser";
S::gp(array('action', 'step'));
!$adminitem && $adminitem = 'banuser';

if ($adminitem == 'banuser') {
	!$action && $action = 'banuser';
	if (!$step) {
		S::gp(array('username', 'userid'),'G');
		$select[$db_banby] = 'selected';
		$db_banlimit = (int)$db_banlimit;
		$db_autoban ? $autoban_Y = 'checked' : $autoban_N = 'checked';
		$db_bantype == 2 ? $bantype_2 = 'checked' : $bantype_1 = 'checked';
		include PrintEot('banuser');exit;
	}
	if ($action == 'banuser') {
		S::gp(array('username', 'userid', 'ban_reason'),'P');
		S::gp(array('limit','type'),'P',2);
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		!$userid && $username && $userdb = $userService->getByUserName($username);
		$userid && $userdb = $userService->get($userid);
		if (!$userdb) {
			$errorname = $username;
			adminmsg('user_not_exists', $basename . '&adminitem=banuser&action=banuser');
		}
		//Vars for banservice
		//* require pwCache::getPath(D_P . "data/groupdb/group_{$admin_gid}.php");
		pwCache::getData(S::escapePath(D_P . "data/groupdb/group_{$admin_gid}.php"));
		$windid = $admin_name;
		//end Vars
		$banUserService = L::loadClass('BanUser', 'user'); /* @var $banUserService PW_BanUser */
		!$limit && $type = 2;
		$params = array(
			'limit' => $limit,
			'type' => $type,
			'ifmsg' => 1,
			'range' => 1,
			'reason' => $ban_reason
		);
		$return = $banUserService->ban($userdb['uid'],$params);
		if($return === true){
			adminmsg('operate_success', $basename . '&adminitem=banuser&action=banuser');
		}else{
			showmsg($return, $basename . '&adminitem=banuser&action=banuser');
		}
	} elseif ($action == 'freeuser') {
		S::gp(array('username', 'userid'),'P');
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$username && $userdb = $userService->getByUserName($username);
		$userid && $userdb = $userService->get($userid);
		if (!$userdb) {
			$errorname = $username;
			adminmsg('user_not_exists', $basename . '&adminitem=banuser&action=freeuser');
		}
		//Vars for banservice
		//* require pwCache::getPath(D_P . "data/groupdb/group_{$admin_gid}.php");
		pwCache::getData(S::escapePath(D_P . "data/groupdb/group_{$admin_gid}.php"));
		$windid = $admin_name;
		//end Vars
		$banUserService = L::loadClass('BanUser', 'user'); /* @var $banUserService PW_BanUser */
		$params = array(
			'ifmsg' => 1
		);
		$return = $banUserService->banfree($userdb['uid'],$params);
		
		if ($return === true) {
			adminmsg('operate_success', $basename . '&adminitem=banuser&action=freeuser');
		} else {
			showmsg($return, $basename . '&adminitem=banuser&action=freeuser');
		}
	} elseif ($action == 'autoban') {
		S::gp(array('ban'),'P');
		foreach ($ban as $key => $value) {
			if (${'db_'.$key} != $value) {
				setConfig('db_' . $key, $value);
			}
		}
		updatecache_c();
		adminmsg('operate_success', $basename . '&adminitem=banuser&action=autoban');
	}
} elseif ($adminitem == 'viewban') {
	if (empty($action)) {
		S::gp(array('page','banuser','banuseruid','bantype','adminban','starttime','endtime'));
		(!is_numeric($page) || $page < 1) && $page = 1;
		$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
		$sql = "WHERE 1";
		$url = $basename . '&adminitem=viewban';
		$count = 0;
		if ($banuser) {
			$sql .= " AND m.username=".S::sqlEscape($banuser);
			$count = 1;
		}
		if ($banuseruid) {
			$sql .= " AND m.uid=".S::sqlEscape($banuseruid);
			$count = 1;
		}
		if ($bantype) {
			$sql .= " AND b.type=".S::sqlEscape($bantype);
			$url .= "&bantype=$bantype";
		}
		if ($adminban) {
			$sql .= " AND b.admin=".S::sqlEscape($adminban);
			$url .= "&adminban=".rawurlencode($adminban);
		}
		if ($starttime) {
			!is_numeric($starttime) && $starttime = PwStrtoTime($starttime);
			$sql .= " AND b.startdate>".S::sqlEscape($starttime);
			$url .= "&starttime=$starttime";
		}
		if ($endtime) {
			!is_numeric($endtime) && $endtime = PwStrtoTime($endtime);
			$sql .= " AND b.startdate<".S::sqlEscape($endtime);
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
			$db->update("DELETE FROM pw_banuser WHERE id IN(".S::sqlImplode($ids).")");
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userService->updates($uids1, array('groupid' => -1));
			/**
			$uids2 && $db->update("UPDATE pw_members m LEFT JOIN pw_banuser b ON m.uid=b.uid AND b.fid>0 SET m.userstatus=m.userstatus&(~1) WHERE b.uid is NULL AND m.uid IN(".S::sqlImplode($uids2).")");
			**/
			$uids2 && $db->update(pwQuery::buildClause("UPDATE :pw_table m LEFT JOIN pw_banuser b ON m.uid=b.uid AND b.fid>0 SET m.userstatus=m.userstatus&(~1) WHERE b.uid is NULL AND m.uid IN(:uid)", array('pw_members', $uids2)));
		}
		include PrintEot('banuser');exit;
	} elseif ($action == 'freeban') {
		S::gp(array('free'),'P');
		!$free && adminmsg('operate_error', $basename . '&adminitem=viewban');
		$ids = S::sqlImplode($free);
		$uids1 = $uids2 = array();
		//* $_cache = getDatastore();
		$userNames = array();
		$query = $db->query("SELECT b.*,m.username FROM pw_banuser b LEFT JOIN pw_members m ON b.uid=m.uid WHERE b.uid IN ($ids)");
		while ($rt = $db->fetch_array($query)) {
			$userNames[] = $rt['username'];
			//* $_cache->delete('UID_'.$rt['uid']);
			if ($rt['fid']) {
				$uids2[] = $rt['uid'];
			} else {
				$uids1[] = $rt['uid'];
			}
		}
		//* $db->update("DELETE FROM pw_banuser WHERE uid IN($ids)");
		pwQuery::delete('pw_banuser', 'uid IN (:uid)', array($free));
		
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->updates($uids1, array('groupid'=>-1));
		/**
		$uids2 && $db->update("UPDATE pw_members m LEFT JOIN pw_banuser b ON m.uid=b.uid AND b.fid>0 SET m.userstatus=m.userstatus&(~1) WHERE b.uid is NULL AND m.uid IN(".S::sqlImplode($uids2).")");
	    **/
		$uids2 && $db->update(pwQuery::buildClause("UPDATE :pw_table m LEFT JOIN pw_banuser b ON m.uid=b.uid AND b.fid>0 SET m.userstatus=m.userstatus&(~1) WHERE b.uid is NULL AND m.uid IN(:uid)", array('pw_members', $uids2)));
		M::sendNotice(
			$userNames,
			array(
				'title' => getLangInfo('writemsg','banuser_free_title'),
				'content' => getLangInfo('writemsg','banuser_free_content',array(
					'manager'	=> $admin_name,
				)),
			)
		);
		adminmsg('operate_success', $basename . '&adminitem=viewban');
	}
}
?>