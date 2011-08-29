<?php
!defined('P_W') && exit('Forbidden');

/**
 * 数据分析
 *
 * @package DataAnalyse
 */
class Datanalyse {
	var $db;
	var $nowtimestamp;
	var $overtimestamp;
	var $historyTime;
	var $overTime = 30;
	var $defaultLimit = 20;
	var $specialLimit = null;
	var $actions;

	function Datanalyse() {
		$this->__construct();
	}

	function __construct() {
		global $db;
		$this->db = & $db;
		$this->historyTime = $this->getTimestamp('h');
		$this->nowtimestamp = $this->getTimestamp('n');
		$this->overtimestamp = $this->getTimestamp('o');
		$this->actions = new DatanalyseAction();
	}

	/**
	 * @param $specialLimit the $specialLimit to set
	 */
	function setSpecialLimit($specialLimit) {
		$this->specialLimit = $specialLimit;
	}

	/**
	 * @param $type
	 * @return unknown_type
	 */
	function getDatanalyseForRateByType($type) {
		if ($type == "1") {
			$actions = $this->actions->threadRate;
		} elseif ($type == "3") {
			$actions = $this->actions->picRate;
		} elseif ($type == "2") {
			$actions = $this->actions->diaryRate;
		}
		if ($actions) {
			$sql = "SELECT a.tag, SUM(a.num) AS num, a.action FROM pw_datanalyse a
					WHERE action IN ( " . S::sqlImplode($actions) . " )
					AND timeunit >= " . S::sqlEscape($this->getTimestamp('w')) . "
					GROUP BY a.tag,a.action  ORDER BY num DESC";
			$query = $this->db->query($sql);
			$result = array();
			while ($rt = $this->db->fetch_array($query)) {
				if (!in_array($rt['action'], $result)) {
					$result[$rt['tag']] = $rt['action'];
					$nums[] = $rt['num'];
					$tags[] = $rt['tag'];
				}
			}
			$r1 = array();
			if ($type == "1") {
				$r1 = $this->getThreadSort($tags, $nums);
			} elseif ($type == "3") {
				$r1 = $this->getPicSort($tags, $nums);
			} elseif ($type == "2") {
				$r1 = $this->getDiarySort($tags, $nums);
			}
			if (is_array($r1)) {
				foreach($r1 as $key => $value) {
					$value['action'] = $result[$value['id']];
					$r1[$key] = $value;
				}
			}
			if (!empty($tags) && !empty($r1)) {
				foreach($tags as $key => $value) {
					if (!empty($r1[$value])) {
						$result[$value] = $r1[$value];
					}
				}
			}
			return $result;
		}
	}

	/**
	 * @param $action
	 * @return unknown_type
	 */
	function clearData($action) {
		//清理超时数据
		$sql = "DELETE FROM pw_datanalyse WHERE action=" . S::sqlEscape($action) . "
				AND timeunit < " . S::sqlEscape($this->overtimestamp) . " AND timeunit != " . S::sqlEscape($this->historyTime);
		$this->db->update($sql);
		
		//保留每天的top 100
		$d_time = $this->getDeleteTimes();
		$_w = "";
		foreach($d_time as $t) {
			$sql = "SELECT num FROM pw_datanalyse
					WHERE action = " . S::sqlEscape($action) . " AND timeunit = " . S::sqlEscape($t) . "
					ORDER BY num DESC LIMIT " . $this->getMaxNum() . ",1";
			$rt = $this->db->get_value($sql);
			if ($rt) {
				$_w .= "( action = " . S::sqlEscape($action) . " AND timeunit = " . S::sqlEscape($t) . " AND num < " . S::sqlEscape($rt) . " ) OR ";
			}
		}
		$_w = trim($_w, " OR");
		if ($_w) {
			$this->db->update("DELETE FROM pw_datanalyse WHERE $_w ");
		}
		$this->setDeleteTimePoint();
	}

	/**
	 * @return unknown_type
	 */
	function getDeleteTimes() {
		$file = @fopen(D_P . "data/bbscache/datanlyse.txt", "rb");
		if ($file) {
			$c = fgets($file, '100');
			$c = explode("=", trim($c));
			if (count($c) > 1) {
				$d_points = $c[1];
			}
			fclose($file);
		}
		if (!$d_points) {
			$d_points = $this->overtimestamp;
		}
		$diff = ($this->nowtimestamp - $d_points) / 86400;
		$result = array();
		for ($index = 0; $index < $diff; $index++) {
			$result[] = $d_points + $index * 24 * 60 * 60;
		}
		return $result;
	}

