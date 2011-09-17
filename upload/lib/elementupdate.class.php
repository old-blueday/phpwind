<?php
!function_exists('readover') && exit('Forbidden');

/**
 * ElementUpdate class
 *
 * @copyright phpwind
 * @author xiaolang
 * @package Element
 */
class ElementUpdate {
	var $db;
	var $ifcache;
	var $cachenum;
	var $mark;
	var $updatelist;
	var $updatetype;
	var $judge;
	var $special;

	function ElementUpdate($mark = null) {
		global $db, $db_ifpwcache, $db_cachenum;

		$this->db = $db;
		$this->ifcache = $db_ifpwcache;
		$this->cachenum = $db_cachenum ? $db_cachenum : 20;
		$this->updatelist = array();
		$this->updatetype = array();
		$this->mark = $mark;
	}
	
	function _inTidBlackList($tid) {
		global $db_tidblacklist;
		return ($db_tidblacklist && strpos(",$db_tidblacklist,", ",$tid,") !== false);
	}

	function _inUidBlackList($uid) {
		global $db_uidblacklist;
		return ($db_uidblacklist && strpos(",$db_uidblacklist,", ",$uid,") !== false);
	}
	/**
	 * user sort update
	 *
	 * @param array $winddb
	 */
	function userSortUpdate($winddb) {
		global $timestamp, $tdtime, $montime, $_CREDITDB;
		if ($this->_inUidBlackList($winddb['uid'])) {
			return false;
		}
		$usersort_judge = array();
		//* include pwCache::getPath(D_P . 'data/bbscache/usersort_judge.php');
		extract(pwCache::getData(D_P . 'data/bbscache/usersort_judge.php', false));
		$winddb['lastpost'] < $tdtime && $winddb['todaypost'] = 0;
		$winddb['lastpost'] < $montime && $winddb['monthpost'] = 0;
		$sorttype = array(
			'money',
			'rvrc',
			'credit',
			'currency',
			'todaypost',
			'monthpost',
			'postnum',
			'monoltime',
			'onlinetime',
			'digests',
			'f_num'
		);
		if ($_CREDITDB) {
			$query = $this->db->query("SELECT cid,value FROM pw_membercredit WHERE uid=" . S::sqlEscape($winddb['uid']));
			while ($rt = $this->db->fetch_array($query)) {
				if (!$rt['value']) continue;
				$winddb[$rt['cid']] = $rt['value'];
			}
			foreach ($_CREDITDB as $key => $val) {
				is_numeric($key) && $sorttype[] = $key;
			}
		}
		$change = $marks = array();
		foreach ($sorttype as $value) {
			if (in_array($value,$sorttype) && $winddb[$value]>PW_OVERFLOW_NUM) {
				$this->_excuteOverflow($winddb['uid'],$value);
				$winddb[$value] = 0;
			}
			if ($winddb[$value] > $usersort_judge[$value]) {
				$marks[] = $value;
				if ($value == 'rvrc') {
					$winddb[$value] = floor($winddb[$value] / 10);
				} elseif ($value == 'onlinetime' || $value == 'monoltime') {
					$winddb[$value] = floor($winddb[$value] / 3600);
				}
				$change[] = array(
					'usersort',
					$value,
					$winddb['uid'],
					$winddb[$value],
					$winddb['username'],
					$timestamp
				);
			}
		}
		$rand_array = array(
			'todaypost',
			'monthpost',
			'monoltime'
		);
		$rand_key = array_rand($rand_array);
		$rand_mark = $rand_array[$rand_key];
		if (!in_array($rand_mark, $marks)) {
			$marks[] = $rand_mark;
		}
		if ($marks && $change) {
			$this->db->update("REPLACE INTO pw_elements(type,mark,id,value,addition,time) VALUES " . S::sqlMulti($change, false));
			$sortlist = array();
			$dellist = array();
			$query = $this->db->query("SELECT * FROM pw_elements WHERE type='usersort' AND mark IN (" . S::sqlImplode($marks) . ") ORDER BY mark,value DESC");
			while ($rt = $this->db->fetch_array($query)) {
				if (($rt['mark'] == 'todaypost' && $rt['time'] < $tdtime) || (in_array($rt['mark'], array(
					'monthpost',
					'monoltime'
				)) && $rt['time'] < $montime)) {
					$dellist[] = $rt['eid'];
					continue;
				}
				$sortlist[$rt['mark']][] = $rt;
			}
			$judge = $usersort_judge;
			foreach ($sortlist as $key => $value) {
				if (count($value) > $this->cachenum) {
					$tem = array_pop($value);
					$dellist[] = $tem['eid'];
				}
				if (count($value) == $this->cachenum) {
					$tem = end($value);
					$judge[$key] = $tem['value'];
				} else {
					$judge[$key] = '0';
				}
			}
			if ($dellist) {
				$this->db->update("DELETE FROM pw_elements WHERE eid IN (" . S::sqlImplode($dellist) . ")");
			}
			if ($judge != $usersort_judge) {
				pwCache::setData(D_P . 'data/bbscache/usersort_judge.php', "<?php\r\n\$usersort_judge=" . pw_var_export($judge) . ";\r\n?>");
			}
		}
	}
	/**
	 * 处理异常数据
	 * @param $uid
	 * @param $type
	 */
	function _excuteOverflow($uid,$type) {
		$userService = L::loadClass('userservice','user');
		return $userService->update($uid,array(),array($type=>0));
	}

