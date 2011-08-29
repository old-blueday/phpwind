<?php
!defined('P_W') && exit('Forbidden');
@include_once (R_P . 'lib/base/basedb.php');

class PW_OwritedataDB extends BaseDB {
	var $_tableName = 'pw_owritedata';

	/**
	 * 获得最新记录数据
	 * @param int $num
	 * @return array:
	 */
	function getNewData($num) {
		$query = $this->_db->query("SELECT w.*, m.* FROM $this->_tableName w LEFT JOIN pw_members m
			ON w.uid=m.uid ORDER BY w.id DESC LIMIT 0,$num ");
		return $this->_getAllResultFromQuery($query);
	}

	/**
	 * 根据IDs获得记录信息
	 * @param array $ids
	 * @return multitype:
	 */
	function getDataByIds($ids) {
		if (empty($ids) || !is_array($ids)) return array();
		$query = $this->_db->query("SELECT w.*, m.* FROM $this->_tableName w LEFT JOIN pw_members m 
			ON w.uid=m.uid WHERE w.id IN (" . S::sqlImplode($ids) . ") ORDER BY w.id DESC");
		return $this->_getAllResultFromQuery($query);
	}

}
?>