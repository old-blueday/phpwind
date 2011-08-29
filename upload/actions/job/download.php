<?php
!defined('P_W') && exit('Forbidden');
set_time_limit(300);
$aid = (int)S::getGP('aid');
empty($aid) && Showmsg('job_attach_error');
S::gp(array('type','check'), 'GP');
require_once (R_P . 'require/credit.php');

if (!$windid && ($userdb = getCurrentOnlineUser()) && $userdb['ip'] == $onlineip) {
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$winddb = $userService->get($userdb['uid']);
	$winduid = $winddb['uid'];
	$groupid = $winddb['groupid'];
	$groupid == '-1' && $groupid = $winddb['memberid'];
	$userrvrc = round($winddb['rvrc'] / 10, 1);
	$windid = $winddb['username'];
	if (file_exists(D_P . "data/groupdb/group_$groupid.php")) {
		//* require_once pwCache::getPath(S::escapePath(D_P . "data/groupdb/group_$groupid.php"));
		pwCache::getData(S::escapePath(D_P . "data/groupdb/group_$groupid.php"));
	} else {
		//* require_once pwCache::getPath(D_P . "data/groupdb/group_1.php");
		pwCache::getData(D_P . "data/groupdb/group_1.php");
	}
	define('FX', 1);
}
$downloadServer = getDownloadFactory($type);
$downloadServer->init($aid);
$attachInfo = $downloadServer->attach;
if ($attachInfo['fid'] && $attachInfo['uid'] != $winduid) {
	//实名认证权限
	L::loadClass('forum', 'forum', false);
	$pwforum = new PwForum($attachInfo['fid']);
	if ($GLOBALS['db_authstate'] && true !== ($authMessage = $pwforum->authStatus($GLOBALS['winddb']['userstatus'],$pwforum->forumset['auth_logicalmethod']))) {
		$msg = getLangInfo('msg',$authMessage . '_upload');
		require_once PrintEot('ajax');
		ajax_footer();
	}
}


