<?php
!defined('P_W') && exit('Forbidden');
require_once (R_P . 'lib/base/systemdata.php');

class PW_SharelinksSource extends SystemData {

	/**
	 * @param array $config
	 * @param int $num
	 */
	function getSourceData($config, $num) {
		$config = $this->_initConfig($config);
		$_tmp = $this->_getData($config['sharelinksort'], $num);
		foreach ($_tmp as $key => $value) {
			$_tmp[$key] = $this->_cookData($value);
		}
		return $_tmp;
	}

	/**
	 * @param string $type
	 * @param int $num
	 */
	function _getData($type, $num) {
		$write = L::loadDB('sharelinks', 'site');
		$haveLogo = $type == 'new' ? false : true;
		return $write->getNewData($num,$haveLogo);
	}

	/**
	 * 格式化输出结果
	 * @param unknown_type $data
	 * @return unknown
	 */
	function _cookData($data) {
		global $db_bbsurl;
		$data['title'] = $data['name'];
		$data['image'] = $data['logo'];
		return $data;
	}

	/**(non-PHPdoc)
	 * @see lib/base/SystemData#getSourceConfig()
	 */
	function getSourceConfig() {
		return array(
			'sharelinksort' => array(
				'name' => '友情链接',
				'type' => 'select',
				'value' => array(
					'new' => '文字友情链接',
					'newhavelogo' => '图片友情链接'
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
		$temp['sharelinksort'] = isset($config['sharelinksort']) ? $config['sharelinksort'] : 'new';
		return $temp;
	}

}
?>