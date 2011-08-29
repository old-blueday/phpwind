<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('job'),'GP');
if (!$windid && !in_array($job,array('memberlist'))) {
	Showmsg('not_login');
}

if ($job == 'user_authentication') {//用户身份验证
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$data = $userService->get($winduid, false, false, true);
	$tradeinfo = $data['tradeinfo'] ? $data['tradeinfo'] : '';
	$tradeinfo = unserialize($tradeinfo);

	$user_id = $tradeinfo['user_id'];
	$isBinded = $tradeinfo['isbinded'];
	$isCertified = $tradeinfo['iscertified'];
	if ($user_id && $isBinded == 'T' && $isCertified != 'T') {//如果绑定，但未实名认证
		require_once(R_P . 'lib/activity/alipay_push.php');
		$alipayPush = new AlipayPush();
		$is_success = $alipayPush->user_query($winduid);//查询是否实名认证
		if ($is_success == 'T') {
			$tradeinfo['iscertified'] = $is_success;
			$tradeinfo = addslashes(serialize($tradeinfo));
			$userService->update($winduid, array(), array(), array('tradeinfo'=>$tradeinfo));
			echo 'success';
		} else {
			echo 'iscertified_fail';
		}
	} elseif ($isBinded != 'T') {
		echo 'isbinded_fail';
	}
	ajax_footer();
} elseif ($job == 'upload') {//附件上传
	S::gp(array('actmid'),GP,2);
	if ($_POST['step'] == 2){
		L::loadClass('activityupload', 'upload', false);
		require_once(R_P.'require/functions.php');
		$img = new ActivityUpload(0,$actmid);
		PwUpload::upload($img);
		pwFtpClose($GLOBALS['ftp']);
		$fileuploadurl = $img->attachs['fileuploadurl'];
		if ($fileuploadurl) {
			echo "success\t".$fileuploadurl;
		} else {
			echo "error\t";
		}
	} else {
		require_once PrintEot('ajax');
	}
	ajax_footer();

} elseif ($job == 'delimg') {//删除附件

		S::gp(array('tid','actmid','fieldid'),GP,2);
		S::gp(array('attachment'),GP);
		if (!$actmid || !$fieldid) {
			echo 'fail';ajax_footer();
		}

		if ($tid) {
			$fieldService = L::loadClass('ActivityField', 'activity');
			$fielddata = $fieldService->getField($fieldid);
			$fieldname = $fielddata['fieldname'];
			if ($fielddata['ifdel'] == 1) {
				$tableName = getActivityValueTableNameByActmid($actmid, 1, 1);
			} else {
				$tableName = getActivityValueTableNameByActmid($actmid, 0, 0);
			}

			$path = $db->get_value("SELECT $fieldname FROM $tableName WHERE tid=". S::sqlEscape($tid));
			!$path && $path = $attachment;
		} else {
			$path = $attachment;
		}
		

		if (strpos($path,'..') !== false) {
			return false;
		}
		$lastpos = strrpos($path,'/') + 1;
		$s_path = substr($path, 0, $lastpos) . 's_' . substr($path, $lastpos);

		if (!file_exists("$attachpath/$path")) {
			if (pwFtpNew($ftp,$db_ifftp)) {
				$ftp->delete($path);
				$ftp->delete($s_path);
				require_once(R_P.'require/functions.php');
				pwFtpClose($ftp);
			}
		} else {
			P_unlink("$attachdir/$path");
			if (file_exists("$attachdir/$s_path")) {
				P_unlink("$attachdir/$s_path");
			}
		}

		if ($tid) {
			$db->update("UPDATE $tableName SET $fieldname='' WHERE tid=". S::sqlEscape($tid));
		}

		echo 'success';
		ajax_footer();

} elseif ($job == 'recommend') {//推荐

	S::gp(array('tid','actmid'),G,2);
	if (!S::inArray($windid, $manager) && !$SYSTEM['recommendactive']) {
		echo "noright\t";
	}
	$defaultValueTableName = getActivityValueTableNameByActmid();
	$rt = $db->get_one("SELECT recommend FROM $defaultValueTableName WHERE tid=".S::sqlEscape($tid) . " AND actmid=" .S::sqlEscape($actmid));
	if ($rt) {
		if ($rt['recommend'] == 1) {
			$newrecommend = 0;
		} else {
			$newrecommend = 1;
		}
		$db->update("UPDATE $defaultValueTableName SET recommend=".S::sqlEscape($newrecommend)." WHERE tid=".S::sqlEscape($tid)." AND actmid=" .S::sqlEscape($actmid));

		echo "success\t".$newrecommend;
	} else {
		echo "fail\t";
	}
	ajax_footer();

} elseif ($job == 'signup') {//报名

	S::gp(array('tid','thelast','authorid','actmid'),GP,2);

	L::loadClass('ActivityForBbs', 'activity', false);
	$postActForBbs = new PW_ActivityForBbs($data);

	$data = array();

	$defaultValueTableName = getActivityValueTableNameByActmid();
		$defaultValue = $db->get_one("SELECT iscertified,iscancel,signupstarttime,signupendtime,endtime,minparticipant,maxparticipant,userlimit,fees,paymethod,batch_no,t.subject,t.authorid FROM $defaultValueTableName dv LEFT JOIN pw_threads t USING(tid) WHERE dv.tid=".S::sqlEscape($tid));

	if ($defaultValue['iscancel']) {//判断是否活动取消
		Showmsg('act_signup_iscancel_error');
	}
	if ($defaultValue['signupstarttime'] > $timestamp || $defaultValue['signupendtime'] < $timestamp) {//未在报名时间内
		Showmsg('act_signup_time_error');
	}
	$defaultValue['authorid'] == $winduid && Showmsg('act_signup_owner_error');//发起人无法参与报名

	$feesdb = unserialize($defaultValue['fees']);//费用
	$isFree = count($feesdb) > 0 ? false : true;//判断该活动是否免费
	$paymethod = $defaultValue['paymethod'];//支付方式

	if ($defaultValue['paymethod'] == 1) {//实名认证和创建活动号
		if (!$defaultValue['batch_no']) {
			Showmsg('act_signup_batch_no_error');
		}
		if (!$defaultValue['iscertified']) {
			Showmsg('act_signup_iscertified_error');
		}
	}

	if ($thelast != 1) {//报名第一步

		//已报名人数
		$orderMemberNums = $postActForBbs->peopleAlreadySignup($tid);
		if ($defaultValue['maxparticipant']) {
			$theMoreNum = $defaultValue['maxparticipant'] - $orderMemberNums;//剩余报名人数
			$theMoreNum == 0 && Showmsg('act_signup_is_full');//报名人数已满
		}

		if (empty($_POST['step'])) {
			$memberdb = $db->get_one("SELECT nickname,mobile FROM pw_activitymembers WHERE tid=".S::sqlEscape($tid)."AND uid=".S::sqlEscape($winduid). " AND fupid=0 AND isadditional=0 ORDER BY signuptime DESC" );

			$signupType = array();//报名人数类型
			foreach ($feesdb as $key => $value) {
				$signupType[$key] = $value['condition'];
			}
			$fieldService = L::loadClass('ActivityField', 'activity');
			$userlimitIfable = $fieldService->getFieldByModelIdAndName($actmid, 'userlimit');

			$isU = (!$userlimitIfable || $defaultValue['userlimit'] == 2 && isFriend($authorid,$winduid) || $defaultValue['userlimit'] == 1) ? 1 : 0;//报名限制
				
			require_once PrintEot('ajax');
			ajax_footer();
		} elseif ($_POST['step'] == '2') {
			PostCheck();
			S::gp(array('signup','telephone','mobile','address','message','ifanonymous','nickname'));

			$totalsignupnums = 0;
			$totalcash = 0;
			$newsignup = array();
			foreach ($signup as $key => $value) {
				$value = (int)$value;
				$totalcash += $feesdb[$key]['money'] * $value;//总费用
				$totalsignupnums += $value;//总人数
				$newsignup[$key] = (int)$value;
			}
			$signup = serialize($newsignup);

			if ($totalsignupnums == 0) {//报名人数至少为1人
				Showmsg('act_signupnums_error');
			} elseif ($totalsignupnums > 65000) {//输入人数过大
				Showmsg('act_signupnums_error_max');
			}
			if (!$mobile || !$nickname) {//称呼手机必填
				Showmsg('act_mobile_nickname_error');
			}
			if ($defaultValue['maxparticipant'] && $defaultValue['maxparticipant'] - $orderMemberNums < $totalsignupnums) {//总报名人数限制
				Showmsg('act_num_overflow');
			}

			$sqlarray = array(
				'tid'			=> $tid,
				'uid'			=> $winduid,
				'actmid'		=> $actmid,
				'username'		=> $windid,
				'signupnum'		=> $totalsignupnums,
				'signupdetail'	=> $signup,
				'nickname'		=> $nickname,
				'totalcash'		=> $totalcash,
				'mobile'		=> $mobile,
				'telephone'		=> $telephone,
				'address'		=> $address,
				'message'		=> $message,
				'ifanonymous'	=> $ifanonymous,
				'signuptime'	=> $timestamp
			);
			$db->update("INSERT INTO pw_activitymembers SET " . S::sqlSingle($sqlarray));
			$actuid = $db->insert_id();
			$nextto = 'signup';
			$db->update("UPDATE $defaultValueTableName SET updatetime=".S::sqlEscape($timestamp)." WHERE tid=".S::sqlEscape($tid));//报名列表动态时间

			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			/*短消息通知 报名 发起人*/
			$authorUserName = $userService->getUserNameByUserId($authorid);
			if (!$authorUserName) { 
				Showmsg('user_not_exists');
			}
			//版块信息、创建时间/
			$threadInfo = $db->get_one("SELECT * FROM pw_threads WHERE tid=".S::sqlEscape($tid));
			$createTime = get_date($threadInfo['postdate']);
			//* require_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
			pwCache::getData(D_P.'data/bbscache/forum_cache.php');
			M::sendNotice(
				array($authorUserName),
				array(
					'title' => getLangInfo('writemsg', 'activity_signup_new_title', array(
							'username' => $windid
						)
					),
					'content' => getLangInfo('writemsg', 'activity_signup_new_content', array(
							'username' => $windid,
							'uid' => $winduid,
							'tid' => $tid,
							'fid' => $threadInfo['fid'],
							'createtime' =>$createTime,
							'fname' => $forum[$threadInfo['fid']]['name'],
							'subject' => $defaultValue['subject']
						)
					),
				),'notice_active','notice_active'
			);
			
			
			Showmsg('act_signup_nextstep');
		}
	} elseif ($thelast == 1) {
		S::gp(array('totalsignupnums','totalcash','actuid'));
		$fees = '';
		foreach ($feesdb as $value) {
			$fees .= ($fees ? '，' : '') .$value['money'] . getLangInfo('other','act_RMB') . '/' . $value['condition'];
		}
		!$fees && $fees = getLangInfo('other','act_free');

		$signupdetail = $db->get_value("SELECT signupdetail FROM pw_activitymembers WHERE actuid=".S::sqlEscape($actuid));
		$signupnumsdb = unserialize($signupdetail);//报名人数
		$signupdetail = '';
		foreach ($signupnumsdb as $key => $value) {
			$signupdetail .= ($signupdetail ? '，' : '') .$feesdb[$key]['condition'].$value.getLangInfo('other','act_people');
		}
		require_once PrintEot('ajax');ajax_footer();
	}
} elseif ($job == 'memberlist') {//展示报名信息列表
	S::gp(array('page','tid','fid','authorid','paymethod','actmid'),GP,2);

	L::loadClass('ActivityForBbs', 'activity', false);
	$postActForBbs = new PW_ActivityForBbs($data);

	$data = array();
	$author = $db->get_value('SELECT authorid FROM pw_threads WHERE tid = ' . $tid);
	$isAdminright = $postActForBbs->getAdminRight($author);
		
	$db_perpage = 20;

	$count = $payMemberNums = $orderMemberNums = 0;
	$query = $db->query("SELECT signupnum,ifpay FROM pw_activitymembers WHERE fupid=0 AND tid=".S::sqlEscape($tid));
	while ($rt = $db->fetch_array($query)) {
		if ($rt['ifpay'] != 3) {//费用关闭的不算
			$orderMemberNums += $rt['signupnum'];//已报名人数
		}
		if ($rt['ifpay'] != 0 && $rt['ifpay'] != 3) {//自己支付1、确认支付2、费用退完4
			$payMemberNums += $rt['signupnum'];//已经付款的人数
		}
		$count++;
	}

	if ($winduid) {
		$page < 1 && $page = 1;
		$numofpage = ceil($count/$db_perpage);
		if ($numofpage && $page > $numofpage) {
			$page = $numofpage;
		}
		$start = ($page-1)*$db_perpage;
		$limit = S::sqlLimit($start,$db_perpage);
		$pages = numofpage($count, $page, $numofpage, "pw_ajax.php?action=$action&job=$job&tid=$tid&authorid=$authorid&paymethod=$paymethod&", null, 'ajaxview');

		$defaultValueTableName = getActivityValueTableNameByActmid();
		$defaultValue = $db->get_one("SELECT fees,endtime,iscancel FROM $defaultValueTableName WHERE tid=".S::sqlEscape($tid));
		$feesdb = unserialize($defaultValue['fees']);
		$isFree = count($feesdb) > 0 ? false : true;//判断该活动是否免费

		$iscancel = $defaultValue['iscancel'];//活动取消
		$endtimeStatus = $defaultValue['endtime'] + 30*86400 - $timestamp;//结束时间后一个月,>0 则可以操作,< 0无法操作

		$memberlistdb = $addmemberlistdb = $refundmemberlistdb = array();
		$query = $db->query("SELECT * FROM pw_activitymembers WHERE fupid=0 AND tid=".S::sqlEscape($tid)." ORDER BY (uid=".S::sqlEscape($winduid).") DESC,ifpay ASC,actuid DESC $limit");//正常报名
		while ($rt = $db->fetch_array($query)) {
			$rt['signuptime'] = get_date($rt['signuptime'],'n-j H:i');
			if ($rt['signupdetail']) {
				$rt['signupdetail'] = unserialize($rt['signupdetail']);
				foreach ($rt['signupdetail'] as $key => $value) {
					$rt['signupmember'] .= ($rt['signupmember'] ? '，' : '') .$feesdb[$key]['condition'].$value.'人';
				}
			}
			$isFree && $rt['totalcash'] = getLangInfo('other','act_free');//如果活动免费，则金额置为免费
			$memberlistdb[$rt['actuid']] = $rt;
		}
		if ($paymethod == 1 && $memberlistdb) {
			$query = $db->query("SELECT * FROM pw_activitymembers WHERE isadditional=1 ORDER BY ifpay ASC,actuid DESC");//追加数组
			while ($rt = $db->fetch_array($query)) {
				$rt['signuptime'] = get_date($rt['signuptime'],'n-j H:i');
				$addmemberlistdb[$rt['fupid']][] = $rt;
			}
			$query = $db->query("SELECT * FROM pw_activitymembers WHERE isrefund=1 ORDER BY actuid DESC");
			while ($rt = $db->fetch_array($query)) {
				$rt['signuptime'] = get_date($rt['signuptime'],'n-j H:i');
				$refundmemberlistdb[$rt['fupid']][] = $rt;
			}
			
			foreach ($memberlistdb as $value) {
				$rowspannum = 0;
				$rowspannum += count($addmemberlistdb[$value['actuid']]) + count($refundmemberlistdb[$value['actuid']]);
				if ($addmemberlistdb[$value['actuid']]) {
					foreach ($addmemberlistdb[$value['actuid']] as $val) {
						if ($refundmemberlistdb[$val['actuid']]) {
							$rowspannum += count($refundmemberlistdb[$val['actuid']]);
						}
					}
				}
				$memberlistdb[$value['actuid']]['rowspannum'] = $rowspannum;
			}
		}
	}
		
	require_once PrintEot('ajax');ajax_footer();
} elseif ($job == 'detailshow') {//展示报名信息（详情）
		S::gp(array('actuid','authorid','tid','paymethod','actmid'),GP,2);
		$data = array();

		L::loadClass('ActivityForBbs', 'activity', false);
		$postActForBbs = new PW_ActivityForBbs($data);

		$isAdminright = $postActForBbs->getAdminRight($authorid);

		$detailinfo = $db->get_one("SELECT * FROM pw_activitymembers WHERE actuid=".S::sqlEscape($actuid));
		if ($detailinfo['isrefund'] || $detailinfo['isadditional']  || $detailinfo['ifanonymous'] && !$isAdminright && $detailinfo['uid'] != $winduid) {//追加的无法查看、退款的无法查看、匿名但没有权限的无法查看
			Showmsg('act_detailshow_error');
		}
		$defaultValueTableName = getActivityValueTableNameByActmid();
		$defaultValue = $db->get_one("SELECT fees,endtime FROM $defaultValueTableName WHERE tid=".S::sqlEscape($tid));
		$endtimeStatus = $defaultValue['endtime'] + 30*86400 - $timestamp;//结束时间后一个月,>0 则可以操作,< 0无法操作
		$feesdb = unserialize($defaultValue['fees']);
		$isFree = count($feesdb) > 0 ? false : true;//判断该活动是否免费
		$detailinfo['signupdetail'] = unserialize($detailinfo['signupdetail']);
		foreach ($detailinfo['signupdetail'] as $key => $value) {
			$detailinfo['signupmember'] .= ($detailinfo['signupmember'] ? '，' : '') .$feesdb[$key]['condition'].$value.'人';
		}
		
		if ($paymethod == 1) {
			$addmemberlistdb = $refundfupdb = array();
			$query = $db->query("SELECT actuid,totalcash,ifpay,refundcost FROM pw_activitymembers WHERE isadditional=1 AND fupid=".S::sqlEscape($detailinfo['actuid']));
			while ($rt = $db->fetch_array($query)) {
				$addmemberlistdb[] = $rt;
				if ($rt['refundcost']) {
					$refundfupdb[$rt['actuid']] = $rt['actuid'];
					
				}
				$refundfupdb[$detailinfo['actuid']] = $detailinfo['actuid'];
			}
			if ($refundfupdb) {
				$refundmemberlistdb = array();
				$query = $db->query("SELECT actuid,totalcash,ifpay FROM pw_activitymembers WHERE isrefund=1 AND fupid IN(".S::sqlImplode($refundfupdb).")");
				while ($rt = $db->fetch_array($query)) {
					$refundmemberlistdb[] = $rt;
				}
			}
		}
		
		require_once PrintEot('ajax');ajax_footer();
} elseif ($job == 'modify') {//修改报名信息
		S::gp(array('actmid','actuid','tid'),GP,2);

		$defaultValueTableName = getActivityValueTableNameByActmid();
		$defaultValue = $db->get_one("SELECT signupstarttime,signupendtime,endtime,minparticipant,maxparticipant,userlimit,fees,paymethod FROM $defaultValueTableName WHERE tid=".S::sqlEscape($tid));

		$feesdb = unserialize($defaultValue['fees']);//费用
		$paymethod = $defaultValue['paymethod'];//支付方式

		//已报名人数
		$orderMemberNums = $db->get_value("SELECT SUM(signupnum) as sum FROM pw_activitymembers WHERE tid=".S::sqlEscape($tid)." AND fupid=0 AND ifpay IN('0','1','2','4')");

		if (empty($_POST['step'])) {

			$defaultValue['endtime'] + 30*86400 < $timestamp && Showmsg('act_modify_error');//活动结束超过30天无法修改

			$signupinfo = $db->get_one("SELECT * FROM pw_activitymembers WHERE actuid=".S::sqlEscape($actuid)." AND uid=".S::sqlEscape($winduid));
			if ($signupinfo['isrefund'] || $signupinfo['isadditional']  || !$signupinfo) {//追加的无法修改、退款的无法修改、报名信息不存在
				Showmsg('act_modify_error');
			}
			$signupinfo['signupdetail'] = unserialize($signupinfo['signupdetail']);
			$ownernums = $signupinfo['signupnum'];//个人报名人数
			$signupinfo['ifanonymous'] && $checked = 'checked';
			
			$signupType = array();//报名人数类型
			foreach ($feesdb as $key => $value) {
				$signupType[$key] = $value['condition'];
			}
			$defaultValue['maxparticipant'] && $theMoreNum = $defaultValue['maxparticipant'] - $orderMemberNums;//剩余报名人数

			require_once PrintEot('ajax');ajax_footer();
		} elseif ($_POST['step'] == '2') {
			PostCheck();
			S::gp(array('ownernums'),P,2);
			S::gp(array('signup','telephone','mobile','address','message','ifanonymous','nickname'),P);

			$totalsignupnums = 0;
			$totalcash = 0;
			$newsignup = array();
			foreach ($signup as $key => $value) {
				$value = (int)$value;
				$totalcash += $feesdb[$key]['money'] * $value;//总费用
				$totalsignupnums += $value;//总人数
				$newsignup[$key] = (int)$value;
			}
			$signup = serialize($newsignup);

			if ($totalsignupnums == 0) {//报名人数至少为1人
				echo 'act_signupnums_error';ajax_footer();
			} elseif ($totalsignupnums > 65000) {//输入人数过大
				echo 'act_signupnums_error_max';ajax_footer();
			}
			if (!$mobile || !$nickname) {//称呼手机必填
				echo 'act_mobile_nickname_error';ajax_footer();
			}
			if ($defaultValue['maxparticipant'] && $defaultValue['maxparticipant'] - $orderMemberNums + $ownernums < $totalsignupnums) {//总报名人数限制
				echo 'act_num_overflow';ajax_footer();
			}

			$sqlarray = array(
				'signupnum'		=> $totalsignupnums,
				'signupdetail'	=> $signup,
				'nickname'		=> $nickname,
				'totalcash'		=> $totalcash,
				'mobile'		=> $mobile,
				'telephone'		=> $telephone,
				'address'		=> $address,
				'message'		=> $message,
				'ifanonymous'	=> $ifanonymous
			);

			$db->update("UPDATE pw_activitymembers SET " . S::sqlSingle($sqlarray)." WHERE actuid=".S::sqlEscape($actuid)." AND uid=".S::sqlEscape($winduid));
			$db->update("UPDATE $defaultValueTableName SET updatetime=".S::sqlEscape($timestamp)." WHERE tid=".S::sqlEscape($tid));//报名列表动态时间

			echo "success";
			ajax_footer();
		}
} elseif ($job == 'close') {//关闭报名信息
	S::gp(array('actuid','paymethod','tid'),GP,2);

	$memberdb = $db->get_one("SELECT am.ifpay,am.uid,am.username,am.tid,am.isadditional,am.isrefund,t.subject,t.authorid,t.author FROM pw_activitymembers am LEFT JOIN pw_threads t ON am.tid=t.tid WHERE actuid=".S::sqlEscape($actuid));
	$isadditional = $memberdb['isadditional'];//是否追加

	if (empty($_POST['step'])) {
		$defaultValueTableName = getActivityValueTableNameByActmid();
		$defaultValue = $db->get_one("SELECT endtime FROM $defaultValueTableName WHERE tid=".S::sqlEscape($tid));
		$defaultValue['endtime'] + 30*86400 < $timestamp && Showmsg('act_endtime_toolong');//结束时间后一个月,>0 则可以操作,< 0无法操作

		$memberdb['ifpay'] != 0 && Showmsg('act_close_payment_error');//只有未支付状态下可以操作
		$winduid != $memberdb['authorid'] && Showmsg('act_close_payment_noright');//只有发起人可以操作
		$memberdb['isrefund'] && Showmsg('act_close_payment_noright');//退款的无法关闭

		require_once PrintEot('ajax');ajax_footer();			
	} elseif ($_POST['step'] == 2) {
		PostCheck();
		if ($paymethod == 1) {//如果是支付宝付款，则需要支付宝接口通信
			require_once(R_P . 'lib/activity/alipay_push.php');
			$alipayPush = new AlipayPush();
			$is_success = $alipayPush->close_aa_detail_payment($tid,$actuid);
			echo $is_success;
			ajax_footer();
		} else {
			$db->update("UPDATE pw_activitymembers SET ifpay=3 WHERE actuid=".S::sqlEscape($actuid));//费用关闭
			$defaultValueTableName = getActivityValueTableNameByActmid();
			$db->update("UPDATE $defaultValueTableName SET updatetime=".S::sqlEscape($timestamp)." WHERE tid=".S::sqlEscape($tid));//报名列表动态时间
			//现金支付
			/*短消息通知 删除报名者 发起人*/
			M::sendNotice(
				array($memberdb['author']),
				array(
					'title' => getLangInfo('writemsg', 'activity_signup_close_title', array(
							'username' => $memberdb['username']
						)
					),
					'content' => getLangInfo('writemsg', 'activity_signup_close_content', array(
							'username' => $memberdb['username'],
							'uid'      => $memberdb['uid'],
							'tid'      => $memberdb['tid'],
							'subject'  => $memberdb['subject']
						)
					)
				), 'notice_active', 'notice_active'
			);
			
			/*短消息通知 删除报名者 参与人*/			
			M::sendNotice(
				array($memberdb['username']),
				array(
					'title' => getLangInfo('writemsg', 'activity_signuper_close_title', array(
							'username' => $memberdb['author']
						)
					),
					'content' => getLangInfo('writemsg', 'activity_signuper_close_content', array(
							'username' => $memberdb['author'],
							'uid'      => $memberdb['authorid'],
							'tid'      => $memberdb['tid'],
							'subject'  => $memberdb['subject']
						)
					)
				),'notice_active', 'notice_active'
			);
			
			echo "success";
			ajax_footer();
		}
	}
} elseif ($job == 'confirmpay') {//确认支付
	S::gp(array('tid','actuid','authorid','actmid'),GP,2);
	
	$memberdb = $db->get_one("SELECT am.ifpay,am.uid,am.username,am.tid,am.totalcash,am.isadditional,am.isrefund,t.subject,t.authorid,t.author FROM pw_activitymembers am LEFT JOIN pw_threads t ON am.tid=t.tid WHERE am.actuid=".S::sqlEscape($actuid));
	$defaultValueTableName = getActivityValueTableNameByActmid();

	if (empty($_POST['step'])) {
		
		$defaultValue = $db->get_one("SELECT endtime FROM $defaultValueTableName WHERE tid=".S::sqlEscape($tid));
		$defaultValue['endtime'] + 30*86400 < $timestamp && Showmsg('act_endtime_toolong');//结束时间后一个月,>0 则可以操作,< 0无法操作
		$memberdb['ifpay'] != 0 && Showmsg('act_confirmpay_error');
		$winduid != $memberdb['authorid'] && Showmsg('act_confirmpay_noright');//只有发起人可以操作
		$memberdb['isrefund'] && Showmsg('act_confirmpay_noright');//退款的无法关闭

		require_once PrintEot('ajax');ajax_footer();
	} elseif ($_POST['step'] == 2) {
		PostCheck();

		if ($memberdb['ifpay'] == 0) {
			/*查询订单状态*/
			require_once(R_P . 'lib/activity/alipay_push.php');
			$alipayPush = new AlipayPush();
			$alipayPush->query_aa_detail_payment($tid,$actuid);
			/*查询订单状态*/
		}
		$ifpay = $db->get_value("SELECT ifpay FROM pw_activitymembers WHERE actuid=".S::sqlEscape($actuid));
		if ($ifpay == 0) {
			$db->update("UPDATE pw_activitymembers SET ifpay=2 WHERE actuid=".S::sqlEscape($actuid));//线下支付的ifpay=2
			$db->update("UPDATE $defaultValueTableName SET updatetime=".S::sqlEscape($timestamp)." WHERE tid=".S::sqlEscape($tid));//报名列表动态时间
		}

		/*支付成功费用流通日志*/
		L::loadClass('ActivityForBbs', 'activity', false);
		$postActForBbs = new PW_ActivityForBbs($data);

		$data = array();
		$statusValue = $postActForBbs->getActivityStatusValue($tid);
		$postActForBbs->UpdatePayLog($tid,$actuid,$statusValue);
		/*支付成功费用流通日志*/

		/*短消息通知 确认支付 发起人*/
		//只适合支付宝支付
		$contentText = $memberdb['isadditional'] ? 'activity_confirmpay2_content' : 'activity_confirmpay_content';
		M::sendNotice(
			array($memberdb['author']),
			array(
				'title' => getLangInfo('writemsg', 'activity_confirmpay_title', array(
						'username' => $memberdb['username']
					)
				),
				'content' => getLangInfo('writemsg', $contentText, array(
						'username'  => $memberdb['username'],
						'uid'       => $memberdb['uid'],
						'tid'       => $tid,
						'subject'   => $memberdb['subject'],
						'totalcash'	=> $memberdb['totalcash']
					)
				)
			),
			'notice_active', 
			'notice_active'
		);

		/*短消息通知 确认支付 参与人*/
		//只适合支付宝支付
		$signuperContentText = $memberdb['isadditional'] ? 'activity_confirmpay2_signuper_content' : 'activity_confirmpay_signuper_content';
		M::sendNotice(
			array($memberdb['username']),
			array(
				'title' => getLangInfo('writemsg', 'activity_confirmpay_signuper_title', array(
						'username' => $memberdb['author']
					)
				),
				'content' => getLangInfo('writemsg', $signuperContentText, array(
						'username'  => $memberdb['author'],
						'uid'       => $memberdb['authorid'],
						'tid'       => $tid,
						'subject'   => $memberdb['subject'],
						'totalcash'	=> $memberdb['totalcash']
					)
				)
			),
			'notice_active', 
			'notice_active'
		);
		
		echo "success";
		ajax_footer();
	}
} elseif ($job == 'toalipay') {//去支付宝付款
	S::gp(array('actuid','tid','actmid','signuper'),GP,2);
	if (empty($_POST['step'])) {
		L::loadClass('ActivityForBbs', 'activity', false);
		$postActForBbs = new PW_ActivityForBbs($data);

		$data = array();

		$memberdb = $db->get_one("SELECT am.ifpay,am.isrefund,am.uid,am.ifanonymous,t.authorid FROM pw_activitymembers am LEFT JOIN pw_threads t USING(tid) WHERE am.actuid=".S::sqlEscape($actuid));
		$memberdb['authorid'] == $winduid && Showmsg('act_toalipay_authorid');//发起人无法替别人支付
		$isAdminright = $postActForBbs->getAdminRight($memberdb['authorid']);
		if ($memberdb['isrefund'] || $memberdb['ifanonymous'] && !$isAdminright && $memberdb['uid'] != $winduid) {//退款的无法支付、匿名但没有权限的无法支付
			Showmsg('act_toalipay_error');
		}

		if ($memberdb['ifpay'] == 0) {
			/*查询订单状态*/
			require_once(R_P . 'lib/activity/alipay_push.php');
			$alipayPush = new AlipayPush();
			$ifpay = $alipayPush->query_aa_detail_payment($tid,$actuid);
			/*支付成功费用流通日志*/
			if (is_numeric($ifpay) && $ifpay > 0) {
				$statusValue = $postActForBbs->getActivityStatusValue($tid);
				$postActForBbs->UpdatePayLog($tid,$actuid,$statusValue);
			}
			/*支付成功费用流通日志*/
			/*查询订单状态*/
		}
			
		$fromuid = $signuper == $winduid ? '-1' : $winduid;
		$defaultValueTableName = getActivityValueTableNameByActmid();
		$defaultValue = $db->get_one("SELECT dt.fees,dt.paymethod,dt.iscancel,dt.endtime,am.signupdetail,am.ifpay,am.totalcash,am.additionalreason FROM $defaultValueTableName dt LEFT JOIN pw_activitymembers am USING(tid) WHERE am.tid=".S::sqlEscape($tid)." AND am.actuid=".S::sqlEscape($actuid));
		$additionalreason = $defaultValue['additionalreason'];

		!$defaultValue && Showmsg('undefined_action');//交易不存在
		$defaultValue['paymethod'] != 1 && Showmsg('act_toalipay_paymethod');//只有支付方式为支付宝才可以支付
		$defaultValue['endtime'] + 30*86400 < $timestamp && Showmsg('act_endtime_toolong');//结束时间后一个月,>0 则可以操作,< 0无法操作
		$defaultValue['ifpay'] != 0 && Showmsg('act_toalipay_payed');//只有未支付状态才可以支付
		$defaultValue['iscancel'] == 1 && Showmsg('act_iscancelled_y');//活动被取消无法支付

		$feesdb = unserialize($defaultValue['fees']);//费用
		$fees = '';
		foreach ($feesdb as $value) {
			$fees .= ($fees ? '，' : '') .$value['money'] . getLangInfo('other','act_RMB') . '/'.$value['condition'];
		}
		$signupdetail = $db->get_value("SELECT signupdetail FROM pw_activitymembers WHERE actuid=".S::sqlEscape($actuid));
		if ($signupdetail) {
			$signupnumsdb = unserialize($signupdetail);//报名人数
			$signupdetail = '';
			foreach ($signupnumsdb as $key => $value) {
				$signupdetail .= ($signupdetail ? '，' : '') .$feesdb[$key]['condition'].$value.getLangInfo('other','act_people');
			}
		}
		
		$totalcash = $defaultValue['totalcash'];
		require_once PrintEot('ajax');ajax_footer();
	}
	require_once PrintEot('ajax');ajax_footer();
} elseif ($job == 'sendmsg') {//群发短消息
		S::gp(array('tid','actmid','authorid'));
		$data = array();

		L::loadClass('ActivityForBbs', 'activity', false);
		$postActForBbs = new PW_ActivityForBbs($data);

		$isAdminright = $postActForBbs->getAdminRight($authorid);
		$isAdminright != 1 && Showmsg('act_sendmsg_noright');		

		if (empty($_POST['step'])) {
			$tid = $db->get_value("SELECT tid FROM pw_activitymembers WHERE tid=".S::sqlEscape($tid));
			!$tid && Showmsg('act_sendmsg_fail');

			require_once PrintEot('ajax');ajax_footer();
		} elseif ($_POST['step'] == 2) {
			PostCheck();
			S::gp(array('subject','atc_content','tid','ifsave'));
			require_once(R_P.'require/common.php');

			$msg_title   = trim($subject);
			$atc_content = trim($atc_content);
			if (empty($atc_content) || empty($msg_title)) {
				Showmsg('msg_empty');
			} elseif (strlen($msg_title) > 75 || strlen($atc_content) > 1500) {
				Showmsg('msg_subject_limit');
			}
			require_once(R_P.'require/bbscode.php');
			$wordsfb = L::loadClass('FilterUtil', 'filter');
			if (($banword = $wordsfb->comprise($msg_title)) !== false) {
				Showmsg('title_wordsfb');
			}
			if (($banword = $wordsfb->comprise($atc_content, false)) !== false) {
				Showmsg('content_wordsfb');
			}

			$query = $db->query("SELECT uid FROM pw_activitymembers WHERE tid=".S::sqlEscape($tid)." GROUP BY uid");
			while ($rt = $db->fetch_array($query)) {
				$uiddb[] = $rt['uid'];
			}
			$ifuids = $sqladd = $msglog = array();
			$uids = S::sqlImplode($uiddb);
			if ($uids) {
				$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
				$userNames = $userService->getUserNamesByUserIds($uids);
				M::sendNotice(
					$userNames,
					array(
						'create_uid'		=> $winduid,
						'create_username'	=> $windid,
						'title' 			=> $msg_title,
						'content' 			=> $atc_content
					),
					'notice_active',
					'notice_active'
				);
			}
			Showmsg('send_success');
		}
} elseif ($job == 'refund') {//退款
	S::gp(array('actuid','actmid','tid','authorid','thelast'),GP,2);
	$memberdb = $db->get_one("SELECT am.ifpay,am.isrefund,am.username,am.totalcash,t.authorid FROM pw_activitymembers am LEFT JOIN pw_threads t USING(tid) WHERE am.actuid=".S::sqlEscape($actuid));
	if ($memberdb['isrefund'] || $memberdb['authorid'] != $winduid) {//退款交易无法操作、不是发起人无法操作
		Showmsg('act_refund_noright');
	}
	$memberdb['ifpay'] != 1 && Showmsg('act_refund_error');//支付宝支付成功才能退款

	if ($thelast != 1) {
		$defaultValueTableName = getActivityValueTableNameByActmid();
		$defaultValue = $db->get_one("SELECT paymethod,endtime,fees FROM $defaultValueTableName WHERE tid=".S::sqlEscape($tid));
		$feesdb = unserialize($defaultValue['fees']);//费用
		$isFree = count($feesdb) > 0 ? false : true;//判断该活动是否免费
		$isFree && Showmsg('act_refund_free');//免费的活动不能退款

		$defaultValue['endtime'] + 30*86400 < $timestamp && Showmsg('act_endtime_toolong');//结束时间后一个月,>0 则可以操作,< 0无法操作
		$paymethod = $defaultValue['paymethod'];
		$paymethod != 1 && Showmsg('act_toalipay_paymethod');//支付宝支付才能退款
	}

	$tempcost = $db->get_value("SELECT SUM(totalcash) as sum FROM pw_activitymembers WHERE isrefund=1 AND fupid=".S::sqlEscape($actuid));//已退费用
	$morecost = $memberdb['totalcash'] - $tempcost;//剩余费用
	if ($morecost == 0) {//退款完毕
		$db->update("UPDATE pw_activitymembers SET ifpay=4 WHERE actuid=".S::sqlEscape($actuid));
		Showmsg('act_refund_cost_finish');
	}

	if ($thelast != 1) {//退款第一步
		if (empty($_POST['step'])) {

			require_once PrintEot('ajax');ajax_footer();
		} elseif ($_POST['step'] == 2) {
			S::gp(array('reason','cost'));

			if ($cost == 0 || number_format($cost, 2, '.', '') > number_format($morecost, 2, '.', '') || !preg_match("/^(([1-9]\d*)|0)(\.\d{0,2})?$/", $cost)) {
				Showmsg('act_refund_costerror');
			}

			if (strlen($reason) > 250 || $reason == '') {
				Showmsg('act_refund_reasonlenthlimit');
			}

			$sqlArray = array(
				'refundcost'	=> $cost,
				'refundreason'	=> $reason,
			);

			$db->update("UPDATE pw_activitymembers SET ".S::sqlSingle($sqlArray)." WHERE actuid=".S::sqlEscape($actuid));

			$nextto = 'refund';
			Showmsg('act_refund_nextstep');
		}
	} elseif ($thelast == 1) {
		S::gp(array('cost'));
		require_once PrintEot('ajax');ajax_footer();
	}	
} elseif ($job == 'additional') {//追加费用
	S::gp(array('actuid','tid','more','authorid','actmid'),GP,2);

	if (empty($more)) {
		$memberdb = $db->get_one("SELECT isrefund,isadditional,uid,actmid,username,actuid FROM pw_activitymembers WHERE actuid=".S::sqlEscape($actuid));
	}
	$defaultValueTableName = getActivityValueTableNameByActmid();

	if (empty($_POST['step'])) {
		$defaultValue = $db->get_one("SELECT dt.paymethod,dt.iscancel,dt.endtime,dt.fees,t.authorid FROM $defaultValueTableName dt LEFT JOIN pw_threads t USING(tid) WHERE dt.tid=".S::sqlEscape($tid));
		$feesdb = unserialize($defaultValue['fees']);//费用
		$isFree = count($feesdb) > 0 ? false : true;//判断该活动是否免费
		$isFree && Showmsg('act_additional_free');//免费的活动不能追加费用

		if ($memberdb['isrefund'] || $memberdb['isadditional'] || $defaultValue['authorid'] != $winduid) {//退款交易无法追加、追加的交易无法操作、只有发起人才能追加
			$defaultValue['authorid'] != $winduid && Showmsg('act_additional_noright');
		}
		$defaultValue['paymethod'] != 1 && Showmsg('act_toalipay_paymethod');//只有支付宝支付才能追加
		$defaultValue['iscancel'] == 1 && Showmsg('act_iscancelled_y');//活动取消无法追加
		$defaultValue['endtime'] + 30*86400 < $timestamp && Showmsg('act_endtime_toolong');//结束时间后一个月,>0 则可以操作,< 0无法操作

		if (!empty($more)) {

			$memberlist = array();
			$query = $db->query("SELECT uid,username FROM pw_activitymembers WHERE fupid=0 AND tid=".S::sqlEscape($tid). " GROUP BY uid");
			while ($rt = $db->fetch_array($query)) {
				$memberlist[$rt['uid']] = $rt['username'];
			}
		}
			
		require_once PrintEot('ajax');ajax_footer();
	} elseif ($_POST['step'] == 2) {
		S::gp(array('totalcost','uids','cost_','additionalreason'),P);
			
		$db->update("UPDATE $defaultValueTableName SET updatetime=".S::sqlEscape($timestamp)." WHERE tid=".S::sqlEscape($tid));//报名列表动态时间

		require_once R_P.'require/msg.php';
		$thread = $db->get_one("SELECT subject FROM pw_threads WHERE tid=".S::sqlEscape($tid));

		if (empty($more)) {
			if (!preg_match("/^(([1-9]\d*)|0)(\.\d{0,2})?$/", $totalcost) || $totalcost == 0) {
				echo "totalcost_error";ajax_footer();
			}
			$sqlarray = array(
				'fupid'				=> $actuid,
				'tid'				=> $tid,
				'uid'				=> $memberdb['uid'],
				'actmid'			=> $memberdb['actmid'],
				'username'			=> $memberdb['username'],
				'totalcash'			=> $totalcost,
				'signuptime'		=> $timestamp,
				'isadditional'		=> 1,
				'additionalreason'	=> $additionalreason,
			);
			$db->update("INSERT INTO pw_activitymembers SET " . S::sqlSingle($sqlarray));

			/*短消息通知 追加费用 参与人*/
			M::sendNotice(
				array($memberdb['username']),
				array(
					'title' => getLangInfo('writemsg', 'activity_additional_title', array(
							'username' => $windid
						)
					),
					'content' => getLangInfo('writemsg', 'activity_additional_content', array(
							'username'  => $windid,
							'uid'       => $winduid,
							'tid'       => $tid,
							'subject'   => $thread['subject'],
							'totalcash'	=> $totalcost
						)
					)
				),
				'notice_active', 
				'notice_active'
			);
			echo "success";
			ajax_footer();
		} else {//批量追加		
			$query = $db->query("SELECT * FROM pw_activitymembers WHERE fupid=0 AND isrefund=0 AND tid=".S::sqlEscape($tid)." AND uid IN(".S::sqlImplode($uids). ") GROUP BY uid ORDER BY signuptime DESC");
			while ($rt = $db->fetch_array($query)) {
				$actmid = $rt['actmid'];
				$memberdb[$rt['uid']] = $rt;
			}
			foreach ($uids as $uid) {
				if (!preg_match("/^(([1-9]\d*)|0)(\.\d{0,2})?$/", $cost_[$uid])) {
					continue;
				}
				if (isset($memberdb[$uid]) && $cost_[$uid] > 0) {
					$sqlarray[] = array(
						'fupid'				=> $memberdb[$uid]['actuid'],
						'tid'				=> $tid,
						'uid'				=> $memberdb[$uid]['uid'],
						'actmid'			=> $memberdb[$uid]['actmid'],
						'username'			=> $memberdb[$uid]['username'],
						'totalcash'			=> $cost_[$uid],
						'signuptime'		=> $timestamp,
						'isadditional'		=> 1,
						'additionalreason'	=> $additionalreason,
					);
				} elseif (!isset($memberdb[$uid]) && $cost_[$uid] > 0) {
					$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
					$username = $userService->getUserNameByUserId($uid);
					$sqlarray[] = array(
						'fupid'				=> 0,
						'tid'				=> $tid,
						'uid'				=> $uid,
						'actmid'			=> $actmid,
						'username'			=> $username,
						'totalcash'			=> $cost_[$uid],
						'signuptime'		=> $timestamp,
						'isadditional'		=> 1,
						'additionalreason'	=> $additionalreason,
					);
				}
				
				M::sendNotice(
					array($memberdb[$uid]['username']),
					array(
						'title' => getLangInfo('writemsg', 'activity_additional_title', array(
								'username' => $windid
							)
						),
						'content' => getLangInfo('writemsg', 'activity_additional_content', array(
								'username'  => $windid,
								'uid'       => $winduid,
								'tid'       => $tid,
								'subject'   => $thread['subject'],
								'totalcash'	=> $cost_[$uid]
							)
						)
					),
					'notice_active', 
					'notice_active'
				);

			}
			$db->update("INSERT INTO pw_activitymembers (fupid,tid,uid,actmid,username,totalcash,signuptime,isadditional,additionalreason) VALUES " . S::sqlMulti($sqlarray));
			echo "success";
			ajax_footer();
		}
	}
} elseif ($job == 'addnewmember') {//追加费用时添加新用户
	S::gp(array('tid'),P,2);
	S::gp(array('username'),P);
	
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$uid = $userService->getUserIdByUserName($username);
	$readcheck = $db->get_value("SELECT authorid FROM pw_threads WHERE tid=".S::sqlEscape($tid)." AND authorid=". S::sqlEscape($uid));
	$check = $db->get_value("SELECT uid FROM pw_activitymembers WHERE fupid=0 AND tid=".S::sqlEscape($tid)." AND uid=". S::sqlEscape($uid));
	if ($readcheck) {
		echo "authorerror\t";
	} elseif ($check) {
		echo "exist\t";
	} elseif ($uid) {
		echo "success\t$uid\t$username";
	} else {
		echo "error\t";
	}
	ajax_footer();
}

function isFriend($uid,$friend) {
	global $db;
	if ($db->get_value("SELECT uid FROM pw_friends WHERE uid=" . S::sqlEscape($uid) . ' AND friendid=' . S::sqlEscape($friend) . " AND status='0'")) {
		return true;
	}
	return false;
}