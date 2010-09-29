<?php
!defined ('P_W') && exit('Forbidden');
function update_markinfo($fid, $tid, $pid) {
	global $db;
	$perpage = 10;
	$pid = intval($pid);
	$whereStr = " fid=".pwEscape($fid)." AND tid=".pwEscape($tid)." AND pid=" . pwEscape($pid) . " AND ifhide=0 ";
	$count = $db->get_value("SELECT COUNT(*) FROM pw_pinglog WHERE $whereStr ");
	$markInfo = "";
	if ($count) {
		$query = $db->query("SELECT id FROM pw_pinglog WHERE $whereStr ORDER BY pingdate DESC LIMIT 0,$perpage");
		$ids = array();
		while ($rt = $db->fetch_array($query)) {
			$ids[] = $rt['id'];
		}
		$markInfo = $count . ":" . implode(",", $ids);
	}
	if ($pid == 0) {
		$pw_tmsgs = GetTtable($tid);
		$db->update("UPDATE $pw_tmsgs SET ifmark=" . pwEscape($markInfo) . " WHERE tid=" . pwEscape($tid));
	} else {
		$db->update("UPDATE ".GetPtable("N",$tid)." SET ifmark=".pwEscape($markInfo)." WHERE pid=".pwEscape($pid));
	}
	return $markInfo;
}


function get_pinglogs($tid, $pingIdArr) {
	if (empty($pingIdArr)) return ;

	global $db,$fid;
	$pingIds = array();
	$pingLogs = array();
	foreach ($pingIdArr as $pid => $markInfo) {
		list($count, $ids) = explode(":", $markInfo);
		$pingLogs[$pid]['count'] = $count;
		$pingIds = array_merge($pingIds, explode(",", $ids));
	}
	if (!count($pingIds)) return array();
	$query = $db->query("SELECT a.*,b.uid,b.icon FROM pw_pinglog a LEFT JOIN pw_members b ON a.pinger=b.username WHERE a.id IN (".pwImplode($pingIds).") ");
	while ($rt = $db->fetch_array($query)) {
		$rt['pid'] = $rt['pid'] ? $rt['pid'] : 'tpc';
		list($rt['pingtime'],$rt['pingdate']) = getLastDate($rt['pingdate']);
		$rt['record'] = $rt['record'] ? $rt['record'] : "-";
		if ($rt['point'] > 0) $rt['point'] = "+" . $rt['point'];
		$tmp = showfacedesign($rt['icon'],true,'s');
		$rt['icon'] = $tmp[0];
		$pingLogs[$rt['pid']]['data'][$rt['id']] = $rt;
	}
	foreach ($pingLogs as $pid => $data) {
		if (is_array($pingLogs[$pid]['data'])) krsort($pingLogs[$pid]['data']);
	}
	return $pingLogs;
}
?>