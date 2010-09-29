<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );

class PW_RateDB extends BaseDB {
	var $_tableName = "pw_rate";

	/**
	 * 增加一条用户评价记录
	 *
	 * @param array $fieldData
	 * @return lastinsertId
	 */
	function add($fieldData) {
		$this->_db->update ( "INSERT INTO " . $this->_tableName . " SET " . $this->_getUpdateSqlString ( $fieldData ) );
		return $this->_db->insert_id ();
	}

	/**
	 * 获取用户参加某个对象的评价的信息，主要用于用户是否还能评价
	 *
	 * @param int $userId
	 * @param int $objectId
	 * @param int $optionId
	 * @return array
	 */
	function getsByUserId($userId, $objectId, $typeId) {
		return $this->_db->get_one ( "SELECT * FROM " . $this->_tableName . " WHERE typeid=" . $typeId . " AND objectid=" . $objectId . " and uid=" . $userId );
	}

	function getsByIp($ip, $objectId, $typeId) {
		return $this->_db->get_one ( "SELECT * FROM " . $this->_tableName . " WHERE typeid=" . $typeId . " AND objectid=" . $objectId . " and uid=0 and ip='" . $ip . "'" );
	}

	function countByUserId($userId) {
		$today = explode ( ",", date ( "Y,m,d", time () ) );
		$created_at = mktime ( 0, 0, 0, $today [1], $today [2], $today [0] );
		$result = $this->_db->get_one ( "SELECT COUNT(*) AS total FROM " . $this->_tableName . " WHERE uid=" . $userId . " AND created_at>" . $created_at . " LIMIT 1" );
		return $result ['total'];
	}

	function countByIp($ip) {
		$today = explode ( ",", date ( "Y,m,d", time () ) );
		$created_at = mktime ( 0, 0, 0, $today [1], $today [2], $today [0] );
		$result = $this->_db->get_one ( "SELECT COUNT(*) AS total FROM " . $this->_tableName . " WHERE uid=0 AND created_at>" . $created_at . " AND ip='" . $ip . "'  LIMIT 1" );
		return $result ['total'];
	}

	/**
	 * 本周选项之最
	 *
	 * @param unknown_type $typeId
	 * @return unknown
	 */
	function getByWeek($typeId) {
		$beforeWeek = strtotime ( "-1 week" );
		$sql = "select *,max(result.total) as max from (SELECT optionid,objectid,COUNT(*) AS total FROM " . $this->_tableName . " WHERE typeid=" . $typeId . " AND created_at>" . $beforeWeek . " GROUP BY optionid,objectid ORDER BY null) AS result GROUP BY optionid ORDER BY null";
		$query = $this->_db->query ( $sql );
		return $this->_getAllResultFromQuery ( $query );
	}

	function getFromTmpTableByWeek($typeId) {
		$beforeWeek = strtotime ( "-1 week" );
		$mysql = $this->_db;
		$mysql->query ( "CREATE TEMPORARY TABLE tmp_rate_table (optionid int(10) NOT NULL,objectid int NOT NULL,total int NOT NULL)" );
		//$mysql->query ( "LOCK TABLES " . $this->_tableName . " read" );
		$mysql->query ( "INSERT INTO tmp_rate_table SELECT optionid,objectid,COUNT(*) AS total FROM " . $this->_tableName . " WHERE typeid=" . $typeId . " and created_at>" . $beforeWeek . " GROUP BY optionid,objectid ORDER BY null" );
		$query = $mysql->query ( "SELECT *,MAX(total) AS max FROM tmp_rate_table GROUP BY optionid ORDER BY null" );
		//$mysql->query ( "UNLOCK TABLES" );
		$mysql->query ( "DROP TABLE tmp_rate_table" );
		$result = $this->_getAllResultFromQuery ( $query );
		return $result;
	}

	function getStruct() {
		return array ('objectid', 'optionid', 'typeid', 'uid', 'created_at', 'ip' );
	}

}
?>