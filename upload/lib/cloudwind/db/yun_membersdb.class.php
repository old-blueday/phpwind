<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 用户DAO服务
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/db/yun_basedb.php';
class PW_YUN_MembersDB extends YUN_BaseDB {
	var $_tableName = "pw_members";
	var $_primaryKey = 'uid';
	function getsByUserIds($userId) {
		$userId = (is_array ( $userId )) ? pwImplode ( $userId ) : $userId;
		$query = $this->_db->query ( "SELECT uid,username,regdate FROM " . $this->_tableName . " WHERE uid in(" . $userId . ")" );
		return $this->_getAllResultFromQuery ( $query );
	}
	function getMembersByPage($page, $perpage) {
		$start = ($page - 1) * $perpage;
		$start = intval ( $start );
		$perpage = intval ( $perpage );
		if ($perpage < 1)
			return false;
		$end = $perpage * $page;
		return $this->_getMembersByPage ( $start, $end );
	}
	function _getMembersByPage($start, $end) {
		$query = $this->_db->query ( "SELECT uid,username,regdate FROM " . $this->_tableName . "  WHERE uid >= " . $this->_addSlashes ( $start ) . " AND uid <= " . $this->_addSlashes ( $end ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getIdsByRange($minId, $maxId) {
		$query = $this->_db->query ( "SELECT uid FROM " . $this->_tableName . "  WHERE uid >= " . $this->_addSlashes ( $minId ) . " AND uid <= " . $this->_addSlashes ( $maxId ) );
		return $this->_getAllResultFromQuery ( $query );
	}
}
?>