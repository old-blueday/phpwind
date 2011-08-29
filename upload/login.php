<?php
define('PRO','1');
define('SCR','login');
require_once('global.php');
require (L::style('', $skinco, true));
if ("wind" != $tplpath && file_exists(D_P.'data/style/'.$tplpath.'_css.htm')) {
	$css_path = D_P.'data/style/'.$tplpath.'_css.htm';
} else{
	$css_path = D_P.'data/style/wind_css.htm';
}
S::gp(array('ajax'),'P');

if ($db_pptifopen && $db_ppttype == 'client') {
	if ($ajax) {
		$message = getLangInfo('msg', 'passport_login');
		echo "error\t" . $message;
		ajax_footer();
	}
	Showmsg('passport_login');
}

S::gp(array('action','forward'));
!$db_pptifopen && $forward = '';
$pre_url = $pwServer['HTTP_REFERER'] ? $pwServer['HTTP_REFERER'] : $db_bbsurl.'/'.$db_bfn;

if (strpos($pre_url,'login.php') !== false || strpos($pre_url,$db_registerfile) !== false) {
	$pre_url = $db_bfn;
}
!$action && $action = "login";

/* platform weibo app */
$siteBindService = L::loadClass('WeiboSiteBindService', 'sns/weibotoplatform/service'); /* @var $siteBindService PW_WeiboSiteBindService */
if ($siteBindService->isOpen() && $action == 'weibologinregister') {
	InitGP(array('step'));
	if ($step == 'finish') require_once(R_P.'require/weibologin.php');
}

