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
		$rt = $this->db->get_one("SELECT * FROM pw_userapp WHERE uid=".pwEscape($uid)." AND appid=".pwEscape($appid));
		if ($rt['allowfeed']) {
			$descrip = Char_cv($descrip);
			$this->db->update("INSERT INTO pw_feed SET " . pwSqlSingle(array(
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
		$rt = $this->db->get_one("SELECT uid FROM pw_userapp WHERE uid=".pwEscape($uid)." AND appid=".pwEscape($appid));
		if ($rt && $appinfo) {
			$appinfo = serialize($appinfo);
			$this->db->update("UPDATE pw_userapp SET appinfo=" .pwEscape($appinfo). "WHERE uid=".pwEscape($uid)." AND appid=".pwEscape($appid));
			return new ApiResponse(true);
		}
		return new ApiResponse(false);
	}

	function insertAppevent($uid,$appevent = array(),$appid) {//插入用户的单个应用信息
		$rt = $this->db->get_one("SELECT uid FROM pw_userapp WHERE uid=".pwEscape($uid)." AND appid=".pwEscape($appid));
		if ($rt && $appevent) {
			$appevent = serialize($appevent);
			$this->db->update("UPDATE pw_userapp SET appevent=" .pwEscape($appevent). "WHERE uid=".pwEscape($uid)." AND appid=".pwEscape($appid));
			return new ApiResponse(true);
		}
		return new ApiResponse(false);
	}
}
?>