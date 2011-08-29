<?php
/**
 * 评分操作
 * 
 * @author pw team, Oct 20, 2010
 * @copyright 2003-2010 phpwind.net. All rights reserved.
 * @version 
 * @package forum
 */
 
!defined('P_W') && exit('Forbidden');
 
class PW_Ping {

	var $db;

	var $postData;
	var $markable;
	var $markset;
	var $userCreditInfo;
	var $fid;
	var $tid;
	var $forum;
	
	function PW_Ping() {
		global $db;
		$this->db = & $db;
		$this->markable = null;
	}

	function init($tid, $pids) {
		if (!is_null($this->markable)) {
			return;
		}
		$this->_initPostData($tid, $pids);
		$this->_initMarkInfo();
	}
	
	function check($checkType = '') {
		if (empty($this->postData)) {
			return 'selid_illegal';
		}
		if (!$this->forum->isForum()) {
			return 'data_error';
		}
		if (empty($this->markset)) {
			return 'markright_set_error';
		}
		if (!$this->markable && !$this->forum->isBM($GLOBALS['windid'])) {
			return 'no_markright';
		}
		if (($return = $this->pingCheck($checkType)) !== true) {
			return $return;
		}
		return true;
	}
	
	function doPing($cid, $addpoint, $params = array()) {
		global $credit,$winddb,$winduid,$windid,$timestamp,$onlineip,$gp_gptype;
		
		require_once(R_P.'require/credit.php');
		$add_c = $tmp = $pingLog = array();
		if (is_array($cid)) {
			foreach ($cid as $k => $v) {
				if ($v && isset($credit->cType[$v]) && is_numeric($addpoint[$k]) && $addpoint[$k] <> 0) {
					if (!isset($this->markset[$v])) return 'masigle_credit_right';
					$tmp[$v] += intval($addpoint[$k]);
				}
			}
			foreach ($tmp as $k => $v) {
				if (!$v) continue;
				if ($v > $this->markset[$k]['maxper'] || $v < $this->markset[$k]['minper']) {
					$GLOBALS['limitCreditType'] = $k;//GLOBAL
					$GLOBALS['markset'] = $this->markset;//GLOBAL
					return 'masigle_creditlimit';
				}
				$add_c[$k] = $v;
			}
		}
		if (empty($add_c)) return 'member_credit_error';
		if (strlen($params['atc_content']) > 100) return 'showping_content_too_long';
		
		$count = count($this->postData);
		foreach ($add_c as $k => $v) {
			$allpoint = abs($v) * $count;
			if ($allpoint > $this->markset[$k]['leavepoint']) {
				$GLOBALS['leavepoint'] = $this->markset[$k]['leavepoint'];
				return 'masigle_point';
			}
			if (isset($this->userCreditInfo[$k])) {
				$this->userCreditInfo[$k]['pingdate'] = $timestamp;
				$this->userCreditInfo[$k]['pingnum'] += $allpoint;
			} else {
				$this->userCreditInfo[$k] = array(
					'pingdate'	=> $timestamp,
					'pingnum'	=> $allpoint,
					'pingtype'	=> $k
				);
			}
			//需扣除积分
			if ($this->markset[$k]['markdt'] && $allpoint > 0) {
				if ($credit->get($winduid, $k) < $allpoint) return 'credit_enough';
				$credit->set($winduid, $k, -$allpoint, false);
			}
		}
		$newcreditdb = '';
		foreach ($this->userCreditInfo as $v) {
			$newcreditdb .= ($newcreditdb ? '|' : '') . implode("\t",$v);
		}
		//更新用户评分信息
		$userService = L::loadClass('UserService', 'user'); /* @var $this->userService PW_UserService */
		$userService->update($winduid, array(), array(), array('credit' => $newcreditdb));
		
		$singlepoint = array_sum($add_c);
		require_once(R_P.'require/showimg.php');
		//* $threadService = L::loadClass("threads", 'forum');
		foreach ($this->postData as $pid => $atc) {
			!$atc['subject'] && $atc['subject'] = substrs(strip_tags(convert($atc['content'])),35);
			/*积分日志*/
			$credit->addLog('credit_showping', $add_c, array(
				'uid'		=> $atc['authorid'],
				'username'	=> $atc['author'],
				'ip'		=> $onlineip,
				'operator'	=> $windid,
				'tid'		=> $this->tid,
				'subject'	=> $atc['subject'],
				'reason'	=> $params['atc_content']
			));
			//为被评用户增加积分
			$credit->sets($atc['authorid'], $add_c, false);
			if (!is_numeric($pid)) {
				//主题时，更新总评分数
				//* $this->db->update("UPDATE pw_threads SET ifmark=ifmark+" . S::sqlEscape($singlepoint)." WHERE tid=" . S::sqlEscape($tid));
				$this->db->update(pwQuery::buildClause("UPDATE :pw_table SET ifmark=ifmark+:ifmark WHERE tid=:tid", array('pw_threads', $singlepoint, $this->tid)));
				
				$rpid = 0;
			} else {
				$rpid = $pid;
			}
			$pwSQL = $ping = array();
			$affect = '';
			list($ping['pingtime'],$ping['pingdate']) = getLastDate($timestamp);
			list($face) = showfacedesign($winddb['icon'],1,'m');
			foreach ($add_c as $key => $value) {
				/*记录评分日志*/
				$pwSQL = array(
					'fid'	=> $this->fid,
					'tid'	=> $this->tid,
					'pid'	=> $rpid,
					'name'	=> $key,//$credit->cType[$key],
					'point'	=> $value,
					'pinger'=> $windid,
					'record'=> $params['atc_content'],
					'pingdate'=> $timestamp,
				);
				$affect .= ($affect ? ',' : '') . $credit->cType[$key] . ':' . $value;
				
				$this->db->update("INSERT INTO pw_pinglog SET " . S::sqlSingle($pwSQL));
				$pingLogId = $this->db->insert_id();
				
				$pingLog[$pid][$key] = array(
					'fid'	=> $this->fid,
					'tid'	=> $this->tid,
					'pid'	=> $pid,
					'name'	=> $credit->cType[$key],
					'point'	=> $value>0 ? "+$value" : $value,
					'pinger'=> $windid,
					'pingeruid'=> $winduid,
					'record'=> $params['atc_content'] ? $params['atc_content'] : '-',
					'face'	=> $face,
					'pingtime'=> $ping['pingtime'],
					'pingdate'=> $ping['pingdate'],
					'pingLogId'=>$pingLogId
				);
			}

			$this->update_markinfo($this->tid, $rpid);
			//* $threadService->clearTmsgsByThreadId($this->tid);
			Perf::gatherInfo('changeTmsgWithThreadIds', array('tid'=>$this->tid));
			$this->postData[$pid]['ifmark'] = $ifmark;

			if ($params['ifmsg'] && !$atc['anonymous'] && $atc['author'] != $windid) {
				//发消息
				$title = getLangInfo('writemsg','ping_title',array('sender'=>$windid,'receiver'=>$atc['author']));
				$content = getLangInfo('writemsg','ping_content',array(
					'manager'	=> $windid,
					'fid'		=> $atc['fid'],
					'tid'		=> $this->tid,
					'pid'		=> $pid,
					'subject'	=> $atc['subject'],
					'postdate'	=> get_date($atc['postdate']),
					'forum'		=> strip_tags($this->forum->foruminfo['name']),
					'affect'    => $affect,
					'admindate'	=> get_date($timestamp),
					'reason'	=> stripslashes($params['atc_content']),
					'sender'    => $windid,
					'receiver'  => $atc['author']
				));
				$this->sendMessage($atc['author'],$title,$content);
			}
			if ($gp_gptype == 'system'){
				require_once(R_P.'require/writelog.php');
				$log = array(
					'type'      => 'credit',
					'username1' => $atc['author'],
					'username2' => $windid,
					'field1'    => $this->fid,
					'field2'    => '',
					'field3'    => '',
					'descrip'   => 'credit_descrip',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
					'tid'		=> $this->tid,
					'forum'		=> strip_tags($this->forum->foruminfo['name']),
					'subject'	=> $atc['subject'],
					'affect'	=> $affect,
					'reason'	=> $params['atc_content']
				);
				writelog($log);
			}
		}
		$credit->runsql();
		defined('AJAX') && $GLOBALS['pingLog'] = $pingLog;
		
		/*评分内容作为回复*/
		if ($params['ifpost'] && $params['atc_content']) {
			$replyReturn = $this->addPost($this->tid, $params['atc_content']);
		}
		if ($GLOBALS['db_autoban'] && $singlepoint < 0) {
			require_once(R_P.'require/autoban.php');
			foreach ($this->postData as $pid => $atc) {
				autoban($atc['authorid']);
			}
		}
		if ($this->forum->foruminfo['allowhtm'] && $_REQUEST['page'] == 1) {
			$StaticPage = L::loadClass('StaticPage');
			$StaticPage->update($this->tid);
		}
		if (isset($replyReturn) && $replyReturn !== true) {
			$replyReturn = getLangInfo('msg', $replyReturn);
			return '评分已完成！回复失败，可能的原因是:'.$replyReturn;
		}
		return true;
	}
	
