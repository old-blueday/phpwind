<?php

!defined('P_W') && exit('Forbidden');
//api mode 4

class Friend {
	
	var $base;
	var $db;

	function Friend($base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	function get($uid, $num, $start = 0) {
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
		while ($rt = $this->db->fetch_array($query)) {
			$users[] = $rt['friendid'];
		}
		return new ApiResponse($users);
	}

	function getList() {

	}

	function getAppUsers($appid, $uid, $num, $start = 0) {

      if ($num == 'all') {
         $num = 500;
      } elseif (!is_numeric($num) || $num < 1) {
         $num = 20;
      } elseif ($num > 500) {
         $num = 500;
      }
      (!is_numeric($start) || $start < 0) && $start = 0;

      $users =  $appusers = array();
      $query = $this->db->query("SELECT friendid FROM pw_friends WHERE status='0' AND uid=" . S::sqlEscape($uid) . S::sqlLimit($start, $num));
      while ($rt = $this->db->fetch_array($query)) {
         $users[] = $rt['friendid'];
      }

	  $query = $this->db->query("SELECT uid FROM pw_userapp WHERE uid IN (".S::sqlImplode($users).") AND appid=".S::sqlEscape($appid));
      while ($rt = $this->db->fetch_array($query)) {
         $appusers[] = $rt['uid'];
      }
 
      return new ApiResponse($appusers);
   }
}
?>