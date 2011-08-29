<?php
!defined('P_W') && exit('Forbidden');
require_once (R_P.'lib/base/systemdata.php');
require_once (R_P.'u/require/core.php');
class PW_GroupImageSource extends SystemData {
	var $_dataAnalyseService;
	
	function getSourceData($config,$num) {
		$config = $this->_initConfig($config);
		$result = $this->_getData($config['sorttype'],$num);
		foreach ($result as $key => $value) {
			$result[$key] = $this->_cookData($value);
		}
		return $result;
	}
	
	function _getData($sortType,$num) {
		$dataAnalyseService = $this->_getDataAnalyseService();
		switch ($sortType) {
			case '':
			case 'new' :
				return $dataAnalyseService->getDataByAction('groupphoto', 'groupPicNew', $num);
			case 'comment' :
				return $dataAnalyseService->getDataByAction('groupphoto', 'groupPicComment', $num);
		}
		return array();
	}

	function getSourceConfig() {
		return array(
			'sorttype' => array(
				'name' => '排序类型',
				'type' => 'select',
				'value' => array(
					'new'	=> '最新图片',
					'comment'	=> '热门图片'
				),
			)
		);
	}
	
	function _cookData($data) {
		global $db_bbsurl;
		$data['url'] = $db_bbsurl . '/apps.php?q=galbum&a=view&cyid='.$data['ownerid'].'&pid=' . $data['pid'];
		$data['title'] = $data['aname'];
		$data['image'] = getphotourl($data['path']);
		return $data;
	}
	
	function _initConfig($config) {
		$temp = array();
		$temp['sorttype'] = $config['sorttype'];

		return $temp;
	}
	
	function _getDataAnalyseService() {
		if (!$this->_dataAnalyseService) {
			$this->_dataAnalyseService = L::loadClass('datanalyseService', 'datanalyse');
		}
		return $this->_dataAnalyseService;
	}
	
}