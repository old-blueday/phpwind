<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
class GatherQuery_UserDefine_PW_Cache{
	var $_service = null;
	function init() {
		if (! S::isObj ( $this->_service )) {
			$this->_service = new GatherQuery_UserDefine_PW_Cache_Impl ();
		}
	}
	
	function insert($tableName, $fields, $expand = array()) {
	}
	
	function replace($tableName, $fields, $expand = array()) {
		perf::checkMemcache () && $this->_service->clearCacheByName ( $tableName, $fields );
	}
	
	function update($tableName, $fields, $expand = array()) {
		perf::checkMemcache () && $this->_service->clearCacheByName ( $tableName, $fields );
	}
	
	function delete($tableName, $fields, $expand = array()) {
		perf::checkMemcache () && $this->_service->clearCacheByName ( $tableName, $fields );
	}
	
	function select($tableName, $fields, $expand = array()) {
	
	}
}

class GatherQuery_UserDefine_PW_Cache_Impl {
	
	function clearCacheByName($tableName,$fields){
		if (! isset ( $fields ['name'] )) {
			return false;
		}
		$cache = Perf::gatherCache('PW_Cache');
		$cache->clearCacheByName($fields['name']);
	}
}