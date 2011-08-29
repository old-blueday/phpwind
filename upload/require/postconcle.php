<?php
!function_exists('readover') && exit('Forbidden');

global $db_picpath,$db_attachname;
$imgdt    = $timestamp + $db_hour;
$attachdt = $imgdt + $db_hour * 100;
if (@rename($db_picpath,$imgdt) && @rename($db_attachname,$attachdt)) {
	require_once(R_P.'admin/cache.php');
	setConfig('db_picpath', $imgdt);
	setConfig('db_attachname', $attachdt);
	updatecache_c();
}
pwCache::setData(D_P."data/bbscache/set_cache.php","<?php die;?>|$timestamp");
?>