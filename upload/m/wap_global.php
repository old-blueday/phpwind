<?php
error_reporting(0);
define('W_P',__FILE__ ? substr(__FILE__,0,-16) : '../');
require_once(W_P.'global.php');
if(preg_match('/(mozilla|m3gate|winwap|openwave)/i', $pwServer['HTTP_USER_AGENT'])) ObHeader($_mainUrl);
require_once(R_P.'m/wap_mod.php');
//* include_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
pwCache::getData(D_P.'data/bbscache/forum_cache.php');

if (!$db_wapifopen) {
	wap_msg('wap_closed');
}
if ($db_charset != 'utf8') {
	L::loadClass('Chinese', 'utility/lang', false);
	$chs = new Chinese('UTF8',$db_charset);
	foreach ($_POST as $key => $value) {
		$_POST[$key] = addslashes($chs->Convert(stripslashes($value)));
	}
}
function forumcheck($fid,$type) {
	global $db,$groupid,$_G,$fm;
	$fm = $db->get_one("SELECT f.password,f.allowvisit,f.allowread,f.f_type,f.f_check,f.allowpost,f.allowrp,fe.* FROM pw_forums f LEFT JOIN pw_forumsextra fe ON f.fid=fe.fid WHERE f.fid=".S::sqlEscape($fid));
	$forumset = unserialize($fm['forumset']);
	$fm['rvrcneed'] = $forumset['rvrcneed'];
	$fm['moneyneed'] = $forumset['moneyneed'];
	$fm['creditneed'] = $forumset['creditneed'];
	$fm['postnumneed'] = $forumset['postnumneed'];
	if (!$fm || $fm['f_type']=='former' && $groupid=='guest' || $fm['password']!='' || $fm['f_type']=='hidden' || $type == 'list' &&$fm['allowvisit'] && @strpos($fm['allowvisit'],",$groupid,")===false || $type == 'read' && $fm['allowread'] && @strpos($fm['allowread'],",$groupid,")===false || $fm['f_check']>'0' || wap_creditcheck()) {
		wap_msg('forum_right');
	}
}
function wap_check($fid,$action) {
	global $db,$groupid,$_G,$_time,$db_titlemax,$db_postmin,$db_postmax,$subject,$content;

	$subject = trim($subject);
	$content = trim($content);
	if ($action == 'new' && (!$subject || strlen($subject)>$db_titlemax)) {
		wap_msg('subject_limit');
	}
	if (strlen($content)>=$db_postmax || strlen($content)<$db_postmin) {
		wap_msg('content_limit');
	}

	$fm = $db->get_one("SELECT f.forumadmin,f.fupadmin,f.password,f.allowvisit,f.f_type,f.f_check,f.allowpost,f.allowrp,fe.forumset FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid=".S::sqlEscape($fid));
	$forumset  = unserialize($fm['forumset']);
	if (!$fm || $fm['password']!='' || $fm['f_type']=='hidden' || $fm['allowvisit'] && @strpos($fm['allowvisit'],",$groupid,")===false) {
		wap_msg('post_right');
	}
	if ($action == 'new') {
		$isGM = S::inArray($GLOBALS['windid'],$GLOBALS['manager']);
		$isBM = admincheck($fm['forumadmin'],$fm['fupadmin'],$GLOBALS['windid']);
		if ($fm['f_check']=='1' || $fm['f_check']=='3') {
			wap_msg('post_right');
		}
		if ($fm['allowpost'] && strpos($fm['allowpost'],",$groupid,")===false) {
			wap_msg('post_right');
		}
		if (!$fm['allowpost'] && $_G['allowpost']==0) {
			wap_msg('post_group');
		}
		if ($forumset['allowtime'] && !$isGM && !allowcheck($forumset['allowtime'],"$_time[hours]",'') && !pwRights($isBM,'allowtime')) {
			wap_msg('post_right');
		}
	} elseif ($action == 'reply') {
		if ($fm['f_check']=='2' || $fm['f_check']=='3') {
			wap_msg('reply_right');
		}
		if ($fm['allowrp'] && strpos($fm['allowrp'],",$groupid,")===false) {
			wap_msg('reply_right');
		}
		if (!$fm['allowrp'] && $_G['allowrp']==0) {
			wap_msg('reply_group');
		}
	}
}

function wap_creditcheck() {
	global $db,$winddb,$userrvrc,$fm,$groupid;
	$fm['rvrcneed']   /= 10;
	$fm['moneyneed']   = (int) $fm['moneyneed'];
	$fm['creditneed']  = (int) $fm['creditneed'];
	$fm['postnumneed'] = (int) $fm['postnumneed'];
	$check = 1;
	if ($fm['rvrcneed'] && $userrvrc < $fm['rvrcneed']) {
		$check = 0;
	} elseif ($fm['moneyneed'] && $winddb['money'] < $fm['moneyneed']) {
		$check = 0;
	} elseif ($fm['creditneed'] && $winddb['credit'] < $fm['creditneed']) {
		$check = 0;
	} elseif ($fm['postnumneed'] && $winddb['postnum'] < $fm['postnumneed']) {
		$check = 0;
	}
	if (!$check) {
		return true;
	}else{
		return false;
	}
}
?>