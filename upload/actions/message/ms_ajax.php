<?php
!defined('P_W') && exit('Forbidden');
S::gp(array('action'));
define('FRIEND_SEPARATER', '#%');
!in_array($action, array('friend', 'mark', 'del', 'postReply', 'overlook', 'post', 'agree', 'markgroup', 'shield',
	'unshield','open','close','replay')) && ajaxExport("非法操作请返回");
if (in_array($action, array('friend', 'agree','overlook'))) {
	L::loadClass('friend', 'friend', false);
	$friendObj = new PW_Friend(FRIEND_SEPARATER);
}
if(!$winduid) ajaxExport(array('bool' => $bool, 'message' => '你还没有登录'));
if ('friend' == $action) {
	/*
	S::gp(array('gname'));
	if ($gname == '-2') {
		 $friend = $friendObj->getFriends($winduid);
		 $group	 = $friendObj->getFriendColonys($winduid);
		 $json	 = array('friend'=>$friend,'group'=>$group);
		 ajaxExport($json);
	} elseif ($gname == '0') {
		 ajaxExport($friendObj->getFriendsByColony($winduid, 0));
	} elseif ($gname) {
		 ajaxExport($friendObj->getFriendsByColony($winduid, $gname, 'name'));
	} else {
		$friends = array();
		$attentionService = L::loadClass('attention','friend');
		$attentionList = $attentionService->getUidsInFollowList($winduid);
		if(S::isArray($attentionList)) {
			$userService = L::loadClass('userservice','user');
			$friends = $userService->getUserNamesByUserIds($attentionList);
		}
		ajaxExport($friends);
	}
	*/
	$attention = array();
	$attentionService = L::loadClass('attention','friend');
	$attentionList = $attentionService->getUidsInFollowList($winduid);
	if(S::isArray($attentionList)) {
		$userService = L::loadClass('userservice','user');
		$attention = $userService->getUserNamesByUserIds($attentionList);
	}
	$friend = $friendObj->getFriends($winduid);
	$json	 = array('friend'=>$friend,'attention'=>implode(FRIEND_SEPARATER,$attention));
	ajaxExport($json);
	
} elseif ('mark' == $action) {
	S::gp(array('rids'), 'GP');
	empty($rids) && ajaxExport("非法操作请返回");
	!is_array($rids) && $rids = explode(',', trim($rids, ','));
	if (!($messageServer->markMessages($winduid, $rids))) {
		ajaxExport("标记已读操作失败");
	}
	ajaxExport("标记已读操作成功!");
} elseif ('markgroup' == $action) {
	S::gp(array('rids'), 'GP');
	empty($rids) && ajaxExport("非法操作请返回");
	!is_array($rids) && $rids = explode(',', trim($rids, ','));
	if (!($messageServer->markGroupMessages($winduid, $rids))) {
		ajaxExport("标记已读操作失败");
	}
	ajaxExport("标记已读操作成功!");
} elseif ('del' == $action) {
	S::gp(array('rids'), 'GP');
	empty($rids) && ajaxExport("非法操作请返回");
	!is_array($rids) && $rids = explode(',', trim($rids, ','));
	if (!($messageServer->deleteMessages($winduid, $rids))) {
		ajaxExport("删除操作失败");
	}
	ajaxExport("删除操作成功!");
} elseif ('postReply' == $action) {
	S::gp(array('parentMid', 'atc_content','rid','gdcode','flashatt','tid','ifMessagePostReply'), 'GP');
	if(!$_G['allowmessege']) ajaxExport(array('bool' => false, 'message' => '你所在的用户组不能发送消息'));
	if(($db_gdcheck & 8) && false === GdConfirm($gdcode,true)){
		ajaxExport(array('bool' => false, 'message' => '你的验证码不正确或过期'));
	}
	empty($parentMid) && ajaxExport(array('bool' => false, 'message' => '非法操作请返回'));
	empty($atc_content) && $atc_content !== '0' && ajaxExport(array('bool' => false, 'message' => '回复内容不能为空'));
	$atc_content = trim(strip_tags($atc_content));
	$filterUtil = L::loadClass('filterutil', 'filter');
	$atc_content = $filterUtil->convert($atc_content);
	$messageInfo = array('create_uid' => $winduid, 'create_username' => $windid, 'title' => $windid,
		'content' => $atc_content);
	if (!($message = $messageServer->sendReply($winduid, $rid, $parentMid, $messageInfo))) {
		ajaxExport(array('bool' => false, 'message' => '回复失败'));
	} else {
		L::loadClass('messageupload', 'upload', false);
		if ($db_allowupload && $_G['allowupload'] && (PwUpload::getUploadNum() || $flashatt)) {
			S::gp(array('savetoalbum', 'albumid'), 'P', 2);
			$messageAtt = new messageAtt($parentMid,$rid);
			$messageAtt->setFlashAtt($flashatt, $savetoalbum, $albumid);
			$attachData = PwUpload::upload($messageAtt);
		}
	}
	if ($ifMessagePostReply) {
		$pingService = L::loadClass('ping', 'forum');
		if (($pingService->checkReplyRight($tid)) !== true) {
			ajaxExport(array('bool' => false, 'message' => '您不能对帖子进行回复'));
		}
		$atc_content = $atc_content."\r\n\r\n[size=2][color=#a5a5a5]内容来自[短消息][/color] [/size]";
		if ($result = $pingService->addPost($tid, $atc_content) !== true) {
			ajaxExport(array('bool' => false, 'message' => $result));
		}
	}
	ajaxExport(array('bool' => true, 'message' => '消息已发送'));
} elseif ($action == 'overlook') {
	/* 忽略请求 */
	S::gp(array('rids', 'typeid','fuid'), 'GP');
	empty($rids) && ajaxExport("非法操作请返回");
	!is_array($rids) && $rids = explode(',', trim($rids, ','));
	$ignoreType = $messageServer->getReverseConst($typeid);
	switch($ignoreType){
		case 'request_friend' : $msg = getLangInfo('message','friend_add_ignore');
								$friendObj->deleteMeFromFriends($winduid,$fuid);
								break;
		case 'request_group' : $msg = getLangInfo('message','colony_add_ignore');break;
		case 'request_app' : $msg = getLangInfo('message','app_add_ignore');break;
		default:$msg = getLangInfo('message','request_ignore');break;
	}
	$messageServer->overlookRequests($winduid, $rids);
	ajaxExport($msg);
} elseif ($action == 'post') {
	S::gp(array('_usernames', 'atc_title', 'atc_content','flashatt','gdcode'));
	$usernames = $_usernames;/*specia;*/
	$atc_title = trim($atc_title);
	$atc_content = trim($atc_content);
	if(($db_gdcheck & 8) && false === GdConfirm($gdcode,true)){
		ajaxExport(array('bool' => false, 'message' => '你的验证码不正确或过期'));
	}
	if(!$_G['allowmessege']){
		ajaxExport(array('bool' => false, 'message' => '你所在的用户组不能发送消息'));
	}
	if ("" == $usernames) {
		ajaxExport(array('bool' => false, 'message' => '收件人不能为空'));
	}
	if (in_array($windid,$usernames)) {
		ajaxExport(array('bool' => false, 'message' => '你不能给自己发消息'));
	}
	if (count($usernames) > 1 && intval($_G['multiopen']) < 1 ) {
		ajaxExport(array('bool' => false, 'message' => '你不能发送多人消息'));
	}
	if($_FILES['attachment']){
		unset($_FILES['attachment']);
	}
	if( count($_FILES) > $db_attachnum ){
		ajaxExport(array('bool' => false, 'message' => '最多可上传附件'.$db_attachnum.'个'));
	}
	$usernames = is_array($usernames) ? $usernames : explode(",", $usernames);
	if (in_array($windid, array($usernames))) {
		unset($usernames[$windid]);
	}
	if(!($messageServer->checkUserMessageLevle('sms',1))){
		ajaxExport(array('bool' => false, 'message' => '你已超过每日发送消息数或你的消息总数已满'));
	}
	list($bool,$message) = $messageServer->checkReceiver($usernames);
	if(!$bool){
		ajaxExport(array('bool' => $bool, 'message' => $message));
	}
	if ("" == $atc_title) {
		ajaxExport(array('bool' => false, 'message' => '标题不能为空'));
	}
	if (200 < strlen($atc_title)) {
		ajaxExport(array('bool' => false, 'message' => '标题不能超过限度'));
	}
	if ("" == $atc_content) {
		ajaxExport(array('bool' => false, 'message' => '内容不能为空'));
	}
	if( isset($_G['messagecontentsize']) && $_G['messagecontentsize'] > 0 && strlen($atc_content) > $_G['messagecontentsize']){
		ajaxExport(array('bool' => false, 'message' => '内容超过限定长度'.$_G['messagecontentsize'].'字节'));
	}
	$filterUtil = L::loadClass('filterutil', 'filter');
	$atc_content = $filterUtil->convert($atc_content);
	$atc_title   = $filterUtil->convert($atc_title);
	$messageInfo = array('create_uid' => $winduid, 'create_username' => $windid, 'title' => $atc_title,
		'content' => $atc_content);
	$messageService = L::loadClass("message", 'message');
	if (($messageId = $messageService->sendMessage($winduid, $usernames, $messageInfo))) {
		initJob($winduid,'doSendMessage',array('user'=>$usernames));
		define('AJAX',1);
		L::loadClass('messageupload', 'upload', false);
		if ($db_allowupload && $_G['allowupload'] && (PwUpload::getUploadNum() || $flashatt)) {
			S::gp(array('savetoalbum', 'albumid'), 'P', 2);
			$messageAtt = new messageAtt($messageId);
			$messageAtt->setFlashAtt($flashatt, $savetoalbum, $albumid);
			PwUpload::upload($messageAtt);
		}
	}
	ajaxExport(array('bool' => true, 'message' => '消息已发送'));
} elseif ('agree' == $action) {
	/* 请求同意  */
	S::gp(array('rids', 'typeid', 'fid','cyid','check'), 'GP');
	empty($rids) && ajaxExport("非法操作请返回");
	!is_array($rids) && $rids = explode(',', trim($rids, ','));
	$fid && !is_array($fid) && $fid = array($fid);
	$agreeType = $messageServer->getReverseConst($typeid);
	switch ($agreeType) {
		case 'request_friend' :
			$return = $friendObj->argeeAddedFriends($winduid, $fid);
			/*xufazhang 2010-07-22 */
			$friendService = L::loadClass('Friend', 'friend'); /* @var $friendService PW_Friend */
			foreach($fid as $value){
			$friendService->addFriend($winduid, $value);
			$friendService->addFriend($value, $winduid);
			}
			$fid = $fid[0];
			$msg = getLangInfo('message',$return);
			break;
		case 'request_group' :
			$return = $check == 1 ? $friendObj->checkJoinColony($cyid,$fid):$friendObj->agreeJoinColony($cyid,$winduid, $windid);
			if($return == 'colony_check_fail'){
				/*无权限审核，直接将该消息删除*/
				$messageServer->deleteMessages($winduid,$rids);
			}
			$msg = getLangInfo('message',$return);
			break;
		case 'request_app' :
			$return = $friendObj->agreeWithApp(0);
			$msg = getLangInfo('message',$return);
			break;
		default :
			break;
	}
	if (in_array($return,array('app_add_success','colony_joinsuccess','friend_add_success','colony_check_success'))) {
		$messageServer->agreeRequests($winduid, $rids);
	}else{
		$messageServer->updateRequest(array('status'=>0),$winduid, $rids[0]);
	}
	ajaxExport($msg);
} elseif ('shield' == $action) {
	/* 屏蔽多人消息 */
	S::gp(array('rid', 'mid'), 'GP');
	(empty($rid) || empty($mid)) && ajaxExport("非法操作请返回");
	if (!($messageServer->shieldGroupMessage($winduid, $rid, $mid))) {
		ajaxExport("屏蔽多人消息失败");
	}
	ajaxExport("屏蔽操作成功!");
} elseif ('unshield' == $action) {
	/* 恢复多人消息 */
	S::gp(array('rid', 'mid'), 'GP');
	(empty($rid) || empty($mid)) && ajaxExport("非法操作请返回");
	if (!($messageServer->recoverGroupMessage($winduid, $rid, $mid))) {
		ajaxExport("恢复多人消息失败");
	}
	ajaxExport("恢复操作成功!");
} elseif ('close' == $action) {
	/* 拒收群组消息 */
	S::gp(array('gid', 'mid'), 'GP');
	empty($gid) && ajaxExport("群组已删除");
	empty($mid) && ajaxExport("非法操作请返回");
	if (!($messageServer->closeGroupMessage($winduid, $gid, $mid))) {
		ajaxExport("拒收群组消息失败");
	}
	ajaxExport("拒收群组消息成功!");
} elseif ('open' == $action) {
	/* 启用群组消息 */
	S::gp(array('gid', 'mid'), 'GP');
	(empty($gid) || empty($mid)) && ajaxExport("非法操作请返回");
	if (!($messageServer->openGroupMessage($winduid, $gid, $mid))) {
		ajaxExport("启用群组消息失败");
	}
	ajaxExport("启用群组消息成功!");
} elseif ('replay' == $action){


}

?>