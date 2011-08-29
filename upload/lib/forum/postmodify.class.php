<?php
!defined('P_W') && exit('Forbidden');

/**
 * 编辑帖子
 * 
 * @package Thread
 */
class postModify {
	
	var $db;
	var $post;
	var $forum;
	var $postdata = null;
	
	var $type;
	var $data;
	var $att;
	
	var $tid;
	var $pid;
	
	var $atcdb = array();
	var $oldattach = array();
	//var $delattach = array();
	var $alterattach = array();
	var $replacedb = array();

	var $newImgNum;
	var $oldImgNum;
	var $extraBehavior = null;
	
	function postModify($tid, $pid, &$post) {
		global $db;
		$this->tid = $tid;
		$this->pid = $pid;
		
		$this->db = & $db;
		$this->post = & $post;
		$this->forum = & $post->forum;
		$this->newImgNum = $this->oldImgNum = 0;
	}
	
	function init() {
		$this->atcdb = $this->getData();
		if ($this->atcdb['aid']) {
			$pw_attachs = L::loadDB('attachs', 'forum');
			$this->atcdb['attachs'] = $pw_attachs->getByTid($this->tid, $this->pid);
		} else {
			$this->atcdb['attachs'] = array();
		}
		$this->setBehavior();
		return $this->atcdb;
	}

