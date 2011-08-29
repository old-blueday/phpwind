<?php 
!defined('P_W') && exit('Forbidden');

/**
 * 工作经历DAO
 * @package PW_User_CareerDB
 */
class PW_UserCareerDB extends BaseDB{
	
	var $_tableName = 'pw_user_career';
	var $_companyTable = 'pw_company';
	var $_primaryKey = 'careerid';
	
	/**
	 * 添加工作经历
	 * @param array $data
	 * @return bool
	 */
	function addCareers($data){
		if (!S::isArray($data)) return false;
		return $this->_db->update("INSERT INTO $this->_tableName (uid,companyid,starttime) VALUES " . S::sqlMulti($data)."");
	}
	
	/**
	 * 根据用户ID获取工作经历
	 * @param int uid
	 * @return array
	 */
	function getCareers($uid){
		$uid = (int) $uid;
		if ($uid < 1) return array();
		$query = $this->_db->query("SELECT uc.*, c.companyname FROM  $this->_tableName uc LEFT JOIN $this->_companyTable c USING(companyid) WHERE uc.uid = " . S::sqlEscape($uid) . ' ORDER BY careerid ASC');
		return $this->_getAllResultFromQuery($query,'careerid');
	}
	
	/**
	 * 根据公司ID获取用户id
	 * @param int companyId
	 * @return array
	 */
	function getUserIdsByCompanyId($companyId){
		if (!$companyId) return array();
		$query = $this->_db->query("SELECT uid FROM  $this->_tableName WHERE companyid = " . S::sqlEscape($companyId) . "");
		return $this->_getAllResultFromQuery($query,'uid');
	}
	
	/**
	 * 根据工作经历ID获取一条工作经历
	 * @param int careerId
	 * @return array
	 */
	function getCareer($careerId){
		if (!$careerId) return array();
		return $this->_get($careerId);
	}
	
	/**
	 * 编辑一条工作经历
	 * @param int $uid 用户id
	 * @param int $companyId 公司Id
	 * @param int $startTime 入公司年份
	 * @return bool
	 */
	function editCareer($careerId,$companyId,$startTime){
		if (!$careerId || !$companyId || !$startTime) return false;
		return pwQuery::update($this->_tableName, "careerid=:careerid", array($careerId), array('companyid'=>$companyId,'starttime'=>$startTime));
	}
	
	/**
	 * 根据工作经历id批量删除工作经历
	 * @param int careerIds 工作经历id
	 * @return bool
	 */
	function deleteCareers($careerIds){
		if(!$careerIds) return false;
		return pwQuery::delete($this->_tableName, 'IN(:careerid)', array($careerIds));
	}
	
	/**
	 * 根据工作经历id删除一条工作经历
	 * @param int careerId 工作经历id
	 * @return bool
	 */
	function deleteCareer($careerId){
		if(!$careerId) return false;
		return pwQuery::delete($this->_tableName, 'careerid=:careerid', array($careerId));
	}
}