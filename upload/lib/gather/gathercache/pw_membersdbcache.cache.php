<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );

class GatherCache_PW_MembersDbCache_Cache extends GatherCache_Base_Cache {
	
	var $_tableName = 'pw_cache_members';
	var $_time = null;
	var $_db = null;
	var $_shardNums = 1;
	
	function GatherCache_PW_MembersDbCache_Cache() {
		$this->_db = &$GLOBALS ['db'];
		$this->_time = &$GLOBALS ['timestamp'];
	}
	
	function _getMembersKey($userId){
		return 'u' . $userId;
	}
	
	function _getCreditKey($userId){
		return 'c' . $userId;
	}
	
	function _getColonyKey($userId){
		return 'g' . $userId;
	}	
	
	/**
	 * 从dbcache获取用户信息， 积分信息，群组信息 （该函数仅供read.php页面调用）
	 *
	 * @param array $userIds
	 * @param boolean $showCredit 是否要获取积分信息
	 * @param boolean $showColony 是否要获取群组信息
	 * @param boolean $withMemberInfo 是否获取MemberInfo信息
	 * @return array() 
	 */
	function getUserDBCacheByUserIds($userIds, $showCredit = false, $showColony = false, $showfield = false) {
		if (!S::isArray($userIds)) return array(array(), array(), array());
		$membersKeys = $colonyKeys = $creditKeys = $members = $colony = $credit = array ();
		foreach ( $userIds as $userId ) {
			$membersKeys [$this->_getMembersKey($userId)] = $userId;
			$showCredit && $creditKeys [$this->_getCreditKey($userId)] = $userId;
			$showColony && $colonyKeys [$this->_getColonyKey($userId)] = $userId;			
		}
		$tmpResult = $this->get(array_keys($membersKeys + $colonyKeys + $creditKeys));
		if ($tmpResult){
			foreach ( $tmpResult as $key => $value ) {
				if (!is_array($value)) continue;
				if (isset($membersKeys[$key])){
					$members[$membersKeys[$key]] = $value;
					unset($membersKeys[$key]);
				}else if (isset($creditKeys[$key])){
					$credit[$creditKeys[$key]] = $value;
					unset($creditKeys[$key]);
				}else if (isset($colonyKeys[$key])){
					$colony[$colonyKeys[$key]] = $value;
					unset($colonyKeys[$key]);			
				}
			}
		}
		if ($membersKeys) { #会员信息
			$members += (array)$this->_getMembersByUserIdsNoCache($membersKeys, $showfield);
		}
		if ($showCredit && $creditKeys) { #自定义积分显示
			$credit += (array)$this->_getCreditByUserIdsNoCache($creditKeys);
		}
		if ($showColony && $colonyKeys) { #群组信息
			$colony += (array)$this->_getColonyByUserIdsNoCache($colonyKeys);
		}		
		return array($members, $credit, $colony);
	}
	
	/**
	 * 获取会员信息
	 *
	 * @param array $userIds
	 * @param boolean $showfield 是否获取MemberInfo信息
	 * @return array()
	 */
	function _getMembersByUserIdsNoCache($userIds, $showfield = false) {
		if (!S::isArray($userIds)) return array();
		global $customfield;
		$fieldinfo = '';
		if (is_array($customfield)) {
			foreach ($customfield as $value) {
				if ($value['ifsys']) continue;
				$fieldinfo .= ',mi.field_'.(int)$value['id'];
			}
		}
		!empty($showfield) && $fieldinfo .= ',mi.customdata';
		$tableinfo = $fieldinfo ? 'LEFT JOIN pw_memberinfo mi ON mi.uid=m.uid' : '';		
		$query = $this->_db->query ( "SELECT m.uid,m.username,m.gender,m.oicq,m.aliww,m.groupid,m.memberid,m.icon AS micon ,m.hack,m.honor,m.signature,m.regdate,m.medals,m.userstatus,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency,md.thisvisit,md.lastvisit,md.onlinetime,md.starttime,md.punch $fieldinfo FROM pw_members m LEFT JOIN pw_memberdata md ON m.uid=md.uid $tableinfo WHERE m.uid IN (" . S::sqlImplode ( $userIds, false ) . ") " );
		$members = $tmpMembers = array();
		while ( $rt = $this->_db->fetch_array ( $query ) ) {
			$members [$rt ['uid']] = $rt;
			$tmpMembers [$this->_getMembersKey($rt ['uid'])] = $rt;
		}		
		foreach ($userIds as $userId){
			!isset($members[$userId]) && $tmpMembers[$this->_getMembersKey($userId)] = array();
		}			
		$this->update ($tmpMembers);
		return $members;
	}
	
