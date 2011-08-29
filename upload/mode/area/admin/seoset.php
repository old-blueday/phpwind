<?php
!function_exists('adminmsg') && exit('Forbidden');
$m = $db_mode = 'area';
define('M_P',R_P . "mode/$db_mode/");

$channel = L::loadClass('channelService', 'area');
$clist = $channel->getChannels();
if ($action == 'update') {
	S::gp(array('channellist'),'P');
	foreach ($channellist as $key=>$value) {
		if (!isset($clist[$key])) continue;
		$seo = array(); 
		$seo['metatitle'] = trim(strip_tags($value['metatitle']));
		$seo['metadescrip'] = trim(strip_tags($value['metadescrip']));
		$seo['metakeywords'] = trim(strip_tags($value['metakeywords']));
		if ($clist[$key]['metatitle'] != $seo['metatitle'] || $clist[$key]['metadescrip'] != $seo['metadescrip'] || $clist[$key]['metakeywords'] != $seo['metakeywords']) {
			$channel->updateChannelSEO($clist[$key]['id'], $seo);
		}
	}
	adminmsg('operate_success', "$basename&mode=$db_mode");
} else {
	include PrintMode('seoset');
}
?>