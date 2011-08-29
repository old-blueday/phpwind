<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 日志设置DAO服务
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/db/yun_basedb.php';
class PW_YUN_LogSettingDB extends YUN_BaseDB {
	var $_tableName = 'pw_log_setting';
	var $_primaryKey = 'id';
	function replace($id, $vector, $cipher, $hash) {
		if (! $id || ! $vector || ! $cipher || ! $hash)
			return false;
		return $this->_db->query ( "REPLACE INTO " . $this->_tableName . "(id,vector,cipher,field1,field2,field3,field4) VALUES (" . pwEscape ( $id ) . "," . pwEscape ( $vector ) . "," . pwEscape ( $cipher ) . "," . pwEscape ( $hash ) . ",'',0,0)" );
	}
	function get($id) {
		return $this->_get ( $id );
	}
}