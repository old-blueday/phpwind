<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND_VERSION_DIR . '/dao/dao_basedb.class.php';
class CloudWind_Search_PostsDb extends CloudWind_Base_Db {
	var $_tableName = 'pw_posts';
	var $_primaryKey = 'pid';
	
	function getsByPostIds($postIds, $table) {
		if (! $this->_checkTable ( $table ))
			return false;
		$postIds = (is_array ( $postIds )) ? CLOUDWIND_SECURITY_SERVICE::sqlImplode ( $postIds ) : $postIds;
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
		$dbposts = $GLOBALS['db_plist'];
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
	
	function deletePostByPid($pid, $table) {
		$pid = intval($pid);
		if (! $this->_checkTable ( $table ) || $pid < 1)
			return false;
		$this->_db->update("DELETE FROM " . CLOUDWIND_SECURITY_SERVICE::sqlMetadata($table) . " WHERE pid = " . CLOUDWIND_SECURITY_SERVICE::sqlEscape($pid));
		return $this->_db->affected_rows();
	}
	
	function setPostCheckedByPid($pid, $table) {
		$pid = intval($pid);
		if (! $this->_checkTable ( $table ) || $pid < 1)
			return false;
		$this->_db->update("UPDATE " . CLOUDWIND_SECURITY_SERVICE::sqlMetadata($table) . " SET ifshield = 0 WHERE pid = " . CLOUDWIND_SECURITY_SERVICE::sqlEscape($pid));
		return $this->_db->affected_rows();
	}
	
	function maxPostId($table) {
		if (!in_array($table, array('pw_posts', 'pw_pidtmp'))) return false;
		$result = $this->_db->get_one ( "SELECT max(pid) as max FROM $table" );
		return  ($result && $result ['max'] > 0) ? $result ['max'] : 0;
	}
	
	function countPostsNum($table) {
		if (!in_array($table, array('pw_posts', 'pw_pidtmp'))) return false;
		$result = $this->_db->get_one ( "SELECT max(pid) as total FROM $table" );
		return ($result && $result ['total'] > 0) ? $result ['total'] : 0;
	}
}