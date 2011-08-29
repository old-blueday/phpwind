<?php
!defined('P_W') && exit('Forbidden');
define('AJAX', '1');
require_once (R_P . 'require/functions.php');

!$winduid && Showmsg('not_login');

S::gp(array('action'));

if ($action == 'delatt') {
	
	PostCheck();
	S::gp(array('did', 'aid'));
	empty($aid) && Showmsg('job_attach_error');
	
	$attach = $db->get_one("SELECT * FROM pw_attachs WHERE aid=" . S::sqlEscape($aid));
	!$attach && Showmsg('job_attach_error');
	if (empty($attach['attachurl']) || strpos($attach['attachurl'], '..') !== false) {
		Showmsg('job_attach_error');
	}

	$aid = $attach['aid'];

	//获取管理权限
	$isGM = S::inArray($windid, $manager);
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
			//$db->update("UPDATE pw_diary SET aid=".S::sqlEscape($attachs)."WHERE did=" . S::sqlEscape($did));
		    pwQuery::update('pw_diary','did =:did' , array($did), array('aid'=> $attachs));
		}
		
		$db->update("DELETE FROM pw_attachs WHERE aid=" . S::sqlEscape($aid));
		echo 'success';
		ajax_footer();
	} else {
		Showmsg('job_attach_right');
	}
	
}

?>