	/**
	 * @return unknown_type
	 */
	function setDeleteTimePoint() {
		$c = "timepoint=" . $this->nowtimestamp;
		pwCache::writeover(D_P . "data/bbscache/datanlyse.txt",$c);
	}

	/**
	 * @return unknown_type
	 */
	function getMaxNum() {
		return $this->defaultLimit * 10;
	}

	/**
	 * public method
	 * @param $action
	 * @param $type
	 * @param $limit
	 * @return unknown_type
	 */
	function getSortData($action, $type, $limit, $subType = null) {
		//$this->clearData($action);
		$result = array();
		$types = array(
			'today',
			'week',
			'month',
			'history'
		);
		if (!$limit || !is_numeric($limit) || $limit > $this->defaultLimit) {
			$limit = $this->defaultLimit;
		}
		if ($this->specialLimit != null) {
			$limit = $this->specialLimit;
		}
		if (in_array($type, $types)) {
			$result = $this->sortData($action, $type, $limit);
		} else {
			if ($this->actions->getMember($action) && ($subType != null)) {
				$result = $this->memberSortByType($subType, $limit);
			} elseif ($this->actions->getForum($action) && ($subType != null)) {
				$result = $this->forumSortByType($subType, $limit);
			}
		}
		return $result;
	}

	/**
	 * @param $action
	 * @return unknown_type
	 */
	function memberSortByType($action, $limit) {
		L::loadClass('element', '', false);
		$pwElement = new PW_Element($this->defaultLimit);
		$data = $pwElement->userSort($action, $limit);
		foreach($data as $key => $value) {
			$tags[] = $value['addition']['uid'];
			$nums[$value['addition']['uid']] = $this->formatValue($value['value']);
		}
		$r = $this->getMemberSort($tags, $nums);
		if (!empty($tags) && !empty($r)) {
			foreach($tags as $key => $value) {
				if (!empty($r[$value])) {
					$result[] = $r[$value];
				}
			}
		}
		return $result;
	}

	function forumSortByType($action, $limit) {
		L::loadClass('element', '', false);
		$pwElement = new PW_Element($this->defaultLimit);
		$data = $pwElement->forumSort($action, $limit);
		foreach($data as $key => $value) {
			$tags[] = $value['addition']['fid'];
			$nums[$value['addition']['fid']] = $this->formatValue($value['value']);
		}
		$r = $this->getForumSort($tags, $nums);
		if (!empty($tags) && !empty($r)) {
			foreach($tags as $key => $value) {
				if (!empty($r[$value])) {
					$result[] = $r[$value];
				}
			}
		}
		return $result;
	}

