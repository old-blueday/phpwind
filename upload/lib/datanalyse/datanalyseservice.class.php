<?php
!function_exists('readover') && exit('Forbidden');

class PW_DatanalyseService {
	var $types = array('diary', 'photo', 'thread', 'user', 'groupphoto', 'owrite');

	/**
	 * @param string $type
	 * @param string/array $action
	 * @param int $num
	 */
	function getDataByAction($type, $action, $num) {
		$datanalyse = $this->_getDatanalyseServiceByType($type);
		return $datanalyse->getDataAndNumsByAction($action, $num);
	}

	/**
	 * @param string $type
	 * @param string $action
	 * @param int $num
	 * @param int $time
	 */
	function getDataByActionAndTime($type, $action, $num, $time) {
		$datanalyse = $this->_getDatanalyseServiceByType($type);
		return $datanalyse->getDataAndNumsByAction($action, $num, $time);
	}
	
	/**
	 * @param string $type
	 * @param string $action
	 * @param int $num
	 * @param int $time
	 */
	function getHotArticleByAction($type, $action, $num, $time) {
		$datanalyse = $this->_getDatanalyseServiceByType($type);
		return $datanalyse->getHotArticleByAction($action, $num, $time);
	}

	/**
	 * 过滤支持的类型白名单
	 * @param string $type
	 * @return string
	 */
	function _filterType($type) {
		return in_array($type, $this->types) ? $type : '';
	}

	/**
	 * @param string $type
	 * @return Object
	 */
	function _getDatanalyseServiceByType($type) {
		$type = $this->_filterType($type);
		return L::loadClass(strtolower($type) . 'analyse', 'datanalyse/datanalyse');
	}
}
?>