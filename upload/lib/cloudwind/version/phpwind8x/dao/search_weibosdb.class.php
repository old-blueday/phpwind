<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND_VERSION_DIR . '/dao/dao_basedb.class.php';
class CloudWind_Search_WeibosDb extends CloudWind_Base_Db {
	var $_tableName = "pw_weibo_content";
	var $_primaryKey = 'mid';
	function getWeibosByPage($page, $perpage) {
		list ( $start, $end ) = $this->_getRange ( $page, $perpage );
		return $this->_getWeibosByPage ( $start, $end );
	}
	function _getWeibosByPage($start, $end) {
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName . "  WHERE mid >= " . $this->_addSlashes ( $start ) . " AND mid <= " . $this->_addSlashes ( $end ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getsWeibosIds($aids) {
		$aids = (is_array ( $aids )) ? CLOUDWIND_SECURITY_SERVICE::sqlImplode ( $aids ) : $aids;
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName . " WHERE mid in(" . $aids . ")" );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getIdsByRange($minId, $maxId) {
		$query = $this->_db->query ( "SELECT mid FROM " . $this->_tableName . "  WHERE mid >= " . $this->_addSlashes ( $minId ) . " AND mid <= " . $this->_addSlashes ( $maxId ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function maxWeiboId() {
		$result = $this->_db->get_one ( "SELECT max(mid) as max FROM $this->_tableName" );
		return  ($result && $result ['max'] > 0) ? $result ['max'] : 0;
	}
	
	function countWeibosNum() {
		$result = $this->_db->get_one ( "SELECT count(mid) as total FROM $this->_tableName" );
		return ($result && $result ['total'] > 0) ? $result ['total'] : 0;
	}
}
?>