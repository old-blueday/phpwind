<?php
!defined('P_W') && exit('Forbidden');
class PW_Ms_MessagesDB extends BaseDB {
	var $_tableName = 'pw_ms_messages';
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
	function getMessagesByMessageIds($messageIds){
		$messageIds = is_array($messageIds) ? S::sqlImplode($messageIds) : $messageIds;
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName. " WHERE mid in (".$messageIds.") " );
		return $this->_getAllResultFromQuery ( $query );
	}
	function deleteMessagesByMessageIds($messageIds){
		$messageIds = is_array($messageIds) ? S::sqlImplode($messageIds) : $messageIds;
		$query = $this->_db->query ( "DELETE FROM " . $this->_tableName. " WHERE mid in (".$messageIds.") " );
		return $this->_db->affected_rows ();
	}
	function updateMessagesByMessageIds($fieldData,$messageIds){
		$messageIds = is_array($messageIds) ? S::sqlImplode($messageIds) : $messageIds;
		$this->_db->update ( "UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString ( $fieldData ) . " WHERE mid in (".$messageIds.") " );
		return $this->_db->affected_rows ();
	}
	/**
	 * TODO 注意:仅仅为后台管理调用操作
	 * @param $sql
	 * @return unknown_type
	 */
	function getManageMessages($sql){
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName. "  ".$sql );
		return $this->_getAllResultFromQuery ( $query );
	}
	/**
	 * TODO 注意:仅仅为后台管理调用操作
	 * @param $sql
	 * @return unknown_type
	 */
	function countManageMessages($sql){
		$result = $this->_db->get_one ( "SELECT COUNT(*) as total FROM " . $this->_tableName. "  ".$sql );
		return $result['total'];
	}
	/**
	 * TODO 注意:仅仅为后台管理调用操作
	 * @param $sql
	 * @return unknown_type
	 */
	function deleteManageMessages($sql){
		$query = $this->_db->query ( "DELETE FROM " . $this->_tableName. "  ".$sql );
		return $this->_db->affected_rows ();
	}
	
}