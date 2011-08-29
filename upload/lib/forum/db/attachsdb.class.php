<?php
!defined('P_W') && exit('Forbidden');

class PW_AttachsDB extends BaseDB {

	var $_tableName = "pw_attachs";

	function add($fieldData) {
		$fieldData = $this->_checkData($fieldData);
		$this->_db->update("INSERT INTO ".$this->_tableName. " SET " . $this->_getUpdateSqlString ( $fieldData ) );
		return $this->_db->insert_id();
	}

	function insert($fieldsData) {
		if(!S::isArray($fieldsData)) return false;
		$this->_db->update("INSERT INTO " . $this->_tableName . " (uid,name,type,size,attachurl,uploadtime,descrip,ifthumb) VALUES  " . S::sqlMulti($fieldsData));
		return true;
	}
	
	function delete($aids) {
		if (empty($aids)) return false;
		$this->_db->update('DELETE FROM ' . $this->_tableName . ' WHERE aid' . $this->_sqlIn($aids));
		return true;
	}

	function delByTid($tid,$pid=null) {
		$this->_db->update("DELETE FROM ".$this->_tableName." WHERE tid=".intval($tid).(!is_null($pid) ? " AND pid=".intval($pid) : ""));
		return true;
	}

	function getByTid($tid, $pid = null, $nums = null, $type = null) {
		$sql = 'SELECT * FROM ' . $this->_tableName . ' WHERE tid' . $this->_sqlIn($tid);
		if (!is_null($pid)) {
			$sql .= ' AND pid' . $this->_sqlIn($pid);
		}
		if (!is_null($type)) {
			$sql .= ' AND type' . $this->_sqlIn($type);
		}
		$nums && $sql .= S::sqlLimit($nums);
		$data = array();
		$query = $this->_db->query($sql);
		while($rt = $this->_db->fetch_array($query)) {
			$data[$rt['aid']] = $rt;
		}
		return $data;
	}

	function nextImgByUid($uid,$aid) {
		return $this->_db->get_value("SELECT aid FROM pw_attachs WHERE uid=".intval($uid)." AND type='img' AND aid<".intval($aid)." ORDER BY aid DESC LIMIT 1");
	}

	function prevImgByUid($uid,$aid) {
		return $this->_db->get_value("SELECT aid FROM pw_attachs WHERE uid=".intval($uid)." AND type='img' AND aid>".intval($aid)." ORDER BY aid LIMIT 1");
	}

	function get($aid) {
		$data = $this->_db->get_one("SELECT * FROM ".$this->_tableName." WHERE aid=".intval($aid));
		if (!$data) return null;
		return $data;
	}

