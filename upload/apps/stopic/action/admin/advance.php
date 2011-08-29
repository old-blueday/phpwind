<?php
!defined('P_W') && exit('Forbidden');

$ajaxurl = EncodeUrl($stopic_admin_url);
$bg_perpage = 8;

S::gp(array("jobact"));
S::gp(array("stopic_id", "category_id"), null, 2);

if ('save' == $jobact) {

	if ($stopic_id <= 0) Showmsg('参数错误，请您重试', "javascript:history.back();");
	$stopic_data = $stopic_service->getSTopicInfoById($stopic_id);
	if (null == $stopic_data) Showmsg('找不到专题数据，请您重试', "javascript:history.back();");

	S::gp(array("stopic_title", "layout_set", "is_new_bg", "bg_id","is_new_bg", "seo_keyword", "seo_desc"));
	$bg_id = intval($bg_id);

	if ($is_new_bg) $bg_id = 0;
	if ($is_new_bg && count($_FILES) == 1 && $_FILES["background"]["name"] && $_FILES["background"]["size"]) {
		$new_bg_id = $stopic_service->uploadPicture($_FILES, $category_id, $admin_name);
		!$new_bg_id && Showmsg ("对不起，背景图片增加失败", "javascript:history.back();");
		$bg_id = $new_bg_id;
	}
	$bg_id = ($is_new_bg == 2) ? 0 :$bg_id;
	$stopic_service->updateSTopicById($stopic_id, array(
		"title" => $stopic_title,
		"category_id" => $category_id,
		"layout_config" => $layout_set,
		"bg_id" => $bg_id,
		"seo_keyword" => $seo_keyword,
		"seo_desc" => $seo_desc,
	));
	$GLOBALS['ifDelOldUnit'] = true;
	$stopic_service->creatStopicHtml($stopic_id);
	
	ObHeader($basename."&job=$job&stopic_id=$stopic_id");
} else {

	if ($stopic_id) {
		$stopic_data = $stopic_service->getSTopicInfoById($stopic_id);
		if (null == $stopic_data) $stopic_id = 0;
		$layout_data = $stopic_data['layout_config'];
	}
	
	if (empty($layout_data)) $layout_data = $stopic_service->getLayoutDefaultSet();

	$styles	= $stopic_service->getStyles();
	
	$category_id = $category_id ? $category_id : ($stopic_data ? $stopic_data['category_id'] : 0);
	$bg_list = $stopic_service->getPicturesAndDefaultBGs($category_id);
	$bg_total_page = ceil(count($bg_list) / $bg_perpage);
	$is_new_bg = ($stopic_data['bg_id'] == 0) ? "checked" : "";
	
	include stopic_use_layout('iframe');
}
?>