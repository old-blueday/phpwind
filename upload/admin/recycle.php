<?php
!function_exists('adminmsg') && exit('Forbidden');
$type != 'reply' && $type = 'topic';
!$db_perpage && $db_perpage = 25;
$basename="$admin_file?adminjob=recycle";
InitGP(array('fid'),'GP',2);
require_once(R_P.'require/updateforum.php');

$sql = '';
if ($admin_gid == 5) {
	list($allowfid,$forumcache) = GetAllowForum($admin_name);
	$sql = $allowfid ? " AND r.fid IN($allowfid)" : '';
} else {
	include(D_P.'data/bbscache/forumcache.php');
	list($hidefid,$hideforum) = GetHiddenForum();
	if ($admin_gid == 3) {
		$forumcache .= $hideforum;
	} else {
		$hidefid && $sql = " AND r.fid NOT IN($hidefid)";
	}
}
$forumcache = str_replace("<option value=\"$fid\">","<option value=\"$fid\" selected>",$forumcache);

if (!$action) {

	InitGP(array('admin','username','keyword','cname'));
	InitGP(array('uid','page','from','cyid'),'GP',2);
	
	$admin && $sql .= " AND r.admin=".pwEscape($admin);
	if ($username) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$uid = $userService->getUserIdByUserName($username);
		(!$uid) && Showmsg('用户不存在');
	}
	(!is_numeric($page) || $page<1) && $page = 1;
	$limit = pwLimit(($page-1)*$db_perpage,$db_perpage);
	if ($type == 'topic'){

		//关键字
		if ($keyword) {
			$keyword = trim($keyword);
			if (strpos($keyword,'*') !== false) {
				$sqlKeyword = str_replace('*','%',$keyword);
				$sql .= " AND t.subject LIKE ".pwEscape($sqlKeyword);
			} else {
				$sql .= " AND t.subject=".pwEscape($keyword);
			}
		}
		//来源
		$fromSelect[$from] = "selected";
		if ('1' == $from && $fid) {
			$forum[$fid]['type'] == 'category' && adminmsg('forum_category_err');
			$sql .= " AND r.fid=".pwEscape($fid);
		} elseif ('1' == $from && empty($fid)) {
			$sql .= " AND r.fid>0";
		} elseif ('2' == $from && ($cname || $cyid)) {
			$cname && $cyid = $db->get_value("SELECT id FROM pw_colonys WHERE cname=" . pwEscape($cname));
			empty($cyid) && Showmsg('群组不存在');
			$sql .= " AND a.cyid=".pwEscape($cyid);
		}
		$uid && $sql .= " AND t.authorid=".pwEscape($uid);
		if ('2' == $from) {
			$rt = $db->get_one("SELECT COUNT(*) AS sum FROM pw_argument a LEFT JOIN pw_recycle r ON a.tid=r.tid LEFT JOIN pw_threads t ON r.tid=t.tid WHERE r.pid='0' $sql");
			$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage), "$basename&from=2&keyword=" . rawurlencode($keyword) . "&cyid=$cyid&uid=$uid&admin=$admin&type=$type&");
			$query = $db->query("SELECT r.*,t.subject,t.author,t.authorid,c.id AS cyid,c.cname FROM pw_argument a LEFT JOIN pw_recycle r ON r.tid=a.tid LEFT JOIN pw_threads t ON r.tid=t.tid LEFT JOIN pw_colonys c ON a.cyid=c.id WHERE r.pid='0' $sql ORDER BY deltime DESC $limit");
			while ($rt = $db->fetch_array($query)) {
				$rt['deltime'] = get_date($rt['deltime']);
				$rt['subject'] = substrs($rt['subject'],50);
				$recycledb[$rt['tid']] = $rt;
				$ttable_a[GetTtable($rt['tid'])][] = $rt['tid'];
			}
		} else {
			$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_recycle r LEFT JOIN pw_threads t USING(tid) WHERE r.pid='0' $sql");
			$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&from=$from&keyword=".rawurlencode($keyword)."&fid=$fid&uid=$uid&admin=$admin&type=$type&");
			$query = $db->query("SELECT r.*,t.subject,t.author,t.authorid,t.tpcstatus FROM pw_recycle r LEFT JOIN pw_threads t USING(tid) WHERE r.pid='0' $sql ORDER BY deltime DESC $limit");
			while ($rt = $db->fetch_array($query)) {
				$rt['deltime'] = get_date($rt['deltime']);
				$rt['subject'] = substrs($rt['subject'],50);
				$rt['fname']   = $forum[$rt['fid']]['name'];
				getstatus($rt['tpcstatus'], 1) && $cyids[] = $rt['tid'];
				$recycledb[$rt['tid']] = $rt;
				$ttable_a[GetTtable($rt['tid'])][] = $rt['tid'];
			}
			if ($cyids) {
				$query = $db->query("SELECT a.tid,a.cyid,c.cname FROM pw_argument a LEFT JOIN pw_colonys c ON a.cyid=c.id WHERE tid IN (" . pwImplode($cyids) . ')');
				while ($rt = $db->fetch_array($query)) {
					$recycledb[$rt['tid']]['cyid'] = $rt['cyid'];
					$recycledb[$rt['tid']]['cname'] = $rt['cname'];
				}
			}
		}
		foreach ($ttable_a as $pw_tmsgs => $value) {
			$value = pwImplode($value);
			$query = $db->query("SELECT tid,content FROM $pw_tmsgs WHERE tid IN($value)");
			while ($rt = $db->fetch_array($query)) {
				$rt['content'] = str_replace("\n","<br>",$rt['content']);
				$recycledb[$rt['tid']]['content'] = $rt['content'];
			}
		}

	} else {

		//关键字
		if ($keyword) {
			$keyword = trim($keyword);
			if (strpos($keyword,'*') !== false) {
				$sqlKeyword = str_replace('*','%',$keyword);
				$sql .= " AND p.content LIKE ".pwEscape($sqlKeyword);
			} else {
				$sql .= " AND p.content=".pwEscape($keyword);
			}
		}
		//来源
		if ('1' == $from && $fid) {
			$forum[$fid]['type'] == 'category' && adminmsg('forum_category_err');
			$sql .= " AND r.fid=".pwEscape($fid);
		} elseif ('2' == $from && ($cname || $cyid)) {
			$cname && $cyid = $db->get_value("SELECT id FROM pw_colonys WHERE cname=" . pwEscape($cname));
			empty($cyid) && Showmsg('群组不存在');
			$sql .= " AND a.cyid=".pwEscape($cyid);
		} elseif ('2' == $from) {
			$sql .= " AND a.cyid!=0";
		} elseif ('1' == $from) {
			$sql .= " AND r.fid!=0";
		}

		$uid && $sql .= " AND p.authorid=".pwEscape($uid);
		
		if ($db_plist && count($db_plist)>1) {
			InitGP(array('ptable'));
			!is_numeric($ptable) && $ptable = $db_ptable;
			foreach ($db_plist as $key => $val) {
				$name = $val ? $val : ($key != 0 ? getLangInfo('other','posttable').$key : getLangInfo('other','posttable'));
				$p_table .= "<option value=\"$key\">".$name."</option>";
			}
			$p_table  = str_replace("<option value=\"$ptable\">","<option value=\"$ptable\" selected>",$p_table);
			$url_a	 .= "ptable=$ptable&";
			$pw_posts = GetPtable($ptable);
		} else {
			$pw_posts = 'pw_posts';
		}
		
		if ('2' == $from) {
			$rt = $db->get_one("SELECT COUNT(*) AS sum FROM pw_recycle r LEFT JOIN pw_argument a ON r.tid=a.tid LEFT JOIN $pw_posts p ON r.pid=p.pid WHERE r.pid>'0' $sql AND p.fid='0'");
			$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage), "$basename&from=2&keyword=" . rawurlencode($keyword) . "&cyid=$cyid&uid=$uid&admin=$admin&type=$type&ptable=$ptable&");
			$query = $db->query("SELECT r.*,p.author,p.authorid,p.content,t.subject,t.tpcstatus FROM pw_recycle r LEFT JOIN $pw_posts p USING(pid) LEFT JOIN pw_argument a ON r.tid=a.tid LEFT JOIN pw_threads t ON r.tid=t.tid WHERE r.pid>'0' $sql AND p.fid='0' ORDER BY deltime DESC $limit");
		} else {
			$rt = $db->get_one("SELECT COUNT(*) AS sum FROM pw_recycle r LEFT JOIN pw_argument a ON r.tid=a.tid LEFT JOIN $pw_posts p ON r.pid=p.pid WHERE r.pid>'0' $sql AND p.fid='0'");
			$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage), "$basename&from=2&keyword=" . rawurlencode($keyword) . "&cyid=$cyid&uid=$uid&admin=$admin&type=$type&ptable=$ptable&");
			$query = $db->query("SELECT r.*,p.author,p.authorid,p.content,t.subject,t.tpcstatus FROM pw_recycle r LEFT JOIN $pw_posts p USING(pid) LEFT JOIN pw_threads t ON r.tid=t.tid WHERE r.pid>'0' $sql AND p.fid='0' ORDER BY deltime DESC $limit");
		}
		while ($rt = $db->fetch_array($query)) {
			$rt['deltime'] = get_date($rt['deltime']);
			$rt['subject'] = substrs($rt['subject'],50);
			$rt['content'] = str_replace("\n","<br>",$rt['content']);
			$rt['fname']   = $forum[$rt['fid']]['name'];
			getstatus($rt['tpcstatus'], 1) && $cyids[] = $rt['tid'];
			$recycledb[$rt['tid']]   = $rt;
		}
		if ($cyids) {
			$query = $db->query("SELECT a.tid,a.cyid,c.cname FROM pw_argument a LEFT JOIN pw_colonys c ON a.cyid=c.id WHERE tid IN (" . pwImplode($cyids) . ')');
			while ($rt = $db->fetch_array($query)) {
				$colonydb[$rt['tid']]['cyid'] = $rt['cyid'];
				$colonydb[$rt['tid']]['cname'] = $rt['cname'];
			}
		}
	}
	include PrintEot('recycle');exit;

} elseif ($_POST['action'] == 'revert') {

	InitGP(array('selid'),'P');
	$arrselids = $fids	= array();
	foreach ($selid as $key => $value) {
		if (is_numeric($key)) {
			$fids[$value] = 1;
			$arrselids[] = $key;
		}
	}
	if ($arrselids) {
		$selids = pwImplode($arrselids);
		if ($type == 'topic') {
			$ptable_a = $modeldb = $activityDb = $pcdb = array();
			$query = $db->query("SELECT fid,ptable,modelid,special,tid,tpcstatus FROM pw_threads WHERE tid IN ($selids)");
			while ($rt = $db->fetch_array($query)) {
				if ($rt['modelid']) {
					$modeldb[$rt['modelid']][] = $rt['tid'];
				} elseif ($rt['special'] == 8) {//活动
					$activityDb[] = $rt['tid'];
				} elseif ($rt['special'] > 20) {//团购
					$pcdb[$rt['special']][] = $rt['tid'];
				}
				if ($rt['tpcstatus'] && getstatus($rt['tpcstatus'], 1)) {
					$cydb[] = $rt['tid'];
				}
				$ptable_a[$rt['ptable']]=1;
			}
			foreach ($ptable_a as $key => $val) {
				$pw_posts = GetPtable($key);
				$db->update("UPDATE pw_recycle r LEFT JOIN $pw_posts p ON r.tid=p.tid SET p.fid=r.fid WHERE r.pid='0' AND r.tid IN($selids)");
			}
			$db->update("UPDATE pw_recycle r LEFT JOIN pw_threads t ON r.tid=t.tid SET t.fid=r.fid,t.ifshield='0' WHERE r.pid='0' AND r.tid IN ($selids)");
			$db->update("UPDATE pw_attachs a LEFT JOIN pw_recycle r ON a.tid=r.tid SET a.fid=r.fid WHERE a.tid IN($selids)");

			$db->update("DELETE FROM pw_recycle WHERE tid IN ($selids)");

			if ($modeldb) {
				RevertModelTopic($modeldb);
			}
			if ($activityDb) {
				RevertActivityTopic($activityDb);
			}
			if ($pcdb) {
				RevertPcTopic($pcdb);
			}
			if ($cydb) {
				ReverColonyTopic($cydb);
			}
			$thread = L::loadClass('threads', 'forum');
			$thread->delThreads($arrselids);
		} else {
			InitGP(array('ptable'));
			!is_numeric($ptable) && $ptable = $db_ptable;
			$pw_posts = GetPtable($ptable);
			$db->update("UPDATE $pw_posts p LEFT JOIN pw_recycle r ON p.pid=r.pid SET p.tid=r.tid,p.fid=r.fid WHERE p.pid IN ($selids)");
			$db->update("UPDATE pw_attachs a LEFT JOIN pw_recycle r ON a.pid=r.pid SET a.fid=r.fid WHERE a.pid IN ($selids)");
			$delarticle = L::loadClass('delarticle', 'forum');
			$delarticle->resetReplayToppedFloor('',$selids,$pw_posts);
			$repliesnum = array();
			$query = $db->query("SELECT * FROM pw_recycle WHERE pid IN ($selids)");
			while ($rt = $db->fetch_array($query)) {
				$repliesnum[$rt['tid']]++;
			}
			foreach ($repliesnum as $key => $val) {
				$db->update("UPDATE pw_threads SET replies=replies+".pwEscape($val)."WHERE tid=".pwEscape($key,false));
			}
			$db->update("DELETE FROM pw_recycle WHERE pid IN ($selids)");
		}
	}
	foreach ($fids as $key => $value) {
		updateforum($key);
	}
	adminmsg('operate_success',"$basename&type=$type");

} elseif ($action == 'delete' || $action == 'del') {

	$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
	if ($action == 'del') {
		InitGP(array('selid'),'P');
		$selids = array();
		foreach ($selid as $key => $value) {
			if (is_numeric($key)) {
				$selids[] = $key;
			}
		}
		$selids && $selids = pwImplode($selids);
		empty($selids) && adminmsg('operate_success');
	}
	$_tids = $_pids = array();
	$goon = 0;
	if ($type == 'topic') {
		$delids = $shids = $pollids = $actids = $rewids = $ids = $taid_a = $ttable_a = $ptable_a = array();
		if ($action == 'del') {
			$sql = "WHERE  r.pid='0' AND r.tid IN ($selids)";
		} else {
			$sql = "WHERE r.pid='0' AND (t.fid='0' OR t.ifshield='2')";
		}
		$query  = $db->query("SELECT r.*,t.special,t.ifshield,t.ifupload,t.ptable,t.replies,t.fid AS ckfid FROM pw_recycle r LEFT JOIN pw_threads t ON r.tid=t.tid $sql LIMIT 100");
		while (@extract($db->fetch_array($query))) {
			$goon = 1;
			$ids[] = $tid;
			($ifshield != '2' || $replies == '0' || $ckfid == '0') && $delids[] = $tid;
			$special == 1 && $pollids[] = $tid;
			$special == 2 && $actids[]  = $tid;
			$special == 3 && $rewids[]  = $tid;
			if ($ifshield != '2' || $replies == '0' || $ckfid == '0') {
				$ptable_a[$ptable] = 1;
				$ttable_a[GetTtable($tid)][] = $tid;
			}
			if ($ifupload) {
				$taid_a[GetTtable($tid)][] = $tid;
				$_tids[$tid] = $tid;
				$_pids[0] = 0;
				if ($ifshield != '2' || $replies == '0' || $ckfid == '0') {
					$pw_posts = GetPtable($ptable);
					$query2 = $db->query("SELECT pid FROM $pw_posts WHERE tid=" . pwEscape($tid,false) . " AND aid!=''");
					while ($pid2 = $db->fetch_array($query2)) {
						$_pids[$pid2['pid']] = $pid2['pid'];
					}
				}
			}
		}
		if ($pollids) {
			$pollids = pwImplode($pollids,false);
			$db->update("DELETE FROM pw_polls WHERE tid IN($pollids)");
		}
		if ($actids) {
			$actids = pwImplode($actids,false);
			$db->update("DELETE FROM pw_activity WHERE tid IN($actids)");
			$db->update("DELETE FROM pw_actmember WHERE actid IN($actids)");
		}
		if ($rewids) {
			$rewids = pwImplode($rewids,false);
			$db->update("DELETE FROM pw_reward WHERE tid IN($rewids)");
		}
		$delids = pwImplode($delids,false);
		if ($delids) {
			# $db->update("DELETE FROM pw_threads	WHERE tid IN($delids)");
			# ThreadManager
            $threadManager = L::loadClass("threadmanager", 'forum');
			$threadManager->deleteByThreadIds($fid,$delids);

			foreach ($ptable_a as $key => $val) {
				$pw_posts = GetPtable($key);
				$db->update("DELETE FROM $pw_posts WHERE tid IN($delids)");
			}
		}
		foreach ($ttable_a as $pw_tmsgs => $val) {
			if ($val) {
				$val = pwImplode($val,false);
				$db->update("DELETE FROM $pw_tmsgs WHERE tid IN($val)");
			}
		}
		delete_tag($delids);
		if ($ids) {
			$ids = pwImplode($ids,false);
			$db->update("DELETE FROM pw_recycle WHERE tid IN ($ids)");
		}
	} else {
		InitGP(array('ptable'));
		!is_numeric($ptable) && $ptable = $db_ptable;
		$pw_posts = GetPtable($ptable);
		if ($action == 'del') {
			$sql = "WHERE p.pid IN ($selids) AND p.tid='0'";
		} else {
			$sql = "WHERE p.tid='0'";
		}
		$query = $db->query("SELECT p.pid,p.aid,r.tid FROM $pw_posts p LEFT JOIN pw_recycle r ON p.pid=r.pid $sql LIMIT 100");
		while ($rt = $db->fetch_array($query)) {
			$goon = 1;
			$ids[] = $rt['pid'];
			if ($rt['aid']) {
				$_pids[$rt['pid']] = $rt['pid'];
				$_tids[$rt['tid']] = $rt['tid'];
			}
		}
		if ($ids) {
			$ids = pwImplode($ids,false);
			$db->update("DELETE FROM $pw_posts WHERE pid IN ($ids)");
			$db->update("DELETE FROM pw_recycle WHERE pid IN ($ids)");
		}
	}

	if ($_tids) {
		$pw_attachs = L::loadDB('attachs', 'forum');
		$attachdb = $pw_attachs->getByTid($_tids,$_pids);
		require_once(R_P.'require/updateforum.php');
		delete_att($attachdb);
		pwFtpClose($ftp);
	}
	if ($goon && $action == 'delete') {
		$j_url = "$basename&action=$action&type=$type";
		adminmsg('delete_recycle',EncodeUrl($j_url),2);
	} else {
		adminmsg('operate_success',"$basename&type=$type");
	}
} elseif ($action == 'clear') {
	InitGP(array('selid'),'P');
	$selids = array();
	foreach($selid as $key => $value){
		if(is_numeric($key)){
			$selids[] = $key;
		}
	}
	$selids && $selids = pwImplode($selids);
	if ($selids) {
		if ($type == 'topic') {
			$db->update("DELETE FROM pw_recycle WHERE tid IN ($selids) AND pid='0'");
		} else {
			$db->update("DELETE FROM pw_recycle WHERE pid IN ($selids)");
		}
		adminmsg('operate_success',"$basename&type=$type");
	} else {
		adminmsg('operate_error',"$basename&type=$type");
	}
} elseif ($action == 'empty'){
	$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
	$recycleService = new PW_RecycleEmpty();
	if($type == 'topic'){
		$result = $recycleService->emptyTopic();
		adminmsg($result);
	}else{
		InitGP(array('ptable'));
		!is_numeric($ptable) && $ptable = $db_ptable;
		$result = $recycleService->emptyReply($ptable);
		$j_url = "$basename&type=$type";
		adminmsg($result,EncodeUrl($j_url),2);
	}
}

