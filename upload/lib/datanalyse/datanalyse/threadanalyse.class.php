<?php
!defined('P_W') && exit('Forbidden');
include_once (R_P . 'lib/datanalyse/datanalyse.base.php');

/**
 * 帖子排行
 * @author yishuo
 */
class PW_Threadanalyse extends PW_Datanalyse {
	var $pk = 'tid';
	/* 回复排行，评价排行，收藏排行，分享排行 */
	var $actions = array('threadPost', 'threadFav', 'threadShare', 'threadRate');

	function PW_Threadanalyse() {
		$this->__construct();
	}

	function __construct() {
		parent::__construct();
	}

	/**
	 * 获得类别评价类型
	 * 	1 帖子评价
	 *  2 日志评价
	 *  3 相片评价
	 * @return array
	 */
	function _getExtendActions() {
		global $db_ratepower;
		$rateSets = unserialize($db_ratepower);
		if ($rateSets[1]) {
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
		$threadsDB = L::loadDB('threads', 'forum');
		$result = $threadsDB->getsBythreadIds($this->tags);
		return $result;
	}

}
?>