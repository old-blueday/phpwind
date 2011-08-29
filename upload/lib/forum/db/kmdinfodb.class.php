<?php
!defined ( 'P_W' ) && exit ( 'Forbidden' );

class PW_KmdInfoDB extends BaseDB {
	
	var $_tableName = 'pw_kmd_info';
	var $_primaryKey = 'kid';
	var $_allowFields = array('kid', 'fid', 'uid', 'tid', 'status', 'starttime', 'endtime');
	
	/**
	 * 增加一条孔明灯记录
	 * @param array $fieldData
	 * @return bool
	 */
	function addKmdInfo($fieldData) {
		$fieldData = $this->_checkAllowField($fieldData, $this->_allowFields);
		if (!S::isArray($fieldData)) return false;
		return $this->_insert($fieldData);
	}
	
	/**
	 * 根据id删除孔明灯
	 * @param int $kid
	 * @return bool
	 */
	function deleteKmdInfoByKid($kid) {
		$kid = intval($kid);
		if ($kid < 1) return false;
		return $this->_delete($kid);
	}
	
	/**
	 * 根据id批量删除孔明灯
	 * @param array $kids
	 * @return bool
	 */
	function deleteKmdInfoByKids($kids) {
		if (!S::isArray($kids)) return false;
		return pwQuery::delete($this->_tableName, "{$this->_primaryKey} IN (:{$this->_primaryKey})", array($kids));
	}
	
	/**
	 * 根据kid获取孔明灯记录
	 * @param int $kid
	 * @return array
	 */
	function getKmdInfoByKid($kid) {
		$kid = intval($kid);
		if ($kid < 1) return false;
		return $this->_get($kid);
	}
	
	/**
	 * 根据id更新孔明灯信息
	 * @param array $fieldData
	 * @param int $kid
	 */
	function updateKmdInfo($fieldData, $kid) {
		list($fieldData, $kid) = array($this->_checkAllowField($fieldData, $this->_allowFields), intval($kid));
		if ($kid < 1 || !S::isArray($fieldData)) return false;
		return $this->_update($fieldData, $kid);
	}
	
	/**
	 * 回收到期的孔明灯
	 * @return bool
	 */
	function recycleAllExpiredKmds() {
		global $timestamp;
		$data = array('uid' => 0, 'tid' => 0, 'status' => 0, 'starttime' => 0, 'endtime' => 0);
		return pwQuery::update($this->_tableName, 'endtime<=:endtime', array($timestamp), $data);
	}
	
	/**
	 * 获取所有过期的孔明灯
	 */
	function getAllExpiredKmds(){
		global $timestamp;
		$query = $this->_db->query("SELECT * FROM $this->_tableName WHERE uid>0 AND endtime<=$timestamp");
		return $this->_getAllResultFromQuery($query, 'kid');
	}
	
	/**
	 * 获取所有使用中的孔明灯
	 */
	function getAllValidKmds($fid=0){
		$fid = intval($fid);
		$sql = "SELECT * FROM $this->_tableName WHERE uid>0";
		$fid && $sql .= " AND fid=$fid";
		$query = $this->_db->query($sql);
		return $this->_getAllResultFromQuery($query, 'kid');
	}
	
	/**
	 * 根据id获取详细信息
	 * @param int $kid
	 * @return array
	 */
	function getKmdInfoDetailByKid($kid) {
		$kid = intval($kid);
		if ($kid < 1) return array();
		return $this->_db->get_one("SELECT i.*, t.subject, f.name as forumname FROM $this->_tableName i LEFT JOIN pw_threads t USING(tid) LEFT JOIN pw_forums f ON(i.fid=f.fid) WHERE i.kid=" . S::sqlEscape($kid));
	}
	
	/**
	 * 根据id批量获取孔明灯信息
	 * @param array $kids
	 * @return array
	 */
	function getKmdInfoByKids($kids) {
		if (!S::isArray($kids)) return array();
		$query = $this->_db->query(pwQuery::selectClause($this->_tableName, "{$this->_primaryKey} IN (:{$this->_primaryKey})", array($kids)));
		return $this->_getAllResultFromQuery($query, 'kid');
	}
	
	/**
	 * 根据uid获取孔明灯信息
	 * @param int $uid
	 * @return array
	 */
	function getKmdInfoByUid($uid, $start, $limit) {
		list($uid, $start, $limit) = array(intval($uid), intval($start), intval($limit));
		if ($uid < 1) return array();
		$query = $this->_db->query(pwQuery::selectClause($this->_tableName, "uid=:uid", array($uid), array(PW_LIMIT => array($start, $limit))));
		return $this->_getAllResultFromQuery($query, 'kid');
	}
	
	/**
	 * 根据tid获取孔明灯信息
	 * @param int $tid
	 * @return array
	 */
	function getKmdInfoByTid($tid) {
		$tid = intval($tid);
		if ($tid < 1) return array();
		return $this->_db->get_one(pwQuery::selectClause($this->_tableName, "tid=:tid", array($tid)));
	}
	
