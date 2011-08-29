<?php
/**
 * 新鲜事消息体数据库DAO服务
 * @package PW_Weibo_ContentDB
 * @author suqian
 */
!defined('P_W') && exit('Forbidden');
class PW_Weibo_ContentDB extends BaseDB {

	var $_tableName = 'pw_weibo_content';
	var $_foreignTableName = 'pw_weibo_relations';	
	var $_primaryKey = 'mid';

	function insert($fieldData){
		return $this->_insert($fieldData);
	}

	function update($fieldData,$id){
		return $this->_update($fieldData,$id);
	}

	function delete($id){
		return $this->_delete($id);
	}

	function get($id){
		return $this->_get($id);
	}

	function count(){
		return $this->_count();
	}

	function updateCountNum($fieldData, $id) {
		$sql = '';
		foreach ($fieldData as $key => $value) {
			if ($key == 'transmit' || $key == 'replies') {
				$sql .= ($sql ? ',' : '') . $key . '=' . $key . '+' . intval($value);
			}
		}
		$this->_db->query('UPDATE ' . $this->_tableName . ' SET ' . $sql . ' WHERE mid=' . S::sqlEscape($id));
	}

	function getWeibosByMid($mids){
		$sql = 'SELECT * FROM ' . $this->_tableName.' WHERE mid' . $this->_sqlIn($mids);
		$query = $this->_db->query($sql);
		return $this->_getAllResultFromQuery($query, 'mid');
	}

	function getWeibosByType($type, $start, $limit) {
		$sql = 'SELECT * FROM ' . $this->_tableName . ' WHERE type=' . S::sqlEscape($type) . ' ORDER BY objectid DESC ' . S::sqlLimit($start, $limit);
		$query = $this->_db->query($sql);
		return  $this->_getAllResultFromQuery($query);
	}

	function getWeibosByObjectIdsAndType($objectIds,$type){
		if (!isset($type) || empty($objectIds)) {
			return array();
		}
		$objectIds = is_array($objectIds) ? $objectIds : array($objectIds);
		$sql = 'SELECT mid,uid FROM '.$this->_tableName.' WHERE type = ' . S::sqlEscape($type) . ' AND objectid in (' . S::sqlImplode($objectIds).')';
		$query = $this->_db->query($sql);
		return  $this->_getAllResultFromQuery($query);
	}
	
	function getMidsByObjectIdsAndType($objectIds, $type) {
		if (!isset($type) || empty($objectIds)) {
			return array();
		}
		$objectIds = is_array($objectIds) ? $objectIds : array($objectIds);
		$sql = 'SELECT mid FROM '.$this->_tableName.' WHERE type = ' . S::sqlEscape($type) . ' AND objectid in (' . S::sqlImplode($objectIds).')';
		$query = $this->_db->query($sql);
		return $this->_getAllResultFromQuery($query);
	}
	
	function getWeibos($page = 1,$perpage = 20){
		if (!$this->_isLegalNumeric($page) || !$this->_isLegalNumeric($perpage)){
			return array();
		} 
		$offset = ($page - 1) * $perpage;
		$sql = 'SELECT * FROM '.$this->_tableName.'   ORDER BY mid DESC '.$this->_Limit($offset,$perpage);
		$query = $this->_db->query($sql);
		return  $this->_getAllResultFromQuery($query);
	}
	
	function getWeibosByTypesAndNum($types = array(), $num = 10){
		if (!$types || !is_array($types) || !$num) return false;
		$sql = 'SELECT * FROM '.$this->_tableName.' WHERE type IN(' . S::sqlImplode($types) .') ORDER BY mid DESC '.$this->_Limit($num);
		$query = $this->_db->query($sql);
		return  $this->_getAllResultFromQuery($query);
	}
	
	function getWeibosCount(){
		return $this->_count();
	}
	
	function getUserWeibos($uid,$page = 1,$perpage = 20){
		if (!$this->_isLegalNumeric($page) || !$this->_isLegalNumeric($perpage) || !$this->_isLegalNumeric($uid)){
			return array();
		}
		$offset = ($page - 1) * $perpage;
		$sql = 'SELECT * FROM '.$this->_tableName.' WHERE  uid = '.$this->_addSlashes($uid).'  ORDER BY mid DESC '.$this->_Limit($offset,$perpage);
		$query = $this->_db->query($sql);
		return  $this->_getAllResultFromQuery($query);
	}
	
	function getUserWeibosCount($uid){
		if(!$this->_isLegalNumeric($uid)){
			return 0;
		}
		$sql = 'SELECT count(*) FROM '.$this->_tableName.' WHERE  uid = '.$this->_addSlashes($uid);
		return $this->_db->get_value($sql);
	}
	
