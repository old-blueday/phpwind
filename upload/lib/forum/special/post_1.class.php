<?php
!defined('P_W') && exit('Forbidden');

//mode 1
class postSpecial {

	var $db;
	var $post;
	var $forum;

	var $data;
	var $special = 1;

	var $maxselect;

	function postSpecial($post) {
		global $db,$db_selcount;
		$this->db =& $db;
		$this->post =& $post;
		$this->forum =& $post->forum;

		$this->maxselect =& $db_selcount;
		$this->data = array(
			'tid'			=> 0,	'voteopts'		=> '',
			'modifiable'	=> 0,	'previewable'	=> 0,
			'multiple'		=> 0,	'mostvotes'		=> 0,
			'timelimit'		=> 0,	'leastvotes'	=> 0,
			'regdatelimit' 	=> 0,	'creditlimit' 	=> '',
			'postnumlimit' 	=> 0
		);
	}

	function postCheck() {
		global $groupid;
		if (($this->forum->foruminfo['allowpost'] && strpos($this->forum->foruminfo['allowpost'],','.$groupid.',') === false)  && !$this->post->admincheck && $this->post->_G['allownewvote'] == 0) {
			Showmsg('postnew_group_vote');
		}
	}

	function resetInfo($tid, $atcdb) {
		global $timestamp;
		$reset = $this->db->get_one("SELECT * FROM pw_polls WHERE tid=" . S::sqlEscape($tid));
		$reset['votearray'] = unserialize($reset['voteopts']);
		$reset['multi'] = $reset['ifmodify'] = $reset['ifpreview'] = '';
		$reset['multiple'] && $reset['multi'] = 'checked';
		$reset['modifiable'] && $reset['ifmodify'] = 'checked';
		$reset['previewable'] && $reset['ifpreview'] = 'checked';
		$reset['close'] = ($atcdb['state'] || ($reset['timelimit'] && $timestamp - $atcdb['postdate'] > $reset['timelimit'] * 86400)) ? 1 : 0;
		$reset['voteable'] = ($this->post->_G['modifyvote'] && $reset['close'] == 0) ? '' : 'disabled';
		$reset['regdatelimit'] = $reset['regdatelimit'] ? get_date($reset['regdatelimit'], 'Y-m-d') : '';
		$reset['postnumlimit'] = $reset['postnumlimit'] > 0 ? $reset['postnumlimit'] : '';
		$creditlimit = unserialize($reset['creditlimit']);
		empty($creditlimit) && $creditlimit = array();
		foreach ($creditlimit as $key => $value) {
			$reset['ifcreditlimit' . $key] = $value;
		}
		return $reset;
	}

	function setOpts($opts, $oldopts = array()) {
		$array = array();
		foreach ($opts as $key => $value) {
			if ($value = trim($value)) {
				$array[$key] = array(stripslashes($value), isset($oldopts[$key][1]) ? $oldopts[$key][1] : 0);
			}
		}
		$vtcount = count($array);
		if ($vtcount < 1) {
			Showmsg('postfunc_noempty');
		} elseif ($vtcount > $this->maxselect) {
			Showmsg('vote_num_limit');
		}
		$this->data['voteopts'] = serialize($array);

		return $vtcount;
	}

	function setUp($vtcount) {
		global $timestamp;
		$regdatelimit = S::getGP('regdatelimit', 'P');
		$multiplevote = intval(S::getGP('multiplevote', 'P'));
		$mostvotes = intval(S::getGP('mostvotes', 'P'));
		$timelimit = intval(S::getGP('timelimit', 'P'));
		$modifiable = intval(S::getGP('modifiable', 'P'));
		$previewable = intval(S::getGP('previewable', 'P'));
		$leastvotes = intval(S::getGP('leastvotes', 'P'));
		$postnumlimit = intval(S::getGP('postnumlimit', 'P'));
		$creditlimit = S::getGP('creditlimit', 'P');

		if (empty($multiplevote)) {
			$mostvotes = 1;
		} elseif ($mostvotes > $vtcount || $mostvotes < 1) {
			$mostvotes = $vtcount;
		}
		if (empty($multiplevote) || $leastvotes > $mostvotes || $leastvotes < 1) {
			$leastvotes = 1;
		}
		$timelimit < 0 && $timelimit = 0;
		$postnumlimit < 0 && $postnumlimit = 0;
		$regdatelimit = strtotime($regdatelimit);
		$regdatelimit = $regdatelimit > $timestamp ? $timestamp : $regdatelimit;

		$creditlimit_temp = array();
		foreach ($creditlimit as $key => $value) {
			if (!empty($value)) {
				$creditlimit_temp[$key] = (int)$value;
			}
		}
		$this->data['modifiable'] = $modifiable;
		$this->data['previewable'] = $previewable;
		$this->data['multiple'] = $multiplevote;
		$this->data['mostvotes'] = $mostvotes;
		$this->data['leastvotes'] = $leastvotes;
		$this->data['timelimit'] = $timelimit;
		$this->data['regdatelimit'] = $regdatelimit;
		$this->data['creditlimit'] = serialize($creditlimit_temp);
		$this->data['postnumlimit'] = $postnumlimit;
	}

	function initData() {
		$vt_select = explode("\n", S::escapeChar(S::getGP('vt_select', 'P')));
		$vtcount = $this->setOpts($vt_select);
		$this->setUp($vtcount);
	}

	function insertData($tid) {
		$this->data['tid'] = $tid;
		$this->db->update("INSERT INTO pw_polls SET " . S::sqlSingle($this->data));
	}

	function modifyData($tid) {
		$voteopts = $this->db->get_value("SELECT voteopts FROM pw_polls WHERE tid=" . S::sqlEscape($tid));
		$voteopts = unserialize($voteopts);
		$vt_selarray = S::escapeChar(S::getGP('vt_selarray', 'P'));

		if ($this->post->_G['modifyvote'] && is_array($voteopts) && is_array($vt_selarray)) {
			$vtcount = $this->setOpts($vt_selarray, $voteopts);
			$this->setUp($vtcount);
		}
		if (S::getGP('vote_close')) {
			//$this->db->update("UPDATE pw_threads SET state='1' WHERE tid=" . S::sqlEscape($tid));
			pwQuery::update('pw_threads', 'tid=:tid', array($tid), array('state'=>1));
			
			//临时修改，待改进
			//* $threads = L::loadClass('Threads', 'forum');
			//* $threads->delThreads($tid);
		}
	}

	function updateData($tid) {
		if ($this->data['voteopts']) {
			$this->data['tid'] = $tid;
			$this->db->update("UPDATE pw_polls SET " . S::sqlSingle($this->data) . ' WHERE tid=' . S::sqlEscape($tid));
		}
	}
}
?>