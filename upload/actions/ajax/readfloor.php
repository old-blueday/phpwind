<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('tid', 'pids'));

if (empty($tid) || empty($pids)) {
	echo 'fail';ajax_footer();
}
$pidArr = array_unique(explode(',', $pids));
$readdb = $_pids = array();

$threadService = L::loadClass('threads', 'forum');
$read = $threadService->getThreads($tid, in_array('tpc', $pidArr));
if (empty($read)) {
	echo 'fail';ajax_footer();
}
if (!($foruminfo = L::forum($read['fid']))) {
	echo 'fail';ajax_footer();
}
$ptable = $read['ptable'];
$forumset = $foruminfo['forumset'];
list(,,$downloadmoney,$downloadimg) = explode("\t",$forumset['uploadset']);

$isGM = CkInArray($windid,$manager);
$isBM = admincheck($foruminfo['forumadmin'], $foruminfo['fupadmin'], $windid);
$admincheck = ($isGM || $isBM) ? 1 : 0;
if (!$isGM) {#非创始人权限获取
	$pwSystem = pwRights($isBM);
	if ($pwSystem && ($pwSystem['tpccheck'] || $pwSystem['digestadmin'] || $pwSystem['lockadmin'] || $pwSystem['pushadmin'] || $pwSystem['coloradmin'] || $pwSystem['downadmin'] || $pwSystem['delatc'] || $pwSystem['moveatc'] || $pwSystem['copyatc'] || $pwSystem['topped'] || $pwSystem['unite'] || $pwSystem['pingcp'] || $pwSystem['areapush'])) {
		$managecheck = 1;
	}
	$pwPostHide = $pwSystem['posthide'];
	$pwSellHide = $pwSystem['sellhide'];
	$pwEncodeHide = $pwSystem['encodehide'];
} else {
	$managecheck = $pwPostHide = $pwSellHide = $pwEncodeHide = 1;
}

if (in_array('tpc', $pidArr)) {
	$read['pid'] = 'tpc';
	$readdb[] = $read;
	$read['aid'] && $read['ifhide'] && $_pids['tpc'] = 0;
	$pidArr = array_diff($pidArr, array('tpc'));
}

if ($pidArr) {
	$pw_posts = GetPtable($ptable);
	$query = $db->query("SELECT * FROM $pw_posts WHERE tid=" . pwEscape($tid) . " AND ifcheck='1' AND pid IN(" . S::sqlImplode($pidArr) . ')');
	while ($read = $db->fetch_array($query)) {
		$readdb[] = $read;
		$read['aid'] && $read['ifhide'] && $_pids[$read['pid']] = $read['pid'];
	}
}
$attachdb = array();
if ($_pids) {
	$query = $db->query('SELECT * FROM pw_attachs WHERE tid=' . pwEscape($tid) . " AND pid IN (" . pwImplode($_pids) . ")");
	while ($rt = $db->fetch_array($query)) {
		if ($rt['pid'] == '0') $rt['pid'] = 'tpc';
		$attachdb[$rt['pid']][$rt['aid']] = $rt;
	}
}

require_once(R_P . 'require/bbscode.php');
foreach ($readdb as $key => $read) {
	$readdb[$key] = viewread($read);
}

require_once PrintEot('readfloor');
ajax_footer();