	function check() {
		global $timestamp;
		if (empty($this->atcdb) || $this->atcdb['fid'] != $this->forum->fid) {
			return $this->post->showmsg('illegal_tid');
		}
		if (!$this->post->isGM && !pwRights($this->post->isBM, 'deltpcs')) {
			if (!$this->modifyRight()) {
				return $this->post->showmsg('modify_noper');
			}
			if ($this->atcdb['locked'] % 3 > 0 && !$this->modifyLockedRight()) {
				return $this->post->showmsg('modify_locked');
			}
		}
		if ($this->post->uid != $this->atcdb['authorid']) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$authordb = $userService->get($this->atcdb['authorid']);
			/**Begin modify by liaohu*/
			$pce_arr = explode(",",$GLOBALS['SYSTEM']['tcanedit']);
			if (($authordb['groupid'] == 3 || $authordb['groupid'] == 4 || $authordb['groupid'] == 5) && !in_array($authordb['groupid'],$pce_arr)) {
				return $this->post->showmsg('modify_admin');
			}
			/**End modify by liaohu*/
		}
		//版块编辑时间限制
		L::loadClass('forum', 'forum', false);
		global $postedittime,$windid,$winduid;
		//$pwforum = new PwForum($this->atcdb['fid']);
		$isBM = $this->forum->isBM($windid);
		$userSystemRight =  userSystemRight($windid, $isBM, 'deltpcs');
		$postedittime = $this->forum->foruminfo['forumset']['postedittime'];
		if (!$userSystemRight && $this->post->uid == $this->atcdb['authorid'] && $postedittime !== "" &&  $postedittime != 0 && ($timestamp - $this->atcdb['postdate']) >  $postedittime * 60) {
			return $this->post->showmsg('modify_forumtimelimit');
		}
		if ($this->post->uid == $this->atcdb['authorid'] && $this->post->_G['edittime'] && ($timestamp - $this->atcdb['postdate']) > $this->post->_G['edittime'] * 60) {
			return $this->post->showmsg('modify_timelimit'); 
		}
	}
	
	
	function modifyLockedRight() {
		if ($this->extraBehavior && $this->extraBehavior->modifyLockedRight()) {
			return true;
		}
		return false;
	}

	function modifyRight() {
		if ($this->atcdb['authorid'] == $this->post->uid) {
			return true;
		}
		if ($this->extraBehavior && $this->extraBehavior->modifyRight()) {
			return true;
		}
		return false;
	}
	
	function setBehavior() {
	}
	
	function setModifyPostData($postdata) {
		return $postdata;
	}
	
	function hasAtt() {
		return !empty($this->atcdb['attachs']);
	}
	
	function initAttachs(/*$keep, */$oldatt_special, $oldatt_needrvrc, $oldatt_ctype, $oldatt_desc) {
		global $db_enhideset, $db_sellset, $db_attachnum;
		//$keep = (array) $keep;
		is_array($oldatt_special) || $oldatt_special = array();
		is_array($oldatt_needrvrc) || $oldatt_needrvrc = array();
		is_array($oldatt_ctype) || $oldatt_ctype = array();
		is_array($oldatt_desc) || $oldatt_desc = array();

		$oldattach = $this->atcdb['attachs'];
		foreach ($oldattach as $key => $value) {
			$isImg = ($value['type'] == 'img');
			/*
			if (!in_array($key, $keep)) {
				$this->delattach[$key] = $value;
			} else {
				*/
				$v = array(
					'special' => isset($oldatt_special[$key]) ? $oldatt_special[$key] : $value['special'],
					'ctype' => isset($oldatt_ctype[$key]) ? $oldatt_ctype[$key] : $value['ctype'],
					'needrvrc' => isset($oldatt_needrvrc[$key]) ? $oldatt_needrvrc[$key] : $value['needrvrc'],
					'desc' => isset($oldatt_desc[$key]) ? $oldatt_desc[$key] : $value['descrip']
				);
				if ($v['needrvrc'] > 0 && ($v['special'] == 1 && $this->post->allowencode && in_array($v['ctype'], $db_enhideset['type']) || $v['special'] == 2 && $this->post->allowsell && in_array($v['ctype'], $db_sellset['type']))) {
				
				} else {
					$v['needrvrc'] = $v['special'] = 0;
					$v['ctype'] = '';
				}
				$oldattach[$key] = array_merge($oldattach[$key], $v);
				
				if (array_key_exists('replace_' . $key, $_FILES)) {
					$db_attachnum++;
					$this->replacedb[$key] = $oldattach[$key];
				} else {
					if ($value['needrvrc'] != $v['needrvrc']
						|| $value['special'] != $v['special']
						|| $value['ctype'] != $v['ctype']
						|| $value['descrip'] != $v['desc']) {
						$this->alterattach[$key] = $v;
					}
					$isImg && $this->newImgNum++;
				}
				$this->oldattach[$key] = $oldattach[$key];
			//}
			$isImg && $this->oldImgNum++;
		}
	}
	
	function alterinfo() {
		global $db_postedittime,$windid,$winduid,$manager,$groupid;
		L::loadClass('forum', 'forum', false);
		//$pwforum = new PwForum($this->atcdb['fid']);
		$postedittime = (int)$db_postedittime * 60;
		$isBM = $this->forum->isBM($windid);
		$userSystemRight =  userSystemRight($windid, $isBM, 'deltpcs');
		if(S::inArray($windid, $manager) || $groupid == 3){
			$alterinfo = '';
		}elseif ($this->post->uid == $this->atcdb['authorid'] && $db_postedittime != 0 && ($this->atcdb['postdate'] + $postedittime < $GLOBALS['timestamp'])) {
			global $altername, $db_anonymousname, $timeofedit, $timestamp;
			$altername = ($this->data['anonymous'] && $this->post->uid == $this->atcdb['authorid']) ? $db_anonymousname : $this->post->username;
			$timeofedit = get_date($timestamp);
			$alterinfo = getLangInfo('post', 'edit_post');
			
		} elseif ($userSystemRight &&  $this->post->uid != $this->atcdb['authorid'] && $db_postedittime != 0 && ($this->atcdb['postdate'] + $postedittime < $GLOBALS['timestamp'])) {
			global $altername, $db_anonymousname, $timeofedit, $timestamp;
			$altername = ($this->data['anonymous'] && $this->post->uid == $this->atcdb['authorid']) ? $db_anonymousname : $this->post->username;
			$timeofedit = get_date($timestamp);
			$alterinfo = getLangInfo('post', 'edit_post');	
		} else {
			$alterinfo = '';
		}
		return $alterinfo;
	}
	
	function setPostData(&$postdata) {
		$this->postdata = & $postdata;
		$this->att = & $postdata->att;
		$this->data = $postdata->getData();
		if ($this->extraBehavior) {
			$this->data = $this->setModifyPostData($this->data);
		}

		if (is_object($postdata->tag)) {
			if ($this->data['tags'] != $this->atcdb['tags']) {
				$postdata->tag->update($this->tid);
			}
			$this->data['tags'] .= "\t" . $postdata->tag->relate($this->data['title'], $this->data['content']);
		}
		if ($this->att) {
			global $credit, $timestamp;
			if (is_object($credit)) {
				$credit->runsql();
			}
			
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$pwSQL = array(
				'uploadtime' => $timestamp,
				'uploadnum' => $this->post->user['uploadnum']
			);
			$userService->update($this->post->uid, array(), $pwSQL);
		}
		$this->oldattach && $this->data['aid'] += count($this->oldattach);
		$this->data['alterinfo'] = $this->alterinfo();
		if (!$this->data['anonymous']) {
			$this->data['lastposter'] = $this->atcdb['author'];
		}
	}
	
	function execute(&$postdata) {
		global $credit;
		$this->setPostData($postdata);
		$this->update();
		$this->updateAtt();
		$this->editlog();
		$this->afterModify();
		$this->editFilter();
		is_object($credit) && $credit->runsql();
	}
	
	function editFilter() {
		if ($this->postdata->filter->filter_weight > 1) {
			$this->postdata->filter->insert($this->tid, $this->pid, implode(',', $this->postdata->filter->filter_word), $this->postdata->filter->filter_weight);
		} elseif (!$this->atcdb['ifcheck'] || !$this->atcdb['ifwordsfb']) {
			$this->postdata->filter->delete($this->tid, $this->pid);
		}
	}
	
	function updateAtt() {
		/*
		if ($this->delattach) {
			require_once (R_P . 'require/functions.php');
			require_once (R_P . 'require/updateforum.php');
			delete_att($this->delattach);
			pwFtpClose($GLOBALS['ftp']);
		}
		*/
		if ($this->alterattach) {
			$pw_attachs = L::loadDB('attachs', 'forum');
			foreach ($this->alterattach as $aid => $v) {
				$pw_attachs->updateById($aid, array(
					'needrvrc' => $v['needrvrc'],
					'descrip' => $v['desc'],
					'special' => $v['special'],
					'ctype' => $v['ctype']
				));
			}
		}
		if (is_object($this->att) && ($aids = $this->att->getAids())) {
			$pw_attachs = L::loadDB('attachs', 'forum');
			$pw_attachs->updateById($aids, array(
				'tid' => $this->tid,
				'pid' => $this->pid
			));
		}
		$this->updateImgAtt();
	}
	
	function editlog() {
		if ($this->post->uid != $this->atcdb['authorid'] && ($this->post->isGM || pwRights($this->post->isBM, 'deltpcs'))) {
			/**
			 * 管理员编辑帖子的安全日记
			 */
			global $timestamp, $onlineip;
			require_once (R_P . 'require/writelog.php');
			$log = array(
				'type' => 'edit',
				'username1' => $this->atcdb['author'],
				'username2' => $this->post->username,
				'field1' => $this->forum->fid,
				'field2' => '',
				'field3' => '',
				'descrip' => 'edit_descrip',
				'timestamp' => $timestamp,
				'ip' => $onlineip,
				'tid' => $this->tid,
				'forum' => $this->forum->name,
				'subject' => substrs($this->data['title'], 28),
				'reason' => 'edit article'
			);
			writelog($log);
		}
	}
}

