<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = $admin_file.'?adminjob=repair';
if (in_array($_POST['action'],array('repair','optimize'))) {
	!$_POST['tabledb'] && adminmsg('db_empty_tables');
	$action = strtoupper($_POST['action']);
	$table = S::escapeChar(implode(', ',$_POST['tabledb']));
	@set_time_limit(200);
	if($_POST['action']=='repair'){
		$query = $db->query("$action TABLE $table EXTENDED");
	}else{
		$query = $db->query("$action TABLE $table");
	}
	while ($rt = $db->fetch_array($query)) {
		$rt['Table']  = substr(strrchr($rt['Table'] ,'.'),1);
		$msgdb[] = $rt;
	}
	$db->free_result($query);
} else {
	require_once(R_P.'admin/table.php');
	list($pwdb,$otherdb) = N_getTabledb(true);
}
include PrintEot('repair');exit;
?>