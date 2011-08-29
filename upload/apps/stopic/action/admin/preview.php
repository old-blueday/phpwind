<?php
!defined('P_W') && exit('Forbidden');
define('AJAX',1);
S::gp(array('stopic_id','block_config'));

$stopic_id = (int) $stopic_id;
if (!$stopic_id) showmsg('undefined_error');

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

$stopic_service->updateSTopicById($stopic_id,array('block_config'=>$new_config));

$stopic	= $stopic_service->getSTopicInfoById($stopic_id);

$tpl_content	= $stopic_service->getStopicContent($stopic_id,0);
@extract($stopic, EXTR_SKIP);

include(A_P.'template/stopic.htm');
afooter(1);
?>