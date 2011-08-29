<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
class GatherQuery_UserDefine_PW_Userapp{
	var $_service = null;
	function init() {
		if (! S::isObj ( $this->_service )) {
			$this->_service = new GatherQuery_UserDefine_PW_Userapp_Impl ();
		}
	}
	
	function insert($tableName, $fields, $expand = array()) {
	}
	
	function replace($tableName, $fields, $expand = array()) {
		perf::checkMemcache () && $this->_service->clearCacheByUid ( $tableName, $fields );
	}
	
	function update($tableName, $fields, $expand = array()) {
		perf::checkMemcache () && $this->_service->clearCacheByUid ( $tableName, $fields );
	}
	
	function delete($tableName, $fields, $expand = array()) {
		perf::checkMemcache () && $this->_service->clearCacheByUid ( $tableName, $fields );
	}
	
	function select($tableName, $fields, $expand = array()) {
	
	}
}

class GatherQuery_UserDefine_PW_Userapp_Impl {
	
	function clearCacheByUid($tableName,$fields){
		if (! isset ( $fields ['uid'] )) {
			return false;
		}
		$cache = Perf::gatherCache('PW_Userapp');
		$cache->clearCacheByUid($fields['uid']);
	}
}