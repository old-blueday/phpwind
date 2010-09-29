<?php
!defined('P_W') && exit('Forbidden');
require_once (R_P . 'lib/base/systemdata.php');

class PW_WeiboSource extends SystemData {
	
	/**
	 * 根据配置信息获得新鲜事排行数据
	 * @param Array $config
	 * @param int $num
	 */
	function getSourceData($config, $num) {
		$config = $this->_initConfig($config);
		$_tmp = $this->_getData($config['weibotype'], $num);
		foreach ($_tmp as $key => $value) {
			$_tmp[$key] = $this->_cookData($value);
		}
		return $_tmp;
	}
	
	function getSourceConfig() {
		return array(
			'weibotype' => array(
				'name' => '微薄类型',
				'type' => 'select',
				'value' => $this->_getWeiboType()
			)
		);
	}
	
	function _getWeiboType() {
		return array(
			'all' => '所有类型',
			'article' => '帖子',
			'diary' => '日志',
			//'photos' => '相册',
			'group_article' => '群组话题',
			//'group_photos' => '群组相册',
			'group_active' => '群组活动',
			'group_write' => '群组记录/讨论'
		);
	}
	
	/**
	 * 根据类型获得文章排行数据
	 * @param string $type
	 * @param int $columnid
	 * @param int $num
	 */
	function _getData($type, $num) {
		$num = (int) $num;
		if (!$num) $num = 10;
		switch ($type) {
			case 'all':
				return $this->_getAllWeibo($num);
			default:
				return $this->_getWeibosByType($type,$num);
		}
	}
	
	function _getAllWeibo($num) {
		$weiboService = $this->_getWeiboService();
		return $weiboService->getWeibos(1, $num);
	}
	
	function _getWeibosByType($type, $num) {
		$weiboService = $this->_getWeiboService();
		return $weiboService->getWeibosByType($type, 1, $num);
	}
	
	/**
	 * 格式化数据统一输出
	 * @param array $data
	 * @return array
	 */
	function _cookData($data) {
		global $db_bbsurl;
		unset($data['password']);
		$data['url'] = $db_bbsurl . '/u.php?uid=' . $data['uid'];
		$data['title'] = strip_tags($data['content']);
		$data['descrip'] = strip_tags($data['content']);
		if ($data['extra']['photos'] && is_array($data['extra']['photos'])) {
			$image = $data['extra']['photos'][0];
			$temp = geturl($image['path']);
			$data['image'] = $temp[0] ? $temp[0] : '';
		}
		return $data;
	}
	
	/**
	 * @param array $config
	 * @return array
	 */
	function _initConfig($config) {
		$temp = array();
		$temp['weibotype'] = isset($config['weibotype']) ? $config['weibotype'] : 'all';
		return $temp;
	}
	
	function _getWeiboService() {
		return L::loadClass('weibo', 'sns'); /*@var PW_Weibo*/
	}
}