	/**
	 * hot favor update
	 *
	 * @param int $tid
	 * @param int $fid
	 * @return
	 */
	function hotfavorUpdate($tid, $fid) {

		if (!($this->ifcache & 1024) || !$tid || !$fid) {
			return false;
		}
		$eid = $this->db->get_value("SELECT eid FROM pw_elements WHERE type='hotfavor' AND mark=" . S::sqlEscape($fid) . " AND id=" . S::sqlEscape($tid));

		if ($eid) {
			$this->db->update("UPDATE pw_elements SET value=value+1 WHERE eid=" . S::sqlEscape($eid));
		} else {
			$rt = $this->db->get_one("SELECT favors FROM pw_threads WHERE tid=" . S::sqlEscape($tid));
			$rs = $this->db->get_one("SELECT value,eid FROM pw_elements WHERE type='hotfavor' ORDER BY value ASC");

			if ($rt['favors'] > $rs['value']) {
				$this->db->update("DELETE FROM pw_elements WHERE eid=" . S::sqlEscape($rs['eid']));
				$favors = array(
					'id' => $tid,
					'mark' => $fid,
					'value' => $rt['favors'],
					'type' => 'hotfavor'
				);
				$this->db->update("REPLACE INTO pw_elements SET" . S::sqlSingle($favors, false));
			}
		}
		return true;
	}

	/**
	 * new favor update
	 *
	 * @param int $tid
	 * @param int $fid
	 * @return
	 */
	function newfavorUpdate($tid, $fid) {
		global $timestamp, $winduid, $windid;
		if (!$tid || !$fid) {
			return false;
		}

		$eid = $this->db->get_value("SELECT eid FROM pw_elements WHERE type='newfavor' AND mark=" . S::sqlEscape($fid) . " AND id=" . S::sqlEscape($tid));

		if ($eid) {
			$this->db->update("UPDATE pw_elements SET value=value+1 WHERE eid=" . S::sqlEscape($eid));
		} else {
			$count = $this->db->get_value("SELECT COUNT(*) as count FROM pw_elements WHERE type='newfavor' AND mark=" . S::sqlEscape($fid));
			$rt = $this->db->get_one("SELECT favors FROM pw_threads WHERE tid=" . S::sqlEscape($tid));

			if ($count < 20) {
				$favors = array(
					'id' => $tid,
					'mark' => $fid,
					'value' => $rt['favors'],
					'type' => 'newfavor',
					'addition' => $winduid . '|' . $windid,
					'time' => $timestamp
				);
				$this->db->update("REPLACE INTO pw_elements SET" . S::sqlSingle($favors, false));
			} else {
				$rs = $this->db->get_one("SELECT eid FROM pw_elements WHERE type='newfavor' AND mark=" . S::sqlEscape($fid) . " ORDER BY time ASC");
				$favors = array(
					'id' => $tid,
					'mark' => $fid,
					'value' => $rt['favors'],
					'type' => 'newfavor',
					'addition' => $winduid . '|' . $windid,
					'time' => $timestamp
				);
				$this->db->update("UPDATE pw_elements SET" . S::sqlSingle($favors, false) . " WHERE eid=" . S::sqlEscape($rs['eid']));
			}
		}
		return true;
	}

