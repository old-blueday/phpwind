<?php
!defined('R_P') && exit('Forbidden');

require_once(R_P . 'require/showimg.php');

$pwforum = new PwForum($fid);
if (!$pwforum->isForum(true)) {
	Showmsg('data_error');
}
$foruminfo =& $pwforum->foruminfo;
$groupRight =& $newColony->getRight();
$pwModeImg = "$imgpath/apps";
require_once(R_P . 'u/require/core.php');
//* include_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
pwCache::getData(D_P . 'data/bbscache/o_config.php');

require_once(R_P . 'require/header.php');
list($guidename, $forumtitle) = $pwforum->getTitle();
$msg_guide = $pwforum->headguide($guidename);

$styleid = $colony['styleid'];
$basename = "thread.php?cyid=$cyid&showtype=set";

if (!$colony['ifwriteopen'] && !$ifadmin && (!$colony['ifcyer'] || $colony['ifadmin'] == '-1')) {
	Showmsg('colony_cnmenber');
}
list($faceurl) = showfacedesign($winddb['icon'], 1, 'm');

!$ifadmin && Showmsg('undefined_action');
$a_key = 'set';

S::gp('t');

//获取功能权限
$ifsetable = $newColony->getSetAble($t);
!$ifsetable && Showmsg('colony_setunable');
$t && $tmpUrlAdd .= '&t=' . $t;

if (empty($t)) {

	$jsStyle = pwJsonEncode($o_styledb);
	$jsStyleRelation = pwJsonEncode($o_style_relation);

	if (empty($_POST['step'])) {

		$titledetail = explode("~",$colony['titlefont']);
		$titlecolor = $titledetail[0];
		if ($titlecolor && !preg_match('/\#[0-9A-F]{6}/is',$titlecolor)) {
			$titlecolor = '';
		}
		if ($titledetail[1] == '1') {
			$stylename[1] = 'b one';
		} else {
			$stylename[1] = 'b';
		}
		if ($titledetail[2] == '1') {
			$stylename[2] = 'u one';
		} else {
			$stylename[2] = 'u';
		}
		if ($titledetail[3] == '1') {
			$stylename[3] = 'one';
		} else {
			$stylename[3] = '';
		}
		$filetype = (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype));
		$default_type = array('gif','jpg','jpeg','bmp','png');
		foreach ($default_type as $value) {
			$cnimg_1[$value] = $o_imgsize ? $o_imgsize :  $filetype[$value];
			$cnimg_2[$value] = 2048;
		}
		$newColony->initBanner();
		$set_banner = $colony['banner'] ? $colony['banner'] : $imgpath . '/g/' . $colony['colonystyle'] . '/preview.jpg';

		require_once PrintEot('thread_set');
		footer();

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
		
		require_once(R_P . 'require/functions.php');
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
		pwQuery::update('pw_colonys', 'id=:id', array($cyid),$pwSQL);

		refreshto("{$basename}",'colony_setsuccess');
	}

} elseif ($t == 'annouce') {

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
	pwQuery::update('pw_colonys', 'id=:id', array($cyid),$pwSQL);
	refreshto("{$basename}",'colony_setsuccess');
	
} elseif ($t == 'style') {
	
	if (empty($_POST['step'])) {
		
		$names = array();
		$query = $db->query("SELECT * FROM pw_cnskin");
		while ($rt = $db->fetch_array($query)) {
			$names[$rt['dir']] = $rt['name'];
		}
		require_once PrintEot('thread_set');
		footer();

	} else {

		S::gp(array('colonystyle'), 'P');
		$pwSQL = array(
			'colonystyle' => $colonystyle
		);

		//* $db->update("UPDATE pw_colonys SET " . S::sqlSingle($pwSQL) . ' WHERE id=' . S::sqlEscape($cyid));
		pwQuery::update('pw_colonys', 'id=:id', array($cyid), $pwSQL);

		refreshto("{$basename}&t=$t",'colony_setsuccess');
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

		require_once PrintEot('thread_set');
		footer();

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
		
		refreshto("{$basename}&t=$t",'colony_setsuccess');
	}

} elseif ($t == 'merge') {
	
	if (!($windid == $colony['admin'] && $groupRight['allowmerge'] || $groupid == '3')) {
		Showmsg('您没有权限进行合并操作!');
	}

	require_once(A_P . 'groups/lib/colonys.class.php');
	$colonyServer = new PW_Colony();

	if (empty($_POST['step'])) {
		
		$groupList = $colonyServer->getColonyList(array('admin' =>$colony['admin']));
		if (count($groupList) == 1) {
			Showmsg('没有可以合并的群组!');
		}
		require_once PrintEot('thread_set');
		footer();

	} else {

		S::gp(array('tocid'));
		S::gp(array('password'));

		if (!threadSetCheckOwnerPassword($winduid, $password)) {
			Showmsg('您输入的密码不正确!');
		}
		if (!($toColony = $colonyServer->getColonyById($tocid)) || $toColony['admin'] != $colony['admin']) {
			Showmsg('undefined_action');				
		}
		require_once(R_P . 'require/functions.php');
		if (PwColony::calculateCredit($colony) > PwColony::calculateCredit($toColony)) {
			Showmsg('只允许群积分低的群组并入群积分高的群组!');
		}
		$colonyServer->mergeColony($tocid, $cyid);

		refreshto("thread.php?cyid=$tocid", 'operate_success');
	}
} elseif ($t == 'attorn') {

	if (!($windid == $colony['admin'] && $groupRight['allowattorn'] || $groupid == '3')) {
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
		require_once PrintEot('thread_set');
		footer();

	} else {

		S::gp(array('password'));
		S::gp(array('newmanager'), 'GP', 2);

		if (!threadSetCheckOwnerPassword($winduid, $password)) {
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

		refreshto("thread.php?cyid=$cyid", '转让群组成功!');
	}
} elseif ($t == 'disband') {
	
	if (!($windid == $colony['admin'] && $groupRight['allowdisband'] || $groupid == '3')) {
		Showmsg('colony_out_right');
	}

	if (empty($_POST['step'])) {

		require_once PrintEot('thread_set');
		footer();

	} else {
		
		S::gp(array('password'));

		if (!threadSetCheckOwnerPassword($winduid, $password)) {
			Showmsg('您输入的密码不正确!');
		}
		if ($db->get_value("SELECT COUNT(*) as sum FROM pw_cnalbum WHERE atype=1 AND ownerid=" . S::sqlEscape($cyid)) > 0) {
			Showmsg('colony_del_photo');
		}
		if ($colony['cnimg']) {
			require_once(R_P . 'require/functions.php');
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
		
		refreshto("apps.php?q=groups", '解散群组成功!');
	}
} else {
	
	Showmsg('undefined_action');
}

function threadSetCheckOwnerPassword($ownerId, $inputPassword) {
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$userData = $userService->get($ownerId);
	return md5($inputPassword) == $userData['password'];
}
?>