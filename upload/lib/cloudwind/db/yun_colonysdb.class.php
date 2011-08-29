<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 群组DAO服务
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/db/yun_basedb.php';
class PW_YUN_ColonysDB extends YUN_BaseDB {
	var $_tableName = "pw_colonys";
	var $_primaryKey = 'id';
	function getColonysByPage($page, $perpage) {
		$start = ($page - 1) * $perpage;
		$start = intval ( $start );
		$perpage = intval ( $perpage );
		if ($perpage < 1)
			return false;
		$end = $perpage * $page;
		return $this->_getColonysByPage ( $start, $end );
	}
	function _getColonysByPage($start, $end) {
		$query = $this->_db->query ( "SELECT id,classid,cname FROM " . $this->_tableName . "  WHERE id >= " . $this->_addSlashes ( $start ) . " AND id <= " . $this->_addSlashes ( $end ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getsByColonyIds($ids) {
		$ids = (is_array ( $ids )) ? pwImplode ( $ids ) : $ids;
		$query = $this->_db->query ( "SELECT id,classid,cname FROM " . $this->_tableName . " WHERE id in(" . $ids . ")" );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getIdsByRange($minId, $maxId) {
		$query = $this->_db->query ( "SELECT id FROM " . $this->_tableName . "  WHERE id >= " . $this->_addSlashes ( $minId ) . " AND id <= " . $this->_addSlashes ( $maxId ) );
		return $this->_getAllResultFromQuery ( $query );
	}
}
?>