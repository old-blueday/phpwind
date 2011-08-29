<?php
!defined('P_W') && exit('Forbidden');

class PW_LevelHook {
	var $levels = array();
	var $rights = array();
	
	var $systemLevels = array();
	var $systemRights = array();

	function __construct() {

	}

	function PW_LevelHook() {
		$this->__construct();
	}

	function init($db_modes, $group = array(),$system=array()) {
		foreach ($db_modes as $key => $value) {
			$levelFile = S::escapePath(R_P . 'mode/' . $key . '/config/level.php');
			if (!file_exists($levelFile)) continue;
			$level = include ($levelFile);
			$this->_cookLevel($key,$level, $group, $system);
		}
	}
	
	function _cookLevel($mode,$level,$group,$system) {
		foreach ($level['field'] as $key => $value) {
			if (isset($value['gptype']) && $value['gptype']=='system') {
				$value['html'] = $this->_getTypeHtml($key, $value, $system);
				$this->systemRights[] = $key;
				$this->systemLevels[$key] = $value;
				unset($level['field'][$key]);
			} else {
				$level['field'][$key]['html'] = $this->_getTypeHtml($key, $value, $group);
				$this->rights[] = $key;
			}
		}
		if ($level['field']) {
			$this->levels[$mode] = $level;
		}
	}
	
	function getSystemLevels() {
		return $this->systemLevels;
	}
	function getSystemRights() {
		if (!$this->systemRights) {
			$this->systemRights = array_keys($this->systemLevels);
		}
		return $this->systemRights;
	}

	function getLevels() {
		return $this->levels;
	}

	function getRights() {
		if (!$this->rights) {
			foreach ($this->levels as $key => $value) {
				$rights = array_keys($value['field']);
				$this->rights += $rights;
			}
		}
		return $this->rights;
	}

	/**
	 * 返回只带标题的扩展权限
	 */
	function getOtherLevelTitles() {
		$result = array();
		foreach ($this->levels as $key => $value) {
			$result[$key] = $value['title'];
		}
		return $result;
	}
/*
	function _cookLevel($level, $group) {
		foreach ($level['field'] as $key => $value) {
			$level['field'][$key]['html'] = $this->_getTypeHtml($key, $value, $group);
			$this->right[] = $key;
		}
		return $level;
	}
*/
	function _getTypeHtml($field, $fieldInfo, $group) {
		switch ($fieldInfo['type']) {
			case 'radio' :
				return $this->_getRadioHtml($field, $fieldInfo['value'], $group);
			case 'text' :
				return $this->_getTextHtml($field, $fieldInfo['value'], $group);
			default :
				return '';
		}
	}

	function _getRadioHtml($field, $values, $group) {
		$_html = '<ul class="list_A list_80 cc">';
		foreach ($values as $key => $value) {
			$checked = $group[$field] == $key ? 'checked="checked"' : '';
			$_html .= '<li><input value="' . $key . '" name="group[' . $field . ']" type="radio" ' . $checked . '>' . $value . '</li>';
		}
		$_html .= '</ul>';
		return $_html;
	}
	
	function _getTextHtml($field, $value, $group){		
		$_html = '<input value="' . $group[$field] . '" name="group[' . $field . ']" type="text" />';
		return $_html;	
	}

}