<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
class PW_OperateLog {
	
	function logThreads($operate, $fields) {
		if (! isset ( $fields ['tid'] )) {
			return false;
		}
		$ids = (is_array ( $fields ['tid'] )) ? $fields ['tid'] : array ($fields ['tid'] );
		return $this->_insertLog ( 'pw_log_threads', $ids, $operate );
	}
	
	function logMembers($operate, $fields) {
		if (! isset ( $fields ['uid'] )) {
			return false;
		}
		$ids = (is_array ( $fields ['uid'] )) ? $fields ['uid'] : array ($fields ['uid'] );
		return $this->_insertLog ( 'pw_log_members', $ids, $operate );
	}
	
	function logDiarys($operate, $fields) {
		if (! isset ( $fields ['did'] )) {
			return false;
		}
		$ids = (is_array ( $fields ['did'] )) ? $fields ['did'] : array ($fields ['did'] );
		return $this->_insertLog ( 'pw_log_diary', $ids, $operate );
	}
	
	function logPosts($operate, $fields) {
		if (! isset ( $fields ['pid'] )) {
			return false;
		}
		$ids = (is_array ( $fields ['pid'] )) ? $fields ['pid'] : array ($fields ['pid'] );
		return $this->_insertLog ( 'pw_log_posts', $ids, $operate );
	}
	
	function logForums($operate, $fields) {
		if (! isset ( $fields ['fid'] )) {
			return false;
		}
		$ids = (is_array ( $fields ['fid'] )) ? $fields ['fid'] : array ($fields ['fid'] );
		return $this->_insertLog ( 'pw_log_forums', $ids, $operate );
	}
	
	function logColonys($operate, $fields) {
		if (! isset ( $fields ['id'] )) {
			return false;
		}
		$ids = (is_array ( $fields ['id'] )) ? $fields ['id'] : array ($fields ['id'] );
		return $this->_insertLog ( 'pw_log_colonys', $ids, $operate );
	}	
	
	function _insertLog($tableName, $sids, $operate) {
		if (! $tableName || ! S::isArray ( $sids ) || ! $operate)
			return false;
		global $db, $timestamp;
		$operates = array ('update' => 1, 'delete' => 2, 'insert' => 3 );
		foreach ( $sids as $sid ) {
			pwQuery::replace ( $tableName, array ('id' => $sid, 'sid' => $sid, 'operate' => $operates [$operate], 'modified_time' => $timestamp ) );
		}
		return true;
	}
}