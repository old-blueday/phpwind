<?php
!defined('A_P') && exit('Forbidden');
$a_key = 'thread';
if (isset($_GET['ajax'])) {
	define('AJAX','1');
}
require_once(R_P.'require/writelog.php');
S::gp(array('cyid','action','tidarray','seltid'));
$selids = $foruminfo = array();
require_once(R_P . 'apps/groups/lib/colony.class.php');
$newColony = new PwColony($cyid);
if (!$colony =& $newColony->getInfo()) {
	Showmsg('data_error');
}
require_once(R_P . 'require/bbscode.php');
$newColony->initBanner();
$groupRight =& $newColony->getRight();
$colony_name = $newColony->getNameStyle();
$descrip = convert($colony['descrip'], array());

$newColony->checkAction($action);

if (!$tidarray && is_numeric($seltid)) {
	$tidarray = array($seltid);
}
if (empty($tidarray)) {
	Showmsg('no_selected_topic');
}

//验证帖子的合法性（是否是本群的帖子，是否越权操作）
$threaddb = $newColony->checkTopic($tidarray);
empty($threaddb) && Showmsg('data_error');
$selids = array_keys($threaddb);

//站点创始人，后台赋予群组管理权限的用户组，群组等级到达后台配置的要求才能有管理权限
$ifTopicAdmin = $newColony->checkTopicAdmin($action,$seltid);
//当用户删除自己的帖子
$ifOwnDelRight = $newColony->getOwnDelRight($action,$threaddb[$seltid]['authorid'],$seltid);

(!$ifTopicAdmin && !$ifOwnDelRight) && Showmsg('colony_topicadmin');

//取关联版块的信息
$foruminfo = L::forum($colony['classid']);
$tmpActionUrl = 'thread.php?cyid=' . $cyid;

//SEO
require_once(R_P . 'apps/groups/lib/colonyseo.class.php');
$colonySeo = new Pw_ColonySEO($cyid);
$webPageTitle = $colonySeo->getPageTitle($groupRight['modeset']['thread']['title'],$colony['cname']);
$metaDescription = $colonySeo->getPageMetadescrip($colony['descrip']);
$metaKeywords = $colonySeo->getPageMetakeyword($colony['cname']);

if (empty($_POST['step'])) {
	
	//操作标题
	$lang_action = array('del'=>'删除话题','highlight'=>'话题标题加亮操作','lock'=>'话题锁定操作','pushtopic'=>'话题提前操作','downtopic'=>'话题压帖操作','toptopic'=>'话题置顶操作','digest'=>'话题精华操作');

	$reason_sel = '';
	$reason_a   = explode("\n",$db_adminreason);
	foreach ($reason_a as $k => $v) {
		if ($v = trim($v)) {
			$reason_sel .= "<option value=\"$v\">$v</option>";
		} else {
			$reason_sel .= "<option value=\"\">-------</option>";
		}
	}

} else {
	S::gp(array('atc_content'),'P');
	if ($SYSTEM['enterreason'] && !$atc_content && !defined('AJAX')) {
		Showmsg('enterreason');
	}
}



