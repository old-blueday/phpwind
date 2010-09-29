<?php
!defined('R_P') && exit('Forbidden');
$USCR = 'space_index';

$isGM = CkInArray($windid,$manager);
!$isGM && $groupid==3 && $isGM=1;
if ($uid) {

} elseif ($username) {
	$uid = $db->get_value("SELECT uid FROM pw_members WHERE username=" . pwEscape($username));
} else {
	$uid = $winduid;
}
require_once(R_P . 'u/lib/space.class.php');
$newSpace = new PwSpace($uid);
if (!$space =& $newSpace->getInfo()) {
	Showmsg('您访问的空间不存在!');
}
$inv_config = L::config(null, 'inv_config');
if (GetCookie('userads') && $inv_linkopen && $inv_linktype == '0') {
	list($uid,$a) = explode("\t",GetCookie('userads'));
	if (is_numeric($uid) || ($a && strlen($a)<16)) {
		require_once(R_P.'require/userads.php');
	}
}
$newSpace->initSet();
$indexRight = $newSpace->viewRight('index');
$indexValue = $newSpace->getPrivacyByKey('index');
if ($indexRight) {
	$data = $newSpace->layout();
} else {
	$data = array(0 => $newSpace->getSpaceData(array('info' => 1)));
}
$siteName = getSiteName('o');
$uSeo = USeo::getInstance();
$uSeo->set(
	$space['name'] . ' - ' . $siteName,
	$space['name'],
	$space['name'] . ',' . $siteName
);
if ($winduid && !$space['isMe']) {
	//邀请处理
	if (GetCookie('o_invite') && $db_modes['o']['ifopen'] == 1) {
		list($o_u,$hash,$app) = explode("\t",GetCookie('o_invite'));
		if (is_numeric($o_u) && strlen($hash) == 18) {
			require_once(R_P.'require/o_invite.php');
		}
	}
	$visitors = unserialize($space['visitors']);
	is_array($visitors) || $visitors = array();

	if (!isset($visitors[$winduid]) || $timestamp - $visitors[$winduid] > 900) {
		$visitors[$winduid] = $timestamp;
		arsort($visitors);
		if (count($visitors) > 9) array_pop($visitors);
		$db->pw_update(
			"SELECT uid FROM pw_space WHERE uid=" . pwEscape($uid),
			"UPDATE pw_space SET visits=visits+'1',visitors=" . pwEscape(serialize($visitors),false) . " WHERE uid=" . pwEscape($uid),
			"INSERT INTO pw_space SET " . pwSqlSingle(array(
				'uid'		=> $uid,
				'visits'	=> 1,
				'visitors'	=> serialize($visitors)
			),false)
		);
	}
	$tovisitors = $db->get_value("SELECT tovisitors FROM pw_space WHERE uid=" . pwEscape($winduid));
	$tovisitors = unserialize($tovisitors);
	is_array($tovisitors) || $tovisitors = array();

	if (!isset($tovisitors[$uid]) || $timestamp - $tovisitors[$uid] > 900) {
		$tovisitors[$uid] = $timestamp;
		arsort($tovisitors);
		if (count($tovisitors) > 9) array_pop($tovisitors);
		$db->update("UPDATE pw_space SET tovisits=tovisits+'1',tovisitors=" . pwEscape(serialize($tovisitors),false) .  " WHERE uid=" . pwEscape($winduid));
	}
	//猪头回收
	$user_icon = explode('|',$space['icon']);
	if($user_icon[4] && $space['tooltime'] < $timestamp-86400){
		$space['icon'] = "$user_icon[0]|$user_icon[1]|$user_icon[2]|$user_icon[3]|0";
		$db->update("UPDATE pw_members SET icon=".pwEscape($space['icon'],false)." WHERE uid=".pwEscape($space['uid']));
	}
}

$isSpace = true;
require_once(uTemplate::printEot(($space['spacetype'] || !$indexRight) ? 'space_blog_index' : 'space_index'));

pwOutPut();

?>