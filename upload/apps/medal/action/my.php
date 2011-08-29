<?php 
!defined('A_P') && exit('Forbidden');
if (!$space =& $newSpace->getInfo()) {
	Showmsg('您访问的空间不存在!');
}

/* 前台勋章页面 */
$medalService = L::loadClass('MedalService', 'medal'); /* @var $medalService PW_MedalService */
if ($a == 'all') {
	$userApply = $medalService->getUserApplys($winduid);
	$awardMedal = $medalService->getAwardMedalUsers(array(),1,10);//动态播放列表
	$medalAll  = $medalService->getAllMedals();
}
$medalTemp = $medalService->getUserMedals($winduid,'all'); //获取会员已经拥有的勋章
$medalCount = count($medalTemp); //总数
$userMedalId = $medal = array();
foreach ($medalTemp as $v) {
	if ($v['is_have'] == 1) {
		$userMedal[$v['medal_id']] = $v;
	}
	$medal[$v['medal_id']] = $v;
}
$userMedalCount = count($userMedal); //开启数
if ($a == 'my') $medal = $userMedal;

require_once PrintEot('m_medal'); 
pwOutPut();

?>