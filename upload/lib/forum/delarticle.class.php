<?php
!defined('P_W') && exit('Forbidden');

/**
 * 帖子批量删除操作
 * fix by sky_hold@163.com
 * 
 * @package Thread
 */
class PW_DelArticle {

	var $db;

	function PW_DelArticle() {
		global $db;
		$this->db =& $db;
	}

	function sqlFormatByIds($ids) {
		if (empty($ids)) {
			return '';
		}
		if (!is_array($ids) && !is_numeric($ids)) {
			$ids = explode(',', $ids);
		}
		return is_array($ids) ? "IN(" . S::sqlImplode($ids) . ')' : "=" . S::sqlEscape($ids);
	}
	
	function getForumInfo($fid, $key = null) {
		$forum = L::forum($fid);
		return $key ? $forum[$key] : $forum;
	}

	function getCreditSet($fid) {
		static $array = array();
		if (!isset($array[$fid])) {
			global $credit,$db_creditset;
			$credit || require_once (R_P . 'require/credit.php');
			$array[$fid] = $credit->creditset($this->getForumInfo($fid, 'creditset'), $db_creditset);
		}
		return $array[$fid];
	}

	function getTopicDb($sqlwhere) {
		$readdb = array();
		$query = $this->db->query("SELECT tid,fid,postdate,author,authorid,subject,replies,topped,special,ifupload,ptable,ifcheck,tpcstatus,modelid,specialsort FROM pw_threads WHERE $sqlwhere");
		while ($read = $this->db->fetch_array($query)) {
			$readdb[] = $read;
		}
		return $readdb;
	}

	function delTopicByUids($uids, $recycle = false) {
		if (!$sqlby = $this->sqlFormatByIds($uids)) {
			return;
		}
		$readdb = $this->getTopicDb("authorid $sqlby");
		$this->delTopic($readdb, $recycle);
	}

	function delTopicByTids($tids, $recycle = false, $delCredit = true) {
		if (!$sqlby = $this->sqlFormatByIds($tids)) {
			return;
		}
		$readdb = $this->getTopicDb("tid $sqlby");
		$this->delTopic($readdb, $recycle, $delCredit);
	}

