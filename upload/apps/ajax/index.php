<?php
!defined('A_P') && exit('Forbidden');

$baseUrl = 'apps.php?';
$basename = 'apps.php?q='.$q.'&';

define('AJAX','1');
define('F_M',true);
InitGP(array('a'));

if (!in_array($a,array('showgroupwritecommlist'))) {
	!$winduid && Showmsg('not_login');
}

require_once(R_P.'require/functions.php');
include_once(D_P . 'data/bbscache/o_config.php');

$isGM = CkInArray($windid,$manager);
!$isGM && $groupid==3 && $isGM=1;
$tpc_author = $windid;

if ($a == 'delshare') {

	InitGP(array('id'),'',2);
	if (!$id) Showmsg('undefined_action');
	$share = $db->get_one("SELECT * FROM pw_collection WHERE id=".pwEscape($id));
	!$share && Showmsg('mode_o_no_share');
	if ($winduid != $share['uid'] && !$isGM) {
		Showmsg('mode_o_delshare_permit_err');
	}
	$db->update("DELETE FROM pw_collection WHERE id=".pwEscape($id));
	if ($affected_rows = delAppAction('share',$id)) {
		countPosts("-$affected_rows");
	}
	//积分变动
	require_once(R_P.'require/credit.php');
	$o_share_creditset = unserialize($o_share_creditset);
	$creditset = getCreditset($o_share_creditset['Delete'],false);
	$creditset = array_diff($creditset,array(0));
	if (!empty($creditset)) {
		require_once(R_P.'require/postfunc.php');
		$credit->sets($share['uid'],$creditset,true);
		updateMemberid($share['uid'],false);
	}

	if ($creditlog = unserialize($o_share_creditlog)) {
		addLog($creditlog['Delete'],$share['username'],$share['uid'],'share_Delete');
	}
	updateUserAppNum($share['uid'],'share','minus');
	echo "success\t";ajax_footer();

} elseif ($a == 'delfriend') {

	InitGP(array('u'),'',2);
	if ($u) {
		if ($friend = getOneFriend($u)) {
			$db->update("DELETE FROM pw_friends WHERE (uid=".pwEscape($winduid)." AND friendid=".pwEscape($u).") OR (uid=".pwEscape($u)." AND friendid=".pwEscape($winduid).")");
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			
			$userIds = array();
			$user = $userService->get($winduid, false, true);
			$user['f_num'] && $userIds[] = $winduid;
			$user = $userService->get($u, false, true);
			$user['f_num'] && $userIds[] = $u;
			
			count($userIds) && $userService->updatesByIncrement($userIds, array(), array('f_num' => -1));

			echo "success|";ajax_footer();
		} else {
			Showmsg('mode_o_not_friend');
		}
	} else {
		Showmsg('mode_o_not_uid');
	}
} elseif ($a == 'addfriend') {

	InitGP(array('u'),'',2);
	if ($u == $winduid) {
		Showmsg('friend_selferror');
	}
	if ($u) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$toUserInfo = $userService->get($u);//username,userstatus
		$userstatus = $toUserInfo['userstatus'];
		$friendcheck = getstatus($userstatus, PW_USERSTATUS_CFGFRIEND, 3);
		if ($friendcheck == 2) {
			Showmsg('friend_not_add');
		} elseif ($friendcheck == 1) {
			Showmsg('friend_add_check');
		}
		if (getOneFriend($u)) {
			Showmsg('mode_o_is_friend');
		}
		$pwSQL = array();
		$pwSQL[] = array($winduid,$u,$timestamp,0);
		$pwSQL[] = array($u,$winduid,$timestamp,0);
		$db->update("REPLACE INTO pw_friends(uid,friendid,joindate,status) VALUES ".pwSqlMulti($pwSQL,false));
		$userService->updatesByIncrement(array($winduid, $u), array(), array('f_num'=>1));
		$myurl = $db_bbsurl."/".$baseUrl."q=user&u=".$winduid;
		
		M::sendNotice(
			array($toUserInfo['username']),
			array(
				'title' => getLangInfo('writemsg','o_friend_success_title',array(
					'username'=>$windid
				)),
				'content' => getLangInfo('writemsg','o_friend_success_cotent',array(
					'uid'		=> $winduid,
					'username'	=> $windid,
					'myurl'		=> $myurl
				)),
			)
		);
		
		//job sign
		$friendUserName = $toUserInfo['username'];
		initJob($winduid,"doAddFriend",array('user'=>$friendUserName));
		echo "success|";ajax_footer();
	} else {
		Showmsg('mode_o_not_uid');
	}
} elseif ($a == 'showcommlist') {
	
	InitGP(array('type','id'),'P');
	$id = (int)$id;
	if (!$id) Showmsg('undefined_action');
	if(!checkCommType($type)) Showmsg('undefined_action');
	require_once(R_P.'require/showimg.php');
	require_once(R_P.'require/bbscode.php');
	$wordsfb = L::loadClass('FilterUtil', 'filter');
	$orderby = $type == 'groupwrite' ? 'ASC' : 'DESC';
	$comment = array();
	$query = $db->query("SELECT c.*,m.icon as face,m.groupid FROM pw_comment c LEFT JOIN pw_members m ON c.uid=m.uid WHERE c.type=".pwEscape($type)." AND c.typeid=".pwEscape($id)." AND upid='0' ORDER BY c.postdate $orderby".pwLimit(0,100));
	while ($rt = $db->fetch_array($query)) {
		list($rt['postdate']) = getLastDate($rt['postdate'],0);
		if ($rt['groupid'] == 6 && $db_shield && $groupid != 3) {
			$rt['title'] = getLangInfo('other','ban_comment');
		} elseif (!$wordsfb->equal($rt['ifwordsfb'])) {
			$rt['title'] = $wordsfb->convert($rt['title'], array(
				'id'	=> $rt['id'],
				'type'	=> 'comments',
				'code'	=> $rt['ifwordsfb']
			));
		}
		list($rt['face']) =  showfacedesign($rt['face'],1,'m');
		$comment[] = $rt;
	}
	$count = count($comment);
	$lastcomment = end($comment);
	require_once printEOT('m_ajax');ajax_footer();

} elseif ($a == 'showgroupwritecommlist') {

	InitGP(array('type','id'),'P');
	$id = (int)$id;
	if (!$id) Showmsg('undefined_action');
	
	//根据$id取出群组的ID
	$cyid = $db->get_value("SELECT cyid FROM pw_cwritedata WHERE id=".pwEscape($id));
	require_once(R_P . 'apps/groups/lib/colony.class.php');
	$newColony = new PwColony($cyid);
	if (!$colony =& $newColony->getInfo()) {
		Showmsg('data_error');
	}
	$isGM = CkInArray($windid,$manager);
	$ifadmin = ($colony['ifadmin'] == '1' || $colony['admin'] == $windid || $isGM || $SYSTEM['colonyright']);

	require_once(R_P.'require/showimg.php');
	require_once(R_P.'require/bbscode.php');
	$wordsfb = L::loadClass('FilterUtil', 'filter');
	$comment = array();
	$query = $db->query("SELECT c.*,m.icon as face,m.groupid FROM pw_comment c LEFT JOIN pw_members m ON c.uid=m.uid WHERE c.type='groupwrite' AND c.typeid=".pwEscape($id)." AND upid='0' ORDER BY c.postdate ASC".pwLimit(0,100));
	while ($rt = $db->fetch_array($query)) {
		list($rt['postdate']) = getLastDate($rt['postdate'],0);
		if ($rt['groupid'] == 6 && $db_shield && $groupid != 3) {
			$rt['title'] = getLangInfo('other','ban_comment');
		} elseif (!$wordsfb->equal($rt['ifwordsfb'])) {
			$rt['title'] = $wordsfb->convert($rt['title'], array(
				'id'	=> $rt['id'],
				'type'	=> 'comments',
				'code'	=> $rt['ifwordsfb']
			));
		}
		list($rt['face']) =  showfacedesign($rt['face'],1,'m');
		$rt['ifshowdel'] = ($ifadmin || $winduid == $rt['uid']) ? '1' : '0';
		$comment[] = $rt;
	}
	$count = count($comment);
	$lastcomment = end($comment);
	$a = 'showcommlist';
	require_once printEOT('m_ajax');ajax_footer();

} elseif ($a == 'commreply') {
	require_once(R_P.'require/showimg.php');
	require_once(R_P.'require/postfunc.php');
	banUser();
	InitGP(array('type','id','title','upid','position','other'),'P');
	$title 	= str_replace('&#61;','=',$title);
	$id = (int)$id;
	$upid = (int)$upid ? (int)$upid : 0;
	if (!$id) Showmsg('undefined_action');
	if(!checkCommType($type)) Showmsg('undefined_action');
	$app_table = $id_filed = '';
	list($app_table,$id_filed) = getCommTypeTable($type);
	$count = $db->get_value("SELECT COUNT(*) AS count FROM pw_comment WHERE type=".pwEscape($type)." AND typeid=".pwEscape($id));
	if ($count > 99) Showmsg('mode_o_com_count_max');
	if (strlen($title)<3 || strlen($title)>200) {
		Showmsg('mode_o_com_title_error');
	}
	require_once(R_P.'require/bbscode.php');
	$wordsfb = L::loadClass('FilterUtil', 'filter');
	if (($banword = $wordsfb->comprise($title, true, true)) !== false) {
		Showmsg('title_wordsfb');
	}
	$data = array(
		'uid' 		=> $winduid,
		'username'	=> $windid,
		'title'		=> $title,
		'type'		=> $type,
		'typeid'	=> $id,
		'upid'		=> $upid,
		'postdate'	=> $timestamp,
		'ifwordsfb' => $wordsfb->ifwordsfb(stripslashes($title))
	);
	$db->update("INSERT INTO pw_comment SET ".pwSqlSingle($data));
	$insertid = $db->insert_id();
	if ($app_table && $id_filed) {
		$db->update("UPDATE $app_table SET c_num=c_num+1 WHERE $id_filed=".pwEscape($id));
	}
	if ($insertid) {
		
		//zhudong 当为群组的记录的时候，更改群组记录的发表时间
		if ($type == 'groupwrite') {
			$db->update("UPDATE pw_cwritedata SET replay_time=".pwEscape($timestamp)." WHERE id=".pwEscape($id));
		}

		countPosts('+1');
		$threadComment = array(
			'diary'	=> 'diaryComment',
			'photo'	=> 'picComment',
			'groupphoto' => 'groupPicComment',
			'write' => 'writeComment',
		);
		if (isset($threadComment[$type])) {
			updateDatanalyse($id,$threadComment[$type],1);
		}
		if (strpos($title,'[s:') !== false) {
			$title = showface($title);
		}
		list($face) = showfacedesign($winddb['icon'],1,'m');
		$title = convert($title,$db_windpost);

		$postdate = get_date($timestamp);
		$tousername = getUserNameByTypeAndId($type,$id);

		//echo "success\t".$insertid."\t".$face."\t".$title;
		if($tousername != $windid){
			M::sendMessage(
				$winduid,
				array($tousername),
				array(
					'create_uid'	=> $winduid,
					'create_username'	=> $windid,
					'title' => getLangInfo('writemsg','o_'.$type.'_success_title',array(
						'formname'	=> $windid,
						'sender'    => $windid,
						'receiver'  => $tousername,
					)),
					'content' => getLangInfo('writemsg','o_'.$type.'_success_cotent',array(
						'formuid' 	=> $winduid,
						'formname'	=> $windid,
						'touid'		=> $uid,
						'title'		=> strip_tags($title),
						'type'		=> $type,
						'id'		=> $id,
						'sender'    => $windid,
						'receiver'  => $tousername,
					)),
				),
				'sms_comment_'.$type,
				'sms_comment'
			);
		}
		if(empty($other)){
			require_once printEOT('m_ajax');
		}else{
			echo 'ok';
		}
		ajax_footer();
	} else {
		Showmsg('undefined_action');
	}
} elseif ($a == 'commdel') {
	InitGP(array('id'),'P',2);
	if (!$id) Showmsg('undefined_action');
	$thiscomm = $db->get_one("SELECT uid,type,typeid FROM pw_comment WHERE id=".pwEscape($id));
	if (!$isGM && $thiscomm['uid'] != $winduid) {
		if ($thiscomm['type'] == 'groupwrite') {
			$colony = $db->get_one("SELECT c.admin,cb.ifadmin FROM pw_cwritedata cw LEFT JOIN pw_colonys c ON cw.cyid=c.id LEFT JOIN pw_cmembers cb ON c.id=cb.colonyid AND cb.uid=" . pwEscape($winduid) . " WHERE cw.id=" . pwEscape($thiscomm['typeid']));
			if ($colony['admin'] != $windid && $colony['ifadmin'] != 1) {
				Showmsg('mode_o_com_del_priv');
			}
		} elseif ($thiscomm['type'] == 'active') {
			$colony = $db->get_one("SELECT c.admin,cb.ifadmin FROM pw_active a LEFT JOIN pw_colonys c ON a.cid=c.id LEFT JOIN pw_cmembers cb ON c.id=cb.colonyid AND cb.uid=" . pwEscape($winduid) . " WHERE a.id=" . pwEscape($thiscomm['typeid']));
			if ($colony['admin'] != $windid && $colony['ifadmin'] != 1) {
				Showmsg('mode_o_com_del_priv');
			}
		} elseif ($thiscomm['type'] == 'groupphoto') {
			$colony = $db->get_one("SELECT c.admin,cb.ifadmin FROM pw_cnphoto cp LEFT JOIN pw_cnalbum ca ON cp.aid=ca.aid LEFT JOIN pw_colonys c ON ca.ownerid=c.id LEFT JOIN pw_cmembers cb ON ca.ownerid=cb.colonyid AND cb.uid=" . pwEscape($winduid) . " WHERE cp.pid=" . pwEscape($thiscomm['typeid']));
			if ($colony['admin'] != $windid && $colony['ifadmin'] != 1) {
				Showmsg('mode_o_com_del_priv');
			}
		} else {
			Showmsg('mode_o_com_del_priv');
		}
	}

	$updatenum = 0;
	$db->update("DELETE FROM pw_comment WHERE id=".pwEscape($id));
	$updatenum += $db->affected_rows();
	$db->update("DELETE FROM pw_comment WHERE upid=".pwEscape($id));
	$updatenum += $db->affected_rows();
	list($app_table,$app_filed) = getCommTypeTable($thiscomm['type']);
	if ($updatenum && $app_table && $thiscomm['typeid']) {
		$db->update("UPDATE $app_table SET c_num=c_num-".pwEscape($updatenum)." WHERE $app_filed=".pwEscape($thiscomm['typeid']));
	}
	countPosts("-$updatenum");
	echo "success\t$id";
	ajax_footer();
} elseif ($a == 'addfriendtype') {
	InitGP(array('u','name'),'P');
	$u = (int) $u;
	if (!$u) Showmsg('undefined_action');
	if ($u != $winduid && !$isGM) Showmsg('undefined_action');
	if (strlen($name)<1 || strlen($name)>20) Showmsg('mode_o_addftype_name_leng');
	$check = $db->get_one("SELECT ftid FROM pw_friendtype WHERE uid=".pwEscape($u)." AND name=".pwEscape($name));
	if ($check) Showmsg('mode_o_addftype_name_exist');
	$count = $db->get_value("SELECT COUNT(*) AS count FROM pw_friendtype WHERE uid=".pwEscape($u));
	if ($count>=20) Showmsg('mode_o_addftype_length');
	$db->update("INSERT INTO pw_friendtype(uid,name) VALUES(".pwEscape($u).",".pwEscape($name).")");
	$id = $db->insert_id();
	if ($id) {
		echo "success\t$id";
		ajax_footer();
	} else {
		Showmsg('undefined_action');
	}
} elseif ($a == 'delfriendtype') {
	InitGP(array('u','ftid'),'P',2);
	$where = '';
	if (!$isGM) {
		if (!$u) Showmsg('undefined_action');
		if ($u != $winduid) Showmsg('undefined_action');
		$where .= " AND uid=".pwEscape($u);
	}

	if (!$ftid) Showmsg('undefined_action');
	$db->update("DELETE FROM pw_friendtype WHERE ftid=".pwEscape($ftid)."$where");
	if ($db->affected_rows()) {
		$db->update("UPDATE pw_friends SET ftid=0 WHERE ftid=".pwEscape($ftid));
		echo "success";
		ajax_footer();
	} else {
		Showmsg('undefined_action');
	}
} elseif ($a == 'eidtfriendtype') {
	InitGP(array('u','ftid','name'),'P');
	$u = (int) $u;
	$ftid = (int) $ftid;
	if (!$u) Showmsg('undefined_action');
	if (!$isGM) {
		if ($u != $winduid) Showmsg('mode_o_addftype_n');
	}
	if (!$ftid) Showmsg('undefined_action');
	if (strlen($name)<1 || strlen($name)>20) Showmsg('mode_o_addftype_name_leng');
	$check = $db->get_one("SELECT ftid FROM pw_friendtype WHERE uid=".pwEscape($u)." AND name=".pwEscape($name));
	if ($check) Showmsg('mode_o_addftype_name_exist');
	$db->update("UPDATE pw_friendtype SET name=".pwEscape($name)." WHERE uid=".pwEscape($u)." AND ftid=".pwEscape($ftid));
	if ($db->affected_rows()) {
		echo "success";
		ajax_footer();
	} else {
		Showmsg('undefined_action');
	}
} elseif ($a == 'postboard') {

	require_once(R_P.'require/postfunc.php');
	banUser();
	InitGP(array('uid','title'),'P');
	$title 	= str_replace('&#61;','=',$title);
	$uid = (int)$uid;
	if (!$uid) Showmsg('undefined_action');
	if ($uid == $winduid) Showmsg('mode_o_board_self');
	if (!isFriend($uid,$winduid)) Showmsg('mode_o_board_not_friend');
	if (strlen($title)>3 && strlen($title)>200) Showmsg('mode_o_board_too_lang');

	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$tousername = $userService->getUserNameByUserId($uid);

	if (!$tousername) Showmsg('undefined_action');

	require_once(R_P.'require/bbscode.php');
	$wordsfb = L::loadClass('FilterUtil', 'filter');
	if (($banword = $wordsfb->comprise($title, false)) !== false) {
		Showmsg('title_wordsfb');
	}
	$data = array(
		'uid' 		=> $winduid,
		'username'	=> $windid,
		'touid'		=> $uid,
		'title'		=> $title,
		'postdate'	=> $timestamp,
		'ifwordsfb' => $wordsfb->ifwordsfb(stripslashes($title))
	);
	$db->update("INSERT INTO pw_oboard SET ".pwSqlSingle($data));
	$thisid = $db->insert_id();

	$userCache = L::loadClass('UserCache', 'user');//ismodify
	$userCache->delete($uid, 'messageboard');

	if ($thisid) {
		M::sendMessage(
			$winduid,
			array($tousername),
			array(
				'create_uid'	=> $winduid,
				'create_username'	=> $windid,
				'title' => getLangInfo('writemsg','o_board_success_title',array(
					'formname'	=> $windid,
					'sender'    => $windid,
					'receiver'  => $tousername,
				)),
				'content' => getLangInfo('writemsg','o_board_success_cotent',array(
					'formuid' 	=> $winduid,
					'formname'	=> $windid,
					'touid'		=> $uid,
					'content'	=> $data['title'],
					'sender'    => $windid,
					'receiver'  => $tousername,
				)),
			),
			'sms_message',
			'sms_guestbook'
		);
		countPosts('+1');
		require_once(R_P.'require/showimg.php');
		list($myface) = showfacedesign($winddb['icon'],1,'m');
		//require_once(R_P.'require/bbscode.php');
		if (strpos($title,'[s:') !== false) {
			$title = showface($title);
		}
		//require_once(R_P.'require/bbscode.php');
		$title = convert(stripslashes($title), $db_windpost);
		$postdate = get_date($timestamp);
		require_once printEOT('m_ajax');ajax_footer();
	} else {
		Showmsg('undefined_action');
	}
} elseif ($a == 'delboard') {

	InitGP(array('id'),'P',2);
	if (!$id) Showmsg('undefined_action');
	$board = $db->get_one("SELECT * FROM pw_oboard WHERE id=" . pwEscape($id));
	if (!$board || (!$isGM && $board['uid'] != $winduid && $board['touid'] != $winduid)) {
		Showmsg('undefined_action');
	}
	$db->update("DELETE FROM pw_oboard WHERE id=" . pwEscape($id));
	
	$userCache = L::loadClass('UserCache', 'user');//ismodify
	$userCache->delete($board['touid'], 'messageboard');

	$affected_rows = delAppAction('board',$id)+1;
	countPosts("-$affected_rows");
	echo "success";
	ajax_footer();

} elseif ($a == 'showftlist') {

	InitGP(array('u'),'P',2);
	if (!$u) Showmsg('undefined_action');
	if ($u != $winduid) Showmsg('undefined_action');
	$query = $db->query("SELECT * FROM pw_friendtype WHERE uid=".pwEscape($u)." ORDER BY ftid");
	$types = array();
	while ($rt = $db->fetch_array($query)) {
		$types[] = $rt;
	}
	if (count($types)) {
		$str = pwJsonEncode($types);
	} else {
		$str = '';
	}
	echo "success\t$str";
	ajax_footer();

} elseif ($a == 'setfriendtype') {

	InitGP(array('friendid','ftid'),'P',2);
	!$ftid && $ftid = 0;
	if (!$friendid) Showmsg('undefined_action');
	$db->update("UPDATE pw_friends SET ftid=".pwEscape($ftid)." WHERE uid=".pwEscape($winduid)." AND friendid=".pwEscape($friendid));
	echo "success";
	ajax_footer();

} elseif ($a == 'adddiarytype') {

	InitGP(array('u','name','b'),'P');
	if ((int)$b == 1) {
		echo "success\t$b";
		ajax_footer();
	}
	$u = (int) $u;

	$diaryService = L::loadClass('Diary', 'diary'); /* @var $diaryService PW_Diary */
	
	$data = array(
		'uid'	=>	$u,
		'name'	=>	$name
	);
	$id = $diaryService->addTypeByDiary($data);
	if ($id) {
		echo "success\t$id";
		ajax_footer();
	} else {
		Showmsg('undefined_action');
	}
} elseif ($a == 'deldiarytype') {

	InitGP(array('u','dtid'),'P',2);
	$where = '';
	if (!$isGM) {
		if (!$u) Showmsg('undefined_action');
		if ($u != $winduid) Showmsg('undefined_action');
		$where .= " AND uid=".pwEscape($u);
	}

	if (!$dtid) Showmsg('undefined_action');
	
	$diaryService = L::loadClass('Diary', 'diary'); /* @var $diaryService PW_Diary */
	$affected_rows = $diaryService->delDiaryTypeByDtid($dtid);
	if ($affected_rows) {
		echo "success";
		ajax_footer();
	} else {
		Showmsg('undefined_action');
	}
} elseif ($a == 'eidtdiarytype') {

	InitGP(array('u','dtid','name'),'P');
	$u = (int) $u;
	$dtid = (int) $dtid;
	
	$diaryService = L::loadClass('Diary', 'diary'); /* @var $diaryService PW_Diary */
	$data = array(
		'name'	=>	$name
	);
	$affected_rows = $diaryService->editTypeByDiary($u, $dtid, $data);
	if ($affected_rows) {
		echo "success";
		ajax_footer();
	} else {
		Showmsg('undefined_action');
	}
} elseif ($a == 'deldiary') {

	InitGP(array('id'),'',2);
	if (!$id) Showmsg('undefined_action');

	$diaryService = L::loadClass('Diary', 'diary'); /* @var $diaryService PW_Diary */
	$diary = $diaryService->get($id);
	$diaryService->delDiary($id);
	$weiboService = L::loadClass('weibo','sns'); /* @var $weiboService PW_Weibo */
	$weibo = $weiboService->getWeibosByObjectIdsAndType($id,'diary');
	if ($weibo) {
		$weiboService->deleteWeibos($weibo['mid']);
	}
//	$diary = $db->get_one("SELECT did,dtid,uid,username FROM pw_diary WHERE did=".pwEscape($id));
//	!$diary && Showmsg('mode_o_no_diary');
//
//	if ($winduid != $diary['uid'] && !$isGM) {
//		Showmsg('mode_o_deldiary_permit_err');
//	}
//	$db->update("DELETE FROM pw_diary WHERE did=".pwEscape($id));
//	$db->update("UPDATE pw_diarytype SET num=num-1 WHERE dtid=".pwEscape($diary['dtid']));
	if ($affected_rows = delAppAction('diary',$id)) {
		countPosts("-$affected_rows");
	}
	$userCache = L::loadClass('Usercache', 'user');
	$userCache->delete($winduid, 'carddiary');
	/*
	$usercache = L::loadDB('Usercache', 'user');
	$usercache->delete($winduid, 'diary', $id);
	*/
	//积分变动
	require_once(R_P.'require/credit.php');
	$o_diary_creditset = unserialize($o_diary_creditset);
	$creditset = getCreditset($o_diary_creditset['Delete'],false);
	$creditset = array_diff($creditset,array(0));
	if (!empty($creditset)) {
		require_once(R_P.'require/postfunc.php');
		$credit->sets($diary['uid'],$creditset,true);
		updateMemberid($diary['uid'],false);
	}

	if ($creditlog = unserialize($o_diary_creditlog)) {
		addLog($creditlog['Delete'],$diary['username'],$diary['uid'],'diary_Delete');
	}

	updateUserAppNum($diary['uid'],'diary','minus');
	echo "success\t";ajax_footer();

} elseif ($a == 'copydiary') {

	InitGP(array('did','dtid','privacy'));
	if (!$did) Showmsg('undefined_action');
	$diary = $db->get_one("SELECT d.did,d.aid,d.uid,d.dtid,d.subject,d.content,d.ifconvert,d.ifwordsfb,d.ifcopy,m.username FROM pw_diary d LEFT JOIN pw_members m USING(uid) WHERE d.did=".pwEscape($did));

	!$diary['ifcopy'] && Showmsg('mode_o_copy_permit_err');

	$diary['copyurl'] = $diary['uid']."|".$diary['username']."|{$GLOBALS[db_bbsurl]}/apps.php?q=diary&uid={$diary[uid]}&a=detail&did={$diary[did]}";

	/*$rt = $db->get_one("SELECT name FROM pw_diarytype WHERE dtid=".pwEscape($diary['dtid'])." AND uid=".pwEscape($diary['uid']));
	if ($rt['name']) {
		$check = $db->get_one("SELECT dtid FROM pw_diarytype WHERE uid=".pwEscape($winduid)." AND name=".pwEscape($rt['name']));
		$count = $db->get_value("SELECT COUNT(*) AS count FROM pw_diarytype WHERE uid=".pwEscape($winduid));
		if (!$check && $count <= 20) {
			$db->update("INSERT INTO pw_diarytype(uid,name,num) VALUES(".pwEscape($winduid).",".pwEscape($rt['name']).",1)");
			$dtid = $db->insert_id();
		} elseif ($count > 20) {
			$dtid = 0;
		} else {
			$dtid = $check['dtid'];
			$db->update("UPDATE pw_diarytype SET num=num+1 WHERE dtid=".pwEscape($dtid));
		}
	}*///分类不存在则自动生成分类
	
	$dtid = (int)$dtid;
	$privacy = (int)$privacy;

	$pwSQL = pwSqlSingle(array(
		'uid'		=> $winduid,
		'dtid'		=> $dtid,
		'username'	=> $windid,
		'privacy'	=> $privacy,
		'subject'	=> $diary['subject'],
//		'content'	=> $diary['content'],
		'copyurl'	=> $diary['copyurl'],
		'ifcopy'	=> $diary['ifcopy'],
		'ifconvert'	=> $diary['ifconvert'],
		'ifwordsfb'	=> $diary['ifwordsfb'],
		'postdate'	=> $timestamp,
	));


	$db->update("INSERT INTO pw_diary SET $pwSQL");
	$did = $db->insert_id();
	
	$db->update("UPDATE pw_diarytype SET num=num+1 WHERE uid=".pwEscape($winduid)." AND dtid=".pwEscape($dtid));//更新分类日志数
	
	//*=======拷贝图片待优化===========*//
	$diaryAttachs = $diary['aid'] ? unserialize($diary['aid']) : array();
	L::loadClass('upload', '', false);
	require_once(R_P.'require/imgfunc.php');
	$uploadSerivce = new PwUpload();
	$ifthumb = 0;
	if ($o_attachdir) {
		if ($o_attachdir == 1) {
			$savedir = "Type_$attach_ext";
		} elseif ($o_attachdir == 2) {
			$savedir = 'Mon_'.date('ym');
		} elseif ($o_attachdir == 3) {
			$savedir = 'Day_'.date('ymd');
		}
	}

	foreach ($diaryAttachs as $at) {
		if ($at['type'] == 'img') {
			$a_url = geturl('diary/'.$at['attachurl'],'show');
			$attach_ext = strtolower(substr(strrchr($a_url[0],'.'),1));
			$prename = substr(md5($timestamp.randstr(8)),10,15);
			$filename = $winduid."_{$did}_$prename.$attach_ext";
			$attachurl = "$savedir/$filename";
			$fileuplodeurl = "$attachdir/diary/$attachurl";
			$uploadSerivce->postupload($a_url[0],$fileuplodeurl);
			
			if ($db_ifathumb) {
				$thumbdir = "thumb/diary/$attachurl";
				$thumburl = "$attachdir/$thumbdir";
				$ifthumb = 1;
				$thumbsize = $uploadSerivce->MakeThumb($fileuplodeurl,$thumburl,$db_athumbsize,$ifthumb);
			}
			
			$data = array(
			'did'		=> $did,				'uid'		=> $winduid,
			'hits'		=> 0,					'name'		=> $at['name'],
			'type'		=> $at['type'],			'size'		=> $at['size'],
			'attachurl'	=> $attachurl,			'needrvrc'	=> $value['needrvrc'],
			'special'	=> $at['special'],		'ctype'		=> $at['ctype'],
			'uploadtime'=> $timestamp,			'descrip'	=> $at['descrip'],
			'ifthumb'	=> 0
			);
			$db->update("INSERT INTO pw_attachs SET ".pwSqlSingle($data));
			$aid = $db->insert_id();
			$data['aid'] = $aid;
			$aids[] = $data['aid'];
			$diaryAid[$aid] = $data;
		}
	}	
	//*=======拷贝图片===========*//
	
	$diaryAid = $diaryAid ? serialize($diaryAid) : '';
	
	if ($aids) {
		preg_match_all('/attachment=(\d+)/i', $diary['content'], $result);
		$diary['content'] = str_replace($result[1], $aids, $diary['content']);
	}
	
	$db->update("UPDATE pw_diary SET aid = ".pwEscape($diaryAid).",content=".pwEscape($diary['content'])." WHERE did=".pwEscape($did)." AND uid=".pwEscape($winduid));
	countPosts('+1');
	updateUserAppNum($winduid,'diary');
	echo "success\t$did";ajax_footer();
} elseif ($a == 'feedsetting') {
	if (empty($_POST['step'])) {
		$friend = getFriends($winduid);
		if (empty($friend)) Showmsg('no_friend');
		foreach ($friend as $key => $value) {
			$value['iffeed'] && $checked[$key] = 'CHECKED';
			$frienddb[$value['ftid']][] = $value;
		}
		$query = $db->query("SELECT * FROM pw_friendtype WHERE uid=".pwEscape($winduid)." ORDER BY ftid");
		$friendtype = array();
		while ($rt = $db->fetch_array($query)) {
			$friendtype[$rt['ftid']] = $rt;
		}
		$no_group_name = getLangInfo('other','no_group_name');
		$friendtype[0] = array('ftid' => 0,'uid' => $winduid,'name' => $no_group_name);
		require_once PrintEot('m_ajax');ajax_footer();
	} else {
		InitGP(array('selid'));
		if (!empty($selid)) {
			$db->update("UPDATE pw_friends SET iffeed='1' WHERE uid=".pwEscape($winduid)." AND friendid IN (".pwImplode($selid).")");
			$db->update("UPDATE pw_friends SET iffeed='0' WHERE uid=".pwEscape($winduid)." AND friendid NOT IN (".pwImplode($selid).")");
		} else {
			$db->update("UPDATE pw_friends SET iffeed='0' WHERE uid=".pwEscape($winduid));
		}
		Showmsg('o_feedfriend_success');
	}
} elseif ($a == 'mutiuploadphoto') {
	InitGP(array('aid'));
	!$aid && Showmsg('select_ablum');
	!$winduid && Showmsg('undefined_action');
	$rt = $db->get_one("SELECT atype,ownerid,photonum FROM pw_cnalbum WHERE aid=" . pwEscape($aid));
	if (empty($rt)) {
		Showmsg('undefined_action');
	}
	if ($rt['atype'] == 1) {
		$colony = $db->get_one("SELECT * FROM pw_colonys WHERE id=" . pwEscape($rt['ownerid']));
		$level = $colony['speciallevel'] ? $colony['speciallevel'] : $colony['commonlevel'];
		$o_maxphotonum = $db->get_value("SELECT maxphotonum FROM pw_cnlevel WHERE id=" . pwEscape($level));
	} else {
		$winduid != $rt['ownerid'] && Showmsg('colony_phototype');
	}
	$o_maxphotonum && $rt['photonum'] >= $o_maxphotonum && Showmsg('mutiupload_photofull');
	echo "success";ajax_footer();
}

function getUserNameByTypeAndId($type,$id) {
	global $db;
	switch ($type) {
		case 'share':
			return $db->get_value("SELECT username FROM pw_collection WHERE id=".pwEscape($id));
			break;
		case 'write':
			return $db->get_value("SELECT m.username FROM pw_owritedata o LEFT JOIN pw_members m ON o.uid=m.uid WHERE o.id=".pwEscape($id));
			break;
		case 'photo':
			return $db->get_value("SELECT uploader FROM pw_cnphoto WHERE pid=".pwEscape($id));
			break;
		case 'diary':
			return $db->get_value("SELECT username FROM pw_diary WHERE did=".pwEscape($id));
			break;
		default:
			return false;
	}
}
?>