<?php
!defined('P_W') && exit('Forbidden');
/**
 * 用户好友服务层
 * @package  PW_Friend
 * @author   苏骞
 *
 */
class PW_Friend {

	var $_separate = ',';
	var $_db = null;
	var $_timestamp = 0;

	function PW_Friend($separate = '#%') {
		global $db,$timestamp;
		$this->_separate = $separate;
		$this->_db = $db;
		$this->_timestamp = $timestamp;
	}
	/**
	 * 获取指定用户所有好友
	 *
	 * @param int $uid 用户ID
	 * @return array 返回指定格式的好友列表
	 */
	function getFriendsByUid($uid) {
		$sql = 'SELECT a.friendid, b.username FROM pw_friends a INNER JOIN pw_members b ON a.friendid = b.uid WHERE status = 0 AND a.uid = ' .S::sqlEscape($uid);
		return $this->_getAllResultFromSql($sql);
	}

	function getFriends($uid) {
		return $this->arrayToString($this->getFriendsByUid($uid),'username');
	}
 	/**
	 * 根据好友组来获取指定用户好友列表
	 *
	 * @param int $uid      用户ID
	 * @param int $colonyid 好友组ID
	 * @return array 返回指定格式的好友列表
	 */
	function getFriendsByColonyId($uid,$colonyid) {
		$sql= 'SELECT username FROM pw_friends a INNER JOIN `pw_members` b ON a.friendid = b.uid WHERE status = 0 AND a.uid = ' .S::sqlEscape($uid). ' AND ftid = ' .S::sqlEscape($colonyid);
		return $this->_getAllResultFromSql($sql);
	}
	 /**
	 * 根据好友组来获取指定用户好友列表
	 *
	 * @param int $uid      用户ID
	 * @param int $name 好友组
	 * @return array 返回指定格式的好友列表
	 */
	function getFriendsByColonyName($uid,$name) {
		$result = $this->_db->get_one('select ftid from pw_friendtype where name=' . S::sqlEscape($name));
		$sql = 'SELECT username FROM pw_friends a INNER JOIN `pw_members` b ON a.friendid = b.uid WHERE status = 0 AND a.uid = ' .S::sqlEscape($uid). ' AND ftid = ' . S::sqlEscape($result['ftid']);
		return $this->_getAllResultFromSql($sql);
	}

	function getFriendsByColony($uid,$colonyid,$type='id') {
		if($type == 'id'){
		 	return $this->arrayToString($this->getFriendsByColonyId($uid,$colonyid),'username');
		}
		elseif($type == 'name'){
			return $this->arrayToString($this->getFriendsByColonyName($uid,$colonyid),'username');
		}
	}
 	/**
	 * 获取指定用户好友组
	 *
	 * @param int $uid 用户ID
	 * @return array 返回指定格式的好友列表
	 */
	function getFriendColonysByUid($uid) {
		$sql = 'SELECT ftid,name  FROM pw_friendtype WHERE uid =' .S::sqlEscape($uid);
		return $this->_getAllResultFromSql($sql);
	}

	function getFriendColonys($uid) {
		return $this->arrayToString($this->getFriendColonysByUid($uid),'name');
	}
	 /**
	 * 处理结果集，按指定格式返回
	 *
	 * @param array $result 待处理的数组
	 * @param string $col 返回数组中的指定下标
	 * @return string 返回指定格式的切割的字符串
	 */

	function arrayToString($result,$col) {
		$string = '';
		foreach($result as $key => $value) {
			if($col && $value[$col]) {
				$string .= $string ?  $this->_separate.$value[$col] : $value[$col];
			}
		}
		return $string;
	}

