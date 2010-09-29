<?php
!function_exists('readover') && exit('Forbidden');

$dirname = '';
if ($db_forumdir == '1') {
	$dirname = substr($pwServer['PHP_SELF'], 0, strrpos($pwServer['PHP_SELF'],'/'));
	$dirname = substr($dirname,strrpos($dirname,'/')+1);
} elseif ($db_forumdir == '2') {
	$dirname = substr($pwServer['HTTP_HOST'], 0, strpos($pwServer['HTTP_HOST'],'.'));
}
$sqlwhere = " AND (f.type!='category' OR f.type='category' AND f.dirname='')";
if ($dirname) {
	$fids = array();
	$query = $db->query("SELECT fid FROM pw_forums WHERE type='category' AND dirname=" . pwEscape($dirname,false));
	while ($forums = $db->fetch_array($query,MYSQL_NUM)) {
		$fids[] = $forums[0];
	}
	if ($fids) {
		$fids = pwImplode($fids);
		$sqlwhere = "AND (f.fid IN ($fids) OR f.fup IN ($fids))";
	}
}
?>