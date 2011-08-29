<?php
!function_exists('adminmsg') && exit('Forbidden');
//require_once D_P.'data/bbscache/forum_cache.php';
S::gp(array('adminitem'));
//* require_once pwCache::getPath(D_P.'data/bbscache/level.php');
pwCache::getData(D_P.'data/bbscache/level.php');
$ltitle['-1'] = getLangInfo('all','reg_member');
$basename = "$admin_file?adminjob=userstats&adminitem=$adminitem";
empty($adminitem) && $adminitem = 'userstats';
if ($adminitem == 'userstats'){
	if (empty($_POST['action'])) {
		$groupnum = array();
		$query = $db->query("SELECT COUNT(*) AS count,groupid FROM pw_members  GROUP BY groupid");
		$s_sum = 0;
		while ($group = $db->fetch_array($query)) {
			$s_sum += $group['count'];
			$groupnum[] = array($group['count'],$group['groupid'],$ltitle[$group['groupid']]);
		}
		include PrintEot('userstats');exit;
	}
}elseif ($adminitem =='editgroup'){
	if (!$action) {
		$groupselect = '';
		$query = $db->query("SELECT gid,grouptitle FROM pw_usergroups WHERE gptype IN ('system','special','default') AND gid NOT IN (1,2,5)");
		while ($group = $db->fetch_array($query)){
			$groupselect .= "<option value='$group[gid]'>$group[grouptitle]</option>";
		}
		include PrintEot('userstats');exit;
	
	} elseif ($_POST['action'] == 'add') {
	
		S::gp(array('members'),'P');
		S::gp(array('gid'),'P',2);
		!$members && adminmsg('operate_fail');
		if ($gid == 3 && !If_manager) {
			adminmsg('manager_right');
		} elseif ($gid == 4 && !If_manager && $admin_gid != 3) {
			adminmsg('chiefadmin_right');
		} elseif ($gid == 5) {
			adminmsg('setuser_forumadmin');
		}
		
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		
		$groups = explode(",",$members);
		$groups = array_unique($groups);
		$uids = $memberdb = array();
		foreach ($groups as $value) {
			if ($value) {
				$member = $userService->getByUserName($value);
				if (!$member['uid']) {
					$errorname = $value;
					adminmsg('user_not_exists');
				} elseif ($member['groupid'] != '-1') {
					adminmsg('member_only');
				}
				$uids[] = $member['uid'];
				$memberdb[] = $member;
			}
		}
		!$uids && adminmsg('operate_fail');
	
		$gids  = array();
		$query = $db->query("SELECT gid FROM pw_usergroups WHERE gptype IN ('system','special','default') AND gid NOT IN (1,2,5)");
		while ($rt = $db->fetch_array($query)) {
			$gids[] = $rt['gid'];
		}
		if (in_array($gid,$gids)) {
			foreach ($memberdb as $member) {
				admincheck($member['uid'],$member['username'],$gid,$member['groups'],'update');
			}
		}
		$uids && $userService->updates($uids, array('groupid'=>$gid));
		adminmsg('operate_success');
	}
}
?>