	function getImgsByTid($tid) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . ' WHERE tid=' . S::sqlEscape($tid) . " AND pid=0 AND type='img'");
		return $this->_getAllResultFromQuery($query);
	}

	function getLatestAttachByTidType($tid,$type='img') {
		return $this->_db->get_value("SELECT attachurl FROM " . $this->_tableName . ' WHERE tid=' . S::sqlEscape($tid) . ' AND pid=0 AND type=' . S::sqlEscape($type) . ' ORDER BY aid DESC Limit 1');
	}

	function getLatestAttachInfoByTidType($tid,$type='img') {
		return $this->_db->get_one("SELECT attachurl,ifthumb FROM " . $this->_tableName . ' WHERE tid=' . S::sqlEscape($tid) . ' AND pid=0 AND type=' . S::sqlEscape($type) . ' ORDER BY aid DESC Limit 1');
	}
	
	function countThreadImagesByTidUid($tid,$uid) {
		return $this->_db->get_value("SELECT COUNT(*) FROM " . $this->_tableName . ' WHERE tid=' . S::sqlEscape($tid) . ' AND uid=' . S::sqlEscape($uid) . " AND type='img' AND fid>0");
	}
	
	function countTopicImagesByTid($tid) {
		return $this->_db->get_value("SELECT COUNT(*) FROM " . $this->_tableName . ' WHERE tid=' . S::sqlEscape($tid) . " AND pid=0 AND type='img'");
	}
	
	function getUidByTidPidType($tid,$pid,$type='img') {
		return $this->_db->get_value("SELECT uid FROM " . $this->_tableName . ' WHERE tid=' . S::sqlEscape($tid) . ' AND pid=' . S::sqlEscape($pid) . ' AND type=' . S::sqlEscape($type) . ' Limit 1');
	}	
	
	/**
	 * 获取版块图酷帖
	 * @param array $sql
	 * return array
	 */
	function getImgs($fieldData,$topicNum=1,$startTime,$endTime,$offset,$size=10){
		if(!$fieldData || !$startTime || !$endTime || !$topicNum) return array();
		if($topicNum < 1) $topicNum = 1;
		if($startTime){
			$sql .= "t.postdate>" . S::sqlEscape($startTime);
		}
		if($endTime){
			$sql .="AND t.postdate<" . S::sqlEscape($endTime);
		}
		if ($fieldData) {
			$sql .= "AND t1.fid =" . S::sqlEscape($fieldData);
		}
		$query = $this->_db->query("SELECT t.tid FROM pw_threads t FORCE INDEX (idx_postdate) LEFT JOIN $this->_tableName t1 USING(tid) WHERE $sql AND t1.pid=0 AND t1.type='img' GROUP BY (t1.tid) HAVING COUNT(t1.aid) >= " . S::sqlEscape($topicNum). S::sqlLimit($offset,$size));
		return $this->_getAllResultFromQuery($query,'tid');
	}
	
	/**
	 * 根据主题图片附件数获取不满足条件的tid
	 * Enter description here ...
	 */
	function getUnsatisfiedTidsByTopicImageNum($fid,$tpcImageNum){
		$tids = array();
		$fid = intval($fid);
		$tpcImageNum = intval($tpcImageNum);
		$query = $this->_db->query("SELECT tid FROM $this->_tableName WHERE fid=$fid AND pid=0 GROUP BY tid HAVING COUNT(aid)<$tpcImageNum");
		while($rt = $this->_db->fetch_array($query)) {
			$tids[] = $rt['tid'];
		}
		return $tids;
	}
	/**
	 * 
	 * 计算图酷帖总条数
	 * @param array $fids
	 * @param int $startTime
	 * @param int $endTime 
	 * @param int $topicNum后台设置的图酷显示图片数
	 * return int $num
	 */
	function countTuCoolThreadNum($fid,$startTime,$endTime,$topicNum){
		if(!$startTime || !$endTime || !$topicNum) return array();
		if($topicNum < 1) $topicNum = 1;
		if($startTime){
			$sql .= "t.postdate>" . S::sqlEscape($startTime);
		}
		if($endTime){
			$sql .="AND t.postdate<" . S::sqlEscape($endTime);
		}
		if ($fid) {
			$sql .= "AND t1.fid = " . S::sqlEscape($fid);
		}
		$query = $this->_db->query("SELECT t.tid FROM pw_threads t FORCE INDEX (idx_postdate) LEFT JOIN $this->_tableName t1 USING(tid) WHERE $sql AND t1.pid=0 AND t1.type='img' GROUP BY (t1.tid) HAVING COUNT(t1.aid) >= " . S::sqlEscape($topicNum));
		return count($this->_getAllResultFromQuery($query,'tid'));
	}
	
	function gets($params) {
		$params = $this->_checkData($params);
		$data = $where = array();
		foreach ($params as $key=>$value) {
			if (is_array($value)) {
				$where[] = "$key IN (".$this->_getImplodeString($value).")";
			} else {
				$where[] = "$key=".$this->_addSlashes($value);
			}
		}
		$query = $this->_db->query("SELECT * FROM ".$this->_tableName.($where ? " WHERE ".implode(' AND ',$where) : ""));
		while($rt = $this->_db->fetch_array($query)) {
			$data[$rt['aid']] = $rt;
		}
		return $data;
	}

	function groupByTidAndPid($step,$num = 5000) {
		$step = (int)$step;
		$num = (int)$num;
		$start = $step*$num;
		$end = $start + $num;
		$result = array();
		$query = $this->_db->query("SELECT tid, pid FROM pw_attachs WHERE aid >".S::sqlEscape($start)." AND aid <".S::sqlEscape($end)." GROUP BY tid, pid");
		while ($rt = $this->_db->fetch_array($query)) {
			$result[] = $rt;
		}
		return $result;
	}

	function updateById($aids,$data) {
		if (empty($aids) || empty($data)) return false;
		$data = $this->_checkData($data);
		if (is_array($aids)) {
			$this->_db->update("UPDATE pw_attachs SET " . S::sqlSingle($data) . ' WHERE aid IN(' . $this->_getImplodeString($aids) . ')');
		} else {
			$this->_db->update("UPDATE pw_attachs SET " . S::sqlSingle($data) . ' WHERE aid=' . intval($aids));
		}
		return true;
	}

	/**
	 * 例子:
	 *   updateByTid($tids,$data);
	 *   updateByTid($tids,$pid,$data);
	 */
	function updateByTid($tids,$p1,$p2=null) {
		if (empty($tids)) return false;
		if (is_null($p2)) {
			$data = $p1;
			$pid = null;
		} else {
			$data = $p2;
			$pid = $p1;
		}
		if (empty($data)) return false;
		$data = $this->_checkData($data);
		if (is_array($tids)) {
			$this->_db->update("UPDATE pw_attachs SET " . S::sqlSingle($data) . ' WHERE tid IN(' . $this->_getImplodeString($tids) . ')' . (!is_null($pid) ? " AND pid=".intval($pid) : ""));
		} else {
			$this->_db->update("UPDATE pw_attachs SET " . S::sqlSingle($data) . ' WHERE tid=' . intval($tids) . (!is_null($pid) ? " AND pid=".intval($pid) : ""));
		}
		return true;
	}

	function increaseField($aid, $fieldName, $step = 1) {
		if (! in_array ( $fieldName, array ('hits' ) ))
			return 0;
		$step = intval ( $step );
		if ($step == 0)
			return 0;
		$step = $step > 0 ? "+" . $step : $step;
		$this->_db->update("UPDATE " . $this->_tableName . " SET $fieldName=$fieldName" . $step . " WHERE aid=" . intval($aid) . " LIMIT 1" );
		return $this->_db->affected_rows();
	}

	function getTableStructs($type) {
		$query = $this->_db->query("show table status like ".S::sqlEscape($this->_tableName));
		$data = $this->_db->fetch_array($query);
		if (isset($data[$type])) {
			return $data[$type];
		}
		return null;
	}

	function getStruct() {
		return array('aid','fid','uid','tid','pid','did','name','type','size', 'attachurl','hits','needrvrc', 'special','ctype', 'uploadtime','descrip','ifthumb','mid');
	}

	function _checkData($data) {
		if (!is_array($data) || !count($data)) return null;
		$data = $this->_checkAllowField($data,$this->getStruct());
		return $data;
	}

	function getsByAids($aids){
		$aids = is_array($aids) ? S::sqlImplode($aids) : $aids;
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName. " WHERE aid in (".$aids.") " );
		return $this->_getAllResultFromQuery ( $query );
	}

	function deleteByAids($aids){
		return $this->_db->update ( "DELETE FROM " . $this->_tableName. " WHERE aid in( ".S::sqlImplode($aids).")");
	}

	function countMultiUpload($userId){
		return $this->_db->get_value("SELECT COUNT(*) AS sum FROM pw_attachs WHERE fid=0 AND tid=0 AND pid='0' AND mid='0' AND did ='0' AND uid=" . S::sqlEscape($userId));
	}

	function _sqlIn($ids) {
		return (is_array($ids) && $ids) ? ' IN (' . S::sqlImplode($ids) . ')' : '=' . S::sqlEscape($ids);
	}

	function getDiaryAttachsBydid($id) {
		if (!$id) return false;
		$query = $this->_db->query( "SELECT * FROM " . $this->_tableName. " WHERE did=".$this->_addSlashes($id)." AND fid=0");
		return $this->_getAllResultFromQuery($query);
	}

	function getByUids($userIds) {
		if (!$userIds) return false;
		$userIds = $userIds ? $this->_getImplodeString($userIds) : $this->_addSlashes($userIds);
		$query = $this->_db->query( "SELECT * FROM " . $this->_tableName. " WHERE uid IN( ".$userIds." )");
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 图酷帖附件
	 * @param $tid 帖子tid
	 * @param $uid 
	 * @return array
	 */
	function getByTidAndUid($tid,$uid) {
		$tid = intval($tid);
		$uid = intval($uid);
		if ($tid < 1 || $uid < 1) return array();
		$query = $this->_db->query( "SELECT * FROM " . $this->_tableName. " WHERE tid = " . $this->_addSlashes($tid) . " AND uid = " . $this->_addSlashes($uid));
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 注意只提供搜索服务
	 * @param $sql
	 * @return unknown_type
	 */
	function countSearch($sql){
		$result = $this->_db->get_one ( $sql );
		return ($result) ? $result['total'] : 0;
	}
	/**
	 * 注意只提供搜索服务
	 * @param $sql
	 * @return unknown_type
	 */
	function getSearch($sql){
		$query = $this->_db->query ($sql);
		return $this->_getAllResultFromQuery ( $query );
	}
}

?>