<?php
!function_exists('readover') && exit('Forbidden');

if (!$_POST['step']) {
	
	/* modify for authentication */
	$isAuthMobile = getstatus($userdb['userstatus'], PW_USERSTATUS_AUTHMOBILE);
	if ($db_authstate) {
		if ($isAuthMobile && $userdb['authmobile']) {//将手机号的中间四位隐掉
			$authmobile = $userdb['authmobile'];
			for ($i = 3; $i<=6; $i++) {
				$authmobile{$i} = '*';
			}
		}
	}
	/* modify for authentication */

	$customFieldsString = getCustomFieldsAndDefaultValue('contact');
	
	require_once uTemplate::PrintEot('info_link');
	pwOutPut();

} elseif ($_POST['step'] == '2') {

	PostCheck();
	S::slashes($userdb);
	S::gp(array('prooicq', 'proaliww','proicq','proyahoo', 'promsn', 'proauthmobile','oicq'),'P');

	//联系方式 处理
	//$prooicq && !is_numeric($prooicq) && Showmsg('illegal_OICQ');
	$proicq && !is_numeric($proicq) && Showmsg('illegal_OICQ');
	$oicq && !is_numeric($oicq) && Showmsg('QQ号码只能输入数字');

	//update member
	$pwSQL = array(
		//'aliww' => $proaliww, 
		'icq' => $proicq, 
		//'yahoo' => $proyahoo, 'msn' => $promsn
	);

	/* modify for authentication */
	if (!getstatus($userdb['userstatus'], PW_USERSTATUS_AUTHMOBILE)) {
		$proauthmobile && !preg_match('/^1(3|5|8)[0-9]{9}$/', $proauthmobile) && Showmsg('illegal_authmobile');
		$pwSQL['authmobile'] = $proauthmobile;
	}
	/* modify for authentication */

	$userService->update($winduid, $pwSQL);
	//* $_cache = getDatastore();
	//* $_cache->delete('UID_'.$winduid);
	
	//update customerfield data
	$customfieldService = L::loadClass('CustomerFieldService', 'user'); /* @var $customfieldService PW_CustomerFieldService */
	$customfieldService->saveProfileCustomerData('contact');
	
	//job sign
	initJob($winduid,"doUpdatedata");
	refreshto("profile.php?action=modify&info_type=$info_type",'operate_success',2,true);
}