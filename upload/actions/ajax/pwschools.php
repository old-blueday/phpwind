<?php
!defined('P_W') && exit('Forbidden');

define('AJAX','1');
S::gp(array(
	'type',
	'areaid'
),2);
S::gp(array('job','sid'));
if (empty($job)) {
	$jsFile = D_P . 'data/bbscache/areadata.js';
	if (!file_exists($jsFile)) {
		$areaService = L::LoadClass('areasservice','utility');
		$areaService->setAreaCache();
	}
	require_once PrintEot('ajax');
	footer();
} elseif ($job == 'getschools') {
	$schoolService = L::LoadClass('schoolservice','user');
	$schools = $schoolService->getByAreaAndType($areaid,$type);
	echo pwJsonEncode($schools);
	ajax_footer();
} elseif ($job == 'deleducation') {
	S::gp(array('educationid'));
	$educationid = (int) $educationid;
	if ($educationid < 1) {
		echo "error\t数据错误";
		ajax_footer();
		exit;
	}
	$educationService = L::loadClass('EducationService', 'user');
	$educationItemInfo = $educationService->getEducationById($educationid);
	if (!S::isArray($educationItemInfo) || $educationItemInfo['uid'] != $winduid) {
		echo "error\t非法操作";
		ajax_footer();
		exit;
	}
	$ifSuccess = $educationService->deleteEducationById($educationid);
	if ($ifSuccess) {
		echo "success\t删除成功";
		ajax_footer();
		exit;
	} else {
		echo "error\t删除失败";
		ajax_footer();
		exit;
	}
} elseif ($job == 'delcareer') {
	S::gp(array('careerid'));
	$careerid = (int) $careerid;
	if ($careerid < 1) {
		echo "error\t数据错误";
		ajax_footer();
		exit;
	}
	$careerService = L::loadClass('CareerService', 'user');
	$careerItemInfo = $careerService->getCareerById($careerid);
	if (!S::isArray($careerItemInfo) || $careerItemInfo['uid'] != $winduid) {
		echo "error\t非法操作";
		ajax_footer();
		exit;
	}
	$ifSuccess = $careerService->deleteCareerById($careerid);
	if ($ifSuccess) {
		echo "success\t删除成功";
		ajax_footer();
		exit;
	} else {
		echo "error\t删除失败";
		ajax_footer();
		exit;
	}
}
