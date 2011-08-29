<?php

!defined('P_W') && exit('Forbidden');
//api mode 7

class UserApp {
	
	var $base;
	var $db;

	function UserApp($base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	function isInstall($uid) {
		$appid = array();
		//$query = $this->db->query("SELECT appid FROM pw_userapp WHERE uid=" . S::sqlEscape($uid));
		$appclient = L::loadClass('appclient');
		$apps = $appclient->getUserAppsByUid($uid);
		foreach($apps as $v) {
			$appid[] = $v['appid'];
		}
		/*
		while ($rt = $this->db->fetch_array($query)) {
			$appid[] = $rt['appid'];
		}
		*/
		return new ApiResponse($appid);
	}

	function add($uid, $appid, $appname, $allowfeed ,$descrip) {
		global $timestamp;
		/*
		$this->db->update("REPLACE INTO pw_userapp SET " . S::sqlSingle(array(
			'uid'		=> $uid,
			'appid'		=> $appid,
			'appname'	=> $appname,
		)));
		*/
		pwQuery::replace(
			'pw_userapp',
			array(
				'uid'		=> $uid,
				'appid'		=> $appid,
				'appname'	=> $appname,
			)
		);
		
		if ($allowfeed) {
			$descrip = S::escapeChar($descrip);
			$this->db->update("INSERT INTO pw_feed SET " . S::sqlSingle(array(
				'uid'		=> $uid,
				'type'		=> 'app',
				'descrip'	=> $descrip,
				'timestamp'	=> $timestamp
			),false));
		}
		
		return new ApiResponse(true);
	}

	function appsUpdateCache($apps) {
		if ($apps && is_array($apps)) {

			require_once(R_P.'admin/cache.php');
			setConfig('db_apps_list',$apps);
			updatecache_c();
			return new ApiResponse(true);
		} else {
			return new ApiResponse(false);
		}
	}
}
?>