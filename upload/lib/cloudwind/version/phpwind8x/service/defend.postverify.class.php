<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/core/public/core.service.class.php';
class CloudWind_Defend_PostVerify extends CloudWind_Core_Service {
	
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
			$threads = $this->getThreadsByThreadIds ( $tids );
		}
		if ($pids) {
			$posts = $this->getPostsByPostIds ( $pids );
		}
		return array_merge ( $threads, $posts );
	}
	
	function verify($operate, $tid, $pid) {
		$tid = intval ( $tid );
		$pid = intval ( $pid );
		if ($operate == 2) {
			$this->deletePost ( $tid, $pid );
		} elseif ($operate == 1) {
			$this->verifyPost ( $tid, $pid );
		}
		$this->deletePostVerifyByTidAndPid ( $tid, $pid );
		return true;
	}
	
	function countPostVerify() {
		$postVerifyDao = $this->getPostVerifyDao ();
		return $postVerifyDao->count ();
	}
	
	function deletePostVerifyByTidAndPid($tid, $pid) {
		$postVerifyDao = $this->getPostVerifyDao ();
		return $postVerifyDao->deleteByTidAndPid ( $tid, $pid );
	}
	
	function getThreadsByThreadIds($threadIds) {
		$threadService = $this->_getThreadService ();
		return $threadService->getThreadsByThreadIds ( $threadIds );
	}
	
	function getPostsByPostIds($postIds) {
		$postService = $this->_getPostService ();
		$tables = $posts = array ();
		foreach ( $postIds as $v ) {
			$postTable = GetPtable ( 'N', $v ['tid'] );
			$tables [$postTable] [] = $v ['pid'];
		}
		foreach ( $tables as $tableName => $pids ) {
			$result = $postService->getsByPostIds ( $pids, $tableName );
			$result && $posts = array_merge ( $posts, $result );
		}
		return $posts;
	}
	
	function verifyPost($tid, $pid) {
		if ($pid && $tid) {
			$postTable = GetPtable ( 'N', $tid );
			$postService = $this->_getPostService ();
			return $postService->setPostCheckedByPid ( $pid, $postTable );
		}
		$threadService = $this->_getThreadService ();
		($tid) && $threadService->setThreadCheckedByTid ( $tid );
		return true;
	}
	
	function deletePost($tid, $pid) {
		if ($pid && $tid) {
			$postTable = GetPtable ( 'N', $tid );
			$postService = $this->_getPostService ();
			return $postService->deletePostByPid ( $pid, $postTable );
		}
		$threadService = $this->_getThreadService ();
		($tid) && $threadService->deleteThreadByTid ( $tid );
		return true;
	}
	
	function _getPostService() {
		$serviceFactory = $this->_getServiceFactory ();
		return $serviceFactory->getSearchPostService ();
	}
	
	function _getThreadService() {
		$serviceFactory = $this->_getServiceFactory ();
		return $serviceFactory->getSearchThreadService ();
	}
	
	function getPostVerifyDao() {
		static $dao = null;
		if (! $dao) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/dao_factory.class.php';
			$factory = new CloudWind_Dao_Factory ();
			$dao = $factory->getDefendPostVerifyDao ();
		}
		return $dao;
	}
	
	function _getServiceFactory() {
		static $serviceFactory = null;
		if (! $serviceFactory) {
			require_once CLOUDWIND_VERSION_DIR . '/service/service.factory.class.php';
			$serviceFactory = new CloudWind_Service_Factory ();
		}
		return $serviceFactory;
	}
}