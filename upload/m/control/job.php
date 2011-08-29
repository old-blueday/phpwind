<?php
!defined('W_P') && exit('Forbidden');
define('SCR', 'job');
InitGP(array(
	'action','tid'
));
$basename = "index.php?a=read&tid=$tid";
if ($action == 'download') {
	set_time_limit(300);
	$aid = (int) GetGP('aid');
	empty($aid) && wap_msg('job_attach_error', $basename);
	
	$pw_attachs = L::loadDB('attachs','forum');
	
	$attach = $pw_attachs->get($aid);
	!$attach && wap_msg('job_attach_error', $basename);
	if (empty($attach['attachurl']) || strpos($attach['attachurl'], '..') !== false) {
		wap_msg('job_attach_error', $basename);
	}
	$fid = $attach['fid'];
	$aid = $attach['aid'];
	$tid = $attach['tid'];
	$pid = $attach['pid'];
	$fid = $db->get_value('SELECT fid FROM pw_threads WHERE tid=' . pwEscape($tid, false));
	$fid || wap_msg('data_error', $basename);
	if (!$windid && GetCookie('winduser') && $ol_offset) {
		$userdb = explode("\t", getuserdb(D_P . "data/bbscache/online.php", $ol_offset));
		if ($userdb && $userdb[2] == $onlineip) {
			$winddb = $db->get_one("SELECT m.uid,m.username,m.groupid,m.memberid,m.groups,md.money,md.rvrc FROM pw_members m LEFT JOIN pw_memberdata md USING(uid) WHERE m.uid=" . pwEscape($userdb['8']));
			$winduid = $winddb['uid'];
			$groupid = $winddb['groupid'];
			$groupid == '-1' && $groupid = $winddb['memberid'];
			$userrvrc = round($winddb['rvrc'] / 10, 1);
			$windid = $winddb['username'];
			if (file_exists(D_P . "data/groupdb/group_$groupid.php")) {
				require_once Pcv(D_P . "data/groupdb/group_$groupid.php");
			} else {
				require_once (D_P . "data/groupdb/group_1.php");
			}
		}
		define('FX', 1);
	}
	if (!($foruminfo = L::forum($fid))) {
		$foruminfo = $db->get_one("SELECT f.*,fe.creditset,fe.forumset,fe.commend FROM pw_forums f LEFT JOIN pw_forumsextra fe ON f.fid=fe.fid WHERE f.fid=" . pwEscape($fid));
		if ($foruminfo) {
			$foruminfo['creditset'] = unserialize($foruminfo['creditset']);
			$foruminfo['forumset'] = unserialize($foruminfo['forumset']);
			$foruminfo['commend'] = unserialize($foruminfo['commend']);
		}
	}
	!$foruminfo && wap_msg('data_error', $basename);
	require_once (R_P . 'require/forum.php');
	wind_forumcheck($foruminfo);
	if ($groupid == '3' || admincheck($foruminfo['forumadmin'], $foruminfo['fupadmin'], $windid)) { #获取管理权限
		$admincheck = 1;
	} else {
		$admincheck = 0;
	}
	if ($foruminfo['allowdownload'] && !allowcheck($foruminfo['allowdownload'], $groupid, $winddb['groups']) && !$admincheck) { #版块权限判断
		wap_msg('job_attach_forum', $basename);
	}
	if (!$foruminfo['allowdownload'] && $_G['allowdownload'] == 0 && !$admincheck) { #用户组权限判断
		wap_msg('job_attach_group', $basename);
	}
	if (!$attach_url && !$db_ftpweb && !is_readable("$attachdir/" . $attach['attachurl'])) {
		wap_msg('job_attach_error', $basename);
	}
	$fgeturl = geturl($attach['attachurl']);
	!$fgeturl[0] && wap_msg('job_attach_error', $basename);
	
	$filename = basename("$attachdir/" . $attach['attachurl']);
	$fileext = substr(strrchr($attach['attachurl'], '.'), 1);
	$filesize = 0;
	
	if (strpos($pwServer['HTTP_USER_AGENT'], 'MSIE') !== false && $fileext == 'torrent') {
		$attachment = 'inline';
	} else {
		$attachment = 'attachment';
	}
	$attach['name'] = trim(str_replace('&nbsp;', ' ', $attach['name']));
	if ($db_charset == 'utf-8') {
		if (function_exists('mb_convert_encoding')) {
			$attach['name'] = mb_convert_encoding($attach['name'], "gbk", 'utf-8');
		} else {
			L::loadClass('Chinese', 'utility/lang', false);
			$chs = new Chinese('UTF8', 'gbk');
			$attach['name'] = $chs->Convert($attach['name'],'UTF8','gbk');
		}
	}
	
	$credit = $uploadcredit = $downloadmoney = null;
	
	if ($_G['allowdownload'] == 1) {
		$forumset = $foruminfo['forumset'];
		list($uploadcredit, , $downloadmoney, ) = explode("\t", $forumset['uploadset']);
		if ($downloadmoney) {
			require_once (R_P . 'require/credit.php');
			if ($downloadmoney > 0 && $credit->get($winduid, $uploadcredit) < $downloadmoney) {
				$creditname = $credit->cType[$uploadcredit];
				wap_msg('download_money_limit', $basename);
			}
			$credit->addLog('topic_download', array(
				$uploadcredit => -$downloadmoney
			), array(
				'uid' => $winduid, 
				'username' => $windid, 
				'ip' => $onlineip, 
				'fname' => $foruminfo['name']
			));
			if (!$credit->set($winduid, $uploadcredit, -$downloadmoney, false)) {
				wap_msg('undefined_action', $basename);
			}
		}
	}
	if ($attach['needrvrc'] > 0 && !$admincheck) {
		!$windid && wap_msg('job_attach_special', $basename);
		require_once (R_P . 'require/credit.php');
		if ($attach['special'] == '2') {
			if (!$ifbuy = $db->get_one("SELECT uid FROM pw_attachbuy WHERE aid=" . pwEscape($aid) . " AND uid=" . pwEscape($winduid))) {
				!$attach['ctype'] && $attach['ctype'] = 'money';
				$usercredit = $credit->get($winduid, $attach['ctype']);
				$creditName = $credit->cType[$attach['ctype']];
				$db_sellset['price'] > 0 && $attach['needrvrc'] = min($attach['needrvrc'], $db_sellset['price']);
				if ($usercredit < $attach['needrvrc']) {
					$needrvrc = $attach['needrvrc'];
					wap_msg(($downloadmoney > 0 && $uploadcredit == $attach['ctype']) ? 'job_attach_sale_download' : 'job_attach_sale', $basename);
				}
				$db->update("INSERT INTO pw_attachbuy SET " . pwSqlSingle(array(
					'aid' => $aid, 
					'uid' => $winduid, 
					'ctype' => $attach['ctype'], 
					'cost' => $attach['needrvrc']
				)));
				$credit->addLog('topic_attbuy', array(
					$attach['ctype'] => -$attach['needrvrc']
				), array(
					'uid' => $winduid, 
					'username' => $windid, 
					'ip' => $onlineip
				));
				$credit->set($winduid, $attach['ctype'], -$attach['needrvrc'], false);
				
				if ($db_sellset['income'] < 1 || ($income = $db->get_value("SELECT SUM(cost) AS sum FROM pw_attachbuy WHERE aid=" . pwEscape($aid))) < $db_sellset['income']) {
					$username = $db->get_value("SELECT username FROM pw_members WHERE uid=" . pwEscape($attach['uid'], false));
					$credit->addLog('topic_attsell', array(
						$attach['ctype'] => $attach['needrvrc']
					), array(
						'uid' => $attach['uid'], 
						'username' => $username, 
						'ip' => $onlineip, 
						'buyer' => $windid
					));
					$credit->set($attach['uid'], $attach['ctype'], $attach['needrvrc'], false);
				}
			}
		} else {
			!$attach['ctype'] && $attach['ctype'] = 'rvrc';
			$usercredit = $credit->get($winduid, $attach['ctype']);
			if ($usercredit < $attach['needrvrc']) {
				$needrvrc = $attach['needrvrc'];
				$creditName = $credit->cType[$attach['ctype']];
				wap_msg(($downloadmoney > 0 && $uploadcredit == $attach['ctype']) ? 'job_attach_rvrc_download' : 'job_attach_rvrc');
			}
		}
	}
	if (isset($credit) && $credit->setUser) {
		$credit->runsql();
	}
	$pw_attachs->increaseField($aid, 'hits');
	
	if ($db_attachhide && $attach['size'] > $db_attachhide && $attach['type'] == 'zip' && !defined('FX')) {
		ObHeader($fgeturl[0]);
	} elseif ($fgeturl[1] == 'Local') {
		$filename = "$attachdir/" . $attach['attachurl'];
		$filesize = filesize($filename);
	}
	$ctype = '';
	switch ($fileext) {
		case "pdf" :
			$ctype = "application/pdf";
			break;
		case "rar" :
		case "zip" :
			$ctype = "application/zip";
			break;
		case "doc" :
			$ctype = "application/msword";
			break;
		case "xls" :
			$ctype = "application/vnd.ms-excel";
			break;
		case "ppt" :
			$ctype = "application/vnd.ms-powerpoint";
			break;
		case "gif" :
			$ctype = "image/gif";
			break;
		case "png" :
			$ctype = "image/png";
			break;
		case "jpeg" :
		case "jpg" :
			$ctype = "image/jpeg";
			break;
		case "wav" :
			$ctype = "audio/x-wav";
			break;
		case "mpeg" :
		case "mpg" :
		case "mpe" :
			$ctype = "video/x-mpeg";
			break;
		case "mov" :
			$ctype = "video/quicktime";
			break;
		case "avi" :
			$ctype = "video/x-msvideo";
			break;
		case "txt" :
			$ctype = "text/plain";
			break;
		default :
			$ctype = "application/octet-stream";
	}
	ob_end_clean();
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $timestamp + 86400) . ' GMT');
	header('Expires: ' . gmdate('D, d M Y H:i:s', $timestamp + 86400) . ' GMT');
	header('Cache-control: max-age=86400');
	header('Content-Encoding: none');
	header("Content-Disposition: $attachment; filename=\"{$attach['name']}\"");
	header("Content-type: $ctype");
	header("Content-Transfer-Encoding: binary");
	$filesize && header("Content-Length: $filesize");
	$i = 1;
	while (!@readfile($fgeturl[0])) {
		if (++$i > 3) break;
	}
	exit();

}

function getuserdb($filename, $offset) {
	global $db_olsize;
	if (!$offset || $offset % ($db_olsize + 1) != 0) {
		return false;
	} else {
		$fp = fopen($filename, "rb");
		flock($fp, LOCK_SH);
		fseek($fp, $offset);
		$Checkdata = fread($fp, $db_olsize);
		fclose($fp);
		return $Checkdata;
	}
}

?>