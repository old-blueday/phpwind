<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('aid'), 'GP', 2);

empty($aid) && Showmsg('job_attach_error');
$attachService = L::loadClass('attachs','forum');
$attachInfo = $attachService->getByAid($aid);

if (!S::isArray($attachInfo) || $attachInfo['type'] != 'img' || !$attachInfo['tid']) Showmsg('job_attach_error');

$tucoolService = L::loadClass('tucool','forum');

if ($tucoolService->setCover($attachInfo['tid'],$attachInfo['attachurl'])){
	echo "success";
	ajax_footer();
}
Showmsg('undefined_action');