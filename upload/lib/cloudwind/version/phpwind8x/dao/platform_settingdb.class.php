<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND_VERSION_DIR . '/dao/dao_basedb.class.php';
class CloudWind_Platform_SettingDb extends CloudWind_Base_Db {
	var $_tableName = 'pw_yun_setting';
	var $_primaryKey = 'id';
	function replace($id, $setting) {
		if (! $id)
			return false;
		return $this->_db->query ( "REPLACE INTO " . $this->_tableName . "(id,setting) VALUES (" . CLOUDWIND_SECURITY_SERVICE::sqlEscape ( $id ) . "," . CLOUDWIND_SECURITY_SERVICE::sqlEscape ( $setting ) . ")" );
	}
	function update($fields, $id) {
		return $this->_update ( $fields, $id );
	}
	function get($id) {
		return $this->_get ( $id );
	}
}