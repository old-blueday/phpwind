<?php
!defined('P_W') && exit('Forbidden');

class PW_KmdSpreadDB extends BaseDB {
	
	var $_tableName = 'pw_kmd_spread';
	var $_primaryKey = 'sid';
	var $_allowFields = array('sid', 'displayorder', 'name', 'day', 'price', 'discount');
	
	/**
	 * 增加一条套餐记录
	 * @param array $fieldData
	 * @return bool
	 */
	function addSpread($fieldData) {
		$fieldData = $this->_checkAllowField($fieldData, $this->_allowFields);
		if (!S::isArray($fieldData)) return false;
		return $this->_insert($fieldData);
	}
	
	/**
	 * 批量增加套餐信息
	 * @param array $fieldData
	 * @return bool
	 */
	function addSpreads($fieldData) {
		if (!S::isArray($fieldData)) return false;
		$data = array();
		foreach ($fieldData as $value) {
			$value = $this->_checkAllowField($value, $this->_allowFields);
			if (!S::isArray($value)) continue;
			$data[] = $value;
		}
		if (!S::isArray($data)) return false;
		return $this->_db->query(pwQuery::buildClause('INSERT INTO :table VALUES :data', array($this->_tableName, S::sqlMulti($data))));
	}
	
	/**
	 * 根据套餐id删除套餐信息
	 * @param int $sid
	 * @return bool
	 */
	function deleteSpreadBySid($sid) {
		$sid = intval($sid);
		if ($sid < 1) return false;
		return $this->_delete($sid);
	}
	
	/**
	 * 根据套餐id批量删除信息
	 * @param array $sids
	 * @return bool
	 */
	function deleteSpreadBySids($sids) {
		if (!S::isArray($sids)) return false;
		return pwQuery::delete($this->_tableName, "{$this->_primaryKey} IN (:{$this->_primaryKey})", array($sids));
	}
	
	/**
	 * 更新套餐信息
	 * @param array $fieldData
	 * @param int $sid
	 * @return bool
	 */
	function updateSpread($fieldData, $sid) {
		list($fieldData, $sid) = array($this->_checkAllowField($fieldData, $this->_allowFields), intval($sid));
		if ($sid < 1 || !S::isArray($fieldData)) return false;
		return $this->_update($fieldData, $sid);
	}
	
	/**
	 * 根据套餐id获取套餐信息
	 * @param int $sid
	 * @return array
	 */
	function getSpreadBySid($sid) {
		$sid = intval($sid);
		if ($sid < 1) return array();
		return $this->_get($sid);
	}
	
	/**
	 * 根据套餐id批量获取套餐信息
	 * @param array $sids
	 * @return array
	 */
	function getSpreadsBySids($sids) {
		if (!S::isArray($sids)) return array();
		$query = $this->_db->query(pwQuery::selectClause($this->_tableName, "{$this->_primaryKey} IN (:{$this->_primaryKey})", array($sids), array(PW_ORDERBY => array('displayorder' => PW_ASC))));
		return $this->_getAllResultFromQuery($query, 'sid');
	}
	
	/**
	 * 获取所有套餐信息
	 * @return array
	 */
	function getAllSpreads() {
		$query = $this->_db->query(pwQuery::selectClause($this->_tableName, '', array(), array(PW_ORDERBY => array('displayorder' => PW_ASC))));
		return $this->_getAllResultFromQuery($query, 'sid');
	}
	
	/**
	 * 分页获取套餐信息
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	function getAllSpreadsWithLimit($start, $limit) {
		list($start, $limit) = array(intval($start), intval($limit));
		$query = $this->_db->query(pwQuery::selectClause($this->_tableName, '', array(), array(PW_ORDERBY => array('displayorder' => PW_ASC), PW_LIMIT => array($start, $limit))));
		return $this->_getAllResultFromQuery($query, 'sid');
	}
	
	/**
	 * 统计所有套餐数量
	 * @return int
	 */
	function countSpreads() {
		$total = $this->_db->get_value(pwQuery::selectClause($this->_tableName, '', array(), array(PW_EXPR => array('COUNT(*) AS total'))));
		return $total;
	}
}
?>