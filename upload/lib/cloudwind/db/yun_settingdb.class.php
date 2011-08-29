<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 配置DAO服务
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-7-7
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/db/yun_basedb.php';
class PW_YUN_SettingDB extends YUN_BaseDB {
	var $_tableName = 'pw_yun_setting';
	var $_primaryKey = 'id';
	function replace($id, $setting) {
		if (! $id)
			return false;
		return $this->_db->query ( "REPLACE INTO " . $this->_tableName . "(id,setting) VALUES (" . pwEscape ( $id ) . "," . pwEscape ( $setting ) . ")" );
	}
	function update($fields, $id) {
		return $this->_update ( $fields, $id );
	}
	function get($id) {
		return $this->_get ( $id );
	}
}