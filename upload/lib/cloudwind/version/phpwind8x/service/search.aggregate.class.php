<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
class CloudWind_Platform_Aggregate {
	var $_doubleModel = 0;
	var $_service = array ();
	
	function CloudWind_Platform_Aggregate() {
		$this->__construct ();
	}
	
	function __construct() {
		if (! isset ( $this->_service ['parse'] ) || ! $this->_service ['parse']) {
			$this->_service ['parse'] = new CloudWind_Platform_Aggregate_SQLParseExtension ();
		}
		if (! isset ( $this->_service ['operate'] ) || ! $this->_service ['operate']) {
			$yunModel = CloudWind_getConfig ( 'yun_model' );
			$this->_service ['operate'] = ($yunModel ['search_model'] == 100) ? new CloudWind_Platform_Aggregate_OperateLog () : new CloudWind_Platform_Aggregate_SyncOperateLog ();
		}
		if ($this->_doubleModel && (! isset ( $this->_service ['operate_normal'] ) || ! isset ( $this->_service ['operate_sync'] ) || ! $this->_service ['operate_normal'] || ! $this->_service ['operate_sync'])) {
			$this->_service ['operate_normal'] = new CloudWind_Platform_Aggregate_OperateLog ();
			$this->_service ['operate_sync'] = new CloudWind_Platform_Aggregate_SyncOperateLog ();
		}
	}
	
	function collectSQL($sql) {
		list ( $operate, $tableName, $fields ) = $this->_service ['parse']->parseSQL ( $sql, $GLOBALS ['db'] );
		if (! $operate) {
			return false;
		}
		if ($this->_doubleModel) {
			$this->_service ['operate_normal']->operateLog ( $operate, $tableName, $fields );
			$this->_service ['operate_sync']->operateLog ( $operate, $tableName, $fields );
		} else {
			$this->_service ['operate']->operateLog ( $operate, $tableName, $fields );
		}
		return true;
	}
}

class CloudWind_Platform_Aggregate_SQLParseExtension {
	
	var $_tables = array ('pw_threads', 'pw_members', 'pw_colonys', 'pw_forums', 'pw_diary', 'pw_posts', 'pw_tmsgs', 'pw_memberdata', 'pw_attachs', 'pw_weibo_content' );
	
	function parseSQL($sql, $db) {
		$sql = trim ( $sql );
		if (! $sql) {
			return array (false, false, false );
		}
		list ( $bool, $operate, $tableName ) = $this->_matchOperateAndTableName ( $sql );
		if (! $bool) {
			return array (false, false, false );
		}
		list ( $tableName, $operate ) = array (strtolower ( $tableName ), strtolower ( $operate ) );
		if (in_array ( $operate, array ('insert', 'replace' ) )) {
			$insert_id = $db->insert_id ();
			$info = array ('insert_id' => $insert_id );
		} else {
			$info = $this->_parseSQL ( $operate, $sql );
		}
		return array ($operate, $tableName, $info );
	}
	
	function _matchOperateAndTableName($sql) {
		preg_match ( '/^(DELETE|INSERT|REPLACE|UPDATE)\s+(.+\s)?`?(pw_\w+)`?\s+/i', $sql, $match );
		if (! $match) {
			return array (false, false, false );
		}
		list ( , $operate, , $tableName ) = $match;
		if (! in_array ( $tableName, $this->_getTables () )) {
			return array (false, false, false );
		}
		return array (true, $operate, $tableName );
	}
	
	function _getTables() {
		$extendTableNames = array ();
		if ($GLOBALS ['db_tlist']) {
			foreach ( $GLOBALS ['db_tlist'] as $k => $v ) {
				$extendTableNames [] = 'pw_tmsgs' . ($k ? $k : '');
			}
		}
		if ($GLOBALS ['db_plist']) {
			foreach ( $GLOBALS ['db_plist'] as $k => $v ) {
				$extendTableNames [] = 'pw_posts' . ($k ? $k : '');
			}
		}
		return array_merge ( $this->_tables, $extendTableNames );
	}
	
	function _parseSQL($operate, $sql) {
		$operate = strtoupper ( $operate );
		switch ($operate) {
			case 'INSERT' :
			case 'REPLACE' :
				return $this->_matchInsertInfo ( $sql );
				break;
			case 'UPDATE' :
				return $this->_matchUpdateInfo ( $sql );
				break;
			case 'DELETE' :
				return $this->_matchDeleteInfo ( $sql );
				break;
			default :
				return false;
				break;
		}
		return true;
	}
	
	function _matchInsertInfo($sql) {
		preg_match ( '/^(INSERT|REPLACE)\s+INTO\s+`?(pw_\w+)`?\s+/i', $sql, $match );
		if (! $match || ! isset ( $match [2] ) || ! ($tableName = $match [2])) {
			return false;
		}
		$result = $this->_matchFields ( $sql );
		return $tableName;
	}
	
