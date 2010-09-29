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

	InitGP(array('by','subject','percount'));
	$atc_content = $_POST['atc_content'];
	if(empty($subject) || empty($atc_content)){
		adminmsg('sendmsg_empty');
	}
	$pwSendmail = array();
	if($by==0){
		$sendto = GetGP('sendto');
		!$sendto && adminmsg('operate_error');
		settype($sendto,'array');
		$pwSendmail['info'] = $sendto;
		$pwSendmail['count'] = $db->get_value("SELECT COUNT(*) FROM pw_members WHERE groupid IN(".pwImplode($sendto).")");
	} elseif($by==1){
		$onlineuser = GetOnlineUser();
		$uids = array();
		foreach($onlineuser as $key => $value){
			is_numeric($key) && $uids[] =  $key;
		}
		$pwSendmail['count'] = count($uids);
	} elseif ($by == 2) {
		InitGP(array('starttime','endtime'),'P');
		$stime = PwStrtoTime($starttime);
		$etime   = PwStrtoTime($endtime);
		if ($stime > $etime) {
			$tmp = $etime;
			$etime = $stime;
			$stime = $timp;
		}
		$pwSendmail['info'] = array('stime'=>$stime,'etime'=>$etime);
		$pwSendmail['count'] = $db->get_value("SELECT COUNT(*) FROM pw_members WHERE regdate BETWEEN".pwEscape($stime)."AND".pwEscape($etime));
	} else{
		adminmsg('operate_error');
	}
	$pwSendmail['num'] = $percount ? (int)$percount : 100;
	$pwSendmail['by'] = (int)$by;
	$pwSendmail['subject'] = $subject;
	$pwSendmail['content'] = $atc_content;
	$pwSendmail['sent'] = 0;
	$pwSendmail['step'] = 0;
	writeover($tmpCachefile,"<?php\r\ndie();\r\n?>\r\n".serialize($pwSendmail));
	include PrintEot('sendmail');exit;
} elseif ($action == 'confirm') {
	$pwSendmail = readover($tmpCachefile);
	$pwSendmail = trim(substr($pwSendmail,19));
	$pwSendmail = @unserialize($pwSendmail);
	if (empty($pwSendmail)) {
		P_unlink($tmpCachefile);
		adminmsg('operate_fail');
	}
	$pwSendmail['lasttime'] = get_date(pwFilemtime($tmpCachefile));
	include PrintEot('sendmail');exit;
} elseif ($action == 'groupsend') {
	require_once(R_P.'require/sendemail.php');

	$pwSendmail = readover($tmpCachefile);
	$pwSendmail = trim(substr($pwSendmail,19));
	$pwSendmail = @unserialize($pwSendmail);
	if (empty($pwSendmail)) {
		P_unlink($tmpCachefile);
		adminmsg('sendmsg_nolog');
	}
	if($pwSendmail['by']==0){
		$pwSQL = "WHERE groupid IN(".pwImplode($pwSendmail['info']).")";
	} elseif($pwSendmail['by']==1){
		$onlineuser = GetOnlineUser();
		$uids = array();
		foreach($onlineuser as $key => $value){
			is_numeric($key) && $uids[] =  $key;
		}
		$pwSQL = "WHERE uid IN(".pwImplode($uids).")";
	} elseif ($pwSendmail['by'] == 2) {//TODO Efficiency problems
		$pwSQL = "WHERE regdate BETWEEN".pwEscape($pwSendmail['info']['stime'])."AND".pwEscape($pwSendmail['info']['etime']);
	} else{
		adminmsg('operate_error');
	}
	$limit = pwLimit($pwSendmail['step']*$pwSendmail['num'],$pwSendmail['num']);
	$query = $db->query("SELECT uid,username,email FROM pw_members $pwSQL $limit");
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
		writeover($tmpCachefile,"<?php\r\ndie();\r\n?>\r\n".serialize($pwSendmail));
		$j_url = "$basename&action=$action";
		adminmsg("sendmsg_step",EncodeUrl($j_url),1);
	} else{
		P_unlink($tmpCachefile);
		adminmsg('sendmsg_success');
	}
} elseif ($action == 'erase') {
	PostCheck($verify);
	P_unlink($tmpCachefile);
	adminmsg('operate_success');
}
?>