	/**
	 * hits sort update
	 *
	 * @param array $threaddb
	 * @param int $fid
	 * @return
	 */
	function hitSortUpdate($threaddb, $fid) {
		if (!($this->ifcache & 112)) {
			return false;
		}
		if (!$this->judge['hitsort']) {
			$hitsort_judge = array();
			//* include pwCache::getPath(D_P . 'data/bbscache/hitsort_judge.php');
			extract(pwCache::getData(D_P . 'data/bbscache/hitsort_judge.php', false));
			$this->judge['hitsort'] = $hitsort_judge;
		}
		foreach ($threaddb as $thread) {
			if ($this->_inTidBlackList($thread['tid'])) {
				continue;
			}
			$thread['postdate'] = PwStrtoTime($thread['postdate']);
			if ($this->ifcache & 16) {
				if ($thread['hits'] > $hitsort_judge['hitsort'][$fid]) {
					$this->updatelist[] = array(
						'hitsort',
						$fid,
						$thread['tid'],
						$thread['hits'],
						'',
						0
					);
					$this->updatetype['hitsort'] = 1;
				}
			}
			if ($this->ifcache & 32 && $thread['postdate'] > 24 * 3600) {
				if ($thread['hits'] > $hitsort_judge['hitsortday'][$fid]) {
					$this->updatelist[] = array(
						'hitsortday',
						$fid,
						$thread['tid'],
						$thread['hits'],
						$thread['postdate'],
						0
					);
					$this->updatetype['hitsortday'] = 1;
				}
			}
			if ($this->ifcache & 64 && $thread['postdate'] > 7 * 24 * 3600) {
				if ($thread['hits'] > $hitsort_judge['hitsortweek'][$fid]) {
					$this->updatelist[] = array(
						'hitsortweek',
						$fid,
						$thread['tid'],
						$thread['hits'],
						$thread['postdate'],
						0
					);
					$this->updatetype['hitsortweek'] = 1;
				}
			}
		}
		return true;
	}
	/**
	 * reply sort update
	 *
	 * @param int $tid
	 * @param int $fid
	 * @param string $postdate
	 * @param int $replies
	 * @return
	 */
	function replySortUpdate($tid, $fid, $postdate, $replies) {
		global $timestamp;
		if (!($this->ifcache & 14) || $this->_inTidBlackList($tid)) {
			return false;
		}
		$special = (int) $this->special;
		if (!$this->judge['replysort']) {
			$replysort_judge = array();
			//* include pwCache::getPath(S::escapePath(D_P . 'data/bbscache/replysort_judge_' . $special . '.php'));
			extract(pwCache::getData(S::escapePath(D_P . 'data/bbscache/replysort_judge_' . $special . '.php'), false));
			$this->judge['replysort'] = $replysort_judge;
		}

		if ($this->ifcache & 2) {
			if ($replies > $replysort_judge['replysort'][$fid]) {
				$this->updatelist[] = array(
					'replysort',
					$fid,
					$tid,
					$replies,
					'',
					$special
				);
				$this->updatetype['replysort'] = 1;
			}
		}
		if ($this->ifcache & 4 && $postdate > $timestamp - 24 * 3600) {
			if ($replies > $this->judge['replysort']['replysortday'][$fid]) {
				$this->updatelist[] = array(
					'replysortday',
					$fid,
					$tid,
					$replies,
					$postdate,
					$special
				);
				$this->updatetype['replysortday'] = 1;
			}
		}

		if ($this->ifcache & 8 && $postdate > $timestamp - 7 * 24 * 3600) {
			if ($replies > $this->judge['replysort']['replysortweek'][$fid]) {
				$this->updatelist[] = array(
					'replysortweek',
					$fid,
					$tid,
					$replies,
					$postdate,
					$special
				);
				$this->updatetype['replysortweek'] = 1;
			}
		}
		return true;
	}
	/**
	 * new subject update
	 *
	 * @param int $tid
	 * @param int $fid
	 * @param string $postdate
	 * @return
	 */
	function newSubjectUpdate($tid, $fid, $postdate) {
		if (!($this->ifcache & 128) || $this->_inTidBlackList($tid)) {
			return false;
		}
		$this->updatelist[] = array(
			'newsubject',
			$fid,
			$tid,
			$postdate,
			'',
			'0'
		);
		$this->updatetype['newsubject'] = 1;
		return true;
	}
	
