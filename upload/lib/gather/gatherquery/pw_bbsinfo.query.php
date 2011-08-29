<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
class GatherQuery_UserDefine_PW_BbsInfo {
	var $_service = null;
	function init() {
		if (! S::isObj ( $this->_service )) {
			$this->_service = new GatherQuery_UserDefine_PW_BbsInfo_Impl ();
		}
	}
	
	function insert($tableName, $fields, $expand = array()) {

	}
	
	function update($tableName, $fields, $expand = array()) {
		if (perf::checkMemcache()){
			$this->_service->cleanBbsInfoCacheById ($tableName, $fields );
		}
	}
	
	function delete($tableName, $fields, $expand = array()) {
		if (perf::checkMemcache()){
			$this->_service->cleanBbsInfoCacheById ($tableName, $fields );
		}
	}
	
	function select($tableName, $fields, $expand = array()) {
	
	}
}


class GatherQuery_UserDefine_PW_BbsInfo_Impl {

	function cleanBbsInfoCacheById($tableName, $fields) {
		$id = isset($fields ['id']) ? $fields ['id'] : 1;
		$cache = Perf::gatherCache ( 'pw_bbsinfo' );
		$cache->clearBbsInfoCacheById($id);
	}
}