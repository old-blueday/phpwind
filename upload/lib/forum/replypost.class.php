<?php
!defined('P_W') && exit('Forbidden');

/**
 * Reply
 * 
 * @package Thread
 */
class replyPost {
	
	var $db;
	var $post;
	var $forum;
	var $postdata;
	
	var $data;
	var $att;
	
	var $tid;
	var $tpcArr;
	var $replyToUser;

	var $extraBehavior = null;
	
	function replyPost(&$post) {
		global $db;
		$this->db = & $db;
		$this->post = & $post;
		$this->forum = & $post->forum;
		$this->type = 'Reply';
	}
	
	function setTpc($arr) {
		$this->tpcArr = $arr;
		$this->tid = $this->tpcArr['tid'];
		$this->setBehavior();
	}

	function setBehavior() {
		if ($this->extraBehavior !== null) {
			return;
		}
		if (getstatus($this->tpcArr['tpcstatus'], 1)) {
			$cyid = $this->db->get_value("SELECT cyid FROM pw_argument WHERE tid=" . S::sqlEscape($this->tpcArr['tid']));
			require_once(R_P . 'apps/groups/lib/colonypost.class.php');
			$this->extraBehavior = new PwColonyPost($cyid);
		}
	}

	function setToUser($username) {
		$this->replyToUser = $username;
	}
	
	function creditSet() {
		static $creditset = null;
		if (!isset($creditset)) {
			global $db_creditset, $credit;
			require_once (R_P . 'require/credit.php');
			$creditset = $credit->creditset($this->forum->creditset, $db_creditset);
			$creditset = $creditset[$this->type];
		}
		return $creditset;
	}

	function userCreidtSet() {
		$creditset = $this->creditSet();
		if (($times = $this->forum->authCredit($this->post->user['userstatus'])) > 1) {
			foreach ($creditset as $key => $value) {
				$value > 0 && $creditset[$key] *= $times;
			}
		}
		return $creditset;
	}
	
	function check() {
		$this->post->checkUserCredit($this->creditSet());
		/**
		* 版块权限判断
		*/
		if (!$this->getReplyForumRight()) {
			return $this->post->showmsg('reply_forum_right');
		}
		if ($this->extraBehavior) {
			if (($return = $this->extraBehavior->replyCheck()) !== true) {
				return $this->post->showmsg($return);
			}
		}
	}
	
	function setPostData(&$postdata) {
		$this->postdata = & $postdata;
		$this->att = & $postdata->att;
		$this->data = $postdata->getData();
		if (stripslashes($this->data['title']) == 'Re:' . $this->tpcArr['subject']) {
			$this->data['title'] = '';
		}
	}
	
	/**
	 * @author papa
	 * @param $pid
	 * @return unknown_type
	 */
	function setPostFloor($pid) {
		$sql = "INSERT INTO pw_postsfloor SET pid=" . S::sqlEscape($pid) . ", tid=" . S::sqlEscape($this->tid);
		$this->db->update($sql);
	}
	
