<?php

!defined('P_W') && exit('Forbidden');
//api mode 10

class Cache {

	var $base;
	var $db;

	function Cache($base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	function updatesyncredit($syncredit) {
		
		require_once(R_P . 'admin/cache.php');
		setConfig('uc_syncredit', $syncredit);
		updatecache_c();

		return new ApiResponse(1);
	}
}
?>