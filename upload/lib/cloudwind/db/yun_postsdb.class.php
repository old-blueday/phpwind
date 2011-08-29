<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 回复DAO服务
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/db/yun_basedb.php';
class PW_YUN_PostsDB extends YUN_BaseDB {
	var $_tableName = 'pw_posts';
	var $_primaryKey = 'pid';
	
	function getsByPostIds($postIds, $table) {
		if (! $this->_checkTable ( $table ))
			return false;
		$postIds = (is_array ( $postIds )) ? pwImplode ( $postIds ) : $postIds;
		$query = $this->_db->query ( "SELECT * FROM " . $table . " p  WHERE p.pid in(" . $postIds . ") ORDER BY p.postdate DESC" );
		return $this->_getAllResultFromQuery ( $query );
	}
	function getPostsByRange($start, $end, $table) {
		if (! $this->_checkTable ( $table ))
			return false;
		$query = $this->_db->query ( "SELECT * FROM `" . $table . "` p WHERE p.pid >= " . $this->_addSlashes ( $start ) . " AND p.pid <= " . $this->_addSlashes ( $end ) . " AND p.ifcheck = 1 " );
		return $this->_getAllResultFromQuery ( $query );
	}
	function getMaxPid($table) {
		if (! $this->_checkTable ( $table ))
			return false;
		return $this->_db->get_one ( "SELECT min(pid) AS min,max(pid) AS max FROM " . $table . " limit 1" );
	}
	function _checkTable($table) {
		if ($table === 'pw_posts')
			return true;
		$dbposts = $GLOBALS ['db_plist'];
		if (! $dbposts)
			return false;
		$tables = array ();
		foreach ( $dbposts as $k => $v ) {
			($k > 0) && $tables [] = 'pw_posts' . $k;
		}
		return ($tables && in_array ( $table, $tables )) ? true : false;
	}
	
	function getIdsByRange($tableName, $minId, $maxId) {
		if (! $this->_checkTable ( $tableName ))
			return false;
		$query = $this->_db->query ( "SELECT pid FROM " . $tableName . "  WHERE pid >= " . $this->_addSlashes ( $minId ) . " AND pid <= " . $this->_addSlashes ( $maxId ) );
		return $this->_getAllResultFromQuery ( $query );
	}
}