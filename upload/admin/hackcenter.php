<?php
!function_exists('adminmsg') && exit('Forbidden');
require_once(R_P.'require/sql_deal.php');
$basename="$admin_file?adminjob=hackcenter";

if (!$action) {

	$installdb = $uninstalldb = array();
	foreach ($db_hackdb as $key => $value) {
		$value[0] = htmlspecialchars($value[0]);
		${$value[1].'_'.$value[2]} = 'SELECTED';
		$value[4] = EncodeUrl("$basename&action=delete&id=$value[1]");
		if (file_exists(R_P."/hack/$key/index.php")) {
			$installdb['index'][$key] = $value;
		} else {
			$installdb['noindex'][$key] = $value;
		}
	}
	if ($fp = opendir(R_P.'hack')) {
		$infodb = array();
		while (($hackdir = readdir($fp))) {
			if (strpos($hackdir,'.')===false && empty($db_hackdb[$hackdir])) {
				$hackname = $hackdir;
				$hackopen = 0;
				if (function_exists('file_get_contents')) {
					$filedata = @file_get_contents(R_P."hack/$hackdir/info.xml");
				} else {
					$filedata = pwCache::readover(R_P."hack/$hackdir/info.xml");
				}
				if (preg_match('/\<hackname\>(.+?)\<\/hackname\>\s+\<ifopen\>(.+?)\<\/ifopen\>/is',$filedata,$infodb)) {
					$infodb[1] && $hackname = S::escapeChar(str_replace(array("\n"),'',$infodb[1]));
					$hackopen = (int)$infodb[2];
				}
				$hackurl = EncodeUrl("$basename&action=add&hackdir=$hackdir&hackname=".rawurlencode($hackname)."&hackopen=$hackopen");
				$uninstalldb[] = array($hackname,$hackdir,$hackopen,$hackurl);
			}
		}
		closedir($fp);
	}
	unset($db_hackdb);
	include PrintEot('hackcenter');exit;

} elseif ($action == 'edit') {
	S::gp(array('hackname'),'GP',0);

	!is_array($hackname) && $hackname = array();
	foreach ($hackname as $key => $value) {
		$value = str_replace(array("\t","\n","\r",'  '),array('&nbsp; &nbsp; ','<br />','','&nbsp; '),$value);
		if ($value && $db_hackdb[$key][1] == $key && ($db_hackdb[$key][0] != $value || $db_hackdb[$key][2] != $hackopen[$key])) {
			$db_hackdb[$key] = array(stripslashes($value),$key/*,$hackopen[$key]*/);
		}
	}
	setConfig('db_hackdb', $db_hackdb);
	updatecache_c();
	adminmsg('operate_success');

} elseif ($action == 'delete') {

	S::gp(array('id'));
	empty($db_hackdb[$id]) && adminmsg('hackcenter_del');
	unset($db_hackdb[$id]);
	$sqlarray = file_exists(R_P."hack/$id/sql.txt") ? FileArray($id) : array();
	!empty($sqlarray) && SQLDrop($sqlarray);
	setConfig('db_hackdb', $db_hackdb);

	$navConfigService = L::loadClass('navconfig', 'site');
	$navConfigService->deleteByKey('hack_'.$id);
	updatecache_c();

	adminmsg('operate_success');

} elseif ($action == 'add') {

	S::gp(array('hackdir','hackname','hackopen'),'G');
	!empty($db_hackdb[$hackdir]) && adminmsg('hackcenter_sign_exists');
	$sqlarray = file_exists(R_P."hack/$hackdir/sql.txt") ? FileArray($hackdir) : array();
	!empty($sqlarray) && SQLCreate($sqlarray);
	$db_hackdb[$hackdir] = array($hackname, $hackdir, $hackopen);
	setConfig('db_hackdb', $db_hackdb);

	if ($hackdir == 'toolcenter') {
		$link  = 'profile.php?action=toolcenter';
	}	else {
		$link  = 'hack.php?H_name='.$hackdir;
	}

	$navConfigService = L::loadClass('navconfig', 'site');
	$navUpId = $navConfigService->getByKey('hack', PW_NAV_TYPE_HEAD_RIGHT);
	$navUpId = $navUpId ? $navUpId['nid'] : 0;
	$navConfigService->add(PW_NAV_TYPE_HEAD_RIGHT, array('nkey'=>'hack_'.$hackdir, 'title'=>strip_tags($hackname), 'link'=>$link, 'upid'=>$navUpId, 'isshow'=>$hackopen ? 1 : 0));
	updatecache_c();
	
	adminmsg('operate_success');
}
?>