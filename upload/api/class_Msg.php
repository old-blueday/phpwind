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
				'title' => Char_cv(stripslashes($subject)),
				'content' => Char_cv(stripslashes($content))
			),'notice_apps', 'notice_apps'
		);

		return new ApiResponse(true);
	}

	function SendAppmsg ($toname, $fromname, $subject, $content) {
		$userService = $this->_getUserService();
		
		M::sendNotice(
			array($toname),
			array(
				'title' => Char_cv(stripslashes($subject)),
				'content' => Char_cv(stripslashes($content))
			),'notice_apps', 'notice_apps'
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