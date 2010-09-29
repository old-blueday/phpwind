<?php
!defined('P_W') && exit('Forbidden');

InitGP(array(
	'pcmid',
	'pcid',
	'tid',
	'jointype',
	'payway'
));
$read = $db->get_one("SELECT authorid,subject,fid FROM pw_threads WHERE tid=" . pwEscape($tid));
$foruminfo = $db->get_one('SELECT forumadmin,fupadmin FROM pw_forums WHERE fid=' . pwEscape($read['fid']));
$isGM = CkInArray($windid, $manager);
$isBM = admincheck($foruminfo['forumadmin'], $foruminfo['fupadmin'], $windid);
L::loadClass('postcate', 'forum', false);
$post = array();
$postCate = new postCate($post);
$isadminright = $postCate->getAdminright($pcid, $read['authorid']);
if ($isadminright != 1) {
	echo "noright\t";
	ajax_footer();
}

$db->UPDATE("DELETE FROM pw_pcmember WHERE pcmid=" . pwEscape($pcmid));

echo "success\t$jointype\t$tid\t$payway";
ajax_footer();
