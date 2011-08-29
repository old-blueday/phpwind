<?php
define('SCR','sendemail');
require_once('global.php');
require_once(R_P.'require/header.php');

$groupid == 'guest' && Showmsg('not_login');
S::gp(array('action'));
!$action && $action = 'mailto';

if ($action == 'mailto') {

	S::gp(array('uid','username'));
	
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	if ($username || is_numeric($uid)) {
		if ($username) {
			$userdb = $userService->getByUserName($username);
		} else {
			$userdb = $userService->get($uid);
		}
	} else {
		$userdb = '';
	}
	!$userdb && Showmsg('undefined_action');

	$rt = $userService->get($winduid, false, false, true);
	if ($timestamp-$rt['lasttime'] < 60) {
		Showmsg('sendeamil_limit');
	}
	if (empty($_POST['step'])) {

		if (!getstatus($userdb['userstatus'], PW_USERSTATUS_RECEIVEMAIL) && $groupid != '3' && $groupid != '4') {
			Showmsg('sendeamil_refused');
		}
		$to_mail = $userdb['email'];
		$to_user = $userdb['username'];

		if (!getstatus($userdb['userstatus'], PW_USERSTATUS_PUBLICMAIL) && $groupid != '3' && $groupid != '4') {
			$hiddenmail = 1;
		} else {
			$hiddenmail = 0;
		}
		require_once(PrintEot('sendmail'));footer();

	} else {

		PostCheck(1,$db_gdcheck & 16);

		if (!getstatus($userdb['userstatus'], PW_USERSTATUS_RECEIVEMAIL) && $groupid != '3' && $groupid != '4') {
			Showmsg('sendeamil_refused');
		}
		$sendtoemail = $userdb['email'];
		S::gp(array('subject','atc_content','fromname','fromemail','sendtoname'));

		if (empty($subject)) {
			Showmsg('sendeamil_subject_limit');
		}
		if (empty($atc_content) || strlen($atc_content) <= 20) {
			Showmsg('sendeamil_content_limit');
		} elseif (!ereg("^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$",$sendtoemail) || !ereg("^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$",$fromemail)) {
			Showmsg('illegal_email');
		}
		
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