	/**
	 * 获取查询结果
	 *
	 * @access protected
	 * @param string $sql sql语句
	 * @return array 返回结果集
	 */
	function _getAllResultFromSql($sql) {
		$result = array();
		$query = $this->_db->query($sql);
		while ($rt = $this->_db->fetch_array($query)) {
			$result[] = $rt;
		}
		return $result;
	}
	/**
	 * 获取指定用户尚未验证的好友
	 *
	 * @access public
	 * @param int $uid 用户ID
	 * @param array $friendid 未验证用户ID
	 * @return array 返回未验证的用户相关信息
	 */
	function getUnValidFriends($uid,$friendid = 0) {
		$friendid && !is_array($friendid) && $friendid = array($friendid);
		$friendSql = $friendid ? ' AND f.uid IN('.S::sqlImplode($friendid).')':'';
		$sql = "SELECT f.uid,m.uid AS ifu,m.username,mf.uid AS iffriend FROM pw_friends f LEFT JOIN pw_members m ON f.uid=m.uid LEFT JOIN pw_friends mf ON f.friendid=mf.uid AND f.uid=mf.friendid AND mf.status='0' WHERE f.friendid=" . S::sqlEscape($uid) . $friendSql."  AND f.status='1'";
		return  $this->_getAllResultFromSql($sql);
	}
	/**
	 * 对尚未验证的用户进行筛选处理
	 *
	 * @access public
	 * @param int $uid 用户ID
	 * @param array $friendid 未验证用户ID
	 * @param boolean $double 是否双向操作（只接受,接授并添加好友）
	 * @return array 返回相关数据的二维数组,下标update表示待验证的用户ID、add表示要添架的好友、del表示删除脏数据
	 */
	function filterUnValidFriends($uid,$friendid,$double = true){
		$friend = $this->getUnValidFriends($uid,$friendid);
		$friendUpdate  = $delData = $addFriend = array();
		foreach($friend as  $key=>$value) {
			if ($value['ifu']) {
				$friendUpdate[]  = $value['uid'];
				if ($double && !$value['iffriend']) {
					$addFriend[] = array(intval($uid),$value['uid'],0,$this->_timestamp,'');
				}
			} else {
				$delData[] = $value['uid'];
			}
		}
		return array('update'=>$friendUpdate,'add'=>$addFriend,'del'=>$delData);
	}
	/**
	 * 验证好友
	 *
	 * @access public
	 * @param int $uid 用户ID
	 * @param array $friendUpdate 未验证好友
	 * @return int 返回更新的结果数
	 */
	function validateFriends($uid,$friendUpdate) {
		!is_array($friendUpdate) && $friendUpdate = array($friendUpdate);
		$friendUpdate && $this->_db->update("UPDATE pw_friends SET status='0',descrip='',joindate=" . S::sqlEscape($this->_timestamp) . " WHERE friendid=" .S::sqlEscape($uid) . " AND uid IN(".S::sqlImplode($friendUpdate).")");
		return count($friendUpdate);
	}
	/**
	 * 添加好友
	 *
	 * @access public
	 * @param array $addFriend 带添加的好友
	 * @return int 返回添加的好友数
	 */
	function addFriends($addFriend) {
		!is_array($addFriend) && $addFriend = array($addFriend);
		$addFriend && $this->_db->update("REPLACE INTO pw_friends (uid,friendid,status,joindate,descrip) VALUES ".S::sqlMulti($addFriend,false));
		return count($addFriend);
	}
	/**
	 * 取得用户设定的添加好友隐私设置
	 *
	 * @access public
	 * @param array $addFriend 用户ID
	 * @return int 结果集
	 */
	function getFriendCheck($friendid) {
		!is_array($friendid) && $friendid = array($friendid);
		$sql = "SELECT uid,userstatus FROM pw_members WHERE uid IN ( ".S::sqlImplode($friendid)." )";
		$result = $this->_getAllResultFromSql($sql);
		$check = array();
		foreach($result as $key=>$value){
			$check[$value['uid']] = $value['userstatus'];
		}
		return $check;

	}
	/**
	 * 从某好友列表中删除自己
	 *
	 * @access public
	 * @param int $uid 用户ID
	 * @param array $delData 我的好友列表(验证与未验证)
	 * @return int 返回删除的个数
	 */
	function deleteMeFromFriends($uid,$delData) {
		!is_array($delData) && $delData = array($delData);
		$delData && $this->_db->update("DELETE FROM pw_friends WHERE friendid=".S::sqlEscape($uid)." AND uid IN(".S::sqlImplode($delData).")");
		return count($delData);
	}
	/**
	 * 从用户好友列表中删除指定好友
	 *
	 * @access public
	 * @param int $uid 用户ID
	 * @param array $delData 我的好友列表(验证与未验证)
	 * @return int 返回删除的个数
	 */
	function deleteFriendsFromMe($uid,$delData) {
		!is_array($delData) && $delData = array($delData);
		$delData && $this->_db->update("DELETE FROM pw_friends WHERE uid=".S::sqlEscape($uid)." AND friendid IN(".S::sqlImplode($delData).")");
		return count($delData);
	}
	/**
	 * 更新指定用户的好友总数
	 *
	 * @access public
	 * @param array $uid 指定更新的用户
	 */
	function updateFriendNums($uid,$increment=0) {
		!is_array($uid) && $uid = array($uid);
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$increment && $userService->updatesByIncrement($uid, array(), array('f_num' => $increment));
	}

