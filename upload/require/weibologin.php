<?php
!function_exists('readover') && exit('Forbidden');
(!defined('SCR') || SCR != 'login') && exit('Forbidden');

//TODO refactor all

InitGP(array('type'));

$weiboLoginService = L::loadClass('WeiboLoginService', 'sns/weibotoplatform/service'); /* @var $weiboLoginService PW_WeiboLoginService */
if ($action == 'weibologin') {
	if (!$weiboLoginService->isWayAllowLogin($type)) Showmsg('登录类型错误，请重试');
	
	InitGP(array('from'));
	$from = getLoginReferer($from);
	
	$sessionId = GetCookie(PW_WEIBO_LOGIN_COOKIE_NAME);
	if (!$sessionId || !$weiboLoginService->getLoginSession($sessionId)) {
		$sessionId = $weiboLoginService->createLoginSession();
	}
	$weiboLoginService->updateLoginSession($sessionId, array('httpReferer' => $from, 'type' => $type));
	
	Cookie(PW_WEIBO_LOGIN_COOKIE_NAME, $sessionId, $timestamp + PW_WEIBO_LOGIN_COOKIE_EXIPRE);
	
	ObHeader($weiboLoginService->getLoginUrl($sessionId, $type));
} elseif ($action == 'weibologinregister') {
	$sessionId = GetCookie(PW_WEIBO_LOGIN_COOKIE_NAME);
	$sessionInfo = $weiboLoginService->getLoginSession($sessionId);
	if (!$sessionId || !$sessionInfo || !$sessionInfo['sessiondata']['sinaUser']) refreshto('login.php', '登录会话超时，请重试', 3);
	$weiboUser = $sessionInfo['sessiondata']['sinaUser']; //for compatible
	
	$type = $sessionInfo['sessiondata']['type'];
	$loginWay = $weiboLoginService->getLoginWay($type);
	if (!$loginWay) Showmsg('登录类型错误，请重试');
	
	$weiboSiteBindInfoService = L::loadClass('WeiboSiteBindInfoService', 'sns/weibotoplatform/service'); /* @var $weiboSiteBindInfoService PW_WeiboSiteBindInfoService */
	$weiboAccount = $weiboSiteBindInfoService->getOfficalAccount($type);
	
	/**
	 * 注册初始化
	 */
	require_once(R_P.'require/functions.php');
	$rg_config  = L::reg(); //注册配置
	$inv_config = L::config(null, 'inv_config'); //邀请注册配置
	
	if (isRegClose()) Showmsg($rg_config['rg_whyregclose']); //注册是否关闭
	
	list($regminname,$regmaxname) = explode("\t", $rg_config['rg_namelen']); //用户名长度配置
	list($rg_regminpwd,$rg_regmaxpwd) = explode("\t", $rg_config['rg_pwdlen']); //密码长度配置
	
	if ($db_pptifopen && $db_ppttype == 'client') Showmsg('passport_register'); //通行证注册控制
	list($regq, , , ,$showq) = explode("\t", $db_qcheck); //验证问题配置：是否显示验证问题，是否显示答案
	
	if ($rg_config['rg_allowsameip'] && file_exists(D_P.'data/bbscache/ip_cache.php') && !in_array($step,array('finish','permit'))) {
		$ipdata  = readover(D_P.'data/bbscache/ip_cache.php');
		$pretime = (int)substr($ipdata,13,10);
		if ($timestamp - $pretime > $rg_config['rg_allowsameip'] * 3600) {
			P_unlink(D_P.'data/bbscache/ip_cache.php');
		} elseif (strpos($ipdata,"<$onlineip>") !== false) {
			Showmsg('reg_limit');
		}
	}
	
	InitGP(array('step'));
	if ($step == 'doreg') {
		$regq = 0; //验证问题去除，自动通过
		PostCheck(0, $db_gdcheck & 1, $regq, 0); //此函数变态之极。参数：是否检查验证码，是否检查验证问题
		if ($_GET['method'] || (!($db_gdcheck & 1) && $_POST['gdcode']) ||
			(!($db_ckquestion & 1) && ($_POST['qanswer'] || $_POST['qkey']))
		) {
			Showmsg('undefined_action');
		}
	
		S::gp(array('regreason','regname','regpwd','regpwdrepeat','regemail','customdata', 'regemailtoall','rgpermit','authmobile','authverify'),'P');
		S::gp(array('question','customquest','answer'),'P');
		S::gp(array('useweiboavatar', 'isfollow'),'P');
		S::gp(array('invcode'),'P');
		
		if ($db_authstate && $db_authreg) {
			$authService = L::loadClass('Authentication', 'user');
			$status = $authService->checkverify($authmobile, ip2long($onlineip), $authverify);
			!$status && Showmsg('手机验证码填写错误');
		}
		
		!$rgpermit && Showmsg('reg_permit_notchecked');
		
		$regreason = '通过帐号通注册'; //自动通过
		$regpwd = $regpwdrepeat = $weiboLoginService->generateLoginTmpPassword();
	
		$sRegpwd = $regpwd;
		$register = L::loadClass('Register', 'user');
		/** @var $register PW_Register */
	
		$rg_config['rg_allowregister']==2 && $register->checkInv($invcode);
		$register->checkSameNP($regname, $regpwd);
	
		$register->setStatus(11);
		$regemailtoall && $register->setStatus(7);
		$register->setName($regname);
		$register->setPwd($regpwd, $regpwdrepeat);
		$register->setEmail($regemail);
		$register->setSafecv($question, $customquest, $answer);
		$register->setReason($regreason);
		$register->setCustomdata($customdata);
		$register->data['yz'] = 1; //round the email check
		$register->execute();
	
		if ($rg_config['rg_allowregister']==2) {
			$register->disposeInv();
		}
		list($winduid, $rgyz, $safecv) = $register->getRegUser();
		//用户自定义字段
		$customfieldService = L::loadClass('CustomerFieldService','user');/* @var $customfieldService PW_CustomerFieldService */
		$customfieldService->saveRegisterCustomerData();
		
		$windid  = $regname;
		$windpwd = md5($regpwd);

		if ($db_authstate && $db_authreg) {
			$authService->syncuser($authmobile, ip2long($onlineip), $authverify, $winduid, $windid, 'register');
			$authService->setCurrentInfo('register');
			$userService = L::loadClass('userservice', 'user');/* @var $register PW_Register */
			$userService->update($winduid,array('authmobile' => $authmobile));
			$userService->setUserStatus($winduid, PW_USERSTATUS_AUTHMOBILE, true);
			//颁发勋章
			if ($db_md_ifopen) {
				$medalService = L::loadClass('medalservice','medal');
				$medalService->awardMedalByIdentify($winduid,'shimingrenzheng');
			}
		}
		
		if ($rg_config['rg_allowsameip']) {
			if (file_exists(D_P.'data/bbscache/ip_cache.php')) {
				writeover(D_P.'data/bbscache/ip_cache.php',"<$onlineip>","ab");
			} else {
				writeover(D_P.'data/bbscache/ip_cache.php',"<?php die;?><$timestamp>\n<$onlineip>");
			}
		}
		
		if ($useweiboavatar) {
			require_once(R_P.'require/showimg.php');
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userService->update($winduid, array('icon' => setIcon($weiboUser['avatar'], 2, array('', '', 80, 80))));
		}
		
		$isSuccess = $weiboLoginService->bindNewLoginUser($winduid, $sessionInfo['sessiondata']['platformSessionId'], array('randomPassword' => $regpwd, 'type' => $type));
		
		$userBindService = L::loadClass('WeiboUserBindService', 'sns/weibotoplatform/service'); /* @var $userBindService PW_WeiboUserBindService */
		if ($weiboAccount && $isfollow) $userBindService->follow($type, $winduid);
		
		Cookie("winduser",StrCode($winduid."\t".PwdCode($windpwd)."\t".$safecv));
		Cookie("ck_info",$db_ckpath."\t".$db_ckdomain);
		Cookie('lastvisit','',0);
		//自动获取勋章_start
		require_once(R_P.'require/functions.php');
		doMedalBehavior($winduid,'continue_login');
		//自动获取勋章_end
		$verifyhash = GetVerify($winduid);
		if ($rg_config['rg_regguide']) {
			$option = $useweiboavatar ? 2 : 1;
			ObHeader("$db_registerfile?step=finish&option=$option&verify=$verifyhash");
		}
		ObHeader("login.php?action=weibologinregister&step=finish&verify=$verifyhash");
	} elseif ($step == 'finish') {
		$loginUserInfo = $weiboLoginService->getLoginUserInfo($winduid);
		if (!$loginUserInfo) Showmsg('注册失败');
	
		if (empty($jumpurl) || false !== strpos($jumpurl, $regurl)) {
			$jumpurl = isset($sessionInfo['sessiondata']['httpReferer']) ? $sessionInfo['sessiondata']['httpReferer'] : $db_bfn;
		}
		
		require_once(PrintEot('weibologin_register'));
		footer();
	}
	
	if ($db_authstate && $db_authreg) {
		$authService = L::loadClass('Authentication', 'user');
		list($authStep, $remainTime, $waitTime, $mobile) = $authService->getStatus('register');
		$authStep_1 = $authStep_2 = 'none';
		${'authStep_' . $authStep} = '';
	}
	!$rg_config['rg_timestart'] && $rg_config['rg_timestart'] = 1960;
	!$rg_config['rg_timeend'] && $rg_config['rg_timeend'] = 2000;
	$img = @opendir(S::escapeDir("$imgdir/face"));
	while ($imagearray = @readdir($img)) {
		if ($imagearray!="." && $imagearray!=".." && $imagearray!="" && $imagearray!="none.gif") {
			$imgselect.="<option value='$imagearray'>$imagearray</option>";
		}
	}
	@closedir($img);
	//require_once(R_P.'require/header.php');
	$custominfo = unserialize($db_union[7]);
	$customfield = L::config('customfield','customfield');
	$customfieldService = L::loadClass('CustomerFieldService','user');
	require_once(PrintEot('weibologin_register'));footer();
} elseif ($action == 'weibologinbind') {
	$sessionId = GetCookie(PW_WEIBO_LOGIN_COOKIE_NAME);
	$sessionInfo = $weiboLoginService->getLoginSession($sessionId);
	if (!$sessionId || !$sessionInfo || !$sessionInfo['sessiondata']['sinaUser']) refreshto('login.php', '登录会话超时，请重试', 3);
	$weiboUser = $sessionInfo['sessiondata']['sinaUser']; //for compatible
	list(,$_LoginInfo) = pwNavBar();
	
	$type = $sessionInfo['sessiondata']['type'];
	$loginWay = $weiboLoginService->getLoginWay($type);
	if (!$loginWay) Showmsg('登录类型错误，请重试');
	
	$weiboSiteBindInfoService = L::loadClass('WeiboSiteBindInfoService', 'sns/weibotoplatform/service'); /* @var $weiboSiteBindInfoService PW_WeiboSiteBindInfoService */
	$weiboAccount = $weiboSiteBindInfoService->getOfficalAccount($type);
	
	InitGP(array('step'));
	if (2 == $step) {
		$loginq = 0; //验证问题去除，自动通过
		$db_gdcheck = 0; //验证码去除，自动通过
		PostCheck(0,$db_gdcheck & 2,$loginq,0); //from login.php
		require_once(R_P . 'require/checkpass.php');

		InitGP(array('pwuser','pwpwd','question','customquest','answer','cktime','hideid','jumpurl','lgt','keepyear'),'P');
		InitGP(array('isfollow'));
		if (!$pwuser || !$pwpwd) Showmsg('login_empty');
		
		$loginUser = array('username' => $pwuser, 'password' => md5($pwpwd));
		$loginUser['safecv'] = $db_ifsafecv ? questcode($question, $customquest, $answer) : '';
		list($winduid, $groupid, $windpwd, $showmsginfo) = processLogin(null, $loginUser, $cktime, $lgt);
		
		require_once(file_exists(D_P."data/groupdb/group_$groupid.php") 
			? Pcv(D_P."data/groupdb/group_$groupid.php") : D_P."data/groupdb/group_1.php");
		($_G['allowhide'] && $hideid) ? Cookie('hideid',"1",$cktime) : Loginipwrite($winduid);
	
		if (GetCookie('o_invite') && $db_modes['o']['ifopen'] == 1) {
			list($o_u,$hash,$app) = explode("\t",GetCookie('o_invite'));
			if (is_numeric($o_u) && strlen($hash) == 18) {
				require_once(R_P.'require/o_invite.php');
			}
		}
		if (empty($jumpurl) || false !== strpos($jumpurl, $regurl)) {
			$jumpurl = isset($sessionInfo['sessiondata']['httpReferer']) ? $sessionInfo['sessiondata']['httpReferer'] : $db_bfn;
		}
		//passport
		if ($db_pptifopen && $db_ppttype == 'server' && ($db_ppturls || $forward)) {
			$tmp = $jumpurl;
			$jumpurl = $forward ? $forward : $db_ppturls;
			$forward = $tmp;
			//TODO 这里面有obheader，用到$action
			require_once(R_P.'require/passport_server.php');
		}
		//passport
		
		$isSuccess = $weiboLoginService->bindExistLoginUser($winduid, $sessionInfo['sessiondata']['platformSessionId']);
		
		$userBindService = L::loadClass('WeiboUserBindService', 'sns/weibotoplatform/service'); /* @var $userBindService PW_WeiboUserBindService */
		if ($weiboAccount && $isfollow) $userBindService->follow($type, $winduid);
		
		refreshto($jumpurl,'have_login');
	}
	
	$arr_logintype = array();
	if ($db_logintype) {
		for ($i = 0; $i < 3; $i++) {
			if ($db_logintype & pow(2,$i)) $arr_logintype[] = $i;
		}
	} else {
		$arr_logintype[0] = 0;
	}
	
	require_once(PrintEot('weibologin_bind'));
	footer();
} elseif ($action == 'weibologinroute') {
	$sessionId = GetCookie(PW_WEIBO_LOGIN_COOKIE_NAME);
	$sessionInfo = $weiboLoginService->getLoginSession($sessionId);
	if (!$sessionId || !$sessionInfo || !$sessionInfo['sessiondata']['platformSessionId']) Showmsg('验证会话错误，请重试');
	
	$type = $sessionInfo['sessiondata']['type'];
	$loginWay = $weiboLoginService->getLoginWay($type);
	if (!$loginWay) Showmsg('登录类型错误，请重试');
	
	if (!$sessionInfo['sessiondata']['isBound']) {
		$jumpurl = !isRegClose() ? 'login.php?action=weibologinregister' : 'login.php?action=weibologinbind';
		//$jumpnow = 1;
		$msg_info = '使用' . $loginWay['accountTitle'] . '认证通过（窗口将自动关闭）';
		extract(L::style('',$skinco));
		require_once PrintEot('weibologin_notice');
		pwOutPut();
		exit;
	}

	$userId = $weiboLoginService->fetchBoundUser($sessionInfo['sessiondata']['platformSessionId']);
	if (!$userId) Showmsg('使用' . $loginWay['accountTitle'] . '自动登录失败，请重试');
	
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	if (!$userService->get($userId)) {
		$userBindService = L::loadClass('WeiboUserBindService', 'sns/weibotoplatform/service'); /* @var $userBindService PW_WeiboUserBindService */
		$userBindService->unbind($userId, $type);
		Showmsg('用户在站点已删除，请重试');
	}
		
	list($winduid, $groupid, $windpwd, $showmsginfo) = processLogin($userId);

	require_once(file_exists(D_P."data/groupdb/group_$groupid.php") 
		? Pcv(D_P."data/groupdb/group_$groupid.php") : D_P."data/groupdb/group_1.php");
	Loginipwrite($winduid);

	if (GetCookie('o_invite') && $db_modes['o']['ifopen'] == 1) {
		list($o_u,$hash,$app) = explode("\t",GetCookie('o_invite'));
		if (is_numeric($o_u) && strlen($hash) == 18) {
			require_once(R_P.'require/o_invite.php');
		}
	}
	$jumpurl = isset($sessionInfo['sessiondata']['httpReferer']) ? $sessionInfo['sessiondata']['httpReferer'] : $db_bfn;
	//passport
	if ($db_pptifopen && $db_ppttype == 'server' && ($db_ppturls || $forward)) {
		$tmp = $jumpurl;
		$jumpurl = $forward ? $forward : $db_ppturls;
		$forward = $tmp;
		//TODO 这里面有obheader，用到$action
		require_once(R_P.'require/passport_server.php');
	}
	//passport
	
	$msg_info = '使用' . $loginWay['accountTitle'] . '登录成功（窗口将自动关闭）';
	extract(L::style('',$skinco));
	require_once PrintEot('weibologin_notice');
	pwOutPut();
} elseif ($action == 'weibologincallback') {
	$params = array_merge($_GET, $_POST);
	unset($params['action']);
	$isSuccess = $weiboLoginService->callback($params);
	if (true !== $isSuccess) Showmsg($isSuccess ? $isSuccess : '登录失败，请重试');
	
	ObHeader('login.php?action=weibologinroute');
}


