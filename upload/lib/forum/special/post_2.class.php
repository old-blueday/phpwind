<?php
!defined('P_W') && exit('Forbidden');

//mode 2
//活动
class postSpecial {

	var $db;
	var $post;
	var $forum;

	var $data;
	var $special = 2;

	function postSpecial($post) {
		global $db,$db_selcount;
		//Showmsg('post_allowtype');//暂时取消活动帖功能
		$this->db =& $db;
		$this->post =& $post;
		$this->forum =& $post->forum;

		$this->data = array(
			'tid'		=> 0,						'subject'	=> '',
			'admin'		=> $this->post->uid,		'starttime'	=> 0,
			'endtime'	=> 0,						'location'	=> '',
			'num'		=> 0,						'sexneed'	=> 0,
			'costs'		=> 0,						'deadline'	=> 0
		);
	}

	function postCheck() {
		if (!$this->post->_G['allowactive']) {
			Showmsg('postnew_group_active');
		}
	}

	function setInfo() {
		$set = array(
			'sel_0' => 'checked',
			'sel_1' => '',
			'sel_2' => ''
		);
		return $set;
	}

	function resetInfo($tid, $atcdb) {
		$reset = $this->db->get_one("SELECT * FROM pw_activity WHERE tid=" . S::sqlEscape($tid));
		$reset['starttime'] = get_date($reset['starttime'], "Y-m-d H:i");
		$reset['endtime']   = get_date($reset['endtime'], "Y-m-d H:i");
		$reset['deadline']  = get_date($reset['deadline'], "Y-m-d H:i");
		$reset['sel_' . $reset['sexneed']] = 'checked';

		return $reset;
	}

	function _setData() {
		$this->data['subject']	= S::escapeChar(S::getGP('act_subject', 'P'));
		$this->data['location']	= S::escapeChar(S::getGP('act_location', 'P'));
		$this->data['sexneed']	= intval(S::getGP('act_sex'));
		$act_starttime	= S::escapeChar(S::getGP('act_starttime'));
		$act_deadline	= S::escapeChar(S::getGP('act_deadline'));
		$act_endtime	= S::escapeChar(S::getGP('act_endtime'));
		$act_num		= intval(S::getGP('act_num'));
		$act_costs		= intval(S::getGP('act_costs'));

		!($this->data['subject'] && $act_starttime && $act_deadline) && Showmsg('active_data_empty');
		$act_starttime= PwStrtoTime($act_starttime);
		$act_endtime  = PwStrtoTime($act_endtime);
		$act_deadline = PwStrtoTime($act_deadline);

		$act_num < 1 && $act_num = 0;
		$act_costs < 1 && $act_costs = 0;
		
		$this->data['starttime'] = $act_starttime;
		$this->data['deadline'] = $act_deadline;
		$this->data['endtime'] = $act_endtime;
		$this->data['num'] = $act_num;
		$this->data['costs'] = $act_costs;
	}

	function initData() {
		global $timestamp;
		$this->_setData();
		$this->data['starttime'] < $timestamp && Showmsg('starttime_limit');
		$this->data['deadline'] < $timestamp && Showmsg('deadline_limit');
		$this->data['endtime'] && $this->data['deadline'] > $this->data['endtime'] && Showmsg('deadline_endtime_limit');
		$this->data['endtime'] && $this->data['starttime'] > $this->data['endtime'] && Showmsg('endtime_limit');
	}

	function insertData($tid) {
		$this->data['tid'] = $tid;
		$this->db->update("INSERT INTO pw_activity SET " . S::sqlSingle($this->data));
	}

	function modifyData($tid) {
		global $timestamp;
		$this->_setData();
		$act = $this->db->get_one("SELECT * FROM pw_activity WHERE tid=" . S::sqlEscape($tid));

		if ($timestamp > $act['starttime'] && $act['starttime'] != $this->data['starttime']) {
			Showmsg('can_not_modify_start');
		}
		if ($this->data['starttime'] < $timestamp && $act['starttime'] != $this->data['starttime']) {
			Showmsg('starttime_limit');
		}
		if ($this->data['endtime'] < $timestamp && $act['endtime'] > $this->data['endtime']) {
			Showmsg('can_not_end');
		}
		if ($this->data['endtime'] && $this->data['starttime'] > $this->data['endtime']) {
			Showmsg('endtime_limit');
		}
		if ($this->data['endtime'] && $this->data['deadline'] > $this->data['endtime'] && $this->data['deadline'] != $act['deadline']) {
			Showmsg('deadline_endtime_limit');
		}
		extract($this->db->get_one('SELECT COUNT(*) AS count FROM pw_actmember WHERE actid=' . S::sqlEscape($tid) . ' AND state=1'));
		if ($this->data['num'] != 0 && $this->data['num'] < $count) {
			Showmsg('active_num_limit');
		}
	}

	function updateData($tid) {
		$this->db->update("UPDATE pw_activity SET " . S::sqlSingle(array(
			'subject'	=> $this->data['subject'],
			'starttime'	=> $this->data['starttime'],
			'endtime'	=> $this->data['endtime'],
			'location'	=> $this->data['location'],
			'num'		=> $this->data['num'],
			'sexneed'	=> $this->data['sexneed'],
			'costs'		=> $this->data['costs'],
			'deadline'	=> $this->data['deadline']
		)) . " WHERE tid=" . S::sqlEscape($tid));
	}
}
?>