	/**
	 * 取消评分
	 * @param $tid
	 * @param $pids
	 * @param $params
	 */
	function deletePing($params = array()) {
		global $groupid,$windid,$winduid,$credit,$onlineip,$timestamp,$gp_gptype;
		//* $threadService = L::loadClass("threads", 'forum');
		require_once(R_P.'require/credit.php');
		foreach ($this->postData as $pid => $atc) {
			$rpid = $pid == 'tpc' ? '0' : $pid; // delete pinglog
			$pingdata = $this->db->get_one('SELECT * FROM pw_pinglog WHERE tid=' . S::sqlEscape($this->tid) . ' AND pid=' . S::sqlEscape($rpid) . ' AND pinger=' . S::sqlEscape($windid) . ' ORDER BY pingdate DESC LIMIT 1');
			$this->db->update('DELETE FROM pw_pinglog WHERE id=' . S::sqlEscape($pingdata['id']));
			$this->update_markinfo($this->tid, $rpid);
			//* $threadService->clearTmsgsByThreadId($this->tid);
			Perf::gatherInfo('changeTmsgWithThreadIds', array('tid'=>$this->tid));
			$addpoint = $pingdata['point'];
			if (!$cid = $credit->getCreditTypeByName($pingdata['name'])) {
				continue;
			}
			$cName = $credit->cType[$cid];
			$addpoint = $addpoint>0 ? -$addpoint : abs($addpoint);
			!$atc['subject'] && $atc['subject'] = substrs(strip_tags(convert($atc['content'])),35);

			$credit->addLog('credit_delping', array($cid => $addpoint) ,array(
				'uid'		=> $atc['authorid'],
				'username'	=> $atc['author'],
				'ip'		=> $onlineip,
				'operator'	=> $windid,
				'tid'		=> $this->tid,
				'subject'	=> $atc['subject'],
				'reason'	=> $params['atc_content']
			));
			$credit->set($atc['authorid'], $cid, $addpoint);

			if (!is_numeric($pid)) {
				//* $this->db->update('UPDATE pw_threads SET ifmark=ifmark+'.S::sqlEscape($addpoint).' WHERE tid='.S::sqlEscape($tid));
				$this->db->update(pwQuery::buildClause("UPDATE :pw_table SET ifmark=ifmark+:ifmark WHERE tid=:tid", array('pw_threads', $addpoint, $this->tid)));
			}
			if ($params['ifmsg'] && !$atc['anonymous'] && $atc['author'] != $windid) {
				//发消息
				$title = getLangInfo('writemsg','delping_title',array('sender'=> $windid,'receiver'=>$atc['author']));
				$content = getLangInfo('writemsg','delping_content',array(
							'manager'	=> $windid,
							'fid'		=> $atc['fid'],
							'tid'		=> $this->tid,
							'pid'		=> $pid,
							'subject'	=> $atc['subject'],
							'postdate'	=> get_date($atc['postdate']),
							'forum'		=> strip_tags($this->forum->foruminfo['name']),
							'affect'    => "{$cName}:$addpoint",
							'admindate'	=> get_date($timestamp),
							'reason'	=> stripslashes($params['atc_content']),
							'sender'    => $windid,
							'receiver'  => $atc['author']
				));
				$this->sendMessage($atc['author'], $title, $content);
			}
			if ($gp_gptype == 'system'){
				require_once(R_P.'require/writelog.php');
				$log = array(
					'type'      => 'credit',
					'username1' => $atc['author'],
					'username2' => $windid,
					'field1'    => $atc['fid'],
					'field2'    => '',
					'field3'    => '',
					'descrip'   => 'creditdel_descrip',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
					'tid'		=> $this->tid,
					'forum'		=> strip_tags($this->forum->foruminfo['name']),
					'subject'	=> $atc['subject'],
					'affect'	=> "$name:$addpoint",
					'reason'	=> $params['atc_content']
				);
				writelog($log);
			}
			$pingLog[$pid] = $pingdata['id'];
		}
		$credit->runsql();
		defined('AJAX') && $GLOBALS['pingLog'] = $pingLog;//GLOBAL
		if ($this->forum->foruminfo['allowhtm'] && $_REQUEST['page']==1) {
			$StaticPage = L::loadClass('StaticPage');
			$StaticPage->update($this->tid);
		}
		return true;
	}

