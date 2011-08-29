<?php
!defined('P_W') && exit('Forbidden');

PostCheck();
S::gp(array('touid','reload'), 'GP', 2);
if ($touid == $winduid) {
	Showmsg('friend_self_add_error');
}
$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
$friend = $userService->get($touid); //uid,username,userstatus,icon
if (!$friend) {
	$errorname = '';
	Showmsg('user_not_exists');
}

$attentionService = L::loadClass('Attention', 'friend'); /* @var $attentionService PW_Attention */
if ($attentionService->isInBlackList($touid, $winduid)) {
	Showmsg('对方已设置隐私，您无法加为好友!');
}

$rs = $db->get_one("SELECT uid,status FROM pw_friends WHERE uid=" . S::sqlEscape($winduid) . " AND friendid=" . S::sqlEscape($friend['uid']));
if ($rs) {
	if ($rs['status'] == '1') {
		Showmsg('friend_status_check');
	} elseif ($rs['status'] == '0') {
		Showmsg('friend_already_exists');
	}
}
$friendcheck = getstatus($friend['userstatus'], PW_USERSTATUS_CFGFRIEND, 3);

if ($friendcheck == 2) {
	Showmsg('friend_not_add');
}
if (empty($_POST['step'])) {
	
	if ($friendcheck == 0 || $friendcheck == 1) {
		
		require_once (R_P . 'require/showimg.php');
		list($faceurl) = showfacedesign($friend['icon'], '1', 's');
		$query = $db->query("SELECT ftid,name FROM pw_friendtype WHERE uid=" . S::sqlEscape($winduid) . " ORDER BY ftid");
		$types = array();
		while ($rt = $db->fetch_array($query)) {
			$types[$rt['ftid']] = $rt['name'];
		}
		require_once PrintEot('ajax');
		ajax_footer();
	}
} else {
	S::gp(array(
		'friendtype'
	));
	
	if ($friendtype > 0) {
		$checkftid = $db->get_value("SELECT ftid FROM pw_friendtype WHERE uid=" . S::sqlEscape($winduid) . " AND ftid=" . S::sqlEscape($friendtype));
		if (empty($checkftid)) Showmsg('friend_type_not_exists');
	}
	if (!$friendcheck) {

		$friendService = L::loadClass('Friend', 'friend'); /* @var $friendService PW_Friend */
		$friendService->addFriend($winduid, $friend['uid'], '', $friendtype);
		$result = $friendService->addFriend($friend['uid'], $winduid);
		
	// defend start	
		CloudWind::yunUserDefend('addfriend', $winduid, $windid, $timestamp, 0, ($result === true ? 101 : 102),(!S::IsBool($result) ? $reason : ''),'','',array('uniqueid'=>$winduid.'-'.$friend['uid']));
	// defend end

		$userCacheService = L::loadClass('UserCache', 'user'); /* @var $userCacheService PW_UserCache */
		$userCacheService->delete($winduid, 'recommendUsers');
		M::sendNotice(
			array($friend['username']),
			array(
				'title' => getLangInfo('writemsg','friend_add_title_1',array(
					'username'=>$windid
				)),
				'content' => getLangInfo('writemsg','friend_add_content_1',array(
					'uid'=>$winduid,
					'username'=>$windid
				)),
			)
		);
		
		//job sign
		initJob($winduid, "doAddFriend", array(
			'user' => $friend['username']
		));
		
		if (empty($reload)) {
			Showmsg('friend_update_success');
		} else {
			Showmsg('ajax_friend_update_success');
		}
	
	} elseif ($friendcheck == 1) {
		S::gp(array('checkmsg'), 'P');
		if (strlen($checkmsg) > 255) {
			$_strmaxlen = 255;
			Showmsg('string_limit');
		}

		//$db->query("DELETE FROM pw_attention WHERE uid=" . S::sqlEscape($winduid) . " AND touid=" . S::sqlEscape($touid));
		//addSingleFriend(false, $winduid, $friend['uid'], $timestamp, 1, $friendtype, $checkmsg); 
		/*xufazhang 2010-07-22 start*/
		//$friendService = L::loadClass('Friend', 'friend'); // @var $friendService PW_Friend 
		//$friendService->addFriend($winduid, $friend['uid'], $checkmsg, $friendtype, false);
		/*xufazhang 2010-07-22 end*/
		M::sendRequest(
			$winduid,
			array($friend['username']),
			array(
				'create_uid' => $winduid,
				'create_username' => $windid,
				'title' => getLangInfo('writemsg','friend_add_title_2',array('username'=>$windid)),
				'content' => getLangInfo('writemsg','friend_add_content_2',array(
					'uid' => $winduid,
					'username' => $windid,
					'msg' => stripslashes($checkmsg))
				),
			),
			'request_friend',
			'request_friend'
		);
		
		//job sign
		initJob($winduid, "doAddFriend", array(
			'user' => $friend['username']
		));
		
		if (empty($reload)) {
			Showmsg('friend_add_check');
		} else {
			Showmsg('ajax_friend_add_check');			
		}
		
	} else {
		Showmsg('undefined_action');
	}
}
