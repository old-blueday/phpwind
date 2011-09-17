<?php
!function_exists('writeover') && exit('Forbidden');
require_once(R_P.'require/pw_func.php');

function updatecache($array='') {
	if (empty($array) || !is_array($array)) {
		updatecache_i(1);
		if(R_P==D_P || !file_exists(D_P.'data/bbscache/config.php')||!file_exists(D_P.'data/bbscache/dbreg.php')){
			updatecache_c();
		}
		updatecache_p(1);
		updatecache_w();
		updatecache_sy();
		updatecache_g();
		updatecache_bk();
		updatecache_df();
		updatecache_ol();
		updatecache_mddb(1);
		updatecache_ml();

		updatecache_f(1);
		updatecache_l(1);
		updatecache_gr(1);

		updatecache_inv();
		updatecache_plan();
		updatecache_ftp();
		updatecache_field(1);
		updatecache_form();
		updatecache_help();
		cache_read();
		updatecache_hotforum();
		updatecache_openforum();
		updatecache_topic();
		updatecache_postcate();
		updateCacheActivity();

		updatecache_conf('nf', false, 'newinfo_config.php');
		//updateStampCache();
		//updateBlockCache();
	} else {
		foreach ($array as $value) {
			$value();
		}
	}
}
function updatecache_f($return=0) {
	global $db;
	updatecache_fd(false);
	//* $db->update("UPDATE pw_forums SET ifsub='0' WHERE type<3");
	$db->update(pwQuery::buildClause("UPDATE :pw_table SET ifsub='0' WHERE type<3", array('pw_forums')));
	
	//* $db->update("UPDATE pw_forums SET ifsub='1' WHERE type>2");
	$db->update(pwQuery::buildClause("UPDATE :pw_table SET ifsub='1' WHERE type>2", array('pw_forums')));

	$t_typedb = getTopictypeCache();
	$appdb = array();
	$query = $db->query("SELECT f.*,fe.creditset,fe.forumset,fe.commend,fe.appinfo,c.ifopen as cnifopen FROM pw_forums f LEFT JOIN pw_forumsextra fe ON f.fid=fe.fid LEFT JOIN pw_cnclass c ON f.fid=c.fid ORDER BY f.vieworder,f.fid");
	$fkeys = array('fid','fup','ifsub','childid','type','name','style','f_type','ifcms','ifhide','title','metadescrip','descrip','keywords','forumadmin');
	$catedb = $forumdb = $subdb1 = $subdb2 = $forum_cache = $fname= array();
	while ($forums = $db->fetch_array($query)) {
		$forums['topictype'] = $t_typedb[$forums['fid']];
		writeforumscache($forums);
		$fname[$forums['fid']] = str_replace(array("\\","'",'<','>'),array("\\\\","\'",'&lt;','&gt;'), strip_tags($forums['name']));
		$forum = array();
		foreach ($fkeys as $k) {
			$forum[$k] = $forums[$k];
		}
		if ($forums['appinfo']) {
			$forums['appinfo'] = unserialize($forums['appinfo']);
			$appdb[$forums['fid']] = $forums['appinfo'];
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

	$forumcache = '';$pwForumAllList = $forumlist_cache = array();
	foreach ($catedb as $cate) {
		if (!$cate) continue;
		$forum_cache[$cate['fid']] = $cate;
		if ($forumdb[$cate['fid']]) {
			if($cate['f_type']!='hidden'){
				$forumlist_cache[$cate['fid']]['name'] = strip_tags($cate['name']);
			} else {
				$pwForumAllList[$cate['fid']]['name'] = strip_tags($cate['name']);
			}
		}
		if ($cate['cms']) {
			$cmscache .= "<option value=\"$cate[fid]\">&gt;&gt; {$fname[$cate[fid]]}</option>\r\n";
		} elseif ($cate['f_type']!='hidden') {
			$forumcache .= "<option value=\"$cate[fid]\">&gt;&gt; {$fname[$cate[fid]]}</option>\r\n";
		}
		if (!$forumdb[$cate['fid']]) continue;

		foreach ($forumdb[$cate['fid']] as $forum) {
			$forum_cache[$forum['fid']] = $forum;
			if($forum['f_type']!='hidden'){
				if (!isset($forumlist_cache[$cate['fid']]['name'])) {
					$forumlist_cache[$cate['fid']]['name'] = $pwForumAllList[$cate['fid']]['name'];
				}
				$forumlist_cache[$cate['fid']]['child'][$forum['fid']] = strip_tags($forum['name']);
			} else {

				if (!isset($pwForumAllList[$cate['fid']]['name'])) {
					$pwForumAllList[$cate['fid']]['name'] = $forumlist_cache[$cate['fid']]['name'];
				}
				$pwForumAllList[$cate['fid']]['child'][$forum['fid']] = strip_tags($forum['name']);
			}
			if ($forum['cms']) {
				$cmscache .= "<option value=\"$forum[fid]\"> &nbsp;|- {$fname[$forum[fid]]}</option>\r\n";
			} elseif ($forum['f_type']!='hidden') {
				$forumcache .= "<option value=\"$forum[fid]\"> &nbsp;|- {$fname[$forum[fid]]}</option>\r\n";
			}
			if (!$subdb1[$forum['fid']]) continue;

			foreach ($subdb1[$forum['fid']] as $sub1) {
				$forum_cache[$sub1['fid']] = $sub1;

				if ($sub1['cms']) {
					$cmscache .= "<option value=\"$sub1[fid]\"> &nbsp; &nbsp;|-  {$fname[$sub1[fid]]}</option>\r\n";
				} elseif ($sub1['f_type']!='hidden') {
					$forumcache .= "<option value=\"$sub1[fid]\"> &nbsp; &nbsp;|-  {$fname[$sub1[fid]]}</option>\r\n";
				}
				if (!$subdb2[$sub1['fid']]) continue;

				foreach ($subdb2[$sub1['fid']] as $sub2) {
					$forum_cache[$sub2['fid']] = $sub2;

					if ($sub2['cms']) {
						$cmscache .= "<option value=\"$sub2[fid]\">&nbsp;&nbsp; &nbsp; &nbsp;|-  {$fname[$sub2[fid]]}</option>\r\n";
					} elseif ($sub2['f_type']!='hidden') {
						$forumcache .= "<option value=\"$sub2[fid]\">&nbsp;&nbsp; &nbsp; &nbsp;|-  {$fname[$sub2[fid]]}</option>\r\n";
					}
				}
			}
		}
	}

	$forum_cache = "\$forum=".pw_var_export($forum_cache).";";
	$forumcache  = "\$forumcache='\r\n$forumcache';\r\n\$cmscache='\r\n$cmscache';";
	$forumlist_cache = "\$pwForumList=".pw_var_export($forumlist_cache).";\r\n\$pwForumAllList = "
					. pw_var_export($pwForumAllList).";";
	$topic_type_cache = "\$topic_type_cache=".pw_var_export($t_typedb).";";
	$forum_appinfo = "\$forum_appinfo=".pw_var_export($appdb).";";

	pwCache::setData(D_P."data/bbscache/forumcache.php","<?php\r\n".$forumcache."\r\n?>");
	pwCache::setData(D_P.'data/bbscache/forum_cache.php',"<?php\r\n".$forum_cache."\r\n?>");
	pwCache::setData(D_P.'data/bbscache/forumlist_cache.php',"<?php\r\n".$forumlist_cache."\r\n?>");
	pwCache::setData(D_P.'data/bbscache/forum_appinfo.php',"<?php\r\n".$forum_appinfo."\r\n?>");
	pwCache::setData(D_P.'data/bbscache/forum_typecache.php',"<?php\r\n".$topic_type_cache."\r\n?>");
	$cache = $forum_cache."\r\n".$forumlist_cache;
	$db->pw_update(
		"SELECT * FROM pw_cache WHERE name='forum_cache'",
		"UPDATE pw_cache SET cache=".S::sqlEscape($cache,false)." WHERE name='forum_cache'",
		"INSERT INTO pw_cache SET name='forum_cache',cache=".S::sqlEscape($cache,false)
	);
	if (empty($return)) {
		cache_read();
	}
}
function updatecache_forums($fids) {
	global $db;
	$fids = is_array($fids) ? array_unique($fids) : array($fids);
	if (!empty($fids)) {
		$t_typedb = getTopictypeCache();
		$query	= $db->query("SELECT f.*,fe.creditset,fe.forumset,fe.commend,fe.appinfo,c.ifopen as cnifopen FROM pw_forums f LEFT JOIN pw_forumsextra fe ON f.fid=fe.fid LEFT JOIN pw_cnclass c ON f.fid=c.fid WHERE f.fid IN (".S::sqlImplode($fids,false).")");
		while ($forums = $db->fetch_array($query)) {
			$forums['topictype'] = $t_typedb[$forums['fid']];
			writeforumscache($forums);
		}
	}
}
function writeforumscache($forum) {
	$cache = '';
	foreach ($forum as $key => $value) {
		if ($value && in_array($key,array('creditset','forumset','commend','appinfo'))){
			$forum[$key] = unserialize($value);
		}
	}
	pwCache::setData( S::escapePath(D_P."data/forums/fid_{$forum['fid']}.php"),"<?php\r\n\$foruminfo = ".pw_var_export($forum).";\r\n?>");
}
function updatecache_fd($cacheforums=true) {
	global $db;
	$childfid = $havechild = $updatefids = array();
	$query = $db->query("SELECT fid FROM pw_forums WHERE childid=1");
	while ($rt = $db->fetch_array($query)) {
		$childfid[] = $rt['fid'];
	}
	$fup_a = array(0 => '');
	$query = $db->query("SELECT fid,fup,type,forumadmin,fupadmin FROM pw_forums ORDER BY type");
	while ($rt = $db->fetch_array($query)) {
		$rt['fup'] > 0 && $havechild[$rt['fup']] = 1;


		$fupadmin = $fup_a[$rt['fup']];
		if ($rt['fupadmin'] != $fupadmin) {
			//$db->update("UPDATE pw_forums SET fupadmin=" . S::sqlEscape($fupadmin) . " WHERE fid=" . S::sqlEscape($rt['fid']));
			pwQuery::update('pw_forums', 'fid=:fid', array($rt['fid']), array('fupadmin'=>$fupadmin));
			$updatefids[] = $rt['fid'];
		}
		if ($rt['forumadmin'] = trim($rt['forumadmin'],',')) {
			$fupadmin .= $fupadmin ? $rt['forumadmin'].',' : ','.$rt['forumadmin'].',';
		}
		$fup_a[$rt['fid']] = $fupadmin;
	}
	$havechild = array_keys($havechild);
	$fids = array_diff($childfid,$havechild);
	if ($fids) {
		//$db->update("UPDATE pw_forums SET childid='0' WHERE fid IN(".S::sqlImplode($fids,false).")");
		pwQuery::update('pw_forums', 'fid IN(:fid)', array($fids), array('childid'=>0));
	}
	$fids = array_diff($havechild,$childfid);
	if ($fids) {
		$updatefids = array_merge($updatefids,$fids);
		//$db->update("UPDATE pw_forums SET childid='1' WHERE fid IN(".S::sqlImplode($fids,false).")");
		pwQuery::update('pw_forums', 'fid IN (:fid)', array($fids), array('childid'=>'1'));
	}
	if ($cacheforums === true && $updatefids) {
		$updatefids = array_unique($updatefids);
		updatecache_forums($updatefids);
	}
}

/**
* 更新公告缓冲,更新友情联接缓冲
*/
function updatecache_i($return=0) {
	global $db,$db_windpost,$timestamp;
	//公告部分
	require_once(R_P.'require/bbscode.php');
	//* @include pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
	extract(pwCache::getData(D_P.'data/bbscache/forum_cache.php', false));
	//* $db->update("UPDATE pw_forumdata SET aid='0',aids='',aidcache='0'");
	pwQuery::update('pw_forumdata', null, null, array('aid'=>0,'aids'=>0, 'aidcache'=>0));
	
	//* $db->update("UPDATE pw_announce SET ifconvert='0' WHERE ifconvert!='0'");
	pwQuery::update('pw_announce','ifconvert!=:ifconvert', array(0), array('ifconvert'=>0));
	$num = 0;
	$sharelink = $notice_A = $notice_C = $C_cfid = $F_ffid = $F_fid = $cachedb = $cachetype = $newforum = array();
	$query = $db->query("SELECT aid,fid,author,startdate,enddate,url,subject,content FROM pw_announce WHERE ifopen='1' AND (enddate=0 OR enddate>=".S::sqlEscape($timestamp).") ORDER BY vieworder,startdate DESC");
	while ($rt = $db->fetch_array($query)) {
		if ($rt['fid'] == -1) {
			if ($rt['startdate'] <= $timestamp) {
				$num++;
			} else {
				continue;
			}
			$notice_A[$rt['aid']] = array(
				'aid' => $rt['aid'],
				'author' => $rt['author'],
				'startdate' => $rt['startdate'],
				'stime' => get_date($rt['startdate'],'y-m-d'),
				'enddate' => $rt['enddate'],
				'subject' => $rt['subject'],
				'url' => $rt['url']
			);
		} elseif (!$forum[$rt['fid']]['cms'] && $forum[$rt['fid']]['type']=='category') {
			if ($rt['startdate']<=$timestamp) {
				if ($C_cfid[$rt['fid']]) {
					continue;
				} elseif (!$rt['enddate']) {
					$C_cfid[$rt['fid']] = true;
				}
			}
			$rt['subject'] = substrs($rt['subject'],65);
			$notice_C[$rt['fid']][$rt['aid']] = array(
				'aid' => $rt['aid'],
				'fid' => $rt['fid'],
				'author' => $rt['author'],
				'startdate' => $rt['startdate'],
				'enddate' => $rt['enddate'],
				'subject' => $rt['subject'],
				'url' => $rt['url']
			);
		} elseif ($rt['fid']!=-2) {
			if ($rt['startdate']<=$timestamp) {
				if ($F_ffid[$rt['fid']]) {
					continue;
				} elseif (!$rt['enddate']) {
					$F_ffid[$rt['fid']] = true;
				}
			}
			if (!$F_fid[$rt['fid']]['aid'] && $rt['startdate']<=$timestamp && (!$rt['enddate'] || $rt['enddate']>=$timestamp)) {
				$F_fid[$rt['fid']]['aid'] = $rt['aid'];
				if ($rt['content']!=convert($rt['content'],$db_windpost,2)) {
					//* $db->update("UPDATE pw_announce SET ifconvert='1' WHERE aid=".S::sqlEscape($rt['aid'],false));
					pwQuery::update('pw_announce','aid=:aid', array($rt['aid']), array('ifconvert'=>1));
				}
			} else {
				$F_fid[$rt['fid']]['aids'] .= ",$rt[aid]";
			}
		}
	}
	foreach ($forum as $key => $value) {
		$value['aids'] = '';
		$value['aid'] = $value['aidcache'] = 0;
		$update = false;
		if ((int)$F_fid[$key]['aid']>0) {
			$update = true;
			$value['aid'] = $F_fid[$key]['aid'];
		}
		if ($F_fid[$key]['aids']) {
			$update = true;
			$value['aids'] = substr($F_fid[$key]['aids'],1);
			$value['aidcache'] = $timestamp;
		}
		//* $update && $db->update("UPDATE pw_forumdata SET ".S::sqlSingle(array('aid'=>$value['aid'],'aids'=>$value['aids'],'aidcache'=>$value['aidcache']))."WHERE fid=".S::sqlEscape($key));
		$update && pwQuery::update('pw_forumdata', 'fid=:fid', array($key), array('aid'=>$value['aid'],'aids'=>$value['aids'],'aidcache'=>$value['aidcache']));

		$newforum[$key] = $value;
	}
	//友情链接部分
	$sharelogo = $sharetext = '';
	$query = $db->query("SELECT threadorder,name,url,descrip,logo FROM pw_sharelinks WHERE ifcheck=1 ORDER BY threadorder");
	while ($rt = $db->fetch_array($query)) {
		if ($rt['threadorder']<0) {
			$sharelink[0][] = ($rt['logo'] ? "<dl class=\"sharelink cc\"><dt class=\"fl mr10\"><a href=\"$rt[url]\"><img src=\"$rt[logo]\" width=\"88\" height=\"31\" alt=\"$rt[name]\" title=\"$rt[name]\" /></a></dt>" : '')."<dd><p><a href=\"$rt[url]\" target=\"_blank\">$rt[name]</a></p>$rt[descrip]</dd></dl>";
		} elseif ($rt['logo']) {
			$sharelogo .= "<a href=\"$rt[url]\" target=\"_blank\"><img src=\"$rt[logo]\" alt=\"$rt[descrip]\" title=\"$rt[descrip]\" width=\"88\" height=\"31\"></a>";
		} else {
			$sharetext .= "<a href=\"$rt[url]\" target=\"_blank\" title=\"$rt[name] $rt[descrip]\">$rt[name]</a>";
		}
	}
	if ($sharelogo || $sharetext) {
		$brtags = $sharelogo && $sharetext ? '<div class="c"></div>' : '';
		$sharelink[1] = rtrim($sharelogo).$brtags.rtrim($sharetext);
	} else {
		$sharelink[1] = '';
	}
	//更新系统公告+友情链接
	$cachetype['index_cache'] = "\$notice_A=".pw_var_export($notice_A).";\r\n\$sharelink=".pw_var_export($sharelink).";";
	pwCache::setData(D_P.'data/bbscache/index_cache.php',"<?php\r\n{$cachetype[index_cache]}\r\n?>");
	//更新分类公告
	$cachetype['thread_announce'] = "\$notice_A=".pw_var_export($notice_A).";\r\n\$notice_C=".pw_var_export($notice_C).";";
	pwCache::setData(D_P.'data/bbscache/thread_announce.php',"<?php\r\n{$cachetype[thread_announce]}\r\n?>");
	//更新版规
	if ($newforum != $forum) {
		$cachetype['forum_cache'] = "\$forum=".pw_var_export($newforum).";";
		pwCache::setData(D_P.'data/bbscache/forum_cache.php',"<?php\r\n{$cachetype[forum_cache]}\r\n?>");
		//* @include pwCache::getPath(D_P.'data/bbscache/cache_read.php');
		extract(pwCache::getData(D_P.'data/bbscache/cache_read.php', false));
		//* @include pwCache::getPath(D_P.'data/bbscache/forumcache.php');
		extract(pwCache::getData(D_P.'data/bbscache/forumcache.php', false));
		$cachetype['forum_cache'] .= "\r\n\$forumcache='".str_replace(array("\\","'"),array("\\\\","\'"),$forumcache)."';\r\n\$topic_type_cache=".pw_var_export($topic_type_cache).";\r\n\$pwForumList=".pw_var_export($pwForumList).";";
		$sql_in = ",'forum_cache'";
	} else {
		$sql_in = '';
	}
	//更新缓存数据库
	$query = $db->query("SELECT name FROM pw_cache WHERE name IN('index_cache','thread_announce'$sql_in)");
	while ($rt = $db->fetch_array($query,MYSQL_NUM)) {
		$cachedb[] = $rt[0];
	}
	$db->free_result($query);
	foreach ($cachetype as $key => $value) {
		if (in_array($key,$cachedb)) {
			$db->update("UPDATE pw_cache SET cache=".S::sqlEscape($value,false)."WHERE name=".S::sqlEscape($key,false));
		} else {
			$db->update("INSERT INTO pw_cache SET ".S::sqlSingle(array('name'=>$key,'cache'=>$value)));
		}
	}
	if (empty($return)) {
		cache_read();
	}
}
/**
* 更新用户组缓冲
*/
function updatecache_g($gid = array()) {
	global $db;
	$sql = '';
	if (!empty($gid) && is_array($gid)) {
		$sql .= ' AND gid IN(' . S::sqlImplode($gid) . ')';
	} elseif (is_numeric($gid)) {
		$sql .= ' AND gid=' . S::sqlEscape($gid);
	} else {
		$sql .= " AND (ifdefault='0' OR gid='1')";
	}
	$gdb	= array();
	$query	= $db->query("SELECT gid,gptype,grouptitle,groupimg,grouppost FROM pw_usergroups WHERE 1 $sql");
	while ($rt = $db->fetch_array($query)) {
		$rt['SYSTEM'] = $rt['_G'] = array();
		$gdb[$rt['gid']] = $rt;
	}
	if (empty($gdb)) {
		return;
	}
	$query	= $db->query("SELECT gid,rkey,type,rvalue FROM pw_permission WHERE uid='0' AND fid='0' AND gid IN(" . (S::sqlImplode(array_keys($gdb))) .")");
	while ($rt = $db->fetch_array($query)) {
		$id = $rt['gid'];
		if (!isset($gdb[$id])) continue;
		if ($rt['type'] == 'basic') {
			$gdb[$id]['_G'][$rt['rkey']] = $rt['rvalue'];
		} elseif ($gdb[$id]['gptype'] == 'special' || $gdb[$id]['gptype']=='system' && $rt['type'] != 'special') {
			$gdb[$id]['SYSTEM'][$rt['rkey']] = $rt['rvalue'];
		}
	}

	foreach ($gdb as $key => $group) {
		updatecache_gp($group);
	}
}

function updatecache_gp($group) {
	$groupcache = '';
	foreach ($group as $key => $value) {
		if (is_array($value)) {
			$groupcache .= "\${$key}=".pw_var_export($value).";\r\n";
		} else {
			$groupcache .= "\$gp_$key=".pw_var_export($value).";\r\n";
		}
	}
	pwCache::setData( S::escapePath(D_P."data/groupdb/group_$group[gid].php"),"<?php\r\n".$groupcache."?>");
}
function updatecache_gr($return=0) {
	global $db;
	$gpright = array();
	$query = $db->query("SELECT gid,rkey,rvalue FROM pw_permission WHERE uid='0' AND fid='0' AND rkey IN ('imgwidth','imgheight','fontsize')");
	while ($rt = $db->fetch_array($query)) {
		$gpright[$rt['gid']][$rt['rkey']] = $rt['rvalue'];
	}
	$gpright = "\$gp_right=".pw_var_export($gpright).";";
	pwCache::setData(D_P."data/bbscache/gp_right.php","<?php\r\n{$gpright}\r\n?>");
	$cache = $gpright;
	$db->pw_update(
		"SELECT * FROM pw_cache WHERE name='gp_right'",
		"UPDATE pw_cache SET cache=".S::sqlEscape($cache,false)."WHERE name='gp_right'",
		"INSERT INTO pw_cache SET name='gp_right',cache=".S::sqlEscape($cache,false)
	);
	if (empty($return)) {
		cache_read();
	}
}
/**
* 更新用户等级缓冲
*/
function updatecache_l($return=0) {
	global $db;
	$query = $db->query("SELECT gid,gptype,grouptitle,groupimg,grouppost FROM pw_usergroups ORDER BY grouppost,gid");
	$defaultdb = "\$ltitle=\$lpic=\$lneed=array();\r\n/**\r\n* default\r\n*/\r\n";
	$sysdb = "\r\n/**\r\n* system\r\n*/\r\n";
	$vipdb = "\r\n/**\r\n* special\r\n*/\r\n";
	$memdb = "\r\n/**\r\n* member\r\n*/\r\n";
	while (@extract($db->fetch_array($query))) {
		$gid = (int)$gid;
		if ($gptype == 'member') {
			$memdb .= "\$ltitle[$gid]=".pw_var_export($grouptitle).";\t\t\$lpic[$gid]=".pw_var_export($groupimg). ";\t\t\$lneed[$gid]=".pw_var_export($grouppost).";\r\n";
		} elseif ($gptype == 'special') {
			$vipdb .= "\$ltitle[$gid]=".pw_var_export($grouptitle).";\t\t\$lpic[$gid]=".pw_var_export($groupimg). ";\r\n";
		} elseif ($gptype == 'system') {
			$sysdb .= "\$ltitle[$gid]=".pw_var_export($grouptitle).";\t\t\$lpic[$gid]=".pw_var_export($groupimg). ";\r\n";
		} elseif ($gptype == 'default') {
			$defaultdb .= "\$ltitle[$gid]=".pw_var_export($grouptitle).";\t\t\$lpic[$gid]=". pw_var_export($groupimg).";\r\n";
		}
	}
	pwCache::setData(D_P.'data/bbscache/level.php',"<?php\r\n".$defaultdb.$sysdb.$vipdb.$memdb."\r\n?>");
	$cache = $defaultdb.$sysdb.$vipdb.$memdb;
	$db->pw_update(
		"SELECT * FROM pw_cache WHERE name='level'",
		"UPDATE pw_cache SET cache=".S::sqlEscape($cache,false)."WHERE name='level'",
		"INSERT INTO pw_cache SET name='level',cache=".S::sqlEscape($cache,false)
	);
	if (empty($return)) {
		cache_read();
	}
}

/**
* 更新核心设置组缓冲
*/
function updatecache_ad() {
	global $db,$timestamp;
	$cates = $advertdb = array();
	$query = $db->query("SELECT uid as ifhire,ckey,ifshow,config FROM pw_advert WHERE type IN (0,2) AND ifshow=1");
	while ($rt = $db->fetch_array($query)) {
		$rt['config'] = unserialize($rt['config']);
		$cates[$rt['ckey']] = $rt;
	}
	$query = $db->query("SELECT * FROM pw_advert WHERE type IN (1,3) AND ifshow=1 ORDER BY orderby ASC");
	$index = 0;
	while ($rt = $db->fetch_array($query)) {
		if (!isset($cates[$rt['ckey']]) || ($rt['etime'] && $rt['etime']<$timestamp)) continue;

		if (!isset($advertdb[$rt['ckey']]['config'])) {
			$advertdb['config'][$rt['ckey']] = $cates[$rt['ckey']]['config']['display'];
		}
		$ad   = array();$code = $jspop = $style = '';
		$conf = unserialize($rt['config']);

		if ($conf['type'] == 'code') {
			$code = $conf['htmlcode'];
		} elseif ($conf['type'] == 'txt') {
			$conf['color'] && $style .= "color:$conf[color];";
			$conf['size'] && $style .= "font-size:$conf[size];";
			$style && $style = " style=\"$style\" ";
			$code = "<a href=\"$conf[link]\" target=\"_blank\"{$style}>$conf[title]</a>";
		} elseif ($conf['type'] == 'img') {
			$conf['width'] && $style .= " width=\"$conf[width]\"";
			$conf['height'] && $style .= " height=\"$conf[height]\"";
			$conf['descrip'] && $style .= " alt=\"$conf[descrip]\"";
			/*support multi pictures*/
			if($conf['multi']>1){
				$code ='<div id="x-pics'.$index.'">';
				/* support special adver*/
				if(in_array($rt['ckey'],array('Site.PopupNotice','Site.FloatRand','Site.FloatLeft','Site.FloatRight'))){
					$code .= '<script type="text/javascript">imgloop('.$index.')<\/script>';
				}else{
					$code .= '<script type="text/javascript">window.onload=function(){imgloop('.$index.')}</script>';
				}
				for($i=0;$i<$conf['multi'];$i++){
					$code .= "<a title=\"$conf[title]\" href=\"".$conf['link'][$i]."\" target=\"_blank\" style=\"display:none;\"><img src=\"".$conf[url][$i]."\"{$style}/></a>";
				}
				$index++;
				$code .='</div>';
			}else{
				$code = "<a title=\"$conf[title]\" href=\"$conf[link]\" target=\"_blank\"><img src=\"$conf[url]\"{$style}/></a>";
			}
		} elseif ($conf['type'] == 'flash') {
			$conf['width'] && $style .= " width=\"$conf[width]\"";
			$conf['height'] && $style .= " height=\"$conf[height]\"";
			$code = "<embed src=\"$conf[link]\" type=\"application/x-shockwave-flash\"{$style} wmode=\"opaque\" allowScriptAccess=\"always\"></embed>";
		}
		if ($conf['winHeight']) {
			$conf['title'] = ($conf['winTitle']) ? $conf['winTitle'] : $conf['title'];
			$conf['winHeight']= (int) $conf['winHeight'];
			$conf['winWidth'] = (int) $conf['winWidth'];
			$conf['winClose'] = (int) $conf['winClose'];
			$jspop = "{'title':'$conf[title]','winHeight':$conf[winHeight],'winWidth':$conf[winWidth],'winClose':$conf[winClose]}";
		}
		if ($conf['lou']) {
			$ad['lou'] = $conf['lou'];
		}
		if ($conf['fid']) {
			$ad['fid'] = $conf['fid'];
		}
		if ($conf['page']) {
			$ad['page'] = ",".$conf['page'].",";
		}
		if ($conf['mode']) {
			$ad['mode'] = $conf['mode'];
		}
		if ($conf['ddate']) {
			$ad['ddate'] = $conf['ddate'];
		}
		if ($conf['dweek']) {
			$ad['dweek'] = $conf['dweek'];
		}
		if ($conf['dtime']) {
			$ad['dtime'] = $conf['dtime'];
		}
		$ad['stime'] = $rt['stime'];
		$ad['etime'] = $rt['etime'];
		$ad['code']  = $jspop ? array('other'=>$jspop,'code'=>$code) : $code;
		$advertdb[$rt['ckey']][$rt['id']] = $ad;
	}
	return $advertdb;
}

/**
* 更新核心设置组缓冲
*/
function updatecache_c() {
	global $db,$db_bbsurl,$db_mode, $updateCookiePre;
	$query = $db->query("SELECT db_name,vtype,db_value FROM pw_config");
	$configdb = $regdb = "<?php\r\n";
	$sitehash = '';
	$cookiepre = false;
	while (@extract($db->fetch_array($query))) {
		$db_name = key_cv($db_name);
		if ($vtype == 'array' && !is_array($db_value = unserialize($db_value))) {
			$db_value = array();
		}
		if (strpos($db_name, 'db_') !== false || strpos($db_name, 'uc_') !== false) {
			$configdb .= "\$$db_name=".pw_var_export($db_value).";\r\n";
		} elseif (strpos($db_name,'rg_') !== false) {
			$regdb .= "\$$db_name=".pw_var_export($db_value).";\r\n";
		}
		$db_name == 'db_cookiepre' && $cookiepre = true;
		$db_name == 'db_sitehash' && $sitehash = $db_value;
	}
	$advertdb = updatecache_ad();
	$configdb .= "\$db_advertdb=".pw_var_export($advertdb).";\r\n";
	$configdb .= "\$db_windcode=".pw_var_export(updatecache_wcode()).";\r\n";
	!$cookiepre && $configdb .= "\$db_cookiepre='" . substr(md5($sitehash), 0, 5) . "';\r\n";
	
	$creditdb = array();
	$query = $db->query("SELECT * FROM pw_credits");
	//$query = $db->query("SELECT * FROM pw_credits WHERE type='main'");
	while ($rt = $db->fetch_array($query)) {
		$creditdb[$rt['cid']] = array($rt['name'],$rt['unit'],$rt['description']);
	}
	$configdb .= "\$_CREDITDB=".pw_var_export($creditdb).";\r\n?>";
	$regdb .= "?>";
	pwCache::setData(D_P.'data/bbscache/config.php',$configdb);
	pwCache::setData(D_P.'data/bbscache/dbreg.php',$regdb);
	updatecache_baseconfig();
}

/**
 * 基础配置文件更新
 *
 */
function updatecache_baseconfig(){
	global $db;
	$baseConfigNames = array('db_datastore' ,'db_memcache', 'db_classfile_compress', 'db_cachefile_compress', 'db_filecache_to_memcache');
	$baseConfig = "<?php\r\n";
	$query = $db->query('SELECT db_name,vtype,db_value FROM pw_config WHERE db_name IN (' . S::sqlImplode($baseConfigNames) . ')');	
	while(@extract($db->fetch_array($query))){
		$db_name = key_cv($db_name);
		if ($vtype == 'array' && !is_array($db_value = unserialize($db_value))) {
			$db_value = array();
		}	
		$baseConfig .= "\$$db_name=".pw_var_export($db_value).";\r\n";
	}
	$baseConfig .= "?>";
	pwCache::writeover(D_P . 'data/bbscache/baseconfig.php', $baseConfig);
}

function updatecache_wcode() {
	global $db;
	$pwcode = array();
	$regxp = array('([^\(&]+?)','(\w+)','(\d+)');
	$query = $db->query("SELECT name,pattern,replacement,param FROM pw_windcode");
	while (@extract($db->fetch_array($query))) {
		list($o,$t,$s) = explode("\t",$pattern);
		$name = preg_quote($name,'/');
		if ($param == 2) {
			$pwcode['searcharray'][] = "/\[$name=$regxp[$o]\]$regxp[$t]\[\/$name\]/is";
		} elseif ($param == 3) {
			$pwcode['searcharray'][] = "/\[$name=$regxp[$o],$regxp[$t]\]$regxp[$s]\[\/$name\]/is";
		} else {
			$pwcode['searcharray'][] = "/\[$name\]$regxp[$o]\[\/$name\]/is";
		}
		$pwcode['replacearray'][] = preg_replace('/\{(\d+)\}/i','\\\\\\1',$replacement);
	}
	return $pwcode;
}

/**
* 更新风格缓冲
*/
function updatecache_sy($name='') {
	global $db,$db_picpath;
	$imgpath = '../../'.$db_picpath;
	$sqlwhere = "WHERE uid=0 ";
	if ($name) {
		$sqlwhere .= " AND name=".S::sqlEscape($name);
	}
	$query = $db->query("SELECT * FROM pw_styles $sqlwhere");
	while (@extract($db->fetch_array($query))) {
		$stylecontent = "<?php
\$stylepath = ".pw_var_export($stylepath).";
\$tplpath = ".pw_var_export($tplpath).";
\$yeyestyle = ".pw_var_export($yeyestyle).";
\$bgcolor = ".pw_var_export($bgcolor).";
\$linkcolor = ".pw_var_export($linkcolor).";
\$tablecolor = ".pw_var_export($tablecolor).";
\$tdcolor = ".pw_var_export($tdcolor).";
\$tablewidth = ".pw_var_export($tablewidth).";
\$mtablewidth = ".pw_var_export($mtablewidth).";
\$headcolor	= ".pw_var_export($headcolor).";
\$headborder = ".pw_var_export($headborder).";
\$headfontone = ".pw_var_export($headfontone).";
\$headfonttwo = ".pw_var_export($headfonttwo).";
\$cbgcolor = ".pw_var_export($cbgcolor).";
\$cbgborder = ".pw_var_export($cbgborder).";
\$cbgfont = ".pw_var_export($cbgfont).";
\$forumcolorone	= ".pw_var_export($forumcolorone).";
\$forumcolortwo	= ".pw_var_export($forumcolortwo).";
\$extcss = ".pw_var_export($extcss).";
\?>";
		$style_css = explode('<!--css-->',readover(D_P."data/style/{$tplpath}_css.htm"));
		$style_css = addslashes(str_replace(array('<style type="text/css">','</style>'),'',$style_css[1]));
		eval("\$style_css = \"$style_css\";");
		//writeover(D_P."data/bbscache/$tplpath.css",$style_css);
		pwCache::writeover(S::escapePath(D_P."data/bbscache/".$tplpath."_".$stylepath.".css"),$style_css);
		pwCache::setData(S::escapePath(D_P."data/style/$name.php"),str_replace("\?>","?>",$stylecontent));
		$sqlStyles[] = $name;
	}
	if (empty($name)) {
		$fp = opendir(D_P."data/style/");
		while ($skinfile = readdir($fp)) {
			if (eregi("\.php$",$skinfile)) {
				$skinfile = str_replace(".php","",$skinfile);
				$styles[] = $skinfile;
			}
		}
		closedir($fp);
		foreach ($styles as $key => $value) {
			if (!in_array($value,$sqlStyles)) {
				//* include S::escapePath(D_P."data/style/$value.php");
				extract(pwCache::getData(S::escapePath(D_P."data/style/$value.php"), false));
				
				$style_css = explode('<!--css-->',readover(D_P."data/style/{$tplpath}_css.htm"));
				$style_css = addslashes(str_replace(array('<style type="text/css">','</style>'),'',$style_css[1]));
				eval("\$style_css = \"$style_css\";");
				//writeover(D_P."data/bbscache/$tplpath.css",$style_css);
				pwCache::writeover(S::escapePath(D_P."data/bbscache/".$tplpath."_".$stylepath.".css"),$style_css);
			}
		}
	}
}
/**
* 更新动作表情缓冲
*/
function updatecache_p($return=0) {
	global $db,$db_htmdir;
	$faces		= "\$faces=array(\r\n"; //表情组
	$face		= "\$face=array(\r\n"; //表情
	$jsface		= "var face=new Array();\n";
	$jsfaces	= "var faces=new Array();\n";
	$jsfacedb	= "var facedb={};\n";

	$count = 0;
	@extract($db->get_one("SELECT db_value AS fc_shownum FROM pw_config WHERE db_name='fc_shownum'"));
	$rs = $db->query("SELECT * FROM pw_smiles WHERE type=0 ORDER BY vieworder");
	while (@extract(db_cv($db->fetch_array($rs)))) {
		if ($count == 0) {
			$jsdefault="var defaultface='$path';\nvar fc_shownum='$fc_shownum';\n\n";
			$count = 1;
		}
		$faces		.= "\t'$path'=>array(\r\n";
		$faces		.= "\t\t'name'=>'$name',\r\n";
		$faces		.=  "\t\t'child'=>array(";
		$jsfaces	.= "faces['$path'] = [";
		$jsfacedb	.= "facedb['$path'] = '$name';\n";
		$query = $db->query("SELECT * FROM pw_smiles WHERE type='$id' ORDER BY vieworder");
		while ($smile = db_cv($db->fetch_array($query))) {
			$face	.= "\t'$smile[id]'=>array('$path/$smile[path]','$smile[name]','$smile[descipt]'),\r\n";
			$faces	.= "'$smile[id]',";
			$jsface	.= "face[$smile[id]]=['$path/$smile[path]','$smile[name]'];\n";
			$jsfaces.= "$smile[id],";
		}
		$faces		.= "),\r\n";
		$faces		.= "\t),\r\n";
		$jsfaces	.= "];\n";
	}
	$faces	.= ");\r\n";
	$face	.= ");";
	pwCache::writeover(R_P . $db_htmdir . '/js/face.js',$jsdefault.$jsfacedb."\n".$jsface."\n".$jsfaces);
	pwCache::setData(D_P . "data/bbscache/postcache.php","<?php\r\n".$faces.$face."\r\n?>");

	$cache = $faces.$face;
	$db->pw_update(
		"SELECT * FROM pw_cache WHERE name='postcache'",
		"UPDATE pw_cache SET cache=".S::sqlEscape($cache,false)."WHERE name='postcache'",
		"INSERT INTO pw_cache SET name='postcache',cache=".S::sqlEscape($cache,false)
	);
	if (empty($return)) {
		cache_read();
	}
}
/***************** APP更改内容 缓存更改 START **********/
/**
* 更新禁用词语缓冲
*/
function updatecache_w() {
	global $db;
	//$db_wordsfb = $db->get_value("SELECT db_value FROM pw_config WHERE db_name='db_wordsfb'");
	//$db_wordsfb = $db_wordsfb >=127 ? 1 : $db_wordsfb+1;
	//$db->update("UPDATE pw_config SET db_value=".S::sqlEscape($db_wordsfb,false)."WHERE db_name='db_wordsfb'");
	//updatecache_c();

	$sql = "SELECT id FROM pw_filter_class WHERE state=0";
	$query = $db->query($sql);
	$classid = '';
	while ($value = $db->fetch_array($query)) {
		$classid .= $classid ? ", ".S::sqlEscape($value['id']) : S::sqlEscape($value['id']);
	}

	if (!$classid) $classid=-1;

	$writeinfo = '';
	$replace = $wordsfb = $alarm = array();
	$query = $db->query("SELECT * FROM pw_wordfb WHERE classid NOT IN ($classid) ORDER BY id");
	while (@extract($db->fetch_array($query))) {
		if ($word) {
			$word = trim(preg_quote($word,'/'));
			//$word = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", $word);
			switch ($type) {
			    case 1:
			        $wordsfb[$word] = $wordreplace;
			        break;
			    case 2:
			        $alarm[$word] = $wordreplace;
			         break;
			    case 3:
			        $replace[$word] = $wordreplace;
			        break;
			    default:
			        $replace[$word] = $wordreplace;
			        break;
			}
		}
	}
	$writeinfo .= "\r\n\$wordsfb=".pw_var_export($wordsfb).";";
	$writeinfo .= "\r\n\$alarm=".pw_var_export($alarm).";";
	$writeinfo .= "\r\n\$replace=".pw_var_export($replace).";";
	pwCache::setData(D_P."data/bbscache/wordsfb.php","<?php\r\n".$writeinfo."\r\n?>");
}
/***************** APP更改内容 缓存更改 END **********/

function updatecache_bk() {
	global $db;
	$query=$db->query("SELECT * FROM pw_hack WHERE hk_name LIKE 'bk_%'");
	$configdb="<?php\r\n";
	while (@extract($db->fetch_array($query))) {
		$hk_name = key_cv($hk_name);
		$configdb.="\$$hk_name=".pw_var_export($hk_value).";\r\n";
	}
	$configdb.="?>";
	pwCache::setData(D_P.'data/bbscache/bk_config.php',$configdb);
}
function updatecache_df() {
	global $db;
	$query = $db->query("SELECT * FROM pw_config WHERE db_name LIKE 'df_%'");
	$configdb = "<?php\r\n";
	$_cachedb = $_newdb = $_cmsdb = $_fiddb = $_forumlogodb = array();

	while (@extract($db->fetch_array($query))) {
		$db_name = key_cv($db_name);
		if ($db_name == 'df_cache') {
			$db_value = P_unserialize($db_value);
			if (is_array($db_value)) {
				foreach ($db_value as $key=>$value) {
					$_cachedb[$key] = array(trim($value[0]),trim($value[1]));
				}
			}
		} elseif ($db_name == 'df_NEW') {
			$db_value = P_unserialize($db_value);
			if (is_array($db_value)) {
				foreach ($db_value as $value) {
					$_newdb[] = $value;
				}
			}
		} elseif ($db_name=='df_CMS') {
			$db_value = P_unserialize($db_value);
			if (is_array($db_value)) {
				foreach ($db_value as $value) {
					$_cmsdb[] = $value;
				}
			}
		} elseif ($db_name=='df_FID') {
			$db_value = P_unserialize($db_value);
			if (is_array($db_value)) {
				foreach ($db_value as $value) {
					$_fiddb[] = $value;
				}
			}
		} elseif ($db_name=='df_forumlogo') {
			$db_value = P_unserialize($db_value);
			if (is_array($db_value)) {
				foreach($db_value as $key => $value){
					if ($value[0]) {
						$_forumlogodb[$key] = array($value[0],$value[1],$value[2],$value[3]);
					}
				}
			}
		} else {
			$configdb .= "\$$db_name=".pw_var_export($db_value).";\r\n";
		}
	}
	$configdb .= "\r\n\$df_cache=".pw_var_export($_cachedb).";\r\n";
	$configdb .= "\r\n\$df_NEW=".pw_var_export($_newdb).";\r\n";
	$configdb .= "\r\n\$df_CMS=".pw_var_export($_cmsdb).";\r\n";
	$configdb .= "\r\n\$df_FID=".pw_var_export($_fiddb).";\r\n";
	$configdb .= "\r\n\$df_forumlogo=".pw_var_export($_forumlogodb).";\r\n";
	$configdb .= "?>";
	pwCache::setData(D_P.'data/bbscache/c_config.php',$configdb);
}
/*
function updatecache_cy() {
	global $db;
	$query = $db->query("SELECT * FROM pw_hack WHERE hk_name LIKE 'cn\_%'");
	$colonydb = "<?php\r\n";
	while (@extract($db->fetch_array($query))) {
		$hk_name = key_cv($hk_name);
		$colonydb .= "\$$hk_name=".pw_var_export($hk_value).";\r\n";
	}
	$colonydb .= "\n?>";
	writeover(D_P.'data/bbscache/cn_config.php', $colonydb);
}
*/
function updatecache_inv() {
	global $db;
	$query = $db->query("SELECT * FROM pw_hack WHERE hk_name LIKE 'inv_%'");
	$invdb = "<?php\r\n";
	while (@extract($db->fetch_array($query))) {
		$hk_name = key_cv($hk_name);
		$invdb .="\$$hk_name=".pw_var_export($hk_value).";\r\n";
	}
	$invdb .="\n?>";
	pwCache::setData(D_P.'data/bbscache/inv_config.php', $invdb);
}
function updatecache_ol() {
	global $db;
	$onlinedb = "<?php\r\n";
	$query = $db->query("SELECT * FROM pw_config WHERE db_name LIKE 'ol_%'");
	while (@extract($db->fetch_array($query))) {
		$db_name   = key_cv($db_name);
		$onlinedb .= "\$$db_name=".pw_var_export($db_value).";\r\n";
	}
	$onlinedb .= "?>";
	pwCache::setData(D_P.'data/bbscache/ol_config.php',$onlinedb);
}

function updatecache_mddb($return=0) {
	global $db;
	$medalService = L::loadClass('medalservice','medal');
	$medalInfos = $medalService->getAllOpenMedals();
	
	$cache.="\$_MEDALDB=".pw_var_export($medalInfos).";\r\n";
	$db->pw_update(
		"SELECT * FROM pw_cache WHERE name='medaldb'",
		"UPDATE pw_cache SET cache=".S::sqlEscape($cache,false)."WHERE name='medaldb'",
		"INSERT INTO pw_cache SET name='medaldb',cache=".S::sqlEscape($cache,false)
	);
	if (empty($return)) {
		cache_read();
	}
}
function updatecache_plan() {
	global $db;
	$nexttime = $db->get_value("SELECT MIN(nexttime) FROM pw_plan WHERE ifopen='1' AND nexttime>0");
	//* $db->update("UPDATE pw_bbsinfo SET plantime='$nexttime' WHERE id='1'");
	pwQuery::update('pw_bbsinfo', 'id=:id', array(1), array('plantime'=>$nexttime));
}
function updatecache_conf($m,$hk = false,$filename = '') {
	global $db;
	if ($hk) {
		$sql = "SELECT hk_name AS name,vtype,hk_value AS value FROM pw_hack WHERE hk_name LIKE '{$m}\_%'";
	} else {
		$sql = "SELECT db_name AS name,vtype,db_value AS value FROM pw_config WHERE db_name LIKE '{$m}\_%'";
	}
	$confdb	= '';
	$query = $db->query($sql);
	while ($rt = $db->fetch_array($query)) {
		$rt['name'] = key_cv($rt['name']);
		if ($rt['vtype'] == 'array' && !is_array($rt['value'] = unserialize($rt['value']))) {
			$rt['value'] = array();
		}
		$confdb	.= "\${$rt['name']}=" . pw_var_export($rt['value']) . ";\r\n";
	}
	!$filename && $filename = $m.'_config.php';
	pwCache::setData(S::escapePath(D_P."data/bbscache/$filename"),"<?php\r\n".$confdb."?>");
}
function updatecache_ml() {
	global $db;
	$maildb	= '';
	$query	= $db->query("SELECT * FROM pw_config WHERE db_name LIKE 'ml\_%'");
	while (@extract($db->fetch_array($query))) {
		$db_name = key_cv($db_name);
		$maildb	.= "\$$db_name=".pw_var_export($db_value).";\r\n";
	}
	pwCache::setData(D_P.'data/bbscache/mail_config.php',"<?php\r\n".$maildb."?>");
}
function updatecache_ftp() {
	global $db;
	$ftpdb	= '';
	$query	= $db->query("SELECT * FROM pw_config WHERE db_name LIKE 'ftp\_%'");
	while (@extract($db->fetch_array($query))) {
		$db_name = key_cv($db_name);
		$ftpdb	.= "\$$db_name=".pw_var_export($db_value).";\r\n";
	}
	pwCache::setData(D_P.'data/bbscache/ftp_config.php',"<?php\r\n".$ftpdb."?>");
}
function updatecache_field($return=0) {
	global $db;
	$customfield = array();
	$query = $db->query("SELECT * FROM pw_customfield WHERE state='1' ORDER BY vieworder ASC");
	while ($rt = $db->fetch_array($query)) {
		$customfield[] = $rt;
	}
	$cachedb = "\$customfield=".pw_var_export($customfield).";";
	pwCache::setData(D_P.'data/bbscache/customfield.php',"<?php\r\n".$cachedb."\r\n?>");
	$cache = $cachedb;
	$db->pw_update(
		"SELECT * FROM pw_cache WHERE name='customfield'",
		"UPDATE pw_cache SET cache=".S::sqlEscape($cache,false)."WHERE name='customfield'",
		"INSERT INTO pw_cache SET name='customfield',cache=".S::sqlEscape($cache,false)
	);
	if (empty($return)) {
		cache_read();
	}
}
function updatecache_form() {
	global $db;
	$rs = $db->query("SELECT id,name,value FROM pw_setform WHERE ifopen='1'");
	$setform = "<?php\n\$setformdb=array(\n";
	while ($rt = $db->fetch_array($rs)) {
		$rt['value'] = unserialize($rt['value']);
		$setformdb[$rt['id']] = $rt;
	}
	$setformdb = "\$setformdb=".pw_var_export($setformdb).";\r\n";
	pwCache::setData(D_P.'data/bbscache/setform.php',"<?php\r\n".$setformdb."?>");
}
function updatecache_help() {
	global $db;
	$db->update("UPDATE pw_help SET lv='0',fathers='' WHERE hup='0'");
	$ifchilds = $_HELP = $gorydb = $subdb = array();
	$query = $db->query('SELECT hid,hup,lv,fathers,ifchild,title,url,content,vieworder FROM pw_help ORDER BY vieworder,hid');
	while ($rt = $db->fetch_array($query)) {
		$rt['ifcontent'] = $rt['content'] ? 1 : 0;
		if (!$rt['hup']) {
			$gorydb[] = array('hid' => $rt['hid'],'hup' => $rt['hup'],'lv' => $rt['lv'],'fathers' => $rt['fathers'],'ifchild' => $rt['ifchild'],'title' => $rt['title'],'vieworder' => $rt['vieworder'],'ifcontent' => $rt['ifcontent'],'url' => $rt['url']);
		} else {
			$subdb[$rt['hup']][] = array('hid' => $rt['hid'],'hup' => $rt['hup'],'lv' => $rt['lv'],'fathers' => $rt['fathers'],'ifchild' => $rt['ifchild'],'title' => $rt['title'],'vieworder' => $rt['vieworder'],'ifcontent' => $rt['ifcontent'],'url' => $rt['url']);
		}
	}
	foreach ($gorydb as $value) {
		if (!empty($value)) {
			$_HELP[$value['hid']] = $value;
			if (!empty($subdb[$value['hid']])) {
				$value['ifchild']!='1' && $ifchilds[] = $value['hid'];
				$_HELP += get_subhelp($subdb,$value['hid']);
			}
		}
	}
	if ($ifchilds) {
		$db->update("UPDATE pw_help SET ifchild='1' WHERE hid IN (".S::sqlImplode($ifchilds,false).')');
	}
	$writecache = '$_HELP = '.pw_var_export($_HELP).";\r\n";
	pwCache::setData(D_P.'data/bbscache/help_cache.php',"<?php\r\n$writecache?>");
}
function updatecache_hotforum() {
	global $db;
	$shortcutforum = array();
	$query	= $db->query("SELECT f.fid,f.type,f.name,f.style,f.f_type FROM pw_forums f LEFT JOIN pw_forumdata fd ON f.fid=fd.fid ORDER BY fd.article LIMIT 6");
	while ($forum = $db->fetch_array($query)) {
		$shortcutforum[$forum['fid']] = $forum;
	}
	$shortcutforum = "\$shortcutforum=".pw_var_export($shortcutforum).";";
	pwCache::setData(D_P.'data/bbscache/shortcutforum_cache.php',"<?php\r\n$shortcutforum?>");
}

function updatecache_openforum() {
	global $db;
	$forumIds=array();
	$query	= $db->query("SELECT * FROM pw_forums WHERE type<>'category'");
	while ($foruminfo = $db->fetch_array($query)) {
		if (!$foruminfo['allowvisit'] && $foruminfo['f_type'] != 'hidden' && !$foruminfo['password'] && !$foruminfo['forumsell']){
			$forumIds[]=$foruminfo['fid'];
		}
	}
	$openforum = "\$openforum=".pw_var_export($forumIds).";";
	pwCache::setData(D_P.'data/bbscache/forum_cache_openforum.php',"<?php\r\n$openforum?>");
}
/*更新分类主题缓存*/
function updatecache_topic() {
	global $db;
	$topiccatedb = $topicmodeldb = array();
	$query = $db->query("SELECT cateid,name,ifable,vieworder,ifdel FROM pw_topiccate ORDER BY vieworder");

	$configdb = "<?php\r\n";
	while ($rt = $db->fetch_array($query)) {
		$topiccatedb[$rt['cateid']] = $rt;
	}

	$query = $db->query("SELECT name,modelid,cateid,ifable,vieworder FROM pw_topicmodel ORDER BY vieworder");
	$configdb = "<?php\r\n";
	while ($rt = $db->fetch_array($query)) {
		$topicmodeldb[$rt['modelid']] = $rt;
		$modelids[] = $rt['modelid'];
	}
	$configdb .= "\$topiccatedb=".pw_var_export($topiccatedb).";\r\n";
	$configdb .= "\$topicmodeldb=".pw_var_export($topicmodeldb).";\r\n";
	$configdb .= "?>";
	pwCache::setData(D_P.'data/bbscache/topic_config.php',$configdb);
	$modelids = implode(',',$modelids);
	setConfig('db_modelids',$modelids);
	updatecache_c();
}

/*更新团购缓存*/
function updatecache_postcate() {
	global $db;
	$postcatedb = array();
	$query = $db->query("SELECT pcid,name,ifable,vieworder,viewright,adminright FROM pw_postcate ORDER BY vieworder");
	$configdb = "<?php\r\n";
	while ($rt = $db->fetch_array($query)) {
		$postcatedb[$rt['pcid']] = $rt;
		$pcids[] = $rt['pcid'];
	}

	$configdb .= "\$postcatedb=".pw_var_export($postcatedb).";\r\n";
	$configdb .= "?>";
	pwCache::setData(D_P.'data/bbscache/postcate_config.php',$configdb);
	$pcids = implode(',',$pcids);
	setConfig('db_pcids',$pcids);
	updatecache_c();
}

/**
 * 更新活动缓存
 * @global DB 数据库
 */
function updateCacheActivity() {
	global $db;
	$activity_catedb = $activity_modeldb = array();

	$query = $db->query("SELECT actid,name,ifable,vieworder,ifdel FROM pw_activitycate ORDER BY vieworder");
	$configdb = "<?php\r\n";
	while ($rt = $db->fetch_array($query)) {
		$activity_catedb[$rt['actid']] = $rt;
	}

	$query = $db->query("SELECT actmid,name,actid,ifable,vieworder FROM pw_activitymodel ORDER BY vieworder");
	$configdb = "<?php\r\n";
	while ($rt = $db->fetch_array($query)) {
		$activity_modeldb[$rt['actmid']] = $rt;
		$actmids[] = $rt['actmid'];
	}

	$configdb .= "\$activity_catedb=".pw_var_export($activity_catedb).";\r\n";
	$configdb .= "\$activity_modeldb=".pw_var_export($activity_modeldb).";\r\n";
	$configdb .= "?>";
	pwCache::setData(D_P.'data/bbscache/activity_config.php',$configdb);
	$actmids = implode(',',$actmids);
	setConfig('db_actmids',$actmids);
	updatecache_c();
}

function get_subhelp($subdb,$hid,$lv = 0,$fathers = null) {
	global $db;
	$rtarray = array();
	$lv++;
	$fathers .= (empty($fathers) ? '' : ',').$hid;
	foreach ($subdb[$hid] as $value) {
		$sql = array();
		if ($value['lv'] != $lv) {
			$value['lv'] = $lv;
			$sql['lv'] = $lv;
		}
		if ($value['fathers']!=$fathers) {
			$value['fathers'] = $fathers;
			$sql['fathers'] = $fathers;
		}
		$get = 1;
		if (empty($subdb[$value['hid']])) {
			$get = 0;
			if ($value['ifchild']!='0') {
				$sql['ifchild'] = 0;
				$value['ifchild'] = '0';
			}
		} elseif ($value['ifchild']!='1') {
			$sql['ifchild'] = 1;
			$value['ifchild'] = '1';
		}
		$rtarray[$value['hid']] = $value;
		$sql && $db->update("UPDATE pw_help SET ".S::sqlSingle($sql,false)."WHERE hid=".S::sqlEscape($value['hid']));
		$get && $rtarray += get_subhelp($subdb,$value['hid'],$lv,$fathers);
	}
	return $rtarray;
}
function cache_read() {
	global $db;
	$query = $db->query("SELECT * FROM pw_cache WHERE name IN('forum_cache','level','gp_right','customfield','medaldb','postcache','index_cache','thread_announce')");
	$c = array();
	while ($rt = $db->fetch_array($query)) {
		$c[$rt['name']] = $rt['cache']."\r\n\r\n";
	}
	pwCache::setData(D_P.'data/bbscache/cache_index.php',"<?php\r\n{$c[level]}{$c[index_cache]}?>");
	pwCache::setData(D_P.'data/bbscache/cache_thread.php',"<?php\r\n{$c[forum_cache]}{$c[thread_announce]}?>");
	pwCache::setData(D_P.'data/bbscache/cache_read.php',"<?php\r\n{$c[forum_cache]}{$c[level]}{$c[gp_right]}{$c[customfield]}{$c[medaldb]}?>");
	pwCache::setData(D_P.'data/bbscache/cache_post.php',"<?php\r\n{$c[forum_cache]}{$c[level]}{$c[postcache]}?>");
}
function db_cv($array) {
	if (is_array($array)) {
		foreach ($array as $key => $value) {
			$array[$key] = db_cv($value);
		}
	} else {
		$array = str_replace(array("\\","'"),array("\\\\","\'"),$array);
	}
	return $array;
}
function key_cv($key) {
	return preg_replace('/[^\d\w\_]/is','',$key);
}

function updateStampCache(){
	global $db;
	$mode_stamp = array();
	$query = $db->query("SELECT s.*,b.name as block,b.function,b.type,b.fid,b.special,b.ifextra FROM pw_stamp s LEFT JOIN pw_block b ON s.init=b.bid");
	while ($rt = $db->fetch_array($query)) {
		$mode_stamp[$rt['stamp']] = $rt;
	}
	writeover(D_P.'data/bbscache/mode_stamp.php',"<?php\r\n\$mode_stamp = ".pw_var_export($mode_stamp)."\r\n?>");
}
function updateBlockCache(){
	global $db;
	$mode_block = array();
	$query = $db->query("SELECT * FROM pw_block ORDER BY function, bid");
	while ($rt = $db->fetch_array($query)) {
		$mode_block[$rt['bid']] = $rt;
	}
	writeover(D_P.'data/bbscache/mode_block.php',"<?php\r\n\$mode_block = ".pw_var_export($mode_block)."\r\n?>");
}

function updateoptimize ($config=array(),$type=1,$o='') {
	foreach ($config as $key => $value) {
		if (strpos($key,'db_') !== false) {
			$optimize = $config;
		} else {
			if (!in_array($key,array('imgsize','imgwidth','imglen'))) {
				$optimize['db_'.$key] = $value;
			}
		}
	}
	$configcache = "<?php\r\n\$optimize_conf=array(\r\n'$o'=>array(\r\n";
	$configcache .= "'$type'=>".pw_var_export($optimize).",\r\n\r\n";
	$configcache .= "),\r\n);\r\n?>";
	pwCache::setData(S::escapePath(D_P."data/bbscache/optimize_{$o}.php"),$configcache);
}

/*缓存配置设置
* @$key => db_name; @value => $db_value;
*/
function setConfig($key, $value, $decrip = null, $hk = false) {
	global $db;
	$vtype = 'string';
	$strip = true;
	if (is_array($value)) {
		$value = serialize($value);
		$vtype = 'array';
		$strip = false;
	}
	$pwSQL = array('vtype' => $vtype);
	isset($decrip) && $pwSQL['decrip'] = $decrip;

	if ($hk) {
		$pwSQL['hk_value'] = $value;
		$db->pw_update(
			"SELECT * FROM pw_hack WHERE hk_name=" . S::sqlEscape($key),
			"UPDATE pw_hack SET " . S::sqlSingle($pwSQL,$strip) . ' WHERE hk_name=' . S::sqlEscape($key),
			"INSERT INTO pw_hack SET " . S::sqlSingle(array_merge(array('hk_name' => $key), $pwSQL),$strip)
		);
	} else {
		$pwSQL['db_value'] = $value;
		$db->pw_update(
			"SELECT * FROM pw_config WHERE db_name=" . S::sqlEscape($key),
			"UPDATE pw_config SET " . S::sqlSingle($pwSQL,$strip) . ' WHERE db_name=' . S::sqlEscape($key),
			"INSERT INTO pw_config SET " . S::sqlSingle(array_merge(array('db_name' => $key), $pwSQL),$strip)
		);
	}
}

function saveConfig(){
	global $db;
	$configdb = array();
	$temppre = array('config' => 'db_', 'ftp' => 'ftp_', 'reg' => 'rg_', 'mail' => 'ml_');
	foreach ($temppre as $key => $pre) {
		if (!isset($GLOBALS[$key])) continue;
		$key_a = $GLOBALS[$key];
		if (is_array($key_a)) {
			foreach ($key_a as $k => $value) {
				$var = $pre . $k;
				$vtype = 'string';
				if (is_array($value)) {
					$vtype = 'array';
					$value = serialize($value);
				}
				$configdb[$var] = array($var, $vtype, $value);
			}
		}
	}
	if (!empty($configdb)) {
		$names = array_keys($configdb);
		$query = $db->query('SELECT db_name,vtype,db_value FROM pw_config WHERE db_name IN (' . S::sqlImplode($names, false) . ')');
		while ($rt = $db->fetch_array($query)) {
			if (isset($configdb[$rt['db_name']])) {
				if ($rt['db_value'] != $configdb[$rt['db_name']]) {
					$db->update("UPDATE pw_config SET " . S::sqlSingle(array(
						'db_value' => $configdb[$rt['db_name']][2], 'vtype' => $configdb[$rt['db_name']][1])) . ' WHERE db_name=' . S::sqlEscape($rt['db_name']));
				}
				unset($configdb[$rt['db_name']]);
			}
		}
		$db->free_result($query);
		$pwSqlMulti = S::sqlMulti($configdb);
		$pwSqlMulti && $db->update('INSERT INTO pw_config (db_name,vtype,db_value) VALUES' . $pwSqlMulti);
		updatecache_c();
		return true;
	} else{
		return false;
	}
}
function getTopictypeCache() {
	global $db;
	$t_typedb = $typedb = $subtypedb = array();
	$query = $db->query("SELECT * FROM pw_topictype ORDER BY vieworder ");
	while ($rt = $db->fetch_array($query)) {
		if ($rt['upid'] == 0) {
			$typedb[$rt['id']] = $rt;
		} else {
			$subtypedb[$rt['upid']][] = $rt;
		}
	}
	foreach ($typedb as $key => $value) {
		$t_typedb[$value['fid']][$key] = $value;
		empty($subtypedb[$value['id']]) && $subtypedb[$value['id']] = array();
		foreach ($subtypedb[$value['id']] as $k => $v) {
			$t_typedb[$v['fid']][$v['id']] = $v;
		}
	}
	return $t_typedb;
}
?>