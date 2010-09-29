<?php
!defined('P_W') && exit('Forbidden');
define('AJAX', '1');
require_once (R_P . 'require/functions.php');

!$winduid && Showmsg('not_login');

InitGP(array('action'));

if ($action == 'delatt') {
	
	PostCheck();
	InitGP(array('did', 'aid'));
	empty($aid) && Showmsg('job_attach_error');
	
	$attach = $db->get_one("SELECT * FROM pw_attachs WHERE aid=" . pwEscape($aid));
	!$attach && Showmsg('job_attach_error');
	if (empty($attach['attachurl']) || strpos($attach['attachurl'], '..') !== false) {
		Showmsg('job_attach_error');
	}

	$aid = $attach['aid'];

	//获取管理权限
	$isGM = CkInArray($windid, $manager);
	!$isGM && $groupid = 3 && $isGM = 1;
	if ($isGM) {
		$admincheck = 1;
	} else {
		$admincheck = 0;
	}
	$attach['attachurl'] = "diary/".$attach['attachurl'];
	if ($admincheck || $attach['uid'] == $winduid) {
		pwDelatt($attach['attachurl'], $db_ifftp);
		pwFtpClose($ftp);
		
		$diaryService = L::loadClass('Diary', 'diary'); /* @var $diaryService PW_Diary */
		$diary = array();
		$diary = $diaryService->get($did);
		
		$attachs = unserialize($diary['aid']);

		if (is_array($attachs)) {
			unset($attachs[$aid]);
			$attachs = $attachs ? serialize($attachs) : '';
			$db->update("UPDATE pw_diary SET aid=".pwEscape($attachs)."WHERE did=" . pwEscape($did));
		}
		
		$db->update("DELETE FROM pw_attachs WHERE aid=" . pwEscape($aid));
		echo 'success';
		ajax_footer();
	} else {
		Showmsg('job_attach_right');
	}
	
}

?>