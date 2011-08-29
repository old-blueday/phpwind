<?php
!defined('P_W') && exit('Forbidden');
require_once (R_P.'lib/base/systemdata.php');
class PW_ForumSource extends SystemData {
	var $_element;
	var $_lang = array(
		'title'	=> '版块名称',
	);
	function getSourceData($config,$num) {
		$config = $this->_initConfig($config);
		$element = $this->_getElement();
		return $element->forumSort($config['forumsort'],$num);
	}
	
	function getRelateType() {
		return false;
	}
	//article：帖子总数，topic：主题数，tpost：今日发帖数
	function getSourceConfig() {
		return array(
			'forumsort' 	=> array(
				'name' 	=> '版块排行',
				'type'	=> 'select',
				'value'	=> array(
					'article'	=> '帖子排行',
					'topic'		=> '主题排行',
					'tpost'		=> '今日排行',
				),
			),
		);
	}
	
	function _getElement() {
		if (!$this->_element) {
			$this->_element = L::loadClass('element');
		}
		return $this->_element;
	}
	
	function _initConfig($config) {
		$temp = array();
		$temp['forumsort'] = $config['forumsort'];

		return $temp;
	}
	
}