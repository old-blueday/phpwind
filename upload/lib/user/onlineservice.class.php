<?php
!defined('P_W') && exit('Forbidden');
class PW_OnlineService{
	
	var $page_index = array('index' => 1, 'thread' => 2, 'read' => 3, 'cate' => 4, 'mode' => 5, 'other' => 6);
	var $db;	
	// 对于同一个ip的新游客，当给其分配新的token时，需要将lastvisit距离当前timestamp在$tokenTime秒内的游客删除
	var $tokenTime = 60;
	
	function PW_Online(){
		$this->db = $GLOBALS['db'];
	}
	
	function __construct(){
		$this->PW_Online();
	}
	
	/**
	 * 更新在线的登录用户信息
	 *
	 * @return boolean
	 */
	function updateOnlineUser() { 
		global $fid, $tid, $timestamp, $winduid, $windid, $onlineip, $groupid, $wind_in, $db_onlinetime, $db_ipstates, $db_today, $lastvisit, $tdtime;
		if ($winduid < 1) return false;
		
		$ifhide = $GLOBALS['_G']['allowhide'] && GetCookie('hideid') ? 1 : 0;
		$pwSQL = S::sqlSingle(array('uid' => $winduid, 
									'ip' => $this->_ip2long($onlineip), 
									'groupid' => $groupid, 
									'username' => $windid, 
									'lastvisit' => $timestamp, 
									'fid' => $fid, 
									'tid' => $tid, 
									'action' => $this->page_index[$wind_in], 
									'ifhide' => $ifhide));
		// 间隔一段时间删除过期用户，避免频繁删除导致性能下降
		if ($timestamp % 20 == 0){
			$this->db->update('DELETE FROM pw_online_user WHERE lastvisit<' . S::sqlEscape($timestamp - $db_onlinetime));
		}
		return $this->db->update('REPLACE INTO pw_online_user SET ' . $pwSQL);
	}
	
	/**
	 * 更新在线的游客信息
	 *
	 * @return boolean
	 */
	function updateOnlineGuest(){
		global $fid, $tid, $timestamp, $onlineip,$db_onlinetime,$wind_in;
		if (!($guestInfo = $this->getGuestInfo())){
			return false;
		}

		$ifhide = $GLOBALS['_G']['allowhide'] && GetCookie('hideid') ? 1 : 0;
		if ($guestInfo['token'] == 0){
			// 删除过期的游客或者同IP在60秒内更新过的游客（防止恶意刷人数的行为）
			$this->db->update('DELETE FROM pw_online_guest WHERE lastvisit<' . S::sqlEscape($timestamp - $db_onlinetime) . 
				' OR (ip = ' . S::sqlEscape($guestInfo['ip']) . ' AND  lastvisit>' . S::sqlEscape($timestamp - $this->tokenTime) . ')');
			$token = rand(1,255);
			$this->setGuestToken($token);
		} else {
			// 间隔一段时间删除过期用户，避免频繁删除导致性能下降
			if ($timestamp % 20 == 0){
				$this->db->update('DELETE FROM pw_online_guest WHERE lastvisit<' . S::sqlEscape($timestamp - $db_onlinetime));
			}
			$token = $guestInfo['token'];
		}

		$pwSQL = S::sqlSingle(array('ip' => $guestInfo['ip'], 
									'token' => $token,
									'lastvisit' => $timestamp, 
									'fid' => $fid, 
									'tid' => $tid, 
									'action' => $this->page_index[$wind_in], 
									'ifhide' => $ifhide));
		$this->db->update("REPLACE INTO pw_online_guest SET " . $pwSQL);
	}
	
	/**
	 * 当用户登录时调用此接口，删除其在“在线游客”表的记录
	 *
	 * @return boolean
	 */
	function deleteOnlineGuest($guestInfo = null){
		if (!$guestInfo  && !($guestInfo = $this->getGuestInfo())){
			return false;
		}
		return $this->db->update('DELETE FROM pw_online_guest WHERE ip=' . S::sqlEscape($guestInfo['ip']) . ' AND token = ' .  S::sqlEscape($guestInfo['token']));
	}
	
	/**
	 * 当用户退出是调用此接口，删除其在“在线用户”表的记录
	 *
	 * @return boolean
	 */
	function deleteOnlineUser($userId){
		if (($userId = intval($userId)) < 1) return false;
		return $this->db->update("DELETE FROM pw_online_user WHERE uid=" . S::sqlEscape($userId));
	}
	
	/**
	 * 获取所有在线的登录用户列表, 专为sort.php统计在线人数使用
	 *
	 * @param integer $start 页码
	 * @param integer $perpage 每页数目
	 * @param integer &$number 回传参数，所有在线人数
	 * @return array
	 */
	function getAllOnlineWithPaging($start, $perpage, &$number){
		$online_user_num = $this->countOnlineUser();
		$online_guest_num = $this->countOnlineGuest();
		if ($start * $perpage <= $online_user_num){
			$all = $this->getOnlineUser($start, $perpage);
		}else if (($start-1) * $perpage + 1> $online_user_num){
			$all = $this->getOnlineGuest($start, $perpage);
		}else{
			$all = array_merge($this->getOnlineUser($start, $perpage), $this->getOnlineGuest(1, $perpage));
		}
		$number = $online_user_num + $online_guest_num;
		return $all;
	}
	
	/**
	 * 获取所有在线用户列表，包括登录用户和游客, 不提供分页
	 *
	 * @return array
	 */
	function getAllOnline(){
		return array_merge((array)$this->getOnlineUser(), (array)$this->getOnlineGuest());
	}		
		
