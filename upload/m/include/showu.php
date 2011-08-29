<?php
!defined('W_P') && exit('Forbidden');
if (!$winduid && !$_G['allowprofile'])
	wap_msg('not_login');
InitGP(array('uid','username'));
$action = 'show';

$isU = false;
!$uid && !$username && $uid = $winduid;

if ($uid) {
 	$sql = 'm.uid=' . pwEscape($uid);
 	$uid == $winduid && $isU = true;
} else {
 	$sql = 'm.username=' . pwEscape($username);
 	$username == $windid && $isU = true;
}

$userdb = $db -> get_one("SELECT m.uid,m.username,m.email,m.groupid,m.memberid,m.icon,m.gender,m.regdate,m.signature,m.introduce,m.oicq,m.msn,m.yahoo,m.site,m.honor,m.bday,m.medals,m.userstatus,md.thisvisit,md.onlinetime,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency,md.lastvisit,md.lastpost,md.todaypost,md.onlineip FROM pw_members m LEFT JOIN pw_memberdata md ON m.uid=md.uid WHERE $sql");

if (empty($userdb)) {
 	$errorname = '';
 	wap_msg('user_not_exists');
}
$userdb['honor'] = substrs($userdb['honor'], 90);

if (!$isU && !$_G['allowprofile'] && !$userdb['isfriend']) {
 	wap_msg('profile_right');
}

$uid = $userdb['uid'];
include_once(D_P . 'data/bbscache/level.php');
$systitle = $userdb['groupid'] == '-1' ? '' : $ltitle[$userdb['groupid']];
$p_list = $db_plist ? explode(',', $db_plist) : array();

if (!$winduid && !$_G['allowprofile']) 
	Showmsg('not_login');

include_once(D_P . 'data/bbscache/md_config.php');
require_once(R_P . 'require/credit.php');
require_once(R_P . 'require/forum.php');
require_once(R_P . 'require/postfunc.php');

$customdata = $custominfo = $colonydb = array();
$user_icon = explode('|', $userdb['icon']);
if ($user_icon[4] && $userdb['tooltime'] < $timestamp-86400) {
 	$userdb['icon'] = "$user_icon[0]|$user_icon[1]|$user_icon[2]|$user_icon[3]|0";
 	$db -> update("UPDATE pw_members SET icon=" . pwEscape($userdb['icon'], false) . " WHERE uid=" . pwEscape($userdb['uid']));
 	$usericon = showfacedesign($userdb['icon'], true);
}
$query = $db -> query("SELECT cy.id,cy.cname FROM pw_cmembers c LEFT JOIN pw_colonys cy ON cy.id=c.colonyid WHERE c.uid=" . pwEscape($userdb['uid']));
while ($rt = $db -> fetch_array($query)){
	 $colonydb[] = $rt;
}

if ($db_md_ifopen && $userdb['medals']){
	$_MEDALDB = L::config('_MEDALDB', 'cache_read');
	$userdb['medals'] = explode(',', $userdb['medals']);
}

//获得用户积分信息
$usercredit = array(
	'postnum' => $userdb['postnum'],
	'digests' => $userdb['digests'],
	'rvrc' => $userdb['rvrc'],
	'money' => $userdb['money'],
	'credit' => $userdb['credit'],
	'currency' => $userdb['currency'],
	'onlinetime' => $userdb['onlinetime']
);
foreach ($credit -> get($userdb['uid']) as $key => $value){
	$usercredit[$key] = $value;
}
$totalcredit = CalculateCredit($usercredit, unserialize($db_upgrade));
$newmemberid = getmemberid($totalcredit);

