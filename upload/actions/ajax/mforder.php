<?php
!defined('P_W') && exit('Forbidden');

PostCheck();
if ($groupid != 3 && $groupid != 4) {
	Showmsg('undefined_action');
}
$rightset = $db->get_value("SELECT value FROM pw_adminset WHERE gid=" . S::sqlEscape($groupid));
require_once (R_P . 'require/pw_func.php');
//$rightset = P_unserialize($rightset);
if (!$rightset || !(is_array($rightset = unserialize($rightset)))) {
	$rightset = array();
}

if (!$rightset['setforum']) {
	Showmsg('undefined_action');
}
$vieworder = 0;
foreach ($_POST['cate'] as $cid => $fids) {
	$fid_a = explode(',', $fids);
	foreach ($fid_a as $key => $fid) {
		//$db->update("UPDATE pw_forums SET vieworder=" . S::sqlEscape($key) . " WHERE fid=" . S::sqlEscape($fid));
		pwQuery::update('pw_forums', 'fid=:fid', array($fid), array('vieworder'=>intval($key)));
	}
	//$db->update("UPDATE pw_forums SET vieworder=" . S::sqlEscape($vieworder) . " WHERE fid=" . S::sqlEscape($cid));
	pwQuery::update('pw_forums', 'fid=:fid', array($cid), array('vieworder'=>intval($vieworder)));
	++$vieworder;
}
require_once (R_P . 'admin/cache.php');
updatecache_f();

Showmsg('operate_success');
