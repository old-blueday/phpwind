<?php
!defined('P_W') && exit('Forbidden');
pwCache::getData (D_P . 'data/bbscache/level.php');

s::gp(array('action','job','step'));
if (empty($action)) {
	if ($step != 2) {
		list($db_opensch, $db_schstart, $db_schend) = explode("\t", $db_opensch);
		$db_schend = (int) $db_schend;
		$db_schstart = (int) $db_schstart;
		ifcheck($db_opensch, 'opensch');
		ifcheck($db_openbuildattachs, 'openbuildattachs');
		$db_maxresult = (int) $db_maxresult;
		$db_schwait = (int) $db_schwait;
		$db_hotwords = ($db_hotwords) ? $db_hotwords : '';
		$db_filterids = ($db_filterids) ? $db_filterids : '';
		$operate_log = array();
		if (S::isArray($db_operate_log)){
			foreach ($db_operate_log as $v){
				$operate_log[$v] = 'CHECKED';
			}
		}
		$search_type_expand = array();
		if (S::isArray($db_search_type)){
			foreach ($db_search_type as $key=>$v){
				$search_type_expand[$key] = 'CHECKED';
			}
		}
	
		
		$db_hotwordsconfig = $db_hotwordsconfig ? unserialize($db_hotwordsconfig) : array();
		ifcheck($db_hotwordsconfig['openautoinvoke'], 'openautoinvoke');
		
		$_wheresql = $db_hotwordsconfig['openautoinvoke'] == 1 ? '' : " AND fromtype = 'custom'"; 
		
		$filterService = L::loadClass('FilterUtil', 'filter');
		
		$query = $db->query(" SELECT * FROM pw_searchhotwords WHERE 1 $_wheresql ORDER BY vieworder ASC, id DESC ".S::sqlLimit($db_hotwordsconfig['shownum']));
		while ($rt = $db->fetch_array($query)) {
			if (($GLOBALS['banword'] = $filterService->comprise($rt['keyword'])) !== false) continue;
			$searchHotwords[] = $rt;
		}

//		if (!$db_dopen) {/*日志应用关闭*/
//			$search_type_disabled['diary'] = "disabled";
//			if ($search_type_expand['diary']) $search_type_expand['diary'] = "";
//		}
//		if (!$db_groups_open){/*群组应用关闭*/
//			 $search_type_disabled['group'] = "disabled"; 
//			 if ($search_type_expand['group']) $search_type_expand['group'] = "";
//		}
		
	} else {
		S::gp(array('schctl','config','hotwordsconfig','view','new_view'));
		$schctl['schstart'] > 23 && $schctl['schstart'] = 0;
		$schctl['schend'] > 23 && $schctl['schend'] = 0;
		$config['opensch'] = $schctl['opensch'] . "\t" . $schctl['schstart'] . "\t" . $schctl['schend'];
		$config['maxresult'] = intval($config['maxresult']);
		$config['schwait'] = intval($config['schwait']);
//		$config['hotwords'] = trim($config['hotwords']);
		$config['filterids'] = trim($config['filterids']);
		$config['operate_log'] = (array)$config['operate_log'];
		$config['search_type_expand'] = (array)$config['search_type_expand'];
		$config['openbuildattachs'] = $config['openbuildattachs'];
		if ($config['operate_log'] && array_diff($config['operate_log'], array('log_forums','log_threads','log_posts','log_diarys','log_members', 'log_colonys'))){
			showMsg("抱歉,操作行为记录类型不存在");
		}
		if ($config['search_type_expand'] && array_diff($config['search_type_expand'], array('cms','diary','group'))){
			showMsg("抱歉,搜索类型扩展不存在");
		}		
		if ($config['filterids']) {
			$filterids = explode(",", $config['filterids']);
			foreach ($filterids as $id) {
				$id = intval($id);
				if ($id < 1) {
					adminmsg('搜索过滤版块ID不能为字符');
				}
			}
			$config['filterids'] = implode(',',$filterids);
		}

		$temp = $tempHotwords = array();
	
		$query = $db->query(" SELECT * FROM pw_searchhotwords ORDER BY vieworder ASC");
		while ($rt = $db->fetch_array($query)) {
			$temp['keyword'] = $rt['keyword'];
			$temp['vieworder'] = $rt['vieworder'];
			$tempHotwords[$rt['id']]= $temp;
		}
		
		$_vieworder = array();
		foreach ((array)$view as $tempId=>$value) {
			if (!$value['keyword']) Showmsg('关键字不能为空');
			$_vieworder[] = $value['vieworder'];
			$value['vieworder'] = abs(intval($value['vieworder']));
			$delHotwordsNoIds[] = $tempId;
			if ($tempHotwords[$tempId]['keyword'] != $value['keyword']) {
				$updateHotwordsDb[$tempId] = array('keyword'=>$value['keyword'],'vieworder'=>$value['vieworder'], 'fromtype'=>'custom');
			} elseif ($tempHotwords[$tempId]['vieworder'] != $value['vieworder']) {
				$updateHotwordsDb[$tempId] = array('keyword'=>$value['keyword'],'vieworder'=>$value['vieworder']);
			} 
		}
		
		
/*		if ($_vieworder) {
			if (count(array_unique($_vieworder)) < count($_vieworder)) {
				Showmsg('顺序不能重复');
			}
		}*/
		$filterService = L::loadClass('FilterUtil', 'filter');
		if ($updateHotwordsDb) {
			foreach ($updateHotwordsDb as $key=>$value) {
				if (($GLOBALS['banword'] = $filterService->comprise($value['keyword'])) !== false) {
					Showmsg('content_wordsfb');
				}
				$updateArr = array( 'keyword' => $value['keyword'], 'vieworder' => $value['vieworder']);
				$value['fromtype'] && $updateArr = array_merge($updateArr, array('fromtype'=>$value['fromtype']));
				//$db->update(" UPDATE pw_searchhotwords SET ".S::sqlSingle($updateArr)." WHERE id=".S::sqlEscape($key));
				pwQuery::update('pw_searchhotwords', "id=:id", array($key), $updateArr);
			}
		}
		
		if (!$view) {
			foreach ($tempHotwords as $key=>$value) {
				if (!$key) continue;
				$delHotwordsIds[] = $key;
			}
			$delHotwordsIds && pwQuery::delete('pw_searchhotwords', 'id IN(:id)', array($delHotwordsIds));
		}
		
		if($delHotwordsNoIds) {
			//$db->update(" DELETE FROM pw_searchhotwords WHERE id NOT IN(".S::sqlImplode($delHotwordsNoIds).")");
			pwQuery::delete('pw_searchhotwords', 'id NOT IN(:id)', array($delHotwordsNoIds));
		}
		
		foreach ((array)$new_view['keyword'] as $key=>$value) {
			if (!$value) continue;
			if (($GLOBALS['banword'] = $filterService->comprise($value)) !== false) {
					Showmsg('content_wordsfb');
			}
			$tempKeywords[] = array(
					'keyword' => $value,
					'vieworder'	=> intval($new_view['vieworder'][$key]),
					'fromtype'	=> 'custom',
					'posttime'	=> $timestamp
			);
		}
		if ($tempKeywords) {
			$db->update ("INSERT INTO pw_searchhotwords(keyword,vieworder,fromtype,posttime) VALUES " . S::sqlMulti($tempKeywords));
		}

		$hotwordsconfig['shownum'] = abs(intval($hotwordsconfig['shownum']));
		$hotwordsconfig['invokeperiod'] = abs(intval($hotwordsconfig['invokeperiod']));
		$autoInvoke = array('isOpne'=> $hotwordsconfig['openautoinvoke'], 'period'=>$hotwordsconfig['invokeperiod']);		
		L::loadClass ( 'hotwordssearcher', 'search/userdefine' );
		$hotwordsServer = new PW_HotwordsSearcher ();
		$hotwordsServer->update($autoInvoke, $hotwordsconfig['shownum']);
		
		if (!$hotwordsconfig['shownum']) setConfig ('db_hotwords', '');
		if (!$hotwordsconfig['invokeperiod']) {
			pwQuery::delete('pw_searchhotwords', 'fromtype=:id', array('auto'));
		}
		$config['hotwordsconfig'] = $hotwordsconfig ? serialize($hotwordsconfig) : '';
		setConfig ('db_opensch', $config['opensch']);
		setConfig ('db_openbuildattachs', $config['openbuildattachs']);
		setConfig ('db_maxresult', $config['maxresult']);
		setConfig ('db_schwait', $config['schwait']);
		setConfig ('db_hotwordsconfig', $config['hotwordsconfig']);
		setConfig ('db_filterids', $config['filterids']);
		setConfig ('db_operate_log', $config['operate_log']);
		setConfigSearchTypeExpand($config['search_type_expand']);
		updatecache_c();
		adminmsg("operate_success");
	}	
} elseif ($action == 'cp') {
	if (empty($job)) {
		s::gp(array('keyword','page'));
		$sql = '';
		$ids = array();
		$keyword && $sql .= " AND keyword LIKE ".s::sqlEscape("%$keyword%");
		$count = $db->get_value("SELECT COUNT(*) FROM pw_searchadvert WHERE 1 $sql");
		$page<1 && $page = 1;
		$limit = pwLimit(($page-1)*$db_perpage,$db_perpage);
		$pages = numofpage($count,$page,ceil($count/$db_perpage), "$basename&action=$action&keyword=".rawurlencode($keyword).'&');
		$query = $db->query("SELECT * FROM pw_searchadvert WHERE 1 $sql ORDER BY id DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['keyword'] = str_replace($keyword,'<em class="s1">'.$keyword.'</em>',$rt['keyword']);
			$rt['endtime'] = get_date($rt['endtime'],'Y-m-d');
			$adverts[$rt['id']] = $rt;
		}

	} elseif ($job == 'add') {
		$advert = array(
			'starttime'	=> get_date($timestamp,'Y-m-d'),
			'endtime'	=> get_date($timestamp + 31536000,'Y-m-d'),
		);
		$ifshow_Y = 'checked';
		$advert['orderby'] = 0;
		$showddate = '';
	} elseif ($job == 'edit') {
		s::gp(array('id'));
		$advert = $db->get_one("SELECT * FROM pw_searchadvert WHERE id=".s::sqlEscape($id));
		!$advert && adminmsg('advert_id_error');
		$advert['skey'] = $advert['keyword'];
		ifcheck($advert['ifshow'],'ifshow');
		$advert['starttime'] = get_date($advert['starttime'],'Y-m-d');
		$advert['endtime'] = get_date($advert['endtime'],'Y-m-d');
		$config = unserialize($advert['config']);
		$config['ddate'] = $config['ddate'] ? str_replace(',',':true,',$config['ddate']) . ':true' : '';
		$config['dweek'] = $config['dweek'] ? str_replace(',',':true,',$config['dweek']) . ':true' : '';
		$config['dtime'] = $config['dtime'] ? str_replace(',',':true,',$config['dtime']) . ':true' : '';
		if ($config['ddate'] || $config['dweek'] || $config['dtime']) {
			$showddate = "{days:{".$config['ddate']."},weeks:{".$config['dweek']."},hours:{".$config['dtime']."}}";
		} else {
			$showddate = '';
		}
	} elseif ($job == 'save') {
		s::gp(array('id','advert','ddate','dweek','dtime'));
		$id = intval($id);
		$basename .= $id ? "&job=edit&id=$id" : "";
		!$advert['skey'] && adminmsg('广告关键字不为空');
		!$advert['code'] && adminmsg('广告代码不为空');
		$advert['code'] = stripslashes(str_replace(array('&#61;','&amp;'),array('=','&'),$advert['code']));
		$advert['keyword'] = $advert['skey'];
		$advert['code'] = addslashes($advert['code']);
		$advert['starttime'] = PwStrtoTime($advert['starttime']);
		$advert['endtime'] = PwStrtoTime($advert['endtime']);
		$advert['orderby'] = (int)$advert['orderby'];
		$config = array();
		$config['ddate'] = $config['dweek'] = $config['dtime'] = '';
		if (is_array($ddate)) {
			$config['ddate'] = implode(',',$ddate);
		}
		if (is_array($dweek)) {
			$config['dweek'] = implode(',',$dweek);
		}
		if (is_array($dtime) && count($dtime)<24) {
			$config['dtime'] = implode(',',$dtime);
		}
		$config = addslashes(serialize($config));
		if ($id) {
			$db->update("UPDATE pw_searchadvert SET " . s::sqlSingle(array(
			'keyword'		=> $advert['keyword'],
			'starttime'		=> $advert['starttime'],
			'endtime'		=> $advert['endtime'],
			'code'			=> $advert['code'],
			'ifshow'		=> $advert['ifshow'],
			'orderby'		=> $advert['orderby'],
			'config'		=> $config,
			)) . " WHERE id=".pwEscape($id));
		} else {
			$db->update("INSERT INTO pw_searchadvert SET " . s::sqlSingle(array(
			'keyword'		=> $advert['keyword'],
			'starttime'		=> $advert['starttime'],
			'endtime'		=> $advert['endtime'],
			'code'			=> $advert['code'],
			'ifshow'		=> $advert['ifshow'],
			'orderby'		=> $advert['orderby'],
			'config'		=> $config,
			)));
			$id = $db->insert_id();
		}
		updatecache_search();
		adminmsg('operate_success',"$basename&action=$action");
	} elseif ($job == 'del') {
		s::gp(array('selid','id'));
		if($id){
			$selid = array($id);
		}
		if (!$selid = checkselid($selid)) {//过滤
			adminmsg('operate_error',"$basename&action=$action");
		}
		$db->update("DELETE FROM pw_searchadvert WHERE id IN ($selid)");//已经过滤
		updatecache_search();
		adminmsg('operate_success',"$basename&action=$action");
	} elseif ($job == 'commit') {
		s::gp(array('orderby'));
		foreach ($orderby as $key => $value) {
			$key && $db->update("UPDATE pw_searchadvert SET orderby=".S::sqlEscape($value)." WHERE id=".S::sqlEscape($key));
		}
		updatecache_search();
		adminmsg('operate_success',"$basename&action=$action");	
	}
} elseif ($action == 'forum') {
	pwCache::getData(D_P.'data/bbscache/forumcache.php');
	require_once(R_P.'require/updateforum.php');
	$catedb = $forumdb = $subdb1 = $subdb2 = $searchforum = array();
	$space  = '<i class="lower lower_a"></i>';
	$query = $db->query("SELECT fid,vieworder FROM pw_searchforum");
	while ($rt = $db->fetch_array($query)) {
		$searchforum[$rt['fid']] = $rt;
	}
	$db->free_result($query);
	
	$query = $db->query("SELECT fid,fup,type,name,f_type FROM pw_forums WHERE cms!='1' ORDER BY vieworder");
	while ($forums = $db->fetch_array($query)) {
		$forums['name'] = Quot_cv(strip_tags($forums['name']));
		$forums['vieworder'] = (int)$searchforum[$forums['fid']]['vieworder'];
		$forums['isrecommend'] = $searchforum[$forums['fid']] ? 'checked' : '';
		if ($forums['type'] == 'category') {
			$catedb[$forums['fid']] = $forums;
		} elseif ($forums['type'] == 'forum') {
			$forumdb[$forums['fid']] = $forums;
		} elseif ($forums['type'] == 'sub') {
			$subdb1[$forums['fid']] = $forums;
		} else {
			$subdb2[$forums['fid']] = $forums;
		}
	}
	
	//$fup_forumcache = $forumcache;
	$fup_forumcache = getForumSelectHtml();
	
	foreach ($subdb2 as $value) {
		$fup_forumcache = str_replace("<option value=\"{$value['fid']}\">&nbsp;&nbsp; &nbsp; &nbsp;|-  {$value['name']}</option>\r\n",'',$fup_forumcache);
	}
	$threaddb = array();

	foreach ($catedb as $cate) {
	$threaddb[$cate['fid']] = array();
		foreach ($forumdb as $key2 => $forumss) {
			if ($forumss['fup'] == $cate['fid']) {
				$threaddb[$cate['fid']][] = $forumss;
				unset($forumdb[$key2]);
				foreach ($subdb1 as $key3 => $sub1) {
					if ($sub1['fup'] == $forumss['fid']) {
						$threaddb[$cate['fid']][] = $sub1;
						unset($subdb1[$key3]);
						foreach ($subdb2 as $key4 => $sub2) {
							if ($sub2['fup'] == $sub1['fid']) {
								$threaddb[$cate['fid']][] = $sub2;
								unset($subdb2[$key4]);
							}
						}
					}
				}
			}
		}
	}
	$forum_L = array();
	if ($forumdb) {
		foreach ($forumdb as $value) {
			$forum_L[] = $value;
		}
	}
	if ($subdb1) {
		foreach ($subdb1 as $value) {
			$forum_L[] = $value;
		}
	}
	if ($subdb2) {
		foreach ($subdb2 as $value) {
			$forum_L[] = $value;
		}
	}
	$ajaxurl = EncodeUrl($basename);
} elseif ($action == 'editforum') {
	
	InitGP(array('fidcommend'), 'P', 0);
	InitGP(array('order'), 'P', 2);
	$pwSQL = $forumDB = $fids = array();
	
	$query = $db->query("SELECT fid,vieworder FROM pw_searchforum");
	while ($rt = $db->fetch_array($query)) {
		$forumDB[$rt['fid']]['fid'] = $rt['fid'];
		$forumDB[$rt['fid']]['vieworder'] = (int)$rt['vieworder'];
		$fids[] = $rt['fid'];
	}
	
	foreach ($fidcommend as $key=>$value) {//用于add|update
		$vieworder  = (int)$order[$key];
		if (!S::inArray($key, $fids)) {
			$addSQL[$key]['fid'] = $key;
			$addSQL[$key]['vieworder'] = $vieworder;
		} else {
			if ($vieworder !== $forumDB[$key]['vieworder']) {
				$updateArr[$key]['vieworder'] = $vieworder;
			}
		}
	}
	
	foreach ($order as $key=>$value) {//用于delect
		if (S::inArray($key, $fids)) {
			!$fidcommend[$key] && $delSQL[$key] = $key;
		}
	}
	
	$addSQL && $db->update("REPLACE INTO pw_searchforum (fid,vieworder) VALUES " . pwSqlMulti($addSQL));
	if ($updateArr) {
		foreach ($updateArr as $key=>$value) {
			$value && $db->update("UPDATE pw_searchforum SET " . pwSqlSingle($value)." WHERE fid=".pwEscape($key,false));
		}
	}
	$delSQL && $db->update("DELETE FROM pw_searchforum WHERE fid IN(".pwImplode($delSQL).")");
	
	//if ($addSQL || $updateArr || $delSQL) {
		updatecache_search();
	//}
	adminmsg('operate_success',"$basename&action=forum");
	
} elseif ($action == 'statistic') {
	s::gp(array('keyword','createtime_s', 'createtime_e'));
	$createtime_s = $createtime_s ? $createtime_s : get_date($timestamp - 7*24*3600,'Y-m-d');
	$createtime_e = $createtime_e ? $createtime_e : get_date($timestamp,'Y-m-d');
	$addsql = '';
	
	if ($keyword) {
		$keyword = trim($keyword);
		$keywordarray = explode(",", $keyword);
		foreach ($keywordarray as $value) {
			$value = str_replace('*', '%', $value);
			$keywhere .= " OR keyword LIKE " . S::sqlEscape("%$value%");
		}
		$keywhere = substr_replace($keywhere, "", 0, 3);
		$addsql .= " AND ($keywhere) ";
	}
	
	if ($createtime_s) {
		$addsql .= " AND created_time >= ".s::sqlEscape(PwStrtoTime($createtime_s));
	}
	
	if ($createtime_e) {
		$addsql .= " AND created_time <= ".s::sqlEscape(PwStrtoTime($createtime_e));
	}
	
	$statisticDb = array();
	$sql = "SELECT keyword, sum( num ) AS times FROM `pw_searchstatistic` WHERE 1 $addsql GROUP BY keyword ORDER BY times DESC LIMIT 0 , 500";
	$qurey = $db->query($sql);
	while ($rt = $db->fetch_array($qurey)) {
		$rt['keyword'] = str_replace ( array ("&#160;", "&#61;", "&nbsp;", "&#60;", "<", ">", "&gt;", "(", ")", "&#41;" ), array (" " ), $rt['keyword'] );
		$statisticDb[] = $rt;
	}
}

