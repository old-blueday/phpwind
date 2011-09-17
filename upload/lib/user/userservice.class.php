<?php
/**
 * 用户服务类文件
 * 
 * @package User
 */

!defined('P_W') && exit('Forbidden');

/**
 * 用户服务对象
 * 
 * @package User
 */
class PW_UserService {
	
	/**
	 * 获取用户信息
	 *
	 * @param int $userId 用户ID
	 * @param bool $withMainFields 是否取用户主要信息
	 * @param bool $withMemberDataFields 是否取用户基本信息
	 * @param bool $withMemberInfoFields 是否取用户相关信息
	 * @return array|null 用户数据数组，找不到返回null
	 */
	function get($userId, $withMainFields = true, $withMemberDataFields = false, $withMemberInfoFields = false) {
		$userId = (int) $userId;
		if ($userId <= 0) return null;
		if (perf::checkMemcache()){
			$_cacheService = Perf::gatherCache('pw_members');
			return $_cacheService->getAllFieldByUserId($userId, $withMainFields, $withMemberDataFields, $withMemberInfoFields);			
		}
		$membersDb = $this->_getMembersDB();
		return $membersDb->getWithJoin($userId, $withMainFields, $withMemberDataFields, $withMemberInfoFields);
		/*
		$member = array();
		if ($withMainFields) {
			$membersDb = $this->_getMembersDB();
			$data = $membersDb->get($userId);
			if ($data) $member = array_merge($member, $data);
		}
		if ($withMemberDataFields) {
			$memberDataDb = $this->_getMemberDataDB();
			$data = $memberDataDb->get($userId);
			if ($data) $member = array_merge($member, $data);
		}
		if ($withMemberInfoFields) {
			$memberInfoDb = $this->_getMemberInfoDB();
			$data = $memberInfoDb->get($userId);
			if ($data) $member = array_merge($member, $data);
		}
		return $member ? $member : null;
		*/
	}
	
	/**
	 * 根据用户id批量获取用户信息
	 * @param array $userIds
	 * @return array
	 */
	function getByUserIds($userIds) {
		if (!is_array($userIds) || !count($userIds)) return array();
		if (perf::checkMemcache()){
			$_cacheService = Perf::gatherCache('pw_members');
			return $_cacheService->getMembersByUserIds($userIds);
		}
		$membersDb = $this->_getMembersDB();
		return $membersDb->getUsersByUserIds($userIds);
	}
	
	/**
	 * 根据用户id批量获取用户信息，包含memberdata表信息
	 * @param array $userIds
	 * @return array
	 */
	function getUsersWithMemberDataByUserIds($userIds) {
		if (!is_array($userIds) || !count($userIds)) return array();
		if (perf::checkMemcache()){
			$_cacheService = Perf::gatherCache('pw_members');
			return $_cacheService->getAllFieldByUserIds($userIds, true, true);
		}	
		$membersDb = $this->_getMembersDB();
		return $membersDb->getUserInfosByUserIds($userIds);
	}
	
	/**
	 * 根据用户名获取用户信息
	 *
	 * @param string $userName
	 * @param bool $withMainFields 是否取用户主要信息
	 * @param bool $withMemberDataFields 是否取用户基本信息
	 * @param bool $withMemberInfoFields 是否取用户相关信息
	 * @return array|null 用户数据数组，找不到返回null
	 */
	function getByUserName($userName, $withMainFields = true, $withMemberDataFields = false, $withMemberInfoFields = false) {
		$userName = trim($userName);
		if (!$userName) return null;
		
		$member = array();
		$membersDb = $this->_getMembersDB();
		$data = $membersDb->getUserByUserName($userName);
		if (!$data || !$data['uid']) return null;
		
		$userId = (int) $data['uid'];
		$withMainFields && $member = array_merge($member, $data);
		if ($withMemberDataFields) {
			$memberDataDb = $this->_getMemberDataDB();
			$data = $memberDataDb->get($userId);
			if ($data) $member = array_merge($member, $data);
		}
		if ($withMemberInfoFields) {
			$memberInfoDb = $this->_getMemberInfoDB();
			$data = $memberInfoDb->get($userId);
			if ($data) $member = array_merge($member, $data);
		}
		return $member ? $member : null;
	}
	
