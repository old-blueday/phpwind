<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 用户信息缓存类，包含对如下表的缓存 pw_members, pw_memberdata, pw_memberInfo, pw_memberCredit, pw_singleRight
 *
 */
class GatherCache_PW_Members_Cache extends GatherCache_Base_Cache {
	var $_defaultCache = PW_CACHE_MEMCACHE;
	var $_prefix = 'member_';

	var $_membersField = array ('uid' => null, 'username' => null, 'password' => null, 'safecv' => null, 'email' => null, 'groupid' => null, 'memberid' => null, 'groups' => null, 'icon' => null, 'gender' => null, 'regdate' => null, 'signature' => null, 'introduce' => null, 'oicq' => null, 'aliww' => null, 'icq' => null, 'msn' => null, 'yahoo' => null, 'site' => null, 'location' => null, 'honor' => null, 'bday' => null, 'lastaddrst' => null, 'yz' => null, 'timedf' => null, 'style' => null, 'datefm' => null, 't_num' => null, 'p_num' => null, 'attach' => null, 'hack' => null, 'newpm' => null, 'banpm' => null, 'msggroups' => null, 'medals' => null, 'userstatus' => null, 'shortcut' => null );
	var $_memberDataField = array ('uid' => null, 'postnum' => null, 'digests' => null, 'rvrc' => null, 'money' => null, 'credit' => null, 'currency' => null, 'lastvisit' => null, 'thisvisit' => null, 'lastpost' => null, 'onlinetime' => null, 'monoltime' => null, 'todaypost' => null, 'monthpost' => null, 'uploadtime' => null, 'uploadnum' => null, 'follows' => null, 'fans' => null, 'newfans' => null, 'newreferto' => null, 'newcomment' => null, 'onlineip' => null, 'starttime' => null, 'postcheck' => null, 'pwdctime' => null, 'f_num' => null, 'creditpop' => null, 'jobnum' => null, 'lastmsg' => null, 'lastgrab' => null, 'punch' => null,'newnotice' => null, 'newrequest' => null );
	var $_memberInfoField = array ('uid' => null, 'adsips' => null, 'credit' => null, 'deposit' => null, 'startdate' => null, 'ddeposit' => null, 'dstartdate' => null, 'regreason' => null, 'readmsg' => null, 'delmsg' => null, 'tooltime' => null, 'replyinfo' => null, 'lasttime' => null, 'digtid' => null, 'customdata' => null, 'tradeinfo' => null );
	var $_singleRightField = array ('uid' => null, 'visit' => null, 'post' => null, 'reply' => null );

	/**
	 * 获取一条members表信息
	 *
	 * @param int $userId
	 * @return array
	 */
	function getMembersByUserId($userId) {
		$userId = S::int ( $userId );
		if ($userId < 1) {
			return false;
		}
		$key = $this->_getMembersKey ( $userId );
		$result = $this->_cacheService->get ( $key );
		if ($result === false) {
			$result = $this->_getMembersByUserIdNoCache ( $userId );
			$result = $result ? $result : array();
			$this->_cacheService->set ( $key,  $result);
		}
		return $result;
	}

	/**
	 * 获取一条MemberData信息
	 *
	 * @param int $userId
	 * @return array
	 */
	function getMemberDataByUserId($userId) {
		$userId = S::int ( $userId );
		if ($userId < 1) {
			return false;
		}
		$key = $this->_getMemberDataKey ( $userId );
		$result = $this->_cacheService->get ( $key );
		if ($result === false) {
			$result = $this->_getMemberDataByUserIdNoCache ( $userId );
			$result = $result ? $result : array();
			$this->_cacheService->set ( $key, $result );
		}
		return $result;
	}

	/**
	 * 获取一条MemberInfo信息
	 *
	 * @param int $userId
	 * @return array
	 */
	function getMemberInfoByUserId($userId) {
		$userId = S::int ( $userId );
		if ($userId < 1) {
			return false;
		}
		$key = $this->_getMemberInfoKey ( $userId );
		$result = $this->_cacheService->get ( $key );
		if ($result === false ) {
			$result = $this->_getMemberInfoByUserIdNoCache ( $userId );
			$result = $result ? $result : array();
			$this->_cacheService->set ( $key, $result );
		}
		return $result;
	}