	function delTopic($readdb, $recycle = false, $delCredit = true, $extra = array()) {
		global $db,$db_htmdir,$db_guestread,$windid,$db_ifpwcache,$db_creditset,$timestamp,$onlineip,$credit;
		if ($db_guestread) {
			require_once(R_P.'require/guestfunc.php');
		}
		require_once (R_P . 'require/credit.php');
		$updatetop = 0;
		$kmdTids = $specialdb = $tids = $fids = $ttable_a = $ptable_a = $recycledb = $deluids = $delutids = $cydb = $modeldb = $pcdb = $activityDb = array();

		foreach ($readdb as $key => $read) {
			$isInRecycle = ($read['fid'] == 0 && $read['ifcheck'] == 1);
			$msg_delrvrc = $msg_delmoney = 0;

			if ($delCredit && !$isInRecycle) {/* 删除积分 */
				$creditset = $this->getcreditset($read['fid']);
				$credit->addLog('topic_Delete', $creditset['Delete'], array(
						'uid' => $read['authorid'],
						'username' => $read['author'],
						'ip' => $onlineip,
						'fname' => strip_tags($this->getForumInfo($read['fid'], 'name')),
						'operator' => $windid
					)
				);
				$credit->sets($read['authorid'], $creditset['Delete'], false);
				$msg_delrvrc = abs($creditset['Delete']['rvrc']);
				$msg_delmoney = abs($creditset['Delete']['money']);
			}

			/*记录日志 */
			$logdb[] = array(
				'type' => 'delete',
				'username1' => $read['author'],
				'username2' => $windid,
				'field1' => $read['fid'],
				'field2' => $read['tid'],
				'field3' => '',
				'descrip' => 'del_descrip',
				'timestamp' => $timestamp,
				'ip' => $onlineip,
				'affect'    => "{$GLOBALS[db_rvrcname]}：-{$msg_delrvrc}，{$GLOBALS[db_moneyname]}：-{$msg_delmoney}",
				'tid'		=> $read['tid'],
				'subject'	=> substrs($read['subject'],28),
				'reason' => $extra['reason']
			);

			if ($read['modelid']) {
				$modeldb[$read['modelid']][] = $read['tid'];
			} elseif ($read['special'] == 8) {//活动
				$activityDb[] = $read['tid'];
			} elseif ($read['special'] > 20) {
				$pcdb[$read['special']][] = $read['tid'];
			} elseif ($read['special'] == 6) {
				$robbuildTids[] = $read['tid'];
			}
			if ($read['special'] > 0 && $read['special'] < 5) {
				$specialdb[$read['special']][] = $read['tid'];
			}
			if ($read['tpcstatus'] && getstatus($read['tpcstatus'], 1)) {
				$cydb[] = $read['tid'];
			}
			$htmurl = R_P . $db_readdir . '/' . $read['fid'] . '/' . date('ym',$read['postdate']) . '/' . $read['tid'] . '.html';
			if (file_exists($htmurl)) {
				P_unlink($htmurl);
			}
			if ($db_guestread) {
				clearguestcache($read['tid'], $read['replies']);
			}
			if ($recycle) {
				$recycledb[] = array('pid' => 0, 'tid' => $read['tid'], 'fid' => $read['fid'], 'deltime' => $GLOBALS['timestamp'], 'admin' => $GLOBALS['windid']);
			}
			$read['specialsort'] > 0 && $updatetop = 1;
			$read['specialsort'] == PW_THREADSPECIALSORT_KMD && $kmdTids[] = $read['tid'];
			$ttable_a[GetTtable($read['tid'])] = 1;
			$ptable_a[$read['ptable']] = 1;
			$fids[$read['fid']]['tids'][] = $read['tid'];
			!$isInRecycle && $deluids[$read['authorid']]++;
			if ($read['fid'] > 0 && $read['ifcheck'] < 2) {
				$fids[$read['fid']]['replies'] += $read['replies'];
				if ($read['ifcheck']) {
					$fids[$read['fid']]['topic']++;
					$delutids[$read['authorid']][] = $read['tid'];
				}
			}
			$tids[] = $read['tid'];
		}
		if (!$tids) {
			return true;
		}
		require_once(R_P.'require/updateforum.php');
		$deltids = S::sqlImplode($tids);
		
		/*写操作日志 */
		require_once (R_P . 'require/writelog.php');
		foreach ($logdb as $key => $val) {
			writelog($val);
		}
			
		if ($cydb) {
			$this->_reCountColony($cydb);
		}
		if ($recycle) {
			//$this->db->update("UPDATE pw_threads SET fid='0',ifcheck='1',topped='0' WHERE tid IN($deltids)");
			pwQuery::update('pw_threads', 'tid IN (:tid)' , array($tids), array('fid'=>'0','ifcheck'=>'1','topped'=>'0'));//这里的$tid是还未过滤的$deltids
			foreach ($ptable_a as $key => $val) {
				$pw_posts = GetPtable($key);
				//$this->db->update("UPDATE $pw_posts SET fid='0' WHERE tid IN($deltids)");
				pwQuery::update($pw_posts, 'tid IN(:tid)', array($tids), array('fid' => '0'));
			}
			if ($recycledb) {
				$this->db->update("REPLACE INTO pw_recycle (pid,tid,fid,deltime,admin) VALUES " . S::sqlMulti($recycledb));
			}
			// ThreadManager reflesh memcache
			/*
			$threadlist = L::loadClass("threadlist", 'forum');
			foreach ($fids as $fid => $value) {
				$threadlist->refreshThreadIdsByForumId($fid);
			}
			
			$threads = L::loadClass('Threads', 'forum');
			$threads->delThreads($tids);
			*/	
			Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>array_keys($fids)));
			Perf::gatherInfo('changeThreadWithThreadIds', array('tid'=>$tids));		
			
