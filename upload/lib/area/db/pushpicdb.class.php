<?php
!defined('P_W') && exit('Forbidden');
class PW_PushPicDB extends BaseDB {
	var $_tableName = "pw_pushpic";

	function getData($id){
		return $this->_db->get_one("SELECT * FROM ".$this->_tableName." WHERE id=".S::sqlEscape($id));
	}
	function getDatasByInvokePiece($invokePieceId){
		$temp = array();
		$query = $this->_db->query("SELECT * FROM ".$this->_tableName." WHERE invokepieceid=".S::sqlEscape($invokePieceId));
		while ($rt = $this->_db->fetch_array($query)) {
			$temp[] = $rt;
		}
		return $temp;
	}

	function getAll() {
		$query	= $this->_db->query("SELECT * FROM ".$this->_tableName);
		while ($rt = $this->_db->fetch_array($query)) {
			$temp[] = $rt;
		}
		return $temp;
	}

	function add($array){
		$array = $this->_checkData($array);
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
		return array('id','path','invokepieceid','creator','createtime');
	}
}
?>