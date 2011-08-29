<?php
!defined('P_W') && exit('Forbidden');
require_once (R_P . 'lib/base/systemdata.php');

class PW_PhotoSource extends SystemData {

	/**
	 * @param array $config
	 * @param int $num
	 */
	function getSourceData($config, $num) {
		$config = $this->_initConfig($config);
		$_tmp = $this->_getData($config['photosort'], $num);
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
		$element = $this->_getElement();
		switch ($type) {
			case '':
			case 'new' :
				return $element->getDataByAction('photo', 'picNew', $num);
			case 'comment' :
				return $element->getDataByAction('photo', 'picComment', $num);
			case 'rate' :
				return $element->getDataByAction('photo', (array) $this->_getPictureRateTypes(), $num);
			case 'fav' :
				return $element->getDataByAction('photo', 'picFav', $num);
			case 'share' :
				return $element->getDataByAction('photo', 'picShare', $num);
			default :
				return array();
		}
	}

	/**
	 * 获得照片评论类型
	 * @return multitype:
	 */
	function _getPictureRateTypes() {
		global $db_ratepower;
		$rateSets = unserialize($db_ratepower);
		$_tmp = array();
		if ($rateSets[3]) {
			$rate = L::loadClass('rate', 'rate');
			$_tmp = $rate->getRatePictureHotTypes();
		}
		return array_keys($_tmp);
	}

	/**(non-PHPdoc)
	 * @see lib/base/SystemData#getSourceConfig()
	 */
	function getSourceConfig() {
		return array(
			'photosort' => array(
				'name' => '照片排行', 
				'type' => 'select', 
				'value' => array(
					'new' => '最新照片', 
					'comment' => '评论排行', 
					'rate' => '评价排行', 
					'fav' => '收藏排行', 
//					'share' => '分享排行'
				)
			)
		);
	}

	function _cookData($data) {
		global $db_bbsurl,$attachpath;
		$data['url'] = $db_bbsurl . '/apps.php?q=photos&a=view&pid=' . $data['pid'];
		$data['title'] = $data['pintro'];
		if($data['path'] && substr($rt['path'],0,7) != 'http://'){
				$a_url = geturl($data['path']);
				$data['imgurl'] = is_array($a_url) ? $a_url[0] : $a_url;
		}
		$data['image'] = $data['imgurl'];
		return $data;
	}

	/**
	 * @param array $config
	 * @return array
	 */
	function _initConfig($config) {
		$temp = array();
		$temp['photosort'] = isset($config['photosort']) ? $config['photosort'] : '';
		return $temp;
	}

	function _getElement() {
		if (!$this->_element) {
			$this->_element = L::loadClass('datanalyseService', 'datanalyse');
		}
		return $this->_element;
	}
	
}
?>