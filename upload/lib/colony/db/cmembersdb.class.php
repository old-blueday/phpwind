<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );

class PW_CmembersDB extends BaseDB {
	var $_tableName = "pw_cmembers";
	var $_colonysTableName = "pw_colonys";
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
	function getUserIdsByColonyId($colonyId){
		$query = $this->_db->query ( "SELECT uid  FROM " . $this->_tableName. " WHERE colonyid = ".$this->_addSlashes($colonyId));
		return $this->_getAllResultFromQuery ( $query );
	}
	
	/**
	 * 批量获取用户群组信息
	 *
	 * @param array $userIds
	 * @return array
	 */
	function getsCmemberAndColonyByUserIds($userIds){
		$query = $this->_db->query ( "SELECT c.uid,cy.id,cy.cname"
							. " FROM ". $this->_tableName ." c LEFT JOIN ". $this->_colonysTableName ." cy ON cy.id=c.colonyid"
							. " WHERE c.uid IN(".S::sqlImplode($userIds,false).") AND c.ifadmin!='-1'");
		return $this->_getAllResultFromQuery ( $query, 'uid');		
	}
}
?>