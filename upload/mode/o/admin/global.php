<?php
!function_exists('adminmsg') && exit('Forbidden');

@include_once(D_P.'data/bbscache/o_config.php');

if (empty($_POST['step'])) {

	ifcheck($o_browseopen,'browseopen');

	require_once PrintMode('global');

} else {

	InitGP(array('config','indexset'),'GP',2);

	$updatecache = false;
	foreach ($config as $key => $value) {
		if (${'o_'.$key} != $value) {
			$db->pw_update(
				'SELECT hk_name FROM pw_hack WHERE hk_name=' . pwEscape("o_$key"),
				'UPDATE pw_hack SET ' . pwSqlSingle(array('hk_value' => $value, 'vtype' => 'string')) . ' WHERE hk_name=' . pwEscape("o_$key"),
				'INSERT INTO pw_hack SET ' . pwSqlSingle(array('hk_name' => "o_$key", 'vtype' => 'string', 'hk_value' => $value))
			);
			$updatecache = true;
		}
	}
	$updatecache && updatecache_conf('o',true);
	adminmsg('operate_success');
}
?>