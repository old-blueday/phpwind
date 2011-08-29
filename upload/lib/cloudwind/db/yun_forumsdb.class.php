<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 版块DAO服务
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/db/yun_basedb.php';
class PW_YUN_ForumsDB extends YUN_BaseDB {
	var $_tableName = 'pw_forums';
	var $_primaryKey = 'fid';
	
	function getForumsByPage($page, $perpage) {
		$start = ($page - 1) * $perpage;
		$start = intval ( $start );
		$perpage = intval ( $perpage );
		if ($perpage < 1)
			return false;
		$end = $perpage * $page;
		return $this->_getForumsByPage ( $start, $end );
	}
	function _getForumsByPage($start, $end) {
		$query = $this->_db->query ( "SELECT fid,name,descrip FROM " . $this->_tableName . "  WHERE fid >= " . $this->_addSlashes ( $start ) . " AND fid <= " . $this->_addSlashes ( $end ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getsByForumIds($forumIds) {
		$forumIds = (is_array ( $forumIds )) ? pwImplode ( $forumIds ) : $forumIds;
		$query = $this->_db->query ( "SELECT fid,name,descrip FROM " . $this->_tableName . " WHERE fid in(" . $forumIds . ")" );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getIdsByRange($minId, $maxId) {
		$query = $this->_db->query ( "SELECT fid FROM " . $this->_tableName . "  WHERE fid >= " . $this->_addSlashes ( $minId ) . " AND fid <= " . $this->_addSlashes ( $maxId ) );
		return $this->_getAllResultFromQuery ( $query );
	}
}