<?php
!defined('P_W') && exit('Forbidden');
include_once (R_P . 'lib/datanalyse/datanalyse.base.php');

class PW_Diaryanalyse extends PW_Datanalyse {
	var $pk = 'did';
	var $actions = array('diaryNew', 'diaryComment', 'diaryFav', 'diaryShare', 'diaryRate');

	function PW_Diaryanalyse() {
		$this->__construct();
	}

	function __construct() {
		parent::__construct();
	}

	/**
	 * 获得根据类别评价类型
	 * @return array
	 */
	function _getExtendActions() {
		global $db_ratepower;
		$rateSets = unserialize($db_ratepower);
		if ($rateSets[2]) {
			$rate = L::loadClass('rate', 'rate');
			$_tmp = $rate->getRateDiaryHotTypes();
		}
		return is_array($_tmp) ? array_keys($_tmp) : array();
	}

	/**
	 * 根据日志ID数组获得日志信息
	 * @return array
	 */
	function _getDataByTags() {
		if (empty($this->tags)) return array();
		$diaryDB = L::loadDB('diary', 'diary');
		$result = $diaryDB->getsByDids($this->tags);
		return $result;
	}

}
?>