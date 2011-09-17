<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND_VERSION_DIR . '/dao/dao_basedb.class.php';
class CloudWind_Platform_LogSettingDb extends CloudWind_Base_Db {
	var $_tableName = 'pw_log_setting';
	var $_primaryKey = 'id';
	function replace($id, $vector, $cipher, $hash) {
		if (! $id || ! $vector || ! $cipher || ! $hash)
			return false;
		return $this->_db->query ( "REPLACE INTO " . $this->_tableName . "(id,vector,cipher,field1,field2,field3,field4) VALUES (" . CLOUDWIND_SECURITY_SERVICE::sqlEscape ( $id ) . "," . CLOUDWIND_SECURITY_SERVICE::sqlEscape ( $vector ) . "," . CLOUDWIND_SECURITY_SERVICE::sqlEscape ( $cipher ) . "," . CLOUDWIND_SECURITY_SERVICE::sqlEscape ( $hash ) . ",'',0,0)" );
	}
	function get($id) {
		return $this->_get ( $id );
	}
}