function processLogin($userId, $user = null, $cktime = '31536000', $lgt = 0) {
	global $timestamp, $db_ckpath, $db_ckdomain, $db_autoban;
	if (!$user) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$user = $userService->get($userId, true, true);
	}
	
	$pwuser = $user['username'];
	$md5_pwpwd = $user['password'];
	$safecv = $user['safecv'];
	
	require_once(R_P . 'require/checkpass.php');
	$logininfo = checkpass($pwuser, $md5_pwpwd, $safecv, $lgt);
	if (!is_array($logininfo)) {
		Showmsg($logininfo);
	}
	list($winduid, , $windpwd, ) = $logininfo;
		
	/*update cache*/
	$_cache = getDatastore();
	$_cache->delete("UID_".$winduid);
	
	$cktime != 0 && $cktime += $timestamp;
	Cookie("winduser",StrCode($winduid."\t".$windpwd."\t".$safecv),$cktime);
	Cookie("ck_info",$db_ckpath."\t".$db_ckdomain);
	Cookie('lastvisit','',0);//将$lastvist清空以将刚注册的会员加入今日到访会员中
	//自动获取勋章_start
	require_once(R_P.'require/functions.php');
	doMedalBehavior($winduid,'continue_login');
	//自动获取勋章_end
	
	if ($db_autoban) {
		require_once(R_P.'require/autoban.php');
		autoban($winduid);
	}
	
	return $logininfo;
}