	/**
	 * 对好友请求进行操作,可进行多条和单条同时处理
	 *
	 * @access public
	 * @param int     $uid 用户ID
	 * @param array   $friendid 待验证好友ID
	 * @param boolean $double 验证好友是否为双向
	 * @return string
	 */
	function argeeAddedFriends($uid,$friendid,$double = true) {
		if(intval($uid) <= 0)
			return 'friend_add_fail';
		!is_array($friendid) && $friendid = array($friendid);
		$addCount = $delCount = 0;
		$result = $this->filterUnValidFriends($uid,$friendid,$double);
		$result['update'] && $updateCount = $this->validateFriends($uid,$result['update']);
		$result['add'] && $addCount = $this->addFriends($result['add']);
		$result['del'] && $delCount = $this->deleteMeFromFriends($uid,$result['del']);
		$count = $addCount-$delCount;
		$count && $this->updateFriendNums($uid,$count);
		$result['update'] && $this->updateFriendNums($result['update'],1);
		$this->_sendAgreeNotice($friendid[0],$uid);
		return 'friend_add_success';

	}

	function _sendAgreeNotice($friendid,$winduid) {
		global $db;
		$username = $db->get_value("SELECT username FROM pw_members WHERE uid=".S::sqlEscape($friendid));
		$winddb = $db->get_one("SELECT username,uid FROM pw_members WHERE uid=".S::sqlEscape($winduid));
		if (!$username) return false;

		M::sendNotice(
			array($username),
			array(
				'title' => getLangInfo('writemsg','friend_agree_title',array(
					'username'=>$winddb['username']
				)),
				'content' => getLangInfo('writemsg','friend_agree_content',array(
					'uid'=>$winddb['uid'],
					'username'=>$winddb['username']
				)),
			)
		);
	}

	function isLegalUid($uid) {
		if( 1 > (int)$uid )
			return false;
		return true;
	}

	/**
	 * 同意加入群组请求
	 *
	 * @access public
	 * @param  int     $cyid 	   群组ID
	 * @param  int     $uid  	  用户ID
	 * @param  stirng  $username 用户名
	 * @return string
		colony_joinsuccess			加入成功
		colony_joinsuccess_check	加入成功，需要验证
		colony_alreadyjoin			加入失败，已加入
		colony_joinsuccess_check2	加入失败，已加入，未验证
		colony_joinrefuse			加入失败，拒绝加入
		colony_memberlimit			加入失败，群会员达到上限
		colony_joinlimit			加入失败，用户加入的群达到上限
		colony_joinfail				加入失败，用户积分不足
	 */
	function agreeJoinColony($cyid,$uid,$username) {
		require_once(R_P . 'apps/groups/lib/colony.class.php');
		$colony = new PwColony($cyid);
		return  $colony->join($uid,$username);
	}

	/**
	 * 审核加入群组请求
	 *
	 * @access public
	 * @param  int     $cyid 	   群组ID
	 * @param  int     $uid  	  用户ID
	 * @return string  		  审核状态
	 */
	function checkJoinColony($cyid,$uid) {
		require_once(R_P . 'apps/groups/lib/colony.class.php');
		$colony = new PwColony($cyid);
		if($colony->checkMembers($uid)){
			return 'colony_check_success';
		}
		return 'colony_check_fail';
	}


