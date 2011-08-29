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
		if ($tableName == 'pw_threads' && $GLOBALS['db_hits_store'] == 1) $this->_service->updateHits($fields);
		if (perf::checkMemcache ()) {
			$this->_service->cleanThreadCacheWithForumIds ( $fields );
		}
	}
	
	function update($tableName, $fields, $expand = array()) {
		if (isset ( $expand ['fid'] ) && isset ( $expand ['ifcheck'] ) && $expand ['fid'] == 0 && $expand ['ifcheck'] == 1) {
			$this->_service->logThreads ( 'delete', $fields ); //recycle thread
		} else {
			$this->_service->logThreads ( 'update', $fields );
		}
		$this->_service->syncData ( 'update', $fields );
		if (perf::checkMemcache ()) {
			$this->_service->cleanThreadCacheWithThreadIds ( $tableName, $fields );
			$this->_service->cleanThreadCacheWithForumIds ( $fields, $expand );
		}
		$this->_service->updateThreadImage('update',$fields,$expand);
		//$this->_service->updateThreadsIndexer('update',$fields,$expand);
	}
	
	function delete($tableName, $fields, $expand = array()) {
		$this->_service->logThreads ( 'delete', $fields );
		$this->_service->syncData ( 'delete', $fields );
		if (perf::checkMemcache ()) {
			$this->_service->cleanThreadCacheWithThreadIds ( $tableName, $fields );
			$this->_service->cleanThreadCacheWithForumIds ( $fields );
		}
		$this->_service->updateThreadImage('delete',$fields,$expand);
		//$this->_service->updateThreadsIndexer('delete',$fields,$expand);
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
	
	/**
	 * 当帖子点击数开启使用数据库缓存时，那么每次往pw_threads表插入数据时相应的需要在pw_hits_threads插入一条数据
	 *
	 * @param array $fields
	 * @return boolean
	 */
	function updateHits($fields){
		if (! isset ( $fields ['insert_id'] )) {
			return false;
		}
		return $GLOBALS['db']->update('INSERT INTO pw_hits_threads SET tid='. S::sqlEscape(intval($fields ['insert_id'])) . ',hits='. S::sqlEscape(intval($fields ['hits'])));
	}
	
	function updateThreadImage($operate, $fields,$expand = array()){
		$tids = is_array($fields['tid']) ? $fields['tid'] : array($fields['tid']);
		if (!$tids) return false;
		//删除图酷
		if ($operate == 'delete') {
			return $GLOBALS['db']->update('DELETE FROM pw_threads_img WHERE tid IN ('. S::sqlImplode($tids) . ')');
		}
		//更新图酷
		if($operate == 'update' && (isset($expand['ifcheck']) || isset($expand['topped']) || isset($expand['fid']))){
			isset($expand['ifcheck']) && $GLOBALS['db']->update('UPDATE pw_threads_img SET ifcheck='.intval($expand['ifcheck']).' WHERE tid IN ('. S::sqlImplode($tids) . ')');
			isset($expand['topped']) && $GLOBALS['db']->update('UPDATE pw_threads_img SET topped='.intval($expand['topped']).' WHERE tid IN ('. S::sqlImplode($tids) . ')');
			isset($expand['fid']) && $GLOBALS['db']->update('UPDATE pw_threads_img SET fid='.intval($expand['fid']).' WHERE tid IN ('. S::sqlImplode($tids) . ')');
		}
	}
	
	/**
	 * 更新帖子索引表内容
	 * @param string $operate
	 * @param array $fields
	 * @param array $expand
	 */
	function updateThreadsIndexer($operate, $fields,$expand = array()){
		/*
		if ($operate == 'insert') {
			$threadsIndexerDB = L::loadDB('threadsindexer','forum');
			$threadsIndexerDB->add($fields);
		}*/
		if (!$sqlWhere = $this->_buildWhereStatement($fields)) return false;
		if ($operate == 'delete') {
			return $GLOBALS['db']->update('DELETE FROM pw_threadsindexer '. $sqlWhere);
		}
		if ($operate == 'update') {
			if (!$sqlUpdate = $this->_buildUpdateStatement($expand)) return false;
			return $GLOBALS['db']->update("UPDATE pw_threadsindexer SET $sqlUpdate $sqlWhere");
		}
	}
	
	/**
	 * 组装where条件
	 * @param array $fields
	 * @return string
	 */
	function _buildWhereStatement($fields) {
		if (!S::isArray($fields)) return '';
		$sqlWhere = '';
		foreach ($fields as $k=>$v) {
			$tmpString = S::isArray($v) ? sprintf('%s IN (%s)',$k,S::sqlImplode($v)) : sprintf('%s=%s',$k,S::sqlEscape($v));
			$sqlWhere .= $sqlWhere ? " AND $tmpString" : "WHERE $tmpString";
		}
		return $sqlWhere;
	}
	
	/**
	 * 组装update语句
	 * @param unknown_type $expand
	 */
	function _buildUpdateStatement($expand) {
		$expand = $this->_filterThreadsindexerFields($expand);
		if (!S::isArray($expand)) return '';
		$sqlUpdate = array();
		foreach ($expand as $k=>$v) {
			$sqlUpdate[] = sprintf('%s=%s',$k,S::sqlEscape($v));
		}
		return implode(',' , $sqlUpdate);
	}
	
	function _filterThreadsindexerFields($fields){
		if (!S::isArray($fields)) return false;
		$allowedFields = array('tid','fid','authorid','ifcheck','type','postdate','lastpost','topped','digest','special');
		return array_intersect($allowedFields, $fields);
	}
}