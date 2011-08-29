<?php
!defined('P_W') && exit('Forbidden');
class PW_PortalPageDB extends BaseDB {
	var $_tableName = "pw_portalpage";

	function getData($sign){
		return $this->_db->get_one("SELECT * FROM ".$this->_tableName." WHERE sign=".S::sqlEscape($sign));
	}

	function getAll() {
		$temp = array();
		$query	= $this->_db->query("SELECT * FROM ".$this->_tableName);
		while ($rt = $this->_db->fetch_array($query)) {
			$temp[] = $rt;
		}
		return $temp;
	}
	
	function deleteBySign($sign) {
		return $this->_db->update('DELETE FROM '.$this->_tableName.' WHERE sign='.S::sqlEscape($sign));
	}

	function add($array){
		$array = $this->_checkData($array);
		if (!$array) return false;
		$this->_db->update("INSERT INTO ".$this->_tableName." SET ".S::sqlSingle($array,false));
		return $this->_db->insert_id();
	}


	function _checkData($array) {
		if (!is_array($array)) return false;
		$strtct = $this->getStruct();
		foreach ($array as $key=>$value) {
			if (!in_array($key,$strtct)) {
				unset($array[$key]);
			}
		}
		return $array;
	}
	function getStruct() {
		return array('id','sign','title');
	}
}
?>