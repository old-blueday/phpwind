<?php
!defined('P_W') && exit('Forbidden');
require_once (R_P.'lib/base/systemdata.php');
class PW_ImageSource extends SystemData {
	var $_element;
	function getSourceData($config,$num) {
		$config = $this->_initConfig($config);
		
		$element = $this->_getElement();
		return $element->newPic($config['fid'],$num);
	}
	
	function getRelateType() {
		return 'subject';
	}
	
	function getSourceConfig() {
		return array(
			'fid'	=> array(
				'name' 	=> '选择版块',
				'type' 	=> 'mselect',
				'value'	=> $this->_getForums(),
			),
		);
	}
	
	function _getForums() {
		$forumOption = L::loadClass('forumoption');
		return $forumOption->getForums();
	}
	
	function _getElement() {
		if (!$this->_element) {
			$this->_element = L::loadClass('element');
		}
		return $this->_element;
	}
	
	function _initConfig($config) {
		$temp = array();
		$temp['fid'] = $config['fid'];

		return $temp;
	}
}