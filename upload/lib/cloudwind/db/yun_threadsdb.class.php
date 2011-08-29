<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 帖子DAO服务
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/db/yun_basedb.php';
class PW_YUN_ThreadsDB extends YUN_BaseDB {
	var $_tableName = 'pw_threads';
	var $_tableName2 = 'pw_tmsgs';
	var $_primaryKey = 'tid';
	function getsBythreadIds($threadIds) {
		$threadIds = (is_array ( $threadIds )) ? $threadIds : explode ( ",", $threadIds );
		foreach ( $threadIds as $threadId ) {
			$table = GetTtable ( $threadId );
			$tables [$table] [] = $threadId;
		}
		$threads = array ();
		foreach ( $tables as $table => $tids ) {
			$t = $this->_getsBythreadIds ( $tids, $table );
			$threads = $threads + $t;
		}
		$tmp = array ();
		foreach ( $threads as $t ) {
			$tmp [$t ['tid']] = $t;
		}
		$result = array ();
		foreach ( $threadIds as $threadId ) {
			(isset ( $tmp [$threadId] )) ? $result [] = $tmp [$threadId] : '';
		}
		return $result;
	}
	
	function _getsBythreadIds($threadIds, $tmsgsTableName) {
		$this->_tableName2 = ($tmsgsTableName) ? $tmsgsTableName : $this->_tableName2;
		$threadIds = (is_array ( $threadIds )) ? pwImplode ( $threadIds ) : $threadIds;
		$query = $this->_db->query ( "SELECT t.*,th.content FROM " . $this->_tableName . " t left join " . $this->_tableName2 . " th on t.tid=th.tid WHERE  t.tid in(" . $threadIds . ") ORDER BY t.postdate DESC" );
		return $this->_getAllResultFromQuery ( $query );
	}
	function getThreadsByPage($page, $perpage) {
		$start = ($page - 1) * $perpage;
		$start = intval ( $start );
		$perpage = intval ( $perpage );
		if ($perpage < 1)
			return false;
		$end = $perpage * $page;
		$threadIds = $this->_getThreadsByPage ( $start, $end );
		if (! $threadIds)
			return false;
		$tmp = array ();
		foreach ( $threadIds as $t ) {
			$tmp [] = $t ['tid'];
		}
		return $this->getsBythreadIds ( $tmp );
	}
	
	function _getThreadsByPage($start, $end) {
		$query = $this->_db->query ( "SELECT t.tid FROM " . $this->_tableName . " t WHERE t.tid >= " . $this->_addSlashes ( $start ) . " AND t.tid <= " . $this->_addSlashes ( $end ) . " AND t.ifcheck = 1 AND t.fid != 0 " );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getThreadsByForumId($forumId, $period) {
		$forumId = intval ( $forumId );
		return $this->_getThreadsByLastPost ( $period );
	}
	
	function _getThreadsByLastPost($lastPost) {
		$lastPost = intval ( $lastPost );
		$query = $this->_db->query ( "SELECT t.tid FROM " . $this->_tableName . " t WHERE fid != 0 and lastpost > " . $this->_addSlashes ( $lastPost ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getIdsByRange($minId, $maxId) {
		$query = $this->_db->query ( "SELECT t.tid FROM " . $this->_tableName . " t WHERE t.tid >= " . $this->_addSlashes ( $minId ) . " AND t.tid <= " . $this->_addSlashes ( $maxId ) . " AND t.ifcheck = 1 AND t.fid != 0 " );
		return $this->_getAllResultFromQuery ( $query );
	}

}