class topicModify extends postModify {
	
	var $pw_tmsgs;
	
	function topicModify($tid, $pid, &$post) {
		parent::postModify($tid, $pid, $post);
		$this->type = 'topic';
		$this->pw_tmsgs = GetTtable($this->tid);
	}
	
	function getData() {
		return $this->db->get_one("SELECT t.*,tm.content,tm.aid,tm.ifsign,tm.tags,tm.ifwordsfb,tm.magic FROM pw_threads t LEFT JOIN $this->pw_tmsgs tm USING(tid) WHERE t.tid=" . S::sqlEscape($this->tid));
	}

	function setBehavior() {
		if ($this->extraBehavior !== null) {
			return;
		}
		if ($this->atcdb['tpcstatus'] && getstatus($this->atcdb['tpcstatus'], 1)) {
			$cyid = $this->db->get_value("SELECT cyid FROM pw_argument WHERE tid=" . S::sqlEscape($this->atcdb['tid']));
			if ($cyid) {
				require_once(R_P . 'apps/groups/lib/colonypost.class.php');
				$this->extraBehavior = new PwColonyPost($cyid);
			}
		}
	}

	function setModifyPostData($postdata) {
		if ($this->extraBehavior) {
			$postdata = $this->extraBehavior->setTopicModifyPostData($postdata);
		}
		return $postdata;
	}
	
