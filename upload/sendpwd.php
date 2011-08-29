<?php
define('SCR','sendpwd');
require_once('global.php');
require (L::style('', $skinco, true));
if ("wind" != $tplpath && file_exists(D_P.'data/style/'.$tplpath.'_css.htm')) {
	$css_path = D_P.'data/style/'.$tplpath.'_css.htm';
} else{
	$css_path = D_P.'data/style/wind_css.htm';
}

S::gp(array('action'));
!CkInArray($action ,array('getback','getverify','checkverify')) && $action = 'sendpwd';
//!CkInArray($action ,array('getverify','checkverify')) && require_once(R_P.'require/header.php');;

if ($action == 'sendpwd') {

	if ($_POST['step'] != 2) {

		if ($db_authstate && $db_authgetpwd) {
			$authService = L::loadClass('Authentication', 'user');
			list($authStep, $remainTime, $waitTime, $mobile) = $authService->getStatus('findpwd');
			$authStep_1 = $authStep_2 = 'none';
			${'authStep_' . $authStep} = '';
			$verifyUsername = $authStep==1 ? '' : getCookie('findpwd_verifyUsername');
		}

		require_once(PrintEot('sendpwd'));footer();

	} else {

		PostCheck(0,$db_gdcheck & 16);
		S::gp(array('type','pwuser', 'email','authmobile', 'question', 'customquest', 'answer'));
		
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userarray = $userService->getByUserName($pwuser);
		
		if ($db_ifsafecv) {
			require_once(R_P.'require/checkpass.php');
			$safecv = questcode($question,$customquest,$answer);
			if ($userarray['safecv'] != $safecv) {
				Showmsg('safecv_error',1);
			}
		}
		if ($userarray) {
			if ($type == 1) {
				//手机取回
				S::gp(array('authverify','new_pwd','pwdreapt'));
				$authService = L::loadClass('Authentication', 'user');
				if (!$authService->checkverify($authmobile, $userarray['uid'], $authverify)) {
					Showmsg('手机验证码填写错误',1);
				}
				if (!$new_pwd || $new_pwd != $pwdreapt) {
					Showmsg('password_confirm',1);
				} else {
					$new_pwd = str_replace(array("\t","\r","\n"), '', stripslashes($new_pwd));
					$new_pwd = md5($new_pwd);
					$userService->update($userarray['uid'], array('password' => $new_pwd));
					$authService->setCurrentInfo('findpwd');
					Cookie('findpwd_verifyUsername', '', 0);
					refreshto('login.php','password_change_success');
				}
			} else {
				//email取回
				if (strtolower($userarray['email']) != strtolower($email)) {
					Showmsg('email_error',1);
				}
				if ($timestamp - GetCookie('lastwrite') <= 60) {
					$_G['postpertime'] = 60;
					Showmsg('sendpwd_limit',1);
				}
				Cookie('lastwrite',$timestamp);
				$send_email = $userarray['email'];
				$submit		= md5($userarray['regdate'].substr($userarray['password'],10).$timestamp);
				$sendtoname = $pwuser;
				$pwuser		= rawurlencode($pwuser);
				
				require_once(R_P.'require/sendemail.php');
				$sendinfo = sendemail($send_email,'email_sendpwd_subject','email_sendpwd_content','email_additional');
				
				if ($sendinfo===true) {
					Showmsg('mail_success',1);
				} elseif (is_string($sendinfo)) {
					Showmsg($sendinfo,1);
				} else {
					Showmsg('mail_failed',1);
				}
			}
		} else {
			$errorname = $pwuser;
			Showmsg('user_not_exists',1);
		}
	}
} elseif ($action == 'getverify' || $action == 'checkverify') {
	/*获取验证码|检验验证码*/
	//PostCheck();
	S::gp(array('authmobile','pwuser','authverify'));
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$userarray = $userService->getByUserName($pwuser);

	if (!getstatus($userarray['userstatus'], PW_USERSTATUS_AUTHMOBILE) || !$userarray['authmobile']) {
		echo 3;
	} elseif ($userarray['authmobile'] != $authmobile) {
		echo 7;
	} else {
		$authService = L::loadClass('Authentication', 'user');
		if ($action == 'getverify') {
			$status = $authService->getverify('findpwd', $authmobile, $userarray['uid'], true, 'findpwd');
			Cookie('findpwd_verifyUsername', $userarray['username']);
			echo $status;
		} else {
			$status = $authService->checkverify($authmobile, $userarray['uid'], $authverify);
			echo $status ? 0 : 5;
		}
	}
	ajax_footer();
}

if ($action == 'getback') {
	S::gp(array('pwuser', 'submit', 'st'));
	if (S::inArray($pwuser,$manager) || !$submit || !$st) {
		Showmsg('undefined_action',1);
	}
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$detail = $userService->getByUserName($pwuser, true, true);
	if (!empty($detail)) {
		$e_login = explode('|',$detail['onlineip']);
		if ($e_login[0]!=$onlineip.' *' || ($timestamp-$e_login[1])>600 || $e_login[2]>1) {
			if (($timestamp-$st)>3600) {
				Showmsg('链接已过期，提交后请在1小时内修改，请重新找回密码！',1);
			}
			if ($submit==md5($detail['regdate'].substr($detail['password'],10).$st)) {
				if (empty($_POST['jop'])) {
					$rg_config  = L::reg();
					list($rg_regminpwd,$rg_regmaxpwd) = explode("\t", $rg_config['rg_pwdlen']);
					require_once(R_P.'require/header.php');
					require_once PrintEot('getpwd');footer();
				} else {
					S::gp(array('new_pwd','pwdreapt'));
					if (!$new_pwd || $new_pwd!=$pwdreapt) {
						Showmsg('password_confirm',1);
					} else {
						$GLOBALS['showPwdLogin'] = 1;
						$register = L::loadClass('Register', 'user');
						$register->checkPwd($new_pwd, $pwdreapt);
						$new_pwd = str_replace(array("\t","\r","\n"), '', stripslashes($new_pwd));
						$new_pwd = md5($new_pwd);
						$userService->update($detail['uid'], array('password' => $new_pwd));
						refreshto('login.php','password_change_success');
					}
				}
			} else {
				global $L_T;
				$L_T = ($timestamp-$e_login[1])>600 ? 5 : $e_login[2];
				$L_T ? $L_T-- : $L_T=5;
				$F_login = "$onlineip *|$timestamp|$L_T";
				$userService->update($detail['uid'], array(), array('onlineip' => $F_login));
				Showmsg('password_confirm_fail',1);
			}
		} else {
			global $L_T;
			$L_T = 600-($timestamp-$e_login[1]);
			Showmsg('login_forbid');
		}
	} else {
		$errorname = $pwuser;
		Showmsg('user_not_exists',1);
	}
}
?>