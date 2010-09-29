<?php
!defined('P_W') && exit('Forbidden');

/**
 * 菜单
 * 
 * @package Tool
 */
class MenuHead {
	var $childs = array();
	var $name;
	var $id;
	function MenuHead($id, $name) {
		$this->id = $id;
		$this->name = $name;
	}
	function getName() {
		return $this->name;
	}
	function getId() {
		return $this->id;
	}
	function haveChilds() {
		if (count($this->childs) > 0) {
			return true;
		}
		return false;
	}
	function haveItems() {
		if ($this->haveChilds()) {
			foreach ($this->childs as $child) {
				$className = strtolower(get_class($child));
				if ($className == 'menu' && $child->haveItems() || $className == 'menuitem') {
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
			return "'" . $this->getId() . "':{'id':'','items':[" . implode(',', $temp) . "]}";
		}
		return false;
	}
}
?>