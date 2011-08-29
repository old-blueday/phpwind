<?php
!defined('P_W') && exit('Forbidden');

S::gp(array("stopic_id"), null, 2);

$tpl_content = '';
if ($stopic_id) {
	$stopic_data = $stopic_service->getSTopicInfoById($stopic_id);
	if (null == $stopic_data) Showmsg('找不到专题数据，请您重试', $basename."&job=stman");
	$tpl_content =  $stopic_service->getStopicContent($stopic_id,1);
} else {
	$stopic_data = $stopic_service->getEmptySTopic();
	if ($stopic_data) $stopic_id = $stopic_data['stopic_id'];
}

$layout_data = !empty($stopic_data['layout_config']) ? $stopic_data['layout_config'] : $stopic_service->getLayoutDefaultSet();

$layout_list = $stopic_service->getLayoutList();
$category_list = $stopic_service->getCategorys();
$styles	= $stopic_service->getStyles();
$blocks	= $stopic_service->getBlocks();

$ajaxurl = EncodeUrl($stopic_admin_url);

$file_url = $stopic_service->_getSTopicConfig('htmlUrl') . '/';
$file_name = isset($stopic_data['file_name']) && $stopic_data['file_name'] ? $stopic_data['file_name'] : ($stopic_id ? $stopic_id : '');
$file_suffix = $stopic_service->_getSTopicConfig('htmlSuffix');

if ('' == $tpl_content) {
	$append_default_banner = 'block_banner_101';
	$append_default_nvgt = 'block_nvgt_101';
	if (null == $stopic_service->getStopicUnitByStopic($stopic_id, $append_default_banner)) {
		$stopic_service->addUnit(array(
			'stopic_id'=>$stopic_id,
			'html_id'=>$append_default_banner,
			'title'=>'',
			'data'=>array(
				'image' => $layout_data['bannerurl'],
				'title' => '我的专题',
				'title_left' => '50',
				'title_top' => '50',
				'title_style' => '',
				'title_size' => '50',
				'title_color' => '#000000',
			),
		));
	}
	if (null == $stopic_service->getStopicUnitByStopic($stopic_id, $append_default_nvgt)) {
		$stopic_service->addUnit(array(
			'stopic_id'=>$stopic_id,
			'html_id'=>$append_default_nvgt,
			'title'=>'',
			'data'=>array(
				'link_color' => $layout_data['navfontcolor'],
				'link_bgcolor' => $layout_data['navbgcolor'],
				'nav' => array(
					array('title' => '导航链接1', 'url' => 'javascript:;',),
					array('title' => '导航链接2', 'url' => 'javascript:;',),
					array('title' => '导航链接3', 'url' => 'javascript:;',),
				)
			),
		));
	}
}

include stopic_use_layout('admin');
?>