<?php
!defined('P_W') && exit('Forbidden');
function sortTagRelate($row1,$row2) {
	return strcmp($row1['index'], $row2['index']) ;
}
function getTagRelate($tagrelate) {
	$temp = array();
	foreach ($tagrelate['index'] as $key=>$value) {
		$value = (int)$value;
		if (!$tagrelate['title'][$key]) continue;
		$temp[] = array('index'=>$value,'title'=>$tagrelate['title'][$key],'url'=>$tagrelate['url'][$key]);
	}
	usort($temp,'sortTagRelate');
	return $temp;
}
?>