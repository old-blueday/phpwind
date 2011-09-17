<?php
!defined('P_W') && exit('Forbidden');
class PW_ThreadsDB extends BaseDB {
	var $_tableName  = 'pw_threads';
	var $_tableName2 = 'pw_tmsgs';
	var $_tableName3 = 'pw_threads_img';
	var $_primaryKey = 'tid';
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
	/**
	 * 针对于分表
	 * @param $threadIds
	 */
	function getsBythreadIds($threadIds){
		$threadIds = (is_array($threadIds)) ? $threadIds : explode(",",$threadIds);
		foreach($threadIds as $threadId){
			$table = GetTtable($threadId);
			$tables[$table][] = $threadId;
		}
		$threads = array();
		foreach($tables as $table=>$tids){
			$t = $this->_getsBythreadIds($tids,$table);
			$threads = array_merge($threads,$t);
		}
		$tmp = array();
		foreach($threads as $t){
			$tmp[$t['tid']] = $t;
		}
		$result = array();
		foreach($threadIds as $threadId){
			(isset($tmp[$threadId])) ? $result[] = $tmp[$threadId] : '';
		}
		return $result;
	}

	function _getsBythreadIds($threadIds,$tmsgsTableName){
		$this->_tableName2 = ($tmsgsTableName) ? $tmsgsTableName : $this->_tableName2;
		$threadIds = (is_array($threadIds)) ? S::sqlImplode($threadIds) : $threadIds;
		$query = $this->_db->query ( "SELECT t.*,th.content FROM ".$this->_tableName." t left join ".$this->_tableName2." th on t.tid=th.tid WHERE t.ifcheck = 1 AND t.fid != 0  AND t.tid in(".$threadIds.") ORDER BY t.postdate DESC" );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function _getFilterids(){
		global $db_filterids;
		$_sql_where='';
		if($db_filterids){
			$_sql_where=" AND t.fid not in(".$db_filterids.") ";
		}
		return $_sql_where;
	}
	function getLatestThreadsCount($forumIds, $starttime, $endtime){
		$_sql_where=$this->_getFilterids();
		
		if ($forumIds) {
			$forumIds = (is_array ( $forumIds )) ? $forumIds : array ($forumIds );
			$_sql_where .= " AND t.fid IN(" . S::sqlImplode ( $forumIds ) . ")";
		}
		
		if ($starttime) {
			$ifpostdate = 1;
			$_sql_where .= " AND t.postdate > " . S::sqlEscape ( $starttime );
		}
		
		if ($endtime) {
			$ifpostdate = 1;
			$_sql_where .= " AND t.postdate < " . S::sqlEscape ( $endtime );
		}

		if ($ifpostdate == 1) {
			$forceIndex = 'FORCE INDEX (idx_postdate)';
		}
		
		$total=$this->countSearch("SELECT count(*) as total FROM ".$this->_tableName." t ".$forceIndex." WHERE t.ifcheck = 1 AND t.fid !=0 ".$_sql_where);
		return ($total<500) ? $total :500;
	}
	
	function getLatestThreads($forumIds, $starttime, $endtime, $offset, $limit){
		$threadIds = $this->_getLatestThreads($forumIds, $starttime, $endtime, $offset,$limit);
		if(!$threadIds) return false;
		$tmp = array();
		foreach($threadIds as $t){
			$tmp[] = $t['tid'];
		}
		return $this->getsBythreadIds($tmp);
	}
	
	function _getLatestThreads($forumIds, $starttime, $endtime, $offset, $limit){
		$_sql_where=$this->_getFilterids();
		
		if ($forumIds) {
			$forumIds = (is_array ( $forumIds )) ? $forumIds : array ($forumIds );
			$_sql_where .= " AND t.fid IN(" . S::sqlImplode ( $forumIds ) . ")";
		}
		
		if ($starttime) {
			$_sql_where .= " AND t.postdate > " . S::sqlEscape ( $starttime );
		}
		
		if ($endtime) {
			$_sql_where .= " AND t.postdate < " . S::sqlEscape ( $endtime );
		}
		
		$query = $this->_db->query ("SELECT t.tid FROM ".$this->_tableName." t WHERE t.ifcheck = 1 AND t.ifshield != 1 ".$_sql_where." ORDER BY t.postdate DESC LIMIT " . $offset . "," . $limit );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	/**
	 * 最新图片帖
	 */
	function getLatestImageThreads($limit){
		$limit = intval($limit);
		if (!$limit) return array();
		$query = $this->_db->query ("SELECT t.tid,t.subject FROM ".$this->_tableName3." ti LEFT JOIN ".$this->_tableName." t USING(tid) WHERE t.ifcheck = 1 AND t.fid != 0 AND t.locked = 0 ORDER BY t.postdate DESC LIMIT ". $limit );
		return $this->_getAllResultFromQuery ( $query ,'tid');
	}
	
	function deleteTucoolThreadsByTids($tids){
		return pwQuery::delete($this->_tableName3, 'tid IN (:tid)', array($tids));
	}
	function getDigestThreadsCount($uid, $forumIds, $starttime, $endtime){
		$_sql_where=$this->_getFilterids();
		
		if ($uid) {
			$_sql_where .=  ' AND t.authorid=' . S::sqlEscape($uid);
		}
		
		if ($forumIds) {
			$forumIds = (is_array ( $forumIds )) ? $forumIds : array ($forumIds );
			$_sql_where .= " AND t.fid IN(" . S::sqlImplode ( $forumIds ) . ")";
		}
		
		if ($starttime) {
			$_sql_where .= " AND t.postdate > " . S::sqlEscape ( $starttime );
		}
		
		if ($endtime) {
			$_sql_where .= " AND t.postdate < " . S::sqlEscape ( $endtime );
		}
		
		$total=$this->countSearch("SELECT count(*) as total FROM ".$this->_tableName." t WHERE t.ifcheck = 1{$_sql_where} AND t.digest IN ('1','2')");
		return ($total<500) ? $total : 500;
	}
	
	function getDigestThreads($uid, $digest,$forumIds, $starttime, $endtime, $offset, $limit){
		$threadIds = $this->_getDigestThreads($uid,$digest,$forumIds,$starttime,$endtime,$offset,$limit);
		if(!$threadIds) return false;
		$tmp = array();
		foreach($threadIds as $t){
			$tmp[] = $t['tid'];
		}
		return $this->getsBythreadIds($tmp);
	}

	function _getDigestThreads($uid, $digest, $forumIds, $starttime, $endtime, $offset, $limit){
		$_sql_where=$this->_getFilterids();
		
		if ($uid) {
			$_sql_where .=  ' AND t.authorid=' . S::sqlEscape($uid);
		}
		
		if ($digest) {
			$digest = (is_array ( $digest )) ? $digest : array ($digest );
			$_sql_where .= " AND t.digest IN(" . S::sqlImplode ( $digest ) . ")";
		}
		
		if ($forumIds) {
			$forumIds = (is_array ( $forumIds )) ? $forumIds : array ($forumIds );
			$_sql_where .= " AND t.fid IN(" . S::sqlImplode ( $forumIds ) . ")";
		}
		
		if ($starttime) {
			$_sql_where .= " AND t.postdate > " . S::sqlEscape ( $starttime );
		}
		
		if ($endtime) {
			$_sql_where .= " AND t.postdate < " . S::sqlEscape ( $endtime );
		}
		
		$query = $this->_db->query ( "SELECT t.tid FROM ".$this->_tableName." t WHERE t.ifcheck = 1{$_sql_where} ORDER BY t.postdate DESC LIMIT " . $offset . "," . $limit );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getThreadsCountByPostdate($postdate) {
		$_sql_where=$this->_getFilterids();
		$total=$this->countSearch("SELECT count(*) as total FROM ".$this->_tableName." t WHERE t.ifcheck = 1 ".$_sql_where." AND postdate>=".S::sqlEscape($postdate));
		return $total;
	}
	
	function getThreadsByPostdate($offset,$limit,$postdate) {
		$threadIds = $this->_getThreadsByPostdate($offset,$limit,$postdate);
		if(!$threadIds) return false;
		$tmp = array();
		foreach($threadIds as $t){
			$tmp[] = $t['tid'];
		}
		return $this->getsBythreadIds($tmp);
	}
	
	function _getThreadsByPostdate($offset,$limit,$postdate){
		$_sql_where=$this->_getFilterids();
		$query = $this->_db->query ("SELECT t.tid FROM ".$this->_tableName." t WHERE t.ifcheck = 1 ".$_sql_where." AND t.postdate>=".S::sqlEscape($postdate)." ORDER BY t.postdate DESC LIMIT " . $offset . "," . $limit );
		return $this->_getAllResultFromQuery ( $query );
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

	/**
	 * 跟据板块id获取帖子基本信息
	 *
	 * @param int $forumId 板块id
	 * @param int $offset
	 * @param int $limit
	 * @return array
	 */
	function getThreadsByFroumId($forumId, $offset, $limit) {
		$forumId = S::int($forumId);
		if($forumId < 1){
			return false;
		}
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE fid=" . S::sqlEscape($forumId) . "AND ifcheck=1 AND specialsort=0 ORDER BY lastpost DESC LIMIT $offset,$limit");
		return $this->_getAllResultFromQuery($query, $this->_primaryKey);
	}

	/**
	 * 根据一组帖子id，获取其帖子基本信息
	 *
	 * @param array $threadIds 帖子id列表
	 * @return array
	 */
	function getThreadsByThreadIds($threadIds) {
		if (!S::isArray($threadIds)) {
			return false;
		}
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE tid IN (" . S::sqlImplode($threadIds, false) . ") ORDER BY lastpost DESC");
		return $this->_getAllResultFromQuery($query, $this->_primaryKey);
	}

	/**
	 * 根据帖子id，获取帖子的详细信息,即只查询tmsgs表
	 *
	 * @param int $tid 帖子id
	 * @return array
	 */
	function getTmsgByThreadId($threadId) {
		$threadId = S::int($threadId);
		if($threadId < 1){
			return false;
		}
		$pw_tmsgs = GetTtable($threadId);
		return $this->_db->get_one("SELECT * FROM $pw_tmsgs WHERE tid=" . S::sqlEscape($threadId));
	}

	/**
	 * 根据一个帖子id，获取一条帖子基本信息
	 *
	 * @param int $tid
	 * @return array
	 */
	function getThreadByThreadId($threadId) {
		$threadId = S::int($threadId);
		if($threadId < 1){
			return false;
		}
		return  $this->_db->get_one("SELECT * FROM ". $this->_tableName ." WHERE tid=" . S::sqlEscape($threadId));
	}

	/**
	 * 根据帖子id，获取帖子的基本信息和详细信息(即查询pw_threads和pw_tmsgs表)
	 *
	 * @param int $tid 帖子id
	 * @return array
	 */
	function getThreadAndTmsgByThreadId($threadId) {
		$threadId = S::int($threadId);
		if($threadId < 1){
			return false;
		}
		$pw_tmsgs = GetTtable($threadId);
		return $this->_db->get_one("SELECT t.* ,tm.* FROM {$this->_tableName} t LEFT JOIN $pw_tmsgs tm ON t.tid=tm.tid WHERE t.tid=" . S::sqlEscape($threadId));
	}

	/**
	 * 删除pw_threads表的一条记录
	 *
	 * @param int $threadId 帖子id
	 * @return int
	 */
	function deleteByThreadId($threadId) {
		$threadId = S::int($threadId);
		if($threadId < 1){
			return false;
		}
		//$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE tid=" . S::sqlEscape($threadId));
		pwQuery::delete($this->_tableName, 'tid=:tid', array($threadId));
		return $this->_db->affected_rows();
	}

	/**
	 * 删除pw_threads表里一组记录
	 *
	 * @param array $threadIds 帖子id （数组格式）
	 * @return int
	 */
	function deleteByThreadIds($threadIds) {
		if(!S::isArray($threadIds)){
			return false;
		}
		//$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE tid in(" . $threadIds . ")");
		pwQuery::delete($this->_tableName, 'tid IN (:tid)', array($threadIds));
		return $this->_db->affected_rows();
	}

	/**
	 * 根据板块id删除帖子
	 *
	 * @param int $forumId 板块id
	 * @return int
	 */
	function deleteByForumId($forumId) {
		$forumId = S::int($forumId);
		if($forumId < 1){
			return false;
		}
		//$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE fid=" . S::sqlEscape($forumId));
		pwQuery::delete($this->_tableName, 'fid=:fid', array($forumId));
		return $this->_db->affected_rows();
	}

	/**
	 * 根据作者id 删除帖子
	 *
	 * @param int $authorId 作者id
	 * @return int
	 */
	function deleteByAuthorId($authorId) {
		$authorId = S::int($authorId);
		if($authorId < 1){
			return false;
		}
		//$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE authorid=" . S::sqlEscape($authorId, false));
		pwQuery::delete($this->_tableName, 'authorid=:authorid', array($authorId));
		return $this->_db->affected_rows();
	}
	
	function setTpcStatusByThreadIds($tids,$mask){
		$this->_db->update("UPDATE $this->_tableName SET tpcstatus=tpcstatus & ".S::int($mask)." WHERE tid IN(".S::sqlImplode($tids).")");
	}

}