	/**
	 * 同意加入应用请求
	 *
	 * @access public
	 * @param  int     $appId 应用ID
	 * @return string 是否添加应用成功
	 */
	function agreeWithApp($appId) {
		return 'app_add_success';
	}

	/**
	 * 获取指定用户的指定好友的记录
	 *
	 * @access public
	 * @param  int     $uid 用户ID
	 * @param  int     $friendid 好友ID
	 * @return array   好友的记录
	 */
	function getFriendByUidAndFriendid($uid,$friendid) {
		if(!$this->isLegalUid($uid) && !$this->isLegalUid($friendid)) {
			return array();
		}
		$friend = $this->_db->get_one("SELECT * FROM pw_friends WHERE uid=" . S::sqlEscape($uid) . " AND friendid=" . S::sqlEscape($friendid));
		return $friend;
	}


	/**
	 * 判断是否好友
	 *
	 * @param int $uid
	 * @param int $friendid
	 * @return bool
	 */
	function isFriend($uid, $friendid) {
		if(!$this->isLegalUid($uid) && !$this->isLegalUid($friendid)){
			return false;
		}
		$friendDB = $this->_getFriendDB();
		$user = $friendDB->getUserByUidAndFriendid($uid, $friendid);
		if (!$user) return 'null';
		if ($user['status'] !== '0') return false;
		return true;
	}

	/**
	 * 根据用户uid，找出他的好友uid
	 *
	 * @param int $userId
	 * @return array()	$friendsUids	array(0=>uid1,1=>uid2,.......n=>uidn)
	 */
	function findFriendsByUid($userId) {
		$friends = $this->getFriendsByUid($userId);
		if(!$friends) return array();

		$friendsUids = array();
		foreach ($friends as $friend) {
			$friendsUids[] = $friend['friendid'];
		}
		$friendsUids && $friendsUids = array_diff($friendsUids,array($userId));
		if(!$friendsUids) return array();

		return $friendsUids;
	}

	/**
	 *
	 */
	function getFriendInfoByUid($uid) {
		if(!$this->isLegalUid($uid)){
			return false;
		}
		$sql = 'SELECT f.friendid,m.groupid,f.ftid FROM pw_friends f INNER JOIN pw_members m ON f.friendid = m.uid WHERE f.status = 0 AND f.uid = ' .S::sqlEscape($uid);
		return $this->_getAllResultFromSql($sql);
	}

	/**
	 * 取得用户的好友总数、好友分组人数、未分组人数
	 * @param $uid
	 * @author luomingqu
	 * @return  好友总数、好友分组人数、未分组人数
	 */
	function getFriendsTypeAndNum($uid) {
		$friendsNums = $defaultTypeFriendNum = 0;
		$friendTypeTemp = $friendType = array();
		$friendTypeTemp = $this->getFriendColonysByUid($uid);
		foreach ($friendTypeTemp as $value) {
			$friendType[$value['ftid']] = $value;
		}

		$friendTemp = $this->getFriendInfoByUid($uid);
		foreach ($friendTemp as $value) {
			$friendsNums ++;
			$typeFriendNum[$value['ftid']]++;
			$value['ftid'] == 0 && $defaultTypeFriendNum++;
		}

		foreach ($friendType as $key => $value) {
			$friendType[$key]['num'] = (int)$typeFriendNum[$key];
		}

		return array($friendsNums,$friendType,$defaultTypeFriendNum);
	}


	/**
	 * 统计好友数量
	 *
	 * @param $uid
	 * @param $ftype
	 */
	function countUserFriends($uid, $ftype = null) {
		$sqlAdd = "";
		if (!is_null($ftype)) $sqlAdd .= "AND ftid=".S::sqlEscape($ftype);
		$sql = "SELECT COUNT(m.uid) FROM pw_friends f LEFT JOIN pw_members m ON f.friendid=m.uid WHERE m.uid IS NOT NULL AND f.uid =".S::sqlEscape($uid)." AND status=0 ".$sqlAdd;
		return $this->_db->get_value($sql);
	}