function RevertModelTopic($modeldb){
	global $db;
	foreach ($modeldb as $key => $value) {
		$modelids = pwImplode($value);
		$pw_topicvalue = GetTopcitable($key);
		$db->update("UPDATE $pw_topicvalue SET ifrecycle='0' WHERE tid IN($modelids)");
	}
}

function RevertPcTopic($pcdb){
	global $db;
	foreach ($pcdb as $key => $value) {
		$pcids =  pwImplode($value);
		$key = $key > 20 ? $key - 20 : 0;
		$pcvaluetable = GetPcatetable($key);
		$db->update("UPDATE $pcvaluetable SET ifrecycle='0' WHERE tid IN($pcids)");
	}
}

function RevertActivityTopic($activityDb) {
	global $db;
	$defaultValueTableName = getActivityValueTableNameByActmid();
	$newActivityDb = array();
	$query = $db->query("SELECT actmid,tid FROM $defaultValueTableName WHERE tid IN(".pwImplode($activityDb).")");
	while ($rt = $db->fetch_array($query)) {
		$newActivityDb[$rt['actmid']][] = $rt['tid'];
	}
	/*支付成功费用流通日志*/
	L::loadClass('ActivityForBbs', 'activity', false);
	$postActForBbs = new PW_ActivityForBbs($data);

	$data = array();
	/*支付成功费用流通日志*/
	foreach ($newActivityDb as $key => $value) {
		$tids = pwImplode($value);
		$userDefinedValueTableName = getActivityValueTableNameByActmid($key, 1, 1);
		$db->update("UPDATE $defaultValueTableName SET ifrecycle='0' WHERE tid IN($tids)");
		$db->update("UPDATE $userDefinedValueTableName SET ifrecycle='0' WHERE tid IN($tids)");
		/*支付成功费用流通日志*/
		foreach ($value as $tid) {
			$statusValue = $postActForBbs->getActivityStatusValue($tid);
			$postActForBbs->UpdatePayLog($tid,0,$statusValue);
		}
		/*支付成功费用流通日志*/
	}
}

