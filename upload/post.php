<?php
define('SCR','post');
if (isset($_POST['ajax']) && $_POST['ajax'] == 1) {
	define("AJAX",1);
}
require_once('global.php');
L::loadClass('forum', 'forum', false);
L::loadClass('post', 'forum', false);
//* include_once pwCache::getPath(D_P.'data/bbscache/cache_post.php');
pwCache::getData(D_P.'data/bbscache/cache_post.php');
/**
* 版块缓冲文件
*/

empty($fid) && Showmsg('undefined_action');
$pwforum = new PwForum($fid);
$pwpost  = new PwPost($pwforum);
if (!S::inArray($windid, $manager)) {
	$pwpost->forumcheck();
	$pwpost->postcheck();
}

$foruminfo =& $pwforum->foruminfo;
$forumset =& $pwforum->forumset;

if ($forumset['link']) {
	Showmsg('本版块为外链版块，禁止发帖');
}

S::gp(array('action','article','pid','page'));
S::gp(array('special','modelid','pcid', 'cyid','actmid'),GP,2);
$replacedb = array();
$secondurl = "thread.php?fid=$fid";
!$action && $action = "new";
$replayorder_default = 'checked';
if ($action == 'new') {

	$theSpecialFlag = false;//是否是特殊帖子（分类、团购、活动）
	if ($modelid > 0) {/*主题分类*/
		L::loadClass('posttopic', 'forum', false);
		$postTopic = new postTopic($pwpost);
		if (!$_G['allowmodelid']) {
			Showmsg('post_allowpost');
		}
		if (strpos(",".$pwforum->foruminfo['modelid'].",",",".$modelid.",") === false) {
			Showmsg('forum_model_undefined');
		}
		if (!$postTopic->topiccatedb[$postTopic->topicmodeldb[$modelid]['cateid']]['ifable']) {
			Showmsg('topic_cate_unable');
		}
		!$postTopic->topicmodeldb[$modelid]['ifable'] && Showmsg('topic_model_unable');
		$special = $pcid = $actmid = 0;
		$theSpecialFlag = true;
	} elseif ($pcid > 0) {/*团购*/
		L::loadClass('postcate', 'forum', false);
		$postCate = new postCate($pwpost);
		if (strpos(",".$pwforum->foruminfo['pcid'].",",",".$pcid.",") === false) {
			Showmsg('post_allowtype');
		}
		if (!$postCate->postcatedb[$pcid]['ifable']) {
			Showmsg('forum_pc_undefined');
		}
		if (strpos(",".$_G['allowpcid'].",",",".$pcid.",") === false) {
			Showmsg('post_allowpost');
		}
		$special = $modelid = $actmid = 0;
		$theSpecialFlag = true;
	} elseif ($actmid > 0) {/*活动分类*/
		L::loadClass('ActivityForBbs', 'activity', false);
		$postActForBbs = new PW_ActivityForBbs($pwpost);
		if (!$_G['allowactivity']) {
			Showmsg('post_allowpost');
		}
		if (strpos(",".$pwforum->foruminfo['actmids'].",",",".$actmid.",") === false) {
			Showmsg('forum_model_undefined');
		}
		if (!$postActForBbs->activitycatedb[$postActForBbs->activitymodeldb[$actmid]['actid']]['ifable']) {
			Showmsg('topic_cate_unable');
		}
		!$postActForBbs->activitymodeldb[$actmid]['ifable'] && Showmsg('topic_model_unable');
		$special = $pcid = $modelid = 0;
		$theSpecialFlag = true;
	} elseif (!($pwforum->foruminfo['allowtype'] & pow(2,$special))) {
		$modelid = $pcid = $actmid = 0;
		if (empty($special) && $pwforum->foruminfo['allowtype'] > 0) {
			$special = (int)log($pwforum->foruminfo['allowtype'],2);
		} elseif ($pwforum->foruminfo['modelid'] || $pwforum->foruminfo['pcid'] || $pwforum->foruminfo['actmids']) {
			L::loadClass('posttopic', 'forum', false);
			$postTopic = new postTopic($pwpost);
			$modeliddb = explode(",",$pwforum->foruminfo['modelid']);
	
			/*判断分类信息是否存在*/
			foreach ($modeliddb as $value) {
				if ($postTopic->topiccatedb[$postTopic->topicmodeldb[$value]['cateid']]['ifable'] && $_G['allowmodelid'] && $postTopic->topicmodeldb[$value]['ifable']) {
					$modelid = $value;
					$theSpecialFlag = true;
					break;
				}
			}

			/*判断团购是否存在*/
			if (!$modelid) {
				L::loadClass('postcate', 'forum', false);
				$postCate = new postCate($pwpost);
				$pciddb = explode(",",$pwforum->foruminfo['pcid']);
			
				foreach ($pciddb as $value) {
					if ($postCate->postcatedb[$value]['ifable'] && strpos(",".$_G['allowpcid'].",",",".$value.",") !== false) {
						$theSpecialFlag = true;
						$pcid = $value;
						break;
					}
				}
			}

			/*判断活动是否存在*/
			if (!$pcid && !$modelid) {
				L::loadClass('ActivityForBbs', 'activity', false);
				$postActForBbs = new PW_ActivityForBbs($pwpost);

				$actmiddb = explode(",",$pwforum->foruminfo['actmids']);
				foreach ($actmiddb as $value) {
					if ($postActForBbs->activitycatedb[$postActForBbs->activitymodeldb[$value]['actid']]['ifable'] && $_G['allowactivity'] && $postActForBbs->activitymodeldb[$value]['ifable']) {
						$actmid = $value;
						$theSpecialFlag = true;
						break;
					}
				}
				if (!$actmid) {
					Showmsg('post_allowtype');
				}
			}
		} else {
			Showmsg('post_allowtype');
		}

	}
}
/**
* 禁止受限制用户发言
*/
if ($groupid == 6 || getstatus($winddb['userstatus'], PW_USERSTATUS_BANUSER)) {
	$flag  = 0;
	$bandb = $delban = array();
	$query = $db->query("SELECT * FROM pw_banuser WHERE uid=".S::sqlEscape($winduid));
	while ($rt = $db->fetch_array($query)) {
		if ($rt['type'] == 1 && $timestamp - $rt['startdate'] > $rt['days']*86400) {
			$delban[] = $rt['id'];
		} elseif ($rt['fid'] == 0 || $rt['fid'] == $fid) {
			$bandb[$rt['fid']] = $rt;
		} else {
			$flag = 1;
		}
	}
	$delban && $db->update('DELETE FROM pw_banuser WHERE id IN('.S::sqlImplode($delban).')');

	$updateUser = array();
	if ($groupid == 6 && !isset($bandb[0])) {
		$updateUser['groupid'] = -1;
	}
	if (getstatus($winddb['userstatus'], PW_USERSTATUS_BANUSER) && !isset($bandb[$fid]) && !$flag) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->setUserStatus($winduid, PW_USERSTATUS_BANUSER, false);
	}
	if (count($updateUser)) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userService->update($winduid, $updateUser);
	}
	if ($bandb) {
		$bandb = current($bandb);
		if ($bandb['type'] == 1) {
			$s_date = get_date($bandb['startdate']);
			$e_date = $bandb['startdate'] + $bandb['days']*86400;
			$e_date = get_date($e_date);
			Showmsg('ban_info1');
		} else {
			if ($bandb['type'] == 3) {
				Cookie('force',$winduid);
				Showmsg('ban_info3');
			} else {
				Showmsg('ban_info2');
			}
		}
	}
}
if (GetCookie('force') && $winduid != GetCookie('force')) {
	$force = GetCookie('force');
	$bandb = $db->get_one("SELECT type FROM pw_banuser WHERE uid=".S::sqlEscape($force)." AND fid='0'");
	if ($bandb['type'] == 3) {
		Showmsg('ban_info3');
	} else {
		Cookie('force','',0);
	}
}

