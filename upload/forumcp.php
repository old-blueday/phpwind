<?php
require_once('global.php');
require_once(R_P.'require/functions.php');
require_once(R_P.'require/forum.php');
include_once(D_P.'data/bbscache/forum_cache.php');
$groupid == 'guest' && Showmsg('not_login');

$fiddb = array();
InitGP(array('action'));

if ($db_mode != 'bbs' && $db_bfn == 'index.php') {
	$db_bfn_temp = $db_bbsurl."/index.php?m=bbs";
} else {
	$db_bfn_temp = $db_bfn;
}

$isGM = CkInArray($windid,$manager);
if ($action) {
	!$fid && Showmsg('data_error');
	if (!($forums = L::forum($fid))) {
		Showmsg('data_error');
	}
	(!$forums || $forums['type'] == 'category') && Showmsg('data_error');
	$isBM = admincheck($forums['forumadmin'],$forums['fupadmin'],$windid);
	if (!in_array($groupid,array('3','4')) && !$isBM && !$isGM) {
		Showmsg('not_forumadmin');
	}
	$forumset = $forums['forumset'];
	$first_admin = $db_adminset && strpos($forums['forumadmin'],','.$windid.',')===0 ? 1 : 0;
} else {
	$query = $db->query("SELECT fid,forumadmin,fupadmin FROM pw_forums WHERE cms=0 AND type!='category'");
	while ($rt = $db->fetch_array($query)) {
		if (in_array($groupid,array('3','4')) || admincheck($rt['forumadmin'],$rt['fupadmin'],$windid) || $isGM) {
			$fiddb[] = $rt['fid'];
		}
	}
	!$fiddb && Showmsg('not_forumadmin');
}
require_once(R_P.'require/header.php');

