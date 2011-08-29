<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=setads";

if ($action != 'submit') {

	${'ads_'.$db_ads}='checked';
	include PrintEot('setads');

} elseif ($_POST['action'] == "submit") {

	S::gp(array('ads'),'P');
	setConfig('db_ads', $ads);
	updatecache_c();
	adminmsg('operate_success');
}
?>