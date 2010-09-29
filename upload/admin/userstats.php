<?php
!function_exists('adminmsg') && exit('Forbidden');
//require_once D_P.'data/bbscache/forum_cache.php';
require_once(D_P.'data/bbscache/level.php');
$ltitle['-1'] = getLangInfo('all','reg_member');
$basename = "$admin_file?adminjob=userstats";

if (empty($_POST['action'])) {

	$groupnum = array();
	$query = $db->query("SELECT COUNT(*) AS count,groupid FROM pw_members WHERE groupid!='-1' GROUP BY groupid");
	$s_sum = 0;
	while ($group = $db->fetch_array($query)) {
		$s_sum += $group['count'];
		$groupnum[] = array($group['count'],$group['groupid'],$ltitle[$group['groupid']]);
	}
	$rt = $db->get_one("SELECT totalmember FROM pw_bbsinfo WHERE id='1'");
	$m_sum = $rt['totalmember'] - $s_sum;
	$m_sum < 0 && $m_sum = 0;
	$groupnum[] = array($m_sum,-1,$ltitle['-1']);

	include PrintEot('userstats');exit;
}
?>