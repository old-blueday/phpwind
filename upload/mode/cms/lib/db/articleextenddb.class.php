<?php
!defined('P_W') && exit('Forbidden');

class PW_ArticleExtendDB extends BaseDB {
	var $_tableName = "pw_cms_articleextend";
	var $_primaryKey = 'article_id';
	function insert($fieldData) {
		$fieldData = $this->_checkAllowField($fieldData,$this->getStruct());
		return $this->_insert($fieldData);
	}

	function update($fieldData, $id) {
		$fieldData = $this->_checkAllowField($fieldData,$this->getStruct());
		return $this->_update($fieldData, $id);
	}

	function delete($id) {
		return $this->_delete($id);
	}

	function get($id) {
		$temp = $this->_get($id);
		return $temp;
	}

	function getStruct() {
		return array('article_id', 'hits');
	}
}