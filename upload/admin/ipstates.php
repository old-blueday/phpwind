<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$admin_file?adminjob=ipban&job=ipstates";
if ($action != 'submit' && $action != 'ipIndex') {
	ifcheck($db_ipstates, 'ipstates');
	include PrintEot('ipstates');
} elseif ($_POST['action'] == "submit") {
	S::gp(array('ipstates'), 'P');
	setConfig('db_ipstates', $ipstates);
	updatecache_c();
	$navConfigService = L::loadClass('navconfig', 'site');
	$navConfigService->controlShowByKey('sort_ipstate', $ipstates);
	adminmsg('operate_success');
} elseif ($action == "ipIndex") {
	$ipTable = L::loadClass('IPTable', 'utility');
	$ipTable->createIpIndex();
	adminmsg('operate_success');
}
?>