	/**
	 * 获取一条SingleRight信息
	 *
	 * @param int $userId
	 * @return array
	 */
	function getSingleRightByUserId($userId) {
		$userId = S::int ( $userId );
		if ($userId < 1 ) {
			return false;
		}
		$key = $this->_getSingleRightKey ( $userId );
		$result = $this->_cacheService->get ( $key );
		if ($result === false){
			$result = $this->_getSingleRightByUserIdNoCache ( $userId );
			$result = $result ? $result : array();
			$this->_cacheService->set ( $key, $result );
		}
		return $result;
	}

	/**
	 * 批量获取一组Members信息
	 *
	 * @param array $userIds
	 * @return array
	 */
	function getMembersByUserIds($userIds) {
		if (! S::isArray ( $userIds )) {
			return false;
		}
		$userIds = array_unique ( $userIds );
		$result = $_tmpResult = $keys = $_tmpUserIds = array ();	
		foreach ( $userIds as $userId ) {
			$keys [$this->_getMembersKey ( $userId )] = $userId;
		}
		if (($members = $this->_cacheService->get ( array_keys($keys) ))) {
			$_unique = $this->getUnique();
			foreach ($keys as $key=>$userId){
				$_key = $_unique . $key;
				if (isset($members[$_key]) && is_array($members[$_key])){
					$_tmpUserIds [] = $userId;
					$result[$userId] = $members[$_key];
				}
			}
		}		
		$userIds = array_diff ( $userIds, $_tmpUserIds );
		if ($userIds) {
			$_tmpResult = $this->_getMembersByUserIdsNoCache ( $userIds );
			foreach ($userIds as $userId){
				$this->_cacheService->set ( $this->_getMembersKey ( $userId ), isset($_tmpResult[$userId]) ? $_tmpResult[$userId] : array() );
			}
		}			
		return  (array)$result + (array)$_tmpResult;
	}

	/**
	 * 批量获取一组MemberData信息
	 *
	 * @param array $userIds
	 * @return array
	 */
	function getMemberDataByUserIds($userIds) {
		if (! S::isArray ( $userIds )) {
			return false;
		}
		$userIds = array_unique ( $userIds );
		$result = $_tmpResult = $keys = $_tmpUserIds = array ();
		foreach ( $userIds as $userId ) {
			$keys [$this->_getMemberDataKey ( $userId )] = $userId;
		}
		if (($members = $this->_cacheService->get ( array_keys($keys) ))) {
			$_unique = $this->getUnique();
			foreach ($keys as $key=>$userId){
				$_key = $_unique . $key;
				if (isset($members[$_key]) && is_array($members[$_key])){
					$_tmpUserIds [] = $userId;
					$result[$userId] = $members[$_key];
				}
			}
		}
		$userIds = array_diff ( $userIds, $_tmpUserIds );
		if ($userIds) {
			$_tmpResult = $this->_getMemberDataByUserIdsNoCache ( $userIds );
			foreach ($userIds as $userId){
				$this->_cacheService->set ( $this->_getMemberDataKey ( $userId ), isset($_tmpResult[$userId]) ? $_tmpResult[$userId] : array() );
			}
		}		
		
		return (array)$result + (array)$_tmpResult;
	}

	/**
	 * 批量获取一组MemberInfo信息
	 *
	 * @param array $userIds
	 * @return array
	 */
	function getMemberInfoByUserIds($userIds) {
		if (! S::isArray ( $userIds )) {
			return false;
		}
		$userIds = array_unique ( $userIds );
		$result = $_tmpResult = $keys = $_tmpUserIds = array ();
		foreach ( $userIds as $userId ) {
			$keys [$this->_getMemberInfoKey ( $userId )] = $userId;
		}
		if (($members = $this->_cacheService->get ( array_keys($keys) ))) {
			$_unique = $this->getUnique();
			foreach ($keys as $key=>$userId){
				$_key = $_unique . $key;
				if (isset($members[$_key]) && is_array($members[$_key])){
					$_tmpUserIds [] = $userId;
					$result[$userId] = $members[$_key];
				}
			}
		}		
		$userIds = array_diff ( $userIds, $_tmpUserIds );
		if ($userIds) {
			$_tmpResult = $this->_getMemberInfoByUserIdsNoCache ( $userIds );
			foreach ($userIds as $userId){
				$this->_cacheService->set ( $this->_getMemberInfoKey ( $userId ), isset($_tmpResult[$userId]) ? $_tmpResult[$userId] : array() );
			}
		}			
		return (array)$result + (array)$_tmpResult;
	}

