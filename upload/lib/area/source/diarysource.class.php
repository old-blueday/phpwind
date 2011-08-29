<?php
!defined('P_W') && exit('Forbidden');
require_once (R_P . 'lib/base/systemdata.php');

class PW_DiarySource extends SystemData {

	/**
	 * 根据配置信息获得日志排行数据
	 * @param Array $config
	 * @param int $num
	 */
	function getSourceData($config, $num) {
		$config = $this->_initConfig($config);
		$_tmp = $this->_getData($config['diarysort'], $num);
		foreach ($_tmp as $key => $value) {
			$_tmp[$key] = $this->_cookData($value);
		}
		return $_tmp;
	}

	/* (non-PHPdoc)
	 * @see lib/base/SystemData#getSourceConfig()
	 */
	function getSourceConfig() {
		return array(
			'diarysort' => array(
				'name' => '日志排行', 
				'type' => 'select', 
				'value' => array(
					'new' => '最新日志', 
					'comment' => '评论排行', 
					'rate' => '评价排行', 
					'fav' => '收藏排行', 
					//'share' => '分享排行'
				)
			)
		);
	}

	/**
	 * 根据类型获得日志排行数据
	 * @param string $type
	 * @param int $num
	 */
	function _getData($type, $num) {
		$element = $this->_getElement();
		if (!$type) $type = 'new';
		switch ($type) {
			case 'new' :
				return $element->getDataByAction('diary', 'diaryNew', $num);
			case 'comment' :
				return $element->getDataByAction('diary', 'diaryComment', $num);
			case 'rate' :
				return $element->getDataByAction('diary', $this->_getDiaryRateTypes(), $num);
			case 'fav' :
				return $element->getDataByAction('diary', 'diaryFav', $num);
			case 'share' :
				return $element->getDataByAction('diary', 'diaryShare', $num);
			default :
				return array();//fix warning by noizy
		}
	}

	/**
	 * 获得照片评论类型
	 * @return multitype:
	 */
	function _getDiaryRateTypes() {
		global $db_ratepower;
		$rateSets = unserialize($db_ratepower);
		if ($rateSets[2]) {
			$rate = L::loadClass('rate', 'rate');
			$_tmp = $rate->getRateDiaryHotTypes();
		}
		return array_keys($_tmp);
	}

	/**
	 * 格式化数据统一输出
	 * @param array $data
	 * @return array
	 */
	function _cookData($data) {
		global $db_bbsurl;
		if($data['uid']){
			$userService = L::loadClass('userService', 'user');
			$data['authorid'] = $data['uid'];
			$data['author'] = $userService->getUserNameByUserId($data['uid']);
			$data['authorurl'] = 'u.php?uid='.$data['uid'];
		}else{
			$data['author'] = '';
			$data['authorurl'] = '';
		}
		$data['url'] = $db_bbsurl . '/apps.php?q=diary&a=detail&did=' . $data['did'] . '&uid=' . $data['uid'];
		$data['title'] = strip_tags($data['subject']);
		$data['descrip'] = substrs(strip_tags(stripWindCode($data['content'])),100);
		return $data;
	}

	/**
	 * @param array $config
	 * @return array
	 */
	function _initConfig($config) {
		$temp = array();
		$temp['diarysort'] = isset($config['diarysort']) ? $config['diarysort'] : '';
		return $temp;
	}

	function _getElement() {
		if (!$this->_element) {
			$this->_element = L::loadClass('datanalyseService', 'datanalyse');
		}
		return $this->_element;
	}
}