<?php
!defined('P_W') && exit('Forbidden');

/**
 * Topic
 * 
 * @package Thread
 */
class topicPost {
	
	var $db;
	var $post;
	var $forum;
	var $postdata;
	var $tid;
	
	var $data;
	var $att;
	var $type;

	var $extraBehavior = null;
	
	function topicPost(&$post) {
		global $db;
		$this->db = & $db;
		$this->post = & $post;
		$this->forum = & $post->forum;
		$this->type = 'Post';
	}
	
	function setPostData(&$postdata) {
		$this->postdata = & $postdata;
		$this->att = & $postdata->att;
		$this->data = $postdata->getData();
		if ($this->extraBehavior) {
			$this->data = $this->extraBehavior->setTopicPostData($this->data);
		}
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
		if (!$this->getPostnewForumRight()) {
			return $this->post->showmsg('postnew_forum_right');
		}
		if ($this->extraBehavior) {
			if (($return = $this->extraBehavior->topicCheck()) !== true) {
				return $this->post->showmsg($return);
			}
		}
	}

	function execute(&$postdata) {
		global $timestamp, $db_ptable, $onlineip;
		$this->setPostData($postdata);
		/*
		$pwSQL = S::sqlSingle(array(
			'fid' => $this->data['fid'],
			'icon' => $this->data['icon'],
			'author' => $this->data['author'],
			'authorid' => $this->data['authorid'],
			'subject' => $this->data['title'],
			'ifcheck' => $this->data['ifcheck'],
			'type' => $this->data['w_type'],
			'postdate' => $timestamp,
			'lastpost' => $timestamp,
			'lastposter' => $this->data['lastposter'],
			'hits' => 1,
			'replies' => 0,
			'topped' => $this->data['topped'],
			'digest' => $this->data['digest'],
			'special ' => $this->data['special'],
			'state' => 0,
			'ifupload' => $this->data['ifupload'],
			'ifmail' => $this->data['ifmail'],
			'anonymous' => $this->data['anonymous'],
			'ptable' => $db_ptable,
			'ifmagic' => $this->data['ifmagic'],
			'ifhide' => $this->data['hideatt'],
			'tpcstatus' => $this->data['tpcstatus'],
			'modelid' => $this->data['modelid']
		));*/
		//$this->db->update("INSERT INTO pw_threads SET $pwSQL");
		$pwSQL = array(
			'fid' => $this->data['fid'],
			'icon' => $this->data['icon'],
			'author' => $this->data['author'],
			'authorid' => $this->data['authorid'],
			'subject' => $this->data['title'],
			'ifcheck' => $this->data['ifcheck'],
			'type' => $this->data['w_type'],
			'postdate' => $timestamp,
			'lastpost' => $timestamp,
			'lastposter' => $this->data['lastposter'],
			'hits' => 1,
			'replies' => 0,
			'topped' => $this->data['topped'],
			'digest' => $this->data['digest'],
			'special ' => $this->data['special'],
			'state' => 0,
			'ifupload' => $this->data['ifupload'],
			'ifmail' => $this->data['ifmail'],
			'anonymous' => $this->data['anonymous'],
			'ptable' => $db_ptable,
			'ifmagic' => $this->data['ifmagic'],
			'ifhide' => $this->data['hideatt'],
			'tpcstatus' => $this->data['tpcstatus'],
			'modelid' => $this->data['modelid'],
			'frommob' => $this->data['frommob']
			
		);
		$this->tid = pwQuery::insert('pw_threads', $pwSQL);
		//* $this->tid = $this->db->insert_id();
		# memcache refresh
		// $threadList = L::loadClass("threadlist", 'forum');
		// $threadList->updateThreadIdsByForumId($this->data['fid'], $this->tid);
		//* Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$this->data['fid'])); //pwQuery::insert已经执行清理缓存
		
		$pw_tmsgs = GetTtable($this->tid);
		
		if (is_object($postdata->tag)) {
			$postdata->tag->insert($this->tid);
			$this->data['tags'] .= "\t" . $postdata->tag->relate($this->data['title'], $this->data['content']);
		}
		if (is_object($this->att) && ($aids = $this->att->getAids())) {
			$this->att->pw_attachs->updateById($aids, array('tid' => $this->tid));
			$topicImgNum = $this->att->getUploadImgNum();
			if ($this->forum->forumset['iftucool'] && $this->forum->forumset['tucoolpic'] && $topicImgNum >= $this->forum->forumset['tucoolpic']) {
				$tucoolService = L::loadClass('tucool','forum');
				$tucoolService->add(
					array(
						'fid' => $this->data['fid'],
						'tid' => $this->tid,
						'tpcnum' => $topicImgNum
					)
				);
			}
		}
		$ipTable = L::loadClass('IPTable', 'utility');
		
		$pwSQL = S::sqlSingle(array(
			'tid' => $this->tid,
			'aid' => $this->data['aid'],
			'userip' => $onlineip,
			'ifsign' => $this->data['ifsign'],
			'buy' => '',
			'ipfrom' => $ipTable->getIpFrom($onlineip),
			'tags' => $this->data['tags'],
			'ifconvert' => $this->data['convert'],
			'ifwordsfb' => $this->data['ifwordsfb'],
			'content' => $this->data['content'],
			'magic' => $this->data['magic']
		));
		$this->db->update("INSERT INTO $pw_tmsgs SET $pwSQL");
		
		if ($this->data['digest']) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userService->updateByIncrement($this->data['authorid'], array(), array('digests' => 1));
			$this->post->user['digests']++;
		}
		if ($this->data['replyreward']) {
			$replyRewardService = L::loadClass('ReplyReward', 'forum');/* @var $replyRewardService PW_ReplyReward */
			$this->data['replyreward']['tid'] = $this->tid;
			$replyRewardService->addNewReward($this->data['authorid'], $this->data['replyreward']);
		}
		$this->post->updateUserInfo($this->type, $this->userCreidtSet(), $this->data['content']);
		$this->afterpost();

