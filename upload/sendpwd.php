<?php
define('SCR','sendpwd');
require_once('global.php');
require_once(R_P.'require/header.php');
InitGP(array('action'));

$action != 'getback' && $action = 'sendpwd';
if ($action=='sendpwd') {
	if ($_POST['step']!=2) {
		require_once(PrintEot('sendpwd'));footer();
	} else {
		PostCheck(0,$db_gdcheck & 16);
		InitGP(array('pwuser', 'email', 'question', 'customquest', 'answer'));
		
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userarray = $userService->getByUserName($pwuser);
		
		if (strtolower($userarray['email']) != strtolower($email)) {
			Showmsg('email_error',1);
		}
		if ($db_ifsafecv) {
			require_once(R_P.'require/checkpass.php');
			$safecv = questcode($question,$customquest,$answer);
			if ($userarray['safecv'] != $safecv) {
				Showmsg('safecv_error',1);
			}
		}
		
		if ($userarray) {
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
		} else {
			$errorname = $pwuser;
			Showmsg('user_not_exists',1);
		}
	}
}
if ($action=='getback') {
	InitGP(array('pwuser', 'submit', 'st'));
	if (CkInArray($pwuser,$manager) || !$submit || !$st) {
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
					require_once PrintEot('getpwd');footer();
				} else {
					InitGP(array('new_pwd','pwdreapt'));
					if (!$new_pwd || $new_pwd!=$pwdreapt) {
						Showmsg('password_confirm',1);
					} else {
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