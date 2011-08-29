<?php
!defined('P_W') && exit('Forbidden');
@include_once (R_P . 'lib/base/basedb.php');

class PW_TuCoolDB extends BaseDB {
	var $_tableName = 'pw_threads_img';
	var $_primaryKey = 'tid';

	function add($data) {
		$fieldData['fid'] = intval($data['fid']);
		$fieldData['tid'] = intval($data['tid']);
		$fieldData['tpcnum'] = intval($data['tpcnum']);
		$fieldData['totalnum'] = intval($data['totalnum']);
		isset($data['ifcheck']) && $fieldData['ifcheck'] = intval($data['ifcheck']);
		isset($data['topped']) && $fieldData['topped'] = intval($data['topped']);
		return $this->_insert($fieldData);
	}
	
	function delete($id){
		return $this->_delete($id);
	}
	
	function get($id){
		return $this->_get($id);
	}
	
	function update($fieldData ,$id) {
		return $this->_update($fieldData ,$id);
	}
	
	/**
	 * 
	 * 获取最新图酷帖排行
	 * @param string $fid
	 * @param string $order
	 * @return array
	 */
	function newTuCoolSort($fid,$num){
		$num = intval($num);
		if ($num < 1) return array();
		$posts = array();
		$sqlWhere = '';
		$fid && $sqlWhere .= " AND t.fid IN ($fid)";
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqlWhere .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t1.totalnum,t1.collectnum,t1.cover,t1.tpcnum,t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies FROM $this->_tableName t1 LEFT JOIN pw_threads t USING(tid) WHERE t.ifcheck = 1 AND t.fid != 0 $sqlWhere AND t1.totalnum >0 AND t.ifshield != 1 AND t.locked != 2  ORDER BY t.tid DESC ".S::sqlLimit($num));
		while ($row = $this->_db->fetch_array($query)) {
			$posts[] = $row;
		}
		return $posts;
	}

	/**
	 * 
	 * 按总图片数获取图酷帖排行
	 * @param string $fid
	 * @return array
	 */
	function subjectPicNumSort($fid,$num){
		$num = intval($num);
		if ($num < 1) return array();
		$posts = array();
		$sqlWhere = '';
		$fid && $sqlWhere .= " AND t.fid IN ($fid)";
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqlWhere .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t1.totalnum,t1.collectnum,t1.cover,t1.tpcnum,t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies FROM $this->_tableName t1 LEFT JOIN pw_threads t USING(tid) WHERE t.ifcheck = 1 AND t.fid != 0 $sqlWhere AND t1.totalnum >0 AND t.ifshield != 1 AND t.locked != 2  ORDER BY t1.totalnum DESC ".S::sqlLimit($num));
		while ($row = $this->_db->fetch_array($query)) {
			$posts[] = $row;
		}
		return $posts;
	}

	/**
	 * 
	 * 图酷帖今日点击排行
	 * @param string $fids
	 * @param int $num
	 * @return array
	 */
	function getTucoolThreadsByHitSortToday($fids,$num){
		global $timestamp;
		$num = intval($num);
		if ($num < 1) return array();
		$sqlAdd = '';
		$today = PwStrtoTime(get_date($timestamp,'Ymd'));
		$sqlAdd .= " AND t.postdate >= " . S::sqlEscape($today);
		$fids && $sqlAdd .= " AND t.fid IN ($fids)";
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqlAdd .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies,ti.totalnum,ti.collectnum,ti.cover,ti.tpcnum FROM pw_threads t LEFT JOIN $this->_tableName ti USING(tid) WHERE t.ifcheck = 1 AND t.fid != 0 $sqlAdd AND ti.totalnum >0 AND t.ifshield != 1 AND t.locked != 2  ORDER BY t.hits DESC ".S::sqlLimit($num));
		return $this->_getAllResultFromQuery ($query);
	}

	/**
	 * 
	 * 图酷帖昨日点击排行
	 * @param string $fids
	 * @param int $num
	 * @return array
	 */
	function getTucoolThreadsByHitSortYesterday($fids,$num){
		global $timestamp;
		$num = intval($num);
		if ($num < 1) return array();
		$sqlAdd = '';
		$today = PwStrtoTime(get_date($timestamp,'Ymd'));
		$yesterday = $today - 3600*24;
		$sqlAdd .= " AND t.postdate BETWEEN $yesterday AND $today";
		$fids && $sqlAdd .= " AND t.fid IN ($fids)";
		$blackListedTids = $this->_getBlackListedTids();
		$blackListedTids && $sqlAdd .= ' AND t.tid NOT IN (' . $blackListedTids . ')';
		$query = $this->_db->query("SELECT t.tid,t.fid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies,ti.totalnum,ti.collectnum,ti.cover,ti.tpcnum FROM pw_threads t LEFT JOIN $this->_tableName ti USING(tid) WHERE t.ifcheck = 1 AND t.fid != 0 $sqlAdd AND ti.totalnum >0 AND t.ifshield != 1 AND t.locked != 2  ORDER BY t.hits DESC ".S::sqlLimit($num));
		return $this->_getAllResultFromQuery ($query);
	}

	/**
	 * 
	 * 根据tids批量查
	 * @param array $tids
	 * @param int $num
	 * @return array
	 */
	function getTucoolThreadsByTids($tids){
		if (!S::isArray($tids)) return array();
		$query = $this->_db->query("SELECT * FROM $this->_tableName WHERE tid IN  (" . S::sqlImplode($tids) . ")");
		return $this->_getAllResultFromQuery ($query);
	}

	function updateCollectNum($tid) {
		$tid = intval($tid);
		if ($tid < 1) return false;
		return pwQuery::update($this->_tableName, 'tid=:tid', array($tid), null, array(PW_EXPR=>array('collectnum=collectnum+1')));	
	}
	
	function _getBlackListedTids() {
		global $db_tidblacklist;
		return $db_tidblacklist;
	}
}
?>