function ReverColonyTopic($tids) {
	global $db;
	$query = $db->query("SELECT COUNT(*) AS tnum, SUM(b.replies+1) AS pnum, a.cyid FROM pw_argument a LEFT JOIN pw_threads b ON a.tid=b.tid WHERE a.tid IN(" . pwImplode($tids) . ") GROUP BY a.cyid");
	while ($rt = $db->fetch_array($query)) {
		$db->update("UPDATE pw_colonys SET tnum=tnum+" . pwEscape($rt['tnum']) . ',pnum=pnum+' . pwEscape($rt['pnum']) . ' WHERE id=' . pwEscape($rt['cyid']));
	}
}

class PW_RecycleEmpty {

	var $_db = null;
	var $_defaultNum = 20;

	function PW_RecycleEmpty(){
		$this->_db = &$GLOBALS['db'];
	}

	/*
	 * 清空主题回收站 [如果主题回收站东西太多，则分任务执行]
	 */
	function emptyTopic() {
		$count = $this->_countRecycle();
		if( $count < 1 ){
			return $this->_getMessage('empty_topic_error');
		}
		if( $count <= $this->_defaultNum ){
			 $this->_deleteRecycle();
		}else{
			$task = ceil($count/$this->_defaultNum);
			for($i=1;$i<=$task;$i++){
				$this->_deleteRecycle();
			}
		}
		$this->_emptyRecycle();
		return $this->_getMessage('empty_topic_success');
	}

