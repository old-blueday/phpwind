<?php
!defined('P_W') && exit('Forbidden');

class PW_KmdPayLogDB extends BaseDB {
	
	var $_tableName = 'pw_kmd_paylog';
	var $_primaryKey = 'id';
	var $_allowFields = array('id', 'fid', 'uid', 'sid', 'kid', 'type', 'money', 'status', 'createtime');
	
	/**
	 * 增加一条支付记录
	 * @param  array $fieldData
	 * @return bool
	 */
	function addPayLog($fieldData) {
		$fieldData = $this->_checkAllowField($fieldData, $this->_allowFields);
		if (!S::isArray($fieldData)) return false;
		return $this->_insert($fieldData);
	}
	
	/**
	 * 根据id删除记录
	 * @param int $id
	 * @return bool
	 */
	function deletePayLogById($id) {
		$id = intval($id);
		if ($id < 1) return false;
		return $this->_delete($id);
	}
	
	/**
	 * 根据id批量删除记录
	 * @param array $ids
	 * @return bool
	 */
	function deletePayLogsByIds($ids) {
		if (!S::isArray($ids)) return false;
		return pwQuery::delete($this->_tableName, "{$this->_primaryKey} IN (:{$this->_primaryKey})", array($ids));
	}
	
	/**
	 * 根据uid删除记录
	 * @param int $uid
	 * @return bool
	 */
	function deletePayLogByUid($uid) {
		$uid = intval($uid);
		if ($uid < 1) return false;
		return pwQuery::delete($this->_tableName, "uid=:uid", array($uid));
	}
	
	/**
	 * 根据uid批量删除记录
	 * @param array $uids
	 * @return bool
	 */
	function deletePayLogsByUids($uids) {
		if (!S::isArray($uids)) return false;
		return pwQuery::delete($this->_tableName, "uid IN (:uid)", array($uids));
	}
	
	/**
	 * 更新支付记录
	 * @param array $fieldData
	 * @param int $id
	 * @return bool
	 */
	function updatePayLog($fieldData, $id) {
		list($fieldData, $id) = array($this->_checkAllowField($fieldData, $this->_allowFields), intval($id));
		if ($id < 1 || !S::isArray($fieldData)) return false;
		return $this->_update($fieldData, $id);
	}
	
	/**
	 * 批量设置状态为已付款
	 * @param array $ids
	 * @return bool
	 */
	function setLogsPayedByIds($ids) {
		if (!S::isArray($ids)) return false;
		$fieldData = array('status' => 2);
		return pwQuery::update($this->_tableName, "{$this->_primaryKey} IN (:{$this->_primaryKey})", array($ids), $fieldData);
	}
	
	/**
	 * 把一定时间内的记录设为无效
	 * @param int $timestamp
	 * @return bool
	 */
	function setPayLogsInvalidUsingTimestamp($uid, $timestamp) {
		list($uid, $timestamp) = array(intval($uid), intval($timestamp));
		if ($uid < 1 || $timestamp < 0) return false;
		$fieldData = array('status' => 3);
		return pwQuery::update($this->_tableName, 'uid=:uid AND status=:status AND createtime<:createtime', array($uid, 1, $timestamp), $fieldData);
	}
	
	/**
	 * 根据id获取支付信息
	 * @param int $id
	 * @return array
	 */
	function getPayLogById($id) {
		$id = intval($id);
		if ($id < 1) return array();
		return $this->_get($id);
	}
	
	/**
	 * 根据id批量获取支付信息
	 * @param array $ids
	 * @return array
	 */
	function getPayLogByIds($ids) {
		if (!S::isArray($ids)) return array();
		$query = $this->_db->query(pwQuery::selectClause($this->_tableName, "{$this->_primaryKey} IN (:{$this->_primaryKey})", array($ids)));
		return $this->_getAllResultFromQuery($query, 'id');
	}
	
	/**
	 * 根据uid获取所有未付款的记录
	 * @param int $uid
	 * @return array
	 */
	function getUnPayedLogsByUid($uid) {
		$uid = intval($uid);
		if ($uid < 1) return array();
		$query = $this->_db->query(pwQuery::buildClause("SELECT l.*, s.name, s.price, s.discount,s.day FROM $this->_tableName l LEFT JOIN pw_kmd_spread s USING(sid) WHERE l.uid=:uid AND l.status=:status ORDER BY l.createtime DESC", array($uid, 1)));
		return $this->_getAllResultFromQuery($query, 'id');
	}
	
	/**
	 * 根据uid获取所有记录
	 * @param array $uid
	 * @return array
	 */
	function getPayLogsByUid($uid) {
		$uid = intval($uid);
		if ($uid < 1) return array();
		$query = $this->_db->query(pwQuery::selectClause($this->_tableName, "uid=:uid", array($uid), array(PW_ORDERBY => array('createtime' => PW_DESC))));
		return $this->_getAllResultFromQuery($query, 'uid');
	}
	
