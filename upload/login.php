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

if ($db_pptifopen && $db_ppttype == 'client') {
	Showmsg('passport_login');
}

S::gp(array('action','forward'));
!$db_pptifopen && $forward = '';
$pre_url = $pwServer['HTTP_REFERER'] ? $pwServer['HTTP_REFERER'] : $db_bbsurl.'/'.$db_bfn;

if (strpos($pre_url,'login.php') !== false || strpos($pre_url,$db_registerfile) !== false) {
	$pre_url = $db_bfn;
}
!$action && $action = "login";

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

		PostCheck(0,$db_gdcheck & 2,$db_ckquestion & 2 && $db_question,0);
		require_once(R_P . 'require/checkpass.php');

		S::gp(array('pwuser','pwpwd','question','customquest','answer','cktime','hideid','jumpurl','lgt','keepyear'),'P');

		$jumpurl = str_replace(array('&#61;','&amp;'),array('=','&'),$jumpurl);

		if (!$pwuser || !$pwpwd) {
			Showmsg('login_empty');
		}
		$md5_pwpwd = md5($pwpwd);
		$safecv = $db_ifsafecv ? questcode($question, $customquest, $answer) : '';

		//list($winduid, $groupid, $windpwd, $showmsginfo) = checkpass($pwuser, $md5_pwpwd, $safecv, $lgt);
		$logininfo = checkpass($pwuser, $md5_pwpwd, $safecv, $lgt);
		if (!is_array($logininfo)) {
			if ($logininfo == 'login_jihuo') {
				$regEmail = getRegEmail($pwuser);
				ObHeader("$db_registerfile?step=finish&email=$regEmail");
			}
			Showmsg($logininfo);
		}
		list($winduid, $groupid, $windpwd, $showmsginfo) = $logininfo;
		
		//* 当游客“登陆”时，删除该游客在pw_online_guest表中的记录
		$onlineService = L::loadClass('OnlineService', 'user');
		$onlineService->deleteOnlineGuest();		
		
		/*update cache*/
		//* $_cache = getDatastore();
		//* $_cache->delete("UID_".$winduid);
		perf::gatherInfo('changeMembersWithUserIds', array('uid'=>$winduid));
		
		if (file_exists(D_P."data/groupdb/group_$groupid.php")) {
			//* require_once pwCache::getPath(S::escapePath(D_P."data/groupdb/group_$groupid.php"));
			pwCache::getData(S::escapePath(D_P."data/groupdb/group_$groupid.php"));
		} else {
			//* require_once pwCache::getPath(D_P."data/groupdb/group_1.php");
			pwCache::getData(D_P."data/groupdb/group_1.php");
		}
		(int)$keepyear && $cktime = '31536000';
		$cktime != 0 && $cktime += $timestamp;
		Cookie("winduser",StrCode($winduid."\t".$windpwd."\t".$safecv),$cktime);
		Cookie("ck_info",$db_ckpath."\t".$db_ckdomain);
		//Cookie("ucuser",'cc',$cktime);
		Cookie('lastvisit','',0);//将$lastvist清空以将刚注册的会员加入今日到访会员中
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
		$jumpurl = $isRegActivate ? "$db_registerfile?step=finish&verify=$verifyhash": $jumpurl;
		refreshto($jumpurl,'have_login');
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
	refreshto($pre_url,'login_out');/*退出url 不要使用$pre_url 因为如果在修改密码后会造成一个循环跳转*/
}
?>