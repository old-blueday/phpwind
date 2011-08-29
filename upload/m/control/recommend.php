<?php
!defined('W_P') && exit('Forbidden');
$defaultType = '';
if ($db_waprecommend) {
	InitGP(array('type','page'),'GP');
	require_once W_P.'include/db/recommend.db.php';
	$recommend = new RecommendDB();
	$recommendTypes = $recommend->getRecommendActiveType();
	$recommendbs = $recommend->getRecommendByType($type,$page);
	$url = "index.php?a=recommend&" . ($type ? "&amp;type=$type" : "") . '&amp;';
	$pages = getPages($page,count($recommendbs),$url);
}else{
	wap_msg ( 'recommend_close');
}
Cookie("wap_scr", serialize(array("page"=>"recommend")));
wap_header();
require_once PrintWAP('recommend');
wap_footer();
?>