	/**
	 * 根据uid分页获取记录
	 * @param int $uid
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	function getPayLogsByUidWithLimit($uid, $start, $limit) {
		list($uid, $start, $limit) = array(intval($uid), intval($start), intval($limit));
		if ($uid < 1) return array();
		$query = $this->_db->query(pwQuery::selectClause($this->_tableName, "uid=:uid", array($uid), array(PW_ORDERBY => array('createtime' => PW_DESC), PW_LIMIT => array($start, $limit))));
		return $this->_getAllResultFromQuery($query, 'uid');
	}
	
	/**
	 * 根据uid统计记录数量
	 * @param int $uid
	 * @return int
	 */
	function countPayLogsByUid($uid) {
		$uid = intval($uid);
		if ($uid < 1) return false;
		$total = $this->_db->get_value(pwQuery::selectClause($this->_tableName, '', array(), array(PW_EXPR => array('COUNT(*) AS total'))));
		return $total;
	}
	
	/**
	 * 统计续费了但是已过期的孔明灯数量
	 * @param int $fid
	 * @return int
	 */
	function countRenewedButExpiredNum($fid) {
		$fid = intval($fid);
		if ($fid < 1) return false;
		$total = $this->_db->get_value("SELECT COUNT(*) AS total FROM $this->_tableName l LEFT JOIN pw_kmd_info i USING(kid) WHERE l.fid =" . S::sqlEscape($fid) . " AND l.status = 1 AND l.kid != 0 AND l.uid != i.uid" );
		return $total;
	}
	
	/**
	 * 根据条件统计收入
	 * @param int $fid
	 * @param int $uid
	 * @param int $starttime
	 * @param int $endtime
	 * @return int
	 */
	function countKmdIncomeWithCondition($fid, $uid, $starttime, $endtime) {
		list($fid, $uid, $starttime, $endtime, $sql) = array(intval($fid), intval($uid), intval($starttime), intval($endtime), ' AND status='.KMD_PAY_STATUS_PAYED);
		$fid && ($sql .= S::isArray($fid) ? ' AND fid IN(' . S::sqlImplode($fid) . ')' : ' AND fid=' . S::sqlEscape($fid));
		$uid && $sql .= ' AND uid=' . S::sqlEscape($uid);
		$starttime && $sql .= ' AND createtime>' . S::sqlEscape($starttime);
		$endtime && $sql .= ' AND createtime<' . S::sqlEscape($endtime);
		return $this->_db->get_value("SELECT SUM(money) AS total FROM $this->_tableName WHERE 1 $sql");
	}
	
	/**
	 * 根据条件获取记录
	 * @param int $start
	 * @param int $limit
	 * @param int $fid
	 * @param int $uid
	 * @param int $status
	 * @param int $starttime
	 * @param int $endtime
	 * @return array
	 */
	function getPayLogsWithCondition($start, $limit, $fid, $uid, $status, $starttime, $endtime, $kid = null) {
		list($start, $limit, $fid, $uid, $status, $starttime, $endtime, $sql) = array(intval($start), intval($limit), intval($fid), intval($uid), intval($status), intval($starttime), intval($endtime), '');
		$fid && ($sql .= S::isArray($fid) ? ' AND p.fid IN(' . S::sqlImplode($fid) . ')' : ' AND p.fid=' . S::sqlEscape($fid));
		$uid && $sql .= ' AND p.uid=' . S::sqlEscape($uid);
		$status && $sql .= ' AND p.status=' . S::sqlEscape($status);
		$starttime && $sql .= ' AND p.createtime>' . S::sqlEscape($starttime);
		$endtime && $sql .= ' AND p.createtime<' . S::sqlEscape($endtime);
		!is_null($kid) && $sql .= ' AND kid=' . S::sqlEscape($kid);
		$query = $this->_db->query("SELECT p.* FROM $this->_tableName p WHERE 1 $sql ORDER BY p.createtime DESC " . S::sqlLimit($start, $limit));
		return $this->_getAllResultFromQuery($query, 'id');
	}
	
	/**
	 * 根据条件统计记录数量
	 * @param int $start
	 * @param int $limit
	 * @param int $fid
	 * @param int $uid
	 * @param int $status
	 * @param int $starttime
	 * @param int $endtime
	 * @return int
	 */
	function countPayLogsWithCondition($fid, $uid, $status, $starttime, $endtime, $kid = null) {
		list($fid, $uid, $status, $starttime, $endtime, $sql) = array(intval($fid), intval($uid), intval($status), intval($starttime), intval($endtime), '');
		$fid && ($sql .= S::isArray($fid) ? ' AND fid IN(' . S::sqlImplode($fid) . ')' : ' AND fid=' . S::sqlEscape($fid));
		$uid && $sql .= ' AND uid=' . S::sqlEscape($uid);
		$status && $sql .= ' AND status=' . S::sqlEscape($status);
		$starttime && $sql .= ' AND createtime>' . S::sqlEscape($starttime);
		$endtime && $sql .= ' AND createtime<' . S::sqlEscape($endtime);
		!is_null($kid) && $sql .= ' AND kid=' . S::sqlEscape($kid);
		$total = $this->_db->get_value("SELECT COUNT(*) AS total FROM $this->_tableName WHERE 1 $sql");
		return $total;
	}
}
?>