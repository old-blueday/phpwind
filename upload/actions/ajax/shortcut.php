<?php
!defined('P_W') && exit('Forbidden');

!$fid && Showmsg('undefined_action');
S::gp(array('type'));
$myshortcut = explode(',', $winddb['shortcut']);
foreach ($myshortcut as $key => $value) {
	if (!$value || !is_numeric($value)) {
		unset($myshortcut[$key]);
	}
}
$myshortcut = array_unique($myshortcut);

$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */

if ($type == "delete") {
	
	if (empty($myshortcut) && $db_shortcutforum) {
		$myshortcut = array_keys($db_shortcutforum);
	}
	if(in_array($fid, $myshortcut)){
		$shortcut = array_diff($myshortcut,array($fid));
	}
	$shortcut = ($shortcut) ? $shortcut : array();
	$shortcut = ',' . implode(',', $shortcut) . ',';
	$shortcut .= $shortcut . "\t" . $winddb['appshortcut'];
	
	$userService->update($winduid, array('shortcut'=>$shortcut));
	
	Showmsg("shortcutno");

} elseif (in_array($fid, $myshortcut)) {

	foreach ($myshortcut as $key => $value) {
		if (!$value || $value == $fid) {
			unset($myshortcut[$key]);
		}
	}
	$shortcut = ',' . implode(',', $myshortcut) . ',';
	$shortcut .= $shortcut . "\t" . $winddb['appshortcut'];
	
	$userService->update($winduid, array('shortcut'=>$shortcut));
	Showmsg("shortcutno");

} else {

	count($myshortcut) >= 6 && Showmsg('shortcut_numlimit');
	//* require_once pwCache::getPath(D_P . 'data/bbscache/forum_cache.php');
	pwCache::getData(D_P . 'data/bbscache/forum_cache.php');
	$forumkeys = array_keys($forum);
	!in_array($fid, $forumkeys) && Showmsg('undefined_action');
	$myshortcut[] = $fid;
	
	$shortcut = ',' . implode(',', $myshortcut) . ',';
	$shortcut .= $shortcut . "\t" . $winddb['appshortcut'];
	$userService->update($winduid, array('shortcut'=>$shortcut));
	Showmsg("shortcutok");
}
