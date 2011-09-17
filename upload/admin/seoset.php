<?php
!defined('P_W') && exit('Forbidden');
S::gp(array('mode'), 'GP');
$unSeoset = getUnSeoset();
if (empty($mode) || $mode == 'bbs') {
	if ($action == 'update') {
		//* include pwCache::getPath(D_P . 'data/bbscache/forum_cache.php');
		pwCache::getData(D_P . 'data/bbscache/forum_cache.php');
		S::gp(array('seoset', 'forums'), 'p');
		foreach ($forums as $key => $value) {
			$forums[$key]['title'] = $value['title'] = S::escapeChar(strip_tags($value['title']));
			$forums[$key]['descrip'] = $value['descrip'] = S::escapeChar(strip_tags($value['descrip']));
			$forums[$key]['keywords'] = $value['keywords'] = S::escapeChar(strip_tags($value['keywords']));
			if ($forum[$key]['title'] != $value['title'] || $forum[$key]['descrip'] != $value['descrip'] || $forum[$key]['keywords'] != $value['keywords']) {
				//$db->update("UPDATE pw_forums SET title=" . S::sqlEscape($value['title']) . ",metadescrip=" . S::sqlEscape($value['descrip']) . ",keywords=" . S::sqlEscape($value['keywords']) . " WHERE fid = " . S::sqlEscape($key));
				pwQuery::update('pw_forums', 'fid=:fid', array($key), array('title' => $value['title'], 'metadescrip' => $value['descrip'], 'keywords' => $value['keywords']));
			}
		}
		updatecache_f();
		foreach ($seoset as $key => $value) {
			foreach ($value as $k => $var) {
				$seoset[$key][$k] = S::escapeChar(strip_tags(stripslashes($var)));
			}
		}
		setConfig('db_seoset', $seoset, null, false);
		updatecache_c();
		$basename = $basename . '&mode=' . $mode;
		adminmsg('operate_success');
	} else {
		#get forums
		$sql = "SELECT fid,fup,name,type,title,metadescrip,keywords FROM pw_forums ORDER BY vieworder";
		$query = $db->query($sql);
		while ($rt = $db->fetch_array($query)) {
			$rt['name'] = Quot_cv(strip_tags($rt['name']));
			if ($rt['type'] == 'category') {
				$categorys[] = $rt;
			} elseif ($rt['type'] == 'forum') {
				$forums[] = $rt;
			} elseif ($rt['type'] == 'sub') {
				$subForums1[] = $rt;
			} else {
				$subForums2[] = $rt;
			}
		}
		if (empty($mode) || $mode == 'bbs') {
			$forumList = array();
			foreach ($categorys as $k1 => $category) {
				foreach ($forums as $k2 => $forum) {
					if ($forum['fup'] == $category['fid']) {
						$forum['limage1'] = '&nbsp;';
						$forum['limage'] = '&nbsp;';
						$forumList[$category['fid']][] = $forum;
						foreach ($subForums1 as $k3 => $subf1) {
							if ($subf1['fup'] == $forum['fid']) {
								$subf1['limage1'] = '&nbsp;&nbsp;';
								$subf1['limage'] = '&nbsp;&nbsp;;';
								$forumList[$category['fid']][] = $subf1;
								foreach ($subForums2 as $k4 => $subf2) {
									if ($subf2['fup'] == $subf1['fid']) {
										$subf2['limage1'] = '&nbsp;&nbsp;&nbsp;';
										$subf2['limage'] = '&nbsp;&nbsp;&nbsp;';
										$forumList[$category['fid']][] = $subf2;
									}
								}
							}
						}
					}
				}
			}
		}
		unset($forums, $subForums1, $subForums2);
		include PrintEot('seoset');
	}
} else {
	if (in_array($mode, $unSeoset)) exit();
	if (file_exists(D_P . 'data/bbscache/' . S::escapePath($mode) . '_config.php')) pwCache::getData(D_P . 'data/bbscache/' . S::escapePath($mode) . '_config.php');
	if (file_exists(R_P . 'mode/' . S::escapePath($mode) . '/admin/seoset.php')) require_once S::escapePath(R_P . 'mode/' . S::escapePath($mode) . '/admin/seoset.php');
	if (file_exists(R_P . 'mode/' . S::escapePath($mode) . '/config/seoset.php')) require_once S::escapePath(R_P . 'mode/' . S::escapePath($mode) . '/config/seoset.php');
} 
if ($mode == 'sitemap') {
	if(!$action){
		//* @include_once pwCache::getPath(D_P.'data/bbscache/sm_config.php');
		pwCache::getData(D_P.'data/bbscache/sm_config.php');
		include PrintEot('sitemap');exit;
	} elseif($action == 'create'){
		p_unlink(D_P.'sitemap.xml');
		adminmsg('operate_success',"$basename&mode=sitemap");
	} elseif($_POST['action'] == 'baidu'){
		S::gp(array('config'));
		foreach($config as $key=>$value){
			$hk_name = 'sm_'.$key;
			$db->pw_update(
				"SELECT hk_name FROM pw_hack WHERE hk_name=".S::sqlEscape($hk_name),
				"UPDATE pw_hack SET hk_value=".S::sqlEscape($value)."WHERE hk_name=".S::sqlEscape($hk_name),
				"INSERT INTO pw_hack SET hk_name=".S::sqlEscape($hk_name).",hk_value=".S::sqlEscape($value)
			);
	}
		updatecache_sm();
		adminmsg('operate_success',"$basename&mode=sitemap");
}}
function getUnSeoset() {
	global $db_modes;
	$unSeoset = array();
	foreach ($db_modes as $key => $value) {
		if ($key != 'bbs' && !file_exists(R_P . 'mode/' . S::escapePath($key) . '/admin/seoset.php') && !file_exists(R_P . 'mode/' . S::escapePath($key) . '/config/seoset.php')) $unSeoset[] = $key;
	}
	return $unSeoset;
}
function Strip_Space($v) {
	return trim(preg_replace('([\s| ]+)', ' ', $v));
}
function updatecache_sm() {
	global $db;
	$hk_name = $hk_value = '';
	$query    = $db->query("SELECT * FROM pw_hack WHERE hk_name LIKE 'sm_%'");
	$configdb = "<?php\r\n";
	while (@extract($db->fetch_array($query))) {
		$hk_name = key_cv($hk_name);
		$configdb.="\$$hk_name=".pw_var_export($hk_value).";\r\n";
	}
	$configdb.="?>";
	pwCache::setData(D_P.'data/bbscache/sm_config.php',$configdb);
}
exit();
?>