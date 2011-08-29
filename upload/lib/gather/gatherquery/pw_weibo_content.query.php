<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
class GatherQuery_UserDefine_PW_Weibo_content {
	var $_service = null;
	function init() {
		if (! S::isObj ( $this->_service )) {
			$this->_service = new GatherQuery_UserDefine_PW_Weibo_content_Impl ();
		}
	}
	
	function insert($tableName, $fields, $expand = array()) {
	}
	
	function update($tableName, $fields, $expand = array()) {
		if (perf::checkMemcache ()) {
			$this->_service->cleanWeibocontentCacheWithMids ( $tableName, $fields );
		}
	}
	
	function delete($tableName, $fields, $expand = array()) {
		if (perf::checkMemcache ()) {
			$this->_service->cleanWeibocontentCacheWithMids ( $tableName, $fields );
		}
	}
	
	function select($tableName, $fields, $expand = array()) {
	
	}
}

class GatherQuery_UserDefine_PW_Weibo_content_Impl {
	
	function cleanWeibocontentCacheWithMids($tableName, $fields){
		if (! isset ( $fields ['mid'] )) {
			return false;
		}
		$mids = (is_array ( $fields ['mid'] )) ? $fields ['mid'] : array ($fields ['mid'] );
		$cache = Perf::gatherCache('pw_weibo_content');
		$cache->clearCacheForWeiboContentsByMids($mids);
	}
}