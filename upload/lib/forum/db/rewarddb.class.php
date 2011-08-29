<?php
!defined('P_W') && exit('Forbidden');
@include_once (R_P . 'lib/base/basedb.php');

class PW_RewardDB extends BaseDB {
	var $_tableName = 'pw_reward';
	
	/**
	 * 
	 * 获取最新悬赏帖排行
	 * @param string $fid
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function newReward($fid,$num,$timestamp,$order = 'DESC'){
		if(!$timestamp) return array();
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$posts = array();
		$sqlWhere = '';
		$fid && $sqlWhere .= " t.fid IN ($fid) ";
		$timestamp && $sqlWhere = ' AND t1.timelimit > '. S::SqlEscape($timestamp);
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqlWhere .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t1.*,t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies FROM $this->_tableName t1 LEFT JOIN pw_threads t USING(tid) WHERE 1 $sqlWhere AND t.ifshield != 1 AND t.locked != 2  AND t.state != 3 AND t.ifcheck = 1 AND t.fid != 0 ORDER BY t.postdate $order ".S::sqlLimit($num));
		while ($row = $this->_db->fetch_array($query)) {
			$posts[] = $row;
		}
		return $posts;
	}
	
	/**
	 * 
	 * 按悬赏金额获取悬赏帖排行
	 * @param string $fid
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function topReward($fid,$num,$timestamp,$order = 'DESC'){
		if(!$timestamp) return array();
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$posts = array();
		$sqlWhere = '';
		$fid && $sqlWhere .= " t.fid IN ($fid) ";
		$timestamp && $sqlWhere = ' AND t1.timelimit > '. S::SqlEscape($timestamp);
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqlWhere .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t1.*,t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies FROM $this->_tableName t1 LEFT JOIN pw_threads t USING(tid) WHERE 1 $sqlWhere AND t.ifshield != 1 AND t.locked != 2  AND t.state != 3 AND t.ifcheck = 1 AND t.fid != 0 ORDER BY t1.cbval + t1.caval $order ".S::sqlLimit($num));
		while ($row = $this->_db->fetch_array($query)) {
			$posts[] = $row;
		}
		return $posts;
	}
	
	/**
	 * 
	 * 按回复数获取悬赏帖排行
	 * @param string $fid
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function replySortReward($fid,$num,$timestamp,$order = 'DESC'){
		if(!$timestamp) return array();
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$posts = array();
		$sqlWhere = '';
		$fid && $sqlWhere .= " t.fid IN ($fid) ";
		$timestamp && $sqlWhere = ' AND t1.timelimit > '. S::SqlEscape($timestamp);
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqlWhere .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t1.*,t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies FROM $this->_tableName t1 LEFT JOIN pw_threads t USING(tid) WHERE 1 $sqlWhere AND t.ifshield != 1 AND t.locked != 2  AND t.state != 3 AND t.ifcheck = 1 AND t.fid != 0 ORDER BY t.replies $order ".S::sqlLimit($num));
		while ($row = $this->_db->fetch_array($query)) {
			$posts[] = $row;
		}
		return $posts;
	}

	/**
	 * 
	 * 按点击数获取悬赏帖排行
	 * @param string $fid
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function hitSortReward($fid,$num,$timestamp,$order = 'DESC'){
		if(!$timestamp) return array();
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$posts = array();
		$sqlWhere = '';
		$fid && $sqlWhere .= " t.fid IN ($fid) ";
		$timestamp && $sqlWhere = ' AND t1.timelimit > '. S::SqlEscape($timestamp);
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqlWhere .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t1.*,t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies FROM $this->_tableName t1 LEFT JOIN pw_threads t USING(tid) WHERE 1 $sqlWhere AND t.ifshield != 1 AND t.locked != 2  AND t.state != 3 AND t.ifcheck = 1 AND t.fid != 0 ORDER BY t.hits $order ".S::sqlLimit($num));
		while ($row = $this->_db->fetch_array($query)) {
			$posts[] = $row;
		}
		return $posts;
	}
	
	function _getBlackListedTids() {
		global $db_tidblacklist;
		return $db_tidblacklist;
	}
}

?>