	function resetData() {
		return array(
			'aid' => $this->atcdb['aid'],
			'ifsign' => $this->atcdb['ifsign'],
			'tags' => $this->atcdb['tags'],
			'content' => $this->atcdb['content'],
			'magic' => $this->atcdb['magic'],
			'icon' => $this->atcdb['icon'],
			'title' => $this->atcdb['subject'],
			'w_type' => $this->atcdb['type'],
			'ifupload' => $this->atcdb['ifupload'],
			'ifmail' => $this->atcdb['ifmail'],
			'anonymous' => $this->atcdb['anonymous'],
			'ifmagic' => $this->atcdb['ifmagic'],
			'hideatt' => $this->atcdb['ifhide'],
			'tpcstatus' => $this->atcdb['tpcstatus']
		);
	}
	
	function update() {
		$pwSQL = array(
			'aid' => $this->data['aid'],
			'ifsign' => $this->data['ifsign'],
			'alterinfo' => $this->data['alterinfo'],
			'tags' => $this->data['tags'],
			'ifconvert' => $this->data['convert'],
			'ifwordsfb' => $this->data['ifwordsfb'],
			'content' => $this->data['content'],
			'magic' => $this->data['magic']
		);
		if ($this->post->uid == $this->atcdb['authorid']) {
			global $onlineip;
			$ipTable = L::loadClass('IPTable', 'utility');
			$pwSQL['userip'] = $onlineip;
			$pwSQL['ipfrom'] = $ipTable->getIpFrom($onlineip);
		}
		//* $this->db->update("UPDATE $this->pw_tmsgs SET " . S::sqlSingle($pwSQL) . " WHERE tid=" . S::sqlEscape($this->tid));
		pwQuery::update($this->pw_tmsgs, 'tid=:tid', array($this->tid), $pwSQL);
		
		$pwSQL = array(
			'icon' => $this->data['icon'],
			'subject' => $this->data['title'],
			'type' => $this->data['w_type'],
			'ifupload' => $this->data['ifupload'],
			'ifmail' => $this->data['ifmail'],
			'anonymous' => $this->data['anonymous'],
			'ifmagic' => $this->data['ifmagic'],
			'ifhide' => $this->data['hideatt'],
			'ifcheck' => $this->data['ifcheck'],
			'tpcstatus' => $this->data['tpcstatus']
		);
		if ($this->data['anonymous'] != $this->atcdb['anonymous'] && $this->atcdb['postdate'] == $this->atcdb['lastpost']) {
			$pwSQL['lastposter'] = $this->data['lastposter'];
		}
		//$this->db->update("UPDATE pw_threads SET " . S::sqlSingle($pwSQL) . " WHERE tid=" . S::sqlEscape($this->tid));
		pwQuery::update('pw_threads', 'tid = :tid' , array($this->tid), $pwSQL);
		Perf::gatherInfo('changeThreadListWithThreadIds', array('tid'=>$this->tid));
	}
	