include PrintEot('searcher');
exit();

/**
* 更新缓存
*/
function updatecache_search() {
	global $db;
	$query = $db->query("SELECT * FROM pw_searchadvert WHERE ifshow = 1 ORDER BY orderby ASC");
	while ($rt = $db->fetch_array($query)) {
		$t = array();
		$t['keyword'] = $rt['keyword'];
		$t['starttime'] = $rt['starttime'];
		$t['endtime'] = $rt['endtime'];
		$t['code'] = str_replace(array("\\\\","\'",'&lt;','&gt;','&quot;'),array("\\","'",'<','>','"'),$rt['code']);
		$rt['config'] = unserialize($rt['config']);
		$rt['config']['ddate'] && $t['ddate'] = $rt['config']['ddate'];
		$rt['config']['dweek'] && $t['dweek'] = $rt['config']['dweek'];
		$rt['config']['dtime'] && $t['dtime'] = $rt['config']['dtime'];
		$_cachedb[] = $t;
	}
	$_cachedb = $_cachedb ? $_cachedb : array();
	$query = $db->query("SELECT fid,vieworder FROM pw_searchforum ORDER BY vieworder,fid DESC");
	while ($rt = $db->fetch_array($query)) {
		$fids[] = $rt['fid'];
	}
	$db->free_result($query);
	
	$forumsDB = $_cacheforumsdb = array();
	if ($fids) {
		$query = $db->query("SELECT fid,name FROM pw_forums WHERE fid IN(".pwImplode($fids).")");
		while ($rt = $db->fetch_array($query)) {
			$forumsDB[$rt['fid']] = $rt;
		}
		
		$db->free_result($query);
		foreach ($fids as $fid) {
			if (!$forumsDB[$fid]['name']) continue;
			$_cacheforumsdb[$fid] = $forumsDB[$fid]['name'];
		
		}
	}
	
	pwCache::setData (D_P . 'data/bbscache/search_config.php', array('s_searchforumdb' => $_cacheforumsdb,'s_advertdb' => $_cachedb), true);
}

