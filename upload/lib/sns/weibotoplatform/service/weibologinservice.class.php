<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('WeiboBindBaseService', 'sns/weibotoplatform/service', false);

define('PW_WEIBO_LOGIN_COOKIE_NAME', 'pw_weibo_login');
define('PW_WEIBO_LOGIN_COOKIE_EXIPRE', 1800);

class PW_WeiboLoginService extends PW_WeiboBindBaseService {
	
	function getLoginWays() {
		$siteBindService = $this->_getSiteBindService();
		if (!$siteBindService->isOpen()) return array();
		
		$loginTypes = array();
		foreach ($siteBindService->getBindTypes() as $type => $config) {
			if (!$config['allowLogin']) continue;
			$loginTypes[$type] = array('type' => $type, 'title' => $config['title'], 'accountTitle' => $config['accountTitle'], 'loginLogo' => $config['logo16x16'], 'isWeibo' => $config['allowSync']['weibo']);
		}
		return $loginTypes;
	}
	
	function getLoginWay($wayType) {
		$ways = $this->getLoginWays();
		return isset($ways[$wayType]) ? $ways[$wayType] : array();
	}
	
	function isWayAllowLogin($wayType) {
		$ways = $this->getLoginWays();
		return isset($ways[$wayType]);
	}
	
	function getLoginSession($sessionId) {
		global $timestamp;
		$this->_collectLoginSessionGarbage();
		$sessionDao = $this->_getLoginSessionDao();
		
		if ('' == $sessionId) return null;
		
		$sessionInfo = $sessionDao->get($sessionId);
		if ($sessionInfo['expire'] <= $timestamp) {
			$sessionDao->delete($sessionId);
			return null;
		}
		$sessionDao->update($sessionId, array('expire' => $this->_getExpireTimestamp()));
		
		return $sessionInfo;
	}
	
	function createLoginSession($sessionData = '') {
		$this->_collectLoginSessionGarbage();
		$sessionDao = $this->_getLoginSessionDao();
		
		$sessionId = $this->_generateSessionId();
		$sessionDao->add(array(
			'sessionid' => $sessionId,
			'expire' => $this->_getExpireTimestamp(),
			'sessiondata' => $sessionData,
		));
		return $sessionId;
	}
	
	function getLoginUrl($loginSessionId, $wayType) {
		$platformApiClient = $this->_getPlatformApiClient();
		return $platformApiClient->buildPageUrl(PW_PLATFORM_CLIENT_DEFAULT_GUEST_USER_ID, 'weibo.login.guide', array('siteSessionId' => $loginSessionId, 'siteSideType' => $wayType));
	}
	
	function callback($params) {
		global $db_charset;
		$platformApiClient = $this->_getPlatformApiClient();
		$got = PlatformApiClientUtility::decodeJson($platformApiClient->post('weibo.login.sitecallback', $params));
		return isset($got->result) ? $got->result : (isset($got->message) ? PlatformApiClientUtility::convertCharset('UTF-8', $db_charset, $got->message) : false);
	}
	
	function fetchBoundUser($platformSessionId) {
		$platformApiClient = $this->_getPlatformApiClient();
		$isSuccess = PlatformApiClientUtility::decodeJson($platformApiClient->post('weibo.login.fetchuser', array('platformSessionId' => $platformSessionId)));
		return isset($isSuccess->result) ? $isSuccess->result : 0;
	}
	
	function bindNewLoginUser($userId, $platformSessionId, $extraUserInfo = array()) {
		global $timestamp;
		
		if ($userId <= 0) return 0;
		$loginUserDao = $this->_getLoginUserDao();
		$extraUserInfo = is_array($extraUserInfo) ? $extraUserInfo : array();
		
		$loginUserInfo = array('uid' => $userId, 'createtime' => $timestamp);
		$loginUserInfo['extra'] = $extraUserInfo ? serialize($extraUserInfo) : '';
		$loginUserDao->add($loginUserInfo);
		
		$platformApiClient = $this->_getPlatformApiClient();
		$isSuccess = PlatformApiClientUtility::decodeJson($platformApiClient->post('weibo.login.bind', array('platformSessionId' => $platformSessionId, 'userId' => $userId, 'isNew' => true)));

		return isset($isSuccess->result) ? $isSuccess->result : 0;
	}
	
