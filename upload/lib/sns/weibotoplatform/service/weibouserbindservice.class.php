<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('WeiboBindBaseService', 'sns/weibotoplatform/service', false);

class PW_WeiboUserBindService extends PW_WeiboBindBaseService {
	function getBindList($userId) {
		$userId = intval($userId);
		if ($userId <= 0) return array();
		
		$siteBindService = $this->_getSiteBindService();
		$userBindDao = $this->_getBindDao();
		
		$bindList = array();
		$userBindTypes = $userBindDao->getAllByUserId($userId);
		foreach ($siteBindService->getBindTypes() as $bindType => $bindTypeConfig) {
			$bindList[$bindType] = array(
				'config' => $bindTypeConfig,
				'isBind' => isset($userBindTypes[$bindType]),
				'bindInfo' => isset($userBindTypes[$bindType]) ? $userBindTypes[$bindType] : array(),
				'bindUrl' => isset($userBindTypes[$bindType]) ? '' : $this->_getBindUrl($userId, $bindType),
			);
		}
		return $bindList;
	}
	
	function callback($userId, $params) {
		global $db_charset;
		$params['site_uid'] = $userId;
		$platformApiClient = $this->_getPlatformApiClient();
		$got = PlatformApiClientUtility::decodeJson($platformApiClient->post('weibo.bind.sitecallback', $params));
		return isset($got->result) ? $got->result : (isset($got->message) ? PlatformApiClientUtility::convertCharset('UTF-8', $db_charset, $got->message) : false);
	}
	
	function isBindOne($userId) {
		$userId = intval($userId);
		if ($userId <= 0) return false;
		
		$userBindDao = $this->_getBindDao();
		return (bool) $userBindDao->getAllByUserId($userId);
	}
	
	function getABind($userId) {
		$userId = intval($userId);
		if ($userId <= 0) return null;
		
		$userBindDao = $this->_getBindDao();
		$binds = $userBindDao->getAllByUserId($userId);
		return $binds ? current($binds) : null;
	}
	
	function isBind($userId, $bindType) {
		return (bool) $this->getBindInfo($userId, $bindType);
	}
	
	function getBindInfo($userId, $bindType) {
		$userId = intval($userId);
		if ($userId <= 0) return false;
		
		$bindDao = $this->_getBindDao();
		return $bindDao->get($userId, $bindType);
	}
	
	function localBind($userId, $bindType, $bindInfo) {
		$userId = intval($userId);
		if ($userId <= 0) return false;
		
		$userBindDao = $this->_getBindDao();
		$existBind = $userBindDao->get($userId, $bindType);
		if ($existBind) {
			$userBindDao->update($userId, $bindType, $bindInfo);
			return true;
		}

		return (bool) $userBindDao->add($userId, $bindType, $bindInfo);
	}
	
	function localUnBind($userId, $bindType) {
		$userId = intval($userId);
		if ($userId <= 0) return false;
		
		$bindDao = $this->_getBindDao();
		return (bool) $bindDao->delete($userId, $bindType);
	}
	
	function unbind($siteUserId, $bindType) {
		$platformApiClient = $this->_getPlatformApiClient();
		$isSuccess = PlatformApiClientUtility::decodeJson($platformApiClient->post('weibo.bind.release', array('site_uid' => $siteUserId, 'bind_type' => $bindType)));
		return isset($isSuccess->result) && $isSuccess->result;
	}
	
	function follow($bindType, $siteUserId, $siteUserFriendId = 0) {
		$platformApiClient = $this->_getPlatformApiClient();
		$isSuccess = PlatformApiClientUtility::decodeJson($platformApiClient->post('weibo.friendship.follow', array('site_uid' => $siteUserId, 'bind_type' => $bindType, 'friend_uid' => $siteUserFriendId)));
		return isset($isSuccess->result) && $isSuccess->result;
	}
	
	function isFollow($bindType, $siteUserId, $siteUserFriendId = 0) {
		$platformApiClient = $this->_getPlatformApiClient();
		$isSuccess = PlatformApiClientUtility::decodeJson($platformApiClient->post('weibo.friendship.isfollow', array('site_uid' => $siteUserId, 'bind_type' => $bindType, 'friend_uid' => $siteUserFriendId)));
		return isset($isSuccess->result) && $isSuccess->result;
	}
	
	/**
	 * @return array bindType => userId => bindInfo
	 */
	function getUsersLocalBindInfo($userIds) {
		if (!is_array($userIds) || !count($userIds)) return array();
		
		$bindDao = $this->_getBindDao();
		return $bindDao->gets($userIds);
	}
	
	function updateBindInfo($userId, $bindType, $bindInfo) {
		$userId = intval($userId);
		if ($userId <= 0) return false;
		
		$bindDao = $this->_getBindDao();
		return (bool) $bindDao->update($userId, $bindType, $bindInfo);
	}
	
	
	

	function _getBindUrl($siteUserId, $bindType) {
		$platformApiClient = $this->_getPlatformApiClient();
		return $platformApiClient->buildPageUrl($siteUserId, 'weibo.bind.guide', array('bind_type' => $bindType));
	}
	
	
	
	/**
	 * @return PW_WeiboSiteBindService
	 */
	function _getSiteBindService() {
		return L::loadClass('WeiboSiteBindService', 'sns/weibotoplatform/service');
	}
	
	/**
	 * @return PW_WeiboBindDB
	 */
	function _getBindDao() {
		return L::loadDB('weibobind','sns/weibotoplatform');
	}
	
}

