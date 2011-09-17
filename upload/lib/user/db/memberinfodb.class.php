<?php
!defined('P_W') && exit('Forbidden');

class PW_MemberinfoDB extends BaseDB {
	var $_tableName = "pw_memberinfo";
	var $_primaryKey = 'uid';
	
	function get($id) {
		return $this->_get($id);
	}
	
	function insert($fieldData) {
		return $this->_insert($fieldData);
	}
	
	function update($fieldData, $id) {
		return $this->_update($fieldData, $id);
	}
	
	function updates($fieldData, $ids) {
		if (!$this->_check() || !$fieldData || empty($ids)) return false;
		/**
		$this->_db->update("UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fieldData) . " WHERE " . $this->_primaryKey . " IN (" . $this->_getImplodeString($ids) . ")");
		**/
		pwQuery::update('pw_memberinfo', 'uid IN(:uid)', array($ids), $fieldData);
		return $this->_db->affected_rows();
	}
	
	function increase($userId, $increments) {
		$userId = intval($userId);
		if ($userId <= 0 || !is_array($increments)) return 0;
		
		$incrementStatement = array();
		foreach ($increments as $field => $offset) {
			$offset = intval($offset);
			if (!$offset) continue;
			if ($offset<0){
				$incrementStatement[] = $field . "=" . $field   . $offset;
			}else{
				$incrementStatement[] = $field . "=" . $field . "+" . $offset;
			}
		}
		if (empty($incrementStatement)) return 0;
		
		//* $this->_db->update("UPDATE " . $this->_tableName . " SET " . implode(", ", $incrementStatement) . " WHERE uid=" . $this->_addSlashes($userId));
		$this->_db->update(pwQuery::buildClause("UPDATE :pw_table SET " . implode(", ", $incrementStatement) . " WHERE uid=:uid", array($this->_tableName, $userId)));
		
		return $this->_db->affected_rows();
	}
	
	function delete($id) {
		return $this->_delete($id);
	}
	
	/**
	 * 批量获取用户Info信息
	 *
	 * @param array $userIds
	 * @return array
	 */
	function getUsersByUserIds($userIds) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE uid IN(" . S::sqlImplode($userIds) . ")");
		return $this->_getAllResultFromQuery($query, 'uid');
	}	
}
?>