<?php
!defined('P_W') && exit('Forbidden');
define('AJAX',1);
S::gp(array('stopic_id','category_id','is_cover'), null, 2);
S::gp(array('block_config','stopic_title', 'file_name'));

$file_name = trim($file_name);
if ('' == $file_name) $file_name = $stopic_id;

if (!$stopic_id || !$category_id) showmsg('undefined_error');
$stopic_data = $stopic_service->getSTopicInfoById($stopic_id);
if (empty($stopic_data)) showmsg('undefined_error');
if (!stopic_check_file_name($file_name)) Showmsg('文件名格式错误，只允许英文字母、数字、“-”和“_”', $basename."&job=stman");

$old_file_name = $stopic_data['file_name'];
if ('' == $old_file_name) $old_file_name = $stopic_id;

$is_conflict = false;
if ($file_name != $old_file_name || $stopic_service->isFileUsed($stopic_id, $file_name)) {
	if (file_exists($stopic_service->getStopicDir($stopic_id, $file_name))) {
		$is_conflict = true;
	}
}

if ($is_conflict && !$is_cover) {
	$job = 'showconfirm';
	include stopic_use_layout('ajax');
} else {
	$new_config = array();
	if (isset($block_config['container'])) {
		foreach ($block_config['container'] as $layout_id) {
			foreach ($block_config as $block_container => $blocks) {
				if (false !== strpos($block_container, $layout_id)) {
					$new_config[$layout_id][substr($block_container, strlen($layout_id)+1)] = $block_config[$block_container];
					unset($block_config[$block_container]);
				}
			}
		}
	}
	
	$update_fields = array('category_id'=>$category_id, 'title'=>$stopic_title, 'block_config'=>$new_config, 'file_name'=>$file_name);
	if ('' == $stopic_data['layout_config']) $update_fields['layout_config'] = $stopic_service->getLayoutDefaultSet();
	$stopic_service->updateSTopicById($stopic_id, $update_fields);
	
	$stopic_service->creatStopicHtml($stopic_id);
	
	$stopicUrl	= $stopic_service->getStopicUrl($stopic_id, $file_name);
	
	include stopic_use_layout('ajax');
}
?>