<?php
!defined('P_W') && exit('Forbidden');
class PW_Ms_SearchsDB extends BaseDB {
	var $_tableName = 'pw_ms_searchs';
	var $_primaryKey = 'rid';
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
	function addSearchs($fieldDatas){
		return $this->_db->update("INSERT INTO ".$this->_tableName." (uid,mid,typeid,create_uid,created_time) VALUES  ".S::sqlMulti($fieldDatas,FALSE));
	}
	function getsByTypeId($userId,$typeId,$createUserId,$offset,$limit){
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName. " WHERE uid = ".$this->_addSlashes($userId)." AND create_uid=".$this->_addSlashes($createUserId)." AND typeid=".$this->_addSlashes($typeId)." ORDER BY created_time DESC LIMIT " . $offset . "," . $limit );
		return $this->_getAllResultFromQuery ( $query );
	}
	function countByTypeId($userId,$typeId,$createUserId){
		$result = $this->_db->get_one ( "SELECT COUNT(*) as total FROM " . $this->_tableName. " WHERE uid = ".$this->_addSlashes($userId)." AND create_uid=".$this->_addSlashes($createUserId)." AND typeid=".$this->_addSlashes($typeId)." ORDER BY created_time DESC " );
		return $result['total'];
	}
	function getsByUserIdAndCreateUserId($userId,$createUserId,$offset,$limit){
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName. " WHERE uid = ".$this->_addSlashes($userId)." AND create_uid=".$this->_addSlashes($createUserId)." ORDER BY created_time DESC LIMIT " . $offset . "," . $limit );
		return $this->_getAllResultFromQuery ( $query );
	}
	function countByUserIdAndCreateUserId($userId,$createUserId){
		$result = $this->_db->get_one ( "SELECT COUNT(*) as total FROM " . $this->_tableName. " WHERE uid = ".$this->_addSlashes($userId)." AND create_uid=".$this->_addSlashes($createUserId)." ORDER BY created_time DESC LIMIT 1");
		return $result['total'];
	}
	function deletesByUserId($userId,$relationIds){
		$relationIds = is_array($relationIds) ? S::sqlImplode($relationIds) : $relationIds;
		$query = $this->_db->query ( "DELETE FROM " . $this->_tableName. " WHERE rid in( ".$relationIds. " ) AND uid = ".$this->_addSlashes($userId));
		return $this->_db->affected_rows ();
	}
	function deleteAll($userId){
		$query = $this->_db->query ( "DELETE FROM " . $this->_tableName. " WHERE uid = ".$this->_addSlashes($userId));
		return $this->_db->affected_rows ();
	}
	function deleteByMessageIds($messageIds){
		$messageIds = is_array($messageIds) ? S::sqlImplode($messageIds) : $messageIds;
		$query = $this->_db->query ( "DELETE FROM " . $this->_tableName. " WHERE mid in( ".$messageIds. " )");
		return $this->_db->affected_rows ();
	}
}