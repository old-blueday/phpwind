<?php
!defined('P_W') && exit('Forbidden');
require_once (R_P.'lib/base/systemdata.php');
class PW_UserSource extends SystemData {
	var $_element;
	var $_lang = array(
		'title'	=> '用户名',
	);
	function getSourceData($config,$num) {
		$config = $this->_initConfig($config);
		$element = $this->_getElement();
		return $element->userSort($config['usersort'],$num);
	}
	
	function getRelateType() {
		return false;
	}
	
	function getSourceConfig() {
		return array(
			'usersort' 	=> array(
				'name' 	=> '会员排行',
				'type'	=> 'select',
				'value'	=> array(
					'money'		=>	'金钱',
					'rvrc'		=>	'威望',
					'onlinetime'=>	'在线时间排行',
					'todaypost'	=>	'今日发帖',
					'monthpost'	=>	'月发帖',
					'postnum'	=>	'发帖排行',
					'monoltime'	=>	'月在线',
					'credit'	=>	'贡献值',
					'currency'	=>	'交易币',
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
		$temp['usersort'] = $config['usersort'];

		return $temp;
	}
}