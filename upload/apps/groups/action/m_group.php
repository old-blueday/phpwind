<?php
!defined('A_P') && exit('Forbidden');

S::gp(array('a','digest','job', 'ajax', 'iscontinue'));
if ($a && in_array($a, array('join'))) {
	define('AJAX' , 1);
}
if ($ajax == 1) define('AJAX' , 1);
//!$winduid && Showmsg('not_login');

!$db_groups_open && Showmsg('groups_close');
SCR == 'mode' && ObHeader('apps.php?' . $pwServer['QUERY_STRING']);

if ($db_question && $o_groups_p_qcheck) {
	$qkey = array_rand($db_question);
}
S::gp(array('cyid','page'), null, 2);
$db_perpage = 10;

require_once(R_P . 'apps/groups/lib/colony.class.php');
$newColony = new PwColony($cyid);

if (!$colony =& $newColony->getInfo()) {
	Showmsg('data_error');
}

//当群组视图关闭状态下
$ajaxList = array('join', 'out', 'uintro', 'writepost', 'writedel', 'del', 'ajaxedit', 'fanoutmsg', 'checkpostright');
$acList = array('join', 'quit', 'delmember', 'sendmsg', 'exportmember', 'del');
if ($colony['viewtype'] == 1 && !(in_array($a, $ajaxList) || $a == 'active' && in_array($job, $acList))) {
	$newColony->jumpToBBS($q, $a, $cyid);
} elseif($colony['viewtype'] == '0') {
	$cnclass['fid'] = $db->get_value("SELECT fid FROM pw_cnclass WHERE fid=" . S::sqlEscape($colony['classid']). " AND ifopen=1");
}

require_once(R_P . 'require/bbscode.php');
$newColony->initBanner();
$groupRight =& $newColony->getRight();

if ($groupRight['modeset']['thread']['ifopen'] != 1 && $a == 'thread') {
	ObHeader("apps.php?q=group&cyid=$cyid");
}

$colony_name = $newColony->getNameStyle();
$descrip = convert($colony['descrip'], array());

if ($colony['ifcyer']) {
	if ($timestamp - $colony['lastvisit'] > 3600) {
		$db->update("UPDATE pw_cmembers SET lastvisit=" . S::sqlEscape($timestamp) . ' WHERE id=' . S::sqlEscape($colony['ifcyer']));
	}
}

$a_key = 'index';
$isGM = S::inArray($windid,$manager);
$ifadmin = $newColony->getIfadmin();
$ifcolonyadmin = $newColony->getColonyAdmin();
$ifbbsadmin = $newColony->getBbsAdmin($isGM);

$favortitle = str_replace(array("&#39;","'","\"","\\"),array("‘","\\'","\\\"","\\\\"), $colony['cname']);
$tmpActionUrl = 'thread.php?cyid=' . $cyid;

//邀请处理
if (GetCookie('o_invite') && $db_modes['o']['ifopen'] == 1) {
	list($o_u,$hash,$app) = explode("\t",GetCookie('o_invite'));
	if (is_numeric($o_u) && strlen($hash) == 18) {
		require_once(R_P.'require/o_invite.php');
	}
}

//SEO
require_once(R_P . 'apps/groups/lib/colonyseo.class.php');
$colonySeo = new Pw_ColonySEO($cyid);
$webPageTitle = $colonySeo->getPageTitle($groupRight['modeset'][$a]['title'],$colony['cname']);
$metaDescription = $colonySeo->getPageMetadescrip($colony['descrip']);
$metaKeywords = $colonySeo->getPageMetakeyword($colony['cname']);

