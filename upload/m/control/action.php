<?php
!defined('W_P') && exit('Forbidden');

!$winduid && wap_msg ( 'not_login' );
InitGP(array(
	'action','fid'
));
intval($fid) <= 0 && wap_msg('undefined_action',"index.php?a=forum");
$myshortcut = explode(',', $winddb['shortcut']);
foreach ($myshortcut as $key => $value) {
	if (!$value || !is_numeric($value)) {
		unset($myshortcut[$key]);
	}
}
$myshortcut = array_unique($myshortcut);
if($action == 'del'){
	if(in_array($fid, $myshortcut)){
		$shortcut = array_diff($myshortcut,array($fid));
	}
	$shortcut = ($shortcut) ? $shortcut : array();
	$shortcut = ',' . implode(',', $shortcut) . ',';
	$shortcut .= $shortcut . "\t" . $winddb['appshortcut'];
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$userService->update($winduid, array('shortcut'=>$shortcut));
	$url = "index.php?a=forum&fid=$fid";
	wap_msg("wap_shortcutno",$url);
	
}elseif($action == 'add'){
	$url = "index.php?a=forum&fid=$fid";
	if(in_array($fid,$myshortcut)){
		foreach ($myshortcut as $key => $value) {
			if (!$value || $value == $fid) {
				unset($myshortcut[$key]);
			}
		}
		$shortcut = ',' . implode(',', $myshortcut) . ',';
		$shortcut .= $shortcut . "\t" . $winddb['appshortcut'];
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->update($winduid, array('shortcut'=>$shortcut));
		wap_msg("wap_shortcutno",$url);
	}else{
		count($myshortcut) >= 6 && wap_msg('wap_shortcut_numlimit',$url);
		require_once (D_P . 'data/bbscache/forum_cache.php');
		$forumkeys = array_keys($forum);
		!in_array($fid, $forumkeys) && wap_msg('undefined_action',$url);
		$myshortcut[] = $fid;
		$shortcut = ',' . implode(',', $myshortcut) . ',';
		$shortcut .= $shortcut . "\t" . $winddb['appshortcut'];
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->update($winduid, array('shortcut'=>$shortcut));
		wap_msg("wap_shortcutok",$url);
	}
	
}
?>