<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
class GatherQuery_UserDefine_PW_Members {
	var $_service = null;
	function init() {
		if (! S::isObj ( $this->_service )) {
			$this->_service = new GatherQuery_UserDefine_PW_Members_Impl ();
		}
	}
	
	function insert($tableName, $fields, $expand = array()) {
		$this->_service->logMembers ( 'insert', $fields );
		$this->_service->syncData ( 'insert', $fields );
		if (perf::checkMemcache ()) {
			$this->_service->cleanMemberCacheWithUserIds ( $tableName, $fields );
		} else {
			$this->_service->cleanMemberDbCacheWithUserIds ( $tableName, $fields );
		}
	}
	
	function update($tableName, $fields, $expand = array()) {
		if (perf::checkMemcache ()) {
			$this->_service->cleanMemberCacheWithUserIds ( $tableName, $fields );
		} else {
			$this->_service->cleanMemberDbCacheWithUserIds ( $tableName, $fields );
		}
	}
	
	function delete($tableName, $fields, $expand = array()) {
		$this->_service->logMembers ( 'delete', $fields );
		$this->_service->syncData ( 'delete', $fields );
		if (perf::checkMemcache ()) {
			$this->_service->cleanMemberCacheWithUserIds ( $tableName, $fields );
		} else {
			$this->_service->cleanMemberDbCacheWithUserIds ( $tableName, $fields );
		}
	}
	
	function select($tableName, $fields, $expand = array()) {
	
	}
}

class GatherQuery_UserDefine_PW_Members_Impl {
	/*
	 * 记录pw_members更新/删除操作
	 */
	function logMembers($operate, $fields) {
		global $db_operate_log;
		(isset ( $fields ['insert_id'] )) && $fields ['uid'] = $fields ['insert_id'];
		if (! $db_operate_log || ! in_array ( 'log_members', $db_operate_log ) || ! isset ( $fields ['uid'] )) {
			return false;
		}
		$service = L::loadClass ( 'operatelog', 'utility' );
		$service->logMembers ( $operate, $fields );
	}
	
	/*
	 * sphinx实时索引扩展  如果需要请部署(insert/update/delete)
	 */
	function syncData($operate, $fields) {
		global $db_sphinx;
		(isset ( $fields ['insert_id'] )) && $fields ['uid'] = $fields ['insert_id'];
		if (! isset ( $db_sphinx ['sync'] ['sync_members'] ) || ! isset ( $fields ['uid'] )) {
			return false;
		}
		$service = L::loadClass ( 'realtimesearcher', 'search/userdefine' );
		$service->syncData ( 'member', $operate, $fields ['uid'] );
	}
	
	function cleanMemberDbCacheWithUserIds($tableName, $fields) {
		if (! isset ( $fields ['uid'] )) {
			return false;
		}
		$userIds = (is_array ( $fields ['uid'] )) ? $fields ['uid'] : array ($fields ['uid'] );
		$_dbCacheService = Perf::gatherCache ( 'pw_membersdbcache' );
		switch ($tableName) {
			case 'pw_members' :
			case 'pw_memberdata' :
			case 'pw_memberinfo' :
				$_dbCacheService->clearMembersDbCacheByUserIds ( $userIds );
				break;
			case 'pw_membercredit' :
				$_dbCacheService->clearCreditDbCacheByUserIds ( $userIds );
				break;
			case 'pw_cmembers' :
				$_dbCacheService->clearColonyDbCacheByUserIds ( $userIds );
				break;
		}
		return true;
	}
	
	function cleanMemberCacheWithUserIds($tableName, $fields) {
		if (!isset( $fields['uid'] ) && !isset( $fields['userid'] )) {
			return false;
		}
		$userIds = (is_array ( $fields ['uid'] )) ? $fields ['uid'] : array ($fields ['uid'] );
		$cache = Perf::gatherCache ( 'pw_members' );
		switch ($tableName) {
			case 'pw_members' :
				$cache->clearCacheForMembersByUserIds ( $userIds );
				break;
			case 'pw_memberdata' :
				$cache->clearCacheForMemberDataByUserIds ( $userIds );
				break;
			case 'pw_memberinfo' :
				$cache->clearCacheForMemberInfoByUserIds ( $userIds );
				break;
			case 'pw_singleright' :
				$cache->clearCacheForSingleRightByUserIds ( $userIds );
				break;
			case 'pw_membercredit' :
				$cache->clearCacheForMemberCreditByUserIds ( $userIds );
				break;
			case 'pw_banuser' :
				$cache->clearCacheForMembersByUserIds ( $userIds );
				break;
			case 'pw_cmembers' :
				$cache->clearCacheForCmemberAndColonyByUserIds ( $userIds );
				break;
			case 'pw_membertags_relations' :
				$userIds = (is_array ( $fields ['userid'] )) ? $fields ['userid'] : array ($fields ['userid'] );
				$cache->clearCacheForMemberTagsByUserIds ( $userIds );
				break;
		}
		return true;
	}
}
