<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$admin_file?adminjob=sendmsg";

if (empty($action)) {
	
	include PrintEot('sendmsg');
	exit();

} elseif ($action == "send") {

	$messageServer = L::loadClass('message', 'message');
	$pwServer['REQUEST_METHOD'] != 'POST' && PostCheck($verify);
	S::gp(array('step', 'by', 'sendto', 'touser', 'subject', 'atc_content'));

	if ($by == 1) {
		
		!$sendto && adminmsg('mes_send_name_long');
		empty($step) && $step = 1;
		is_array($sendto) || $sendto = explode(',', $sendto);

		if ($step == 1) {
			if (empty($subject) || empty($atc_content)) {
				adminmsg('sendmsg_empty');
			}
			$sendGroup = $sendto;
			if (in_array('-1', $sendto)) {
				$query = $db->query("SELECT gid FROM pw_usergroups WHERE gptype='member'");
				while ($rs = $db->fetch_array($query)) {
					$sendGroup[] = $rs['gid'];
				}
				$sendGroup = array_diff($sendGroup, array('-1'));
			}
			$subject = S::escapeChar($subject);
			$sendmessage = S::escapeChar($atc_content);
			$messageInfo = array('create_uid' => $winduid, 'create_username' => $windid, 'title' => $subject, 'content' => $sendmessage);
			$messageServer->createMessageTasks($sendGroup, $messageInfo);
			$count = $db->get_value("SELECT COUNT(*) AS sum FROM pw_members WHERE groupid IN(" . S::sqlImplode($sendto) . ')');
		} else {
			S::gp(array('count'));
		}
		$perpage = 10000;
		$havesend = $step * $perpage;
		$tmpArray = $sendto + array(0);
		$db->query("CREATE TEMPORARY TABLE tmp_datastate SELECT uid FROM pw_members WHERE groupid IN(" . S::sqlImplode($tmpArray) . ')'. pwLimit(($step-1)*$perpage, $perpage));
		
		$db->update("INSERT INTO pw_ms_configs (uid) SELECT a.uid FROM tmp_datastate a LEFT JOIN pw_ms_configs b ON a.uid=b.uid WHERE b.uid IS NULL");
		$db->update("UPDATE tmp_datastate a LEFT JOIN pw_members m ON a.uid=m.uid LEFT JOIN pw_ms_configs c ON a.uid=c.uid SET m.newpm=m.newpm+1,c.notice_num=c.notice_num+1");
		
		//* 清除pw_members缓存 start 
		$_num = $db->get_value("SELECT count(*) FROM tmp_datastate");
		if ($_num > 1500){
			$_cacheService = L::loadClass('cacheservice', 'utility');
			$_cacheService->flush(PW_CACHE_MEMCACHE);				
		}else {
			$_query = $db->query("SELECT uid FROM tmp_datastate");
			$_uids = array();
			while ($rt = $db->fetch_array($_query)){
			 	$_uids[] = $rt['uid'];
			}		
			Perf::gatherInfo('changeMembersWithUserIds', array('uid'=>$_uids));		
		}
		//* 清除pw_members缓存 end
		
		if ($havesend < $count) {
			$step++;
			$j_url = "$basename&action=$action&step=$step&sendto=" . implode(',', $sendto) . "&by=$by&count=$count";
			adminmsg("sendmsg_step", EncodeUrl($j_url), 1);
		}
		adminmsg('operate_success');
	
	} elseif ($by == 2) {
		
		$cache_file = D_P . "data/bbscache/" . substr(md5($admin_pwd), 10, 10) . ".txt";
		if (!$step) {
			pwCache::setData($cache_file, $atc_content);
		} else {
			//* $atc_content = readover($cache_file);
			$atc_content = pwCache::getData($cache_file, false, true);
		}
		if (empty($subject) || empty($atc_content)) {
			adminmsg('sendmsg_empty');
		}
		$subject = S::escapeChar($subject);
		$sendmessage = S::escapeChar($atc_content);
		$percount = 100;
		empty($step) && $step = 1;
		
		//
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$db_onlinetime = ($db_onlinetime>0) ? $db_onlinetime : 1200;
		
		$onlineuser = array();
		if($onlineusers = $userService->findOnlineUsers(($timestamp-$db_onlinetime))){
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
	} elseif ($by == 3) {	//增加按用户发送@modify panjl@2010-11-3

		!$touser && adminmsg('operate_error');
		if (empty($subject) || empty($atc_content)) {
			adminmsg('sendmsg_empty');
		}
		$subject = S::escapeChar($subject);
		$sendmessage = S::escapeChar($atc_content);
		$userService = L::loadClass('UserService', 'user');
		$to_a_temp = explode(',', $touser);		
		$to_a = array();
		$to_a_err = array();
		foreach( $to_a_temp as $value ){
			$flag = $userService->isExistByUserName($value);
			if( true === $flag ){
				array_push($to_a, $value);
			}else{
				array_push($to_a_err, $value);
			}
		}
		$to_a && $sqlwhere = "username IN(" . S::sqlImplode($to_a) . ")";
		$count = count($to_a);
		if($sqlwhere){
			$query = $db->query("SELECT uid FROM pw_members WHERE $sqlwhere");
			$uids = array();
			while ($rt = $db->fetch_array($query)) {
				$uids[] = $rt['uid'];
			}
		}
		if ($uids) {
			$messageInfo = array('create_uid'=>$winduid,'create_username'=>$windid,'title'=>$subject,'content'=>$sendmessage);
			$messageServer->sendOnlineMessages($uids,$messageInfo);
		}
		if(0 !== count($to_a_err)){
			$existUsers = S::sqlImplode($to_a_err);
			adminmsg('sendmsg_success_part');
		}
		adminmsg('sendmsg_success');

	} else {
		adminmsg('operate_error');
	}
}
?>