	/**
	 * 批量获取一组MemberCredit信息
	 *
	 * @param array $userIds
	 * @return array
	 */
	function getMemberCreditByUserIds($userIds) {
		if (! S::isArray ( $userIds )) {
			return false;
		}
		$userIds = array_unique ( $userIds );
		$result = $_tmpResult = $keys = $_tmpUserIds = array ();
		foreach ( $userIds as $userId ) {
			$keys [$this->_getMemberCreditKey ( $userId )] = $userId;
		}
		if (($members = $this->_cacheService->get ( array_keys($keys) ))) {
			$_unique = $this->getUnique();
			foreach ($keys as $key=>$userId){
				$_key = $_unique . $key;
				if (isset($members[$_key]) && is_array($members[$_key])){
					$_tmpUserIds [] = $userId;
					$result[$userId] = $members[$_key];
				}
			}
		}
		$userIds = array_diff ( $userIds, $_tmpUserIds );
		if ($userIds) {
			$_tmpResult = $this->_getMemberCreditByUserIdsNoCache ( $userIds );
			foreach ($userIds as $userId){
				$this->_cacheService->set ( $this->_getMemberCreditKey ( $userId ), isset($_tmpResult[$userId]) ? $_tmpResult[$userId] : array() );
			}
		}

		return (array)$result + (array)$_tmpResult;
	}

	/**
	 * 批量获取用户群组信息， 仅供read.php在获取用户信息时调用
	 *
	 * @param array $userIds
	 * @return array
	 */
	function getCmemberAndColonyByUserIds($userIds) {
		if (! S::isArray ( $userIds )) {
			return false;
		}
		$userIds = array_unique ( $userIds );
		$result = $_tmpResult = $keys = $_tmpUserIds = array ();
		foreach ( $userIds as $userId ) {
			$keys [$this->_getCmemberAndColonyKey ( $userId )] = $userId;
		}
		if (($members = $this->_cacheService->get ( array_keys($keys) ))) {
			$_unique = $this->getUnique();
			foreach ($keys as $key=>$userId){
				$_key = $_unique . $key;
				if (isset($members[$_key]) && is_array($members[$_key])){
					$_tmpUserIds [] = $userId;
					$result[$userId] = $members[$_key];
				}
			}
		}
		$userIds = array_diff ( $userIds, $_tmpUserIds );
		if ($userIds) {
			$_tmpResult = $this->_getCmemberAndColonyByUserIdsNoCache ( $userIds );
			foreach ($userIds as $userId){
				$this->_cacheService->set ( $this->_getCmemberAndColonyKey ( $userId ), isset($_tmpResult[$userId]) ? $_tmpResult[$userId] : array() );
			}
		}		
		return (array)$result + (array)$_tmpResult;
	}

	/**
	 * 获取一条用户基本信息和Data信息和SingleRight信息，仅供global.php里的getUserByUid函数调用
	 * 实现了这条sql语句 "SELECT m.*, md.*, sr.* FROM pw_members m LEFT JOIN pw_memberdata md ON m.uid=md.uid LEFT JOIN pw_singleright sr ON m.uid=sr.uid WHERE m.uid=" . S::sqlEscape($uid) . " AND m.groupid<>'0' AND md.uid IS NOT NULL"
	 * 
	 * @param int $userId
	 * @return array
	 */
	/**
	function getMembersAndMemberDataAndSingleRightByUserId($userId) {
		$userId = S::int ( $userId );
		if ($userId < 1) return false;
		$membersAndMemberData = $this->getAllByUserId($userId, true, true);
		$singleRight = $this->getSingleRightByUserId($userId);
		return  (array)$membersAndMemberData + ($singleRight ? (array)$singleRight : $this->_singleRightField);
	}
	**/

