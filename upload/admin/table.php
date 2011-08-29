<?php
!function_exists('adminmsg') && exit('Forbidden');
require_once GetLang('dbtable');

if (trim($db_modelids,',')) {
	$m_list = explode(',',$db_modelids);
	foreach ($m_list as $value) {
		$table = 'pw_topicvalue'.intval($value);
		$lang['dbtable'][$table] = $lang['dbtable']['pw_topicvalue'];
	}
}
if (trim($db_pcids,',')) {
	$m_list = explode(',',$db_pcids);
	foreach ($m_list as $value) {
		$table = 'pw_pcvalue'.intval($value);
		$lang['dbtable'][$table] = $lang['dbtable']['pw_pcvalue'];
	}
}
if ($db_plist && count($db_plist)>1) {
	foreach ($db_plist as $key => $value) {
		if($key == 0) continue;
		$table = 'pw_posts'.$key;
		$lang['dbtable'][$table] = $lang['dbtable']['pw_posts'];
	}
}
if ($db_tlist) {
	!is_array($db_tlist) && $db_tlist = array();
	foreach ($db_tlist as $key => $value) {
		if ($key == 0) continue;
		$table = 'pw_tmsgs'.$key;
		$lang['dbtable'][$table] = $lang['dbtable']['pw_tmsgs'];
	}
}
unset($lang['dbtable']['pw_topicvalue'], $lang['dbtable']['pw_pcvalue']);
$tabledb = array_keys($lang['dbtable']);
sort($tabledb);

function N_getTabledb($showother = null){//fix bug $PW
	global $tabledb, $PW;
	$table_a = array();
	if ($PW != 'pw_') {
		foreach ($tabledb as $key => $value) {
			$table_a[0][$key] = str_replace('pw_', $PW, $value);
		}
	} else {
		$table_a[0] = $tabledb;
	}
	if (!empty($showother)) {
		global $db;
		$table_a[1] = array();
		$postsMergeTable = $PW != 'pw_' ? str_replace('pw_', $PW, 'pw_merge_posts') : 'pw_merge_posts';
		$tmsgsMergeTable = $PW != 'pw_' ? str_replace('pw_', $PW, 'pw_merge_tmsgs') : 'pw_merge_tmsgs';
		$creditLogMergeTable = $PW != 'pw_' ? str_replace('pw_', $PW, 'pw_merge_creditlog') : 'pw_merge_creditlog';
		$query = $db->query('SHOW TABLES');
		while ($rt = $db->fetch_array($query,MYSQL_NUM)) {
			$value = trim($rt[0]);
			if (in_array($value, array($postsMergeTable, $tmsgsMergeTable, $creditLogMergeTable))) continue;
			!in_array($value,$table_a[0]) && $table_a[1][] = $value;
		}
	}
	return $table_a;
}
?>