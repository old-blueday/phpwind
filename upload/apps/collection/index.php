<?php
!defined('A_P') && exit('Forbidden');

S::gp(array('uid'), 2);
//* include_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
pwCache::getData(D_P . 'data/bbscache/o_config.php');
S::gp(array('page', 'ajax'));

if ($q == "collection") {
	
	require_once(R_P.'require/showimg.php');	
	S::gp(array('a','type','space'),null,1);
	require_once(R_P . 'u/lib/space.class.php');
	$newSpace = new PwSpace($uid ? $uid : $winduid);
	$space =& $newSpace->getInfo();
	
	$sum = 0;
	$where = '';
	
	if ($ajax == '1') {
		require_once S::escapePath($appEntryBasePath . 'action/ajax.php');
	} else {
		!$winduid && Showmsg('not_login');
		$page = intval($page);
		$page < 1 && $page = 1;
		$db_perpage = 10;
		require_once S::escapePath($appEntryBasePath . '/action/my.php');
	}
} elseif ($q == "sharelink") {
	require_once S::escapePath($appEntryBasePath . '/action/m_sharelink.php');
}


function getfavor($tids) {
	$tids  = explode('|',$tids);
	$tiddb = array();
	$count = 0;
	foreach ($tids as $key => $t) {
		if ($t) {
			$v = explode(',',$t);
			foreach ($v as $k => $v1) {
				$count++;
				$tiddb[$key][$v1] = $v1;
			}
		}
	}
	return array($tiddb,$count);
}
function get_key($tid,$tiddb) {
	foreach ($tiddb as $key => $value) {
		if (in_array($tid,$value)) {
			return $key;
		}
	}
	return null;
}


function makefavor($tiddb) {
	$newtids = $ex = '';
	$k = 0;
	ksort($tiddb);
	foreach ($tiddb as $key => $val) {
		$new_tids = '';
		rsort($val);
		if ($key != $k) {
			$s = $key - $k;
			for ($i = 0; $i < $s; $i++) {
				$newtids .= '|';
			}
		}
		foreach ($val as $k => $v) {
			is_numeric($v) && $new_tids .= $new_tids ? ','.$v : $v;
		}
		$newtids .= $ex.$new_tids;
		$k  = $key + 1;
		$ex = '|';
	}
	return $newtids;
}
?>