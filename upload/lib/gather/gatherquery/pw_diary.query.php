<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
class GatherQuery_UserDefine_PW_Diary {
	var $_service = null;
	function init() {
		if (! S::isObj ( $this->_service )) {
			$this->_service = new GatherQuery_UserDefine_PW_Diary_Impl ();
		}
	}
	
	function insert($tableName, $fields, $expand = array()) {
		$this->_service->logDiarys ( 'insert', $fields );
		$this->_service->syncData ( 'insert', $fields );
	}
	
	function update($tableName, $fields, $expand = array()) {
		$this->_service->logDiarys ( 'update', $fields );
		$this->_service->syncData ( 'update', $fields );
	}
	
	function delete($tableName, $fields, $expand = array()) {
		$this->_service->logDiarys ( 'delete', $fields );
		$this->_service->syncData ( 'delete', $fields );
	}
	
	function select($tableName, $fields, $expand = array()) {
	
	}
}

class GatherQuery_UserDefine_PW_Diary_Impl {
	/*
	 * 记录pw_diarys更新/删除操作
	 */
	function logDiarys($operate, $fields) {
		global $db_operate_log;
		(isset ( $fields ['insert_id'] )) && $fields ['did'] = $fields ['insert_id'];
		if (! $db_operate_log || ! in_array ( 'log_diarys', $db_operate_log ) || ! isset ( $fields ['did'] )) {
			return false;
		}
		$service = L::loadClass ( 'operatelog', 'utility' );
		$service->logDiarys ( $operate, $fields );
	}
	
	/*
	 * sphinx实时索引扩展  如果需要请部署(insert/update/delete)
	 */
	function syncData($operate, $fields) {
		global $db_sphinx;
		(isset ( $fields ['insert_id'] )) && $fields ['did'] = $fields ['insert_id'];
		if (! isset ( $db_sphinx ['sync'] ['sync_diarys'] ) || ! isset ( $fields ['did'] )) {
			return false;
		}
		$service = L::loadClass ( 'realtimesearcher', 'search/userdefine' );
		$service->syncData ( 'diary', $operate, $fields ['did'] );
	}
}