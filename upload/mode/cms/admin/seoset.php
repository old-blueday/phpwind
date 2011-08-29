<?php
!defined('P_W') && exit('Forbidden');
$m = $db_mode = 'cms';
define('M_P', R_P . "mode/$db_mode/");
require_once (R_P . 'mode/cms/require/core.php');
S::gp(array('action'));

$columnService = C::loadClass('columnservice');
/* @var $columnService PW_ColumnService */

if ($action == 'update') {
	S::gp(array('seo', 'seoset'), 'P');
	foreach ($seo as $key => $value) {
		$seo = array();
		$seo['cid'] = $key;
		$seo['seotitle'] = trim(strip_tags($value['seotitle']));
		$seo['seodesc'] = trim(strip_tags($value['seodesc']));
		$seo['seokeywords'] = trim(strip_tags($value['seokeywords']));
		$columnService->updateColumnSEO($seo);
	}
	foreach ($seoset as $key => $value) {
		foreach ($value as $k => $var) {
			$seoset[$key][$k] = S::escapeChar(strip_tags($var));
		}
	}
	setConfig('cms_seoset',$seoset,null,true);
	updatecache_conf('cms', true);
	adminmsg('operate_success', "$basename&mode=$db_mode");
} else {
	$channles = $columnService->getAllOrderColumns();
	include PrintMode('seoset');
}

/**
 * @param unknown_type $level
 */
function getColumnLevelHtml($level) {
	if ($level == 0) {
		return '<i class="expand expand_b"></i>';
	} else {
		$html .= '';
		for ($i = 1; $i < $level; $i++) {
			$html .= '<i id="" class="lower lower_a"></i>';
		}
		$html .= '<i id="" class="lower"></i>';
	}
	return $html;
}
?>