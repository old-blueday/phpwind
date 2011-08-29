<?php

!defined('P_W') && exit('Forbidden');
//api mode 9

class Site {
	
	var $base;
	var $db;

	function Site($base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	/**
	 * 插入站长中心id
	 * @param int $siteId 站长中心id
	 * @return bool
	 */
	function insertWebmasterKey($siteId) {
		if ($siteId <= 0) return new ApiResponse(false);

		require_once(R_P.'admin/cache.php');

		setConfig('db_siteappkey', $siteId);
		updatecache_c();

		return new ApiResponse(true);
	}

	/**
	 * 获取站长中心id
	 * @return string
	 */
	function getWebmasterKey() {
		
		global $db_siteappkey;

		if (empty($db_siteappkey)) return new ApiResponse(false);

		return new ApiResponse($db_siteappkey);
	}

	function connect() {
		return new ApiResponse(1);
	}
}
?>