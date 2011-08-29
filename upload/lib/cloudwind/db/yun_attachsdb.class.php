<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 附件DAO服务
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/db/yun_basedb.php';
class PW_YUN_AttachsDB extends YUN_BaseDB {
	var $_tableName = "pw_attachs";
	var $_primaryKey = 'aid';
	function getAttachsByPage($page, $perpage) {
		$start = ($page - 1) * $perpage;
		$start = intval ( $start );
		$perpage = intval ( $perpage );
		if ($perpage < 1)
			return false;
		$end = $perpage * $page;
		return $this->_getAttachsByPage ( $start, $end );
	}
	function _getAttachsByPage($start, $end) {
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName . "  WHERE aid >= " . $this->_addSlashes ( $start ) . " AND aid <= " . $this->_addSlashes ( $end ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getsAttachsIds($aids) {
		$aids = (is_array ( $aids )) ? pwImplode ( $aids ) : $aids;
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName . " WHERE aid in(" . $aids . ")" );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getIdsByRange($minId, $maxId) {
		$query = $this->_db->query ( "SELECT aid FROM " . $this->_tableName . "  WHERE aid >= " . $this->_addSlashes ( $minId ) . " AND aid <= " . $this->_addSlashes ( $maxId ) );
		return $this->_getAllResultFromQuery ( $query );
	}
}
?>