if (!$action) {

	$forum_name = '';
	$fids		= pwImplode($fiddb);
	$froumdb	= array();
	$query = $db->query("SELECT * FROM pw_forums f LEFT JOIN pw_forumdata fd USING(fid) WHERE f.fid IN($fids)");
	while ($rt = $db->fetch_array($query)) {
		$forumdb[] = $rt;
	}
	$i = count($forumdb);
	if ($i > 4) {
		$j_sum = 4;
		$j_wid = '25%';
	} else {
		$j_sum = $i;
		$j_wid = (100/$i).'%';
	}
	require_once(PrintEot('forumcp'));footer();

} elseif ($action == 'edit') {

	$forum_name = $forums['name'];
	InitGP(array('type'));
	!$type && $type = 'notice';

	if ($type == 'notice') {

		$annoucedb = array();
		$pages = ''; $page = $_GET['page']; (int)$page<1 && $page = 1;
		$query = $db->query('SELECT aid,ifopen,vieworder,author,subject,startdate,enddate FROM pw_announce WHERE fid='.pwEscape($fid).' ORDER BY fid,vieworder,startdate DESC '.pwLimit(($page-1)*$db_perpage,$db_perpage));
		while ($rt = $db->fetch_array($query)) {
			$rt['subject'] = substrs($rt['subject'],30);
			$rt['starttime'] = $rt['startdate'] ? get_date($rt['startdate'],'Y-m-d H:i') : '--';
			$rt['endtime'] = $rt['enddate'] ? get_date($rt['enddate'],'Y-m-d H:i') : '--';
			$annoucedb[] = $rt;
		}
		$db->free_result($query);
		$count = $db->get_value('SELECT COUNT(*) FROM pw_announce WHERE fid='.pwEscape($fid));
		if ($count > $db_perpage) {
			require_once(R_P.'require/forum.php');
			$pages = numofpage($count,$page,ceil($count/$db_perpage), "forumcp.php?action=edit&fid=$fid&type=$type&");
		}
		require_once(PrintEot('forumcp'));footer();

	} elseif ($type == 'n_del') {

		PostCheck();
		$aid = (int)$_GET['aid'];
		$rt = $db->get_one('SELECT aid,fid,ifopen FROM pw_announce WHERE aid='.pwEscape($aid));
		(!$rt['aid'] || $rt['fid']!=$fid) && Showmsg('data_error');
		$db->update('DELETE FROM pw_announce WHERE aid='.pwEscape($aid));
		if ($rt['ifopen']) {
			require_once(R_P.'require/updatenotice.php');
			updatecache_i_i($fid);
		}
		refreshto("forumcp.php?action=edit&fid=$fid",'operate_success');

	} elseif ($type == 'n_order') {

		PostCheck();
		!is_array($vieworder = $_POST['vieworder']) && $vieworder = array();
		$updatedb = array();
		foreach ($vieworder as $key => $value) {
			if (is_numeric($key)) {
				$value = (int)$value;
				$updatedb[$value] .= ",'$key'";
			}
		}
		foreach ($updatedb as $key => $value) {
			$value && $db->update("UPDATE pw_announce SET vieworder='$key' WHERE aid IN (".substr($value,1).')');
		}
		require_once(R_P.'require/updatenotice.php');
		updatecache_i_i($fid);
		refreshto("forumcp.php?action=edit&fid=$fid",'operate_success');

	} elseif ($type == 'add' || $type == 'edit') {
		InitGP(array('aid'),'GP',2);

		if (empty($_POST['step'])) {

			$ifopen_Y = 'CHECKED'; $vieworder = (int)$vieworder;
			$ifopen_N = $subject = $atc_content = $enddate = '';
			$startdate = get_date($timestamp,'Y-m-d H:i');
			if ($type == 'edit') {
				$db_redundancy = 0;
				$rt = $db->get_one('SELECT aid,fid,ifopen,vieworder,startdate,enddate,subject,content FROM pw_announce WHERE aid='.pwEscape($aid));
				!$rt['aid'] && Showmsg('data_error');
				extract($rt,EXTR_OVERWRITE);
				if (!$ifopen) {
					$ifopen_Y = '';
					$ifopen_N = 'CHECKED';
				}
				$startdate && $startdate = get_date($startdate,'Y-m-d H:i'); $enddate && $enddate = get_date($enddate,'Y-m-d H:i');
				$atc_content = $content;
			}
			require_once(PrintEot('forumcp'));footer();

		} else {

			PostCheck();
			!$fid && Showmsg('annouce_fid');
			InitGP(array('startdate','enddate','atc_title'),'P');
			$startdate = $startdate ? PwStrtoTime($startdate) : $timestamp;
			$enddate = $enddate ? PwStrtoTime($enddate) : '';
			$enddate && $enddate<=$startdate && Showmsg('annouce_time');
			InitGP(array('ifopen','vieworder'),'P',2);
			$atc_content = trim(Char_cv($_POST['atc_content']));
			if ($type == 'add') {
				!$atc_title && Showmsg('annouce_title');
				!$atc_content && Showmsg('annouce_content');

				$pwSQL = pwSqlSingle(array(
					'fid'		=> $fid,
					'ifopen'	=> $ifopen,
					'vieworder'	=> $vieworder,
					'author'	=> $windid,
					'startdate'	=> $startdate,
					'enddate'	=> $enddate,
					'url'		=> $url,
					'subject'	=> $atc_title,
					'content'	=> $atc_content
				));
				$db->update("INSERT INTO pw_announce SET $pwSQL");
				if ($ifopen && (!$enddate || $enddate>=$timestamp)) {
					require_once(R_P.'require/updatenotice.php');
					updatecache_i_i($fid);
				}
			} else {
				$rt = $db->get_one('SELECT aid,fid,content FROM pw_announce WHERE aid='.pwEscape($aid));
				!$atc_title && Showmsg('annouce_title');
				!$atc_content && Showmsg('annouce_content');

				(!$rt['aid'] || $rt['fid']!=$fid) && Showmsg('data_error');
				$pwSQL = pwSqlSingle(array(
					'ifopen'	=> $ifopen,
					'vieworder'	=> $vieworder,
					'startdate'	=> $startdate,
					'enddate'	=> $enddate,
					'url'		=> $url,
					'subject'	=> $atc_title,
					'content'	=> $atc_content
				));
				$db->update("UPDATE pw_announce SET $pwSQL WHERE aid=".pwEscape($aid));
				require_once(R_P.'require/updatenotice.php');
				updatecache_i_i($fid);
			}
			refreshto("forumcp.php?action=edit&fid=$fid",'operate_success');
		}
	} elseif ($type == 'report') {

		InitGP(array('page'),'GP',2);
		$page < 1 && $page = 1;
		$limit = pwLimit(($page-1)*$db_perpage,$db_perpage);

		if($forums['childid'] == 1) {
			foreach ($forum as $key => $value) {
				if($value['fup'] == $fid){
					$fiddb[] = $key;
				}
				if(in_array($value['fup'],$fiddb)) {
					$fiddb[] = $key;
				}
			}
		}
		$fiddb = array_merge(array($fid),$fiddb);

		$rt = $db->get_one('SELECT COUNT(*) AS count FROM pw_report r LEFT JOIN pw_threads t ON t.tid=r.tid WHERE t.fid IN('.pwImplode($fiddb).')');
		$sum = $rt['count'];
		$numofpage = ceil($sum/$db_perpage);
		$pages = numofpage($sum,$page,$numofpage,"forumcp.php?action=edit&type=report&fid=$fid&");

		$query = $db->query('SELECT r.*,m.username,t.fid FROM pw_report r LEFT JOIN pw_members m ON m.uid=r.uid LEFT JOIN pw_threads t ON t.tid=r.tid WHERE t.fid IN ('.pwImplode($fiddb).') ORDER BY id '.$limit);
		while ($rt = $db->fetch_array($query)) {
			$rt['fname'] = $forum[$rt['fid']]['name'];
			$reportdb[] = $rt;
		}
		require_once(PrintEot('forumcp'));footer();

	} elseif ($type == 'f_type') {

		if (!($foruminfo = L::forum($fid))) {
			Showmsg('data_error');
		}
		$forumset = $foruminfo['forumset'];

		if (empty($_POST['step'])) {

			$forumset['addtpctype'] ? $addtpctype_Y='checked' : $addtpctype_N='checked';
			$t_type = (int)$foruminfo['t_type'];
			${'t_type_'.$t_type}='checked';

			//主题分类
			$query = $db->query("SELECT id,name,vieworder,upid FROM pw_topictype WHERE fid=".pwEscape($fid)." ORDER BY vieworder");
			while ($rt = $db->fetch_array($query)) {
				$rt['name'] = str_replace(array('<','>','"',"'"),array("&lt;","&gt;","&quot;","&#39;"),$rt['name']);
				if($rt['upid'] == 0) {
					$typedb[$rt['id']] = $rt;
				} else {
					$subtypedb[$rt['id']] = $rt;
				}
			}

			require_once(PrintEot('forumcp'));footer();

		} else {

			PostCheck();
			Add_S($forumset);
			InitGP(array('t_view_db','new_t_view_db','new_t_sub_view_db','addtpctype'),'P');
			InitGP(array('t_db','new_t_db','new_t_sub_db','f_type','t_type'),'P',0);
			$temptype = array('t_db','new_t_db','new_t_sub_db');
			empty($t_db) && $t_db = array();
			empty($new_t_db) && $new_t_db = array();
			empty($new_t_sub_db) && $new_t_sub_db = array();
			foreach ($t_db as $key => $value) {
				$value = str_replace(array('&#46;&#46;','&#41;','&#60;','&#61;'),array('..',')','<','='),$value);
				$t_db[$key] = $value;
			}


			//主题分类
			empty($t_db) && $t_db = array();
			empty($new_t_db) && $new_t_db = array();
			empty($new_t_sub_db) && $new_t_sub_db = array();

			//更新原有的分类
			foreach ($t_db as $key => $value) {
				if(empty($value)) continue;
				$db->update("UPDATE pw_topictype SET " . pwSqlSingle(array(
					'name'			=> $value,
					'vieworder'		=> $t_view_db[$key]
				)) . " WHERE id=".pwEscape($key));
			}

			//增加新分类

			foreach ($new_t_db as $key => $value) {
				if(empty($value)) continue;
				$value = str_replace(array('&#46;&#46;','&#41;','&#60;','&#61;'),array('..',')','<','='),$value);
				$typedb[] = array ('fid' => $fid,'name' => $value,'vieworder'=>$new_t_view_db[$key]);
			}
			if ($typedb) {
				$db->update("REPLACE INTO pw_topictype (fid,name,vieworder) VALUES " . pwSqlMulti($typedb));
			}
			//增加二级新分类
			foreach ($new_t_sub_db as $key => $value) {
				foreach ($value as $k => $v) {
					if (empty($v)) continue;
					$v = str_replace(array('&#46;&#46;','&#41;','&#60;','&#61;'),array('..',')','<','='),$v);
					$subtypedb[] = array ('fid' => $fid,'name' => $v,'vieworder'=>$new_t_sub_view_db[$key][$k],'upid'=>$key);
				}
			}
			if ($subtypedb) {
				$db->update("REPLACE INTO pw_topictype (fid,name,vieworder,upid) VALUES " . pwSqlMulti($subtypedb));
			}
            require_once (R_P.'admin/cache.php');
			if ($addtpctype != $forumset['addtpctype']) {
				$forumset['addtpctype'] = $addtpctype;
				$forumset = serialize($forumset);
				if ($foruminfo['fid']) {
					$db->update('UPDATE pw_forumsextra SET forumset='.pwEscape($forumset).' WHERE fid='.pwEscape($fid));
				} else {
					$db->update('INSERT INTO pw_forumsextra SET '.pwSqlSingle(array('fid'=>$fid,'forumset'=>$forumset)));
				}
				updatecache_forums($fid);
			}
			$foruminfo = L::forum($fid);
			if($t_type != $foruminfo['t_type']){
				$db->update("UPDATE pw_forums SET " . pwSqlSingle(array('t_type'=> $t_type)) . "WHERE fid=".pwEscape($fid));
			}
            updatecache_f();
			refreshto("forumcp.php?action=edit&type=f_type&fid=$fid",'operate_success');
		}
	} elseif ($type == 'reward') {

		InitGP(array('starttime','endtime','username'));
		InitGP(array('page'),'GP',2);
		$page < 1 && $page=1;
		$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";

		$sql = $url_a = '';
		$_POST['starttime'] && $starttime= PwStrtoTime($starttime);
		$_POST['endtime']   && $endtime  = PwStrtoTime($endtime);
		if ($username) {
			$sql.=' AND t.author='.pwEscape($username);
			$url_a.="username=".rawurlencode($username)."&";
		}
		if ($starttime) {
			$sql.=' AND t.postdate>'.pwEscape($starttime);
			$url_a.="starttime=$starttime&";
		}
		if ($endtime) {
			$sql.=' AND t.postdate<'.pwEscape($endtime);
			$url_a.="endtime=$endtime&";
		}

		$rt = $db->get_one("SELECT COUNT(*) AS count FROM pw_threads t LEFT JOIN pw_reward r USING(tid) WHERE t.fid=".pwEscape($fid)." AND t.special='3' AND t.state='0' AND r.timelimit<".pwEscape($timestamp).$sql);
		$sum = $rt['count'];
		$numofpage = ceil($sum/$db_perpage);
		$pages = numofpage($sum,$page,$numofpage,"forumcp.php?action=edit&type=reward&fid=$fid&$url_a");

		$threaddb = array();
		$query = $db->query("SELECT t.tid,t.fid,t.subject,t.author,t.authorid,t.postdate,r.cbtype,r.cbval,r.catype,r.caval FROM pw_threads t LEFT JOIN pw_reward r USING(tid) WHERE t.fid=".pwEscape($fid)." AND t.special='3' AND t.state='0' AND r.timelimit>".pwEscape($timestamp).$sql." ORDER BY t.postdate $limit");

		while ($rt = $db->fetch_array($query)) {
			$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
			$rt['cbtype'] = is_numeric($rt['cbtype']) ? $_CREDITDB[$rt['cbtype']][0] : ${'db_'.$rt['cbtype'].'name'};
			$rt['catype'] = is_numeric($rt['catype']) ? $_CREDITDB[$rt['catype']][0] : ${'db_'.$rt['catype'].'name'};
			$rt['binfo'] = $rt['cbval']."&nbsp;".$rt['cbtype'];
			$rt['ainfo'] = $rt['caval']."&nbsp;".$rt['catype'];
			$threaddb[]  = $rt;
		}
		require_once(PrintEot('forumcp'));footer();

	} elseif ($type == 'thread') {

		InitGP(array('starttime','endtime','username','t_type'));
		InitGP(array('page'),'GP',2);
		$page < 1 && $page=1;
		$limit="LIMIT ".($page-1)*$db_perpage.",$db_perpage";
		$sql = $url_a = '';
		$_POST['starttime'] && $starttime= PwStrtoTime($starttime);
		$_POST['endtime']   && $endtime  = PwStrtoTime($endtime);
		if ($username) {
			$sql.=' AND author='.pwEscape($username);
			$url_a.="username=".rawurlencode($username)."&";
		}
		if ($starttime) {
			$sql.=' AND postdate>'.pwEscape($starttime);
			$url_a.="starttime=$starttime&";
		}
		if ($endtime) {
			$sql.=' AND postdate<'.pwEscape($endtime);
			$url_a.="endtime=$endtime&";
		}
		if ($t_type) {
			switch($t_type) {
				case 'digest':
					$sql.=" AND digest>'0'";
					break;
				case 'active':
					$sql.=" AND special='2'";
					break;
				case 'reward':
					$sql.=" AND special='3'";
					break;
				case 'sale':
					$sql.=" AND special='4'";
					break;
				default :
					$sql.=" AND digest>'0'";
			}
			$url_a.="t_type=$t_type&";
		}
		if ($sql) {
			$rt = $db->get_one("SELECT COUNT(*) AS sum FROM pw_threads WHERE fid=".pwEscape($fid)." AND ifcheck=1 $sql");
		} else {
			$rt = $db->get_one('SELECT topic AS sum FROM pw_forumdata WHERE fid='.pwEscape($fid));
		}
		$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage), "forumcp.php?action=edit&type=thread&fid=$fid&$url_a");
		$query = $db->query("SELECT tid,subject,author,authorid,postdate,titlefont,topped,digest FROM pw_threads WHERE fid=".pwEscape($fid)." AND ifcheck='1' $sql ORDER BY topped DESC,lastpost DESC $limit");
		$threaddb = array();
		while ($rt = $db->fetch_array($query)) {
			$rt['subject'] = substrs($rt['subject'],35);
			if ($rt['titlefont']) {
				$titledetail = explode("~",$rt['titlefont']);
				if ($titledetail[0])$rt['subject'] = "<font color=\"$titledetail[0]\">$rt[subject]</font>";
				if ($titledetail[1])$rt['subject'] = "<b>$rt[subject]</b>";
				if ($titledetail[2])$rt['subject'] = "<i>$rt[subject]</i>";
				if ($titledetail[3])$rt['subject'] = "<u>$rt[subject]</u>";
			}
			$rt['postdate'] = get_date($rt['postdate']);
			$threaddb[] = $rt;
		}
		require_once PrintEot('forumcp');footer();

	} elseif ($type == 'tcheck') {
		if(!$isGM && !pwRights($isBM,'viewcheck')) Showmsg('not_forumadmin');
		if (empty($_POST['step'])) {

			InitGP(array('starttime','endtime','username'));
			InitGP(array('page'),'GP',2);
			$page < 1 && $page=1;
			$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
			$sql = $url_a = '';
			$_POST['starttime'] && $starttime= PwStrtoTime($starttime);
			$_POST['endtime']   && $endtime  = PwStrtoTime($endtime);
			if ($username) {
				$sql.=' AND author='.pwEscape($username);
				$url_a.="username=".rawurlencode($username)."&";
			}
			if ($starttime) {
				$sql.=' AND postdate>'.pwEscape($starttime);
				$url_a.="starttime=$starttime&";
			}
			if ($endtime) {
				$sql.=' AND postdate<'.pwEscape($endtime);
				$url_a.="endtime=$endtime&";
			}
			$rt = $db->get_one("SELECT COUNT(*) AS sum FROM pw_threads WHERE fid=".pwEscape($fid)." AND ifcheck='0' $sql");
			$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage), "forumcp.php?action=edit&type=tcheck&fid=$fid&$url_a");

			$threaddb = $ttable_a = array();
			$query = $db->query("SELECT tid,subject,author,authorid,postdate FROM pw_threads WHERE fid=".pwEscape($fid)." AND ifcheck='0' $sql ORDER BY topped DESC,lastpost DESC $limit");
			while ($rt = $db->fetch_array($query)) {
				$rt['subject']	= substrs($rt['subject'],35);
				$rt['postdate']	= get_date($rt['postdate']);
				$threaddb[$rt['tid']] = $rt;
				$ttable_a[GetTtable($rt['tid'])][] = $rt['tid'];
			}
			include_once(D_P.'data/bbscache/wordsfb.php');
			foreach ($ttable_a as $pw_tmsgs=>$value) {
				$value = pwImplode($value);
				$query = $db->query("SELECT tid,content FROM $pw_tmsgs WHERE tid IN($value)");
				while ($rt = $db->fetch_array($query)) {
					$rt['content'] = str_replace("\n","<br>",$rt['content']);
					foreach ($alarm as $key=>$value) {
						$rt['content'] = str_replace($key,'<span style="background-color:#ffff66">'.$key.'</span>',$rt['content']);
					}
					$threaddb[$rt['tid']]['content'] = $rt['content'];
				}
			}
			require_once PrintEot('forumcp');footer();

		} elseif ($_POST['step'] == 3) {

			PostCheck();
			InitGP(array('selid','ifmsg'));
			$tids = array();
			foreach ($selid as $key=>$value) {
				is_numeric($value) && $tids[] = $value;
			}
			!$tids && Showmsg('id_error');
			//临时修改，待改进
			$threads = L::loadClass('Threads', 'forum');
			$threads->delThreads($tids);
			$db->update("UPDATE pw_threads SET ifcheck='1' WHERE tid IN(".pwImplode($tids).") AND fid=".pwEscape($fid));

			$checkarticle = L::loadClass('DelArticle', 'forum');
			$readdb = $checkarticle->getTopicDb("tid ".$checkarticle->sqlFormatByIds($tids));
			foreach ($readdb as $tpcData) {
				if ($ifmsg) {
					M::sendNotice(
						array($tpcData['author']),
						array(
							'title' => getLangInfo('writemsg','check_title'),
							'content' => getLangInfo('writemsg','check_content',array(
								'manager'	=> $windid,
								'fid'		=> $tpcData['fid'],
								'tid'		=> $tpcData['tid'],
								'subject'	=> $tpcData['subject'],
								'postdate'	=> get_date($tpcData['postdate']),
								'forum'		=> strip_tags($forum[$fid]['name']),
								'affect'    => "",
								'admindate'	=> get_date($timestamp),
								'reason'	=> stripslashes($atc_content)
							)),
						)
					);
				}
			}

			$threadList = L::loadClass("threadlist", 'forum');
			$threadList->refreshThreadIdsByForumId($fid);

			require_once(R_P.'require/updateforum.php');
			updateforum($fid);
			refreshto("forumcp.php?action=edit&type=$type&fid=$fid",'operate_success');

		} else {

			PostCheck();
			InitGP(array('selid','ifmsg'));
			$delids = '';
			foreach ($selid as $key => $value) {
				if (is_numeric($value)) {
					$delids .= $value.',';
				}
			}
			!$delids && Showmsg('mawhole_nodata');
			$delids = substr($delids,0,-1);

			$readdb = array();
			$delarticle = L::loadClass('DelArticle', 'forum');
			$readdb = $delarticle->getTopicDb("tid ".$delarticle->sqlFormatByIds($delids));

			//积分操作
			require_once(R_P.'require/credit.php');
			$creditOpKey = "Delete";
			$foruminfo = L::forum($fid);
			$creditset = $credit->creditset($foruminfo['creditset'],$db_creditset);
			$msg_delrvrc  = abs($creditset['Delete']['rvrc']);
			$msg_delmoney = abs($creditset['Delete']['money']);

			foreach ($readdb as $tpcData) {
				if ($ifmsg) {
					M::sendNotice(
						array($tpcData['author']),
						array(
							'title' => getLangInfo('writemsg','del_title'),
							'content' => getLangInfo('writemsg','del_content',array(
								'manager'	=> $windid,
								'fid'		=> $tpcData['fid'],
								'tid'		=> $tpcData['tid'],
								'subject'	=> $tpcData['subject'],
								'postdate'	=> get_date($tpcData['postdate']),
								'forum'		=> strip_tags($forum[$fid]['name']),
								'affect'    => "{$db_rvrcname}:-{$msg_delrvrc},{$db_moneyname}:-{$msg_delmoney}",
								'admindate'	=> get_date($timestamp),
								'reason'	=> stripslashes($atc_content)
							)),
						)
					);
				}
			}
			$delarticle->delTopic($readdb, false, true, array('reason' => $atc_content));

			# memcache refresh
			$threadList = L::loadClass("threadlist", 'forum');
			$threadList->refreshThreadIdsByForumId($fid);

			refreshto("forumcp.php?action=edit&type=$type&fid=$fid",'operate_success');
		}
	} elseif ($type == 'pcheck') {
		if(!$isGM && !pwRights($isBM,'viewcheck')) Showmsg('not_forumadmin');
		if (empty($_POST['step'])) {

			InitGP(array('starttime','endtime','username','ptable'));
			InitGP(array('page'),'GP',2);
			$page < 1 && $page=1;
			$limit = pwLimit(($page-1)*$db_perpage,$db_perpage);
			$sql = $url_a = '';
			$_POST['starttime'] && $starttime= PwStrtoTime($starttime);
			$_POST['endtime']   && $endtime  = PwStrtoTime($endtime);
			if ($username) {
				$sql.=' AND author='.pwEscape($username);
				$url_a.="username=".rawurlencode($username)."&";
			}
			if ($starttime) {
				$sql.=' AND postdate>'.pwEscape($starttime);
				$url_a.="starttime=$starttime&";
			}
			if ($endtime) {
				$sql.=' AND postdate<'.pwEscape($endtime);
				$url_a.="endtime=$endtime&";
			}
			if ($db_plist && count($db_plist)>1) {
				!is_numeric($ptable) && $ptable = $db_ptable;
				foreach ($db_plist as $key=>$val) {
					$name = $val ? $val : ($key != 0 ? getLangInfo('other','posttable').$key : getLangInfo('other','posttable'));
					$p_table .= "<option value=\"$key\">".$name."</option>";
				}
				$p_table  = str_replace("<option value=\"$ptable\">","<option value=\"$ptable\" selected>",$p_table);
				$url_a	 .= "ptable=$ptable&";
				$pw_posts = GetPtable($ptable);
			} else {
				$pw_posts = 'pw_posts';
			}
			$rt = $db->get_one("SELECT COUNT(*) AS sum FROM $pw_posts WHERE fid=".pwEscape($fid)." AND ifcheck='0' $sql");
			$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage), "forumcp.php?action=edit&type=$type&fid=$fid&$url_a");

			$postdb = array();
			include_once(D_P.'data/bbscache/wordsfb.php');
			$query = $db->query("SELECT pid,tid,subject,author,authorid,postdate,content FROM $pw_posts WHERE fid=".pwEscape($fid)." AND ifcheck='0' $sql $limit");
			while ($rt = $db->fetch_array($query)) {
				if ($rt['subject']) {
					$rt['subject'] = substrs($rt['subject'],35);
				} else {
					$rt['subject'] = substrs($rt['content'],35);
				}
				$rt['postdate'] = get_date($rt['postdate']);
				$rt['content']  = str_replace("\n","<br>",$rt['content']);
				foreach ($alarm as $key => $value) {
					$rt['content'] = str_replace($key,'<span style="background-color:#ffff66">'.$key.'</span>',$rt['content']);
				}
				$postdb[] = $rt;
			}
			require_once PrintEot('forumcp');footer();

		} elseif ($_POST['step'] == 3) {

			PostCheck();
			InitGP(array('selid','ptable'));
			$pids = '';
			foreach ($selid as $key => $value) {
				is_numeric($value) && $pids .= ($pids ? ',' : '').$value;
			}
			!$pids && Showmsg('id_error');

			$pw_posts = GetPtable($ptable);

			$update_tids = array();
			$query = $db->query("SELECT tid,pid,fid,aid,author,authorid,postdate,subject,content FROM $pw_posts WHERE fid='$fid' AND pid IN($pids)");
			while ($rt = $db->fetch_array($query)) {
				$update_tids[$rt['tid']] ++;
				if ($_POST['ifmsg']) {
					if (!$rt['subject']) {
						$rt['subject'] = substrs($rt['content'],35);
					}
					M::sendNotice(
						array($rt['author']),
						array(
							'title' => getLangInfo('writemsg','check_title'),
							'content' => getLangInfo('writemsg','check_content',array(
								'manager'	=> $windid,
								'fid'		=> $fid,
								'tid'		=> $rt['tid'],
								'subject'	=> substrs($rt['subject'],28),
								'postdate'	=> get_date($rt['postdate']),
								'forum'		=> strip_tags($forum[$fid]['name']),
								'affect'	=> "",
								'admindate'	=> get_date($timestamp),
								'reason'	=> stripslashes($atc_content)
							)),
						)
					);
				}
			}
			foreach ($update_tids as $key => $value) {
				$rt = $db->get_one("SELECT postdate,author FROM $pw_posts WHERE tid=".pwEscape($key)."ORDER BY postdate DESC LIMIT 1");
				$db->update("UPDATE pw_threads SET replies=replies+".pwEscape($value).",lastpost=".pwEscape($rt['postdate'],false).",lastposter =".pwEscape($rt['author'],false)."WHERE tid=".pwEscape($key));
				# memcache refresh
				$threadList = L::loadClass("threadlist", 'forum');
                $threadList->updateThreadIdsByForumId($fid,$tid);
			}

			$db->update("UPDATE $pw_posts SET ifcheck='1' WHERE pid IN($pids) AND fid='$fid'");
			require_once(R_P.'require/updateforum.php');
			updateforum($fid);
			refreshto("forumcp.php?action=edit&type=$type&fid=$fid",'operate_success');

		} else {
			PostCheck();
			InitGP(array('selid','ptable'));

			require_once(R_P.'require/credit.php');

			$creditOpKey = "Deleterp";
			$foruminfo = L::forum($fid);
			$creditset = $credit->creditset($foruminfo['creditset'],$db_creditset);

			$tids = '';
			$_tids = $_pids = $deluids = array();
			foreach ($selid as $key => $value) {
				is_numeric($value) && $tids .= ($tids ? ',' : '').$value;
			}

			!$tids && Showmsg('id_error');

			$msg_delrvrc  = abs($creditset['Deleterp']['rvrc']);
			$msg_delmoney = abs($creditset['Deleterp']['money']);

			$pw_posts = GetPtable($ptable);
			$query = $db->query("SELECT tid,pid,fid,aid,author,authorid,postdate,subject,content FROM $pw_posts WHERE fid='$fid' AND pid IN($tids)");
			while ($rt = $db->fetch_array($query)) {
				$rt['fid'] != $fid && Showmsg('admin_forum_right');
				$deluids[$rt['authorid']] = isset($deluids[$rt['authorid']]) ? $deluids[$rt['authorid']] + 1 : 1;

				//积分操作
				$credit->addLog("topic_$creditOpKey", $creditset[$creditOpKey], array(
					'uid' => $rt['authorid'],
					'username' => $rt['author'],
					'ip' => $onlineip,
					'fname' => strip_tags($foruminfo['name']),
					'operator' => $windid,
				));
				$credit->sets($rt['authorid'],$creditset[$creditOpKey],false);

				if ($rt['aid']) {
					$_tids[$rt['tid']] = $rt['tid'];
					$_pids[$rt['pid']] = $rt['pid'];
				}

				if ($_POST['ifmsg']) {
					if (!$rt['subject']) {
						$rt['subject'] = substrs($rt['content'],35);
					}
					M::sendNotice(
						array($rt['author']),
						array(
							'title' => getLangInfo('writemsg','delrp_title'),
							'content' => getLangInfo('writemsg','delrp_content',array(
								'manager'	=> $windid,
								'fid'		=> $fid,
								'tid'		=> $rt['tid'],
								'subject'	=> substrs($rt['subject'],28),
								'postdate'	=> get_date($rt['postdate']),
								'forum'		=> strip_tags($forum[$fid]['name']),
								'affect'	=> "{$db_rvrcname}：-{$msg_delrvrc}，{$db_moneyname}：-{$msg_delmoney}",
								'admindate'	=> get_date($timestamp),
								'reason'	=> stripslashes($atc_content)
							)),
						)
					);
				}

			}
			$credit->runsql();

			$db->update("DELETE FROM $pw_posts WHERE pid IN($tids)");
			if ($_tids && $_pids) {
				$pw_attachs = L::loadDB('attachs', 'forum');
				$attachdb = $pw_attachs->getByTid($_tids,$_pids);
				require_once(R_P.'require/updateforum.php');
				delete_att($attachdb);
				pwFtpClose($ftp);
			}
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			foreach ($deluids as $uid => $value) {
				$userService->updateByIncrement($uid, array(), array('postnum' => -$value));
			}
			refreshto("forumcp.php?action=edit&type=$type&fid=$fid",'operate_success');
		}
	} elseif ($type == 'commend') {

		if (empty($_POST['step'])) {

			$commend = array();
			if ($forumset['commendlist']) {
				$query = $db->query("SELECT tid,authorid,author,postdate,subject FROM pw_threads WHERE tid IN($forumset[commendlist])");
				while ($rt=$db->fetch_array($query)) {
					$rt['postdate'] = get_date($rt['postdate']);
					$commend[] = $rt;
				}
			}
			require_once PrintEot('forumcp');footer();

		} else {

			PostCheck();
			InitGP(array('selid'));
			foreach ($selid as $key => $value) {
				if (is_numeric($value)) {
					$forumset['commendlist'] = trim(str_replace(",$value,",",",",$forumset[commendlist],"),',');
				}
			}
			updatecommend($fid,$forumset);
			refreshto("forumcp.php?action=edit&type=$type&fid=$fid",'operate_success');
		}
	} elseif ($type == 'adminset') {

		!$first_admin && Showmsg('undefined_action');
		$admin_a = explode(',',trim($forums['forumadmin'],','));
		$firstadmin = $admin_a[0];
		$firstadmin != $windid && Showmsg('undefined_action');
		if (empty($_POST['step'])) {
			$s_admin = trim(str_replace(",$firstadmin,",',',$forums['forumadmin']),',');
			require_once(PrintEot('forumcp'));footer();

		} else {

			PostCheck();
			InitGP(array('forumadmin'),'P');

			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$errorname = '';
			if ($forums['forumadmin'] != stripslashes(",".$windid.",$forumadmin,")) {
				$newadmin = array('0'=>$windid);
				$newadmin_a = array_unique(explode(",",$forumadmin));
				foreach ($newadmin_a as $aid => $value) {
					$value = trim($value);
					if ($value && !in_array($value,$newadmin)) {
						$mb = $userService->getByUserName($value);
						if ($mb) {
							$newadmin[] = $value;
							if ($mb['groupid'] == -1) {
								$userService->update($mb['uid'], array('groupid' => 5));
								$pwSQL = pwSqlSingle(array(
									'uid'		=> $mb['uid'],
									'username'	=> $value,
									'groupid'	=> 5,
									'groups'	=> $mb['groups']
								));
								$db->update("REPLACE INTO pw_administrators SET $pwSQL");
							} elseif ($mb['groupid'] <> 5 && strpos($mb['groups'],',5,')===false) {
								$mb['groups'] = $mb['groups'] ? $mb['groups'].'5,' : ",5,";
								$userService->update($mb['uid'], array('groups' => $mb['groups']));
								$pwSQL = pwSqlSingle(array(
									'uid'		=> $mb['uid'],
									'username'	=> $value,
									'groupid'	=> $mb['groupid'],
									'groups'	=> $mb['groups']
								));
								$db->update("REPLACE INTO pw_administrators SET $pwSQL");
							}
						} else {
							$errorname .= ','.$value;
						}
					}
				}
				$oldfadmin = explode(',',trim($forums['forumadmin'],','));
				if ($oldfadmin) {
					$f_admin = array();
					$query = $db->query("SELECT forumadmin FROM pw_forums WHERE fid<>".pwEscape($fid)." AND forumadmin<>''");
					while ($rt = $db->fetch_array($query)) {
						foreach (explode(",",$rt['forumadmin']) as $key=>$value) {
							if ($value = trim($value)) {
								$f_admin[] = $value;
							}
						}
					}
					$f_admin = array_unique($f_admin);

					foreach ($userService->getByUserNames($oldfadmin) as $rt) {
						if (!in_array($rt['username'],$newadmin) && !in_array($rt['username'],$f_admin)) {
							if ($rt['groupid']=='5') {
								$userService->update($rt['uid'], array('groupid' => -1));
								$rt['groupid'] = -1;
							} else {
								$rt['groups'] = str_replace(',5,',',',$rt['groups']);
								$rt['groups']==',' && $rt['groups'] = '';
								$userService->update($rt['uid'], array('groups' => $rt['groups']));
							}
							if (in_array($rt['groupid'],array('-1','6','7')) && $rt['groups']=='') {
								$db->update("DELETE FROM pw_administrators WHERE uid=".pwEscape($rt['uid'],false));
							} else {
								$db->update("REPLACE INTO pw_administrators SET".pwSqlSingle(array(
									'uid'		=> $rt['uid'],
									'username'	=> $rt['username'],
									'groupid'	=> $rt['groupid'],
									'groups'	=> $rt['groups']
								),false));
							}
						}
					}
				}
				$newadmin = addslashes(implode(',',$newadmin));
				$db->update("UPDATE pw_forums SET forumadmin=',$newadmin,' WHERE fid=".pwEscape($fid));
				require_once R_P.'admin/cache.php';
				updatecache_forums($fid);
				updatecache_fd(true);
			}
			if ($errorname) {
				$errorname = Char_cv(substr($errorname,1));
				Showmsg('user_not_exists');
			} else {
				refreshto("forumcp.php?action=edit&type=$type&fid=$fid",'operate_success');
			}
		}
	} elseif ($type == 'trecycle' && $db_recycle) {

		require_once(R_P.'require/updateforum.php');
		require_once(R_P.'require/writelog.php');
		InitGP(array('page','step'),'GP',2);

		if (empty($step)) {

			InitGP(array('username','starttime','endtime','t_type'));
			$page<1 && $page = 1;
			$limit = pwLimit(($page-1)*$db_perpage,$db_perpage);

			$sql = $url_a = '';
			$starttime && $starttime= PwStrtoTime($starttime);
			$endtime   && $endtime  = PwStrtoTime($endtime);
			if ($username) {
				$sql.=' AND t.author='.pwEscape($username);
				$url_a.="username=".rawurlencode($username)."&";
			}
			if ($starttime) {
				$sql.=' AND t.postdate>'.pwEscape($starttime);
				$url_a.="starttime=$starttime&";
			}
			if ($endtime) {
				$sql.=' AND t.postdate<'.pwEscape($endtime);
				$url_a.="endtime=$endtime&";
			}
			if ($t_type) {
				switch($t_type) {
					case 'digest':
						$sql.=" AND t.digest>'0'";
						break;
					case 'active':
						$sql.=" AND t.special='2'";
						break;
					case 'reward':
						$sql.=" AND t.special='3'";
						break;
					case 'sale':
						$sql.=" AND t.special='4'";
						break;
					default :
				}
				${'t_type_'.$t_type} = 'selected';
				$url_a.="t_type=$t_type&";
			}
			$rt = $db->get_one("SELECT COUNT(*) AS sum FROM pw_recycle r LEFT JOIN pw_threads t USING(tid) WHERE r.pid='0' AND r.fid=".pwEscape($fid).$sql);
			$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage), "forumcp.php?action=edit&type=trecycle&fid=$fid&$url_a");

			$ttable_a = array();
			$query = $db->query("SELECT r.*,t.subject,t.author,t.authorid FROM pw_recycle r LEFT JOIN pw_threads t USING(tid) WHERE r.pid='0' AND r.fid=".pwEscape($fid).$sql." ORDER BY deltime DESC $limit");
			while ($rt = $db->fetch_array($query)) {
				$rt['deltime'] = get_date($rt['deltime']);
				$rt['subject'] = substrs($rt['subject'],50);
				$rt['fname']   = $forum[$rt['fid']]['name'];
				$recycledb[$rt['tid']] = $rt;
				$ttable_a[GetTtable($rt['tid'])][] = $rt['tid'];
			}
			foreach ($ttable_a as $pw_tmsgs => $value) {
				$value = pwImplode($value);
				$query = $db->query("SELECT tid,content FROM $pw_tmsgs WHERE tid IN($value)");
				while ($rt = $db->fetch_array($query)) {
					$rt['content'] = str_replace("\n","<br>",$rt['content']);
					$recycledb[$rt['tid']]['content'] = $rt['content'];
				}
			}
			require_once(PrintEot('forumcp'));footer();

		} elseif ($_POST['step'] == '1' && $forumset['recycle'] & 2) {

			PostCheck();
			InitGP(array('ids'),'P');
			count($ids) > 500 && Showmsg('forumcp_recycle_maxcount');
			recycle($ids);
			$logdb = array(
				'type'      => 'recycle',
				'username1' => '',
				'username2' => $windid,
				'field1'    => $fid,
				'field2'    => '',
				'field3'    => '',
				'descrip'   => 'recycle_topic_delete',
				'timestamp' => $timestamp,
				'ip'        => $onlineip,
				'affect'    => '',
				'forum'		=> $forum[$fid]['name'],
				'reason'	=> ''
			);
			writelog($logdb);
			refreshto("forumcp.php?action=edit&type=trecycle&fid=$fid",'operate_success');

		} elseif ($_POST['step'] == '2' && $forumset['recycle'] & 4) {

			PostCheck();
			InitGP(array('ids'),'P');
			count($ids) > 500 && Showmsg('forumcp_recycle_maxcount');
			$reids = $logdb = $ptable_a = array();
			foreach ($ids as $key => $value) {
				if (is_numeric($value)) {
					$reids[] = $value;
				}
			}
			!$reids && Showmsg('forumcp_recycle_nodata');

			$reids = pwImplode($reids);
			$query = $db->query("SELECT r.*,t.ptable FROM pw_recycle r LEFT JOIN pw_threads t ON r.tid=t.tid WHERE r.pid='0' AND r.tid IN ($reids)");
			$reids = $ptable_a = array();
			while ($read = $db->fetch_array($query)) {
				$read['fid'] != $fid && Showmsg('admin_forum_right');
				$ptable_a[$read['ptable']] = 1;
				$reids[] = $read['tid'];
			}
			if ($reids) {
				$pw_attachs = L::loadDB('attachs', 'forum');
				$pw_attachs->updateByTid($reids,array('fid'=>$fid));

				//临时修改，待改进
				$threads = L::loadClass('Threads', 'forum');
				$threads->delThreads($reids);
				$reids = pwImplode($reids);
				$db->update("UPDATE pw_threads SET ".pwSqlSingle(array('fid'=>$fid,'ifshield'=>0))."WHERE tid IN($reids)");
				$db->update("DELETE FROM pw_recycle WHERE tid IN ($reids)");
				foreach ($ptable_a as $key => $val) {
					$pw_posts = GetPtable($key);
					$db->update("UPDATE $pw_posts SET fid=".pwEscape($fid)."WHERE tid IN($reids)");
				}
			}
			updateforum($fid);
			$logdb = array(
				'type'      => 'recycle',
				'username1' => '',
				'username2' => $windid,
				'field1'    => $fid,
				'field2'    => '',
				'field3'    => '',
				'descrip'   => 'recycle_topic_restore',
				'timestamp' => $timestamp,
				'ip'        => $onlineip,
				'affect'    => '',
				'forum'		=> $forum[$fid]['name'],
				'reason'	=> ''
			);
			writelog($logdb);
			refreshto("forumcp.php?action=edit&type=trecycle&fid=$fid",'operate_success');

		} elseif ($step == '3' && $forumset['recycle'] & 8) {

			PostCheck();
			$ids = array();
			$flag = false;
			$query = $db->query("SELECT * FROM pw_recycle WHERE fid=".pwEscape($fid)." AND pid='0' LIMIT 100");
			while ($rt = $db->fetch_array($query)) {
				$flag || $flag = true;
				$ids[] = $rt['tid'];
			}
			if ($flag) {
				recycle($ids);
				refreshto("forumcp.php?action=edit&type=trecycle&fid=$fid&step=3&verify=$verifyhash", 'delete_recycle');
			} else {
				$logdb = array(
					'type'      => 'recycle',
					'username1' => '',
					'username2' => $windid,
					'field1'    => $fid,
					'field2'    => '',
					'field3'    => '',
					'descrip'   => 'recycle_topic_empty',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
					'affect'    => '',
					'forum'		=> $forum[$fid]['name'],
					'reason'	=> ''
				);
				writelog($logdb);
				refreshto("forumcp.php?action=edit&type=trecycle&fid=$fid",'operate_success');
			}
		}
	} elseif ($type == 'precycle' && $db_recycle) {

		require_once(R_P.'require/updateforum.php');
		require_once(R_P.'require/writelog.php');
		InitGP(array('ptable'));
		InitGP(array('step','page'),'GP',2);
		$db_perpage = 5;

		if (empty($step)) {

			InitGP(array('username','starttime','endtime','t_type'));
			$sql = $url_a = '';
			if ($db_plist && count($db_plist)>1) {
				!is_numeric($ptable) && $ptable = $db_ptable;
				foreach ($db_plist as $key=>$val) {
					$name = $val ? $val : ($key != 0 ? getLangInfo('other','posttable').$key : getLangInfo('other','posttable'));
					$p_table .= "<option value=\"$key\">".$name."</option>";
				}
				$p_table  = str_replace("<option value=\"$ptable\">","<option value=\"$ptable\" selected>",$p_table);

				$url_a	 .= "ptable=$ptable&";
				$pw_posts = GetPtable($ptable);
			} else {
				$pw_posts = 'pw_posts';
			}
			$starttime && $starttime= PwStrtoTime($starttime);
			$endtime   && $endtime  = PwStrtoTime($endtime);
			if ($username) {
				$sql.=' AND p.author='.pwEscape($username);
				$url_a.="username=".rawurlencode($username)."&";
			}
			if ($starttime) {
				$sql.=' AND p.postdate>'.pwEscape($starttime);
				$url_a.="starttime=$starttime&";
			}
			if ($endtime) {
				$sql.=' AND p.postdate<'.pwEscape($endtime);
				$url_a.="endtime=$endtime&";
			}
			if ($t_type) {
				switch($t_type) {
					case 'digest':
						$sql.=" AND t.digest>'0'";
						break;
					case 'active':
						$sql.=" AND t.special='2'";
						break;
					case 'reward':
						$sql.=" AND t.special='3'";
						break;
					case 'sale':
						$sql.=" AND t.special='4'";
						break;
					default :
						$sql.="";
				}
				${'t_type_'.$t_type} = 'selected';
				$url_a.="t_type=$t_type&";
			}

			(!is_numeric($page) || $page<1) && $page = 1;
			$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
			$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_recycle r LEFT JOIN $pw_posts p USING(pid) LEFT JOIN pw_threads t ON r.tid=t.tid WHERE r.fid=".pwEscape($fid)." AND r.pid>'0' AND p.fid='0' $sql");
			$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage), "forumcp.php?action=edit&type=precycle&fid=$fid&$url_a");

			$query = $db->query("SELECT r.*,p.author,p.authorid,p.content,t.subject FROM pw_recycle r LEFT JOIN $pw_posts p ON r.pid=p.pid LEFT JOIN pw_threads t ON r.tid=t.tid WHERE r.fid=".pwEscape($fid)." AND r.pid>'0' AND p.fid='0' $sql ORDER BY r.deltime DESC $limit");
			while ($rt = $db->fetch_array($query)) {
				$rt['deltime'] = get_date($rt['deltime']);
				$rt['subject'] = substrs($rt['subject'],50);
				$rt['content'] = str_replace("\n","<br>",$rt['content']);
				$rt['fname']   = $forum[$rt['fid']]['name'];
				$recycledb[]   = $rt;
			}
			require_once(PrintEot('forumcp'));footer();

		} elseif ($_POST['step'] == '1' && $forumset['recycle'] & 2) {

			PostCheck();
			InitGP(array('ids'),'P');
			count($ids) > 500 && Showmsg('forumcp_recycle_maxcount');
			$delids = array();
			foreach ($ids as $key => $value) {
				if (is_numeric($value)) {
					$delids[] = $value;
				}
			}
			$delids && $delids = pwImplode($delids);
			!$delids && Showmsg('forumcp_recycle_nodata');
			!is_numeric($ptable) && $ptable = $db_ptable;
			$pw_posts = GetPtable($ptable);

			$_pids = $_tids = array();
			$query = $db->query("SELECT * FROM pw_recycle WHERE pid IN($delids)");
			while ($read = $db->fetch_array($query)) {
				$read['fid'] != $fid && Showmsg('admin_forum_right');
				$_tids[$read['tid']] = $read['tid'];
				$_pids[$read['pid']] = $read['pid'];
			}
			if ($_pids) {
				$pw_attachs = L::loadDB('attachs', 'forum');
				$attachdb = $pw_attachs->getByTid($_tids,$_pids);
				require_once(R_P.'require/updateforum.php');
				delete_att($attachdb);
				pwFtpClose($ftp);

				$_pids = pwImplode($_pids);
				$db->update("DELETE FROM $pw_posts WHERE pid IN ($_pids)");
			}
			$logdb = array(
				'type'      => 'recycle',
				'username1' => '',
				'username2' => $windid,
				'field1'    => $fid,
				'field2'    => '',
				'field3'    => '',
				'descrip'   => 'recycle_reply_delete',
				'timestamp' => $timestamp,
				'ip'        => $onlineip,
				'affect'    => '',
				'forum'		=> $forum[$fid]['name'],
				'reason'	=> ''
			);
			writelog($logdb);
			refreshto("forumcp.php?action=edit&type=precycle&fid=$fid&ptable=$ptable",'operate_success');

		} elseif ($_POST['step'] == '2' && $forumset['recycle'] & 4) {

			PostCheck();
			InitGP(array('ids'),'P');
			count($ids) > 500 && Showmsg('forumcp_recycle_maxcount');
			$reids = array();
			foreach ($ids as $key => $value) {
				if (is_numeric($value)) {
					$reids[] = $value;
				}
			}
			$reids && $reids = pwImplode($reids);
			!$reids && Showmsg('forumcp_recycle_nodata');
			!is_numeric($ptable) && $ptable = $db_ptable;
			$pw_posts = GetPtable($ptable);
			$repliesnum = $ids = $delids = $rids = array();
			$articlenum = 0;
			$query = $db->query("SELECT r.*,t.fid as tfid FROM pw_recycle r LEFT JOIN pw_threads t ON r.tid=t.tid WHERE r.pid IN($reids)");
			while ($read = $db->fetch_array($query)) {
				$read['fid'] != $fid && Showmsg('admin_forum_right');
				if ($read['tfid']) {
					$ids[] = $read['pid'];
					$articlenum++;
				} else {
					$delids[] = $read['pid'];
				}
				$rids[] = $read['pid'];
				$repliesnum[$read['tid']]++;
			}
			if ($ids) {
				$ids = pwImplode($ids);
				$db->update("UPDATE $pw_posts p LEFT JOIN pw_recycle r ON p.pid=r.pid SET p.tid=r.tid,p.fid=r.fid WHERE p.pid IN ($ids)");
			}
			if ($delids) {
				$delids = pwImplode($delids);
				$db->update("UPDATE $pw_posts p LEFT JOIN pw_recycle r ON p.pid=r.pid SET p.tid=r.tid,p.fid='0' WHERE p.pid IN ($delids)");
			}
			if ($rids) {
				$rids = pwImplode($rids);
				$db->update("DELETE FROM pw_recycle WHERE pid IN ($ids)");
			}

			foreach ($repliesnum as $key => $val) {
				$db->update("UPDATE pw_threads SET replies=replies+".pwEscape($val)." WHERE tid=".pwEscape($key));
			}
			$articlenum && $db->update("UPDATE pw_forumdata SET article=article+".pwEscape($articlenum)." WHERE fid=".pwEscape($fid));
			$logdb = array(
				'type'      => 'recycle',
				'username1' => '',
				'username2' => $windid,
				'field1'    => $fid,
				'field2'    => '',
				'field3'    => '',
				'descrip'   => 'recycle_reply_restore',
				'timestamp' => $timestamp,
				'ip'        => $onlineip,
				'affect'    => '',
				'forum'		=> $forum[$fid]['name'],
				'reason'	=> ''
			);
			writelog($logdb);
			refreshto("forumcp.php?action=edit&type=precycle&fid=$fid&ptable=$ptable",'operate_success');

		} elseif($step == '3' && $forumset['recycle'] & 8){

			PostCheck();
			$_pids = $_tids = array();
			$flag = false;
			!is_numeric($ptable) && $ptable = $db_ptable;
			$pw_posts = GetPtable($ptable);
			$query = $db->query("SELECT * FROM pw_recycle WHERE fid=".pwEscape($fid)." AND pid>'0' LIMIT 100");
			while ($rt = $db->fetch_array($query)) {
				$flag || $flag = true;
				$_pids[$rt['pid']] = $rt['pid'];
				$_tids[$rt['tid']] = $rt['tid'];
			}
			if ($flag) {
				if ($_pids) {
					$pw_attachs = L::loadDB('attachs', 'forum');
					$attachdb = $pw_attachs->getByTid($_tids,$_pids);
					require_once(R_P.'require/updateforum.php');
					delete_att($attachdb);
					pwFtpClose($ftp);
					$_pids = pwImplode($_pids);
					$db->update("DELETE FROM $pw_posts WHERE pid IN ($_pids)");
					$db->update("DELETE FROM pw_recycle WHERE pid IN ($_pids)");
				}
				refreshto("forumcp.php?action=edit&type=$type&fid=$fid&step=3&ptable=$ptable&verify=$verifyhash", 'delete_recycle');
			} else {
				$logdb = array(
					'type'      => 'recycle',
					'username1' => '',
					'username2' => $windid,
					'field1'    => $fid,
					'field2'    => '',
					'field3'    => '',
					'descrip'   => 'recycle_reply_empty',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
					'affect'    => '',
					'forum'		=> $forum[$fid]['name'],
					'reason'	=> ''
				);
				writelog($logdb);
				refreshto("forumcp.php?action=edit&type=precycle&fid=$fid",'operate_success');
			}
		}
	} elseif ($type == 'msg') {
		$msgdb = array();
		$pages = ''; $page = $_GET['page']; (int)$page<1 && $page = 1;
		$query = $db->query('SELECT id,uid,username,toname,msgtype,posttime,savetime,message FROM pw_forummsg WHERE fid='.pwEscape($fid).' ORDER BY posttime DESC '.pwLimit(($page-1)*$db_perpage,$db_perpage));
		while ($rt = $db->fetch_array($query)) {
			if($rt['savetime'] < $timestamp) {
				$db->query("DELETE FROM pw_forummsg WHERE id='$rt[id]'");
			} else {
				$rt['posttime'] = $rt['posttime'] ? get_date($rt['posttime'],'Y-m-d H:i') : '--';
				$rt['savetime'] = $rt['savetime'] ? get_date($rt['savetime'],'Y-m-d H:i') : '--';
				if ((strpos($rt['toname'],','.$windid.',') !== false && $rt['msgtype'] == '2') || $groupid == '3' || $groupid == '4' || CkInArray($windid,$manager) || $rt['msgtype'] == '1' || $rt['uid'] == $winduid) {
					if ($rt['uid'] != $winduid && $groupid != '3' && $groupid != '4' && CkInArray($windid,$manager) === false) {
						$rt['ifuse'] = 'disabled';
					} else {
						$rt['ifuse'] = '';
					}
					$msgdb[] = $rt;
				}
			}
		}
		$db->free_result($query);
		$count = $db->get_value('SELECT COUNT(*) FROM pw_forummsg WHERE fid='.pwEscape($fid));
		if ($count > $db_perpage) {
			require_once(R_P.'require/forum.php');
			$pages = numofpage($count,$page,ceil($count/$db_perpage), "forumcp.php?action=edit&fid=$fid&type=$type&");
		}
		if ($_POST['demsg']) {
			InitGP(array('ids'));
			foreach ($ids as $key => $value) {
				if (is_numeric($value)) {
					$iids[] = $value;
				}
			}
			$ids = pwImplode($iids);

			!$ids && Showmsg('forummsg_nodata');
			$db->query("DELETE FROM pw_forummsg WHERE id IN($ids)");
			refreshto("forumcp.php?action=edit&type=msg&fid=$fid",'operate_success');
		}
		require_once(PrintEot('forumcp'));footer();

	} elseif ($type == 'addmsg') {
		if (empty($_POST['step'])) {

			$adminname = explode(',',trim($forums['forumadmin'],','));
			require_once(PrintEot('forumcp'));footer();

		} else {

			PostCheck();
			!$fid && Showmsg('annouce_fid');
			InitGP(array('msgtype','toname','savetime'),'P');

			!$msgtype && !$toname && Showmsg('forummsg_object');

			$msgtype == 1 ? $toname = '' : $msgtype = 2;
			$savetime = $timestamp + (intval($savetime) > 0 ? intval($savetime) : 30) * 86400;

			$message = trim(Char_cv($_POST['message']));
			!$message && Showmsg('forummsg_content');
			$toname = ",".implode(',',$toname).",";

			$pwSQL = pwSqlSingle(array(
				'fid'		=> $fid,
				'uid'		=> $winduid,
				'username'	=> $windid,
				'toname'	=> $toname,
				'msgtype'	=> $msgtype,
				'posttime'	=> $timestamp,
				'savetime'	=> $savetime,
				'message'	=> $message
			));
			$db->update("INSERT INTO pw_forummsg SET $pwSQL");
			refreshto("forumcp.php?action=edit&type=msg&fid=$fid",'operate_success');
		}
	}
} elseif ($action == 'del') {

	PostCheck();
	InitGP(array('selid','type'));
	$selids = array();
	foreach ($selid as $key => $value) {
		is_numeric($value) && $selids[] = $value;
	}
	if ($selids) {
		$selids = pwImplode($selids);
	} else {
		Showmsg('id_error');
	}
	if ($type == 'report') {
		$db->update("DELETE FROM pw_report WHERE id IN ($selids)");
		refreshto("forumcp.php?action=edit&type=report&fid=$fid",'operate_success');
	}
}