	function execute($postdata) {
		global $db_cvtime, $db_ptable, $onlineip, $db_plist;
		$this->setPostData($postdata);
		$ipTable = L::loadClass('IPTable', 'utility');
		$ipfrom = $ipTable->getIpFrom($onlineip);
		$timestamp = time();
		$db_cvtime!=0 && $timestamp += $db_cvtime*60;
		$pwSQL = array(
			'fid' => $this->data['fid'],
			'tid' => $this->tid,
			'aid' => $this->data['aid'],
			'author' => $this->data['author'],
			'authorid' => $this->data['authorid'],
			'icon' => $this->data['icon'],
			'postdate' => $timestamp,
			'subject' => $this->data['title'],
			'userip' => $onlineip,
			'ifsign' => $this->data['ifsign'],
			'ipfrom' => $ipfrom,
			'ifconvert' => $this->data['convert'],
			'ifwordsfb' => $this->data['ifwordsfb'],
			'ifcheck' => $this->data['ifcheck'],
			'content' => $this->data['content'],
			'anonymous' => $this->data['anonymous'],
			'ifhide' => $this->data['hideatt']
		);
		$pw_posts = GetPtable($this->tpcArr['ptable']);
		if ($db_plist && count($db_plist) > 1) {
			//* $this->db->update("INSERT INTO pw_pidtmp(pid) VALUES(null)");
			//* $pid = $this->db->insert_id();
			$uniqueService = L::loadClass ('unique', 'utility');
			$pid = $uniqueService->getUnique('post');	
		} else {
			$pid = '';
		}
		$pwSQL['pid'] = $pid;
		//$pwSQL = S::sqlSingle($pwSQL);
		//$this->db->update("INSERT INTO $pw_posts SET $pwSQL");
		pwQuery::insert($pw_posts, $pwSQL);
		!$pid && $pid = $this->db->insert_id();
		$this->tpcArr['openIndex'] && $this->setPostFloor($pid);
		$this->pid = $pid;
		if (is_object($this->att) && ($aids = $this->att->getAids())) {
			$this->db->update("UPDATE pw_attachs SET " . S::sqlSingle(array(
				'tid' => $this->tid,
				'pid' => $this->pid
			)) . ' WHERE aid IN(' . S::sqlImplode($aids) . ')');
			//tucool
			$imgNum = $this->att->getUploadImgNum();
			if ($this->forum->forumset['iftucool'] && $this->forum->forumset['tucoolpic'] && $imgNum) {
				$tucoolService = L::loadClass('tucool','forum');
				$tucoolService->setForum($this->forum->foruminfo);
				$tucoolService->updateTucoolImageNum($this->tid);
			}
		}
		if ($this->data['ifcheck'] == 1) {
			$sqladd1 = '';
			$sqladd = array(
				'lastposter' => $this->data['lastposter']
			);
			$this->tpcArr['locked'] < 3 && $this->tpcArr['lastpost'] < $timestamp && $sqladd['lastpost'] = $timestamp;
			$this->data['ifupload'] && $sqladd['ifupload'] = $this->data['ifupload'];
			$ret = $this->sendMail();
			if ($ret & 2) {
				$sqladd['ifmail'] = 4;
			} elseif ($ret & 1) {
				$sqladd1 = "ifmail=ifmail-1,";
			}
			$this->db->update("UPDATE pw_threads SET {$sqladd1}replies=replies+1,hits=hits+1," . S::sqlSingle($sqladd) . " WHERE tid=" . S::sqlEscape($this->tid));
			Perf::gatherInfo('changeThreads', array('tid'=>$this->tid));
		}
		$this->post->updateUserInfo($this->type, $this->userCreidtSet(), $this->data['content']);
		$this->afterReply();

		if ($this->extraBehavior) {
			$this->extraBehavior->replyPost($this->pid, $this->tid, $this->data);
		}
	}
	
