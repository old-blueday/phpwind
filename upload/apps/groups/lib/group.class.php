<?php
!defined('P_W') && exit('Forbidden');

class PwGroup {
	var $db;
	var $colony;
	var $colonyid;
	var $classid;
	
	function PwGroup($colony) {
		global $db;
		$this->db =& $db;
		$this->colony =& $colony;
		$this->colonyid =& $colony['id'];
		$this->classid =& $colony['classid'];
	
	}

	function getGroupCreditset($type) {
		$creditset = unserialize($this->colony['creditset']);
		$creditset = array_filter($creditset[$type],"group_filter");
		$creditset = is_array($creditset) ? $creditset : array();
		return $creditset;
	}

	function getGroupcateCreditset($type) {
		$creditset = $this->db->get_value("SELECT creditset FROM pw_cnclass WHERE fid=".S::sqlEscape($this->classid));
		$creditset = unserialize($creditset);
		$creditset = array_filter($creditset[$type],"group_filter");
		$creditset = is_array($creditset) ? $creditset : array();
		return $creditset;
	}

	function getGroupsCreditset($type) {
		global $o_groups_creditset;
		//* include_once pwCache::getPath(D_P.'data/bbscache/o_config.php');
		extract(pwCache::getData(D_P.'data/bbscache/o_config.php', false));
		$creditset = array_filter($creditset[$type],"group_filter");
		$creditset = is_array($creditset) ? $creditset : array();
		
		return $creditset;
	}

	function addArgument($tid){
		global $timestamp;
		$this->db->update("INSERT INTO pw_argument SET " . S::sqlSingle(array('tid' => $tid, 'cyid' => $this->colonyid, 'postdate' => $timestamp, 'lastpost' => $timestamp)));
	}

}

function group_filter($value) {
		
	if(empty($value)) {
		return false;
	}
	return true;

}

function getGroupByCyid($cyid) {
	global $winduid,$db;
	$colony = $db->get_one("SELECT c.*,cm.id AS ifcyer,cm.ifadmin,cm.lastvisit FROM pw_colonys c LEFT JOIN pw_cmembers cm ON c.id=cm.colonyid AND cm.uid=" . S::sqlEscape($winduid) . ' WHERE c.id=' . S::sqlEscape($cyid));
	empty($colony) && Showmsg('data_error');
	return $colony;
}


?>