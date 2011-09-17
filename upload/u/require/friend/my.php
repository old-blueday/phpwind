<?php
!defined('R_P') && exit('Forbidden');

require_once (R_P . 'require/showimg.php');

S::gp(array('type', 'u'));
$u = (int) $u;
!$u && $u = $winduid;

$basename = "u.php?";
$type = empty($type) ? 'attention' : $type;
$thisbase = $basename . "a=$a&type=$type&";

if ($type == 'attention') {
	$attentionSerivce = L::loadClass('Attention', 'friend'); /* @var $attentionSerivce PW_Attention */
	$updateSQL = array();
	$count = $winddb['follows'];
	if ($count < 1000 ||  $count > 10000000) {
		$num = $attentionSerivce->countFollows($winduid);
		if ($num != $count) {
			$updateSQL['follows'] = $winddb['follows'] = $count = $num;
		}
	}
	$page > ceil($count/$db_perpage) && $page = ceil($count/$db_perpage);
	$attentions = $count ? $attentionSerivce->getFollowListInPage($winduid, $page, $db_perpage) : array();
	$attentionedUids = $myAttentionUids = array();
	foreach ($attentions as $attention) {
		$attentionedUids[] = $attention['uid'];
	}
	$myAttentionUids = $attentionSerivce->getUidsInFansListByFriendids($winduid, $attentionedUids);
	foreach ($attentions as $key => $attention) {
		$attentions[$key]['attention'] = 1;
		if (!in_array($attention['uid'], $myAttentionUids)) continue;
		$attentions[$key]['attentionEach'] = 1;
	}
	if ($updateSQL) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->update($winduid, array(), $updateSQL);
	}
	$pages = numofpage($count,$page,ceil($count/$db_perpage),"{$thisbase}&");

	//所有好友
	$friendsAllUids = getFriendsIdArr($winduid);

} elseif ($type == 'attentioned') {
	$attentionSerivce = L::loadClass('Attention', 'friend'); /* @var $attentionSerivce PW_Attention */
	$updateSQL = array();
	$count = $winddb['fans'];
	if ($count < 1000 ||  $count > 10000000) {
		$num = $attentionSerivce->countFans($winduid);
		if ($num != $count) {
			$updateSQL['fans'] = $winddb['fans'] = $count = $num;
		}
	}
	if ($winddb['newfans'] > 0) {
		$updateSQL['newfans'] = $winddb['newfans'] = 0;
	}
	$page > ceil($count/$db_perpage) && $page = ceil($count/$db_perpage);
	$attentions = $count ? $attentionSerivce->getFansListInPage($winduid, $page, $db_perpage) : array();
	$attentionedUids = $myAttentionUids = array();
	foreach ($attentions as $attention) {
		$attentionedUids[] = $attention['uid'];
	}
	$myAttentionUids = $attentionSerivce->getUidsInFollowListByFriendids($winduid, $attentionedUids);
	foreach ($attentions as $key => $attention) {
		$attentions[$key]['attention'] = 0;
		if (!in_array($attention['uid'], $myAttentionUids)) continue;
		$attentions[$key]['attentionEach'] = 1;
		$attentions[$key]['attention'] = 1;
	}
	if ($updateSQL) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->update($winduid, array(), $updateSQL);
	}
	$pages = numofpage($count,$page,ceil($count/$db_perpage),"{$thisbase}&");

	//所有好友
	$friendsAllUids = getFriendsIdArr($winduid);
} elseif ($type == 'my') {

	$page = (int) S::getGP('page');
	$ftid = (int)S::getGP('ftid');
	$page < 1 && $page = 1;
	$db_perpage = 12;
	$ftype = $ftid == '-1' ? 0 : (is_numeric($ftid) && $ftid > 0 ? $ftid : null);
	$friendType  = array();
	$count = $friendsNums = $defaultTypeFriendNum = 0;
	$friendsService = L::loadClass('Friend', 'friend'); /* @var $friendsService PW_Friend */
	list($friendsNums, $friendType, $defaultTypeFriendNum) = $friendsService->getFriendsTypeAndNum($winduid);
	if ($friendsNums != $winddb['f_num']) {
		$userService = L::loadClass('UserService', 'user');
		$userService->update($winduid, array(), array('f_num' => $friendsNums));
	}
	$count = (int)$friendsService->countUserFriends($u,$ftype);
	$page > ceil($count/$db_perpage) && $page = ceil($count/$db_perpage);
	$friends = $count ? $friendsService->findUserFriendsInPage($u, $page, $db_perpage, $ftype) : array();

	$uids = array_keys($friends);
	$attentionSerivce = L::loadClass('attention', 'friend'); /* @var $attentionSerivce PW_Attention */
	$myFansUids = $attentionSerivce->getUidsInFansListByFriendids($winduid, $uids);
	foreach ($friends as $key => $friend) {
		$attentions[$key]['attentionEach'] = 0;
		if ($friend['attention'] && in_array($friend['uid'], $myFansUids)) {
			$friends[$key]['attentionEach'] = 1;
		}
	}
	$friendsAllUids = getFriendsIdArr($winduid);
	$pages = numofpage($count,$page,ceil($count/$db_perpage),"{$thisbase}ftid=$ftype&");
} elseif ($type == 'find') {

	S::gp(array('step', 'according'));
	$db_perpage = 12;
	$according = $according ? $according : 'user';
	${$according.'checked'} = 'selected="selected"';
	//所有好友
	$friendsAllUids = getFriendsIdArr($winduid);

	if ($step == 2) {
		S::gp(array('f_keyword', 'decode'));
		!isset($f_keyword) && Showmsg('pse_input_keyword');
		$decode && $f_keyword = pwConvert($f_keyword, $db_charset, 'utf8');

		if($according && !in_array($according,array('user','uid','email','tags'))){
			showMsg("抱歉,搜索类型不存在");
		}

		$f_keyword = strip_tags($f_keyword);
		$count = 0;
		$members = $myAttentionUids = array();
		$searchURL = "u.php?a=friend&type=find";

		switch($according){
			case "user" :
				$searcherService = L::loadclass('searcher', 'search'); /* @var $searcherService PW_searcher */
				$uids = $memberdata = $attentionData = array();
				list($count,$users) = $searcherService->searchUsers($f_keyword,$page,$db_perpage);
				$users = $users ? $users : array();
				foreach ($users as $user) {
					$uids[] = $user['uid'];
				}
				if ($uids) {
					$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
					foreach ($userService->getUsersWithMemberDataByUserIds($uids) as $rt) {
						$memberdata[$rt['uid']] = $rt['thisvisit'];
					}

					$attentionSerivce = L::loadClass('attention', 'friend'); /* @var $attentionSerivce PW_Attention */
					$myAttentionsInfo = $attentionSerivce->getFollowListByFriendids($winduid, $uids);
					foreach ($myAttentionsInfo as $myAttentions) {
						$myAttentionUids[] = $myAttentions['friendid'];
					}
					foreach($users as $key => $user) {
						$user['thisvisit'] = $memberdata[$user['uid']];
						list($user['face']) = showfacedesign($user['icon'], '1', 's');
						in_array($user['uid'], $myAttentionUids) && $user['attention'] = 1;
						$members[] = $user;
					}
				}
				$members && $pages = ($count) ? numofpage($count,$page,ceil($count/$db_perpage),$searchURL."&f_keyword=".urlencode($f_keyword)."&step=2&",null,'',true) : '';
				break;

			case "uid" :
				$f_keyword = (int)$f_keyword;
				$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
				$user = $userService->get($f_keyword, true, true);
				if ($user) {
					$count = 1;
					$attentionSerivce = L::loadClass('Attention', 'friend'); /* @var $attentionSerivce PW_Attention */
					$user['attention'] = $attentionSerivce->isFollow($winduid, $user['uid']);
					list($user['face']) = showfacedesign($user['icon'], '1', 's');
					$members[] = $user;
				}
				!$f_keyword && $f_keyword = '';

				break;

			case "email" :
				$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
				$user = $userService->get($userService->getUserIdByEmail($f_keyword), true, true);
				if ($user) {
					$count = 1;
					$attentionSerivce = L::loadClass('Attention', 'friend'); /* @var $attentionSerivce PW_Attention */
					$user['attention'] = $attentionSerivce->isFollow($winduid, $user['uid']);
					list($user['face']) = showfacedesign($user['icon'], '1', 'm');
					$members[] = $user;
				}
				break;

			case "tags" :  //标签找人
				$memberTagsService = L::loadClass('MemberTagsService','user');
				list($count,$tagsUids,$memberTags) = $memberTagsService->getUidsByTagName($f_keyword, ($page - 1) * $db_perpage, $db_perpage);
				$tagsUids = $tagsUids ? $tagsUids : array();
				$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
				$users = $userService->getByUserIds($tagsUids);
				$users = $users ? $users : array();
				foreach ($users as $user) {
					$uids[] = $user['uid'];
				}
				if ($uids) {
					foreach ($userService->getUsersWithMemberDataByUserIds($uids) as $rt) {
						$memberdata[$rt['uid']] = $rt['thisvisit'];
					}

					$attentionSerivce = L::loadClass('attention', 'friend'); /* @var $attentionSerivce PW_Attention */
					$myAttentionsInfo = $attentionSerivce->getFollowListByFriendids($winduid, $uids);
					foreach ($myAttentionsInfo as $myAttentions) {
						$myAttentionUids[] = $myAttentions['friendid'];
					}
					foreach($users as $key => $user) {
						$user['thisvisit'] = $memberdata[$user['uid']];
						list($user['face']) = showfacedesign($user['icon'], '1', 's');
						in_array($user['uid'], $myAttentionUids) && $user['attention'] = 1;
						$members[] = $user;
					}
				}
				$members && $pages = ($count) ? numofpage($count,$page,ceil($count/$db_perpage),$searchURL."&f_keyword=".urlencode($f_keyword)."&step=2&according=tags&",null,'',true) : '';
				break;
		}
	} else {

		$friendUids = $mostFriendUids = $recommendUids = $onlineUids = $attentionUids = $onlineMembers = array();
		$friendService = L::loadClass('Friend', 'friend'); /* @var $friendService PW_Friend */
		$friendUids = $friendService->findFriendsByUid($winduid);

		/**=================朋友的朋友Start==============**/

		$mostFriends = array();
		$friendUidList =  $friendUids;
		$friendUids  = randArray($friendUids,10);
		if ($friendUids) {
			$query = $db->query('SELECT friendid FROM pw_friends WHERE uid IN(' . S::sqlImplode($friendUids) . ') AND status=0');
			$mfriends = array();
			while ($rt = $db->fetch_array($query)) {
				if ($rt['friendid'] == $winduid || S::inArray($rt['friendid'], $friendUidList))
					continue;
				$mostFriendUids[] = $rt['friendid'];
			}

			$mostFriendUids  = randArray($mostFriendUids, 6);
			if ($mostFriendUids) {
				$query = $db->query('SELECT m.uid,m.username,m.icon as face,m.honor,m.groupid,m.memberid,md.fans FROM pw_members m'. " LEFT JOIN pw_memberdata md ON m.uid = md.uid".' WHERE m.uid IN(' . S::sqlImplode($mostFriendUids) . ')');
				while ($rt = $db->fetch_array($query)) {
					list($rt['face']) = showfacedesign($rt['face'], '1', 's');
					$mostFriends[] = $rt;
				}
			}
		}

		/**=================朋友的朋友End==============**/

		/**=================可能感兴趣的人Start==============**/
		$recommendUsers['recommendUsers'] = array();
		$recommendUsers = $newSpace->getSpaceData(array('recommendUsers'=>12));
		$recommendUsers = $recommendUsers['recommendUsers'];
		/**=================可能感兴趣的人End==============**/

		/**=================当前在线的人数Start==============**/
		require_once (R_P . 'require/functions.php');
		$onlineUsers = GetOnlineUser();
		if ($onlineUsers) {
			$onlineUserkeys = array_keys($onlineUsers);
			$onlineUserkeys = randArray($onlineUserkeys, 6);
			$onlineUids = $onlineUserkeys;
			$query = $db->query("SELECT m.uid,m.username,m.email,m.icon as face,m.regdate,m.honor,m.gender,m.groupid,m.memberid,md.thisvisit,md.fans" . " FROM pw_members m " . " LEFT JOIN pw_memberdata md ON m.uid = md.uid" . " WHERE m.uid IN(" . S::sqlImplode($onlineUserkeys) . ")" . " AND m.uid !=" . S::sqlEscape($winduid));
			while ($rt = $db->fetch_array($query)) {
				list($rt['face']) = showfacedesign($rt['face'], '1', 'm');
				$rt['regdate'] = get_date($rt['regdate']);
				$rt['honor'] = substrs($rt['honor'], 50);
				$onlineMembers[] = $rt;
			}
		}
//		var_export($onlineMembers);exit;
		$attentionUids = array_merge($mostFriendUids,$recommendUids,$onlineUids);
		if ($attentionUids) {
			$attentionSerivce = L::loadClass('Attention', 'friend'); /* @var $attentionSerivce PW_Attention */
			$myAttentionsInfo = $myAttentionUids = array();
			$myAttentionsInfo = $attentionSerivce->getFollowListByFriendids($winduid, $attentionUids);
			foreach ($myAttentionsInfo as $myAttentions) {
				$myAttentionUids[] = $myAttentions['friendid'];
			}

			foreach ($mostFriends as $key=>$mostFriend) {
				in_array($mostFriend['uid'], $myAttentionUids) && $mostFriends[$key]['attention'] = 1;
			}

			foreach ((array)$recommendUsers as $key=>$recommendUser) {
				in_array($recommendUser['uid'], $myAttentionUids) && $recommendUsers[$key]['attention'] = 1;
			}

			foreach ($onlineMembers as $key=>$onlineMember) {
				in_array($onlineMember['uid'], $myAttentionUids) && $onlineMembers[$key]['attention'] = 1;
			}

		}
	/**=================当前在线的人数End==============**/

	}
	$username = $windid;

} elseif ($type == 'invite') {
	if (!$rg_allowregister) ObHeader($basename.'a=friend&type=attention');
	$spaceurl = $db_bbsurl.'/u.php?a=invite&uid='.$winduid;
	/*xufazhang 08-17*/
	$hash = appkey($winduid);
	$spaceurl .= '&hash='.$hash;

	require_once (R_P . 'require/credit.php');
	//* include pwCache::getPath(D_P . 'data/bbscache/inv_config.php');
	//* include pwCache::getPath(D_P . 'data/bbscache/mail_config.php');
	//* include pwCache::getPath(D_P . 'data/bbscache/dbreg.php');
	extract(pwCache::getData(D_P . 'data/bbscache/inv_config.php', false));
	extract(pwCache::getData(D_P . 'data/bbscache/mail_config.php', false));
	extract(pwCache::getData(D_P . 'data/bbscache/dbreg.php', false));	
	
	$thisbase .= 'type=' . $type;
	//$inv_linkcontent = $spaceurl."\r\n".$inv_linkcontent;
	if ($rg_allowregister == 2) {
		$_overtime = $timestamp - (int) $inv_days * 86400;
		$page = S::getGP('page');
		$page = (int)$page;
		$db_perpage = 15;
		(!is_numeric($page) || $page < 1) && $page = 1;
		$limit = S::sqlLimit(($page - 1) * $db_perpage, $db_perpage);
		$rt = $db->get_one("SELECT COUNT(*) AS sum FROM pw_invitecode WHERE uid=" . S::sqlEscape($winduid) . " AND ifused = '0' AND createtime >= " . S::sqlEscape($_overtime));
		$pages = numofpage($rt['sum'], $page, ceil($rt['sum'] / $db_perpage), "$thisbase&");
		$query = $db->query("SELECT * FROM pw_invitecode WHERE uid=" . S::sqlEscape($winduid) . "  AND ifused = '0' AND createtime >= " . S::sqlEscape($_overtime) . " ORDER BY id DESC $limit");
		$invdb = array();
		while ($rt = $db->fetch_array($query)) {
			$rt['overtime'] = get_date(($rt['createtime'] + (int) $inv_days * 86400), 'Y-m-d H:i:s');
//			$rt['invlink'] = $db_bbsurl . '/' . $db_registerfile . '?invcode=' . $rt['invcode'];
			$invdb[] = $rt;
		}
	}
} elseif ($type == 'inviteCode') {
	if ($rg_allowregister != 2) ObHeader($basename.'a=friend&type=attention');
	S::gp(array('step', 't'), 'GP');
	require_once (R_P . 'require/credit.php');
	//* include_once pwCache::getPath(D_P . "data/bbscache/inv_config.php");
	pwCache::getData(D_P . "data/bbscache/inv_config.php");
	$allowinvite = allowcheck($inv_groups, $groupid, $winddb['groups']) ? 1 : 0;
	$usrecredit = ${'db_' . $inv_credit . 'name'};
	$creditto = array('rvrc' => $userrvrc, 'money' => $winddb['money'], 'credit' => $winddb['credit'],
		'currency' => $winddb['currency']);
	if (empty($step)) {
		$_sql = "";
		$_overtime = (int)($timestamp -  $inv_days * 86400);
		if ($t == 'register') {
			$_sql = " AND ifused = '1' ";
		} elseif ($t == 'notused') {
			$_sql = " AND ifused = '0' AND createtime >= " . S::sqlEscape($_overtime);
		} elseif ($t == 'overtime') {
			$_sql = " AND ifused = '0' AND createtime < " . S::sqlEscape($_overtime);
		}

		$page = S::getGP('page');
		$page = (int)$page;
		$db_perpage = 10;
		(!is_numeric($page) || $page < 1) && $page = 1;
		$rt = $db->get_one("SELECT COUNT(*) AS sum FROM pw_invitecode WHERE uid=" . S::sqlEscape($winduid) . " $_sql ");
		$pageCount = ceil($rt['sum'] / $db_perpage);
		$page = $page < 0 ? 1 : $page > $pageCount ? $pageCount : $page;
		$pages = numofpage($rt['sum'], $page, $pageCount, $thisbase . "type=inviteCode&t=$t&");
		$limit = S::sqlLimit(($page - 1) * $db_perpage, $db_perpage);
		$query = $db->query("SELECT * FROM pw_invitecode WHERE uid=" . S::sqlEscape($winduid) . " $_sql  ORDER BY id DESC $limit");
		$invdb = array();
		while ($rt = $db->fetch_array($query)) {
			$rt['used'] = '';
			if ($rt['ifused'] =='0' && $rt['createtime'] < $_overtime){
				$rt['used'] = "<span class='gray'>已过期</span>";
			} elseif ($rt['ifused'] == '0' && $rt['createtime'] >= $_overtime){
				$rt['used'] = "<span class='s3'  >未使用</span>";
			} elseif ($rt['ifused'] == '1'){
				$rt['used'] = "<span class='s2'  >已注册</span>";
			}
			$rt['overtime'] = get_date(($rt['createtime'] + (int) $inv_days * 86400), 'Y-m-d H:i:s');
			$rt['usetime'] = $rt['usetime'] ? get_date($rt['usetime'], 'Y-m-d H:i:s') : '';
			$invdb[] = $rt;
		}
	}
} elseif ($type == 'viewer') {
	$username = $windid;
	$userdb = $db->get_one("SELECT m.uid,m.username,m.email,m.groupid,m.memberid,m.icon,ud.index_privacy,ud.profile_privacy,ud.info_privacy,ud.credit_privacy,ud.owrite_privacy,ud.msgboard_privacy,ud.visits,ud.whovisit FROM pw_members m LEFT JOIN pw_ouserdata ud ON m.uid=ud.uid WHERE m.uid=" . S::sqlEscape($u));
	$whovisit = unserialize($userdb['whovisit']);
	is_array($whovisit) || $whovisit = array();
	$visituids = array_keys($whovisit);
	if ($visituids) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		foreach ($userService->getUsersWithMemberDataByUserIds($visituids) as $rt) {
			list($rt['face']) = showfacedesign($rt['icon'], 1, 'm');
			$whovisit[$rt['uid']] = get_date($whovisit[$rt['uid']], ($whovisit[$rt['uid']] < $tdtime ? 'm-d' : 'H:i'));
			$whovisit[$rt['uid']] = array('visittime' => $whovisit[$rt['uid']]) + $rt;
		}
	}
} elseif ($type == 'birthday') {
	$birthday = $winddb['bday'];
	$customFieldService = L::loadClass('customerfield','user');
	$fieldInfo = $customFieldService->getFieldByFieldName('bday');
	switch ($fieldInfo['category']) {
		case 'basic' :
			$birthdayLink = 'base';
			break;
		case 'contact' :
			$birthdayLink = 'link';
			break;
		case 'education' :
			$birthdayLink = 'education';
			break;
		case 'other': 
			$birthdayLink = 'other';
			break;
	}
	$friendsService = L::loadClass('Friend', 'friend'); /* @var $friendsService PW_Friend */
	$friendsBirthday = $friendsService->findUserFriendsBirthdayInPage($winduid, 20, 1, 25);
}