	/**
	 * 根据用户找出好友列表服务
	 *
	 * @param $uid
	 * @param $page
	 * @param $perpage
	 * @param $ftype
	 */
	function findUserFriendsInPage($uid, $page = 1, $perpage = 20, $ftype = null) {
		$friendTemp = $friend = array();
		$friendTemp = $this->findUserFriends($uid, $page, $perpage, $ftype);
		require_once(R_P.'require/showimg.php');
		foreach ($friendTemp as $value) {
			list($value['face']) = showfacedesign($value['face'], '1', 's');
			$value['honor'] = substrs($value['honor'],90);
			$value['lastvisit']	= get_date($value['lastvisit']);
			$friend[$value['uid']] = $value;
		}
		return $friend;
	}

	/**
	 * 根据生日找出好友列表服务
	 *
	 * @param int $uid 用户uid
	 * @param int $nums 排序个数
	 * @param int $page 起始数
	 * @param int $perpage 查找个数
	 * @return array 
	 */
	function findUserFriendsBirthdayInPage($uid, $nums = 3, $page = 1, $perpage = 25) {
		global $timestamp;
		$uid = intval($uid);
		if (!$uid)  return null;
		$page = intval($page);
		$perpage = intval($perpage);
		if ($page <= 0 || $perpage <= 0) return array();
		$offset = ($page - 1) * $perpage;
		$birthdayInfo = array();
		$query = $this->_db->query("SELECT m.uid,m.username,m.bday,m.icon as face " .
			" FROM pw_friends f" .
			" LEFT JOIN pw_members m ON f.friendid=m.uid".
			" WHERE DAYOFYEAR( m.bday ) - DAYOFYEAR(CURDATE()) between 0 and 2 AND f.uid=" . S::sqlEscape($uid) . " AND f.status=0 " . $sqlAdd .S::sqlLimit($offset, $perpage)
		);
		while ($rt = $this->_db->fetch_array($query)) {
			$bday = get_date(PwStrtoTime($rt['bday']), 'm-d');
			$nowday = get_date($timestamp, 'm-d');
			if($bday >= $nowday){
				$birthdayInfo[] = $rt;
			}
		}
		return $this->getBirthdaysByFriends($birthdayInfo,$nums);
	}
	
	/**
	 * 根据好友生日排序
	 *
	 * @param array $birthdayInfo 用户生日信息
	 * @param $nums 排序个数
	 * @return array 
	 */
	function getBirthdaysByFriends($birthdayInfo,$nums) {
		require_once(R_P.'require/showimg.php');
		if (!S::isArray($birthdayInfo)) return array();
		$birthdays = $friendBirthday = array();
		foreach ($birthdayInfo as $value) {
			list($value['face']) = showfacedesign($value['face'], '1', 's');
			$birthdays[$value['uid']] = $value;
		}
		foreach($birthdays as $key => $val) {
			$day = explode('-',$val['bday']);
			$friendBirthday[$val[uid]][bday] = $day['1'] .'-'. $day['2'];
			$friendBirthday[$val[uid]][uid] = $val['uid'];
			$friendBirthday[$val[uid]][username] = $val['username'];  
			$friendBirthday[$val[uid]][face] = $val['face'];     
		} 
   		asort($friendBirthday);
   		return array_slice($friendBirthday,0,$nums,true);
	}
	
	/**
	 * 根据用户找出好友列表数据
	 *
	 * @param int $uid
	 * @param int $page
	 * @param int $perpage
	 * @param unknown_type $ftype
	 */
	function findUserFriends($uid, $page = 1, $perpage = 20, $ftype = null) {
		$sqlAdd = "";
		if (!is_null($ftype)) $sqlAdd .= " AND f.ftid=".S::sqlEscape($ftype);
		$page = intval($page);
		$perpage = intval($perpage);
		if ($page <= 0 || $perpage <= 0) return array();

		$offset = ($page - 1) * $perpage;
		$result = array();
		$query = $this->_db->query("SELECT m.uid,m.username,m.icon as face,m.honor,m.groupid,m.memberid,m.gender,md.thisvisit,md.lastvisit,md.fans,f.ftid,a.uid AS attention" .
			" FROM pw_friends f" .
			" LEFT JOIN pw_attention a ON f.uid=a.uid AND f.friendid=a.friendid" .
			" LEFT JOIN pw_members m ON f.friendid=m.uid".
			" LEFT JOIN pw_memberdata md ON f.friendid=md.uid".
			" WHERE m.uid IS NOT NULL AND f.uid=" . S::sqlEscape($uid) . " AND f.status=0 " . $sqlAdd .
			" ORDER BY f.joindate DESC ".S::sqlLimit($offset, $perpage)
		);
		while ($rt = $this->_db->fetch_array($query)) {
			$result[] = $rt;
		}

		return $result;

	}

