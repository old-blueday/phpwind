<?php
!defined('P_W') && exit('Forbidden');
@include_once (R_P . 'lib/base/basedb.php');

class PW_DatanalyseDB extends BaseDB {
	var $_tableName = "pw_datanalyse";

	function getMaxNumByActionAndTime($action, $time, $top) {
		empty($top) && $top = 1;
		return $this->_db->get_value("SELECT num FROM $this->_tableName WHERE action = " . S::sqlEscape($action) . " 
						   AND timeunit = " . S::sqlEscape($time) . " ORDER BY num DESC LIMIT $top,1");
	}

	/**
	 * 根据时间获得热榜数据
	 * @param string $action
	 * @param int $time
	 * @param int $num
	 * @return string
	 */
	function getTagsByActionAndTime($action, $num, $time) {
		if (empty($time)) return $this->_getHistoryDataByAction($action, $num);
		return $this->_getDataByTimeAndAction($action, $num, $time);
	}

	/**
	 * 根据时间获得热榜数据
	 * @param string $action
	 * @param int $time
	 * @param int $num
	 * @return array
	 */
	function getTagsByActionsAndTime($actions, $num, $time) {
		if (empty($time)) return $this->_getHistoryDataByActions($actions, $num);
		return $this->_getDataByTimeAndActions($actions, $num, $time);
	}
	
	/**
	 * 根据时间获得热榜数据
	 * @param string $action
	 * @param int $time
	 * @param int $num
	 * @return array
	 */
	function getDataOderByTag($actions, $num, $time) {
		if (empty($time)) return $this->_getHistoryDataByActions($actions, $num);
		return $this->_getDataOderByTag($actions, $num, $time);
	}

	/**
	 * @param array $actions
	 * @param int $num
	 * @return array:
	 */
	function _getHistoryDataByActions($actions, $num) {
		if (empty($actions) || empty($num)) return array();
		$time = $this->_getHistoryTime();
		$query = $this->_db->query("SELECT a.tag,SUM(a.num) AS nums FROM $this->_tableName a 
			WHERE a.action IN (" . S::sqlImplode($actions) . ") AND a.timeunit = " . S::sqlEscape($time) . " GROUP BY a.tag 
			ORDER BY nums DESC LIMIT 0,$num");
		return $this->_getAllResultFromQuery($query);
	}

	/**
	 * @param string $action
	 * @param int $num
	 * @return array:
	 */
	function _getHistoryDataByAction($action, $num) {
		if (empty($action) || empty($num)) return array();
		$time = $this->_getHistoryTime();
		$query = $this->_db->query("SELECT a.tag,SUM(a.num) AS nums FROM $this->_tableName a 
			WHERE a.action = (" . S::sqlEscape($action) . ") AND a.timeunit = " . S::sqlEscape($time) . " GROUP BY a.tag 
			ORDER BY nums DESC,tag LIMIT 0,$num");
		return $this->_getAllResultFromQuery($query);
	}

	/**
	 * @param array $actions
	 * @param int $num
	 * @param int $time
	 * @return array
	 */
	function _getDataByTimeAndActions($actions, $num, $time) {
		if (empty($actions) || empty($time)) return array();
		$query = $this->_db->query("SELECT a.tag,SUM(a.num) AS nums FROM $this->_tableName a 
			WHERE a.action IN (" . S::sqlImplode($actions) . ") AND a.timeunit >= " . S::sqlEscape($time) . " GROUP BY a.tag 
			ORDER BY nums DESC LIMIT 0,$num");
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * @param array $actions
	 * @param int $num
	 * @param int $time
	 * @return array
	 */
	function _getDataOderByTag($actions, $num, $time) {
		if (empty($actions) || empty($time)) return array();
		$query = $this->_db->query("SELECT a.tag,SUM(a.num) AS nums FROM $this->_tableName a 
			WHERE a.action IN (" . S::sqlImplode($actions) . ") AND a.timeunit >= " . S::sqlEscape($time) . " GROUP BY a.tag 
			ORDER BY a.tag DESC LIMIT 0,$num");
		return $this->_getAllResultFromQuery($query);
	}

	/**
	 * @param string $action
	 * @param int $num
	 * @param int $time
	 * @return array:
	 */
	function _getDataByTimeAndAction($action, $num, $time) {
		$query = $this->_db->query("SELECT a.tag,SUM(a.num) AS nums FROM $this->_tableName a 
			WHERE a.action = " . S::sqlEscape($action) . " AND a.timeunit >= " . S::sqlEscape($time) . " GROUP BY a.tag 
			ORDER BY nums DESC LIMIT 0,$num");
		return $this->_getAllResultFromQuery($query);
	}

	/* 数据清理方法块  */
	
	/**
	 * @param string $action
	 * @param int $time
	 */
	function deleteDataByTimeAndAction($action, $time, $num) {
		return $this->_db->update("DELETE FROM $this->_tableName WHERE timeunit = " . S::sqlEscape($time) . " AND action = " . S::sqlEscape($action) . " AND num < " . S::sqlEscape($num));
	}

	function deleteDataByActionAndTag($action, $tag) {
		return $this->_db->update("DELETE FROM $this->_tableName WHERE action = " . S::sqlEscape($action) . " AND tag IN ( " . S::sqlImplode($tag) . ")");
	}

	/**
	 * 清理某个时间点之前的数据
	 * @param int $time
	 */
	function _deleteDataByTime($time) {
		return $this->_db->update("DELETE FROM $this->_tableName WHERE timeunit <= " . S::sqlEscape($time) . " AND timeunit != " . S::sqlEscape($this->_getHistoryTime()));
	}

	/**
	 * 返回历史时间点
	 * @return int
	 */
	function _getHistoryTime() {
		return mktime(0, 0, 0, 0, 0, 0);
	}

}
?>