if ($userdb['memberid'] <> $newmemberid){
 	$userdb['memberid'] = $newmemberid;
 	$db -> update("UPDATE pw_members SET memberid=" . pwEscape($newmemberid, false) . "WHERE uid=" . pwEscape($userdb['uid']));
}
if ($db_autoban){
 	require_once(R_P . 'require/autoban.php');
 	autoban($userdb['uid']);
}
if ($userdb['groupid'] == '6' || getstatus($userdb['userstatus'], 1)){
 	$pwSQL = '';
 	$isBan = false;
 	$bandb = $delban = array();
 	$query = $db -> query("SELECT * FROM pw_banuser WHERE uid=" . pwEscape($userdb['uid']));
 	while ($rt = $db -> fetch_array($query)){
		if ($rt['type'] == 1 && $timestamp - $rt['startdate'] > $rt['days'] * 86400){
			$delban[] = $rt['id'];
		}elseif ($rt['fid'] == 0){
			$rt['startdate'] = get_date($rt['startdate']);
			$bandb = $rt;
		}else{
			$isBan = true;
			$rt['startdate'] = get_date($rt['startdate']);
			$bandb[] = $rt;
		}
	}
	$delban && $db -> update('DELETE FROM pw_banuser WHERE id IN(' . pwImplode($delban) . ')');
	($userdb['groupid'] == '6' && !$bandb) && $pwSQL .= "groupid='-1',";
	(getstatus($userdb['userstatus'], 1) && !$isBan) && $pwSQL .= 'userstatus=userstatus&(~1),';
	if ($pwSQL = rtrim($pwSQL, ',')){
     	$db -> update("UPDATE pw_members SET $pwSQL WHERE uid=" . pwEscape($userdb['uid']));
    }
 	if ($isBan){
     	include_once(D_P . 'data/bbscache/forum_cache.php');
    }
}

if (!getstatus($userdb['userstatus'], 7) && !CkInArray($windid, $manager) && $userdb['uid'] != $winduid){
	$userdb['email'] = '保密';
}
if (getstatus($userdb['userstatus'], 9) && $db_signwindcode){
	require_once(R_P . 'require/bbscode.php');
	if ($_G['imgwidth'] && $_G['imgheight']){
		$db_windpic['picwidth'] = $_G['imgwidth'];
		$db_windpic['picheight'] = $_G['imgheight'];
	}
	$_G['fontsize'] && $db_windpic['size'] = $_G['fontsize'];
	$userdb['signature'] = convert($userdb['signature'], $db_windpic, 2);
}
$userdb['signature'] = str_replace("\n", "<br>", $userdb['signature']);
$db_union[7] && list($customdata, $custominfo) = getCustom($userdb['customdata']);

$userdb['rvrc'] = floor($userdb['rvrc'] / 10);
if ($db_ifonlinetime && $userdb['onlinetime']){
	$userdb['onlinetime'] = floor($userdb['onlinetime'] / 3600);
}else{
	$userdb['onlinetime'] = 0;
}
if (!$userdb['todaypost'] || $userdb['lastpost'] < $tdtime) $userdb['todaypost'] = 0;
$averagepost = floor($userdb['postnum'] / (ceil(($timestamp - $userdb['regdate']) / (3600 * 24))));
$userdb['regdate'] = get_date($userdb['regdate'], 'Y-m-d');
$userdb['lastvisit'] = get_date($userdb['lastvisit'], 'Y-m-d');
$userdb['onlineip'] = explode('|', $userdb['onlineip']);

$userdb[gender] = getGender($userdb[gender]);
require_once PrintWAP('showu');

function getGender($gender){
    if($gender == 1){
    	return '男';
    }elseif($gender == 2){
    	return '女';
    }else{
    	return '保密';
    }
}

function getCustom($data, $unserialize = true, $strips = null){
	global $db_union;
	$customdata = array();
	if (!$data || ($unserialize ? !is_array($data = unserialize($data)) : !is_array($data))){
		$data = array();
	}elseif (!is_array($custominfo = unserialize($db_union[7]))){
		$custominfo = array();
	}
	if (!empty($data) && !empty($custominfo)){
		foreach ($data as $key => $value){
			if (!empty($strips)){
				$customdata[stripslashes(Char_cv($key))] = stripslashes(Char_cv($value));
			}elseif ($custominfo[$key] && $value){
				$customdata[$key] = $value;
			}
		}
	}
    return array($customdata, $custominfo);
}
?>
