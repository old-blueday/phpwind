<?php
/**
 * 记录服务类文件
 * 
 * @package Write
 */

!defined('P_W') && exit('Forbidden');

/**
 * 记录服务对象
 * 
 * @package Write
 */
class PW_Write {
	
	/**
	 * 根据用户，计算记录数量
	 * 
	 * @param array $userIds	array(1,2,3....,n)
	 * @return int $result 数量
	 */
	function countUserFriendsWrites($userIds) {
		$writeDb = $this->_getWriteDB();
		$result = $writeDb->countUserWritesSqlByIn($userIds);
		return $result;
	}
	
	/**
	 * 找出记录板块里的最新记录,最新记录包括好友和自己
	 * 
	 * @param array $userIds
	 * @param int $page
	 * @param int $perpage
	 */
	function findUserFriendsWritesInPage($userId, $page = 1, $perpage = 20, $pageUrl) {
		if ($userId <= 0) return array();
		
		$writedata = $touids = array();
		$friends = $this->findOpenWritesFriendsUids($userId);
		$friends[] = $userId;
		
		$count = $this->countUserFriendsWrites($friends);
		$pages = numofpage($count, $page, ceil($count / $perpage), $pageUrl);
		
		$friendsWrites = $this->findUserWritesByUids($friends, $page, $perpage);
		list($writedata, $touids) = $friendsWrites;
		
		return array($this->parse($writedata), $touids, $pages, $count);
	}
	
	/**
	 * 根据用户uid找出的记录
	 * 
	 * @param array $userIds
	 * @param int $page
	 * @param int $perpage
	 */
	function findUserWritesByUids($userIds, $page = 1, $perpage = 20) {
		$reslut = array();
		$writeDb = $this->_getWriteDB();
		$writeDataTemp = $writeDb->findUserWritesByUids($userIds, $page, $perpage);
		
		if (!$writeDataTemp) return array();
		$writedata = array();
		$writedata = $this->_formatWriteData($writeDataTemp);
		
		foreach ($writedata as $write) {
			if (!$write['touid']) continue;
			$touids[$write['touid']] = $write['touid'];
		}
		
		if ($touids) {
			$userService = $this->_serviceFactory('UserService', 'user'); /* @var $userService PW_UserService */
			$members = $userService->getByUserIds($touids);
			foreach ($members as $member) {
				$touids[$member['uid']] = $member['username'];
			}
		}
		
		return array($writedata, $touids);
	}
	
	/**
	 * 格式记录的数据，比如时时间、头像
	 * 
	 * @param unknown_type $date
	 */
	function _formatWriteData($date) {
		global $db_shield, $groupid;
		require_once(R_P . 'require/showimg.php');
		if ($date && !is_array($date)) return array();
		$result = array();
		foreach ($date as $write) {
			if ($write['groupid'] == 6 && $db_shield && $groupid != 3) {
				$write['content'] = appShield('ban_write');
			}
			list($write['postdate']) = getLastDate($write['postdate']);
			list($write['icon']) = showfacedesign($write['icon'], 1, 'm');
			$result[] = $write;
		}
		return $result;
	}
	
	/**
	 * 根据用户id 找出公开记录的好友
	 * 
	 * @param int 	$userId 用户id
	 * @param bool	$ifSelf	是否包括自己
	 * @return array  $result	返回uids
	 */
	function findOpenWritesFriendsUids($userId, $ifSelf = True) {
		$result = array();
		$_friends = array();
		$_friends = $this->_findUserFriendsUids($userId);
		if (!$_friends) return false;
		
		$_friendsUids = array();
		foreach ($_friends as $friend) {
			$_friendsUids[] = $friend['friendid'];
		}
		
		$_openWritesFriends = array();
		$_openWritesFriends = $this->_findUserOwritePrivacy($_friendsUids);
		if ($ifSelf) $result = array($userId);
		
		foreach ($_openWritesFriends as $friend) {
			$result[] = $friend['uid'];
		}
		
		return $result;
	}
	
	/**
	 * 根据用户uid，统计自己的记录数量
	 * 
	 * @param	int	$userId	用户id
	 * @return	$result		统计数
	 */
	function countUserWrites($userId) {
		$writeDb = $this->_getWriteDB();
		$result = $writeDb->countUserWrites($userId);
		return $result;
	}
	
	/**
	 * 取得用户自己的记录
	 * 
	 * @param $userId
	 * @param $page
	 * @param $perpage
	 * @param $pageUrl
	 * @return array    $reslut=>是记录数据，$page=> 是分页
	 */
	function findUserWritesInPage($userId, $page = 1, $perpage = 20, $pageUrl) {
		$count = $this->countUserWrites($userId);
		if (!$count) return array();
		$pages = numofpage($count, $page, ceil($count / $perpage), $pageUrl);
		
		$userWrites = $this->findUserWritesByUids(array($userId), $page, $perpage);
		
		$writedata = array();
		$touids = array();
		list($writedata, $touids) = $userWrites;
		if (!$writedata) return array();
		
		$reslut = array();
		foreach ($writedata as $writes) {
			$writes['username'] = $touids[$writes['touid']];
			if ($writes['touid']) $writes['content'] = '@<a href="'.USER_URL. $writes['touid'] . '">' . $writes['username'] . '</a> ' . $writes['content'];
			$reslut[] = $writes;
		}
		return array($this->parse($reslut), $pages, $count);
	}
	
