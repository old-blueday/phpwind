<?php
!function_exists('readover') && exit('Forbidden');

S::gp(array('action','viewvoter'));
if(empty($viewvoter) && ($admincheck || $groupid == 3 || $_G['viewvote'])){
	$viewvoter = 'yes';
}
if ($viewvoter == 'yes' && !$admincheck && $groupid != 3 && !$_G['viewvote']) {
	Showmsg('readvote_noright');
}
$expression = $winduid ? 'v.uid='.S::sqlEscape($winduid) : 'v.username='.S::sqlEscape($onlineip);
$readvote = $db->get_one("SELECT p.*,v.tid AS havevote FROM pw_polls p LEFT JOIN pw_voter v ON p.tid=v.tid AND {$expression} WHERE p.tid=" . S::sqlEscape($tid) . " GROUP BY p.tid");

if ($action == 'modify' && !$readvote['modifiable']) {
	Showmsg('vote_not_modify');
}
$voters		= $readvote['voters'];
$special    = 'read_vote';
$vote_close = ($read['state'] || ($readvote['timelimit'] && $timestamp - $read['postdate'] > $readvote['timelimit'] * 86400)) ? 1 : 0;
$tpc_date   = get_date($read['postdate']);
$tpc_endtime = $readvote['timelimit'] ? get_date($read['postdate'] + $readvote['timelimit'] * 86400) : '无限制';
$regdatelimit = $readvote['regdatelimit'] ? get_date($readvote['regdatelimit'],'Y-m-d') : '';
$creditlimit = !empty($readvote['creditlimit']) ? unserialize($readvote['creditlimit']) : '';
if ($creditlimit) {
	require_once(R_P.'require/credit.php');
}
vote($readvote);
//
if ($vote_close) {
	$specialTips = '本次投票已结束';
} else {
	$specialTips = "投票截止时间：{$tpc_endtime}<br>";
	if ( $readvote['regdatelimit'] || $credit->cType || $readvote['postnumlimit']){
		$readvote['regdatelimit'] && $specialTips .= "注册于{$regdatelimit}前，";
		if ($creditlimit) {
			foreach ($credit->cType as $key => $value) {
				if (empty($creditlimit[$key])) continue;
				$specialTips .= "{$value}{$creditlimit[$key]}以上，";
			}
		}
		$readvote['postnumlimit'] && $specialTips .= "发帖数大于{$readvote[postnumlimit]}";
		$specialTips .= "方可投票";
	}
}

function vote($readvote) {
	global $db,$votetype,$ifview,$votedb,$votesum,$action,$viewvoter,$tid,$admincheck,$vote_close;
	$votearray = unserialize($readvote['voteopts']);
	$votetype = $readvote['mostvotes'] > 1 ? 'checkbox' : 'radio';
	$votesum  = 0;
	$votedb   = $voter =  array();
	$ifview   = $viewvoter == 'yes' ? 'no' : 'yes';
	foreach ($votearray as $option) {
		$votesum += $option[1];
	}
	if ($viewvoter == 'yes') {
		$query = $db->query("SELECT username,vote FROM pw_voter WHERE tid=" . S::sqlEscape($tid) . " LIMIT 500");
		while ($rt = $db->fetch_array($query)) {
			$voter[$rt['vote']][] = $rt[username];
		}
	}
	foreach ($votearray as $key => $value) {
		$vote = array();
		$vote['percent'] = $votesum ? round(100 * $value[1] / ($votesum) ,2) : 0;
		if ($readvote['previewable'] == 0 || $readvote['havevote'] || $vote_close) {
			$vote['width'] = floor(500 * $value[1] / ($votesum + 1));
			$vote['num']   = $value[1];
		} else {
			$vote['width'] = 0;
			$vote['num']   = '*';
		}
		$vote['name']  = $value[0];
		$vote['voter'] = $voter[$key];
		$votedb[$key]  = $vote;
	}
}
?>