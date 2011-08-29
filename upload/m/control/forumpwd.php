<?php
defined('W_P') or exit('Forbidden');
if (empty($wind_action)) {
	wap_header();
	require_once PrintWAP('forumpwd');
	wap_footer();
} else {
	if ($forum['password'] == md5($wind_pwd) && $groupid != 'guest') {
		Cookie("pwdcheck[$fid]", $forum['password']);
	} elseif ($groupid == 'guest') {
		wap_msg('forumpw_guest', 'index.php?a=list');
	} else {
		wap_msg('forumpw_pwd_error', 'index.php?a=forum&fid=' . $fid);
	}
}

?>