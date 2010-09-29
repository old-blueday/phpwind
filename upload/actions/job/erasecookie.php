<?php
!defined('P_W') && exit('Forbidden');

checkVerify('loginhash');
$cookiepre = CookiePre() . '_';
foreach ($_COOKIE as $key => $value) {
	if (strpos($key, $cookiepre) === 0) {
		Cookie(substr($key, strlen($cookiepre)), '', 0);
	}
}
$referer = strpos($pwServer['HTTP_REFERER'], $db_bbsurl) === 0 ? $pwServer['HTTP_REFERER'] : $db_bbsurl . '/' . $db_bfn;
ObHeader($referer);
