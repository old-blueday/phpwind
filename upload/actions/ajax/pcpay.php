<?php
!defined('P_W') && exit('Forbidden');

S::gp(array(
	'pcmid',
	'pcid',
	'tid',
	'jointype',
	'payway'
));
if (!$pcmid) Showmsg('undefined_action');

$read = $db->get_one("SELECT authorid,subject,fid FROM pw_threads WHERE tid=" . S::sqlEscape($tid));
$foruminfo = $db->get_one('SELECT forumadmin,fupadmin FROM pw_forums WHERE fid=' . S::sqlEscape($read['fid']));
$isGM = S::inArray($windid, $manager);
$isBM = admincheck($foruminfo['forumadmin'], $foruminfo['fupadmin'], $windid);
L::loadClass('postcate', 'forum', false);
$post = array();
$postCate = new postCate($post);
$isadminright = $postCate->getAdminright($pcid, $read['authorid']);
if ($isadminright != 1) {
	Showmsg('pcpay_noright');
}

if ($_POST['step'] == 2) {
	PostCheck();
	$db->update("UPDATE pw_pcmember SET ifpay=1 WHERE pcmid=" . S::sqlEscape($pcmid));
	echo "success\t$jointype\t$tid\t$payway";
	ajax_footer();
}

require_once PrintEot('ajax');
ajax_footer();
