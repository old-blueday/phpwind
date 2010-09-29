<?php
!defined('P_W') && exit('Forbidden');

class PW_PageCacheConfigList {

	function getConfig() {
		return $this->_pageConfig();
	}

	function _pageConfig() {
		$cache_config = array(
			'hotArticle' => array('type' => 'article', 'sorttype' => 'hotday', 'cachetime' => 1800, 'num' => '10'), 
			'newArticle' => array('type' => 'article', 'sorttype' => 'new', 'cachetime' => 400, 'num' => '15'));
		return $cache_config;
	}
}
?>