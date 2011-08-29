<?php
!function_exists('readover') && exit('Forbidden');

$debatestand = 0;
if ($groupid != 'guest' && !$tpc_locked) {
	$debatestand = $db->get_value("SELECT standpoint FROM pw_debatedata WHERE pid='0' AND tid=".S::sqlEscape($tid)."AND authorid=".S::sqlEscape($winduid));
	$debatestand = (int)$debatestand;
	${'debate_'.$debatestand} = 'SELECTED';
}
if ($page == 1) {
	$debate = $db->get_one("SELECT obtitle,retitle,endtime,obvote,revote,obposts,reposts,umpire,umpirepoint,debater,judge FROM pw_debates WHERE tid=".S::sqlEscape($tid));
}
$stand = (int)S::getGP('stand');
if (!$uid && $read['replies'] > 0 && $stand > 0 && $stand < 4) {
	if ($stand == 3) {
		$rt = $db->get_one("SELECT COUNT(*) AS n FROM pw_debatedata WHERE pid>'0' AND tid=".S::sqlEscape($tid));
		$read['replies'] -= $rt['n'];
		$sqladd = " AND dd.standpoint IS NULL";
	} else {
		$rt = $db->get_one("SELECT COUNT(*) AS n FROM pw_debatedata WHERE pid>'0' AND tid=".S::sqlEscape($tid)." AND standpoint=".S::sqlEscape($stand));
		$read['replies'] = $rt['n'];
		$sqladd = " AND dd.standpoint=".S::sqlEscape($stand);
	}
	$urladd = "&stand=$stand";
	$count = $read['replies']+1;
	$numofpage = ceil($count/$db_readperpage);
	if ($page == 'e' || $page > $numofpage) {
		$page = $numofpage;
	}
}
$fieldadd .= ',dd.standpoint,dd.vote';
$tablaadd .= ' LEFT JOIN pw_debatedata dd ON t.pid=dd.pid';
$special = 'read_debate';

//for read page
$specialTips = '';
if($debate[judge]){
	$specialTips .= "此辩论已结束，裁判 <b><a href=\"u.php?username=$debate[umpire]\">$debate[umpire]</a></b> 宣布:";
	if($debate[judge]==1){
		$specialTips .= "<b class=\"s3\">正方胜</b>";
	}elseif($debate[judge]==3){
		$specialTips .= "<b class=\"s8\">反方胜</b>";
	}else{
		$specialTips .= "<b class=\"s1\">平局</b>";
	}
	$specialTips .= '，最佳辩手:<a href="u.php?username='.$debate[debater].'" target="_blank"><b class="s7">'.$debate[debater].'</b></a>';
}elseif($debate['endtime'] < $timestamp){
	$specialTips .= '此辩论已结束，等待裁判宣布辩论结果。。。';
}else{
	$debate['endtime'] = get_date($debate['endtime'],"Y-m-d H:i");
	$specialTips .= "辩论结束时间：{$debate[endtime]} ， 裁判：<a href=\"u.php?username={$debate[umpire]}\" class=\"mr10\">$debate[umpire]</a>";
}if($windid==$debate[umpire]){
	$specialTips .= ' <a id="judgedebate" href="javascript:void(0)" class="s4" onClick="sendmsg(\'pw_ajax.php?action=debate&do=judge&tid='.$tid."','','judgedebate')\"/>[裁判点评]</a>";
}

$tmpVotes = $debate[revote]+$debate[obvote];
$tmpob = $tmpVotes ? round($debate[obvote]/$tmpVotes,2)*100 : 0;
$tmpre = $tmpVotes ? round($debate[revote]/$tmpVotes,2)*100 : 0;
?>