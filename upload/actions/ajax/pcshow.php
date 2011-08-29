<?php
!defined('P_W') && exit('Forbidden');

S::gp(array(
	'pcmid',
	'pcid',
	'tid'
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

$pcmember = $db->get_one("SELECT uid,username,name,zip,message,nums,totalcash,phone,mobile,address,extra,ifpay FROM pw_pcmember WHERE pcmid=" . S::sqlEscape($pcmid));

require_once PrintEot('ajax');
ajax_footer();
