<?php
!function_exists('readover') && exit('Forbidden');
$wind_in = 'medal';
//* include_once pwCache::getPath(D_P.'data/bbscache/md_config.php');
//* include_once pwCache::getPath(D_P.'data/bbscache/medaldb.php');
pwCache::getData(D_P.'data/bbscache/md_config.php');
pwCache::getData(D_P.'data/bbscache/medaldb.php');
include_once(R_P.'require/showimg.php');
!$md_ifopen && Showmsg('medal_close');

//* $_cache = getDatastore();
$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */

$userdb = $userService->get($winduid); //medals,icon
if ($userdb['medals']) {
	$userdb['medals'] = explode(',',$userdb['medals']);
} else{
	$userdb['medals'] = '';
}
$userface = showfacedesign($userdb['icon'],'','m');
S::gp(array('action'));

if (!$action) {

	if ($userdb['medals']) {
		$ifunset = 0;
		foreach ($userdb['medals'] as $key => $val) {
			if (!array_key_exists($val,$_MEDALDB)) {
				unset($userdb['medals'][$key]);
				$ifunset = 1;
			}
		}
		if ($ifunset) {
			$newmedals = implode(',',$userdb['medals']);
			$userService->update($winduid, array('medals'=>$newmedals));
			//* $_cache->delete('UID_'.$winduid);
			!$newmedals && updatemedal_list();
		}
	}
	require_once PrintHack('index');footer();

} elseif ($action == 'list') {

	$groupid == 'guest' && Showmsg('not_login');
	if (!file_exists(D_P.'data/bbscache/medals_list.php')) {
		updatemedal_list();
	}
	//* $uids = substr(readover(D_P.'data/bbscache/medals_list.php'),12);
	$uids = substr(pwCache::getData(D_P.'data/bbscache/medals_list.php', false, true),12);
	if ($uids) {
		S::gp(array('page'));
		(!is_numeric($page) || $page < 1) && $page = 1;
		$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
		$uids = explode(",",$uids);
		$sum = count($uids);
		$pages = numofpage($sum,$page,ceil($sum/$db_perpage),"$basename&action=list&");
		$listdb=array();
		$userIds = array_slice($uids,($page-1)*$db_perpage,$db_perpage);
		unset($userIds[0]);
		$members = $userService->getByUserIds($userIds); //uid,username,medals
		foreach ($members as $rt) {
			$medals = '';
			$md_a = explode(',',$rt['medals']);
			foreach ($md_a as $key => $value) {
				if ($value) {
					if (strpos($md_groups,",$groupid,") !== false) {
						$medals .= "<a href=\"$basename&action=award&type=2&pwuser=".rawurlencode($rt[username])."&medal=$value\" target=\"_blank\"><img src=\"$hkimg/{$_MEDALDB[$value][picurl]}\" title=\"{$_MEDALDB[$value][name]}\"></a> ";
					} else {
						$medals .= "<img src=\"$hkimg/{$_MEDALDB[$value][picurl]}\" title=\"{$_MEDALDB[$value][name]}\"> ";
					}
				}
			}
			$rt['medals'] = $medals;
			$listdb[] = $rt;
		}
	}
	require_once PrintHack('index');footer();

} elseif ($action == 'award') {

	if (strpos($md_groups,",$groupid,") === false) {
		Showmsg('medal_groupright');
	}
	if (!$_POST['step']) {

		S::gp(array('type','pwuser','medal'));
		if ($type == 2) {
			$type_2 = "checked";
			$type_1 = "";
		} else {
			$type_1 = "checked";
			$type_2 = "";
		}
		require_once PrintHack('index');footer();

	} elseif ($_POST['step'] == "2") {

		S::gp(array('pwuser','reason','medal','type','timelimit'),null,'1');
		strpos($pwuser,',') && $pwuser = explode(',',$pwuser);
		$medal  = (int)$medal;
		!$medal && Showmsg('medal_nomedal');
		$reason = S::escapeChar($reason);
		!$reason && Showmsg('medal_noreason');
		$timelimit = (int)$timelimit;
		if (is_array($pwuser)) {
			foreach ($pwuser as $key => $val) {
				if (!$val) {
					unset($pwuser[$key]);
				} else {
					$pwuser[$key] = $val;
				}
			}
		} else {
			$pwuser = array($pwuser);
		}
		!$pwuser && Showmsg('username_empty');
		
		$awardusers = $medaluser = array();
		$members = $userService->getByUserNames($pwuser); //uid,username,medals
		foreach ($members as $rt) {
			S::slashes($rt);
			if ($type == 1) {
				if ($rt['medals'] && strpos(",$rt[medals],",",$medal,") !== false) {
					$erroruser = $rt['username'];
					Showmsg('medal_alreadyhave');
				} elseif ($rt['medals']) {
					$rt['medals'] = "$rt[medals],$medal";
				} else{
					$rt['medals'] = $medal;
				}
				$medaluser[] = array($rt['uid'],$medal);
			} elseif ($type == 2) {
				if (!$rt['medals'] || strpos(",$rt[medals],",",$medal,") === false) {
					$erroruser = $rt['username'];
					Showmsg('medal_none');
				} else {
					$rt['medals'] = substr(str_replace(",$medal,",',',",$rt[medals],"),1,-1);
				}
				$medaluser[] = $rt['uid'];
			} else {
				Showmsg('illegal_request');
			}
			$awardusers[]	= $rt;
		}
		
		//检查是否有申请记录
		$applies = $db->query("SELECT id FROM pw_medalslogs WHERE awardee IN (".S::sqlImplode($pwuser).") AND action=3 AND level = ".S::sqlEscape($medal));
		while ($rt = $db->fetch_array($applies)){
			 Showmsg('medal_haveapp');
		}
		
		!count($awardusers) && Showmsg('medal_nouser');
		$insertlogs = array();
		foreach ($awardusers as $rt) {
			if ($type == 1) {
				if ($md_ifmsg) {
					M::sendNotice(
						array($rt['username']),
						array(
							'title' => getLangInfo('writemsg','metal_add'),
							'content' => getLangInfo('writemsg','metal_add_content',array(
								'mname'		=> $_MEDALDB[$medal]['name'],
								'windid'	=> $windid,
								'reason'	=> stripslashes($reason)
							)),
						)
					);
				}
			} elseif ($type == 2) {
				if($md_ifmsg){
					M::sendNotice(
						array($rt['username']),
						array(
							'title' => getLangInfo('writemsg','metal_cancel'),
							'content' => getLangInfo('writemsg','metal_cancel_content',array(
								'mname'		=> $_MEDALDB[$medal]['name'],
								'windid'	=> $windid,
								'reason'	=> stripslashes($reason)
							)),
						)
					);
				}
				$timelimit = 0;
				$db->update("UPDATE pw_medalslogs SET state='1' WHERE awardee=".S::sqlEscape($rt['username'],false)."AND level=".S::sqlEscape($medal));
			} else {
				Showmsg('illegal_request');
			}
			$rt['medals'] == ',' && $rt['medals'] = '';
			$userService->update($rt['uid'], array('medals'=>$rt['medals']));
			//* $_cache->delete('UID_'.$rt['uid']);
			$insertlogs[] = array($rt['username'],$windid,$timestamp,$timelimit,$medal,$type,$reason);
		}
		if ($medaluser) {
			if ($type == 1) {
				$db->update("INSERT INTO pw_medaluser(uid,mid) VALUES ".S::sqlMulti($medaluser));
			} elseif ($type == 2) {
				$db->update('DELETE FROM pw_medaluser WHERE mid='.S::sqlEscape($medal).' AND uid IN('.S::sqlImplode($medaluser).')');
			}
		}
		if (count($insertlogs)) {
			$db->update("INSERT INTO pw_medalslogs(awardee,awarder,awardtime,timelimit,level,action,why) VALUES".S::sqlMulti($insertlogs));
		}
		updatemedal_list();
		refreshto("$basename&action=list",'operate_success');
	}
} elseif ($action == 'log') {

	$groupid == 'guest' && Showmsg('not_login');

	if (!$_GET['job']) {

		S::gp(array('page'));
		(!is_numeric($page) || $page < 1) && $page = 1;
		$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
		$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_medalslogs WHERE action<>3");
		$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&action=log&");

		$logdb = array();
		$query = $db->query("SELECT * FROM pw_medalslogs WHERE action<>3 ORDER BY id DESC $limit");
		while($rt = $db->fetch_array($query)){
			$rt['awardtime'] = get_date($rt['awardtime'],'y-m-d H:i');
			$logdb[] = $rt;
		}
		require_once PrintHack('index');footer();

	} elseif ($_GET['job'] == 'del') {

		$groupid != '3' && Showmsg('medal_dellog');
		$id = (int)S::getGP('id');
		$rt = $db->get_one("SELECT id,state,action,timelimit FROM pw_medalslogs WHERE id=".S::sqlEscape($id));
		if ($rt['action'] == 1 && $rt['state'] == 0 && $rt['timelimit'] > 0) {
			Showmsg('medallog_del_error');
		}
		$db->update("DELETE FROM pw_medalslogs WHERE id=".S::sqlEscape($id));
		refreshto("$basename&action=log",'operate_success');

	} else {
		Showmsg('illegal_request');
	}
} elseif ($action == 'apply') {

	!$md_ifapply && Showmsg('medal_appclose');
	if (strpos($md_appgroups,",$groupid,") === false) {
		Showmsg('medal_appgroupright');
	}
	$appcheck = $db->get_one("SELECT id FROM pw_medalslogs WHERE awardee=".S::sqlEscape($windid)." AND action=3");
	$appcheck && Showmsg('medal_haveapp');

	if (!$_POST['step']) {

		$id = (int)S::getGP('id');
		require_once PrintHack('index');footer();

	} elseif ($_POST['step'] == 2) {

		S::gp(array('reason','medal','timelimit'));
		!$reason && Showmsg('medal_noreason');
		$medal  = (int)$medal;
		!$medal && Showmsg('medal_nomedal');
		$reason = S::escapeChar($reason);
		$timelimit = (int)$timelimit;
		$userdb['medals'] && in_array($medal,$userdb['medals']) && Showmsg('medal_alreadyhaveself');
		$db->update("INSERT INTO pw_medalslogs SET " . S::sqlSingle(array(
			'awardee'	=> $windid,
			'awardtime'	=> $timestamp,
			'timelimit'	=> $timelimit,
			'level'		=> $medal,
			'action'	=> 3,
			'why'		=> $reason
		)));
		M::sendNotice(
			array($windid),
			array(
				'title' => getLangInfo('writemsg','metal_post_title'),
				'content' => getLangInfo('writemsg','metal_post_content',array(
					'mname'		=> $_MEDALDB[$medal]['name'],
					'windid'	=> $windid,
					'reason'	=> stripslashes($reason)
				)),
			)
		);

		refreshto($basename,'operate_success');

	} else {
		Showmsg('illegal_request');
	}
} elseif ($action == 'approve') {
	!$md_ifapply && Showmsg('medal_appclose');
	if (strpos($md_groups,",$groupid,") === false) {
		Showmsg('medal_groupright');
	}
	$job = S::escapeChar(S::getGP('job'));

	if (!$job) {

		S::gp(array('page'));
		(!is_numeric($page) || $page < 1) && $page = 1;
		$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
		$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_medalslogs WHERE action=3");
		$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&action=approve&");

		$appdb = array();
		$query = $db->query("SELECT * FROM pw_medalslogs WHERE action=3 ORDER BY id ASC $limit");
		while($rt = $db->fetch_array($query)){
			$rt['awardtime'] = get_date($rt['awardtime'],'y-m-d H:i');
			$appdb[] = $rt;
		}
		require_once PrintHack('index');footer();

	} elseif ($job == 'pass') {

		$id = S::getGP('id');
		if (is_array($id)) {
			foreach ($id as $key => $val) {
				$val = (int)$val;
				if ($val) {
					$id[$key] = $val;
				} else {
					unset($id[$key]);
				}
			}
			if (count($id)) {
				$id = S::sqlImplode($id);
			} else {
				Showmsg('medal_nobody');
			}
		} else {
			$id = (int)$id;
			!$id && Showmsg('medal_nobody');
		}
		$medaluser = array();
		$rs = $db->query("SELECT l.level,l.why,m.uid,m.username,m.medals FROM pw_medalslogs l LEFT JOIN pw_members m ON l.awardee=m.username WHERE l.id IN($id)");
		while ($rt = $db->fetch_array($rs)) {
			$medal 	= $rt['level'];
			$reason = $rt['why'];
			if ($rt['medals'] && strpos(",$rt[medals],",",$medal,") !== false) {
				continue;
			} elseif ($rt['medals']) {
				$medals = "$rt[medals],$medal";
			} else {
				$medals = $medal;
			}
			$medaluser[] = array($rt['uid'],$medal);
			if ($md_ifmsg) {
				M::sendNotice(
					array($rt['username']),
					array(
						'title' => getLangInfo('writemsg','metal_add'),
						'content' => getLangInfo('writemsg','metal_add_content',array(
							'mname'		=> $_MEDALDB[$medal]['name'],
							'windid'	=> $windid,
							'reason'	=> $reason
						)),
					)
				);
			}
			$medals == ',' && $medals = '';
			$userService->update($rt['uid'], array('medals'=>$medals));
			//* $_cache->delete('UID_'.$rt['uid']);
			if ($medaluser) {
				$db->update("INSERT INTO pw_medaluser(uid,mid) VALUES ".S::sqlMulti($medaluser));
			}
		}
		$db->free_result();
		unset($medal,$medals,$reason);
		$db->update("UPDATE pw_medalslogs SET " . S::sqlSingle(array(
			'awarder'	=> $windid,
			'awardtime'	=> $timestamp,
			'action'	=> 1
		)) . " WHERE id IN($id)");

		updatemedal_list();
		refreshto("$basename&action=approve",'operate_success');

	} elseif ($job == 'del') {

		$id = S::getGP('id');
		if (is_array($id)) {
			foreach($id as $key => $val) {
				$val = (int)$val;
				if ($val) {
					$id[$key] = $val;
				} else {
					unset($id[$key]);
				}
			}
			if (count($id)) {
				$id = S::sqlImplode($id);
				if ($md_ifmsg) {
					$query = $db->query("SELECT awardee,level,why FROM pw_medalslogs WHERE id IN($id)");
					while ($rt = $db->fetch_array($query)) {
						$medal = $rt['level'];
						$reason = $rt['why'];
						M::sendNotice(
							array($rt['awardee']),
							array(
								'title' => getLangInfo('writemsg','metal_refuse'),
								'content' => getLangInfo('writemsg','metal_refuse_content',array(
									'mname'		=> $_MEDALDB[$medal]['name'],
									'windid'	=> $windid,
									'reason'	=> $reason
								)),
							)
						);
					}
				}
				$db->update("DELETE FROM pw_medalslogs WHERE id IN($id)");
			} else {
				Showmsg('medal_nobody');
			}
		} else {
			$id = (int)$id;
			!$id && Showmsg('medal_nobody');
			if ($md_ifmsg) {
				$rt = $db->get_one("SELECT awardee,level,why FROM pw_medalslogs WHERE id=".S::sqlEscape($id));
				!$rt && Showmsg('medal_nobody');
				$medal = $rt['level'];
				$reason = $rt['why'];
				M::sendNotice(
					array($rt['awardee']),
					array(
						'title' => getLangInfo('writemsg','metal_refuse'),
						'content' => getLangInfo('writemsg','metal_refuse_content',array(
							'mname'		=> $_MEDALDB[$medal]['name'],
							'windid'	=> $windid,
							'reason'	=> $reason
						)),
					)
				);
			}
			$db->update("DELETE FROM pw_medalslogs WHERE id=".S::sqlEscape($id));
		}
		refreshto("$basename&action=approve",'operate_success');
	} else {
		Showmsg('illegal_request');
	}
} else {
	Showmsg('illegal_request');
}

function updatemedal_list(){
	global $db;
	$query = $db->query("SELECT uid FROM pw_medaluser GROUP BY uid");
	$medaldb = '<?php die;?>0';
	while ($rt = $db->fetch_array($query)) {
		$medaldb .= ','.$rt['uid'];
	}
	pwCache::setData(D_P.'data/bbscache/medals_list.php',$medaldb);
}

?>