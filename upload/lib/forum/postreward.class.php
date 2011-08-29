<?php
!defined('P_W') && exit('Forbidden');

/**
 * RewardThread
 * 
 * @package Thread
 */
class postReward {
	
	var $db;
	var $post;
	var $forum;
	
	var $data;
	var $special = 3;
	
	var $valid;
	var $b_val;
	var $a_val;
	var $allowcredit = array();
	
	function postReward($post) {
		global $db;
		$this->db = & $db;
		$this->post = & $post;
		$this->forum = & $post->forum;
		
		list($this->valid, $this->b_val, $this->a_val, $this->allowcredit) = explode("\t", $this->forum->forumset['rewarddb']);
		!$this->valid && $this->valid = 10;
		$this->allowcredit = explode(',', $this->allowcredit);
		
		$this->data = array(
			'tid' => 0,
			'cbtype' => '',
			'catype' => '',
			'cbval' => 0,
			'caval' => 0,
			'timelimit' => 0
		);
	}
	
	function postCheck() {
		if (!$this->post->_G['allowreward']) {
			Showmsg('postnew_group_reward');
		}
	}
	
	function getCtype() {
		$sel = '';
		$ctype = pwCreditNames();
		foreach ($this->allowcredit as $key => $val) {
			$sel .= "<option value=\"$val\">" . $ctype[$val] . "</option>";
		}
		return $sel;
	}
	
	function setData() {
		$bonus = S::escapeChar(S::getGP('bonus', 'P'), true);
		$ctype = S::escapeChar(S::getGP('ctype', 'P'));
		
		$bonus['best'] < $this->b_val && Showmsg('credit_limit');
		$bonus['active'] < $this->a_val && Showmsg('credit_limit');
		reset($this->allowcredit);
		if (!$ctype['best']) $ctype['best'] = current($this->allowcredit);
		if (!$ctype['active']) $ctype['active'] = current($this->allowcredit);
		if (!in_array($ctype['best'], $this->allowcredit) || !in_array($ctype['active'], $this->allowcredit)) {
			Showmsg('reward_credit_error');
		}
		$this->data['cbtype'] = $ctype['best'];
		$this->data['catype'] = $ctype['active'];
		$this->data['cbval'] = $bonus['best'];
		$this->data['caval'] = $bonus['active'];
	}
	
	function addCredit($action) {
		global $credit, $onlineip;
		require_once (R_P . 'require/credit.php');
		
		$logdata = array(
			'uid' => $this->post->uid,
			'username' => $this->post->username,
			'ip' => $onlineip,
			'fname' => $this->forum->name,
			'cbtype' => $credit->cType[$this->data['cbtype']],
			'cbval' => $this->data['cbval'],
			'catype' => $credit->cType[$this->data['catype']],
			'caval' => $this->data['caval']
		);
		if ($this->data['cbtype'] == $this->data['catype']) {
			$reduce = $this->data['cbval'] * 2 + $this->data['caval'];
			$credit->get($this->post->uid, $this->data['cbtype']) < $reduce && Showmsg('reward_credit_limit');
			$credit->addLog('reward_' . $action, array(
				$this->data['cbtype'] => -$reduce
			), $logdata);
			$credit->set($this->post->uid, $this->data['cbtype'], -$reduce, false);
		} else {
			foreach (array(
				'cb',
				'ca'
			) as $key => $val) {
				$ctype = $this->data[$val . 'type'];
				$cval = $this->data[$val . 'val'];
				$reduce = $cval * ($val == 'cb' ? 2 : 1);
				$credit->get($this->post->uid, $ctype) < $reduce && Showmsg('reward_credit_limit');
				$credit->addLog('reward_' . $action, array(
					$ctype => -$reduce
				), $logdata);
				$credit->set($this->post->uid, $ctype, -$reduce, false);
			}
		}
	}
	
	function initData() {
		$this->setData();
		if ($this->data['cbval'] == 0 && $this->data['caval'] == 0) {
			$this->special = 0;
		} else {
			$this->addCredit('new');
		}
	}
	
	function insertData($tid) {
		if ($this->special != 0) {
			global $timestamp;
			$this->data['tid'] = $tid;
			$this->data['timelimit'] = $timestamp + $this->valid * 86400;
			$this->db->update("INSERT INTO pw_reward SET " . S::sqlSingle($this->data));
		}
	}
}
?>