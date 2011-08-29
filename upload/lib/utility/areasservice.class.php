<?php
!defined('P_W') && exit('Forbidden');

/**
 * 地区服务层
 * @package  PW_AreasService
 * @author phpwind @2010-1-18
 */
class PW_AreasService {

	/**
	 * 添加
	 * 
	 * @param array $fieldsData 数据数组，以数据库字段为key
	 * @return int 
	 */
	function addArea($fieldsData) {
		$fieldsData = $this->checkFieldsData($fieldsData);
		if (!S::isArray($fieldsData)) return false;
		$areaDb = $this->_getAreasDB();
		$result = $areaDb->insert($fieldsData);
		$this->setAreaCache();
		return $result;
	}

	/**
	 * 批量添加
	 * 
	 * @param array $fieldsData 二维数据数组
	 * @return int 
	 */
	function addAreas($fieldsData) {
		foreach ($fieldsData as $v) {
			$tmpData = $this->buildAddData($v);
			$fieldsDatas[] = $this->checkFieldsData($tmpData);
		}
		if (!S::isArray($fieldsDatas)) return false;
		$areaDb = $this->_getAreasDB();
		$result = $areaDb->addAreas($fieldsDatas);
		$this->setAreaCache();
		return $result;
	}
	
	/**
	 * 更新
	 * 
	 * @param array $fieldsData 数据数组，以数据库字段为key
	 * @param int $areaid  地区ID
	 * @return boolean 
	 */
	function updateArea($fieldsData,$areaid) {
		$areaid = intval($areaid);
		$fieldsData = $this->buildAddData($fieldsData);
		$fieldsData = $this->checkFieldsData($fieldsData);
		if ($areaid < 1 || !S::isArray($fieldsData)) return false;
		$areaDb = $this->_getAreasDB();
		$result = $areaDb->update($fieldsData,$areaid);
		$this->setAreaCache();
		return $result; 
	}
	
	/**
	 * 单个删除
	 * 
	 * @param int $areaid  地区ID
	 * @return boolean
	 */
	function deleteAreaByAreaId($areaid) {
		$areaid = intval($areaid);
		if ($areaid < 1) return false;
		$areaDb = $this->_getAreasDB();
		$result = $areaDb->delete($areaid);
		$this->setAreaCache();
		return $result; 
	}
	
	/**
	 * 批量删除
	 * 
	 * @param array $areaids  地区IDs
	 * @return boolean
	 */
	function deleteAreaByAreaIds($areaids) {
		if(!S::isArray($areaids)) return false;
		$areaDb = $this->_getAreasDB();
		$result = $areaDb->deleteByAreaIds($areaids);
		$this->setAreaCache();
		return $result; 
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
		$areaDb = $this->_getAreasDB();
		return $areaDb->getAreaByAreaId($areaid);
	}
	
	function getFullAreaByAreaIds($areaids){
		if (!S::isArray($areaids)) return array();
		$areaDb = $this->_getAreasDB();
		return $areaDb->getFullAreaByAreaIds($areaids);
	}
	
	/**
	 * 根据多个地区id获取信息
	 * @param array $areaids
	 * @return array
	 */
	function getAreasByAreadIds($areaids) {
		if (!S::isArray($areaids)) return array();
		$areaDb = $this->_getAreasDB();
		return $areaDb->getAreasByAreadIds($areaids);
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
		$areaDb = $this->_getAreasDB();
		return $areaDb->getAreaByAreaName($areaName);
	}
	
	/**
	 * 根据level获取地区,暂时没用
	 * 
	 * @param int $level  1国家2省份3市4县区
	 * @return array
	 */
	function getAreaByAreaLevel($level) {
		$level = intval($level);
		if ($level < 1) return array();
		$areaDb = $this->_getAreasDB();
		return $areaDb->getAreaByAreaLevel($level);
	}
	
	/**
	 * 获取多个地区的上级或者上两级ids
	 * @param array $areaids
	 * @return array
	 */
	function getParentidByAreaids($areaids) {
		if (!S::isArray($areaids)) return array();
		$tempResult = $this->getAreasByAreadIds($areaids);
		$upids = $upperids = $tempids = array();
		foreach ($tempResult as $key => $value) {
			$upids[$key] = $tempids[] = $value['parentid'];
		}
		$tempids = array_filter(array_unique($tempids));
		if (!S::isArray($tempids)) return array($upids, $upperids);
		$anotherTempResult = $this->getAreasByAreadIds($tempids);
		foreach ($anotherTempResult as $k => $v) {
			$upperids[$k] = $v['parentid'];
		}
		return array($upids, $upperids);
	}
	
