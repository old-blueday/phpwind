<?php
!defined ('P_W') && exit('Forbidden');
function update_markinfo($fid, $tid, $pid) {
	global $db;
	$perpage = 10;
	$pid = intval($pid);
	$creditnames = pwCreditNames();
	$whereStr = " fid=" . S::sqlEscape($fid) . " AND tid=" . S::sqlEscape($tid) . " AND pid=" . S::sqlEscape($pid) . " AND ifhide=0 ";
	$count = 0;
	$creditCount = array();
	$query = $db->query("SELECT COUNT(*) AS count,name,SUM(point) AS sum FROM pw_pinglog WHERE $whereStr GROUP BY name");
	while ($rt = $db->fetch_array($query)) {
		$count += $rt['count'];
		if (isset($creditnames[$rt['name']])) {
			$creditCount[$rt['name']] += $rt['sum'];
		} elseif (in_array($rt['name'], $creditnames)) {
			$key = array_search($rt['name'], $creditnames);
			$creditCount[$key] += $rt['sum'];
		}
	}
	$markInfo = '';
	if ($count) {
		$query = $db->query("SELECT id FROM pw_pinglog WHERE $whereStr ORDER BY id DESC LIMIT 0,$perpage");
		$ids = array();
		while ($rt = $db->fetch_array($query)) {
			$ids[] = $rt['id'];
		}
		$markInfo = $count . ":" . implode(",", $ids);
		if ($creditCount) {
			$tmp = array();
			foreach ($creditCount as $key => $value) {
				$tmp[] = $key . '=' . $value;
			}
			$markInfo .= ':' . implode(',', $tmp);
		}
	}
	if ($pid == 0) {
		//* $db->update("UPDATE $pw_tmsgs SET ifmark=" . S::sqlEscape($markInfo) . " WHERE tid=" . S::sqlEscape($tid));
		$pw_tmsgs = GetTtable($tid);
		pwQuery::update($pw_tmsgs, 'tid=:tid', array($tid), array('ifmark'=>$markInfo));
	} else {
		$db->update("UPDATE ".GetPtable("N",$tid)." SET ifmark=".S::sqlEscape($markInfo)." WHERE pid=".S::sqlEscape($pid));
	}
	return $markInfo;
}


function get_pinglogs($tid, $pingIdArr) {
	if (empty($pingIdArr)) return;

	global $db,$fid,$creditnames;
	$pingIds = array();
	$pingLogs = array();
	foreach ($pingIdArr as $pid => $markInfo) {
		list($count, $ids, $creditCount) = explode(":", $markInfo);
		$pingLogs[$pid]['count'] = $count;
		$pingLogs[$pid]['creditCount'] = parseCreditCount($creditCount);
		$pingIds = array_merge($pingIds, explode(",", $ids));
	}
	if (!count($pingIds)) return array();
	$query = $db->query("SELECT a.*,b.uid,b.icon FROM pw_pinglog a LEFT JOIN pw_members b ON a.pinger=b.username WHERE a.id IN (".S::sqlImplode($pingIds).") ");
	while ($rt = $db->fetch_array($query)) {
		$rt['pid'] = $rt['pid'] ? $rt['pid'] : 'tpc';
		list($rt['pingtime'],$rt['pingdate']) = getLastDate($rt['pingdate']);
		$rt['record'] = $rt['record'] ? $rt['record'] : "-";
		if ($rt['point'] > 0) $rt['point'] = "+" . $rt['point'];
		$tmp = showfacedesign($rt['icon'],true,'s');
		$rt['icon'] = $tmp[0];
		isset($creditnames[$rt['name']]) && $rt['name'] = $creditnames[$rt['name']];
		$pingLogs[$rt['pid']]['data'][$rt['id']] = $rt;
	}
	foreach ($pingLogs as $pid => $data) {
		if (is_array($pingLogs[$pid]['data'])) krsort($pingLogs[$pid]['data']);
	}
	return $pingLogs;
}

function parseCreditCount($creditCount) {
	if (!$creditCount) return array();
	$arr = explode(',', $creditCount);
	$array = array();
	foreach ($arr as $value) {
		list($cType, $cValue) = explode('=', $value);
		$array[$cType] = ($cValue > 0 ? '+' : '') . $cValue;
	}
	return $array;
}
?>