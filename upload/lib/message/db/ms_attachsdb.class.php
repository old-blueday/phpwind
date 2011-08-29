<?php
!defined('P_W') && exit('Forbidden');
class PW_Ms_AttachsDB extends BaseDB {
	var $_tableName = 'pw_ms_attachs';
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
	function addAttachs($fieldDatas){
		return $this->_db->update("INSERT INTO ".$this->_tableName." (uid,aid,mid,rid,status,created_time) VALUES  ".S::sqlMulti($fieldDatas,FALSE));
	}
	function getAttachsByMessageId($userId,$messageId){
		$query = $this->_db->query ( "SELECT *  FROM " . $this->_tableName. " WHERE mid = ".$this->_addSlashes($messageId));
		return $this->_getAllResultFromQuery ( $query );
	}
	function getAttachsByMessageIds($messageIds){
		$query = $this->_db->query ( "SELECT *  FROM " . $this->_tableName. " WHERE mid in( ".S::sqlImplode($messageIds).")");
		return $this->_getAllResultFromQuery ( $query );
	}
	function deleteAttachsByMessageIds($messageIds){
		return $this->_db->update ( "DELETE FROM " . $this->_tableName. " WHERE mid in( ".S::sqlImplode($messageIds).")");
	}
	function getAllAttachs($start,$limit){
		$query = $this->_db->query ( "SELECT *  FROM " . $this->_tableName. " ORDER BY created_time DESC LIMIT ".$start.",".$limit);
		return $this->_getAllResultFromQuery ( $query );
	}
	function countAllAttachs(){
		$result = $this->_db->get_one (  "SELECT COUNT(*) as total FROM " . $this->_tableName );
		return $result['total'];
	}
}