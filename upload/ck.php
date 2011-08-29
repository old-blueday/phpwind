<?php
define('CK', 1);
require_once('global.php');
if (S::getServer('HTTP_IF_MODIFIED_SINCE') || S::getServer('HTTP_IF_NONE_MATCH') || empty($_COOKIE) && !$pwServer['HTTP_USER_AGENT']) {
	sendHeader('304');exit;
}

if ($_GET['admin']) {
	$db_ckpath	 = '/';
	$db_ckdomain = '';
}

header('Pragma:no-cache');
header('Cache-control:no-cache');

$checkCode = L::loadClass('checkcode', 'site');
$checkCode->out();
exit;
?>