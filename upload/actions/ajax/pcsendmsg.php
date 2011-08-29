<?php
!defined('P_W') && exit('Forbidden');

S::gp(array(
	'tid',
	'pcid'
));
$read = $db->get_one("SELECT authorid,subject,fid FROM pw_threads WHERE tid=" . S::sqlEscape($tid));
$foruminfo = $db->get_one('SELECT forumadmin,fupadmin FROM pw_forums WHERE fid=' . S::sqlEscape($read['fid']));
$isGM = S::inArray($windid, $manager);
$isBM = admincheck($foruminfo['forumadmin'], $foruminfo['fupadmin'], $windid);
L::loadClass('postcate', 'forum', false);
$post = array();
$postCate = new postCate($post);
$isadminright = $postCate->getAdminright($pcid, $read['authorid']);

if (!$isadminright) {
	Showmsg('pcexport_none');
}

if (empty($_POST['step'])) {
	$sum = $db->get_value("SELECT SUM(nums) as nums FROM pw_pcmember WHERE tid=" . S::sqlEscape($tid));
	!$sum && Showmsg('pcsendmsg_fail');
	
	require_once PrintEot('ajax');
	ajax_footer();
} elseif ($_POST['step'] == 2) {
	PostCheck();
	S::gp(array(
		'subject',
		'atc_content',
		'tid',
		'ifsave',
		'pcid'
	));
	require_once (R_P . 'require/common.php');
	
	$msg_title = trim($subject);
	$atc_content = trim($atc_content);
	if (empty($atc_content) || empty($msg_title)) {
		Showmsg('msg_empty');
	} elseif (strlen($msg_title) > 75 || strlen($atc_content) > 1500) {
		Showmsg('msg_subject_limit');
	}
	require_once (R_P . 'require/bbscode.php');
	$wordsfb = L::loadClass('FilterUtil', 'filter');
	if (($banword = $wordsfb->comprise($msg_title)) !== false) {
		Showmsg('title_wordsfb');
	}
	if (($banword = $wordsfb->comprise($atc_content, false)) !== false) {
		Showmsg('content_wordsfb');
	}
	
	$query = $db->query("SELECT uid FROM pw_pcmember WHERE tid=" . S::sqlEscape($tid) . " GROUP BY uid");
	while ($rt = $db->fetch_array($query)) {
		$uiddb[] = $rt['uid'];
	}
	$messageType = ($pcid == 1) ? 'notice_postcate' : 'notice_active';
	$ifuids = $sqladd = $msglog = array();
	if ($uiddb) {
		$userNames = array();
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userNames = $userService->getUserNamesByUserIds($uiddb);
		M::sendNotice(
			$userNames,
			array(
				'create_uid'	=> $winduid,
				'create_username'	=> $windid,
				'title' => $msg_title,
				'content' => $atc_content
			),
			$messageType,
			$messageType
		);
	}

	Showmsg('send_success');
}