	/**
	 * open post
	 *
	 * @param int $fid
	 * @param int $tid
	 * @return
	 */
	function openThreadUpdate($fid, $tid) {
		global $timestamp;
		if (!($this->ifcache & 16384) || $this->_inTidBlackList($tid)) {
			return false;
		}
		$this->updatelist[] = array(
			'openthread',
			$fid,
			$tid,
			$timestamp,
			'',
			'0'
		);
		$this->updatetype['openthread'] = 1;
		return true;
	}
	/**
	 * last post user
	 *
	 * @param int $rid
	 * @param int $tid
	 * @param string $postdate
	 * @return
	 */
	function lastPostUpdate($uid, $tid) {
		global $timestamp;
		$this->updatelist[] = array(
			'lastpostuser',
			'tid',
			$uid,
			$timestamp,
			'',
			'0'
		);
		$this->updatetype['lastpostuser'] = 1;
		return true;
	}
	/**
	 * total fans user
	 *
	 * @param int $uid
	 * @return
	 */
	function totalFansUpdate($uid){
		if (!$uid) {
			return false;
		}

		$eid = $this->db->get_value("SELECT eid FROM pw_elements WHERE type='totalfans'  AND id=" . S::int($uid));

		if ($eid) {
			$this->db->update("UPDATE pw_elements SET value=value+1 WHERE eid=" . S::int($eid));
		} else {
			$fannum = $this->db->get_one("SELECT fans FROM pw_memberdata WHERE uid=" .S::int($uid));
			$this->updatelist[] = array(
				'totalfans',
				'fans',
				$uid,
				$fannum['fans'],
				'',
				'0'
			);
			$this->updatetype['totalfans'] =1;
		}
		return true;
	}
	/**
	 * today fans order
	 *
	 * @param int $uid
	 * @return
	 */
	function todayFansUpdate($uid){
		global $tdtime,$timestamp;
		if (!$uid) {
			return false;
		}

		$eid = $this->db->get_value("SELECT eid FROM pw_elements WHERE type='todayfans'  AND id=" . S::int($uid));
		if ($eid) {
			$this->db->update("UPDATE pw_elements SET value=value+1 WHERE eid=" . S::int($eid));
		} else {
			$count = $this->db->get_value("SELECT COUNT(*) AS count FROM pw_attention  WHERE friendid=" .S::int($uid)." AND joindate>".S::int($tdtime));
			$this->updatelist[] = array(
				'todayfans',
				'fans',
				$uid,
				$count,
				$timestamp,
				'0'
			);
			$this->updatetype['todayfans'] = 1;
		}
		return true;
	}
	/**
	 * user ugrade  
	 *
	 * @param int $uid
	 * @return
	 */
	function ugradeUserUpdate($uid){
		global $timestamp;

		$this->updatelist[] = array(
			'gradeuser',
			'time',
			$uid,
			$timestamp,
			'',
			'0'
		);
		$this->updatetype['gradeuser'] = 1;
		return true;
	}
	/**
	 * new reply update
	 *
	 * @param int $tid
	 * @param int $fid
	 * @param string $postdate
	 * @return
	 */
	function newReplyUpdate($tid, $fid, $postdate) {
		if (!($this->ifcache & 256) || $this->_inTidBlackList($tid)) {
			return false;
		}
		$this->updatelist[] = array(
			'newreply',
			$fid,
			$tid,
			$postdate,
			'',
			'0'
		);
		$this->updatetype['newreply'] = 1;
		return true;
	}
	/**
	 * new reply update
	 *
	 * @param int $tid
	 * @param int $fid
	 * @param string $postdate
	 * @return
	 */
	function newPicUpdate($aid, $fid, $tid, $addition, $ifthumb = 0, $atc_content) {
		if (!($this->ifcache & 512)) {
			return false;
		}
		$ifthumb = (int) $ifthumb;
		$atc_content = substrs(stripWindCode($atc_content), 30);
		$additions = array(
			'0' => $addition,
			'1' => $atc_content
		);
		$addition = addslashes(serialize($additions));
		$this->updatelist[] = array(
			'newpic',
			$fid,
			$tid,
			$aid,
			$addition,
			$ifthumb
		);
		$this->updatetype['newpic'] = 1;
		return true;
	}

	function setMark($mark) {
		$this->mark = $mark;
	}
	function setCacheNum($num) {
		$this->cachenum = $num;
	}
	function setUpdateList($updatelist) {
		$this->updatelist = $updatelist;
	}

	function setUpdateType($updatetype) {
		$this->updatetype = $updatetype;
	}

