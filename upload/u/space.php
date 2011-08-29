<?php
!defined('R_P') && exit('Forbidden');
$USCR = 'space_index';

$isGM = S::inArray($windid,$manager);
!$isGM && $groupid==3 && $isGM=1;
if ($username) {
	$uid = $db->get_value("SELECT uid FROM pw_members WHERE username=" . S::sqlEscape($username));
} else {
	$uid = $uid ? intval($uid) : $winduid;
}
if ($uid) {
	//* $_cache = getDatastore();
	//* $_cache->delete(array("UID_$uid","UID_CREDIT_$uid","UID_GROUP_$uid"));
	
	//* perf::gatherInfo('changeMembersWithUserIds', array('uid'=>$uid));
	//* perf::gatherInfo('changeMemberCreditWithUserIds', array('uid'=>$uid));
	//* perf::gatherInfo('changeCmemberAndColonyWithUserIds', array('uid'=>$uid));
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
	//var_dump($data);
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
			"SELECT uid FROM pw_space WHERE uid=" . S::sqlEscape($uid),
			"UPDATE pw_space SET visits=visits+'1',visitors=" . S::sqlEscape(serialize($visitors),false) . " WHERE uid=" . S::sqlEscape($uid),
			"INSERT INTO pw_space SET " . S::sqlSingle(array(
				'uid'		=> $uid,
				'visits'	=> 1,
				'visitors'	=> serialize($visitors)
			),false)
		);
	}
	$tovisitors = $db->get_value("SELECT tovisitors FROM pw_space WHERE uid=" . S::sqlEscape($winduid));
	$tovisitors = unserialize($tovisitors);
	is_array($tovisitors) || $tovisitors = array();
	
	if (!isset($tovisitors[$uid]) || $timestamp - $tovisitors[$uid] > 900) {
		$tovisitors[$uid] = $timestamp;
		arsort($tovisitors);
		if (count($tovisitors) > 9) array_pop($tovisitors);
		$db->update("UPDATE pw_space SET tovisits=tovisits+'1',tovisitors=" . S::sqlEscape(serialize($tovisitors),false) .  " WHERE uid=" . S::sqlEscape($winduid));
	}
	//猪头回收
	$user_icon = explode('|',$space['icon']);
	if($user_icon[4] && $space['tooltime'] < $timestamp-86400){
		$space['icon'] = "$user_icon[0]|$user_icon[1]|$user_icon[2]|$user_icon[3]|0";
		/**
		$db->update("UPDATE pw_members SET icon=".S::sqlEscape($space['icon'],false)." WHERE uid=".S::sqlEscape($space['uid']));
		**/
		pwQuery::update('pw_members', 'uid =:uid', array($space['uid']), array('icon'=>$space['icon']));
	}
}

$isSpace = true;

$spaceTemplate = "";
$spacestyle = (($space['spacestyle'] === '2') || ($space['spacestyle'] === '3')) ? $space['spacestyle'] : 2;

$spaceTemplate = 'space_' . $spacestyle . '_index';
//var_dump($spaceTemplate);
//require_once(uTemplate::printEot(($space['spacetype'] || !$indexRight) ? 'space_blog_index' : 'space_index'));
require_once(uTemplate::printEot($spaceTemplate));

pwOutPut();

?>