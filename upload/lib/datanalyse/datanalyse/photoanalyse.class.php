<?php
!defined('P_W') && exit('Forbidden');
include_once (R_P . 'lib/datanalyse/datanalyse.base.php');

/**
 * 相册排行
 * @author yishuo
 */
class PW_Photoanalyse extends PW_Datanalyse {
	var $pk = 'pid';
	var $actions = array('picNew', 'picComment', 'picFav', 'picShare', 'picRate');

	function PW_Photoanalyse() {
		$this->__construct();
	}

	function __construct() {
		parent::__construct();
	}

	/**
	 * 获得根据类别评价类型
	 *  1 帖子评价
	 *  2 日志评价
	 *  3 相片评价
	 * @return array
	 */
	function _getExtendActions() {
		global $db_ratepower;
		$rateSets = unserialize($db_ratepower);
		$_tmp = array();
		if ($rateSets[3]) {
			$rate = L::loadClass('rate', 'rate');
			$_tmp = $rate->getRatePictureHotTypes();
		}
		return array_keys($_tmp);
	}

	/**
	 * 根据日志ID数组获得日志信息
	 * @return array
	 */
	function _getDataByTags() {
		if (empty($this->tags)) return array();
		$cnphotoDB = L::loadDB('cnphoto', 'colony');
		$result = $cnphotoDB->getDataByPids($this->tags);
		return $result;
	}

}
?>