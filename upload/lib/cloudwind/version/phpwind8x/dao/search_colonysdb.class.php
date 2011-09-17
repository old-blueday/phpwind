<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND_VERSION_DIR . '/dao/dao_basedb.class.php';
class CloudWind_Search_ColonysDb extends CloudWind_Base_Db {
	var $_tableName = "pw_colonys";
	var $_primaryKey = 'id';
	function getColonysByPage($page, $perpage) {
		list ( $start, $end ) = $this->_getRange ( $page, $perpage );
		return $this->_getColonysByPage ( $start, $end );
	}
	function _getColonysByPage($start, $end) {
		$query = $this->_db->query ( "SELECT id,classid,cname FROM " . $this->_tableName . "  WHERE id >= " . $this->_addSlashes ( $start ) . " AND id <= " . $this->_addSlashes ( $end ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getsByColonyIds($ids) {
		$ids = (is_array ( $ids )) ? CLOUDWIND_SECURITY_SERVICE::sqlImplode ( $ids ) : $ids;
		$query = $this->_db->query ( "SELECT id,classid,cname FROM " . $this->_tableName . " WHERE id in(" . $ids . ")" );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getIdsByRange($minId, $maxId) {
		$query = $this->_db->query ( "SELECT id FROM " . $this->_tableName . "  WHERE id >= " . $this->_addSlashes ( $minId ) . " AND id <= " . $this->_addSlashes ( $maxId ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function maxColonyId() {
		$result = $this->_db->get_one ( "SELECT max(id) as max FROM $this->_tableName" );
		return  ($result && $result ['max'] > 0) ? $result ['max'] : 0;
	}
	
	function countColonysNum() {
		$result = $this->_db->get_one ( "SELECT count(id) as total FROM $this->_tableName" );
		return ($result && $result ['total'] > 0) ? $result ['total'] : 0;
	}
}
?>