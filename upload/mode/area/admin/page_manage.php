<?php
!defined('P_W') && exit('Forbidden');
$portalPageService = L::loadClass('portalpageservice', 'area');
$actionUrl=$admin_file."?adminjob=mode&admintype=area_page_manage";
$ajaxActionUrl=EncodeUrl($actionUrl);

if (!$action) {
	$portalPages = $portalPageService->getPortalPagesFromDB();
	include PrintMode('page_manage');exit;
} elseif ($action =='update') {
	S::gp(array('sign'));
	if (!$sign) {
		echo '数据有误';
		ajax_footer();exit;
	}
	$staticPath = S::escapePath(PORTAL_PATH . $sign .'/index.html');
	touch($staticPath,strtotime('1970'));
	echo getLangInfo('msg','operate_success');
	ajax_footer();exit;
} elseif ($action == 'clear') {
	S::gp(array('sign'));
	if (!$sign) Showmsg('数据有误');
	$portalPageService->deletePortalPage($sign);
	updatePortalTemplate($sign);
	Showmsg("修改成功!");
}

function updatePortalTemplate($sign) {
	$tarTpl = S::escapePath(D_P . "data/tplcache/portal_" . $sign . '.htm');
	P_unlink($tarTpl);
}