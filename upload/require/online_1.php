<?php
!function_exists('readover') && exit('Forbidden');

isset($forum) || include pwCache::getPath(D_P.'data/bbscache/forum_cache.php');

$onlinedb = $gusetdb = array();

$query = $db->query("SELECT username,lastvisit,ip,fid,tid,groupid,action,ifhide,uid FROM pw_online" . (empty($db_showguest) ? ' WHERE uid!=0' : ''));

while ($rt = $db->fetch_array($query)) {
	if ($rt['uid']) {
		$inread = $rt['tid'] ? '(Read)' : '';
		if (strpos($db_showgroup,",".$rt['groupid'].",") !== false) {
			$rt['img'] = $rt['groupid'];
		} else {
			$rt['img'] = '6';
		}
		if ($rt['ifhide']) {
			if ($groupid == 3) {
				$adminonly  = "&#38544;&#36523;:$rt[username]\n";
			}
			$rt['img']		= '6';
			$rt['username'] = '&#38544;&#36523;&#20250;&#21592;';
			$rt['uid']		= 0;
		} else {
			$adminonly = '';
		}
		if ($groupid == '3') {
			$adminonly = "{$adminonly}I P : $rt[ip]\n";
		}
		$fname  = $forum[$rt['fid']]['name'];
		$action = $fname ? substrs(strip_tags($fname),13) : getLangInfo('action',$rt['action']);
		$rt['lastvisit']  = get_date($rt['lastvisit'],'m-d H:i');
		$rt['onlineinfo'] = "$adminonly&#35770;&#22363;: $action{$inread}\n&#26102;&#38388;: $rt[lastvisit]";
		$onlinedb[] = $rt;
	} else {
		$inread = $rt['tid'] ? '(Read)' : '';
		$rt['img'] = '2';
		$rt['username'] = 'guest';

		if ($groupid == '3') {
			$ipinfo = "I P : {$rt[ip]}\n";
		}
		$fname  = $forum[$rt['fid']]['name'];
		$action = $fname ? substrs(strip_tags($fname),13) : getLangInfo('action',$rt['action']);
		$rt['lastvisit']  = get_date($rt[lastvisit],'m-d H:i');
		$rt['onlineinfo'] = "$ipinfo&#35770;&#22363;: $action{$inread}\n&#26102;&#38388;: $rt[lastvisit]";
		$gusetdb[] = $rt;
	}
}
$onlinedb =  arrayMutiUnique($onlinedb,'username');
if ($db_showguest) {
	$onlinedb = array_merge($onlinedb,$gusetdb);
}
$index_whosonline = '<div><table align="center" cellspacing="0" cellpadding="0" width="100%"><tr>';
$flag = -1;
foreach ($onlinedb as $key => $val) {
	$flag++;
	if ($flag % 7 == 0) $index_whosonline .= '</tr><tr>';
	$index_whosonline .= "<td style=\"padding:0 0 5px;border:0;width:14%\"><img src=\"$imgpath/$stylepath/group/$val[img].gif\" align=\"absmiddle\"> <a href=\"". USER_URL ."$val[uid]\" title=\"$val[onlineinfo]\">$val[username]</a></td>";
}
$index_whosonline .= '</tr></table></div>';

/**
 * 去除二维数组中指定键名的数组
 *
 * @param array $array	二维数组
 * @param int $index	二维数组中的键名
 * @return array
 */

function arrayMutiUnique($array,$index) {
	!is_array($array) && $array = array();
	$tempArray = array();
	foreach ($array as $key => $value) {
		$tempValue = $value[$index];
		if (in_array($tempValue,$tempArray)) {
			unset($array[$key]);
		}
		$tempArray[] = $tempValue;
	}
	return $array;
}
?>