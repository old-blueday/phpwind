<?php
!function_exists('adminmsg') && exit('Forbidden');

//* @include_once pwCache::getPath(D_P.'data/bbscache/o_config.php');
pwCache::getData(D_P.'data/bbscache/o_config.php');

require_once(A_P . 'lib/colonys.class.php');
$newColony = new PW_Colony();

if (empty($action)) {

	if (empty($_POST['step'])) {

		require_once(R_P.'require/credit.php');
		ifcheck($db_groups_open,'groups_open');
		ifcheck($o_groups_gdcheck,'groups_gdcheck');
		ifcheck($o_groups_p_gdcheck,'groups_p_gdcheck');
		ifcheck($o_groups_qcheck,'groups_qcheck');
		ifcheck($o_groups_p_qcheck,'groups_p_qcheck');
		ifcheck($o_newcolony,'newcolony');
		ifcheck($o_virement,'virement');
		$moneyname = $credit->cType[$o_moneytype];
		$maxuploadsize = @ini_get('upload_max_filesize');

		$creategroup = ''; $num = 0;
		foreach ($ltitle as $key => $value) {
			if ($key != 1 && $key != 2 && $key !='6' && $key !='7') {
				$num++;
				$htm_tr = $num % 4 == 0 ? '' : '';
				$g_checked = strpos($o_groups,",$key,") !== false ? 'checked' : '';
				$creategroup .= "<li><input type=\"checkbox\" name=\"groups[]\" value=\"$key\" $g_checked>$value</li>$htm_tr";
			}
		}
		$creategroup && $creategroup = "<ul class=\"list_A list_120 cc\">$creategroup</ul>";

		$creditset = $o_groups_creditset;
		$creditlog = array();
		foreach ($o_groups_creditlog as $key => $value) {
			foreach ($value as $k => $v) {
				$creditlog[$key][$k] = 'CHECKED';
			}
		}

		require_once PrintApp('admin');

	} else {

		S::gp(array('config','creditset','creditlog'),'GP',0);
		S::gp(array('groups','groups_open'),'GP',2);

		require_once(R_P.'admin/cache.php');
		setConfig('db_groups_open', $groups_open);
		updatecache_c();

		foreach ($config as $key => $value) {
			switch ($key) {
				case 'moneytype':
					$config[$key] = S::escapeChar($value);break;
				case 'rate':
					$config[$key] = (double)$value;break;
				default:
					$config[$key] = (int)$value;
			}
		}
		$config['groups'] = is_array($groups) ? ','.implode(',',$groups).',' : '';

		$updatecache = false;

		$config['groups_creditset'] = array();
		if (is_array($creditset) && !empty($creditset)) {
			foreach ($creditset as $key => $value) {
				foreach ($value as $k => $v) {
					$creditset[$key][$k] = ($v === '') ? (in_array($key, array('Post','Reply','Delete','Deleterp')) ? '' : 0) : round($v, ($k=='rvrc' ? 1 : 0));
				}
			}
			$config['groups_creditset'] = $creditset;
		}
		$config['groups_creditlog'] = (is_array($creditlog) && !empty($creditlog)) ? $creditlog : array();

		foreach ($config as $key => $value) {
			setConfig("o_$key", $value, null, true);
		}
		updatecache_conf('o', true);
		adminmsg('operate_success',$j_url);
	}

} elseif ($action == 'setting') {

	!is_array($config = $_POST['config']) && $config = array();
	foreach ($config as $key => $value) {
		if ($value) {
			$isint = false;
			if ($_POST['step'] == 'basic') {
				if ($key == 'name' || $key == 'moneytype') {
					$config[$key] = S::escapeChar($value);
				} elseif ($key == 'rate') {
					$config[$key] = (double)$value;
				} else {
					$isint = true;
				}
			} else {
				$isint = true;
			}
			$isint && $config[$key] = (int)$value;
		}
	}
	if ($_POST['step'] == 'basic') {
		!is_array($groups = $_POST['groups']) && $groups = array();
		$config['groups'] = ','.implode(',',$groups).',';
	}
	$updatecache = false;
	foreach ($config as $key => $value) {
		if (${'cn_'.$key} != $value) {
			$db->pw_update(
				"SELECT hk_name FROM pw_hack WHERE hk_name=" . S::sqlEscape("cn_$key"),
				"UPDATE pw_hack SET hk_value=" . S::sqlEscape($value) . "WHERE hk_name=" . S::sqlEscape("cn_$key"),
				"INSERT INTO pw_hack SET hk_name=" . S::sqlEscape("cn_$key") . ",hk_value=" . S::sqlEscape($value)
			);
			$updatecache = true;
		}
	}
	$j_url = '';
	if ($_POST['step'] == 'updatecache') {
		$updatecache = true;
		$j_url = "$basename&action=cache";
	} elseif ($_POST['step'] == 'photo') {
		$j_url = "$basename&action=photo";
	}
	$updatecache && updatecache_cnc();
	adminmsg('operate_success',$j_url);
	
} elseif ($action == 'thread') {

	S::gp(array('cyid'));

	if ($_POST['step'] == 'updatecache') {

		$j_url = "$basename&action=cache";
		$cyid = (int)$cyid;
		!$cyid && adminmsg('illegal_group_cyid',$j_url);
		require_once(R_P . 'apps/groups/lib/colony.class.php');
		$newColony = new PwColony($cyid);
		$colony = $newColony->getInfo();
		$count = $newColony->getArgumentCount();
		if ($count != $colony['tnum']) {
			$newColony->updateInfoCount(array('tnum' => $count));	
		}	
		adminmsg('operate_success',$j_url);	
	}

} elseif ($action == 'class') {

	$classdb = $isclass = array();
	$query = $db->query("SELECT * FROM pw_cnclass");
	while ($rt = $db->fetch_array($query)) {
		$classdb[$rt['fid']] = $rt;
		if ($rt['ifopen']) {
			$isclass[] = $rt['fid'];
		}
	}

	if (empty($_POST['step'])) {

		$o_classdb = array();
		foreach ($forum as $key => $value) {
			if ($value['type'] == 'forum' && !$value['cms'] && isset($forum[$value['fup']]) && $forum[$value['fup']]['type'] == 'category') {
				$o_classdb[$value['fup']][] = $value['fid'];
			}
		}
		require_once PrintApp('admin');

	} else {

		S::gp(array('selid','cname'));
		empty($selid) && $selid = array();
		empty($isclass) && $isclass = array();
		if ($delclass = array_diff($isclass, $selid)) {
			$db->update("UPDATE pw_cnclass SET ifopen=0,cname='' WHERE fid IN (" . S::sqlImplode($delclass) . ')');
		}
		if ($addclass = array_diff($selid, $isclass)) {
			$pwSQL = array();
			foreach ($addclass as $key => $value) {
				!$cname[$value] && $cname[$value] = strip_tags($forum[$value]['name']);
				$pwSQL[] = array($value,$cname[$value],1);
			}
			$db->update("REPLACE INTO pw_cnclass (fid,cname,ifopen) VALUES " . S::sqlMulti($pwSQL));
		}
		if ($upclass = array_intersect($selid,$isclass)) {
			foreach ($upclass as $key => $value) {
				!$cname[$value] && $cname[$value] = strip_tags($forum[$value]['name']);
				if ($cname[$value] != $classdb[$value]['cname']) {
					$db->update("UPDATE pw_cnclass SET cname=" . S::sqlEscape($cname[$value]) . ' WHERE fid=' . S::sqlEscape($value));
				}
			}
		}
		updatecache_cnc();
		adminmsg('operate_success', "$basename&action=class");

	}
} elseif ($action == 'credit') {

	S::gp(array('fid'));
	$f = $db->get_one("SELECT creditset FROM pw_cnclass WHERE fid=" . S::sqlEscape($fid));
	!$f && adminmsg('operate_fail');

	require_once(R_P . 'require/credit.php');

	if (empty($_POST['step'])) {

		$creditset = unserialize($f['creditset']);
		require_once PrintApp('admin');

	} else {

		S::gp(array('creditset'), 'P');

		foreach ($creditset as $key => $value) {
			foreach ($value as $k => $v) {
				if (is_numeric($v)) {
					$creditset[$key][$k] = round($v, $k == 'rvrc' ? 1 : 0);
				} else {
					$creditset[$key][$k] = '';
				}
			}
		}
		$creditset = $creditset ? serialize($creditset) : '';
		$db->update("UPDATE pw_cnclass SET creditset=" . S::sqlEscape($creditset) . ' WHERE fid=' . S::sqlEscape($fid));

		adminmsg('operate_success', "$basename&action=credit&fid=$fid");
	}

} elseif ($action == 'colony') {

	if (empty($_POST['step'])) {

		S::gp(array('cid', 'level', 'cname', 'admin', 'firstgradestyle', 'secondgradestyle','styleid'));

		!is_array($o_classdb) && $o_classdb = array();
		$sqladd = $pageadd = '';

		if ($styleid) {
			$firstgradestyle = $styleid;
		} else {
			$styleid = 0;
		}
		
		if ($o_styledb && $firstgradestyle) {
			if (isset($o_styledb[$secondgradestyle])) {
				$styleid = $secondgradestyle;
			} elseif (isset($o_styledb[$firstgradestyle])) {
				$styleid = $firstgradestyle;
			}
		}
				
		if ($styleid) {
			$pageadd .= "&styleid=$styleid";
			if (!$secondgradestyle) {
				$subStyle = $o_style_relation[$styleid] ? $o_style_relation[$styleid] : array($styleid);				
				$sqladd .= ' AND styleid IN('.S::sqlImplode($subStyle).')';
			} else {
				$sqladd .= ' AND styleid='.S::sqlEscape($styleid);
			}
		}
		if ($cid) {
			$pageadd .= "&cid=$cid";
			$sqladd .= ' AND classid='.S::sqlEscape($cid > 0 ? $cid : 0);
		}
		if ($level) {
			if (isset($o_groups_levelneed[$level])) {
				$sqladd .= ' AND commonlevel=' . S::sqlEscape($level) . ' AND speciallevel=0';
			} elseif (isset($o_groups_level[$level])) {
				$sqladd .= ' AND speciallevel=' . S::sqlEscape($level);
			} elseif ($level == '-1') {
				$sqladd .= " AND speciallevel='0'";
			}
			$pageadd .= '&level='.$level;
		}

		$cname = trim($cname);
		$admin = trim($admin);
		if ($cname != '') {
			$pageadd .= '&cname='.rawurlencode($cname);
			if (strpos($cname,'*') !== false) {
				$sql_cname = addslashes(str_replace('*','%',$cname));
				$sqladd .= ' AND cname LIKE '.S::sqlEscape($sql_cname);
			} else {
				$sql_cname = addslashes($cname);
				$sqladd .= '  AND cname = '.S::sqlEscape($sql_cname);
			}
		}
		if ($admin != '') {
			$pageadd .= '&admin='.rawurlencode($admin);
			if (strpos($admin,'*') !== false) {
				$sql_admin = addslashes(str_replace('*','%',$admin));
				$sqladd .= ' AND admin LIKE '.S::sqlEscape($sql_admin);
			} else {
				$sql_admin = addslashes($admin);
				$sqladd .= '  AND admin = '.S::sqlEscape($sql_admin);
			}
		}

		$options = "<option value=\"0\">不限制</option><option value=\"-1\"" . ($cid == -1 ? ' selected' : '') . ">不关联</option>";
		foreach ($o_classdb as $key => $value) {
			$options .= "<option value=\"$key\"" . ($key == $cid ? ' selected' : '') . ">$value</option>";
		}

		$leveloptions = '';
		foreach ($o_groups_level as $key => $value) {
			$leveloptions .= "<option value=\"$key\"" . ($key == $level ? ' selected' : '') . ">$value</option>";
		}
		
		$pages = '';
		$db_perpage = 20;
		$colonys = array();
		S::gp(array('page'),'GP',2);
		$page < 1 && $page = 1;
		$id = ($page-1) * $db_perpage;
			//var_dump("SELECT id,cname,admin,classid,commonlevel,speciallevel,ifshow,ifshowpic,vieworder,styleid FROM pw_colonys WHERE 1 " . $sqladd ." order by id desc ". S::sqlLimit($id,$db_perpage));exit;	
		$query = $db->query("SELECT id,cname,admin,classid,commonlevel,speciallevel,ifshow,ifshowpic,vieworder,styleid FROM pw_colonys WHERE 1 " . $sqladd ." order by id desc ". S::sqlLimit($id,$db_perpage));
		while ($rt = $db->fetch_array($query)) {
			$rt['speciallevel'] == 0 && $rt['speciallevel'] = $rt['commonlevel'];
			$rt['cname'] = trim($rt['cname']);
			$rt['classname'] = $rt['classid'] ? $o_classdb[$rt['classid']] : '';
			$colonys[] = $rt;
		}
		$db->free_result($query);
		$count = $db->get_value('SELECT COUNT(*) FROM pw_colonys WHERE 1' . $sqladd);
		if ($count > $db_perpage) {
			$pages = numofpage($count,$page,ceil($count/$db_perpage),"$basename&action=colony$pageadd&");
		}

		$jsStyle = pwJsonEncode($o_styledb);
		$jsStyleRelation = pwJsonEncode($o_style_relation);

		require_once PrintApp('admin');

	} else {

		S::gp(array('ifshow','selid','ids','ifshowpic','vieworder'));

		$basename .= '&action=colony';

		is_array($ifshow) || $ifshow = array();
		is_array($vieworder) || $vieworder = array();
		foreach ($ids as $key =>$id) {
			//* $db->update("UPDATE pw_colonys SET ifshow=".S::sqlEscape($ifshow[$id]).",vieworder=".S::sqlEscape($vieworder[$id]).",ifshowpic=".S::sqlEscape($ifshowpic[$id])." WHERE id=".S::sqlEscape($id));
			$db->update(pwQuery::buildClause("UPDATE :pw_table SET ifshow=:ifshow,vieworder=:vieworder,ifshowpic=:ifshowpic WHERE id=:id", array('pw_colonys', $ifshow[$id],$vieworder[$id], $ifshowpic[$id],$id)));
		}


		adminmsg('operate_success');
	}

} elseif ($action == 'editcolony') {

	require_once(R_P. 'require/credit.php');
	require_once(R_P . 'apps/groups/lib/colony.class.php');
	S::gp(array('cyid'));
	$colony = $db->get_one("SELECT * FROM pw_colonys WHERE id=" . S::sqlEscape($cyid));
	!$colony && adminmsg('undefined_action');

	if (empty($_POST['step'])) {

		is_array($creditset = unserialize($colony['creditset'])) || $creditset = array();
		list($colony['cnimg']) =PwColony::getColonyCnimg($colony['cnimg']);
		$filetype = (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype));
		$default_type = array('gif','jpg','jpeg','bmp','png');
		foreach ($default_type as $value) {
			$cnimg_1[$value] = $o_imgsize ? $o_imgsize : $filetype[$value];
			$cnimg_2[$value] = 2048;
		}
		!$colony['colonystyle'] && $colony['colonystyle'] = 'skin_default';
		$colony['banner'] && list($colony['banner']) = geturl("cn_img/{$colony['banner']}",'lf');
		$set_banner = $colony['banner'] ? $colony['banner'] : $imgpath . '/g/' . $colony['colonystyle'] . '/preview.jpg';
		$options = '<option value="0"' . ($colony['classid'] ? '' : ' selected') . '>不关联</option>';
		foreach ($o_classdb as $key => $value) {
			$options .= "<option value=\"$key\"" . ($key == $colony['classid'] ? ' selected' : '') . ">$value</option>";
		}

		ifcheck($colony['iftopicshowinforum'], 'iftopicshowinforum');
		$ifcheck_0 = $ifcheck_1 = $ifcheck_2 = $ifopen_Y = $ifopen_N = $albumopen_Y = $albumopen_N = '';

		$colony['ifcheck'] = $colony['ifcheck'] ? $colony['ifcheck'] : '2';
		${'ifcheck_'.$colony['ifcheck']} = 'selected';

		$leveloptions = '';
		foreach ($o_groups_level as $key => $value) {
			if (!isset($o_groups_levelneed[$key])) {
				$leveloptions .= "<option value=\"$key\"" . ($key == $colony['speciallevel'] ? ' selected' : '') . ">$value</option>";
			}
		}
		$jsStyle = pwJsonEncode($o_styledb);
		$jsStyleRelation = pwJsonEncode($o_style_relation);
	
		$cnclass['fid'] = $db->get_value("SELECT fid FROM pw_cnclass WHERE fid=" . S::sqlEscape($colony['classid']). " AND ifopen=1");
		if (!$cnclass['fid']) {
			$cidDisplay = "display:none";
			$viewtype_2 = 'checked';
		} else {
			$cidDisplay = '';
			${'viewtype_'.$colony['viewtype']} = 'checked';
		}
		require_once PrintApp('admin');

	} else {
		$basename .= "&action=$action&cyid=$cyid";
		S::gp(array('cname','annouce','descrip','admin','firstgradestyle','secondgradestyle','viewtype','speciallevel','q_1','q_2'),'P');
		S::gp(array('iftopicshowinforum'), '', 2);
		!$cname && Showmsg('colony_emptyname');
		strlen($cname) > 50 && Showmsg('colony_cnamelimit_admin');
		strlen($descrip) > 255 && Showmsg('colony_descriplimit');
		$styleid = 0;
		if ($o_styledb) {
			if (!isset($o_style_relation[$firstgradestyle])) {
				Showmsg('请选择分类!');
			}
			if (empty($o_style_relation[$firstgradestyle])) {
				$styleid = $firstgradestyle;
			} else {
				!in_array($secondgradestyle, $o_style_relation[$firstgradestyle]) && Showmsg('请选择二级分类!');
				$styleid = $secondgradestyle;
			}
		}
		require_once(R_P.'require/bbscode.php');
		$wordsfb = L::loadClass('FilterUtil', 'filter');
		foreach (array($cname, $annouce, $descrip) as $key => $value) {
			if (($banword = $wordsfb->comprise($value)) !== false) {
				Showmsg('content_wordsfb');
			}
		}
		strlen($annouce) > 50000 && Showmsg('colony_annoucelimit');
		$annouce = explode("\n",$annouce,5);
		end($annouce);
		$annouce[key($annouce)] = str_replace(array("\r","\n"),'',current($annouce));
		$annouce = implode("\r\n",$annouce);

		if ($colony['cname'] != stripcslashes($cname) && $db->get_value("SELECT id FROM pw_colonys WHERE cname=" . S::sqlEscape($cname))) {
			Showmsg('colony_samename');
		}
		S::gp(array('cid','ifcheck'), 'P', 2);
		$pwSQL = array(
			'cname'			=> $cname,
			'ifcheck'		=> $ifcheck,
			'annouce'		=> $annouce,
			'descrip'		=> $descrip,
			'speciallevel'	=> $speciallevel,
			'viewtype'		=> $viewtype,
			'iftopicshowinforum' => $iftopicshowinforum
		);

		require_once(R_P . 'apps/groups/lib/imgupload.class.php');
		if (empty($q_1)) {
			$img = new CnimgUpload($cyid);
			PwUpload::upload($img);
			pwFtpClose($ftp);
			if ($cnimg = $img->getImgUrl()) {
				$pwSQL['cnimg'] = substr(strrchr($cnimg,'/'),1);			
			}
		} else {
			$pwSQL['cnimg'] = '';
		}	
		if (empty($q_2)) {
			$banner = new BannerUpload($cyid);
			PwUpload::upload($banner);
			pwFtpClose($ftp);
			if ($cnbanner = $banner->getImgUrl()) {
				$pwSQL['banner'] = substr(strrchr($cnbanner,'/'),1);
			}
		} else {
			$pwSQL['banner'] = '';
		}

		if ($admin != $colony['admin']) {
			$rt = $db->get_one("SELECT m.uid,c.id AS ifcyer FROM pw_members m LEFT JOIN pw_cmembers c ON m.uid=c.uid AND c.colonyid=" . S::sqlEscape($cyid) . " WHERE m.username=" . S::sqlEscape($admin));
			if (empty($rt)) {
				$errorname = $admin;
				adminmsg('user_not_exists');
			}
			if ($rt['ifcyer']) {
				//* $db->update("UPDATE pw_cmembers SET ifadmin=1 WHERE colonyid=" . S::sqlEscape($cyid) . ' AND uid=' . S::sqlEscape($rt['uid']));
				pwQuery::update('pw_cmembers', 'colonyid=:colonyid AND uid=:uid', array($cyid, $rt['uid']), array('ifadmin'=>1));
			} else {
				/**
				$db->update("INSERT INTO pw_cmembers SET " . S::sqlSingle(array(
					'uid' => $rt['uid'],
					'username' => $admin,
					'ifadmin' => 1,
					'colonyid' => $cyid,
					'addtime' => $timestamp
				)));
				**/
				pwQuery::insert('pw_cmembers', array(
					'uid' => $rt['uid'],
					'username' => $admin,
					'ifadmin' => 1,
					'colonyid' => $cyid,
					'addtime' => $timestamp
				));
			}
			$pwSQL['admin'] = $admin;
		}
		require_once(A_P . 'lib/colonys.class.php');
		$colonyServer = new PW_Colony();		
		if ($cid != $colony['classid']) {
			$cid = isset($o_classdb[$cid]) ? $cid : 0;
			$colonyServer->changeTopicToForum($cyid, $iftopicshowinforum, $cid, $colony['classid']);
			$pwSQL['classid'] = $cid;
		} elseif ($iftopicshowinforum != $colony['iftopicshowinforum'] && $colony['classid'] > 0) {
			$colonyServer->changeTopicShowInForum($cyid, $iftopicshowinforum, $colony['classid']);
		}
		$pwSQL['styleid'] = $styleid;
		//* $db->update("UPDATE pw_colonys SET " . S::sqlSingle($pwSQL) . ' WHERE id=' . S::sqlEscape($cyid));
		pwQuery::update('pw_colonys', 'id=:id', array($cyid), $pwSQL);
		require_once(R_P .'u/require/core.php');
		updateGroupLevel($cyid, $colony);		
		adminmsg('operate_success',"$basename&action=editcolony");
	}
} elseif ($action == 'mergecolony') {

	if (empty($_POST['step'])) {

		require_once PrintApp('admin');

	} else {

		$basename = $basename.'&action=mergecolony';
		S::gp(array('fromcname','tocname'), '');
		require_once(A_P . 'lib/colony.class.php');
		require_once(A_P . 'lib/colonys.class.php');
		$colonyServer = new PW_Colony();
		if (!$colony = $colonyServer->getColonyByName($fromcname)) {
			adminmsg('源群组不存在!');
		}
		if (!$toColony = $colonyServer->getColonyByName($tocname)) {
			adminmsg('目标群组不存在!');;
		}
		if (PwColony::calculateCredit($colony) > PwColony::calculateCredit($toColony)) {
			Showmsg('只允许群积分低的群组并入群积分高的群组!');
		}
		$colonyServer->mergeColony($toColony['id'], $colony['id']);

		adminmsg('operate_success',"$basename&action=mergecolony");
	}

} elseif ($action == 'delcolony') {

	S::gp(array('cyid'),'',2);
	$rt = $db->get_one("SELECT classid,cnimg FROM pw_colonys WHERE id=" . S::sqlEscape($cyid));
	if (!empty($rt)) {
		Delcnimg($rt['cnimg']);
		pwFtpClose($ftp);
		//updateUserAppNum($rt['uid'],'group','recount');
		$db->update("UPDATE pw_cmembers a LEFT JOIN pw_ouserdata o ON a.uid=o.uid SET o.groupnum=o.groupnum-1 WHERE a.colonyid=" . S::sqlEscape($cyid) . ' AND o.groupnum>0');
		$db->update("DELETE FROM pw_argument WHERE cyid=" . S::sqlEscape($cyid));
		$db->update("DELETE FROM pw_cmembers WHERE colonyid=" . S::sqlEscape($cyid));
		//* $db->update("DELETE FROM pw_colonys  WHERE id=" . S::sqlEscape($cyid));
		pwQuery::delete('pw_colonys', 'id=:id', array($cyid));
		$db->update("UPDATE pw_cnclass SET cnsum=cnsum-1 WHERE fid=" . S::sqlEscape($rt['classid']) . ' AND cnsum>0');
	}
	adminmsg('operate_success',"$basename&action=colony");

} elseif ($action == 'log') {

	if ($_POST['step'] != 'del') {

		require_once GetLang('logtype');
		S::gp(array('keyword','page'));
		$db_perpage = 20;
		$logdb = array();
		$pages = $sqladd = $addpages = '';
		if ($keyword) {
			$sqladd = " AND descrip LIKE ".S::sqlEscape("%$keyword%");
			$addpages = "&keyword=".rawurlencode($keyword);
		}
		(int)$page<1 && $page = 1;
		$id = ($page-1)*$db_perpage;
		$query = $db->query("SELECT id,type,field2,field3,username1,timestamp,descrip FROM pw_forumlog WHERE type LIKE 'cy\_%' $sqladd".S::sqlLimit($id,$db_perpage));
		while ($rt = $db->fetch_array($query)) {
			$rt['timestamp'] = get_date($rt['timestamp']);
			$rt['descrip'] = str_replace(array('[b]','[/b]'),array('<b>','</b>'),$rt['descrip']);
			$logdb[] = $rt;
		}
		$db->free_result($query);
		$count = $db->get_value("SELECT COUNT(*) FROM pw_forumlog WHERE type LIKE 'cy\_%' $sqladd");
		if ($count > $db_perpage) {
			require_once(R_P.'require/forum.php');
			$pages = numofpage($count,$page,ceil($count/$db_perpage),"$basename&action=log$addpages&");
		}
		require_once PrintApp('admin');

	} else {

		S::gp(array('selid'),'P',1);
		if (!($selid = checkselid($selid))) {
			$basename = 'javascript:history.go(-1);';
			adminmsg('operate_error');
		}
		$selid && $db->update("DELETE FROM pw_forumlog WHERE type LIKE 'cy\_%' AND id IN($selid)");
		adminmsg('operate_success',"$basename&action=log");
	}
} elseif ($action == 'cache') {

	if (empty($_POST['step'])) {
		
		require_once PrintApp('admin');

	} elseif ($_POST['step'] == 'updatecache') {
		S::gp('cyid');
		$db->update("UPDATE pw_cnclass SET cnsum='0'");
		$query = $db->query("SELECT classid,COUNT(*) AS sum FROM pw_colonys WHERE classid>0 GROUP BY classid");
		while ($rt = $db->fetch_array($query)) {
			$db->update("UPDATE pw_cnclass SET cnsum=" . S::sqlEscape($rt['sum']) . ' WHERE fid=' . S::sqlEscape($rt['classid']));
		}
		$j_url = "$basename&action=cache";
		adminmsg('operate_success',$j_url);

	} elseif ($_POST['step'] == 'delcolony') {

		$query = $db->query("SELECT id,cnimg FROM pw_colonys WHERE classid<1");
		while ($rt = $db->fetch_array($query,MYSQL_NUM)) {
			Delcnimg($rt[1]);
			$db->update("UPDATE pw_cmembers a LEFT JOIN pw_ouserdata o ON a.uid=o.uid SET o.groupnum=o.groupnum-1 WHERE a.colonyid=" . S::sqlEscape($rt[0],false) . ' AND o.groupnum>0');
			$db->update("DELETE FROM pw_argument WHERE cyid=" . S::sqlEscape($rt[0],false));
			$db->update("DELETE FROM pw_cmembers WHERE colonyid=" . S::sqlEscape($rt[0],false));
			//* $db->update("DELETE FROM pw_colonys  WHERE id=" . S::sqlEscape($rt[0],false));
			pwQuery::delete('pw_colonys', 'id=:id', array($rt[0]));
		}
		pwFtpClose($ftp);
		adminmsg('operate_success',"$basename&action=cache");
	}
} elseif ($action == 'level') {

	require_once(A_P . 'action/admin_level.php');

} elseif ($action == 'style') {

	S::gp(array('job'));
	require_once(R_P.'apps/groups/lib/groupstyle.class.php');
	$groupStyle = new GroupStyle();
	$allStyles = $allStylesOfOpen = array();
	$allStyles	= $groupStyle -> getAllStyles();
	$openStyles	= $groupStyle -> getOpenStyles();
	$firstGradeStyleIds	= $groupStyle -> getFirstGradeStyleIds();
	$secondGradeStyles  = $groupStyle -> getGradeStylesByUpid($firstGradeStyleIds);

	if (empty($job)) { //列表管理

		if (empty($_POST['step'])) {

			require_once PrintApp('admin');

		} else {

			S::gp(array('selid','cname','delid','new_t_sub_db', 'new_t_sub_view_db', 'vieworder'));

			$newSubStyle = array();

			if ($cname) {
				foreach ($cname as $value) {
					if (!$value) {
						adminmsg('群组分类不能为空', "$basename&action=style");exit;
					}
				}
			}
			
			//更新开启状态
			empty($selid) && $selid = array();
			$db->update("UPDATE pw_cnstyles SET ifopen=0");
			if($selid) {
				$db->update("UPDATE pw_cnstyles SET ifopen=1 WHERE id IN (" . S::sqlImplode($selid) . ')');
			}
			//更新分类名称
						
			foreach ($allStyles as $key => $value) {
				if ($value['cname'] != $cname[$key] || $value['vieworder'] != $vieworder[$key]) {
					$db->update('UPDATE pw_cnstyles SET cname=' . S::sqlEscape($cname[$key]) . ', vieworder = ' . S::sqlEscape($vieworder[$key]) . ' WHERE id=' . S::sqlEscape($key));
				}
			}

			//删除分类
			if ($delid) {
				//组合需要删除的分类的ID
				
				foreach ($delid as $id) {		
					if ($allStyles[$id]['upid'] == 0) {
						foreach ($allStyles as $tempStyle){
							if ($tempStyle['upid'] == $id) {
								$delids[$id][] = $tempStyle['id'];
							}
						}
					}
					!$delids && $tmpdelids[$id][] = $id;
				}
				$delids = $delids ? $delids : $tmpdelids;
				foreach ($delids as $key=>$ids) {
					foreach ($ids as $id) {
						$cname  = $db->get_value("SELECT cname FROM pw_colonys WHERE styleid =" . S::sqlEscape($id));
						$cname && adminmsg("群组:{$cname} 下有该分类:{$allStyles[$id][cname]}存在，不可删除", "$basename&action=style");
						$db->query("DELETE FROM pw_cnstyles WHERE id = " . S::sqlEscape($id));
					}
					$ucname  = $db->get_value("SELECT cname FROM pw_colonys WHERE styleid =" . S::sqlEscape($key));
					
					$ucname && adminmsg("群组:{$ucname} 下有该分类:{$allStyles[$key][cname]}存在，不可删除", "$basename&action=style");
					$db->query("DELETE FROM pw_cnstyles WHERE id = " . S::sqlEscape($key));
				}
//				$db->query("UPDATE pw_colonys SET styleid = 0 WHERE styleid IN(" . S::sqlImplode($delids) . ')');
//				$db->query("DELETE FROM pw_cnstyles WHERE id IN (" . S::sqlImplode($delids) . ')');
			}

			//添加新的二级分类
			if (!empty($new_t_sub_db)) {
				foreach ($new_t_sub_db as $k => $v) {
					foreach($v as $kk => $vv) {
						if (empty($vv)) continue;
						$newSubStyle[] = array($vv,1,$k,$new_t_sub_view_db[$k][$kk]);
					}
				}
				if ($newSubStyle) {
					$groupStyle->addNewSubStyle($newSubStyle);
				}
			}
			updatecache_cnc_s();
			adminmsg('operate_success', "$basename&action=style");
		}

	} else { //添加新分类

		S::gp(array('cname'));
		!$cname && adminmsg('群组分类不能为空', "$basename&action=style");
		$count = $db->get_one("SELECT COUNT(*) AS count FROM pw_cnstyles WHERE cname=" . S::sqlEscape($cname) . " AND upid=0");
		if ($count['count'] == 0) {
			$db->query("INSERT INTO pw_cnstyles(cname,ifopen,csum) VALUES ('$cname','1','0')");
			updatecache_cnc_s();
			adminmsg('operate_success', "$basename&action=style");
		} else {
			adminmsg('已有该分类', "$basename&action=style");
		}
	}
} elseif ($action == 'colonystyle') {

	if (empty($_POST['step'])) {

		$dir = $imgdir.'/g/';
		$files1 = opendir($dir);
		$array = array();
		while ($skinfile = readdir($files1)) {
			if (strstr($skinfile, 'skin_')) {
				$array[] = $skinfile;
			}
		}
		$names = array();
		$query = $db->query("SELECT * FROM pw_cnskin");
		while ($rt = $db->fetch_array($query)) {
			$names[$rt['dir']] = $rt['name'];
		}

		require_once PrintApp('admin');

	} else {

		S::gp(array('name','style_name'));

		$db->update("DELETE FROM pw_cnskin");

		$pwSQL = array();
		foreach ($name as $key => $value) {
			$pwSQL[] = array($value, $style_name[$key]);
		}
		$db->update("REPLACE INTO pw_cnskin (dir, name) VALUES " . S::sqlMulti($pwSQL));

		adminmsg('operate_success',"$basename&action=colonystyle");
	}
}

