<?php 
!defined('A_P') && exit('Forbidden');
/* 申请操作 */
define('AJAX','1');
S::gp(array('id'));
if (!$winduid) Showmsg('您还未登录');
$id = (int) $id;
if ($id < 1 || !$db_md_ifapply)  Showmsg('非法操作'); 
$medalService = L::loadClass('MedalService', 'medal'); /* @var $medalService PW_MedalService */
$medalInfo = $medalService->getMedal($id);
if (!in_array($winddb['memberid'], (array)$medalInfo['allow_group']) && $medalInfo['allow_group']) Showmsg('您所在用户组暂时无法申请该勋章'); 
$result = $medalService->applyMedal($winduid, $id);
if (is_array($result)) {
	Showmsg($result[1]);
} else {
	Showmsg('申请成功！');
}
?>  