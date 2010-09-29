<?php
!defined('P_W') && exit('Forbidden');

PostCheck();
InitGP(array(
	'aid',
	'type',
	'page'
));
empty($aid) && Showmsg('job_attach_error');

$delfileServer = getDelfileFactory($type);
$delfileServer->init($aid);
if (($return = $delfileServer->execute()) !== true) {
	Showmsg($return);
}
echo 'success';
ajax_footer();

function getDelfileFactory($type) {
	switch ($type) {
		case 'active':
			$obj = new activeDelfile();break;
		/*
		case 'message':
			$obj = new messageDelfile();break;
		*/
		default:
			$obj = new threadDelfile();
	}
	return $obj;
}

class delfileInterface {

	var $attach;

	function init() {
	}
	
	function execute() {
		return 'job_attach_error';
	}

	function delfile($url) {
		global $db_ifftp,$ftp;
		pwDelatt($url, $db_ifftp);
		pwFtpClose($ftp);
	}
}

class activeDelfile extends delfileInterface {
	
	var $_db;
	var $aid;
	var $isGM;
	var $groupid;
	var $uid;

	function activeDelfile() {
		global $db, $winduid, $windid, $manager, $groupid;
		$this->_db =& $db;
		$this->uid = $winduid;
		$this->groupid = $groupid;
		$this->isGM = CkInArray($windid, $manager);
	}

	function init($aid) {
		$this->aid = $aid;
		$this->attach = $this->_db->get_one("SELECT * FROM pw_actattachs WHERE aid=" . pwEscape($aid));
	}
	
	function execute() {
		if (($return = $this->_check()) !== true) {
			return $return;
		}
		$this->_del();
		return true;
	}

	function _check () {
		if (empty($this->attach)) {
			return 'job_attach_error';
		}
		if ($this->groupid == 'guest') {
			return 'job_attach_right';
		}
		if (empty($this->attach['attachurl']) || strpos($this->attach['attachurl'], '..') !== false) {
			return 'job_attach_error';
		}
		if ($this->attach['uid'] != $this->uid && !$this->isGM && !pwRights(false, 'delattach')) {
			return 'job_attach_right';
		}
		return true;
	}

	function _del() {
		$this->delfile($this->attach['attachurl']);
		$this->_db->update("DELETE FROM pw_actattachs WHERE aid=" . pwEscape($this->aid));
	}
}

class threadDelfile extends delfileInterface {
	
	var $_db;
	var $aid;
	var $tid;

	var $user;
	var $groupid;
	var $uid;
	var $username;
	var $_G;
	var $isGM;

	var $admincheck;
	var $foruminfo;

	function threadDelfile() {
		global $db,$winddb,$groupid,$windid,$winduid,$_G,$manager;
		$this->_db =& $db;
		
		$this->_G =& $_G;
		$this->uid =& $winduid;
		$this->username =& $windid;
		$this->user =& $winddb;
		$this->groupid =& $groupid;
		$this->isGM = CkInArray($this->username, $manager);

		$this->foruminfo = array();
	}
	
	function init($aid) {
		$this->aid = $aid;
		$this->attach = $this->_db->get_one("SELECT * FROM pw_attachs WHERE aid=" . pwEscape($aid));
	}

	function execute() {
		if (($return = $this->_check()) !== true) {
			return $return;
		}
		$this->_del();
		return true;
	}

	function _check () {
		if (empty($this->attach)) {
			return 'job_attach_error';
		}
		if ($this->groupid == 'guest') {
			return 'job_attach_right';
		}
		if (empty($this->attach['attachurl']) || strpos($this->attach['attachurl'], '..') !== false) {
			return 'job_attach_error';
		}
		if (($return = $this->_checkAllow()) !== true) {
			return $return;
		}
		if (!$this->admincheck && $this->attach['uid'] != $this->uid) {
			return 'job_attach_right';
		}
		return true;
	}

	function _checkAllow() {
		$this->tid = $this->attach['tid'];
		$thread = $this->_db->get_one("SELECT fid,tpcstatus,ifcheck FROM pw_threads WHERE tid=" . pwEscape($this->tid, false));

		if (getstatus($thread['tpcstatus'], 1) && !$thread['fid'] && $thread['ifcheck'] == '2') {
			return $this->_checkColony($thread['fid']);
		} else {
			return $this->_checkForum($thread['fid']);
		}
	}

	function _checkColony() {
		$this->admincheck = ($this->isGM || pwRights(false, 'delattach', $fid)) ? 1 : 0;
		return true;
	}

	function _checkForum($fid) {
		L::loadClass('forum', 'forum', false);
		$pwforum = new PwForum($fid);
		if (!$pwforum->isForum()) {
			return 'data_error';
		}
		$pwforum->forumcheck($this->user, $this->groupid);
		$this->foruminfo =& $pwforum->foruminfo;

		$isBM = $pwforum->isBM($this->username);
		$this->admincheck = ($this->isGM || pwRights($isBM, 'delattach', $fid)) ? 1 : 0;
		return true;
	}

	function _del() {
		$this->delfile($this->attach['attachurl']);	
		$this->_db->update("DELETE FROM pw_attachs WHERE aid=" . pwEscape($this->aid));

		require_once(R_P . 'require/updateforum.php');
		$ifupload = getattachtype($this->tid);
		$ifaid = $ifupload === false ? 0 : 1;
		$updateArr = array('aid' =>$ifaid);
		if ($this->attach['pid']) {
			$pw_posts = GetPtable('N', $this->tid);
			$content = $this->_db->get_value("SELECT content FROM $pw_posts WHERE tid=" . pwEscape($this->tid, false) . "AND pid=" . pwEscape($this->attach['pid'], false));
			if (($content = $this->parseAttContent($content)) !== false) {
				$updateArr['content'] = $content;
				$updateThreadCache = TRUE;
			}
			$this->_db->update("UPDATE $pw_posts SET " . pwSqlSingle($updateArr) . " WHERE tid=" . pwEscape($this->tid, false) . "AND pid=" . pwEscape($this->attach['pid'], false));
		} else {
			$pw_tmsgs = GetTtable($this->tid);
			$content = $this->_db->get_value("SELECT content FROM $pw_tmsgs WHERE tid=" . pwEscape($this->tid, false));
			if (($content = $this->parseAttContent($content)) !== false) {
				$updateArr['content'] = $content;
				$updateThreadCache = TRUE;
			}
			$this->_db->update("UPDATE $pw_tmsgs SET " . pwSqlSingle($updateArr) . " WHERE tid=" . pwEscape($this->tid, false));
		}
		
		if ($updateThreadCache) {
			$threadService = L::loadClass("threads", 'forum'); /* @var $threadService PW_Threads */ 
			$threadService->clearTmsgsByThreadId($this->tid);
		}
		
		$ifupload = (int) $ifupload;
		$this->_db->update('UPDATE pw_threads SET ifupload=' . pwEscape($ifupload) . ' WHERE tid=' . pwEscape($this->tid));
		if ($this->foruminfo['allowhtm'] && $GLOBALS['page'] == 1) {
			$StaticPage = L::loadClass('StaticPage');
			$StaticPage->update($this->tid);
		}
	}
	
	function parseAttContent($content) {
		if (strpos($content,"[attachment={$this->aid}]") === false) {
			return false;
		}
		return str_replace("[attachment={$this->aid}]",'',$content);
	}
}