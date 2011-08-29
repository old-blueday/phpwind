<?php
define('SCR','notice');
require_once('global.php');
require_once(R_P.'require/forum.php');
require_once(R_P.'require/bbscode.php');
require_once(R_P.'require/header.php');
//* include_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
pwCache::getData(D_P.'data/bbscache/forum_cache.php');

$guide=array();
$sql_select = '';
$db_windpost['checkurl'] = 0;
if ($fid && $fid!='-1' && $fid!='-2') {
	!$forum[$fid] && Showmsg('data_error');
	if ($forum[$fid]['type'] != 'category') {
		if (!($foruminfo = L::forum($fid))) {
			Showmsg('data_error');
		}
		wind_forumcheck($foruminfo);
		$guide[$fid] = array($forum[$fid]['name'],"thread.php?fid=$fid");
	} else {
		$guide[$fid] = array($forum[$fid]['name'],"index.php?cateid=$fid");
		$sql_select = ',url';
	}
} elseif ($fid==-2) {
	$guide[$fid] = array($db_wwwname,$db_wwwurl);
} else {
	$fid = -1;
	$sql_select = ',url';
}

$noticedb = array();
$query = $db->query("SELECT aid,author,startdate,enddate,subject,content $sql_select FROM pw_announce WHERE fid=".S::sqlEscape($fid)." AND ifopen='1' AND startdate<=".S::sqlEscape($timestamp)." AND (enddate=0 OR enddate>=".S::sqlEscape($timestamp).") ORDER BY vieworder,startdate DESC");
while ($rt = $db->fetch_array($query)) {
	$rt['rawauthor'] = rawurlencode($rt['author']);
	$rt['startdate'] = get_date($rt['startdate']);
	if ($sql_select && $rt['url']) {
		$rt['content'] = "<a href=\"$rt[url]\" target=\"_blank\">{$rt[url]}</a>";
	} else {
		$rt['content'] = convert(str_replace(array("\n","\r\n"),'<br />',$rt['content']),$db_windpost,'post');
	}
	$noticedb[] = $rt;
}
$db->free_result($query);
$msg_guide = headguide($guide);
require_once(PrintEot('notice'));footer();
?>