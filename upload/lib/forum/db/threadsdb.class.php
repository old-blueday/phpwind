<?php
!defined('P_W') && exit('Forbidden');
class PW_ThreadsDB extends BaseDB {
	var $_tableName  = 'pw_threads';
	var $_tableName2 = 'pw_tmsgs';
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
			$threads = $threads + $t;
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
		$threadIds = (is_array($threadIds)) ? pwImplode($threadIds) : $threadIds;
		$query = $this->_db->query ( "SELECT t.*,th.content FROM ".$this->_tableName." t left join ".$this->_tableName2." th on t.tid=th.tid WHERE t.ifcheck = 1 AND t.fid != 0  AND t.tid in(".$threadIds.") ORDER BY t.postdate DESC" );
		return $this->_getAllResultFromQuery ( $query );
	}
	function getLatestThreads($offset,$limit){
		$threadIds = $this->_getLatestThreads($offset,$limit);
		if(!$threadIds) return false;
		$tmp = array();
		foreach($threadIds as $t){
			$tmp[] = $t['tid'];
		}
		return $this->getsBythreadIds($tmp);
	}
	
	function _getLatestThreads($offset,$limit){
		$query = $this->_db->query ( "SELECT t.tid FROM ".$this->_tableName." t WHERE t.ifcheck = 1 ORDER BY t.postdate DESC LIMIT " . $offset . "," . $limit );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getDigestThreads($uid, $digest, $offset, $limit){
		$threadIds = $this->_getDigestThreads($uid,$digest,$offset,$limit);
		if(!$threadIds) return false;
		$tmp = array();
		foreach($threadIds as $t){
			$tmp[] = $t['tid'];
		}
		return $this->getsBythreadIds($tmp);
	}
	
	function _getDigestThreads($uid,$digest,$offset,$limit){
		$_sql_where = $uid ? ' AND t.authorid=' . pwEscape($uid) : '';
		$digest = (is_array($digest)) ? pwImplode($digest) : $digest;
		$query = $this->_db->query ( "SELECT t.tid FROM ".$this->_tableName." t WHERE t.ifcheck = 1{$_sql_where} AND t.digest IN (" .$digest. ") ORDER BY t.postdate DESC LIMIT " . $offset . "," . $limit );
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
}