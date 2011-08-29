<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$admin_file?adminjob=present";

require_once(R_P."require/credit.php");

if (empty($action)) {

	include PrintEot('present');exit;

} elseif ($action == "send") {
	$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
	S::gp(array('step','by','sendto','touser','subject','atc_content','present','percount','count'));

	$cache_file = D_P."data/bbscache/".substr($admin_pwd,10,10).".txt";
	if (!$step) {
		pwCache::setData($cache_file,$atc_content);
	} else {
		//* $atc_content = readover($cache_file);
		$atc_content = pwCache::getData($cache_file, false, true);
	}
	if (empty($subject) || empty($atc_content)) {
		adminmsg('sendmsg_empty');
	}
	$sendmessage = $atc_content;
	!$percount && $percount = 100;
	empty($step) && $step = 1;
	$start = ($step-1)*$percount;
	//$limit = S::sqlLimit($start,$percount);

	$creditlist = '';
	$sendmessage .= '<br /><br /><b>'.getLangInfo('other','affect').'</b>';

	foreach ($present as $key => $val) {
		if (empty($val)) continue;
		if (is_numeric($val)) {
			$creditlist .= "&present[$key]=$val";
			$sendmessage .= $credit->cType[$key]."<font color=#FA891B>(+$val)</font> ";
		} else {
			adminmsg('credit_isnum');
		}
	}
	if ($by == 0) {
		!$sendto && adminmsg('operate_error');
		is_array($sendto) && $sendto = implode(",",$sendto);
		$sendto && $sqlwhere = "groupid IN('".str_replace(",","','",$sendto)."')";
		if ($step == 1 && $sqlwhere) {
			$rs = $db->get_one("SELECT count(*) AS count FROM pw_members WHERE $sqlwhere");
			$count = $rs['count'];
			$sendto = explode(',',$sendto);
			if(in_array('-1',$sendto)){
				$query = $db->query("SELECT gid FROM pw_usergroups WHERE gptype='member'");
				while($rs = $db->fetch_array($query)){
					$sendto[] = $rs['gid'];
				}
				$sendto = array_diff($sendto,array('-1'));
			}
			$messageServer = L::loadClass("message", 'message');
			$messageServer->createMessageTasks($sendto,array('create_uid'=>$rightset['uid'],'create_username'=>$admin_name,'title'=>$subject,'content'=>$sendmessage));
		}
	} elseif ($by == 1) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$uids = array();
		if($onlineuser = $userService->findOnlineUsers(($timestamp-$db_onlinetime))){
			foreach($onlineuser as $user){
				$uids[] = $user['uid'];
			}
		}
		$uids = S::sqlImplode($uids);
		$uids && $sqlwhere = "uid IN($uids)";
		$count = count($onlineuser);
	} elseif ($by == 2) {
		!$touser && adminmsg('operate_error');
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
		/* //修改用户名输入有错误时发送失败@modify panjl@2010-11-3
		 if(0 !== count($to_a_err)){
			$existUsers = S::sqlImplode($to_a_err);
			adminmsg('sendmsg_success_part');
			}
			*/
	} else {
		adminmsg('operate_error');
	}
	$pruids = $usernames = $u_a = array();
	if($sqlwhere){
		$query = $db->query("SELECT uid,username,email,newpm FROM pw_members WHERE $sqlwhere");
		while ($rt = $db->fetch_array($query)) {
			$u_a[] = $rt['uid'];

			if ($by > 0) {
				$usernames[] = $rt['username'];
			} else {
				$pruids[] = $rt['uid'];
			}
			$credit->addLog('other_present',$present,array(
				'uid'		=> $rt['uid'],
				'username'	=> $rt['username'],
				'ip'		=> $onlineip,
				'admin'		=> $admin_name
			));
		}
	}
	//step by step
	$usernames && $usernames = array_slice($usernames, $offset,$percount);
	$pruids && $pruids = array_slice($pruids, $offset,$percount);
	$u_a && $u_a = array_slice($u_a, $offset,$percount);
	
	if ($usernames) {
		M::sendNotice($usernames,array('title' => $subject,'content' => $sendmessage));
	}
	if ($pruids) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->updatesByIncrement($pruids, array('newpm'=>1));
	}
	if ($u_a) {
		$credit->setus($u_a,$present);
	}
	//$havesend = $step*$percount;
	if ($count > $step*$percount) {
		$step++;
		$j_url = "$basename&action=$action&step=$step&count=$count&sendto=$sendto&touser=" . rawurlencode($touser) . "&subject=". rawurlencode($subject)."&by=$by&percount=$percount$creditlist";
		adminmsg("sendmsg_step",EncodeUrl($j_url),1);
	} else {
		//修改用户名输入有错误时发送失败@modify panjl@2010-11-3
		if(0 !== count($to_a_err)){
			$existUsers = S::sqlImplode($to_a_err);
			adminmsg('sendmsg_success_part');
		}
		pwCache::deleteData($cache_file);
		adminmsg('sendmsg_success');
	}
}
?>