<?php
!function_exists('readover') && exit('Forbidden');
require_once(R_P.'require/functions.php');
PwNewDB();
$friendServer = L::loadClass('Friend', 'friend');

if ($hash == appkey($o_u,$app) && $winduid && ($o_u !== $winduid)) {
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$ckuser = $userService->get($o_u);
	$iffriend = $friendServer->getFriendByUidAndFriendid($winduid,$o_u);
	if ($ckuser && empty($iffriend)) {
		$friendcheck = getstatus($ckuser['userstatus'], PW_USERSTATUS_CFGFRIEND, 3);
		if (!$friendcheck) {
			$db->query("DELETE FROM pw_attention WHERE uid=" . S::sqlEscape($winduid) . " AND friendid=".S::sqlEscape($o_u));
		
			addSingleFriend(true, $winduid,$o_u, $timestamp, 0);
			addSingleFriend(true, $o_u, $winduid, $timestamp, 0);
			
			M::sendNotice(
				array($ckuser['username']),
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

	
		} elseif ($friendcheck == 1) {
			
			$db->query("DELETE FROM pw_attention WHERE uid=" . S::sqlEscape($winduid) . " AND friendid=".S::sqlEscape($touid));
			addSingleFriend(false, $winduid, $o_u, $timestamp, 1);
			M::sendRequest(
				$winduid,
				array($ckuser['username']),
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
		}
		Cookie('o_invite','');
	}
}
function addSingleFriend($updatemem, $winduid, $frienduid, $timestamp, $status, $friendtype = 0, $checkmsg = '') {
	global $db;
	
	$pwSQL = S::sqlSingle(array(
		'uid' => $winduid,
		'friendid' => $frienduid,
		'joindate' => $timestamp,
		'status' => $status,
		'descrip' => $checkmsg,
		'ftid' => $friendtype
	));
	
	$attentionService = L::loadClass('Attention', 'friend'); /* @var $attentionService PW_Attention */
	if ($isAttention = $attentionService->isFollow($winduid, $frienduid)) {
		$db->update("UPDATE pw_friends SET status = 0 WHERE uid=".S::sqlEscape($winduid)." AND friendid=".S::sqlEscape($frienduid));
	} else {
		if($winduid != $frienduid)$db->update("INSERT INTO pw_friends SET $pwSQL");
		$attentionService->addFollow($winduid, $frienduid,'','addFriend');
	}
	
	if ($updatemem) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->updateByIncrement($winduid, array(), array('f_num' => 1));
	}
}
?>