	/**
	 * 根据用户名批量获取用户信息
	 * 
	 * @param array $userNames
	 * @return array
	 */
	function getByUserNames($userNames) {
		if (!is_array($userNames) || !count($userNames)) return array();
		
		$membersDb = $this->_getMembersDB();
		return $membersDb->getUsersByUserNames($userNames);
	}
	
	/**
	 * 根据用户名获取用户id
	 * 
	 * @param string $userName 用户名
	 * @return int
	 */
	function getUserIdByUserName($userName) {
		if (!$data = $this->getByUserName($userName)) return 0;
		return (int) $data['uid'];
	}
	
	/**
	 * 根据用户email获取用户id
	 * 
	 * @param string $email 邮箱
	 * @return int
	 */
	function getUserIdByEmail($email) {
		if (!$data = $this->getByEmail($email)) return 0;
		return (int) $data['uid'];
	}
	
	/**
	 * 根据用户id获取用户名
	 * 
	 * @param int $userId 用户id
	 * @return string|null
	 */
	function getUserNameByUserId($userId) {
		$userId = S::int($userId);
		if ($userId < 1) return false;
		if (perf::checkMemcache()){
			$_cacheService = Perf::gatherCache('pw_members');
			return $_cacheService->getUserNameByUserId($userId);
		}				
		if (!$data = $this->get($userId)) return null;
		return $data['username'];
	}
	
	/**
	 * 根据用户id批量获取用户名
	 * 
	 * @param array $userIds 用户id数组
	 * @return array 以uid为key，用户名为值的数组
	 */
	function getUserNamesByUserIds($userIds) {
		if (!is_array($userIds) || !count($userIds)) return array();
		if (perf::checkMemcache()){
			$_cacheService = Perf::gatherCache('pw_members');
			return $_cacheService->getUserNameByUserIds($userIds);
		}
		$userNames = array();
		$members = $this->getByUserIds($userIds);
		foreach ($members as $member) {
			$member['uid'] && $userNames[$member['uid']] = $member['username'];
		}
		return $userNames;
	}
	
	/**
	 * 根据email获取用户信息
	 * 
	 * @param string $email
	 * @return array|null 用户数据数组，找不到返回null
	 */
	function getByEmail($email) {
		$email = trim($email);
		if ('' == $email) return null;
		
		$membersDb = $this->_getMembersDB();
		$users = $membersDb->getUserByUserEmails(array($email));
		return !empty($users) ? current($users) : null;
	}
	
	/**
	 * 根据email批量获取用户信息
	 * @param array $emails
	 * @return array
	 */
	function getByEmails($emails) {
		if (!is_array($emails) || !count($emails)) return array();
		
		$membersDb = $this->_getMembersDB();
		return $membersDb->getUserByUserEmails($emails);
	}

	
	/**
	 * 根据groupid获取多个用户信息
	 * @param array $groupIds
	 * @return array
	 */
	function getByGroupId($groupId) {
		$membersDb = $this->_getMembersDB();
		return $membersDb->getUsersByGroupId($groupId);
	}
	
	
	/**
	 * 根据groupid批量获取多个用户信息
	 * @param array $groupIds
	 * @return array
	 */
	function getByGroupIds($groupIds) {
		if (!is_array($groupIds) || !count($groupIds)) return array();
		
		$membersDb = $this->_getMembersDB();
		return $membersDb->getUsersByGroupIds($groupIds);
	}
	
	/**
	 * 查找最新用户
	 * 
	 * @return array|null 最新用户信息，找不到返回null
	 */
	function getLatestNewUser() {
		$membersDb = $this->_getMembersDB();
		$users = $membersDb->findUsersOrderByUserId();
		return count($users) ? current($users) : null;
	}
	
	/**
	 * 查找最新的几个用户
	 * 
	 * @return array
	 */
	function findLatestNewUsers($number = 10) {
		$number = intval($number);
		if ($number <= 0) return array();
		
		$membersDb = $this->_getMembersDB();
		return $membersDb->findUsersOrderByUserId($number);
	}
	
	/**
	 * 查找最新的几个未被禁言的用户
	 * 
	 * @return array
	 */
	function findNotBannedNewUsers($number = 10) {
		$number = intval($number);
		if ($number <= 0) return array();
		
		$membersDb = $this->_getMembersDB();
		return $membersDb->findNotBannedUsersOrderByUserId($number);
	}
	
	
	/**
	 * 获得Members全部数据的个数
	 */
	function count() {
		$membersDb = $this->_getMembersDB();
		return $membersDb->_count();
	}
	