	/**
	 * @param $action
	 * @param $type
	 * @param $limit
	 * @return unknown_type
	 */
	function sortData($action, $type, $limit) {
		if ($type == "today") {
			$timeSpan = " AND a.timeunit = " . S::sqlEscape($this->nowtimestamp);
		} else if ($type == "week") {
			$timeSpan = " AND a.timeunit >= " . S::sqlEscape($this->getTimestamp('w'));
		} else if ($type == "month") {
			$timeSpan = " AND a.timeunit >= " . S::sqlEscape($this->getTimestamp('m'));
		} else if ($type == "history") {
			$timeSpan = " AND a.timeunit = " . S::sqlEscape($this->historyTime);
		}
		if ($action == 'memberShareAll') {
			$w_action = "a.action IN ( " . S::sqlImplode($this->actions->share) . " )";
		} else {
			$w_action = "a.action = " . S::sqlEscape($action);
		}
		if (in_array($action, $this->actions->member)) {
			$joinTable = " LEFT JOIN pw_members m ON a.tag = m.uid ";
			$w_action && $w_action .= " AND m.uid is not null ";
		} elseif (in_array($action, $this->actions->thread)) {
			$joinTable = "LEFT JOIN pw_threads t ON a.tag = t.tid ";
			$w_action && $w_action .= " AND t.tid is not null AND t.fid != '0' ";
		} elseif (in_array($action, $this->actions->diary)) {
			$joinTable = " LEFT JOIN pw_diary d ON a.tag = d.did ";
			$w_action && $w_action .= " AND d.did is not null ";
		} elseif (in_array($action, $this->actions->pic)) {
			$joinTable = " LEFT JOIN pw_cnphoto c ON a.tag = c.pid ";
			$w_action && $w_action .= " AND c.pid is not null AND c.aid != '0' ";
		}
		$sql = "SELECT a.tag, SUM(a.num) AS num FROM pw_datanalyse a $joinTable
			WHERE $w_action $timeSpan GROUP BY a.tag ORDER BY num DESC,tag LIMIT $limit ";
		$query = $this->db->query($sql);
		while ($rt = $this->db->fetch_array($query)) {
			$tags[] = $rt['tag'];
			$nums[$rt['tag']] = $action == "memberOnLine" ? $this->getOnlineTime($rt['num']) : $this->formatValue($rt['num']);
		}
		if ($this->actions->getMember($action)) {
			$r = $this->getMemberSort($tags, $nums);
		} elseif ($this->actions->getThread($action)) {
			$r = $this->getThreadSort($tags, $nums);
		} elseif ($this->actions->getDiary($action)) {
			$r = $this->getDiarySort($tags, $nums);
		} elseif ($this->actions->getPic($action)) {
			$r = $this->getPicSort($tags, $nums);
		}
		if (!empty($tags) && !empty($r)) {
			foreach($tags as $key => $value) {
				$result[] = $r[$value];
			}
		}
		return $result;
	}

	function getForumSort($tags, $nums) {
		if (!empty($tags) && !empty($nums)) {
			$sql = "SELECT f.fid,f.name,fd.lastpost FROM pw_forumdata fd LEFT JOIN pw_forums f ON f.fid = fd.fid
					WHERE f.fid IN ( " . S::sqlImplode($tags) . " )";
			$query = $this->db->query($sql);
			$result = array();
			while ($rt = $this->db->fetch_array($query)) {
				$r['id'] = $rt['fid'];
				$r['title'] = $rt['name'];
				$lastpost = explode("\t", $rt['lastpost']);
				$r['desc'] = $lastpost[0];
				$r['author'] = $lastpost[1];
				$r['lastDate'] = get_date($lastpost[2]);
				$r['url'] = $lastpost[3];
				$r['value'] = $nums[$rt['fid']];
				$result[$rt['fid']] = $r;
			}
		}
		return $result;
	}

	/**
	 * @param $tags
	 * @param $nums
	 * @return unknown_type
	 */
	function getPicSort($tags, $nums) {
		if (!empty($tags) && !empty($nums)) {
			$sql = "SELECT p.pid, p.path, p.uploader, p.uptime, p.pintro,  a.aid, a.ownerid, a.atype, p.ifthumb
					FROM pw_cnphoto p LEFT JOIN pw_cnalbum a ON p.aid = a.aid WHERE p.pid IN ( " . S::sqlImplode($tags) . " )";
			$query = $this->db->query($sql);
			$result = array();
			while ($rt = $this->db->fetch_array($query)) {
				$r['id'] = $rt['pid'];
				if ($rt['atype'] == "0") {
					$r['url'] = "apps.php?q=photos&uid=" . $rt['ownerid'] . "&a=view&pid=" . $rt['pid'];
				} else {
					$r['url'] = "apps.php?q=galbum&a=album&cyid=" . $rt['ownerid'] . "&aid=" . $rt['aid'];
				}
				$r['author'] = $rt['uploader'];
				$r['image'] = $rt['path']; //getphotourl($rt['path'],$rt['ifthumb']);
				$r['ifthumb'] = $rt['ifthumb'];
				$r['lasttime'] = get_date($rt["uptime"]);
				$r['value'] = $nums[$rt['pid']];
				$r['title'] = $rt['pintro'] ? $rt['pintro'] : getLangInfo("other", "photo_none_desc");
				$result[$rt['pid']] = $r;
			}
		}
		return $result;
	}

