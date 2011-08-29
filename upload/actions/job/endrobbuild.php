<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('tid'));

$robbuildService = L::loadClass('RobBuild', 'forum');
$robbuild = $robbuildService->getByTid($tid);

((!S::inArray($windid,$manager) && $robbuild['authorid'] != $winduid) || $robbuild['status']) && Showmsg('undefined_action');

$robbuildService->update(array('status'=>2),$tid);
refreshto("read.php?tid=$tid", 'operate_success');