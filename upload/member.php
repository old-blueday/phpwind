<?php
define('SCR','member');
require_once('global.php');
@extract($db->get_one("SELECT newmember AS bbsnewer,totalmember as count FROM pw_bbsinfo WHERE id=1"));
/**
* 用户组权限判断
*/
$_G['allowmember']==0 && Showmsg('member_right');
S::gp(array('page','orderway','asc'));
require_once(R_P.'require/header.php');

$db_maxmember && $page>$db_maxmember && $page=$db_maxmember;
if (!is_numeric($page) || $page < 1) {
	$page = 1;
}
$numofpage = $count%$db_perpage==0 ? floor($count/$db_perpage) : floor($count/$db_perpage)+1;
$numofpage && $page > $numofpage && $page = $numofpage;
$pages = numofpage($count,$page,$numofpage,"member.php?orderway=$orderway&asc=$asc&",$db_maxmember);
$start = ($page-1)*$db_perpage;
$orderarray = array(
	'1' => 'uid',
	'2' => 'username',
	'3' => 'thisvisit',
	'4' => 'postnum',
	'5' => 'digests',
	'6' => 'rvrc',
	'7' => 'money',
);

$orderby = isset($orderarray[$orderway]) ? $orderarray[$orderway] : false;

!$orderby && $orderby = 'uid';
if ($orderby == 'uid' || $orderby == 'username') {
	$sql_table = "pw_members m LEFT JOIN pw_memberdata md USING(uid)";
} else {
	$sql_table = "pw_memberdata md LEFT JOIN pw_members m USING(uid)";
}
$asc = $asc == 'ASC'? 'ASC' :'DESC';
$pwLimit = S::sqlLimit($start,$db_perpage);
$memberdb = array();
$query = $db->query("SELECT m.uid,m.username,m.email,m.gender,m.regdate,m.oicq,m.site,m.location,md.postnum,md.digests,md.rvrc,md.money,md.thisvisit FROM $sql_table ORDER BY $orderby $asc $pwLimit");
while ($member = $db->fetch_array($query)) {
	$member['site'] && $member['site'] = "<a href='$member[site]' target='_blank'>view website</a>";
	$member['regdate'] = get_date($member['regdate'],"Y-m-d");
	$member['thisvisit'] = get_date($member['thisvisit']);//就是这次他登录的时间
	$member['rvrc'] = floor($member['rvrc']/10);
	$memberdb[] = $member;
}
require_once(PrintEot('member'));footer();
?>