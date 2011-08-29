<?php
!defined('P_W') && exit('Forbidden');

$threadlog = explode(',', GetCookie('threadlog'));
@krsort($threadlog);
$fids = ',';
$i = 0;
foreach ($threadlog as $key => $value) {
	if (is_numeric($value)) {
		$fids .= $value . ',';
		if (++$i > 9) break;
	}
}
Cookie('threadlog', $fids);
//* include_once pwCache::getPath(D_P . 'data/bbscache/forum_cache.php');
pwCache::getData(D_P . 'data/bbscache/forum_cache.php');
$threaddb = array();
foreach ($forum as $key => $value) {
	if (in_array($key, $threadlog)) {
		$threaddb[$key] = $value['name'];
	}
}
require_once PrintEot('ajax');
ajax_footer();
