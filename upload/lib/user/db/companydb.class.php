<?php 
!defined('P_W') && exit('Forbidden');

/**
 * 公司DAO
 * @package PW_SchoolDB
 */
class PW_CompanyDB extends BaseDB{
	
	var $_tableName = 'pw_company';
	var $_primaryKey = 'companyid';

	/**
	 * 添加一个公司
	 * @param string $companyName 公司名称
	 * @return int $companyId
	 */
	function addCompany($companyName){
		if (!$companyName) return false;
		$companyName = trim($companyName);
		$tmpCompanyId = $this->_db->get_value("SELECT `companyid` FROM $this->_tableName WHERE companyname =" . S::sqlEscape($companyName)."");
		if($tmpCompanyId > 0) return $tmpCompanyId;
		return $this->_insert(array('companyname' => $companyName));
	}
	
	/**
	 * 根据公司ID获取公司名称
	 * @param int $companyId
	 * @return array
	 */
	function getCompanyNameById($companyId){
		$companyId = intval($companyId);
		if($companyId < 1) continue;
		$companyData = $this->_get($companyId);
		return $companyData['companyname'];
	}
	
	/**
	 * 根据公司ID批量获取公司名称
	 * @param int $companyIds
	 * @return array
	 */
	function getCompanyNameByIds($companyIds){
		if(!S::isArray($companyIds)) return false;
		$query = $this->_db->query("SELECT companyname FROM  $this->_tableName WHERE companyid in (" . S::sqlImplode($companyIds) . ")");
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 根据公司名称获取公司数据
	 * @param int $companyNames
	 * @return array
	 */
	function getCompanyByNames($companyNames){
		if(!S::isArray($companyNames)) return false;
		$query = $this->_db->query("SELECT * FROM  $this->_tableName WHERE companyname in (" . S::sqlImplode($companyNames) . ")");
		return $this->_getAllResultFromQuery($query,'companyid');
	}
	
	/**
	 * 根据公司名称获取公司ID
	 * @param string $companyName
	 * @return array
	 */
	function getCompanyIdByName($companyName){
		if (!$companyName) return array();
		return $this->_db->get_value("SELECT companyid FROM  $this->_tableName WHERE companyname = " . S::sqlEscape($companyName) . "");
	}
	
	/**
	 * 根据公司名称批量获取公司ID
	 * @param string $companyNames
	 * @return array
	 */
	function getCompanyIdsByName($companyNames){
		if (!S::isArray($companyNames)) return array();
		$query = $this->_db->query("SELECT companyid FROM  $this->_tableName WHERE companyname in (" . S::sqlImplode($companyNames) . ")");
		return $this->_getAllResultFromQuery($query);
	}
	
	/**
	 * 编辑公司名称
	 * @param int $companyId 公司Id
	 * @param string $companyName 公司名称
	 * @return bool
	 */
	function editCompany($companyId,$companyName){
		if (!$companyId || !$companyName) return false;
		return pwQuery::update($this->_tableName, "companyid=:companyid", array($companyId), array('companyname'=>$companyName));
	}
	
	/**
	 * 根据公司id删除记录
	 * @param int $companyId 工作公司id
	 * @return bool
	 */
	function deleteCompany($companyId){
		if(!$companyId) return false;
		return pwQuery::delete($this->_tableName, 'companyid=:companyid', array($companyId));
	}
}