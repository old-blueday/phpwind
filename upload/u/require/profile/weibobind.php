<?php
!function_exists('readover') && exit('Forbidden');

InitGP(array('t', 'type'));

$siteBindService = L::loadClass('WeiboSiteBindService', 'sns/weibotoplatform/service'); /* @var $siteBindService PW_WeiboSiteBindService */
if (!$siteBindService->isOpen()) Showmsg('站点还未开启帐号通应用');

if (empty($t)) {
	$userBindService = L::loadClass('WeiboUserBindService', 'sns/weibotoplatform/service'); /* @var $userBindService PW_WeiboUserBindService */
	$userBindList = $userBindService->getBindList($winduid);
	
	$weiboLoginService = L::loadClass('WeiboLoginService', 'sns/weibotoplatform/service'); /* @var $weiboLoginService PW_WeiboLoginService */
	$isNotResetPassword = $weiboLoginService->isLoginUserNotResetPassword($winduid);

	$syncer = L::loadClass('WeiboSyncer', 'sns/weibotoplatform'); /* @var $syncer PW_WeiboSyncer */
	$syncSetting = $syncer->getUserWeiboSyncSetting($winduid);
	ifchecked('article_issync', $syncSetting['article']);
	ifchecked('diary_issync', $syncSetting['diary']);
	ifchecked('photos_issync', $syncSetting['photos']);
	ifchecked('group_issync', $syncSetting['group']);
	ifchecked('transmit_issync', $syncSetting['transmit']);
	ifchecked('comment_issync', $syncSetting['comment']);
	
	require_once(R_P.'require/showimg.php');
	list($faceurl) = showfacedesign($winddb['icon'],1,'m');
	
	require_once uTemplate::printEot('profile_weibobind');
	pwOutPut();
} elseif ($t == 'tounbind') {
	define('AJAX', 1);
	
	if (!$siteBindService->isBind($type)) Showmsg('站点还未支持该类型站点的绑定，或者绑定类型错误');
	
	$userBindService = L::loadClass('WeiboUserBindService', 'sns/weibotoplatform/service'); /* @var $userBindService PW_WeiboUserBindService */
	if (!$userBindService->isBind($winduid, $type)) Showmsg('你还未绑定该站点，无需创建密码');
	
	$weiboLoginService = L::loadClass('WeiboLoginService', 'sns/weibotoplatform/service'); /* @var $weiboLoginService PW_WeiboLoginService */
	$isNotResetPassword = $weiboLoginService->isLoginUserNotResetPassword($winduid);
	
	require_once uTemplate::printEot('profile_weibobind_ajax');
	ajax_footer();
} elseif ($t == 'unbind') {
	define('AJAX', 1);
	
	if (!$siteBindService->isBind($type)) Showmsg('站点还未支持该类型站点的绑定，或者绑定类型错误');
	
	$userBindService = L::loadClass('WeiboUserBindService', 'sns/weibotoplatform/service'); /* @var $userBindService PW_WeiboUserBindService */
	if (!$userBindService->isBind($winduid, $type)) Showmsg('你还未绑定该站点，无需解绑');
	
	$weiboLoginService = L::loadClass('WeiboLoginService', 'sns/weibotoplatform/service'); /* @var $weiboLoginService PW_WeiboLoginService */
	if ($weiboLoginService->isLoginUserNotResetPassword($winduid)) Showmsg('你的帐号未创建密码，请创建密码后再解除绑定');
	
	$isSuccess = $userBindService->unbind($winduid, $type);
	
	if (!$isSuccess) Showmsg("解绑失败，请重试");
	
	echo "你的解绑操作成功\tjump\tprofile.php?action=weibobind";
	ajax_footer();
} elseif ($t == 'resetandunbind') {
	define('AJAX', 1);
	
	if (!$siteBindService->isBind($type)) Showmsg('站点还未支持该类型站点的绑定，或者绑定类型错误');
	
	$userBindService = L::loadClass('WeiboUserBindService', 'sns/weibotoplatform/service'); /* @var $userBindService PW_WeiboUserBindService */
	if (!$userBindService->isBind($winduid, $type)) Showmsg('你还未绑定该站点，无需解绑');
	
	PostCheck();
	InitGP(array('resetpwd', 'resetpwd_repeat'), 'P');
	$isSuccess = weiboResetUserPassword($winduid, $resetpwd, $resetpwd_repeat);
	if (!$isSuccess) Showmsg('你已创建密码，或者新旧密码相同');
	
	$isSuccess = $userBindService->unbind($winduid, $type);
	echo $isSuccess ? "创建密码和解绑操作成功" : "密码已创建，但解绑操作失败，请重试";
	echo "\tjump\tprofile.php?action=weibobind";
	ajax_footer();
} elseif ($t == 'setsync') {
	PostCheck();
	InitGP(array('article_issync', 'diary_issync', 'photos_issync', 'group_issync', 'transmit_issync', 'comment_issync'), 'P', 2);
	$syncSetting = array(
		'article' => (bool) $article_issync,
		'diary' => (bool) $diary_issync,
		'photos' => (bool) $photos_issync,
		'group' => (bool) $group_issync,
		'transmit' => (bool) $transmit_issync,
		'comment' => (bool) $comment_issync,
	);
	$syncer = L::loadClass('WeiboSyncer', 'sns/weibotoplatform'); /* @var $syncer PW_WeiboSyncer */
	$syncer->updateUserWeiboSyncSetting($winduid, $syncSetting);

	refreshto('profile.php?action=weibobind','operate_success', 2, true);
} elseif ($t == 'resetpwd') {
	$weiboLoginService = L::loadClass('WeiboLoginService', 'sns/weibotoplatform/service'); /* @var $weiboLoginService PW_WeiboLoginService */
	$isNotResetPassword = $weiboLoginService->isLoginUserNotResetPassword($winduid);
	if (!$isNotResetPassword) Showmsg('你已经创建密码，不需要再次创建');
	
	require_once uTemplate::printEot('profile_weibobind');
	pwOutPut();
} elseif ($t == 'setpassword') {
	$weiboLoginService = L::loadClass('WeiboLoginService', 'sns/weibotoplatform/service'); /* @var $weiboLoginService PW_WeiboLoginService */
	$isNotResetPassword = $weiboLoginService->isLoginUserNotResetPassword($winduid);
	if (!$isNotResetPassword) Showmsg('你已经创建密码，不需要再次创建');
	
	PostCheck();
	InitGP(array('resetpwd', 'resetpwd_repeat'), 'P');
	$isSuccess = weiboResetUserPassword($winduid, $resetpwd, $resetpwd_repeat);
	if (!$isSuccess) Showmsg('你已创建密码，或者新旧密码相同');
	
	refreshto('profile.php?action=weibobind','创建密码成功!', 2, true);
} elseif ($t == 'bindsuccess') {
	extract(L::style('',$skinco));
	
	$msg_info = '绑定帐号成功（窗口将自动关闭）';
	require_once uTemplate::printEot('profile_privacy_bindsuccess');
	pwOutPut();
} elseif ($t == 'callback') {
	$userBindService = L::loadClass('WeiboUserBindService', 'sns/weibotoplatform/service'); /* @var $userBindService PW_WeiboUserBindService */
	
	$params = array_merge($_GET, $_POST);
	unset($params['action'], $params['t']);
	$isSuccess = $userBindService->callback($winduid, $params);
	if (true !== $isSuccess) Showmsg($isSuccess ? $isSuccess : '绑定失败，请重试');
	
	ObHeader('profile.php?action=weibobind&t=bindsuccess');
}

