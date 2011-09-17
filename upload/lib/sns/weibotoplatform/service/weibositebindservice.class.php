<?php
!defined('P_W') && exit('Forbidden');

define('PW_PLATFORM_WEIBO_APP_CLIENT_VERSION', '1.5');

L::loadClass('WeiboBindBaseService', 'sns/weibotoplatform/service', false);

class PW_WeiboSiteBindService extends PW_WeiboBindBaseService {
	var $_config_key = 'db_platform_weibo';
	
	function open($bindTypes) {
		if (!is_array($bindTypes) || !count($bindTypes)) return false;
		
		$bindConfig = new PW_WeiboSiteBindConfig(array('status' => true, 'bindTypes' => $bindTypes));
		$this->_saveBindConfig($bindConfig);
		return true;
	}
	
	function close() {
		$tmp = $this->_getBindConfig();
		$this->_saveBindConfig($tmp->setStatus(false));
		return true;
	}
	
	function isOpen() {
		$tmp = $this->_getBindConfig();
		return $tmp->isOpen();
	}
	
	function isBind($bindType) {
		if (!$this->isOpen()) return false;
		$types = $this->getBindTypes();
		return isset($types[$bindType]);
	}
	
	function getBindTypes() {
		if (!$this->isOpen()) return array();
		$tmp = $this->_getBindConfig();
		return $tmp->getBindTypes();
	}
	
	function getBindType($bindType) {
		if (!$this->isOpen()) return array();
		$types = $this->getBindTypes();
		return isset($types[$bindType]) ? $types[$bindType] : array();
	}
	
	function getAppConfigUrl() {
		$platformApiClient = $this->_getPlatformApiClient();
		return $platformApiClient->buildPageUrl(PW_PLATFORM_CLIENT_DEFAULT_ADMIN_USER_ID, 'weibo.setting', array('weibo_app_v' => PW_PLATFORM_WEIBO_APP_CLIENT_VERSION));
	}
	
	
	
	/**
	 * @return PW_WeiboSiteBindConfig
	 */
	function _getBindConfig() {
		static $_config = null;
		if (null === $_config) {
			global $db_sinaweibo_status, ${$this->_config_key};
			
			if (null !== ${$this->_config_key}) {
				$_config = new PW_WeiboSiteBindConfig(${$this->_config_key});
			} elseif (null !== $db_sinaweibo_status) {
				if ($db_sinaweibo_status) {
					//TODO types config
					$_config = new PW_WeiboSiteBindConfig(array(
						'status' => true, 
						'bindTypes' => array(
							'sinaweibo' => array(
								'title' => '新浪微博',
								'accountTitle' => '微博帐号',
								'description' => '使用新浪微博帐号登录，并同步微博',
								'typeId' => 50,
						
								'logoRectangle' => 'http://apps.phpwind.net/statics/weibo/icon/sina.png',
								'logo16x16' => 'http://apps.phpwind.net/statics/weibo/icon/sina_n.png',
								'logoGray16x16' => 'http://apps.phpwind.net/statics/weibo/icon/sina_o.png',
								'logo48x48' => 'http://apps.phpwind.net/statics/weibo/icon/sina48x48.png',
								
								'allowLogin' => true,
								'allowSync' => array(
									'weibo' => true,
								),
								
								'uidUrlPrefix' => 'http://t.sina.com.cn/',
							)
						)
					));
				} else {
					$_config = new PW_WeiboSiteBindConfig;
				}
				$this->_saveBindConfig($_config);
			} else {
				$_config = new PW_WeiboSiteBindConfig;
			}
		}
		return $_config;
		
	}
	
	/**
	 * 
	 * @param PW_WeiboSiteBindConfig $config
	 */
	function _saveBindConfig($bindConfig) {
		require_once (R_P . 'admin/cache.php');
		setConfig($this->_config_key, $bindConfig->toArray());
		updatecache_c();
	}
}

class PW_WeiboSiteBindConfig {
	var $_isOpen = false;
	var $_bindTypes = array();
	
	function PW_WeiboSiteBindConfig($config = array()) {
		$this->_isOpen = isset($config['status']) ? ((bool) $config['status']) : false;
		$this->_bindTypes = isset($config['bindTypes']) ? $config['bindTypes'] : array();
	}
	
	function isOpen() {
		return $this->_isOpen;
	}
	
	/**
	 * 
	 * @param $isOpen
	 * @return PW_WeiboSiteBindConfig
	 */
	function setStatus($isOpen) {
		$this->_isOpen = (bool) $isOpen;
		return $this;
	}
	
	function getBindTypes() {
		return $this->_bindTypes;
	}
	
	function toArray() {
		return array(
			'status' => $this->isOpen(),
			'bindTypes' => $this->getBindTypes(),
		);
	}
}
