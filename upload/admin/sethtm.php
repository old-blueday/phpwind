<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=sethtm";

if (!$_POST['step']) {

	ifcheck($db_htmifopen,'htmifopen');
	!$db_dir && $db_dir='.php?';
	!$db_ext && $db_ext='.html';
	include PrintEot('sethtm');exit;

} elseif ($_POST['step'] == 2) {

	S::gp(array('config'),'P');
	foreach ($config as $key => $value) {
		setConfig('db_'.$key, $value);
	}
	updatecache_c();
	adminmsg('operate_success');
}
?>