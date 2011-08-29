<?php
!function_exists('readover') && exit('Forbidden');

S::gp(array('step','info_type'));
if (!$step) {
	$memberTagsService = L::loadClass('MemberTagsService','user');
	$modelList['tags'] = array('num' => 10,'expire' => 7200 );
	$spaceData = $newSpace->getSpaceData($modelList);
	$memberTags = $spaceData['tags'];//个人标签
	$hotTagsNum = $memberTagsService->countHotTagsNum();
	$hotTags = $memberTagsService->getTagsByNum(8);

	require_once uTemplate::PrintEot('info_tags');
	pwOutPut();
}
?>