function updatecache_fd1() {
	global $db;
	require_once R_P.'admin/cache.php';
	$db->update("UPDATE pw_forums SET childid='0',fupadmin=''");
	$query = $db->query("SELECT fid,forumadmin FROM pw_forums WHERE type='category' ORDER BY vieworder");
	while ($cate = $db->fetch_array($query)) {
		Add_S($cate);
		$query2 = $db->query("SELECT fid,forumadmin FROM pw_forums WHERE type='forum' AND fup=".pwEscape($cate['fid']));
		if ($db->num_rows($query2)) {
			$havechild[] = $cate['fid'];
			while ($forum = $db->fetch_array($query2)) {
				Add_S($forum);
				$fupadmin = trim($cate['forumadmin']);
				if ($fupadmin) {
					$db->update("UPDATE pw_forums SET fupadmin=".pwEscape($fupadmin)." WHERE fid=".pwEscape($forum['fid']));
				}
				if (trim($forum['forumadmin'])) {
					$fupadmin .= $fupadmin ? substr($forum['forumadmin'],1) : $forum['forumadmin']; //is
				}
				$query3 = $db->query("SELECT fid,forumadmin FROM pw_forums WHERE type='sub' AND fup=".pwEscape($forum['fid']));
				if ($db->num_rows($query3)) {
					$havechild[] = $forum['fid'];
					while ($sub1 = $db->fetch_array($query3)) {
						Add_S($sub1);
						$fupadmin1 = $fupadmin;
						if ($fupadmin1) {
							$db->update("UPDATE pw_forums SET fupadmin=".pwEscape($fupadmin1)." WHERE fid=".pwEscape($sub1['fid']));
						}
						if (trim($sub1['forumadmin'])) {
							$fupadmin1 .= $fupadmin1 ? substr($sub1['forumadmin'],1) : $sub1['forumadmin'];
						}
						$query4 = $db->query("SELECT fid,forumadmin FROM pw_forums WHERE type='sub' AND fup=".pwEscape($sub1['fid']));
						if ($db->num_rows($query4)) {
							$havechild[] = $sub1['fid'];
							while ($sub2 = $db->fetch_array($query4)) {
								Add_S($sub2);
								$fupadmin2 = $fupadmin1;
								if ($fupadmin2) {
									$db->update("UPDATE pw_forums SET fupadmin=".pwEscape($fupadmin2)." WHERE fid=".pwEscape($sub2['fid']));
								}
							}
						}
					}
				}
			}
		}
	}
	if ($havechild) {
		$havechilds = pwImplode($havechild);
		$db->update("UPDATE pw_forums SET childid='1' WHERE fid IN($havechilds)");
	}
}