	/**
	 * 添加一个用户
	 * 
	 * @param array $mainFields 用户主要信息数组
	 * @param array $memberDataFields 用户基本信息数组
	 * @param array $memberInfoFields 用户相关信息数组
	 * @return int 新增用户id，失败返回0
	 */
	function add($mainFields, $memberDataFields = array(), $memberInfoFields = array()) {
		if (!is_array($mainFields) || !count($mainFields)) return 0;
		if (!isset($mainFields['username']) || !isset($mainFields['password'])) return 0;
		if ('' == $mainFields['username'] || '' == $mainFields['password']) return 0;
		
		$membersDb = $this->_getMembersDB();
		$userId = $membersDb->insert($mainFields);
		if (!$userId) return 0;
		
		$memberDataFields['uid'] = $userId;
		$memberDataDb = $this->_getMemberDataDB();
		$memberDataDb->insert($memberDataFields);
		
		$this->_replaceMemberInfo($userId, $memberInfoFields, false);
		
		return $userId;
	}
	
	/**
	 * 更新用户信息
	 * 
	 * @param int $userId
	 * @param array $mainFields 用户主要信息数组
	 * @param array $memberDataFields 用户基本信息数组
	 * @param array $memberInfoFields 用户相关信息数组
	 * @return bool 是否更新
	 */
	function update($userId, $mainFields = array(), $memberDataFields = array(), $memberInfoFields = array()) {
		$userId = intval($userId);
		if ($userId <= 0) return false;
		
		$updates = 0;
		if (is_array($mainFields) && count($mainFields)) {
			$membersDb = $this->_getMembersDB();
			$updates += $membersDb->update($mainFields, $userId); //TODO refactor update
		}
		if (is_array($memberDataFields) && count($memberDataFields)) {
			$memberDataDb = $this->_getMemberDataDB();
			$updates += $memberDataDb->update($memberDataFields, $userId);
		}
		$updates += $this->_replaceMemberInfo($userId, $memberInfoFields);
		
		return (bool) $updates;
	}
	
	/**
	 * 批量更新用户信息
	 * 
	 * @param array $userIds
	 * @param array $mainFields 用户主要信息数组
	 * @param array $memberDataFields 用户基本信息数组
	 * @param array $memberInfoFields 用户相关信息数组
	 * @return int 更新个数
	 */
	function updates($userIds, $mainFields = array(), $memberDataFields = array(), $memberInfoFields = array()) {
		if (!is_array($userIds) || !count($userIds)) return 0;
		
		$updates = 0;
		if (is_array($mainFields) && count($mainFields)) {
			$membersDb = $this->_getMembersDB();
			$updates += $membersDb->updates($mainFields, $userIds); //TODO refactor update
		}
		if (is_array($memberDataFields) && count($memberDataFields)) {
			$memberDataDb = $this->_getMemberDataDB();
			$updates += $memberDataDb->updates($memberDataFields, $userIds);
		}
		if (is_array($memberInfoFields) && count($memberInfoFields)) {
			foreach ($userIds as $userId) {
				$updates += $this->_replaceMemberInfo($userId, $memberInfoFields);
			}
		}
		
		return $updates;
	}
	
	function clearUserMessage($uid){
		$uid = intval($uid);
		if ($uid < 1) return false;
		$this->update($uid, array('newpm'=>0), array('newfans'=>0,'newreferto'=>0,'newnotice'=>0,'newrequest'=>0));
	}
	
	/**
	 * 增量更新用户信息
	 * 
	 * @param int $userId
	 * @param array $mainFields 用户主要信息数组
	 * @param array $memberDataFields 用户基本信息数组
	 * @param array $memberInfoFields 用户相关信息数组
	 * @return bool
	 */
	function updateByIncrement($userId, $mainFields = array(), $memberDataFields = array(), $memberInfoFields = array()) {
		$userId = intval($userId);
		if ($userId <= 0) return false;
		
		$updates = 0;
		if (is_array($mainFields) && count($mainFields)) {
			$membersDb = $this->_getMembersDB();
			$updates += $membersDb->increase($userId, $mainFields);
		}
		if (is_array($memberDataFields) && count($memberDataFields)) {
			$memberDataDb = $this->_getMemberDataDB();
			$updates += $memberDataDb->increase($userId, $memberDataFields);
		}
		if (is_array($memberInfoFields) && count($memberInfoFields)) {
			$memberInfoDb = $this->_getMemberInfoDB();
			$updates += $memberInfoDb->increase($userId, $memberInfoFields);
		}
		return (bool) $updates;
	}
	