function viewread($read) {
	global $winduid,$isGM,$pwSystem,$_G,$db_windpost,$tpc_buy,$tpc_pid,$tpc_tag,$tpc_author,$tid;
	$tpc_buy = $read['buy'];
	$tpc_pid = $read['pid'];
	$tpc_tag = NULL;
	$tpc_author = '';
	if ($read['anonymous']) {
		$anonymous = (!$isGM && $winduid != $read['authorid'] && !$pwSystem['anonyhide']);
	} else {
		$anonymous = false;
	}
	if (!$anonymous) {
		$tpc_author = $read['author'];
	}
	$read['ifsign']<2 && $read['content'] = str_replace("\n", "<br />", $read['content']);
	$read['leaveword'] && $read['content'] .= leaveword($read['leaveword'],$read['pid']);
	if ($read['ifwordsfb'] != $GLOBALS['db_wordsfb']) {
		$read['content'] = wordsConvert($read['content'], array(
			'id'	=> ($tpc_pid == 'tpc') ? $tid : $tpc_pid,
			'type'	=> ($tpc_pid == 'tpc') ? 'topic' : 'posts',
			'code'	=> $read['ifwordsfb']
		));
	}
	$read['content'] = convert($read['content'], $db_windpost);
	$aids = array();
	if ($read['aid']) {
		$attachs = $GLOBALS['attachdb'][$read['pid']];
		$read['ifhide'] > 0 && ifpost($tid) >= 1 && $read['ifhide'] = 0;
		if (is_array($attachs) && !$read['ifhide']) {
			$aids = attachment($read['content']);
		}
	}
	if ($attachs && is_array($attachs) && !$read['ifhide'] && empty($viewpic)) {
		if ($winduid == $read['authorid'] || $isGM || $pwSystem['delattach']) {
			$dfadmin = 1;
		} else {
			$dfadmin = 0;
		}
		foreach ($attachs as $at) {
			$atype = '';
			$rat = array();	
			
			if ($at['type'] == 'img' && $at['needrvrc'] == 0 && (!$GLOBALS['downloadimg'] || !$GLOBALS['downloadmoney'] || $_G['allowdownload'] == 2)) {
				$a_url = geturl($at['attachurl'],'show');
				if (is_array($a_url)) {
					$atype = 'pic';
					$dfurl = '<br>'.cvpic($a_url[0], 1, $db_windpost['picwidth'], $db_windpost['picheight'], $at['ifthumb']);
					$rat = array('aid' => $at['aid'], 'name' => $at['name'], 'img' => $dfurl, 'dfadmin' => $dfadmin, 'desc' => $at['descrip']);
				} elseif ($a_url == 'imgurl') {
					$atype = 'picurl';
					$rat = array('aid' => $at['aid'], 'name' => $at['name'], 'dfadmin' => $dfadmin, 'verify' => md5("showimg{$tid}{$read[pid]}{$fid}{$at[aid]}{$GLOBALS[db_hash]}"));
				}
			} else {
				$atype = 'downattach';
				$rat = array('aid' => $at['aid'], 'name' => $at['name'], 'size' => $at['size'], 'hits' => $at['hits'],'special' => $at['special'], 'cname' => $GLOBALS['creditnames'][$at['ctype']], 'type' => $at['type'], 'dfadmin' => $dfadmin, 'desc' => $at['descrip'], 'ext' => strtolower(substr(strrchr($at['name'],'.'),1)));
				if ($at['needrvrc'] > 0) {
					!$at['ctype'] && $at['ctype'] = $at['special'] == 2 ? 'money' : 'rvrc';
					if($at['type'] == 'img') {
						$a_url = geturl($at['attachurl'],'show');
						$dfurl = '<br>'.cvpic($a_url[0], 1, $db_windpost['picwidth'], $db_windpost['picheight'], $at['ifthumb']);
						$rat['img'] = $dfurl;
					}
					if ($at['special'] == 2) {//出售
						$GLOBALS['db_sellset']['price'] > 0 && $at['needrvrc'] = min($at['needrvrc'], $GLOBALS['db_sellset']['price']);
						$rat['isBuy'] = false;
						if (in_array($at['aid'],$buyAids)) $rat['isBuy'] = true;
					} else {//加密
						$creditdb[$at['ctype']] >= $at['needrvrc'] && $rat['isThrough'] = true;
					}
					$rat['needrvrc'] = $at['needrvrc'];
				}
			}
			if (!$atype) continue;
			if (in_array($at['aid'], $aids)) {
				$read['content'] = attcontent($read['content'], $atype, $rat);
			} else {
				$read[$atype][$at['aid']] = $rat;
			}
		}
	}
	return $read;
}