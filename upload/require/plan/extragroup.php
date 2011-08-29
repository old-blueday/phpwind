<?php
!function_exists('readover') && exit('Forbidden');

$updatecache_fd = 0;
$sqladd = array();
$expiredUsers = array();
$query = $db->query("SELECT eg.uid,eg.gid,eg.togid,m.groupid,m.groups,m.username FROM pw_extragroups eg LEFT JOIN pw_members m USING(uid) WHERE eg.startdate+eg.days*86400<".S::sqlEscape($timestamp,false)."LIMIT 100");

$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */

while ($rt = $db->fetch_array($query)) {
	if (!isset($expiredUsers[$rt['uid']])) {
		$expiredUsers[$rt['uid']] = true;
	} else {
		//一个用户同时有多个任务，需要刷新用户数据
		$refreshUser = $userService->get($rt['uid']);
		$rt['groupid'] = $refreshUser['groupid'];
		$rt['groups'] = $refreshUser['groups'];
		$rt['username'] = $refreshUser['username'];
	}

	if ($rt['gid']==$rt['groupid']) {
		$newgid=($rt['togid'] && strpos($rt['groups'],",$rt[togid],")!==false) ? $rt['togid'] : '-1';
		$newgroups=str_replace(','.$newgid.',',',',$rt['groups']);
	} else {
		$newgid=$rt['groupid'];
		$newgroups=str_replace(','.$rt['gid'].',',',',$rt['groups']);
	}
	if ($rt['gid']=='5') {
		$query1 = $db->query("SELECT fid,forumadmin FROM pw_forums WHERE forumadmin!=''");
		while ($fm = $db->fetch_array($query1)) {
			if ($fm['forumadmin'] && strpos($fm['forumadmin'],",$rt[username],")!==false) {
				$newadmin = str_replace(",$rt[username],",',',$fm['forumadmin']);
				$newadmin == ',' && $newadmin = '';
				//$db->update("UPDATE pw_forums SET forumadmin=".S::sqlEscape($newadmin,false)."WHERE fid=".S::sqlEscape($fm['fid'],false));
				pwQuery::update('pw_forums', 'fid=:fid', array($fm['fid']), array('forumadmin' => $newadmin));
				$updatecache_fd=1;
			}
		}
	}
	$newgroups==',' && $newgroups='';
	if (in_array($newgid,array('-1','6','7')) && $newgroups=='') {
		$db->update("DELETE FROM pw_administrators WHERE uid=".S::sqlEscape($rt['uid'],false));
	} else {
		$sqladd[] = array($rt['uid'],$rt['username'],$newgid,$newgroups);
	}
	$userService->update($rt['uid'], array('groupid'=>$newgid,'groups'=>$newgroups));
	$db->update("DELETE FROM pw_extragroups WHERE uid=".S::sqlEscape($rt['uid'],false)."AND gid=".S::sqlEscape($rt['gid'],false));
}
if ($sqladd) {
	$db->update("REPLACE INTO pw_administrators (uid,username,groupid,groups) VALUES ".S::sqlMulti($sqladd,false));
}
if ($updatecache_fd) {
	$havechild = array();
	$db->update("UPDATE pw_forums SET childid='0',fupadmin=''");
	$query = $db->query("SELECT fid,forumadmin FROM pw_forums WHERE type='category' ORDER BY vieworder");
	while ($cate = $db->fetch_array($query)) {
		$query2 = $db->query("SELECT fid,forumadmin FROM pw_forums WHERE type='forum' AND fup=".S::sqlEscape($cate['fid'],false));
		if ($db->num_rows($query2)) {
			$havechild[] = $cate['fid'];
			while ($forum = $db->fetch_array($query2)){
				$fupadmin = trim($cate['forumadmin']);
				if ($fupadmin) {
					//$db->update("UPDATE pw_forums SET fupadmin=".S::sqlEscape($fupadmin,false)."WHERE fid=".S::sqlEscape($forum['fid'],false));
					pwQuery::update('pw_forums', 'fid=:fid', array($forum['fid']), array('fupadmin' => $fupadmin));
				}
				if (trim($forum['forumadmin'])) {
					$fupadmin .= $fupadmin ? substr($forum['forumadmin'],1) : $forum['forumadmin']; //is
				}
				$query3 = $db->query("SELECT fid,forumadmin FROM pw_forums WHERE type='sub' AND fup=".S::sqlEscape($forum['fid'],false));
				if ($db->num_rows($query3)) {
					$havechild[] = $forum['fid'];
					while ($sub1 = $db->fetch_array($query3)){
						$fupadmin1=$fupadmin;
						if ($fupadmin1) {
							//$db->update("UPDATE pw_forums SET fupadmin=".S::sqlEscape($fupadmin1,false)."WHERE fid=".S::sqlEscape($sub1['fid'],false));
							pwQuery::update('pw_forums', 'fid=:fid', array($sub1['fid']), array('fupadmin' => $fupadmin1));
						}
						if (trim($sub1['forumadmin'])) {
							$fupadmin1 .= $fupadmin1 ? substr($sub1['forumadmin'],1) : $sub1['forumadmin'];
						}
						$query4 = $db->query("SELECT fid,forumadmin FROM pw_forums WHERE type='sub' AND fup=".S::sqlEscape($sub1['fid'],false));
						if ($db->num_rows($query4)) {
							$havechild[] = $sub1['fid'];
							while ($sub2 = $db->fetch_array($query4)){
								$fupadmin2 = $fupadmin1;
								if ($fupadmin2) {
									//$db->update("UPDATE pw_forums SET fupadmin=".S::sqlEscape($fupadmin2,false)."WHERE fid=".S::sqlEscape($sub2['fid'],false));
								    pwQuery::update('pw_forums', 'fid=:fid', array($sub2['fid']), array('fupadmin' => $fupadmin2));
								}
							}
						}
					}
				}
			}
		}
	}
	if ($havechild) {
		/*
		$havechilds = S::sqlImplode($havechild,false);
		$db->update("UPDATE pw_forums SET childid='1' WHERE fid IN($havechilds)");
		*/
		pwQuery::update('pw_forums', 'fid IN(:fid)', array($havechild), array('childid' => '1'));
	}
}
?>