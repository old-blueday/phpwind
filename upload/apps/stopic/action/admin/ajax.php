<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('jobact'));
define('AJAX',1);

if ("showbg" == $jobact) {
	$bg_perpage = 6;
	S::gp(array('page', 'cid', 'bgid'), null, 2);

	$sum = $stopic_service->countPictures($cid);
	$total = ceil($sum/$bg_perpage);

	if ($page <= 0) $page = 1;
	if ($page > $total) $page = $total;
	//$bg_list = $stopic_service->getPictures($page, $bg_perpage, $cid);
	$bg_list = $stopic_service->getPicturesAndDefaultBGs($cid);

	$is_lastpage = $page == $total || !$sum;
} elseif ("showcopy" == $jobact) {
	S::gp(array('cid'));
	$cid = intval($cid);
	$useful_stopics = $stopic_service->findUsefulSTopicInCategory(5, $cid);
}


include stopic_use_layout('ajax');
?>