	function getWrite($writeId) {
		$writeDb = $this->_getWriteDB();
		return $writeDb->_get($writeId);
	}
	
	function getUserOuserInfo($userId) {
		global $winddb, $winduid;
		if ($userId == $winduid) return $winddb;
		
		$userService = $this->_serviceFactory('UserService', 'user'); /* @var $userService PW_UserService */
		$member = $userService->get($userId);
		$memberInfo = array();
		$memberInfo = array('uid' => $member['uid'], 'username' => $member['username'], 'icon' => $member['icon'], 'groupid' => $member['groupid']);
		
		$ouserdataService = $this->_serviceFactory('Ouserdata', 'sns'); /* @var $ouserdataService PW_Ouserdata */
		$ouserdata = $ouserdataService->get($userId);
		$ouserdataInfo = array();
		$ouserdataInfo = array('index_privacy' => $ouserdata['index_privacy'], 'owrite_privacy' => $ouserdata['owrite_privacy']);
		
		$result = array();
		$result = array_merge($memberInfo, $ouserdataInfo);
		
		return $result;
	}
	
	function post($text, $tosign, $minLenText = 3, $maxLenText = 255, $source = 'web', $photoId = 0, $ruid = 0) {
		global $winduid;
		$text = $this->_checkPost($text, $minLenText, $maxLenText);
		
		$ruid = $this->_getFirstCharByText($text, $ruid);
		if ($ruid) {
			$text = ltrim(strstr($text, 32));
			strlen($text) < $minLenText && $this->_showMsg('mode_o_write_textminlen');
		}
		
		$content = $this->_getTextWithPhoto($photoId, $text);
		
		$f_id = $this->send($winduid, array('touid' => $ruid, 'postdate' => $this->_getTimestamp(), 'isshare' => 0, 'source' => $source, 'content' => S::escapeChar($content)));
		
		return array($f_id, $content);
	
	}
	
	function parse($writes) {
		if (!is_array($writes) || !count($writes)) return $writes;
		$newList = array();
		
		$photoIds = array();
		$smileParser = L::loadClass('smileparser', 'smile'); /* @var $smileParser PW_SmileParser */
		foreach ($writes as $key => $write) {
			$write = $this->_parsePhoto($write);
			if (isset($write['photoId']) && $write['photoId']) $photoIds[$write['id']] = $write['photoId'];
			unset($write['photoId']);
			$write['content'] = $smileParser->parse($write['content']);
			$newList[$write['id']] = $write;
		}
		
		if (!empty($photoIds)) {
			$newList = $this->_appendPhotosInfo($newList, $photoIds);
		}
		return $newList;
	}
	
	function _parsePhoto($writeData) {
		if (!preg_match('/\[upload=(\d+)\]/Ui', $writeData['content'], $out)) return $writeData;
		
		$photoId = $out[1];
		$writeData['content'] = str_replace('[upload=' . $photoId . ']', '', $writeData['content']);
		$writeData['photoId'] = $photoId;
		return $writeData;
	}
	function _appendPhotosInfo($writes, $photoIds) {
		if (!is_array($photoIds) || !count($photoIds)) return $writes;
		$photos = array();
		
		global $db;
		$query = $db->query("SELECT * FROM pw_cnphoto WHERE pid IN (" . S::sqlImplode($photoIds) . ")");
		while ($rt = $db->fetch_array($query)) {
			$photos[$rt['pid']] = array('photo'=> getphotourl($rt['path']), 'photoThumb'=>getphotourl($rt['path'], $rt['ifthumb']));
		}
		foreach ($photoIds as $writeId => $photoId) {
			if (!isset($photos[$photoId])) continue;
			$writes[$writeId]['photo'] = $photos[$photoId]['photo'];
			$writes[$writeId]['photoThumb'] = $photos[$photoId]['photoThumb'];
		}
		return $writes;
	}
	
	function _checkPost($text, $minLenText, $maxLenText) {
		global $winduid;
		$textlen = strlen($text);
		$textlen < $minLenText && $this->_showMsg('mode_o_write_textminlen');
		$textlen > $maxLenText && $this->_showMsg('mode_o_write_textmaxlen');
		
		$writeDb = $this->_getWriteDB();
		$rt = $writeDb->getlLatestWritesbyUid($winduid);
		if ($rt['content'] == $text) {
			$this->_showMsg('mode_o_write_sametext');
		} elseif ($this->_getTimestamp() - $rt['postdate'] < 1) {
			$this->_showMsg('mode_o_write_timelimit');
		}
		
		$wordsService = $this->_serviceFactory('FilterUtil', 'filter');
		if (($banword = $wordsService->comprise($text)) !== false) {
			$this->_showMsg('content_wordsfb');
		}
		
		return $text;
	}
	
