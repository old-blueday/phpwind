<?php
defined('OPEN_IM_ENABLED') || exit('Forbidden');
//modded for OpenIM
//OpenIM亮灯的view helper
$openImLampViewHelper = L::loadClass('OpenImLamp', 'openim');
if ($db_openim_isopen) {
	$openImLampViewHelper->enableLamp();
} else {
	$openImLampViewHelper->disableLamp();
}

$openimService = L::loadClass('OpenIm', 'openim');
//$openimService->initPlatformApiClient($db_sitehash, $db_siteownerid, $charset);
$openImLampViewHelper->setSiteId($openimService->getSiteId());
unset($openimService);
$openImLampViewHelper->setEncoding($charset);
$openImLampViewHelper->setClientDownloadUrl($db_bbsurl . '/yunliao.php');
$openImLampViewHelper->setViewerUserId($winduid);