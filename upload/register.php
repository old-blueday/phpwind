<?php
define('SCR','register');
require_once('global.php');
require_once(R_P.'require/functions.php');

require (L::style('', $skinco, true));
if ("wind" != $tplpath && file_exists(D_P.'data/style/'.$tplpath.'_css.htm')) {
	$css_path = D_P.'data/style/'.$tplpath.'_css.htm';
} else{
	$css_path = D_P.'data/style/wind_css.htm';
}

$rg_config  = L::reg();
$inv_config = L::config(null, 'inv_config');
list($regminname,$regmaxname) = explode("\t", $rg_config['rg_namelen']);
list($rg_regminpwd,$rg_regmaxpwd) = explode("\t", $rg_config['rg_pwdlen']);

if ($db_pptifopen && $db_ppttype == 'client') {
	Showmsg('passport_register');
}
list(,$showq) = explode("\t", $db_qcheck);

if (S::getGP('action','P') == 'regcheck') {
	$registerCheckService = L::loadClass('registercheck', 'user', true);
	S::gp(array('type'),'P');

	if ($type == 'regname') {
		S::gp('username','P');
		echo $registerCheckService->checkUsername($username);
	} elseif ($type == 'regemail') {
		sleep(1);
		S::gp('email','P');
		echo $registerCheckService->checkEmail($email);
	} elseif ($type == 'reggdcode') {
		S::gp('gdcode','P');
		$gdcode = pwConvert(rawurldecode($gdcode), $db_charset, 'utf-8');
		echo $registerCheckService->checkGdcode($gdcode);
	} elseif ($type == 'qanswer') {
		S::gp(array('answer','question'),'P');
		echo $registerCheckService->checkQanswer($answer, $question);
	} elseif ($type == 'invcode') {
		S::gp('invcode','P');
		echo $registerCheckService->checkInvcode($invcode);
	} elseif ($type == 'customerfield') {
		S::gp(array('fieldname'),'P');
		$value = str_replace('%26', '&amp;' ,S::escapeChar(S::getGP('value')));
		echo $registerCheckService->checkCustomerField($fieldname,$value);
	} elseif ($type == 'all') {
		S::gp(array('data'));
		$data = pwHtmlspecialchars_decode(stripslashes($data));
		require_once(R_P . 'lib/utility/json.class.php');
		$json = new Services_JSON(true);
		$data = $json->decode($data);
		$returnArray = array();
		foreach ($data as $value) {
			switch ($value[1]) {
				case 'regname' :
					$return = $registerCheckService->checkUsername($value[2]);
					break;
				case 'regemail' :
					$return = $registerCheckService->checkEmail($value[2]);
					break;
				case 'reggdcode' :
					$value[2] = pwConvert(rawurldecode($value[2]), $db_charset, 'utf-8');
					$return = $registerCheckService->checkGdcode($value[2]);
					break;
				case 'qanswer' :
					list($question, $answer) = explode('|', $value[2]);
					$return = $registerCheckService->checkQanswer($answer, $question);
					break;
				case 'invcode' :
					$return = $registerCheckService->checkInvcode($value[2]);
					break;
				case 'customerfield' :
					list($fieldname,$v) = explode('|', $value[2]);
					$v = S::escapeChar(urldecode($v));
					$return = $registerCheckService->checkCustomerField($fieldname,$v);
					break;
			}
			$return && $returnArray[$value[0]] = $return;
		}
		if (!S::isArray($returnArray)) {
			echo 'success';
		} else {
			echo pwJsonEncode($returnArray);
		}
	}
	ajax_footer();

} elseif (S::getGP('action','P') == 'pay') {

	//* include_once pwCache::getPath(D_P."data/bbscache/inv_config.php");
	//* include_once pwCache::getPath(D_P.'data/bbscache/ol_config.php');
	pwCache::getData(D_P."data/bbscache/inv_config.php");
	pwCache::getData(D_P.'data/bbscache/ol_config.php');
	
	if ($_POST['step'] == '3') {
		S::gp(array('invnum','email'));
		if (!is_numeric($invnum) ||$invnum<1) $invnum = 1;
		$order_no =str_pad('0',10,"0",STR_PAD_LEFT).get_date($timestamp,'YmdHis').num_rand(5);
		$rt = array();
		if ($rg_config['rg_emailtype'] == 1 && $rg_config['rg_email']) {
			$e_check = 0;
			$e_limit = explode(',', $rg_config['rg_email']);
			foreach ($e_limit as $key => $val) {
				if (strpos($email,"@".$val) !== false) {
					$e_check = 1;
					break;
				}
			}
			if ($e_check == 0){
				Showmsg('电子邮箱不是系统指定的邮箱地址，不能注册!');
			}
		}
		if ($rg_config['rg_emailtype'] == 2 && $rg_config['rg_banemail']){
			$e_check = 0;
			$e_limit = explode(',', $rg_config['rg_banemail']);
			foreach ($e_limit as $key => $val) {
				if (strpos($email,"@".$val) !== false) {
					$e_check = 1;
					break;
				}
			}
			if ($e_check == 1){
				Showmsg('请输入正确的电子邮箱地址!');
			}
		}
		if (!preg_match('/^[a-z0-9\-_\.]{2,}@([a-z\-0-9]+\.)+[a-z]{2,3}$/i', $email) ){
			Showmsg('电子邮箱地址格式有误，请重新填写!');
		}
		$db->update("INSERT INTO pw_clientorder SET " . S::sqlSingle(array(
			'order_no'	=> $order_no,
			'type'		=> 4,
			'uid'		=> 0,
			'price'		=> $inv_price,
			'payemail'	=> $email,
			'number'	=> $invnum,
			'date'		=> $timestamp,
			'state'		=> 0,
		)));
	
		if (!$ol_payto) {
			Showmsg('olpay_alipayerror');
		}
		require_once(R_P.'require/onlinepay.php');
		$olpay = new OnlinePay($ol_payto);
		ObHeader($olpay->alipayurl($order_no, $invnum * $inv_price, 4));				
	}
} elseif (GetGP('action','P') == 'auth') {

	/*实名认证获取验证码*/
	InitGP('mobile');
	$authService = L::loadClass('Authentication', 'user');

	if ($_POST['step'] == '1') {
		
		$status = $authService->getverify('register', $mobile, ip2long($onlineip),false,'register');
		echo $status;
	
	} elseif ($_POST['step'] == '2') {

		InitGP('authverify');
		$status = $authService->checkverify($mobile, ip2long($onlineip), $authverify);
		echo $status ? 0 : 5;
	}
	ajax_footer();
}
if ($rg_config['rg_allowregister'] == 0 || ($rg_config['rg_registertype'] == 1 && date('j',$timestamp) != $rg_config['rg_regmon']) || ($rg_config['rg_registertype'] == 2 && date('w',$timestamp) != $rg_config['rg_regweek'])) {
	Showmsg($rg_config['rg_whyregclose']);
}
S::gp(array('forward')); !$db_pptifopen && $forward = '';
S::gp(array('invcode','step','action'));