function ifchecked($out, $var) {
	$GLOBALS[$out] = $var ? ' checked' : '';
}
function weiboResetUserPassword($userId, $password, $repeatPassword) {
	global $db_ckpath, $db_ckdomain;
	
	if ('' == $password || '' == $repeatPassword) Showmsg('创建密码不能为空');
	
	$rg_config  = L::reg();
	list($rg_regminpwd,$rg_regmaxpwd) = explode("\t", $rg_config['rg_pwdlen']);
	$register = L::loadClass('Register', 'user');
	$register->checkPwd($password, $repeatPassword);
	
	$weiboLoginService = L::loadClass('WeiboLoginService', 'sns/weibotoplatform/service'); /* @var $weiboLoginService PW_WeiboLoginService */
	$isSuccess = $weiboLoginService->resetLoginUserPassword($userId, $password);
	if (!$isSuccess) return false;
	
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$user = $userService->get($userId);
	Cookie("winduser",StrCode($userId."\t".PwdCode($user['password'])."\t".$user['safecv']));
	Cookie("ck_info",$db_ckpath."\t".$db_ckdomain);
	Cookie('lastvisit','',0);
	//自动获取勋章_start
	require_once(R_P.'require/functions.php');
	doMedalBehavior($userId,'continue_login');
	//自动获取勋章_end
	return true;
}
