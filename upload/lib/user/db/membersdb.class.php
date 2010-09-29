<?php
!defined('P_W') && exit('Forbidden');

class PW_MembersDB extends BaseDB {
	var $_tableName = "pw_members";
	var $_memberDataTableName = "pw_memberdata";
	var $_memberInfoTableName = "pw_memberinfo";
	var $_primaryKey = 'uid';
	
	function get($id) {
		return $this->_get($id);
	}
	
	function getWithJoin($userId, $withMainTable = true, $withMemberDataTable = false, $withMemberInfoTable = false) {
		$userId = intval($userId);
		if ($userId <= 0) return null;
		if (!$withMainTable && !$withMemberDataTable && !$withMemberInfoTable) return null;
		
		$tables = array('a' => $this->_tableName, 'b' => $this->_memberDataTableName, 'c' => $this->_memberInfoTableName);
		$selects = array('a' => $withMainTable, 'b' => $withMemberDataTable, 'c' => $withMemberInfoTable);
		
		$fields = array();
		$firstTable = null;
		$firstAlias = null;
		$leftJoins = array();
		foreach ($tables as $alias => $tableName) {
			if (!$selects[$alias]) continue;
			$fields[$alias] = $alias . ".*";
			if (null === $firstTable) {
				$firstTable = $tableName;
				$firstAlias = $alias;
			} else {
				$leftJoins[] = " LEFT JOIN " . $tableName . " AS " . $alias . " ON " . $firstAlias . ".uid=" . $alias . ".uid ";
			}
		}
		if ($withMemberDataTable && $withMemberInfoTable) { //TODO refactor
			unset($fields['b']);
			$fields['b'] = "b.*, c.credit AS creditinfo";
		}
		return $this->_db->get_one("SELECT " . implode(',', $fields) . " FROM " . $firstTable . " AS " . $firstAlias . " " . implode(' ', $leftJoins) . " WHERE " . $firstAlias . ".uid=" . $this->_addSlashes($userId));
	}
	
	function insert($fieldData) {
		return $this->_insert($fieldData);
	}
	
	function update($fieldData, $id) {
		return $this->_update($fieldData, $id);
	}
	
	function updates($fieldData, $ids) {
		if (!$this->_check() || !$fieldData || empty($ids)) return false;
		$this->_db->update("UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fieldData) . " WHERE " . $this->_primaryKey . " IN (" . $this->_getImplodeString($ids) . ")");
		return $this->_db->affected_rows();
	}
	
	function increase($userId, $increments) {
		$userId = intval($userId);
		if ($userId <= 0 || !is_array($increments)) return 0;
		
		$incrementStatement = array();
		foreach ($increments as $field => $offset) {
			$offset = intval($offset);
			if (!$offset) continue;
			$incrementStatement[] = $field . "=" . $field . "+" . $offset;
		}
		if (empty($incrementStatement)) return 0;
		
		$this->_db->update("UPDATE " . $this->_tableName . " SET " . implode(", ", $incrementStatement) . " WHERE uid=" . $this->_addSlashes($userId));
		return $this->_db->affected_rows();
	}
	
	function delete($id) {
		return $this->_delete($id);
	}
	
	function count() {
		return $this->_count();
	}
	
	/**
	 * 更新userstatus字段
	 * 
	 * @param int $userId 用户id
	 * @param int $bit 用户状态类型 常量：PW_USERSTATUS_*
	 * @param bool|int $status 状态值，0-false, 1-true, other
	 * @param int $num 所占bit位数
	 * @return int 更新条数
	 */
	function setUserStatus($userId, $bit, $status = true, $num = 1) {
		list($userId, $bit, $num) = array(intval($userId), intval($bit), intval($num));
		if ($userId <= 0 || $bit <= 0 || $num <= 0) return false;
		
		$status = sprintf('%0' . $num . 'b', $status); // to binary
		

		--$bit;
		$userstatus = array();
		$userstatus[] = '&~((pow(2, ' . $num . ') - 1)<<' . $bit . ')'; //alacner said: clean all bits
		for ($i = $num - 1; $i >= 0; $i--) {
			if (isset($status[$i]) && $status[$i]) {
				$userstatus[] = '|(1<<' . $bit . ')';
			} else {
				$userstatus[] = '&~(1<<' . $bit . ')';
			}
			++$bit;
		}
		
		$userstatus = 'userstatus=userstatus' . implode('', $userstatus);
		$this->_db->update("UPDATE " . $this->_tableName . " SET $userstatus WHERE uid=" . $this->_addSlashes($userId));
		return $this->_db->affected_rows();
	}
	
	function getUsersByUserNames($userNames) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE username IN(" . pwImplode($userNames) . ")");
		return $this->_getAllResultFromQuery($query);
	}
	
	function getUsersByUserIds($userIds) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE uid IN(" . pwImplode($userIds) . ")");
		return $this->_getAllResultFromQuery($query, 'uid');
	}
	
	function getUserByUserName($userName, $fields = '*') {
		if (!$userName) return false;
		return $this->_db->get_one("SELECT $fields FROM " . $this->_tableName . " WHERE username = " . $this->_addSlashes($userName));
	}
	
	/**
	 * 根据邮件内容获得论坛注册用户
	 * @author papa
	 * @param Array $emails
	 * @return Array:
	 */
	function getUserByUserEmails($emails) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE email IN (" . pwImplode($emails) . ")");
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 根据groupid获取用户
	 * 
	 * @param array $groupIds groupId数组
	 * @return array
	 */
	function getUsersByGroupIds($groupIds) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE groupid IN(" . pwImplode($groupIds) . ")");
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 根据groupid获取用户
	 * 
	 * @param array $groupIds groupId
	 * @return array
	 */
	function getUsersByGroupId($groupId) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE groupid = " . $this->_addSlashes($groupId));
		return $this->_getAllResultFromQuery($query);
	}
	
	function getUserInfosByUserIds($userIds) {
		$userIds = (is_array($userIds)) ? pwImplode($userIds) : $userIds;
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " m LEFT JOIN " . $this->_memberDataTableName . " md ON m.uid=md.uid WHERE m.uid IN(" . $userIds . ")");
		return $this->_getAllResultFromQuery($query, 'uid');
	}
	
	function findUsersOrderByUserId($limit = 1) {
		$limit = intval($limit);
		if ($limit <= 0) return array();
		
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " ORDER BY uid DESC LIMIT " . $limit);
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 注意只提供搜索服务
	 * @version phpwind 8.0
	 */
	function countSearch($keywords) {
		$result = $this->_db->get_one("SELECT COUNT(*) as total FROM " . $this->_tableName . " WHERE username like " . pwEscape("%$keywords%") . " LIMIT 1");
		return ($result) ? $result['total'] : 0;
	}
	
	/**
	 * 注意只提供搜索服务
	 * @version phpwind 8.0
	 */
	function getSearch($keywords, $offset, $limit) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE username like " . pwEscape("%$keywords%") . " LIMIT " . $offset . "," . $limit);
		return $this->_getAllResultFromQuery($query);
	}

}
?>