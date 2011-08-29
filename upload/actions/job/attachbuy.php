<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('aid','type','step'));
!$aid && Showmsg('undefined_action');

//TODO lmq待优化
if ($type == 'record') {
	
	require_once(R_P.'require/credit.php');
	S::gp(array('page'), '', 2);
	$page < 1 && $page = 1;
	$db_perpage = 10;
	$total = $db->get_value("SELECT COUNT(*) AS sum FROM pw_attachbuy WHERE aid=" . S::sqlEscape($aid));
	$pages = numofpage($total, $page, ceil($total/$db_perpage), "job.php?action=attachbuy&type=record&aid=$aid&", null, 'ajaxurl');
	$buy = array();
	$query = $db->query("SELECT a.*,m.username FROM pw_attachbuy a LEFT JOIN pw_members m ON a.uid = m.uid WHERE a.aid=" . S::sqlEscape($aid) . S::sqlLimit(($page - 1) * $db_perpage, $db_perpage));
	while ($rt = $db->fetch_array($query)) {
		$rt['createdtime'] = get_date($rt['createdtime'],'Y-m-d H:i:s');
		$rt['ctype'] = $credit->cType[$rt['ctype']];
		$buy[] = $rt;
	}
	require_once PrintEot('ajax');
	ajax_footer();
	
} elseif ($type == 'download') {

	require_once (R_P . 'require/credit.php');
	$downloadServer = new threadDownload();
	$downloadServer->init($aid);
	if (($return = $downloadServer->check()) !== true) {
		list($msg) = array($return);
		require_once PrintEot('ajax');
		ajax_footer();
	}
	
	if($step == 2) {
		
		$downloadServer->execute();
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
		
	} else {
		if (($return = $downloadServer->downloadCredit()) !== true) {
			$list = array();
			list($msg,$creditdata) = $return;
			require_once PrintEot('ajax');
			ajax_footer();
		}
		
		list($msg,$creditdata) = $downloadServer->checkAttachCredit();
		require_once PrintEot('ajax');
		ajax_footer();
	}

}

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

	function threadDownload() {
		global $db,$winddb,$groupid,$windid,$winduid,$_G,$timestamp;
		$this->_db =& $db;
		
		$this->_G =& $_G;
		$this->uid =& $winduid;
		$this->username =& $windid;
		$this->user =& $winddb;
		$this->groupid =& $groupid;
		$this->timestamp =& $timestamp;
		$this->foruminfo = array();
		$this->alreadyBuy = false;
	}

	function init($aid) {
		$this->aid = $aid;
		$this->_attachDB = L::loadDB('attachs', 'forum');
		$this->attach = $this->_attachDB->get($aid);
	}
	
	function execute() {
		
		if (($return = $this->downloadCreditPass()) !== true) {
			return $return;
		}
		
		if (($return = $this->buyAttachPass()) !== true) {
			return $return;
		}
		global $credit;
		if (isset($credit) && $credit->setUser && !$this->ifadmin()) {
			$credit->runsql();
		}
		$this->_attachDB->increaseField($this->aid, 'hits');

		return true;
	}

	function downloadCredit() {		
		if ($this->_G['allowdownload'] != 1 || $this->ifadmin()) {
			return true;
		}
		
		if ($this->ifBuyAtt()) {
			return array('payment',array());
		}
		
		if ($this->ifadmin()) {
			return array('ifadmin',array());
		} 
		
		global $uploadcredit,$downloadmoney;
		$forumset = $this->foruminfo['forumset'];
		list($uploadcredit, , $downloadmoney, ) = explode("\t", $forumset['uploadset']);
		if ($downloadmoney) {
			global $credit;
			$usercredit = $credit->get($this->uid, $uploadcredit);
			$creditname = $credit->cType[$uploadcredit];

			global $db_sellset;
			!$this->attach['ctype'] && $this->attach['ctype'] = 'money';
			$attachCreditName = $credit->cType[$this->attach['ctype']];
			$db_sellset['price'] > 0 && $this->attach['needrvrc'] = min($this->attach['needrvrc'], $db_sellset['price']);
			$attachNeedrvrc = $this->attach['needrvrc'];
				
			if ($downloadmoney > 0 && $usercredit < $downloadmoney) {
				return array('forumnotenough',array('needrvrc'=>$attachNeedrvrc.$attachCreditName,'downloadmoney'=>$GLOBALS[downloadmoney].$creditname,'usercredit'=>$usercredit.$creditname));
			}
			
			if ($attachCreditName == $creditname) {
				$totalMoney = $this->attach['needrvrc'] + $GLOBALS['downloadmoney'];
				if ($usercredit < $totalMoney) {
					return array('forumnotenough',array(
						'needrvrc'=>$attachNeedrvrc.$attachCreditName,'downloadmoney'=>$GLOBALS['downloadmoney'].$creditname, 'usercredit'=>$usercredit.$creditname,'totalMoney'=>$totalMoney.$creditname));
				}
				$totalMoney  = $totalMoney.$creditname;
			} else {
				$needusercredit = $credit->get($this->uid, $this->attach['ctype']).$attachCreditName;
				$totalMoney = 0;
			}
			
			$usercredit = $usercredit.$creditname. " ". $needusercredit;
			return array('forumpass',array(
						'needrvrc'=>$attachNeedrvrc.$attachCreditName,'downloadmoney'=>$GLOBALS['downloadmoney'].$creditname, 'usercredit'=>$usercredit,'totalMoney'=>$totalMoney));
		}
		return true;
	}

	function ifBuyAtt() {
		return $this->_db->get_one("SELECT uid FROM pw_attachbuy WHERE aid=" . S::sqlEscape($this->aid) . " AND uid=" . S::sqlEscape($this->uid));
	}

	function ifadmin() {
		if ($this->uid == $this->attach['uid'] || $this->admincheck) {
			return true;
		}
	}
	
	function buyAttach() {
		if ($this->ifBuyAtt()) {
			return array('payment',array());
		}

		if ($this->ifadmin()) {
			return array('ifadmin',array());
		} 
		
		global $credit,$db_sellset,$uploadcredit,$downloadmoney;
		
		!$this->attach['ctype'] && $this->attach['ctype'] = 'money';
		$usercredit = $credit->get($this->uid, $this->attach['ctype']);
		$creditName = $credit->cType[$this->attach['ctype']];
		$db_sellset['price'] > 0 && $this->attach['needrvrc'] = min($this->attach['needrvrc'], $db_sellset['price']);
		$needrvrc = $this->attach['needrvrc'];
		if ($usercredit < $this->attach['needrvrc']) {
			$action = 'attachbuy';
			$type = "download";
			return array('notenough',array('needrvrc'=>$needrvrc.$creditName, 'usercredit'=>$usercredit.$creditName));
		}
		
		return array('pass',array('needrvrc'=>$needrvrc.$creditName, 'usercredit'=>$usercredit.$creditName));
		
	}

	function buyAttachPass() {
		if ($this->ifBuyAtt() || $this->ifadmin()) {
			return true;
		}
		global $credit;
		$this->_db->update("INSERT INTO pw_attachbuy SET " . S::sqlSingle(array(
			'aid' => $this->aid,
			'uid' => $this->uid,
			'ctype' => $this->attach['ctype'],
			'cost' => $this->attach['needrvrc'],
			'createdtime' => $this->timestamp
		)));
		$credit->addLog('topic_attbuy',
			array($this->attach['ctype'] => -$this->attach['needrvrc']),
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
	
	function downloadCreditPass() {		
		if ($this->_G['allowdownload'] != 1 || $this->ifadmin()) {
			return true;
		}
		global $uploadcredit,$downloadmoney;
		$forumset = $this->foruminfo['forumset'];
		list($uploadcredit, , $downloadmoney, ) = explode("\t", $forumset['uploadset']);
		if ($downloadmoney) {
			global $credit;
			$credit->addLog('topic_download',
				array($uploadcredit => -$downloadmoney),
				array(
					'uid' => $this->uid,
					'username' => $this->username,
					'ip' => $GLOBALS['onlineip'],
					'fname' => $this->foruminfo['name']
				)
			);
			if (!$credit->set($this->uid, $uploadcredit, -$downloadmoney, false)) {
				return array('undefined_action',array());
			}
		}
		return true;
	}
	
	function needCredit() {
		global $credit,$uploadcredit,$downloadmoney;
		!$this->attach['ctype'] && $this->attach['ctype'] = 'rvrc';
		$usercredit = $credit->get($this->uid, $this->attach['ctype']);
		if ($usercredit < $this->attach['needrvrc']) {
			global $usercredit;
			$GLOBALS['needrvrc'] = $this->attach['needrvrc'];
			$GLOBALS['creditName'] = $credit->cType[$this->attach['ctype']];
			return ($downloadmoney > 0 && $uploadcredit == $this->attach['ctype']) ? 'job_attach_rvrc_download' : 'job_attach_rvrc';
		}
		return true;
	}

	function checkAttachCredit() {
		if (!$this->uid) {
			return array('specialAttach',array());//本附件为特殊附件，游客不能下载！
		}
		if ($this->attach['special'] == '2') {
			return $this->buyAttach();
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
			return 'forum_purview_erro';//读取数据错误,原因：您要访问的链接无效,可能链接不完整,或数据已被删除!
		}
		
		if ($pwforum->foruminfo['f_type'] == 'former' && $this->groupid == 'guest' && $_COOKIE) {
			return 'forum_purview_erro';//本版块为正规版块,只有注册会员才能进入!
		}
		if (!empty($pwforum->foruminfo['style']) && file_exists(D_P . "data/style/{$pwforum->foruminfo[style]}.php")) {
			$GLOBALS['skin'] = $pwforum->foruminfo['style'];
		}
		$pwdcheck = GetCookie('pwdcheck');
		
		if ($pwforum->foruminfo['password'] != '' && ($groupid == 'guest' || $pwdcheck[$pwforum->fid] != $pwforum->foruminfo['password'] && !S::inArray($this->user['username'], $GLOBALS['manager']))) {
			require_once (R_P . 'require/forumpw.php');
		}
		if (!$pwforum->allowvisit($this->user, $this->groupid)) {
			return 'forum_purview_erro';//对不起,本版块为认证版块,您没有权限查看此版块的内容!
		}
		if (!$pwforum->foruminfo['cms'] && $pwforum->foruminfo['f_type'] == 'hidden' && !$pwforum->foruminfo['allowvisit']) {
			return 'forum_purview_erro';//本版块为隐藏版块,您无权进入!
		}
		
		$this->foruminfo =& $pwforum->foruminfo;
		$this->admincheck = ($this->groupid == '3' || $pwforum->isBM($this->username)) ? 1 : 0;

		if (!$this->admincheck && !$pwforum->allowdownload($this->user, $this->groupid)) { //版块权限判断
			return 'forum_purview_erro';//对不起，本版块只有特定用户可以下载附件，请返回
		}
		if (!$this->foruminfo['allowdownload'] && $this->_G['allowdownload'] == 0 && !$this->admincheck) { //用户组权限判断
			return 'forum_purview_erro';//用户组权限：你所属的用户组没有下载附件的权限
		}
		return true;
	}
}