	/**
	 * @param $tags
	 * @param $nums
	 * @return unknown_type
	 */
	function getDiarySort($tags, $nums) {
		if (!empty($tags) && !empty($nums)) {
			$sql = "SELECT d.did, d.subject, d.postdate, d.username, d.uid FROM pw_diary d
					WHERE d.did IN ( " . S::sqlImplode($tags) . " )";
			$query = $this->db->query($sql);
			$result = array();
			while ($rt = $this->db->fetch_array($query)) {
				$r['id'] = $rt['did'];
				$r['title'] = $rt['subject'];
				$r['author'] = $rt['username'];
				$r['url'] = "apps.php?q=diary&a=detail&uid=".$rt['uid']."&did=".$rt['did'];
				$r['postdate'] = get_date($rt['postdate']);
				$r['value'] = $nums[$rt['did']];
				$result[$rt['did']] = $r;
			}
		}
		return $result;
	}

	/**
	 * @param $tags
	 * @param $nums
	 * @return unknown_type
	 */
	function getThreadSort($tags, $nums) {
		if (!empty($tags) && !empty($nums)) {
			$sql = "SELECT t.tid, t.fid, t.author, t.subject, t.postdate  FROM pw_threads t
					WHERE t.tid IN ( " . S::sqlImplode($tags) . " ) ";
			$query = $this->db->query($sql);
			$result = array();
			while ($rt = $this->db->fetch_array($query)) {
				$r['id'] = $rt['tid'];
				$r['fid'] = $rt['fid'];
				$r['title'] = $rt['subject'];
				$r['author'] = $rt['author'];
				$r['url'] = "read.php?tid=" . $rt['tid'];
				$r['postdate'] = get_date($rt['postdate']);
				$r['value'] = $nums[$rt['tid']];
				$result[$rt['tid']] = $r;
			}
		}
		return $result;
	}

	/**
	 * @param $tags
	 * @param $nums
	 * @return unknown_type
	 */
	function getMemberSort($tags, $nums) {
		if (!empty($tags) && !empty($nums)) {
			$sql = "SELECT m.uid, m.icon, m.username, md.lastvisit
					FROM pw_members m LEFT JOIN pw_memberdata md ON m.uid=md.uid
					WHERE m.uid IN ( " . S::sqlImplode($tags) . " ) ";
			$query = $this->db->query($sql);
			$result = array();
			while ($rt = $this->db->fetch_array($query)) {
				$r['id'] = $rt['uid'];
				$r['title'] = $rt['username'];
				$r['url'] = USER_URL . $rt['uid'];
				list($userIcon) = showfacedesign($rt["icon"], 1, 'm');
				$r['image'] = $userIcon;
				list($lastDate) = getLastDate($rt["lastvisit"]);
				$r['lastDate'] = $lastDate;
				$r['value'] = $nums[$rt['uid']]; //$this->getOnlineTime($nums [$rt ['uid']]);
				$result[$rt['uid']] = $r;
			}
		}
		return $result;
	}

	/**
	 * @param $value
	 * @return unknown_type
	 */
	function getOnlineTime($value) {
		if ($value < 60) {
			$value = "0分钟";
		} elseif ($value >= 60 && $value < 3600) {
			$value = ceil($value / 60) . "分钟";
		} elseif ($value >= 3600) {
			$value = ceil($value / 3600) . "小时" . ceil(($value % 3600) / 60) . "分钟";
		}
		return $value;
	}

	function formatValue($value) {
		if (strlen($value) > 5) {
			$result = ceil($value / 10000) . '万';
		} else {
			$result = $value;
		}
		return $result;
	}

	/**
	 * @param $type
	 * @return unknown_type
	 */
	function getTimestamp($type = 'n') {
		global $timestamp;
		$_tmp = PwStrtoTime(get_date($timestamp, 'Y-m-d'));
		if ($type == 'o') {
			$_tmp -= (int)$this->overTime * 60 * 60 * 24;
		} elseif ($type == 'w') {
			$_tmp -= 7 * 60 * 60 * 24;
		} elseif ($type == 'm') {
			$_tmp -= 30 * 60 * 60 * 24;
		} elseif ($type == 'h') {
			$_tmp = mktime(0, 0, 0, 0, 0, 0);
		}
		return $_tmp;
	}
}

/**
 * 数据分析操作
 *
 * @package DataAnalyse
 */
