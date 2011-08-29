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
		if ($cname) {
			$pageadd .= '&cname=$cname';
			$sql_cname = str_replace('*','%',$cname);
			$sqladd .= ' AND cname LIKE '.S::sqlEscape($sql_cname);
		}
		if ($admin) {
			$pageadd .= '&admin=$admin';
			$sql_admin = str_replace('*','%',$admin);
			$sqladd .= ' AND admin LIKE '.S::sqlEscape($sql_admin);
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
			pwQuery::update('pw_colonys', 'id=:id', array($id), array('ifshow'=>$ifshow[$id], 'vieworder'=>$vieworder[$id], 'ifshowpic'=>$ifshowpic[$id]));
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
		strlen($cname) > 20 && Showmsg('colony_cnamelimit');
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
} elseif ($action == 'argument') {

	S::gp(array('step'));

	if ($step == 'delete') {

		S::gp(array('ttype', 'delid', 'ttable', 'ptable'));
		require_once(R_P.'require/updateforum.php');
		if ($ttype == '1') {

			!$delid && adminmsg('operate_error');
			$pw_tmsgs = 'pw_tmsgs' . ($ttable > 0 ? intval($ttable) : '');
			$fidarray = $delaids = $specialdb = array();
			$tmpDelids = $delid;
			$delids = S::sqlImplode($delid);
			$tnum = array();
			$pnum = array();
			/**
			* 删除帖子
			*/
			$db_guestread && require_once(R_P.'require/guestfunc.php');
			$ptable_a = $delnum = array();
			$query = $db->query("SELECT t.tid,t.fid,t.authorid,t.replies,t.postdate,t.special,t.ptable,tm.aid,t.ifupload FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid WHERE t.tid IN($delids)");
			while (@extract($db->fetch_array($query))) {
				if (!in_array($fid, $fidarray)) {
					$fidarray[] = $fid;
				}
				$delnum[$authorid]++;
				$ptable_a[$ptable] = 1;
				if ($aid) {
					$attachs = unserialize(stripslashes($aid));
					foreach ($attachs as $key => $value) {
						is_numeric($key) && $delaids[] = $key;
						P_unlink("$attachdir/$value[attachurl]");
						$value['ifthumb'] && P_unlink("$attachdir/thumb/$value[attachurl]");
					}
				}
				switch ($special) {
					case 1:
					case 2:
					case 3:
					case 4:
						$specialdb[$special][] = $tid;break;
				}
				$pw_posts = GetPtable($ptable);
				if ($ifupload) {
					$query2 = $db->query("SELECT aid FROM $pw_posts WHERE tid=".S::sqlEscape($tid));
					while (@extract($db->fetch_array($query2))) {
						if ($aid) {
							$attachs = unserialize(stripslashes($aid));
							foreach ($attachs as $key => $value) {
								is_numeric($key) && $delaids[] = $key;
								P_unlink("$attachdir/$value[attachurl]");
								$value['ifthumb'] && P_unlink("$attachdir/thumb/$value[attachurl]");
							}
						}
					}
				}
				$htmurl = $db_readdir.'/'.$fid.'/'.date('ym',$postdate).'/'.$tid.'.html';
				if (file_exists(R_P.$htmurl)) {
					P_unlink(R_P.$htmurl);
				}
				$db_guestread && clearguestcache($tid,$replies);

				//统计用户的回复数
				$query3 = $db->query("SELECT authorid FROM $pw_posts WHERE tid=".S::sqlEscape($tid));
				while ($rt3 = $db->fetch_array($query3)) {
					$delnum[$rt3['authorid']]++;
					$pnum[$tid]++;
				}
				$tnum[$tid]++;

				//统计群组的主题数、回复数、今天发帖数
				$clonys = array();
				$cyid = $db->get_value( " SELECT cyid FROM pw_argument WHERE tid=". S::sqlEscape($tid));
				$colonys[$cyid]['tnum'] = $tnum[$tid];
				$colonys[$cyid]['pnum'] = $pnum[$tid] + $tnum[$tid];
				$colonys[$cyid]['todaypost'] = $colonys[$cyid]['pnum'];

			}

			if (isset($specialdb[1])) {
				$pollids = S::sqlImplode($specialdb[1]);
				$db->update("DELETE FROM pw_polls WHERE tid IN($pollids)");
			}
			if (isset($specialdb[2])) {
				$actids = S::sqlImplode($specialdb[2]);
				$db->update("DELETE FROM pw_activity WHERE tid IN($actids)");
				$db->update("DELETE FROM pw_actmember WHERE actid IN($actids)");
			}
			if (isset($specialdb[3])) {
				$rewids = S::sqlImplode($specialdb[3]);
				$db->update("DELETE FROM pw_reward WHERE tid IN($rewids)");
			}
			if (isset($specialdb[4])) {
				$tradeids = S::sqlImplode($specialdb[4]);
				$db->update("DELETE FROM pw_trade WHERE tid IN($tradeids)");
			}
			if ($delaids) {
				$delaids = S::sqlImplode($delaids);
				$db->update("DELETE FROM pw_attachs WHERE aid IN($delaids)");
			}

			# $db->update("DELETE FROM pw_threads WHERE tid IN ($delids)");
			# ThreadManager
            //* $threadManager = L::loadClass("threadmanager", 'forum');
			//* $threadManager->deleteByThreadIds($fid,$delids);
			$threadService = L::loadclass('threads', 'forum');
			$threadService->deleteByThreadIds($tmpDelids);	
			Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));			

			$db->update("DELETE FROM pw_argument WHERE tid IN($delids)");

			require_once(R_P .'u/require/core.php');
			foreach ($colonys as $key => $value) {
				$colony = $newColony->getColonyById($key);
				$colony['tnum'] -= $value['tnum'];
				$colony['pnum'] -= $value['pnum'];
				updateGroupLevel($key, $colony);

				//* $db->update("UPDATE pw_colonys SET tnum=tnum-" . S::sqlEscape($value['tnum']) . ", pnum=pnum-" . S::sqlEscape($value['pnum']) . ", todaypost=todaypost-" . S::sqlEscape($value['todaypost']) . " WHERE id=". S::sqlEscape($key));
				$db->update(pwQuery::buildClause("UPDATE :pw_table SET tnum=tnum-:tnum,pnum=pnum-:pnum,todaypost=todaypost-:todaypost WHERE id=:id", array('pw_colonys',$value['tnum'], $value['pnum'], $value['todaypost'], $key)));
			}

			foreach ($ptable_a as $key => $val) {
				$pw_posts = GetPtable($key);
				//$db->update("DELETE FROM $pw_posts WHERE tid IN ($delids)");
				pwQuery::delete($pw_posts, 'tid IN(:tid)', array($delid));//此处需要传入数组，所以用$delid传入
			}
			$db->update("DELETE FROM $pw_tmsgs WHERE tid IN ($delids)");
			delete_tag($delids);
			/**
			* 数据更新
			*/

			foreach ($fidarray as $fid) {
				updateforum($fid);
			}

			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			foreach ($delnum as $key => $value){
				$userService->updateByIncrement($key, array(), array('postnum'=>-$value));
			}
			//* P_unlink(D_P.'data/bbscache/c_cache.php');
			pwCache::deleteData(D_P.'data/bbscache/c_cache.php');

		} else {

			!$delid && adminmsg('operate_error');
			$pw_posts = GetPtable($ptable);
			$fidarray = $tidarray = $delnum = $delaids = array();
			$delids = S::sqlImplode($delid);

			$query = $db->query("SELECT aid,tid,fid,authorid FROM $pw_posts WHERE pid IN ($delids)");
			while (@extract($db->fetch_array($query))) {
				$fidarray[$fid]	= 1;
				$tidarray[]		= $tid;
				if ($aid) {
					$attachs = unserialize(stripslashes($aid));
					foreach ($attachs as $key => $value) {
						is_numeric($key) && $delaids[] = $key;
						P_unlink("$attachdir/$value[attachurl]");
						$value['ifthumb'] && P_unlink("$attachdir/thumb/$value[attachurl]");
					}
				}
				$delnum[$authorid]++;
			}
			/**
			* 删除帖子
			*/
			if ($tidarray) {
				$dtids = array_unique($tidarray);
				$query = $db->query("SELECT tid,fid,postdate,ifupload FROM pw_threads WHERE tid IN(" . S::sqlImplode($dtids) . ")");
				while (@extract($db->fetch_array($query))) {
					$htmurl = $db_readdir.'/'.$fid.'/'.date('ym',$postdate).'/'.$tid.'.html';
					if (file_exists(R_P . $htmurl)) {
						P_unlink(R_P . $htmurl);
					}
				}
			}
			if ($delaids) {
				$delaids = S::sqlImplode($delaids);
				$db->update("DELETE FROM pw_attachs WHERE aid IN($delaids)");
			}
			//$db->update("DELETE FROM $pw_posts WHERE pid IN ($delids)");
			pwQuery::delete($pw_posts, 'pid IN(:pid)', array($delid));
			$tidarray = array_count_values($tidarray);
			foreach ($tidarray as $key => $value) {
				//$db->update("UPDATE pw_threads SET replies=replies-".S::sqlEscape($value)." WHERE tid=" . S::sqlEscape($key));
				$db->update(pwQuery::buildClause("UPDATE :pw_table SET replies=replies-:replies WHERE tid=:tid", array('pw_threads', $value, $key)));
			}
			/**
			* 数据更新
			*/
			foreach ($fidarray as $fid => $v) {
				updateforum($fid);
			}

			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			foreach ($delnum as $key => $value){
				$userService->updateByIncrement($key, array(), array('postnum'=>-$value));
			}
			//* P_unlink(D_P.'data/bbscache/c_cache.php');
			pwCache::deleteData(D_P.'data/bbscache/c_cache.php');
		}
		adminmsg('operate_success',"$basename&action=argument&");

	} else {

		S::gp(array('ttable', 'ptable', 'page','cid','author','ckauthor','keyword','ktype','ttype','ckkeyword','postdate_s','postdate_e','orderby','sc','perpage','cname'));

		$postdateStartString = $postdate_s && is_numeric($postdate_s) ? get_date($postdate_s, 'Y-m-d') : $postdate_s;
		$postdateEndString = $postdate_e && is_numeric($postdate_e) ? get_date($postdate_e, 'Y-m-d') : $postdate_e;

		$threadTables = $postTables = array();
		if ($db_tlist && count($db_tlist)>1) {
			foreach ($db_tlist as $key => $value) {
				$name = !empty($value['2']) ? $value['2'] : ($key == 0 ? 'tmsgs' : 'tmsgs'.$key);
				$threadTables[$key] = $name;
			}
		}
		if ($db_plist && count($db_plist)>1) {
			foreach ($db_plist as $key => $val) {
				$name = $val ? $val : ($key != 0 ? getLangInfo('other','posttable').$key : getLangInfo('other','posttable'));
				$postTables[$key] = $name;
			}
		}
		empty($o_classdb) && $o_classdb = array();
		$colonySelection = formSelect('cid', $cid, array('-1'=>'不限制') + $o_classdb, 'class="select_wa"');
		$threadTableSelection = !empty($threadTables) ? formSelect('ttable', $ttable, $threadTables, 'class="select_wa"') : '';
		$postTableSelection = !empty($threadTables) ? formSelect('ptable', $ptable, $postTables, 'class="select_wa"') : '';
		$searchTypeSelection = formSelect('ttype', $ttype, array('1'=>'主题', '2'=>'回复'), 'onchange="seltable(this.value);" class="select_wa fl mr20"');
		$keywordTypeSelection = formSelect('ktype', $ktype, array('subject'=>'标题', 'content'=>'内容'), 'class="select_wa fl mr20"');
		$orderTypeSelection = formSelect('orderby', $orderby, array('postdate'=>'发表日期', 'authorid'=>'帖子作者'), 'class="select_wa fl mr20"');
		$ckauthorChecked = $ckauthor ? 'checked' : '';
		$ckkeywordChecked = $ckkeyword ? 'checked' : '';
		$ascChecked = $sc == 'ASC' ? 'checked' : '';
		$descChecked = !$ascChecked ? 'checked' : '';
		$ttype = !$ttype ? 1 : $ttype;

		$argumentdb = array();
		$addpage = $sqltab = $sql = '';
		$tpre = 't';
		if ($ttype == '1') {
			$sqltab = 'pw_threads t';
			$pw_tmsgs = 'pw_tmsgs' . ($ttable > 0 ? intval($ttable) : '');
			$tpre = 'tm';
			$addpage .= "ttable=$ttable&";
		} else {
			$sqltab = GetPtable($ptable) . ' t';
			$addpage .= "ttype=2&ptable=$ptable&";
		}
		if ((int)$cid > 0) {
			$sql .= " AND t.fid=" . S::sqlEscape($cid);
			$addpage .= "cid=$cid&";
		}
		if ($cname){
			$addpage .= "cname=$cname&";
			$sql .= " AND c.cname LIKE " . S::sqlEscape(str_replace('*', '%', $cname));
		}
		if ($author) {
			$addpage .= "author=$author&";
			if ($ckauthor) {
				$addpage .= "ckauthor=$ckauthor&";
				$u_sql = "username=" . S::sqlEscape($author);
			} else {
				$u_sql = "username LIKE " . S::sqlEscape('%'.$author.'%');
			}
			$authorids = array();
			$query = $db->query("SELECT uid FROM pw_members WHERE $u_sql LIMIT 30");
			while ($rt = $db->fetch_array($query)) {
				$authorids[] = $rt['uid'];
			}
			if (!$authorids) {
				$authorids = array(0);
				//adminmsg('author_nofind', $basename . '&action=argument');
			}
			$sql .= " AND t.authorid IN(" . S::sqlImplode($authorids) . ")";
		}
		if ($keyword) {
			$addpage .= "keyword=$keyword&ktype=$ktype&ckkeyword=$ckkeyword&";

			if ($ckkeyword) {
				$k_sql = " = " . S::sqlEscape($keyword);
			} else {
				$k_sql = " LIKE " . S::sqlEscape('%'.$keyword.'%');
			}
			if ($ktype == 'subject') {
				$sql .= " AND t.subject" . $k_sql;
			} else {
				$ttype == '1' && $sqltab .= " LEFT JOIN $pw_tmsgs tm ON t.tid=tm.tid";
				$sql .= " AND {$tpre}.content" . $k_sql;
			}
		}
		if ($postdate_s) {
			!is_numeric($postdate_s) && $postdate_s = PwStrtoTime($postdate_s);
			$sql .= " AND t.postdate>".S::sqlEscape($postdate_s);
			$addpage .= "postdate_s=$postdate_s&";
		}
		if ($postdate_e) {
			!is_numeric($postdate_e) && $postdate_e = PwStrtoTime($postdate_e);
			$sql .= " AND t.postdate<".S::sqlEscape($postdate_e);
			$addpage .= "postdate_e=$postdate_e&";
		}
		$sql_orderby = ($orderby == 'postdate') ? 'ORDER BY t.postdate' : 'ORDER BY t.authorid';
		$sc != 'ASC' && $sc = 'DESC';
		!$perpage && $perpage = $db_perpage;
		(int)$page<1 && $page = 1;
		$limit = S::sqlLimit(($page-1)*$perpage,$perpage);

		$query = $db->query("SELECT t.*,a.*,c.cname FROM $sqltab LEFT JOIN pw_argument a ON t.tid=a.tid LEFT JOIN pw_colonys c ON a.cyid=c.id WHERE 1 $sql AND a.tid IS NOT NULL $sql_orderby $sc $limit");
		$argumentdb = array();
		while ($rt = $db->fetch_array($query)) {
			$rt['delid'] = isset($rt['pid']) ? $rt['pid'] : $rt['tid'];
			!$rt['subject'] && $rt['subject'] = substrs($rt['content'], 30);
			$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
			$argumentdb[] = $rt;
		}
		$db->free_result($query);
		@extract($db->get_one("SELECT COUNT(*) AS count FROM $sqltab LEFT JOIN pw_argument a ON t.tid=a.tid AND a.tid IS NOT NULL LEFT JOIN pw_colonys c ON a.cyid=c.id WHERE 1 $sql AND a.tid IS NOT NULL"));
		if ($count > $perpage) {
			$pages = numofpage($count,$page,ceil($count/$perpage), "$basename&action=argument&step=list&ttype=$ttype&orderby=$orderby&sc=$sc&perpage=$perpage&$addpage");
		}

		$author = str_replace('%', '*', $author);
	}
	require_once PrintApp('admin');

} elseif ($action == 'album') {

	if ($job == 'delete') {

		require_once(R_P .'u/require/core.php');
		S::gp(array('selid','aname','owner','cid','crtime_s','crtime_e','lasttime_s','lasttime_e','private','lines','orderway','ordertype','cname'));
		empty($selid) && adminmsg("no_album_selid");

		$query = $db->query("SELECT ownerid,COUNT(*) AS sum,SUM(photonum) AS photonum FROM pw_cnalbum WHERE aid IN(" . S::sqlImplode($selid) . ") AND atype='1' GROUP BY ownerid");
		while ($rt = $db->fetch_array($query)) {

			$colony = $newColony->getColonyById($rt['ownerid']);
			$colony['albumnum'] -= $rt['sum'];
			updateGroupLevel($rt['ownerid'], $colony);

			//* $db->update("UPDATE pw_colonys SET albumnum=albumnum-" . S::sqlEscape($rt[sum]) . ",photonum=photonum-". S::sqlEscape($rt['photonum']) ." WHERE id=" . S::sqlEscape($rt['ownerid']));
			$db->update(pwQuery::buildClause("UPDATE :pw_table SET albumnum=albumnum-:albumnum,photonum=photonum-:photonum WHERE id=:id", array('pw_colonys',$rt['sum'], $rt['photonum'], $rt['ownerid'])));
		}

		foreach ($selid as $key => $aid) {
			$query = $db->query("SELECT cn.pid,cn.path,cn.ifthumb,ca.ownerid FROM pw_cnphoto cn LEFT JOIN pw_cnalbum ca ON cn.aid=ca.aid WHERE cn.aid=" . S::sqlEscape($aid));
			if (($num = $db->num_rows($query)) > 0) {
				$affected_rows = 0;
				while ($rt = $db->fetch_array($query)) {
					$uids[] = $rt['ownerid'];
					pwDelatt($rt['path'], $db_ifftp);
					if ($rt['ifthumb']) {
						$lastpos = strrpos($rt['path'],'/') + 1;
						pwDelatt(substr($rt['path'], 0, $lastpos) . 's_' . substr($rt['path'], $lastpos), $db_ifftp);
					}
					$affected_rows += delAppAction('photo',$rt['pid'])+1;//TODO 效率？
				}
				pwFtpClose($ftp);
				countPosts("-$affected_rows");
			}
		}
		//获取相片总数
//		$query = $db->query("SELECT ownerid AS cyid,COUNT(*) AS count,SUM(photonum) AS photonum FROM pw_cnalbum WHERE aid IN(" . S::sqlImplode($selid) . ') GROUP BY ownerid');
//		while ($rt = $db->fetch_array($query)) {
//			$albumnum = (int)$rt['count'];
//			$photonum = (int)$rt['photonum'];
//			$cyid = (int)$rt['cyid'];
//			$db->update("UPDATE pw_colonys SET albumnum=albumnum-$albumnum,photonum=photonum-$photonum" . " WHERE id=" . S::sqlEscape($cyid));
//		}

		$db->update("DELETE FROM pw_cnphoto WHERE aid IN(" . S::sqlImplode($selid) . ')');
		$db->update("DELETE FROM pw_cnalbum WHERE aid IN(" . S::sqlImplode($selid) . ')');

		$uids = array_unique($uids);
		updateUserAppNum($uids,'photo','recount');
		adminmsg('operate_success',"$basename&action=album&job=list&aname=" . rawurlencode($aname). "&cname=".rawurlencode($cname)."&owner=" .rawurlencode($owner). "&cid=$cid&crtime_s=$crtime_s&crtime_e=$crtime_e&lasttime_s=$lasttime_s&lasttime_e=$lasttime_e&private=$private&lines=$lines&orderway=$orderway&ordertype=$ordertype&page=$page&");

	} elseif ($job == 'edit') {

		S::gp(array('aid'));
		$album = $db->get_one("SELECT aid,aname,aintro,private FROM pw_cnalbum WHERE aid=".S::sqlEscape($aid));
		empty($album) && Showmsg('album_not_exist',"$basename&action=albums");

		if (empty($_POST['step'])) {

			S::gp(array('cname','aname','owner','cid','crtime_s','crtime_e','lasttime_s','lasttime_e','private','lines','orderway','ordertype','page'));
			${'private_'.$album['private']} = 'selected';
			require_once PrintApp('admin');

		} else {

			S::gp(array('aname','aintro','private'));
			S::gp(array('url_cname','url_aname','url_owner','url_cid','url_crtime_s','url_crtime_e','url_lasttime_s','url_lasttime_e','url_private','url_lines','url_orderway','url_ordertype','url_page'));
			$db->update("UPDATE pw_cnalbum SET ".S::sqlSingle(array('aname' => $aname,'aintro' => $aintro, 'private' => $private))." WHERE aid=".S::sqlEscape($aid));
			adminmsg('operate_success',"$basename&action=album&job=list&&cname=".rawurlencode($url_cname)."&aname=".rawurlencode($url_aname)."&owner=".rawurlencode($url_owner)."&cid=$url_cid&crtime_s=$url_crtime_s&crtime_e=$url_crtime_e&lasttime_s=$url_lasttime_s&lasttime_e=$url_lasttime_e&private=$url_private&lines=$url_lines&orderway=$url_orderway&ordertype=$url_ordertype&page=$url_page&");
		}
	} else {
		S::gp(array('aname','owner','cid','crtime_s','crtime_e','lasttime_s','lasttime_e','private','lines','orderway','ordertype','page','cname', 'searchDisplay'));

		$crtimeStartString = $crtime_s && is_numeric($crtime_s) ? get_date($crtime_s, 'Y-m-d') : $crtime_s;
		$crtimeEndString = $crtime_e && is_numeric($crtime_e) ? get_date($crtime_e, 'Y-m-d') : $crtime_e;
		$lasttimeStartString = $lasttime_s && is_numeric($lasttime_s) ? get_date($lasttime_s, 'Y-m-d') : $lasttime_s;
		$lasttimeEndString = $lasttime_e && is_numeric($lasttime_e) ? get_date($lasttime_e, 'Y-m-d') : $lasttime_e;
		empty($o_classdb) && $o_classdb = array();
		$colonySelection = formSelect('cid', $cid, array(''=>'不限制') + $o_classdb, 'class="select_wa"');
		null === $private && $private = -1;
		$privateSelection = formSelect('private', $private, array('-1'=>'不限制', '0'=>'全站可见', '1'=>'仅群内可见'), 'class="select_wa"');
		$orderwaySelection = formSelect('orderway', $orderway, array('crtime'=>'按发表时间排序', 'lasttime'=>'最后更新时间'), 'class="select_wa fl mr20"');
		$ascChecked = $ordertype == 'asc' ? 'checked' : '';
		$descChecked = !$ascChecked ? 'checked' : '';

		$encode_aname = rawurlencode($aname);
		$encode_owner = rawurlencode($owner);
		$crtime_s && !is_numeric($crtime_s) && $crtime_s = PwStrtoTime($crtime_s);
		$crtime_e && !is_numeric($crtime_e) && $crtime_e = PwStrtoTime($crtime_e);
		$lasttime_s && !is_numeric($lasttime_s) && $lasttime_s = PwStrtoTime($lasttime_s);
		$lasttime_e && !is_numeric($lasttime_e) && $lasttime_e = PwStrtoTime($lasttime_e);
		null === $searchDisplay && $searchDisplay = 'none';

		$sql = "c.atype='1'";
		$sqltab = $urladd = '';
		if ($cname) {
			$cname = str_replace('*','%',$cname);
			$sql .= ' AND cl.cname LIKE '.S::sqlEscape($cname);
			$urladd .= '&cname='.rawurlencode($cname);
		}
		if ($aname) {
			$aname = str_replace('*','%',$aname);
			$sql .= ' AND c.aname LIKE '.S::sqlEscape($aname);
			$urladd .= '&aname='.rawurlencode($aname);
		}
		if ($owner) {
			$owner = str_replace('*','%',$owner);
			$sql .= ' AND c.owner LIKE '.S::sqlEscape($owner);
			$urladd .= '&owner='.rawurlencode($owner);
		}
		if ($cid) {
			$sql .= ' AND cl.classid LIKE '.S::sqlEscape($cid);
			$urladd .= '&cid='.rawurlencode($cid);
		}
		if ($crtime_s) {
			$sql .= ' AND c.crtime>='.S::sqlEscape($crtime_s);
			$urladd .= "&crtime_s=$crtime_s";
		}
		if ($crtime_e) {
			$sql .= ' AND c.crtime<='.S::sqlEscape($crtime_e);
			$urladd .= "&crtime_e=$crtime_e";
		}
		if ($lasttime_s) {
			$sql .= ' AND c.lasttime>='.S::sqlEscape($lasttime_s);
			$urladd .= "&lasttime_s=$lasttime_s";
		}
		if ($lasttime_e) {
			$sql .= ' AND c.lasttime<='.S::sqlEscape($lasttime_e);
			$urladd .= "&lasttime_e=$lasttime_e";
		}
		if ($private > -1) {
			$sql .= ' AND c.private='.S::sqlEscape($private);
			$urladd .= "&private=$private";
		}
		$sql_orderway = $orderway == 'crtime' ? 'c.crtime' : 'c.lasttime';
		$ordertype = $ordertype == 'asc' ? 'asc' : 'desc';
		$urladd .= "&orderway=$orderway&ordertype=$ordertype&lines=$lines&searchDisplay=$searchDisplay";
		$count = $db->get_value("SELECT COUNT(*) AS count FROM pw_cnalbum c LEFT JOIN pw_colonys cl ON c.ownerid=cl.id WHERE $sql");
		//empty($count) && adminmsg('album_not_exist',"$basename&action=album");
		!is_numeric($lines) && $lines=30;
		$page < 1 && $page = 1;
		$numofpage = ceil($count/$lines);
		if ($numofpage && $page > $numofpage) {
			$page = $numofpage;
		}
		$pages = numofpage($count,$page,$numofpage,"$basename&action=$action&job=list$urladd&");
		$start = ($page-1)*$lines;
		$limit = S::sqlLimit($start,$lines);
		$query = $db->query("SELECT c.aid,c.aname,c.private,c.ownerid,c.owner,c.photonum,c.lasttime,c.lastpid,c.crtime,cl.cname,cl.admin FROM pw_cnalbum c LEFT JOIN pw_colonys cl ON c.ownerid=cl.id WHERE $sql ORDER BY $sql_orderway $ordertype ".$limit);
		while ($rt = $db->fetch_array($query)) {
			$rt['s_aname'] = substrs($rt['aname'],30);
			$rt['lasttime'] = $rt['lasttime'] ? get_date($rt['lasttime']) : '-';
			$rt['crtime'] 	= $rt['crtime'] ? get_date($rt['crtime']) : '-';
			$albumdb[] = $rt;
		}

		$cname = str_replace('%', '*', $cname);
		$aname = str_replace('%', '*', $aname);
	}

	require_once PrintApp('admin');

} elseif ($action == 'photos') {

	if ($job == 'delete') {

		S::gp(array('aid','cid','cname','aname','uploader','pintro','uptime_s','uptime_e','orderway','ordertype','lines','page','selid'));
		require_once(R_P . 'u/require/core.php');
		foreach ($selid as $key => $pid) {
			$photo = $db->get_one("SELECT cp.path,ca.aid,ca.lastphoto,ca.lastpid,ca.ownerid FROM pw_cnphoto cp LEFT JOIN pw_cnalbum ca ON cp.aid=ca.aid WHERE cp.pid=" . S::sqlEscape($pid) . " AND ca.atype='1'");
			if (empty($photo)) {
				adminmsg('data_error',"$basename&action=photos");
			}
			$uids[] = $photo['ownerid'];
			$db->update("DELETE FROM pw_cnphoto WHERE pid=" . S::sqlEscape($pid));

			$pwSQL = array();
			if ($photo['path'] == $photo['lastphoto']) {
				$pwSQL['lastphoto'] = $db->get_value("SELECT path FROM pw_cnphoto WHERE aid=" . S::sqlEscape($photo['aid']) . " ORDER BY pid DESC LIMIT 1");
			}
			if (strpos(",$photo[lastpid],",",$pid,") !== false) {
				$pwSQL['lastpid'] = implode(',',getLastPid($photo['aid']));
			}
			$upsql = $pwSQL ? ',' . S::sqlSingle($pwSQL) : '';
			$db->update("UPDATE pw_cnalbum SET photonum=photonum-1{$upsql} WHERE aid=" . S::sqlEscape($photo['aid']));

			$colony = $newColony->getColonyById($photo['ownerid']);
			$colony['photonum']--;
			updateGroupLevel($photo['ownerid'], $colony);

			//* $db->update("UPDATE pw_colonys SET photonum=photonum-1 WHERE id=" . S::sqlEscape($photo['ownerid']));
			$db->update(pwQuery::buildClause("UPDATE :pw_table SET photonum=photonum-1 WHERE id=:id", array('pw_colonys',$photo['ownerid'])));

			pwDelatt($photo['path'], $db_ifftp);
			$lastpos = strrpos($photo['path'],'/') + 1;
			pwDelatt(substr($photo['path'], 0, $lastpos) . 's_' . substr($photo['path'], $lastpos), $db_ifftp);
			pwFtpClose($ftp);

			$affected_rows = delAppAction('photo',$pid) + 1;
			countPosts("-$affected_rows");
		}
		$uids = array_unique($uids);
		updateUserAppNum($uids,'photo','recount');
		adminmsg('operate_success',"$basename&action=photos&job=list&aid=$aid&cid=$cid&cname=".rawurlencode($cname)."&aname=".rawurlencode($aname)."&uploader=".rawurlencode($uploader)."&pintro=".rawurlencode($pintro)."&uptime_s=$uptime_s&uptime_e=$uptime_e&orderway=$orderway&ordertype=$ordertype&lines=$lines&page=$page&");
	} else {

		require_once(R_P . 'u/require/core.php');
		S::gp(array('cid','cname','aid','aname','uploader','pintro','uptime_s','uptime_e','orderway','ordertype','lines','page'));

		empty($o_classdb) && $o_classdb = array();
		$colonySelection = formSelect('cid', $cid, array(''=>'不限制') + $o_classdb, 'class="select_wa"');
		$uptimeStartString = $uptime_s && is_numeric($uptime_s) ? get_date($uptime_s, 'Y-m-d') : $uptime_s;
		$uptimeEndString = $uptime_e && is_numeric($uptime_e) ? get_date($uptime_e, 'Y-m-d') : $uptime_e;
		$orderwaySelection = formSelect('orderway', $orderway, array('uptime'=>'上传日期', 'hits'=>'浏览数'), 'class="input input_wa mr20 fl"');
		$ascChecked = $ordertype == 'asc' ? 'checked' : '';
		$descChecked = !$ascChecked ? 'checked' : '';

		$uptime_s && $uptime_s = PwStrtoTime($uptime_s);
		$uptime_e && $uptime_e = PwStrtoTime($uptime_e);

		$urladd = '';
		$sql = "ca.atype='1'";

		if	($cid) {
			$sql .= ' AND c.classid='.S::sqlEscape($cid);
			$urladd .= '&cid='.$cid;
		}

		if ($cname) {
			$urladd .= '&cname='.rawurlencode($cname);
			$cname = str_replace('*','%',$cname);
			$sql .= ' AND c.cname LIKE '.S::sqlEscape($cname);
		}

		if ($aid) {
			$sql .= ' AND ca.aid ='.S::sqlEscape($aid);
			$urladd .= '&aid='.$aid;
		}
		if ($aname) {
			$urladd .= '&aname='.rawurlencode($aname);
			$aname = str_replace('*','%',$aname);
			$sql .= ' AND ca.aname LIKE '.S::sqlEscape($aname);
		}
		if ($uploader) {
			$uploader = str_replace('*','%',$uploader);
			$sql .= ' AND cp.uploader LIKE '.S::sqlEscape($uploader);
			$urladd .= '&uploader='.rawurlencode($uploader);
		}
		if ($pintro) {
			$pintro = str_replace('*','%',$pintro);
			$sql .= ' AND cp.pintro LIKE '.S::sqlEscape($pintro);
			$urladd .= '&pintro='.rawurlencode($pintro);
		}
		if ($uptime_s) {
			$sql .= ' AND cp.uptime>='.S::sqlEscape($uptime_s);
			$urladd .= "&uptime_s=$uptime_s";
		}
		if ($uptime_e) {
			$uptime_e_sql = $uptime_e + 24*3600;
			$sql .= ' AND cp.uptime<='.S::sqlEscape($uptime_e_sql);
			$urladd .= "&uptime_e=$uptime_e";
		}
		switch ($orderway) {
			case 'uptime' :
				$sql_orderway = 'cp.uptime';break;
			case 'hits' :
				$sql_orderway = 'cp.hits';break;
			case 'c_num' :
				$sql_orderway = 'cp.c_num';break;
			default:
				$sql_orderway = '';break;
		}

		$ordertype = $ordertype == 'asc' ? 'asc' : 'desc';
		$sqladd = $sql_orderway ? "ORDER BY $sql_orderway $ordertype" : '';
		$urladd .= $sql_orderway ? "&orderway=$orderway&ordertype=$ordertype" : '';
		$count = $db->get_value("SELECT COUNT(*) AS count FROM pw_cnphoto cp LEFT JOIN pw_cnalbum ca ON cp.aid=ca.aid LEFT JOIN pw_colonys c ON ca.ownerid=c.id WHERE $sql");
		//empty($count) && adminmsg('no_photos',"$basename&action=photos");
		!is_numeric($lines) && $lines=30;
		$page < 1 && $page = 1;
		$numofpage = ceil($count/$lines);
		if ($numofpage && $page > $numofpage) {
			$page = $numofpage;
		}
		$pages = numofpage($count,$page,$numofpage,"$basename&action=$action&job=list&lines=$lines$urladd&");
		$start = ($page-1)*$lines;
		$limit = S::sqlLimit($start,$lines);
		$query = $db->query("SELECT cp.pid,cp.aid,cp.path,cp.uploader,cp.uptime,cp.ifthumb,cp.hits,cp.c_num,ca.aname,ca.ownerid,c.cname FROM pw_cnphoto cp LEFT JOIN pw_cnalbum ca ON cp.aid=ca.aid LEFT JOIN pw_colonys c ON ca.ownerid=c.id WHERE ".$sql." ".$sqladd." ".$limit);
		$cnpho = array();
		while ($rt = $db->fetch_array($query)) {
			$rt['s_aname']	= substrs($rt['aname'],10);
			$rt['path']	= getphotourl($rt['path'], $rt['ifthumb']);
			$rt['uptime']	= get_date($rt['uptime']);
			$cnpho[] = $rt;
		}

		$cname = str_replace('%', '*', $cname);
		$aname = str_replace('%', '*', $aname);
		$uploader = str_replace('%', '*', $uploader);
		$pintro = str_replace('%', '*', $pintro);

		require_once PrintApp('admin');
	}
} elseif ($action == 'level') {

	require_once(A_P . 'action/admin_level.php');

} elseif ($action == 'active') {


	S::gp(array('job'));

	if ($job == 'del') {

		if (empty($_POST['step'])) {

			S::gp(array('id'));
			define('AJAX', 1);
			$posthash = EncodeUrl("$basename&action=active&job=del&selid=$id&ajax=1");
			require_once (A_P . 'template/admin_ajax.htm');
			ajax_footer();

		} else {

			S::gp(array('selid','urladd'));

			if (isset($_GET['ajax'])) {
				define('AJAX', 1);
			}
			$basename .= "&action=$action&job=list" . $urladd;
			if (!$selid) {
				adminmsg('operate_error');
			}
			require_once(A_P . 'lib/active.class.php');
			$newActive = new PW_Active();
			$newActive->delActive($selid);

			if (defined('AJAX')) {
				echo "ok\t$selid";ajax_footer();
			} else {
				adminmsg('operate_success');
			}
		}

	} else {
		S::gp(array('page','type','limit','activestate'), 'GP', 2);
		S::gp(array('title','cname','cadmin','createtime_s','createtime_e','orderway','ordertype','activestate'));
		$activeState = formSelect('activestate',$activestate,array('-1'=>'不限制','1'=>'可以报名','2'=>'人员已满','3'=>'报名已截止','4'=>'活动进行中','5'=>'活动已结束'),'class="select_wa"');
		$activeTypes = array(1 => '出游', 2 => '聚餐 ', 3 => '舞会', 4 => '户外', 5 => '烧烤', 6 => '其他');
		$activeTypeSelection = formSelect('type', $type, $activeTypes, 'class="select_wa"', '不限制');
		$orderwayTypeSelection = formSelect('orderway', $orderway, array('members'=>'报名人数', 'id'=>'发布时间'), 'class="select_wa fl mr20"');
		$ascChecked = $ordertype == 'asc' ? 'checked' : '';
		$descChecked = !$ascChecked ? 'checked' : '';

		$page < 1 && $page = 1;
		$basename .= "&action=$action";
		$url_add = '';
		$data = array();
		if ($title) {
			$data['title'] = $title;
			$url_add .= '&title=' . rawurlencode($title);
		}
		if ($type) {
			$data['type'] = $type;
			$url_add .= '&type=' . $type;
		}
		if ($activestate) {
			$data['activestate'] = $activestate;
			$url_add .= '&activestate=' . $activestate;
		}
		if ($cname) {
			$str_cname = str_replace('*','%', $cname);
			$where = ($str_cname == $cname ? '=' : ' LIKE ') . S::sqlEscape($str_cname);
			$array = array();
			$query = $db->query("SELECT id FROM pw_colonys WHERE cname " . $where . ' LIMIT 100');
			while ($rt = $db->fetch_array($query)) {
				$array[] = $rt['id'];
			}
			if (empty($array)) {
				Showmsg('没有找到相关的活动!');
			} else {
				$data['cid'] = $array;
			}
			$url_add .= '&cname=' . rawurlencode($cname);
		}
		if ($cadmin) {
			$str_cadmin = str_replace('*','%', $cadmin);
			$where = ($str_cadmin == $cadmin ? '=' : ' LIKE ') . S::sqlEscape($str_cadmin);
			$array = array();
			$query = $db->query("SELECT uid FROM pw_members WHERE username" . $where . ' LIMIT 100');
			while ($rt = $db->fetch_array($query)) {
				$array[] = $rt['uid'];
			}
			if (empty($array)) {
				Showmsg('没有找到相关的活动!');
			} else {
				$data['uid'] = $array;
			}
			$url_add .= '&cadmin=' . rawurlencode($cadmin);
		}
		if ($createtime_s) {
			$data['createtime_s'] = PwStrtoTime($createtime_s);
			$url_add .= '&createtime_s=' . $createtime_s;
		}
		if ($createtime_e) {
			$data['createtime_e'] = PwStrtoTime($createtime_e);
			$url_add .= '&createtime_e=' . $createtime_e;
		}
		$limit = intval($limit);
		$limit < 1 && $limit = 30;
		$url_add .= "&orderway=$orderway&ordertype=$ordertype&limit=$limit";
		!$orderway && $orderway = 'id';

		require_once(A_P . 'lib/active.class.php');
		$newActive = new PW_Active();
		list($activedb, $total) = $newActive->searchList($data, $limit, ($page - 1) * $limit, $orderway, $ordertype, true);

		if (empty($activedb)) {
			//Showmsg('没有找到相关的活动!');
		}
		$pages = numofpage($total, $page, ceil($total / $limit), "$basename&job=list{$url_add}&");

		$uids = $cids = array();
		foreach ($activedb as $key => $value) {
			$activedb[$key]['createtime'] = get_date($value['createtime']);
			$uids[] = $value['uid'];
			$cids[] = $value['cid'];
		}
		$users = array();
		if ($uids) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$users = $userService->getUserNamesByUserIds($uids);
		}
		$colonys = array();
		if ($cids) {
			$query = $db->query("SELECT id,cname FROM pw_colonys WHERE id IN (" . S::sqlImplode($cids) . ')');
			while ($rt = $db->fetch_array($query)) {
				$colonys[$rt['id']] = $rt['cname'];
			}
		}

		$title = str_replace('%', '*', $title);

		require_once PrintApp('admin');
	}

} elseif ($action == 'write') {

	if ($job == 'del') {

		S::gp(array('selid','content','username','postdate_s','postdate_e','ordertype','page','lines'));
		empty($selid) && adminmsg("no_write_selid","$basename&action=write");
		require_once(R_P. "u/require/core.php");
		foreach ($selid as $key => $id) {
			$writedb = $db->get_one("SELECT uid,cyid FROM pw_cwritedata WHERE id=".S::sqlEscape($id));
			if (empty($writedb)) {
				adminmsg('data_error',"$basename&action=write");
			}
			$uids[] = $writedb['uid'];
			$db->update("DELETE FROM pw_cwritedata WHERE id=".S::sqlEscape($id));

			$colony = $newColony->getColonyById($writedb['cyid']);
			$colony['writenum']--;
			updateGroupLevel($colony['id'], $colony);

			//* $db->update("UPDATE pw_colonys SET writenum=writenum-1 WHERE id=". S::sqlEscape($writedb['cyid']));
			$db->update(pwQuery::buildClause("UPDATE :pw_table SET writenum=writenum-1 WHERE id=:id", array('pw_colonys',$writedb['cyid'])));

			$affected_rows = delAppAction('write',$id)+1;
			countPosts("-$affected_rows");
		}
		$uids = array_unique($uids);
		updateUserAppNum($uids,'owrite','recount');
		adminmsg('operate_success',"$basename&action=write&job=list&content=".rawurlencode($content)."&username=".rawurlencode($username)."&postdate_s=$postdate_s&postdate_e=$postdate_e&ordertype=$ordertype&lines=$lines&page=$page&");
	} else {
		S::gp(array('content','username','cid','cname','postdate_s','postdate_e','ordertype','page','lines'));

		empty($o_classdb) && $o_classdb = array();
		$colonySelection = formSelect('cid', $cid, array('-1'=>'不限制') + $o_classdb, 'class="select_wa"');
		$postdateStartString = $postdate_s && is_numeric($postdate_s) ? get_date($postdate_s, 'Y-m-d') : $postdate_s;
		$postdateEndString = $postdate_e && is_numeric($postdate_e) ? get_date($postdate_e, 'Y-m-d') : $postdate_e;
		$ascChecked = $ordertype == 'asc' ? 'checked' : '';
		$descChecked = !$ascChecked ? 'checked' : '';

		$postdate_s && !is_numeric($postdate_s) && $postdate_s = PwStrtoTime($postdate_s);
		$postdate_e && !is_numeric($postdate_e) && $postdate_e = PwStrtoTime($postdate_e);
		$sql = $urladd = '';
		if ($content) {
			$content = str_replace('*','%',$content);
			$sql .= $sql ? ' AND' : '';
			$sql .= ' w.content LIKE '.S::sqlEscape($content);
			$urladd .= '&content='.rawurlencode($content);
		}
		if ($username) {
			$username = str_replace('*','%',$username);
			$sql .= $sql ? ' AND' : '';
			$sql .= ' m.username LIKE '.S::sqlEscape($username);
			$urladd .= '&username='.rawurlencode($username);
		}
		if ((int)$cid > 0) {
			$sql .= $sql ? ' AND' : '';
			$sql .= "  n.classid=" . S::sqlEscape($cid);
			$urladd .= "cid=$cid&";
		}
		if ($cname){
			$sql .= $sql ? ' AND' : '';
			$sql .= "  n.cname LIKE " . S::sqlEscape(str_replace('*', '%', $cname));
			$urladd .= "cname=$cname&";
		}
		if ($postdate_s) {
			$sql .= $sql ? ' AND' : '';
			$sql .= ' w.postdate>='.S::sqlEscape($postdate_s);
			$urladd .= "&postdate_s=$postdate_s";
		}
		if ($postdate_e) {
			$sql .= $sql ? ' AND' : '';
			$sql .= ' w.postdate<='.S::sqlEscape($postdate_e);
			$urladd .= "&postdate_e=$postdate_e";
		}
		$ordertype = $ordertype == 'asc' ? 'asc' : 'desc';
		$urladd .= "&ordertype=$ordertype&lines=$lines";
		$sql && $sql = 'WHERE' .$sql;
		$count = $db->get_value("SELECT COUNT(*) AS count FROM pw_cwritedata w LEFT JOIN pw_members m ON w.uid=m.uid LEFT JOIN pw_colonys n on w.cyid=n.id $sql");
		//empty($count) && adminmsg('write_not_exist',"$basename&action=write");
		!is_numeric($lines) && $lines=30;
		$page < 1 && $page = 1;
		$numofpage = ceil($count/$lines);
		if ($numofpage && $page > $numofpage) {
			$page = $numofpage;
		}
		$pages=numofpage($count,$page,$numofpage,"$basename&action=write&job=list$urladd&");
		$start = ($page-1)*$lines;
		$limit = S::sqlLimit($start,$lines);

		$query = $db->query("SELECT w.id,w.uid,m.username,w.postdate,w.source,w.content,w.c_num,w.cyid,n.cname FROM pw_cwritedata w LEFT JOIN pw_members m ON w.uid=m.uid LEFT JOIN pw_colonys n on w.cyid=n.id $sql "."ORDER BY postdate $ordertype ".$limit);
		while ($rt = $db->fetch_array($query)) {
			$rt['s_content'] = substrs($rt['content'],40);
			$rt['postdate'] = $rt['postdate'] ? get_date($rt['postdate']) : '-';
			$writedb[] = $rt;
		}

		$content = str_replace('%', '*', $content);
		$username = str_replace('%', '*', $username);

	}

	require_once PrintApp('admin');

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

			S::gp(array('selid','cname','delid','new_t_sub_db'));

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
				if ($value['cname'] != $cname[$key]) {
					$db->update("UPDATE pw_cnstyles SET cname=" . S::sqlEscape($cname[$key]) . ' WHERE id=' . S::sqlEscape($key));
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
					!$delids && $delids[$id][] = $id;
				}
				
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
						$newSubStyle[] = array($vv,1,$k);
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
	$query = $db->query('SELECT id,cname,upid FROM pw_cnstyles WHERE ifopen=1 ORDER BY upid ASC');
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