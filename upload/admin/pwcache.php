<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$amind_file?adminjob=pwcache";

if (empty($action)) {

	if ($_POST['step']) {

		InitGP(array('config'),'P');
		$ifpwcache = 0;
		foreach ($config['ifpwcache'] as $val) {
			$ifpwcache ^= (int)$val;
		}
		setConfig('db_ifpwcache', $ifpwcache);
	
		$cachenum = $config['cachenum'] ? (int)$config['cachenum'] : 20;
		setConfig('db_cachenum', $cachenum);

		updatecache_c();
		adminmsg('operate_success',$basename);

	} else {

		$ifpwcache_1 = $db_ifpwcache&1 ? 'checked' : '';
		$ifpwcache_2 = $db_ifpwcache&2 ? 'checked' : '';
		$ifpwcache_4 = $db_ifpwcache&4 ? 'checked' : '';
		$ifpwcache_8 = $db_ifpwcache&8 ? 'checked' : '';
		$ifpwcache_16 = $db_ifpwcache&16 ? 'checked' : '';
		$ifpwcache_32 = $db_ifpwcache&32 ? 'checked' : '';
		$ifpwcache_64 = $db_ifpwcache&64 ? 'checked' : '';
		$ifpwcache_128 = $db_ifpwcache&128 ? 'checked' : '';
		$ifpwcache_256 = $db_ifpwcache&256 ? 'checked' : '';
		$ifpwcache_512 = $db_ifpwcache&512 ? 'checked' : '';
		$ifpwcache_1024 = $db_ifpwcache&1024 ? 'checked' : '';
		!$db_cachenum && $db_cachenum = 20;
		include PrintEot('pwcache');exit;
	}
} elseif ($action == 'update') {

	$type = GetGP('type','G');

	if (!$type) {

		include PrintEot('pwcache');exit;

	} else {

		!$db_sortnum && $db_sortnum = 20;
		L::loadClass('getinfo', '', false);
		$getinfo =& GetInfo::getInstance();
		if (in_array($type,array('replysort','replysortday','replysortweek'))){
			$step = intval(GetGP('step'));
			require_once(D_P.'data/bbscache/forum_cache.php');
			$arr_forumkeys = array_keys($forum);

			$fourmlimit = array();
			for ($j=0;$j<5;$j++) {
				$replysort_judge = '';
				@include_once Pcv(D_P.'data/bbscache/replysort_judge_'.$j.'.php');
				$replysort_judge && $fourmlimit[$j] = $replysort_judge;
			}
			if (!$step) {
				$step = 0;
				for ($j=0;$j<5;$j++) {
					$fourmlimit[$j][$type] = array();
				}
				$db->query("DELETE FROM pw_elements WHERE type=".pwEscape($type));
			}
			$total = count($arr_forumkeys);
			for ($i=0;$i<5;$i++) {
				if ($step < $total) {
					$fourmid = $arr_forumkeys[$step];
					!$forum[$fourmid] && adminmsg('undefined_action');
					$step++;
					if ($forum[$fourmid]['type']=='category') {
						continue;
					} else {
						$foruminfo = $db->get_one("SELECT allowtype FROM pw_forums WHERE fid=".pwEscape($fourmid));
						$allowtype = $foruminfo['allowtype'];
						$arr_posts = array();
						if ($type == 'replysortday') {
							$hour = 24;
						} elseif ($type == 'replysortweek') {
							$hour = 7 * 24;
						} else {
							$hour = 0;
						}
						for ($j=0;$j<5;$j++) {
							if ($allowtype & pow(2,$j)) {
								$arr_posts[$j] = $getinfo->getPostList('replysort',$fourmid,$db_sortnum,$hour,$j);
								$arr_posts[$j] = arr_unique($arr_posts[$j]);
							}
						}
						foreach ($arr_posts as $key => $value) {
							foreach ($value as $k => $v) {
								$arr_posts[$key][$k]['type'] = $type;
								$arr_posts[$key][$k]['mark'] = $fourmid;
								$type == 'replysort' && $arr_posts[$key][$k]['addition'] = '';
							}
						}

						$updatesql = array();
						foreach ($arr_posts as $key => $value) {
							if (count($value) == $db_sortnum) {
								$tmpdate = end($value);
								$fourmlimit[$key][$type][$fourmid] = $tmpdate['value'];
							} else {
								$fourmlimit[$key][$type][$fourmid] = 0;
							}
							$updatesql = array_merge($updatesql,$value);
						}
						if ($updatesql) {
							$sql = "REPLACE INTO pw_elements(id,value,addition,special,type,mark) VALUES".pwSqlMulti($updatesql,false);
							$db->update($sql);
						}
					}
				} else {
					break;
				}
			}
			foreach ($fourmlimit as $key => $value) {
				if ($value) {
					writeover(D_P.'data/bbscache/'.Pcv('replysort_judge_'.$key).'.php',"<?php\r\n\$replysort_judge=".pw_var_export($value).";\r\n?>");
				}
			}
			if ($step < $total) {
				adminmsg('updatecache_total_step',"$basename&action=update&type=$type&step=$step");
			}
		} elseif (in_array($type,array('hitsort','hitsortday','hitsortweek'))) {
			$step = intval(GetGP('step'));
			require_once(D_P.'data/bbscache/forum_cache.php');
			$arr_forumkeys = array_keys($forum);

			$fourmlimit = array();
			@include_once(D_P.'data/bbscache/hitsort_judge.php');
			$hitsort_judge && $fourmlimit = $hitsort_judge;
			if (!$step) {
				$step = 0;
				$db->query("DELETE FROM pw_elements WHERE type=".pwEscape($type));
				$fourmlimit[$type] = array();
			}
			$total = count($arr_forumkeys);
			for ($i=0;$i<5;$i++) {
				if ($step < $total) {
					$fourmid = $arr_forumkeys[$step];
					!$forum[$fourmid] && adminmsg('undefined_action');
					$step++;
					if ($forum[$fourmid]['type']=='category') {
						continue;
					} else {
						$arr_posts = array();
						if ($type == 'hitsortday') {
							$hour = 24;
						} elseif ($type == 'hitsortweek') {
							$hour = 7 * 24;
						} else {
							$hour = 0;
						}

						$arr_posts = $getinfo->getPostList('hitsort',$fourmid,$db_sortnum,$hour);
						$arr_posts = arr_unique($arr_posts);
						foreach ($arr_posts as $key => $value) {
							$arr_posts[$key]['type'] = $type;
							$arr_posts[$key]['mark'] = $fourmid;
							$type == 'hitsort' && $arr_posts[$key]['addition'] = '';
						}


						if (count($arr_posts) == $db_sortnum) {
							$tmpdate = end($arr_posts);
							$fourmlimit[$type][$fourmid] = $tmpdate['value'];
						} else {
							$fourmlimit[$type][$fourmid] = 0;
						}

						if ($arr_posts) {
							$sql = "REPLACE INTO pw_elements(id,value,addition,special,type,mark) VALUES".pwSqlMulti($arr_posts,false);
							$db->update($sql);
						}
					}
				} else {
					break;
				}
			}

			writeover(D_P.'data/bbscache/hitsort_judge.php',"<?php\r\n\$hitsort_judge=".pw_var_export($fourmlimit).";\r\n?>");

			if ($step < $total) {
				adminmsg('updatecache_total_step',"$basename&action=update&type=$type&step=$step");
			}
		} elseif ($type=='usersort') {
			include_once(D_P.'data/bbscache/usersort_judge.php');
			$step = intval(GetGP('step'));
			$sorttype = array('money','rvrc','credit','currency','todaypost','monthpost','postnum','monoltime','onlinetime','digests','f_num');
			foreach ($_CREDITDB as $key => $val) {
				is_numeric($key) &&	$sorttype[] = $key;
			}
			$arr_sortkeys = array_keys($sorttype);
			if (!$step) {
				$step = 0;
				$_usersort = array();
				$usersort_judge = array();
			}
			$total = count($sorttype);
			if ($step < $total) {
				$mark = $sorttype[$step];
				$db->update("DELETE FROM pw_elements WHERE type='usersort' AND mark=".pwEscape($mark));
				$step++;
				$_usersort = $getinfo->userSort($mark,$db_sortnum,false);
				$_usersort = arr_unique($_usersort);
				if (is_array($_usersort) && count($_usersort)==$db_sortnum) {
					$tmpdate = end($_usersort);
					$usersort_judge[$mark] = $tmpdate['value'];
				} else {
					$usersort_judge[$mark] = 0;
				}
				if ($_usersort) {
					$sql = "REPLACE INTO pw_elements(id,value,addition,type,mark) VALUES".pwSqlMulti($_usersort,false);
					$db->update($sql);
				}

				writeover(D_P.'data/bbscache/usersort_judge.php',"<?php\r\n\$usersort_judge=".pw_var_export($usersort_judge).";\r\n?>");

				adminmsg('updatecache_total_step',"$basename&action=update&type=usersort&step=$step");
			}
		} elseif ($type=='newsubject') {
			$step = intval(GetGP('step'));
			require_once(D_P.'data/bbscache/forum_cache.php');
			$arr_forumkeys = array_keys($forum);
			if (!$step) {
				$step = 0;
				$db->query("DELETE FROM pw_elements WHERE type='newsubject'");
			}
			$total = count($arr_forumkeys);
			for ($i=0;$i<5;$i++) {
				if ($step < $total) {
					$fourmid = $arr_forumkeys[$step];
					!$forum[$fourmid] && adminmsg('undefined_action');
					$step++;
					if ($forum[$fourmid]['type']=='category') {
						continue;
					} else {
						$arr_posts = array();
						$arr_posts = $getinfo->getPostList('newsubject',$fourmid,$db_sortnum);
						$arr_posts = arr_unique($arr_posts);
						foreach ($arr_posts as $key => $value) {
							$arr_posts[$key]['type'] = 'newsubject';
							$arr_posts[$key]['mark'] = $fourmid;
						}
						if ($arr_posts) {
							$sql = "REPLACE INTO pw_elements(id,value,type,mark) VALUES".pwSqlMulti($arr_posts,false);
							$db->update($sql);
						}
					}
				} else {
					break;
				}
			}
			if ($step < $total) {
				adminmsg('updatecache_total_step',"$basename&action=update&type=newsubject&step=$step");
			}
		} elseif ($type == 'newreply') {
			$step = intval(GetGP('step'));
			require_once(D_P.'data/bbscache/forum_cache.php');
			$arr_forumkeys = array_keys($forum);
			if (!$step) {
				$step = 0;
				$db->query("DELETE FROM pw_elements WHERE type='newreply'");
			}
			$total = count($arr_forumkeys);
			for ($i=0;$i<5;$i++) {
				if ($step < $total) {
					$fourmid = $arr_forumkeys[$step];
					!$forum[$fourmid] && adminmsg('undefined_action');
					$step++;
					if ($forum[$fourmid]['type']=='category') {
						continue;
					} else {
						$arr_posts = array();
						$arr_posts = $getinfo->getPostList('newreply',$fourmid,$db_sortnum);
						$arr_posts = arr_unique($arr_posts);
						foreach ($arr_posts as $key => $value) {
							$arr_posts[$key]['type'] = 'newreply';
							$arr_posts[$key]['mark'] = $fourmid;
						}
						if ($arr_posts) {
							$sql = "REPLACE INTO pw_elements(id,value,type,mark) VALUES".pwSqlMulti($arr_posts,false);
							$db->update($sql);
						}
					}
				} else {
					break;
				}
			}
			if ($step < $total) {
				adminmsg('updatecache_total_step',"$basename&action=update&type=newreply&step=$step");
			}
		} elseif ($type == 'newpic') {
			//adminmsg('newpic_not_needupdate');
			$step = intval(GetGP('step'));
			require_once(D_P.'data/bbscache/forum_cache.php');
			$arr_forumkeys = array_keys($forum);
			if (!$step) {
				$step = 0;
				$db->query("DELETE FROM pw_elements WHERE type='newpic'");
			}
			$total = count($arr_forumkeys);
			for ($i=0;$i<5;$i++) {
				if ($step < $total) {
					$fourmid = $arr_forumkeys[$step];
					!$forum[$fourmid] && adminmsg('undefined_action');
					$step++;
					if ($forum[$fourmid]['type']=='category') {
						continue;
					} else {
						$arr_posts = array();
						$arr_posts = $getinfo->newAttach('img',$fourmid,$db_sortnum);
						$arr_posts = arr_unique($arr_posts);
						foreach ($arr_posts as $key => $value) {
							$arr_posts[$key]['type'] = 'newpic';
							$arr_posts[$key]['mark'] = $fourmid;
						}
						if ($arr_posts) {
							$sql = "REPLACE INTO pw_elements(id,value,addition,special,type,mark) VALUES".pwSqlMulti($arr_posts,true);
							$db->update($sql);
						}
					}
				} else {
					break;
				}
			}
			if ($step < $total) {
				adminmsg('updatecache_total_step',"$basename&action=update&type=newpic&step=$step");
			}
		} elseif ($type=='hotfavor') {
			$step = intval(GetGP('step'));
			require_once(D_P.'data/bbscache/forum_cache.php');
			$arr_forumkeys = array_keys($forum);
			if (!$step) {
				$step = 0;
				$db->query("DELETE FROM pw_elements WHERE type='hotfavor'");
			}
			$total = count($arr_forumkeys);
			for ($i=0;$i<5;$i++) {
				if ($step < $total) {
					$fourmid = $arr_forumkeys[$step];
					!$forum[$fourmid] && adminmsg('undefined_action');
					$step++;
					if ($forum[$fourmid]['type']=='category') {
						continue;
					} else {
						$arr_posts = array();
						$arr_posts = $getinfo->hotfavor($fourmid,$db_sortnum);
						$arr_posts = arr_unique($arr_posts);

						foreach ($arr_posts as $key => $value) {
							$arr_posts[$key]['type'] = 'hotfavor';
						}

						if ($arr_posts) {
							$sql = "REPLACE INTO pw_elements(id,mark,value,type) VALUES".pwSqlMulti($arr_posts,false);
							$db->update($sql);
						}
					}
				} else {
					break;
				}
			}
			if ($step < $total) {
				adminmsg('updatecache_total_step',"$basename&action=update&type=hotfavor&step=$step");
			}
		}
		adminmsg('operate_success',"$basename");
	}
} else {
	adminmsg('undefined_action');
}
include PrintEot('pwcache');exit;

function arr_unique($array){
	if (is_array($array)) {
		$temp_array = array();
		foreach ($array as $key => $value) {
			$var_md5 = md5(is_array($value) ? serialize($value) : $value);
			if (in_array($var_md5,$temp_array)) {
				unset($array[$key]);
			} else {
				$temp_array[] = $var_md5;
			}
		}
	}
	return $array;
}
?>