	function _deleteRecycle(){
		$delarticle = $this->getDelArticelService();
		$threadIds = $this->_getRecycle();
		if(!is_array($threadIds) && !$threadIds ){
			return null;
		}
		return $delarticle->delTopicByTids($threadIds);
	}

	function _getRecycle(){
		$query = $this->_db->query("SELECT * FROM pw_recycle WHERE pid=0 LIMIT ".$this->_defaultNum);
		$threadIds = array();
		while($result = $this->_db->fetch_array($query)){
			$threadIds[] = $result['tid'];
		}
		return $threadIds;
	}

	function _countRecycle(){
		return $this->_db->get_value("SELECT COUNT(*) as count FROM pw_recycle WHERE pid=0  LIMIT 1");
	}

	function _emptyRecycle(){
		$this->_db->query("DELETE FROM pw_recycle WHERE pid=0");
	}

	/*
	 * 清除回复回收站
	 */
	function emptyReply($ptable){
		$count = $this->_countReplyRecycle();
		if( $count < 1 ){
			return $this->_getMessage('empty_reply_error');
		}
		if( $count <= $this->_defaultNum ){
			 $this->_deleteReplyRecycle($ptable);
		}else{
			$task = ceil($count/$this->_defaultNum);
			for($i=1;$i<=$task;$i++){
				$this->_deleteReplyRecycle($ptable);
			}
		}
		$this->_emptyReplyRecycle();
		return $this->_getMessage('empty_reply_success');
	}

