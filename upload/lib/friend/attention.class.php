<?php
!defined('P_W') && exit('Forbidden');

/**
 * 关注服务层
 * 
 * @package  PW_Attention
 * @author   lmq
 * @abstract  
 */
class PW_Attention {
	
	var $_timestamp = 0;
	
	function PW_Attention() {
		global $timestamp;
		$this->_timestamp = $timestamp;
	}
	
	/**
	 * 用户添加关注，同时推送条新鲜事
	 * 
	 * @param $uid	用户
	 * @param $friendid 关注的对象
	 * @param $limit	新鲜事数量
	 */
	function addFollow($uid, $friendid, $limit = 20) {//fixed
		if (!$uid || !$friendid) return false;
		if ($this->isFollow($uid, $friendid)) return 'user_attention_exists';
	
		$attentionDB = $this->_getAttentionDB();
		$attentionDB->insert(array(
			'uid' => $uid,
			'friendid' => $friendid,
			'joindate' => $this->_timestamp
		));
	
		$userServer = L::loadClass('UserService', 'user');
		$userServer->updateByIncrement($uid, array(), array('follows' => 1));
		$userServer->updateByIncrement($friendid, array(), array('fans' => 1, 'newfans' => 1));

		$this->addUserWeiboRelationsByFriendid($friendid, $uid, $limit);
		return true;
	}

	/**
	 * 添加关注时,增加这个人的最新新鲜事（20条）
	 * 
	 * @param $friendid
	 * @param $uid
	 * @param $limit
	 */
	function addUserWeiboRelationsByFriendid($friendid, $uid, $limit = 20) {
		if (!$uid || !$friendid) return false;
		$weiboService = $this->_serviceFactory('Weibo', 'sns'); /* @var $weiboService PW_Weibo */
		return $weiboService->pushData($uid, $friendid, $limit);
	}

	/**
	 * 解除关注
	 * 
	 * 情况1:如果不是好友,就可以删记录
	 * 情况2:如果是好友,更新attention为0 代表不关注
	 * @param int $uid
	 * @param int $friendid
	 */
	function delFollow($uid, $friendid) {//fixed
		if (!$uid || !$friendid) return false;
		if (!$this->isFollow($uid, $friendid)) return 'user_not_exists';

		$attentionDB = $this->_getAttentionDB();
		$attentionDB->delByUidAndFriendid($uid, $friendid);

		$userServer = L::loadClass('UserService', 'user');
		$userServer->updateByIncrement($uid, array(), array('follows' => -1));
		$userServer->updateByIncrement($friendid, array(), array('fans' => -1));
		
		$this->delUserWeiboRelationsByFriendid($uid, $friendid);
		
		return true;
	}
	
	/**
	 * 解除对莫个人关注，同时删掉关注者的新鲜事
	 * 
	 * @param int $uid
	 * @param int $friendid
	 */
	function delUserWeiboRelationsByFriendid($uid, $friendid) {
		if (!$uid || !$friendid) return false;
		$weiboService = $this->_serviceFactory('Weibo', 'sns'); /* @var $weiboService PW_Weibo */
		return $weiboService->removeRelation($uid,$friendid);
	}
	
	/**
	 * 判断是否关注
	 * 
	 * @param int $uid  
	 * @param int $friendid
	 * @return bool
	 */
	function isFollow($uid, $friendid) {//fixed
		if (!$uid || !$friendid) return false;
		$user = $this->getUserByUidAndFriendid($uid, $friendid);
		return !empty($user);
	}

	/**
	 * 获取我关注的人/list
	 * 
	 * @param int $uid
	 */
	function getFollowList($uid, $page = 1, $perpage = 20) {//fixed
		if (!$uid) return false;
		$perpage = (int)$perpage;
		$offset = ($page -1 ) * $perpage;
		$attentionDB = $this->_getAttentionDB();
		return $attentionDB->getFollowList($uid, $offset, $perpage);
	}
	
	/**
	 * 获取关注我的人/list
	 * 
	 * @param int $uid
	 */
	function getFansList($uid) {//fixed
		if (!$uid) return false;
		$attentionDB = $this->_getAttentionDB();
		return $attentionDB->getFansList($uid);
	}
	
