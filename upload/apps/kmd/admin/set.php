<?php
!function_exists('adminmsg') && exit ( 'Forbidden' );
define('KMD_PATH', dirname(dirname(__FILE__)));
$adminitem or $adminitem = 'basic';
$kmdService = L::loadClass('kmdservice', 'forum');
S::gp(array('step','page'),'gp',2);

//pagination
$pagesize = $db_perpage;
$page < 1 && $page = 1;
$offset = ($page - 1) * $db_perpage;

//job url
$jobUrl = $basename;
$basename .= "&adminitem=$adminitem";

if(empty($step) && in_array($adminitem, array('paylog','check','kmdmanage'))){
	pwCache::getData(D_P . 'data/bbscache/forumcache.php');
}

if ($adminitem == 'basic') {
	//孔明灯基本设置
	if ($step == 2) {
		S::gp(array('config','spreads', 'newspreads', 'username'));
		$tmpSpreads = $tmpNewSpreads = array();
		if (S::isArray($spreads['name'])) {
			//update spreads
			foreach ($spreads['name'] as $key => $value ) {
				if(!checkSpread(
					$spreads['day'][$key],
					$spreads['discount'][$key],
					$spreads['name'][$key],
					$spreads['price'][$key],
					$spreads['displayorder'][$key]
				)) continue;
				$tmpSpreads[$key] = array (
					'displayorder' => $spreads['displayorder'][$key], 
					'name' => $spreads['name'][$key], 
					'day' => $spreads['day'][$key], 
					'price' => number_format($spreads['price'][$key],2,'.',''), 
					'discount' => round($spreads['discount'][$key],1)
				);
			}
			$kmdService->updateSpreads($tmpSpreads);
		}
		if (S::isArray($newspreads['name'])) {
			//new kmd spreads
			foreach ($newspreads['name'] as $key => $value ) {
				if(!checkSpread(
					$newspreads['day'][$key],
					$newspreads['discount'][$key],
					$newspreads['name'][$key],
					$newspreads['price'][$key],
					$newspreads['displayorder'][$key]
				)) continue;
				$tmpNewSpreads[] = array (
					'displayorder' => $newspreads['displayorder'][$key], 
					'name' => $newspreads['name'][$key], 
					'day' => $newspreads['day'][$key], 
					'price' => number_format($newspreads['price'][$key],2,'.',''), 
					'discount' => round($newspreads['discount'][$key],1)
				);
			}
			$kmdService->addSpreads($tmpNewSpreads);
		}
		//审核人
		$resultUsername = array();
		if ($username) {
			(strpos($username, ',') !== false) && $username = array_unique(explode(',', $username));
			!is_array($username) && $username = array($username);
			$sql = 'SELECT uid, username FROM pw_members WHERE username IN (' . S::sqlImplode($username) . ')';
			$query = $db->query($sql);
			while ($rs = $db->fetch_array($query)) {
				$resultUsername[] = $rs['username'];
			}
			$difference = array();
			if (count($username) != count($resultUsername)) {
				$difference = array_diff($username, $resultUsername);
				if (!empty($difference) && is_array($difference)) {
					$diffStr = implode(',', $difference) . '不存在';
					adminmsg($diffStr, $basename);
				}
			}
		}
		$config['kmd_reviewperson'] = implode(',', $resultUsername);
		//关闭孔明灯
		if ($db_kmd_ifkmd && !$config['kmd_ifkmd']){
			$kmdService->recycleAllKmds();
		}
		saveConfig();
		adminmsg('operate_success');
	} elseif ($step == 3){
		//删除套餐
		define('AJAX', 1);
		S::gp(array('sid'),'gp',2);
		if ($sid < 1) adminmsg('operate_error');
		$kmdService->deleteSpreadById($sid);
		echo "success\t" . $sid;
		ajax_footer();
	} else {
		pwCache::getData( D_P . 'data/bbscache/ol_config.php');
		ifcheck($db_kmd_ifkmd,'ifkmd');
		$spreads = $kmdService->getSpreads();
		${'deducttime_' . $db_kmd_deducttime} = 'selected';
		require_once PrintApp('set');
	}
} else if ($adminitem == 'paylog') {
	//购买记录
	if ($step == 2) {
		S::gp(array('operater','payid'),'gp',2);
		S::gp(array('payids'));
		$payid > 0 && $payids = array($payid);
		if (!S::isArray($payids)){
			adminmsg('operate_error');
		}
		if ($operater == 1) {
			//确认支付
			$kmdService->recycleAllExpiredKmds();
			foreach ($payids as $v) {
				$payLog = $kmdService->getPayLogById($v);
				if (!S::isArray($payLog) || $payLog['status'] != KMD_PAY_STATUS_NOTPAY) continue;
				
				//spread info
				$spread = $kmdService->getSpreadById($payLog['sid']);
				if (!S::isArray($spread)) continue;
				
				$fieldData = array(
						'fid' => $payLog['fid'],
						'uid' => $payLog['uid'],
						'starttime' => $timestamp,
						'endtime' => $timestamp + $spread['day'] * 86400,
						'status'	=> KMD_THREAD_STATUS_EMPTY
				);
				if ($payLog['kid'] == 0) {
					//新买
					$kmdService->addKmdInfo($fieldData);
				} else {
					$kmdInfo = $kmdService->getKmdInfoDetailByKid($payLog['kid']);
					if (!S::isArray($kmdInfo) || $kmdInfo['uid'] != $payLog['uid']) {
						//孔明灯不再属于续费者,为续费用户新增一个
						$kmdService->addKmdInfo($fieldData);
					} else {
						//续费
						$fieldData = array(
							'endtime' => $kmdInfo['endtime'] + $spread['day'] * 86400,
						);
						$kmdService->updateKmdInfo($fieldData,$payLog['kid']);
					}
				}
				$fieldData = array(
					'status' => KMD_PAY_STATUS_PAYED
				);
				$kmdService->updatePayLog($fieldData,$v);
				
				//send message
				$forum = L::forum($payLog['fid']);
				sendKmdMessages(
					$payLog['uid'], 
					'kmd_admin_paylog_checked_title', 
					'kmd_admin_paylog_checked_content',
					array(),
					array('forumname'=>$forum['name'],'fid'=>$payLog['fid'],'price'=>$payLog['money'])
				);
			}
		} elseif ($operater == 2) {
			//未到帐
			foreach ($payids as $v) {
				$payLog = $kmdService->getPayLogById($v);
				if (!S::isArray($payLog)) continue;
				//$kmdService->deletePayLogById($v);
				$fieldData = array(
					'status' => KMD_PAY_STATUS_INVALID
				);
				$kmdService->updatePayLog($fieldData,$v);
				//send message
				$forum = L::forum($payLog['fid']);
				sendKmdMessages(
					$payLog['uid'], 
					'kmd_admin_paylog_reject_title', 
					'kmd_admin_paylog_reject_content',
					array(),
					array('forumname'=>$forum['name'],'fid'=>$payLog['fid'],'price'=>$payLog['money'])
				);
			}
		}
		adminmsg('operate_success');
	} else {
		//购买记录列表
		S::gp(array('username','starttime','endtime'));
		S::gp(array('fid','status'),'gp',2);
		$forumcache = getSelectedForumCache($fid);
		$params = array();
		if ($username) {
			$userService = L::loadClass('userservice','user'); /* @var $userService PW_UserService */
			$userInfo = $userService->getByUserName($username);
			$userInfo && $params['uid'] = $userInfo['uid'];
		}
		if ($starttime && $endtime) {
			$params['starttime'] = PwStrtoTime($starttime);
			$params['endtime'] = PwStrtoTime($endtime) + 86400;
		}
		$fid > 0 && $params['fid'] = $fid;
		$status > 0 && $params['status'] = $status;
		$count = $income = 0;
		$count = (int)$kmdService->countPayLogs($params);
		if ($count) {
			$payLogs = $kmdService->searchPayLogs($params,$offset,$pagesize);
			$income = (float)$kmdService->getKmdIncome($params);
		}
		$pages = numofpage($count, $page, ceil($count/$pagesize), "$basename&username=$username&fid=$fid&status=$status&starttime=$starttime&endtime=$endtime&");
		require_once PrintApp('set');
	}
} else if ($adminitem == 'check') {
	//帖子审核
	if ($step == 2) {
		S::gp(array('operater','kid'),'gp',2);
		S::gp(array('kids'));
		$kid > 0 && $kids = array($kid);
		if (!S::isArray($kids)){
			adminmsg('operate_error');
		}
		foreach ($kids as $kid){
			$kmdInfo = $kmdService->getKmdInfoDetailByKid($kid);
			if ($kmdInfo['status'] != KMD_THREAD_STATUS_CHECK) {
				continue;
			}
			$fieldData = array();
			if ($operater == 1) {
				//通过审核
				$fieldData['status'] = KMD_THREAD_STATUS_OK;
			} elseif ($operater == 2){
				//拒绝审核
				$fieldData['status'] = KMD_THREAD_STATUS_REJECT;
				$fieldData['tid'] = 0;
			}
			$kmdService->updateKmdInfo($fieldData,$kid);
			$kmdService->updateKmdThread($kid);
			
			//send message
			sendKmdMessages(
				$kmdInfo['uid'], 
				$operater == 1 ? 'kmd_admin_thread_checked_title' : 'kmd_admin_thread_reject_title', 
				$operater == 1 ? 'kmd_admin_thread_checked_content' : 'kmd_admin_thread_reject_content', 
				array(),
				array('subject'=>$kmdInfo['subject'],'tid'=>$kmdInfo['tid'])
			);
		}
		require_once(R_P . 'require/updateforum.php');
		updatetop();
		adminmsg('operate_success');
	} else {
		//待审帖子列表
		S::gp(array('username'));
		S::gp(array('fid'),'gp',2);
		$forumcache = getSelectedForumCache($fid);
		$params = array('status' => KMD_THREAD_STATUS_CHECK);
		$fid > 0 && $params['fid'] = $fid;
		if ($username) {
			$userService = L::loadClass('userservice','user'); /* @var $userService PW_UserService */
			$userInfo = $userService->getByUserName($username);
			$userInfo && $params['uid'] = $userInfo['uid'];
		}
		//get data
		$count = (int)$kmdService->countKmdInfosWithCondition($params);
		if ($count) {
			$checks = $kmdService->getKmdInfosWithCondition($params,$offset,$pagesize);
		}
		$pages = numofpage($count, $page, ceil($count/$pagesize), "$basename&username=$username&fid=$fid&");
		require_once PrintApp('set');
	}
} else if ($adminitem == 'kmdmanage') {
	//孔明灯管理
	if ($step == 2) {
		S::gp(array('operater','kid'),'gp',2);
		S::gp(array('kids'));
		$kid > 0 && $kids = array($kid);
		if (!S::isArray($kids)){
			adminmsg('operate_error');
		}
		foreach ($kids as $kid) {
			if ($operater == 1) {
				//撤销孔明灯
				$kmdInfo = $kmdService->getKmdInfoByKid($kid);
				if (!$kmdInfo || !$kmdInfo['uid']) {
					continue;
				}
				$kmdService->initKmdInfoByKid($kid);
				//send message
				sendKmdMessages(
					$kmdInfo['uid'], 
					'kmd_admin_kmd_canceled_title', 
					'kmd_admin_kmd_canceled_content'
				);
			}
		}
		require_once R_P. 'require/updateforum.php';
		updatetop();
		adminmsg('operate_success');
	} elseif ($step == 3) {
		//ajax 添加孔明灯 TODO
		S::gp(array('action'));
		if ($action == 'save') {
			
		} else {
			define('AJAX', 1);
		    require_once PrintApp('set_ajax');
		    ajax_footer();
		}
	} else {
		//管理列表
		S::gp(array('username','starttime','endtime'));
		S::gp(array('fid','status'),'gp',2);
		$forumcache = getSelectedForumCache($fid);
		$params = array();
		$status > 0 && $params['status'] = $status;
		$fid > 0 && $params['fid'] = $fid;
		if ($username) {
			$userService = L::loadClass('userservice','user'); /* @var $userService PW_UserService */
			$userInfo = $userService->getByUserName($username);
			$userInfo && $params['uid'] = $userInfo['uid'];
		}
		if ($starttime && $endtime) {
			$params['starttime'] = PwStrtoTime($starttime);
			$params['endtime'] = PwStrtoTime($endtime) + 86400;
		}
		//get data
		$count = (int)$kmdService->countKmdInfosWithCondition($params);
		if ($count) {
			$kmdInfos = $kmdService->getKmdInfosWithCondition($params,$offset,$pagesize);
		}
		$pages = numofpage($count, $page, ceil($count/$pagesize), "$basename&username=$username&fid=$fid&starttime=$starttime&endtime=$endtime&");
		require_once PrintApp('set');
	}
} else if ($adminitem == 'usermanage') {
	//用户管理
	S::gp(array('username'));
	$params = array();
	if ($username) {
		$userService = L::loadClass('userservice','user'); /* @var $userService PW_UserService */
		$userInfo = $userService->getByUserName($username);
		$userInfo && $params['uid'] = $userInfo['uid'];
	}
	//get data
	$count = (int)$kmdService->countKmdUsers($params);
	$count > 0 && $users = $kmdService->searchUsers($params, $offset, $pagesize);
	$pages = numofpage($count, $page, ceil($count/$pagesize), "$basename&username=$username&");
	require_once PrintApp('set');
}

function sendKmdMessages($uid, $title, $content,$paramsTitle = array(),$paramsContent = array()) {
	$uid = intval($uid);
	if ($uid < 1 || !$title || !$content) return false;
	$userService = L::loadClass('userservice','user'); /* @var $userService PW_UserService */
	$userInfo = $userService->get($uid);
	if (!$userInfo) return false;
	M::sendNotice(array($userInfo['username']), array(
		'title' => getLangInfo('writemsg', $title, $paramsTitle),
		'content' => getLangInfo('writemsg',$content, $paramsContent))
	);
}

function checkSpread($day,$discount,$name,$rmb,$rank){
	if (!$day || !preg_match('/^[1-9][0-9]*$/', $day)) return false;
	if (!$name || strlen($name) > 150) return false;
	if ($discount && !preg_match('/^[0-9](\.[0-9])?$/', $discount)) return false;
	if (strlen($rank) && !preg_match('/^\d+$/', $rank)) return false;
	return true;
}

function getSelectedForumCache($fid){
	global $forumcache;
	$fid = intval($fid);
	if ($fid < 1) return $forumcache;
	return str_replace("<option value=\"$fid\">", "<option value=\"$fid\" selected>", $forumcache);
}
?>