	function _matchDeleteInfo($sql) {
		preg_match ( '/^DELETE\s+FROM\s+`?(pw_\w+)`?\s+WHERE\s+/i', $sql, $match );
		if (! $match || ! isset ( $match [1] ) || ! ($tableName = $match [1])) {
			return false;
		}
		$fields = $this->_matchFields ( $sql );
		return $fields;
	}
	
	function _matchUpdateInfo($sql) {
		preg_match ( '/^UPDATE\s+`?(pw_\w+)`?\s+SET\s+/i', $sql, $match );
		if (! $match || ! isset ( $match [1] ) || ! ($tableName = $match [1])) {
			return false;
		}
		$fields = $this->_matchFields ( $sql );
		return $fields;
	}
	
	function _matchFields($sql) {
		preg_match_all ( '/`?(\w+)`?\s*(=|in)\s*(\'|\")?(\d+|\(.+\))(\'|\")?/i', $sql, $match );
		if (! $match || ! isset ( $match [1] ) || ! isset ( $match [2] ) || ! isset ( $match [3] )) {
			return false;
		}
		list ( , $fields, $expression, , $values ) = $match;
		$tmp = array ();
		foreach ( $fields as $key => $field ) {
			if (trim ( strtolower ( $expression [$key] ) ) == 'in') {
				$value = explode ( ',', str_replace ( array ('(', ')', '"', "'" ), array ('' ), $values [$key] ) );
			} else {
				$value = $values [$key];
			}
			$tmp [$field] = $value;
		}
		return $tmp;
	}

}

class CloudWind_Platform_Aggregate_OperateLog {
	
	function operateLog($operate, $tableName, $info) {
		$tableName = $this->convertTableName ( $tableName );
		switch ($tableName) {
			case 'pw_threads' :
				$this->logThreads ( $operate, $info );
				break;
			case 'pw_posts' :
				$this->logPosts ( $operate, $info );
				break;
			case 'pw_members' :
				$this->logMembers ( $operate, $info );
				break;
			case 'pw_forums' :
				$this->logForums ( $operate, $info );
				break;
			case 'pw_diary' :
				$this->logDiarys ( $operate, $info );
				break;
			case 'pw_colonys' :
				$this->logColonys ( $operate, $info );
				break;
			case 'pw_attachs' :
				$this->logAttachs ( $operate, $info );
				break;
			case 'pw_weibo_content' :
				$this->logWeibos ( $operate, $info );
				break;
			default :
				break;
		}
		return true;
	}
	
	function convertTableName($tablename) {
		$extendTableNames = array ();
		if ($GLOBALS ['db_tlist']) {
			foreach ( $GLOBALS ['db_tlist'] as $k => $v ) {
				$extendTableNames ['pw_tmsgs' . ($k ? $k : '')] = 'pw_threads';
			}
		}
		if ($GLOBALS ['db_plist']) {
			foreach ( $GLOBALS ['db_plist'] as $k => $v ) {
				$extendTableNames ['pw_posts' . ($k ? $k : '')] = 'pw_posts';
			}
		}
		$tableNames = array ('pw_tmsgs' => 'pw_threads', 'pw_memberinfo' => 'pw_members', 'pw_memberdata' => 'pw_members', 'pw_singleright' => 'pw_members', 'pw_membercredit' => 'pw_members', 'pw_banuser' => 'pw_members', 'pw_cmembers' => 'pw_members', 'pw_forumdata' => 'pw_forums', 'pw_announce' => 'pw_forums' );
		$tableNames += $extendTableNames;
		return (isset ( $tableNames [$tablename] )) ? $tableNames [$tablename] : $tablename;
	}
	
	function logThreads($operate, $fields) {
		$fields ['tid'] = (isset ( $fields ['insert_id'] )) ? $fields ['insert_id'] : $fields ['tid'];
		if (! isset ( $fields ['tid'] ) || SCR == 'read') {
			return false;
		}
		$ids = (is_array ( $fields ['tid'] )) ? $fields ['tid'] : array ($fields ['tid'] );
		return $this->_insertLog ( 'pw_log_threads', $ids, $operate );
	}
	
	function logMembers($operate, $fields) {
		$fields ['uid'] = (isset ( $fields ['insert_id'] )) ? $fields ['insert_id'] : $fields ['uid'];
		if (! isset ( $fields ['uid'] )) {
			return false;
		}
		$ids = (is_array ( $fields ['uid'] )) ? $fields ['uid'] : array ($fields ['uid'] );
		return $this->_insertLog ( 'pw_log_members', $ids, $operate );
	}
	