	function sendMail() {
		global $db_msgreplynotice,$db_replysendmail,$db_replysitemail,$windid,$winduid;
		$ret = $msgNotice = 0;
		if ($db_msgreplynotice && $this->replyToUser && $this->replyToUser != $windid) {
			M::sendMessage(
				$winduid,
				array($this->replyToUser),
				array(
					'create_uid' => $winduid,
					'create_username' => $windid,
					'title' => getLangInfo('writemsg','subject_replytouser_title',array(
						'windid'	=> $windid,
						'title'		=> substrs(strip_tags($this->tpcArr['subject']), 30, 'Y')
					)),
					'content' => getLangInfo('writemsg','subject_reply_content',array(
						'tid' => $this->tid,
						'pid' => $this->pid,
						'windid' => $windid,
						'content'	=> substrs(strip_tags($this->data['content']), 60, 'Y')
					)),
				),
				'sms_reply',
				'sms_reply'
			);
			$msgNotice = 1;
		}
		if ($this->data['authorid'] == $this->tpcArr['authorid']) {
			return $ret;
		}
		if ($db_replysendmail == 1 && ($this->tpcArr['ifmail'] == 1 || $this->tpcArr['ifmail'] == 3)) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$receiver = $this->tpcArr['author'];
			$old_title = $this->tpcArr['subject'];
			$detail = $userService->get($this->tpcArr['authorid']);
			$send_address = $detail['email'];
			if (getstatus($detail['userstatus'], PW_USERSTATUS_RECEIVEMAIL)) {
				require_once (R_P . 'require/sendemail.php');
				sendemail($send_address, 'email_reply_subject', 'email_reply_content', 'email_additional');
			}
			$ret = 1;
		}
		if ($db_replysitemail && !$msgNotice && ($this->tpcArr['ifmail'] == 2 || $this->tpcArr['ifmail'] == 3)) {
			/*
			$userService = L::loadClass('UserService', 'user');
			$rt = $userService->get($this->tpcArr['authorid'], true, false, true);
			$replyinfo = $rt['replyinfo'] ? $rt['replyinfo'] . $this->tid . ',' : ",$this->tid,";
			$userService->update($this->tpcArr['authorid'], array(), array(), array('replyinfo' => $replyinfo));
			if (!getstatus($rt['userstatus'], PW_USERSTATUS_NEWRP)) {
				$userService->setUserStatus($this->tpcArr['authorid'], PW_USERSTATUS_NEWRP, true);
			}
			*/
			M::sendMessage(
				$winduid,
				array($this->tpcArr['author']),
				array(
					'create_uid' => $winduid,
					'create_username' => $windid,
					'title' => getLangInfo('writemsg','subject_reply_title',array(
						'windid'	=> $windid,
						'author'	=> $this->tpcArr['author'],
						'title'		=> substrs(strip_tags($this->tpcArr['subject']), 30, 'Y')
					)),
					'content' => getLangInfo('writemsg','subject_reply_content',array(
						'tid' => $this->tid,
						'pid' => $this->pid,
						'windid' => $windid,
						'content'	=> substrs(strip_tags(stripWindCode($this->data['content'])), 60, 'Y')
					)),
				),
				'sms_reply',
				'sms_reply'
			);
			$ret += 2;
		}
		return $ret;
	}
	
	function afterReply() {
		global $db_ifpwcache, $timestamp, $db_readperpage;
		if ($this->data['ifcheck'] == 1) {
			if ($this->forum->foruminfo['allowhtm'] && !$this->forum->foruminfo['cms'] && $this->tpcArr['replies'] < $db_readperpage) {
				$StaticPage = L::loadClass('StaticPage');
				$StaticPage->update($this->tid);
			}
			if ($this->tpcArr['ifcheck'] == 1) {
				$lastpost = array(
					'subject' => $this->data['title'] ? substrs($this->data['title'], 26) : 'Re:' . substrs($this->tpcArr['subject'], 26),
					'author' => $this->data['lastposter'],
					'lastpost' => $timestamp,
					'tid' => $this->tid,
					't_date' => $this->tpcArr['postdate']
				);
				$this->forum->lastinfo('reply', '+', $lastpost);
			}
			
			//Start Here pwcache
			if ($db_ifpwcache & 270) {
				L::loadClass('elementupdate', '', false);
				$elementupdate = new ElementUpdate($this->forum->fid);
				$elementupdate->special = $this->tpcArr['special'];
				if ($db_ifpwcache & 14) {
					$elementupdate->replySortUpdate($this->tid, $this->forum->fid, $this->tpcArr['postdate'], $this->tpcArr['replies'] + 1);
				}
				if ($db_ifpwcache & 256) {
					$elementupdate->newReplyUpdate($this->tid, $this->forum->fid, $this->tpcArr['postdate']);
				}
				$elementupdate->updateSQL();
			}
			require_once (R_P . 'require/functions.php');
			updateDatanalyse($this->data['authorid'], 'memberThread', 1);
			updateDatanalyse($this->tid, 'threadPost', 1);
			
			// memcache refresh
			// $threadsObj = L::loadclass("threads", 'forum');
			// $threadsObj->clearThreadByThreadId($this->tid);
			
			// memcache refresh
			// $threadlistObj = L::loadclass("threadlist", 'forum');
			// $threadlistObj->updateThreadIdsByForumId($this->forum->fid, $this->tid);
				
			Perf::gatherInfo('changeThreadWithThreadIds', array('tid'=>$this->tid));
			Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$this->forum->fid));		
			
		}
		if ($this->postdata->filter->filter_weight > 1) {
			$this->postdata->filter->insert($this->tid, $this->pid, implode(',', $this->postdata->filter->filter_word), $this->postdata->filter->filter_weight);
		}
	}
	
	function getNewId() {
		return $this->pid;
	}

	/**
	 * 获取用户在版块中的发表回复权限
	 * @author zhudong
	 * @return int $right
	 */
	 function getReplyForumRight() {
		$right = false;
		if ($this->post->admincheck) {
			$right = true;
		} elseif ($this->forum->allowreply($this->post->user,$this->post->groupid)) {
			$right = true;
		} elseif ($this->extraBehavior) {//当在群组中
			$this->extraBehavior->replyCheck() && $right = true;
		}
		return $right;
	 }
	
}
?>