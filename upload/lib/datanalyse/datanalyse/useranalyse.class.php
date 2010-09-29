<?php
!defined('P_W') && exit('Forbidden');
include_once (R_P . 'lib/datanalyse/datanalyse.base.php');

/**
 * 用户排行
 * @author yishuo
 */
class PW_Useranalyse extends PW_Datanalyse {
	var $pk = 'tid';
	/* 用户在线排行，分享排行，积分排行，好友排行 */
	var $actions = array('memberOnLine', 'memberThread', 'memberShare', 'memberCredit', 'memberFriend');

	function PW_Useranalyse() {
		$this->__construct();
	}

	function __construct() {
		parent::__construct();
	}

	/**
	 * 根据日志ID数组获得日志信息
	 * @return array
	 */
	function _getDataByTags() {
		if (empty($this->tags)) return array();
		
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		return $userService->getUsersWithMemberDataByUserIds($this->tags);
	}
}
?>