if ($action == 'toptopic') {
	S::gp(array('topped'));
	if (empty($_POST['step'])) {
		if (defined('AJAX')) {
			$a = 'toptopic';
			require_once PrintEot('m_ajax');footer();
		} else {
			if (is_numeric($seltid)) {
				${'topped_'.intval($threaddb[$seltid]['topped'])} = 'checked';
			}
			require_once PrintEot('m_topicadmin');footer();
		}

	} else {
		S::gp(array('ifmsg','timelimit'));
		PostCheck();
		(is_null($topped)) && Showmsg('mawhole_notopped');
		$msgdb = $logdb = array();
		$timelimit = PwStrtoTime($timelimit);
		$toolfield = $timelimit > $timestamp && $topped ? $timelimit : '';

		$query = $db->query("SELECT t.tid,t.fid,t.postdate,t.author,t.authorid,t.subject,a.topped,a.toolfield FROM pw_threads t LEFT JOIN pw_argument a ON t.tid=a.tid WHERE t.tid IN(".S::sqlImplode($selids).")");
		$tid_fid = array();
		while ($rt = $db->fetch_array($query)) {
			$tid_fid[$rt['tid']] = $rt['fid'];
			if ($topped && $topped != $rt['topped']) {
				if ($ifmsg) {
					$msgdb[] = array(
						'toUser'	=> $rt['author'],
						'title'		=> getLangInfo('writemsg','top_title'),
						'content'	=> getLangInfo('writemsg','top_content',array(
							'manager'	=> $windid,
							'fid'		=> $fid,
							'tid'		=> $rt['tid'],
							'subject'	=> $rt['subject'],
							'postdate'	=> get_date($rt['postdate']),
							'forum'		=> strip_tags($forum[$fid]['name']),
							'admindate'	=> get_date($timestamp),
							'reason'	=> stripslashes($atc_content)
						))
					);
				}
				$logdb[] = array(
					'type'      => 'topped',
					'username1' => $rt['author'],
					'username2' => $windid,
					'field1'    => $fid,
					'field2'    => $tid,
					'field3'    => '',
					'descrip'   => 'topped_descrip',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
					'topped'	=> $topped,
					'tid'		=> $rt['tid'],
					'subject'	=> substrs($rt['subject'],28),
					'forum'		=> $forum[$fid]['name'],
					'reason'	=> stripslashes($atc_content)
				);
			} elseif ($rt['topped'] && !$topped) {
				if ($ifmsg) {
					$msgdb[] = array(
						'toUser'	=> $rt['author'],
						'title'		=> getLangInfo('writemsg','untop_title'),
						'content'	=> getLangInfo('writemsg','untop_content',array(
							'manager'	=> $windid,
							'fid'		=> $fid,
							'tid'		=> $rt['tid'],
							'subject'	=> $rt['subject'],
							'postdate'	=> get_date($rt['postdate']),
							'forum'		=> strip_tags($forum[$fid]['name']),
							'admindate'	=> get_date($timestamp),
							'reason'	=> stripslashes($atc_content)
						))
					);
				}
				$logdb[] = array(
					'type'      => 'topped',
					'username1' => $rt['author'],
					'username2' => $windid,
					'field1'    => $fid,
					'field2'    => $rt['tid'],
					'field3'    => '',
					'descrip'   => 'untopped_descrip',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
					'tid'		=> $rt['tid'],
					'subject'	=> substrs($rt['subject'],28),
					'forum'		=> $forum[$fid]['name'],
					'reason'	=> stripslashes($atc_content)
				);
			}
			if ($toolfield || $rt['toolfield']) {
				$t = explode(',',$rt['toolfield']);
				$rt['toolfield'] = $toolfield.','.$t[1];
				$pwSQL = S::sqlSingle(array(
					'topped'	=> $topped,
					'toolfield'	=> $rt['toolfield']
				));
				$db->update("UPDATE pw_argument SET $pwSQL WHERE tid=".S::sqlEscape($rt['tid']));
			} else {
				$tids[] = $rt['tid'];
			}
		}
		sendMawholeMessages($msgdb);
		foreach ($logdb as $key => $val) {
			writelog($val);
		}
		if($tids){
			$db->update("UPDATE pw_argument SET topped=".S::sqlEscape($topped)." WHERE tid IN(".S::sqlImplode($tids).")");
		}
		if (defined('AJAX')) {
			Showmsg("toptopic_success_ajax");
		} else {
			refreshto("apps.php?q=group&a=thread&cyid=$cyid",'toptopic_success');
		}
		
	}
	
} elseif ($action == 'digest') {
	S::gp(array('digest'));
	if (empty($_POST['step'])) {

		if (defined('AJAX')) {
			$a = 'digest';
			require_once PrintEot('m_ajax');footer();
		} else {
			if (is_numeric($seltid)) {
				${'digest_'.intval($threaddb[$seltid]['digest'])} = 'checked';
			}
			require_once PrintEot('m_topicadmin');footer();
		}

	} else {
		PostCheck();
		S::gp(array('ifmsg','nextto'));
		(is_null($digest)) && Showmsg('mawhole_nodigest');

		//zhudong 群组积分配置策略：无关联群组取全站积分配置，关联群组取版块和全站综合积分配置
		require_once(R_P.'require/credit.php');
		$creditset = $credit->creditset($foruminfo['creditset'],$db_creditset);

		$add_rvrc  = (int)$creditset['Digest']['rvrc'];
		$add_money = (int)$creditset['Digest']['money'];
		$del_rvrc  = abs($creditset['Undigest']['rvrc']);
		$del_money = abs($creditset['Undigest']['money']);

		$msgdb = $logdb = array();
		$query = $db->query("SELECT a.digest,t.tid,t.fid,t.postdate,t.author,t.authorid,t.subject FROM pw_argument a LEFT JOIN  pw_threads t ON a.tid=t.tid WHERE a.tid IN(".S::sqlImplode($selids).")");
		while ($rt = $db->fetch_array($query)) {
			if (!$rt['digest'] && $digest) {
				if ($ifmsg) {
					$msgdb[] = array(
						'toUser'	=> $rt['author'],
						'title'		=> getLangInfo('writemsg','digest_title'),
						'content'	=> getLangInfo('writemsg','digest_content',array(
							'manager'	=> $windid,
							'fid'		=> $fid,
							'tid'		=> $rt['tid'],
							'subject'	=> $rt['subject'],
							'postdate'	=> get_date($rt['postdate']),
							'forum'		=> strip_tags($forum[$fid]['name']),
							'affect'    => "{$db_rvrcname}：+{$add_rvrc}，{$db_moneyname}：+{$add_money}",
							'admindate'	=> get_date($timestamp),
							'reason'	=> stripslashes($atc_content)
						))
					);
				}
				$credit->addLog('topic_Digest',$creditset['Digest'],array(
					'uid'		=> $rt['authorid'],
					'username'	=> $rt['author'],
					'ip'		=> $onlineip,
					'fname'		=> strip_tags($forum[$fid]['name']),
					'operator'	=> $windid
				));
				$credit->sets($rt['authorid'],$creditset['Digest'],false);
				$credit->setMdata($rt['authorid'],'digests',1);

				$logdb[] = array(
					'type'      => 'digest',
					'username1' => $rt['author'],
					'username2' => $windid,
					'field1'    => $fid,
					'field2'    => $rt['tid'],
					'field3'    => '',
					'descrip'   => 'digest_descrip',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
					'digest'	=> $digest,
					'affect'    => "{$db_rvrcname}：+{$add_rvrc}，{$db_moneyname}：+{$add_money}",
					'tid'		=> $rt['tid'],
					'digest'	=> $digest,
					'subject'	=> substrs($rt['subject'],28),
					'forum'		=> $forum[$fid]['name'],
					'reason'	=> stripslashes($atc_content)
				);
			} elseif ($rt['digest'] && !$digest) {
				if ($ifmsg) {
					$msgdb[] = array(
						'toUser'	=> $rt['author'],
						'title'		=> getLangInfo('writemsg','undigest_title'),
						'content'	=> getLangInfo('writemsg','undigest_content',array(
							'manager'	=> $windid,
							'fid'		=> $fid,
							'tid'		=> $rt['tid'],
							'subject'	=> $rt['subject'],
							'postdate'	=> get_date($rt['postdate']),
							'forum'		=> strip_tags($forum[$fid]['name']),
							'affect'    => "{$db_rvrcname}：-{$del_rvrc}，{$db_moneyname}：-{$del_money}",
							'admindate'	=> get_date($timestamp),
							'reason'	=> stripslashes($atc_content)
						))
					);
				}
				$credit->addLog('topic_Undigest',$creditset['Undigest'],array(
					'uid'		=> $rt['authorid'],
					'username'	=> $rt['author'],
					'ip'		=> $onlineip,
					'fname'		=> strip_tags($forum[$fid]['name']),
					'operator'	=> $windid
				));
				$credit->sets($rt['authorid'],$creditset['Undigest'],false);
				$credit->setMdata($rt['authorid'],'digests',-1);

				$logdb[] = array(
					'type'      => 'digest',
					'username1' => $rt['author'],
					'username2' => $windid,
					'field1'    => $fid,
					'field2'    => $rt['tid'],
					'field3'    => '',
					'descrip'   => 'undigest_descrip',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
					'affect'    => "{$db_rvrcname}：-{$del_rvrc}，{$db_moneyname}：-{$del_money}",
					'tid'		=> $rt['tid'],
					'subject'	=> substrs($rt['subject'],28),
					'forum'		=> $forum[$fid]['name'],
					'reason'	=> stripslashes($atc_content)
				);
			}
		}
		$credit->runsql();

		sendMawholeMessages($msgdb);
		foreach ($logdb as $key => $val) {
			writelog($val);
		}
		$db->update("UPDATE pw_argument SET digest=".S::sqlEscape($digest)." WHERE tid IN(".S::sqlImplode($selids).")",0);
		
		if (defined('AJAX')) {
			Showmsg("digest_success_ajax");
		} else {
			refreshto("apps.php?q=group&a=thread&cyid=$cyid",'digest_success');
		}
	}

} elseif ($action == 'lock') {

	if (empty($_POST['step'])) {

		if (is_numeric($seltid)) {
			$threaddb[$seltid]['locked'] %= 3;
			${'lock_'.$threaddb[$seltid]['locked']} = 'checked';
		}
		require_once PrintEot('m_topicadmin');footer();

	} else {

		PostCheck();
		S::gp(array('locked'));
		S::gp(array('ifmsg'),'P',2);
		(is_null($locked)) && Showmsg('mawhole_nolock');

		$msgdb = $logdb = array();
		$query = $db->query("SELECT locked,tid,fid,postdate,author,authorid,subject FROM pw_threads WHERE tid IN(".S::sqlImplode($selids).")");
		while ($rt = $db->fetch_array($query)) {
			if ($rt['locked']%3 <> $locked && $locked) {
				$s = $rt['locked'] > 2 ? $locked + 3 : $locked;
				//$db->update('UPDATE pw_threads SET locked='.S::sqlEscape($s).' WHERE tid='.S::sqlEscape($rt['tid']));
				pwQuery::update('pw_threads', 'tid=:tid', array($rt['tid']), array('locked'=>$s));
				if ($ifmsg) {
					$msgdb[] = array(
						'toUser'	=> $rt['author'],
						'title'		=> getLangInfo('writemsg','lock_title'),
						'content'	=> getLangInfo('writemsg','lock_content',array(
							'manager'	=> $windid,
							'fid'		=> $fid,
							'tid'		=> $rt['tid'],
							'subject'	=> $rt['subject'],
							'postdate'	=> get_date($rt['postdate']),
							'forum'		=> strip_tags($forum[$fid]['name']),
							'admindate'	=> get_date($timestamp),
							'reason'	=> stripslashes($atc_content)
						))
					);
				}
				$logdb[] = array(
					'type'      => 'locked',
					'username1' => $rt['author'],
					'username2' => $windid,
					'field1'    => $fid,
					'field2'    => $rt['tid'],
					'field3'    => '',
					'descrip'   => 'lock_descrip',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
					'tid'		=> $rt['tid'],
					'subject'	=> substrs($rt['subject'],28),
					'forum'		=> $forum[$fid]['name'],
					'reason'	=> stripslashes($atc_content)
				);
			} elseif ($rt['locked']%3 <> 0 && !$locked) {
				$s = $rt['locked'] > 2 ? 3 : 0;
				//$db->update("UPDATE pw_threads SET locked='$s' WHERE tid=".S::sqlEscape($rt['tid']));
				pwQuery::update('pw_threads', "tid=:tid", array($rt['tid']), array("locked"=>$s));
				if ($ifmsg) {
					$msgdb[] = array(
						'toUser'	=> $rt['author'],
						'title'		=> getLangInfo('writemsg','unlock_title'),
						'content'	=> getLangInfo('writemsg','unlock_content',array(
							'manager'	=> $windid,
							'fid'		=> $fid,
							'tid'		=> $rt['tid'],
							'subject'	=> $rt['subject'],
							'postdate'	=> get_date($rt['postdate']),
							'forum'		=> strip_tags($forum[$fid]['name']),
							'admindate'	=> get_date($timestamp),
							'reason'	=> stripslashes($atc_content)
						))
					);
				}
				$logdb[] = array(
					'type'      => 'locked',
					'username1' => $rt['author'],
					'username2' => $windid,
					'field1'    => $fid,
					'field2'    => $rt['tid'],
					'field3'    => '',
					'descrip'   => 'unlock_descrip',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
					'tid'		=> $rt['tid'],
					'subject'	=> substrs($rt['subject'],28),
					'forum'		=> $forum[$fid]['name'],
					'reason'	=> stripslashes($atc_content)
				);
			}
		}
		sendMawholeMessages($msgdb);
		foreach ($logdb as $key => $val) {
			writelog($val);
		}
		refreshto("apps.php?q=group&a=thread&cyid=$cyid",'lock_success');
	}

} elseif ($action == 'pushtopic') {

	$pushtime_top = (int)pwRights(false,'pushtime');
	if (empty($_POST['step'])) {
		require_once PrintEot('m_topicadmin');footer();
	} else {
		PostCheck();
		S::gp(array('ifmsg','nextto','pushtime'));
		if (!is_numeric($pushtime)) {
			Showmsg('mawhole_erropushtime');
		}
		if ($pushtime_top && $pushtime > $pushtime_top) {
			Showmsg('mawhole_beyondpushtime');
		}
		
		$msgdb = $logdb = array();
		$query = $db->query("SELECT tid,fid,postdate,author,authorid,subject FROM pw_threads WHERE tid IN(".S::sqlImplode($selids).")");
		while ($rt = $db->fetch_array($query)) {
			if ($ifmsg) {
				$msgdb[] = array(
					'toUser'	=> $rt['author'],
					'title'		=> getLangInfo('writemsg','push_title'),
					'content'	=> getLangInfo('writemsg','push_content',array(
						'manager'	=> $windid,
						'fid'		=> $fid,
						'tid'		=> $rt['tid'],
						'subject'	=> $rt['subject'],
						'postdate'	=> get_date($rt['postdate']),
						'forum'		=> strip_tags($forum[$fid]['name']),
						'admindate'	=> get_date($timestamp),
						'reason'	=> stripslashes($atc_content)
					))
				);
			}
			$logdb[] = array(
				'type'      => 'push',
				'username1' => $rt['author'],
				'username2' => $windid,
				'field1'    => $fid,
				'field2'    => $rt['tid'],
				'field3'    => '',
				'descrip'   => 'push_descrip',
				'timestamp' => $timestamp,
				'ip'        => $onlineip,
				'tid'		=> $rt['tid'],
				'subject'	=> substrs($rt['subject'],28),
				'forum'		=> $forum[$fid]['name'],
				'reason'	=> stripslashes($atc_content)
			);
		}
		sendMawholeMessages($msgdb);
		foreach ($logdb as $key => $val) {
			writelog($val);
		}

		$pushtime < 0 && $pushtime = 1;
		$uptime = $timestamp+$pushtime*3600;
		$db->update("UPDATE pw_argument SET lastpost=".S::sqlEscape($uptime)."WHERE tid IN(".S::sqlImplode($selids).")");

		refreshto("apps.php?q=group&a=thread&cyid=$cyid",'pushtopic_success');
	}


} elseif ($action == 'downtopic') {

	if (empty($_POST['step'])) {

		if (is_numeric($seltid)) {
			if ($threaddb[$seltid]['locked']>2) {$lock_1 = 'checked';}else {$lock_0 = 'checked';}
		} else {
			$lock_0 = 'checked';
		}
		require_once PrintEot('m_topicadmin');footer();

	} else {

		PostCheck();
		S::gp(array('ifmsg','nextto','timelimit','ifpush'));
	
		$timelimit < 0 && $timelimit = 24;
		$downtime = $timelimit * 3600;
		$msgdb = $logdb = array();
		//* $threadList = L::loadClass("threadlist", 'forum');
		$query = $db->query("SELECT tid,fid,postdate,author,authorid,subject,locked FROM pw_threads WHERE tid IN(".S::sqlImplode($selids).")");
		while ($rt = $db->fetch_array($query)) {
			$sql = "locked='".($ifpush ? ($rt['locked']%3 + 3) : $rt['locked']%3)."'";
			$db->update("UPDATE pw_argument SET lastpost=lastpost-".S::sqlEscape($downtime)." WHERE tid=".S::sqlEscape($rt['tid']));
			//$db->update("UPDATE pw_threads SET $sql WHERE tid=".S::sqlEscape($rt['tid']));
			$db->update(pwQuery::buildClause("UPDATE :pw_table SET {$sql} WHERE tid=:tid", array('pw_threads', $rt['tid'])));
			if ($ifmsg) {
				$msgdb[] = array(
					'toUser'	=> $rt['author'],
					'title'		=> getLangInfo('writemsg','down_title'),
					'content'	=> getLangInfo('writemsg','down_content',array(
						'manager'	=> $windid,
						'timelimit'	=> $timelimit,
						'fid'		=> $fid,
						'tid'		=> $rt['tid'],
						'subject'	=> $rt['subject'],
						'postdate'	=> get_date($rt['postdate']),
						'forum'		=> strip_tags($forum[$fid]['name']),
						'admindate'	=> get_date($timestamp),
						'reason'	=> stripslashes($atc_content)
					))
				);
			}
			$logdb[] = array(
				'type'      => 'down',
				'username1' => $rt['author'],
				'username2' => $windid,
				'field1'    => $fid,
				'field2'    => $rt['tid'],
				'field3'    => '',
				'descrip'   => 'down_descrip',
				'timestamp' => $timestamp,
				'ip'        => $onlineip,
				'tid'		=> $rt['tid'],
				'subject'	=> substrs($rt['subject'],28),
				'forum'		=> $forum[$fid]['name'],
				'reason'	=> stripslashes($atc_content)
			);
			//* $threadList->updateThreadIdsByForumId($fid,$rt['tid'],$downtime);
			Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));
		}
		sendMawholeMessages($msgdb);
		foreach ($logdb as $key => $val) {
			writelog($val);
		}
		refreshto("apps.php?q=group&a=thread&cyid=$cyid",'downtopic_success');
	}

} elseif ($action == 'highlight') {

	if (empty($_POST['step'])) {

		if (is_numeric($seltid)) {
			$rt = $db->get_one("SELECT a.titlefont,t.author FROM pw_argument a LEFT JOIN pw_threads t ON a.tid=t.tid WHERE a.tid=".S::sqlEscape($seltid));
			$titledetail = explode("~",$rt['titlefont']);
			$titlecolor = $titledetail[0];
			if ($titlecolor && !preg_match('/\#[0-9A-F]{6}/is',$titlecolor)) {
				$titlecolor = '';
			}
			if ($titledetail[1]=='1') {
				$stylename[1]='b one';
			} else {
				$stylename[1]='b';
			}
			if ($titledetail[2]=='1') {
				$stylename[2]='u one';
			} else {
				$stylename[2]='u';
			}
			if ($titledetail[3]=='1') {
				$stylename[3]='one';
			} else {
				$stylename[3]='';
			}
		}
		require_once PrintEot('m_topicadmin');footer();

	} else {
		PostCheck();
		S::gp(array('title1','title2','title3','title4','nextto','ifmsg','timelimit'));

		if ($title1 && !preg_match('/#[0-9A-F]{6}/is',$title1)) {
			Showmsg('mawhole_nodata');
		}
		!$selids && Showmsg('mawhole_nodata');

		$titlefont = S::escapeChar("$title1~$title2~$title3~$title4~$title5~$title6~");
		$ifedit = (!$title1 && !$title2 && !$title3 && !$title4) ? 0 : 1;
		$toolfield = $timelimit>0 && $ifedit ? $timelimit*86400 + $timestamp : '';

		$msgdb = $logdb = array();
		$query = $db->query("SELECT a.tid,a.postdate,t.author,t.authorid,t.subject,a.toolfield FROM pw_argument a LEFT JOIN pw_threads t ON a.tid=t.tid WHERE a.tid IN(".S::sqlImplode($selids).")");
		while ($rt = $db->fetch_array($query)) {
			if ($ifmsg) {
				$msgdb[] = array(
					'toUser'	=> $rt['author'],
					'title'		=> getLangInfo('writemsg',$ifedit ? 'highlight_title' : 'unhighlight_title'),
					'content'	=> getLangInfo('writemsg',$ifedit ? 'highlight_content' : 'unhighlight_content',array(
						'manager'	=> $windid,
						'fid'		=> $fid,
						'tid'		=> $rt['tid'],
						'subject'	=> $rt['subject'],
						'postdate'	=> get_date($rt['postdate']),
						'forum'		=> strip_tags($forum[$fid]['name']),
						'admindate'	=> get_date($timestamp),
						'reason'	=> stripslashes($atc_content)
					))
				);
			}
			$logdb[] = array(
				'type'      => 'highlight',
				'username1' => $rt['author'],
				'username2' => $windid,
				'field1'    => $fid,
				'field2'    => $rt['tid'],
				'field3'    => '',
				'descrip'   => $ifedit ? 'highlight_descrip' : 'unhighlight_descrip',
				'timestamp' => $timestamp,
				'ip'        => $onlineip,
				'tid'		=> $rt['tid'],
				'subject'	=> substrs($rt['subject'],28),
				'forum'		=> $forum[$fid]['name'],
				'reason'	=> stripslashes($atc_content)
			);
			if ($toolfield || $rt['toolfield']) {
				$t = explode(',',$rt['toolfield']);
				$rt['toolfield'] = $t[0].','.$toolfield;
				$db->update("UPDATE pw_argument SET titlefont=".S::sqlEscape($titlefont).',toolfield='.S::sqlEscape($rt['toolfield']).' WHERE tid='.S::sqlEscape($rt['tid']));
			} else {
				$tids[] = $rt['tid'];
			}
		}
		sendMawholeMessages($msgdb);
		foreach ($logdb as $key => $val) {
			writelog($val);
		}
	
		if ($tids) {
			$db->update("UPDATE pw_argument SET titlefont=".S::sqlEscape($titlefont)." WHERE tid IN(". S::sqlImplode($tids).")");
		}
		refreshto("apps.php?q=group&a=thread&cyid=$cyid",'highlight_success');

	}

} elseif ($action == 'del') {

	if (empty($_POST['step'])) {
		if (defined('AJAX')) {
			$a = 'del';
			require_once PrintEot('m_ajax');ajax_footer();
		} else {
			require_once PrintEot('m_topicadmin');footer();
		}
		
	} else {

		PostCheck();
		S::gp(array('ifdel','ifmsg'));

		$msgdb = array();

		require_once(R_P.'require/credit.php');
		$creditset = $credit->creditset($foruminfo['creditset'],$db_creditset);
		$msg_delrvrc  = $ifdel ? abs($creditset['Delete']['rvrc']) : 0;
		$msg_delmoney = $ifdel ? abs($creditset['Delete']['money']) : 0;

		$delarticle = L::loadClass('DelArticle', 'forum');
		$readdb = $delarticle->getTopicDb('tid ' . $delarticle->sqlFormatByIds($selids));
		
		foreach ($readdb as $key => $read) {
			if ($ifmsg) {
				$msgdb[] = array(
					'toUser'	=> $read['author'],
					'title'		=> getLangInfo('writemsg','del_title'),
					'content'	=> getLangInfo('writemsg','del_content',array(
						'manager'	=> $windid,
						'fid'		=> $read['fid'],
						'tid'		=> $read['tid'],
						'subject'	=> $read['subject'],
						'postdate'	=> get_date($read['postdate']),
						'forum'		=> strip_tags($forum[$fid]['name']),
						'affect'    => "{$db_rvrcname}:-{$msg_delrvrc},{$db_moneyname}:-{$msg_delmoney}",
						'admindate'	=> get_date($timestamp),
						'reason'	=> stripslashes($atc_content)
					))
				);
			}
		}
		$delarticle->delTopic($readdb, $db_recycle, $ifdel, array('reason' => $atc_content));
		
		$weiboService = L::loadClass('weibo','sns'); /* @var $weiboService PW_Weibo */
		$weibos = $weiboService->getWeibosByObjectIdsAndType($selids,'group_article');
		if($weibos){
			$mids = array();
			foreach($weibos as $key => $weibo){
				$mids[] = $weibo['mid'];
			}
			$weiboService->deleteWeibos($mids);
		}
		countPosts('-1');

		$credit->runsql();
		sendMawholeMessages($msgdb);

		if ($db_ifpwcache ^ 1) {
			$db->update("DELETE FROM pw_elements WHERE type !='usersort' AND id IN(" . S::sqlImplode($selids) . ')');
		}
		//* P_unlink(D_P.'data/bbscache/c_cache.php');
		pwCache::deleteData(D_P.'data/bbscache/c_cache.php');
		
		if (!defined('AJAX')) {
			refreshto("apps.php?q=group&a=thread&cyid=$cyid",'deltopic_success');
		} else {
			Showmsg('deltopic_success_ajax');
		}
	}

}
function checkForHeadTopic($toptype,$fid,$selForums){
	require_once(R_P.'require/updateforum.php');
	list($catedbs,$top_1,$top_2,$top_3) = getForumListForHeadTopic($fid);
	$topAll = '';
	if($toptype == 0){
		return true;
	}
	if ($toptype == 1) {
		$topAll = ',' .implode(',',array_keys((array)$top_1)) . ',';
	} elseif ($toptype == 2) {
		$topAll = ',' . implode(',',array_keys((array)$top_2)) . ',';
	} elseif ($toptype == 3) {
		$topAll = ',' . implode(',',array_keys((array)$top_3)) . ',';
	}
	foreach ((array)$selForums as $key => $value) {
		if (strpos($topAll,','.$value.',') !== false) {
			return true;
		}
	}
	return false;
}

function sendMawholeMessages($msgdb) {
	foreach ($msgdb as $key => $val) {
		M::sendNotice(
			array($val['toUser']),
			array(
				'title' => $val['title'],
				'content' => $val['content'],
			)
		);
	}
}

?>