	/**
	 * 根据一个用户id获取用户名
	 * 
	 * @param int $userId 用户id
	 * @return string
	 */
	function getUserNameByUserId($userId) {
		$userId = S::int ( $userId );
		if ($userId < 1) return false;
		$result = $this->getMembersByUserId ( $userId );
		return $result ? $result['username'] : false;
	}

	/**
	 * 根据用户id批量获取用户名
	 * 
	 * @param array $userIds 用户id数组
	 * @return array 以uid为key，用户名为值的数组
	 */
	function getUserNameByUserIds($userIds) {
		if (! S::isArray ( $userIds )) {
			return false;
		}
		if (!($members = $this->getMembersByUserIds($userIds))) return false;
		$_userNames = array ();
		foreach ( $members as $member ) {
			$_userNames [$member ['uid']] = $member ['username'];
		}
		return $_userNames;
	}

	/**
	 * 获取用户信息
	 *
	 * @param int $userId 用户ID
	 * @param bool $isMembers 是否取用户主要信息
	 * @param bool $isMemberData 是否取用户基本信息
	 * @param bool $isMemberInfo 是否取用户相关信息
	 * @return array|boolean
	 */
	
	function getAllFieldByUserId($userId, $isMembers = true, $isMemberData = false, $isMemberInfo = false) {
		$userId = S::int($userId);
		if ($userId < 1) return false;
		$members = $isMembers ? $this->getMembersByUserId($userId) : false;
		$memberData = $isMemberData ? $this->getMemberDataByUserId($userId) : false;
		$memberInfo = $isMemberInfo ? $this->getMemberInfoByUserId($userId) : false;
		return $this->_joinTables(array($isMembers, $isMemberData, $isMemberInfo), array($members, $memberData, $memberInfo));
	}
	
	
	/**
	 * 从缓存中批量获取用户基本信息，Data信息，Info信息
	 *
	 * @param array $userIds
	 * @return array
	 */
	
	function getAllFieldByUserIds($userIds, $isMembers = true, $isMemberData = false, $isMemberInfo = false) {
		if (! S::isArray ( $userIds )) return false;
		$arrMembers = $isMembers ? $this->getMembersByUserIds($userIds) : array();
		$arrMemberData = $isMemberData ? $this->getMemberDataByUserIds($userIds) : array();
		$arrMemberInfo = $isMemberInfo ? $this->getMemberInfoByUserIds($userIds) : array();
		$result = array();
		foreach ($userIds as $userId){
			$isMembers && $members = isset ( $arrMembers [$userId]) ?  $arrMembers [$userId] : false;
			$isMemberData && $memberData = isset ( $arrMemberData [$userId]) ?  $arrMemberData [$userId] : false;
			$isMemberInfo && $memberInfo = isset ( $arrMemberInfo [$userId]) ?  $arrMemberInfo [$userId] : false;
			$tmp = $this->_joinTables(array($isMembers, $isMemberData, $isMemberInfo), array($members, $memberData, $memberInfo));
			$tmp && $result [$userId] = $tmp;
		}
		return $result;
	}
	
	
	/**
	 * 模拟数据库left join的效果
	 *
	 * @param array $tables  需要查询的表 array(true, false, true)
	 * @param array $values  对应上面需要查询的表 array($result1, false, $result3)
	 * @return array
	 */
	function _joinTables($tables, $values){
		$tableField = array($this->_membersField, $this->_memberDataField, $this->_memberInfoField);
		$tableAlias = array('m.', 'md.', 'mi.');
		$first = false;
		$result = array();
		foreach ($tables as $k => $table){
			if (!$first && $table){
				if (!$values[$k]) return false;
				$first = true;
			}
			if ($first){
				!$values[$k] && $values[$k] = $tableField[$k];
				$values[$k][$tableAlias[$k]. 'uid'] = $values[$k]['uid'];
				if (isset($result['credit'])) {
					$values[$k]['creditinfo'] = $values[$k]['credit'];
					$values[$k][$tableAlias[$k]. 'credit'] = $values[$k]['credit'];
				}
				(!isset($result['credit']) && $table && $values[$k]['credit']) && $result['credit'] = $values[$k]['credit'];
				$result += $values[$k];
			}
		}
		return $first ? $result : false;
	}