function Delcnimg($filename) {
	return pwDelatt("cn_img/$filename",$GLOBALS['db_ifftp']);
}

function updatecache_cnc() {
	global $db;
	$classdb = array();
	$query = $db->query('SELECT fid,cname FROM pw_cnclass WHERE ifopen=1');
	while ($rt = $db->fetch_array($query)) {
		$classdb[$rt['fid']] = $rt['cname'];
	}
	$classdb = serialize($classdb);
	$db->pw_update(
		"SELECT hk_name FROM pw_hack WHERE hk_name='o_classdb'",
		'UPDATE pw_hack SET ' . S::sqlSingle(array('hk_value' => $classdb, 'vtype' => 'array')) . " WHERE hk_name='o_classdb'",
		'INSERT INTO pw_hack SET ' . S::sqlSingle(array('hk_name' => 'o_classdb', 'vtype' => 'array', 'hk_value' => $classdb))
	);
	updatecache_conf('o',true);
}

function updatecache_cnc_s() {
	global $db;
	$styledb = $style_relation = array();
	$query = $db->query('SELECT id,cname,upid FROM pw_cnstyles WHERE ifopen=1 ORDER BY upid ASC,vieworder ASC');
	while ($rt = $db->fetch_array($query)) {
		$styledb[$rt['id']] = array(
			'cname'	=> $rt['cname'],
			'upid'	=> $rt['upid']
		);
		if ($rt['upid']) {
			$style_relation[$rt['upid']][] = $rt['id'];
		} else {
			$style_relation[$rt['id']] = array();
		}
	}
	$styledb = serialize($styledb);
	$style_relation = serialize($style_relation);
	$db->pw_update(
		"SELECT hk_name FROM pw_hack WHERE hk_name='o_styledb'",
		'UPDATE pw_hack SET ' . S::sqlSingle(array('hk_value' => $styledb, 'vtype' => 'array')) . " WHERE hk_name='o_styledb'",
		'INSERT INTO pw_hack SET ' . S::sqlSingle(array('hk_name' => 'o_styledb', 'vtype' => 'array', 'hk_value' => $styledb))
	);
	$db->pw_update(
		"SELECT hk_name FROM pw_hack WHERE hk_name='o_style_relation'",
		'UPDATE pw_hack SET ' . S::sqlSingle(array('hk_value' => $style_relation, 'vtype' => 'array')) . " WHERE hk_name='o_style_relation'",
		'INSERT INTO pw_hack SET ' . S::sqlSingle(array('hk_name' => 'o_style_relation', 'vtype' => 'array', 'hk_value' => $style_relation))
	);
	updatecache_conf('o',true);
}
?>