	function getUserAttentionWeibos($uid, $where = array(), $page = 1, $perpage = 20){
		if (!$this->_isLegalNumeric($page) || !$this->_isLegalNumeric($perpage) || !$this->_isLegalNumeric($uid)){
			return array();
		} 
		$offset = ($page - 1) * $perpage;
		$sqlAdd = $this->_filterSql($where);
		$sql = 'SELECT * FROM ' . $this->_foreignTableName . ' a LEFT JOIN ' . $this->_tableName . ' b ON a.mid=b.mid WHERE a.uid=' . $this->_addSlashes($uid) . $sqlAdd . ' ORDER BY a.postdate DESC ' . $this->_Limit($offset,$perpage);
		$query = $this->_db->query($sql);
		return $this->_getAllResultFromQuery($query);
	}
	
	function getUserAttentionWeibosCount($uid, $where = array()) {
		if (!$this->_isLegalNumeric($uid)) {
			return 0;
		}
		$sqlAdd = $this->_filterSql($where);
		$sql = 'SELECT COUNT(*) FROM ' . $this->_foreignTableName . ' a LEFT JOIN ' . $this->_tableName . ' b ON a.mid=b.mid WHERE a.uid=' . $this->_addSlashes($uid) . $sqlAdd;
		return  $this->_db->get_value($sql);
	}
	
	function getUserAttentionWeibosNotMe($uid,$page = 1,$perpage = 20){
		if (!$this->_isLegalNumeric($page) || !$this->_isLegalNumeric($perpage) || !$this->_isLegalNumeric($uid)){
			return array();
		}
		$offset = ($page - 1) * $perpage;
		$sql = 'SELECT * FROM '.$this->_foreignTableName.' a LEFT JOIN '.$this->_tableName.' b ON a.mid=b.mid WHERE a.uid = '.$this->_addSlashes($uid).' and a.authorid != '.$this->_addSlashes($uid).' ORDER BY a.postdate DESC '.$this->_Limit($offset,$perpage);
		$query = $this->_db->query($sql);
		return  $this->_getAllResultFromQuery($query);
	}
	
	function getUserAttentionWeibosNotMeCount($uid){
		if(!$this->_isLegalNumeric($uid)){
			return false;
		}
		$sql = 'SELECT count(*) FROM '.$this->_foreignTableName.' a LEFT JOIN '.$this->_tableName.' b ON a.authorid = b.uid AND a.mid = b.mid WHERE  a.uid = '.$this->_addSlashes($uid).' and a.authorid != '.$this->_addSlashes($uid);
		return  $this->_db->get_value($sql);
	}