if ($groupid != 'guest' && $action != 'quit') {
	
	if ($db_pptifopen && $db_ppttype == 'server' && ($db_ppturls || $forward)) {
		$jumpurl = $forward ? $forward : $db_ppturls;
		$forward = $pre_url;
		require_once(R_P.'require/passport_server.php');
	} elseif (empty($_POST)) {
		ObHeader($pre_url);
//		Showmsg('login_have');
	}
}
list(,$showq) = explode("\t", $db_qcheck);
if ($action == 'login') {

	if (empty($_POST['step'])) {
		$arr_logintype = array();
		if ($db_logintype) {
			for ($i = 0; $i < 3; $i++) {
				if ($db_logintype & pow(2,$i)) {
					$arr_logintype[] = $i;
				}
			}
		} else {
			$arr_logintype[0] = 0;
		}
		if ((GetCookie('o_invite') && $db_modes['o']['ifopen']==1) || $isfromreg) {
			S::gp(array('jumpurl'));
		} else {
			$jumpurl = $pre_url;
		}
		list(,$_LoginInfo) = pwNavBar();
		require_once PrintEot('login');footer();

	} else {

		S::gp(array('pwuser','pwpwd','question','customquest','answer','cktime','hideid','jumpurl','lgt','keepyear', 'ajax', 'ajaxstep'),'P');
		list($ajax, $ajaxstep) = array(intval($ajax), intval($ajaxstep));
		require_once(R_P . 'require/checkpass.php');
		$ajax && define('AJAX', 1);
		
		if ($ajax && !$ajaxstep) {
			if (!$pwuser || !$pwpwd) showLoginAjaxMessage('login_empty');
			$md5Pwd = md5($pwpwd);
			$loginInfo = checkpass($pwuser, $md5Pwd, '', $lgt, false);
			if (!S::isArray($loginInfo)) {
				CloudWind::yunUserDefend('login', CloudWind::getNotLoginUid(), $pwuser, $timestamp, 0, 102, $logininfo, '', '', '');
				showLoginAjaxMessage($loginInfo);
			}
			
			list(,$_LoginInfo) = pwNavBar();
			list(,,,,$hasSafeCv) = $loginInfo;
			if (($db_ifsafecv && $hasSafeCv) || ($db_gdcheck & 2) || $_LoginInfo['qcheck']) {
				require_once PrintEot('headerlogin');ajax_footer();
			}
		}
		
		if ($ajax && $ajaxstep == 2) {
			if ($db_gdcheck & 2) {
				$checkCode = GdConfirm(S::getGp('gdcode', 'P'), true);
				!$checkCode && showLoginAjaxMessage('gdcodeerror');
			}
			
			if ($db_ckquestion & 2) {
				list($qanswer, $questionKey) = array(S::getGp('qanswer', 'P'), S::getGp('qkey', 'P'));
				$checkAnswer = Qcheck($qanswer, $questionKey, true);
				!$checkAnswer && showLoginAjaxMessage('ckquestionerror');
			}
		} else {
			PostCheck(0, $db_gdcheck & 2, $db_ckquestion & 2 && $db_question, 0);
		}
		$jumpurl = str_replace(array('&#61;', '&amp;'), array('=', '&'), $jumpurl);
		if (!$pwuser || !$pwpwd) Showmsg('login_empty');
		$md5_pwpwd = md5($pwpwd);
		$safecv = $db_ifsafecv ? questcode($question, $customquest, $answer) : '';

		$logininfo = checkpass($pwuser, $md5_pwpwd, $safecv, $lgt);
 		if (!is_array($logininfo)) {
			if ($logininfo == 'login_jihuo') {
				$regEmail = getRegEmail($pwuser);
				ObHeader("$db_registerfile?step=finish&email=$regEmail");
			}
			// defend start	
			CloudWind::yunUserDefend('login', CloudWind::getNotLoginUid(), $pwuser, $timestamp, 0, 102,$logininfo,'','','');
			// defend end
			if ($ajax && $ajaxstep == 2 && $logininfo == 'login_safecv_error') showLoginAjaxMessage("safequestionerror\t$L_T");
			Showmsg($logininfo);
		}
		list($winduid, $groupid, $windpwd, $showmsginfo) = $logininfo;
		CloudWind::yunUserDefend('login', $winduid, $pwuser, $timestamp, 0, 101,'','','','');
		//* 当游客“登录”时，删除该游客在pw_online_guest表中的记录
		$onlineService = L::loadClass('OnlineService', 'user');
		$onlineService->deleteOnlineGuest();		
		
		perf::gatherInfo('changeMembersWithUserIds', array('uid'=>$winduid));
		
		if (file_exists(D_P."data/groupdb/group_$groupid.php")) {
			pwCache::getData(S::escapePath(D_P."data/groupdb/group_$groupid.php"));
		} else {
			pwCache::getData(D_P."data/groupdb/group_1.php");
		}
		(int)$keepyear && $cktime = '31536000';
		$cktime != 0 && $cktime += $timestamp;
		Cookie("winduser",StrCode($winduid."\t".$windpwd."\t".$safecv),$cktime);
		Cookie("ck_info",$db_ckpath."\t".$db_ckdomain);
		Cookie('lastvisit','',0);//将$lastvist清空以将刚注册的会员加入今日到访会员中
		require_once R_P.'u/require/core.php';
		updateMemberid($winduid, false);
		if ($db_autoban) {
			require_once(R_P.'require/autoban.php');
			autoban($winduid);
		}
		($_G['allowhide'] && $hideid) ? Cookie('hideid',"1",$cktime) : Loginipwrite($winduid);
		(empty($jumpurl) || false !== strpos($jumpurl, $regurl) || false !== strpos($jumpurl,'sendpwd.php')) && $jumpurl = $db_bfn;

		if (GetCookie('o_invite') && $db_modes['o']['ifopen'] == 1) {
			list($o_u,$hash,$app) = explode("\t",GetCookie('o_invite'));
			if (is_numeric($o_u) && strlen($hash) == 18) {
				require_once(R_P.'require/o_invite.php');
			}
		}
		//passport
		if ($db_pptifopen && $db_ppttype == 'server' && ($db_ppturls || $forward)) {
			$tmp = $jumpurl;
			$jumpurl = $forward ? $forward : $db_ppturls;
			$forward = $tmp;
			require_once(R_P.'require/passport_server.php');
		}
		//passport
		$isRegActivate = GetCookie('regactivate');
		Cookie('regactivate','',0);
		
		pwHook::runHook('after_login');
		$jumpurl = $isRegActivate ? "$db_registerfile?step=finish&verify=$verifyhash": $jumpurl;
		if (!$ajax) refreshto($jumpurl,'have_login','',true);
		echo "success\t" . $jumpurl;ajax_footer();
	}
} elseif ($action == 'quit') {

	if (!$db_pptifopen || !$db_pptcmode) {
		checkVerify('loginhash');
	}
	require_once(R_P.'require/checkpass.php');

	if ($groupid == '6') {
		$bandb = $db->get_one("SELECT type FROM pw_banuser WHERE uid=".S::sqlEscape($winduid)." AND fid='0'");
		if ($bandb['type'] == 3) {
			Cookie('force',$winduid);
		}
	}
	
	//* 当用户“退出”时，删除该用户在pw_online_user表中的记录
	$onlineService = L::loadClass('OnlineService', 'user');
	$onlineService->deleteOnlineUser($winduid);
	// defend start	
	CloudWind::yunUserDefend('quit', $winduid, $windid, $timestamp, 0, 101,'','','','');
	// defend end
	Loginout();
	require_once(R_P . 'uc_client/uc_client.php');
	$showmsginfo = uc_user_synlogout();

	//passport
	if ($db_pptifopen && $db_ppttype == 'server' && ($db_ppturls || $forward)) {
		$jumpurl = $forward ? $forward : $db_ppturls;
		$forward = $pre_url;
		require_once(R_P.'require/passport_server.php');
	}
	//passport
	Cookie("jobpop",0);/*jobpop*/

	if (preg_match('/u.php$/i', $pre_url)) {
		$pre_url = $db_bfn;
	}

	refreshto($pre_url,'login_out','',true);/*退出url 不要使用$pre_url 因为如果在修改密码后会造成一个循环跳转*/
}
if ($siteBindService->isOpen()) {
	require_once(R_P.'require/weibologin.php');
}
?>