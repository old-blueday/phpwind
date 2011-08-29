<?php
!defined('P_W') && exit('Forbidden');

define('PW_WEIBO_BINDTYPE_SINA', 'sinaweibo');

class PW_WeiboBindService {
	
	function getBindUrl($siteUserId) {
		$platformApiClient = $this->_getPlatformApiClient();
		return $platformApiClient->buildPageUrl($siteUserId, 'weibo.bind.guide');
	}
	
	function getAppConfigUrl() {
		$platformApiClient = $this->_getPlatformApiClient();
		return $platformApiClient->buildPageUrl(0, 'weibo.setting');
	}
	
	function callPlatformUnBind($siteUserId, $weiboType) {
		$platformApiClient = $this->_getPlatformApiClient();
		return $platformApiClient->post('weibo.bind.release', array('site_uid' => $siteUserId, 'weibo_type' => $weiboType));
	}
	
	function _getPlatformApiClient() {
		static $client = null;
		if (!$client) {
			global $db_sitehash, $db_siteownerid;
			L::loadClass('client', 'utility/platformapisdk', false);
			$client = new PlatformApiClient($db_sitehash, $db_siteownerid);
		}
		return $client;
	}
	
	function _decodeApiData($data) {
		L::loadClass('json', 'utility', false);
		$json = new Services_JSON();
		return $json->decode($data);
	}
	
	function localBind($userId, $weiboType, $bindInfo) {
		$userId = intval($userId);
		if ($userId <= 0 || !$this->_checkWeiboType($weiboType)) return false;
		
		$bindDao = $this->_getBindDao();
		$existBind = $bindDao->get($userId, $weiboType);
		if ($existBind) {
			return (bool) $bindDao->update($userId, $weiboType, $bindInfo);
		}
		return (bool) $bindDao->add($userId, $weiboType, $bindInfo);
	}
	
	function updateBindInfo($userId, $weiboType, $bindInfo) {
		$userId = intval($userId);
		if ($userId <= 0 || !$this->_checkWeiboType($weiboType)) return false;
		
		$bindDao = $this->_getBindDao();
		return (bool) $bindDao->update($userId, $weiboType, $bindInfo);
	}
	
	function localUnBind($userId, $weiboType) {
		$userId = intval($userId);
		if ($userId <= 0 || !$this->_checkWeiboType($weiboType)) return false;
		
		$bindDao = $this->_getBindDao();
		return (bool) $bindDao->delete($userId, $weiboType);
	}
	
	function getLocalBindInfo($userId, $weiboType) {
		$userId = intval($userId);
		if ($userId <= 0 || !$this->_checkWeiboType($weiboType)) return false;
		
		$bindDao = $this->_getBindDao();
		return $bindDao->get($userId, $weiboType);
	}
	
	function getUsersLocalBindInfo($userIds, $weiboType) {
		if (!is_array($userIds) || !count($userIds) || !$this->_checkWeiboType($weiboType)) return array();
		
		$bindDao = $this->_getBindDao();
		return $bindDao->gets($userIds, $weiboType);
	}
	
	function isLocalBind($userId, $weiboType) {
		return (bool) $this->getLocalBindInfo($userId, $weiboType);
	}
	
	
	
	function _checkWeiboType($weiboType) {
		return in_array($weiboType, array(PW_WEIBO_BINDTYPE_SINA));
	}
	
	/**
	 * @return PW_WeiboBindDB
	 */
	function _getBindDao() {
		return L::loadDB('weibobind','sns/weibotoplatform');
	}
}