	function logDiarys($operate, $fields) {
		$fields ['did'] = (isset ( $fields ['insert_id'] )) ? $fields ['insert_id'] : $fields ['did'];
		if (! isset ( $fields ['did'] )) {
			return false;
		}
		$ids = (is_array ( $fields ['did'] )) ? $fields ['did'] : array ($fields ['did'] );
		return $this->_insertLog ( 'pw_log_diary', $ids, $operate );
	}
	
	function logPosts($operate, $fields) {
		$fields ['pid'] = (isset ( $fields ['insert_id'] )) ? $fields ['insert_id'] : $fields ['pid'];
		if (! isset ( $fields ['pid'] )) {
			return false;
		}
		$ids = (is_array ( $fields ['pid'] )) ? $fields ['pid'] : array ($fields ['pid'] );
		return $this->_insertLog ( 'pw_log_posts', $ids, $operate );
	}
	
	function logForums($operate, $fields) {
		$fields ['fid'] = (isset ( $fields ['insert_id'] )) ? $fields ['insert_id'] : $fields ['fid'];
		if (! isset ( $fields ['fid'] )) {
			return false;
		}
		$ids = (is_array ( $fields ['fid'] )) ? $fields ['fid'] : array ($fields ['fid'] );
		return $this->_insertLog ( 'pw_log_forums', $ids, $operate );
	}
	
	function logColonys($operate, $fields) {
		$fields ['id'] = (isset ( $fields ['insert_id'] )) ? $fields ['insert_id'] : $fields ['id'];
		if (! isset ( $fields ['id'] )) {
			return false;
		}
		$ids = (is_array ( $fields ['id'] )) ? $fields ['id'] : array ($fields ['id'] );
		return $this->_insertLog ( 'pw_log_colonys', $ids, $operate );
	}
	
	function logAttachs($operate, $fields) {
		$fields ['aid'] = (isset ( $fields ['insert_id'] )) ? $fields ['insert_id'] : $fields ['aid'];
		if (! isset ( $fields ['aid'] )) {
			return false;
		}
		$ids = (is_array ( $fields ['aid'] )) ? $fields ['aid'] : array ($fields ['aid'] );
		return $this->_insertLog ( 'pw_log_attachs', $ids, $operate );
	}
	
	function logWeibos($operate, $fields) {
		$fields ['mid'] = (isset ( $fields ['insert_id'] )) ? $fields ['insert_id'] : $fields ['mid'];
		if (! isset ( $fields ['mid'] )) {
			return false;
		}
		$ids = (is_array ( $fields ['mid'] )) ? $fields ['mid'] : array ($fields ['mid'] );
		return $this->_insertLog ( 'pw_log_weibos', $ids, $operate );
	}
	
	function _insertLog($tableName, $sids, $operate) {
		if (! $tableName || ! is_array ( $sids ) || ! $operate)
			return false;
		global $db, $timestamp;
		$operates = array ('update' => 1, 'delete' => 2, 'insert' => 3 );
		foreach ( $sids as $sid ) {
			$sid && $db->query ( "REPLACE INTO `" . $tableName . "` SET `id`  =  " . intval ( $sid ) . " , `sid`  = " . intval ( $sid ) . " , `operate`  =  " . intval ( $operates [$operate] ) . " , `modified_time`  = " . intval ( $timestamp ) );
		}
		return true;
	}
}

class CloudWind_Platform_Aggregate_SyncOperateLog {
	
	function operateLog($operate, $tableName, $info) {
		$tableName = $this->convertTableName ( $tableName );
		switch ($tableName) {
			case 'pw_threads' :
				$this->logThreads ( $operate, $info );
				break;
			case 'pw_posts' :
				$this->logPosts ( $operate, $info );
				break;
			case 'pw_members' :
				$this->logMembers ( $operate, $info );
				break;
			case 'pw_forums' :
				$this->logForums ( $operate, $info );
				break;
			case 'pw_diary' :
				$this->logDiarys ( $operate, $info );
				break;
			case 'pw_colonys' :
				$this->logColonys ( $operate, $info );
				break;
			case 'pw_attachs' :
				$this->logAttachs ( $operate, $info );
				break;
			case 'pw_weibo_content' :
				$this->logWeibos ( $operate, $info );
				break;
			default :
				break;
		}
		return true;
	}
	
	function convertTableName($tablename) {
		$extendTableNames = array ();
		if ($GLOBALS ['db_tlist']) {
			foreach ( $GLOBALS ['db_tlist'] as $k => $v ) {
				$extendTableNames ['pw_tmsgs' . ($k ? $k : '')] = 'pw_threads';
			}
		}
		if ($GLOBALS ['db_plist']) {
			foreach ( $GLOBALS ['db_plist'] as $k => $v ) {
				$extendTableNames ['pw_posts' . ($k ? $k : '')] = 'pw_posts';
			}
		}
		$tableNames = array ('pw_tmsgs' => 'pw_threads', 'pw_memberinfo' => 'pw_members', 'pw_memberdata' => 'pw_members', 'pw_singleright' => 'pw_members', 'pw_membercredit' => 'pw_members', 'pw_banuser' => 'pw_members', 'pw_cmembers' => 'pw_members', 'pw_forumdata' => 'pw_forums', 'pw_announce' => 'pw_forums' );
		$tableNames += $extendTableNames;
		return (isset ( $tableNames [$tablename] )) ? $tableNames [$tablename] : $tablename;
	}
	
