<?php
!defined('P_W') && exit('Forbidden');
class PW_Ms_TasksDB extends BaseDB {
	var $_tableName = 'pw_ms_tasks';
	var $_primaryKey = 'id';
	function insert($fieldData){
		return $this->_insert($fieldData);
	}
	function update($fieldData,$id){
		return $this->_update($fieldData,$id);
	}
	function delete($id){
		return $this->_delete($id);
	}
	function get($id){
		return $this->_get($id);
	}
	function count(){
		return $this->_count();
	}
	function addTasks($fieldDatas){
		return $this->_db->update("INSERT INTO ".$this->_tableName." (oid,mid,created_time) VALUES  ".S::sqlMulti($fieldDatas,FALSE));
	}
	function getsByCreateTime($groupIds,$createTime){
		if(!is_array($groupIds)) return false;
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName. " WHERE created_time > " .$this->_addSlashes($createTime)." AND oid in(".S::sqlImplode($groupIds).")");
		return $this->_getAllResultFromQuery ( $query );
	}
}