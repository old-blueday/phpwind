<?php
!defined('P_W') && exit('Forbidden');

require_once(R_P . 'apps/groups/lib/colony.class.php');

class PwColonyPost extends PwColony {
	
	function PwColonyPost($cyid) {
		parent::PwColony($cyid);
	}

	function check($checkU = 1) {
		global $winduid;
		if (!$winduid) {
			return 'not_login';
		}
		if ($this->getIfadmin() || $this->info['ifFullMember']) {
			return true;
		}
		if (!$this->info['ifopen']) {
			return 'colony_cnmenber';
		}
		if ($checkU || empty($this->info['classid'])) {
			if ($this->info['ifadmin'] == '-1') {
				return 'colony_post';
			}
			return 'colony_post2';
		}
		return true;
	}

	function topicCheck() {
		return $this->check(1);
	}

	function replyCheck() {
		return $this->check(0);
	}

	function modifyLockedRight() {
		return $this->getIfadmin();
	}

	function setTopicPostData($postdata) {
		if (empty($this->info['classid']) || empty($this->info['iftopicshowinforum'])) {
			$postdata['ifcheck'] = 2;
		}
		setstatus($postdata['tpcstatus'], 1);
		return $postdata;
	}

	function setTopicModifyPostData($postdata) {
		if (empty($this->info['classid']) || empty($this->info['iftopicshowinforum'])) {
			$postdata['ifcheck'] = 2;
		}
		return $postdata;
	}

	function topicPost($tid, $postdata) {
		global $timestamp, $winduid, $windid;

		if (!$this->info) {
			return false;
		}
		$this->_db->update("INSERT INTO pw_argument SET " . S::sqlSingle(array(
			'tid'		=> $tid,
			'cyid'		=> $this->info['id'],
			'postdate'	=> $timestamp,
			'lastpost'	=> $timestamp
		)));
		
		if ($postdata['ifcheck'] > 0) {
			require_once(R_P . 'u/require/core.php');
			//tnum加一
			//* $this->_db->update("UPDATE pw_colonys SET tnum=tnum+'1',pnum=pnum+'1',todaypost=todaypost+'1' WHERE id=" . S::sqlEscape($this->cyid));
			$this->_db->update(pwQuery::buildClause("UPDATE :pw_table SET tnum=tnum+1,pnum=pnum+1,todaypost=todaypost+1 WHERE id=:id", array('pw_colonys',$this->cyid)));
			$this->info['tnum']++;
			$this->info['pnum']++;
			updateGroupLevel($this->cyid, $this->info);
			
	
			if($this->info['ifopen']){
				$weiboService = L::loadClass('weibo','sns');/* @var $weiboService PW_Weibo */ 
				$weiboContent = substrs(stripWindCode($postdata['content']), 125);
				$weiboExtra = array(
						'cyid' => $this->cyid,
						'title' => stripslashes($postdata['title']),
						'cname' => $this->info['cname'],
					);
				$weiboService->send($winduid,$weiboContent,'group_article',$tid,$weiboExtra);
			}
		}
		//更新群成员表里面的最后发言时间
		$this->_db->update("UPDATE pw_cmembers SET lastpost=" . S::sqlEscape($timestamp) . " WHERE uid=" . S::sqlEscape($winduid));
	}

	function replyPost($pid, $tid, $postdata) {
		global $timestamp, $winduid;

		if (!$this->info) {
			return false;
		}
		require_once(R_P . 'u/require/core.php');
		$count = $this->_db->get_value("SELECT COUNT(*) AS count FROM pw_group_replay WHERE uid=" . S::sqlEscape($postdata['authorid']) . " AND tid=" . S::sqlEscape($tid) . " AND is_read=0");
		if ($count == 0) {
			$pwSQL = array(
				'uid'		=> $postdata['authorid'],
				'tid'		=> $tid,
				'cyid'		=> $this->cyid,
				'is_read'	=> 0,
				'num'		=> 1,
				'add_time'	=> $timestamp
			);
			$this->_db->update('INSERT INTO pw_group_replay SET ' . S::sqlSingle($pwSQL));
		} else { 
			$this->_db->update('UPDATE pw_group_replay SET add_time=' . S::sqlEscape($timestamp) . ',num=num +1  WHERE uid=' . S::sqlEscape($postdata['authorid'])." and tid=" . S::sqlEscape($tid));
		}
		
		//$this->_db->update("UPDATE pw_threads SET lastpost=".S::sqlEscape($timestamp)." WHERE tid=".S::sqlEscape($tid)." AND locked<3 AND lastpost <".S::sqlEscape($timestamp));
		pwQuery::update('pw_threads', 'tid=:tid AND locked<:locked AND lastpost<:lastpost' , array($tid, 3, $timestamp), array('lastpost'=>$timestamp));
		$thread_locked = $this->_db->get_value("SELECT locked FROM pw_threads WHERE tid=".S::sqlEscape($tid));
		if ($thread_locked < 3) {
			$this->_db->update('UPDATE pw_argument SET lastpost=' . S::sqlEscape($timestamp) . ' WHERE tid=' . S::sqlEscape($tid) . ' AND lastpost< '.S::sqlEscape($timestamp));
		}
		//* $this->_db->update("UPDATE pw_colonys SET pnum=pnum+'1',todaypost=todaypost+1 WHERE id=" . S::sqlEscape($this->cyid));
		$this->_db->update(pwQuery::buildClause("UPDATE :pw_table SET pnum=pnum+1,todaypost=todaypost+1 WHERE id=:id", array('pw_colonys',$this->cyid)));
		$this->info['pnum']++;
		updateGroupLevel($this->info['id'], $this->info);

		//更新群成员表里面的最后发言时间
		$this->_db->update("UPDATE pw_cmembers SET lastpost=" . S::sqlEscape($timestamp) . " WHERE uid=" . S::sqlEscape($winduid));
	}

	function modifyRight() {
		return $this->getIfadmin();
	}

	function topicModify($tid, $postdata) {
		if ($postdata['ifcheck']) {
			$actions = '+';
			$this->info['tnum']++;
		} else {
			$actions = '-';
			$this->info['tnum']--;
		}
		//* $this->_db->update("UPDATE pw_colonys SET tnum=tnum{$actions}'1' WHERE id=" . S::sqlEscape($this->cyid));
		$this->_db->update(pwQuery::buildClause("UPDATE :pw_table SET tnum=tnum{$actions}'1' WHERE id=:id", array('pw_colonys',$this->cyid)));
		updateGroupLevel($this->cyid, $this->info);
	}
}
?>