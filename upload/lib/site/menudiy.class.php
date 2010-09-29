<?php
!defined('P_W') && exit('Forbidden');

/**
 * 菜单
 * 
 * @package Tool
 */
class MenuDiy {
	var $childs = array();
	function haveChilds() {
		if (count($this->childs) > 0) {
			return true;
		}
		return false;
	}
	
	function addChild($child) {
		if (is_object($child) || is_array($child)) {
			$this->childs[] = $child;
		}
	}
	function myStruct() {
		if ($this->haveChilds()) {
			$temp = array();
			foreach ($this->childs as $child) {
				$str_temp = $child->myStruct();
				if ($str_temp) {
					$temp[] = $str_temp;
				}
			}
			return "[" . implode(',', $temp) . "]";
		}
		return "[]";
	}
}
?>