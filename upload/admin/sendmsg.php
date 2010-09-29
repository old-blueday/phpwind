<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$admin_file?adminjob=sendmsg";

if (empty($action)) {
	
	include PrintEot('sendmsg');
	exit();

} elseif ($action == "send") {
	$messageServer = L::loadClass('message', 'message');
	$pwServer['REQUEST_METHOD'] != 'POST' && PostCheck($verify);
	InitGP(array('step', 'by', 'sendto', 'subject', 'atc_content'));
	
	if ($by == 1) {
		
		!$sendto && adminmsg('operate_error');
		if (empty($subject) || empty($atc_content)) {
			adminmsg('sendmsg_empty');
		}
		if(in_array('-1',$sendto)){
			$query = $db->query("SELECT gid FROM pw_usergroups WHERE gptype='member'");
			while($rs = $db->fetch_array($query)){
				$sendto[] = $rs['gid'];
			}
			$sendto = array_diff($sendto,array('-1'));
		}
		$subject = Char_cv($subject);
		$sendmessage = Char_cv($atc_content);
//		$sendto = implode(",", $sendto);
		$messageInfo = array('create_uid'=>$winduid,'create_username'=>$windid,'title'=>$subject,'content'=>$sendmessage);
		$messageServer->createMessageTasks($sendto,$messageInfo);
		
		adminmsg('operate_success');
	
	} elseif ($by == 2) {
		
		$cache_file = D_P . "data/bbscache/" . substr(md5($admin_pwd), 10, 10) . ".txt";
		if (!$step) {
			writeover($cache_file, $atc_content);
		} else {
			$atc_content = readover($cache_file);
		}
		if (empty($subject) || empty($atc_content)) {
			adminmsg('sendmsg_empty');
		}
		$subject = Char_cv($subject);
		$sendmessage = Char_cv($atc_content);
		$percount = 100;
		empty($step) && $step = 1;
		
		
		//
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$db_onlinetime = ($db_onlinetime>0) ? $db_onlinetime : 20;
		
		$onlineuser = array();
		if($onlineusers = $userService->findOnlineUsers(($timestamp-$db_onlinetime*60))){
			foreach($onlineusers as $user){
				$onlineuser[] = $user['uid'];
			}
		}
//		$count = count($onlineuser);
		$uids = array();
//		$start = ($step - 1) * $percount;
		$uids = array_splice($onlineuser, $start, $percount);
		if ($uids) {
			$messageInfo = array('create_uid'=>$winduid,'create_username'=>$windid,'title'=>$subject,'content'=>$sendmessage);
			$messageServer->sendOnlineMessages($uids,$messageInfo);
		}
		adminmsg('operate_success');
		/*$havesend = $step * $percount;
		if ($count > ($step * $percount)) {
			$step++;
			$j_url = "$basename&action=$action&step=$step&subject=" . rawurlencode($subject) . "&by=$by";
			adminmsg("sendmsg_step", EncodeUrl($j_url), 1);
		} else {
			P_unlink($cache_file);
			adminmsg('sendmsg_success');
		}*/
	} else {
		adminmsg('operate_error');
	}
}
?>