<?php
!defined('P_W') && exit('Forbidden');

class PW_MedalInfoDB extends BaseDB {
	var $_tableName = 'pw_medal_info';
	var $_primaryKey = 'medal_id';
	function getAll($condition = array(),$order = false) {
		$_sql = $this->_cookSql($condition);
		$_order = $this->_cookOrder($order);
		$query = $this->_db->query("SELECT * FROM ".$this->_tableName." ".$_sql." ORDER BY $_order");
		$temp = array();
		while ($rt = $this->_db->fetch_array($query)) {
			$rt	= $this->_unserializeData($rt);
			$temp[$rt['medal_id']] = $rt;
		}
		return $temp;
	}
	
	function _cookOrder($order) {
		if (!$order) return 'sortorder';
		if ($order = 'confine') return 'confine';
	}
	
	function _cookSql($condition) {
		if (!is_array($condition) || !$condition) return '';
		$_sql = ' WHERE 1 ';
		if (isset($condition['type']) && is_numeric($condition['type'])) {
			$_sql .= ' AND type='.$this->_addSlashes($condition['type']);
		}
		if (isset($condition['is_open']) && is_numeric($condition['is_open'])) {
			$_sql .= ' AND is_open='.$this->_addSlashes($condition['is_open']);
		}
		return $_sql;
	}
	function get($medalId) {
		$medalId = (int) $medalId;
		$temp = $this->_get($medalId);
		return $this->_unserializeData($temp);
	}
	
	function getByIdentify($identify) {
		$temp	= $this->_db->get_one("SELECT * FROM ".$this->_tableName." WHERE identify=".S::sqlEscape($identify));
		if (!$temp) return array();
		return $this->_unserializeData($temp);
	}
	
	function update($medalId, $fieldData) {
		$fieldData = $this->_checkData($fieldData);
		if (!$fieldData) return false;
		return $this->_update($fieldData,$medalId);
	}
	
	function delete($medalId) {
		return $this->_delete($medalId);
	}
	
	function deleteByIdentify($identify) {
		if (!$this->_check() || !$identify) return false;
		return pwQuery::delete($this->_tableName, "identify=:identify", array($identify));
	}
	
	function insert($fieldData) {
		$fieldData = $this->_checkData($fieldData);
		if (!$fieldData) return false;
		return $this->_insert($fieldData);
	}
	
	function _checkData($data){
		if (!is_array($data) || !count($data)) return null;
		$data = $this->_checkAllowField($data,$this->getStruct());
		$data = $this->_serializeData($data);
		if (isset($data['descrip']) && strlen($data['descrip'])>255) {
			return null;
		}
		return $data;
	}
	
	function _serializeData($data) {
		if (isset($data['allow_group']) && is_array($data['allow_group'])) $data['allow_group'] = serialize($data['allow_group']);
		return $data;
	}

	function _unserializeData($data) {
		if ($data['allow_group']) $data['allow_group'] = unserialize($data['allow_group']);
		return $data;
	}
	
	function getStruct() {
		return array('medal_id','identify','name','descrip','type','image','sortorder','is_apply','is_open','allow_group','associate','confine');
	}
}