<?php

!defined('P_W') && exit('Forbidden');
//api mode 5

class Feed {
	
	var $base;
	var $db;

	function Feed($base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	function publishTemplatizedAction($uid, $descrip, $appid) {//插入动态信息
		global $timestamp;
		//$rt = $this->db->get_one("SELECT * FROM pw_userapp WHERE uid=".S::sqlEscape($uid)." AND appid=".S::sqlEscape($appid));
		$appclient = L::loadClass('appclient');
		$rt = $appclient->getUserAppByUidAndAppid($uid,$appid);
		if ($rt['allowfeed']) {
			$descrip = S::escapeChar($descrip);
			$this->db->update("INSERT INTO pw_feed SET " . S::sqlSingle(array(
				'uid'		=> $uid,
				'type'		=> 'app',
				'descrip'	=> $descrip,
				'timestamp'	=> $timestamp
			),false));
			return new ApiResponse(true);
		}
		return new ApiResponse(false);
	}

	function insertAppinfo($uid,$appinfo = '',$appid) {//插入用户的单个应用的属性
		//$rt = $this->db->get_one("SELECT uid FROM pw_userapp WHERE uid=".S::sqlEscape($uid)." AND appid=".S::sqlEscape($appid));
		$appclient = L::loadClass('appclient');
		$rt = $appclient->getUserAppByUidAndAppid($uid,$appid);
		if ($rt && $appinfo) {
			$appinfo = serialize($appinfo);
			//$this->db->update("UPDATE pw_userapp SET appinfo=" .S::sqlEscape($appinfo). "WHERE uid=".S::sqlEscape($uid)." AND appid=".S::sqlEscape($appid));
			pwQuery::update('pw_userapp', 'uid=:uid AND appid=:appid', array($uid,$appid), array('appinfo'=>$appinfo));
			return new ApiResponse(true);
		}
		return new ApiResponse(false);
	}

	function insertAppevent($uid,$appevent = array(),$appid) {//插入用户的单个应用信息
		//$rt = $this->db->get_one("SELECT uid FROM pw_userapp WHERE uid=".S::sqlEscape($uid)." AND appid=".S::sqlEscape($appid));
		$appclient = L::loadClass('appclient');
		$rt = $appclient->getUserAppByUidAndAppid($uid,$appid);
		if ($rt && $appevent) {
			$appevent = serialize($appevent);
			//$this->db->update("UPDATE pw_userapp SET appevent=" .S::sqlEscape($appevent). "WHERE uid=".S::sqlEscape($uid)." AND appid=".S::sqlEscape($appid));
			pwQuery::update('pw_userapp', 'uid=:uid AND appid=:appid', array($uid,$appid), array('appevent'=>$appevent));
			return new ApiResponse(true);
		}
		return new ApiResponse(false);
	}
}
?>