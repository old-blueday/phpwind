<?php
!defined('P_W') && exit('Forbidden');

/**
 * 菜单项
 * 
 * @package Tool
 */
class MenuItem {
	var $name;
	var $id;
	var $url;
	var $isdiy;
	function menuItem($id, $name, $url, $isdiy = false) {
		$this->id = $id;
		$this->name = $name;
		$this->url = $url;
		$this->isdiy = $isdiy;
	}
	function getName() {
		return $this->name;
	}
	function getUrl() {
		return $this->url;
	}
	function getId() {
		return $this->id;
	}
	function getIsDiy() {
		return $this->isdiy;
	}
	function myStruct() {
		return "{id:'" . $this->getId() . "',name:'" . $this->getName() . "',url:'" . $this->getUrl() . "'}";
	}
}
?>