	function _getFirstCharByText($text, $ruid = 0) {
		$firstchar = substr($text, 0, 1);
		switch ($firstchar) {
			case '@':
				$uname = trim(substr($text, 1, strpos($text, 32) - 1));
				$userService = $this->_serviceFactory('UserService', 'user'); /* @var $userService PW_UserService */
				$member = $userService->getByUserName($uname);
				$ruid = $member['uid'];
				break;
			default:
				$ruid = (int) $ruid;
				$firstchar = '';
		}
		
		return $ruid;
	}
	
	function _getTextWithPhoto($photoId, $content) {
		global $winduid;
		$content = preg_replace('/(\[(upload=\d+)\])/Ui', '&#91;\2&#93;', $content);
		
		if (!$photoId) return $content;
		
		L::loadClass('photo', 'colony', false);
		$albumService = new PW_Photo($winduid, 0, 0, 0);
		$photoInfo = $albumService->getPhotoInfo($photoId);
		if (!$photoInfo) return $content;
		
		$albumInfo = $albumService->getAlbumInfo($photoInfo['aid']);
		if ($albumInfo['ownerid'] == $winduid) {
			$content = "[upload=$photoId]" . $content;
		}
		
		return $content;
	}
	
	function _getTimestamp() {
		global $timestamp;
		return $timestamp;
	}
	
	function send($userId, $fieldsData) {
		$writeDb = $this->_getWriteDB();
		return $writeDb->add(array('uid' => $userId) + $fieldsData);
	}
	
	function delete($writeId) {
		$row = array();
		$writeDb = $this->_getWriteDB();
		$row = $this->getWrite($writeId);
		if (empty($row)) return 0;
		return $writeDb->_delete($writeId);
	}
	
	/**
	 * 取得@我的记录数量
	 * 
	 * @param int	$userId		@我的     touid
	 */
	function countWritesToUser($userId) {
		$writeDb = $this->_getWriteDB();
		$result = $writeDb->countWritesToUser($userId);
		
		return $result;
	}
	
	/**
	 * 取得@我的记录列表
	 * 
	 * @param $userId
	 * @param $page
	 * @param $perpage
	 * @param $pageUrl
	 */
	function findWritesToUserInPage($userId, $page = 1, $perpage = 20, $pageUrl) {
		global $db_shield, $groupid, $winduid, $windid;
		
		$count = $this->countWritesToUser($userId);
		if (!$count) return array();
		$pages = numofpage($count, $page, ceil($count / $perpage), $pageUrl);
		
		$writeDb = $this->_getWriteDB();
		$writedata = $writeDb->findWritesToUser($userId, $page, $perpage);
		if (!$writedata) return array();
		
		$result = array();
		foreach ($writedata as $write) {
			if ($write['groupid'] == 6 && $db_shield && $groupid != 3) {
				$write['content'] = appShield('ban_write');
			}
			list($write['postdate']) = getLastDate($write['postdate']);
			list($write['icon']) = showfacedesign($write['icon'], 1, 'm');
			if ($write['touid'] && $write['username']) {
				$write['content'] = '<a href="'.USER_URL. $write['uid'] . '">' . $write['username'] . '</a> @<a href="u.php?&uid=' . $winduid . '">' . $windid . '</a> ' . $write['content'];
			}
			$result[] = $write;
		}
		
		return array($this->parse($result), $pages, $count);
	}
	
	/**
	 * 根据用户范围，筛选记录权限
	 * 
	 * @param array		$userIds		array(1,2,3,4,......n)	
	 * @return array 	
	 */
	function _findUserOwritePrivacy($userIds) {
		$ouserdataService = $this->_serviceFactory('Ouserdata', 'sns'); /* @var $ouserdataService PW_Ouserdata */
		return $ouserdataService->findUserOwritePrivacy($userIds);
	}
	
	/**
	 * 找出用户的朋友UID
	 * @param int $userId	用户
	 * @param bool $ifSelf	是否包括自己
	 */
	function _findUserFriendsUids($userId) {
		$friendsService = $this->_serviceFactory('Friend', 'friend'); /* @var $friendsService PW_Friend */
		return $friendsService->getFriendsByUid($userId);
	}
	
	/**
	 * Get PW_WriteDB
	 * 
	 * @access protected
	 * @return PW_WriteDB
	 */
	function _getWriteDB() {
		return L::loadDB('Write', 'sns');
	}
	
	function _showMsg($msg) {
		return Showmsg($msg);
		;
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