	/**
	 * 获取我关注的人UIDS/array(0=>uid1,1=>uid2,...n=>uidn);
	 * 
	 * @param int $uid
	 */
	function getUidsInFollowList($uid, $page = 1, $perpage = 500) {//fixed
		if (!$uid) return false;
		$users = $attention = array();
		$users = $this->getFollowList($uid, $page, $perpage);
		if (!$users) return array();
		foreach ($users as $user) {
			$attention[] = $user['friendid'];
		}
		return $attention;
	}
	
	/**
	 * 根据用户，指定关系对象，获得关注信息。
	 * 
	 * @param int $uid
	 * @param int $friendids
	 */
	function getFollowListByFriendids($uid,$friendids = array()) {//fixed
		if (!$uid) return false;
		$attentionDB = $this->_getAttentionDB();
		return $attentionDB->getFollowListByFriendids($uid, $friendids);
	}
	
	/**
	 * 根据用户，指定关系对象，获得关注uids。
	 * 
	 * @param int $uid
	 * @param int $friendids
	 */
	function getUidsInFollowListByFriendids($uid,$friendids = array()) {//fixed
		if (!$uid) return false;
		$attentionInfo = $attentionUids = array();
		$attentionInfo = $this->getFollowListByFriendids($uid, $friendids);
		foreach ($attentionInfo as $attention) {
			$attentionUids[] = $attention['friendid'];
		}
		return $attentionUids;
	}
	
	function getUidsInFansListByFriendids($uid,$friendids = array()) {//fixed
		if (!$uid) return false;
		$attentionInfo = $attentionUids = array();
		$attentionDB = $this->_getAttentionDB();
		$attentionInfo = $attentionDB->getUidsInFansListByFriendids($uid, $friendids);
		foreach ($attentionInfo as $attention) {
			$attentionUids[] = $attention['uid'];
		}
		return $attentionUids;
	}
	
	/**
	 * 根据用户id和好友id,找出相关信息
	 * 
	 * @param int $uid
	 * @param int $friendid
	 */
	function getUserByUidAndFriendid($uid, $friendid) {//fixed
		if (!$uid || !$friendid) return false;
		$attentionDB = $this->_getAttentionDB();
		return $attentionDB->getUserByUidAndFriendid($uid, $friendid);
	}
		
	function getFollowListInPage($uid, $page = 1, $perpage = 20) {//fixed
		if (!$uid) return false;
		$perpage = (int)$perpage;
		$offset = ($page -1 ) * $perpage;
		$attentionDB = $this->_getAttentionDB();
		$attention = $temp = array();
		$temp = $attentionDB->findAttentions($uid, $offset, $perpage);
		return $this->_formatAttentionsData($temp);
	}
	
	function getFansListInPage($uid, $page = 1, $perpage = 20) {//fixed
		if (!$uid) return false;
		$perpage = (int)$perpage;
		$offset = ($page -1 ) * $perpage;
		$attentionDB = $this->_getAttentionDB();
		$attention = $temp = array();
		$temp =  $attentionDB->findFans($uid, $offset, $perpage);
		return $this->_formatAttentionsData($temp);
	}
	
	function _formatAttentionsData($temp) {//fixed
		if(!$temp || !is_array($temp)) return false;
		require_once(R_P.'require/showimg.php');
		$result = array();
		foreach ($temp as $value) {
			list($value['face']) = showfacedesign($value['face'], '1', 's');
			$value['honor'] = substrs($value['honor'],90);
			$value['lastvisit']	= get_date($value['lastvisit']);
			$result[$value['uid']] = $value;
		}
		return $result;
	}
	
	/**
	 * 获得我关注人数/count
	 * 
	 * @param int $uid
	 */
	function countFollows($uid) {//fixed
		if (!$uid) return false;
		$attentionDB = $this->_getAttentionDB();
		return $attentionDB->countFollows($uid);
	}
	
	/**
	 * 获得被关注人数/fans 人数
	 * 
	 * @param int $uid
	 */
	function countFans($uid) {//fixed
		if (!$uid) return false;
		$attentionDB = $this->_getAttentionDB();
		return $attentionDB->countFans($uid);
	}
	