	/*
	 * 获取当前登录用户组信息、评分信息
	 */
	function _getUserInfo() {
		global $winduid,$tdtime;
		if (empty($winduid)) {
			return array();
		}
		$userInfo = array();
		$userService = L::loadClass('UserService', 'user'); /* @var $this->userService PW_UserService */
		$tmpUserInfo = $userService->get($winduid, true, false, true);
		if ($tmpUserInfo) {
			$userInfo['groups'] = array();
			$userInfo['credit'] = array();
			if ($tmpUserInfo['groups']) {
				foreach (explode(',',$tmpUserInfo['groups']) as $k => $v) {
					is_numeric($v) && $userInfo['groups'][] = $v;
				}
			}
			if ($tmpUserInfo['credit']) {
				foreach (explode('|',$tmpUserInfo['credit']) as $v) {
					//当前用户的每种评分类型的已评信息
					$cv = explode("\t",$v);
					if ($cv['0'] >= $tdtime) {
						$userInfo['credit'][$cv['2']]['pingdate'] = $cv['0'];
						$userInfo['credit'][$cv['2']]['pingnum'] = $cv['1'];
						$userInfo['credit'][$cv['2']]['pingtype'] = $cv['2'];
					}
				}
			}
		}
		return $userInfo;
	}
	
