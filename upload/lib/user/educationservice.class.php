<?php 
!defined('P_W') && exit('Forbidden');
/**
 * 教育经历service
 * @package PW_Education
 */
class PW_EducationService{

	var $educationMap = array();
	
	function PW_EducationService(){
		$this->_initEducationMap();
	}
	
	function _initEducationMap(){
		$this->educationMap = array(
			1 => '小学',
			2 => '初中',
			3 => '高中',
			4 => '大学专科',
			5 => '大学本科',
			6 => '硕士',
			7 => '博士',
			8 => '博士后',
		);
	}
	
	/**
	 * 添加一条教育经历
	 * @param int $uid 用户id
	 * @param int $schoolId 学校Id
	 * @param int $educationLevel教育程度
	 * @param int $startTime 入学年份
	 * @return int $educationId自增id
	 */
	function addEducation($uid,$schoolId,$educationLevel,$startTime){
		$uid = intval($uid);
		$schoolId = intval($schoolId);
		$eduicationLevel = intval($educationLevel);
		$startTime = intval($startTime);
		if ($uid < 1 || $schoolId < 1 || $educationLevel < 0) return false;
		$fieldData = array('uid'=>$uid, 'schoolid'=>$schoolId, 'educationlevel'=>$eduicationLevel, 'starttime'=>$startTime);
		$eduDb = $this->_getEducationDao();
		return $eduDb->addEducation($fieldData);
	}
	
	/**
	 * 批量添加教育经历
	 * @param array $data
	 * @return $educationIds教育经历Id
	 */
	function addEducations($data){
		if (!S::isArray($data)) return array();
		$fieldData = array();
		foreach ($data as $value){
			$value['uid'] 			= intval($value['uid']);
			$value['schoolid'] 		= intval($value['schoolid']);
			$value['educationlevel']= intval($value['educationlevel']);
			$value['starttime'] 	= intval($value['starttime']);
			if($value['uid'] < 1 || $value['schoolid'] < 1 || $value['educationlevel'] < 0) continue;
			$fieldData[] = $value;
		}
		$eduDb = $this->_getEducationDao();
		return $eduDb->addEducations($fieldData);
	}
	
	/**
	 * 根据用户ID获取教育经历
	 * @param int uid
	 * @return array
	 */
	function getEducationsByUid($uid){
		$uid = intval($uid);
		if($uid < 1) return array();
		$eduDb = $this->_getEducationDao();
		return $eduDb->getEducations($uid);
	}
	
	/**
	 * 根据学校ID获取用户名称
	 * @param int schoolId
	 * @return array
	 */
	function getUserNameBySchoolId($schoolId){
		$schoolId = (int) $schoolId;
		if ($schoolId < 1) return array();
		$eduDb = $this->_getEducationDao();
		$userIds = $eduDb->getUserId($schoolId);
		$userService = L::loadClass('UserService', 'user');
		return $userService->getByUserIds($userIds);
	}
	
	/**
	 * 根据教育经历ID获取一条教育经历
	 * @param int educationId
	 * @return array
	 */
	function getEducationById($educationId){
		$educationId = intval($educationId);
		if($educationId < 1) return array();
		$eduDb = $this->_getEducationDao();
		return $eduDb->getEducation($educationId);
	}
	
	/**
	 * 编辑一条教育经历
	 * @param int $uid 用户id
	 * @param int $schoolId 学校Id
	 * @param int $educationLevel 教育程度
	 * @param int $startTime 入学年份
	 * @return bool
	 */
	function editEducation($educationId,$educationLevel,$schoolId,$startTime){
		$educationId = intval($educationId);
		$educationLevel = intval($educationLevel);
		$schoolId = intval($schoolId);
		$starTime = intval($startTime);
		if($educationId < 1 || $educationLevel < 0 || $schoolId < 1) return false;
		$eduDb = $this->_getEducationDao();
		return $eduDb->editEducation($educationId,$educationLevel,$schoolId,$startTime);
	}
	
	/**
	 * 根据学校ID删除数据
	 * @param int schoolId 学校id
	 * @return bool
	 */
	function deleteEduBySchoolId($schoolId){
		$schoolId = intval(schoolId);
		$eduDb = $this->_getEducationDao();
		return $eduDb->deleteEduBySchoolId($schoolId);
	}
	
	/**
	 * 根据学校ID删除多条数据
	 * @param int schoolIds 学校id
	 * @return bool
	 */	
	function deleteEduBySchools($schoolIds){
		if(!S::isArray($schoolIds)) return false;
		$filteredSchoolIds = array();
		foreach ($schoolIds as $value) {
			$value = (int) $value;
			if ($value < 1) continue;
			$filteredSchoolIds[] = $value;
		}
		if (!S::isArray($filteredSchoolIds)) return false;
		$eduDb = $this->_getEducationDao();
		return $eduDb->deleteEduBySchoolIds($filteredSchoolIds);
	}
	
	
	/**
	 * 根据教育经历id删除一条教育经历
	 * @param int educationId 教育经历id
	 * @return bool
	 */
	function deleteEducationById($educationId){
		$educationId = intval($educationId);
		if($educationId < 1) return false;
		$eduDb = $this->_getEducationDao();
		return $eduDb->deleteEducation($educationId);
	}
	
	/**
	 * 删除多条教育经历
	 * @param array educationIds 教育经历id
	 * @return bool
	 */
	function deleteEducationByIds($educationIds){
		if (!S::isArray($educationIds)) return false;
		$eduDb = $this->_getEducationDao();
		$filteredEducationIds = array();
		foreach($educationIds as $value){		
			$value = intval($value);
			if($value < 1) continue;
			$filteredEducationIds[] = $value;
		}
		if (!S::isArray($filteredEducationIds)) return false;
		return $educations = $eduDb->deleteEducations($filteredEducationIds);
	}
	
	function _getEducationDao(){
		return L::loadDB('UserEducation', 'user'); 
	}
}