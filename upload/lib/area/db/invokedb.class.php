<?php
!defined('P_W') && exit('Forbidden');
class PW_InvokeDB extends BaseDB {
	var $_tableName = "pw_invoke";
	
	function getInvokes($page,$prePage) {
		$page = (int) $page;
		$page<=0 && $page =1;
		$query = $this->_db->query("SELECT * FROM ".$this->_tableName." ".S::sqlLimit($page*$prePage,$prePage));
		while ($rt = $this->_db->fetch_array($query)) {
			$rt	= $this->_unserializeData($rt);
			$temp[] = $rt;
		}
		return $temp;
	}

	function getDataByName($name) {
		$temp	= $this->_db->get_one("SELECT * FROM ".$this->_tableName." WHERE name=".S::sqlEscape($name));
		return $temp;
	}

	function getDataById($id) {
		$temp	= $this->_db->get_one("SELECT * FROM ".$this->_tableName." WHERE id=".S::sqlEscape($id));
		if (!$temp) return array();
		return $this->_unserializeData($temp);
	}

	function updateByName($name,$array) {
		$array	= $this->_checkData($array);
		if (!$array) {
			return null;
		}
		$this->_db->update("UPDATE ".$this->_tableName." SET ".S::sqlSingle($array,false)." WHERE name=".S::sqlEscape($name));
	}

	function count($type='') {
		$sqladd = '';
		if ($type) {
			$sqladd = $this->_getSqlAdd($type);
			if (!$sqladd) return 0;
		}
		return $this->_db->get_value("SELECT COUNT(*) AS count FROM ".$this->_tableName." $sqladd");
	}

	function insertData($array) {
		$array	= $this->_checkData($array);
		if (!$array || !$array['name']) {
			return null;
		}
		$this->_db->update("INSERT INTO ".$this->_tableName." SET ".S::sqlSingle($array,false));
		return $this->_db->insert_id();
	}
	function replaceData($array) {
		$array	= $this->_checkData($array);
		if (!$array || !$array['name']) {
			return null;
		}
		$this->_db->update("REPLACE INTO ".$this->_tableName." SET ".S::sqlSingle($array,false));
		return $this->_db->insert_id();
	}
	function deleteByName($name){
		$this->_db->update("DELETE FROM ".$this->_tableName." WHERE name=".S::sqlEscape($name));
	}
	function deleteByNames($names){
		if (!is_array($names) || !$names) return null;
		$this->_db->update("DELETE FROM ".$this->_tableName." WHERE name IN(".S::sqlImplode($names).")");
	}

	function getDatesByNames($names) {
		if (!is_array($names) || !$names) return null;
		$temp	= array();
		$query	= $this->_db->query("SELECT * FROM ".$this->_tableName." WHERE name IN(".S::sqlImplode($names).")");
		while ($rt = $this->_db->fetch_array($query)) {
			$rt	= $this->_unserializeData($rt);
			$temp[] = $rt;
		}
		return $temp;
	}

	/*
	 * private functions
	 */
	function _getSqlAdd($type,$join=false) {
		$sqladd = '';
		$pw_tpl = L::loadDB('Tpl', 'area');
		if ($type) {
			$tplids = $pw_tpl->getTplIdsByType($type);
			if ($tplids) {
				$field 	= $join ? 'i.tplid':'tplid';
				$sqladd = " WHERE $field IN (".S::sqlImplode($tplids).")";
			}
		}
		return $sqladd;
	}
	function getStruct() {
		return array('id','name','tplid','tagcode','parsecode','title');
	}

	function _checkData($data){
		if (!is_array($data) || !count($data)) return null;
		$data = $this->_checkAllowField($data,$this->getStruct());
		$data = $this->_serializeData($data);
		if ($array['descrip'] && strlen($array['descrip'])>255) {
			return null;
		}
		return $data;
	}
	function _serializeData($data) {
		return $data;
	}

	function _unserializeData($data) {
		return $data;
	}
}
?>