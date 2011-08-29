<?php
!function_exists('readover') && exit('Forbidden');
require_once R_P . 'require/showimg.php';

/**
 * 好友邀请服务
 * @author papa
 * 2010-04-21 
 */
class PW_Invite {
	var $_db;

	function __construct() {
		$this->PW_Invite();
	}

	function PW_Invite() {
		global $db;
		$this->_db = & $db;
	}

	/**
	 * 发送邀请邮件
	 * @param Array $emailList
	 * @return boolean
	 */
	function sendInviteEmail($emailList) {
		global $winddb;
		require (R_P . 'require/sendemail.php');
		$GLOBALS['fromemail'] = $winddb['email'];
		foreach ($emailList as $email) {
			$GLOBALS['sendtoname'] = $email;
			sendemail($email, 'email_mode_o_title', 'email_mode_o_content');
		}
		return false;
	}

	/**
	 * 发送邀请码邮件
	 * @param unknown_type $emailList
	 * @return string
	 */
	function sendInviteCode($emailList) {
		require (R_P . 'require/sendemail.php');
		foreach ($emailList as $email) {
			sendemail($email, 'email_invite_subject', 'email_invite_content_new');
		}
		return false;
	}

	/**
	 * 根据好友邀请类型获得联系人邮件列表
	 * @param string $type		邀请类型(162,163,gmail,gtalk,msn)
	 * @param string $username	用户名
	 * @param string $password	密码
	 * @return Array:
	 */
	function getEmailAddressListByType($type, $username = '', $password = '') {
		$invitation = $this->_inviteServiceFactory($type);
		return $invitation->getEmailAddressList($username, $password);
	}

	/**
	 * 根据联系人邮件列表获得好友列表
	 * @param Array $emailList
	 * @return Array:
	 */
	function getUsersFromEmailList($emailList) {
		$result = array();
		if (!empty($emailList) && is_array($emailList)) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			foreach ($userService->getByEmails($emailList) as $rt) {
				$rt['face'] = showfacedesign($rt['icon'], '1', 's');
				$rt['face'] = $rt['face'][0];
				$result[$rt['uid']] = $rt;
			}
		}
		return $result;
	}

	/**
	 * @param unknown_type $friends
	 */
	function getFriendsFromUserList($userId, $users) {
		$userId = intval($userId);
		if($userId < 1 && !is_array($users)){
			return array();
		}
		$result = array();
		if (!empty($users) && is_array($users)) {
			$_sql = "SELECT f.uid FROM pw_friends f WHERE f.uid=" . S::sqlEscape($userId) . " AND f.friendid IN (" . S::sqlImplode(array_keys($users)) . ")";
			$_query = $this->_db->query($_sql);
			while ($rt = $this->_db->fetch_array($_query)) {
				$result[] = $rt;
			}
		}
		return $result;
	}

	/**
	 * 根据联系人邮件列表获得非好友列表
	 * @param int $userId
	 * @param Array $emailList
	 * @return Array:
	 */
	function getNotFriendsFromEmailList($userId, $emailList) {
		$result = array();
		return $result;
	}

	/**
	 * 获得好友邀请服务实例
	 * @param unknown_type $name
	 * @return NULL|Ambigous <>|unknown
	 */
	function _inviteServiceFactory($type) {
		static $classes = array();
		$type = strtolower($type);
		$filename = R_P . "u/lib/invite/" . $type . ".inv.php";
		if (!is_file($filename)) {
			return null;
		}
		$class = 'INV_' . ucfirst($type);
		if (isset($classes[$class])) {
			return $classes[$class];
		}
		include S::escapePath($filename);
		$classes[$class] = new $class();
		return $classes[$class];
	}
}
?>