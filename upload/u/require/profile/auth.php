<?php
!function_exists('readover') && exit('Forbidden');

S::gp(array('check_step'));

if (empty($check_step)) {
	
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$userdb = $userService->get($winduid, true, false, true);
	if (!is_array($trade = unserialize($userdb['tradeinfo']))) {
		$trade = array();
	}
	$isAuthMobile = getstatus($userdb['userstatus'], PW_USERSTATUS_AUTHMOBILE);
	$isAuthAlipay = getstatus($userdb['userstatus'], PW_USERSTATUS_AUTHALIPAY);
	$isAuthCertificate = getstatus($userdb['userstatus'], PW_USERSTATUS_AUTHCERTIFICATE);
	if ($isAuthMobile && $userdb['authmobile']) {//将手机号的中间四位隐掉
		$authmobile = $userdb['authmobile'];
		for ($i = 3; $i<=6; $i++) {
			$authmobile{$i} = '*';
		}
	}
	
	if ($db_authcertificate) {//证件认证信息
		$authService = L::loadClass('Authentication', 'user');
		$certificateInfo = $authService->getCertificateInfoByUid($winduid);
	}

} elseif ($check_step == 'mobile') {

	S::gp(array('check_mobile'));
	if (!$check_mobile) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userdb = $userService->get($winduid, true, false, true);
		$check_mobile = $userdb['authmobile'];
	}
	$authService = L::loadClass('Authentication', 'user');
	list($authStep, $remainTime, $waitTime, $mobile) = $authService->getStatus('profile');
	
	$authStep_1 = $authStep_2 = 'none';
	${'authStep_' . $authStep} = '';
	
	if ($authStep == 1) {
		if (!$check_mobile) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userdb = $userService->get($winduid, true, false, true);
			$mobile = $userdb['authmobile'];
		} else {
			$mobile = $check_mobile;
		}
	}

} elseif ($check_step == 'alipay') {

	S::gp(array('userfrom'));
	!$userfrom && $userfrom = 'modify';
	$authService = L::loadClass('Authentication', 'user');
	$returnData = $authService->sendData('credit.alipay.geturl', array(
		'userid'	=> $winduid,
		'username'	=> $windid,
		'userfrom'	=> $userfrom,
		'returnurl'	=> $db_bbsurl.'/profile.php?action=auth&check_step=authalipay',
		'charset'	=> $GLOBALS['charset']
	));
	$returnData->url && ObHeader($returnData->url);
	Showmsg('与服务器通信失败,请稍候再试');
} elseif ($check_step == 'authalipay') {
	
	S::gp(array('alipay','is_certified','is_success','userid','usersign'));

	if ($userid != $winduid) {
		Showmsg('undefined_action');
	}
	$authService = L::loadClass('Authentication', 'user');
	$returnData = $authService->sendData('credit.alipay.checksign', array(
		'userid'		=> $winduid,
		'alipay'		=> $alipay,
		'is_success'	=> $is_success,
		'is_certified'	=> $is_certified,
		'usersign'		=> $usersign
	));
	$isSuccess = ($returnData->status && $is_success == 'T');

	if ($isSuccess) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userdb = $userService->get($winduid, false, false, true);
		if (!is_array($trade = unserialize($userdb['tradeinfo']))) {
			$trade = array();
		}
		$trade['alipay'] = $alipay;
		$trade['iscertified'] = $is_certified;
		$userService->update($winduid, array(), array(), array('tradeinfo' => serialize($trade)));
		$userService->setUserStatus($winduid, PW_USERSTATUS_AUTHALIPAY);
		//颁发勋章
		if ($db_md_ifopen) {
			$medalService = L::loadClass('medalservice','medal');
			$medalService->awardMedalByIdentify($winduid,'shimingrenzheng');
		}
		//任务
		//initJob($winduid,'doAuthAlipay');
		//if($db_job_isopen){
			$jobService = L::loadclass("job", 'job'); /* @var $jobService PW_Job */
			$jobService->jobController($winduid,'doAuthAlipay');
		//}
	}
	
} elseif ($check_step == 'certificate') {//证件认证
	$step = S::getGP('step');
	$authService = L::loadClass('Authentication', 'user');
	if (empty($step)) {
		$certificateTypesHtml = $authService->getCertificateTypeHtml();
	} elseif($step == 2) {
		S::gp(array('certificate'));
		L::loadClass('certificateupload', 'upload', false);
		!$certificate['number'] && Showmsg("请输入证件编号");
		//删除原有认证
		$certificateInfo = $authService->getCertificateInfoByUid($winduid);
		$certificateInfo && $authService->deleteCertificateById($certificateInfo['id']);
		
		$certificateUploadBehavior = new CertificateUpload($winduid);
		PwUpload::upload($certificateUploadBehavior);
		$certificateInfo = $authService->getCertificateInfoByUid($winduid);
		$data = array(
					'type' => $certificate['type'],
					'number' => $certificate['number'],
					'createtime' => $timestamp,
					'state' => 1,//待审核
				);
		if (!S::isArray($certificateInfo) || !$certificateInfo['attach1'] && !$certificateInfo['attach2']) {
			/*
			$data['uid'] = $winduid;
			$authService->addCertificateInfo($data);
			*/
			Showmsg("请上传至少一张证件图片再提交");
		} else {
			$authService->updateCertificateInfo($data,$certificateInfo['id']);

		}
		refreshto("profile.php?action=auth",'提交成功，请等待管理员审核');
	}
}

require_once uTemplate::PrintEot('profile_auth');
pwOutPut();
?>