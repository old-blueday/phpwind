<?php
!function_exists('adminmsg') && exit('Forbidden');
S::gp(array('action'));
!$admintype && $admintype = 'reportcontent';
$basename="$admin_file?adminjob=report&admintype=$admintype";
if ($admintype == 'reportcontent') {
	if(empty($action) || $action == 'deal'){
		S::gp(array('page','type'));
		(!is_numeric($page) || $page < 1) && $page=1;
		$limit= S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
		$sql = $action == 'deal' ? "state='1'" : "state='0'";
		if ($type) {
			$sql .= " AND type=".S::sqlEscape($type);
			${'select_'.$type} = 'selected';
		}
	
		$query = $db->query("SELECT r.*,m.username FROM pw_report r LEFT JOIN pw_members m ON r.uid=m.uid WHERE $sql ORDER BY id DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['url'] = getUrlByType($rt['type'],$rt['tid'],$rt['pid'],$rt['uid']);
			empty($rt['type']) && $rt['type'] = 'topic';
			$rt['type'] = getLangInfo('other',$rt['type']);
			$reportdb[] = $rt;
		}
	
		$rt=$db->get_one("SELECT COUNT(*) AS count FROM pw_report WHERE $sql");
		$sum=$rt['count'];
		$numofpage=ceil($sum/$db_perpage);
		$pageurl = $action == 'deal' ? $basename."&action=deal" : $basename."&";
		$pages=numofpage($sum,$page,$numofpage,$pageurl);
	
		include PrintEot('report');exit;
	} elseif ($action == 'done') {
		S::gp(array('selid'));
		!$selid && adminmsg('operate_error');
	
		$selids = array();
		foreach($selid as $value){
			is_numeric($value) && $selids[] =$value;
		}
		if($selids){
			$selids=S::sqlImplode($selids);
			$db->update("UPDATE pw_report SET state='1' WHERE id IN ($selids)");
	
			$query = $db->query("SELECT r.tid,r.pid,r.uid,r.type,r.reason,m.username FROM pw_report r LEFT JOIN pw_members m ON r.uid=m.uid WHERE r.id IN($selids)");
			while (@extract($db->fetch_array($query))) {
				empty($type) && $type = 'topic';
				M::sendNotice(
					array($username),
					array(
						'title' => getLangInfo('writemsg','report_deal_title'),
						'content' => getLangInfo('writemsg','report_deal_content',array(
							'manager'	=> $admin_name,
							'url'		=> $db_bbsurl.'/'.getUrlByType($type,$tid,$pid),
							'admindate'	=> get_date($timestamp),
							'reason'	=> stripslashes($reason),
							'type'		=> getLangInfo('other',$type),
						)),
					)
				);
			}
		}
	
		adminmsg('operate_success');
	} elseif ($action == 'del') {
	
		S::gp(array('selid'),'P');
		$delids = array();
		foreach($selid as $value){
			is_numeric($value) && $delids[] = $value;
		}
		if($delids){
			$delids=S::sqlImplode($delids);
			$db->update("DELETE FROM pw_report WHERE id IN ($delids)");
		}
		adminmsg('operate_success');
	
	}
} elseif ($admintype == 'reportremind') {
	!$action && $action = 'list';
	$remindMember = array();
	$remindMember = $db->get_value("SELECT db_value FROM pw_config WHERE db_name = 'report_remind'");
	$remindMember = $remindMember ? unserialize($remindMember) : array();
	if ($action == 'list') {
		S::gp(array('page'));
		$page = (int) $page;
		$page < 1 && $page=1;
		$members = array();
		($count = count($remindMember)) && $members = array_slice($remindMember, (($page - 1) * $db_perpage), $db_perpage);
		$numofpage = ceil($count/$db_perpage);
		$pages = numofpage($count,$page,$numofpage,$basename.'&action=list&');
		include PrintEot('report');exit;
	} elseif ($action == 'del') {
		S::gp(array('selid'));
		!$selid && adminmsg('operate_error');
		foreach($selid as $value){
			$value = (int) $value;
			if (isset($remindMember[$value])) unset($remindMember[$value]);
		}
		$db->update('UPDATE pw_config SET db_value =' . S::sqlEscape(serialize($remindMember)) . " WHERE db_name = 'report_remind'");
		adminmsg('operate_success');
	} elseif ($action == 'add') {
		S::gp(array('username'));
		//* include_once pwCache::getPath(D_P . 'data/bbscache/level.php');
		pwCache::getData(D_P . 'data/bbscache/level.php');
		if (!$username) {
			echo 'empty';
			ajax_footer();
		}
		$userService = L::loadClass('userservice', 'user');
		$memberInfo = $userService->getByUserName($username);
		if (!$memberInfo) {
			echo 'empty';
			ajax_footer();
		}
		if ($remindMember[$memberInfo['uid']]) {
			echo 'exists';
			ajax_footer();
		}
		$groupId = $memberInfo['groupid'] == -1 ? 1 : $memberInfo['groupid'];
		$remindMember[$memberInfo['uid']] = $returnArray = array('uid' => $memberInfo['uid'], 'username' => $memberInfo['username'], 'groupname' => $ltitle[$groupId]);
		$db->update("REPLACE INTO pw_config SET vtype = 'array', db_name = 'report_remind', db_value =" . S::sqlEscape(serialize($remindMember)));
		echo "{$returnArray[uid]}\t{$returnArray[username]}\t{$returnArray[groupname]}";
		ajax_footer();
	}
}