if (empty($type) && $check) {
	S::gp(array('mt'));
	$msg = $downloadServer->check();
	require_once PrintEot('ajax');
	ajax_footer();
}
if (($return = $downloadServer->execute()) !== true) {
	Showmsg($return);
}
$attach =& $downloadServer->getInfo();
$fgeturl =& $downloadServer->getUrl();

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
		$attach['name'] = $chs->Convert($attach['name']);
	}
}
if ($db_attachhide && $attach['size'] > $db_attachhide && $attach['type'] == 'zip' && !defined('FX')) {
	ObHeader($fgeturl[0]);
} elseif ($fgeturl[1] == 'Local') {
	$fgeturl[0] = R_P . $fgeturl[0];
	$filesize = filesize($fgeturl[0]);
}
$ctype = '';
switch ($fileext) {
	case "pdf":
		$ctype = "application/pdf";
		break;
	case "rar":
	case "zip":
		$ctype = "application/zip";
		break;
	case "doc":
		$ctype = "application/msword";
		break;
	case "xls":
		$ctype = "application/vnd.ms-excel";
		break;
	case "ppt":
		$ctype = "application/vnd.ms-powerpoint";
		break;
	case "gif":
		$ctype = "image/gif";
		break;
	case "png":
		$ctype = "image/png";
		break;
	case "jpeg":
	case "jpg":
		$ctype = "image/jpeg";
		break;
	case "wav":
		$ctype = "audio/x-wav";
		break;
	case "mpeg":
	case "mpg":
	case "mpe":
		$ctype = "video/x-mpeg";
		break;
	case "mov":
		$ctype = "video/quicktime";
		break;
	case "avi":
		$ctype = "video/x-msvideo";
		break;
	case "txt":
		$ctype = "text/plain";
		break;
	default:
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

function getCurrentOnlineUser() {
	global $db_online, $ol_offset, $db;
	if (empty($db_online)) {
		$userdb = explode("\t", getuserdb(D_P . "data/bbscache/online.php", $ol_offset));
		return $userdb ? array('uid' => $userdb[8], 'ip' => $userdb[2]) : array();
	} else {
		//* $olid = (int)GetCookie('olid');
		//* $userdb = $db->get_one("SELECT uid,ip FROM pw_online WHERE olid=" . S::sqlEscape($olid) . ' AND uid>0');
		
		$onlineService = L::loadClass('OnlineService', 'user');
		$userdb = $onlineService->getOnlineUserByUserId($GLOBALS['winduid']);	
				
		return $userdb;
	}
}

function getDownloadFactory($type) {
	if ($type == 'active') {
		return new activeDownload();
	} elseif ($type == 'message') {
		return new messageDownload();
	} elseif ($type && file_exists(R_P . "require/extents/attach/{$type}download.class.php")) {
		$class = $type . 'Download';
		require_once S::escapePath(R_P . "require/extents/attach/{$type}download.class.php");
		return new $class();
	}
	return new threadDownload();
}

//Interface
class downloadInterface {

	var $url;
	var $attach = array();
	
	function execute() {
		return 'job_attach_error';
	}

	function &getInfo() {
		return $this->attach;
	}

	function &getUrl() {
		return $this->url;
	}
}

class messageDownload extends downloadInterface {
	
	var $_db;
	var $aid;

	function messageDownload() {
		global $db;
		$this->_db =& $db;
	}

	function init($aid) {
		$this->aid = $aid;
		$this->attach = $this->_db->get_one("SELECT * FROM pw_attachs WHERE aid=" . S::sqlEscape($aid));
	}

	function execute() {
		if (empty($this->attach)) {
			return 'job_attach_error';
		}
		$this->url = geturl($this->attach['attachurl']);
		if (!$this->url[0]) {
			return 'job_attach_error';
		}
		$this->_db->update("UPDATE pw_attachs SET hits=hits+1 WHERE aid=" . S::sqlEscape($this->aid));
		return true;
	}
}

class activeDownload extends downloadInterface {
	
	var $_db;
	var $aid;

	function activeDownload() {
		global $db;
		$this->_db =& $db;
	}

	function init($aid) {
		$this->aid = $aid;
		$this->attach = $this->_db->get_one("SELECT * FROM pw_actattachs WHERE aid=" . S::sqlEscape($aid));
	}

	function execute() {
		if (empty($this->attach)) {
			return 'job_attach_error';
		}
		$this->url = geturl($this->attach['attachurl']);
		if (!$this->url[0]) {
			return 'job_attach_error';
		}
		$this->_db->update("UPDATE pw_actattachs SET hits=hits+1 WHERE aid=" . S::sqlEscape($this->aid));
		return true;
	}
}

class threadDownload extends downloadInterface {

	var $_db;
	var $_attachDB;

	var $tid;
	var $aid;
	var $user;
	var $groupid;
	var $uid;
	var $username;
	var $_G;

	var $admincheck;
	var $foruminfo;
	var $downloadmoney;
	var $uploadcredit;

	var $ifsale;
	var $ifdown;

	function threadDownload() {
		global $db,$winddb,$groupid,$windid,$winduid,$_G;
		$this->_db =& $db;
		
		$this->_G =& $_G;
		$this->uid =& $winduid;
		$this->username =& $windid;
		$this->user =& $winddb;
		$this->groupid =& $groupid;

		$this->foruminfo = array();
		$this->downloadmoney = '';
		$this->uploadcredit = '';

		$this->ifsale = 0;
		$this->ifdown = 0;
	}

	function init($aid) {
		$this->aid = $aid;
		$this->_attachDB = L::loadDB('attachs', 'forum');
		$this->attach = $this->_attachDB->get($aid);
	}

	function execute() {
		if (($return = $this->check()) !== true) {
			return $return;
		}
		if (($return = $this->deductDownloadCredit()) !== true) {
			return $return;
		}
		if (($return = $this->deductSaleCredit()) !== true) {
			return $return;
		}
		global $credit;
		if (isset($credit) && $credit->setUser) {
			$credit->runsql();
		}
		$this->_attachDB->increaseField($this->aid, 'hits');

		return true;
	}

	function checkDownloadCredit() {
		if ($this->_G['allowdownload'] != 1 || !$this->downloadmoney || $this->ifadmin()) {
			return true;
		}
		$this->ifdown = 2;
		if ($this->ifDownloadAtt()) {
			return true;
		}
		$this->ifdown = 1;
		global $credit;
		require_once (R_P . 'require/credit.php');
		if ($this->downloadmoney > 0 && $credit->get($this->uid, $this->uploadcredit) < $this->downloadmoney) {
			$GLOBALS['creditname'] = $credit->cType[$this->uploadcredit];
			$GLOBALS['downloadmoney'] = $this->downloadmoney;
			return 'download_money_limit';
		}
		return true;
	}
	
	function deductDownloadCredit() {
		if ($this->ifdown != 1) {
			return true;
		}
		global $credit;
		$this->_db->update("INSERT INTO pw_attachdownload SET " . S::sqlSingle(array(
			'aid' => $this->aid,
			'uid' => $this->uid,
			'ctype' => $this->uploadcredit,
			'cost' => $this->downloadmoney,
			'createdtime' => $GLOBALS['timestamp']
		)));
		$credit->addLog('topic_download',
			array($this->uploadcredit => -$this->downloadmoney),
			array(
				'uid' => $this->uid,
				'username' => $this->username,
				'ip' => $GLOBALS['onlineip'],
				'fname' => $this->foruminfo['name']
			)
		);
		if (!$credit->set($this->uid, $this->uploadcredit, -$this->downloadmoney, false)) {
			return 'undefined_action';
		}
		return true;
	}

	function ifBuyAtt() {
		return $this->_db->get_one("SELECT uid FROM pw_attachbuy WHERE aid=" . S::sqlEscape($this->aid) . " AND uid=" . S::sqlEscape($this->uid));
	}

	function ifDownloadAtt() {
		return $this->_db->get_one("SELECT uid FROM pw_attachdownload WHERE aid=" . S::sqlEscape($this->aid) . " AND uid=" . S::sqlEscape($this->uid));
	}

	function deductSaleCredit() {
		if ($this->ifsale != 1) {
			return true;
		}
		global $credit;
		$this->_db->update("INSERT INTO pw_attachbuy SET " . S::sqlSingle(array(
			'aid' => $this->aid,
			'uid' => $this->uid,
			'ctype' => $this->attach['ctype'],
			'cost' => $this->attach['needrvrc'],
			'createdtime' => $GLOBALS['timestamp']
		)));
		$credit->addLog('topic_attbuy', 
			array($attach['ctype'] => -$this->attach['needrvrc']),
			array(
				'uid' => $this->uid,
				'username' => $this->username,
				'ip' => $GLOBALS['onlineip']
			)
		);
		$credit->set($this->uid, $this->attach['ctype'], -$this->attach['needrvrc'], false);
		if ($db_sellset['income'] < 1 || ($income = $this->_db->get_value("SELECT SUM(cost) AS sum FROM pw_attachbuy WHERE aid=" . S::sqlEscape($this->aid))) < $db_sellset['income']) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$username = $userService->getUserNameByUserId($this->attach['uid']);
			$credit->addLog('topic_attsell',
				array($this->attach['ctype'] => $this->attach['needrvrc']),
				array(
					'uid' => $this->attach['uid'],
					'username' => $username,
					'ip' => $GLOBALS['onlineip'],
					'buyer' => $this->username
				)
			);
			$credit->set($this->attach['uid'], $this->attach['ctype'], $this->attach['needrvrc'], false);
		}
		return true;
	}

	function saleCredit() {
		if ($this->ifadmin()) {
			return true;
		}
		$this->ifsale = 2;
		if ($this->ifBuyAtt()) {
			return true;
		}
		global $credit,$db_sellset,$creditName,$usercredit;
		require_once (R_P . 'require/credit.php');

		!$this->attach['ctype'] && $this->attach['ctype'] = 'money';
		$usercredit = $credit->get($this->uid, $this->attach['ctype']);
		$creditName = $credit->cType[$this->attach['ctype']];
		$db_sellset['price'] > 0 && $this->attach['needrvrc'] = min($this->attach['needrvrc'], $db_sellset['price']);
		$d = ($this->ifdown == 1 && $this->uploadcredit == $this->attach['ctype']) ? $this->downloadmoney : 0;
		if ($usercredit < $this->attach['needrvrc'] + $d) {
			$GLOBALS['needrvrc'] = $this->attach['needrvrc'];
			$GLOBALS['downloadmoney'] = $this->downloadmoney;
			return ($d) ? 'job_attach_sale_download' : 'job_attach_sale';
		}
		$this->ifsale = 1;
		return true;
	}

	function needCredit() {
		global $credit,$usercredit;
		require_once (R_P . 'require/credit.php');
		!$this->attach['ctype'] && $this->attach['ctype'] = 'rvrc';
		$usercredit = $credit->get($this->uid, $this->attach['ctype']);
		if ($usercredit < $this->attach['needrvrc']) {
			$GLOBALS['needrvrc'] = $this->attach['needrvrc'];
			$GLOBALS['creditName'] = $credit->cType[$this->attach['ctype']];
			$GLOBALS['downloadmoney'] = $this->downloadmoney;
			return ($this->downloadmoney > 0 && $this->uploadcredit == $this->attach['ctype']) ? 'job_attach_rvrc_download' : 'job_attach_rvrc';
		}
		return true;
	}

	function ifadmin() {
		if ($this->uid == $this->attach['uid'] || $this->admincheck) {
			return true;
		}
		return false;
	}

	function checkNeedCredit() {
		if ($this->attach['needrvrc'] < 1 || $this->ifadmin()) {
			return true;
		}
		if (!$this->uid) {
			return 'job_attach_special';
		}
		if ($this->attach['special'] == '2') {
			return $this->saleCredit();
		} else {
			return $this->needCredit();
		}
	}

	function check() {
		global $attach_url,$db_ftpweb,$attachdir;
		if (empty($this->attach)) {
			return 'job_attach_error';
		}
		if (empty($this->attach['attachurl']) || strpos($this->attach['attachurl'], '..') !== false) {
			return 'job_attach_error';
		}
		if (!$attach_url && !$db_ftpweb && !is_readable("$attachdir/" . $this->attach['attachurl'])) {
			return 'job_attach_error';
		}
		$this->url = geturl($this->attach['attachurl']);
		if (!$this->url[0]) {
			return 'job_attach_error';
		}
		if (($return = $this->_checkForum()) !== true) {
			return $return;
		}
		if (($return = $this->checkDownloadCredit()) !== true) {
			return $return;
		}
		if (($return = $this->checkNeedCredit()) !== true) {
			return $return;
		}
		return true;
	}

	function _checkForum() {
		$this->tid = $this->attach['tid'];
		$thread = $this->_db->get_one("SELECT fid,tpcstatus,ifcheck FROM pw_threads WHERE tid=" . S::sqlEscape($this->tid, false));

		if (getstatus($thread['tpcstatus'], 1) && !$thread['fid'] && $thread['ifcheck'] == '2') {
			return true;
		}
		L::loadClass('forum', 'forum', false);
		$pwforum = new PwForum($thread['fid']);
		if (!$pwforum->isForum()) {
			return 'data_error';
		}
		$pwforum->forumcheck($this->user, $this->groupid);
		$this->foruminfo =& $pwforum->foruminfo;
		$this->admincheck = ($this->groupid == '3' || $pwforum->isBM($this->username)) ? 1 : 0;
		
		$forumset = $this->foruminfo['forumset'];
		list($this->uploadcredit, , $this->downloadmoney, ) = explode("\t", $forumset['uploadset']);

		if (!$this->admincheck && !$pwforum->allowdownload($this->user, $this->groupid)) { //版块权限判断
			return 'job_attach_forum';
		}
		if ($this->groupid=='guest' && $this->_G['allowdownload'] == 0)  return 'download_not_login';
		if (!$this->foruminfo['allowdownload'] && $this->_G['allowdownload'] == 0 && !$this->admincheck) { //用户组权限判断
			return 'job_attach_group';
		}
		return true;
	}
}