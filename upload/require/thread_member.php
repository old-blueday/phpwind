<?php
!defined('R_P') && exit('Forbidden');

InitGP(array('a'));

if (!($foruminfo = L::forum($fid))) {
	Showmsg('data_error');
}
$groupRight =& $newColony->getRight();
$pwModeImg = "$imgpath/apps";
require_once(R_P . 'u/require/core.php');
include_once(D_P . 'data/bbscache/o_config.php');

require_once(R_P . 'require/header.php');
list($guidename,$forumtitle) = getforumtitle(forumindex($foruminfo['fup'],1));
$msg_guide = headguide($guidename);

$styleid = $colony['styleid'];
$basename = "thread.php?cyid=$cyid&showtype=member";

if (empty($a)) {

	$a_key = 'member';
	if (!$colony['ifmemberopen'] && !$ifadmin && (!$colony['ifcyer'] || $colony['ifadmin'] == '-1')) {
		Showmsg('colony_cnmenber');
	}
	if (empty($_POST['operateStep'])) {

		require_once(R_P.'require/showimg.php');
		InitGP(array('group', 'orderby'));
		$group && $tmpUrlAdd .= '&group='.$group;
		$lang_no_member = array('2'=>'没有普通成员','3'=>'没有未验证会员','4'=>'没有最近访客');
		$order_lastpost = $order_lastvisit = '';

		if ($group && $group == 4) {
			$visitor = $newColony->getVisitor();
			$total = count($visitor);
			$numofpage = ceil($total/$db_perpage);
			$numofpage = ($db_maxpage && $numofpage > $db_maxpage) ? $db_maxpage : $numofpage;
			$page < 1 ? $page = 1 : ($page > $numofpage ? $page = $numofpage : null);
			$pageurl = "{$basename}a=member&group=4&";
			$pages = numofpage($total,$page,$numofpage,$pageurl,$db_maxpage);
			$visitor = array_slice($visitor,($page-1) * $db_perpage, $db_perpage, true);
			$visitorids = array_keys($visitor);
			if ($visitorids) {
				$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
				foreach ($userService->getByUserIds($visitorids) as $rt) {
					$rt['lastvisit'] = $visitor[$rt['uid']];
					list($rt['icon']) = showfacedesign($rt['icon'],1);
					$memdb[] = $rt;
				}
			} else {
				$memdb = array();
			}
		} else {
			InitGP(array('page'),GP,2);
			$sqlsel = '';
			if ($group == 1) {
				$sqlsel = " AND cm.ifadmin='1'";
			} elseif ($group == 2) {
				$sqlsel = " AND cm.ifadmin='0'";
			} elseif ($group == 3) {
				$sqlsel = " AND cm.ifadmin='-1'";
			}
			$total = $db->get_value("SELECT COUNT(*) AS sum FROM pw_cmembers cm WHERE cm.colonyid=" . pwEscape($cyid) . $sqlsel);
			if ($total) {
				if (in_array($orderby, array('lastpost', 'lastvisit'))) {
					$order	= $orderby;
					$urladd	= $orderby ? "orderby=$orderby&" : '';
					${'order_' . $orderby} = ' class="current"';
				} else {
					$order	= 'ifadmin';
					$urladd	= '';
				}
				list($pages, $limit) = pwLimitPages($total,$page,"{$basename}&group=$group&$urladd");
				$memdb = array();
				$query = $db->query("SELECT cm.*,m.icon,m.honor,md.thisvisit FROM pw_cmembers cm LEFT JOIN pw_members m ON cm.uid=m.uid LEFT JOIN pw_memberdata md ON m.uid=md.uid WHERE cm.colonyid=" . pwEscape($cyid) . $sqlsel . " ORDER BY cm.{$order} DESC $limit");
				while ($rt = $db->fetch_array($query)) {
					list($rt['icon']) = showfacedesign($rt['icon'],1);
					$memdb[$rt['username']] = $rt;
				}
				$colonyOwner = $memdb[$colony['admin']];
				unset($memdb[$colony['admin']]);
				$colonyOwner && array_unshift($memdb,$colonyOwner);
			}
		}
		$urladd = $group ? '&group=' . $group : '';

		require_once PrintEot('thread_member');
		footer();

	} else {

		!$ifadmin && Showmsg('undefined_action');
		InitGP(array('selid'), 'P', 2);

		if (!$selid || !is_array($selid)) {
			Showmsg('id_error');
		}
		$toUsers = array();
		$operateStep = GetGP('operateStep','P');
		switch ($operateStep) {
			case 'addadmin':
				($colony['admin'] != $windid && $groupid != 3) && Showmsg('colony_manager');
				$query = $db->query("SELECT ifadmin,username FROM pw_cmembers WHERE colonyid=" . pwEscape($cyid) . ' AND uid IN(' . pwImplode($selid) . ") AND ifadmin!='1'");
				$newMemberCount = 0;
				while ($rt = $db->fetch_array($query)) {
					$rt['ifadmin'] == -1 && $newMemberCount++;
					$toUsers[] = $rt['username'];
				}
				$newColony->updateInfoCount(array('members' => $newMemberCount));
				$db->update("UPDATE pw_cmembers SET ifadmin='1' WHERE colonyid=" . pwEscape($cyid) . ' AND uid IN(' . pwImplode($selid) . ") AND ifadmin!='1'");
				break;
			case 'deladmin':
				($colony['admin'] != $windid && $groupid != 3) && Showmsg('colony_manager');
				$query = $db->query("SELECT username FROM pw_cmembers WHERE colonyid=" . pwEscape($cyid) . ' AND uid IN(' . pwImplode($selid) . ") AND ifadmin='1'");
				while ($rt = $db->fetch_array($query)) {
					$colony['admin'] == $rt['username'] && Showmsg('colony_delladminfail');
					$toUsers[] = $rt['username'];
				}
				$db->update("UPDATE pw_cmembers SET ifadmin='0' WHERE colonyid=" . pwEscape($cyid) . ' AND uid IN(' . pwImplode($selid) . ") AND ifadmin='1'");
				break;
			case 'check':
				$toUsers = $newColony->checkMembers($selid);
				break;
			case 'del':
				$query = $db->query("SELECT username,ifadmin FROM pw_cmembers WHERE colonyid=" . pwEscape($cyid) . ' AND uid IN(' . pwImplode($selid) . ")");
				while ($rt = $db->fetch_array($query)) {
					if ($rt['username'] == $colony['admin']) {
						Showmsg('colony_delfail');
					}

					if ($groupid != 3 && $rt['ifadmin'] == '1' && $colony['admin'] != $windid) {
						Showmsg('colony_manager');
					}
					$rt['ifadmin'] != -1 && $trueMemberCount++;
					$toUsers[] = $rt['username'];
				}
				$db->update("DELETE FROM pw_cmembers WHERE colonyid=" . pwEscape($cyid) . " AND uid IN(" . pwImplode($selid) . ")");
				$newColony->updateInfoCount(array('members' => -$trueMemberCount));
				$colony['members'] -= $trueMemberCount;
				updateGroupLevel($colony['id'], $colony);

				break;
			default:
				Showmsg('undefined_action');
		}
		if ($toUsers) {
			M::sendNotice(
				$toUsers,
				array(
					'title' => getLangInfo('writemsg','o_' . $operateStep . '_title',array(
						'cname'	=> Char_cv($colony['cname']),
					)),
					'content' => getLangInfo('writemsg','o_' . $operateStep . '_content',array(
						'cname'	=> Char_cv($colony['cname']),
						'curl'	=> "$db_bbsurl/{$basename}cyid=$cyid"
					)),
				)
			);
		}
		refreshto("{$basename}",'operate_success');
	}
} elseif ($a == 'fanoutmsg') {

	define('AJAX',1);
	!$ifadmin && Showmsg('undefined_action');

	if (empty($_POST['step'])) {

		InitGP(array('selid', 'group'), null, 2);

		$uids = $usernames = array();

		if ($selid) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			foreach ($userService->getByUserIds($selid) as $rt) {
				$uids[] = $rt['uid'];
				$usernames[] = $rt['username'];
			}
		} else {
			$sql = ' WHERE colonyid=' . pwEscape($cyid) . ' AND uid<>' . pwEscape($winduid);
			switch ($group) {
				case '1': $sql .= " AND ifadmin='1'";break;
				case '2': $sql .= " AND ifadmin='0'";break;
				case '3': $sql .= " AND ifadmin='-1'";break;
				default :$group = 0;
			}
			$total = $db->get_value("SELECT COUNT(*) AS sum FROM pw_cmembers $sql");
			$query = $db->query("SELECT uid,username FROM pw_cmembers $sql LIMIT 3");
			while ($rt = $db->fetch_array($query)) {
				$usernames[] = $rt['username'];
			}
		}
		if (!$usernames) {
			Showmsg('selid_error');
		}
		$uids = implode(',', $uids);
		$usernames = implode(', ', $usernames);

		require_once PrintEot('thread_ajax');
		ajax_footer();

	} else {

		InitGP(array('group'), null, 2);
		InitGP(array('uids', 'subject', 'content'));

		if (!$content || !$subject) {
			Showmsg('msg_empty');
		} elseif (strlen($subject)>75 || strlen($content)>1500) {
			Showmsg('msg_subject_limit');
		}
		require_once(R_P . 'require/bbscode.php');
		$wordsfb = L::loadClass('FilterUtil', 'filter');
		if (($banword = $wordsfb->comprise($subject)) !== false) {
			Showmsg('title_wordsfb');
		}
		if (($banword = $wordsfb->comprise($content, false)) !== false) {
			Showmsg('content_wordsfb');
		}

		$toUsers = array();
		if ($uids) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			foreach ($userService->getByUserIds($uids) as $user) {
				$toUsers[] = $user['username'];
			}
		} else {
			$sql = ' WHERE colonyid=' . pwEscape($cyid) . ' AND uid<>' . pwEscape($winduid);
			switch ($group) {
				case '1': $sql .= " AND ifadmin='1'";break;
				case '2': $sql .= " AND ifadmin='0'";break;
				case '3': $sql .= " AND ifadmin='-1'";break;
			}
			$query = $db->query("SELECT username FROM pw_cmembers $sql LIMIT 500");
			while ($rt = $db->fetch_array($query)) {
				$toUsers[] = $rt['username'];
			}
		}
		if ($toUsers) {
			M::sendMessage(
				$winduid,
				$toUsers,
				array(
					'create_uid' => $winduid,
					'create_username' => $windid,
					'title' => $subject,
					'content' => stripslashes($content),
				)
			);
		}

		Showmsg('send_success');
	}
} elseif ($a == 'invite') {

	empty($winduid) && Showmsg('not_login');
	InitGP(array('id','type'));
	require_once(R_P . 'require/functions.php');
	$customdes = getLangInfo('other','invite_custom_des');
	$tmpUrlAdd .= '&a=invite';

	if ($type == 'groupactive') {
		$invite_url = $db_bbsurl.'/u.php?a=invite&type=groupactive&id=' . $id . '&uid=' . $winduid . '&hash=' . appkey($winduid, $type);
		$activeArray = $db->get_one("SELECT * FROM pw_active WHERE id=".pwEscape($id));
		$objectName = $activeArray['title'];
		$objectDescrip = substrs($activeArray['content'],30);
		$activeId = $activeArray['id'];
		$emailContent = getLangInfo('email','email_groupactive_invite_content');
	} else {
		$id = $cyid;
		$type = 'group';
		$invite_url = $db_bbsurl.'/u.php?a=invite&type=group&id=' . $cyid . '&uid=' . $winduid . '&hash=' . appkey($winduid, $type);
		$objectName = $colony['cname'];
		$objectDescrip = substrs($colony['descrip'],30);
		$emailContent = getLangInfo('email','email_group_invite_content');
	}

	if (empty($_POST['step'])) {

		InitGP("id",null,2);
		@include_once(D_P.'data/bbscache/o_config.php');
		$friend = getFriends($winduid) ? getFriends($winduid) : array();
		foreach ($friend as $key => $value) {
			$frienddb[$value['ftid']][] = $value;
		}
		$query = $db->query("SELECT * FROM pw_friendtype WHERE uid=".pwEscape($winduid)." ORDER BY ftid");
		$friendtype = array();
		while ($rt = $db->fetch_array($query)) {
			$friendtype[$rt['ftid']] = $rt;
		}
		$no_group_name = getLangInfo('other','no_group_name');
		$friendtype[0] = array('ftid' => 0,'uid' => $winduid,'name' => $no_group_name);

		require_once PrintEot('thread_member');
		footer();

	} elseif($_POST['step'] == 1) { // 发送email邀请

		InitGP(array('emails','customdes'),'P');
		strlen($emails)>200 && Showmsg('mode_o_email_toolang');
		strlen($content)>200 && Showmsg('mode_o_extra_toolang');
		if (strpos($emails,',') !== false) {
			$emails = explode(',',$emails);
		} else {
			$emails = explode("\n",$emails);
		}
		count($emails)>5 && Showmsg('mode_o_email_toolang');
		if ($emails) {
			foreach ($emails as $key=>$email) {
				$emails[$key] = trim($email);
				$emails[$key] = str_replace('&nbsp;','',$emails[$key]);
				if (!$email) {
					unset($emails[$key]);
				} elseif (!preg_match("/^[-a-zA-Z0-9_\.]+@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/",$emails[$key])) {
					Showmsg('mode_o_email_format_err');
				}
			}
		}
		!$emails && Showmsg('mode_o_email_empty');
		require_once(R_P.'require/sendemail.php');
		foreach ($emails as $email) {
			sendemail($email, 'email_' . $type . '_invite_subject', 'email_' . $type . '_invite_content');
		}
		Showmsg('operate_success');

	} elseif($_POST['step'] == 2) {

		InitGP(array('sendtoname','touid'),'P');

		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */

		$uids = array();
		if ($sendtoname) {
			$userId = $userService->getUserIdByUserName($sendtoname);
			if (!$userId) {
				$errorname = $sendtoname;
				Showmsg('user_not_exists');
			}
			$uids[] = $userId;
		}
		if (is_array($touid)) {
			foreach ($touid as $key => $value) {
				if (is_numeric($value)) {
					$uids[] = $value;
				}
			}
		}
		!$uids && Showmsg('msg_empty');

		$toUsers = $userService->getUserNamesByUserIds($uids);
		$inColonyUsers = array();
		$query = $db->query("SELECT username FROM pw_cmembers WHERE uid IN(".pwImplode($uids).") AND colonyid=".pwEscape($cyid));
		while ($rt = $db->fetch_array($query)) {
			$inColonyUsers[] = $rt['username'];
		}
		$toUsers = array_diff($toUsers,$inColonyUsers);

		M::sendRequest(
			$winduid,
			$toUsers,
			array(
				'create_uid' => $winduid,
				'create_username' => $windid,
				'title' => getLangInfo('writemsg', 'email_'.$type.'_invite_subject'),
				'content' => getLangInfo('writemsg', 'email_'.$type.'_invite_content'),
				'extra' => serialize(array('cyid' => $id))
			),
			'request_group',
			'request_group'
		);
		if ($inColonyUsers) {
			$inColonyUsers = implode(',',$inColonyUsers);
			Showmsg('colony_invite_message');
		} else {
			Showmsg('operate_success');
		}
	}
} else {

	Showmsg('undefined_action');
}
?>