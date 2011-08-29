<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('WeiboBindBaseService', 'sns/weibotoplatform/service', false);

class PW_WeiboSiteBindInfoService extends PW_WeiboBindBaseService {
	var $_config_key = 'db_platform_weibo_bind_info';
	
	function getWeiboTemplates() {
		$templates = $this->_getBindInfo('weiboTemplates');
		if (!$templates) return $this->_getDefaultWeiboTemplates();
		
		return $templates;
	}
	
	function saveWeiboTemplates($templates) {
		return $this->_saveBindInfo(array('weiboTemplates' => $templates));
	}
	
	function getWeiboTemplateByType($type) {
		$templates = $this->getWeiboTemplates();
		return $templates[$type];
	}
	
	function getOfficalAccount($bindType) {
		$siteBindService = $this->_getSiteBindService();
		$bindConfig = $siteBindService->getBindType($bindType);
		if (!$bindConfig) return null;
		
		$account = $this->_getBindInfo('officalAccount_' . $bindType);
		if (!$account) return null;
		
		$account['url'] = $bindConfig['uidUrlPrefix'] ? $bindConfig['uidUrlPrefix'] . $account['id'] : '';
		return $account;
	}
	
	function getOfficalAccounts() {
		$accounts = array();
		$siteBindService = $this->_getSiteBindService();
		foreach ($siteBindService->getBindTypes() as $bindType => $bindConfig) {
			$account = $this->getOfficalAccount($bindType);
			if ($account) $accounts[$bindType] = $account;
		}
		return $accounts;
	}
	
	function save($options) {
		return $this->_saveBindInfo($options);
	}
	

	
	
	function _getDefaultWeiboTemplates($type = null) {
		$sets = array(
			'article' => '[{title}] {content} {url}',
			'photos' => '我刚上传了{photo_count}张照片, 快来看看{url}',
			'group_photos' => '我刚上传了{photo_count}张照片, 快来看看{url}',
		);
		$sets['diary'] = $sets['group_active'] = $sets['cms'] = $sets['article'];
		
		return $type ? $sets[$type] : $sets;
	}
	
	
	
	function _getBindInfo($key = null) {
		global ${$this->_config_key};
		$bindInfo = ${$this->_config_key};
		
		return $key ? $bindInfo[$key] : $bindInfo;
	}
	
	function _saveBindInfo($options) {
		require_once (R_P . 'admin/cache.php');
		
		global ${$this->_config_key};
		$bindInfo = ${$this->_config_key} ? ${$this->_config_key} : array();
		
		foreach ($options as $key => $value) {
			$bindInfo[$key] = $value;
		}
		
		${$this->_config_key} = $bindInfo;
		
		setConfig($this->_config_key, $bindInfo);
		updatecache_c();
		return true;
	}
	
	/**
	 * @return PW_WeiboSiteBindService
	 */
	function _getSiteBindService() {
		return L::loadClass('WeiboSiteBindService', 'sns/weibotoplatform/service');
	}
}
