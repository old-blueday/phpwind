<?php
require_once('wap_global.php');
require_once(R_P.'require/postfunc.php');
require_once(R_P.'require/forum.php');

empty($winduid) && wap_msg('not_login');

//list($db_openpost,$db_poststart,$db_postend) = explode("\t",$db_openpost);
list($db_openpost, $db_poststart, $db_poststartminute, $db_postend, $db_postendminute) = explode("\t", $db_openpost);
$currentMinute = get_date($timestamp,'i');
if ($db_openpost == 1 && $groupid != 3 && $groupid != 4) {
	if ($db_poststart < $db_postend && (($_time['hours'] < $db_poststart || ($_time['hours'] == $db_poststart && $currentMinute < $db_poststartminute)) || ($_time['hours'] > $db_postend || ($_time['hours'] == $db_postend && $currentMinute > $db_postendminute)))) {
		wap_msg("post_openpost");
	} elseif ($db_poststart > $db_postend && (($_time['hours'] < $db_poststart || ($_time['hours'] == $db_poststart && $currentMinute < $db_poststartminute)) && ($_time['hours'] > $db_postend || ($_time['hours'] == $db_postend && $currentMinute > $db_postendminute)))) {
		wap_msg("post_openpost");
	} elseif ($db_poststart == $db_postend && $_time['hours'] == $db_poststart && ($currentMinute < $db_poststartminute || $currentMinute > $db_postendminute)) {
		wap_msg("post_openpost");
	}
}
/*
if ($db_openpost==1 && $db_poststart<$db_postend && (($_time['hours'] < $db_poststart || ($_time['hours'] == $db_poststart && $currentMinute < $db_poststartminute)) || ($_time['hours'] > $db_postend || ($_time['hours'] == $db_postend && $currentMinute > $db_postendminute)))) {
	wap_msg("post_openpost");
}
*/
if (isban($winddb,$fid)) {
	wap_msg('post_ban');
}
$groupid == '7' && wap_msg("post_check");

if ($db_postallowtime && $timestamp-$winddb['regdate']<$db_postallowtime*60) {
	wap_msg('post_newrg_limit');
}
if ($_G['postlimit'] && $winddb['todaypost'] >= $_G['postlimit']) {
	wap_msg('post_gp_limit');
}
if ($_G['postpertime'] && $timestamp-$winddb['lastpost']<=$_G['postpertime']) {
	wap_msg('post_limit');
}
S::gp(array('action'));
!$action && $action = 'new';