	function generateLoginTmpPassword() {
		$salt = "sdf&&*DPp;9d[9Jd(^";
		return substr(md5($salt . uniqid('', true) . mt_rand()), 0, 8);
	}
	
	function getLoginUserInfo($userId) {
		$loginUserDao = $this->_getLoginUserDao();
		$loginUserInfo = $loginUserDao->get($userId);
		$loginUserInfo['extra'] = $loginUserInfo['extra'] ? unserialize($loginUserInfo['extra']) : array();
		return $loginUserInfo;
	}
	
	function bindExistLoginUser($userId, $platformSessionId) {
		if ($userId <= 0) return 0;
		
		$platformApiClient = $this->_getPlatformApiClient();
		$isSuccess = PlatformApiClientUtility::decodeJson($platformApiClient->post('weibo.login.bind', array('platformSessionId' => $platformSessionId, 'userId' => $userId)));

		return isset($isSuccess->result) ? $isSuccess->result : 0;
	}
	
	function updateLoginSession($sessionId, $sessionData) {
		if ('' == $sessionId) return 0;
		
		$sessionInfo = $this->getLoginSession($sessionId);
		if (!$sessionInfo) return 0;
		$sessionDao = $this->_getLoginSessionDao();
		
		$sessionInfo['sessiondata'] = is_array($sessionInfo['sessiondata']) ? $sessionInfo['sessiondata'] : array();
		$sessionData = is_array($sessionData) ? $sessionData : array();
		$sessionData = array_merge($sessionInfo['sessiondata'], $sessionData);
		
		return $sessionDao->update($sessionId, array('sessiondata' => $sessionData));
	}
	
	
	
	function isLoginUserNotResetPassword($userId) {
		if ($userId <= 0) return false;
		$loginUserDao = $this->_getLoginUserDao();
		$loginUser = $loginUserDao->get($userId);
		if (!$loginUser || $loginUser['hasresetpwd']) return false;
		
		return true;
	}
	
	function resetLoginUserPassword($userId, $newPassword) {
		if ($userId <= 0 || '' == $newPassword) return 0;
		if (!$this->isLoginUserNotResetPassword($userId))  return 0;
		
		$this->setLoginUserPasswordHasReset($userId);
		
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		return $userService->update($userId, array('password' => md5($newPassword)));
	}
	
	function setLoginUserPasswordHasReset($userId) {
		if ($userId <= 0) return 0;

		$loginUserDao = $this->_getLoginUserDao();
		return $loginUserDao->update($userId, array('hasresetpwd' => 1));
	}
	

	
	function _collectLoginSessionGarbage() {
		$gcDivisor = 100;
		$gcProbability = 1;
		$sessionDao = $this->_getLoginSessionDao();
		
		if (rand(1, $gcDivisor) <= $gcProbability) {
			global $timestamp;
			$sessionDao->deletesByExpire($timestamp);
		}
	}
	
	function _generateSessionId() {
		$salt = "423^&78fdf*^\tFGFyWEId4\ra&2!cr3s56O1^";
		return md5($salt . uniqid('', true) . mt_rand());
	}
	
	function _getExpireTimestamp() {
		global $timestamp;
		return $timestamp + PW_WEIBO_LOGIN_COOKIE_EXIPRE;
	}
	
	/**
	 * @return PW_WeiboLoginSessionDB
	 */
	function _getLoginSessionDao() {
		return L::loadDB('weibologinsession','sns/weibotoplatform');
	}
	
	/**
	 * @return PW_WeiboLoginUserDB
	 */
	function _getLoginUserDao() {
		return L::loadDB('weibologinuser','sns/weibotoplatform');
	}
	
	/**
	 * @return PW_WeiboSiteBindService
	 */
	function _getSiteBindService() {
		return L::loadClass('WeiboSiteBindService', 'sns/weibotoplatform/service');
	}
}