	/**
	 * 获取用户评分权限
	 * 
	 * @return array($markable,$markset,$creditType)
	 */
	function _initMarkInfo() {
		global $_G, $credit;
		require_once(R_P.'require/credit.php');
		$userInfo = $this->_getUserInfo();
		$tmpMark  = $markset = $groupCredits = array();
		$markable = $_G['markable'];

		$_G['markset'] = unserialize($_G['markset']);
		foreach ($_G['markset'] as $key => $value) {
			if ($value['markctype'] && is_numeric($value['marklimit'][0]) && is_numeric($value['marklimit'][1])) {
				$tmpMark[$key]['minper'][]		= $value['marklimit'][0];
				$tmpMark[$key]['maxper'][]		= $value['marklimit'][1];
				$tmpMark[$key]['maxcredit'][]	= $value['maxcredit'];
				$tmpMark[$key]['markdt']		= $value['markdt'];
			}
		}

		if ($userInfo['groups']) {
			$query = $this->db->query(
				"SELECT gid,rkey,rvalue FROM `pw_permission`
					WHERE uid='0' 
						AND fid='0' 
						AND gid IN(" . S::sqlImplode($userInfo['groups']) . ") 
						AND rkey IN ('markset','markable') 
						AND type='basic'"
			);
			while ($rt = $this->db->fetch_array($query)) {
				$groupCredits[$rt['gid']][$rt['rkey']] = $rt['rvalue'];
			}
		}

		foreach ($groupCredits as $gid => $p) {
			if (is_array($p) && $p['markable']) {
				//评分权限,取各组中最大
				$p['markable'] > $markable && $markable = $p['markable'];
				$p['markset'] = (array)unserialize($p['markset']);
				
				foreach ($p['markset'] as $k => $v) {
					if ($v['markctype'] && is_numeric($v['marklimit'][0]) && is_numeric($v['marklimit'][1])) {
						$tmpMark[$k]['minper'][] = $v['marklimit'][0];
						$tmpMark[$k]['maxper'][] = $v['marklimit'][1];
						is_numeric($v['maxcredit']) && $tmpMark[$k]['maxcredit'][] = $v['maxcredit'];
						!$v['markdt'] && $tmpMark[$k]['markdt'] = 0;
					}
				}
			}
		}

		foreach ($tmpMark as $key => $value) {
			if (!isset($credit->cType[$key])) continue;
			$markset[$key]['minper']	= min($value['minper']);
			$markset[$key]['maxper']	= max($value['maxper']);
			$markset[$key]['maxcredit']	= max($value['maxcredit']);
			$markset[$key]['markdt']	= $value['markdt'];
			if (isset($userInfo['credit'][$key])) {
				$markset[$key]['leavepoint'] = abs($markset[$key]['maxcredit'] - $userInfo['credit'][$key]['pingnum']);
			} else {
				$markset[$key]['leavepoint'] = $markset[$key]['maxcredit'];
			}
		}
		list($this->markable, $this->markset, $this->userCreditInfo) = array($markable, $markset, $userInfo['credit']);
	}
	
