<?php
!defined('W_P') && exit('Forbidden');
class RecommendDB {
	var $db;
	var $perPage = 2;
	
	/**
	 * @return unknown_type
	 */
	function RecommendDB() {
		$this->__construct();
	}
	
	/**
	 * @return unknown_type
	 */
	function __construct(){
		global $db;
		global $wap_perpage;
		$this->db = & $db;
		$this->perPage = & $wap_perpage;
	}
	
	/**
	 * @param $perPage the $perPage to set
	 */
	function setPerPage($perPage) {
		$this->perPage = $perPage;
	}
	
	/**
	 * @param $count
	 * @return unknown_type
	 */
	function getRecommendActiveType($count=''){
		$result = array();
		if ($count) {
			$limit = pwLimit(0,(int)$count);
		}
		$query = $this->db->query("SELECT pt.* FROM pw_wappushtype pt WHERE pt.state ORDER BY pt.sort ASC ".$limit);
		while ($rt = $this->db->fetch_array($query)) {
			$result[] = $rt;
		}
		return $result;
	}
	
	
	/**
	 * @param $type
	 * @param $page
	 * @return unknown_type
	 */
	function getRecommendByType($type,$page=1){
		$result = array();
		$where = " WHERE (t.fid != 0 OR t.fid IS NULL) ";
		if ($type) {
			$where .= " AND  p.typeid = ".pwEscape($type);
		}
		(int)$page < 1 && $page = 1;
		$start = ($page - 1) * $this->perPage;
		$limit = pwLimit($start,$this->perPage);
		$query = $this->db->query("SELECT p.*,t.replies,t.hits,t.author,t.authorid,t.lastposter,t.lastpost FROM pw_wappush p LEFT JOIN pw_threads t ON p.tid = t.tid 
					$where ORDER BY p.id DESC $limit");
		while ($rt = $this->db->fetch_array($query)) {
			$rt['index'] = ++$start;
			list(,$lastDate) = getLastDate($rt['lastpost']);
			$rt['lastpost'] = $lastDate;
			$result[] = $rt;
		}
		return $result;
	}
}
?>