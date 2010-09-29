<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=report";
InitGP(array('action'));
if(empty($action) || $action == 'deal'){
	InitGP(array('page','type'));
	(!is_numeric($page) || $page < 1) && $page=1;
	$limit= pwLimit(($page-1)*$db_perpage,$db_perpage);
	$sql = $action == 'deal' ? "state='1'" : "state='0'";
	if ($type) {
		$sql .= " AND type=".pwEscape($type);
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
	InitGP(array('selid'));
	!$selid && adminmsg('operate_error');

	$selids = array();
	foreach($selid as $value){
		is_numeric($value) && $selids[] =$value;
	}
	if($selids){
		$selids=pwImplode($selids);
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

	InitGP(array('selid'),'P');
	$delids = array();
	foreach($selid as $value){
		is_numeric($value) && $delids[] =$value;
	}
	if($delids){
		$delids=pwImplode($delids);
		$db->update("DELETE FROM pw_report WHERE id IN ($delids)");
	}
	adminmsg('operate_success');

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
			$url = 'u.php?uid='.$tid;
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
	InitGP(array('page'));
	$type = $type==1 ? 1 : 0;
	(!is_numeric($page) || $page < 1) && $page=1;
	$limit= pwLimit(($page-1)*$db_perpage,$db_perpage);
	$sql .= " AND r.type=".pwEscape($type);

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
	InitGP(array('selid'),'P');
	$delids = array();
	foreach($selid as $value){
		is_numeric($value) && $delids[] =$value;
	}
	if($delids){
		$delids=pwImplode($delids);
		$db->update("DELETE FROM pw_report WHERE id IN ($delids)");
	}
	adminmsg('operate_success');
}
*/
?>