<?php
!defined('P_W') && exit('Forbidden');

PostCheck();
$do = GetGP('do');
if ($do == 'vote') {
	$debate = $db->get_one("SELECT endtime,obvote,revote,judge FROM pw_debates WHERE tid=" . pwEscape($tid));
	empty($debate) && Showmsg('data_error');
	if ($debate['judge'] > 0 || $debate['endtime'] < $timestamp) {
		Showmsg('debate_over');
	}
	$standpoint = $db->get_value("SELECT standpoint FROM pw_debatedata WHERE pid='0' AND tid=" . pwEscape($tid) . "AND authorid=" . pwEscape($winduid));
	$standpoint = (int) $standpoint;
	if ($standpoint == 1) {
		Showmsg('debate_voted_Y');
	} elseif ($standpoint == 2) {
		Showmsg('debate_voted_N');
	}
	if (GetGP('q') == 'y') {
		$db->update("UPDATE pw_debates SET obvote=obvote+1 WHERE tid=" . pwEscape($tid));
		$debate['obvote']++;
		$standpoint = 1;
	} else {
		$db->update("UPDATE pw_debates SET revote=revote+1 WHERE tid=" . pwEscape($tid));
		$debate['revote']++;
		$standpoint = 2;
	}
	$db->update("INSERT INTO pw_debatedata SET" . pwSqlSingle(array(
		'pid' => 0,
		'tid' => $tid,
		'authorid' => $winduid,
		'standpoint' => $standpoint,
		'postdate' => $timestamp,
		'vote' => 0,
		'voteids' => ''
	)));
	
	$tmpVotes = $debate['revote'] + $debate['obvote'];
	$tmpob = round($debate['obvote'] / $tmpVotes, 2) * 100;
	$tmpre = round($debate['revote'] / $tmpVotes, 2) * 100;
	
	Showmsg('debate_success');

} elseif ($do == 'judge') {
	
	if ($_POST['step']) {
		InitGP(array(
			'judge',
			'debater',
			'umpirepoint'
		));
		strlen($umpirepoint) > 255 && Showmsg('debate_pointlen');
		$pwSQL['umpirepoint'] = $umpirepoint;
		$debate = $db->get_one("SELECT umpirepoint,debater,judge FROM pw_debates WHERE tid=" . pwEscape($tid));
		if (empty($debate['judge'])) {
			$pwSQL['judge'] = ($judge == 1 || $judge == 3) ? $judge : 2;
		}
		if (empty($debate['debater'])) {
			$rt = $db->get_one("SELECT authorid FROM pw_members m LEFT JOIN pw_debatedata dd ON m.uid=dd.authorid WHERE m.username=" . pwEscape($debater) . "AND dd.tid=" . pwEscape($tid));
			if ($rt) {
				$pwSQL['debater'] = $debater;
			}
		}
		$db->update("UPDATE pw_debates SET" . pwSqlSingle($pwSQL) . " WHERE tid=" . pwEscape($tid));
		Showmsg('debate_judgesuccess');
	} else {
		$debate = $db->get_one("SELECT obvote,revote,obposts,reposts,umpirepoint,debater,judge FROM pw_debates WHERE tid=" . pwEscape($tid));
		if (!$debate['debater']) {
			$debater = array();
			$query = $db->query("SELECT dd.authorid,dd.vote,m.username FROM pw_debatedata dd LEFT JOIN pw_members m ON dd.authorid=m.uid WHERE dd.tid=" . pwEscape($tid) . "ORDER BY dd.vote DESC LIMIT 10");
			while ($rt = $db->fetch_array($query)) {
				$debater[$rt['authorid']]['vote'] += $rt['vote'];
				$debater[$rt['authorid']]['username'] = $rt['username'];
			}
		}
		require_once PrintEot('ajax');
		ajax_footer();
	}
} elseif ($do == 'agree') {
	
	$pid = (int) GetGP('pid');
	$debate = $db->get_one("SELECT endtime,judge FROM pw_debates WHERE tid=" . pwEscape($tid));
	empty($debate) && Showmsg('data_error');
	if ($debate['judge'] > 0 || $debate['endtime'] < $timestamp) {
		Showmsg('debate_over');
	}
	$debate = $db->get_one("SELECT authorid,vote,voteids FROM pw_debatedata WHERE pid=" . pwEscape($pid) . "AND tid=" . pwEscape($tid));
	empty($debate) && Showmsg('data_error');
	$debate['authorid'] == $winduid && Showmsg('debate_voteself');
	if (strpos($debate['voteids'], $winduid) !== false) {
		Showmsg('debate_voted');
	}
	$debate['voteids'] .= "$winduid,";
	$db->update("UPDATE pw_debatedata SET vote=vote+1,voteids=" . pwEscape($debate['voteids'], false) . "WHERE pid=" . pwEscape($pid) . "AND tid=" . pwEscape($tid));
	$vote = $debate['vote'] + 1;
	Showmsg('debate_agree');
}
