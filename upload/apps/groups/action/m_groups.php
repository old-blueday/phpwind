<?php
!defined('A_P') && exit('Forbidden');

!$winduid && Showmsg('not_login');
!$db_groups_open && Showmsg('groups_close');

$isGM = CkInArray($windid,$manager);
!$isGM && $groupid==3 && $isGM=1;

InitGP(array('a', 'uid'));

require_once(R_P . 'u/lib/space.class.php');
$newSpace = new PwSpace($uid ? $uid : $winduid);
if (!$space =& $newSpace->getInfo()) {
	Showmsg('您访问的空间不存在!');
}

if ($uid) {
	$isSpace = true;
	$USCR = 'space_groups';
	require_once Pcv($appEntryBasePath . 'action/view.php');
}

if ($db_question && $o_groups_qcheck) {
	$qkey = array_rand($db_question);
}

require_once(R_P.'require/showimg.php');
$current_tab_id = empty($a) ? 'index' : ($a == 'create' ? 'index' : $a);
$db_perpage = 18;

if (empty($a)) {

	$colonyids = $group_own = $group_other = $apply  = array();
	$counter = 0;
	$query = $db->query("SELECT cm.ifadmin,cm.addtime,c.id,c.cname,c.cnimg,c.admin,c.createtime,cm2.uid FROM pw_cmembers cm LEFT JOIN pw_colonys c ON cm.colonyid=c.id LEFT JOIN pw_members cm2 ON c.admin=cm2.username WHERE cm.uid=" . pwEscape($winduid) . " ORDER BY cm.addtime DESC");
	while ($rt = $db->fetch_array($query)) {
		if ($rt['cnimg']) {
			list($rt['cnimg']) = geturl("cn_img/$rt[cnimg]",'lf');
		} else {
			$rt['cnimg'] = $GLOBALS['imgpath'] . '/g/groupnopic.gif';
		}
		empty($rt['addtime']) && $rt['addtime'] = $rt['createtime'];
		$rt['addtime'] = get_date($rt['addtime'], 'Y-m-d');
		if ($rt['ifadmin'] == '-1') {
			$apply[] = $rt;
		} elseif($rt['admin'] == $windid) {
			$counter++;
			$colonyids[] = $rt['id'];
			$group_own[] = $rt;
		} else {
			$counter++;
			$colonyids[] = $rt['id'];
			$group_other[] = $rt;
		}
	}
	$group_own = array_slice($group_own,0,3);
	$group_other = array_slice($group_other,0,3);
	$do = "conloy";
	if ($colonyids) {
		$perpage = 20;//$db_perpage;
		$weiboService = L::loadClass('weibo','sns');/* @var $weiboService PW_Weibo */
		$count = $weiboService->getConloysWeibosCount($colonyids);
		$pageCount = ceil($count / $perpage);
		$page = validatePage($page,$pageCount);
		$weiboList = $weiboService->getConloysWeibos($colonyids,$page,$perpage);
	}

	list($isheader,$isfooter,$tplname,$isleft) = array(true,true,"m_groups",true);

} elseif ($a == 'my') {

	InitGP(array('page'), '', 2);
	$page < 1 && $page = 1;
	$total = $db->get_one("SELECT COUNT(*) AS sum,SUM(cm.username=c.admin) AS creates FROM pw_cmembers cm LEFT JOIN pw_colonys c ON cm.colonyid=c.id WHERE cm.ifadmin<>'-1' AND cm.uid=" . pwEscape($winduid));
	list($pages, $limit) = pwLimitPages($total['sum'], $page, "{$basename}a=my&");

	$group = array();
	$query = $db->query("SELECT c.id,c.cname,c.cnimg,c.admin,c.members FROM pw_cmembers cm LEFT JOIN pw_colonys c ON cm.colonyid=c.id WHERE cm.ifadmin<>'-1' AND cm.uid=" . pwEscape($winduid) . ' ORDER BY (cm.username=c.admin) DESC ' . $limit);
	while ($rt = $db->fetch_array($query)) {
		if ($rt['cnimg']) {
			list($rt['cnimg']) = geturl("cn_img/$rt[cnimg]",'lf');
		} else {
			$rt['cnimg'] = $GLOBALS['imgpath'] . '/g/groupnopic.gif';
		}
		$group[$rt['id']] = $rt;
	}
	$total['adds'] = $total['sum'] - $total['creates'];
	list($isheader,$isfooter,$tplname,$isleft) = array(true,true,"m_groups",true);

} elseif ($a == 'all') {

	InitGP(array('page', 'styleid', 'friends', 'members'), null, 2);
	InitGP(array('keyword'));

	require_once(R_P . 'apps/groups/lib/colony.class.php');
	$atc_name = getLangInfo('app','group');
	$cMembers = $group = array();
	$sqlsel   = $sqltab = '';

	if ($styleid) {
		$sqlsel .= ' AND c.styleid=' . pwEscape($styleid);
	}
	if ($members) {
		$sqlsel .= ' AND c.members>=' . pwEscape($members);
	}
	if ($keyword) {
		$sqlsel .= ' AND c.cname LIKE ' . pwEscape("%" . $keyword . "%");
	}
	if ($friends) {
		$friends = getFriends($winduid);
		unset($friends[$winduid]);
		$uids = $friends ? array_keys($friends) : array(0);
		$sqltab .= ' LEFT JOIN pw_cmembers cm ON c.id=cm.colonyid';
		$sqlsel .= ' AND cm.uid IN(' . pwImplode($uids) . ')';
	}
	$total = $db->get_value("SELECT COUNT(DISTINCT c.id) AS sum FROM pw_colonys c {$sqltab} WHERE 1 {$sqlsel}");

	if ($total) {
		require_once(R_P . 'require/bbscode.php');
		list($pages, $limit) = pwLimitPages($total, $page, "{$basename}a=all&keyword=".rawurlencode($keyword)."&".($styleid?("styleid=".$styleid):"")."&");
		$query = $db->query("SELECT DISTINCT c.* FROM pw_colonys c {$sqltab} WHERE 1 {$sqlsel} ORDER BY c.id DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			if ($rt['cnimg']) {
				list($rt['cnimg']) = geturl("cn_img/$rt[cnimg]", 'lf');
			} else {
				$rt['cnimg'] = $GLOBALS['imgpath'] . '/g/groupnopic.gif';
			}
			$rt['descrip'] = stripWindCode($rt['descrip']);
			$rt['colonyNums'] = PwColony::calculateCredit($rt);
			$rt['createtime'] = get_date($rt['createtime'], 'Y-m-d');
			$group[$rt['id']] = $rt;
		}
	}
	$colonyids = pwImplode(array_keys($group));
	if ($colonyids) {
		$query = $db->query("SELECT id,ifadmin,colonyid FROM pw_cmembers WHERE colonyid IN ($colonyids) AND uid=" . pwEscape($winduid,false));
		while ($rt = $db->fetch_array($query)) {
			$cMembers[$rt['colonyid']] = $rt['ifadmin'];
		}
	}
	$u = $winduid;
	$username = $windid;
	
	/*
	$o_cate = array();
	include_once(D_P . 'data/bbscache/forum_cache.php');
	if(is_array($o_classdb)){
		foreach ($o_classdb as $key => $value) {
				$o_cate[$forum[$key]['fup']][$key] = $value;
		}
	}
	*/
