<?php
!function_exists('readover') && exit('Forbidden');
/**
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
**/


//* isset($forum) || include pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
isset($forum) || pwCache::getData(D_P.'data/bbscache/forum_cache.php');
$onlinedb = $guestdb = array();
$onlineService = L::loadClass('OnlineService', 'user');
$onlinedb = $onlineService->getOnlineUser();
if (S::isArray($onlinedb)){
	foreach ($onlinedb as $k => $v){
		$inread = $v['tid'] ? '(Read)' : '';
		if (strpos($db_showgroup,",".$v['groupid'].",") !== false) {
			$onlinedb[$k]['img'] = $v['groupid'];
		} else {
			$onlinedb[$k]['img'] = '6';
		}
		if ($v['ifhide']) {
			if ($groupid == 3) {
				$adminonly  = "&#38544;&#36523;:$v[username]\n";
			}
			$onlinedb[$k]['img']		= '6';
			$onlinedb[$k]['username'] = '&#38544;&#36523;&#20250;&#21592;';
			$onlinedb[$k]['uid']		= 0;
		} else {
			$adminonly = '';
		}
		if ($groupid == '3') {
			$adminonly = "{$adminonly}IP : $v[ip]  ";
		}
		$fname  = $forum[$v['fid']]['name'];
		$action = $fname ? substrs(strip_tags($fname),13) : getLangInfo('action',$v['action']);
		$onlinedb[$k]['lastvisit']  = get_date($v['lastvisit'],'m-d H:i');
		$onlinedb[$k]['onlineinfo'] = "$adminonly&#35770;&#22363; : $action{$inread}  &#26102;&#38388; : {$onlinedb[$k]['lastvisit']}";	
	}
}

if($db_showguest && is_array($guestdb = $onlineService->getOnlineGuest())){
	foreach ($guestdb as $k => $v){
		$inread = $v['tid'] ? '(Read)' : '';
		$guestdb[$k]['img'] = '2';
		$guestdb[$k]['username'] = 'guest';
		$guestdb[$k]['uid'] = 0;
		
		if ($groupid == '3') {
			$ipinfo = "IP : {$v['ip']}  ";
		}
		$fname  = $forum[$v['fid']]['name'];
		$action = $fname ? substrs(strip_tags($fname),13) : getLangInfo('action',$v['action']);
		$guestdb[$k]['lastvisit']  = get_date($v[lastvisit],'m-d H:i');
		$guestdb[$k]['onlineinfo'] = "$ipinfo&#35770;&#22363; : $action{$inread}  &#26102;&#38388; : {$guestdb[$k]['lastvisit']}";	
	}
	$onlinedb = array_merge($onlinedb,$guestdb);
}

$index_whosonline = '<div><table align="center" cellspacing="0" cellpadding="0" width="100%"><tr>';
$flag = -1;
foreach ($onlinedb as $key => $val) {
	$flag++;
	if ($flag % 7 == 0) $index_whosonline .= '</tr><tr>';
	$index_whosonline .= "<td style=\"padding:0 0 5px;border:0;width:14%\"><img src=\"$imgpath/$stylepath/group/$val[img].gif\" align=\"absmiddle\"> <a href=\"". USER_URL ."$val[uid]\" title=\"$val[onlineinfo]\" target=\"_blank\" class=\" _cardshow\" data-card-url=\"pw_ajax.php?action=smallcard&type=showcard&uid=".$val[uid]."\" data-card-key=\"$val[username]\">$val[username]</a></td>";
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