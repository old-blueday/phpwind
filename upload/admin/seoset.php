<?php
!defined('P_W') && exit('Forbidden');
InitGP(array('mode'),'GP');
$unSeoset = array('o');
if (empty($mode) || $mode == 'bbs') {
	if ($action == 'update') {
		include (D_P . 'data/bbscache/forum_cache.php');
		InitGP(array('seoset','forums'),'p');
		foreach ( $forums as $key => $value ) {
			$forums[$key]['title'] = $value['title'] = Char_cv(strip_tags($value['title']));
			$forums[$key]['descrip'] = $value['descrip'] = Char_cv(strip_tags($value['descrip']));
			$forums[$key]['keywords'] = $value['keywords'] = Char_cv(strip_tags($value['keywords']));
			if ($forum[$key]['title'] != $value['title'] || $forum[$key]['descrip'] != $value['descrip'] || $forum[$key]['keywords'] != $value['keywords']) {
				$db->update("UPDATE pw_forums SET title=" . pwEscape($value['title']) . ",metadescrip=" . pwEscape($value['descrip']) . ",keywords=" . pwEscape($value['keywords']) . " WHERE fid = " . pwEscape($key));
			}
		}
		updatecache_f();
		foreach ( $seoset as $key => $value ) {
			foreach ( $value as $k => $var ) {
				$seoset[$key][$k] = Char_cv(strip_tags(stripslashes($var)));
			}
		}
		setConfig('db_seoset',$seoset,null,false);
		updatecache_c();
		$basename = $basename . '&mode=' . $mode;
		adminmsg('operate_success');
	} else {
		#get forums
		$sql = "SELECT fid,fup,name,type,title,metadescrip,keywords FROM pw_forums ORDER BY vieworder";
		$query = $db->query($sql);
		while ( $rt = $db->fetch_array($query) ) {
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
			foreach ( $categorys as $k1 => $category ) {
				foreach ( $forums as $k2 => $forum ) {
					if ($forum['fup'] == $category['fid']) {
						$forum['limage1'] = '&nbsp;';
						$forum['limage'] = '&nbsp;';
						$forumList[$category['fid']][] = $forum;
						foreach ( $subForums1 as $k3 => $subf1 ) {
							if ($subf1['fup'] == $forum['fid']) {
								$subf1['limage1'] = '&nbsp;&nbsp;';
								$subf1['limage'] = '&nbsp;&nbsp;;';
								$forumList[$category['fid']][] = $subf1;
								foreach ( $subForums2 as $k4 => $subf2 ) {
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
		unset($forums,$subForums1,$subForums2);
		include PrintEot('seoset');
	}
} else {
	$fileName = R_P . 'mode/' . Pcv($mode) . '/admin/seoset.php';
	if (file_exists($fileName)) {
		if(file_exists(D_P . 'data/bbscache/' . Pcv($mode) . '_config.php'))
			require_once D_P . 'data/bbscache/' . Pcv($mode) . '_config.php';
		require_once Pcv($fileName);
	}
}
function Strip_Space($v) {
	return trim(preg_replace('([\s| ]+)',' ',$v));
}
exit();
?>