//	require_once(M_P.'require/header.php');
//	require_once PrintEot('m_groups');
//	footer();
	list($isheader,$isfooter,$tplname,$isleft) = array(true,true,"m_groups",true);

} elseif ($a == 'friend') {

	InitGP(array('page','cid'), null, 2);

	$friends = getFriends($winduid);
	unset($friends[$winduid]);
	$friends = is_array($friends) ? array_keys($friends) : array();
	$group = array();
	$pages = '';
	$total = 0;

	if (count($friends)) {

		$total = $db->get_value("SELECT COUNT(DISTINCT c.id) AS count FROM pw_cmembers cm LEFT JOIN pw_colonys c ON cm.colonyid=c.id WHERE cm.uid IN(" . pwImplode($friends) . ") AND cm.ifadmin <> '-1'");
		list($pages,$limit) = pwLimitPages($total,$page,"{$basename}a=friend&");
		$friends[] = $winduid;
		$query = $db->query("SELECT c.id,c.cname,c.cnimg,c.admin,SUM(cm.uid='$winduid') AS ifadd FROM pw_cmembers cm LEFT JOIN pw_colonys c ON cm.colonyid=c.id WHERE cm.uid IN(" . pwImplode($friends) . ") AND cm.ifadmin<>'-1' GROUP BY cm.colonyid HAVING(SUM(cm.uid!='$winduid') > 0) ORDER BY cm.colonyid DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			if ($rt['cnimg']) {
				list($rt['cnimg']) = geturl("cn_img/$rt[cnimg]",'lf');
			} else {
				$rt['cnimg'] = $GLOBALS['imgpath'] . '/g/groupnopic.gif';
			}
			$rt['friends'] = array();
			$group[$rt['id']] = $rt;
		}
		if ($group) {
			$query = $db->query("SELECT uid,username,colonyid FROM pw_cmembers WHERE uid IN (" . pwImplode($friends) . ') AND colonyid IN(' . pwImplode(array_keys($group)) . ") AND ifadmin<>'-1'");
			while ($rt = $db->fetch_array($query)) {
				$num = $group[$rt['colonyid']]['ifadd'] ? 2 : 3;
				if ($rt['uid'] != $winduid && count($group[$rt['colonyid']]['friends']) < $num) {
					$group[$rt['colonyid']]['friends'][$rt['uid']] = $rt['username'];
				}
			}
		}
	}
	$u	= $winduid;
	$username = $windid;