	function setJudge($key, $value) {
		$this->judge[$key] = $value;
	}
	/**
	 * update list
	 *
	 * @return
	 */
	function updateSQL() {
		global $timestamp;
		if (!$this->updatelist || !$this->updatetype || !$this->mark) return false;
		$special = (int) $this->special;
		$judges = array();
		$todaytime = $weektime = '';
		foreach ($this->updatetype as $key => $val) {
			if (in_array($key, array(
				'replysort',
				'replysortday',
				'replysortweek'
			)) && $this->judge['replysort']) {
				$judges['replysort'] = $this->judge['replysort'];
			}
			if (in_array($key, array(
				'hitsort',
				'hitsortday',
				'hitsortweek'
			)) && $this->judge['hitsort']) {
				$judges['hitsort'] = $this->judge['hitsort'];
			}
			if (strpos($key, 'day') && !$todaytime) {
				$todaytime = $timestamp - 24 * 3600;
			} elseif (strpos($key, 'week') && !$weektime) {
				$weektime = $timestamp - 7 * 24 * 3600;
			}
		}
		$this->db->update("REPLACE INTO pw_elements (type,mark,id,value,addition,special) VALUES " . S::sqlMulti($this->updatelist, false));
		$sortlist = array();
		$dellis = array();
		$orderIds=array();
		$query = $this->db->query("SELECT eid,type,id,value,addition FROM pw_elements WHERE type IN (" . S::sqlImplode(array_keys($this->updatetype)) . ") AND mark=" . S::sqlEscape($this->mark) . " AND special=" . S::sqlEscape($special) . " ORDER BY type,value DESC");
		while ($rt = $this->db->fetch_array($query)) {
			if (strpos($rt['type'], 'day') && $rt['addition'] && $rt['addition'] < $todaytime) {
				$dellist[] = $rt['eid'];
			} elseif (strpos($rt['type'], 'week') && $rt['addition'] && $rt['addition'] < $weektime) {
				$dellist[] = $rt['eid'];
			} else {
				$sortlist[$rt['type']][] = $rt;
			}
			if ($rt['type']=='todayfans'){/*记录删除前的排序*/
				$orderIds[]=$rt['id'];
			}
		}

		foreach ($sortlist as $key => $value) {
			if (count($value) > $this->cachenum) {
				$tem = array_slice($value, $this->cachenum);
				foreach ($tem as $val) {
					$dellist[] = $val['eid'];
				}
			}
			if (in_array($key, array(
				'replysort',
				'replysortday',
				'replysortweek'
			))) {
				$judgetype = 'replysort';
				array_splice($value, $this->cachenum);
			} elseif (in_array($key, array(
				'hitsort',
				'hitsortday',
				'hitsortweek'
			))) {
				$judgetype = 'hitsort';
				array_splice($value, $this->cachenum);
			} else {
				$judgetype = '';
			}
			if ($judgetype && count($value) == $this->cachenum) {
				$tem = end($value);
				$judges[$judgetype][$key][$this->mark] = $tem['value'];
			} else {
				$judges[$judgetype][$key][$this->mark] = '0';
			}
		}
		if ($dellist) {
			if (in_array('todayfans',array_keys($this->updatetype))){
				pwCache::setData(D_P . 'data/bbscache/yesterday_fans_brand.php', "<?php\r\n\$yesterdayfansbrand=" . pw_var_export($orderIds) . ";\r\n?>");
			}
			$this->db->update("DELETE FROM pw_elements WHERE eid IN (" . S::sqlImplode($dellist) . ")");
		}
		if ($judges) {
			foreach ($judges as $key => $value) {
				if ($key == 'replysort') {
					if ($value != $this->judge['replysort']) {
						pwCache::setData(D_P . 'data/bbscache/replysort_judge_' . $special . '.php', "<?php\r\n\$replysort_judge=" . pw_var_export($value) . ";\r\n?>");
					}
				} elseif ($key == 'hitsort') {
					pwCache::setData(D_P . 'data/bbscache/hitsort_judge.php', "<?php\r\n\$hitsort_judge=" . pw_var_export($value) . ";\r\n?>");
					touch(D_P.'data/bbscache/hitsort_judge.php');
				}
			}
		}
		return true;
	}
	/*
	function newFeedUpdate() {
		global $db_modes,$timestamp,$db;
		if ($db_modes['o']['ifopen'] == 0) {
			return false;
		}

		if ($timestamp - pwFilemtime(D_P.'data/bbscache/feed_cache.php') > 3600 || !file_exists(D_P.'data/bbscache/feed_cache.php')){
			$query = $db->query("SELECT * FROM pw_feed ORDER BY timestamp DESC LIMIT 10");
			while ($rt = $db->fetch_array($query)) {
				$feeddb[] = $rt;
			}
			if ($feeddb){
				writeover(D_P.'data/bbscache/feed_cache.php',"<?php\r\n\$feedcache=".pw_var_export($feeddb).";\r\n?>");
			}
		}
	}
*/
}
?>