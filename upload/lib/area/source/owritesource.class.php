<?php
!defined('P_W') && exit('Forbidden');
require_once (R_P . 'lib/base/systemdata.php');

class PW_OwriteSource extends SystemData {

	/**
	 * @param array $config
	 * @param int $num
	 */
	function getSourceData($config, $num) {
		$config = $this->_initConfig($config);
		$_tmp = $this->_getData($config['writesort'], $num);
		foreach ($_tmp as $key => $value) {
			$temp = $this->_cookData($value);
			if (!$temp['title']) continue; 
			$_tmp[$key] = $temp;
		}
		return $_tmp;
	}

	/**
	 * @param string $type
	 * @param int $num
	 */
	function _getData($type, $num) {
		switch ($type) {
			case '':
			case 'new' :
				$write = L::loadDB('owritedata', 'sns');
				return $write->getNewData($num);
			case 'comment' :
				$datanalyse = L::loadClass('datanalyseService', 'datanalyse');
				return $datanalyse->getDataByAction('owrite', 'writeComment', $num);
			default :
				return;
		}
	}

	/**
	 * 格式化输出结果
	 * @param unknown_type $data
	 * @return unknown
	 */
	function _cookData($data) {
		global $db_bbsurl;
		$data['url'] = $db_bbsurl . '/apps.php?q=write';
		$data['title'] = strip_tags($data['content']);
		return $data;
	}

	/**(non-PHPdoc)
	 * @see lib/base/SystemData#getSourceConfig()
	 */
	function getSourceConfig() {
		return array(
			'writesort' => array(
				'name' => '记录排行', 
				'type' => 'select', 
				'value' => array(
					'new' => '最新记录', 
					'comment' => '回复排行'
				)
			)
		);
	}

	/**
	 * @param array $config
	 * @return array
	 */
	function _initConfig($config) {
		$temp = array();
		$temp['writesort'] = isset($config['writesort']) ? $config['writesort'] : '';
		return $temp;
	}

}
?>