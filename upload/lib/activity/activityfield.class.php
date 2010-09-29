<?php
/**
 * 活动分类
 * 
 * @author Xia Zuojie
 * @package activity
 */
!defined('P_W') && exit('Forbidden');

class PW_ActivityField {
	/**
	 * @return PW_ActivityFieldDB
	 */
	function _getFieldDb() {
		return L::loadDB('ActivityField', 'activity');
	}
	
	function getEnabledAndSearchableFieldsByModelId($modelId) {
		$fields = $this->getFieldsByModelId($modelId);
		foreach($fields as $key=>$field) {
			if (!$field['ifable'] || (!$field['ifasearch'] && !$field['ifsearch'])) {
				unset($fields[$key]);
				continue;
			}
		}
		return $fields;
	}
	
	function getEnabledAndAdvancedSearchableFieldsByModelId($modelId) {
		$fields = $this->getFieldsByModelId($modelId);
		foreach($fields as $key=>$field) {
			if (!$field['ifable'] || !$field['ifasearch']) {
				unset($fields[$key]);
				continue;
			}
		}
		return $fields;
	}
	
	function getDeletableFieldsByModelId($modelId) {
		$fields = $this->getFieldsByModelId($modelId);
		foreach($fields as $key=>$field) {
			if (!$field['ifdel']) {
				unset($fields[$key]);
				continue;
			}
		}
		return $fields;
	}
	
	function getFieldsByModelId($modelId) {
		$temp = $this->_getFieldDb();
		return $temp->getFieldsByModelId($modelId);
	}
	
	function getDefaultSearchFields() {
		$temp = $this->_getFieldDb();
		return $temp->getDefaultSearchFields();
	}
	
	function getField($id) {
		$temp = $this->_getFieldDb();
		return $temp->getField($id);
	}
	
	function getFieldByModelIdAndName($modelId, $fieldName) {
		$temp = $this->_getFieldDb();
		return $temp->getFieldByModelIdAndName($modelId, $fieldName);
	}
	function insertField($fieldData) {
		$temp = $this->_getFieldDb();
		return $temp->insert($fieldData);
	}
	
	function updateField($id, $fieldData) {
		$temp = $this->_getFieldDb();
		$temp->update($id, $fieldData);
	}
	
	function deleteField($id) {
		$temp = $this->_getFieldDb();
		$temp->delete($id);
	}
	
	function getFieldsByIds($ids) {
		if(!is_array($ids)) {
			return false;
		}
		$temp = $this->_getFieldDb();
		return $temp->getFieldsByIds($ids);
	}
}