	/**
	 * 根据fid获取购买的孔明灯信息
	 * @param int $fid
	 * @return array
	 */
	function getKmdInfoByFid($fid) {
		$fid = intval($fid);
		if ($fid < 1) return array();
		$query = $this->_db->query(pwQuery::selectClause($this->_tableName, "fid=:fid AND uid!=:uid", array($fid, 0), array(PW_ORDERBY => array('starttime' => PW_ASC))));
		return $this->_getAllResultFromQuery($query, 'kid');
	}
	
	/**
	 * 根据fid获取一条未被购买的孔明灯
	 * @param int $fid
	 * @return array
	 */
	function getUnusedKmdInfoByFid($fid) {
		$fid = intval($fid);
		if ($fid < 1) return array();
		return $this->_db->get_one(pwQuery::selectClause($this->_tableName, "fid=:fid AND uid=:uid", array($fid, 0), array(PW_COLUMN => array('kid'), PW_LIMIT => array(0, 1))));
	}
	
	/**
	 * 根据fid统计已被购买的孔明灯数量
	 * @param int $fid
	 * @return int
	 */
	function countUsedKmdNumsByFid($fid) {
		$fid = intval($fid);
		if ($fid < 1) return false;
		$total = $this->_db->get_value(pwQuery::selectClause($this->_tableName, 'fid=:fid AND uid!=:uid', array($fid, 0), array(PW_EXPR => array('COUNT(*) AS total'))));
		return $total;
	}
	
	/**
	 * 根据fid批量统计已被购买的孔明灯数量
	 * @param array $fids
	 * @return array
	 */
	function countUsedKmdNumsByFids($fids) {
		if (!S::isArray($fids)) return false;
		$query = $this->_db->query(pwQuery::selectClause($this->_tableName, 'fid IN(:fid) AND uid!=:uid', array($fids, 0), array(PW_COLUMN => array('fid'), PW_EXPR => array('COUNT(fid) AS total'), PW_GROUPBY => array('fid'))));
		return $this->_getAllResultFromQuery($query, 'fid');
	}
	
	function getKmdInfosByStatus($status){
		$status = intval($status);
		$query = $this->_db->query("SELECT * FROM $this->_tableName WHERE status=$status");
		return $this->_getAllResultFromQuery($query, 'kid');
	}
	
	/**
	 * 根据条件查找孔明灯信息
	 * @param int $start
	 * @param int $limit
	 * @param int $fid
	 * @param int $uid
	 * @param int $status
	 * @param int $starttime
	 * @param int $endtime
	 * @return array
	 */
	function getKmdInfosWithCondition($start, $limit, $fid, $uid, $status, $starttime, $endtime) {
		list($start, $limit, $fid, $uid, $status, $starttime, $endtime, $sql) = array(intval($start), intval($limit), intval($fid), intval($uid), intval($status), intval($starttime), intval($endtime), '');
		$fid && ($sql .= S::isArray($fid) ? ' AND i.fid IN(' . S::sqlImplode($fid) . ')' : ' AND i.fid=' . S::sqlEscape($fid));
		$sql .= $uid ? ' AND i.uid=' . S::sqlEscape($uid) : ' AND i.uid>0';
		$status && $sql .= ' AND i.status=' . S::sqlEscape($status);
		$starttime && $sql .= ' AND i.starttime>' . S::sqlEscape($starttime);
		$endtime && $sql .= ' AND i.endtime<' . S::sqlEscape($endtime);
		$query = $this->_db->query("SELECT i.* FROM $this->_tableName i WHERE 1 $sql ORDER BY i.starttime DESC " . S::sqlLimit($start, $limit));
		return $this->_getAllResultFromQuery($query, 'kid');
	}
	
	/**
	 * 根据条件统计孔明灯数量
	 * @param int $fid
	 * @param int $uid
	 * @param int $status
	 * @param int $starttime
	 * @param int $endtime
	 * @return int
	 */
	function countKmdInfosWithCondition($fid, $uid, $status, $starttime, $endtime) {
		list($fid, $uid, $status, $starttime, $endtime, $sql) = array(intval($fid), intval($uid), intval($status), intval($starttime), intval($endtime), '');
		$fid && ($sql .= S::isArray($fid) ? ' AND i.fid IN(' . S::sqlImplode($fid) . ')' : ' AND i.fid=' . S::sqlEscape($fid));
		$sql .= $uid ? ' AND i.uid=' . S::sqlEscape($uid) : ' AND i.uid>0';
		$status && $sql .= ' AND i.status=' . S::sqlEscape($status);
		$starttime && $sql .= ' AND i.starttime>' . S::sqlEscape($starttime);
		$endtime && $sql .= ' AND i.endtime<' . S::sqlEscape($endtime);
		$total = $this->_db->get_value("SELECT COUNT(*) AS total FROM $this->_tableName AS i WHERE 1 $sql");
		return $total;
	}
}
?>