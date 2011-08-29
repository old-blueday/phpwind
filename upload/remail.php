<?php
require_once('global.php');

$windid && Showmsg('undefined_action');
S::gp(array('uid'),'GP',2);

$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
$men = $userService->getUnactivatedUser($uid, '', $db_sitehash);
!$men && Showmsg('remail_error',1);

if (empty($_POST['step'])) {
	$men['password'] = '';
	@extract($men);
	require_once(R_P.'require/header.php');
	require_once PrintEot('remail');footer();

} else {

	S::gp(array('password','rg_email','to_email'));
	$men['password'] != md5($password) && Showmsg('password_error',1);
	$rg_email != $men['email'] && Showmsg('email_error',1);
	if ($to_email && !ereg("^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$",$to_email)) {
		Showmsg('illegal_email');
	}
	$rg_yz = (int)num_rand(9);
	while ($rg_yz < 32) {
		$rg_yz = (int)num_rand(9);
	}
	$userUpdate = array('yz' => $rg_yz);
	if ($to_email) {
		$userUpdate['email'] = $to_email;
	} else {
		$to_email = $men['email'];
	}
	$userService->update($uid, $userUpdate);
	
	$regname = $men['username'];
	$winduid = $uid;
	$timestamp = $men['regdate'];
	$sRegpwd = $password;
	$rgyz = md5($rg_yz.substr(md5($db_sitehash),0,5).substr(md5($regname),0,5));
	require_once(R_P.'require/sendemail.php');
	$sendinfo = sendemail($to_email,'email_check_subject','email_check_content','email_additional');
	if ($sendinfo === true) {
		Showmsg('remail_success',1);
	} else {
		Showmsg(is_string($sendinfo) ? $sendinfo : 'reg_email_fail',1);
	}
}
?>