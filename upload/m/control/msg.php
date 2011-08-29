<?php
!defined('W_P') && exit('Forbidden');
!$windid && wap_msg('not_login');
wap_header('msg',$db_bbsname);

S::gp(array('action'));

if (!$action) {

	$allnum = $newnum = 0;
	$query  = $db->query("SELECT COUNT(*) AS num,ifnew FROM pw_msg WHERE touid=".S::sqlEscape($winduid)." AND type='rebox' GROUP BY ifnew=0");
	while ($rt = $db->fetch_array($query)) {
		 $allnum += $rt['num'];
		$rt['ifnew'] && $newnum = $rt['num'];
	}
	require_once PrintEot('wap_msg');
	wap_footer();

} elseif ($action == 'new') {

	$msgdb = array();
	$query = $db->query("SELECT m.*,mc.title FROM pw_msg m LEFT JOIN pw_msgc mc USING(mid) WHERE m.touid=".S::sqlEscape($winduid)." AND m.type='rebox' AND m.ifnew=1 ORDER BY m.mdate DESC LIMIT 15");
	while ($rt = $db->fetch_array($query)) {
		$rt['title']	= wap_cv($rt['title']);
		$rt['username'] = wap_cv($rt['username']);
		$rt['mdate']	= get_date($rt['mdate']);
		$msgdb[] = $rt;
	}
	require_once PrintEot('wap_msg');
	wap_footer();

} elseif ($action == 'all') {

	$msgdb = array();
	$query = $db->query("SELECT m.*,mc.title FROM pw_msg m LEFT JOIN pw_msgc mc USING(mid) WHERE m.touid=".S::sqlEscape($winduid)." AND m.type='rebox' ORDER BY m.mdate DESC LIMIT 15");
	while ($rt = $db->fetch_array($query)) {
		$rt['title']	= wap_cv($rt['title']);
		$rt['username'] = wap_cv($rt['username']);
		$rt['mdate']	= get_date($rt['mdate'],"m-d H:i");
		$msgdb[] = $rt;
	}
	require_once PrintEot('wap_msg');
	wap_footer();

} elseif ($action == 'read') {
	S::gp(array('mid'),'GP',2);
	$rt  = $db->get_one("SELECT m.*,mc.title,mc.content FROM pw_msg m LEFT JOIN pw_msgc mc USING(mid) WHERE m.touid=".S::sqlEscape($winduid)." AND m.type='rebox' AND m.mid=".S::sqlEscape($mid));
	if (!$rt) {
		wap_msg('no_msg');
	}
	if ($rt['ifnew']) {
		$db->update("UPDATE pw_msg SET ifnew=0 WHERE mid=".S::sqlEscape($rt['mid']));
	}
	$rt['content']	= strip_tags($rt['content']);
	$rt['content']  = substrs($rt['content'],$db_waplimit);
	$rt['content']  = wap_cv($rt['content']);
	$rt['content']  = wap_code($rt['content']);
	$rt['title']	= wap_cv($rt['title']);
	$rt['username'] = wap_cv($rt['username']);
	$rt['mdate']	= get_date($rt['mdate']);
	require_once PrintEot('wap_msg');
	wap_footer();

} elseif ($action == 'write') {

	if (!$_POST['pwuser'] || !$_POST['title'] || !$_POST['content']) {

		S::gp(array('touid'),'GP',2);
		if ($touid) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$rt = $userService->get($touid);
			if ($rt) {
				$pwuser = $rt['username'];
			}
		}
		require_once PrintEot('wap_msg');
		wap_footer();

	} else {

		S::gp(array('pwuser','title','content'),'P');
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$rt = $userService->getByUserName($pwuser);
		if (!$rt) {
			wap_msg('user_not_exists');
		}
		if ($rt['msggroups'] && strpos($rt['msggroups'],",$groupid,")===false || strpos($rt['banpm'],",$windid,")!==false) {
			wap_msg('msg_refuse');
		}
		$title	 = wap_cv($title);
		$content = wap_cv($content);
		$db->update("INSERT INTO pw_msg"
			. " SET ".S::sqlSingle(array(
				'touid'		=> $rt['uid'],
				'fromuid'	=> $winduid,
				'username'	=> $windid,
				'type'		=> 'rebox',
				'ifnew'		=> 1,
				'mdate'		=> $timestamp
		)));
		$mid = $db->insert_id();
		$db->update("REPLACE INTO pw_msgc"
			. " SET ".S::sqlSingle(array(
				'mid'		=> $mid,
				'title'		=> $title,
				'content'	=> $content
		)));

		$userService->updateByIncrement($rt['uid'], array('newpm' => 1));
		wap_msg('msg_success','index.php?m=msg');
	}
} elseif ($action == 'delete') {

	S::gp(array('mid'),'GP',2);
	$db->update("DELETE FROM pw_msg WHERE mid=".S::sqlEscape($mid)." AND type='rebox' AND touid=".S::sqlEscape($winduid));
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$userService->updateByIncrement($winduid, array('newpm' => -1));
	if ($db->affected_rows() > 0) {
		require_once(R_P.'require/msg.php');
		delete_msgc($mid);
	}
	wap_msg('msg_delete','index.php?m=msg');
}
?>