$userlastptime = $groupid != 'guest' ?  $winddb['lastpost'] : GetCookie('userlastptime');
/**
* 灌水预防
*/
$tdtime  >= $winddb['lastpost'] && $winddb['todaypost'] = 0;
$montime >= $winddb['lastpost'] && $winddb['monthpost'] = 0;
if ($_G['postlimit'] && $winddb['todaypost'] >= $_G['postlimit']) {
	Showmsg('post_gp_limit');
}
if (!empty($_POST['step']) && !$pwpost->isGM && $_G['postpertime'] && $timestamp>=$userlastptime && $timestamp-$userlastptime<=$_G['postpertime'] && !pwRights($pwpost->isBM,'postpers')) {
	Showmsg('post_limit');
}
list($postq,$showq) = explode("\t", $db_qcheck);
$_G['uploadtype'] && $db_uploadfiletype = $_G['uploadtype'];
$db_uploadfiletype = !empty($db_uploadfiletype) ? (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype)) : array();
empty($db_sellset['type']) && $db_sellset['type'] = array('money');
empty($db_enhideset['type']) && $db_enhideset['type'] = array('rvrc');

if (empty($_POST['step'])) {

	require_once(R_P.'require/credit.php');
	$editor = getstatus($winddb['userstatus'], PW_USERSTATUS_EDITOR) ? 'wysiwyg' : 'textmode';
	!is_numeric($db_attachnum) && $db_attachnum = 1;
	$htmlsell = ($pwforum->foruminfo['allowsell'] && $_G['allowsell']) ? '' : 'disabled';
	$htmlhide = ($pwforum->forumset['allowencode'] && $_G['allowencode']) ? '' : 'disabled';
	$htmlpost = $attachHide = ($pwforum->foruminfo['allowhide'] && $_G['allowhidden']) ? '' : 'disabled';
	$ifanonymous= ($pwpost->isGM || $pwforum->forumset['anonymous'] && $_G['anonymous']) ? '' : 'disabled';
	$groupid   == 'guest' && $userrvrc = 0;
	$atc_title  = $atc_content = $ifmailck = $selltype = $enhidetype = $alltype = $replyrewardcredit = '';
	$sellCredit = $enhideCredit = $customCreditValue = $userAllCredits = array();

	$attachAllow = pwJsonEncode($db_uploadfiletype);
	$imageAllow = pwJsonEncode(getAllowKeysFromArray($db_uploadfiletype, array('jpg','jpeg','gif','png','bmp')));
	
	if (S::inArray($action, array('new', 'modify')) && $_G['allowreplyreward'] && S::isArray($_CREDITDB)) {
		$customCreditValue = $credit->get($winduid, 'CUSTOM');
	}
	foreach ($credit->cType as $key => $value) {
		if (S::inArray($action, array('new', 'modify')) && $_G['allowreplyreward'] && ($winddb[$key] || $customCreditValue[$key])) {
			$replyrewardcredit .= "<option value=\"$key\">" . $value . "</option>";
			$userAllCredits['c' . $key] = array(
				$winddb[$key] ? ($key == 'rvrc' ? $winddb[$key] / 10 : $winddb[$key]) : $customCreditValue[$key],
				$value,
				$credit->cUnit[$key]
			);
		}
		$alltype .= "<option value=\"$key\">".$value."</option>";
	}
	$userAllCredits && $userAllCredits = pwJsonEncode($userAllCredits);
	foreach ($db_sellset['type'] as $key => $value) {
		$selltype .= "<option value=\"$value\">".$credit->cType[$value]."</option>";
		$sellCredit[$value] = $credit->cType[$value];
	}
	if (is_array($db_enhideset['type'])) {
		foreach ($db_enhideset['type'] as $key => $value) {
			$enhidetype .= "<option value=\"$value\">".$credit->cType[$value]."</option>";
			$enhideCredit[$value] = $credit->cType[$value];
		}
	}
	list($sellCredit, $enhideCredit) = array(pwJsonEncode($sellCredit), pwJsonEncode($enhideCredit));

	require_once(R_P.'require/showimg.php');
	list($postFaceUrl) = showfacedesign($winddb['icon'],1,'m');

	$icondb = array();
	if ($db_threademotion) {
		$emotion = @opendir(S::escapeDir("$imgdir/post/emotion"));
		while (($emotionimg = @readdir($emotion)) !== false) {
			if ($emotionimg != "." && $emotionimg != ".." && $emotionimg != "" && preg_match("/^(\d+)\.(gif|jpg|png|bmp)$/i", $emotionimg, $emotionMatch)) {
				$icondb[$emotionMatch[1]] = $emotionimg;
			}
		}
		ksort($icondb);
		@closedir($emotion);
	}

	//multiple post types
	if ($foruminfo['allowtype'] && (($foruminfo['allowtype'] & 1) || ($foruminfo['allowtype'] & 2 && $_G['allownewvote']) || ($foruminfo['allowtype'] & 4 && $_G['allowactive']) || ($foruminfo['allowtype'] & 8 && $_G['allowreward'])|| ($foruminfo['allowtype'] & 16) || $foruminfo['allowtype'] & 32 && $_G['allowdebate'])) {
		$N_allowtypeopen = true;
	} else {
		$N_allowtypeopen = false;
	}
	
} else {
	if ($db_cloudgdcode && defined('AJAX') && S::inArray($action, array('reply', 'quote'))) $keepCloudCaptchaCode = true;
	PostCheck(1, ($db_gdcheck & 4) && (!$db_postgd || $winddb['postnum'] < $db_postgd), ($db_ckquestion & 4 && (!$postq || $winddb['postnum'] < $postq) && $db_question));
	!$windid && $windid = '游客';
	/*
	if ($db_xforwardip && $_POST['_hexie'] != GetVerify($onlineip.$winddb['regdate'].$fid.$tid)) {
		Showmsg('undefined_action');
	}
	*/
}

//默认动漫表情处理
if ($db_windmagic && ($action == 'new' || ($action == 'modify' && $pid == 'tpc'))) {
	$mDef = '';
	//* @include_once pwCache::getPath(D_P."data/bbscache/myshow_default.php");
	pwCache::getData(D_P."data/bbscache/myshow_default.php");
}
if ($action == "new") {
	require_once(R_P.'require/postnew.php');
} elseif ($action == "reply" || $action == "quote") {
	require_once(R_P.'require/postreply.php');
} elseif ($action == "modify") {
	require_once(R_P.'require/postmodify.php');
} else {
	Showmsg('undefined_action');
}
?>