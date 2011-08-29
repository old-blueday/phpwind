<?php
!function_exists('adminmsg') && exit('Forbidden');

$url = 'http://tongji.phpwind.com/statistic/?' . bulidQueryString(array(
	'app_key' => $db_sitehash,
	'timestamp' => $timestamp,
	'v' => '1.0',
), $db_siteownerid);

include PrintEot('yunstatistics');exit;

function bulidQueryString($params, $appKey) {
	ksort($params);
	reset($params);
	$pairs = array();
	foreach ($params as $key => $value) {
		$pairs[] = urlencode($key) . '=' . $value;
	}
	$string = implode('&', $pairs);
	$string.= '&sig=' . md5($string .'&' . $appKey);
	return $string;
}
?>