function recycle($ids){
	global $db,$fid;
	$delids = array();
	foreach ($ids as $key => $value) {
		if (is_numeric($value)) {
			$delids[] = $value;
		}
	}
	if ($delids) {
		$delids = pwImplode($delids);
	} else {
		Showmsg('forumcp_recycle_nodata');
	}
	$query  = $db->query("SELECT r.*,t.special,t.ifshield,t.ifupload,t.ptable,t.replies,t.fid AS ckfid FROM pw_recycle r LEFT JOIN pw_threads t ON r.tid=t.tid WHERE r.tid IN ($delids) AND r.pid='0' AND r.fid=".pwEscape($fid));
	$taid_a = $ttable_a = $ptable_a = array();
	$delids = $pollids = $actids = $delaids = $rewids = $ids = array();
	while (@extract($db->fetch_array($query))) {
		$ids[] = $tid;
		($ifshield != '2' || $replies == '0' || $ckfid == '0')  && $delids[] = $tid;
		$special == 1 && $pollids[] = $tid;
		$special == 2 && $actids[] = $tid;
		$special == 3 && $rewids[] = $tid;
		if ($ifshield != '2' || $replies == '0' || $ckfid == '0') {
			$ptable_a[$ptable] = 1;
			$ttable_a[GetTtable($tid)][] = $tid;
		}
		if ($ifupload) {
			$taid_a[GetTtable($tid)][] = $tid;
			if ($ifshield != '2' || $replies == '0' || $ckfid == '0') {
				$pw_posts = GetPtable($ptable);
				$query2 = $db->query("SELECT aid FROM $pw_posts WHERE tid=".pwEscape($tid)." AND aid!=''");
				while (@extract($db->fetch_array($query2))) {
					if (!$aid) continue;
					$attachs = unserialize(stripslashes($aid));
					foreach ($attachs as $key => $value) {
						is_numeric($key) && $delaids[] = $key;
						pwDelatt($value['attachurl'],$GLOBALS['db_ifftp']);
						$value['ifthumb'] && pwDelatt("thumb/$value[attachurl]",$GLOBALS['db_ifftp']);
					}
				}
			}
		}
	}
	foreach ($taid_a as $pw_tmsgs => $value) {
		$value = pwImplode($value);
		$query = $db->query("SELECT aid FROM $pw_tmsgs WHERE tid IN($value) AND aid!=''");
		while (@extract($db->fetch_array($query))) {
			if (!$aid) continue;
			$attachs = unserialize(stripslashes($aid));
			foreach ($attachs as $key => $value) {
				is_numeric($key) && $delaids[] = $key;
				pwDelatt($value['attachurl'],$GLOBALS['db_ifftp']);
				$value['ifthumb'] && pwDelatt("thumb/$value[attachurl]",$GLOBALS['db_ifftp']);
			}
		}
	}
	if ($pollids) {
		$pollids = pwImplode($pollids);
		$db->update("DELETE FROM pw_polls WHERE tid IN($pollids)");
	}
	if ($actids) {
		$actids = pwImplode($actids);
		$db->update("DELETE FROM pw_activity WHERE tid IN($actids)");
		$db->update("DELETE FROM pw_actmember WHERE actid IN($actids)");
	}
	if ($rewids) {
		$rewids = pwImplode($rewids);
		$db->update("DELETE FROM pw_reward WHERE tid IN($rewids)");
	}
	if ($delaids) {
		$pw_attachs = L::loadDB('attachs', 'forum');
		$pw_attachs->delete($delaids);
	}
	$delids  = pwImplode($delids);
	if ($delids) {
		# $db->update("DELETE FROM pw_threads	WHERE tid IN($delids)");
		# ThreadManager
                $threadManager = L::loadClass("threadmanager", 'forum');
		$threadManager->deleteByThreadIds($fid,$delids);
	}
	foreach ($ttable_a as $pw_tmsgs => $val) {
		$val = pwImplode($val);
		$db->update("DELETE FROM $pw_tmsgs WHERE tid IN($val)");
	}
	foreach ($ptable_a as $key => $val) {
		$pw_posts = GetPtable($key);
		$db->update("DELETE FROM $pw_posts WHERE tid IN($delids)");
	}
	delete_tag($delids);
	if ($ids) {
		$ids = pwImplode($ids);
		$db->update("DELETE FROM pw_recycle WHERE tid IN ($ids)");
	}
	pwFtpClose($GLOBALS['ftp']);
}
?>