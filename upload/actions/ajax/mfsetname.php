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
S::gp(array(
	'fids',
	'desc'
), 'P', 0);
$ifcache = false;

if (is_array($desc)) {
	foreach ($desc as $fid => $descrip) {
		$descrip = str_replace(array(
			'&#60;',
			'&#61;',
			'<iframe'
		), array(
			'<',
			'=',
			'&lt;iframe'
		), $descrip);
		strlen($descrip) > 250 && Showmsg('descrip_long');
		//$db->update("UPDATE pw_forums SET descrip=" . S::sqlEscape($descrip) . "WHERE fid=" . S::sqlEscape($fid));
		pwQuery::update('pw_forums', 'fid=:fid', array($fid), array('descrip'=>$descrip));
	}
}
if (is_array($fids)) {
	foreach ($fids as $fid => $name) {
		$name = str_replace(array(
			'&#60;',
			'&#61;',
			'<iframe'
		), array(
			'<',
			'=',
			'&lt;iframe'
		), $name);
		//$db->update("UPDATE pw_forums SET name=" . S::sqlEscape($name) . "WHERE fid=" . S::sqlEscape($fid));
		pwQuery::update('pw_forums', 'fid=:fid', array($fid), array('name'=>$name));
	}
	$ifcache = true;
}
if ($ifcache) {
	require_once (R_P . 'admin/cache.php');
	updatecache_f();
}
Showmsg('operate_success');