if ($space == 1) {

	require_once (R_P . 'require/credit.php');
	list($userdb, $ismyfriend, $friendcheck, $usericon, $usercredit, $totalcredit, $appcount, $p_list) = getAppleftinfo($u);

} else {

}

require_once (uTemplate::printEot('friend_index'));
pwOutPut();

/**
 * 数组里随机取几个
 *
 * @param array $dealArray
 * @param int $num
 * return array()
 */
function randArray($dealArray, $num){
	if (!is_array($dealArray)) return "";
	if ($num >= count($dealArray)) return $dealArray;
	if ($num <= 0) return "";
	$temp = array_rand($dealArray,$num);
	$result = array();
	foreach ($temp as $value) {
		$result[] = $dealArray[$value];
	}
	return $result;
}
/**
 * 获取存放会员好友的ID数组
 *
 * @param array $dealArray
 * @param int $num
 * return array()
 */
function getFriendsIdArr($winduid) {
	$friendsAll = $friendsAllUids = array();
	$friendsService = L::loadClass('Friend', 'friend'); /* @var $friendsService PW_Friend */
	$friendsAll = $friendsService->getFriendsByUid($winduid);
	foreach ($friendsAll as $key => $friend) {
		$friendsAllUids[] = $friend['friendid'];
	}
	return $friendsAllUids;
}
?>