<?php
!defined('P_W') && exit('Forbidden');
class PW_TplDB extends BaseDB {
	var $_tableName = "pw_tpl";

	function getData($tplid){
		return $this->_db->get_one("SELECT * FROM ".$this->_tableName." WHERE tplid=".S::sqlEscape($tplid));
	}
	function getTplIdsByType($type){
		$temp = array();
		$query = $this->_db->query("SELECT tplid FROM ".$this->_tableName." WHERE type=".S::sqlEscape($type));
		while ($rt = $this->_db->fetch_array($query)) {
			$temp[] = $rt['tplid'];
		}
		return $temp;
	}
	function getDatas($type,$limit){
		if ($type) {
			$sqladd = ' WHERE type='.S::sqlEscape($type);
			if (!$sqladd) return array();
		} else {
			$sqladd = '';
		}
		$temp	= array();
		$query	= $this->_db->query("SELECT tplid,name,descrip,image FROM ".$this->_tableName." $sqladd".$limit);
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

	function count(){
		return $this->_db->get_value("SELECT COUNT(*) AS count FROM ".$this->_tableName."");
	}
	function countByType($type){
		if (!$type) return $this->count();
		return $this->_db->get_value("SELECT COUNT(*) AS count FROM ".$this->_tableName." WHERE type=".S::sqlEscape($type));
	}
	function insertData($array){
		$array = $this->_checkData($array);
		if (!$array['name'] || !$array['tagcode']) {
			Showmsg('tpl_insert_data_error');
		}
		$this->_db->update("INSERT INTO ".$this->_tableName." SET ".S::sqlSingle($array,false));
		return $this->_db->insert_id();
	}
	function updataById($tplid,$array) {
		$array	= $this->_checkData($array);
		if (!$array) return null;
		$this->_db->update("UPDATE ".$this->_tableName." SET ".S::sqlSingle($array,false)." WHERE tplid=".S::sqlEscape($tplid));
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
		return array('tplid','type','name','descrip','tagcode','image');
	}
}
?>