//	require_once(M_P.'require/header.php');
//	require_once PrintEot('m_groups');
//	footer();
	list($isheader,$isfooter,$tplname,$isleft) = array(true,true,"m_groups",true);

} elseif ($a == 'create') {

	banUser();
	!$o_newcolony && Showmsg('colony_reglimit');
	if($o_groups && strpos($o_groups,','.$groupid.',') === false){
		Showmsg('colony_groupright');
	}
	require_once(R_P.'require/credit.php');
	$o_groups_creditset['Creategroup'] = @array_diff($o_groups_creditset['Creategroup'],array(0));
	
	$costs = '';
	if (!empty($o_groups_creditset['Creategroup']) && is_array($o_groups_creditset['Creategroup'])) {
		foreach ($o_groups_creditset['Creategroup'] as $key => $value) {
			if ($value > 0) {
				$moneyname = $credit->cType[$key];
				if ($value > $credit->get($winduid,$key)) {
                    $GLOBALS['o_createmoney'] = $value;
					Showmsg('colony_creatfailed');
				}
				$unit = $credit->cUnit[$key];
				$value>0 && $costs .= $value.$unit.$moneyname.",";
			}
		}
	}
	$costs = trim($costs,",");
	include_once Pcv(D_P."data/groupdb/group_$groupid.php");
	if ($_G['allowcreate'] && $_G['allowcreate'] <= $db->get_value("SELECT COUNT(*) AS sum FROM pw_colonys WHERE admin=" . pwEscape($windid))) 	{
		Showmsg('colony_numlimit');
	}

	if (empty($_POST['step'])) {

		$u = $winduid;
		$username = $windid;
		$o_cate = array();
		include_once(D_P . 'data/bbscache/forum_cache.php');
		if(is_array($o_classdb)){
			foreach ($o_classdb as $key => $value) {
					$o_cate[$forum[$key]['fup']][$key] = $value;
			}
		}
		
		$cnimg_1 = array();
		$filetype = (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype));
		$default_type = array('gif','jpg','jpeg','bmp','png');
		foreach ($default_type as $value) {
			$cnimg_1[$value] = $o_imgsize ? $o_imgsize : $filetype[$value];
		}

		$jsStyle = pwJsonEncode($o_styledb);
		$jsStyleRelation = pwJsonEncode($o_style_relation);

		list($isheader,$isfooter,$tplname,$isleft) = array(true,true,"m_groups",true);

	} else {

		require_once(R_P.'require/postfunc.php');
		PostCheck(1,$o_groups_gdcheck,$o_groups_qcheck);

		InitGP(array('cname','descrip'),'P');
		InitGP(array('cid','firstgradestyle','secondgradestyle'), 'P', 2);

		(!$cname || strlen($cname) > 50) && Showmsg('colony_emptyname');
		$descrip = str_replace('&#61;' , '=', $descrip);
		strlen($descrip) > 255 && Showmsg('colony_descrip');
		//!$cid && Showmsg('colony_class');

		require_once(R_P . 'require/bbscode.php');
		$wordsfb = L::loadClass('FilterUtil', 'filter');
		if (($banword = $wordsfb->comprise($cname)) !== false) {
			Showmsg('title_wordsfb');
		}
		if (($banword = $wordsfb->comprise($descrip)) !== false) {
			Showmsg('title_wordsfb');
		}
		$styleid = 0;
		$styles = array();
		if ($o_styledb) {
			if (!isset($o_style_relation[$firstgradestyle])) {
				Showmsg('请选择分类!');
			}
			if (empty($o_style_relation[$firstgradestyle])) {
				$styleid = $firstgradestyle;
				array_push($styles,$firstgradestyle);
			} else {
				!in_array($secondgradestyle, $o_style_relation[$firstgradestyle]) && Showmsg('请选择二级分类!');
				$styleid = $secondgradestyle;
				array_push($styles,$firstgradestyle,$secondgradestyle);
			}
		}
		/*
		if (empty($cid) || !isset($o_classdb[$cid])) {
			$cid = 0;
		}
		*/
		$rt = $db->get_one("SELECT id FROM pw_colonys WHERE cname=".pwEscape($cname));
		$rt['id'] > 0 && Showmsg('colony_samename');
		//积分变动
		if (!empty($o_groups_creditset['Creategroup'])) {
			$creditset = getCreditset($o_groups_creditset['Creategroup'],false);
			$credit->sets($winduid,$creditset,true);
			updateMemberid($winduid);
		}
		if ($creditlog = $o_groups_creditlog) {
			addLog($creditlog['Creategroup'],$windid,$winduid,'groups_Creategroup');
		}

		@asort($o_groups_levelneed);
		$commonLevel = key($o_groups_levelneed);
		empty($commonLevel) && Showmsg("系统未创建群组等级,无法创建群组！");

		InitGP(array('title1','title2','title3','title4'));
		$titlefont = Char_cv("$title1~$title2~$title3~$title4~$title5~$title6~");
		$db->update("INSERT INTO pw_colonys SET " . pwSqlSingle(array(
				'cname'		=> $cname,
				//'classid'	=> $cid,
				'styleid'	=> $styleid,
				'commonlevel' => $commonLevel,
				'admin'		=> $windid,
				'members'	=> 1,
				'ifcheck'	=> 2,
				'createtime'=> $timestamp,
				'descrip'	=> $descrip,
				'titlefont' => $titlefont
		)));
		$cyid = $db->insert_id();
		$db->update("UPDATE pw_cnstyles SET csum=csum+1 WHERE id IN (" . pwImplode($styles) . ')');

		require_once(A_P . 'groups/lib/imgupload.class.php');
		$img = new CnimgUpload($cyid);
		PwUpload::upload($img);
		pwFtpClose($ftp);
		if ($cnimg = $img->getImgUrl()) {
			$cnimg = substr(strrchr($cnimg,'/'),1);
			$db->update("UPDATE pw_colonys SET cnimg=".pwEscape($cnimg)." WHERE id=".pwEscape($cyid));
		}

		$db->update("INSERT INTO pw_cmembers SET " . pwSqlSingle(array(
				'uid'		=> $winduid,
				'username'	=> $windid,
				'ifadmin'	=> 1,
				'colonyid'	=> $cyid,
				'addtime'	=> $timestamp
		)));
		
		updateUserAppNum($winduid,'group');
		refreshto("apps.php?q=group&cyid=$cyid&a=set",'colony_regsuccess');
	}
} elseif ($a == 'checkcname') {
	define('AJAX',1);
	InitGP(array('cname'));
	$ckcname = $db->get_value("SELECT cname FROM pw_colonys WHERE cname=".pwEscape($cname));
	if(empty($ckcname)) {
		echo "ok";
	}
	ajax_footer();
}

require_once PrintEot('m_groups');
pwOutPut();
?>