	function afterModify() {
		global $db_ifpwcache;
		
		if (($db_ifpwcache & 512) && $this->att && $this->att->elementpic) {
			$elementpic = $this->att->elementpic;
			L::loadClass('elementupdate', '', false);
			$elementupdate = new ElementUpdate($this->forum->fid);
			$elementupdate->newPicUpdate($elementpic['aid'], $this->forum->fid, $this->tid, $elementpic['attachurl'], '', $this->data['content']);
			$elementupdate->updateSQL();
		}
		//End elementupdate

		if ($this->data['ifcheck'] && $this->forum->foruminfo['allowhtm'] && !$this->forum->foruminfo['cms']) {
			$StaticPage = L::loadClass('StaticPage');
			$StaticPage->update($this->tid);
		}
		if ($this->data['ifcheck'] != $this->atcdb['ifcheck']) {
			$actions = $this->atcdb['ifcheck'] == 1 ? '-' : '+';
			$this->forum->lastinfo('topic', $actions);
			if ($this->extraBehavior) {
				$this->extraBehavior->topicModify($this->tid, $this->data);
			}
		}
		$this->updateForumsextra();
		
		//* $threads = L::loadClass('Threads', 'forum');
		//* $threads->delThreads($this->tid);	
		Perf::gatherInfo('changeThreadWithThreadIds', array('tid'=>$this->tid));	
	}
	
	function updateForumsextra() {
		if ($this->forum->foruminfo['commend']) {
			$commend = $this->forum->foruminfo['commend'];
			$ifchange = 0;
			foreach ($commend as $key => $value) {
				if ($value['tid'] == $this->tid && $value['subject'] != $this->data['title']) {
					$commend[$key]['subject'] = $this->data['title'];
					$ifchange = 1;
				}
			}
			if ($ifchange) {
				$this->db->update("UPDATE pw_forumsextra SET commend = " . S::sqlEscape(serialize($commend)) . " WHERE fid=" . $this->forum->fid);
				require_once (R_P . 'admin/cache.php');
				updatecache_forums($this->forum->fid);
			}
		}
	}

	function updateImgAtt() {
		is_object($this->att) && $this->newImgNum += $this->att->getUploadImgNum();
		if (!$this->newImgNum && $this->oldImgNum) {
			//$this->db->update("DELETE FROM pw_threads_img WHERE tid=" . S::sqlEscape($this->tid));
			$tucoolService = L::loadClass('tucool','forum');
			$tucoolService->delete($this->tid);
		} elseif ($this->newImgNum != $this->oldImgNum) {
			$tucoolService = L::loadClass('tucool','forum');
			$tucoolService->setforum($this->forum->foruminfo);
			$tucoolService->updateTopicImgNum($this->tid,$this->newImgNum);
			/*
			$this->db->update("REPLACE INTO pw_threads_img SET " . S::sqlSingle(array(
				'tid' => $this->tid,
				'fid' => $this->data['fid']
			)));
			*/
		}
	}
}

class replyModify extends postModify {
	
	var $pw_tmsgs;
	var $pw_posts;
	
	function replyModify($tid, $pid, &$post) {
		parent::postModify($tid, $pid, $post);
		$this->type = 'reply';
		$this->pw_posts = GetPtable('N', $tid);
	}
	
	function getData() {
		return $this->db->get_one("SELECT p.*,t.subject as tsubject,t.tpcstatus FROM $this->pw_posts p LEFT JOIN pw_threads t ON p.tid=t.tid WHERE p.pid=" . S::sqlEscape($this->pid));
	}
	
