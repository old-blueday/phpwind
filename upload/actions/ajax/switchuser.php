<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('uid'));
$db_logintype = 2;
require_once (R_P . 'require/checkpass.php');

$id = $db->get_value("SELECT id FROM pw_userbinding WHERE uid=" . S::sqlEscape($winduid));

$user = $db->get_one("SELECT s.password,m.uid,m.safecv FROM pw_userbinding s LEFT JOIN pw_members m ON s.uid=m.uid WHERE s.id=" . S::sqlEscape($id) . ' AND s.uid=' . S::sqlEscape($uid));

$logininfo = checkpass($user['uid'], $user['password'], $user['safecv'], 1);
if (!is_array($logininfo)) {
	switch ($logininfo) {
		case 'login_forbid':
		case 'login_pwd_error':
			Showmsg('switchuser_error');
		default:
			Showmsg($logininfo);
	}
}
list($winduid, $groupid, $windpwd, $showmsginfo) = $logininfo;

$cktime = 7 * 24 * 3600;
(int) $keepyear && $cktime = 31536000;
$cktime != 0 && $cktime += $timestamp;
Cookie("winduser", StrCode($winduid . "\t" . $windpwd . "\t" . $user['safecv']), $cktime);
Cookie("ck_info", $db_ckpath . "\t" . $db_ckdomain);
//Cookie("ucuser",'cc',$cktime);
Cookie('lastvisit', '', 0);

echo "ok\t$showmsginfo";

ajax_footer();