	/**
	 * 批量按增量更新用户信息
	 * 
	 * @param array $userIds
	 * @param array $mainFields 用户主要信息数组
	 * @param array $memberDataFields 用户基本信息数组
	 * @param array $memberInfoFields 用户相关信息数组
	 * @return int 更新个数
	 */
	function updatesByIncrement($userIds, $mainFields = array(), $memberDataFields = array(), $memberInfoFields = array()) {
		if (!is_array($userIds) || !count($userIds)) return 0;
		
		$updates = 0;
		foreach ($userIds as $userId) {
			$updates += (int) $this->updateByIncrement($userId, $mainFields, $memberDataFields, $memberInfoFields);
		}
		return $updates;
	}
	/**
	 * 处理溢出数据
	 * @param $type	溢出字段
	 */
	function updateOverflow($type) {
		$memberDataDb = $this->_getMemberDataDB();
		return $memberDataDb->updateOverflow($type);
	}

	/**
	 * 设置用户某个类型的状态
	 * 
	 * @param int $userId 用户id
	 * @param int $type 用户状态类型 常量：PW_USERSTATUS_*
	 * @param bool|int $status 状态值，0-false, 1-true, other
	 * @return bool
	 */
	function setUserStatus($userId, $type, $status = true) {
		list($userId, $type) = array(intval($userId), intval($type));
		if ($userId <= 0 || $type <= 0) return false;

		$num = $this->_getUserStatusNumberWithUserStatusType($type);
		$membersDb = $this->_getMembersDB();
		return (bool)$membersDb->setUserStatus($userId, $type, $status, $num);
	}

	
	/**
	 * 获取用户某个类型的状态
	 * 
	 * @param int $userId 用户id
	 * @param int $type 用户状态类型 常量：PW_USERSTATUS_*
	 * @return int
	 */
	function getUserStatus($userId, $type) {
		list($userId, $type) = array(intval($userId), intval($type));
		if ($userId <= 0 || $type <= 0) return false;
		if (!$user = $this->get($userId)) return false;
		$num = $this->_getUserStatusNumberWithUserStatusType($type);
		$user['userstatus'] >>= --$type;
		return bindec(substr(sprintf('%0'.$num.'b', $user['userstatus']), -$num));
	}
	
	/**
	 * 删除用户
	 * 
	 * @param int $userId
	 * @return bool
	 */
	function delete($userId) {
		$membersDb = $this->_getMembersDB();
		$memberDataDb = $this->_getMemberDataDB();
		$memberInfoDb = $this->_getMemberInfoDB();
		$banUserDb = $this->_getBanUserDB();
		
		$memberDataDb->delete($userId);
		$memberInfoDb->delete($userId);
		$banUserDb->deleteByUserId($userId);
		return (bool) $membersDb->delete($userId);
	}
	
	/**
	 * 删除多个用户
	 * 
	 * @param array $userIds
	 * @return int 删除个数
	 */
	function deletes($userIds) {
		if (!is_array($userIds) || !count($userIds)) return 0;
		
		$deletes = 0;
		foreach ($userIds as $userId) {
			$deletes += $this->delete($userId);
		}
		return $deletes;
	}
	
	/**
	 * 根据用户id判断用户是否存在
	 * 
	 * @param int $userId
	 * @return boolean
	 */
	function isExist($userId) {
		if (!$data = $this->get($userId)) return false;
		return (bool)$data['uid'];
	}
	
	/**
	 * 根据用户名判断用户是否存在
	 * 
	 * @param string $userName
	 * @return boolean
	 */
	function isExistByUserName($userName) {
		if (!$data = $this->getByUserName($userName)) return false;
		return (bool)$data['uid'];
	}
	
	function findOnlineUsers($onlineTimestamp) { //TODO move to OnlineUserService
		$onlineTimestamp = intval($onlineTimestamp);
		
		$memberDataDb = $this->_getMemberDataDB();
		return $memberDataDb->getOnlineUsers($onlineTimestamp);
	}
	