if (empty($a)) {

	require_once(R_P . 'require/showimg.php');
	$annouce = convert(nl2br($colony['annouce']), $db_windpost);
	list($faceurl) = showfacedesign($winddb['icon'],1,'s');

	$colonyNums = PwColony::calculateCredit($colony);
	$udb = $uids = $newvisit = array();
	$indexModel = array('thread' => array(), 'galbum' => array(), 'write' => array());
	//话题区开始
	if ($groupRight['modeset']['thread']['ifopen'] && $groupRight['layout']['thread']['ifopen']) {
		$colony['count'] = 0;
		if ($colony['tnum'] > 0 && ($colony['ifopen'] || $ifadmin || $colony['ifcyer'])) {
			$_sql_where = $digest == 1 ? " AND a.digest=1" : '';
			$threadLimit = $groupRight['layout']['thread']['num'] > 0 ? intval($groupRight['layout']['thread']['num']) : 20;
			$argdb = $newColony->getArgument($_sql_where, 0, $threadLimit);
			$colony['count'] = $newColony->getArgumentCount($_sql_where);
			foreach ($argdb as $key => $rt) {
				$rt['postdate']	= get_date($rt['postdate'],'m-d H:m:s');
				$rt['lastpost'] = get_date($rt['lastpost']);
				$rt['sub_subject'] = substrs($rt['subject'], 48,'Y');
				$rt['sub_subject'] = $newColony->styleFormat($rt['sub_subject'], $rt['titlefont']);
				$indexModel['thread'][] = $rt;
				$uids[] = $rt['authorid'];
				$lastposter[] = $rt['lastposter'];
			}
		}
	}

	//相册区模块
	if ($groupRight['modeset']['galbum']['ifopen'] && $groupRight['layout']['galbum']['ifopen']) {
		if ($colony['photonum'] > 0) {
			$galbumLimit = $groupRight['layout']['galbum']['num'] > 0 ? intval($groupRight['layout']['galbum']['num']) : 8;
			$_sql_sel = !($colony['ifcyer'] && $colony['ifadmin'] != '-1') ? " AND ca.private='0'" : '';
			$query = $db->query("SELECT cp.pid,cp.path,cp.ifthumb,m.groupid FROM pw_cnalbum ca LEFT JOIN pw_cnphoto cp ON ca.aid=cp.aid LEFT JOIN pw_members m ON cp.uploader=m.username WHERE ca.atype='1'" . $_sql_sel . " AND ca.ownerid=" . S::sqlEscape($cyid) . ' ORDER BY cp.pid DESC' . S::sqlLimit($galbumLimit));
			while ($rt = $db->fetch_array($query)) {
				if (!$rt['pid']) continue;
				$rt['path'] = getphotourl($rt['path'], $rt['ifthumb']);
				if ($rt['groupid'] == 6 && $db_shield && $groupid != 3) {
					$rt['path'] = $pwModeImg.'/banuser.gif';
				}
				$indexModel['galbum'][] = $rt;
			}
		}
	}

	if ($groupRight['modeset']['active']['ifopen'] && $groupRight['layout']['active']['ifopen'] && $colony['activitynum']) {
		$activeLimit = $groupRight['layout']['active']['num'] > 0 ? intval($groupRight['layout']['active']['num']) : 4;
		require_once(A_P . 'groups/lib/active.class.php');
		$newActive = new PW_Active();
		$indexModel['active'] = $newActive->getActiveList($cyid, $activeLimit);
	}

	//记录区开始地方
	if ($groupRight['modeset']['write']['ifopen'] && $groupRight['layout']['write']['ifopen']) {
		$smileParser = L::loadClass('smileparser', 'smile'); /* @var $smileParser PW_SmileParser */
		if ($colony['writenum'] && ($colony['ifwriteopen'] || $ifadmin || $colony['ifcyer'])) {
			require_once(R_P.'require/showimg.php');
			$writeLimit = $groupRight['layout']['write']['num'] > 0 ? intval($groupRight['layout']['write']['num']) : 5;
			$query = $db->query("SELECT w.*,m.username,m.icon,m.groupid FROM pw_cwritedata w LEFT JOIN pw_members m ON w.uid=m.uid WHERE w.cyid=".S::sqlEscape($cyid)." ORDER BY w.replay_time DESC " . S::sqlLimit($writeLimit));
			while ($rt = $db->fetch_array($query)) {
				if ($rt['groupid'] == 6 && $db_shield && $groupid != 3) {
					$rt['content'] = appShield('ban_write');
				}
				//$rt['content'] = faceConvert($rt['content']);// 转换字符串中的表情[s:**]
				$rt['content'] = $smileParser->parse($rt['content']);
				list($rt['postdate']) = getLastDate($rt['postdate'],1);
				list($rt['icon']) = showfacedesign($rt['icon'],1,'m');

				$indexModel['write'][$rt['id']] = $rt;
				//$writedata[] = $rt;
				$typeid[] = $rt['id'];
			}
		}
		if ($typeid) {
			$query2 = $db->query("SELECT cm.*,m.icon FROM pw_comment cm LEFT JOIN pw_members m ON cm.uid=m.uid WHERE type='groupwrite' AND  typeid IN (".S::sqlImplode($typeid,false).") ORDER BY cm.id DESC");
			$tempTypeid = array();
			while ($rt2 = $db->fetch_array($query2)) {
				if(in_array($rt2['typeid'],$tempTypeid)) continue;
				$tempTypeid[] = $rt2['typeid'];
				$indexModel['write'][$rt2['typeid']]['replayuid'] = $rt2['uid'];
				$indexModel['write'][$rt2['typeid']]['replayusername'] = $rt2['username'];
				$indexModel['write'][$rt2['typeid']]['replaytitle'] = $rt2['title'];
				list($indexModel['write'][$rt2['typeid']]['replaypoastdate']) = getLastDate($rt2['postdate'],1);
				list($indexModel['write'][$rt2['typeid']]['replayicon']) = showfacedesign($rt2['icon'],1,'m');
			}
		}
	}
	$newColony->appendVisitor($winduid);
	$magdb = $newColony->getManager();
	foreach ($magdb as $key => $value) {
		if($value['username'] == $colony['admin']){
			unset($magdb[$key]);
			array_unshift($magdb,$value);
		}
	}
	$managerNum = count($magdb);
	$magdb = array_slice($magdb,0,9);
	$memdb = $newColony->getMembers(array('ifadmin'=>'3'), 10, 0, 'addtime');
	if (count($memdb)>9){
		$memdbNum = 10;
		array_pop($memdb);
	}
	$newvisit = $newColony->getVisitor(9);
	$newvisitnum = $newColony->getVisitorNum();
	$likegroup = $newColony->getLikeGroup();

	$uids = array_merge($uids, array_keys($magdb), array_keys($memdb), array_keys($newvisit));
	if ($uids) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		foreach ($userService->getByUserIds($uids) as $rt) {
			list($rt['faceurl']) = showfacedesign($rt['icon'], 1, 's');
			$udb[$rt['uid']] = $rt;
		}
	}
	if ($lastposter) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		foreach ($userService->getByUserNames($lastposter) as $rt) {
			list($rt['faceurl']) = showfacedesign($rt['icon'], 1, 'm');
			$lastposters[$rt['username']] = $rt;
		}
	}
	//用户浏览关注度
	//* $db->update("UPDATE pw_colonys SET visit=visit+1 WHERE id=" . S::sqlEscape($cyid));
	pwQuery::update('pw_colonys', 'id=:id', array($cyid), null, array(PW_EXPR=>array('visit=visit+1')));

	list($isheader,$isfooter,$tplname,$isleft) = array(false, true, "m_group", true);

} elseif ($a == 'thread') {

	if ($groupRight['modeset']['thread']['ifopen']) {
		$a_key = 'thread';
		if ($colony['ifopen'] || $ifadmin || $colony['ifFullMember']) {
			S::gp(array('keyword','digest'));
			$page < 1 && $page = 1;
		
			$_sql_where = '';
			if ($digest == 1) {
				$digest_current = 'current';
				$_sql_where = " AND a.digest=1";
				$urlAdd = 'digest=1&';
				$tmpActionUrl .= '&search=digest';
			} else {
				$all_current = 'current';
			}
			if ($keyword) {
				$s_keyword = '%'.$keyword.'%';
				$_sql_where = " AND t.subject like " . S::sqlEscape($s_keyword);
				$urlAdd .= $urlAdd."keyword=$keyword&";
			}
			$count = $newColony->getArgumentCount($_sql_where);
			$argdb = array();
		
			if ($count) {
				$pages = numofpage($count, $page, ceil($count/$db_perpage), "apps.php?q=group&a=thread&cyid=$cyid&{$urlAdd}");
				$argdb = $newColony->getArgument($_sql_where, ($page - 1) * $db_perpage, $db_perpage);
				foreach ($argdb as $key => $rt) {
					list($rt['format_lastpost'], $rt['lastpost']) = getLastDate($rt['lastpost']);
					$rt['sub_subject'] = substrs($rt['subject'], 60, 'Y');
		
					$keyword && $rt['sub_subject'] = preg_replace('/(?<=[^\s=]|^)('.preg_quote($keyword,'/').')(?=[^\s=]|$)/si','<font color="red"><u>\\1</u></font>',$rt['sub_subject']);
					$rt['sub_subject'] = $newColony->styleFormat($rt['sub_subject'], $rt['titlefont']);
					//$rt['sub_subject'] = substrs($rt['sub_subject'],38,'Y');
					$rt['sub_author'] = substrs($rt['author'],10,'..');
					$rt['tr_style'] = $key%2==0 ? '' : 'g_bgA';
					if ($rt['toolfield']) {
						list($t,$e) = explode(',',$rt['toolfield']);
						$sqladd = '';
						if ($t && $t<$timestamp) {
							$sqladd .= ",topped='0'";
							$rt['topped']='';
							$t='';
						}
						if ($e && $e<$timestamp) {
							$sqladd .= ",titlefont=''";
							$rt['titlefont']='';
							$e='';
						}
						if ($sqladd) {
							$rt['toolfield'] = $t.($e ? ','.$e : '');
							$db->update("UPDATE pw_argument SET toolfield=".S::sqlEscape($rt['toolfield'])." $sqladd WHERE tid=".S::sqlEscape($rt['tid']));
						}
					}
					$face_array[$rt['author']] = $rt['author'];
					$face_array[$rt['lastposter']] =$rt['lastposter'];
					$argdb[$key] = $rt;
				}
			}
			if (!empty($face_array)) {
				require_once(R_P . 'require/showimg.php');
		
				$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
				foreach ($userService->getByUserNames($face_array) as $rt) {
					$user_array[$rt['username']] = $rt;
					list($allFaceurl[$rt['username']]) = showfacedesign($rt['icon'],1,'s');
				}
			}
		
			list($isheader,$isfooter,$tplname,$isleft) = array(false, true, "m_group", true);
		}
	}
} elseif ($a == 'read') {

	$stylepath = 'wind'; //解析文章内容的时候作为图片的默认路径
	$a_key = 'thread';
	if (!$colony['ifopen'] && !$ifadmin && !$colony['ifFullMember']) {
		Showmsg('colony_cnmenber');
	}
	S::gp(array('tid'), null, 2);

	$tmpActionUrl = 'read.php?tid=' . $tid;
	$db_perpage = $db_readperpage;
	$page < 1 && $page = 1;
	$lou = ($page - 1) * $db_perpage;

	$S_sql = $J_sql = '';
	if ($page == 1) {
		$pw_tmsgs = GetTtable($tid);
		$S_sql = ',tm.*,m.uid,m.username,m.groupid,m.memberid,m.icon,m.userstatus,md.thisvisit';
		$J_sql = " LEFT JOIN $pw_tmsgs tm ON t.tid=tm.tid LEFT JOIN pw_members m ON m.uid=t.authorid LEFT JOIN pw_memberdata md ON m.uid=md.uid";
	}
	$read = $db->get_one("SELECT t.*{$S_sql},a.cyid,a.topped,a.digest FROM pw_threads t LEFT JOIN pw_argument a ON a.tid=t.tid{$J_sql} WHERE t.tid=" . S::sqlEscape($tid) . ' AND a.tid IS NOT NULL');
	$titletop1 = 'Re:' . $read['subject']; 
	if (empty($read) || $read['cyid'] != $cyid || $read['fid'] != $colony['classid'] || ($read['fid']==0 && $read['ifcheck']==1)) {
		Showmsg('data_error');
	}

	if (!$ifadmin && $read['locked']%3 == 2) {
		Showmsg('read_locked');
	}

	$webPageTitle = $colonySeo->getPageTitle($read['subject'],$colony['cname']);
	$metaDescription = $colonySeo->getPageMetadescrip($read['subject']);
	$metaKeywords = $colonySeo->getPageMetakeyword($read['subject'],$colony['cname']);
	
	$fid = $read['fid'];
	$foruminfo = L::forum($fid);

	$readdb	  = $_pids = $attachdb = array();
	$ptable	  = $read['ptable'];
	$pw_posts = GetPtable($ptable);
	$replies  = $read['replies'];
	$hits	  = $read['hits'];

	require_once(R_P.'require/showimg.php');
	require_once(R_P.'require/bbscode.php');

	$wordsfb = L::loadClass('FilterUtil', 'filter');
	if ($page == 1) {
		$read['pid'] = 'tpc';
		$readdb[] = $read;
		$read['aid'] && $_pids['tpc'] = 0;
		$lou--;
	}
	pwQuery::update('pw_threads', 'tid=:tid', array($tid), null, array(PW_EXPR=>array('hits=hits+1')));
	if ($read['replies'] > 0) {
		list($pages, $limit) = pwLimitPages($read['replies'], $page, "{$basename}a=$a&cyid=$cyid&tid=$tid&");
		$query = $db->query("SELECT t.*,m.uid,m.username,m.groupid,m.memberid,m.icon,m.userstatus,md.thisvisit FROM $pw_posts t LEFT JOIN pw_members m ON m.uid=t.authorid LEFT JOIN pw_memberdata md ON m.uid=md.uid WHERE t.tid=".S::sqlEscape($tid)." AND t.ifcheck='1' ORDER BY t.postdate $limit");
		while ($read = $db->fetch_array($query)) {
			$read['aid'] && $_pids[$read['pid']] = $read['pid'];
			$readdb[] = $read;
		}
	}
	if ($_pids) {
		extract(L::style());
		$attachShow = new attachShow(($isGM || $pwSystem['delattach']), $forumset['uploadset']);
		$attachShow->init($tid, $_pids);
	}
	$ifColonyAdmin = $ifadmin;
	foreach ($readdb as $key => $read) {
		$tpc_pid = $read['pid'];
		$tpc_author = $read['author'];
		$read['lou'] = ++$lou;
		$db_menuinit .= ",'read_$read[lou]' : 'read_1_$read[lou]'";

		if ($read['anonymous']) {
			$anonymous = (!$isGM && $winduid != $read['authorid'] && !$pwSystem['anonyhide']);
			$read['anonymousname'] = $GLOBALS['db_anonymousname'];
		} else {
			$anonymous = false;
			$read['anonymousname'] = $read['username'];
		}

		$read['ipfrom'] = $db_ipfrom==1 && $_G['viewipfrom'] ? $read['ipfrom'] : '';
		$read['ip'] = $pwSystem['viewip'] ? 'IP:'.$read['userip'] : '';

		if ($db_iftag && $read['tags']) {
			list($read['tag'], $tpc_tag) = getReadTag($read['tags']);
		}
		$tpc_shield = 0;
		$read['ifsign'] < 2 && $read['content'] = str_replace("\n","<br />",$read['content']);
		if ($read['ifshield'] || $read['groupid'] == 6 && $db_shield) {
			if ($read['ifshield'] == 2) {
				$read['content'] = shield('shield_del_article');
				$read['subject'] = '';
				$tpc_shield = 1;
			} else {
				if ($groupid == '3') {
					$read['subject'] = shield('shield_title');
				} else {
					$read['content'] = shield($read['ifshield'] ? 'shield_article' : 'ban_article');
					$read['subject'] = '';
					$tpc_shield = 1;
				}
			}
		}
		if (!$tpc_shield) {
			if (!$wordsfb->equal($read['ifwordsfb'])) {
				$read['content'] = $wordsfb->convert($read['content'], array(
					'id'	=> $read['pid'] == 'tpc' ? $tid : $read['pid'],
					'type'	=> $read['pid'] == 'tpc' ? 'topic' : 'posts',
					'code'	=> $read['ifwordsfb']
				));
			}
			if ($read['ifconvert'] == 2) {
				$read['content'] = convert($read['content'], $db_windpost);
			} else {
				//$tpc_tag && $db_readtag && $read['content'] = relatetag($read['content'], $tpc_tag);
				strpos($read['content'],'[s:') !== false && $read['content'] = showface($read['content']);
			}
			if ($read['aid'] && $GLOBALS['attachShow']->isShow($read['ifhide'], $tid)) {
				$read += $GLOBALS['attachShow']->parseAttachs($read['pid'], $read['content'], $winduid == $read['authorid']);
			}
		}
		list($read['icon']) = showfacedesign($read['icon'],1,'m');
		$read['postdate'] = get_date($read['postdate']);
		$readdb[$key] = $read;
	}
	$resubject = substrs(str_replace('&nbsp;',' ',$readdb['0']['subject']), $db_titlemax - 4);
	list($isheader,$isfooter,$tplname,$isleft) = array(false, true, "m_group", true);

} elseif ($a == 'post') {

	L::loadClass('forum', 'forum', false);
	$fid = $colony['classid'];
	$pwforum = new PwForum($colony['classid']);

	$a_key = 'thread';
	$tmpActionUrl = 'post.php?fid=' . $colony['classid'] . '&cyid=' . $cyid;

	$_G['uploadtype'] && $db_uploadfiletype = $_G['uploadtype'];
	$db_uploadfiletype = !empty($db_uploadfiletype) ? (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype)) : array();

	if (!$ifadmin && !$colony['ifFullMember']) {
		if (!$colony['ifopen']) {
			Showmsg('colony_cnmenber');
		} elseif ($colony['ifadmin'] == '-1') {
			Showmsg('colony_post');
		} else {
			Showmsg('colony_post2');
		}
	}

	if (empty($_POST['step'])) {
		
		$htmlpost = $attachHide = ($_G['allowhidden'] && ($pwforum->foruminfo['allowhide'] || empty($pwforum->foruminfo))) ? '' : 'disabled';
		$editor = getstatus($winddb['userstatus'], PW_USERSTATUS_EDITOR) ? 'wysiwyg' : 'textmode';
		$GLOBALS += getAttachConfig();

		list($isheader,$isfooter,$tplname,$isleft) = array(false, true, "m_group", true);

	} else {

		PostCheck(1,$o_groups_p_gdcheck,$o_groups_p_qcheck);
		/**
		* 禁止受限制用户发言
		*/
		banUser();

		L::loadClass('post', 'forum', false);
		require_once(R_P . 'require/bbscode.php');
		if (empty($colony['classid'])) {
			$pwforum->foruminfo['allowhide'] = 1;
		}
		if ($db_tcheck) { //内容验证
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userDataInfo = $userService->get($winduid, false, true, false);
			$winddb['postcheck'] = $userDataInfo['postcheck'];
		}
		$pwpost  = new PwPost($pwforum);
		$pwpost->fromGroup = true;
		$pwpost->postcheck();

		L::loadClass('topicpost', 'forum', false);
		require_once(R_P . 'apps/groups/lib/colonypost.class.php');
		$topicpost = new topicPost($pwpost);
		$topicpost->extraBehavior = new PwColonyPost($cyid);
		$topicpost->check();

		S::gp(array('atc_title','atc_content'), 'P', 0);
		S::gp(array('atc_convert','flashatt', 'atc_tags','atc_hideatt'), 'P');

		$postdata = new topicPostData($pwpost);
		$postdata->setTitle($atc_title);
		$postdata->setContent($atc_content);
		$postdata->setConvert($atc_convert);
		$postdata->setTags($atc_tags);
		$postdata->setHideatt($atc_hideatt);
		$postdata->setIfsign(1, 0);
		$postdata->conentCheck();

		L::loadClass('attupload', 'upload', false);
		if (PwUpload::getUploadNum() || $flashatt) {
			S::gp(array('savetoalbum', 'albumid'), 'P', 2);
			$postdata->att = new AttUpload($winduid, $flashatt, $savetoalbum, $albumid);
			$postdata->att->check();
		}
		$postdata->iscontinue = $iscontinue;
		
		$topicpost->execute($postdata);
		$tid = $topicpost->getNewId();

		$url = "{$basename}a=read&cyid=$cyid&tid=$tid";
		$msg = defined('AJAX') ?  "success\t".$url : '发布话题成功!';	
		refreshto($url, $msg);
	}
} elseif ($a == 'reply') {

	$a_key = 'thread';

	if (!$ifadmin && !$colony['ifFullMember']) {
		if (!$colony['ifopen']) {
			Showmsg('colony_cnmenber');
		}
		if (empty($colony['classid'])) {
			if ($colony['ifadmin'] == '-1') {
				Showmsg('colony_reply');
			} else {
				Showmsg('colony_reply2');
			}
		}
	}
	banUser();

	S::gp(array('tid'), null, 2);
	$tpc = $db->get_one("SELECT t.tid,t.fid,t.ifcheck,t.author,t.authorid,t.postdate,t.lastpost,t.ifmail,t.special,t.subject,t.type, t.ifshield,t.anonymous,t.ptable,t.replies,t.tpcstatus,t.locked,a.cyid FROM pw_threads t LEFT JOIN pw_argument a ON a.tid=t.tid WHERE t.tid=" . S::sqlEscape($tid) . ' AND a.tid IS NOT NULL');

	if (empty($tpc) || $tpc['cyid'] != $cyid || $tpc['fid'] != $colony['classid']) {
		Showmsg('data_error');
	}

	//锁定和关闭帖子不允许回复
	if (!$ifadmin && $tpc['locked']%3<>0) {
		Showmsg('reply_lockatc');
	}

	L::loadClass('post', 'forum', false);
	L::loadClass('forum', 'forum', false);
	require_once(R_P . 'require/bbscode.php');
	require_once(R_P . 'require/credit.php');
	
	$fid = $colony['classid'];
	$pwforum = new PwForum($colony['classid']);
	$pwpost  = new PwPost($pwforum);

	$_G['uploadtype'] && $db_uploadfiletype = $_G['uploadtype'];
	$db_uploadfiletype = !empty($db_uploadfiletype) ? (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype)) : array();

	if (empty($_POST['step'])) {
		
		$htmlpost = $attachHide = ($_G['allowhidden'] && ($pwforum->foruminfo['allowhide'] || empty($pwforum->foruminfo))) ? '' : 'disabled';
		$GLOBALS += getAttachConfig();
		$editor = getstatus($winddb['userstatus'], PW_USERSTATUS_EDITOR) ? 'wysiwyg' : 'textmode';
	
		S::gp(array('pid'), 'G');

		$atc_title = "RE:$tpc[subject]";
		$atc_title = substrs(str_replace('&nbsp;',' ',$atc_title), $db_titlemax - 2);

		if (!empty($pid)) {
			S::gp(array('article'));
			if ($pid == 'tpc') {
				$pw_tmsgs = GetTtable($tid);
				$atcarray = $tpc;
				$old_content = $db->get_value("SELECT content FROM $pw_tmsgs WHERE tid=" . S::sqlEscape($tid));
			} else {
				!is_numeric($pid) && Showmsg('illegal_tid');
				$pw_posts = GetPtable($tpc['ptable']);
				$atcarray = $db->get_one("SELECT author,postdate,content,anonymous FROM $pw_posts WHERE pid=" . S::sqlEscape($pid));
				$old_content = $atcarray['content'];
			}
			$old_author = $atcarray['anonymous'] ? $db_anonymousname : $atcarray['author'];
			$wtof_oldfile = get_date($atcarray['postdate']);
			$old_content = preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/is",getLangInfo('post','hide_post'),$old_content);
			$old_content = preg_replace("/\[post\](.+?)\[\/post\]/is",getLangInfo('post','post_post'),$old_content);
			$old_content = preg_replace("/\[sell=(.+?)\](.+?)\[\/sell\]/is",getLangInfo('post','sell_post'),$old_content);
			$old_content = preg_replace("/\[quote\](.*)\[\/quote\]/is","",$old_content);
			$bit_content = explode("\n",$old_content);

			if (count($bit_content) > 5) {
				$old_content = "$bit_content[0]\n$bit_content[1]\n$bit_content[2]\n$bit_content[3]\n$bit_content[4]\n.......";
			}
			if (strpos($old_content,$db_bbsurl) !== false) {
				$old_content = str_replace('p_w_picpath',$db_picpath,$old_content);
				$old_content = str_replace('p_w_upload',$db_attachname,$old_content);
			}
			$old_content = preg_replace("/\<(.+?)\>/is","",$old_content);
			$atc_content = "[quote]".($pid == 'tpc' ? getLangInfo('post','info_post_1') : getLangInfo('post','info_post_2'))."\n{$old_content} [url={$db_bbsurl}/apps.php?q=group&a=read&cyid=$cyid&tid=$tid#article_$pid][img]{$imgpath}/back.gif[/img][/url]\n[/quote]\n";
		}

		list($isheader,$isfooter,$tplname,$isleft) = array(false,true,"m_group",true);

	} else {

		PostCheck(1,$o_groups_p_gdcheck,$o_groups_p_qcheck);

		S::gp(array('atc_content', 'atc_title', 'flashatt'));

		$postdata = new replyPostData($pwpost);
		$postdata->setTitle($atc_title);
		$postdata->setContent($atc_content);
		$postdata->setConvert(1);
		$postdata->setIfsign(1, 0);
		$postdata->conentCheck();

		L::loadClass('replypost', 'forum', false);
		require_once(R_P . 'apps/groups/lib/colonypost.class.php');
		$replypost = new replyPost($pwpost);
		$replypost->extraBehavior = new PwColonyPost($cyid);

		$replypost->check();
		$replypost->setTpc($tpc);

		L::loadClass('attupload', 'upload', false);
		if (PwUpload::getUploadNum() || $flashatt) {
			S::gp(array('savetoalbum', 'albumid'), 'P', 2);
			$postdata->att = new AttUpload($winduid, $flashatt, $savetoalbum, $albumid);
			$postdata->att->check();
		}
		$replypost->execute($postdata);
		
		$url = "{$basename}a=read&cyid=$cyid&tid=$tid";
		$msg = defined('AJAX') ?  "success\t".$url : 'colony_postsuccess';	
		refreshto($url, $msg);
	}

} elseif ($a == 'top') {

	S::gp(array('tid'));
	!$ifadmin && Showmsg('undefined_action');

	$db->update("UPDATE pw_argument SET topped=1 WHERE tid=" . S::sqlEscape($tid));
	refreshto("{$basename}a=thread&cyid=$cyid", '置顶成功!');

} elseif ($a == 'deltop') {

	S::gp(array('tid'));
	!$ifadmin && Showmsg('undefined_action');

	$db->update("UPDATE pw_argument SET topped=0 WHERE tid=" . S::sqlEscape($tid));
	refreshto("{$basename}a=thread&cyid=$cyid", '取消置顶成功!');

} elseif ($a == 'write') {

	if ($groupRight['modeset']['write']['ifopen']) {
		$smileParser = L::loadClass('smileparser', 'smile'); /* @var $smileParser PW_SmileParser */
		$a_key = 'write';
		if ($colony['ifwriteopen'] || $ifadmin || $colony['ifcyer']) {
			$tmpActionUrl .= '&showtype=write';
			/* 右侧开始*/
			require_once(R_P.'require/showimg.php');
			list($faceurl) = showfacedesign($winddb['icon'],1,'m');
			$colonyNums = PwColony::calculateCredit($colony);
			$count2 = $colony['writenum'];
			if ($count2) {
				$page = (int)S::getGP('page');
				list($pages,$limit) = pwLimitPages($count2,$page,"{$basename}a=write&cyid=$cyid&");
				//$query = $db->query("SELECT w.*,m.username,m.icon,m.groupid FROM pw_cwritedata w LEFT JOIN pw_members m ON w.uid=m.uid WHERE w.cyid=".S::sqlEscape($cyid)." and w.uid IN (".S::sqlImplode($uidss,false).") ORDER BY w.replay_time DESC $limit");
				$query = $db->query("SELECT w.*,m.username,m.icon,m.groupid FROM pw_cwritedata w LEFT JOIN pw_members m ON w.uid=m.uid WHERE w.cyid=".S::sqlEscape($cyid)." ORDER BY w.replay_time DESC $limit");
				while ($rt = $db->fetch_array($query)) {
					if ($rt['groupid'] == 6 && $db_shield && $groupid != 3) {
						$rt['content'] = appShield('ban_write');
					}
					$rt['content'] = $smileParser->parse($rt['content']);
					list($rt['postdate']) = getLastDate($rt['postdate'],1);
					list($rt['icon']) = showfacedesign($rt['icon'],1,'m');
					$writedata[$rt['id']] = $rt;
					$typeid[] = $rt['id'];
				}
			}
		
			if ($typeid) {
				$query2 = $db->query("SELECT cm.*,m.icon FROM pw_comment cm LEFT JOIN pw_members m ON cm.uid=m.uid WHERE type='groupwrite' AND  typeid IN (".S::sqlImplode($typeid,false).") ORDER BY cm.id DESC");
				$tempTypeid = array();
				while ($rt2 = $db->fetch_array($query2)) {
					if(in_array($rt2['typeid'],$tempTypeid)) continue;
					$tempTypeid[] = $rt2['typeid'];
					$writedata[$rt2['typeid']]['replayuid']= $rt2['uid'];
					$writedata[$rt2['typeid']]['replayusername']= $rt2['username'];
					$writedata[$rt2['typeid']]['replaytitle']= $rt2['title'];
					list($writedata[$rt2['typeid']]['replaypoastdate'])= getLastDate($rt2['postdate'],1);
					list($writedata[$rt2['typeid']]['replayicon'])= showfacedesign($rt2['icon'],1,'m');
				}
			}
		}
		$visitor = $colony['visitor'] ? (array)unserialize($colony['visitor']) : array();
		$magdb = $newColony->getManager();
		foreach ($magdb as $key => $value) {
			if($value['username'] == $colony['admin']){
				unset($magdb[$key]);
				array_unshift($magdb,$value);
			}
		}
		$managerNum = count($magdb);
		$magdb = array_slice($magdb,0,9);
		$memdb = $newColony->getMembers(array('ifadmin'=>'3'), 10, 0, 'addtime');
		if (count($memdb)>9){
			$memdbNum = 10;
			array_pop($memdb);
		}
		$newvisit = $newColony->getVisitor(9);
		$newvisitnum = $newColony->getVisitorNum();
		$likegroup = $newColony->getLikeGroup();
	
		$uids = array_merge(array_keys($magdb), array_keys($memdb), array_keys($newvisit));
		if ($uids) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			foreach ($userService->getByUserIds($uids) as $rt) {
				list($rt['faceurl']) = showfacedesign($rt['icon'], 1, 'm');
				$udb[$rt['uid']] = $rt;
			}
		}
		list($isheader,$isfooter,$tplname,$isleft) = array(false, true, "m_group", true);
	}
} elseif ($a == 'writepost') {

	define('AJAX','1');
	require_once(R_P.'require/postfunc.php');

	if (!$groupRight['modeset']['write']['ifopen']) {
		Showmsg('gwrite_closed');
	}
	if (!$ifadmin && (!$colony['ifcyer'] || $colony['ifadmin'] == '-1')) {
		Showmsg('colony_cnmenber');
	}
	banUser();
	S::gp(array('id','source','encode','tosign'));
	$ruid = 0;
	$minLenText = 3;
	$maxLenText = 255;
	$text = S::getGP('text','P');
	if (!S::inArray(strtolower($encode),array('gbk','utf8','big5'))) {
		$encode = $charset;
	} elseif ($charset != $encode) {
		$text = pwConvert($text,$charset,$encode,true);
	}
	$textlen = strlen(html_entity_decode(stripslashes(trim($text))));
	$textlen < $minLenText && Showmsg('mode_o_write_textminlen');
	$textlen > $maxLenText && Showmsg('mode_o_write_textmaxlen');
	$text2 = trim($text);

	require_once(R_P.'require/bbscode.php');
	$wordsfb = L::loadClass('FilterUtil', 'filter');
	if (($banword = $wordsfb->comprise($text2)) !== false) {
		Showmsg('content_wordsfb');
	}
	$rt = $db->get_one("SELECT postdate,content FROM pw_cwritedata WHERE uid=".S::sqlEscape($winduid)." and cyid=".S::sqlEscape($cyid)." ORDER BY id DESC LIMIT 1");
	if ($rt['content'] == $text2) {
		Showmsg('mode_o_write_sametext');
	} elseif ($timestamp - $rt['postdate'] < 1) {
		Showmsg('mode_o_write_timelimit');
	}
	$source = "group";
	$text = S::escapeChar($text2);

	$db->update("INSERT INTO pw_cwritedata SET"
		. S::sqlSingle(array(
			'uid'		=> $winduid,
			'touid'		=> $ruid,
			'postdate'	=> $timestamp,
			'isshare'	=> 0,
			'source'	=> $source,
			'content'	=> $text,
			'cyid'		=> $cyid,
			'replay_time'	=> $timestamp,
		)));
	$f_id = $db->insert_id();

	//tnum加一
	//* $db->update("UPDATE pw_colonys SET writenum=writenum+'1' WHERE id=" . S::sqlEscape($cyid));
	pwQuery::update('pw_colonys', 'id=:id', array($cyid), null, array(PW_EXPR=>array('writenum=writenum+1')));
	$colony['writenum']++;
	updateGroupLevel($colony['id'], $colony);

	//if ($tosign && $winddb['honor'] != stripslashes($text)) {
	//	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	//	$userService->update($winduid, array('honor'=>$text));
	//}
	Cookie('iftongbu',0);
	if($colony['ifwriteopen']){
		$weiboService = L::loadClass('weibo','sns');/* @var $weiboService PW_Weibo */
		$weiboExtra = array(
					'cyid' => $cyid,
					'cname' => $colony['cname'],
				);
		$weiboService->send($winduid,$text,'group_write',$f_id,$weiboExtra);
	}
	Showmsg('mode_o_write_success');

} elseif ($a == 'writedel') {

	define('AJAX','1');
	S::gp(array('id', 'cyid', 'step'));

	if (empty($step)) {

		require_once PrintEot('m_ajax');
		ajax_footer();

	} elseif (2 == $step) {
		$totalId = (int)$db->get_value("SELECT COUNT(*) FROM pw_cwritedata WHERE id=" . S::sqlEscape($id));
		$totalCyid = (int)$db->get_value("SELECT COUNT(*) FROM pw_colonys WHERE id=" . S::sqlEscape($cyid));
		!($totalId && $totalCyid) && Showmsg('mode_o_write_del_error');

		$db->update("DELETE FROM pw_cwritedata WHERE id=" . S::sqlEscape($id));
		$db->update("DELETE FROM pw_comment WHERE typeid=" . S::sqlEscape($id) . "AND type='groupwrite'");
		//* $db->update("UPDATE pw_colonys SET writenum=writenum-'1' WHERE id=" . S::sqlEscape($cyid));
		pwQuery::update('pw_colonys', 'id=:id', array($cyid), null, array(PW_EXPR=>array('writenum=writenum-1')));
		
		$weiboService = L::loadClass('weibo','sns'); /* @var $weiboService PW_Weibo */
		$weibo = $weiboService->getWeibosByObjectIdsAndType($id,'group_write');
		if($weibo){
			$weiboService->deleteWeibos($weibo['mid']);
		}

		$colony['writenum']--;
		updateGroupLevel($colony['id'], $colony);
		Showmsg('mode_o_write_del');

	}

} elseif ($a == 'active') {
	require_once(A_P . 'groups/lib/active.class.php');
	$newActive = new PW_Active();
	if ($groupRight['modeset']['active']['ifopen']) {
		$a_key = 'active';
		//if ($colony['ifactiveopen'] || $ifadmin || $colony['ifcyer']) {
			$tmpActionUrl .= '&showtype=active';
			if (empty($job)) {
				S::gp(array('page'), '', 2);
				$page < 1 && $page = 1;
				$db_perpage = 10;
		
				require_once(A_P . 'groups/lib/active.class.php');
				$newActive = new PW_Active();
				$total = $newActive->getActiveCount($cyid);
				$pages = numofpage($total, $page, ceil($total/$db_perpage), "{$basename}a=$a&cyid=$cyid&");
				$activedb = $newActive->getActiveList($cyid, $db_perpage, ($page-1)*$db_perpage);
				list($newactivedb) = $newActive->searchList('', 3, 0, 'id', 'DESC');
				$hotactivedb = $newActive->getHotActive(3);

				list($isheader,$isfooter,$tplname,$isleft) = array(false, true, "m_group", true);
		
			} elseif ($job == 'actmember' || $job == 'membermanage') {
		
				S::gp(array('id', 'page'), '', 2);
				$page < 1 && $page = 1;
				$tmpActionUrl .= '&job=' . $job . '&id=' . $id;
				require_once(A_P . 'groups/lib/active.class.php');
				$newActive = new PW_Active();
				if (!($active = $newActive->getActiveById($id)) || $active['cid'] != $cyid) {
					Showmsg('data_error');
				}
				$db_perpage = 20;
				$pages = numofpage($active['members'], $page, ceil($active['members']/$db_perpage), "{$basename}a=$a&job=$job&cyid=$cyid&id=$id&");
				$actMembers = $newActive->getActMembers($id, $db_perpage, ($page - 1) * $db_perpage);
		
				list($isheader,$isfooter,$tplname,$isleft) = array(true,true,"m_group",true);
		
			} elseif ($job == 'post' || $job == 'edit') {
				
				!$ifadmin && Showmsg('undefined_action');
				$tmpActionUrl .= '&job=' . $job;
		
				$active = array();
				if ($job == 'edit') {
					S::gp(array('id'));
					$tmpActionUrl .= '&id=' . $id;
					require_once(A_P . 'groups/lib/active.class.php');
					$newActive = new PW_Active();
					if (!($active = $newActive->getActiveById($id)) || $active['cid'] != $cyid) {
						Showmsg('data_error');
					}
				}
				$db_uploadfiletype = !empty($db_uploadfiletype) ? (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype)) : array();
		
				if (empty($_POST['step'])) {
		
					if ($active) {
						$active = $newActive->convert($active);
						$atc_content = $active['content'];
						${'typeCheck_' . $active['type']} = ' checked';
						${'objecterCheck_' . $active['objecter']} = ' checked';
					} else {
						$typeCheck_6 = $objecterCheck_0 = ' checked';
					}
					$editor = getstatus($winddb['userstatus'], PW_USERSTATUS_EDITOR) ? 'wysiwyg' : 'textmode';

					$_G['uploadtype'] && $db_uploadfiletype = $_G['uploadtype'];
					$db_uploadfiletype = !empty($db_uploadfiletype) ? (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype)) : array();
					$attachAllow = pwJsonEncode($db_uploadfiletype);
					$imageAllow = pwJsonEncode(getAllowKeysFromArray($db_uploadfiletype, array('jpg','jpeg','gif','png','bmp')));
					$attachUrl = 'pweditor.php?action=attach';

					$attach = '';
					if ($job == 'edit' && $attachdb = $newActive->getAttById($id)) {
						foreach ($attachdb as $key => $value) {
							list($value['attachurl'],) = geturl($value['attachurl'], 'lf');
							$attach .= "'$key' : ['$value[name]', '$value[size]', '$value[attachurl]', '$value[type]', '$value[special]', '$value[needrvrc]', '$value[ctype]', '$value[descrip]'],";
						}
						$attach = rtrim($attach,',');
					}
					list($isheader,$isfooter,$tplname,$isleft) = array(false, true, "m_group", true);
		
				} else {
		
					S::gp(array('atc_title','introduction','atc_content'), 'P');
					S::gp(array('begintime', 'endtime', 'deadline', 'address', 'limitnum', 'price', 'owner'), 'P');
					S::gp(array('type', 'objecter'), 'P', 2);
					if(strlen($atc_title) > $db_titlemax) Showmsg('active_title_length');
					if(strlen($address) > $db_titlemax) Showmsg('active_address_length');
					if(strlen($introduction) > 130) Showmsg('active_introduction_length');
					if(strlen($atc_content) > 50000) Showmsg('active_atc_content_length');
					require_once(A_P . 'groups/lib/activepost.class.php');
		
					$activePost = new PwActivePost($cyid);
					if ($job == 'edit') {
						$activePost->setOwner($owner);
						$activePost->initData($active);
						if ($attachdb = $newActive->getAttById($id)) {
							S::gp(array('oldatt_desc'), 'P');
							$activePost->initAttachs($attachdb, $oldatt_desc);
						}
					}
					$activePost->setTitle($atc_title);
					$activePost->setContent($atc_content);
					$activePost->setType($type);
					$activePost->setActiveTime($begintime, $endtime, $deadline);
					$activePost->setAddress($address);
					$activePost->setLimitnum($limitnum);
					$activePost->setObjecter($objecter);
					$activePost->setPrice($price);
					$activePost->setIntroduction($introduction);
		
					if (($ret = $activePost->checkData()) !== true) {
						Showmsg($ret);
					}
					if ($job == 'edit') {
						$activePost->updateData($id);
						refreshto("{$basename}a=$a&cyid=$cyid&job=view&id=$id",'活动编辑成功!');
					} else {
		
						$activePost->setMembers(1);
						$id = $activePost->insertData();
		
						//activitynum加一
						//* $db->update("UPDATE pw_colonys SET activitynum=activitynum+1 WHERE id=" . S::sqlEscape($cyid));
						pwQuery::update('pw_colonys', 'id=:id', array($cyid), null, array(PW_EXPR=>array('activitynum=activitynum+1')));
						$colony['activitynum']++;
						updateGroupLevel($colony['id'], $colony);
						$weiboService = L::loadClass('weibo','sns');/* @var $weiboService PW_Weibo */
						$weiboContent = substrs(stripWindCode($introduction), 125);
						$weiboExtra = array(
								'cyid' => $cyid,
								'title'=>$atc_title,
								'cname' => $colony['cname'],
							);
						$weiboService->send($winduid,$weiboContent,'group_active',$id,$weiboExtra);
						refreshto("{$basename}a=$a&cyid=$cyid&job=view&id=$id",'活动发布成功!');
					}
		
				}
		
			} elseif ($job == 'join') {
		
				define('AJAX', 1);
				!$winduid && Showmsg('not_login');
		
				S::gp(array('id'));
				require_once(A_P . 'groups/lib/active.class.php');
				$newActive = new PW_Active();
				if (!($active = $newActive->getActiveById($id)) || $active['cid'] != $cyid) {
					Showmsg('data_error');
				}
				if (($return = $newActive->checkJoinStatus($id, $winduid)) !== true) {
					Showmsg($return);
				}
		
				if (empty($_POST['step'])) {
		
					require_once PrintEot('m_ajax');
					ajax_footer();
		
				} else {
		
					S::gp(array('realname','phone','mobile','address','anonymous'));
		
					$return = $newActive->appendMember($id, $winduid, array(
						'realname'	=> $realname,
						'phone'		=> $phone,
						'mobile'	=> $mobile,
						'address'	=> $address,
						'anonymous'	=> $anonymous
					));
					$return !== true && Showmsg($return);
		
					Showmsg("报名成功\treload");
				}
		
			} elseif ($job == 'view') {
		
				S::gp(array('id','page'), '', 2);
				$page < 1 && $page = 1;
				$tmpActionUrl .= '&job=view&id=' . $id;
		
				require_once(A_P . 'groups/lib/active.class.php');
				$newActive = new PW_Active();
				if (!($active = $newActive->getActiveInfoById($id)) || $active['cid'] != $cyid) {
					Showmsg('data_error');
				}
		
				//检查是否是群组的成员
				$isJoin = $newActive->isJoin($id,$winduid);
				require_once(R_P . 'require/showimg.php');
				list($active['icon']) = showfacedesign($active['icon'], 1, 'm');
				$active = $newActive->convert($active);
				$membersLimit = 20;
				$actMembers = $newActive->getActMembers($id, $membersLimit);
		
				$webPageTitle = $colonySeo->getPageTitle($active['title'],$colony['cname']);
				$metaDescription = $colonySeo->getPageMetadescrip($active['introduction']);
				$metaKeywords = $colonySeo->getPageMetakeyword($active['title'],$colony['cname']);
		
				$active['content'] = str_replace("\n", '<br />', $active['content']);
				require_once(R_P . 'require/bbscode.php');
				$active['content'] = convert($active['content'], $db_windpost);
				if ($attachs = $newActive->getAttById($id)) {
					extract(L::style());
					$attachShow = new attachShow(($isGM || $pwSystem['delattach']), '', 0, 'active');
					$attachShow->setData($attachs);
					$active += $attachShow->parseAttachs('tpc', $active['content'], $winduid == $active['uid']);
				}
				$newActive->updateHits($id);
				list($newactivedb) = $newActive->searchList(array('cid' => $cyid), 3, 0, 'id', 'DESC');
				$hotactivedb = $newActive->getHotActive(3);
				$relateactivedb = $newActive->getRelateActive($id, 3);
		
				list($commentdb, $subcommentdb, $pages, $count) = getCommentDbByTypeid('active', $id, $page, "{$basename}a=$a&job=$job&cyid=$cyid&id=$id&");
				$comment_type = 'active';
				$comment_typeid = $id;
		
				list($isheader,$isfooter,$tplname,$isleft) = array(false, true, "m_group", true);
		
			} elseif ($job == 'quit') {
		
				define('AJAX', 1);
		
				S::gp(array('id'));
				require_once(A_P . 'groups/lib/active.class.php');
				$newActive = new PW_Active();
				if (!($active = $newActive->getActiveById($id)) || $active['cid'] != $cyid) {
					Showmsg('data_error');
				}
				if (empty($_POST['step'])) {
		
					require_once PrintEot('m_ajax');
					ajax_footer();
		
				} else {
		
					$newActive->quitActive($id, $winduid);
					Showmsg("退出成功!\treload");
				}
			} elseif ($job == 'delmember') {
		
				define('AJAX', 1);
				S::gp(array('id', 'uid'), 'GP', '2');
				!$ifadmin && Showmsg('undefined_action');
		
				require_once(A_P . 'groups/lib/active.class.php');
				$newActive = new PW_Active();
				if (!($active = $newActive->getActiveById($id)) || $active['cid'] != $cyid) {
					Showmsg('data_error');
				}
		
				if (empty($_POST['step'])) {
		
					require_once PrintEot('m_ajax');
					ajax_footer();
		
				} else {
		
					$newActive->quitActive($id, $uid);
					Showmsg("删除成功!\treload");
				}
			} elseif ($job == 'exportmember') {
		
				S::gp(array('id'), 'GP', '2');
				!$ifadmin && Showmsg('undefined_action');
		
				require_once(A_P . 'groups/lib/active.class.php');
				$newActive = new PW_Active();
				if (!($active = $newActive->getActiveById($id)) || $active['cid'] != $cyid) {
					Showmsg('data_error');
				}
		
				header("Content-type:application/vnd.ms-excel");
				header("Content-Disposition:attachment;filename=$active[title].xls");
				header("Pragma: no-cache");
				header("Expires: 0");
		
				$actMembers = $newActive->getActMembers($id);
		
				$titledb = array(
					getLangInfo('other', 'pc_id') . "\t",
					getLangInfo('other', 'pc_username') . "\t",
					getLangInfo('other', 'pc_name') . "\t",
					getLangInfo('other', 'pc_mobile') . "\t",
					getLangInfo('other', 'pc_phone') . "\t",
					getLangInfo('other', 'pc_address') . "\t\n"
				);
		
				foreach ($titledb as $key => $value) {
					echo $value;
				}
				$i = 0;
				foreach ($actMembers as $val) {
					$i++;
					echo "$i\t";
					echo "$val[username]\t";
					echo "$val[realname]\t";
					echo "$val[phone]\t";
					echo "$val[mobile]\t";
					echo "$val[address]\t\n";
				}
				exit;
			} elseif ($job == 'sendmsg') {
		
				define('AJAX', 1);
				S::gp(array('id'), 'GP', '2');
				!$ifadmin && Showmsg('undefined_action');
		
				require_once(A_P . 'groups/lib/active.class.php');
				$newActive = new PW_Active();
				if (!($active = $newActive->getActiveById($id)) || $active['cid'] != $cyid) {
					Showmsg('data_error');
				}
		
				if (empty($_POST['step'])) {
		
					require_once PrintEot('m_ajax');
					ajax_footer();
		
				} else {
		
					S::gp(array('subject','atc_content'));
		
					$msg_title = trim($subject);
					$atc_content = trim($atc_content);
					if (empty($atc_content) || empty($msg_title)) {
						Showmsg('msg_empty');
					} elseif (strlen($msg_title) > 75 || strlen($atc_content) > 1500) {
						Showmsg('msg_subject_limit');
					}
					$wordsfb = L::loadClass('FilterUtil', 'filter');
					if (($banword = $wordsfb->comprise($msg_title)) !== false) {
						Showmsg('title_wordsfb');
					}
					if (($banword = $wordsfb->comprise($atc_content, false)) !== false) {
						Showmsg('content_wordsfb');
					}
					$atc_content .= "<div class=\"fr\" style=\"margin-top:20px\">------来自群活动<a href=\"apps.php?q=group&cyid=$cyid&a=active&job=view&id=$id\">“{$active[title]}”</a>的消息！</div>";
		
					$userNames = array();
					$actMembers = $newActive->getActMembers($id);
					foreach ($actMembers as $val) {
						$userNames[] = $val['username'];
					}
					M::sendNotice(
						$userNames,
						array(
							'create_uid'	=> $winduid,
							'create_username'	=> $windid,
							'title' => $msg_title,
							'content' => $atc_content
						),
						'notice_website',
						null,
						$winduid
					);
					Showmsg('发送成功!');
				}
			} elseif ($job == 'del') {
		
				if (empty($_POST)) {
					define('AJAX', 1);
				}
				S::gp(array('id','frombbs'));
				require_once(A_P . 'groups/lib/active.class.php');
				$newActive = new PW_Active();
				if (!($active = $newActive->getActiveById($id)) || $active['cid'] != $cyid) {
					Showmsg('data_error');
				}
				if (empty($_POST['step'])) {
		
					require_once PrintEot('m_ajax');
					ajax_footer();
		
				} else {
		
					if ($winduid != $active['uid'] && !$ifadmin) {
						Showmsg('您不是活动的创建者，无权取消！');
					}
					$newActive->delActive($id);
		
					$weiboService = L::loadClass('weibo','sns'); /* @var $weiboService PW_Weibo */
					$weibo = $weiboService->getWeibosByObjectIdsAndType($id,'group_active');
					if($weibo){
						$weiboService->deleteWeibos($weibo['mid']);
					}
		
					//activitynum减一
					//$db->update("UPDATE pw_colonys SET activitynum=activitynum-1 WHERE id=" . S::sqlEscape($cyid));
					$colony['activitynum']--;
					updateGroupLevel($colony['id'], $colony);
					if ($frombbs == 1) {
						refreshto("thread.php?cyid=$cyid&showtype=active", '取消成功!');
					} else {
						refreshto("{$basename}a=$a&cyid=$cyid", '取消成功!');
					}
				}
			}
		//}
	}
} elseif ($a == 'member') {
	S::gp(array('operateStep'));
	$a_key = 'member';
	if ($colony['ifmemberopen'] || $ifadmin || $colony['ifcyer']) { 
	$tmpActionUrl .= '&showtype=member';

	if (empty($operateStep)) {

		require_once(R_P.'require/showimg.php');
		S::gp(array('group', 'orderby'));
		$group && $tmpActionUrl .= '&group='.$group;
		$lang_no_member = array('2'=>'没有普通成员','3'=>'没有未验证会员','4'=>'没有最近访客');
		$order_lastpost = $order_lastvisit = '';

		if ($group && $group == 4) {
			$visitor = $newColony->getVisitor();
			$numofpage = ceil($total/$db_perpage);
			$numofpage = ($db_maxpage && $numofpage > $db_maxpage) ? $db_maxpage : $numofpage;
			$page < 1 ? $page = 1 : ($page > $numofpage ? $page = $numofpage : null);
			$pageurl = "{$basename}a=member&cyid=$cyid&group=4&";
			$pages = numofpage($total,$page,$numofpage,$pageurl,$db_maxpage);
			$visitor = array_slice($visitor,($page-1) * $db_perpage, $db_perpage, true);
			$total = count($visitor);
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
			$sqlsel = '';
			if ($group == 1) {
				$sqlsel = " AND cm.ifadmin='1'";
			} elseif ($group == 2) {
				$sqlsel = " AND cm.ifadmin='0'";
			} elseif ($group == 3) {
				$sqlsel = " AND cm.ifadmin='-1'";
			}
			$total = $db->get_value("SELECT COUNT(*) AS sum FROM pw_cmembers cm WHERE cm.colonyid=" . S::sqlEscape($cyid) . $sqlsel);
			if ($total) {
				if (in_array($orderby, array('lastpost', 'lastvisit'))) {
					$order	= $orderby;
					$urladd	= $orderby ? "orderby=$orderby&" : '';
					${'order_' . $orderby} = ' class="current"';
				} else {
					$order	= 'ifadmin';
					$urladd	= '';
				}
				list($pages, $limit) = pwLimitPages($total,$page,"{$basename}a=member&cyid=$cyid&group=$group&$urladd");
				$memdb = array();
				$query = $db->query("SELECT cm.*,m.icon,m.honor,md.thisvisit FROM pw_cmembers cm LEFT JOIN pw_members m ON cm.uid=m.uid LEFT JOIN pw_memberdata md ON m.uid=md.uid WHERE cm.colonyid=" . S::sqlEscape($cyid) . $sqlsel . " ORDER BY cm.{$order} DESC $limit");
				while ($rt = $db->fetch_array($query)) {
					list($rt['icon']) = showfacedesign($rt['icon'],1,'m');
					$memdb[$rt['username']] = $rt;
				}
				$colonyOwner = $memdb[$colony['admin']];
				unset($memdb[$colony['admin']]);
				$colonyOwner && array_unshift($memdb,$colonyOwner);
			}
		}
		$urladd = $group ? '&group=' . $group : '';

		list($isheader,$isfooter,$tplname,$isleft) = array(false, true, "m_group", true);

	} else {

		!$ifadmin && Showmsg('undefined_action');
		S::gp(array('selid'), 'P', 2);

		if (!$selid || !is_array($selid)) {
			Showmsg('id_error');
		}
		$toUsers = array();

		switch ($operateStep) {
			case 'addadmin':
				($colony['admin'] != $windid && $groupid != 3) && Showmsg('colony_manager');
				$query = $db->query("SELECT ifadmin,username FROM pw_cmembers WHERE colonyid=" . S::sqlEscape($cyid) . ' AND uid IN(' . S::sqlImplode($selid) . ") AND ifadmin!='1'");
				$newMemberCount = 0;
				while ($rt = $db->fetch_array($query)) {
					$rt['ifadmin'] == -1 && $newMemberCount++;
					$toUsers[] = $rt['username'];
				}
				$newColony->updateInfoCount(array('members' => $newMemberCount));
				//* $db->update("UPDATE pw_cmembers SET ifadmin='1' WHERE colonyid=" . S::sqlEscape($cyid) . ' AND uid IN(' . S::sqlImplode($selid) . ") AND ifadmin!='1'");
				pwQuery::update('pw_cmembers', 'colonyid=:colonyid AND uid IN(:uid) AND ifadmin!=:ifadmin', array($cyid, $selid, 1), array('ifadmin'=>1));
				break;
			case 'deladmin':
				($colony['admin'] != $windid && $groupid != 3) && Showmsg('colony_manager');
				$query = $db->query("SELECT username FROM pw_cmembers WHERE colonyid=" . S::sqlEscape($cyid) . ' AND uid IN(' . S::sqlImplode($selid) . ") AND ifadmin='1'");
				while ($rt = $db->fetch_array($query)) {
					$colony['admin'] == $rt['username'] && Showmsg('colony_delladminfail');
					$toUsers[] = $rt['username'];
				}
				//* $db->update("UPDATE pw_cmembers SET ifadmin='0' WHERE colonyid=" . S::sqlEscape($cyid) . ' AND uid IN(' . S::sqlImplode($selid) . ") AND ifadmin='1'");
				pwQuery::update('pw_cmembers', 'colonyid=:colonyid AND uid IN(:uid) AND ifadmin=:ifadmin', array($cyid, $selid, 1), array('ifadmin'=>0));
				break;
			case 'check':
				$toUsers = $newColony->checkMembers($selid);
				break;
			case 'del':
				$trueMemberCount = 0;
				$query = $db->query("SELECT username,ifadmin FROM pw_cmembers WHERE colonyid=" . S::sqlEscape($cyid) . ' AND uid IN(' . S::sqlImplode($selid) . ")");
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
				//* $db->update("DELETE FROM pw_cmembers WHERE colonyid=" . S::sqlEscape($cyid) . " AND uid IN(" . S::sqlImplode($selid) . ")");
				pwQuery::delete('pw_cmembers', 'colonyid=:colonyid AND uid IN (:uid)', array($cyid, $selid));

				//$count = $db->affected_rows();
				//$db->update("UPDATE pw_colonys SET members=members-" . S::sqlEscape($count,false) . " WHERE id=" . S::sqlEscape($cyid));
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
						'cname'	=> S::escapeChar($colony['cname']),
					)),
					'content' => getLangInfo('writemsg','o_' . $operateStep . '_content',array(
						'cname'	=> S::escapeChar($colony['cname']),
						'curl'	=> "$db_bbsurl/{$basename}cyid=$cyid"
					)),
				)
			);
		}
		refreshto("{$basename}a=$a&cyid=$cyid",'operate_success');
	}
	}
} elseif ($a == 'invite') {

	empty($winduid) && Showmsg('not_login');
	$a_key = 'member';
	S::gp(array('id','type'));

	$customdes = getLangInfo('other','invite_custom_des');
	$tmpActionUrl = 'thread.php?cyid=' . $cyid . '&showtype=member&a=invite';
	if ($type == 'groupactive') {
		$invite_url = $db_bbsurl.'/u.php?a=invite&type=groupactive&id=' . $id . '&uid=' . $winduid . '&hash=' . appkey($winduid, $type);
		$activeArray = $db->get_one("SELECT * FROM pw_active WHERE id=".S::sqlEscape($id));
		$objectName = $activeArray['title'];
		$colonyName = $colony['cname'];
		$objectDescrip = substrs(stripWindCode($activeArray['content']),30);
		$activeId = $activeArray['id'];
		$emailContent = getLangInfo('email','email_groupactive_invite_content');
	} else {
		$id = $cyid;
		$type = 'group';
		$invite_url = $db_bbsurl.'/u.php?a=invite&type=group&id=' . $cyid . '&uid=' . $winduid . '&hash=' . appkey($winduid, $type);
		$objectName = $colony['cname'];
		$objectDescrip = substrs(stripWindCode($colony['descrip']),30);
		$emailContent = getLangInfo('email','email_group_invite_content');
	}

	if (empty($_POST['step'])) {

		S::gp("id",null,2);
		//* @include_once(D_P.'data/bbscache/o_config.php');
		extract(pwCache::getData(D_P.'data/bbscache/o_config.php', false));
		$friend = getFriends($winduid) ? getFriends($winduid) : array();
		foreach ($friend as $key => $value) {
			$frienddb[$value['ftid']][] = $value;
		}
		$query = $db->query("SELECT * FROM pw_friendtype WHERE uid=".S::sqlEscape($winduid)." ORDER BY ftid");
		$friendtype = array();
		while ($rt = $db->fetch_array($query)) {
			$friendtype[$rt['ftid']] = $rt;
		}
		$no_group_name = getLangInfo('other','no_group_name');
		$friendtype[0] = array('ftid' => 0,'uid' => $winduid,'name' => $no_group_name);
		list($isheader,$isfooter,$tplname,$isleft) = array(false, true, "m_group", false);

	} elseif($_POST['step'] == 1) { // 发送email邀请

		S::gp(array('emails','customdes'),'P');
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

		S::gp(array('sendtoname','touid'),'P');

		$uids = array();
		if ($sendtoname) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$uid = $userService->getUserIdByUserName($sendtoname);
			if (!$uid) {
				$errorname = $sendtoname;
				Showmsg('user_not_exists');
			}
			$uids[] = $uid;
		}
		if (is_array($touid)) {
			foreach ($touid as $key => $value) {
				if (is_numeric($value)) {
					$uids[] = $value;
				}
			}
		}
		!$uids && Showmsg('msg_empty');
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$toUsers = $userService->getUserNamesByUserIds($uids);

		$inColonyUsers = array();
		$query = $db->query("SELECT username FROM pw_cmembers WHERE uid IN(".S::sqlImplode($uids).") AND colonyid=".S::sqlEscape($cyid));
		while ($rt = $db->fetch_array($query)) {
			$inColonyUsers[] = $rt['username'];
		}
		$toUsers = array_diff($toUsers,$inColonyUsers);

		if ($type == 'group') {
			M::sendRequest(
				$winduid,
				$toUsers,
				array(
					'create_uid' => $winduid,
					'create_username' => $windid,
					'title' => getLangInfo('writemsg', 'email_'.$type.'_invite_subject'),
					'content' => getLangInfo('writemsg', 'message_'.$type.'_invite_content'),
					'extra' => serialize(array('cyid' => $id,'check'=>0))
				),
				'request_group',
				'request_group'
			);
		} elseif ($type == 'groupactive') {
			M::sendMessage(
				$winduid,
				$toUsers,
				array(
					'create_uid'	=> $winduid,
					'create_username'	=> $windid,
					'title' => getLangInfo('writemsg', 'message_'.$type.'_invite_subject'),
					'content' => getLangInfo('writemsg', 'message_'.$type.'_invite_content'),
				)
			);
		}
		if ($inColonyUsers) {
			$inColonyUsers = implode(',',$inColonyUsers);
			if (empty($toUsers)) {
				Showmsg('colony_invite_no_message');
			} else {
				Showmsg('colony_invite_message');
			}
		} else {
			Showmsg('operate_success');
		}

	}

} elseif ($a == 'uintro') {

	$a_key = 'member';
	define('AJAX','1');
	define('F_M',true);

	if (empty($_POST['step'])) {

		S::gp(array('uid'), null, 2);
		S::gp(array('job'));

		empty($uid) && $uid = $winduid;

		$uinfo = $db->get_one("SELECT uid,username,realname,gender,tel,email,address,introduce FROM pw_cmembers WHERE colonyid=" . S::sqlEscape($cyid) . ' AND uid=' . S::sqlEscape($uid));
		!$uinfo && Showmsg('data_error');

		$select_0 = $select_1 = $select_2 = '';
		${'select_' . $uinfo['gender']} = ' selected';

		require_once PrintEot('m_ajax');
		footer();

	} else {

		require_once(R_P.'require/postfunc.php');
		S::gp(array('realname','tel','email','address','introduce'),'P');
		S::gp(array('gender'), null, 2);

		//各字段长度检测
		strlen($realname) > 20 && Showmsg('colony_realname_too_long');
		strlen($tel) > 15 && Showmsg('colony_tel_too_long');
		strlen($email) > 50 && Showmsg('colony_email_too_long');
		strlen($address) > 255 && Showmsg('colony_address_too_long');
		strlen($introduce) > 255 && Showmsg('colony_introduce_too_long');

		require_once(R_P.'require/bbscode.php');
		$wordsfb = L::loadClass('FilterUtil', 'filter');
		foreach (array($realname, $address, $introduce) as $key => $value) {
			if (($banword = $wordsfb->comprise($value)) !== false) {
				Showmsg('content_wordsfb');
			}
		}

		if (!empty($email) && !ereg("^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$",$email)) {
			Showmsg('illegal_email');
		}
		/**
		$db->update("UPDATE pw_cmembers SET " . S::sqlSingle(array(
				'realname'	=> $realname,
				'gender'	=> $gender,
				'tel'		=> $tel,
				'email'		=> $email,
				'address'	=> $address,
				'introduce'	=> $introduce,
			)) . " WHERE colonyid=" . S::sqlEscape($cyid) . " AND uid=".S::sqlEscape($winduid)
		);
		**/
		pwQuery::update('pw_cmembers','colonyid=:colonyid AND uid=:uid', array($cyid, $winduid), array(
				'realname'	=> $realname,
				'gender'	=> $gender,
				'tel'		=> $tel,
				'email'		=> $email,
				'address'	=> $address,
				'introduce'	=> $introduce,
			)
		);		
		
		Showmsg('编辑成功!');
	}

} elseif ($a == 'set') {

	!$ifadmin && Showmsg('undefined_action');
	$isGM && $SYSTEM['colonyright'] = 1;
	$a_key = 'set';

	S::gp('t');

	//获取功能权限
	$ifsetable = $newColony->getSetAble($t);
	!$ifsetable && Showmsg('colony_setunable');

	$tmpActionUrl .= '&showtype=set' . (($t && $t != 'style') ? ('&t=' . $t) : '');

	if (empty($t)) {

		$jsStyle = pwJsonEncode($o_styledb);
		$jsStyleRelation = pwJsonEncode($o_style_relation);

		if (empty($_POST['step'])) {

			$titledetail = explode("~",$colony['titlefont']);
			$titlecolor = $titledetail[0];
			if ($titlecolor && !preg_match('/\#[0-9A-F]{6}/is',$titlecolor)) {
				$titlecolor = '';
			}
			$stylename[1] = $titledetail[1] == '1' ? 'b one' : 'b';
			$stylename[2] = $titledetail[2] == '1' ? 'u one' : 'u';
			$stylename[3] = $titledetail[3] == '1' ? 'one' : '';

			$filetype = (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype));
			$default_type = array('gif','jpg','jpeg','bmp','png');
			foreach ($default_type as $value) {
				if (isset($filetype[$value])) $cnimg_1[$value] = $o_imgsize ? $o_imgsize : $filetype[$value];
				$cnimg_2[$value] = 2048;
			}
			$set_banner = $colony['banner'] ? $colony['banner'] : $imgpath . '/g/' . $colony['colonystyle'] . '/preview.jpg';
			list($isheader, $isfooter, $tplname, $isleft) = array(false, true, "m_group", true);

		} else {

			S::gp(array('cname','p_type','firstgradestyle','secondgradestyle','annouce','descrip','q_1','q_2'),'P');
			$descrip = str_replace('&#61;' , '=', $descrip);
			$annouce = str_replace('&#61;' , '=', $annouce);

			strlen($descrip) > 255 && Showmsg('colony_descrip');
			!$cname && Showmsg('colony_emptyname');
			strlen($cname) > 20 && Showmsg('colony_cnamelimit');
			//(!$descrip || strlen($descrip) > 255) && Showmsg('colony_descriplimit');
			if ($colony['cname'] != stripcslashes($cname) && $db->get_value("SELECT id FROM pw_colonys WHERE cname=" . S::sqlEscape($cname))) {
				Showmsg('colony_samename');
			}

			$styleid = 0;
			if ($o_styledb) {
				if (!isset($o_style_relation[$firstgradestyle])) {
					Showmsg('请选择分类!');
				}
				if (empty($o_style_relation[$firstgradestyle])) {
					$styleid = $firstgradestyle;
				} else {
					!in_array($secondgradestyle, $o_style_relation[$firstgradestyle]) && Showmsg('请选择二级分类!');
					$styleid = $secondgradestyle;
				}
			}

			strlen($annouce) > 50000 && Showmsg('colony_annoucelimit');
			$annouce = explode("\n",$annouce,5);
			end($annouce);
			$annouce[key($annouce)] = str_replace(array("\r","\n"),'',current($annouce));
			$annouce = implode("\r\n",$annouce);

			S::gp(array('title1','title2','title3','title4'));
			$titlefont = S::escapeChar("$title1~$title2~$title3~$title4~$title5~$title6~");

			$pwSQL = array(
				'cname'		=> $cname,
				'styleid'   => $styleid,
				'descrip'	=> $descrip,
				'annouce'	=> $annouce,
				'titlefont' => $titlefont
			);

			require_once(A_P . 'groups/lib/imgupload.class.php');
			if (empty($q_1)) {
				$img = new CnimgUpload($cyid);
				PwUpload::upload($img);
				pwFtpClose($ftp);
				if ($cnimg = $img->getImgUrl()) {
					$pwSQL['cnimg'] = substr(strrchr($cnimg,'/'),1);
				}
			} else {
				$pwSQL['cnimg'] = '';
			}
			if (empty($q_2)) {
				$banner = new BannerUpload($cyid);
				PwUpload::upload($banner);
				pwFtpClose($ftp);
				if ($cnbanner = $banner->getImgUrl()) {
					$pwSQL['banner'] = substr(strrchr($cnbanner,'/'),1);
				}
			} else {
				$pwSQL['banner'] = '';
			}

			require_once(R_P.'require/bbscode.php');
			$wordsfb = L::loadClass('FilterUtil', 'filter');
			foreach (array($cname, $annouce, $descrip) as $key => $value) {
				if (($banword = $wordsfb->comprise($value)) !== false) {
					Showmsg('content_wordsfb');
				}
			}

			//* $db->update("UPDATE pw_colonys SET " . S::sqlSingle($pwSQL) . ' WHERE id=' . S::sqlEscape($cyid));
			pwQuery::update('pw_colonys', 'id=:id', array($cyid), $pwSQL);

			refreshto("{$basename}cyid=$cyid&a=set&cyid=$cyid",'colony_setsuccess');
		}

	} elseif ($t == 'annouce'){

		S::gp(array('atc_content'),'P');
		$annouce = $atc_content;
		strlen($annouce) > 15000 && Showmsg('colony_annoucelimit');
		$annouce = explode("\n",$annouce,5);
		end($annouce);
		$annouce[key($annouce)] = str_replace(array("\r","\n"),'',current($annouce));
		$annouce = implode("\r\n",$annouce);
		$pwSQL = array(
				'annouce'	=> $annouce
		);
		require_once(R_P.'require/bbscode.php');
		$wordsfb = L::loadClass('FilterUtil', 'filter');
		foreach (array($annouce) as $key => $value) {
			if (($banword = $wordsfb->comprise($value)) !== false) {
					Showmsg('content_wordsfb');
			}
		}
		//* $db->update("UPDATE pw_colonys SET " . S::sqlSingle($pwSQL) . ' WHERE id=' . S::sqlEscape($cyid));
		pwQuery::update('pw_colonys', 'id=:id', array($cyid), $pwSQL);
		refreshto("{$basename}cyid=$cyid",'colony_setsuccess');

	} elseif ($t == 'style') {

		if (empty($_POST['step'])) {

			$names = array();
			$query = $db->query("SELECT * FROM pw_cnskin");
			while ($rt = $db->fetch_array($query)) {
				$names[$rt['dir']] = $rt['name'];
			}
			list($isheader,$isfooter,$tplname,$isleft) = array(false, true, "m_group", true);

		} else {

			S::gp(array('colonystyle'), 'P');
			$pwSQL = array(
				'colonystyle' => $colonystyle
			);

			//* $db->update("UPDATE pw_colonys SET " . S::sqlSingle($pwSQL) . ' WHERE id=' . S::sqlEscape($cyid));
			pwQuery::update('pw_colonys', 'id=:id', array($cyid), $pwSQL);

			refreshto("{$basename}cyid=$cyid&a=set&t=$t",'colony_setsuccess');
		}

	} elseif ($t == 'privacy') {

		if (empty($_POST['step'])) {

			$ifcheck_0 = $ifcheck_1 = $ifcheck_2 = $ifopen_Y = $ifopen_N = $albumopen_Y = $albumopen_N = $memopen_Y = $memopen_N = $ifinforum_Y = $ifinforum_N='';
			${'ifcheck_'.$colony['ifcheck']} = 'selected';
			${'ifopen_'.($colony['ifopen'] ? 'Y' : 'N')} = 'checked';
			${'ifinforum_'.($colony['ifinforum'] ? 'Y' : 'N')} = 'checked';
			${'ifwriteopen_'.($colony['ifwriteopen'] ? 'Y' : 'N')} = 'checked';
			${'ifmemberopen_'.($colony['ifmemberopen'] ? 'Y' : 'N')} = 'checked';
			${'ifannouceopen_'.($colony['ifannouceopen'] ? 'Y' : 'N')} = 'checked';

			list($isheader,$isfooter,$tplname,$isleft) = array(false,true,"m_group",true);

		} else {

			S::gp(array('ifcheck','ifopen','ifinforum','ifwriteopen','ifmemberopen','ifannouceopen'), 'P', 2);
			$pwSQL = array(
				'ifcheck'	=> $ifcheck,
				'ifopen'	=> $ifopen,
				'ifwriteopen'=>$ifwriteopen,
				'ifmemberopen'=>$ifmemberopen,
				'ifannouceopen'=>$ifannouceopen
			);

			//* $db->update("UPDATE pw_colonys SET " . S::sqlSingle($pwSQL) . ' WHERE id=' . S::sqlEscape($cyid));
			pwQuery::update('pw_colonys', 'id=:id', array($cyid), $pwSQL);

			refreshto("{$basename}cyid=$cyid&a=set&t=$t",'colony_setsuccess');
		}

	} elseif ($t == 'merge') {

		if (!(((1 == $SYSTEM['colonyright'] || $colony['admin'] == $windid) && $groupRight['allowmerge']) || $groupid == '3')) {
			Showmsg('您没有权限进行合并操作!');
		}

		require_once(A_P . 'groups/lib/colonys.class.php');
		$colonyServer = new PW_Colony();

		if (empty($_POST['step'])) {

			$groupList = $colonyServer->getColonyList(array('admin' =>$colony['admin']));
			if (count($groupList) == 1) {
				Showmsg('没有可以合并的群组!');
			}

			list($isheader,$isfooter,$tplname,$isleft) = array(false,true,"m_group",true);

		} else {

			S::gp(array('tocid'));
			S::gp(array('password'));

			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userinfo = $userService->get($winduid);

			if (md5($password) != $userinfo['password']) {
				Showmsg('您输入的密码不正确!');
			}
			if (!($toColony = $colonyServer->getColonyById($tocid)) || $toColony['admin'] != $colony['admin']) {
				Showmsg('undefined_action');
			}
			if (PwColony::calculateCredit($colony) > PwColony::calculateCredit($toColony)) {
				Showmsg('只允许群积分低的群组并入群积分高的群组!');
			}
			$colonyServer->mergeColony($tocid, $cyid);

			refreshto("{$basename}cyid=$tocid", 'operate_success');
		}
	} elseif ($t == 'attorn') {

		if (!(((1 == $SYSTEM['colonyright'] || $colony['admin'] == $windid) && $groupRight['allowmerge']) || $groupid == '3')) {
			Showmsg('您没有权限进行转让操作!');
		}
		if (empty($_POST['step'])) {

			$groupManager = array();
			$query = $db->query("SELECT c.uid,m.username,m.groupid,m.memberid,m.icon FROM pw_cmembers c LEFT JOIN pw_members m ON c.uid=m.uid WHERE c.ifadmin='1' AND c.colonyid=" . S::sqlEscape($cyid));

			while ($rt = $db->fetch_array($query)) {
				$rt['groupid'] == '-1' && $rt['groupid'] = $rt['memberid'];
				if ($rt['username'] == $colony['admin'] || ($o_groups && strpos($o_groups, ',' . $rt['groupid'] . ',') === false)) {
					continue;
				}
				list($rt['faceurl']) = showfacedesign($rt['icon'], 1, 'm');
				$groupManager[] = $rt;
			}

			list($isheader,$isfooter,$tplname,$isleft) = array(false,true,"m_group",true);

		} else {

			S::gp(array('password'));
			S::gp(array('newmanager'), 'GP', 2);

			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userinfo = $userService->get($winduid);
			if (md5($password) != $userinfo['password']) {
				Showmsg('您输入的密码不正确!');
			}

			$userdb = $db->get_one("SELECT m.username,m.groupid,m.memberid FROM pw_cmembers c LEFT JOIN pw_members m ON c.uid=m.uid WHERE c.ifadmin='1' AND c.colonyid=" . S::sqlEscape($cyid) . ' AND c.uid=' . S::sqlEscape($newmanager));

			if (empty($userdb)) {
				Showmsg('请选择要转让的用户!');
			}
			$userdb['groupid'] == '-1' && $userdb['groupid'] = $userdb['memberid'];
			if ($o_groups && strpos($o_groups, ',' . $userdb['groupid'] . ',') === false) {
				Showmsg('您选择的用户没有接受的权限!');
			}

			//* $db->update("UPDATE pw_colonys SET admin=" . S::sqlEscape($userdb['username']) . ' WHERE id=' . S::sqlEscape($cyid));
			pwQuery::update('pw_colonys', 'id=:id', array($cyid), array('admin'=>$userdb['username']));

			M::sendNotice(
				array($userdb['username']),
				array(
					'title' => getLangInfo('writemsg','group_attorn_title'),
					'content' => getLangInfo('writemsg','group_attorn_content',array(
						'username'	=> $windid,
						'cyid'		=> $cyid,
						'cname'		=> $colony['cname'],
						'descrip'	=> $colony['descrip']
					)),
				)
			);

			refreshto("{$basename}cyid=$cyid", '转让群组成功!');
		}
	} elseif ($t == 'disband') {

		if (!(((1 == $SYSTEM['colonyright'] || $colony['admin'] == $windid) && $groupRight['allowmerge']) || $groupid == '3')) {
			Showmsg('colony_out_right');
		}

		if (empty($_POST['step'])) {

			list($isheader,$isfooter,$tplname,$isleft) = array(false,true,"m_group",true);

		} else {

			S::gp(array('password'));
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userinfo = $userService->get($winduid);

			if (md5($password) != $userinfo['password']) {
				Showmsg('您输入的密码不正确!');
			}
			if ($db->get_value("SELECT COUNT(*) as sum FROM pw_cnalbum WHERE atype=1 AND ownerid=" . S::sqlEscape($cyid)) > 0) {
				Showmsg('colony_del_photo');
			}
			if ($colony['cnimg']) {
				pwDelatt("cn_img/$colony[cnimg]",$db_ifftp);
				pwFtpClose($ftp);
			}
			$query = $db->query("SELECT uid FROM pw_cmembers WHERE colonyid=".S::sqlEscape($cyid)." AND ifadmin != '-1'");
			while ($rt = $db->fetch_array($query)) {
				$cMembers[] = $rt['uid'];
			}
			updateUserAppNum($cMembers,'group','minus');
			$db->update("DELETE FROM pw_cmembers WHERE colonyid=" . S::sqlEscape($cyid));
			//* $db->update("DELETE FROM pw_colonys WHERE id=" . S::sqlEscape($cyid));
			pwQuery::delete('pw_colonys', 'id=:id', array($cyid));
			$db->update("UPDATE pw_cnclass SET cnsum=cnsum-1 WHERE fid=" . S::sqlEscape($colony['classid']) . " AND cnsum>0");
			$db->update("DELETE FROM pw_argument WHERE cyid=" . S::sqlEscape($cyid));
			$db->update("DELETE FROM pw_active WHERE cid=" . S::sqlEscape($cyid));

			refreshto("apps.php?q=groups", '解散群组成功!');
		}
	}
} elseif ($a == 'del') {

	define('AJAX',1);
	define('F_M',true);
	S::gp(array('tid', 'pid'), null, 2);
	$rt = $db->get_one("SELECT t.authorid,t.author,t.ptable FROM pw_argument a LEFT JOIN pw_threads t ON a.tid=t.tid WHERE a.tid=" . S::sqlEscape($tid) . ' AND a.cyid=' . S::sqlEscape($cyid));
	$pw_posts = GetPtable($rt['ptable']);
	$reply = $db->get_one("SELECT pid,fid,tid,aid,author,authorid,postdate,subject,content,anonymous,ifcheck FROM $pw_posts WHERE tid='$tid' AND pid=" . S::sqlEscape($pid));
	if (empty($reply)) {
		Showmsg('data_error');
	}
	$ifOwnDelRight = $winduid == $reply['authorid'] && $_G['allowdelatc'] ? 1 : 0;
	if (empty($rt) || (!$ifadmin && !$ifOwnDelRight)) {
		Showmsg('data_error');
	}
	if (empty($_POST['step'])) {

		require_once PrintEot('m_ajax');
		ajax_footer();

	} else {

		$reply['ptable'] = $rt['ptable'];
		!$reply['subject'] && $reply['subject'] = substrs($reply['content'],35);
		$reply['postdate'] = get_date($reply['postdate']);

		require_once(R_P.'require/writelog.php');
		L::loadClass('forum', 'forum', false);
		require_once(R_P.'require/credit.php');
		$pwforum = new PwForum($colony['classid']);
		$creditset = $credit->creditset($pwforum->creditset, $db_creditset);
	
		$msg_delrvrc  = abs($creditset[$d_key]['rvrc']);
		$msg_delmoney = abs($creditset[$d_key]['money']);

		M::sendNotice(
			array($reply['author']),
			array(
				'title' => getLangInfo('writemsg','delrp_title'),
				'content' => getLangInfo('writemsg','delrp_content',array(
					'manager'	=> $windid,
					'fid'		=> $pwforum->fid,
					'tid'		=> $tid,
					'subject'	=> substrs($reply['subject'],28),
					'postdate'	=> $reply['postdate'],
					'forum'		=> strip_tags($pwforum->name),
					'affect'	=> "{$db_rvrcname}：-{$msg_delrvrc}，{$db_moneyname}：-{$msg_delmoney}",
					'admindate'	=> get_date($timestamp),
					'reason'	=> ''
				)),
			)
		);

		//* $db->update("UPDATE pw_colonys SET pnum=pnum-1, todaypost=todaypost-1 WHERE id=". S::sqlEscape($cyid));
		$db->update(pwQuery::buildClause("UPDATE :pw_table SET pnum=pnum-1, todaypost=todaypost-1 WHERE id=:id", array('pw_colonys', $cyid)));
		$colony['pnum']--;
		updateGroupLevel($colony['id'], $colony);

		$delarticle = L::loadClass('DelArticle', 'forum');
		$delarticle->delReply(array($reply), $db_recycle, true, true);
		Showmsg('delreply_success');
	}
} elseif ($a == 'edit') {

	$a_key = 'thread';
	S::gp(array('pid','article'), 'GP');
	banUser();

	L::loadClass('forum', 'forum', false);
	L::loadClass('post', 'forum', false);
	
	$fid = $colony['classid'];
	$pwforum = new PwForum($colony['classid']);
	$pwpost  = new PwPost($pwforum);
	//$pwpost->forumcheck();
	$pwpost->postcheck();

	L::loadClass('postmodify', 'forum', false);
	if ($pid && is_numeric($pid)) {
		$postmodify = new replyModify($tid, $pid, $pwpost);
	} else {
		$postmodify = new topicModify($tid, 0, $pwpost);
	}
	$atcdb = $postmodify->init();
	$postmodify->check();

	if ($postmodify->type == 'topic' && !($atcdb['tpcstatus'] & 1)) {
		Showmsg('data_error');
	}
	$_G['uploadtype'] && $db_uploadfiletype = $_G['uploadtype'];
	$db_uploadfiletype = !empty($db_uploadfiletype) ? (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype)) : array();

	if (empty($_POST['step'])) {

		$editor = getstatus($winddb['userstatus'], PW_USERSTATUS_EDITOR) ? 'wysiwyg' : 'textmode';
		$GLOBALS += getAttachConfig();

		$attach = '';
		if ($atcdb['attachs']) {
			foreach ($atcdb['attachs'] as $key => $value) {
				list($value['attachurl'],) = geturl($value['attachurl'],'lf');
				$attach .= "'$key' : ['$value[name]', '$value[size]', '$value[attachurl]', '$value[type]', '$value[special]', '$value[needrvrc]', '$value[ctype]', '$value[descrip]'],";
			}
			$attach = rtrim($attach,',');
		}
		!$attachHide && $atcdb['ifhide'] && $attachHide = 'checked';
		$atc_content = str_replace(array('<','>','&nbsp;'),array('&lt;','&gt;',' '),$atcdb['content']);
		if (strpos($atc_content,$db_bbsurl) !== false) {
			$atc_content = str_replace('p_w_picpath',$db_picpath,$atc_content);
			$atc_content = str_replace('p_w_upload',$db_attachname,$atc_content);
		}
		$atc_title = $atcdb['subject'];

	} else {

		S::gp(array('atc_title','atc_content'), 'P', 0);
		S::gp(array('atc_tags','atc_hideatt','flashatt','atc_convert','isAttachOpen'), 'P');

		require_once(R_P . 'require/bbscode.php');
		if ($postmodify->type == 'topic') {
			$postdata = new topicPostData($pwpost);
			$postdata->initData($postmodify);
			$postdata->setTags($atc_tags);
		} else {
			$postdata = new replyPostData($pwpost);
			$postdata->initData($postmodify);
		}
		$postdata->setTitle($atc_title);
		$postdata->setContent($atc_content);
		$postdata->setConvert($atc_convert);
		$isAttachOpen && $postdata->setHideatt($atc_hideatt);
		$postdata->setIfsign(1, 0);
		$postdata->conentCheck();
		$postdata->iscontinue = $iscontinue;

		if ($postmodify->hasAtt()) {
			S::gp(array('oldatt_special','oldatt_needrvrc'), 'P', 2);
			S::gp(array('oldatt_ctype','oldatt_desc'), 'P');
			$postmodify->initAttachs($oldatt_special, $oldatt_needrvrc, $oldatt_ctype, $oldatt_desc);
		}
		L::loadClass('attupload', 'upload', false);
		if (PwUpload::getUploadNum() || $flashatt) {
			$postdata->att = new AttUpload($winduid, $flashatt);
			$postdata->att->check();
			$postdata->att->setReplaceAtt($postmodify->replacedb);
		}
		$postmodify->execute($postdata);

		if ($postdata->getIfcheck()) {
			if ($postdata->filter->filter_weight == 3) {
				$pinfo = 'enter_words';
				$banword = implode(',',$postdata->filter->filter_word);
			} elseif($prompts = $pwpost->getprompt()){
				isset($prompts['allowhide'])   && $pinfo = "post_limit_hide";
				isset($prompts['allowsell'])   && $pinfo = "post_limit_sell";
				isset($prompts['allowencode']) && $pinfo = "post_limit_encode";
			}else{
				$pinfo = 'enter_thread';
			}
		} else {
			if ($postdata->filter->filter_weight == 2) {
				$banword = implode(',',$postdata->filter->filter_word);
				$pinfo = 'post_word_check';
			} elseif ($postdata->linkCheckStrategy) {
				$pinfo = 'post_link_check';
			}  else {
				$pinfo = 'post_check';
			}
		}
		$page = floor($article/$db_readperpage) + 1;
		
		$url = "apps.php?q=group&a=read&cyid=$cyid&tid=$tid&page=$page#$pid";
		$msg = defined('AJAX') ?  "success\t".$url : $pinfo;	
		refreshto($url, $msg);

	}
} elseif ($a == 'ajaxedit') {

	define('AJAX',1);
	define('F_M', true);
	S::gp(array('pid', 'article'), 'GP');
	banUser();

	L::loadClass('forum', 'forum', false);
	L::loadClass('post', 'forum', false);

	$pwforum = new PwForum($colony['classid']);
	$pwpost  = new PwPost($pwforum);
	//$pwpost->forumcheck();
	$pwpost->postcheck();

	L::loadClass('postmodify', 'forum', false);
	if ($pid && is_numeric($pid)) {
		$postmodify = new replyModify($tid, $pid, $pwpost);
	} else {
		$pid = 'tpc';
		$postmodify = new topicModify($tid, 0, $pwpost);
	}
	$atcdb = $postmodify->init();
	$postmodify->check();

	if ($postmodify->type == 'topic' && !($atcdb['tpcstatus'] & 1)) {
		Showmsg('data_error');
	}
	if (empty($_POST['step'])) {

		$atc_content = str_replace(array('<','>','&nbsp;'),array('&lt;','&gt;',' '),$atcdb['content']);
		if (strpos($atc_content,$db_bbsurl) !== false) {
			$atc_content = str_replace('p_w_picpath',$db_picpath,$atc_content);
			$atc_content = str_replace('p_w_upload',$db_attachname,$atc_content);
		}
		$atc_title = $atcdb['subject'];

		require_once PrintEot('m_ajax');
		ajax_footer();

	} else {

		S::gp(array('atc_title','atc_content'), 'P', 0);
		require_once(R_P.'require/bbscode.php');

		if ($postmodify->type == 'topic') {
			$postdata = new topicPostData($pwpost);
		} else {
			$postdata = new replyPostData($pwpost);
		}
		$postdata->initData($postmodify);
		$postdata->setTitle($atc_title);
		$postdata->setContent($atc_content);
		$postdata->setConvert(1);
		$postmodify->execute($postdata);

		extract(L::style());

		$leaveword = $atcdb['leaveword'] ? leaveword($atcdb['leaveword']) : '';
		$content   = convert($atc_content.$leaveword, $db_windpost);

		if (strpos($content,'[p:') !== false || strpos($content,'[s:') !== false) {
			$content = showface($content);
		}
		if ($atcdb['ifsign'] < 2) {
			$content = str_replace("\n",'<br />',$content);
		}
		if ($postdata->data['ifwordsfb'] == 0) {
			$wordsfb = L::loadClass('FilterUtil', 'filter');
			$content = addslashes($wordsfb->convert(stripslashes($content)));
		}
		$creditnames = pwCreditNames();
		
		if ($atcdb['attachs']) {
			$attachShow = new attachShow(($pwpost->isGM || pwRights($pwpost->isBM, 'delattach')), $pwforum->forumset['uploadset']);
			$attachShow->setData($atcdb['attachs']);
			$attachShow->parseAttachs($pid, $content, $winduid == $atcdb['authorid']);
		}
		$alterinfo && $content .= "<div id=\"alert_$pid\" style=\"color:gray;margin-top:30px\">[ $alterinfo ]</div>";
		$atcdb['icon'] = $atcdb['icon'] ? "<img src=\"$imgpath/post/emotion/$atcdb[icon].gif\" align=\"left\" border=\"0\" />" : '';
		echo "success\t".stripslashes($atc_title)."\t".str_replace(array("\r","\t"), array("",""), stripslashes($content));
		ajax_footer();
	}
} elseif ($a == 'join') {

	define('F_M',true);
	$groupid == 'guest' && Showmsg('not_login');

	if (($return = $newColony->checkJoinStatus($winduid)) !== true) {
		Showmsg($return);
	}
	S::gp(array('frombbs'));
	$return = $newColony->addColonyUser($winduid, $windid, $frombbs);
	Showmsg($return);

} elseif ($a == 'out') {

	define('AJAX',1);
	define('F_M',true);
	!$colony['ifcyer'] && Showmsg('undefined_action');

	if ($windid == $colony['admin']) {
		Showmsg('colony_out_admin');
	}

	if (empty($_POST['step'])) {

		require_once PrintEot('m_ajax');
		ajax_footer();

	} else {
		if ($colony['ifadmin'] != '-1') {
			$newColony->updateInfoCount(array('members' => -1));
		}
		//* $db->update("DELETE FROM pw_cmembers WHERE colonyid=" . S::sqlEscape($cyid) . " AND uid=" . S::sqlEscape($winduid));
		pwQuery::delete('pw_cmembers', 'colonyid=:colonyid AND uid=:uid', array($cyid, $winduid));
		
		updateUserAppNum($winduid,'group','recount');

		$colony['members']--;
		updateGroupLevel($colony['id'], $colony);

		Showmsg('colony_outsuccess');
	}
} elseif ($a == 'fanoutmsg') {

	define('AJAX',1);
	!$ifadmin && Showmsg('undefined_action');

	$messageServer = L::loadClass('message', 'message');
	!$_G['multiopen'] && Showmsg('您没有群发消息权限');
	if(!($messageServer->checkUserMessageLevle('sms',1))) Showmsg ( '你已超过每日发送消息数或你的消息总数已满' );

	if (empty($_POST['step'])) {

		S::gp(array('selid', 'group'), null, 2);

		$uids = $usernames = array();

		if ($selid) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			foreach ($userService->getByUserIds($selid) as $rt) {
				$uids[] = $rt['uid'];
				$usernames[] = $rt['username'];
			}
		} else {
			$sql = ' WHERE colonyid=' . S::sqlEscape($cyid) . ' AND uid<>' . S::sqlEscape($winduid);
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

		require_once PrintEot('m_ajax');
		ajax_footer();

	} else {

		S::gp(array('group'), null, 2);
		S::gp(array('uids', 'subject', 'content'));

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
		if ($uids) {
			$selid = explode(',', $uids);
			$query = $db->query("SELECT username FROM pw_members WHERE uid IN (" . S::sqlImplode($selid) . ')');
		} else {
			$sql = ' WHERE colonyid=' . S::sqlEscape($cyid) . ' AND uid<>' . S::sqlEscape($winduid);
			switch ($group) {
				case '1': $sql .= " AND ifadmin='1'";break;
				case '2': $sql .= " AND ifadmin='0'";break;
				case '3': $sql .= " AND ifadmin='-1'";break;
			}
			$query = $db->query("SELECT username FROM pw_cmembers $sql LIMIT 500");
		}
		$toUsers = array();
		while ($rt = $db->fetch_array($query)) {
			$toUsers[] = $rt['username'];
		}
		if ($toUsers) {
			if ($uids) {
				M::sendMessage(
					$winduid,
					$toUsers,
					array(
						'create_uid'	=> $winduid,
						'create_username'	=> $windid,
						'title' => $subject,
						'content' => stripslashes($content),
					)
				);
			} else {
				M::sendGroupMessage(
					$winduid,
					$cyid,
					array(
						'create_uid'	=> $winduid,
						'create_username'	=> $windid,
						'title' => $subject,
						'content' => stripslashes($content),
					),
					$toUsers
				);
			}
		}

		Showmsg('send_success');
	}
} elseif ($a == 'checkpostright') {
	define('AJAX','1');
	if (!$ifadmin && !$colony['ifFullMember']) {
		if (!$colony['ifopen']) {
			Showmsg('colony_ajax_post');
		} elseif ($colony['ifadmin'] == '-1') {
			Showmsg('colony_post');
		} else {
			Showmsg('colony_ajax_post');
		}
	} else {
		echo "ok";ajax_footer();
	}
}
require_once PrintEot('m_group');
pwOutPut();