	function setBehavior() {
		if ($this->extraBehavior !== null) {
			return;
		}
		if ($this->atcdb['tpcstatus'] && getstatus($this->atcdb['tpcstatus'], 1)) {
			$cyid = $this->db->get_value("SELECT cyid FROM pw_argument WHERE tid=" . S::sqlEscape($this->atcdb['tid']));
			if ($cyid) {
				require_once(R_P . 'apps/groups/lib/colonypost.class.php');
				$this->extraBehavior = new PwColonyPost($cyid);
			}
		}
	}
	
	function resetData() {
		return array(
			'aid' => $this->atcdb['aid'],
			'ifsign' => $this->atcdb['ifsign'],
			'content' => $this->atcdb['content'],
			'icon' => $this->atcdb['icon'],
			'title' => $this->atcdb['subject'],
			'anonymous' => $this->atcdb['anonymous'],
			'hideatt' => $this->atcdb['ifhide']
		);
	}
	
	function update() {
		$pwSQL = array(
			'aid' => $this->data['aid'],
			'icon' => $this->data['icon'],
			'subject' => $this->data['title'],
			'ifsign' => $this->data['ifsign'],
			'alterinfo' => $this->data['alterinfo'],
			'ifconvert' => $this->data['convert'],
			'ifwordsfb' => $this->data['ifwordsfb'],
			'content' => $this->data['content'],
			'anonymous' => $this->data['anonymous'],
			'ifhide' => $this->data['hideatt'],
			'ifcheck' => $this->data['ifcheck']
		);
		if ($this->post->uid == $this->atcdb['authorid']) {
			global $onlineip;
			$ipTable = L::loadClass('IPTable', 'utility');
			$pwSQL['userip'] = $onlineip;
			$pwSQL['ipfrom'] = $ipTable->getIpFrom($onlineip);
		}
		//$this->db->update("UPDATE $this->pw_posts SET " . S::sqlSingle($pwSQL) . " WHERE pid=" . S::sqlEscape($this->atcdb['pid']));
		pwQuery::update($this->pw_posts, 'pid=:pid', array($this->atcdb['pid']), $pwSQL);
	}
	
	function afterModify() {
		global $page;
		$replies = '';
		$pwSQL = array();
		if ($this->data['anonymous'] != $this->atcdb['anonymous']) {
			$lt = $this->db->get_one("SELECT pid FROM $this->pw_posts WHERE tid=" . S::sqlEscape($this->tid) . " ORDER BY postdate DESC LIMIT 1");
			if ($this->pid == $lt['pid']) {
				$pwSQL['lastposter'] = $this->data['lastposter'];
			}
		}
		$this->data['ifupload'] && $pwSQL['ifupload'] = $this->data['ifupload'];
		
		if ($this->data['ifcheck'] && $this->forum->foruminfo['allowhtm'] && !$this->forum->foruminfo['cms'] && $page == 1) {
			$StaticPage = L::loadClass('StaticPage');
			$StaticPage->update($this->tid);
		}
		if ($this->data['ifcheck'] != $this->atcdb['ifcheck']) {
			$action = ($this->atcdb['ifcheck'] == 1) ? '-' : '+';
			$this->forum->lastinfo('reply', $action);
			$replies = "replies=replies{$action}'1'";
		}
		if ($pwSQL || $replies) {
			$sql = trim(S::sqlSingle($pwSQL) . ',' . $replies, ',');
			$this->db->update("UPDATE pw_threads SET $sql WHERE tid=" . S::sqlEscape($this->tid));
			Perf::gatherInfo('changeThreads', array('tid'=>$this->tid));
		}
	}

	function updateImgAtt() {
		is_object($this->att) && $this->newImgNum += $this->att->getUploadImgNum();
		if ($this->newImgNum == $this->oldImgNum) {
			return true;
		} 
		$tucoolService = L::loadClass('tucool','forum');
		$tucoolService->setforum($this->forum->foruminfo);
		$tucoolService->updateTucoolImageNum($this->tid);
	}
}
?>