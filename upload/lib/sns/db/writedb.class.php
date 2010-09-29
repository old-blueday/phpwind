<?php
/**
 * 记录数据库操作对象
 * 
 * @package Write
 */

!defined('P_W') && exit('Forbidden');

/**
 * 记录数据库操作对象
 * 
 * @package Write
 */
class PW_WriteDB extends BaseDB {
	var $_tableName = "pw_owritedata";
	var $_primaryKey = 'id';

	/**
	 * 添加记录
	 * 
	 * @param array $fieldsData 数据数组，以数据库字段为key
	 * @return int 新增id
	 */
	function add($fieldsData) {
		if (!is_array($fieldsData) || !count($fieldsData)) return 0;
		$this->_db->update("INSERT INTO " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fieldsData));
		return $this->_db->insert_id();
	}
	
	function delete($writeId) {
		$this->_db->update("DELETE FROM " . $this->_tableName . " WHERE id=" . intval($writeId) . " LIMIT 1");
		return $this->_db->affected_rows();
	}
	
	function countUserWrites($userId) {
		return $this->_db->get_value("SELECT COUNT(*) FROM " . $this->_tableName . " WHERE uid=".$this->_addSlashes($userId));
	}
	
	function findUserWritesInPage($userId, $page, $perpage) {
		$page = intval($page);
		$perpage = intval($perpage);
		if ($page <= 0 || $perpage <= 0) return array();
		
		$offset = ($page - 1) * $perpage;
		$query = $this->_db->query("SELECT w.*,m.username,m.groupid FROM ".$this->_tableName." w LEFT JOIN pw_members m ON w.touid=m.uid WHERE w.uid=".$this->_addSlashes($userId)." ORDER BY w.id DESC LIMIT $offset,$perpage");
		return $this->_getAllResultFromQuery($query);
	}
	

	function countUserWritesSqlByIn($userIds) {
		return $this->_db->get_value("SELECT COUNT(*) FROM " . $this->_tableName . 
									" WHERE uid IN(" . $this->_getImplodeString($userIds, false). ")");
	}
	
	
	/**
	 * 
	 * 根据用户uid找出的记录
	 * 
	 * @param array $userIds
	 * @param int $page
	 * @param int $perpage
	 */
	function findUserWritesByUids($userIds, $page=1, $perpage=20) {
		$page = intval($page);
		$perpage = intval($perpage);
		if ($page <= 0 || $perpage <= 0) return array();

		$offset = ($page - 1) * $perpage;
		$query = $this->_db->query("SELECT w.*, m.username, m.icon, m.groupid FROM ".
									$this->_tableName." w".
									" LEFT JOIN pw_members m".
									" ON w.uid=m.uid".
									" WHERE w.uid IN( ".$this->_getImplodeString($userIds).
									") ORDER BY w.id DESC LIMIT $offset,$perpage");
		while ($rt = $this->_db->fetch_array($query)) {
			$result[] = $rt;
		}
		return $result;
	}
	
	/**
	 * 取得@我的记录数量
	 * 
	 * @param int	$userId		@我的     touid
	 */
	function countWritesToUser($userId){
		
		return $this->_db->get_value( " SELECT COUNT(*) FROM ".$this->_tableName.
										" WHERE touid=".$this->_addSlashes($userId)
									);
	}
	
	
	function findWritesToUser($userId, $page=1, $perpage=20) {
		$page = intval($page);
		$perpage = intval($perpage);
		if ($page <= 0 || $perpage <= 0) return array();
		
		$offset = ($page - 1 ) * $perpage;
		$query = $this->_db->query(" SELECT w.*,m.username,m.icon,m.groupid FROM ".
									 $this->_tableName." w".
									 " LEFT JOIN pw_members m".
									 " ON w.uid=m.uid".
									 " WHERE w.touid=".$this->_addSlashes($userId).
									 " ORDER BY w.id DESC".$this->_Limit($offset, $perpage)
									 );
		$result = array();
		while ($rt = $this->_db->fetch_array($query)) {
			$result[] = $rt;
		}
		
		return $result;
	}
	
	
	function getlLatestWritesbyUid($userId) {
		return $this->_db->get_one("SELECT postdate,content FROM ".
						 $this->_tableName." WHERE uid=".$this->_addSlashes($userId)."ORDER BY id DESC LIMIT 1");
	}
}
