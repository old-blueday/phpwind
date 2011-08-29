<?php
!defined('P_W') && exit('Forbidden');
S::gp(array('job'),'GP');

if ($job == 'preview') {//帖子预览

	require_once(R_P.'require/bbscode.php');
	require_once(R_P.'require/header.php');
	S::gp(array('atc_title','atc_content','pcinfo','atc_title'),'P');
	S::gp(array('actmid','tid','authorid'),'P',2);
	$data = array();

	L::loadClass('ActivityForBbs', 'activity', false);
	$postActForBbs = new PW_ActivityForBbs($data);
	
	$actdb = $postActForBbs->getPreviewdata($pcinfo);
		
	list($topicvalue) = $postActForBbs->getActValue($actmid,$actdb);
		
	$atc_content = str_replace("\n","<br>",$atc_content);
	$atc_content = wordsConvert($atc_content);
	$preatc = convert($atc_content,$db_windpost);

	require_once PrintEot('preview');
	footer();

} elseif ($job == 'export') {//导出报名列表

	S::gp(array('tid','actmid'),G,2);

	if (!$windid && ($userdb = getCurrentOnlineUser()) && $userdb['ip'] == $onlineip) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$winddb = $userService->get($userdb['uid']);
		$winduid = $winddb['uid'];
		$groupid = $winddb['groupid'];
		$groupid == '-1' && $groupid = $winddb['memberid'];
		$userrvrc = round($winddb['rvrc'] / 10, 1);
		$windid = $winddb['username'];
		if (file_exists(D_P . "data/groupdb/group_$groupid.php")) {
			//* require_once pwCache::getPath(S::escapePath(D_P . "data/groupdb/group_$groupid.php"));
			pwCache::getData(S::escapePath(D_P . "data/groupdb/group_$groupid.php"));
		} else {
			//* require_once pwCache::getPath(D_P . "data/groupdb/group_1.php");
			pwCache::getData(D_P . "data/groupdb/group_1.php");
		}
		define('FX', 1);
	}
	$read = $db->get_one("SELECT authorid,subject,fid FROM pw_threads WHERE tid=" . S::sqlEscape($tid));//帖子信息
	$data = array();
	L::loadClass('ActivityForBbs', 'activity', false);
	$postActForBbs = new PW_ActivityForBbs($data);

	$isAdminright = $postActForBbs->getAdminRight($read['authorid']);
	!$isAdminright && Showmsg('act_export_noright');

	$ifpaydb = array(
		'0' => getLangInfo('other','act_ifpay_0'),
		'1' => getLangInfo('other','act_ifpay_1'),
		'2' => getLangInfo('other','act_ifpay_1'),
		'3' => getLangInfo('other','act_ifpay_3'),
		'4' => getLangInfo('other','act_ifpay_4'),
	);
	
	if ($db_charset == 'utf-8' || $db_charset == 'big5'){
			foreach($ifpaydb as $key => $value) {
				$ifpaydb[$key] = pwConvert($value,'gbk',$db_charset);
			}
	}
	
	$payMemberNums = $orderMemberNums = 0;
	$query = $db->query("SELECT signupnum,ifpay FROM pw_activitymembers WHERE fupid=0 AND tid=" . S::sqlEscape($tid));
	while ($rt = $db->fetch_array($query)) {
		if ($rt['ifpay'] != 3) {//费用关闭的不算
			$orderMemberNums += $rt['signupnum'];//已报名人数
		}
		if ($rt['ifpay'] != 0 && $rt['ifpay'] != 3) {//自己支付1、确认支付2、费用退完4
			$payMemberNums += $rt['signupnum'];//已经付款的人数
		}
	}

	$defaultValueTableName = getActivityValueTableNameByActmid();
	$defaultValue = $db->get_one("SELECT fees,paymethod FROM $defaultValueTableName WHERE tid=" . S::sqlEscape($tid));
	$feesdb = unserialize($defaultValue['fees']);
	$isFree = count($feesdb) > 0 ? false : true;//判断该活动是否免费

	$memberlistdb = $addmemberlistdb = $refundmemberlistdb = array();
	$query = $db->query("SELECT * FROM pw_activitymembers WHERE fupid=0 AND tid=" . S::sqlEscape($tid) . " ORDER BY (uid=" . S::sqlEscape($winduid) . ") DESC,ifpay ASC,actuid DESC");//正常报名
	while ($rt = $db->fetch_array($query)) {
		if ($db_charset == 'utf-8' || $db_charset == 'big5'){
			foreach($rt as $key => $value) {
				$rt[$key] = pwConvert($value,'gbk',$db_charset);
			}
		}
		if ($rt['signupdetail']) {
			$rt['signupdetail'] = unserialize($rt['signupdetail']);
			foreach ($rt['signupdetail'] as $key => $value) {
				$rt['signupmember'] .= ($rt['signupmember'] ? '，' : '') .$feesdb[$key]['condition'].$value.'人';
			}
		}
		$memberlistdb[$rt['actuid']] = $rt;
	}
	if ($defaultValue['paymethod'] == 1 && $memberlistdb && !$isFree) {
		$query = $db->query("SELECT * FROM pw_activitymembers WHERE isadditional=1 ORDER BY ifpay ASC,actuid DESC");//追加数组
		while ($rt = $db->fetch_array($query)) {
			if ($db_charset == 'utf-8' || $db_charset == 'big5'){
				foreach($rt as $key => $value) {
					$rt[$key] = pwConvert($value,'gbk',$db_charset);
				}
			}
			$addmemberlistdb[$rt['fupid']][] = $rt;
		}
		$query = $db->query("SELECT * FROM pw_activitymembers WHERE isrefund=1 ORDER BY actuid DESC");
		while ($rt = $db->fetch_array($query)) {
			if ($db_charset == 'utf-8' || $db_charset == 'big5'){
				foreach($rt as $key => $value) {
					$rt[$key] = pwConvert($value,'gbk',$db_charset);
				}
			}
			$refundmemberlistdb[$rt['fupid']][] = $rt;
		}
	}
	$titledb = array(
		getLangInfo('other','act_id')."\t",	
		getLangInfo('other','act_username')."\t",
		getLangInfo('other','act_signupmember')."\t",
		getLangInfo('other','act_signuptime')."\t",
		getLangInfo('other','act_nickname')."\t",
		getLangInfo('other','act_mobile')."\t",
		getLangInfo('other','act_telephone')."\t",
		getLangInfo('other','act_address')."\t",
		getLangInfo('other','act_message')."\t",
		getLangInfo('other','act_totalcash')."\t",
		getLangInfo('other','act_ifpay')."\t\n"
	);
	$subject = $read['subject'];
	if ($db_charset == 'utf-8' || $db_charset == 'big5'){
		$subject = pwConvert($subject,'gbk',$db_charset);
	}
	$subject = str_replace(array('&nbsp;','&#160;',' '),array('','',''),$subject);
	/*
	header("Content-type:application/vnd.ms-excel");
	header("Content-Disposition:attachment;filename=$subject.xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	*/

	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $timestamp + 86400) . ' GMT');
	header('Expires: ' . gmdate('D, d M Y H:i:s', $timestamp + 86400) . ' GMT');
	header('Cache-control: max-age=86400');
	header('Content-Encoding: none');
	header("Content-Disposition: attachment; filename=\"$subject.xls\"");
	header("Content-type:application/vnd.ms-excel");
	header("Content-Transfer-Encoding: binary");
	//$filesize && header("Content-Length: $filesize");
	
	echo getLangInfo('other','act_ordermembernums')."{$orderMemberNums}".getLangInfo('other','act_paymembernums')."{$payMemberNums}".getLangInfo('other','act_ren')."\t\n";
	foreach ($titledb as $key => $value) {
		if ($db_charset == 'utf-8' || $db_charset == 'big5'){
			$value = pwConvert($value,'gbk',$db_charset);
		}
		echo $value;
	}
	$i = 0;
	foreach ($memberlistdb as $value) {
		$i++;
		$value['message'] = str_replace("\n","",$value['message']);
		echo "$i\t";
		echo "$value[username]\t";
		echo "$value[signupmember]\t";
		echo get_date($value['signuptime'],'Y-n-j H:i:s') . "\t";
		echo "$value[nickname]\t";
		echo "$value[mobile]\t";
		echo "$value[telephone]\t";
		echo "$value[address]\t";
		echo "$value[message]\t";
		if (!$isFree) {
			if ($value['isadditional']) {
				echo "$value[totalcash]".getLangInfo('other','act_yuan')." ".getLangInfo('other','act_add')."\t";
			} else {
				echo "$value[totalcash]".getLangInfo('other','act_yuan')."\t";
			}
			echo "{$ifpaydb[$value[ifpay]]}\t\n";
		} else {
			echo getLangInfo('other','act_free')."\t";
			if ($value['ifpay'] == 3) {
				echo getLangInfo('other','act_close')."\t\n";
			} else {
				echo getLangInfo('other','act_normal')."\t\n";
			}
		}

		if ($refundmemberlistdb[$value['actuid']]) {
			foreach ($refundmemberlistdb[$value['actuid']] as $refund) {
				$refund['refundreason'] = str_replace("\n","",$refund['refundreason']);
				echo "\t";
				echo "\t";
				echo "\t";
				echo get_date($refund['signuptime'],'Y-n-j H:i:s') . "\t";
				echo "\t";
				echo "\t";
				echo "\t";
				echo "\t";
				echo "$refund[refundreason]\t";
				echo "$refund[totalcash]".getLangInfo('other','act_yuan')." ".getLangInfo('other','act_refund')."\t";
				echo getLangInfo('other','act_refunded')."\t\n";
			}
		}
		if ($addmemberlistdb[$value['actuid']]) {
			foreach ($addmemberlistdb[$value['actuid']] as $add) {
				$add['additionalreason'] = str_replace("\n","",$add['additionalreason']);
				echo "\t";
				echo "\t";
				echo "\t";
				echo get_date($add['signuptime'],'Y-n-j H:i:s') . "\t";
				echo "\t";
				echo "\t";
				echo "\t";
				echo "\t";
				echo "$add[additionalreason]\t";
				echo "$add[totalcash]".getLangInfo('other','act_yuan')." ".getLangInfo('other','act_add')."\t";
				echo "{$ifpaydb[$add[ifpay]]}\t\n";
				if ($refundmemberlistdb[$add['actuid']]) {
					foreach ($refundmemberlistdb[$add['actuid']] as $ref){
						$ref['refundreason'] = str_replace("\n","",$ref['refundreason']);
						echo "\t";
						echo "\t";
						echo "\t";
						echo get_date($ref['signuptime'],'Y-n-j H:i:s') . "\t";
						echo "\t";
						echo "\t";
						echo "\t";
						echo "\t";
						echo "$refund[refundreason]\t";
						echo "$ref[totalcash]".getLangInfo('other','act_yuan')." ".getLangInfo('other','act_refund')."\t";
						echo getLangInfo('other','act_refunded')."\t\n";
					}
				}
			}
		}
	}
	echo getLangInfo('other','act_exporttime').get_date($timestamp,'Y-n-j H:i:s');
	exit;
}

function getCurrentOnlineUser() {
	global $db_online, $ol_offset, $db;
	if (empty($db_online)) {
		$userdb = explode("\t", getuserdb(D_P . "data/bbscache/online.php", $ol_offset));
		return $userdb ? array('uid' => $userdb[8], 'ip' => $userdb[2]) : array();
	} else {
		//* $olid = (int)GetCookie('olid');
		//* $userdb = $db->get_one("SELECT uid,ip FROM pw_online WHERE olid=" . S::sqlEscape($olid) . ' AND uid>0');
		$onlineService = L::loadClass('OnlineService', 'user');
		$userdb = $onlineService->getOnlineUserByUserId($GLOBALS['winduid']);			
		
		return $userdb;
	}
}