	/**
	 * 激活码激活用户
	 * 
	 * @param int $userId
	 * @param string $activateCode 激活码
	 * @param string $siteHash 站点hash
	 * @param string $toemail 激活邮箱地址
	 * @return bool 是否激活成功
	 */
	function activateUser($userId, $activateCode, $siteHash,$toemail) {
		$userId = (int) $userId;
		$activateCode = trim($activateCode);
		if ($userId <= 0 || '' == $activateCode) return false;
		
		$membersDb = $this->_getMembersDB();
		$user = $membersDb->get($userId);
		if($user['email'] != $toemail) return false;
		if (!$user) return false;
		
		$comparedActivateCode = $this->_generateUserActivateCode($user, $siteHash);
		if ($comparedActivateCode == $activateCode) {
			$this->update($userId, array('yz' => 1));
			return true;
		}
		return false;
	}
	
	/**
	 * 获取未激活用户信息
	 * 
	 * @param int $userId 用户id
	 * @param string $email 用户email，这两个参数传入一个即可
	 * @param string $siteHash 站点hash
	 * @return array|null 用户数据数组（带activateCode字段，为该用户的激活码），找不到返回null
	 */
	function getUnactivatedUser($userId, $email, $siteHash) {
		$user = null;
		if ($userId) $user = $this->get($userId);
		if (!$user) $user = $this->getByEmail($email);
		
		if (!$user) return null;
		if ($user['yz'] <= 1) return null;
		
		$user['activateCode'] = $this->_generateUserActivateCode($user, $siteHash);
		return $user;
	}

	/**
	 * 返回某个状态类型所占bit位个数
	 * 
	 * @param int $type 用户状态类型 常量：PW_USERSTATUS_*
	 * @return int
	 */
	function _getUserStatusNumberWithUserStatusType($type) {
		switch ($type) {
			case PW_USERSTATUS_CFGFRIEND : $num = 2; break;
			default: $num = 1;
		}
		return $num;
	}
	
	function _generateUserActivateCode($userData, $siteHash) {
		return md5($userData['yz'] . substr(md5($siteHash), 0, 5) . substr(md5($userData['username']), 0, 5));
	}
	
	function _replaceMemberInfo($userId, $fieldsData, $checkExist = true) {
		if (!is_array($fieldsData) || !count($fieldsData)) return 0;
		
		$memberInfoDb = $this->_getMemberInfoDB();
		
		if ($checkExist && $memberInfoDb->get($userId)) {
			return $memberInfoDb->update($fieldsData, $userId);
		} else {
			$fieldsData['uid'] = $userId;
			return $memberInfoDb->insert($fieldsData);
		}
	}
	
	/**
	 * 组装在线用户现居地、家乡、教育、工作经历等信息
	 * 
	 * @param int $userId 用户id
	 * @return array
	 */
	function getOnLineUsers() {
		global $winduid;
		$onlineUsers = GetOnlineUser();
		if (!s::isArray($onlineUsers)) return array();
		$userIds = array();
		foreach ($onlineUsers as $key => $v) {
			if ($key == $winduid) continue;
			$userIds[] = $key;
		}
		return $userIds;
	}
	
	/**
	 * 组装用户uids
	 * 
	 * @param array $fieldsData 用户信息
	 * @return array
	 */
	function buildUids($fieldsData) {
		$uids = array();
		foreach ((array)$fieldsData as $v) {
			$uids[] = $v['uid'];
		}
		return array_diff($uids,$winduid);
	}
	
	/**
	 * 组装用户信息uid、username、face、在线图标
	 * 
	 * @param array $uids
	 * @return array
	 */
	function buildUserInfo($uids) {
		if (!s::isArray($uids)) return array();
		require_once(R_P.'require/showimg.php');
		$userInfo = array();
		foreach ((array)$this->getUsersWithMemberDataByUserIds($uids) as $data) {
			$user['uid'] = $data['uid'];
			$user['username'] = $data['username'];
			$user['thisvisit'] = $data['thisvisit'];
			list($user['face']) = showfacedesign($data['icon'], '1', 's');
			$userInfo[] = $user;
		}
		return $userInfo;
	}
	
