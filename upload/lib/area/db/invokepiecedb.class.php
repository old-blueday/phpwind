<?php
!defined('P_W') && exit('Forbidden');
class PW_InvokePieceDB extends BaseDB {
	var $_tableName = "pw_invokepiece";

	function getDataById($id) {
		$temp = $this->_db->get_one("SELECT * FROM ".$this->_tableName." WHERE id=".S::sqlEscape($id));
		if (!$temp) return array();
		return $this->_unserializeData($temp);
	}
	function getDatasByIds($ids) {
		if (!is_array($ids) || !$ids) return array();
		$temp	= array();
		$query	= $this->_db->query("SELECT * FROM ".$this->_tableName." WHERE id IN(".S::sqlImplode($ids).")");
		while ($rt = $this->_db->fetch_array($query)) {
			$temp[$rt['id']]	= $this->_unserializeData($rt);
		}
		return $temp;
	}

	function getDatasByInvokeName($invokename) {
		$temp = array();
		$query = $this->_db->query("SELECT * FROM ".$this->_tableName." WHERE invokename=".S::sqlEscape($invokename));
		while ($rt = $this->_db->fetch_array($query)) {
			$temp[$rt['id']]	= $this->_unserializeData($rt);
		}
		return $temp;
	}

	function getDatasByInvokeNames($names) {
		if (!is_array($names) || !$names) return null;
		$temp = array();
		$query = $this->_db->query("SELECT * FROM ".$this->_tableName." WHERE invokename IN(".S::sqlImplode($names).")");
		while ($rt = $this->_db->fetch_array($query)) {
			$temp[]	= $this->_unserializeData($rt);
		}
		return $temp;
	}

	function getDataByInvokeNameAndTitle($invokename,$title) {
		$temp = $this->_db->get_one("SELECT * FROM ".$this->_tableName." WHERE invokename=".S::sqlEscape($invokename)."AND title=".S::sqlEscape($title));
		if (!$temp) return array();
		return $this->_unserializeData($temp);
	}

	function updateById($id,$array) {
		$array	= $this->_checkData($array);
		if (!$array) return null;
		$this->_db->update("UPDATE ".$this->_tableName." SET ".S::sqlSingle($array,false)." WHERE id=".S::sqlEscape($id));
	}

	function insertData($array) {
		$array	= $this->_checkData($array);
		if (!$array || !$array['invokename'] || !$array['action'] || !$array['title'] || !$array['num'] || !$array['param']) {
			return null;
		}

		$this->_db->update("INSERT INTO ".$this->_tableName." SET ".S::sqlSingle($array,false));
		return $this->_db->insert_id();
	}
	function replaceData($array) {
		$array	= $this->_checkData($array);
		if (!$array || !$array['invokename'] || !$array['action'] || !$array['title'] || !$array['num'] || !$array['param']) {
			return null;
		}
		$this->_db->update("REPLACE INTO ".$this->_tableName." SET ".S::sqlSingle($array,false));
		return $this->_db->insert_id();
	}
	function deleteByInvokeName($name) {
		$this->_db->update("DELETE FROM ".$this->_tableName." WHERE invokename=".S::sqlEscape($name));
	}

	function deleteByInvokeNames($names){
		if (!is_array($names) || !$names) return null;
		$this->_db->update("DELETE FROM ".$this->_tableName." WHERE invokename IN(".S::sqlImplode($names).")");
	}

	function insertDatas($array) {
		if (!is_array($array)) return false;
		foreach ($array as $key=>$value) {
			if (!is_array($value)) continue;
			$this->insertData($value);
		}
	}

	function deleteById($id) {
		$this->_db->update("DELETE FROM ".$this->_tableName." WHERE id=".S::sqlEscape($id));
	}

	function getStruct() {
		return array('id','invokename','title','action','config','num','param','cachetime','ifpushonly');
	}
	function _checkData($data){
		if (!is_array($data) || !count($data)) return null;
		$data = $this->_checkAllowField($data,$this->getStruct());
		$data = $this->_serializeData($data);
		return $data;
	}
	function _serializeData($data) {
		if (isset($data['param']) && is_array($data['param'])) $data['param'] = serialize($data['param']);
		if (isset($data['config']) && is_array($data['config'])) $data['config'] = serialize($data['config']);
		return $data;
	}

	function _unserializeData($data) {
		if ($data['param']) $data['param'] = unserialize($data['param']);
		if ($data['config']) $data['config'] = unserialize($data['config']);
		return $data;
	}
}
?>