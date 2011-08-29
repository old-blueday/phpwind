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
					'money'		=>	$GLOBALS['db_moneyname'],
					'rvrc'		=>	$GLOBALS['db_rvrcname'],
					'credit'	=>	$GLOBALS['db_creditname'],
					'currency'	=>	$GLOBALS['db_currencyname'],
					'todaypost'	=>	'今日发帖',
					'monthpost'	=>	'一月发帖',
					'postnum'	=>	'发帖排行',
					'monoltime'	=>	'一月在线',
					'onlinetime'=>	'在线排行',
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