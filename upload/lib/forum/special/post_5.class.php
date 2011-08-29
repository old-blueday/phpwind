<?php
!defined('P_W') && exit('Forbidden');

//mode 5
//辩论
class postSpecial {

	var $db;
	var $post;
	var $forum;

	var $data;
	var $special = 5;

	function postSpecial($post) {
		global $db;
		$this->db =& $db;
		$this->post =& $post;
		$this->forum =& $post->forum;

		$this->data = array(
			'tid'		=> 0,					'authorid'	=> $this->post->uid,
			'postdate'	=> 0,					'obtitle'	=> '',
			'retitle'	=> '',					'endtime'	=> 0,
			'umpire'	=> ''
		);
	}

	function postCheck() {
		if (!$this->post->_G['allowdebate']) {
			Showmsg('postnew_group_debate');
		}
	}

	function setInfo() {
		return array('umpire' => $this->post->username);
	}

	function resetInfo($tid, $atcdb) {
		global $timestamp;
		$reset = $this->db->get_one("SELECT obtitle,retitle,endtime,umpire,judge FROM pw_debates WHERE tid=" . S::sqlEscape($tid));
		$reset['debateable'] = (!$reset['judge'] && $reset['endtime'] > $timestamp) ? '' : "disabled";
		$reset['endtime'] = get_date($reset['endtime'], "Y-m-d H:i");

		return $reset;
	}

	function _setData() {
		global $timestamp;
		$endtime = S::escapeChar(S::getGP('endtime'));
		$obtitle = S::escapeChar(S::getGP('obtitle'));
		$retitle = S::escapeChar(S::getGP('retitle'));
		$umpire  = S::escapeChar(S::getGP('umpire'));

		$endtime = PwStrtoTime($endtime);
		$endtime < $timestamp && Showmsg('debate_time');
		if (empty($obtitle) || empty($retitle)) {
			Showmsg('debate_notitle');
		} elseif (strlen($obtitle) > 255 || strlen($retitle) > 255) {
			Showmsg('debate_titlelen');
		}
		if ($umpire) {
			$umpireuid = $this->db->get_value("SELECT uid FROM pw_members WHERE username=" . S::sqlEscape($umpire));
			empty($umpireuid) && Showmsg('debate_noumpire');
		}
		$this->data['endtime'] = $endtime;
		$this->data['obtitle'] = $obtitle;
		$this->data['retitle'] = $retitle;
		$this->data['umpire']  = $umpire;
		$this->data['postdate']  = $timestamp;
	}

	function initData() {
		$this->_setData();
		!$this->data['umpire'] && $this->data['umpire'] = $this->post->username;
	}

	function insertData($tid) {
		$this->data['tid'] = $tid;
		$this->db->update("INSERT INTO pw_debates SET " . S::sqlSingle($this->data));
	}

	function modifyData($tid) {
		$debate = $this->db->get_one("SELECT endtime,judge FROM pw_debates WHERE tid=" . S::sqlEscape($tid));
		if ($debate && (!$debate['judge'] && $debate['endtime'] > $timestamp)) {
			$this->_setData();
		}
	}

	function updateData($tid) {
		$this->db->update("UPDATE pw_debates SET " . S::sqlSingle(array(
			'obtitle'	=> $this->data['obtitle'],
			'retitle'	=> $this->data['retitle'],
			'endtime'	=> $this->data['endtime'],
			'umpire'	=> $this->data['umpire'],
			'judge'		=> 0
		)) . "WHERE tid=" . S::sqlEscape($tid));
	}

	function reply($tid, $pid) {
		global $timestamp;
		$standpoint = (int)S::getGP('standpoint');
		if ($standpoint == 1 || $standpoint == 2) {
			$pwSQL = array();
			$count = $upSQL = '';
			$debatestand = $this->db->get_value("SELECT standpoint FROM pw_debatedata WHERE pid='0' AND tid=" . S::sqlEscape($tid) . " AND authorid=" . S::sqlEscape($this->post->uid));
			if ($debatestand) {
				$count = $this->db->get_value("SELECT COUNT(*) as count FROM pw_debatedata WHERE pid>'0' AND tid=" . S::sqlEscape($tid) . " AND authorid=" . S::sqlEscape($this->post->uid));
				$standpoint	= $debatestand;
			}
			$pwSQL[] = array(
				'tid'			=> $tid,
				'pid'			=> $pid,
				'authorid'		=> $this->post->uid,
				'standpoint'	=> $standpoint,
				'postdate'		=> $timestamp,
				'vote'			=> 0,
				'voteids'		=> ''
			);
			if (empty($count)) {
				if ($standpoint == 1) {
					$upSQL = 'obposts=obposts+1';
				} else {
					$upSQL = 'reposts=reposts+1';
				}
			}
			if (!$debatestand) {
				$pwSQL[] = array(
					'tid'			=> $tid,
					'pid'			=> 0,
					'authorid'		=> $this->post->uid,
					'standpoint'	=> $standpoint,
					'postdate'		=> $timestamp,
					'vote'			=> 0,
					'voteids'		=> ''
				);
				if ($standpoint == 1) {
					$upSQL .= ($upSQL ? ',' : '').'obvote=obvote+1';
				} else {
					$upSQL .= ($upSQL ? ',' : '').'revote=revote+1';
				}
			}
			$upSQL && $this->db->update("UPDATE pw_debates SET $upSQL WHERE tid=" . S::sqlEscape($tid));
			$pwSQL && $this->db->update("INSERT INTO pw_debatedata (tid,pid,authorid,standpoint,postdate,vote,voteids) VALUES " . S::sqlMulti($pwSQL));
		}
	}
}
?>