<?php
!defined('P_W') && exit('Forbidden');
@include_once (R_P . 'lib/base/basedb.php');

class PW_TradeDB extends BaseDB {
	var $_tableName = 'pw_trade';
	
	/**
	 * 
	 * 获取最新商品帖数据
	 * @param array $fid 板块ID
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function getSourceByPostdate($fid,$num,$order = 'DESC'){
		$num = intval($num);
		$sqladd = '';
		if ($fid) $sqladd .= " AND t.fid IN ($fid)";
		$sqladd .= ' AND t.ifcheck = 1 AND t.fid != 0 AND tr.num > 0 ';
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqladd .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies,tr.icon FROM $this->_tableName tr LEFT JOIN pw_threads t USING(tid) WHERE 1 $sqladd AND t.ifshield != 1 AND t.locked != 2  ORDER BY t.postdate $order " . S::sqlLimit($num));
		return $this->_getAllResultFromQuery ( $query );
	}
	
	/**
	 * 
	 * 按销售数量获取商品帖数据
	 * @param array $fid 板块ID
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function getSourceBySalenum($fid,$num,$order = 'DESC'){
		$num = intval($num);
		$sqladd = '';
		if ($fid) $sqladd .= " AND t.fid IN ($fid)";
		$sqladd .= ' AND t.ifcheck = 1 AND t.fid != 0 AND tr.num > 0 ';
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqladd .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies,tr.icon FROM $this->_tableName tr LEFT JOIN pw_threads t USING(tid) WHERE 1 $sqladd AND t.ifshield != 1 AND t.locked != 2  ORDER BY tr.salenum $order ".S::sqlLimit($num));
		return $this->_getAllResultFromQuery ( $query );
	}
	
	/**
	 * 
	 * 按回复数获取商品帖数据
	 * @param array $fid 板块ID
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function getSourceByReplys($fid,$num,$order = 'DESC'){
		$num = intval($num);
		$sqladd = '';
		if ($fid) $sqladd .= " AND t.fid IN ($fid)";
		$sqladd .= ' AND t.ifcheck = 1 AND t.fid != 0 AND tr.num > 0 ';
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqladd .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies,tr.icon FROM $this->_tableName tr LEFT JOIN pw_threads t USING(tid) WHERE 1 $sqladd AND t.ifshield != 1 AND t.locked != 2  ORDER BY t.replies $order " . S::sqlLimit($num));
		return $this->_getAllResultFromQuery ( $query );
	}

	/**
	 * 
	 * 按点击数获取商品帖排行
	 * @param array $fid 板块ID
	 * @param int $num
	 * @param string $order
	 * @return array
	 */
	function getSourceByHits($fid,$num,$order = 'DESC'){
		$num = intval($num);
		$sqladd = '';
		if ($fid) $sqladd .= " AND t.fid IN ($fid)";
		$sqladd .= ' AND t.ifcheck = 1 AND t.fid != 0 AND t1.num > 0 ';
		$order = strtoupper($order);
		$order !== 'DESC' && $order = 'ASC';
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqladd .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies,t1.icon FROM $this->_tableName t1 LEFT JOIN pw_threads t USING(tid) WHERE 1 $sqladd AND t.ifshield != 1 AND t.locked != 2  ORDER BY t.hits $order " . S::sqlLimit($num));
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function _getBlackListedTids() {
		global $db_tidblacklist;
		return $db_tidblacklist;
	}
}

?>