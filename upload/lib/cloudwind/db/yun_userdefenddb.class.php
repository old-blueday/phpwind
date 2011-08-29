<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 云盾用户日志DAO
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-07-01
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/db/yun_basedb.php';
class PW_YUN_UserDefendDB extends YUN_BaseDB {
	
	var $_tableName = 'pw_log_userdefend';
	
	function getAll() {
		$query = $this->_db->query ( "SELECT * FROM `" . $this->_tableName . "`" );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function deleteAll() {
		$this->_db->query ( "DELETE FROM `" . $this->_tableName . "`" );
		return $this->_db->affected_rows ();
	}

}