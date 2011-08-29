<?php
!function_exists('adminmsg') && exit('Forbidden');
$siteBindInfoService = L::loadClass('WeiboSiteBindInfoService', 'sns/weibotoplatform/service'); /* @var $siteBindInfoService PW_WeiboSiteBindInfoService */

$templatesConfig = array(
	'article' => array(
		'title' => '帖子内容',
		'description' => '{title}为帖子标题 ; {content}为帖子内容摘要 ; {url}为帖子地址',
	),
	'diary' => array(
		'title' => '日志内容',
		'description' => '{title}为日志标题;  {content}为日志内容摘要;  {url}为日志地址',
	),
	'group_active' => array(
		'title' => '群组活动',
		'description' => '{title}为群组活动标题; {content}为群组活动内容摘要; {url}为群组活动地址',
	),
	'cms' => array(
		'title' => '文章内容',
		'description' => '{title}为文章标题; {content}为文章内容摘要;  {url}为文章地址',
	),
	'photos' => array(
		'title' => '相册',
		'description' => '{photo_count}为照片张数;  {url}为相册地址',
	),
	'group_photos' => array(
		'title' => '群组相册',
		'description' => '{photo_count}为照片张数;  {url}为群组相册地址',
	),
);

InitGP(array('step', 'templates'));
if ($step == 'edit' && !empty($templates)) {
	$warningMessage = '';
	foreach ($templatesConfig as $key => $value) {
		if (!isset($templates[$key]) || '' == $templates[$key]) $warningMessage = '所有微博模版不能为空';
	}
	if (!$warningMessage) {
		$siteBindInfoService->saveWeiboTemplates($templates);
		$warningMessage = '恭喜, 设置成功了';
	}
}

$templatesSet = $siteBindInfoService->getWeiboTemplates();

include PrintTemplate('custom_weibo_template');
exit();
?>