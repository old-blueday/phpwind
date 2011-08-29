<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 新鲜事DAO服务
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/db/yun_basedb.php';
class PW_YUN_WeibosDB extends YUN_BaseDB {
	var $_tableName = "pw_weibo_content";
	var $_primaryKey = 'mid';
	function getWeibosByPage($page, $perpage) {
		$start = ($page - 1) * $perpage;
		$start = intval ( $start );
		$perpage = intval ( $perpage );
		if ($perpage < 1)
			return false;
		$end = $perpage * $page;
		return $this->_getWeibosByPage ( $start, $end );
	}
	function _getWeibosByPage($start, $end) {
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName . "  WHERE mid >= " . $this->_addSlashes ( $start ) . " AND mid <= " . $this->_addSlashes ( $end ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getsWeibosIds($aids) {
		$aids = (is_array ( $aids )) ? pwImplode ( $aids ) : $aids;
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName . " WHERE mid in(" . $aids . ")" );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getIdsByRange($minId, $maxId) {
		$query = $this->_db->query ( "SELECT mid FROM " . $this->_tableName . "  WHERE mid >= " . $this->_addSlashes ( $minId ) . " AND mid <= " . $this->_addSlashes ( $maxId ) );
		return $this->_getAllResultFromQuery ( $query );
	}
}
?>