	/**
	 * 可能认识的人
	 * 
	 * @param int $userId 用户id
	 * @return array
	 */
	function getMayKnownUserIds($fieldsData,$num = 12) {
		$onlineUserIds = $this->getOnLineUsers();
		if (!s::isArray($onlineUserIds)) return array();
		if (count($onlineUserIds) <= $num) return $onlineUserIds;
		
		$tmpApartmentUsers = $this->getUsersByApartmentAndUserIds($fieldsData['apartment'],$onlineUserIds,$num);
		$countApartmentUser = count($tmpApartmentUsers);
		$apartmentUsers = $this->buildUids($tmpApartmentUsers);
		if ($countApartmentUser >= $num) return $apartmentUsers;
		$homeUids = array_diff($onlineUserIds,$apartmentUsers);
		$homeNum = $num - $countApartmentUser;

		$tmpHomeUsers = $this->getUsersByHomeAndUserIds($fieldsData['home'],$homeUids,$homeNum);
		$countHomeUser = count($tmpHomeUsers);
		$homeUsers = $this->buildUids($tmpHomeUsers);
		if ($countHomeUser >= $homeNum) return array_merge($apartmentUsers,$homeUsers);
		$companyUids = array_diff($homeUids,$homeUsers);
		$companyNum = $homeNum - $countPlaceUser;
			
		$tmpCompanyUsers = $this->getUsersByCompanyidAndUserIds($fieldsData['companyid'],$companyUids,$companyNum);
		$countCompanyUser = count($tmpCompanyUsers);
		$companyUsers = $this->buildUids($tmpCompanyUsers);
		if ($countCompanyUser >= $companyNum) return array_merge($apartmentUsers,$homeUsers,$companyUsers);
		$educationUids = array_diff($companyUids,$companyUsers);
		$educationNum = $companyNum - $countCompanyUser;

		$tmpEducationUsers = $this->getUsersBySchoolidsAndUserIds($fieldsData['schoolid'],$educationUids,$educationNum);
		$countEducationUser = count($tmpEducationUsers);
		$educationUsers = $this->buildUids($tmpEducationUsers);
		if ($countEducationUser >= $educationNum) return array_merge($apartmentUsers,$homeUsers,$companyUsers,$educationUsers);
		$endUids = array_diff($educationUids,$educationUsers);
		$endNum = $educationNum - $countEducationUser;
		
		return array_merge($apartmentUsers,$homeUsers,$companyUsers,$educationUsers,array_slice($endUids,0,$endNum));
	}
	
	/**
	 * 根据所在地apartment和userIds统计用户
	 * 
	 * @param int $apartment 所在地
	 * @param array $userIds 用户ids
	 * @return int
	 */
	function countUsersByApartmentAndUserIds($apartment,$userIds) {
		$apartment = intval($apartment);
		if ($apartment < 1 || !s::isArray($userIds)) return 0;
		$membersDb = $this->_getMembersDB();
		return $membersDb->countUsersByApartmentAndUserIds($apartment,$userIds);
	}
	
	/**
	 * 根据所在地apartment和userIds获取用户
	 * 
	 * @param int $apartment 所在地
	 * @param array $userIds 用户ids
	 * @return array
	 */
	function getUsersByApartmentAndUserIds($apartment,$userIds,$num) {
		$apartment = intval($apartment);
		if ($apartment < 1 || !s::isArray($userIds)) return array();
		$membersDb = $this->_getMembersDB();
		if ($this->countUsersByApartmentAndUserIds($apartment,$userIds) < 1) return array();
		return $membersDb->getUsersByApartmentAndUserIds($apartment,$userIds,$num);
	}
	
	/**
	 * 根据家乡home和userIds统计用户
	 * 
	 * @param int $home 所在地
	 * @param array $userIds 用户ids
	 * @return int
	 */
	function countUsersByHomeAndUserIds($home,$userIds) {
		$home = intval($home);
		if ($home < 1 || !s::isArray($userIds)) return 0;
		$membersDb = $this->_getMembersDB();
		return $membersDb->countUsersByHomeAndUserIds($home,$userIds);
	}
	
	/**
	 * 根据家乡home和userIds获取用户
	 * 
	 * @param int $home 家乡
	 * @param array $userIds 用户ids
	 * @return array
	 */
	function getUsersByHomeAndUserIds($home,$userIds,$num) {
		$home = intval($home);
		if ($home < 1 || !s::isArray($userIds)) return array();
		$membersDb = $this->_getMembersDB();
		if ($this->countUsersByHomeAndUserIds($home,$userIds) < 1) return array();
		return $membersDb->getUsersByHomeAndUserIds($home,$userIds,$num);
	}
	
