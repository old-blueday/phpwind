<?php
!defined('P_W') && exit('Forbidden');
@include_once (R_P . 'lib/base/basedb.php');

class PW_PcvalueDB extends BaseDB {
	var $_tableName = 'pw_pcvalue1';

	/**
	 * 
	 * 获取最新团购帖数据
	 * @param array $fid 板块ID
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function getSourceByPostdate($fid,$num = 10,$order = 'DESC'){
		global $timestamp;
		$num = intval($num);
		$sqladd = '';
		if ($fid) $sqladd .= " AND t.fid IN ($fid)";
		$sqladd .= " AND pc.endtime >= $timestamp AND t.ifcheck = 1  AND t.fid != 0 AND t.ifshield != 1 AND t.locked != 2";
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqladd .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies,t.anonymous,pc.pcattach FROM $this->_tableName pc LEFT JOIN pw_threads t USING(tid) WHERE 1 $sqladd ORDER BY t.tid $order " . S::sqlLimit($num));
		return $this->_getAllResultFromQuery ( $query );
	}
	
	/**
	 * 
	 * 按即将截止获取团购帖数据
	 * @param array $fid 板块ID
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function getSourceByEndtime($fid,$num = 10,$order = 'ASC'){
		global $timestamp;
		$num = intval($num);
		$sqladd = '';
		if ($fid) $sqladd .= " AND t.fid IN ($fid)";
		$sqladd .= " AND pc.endtime >= $timestamp AND t.ifcheck = 1  AND t.fid != 0 AND t.ifshield != 1 AND t.locked != 2";
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqladd .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies,t.anonymous,pc.pcattach FROM $this->_tableName pc LEFT JOIN pw_threads t USING(tid) WHERE 1 $sqladd ORDER BY pc.endtime $order ".S::sqlLimit($num));
		return $this->_getAllResultFromQuery ( $query );
	}
	
	/**
	 * 
	 * 按回复数获取团购帖数据
	 * @param array $fid 板块ID
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function getSourceByReplys($fid,$num,$order = 'DESC'){
		global $timestamp;
		$num = intval($num);
		$sqladd = '';
		if ($fid) $sqladd .= " AND t.fid IN ($fid)";
		$sqladd .= " AND pc.endtime >= $timestamp AND t.ifcheck = 1  AND t.fid != 0 AND t.ifshield != 1 AND t.locked != 2";
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqladd .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies,t.anonymous,pc.pcattach FROM $this->_tableName pc LEFT JOIN pw_threads t USING(tid) WHERE 1 $sqladd ORDER BY t.replies $order,t.postdate $order " . S::sqlLimit($num));
		return $this->_getAllResultFromQuery ( $query );
	}

	/**
	 * 
	 * 按点击数获取团购帖排行
	 * @param array $fid 板块ID
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function getSourceByHits($fid,$num,$order = 'DESC'){
		global $timestamp;
		$num = intval($num);
		$sqladd = '';
		if ($fid) $sqladd .= " AND t.fid IN ($fid)";
		$sqladd .= " AND pc.endtime >= $timestamp AND t.ifcheck = 1  AND t.fid != 0 AND t.ifshield != 1 AND t.locked != 2";
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqladd .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies,t.anonymous,pc.pcattach FROM $this->_tableName pc LEFT JOIN pw_threads t USING(tid) WHERE 1 $sqladd ORDER BY t.hits $order " . S::sqlLimit($num));
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function _getBlackListedTids() {
		global $db_tidblacklist;
		return $db_tidblacklist;
	}
}

?>