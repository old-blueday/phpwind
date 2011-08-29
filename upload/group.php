<?php
define('APP_GROUP',1);
if (isset($_POST['ajax'])) {
	define('AJAX', '1');
}
require_once('global.php');
!$db_groups_open && Showmsg('groups_close');
//* include_once pwCache::getPath(D_P.'data/bbscache/o_config.php');
pwCache::getData(D_P.'data/bbscache/o_config.php');
S::gp(array('q', 'step'));
$step && $step = (int) $step;

if (empty($q)) {
	require_once(R_P . 'u/require/core.php');
	require_once(R_P . 'apps/groups/lib/colonys.class.php');
	$do = 'reloaddelay';
	$colonyServer = new PW_Colony();
	$weiboService = L::loadClass('weibo','sns');/* @var $weiboService PW_Weibo */
	$randColonys = $colonyServer->getRandColonys(6);
	$newmembers = $colonyServer->getNewMembers(12);
	$weiboList = $weiboService->getConloysWeibos('nocyids', 1, 10);
} elseif ($q == 'create') {
	!$winduid && Showmsg('not_login');
	!$o_newcolony && Showmsg('colony_reglimit');
	require_once(R_P . 'u/require/core.php');
	require_once(R_P . 'require/functions.php');
	banUser();
	if($o_groups && strpos($o_groups,','.$groupid.',') === false){
		Showmsg('colony_groupright');
	}
	$db_question && $o_groups_qcheck && $qkey = array_rand($db_question);
	require_once(R_P.'require/credit.php');
	$o_groups_creditset['Creategroup'] = @array_diff($o_groups_creditset['Creategroup'],array(0));
	$costs = '';
	if (!empty($o_groups_creditset['Creategroup']) && is_array($o_groups_creditset['Creategroup'])) {
		$createGroupCredit = 1;
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
	//* include_once pwCache::getPath(S::escapePath(D_P."data/groupdb/group_$groupid.php"));
	pwCache::getData(S::escapePath(D_P."data/groupdb/group_$groupid.php"));
	if ($_G['allowcreate'] && $_G['allowcreate'] <= $db->get_value("SELECT COUNT(*) AS sum FROM pw_colonys WHERE admin=" . S::sqlEscape($windid))) 	{
		Showmsg('colony_numlimit');
	}
	if (empty($step)) {
		$u = $winduid;
		$username = $windid;
		$o_cate = array();
		//* include_once pwCache::getPath(D_P . 'data/bbscache/forum_cache.php');
		pwCache::getData(D_P . 'data/bbscache/forum_cache.php');
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
		//list($isheader,$isfooter,$tplname,$isleft) = array(true,true,"m_groups",true);
	} else {
		require_once(R_P.'require/postfunc.php');
		PostCheck(1,$o_groups_gdcheck,$o_groups_qcheck);
		S::gp(array('cname','descrip'),'P');
		S::gp(array('cid','firstgradestyle','secondgradestyle'), 'P', 2);
		(!$cname || strlen(stripslashes(html_entity_decode($cname,ENT_QUOTES))) > 20) && Showmsg('colony_emptyname');
		$descrip = str_replace('&#61;' , '=', $descrip);
		strlen(stripslashes(html_entity_decode($descrip,ENT_QUOTES))) > 255 && Showmsg('colony_descrip');
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
		$rt = $db->get_one("SELECT id FROM pw_colonys WHERE cname=".S::sqlEscape($cname));
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
		S::gp(array('title1','title2','title3','title4'));
		$titlefont = S::escapeChar("$title1~$title2~$title3~$title4~$title5~$title6~");
		/**
		$db->update("INSERT INTO pw_colonys SET " . S::sqlSingle(array(
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
		**/
		$cyid = pwQuery::insert('pw_colonys', array(
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
		));
		require_once(A_P . 'groups/lib/imgupload.class.php');
		$img = new CnimgUpload($cyid);
		PwUpload::upload($img);
		pwFtpClose($ftp);
		$db->update("UPDATE pw_cnstyles SET csum=csum+1 WHERE id IN (" . S::sqlImplode($styles) . ')');
		if ($cnimg = $img->getImgUrl()) {
			$cnimg = substr(strrchr($cnimg,'/'),1);
			//* $db->update("UPDATE pw_colonys SET cnimg=".S::sqlEscape($cnimg)." WHERE id=".S::sqlEscape($cyid));
			$db->update(pwQuery::buildClause("UPDATE :pw_table SET cnimg=:cnimg WHERE id=:id", array('pw_colonys',$cnimg,$cyid)));
		}
		/**
		$db->update("INSERT INTO pw_cmembers SET " . S::sqlSingle(array(
				'uid'		=> $winduid,
				'username'	=> $windid,
				'ifadmin'	=> 1,
				'colonyid'	=> $cyid,
				'addtime'	=> $timestamp
		)));
		**/
		pwQuery::insert('pw_cmembers', array(
				'uid'		=> $winduid,
				'username'	=> $windid,
				'ifadmin'	=> 1,
				'colonyid'	=> $cyid,
				'addtime'	=> $timestamp
		));
		
		updateUserAppNum($winduid,'group');
		$url = "apps.php?q=group&cyid=$cyid&a=set";
		$msg = defined('AJAX') ?  "success\t".$url : 'colony_regsuccess';
		refreshto("apps.php?q=group&cyid=$cyid&a=set",$msg);
	}
} elseif ($q == 'all') {
	S::gp(array('page', 'styleid', 'friends', 'members'), null, 2);
	S::gp(array('keyword'));
	$friends && !$winduid && Showmsg('not_login');
	require_once(R_P . 'u/require/core.php');
	require_once(R_P . 'apps/groups/lib/colony.class.php');
	require_once(R_P . 'require/functions.php');
	$atc_name = getLangInfo('app','group');
	$cMembers = $group = array();
	$sqlsel   = $sqltab = '';

	if ($styleid) {
		$tmpStyle = array();
		if ($o_styledb[$styleid]['upid'] == '0') {
			foreach ($o_styledb as $k => $v) {
				if ($v['upid'] == $styleid) {
					$tmpStyle[] = $k;
				}
			}
		}
		$sqlsel .= ' AND c.styleid' . ($tmpStyle ? ' IN(' . S::sqlImplode($tmpStyle) . ')' : '=' . S::sqlEscape($styleid));
	}
	if ($members) {
		$sqlsel .= ' AND c.members>=' . S::sqlEscape($members);
	}
	if ($keyword) {
		$sqlsel .= ' AND c.cname LIKE ' . S::sqlEscape("%" . $keyword . "%");
	}
	if ($friends) {
		$isFriends = $friends;
		$friends = getFriends($winduid);
		unset($friends[$winduid]);
		$uids = $friends ? array_keys($friends) : array(0);
		$sqltab .= ' LEFT JOIN pw_cmembers cm ON c.id=cm.colonyid';
		$sqlsel .= ' AND cm.uid IN(' . S::sqlImplode($uids) . ')';
	}
	$total = $db->get_value("SELECT COUNT(DISTINCT c.id) AS sum FROM pw_colonys c {$sqltab} WHERE 1 {$sqlsel}");

	if ($total) {
		require_once(R_P . 'require/bbscode.php');
		list($pages, $limit) = pwLimitPages($total, $page, "group.php?q=all" . ($members ? "&members=$members" : '') . ($isFriends ? "&friends=$isFriends" : '') . "&keyword=".rawurlencode($keyword)."&".($styleid?("styleid=".$styleid):"")."&");
		$query = $db->query("SELECT DISTINCT c.* FROM pw_colonys c {$sqltab} WHERE 1 {$sqlsel} ORDER BY c.id DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			if ($rt['cnimg']) {
				list($rt['cnimg']) = geturl("cn_img/$rt[cnimg]", 'lf');
			} else {
				$rt['cnimg'] = $GLOBALS['imgpath'] . '/g/groupnopic.gif';
			}
			$rt['cname'] = str_replace($keyword, '<font color="#FF0000">' . $keyword . '</font>', $rt['cname']);
			$rt['descrip'] = str_replace($keyword, '<font color="#FF0000">' . $keyword . '</font>', stripWindCode($rt['descrip']));
			$rt['colonyNums'] = PwColony::calculateCredit($rt);
			$rt['createtime'] = get_date($rt['createtime'], 'Y-m-d');
			$group[$rt['id']] = $rt;
		}
	}
	$colonyids = S::sqlImplode(array_keys($group));
	if ($colonyids) {
		$query = $db->query("SELECT id,ifadmin,colonyid FROM pw_cmembers WHERE colonyid IN ($colonyids) AND uid=" . S::sqlEscape($winduid,false));
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

	require_once(M_P.'require/header.php');
	require_once PrintEot('m_groups');
	footer();
	list($isheader,$isfooter,$tplname,$isleft) = array(true,true,"m_groups",true);
*/
}
require_once(R_P . 'require/header.php');
require_once PrintEot('group');
footer();
?>