<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND_VERSION_DIR . '/dao/dao_basedb.class.php';
class CloudWind_Search_ForumsDb extends CloudWind_Base_Db {
	var $_tableName = 'pw_forums';
	var $_primaryKey = 'fid';
	
	function getForumsByPage($page, $perpage) {
		list ( $start, $end ) = $this->_getRange ( $page, $perpage );
		return $this->_getForumsByPage ( $start, $end );
	}
	function _getForumsByPage($start, $end) {
		$query = $this->_db->query ( "SELECT fid,name,descrip FROM " . $this->_tableName . "  WHERE fid >= " . $this->_addSlashes ( $start ) . " AND fid <= " . $this->_addSlashes ( $end ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getsByForumIds($forumIds) {
		$forumIds = (is_array ( $forumIds )) ? CLOUDWIND_SECURITY_SERVICE::sqlImplode ( $forumIds ) : $forumIds;
		$query = $this->_db->query ( "SELECT fid,name,descrip FROM " . $this->_tableName . " WHERE fid in(" . $forumIds . ")" );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getIdsByRange($minId, $maxId) {
		$query = $this->_db->query ( "SELECT fid FROM " . $this->_tableName . "  WHERE fid >= " . $this->_addSlashes ( $minId ) . " AND fid <= " . $this->_addSlashes ( $maxId ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function maxForumId() {
		$result = $this->_db->get_one ( "SELECT max(fid) as max FROM $this->_tableName" );
		return  ($result && $result ['max'] > 0) ? $result ['max'] : 0;
	}
	
	function countForumsNum() {
		$result = $this->_db->get_one ( "SELECT count(fid) as total FROM $this->_tableName" );
		return ($result && $result ['total'] > 0) ? $result ['total'] : 0;
	}
}