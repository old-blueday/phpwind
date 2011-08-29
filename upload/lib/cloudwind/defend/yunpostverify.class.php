<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 云盾审核服务中心
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2011-03-15
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/foundation/yunbase.class.php';
class PW_YunPostVerify extends PW_YunBase {
	
	function insertPostVerify($type, $tid, $pid) {
		$postVerifyDao = $this->getPostVerifyDao ();
		return $postVerifyDao->replace ( $type, $tid, $pid );
	}
	function getPostVerify($page, $perpage = 20) {
		list ( $start, $perpage ) = $this->filterPage ( $page, $perpage );
		$postVerifyDao = $this->getPostVerifyDao ();
		$result = $postVerifyDao->gets ( $start, $perpage );
		if (! $result) {
			return array ();
		}
		$tids = $pids = array ();
		foreach ( $result as $v ) {
			if ($v ['type'] == 1) {
				$tids [] = $v ['tid'];
			} else {
				$pids [] = array ('pid' => $v ['pid'], 'tid' => $v ['tid'] );
			}
		}
		$threads = $posts = array ();
		if ($tids) {
			$threadDao = $this->getThreadsDao ();
			$threads = $threadDao->getsBythreadIds ( $tids );
		}
		if ($pids) {
			$postDao = $this->_getPostsDao ();
			$tables = array ();
			foreach ( $pids as $v ) {
				$postTable = GetPtable ( 'N', $v ['tid'] );
				$tables [$postTable] [] = $v ['pid'];
			}
			foreach ( $tables as $tableName => $postIds ) {
				$result = $postDao->getsByPostIds ( $postIds, $tableName );
				$result && $posts = array_merge ( $posts, $result );
			}
		}
		return array_merge ( $threads, $posts );
	}
	
	function countPostVerify() {
		$postVerifyDao = $this->getPostVerifyDao ();
		return $postVerifyDao->count ();
	}
	
	function deletePostVerifyByTidAndPid($tid, $pid) {
		$postVerifyDao = $this->getPostVerifyDao ();
		return $postVerifyDao->deleteByTidAndPid ( $tid, $pid );
	}
	
	function verify($operate, $tid, $pid) {
		$tid = intval ( $tid );
		$pid = intval ( $pid );
		if ($operate == 2) {
			$this->_deletePost ( $tid, $pid );
		} elseif ($operate == 1) {
			$this->_verifyPost ( $tid, $pid );
		}
		$this->deletePostVerifyByTidAndPid ( $tid, $pid );
		return true;
	}
	
	function _verifyPost($tid, $pid) {
		if ($pid && $tid) {
			$postTable = GetPtable ( 'N', $tid );
			return $GLOBALS ['db']->query ( "UPDATE " . S::sqlMetadata ( $postTable ) . " SET ifshield=0 WHERE pid=" . pwEscape ( $pid ) );
		}
		($tid) && $GLOBALS ['db']->query ( "UPDATE pw_threads SET ifcheck=1 WHERE tid=" . pwEscape ( $tid ) );
		return true;
	}
	
	function _deletePost($tid, $pid) {
		if ($pid && $tid) {
			$postTable = GetPtable ( 'N', $tid );
			return $GLOBALS ['db']->query ( "DELETE FROM " . S::sqlMetadata ( $postTable ) . " WHERE pid=" . pwEscape ( $pid ) );
		}
		($tid) && $GLOBALS ['db']->query ( "DELETE FROM  pw_threads WHERE tid=" . pwEscape ( $tid ) );
		return true;
	}
	
	function _getPostsDao() {
		static $sPostsDao;
		if (! $sPostsDao) {
			require_once R_P . 'lib/cloudwind/db/yun_postsdb.class.php';
			$sPostsDao = new PW_YUN_PostsDB ();
		}
		return $sPostsDao;
	}
	function getThreadsDao() {
		static $sThreadsDao;
		if (! $sThreadsDao) {
			require_once R_P . 'lib/cloudwind/db/yun_threadsdb.class.php';
			$sThreadsDao = new PW_YUN_ThreadsDB ();
		}
		return $sThreadsDao;
	}
	
	function filterPage($page, $perpage) {
		$page = intval ( $page ) ? intval ( $page ) : 1;
		$start = ($page - 1) * $perpage;
		$start = intval ( $start );
		$perpage = intval ( $perpage );
		return array ($start, $perpage, $page );
	}
	
	function getPostVerifyDao() {
		static $sPostVerifyDao;
		if (! $sPostVerifyDao) {
			require_once R_P . 'lib/cloudwind/db/yun_postverifydb.class.php';
			$sPostVerifyDao = new PW_YUN_PostVerifyDB ();
		}
		return $sPostVerifyDao;
	}
}