			if ($modeldb) {
				$this->_RecycleModelTopic($modeldb);
			}
			if ($activityDb) {
				$this->_RecycleActivityTopic($activityDb);
			}
			if ($pcdb) {
				$this->_RecyclePcTopic($pcdb);
			}
		} else {
			//* $threadManager = L::loadClass("threadmanager", 'forum'); /* @var $threadManager PW_ThreadManager */
			$threadService = L::loadclass('threads', 'forum');
			foreach ($fids as $fid => $value) {
				//* $threadManager->deleteByThreadIds($fid, $value['tids']);
				$threadService->deleteByThreadIds($value['tids']);
				Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));
			}
			foreach ($ttable_a as $pw_tmsgs => $val) {
				//* $this->db->update("DELETE FROM $pw_tmsgs WHERE tid IN($deltids)");
				pwQuery::delete($pw_tmsgs, 'tid IN(:tid)', array($tids));
			}
			
			foreach ($ptable_a as $key => $val) {
				$pw_posts = GetPtable($key);
				//$this->db->update("DELETE FROM $pw_posts WHERE tid IN($deltids)");
				pwQuery::delete($pw_posts, 'tid IN(:tid)', array($tids));
			}
			if ($specialdb) {
				$this->_delSpecialTopic($specialdb);
			}
			if ($modeldb) {
				$this->_delModelTopic($modeldb);
			}
			if ($activityDb) {
				$this->_delActivityTopic($activityDb);
			}
			if ($pcdb) {
				$this->_delPcTopic($pcdb);
			}
			if ($robbuildTids) {
				$robbuildService = L::loadClass("robbuild", 'forum');
				$robbuildService->deleteByTids($robbuildTids);
			}
			if ($cydb) {
				$this->db->update("DELETE FROM pw_argument WHERE tid IN(" . S::sqlImplode($cydb) . ')');
			}
			delete_tag($deltids);		
		}
		/* 删除微博 */
		$weiboService = L::loadClass('weibo','sns'); /* @var $weiboService PW_Weibo */
		$weibos = $weiboService->getWeibosByObjectIdsAndType($tids, 'article');
		if ($weibos) {
			$mids = array();
			foreach($weibos as $key => $weibo){
				$mids[] = $weibo['mid'];
			}
			$weiboService->deleteWeibos($mids);
		}
		
		/* delete cache*/
		if ($db_ifpwcache ^ 1) {
			$db->update("DELETE FROM pw_elements WHERE type !='usersort' AND id IN(" . S::sqlImplode($tids) . ')');
		}
		//* P_unlink(D_P . 'data/bbscache/c_cache.php');
		pwCache::deleteData(D_P . 'data/bbscache/c_cache.php');
				
		/* 扣除积分 */
		$delCredit && $credit->runsql();		
		
		//更新置顶帖表
		$this->db->update("DELETE FROM pw_poststopped WHERE tid IN ($deltids) AND pid = '0' AND fid != '0' ");
		
		if ($delutids) {
			$userCache = L::loadClass('Usercache', 'user');
			$userCache->delete(array_keys($delutids), array('article', 'cardtopic', 'weibo'));
		}
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		foreach ($deluids as $key => $value) {
			$userService->updateByIncrement($key, array(), array('postnum' => -$value));
		}
		$pw_attachs = L::loadDB('attachs', 'forum');
		if ($attachdb = $pw_attachs->getByTid($tids)) {
			delete_att($attachdb, !$recycle);
			require_once(R_P.'require/functions.php');
			pwFtpClose($GLOBALS['ftp']);
		}
		if ($updatetop) {
			if ($kmdTids){
				$kmdService = L::loadClass('kmdservice', 'forum');
				foreach ($kmdTids as $tid){
					$kmdService->initThreadInfoByTid($tid);
				}
			}
			updatetop();
		}
		foreach ($fids as $fid => $value) {
			updateForumCount($fid, -$value['topic'], -$value['replies']);
		}
	}

	function _delSpecialTopic($specialdb) {
		if (isset($specialdb[1])) {
			$pollids = S::sqlImplode($specialdb[1]);
			$this->db->update("DELETE FROM pw_polls WHERE tid IN($pollids)");
		}
		if (isset($specialdb[2])) {
			$actids = S::sqlImplode($specialdb[2]);
			$this->db->update("DELETE FROM pw_activity WHERE tid IN($actids)");
			$this->db->update("DELETE FROM pw_actmember WHERE actid IN($actids)");
		}
		if (isset($specialdb[3])) {
			$rewids = S::sqlImplode($specialdb[3]);
			$this->db->update("DELETE FROM pw_reward WHERE tid IN($rewids)");
		}
		if (isset($specialdb[4])) {
			$tradeids = S::sqlImplode($specialdb[4]);
			$this->db->update("DELETE FROM pw_trade WHERE tid IN($tradeids)");
		}
	}

	function _delModelTopic($modeldb){
		foreach ($modeldb as $key => $value) {
			$modelids = S::sqlImplode($value);
			$pw_topicvalue = GetTopcitable($key);
			$this->db->update("DELETE FROM $pw_topicvalue WHERE tid IN($modelids)");
		}
	}

	function _delPcTopic($pcdb){
		foreach ($pcdb as $key => $value) {
			$pcids =  S::sqlImplode($value);
			$key = $key > 20 ? $key - 20 : 0;
			$key = (int)$key;
			$pcvaluetable = GetPcatetable($key);
			$this->db->update("DELETE FROM $pcvaluetable WHERE tid IN($pcids)");
		}
	}

	/**
	 * 将活动帖子的数据删除
	 * @param array $activityDb 帖子数据，形入array(tid, tid)
	 */
	function _delActivityTopic ($activityDb) {
		$defaultValueTableName = getActivityValueTableNameByActmid();
		$newActivityDb = array();
		$query = $this->db->query("SELECT actmid,tid FROM $defaultValueTableName WHERE tid IN(".S::sqlImplode($activityDb).")");
		while ($rt = $this->db->fetch_array($query)) {
			$newActivityDb[$rt['actmid']][] = $rt['tid'];
		}
		
		/*帖子被删除费用日志更新*/
		L::loadClass('ActivityForBbs', 'activity', false);
		$postActForBbs = new PW_ActivityForBbs($data);

		$data = array();
		/*帖子被删除费用日志更新*/
		foreach ($newActivityDb as $key => $value) {
			$tids = S::sqlImplode($value);
			$userDefinedValueTableName = getActivityValueTableNameByActmid($key, 1, 1);
			$this->db->update("DELETE FROM $defaultValueTableName WHERE tid IN($tids)");
			$this->db->update("DELETE FROM $userDefinedValueTableName WHERE tid IN($tids)");
			$this->db->update("DELETE FROM pw_activitymembers WHERE tid IN($tids)");
			/*帖子被删除费用日志更新*/
			$postActForBbs->UpdatePayLog($value,0,4);
			/*帖子被删除费用日志更新*/
			/*帖子被删除发送站内信*/
			$postActForBbs->activityDelSendmsg($value);
			/*帖子被删除发送站内信*/
		}
	}

	function _RecycleModelTopic($modeldb){
		foreach ($modeldb as $key => $value) {
			$modelids = S::sqlImplode($value);
			$pw_topicvalue = GetTopcitable($key);
			$this->db->update("UPDATE $pw_topicvalue SET ifrecycle='1' WHERE tid IN($modelids)");
		}
	}

	function _RecyclePcTopic($pcdb){
		foreach ($pcdb as $key => $value) {
			$pcids =  S::sqlImplode($value);
			$key = $key > 20 ? $key - 20 : 0;
			$pcvaluetable = GetPcatetable($key);
			$this->db->update("UPDATE $pcvaluetable SET ifrecycle='1' WHERE tid IN($pcids)");
		}
	}

	/**
	 * 将活动帖子的数据放入回收站
	 * @param array $activityDb 帖子数据，形入array(tid, tid)
	 */
	function _RecycleActivityTopic($activityDb) {
		
		$defaultValueTableName = getActivityValueTableNameByActmid();
		$newActivityDb = array();
		$query = $this->db->query("SELECT actmid,tid FROM $defaultValueTableName WHERE tid IN(".S::sqlImplode($activityDb).")");
		while ($rt = $this->db->fetch_array($query)) {
			$newActivityDb[$rt['actmid']][] = $rt['tid'];
		}

		/*帖子被删除费用日志更新*/
		L::loadClass('ActivityForBbs', 'activity', false);
		$postActForBbs = new PW_ActivityForBbs($data);

		$data = array();
		/*帖子被删除费用日志更新*/
		foreach ($newActivityDb as $key => $value) {
			$tids = S::sqlImplode($value);
			$userDefinedValueTableName = getActivityValueTableNameByActmid($key, 1, 1);
			$this->db->update("UPDATE $defaultValueTableName SET ifrecycle='1' WHERE tid IN($tids)");
			$this->db->update("UPDATE $userDefinedValueTableName SET ifrecycle='1' WHERE tid IN($tids)");
			/*帖子被删除费用日志更新*/
			$postActForBbs->UpdatePayLog($value,0,4);
			/*帖子被删除费用日志更新*/
			/*帖子被删除发送站内信*/
			$postActForBbs->activityDelSendmsg($value);
			/*帖子被删除发送站内信*/
		}
	}
	
	/************************/
	/****  回复操作接口  ****/
	/************************/

	function getReplyDb($sqlwhere) {
		global $db_plist;
		$ptable_a = array(0);
		if ($db_plist && count($db_plist) > 1) {
			foreach ($db_plist as $key => $val) {
				if ($key == 0) continue;
				$ptable_a[] = $key;
			}
		}
		$replydb = array();
		foreach ($ptable_a as $key => $value) {
			$pw_post = GetPtable($value);
			$query = $this->db->query("SELECT pid,fid,tid,aid,author,authorid,postdate,subject,content,anonymous,ifcheck FROM $pw_post WHERE $sqlwhere");
			while ($rt = $this->db->fetch_array($query)) {
				$rt['ptable'] = $value;
				$replydb[] = $rt;
			}
		}
		return $replydb;
	}

	function delReplyByPids($pids, $recycle = false, $delCredit = true) {
		if (!$sqlby = $this->sqlFormatByIds($pids)) {
			return;
		}
		$replydb = $this->getReplyDb("pid $sqlby");
		$this->delReply($replydb, $recycle, $delCredit);
	}

	function delReplyByUids($uids, $recycle = false, $delCredit = true) {
		if (!$sqlby = $this->sqlFormatByIds($uids)) {
			return;
		}
		$replydb = $this->getReplyDb("authorid $sqlby");
		$this->delReply($replydb, $recycle, $delCredit);
	}

	function resetReplayToppedFloor($replydb='', $delpids='', $ptable='', $tpcstatus){
		$pids = $tids = array();
		if ($replydb) {
			foreach ($replydb as $key => $value) {
				if ($value['pid'] > 0) {
					$pids[] = $value['pid'];
					$tids[] = $value['tid'];
				}
			}
		}
		if (!empty($pids)) {
			$result = $this->db->update("DELETE FROM pw_poststopped WHERE pid IN (". S::sqlImplode($pids) .")");
			$tids = array_unique($tids);
			if ($result) {
				foreach ($tids as $key => $value) {
					$count = $this->db->get_value("SELECT COUNT(*) FROM pw_poststopped WHERE tid = ".S::sqlEscape($value)." AND fid = '0' AND pid != '0'");
					//$this->db->update("UPDATE pw_threads SET topreplays = ".S::sqlEscape($count,false)."WHERE tid = ".S::sqlEscape($value));
					pwQuery::update('pw_threads', 'tid = :tid' , array($value), array('topreplays'=>$count));
				}
			}
		}
		if ($delpids && $ptable) {
			$tids = array();
			$query = $this->db->query("SELECT tid FROM $ptable WHERE pid IN (". $delpids .") ");
			while ($rt = $this->db->fetch_array($query)) {
				$tids[]  = $rt['tid'];
			}
			$tids = array_unique($tids);
		}
		if (!empty($tids) && !getstatus($tpcstatus, 2)) {
			$query = $this->db->query("SELECT * FROM pw_poststopped WHERE tid IN (". S::sqlImplode($tids) .") 
						AND fid = '0' AND pid != '0' ");
			while ($tr = $this->db->fetch_array($query)) {
				$ptable = GetPtable('N',$tr['tid']);
				$this->db->update("UPDATE pw_poststopped SET floor = (
					SELECT COUNT(*) FROM $ptable p WHERE p.tid = ".S::sqlEscape($tr['tid'])."
					AND p.pid <= ". S::sqlEscape($tr['pid']) ." AND p.pid != '0' AND p.ifcheck = '1' ) WHERE pid = " . S::sqlEscape($tr['pid']));
			}
		}
	}

	function delReply($replydb, $recycle = false, $delCredit = true, $recount = false, $extra = array()) {
		global $credit,$windid,$timestamp,$onlineip,$db_creditset;
		!$credit &&	require_once(R_P.'require/credit.php');
		$tids = $pids = $_tids = $_pids = $ptable_a = $recycledb = $delfids = $deltids = $deluids = $attachdb = $deltpc = array();
	
		foreach ($replydb as $key => $reply) {
			$tids[$reply['tid']] = 1;
			if ($reply['pid'] == 'tpc') {
				$reply['pid'] = 0;
			}
			if ($recycle) {
				$recycledb[] = array('pid' => $reply['pid'], 'tid' => $reply['tid'], 'fid' => $reply['fid'], 'deltime' => $timestamp, 'admin' => $windid);
			}
			if ($reply['pid'] > 0) {
				/*回复*/
				$isInRecycle = ($reply['fid'] == 0 && $reply['tid'] == 0);
				if ($reply['aid']) {
					$_tids[$reply['tid']] = $reply['tid'];
					$_pids[$reply['pid']] = $reply['pid'];
				}
				if (!$isInRecycle) {
					$deluids[$reply['authorid']]++;
					if ($reply['ifcheck']) {
						$delfids[$reply['fid']]['replies']++;
						$deltids[$reply['tid']]++;
					}
				}
				$ptable_a[$reply['ptable']] = 1;
				$pids[] = $reply['pid'];
				$creditType = 'Deleterp';
				$logType = 'delrp_descrip';
			} else {
				/* 主题 */
				$isInRecycle = ($reply['fid'] == 0 && $reply['ifcheck'] == 1);
				!$isInRecycle && $deluids[$reply['authorid']]++;
				$deltpc[] = $reply['tid'];
				$creditType = 'Delete';
				$logType = 'del_descrip';
			}
			
			$msg_delrvrc = $msg_delmoney = 0;
			if ($delCredit && !$isInRecycle) {
				$creditset = $this->getcreditset($reply['fid']);
				$credit->addLog("topic_$creditType", $creditset[$creditType], array(
					'uid'		=> $reply['authorid'],
					'username'	=> $reply['author'],
					'ip'		=> $onlineip,
					'fname'		=> strip_tags($this->getForumInfo($reply['fid'], 'name')),
					'operator'	=> $windid
				));
				$credit->sets($reply['authorid'], $creditset[$creditType], false);
				$msg_delrvrc = abs($creditset[$creditType]['rvrc']);
				$msg_delmoney = abs($creditset[$creditType]['money']);
			}

			/*操作日志 */
			$logdb[] = array(
				'type'      => 'delete',
				'username1' => $reply['author'],
				'username2' => $windid,
				'field1'    => $reply['fid'],
				'field2'    => '',
				'field3'    => '',
				'descrip'   => $logType,
				'timestamp' => $timestamp,
				'ip'        => $onlineip,
				'tid'		=> $reply['tid'],
				'subject'	=> substrs($reply['subject'] ? $reply['subject'] : $reply['content'], 28),
				'affect'	=> "{$GLOBALS[db_rvrcname]}：-{$msg_delrvrc}，{$GLOBALS[db_moneyname]}：-{$msg_delmoney}",
				'reason' 	=> $extra['reason']
			);
		}
		if (!$tids) {
			return true;
		}
		require_once(R_P.'require/updateforum.php');
		$delpids = S::sqlImplode($pids);
		if ($recycle) {
			foreach ($ptable_a as $key => $val) {
				$pw_posts = GetPtable($key);
				//$this->db->update("UPDATE $pw_posts SET tid='0',fid='0' WHERE pid IN($delpids)");
				pwQuery::update($pw_posts,'pid IN(:pid)', array($pids), array('tid' => '0', 'fid' => '0'));
			}
			if ($recycledb) {
				$this->db->update("REPLACE INTO pw_recycle (pid,tid,fid,deltime,admin) VALUES " . S::sqlMulti($recycledb));
			}
		} else {
			foreach ($ptable_a as $key => $val) {
				$pw_posts = GetPtable($key);
				//$this->db->update("DELETE FROM $pw_posts WHERE pid IN($delpids)");
				pwQuery::delete($pw_posts, 'pid IN(:pid)', array($pids));
			}
		}
		if ($delpids) {
			$this->resetReplayToppedFloor($replydb,'','',$extra['tpcstatus']);
		}
		/*前台删主题，默认将其设为屏蔽*/
		if ($deltpc) {
			//$this->db->update("UPDATE pw_threads SET ifshield='2' WHERE tid IN (" . S::sqlImplode($deltpc) . ')');
			pwQuery::update('pw_threads', 'tid IN (:tid)' , array($deltpc), array('ifshield'=>2));
			$pw_attachs = L::loadDB('attachs', 'forum');
			$attachdb += $pw_attachs->getByTid($deltpc, 0);
			!$recycle && delete_tag(S::sqlImplode($deltpc));
			/* 删除微博 */
			$weiboService = L::loadClass('weibo','sns'); /* @var $weiboService PW_Weibo */
			$weibos = $weiboService->getWeibosByObjectIdsAndType($deltpc, 'article');
			if ($weibos) {
				$mids = array();
				foreach($weibos as $key => $weibo){
					$mids[] = $weibo['mid'];
				}
				$weiboService->deleteWeibos($mids);
			}
		}
		if ($_tids) {
			$pw_attachs = L::loadDB('attachs', 'forum');
			$attachdb += $pw_attachs->getByTid($_tids, $_pids);
		}
		if ($attachdb) {
			delete_att($attachdb, !$recycle);
			require_once(R_P.'require/functions.php');
			pwFtpClose($GLOBALS['ftp']);
		}
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		foreach ($deluids as $uid => $value) {
			$userService->updateByIncrement($uid, array(), array('postnum' => -$value));
		}
		if ($deltopic = $this->delReplyTopic(array_keys($tids), $deltpc, $recount, $recycle)) {
			foreach ($deltopic as $fid => $value) {
				$delfids[$fid]['topic'] = $value;
			}
		}
		if ($delfids) {
			//* $threadlist = L::loadClass("threadlist", 'forum');
			foreach ($delfids as $fid => $value) {
				//* $threadlist->refreshThreadIdsByForumId($fid);
				Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));
				updateForumCount($fid, -$value['topic'], -$value['replies']);
			}
		}
		if ($deltids && !$recount) {
			foreach ($deltids as $tid => $value) {
				//$this->db->update("UPDATE pw_threads SET replies=replies-" . S::sqlEscape($value) . " WHERE tid=" . S::sqlEscape($tid));
				$this->db->update(pwQuery::buildClause("UPDATE :pw_table SET replies=replies-:replies WHERE tid=:tid", array('pw_threads', $value, $tid)));
			}
		}
		
		/*写操作日志 */
		require_once (R_P . 'require/writelog.php');
		foreach($logdb as $log){
			writelog($log);
		}
		
		/*扣除积分*/
		$credit->runsql();
				
		return !empty($deltopic);
	}

	function delReplyTopic($tids, $deltpc, $recount, $recycle = false) {
		if (!$tids) {
			return array();
		}
		global $db_readdir,$db_guestread;
		$db_guestread && require_once(R_P.'require/guestfunc.php');
		$deltopic = array();
		$query = $this->db->query("SELECT tid,fid,postdate,lastpost,author,replies,anonymous,ptable,locked FROM pw_threads WHERE tid IN(" . S::sqlImplode($tids) . ")");
		while ($read = $this->db->fetch_array($query)) {
			$htmurl = $db_readdir.'/'.$read['fid'].'/'.date('ym',$read['postdate']).'/'.$read['tid'].'.html';
			if (file_exists(R_P . $htmurl)) {
				P_unlink(R_P . $htmurl);
			}
			if ($db_guestread) {
				clearguestcache($read['tid'], $read['replies']);
			}
			if ($recount) {
				if ($ret = $this->recountTopic($read, in_array($read['tid'], $deltpc), $recycle)) {
					$deltopic[$read['fid']] += 1;
				}
			}
		}
		//* $threads = L::loadClass('Threads', 'forum');
		//* $threads->delThreads($tids);	
		Perf::gatherInfo('changeThreadWithThreadIds', array('tid'=>$tids));	

		return $deltopic;
	}

	function recountTopic($read, $ifdel, $recycle) {
		global $db_anonymousname, $timestamp;
		$ret = 0;
		$tid = $read['tid'];
		$pw_posts = GetPtable($read['ptable']);
		$replies = $this->db->get_value("SELECT COUNT(*) AS replies FROM $pw_posts WHERE tid='$tid' AND ifcheck='1'");
		if (!$replies) {
			$read['anonymous'] && $read['author'] = $db_anonymousname;
			if ($ifdel) {
				if ($recycle) {
					//$this->db->update("UPDATE pw_threads SET fid='0',ifshield='0' WHERE tid='$tid'");
					pwQuery::update('pw_threads', 'tid = :tid' , array($tid), array('fid'=>0,'ifshield'=>0));
				} else {
					//* $threadManager = L::loadClass("threadmanager", 'forum');
					//* $threadManager->deleteByThreadId($read['fid'], $tid);
					$threadService = L::loadclass('threads', 'forum');
					$threadService->deleteByThreadId($tid);	
					Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$read['fid']));				
					$pw_tmsgs = GetTtable($tid);
					//* $this->db->update("DELETE FROM $pw_tmsgs WHERE tid='$tid'");
					pwQuery::delete($pw_tmsgs, 'tid=:tid', array($tid));
				}
				$ret = 1;
			} else {
				$pwSQL = array('replies' => 0, 'lastposter' => $read['author']);
				!($read['lastpost'] > $timestamp || $read['locked'] > 2) && $pwSQL['lastpost'] = $read['postdate'];
				//$this->db->update("UPDATE pw_threads SET " . S::sqlSingle($pwSQL) . " WHERE tid=" . S::sqlEscape($tid));
				pwQuery::update('pw_threads', 'tid = :tid' , array($tid), $pwSQL);
			}
		} else {
			$pt = $this->db->get_one("SELECT postdate,author,anonymous FROM $pw_posts WHERE tid='$tid' ORDER BY postdate DESC LIMIT 1");
			$pt['anonymous'] && $pt['author'] = $db_anonymousname;
			$pwSQL = array('replies' => $replies, 'lastposter' => $pt['author']);
			!($read['lastpost'] > $timestamp || $read['locked'] > 2) && $pwSQL['lastpost'] = $pt['postdate'];
			//$this->db->update("UPDATE pw_threads SET " . S::sqlSingle($pwSQL) . " WHERE tid=" . S::sqlEscape($tid));
			pwQuery::update('pw_threads', 'tid = :tid' , array($tid), $pwSQL);
		}
		return $ret;
	}

	function _reCountColony($tids) {
		$query = $this->db->query("SELECT COUNT(*) AS tnum, SUM(b.replies+1) AS pnum, a.cyid FROM pw_argument a LEFT JOIN pw_threads b ON a.tid=b.tid WHERE a.tid IN(" . S::sqlImplode($tids) . ") AND b.fid>0 AND b.ifcheck='1' GROUP BY a.cyid");
		while ($rt = $this->db->fetch_array($query)) {
			//* $this->db->update("UPDATE pw_colonys SET tnum=tnum-" . S::sqlEscape($rt['tnum']) . ',pnum=pnum-' . S::sqlEscape($rt['pnum']) . ' WHERE id=' . S::sqlEscape($rt['cyid']));
			$this->db->update(pwQuery::buildClause("UPDATE :pw_table SET tnum=tnum-:tnum,pnum=pnum-:pnum WHERE id=:id", array('pw_colonys', $rt['tnum'], $rt['pnum'], $rt['cyid'])));
		}
	}
}
?>