	/**
	 * 根据工作经历companyids和userIds统计用户
	 * 
	 * @param array $companyids
	 * @param array $userIds 用户ids
	 * @return array
	 */
	function countUsersByCompanyidAndUserIds($companyids,$userIds) {
		if (!s::isArray($companyids) || !s::isArray($userIds)) return 0;
		$membersDb = $this->_getMembersDB();
		return $membersDb->countUsersByCompanyidAndUserIds($companyids,$userIds);
	}
	
	/**
	 * 根据工作经历companyids和userIds获取用户
	 * 
	 * @param array $companyids
	 * @param array $userIds 用户ids
	 * @return array
	 */
	function getUsersByCompanyidAndUserIds($companyids,$userIds,$num) {
		if (!s::isArray($companyids) || !s::isArray($userIds)) return array();
		$membersDb = $this->_getMembersDB();
		if ($this->countUsersByCompanyidAndUserIds($companyids,$userIds) < 1) return array();
		return $membersDb->getUsersByCompanyidAndUserIds($companyids,$userIds,$num);
	}
	
	/**
	 * 根据教育经历schoolids和userIds统计用户
	 * 
	 * @param array $schoolids
	 * @param array $userIds 用户ids
	 * @return array
	 */
	function countUsersBySchoolidsAndUserIds($schoolids,$userIds) {
		if (!s::isArray($schoolids) || !s::isArray($userIds)) return 0;
		$membersDb = $this->_getMembersDB();
		return $membersDb->countUsersBySchoolidsAndUserIds($schoolids,$userIds);
	}
	
	/**
	 * 根据教育经历schoolids和userIds获取用户
	 * 
	 * @param array $companyids
	 * @param array $userIds 用户ids
	 * @return array
	 */
	function getUsersBySchoolidsAndUserIds($schoolids,$userIds,$num) {
		if (!s::isArray($schoolids) || !s::isArray($userIds)) return array();
		$membersDb = $this->_getMembersDB();
		if ($this->countUsersBySchoolidsAndUserIds($schoolids,$userIds) < 1) return array();
		return $membersDb->getUsersByCompanyidAndUserIds($schoolids,$userIds,$num);
	}
	
	/**
	 * 获取单条用户、教育、所在地、家乡、工作经历等信息
	 * 
	 * @param int $userId 用户id
	 * @return array
	 */
	function getUserInfoByUserId($userId) {
		$userId = intval($userId);
		if ($userId < 1) return array();
		$membersDb = $this->_getMembersDB();
		return $membersDb->getUserInfoByUserId($userId);
	}
	
	/**
	 * 用户组权限
	 * 
	 * @param int $groupId 用户组
	 * @return array
	 */
	function getRightByGroupId($groupId){
		static $groupRight;
		if (file_exists(D_P . "data/groupdb/group_$groupId.php")) {
			extract(pwCache::getData(S::escapePath(D_P . "data/groupdb/group_$groupId.php"),false));
			$groupRight = $_G;
		}
		return $groupRight;
	}

	function getUserInfoWithFace($uids) {
		if(!S::isArray($uids)) return array();
		require_once (R_P . 'require/showimg.php');
		$usersInfo = array();
		$users = $this->getByUserIds($uids); //'m.uid','m.username','m.icon','m.groupid'

		foreach ($users as $key => $value) {
			list($value['icon']) = showfacedesign($value['icon'], 1, 's');
			$usersInfo[$value['uid']] = $value;
		}
		return $usersInfo;
	}
	
	/**
	 * get PW_MembersDB
	 * 
	 * @access protected
	 * @return PW_MembersDB
	 */
	function _getMembersDB() {
		return L::loadDB('Members', 'user');
	}
	
	/**
	 * get PW_MemberdataDB
	 * 
	 * @return PW_MemberdataDB
	 */
	function _getMemberDataDB() {
		return L::loadDB('MemberData', 'user');
	}
	
	/**
	 * get PW_MemberinfoDB
	 * 
	 * @return PW_MemberinfoDB
	 */
	function _getMemberInfoDB() {
		return L::loadDB('MemberInfo', 'user');
	}
	
	/**
	 * @return PW_BanUserDB
	 */
	function _getBanUserDB() {
		return L::loadDB('BanUser', 'user');
	}
}

