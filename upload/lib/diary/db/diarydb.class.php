<?php
/**
 * 日志数据库操作文件
 * 
 * @package diary
 */

!defined('P_W') && exit('Forbidden');

/**
 * 日志数据库操作对象
 * 
 * @package PW_DiaryDB
 */

class PW_DiaryDB extends BaseDB {
	var $_tableName = 'pw_diary';
	var $_tableName2 = "pw_diarytype";
	var $_primaryKey = 'did';
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
	function getsByDids($dids){
		if(!$dids) return false;
		$dids = (is_array($dids)) ? S::sqlImplode($dids) : $dids;
		$query = $this->_db->query ( "SELECT * FROM ".$this->_tableName." WHERE did in(".$dids.") ORDER BY postdate DESC " );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function countDiarysByUids($userIds, $diaryTypeId = null) {
		if(!$userIds) return false;
		$userIds = (is_array($userIds)) ? $this->_getImplodeString($userIds) : $userIds ;
		$sql = " SELECT COUNT(*) FROM ".$this->_tableName.
								" WHERE privacy!= 2 AND uid IN(".$userIds.")";
		if (!is_null($diaryTypeId)) $sql .= " AND dtid=".$this->_addSlashes($diaryTypeId);
		return $this->_db->get_value($sql);
	}
	
	function findUserDiarysByUids($userIds, $page = 1, $perpage =20 ,$diaryTypeId = null) {
		if(!$userIds) return false;
		$userIds = (is_array($userIds)) ? $this->_getImplodeString($userIds) : $userIds ;
		$result = array();
		$page = (int)$page;
		$perpage = (int)$perpage;
		if ($page <= 0 || $perpage <= 0) return $result;
		
		$offset = ($page -1 ) * $perpage;
		if (!is_null($diaryTypeId)) $addSql .= " AND A.dtid=".$this->_addSlashes($diaryTypeId);
		$sql = " SELECT A.*, B.name AS typename FROM ".$this->_tableName." A".
				" LEFT JOIN ".$this->_tableName2. " B".
				" ON A.dtid=B.dtid".
				" WHERE A.privacy!= 2 AND A.uid IN(".$userIds.") $addSql ".
				" ORDER BY A.postdate DESC".
				$this->_Limit($offset, $perpage);
		$query = $this->_db->query($sql);
		while ($rt = $this->_db->fetch_array($query)) {
			$result[] = $rt;
		}
		
		return $result;
	}
	
	/**
	 * 根据用户uid 统计日志数量
	 * 
	 * @param $userId
	 * @param string $diaryTypeId   日志分类
	 * @param $privacy		日志权限 array(0,1,2) 全站可见，仅好友可见，仅自己可见
	 */
	function countUserDiarys($userId, $diaryTypeId = null, $privacy = array()) {
		
		$sqlAdd = "";
		if (!is_null($diaryTypeId)) $sqlAdd .= " AND dtid=".$this->_addSlashes($diaryTypeId);
		if ($privacy && is_array($privacy)) $sqlAdd .= " AND privacy IN (".$this->_getImplodeString($privacy).")";
		
		$sql = " SELECT COUNT(*) FROM ".$this->_tableName.
								" WHERE uid=".$this->_addSlashes($userId).$sqlAdd;
	
		return $this->_db->get_value($sql);
	}
	
	function findUserDiarys($userId, $page = 1, $perpage = 20, $diaryTypeId = null, $privacy = array()) {
	
		$sqlAdd = "";
		if (!is_null($diaryTypeId)) $sqlAdd .= "AND dtid=".$this->_addSlashes($diaryTypeId);
		if ($privacy && is_array($privacy)) $sqlAdd .= " AND privacy IN (".$this->_getImplodeString($privacy).")";
		
		$page = intval($page);
		$perpage = intval($perpage);
		if ($page <= 0 || $perpage <= 0) return array();
		$offset = ($page - 1) * $perpage;
		$result = array();
		$query = $this->_db->query("SELECT * FROM ".$this->_tableName.
								" WHERE uid=".$this->_addSlashes($userId).$sqlAdd.
								" ORDER BY postdate DESC ".$this->_Limit($offset, $perpage)
							);
		while ($rt = $this->_db->fetch_array($query)) {
			$result[] = $rt;
		}
		
		return $result;
	}
	
	function findDiaryTypeByUid($userId) {
		$result = array();
		$query = $this->_db->query("SELECT * FROM ".$this->_tableName2.
									" WHERE uid=".$this->_addSlashes($userId));
		while ($rt = $this->_db->fetch_array($query)) {
			$result[$rt['dtid']] = $rt;
		}
		
		return $result;
	}
	
	function getDiaryTypeBydtid($dtid) {
		return $this->_db->get_one("SELECT * FROM ".$this->_tableName2." WHERE dtid =".$this->_addSlashes($dtid)." LIMIT 1");
	
	}
	
	
	
	/**
	 * 添加日志分类
	 * 
	 * @param array $data
	 */
	function insertDiaryType($data = array()) {
		$this->_db->update("INSERT INTO ".$this->_tableName2.
							" SET ".$this->_getUpdateSqlString($data));
		return $this->_db->insert_id();
	}
	
	function updateDiaryTypeByDtid($dtid, $data = array()) {
		$this->_db->update("UPDATE ".$this->_tableName2.
							" SET ". $this->_getUpdateSqlString($data). " WHERE dtid=".$this->_addSlashes($dtid));
		return $this->_db->affected_rows();
	}
	
	function deleteDiaryType($dtid) {
		$this->_db->update("DELETE FROM ".$this->_tableName2." WHERE dtid=".$this->_addSlashes($dtid));
		return $this->_db->affected_rows();
	}
	
	function updateDiaryByDtid($data = array() ,$dtid) {
		/**
		$this->_db->update("UPDATE ".$this->_tableName.
							" SET ". $this->_getUpdateSqlString($data). " WHERE dtid=".$this->_addSlashes($dtid));
		**/
		pwQuery::update('pw_diary', 'dtid=:dtid', array($dtid), $data);
		return $this->_db->affected_rows();
	}
	
	function countDiaryTypeNum($id,$exp='+1') {
		$num = intval(trim($exp,'+-'));
		if (strpos($exp,'+') !== false) {
			$this->_db->update("UPDATE ".$this->_tableName2." SET num=num+".$this->_addSlashes($num,false)." WHERE dtid=".$this->_addSlashes($id));
		} else {
			$this->_db->update("UPDATE ".$this->_tableName2." SET num=num-".$this->_addSlashes($num,false)." WHERE dtid=".$this->_addSlashes($id));
		}
		return $this->_db->affected_rows();
	}
	
	/**
	 * 用户不同隐私权限日志的数量
	 * 
	 * @param int $userId
	 * @param array() $privacy     0  全站可见  1 仅好友可见   2仅自己可见
	 */
	function findUserDiaryByPrivacy($userId, $privacy = array()) {
		
		$sqlAdd = "";
		if ($privacy && is_array($privacy)) $sqlAdd .= " AND privacy IN (".$this->_getImplodeString($privacy).")";
		
		$result = array();
		$query = $this->_db->query("SELECT * FROM ".$this->_tableName.
							" WHERE uid=".$this->_addSlashes($userId).$sqlAdd);
		while ($rt = $this->_db->fetch_array($query)) {
			$result[] = $rt;
		}
		return $result;
	}
	
	function delDiaryByUids($uids) {
		if(!$uids) return false;
		/**
		$uids = is_array($uids) ? $this->_getImplodeString($uids) : $this->_addSlashes($uids);
		return $this->_db->update("DELETE FROM ".$this->_tableName. " WHERE uid IN( " .$uids. " )");
		**/
		$uids = is_array($uids) ? $uids : array($uids);
		return pwQuery::delete('pw_diary', 'uid IN(:uid)', array($uids));
	}
	
	function delDiaryTypeByUids($uids) {
		if(!$uids) return false;
		$uids = is_array($uids) ? $this->_getImplodeString($uids) : $this->_addSlashes($uids);
		return $this->_db->update("DELETE FROM ".$this->_tableName2. " WHERE uid IN( " .$uids. " )");
	}
	/**
	 * 注意只提供搜索服务
	 * @version phpwind 8.0
	 */
	function countSearch($sql){
		$result = $this->_db->get_one ( $sql );
		return ($result) ? $result['total'] : 0;
	}
	/**
	 * 注意只提供搜索服务
	 * @version phpwind 8.0
	 */
	function getSearch($sql){
		$query = $this->_db->query ($sql);
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getLatestDiarysCount($starttime, $endtime) {
		$_sql_where = '';
		
		if ($starttime) {
			$_sql_where .= " AND postdate > ".$this->_addSlashes($starttime);
		}
		
		if ($endtime) {
			$_sql_where .= " AND postdate < ".$this->_addSlashes($endtime);
		}
		
		$total = $this->_db->get_value("SELECT count(*) as total FROM ".$this->_tableName." WHERE privacy=0 $_sql_where ORDER BY did DESC ");
		return ($total<500) ? $total :500;
	}

	function getLatestDiarys($starttime = '', $endtime = '', $offset, $limit) {
		$_sql_where = '';
		
		if ($starttime) {
			$_sql_where .= " AND postdate > ".$this->_addSlashes($starttime);
		}
		
		if ($endtime) {
			$_sql_where .= " AND postdate < ".$this->_addSlashes($endtime);
		}
		
		$query = $this->_db->query ("SELECT * FROM ".$this->_tableName." WHERE privacy=0 $_sql_where ORDER BY did DESC ".$this->_Limit($offset, $limit));
		return $this->_getAllResultFromQuery ( $query );
	}
}