	function getWeiboAuthors($num, $exclude) {
		$sql = '';
		if (($max = $this->_db->get_value("SELECT MAX(mid) FROM " . $this->_tableName)) > 1000) {
			$sql .= ' AND mid>' . intval($max - 1000);
		}
		if ($exclude) {
			$sql .= ' AND uid NOT IN(' . S::sqlImplode($exclude) . ')';
		}
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . ' WHERE 1' . $sql . ' GROUP BY uid' . $this->_limit($num));
		return $this->_getAllResultFromQuery($query);
	}

	function getAuthorSort($num, $time) {
		$query = $this->_db->query("SELECT uid,sum(transmit) AS counts FROM " . $this->_tableName . ' WHERE postdate > ' . intval($time) . ' GROUP BY uid ORDER BY counts DESC' . $this->_limit($num));
		return $this->_getAllResultFromQuery($query, 'uid');
	}
	
	/**
	 * 取得n天内被转发次数最多的新鲜事Id
	 * @param int $num 获取记录条数
	 * @return array
	 */
	function getHotTransmit($num=20,$time){
		if(!$time || !$num) return array();
		$query = $this->_db->query("SELECT objectid,count(objectid) as counts FROM " . $this->_tableName . ' WHERE type = 1 AND postdate > ' . S::sqlEscape($time) . ' GROUP BY objectid ORDER BY counts DESC,postdate DESC'. $this->_limit($num));
		return array_keys($this->_getAllResultFromQuery($query,'objectid'));
	}
	
	function getPrevWeiboByType($uid, $type, $time) {
		return $this->_db->get_one('SELECT * FROM ' . $this->_tableName . ' WHERE uid=' . S::sqlEscape($uid) . ' AND postdate>' . S::sqlEscape($time) . ' AND type=' . S::sqlEscape($type) . ' ORDER BY postdate DESC LIMIT 1');
	}

	/*新鲜事搜索*/
	function search($keyword,$type = '', $offset = 0,$perpage = 20,$startDate = '',$endDate = ''){
		$sqlAdd = '';
		/*内容关键字*/
		$keyword && $sqlAdd .= ' AND content like '.S::sqlEscape('%'.$keyword.'%');
		/*发表时间*/
		$startDate && is_numeric($startDate) && $sqlAdd .= ' AND postdate >= ' . S::sqlEscape($startDate);
		$endDate && is_numeric($endDate) && $sqlAdd .= ' AND postdate <= ' . S::sqlEscape($endDate);
		/*新鲜事类型*/
		if (strtoupper($type) != 'ALL') {
			$type = intval($type);
			$type >= 0 && $sqlAdd .= ' AND type = ' . S::sqlEscape($type);
		}
		$sql = 'SELECT COUNT(*) FROM '.$this->_tableName.' WHERE 1 '.$sqlAdd;
		$total =  $this->_db->get_value($sql);
		$offset = intval($offset);
		$perpage = intval($perpage);
		$sql = 'SELECT * FROM '.$this->_tableName.' WHERE 1 '.$sqlAdd ."ORDER BY mid DESC LIMIT $offset,$perpage"; 
		$query = $this->_db->query($sql);
		$result =  $this->_getAllResultFromQuery($query);
		return array($total,$result);
	}
	function adminSearch($uids,$contents,$startDate,$endDate,$type,$orderby,$page = 1,$perpage = 20){
		if (!$this->_isLegalNumeric($page) || !$this->_isLegalNumeric($perpage)){
			return array();
		} 
		$sqlAdd = '';
		if($uids && is_array($uids)){
			$sqlAdd .= ' AND uid IN (' . S::sqlImplode($uids) . ') ';
		}
		if($contents){
			$sqlAdd .= ' AND content like '.S::sqlEscape('%'.$contents.'%');
		}
		if($startDate && is_numeric($startDate)){
			$sqlAdd .= ' AND postdate >= ' . S::sqlEscape($startDate);
		}
		
		if($endDate && is_numeric($endDate)){
			$sqlAdd .= ' AND postdate <= ' . S::sqlEscape($endDate);
		}
		
		$type = intval($type);
		if($type >= 0 ){
			$sqlAdd .= ' AND type = ' . S::sqlEscape($type);
		}
		$orderby = in_array($orderby,array('desc','asc')) ? $orderby : 'desc';
		$sql = 'SELECT count(*) FROM '.$this->_tableName.' WHERE 1=1 '.$sqlAdd;
		$total =  $this->_db->get_value($sql);
		
		$sqlAdd .= ' ORDER BY postdate '.$orderby;
		$offset = ($page - 1) * $perpage;
		$sqlAdd .= $this->_Limit($offset,$perpage);
		
		$sql = 'SELECT * FROM '.$this->_tableName.' WHERE 1=1 '.$sqlAdd;
		$query = $this->_db->query($sql);
		$result =  $this->_getAllResultFromQuery($query);
		return array($total,$result);
	}
	function deleteWeibosByMid($mids){
		if(empty($mids)){
			return false;
		}
		$mids = is_array($mids) ? $mids : array($mids);
		return pwQuery::delete($this->_tableName, 'mid IN (:mid)', array($mids));
		/*
		$sql = 'DELETE FROM '.$this->_tableName.' WHERE mid IN (' . S::sqlImplode($mids) . ') ';
		$this->_db->query($sql);
		return true;
		*/
	}
	
	function _isLegalNumeric($id){
		return intval($id) > 0;
	}
	
	function _filterSql($where = array()) {
		$sqlAdd = '';
		foreach ($where as $key => $value) {
			switch ($key) {
				case 'uidIn':
				case 'uidsIn': $sqlAdd .= " AND a.authorid" . $this->_sqlIn($value);break;
				case 'uidsNotIn': if ($value) $sqlAdd .= " AND a.authorid NOT IN (" . S::sqlImplode($value) . ')';break;
				case 'uidNotIn': $sqlAdd .= " AND a.authorid!=" . S::sqlEscape($value);break;
				case 'source': $sqlAdd .= " AND a.type" . $this->_sqlIn($value);break;
				case 'contenttype': $sqlAdd .= " AND b.contenttype=" . S::sqlEscape($value);break;
			}
		}
		return $sqlAdd;
	}

	function _sqlIn($ids) {
		return (is_array($ids) && $ids) ? ' IN (' . S::sqlImplode($ids) . ')' : '=' . S::sqlEscape($ids);
	}
	
	function findMidsByUids($uids) {
		if (!$uids) return false;
		$uids = $uids ? $this->_getImplodeString($uids) : $this->_addSlashes($uids);
		$query = $this->_db->query("SELECT mid FROM ".$this->_tableName. " WHERE uid IN ( ".$uids." )");
		return $this->_getAllResultFromQuery($query);
	}
}
?>