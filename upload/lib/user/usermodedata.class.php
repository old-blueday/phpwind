<?php
!defined('P_W') && exit('Forbidden');

/**
 *  用户缓存相关服务类文件
 *  
 *	@package user
 */

class PW_UserModeData {

	var $_db;
	
	function PW_UserModeData() {
		global $db;
		$this->_db = &$db;
	}
	
	function get_article($uid, $num = 20) {
		$array = array();
		$query = $this->_db->query("SELECT tid,subject,postdate FROM pw_threads WHERE authorid=" . S::sqlEscape($uid) . ' AND ifcheck=1 AND fid!=0 ORDER BY tid DESC ' . S::sqlLimit($num));
		while ($rt = $this->_db->fetch_array($query)) {
			$rt['postdate'] = get_date($rt['postdate'], 'Y-m-d');
			$array[] = $rt;
		}
		return $array;
	}

	function get_cardtopic($uid, $num = 1) {
		require_once(R_P . 'require/functions.php');
		$_sql_where = '';
		if ($notInFid = getSpecialFid()) {
			$_sql_where = ' AND fid NOT IN(' . $notInFid . ')';
		}
		$rt = $this->_db->get_one("SELECT tid,subject,postdate FROM pw_threads WHERE authorid=" . S::sqlEscape($uid) . $_sql_where . ' ORDER BY tid DESC LIMIT 1');
		if (empty($rt)) {
			return array();
		}
		$pw_tmsgs = getTtable($rt['tid']);
		$r2 = $this->_db->get_one("SELECT aid,content FROM $pw_tmsgs WHERE tid=" . S::sqlEscape($rt['tid']));
		$rt['subject'] = substrs(stripWindCode($rt['subject']), 100, N);
		$rt['content'] = substrs(stripWindCode($r2['content']), 100, N);
		$rt['postdate_s'] = get_date($rt['postdate']);
		if ($r2['aid']) {
			$attachs = L::loadDB('attachs', 'forum');
			$rt['attimages'] = array();
			$atts = $attachs->getByTid($rt['tid'], 0, 4, 'img');
			foreach ($atts as $key => $val) {
				$a_url = geturl($val['attachurl'], 'show', $val['ifthumb']);
				if ($a_url != 'nopic') {
					$rt['attimages'][] = is_array($a_url) ? $a_url[0] : $a_url;
				}
			}
		}
		return $rt;
	}

	function get_carddiary($uid, $num = 1) {
		$diaryServer = L::loadClass('Diary', 'diary');
		$array = $diaryServer->findUserDiarys($uid, 1, $num, null, array(0));
		if (empty($array)) {
			return array();
		}
		$array = array_shift($array);
		$rt = array(
			'did'		 => $array['did'],
			'uid'		 => $array['uid'],
			'subject'	 => substrs(stripWindCode($array['subject']), 100, N),
			'content'	 => substrs(stripWindCode($array['content']), 100, N),
			'postdate_s' => get_date($array['postdate'])
		);
		if ($array['aid']) {
			$atts = unserialize($array['aid']);
			$rt['attimages'] = array();
			$i = 0;
			foreach ($atts as $key => $val) {
				if ($val['type'] != 'img') continue;
				$a_url = geturl('diary/' .$val['attachurl'], 'show', $val['ifthumb']);
				if ($a_url != 'nopic') {
					$rt['attimages'][] = is_array($a_url) ? $a_url[0] : $a_url;
				}
				if (++$i > 3) {
					break;
				}
			}
		}
		return $rt;
	}

	function get_friend($uid, $num = 20) {
		$friendService = L::loadClass('friend', 'friend');
		return $friendService->getUidsInFriendList($uid, 1, $num);
	}

	function get_weibo($uid, $num = 20) {
		$weiboService = L::loadClass('weibo', 'sns');
		return $weiboService->getUserWeibos($uid, 1, $num);
	}
	
	function get_colony($uid, $num = 20) {
		require_once(R_P . 'require/bbscode.php');
		require_once(R_P . 'apps/groups/lib/colony.class.php');
		$o_styledb = L::config('o_styledb', 'o_config');
		$array = array();
		$query = $this->_db->query("SELECT c.* FROM pw_cmembers cm LEFT JOIN pw_colonys c ON cm.colonyid=c.id WHERE cm.uid=" . S::sqlEscape($uid) . " AND cm.ifadmin <> '-1' ORDER BY cm.colonyid DESC " . S::sqlLimit($num));
		while ($rt = $this->_db->fetch_array($query)) {
			if ($rt['cnimg']) {
				list($rt['cnimg']) = geturl("cn_img/$rt[cnimg]",'lf');
			} else {
				$rt['cnimg'] = $GLOBALS['imgpath'] . '/g/groupnopic.gif';
			}
			$rt['descrip'] && $rt['descrip'] = convert($rt['descrip'], array());
			$rt['stylename'] = $o_styledb[$rt['styleid']]['cname'];
			$rt['colonyNums'] = PwColony::calculateCredit($rt);
			$rt['createtime'] = get_date($rt['createtime'], 'Y-m-d');
			$array[] = $rt;
		}
		return $array;
	}

	function get_messageboard($uid, $num = 20) {
		global $db_windpost,$tpc_author;
		require_once(R_P . 'require/bbscode.php');
		require_once(R_P . 'require/showimg.php');
		$array = $boardids = array();
		$query = $this->_db->query("SELECT o.*,m.icon as face,m.groupid FROM pw_oboard o LEFT JOIN pw_members m ON o.uid=m.uid WHERE o.touid=" . S::sqlEscape($uid) . " ORDER BY o.id DESC " . S::sqlLimit($num));
		while ($rt = $this->_db->fetch_array($query)) {
			$rt['postdate']	= get_date($rt['postdate']);
			list($rt['face']) = showfacedesign($rt['face'], 1, 'm');
			if (strpos($rt['title'],'[s:') !== false) {
				$tpc_author = $rt['username'];
				$rt['title'] = showface($rt['title']);
			}
			if (strpos($rt['title'],'[url') !== false) {
				$rt['title'] = convert($rt['title'],$db_windpost);
			}
			$array[$rt['id']] = $rt;
		}
		return $array;
	}

	function get_recommendUsers($uid, $num = 20) {
		$attentionService = L::loadClass('attention', 'friend');
		$attUser = $attentionService->getUidsInFollowList($uid);
		$attUser[] = $uid;
		$weiboService = L::loadClass('weibo', 'sns');
		if (!$user = $weiboService->getWeiboAuthors($num, $attUser)) {
			return array();
		}
		$uids = $array = array();
		foreach ($user as $key => $value) {
			$uids[] = $value['uid'];
		}
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$uinfo = $userService->getUsersWithMemberDataByUserIds($uids);
		foreach ($uinfo as $key => $value) {
			if (!$value['uid']) continue;
			list($value['face']) = showfacedesign($value['icon'], '1', 's');
			$array[] = array(
				'uid' => $value['uid'],
				'username' => $value['username'],
				'honor' => $value['honor'],
				'fans' => $value['fans'],
				'face' => $value['face'],
				'groupid' => $value['groupid'],
				'memberid' => $value['memberid']
			);
		}
		return $array;
	}
	
	function get_friendsBirthday($uid, $value){
		 $friendService = L::loadClass('friend','friend');
		 return $friendService->findUserFriendsBirthdayInPage($uid, $value['num']);
	}
	
	function get_tags($uid){
		 $memberTagsService = L::loadClass('MemberTagsService','user');
		 return $memberTagsService->getMemberTagsByUid($uid);
	}
}
?>