function getForumSelectHtml(){
        global $db;
    	$query	= $db->query("SELECT f.*,fe.creditset,fe.forumset,fe.commend FROM pw_forums f LEFT JOIN pw_forumsextra fe ON f.fid=fe.fid ORDER BY f.vieworder,f.fid");
	$fkeys = array('fid','fup','ifsub','childid','type','name','style','f_type','cms','ifhide');
	$catedb = $forumdb = $subdb1 = $subdb2 = $forum_cache = $fname= array();
	while ($forums = $db->fetch_array($query)) {
		$fname[$forums['fid']] = str_replace(array("\\","'",'<','>'),array("\\\\","\'",'&lt;','&gt;'), strip_tags($forums['name']));
		$forum = array();
		foreach ($fkeys as $k) {
			$forum[$k] = $forums[$k];
		}
		if ($forum['type'] == 'category') {
			$catedb[] = $forum;
		} elseif ($forum['type'] == 'forum') {
			$forumdb[$forum['fup']] || $forumdb[$forum['fup']] = array();
			$forumdb[$forum['fup']][] = $forum;
		} elseif ($forum['type'] == 'sub') {
			$subdb1[$forum['fup']] || $subdb1[$forum['fup']] = array();
			$subdb1[$forum['fup']][] = $forum;
		} else {
			$subdb2[$forum['fup']] || $subdb2[$forum['fup']] = array();
			$subdb2[$forum['fup']][] = $forum;
		}
	}
	$forumcache = '';
	foreach ($catedb as $cate) {
		if (!$cate) continue;
		$forum_cache[$cate['fid']] = $cate;
		$forumlist_cache[$cate['fid']]['name'] = strip_tags($cate['name']);
		$forumcache .= "<option value=\"$cate[fid]\">&gt;&gt; {$fname[$cate[fid]]}</option>\r\n";
		if (!$forumdb[$cate['fid']]) continue;

		foreach ($forumdb[$cate['fid']] as $forum) {
			$forum_cache[$forum['fid']] = $forum;
			$forumlist_cache[$cate['fid']]['child'][$forum['fid']] = strip_tags($forum['name']);
			$forumcache .= "<option value=\"$forum[fid]\"> &nbsp;|- {$fname[$forum[fid]]}</option>\r\n";
			if (!$subdb1[$forum['fid']]) continue;
			foreach ($subdb1[$forum['fid']] as $sub1) {
				$forum_cache[$sub1['fid']] = $sub1;
				$forumcache .= "<option value=\"$sub1[fid]\"> &nbsp; &nbsp;|-  {$fname[$sub1[fid]]}</option>\r\n";
				if (!$subdb2[$sub1['fid']]) continue;

				foreach ($subdb2[$sub1['fid']] as $sub2) {
					$forum_cache[$sub2['fid']] = $sub2;
					$forumcache .= "<option value=\"$sub2[fid]\">&nbsp;&nbsp; &nbsp; &nbsp;|-  {$fname[$sub2[fid]]}</option>\r\n";
				}
			}
		}
	}
        return $forumcache;
}

function setConfigSearchTypeExpand($searchAllowExpandType) {
	global $db_dopen,$db_groups_open;
	//搜索下拉框顺序
	$searchTypeOrder = array('thread' => '帖子','cms'=>'文章','diary' => '日志', 'user' => '用户', 'forum' => '版块', 'group' => '群组');
	$searchAllowDefaultType = array(0=>'thread', 1=>'user', 2=>'forum');
	$searchAllowType = array_merge($searchAllowDefaultType, (array)$searchAllowExpandType);
	$result = array();
	foreach ($searchTypeOrder as $key=>$val) {
		if (!in_array($key, $searchAllowType)) continue;
		$result[$key] = $val;
	}
	setConfig ('db_search_type', $result);
}