	function _deleteReplyRecycle($ptable){
		$delarticle = $this->getDelArticelService();
		$postIds = $this->_getReplyRecycle();
		if(!is_array($postIds) && !$postIds ){
			return null;
		}
		$replydb = $this->_buildPosts($postIds,$ptable);
		return $delarticle->delReply($replydb);
	}

	function _buildPosts($postIds,$ptable){
		$pw_post = GetPtable($ptable);
		$query = $this->_db->query("SELECT * FROM $pw_post WHERE pid in(".pwImplode($postIds).")");
		$replydb = array();
		while ($rt = $this->_db->fetch_array($query)) {
			$rt['ptable'] = $ptable;
			$replydb[] = $rt;
		}
		return $replydb;
	}

	function _emptyReplyRecycle(){
		$this->_db->query("DELETE FROM pw_recycle WHERE pid!=0");
	}

	function _getReplyRecycle(){
		$query = $this->_db->query("SELECT * FROM pw_recycle WHERE pid!=0 LIMIT ".$this->_defaultNum);
		$postIds = array();
		while($result = $this->_db->fetch_array($query)){
			$postIds[] = $result['pid'];
		}
		return $postIds;
	}

	function _countReplyRecycle(){
		return $this->_db->get_value("SELECT COUNT(*) as count FROM pw_recycle WHERE pid!=0  LIMIT 1");
	}

	function getDelArticelService(){
		return L::loadclass("delarticle", 'forum');
	}

	function _getMessage($k){
		$message = array();
		$message['empty_topic_success']   = '清空主题回收站成功!';
		$message['empty_topic_error']     = '主题回收站没有需要清空的内容';
		$message['empty_reply_success']   = '清空回复回收站成功!';
		$message['empty_reply_error']     = '回复回收站没有需要清空的内容';
		return $message[$k];
	}
}
?>