	/**
	 * 根据parent获取地区
	 * 
	 * @param int $parentid 上一级areaid
	 * @return array
	 */
	function getAreaByAreaParent($parentid = 0) {
		$parentid = intval($parentid);
		if ($parentid < 0) return array();
		$areaDb = $this->_getAreasDB();
		return $areaDb->getAreaByAreaParent($parentid);
	}
	
	/**
	 * 组装单个下拉框
	 * 
	 * @param int $parentid 上一级areaid
	 * @param int $defaultValue 默认选中值的id 
	 * @return array
	 */
	function getAreasSelectHtml($parentid = null, $defaultValue = null) {
		$parentid = intval($parentid);
		if ($parentid < 0) return null;
		$areas = $this->getAreaByAreaParent($parentid);
		if (!S::isArray($areas)) return null;
		$areaSelect = '';
		foreach ($areas as $value) {
			$selected = ($defaultValue && $value['areaid'] == $defaultValue) ? 'selected' : '';
			$areaSelect .= "<option value=\"$value[areaid]\" $selected>{$value[name]}</option>\r\n";
		}
		return $areaSelect;
	}

	/**
	 * 获取数据库中所有地区
	 * @return array
	 */
	function getAllAreas() {
		$areaDb = $this->_getAreasDB();
		return $areaDb->getAllAreas();
	}
	
	/**
	 * 构造地区select框
	 * @param array $initValues 默认选中框 。格式如：array(array('parentid'=>0,'selectid'=>'country','defaultid'=>''));
	 * 										其中parentid为上级id,selectid为select框的id,defaultid为默认选中值id
	 * @return string 组装后字符串
	 */
	function buildAllAreasLists($initValues = array(),$forJs = false) {
		static $sHasArea = null, $sKey = 0;
		$areaString = $forJs?'':'<script type="text/javascript">';
		if (!isset($sHasArea)) {
			$areas = $this->getAllAreas();
			//if (!$areas) return false;
			!$forJs && $areaString .= "\r\n var initValues = new Array();\r\n";
			$areaString .= "var areas = new Array();\r\n";
			foreach ($areas as $value) {
				$areaString .= "areas['$value[areaid]']=['$value[name]','$value[parentid]','$value[vieworder]'];\r\n";
			}
			$sHasArea = true;
		}
		if ($initValues && S::isArray($initValues)) {
			foreach ($initValues as $v) {
				!$v['defaultid'] && $v['defaultid'] = -1;
				!$v['hasfirst'] && !$v['hasfirst'] = 0;
				!$forJs && $areaString .= "initValues[$sKey] = {'parentid':'$v[parentid]','selectid':'$v[selectid]','defaultid':$v[defaultid],'hasfirst':$v[hasfirst]};\r\n";
				$sKey++;
			}
		}
		!$forJs && $areaString .= '</script>';
		return $areaString;
	}
	
	function setAreaCache(){
		$file = D_P .'data/bbscache/areadata.js';
		$basicValue = array(array('parentid'=>0,'selectid'=>'province','defaultid'=>''));
		$data = $this->buildAllAreasLists($basicValue,true);
		$data && writeover($file,$data);
	}
	
	/**
	 *检查数组key
	 * 
	 * @return array 检查后$fieldsData
	 */
	function checkFieldsData($fieldsData){
		$data = array();
		if(isset($fieldsData['areaid'])) $data['areaid'] = intval($fieldsData['areaid']);
		if(isset($fieldsData['name'])) {
			$data['name'] = trim($fieldsData['name']);
			$data['name'] = trim(substrs($data['name'], 60, 'N'), ' &nbsp;');
		}
		if(isset($fieldsData['joinname'])) $data['joinname'] = trim($fieldsData['joinname']);
		if(isset($fieldsData['parentid'])) $data['parentid'] = intval($fieldsData['parentid']);
		if(isset($fieldsData['vieworder'])) $data['vieworder'] = intval($fieldsData['vieworder']);
		return $data;
	}
	
	/**
	 *根据parent获取joinname值
	 * 
	 * @param int $parentid 上一级areaid
	 * @return array 检查后$fieldsData
	 */
	function buildAddData($fieldsData){
		if (!isset($fieldsData['parentid']) || !$fieldsData['parentid']) {
			$fieldsData['joinname'] = $fieldsData['name'];
			return $fieldsData;
		}
		$parentData = $this->getAreaByAreaId($fieldsData['parentid']);
		$fieldsData['joinname'] = $parentData['joinname'].','.$fieldsData['name'];
		return $fieldsData;
	}
	
	/**
	 *加载dao
	 * 
	 * @return PW_AreasDB
	 */
	function _getAreasDB() {
		return L::loadDB('areas', 'utility');
	}
}