	/**
	 * 获取用户积分
	 *
	 * @param array $userIds
	 * @return array()
	 */
	function _getCreditByUserIdsNoCache($userIds) {
		if (!S::isArray($userIds)) return array();
		$query = $this->_db->query("SELECT uid,cid,value FROM pw_membercredit WHERE uid IN(".S::sqlImplode($userIds,false).")");
		$memberCredit = $tmpMemberCredit = array();	
		while ($rt = $this->_db->fetch_array($query)) {
			$memberCredit[$rt['uid']][$rt['cid']] = $rt['value'];
			$tmpMemberCredit[$this->_getCreditKey($rt['uid'])][$rt['cid']] = $rt['value'];
		}
		foreach ($userIds as $userId){
			!isset($memberCredit[$userId]) && $tmpMemberCredit[$this->_getCreditKey($userId)] = array();
		}			
		$this->update($tmpMemberCredit);
		return $memberCredit;
	}
	
	/**
	 * 获取用户群组信息
	 *
	 * @param array $userIds
	 * @return array()
	 */
	function _getColonyByUserIdsNoCache($userIds) {
		if (!S::isArray($userIds)) return array();
		$query = $this->_db->query("SELECT c.uid,cy.id,cy.cname"
							. " FROM pw_cmembers c LEFT JOIN pw_colonys cy ON cy.id=c.colonyid"
							. " WHERE c.uid IN(".S::sqlImplode($userIds,false).") AND c.ifadmin!='-1'");
		$cmemberAndColony = $tmpCmemberAndColony = array();			
		while ($rt = $this->_db->fetch_array($query)) {
			$cmemberAndColony[$rt['uid']] = $tmpCmemberAndColony[$this->_getColonyKey($rt['uid'])] = $rt;
		}
		foreach ($userIds as $userId){
			!isset($cmemberAndColony[$userId]) && $tmpCmemberAndColony[$this->_getColonyKey($userId)] = array();
		}		
		$this->update($tmpCmemberAndColony);	
		return $cmemberAndColony;
	}
	
	function clearMembersDbCacheByUserIds($userIds){
		$userIds = ( array ) $userIds;
		$keys = array();
		foreach ( $userIds as $uid ) {
			$keys[] = $this->_getMembersKey ( $uid );
		}
		return $this->delete($keys);		
	}
	
	function clearCreditDbCacheByUserIds($userIds){
		$userIds = ( array ) $userIds;
		$keys = array();
		foreach ( $userIds as $uid ) {
			$keys[] = $this->_getCreditKey ( $uid );
		}
		return $this->delete($keys);		
	}

	function clearColonyDbCacheByUserIds($userIds){
		$userIds = ( array ) $userIds;
		$keys = array();
		foreach ( $userIds as $uid ) {
			$keys[] = $this->_getColonyKey ( $uid );
		}
		return $this->delete($keys);		
	}	
	
	/*
	 * 更新数据
	 */
	function update($fieldDatas, $expire = 3600) {
		$_tableNames = array ();
		foreach ( $fieldDatas as $key => $value ) {
			$_tableName = $this->_getTableName ( $key );
			$_tableNames [$_tableName] [] = array ('ckey' => $key, 'cvalue' => serialize ( $value ), 'expire' => $expire + $this->_time );
		}
		foreach ( $_tableNames as $_tableName => $fields ) {
			$this->_db->update ( "REPLACE INTO `" . $_tableName . "` (ckey,cvalue,expire) VALUES " . S::sqlMulti ( $fields, false ) );
		}
		return true;
	}
	/*
	 * 获取数据
	 */
	function get($keys) {
		$_tableNames = array ();
		foreach ( $keys as $key ) {
			$_tableName = $this->_getTableName ( $key );
			$_tableNames [$_tableName] [] = $key;
		}
		$result = array ();
		foreach ( $_tableNames as $_tableName => $keys ) {
			$query = $this->_db->query ( "SELECT ckey,cvalue FROM `" . $_tableName . "` WHERE ckey IN (" . S::sqlImplode ( $keys, false ) . ") AND expire > " . S::sqlEscape ( $this->_time, false ) );
			while ( $rt = $this->_db->fetch_array ( $query ) ) {
				$result [$rt ['ckey']] = unserialize ( $rt ['cvalue'] );
			}
		}
		return $result;
	}
	/*
	 * 删除数据
	 */
	function delete($keys) {
		$_tableNames = array ();
		foreach ( $keys as $key ) {
			$_tableName = $this->_getTableName ( $key );
			$_tableNames [$_tableName] [] = $key;
		}
		foreach ( $_tableNames as $_tableName => $keys ) {
			$this->_db->update ( "UPDATE `" . $_tableName . "` SET expire = 0 WHERE ckey IN (" . S::sqlImplode ( $keys, false ) . ")" );
		}
		return false;
	}
	
	/*
	 * 清除数据
	 */
	function flush() {
		$_tableNames = $this->_getTableNames ();
		foreach ( $_tableNames as $_tableName ) {
			$this->_db->update ( "TRUNCATE TABLE `" . $_tableName . "`", false );
		}
	}
	/*
	 * 获取所有数据表
	 */
	function _getTableNames() {
		$_tableNames = array ();
		for($i = 0; $i < $this->_shardNums; $i ++) {
			$_tableNames [] = $this->_tableName . (($i > 0) ? $i : '');
		}
		return $_tableNames;
	}
	/*
	 * 根椐key获取数据表
	 */
	function _getTableName($key) {
		return $this->_tableName;
	}
}
?>