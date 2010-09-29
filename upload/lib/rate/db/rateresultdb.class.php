<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );

class PW_RateResultDB extends BaseDB {
	var $_tableName = "pw_rateresult";

	function add($fieldData) {
		$this->_db->update ( "INSERT INTO " . $this->_tableName . " SET " . $this->_getUpdateSqlString ( $fieldData ) );
		return $this->_db->insert_id ();
	}

	/**
	 * 根椐选项ID与对象ID获取结果记录
	 *
	 * @param int $optionId
	 * @param int $objectId
	 * @return array
	 */
	function getByOptionId($optionId, $objectId) {
		return $this->_db->get_one ( "SELECT * FROM " . $this->_tableName . " WHERE optionid=" . $this->_addSlashes ( $optionId ) . " AND objectid=" . $this->_addSlashes ( $objectId ) . "  LIMIT 1" );
	}

	/**
	 * 更新某个对象的选项的评价数
	 *
	 * @param int $optionId
	 * @param int $objectId
	 * @return unknown
	 */
	function updateByOptionId($optionId, $objectId) {
		$this->_db->update ( "UPDATE " . $this->_tableName . " SET num=num+1 WHERE optionid=" . $this->_addSlashes ( $optionId ) . " AND objectid=" . $this->_addSlashes ( $objectId ) . "  LIMIT 1" );
		return $this->_db->affected_rows ();
	}

	/**
	 * 获取某个分类的某个对象的所有选择结果
	 *
	 * @param unknown_type $typeId
	 * @param unknown_type $objectId
	 * @return unknown
	 */
	function getByTypeId($typeId, $objectId) {
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName . " WHERE typeid=" . $this->_addSlashes ( $typeId ) . " AND objectid=" . $this->_addSlashes ( $objectId ) );
		return $this->_getAllResultFromQuery ( $query );
	}
}

?>