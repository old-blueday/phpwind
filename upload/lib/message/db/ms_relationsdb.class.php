<?php
!defined('P_W') && exit('Forbidden');
class PW_Ms_RelationsDB extends BaseDB {
	var $_tableName = 'pw_ms_relations';
	var $_primaryKey = 'rid';
	var $_tableIndex = 'idx_uid_categoryid_modifiedtime';
	function insert($fieldData) {
		return $this->_insert($fieldData);
	}
	function update($fieldData, $id) {
		return $this->_update($fieldData, $id);
	}
	function delete($id) {
		return $this->_delete($id);
	}
	function get($id) {
		return $this->_get($id);
	}
	function count() {
		return $this->_count();
	}
	function addRelations($fieldDatas) {
		$this->_db->update("INSERT INTO " . $this->_tableName . " (uid,mid,categoryid,typeid,status,isown,created_time,modified_time) VALUES  " . S::sqlMulti($fieldDatas, FALSE));
		return $this->_db->insert_id();
	}
	function addReplyRelations($fieldDatas) {
		$this->_db->update("INSERT INTO " . $this->_tableName . " (uid,mid,categoryid,typeid,status,isown,relation,created_time,modified_time) VALUES  " . S::sqlMulti($fieldDatas, FALSE));
		return $this->_db->insert_id();
	}
	function getRelations($userId, $categoryId, $typeId, $offset, $limit) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND categoryid=" . $this->_addSlashes($categoryId) . " AND typeid=" . $this->_addSlashes($typeId) . " ORDER BY modified_time DESC LIMIT " . $offset . "," . $limit);
		return $this->_getAllResultFromQuery($query);
	}
	function countRelations($userId, $categoryId, $typeId) {
		$result = $this->_db->get_one("SELECT COUNT(*) as total FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND categoryid=" . $this->_addSlashes($categoryId) . " AND typeid=" . $this->_addSlashes($typeId) . " ORDER BY modified_time DESC LIMIT 1 ");
		return $result['total'];
	}
	function getAllRelations($userId, $categoryId, $offset, $limit) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " force index(" . $this->_tableIndex . ") WHERE uid = " . $this->_addSlashes($userId) . " AND categoryid=" . $this->_addSlashes($categoryId) . " ORDER BY modified_time DESC LIMIT " . $offset . "," . $limit);
		return $this->_getAllResultFromQuery($query);
	}
	function countAllRelations($userId, $categoryId) {
		$result = $this->_db->get_one("SELECT COUNT(*) as total FROM " . $this->_tableName . " force index(" . $this->_tableIndex . ") WHERE uid = " . $this->_addSlashes($userId) . " AND categoryid=" . $this->_addSlashes($categoryId) . " ORDER BY modified_time DESC LIMIT 1 ");
		return $result['total'];
	}
	function countAllByUserId($userId) {
		$userId = intval($userId);
		if ($userId < 1) return array();
		$query = $this->_db->query("SELECT COUNT(*) as total,categoryid FROM " . $this->_tableName . " WHERE uid = " . S::sqlEscape($userId) . " AND status = 1 GROUP BY categoryid");
		return $this->_getAllResultFromQuery($query,'categoryid');
	}
	function getRelationsByStatus($userId, $categoryId, $status, $offset, $limit) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND categoryid=" . $this->_addSlashes($categoryId) . " AND status=" . $this->_addSlashes($status) . " ORDER BY modified_time DESC LIMIT " . $offset . "," . $limit);
		return $this->_getAllResultFromQuery($query);
	}
	function countRelationsByStatus($userId, $categoryId, $status) {
		$result = $this->_db->get_one("SELECT COUNT(*) as total FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND categoryid=" . $this->_addSlashes($categoryId) . " AND status=" . $this->_addSlashes($status) . " ORDER BY modified_time DESC LIMIT 1 ");
		return $result['total'];
	}
	function getRelationsByIsown($userId, $categoryId, $typeId, $isown, $offset, $limit) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND categoryid=" . $this->_addSlashes($categoryId) . " AND typeid=" . $this->_addSlashes($typeId) . " AND isown=" . $this->_addSlashes($isown) . " ORDER BY modified_time DESC LIMIT " . $offset . "," . $limit);
		return $this->_getAllResultFromQuery($query);
	}
	//获取全部数据
	function getAllRelationsByIsown($userId, $categoryId, $typeId, $isown, $offset, $limit) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND categoryid=" . $this->_addSlashes($categoryId) . " AND isown=" . $this->_addSlashes($isown) . " ORDER BY modified_time DESC LIMIT " . $offset . "," . $limit);
		return $this->_getAllResultFromQuery($query);
	}
	function getRelationsByIsownAndUserId($userId, $isown, $status, $offset, $limit) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND isown=" . $this->_addSlashes($isown) . " AND status = " . $this->_addSlashes($status) . "  ORDER BY modified_time DESC LIMIT " . $offset . "," . $limit);
		return $this->_getAllResultFromQuery($query);
	}
	function getRelationsByStatusAndUserId($userId, $status, $offset, $limit) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND status = " . $this->_addSlashes($status) . "  ORDER BY modified_time DESC LIMIT " . $offset . "," . $limit);
		return $this->_getAllResultFromQuery($query);
	}
	function countRelationsByIsown($userId, $categoryId, $typeId, $isown) {
		$result = $this->_db->get_one("SELECT COUNT(*) as total FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND categoryid=" . $this->_addSlashes($categoryId) . " AND typeid=" . $this->_addSlashes($typeId) . " AND isown=" . $this->_addSlashes($isown) . " LIMIT 1 ");
		return $result['total'];
	}
	function getSpecialRelationsByIsown($userId, $categoryId, $typeId, $isown, $offset, $limit) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND categoryid=" . $this->_addSlashes($categoryId) . " AND isown=" . $this->_addSlashes($isown) . " ORDER BY modified_time DESC LIMIT " . $offset . "," . $limit);
		return $this->_getAllResultFromQuery($query);
	}
	function countSpecialRelationsByIsown($userId, $categoryId, $typeId, $isown) {
		$result = $this->_db->get_one("SELECT COUNT(*) as total FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND categoryid=" . $this->_addSlashes($categoryId) . " AND isown=" . $this->_addSlashes($isown) . " ORDER BY modified_time DESC LIMIT 1 ");
		return $result['total'];
	}
	function getRelationsByUserId($userId, $offset, $limit) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " ORDER BY modified_time DESC LIMIT " . $offset . "," . $limit);
		return $this->_getAllResultFromQuery($query);
	}
	function deleteRelationsByUserId($userId) {
		$query = $this->_db->query("DELETE FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId));
		return $this->_db->affected_rows();
	}
	function deleteRelations($userId, $relationIds) {
		$relationIds = is_array($relationIds) ? S::sqlImplode($relationIds) : $relationIds;
		$query = $this->_db->query("DELETE FROM " . $this->_tableName . " WHERE rid in( " . $relationIds . " ) AND uid = " . $this->_addSlashes($userId));
		return $this->_db->affected_rows();
	}
	function updateRelationsByUserId($fieldData, $userId, $relationIds) {
		$relationIds = is_array($relationIds) ? S::sqlImplode($relationIds) : $relationIds;
		$this->_db->update("UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fieldData) . " WHERE rid in ( " . $relationIds . " ) AND uid=" . $this->_addSlashes($userId));
		return $this->_db->affected_rows();
	}
	function updateRelationsByMessageId($fieldData, $messageId) {
		$this->_db->update("UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fieldData) . " WHERE mid =  " . $this->_addSlashes($messageId));
		return $this->_db->affected_rows();
	}
	function updateRelations($fieldData, $userId, $relationIds) {
		$relationIds = is_array($relationIds) ? S::sqlImplode($relationIds) : $relationIds;
		$this->_db->update("UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fieldData) . " WHERE rid in (  " . $relationIds . " ) AND uid= " . $this->_addSlashes($userId));
		return $this->_db->affected_rows();
	}
	function updateRelationsByUserIdAndMessageId($fieldData, $userId, $messageIds) {
		$messageIds = is_array($messageIds) ? S::sqlImplode($messageIds) : $messageIds;
		$this->_db->update("UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fieldData) . " WHERE mid in ( " . $messageIds . " ) AND uid=" . $this->_addSlashes($userId));
		return $this->_db->affected_rows();
	}
	function getRelation($userId, $relationId) {
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND rid = " . $this->_addSlashes($relationId) . " LIMIT 1");
	}
	function getUpRelation($userId, $categoryId, $relationId, $modified_time, $typeId = null) {
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND categoryid=" . $this->_addSlashes($categoryId) . ($typeId ? (" AND typeid=" . $this->_addSlashes($typeId)) : "") . " AND modified_time > " . $this->_addSlashes($modified_time) . " ORDER BY modified_time ASC LIMIT 1");
	}
	function getDownRelation($userId, $categoryId, $relationId, $modified_time, $typeId = null) {
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND categoryid=" . $this->_addSlashes($categoryId) . ($typeId ? (" AND typeid=" . $this->_addSlashes($typeId)) : "") . " AND modified_time < " . $this->_addSlashes($modified_time) . " ORDER BY modified_time DESC LIMIT 1");
	}
	
	function getDownInfoByType($userId, $categoryId, $relationId, $modified_time, $isown, $typeId = null) {
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND " . (($isown != 3) ? ("isown=" . $this->_addSlashes($isown)) : " 1 ") . " AND categoryid=" . $this->_addSlashes($categoryId) . ($typeId ? (" AND typeid=" . $this->_addSlashes($typeId)) : "") . " AND modified_time < " . $this->_addSlashes($modified_time) . " ORDER BY modified_time DESC  LIMIT 1");
	}
	function getUpInfoByType($userId, $categoryId, $relationId, $modified_time, $isown, $typeId = null) {
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND ". (($isown != 3) ? ("isown=" . $this->_addSlashes($isown)) : " 1 ") . " AND categoryid=" . $this->_addSlashes($categoryId) . ($typeId ? (" AND typeid=" . $this->_addSlashes($typeId)) : "") . " AND modified_time > " . $this->_addSlashes($modified_time) . " ORDER BY modified_time ASC LIMIT 1");
	}
	
	function getUpSpecialRelation($userId, $categoryId, $typeId, $relationId, $modified_time) {
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND categoryid=" . $this->_addSlashes($categoryId) . " AND modified_time > " . $this->_addSlashes($modified_time) . " ORDER BY modified_time ASC LIMIT 1");
	}
	function getDownSpecialRelation($userId, $categoryId, $typeId, $relationId, $modified_time) {
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND categoryid=" . $this->_addSlashes($categoryId) . " AND modified_time < " . $this->_addSlashes($modified_time) . " ORDER BY modified_time DESC LIMIT 1");
	}
	function deleteRelationsByUserIdAndCategoryId($userId, $categoryIds) {
		$categoryIds = is_array($categoryIds) ? S::sqlImplode($categoryIds) : $categoryIds;
		$query = $this->_db->query("DELETE FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND categoryid in( " . $categoryIds . " )");
		return $this->_db->affected_rows();
	}
	function updateRelationsBySegmentTime($fieldData, $segmentTime) {
		$this->_db->update("UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString($fieldData) . " WHERE modified_time <= " . $this->_addSlashes($segmentTime));
		return $this->_db->affected_rows();
	}
	function countAllRelationsByUserId($userId) {
		$result = $this->_db->get_one("SELECT COUNT(*) as total FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " LIMIT 1 ");
		return $result['total'];
	}
	function countSelfByUserId($userId, $timestamp) {
		$result = $this->_db->get_one("SELECT COUNT(*) as total FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND isown=1 AND modified_time >= " . $this->_addSlashes($timestamp) . " LIMIT 1 ");
		return $result['total'];
	}
	function countAllByUserIds($userIds, $typeIds) {
		$query = $this->_db->query("SELECT COUNT(*) as total,uid FROM " . $this->_tableName . " WHERE uid in( " . S::sqlImplode($userIds) . ") AND typeid in( " . S::sqlImplode($typeIds) . ") GROUP BY uid ORDER BY null");
		return $this->_getAllResultFromQuery($query);
	}
	function getRelationsNotRead($userId, $offset, $limit) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND status = 1 ORDER BY modified_time DESC LIMIT " . $offset . "," . $limit);
		return $this->_getAllResultFromQuery($query);
	}
	function countRelationsNotRead($userId) {
		$result = $this->_db->get_one("SELECT COUNT(*) as total FROM " . $this->_tableName . " WHERE uid = " . $this->_addSlashes($userId) . " AND status = 1 ORDER BY modified_time DESC LIMIT 1 ");
		return $result['total'];
	}
	function deleteRelationsByRelationIds($relationIds) {
		$relationIds = is_array($relationIds) ? S::sqlImplode($relationIds) : $relationIds;
		$query = $this->_db->query("DELETE FROM " . $this->_tableName . " WHERE rid in( " . $relationIds . " )");
		return $this->_db->affected_rows();
	}
	function getRelationsByMessageId($messageId) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE mid = " . $this->_addSlashes($messageId));
		return $this->_getAllResultFromQuery($query);
	}
	function getRelationsByRelationIds($relationIds) {
		$relationIds = is_array($relationIds) ? S::sqlImplode($relationIds) : $relationIds;
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE rid IN ( " . $relationIds . ")");
		return $this->_getAllResultFromQuery($query);
	}
	/**
	 * TODO 仅仅用于后台管理
	 */
	function deleteRelationsByMessageIds($messageIds) {
		$messageIds = is_array($messageIds) ? S::sqlImplode($messageIds) : $messageIds;
		$query = $this->_db->query("DELETE FROM " . $this->_tableName . " WHERE mid in( " . $messageIds . " )");
		return $this->_db->affected_rows();
	}
	/**
	 * TODO 仅仅用于后台管理
	 */
	function getManageRelations($sql) {
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " " . $sql);
		return $this->_getAllResultFromQuery($query);
	}
	/**
	 * TODO 仅仅用于后台管理
	 */
	function countManageRelations($sql) {
		$result = $this->_db->get_one("SELECT COUNT(*) as total FROM " . $this->_tableName . "  " . $sql);
		return $result['total'];
	}
	/**
	 * TODO 仅仅用于后台管理
	 */
	function deleteManageRelations($sql) {
		$query = $this->_db->query("DELETE FROM " . $this->_tableName . " " . $sql);
		return $this->_db->affected_rows();
	}
}