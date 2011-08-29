<?php
!defined('P_W') && exit('Forbidden');

/**
 * VoteThread
 * 
 * @package Thread
 */
class postVote {
	
	var $db;
	var $post;
	var $forum;
	
	var $data;
	var $special = 1;
	
	var $maxselect;
	
	function postVote($post) {
		global $db, $db_selcount;
		$this->db = & $db;
		$this->post = & $post;
		$this->forum = & $post->forum;
		
		$this->maxselect = & $db_selcount;
		$this->data = array(
			'tid' => 0,
			'voteopts' => '',
			'modifiable' => 0,
			'previewable' => 0,
			'multiple' => 0,
			'mostvotes' => 0,
			'timelimit' => 0,
			'leastvotes' => 0,
			'regdatelimit' => 0,
			'creditlimit' => '',
			'postnumlimit' => 0
		);
	}
	
	function postCheck() {
		global $groupid;
		if (($this->forum->foruminfo['allowpost'] && strpos($this->forum->foruminfo['allowpost'], ',' . $groupid . ',') === false) && !$this->post->admincheck && $this->post->_G['allownewvote'] == 0) {
			Showmsg('postnew_group_vote');
		}
	}
	
	function initData() {
		global $timestamp;
		!$_POST['vt_select'] && Showmsg('postfunc_noempty');
		$vt_select = S::getGP('vt_select', 'P');
		$vt_select = explode("\n", $vt_select);
		$votearray = array();
		foreach ($vt_select as $key => $option) {
			if ($option = trim($option)) {
				$votearray[] = array(
					stripslashes($option),
					0
				);
			}
		}
		$vtcount = count($votearray);
		if ($vtcount > $this->maxselect) {
			Showmsg('vote_num_limit');
		}
		$regdatelimit = S::getGP('regdatelimit', 'P');
		$multiplevote = intval(S::getGP('multiplevote', 'P'));
		$mostvotes = intval(S::getGP('mostvotes', 'P'));
		$timelimit = intval(S::getGP('timelimit', 'P'));
		$modifiable = intval(S::getGP('modifiable', 'P'));
		$previewable = intval(S::getGP('previewable', 'P'));
		$leastvotes = intval(S::getGP('leastvotes', 'P'));
		$postnumlimit = intval(S::getGP('postnumlimit', 'P'));
		
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
		
		$creditlimit = S::getGP('creditlimit', 'P');
		$creditlimit_temp = array();
		foreach ($creditlimit as $key => $value) {
			if (!empty($value)) {
				$creditlimit_temp[$key] = (int) $value;
			}
		}
		$this->data['voteopts'] = serialize($votearray);
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
	
	function insertData($tid) {
		$this->data['tid'] = $tid;
		$this->db->update("INSERT INTO pw_polls SET " . S::sqlSingle($this->data));
	}
}
?>