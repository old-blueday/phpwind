<?php
!defined('P_W') && exit('Forbidden');

class PW_PageCache {
	var $config;
	var $cache;
	var $signs = array();
	var $updateCache = array();

	function init($pageCacheConfig) {
		$this->signs = array();
		$this->updateCache = array();
		
		$this->config = $pageCacheConfig->getConfig();
		foreach ($this->config as $key => $value) {
			$this->signs[$key] = $this->_getSign($key);
		}
		$pagecache = $this->_getPageCacheService();
		$this->cache = $pagecache->getDataBySigns($this->signs);
	}

	function getData($key) {
		$temp = $this->_getDataFromCache($key);
		
		if ($temp === false) {
			$temp = $this->_getDataFromReality($key);
		}
		!$temp && $temp = array();
		return $temp;
	}

	function getSigns() {
		return $this->signs;
	}

	function deleteCache($key) {
		$sign = $this->_getSign($key);
		$pagecache = $this->_getPageCacheService();
		$pagecache->deleteCache($sign);
	}

	function deleteThisCache() {
		foreach ($this->config as $key => $value) {
			$this->deleteCache($key);
		}
	}

	function _getDataFromReality($type) {
		if (!isset($this->config[$type])) return false;
		$typeConfig = $this->config[$type];
		$typeConfig = $this->_cookConfigForDataSource($typeConfig);
		$dataSourceService = $this->_getDataSourceService();
		$temp = $dataSourceService->getSourceData($typeConfig);
		$this->_filterData($temp);
		$this->_updateCache($type, $temp);
		return $temp;
	}

	function _filterData(&$data) {
		foreach ($data as $key => $value) {
			if (empty($value['title']) || empty($value['url'])) unset($data[$key]);
		}
	}

	function _cookConfigForDataSource($conifg) {
		$result = array();
		foreach ($conifg as $key => $value) {
			if ($key == 'type') {
				$result['action'] = $value;
			} elseif ($key == 'num') {
				$result['num'] = $value;
			} elseif ($key == 'cachetime') {
				continue;
			} else {
				$result['config'][$key] = $value;
			}
		}
		return $result;
	}

	/**
	 * @return PW_DataSourceService
	 */
	function _getDataSourceService() {
		return L::loadClass('datasourceservice', 'area');
	}

	function _updateCache($type, $data) {
		global $timestamp;
		
		$cachetime = $this->config[$type]['cachetime'] ? $this->config[$type]['cachetime'] + $timestamp : 0;
		$temp = array('sign' => $this->_getSign($type), 'type' => $this->_getCacheType($type), 'data' => $data, 
			'cachetime' => $cachetime);
		$pagecache = $this->_getPageCacheService();
		$pagecache->relpace($temp);
	}

	function _getsourceTypeImp($type) {
		return L::loadClass('typedata' . $type, 'model');
	}

	function _getDataFromCache($type) {
		global $timestamp;
		$key = $this->_getSign($type);
		if (isset($this->cache[$key]) && ($this->cache[$key]['cachetime'] == 0 || $this->cache[$key]['cachetime'] > $timestamp)) {return $this->cache[$key]['data'];}
		return false;
	}

	function _getSign($type) {
		if (!isset($this->config[$type])) Showmsg('this config is not defined');
		$temp = $this->config[$type];
		unset($temp['cachetime']);
		return md5(serialize($temp));
	}

	function _getCacheType($type) {
		if (!isset($this->config[$type])) Showmsg('this config is not defined');
		return $this->config[$type]['type'];
	}

	function _getPageCacheService() {
		return L::loadClass('pagecacheservice', 'pagecache');
	}
}
?>