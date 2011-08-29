<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=sendmail";
$tmpCachefile = D_P.'data/bbscache/tmpSendmail.php';

if(empty($action)){
	$resume = false;
	if (@file_exists($tmpCachefile)) {
		$resume = true;
		$pwSendmail['lasttime'] = get_date(pwFilemtime($tmpCachefile));
	}
	include PrintEot('sendmail');exit;
} elseif($action=="send"){
	$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);

	S::gp(array('by','subject','percount'));
	$atc_content = $_POST['atc_content'];
	if(empty($subject) || empty($atc_content)){
		adminmsg('sendmsg_empty');
	}
	$pwSendmail = array();
	if($by==0){
		$sendto = S::getGP('sendto');
		!$sendto && adminmsg('operate_error');
		settype($sendto,'array');
		$pwSendmail['info'] = $sendto;
		$pwSendmail['count'] = $db->get_value("SELECT COUNT(*) FROM pw_members WHERE groupid IN(".S::sqlImplode($sendto).")");
	} elseif($by==1){
		$onlineuser = GetOnlineUser();
		$uids = array();
		foreach($onlineuser as $key => $value){
			is_numeric($key) && $uids[] =  $key;
		}
		$pwSendmail['count'] = count($uids);
	} elseif ($by == 2) {
		S::gp(array('starttime','endtime'),'P');
		$stime = PwStrtoTime($starttime);
		$etime   = PwStrtoTime($endtime);
		if ($stime > $etime) {
			$tmp = $etime;
			$etime = $stime;
			$stime = $tmp;
		}
		$pwSendmail['info'] = array('stime'=>$stime,'etime'=>$etime);
		$pwSendmail['count'] = $db->get_value("SELECT COUNT(*) FROM pw_members WHERE regdate BETWEEN".S::sqlEscape($stime)."AND".S::sqlEscape($etime));
	} elseif ($by == 3) { //增加按最后登录时间发送邮件@modify panjl@2010-11-3
		S::gp(array('loginstarttime','loginendtime'),'P');
		$lostime = PwStrtoTime($loginstarttime);
		$loetime   = PwStrtoTime($loginendtime);
		if ($lostime > $loetime) {
			$lotmp = $loetime;
			$loetime = $lostime;
			$lostime = $lotmp;
		}
		$pwSendmail['info'] = array('lostime'=>$lostime,'loetime'=>$loetime);
		$pwSendmail['count'] = $db->get_value("SELECT COUNT(*) FROM pw_memberdata WHERE lastvisit BETWEEN".S::sqlEscape($lostime)."AND".S::sqlEscape($loetime));
	} elseif($by==4){ //增加输入用户名发送邮件@modify panjl@2010-11-3
		S::gp(array('touser'),'P');
		!$touser && adminmsg('operate_error');
		$userService = L::loadClass('UserService', 'user');
		$to_a_temp = explode(',', $touser);	
		$to_a = $to_a_err = array();
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
			$query = $db->query("SELECT uid,username FROM pw_members WHERE $sqlwhere");
			$uids = $usernames = array();
			while ($rt = $db->fetch_array($query)) {
				$uids[] = $rt['uid'];
				$usernames[] = $rt['username'];
			}
		}
		if(0 !== count($to_a_err)){
			$notExistUsers = S::sqlImplode($to_a_err);
			$pwSendmail['notExistUsers'] = $notExistUsers;
		}
		$pwSendmail['uids'] = $uids;
		$pwSendmail['usernames'] = $usernames;
		$pwSendmail['count'] = count($uids);
	} else{
		adminmsg('operate_error');
	}

	$pwSendmail['num'] = $percount ? (int)$percount : 100;
	$pwSendmail['by'] = (int)$by;
	$pwSendmail['subject'] = $subject;
	$pwSendmail['content'] = $atc_content;
	$pwSendmail['sent'] = 0;
	$pwSendmail['step'] = 0;
	pwCache::setData($tmpCachefile,"<?php\r\ndie();\r\n?>\r\n".serialize($pwSendmail));
	touch($tmpCachefile);
	include PrintEot('sendmail');exit;
} elseif ($action == 'confirm') {
	//* $pwSendmail = readover($tmpCachefile);
	$pwSendmail = pwCache::getData($tmpCachefile, false, true);
	$pwSendmail = trim(substr($pwSendmail,19));
	$pwSendmail = @unserialize($pwSendmail);
	if (empty($pwSendmail)) {
		pwCache::deleteData($tmpCachefile);
		adminmsg('operate_fail');
	}
	$pwSendmail['lasttime'] = get_date(pwFilemtime($tmpCachefile));
	include PrintEot('sendmail');exit;
} elseif ($action == 'groupsend') {
	require_once(R_P.'require/sendemail.php');

	//* $pwSendmail = readover($tmpCachefile);
	$pwSendmail = pwCache::getData($tmpCachefile, false, true);
	$pwSendmail = trim(substr($pwSendmail,19));
	$pwSendmail = @unserialize($pwSendmail);
	if (empty($pwSendmail)) {
		pwCache::deleteData($tmpCachefile);
		adminmsg('sendmsg_nolog');
	}
	if($pwSendmail['by'] == 0){
		$pwSQL = "WHERE groupid IN(".S::sqlImplode($pwSendmail['info']).")";
	} elseif($pwSendmail['by']==1){
		$onlineuser = GetOnlineUser();
		$uids = array();
		foreach($onlineuser as $key => $value){
			is_numeric($key) && $uids[] =  $key;
		}
		$pwSQL = "WHERE uid IN(".S::sqlImplode($uids).")";
	} elseif ($pwSendmail['by'] == 2) {//TODO Efficiency problems
		$pwSQL = "WHERE regdate BETWEEN".S::sqlEscape($pwSendmail['info']['stime'])."AND".S::sqlEscape($pwSendmail['info']['etime']);
	} elseif ($pwSendmail['by'] == 3) { //增加按最后登录时间发送邮件@modify panjl@2010-11-3
		$pwSQL = "WHERE d.lastvisit BETWEEN".S::sqlEscape($pwSendmail['info']['lostime'])."AND".S::sqlEscape($pwSendmail['info']['loetime']);
	} elseif($pwSendmail['by']==4){ //增加输入用户名发送邮件@modify panjl@2010-11-3
		if (!S::isArray($pwSendmail['uids'])) adminmsg('operate_error');
		$pwSQL = " WHERE uid IN(".S::sqlImplode($pwSendmail['uids']).")";
	} else{
		adminmsg('operate_error');
	}
	$limit = S::sqlLimit($pwSendmail['step']*$pwSendmail['num'],$pwSendmail['num']);
	 //增加按最后登录时间发送邮件@modify panjl@2010-11-3
	if ($pwSendmail['by'] == 3) {
		$query = $db->query("SELECT m.uid,m.username,m.email FROM pw_members m left join pw_memberdata d USING(uid) $pwSQL $limit");
	}else{
		$query = $db->query("SELECT uid,username,email FROM pw_members ". $pwSQL. $limit);
	}
	while($rt=$db->fetch_array($query)){
		if (!$rt['email'] || !preg_match("/^[-a-zA-Z0-9_\.]+@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/",$rt['email'])) {
			continue;
		}
		$sendsubject = str_replace(array('$winduid','$windid','$email'),array($rt['uid'],$rt['username'],$rt['email']),$pwSendmail['subject']);
		$sendcontent = str_replace(array('$winduid','$windid','$email'),array($rt['uid'],$rt['username'],$rt['email']),$pwSendmail['content']);

		sendemail($rt['email'],$sendsubject,$sendcontent,'email_additional');
	}

	$pwSendmail['step']++;
	$havesend = $pwSendmail['sent'] = $pwSendmail['step']*$pwSendmail['num'];
	$count = $pwSendmail['count'];
	if($pwSendmail['count'] > $pwSendmail['sent']){
		pwCache::setData($tmpCachefile,"<?php\r\ndie();\r\n?>\r\n".serialize($pwSendmail));
		touch($tmpCachefile);
		$j_url = "$basename&action=$action";
		adminmsg("sendmsg_step",EncodeUrl($j_url),1);
	} else{
		pwCache::deleteData($tmpCachefile);
		$notExistUsers = $pwSendmail['notExistUsers'];
		!$pwSendmail['notExistUsers'] ? adminmsg('sendmsg_success') : adminmsg('sendmail_success_part');
	}
} elseif ($action == 'erase') {
	PostCheck($verify);
	pwCache::deleteData($tmpCachefile);
	adminmsg('operate_success');
}
?>