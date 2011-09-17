<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND_VERSION_DIR . '/dao/dao_basedb.class.php';
class CloudWind_Search_MembersDb extends CloudWind_Base_Db {
	var $_tableName = "pw_members";
	var $_primaryKey = 'uid';
	function getsByUserIds($userId) {
		$userId = (is_array ( $userId )) ? CLOUDWIND_SECURITY_SERVICE::sqlImplode ( $userId ) : $userId;
		$query = $this->_db->query ( "SELECT uid,username,regdate FROM " . $this->_tableName . " WHERE uid in(" . $userId . ")" );
		return $this->_getAllResultFromQuery ( $query );
	}
	function getMembersByPage($page, $perpage) {
		list ( $start, $end ) = $this->_getRange ( $page, $perpage );
		return $this->_getMembersByPage ( $start, $end );
	}
	function _getMembersByPage($start, $end) {
		$query = $this->_db->query ( "SELECT uid,username,regdate FROM " . $this->_tableName . "  WHERE uid >= " . $this->_addSlashes ( $start ) . " AND uid <= " . $this->_addSlashes ( $end ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getIdsByRange($minId, $maxId) {
		$query = $this->_db->query ( "SELECT uid FROM " . $this->_tableName . "  WHERE uid >= " . $this->_addSlashes ( $minId ) . " AND uid <= " . $this->_addSlashes ( $maxId ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function maxMemberId() {
		$result = $this->_db->get_one ( "SELECT max(uid) as max FROM $this->_tableName" );
		return  ($result && $result ['max'] > 0) ? $result ['max'] : 0;
	}
	
	function countMembersNum() {
		$result = $this->_db->get_one ( "SELECT count(uid) as total FROM $this->_tableName" );
		return ($result && $result ['total'] > 0) ? $result ['total'] : 0;
	}
}
?>