if ($rg_config['rg_allowsameip'] && file_exists(D_P.'data/bbscache/ip_cache.php') && !in_array($step,array('finish','permit'))) {
	$ipdata  = readover(D_P.'data/bbscache/ip_cache.php');
	$pretime = (int)substr($ipdata,13,10);
	if ($timestamp - $pretime > $rg_config['rg_allowsameip'] * 3600) {
		//* P_unlink(D_P.'data/bbscache/ip_cache.php');
		pwCache::deleteData(D_P.'data/bbscache/ip_cache.php');
	} elseif (strpos($ipdata,"<$onlineip>") !== false) {
		Showmsg('reg_limit');
	}
}

$step != 'finish' && $groupid != 'guest' && Showmsg('reg_repeat');

if (!$step) {
	
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
	if ($customfield) {
		$customfieldService = L::loadClass('CustomerFieldService','user');
	}
	require_once(PrintEot('register'));footer();

} elseif ($step == 2) {


	PostCheck(0, $db_gdcheck & 1, $db_ckquestion & 1 && $db_question, 0);
	if ($_GET['method'] || (!($db_gdcheck & 1) && $_POST['gdcode']) ||
		(!($db_ckquestion & 1) && ($_POST['qanswer'] || $_POST['qkey']))/* ||
		($db_xforwardip && $_POST['_hexie'] != GetVerify($onlineip))*/
	) {
		Showmsg('undefined_action');
	}

	S::gp(array('regreason','regname','regpwd','regpwdrepeat','regemail','customdata', 'regemailtoall','rgpermit','authmobile','authverify'),'P');
	S::gp(array('question','customquest','answer'),'P');
	
	if ($db_authstate && $db_authreg) {
		$authService = L::loadClass('Authentication', 'user');
		$status = $authService->checkverify($authmobile, ip2long($onlineip), $authverify);
		!$status && Showmsg('手机验证码填写错误');
	}
	!$rgpermit && Showmsg('reg_permit_notchecked');
	$sRegpwd = $regpwd;
	$register = L::loadClass('Register', 'user');
	/* @var $register PW_Register */

	$rg_config['rg_allowregister']==2 && $register->checkInv($invcode);
	$register->checkSameNP($regname, $regpwd);

	$register->setStatus(11);
	$regemailtoall && $register->setStatus(7);
	$register->setName($regname);
	$register->setPwd($regpwd, $regpwdrepeat);
	$register->setEmail($regemail);
	$register->setSafecv($question, $customquest, $answer);
	$register->setReason($regreason);
	//$register->setCustomfield(L::config('customfield','customfield'));
	$register->setCustomdata($customdata);
	$register->execute();

	if ($rg_config['rg_allowregister']==2) {
		$register->disposeInv();
	}
	list($winduid, $rgyz, $safecv) = $register->getRegUser();
	//用户自定义字段
	$customfieldService = L::loadClass('CustomerFieldService','user');/* @var $customfieldService PW_CustomerFieldService */
	$customfieldService->saveRegisterCustomerData();
	/*
	if (S::isArray($fields)) {
		foreach ($fields as $v) {
			$customfieldService->setData($v, $winduid);
		}
	}
	*/
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
	//$iptime=$timestamp+86400;
	//Cookie("ifregip",$onlineip,$iptime);
	if ($rg_config['rg_allowsameip']) {
		if (file_exists(D_P.'data/bbscache/ip_cache.php')) {
			pwCache::setData(D_P.'data/bbscache/ip_cache.php',"<$onlineip>", false, "ab");
		} else {
			pwCache::setData(D_P.'data/bbscache/ip_cache.php',"<?php die;?><$timestamp>\n<$onlineip>");
		}
	}
	//addonlinefile();
	if (GetCookie('userads') && $inv_linkopen && $inv_linktype == '1') {
		require_once(R_P.'require/userads.php');
	}
	if (GetCookie('o_invite') && $db_modes['o']['ifopen'] == 1) {
		list($o_u,$hash,$app) = explode("\t",GetCookie('o_invite'));
		if (is_numeric($o_u) && strlen($hash) == 18) {
			require_once(R_P.'require/o_invite.php');
		}
	}
	if ($rgyz == 1) {
		Cookie("winduser",StrCode($winduid."\t".PwdCode($windpwd)."\t".$safecv));
		Cookie("ck_info",$db_ckpath."\t".$db_ckdomain);
		Cookie('lastvisit','',0);//将$lastvist清空以将刚注册的会员加入今日到访会员中
		/*连续登录天数*/
		if ($db_md_ifopen) {
			require_once(R_P.'require/functions.php');
			doMedalBehavior($winduid,'continue_login');
		}
	}
	//发送短消息
	if ($rg_config['rg_regsendmsg']) {
		$rg_config['rg_welcomemsg'] = str_replace('$rg_name', $regname, $rg_config['rg_welcomemsg']);
		M::sendNotice(
			array($windid),
			array(
				'title' => "Welcome To[{$db_bbsname}]!",
				'content' => $rg_config['rg_welcomemsg'],
			)
		);
	}

	//发送邮件
	//* @include_once pwCache::getPath(D_P.'data/bbscache/mail_config.php');
	pwCache::getData(D_P.'data/bbscache/mail_config.php');
	if ($rg_config['rg_emailcheck']) {
		if ($rg_config['rg_regsendemail'] && $ml_mailifopen) {
			require_once(R_P.'require/sendemail.php');
			sendemail($regemail,'email_welcome_subject','email_welcome_content','email_additional');
		}
		$verifyhash = GetVerify();
		$rgyz = md5($rgyz . substr(md5($db_sitehash),0,5) . substr(md5($regname),0,5));
		require_once(R_P.'require/sendemail.php');
		$sendinfo = sendemail($regemail, 'email_check_subject', 'email_check_content', 'email_additional');
		if ($sendinfo === true) {
			ObHeader("$db_registerfile?step=finish&email=$regemail&verify=$verifyhash");
		} else {
			Showmsg(is_string($sendinfo) ? $sendinfo : 'reg_email_fail');
		}
	} elseif ($rg_config['rg_regsendemail'] && $ml_mailifopen) {
		require_once(R_P.'require/sendemail.php');
		sendemail($regemail,'email_welcome_subject','email_welcome_content','email_additional');
	}
	//发送结束

	//passport
	if ($db_pptifopen && $db_ppttype == 'server' && ($db_ppturls || $forward)) {
		$action = 'login';
		$jumpurl = $forward ? $forward : $db_ppturls;
		empty($forward) && $forward = $db_bbsurl;
		require_once(R_P.'require/passport_server.php');
	}
	//passport

	$verifyhash = GetVerify($winduid);
	ObHeader("$db_registerfile?step=finish&verify=$verifyhash");

} elseif ($step == 'finish') {
	S::gp(array('email','newemail','regname','option','r'));
	S::gp(array('facetype'),'G');
	if (S::getGP('vip') == 'activating') {
		S::gp(array('r_uid','pwd','toemail'),'G');
		$r_uid = (int)$r_uid;
		
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		if($rg_config['rg_emailcheck'] == 0) Showmsg('reg_jihuo_success');
		if (!$userService->activateUser($r_uid, $pwd, $db_sitehash,$toemail)) Showmsg('reg_jihuo_fail');
		Cookie('regactivate',1);
		require_once(PrintEot('register'));
		footer();
	}
	
	if ($option && $option != 'uploadicon') {
		PostCheck();
	}

	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	if ($email && $rg_config['rg_emailcheck']) {
		list(,$emailurl) = explode('@',$email);
		$emailurl = 'http://mail.'.$emailurl;
		if ($r == 1 || $newemail) {
			$men = $userService->getUnactivatedUser(0, $email, $db_sitehash);
			!$men && Showmsg('remail_error',1);
			$regname = $men['username'];
			$winduid = $men['uid'];
			$timestamp = $men['regdate'];
			$rgyz = $men['activateCode'];
			require_once(R_P.'require/sendemail.php');
			$sendtoEmail = $newemail ? $newemail : $email;
			if($newemail){
				if (!preg_match('/^[a-z0-9\-_\.]{2,}@([a-z\-0-9]+\.)+[a-z]{2,3}$/i', $newemail) ){
				Showmsg('电子邮箱地址格式有误，请重新填写!');
				}
				$register = L::loadClass('Register', 'user');
				$register->changeEmail($winduid,$newemail);
				$email = $newemail;
				$regemail = $newemail;
			}
			//if (!$r && $rg_config['rg_regsendemail'] && $ml_mailifopen) {
				//sendemail($sendtoEmail,'email_welcome_subject','email_welcome_content','email_additional');
			//}
			$sendinfo = sendemail($sendtoEmail,'email_check_subject','email_check_content_resend','email_additional');
			if ($sendinfo === true) {
				if($r == 1) ObHeader("$db_registerfile?step=finish&email=$email&verify=$verifyhash&r=3");
				$newemail ? ObHeader("$db_registerfile?step=finish&newemail=$newemail&verify=$verifyhash&r=2") : ObHeader("$db_registerfile?step=finish&email=$email&verify=$verifyhash&r=2");
			} else {
				Showmsg(is_string($sendinfo) ? $sendinfo : 'reg_email_fail');
			}
		}
	}
	if (!$rg_config['rg_emailcheck'] && $option != 'uploadicon') {
		!$winduid && Showmsg('illegal_request');
	}
	
	$rg_config['rg_regguide'] && !$option && $option = 1;
	
	if ($option == '1') {
		S::gp(array('isupload'));
		if ($isupload) {
			S::gp(array('proicon','facetype'),'P');
			require_once(R_P.'require/showimg.php');
			//user icon
			$user_a = array();
			$usericon = '';
			if ($facetype == 1) {
				$usericon = setIcon($proicon, $facetype, $user_a);
			} elseif ($_G['allowportait'] && $facetype == 2) {
				$httpurl = $_POST['httpurl'];
				if (strncmp($httpurl[0],'http',4) != 0 || strrpos($httpurl[0],'|') !== false) {
					refreshto("$db_registerfile?step=finish&facetype=$facetype",getLangInfo('msg','illegal_customimg'),2,true);
				}
				$proicon = $httpurl[0];
				$httpurl[1] = (int)$httpurl[1];
				$httpurl[2] = (int)$httpurl[2];
				$httpurl[3] = (int)$httpurl[3];
				$httpurl[4] = (int)$httpurl[4];
				list($user_a[2], $user_a[3]) = flexlen($httpurl[1], $httpurl[2], $httpurl[3], $httpurl[4]);
				/*
				if (empty($httpurl[1]) && empty($httpurl[2])) {
					list($iconwidth,$iconheight) = getimagesize($proicon);
				} else {
					list($iconwidth,$iconheight) = getfacelen($httpurl[1],$httpurl[2]);
				}
				$user_a[2] = $iconwidth;
				$user_a[3] = $iconheight;
				*/
				$usericon = setIcon($proicon, $facetype, $user_a);
				unset($httpurl);
			}
			pwFtpClose($ftp);
			$userService->update($winduid, array('icon' => $usericon));
			initJob($winduid, 'doUpdateAvatar');
			$jobService = L::loadclass('job', 'job');
			$jobs = $jobService->getJobByJobName('doUpdateAvatar');
			foreach ($jobs as $value) {
				if (isset($value['isuserguide']) && !$value['isuserguide']) continue;
				$job['id'] = $value['id'];
			}
			$jobService->jobGainController($winduid, $job['id']);
			ObHeader("$db_registerfile?step=finish&option=2&verify=$verifyhash");
		}
		
		//系统头像
		$img = @opendir(S::escapeDir("$imgdir/face"));
		while ($imgname = @readdir($img)) {
			if ($imgname != "." && $imgname != ".." && $imgname != "" && eregi("\.(gif|jpg|png|bmp)$",$imgname)) {
				$num++;
				if ($num <= 10) {
					$imgname_array[] = $imgname;
				} else {
					break;
				}
			}
		}
		@closedir($img);
		//flash头像上传参数
		$icon_encode_url = '';
		list($db_ifupload,$db_imgheight,$db_imgwidth,$db_imgsize) = explode("\t",$GLOBALS['db_upload']);
		if ($db_ifupload && $_G['upload']) {
			$pwServer['HTTP_USER_AGENT'] = 'Shockwave Flash';
			$save_param = rawurlencode($db_bbsurl.'/job.php?action=uploadicon&step=2&from=reg&');
			$default_pic = rawurlencode("$db_picpath/facebg.jpg");
			$icon_encode_url = 'saveFace='.$save_param.'&url='.$default_pic.'&imgsize='.$db_imgsize.'&';
		}
		$skipUrl = "$db_registerfile?step=finish&option=2&verify=$verifyhash";
	} elseif ($option == '2') {
		S::gp(array('notskip'));
		$customfieldService = L::loadClass('CustomerFieldService','user');/* @var $customfieldService PW_CustomerFieldService */
		if ($notskip) {
			$customfieldService->saveRegisterCustomerData(2);
			initJob($winduid, 'doUpdatedata');
			$jobService = L::loadclass('job', 'job');
			$jobs = $jobService->getJobByJobName('doUpdatedata');
			foreach ($jobs as $value) {
				if (isset($value['isuserguide']) && !$value['isuserguide']) continue;
				$job['id'] = $value['id'];
			}
			$jobService->jobGainController($winduid, $job['id']);
			ObHeader("$db_registerfile?step=finish&option=3&verify=$verifyhash");
		}
		$complementTemplate = $customfieldService->getRegisterTemplate(2);
		
		if (!$complementTemplate) ObHeader("$db_registerfile?step=finish&option=3&verify=$verifyhash");
		$skipUrl = "$db_registerfile?step=finish&option=3&verify=$verifyhash";
		
	} elseif ($option == '3') {
		S::gp(array('attention'),'P');
		if (!$attention) {
			require_once(R_P.'require/showimg.php');
			if ($rg_config['rg_recommendnames']) {
				$members = $userService->buildUserInfo($rg_config['rg_recommendids']);
			}
			$winduidInfo = $userService->getUserInfoByUserId($winduid);
			$mayKnownUserIds = $userService->getMayKnownUserIds($winduidInfo,12);
			$mayKnownUsers = $userService->buildUserInfo($mayKnownUserIds);

			$skipUrl = "$db_registerfile?step=finish&option=4&verify=$verifyhash";
		} else {
			S::gp(array('uids'));
			$attentionService = L::loadClass('attention', 'friend');
			foreach ($uids as $uid) {
				$attentionService->addFollow($winduid,$uid);
			}
			initJob($winduid, 'doAddFriend');
			$jobService = L::loadclass('job', 'job');
			$jobs = $jobService->getJobByJobName('doAddFriend');
			foreach ($jobs as $value) {
				if (isset($value['isuserguide']) && !$value['isuserguide']) continue;
				$job['id'] = $value['id'];
			}
			$jobService->jobGainController($winduid, $job['id']);
			ObHeader("$db_registerfile?step=finish&option=4&verify=$verifyhash");
		}
	} elseif ($option == '4') {
		//精彩推荐
		$rg_recommendcontent = pwHtmlspecialchars_decode($rg_config['rg_recommendcontent']);
		
		//可能感兴趣的内容~最新图酷帖->最新帖
		$threadsService = L::loadClass('threads', 'forum'); /* @var $threadsService PW_Threads */
		$latestImageThreads = $threadsService->getLatestImageThreads(7);
		foreach ($latestImageThreads as $k=>$v) {
			//$recommendContent['attachurl'] = 
			$a_url = geturl($v['attachurl'], 'show',1);
			$latestImageThreads[$k]['subject'] = substrs($v[subject],12);
			$latestImageThreads[$k]['thumb'] = getMiniUrl($v['attachurl'], $v['ifthumb'], $a_url[1]);
			//url
			$latestImageThreads[$k]['url'] = "read.php?tid={$v['tid']}";
			$db_htmifopen && $latestImageThreads[$k]['url'] = urlRewrite ( $latestImageThreads[$k]['url'] );
			//thumb
			!$latestImageThreads[$k]['thumb'] && $latestImageThreads[$k]['thumb'] = 'images/defaultactive.jpg';
		}
	}
	require_once(PrintEot('register'));footer();

} elseif ($step == 'permit') {

	if (isset($_GET['ajax'])) {
		define('AJAX','1');
	}
	require_once PrintEot('register');ajax_footer();

} else {
	Showmsg('undefined_action');
}
function getMiniUrl($path, $ifthumb, $where) {
	$dir = '';
	($ifthumb & 1) && $dir = 'thumb/';
	($ifthumb & 2) && $dir = 'thumb/mini/';
	if ($where == 'Local') return $GLOBALS['attachpath'] . '/' . $dir . $path;
	if ($where == 'Ftp') return $GLOBALS['db_ftpweb'] . '/' . $dir . $path;
	if (!is_array($GLOBALS['attach_url'])) return $GLOBALS['attach_url'] . '/' . $dir . $path;
	return $GLOBALS['attach_url'][0] . '/' . $dir . $path;
}
?>