	/**
	 * 清除用户基本信息缓存
	 *
	 * @param array $userIds
	 */
	function clearCacheForMembersByUserIds($userIds) {
		$userIds = ( array ) $userIds;
		foreach ( $userIds as $uid ) {
			$this->_cacheService->delete ( $this->_getAllMembersKey ( $uid ) );
			$this->_cacheService->delete ( $this->_getMembersKey ( $uid ) );
		}
		return true;
	}

	/**
	 * 清除用户Data信息缓存
	 *
	 * @param array $userIds
	 */
	function clearCacheForMemberDataByUserIds($userIds) {
		$userIds = ( array ) $userIds;
		foreach ( $userIds as $uid ) {
			$this->_cacheService->delete ( $this->_getAllMembersKey ( $uid ) );
			$this->_cacheService->delete ( $this->_getMemberDataKey ( $uid ) );
		}
		return true;
	}

	/**
	 * 清除用户Info信息缓存
	 *
	 * @param array $userIds
	 */
	function clearCacheForMemberInfoByUserIds($userIds) {
		$userIds = ( array ) $userIds;
		foreach ( $userIds as $uid ) {
			$this->_cacheService->delete ( $this->_getAllMembersKey ( $uid ) );
			$this->_cacheService->delete ( $this->_getMemberInfoKey ( $uid ) );
		}
		return true;
	}

	/**
	 * 清除用户的SingleRight信息
	 *
	 * @param array $userIds
	 */
	function clearCacheForSingleRightByUserIds($userIds) {
		$userIds = ( array ) $userIds;
		foreach ( $userIds as $uid ) {
			$this->_cacheService->delete ( $this->_getSingleRightKey ( $uid ) );
		}
		return true;
	}

	function clearCacheForMemberCreditByUserIds($userIds) {
		$userIds = ( array ) $userIds;
		foreach ( $userIds as $uid ) {
			$this->_cacheService->delete ( $this->_getMemberCreditKey ( $uid ) );
		}
		return true;
	}

	function clearCacheForCmemberAndColonyByUserIds($userIds) {
		$userIds = ( array ) $userIds;
		foreach ( $userIds as $uid ) {
			$this->_cacheService->delete ( $this->_getCmemberAndColonyKey ( $uid ) );
		}
		return true;
	}

	function clearCacheForMemberTagsByUserIds($userIds) {
		$userIds = ( array ) $userIds;
		foreach ( $userIds as $uid ) {
			$this->_cacheService->delete ( $this->_getMemberTagsKey ( $uid ) );
		}
		return true;
	}
	
	/**
	 * 不通过缓存直接从数据库获取一组用户基本信息
	 *
	 * @param array $userIds 用户id数组
	 * @return array
	 */
	function _getMembersByUserIdsNoCache($userIds) {
		if (! S::isArray ( $userIds )) return false;
		$membersDb = L::loadDB ( 'Members', 'user' );
		return $membersDb->getUsersByUserIds ( $userIds );
	}

	/**
	 * 不通过缓存直接从数据库获取一组用户的Data信息
	 *
	 * @param array $userIds 用户id数组
	 * @return array
	 */
	function _getMemberDataByUserIdsNoCache($userIds) {
		if (! S::isArray ( $userIds )) return false;
		$memberDataDb = L::loadDB ( 'MemberData', 'user' );
		return $memberDataDb->getUsersByUserIds ( $userIds );
	}

	/**
	 * 不通过缓存直接从数据库获取一组用户的Info信息
	 *
	 * @param array $userIds 用户id数组
	 * @return array
	 */
	function _getMemberInfoByUserIdsNoCache($userIds) {
		if (! S::isArray ( $userIds )) return false;
		$memberInfoDb = L::loadDB ( 'MemberInfo', 'user' );
		return $memberInfoDb->getUsersByUserIds ( $userIds );
	}

	/**
	 * 不同过缓存直接从数据库获取一条用户基本信息
	 *
	 * @param int $userId 用户id
	 * @return array
	 */
	function _getMembersByUserIdNoCache($userId) {
		$userId = S::int ( $userId );
		if ($userId < 1) return false;
		$membersDb = L::loadDB ( 'Members', 'user' );
		return $membersDb->get ( $userId );
	}