function isRegClose() {
	global $timestamp;
	$rg_config  = L::reg();
	return $rg_config['rg_allowregister'] == 0 
		|| ($rg_config['rg_registertype'] == 1 && date('j',$timestamp) != $rg_config['rg_regmon']) 
		|| ($rg_config['rg_registertype'] == 2 && date('w',$timestamp) != $rg_config['rg_regweek']);
}

function getLoginReferer($fromUrl) {
	global $pwServer, $db_bbsurl, $db_bfn, $db_registerfile;
	$default = $db_bbsurl.'/'.$db_bfn;
	
	$fromUrl = $pwServer['HTTP_REFERER'] ? $pwServer['HTTP_REFERER'] : ($fromUrl ? $fromUrl : $db_bbsurl.'/'.$db_bfn);
	$fromUrl = str_replace(array('&#61;','&amp;'), array('=','&'), $fromUrl);
	if (strpos($fromUrl, 'login.php') !== false || strpos($fromUrl, $db_registerfile) !== false) $fromUrl = $default;
	
	$parsed = parse_url($fromUrl);
	if ($parsed['host']) {
		list($httpHost) = explode(':', $pwServer['HTTP_HOST']);
		if ($parsed['host'] != $httpHost) $fromUrl = $default;
	}
	
	return $fromUrl;
}

