<?php
!defined('W_P') && exit('Forbidden');
class MyspaceDB {
	var $db;
	var $perPage = 2;
	
	function MyspaceDB(){
		$this->__construct();
	}
	
	function __construct(){
		global $db;
		global $wap_perpage;
		$this->db = & $db;
		$this->perPage = & $wap_perpage;
	}
	
	
	/**
	 * @param $perPage		每页显示条数	
	 * @param $perPage the $perPage to set
	 */
	function setPerPage($perPage) {
		$this->perPage = $perPage;
	}

	
	/**
	 * @param $uid			用户ID
	 * @param $page			当前页数
	 * @return unknown_type	返回类型
	 */
	function getArticlesByUser($uid,$page = 1){
		$uid = (int) $uid;
		if (!$uid) return array();
		(int)$page < 1 && $page = 1;
		$start = ($page - 1) * $this->perPage;
		$limit = pwLimit($start,$this->perPage);
		$sql = "SELECT t.fid,t.tid,t.subject,t.hits,t.topped,t.digest,t.ifupload,t.replies,t.lastpost FROM pw_threads t 
				WHERE t.authorid = ".pwEscape($uid)." AND t.fid != '0' ORDER BY t.lastpost DESC $limit";
		$result = $this->_query($sql,$start,'ar');
		return $result;
	}
	

	/**
	 * @param $uid			用户ID
	 * @param $page			当前页数
	 * @return unknown_type	返回类型
	 */
	function getReplaysByUser($uid,$page = 1){
		global $db_ptable;
		$uid = (int) $uid;
		if (!$uid) return array();

		$pw_posts = GetPtable($db_ptable);
		(int)$page < 1 && $page = 1;
		$start = ($page - 1) * $this->perPage;
		$limit = pwLimit($start,$this->perPage);
		$sql = "SELECT p.pid,p.postdate,t.tid,t.fid,t.subject,t.authorid,t.author,t.replies,t.hits,t.topped,t.digest,t.ifupload
			 FROM $pw_posts p LEFT JOIN pw_threads t USING(tid) WHERE p.fid != 0 AND p.authorid= ".pwEscape($uid)." 
			 AND p.fid != '0' ORDER BY p.postdate DESC $limit";
		return $this->_query($sql,$start,'re');
	}
	
	
	/**
	 * @param $uid			用户ID
	 * @param $page			当前页数
	 * @return unknown_type	返回类型
	 */
	function getFavsByUser($uid,$page = 1){
		$uid = (int) $uid;
		if (!$uid) return array();
		$result = array();
		(int)$page < 1 && $page = 1;
		$start = ($page - 1) * $this->perPage;
		$_favs = $this->db->get_one("SELECT tids FROM pw_favors WHERE uid=".pwEscape($uid));
		$_tids = explode(',',trim($_favs['tids'],','));
		$_count = count($_tids);
		$tids = array_slice($_tids,$start,$this->perPage);
		if ($tids) {
			$sql = "SELECT fid,tid,subject,postdate,author,authorid,replies,hits,topped,digest,ifupload FROM pw_threads 
					WHERE tid IN(".pwImplode($tids).") ORDER BY postdate DESC";
			$result = $this->_query($sql,$start,'fav');
		}
		return $result;
	}
	
	/**
	 * @param $sql
	 * @param $start
	 * @param $type
	 * @return unknown_type
	 */
	function _query($sql,$start=0,$type){
		$result = array();
		$query = $this->db->query($sql);
		while ($rt = $this->db->fetch_array($query)) {
			$rt['url'] = $type == 're' ? "index.php?a=reply&tid=".$rt['tid']."&amp;pid=".$rt['pid'] : "index.php?a=read&tid=".$rt['tid'];
			$rt['id'] = ++$start;
			$result[] = $rt;
		}
		return $result;		
	}
	
}
?>