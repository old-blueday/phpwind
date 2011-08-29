<?php
!defined('P_W') && exit('Forbidden');

class PW_PurviewDB extends BaseDB {
	var $_tableName = "pw_cms_purview";
	var $_primaryKey = 'purview_id';

	function insert($fieldData) {
		$fieldData = $this->_cookFiledData($fieldData);
		return $this->_insert($fieldData);
	}

	function update($fieldData, $id) {
		$fieldData = $this->_cookFiledData($fieldData);
		return $this->_update($fieldData, $id);
	}

	function delete($id) {
		return $this->_delete($id);
	}

	function get($id) {
		$temp = $this->_get($id);
		return $this->_unserializeData($temp);
	}

	function count() {
		return $this->_count();
	}

	function findAll($array, $page, $perPage = 20) {
		$_sql_add = $this->_getSearchSQL($array);
		if ($perPage) $limit = S::sqlLimit(($page - 1) * $perPage, $perPage);
		$_sql = "SELECT * FROM " . $this->_tableName . " WHERE 1 $_sql_add " . $limit;
		$temp = array();
		$query = $this->_db->query($_sql);
		while ($rt = $this->_db->fetch_array($query)) {
			$temp[] = $this->_unserializeData($rt);
		}
		return $temp;
	}

	function _getSearchSQL($array) {
		if ($array['username']) {return ' AND username=' . S::sqlEscape($array['username']);}
		return ' ';
	}

	function _cookFiledData($fieldData) {
		$fieldData = $this->_serializeData($fieldData);
		$fieldData = $this->_checkAllowField($fieldData, $this->getStruct());
		return $fieldData;
	}

	function _serializeData($array) {
		if ($array['columns'] && is_array($array['columns'])) {
			$array['columns'] = serialize($array['columns']);
		}
		return $array;
	}

	function _unserializeData($data) {
		if ($data['columns']) $data['columns'] = unserialize($data['columns']);
		return $data;
	}

	function getStruct() {
		return array('purview_id', 'username', 'super', 'columns');
	}
}