<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('jobact'));

if ("delete" == $jobact) {
	S::gp(array('stopic_ids'));
	if (!is_array($stopic_ids) || !count($stopic_ids)) Showmsg('没选择要删除的专题，请您重试', $stopic_admin_url."&job=$job");

	if (!$stopic_service->deleteSTopics($stopic_ids)) Showmsg('所有信息均未修改，请您重试', $stopic_admin_url."&job=$job");
	ObHeader($stopic_admin_url."&job=$job");
} else {
	S::gp(array('page', 'search_title', 'search_cid'));
	$page = intval($page);
	$sum = $stopic_service->countSTopic($search_title, $search_cid);

	$total = ceil($sum/$db_perpage);
	if ($page <= 0) $page = 1;
	if ($page > $total) $page = $total;
	$pages = numofpage($sum,$page,$total,$stopic_admin_url."&job=$job&search_title=$search_title&search_cid=$search_cid&");

	$stopic_list = $stopic_service->findSTopicInPage($page, $db_perpage, $search_title, $search_cid);
	$category_list = $stopic_service->getCategorys();
}

include stopic_use_layout('admin');
?>