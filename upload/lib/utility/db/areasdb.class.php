<?php
!defined('P_W') && exit('Forbidden');

/**
 * 地区数据层
 * @package  PW_AreasDB
 * @author phpwind @2010-1-18
 */
class PW_AreasDB extends BaseDB {
	var $_tableName 	= 	'pw_areas';
	var $_primaryKey 	= 	'areaid';

	/**
	 * 添加
	 * 
	 * @param array $fieldsData 数据数组，以数据库字段为key
	 * @return boolean
	 */
	function insert($fieldsData) {
		if(!S::isArray($fieldsData)) return false;
		return $this->_insert($fieldsData);
	}

	/**
	 * 批量添加
	 * 
	 * @param array $fieldsData
	 * @return boolean
	 */
	function addAreas($fieldsData) {
		if(!S::isArray($fieldsData)) return false;
		$this->_db->update("INSERT INTO " . $this->_tableName . " (name,joinname,parentid,vieworder) VALUES  " . S::sqlMulti($fieldsData));
		return true;
	}
	
	/**
	 * 更新
	 * 
	 * @param int $areaid  地区ID
	 * @param array $fieldsData 数据数组，以数据库字段为key
	 * @return boolean
	 */
	function update($fieldsData,$areaid) {
		$areaid = intval($areaid);
		if($areaid < 1 || !S::isArray($fieldsData)) return false;
		return (bool)$this->_update($fieldsData,$areaid);
	}

	/**
	 * 单个删除
	 * 
	 * @param int $areaid  地区ID
	 * @return boolean 
	 */
	function delete($areaid) {
		$areaid = intval($areaid);
		if ($areaid < 1) return false;
		return (bool)$this->_delete($areaid);
	}

	/**
	 * 批量删除
	 * 
	 * @param array $areaids  地区IDs
	 * @return boolean
	 */
	function deleteByAreaIds($areaids) {
		if(!S::isArray($areaids)) return false;
		return (bool)pwQuery::delete($this->_tableName, "$this->_primaryKey in(:$this->_primaryKey)", array($areaids));
	}
	
	/**
	 * 根据地区ID获取信息
	 * 
	 * @param int $areaid  地区ID
	 * @return array
	 */
	function getAreaByAreaId($areaid) {
		$areaid = intval($areaid);
		if ($areaid < 1) return array();
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE  " . $this->_primaryKey . " = " . $this->_addSlashes($areaid));
	}
	
	/**
	 * 根据多个地区id获取全称
	 * @param array $areaids
	 * @return array
	 */
	function getFullAreaByAreaIds($areaids) {
		$result = array();
		$query = $this->_db->query("SELECT areaid,joinname FROM " . $this->_tableName . " WHERE " . $this->_primaryKey . " IN (" . $this->_getImplodeString($areaids) . ")");
		while ($rt = $this->_db->fetch_array($query)) {
				$result[$rt['areaid']] = $rt['joinname'];
		}
		return $result;
	}
	
	/**
	 * 根据多个地区id获取信息
	 * @param array $areaids
	 * @return array
	 */
	function getAreasByAreadIds($areaids) {
		if (!S::isArray($areaids)) return array();
		$query = $this->_db->query("SELECT * FROM " . $this->_tableName . " WHERE " . $this->_primaryKey . " IN (" . $this->_getImplodeString($areaids) . ")");
		return $this->_getAllResultFromQuery($query, $this->_primaryKey);
	}
	/**
	 * 根据地区名获取信息
	 * 
	 * @param string $areaName 地区名
	 * @return array
	 */
	function getAreaByAreaName($areaName) {
		$areaName = trim($areaName);
		if (!$areaName) return array();
		return $this->_db->get_one("SELECT * FROM " . $this->_tableName . " WHERE name = " . $this->_addSlashes($areaName));
	}
	
	/**
	 * 根据parent获取地区
	 * 
	 * @param int $parent 上一级areaid
	 * @return array
	 */
	function getAreaByAreaParent($parentid) {
		$parentid = intval($parentid);
		if ($parentid < 0) return array();
		$query = $this->_db->query("SELECT * FROM  " . $this->_tableName . " WHERE parentid = " . $this->_addSlashes($parentid) . " ORDER BY vieworder ASC");
		return $this->_getAllResultFromQuery($query);
	}

	/**
	 * 获取数据库中所有地区
	 * @return array
	 */
	function getAllAreas() {
		$query = $this->_db->query('SELECT * FROM ' . $this->_tableName . ' ORDER BY vieworder ASC');
		return $this->_getAllResultFromQuery($query);
	}
}