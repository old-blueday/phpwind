<?php
!defined('P_W') && exit('Forbidden');

foreach ($nav_left['applicationcenter']['items']['onlineapplication']['items'] as $key=>$value) {
	if ($value == 'sinaweibo') { //for compatible
		unset($nav_left['applicationcenter']['items']['onlineapplication']['items'][$key]);
	}
}

$nav_left['applicationcenter']['items']['onlineapplication']['items'][] = 'platformweiboapp';
?>