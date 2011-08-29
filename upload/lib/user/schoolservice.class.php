<?php 
!defined('P_W') && exit('Forbidden');
define('PW_UNIVERSITY', 3); //学校类型为大学
/**
 * 学校设置service
 * @package PW_School
 */
class PW_SchoolService{
	
	/**
	 * 添加一所学校返回学校id
	 * @param string $schoolName 学校名称
	 * @param int $areaId 地区Id
	 * @param int $type 学校类型：1为小学，2为中学，3为大学
	 * @return int
	 */
	function addSchool($areaId,$type,$schoolName){
		$schoolName = trim($schoolName);
		$schoolName = trim(substrs($schoolName, 32, 'N'), ' &nbsp;');
		$areaId = intval($areaId);
		$type = $type ? intval($type) : 1;
		if (!$schoolName || !$areaId < 0 || $type < 1) return false;
		$schoolDb = $this->_getSchoolDao();
		return $schoolDb->addSchool($areaId,$type,$schoolName);
	}
	
	/**
	 *加入多条学校数据
	 * @param array $data数据
	 * @return array $schoolIds学校id
	 */
	function addSchools($data){
		if (!S::isArray($data)) return false;
		$fieldData = array();
		$schoolNames = array();
		$schoolDb = $this->_getSchoolDao();
		foreach ($data as $value){
			$value['areaid']  = intval($value['areaid']);
			$value['schoolname']= trim($value['schoolname']);
			$value['schoolname'] = trim(substrs($value['schoolname'], 32, 'N'), ' &nbsp;');
			$schoolNames[] = trim($value['schoolname']);
			$value['type'] = $value['type'] ? intval($value['type']) : 1;
			if(!$value['schoolname'] || $value['areaid'] < 0 || $value['type'] < 0) continue;
			$fieldData[] = $value;
		}
		$schoolIds = $schoolDb->checkSchoolNames((int)$value['areaid'],$value['type'],$schoolNames); 
		if($schoolIds > 0) return $schoolIds;
		return $schoolDb->addSchools($fieldData); 
	}
	
	/**
	 * 根据学校类型和地区ID获取学校
	 * @param int areaId 地区ID
	 * @param int type 学校类型
	 * @return array 学校数据
	 */
	function getByAreaAndType($areaId,$type){
		$areaId = intval($areaId);
		$type = intval($type);
		if ($areaId < 0 || $type < 1) return false;
		$schoolDb = $this->_getSchoolDao();
		return $schoolDb->getSchoolByArea($areaId,$type);		
	}
	
	/**
	 * 根据一个学校ID获取数据
	 * @param int schoolId
	 * @return array
	 */
	function getBySchoolId($schoolId){
		$schoolId = intval($schoolId);
		if (!$schoolId < 1) return false;
		$schoolDb = $this->_getSchoolDao();
		return $schoolDb->getBySchoolId($schoolId);
	}
	
	/**
	 * 根据多个学校ID获取数据
	 * @param array schoolIds
	 * @return array
	 */
	function getBySchoolIds($schoolIds){
		if(!S::isArray($schoolIds)) return array();
		$schoolDb = $this->_getSchoolDao();
		return $schoolDb->getSchoolsBySchoolIds($schoolIds);
	}
	
	/**
	 * 根据学校ID和名称编辑一条学校数据 
	 * @param int schoolId
	 * @param string newSchoolName
	 * @return bool
	 */
	function editSchool($schoolId,$newSchoolName){
		$schoolId = intval($schoolId);
		$newSchoolName = trim($newSchoolName);
		if (!newSchoolName || $schoolId < 1) return false;
		$schoolDb = $this->_getSchoolDao();
		return $schoolDb->editSchool($schoolId,$newSchoolName);
	}
	
	/**
	 * 根据学校ID删除数据
	 * @param int $schoolId 学校id
	 * @param int $type 学校类型
	 * @return bool
	 */
	function deleteSchool($schoolId,$type){
		$schoolId = intval($schoolId);
		$type = intval($type);
		if($schoolId < 1 || $type < 0) return false;
	  /*if($type = PW_UNIVERSITY){
			$collegeDb = $this->_getCollegeService();
			$deleteCollege = $collegeDb->deleteBySchoolId($schoolId);
		}*/
		$schoolDb = $this->_getSchoolDao();
		$educationDb = $this->_getEducationService();
		$deleteEducation = $educationDb->deleteEduBySchoolId($schoolId);
		return $deleteSchool = $schoolDb->deleteSchool($schoolId);
	}

	/**
	 * 根据多个学校ID删除数据
	 * @param int schoolIds 学校id
	 * @return bool
	 */
	function deleteSchools($schoolIds){
		if(!S::isArray($schoolIds)) return false;
		foreach($schoolIds as $value){
			$value['schoolid'] = intval($value['schoolid']);
			if($value['schoolid'] < 1) continue;
		}
		/*$collegeDb = $this->_getCollegeService();
		$deleteCollege = $collegeDb->deleteBySchoolIds($schoolIds);*/
		$schoolDb = $this->_getSchoolDao();
		$educationDb = $this->_getEducationService();
		$deleteEducation = $educationDb->deleteEduBySchoolIds($schoolIds);
		return $deleteSchool = $schoolDb->deleteSchools($schoolIds);
	}
	
	/**
	 * 组装单个下拉框
	 * 
	 * @param int $parentid 上一级areaid
	 * @param int $defaultValue 默认选中值的id 
	 * @return string
	 */
	function getSchoolsSelectHtml($parentid, $type, $defaultValue = null) {
		$parentid = intval($parentid);
		$type = intval($type);
		if ($parentid < 0 || $type < 0) return null;
		$schools = $this->getByAreaAndType($parentid,$type);
		$schoolsSelect = '';
		foreach ((array)$schools as $value) {
			$selected = ($defaultValue && $value['schoolid'] == $defaultValue) ? 'selected' : '';
			$schoolsSelect .= "<option value=\"$value[schoolid]\" $selected>{$value[schoolname]}</option>\r\n";
		}
		return $schoolsSelect;
	}
	
	function _getSchoolDao(){
		return L::loadDB('School', 'user'); 
	}
	
	/*function _getCollegeService(){
		return L::loadClass('CollegeService', 'user'); 
	}*/
	
	function _getEducationService(){
		return L::loadClass('EducationService', 'user'); 
	}
}
?>