	/**
	 * 不同过缓存直接从数据库获取一条用户Data信息
	 *
	 * @param int $userId 用户id
	 * @return array
	 */
	function _getMemberDataByUserIdNoCache($userId) {
		$userId = S::int ( $userId );
		if ($userId < 1) return false;
		$memberDataDb = L::loadDB ( 'MemberData', 'user' );
		return $memberDataDb->get ( $userId );
	}

	/**
	 * 不同过缓存直接从数据库获取一条用户Info信息
	 *
	 * @param int $userId 用户id
	 * @return array
	 */
	function _getMemberInfoByUserIdNoCache($userId) {
		$userId = S::int ( $userId );
		if ($userId < 1) return false;
		$memberInfoDb = L::loadDB ( 'MemberInfo', 'user' );
		return $memberInfoDb->get ( $userId );
	}

	/**
	 * 不通过缓存从数据库获取一条用户权限信息, 即查询pw_singleRight表
	 *
	 * @param int $userId
	 * @return array
	 */
	function _getSingleRightByUserIdNoCache($userId) {
		$userId = S::int ( $userId );
		if ($userId < 1) return false;
		$singleRightDb = L::loadDB ( 'SingleRight', 'user' );
		return $singleRightDb->get ( $userId );
	}

	/**
	 * 从数据库获取一组MemberCredit数据
	 *
	 * @param array $userIds
	 * @return array
	 */
	function _getMemberCreditByUserIdsNoCache($userIds) {
		if (!S::isArray($userIds)) return false;
		$memberCreditDb = L::loadDB ( 'MemberCredit', 'user' );
		$memberCredits = $memberCreditDb->gets ( $userIds );
		if (! S::isArray ( $memberCredits )) return false;
		$result = array ();
		foreach ( $memberCredits as $mc ) {
			$result [$mc ['uid']] [$mc ['cid']] = $mc ['value'];
			$result[$mc ['uid']]['uid'] = $mc ['uid'];
		}
		return $result;
	}

	/**
	 * 不通过缓存直接从数据库获取用户群组信息， 需要连表查询pw_cmembers和pw_colonys
	 *
	 * @param int $threadId 帖子id
	 * @return array
	 */
	function _getCmemberAndColonyByUserIdsNoCache($userIds) {
		$cmembersDb = L::loadDB ( 'cmembers', 'colony' );
		return $cmembersDb->getsCmemberAndColonyByUserIds ( $userIds );
	}

	/**
	 * 获取用户基本信息的缓存key
	 *
	 * @param int $userId 用户id
	 * @return string
	 */
	function _getMembersKey($userId) {
		return $this->_prefix . 'main_uid_' . $userId;
	}

	/**
	 * 获取用户Data信息的缓存key
	 *
	 * @param int $userId 用户id
	 * @return string
	 */
	function _getMemberDataKey($userId) {
		return $this->_prefix . 'data_uid_' . $userId;
	}

	/**
	 * 获取用户Info信息的缓存key
	 *
	 * @param int $userId 用户id
	 * @return string
	 */
	function _getMemberInfoKey($userId) {
		return $this->_prefix . 'info_uid_' . $userId;
	}

	/**
	 * 获取SingleRight表缓存key
	 *
	 * @param int $userId
	 * @return string
	 */
	function _getSingleRightKey($userId) {
		return $this->_prefix . 'singleright_uid_' . $userId;
	}

	/**
	 * 获取MemberCredit表缓存key
	 *
	 * @param int $userId
	 * @return string
	 */
	function _getMemberCreditKey($userId) {
		return $this->_prefix . 'credit_uid_' . $userId;
	}

	/**
	 * 获会员群组信息在memcache缓存的key
	 *
	 * @param int $userId 用户id
	 * @return string
	 */
	function _getCmemberAndColonyKey($userId) {
		return $this->_prefix . 'colony_uid_' . $userId;
	}

	/**
	 * 获取用户标签在memcache缓存的key
	 *
	 * @param int $userId 用户id
	 * @return string
	 */
	function _getMemberTagsKey($userId) {
		return $this->_prefix . 'membertag_uid_' . $userId;
	}
	
	
	/************************ 分隔符**********************************/

