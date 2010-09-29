<?php
!defined('P_W') && exit('Forbidden');
class PW_SchcacheDB extends BaseDB {
	var $_tableName = 'pw_schcache';
	var $_primaryKey = 'sid';
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
	function getBySchline($schline){
		return $this->_db->get_one ( "SELECT * FROM " . $this->_tableName . " WHERE schline = " . $this->_addSlashes ( $schline ) . " LIMIT 1" );
	}
	function deleteBySchtime($schtime){
		$this->_db->update ( "DELETE FROM " . $this->_tableName . " WHERE schtime < " . $this->_addSlashes ( $schtime ) );
		return $this->_db->affected_rows ();
	}

}