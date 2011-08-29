<?php
!defined('P_W') && exit('Forbidden');
class PW_DiarytypeDB extends BaseDB {
	var $_tableName = 'pw_diarytype';
	var $_primaryKey = 'dtid';
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
	function getsByTdids($tdids){
		$tdids = (is_array($tdids)) ? S::sqlImplode($tdids) : $tdids;
		$query = $this->_db->query ( "SELECT * FROM ".$this->_tableName." WHERE dtid in(".$tdids.")" );
		return $this->_getAllResultFromQuery ( $query );
	}
}