class DatanalyseAction {
	var $rate;
	var $share = array();
	var $picRate = array();
	var $diaryRate = array();
	var $threadRate = array();
	var $member = array(
		'memberOnLine',
		'memberThread',
		'memberShare',
		'memberCredit',
		'memberFriend'
	);
	var $memberUnit = array(
		'',
		'帖',
		'分享'
	);
	var $thread = array(
		'threadPost',
		'threadFav',
		'threadShare',
		'threadRate'
	);
	var $threadUnit = array(
		'回复',
		'收藏',
		'分享',
		'评价'
	);
	var $diary = array(
		'diaryComment',
		'diaryFav',
		'diaryShare',
		'diaryRate'
	);
	var $diaryUnit = array(
		'评论',
		'收藏',
		'分享',
		'评价'
	);
	var $pic = array(
		'picComment',
		'picFav',
		'picShare',
		'picRate'
	);
	var $picUnit = array(
		'评论',
		'收藏',
		'分享',
		'评价'
	);
	var $forum = array(
		'forumPost',
		'forumTopic',
		'forumArticle'
	);
	var $forumUnit = array(
		'帖',
		'主题',
		'文章'
	);
	function DatanalyseAction() {
		$this->rate = L::loadClass('rate', 'rate');
		$this->share = array(
			"memberShareThread",
			"memberShareDiary",
			"memberShareAlbum",
			"memberShareUser",
			"memberShareGroup",
			"memberSharePic",
			"memberShareLink",
			"memberShareVideo",
			"memberShareMusic",
			"memberShareAll"
		);
		$this->picRate = $this->getRate("picRate");
		$this->diaryRate = $this->getRate("diaryRate");
		$this->threadRate = $this->getRate("threadRate");
		!empty($this->share) && $this->member = array_merge($this->member, $this->share);
		!empty($this->picRate) && $this->pic = array_merge($this->pic, $this->picRate);
		!empty($this->diaryRate) && $this->diary = array_merge($this->diary, $this->diaryRate);
		!empty($this->threadRate) && $this->thread = array_merge($this->thread, $this->threadRate);
	}
	function getMemberShareType($index) {
		if (is_numeric($index) && $index < 10) {
			return $this->share[$index];
		}
	}
	function getUnit($action) {
		if (in_array($action, $this->member)) {
			return $this->memberUnit[array_search($action, $this->member) ];
		} elseif (in_array($action, $this->thread)) {
			return $this->threadUnit[array_search($action, $this->thread) ];
		} elseif (in_array($action, $this->diary)) {
			return $this->diaryUnit[array_search($action, $this->diary) ];
		} elseif (in_array($action, $this->pic)) {
			return $this->picUnit[array_search($action, $this->pic) ];
		} elseif (in_array($action, $this->forum)) {
			return $this->forumUnit[array_search($action, $this->forum) ];
		}
	}
	function getAllAction() {
		$array = array_merge($this->member, $this->thread, $this->diary, $this->pic);
		return $array;
	}
	function isAction($action) {
		$array = array_merge($this->member, $this->thread, $this->diary, $this->pic);
		if (in_array($action, $array)) {
			return true;
		}
		return false;
	}
	function getForum($action) {
		if (in_array($action, $this->forum)) {
			return $action;
		}
	}
	function getPic($action) {
		if (in_array($action, $this->pic)) {
			return $action;
		}
	}
	function getDiary($action) {
		if (in_array($action, $this->diary)) {
			return $action;
		}
	}
	function getThread($action) {
		if (in_array($action, $this->thread)) {
			return $action;
		}
	}
	function getMember($action) {
		if (in_array($action, $this->member)) {
			return $action;
		}
	}
	function getRate($tag) {
		$rates = array();
		if ($tag == "threadRate" && $this->getRateSet($tag)) {
			$rates = $this->rate->getRateThreadHotTypes();
		} elseif ($tag == "diaryRate" && $this->getRateSet($tag)) {
			$rates = $this->rate->getRateDiaryHotTypes();
		} elseif ($tag == "picRate" && $this->getRateSet($tag)) {
			$rates = $this->rate->getRatePictureHotTypes();
		}
		$result = array_keys($rates);
		return $result;
	}
	function getRateSet($tag) {
		$result = 1;
		global $db_ratepower;
		$rateSets = unserialize($db_ratepower);
		$tag == 'threadRate' && $type = 1;
		$tag == 'diaryRate' && $type = 2;
		$tag == 'picRate' && $type = 3;
		$type && $result = $rateSets[$type];
		return $result;
	}
}
?>