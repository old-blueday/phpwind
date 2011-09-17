<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
class GatherQuery_UserDefine_PW_PingLog {
	var $_service = null;
	function init() {
		if (! S::isObj ( $this->_service )) {
			$this->_service = new GatherQuery_UserDefine_PW_PingLog_Impl ();
		}
	}
	
	function insert($tableName, $fields, $expand = array()) {
		if (perf::checkMemcache ()) {
			$this->_service->clearPingLogsCacheByTid ( $fields );
		}
	}
	
	function update($tableName, $fields, $expand = array()) {
		if (perf::checkMemcache ()) {
			$this->_service->clearPingLogsCacheByTid ( $fields );
		}
	}
	
	function delete($tableName, $fields, $expand = array()) {
		if (perf::checkMemcache ()) {
			$this->_service->clearPingLogsCacheByTid ( $fields );
		}
	}
	
	function select($tableName, $fields, $expand = array()) {
	
	}
}

class GatherQuery_UserDefine_PW_PingLog_Impl {
	
	function clearPingLogsCacheByTid($fields) {
		global $tid, $db;
		$id = isset ( $fields ['id'] ) ? $fields ['id'] : 1;
		if (! $tid && ! $id) return false;
		if (! $tid) {
			$id = is_array($id) ? end($id) : $id;
			$tid = $db->get_value ( 'SELECT tid FROM pw_pinglog WHERE id=' . S::sqlEscape ( $id ) );
		}
		$cache = Perf::gatherCache ( 'pw_ping' );
		$cache->clearPingLogsCache ( $tid );
	}
}