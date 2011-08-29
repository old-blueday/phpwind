<?php

!defined('P_W') && exit('Forbidden');
//api mode 8

class Invite {
	
	var $base;
	var $db;

	function Invite($base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	function get($appid, $uid, $num, $start = 0) {
		if ($num == 'all') {
			$num = 500;
		} elseif (!is_numeric($num) || $num < 1) {
			$num = 20;
		} elseif ($num > 500) {
			$num = 500;
		}
		(!is_numeric($start) || $start < 0) && $start = 0;

		$users = array();
		$query = $this->db->query("SELECT friendid FROM pw_friends WHERE status='0' AND uid=" . S::sqlEscape($uid) . S::sqlLimit($start, $num));
		$appclient = L::loadClass('appclient');
		while ($rt = $this->db->fetch_array($query)) {
			//$app = $this->db->get_one("SELECT * FROM pw_userapp WHERE uid=".S::sqlEscape($rt['friendid'])." AND appid=".S::sqlEscape($appid));
			$app = $appclient->getUserAppByUidAndAppid($rt['friendid'],$appid);
			if (empty($app)) {
				$users[] = $rt['friendid'];
			}
		}
		return new ApiResponse($users);
	}
}
?>