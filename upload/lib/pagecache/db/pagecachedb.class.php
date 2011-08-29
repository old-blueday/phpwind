<?php
!defined('P_W') && exit('Forbidden');
class PW_PageCacheDB extends BaseDB {
	var $_tableName = "pw_pagecache";
	
	function get($sign) {
		$temp = $this->_db->get_one('SELECT * FROM '.$this->_tableName.' WHERE sign='.S::sqlEscape($sign) );
		$temp['data'] = $this->_unserialize($temp['data']);
		return $temp;
	}
	
	function gets($array) {
		$temp = array();
		if (!$array) return array(); 
		$rs = $this->_db->query('SELECT * FROM '.$this->_tableName.' WHERE sign IN ('.S::sqlImplode($array).')');
		while ($rt = $this->_db->fetch_array($rs)) {
			$rt['data'] = $this->_unserialize($rt['data']);
			$temp[$rt['sign']] = $rt; 
		}
		return $temp;
	}
	
	function insert($array) {
		$array	= $this->_checkData($array);
		if (!$array) return null;
		$this->_db->update('INSERT INTO '.$this->_tableName.' SET '.S::sqlSingle($array,false));
		return $this->_db->insert_id();
	}
	function replace($array) {
		$array	= $this->_checkData($array);
		if (!$array) return null;
		$this->_db->update('REPLACE INTO '.$this->_tableName.' SET '.S::sqlSingle($array,false));
	}
	function updates($array){
		foreach ($array as $key=>$value) {
			$array[$key]['data'] = $this->_serialize($value['data']);
		}
		$this->_db->update("REPLACE INTO ".$this->_tableName." (sign,data,cachetime) VALUES " . S::sqlMulti($array,false));
	}
	function update($sign,$array){
		$array	= $this->_checkData($array);
		if (!$array) return null;
		$this->_db->update('UPDATE '.$this->_tableName.' SET '.S::sqlSingle($array,false).' WHERE sign='.S::sqlEscape($sign));
	}
	function delete($sign) {
		$this->_db->update('DELETE FROM '.$this->_tableName.' WHERE sign='.S::sqlEscape($sign));
	}
	function truncate() {
		$this->_db->update('TRUNCATE TABLE '.$this->_tableName);
	}
	function deleteByType($type) {
		$this->_db->update('DELETE FROM '.$this->_tableName.' WHERE type='.S::sqlEscape($type));
	}

	function getStruct() {
		return array('sign','type','data','cachetime');
	}

	function _checkData($data){
		if (!is_array($data) || !count($data)) return null;	
		$data = $this->_checkAllowField($data,$this->getStruct());
		$data['data'] = $this->_serialize($data['data']);
		
		return $data;
	}


}
?>