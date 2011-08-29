<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
class GatherQuery_UserDefine_PW_Space{
	var $_service = null;
	function init() {
		if (! S::isObj ( $this->_service )) {
			$this->_service = new GatherQuery_UserDefine_PW_Space_Impl ();
		}
	}
	function insert($tableName, $fields, $expand = array()) {
		if (perf::checkMemcache ()) {
			$this->_service->clearCacheForSpaceByUid ( $tableName, $fields );
		}
	}
	function replace($tableName, $fields, $expand = array()) {
		if (perf::checkMemcache ()) {
			$this->_service->clearCacheForSpaceByUid ( $tableName, $fields );
		}
	}
	
	function update($tableName, $fields, $expand = array()) {
		if (perf::checkMemcache ()) {
			$this->_service->clearCacheForSpaceByUid ( $tableName, $fields );
		}
	}
	
	function delete($tableName, $fields, $expand = array()) {
		if (perf::checkMemcache ()) {
			$this->_service->clearCacheForSpaceByUid ( $tableName, $fields );
		}
	}
	
	function select($tableName, $fields, $expand = array()) {
	
	}
}

class GatherQuery_UserDefine_PW_Space_Impl {
	
	function clearCacheForSpaceByUid($tableName, $fields){
		if (! isset ( $fields ['uid'] )) {
			return false;
		}
		$cache = Perf::gatherCache('PW_Space');
		$cache->clearCacheForSpaceByUid($fields ['uid']);
	}
}