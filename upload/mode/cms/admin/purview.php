<?php
!defined('P_W') && exit('Forbidden');
$baseUrl = $admin_file . "?adminjob=mode&admintype=cms_purview&";
$purviewService = C::loadClass('PurviewService');
/* @var $purviewService PW_PurviewService */
S::gp(array('action', 'page'));
if (!$action) {
	$perPage = 20;
	S::gp(array('username'));
	$cmsPurviews = $purviewService->findAll($username, $page, $perPage);
	if (!$username) {
		$count = $purviewService->countPurview();
		$pager = pwGetPager($count, $page, $perPage, $baseUrl);
	}
	$ajaxUrl = EncodeUrl($baseUrl);

} elseif ($action == 'add') {
	S::gp(array('username', 'pid'));
	if (!empty($pid)) {
		$userpurview = $purviewService->findPurviewById($pid);
	}
	$superchecked = $userpurview['super'] ? "checked=checked" : "";
	$columns = $purviewService->getAllColumns();

} elseif ($action == 'addsubmit') {
	S::gp(array('pid', 'username', 'columnids', 'super'));
	if (empty($username)) Showmsg('用户名不能为空', $basename . '&action=add&pid=' . $pid);
	if (!empty($columnids) && !is_array($columnids)) Showmsg('非法操作', $basename . '&action=add&pid=' . $pid);
	if ($super) {
		$columnids = array_keys($purviewService->getAllColumns());
	}
	if ($pid) {
		if (!$purviewService->updatePruviewByUser($username, $columnids, $super, $pid)) Showmsg('编辑操作失败,用户名可能不存在！');
	} else {
		if (!$purviewService->insertPruviewByUser($username, $columnids, $super)) Showmsg('添加操作失败');
	}
	$purviewService->updatePurviewCache();
	Showmsg('操作成功!', $basename . '&$action=add');

} elseif ($action == 'del') {
	S::gp(array('pid'));
	if (empty($pid)) Showmsg('非法操作');
	if (!$purviewService->deletePruviewById($pid)) Showmsg('删除操作失败');
	$purviewService->updatePurviewCache();
	Showmsg('操作成功!');

}

function pwGetPager($count, $page, $perpage, $url) {
	$page = (intval($page) < 1) ? 1 : intval($page);
	$perpage = $perpage ? $perpage : 20;
	$numofpage = ceil($count / $perpage);
	return numofpage($count, $page, $numofpage, $baseUrl);
}

function getColumnLevelHtml($level, $cid) {
	if ($level == 0) {
		return '<i id="column_' . $cid . '" class="expand expand_b" onclick="closeAllSubColumns(' . $cid . ')"></i>';
	} else {
		$html .= '';
		for ($i = 1; $i < $level; $i++) {
			$html .= '<i id="" class="lower lower_a"></i>';
		}
		$html .= '<i id="" class="lower"></i>';
	}
	return $html;
}

include PrintMode('purview');
exit();
?>