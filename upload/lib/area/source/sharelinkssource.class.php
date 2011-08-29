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
		$_tmp = $this->_getData($config, $num);
		foreach ($_tmp as $key => $value) {
			$_tmp[$key] = $this->_cookData($value);
		}
		return $_tmp;
	}

	/**
	 * @param string $type
	 * @param int $num
	 */
	function _getData($config, $num) {
		$shareService = L::loadClass( 'SharelinksService' , 'site' );
		$wighimg = $config['sharelinksort'] == 'new' ? false : true;
		return $shareService->getData( $num, $config['sharelinktype'], $wighimg);
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
			),
			'sharelinktype' => array(
				'name' => '链接分类',
				'type' => 'select',
				'value' => $this->_getSharesTypes()
			)
		);
	}
	
	function _getSharesTypes(){
		$typeService = L::loadClass( 'SharelinkstypeService' , 'site' );
		$ret = $typeService->getAllTypes();
		$result['all'] = '所有分类';
		foreach ($ret as $value){
			$result[$value['stid']] = $value['name'];
		}
		return $result;
	}

	/**
	 * @param array $config
	 * @return array
	 */
	function _initConfig($config) {
		$temp = array();
		$temp['sharelinksort'] = isset($config['sharelinksort']) ? $config['sharelinksort'] : 'new';
		$temp['sharelinktype'] = ($config['sharelinktype'] != 'all') ? $config['sharelinktype'] : null;
		return $temp;
	}

}
?>