if ($action == 'new') {

	if (!$_POST['subject'] || !$_POST['content']) {
		if (!$fid) {
			$fids  = array();
			$query = $db->query("SELECT fid FROM pw_forums WHERE password='' AND allowvisit='' AND f_type!='hidden'");
			while ($rt = $db->fetch_array($query)) {
				$fids[] = $rt['fid'];
			}
			$cates = '';
			foreach ($forum as $key => $value) {
				if (in_array($key,$fids) && $value['type']!='category' && !$value['cms']) {
					$add=$value['type']=='forum' ? "&gt;" : ($forum[$value['fup']]['type']=='forum' ? "&gt;&gt;" : "&gt;&gt;&gt;");
					$value['name'] = wap_cv(strip_tags($value['name']));
					$cates .= "<option value=\"$key\">$add$value[name]</option>\n";
				}
			}
			$refer = "post.php?action=new&amp;tmp=$timestamp";
		} else {
			$forumname = wap_cv(strip_tags($forum[$fid]['name']));
			$refer="post.php?action=new&amp;fid=$fid&amp;tmp=$timestamp";
		}
		wap_header('post',$db_bbsname);
		require_once PrintEot('wap_post');
		wap_footer();
	} else {
		if (!is_numeric($fid)) {
			wap_msg("post_nofid!");
		}
		S::gp(array('subject','content'),'P',0);
		wap_check($fid,'new');
		$subject = wap_cv($subject);
		$content = wap_cv($content);

		$ipfrom  = S::escapeChar(cvipfrom($onlineip));
		/*
		$db->update("INSERT INTO pw_threads"
			. " SET ".S::sqlSingle(array(
				'fid'		=> $fid,
				'ifcheck'	=> 1,
				'subject'	=> $subject,
				'author'	=> $windid,
				'authorid'	=> $winduid,
				'postdate'	=> $timestamp,
				'lastpost'	=> $timestamp,
				'lastposter'=> $windid
		)));*/
		pwQuery::insert('pw_threads', array(
				'fid'		=> $fid,
				'ifcheck'	=> 1,
				'subject'	=> $subject,
				'author'	=> $windid,
				'authorid'	=> $winduid,
				'postdate'	=> $timestamp,
				'lastpost'	=> $timestamp,
				'lastposter'=> $windid
		));
		
		$tid = $db->insert_id();
		# memcache refresh
		// $threadlist = L::loadClass("threadlist", 'forum');
		// $threadlist->updateThreadIdsByForumId($fid,$tid);
		//* Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));
		
		$pw_tmsgs = GetTtable($tid);
		$db->update("INSERT INTO $pw_tmsgs"
			. " SET ".S::sqlSingle(array(
				'tid'		=> $tid,
				'content'	=> $content,
				'userip'	=> $onlineip,
				'ipfrom'	=> $ipfrom
		)));

		$lastpost = $subject."\t".addslashes($windid)."\t".$timestamp."\t"."read.php?tid=$tid&page=e#a";
		//* $db->update("UPDATE pw_forumdata SET lastpost=".S::sqlEscape($lastpost).",tpost=tpost+1,article=article+1,topic=topic+1 WHERE fid=".S::sqlEscape($fid));
		$db->update(pwQuery::buildClause("UPDATE :pw_table SET lastpost=:lastpost,tpost=tpost+1,article=article+1,topic=topic+1 WHERE fid=:fid", array('pw_forumdata', $lastpost, $fid)));

		require_once(R_P.'require/credit.php');
		$fm = $db->get_one("SELECT creditset FROM pw_forumsextra WHERE fid=".S::sqlEscape($fid));
		$creditset = $credit->creditset($fm['creditset'],$db_creditset);
		$credit->addLog('topic_Post',$creditset['Post'],array(
			'uid'		=> $winduid,
			'username'	=> $windid,
			'ip'		=> $onlineip,
			'fname'		=> $forum[$fid]['name']
		));
		$credit->sets($winduid,$creditset['Post'],false);
		$credit->runsql();
		
		$updateData = array('lastpost' => $timestamp);
		$updateIncrementData = array('postnum' => 1);
		($tdtime  >= $winddb['lastpost']) ? ($updateData['todaypost'] = 1) : ($updateIncrementData['todaypost'] = 1);
		($montime >= $winddb['lastpost']) ? ($updateData['monthpost'] = 1) : ($updateIncrementData['monthpost'] = 1);

		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->update($winduid, array(), $updateData);
		$userService->updateByIncrement($winduid, array(), $updateIncrementData);
		
		wap_msg('post_success',"read.php?tid=$tid");
	}
} elseif ($action == 'reply') {

	if (!$tid) {
		wap_msg('undefined_action');
	}
	$tp = $db->get_one("SELECT fid,subject,locked,ifcheck,ptable,tpcstatus FROM pw_threads WHERE tid=".S::sqlEscape($tid));
	!$tp && wap_msg('illegal_tid');
	$fid = $tp['fid'];
	$openIndex = getstatus($tp['tpcstatus'], 2); #高楼索引支持
	if (!$_POST['content']) {
		$tp['subject'] = str_replace('&nbsp;','',wap_cv($tp['subject']));
		$refer = "post.php?action=reply&amp;tid=$tid&amp;tmp=$timestamp";
		wap_header('post',$db_bbsname);
		require_once PrintEot('wap_post');
		wap_footer();
	} else {
		if (!$tp['ifcheck']) {
			wap_msg('reply_ifcheck');
		}
		if ($tp['locked']>0) {
			wap_msg("reply_lockatc");
		}
		S::gp(array('subject','content'),'P',0);
		wap_check($fid,'reply');

		$subject = wap_cv($subject);
		$content = wap_cv($content);
		$ipfrom  = S::escapeChar(cvipfrom($onlineip));

		$pw_posts = GetPtable($tp['ptable']);
		if ($db_plist && count($db_plist)>1) {
			//* $db->update("INSERT INTO pw_pidtmp(pid) values('')");
			//* $pid = $db->insert_id();
			$uniqueService = L::loadClass ('unique', 'utility');
			$pid = $uniqueService->getUnique('post');
		} else {
			$pid = '';
		}
		/*$db->update("INSERT INTO $pw_posts"
			. " SET ".S::sqlSingle(array(
				'pid'		=> $pid,
				'tid'		=> $tid,
				'fid'		=> $fid,
				'ifcheck'	=> 1,
				'subject'	=> $subject,
				'author'	=> $windid,
				'authorid'	=> $winduid,
				'postdate'	=> $timestamp,
				'userip'	=> $onlineip,
				'ipfrom'	=> $ipfrom,
				'content'	=> $content
		)));*/
		pwQuery::insert($pw_posts, array(
				'pid'		=> $pid,
				'tid'		=> $tid,
				'fid'		=> $fid,
				'ifcheck'	=> 1,
				'subject'	=> $subject,
				'author'	=> $windid,
				'authorid'	=> $winduid,
				'postdate'	=> $timestamp,
				'userip'	=> $onlineip,
				'ipfrom'	=> $ipfrom,
				'content'	=> $content
		));
		!$pid && $pid = $db->insert_id();
		/*
		$db->update("UPDATE pw_threads"
			. " SET ".S::sqlSingle(array(
					'lastpost'	=> $timestamp,
					'lastposter'=> $windid,
				))
			. ",replies=replies+1,hits=hits+1"
			. " WHERE tid=".S::sqlEscape($tid)
		);*/
		$db->update(pwQuery::buildClause('UPDATE :pw_table SET lastpost=:lastpost, lastposter=:lastposter,replies=replies+1,hits=hits+1 WHERE tid=:tid', array('pw_threads', $timestamp, $windid, $tid)));
		
		#增加高楼索引
		if ($openIndex && $pid) {
			$db->update("INSERT INTO pw_postsfloor SET pid=". S::sqlEscape($pid) .", tid=". S::sqlEscape($tid));
		}
		
		# memcache refresh
		// $threadList = L::loadClass("threadlist", 'forum');
		// $threadList->updateThreadIdsByForumId($fid,$tid);
		Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));			

		$lastpost = $subject."\t".addslashes($windid)."\t".$timestamp."\t"."read.php?tid=$tid&page=e#a";
		//* $db->update("UPDATE pw_forumdata SET lastpost=".S::sqlEscape($lastpost).",tpost=tpost+1,article=article+1,topic=topic+1 WHERE fid=".S::sqlEscape($fid));
		$db->update(pwQuery::buildClause("UPDATE :pw_table SET lastpost=:lastpost,tpost=tpost+1,article=article+1,topic=topic+1 WHERE fid=:fid", array('pw_forumdata', $lastpost, $fid)));

		require_once(R_P.'require/credit.php');
		$fm = $db->get_one("SELECT creditset FROM pw_forumsextra WHERE fid=".S::sqlEscape($fid));
		$creditset = $credit->creditset($fm['creditset'],$db_creditset);
		$credit->addLog('topic_Reply',$creditset['Reply'],array(
			'uid'		=> $winduid,
			'username'	=> $windid,
			'ip'		=> $onlineip,
			'fname'		=> $forum[$fid]['name']
		));
		$credit->sets($winduid,$creditset['Reply'],false);
		$credit->runsql();
		
		$updateData = array('lastpost' => $timestamp);
		$updateIncrementData = array('postnum' => 1);
		($tdtime  >= $winddb['lastpost']) ? ($updateData['todaypost'] = 1) : ($updateIncrementData['todaypost'] = 1);
		($montime >= $winddb['lastpost']) ? ($updateData['monthpost'] = 1) : ($updateIncrementData['monthpost'] = 1);

		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->update($winduid, array(), $updateData);
		$userService->updateByIncrement($winduid, array(), $updateIncrementData);

		wap_msg('post_success',"read.php?tid=$tid&amp;page=e");
	}
}
?>