function getUrlByType($type,$tid,$pid,$uid = 0) {
	switch ($type) {
		case 'topic':
			if ($pid) {
				$url = 'job.php?action=topost&tid='.$tid.'&pid='.$pid;
			} else {
				$url = 'read.php?tid='.$tid;
			}
			break;
		case 'grouptopic':
			if ($pid) {
				$url = 'job.php?action=topost&tid='.$tid.'&pid='.$pid;
			} else {
				$url = 'read.php?tid='.$tid;
			}
			break;
		case 'diary':
			$url = 'apps.php?q=diary&a=detail&uid='.$pid.'&did='.$tid;
			break;
		case 'photo':
			$url = 'apps.php?q=photos&a=view&uid='.$pid.'&pid='.$tid;
			break;
		case 'group':
			$url = 'apps.php?q=group&cyid='.$tid;
			break;
		case 'groupphoto':
			$url = 'apps.php?q=galbum&a=view&cyid='.$pid.'&pid='.$tid;
			break;
		case 'user':
			$url = USER_URL.$tid;
			break;
		default :
			if ($pid) {
				$url = 'job.php?action=topost&tid='.$tid.'&pid='.$pid;
			} else {
				$url = 'read.php?tid='.$tid;
			}
			break;
	}
	return $url;
}

/*

if($admin_gid == 5){
	list($allowfid,) = GetAllowForum($admin_name);
	$sql = "fid IN($allowfid)";
} else{
	if($admin_gid == 3){
		$sql = '1';
	} else{
		list($hidefid,) = GetHiddenForum();
		$sql = "fid NOT IN($hidefid)";
	}
}

if(empty($_POST['action'])){
	S::gp(array('page'));
	$type = $type==1 ? 1 : 0;
	(!is_numeric($page) || $page < 1) && $page=1;
	$limit= S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
	$sql .= " AND r.type=".S::sqlEscape($type);

	$rt=$db->get_one("SELECT COUNT(*) AS count FROM pw_report r LEFT JOIN pw_threads t ON t.tid=r.tid WHERE $sql");
	$sum=$rt['count'];
	$numofpage=ceil($sum/$db_perpage);
	$pages=numofpage($sum,$page,$numofpage,"$basename&type=$type&");

	$query=$db->query("SELECT r.*,m.username,t.fid FROM pw_report r LEFT JOIN pw_members m ON m.uid=r.uid LEFT JOIN pw_threads t ON t.tid=r.tid WHERE $sql ORDER BY id DESC $limit");
	while($rt=$db->fetch_array($query)){
		$rt['fname']=$forum[$rt['fid']]['name'];
		$reportdb[]=$rt;
	}
	include PrintEot('report');exit;
} elseif($_POST['action']=='del'){
	S::gp(array('selid'),'P');
	$delids = array();
	foreach($selid as $value){
		is_numeric($value) && $delids[] =$value;
	}
	if($delids){
		$delids=S::sqlImplode($delids);
		$db->update("DELETE FROM pw_report WHERE id IN ($delids)");
	}
	adminmsg('operate_success');
}
*/
?>