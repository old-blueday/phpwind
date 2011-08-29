<?php

!defined('P_W') && exit('Forbidden');
//api mode 3

class Msg {
	
	var $base;
	var $db;

	function Msg($base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	function send($uids, $fromUid, $subject, $content) {
		$userService = $this->_getUserService();
		
		$uids = is_numeric($uids) ? array($uids) : explode(',',$uids);
		$userNames = $userService->getUserNamesByUserIds($uids);

		M::sendNotice(
			$userNames,
			array(
				'title' => S::escapeChar(stripslashes($subject)),
				'content' => S::escapeChar(stripslashes($content))
			),'notice_apps', 'notice_apps'
		);

		return new ApiResponse(true);
	}

	function SendAppmsg ($toname, $fromname, $subject, $content) {
		$userService = $this->_getUserService();
		
		M::sendNotice(
			array($toname),
			array(
				'title' => S::escapeChar(stripslashes($subject)),
				'content' => S::escapeChar(stripslashes($content))
			),'notice_apps', 'notice_apps'
		);

		return new ApiResponse(true);
	}
	
	/**
	 * 以某个用户的身份给另一个用户发送短消息
	 * @param int $userId 发送者uid
	 * @param string $receiver 接受者用户名
	 * @param string $subject 标题
	 * @param string $content 内容
	 * return bool
	 */
	function sendMessage ($userId, $receiver, $subject, $content) {
		global $winddb,$winduid,$windid,$groupid,$_G,$SYSTEM;
		$userService = $this->_getUserService();
		$winddb = $userService->get($userId, true, true);
		$winduid = $winddb['uid'];
		$groupid = $winddb['groupid'];
		$windid  = $winddb['username'];
		$groupid == '-1' && $groupid = $winddb['memberid'];
		if (file_exists(D_P."data/groupdb/group_$groupid.php")) {
			extract(pwCache::getData(S::escapePath(D_P."data/groupdb/group_$groupid.php", false)));
		} else {
			extract(pwCache::getData(D_P.'data/groupdb/group_1.php', false));
		}
		M::sendMessage(
			$userId,
			array($receiver),
			array(
				'create_uid' => $winduid,
				'create_username' => $windid,
				'title' => S::escapeChar(stripslashes($subject)),
				'content' => S::escapeChar(stripslashes($content)),
			)
		);
		return new ApiResponse(true);
	}
	
	/**
	 * @return PW_UserService
	 */
	function _getUserService() {
		return L::loadClass('UserService', 'user');
	}
}
?>