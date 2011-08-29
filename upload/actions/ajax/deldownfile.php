<?php
!defined('P_W') && exit('Forbidden');

PostCheck();
S::gp(array(
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
	if ($type == 'active') {
		return new activeDelfile();
	}
	if ($type == 'cms') {
		return new cmsDelfile();
	}
	if ($type == 'diary') {
		return new diaryDelfile();
	}
	if ($type && file_exists(R_P . "require/extents/attach/{$type}Delfile.class.php")) {
		$class = $type . 'Delfile';
		require_once S::escapePath(R_P . "require/extents/attach/{$type}Delfile.class.php");
		return new $class();
	}
	return new threadDelfile();
}

class delfileInterface {

	var $attach;

	function init() {
	}
	
	function execute() {
		return 'job_attach_error';
	}

	function delfile($url, $ifthumb = 0) {
		global $db_ifftp,$ftp;
		pwDelThreadAtt($url, $db_ifftp, $ifthumb);
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
		$this->isGM = S::inArray($windid, $manager);
	}

	function init($aid) {
		$this->aid = $aid;
		$this->attach = $this->_db->get_one("SELECT * FROM pw_actattachs WHERE aid=" . S::sqlEscape($aid));
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
		$this->delfile($this->attach['attachurl'], $this->attach['ifthumb']);
		$this->_db->update("DELETE FROM pw_actattachs WHERE aid=" . S::sqlEscape($this->aid));
	}
}

class cmsDelfile extends delfileInterface {

	var $_db;
	var $aid;
	var $isGM;
	var $groupid;
	var $uid;

	function cmsDelfile() {
		global $db, $winduid, $windid, $manager, $groupid;
		$this->_db =& $db;
		$this->uid = $winduid;
		$this->groupid = $groupid;
		$this->isGM = S::inArray($windid, $manager);
	}
	
	function init($aid) {
		$this->aid = $aid;
		$this->attach = $this->_db->get_one("SELECT * FROM pw_cms_attach WHERE attach_id=" . S::sqlEscape($aid));
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
		if (empty($this->attach['attachurl']) || strpos($this->attach['attachurl'], '..') !== false) {
			return 'job_attach_error';
		}
		$article = $this->_db->get_one("SELECT userid FROM pw_cms_article WHERE article_id=" . S::sqlEscape($this->attach['article_id']));

		if ($article['userid'] != $this->uid && !$this->isGM && !$GLOBALS['SYSTEM']['delattach']) {
			return 'job_attach_right';
		}
		return true;
	}

	function _del() {
		$this->delfile($this->attach['attachurl'], $this->attach['ifthumb']);
		$this->_db->update("DELETE FROM pw_cms_attach WHERE attach_id=" . S::sqlEscape($this->aid) . " LIMIT 1");
		return true;
	}
}

class diaryDelfile extends delfileInterface {

	var $_db;
	var $aid;
	var $isGM;
	var $groupid;
	var $uid;
	var $attachsDB;

	function diaryDelfile() {
		global $db, $winduid, $windid, $manager, $groupid;
		$this->_db =& $db;
		$this->uid = $winduid;
		$this->groupid = $groupid;
		$this->isGM = S::inArray($windid, $manager);
		$this->attachsDB = L::loadDB('attachs', 'forum');
	}
	
	function init($aid) {
		$this->aid = $aid;
		$this->attach = $this->attachsDB->get($aid);
	}

	function execute() {
		if (($return = $this->_check()) !== true) {
			return $return;
		}
		$this->_del();
		return true;
	}

	function _check () {
		if (empty($this->attach) || empty($this->attach['did'])) {
			return 'job_attach_error';
		}
		if (empty($this->attach['attachurl']) || strpos($this->attach['attachurl'], '..') !== false) {
			return 'job_attach_error';
		}
		if ($this->attach['uid'] != $this->uid && !$this->isGM && !$GLOBALS['SYSTEM']['delattach']) {
			return 'job_attach_right';
		}
		return true;
	}

	function _del() {
		$this->delfile($this->attach['attachurl'], $this->attach['ifthumb']);
		$this->attachsDB->delete($this->aid);
		
		$diaryService = L::loadClass('Diary', 'diary'); /* @var $diaryService PW_Diary */
		$diary = $diaryService->get($this->attach['did']);
		$attachs = unserialize($diary['aid']);

		if (is_array($attachs)) {
			unset($attachs[$this->aid]);
			$attachs = $attachs ? serialize($attachs) : '';
		    pwQuery::update('pw_diary','did =:did' , array($this->attach['did']), array('aid'=> $attachs));
		}
		return true;
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
	var $attachsDB;

	function threadDelfile() {
		global $db,$winddb,$groupid,$windid,$winduid,$_G,$manager;
		$this->_db =& $db;
		
		$this->_G =& $_G;
		$this->uid =& $winduid;
		$this->username =& $windid;
		$this->user =& $winddb;
		$this->groupid =& $groupid;
		$this->isGM = S::inArray($this->username, $manager);
		
		$this->attachsDB = L::loadDB('attachs', 'forum');
		$this->foruminfo = array();
	}
	
	function init($aid) {
		$this->aid = $aid;
		$this->attach = $this->attachsDB->get($aid);
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
		$thread = $this->_db->get_one("SELECT fid,tpcstatus,ifcheck FROM pw_threads WHERE tid=" . S::sqlEscape($this->tid, false));

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
		$this->delfile($this->attach['attachurl'], $this->attach['ifthumb']);
		$this->attachsDB->delete($this->aid);

		require_once(R_P . 'require/updateforum.php');
		$ifupload = getattachtype($this->tid);
		$ifaid = $ifupload === false ? 0 : 1;
		$updateArr = array('aid' => $ifaid);
		if ($this->attach['pid']) {
			$pw_posts = GetPtable('N', $this->tid);
			$content = $this->_db->get_value("SELECT content FROM $pw_posts WHERE tid=" . S::sqlEscape($this->tid, false) . "AND pid=" . S::sqlEscape($this->attach['pid'], false));
			if (($content = $this->parseAttContent($content)) !== false) {
				$updateArr['content'] = $content;
				$updateThreadCache = TRUE;
			}
			//$this->_db->update("UPDATE $pw_posts SET " . S::sqlSingle($updateArr) . " WHERE tid=" . S::sqlEscape($this->tid, false) . "AND pid=" . S::sqlEscape($this->attach['pid'], false));
			pwQuery::update($pw_posts, 'tid=:tid AND pid=:pid', array($this->tid, $this->attach['pid']), $updateArr);
		} else {
			$pw_tmsgs = GetTtable($this->tid);
			$content = $this->_db->get_value("SELECT content FROM $pw_tmsgs WHERE tid=" . S::sqlEscape($this->tid, false));
			if (($content = $this->parseAttContent($content)) !== false) {
				$updateArr['content'] = $content;
				$updateThreadCache = TRUE;
			}
			//* $this->_db->update("UPDATE $pw_tmsgs SET " . S::sqlSingle($updateArr) . " WHERE tid=" . S::sqlEscape($this->tid, false));
			pwQuery::update($pw_tmsgs, 'tid=:tid', array($this->tid), $updateArr);
			
		}
		if ($this->attach['type'] == 'img') {
			$tucoolService = L::loadClass('tucool','forum');
			$tucoolService->updateTucoolImageNum($this->tid);
			$tucoolInfo = $tucoolService->get($this->tid);
			if ($this->attach['attachurl'] == $tucoolInfo['cover']) {
				$attachService = L::loadClass('attachs', 'forum'); /* @var $attachService PW_Attachs */
				$coverInfo = $attachService->getLatestAttachInfoByTidType($this->tid);
				$tucoolService->setCover($this->tid, $coverInfo['attachurl'],$coverInfo['ifthumb']);
			}
		}
		if ($updateThreadCache) {
			//* $threadService = L::loadClass("threads", 'forum'); /* @var $threadService PW_Threads */ 
			//* $threadService->clearTmsgsByThreadId($this->tid);
			Perf::gatherInfo('changeThreadWithThreadIds', array('tid'=>$this->tid));
		}
		
		$ifupload = (int) $ifupload;
		//$this->_db->update('UPDATE pw_threads SET ifupload=' . S::sqlEscape($ifupload) . ' WHERE tid=' . S::sqlEscape($this->tid));
		pwQuery::update('pw_threads', "tid=:tid", array($this->tid), array("ifupload"=>$ifupload));
		
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