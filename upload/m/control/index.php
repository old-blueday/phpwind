<?php
!defined('W_P') && exit('Forbidden');
if ($db_waphottopictoday) {
	$element = L::loadclass('element');
	$hots24 = $element->replySortDay(0,4);
}
if ($db_waphottopicweek) {
	$element = L::loadclass('element');
	$hotsw7 = $element->replySortWeek(0,4);
}
$defaultType = '';
if ($db_waprecommend) {
	InitGP(array('type'),'GP');
	require_once W_P.'include/db/recommend.db.php';
	$recommend = new RecommendDB();
	$recommend->setPerPage(4);
	$recommendTypes = $recommend->getRecommendActiveType();
	$recommendbs = $recommend->getRecommendByType($type);
}

$wapfids = array();
if ($db_wapfids) {
	$tmpwapfids = explode(',',$db_wapfids);
	foreach ($tmpwapfids as $val) {
		$wapfids[$val] = wap_cv(strip_tags($forum[$val]['name']));
	}
} else {
	$wapfids = pwGetShortcut();
}
Cookie("wap_scr", serialize(array("page"=>"index")));
wap_header();
require_once PrintWAP('index');
wap_footer();
?>
