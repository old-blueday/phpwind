<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND_VERSION_DIR . '/dao/dao_basedb.class.php';
class CloudWind_Search_DiarysDb extends CloudWind_Base_Db {
	var $_tableName = 'pw_diary';
	var $_primaryKey = 'did';
	
	function getsByDids($dids) {
		$dids = (is_array ( $dids )) ? CLOUDWIND_SECURITY_SERVICE::sqlImplode ( $dids ) : $dids;
		$query = $this->_db->query ( "SELECT did,uid,username,subject,content,postdate FROM " . $this->_tableName . " WHERE did in(" . $dids . ")" );
		return $this->_getAllResultFromQuery ( $query );
	}
	function getDiarysByPage($page, $perpage) {
		list ( $start, $end ) = $this->_getRange ( $page, $perpage );
		return $this->_getDiarysByPage ( $start, $end );
	}
	function _getDiarysByPage($start, $end) {
		$query = $this->_db->query ( "SELECT did,uid,username,subject,content,postdate FROM " . $this->_tableName . "  WHERE did >= " . $this->_addSlashes ( $start ) . " AND did <= " . $this->_addSlashes ( $end ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getDiarysByPostDate($postDate) {
		$postDate = intval ( $postDate );
		$query = $this->_db->query ( "SELECT did FROM " . $this->_tableName . "  WHERE  postdate > " . $this->_addSlashes ( $postDate ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getIdsByRange($minId, $maxId) {
		$query = $this->_db->query ( "SELECT did FROM " . $this->_tableName . "  WHERE did >= " . $this->_addSlashes ( $minId ) . " AND did <= " . $this->_addSlashes ( $maxId ) );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function maxDiaryId() {
		$result = $this->_db->get_one ( "SELECT max(did) as max FROM $this->_tableName" );
		return  ($result && $result ['max'] > 0) ? $result ['max'] : 0;
	}
	
	function countDiarysNum() {
		$result = $this->_db->get_one ( "SELECT count(did) as total FROM $this->_tableName" );
		return ($result && $result ['total'] > 0) ? $result ['total'] : 0;
	}
}