	function _getMembersAndMemberDataAndMemberInfoByUserIdsNoCache($userIds){
		global $customfield;
		$fieldinfo = '';
		if (is_array($customfield)) {
			foreach ($customfield as $value) {
				!$value['ifsys'] && $fieldinfo .= ',mi.field_'.(int)$value['id'];
			}
		}
		$membersDb = L::loadDB ( 'Members', 'user' );
		return $membersDb->getMembersAndMemberDataAndMemberInfoByUserIds ( $userIds,$fieldinfo );
	}
	function _getAllMembersKey($userId){
		return $this->_prefix . 'all_uid_' . $userId;
	}
	
	/**
	 * 获取一组用户信息
	 * 查询members,memberData, memberInfo三张表的部分字段， 仅供global.php, read.php页面里特定地方调用
	 *
	 * @param array $userIds
	 * @param unknown_type $a 备用
	 * @param unknown_type $b 备用
	 * @param unknown_type $c 备用
	 * @return array
	 */
	function getAllByUserIds($userIds, $a=false, $b=false, $c=false){
		$userIds = array_unique ( (array)$userIds );
		$result = $_tmpResult = $keys = $_tmpUserIds = array ();
		foreach ( $userIds as $userId ) {
			$keys [$this->_getAllMembersKey ( $userId )] = $userId;
		}
		if (($members = $this->_cacheService->get ( array_keys($keys) ))) {
			$_unique = $this->getUnique();
			foreach ($keys as $key=>$userId){
				$_key = $_unique . $key;
				if (isset($members[$_key]) && is_array($members[$_key])){
					$_tmpUserIds [] = $userId;
					$result[$userId] = $members[$_key];
				}
			}
		}
		$userIds = array_diff ( $userIds, $_tmpUserIds );
		if ($userIds) {
			$_tmpResult = $this->_getMembersAndMemberDataAndMemberInfoByUserIdsNoCache ( $userIds );
			foreach ($userIds as $userId){
				$this->_cacheService->set ( $this->_getAllMembersKey ( $userId ), isset($_tmpResult[$userId]) ? $_tmpResult[$userId] : array() );
			}
		}		
		return (array)$result + (array)$_tmpResult;		
	}
	
	/**
	 * 获取一条用户信息
	 * 查询members,memberData, memberInfo三张表的部分字段， 仅供global.php, read.php页面里特定地方调用
	 *
	 * @param array $userIds
	 * @param unknown_type $a 备用
	 * @param unknown_type $b 备用
	 * @param unknown_type $c 备用
	 * @return array
	 */	
	function getAllByUserId($userId, $a=false, $b=false, $c=false){
		$userId = S::int($userId);
		if ($userId < 1) return false;
		$members = $this->getAllByUserIds($userId);
		return $members ? current($members) : array();
	}
	
	/**
	 * 获取一条用户信息
	 * 查询members,memberData, singleRight三张表的部分字段， 仅供global.php页面里特定地方调用
	 *
	 * @param array $userIds
	 * @return array
	 */		
	function getMembersAndMemberDataAndSingleRightByUserId($userId) {
		$userId = S::int ( $userId );
		if ($userId < 1) return false;
		$membersAndMemberData = $this->getAllByUserId($userId);
		if (!$membersAndMemberData) return array();
		$singleRight = $this->getSingleRightByUserId($userId);
		return  (array)$membersAndMemberData + ($singleRight ? (array)$singleRight : $this->_singleRightField);
	}	
		
	/**
	 * 根据一个用户id获取用户标签
	 * 
	 * @param int $uid 用户id
	 * @return array
	 */
	function getMemberTagsByUserid($userId) {
		$userId = S::int ( $userId );
		if ($userId < 1) {
			return false;
		}
		$key = $this->_getMemberTagsKey ( $userId );
		$result = $this->_cacheService->get ( $key );
		if ($result === false) {
			$memberTagsService = L::loadClass('memberTagsService', 'user');
			$result = $memberTagsService->getMemberTagsByUidFromDB($userId);
			$result = $result ? $result : array();
			$this->_cacheService->set ( $key,  $result);
		}
		return $result;
	}
}