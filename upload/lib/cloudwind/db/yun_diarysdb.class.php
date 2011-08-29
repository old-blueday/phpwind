<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 日志DAO服务
 * 
 * @author Liu Hui <developer.liuhui@gmail.com> 2010-8-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2100 phpwind.com
 * @license
 */
require_once R_P . 'lib/cloudwind/db/yun_basedb.php';
class PW_YUN_DiarysDB extends YUN_BaseDB {
	var $_tableName = 'pw_diary';
	var $_primaryKey = 'did';
	
	function getsByDids($dids) {
		$dids = (is_array ( $dids )) ? pwImplode ( $dids ) : $dids;
		$query = $this->_db->query ( "SELECT did,uid,username,subject,content,postdate FROM " . $this->_tableName . " WHERE did in(" . $dids . ")" );
		return $this->_getAllResultFromQuery ( $query );
	}
	function getDiarysByPage($page, $perpage) {
		$start = ($page - 1) * $perpage;
		$start = intval ( $start );
		$perpage = intval ( $perpage );
		if ($perpage < 1)
			return false;
		$end = $perpage * $page;
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
}