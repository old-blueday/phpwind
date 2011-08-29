<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
class GatherQuery_UserDefine_PW_Forums {
	var $_service = null;
	function init() {
		if (! S::isObj ( $this->_service )) {
			$this->_service = new GatherQuery_UserDefine_PW_Forums_Impl ();
		}
	}
	
	function insert($tableName, $fields, $expand = array()) {
		$this->_service->logForums ( 'insert', $fields );
	}
	
	function update($tableName, $fields, $expand = array()) {
		$this->_service->logForums ( 'update', $fields );
		if (Perf::checkMemcache()){
			$this->_service->clearForumsCache($tableName, $fields);
		}
	}
	
	function delete($tableName, $fields, $expand = array()) {
		$this->_service->logForums ( 'delete', $fields );
		if (Perf::checkMemcache()){
			$this->_service->clearForumsCache($tableName, $fields);
		}		
	}
	
	function select($tableName, $fields, $expand = array()) {
	
	}
}

class GatherQuery_UserDefine_PW_Forums_Impl {
	function logForums($operate, $fields) {
		global $db_operate_log;
		(isset ( $fields ['insert_id'] )) && $fields ['fid'] = $fields ['insert_id'];
		if (! $db_operate_log || ! in_array ( 'log_forums', $db_operate_log ) || ! isset ( $fields ['fid'] )) {
			return false;
		}
		$service = L::loadClass ( 'operatelog', 'utility' );
		$service->logForums ( $operate, $fields );
	}
	
	function clearForumsCache($tableName, $fields){
		$fids = isset($fields ['fid']) ? ((is_array ( $fields ['fid'] )) ? $fields ['fid'] : array ($fields ['fid'] )) : null;
		$_cacheService = Perf::getCacheService();
		switch ($tableName){
			case 'pw_forums':
				$_cacheService->delete('all_forums_info');
				break;
			case 'pw_forumdata':
				$_cacheService->delete('all_forums_info');
				if ($fids){
					foreach ($fids as $fourmId)
					$_cacheService->delete('forumdata_announce_' . $fourmId);
					break;
				}
			case 'pw_announce':
				$query = $GLOBALS['db']->query('SELECT fid FROM pw_forumdata');
				while($rt = $GLOBALS['db']->fetch_array($query)){
					$_cacheService->delete('forumdata_announce_' . $rt['fid']);
				}
				break;
			default:
				break;
		}
	}
}