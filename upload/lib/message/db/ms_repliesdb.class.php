<?php
!defined('P_W') && exit('Forbidden');
class PW_Ms_RepliesDB extends BaseDB {
	var $_tableName = 'pw_ms_replies';
	var $_primaryKey = 'id';
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
	function getRepliesByMessageId($messageId){
		$query = $this->_db->query ( "SELECT *  FROM " . $this->_tableName. " WHERE parentid = ".$this->_addSlashes($messageId)." ORDER BY created_time ASC");
		return $this->_getAllResultFromQuery ( $query );
	}
	function deleteRepliesByMessageId($messageId){
		$query = $this->_db->query ( "DELETE FROM " . $this->_tableName. " WHERE parentid = ".$this->_addSlashes($messageId));
		return $this->_db->affected_rows ();
	}
	function updateRepliesByMessageId($fieldData,$messageId){
		$this->_db->update ( "UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString ( $fieldData ) . "WHERE parentid =" . $this->_addSlashes ( $messageId ) );
		return $this->_db->affected_rows ();
	}
	function updateRepliesByIds($fieldData,$ids){
		$ids = is_array($ids) ? S::sqlImplode($ids) : $ids;
		$this->_db->update ( "UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString ( $fieldData ) . "WHERE id in (".$ids.")" );
		return $this->_db->affected_rows ();
	}
	function deleteRepliesByMessageIds($messageIds){
		$messageIds = is_array($messageIds) ? S::sqlImplode($messageIds) : $messageIds;
		$query = $this->_db->query ( "DELETE FROM " . $this->_tableName. " WHERE parentid in ( ".$messageIds." )");
		return $this->_db->affected_rows ();
	}
	
}