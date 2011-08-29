<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
class GatherQuery_UserDefine_PW_Threads {
	var $_service = null;
	function init() {
		if (! S::isObj ( $this->_service )) {
			$this->_service = new GatherQuery_UserDefine_PW_Threads_Impl ();
		}
	}
	
	function insert($tableName, $fields, $expand = array()) {
		$this->_service->logThreads ( 'insert', $fields );
		$this->_service->syncData ( 'insert', $fields );
		if (perf::checkMemcache ()) {
			$this->_service->cleanThreadCacheWithForumIds ( $fields );
		}
	}
	
	function update($tableName, $fields, $expand = array()) {
		$this->_service->logThreads ( 'update', $fields );
		$this->_service->syncData ( 'update', $fields );
		if (perf::checkMemcache ()) {
			$this->_service->cleanThreadCacheWithThreadIds ( $tableName, $fields );
			$this->_service->cleanThreadCacheWithForumIds ( $fields, $expand );
		}
	}
	
	function delete($tableName, $fields, $expand = array()) {
		$this->_service->logThreads ( 'delete', $fields );
		$this->_service->syncData ( 'delete', $fields );
		if (perf::checkMemcache ()) {
			$this->_service->cleanThreadCacheWithThreadIds ( $tableName, $fields );
			$this->_service->cleanThreadCacheWithForumIds ( $fields );
		}
	}
	
	function select($tableName, $fields, $expand = array()) {
	
	}
}

class GatherQuery_UserDefine_PW_Threads_Impl {
	/*
	 * 记录帖子更新/删除操作 如果需要请部署(insert/update/delete)
	 */
	function logThreads($operate, $fields) {
		global $db_operate_log;
		(isset ( $fields ['insert_id'] )) && $fields ['tid'] = $fields ['insert_id'];
		if (! $db_operate_log || ! in_array ( 'log_threads', $db_operate_log ) || ! isset ( $fields ['tid'] )) {
			return false;
		}
		$service = L::loadClass ( 'operatelog', 'utility' );
		$service->logThreads ( $operate, $fields );
	}
	/*
	 * sphinx实时索引扩展  如果需要请部署(insert/update/delete)
	 */
	function syncData($operate, $fields) {
		global $db_sphinx;
		(isset ( $fields ['insert_id'] )) && $fields ['tid'] = $fields ['insert_id'];
		if (! isset ( $db_sphinx ['sync'] ['sync_threads'] ) || ! isset ( $fields ['tid'] )) {
			return false;
		}
		$service = L::loadClass ( 'realtimesearcher', 'search/userdefine' );
		$service->syncData ( 'thread', $operate, $fields ['tid'] );
	}
	
	function cleanThreadCacheWithThreadIds($tableName, $fields) {
		if (! isset ( $fields ['tid'] )) {
			return false;
		}
		$threadIds = (is_array ( $fields ['tid'] )) ? $fields ['tid'] : array ($fields ['tid'] );
		$_cacheService = Perf::gatherCache ( 'pw_threads' );
		switch ($tableName) {
			case 'pw_threads' :
				$_cacheService->clearCacheForThreadByThreadIds ( $threadIds );
				break;
			default :
				$_cacheService->clearCacheForTmsgByThreadIds ( $threadIds );
				break;
		}
	}
	
	function cleanThreadCacheWithForumIds($fields, $expand = array()) {
		if (! isset ( $fields ['fid'] ) && ! isset ( $expand ['fid'] )) {
			return false;
		}
		$forumIds = array ();
		isset ( $fields ['fid'] ) && $forumIds = (is_array ( $fields ['fid'] )) ? $fields ['fid'] : array ($fields ['fid'] );
		isset ( $expand ['fid'] ) && $forumIds = array_merge ( $forumIds, (is_array ( $expand ['fid'] )) ? $expand ['fid'] : array ($expand ['fid'] ) );
		$_cacheService = Perf::gatherCache ( 'pw_threads' );
		$_cacheService->clearCacheForThreadListByForumIds ( $forumIds );
	}
}