	function logThreads($operate, $fields) {
		$fields ['tid'] = (isset ( $fields ['insert_id'] )) ? $fields ['insert_id'] : $fields ['tid'];
		if (! isset ( $fields ['tid'] ) || SCR == 'read') {
			return false;
		}
		$ids = (is_array ( $fields ['tid'] )) ? $fields ['tid'] : array ($fields ['tid'] );
		return $this->_insertLog ( 1, $ids, $operate );
	}
	
	function logMembers($operate, $fields) {
		$fields ['uid'] = (isset ( $fields ['insert_id'] )) ? $fields ['insert_id'] : $fields ['uid'];
		if (! isset ( $fields ['uid'] ) || $operate == 'update') {
			return false;
		}
		$ids = (is_array ( $fields ['uid'] )) ? $fields ['uid'] : array ($fields ['uid'] );
		return $this->_insertLog ( 3, $ids, $operate );
	}
	
	function logDiarys($operate, $fields) {
		$fields ['did'] = (isset ( $fields ['insert_id'] )) ? $fields ['insert_id'] : $fields ['did'];
		if (! isset ( $fields ['did'] )) {
			return false;
		}
		$ids = (is_array ( $fields ['did'] )) ? $fields ['did'] : array ($fields ['did'] );
		return $this->_insertLog ( 2, $ids, $operate );
	}
	
	function logPosts($operate, $fields) {
		$fields ['pid'] = (isset ( $fields ['insert_id'] )) ? $fields ['insert_id'] : $fields ['pid'];
		if (! isset ( $fields ['pid'] )) {
			return false;
		}
		$ids = (is_array ( $fields ['pid'] )) ? $fields ['pid'] : array ($fields ['pid'] );
		return $this->_insertLog ( 6, $ids, $operate );
	}
	
	function logForums($operate, $fields) {
		$fields ['fid'] = (isset ( $fields ['insert_id'] )) ? $fields ['insert_id'] : $fields ['fid'];
		if (! isset ( $fields ['fid'] ) || $operate == 'update') {
			return false;
		}
		$ids = (is_array ( $fields ['fid'] )) ? $fields ['fid'] : array ($fields ['fid'] );
		return $this->_insertLog ( 4, $ids, $operate );
	}
	
	function logColonys($operate, $fields) {
		$fields ['id'] = (isset ( $fields ['insert_id'] )) ? $fields ['insert_id'] : $fields ['id'];
		if (! isset ( $fields ['id'] ) || $operate == 'update') {
			return false;
		}
		$ids = (is_array ( $fields ['id'] )) ? $fields ['id'] : array ($fields ['id'] );
		return $this->_insertLog ( 5, $ids, $operate );
	}
	
	function logAttachs($operate, $fields) {
		$fields ['aid'] = (isset ( $fields ['insert_id'] )) ? $fields ['insert_id'] : $fields ['aid'];
		if (! isset ( $fields ['aid'] )) {
			return false;
		}
		$ids = (is_array ( $fields ['aid'] )) ? $fields ['aid'] : array ($fields ['aid'] );
		return $this->_insertLog ( 8, $ids, $operate );
	}
	
	function logWeibos($operate, $fields) {
		$fields ['mid'] = (isset ( $fields ['insert_id'] )) ? $fields ['insert_id'] : $fields ['mid'];
		if (! isset ( $fields ['mid'] )) {
			return false;
		}
		$ids = (is_array ( $fields ['mid'] )) ? $fields ['mid'] : array ($fields ['mid'] );
		return $this->_insertLog ( 7, $ids, $operate );
	}
	
	function _insertLog($type, $sids, $operate) {
		if (! $type || ! is_array ( $sids ) || ! $operate)
			return false;
		global $db, $timestamp;
		$operates = array ('update' => 1, 'delete' => 2, 'insert' => 3 );
		foreach ( $sids as $sid ) {
			$sid && $db->query ( "REPLACE INTO `pw_log_aggregate` SET `id`  =  " . intval ( $sid ) . " , `type`  = " . intval ( $type ) . ", `sid`  = " . intval ( $sid ) . " , `operate`  =  " . intval ( $operates [$operate] ) . " , `modified_time`  = " . intval ( $timestamp ) );
		}
		return true;
	}
}