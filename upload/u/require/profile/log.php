<?php
!defined('R_P') && exit('Forbidden');

$_G['atclog'] || Showmsg('no_atclog_right');
S::gp(array('page','type'));
$page = (int)$page;
require_once GetLang('logtype');
require_once(R_P.'require/functions.php');
require_once(R_P.'require/forum.php');
//* include_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
pwCache::getData(D_P.'data/bbscache/forum_cache.php');
$sqladd = "WHERE username1=".S::sqlEscape($windid,false);
if ($type && $lang['logtype'][$type]) {
	$sqladd .= " AND type=".S::sqlEscape($type);
	$type_sel[$type] = 'selected';
} else {
	$type = '';$type_sel = array();
}
$db_perpage = 15;
(!is_numeric($page) || $page < 1) && $page = 1;
$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
$count = $db->get_value("SELECT COUNT(*) AS count FROM pw_adminlog $sqladd");
$pages = numofpage($count,$page,ceil($count/$db_perpage),"profile.php?action=log&type=$type&");
$logdb = array();
$query = $db->query("SELECT * FROM pw_adminlog $sqladd ORDER BY id DESC $limit");
while ($rt = $db->fetch_array($query)) {
	$rt['date']    = get_date($rt['timestamp']);
	$rt['descrip'] = descriplog($rt['descrip']);
	$logdb[] = $rt;
}
require_once uTemplate::PrintEot('profile_log');
pwOutPut();
?>