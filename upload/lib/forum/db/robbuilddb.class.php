<?php
!defined('P_W') && exit('Forbidden');

class PW_RobBuildDb extends BaseDB {
	var $_tableName  = 'pw_robbuild';
	var $_primaryKey = 'tid';
	
	function get($tid){
		$tid = intval($tid);
		if ($tid < 1) return false;
		return $this->_get($tid);
	}
	function add($data) {
		if (!S::isArray($data)) return false;
		return $this->_insert($data);
	}
	function update($fieldsData ,$tid) {
		$tid = intval($tid);
		if ($tid < 1 || !S::isArray($fieldsData)) return false;
		return $this->_update($fieldsData ,$tid);
	}
	function delete($tid){
		$tid = intval($tid);
		if ($tid < 1) return false;
		return $this->_delete($tid);
	}
	
	/**
	 * 根据tids批量删除数据
	 * 
	 * @param int $tid
	 * @return array 
	 */
	function deleteByTids($tids){
		if (!S::isArray($tids)) return false;
		return $this->_db->update("DELETE FROM " . $this->_tableName . " WHERE tid IN (" . S::sqlImplode($tids) . ")");
	}
}
?>