<?php 
!defined('P_W') && exit('Forbidden');

/**
 * 学校设置DAO
 * @package PW_SchoolDB
 */
class PW_SchoolDB extends BaseDB{
	
	var $_tableName = 'pw_school';
	var $_primaryKey = 'schoolid';
	
	/**
	 * 添加一所学校
	 * @param string $schoolName 学校名称
	 * @param int $areaId 地区Id
	 * @param int $type 学校类型：1为小学，2为中学，3为大学
	 * @return int $Id 学校
	 */
	function addSchool($areaId,$schoolName,$type){
		$areaId = intval($areaId);
		$schoolName = trim($schoolName);
		$type = intval($type);
		if (!$schoolName || $areaId < 1 || $type < 0) return false;
		$tmpSchoolId = $this->_db->get_value("SELECT `schoolid` FROM $this->_tableName WHERE areaid =". S::sqlEscape($areaId). "AND schoolname= ". S::sqlEscape($schoolName)."");
		if(count($tmpSchoolId) > 0) return $tmpSchoolId;
		$fieldData = array($areaId,$schoolName,$type);
		return $this->_insert($fieldData);
	}
	
	/**
	 * 批量添加多所学校
	 * @param string $schoolNames 学校名称
	 * @return int $Id 学校
	 */
	function addSchools($fieldData){
		if (!S::isArray($fieldData)) return false;
		return $this->_db->update("INSERT INTO " . $this->_tableName . " (schoolname,areaid,type) VALUES " . S::sqlMulti($fieldData));
	}
	
	/**
	 * 批量检验学校名是否重复
	 * @param int $areaIds 地区id
	 * @param string $schoolNames 学校名称
	 * @param array schoolids
	 */
	function checkSchoolNames($areaid,$type,$schoolNames){
		if (!$areaid || !$schoolNames || !$type) return false;
		return $this->_db->get_value("SELECT count(*) FROM $this->_tableName WHERE areaid = " . S::sqlEscape($areaid) . " AND type = " . S::sqlEscape($type) . " AND schoolname IN (" . S::sqlImplode($schoolNames) . ")");
	}
	
	/**
	 * 根据一个学校ID获取数据
	 * @param int schoolId
	 * @return array
	 */
	function getBySchoolId($schoolId){
		$schoolId = intval($schoolId);
		if($schoolId < 1) return false;
		return $this->_get($schoolId);
	}
	
	/**
	 * 根据多个学院ID获取数据
	 * @param array schoolIds
	 * @return array
	 */
	function getSchoolsBySchoolIds($schoolIds){
		if(!S::isArray($schoolIds)) return array();
		$query = $this->_db->query("SELECT * FROM  $this->_tableName WHERE schoolid IN (" . S::sqlImplode($schoolIds) . ")");
		return $this->_getAllResultFromQuery($query,'schoolid');
	}
	
	/**
	 * 根据地区ID获取学校
	 * @param int areaId 地区ID
	 * @param int type 学校类型
	 * @return array 学校数据
	 */
	function getSchoolByArea($areaId,$type){
		if(!$areaId || !$type) return false;
		$result = array();
		$query = $this->_db->query("SELECT * FROM  $this->_tableName WHERE areaid = " . S::sqlEscape($areaId) . " And type = " . S::sqlEscape($type)."");
		while ($rt = $this->_db->fetch_array($query)) {
			$rt['schoolname'] = str_replace('&nbsp;', ' ', $rt['schoolname']);
			$result[$rt[schoolid]] = $rt;
		}
		return $result;
		//return $this->_getAllResultFromQuery($query,'schoolid');
	}
	
	/**
	 * 编辑一条学校数据
	 * @param int id
	 * @param string name
	 * @return bool
	 */
	function editSchool($schoolId,$schoolName){
		if (!$schoolId || !$schoolName) return false;
		return pwQuery::update($this->_tableName, "schoolid=:schoolid", array($schoolId), array('schoolname'=>$schoolName));
	}
	
	/**
	 * 根据学校ID删除数据
	 * @param int schoolId 学校id
	 * @return bool
	 */
	function deleteSchool($schoolId){
		if(!$schoolId) return false;
		return pwQuery::delete($this->_tableName, 'schoolid=:schoolid', array($schoolId));
	}
	
	/**
	 * 根据学校ID删除多条数据
	 * @param int schoolIds 学校id
	 * @return bool
	 */	
	function deleteSchools($schoolIds){
		if(!S::isArray($schoolIds)) return false;
		return pwQuery::delete($this->_tableName, 'schoolid IN (:schoolid)', array($schoolIds));
	}
}
?>