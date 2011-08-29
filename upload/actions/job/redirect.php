<?php
!defined('P_W') && exit('Forbidden');

S::gp(array(
	'goto'
));
S::gp(array(
	'aid'
), 'GP', 2);

$pw_attachs = L::loadDB('attachs', 'forum');
$attachs = $pw_attachs->get($aid);
if ($attachs) {
	if ($goto == 'next') {
		$aid = $pw_attachs->nextImgByUid($attachs['uid'], $attachs['aid']);
	} elseif ($goto == 'pre') {
		$aid = $pw_attachs->prevImgByUid($attachs['uid'], $attachs['aid']);
	}
	$aid = intval($aid);
	ObHeader("show.php?action=pic&aid=$aid");
} else {
	Showmsg('pic_not_exists');
}
