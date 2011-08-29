<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
class GatherQuery_UserDefine_PW_Posts {
	var $_service = null;
	function init() {
		if (! S::isObj ( $this->_service )) {
			$this->_service = new GatherQuery_UserDefine_PW_Posts_Impl ();
		}
	}
	
	function insert($tableName, $fields, $expand = array()) {
		global $db_readperpage;
		$this->_service->logPosts ( 'insert', $fields );
		$this->_service->syncData ( 'insert', $fields );
		$_cacheService = Perf::gatherCache ( 'pw_threads' );
		if (! $fields ['tid'])
			return false;
		$this->_service->clearCacheForLastPage ( $fields );
	}
	
	function update($tableName, $fields, $expand = array()) {
		$this->_service->logPosts ( 'update', $fields );
		$this->_service->syncData ( 'update', $fields );
		$this->_clearCacheForThreadPost ( $tableName, $fields ['pid'] );
	}
	
	function delete($tableName, $fields, $expand = array()) {
		$this->_service->logPosts ( 'delete', $fields );
		$this->_service->syncData ( 'delete', $fields );
		$this->_clearCacheForThreadPost ( $tableName, $fields ['pid'] );
	}
	
	function select($tableName, $fields, $expand = array()) {
	
	}
	
	function _clearCacheForThreadPost($tableName, $pids) {
		$tids = $this->_getTidsByPid ( $tableName, $pids );
		if (perf::checkMemcache () && $tids) {
			foreach ( $tids as $tid ) {
				$this->_service->clearCacheForThreadPost ( $tid );
			}
		}
	}
	
	function _getTidsByPid($tableName, $pids) {
		$pids = (array) $pids;
		$tids = array ();
		if (!$pids) return $tids;
		$query = $GLOBALS ['db']->query ( "SELECT DISTINCT tid FROM $tableName WHERE pid IN(" . S::sqlImplode ( $pids ) . ")" );
		while ( $rt = $GLOBALS ['db']->fetch_array ( $query ) ) {
			$tids [] = $rt ['tid'];
		}
		return $tids;
	}
}

class GatherQuery_UserDefine_PW_Posts_Impl {
	function logPosts($operate, $fields) {
		global $db_operate_log;
		(isset ( $fields ['insert_id'] )) && $fields ['pid'] = $fields ['insert_id'];
		if (! $db_operate_log || ! in_array ( 'log_posts', $db_operate_log ) || ! isset ( $fields ['pid'] )) {
			return false;
		}
		$service = L::loadClass ( 'operatelog', 'utility' );
		$service->logPosts ( $operate, $fields );
	}
	/*
	 * sphinx实时索引扩展  如果需要请部署(insert/update/delete)
	 */
	function syncData($operate, $fields) {
		global $db_sphinx;
		(isset ( $fields ['insert_id'] )) && $fields ['pid'] = $fields ['insert_id'];
		if (! isset ( $db_sphinx ['sync'] ['sync_posts'] ) || ! isset ( $fields ['pid'] )) {
			return false;
		}
		$service = L::loadClass ( 'realtimesearcher', 'search/userdefine' );
		$service->syncData ( 'post', $operate, $fields ['pid'] );
	}
	
	function clearCacheForLastPage($fields) {
		if (! isset ( $fields ['tid'] )) {
			return false;
		}
		$_cacheService = Perf::gatherCache ( 'pw_posts' );
		$_cacheService->clearCacheForLastPage ( $fields ['tid'] );
	}
	
	function clearCacheForThreadPost($tid) {
		$_cacheService = Perf::gatherCache ( 'pw_posts' );
		$_cacheService->clearCacheForThreadPost ( $tid );
	}
}