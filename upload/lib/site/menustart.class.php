<?php
!defined('P_W') && exit('Forbidden');

/**
 * 菜单
 * 
 * @package Tool
 */
class MenuStart {
	var $childs = array();
	function haveChilds() {
		if (count($this->childs) > 0) {
			return true;
		}
		return false;
	}
	function haveItems() {
		if ($this->haveChilds()) {
			foreach ($this->childs as $child) {
				if ($child->haveItems()) {
					return true;
				}
			}
		}
		return false;
	}
	function addChild($child) {
		if (is_object($child) || is_array($child)) {
			$this->childs[] = $child;
		}
	}
	function getRealChilds() {
		if ($this->haveChilds()) {
			$temp = array();
			foreach ($this->childs as $child) {
				$str_temp = $child->myStruct();
				if ($str_temp) {
					$temp[] = $str_temp;
				}
			}
			if (count($temp) > 0) {
				return $temp;
			}
			return false;
		}
		return false;
	}
	function myStruct() {
		$temp = $this->getRealChilds();
		if ($temp) {
			return "{" . implode(',', $temp) . "}";
		}
		return false;
	}
}
?>