<?php
!defined('P_W') && exit('Forbidden');

!$winduid && Showmsg('undefined_action');
S::gp(array(
	'pid',
	'type'
));
$rs = $db->get_one('SELECT r.*,t.fid,t.author,t.authorid,t.ptable,t.subject,t.postdate,t.special,t.state FROM pw_reward r LEFT JOIN pw_threads t USING(tid) WHERE r.tid=' . S::sqlEscape($tid));

if (empty($rs) || $rs['special'] != 3 || $rs['state'] != 0) {
	Showmsg('illegal_tid');
}
$pw_posts = GetPtable($rs['ptable']);
$authorid = $rs['authorid'];
$author = $rs['author'];
$fid = $rs['fid'];
$authorid != $winduid && Showmsg('reward_noright');

$rt = $db->get_one("SELECT tid,fid,author,authorid,ifreward FROM $pw_posts WHERE pid=" . S::sqlEscape($pid));
if ($rt['tid'] != $tid || $rt['authorid'] == $authorid) {
	Showmsg('illegal_tid');
}

if (empty($_POST['step'])) {
	
	require_once (R_P . 'require/header.php');
	${'sel_' . $type} = 'checked';
	require_once PrintEot('reward');
	footer();

} else {
	
	PostCheck();
	require_once (R_P . 'require/credit.php');
	//* include_once pwCache::getPath(D_P . 'data/bbscache/forum_cache.php');
	pwCache::getData(D_P . 'data/bbscache/forum_cache.php');
	
	if ($type == '1') {
		//$db->update("UPDATE pw_threads SET state='1' WHERE tid=" . S::sqlEscape($tid));
		pwQuery::update('pw_threads', 'tid=:tid', array($tid), array('state'=>1));
		$db->update('UPDATE pw_reward SET ' . S::sqlSingle(array(
			'author' => $rt['author'],
			'pid' => $pid
		)) . ' WHERE tid=' . S::sqlEscape($tid));
		//$db->update("UPDATE $pw_posts SET ifreward='2' WHERE pid=" . S::sqlEscape($pid));
		pwQuery::update($pw_posts, 'pid=:pid', array($pid), array('ifreward' => '2'));
		$credit->addLog('reward_answer', array(
			$rs['cbtype'] => $rs['cbval']
		), array(
			'uid' => $rt['authorid'],
			'username' => $rt['author'],
			'ip' => $onlineip,
			'fname' => $forum[$fid]['name']
		));
		$credit->set($rt['authorid'], $rs['cbtype'], $rs['cbval'], false); //最佳答案者加分
		$credit->addLog('reward_return', array(
			$rs['cbtype'] => $rs['cbval']
		), array(
			'uid' => $authorid,
			'username' => $author,
			'ip' => $onlineip,
			'fname' => $forum[$fid]['name']
		));
		$credit->set($authorid, $rs['cbtype'], $rs['cbval'], false); //悬赏者返分
		return_value($rt['tid'], $rs['catype'], $rs['caval']);
	} else {
		$rt['ifreward'] && Showmsg('reward_helped');
		$rs['caval'] < 1 && Showmsg('reward_help_error');
		$db->update('UPDATE pw_reward SET caval=caval-1 WHERE tid=' . S::sqlEscape($rt['tid']));
		//$db->update("UPDATE $pw_posts SET ifreward='1' WHERE pid=" . S::sqlEscape($pid));
		pwQuery::update($pw_posts, 'pid=:pid', array($pid), array('ifreward' => '1'));
		$credit->addLog('reward_active', array(
			$rs['catype'] => 1
		), array(
			'uid' => $rt['authorid'],
			'username' => $rt['author'],
			'ip' => $onlineip,
			'fname' => $forum[$fid]['name']
		));
		$credit->set($rt['authorid'], $rs['catype'], 1, false); //热心助人者加分
	}
	$credit->runsql();
	/* clear cache */
	//* $threads = L::loadClass('threads', 'forum');
	//* $threads->delThreads($tid);
	
	if ($_POST['ifmsg']) {
		if ($type == '1') {
			$affect = $credit->cType[$rs['cbtype']] . ":" . $rs['cbval'];
		} else {
			$affect = $credit->cType[$rs['catype']] . ":1";
		}

		M::sendNotice(
			array($rt['author']),
			array(
				'title' => getLangInfo('writemsg','reward_title_' . $type),
				'content' => getLangInfo('writemsg','reward_content_' . $type,array(
					'fid' => $fid,
					'tid' => $rt['tid'],
					'subject' => $rs['subject'],
					'postdate' => get_date($rs['postdate']),
					'forum' => $forum[$fid]['name'],
					'affect' => $affect,
					'admindate' => get_date($timestamp),
					'reason' => 'None'
				)),
			)
		);
	}
	refreshto("read.php?tid=$rt[tid]&ds=1&page=$page", 'operate_success');
}