	/**
	 * 获取post数据
	 */
	function _initPostData($tid, $pids = array()) {
		$postData = array();
		$hasTopic = false; /*是否含主题*/
		foreach ($pids as $k => $v) {
			is_numeric($v) or $hasTopic = true;
		}
		$pw_tmsgs = GetTtable($tid);
		$threadInfo = $this->db->get_one(
			"SELECT t.tid,t.fid,t.author,t.authorid,t.postdate,t.subject,t.anonymous,t.ptable,tm.content,tm.ifmark 
				FROM pw_threads t 
				LEFT JOIN $pw_tmsgs tm USING(tid) 
				WHERE t.tid=" . S::sqlEscape($tid)
		);
		if (!is_array($threadInfo)) return false;
		$GLOBALS['subject'] = $threadInfo['subject'];//GLOBAL
		$hasTopic && $postData['tpc'] = $threadInfo;

		L::loadClass('forum', 'forum', false);
		$this->fid = $threadInfo['fid'];
		$this->tid = $tid;
		$this->forum = new PwForum($this->fid);

		/*取回复数据*/
		if ($pids) {
			$pw_posts = GetPtable($threadInfo['ptable']);
			$query = $this->db->query(
				"SELECT pid,tid,fid,author,authorid,postdate,subject,ifmark,anonymous,content 
					FROM $pw_posts 
					WHERE pid IN(" . S::sqlImplode($pids) . ") AND tid=" . S::sqlEscape($tid));
			while ($rt = $this->db->fetch_array($query)) {
				$rt['subject'] or $rt['subject'] = 'RE:'.$threadInfo['subject'];
				$postData[$rt['pid']] = $rt;
			}
		}
		$this->postData = $postData;
		//return $postData;
	}
	
	/**
	 * 发送消息
	 * @param string $receiver
	 * @param array $content
	 */
	function sendMessage($receiver,$title,$content){
		global $winduid,$windid;
		$messageInfo = array(
			'create_uid'=>$winduid,
			'create_username'=>$windid,
			'title'=>$title,
			'content'=>$content
		);
		M::sendMessage(
			$winduid,
			array($receiver),
			$messageInfo,
			'sms_ratescore',
			'sms_rate'
		);
	}
	
	/**
	 * 新增一个回复
	 * @param int $tid
	 * @param string $content
	 */
	function addPost($tid,$content){
		global $timestamp;
		$tpcarray = $this->db->get_one("SELECT tid,fid,locked,subject,ifcheck,postdate,lastpost,ptable,author FROM pw_threads WHERE tid=" .S::sqlEscape($tid));
		L::loadClass('forum', 'forum', false);
		L::loadClass('post', 'forum', false);
		if($tpcarray['tid'] != $tid)return false;
		$pwforum = new PwForum($tpcarray['fid']);
		$pwpost  = new PwPost($pwforum);
		if(!$pwforum->foruminfo['allowrp'] && !$pwpost->admincheck && $GLOBALS['_G']['allowrp'] == 0){
			return 'reply_group_right';
		}elseif ($pwforum->forumset['lock']&& !$pwpost->isGM && $timestamp - $tpcarray['postdate'] > $pwforum->forumset['lock'] * 86400 && !pwRights($pwpost->isBM,'replylock')) {
			//$forumset['lock'] = $pwforum->forumset['lock'];
			return 'forum_locked';
		} elseif (!$pwpost->isGM && !$tpcarray['ifcheck'] && !pwRights($pwpost->isBM,'viewcheck')) {
			return 'reply_ifcheck';
		} elseif(!$pwpost->isGM && $tpcarray['locked']%3<>0 && !pwRights($pwpost->isBM,'replylock')) {
			return 'reply_lockatc';
		} else {
			L::loadClass('replypost', 'forum', false);
			$replypost = new replyPost($pwpost);
			
			$replypost->setTpc($tpcarray);
			$pwpost->errMode = true;
			$replypost->check();
			if ($pwpost->errMsg && $msg = reset($pwpost->errMsg)) {
				return $msg;
			}
			
			require_once(R_P . 'require/bbscode.php');
			$postdata = new replyPostData($pwpost);
			//set title
			//$title = '回 楼主(' . $tpcarray['author'].')的帖子';
			//strlen($title) <= intval($postdata->titlemax) && $postdata->setTitle($title);
			$replypost->setTpc($tpcarray);
			$postdata->setContent($content);
			$postdata->conentCheck();
			$replypost->execute($postdata);
			return true;
		}
	}
	
	/**
	 * 
	 * @param $tid
	 */
	function checkReply($tid) {
		global $timestamp,$groupid,$winddb,$winduid,$_time;
		$this->hours =& $_time['hours'];
		$tpcarray = $this->db->get_one("SELECT tid,fid,locked,ifcheck,postdate,ptable FROM pw_threads WHERE tid=" . S::sqlEscape($tid));
		if (empty($tpcarray)) {
			return false;
		}
		L::loadClass('forum', 'forum', false);
		L::loadClass('post', 'forum', false);
		$pwforum = new PwForum($tpcarray['fid']);
		$pwpost  = new PwPost($pwforum);
		if(!$pwforum->foruminfo['allowrp'] && !$pwpost->admincheck && $GLOBALS['_G']['allowrp'] == 0){
			return 'reply_group_right';
		}elseif ($pwforum->forumset['lock']&& !$pwpost->isGM && $timestamp - $tpcarray['postdate'] > $pwforum->forumset['lock'] * 86400 && !pwRights($pwpost->isBM,'replylock')) {
			return 'forum_locked';
		} elseif (!$pwpost->isGM && !$tpcarray['ifcheck'] && !pwRights($pwpost->isBM,'viewcheck')) {
			return 'reply_ifcheck';
		} elseif (!$pwpost->isGM && $tpcarray['locked']%3<>0 && !pwRights($pwpost->isBM,'replylock')) {
			return 'reply_lockatc';
		} elseif (!$pwpost->isGM && !$pwpost->forum->allowtime($this->hours) && !pwRights($pwpost->isBM, 'allowtime')) {
			return 'forum_allowtime';
		} else {
			if ($groupid == 6 || getstatus($winddb['userstatus'], PW_USERSTATUS_BANUSER)) {
				$bandb = array();
				$query = $this->db->query("SELECT * FROM pw_banuser WHERE uid=".S::sqlEscape($winduid));
				while ($rt = $this->db->fetch_array($query)) {
					if ($rt['fid'] == 0 || $rt['fid'] == $tpcarray['fid']) {
						$bandb[$rt['fid']] = $rt;
					} 
				}
				if ($bandb) return 'ban_info3';
			} 
			L::loadClass('replypost', 'forum', false);
			$replypost = new replyPost($pwpost);
			$replypost->setTpc($tpcarray);
			$pwpost->errMode = true;
			$replypost->check();
			if ($pwpost->errMsg && $msg = reset($pwpost->errMsg)) {
				return $msg;
			}
			return true;
		}
	}
	
	function pingCheck($checkType = '') {
		global $db_pingtime,$timestamp,$gp_gptype,$winduid,$windid,$manager,$_G;
		$pids = array_keys($this->postData);
		foreach ($pids as $k => $v) {
			!is_numeric($v) && $pids[$k] = 0;
		}
		foreach ($this->postData as $pid => $post) {
			if ($db_pingtime && $timestamp - $post['postdate'] > $db_pingtime*3600 && $gp_gptype != 'system') {
				return 'pingtime_over';
			}
			if ($winduid == $post['authorid'] && $checkType == 1 && !CkInArray($windid,$manager)) {
				return 'masigle_manager';
			}
		}
		if ($checkType == '1' && $_G['markable'] < 2 && $this->isPing($this->tid, $pids)) {
			return 'no_markagain';
		}
		if ($checkType == '2' && $this->isNotPing($this->tid, $pids)) {
			return 'have_not_showping';
		}
		return true;
	}

	function isPing($tid, $pids) {
		$pinglog = $this->pingList($tid, $pids);
		return !empty($pinglog);
	}

	function isNotPing($tid, $pids) {
		$pinglog = $this->pingList($tid, $pids);
		return count($pids) != count($pinglog);
	}

	function pingList($tid, $pids) {
		global $windid;
		if (empty($tid) || empty($pids)) {
			return array();
		}
		$array = array();
		$query = $this->db->query(
			"SELECT pid FROM `pw_pinglog` 
				WHERE tid=" . S::sqlEscape($tid) . " 
					AND pid IN(" . S::sqlImplode($pids) . ") 
					AND pinger=" . S::sqlEscape($windid) . ' GROUP BY pid'
		);
		while ($row = $this->db->fetch_array($query)) {
			$array[$rt['pid']] = 1;
		}
		return $array;
	}
	
	function update_markinfo($tid, $pid) {
		$perpage = 10;
		$pid = intval($pid);
		$creditnames = pwCreditNames();
		$whereStr = " tid=" . S::sqlEscape($tid) . " AND pid=" . S::sqlEscape($pid) . " AND ifhide=0 ";
		$count = 0;
		$creditCount = array();
		$query = $this->db->query("SELECT COUNT(*) AS count,name,SUM(point) AS sum FROM pw_pinglog WHERE $whereStr GROUP BY name");
		while ($rt = $this->db->fetch_array($query)) {
			$count += $rt['count'];
			if (isset($creditnames[$rt['name']])) {
				$creditCount[$rt['name']] += $rt['sum'];
			} elseif (in_array($rt['name'], $creditnames)) {
				$key = array_search($rt['name'], $creditnames);
				$creditCount[$key] += $rt['sum'];
			}
		}
		$markInfo = '';
		if ($count) {
			$query = $this->db->query("SELECT id FROM pw_pinglog WHERE $whereStr ORDER BY id DESC LIMIT 0,$perpage");
			$ids = array();
			while ($rt = $this->db->fetch_array($query)) {
				$ids[] = $rt['id'];
			}
			$markInfo = $count . ":" . implode(",", $ids);
			if ($creditCount) {
				$tmp = array();
				foreach ($creditCount as $key => $value) {
					$tmp[] = $key . '=' . $value;
				}
				$markInfo .= ':' . implode(',', $tmp);
			}
		}
		if ($pid == 0) {
			$pw_tmsgs = GetTtable($tid);
			//* $this->db->update("UPDATE $pw_tmsgs SET ifmark=" . S::sqlEscape($markInfo) . " WHERE tid=" . S::sqlEscape($tid));
			pwQuery::update($pw_tmsgs, 'tid=:tid', array($tid), array('ifmark'=>$markInfo));
		} else {
			$this->db->update("UPDATE ".GetPtable("N",$tid)." SET ifmark=".S::sqlEscape($markInfo)." WHERE pid=".S::sqlEscape($pid));
		}
		return $markInfo;
	}
	

	function getPingLogs($tid, $pingIdArr) {
		if (empty($pingIdArr)) return array();
		global $db,$fid;
		static $creditnames;
		is_array($creditnames) or $creditnames = pwCreditNames();
		$pingIds = array();
		$pingLogs = array();
		foreach ($pingIdArr as $pid => $markInfo) {
			list($count, $ids, $creditCount) = explode(":", $markInfo);
			$pingLogs[$pid]['count'] = $count;
			$pingLogs[$pid]['creditCount'] = $this->parseCreditCount($creditCount);
			$pingIds = array_merge($pingIds, explode(",", $ids));
		}
		if (!count($pingIds)) return array();
		$query = $this->db->query("SELECT a.*,b.uid,b.icon FROM pw_pinglog a LEFT JOIN pw_members b ON a.pinger=b.username WHERE a.id IN (" . S::sqlImplode($pingIds) . ")");
		while ($rt = $this->db->fetch_array($query)) {
			$rt['pid'] = $rt['pid'] ? $rt['pid'] : 'tpc';
			list($rt['pingtime'],$rt['pingdate']) = getLastDate($rt['pingdate']);
			$rt['record'] = $rt['record'] ? $rt['record'] : "-";
			if ($rt['point'] > 0) $rt['point'] = "+" . $rt['point'];
			$tmp = showfacedesign($rt['icon'],true,'s');
			$rt['icon'] = $tmp[0];
			isset($creditnames[$rt['name']]) && $rt['name'] = $creditnames[$rt['name']];
			$pingLogs[$rt['pid']]['data'][$rt['id']] = $rt;
		}
		foreach ($pingLogs as $pid => $data) {
			if (is_array($pingLogs[$pid]['data'])) krsort($pingLogs[$pid]['data']);
		}
		return $pingLogs;
	}
	
	function parseCreditCount($creditCount) {
		if (!$creditCount) return array();
		$arr = explode(',', $creditCount);
		$array = array();
		foreach ($arr as $value) {
			list($cType, $cValue) = explode('=', $value);
			$array[$cType] = ($cValue > 0 ? '+' : '') . $cValue;
		}
		return $array;
	}
}