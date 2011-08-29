<?php
!defined('A_P') && exit('Forbidden');

$baseUrl = 'apps.php?';
$basename = 'apps.php?q='.$q.'&';

define('AJAX','1');
define('F_M',true);
S::gp(array('a'));

if (!in_array($a,array('showgroupwritecommlist'))) {
	!$winduid && Showmsg('not_login');
}

require_once(R_P.'require/functions.php');
//* include_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
pwCache::getData(D_P . 'data/bbscache/o_config.php');

$isGM = S::inArray($windid,$manager);
!$isGM && $groupid==3 && $isGM=1;
$tpc_author = $windid;

if ($a == 'delshare') {

	S::gp(array('id'),'',2);
	if (!$id) Showmsg('undefined_action');
	$share = $db->get_one("SELECT * FROM pw_collection WHERE id=".S::sqlEscape($id));
	!$share && Showmsg('mode_o_no_share');
	if ($winduid != $share['uid'] && !$isGM) {
		Showmsg('mode_o_delshare_permit_err');
	}
	$db->update("DELETE FROM pw_collection WHERE id=".S::sqlEscape($id));
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

	S::gp(array('u'),'',2);
	if ($u) {
		if ($friend = getOneFriend($u)) {
			$db->update("DELETE FROM pw_friends WHERE (uid=".S::sqlEscape($winduid)." AND friendid=".S::sqlEscape($u).") OR (uid=".S::sqlEscape($u)." AND friendid=".S::sqlEscape($winduid).")");
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

	S::gp(array('u'),'',2);
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
		$db->update("REPLACE INTO pw_friends(uid,friendid,joindate,status) VALUES ".S::sqlMulti($pwSQL,false));
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

	S::gp(array('type','id'),'P');
	$id = (int)$id;
	if (!$id) Showmsg('undefined_action');
	if(!checkCommType($type)) Showmsg('undefined_action');
	require_once(R_P.'require/showimg.php');
	require_once(R_P.'require/bbscode.php');
	$wordsfb = L::loadClass('FilterUtil', 'filter');
	$orderby = $type == 'groupwrite' ? 'ASC' : 'DESC';
	$comment = array();
	$query = $db->query("SELECT c.*,m.icon as face,m.groupid FROM pw_comment c LEFT JOIN pw_members m ON c.uid=m.uid WHERE c.type=".S::sqlEscape($type)." AND c.typeid=".S::sqlEscape($id)." AND upid='0' ORDER BY c.postdate $orderby".S::sqlLimit(0,100));
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

	S::gp(array('type','id'),'P');
	$id = (int)$id;
	if (!$id) Showmsg('undefined_action');

	//根据$id取出群组的ID
	$cyid = $db->get_value("SELECT cyid FROM pw_cwritedata WHERE id=".S::sqlEscape($id));
	require_once(R_P . 'apps/groups/lib/colony.class.php');
	$newColony = new PwColony($cyid);
	if (!$colony =& $newColony->getInfo()) {
		Showmsg('data_error');
	}
	$isGM = S::inArray($windid,$manager);
	$ifadmin = ($colony['ifadmin'] == '1' || $colony['admin'] == $windid || $isGM || $SYSTEM['colonyright']);

	require_once(R_P.'require/showimg.php');
	require_once(R_P.'require/bbscode.php');
	$wordsfb = L::loadClass('FilterUtil', 'filter');
	$comment = array();
	$query = $db->query("SELECT c.*,m.icon as face,m.groupid FROM pw_comment c LEFT JOIN pw_members m ON c.uid=m.uid WHERE c.type='groupwrite' AND c.typeid=".S::sqlEscape($id)." AND upid='0' ORDER BY c.postdate ASC".S::sqlLimit(0,100));
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
	S::gp(array('type','id','title','upid','position','other'),'P');
	
	$title 	= nl2br(str_replace('&#61;','=',$title));
	$id = (int)$id;
	$upid = (int)$upid ? (int)$upid : 0;
	if (!$id) Showmsg('undefined_action');
	if(!checkCommType($type)) Showmsg('undefined_action');
	$app_table = $id_filed = '';
	list($app_table,$id_filed) = getCommTypeTable($type);
	$count = $db->get_value("SELECT COUNT(*) AS count FROM pw_comment WHERE type=".S::sqlEscape($type)." AND typeid=".S::sqlEscape($id));
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
	$db->update("INSERT INTO pw_comment SET ".S::sqlSingle($data));
	$insertid = $db->insert_id();
	if ($app_table && $id_filed) {
		$db->update("UPDATE $app_table SET c_num=c_num+1 WHERE $id_filed=".S::sqlEscape($id));
	}
	if ($insertid) {

		//zhudong 当为群组的记录的时候，更改群组记录的发表时间
		if ($type == 'groupwrite') {
			$db->update("UPDATE pw_cwritedata SET replay_time=".S::sqlEscape($timestamp)." WHERE id=".S::sqlEscape($id));
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
			/* change to notice modified for phpwind8.5 
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
			*/
			M::sendNotice(
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
				'notice_comment_'.$type,
				'notice_comment'
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
	S::gp(array('id'),'P',2);
	if (!$id) Showmsg('undefined_action');
	$thiscomm = $db->get_one("SELECT uid,type,typeid FROM pw_comment WHERE id=".S::sqlEscape($id));
	if (!$isGM && $thiscomm['uid'] != $winduid) {
		if ($thiscomm['type'] == 'groupwrite') {
			$colony = $db->get_one("SELECT c.admin,cb.ifadmin FROM pw_cwritedata cw LEFT JOIN pw_colonys c ON cw.cyid=c.id LEFT JOIN pw_cmembers cb ON c.id=cb.colonyid AND cb.uid=" . S::sqlEscape($winduid) . " WHERE cw.id=" . S::sqlEscape($thiscomm['typeid']));
			if ($colony['admin'] != $windid && $colony['ifadmin'] != 1) {
				Showmsg('mode_o_com_del_priv');
			}
		} elseif ($thiscomm['type'] == 'active') {
			$colony = $db->get_one("SELECT c.admin,cb.ifadmin FROM pw_active a LEFT JOIN pw_colonys c ON a.cid=c.id LEFT JOIN pw_cmembers cb ON c.id=cb.colonyid AND cb.uid=" . S::sqlEscape($winduid) . " WHERE a.id=" . S::sqlEscape($thiscomm['typeid']));
			if ($colony['admin'] != $windid && $colony['ifadmin'] != 1) {
				Showmsg('mode_o_com_del_priv');
			}
		} elseif ($thiscomm['type'] == 'groupphoto') {
			$colony = $db->get_one("SELECT c.admin,cb.ifadmin FROM pw_cnphoto cp LEFT JOIN pw_cnalbum ca ON cp.aid=ca.aid LEFT JOIN pw_colonys c ON ca.ownerid=c.id LEFT JOIN pw_cmembers cb ON ca.ownerid=cb.colonyid AND cb.uid=" . S::sqlEscape($winduid) . " WHERE cp.pid=" . S::sqlEscape($thiscomm['typeid']));
			if ($colony['admin'] != $windid && $colony['ifadmin'] != 1) {
				Showmsg('mode_o_com_del_priv');
			}
		} else {
			Showmsg('mode_o_com_del_priv');
		}
	}

	$updatenum = 0;
	$db->update("DELETE FROM pw_comment WHERE id=".S::sqlEscape($id));
	$updatenum += $db->affected_rows();
	$db->update("DELETE FROM pw_comment WHERE upid=".S::sqlEscape($id));
	$updatenum += $db->affected_rows();
	list($app_table,$app_filed) = getCommTypeTable($thiscomm['type']);
	if ($updatenum && $app_table && $thiscomm['typeid']) {
		$db->update("UPDATE $app_table SET c_num=c_num-".S::sqlEscape($updatenum)." WHERE $app_filed=".S::sqlEscape($thiscomm['typeid']));
	}
	countPosts("-$updatenum");
	echo "success\t$id";
	ajax_footer();
} elseif ($a == 'addfriendtype') {
	S::gp(array('u','name'),'P');
	$u = (int) $u;
	if (!$u) Showmsg('undefined_action');
	if ($u != $winduid && !$isGM) Showmsg('undefined_action');
	if (strlen($name)<1 || strlen($name)>20) Showmsg('mode_o_addftype_name_leng');
	$check = $db->get_one("SELECT ftid FROM pw_friendtype WHERE uid=".S::sqlEscape($u)." AND name=".S::sqlEscape($name));
	if ($check) Showmsg('mode_o_addftype_name_exist');
	$count = $db->get_value("SELECT COUNT(*) AS count FROM pw_friendtype WHERE uid=".S::sqlEscape($u));
	if ($count>=20) Showmsg('mode_o_addftype_length');
	$db->update("INSERT INTO pw_friendtype(uid,name) VALUES(".S::sqlEscape($u).",".S::sqlEscape($name).")");
	$id = $db->insert_id();
	if ($id) {
		echo "success\t$id";
		ajax_footer();
	} else {
		Showmsg('undefined_action');
	}
} elseif ($a == 'delfriendtype') {
	S::gp(array('u','dtid'),'P',2);
	$ftid = (int) $dtid;
	$where = '';
	if (!$isGM) {
		if (!$u) Showmsg('undefined_action');
		if ($u != $winduid) Showmsg('undefined_action');
		$where .= " AND uid=".S::sqlEscape($u);
	}

	if (!$ftid) Showmsg('undefined_action');
	$db->update("DELETE FROM pw_friendtype WHERE ftid=".S::sqlEscape($ftid)."$where");
	if ($db->affected_rows()) {
		$db->update("UPDATE pw_friends SET ftid=0 WHERE ftid=".S::sqlEscape($ftid));
		echo "success";
		ajax_footer();
	} else {
		Showmsg('undefined_action');
	}
} elseif ($a == 'eidtfriendtype') {
	S::gp(array('u','dtid','name'),'P');
	$u = (int) $u;
	$ftid = (int) $dtid;
	if (!$u) Showmsg('undefined_action');
	if (!$isGM) {
		if ($u != $winduid) Showmsg('mode_o_addftype_n');
	}
	if (!$ftid) Showmsg('undefined_action');
	if (strlen($name)<1 || strlen($name)>20) Showmsg('mode_o_addftype_name_leng');
	$check = $db->get_one("SELECT ftid FROM pw_friendtype WHERE uid=".S::sqlEscape($u)." AND name=".S::sqlEscape($name));
	if ($check) Showmsg('mode_o_addftype_name_exist');
	$db->update("UPDATE pw_friendtype SET name=".S::sqlEscape($name)." WHERE uid=".S::sqlEscape($u)." AND ftid=".S::sqlEscape($ftid));
	if ($db->affected_rows()) {
		echo "success";
		ajax_footer();
	} else {
		Showmsg('undefined_action');
	}
} elseif ($a == 'postboard') {

	require_once(R_P.'require/postfunc.php');
	banUser();
	S::gp(array('uid','title'),'P');
	$title 	= str_replace('&#61;','=',$title);
	$uid = (int)$uid;
	if (!$uid) Showmsg('undefined_action');
	if ($uid == $winduid) Showmsg('mode_o_board_self');
//	if (!isFriend($uid,$winduid)) Showmsg('mode_o_board_not_friend'); //去掉非好友不能留言
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
	$db->update("INSERT INTO pw_oboard SET ".S::sqlSingle($data));
	$thisid = $db->insert_id();

	$userCache = L::loadClass('UserCache', 'user');//ismodify
	$userCache->delete($uid, 'messageboard');

	if ($thisid) {
		/* change to notice modified for phpwind8.5
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
		*/
		M::sendNotice(
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
			'notice_guestbook',
			'notice_guestbook'
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

	S::gp(array('id'),'P',2);
	if (!$id) Showmsg('undefined_action');
	$board = $db->get_one("SELECT * FROM pw_oboard WHERE id=" . S::sqlEscape($id));
	if (!$board || (!$isGM && $board['uid'] != $winduid && $board['touid'] != $winduid)) {
		Showmsg('undefined_action');
	}
	$db->update("DELETE FROM pw_oboard WHERE id=" . S::sqlEscape($id));

	$userCache = L::loadClass('UserCache', 'user');//ismodify
	$userCache->delete($board['touid'], 'messageboard');

	$affected_rows = delAppAction('board',$id)+1;
	countPosts("-$affected_rows");
	echo "success";
	ajax_footer();

} elseif ($a == 'showftlist') {

	S::gp(array('u'),'P',2);
	if (!$u) Showmsg('undefined_action');
	if ($u != $winduid) Showmsg('undefined_action');
	$query = $db->query("SELECT * FROM pw_friendtype WHERE uid=".S::sqlEscape($u)." ORDER BY ftid");
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

	S::gp(array('friendid','ftid'),'P',2);
	!$ftid && $ftid = 0;
	if (!$friendid) Showmsg('undefined_action');
	$db->update("UPDATE pw_friends SET ftid=".S::sqlEscape($ftid)." WHERE uid=".S::sqlEscape($winduid)." AND friendid=".S::sqlEscape($friendid));
	echo "success";
	ajax_footer();

} elseif ($a == 'delcollecttype') { 
	S::gp(array('u','dtid'),'P');
	$collectionTypeService = L::loadClass('CollectionTypeService', 'collection'); /* @var $collectiontype PW_Collection */
	$collectionService = L::loadClass('Collection', 'collection'); /* @var $collection PW_Collection */
	if (!$u) Showmsg('undefined_action');
	if ($u != $winduid) Showmsg('undefined_action');
	if (!$dtid) Showmsg('undefined_action');	
	if(!$collectionTypeService->delete($dtid)) Showmsg('undefined_action');
	$collectionService->updateByCtid($dtid);
	echo "success";
	ajax_footer();
} elseif ($a == 'addcollecttype') { 
	S::gp(array('u','name'),'P');
	if (strlen($name) > 20) Showmsg('stamp_name_length');
	$collectionTypeService = L::loadClass('CollectionTypeService', 'collection'); /* @var $collectiontype PW_Collection */
	$dataExist = $collectionTypeService->checkTypeExist((int)$u, $name);
	if (!$dataExist) Showmsg('stamp_have_exist');
	$data = array(
		'uid'	=>	(int)$u,
		'name'	=>	$name
	);
	$id = $collectionTypeService->insert($data);
	(!$id) && Showmsg('undefined_action');
	echo "success\t$id";
	ajax_footer();
} elseif ($a == 'editcollecttype') { 
	S::gp(array('u','dtid','name'),'P');
	if (strlen($name) > 20) Showmsg('stamp_name_length');
	$collectionTypeService = L::loadClass('CollectionTypeService', 'collection'); /* @var $collectiontype PW_Collection */
	$dataExist = $collectionTypeService->checkTypeExist((int)$u, $name, $dtid);
	if (!$dataExist) Showmsg('stamp_have_exist');
	$data = array(
		'name'	=>	$name
	);
	$row = $collectionTypeService->update($data, $dtid);
	($row<0) && Showmsg('undefined_action');
	echo "success";
	ajax_footer();
} elseif ($a == 'adddiarytype') {
	S::gp(array('u','name','b'),'P');
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

	S::gp(array('u','dtid'),'P',2);
	$where = '';
	if (!$isGM) {
		if (!$u) Showmsg('undefined_action');
		if ($u != $winduid) Showmsg('undefined_action');
		$where .= " AND uid=".S::sqlEscape($u);
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

	S::gp(array('u','dtid','name'),'P');
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

	S::gp(array('id'),'',2);
	if (!$id) Showmsg('undefined_action');

	$diaryService = L::loadClass('Diary', 'diary'); /* @var $diaryService PW_Diary */
	$diary = $diaryService->get($id);
	$diaryService->delDiary($id);
	$weiboService = L::loadClass('weibo','sns'); /* @var $weiboService PW_Weibo */
	$weibo = $weiboService->getWeibosByObjectIdsAndType($id,'diary');
	if ($weibo) {
		$weiboService->deleteWeibos($weibo['mid']);
	}
//	$diary = $db->get_one("SELECT did,dtid,uid,username FROM pw_diary WHERE did=".S::sqlEscape($id));
//	!$diary && Showmsg('mode_o_no_diary');
//
//	if ($winduid != $diary['uid'] && !$isGM) {
//		Showmsg('mode_o_deldiary_permit_err');
//	}
//	$db->update("DELETE FROM pw_diary WHERE did=".S::sqlEscape($id));
//	$db->update("UPDATE pw_diarytype SET num=num-1 WHERE dtid=".S::sqlEscape($diary['dtid']));
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

	S::gp(array('did','dtid','privacy'));
	if (!$did) Showmsg('undefined_action');
	$diary = $db->get_one("SELECT d.did,d.aid,d.uid,d.dtid,d.subject,d.content,d.ifconvert,d.ifwordsfb,d.ifcopy,m.username FROM pw_diary d LEFT JOIN pw_members m USING(uid) WHERE d.did=".S::sqlEscape($did));

	!$diary['ifcopy'] && Showmsg('mode_o_copy_permit_err');

	$diary['copyurl'] = $diary['uid']."|".$diary['username']."|{$GLOBALS[db_bbsurl]}/apps.php?q=diary&uid={$diary[uid]}&a=detail&did={$diary[did]}";

	/*$rt = $db->get_one("SELECT name FROM pw_diarytype WHERE dtid=".S::sqlEscape($diary['dtid'])." AND uid=".S::sqlEscape($diary['uid']));
	if ($rt['name']) {
		$check = $db->get_one("SELECT dtid FROM pw_diarytype WHERE uid=".S::sqlEscape($winduid)." AND name=".S::sqlEscape($rt['name']));
		$count = $db->get_value("SELECT COUNT(*) AS count FROM pw_diarytype WHERE uid=".S::sqlEscape($winduid));
		if (!$check && $count <= 20) {
			$db->update("INSERT INTO pw_diarytype(uid,name,num) VALUES(".S::sqlEscape($winduid).",".S::sqlEscape($rt['name']).",1)");
			$dtid = $db->insert_id();
		} elseif ($count > 20) {
			$dtid = 0;
		} else {
			$dtid = $check['dtid'];
			$db->update("UPDATE pw_diarytype SET num=num+1 WHERE dtid=".S::sqlEscape($dtid));
		}
	}*///分类不存在则自动生成分类

	$dtid = (int)$dtid;
	$privacy = (int)$privacy;
   /**
	$pwSQL = S::sqlSingle(array(
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
	$db->update("INSERT INTO pw_diary SET $pwSQL");****/
	$pwSQL =array(
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
	);
	pwQuery::insert('pw_diary', $pwSQL);
	$did = $db->insert_id();

	$db->update("UPDATE pw_diarytype SET num=num+1 WHERE uid=".S::sqlEscape($winduid)." AND dtid=".S::sqlEscape($dtid));//更新分类日志数

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
			$db->update("INSERT INTO pw_attachs SET ".S::sqlSingle($data));
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
	//$db->update("UPDATE pw_diary SET aid = ".S::sqlEscape($diaryAid).",content=".S::sqlEscape($diary['content'])." WHERE did=".S::sqlEscape($did)." AND uid=".S::sqlEscape($winduid));
	pwQuery::update('pw_diary', 'did=:did AND uid=:uid', array($did, $winduid), array('aid' => $diaryAid, 'content' => $diary['content']));
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
		$query = $db->query("SELECT * FROM pw_friendtype WHERE uid=".S::sqlEscape($winduid)." ORDER BY ftid");
		$friendtype = array();
		while ($rt = $db->fetch_array($query)) {
			$friendtype[$rt['ftid']] = $rt;
		}
		$no_group_name = getLangInfo('other','no_group_name');
		$friendtype[0] = array('ftid' => 0,'uid' => $winduid,'name' => $no_group_name);
		require_once PrintEot('m_ajax');ajax_footer();
	} else {
		S::gp(array('selid'));
		if (!empty($selid)) {
			$db->update("UPDATE pw_friends SET iffeed='1' WHERE uid=".S::sqlEscape($winduid)." AND friendid IN (".S::sqlImplode($selid).")");
			$db->update("UPDATE pw_friends SET iffeed='0' WHERE uid=".S::sqlEscape($winduid)." AND friendid NOT IN (".S::sqlImplode($selid).")");
		} else {
			$db->update("UPDATE pw_friends SET iffeed='0' WHERE uid=".S::sqlEscape($winduid));
		}
		Showmsg('o_feedfriend_success');
	}
} elseif ($a == 'mutiuploadphoto') {
	S::gp(array('aid'));
	!$aid && Showmsg('select_ablum');
	!$winduid && Showmsg('undefined_action');
	$rt = $db->get_one("SELECT atype,ownerid,photonum FROM pw_cnalbum WHERE aid=" . S::sqlEscape($aid));
	if (empty($rt)) {
		Showmsg('undefined_action');
	}
	if ($rt['atype'] == 1) {
		$colony = $db->get_one("SELECT * FROM pw_colonys WHERE id=" . S::sqlEscape($rt['ownerid']));
		$level = $colony['speciallevel'] ? $colony['speciallevel'] : $colony['commonlevel'];
		$o_maxphotonum = $db->get_value("SELECT maxphotonum FROM pw_cnlevel WHERE id=" . S::sqlEscape($level));
	} else {
		$winduid != $rt['ownerid'] && Showmsg('colony_phototype');
	}
	$o_maxphotonum && $rt['photonum'] >= $o_maxphotonum && Showmsg('mutiupload_photofull');
	echo "success";ajax_footer();
} elseif ($a == 'addtag') {
	$memberTagsService = L::loadClass('MemberTagsService','user');
	($memberTagsService->countTagsByUid($winduid) >= 10) && Showmsg('u_tagsnum_limit');
	S::gp(array('tagname'));
	$tagname = str_replace('%26', '&', $tagname);
	(strlen($tagname) < 1 || strlen($tagname) > 16) && Showmsg('u_tags_limit');
	require_once(R_P . 'require/bbscode.php');
	$wordsfb = L::loadClass('FilterUtil', 'filter');
	if (($banword = $wordsfb->comprise($tagname)) !== false) Showmsg('u_tagname_wordsfb');
	$tags = $memberTagsService->getTagsByTagName($tagname);
	$memberTagData = array(
		'userid' => $winduid,
		'crtime' => $timestamp
	);
	if ($tags) {
		if ($memberTagsService->getTagsByTagidAndUid($tags['tagid'],$winduid)) Showmsg('u_tags_had');
		$memberTagData['tagid'] = $tags['tagid'];
		$memberTagsService->addMemberTags($memberTagData);
		$tagData['num'] = $tags['num']+1;
		$memberTagsService->updateTags($tagData,$tags['tagid']);
	} else {
		$tagData['tagname'] = $tagname;
		$tagData['num'] = 1;
		$memberTagData['tagid'] = $memberTagsService->addTags($tagData);
		$memberTagsService->addMemberTags($memberTagData);
		$tags['tagname'] = $tagname;
		$tags['tagid'] = $memberTagData['tagid'];
	}
	$userCache = L::loadClass('UserCache', 'user');//ismodify
	$userCache->delete($winduid, 'tags');
	
	require_once printEOT('m_ajax');ajax_footer();
}  elseif ($a == 'deltag') {
	S::gp(array('tagid'));

	if (!$tagid) Showmsg('undefined_action');
	$memberTagsService = L::loadClass('MemberTagsService','user');
	$tags = $memberTagsService->getTagsByTagidAndUid($tagid,$winduid);
	if (!$tags || (!$isGM && $tags['userid'] != $winduid) || $tags['tagid'] != $tagid) {
		Showmsg('undefined_action');
	}
	if ($memberTagsService->deleteMemberTags($tags['tagid'],$winduid)) {
		$memberTagsService->updateNumByTagId($tags['tagid'],'-1');
		$userCache = L::loadClass('UserCache', 'user');//ismodify
		$userCache->delete($tags['userid'], 'tags');
	}
	echo "success";
	ajax_footer();
}  elseif ($a == 'changetag') {
	$memberTagsService = L::loadClass('MemberTagsService','user');
	$hotTags = $memberTagsService->getTagsByNum();
	require_once printEOT('m_ajax');ajax_footer();
}  elseif ($a == 'addAttention') {
	S::gp(array('topicName'));
	$topicService = L::loadClass('topic','sns');
	if ($topicName == '') Showmsg('undefined_action');
	$topic = $topicService->getTopicByName($topicName);
	if (!$topic) $addTopic = $topicService->addTopic($topicName);
	$topicid = $topic ? $topic['topicid'] : $addTopic[$topicName];
	if ($topicService->getOneAttentionedTopic($topicid,$winduid)) Showmsg('topic_attention_repeat');
	$topicService->addAttentionTopic($topicid,$winduid);
	echo "success\t";ajax_footer();
}  elseif ($a == 'delAttention') {
	S::gp(array('topicName'));
	if (!$topicName) Showmsg('undefined_action');
	$topicService = L::loadClass('topic','sns');
	$topic = $topicService->getTopicByName($topicName);
	if (!$topic) Showmsg('undefined_action');
	if (!$topicService->getOneAttentionedTopic($topic['topicid'],$winduid)) Showmsg('topic_notAttentioned');
	if (!$isGM && $topic['userid'] != $winduid && $topic['topicname'] != $topicName) {
		Showmsg('undefined_action');
	}
	$topicService->deleteAttentionedTopic($topic['topicid'],$winduid);
	echo "success\t";ajax_footer();
} 

function getUserNameByTypeAndId($type,$id) {
	global $db;
	switch ($type) {
		case 'share':
			return $db->get_value("SELECT username FROM pw_collection WHERE id=".S::sqlEscape($id));
			break;
		case 'write':
			return $db->get_value("SELECT m.username FROM pw_owritedata o LEFT JOIN pw_members m ON o.uid=m.uid WHERE o.id=".S::sqlEscape($id));
			break;
		case 'photo':
			return $db->get_value("SELECT uploader FROM pw_cnphoto WHERE pid=".S::sqlEscape($id));
			break;
		case 'diary':
			return $db->get_value("SELECT username FROM pw_diary WHERE did=".S::sqlEscape($id));
			break;
		default:
			return false;
	}
}
?>