function getAttachConfig() {
	global $db_uploadfiletype, $db_sellset, $db_enhideset, $pwforum, $_G;
	$conf = array();
	$conf['attachAllow'] = pwJsonEncode($db_uploadfiletype);
	$conf['imageAllow'] = pwJsonEncode(getAllowKeysFromArray($db_uploadfiletype, array('jpg','jpeg','gif','png','bmp')));

	$sellCredit = $enhideCredit = array();
	empty($db_sellset['type']) && $db_sellset['type'] = array('money');
	empty($db_enhideset['type']) && $db_enhideset['type'] = array('rvrc');

	foreach ($db_sellset['type'] as $key => $value) {
		$sellCredit[$value] = pwCreditNames($value);
	}
	foreach ($db_enhideset['type'] as $key => $value) {
		$enhideCredit[$value] = pwCreditNames($value);
	}
	list($conf['sellCredit'], $conf['enhideCredit']) = array(pwJsonEncode($sellCredit), pwJsonEncode($enhideCredit));
	$conf['htmlsell'] = ($pwforum->foruminfo['allowsell'] && $_G['allowsell']) ? '' : 'disabled';
	$conf['htmlhide'] = ($pwforum->forumset['allowencode'] && $_G['allowencode']) ? '' : 'disabled';

	return $conf;
}
?>