		if ($this->extraBehavior) {
			$this->extraBehavior->topicPost($this->tid, $this->data);
		}
	}
	
	function afterpost() {
		global $db_ifpwcache, $timestamp;
		if ($this->data['ifcheck'] == 1) {
			if ($this->forum->foruminfo['allowhtm'] && !$this->forum->foruminfo['cms']) {
				$StaticPage = L::loadClass('StaticPage');
				$StaticPage->update($this->tid);
			}
			$lastpost = array(
				'subject' => substrs($this->data['title'], 26),
				'author' => $this->data['lastposter'],
				'lastpost' => $timestamp,
				'tid' => $this->tid,
				't_date' => $timestamp
			);
			$this->forum->lastinfo('topic', '+', $lastpost);
			
			if ($this->forum->isOpen() && !$this->data['anonymous']) {
				require_once (R_P . 'require/functions.php');
				if (!$this->extraBehavior) {
					$weiboService = L::loadClass('weibo','sns');/* @var $weiboService PW_Weibo */ 
					$weiboContent = substrs(stripWindCode($weiboService->escapeStr(strip_tags($this->data['content']))), 125);
					$weiboExtra = array(
									'title' => stripslashes($this->data['title']),
									'fid' => $this->forum->fid,
									'fname' => $this->forum->name,
									'atusers' =>$this->data['atusers']
								);
					$weiboService->send($this->post->uid,$weiboContent,'article',$this->tid,$weiboExtra);
					$threadService = L::loadClass('threads','forum');
					$threadService->setAtUsers($this->tid,0,$this->data['atusers']);
				}
				//会员资讯缓存
				$userCache = L::loadClass('Usercache', 'user');
				$userCache->delete($this->data['authorid'], array('article', 'cardtopic'));
				/*
				$usercachedata = array();
				$usercachedata['subject'] = substrs(stripWindCode($this->data['title']), 100, N);
				$usercachedata['content'] = substrs(stripWindCode($this->data['content']), 100, N);
				$usercachedata['postdate'] = $timestamp;
				if ($this->att) {
					$usercachedata['attimages'] = $this->att->getImages(4);
				}
				$userCache->update($this->data['authorid'], 'topic', $this->tid, $usercachedata);
				*/
			}
			//Start elementupdate
			require_once (D_P . 'data/bbscache/o_config.php');
			if ($db_ifpwcache & 128 || $o_browseopen ||(($db_ifpwcache & 512) && $this->att && $this->att->elementpic)) {
				L::loadClass('elementupdate', '', false);
				$elementupdate = new ElementUpdate($this->forum->fid);
				if ($db_ifpwcache & 128) {
					$elementupdate->newSubjectUpdate($this->tid, $this->forum->fid, $timestamp, $this->data['special']);
				}
				if (($db_ifpwcache & 512) && $this->att && $this->att->elementpic && $this->_checkIfHidden()) {
					$elementupdate->newPicUpdate($this->att->elementpic['aid'], $this->forum->fid, $this->tid, $this->att->elementpic['attachurl'], $this->att->elementpic['ifthumb'], $this->data['content']);
				}
				if ($o_browseopen){
					$elementupdate->setCacheNum(100);/*设置缓存100个*/
					$elementupdate->lastPostUpdate($this->data['authorid'], $this->tid, $timestamp);
				}
				$elementupdate->updateSQL();
			}
			require_once (R_P . 'require/functions.php');
			updateDatanalyse($this->data['authorid'], 'memberThread', 1);
			//End elementupdate
		}
		if ($this->postdata->filter->filter_weight > 1) {
			$this->postdata->filter->insert($this->tid, 0, implode(',', $this->postdata->filter->filter_word), $this->postdata->filter->filter_weight);
		}
		if ($this->data['topped'] > 0) {
			require_once (R_P . 'require/updateforum.php');
			setForumsTopped($this->tid, $this->data['fid'], $this->data['topped']);
			updatetop();
		}
	}
	
	function getNewId() {
		return $this->tid;
	}

	/**
	 * 获取用户在版块中的发表新帖权限
	 * @author zhudong
	 * @return int $right
	 */
	function getPostnewForumRight() {
		$right = false;
		if ($this->post->admincheck) {
			$right = true;
		} elseif ($this->forum->allowpost($this->post->user,$this->post->groupid)) {
			$right = true;
		} elseif ($this->extraBehavior) {//当在群组中
			$this->extraBehavior->topicCheck() && $right = true;
		}
		return $right;
	 }

	 function _checkIfHidden() {
		if ($this->data['hideatt']) return false;
		$patterns = array(
			"/\[post\](.+?)\[\/post\]/eis",
			"/\[hide=(.+?)\](.+?)\[\/hide\]/eis",
			"/\[sell=(.+?)\](.+?)\[\/sell\]/eis"
		);
		$temp = preg_replace($patterns,'',$this->data['content']);
		return $temp == $this->data['content'];
	 }
}
?>