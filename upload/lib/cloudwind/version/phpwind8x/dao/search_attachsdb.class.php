<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND_VERSION_DIR . '/dao/dao_basedb.class.php';
class CloudWind_Search_AttachsDb extends CloudWind_Base_Db {
	var $_tableName = "pw_attachs";
	var $_primaryKey = 'aid';
	function getAttachsByPage($page, $perpage) {
		list ( $start, $end ) = $this->_getRange ( $page, $perpage );
		return $this->_getAttachsByPage ( $start, $end );
	}
	function _getAttachsByPage($start, $end) {
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName . "  WHERE aid >= " . $this->_addSlashes ( $start ) . " AND aid <= " . $this->_addSlashes ( $end ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getsAttachsIds($aids) {
		$aids = (is_array ( $aids )) ? CLOUDWIND_SECURITY_SERVICE::sqlImplode ( $aids ) : $aids;
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName . " WHERE aid in(" . $aids . ")" );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getIdsByRange($minId, $maxId) {
		$query = $this->_db->query ( "SELECT aid FROM " . $this->_tableName . "  WHERE aid >= " . $this->_addSlashes ( $minId ) . " AND aid <= " . $this->_addSlashes ( $maxId ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function maxAttachId() {
		$result = $this->_db->get_one ( "SELECT max(aid) as max FROM $this->_tableName" );
		return  ($result && $result ['max'] > 0) ? $result ['max'] : 0;
	}
	
	function countAttachsNum() {
		$result = $this->_db->get_one ( "SELECT count(aid) as total FROM $this->_tableName" );
		return ($result && $result ['total'] > 0) ? $result ['total'] : 0;
	}
}
?>