<?php
define('SCR','sendemail');
require_once('global.php');
require_once(R_P.'require/header.php');

$groupid == 'guest' && Showmsg('not_login');
S::gp(array('action'));
!$action && $action = 'mailto';

if ($action == 'mailto') {
	S::gp(array('uid','username','step'));
	$hasReceiver = false;
	if ($uid || $username) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userdb = '';
		if ($username || is_numeric($uid)) {
			$userdb = $username ? $userService->getByUserName($username) : $userService->get($uid);
		}
		!$userdb && Showmsg('undefined_action');

		$rt = $userService->get($winduid, false, false, true);
		($timestamp - $rt['lasttime'] < 60) && Showmsg('sendeamil_limit');
		$hasReceiver = true;
	}
	if (empty($step)) {
		if ($hasReceiver && !getstatus($userdb['userstatus'], PW_USERSTATUS_RECEIVEMAIL) && $groupid != '3' && $groupid != '4') {
			Showmsg('sendeamil_refused');
		}
		$to_mail = $hasReceiver ? $userdb['email'] : $db_ceoemail;
		$to_user = $hasReceiver ? $userdb['username'] : '';
		$hiddenmail = 0;
		if ($hasReceiver && !getstatus($userdb['userstatus'], PW_USERSTATUS_PUBLICMAIL) && $groupid != '3' && $groupid != '4') {
			$hiddenmail = 1;
		}
		require_once(PrintEot('sendmail'));footer();
	} else {
		PostCheck(1,$db_gdcheck & 16);
		if ($hasReceiver && !getstatus($userdb['userstatus'], PW_USERSTATUS_RECEIVEMAIL) && $groupid != '3' && $groupid != '4') {
			Showmsg('sendeamil_refused');
		}
		$sendtoemail = $hasReceiver ? $userdb['email'] : $db_ceoemail;
		S::gp(array('subject','atc_content','fromname','fromemail','sendtoname'));

		if (empty($subject)) {
			Showmsg('sendeamil_subject_limit');
		}
		if (empty($atc_content) || strlen($atc_content) <= 20) {
			Showmsg('sendeamil_content_limit');
		} elseif (!ereg("^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$",$sendtoemail) || !ereg("^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$",$fromemail)) {
			Showmsg('illegal_email');
		}
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->update($winduid, array(), array(), array('lasttime'=>$timestamp));

		require_once(R_P.'require/sendemail.php');
		$sendinfo = sendemail($sendtoemail,$subject,$atc_content,'email_additional');
		if ($sendinfo === true) {
			refreshto('index.php','mail_success');
		} else {
			Showmsg(is_string($sendinfo) ? $sendinfo : 'mail_failed');
		}
	}
}
?>