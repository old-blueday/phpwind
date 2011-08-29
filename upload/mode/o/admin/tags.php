<?php
!function_exists('adminmsg') && exit('Forbidden');
$nav = array("$action" => "class='current'");

$memberTagsService = L::loadClass('MemberTagsService', 'user');
S::gp(array('action','tagid','tagname','ifhot','startnum','endnum','page'),'GP');

$perpage = $perpage ? $perpage : 20;
$jumpUrl = "$basename&tagname=".rawurlencode($tagname)."&ifhot=$ifhot&startnum=$startnum&endnum=$endnum&page=$page&";
if (empty($action)) {
	${'sel_'.$ifhot} = 'selected';
	$page = max((int) $page, 1);
	list($count,$tags) = $memberTagsService->getTagsByCondition($tagname, $ifhot, $startnum, $endnum, ($page - 1) * $perpage, $perpage);
	$numofpage = ceil($count/$perpage);
	if ($numofpage && $page > $numofpage) {
		$page = $numofpage;
	}

	$pages=numofpage($count,$page,$numofpage,$jumpUrl);
	require_once PrintMode('tags');exit;
} elseif ($action == 'deltags') {
	if (!$tagid) adminmsg('operate_error');
	$userIds = $memberTagsService->getUidsByTagids($tagid);
	if ($memberTagsService->deleteTagsByTagIds($tagid)) {
		$userCache = L::loadClass('UserCache', 'user');
		$userCache->delete($userIds, 'tags');
		$memberTagsService->setTopCache(100,1);
	}
	adminmsg('operate_success',$jumpUrl);

} elseif ($action == 'sethot') {
	if (!$tagid) adminmsg('operate_error');
	$memberTagsService->setHotByTagids($tagid,1);
	$memberTagsService->setTopCache(100,1);
	adminmsg('operate_success',$jumpUrl);
} elseif ($action == 'setnothot') {
	echo $startnum;
	if (!$tagid) adminmsg('operate_error');
	$memberTagsService->setHotByTagids($tagid,0);
	$memberTagsService->setTopCache(100,1);
	adminmsg('operate_success',$jumpUrl);
}

?>