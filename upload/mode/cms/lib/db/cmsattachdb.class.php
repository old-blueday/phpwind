<?php
!defined('P_W') && exit('Forbidden');

class PW_CmsAttachDB extends BaseDB {
	var $_tableName = "pw_cms_attach";
	var $_primaryKey = 'attach_id';
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
	
	function findArticleAttaches($articleId) {
		$query = $this->_db->query("SELECT * FROM ".$this->_tableName." WHERE article_id=".S::sqlEscape($articleId));
		return $this->_getAllResultFromQuery($query);
	}

	function getStruct() {
		return array('attach_id','name', 'descrip','article_id','type','size','uploadtime','attachurl','ifthumb');
	}
}