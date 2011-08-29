<?php 
!defined('P_W') && exit('Forbidden');

/**
 * 学习经历DAO
 * @package PW_UserEducationDB
 */
class PW_UserEducationDB extends BaseDB{
	
	var $_tableName = 'pw_user_education';
	var $_schoolTable = 'pw_school';
	var $_primaryKey = 'educationid';
	
	/**
	 * 添加教育经历
	 * @param array $data
	 * @return bool
	 */
	function addEducation($data){
		if (!S::isArray($data)) return false;
		return $this->_insert($data);
	}
	
	/**
	 * 批量添加教育经历
	 * @param array $data
	 * @return bool
	 */
	function addEducations($data){
		if (!S::isArray($data)) return false;
		return $this->_db->update("INSERT INTO " . $this->_tableName . "(uid,schoolid,educationlevel,starttime) VALUES " . S::sqlMulti($data));
	}
	
	/**
	 * 根据用户ID获取教育经历
	 * @param int uid
	 * @return array
	 */
	function getEducations($uid){
		if(!$uid) return array();
		$query = $this->_db->query("SELECT ue.*, s.schoolname FROM  $this->_tableName ue LEFT JOIN $this->_schoolTable s USING(schoolid) WHERE ue.uid = " . S::sqlEscape($uid) . " ORDER BY educationid ASC");
		return $this->_getAllResultFromQuery($query,'educationid');
	}
	
	/**
	 * 根据学校或学院ID获取用户ID
	 * @param int schoolId
	 * @return array
	 */
	function getUserId($schoolId){
		if(!$schoolId) return array();
		$query = $this->_db->query("SELECT uid FROM  $this->_tableName WHERE schoolid = " . S::sqlEscape($schoolId) . "");
		return $this->_getAllResultFromQuery($query,'uid');
	}
	
	/**
	 * 根据教育经历ID获取一条教育经历
	 * @param int educationId
	 * @return array
	 */
	function getEducation($educationId){
		if(!$educationId) return array();
		return $this->_get($educationId);
	}
	
	/**
	 * 编辑一条教育经历
	 * @param int $uid 用户id
	 * @param int $schoolId 学校Id
	 * @param int $startTime 入学年份
	 * @return bool
	 */
	function editEducation($educationId,$educationLevel,$schoolId,$startTime){
		if (!$educationId || !$educationLevel || !$schoolId || !$startTime) return false;
		return pwQuery::update($this->_tableName, "educationid=:educationid", array($educationId), array('schoolid'=>$schoolId,'educationlevel'=>$educationLevel,'starttime'=>$startTime));
	}
	
	/**
	 * 根据学校ID删除数据
	 * @param int schoolId 学校id
	 * @return bool
	 */
	function deleteEduBySchoolId($schoolId){
		if(!$schoolId) return false;
		return pwQuery::delete($this->_tableName, 'schoolid=:schoolid', array($schoolId));
	}
	
	/**
	 * 根据学校ID删除多条数据
	 * @param int schoolIds 学校id
	 * @return bool
	 */	
	function deleteEduBySchoolIds($schoolIds){
		if(!S::isArray($schoolIds)) return false;
		return pwQuery::delete($this->_tableName, 'schoolid IN (:schoolid)', array($schoolIds));
	}
	
	/**
	 * 根据教育经历id删除一条教育经历
	 * @param int educationId 教育经历id
	 * @return bool
	 */
	function deleteEducation($educationId){
		if(!$educationId) return false;
		return pwQuery::delete($this->_tableName, 'educationid=:educationid', array($educationId));
	}
	
	/**
	 * 根据教育经历id删除多条教育经历
	 * @param int educationIds 教育经历id
	 * @return bool
	 */
	function deleteEducations($educationIds){
		if(!S::isArray($educationIds)) return false;
		return pwQuery::delete($this->_tableName, 'educationid IN (:educationid)', array($educationIds));
	}
}