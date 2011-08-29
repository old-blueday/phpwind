<?php
!defined('P_W') && exit('Forbidden');

class PW_ArticleContentDB extends BaseDB {
	var $_tableName = "pw_cms_articlecontent";
	var $_primaryKey = 'article_id';
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
	
	function _cookFiledData($fieldData) {
		$fieldData = $this->_serializeData($fieldData);
		$fieldData = $this->_checkAllowField($fieldData,$this->getStruct());
		return $fieldData;
	}
	
	function _serializeData($array) {
		if ($array['relatearticle'] && is_array($array['relatearticle'])) {
			$array['relatearticle'] = serialize($array['relatearticle']);
		}
		return $array;
	}
	
	function _unserializeData($array) {
		if ($array['relatearticle']) {
			$array['relatearticle'] = unserialize($array['relatearticle']);
		}
		return $array;
	}

	function getStruct() {
		return array('article_id', 'content','relatearticle');
	}
}