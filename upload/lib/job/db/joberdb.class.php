<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );

class PW_JoberDB extends BaseDB {
	var $_tableName = "pw_jober";

	function add($fieldData) {
		$this->_db->update ( "INSERT INTO " . $this->_tableName . " SET " . $this->_getUpdateSqlString ( $fieldData ) );
		return $this->_db->insert_id ();
	}

	function update($fieldData, $id) {
		$this->_db->update ( "UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString ( $fieldData ) . " WHERE id=" . $this->_addSlashes ( $id ) . " LIMIT 1" );
		return $this->_db->affected_rows ();
	}

	function delete($id) {
		$this->_db->update ( "DELETE FROM " . $this->_tableName . " WHERE id=" . $this->_addSlashes ( $id ) . " LIMIT 1" );
		return $this->_db->affected_rows ();
	}

	function get($id) {
		$id = intval($id);
		if($id<1){
			return null;
		}
		return $this->_db->get_one ( "SELECT * FROM " . $this->_tableName . " WHERE id=" . $this->_addSlashes ( $id ) . " LIMIT 1" );
	}
	
	function getByJobId($userId,$jobId) {
		$userId = intval($userId);
		$jobId = intval($jobId);
		if($userId<1 || $jobId<1){
			return null;
		}
		return $this->_db->get_one ( "SELECT * FROM " . $this->_tableName . " WHERE jobid=" . $this->_addSlashes ( $jobId ) . " AND userid =" . $this->_addSlashes ( $userId ) . " ORDER BY last DESC LIMIT 1" );
	}

	function getAll() {
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function gets($offset,$limit) {
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName. "  LIMIT " . $offset . "," . $limit );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function countByJobId($jobId){
		$result = $this->_db->get_one ( "SELECT COUNT(*) AS total FROM " . $this->_tableName . " WHERE jobid=".$this->_addSlashes ( $jobId )."  LIMIT 1" );
		return $result ['total'];
	}
	
	function getAppliedJobs($userid){
		$userid = intval($userid);
		if($userid<1){
			return null;
		}
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName. " WHERE userid=".$this->_addSlashes ( $userid )." AND status<=2" );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function countAppliedJobs($userid){
		$userid = intval($userid);
		if($userid<1){
			return null;
		}
		return $this->_db->get_value ( "SELECT COUNT(*) as total FROM " . $this->_tableName. " WHERE userid=".$this->_addSlashes ( $userid )." AND status<2 LIMIT 1" );
	}
	
	function getFinishJobs($userid){
		$userid = intval($userid);
		if($userid<1){
			return null;
		}
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName. " WHERE userid=".$this->_addSlashes ( $userid )." AND total>0" );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getQuitJobs($userid){
		$userid = intval($userid);
		if($userid<1){
			return null;
		}
		$query = $this->_db->query ( "SELECT * FROM " . $this->_tableName. " WHERE userid=".$this->_addSlashes ( $userid )." AND status>=4" );
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function updateByJobId($fieldData, $jobid,$userid) {
		$userid = intval($userid);
		$jobid = intval($jobid);
		if($userid<1 || $jobid<1 ){
			return null;
		}
		$this->_db->update ( "UPDATE " . $this->_tableName . " SET " . $this->_getUpdateSqlString ( $fieldData ) . "  WHERE jobid=" . $this->_addSlashes ( $jobid ) . " AND userid=" . $this->_addSlashes ( $userid ) . " LIMIT 1" );
		return $this->_db->affected_rows ();
	}

	/*查找正在进行中的任务*/
	function getsByJobIds($userid,$ids) {
		if(!is_array($ids)){
			return array();
		}
		$ids = implode(",",$ids);
		return $this->_db->get_one ( "SELECT * FROM " . $this->_tableName. "  WHERE jobid in(" .$ids. ") AND status <= 1 AND userid=".$this->_addSlashes ( $userid )." ORDER BY last DESC LIMIT 1" );
	}
	
	/*查找所有正在进行中的任务*/
	function getInProcessJobersByUserIdAndJobIds($userid, $ids) {
		$userid = (int) $userid;
		if(!S::isArray($ids) || $userid < 1) return array();
		$query = $this->_db->query('SELECT * FROM ' . $this->_tableName . ' WHERE jobid IN(' . S::sqlImplode($ids) . ') AND status = 1 AND userid = ' . S::sqlEscape($userid) . ' ORDER BY last DESC');
		return $this->_getAllResultFromQuery($query);
	}

	function getJobersByJobIds($userid,$ids) {
		$userid = intval($userid);
		if(!is_array($ids) || $userid<1 ){
			return array();
		}
		$ids = implode(",",$ids);
		$query =  $this->_db->query ( "SELECT * FROM " . $this->_tableName. "  WHERE jobid in(" .$ids. ") AND userid=".$this->_addSlashes ( $userid ));
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function getJobersByJobIdAndUserId($userid,$jobid) {
		$userid = intval($userid);
		$jobid = intval($jobid);
		if($userid<1 || $jobid<1 ){
			return null;
		}
		$query =  $this->_db->query ( "SELECT * FROM " . $this->_tableName. "  WHERE jobid=" .$this->_addSlashes($jobid)." AND userid != ".$this->_addSlashes($userid)." AND total>0 LIMIT 20");
		return $this->_getAllResultFromQuery ( $query );
	}
	
	function countJobersByJobIdAndUserId($userid,$jobid) {
		$userid = intval($userid);
		$jobid = intval($jobid);
		if($userid<1 || $jobid<1 ){
			return null;
		}
		$result = $this->_db->get_one ( "SELECT COUNT(*) as total FROM " . $this->_tableName. "  WHERE jobid=" .$this->_addSlashes($jobid)." AND userid != ".$this->_addSlashes($userid)." AND total>0");
		return $result['total'];
	}
}





















?>