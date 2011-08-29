<?php
!defined('A_P') && exit('Forbidden');
S::gp(array('do'));
$USCR = 'space_weibo';
$whilelist = array(
	'my','ajax','detail'
);
if (!in_array($do, $whilelist)) {
	$do = 'my';
}
$perpage = 20;

if ($do == 'my' && $indexRight) {
	$count = $weiboService->getUserWeibosCount($uid);
	$pageCount = ceil($count / $perpage);
	$page = validatePage($page,$pageCount);
	$weiboList = $weiboService->getUserWeibos($uid,$page,$perpage);
	$pages = numofpage($count, $page, $pageCount, "apps.php?q=weibo&do=my&uid=$uid&", null, 'weiboList.my');
/*单条新鲜事页面*/
}elseif ($do == 'detail' && $indexRight){ 
	S::gp(array('','mid','identify','commentpage'), 'GP');
	$weiboList = $weiboService->getWeibosByMid($mid);
	$weiboList = array($weiboList);
	$weiboList = $weiboService->buildData($weiboList);
}
if (defined('AJAX')) {
	
		require_once PrintEot('m_ajax');
		ajax_footer();
	
	} else {
		require_once PrintEot('m_space_weibo');
		pwOutPut();
	}
