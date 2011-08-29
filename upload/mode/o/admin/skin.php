<?php
!function_exists('adminmsg') && exit('Forbidden');

//* @include_once pwCache::getPath(D_P.'data/bbscache/o_config.php');
pwCache::getData(D_P.'data/bbscache/o_config.php');

if (empty($_POST['step'])) {
	
	$skindb = array();
	$dir = R_P . 'u/themes/';
	$fp = opendir($dir);

	while ($skinfile = readdir($fp)) {
		if (!in_array($skinfile, array('.', '..', '.svn')) && is_dir($dir . $skinfile)) {
			$skindb[] = $skinfile;
		}
	}

	require_once PrintMode('skin');

} else {

	S::gp(array('style_name'));
	
	$array = array();
	foreach ($style_name as $key => $value) {
	//	!$value && $value = $key;
		$array[$key] = $value;
	}
	setConfig('o_uskin', $array, null, true);
	updatecache_conf('o', true);

	adminmsg('operate_success');
}
?>