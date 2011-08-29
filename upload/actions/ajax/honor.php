<?php
!defined('P_W') && exit('Forbidden');

!$_G['allowhonor'] && Showmsg('抱歉，你没有编辑个性签名的权限');

if (empty($_POST['step'])) {

	require_once PrintEot('ajax');
	ajax_footer();

} else {

	PostCheck();
	S::gp(array('content'), 'P');
	$content = trim(str_replace("\n", '', $content));
	strlen($content) > 90 && $content = substrs($content, 90);

	$wordsfb = L::loadClass('FilterUtil', 'filter');
	$banword = $wordsfb->comprise(stripslashes($content));
	if ($banword !== false) {
		Showmsg('content_wordsfb');
	}
	if ($winddb['honor'] != stripslashes($content)) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->update($winduid, array('honor'=>$content));
		
		//* $_cache = getDatastore();
		//* $_cache->delete('UID_'.$winduid);
		
		if (L::config('o_weibopost', 'o_config')) {
			$weiboService = L::loadClass('weibo','sns');
			if ($weiboService->sendCheck($content, $groupid)) {
				$weiboService->send($winduid, $content, 'honor');
			}
		}
	}
	//require_once (R_P . 'require/postfunc.php');
	echo "success\t" . stripslashes($content);
	ajax_footer();
}