	/**
	 * 获取所有在线用户，支持分页，若不给$start和$perpage参数则获取全部用户
	 *
	 * @param int $start 
	 * @param int $perpage
	 * @return array
	 */
	function getOnlineUser($start = 0, $perpage = 20){
		$limit = $start < 1 ? '' : S::sqlLimit(($start - 1) * $perpage, $perpage);
		$query = $this->db->query('SELECT * FROM pw_online_user ' . $limit);
		$page_reverse_index = array_flip($this->page_index);
		$users = array();
		while ($rt = $this->db->fetch_array($query)){
			$rt['ip'] = long2ip($rt['ip']);
			$rt['action'] = $page_reverse_index[$rt['action']];
			$users[] = $rt;
		}
		return $users;
	}
	
	/**
	 * 获取所有在线游客，支持分页，若不给$start和$perpage参数则获取全部用户
	 *
	 * @param int $start 
	 * @param int $perpage
	 * @return array
	 */
	function getOnlineGuest($start = 0, $perpage = 20){
		$limit = $start < 1 ? '' : S::sqlLimit(($start - 1) * $perpage, $perpage);
		$query = $this->db->query('SELECT * FROM pw_online_guest ' . $limit);
		$page_reverse_index = array_flip($this->page_index);
		$guests = array();
		while ($rt = $this->db->fetch_array($query)){
			$rt['ip'] = long2ip($rt['ip']);
			$rt['action'] = $page_reverse_index[$rt['action']];
			$guests[] = $rt;
		}
		return $guests;
	}
	
	/**
	 * 获取所有在线用户的用户名，以uid作为key
	 *
	 * @return array
	 */
	function getOnlineUserName(){
		$query = $this->db->query('SELECT uid, username FROM pw_online_user');
		$users = array();
		while ($rt = $this->db->fetch_array($query)){
			$users[$rt['uid']] = $rt['username'];
		}
		return $users;		
	}
	
	/**
	 * 获取某一版块的在线用户
	 *
	 * @param integer $forumId
	 * @return array
	 */
	function getOnlineUserByForumId($forumId){
		if (($forumId = intval($forumId)) < 1) return false;
		$query = $this->db->query('SELECT * FROM pw_online_user WHERE fid=' . S::sqlEscape($forumId));
		$onlineUsers = array();
		while($rt = $this->db->fetch_array($query)){
			$onlineUsers[] = $rt;
		}
		return $onlineUsers;
	}
	
	/**
	 * 根据userid从pw_online_user表获取一条记录
	 *
	 * @param integer $userId
	 * @return array
	 */
	function getOnlineUserByUserId($userId){
		return $this->db->get_one('SELECT * FROM pw_online_user WHERE uid=' . S::sqlEscape($userId));
	}
	
	/**
	 * 统计在线的登录用户数目
	 *
	 * @return integer
	 */
	function countOnlineUser(){
		$rt = $this->db->get_one('SELECT COUNT(*) AS sum FROM pw_online_user');
		return $rt ? $rt['sum'] : $rt;
	}	
	
	/**
	 * 统计所有在线游客数目
	 *
	 * @return integer
	 */
	function countOnlineGuest(){
		$rt = $this->db->get_one("SELECT COUNT(*) AS sum FROM pw_online_guest");
		return $rt ? $rt['sum'] : $rt;		
	}
	
	/**
	 * 统计所有在线用户，包括登录用户和游客
	 *
	 * @return integer
	 */
	function countAllOnline(){
		return (int)$this->countOnlineUser() + (int)$this->countOnlineGuest();
	}
	
	/**
	 * 统计指定ip的在线人数
	 *
	 * @param integer $ip
	 * @return integer
	 */
	function countOnlineGuestByIp($ip){
		if (!$ip) return false;
		$rt = $this->db->get_one('SELECT COUNT(*) AS sum FROM pw_online_guest WHERE ip = ' . S::sqlEscape($ip) );
		return $rt ? $rt['sum'] : 0;
	}
	
	/**
	 * 写游客令牌到cookie
	 *
	 */
	function setGuestToken($token = 0){
		return $token ? Cookie('oltoken', StrCode($this->_ip2long($GLOBALS['onlineip']) . "\t" . $token)) : Cookie('oltoken', 'init');
	}

	/**
	 * 写当前在线会员数和在线游客数到cookie
	 *
	 */
	function setOnlineNumber(){
		return Cookie('online_info',  $GLOBALS['timestamp'] . "\t" .(int)$this->countOnlineUser() . "\t" . $this->countOnlineGuest());
	}
	
	/**
	 * 从cookie获取游客信息
	 * ipchange 表示ip是否改变了，针对adsl的用户
	 *
	 * @return array
	 */
	function getGuestInfo(){
		static $guestInfo = null;
		if (isset($guestInfo)) return $guestInfo;		
		list($ip, $token) = explode("\t", StrCode(GetCookie('oltoken'), 'DECODE'));
		$onlineip = $this->_ip2long($GLOBALS['onlineip']);
		if ($ip != $onlineip || $token > 254 || $token < 1) {
			$guestInfo = array('ip' => $onlineip, 'token' => 0);
			$guestInfo['ipchange'] = ($ip != $onlineip && $token > 0 && $token < 255) ? true : false;
		}else {
			$guestInfo = array('ip' => $onlineip, 'token' => $token , 'ipchange' => false);
		}
		return $guestInfo;
	}	
	
	/**
	 * 封装了ip2long函数，主要是ip地址可能是'unknown'
	 *
	 * @param string $ip
	 * @return int
	 */
	function _ip2long($ip){
		/**
		$ip = ip2long($ip);
		if ($ip === false || $ip == -1) $ip = ip2long('0.0.0.0');
		return $ip;
		**/
		list(, $ip) = unpack('l',pack('l',ip2long($ip)));
		return $ip;		
	}
}

