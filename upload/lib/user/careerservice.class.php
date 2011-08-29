<?php 
!defined('P_W') && exit('Forbidden');
/**
 * 工作经历和公司service
 * @package PW_Career
 */
class PW_CareerService{

//公司start

	/**
	 * 添加一个公司
	 * @param string $companyName 公司名称
	 * @return int
	 */
	function addCompany($companyName){
		$companyName = trim($companyName);
		if (!$companyName) return false;
		$companyDb = $this->_getcompanyDao();
		return $companyDb->addCompany($companyName);
		
		
	}
	
	/**
	 * 根据公司ID获取公司名称
	 * @param int $companyId
	 * @return string companyname
	 */
	function getByCompanyId($companyId){
		$companyId = intval($companyId);
		if ($companyId < 1) return array();
		$companyDb = $this->_getcompanyDao();
		return $companyDb->getCompanyNameById($companyId);
		
	}
	
	/**
	 * 根据公司名称获取公司Id
	 * @param string $companyName
	 * @return int companyid
	 */
	function getByCompanyName($companyName){
		$companyName = trim($companyName);
		if (!$companyName) return false;
		$companyDb = $this->_getcompanyDao();
		return $companyDb->getCompanyIdByName($companyName);
		
	}
	
	/**
	 * 编辑公司信息
	 * @param int $companyId 公司Id
	 * @param string $companyName 公司名称
	 * @return bool
	 */
	function editCompany($uid,$companyId,$startTime){
		$companyName = trim($companyName);
		$companyId = intval($companyId);
		if ($companyId < 1 || !$companyName) return false;
		$companyDb = $this->_getcompanyDao();
		return $companyDb->editCompany($uid,$companyId,$startTime);
	}
	
	
//工作经历start
	
	/**
	 * 添加一条工作经历
	 * @param int $uid 用户id
	 * @param string $companyName 公司名称
	 * @param int $startTime 入公司年份
	 * @return int $careerId自增id
	 */
	function addCareer($uid,$companyName,$startTime){
		$uid = intval($uid);
		$startTime = intval($startTime);
		$companyName = trim($companyName);
		if (!$companyName || $uid < 1) return false;
		$careerDb = $this->_getCareerDao();
		$companyId = $this->getbyCompanyName($companyName);
		if(!$companyId) return false;
		$fieldData = array();
		$fieldData['uid'] = $uid;
		$fieldData['companyid'] = $companyId;
		$fieldData['starttime'] = $startTime;
		return $careerId = $careerDb->addCareer($fieldData);
	}
	
	/**
	 * 批量添加工作经历
	 * @param array $data
	 * @return $careerIds 工作经历id
	 */
	function addCareers($data){
		if (!S::isArray($data)) return false;
		$companyNames = array();
		foreach ($data as $key=>$value){
			$data[$key]['uid']		   = intval($value['uid']);
			$data[$key]['companyname'] = trim($value['companyname']);
			$data[$key]['starttime']   = intval($value['starttime']);
			$companyNames[] 		   = trim($value['companyname']);
			if($value['uid'] < 1 || !$value['companyname']) unset($data[$key]);
		}
		$companyDb = $this->_getCompanyDao();
		$careerDb = $this->_getCareerDao();
		$companyData = (array)$companyDb->getCompanyByNames($companyNames);
		foreach ($companyData as $k=>$v) {
			$companyData[$k] = $v['companyname'];
		}
		$companyInfo = array_flip($companyData);
		$fields = array();
		foreach ($data as $key=>$value){
			if (!in_array($value['companyname'],$companyData)){
				$companyid = $companyDb->addCompany($data[$key]['companyname']);
			}else{
				$companyid = $companyInfo[$value['companyname']];
			}
			$fields[] = array($value['uid'],$companyid,$value['starttime']);
		}
		return $careerDb->addCareers($fields);
		
	}
	
	/**
	 * 根据用户ID获取工作经历
	 * @param int uid
	 * @return array
	 */
	function getCareersByUid($uid) {
		$uid = (int) $uid;
		if ($uid < 1) return array();
		$careerDb = $this->_getCareerDao();
		return $careerDb->getCareers($uid);
	}
	
	/**
	 * 根据公司名称获取用户名称
	 * @param int companyId
	 * @return array
	 */
	function getUserCareerName($companyName){
		$companyName = trim($companyName);
		if (!$companyName) return array();
		$companyId = $this->getByCompanyName($companyName);
		$careerDb = $this->_getCareerDao();
		$userIds = $careerDb->getUserIdsByCompanyId($companyId);
		if(!$userIds) return array();
		$userService = L::loadClass('UserService', 'user');
		return $userService->getByUserIds($userIds);
		
	}
	
	/**
	 * 根据工作经历ID获取一条工作经历
	 * @param int careerId
	 * @return array
	 */
	function getCareerById($careerId){
		$careerId = intval($careerId);
		if($careerId < 1) return array();
		$careerDb = $this->_getCareerDao();
		return $careerDb->getCareer($careerId);
	}
	
	/**
	 * 编辑一条工作经历
	 * @param int $uid 用户id
	 * @param int $companyName 公司名称
	 * @param int $startTime 入公司年份
	 * @return bool
	 */
	function editCareer($careerId,$companyName,$startTime){
		$companyName = trim($companyName);
		$careerId = intval($careerId);
		$starttime = intval($startTime);
		if (!$companyName || $careerId < 1) return false;
		$companyId = $this->getByCompanyName($companyName);
		!$companyId && $companyId = $this->addCompany($companyName);
		if (!$companyId) return false;
		$careerDb = $this->_getCareerDao();
		return $careerDb->editCareer($careerId,$companyId,$startTime);
	}
	
	/**
	 * 根据工作经历id删除一条工作经历
	 * @param int careerId 工作经历id
	 * @return bool
	 */
	function deleteCareerById($careerId){
		$careerId = intval($careerId);
		if($careerId < 1) return false;
		$careerDb = $this->_getCareerDao();
		return $careerDb->deleteCareer($careerId);
	}
	
	/**
	 * 删除多条工作经历
	 * @param array careerIds 教育经历id
	 * @return bool
	 */
	function deleteCareerByIds($careerIds){
		if (!S::isArray($careerIds)) return false;
		$careerDb = $this->_getCareerDao();
		foreach($careerIds as $value){
			$value['careerid'] = intval($value['careerid']);
			if($value['careerid'] < 1) continue;
		}
		return $careerDb->deleteCareers($value['careerid']);
	}

	function _getCompanyDao(){
		return L::loadDB('company', 'user'); 
	}
	
	function _getCareerDao(){
		return L::loadDB('UserCareer', 'user'); 
	}
}