	/**
	 * 用户 touid 是否被用户 uid 屏蔽
	 * @param int $uid 用户id
	 * @param array $uIds 被屏蔽的用户id
	 * return bool
	 */
	function isInBlackList($uid, $touid) {
		if (!$uid || !$touid) return false;
		$attentionBlackListDB = $this->_getAttentionBlackListDB();
		return $attentionBlackListDB->isInBlackList($uid, $touid);
	}

	/**
	 * 获得屏蔽某用户的人的列表
	 * @param int $uid 被屏蔽的用户id
	 * @param array $uIds 用户列表
	 * return array
	 */
	function getBlackListToMe($uid, $uIds = array()) {
		if (!$uid) return false;
		$attentionBlackListDB = $this->_getAttentionBlackListDB();
		$blackList = $attentionBlackListDB->getBlackListToMe($uid, $uIds);
		$array = array();
		if ($blackList) {
			foreach ($blackList as $key => $value) {
				$array[] = $value['uid'];
			}
		}
		return $array;
	}
	
	/**
	 * 获得某用户的屏蔽列表
	 * @param int $uid
	 * return array
	 */
	function getBlackList($uid) {
		if (!$uid) return false;
		$attentionBlackListDB = $this->_getAttentionBlackListDB();
		$blackList = $attentionBlackListDB->getBlackList($uid);
		$array = array();
		if ($blackList) {
			foreach ($blackList as $key => $value) {
				$array[] = $value['touid'];
			}
		}
		return $array;
	}

	function getNamesOfBlackList($uid) {
		if (!$uid) return false;
		if (!$blackList = $this->getBlackList($uid)) {
			return array();
		}
		$userService = L::loadClass('UserService', 'user');
		return $userService->getUserNamesByUserIds($blackList);
	}

	function setBlackList($uid, $newBlackList = array()) {
		if (!$uid) return false;
		$blackList = $this->getBlackList($uid);
		$attentionBlackListDB = $this->_getAttentionBlackListDB();
		if ($add = array_diff($newBlackList, $blackList)) {
			$attentionBlackListDB->add($uid, $add);
			foreach ($add as $val) {
				$this->delFollow($val, $uid);
			}
		}
		if ($del = array_diff($blackList, $newBlackList)) {
			$attentionBlackListDB->del($uid, $del);
		}
		return true;
	}
	
	/**
	 * 获得新增粉丝用户 top10
	 * return array
	 */
	function getTopFansUsers($num){
		$num = intval($num);
		if($num < 0) return array();
		global $timestamp;
		extract (pwCache::getData(D_P.'data/bbscache/o_config.php',false));
		$time = $this->_timestamp - ($o_weibo_hotfansdays ? intval($o_weibo_hotfansdays) * 86400 : 86400);
		$attentionDB = $this->_getAttentionDB();
		$topUserIds = $attentionDB->getTopFansUser($time,$num);
		$tagsService = L::loadClass('memberTagsService', 'user');
		$tagsData = $tagsService->getTagsByUidsForSource($topUserIds);
		$tags = array();
		foreach($tagsData as $v){
			$tags[$v['userid']][] = $v['tagname'];
		}
		$userService = L::loadClass('UserService','user');
		require_once(R_P . 'require/showimg.php');
		$userData = $userService->getByUserIds($topUserIds);
		$newUsersInfo = array();
		$data = array();
		foreach ($topUserIds as $uid){
			if(!$userData[$uid]) continue;
			$data[] = $userData[$uid];
		}
		
		foreach ($data as $key => $value) {
			list($value['icon']) = showfacedesign($value['icon'], 1, 's');
			$value['tags'] = S::isArray($tags[$value['uid']]) ? implode(' ', $tags[$value['uid']]) : $tags[$value['uid']];
			$newUsersInfo[$key] = $value;
		}
		return $newUsersInfo;
	}
	
	/**
	 * Get PW_FriendDB
	 * 
	 * @access protected
	 * @return PW_FriendDB
	 */
	function _getAttentionDB() {
		return L::loadDB('Attention', 'friend');
	}

	function _getAttentionBlackListDB() {
		return L::loadDB('attention_blacklist', 'friend');
	}
	
	/**
	 * 私有加载记录服务入口
	 * @param PW_$name
	 * @return PW_$name
	 */
	function _serviceFactory($name, $dir='') {
		$name = strtolower($name);
		return L::loadClass($name, $dir);
	}
}