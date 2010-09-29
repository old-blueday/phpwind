<?php
/**
 * 活动分类
 * 
 * @author Xia Zuojie
 * @package activity
 */
!defined('P_W') && exit('Forbidden');

class PW_ActivityCategory {
	/**
	 * 是否数据来源于缓存文件
	 * @var bool
	 * @access protected
	 */
	var $_useCache = false;
	/**
	 * @return PW_ActivityCateDB
	 * @access protected
	 */
	function _getCateDb() {
		return L::loadDB('ActivityCate', 'activity');
	}
	/**
	 * @return PW_ActivityModelDB
	 * @access protected
	 */
	function _getModelDb() {
		return L::loadDB('ActivityModel', 'activity');
	}
	function getCates() {
		$temp = $this->_getCateDb();
		return $temp->getCates();
	}
	
	function getModels() {
		$temp = $this->_getModelDb();
		return $temp->getModels();
	}
	
	function updateCate($id, $fieldData) {
		$temp = $this->_getCateDb();
		$temp->update($id, $fieldData);
		return $this;
	}
	
	function updateModel($id, $fieldData) {
		$temp = $this->_getModelDb();
		$temp->update($id, $fieldData);
		return $this;
	}
	
	function getCate($id) {
		$temp = $this->_getCateDb();
		return $temp->get($id);
	}
	
	function deleteCate($id) {
		$temp = $this->_getCateDb();
		return $temp->delete($id);
	}
	
	function getModelsByCateId($id) {
		$temp = $this->_getModelDb();
		return $temp->getModelsByCateId($id);
	}
	
	function getModel($id) {
		$temp = $this->_getModelDb();
		return $temp->get($id);
	}
	
	function getFirstModelByCateId($id) {
		$temp = $this->_getModelDb();
		return $temp->getFirstModelByCateId($id);
	}
	
	function countModelByCateIdAndName($cateId, $name) {
		$temp = $this->_getModelDb();
		return $temp->countModelByCateIdAndName($cateId, $name);
	}
	
	function countModelByCateId($cateId) {
		$temp = $this->_getModelDb();
		return $temp->countModelByCateId($cateId);
	}
	
	function addModel($fieldData) {
		$temp = $this->_getModelDb();
		return $temp->insert($fieldData);
	}
	
	function updateModelByCateIdInIds($cateId, $modelIds, $fieldData) {
		$temp = $this->_getModelDb();
		return $temp->updateModelByCateIdInIds($cateId, $modelIds, $fieldData);
	}
	
	function updateModelByCateIdNotInIds($cateId, $modelIds, $fieldData) {
		$temp = $this->_getModelDb();
		return $temp->updateModelByCateIdNotInIds($cateId, $modelIds, $fieldData);
	}
	
	function getCateIdByModelId($modelId) {
		$model = $this->getModel($modelId);
		return $model['actid'];
	}
	
	function setUseCache($bool) {
		if ($bool) {
			$this->_useCache = true;
		} else {
			$this->_useCache = false;
		}
		return $this;
	}
}