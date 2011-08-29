<?php
!defined('P_W') && exit('Forbidden');
@include_once (R_P . 'lib/base/basedb.php');

class PW_DebateDB extends BaseDB {
	var $_tableName = 'pw_debates';
	
	/**
	 * 
	 * 获取最新辩论帖排行
	 * @param string $fid
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function newDebate($fid,$num,$order = 'DESC'){
		global $timestamp;
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$posts = array();
		$sqlWhere = '';
		$fid && $sqlWhere .= " AND t.fid IN ($fid) ";
		$sqlWhere .= " AND t1.endtime >= $timestamp AND t.ifcheck = 1 AND t.fid != 0 ";
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqlWhere .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t1.*,t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies,t.anonymous FROM $this->_tableName t1 LEFT JOIN pw_threads t USING(tid) WHERE 1 $sqlWhere AND t.ifshield != 1 AND t.locked != 2   ORDER BY t.postdate $order ".S::sqlLimit($num));
		while ($row = $this->_db->fetch_array($query)) {
			$posts[] = $row;
		}
		return $posts;
	}
	
	/**
	 * 
	 * 按即将截止时间获取辩论帖排行
	 * @param string $fid
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function endDebate($fid,$num,$order = 'ASC'){
		global $timestamp;
		$order = strtoupper($order);
		$order !== 'ASC' && $order = 'DESC';
		$posts = array();
		$sqlWhere = '';
		$fid && $sqlWhere .= " AND t.fid IN ($fid) AND t1.judge = 0";
		$sqlWhere .= " AND t1.endtime >= $timestamp AND t.ifcheck = 1  AND t.fid != 0 ";
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqlWhere .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t1.*,t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies,t.anonymous FROM $this->_tableName t1 LEFT JOIN pw_threads t USING(tid) WHERE 1 $sqlWhere AND t.ifshield != 1 AND t.locked != 2   ORDER BY t1.endtime $order ".S::sqlLimit($num));
		while ($row = $this->_db->fetch_array($query)) {
			$posts[] = $row;
		}
		return $posts;
	}
	
	/**
	 * 
	 * 按参与数数获取辩论帖排行（即热门辩论）
	 * @param string $fid
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function hotSortDebate($fid,$num,$order = 'DESC'){
		global $timestamp;
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$posts = array();
		$sqlWhere = '';
		$fid && $sqlWhere .= " AND t.fid IN ($fid) AND t1.judge = 0";
		$sqlWhere .= " AND t1.endtime >= $timestamp AND t.ifcheck = 1  AND t.fid != 0 ";
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqlWhere .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t1.*,(t1.obvote+t1.revote) AS count,t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies,t.anonymous FROM $this->_tableName t1 LEFT JOIN pw_threads t USING(tid) WHERE 1 $sqlWhere AND t.ifshield != 1 AND t.locked != 2   ORDER BY count $order ".S::sqlLimit($num));
		while ($row = $this->_db->fetch_array($query)) {
			$posts[] = $row;
		}
		return $posts;
	}
	
	/**
	 * 
	 * 按回复数获取辩论帖排行
	 * @param string $fid
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function replySortDebate($fid,$num,$order = 'DESC'){
		global $timestamp;
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$posts = array();
		$sqlWhere = '';
		$fid && $sqlWhere .= " AND t.fid IN ($fid) AND t1.judge = 0";
		$sqlWhere .= " AND t1.endtime >= $timestamp AND t.ifcheck = 1  AND t.fid != 0 ";
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqlWhere .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t1.*,t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies,t.anonymous FROM $this->_tableName t1 LEFT JOIN pw_threads t USING(tid) WHERE 1 $sqlWhere AND t.ifshield != 1 AND t.locked != 2   ORDER BY t.replies $order ".S::sqlLimit($num));
		while ($row = $this->_db->fetch_array($query)) {
			$posts[] = $row;
		}
		return $posts;
	}

	/**
	 * 
	 * 按点击数获取辩论帖排行
	 * @param string $fid
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function hitSortDebate($fid,$num,$order = 'DESC'){
		global $timestamp;
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$posts = array();
		$sqlWhere = '';
		$fid && $sqlWhere .= " AND t.fid IN ($fid) AND t1.judge = 0";
		$sqlWhere .= " AND t1.endtime >= $timestamp AND t.ifcheck = 1  AND t.fid != 0 ";
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqlWhere .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t1.*,t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies,t.anonymous FROM $this->_tableName t1 LEFT JOIN pw_threads t USING(tid) WHERE 1 $sqlWhere AND t.ifshield != 1 AND t.locked != 2   ORDER BY t.hits $order ".S::sqlLimit($num));
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