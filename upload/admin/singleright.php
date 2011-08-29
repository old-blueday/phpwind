<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=singleright";

if (empty($action)) {
	if(empty($job)){
		S::gp(array('page','username'));
		$sql = '';
		(!is_numeric($page) || $page<1) && $page = 1;
		$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);

		if($username){
			$sql   = " WHERE m.username=".S::sqlEscape($username);
			$count = 1;
		} else{
			@extract($db->get_one("SELECT COUNT(*) AS count FROM pw_singleright"));
		}
		$pages = numofpage($count,$page,ceil($count/$db_perpage),"$basename&");

		$query = $db->query("SELECT m.uid,m.username,m.groupid,m.memberid FROM pw_singleright sr LEFT JOIN pw_members m USING(uid) $sql $limit");
		$memberdb = array();
		while($rt = $db->fetch_array($query)){
			$rt['level'] = $rt['groupid']=='-1' ? $ltitle[$rt['memberid']] : $ltitle[$rt['groupid']];
			$memberdb[]  = $rt;
		}

		include PrintEot('singleright');exit;

	} elseif($job == 'setright') {
		S::gp(array('uid','username'));
		$username = trim($username);
		$uid = (int) $uid;
		(!$username && !$uid) && adminmsg('用户名不能为空');
		if(!$_POST['step']){
			if ($username) {
				$men = $db->get_one("SELECT m.uid,sr.uid as ifset FROM pw_members m LEFT JOIN pw_singleright sr USING(uid) WHERE m.username=".S::sqlEscape($username));
				if(!$men){
					$errorname = $username;
					adminmsg('user_not_exists');
				}
				$uid = $men['uid'];
			}

			//* include_once pwCache::getPath(D_P.'data/bbscache/forumcache.php');
			pwCache::getData(D_P.'data/bbscache/forumcache.php');
			list($hidefid,$hideforum) = GetHiddenForum();
			$forumcache .= $hideforum;
			$forumcache  = "<option></option>".$forumcache;
			$forum_visit = $forum_post = $forum_reply = $forumcache;

			$rt = $db->get_one("SELECT sr.*,m.username FROM pw_singleright sr LEFT JOIN pw_members m USING(uid) WHERE sr.uid=".S::sqlEscape($uid));

			if ($rt) {
				$visit = explode(',',$rt['visit']);
				$post  = explode(',',$rt['post']);
				$reply = explode(',',$rt['reply']);

				foreach($visit as $key=>$value){
					$forum_visit = str_replace("<option value=\"$value\">","<option value=\"$value\" selected>",$forum_visit);
				}
				foreach($post as $key=>$value){
					$forum_post  = str_replace("<option value=\"$value\">","<option value=\"$value\" selected>",$forum_post);
				}
				foreach($reply as $key=>$value){
					$forum_reply = str_replace("<option value=\"$value\">","<option value=\"$value\" selected>",$forum_reply);
				}
				$username = $rt['username'];
			}
			include PrintEot('singleright');exit;
		} else {
			S::gp(array('visit','post','reply'),'P');
			$tmpArray = array();
			foreach ($visit as $key => $value) {
				if (is_numeric($value)) {
					$tmpArray[] = $value;
				}
			}
			$visit = implode(',',$tmpArray);
			$tmpArray = array();
			foreach ($post as $key => $value) {
				if (is_numeric($value)) {
					$tmpArray[] = $value;
				}
			}
			$post = implode(',',$tmpArray);
			$tmpArray = array();
			foreach ($reply as $key => $value) {
				if (is_numeric($value)) {
					$tmpArray[] = $value;
				}
			}
			$reply = implode(',',$tmpArray);
			/**
			$db->pw_update(
				"SELECT * FROM pw_singleright WHERE uid=" . S::sqlEscape($uid),
				"UPDATE pw_singleright SET" . S::sqlSingle(array(
						'visit'	=> $visit,
						'post'	=> $post,
						'reply'	=> $reply
					))
				. " WHERE uid=" . S::sqlEscape($uid),
				"INSERT INTO pw_singleright SET" . S::sqlSingle(array(
					'uid'	=> $uid,
					'visit'	=> $visit,
					'post'	=> $post,
					'reply'	=> $reply
				))
			);
			**/
			$db->pw_update(
				"SELECT * FROM pw_singleright WHERE uid=" . S::sqlEscape($uid),
				pwQuery::updateClause('pw_singleright', 'uid =:uid', array($uid), array('visit'=> $visit,'post'=> $post,'reply'=> $reply)),
				pwQuery::insertClause('pw_singleright', array('uid'=> $uid,'visit'=> $visit,'post'=> $post,'reply'=> $reply))
			);			

			adminmsg('operate_success');
		}
	} elseif($_POST['job'] == 'del') {
		S::gp(array('selid'),'P');
		$tmpSelid = $selid;
		if(!$selid = checkselid($selid)){
			adminmsg('operate_error');
		}
		//* $db->update("DELETE FROM pw_singleright WHERE uid IN($selid)");
		pwQuery::delete('pw_singleright', 'uid IN (:uid)', array($tmpSelid));
		
		adminmsg('operate_success');
	}
} elseif ($action == 'user') {
	//* include_once pwCache::getPath(D_P.'data/bbscache/level.php');
	//* include_once pwCache::getPath(D_P.'data/bbscache/forumcache.php');
	pwCache::getData(D_P.'data/bbscache/level.php');
	pwCache::getData(D_P.'data/bbscache/forumcache.php');
	
	S::gp(array('page','uid','fid','gid'),GP,2);
	$username = S::escapeChar(S::getGP('username'));
	$groupcache = $pageurl = '';$u_d = array();

	list($hidefid,$hideforum) = GetHiddenForum();
	$forumcache  = "<option></option>".$forumcache.$hideforum;

	$groupcache = "<option></option>";
	$query = $db->query("SELECT gid,grouptitle FROM pw_usergroups WHERE gptype IN('system','special')");
	while ($rs = $db->fetch_array($query)) {
		$groupcache .= "<option value=\"$rs[gid]\">$rs[grouptitle]</option>";
	}
	$sql = "p.gid='0'";
	if ($uid) {
		$sql .= ' AND p.uid='.S::sqlEscape($uid);
		$pageurl .= "uid=$uid&";
	} elseif ($username) {
		$sql .= ' AND m.username='.S::sqlEscape($username);
		$pageurl .= "username=$username&";
	} elseif ($gid) {
		$sql .= ' AND p.uid>0 AND m.groupid='.S::sqlEscape($gid);
		$groupcache = str_replace("<option value=\"$gid\">","<option value=\"$gid\" selected>",$groupcache);
		$pageurl .= "gid=$gid&";
	} else {
		$sql .= ' AND p.uid>0';
	}
	if ($fid) {
		$sql .= ' AND p.fid='.S::sqlEscape($fid);
		$forumcache = str_replace("<option value=\"$fid\">","<option value=\"$fid\" selected>",$forumcache);
		$pageurl .= "fid=$fid&";
	} else {
		$sql .= ' AND p.fid>0';
	}

	if ($db->server_info() > '4.1') {
		$count = $db->get_value("SELECT COUNT(*) as count FROM (SELECT p.uid,p.fid,m.username,m.groupid FROM pw_permission p LEFT JOIN pw_members m ON p.uid=m.uid WHERE $sql GROUP BY p.uid,p.fid) as temp");
	} else {
		$db->query("CREATE TEMPORARY TABLE tmp_singleright (SELECT p.uid,p.fid,m.username,m.groupid FROM pw_permission p LEFT JOIN pw_members m ON p.uid=m.uid WHERE $sql GROUP BY p.uid,p.fid)");
		$count = $db->get_value("SELECT COUNT(*) AS count FROM tmp_singleright");
	}
	(!is_numeric($page) || $page<1) && $page = 1;
	$pages = numofpage($count,$page,ceil($count/$db_perpage),"$basename&action=$action&$pageurl");
	$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);

	$query = $db->query("SELECT p.uid,p.fid,m.username,m.groupid FROM pw_permission p LEFT JOIN pw_members m ON p.uid=m.uid WHERE $sql GROUP BY p.uid,p.fid $limit");
	while ($rd = $db->fetch_array($query)) {
		$u_d[] = $rd;
	}
	if ($uid || $username) {
		$username = $u_d[0]['username'];
		$uid = $u_d[0]['uid'];
	}
	$jschk = ($uid || $username || $fid || $gid) && $pages ? 'true' : 'false';
	include PrintEot('singleright');exit;
} elseif ($action == 'group') {
	//* include_once pwCache::getPath(D_P.'data/bbscache/level.php');
	//* include_once pwCache::getPath(D_P.'data/bbscache/forumcache.php');
	pwCache::getData(D_P.'data/bbscache/level.php');
	pwCache::getData(D_P.'data/bbscache/forumcache.php');	

	S::gp(array('page','fid','gid'),GP,2);
	$groupcache = $pageurl = $sql = '';$g_d = array();

	list($hidefid,$hideforum) = GetHiddenForum();
	$forumcache  = $forumcache.$hideforum;

	$query = $db->query("SELECT gid,grouptitle FROM pw_usergroups WHERE gptype IN('system','special')");
	while ($rs = $db->fetch_array($query)) {
		$groupcache .= "<option value=\"$rs[gid]\">$rs[grouptitle]</option>";
	}
	$sql = "uid='0'";
	if ($fid) {
		$sql .= ' AND fid='.S::sqlEscape($fid);
		$forumcache = str_replace("<option value=\"$fid\">","<option value=\"$fid\" selected>",$forumcache);
		$pageurl .= "fid=$fid&";
	} else {
		$sql .= " AND fid>'0'";
	}
	if ($gid) {
		$sql .= ' AND gid='.S::sqlEscape($gid);
		$groupcache = str_replace("<option value=\"$gid\">","<option value=\"$gid\" selected>",$groupcache);
		$pageurl .= "gid=$gid&";
	} else {
		$sql .= " AND gid>'0'";
	}

	if ($db->server_info() > '4.1') {
		$count = $db->get_value("SELECT COUNT(*) as count FROM (SELECT fid,gid FROM pw_permission WHERE $sql GROUP BY fid,gid) as temp");
	} else {
		$db->query("CREATE TEMPORARY TABLE temp (SELECT fid,gid FROM pw_permission WHERE $sql GROUP BY fid,gid)");
		$count = $db->get_value("SELECT COUNT(*) AS count FROM temp");
	}
	(!is_numeric($page) || $page<1) && $page = 1;
	$pages = numofpage($count,$page,ceil($count/$db_perpage),"$basename&action=$action&$pageurl");
	$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);

	$query = $db->query("SELECT fid,gid FROM pw_permission WHERE $sql GROUP BY fid,gid $limit");
	while ($rd = $db->fetch_array($query)) {
		$g_d[] = $rd;
	}

	$jschk = ($fid || $gid) && $pages ? 'true' : 'false';
	include PrintEot('singleright');exit;
} elseif ($action == 'setright') {//单用户权限设置

	S::gp(array('uid','gid','fid'),'GP',2);
	$pwuser = S::escapeChar(S::getGP('pwuser'));
	$jumpurl = "$basename&action=$job";

	$f = $db->get_one("SELECT name,type FROM pw_forums WHERE fid=".S::sqlEscape($fid));
	empty($f) && adminmsg('undefined_action',$jumpurl);

	//* include_once pwCache::getPath(D_P.'data/bbscache/forumcache.php');
	pwCache::getData(D_P.'data/bbscache/forumcache.php');
	list($hidefid,$hideforum) = GetHiddenForum();
	$forumcache .= $hideforum;

	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	
	if (empty($_POST['step'])) {
		if ($job == 'user') {
			if ($pwuser) {//add
				$rt = $userService->getByUserName($pwuser);
				if (empty($rt)) {
					$errorname = $pwuser;
					adminmsg('user_not_exists',$jumpurl);
				}
				if ($rt['groupid'] == '-1') {
					adminmsg('user_not_right',$jumpurl);
				}
			} else {//edit
				$rt = $userService->get($uid);
				empty($rt) && adminmsg('data_error',$jumpurl);
			}
			$uid		= $rt['uid'];
			$gid		= $rt['groupid'];
			$username	= $rt['username'];
			$grouptitle = $ltitle[$gid];
			$othersel	= $pwuser ? $forumcache : getothersel($uid);
			$sql = "SELECT rkey,rvalue FROM pw_permission WHERE (uid=" . S::sqlEscape($uid) . ' AND fid=' . S::sqlEscape($fid) . "AND gid='0' OR uid='0' AND fid=" . S::sqlEscape($fid) . " AND gid=" . S::sqlEscape($gid) . " OR uid='0' AND fid='0' AND gid=" . S::sqlEscape($gid) . ") AND type='systemforum' ORDER BY uid,fid";
		} else {//group
			$grouptitle = $db->get_value("SELECT grouptitle FROM pw_usergroups WHERE gid=" . S::sqlEscape($gid));
			empty($grouptitle) && adminmsg('operate_error',$jumpurl);
			$othersel = getothersel($gid,'G');
			$sql = "SELECT rkey,rvalue FROM pw_permission WHERE uid='0' AND (fid=".S::sqlEscape($fid)." AND gid=" . S::sqlEscape($gid) . " OR fid='0' AND gid=" . S::sqlEscape($gid) . ") AND type='systemforum' ORDER BY fid";
		}
		$query = $db->query($sql);

		while ($rs = $db->fetch_array($query)) {
			switch ($rs['rkey']) {
				case 'banuser':
				case 'topped':
					${$rs['rkey'].'_0'} = ${$rs['rkey'].'_1'} = ${$rs['rkey'].'_2'} = ${$rs['rkey'].'_3'} = '';
					${$rs['rkey'].'_'.$rs['rvalue']} = 'checked';break;
				case 'banmax':
					$$rs['rkey'] = $rs['rvalue'];break;
				default:
					ifcheck($rs['rvalue'],$rs['rkey']);
			}
		}
		require GetLang('right');
		include PrintEot('singleright');exit;

	} else {

		S::gp(array('group','othergroup','otherfid'));
		if ($uid) {//user
			$rt = $userService->get($uid);
			empty($rt) && adminmsg('data_error',$jumpurl);
			$gid = 0;
		} else {//group
			$rt = $db->get_one("SELECT gid,grouptitle FROM pw_usergroups WHERE gid=" . S::sqlEscape($gid));
			empty($rt) && adminmsg('operate_error',$jumpurl);
			$uid = 0;
		}

		require GetLang('right');
		is_array($othergroup) || $othergroup = array();
		is_array($otherfid) || $otherfid = array();
		$pwSQL = array();
		$otherfid = array_diff($otherfid,array($fid));

		foreach ($group as $key => $value) {
			if (isset($lang['right']['systemforum'][$key])) {
				$pwSQL[] = array($uid, $fid, $gid, $key, 'systemforum', $value);
				if (in_array($key,$othergroup) && $otherfid) {
					foreach ($otherfid as $k => $v) {
						$pwSQL[] = array($uid, $v, $gid, $key, 'systemforum', $value);
					}
				}
			}
		}
		if ($pwSQL) {
			$db->update("REPLACE INTO pw_permission (uid,fid,gid,rkey,type,rvalue) VALUES " . S::sqlMulti($pwSQL));
		}

		adminmsg('operate_success',"$basename&action=setright&job=$job&fid=$fid&uid=$uid&gid=$gid");
	}
} elseif ($action == 'batchright') {//批量用户权限设置
	$jumpurl = $job=='user' ? "$basename&action=user" : "$basename&action=group";
	S::gp(array('selid','all','ids'));
	list($suid,$sfid,$sgid) = explode('_',$ids);
	$suid = (int)$suid;
	$sfid = (int)$sfid;
	$sgid = (int)$sgid;
	$all && $all = $suid || $sfid || $sgid;
	if (empty($_POST['step'])) {
		if (empty($all)) {
			$pwSQL = $u_d = array();
			empty($selid) && adminmsg('operate_error',$jumpurl);
			settype($selid,'array');
			foreach ($selid as $key=>$value) {
				list($uid,$fid,$gid) = explode('_',$value);
				$uid = (int)$uid;
				$fid = (int)$fid;
				$gid = (int)$gid;
				if ($job == 'user' && $fid && $uid) {
					$pwSQL[] = 'p.uid='.S::sqlEscape($uid).'AND p.fid='.S::sqlEscape($fid).'AND p.gid=0';
				} elseif ($job == 'group' && $fid && $gid) {
					$g_d[] = array('fid'=>$fid,'gid'=>$gid);
				}
			}
			if ($pwSQL) {
				$pwSQL = implode(' OR ',$pwSQL);
				if ($job == 'user') {
					$query = $db->query("SELECT p.uid,p.fid,m.username,m.groupid FROM pw_permission p LEFT JOIN pw_members m ON p.uid=m.uid WHERE $pwSQL GROUP BY p.uid,p.fid");
					while ($rd = $db->fetch_array($query)) {
						$u_d[] = $rd;
					}
				}
			}
		}
		$sql = '';
		if ($suid) {
			$rt = $userService->get($suid);
			if ($rt) {
				$username = $rt['username'];
				if ($sfid) {
					$sql = "SELECT rkey,rvalue FROM pw_permission WHERE uid=".S::sqlEscape($suid)."AND fid=".S::sqlEscape($sfid)."AND gid='0' AND type='systemforum'";
				} else {
					$sql = "SELECT rkey,rvalue FROM pw_permission WHERE uid='0' AND fid='0' AND gid=".S::sqlEscape($rt['groupid'],false)."AND type='systemforum'";
				}
			}
		} elseif ($sgid) {
			$sql = "SELECT rkey,rvalue FROM pw_permission WHERE uid='0' AND fid=".S::sqlEscape($sfid)." AND gid=".S::sqlEscape($sgid)."AND type='systemforum'";
		}
		if ($sql) {
			$query = $db->query($sql);
			while ($rs = $db->fetch_array($query)) {
				switch ($rs['rkey']) {
					case 'banuser':
					case 'topped':
						${$rs['rkey'].'_0'} = ${$rs['rkey'].'_1'} = ${$rs['rkey'].'_2'} = ${$rs['rkey'].'_3'} = '';
						${$rs['rkey'].'_'.$rs['rvalue']} = 'checked';break;
					case 'banmax':
						$$rs['rkey'] = $rs['rvalue'];break;
					default:
						ifcheck($rs['rvalue'],$rs['rkey']);
				}
			}
		}

		require GetLang('right');
		include PrintEot('singleright');exit;
	} elseif ($_POST['step'] == 2) {
		$pwSQL = $sel = array();
		if (empty($all)) {
			S::gp(array('group','othergroup'));
			empty($selid) && adminmsg('operate_error',$jumpurl);
			settype($selid,'array');
			foreach ($selid as $key=>$value) {
				list($uid,$fid,$gid) = explode('_',$value);
				$uid = (int)$uid;
				$fid = (int)$fid;
				$gid = (int)$gid;
				if ($job == 'user' && $fid && $uid) {
					$sel[] = array('uid'=>$uid,'fid'=>$fid,'gid'=>0);
				} elseif ($job == 'group' && $fid && $gid) {
					$sel[] = array('uid'=>'0','fid'=>$fid,'gid'=>$gid);
				}
			}
		} else {
			$sql = '';
			if ($job == 'user') {
				$where = "p.gid='0'";
				if ($suid) {
					$where .= ' AND p.uid='.S::sqlEscape($suid);
				} elseif ($sgid) {
					$where .= ' AND p.uid>0 AND m.groupid='.S::sqlEscape($sgid);
				} else {
					$where .= ' AND p.uid>0';
				}
				if ($fid) {
					$where .= ' AND p.fid='.S::sqlEscape($sfid);
				} else {
					$where .= ' AND p.fid>0';
				}
				$sql = "SELECT p.uid,p.fid,p.gid FROM pw_permission p LEFT JOIN pw_members m ON p.uid=m.uid WHERE $sql GROUP BY p.uid,p.fid";
			} elseif ($job == 'group') {
				$where = "uid='0'";
				if ($fid) {
					$where .= ' AND fid='.S::sqlEscape($fid);
				} else {
					$where .= " AND fid>'0'";
				}
				if ($gid) {
					$where .= ' AND gid='.S::sqlEscape($gid);
				} else {
					$where .= " AND gid>'0'";
				}
				$sql = "SELECT uid,fid,gid FROM pw_permission WHERE $sql GROUP BY fid,gid";
			}
			if ($sql) {
				$query = $db->query($sql);
				while ($rt = $db->fetch_array($query)) {
					$sel[] = array('uid'=>$rt['uid'],'fid'=>$rt['fid'],'gid'=>$rt['gid']);
				}
			}

		}
		require GetLang('right');
		is_array($othergroup) || $othergroup = array();
		foreach ($othergroup as $value) {
			if (isset($lang['right']['systemforum'][$value])) {
				foreach ($sel as $k=>$v) {
					$pwSQL[] = array($v['uid'], $v['fid'], $v['gid'], $value, 'systemforum', $group[$value]);
				}
			}
		}
		if ($pwSQL) {
			$db->update("REPLACE INTO pw_permission (uid,fid,gid,rkey,type,rvalue) VALUES " . S::sqlMulti($pwSQL));
		}
		adminmsg('operate_success',$jumpurl);
	}
} elseif ($action == 'delright') {

	$jumpurl = $job=='user' ? "$basename&action=user" : "$basename&action=group";
	S::gp(array('selid','all'));

	if (empty($all)) {
		$pwSQL = array();
		empty($selid) && adminmsg('operate_error',$jumpurl);
		settype($selid,'array');
		foreach ($selid as $key=>$value) {
			list($uid,$fid,$gid) = explode('_',$value);
			$uid = (int)$uid;
			$fid = (int)$fid;
			$gid = (int)$gid;
			if ($job == 'user') {
				if ($fid && $uid) {
					$pwSQL[] = 'uid='.S::sqlEscape($uid).'AND fid='.S::sqlEscape($fid).'AND gid=0';
				}
			} elseif ($job=='group') {
				if ($fid && $gid) {
					$pwSQL[] = 'uid=0 AND fid='.S::sqlEscape($fid).'AND gid='.S::sqlEscape($gid);
				}
			}
		}
		$pwSQL && $pwSQL = implode(' OR ',$pwSQL);
	} else {
		$pwSQL = '';
		list($uid,$fid,$gid) = explode('_',$all);
		$uid = (int)$uid;
		$fid = (int)$fid;
		$gid = (int)$gid;
		if ($job == 'user') {
			if ($uid) {
				$pwSQL = 'uid='.S::sqlEscape($uid);
			} elseif ($gid) {
				$uids = array();
				$sql = 'p.uid>0'.($fid ? ' AND p.fid='.S::sqlEscape($fid) : ' AND p.fid>0').' AND p.gid=0 AND m.groupid='.S::sqlEscape($gid);
				$query = $db->query("SELECT p.uid FROM pw_permission p LEFT JOIN pw_members m ON p.uid=m.uid WHERE $sql GROUP BY p.uid,p.fid");
				while ($rd = $db->fetch_array($query)) {
					$uids[] = $rd['uid'];
				}
				if ($uids) {
					$pwSQL = 'uid IN ('.S::sqlImplode($uids).')';
				} else {
					adminmsg('operate_error',$jumpurl);
				}
			} else {
				$pwSQL = 'uid>0 ';
			}
			$pwSQL .= ($fid ? ' AND fid='.S::sqlEscape($fid) : ' AND fid>0 ') . ' AND gid=0';
		} elseif ($job=='group') {
			if ($fid && $gid) {
				$pwSQL[] = 'uid=0 AND fid='.S::sqlEscape($fid).'AND gid='.S::sqlEscape($gid);
			}
		}
	}
	if ($pwSQL) {
		$db->update("DELETE FROM pw_permission WHERE $pwSQL");
	}

	adminmsg('operate_success',$jumpurl);
}
function getothersel($id,$t = 'U') {
	global $fid,$db,$forum,$forumcache;
	if ($t == 'U') {
		$sql = 'uid=' . S::sqlEscape($id) . " AND fid>'0' AND gid='0'";
	} else {
		$sql = "uid='0' AND fid>'0' AND gid=" . S::sqlEscape($id);
	}
	$g_fid = array($fid);
	$ghtml = $forumcache;
	$query = $db->query("SELECT fid FROM pw_permission WHERE $sql GROUP BY fid");
	while ($rt = $db->fetch_array($query)) {
		$g_fid[] = $rt['fid'];
	}
	$ghtml = preg_replace("/\<option value\=\"(\d+)\"\>(.+?)\<\/option\>\r?\n?/eis","f_ret('\\1','\\2',\$g_fid)",$ghtml);
	$ghtml = str_replace("<option value=\"$fid\">","<option value=\"$fid\" selected>",$ghtml);
	return $ghtml;
}
function f_ret($fid,$fname,$f_a) {
	if (in_array($fid,$f_a)) {
		return "<option value=\"$fid\">$fname</option>\r\n";
	} else {
		return '';
	}
}
?>