	/**
	 * 添加好友
	 *
	 * @param $uid
	 * @param int $touid
	 * @param int $descrip
	 * @param int $ftid
	 * @param bool $isUpdatemem
	 */
	function addFriend($uid, $touid, $descrip = '', $ftid = 0, $isUpdatemem = true) {
		global $winduid;
		if (!$uid || !$touid) return false;
		if (($isFriend = $this->isFriend($uid, $touid)) === true) return 'user_friend_exists';
		$friendDB = $this->_getFriendDB();
		if ($isFriend === 'null') {
			$fieldData = array(
				'uid'			=> $uid,
				'friendid'		=> $touid,
				'status'		=> 0,//是好友
				'descrip'		=> $descrip, //对好友的描述
				'ftid'			=> $ftid, //好友分类
				'joindate'		=> $this->_timestamp
			);
			$friendDB->insert($fieldData);
		} else {
			$friendDB->updateByUidAndFid($uid, $touid, array('status'=>0));
		}

		$attentionService = L::loadClass('attention', 'friend');
		$attentionService->addFollow($uid, $touid,20,'addFriend');
		//$attentionService->addFollow($touid, $uid);

		$userCache = L::loadClass('userCache', 'user');
		$userCache->delete($uid, 'friend');

		if ($isUpdatemem) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userService->updateByIncrement($uid, array(), array('f_num' => 1));
		}
		return true;
	}


	/**
	 * 解除好友
	 *
	 * @param $uid
	 * @param $touid
	 */
	function delFriend($uid, $touid) {
		if(!$uid || !$touid) return false;
		$attentionService = L::loadClass('Attention', 'friend'); /* @var $attentionService PW_Attention */

		$this->_db->update("DELETE FROM pw_friends WHERE uid=" . S::sqlEscape($uid) . " AND friendid=" . S::sqlEscape($touid));
		$this->_db->update("DELETE FROM pw_friends WHERE uid=" . S::sqlEscape($touid) . " AND friendid=" . S::sqlEscape($uid));

		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$user = $userService->get($uid, false, true);
		$user['f_num'] > 0 && $userService->updateByIncrement($uid, array(), array('f_num' => -1));
		$user = $userService->get($touid, false, true);
		$user['f_num'] > 0 && $userService->updateByIncrement($touid, array(), array('f_num' => -1));

		$userCache = L::loadClass('userCache', 'user');
		$userCache->delete(array($uid, $touid), 'friend');

		return true;
	}

	/**
	 * 后台删除会员操作时，删除好友
	 *
	 */
	function delFriendByUids($uids) {
		if(!$uids || !is_array($uids)) return false;
		$friendDB = $this->_getFriendDB();
		$friendDB->delFriendsByUids($uids);
		$friendDB->delFriendsByFriendsUids($uids);
		return true;
	}

	function delFriendByFriendids($uids) {
		if(!$uids || !is_array($uids)) return false;
		$friendDB = $this->_getFriendDB();
		$friendDB->delFriendsByFriendsUids($uids);
		return true;
	}

	function getFriendList($uid, $page, $perpage) {
		$friendDB = $this->_getFriendDB();
		return $friendDB->getFriendList($uid, ($page - 1) * $perpage, $perpage);
	}

	function getUidsInFriendList($uid, $page, $perpage) {
		$array = array();
		if ($list  = $this->getFriendList($uid, $page, $perpage)) {
			foreach ($list as $key => $value) {
				$array[] = $value['friendid'];
			}
		}
		return $array;
	}

	/**
	 * Get _getFriendDB
	 *
	 * @access protected
	 * @return PW_FriendDB
	 */
	function _getFriendDB() {
		return L::loadDB('Friend', 'friend');
	}
}
?>